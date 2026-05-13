<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-300">{{ __('Financeiro') }}</p>
                <h2 class="mt-1 text-3xl font-semibold text-white">{{ __('Relatórios de ganhos e faturamento') }}</h2>
            </div>
            <form method="GET" action="{{ route('financial.reports') }}" class="flex items-center gap-2">
                <label for="month" class="text-sm text-slate-200">{{ __('Mês') }}</label>
                <input id="month" type="month" name="month" value="{{ $referenceMonth->format('Y-m') }}" class="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <button class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Filtrar') }}</button>
            </form>
        </div>
    </x-slot>

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(34,211,238,0.12),_transparent_30%),linear-gradient(180deg,_#f8fbff_0%,_#eef4ff_48%,_#f8fafc_100%)] py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ __('Não foi possível salvar o contrato. Verifique os campos e tente novamente.') }}
                </div>
            @endif

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-white/70 bg-white/90 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('Total faturado') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">R$ {{ number_format($billingTotal, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-white/70 bg-white/90 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('Total recebido') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-700">R$ {{ number_format($billingPaid, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-white/70 bg-white/90 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('Pendente') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-700">R$ {{ number_format($billingPending, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-white/70 bg-white/90 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('A pagar instrutores') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-rose-700">R$ {{ number_format($instructorTotal, 2, ',', '.') }}</p>
                </article>
            </section>

            <section class="rounded-3xl border border-slate-900/10 bg-slate-950 p-6">
                <h3 class="text-xl font-semibold text-white">{{ __('Evolução mensal (6 meses)') }}</h3>
                <div class="mt-4 h-[300px]">
                    <canvas id="moneyFlowChart"></canvas>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <article class="rounded-3xl border border-white/70 bg-white/90 p-6">
                    <h3 class="text-xl font-semibold text-slate-900">{{ __('Ganhos por instrutor (base: presença)') }}</h3>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('Instrutor') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Contrato') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Horas') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Aulas') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Ganho') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Editar contrato') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($instructorRows as $row)
                                    @php
                                        $contract = $row['contract'];
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $row['instructor']->full_name }}</td>
                                        <td class="px-4 py-3 text-slate-600">
                                            @if ($row['payment_type'] === 'hourly')
                                                {{ __('Hora') }} (R$ {{ number_format($row['contract_value'], 2, ',', '.') }}/h)
                                            @elseif ($row['payment_type'] === 'monthly_fixed')
                                                {{ __('Fixo mensal') }} (R$ {{ number_format($row['contract_value'], 2, ',', '.') }})
                                            @else
                                                {{ __('Sem contrato') }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ number_format($row['hours'], 2, ',', '.') }}h</td>
                                        <td class="px-4 py-3 text-slate-600">{{ number_format($row['sessions'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 font-semibold text-slate-900">R$ {{ number_format($row['earnings'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ route('financial.reports.instructor-contract.save', $row['instructor']) }}" class="grid min-w-[340px] gap-2">
                                                @csrf
                                                <input type="hidden" name="month" value="{{ $referenceMonth->format('Y-m') }}">
                                                <input type="hidden" name="contract_id" value="{{ $contract?->id }}">

                                                <div class="grid grid-cols-2 gap-2">
                                                    <select name="payment_type" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                                        <option value="hourly" @selected(($contract?->payment_type ?? 'hourly') === 'hourly')>{{ __('Hora') }}</option>
                                                        <option value="monthly_fixed" @selected(($contract?->payment_type ?? '') === 'monthly_fixed')>{{ __('Fixo mensal') }}</option>
                                                    </select>
                                                    <select name="active" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                                        <option value="1" @selected(($contract?->active ?? true) === true)>{{ __('Ativo') }}</option>
                                                        <option value="0" @selected(($contract?->active ?? true) === false)>{{ __('Inativo') }}</option>
                                                    </select>
                                                </div>

                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="number" step="0.01" min="0" name="hourly_rate" value="{{ old('hourly_rate', $contract?->hourly_rate) }}" placeholder="{{ __('Valor/hora') }}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                                    <input type="number" step="0.01" min="0" name="monthly_amount" value="{{ old('monthly_amount', $contract?->monthly_amount) }}" placeholder="{{ __('Valor mensal') }}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                                </div>

                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="date" name="starts_at" value="{{ old('starts_at', optional($contract?->starts_at)->format('Y-m-d') ?? $referenceMonth->copy()->startOfMonth()->format('Y-m-d')) }}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                                    <input type="date" name="ends_at" value="{{ old('ends_at', optional($contract?->ends_at)->format('Y-m-d')) }}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                                </div>

                                                <input type="text" name="notes" value="{{ old('notes', $contract?->notes) }}" placeholder="{{ __('Observações (opcional)') }}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">

                                                <button class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">
                                                    {{ __('Salvar contrato') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">{{ __('Sem dados de instrutores para o período.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="rounded-3xl border border-white/70 bg-white/90 p-6">
                    <h3 class="text-xl font-semibold text-slate-900">{{ __('Dinheiro por turma') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Origem: cobrança dos alunos no mês selecionado.') }}</p>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('Turma') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Total') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Pago') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Pendente') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($classBilling as $row)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $row['class_name'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">R$ {{ number_format($row['amount'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-emerald-700">R$ {{ number_format($row['paid'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-amber-700">R$ {{ number_format($row['pending'], 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">{{ __('Sem cobranças para o período.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ __('Cobranças vencidas e pendentes: R$ :value', ['value' => number_format($billingOverdue, 2, ',', '.')]) }}
                    </div>
                </article>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = @json($monthlyLabels);
        const billing = @json($monthlyBillingValues);
        const instructors = @json($monthlyInstructorValues);

        new Chart(document.getElementById('moneyFlowChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: @json(__('Faturamento alunos')),
                        data: billing,
                        backgroundColor: 'rgba(16, 185, 129, 0.72)',
                        borderRadius: 10,
                    },
                    {
                        label: @json(__('Custo instrutores')),
                        data: instructors,
                        backgroundColor: 'rgba(244, 63, 94, 0.72)',
                        borderRadius: 10,
                    }
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#e2e8f0' } } },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148, 163, 184, 0.12)' } }
                }
            }
        });
    </script>
</x-app-layout>
