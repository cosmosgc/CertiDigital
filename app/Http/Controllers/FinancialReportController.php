<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseClassAttendance;
use App\Models\Instructor;
use App\Models\InstructorContract;
use App\Models\InstructorPayment;
use App\Models\StudentBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $monthInput = (string) $request->query('month', now()->format('Y-m'));

        try {
            $referenceMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Throwable $e) {
            $referenceMonth = now()->startOfMonth();
        }

        $monthStart = $referenceMonth->copy()->startOfMonth();
        $monthEnd = $referenceMonth->copy()->endOfMonth();

        // --- Filter parsing ---
        $excludeCourses = $request->query('exclude_courses', []);
        $excludeClasses = $request->query('exclude_classes', []);
        if (!is_array($excludeCourses)) $excludeCourses = explode(',', $excludeCourses);
        if (!is_array($excludeClasses)) $excludeClasses = explode(',', $excludeClasses);
        $excludeCourses = array_filter($excludeCourses, fn($v) => is_numeric($v));
        $excludeClasses = array_filter($excludeClasses, fn($v) => is_numeric($v));
        $excludedClassIds = [];
        if (!empty($excludeCourses)) {
            $excludedClassIds = CourseClass::whereIn('course_id', $excludeCourses)->pluck('id')->toArray();
        }
        $excludedClassIds = array_unique(array_merge($excludedClassIds, array_map('intval', $excludeClasses)));
        // --- End filter parsing ---

        $attendances = CourseClassAttendance::with(['courseClass.instructor', 'courseClass.course'])
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->when(count($excludedClassIds) > 0, fn($q) => $q->whereNotIn('course_class_id', $excludedClassIds))
            ->get();

        $instructorRows = Instructor::query()
            ->with('contracts')
            ->orderBy('full_name')
            ->get()
            ->map(function (Instructor $instructor) use ($attendances, $monthStart, $monthEnd) {
                $instructorAttendances = $attendances->filter(
                    fn (CourseClassAttendance $attendance) => $attendance->courseClass?->instructor_id === $instructor->id
                );

                // Group attendances by course_id
                $groupedByCourse = $instructorAttendances->groupBy(fn ($att) => $att->courseClass?->course_id ?? 0);

                $courses = [];
                $totalHours = 0.0;
                $totalSessions = 0;
                $totalEarnings = 0.0;

                foreach ($groupedByCourse as $courseId => $courseAttendances) {
                    $course = $courseAttendances->first()->courseClass?->course;
                    $hours = (float) $courseAttendances->sum('duration_hours');
                    $sessions = $courseAttendances->count();

                    $contract = $this->resolveContractForMonth($instructor, $monthStart, $monthEnd, $courseId);

                    $earnings = 0.0;
                    if ($contract) {
                        if ($contract->payment_type === 'hourly') {
                            $earnings = $hours * (float) $contract->hourly_rate;
                        }

                        if ($contract->payment_type === 'monthly_fixed' && $sessions > 0) {
                            $earnings = (float) $contract->monthly_amount;
                        }
                    }

                    $courses[] = [
                        'course' => $course,
                        'contract' => $contract,
                        'payment_type' => $contract?->payment_type,
                        'contract_value' => $contract?->payment_type === 'hourly'
                            ? (float) $contract?->hourly_rate
                            : (float) $contract?->monthly_amount,
                        'hours' => $hours,
                        'sessions' => $sessions,
                        'earnings' => round($earnings, 2),
                    ];

                    $totalHours += $hours;
                    $totalSessions += $sessions;
                    $totalEarnings += $earnings;
                }

                return [
                    'instructor' => $instructor,
                    'courses' => $courses,
                    'total_hours' => $totalHours,
                    'total_sessions' => $totalSessions,
                    'total_earnings' => round($totalEarnings, 2),
                ];
            });

        $instructorTotal = (float) $instructorRows->sum('total_earnings');

        $payments = InstructorPayment::whereBetween('reference_month', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->keyBy('instructor_id');

        $instructorRows = $instructorRows->map(function ($row) use ($payments) {
            $payment = $payments->get($row['instructor']->id);
            $row['payment'] = $payment;
            $row['payment_status'] = $payment && $payment->paid_at ? 'paid' : 'pending';
            $row['paid_amount'] = $payment ? (float) $payment->amount : 0;
            return $row;
        });

        $instructorPaidTotal = (float) $instructorRows->sum('paid_amount');
        $instructorPendingTotal = max($instructorTotal - $instructorPaidTotal, 0);

        $billingsForMonth = StudentBilling::with(['student', 'courseClass'])
            ->whereBetween('reference_month', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->when(count($excludedClassIds) > 0, fn($q) => $q->whereNotIn('course_class_id', $excludedClassIds))
            ->get();

        $billingTotal = (float) $billingsForMonth->sum('amount');
        $billingPaid = (float) $billingsForMonth->where('status', 'paid')->sum('amount');
        $billingPending = (float) $billingsForMonth->where('status', 'pending')->sum('amount');
        $billingOverdue = (float) $billingsForMonth
            ->where('status', 'pending')
            ->filter(fn (StudentBilling $billing) => $billing->due_date && $billing->due_date->isPast())
            ->sum('amount');
        $billingCanceled = (float) $billingsForMonth->where('status', 'canceled')->sum('amount');

        $classBilling = $billingsForMonth
            ->groupBy('course_class_id')
            ->map(function ($items) {
                /** @var \Illuminate\Support\Collection $items */
                $class = $items->first()?->courseClass;

                return [
                    'class_name' => $class?->name ?? __('Sem turma'),
                    'amount' => (float) $items->sum('amount'),
                    'paid' => (float) $items->where('status', 'paid')->sum('amount'),
                    'pending' => (float) $items->where('status', 'pending')->sum('amount'),
                ];
            })
            ->sortByDesc('amount')
            ->values();

        // --- Billing by course ---
        $courseBilling = $billingsForMonth
            ->groupBy(fn($b) => $b->courseClass?->course_id ?? 0)
            ->map(function ($billings, $courseId) {
                $course = $billings->first()->courseClass?->course;
                return [
                    'course_name' => $course?->title ?? __('Sem curso'),
                    'amount' => (float) $billings->sum('amount'),
                    'paid' => (float) $billings->where('status', 'paid')->sum('amount'),
                    'pending' => (float) $billings->where('status', 'pending')->sum('amount'),
                    'canceled' => (float) $billings->where('status', 'canceled')->sum('amount'),
                ];
            })
            ->sortByDesc('amount')
            ->values();
        // ---

        // Range totals and line graph
        $rangeStartInput = (string) $request->query('start', $monthInput);
        $rangeEndInput = (string) $request->query('end', $monthInput);

        try {
            $rangeStart = Carbon::createFromFormat('Y-m', $rangeStartInput)->startOfMonth();
            $rangeEnd = Carbon::createFromFormat('Y-m', $rangeEndInput)->endOfMonth();
        } catch (\Throwable $e) {
            $rangeStart = $referenceMonth->copy()->startOfMonth();
            $rangeEnd = $referenceMonth->copy()->endOfMonth();
        }

        $billingsForRange = StudentBilling::whereBetween('reference_month', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->when(count($excludedClassIds) > 0, fn($q) => $q->whereNotIn('course_class_id', $excludedClassIds))
            ->get();

        $rangeBillingTotal = (float) $billingsForRange->sum('amount');
        $rangeBillingPaid = (float) $billingsForRange->where('status', 'paid')->sum('amount');
        $rangeBillingPending = (float) $billingsForRange->where('status', 'pending')->sum('amount');
        $rangeBillingOverdue = (float) $billingsForRange
            ->where('status', 'pending')
            ->filter(fn (StudentBilling $billing) => $billing->due_date && $billing->due_date->isPast())
            ->sum('amount');

        $rangeInstructorTotal = $this->calculateInstructorTotalForPeriod($rangeStart, $rangeEnd, $excludedClassIds);

        $rangePayments = InstructorPayment::whereBetween('reference_month', [$rangeStart->toDateString(), $rangeEnd->toDateString()])->get();
        $rangeInstructorPaidTotal = (float) $rangePayments->where('paid_at', '!=', null)->sum('amount');
        $rangeInstructorPendingTotal = max($rangeInstructorTotal - $rangeInstructorPaidTotal, 0);

        $rangeLabels = [];
        $rangeBillingValues = [];
        $rangeInstructorValues = [];

        $cursor = $rangeStart->copy();
        while ($cursor->lte($rangeEnd)) {
            $s = $cursor->copy()->startOfMonth();
            $e = $cursor->copy()->endOfMonth();

            $rangeLabels[] = $cursor->translatedFormat('M/Y');
            $rangeBillingValues[] = (float) StudentBilling::whereBetween('reference_month', [$s->toDateString(), $e->toDateString()])
                ->when(count($excludedClassIds) > 0, fn($q) => $q->whereNotIn('course_class_id', $excludedClassIds))
                ->sum('amount');
            $rangeInstructorValues[] = $this->calculateInstructorTotalForPeriod($s, $e, $excludedClassIds);

            $cursor->addMonth();
        }

        $monthlyLabels = [];
        $monthlyBillingValues = [];
        $monthlyInstructorValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $cursor = now()->startOfMonth()->subMonths($i);
            $start = $cursor->copy()->startOfMonth();
            $end = $cursor->copy()->endOfMonth();

            $monthlyLabels[] = $cursor->translatedFormat('M/Y');
            $monthlyBillingValues[] = (float) StudentBilling::whereBetween('reference_month', [$start->toDateString(), $end->toDateString()])
                ->when(count($excludedClassIds) > 0, fn($q) => $q->whereNotIn('course_class_id', $excludedClassIds))
                ->sum('amount');
            $monthlyInstructorValues[] = $this->calculateInstructorTotalForPeriod($start, $end, $excludedClassIds);
        }

        // --- Future projection (linear regression on last 6 months) ---
        $x = [0, 1, 2, 3, 4, 5];
        $n = 6;
        $sumX = 15;
        $sumX2 = 55;
        $denom = $n * $sumX2 - $sumX * $sumX;

        $sumY = array_sum($monthlyBillingValues);
        $sumXY = 0;
        for ($i = 0; $i < $n; $i++) $sumXY += $x[$i] * $monthlyBillingValues[$i];
        $bSlope = $denom ? ($n * $sumXY - $sumX * $sumY) / $denom : 0;
        $bIntercept = $n ? ($sumY - $bSlope * $sumX) / $n : 0;

        $sumY = array_sum($monthlyInstructorValues);
        $sumXY = 0;
        for ($i = 0; $i < $n; $i++) $sumXY += $x[$i] * $monthlyInstructorValues[$i];
        $iSlope = $denom ? ($n * $sumXY - $sumX * $sumY) / $denom : 0;
        $iIntercept = $n ? ($sumY - $iSlope * $sumX) / $n : 0;

        $projectLabels = [];
        $projectBillingValues = [];
        $projectInstructorValues = [];
        for ($i = 1; $i <= 3; $i++) {
            $m = now()->startOfMonth()->addMonths($i);
            $projectLabels[] = $m->translatedFormat('M/Y');
            $projectBillingValues[] = round(max(0, $bIntercept + $bSlope * (5 + $i)), 2);
            $projectInstructorValues[] = round(max(0, $iIntercept + $iSlope * (5 + $i)), 2);
        }
        // ---

        // --- Heatmap daily data ---
        $dailySessions = CourseClassAttendance::whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->when(count($excludedClassIds) > 0, fn($q) => $q->whereNotIn('course_class_id', $excludedClassIds))
            ->selectRaw('attendance_date, COUNT(*) as session_count, SUM(duration_hours) as total_hours')
            ->groupBy('attendance_date')
            ->get()
            ->keyBy(fn($item) => $item->attendance_date instanceof \Carbon\Carbon ? $item->attendance_date->toDateString() : $item->attendance_date);

        $dailyDueAmounts = StudentBilling::whereBetween('reference_month', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereNotNull('due_date')
            ->when(count($excludedClassIds) > 0, fn($q) => $q->whereNotIn('course_class_id', $excludedClassIds))
            ->selectRaw('due_date, SUM(amount) as total, COUNT(*) as bill_count')
            ->groupBy('due_date')
            ->get()
            ->keyBy(fn($item) => $item->due_date->toDateString());

        $dailyPaidAmounts = StudentBilling::whereBetween('reference_month', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->when(count($excludedClassIds) > 0, fn($q) => $q->whereNotIn('course_class_id', $excludedClassIds))
            ->selectRaw('DATE(paid_at) as paid_date, SUM(amount) as total')
            ->groupBy('paid_date')
            ->get()
            ->keyBy('paid_date');

        $daysInMonth = $referenceMonth->daysInMonth;
        $heatmapDays = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $referenceMonth->copy()->day($day);
            $dateStr = $date->toDateString();
            $heatmapDays[] = [
                'day' => $day,
                'weekday' => $date->dayOfWeek,
                'session_count' => (int) ($dailySessions[$dateStr]->session_count ?? 0),
                'session_hours' => (float) ($dailySessions[$dateStr]->total_hours ?? 0),
                'billing_due' => (float) ($dailyDueAmounts[$dateStr]->total ?? 0),
                'bill_count' => (int) ($dailyDueAmounts[$dateStr]->bill_count ?? 0),
                'paid_amount' => (float) ($dailyPaidAmounts[$dateStr]->total ?? 0),
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'is_future' => $date->isFuture(),
            ];
        }

        // --- Distribution data ---
        $billingStatusAmount = [
            'paid' => $billingPaid,
            'pending_within' => max($billingPending - $billingOverdue, 0),
            'overdue' => $billingOverdue,
            'canceled' => $billingCanceled,
        ];

        // --- Filter lists ---
        $courses = Course::orderBy('title')->get();
        $allClasses = CourseClass::with('course')->orderBy('name')->get();

        return view('financial.reports', [
            'referenceMonth' => $referenceMonth,
            'instructorRows' => $instructorRows,
            'instructorTotal' => $instructorTotal,
            'instructorPaidTotal' => $instructorPaidTotal,
            'instructorPendingTotal' => $instructorPendingTotal,
            'billingTotal' => $billingTotal,
            'billingPaid' => $billingPaid,
            'billingPending' => $billingPending,
            'billingOverdue' => $billingOverdue,
            'classBilling' => $classBilling,
            'monthlyLabels' => $monthlyLabels,
            'monthlyBillingValues' => $monthlyBillingValues,
            'monthlyInstructorValues' => $monthlyInstructorValues,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'rangeBillingTotal' => $rangeBillingTotal,
            'rangeBillingPaid' => $rangeBillingPaid,
            'rangeBillingPending' => $rangeBillingPending,
            'rangeBillingOverdue' => $rangeBillingOverdue,
            'rangeInstructorTotal' => $rangeInstructorTotal,
            'rangeInstructorPaidTotal' => $rangeInstructorPaidTotal,
            'rangeInstructorPendingTotal' => $rangeInstructorPendingTotal,
            'rangeLabels' => $rangeLabels,
            'rangeBillingValues' => $rangeBillingValues,
            'rangeInstructorValues' => $rangeInstructorValues,
            'billingCanceled' => $billingCanceled,
            'courseBilling' => $courseBilling,
            'heatmapDays' => $heatmapDays,
            'billingStatusAmount' => $billingStatusAmount,
            'projectLabels' => $projectLabels,
            'projectBillingValues' => $projectBillingValues,
            'projectInstructorValues' => $projectInstructorValues,
            'courses' => $courses,
            'allClasses' => $allClasses,
            'excludeCourses' => $excludeCourses,
            'excludeClasses' => $excludeClasses,
        ]);
    }

    public function saveInstructorContract(Request $request, Instructor $instructor)
    {
        $validated = $request->validate([
            'contract_id' => ['nullable', 'integer', 'exists:instructor_contracts,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'payment_type' => ['required', 'in:hourly,monthly_fixed'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'monthly_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'month' => ['nullable', 'string'],
        ]);

        $contract = null;
        if (! empty($validated['contract_id'])) {
            $contract = InstructorContract::query()
                ->where('id', $validated['contract_id'])
                ->where('instructor_id', $instructor->id)
                ->first();
        }

        if (! $contract) {
            $contract = new InstructorContract();
            $contract->instructor_id = $instructor->id;
        }

        $contract->course_id = $validated['course_id'] ?? null;
        $contract->payment_type = $validated['payment_type'];
        $contract->hourly_rate = $validated['payment_type'] === 'hourly'
            ? ($validated['hourly_rate'] ?? 0)
            : null;
        $contract->monthly_amount = $validated['payment_type'] === 'monthly_fixed'
            ? ($validated['monthly_amount'] ?? 0)
            : null;
        $contract->starts_at = $validated['starts_at'];
        $contract->ends_at = $validated['ends_at'] ?? null;
        $contract->active = (bool) ($validated['active'] ?? true);
        $contract->notes = $validated['notes'] ?? null;
        $contract->save();

        $month = $validated['month'] ?? now()->format('Y-m');

        return redirect()
            ->route('financial.reports', ['month' => $month])
            ->with('status', __('Contrato do instrutor salvo com sucesso.'));
    }

    private function resolveContractForMonth(Instructor $instructor, Carbon $monthStart, Carbon $monthEnd, ?int $courseId = null): ?InstructorContract
    {
        return $instructor->contracts
            ->first(function (InstructorContract $contract) use ($monthStart, $monthEnd, $courseId) {
                if (! $contract->active) {
                    return false;
                }

                // Match by course_id: if contract has course_id, it must match; if null, it's a fallback
                if ($courseId !== null && $contract->course_id !== null && $contract->course_id !== $courseId) {
                    return false;
                }

                $startsAt = $contract->starts_at;
                $endsAt = $contract->ends_at;

                return $startsAt
                    && $startsAt->lte($monthEnd)
                    && (! $endsAt || $endsAt->gte($monthStart));
            });
    }

    private function calculateInstructorTotalForPeriod(Carbon $periodStart, Carbon $periodEnd, array $excludedClassIds = []): float
    {
        $attendances = CourseClassAttendance::with(['courseClass.instructor.contracts', 'courseClass.course'])
            ->whereBetween('attendance_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->when(count($excludedClassIds) > 0, fn($q) => $q->whereNotIn('course_class_id', $excludedClassIds))
            ->get();

        $instructors = $attendances
            ->pluck('courseClass.instructor')
            ->filter()
            ->unique('id');

        $sum = 0.0;

        foreach ($instructors as $instructor) {
            $instructorAttendances = $attendances->filter(
                fn (CourseClassAttendance $attendance) => $attendance->courseClass?->instructor_id === $instructor->id
            );

            // Group by course
            $groupedByCourse = $instructorAttendances->groupBy(fn ($att) => $att->courseClass?->course_id ?? 0);

            foreach ($groupedByCourse as $courseId => $courseAttendances) {
                $hours = (float) $courseAttendances->sum('duration_hours');
                $sessions = $courseAttendances->count();
                $contract = $this->resolveContractForMonth($instructor, $periodStart, $periodEnd, $courseId);

                if (! $contract) {
                    continue;
                }

                if ($contract->payment_type === 'hourly') {
                    $sum += $hours * (float) $contract->hourly_rate;
                    continue;
                }

                if ($contract->payment_type === 'monthly_fixed' && $sessions > 0) {
                    $sum += (float) $contract->monthly_amount;
                }
            }
        }

        return round($sum, 2);
    }

    public function savePayment(Request $request, Instructor $instructor)
    {
        if ($request->has('remove')) {
            $referenceMonth = $request->input('reference_month', now()->format('Y-m'));
            try {
                $monthDate = Carbon::createFromFormat('Y-m', $referenceMonth)->startOfMonth();
            } catch (\Throwable $e) {
                $monthDate = now()->startOfMonth();
            }

            InstructorPayment::where('instructor_id', $instructor->id)
                ->where('reference_month', $monthDate->toDateString())
                ->delete();

            return redirect()
                ->route('financial.reports', ['month' => $referenceMonth])
                ->with('status', __('Pagamento do instrutor removido.'));
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'reference_month' => ['required', 'string'],
            'paid' => ['nullable', 'boolean'],
        ]);

        $referenceMonth = $validated['reference_month'];

        try {
            $monthDate = Carbon::createFromFormat('Y-m', $referenceMonth)->startOfMonth();
        } catch (\Throwable $e) {
            $monthDate = now()->startOfMonth();
        }

        $amount = (float) $validated['amount'];

        if ($amount <= 0 && ! $request->has('paid')) {
            InstructorPayment::where('instructor_id', $instructor->id)
                ->where('reference_month', $monthDate->toDateString())
                ->delete();

            return redirect()
                ->route('financial.reports', ['month' => $referenceMonth])
                ->with('status', __('Registro de pagamento removido (valor zerado).'));
        }

        $payment = InstructorPayment::firstOrNew([
            'instructor_id' => $instructor->id,
            'reference_month' => $monthDate->toDateString(),
        ]);

        $payment->amount = $amount;
        $payment->paid_at = ! empty($validated['paid']) ? now() : null;
        $payment->save();

        return redirect()
            ->route('financial.reports', ['month' => $referenceMonth])
            ->with('status', $payment->paid_at
                ? __('Pagamento do instrutor registrado como pago.')
                : __('Registro de pagamento do instrutor atualizado.'));
    }
}
