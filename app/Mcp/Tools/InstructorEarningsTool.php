<?php

namespace App\Mcp\Tools;

use App\Models\CourseClassAttendance;
use App\Models\Instructor;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Calculates instructor earnings for a month (YYYY-MM) from attendances x contract rate. Requires admin. Same logic as financial reports.')]
#[IsReadOnly]
#[IsIdempotent]
class InstructorEarningsTool extends Tool
{
    public function shouldRegister(Request $request): bool
    {
        $user = $request->user();
        if (! $user) {
            return true;
        }

        return method_exists($user, 'hasRole') ? $user->hasRole('admin') : true;
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate(
            [
                'month' => 'required|date_format:Y-m',
                'instructor_id' => 'nullable|integer|exists:instructors,id',
            ],
            ['month.date_format' => 'Month must be YYYY-MM, e.g. 2026-10.']
        );

        $monthStart = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $attendances = CourseClassAttendance::with(['courseClass.instructor.contracts', 'courseClass.course'])
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        if (! empty($validated['instructor_id'])) {
            $attendances = $attendances->filter(
                fn ($a) => $a->courseClass?->instructor_id == $validated['instructor_id']
            );
        }

        $grouped = $attendances->groupBy(fn ($a) => $a->courseClass?->instructor_id ?? 0);
        $rows = [];

        foreach ($grouped as $instructorId => $items) {
            $instructor = Instructor::find($instructorId);

            // Group by course: contracts are per-course (course_id match) with
            // NULL course_id as fallback — same logic as FinancialReportController.
            $byCourse = $items->groupBy(fn ($a) => $a->courseClass?->course_id ?? 0);
            $hours = (float) $items->sum('duration_hours');
            $sessions = $items->count();
            $earnings = 0.0;
            $breakdown = [];

            foreach ($byCourse as $courseId => $courseItems) {
                $cHours = (float) $courseItems->sum('duration_hours');
                $cSessions = $courseItems->count();
                $contract = $instructor?->contracts->first(function ($c) use ($monthStart, $monthEnd, $courseId) {
                    if (! $c->active) {
                        return false;
                    }
                    if ($courseId && $c->course_id && (int) $c->course_id !== (int) $courseId) {
                        return false;
                    }

                    return $c->starts_at && $c->starts_at->lte($monthEnd)
                        && (! $c->ends_at || $c->ends_at->gte($monthStart));
                });

                $cEarnings = 0.0;
                if ($contract) {
                    if ($contract->payment_type === 'hourly') {
                        $cEarnings = $cHours * (float) $contract->hourly_rate;
                    } elseif ($contract->payment_type === 'monthly_fixed' && $cSessions > 0) {
                        $cEarnings = (float) $contract->monthly_amount;
                    }
                }
                $earnings += $cEarnings;
                $breakdown[] = [
                    'course_id' => $courseId,
                    'course' => $courseItems->first()?->courseClass?->course?->title,
                    'hours' => $cHours,
                    'sessions' => $cSessions,
                    'payment_type' => $contract?->payment_type,
                    'rate' => $contract?->payment_type === 'hourly'
                        ? (float) $contract->hourly_rate
                        : (float) $contract?->monthly_amount,
                    'earnings' => round($cEarnings, 2),
                ];
            }

            $rows[] = [
                'instructor_id' => $instructorId,
                'instructor' => $instructor?->full_name,
                'hours' => $hours,
                'sessions' => $sessions,
                'earnings' => round($earnings, 2),
                'by_course' => $breakdown,
            ];
        }

        return Response::structured([
            'month' => $validated['month'],
            'total_earnings' => round(array_sum(array_column($rows, 'earnings')), 2),
            'rows' => $rows,
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'month' => $schema->string()->description('Month YYYY-MM, e.g. 2026-10.')->required(),
            'instructor_id' => $schema->integer()->description('Optional instructor ID filter.'),
        ];
    }
}
