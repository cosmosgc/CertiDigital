<?php

namespace App\Http\Controllers;

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

        $attendances = CourseClassAttendance::with(['courseClass.instructor'])
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $instructorRows = Instructor::query()
            ->with('contracts')
            ->orderBy('full_name')
            ->get()
            ->map(function (Instructor $instructor) use ($attendances, $monthStart, $monthEnd) {
                $instructorAttendances = $attendances->filter(
                    fn (CourseClassAttendance $attendance) => $attendance->courseClass?->instructor_id === $instructor->id
                );

                $hours = (float) $instructorAttendances->sum('duration_hours');
                $sessions = $instructorAttendances->count();

                $contract = $this->resolveContractForMonth($instructor, $monthStart, $monthEnd);

                $earnings = 0.0;
                if ($contract) {
                    if ($contract->payment_type === 'hourly') {
                        $earnings = $hours * (float) $contract->hourly_rate;
                    }

                    if ($contract->payment_type === 'monthly_fixed' && $sessions > 0) {
                        $earnings = (float) $contract->monthly_amount;
                    }
                }

                return [
                    'instructor' => $instructor,
                    'contract' => $contract,
                    'payment_type' => $contract?->payment_type,
                    'contract_value' => $contract?->payment_type === 'hourly'
                        ? (float) $contract?->hourly_rate
                        : (float) $contract?->monthly_amount,
                    'hours' => $hours,
                    'sessions' => $sessions,
                    'earnings' => round($earnings, 2),
                ];
            });

		$instructorTotal = (float) $instructorRows->sum('earnings');

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
            ->get();

        $billingTotal = (float) $billingsForMonth->sum('amount');
        $billingPaid = (float) $billingsForMonth->where('status', 'paid')->sum('amount');
        $billingPending = (float) $billingsForMonth->where('status', 'pending')->sum('amount');
        $billingOverdue = (float) $billingsForMonth
            ->where('status', 'pending')
            ->filter(fn (StudentBilling $billing) => $billing->due_date && $billing->due_date->isPast())
            ->sum('amount');

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

        $billingsForRange = StudentBilling::whereBetween('reference_month', [$rangeStart->toDateString(), $rangeEnd->toDateString()])->get();

        $rangeBillingTotal = (float) $billingsForRange->sum('amount');
        $rangeBillingPaid = (float) $billingsForRange->where('status', 'paid')->sum('amount');
        $rangeBillingPending = (float) $billingsForRange->where('status', 'pending')->sum('amount');
        $rangeBillingOverdue = (float) $billingsForRange
            ->where('status', 'pending')
            ->filter(fn (StudentBilling $billing) => $billing->due_date && $billing->due_date->isPast())
            ->sum('amount');

		$rangeInstructorTotal = $this->calculateInstructorTotalForPeriod($rangeStart, $rangeEnd);

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
            $rangeBillingValues[] = (float) StudentBilling::whereBetween('reference_month', [$s->toDateString(), $e->toDateString()])->sum('amount');
            $rangeInstructorValues[] = $this->calculateInstructorTotalForPeriod($s, $e);

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
            $monthlyBillingValues[] = (float) StudentBilling::whereBetween('reference_month', [$start->toDateString(), $end->toDateString()])->sum('amount');
            $monthlyInstructorValues[] = $this->calculateInstructorTotalForPeriod($start, $end);
        }

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
        ]);
    }

    public function saveInstructorContract(Request $request, Instructor $instructor)
    {
        $validated = $request->validate([
            'contract_id' => ['nullable', 'integer', 'exists:instructor_contracts,id'],
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

    private function resolveContractForMonth(Instructor $instructor, Carbon $monthStart, Carbon $monthEnd): ?InstructorContract
    {
        return $instructor->contracts
            ->first(function (InstructorContract $contract) use ($monthStart, $monthEnd) {
                if (! $contract->active) {
                    return false;
                }

                $startsAt = $contract->starts_at;
                $endsAt = $contract->ends_at;

                return $startsAt
                    && $startsAt->lte($monthEnd)
                    && (! $endsAt || $endsAt->gte($monthStart));
            });
    }

    private function calculateInstructorTotalForPeriod(Carbon $periodStart, Carbon $periodEnd): float
    {
        $attendances = CourseClassAttendance::with(['courseClass.instructor.contracts'])
            ->whereBetween('attendance_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
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

            $hours = (float) $instructorAttendances->sum('duration_hours');
            $sessions = $instructorAttendances->count();
            $contract = $this->resolveContractForMonth($instructor, $periodStart, $periodEnd);

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
