<?php

namespace App\Mcp\Tools;

use App\Models\CourseClassAttendance;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Lists class attendances for a course class, optionally filtered by month (YYYY-MM). Requires admin.')]
#[IsReadOnly]
#[IsIdempotent]
class ListAttendancesTool extends Tool
{
    public function shouldRegister(Request $request): bool
    {
        $user = $request->user();

        // Local STDIO has no user: allow (trusted machine).
        if (! $user) {
            return true;
        }

        return method_exists($user, 'hasRole') ? $user->hasRole('admin') : true;
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate(
            [
                'course_class_id' => 'required|integer|exists:course_classes,id',
                'month' => 'nullable|date_format:Y-m',
            ],
            [
                'course_class_id.required' => 'You must provide course_class_id, e.g. 10 for Info J SFS 8h.',
                'month.date_format' => 'Month must be in YYYY-MM format, e.g. 2026-10.',
            ]
        );

        $query = CourseClassAttendance::where('course_class_id', $validated['course_class_id'])
            ->orderBy('attendance_date');

        if (! empty($validated['month'])) {
            $start = $validated['month'].'-01';
            $end = date('Y-m-t', strtotime($start));
            $query->whereBetween('attendance_date', [$start, $end]);
        }

        $rows = $query->get(['id', 'course_class_id', 'name', 'attendance_date', 'duration_hours']);

        return Response::structured([
            'count' => $rows->count(),
            'total_hours' => (float) $rows->sum('duration_hours'),
            'attendances' => $rows->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'date' => $a->attendance_date?->toDateString() ?? (string) $a->attendance_date,
                'duration_hours' => (float) $a->duration_hours,
            ])->toArray(),
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_class_id' => $schema->integer()->description('Course class ID, e.g. 10 or 11.')->required(),
            'month' => $schema->string()->description('Optional month filter YYYY-MM.'),
        ];
    }
}
