<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-300">{{ __('Financeiro') }}</p>
                <h2 class="mt-1 text-3xl font-semibold text-white">{{ __('Relatórios de ganhos e faturamento') }}</h2>
            </div>
            <form method="GET" action="{{ route('financial.reports') }}" class="flex flex-wrap items-end gap-x-4 gap-y-2">
                <div>
                    <label for="month" class="block text-xs text-slate-300">{{ __('Mês') }}</label>
                    <input id="month" type="month" name="month" value="{{ $referenceMonth->format('Y-m') }}" class="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label for="start" class="block text-xs text-slate-300">{{ __('Início') }}</label>
                    <input id="start" type="month" name="start" value="{{ $rangeStart->format('Y-m') }}" class="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label for="end" class="block text-xs text-slate-300">{{ __('Fim') }}</label>
                    <input id="end" type="month" name="end" value="{{ $rangeEnd->format('Y-m') }}" class="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <button class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Filtrar') }}</button>
                <button type="button" onclick="window.print()"
                    class="rounded-xl border border-white/30 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20">
                    {{ __('Imprimir') }}
                </button>
            </form>
        </div>
    </x-slot>

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(34,211,238,0.12),_transparent_30%),linear-gradient(180deg,_#f8fbff_0%,_#eef4ff_48%,_#f8fafc_100%)] py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="no-print rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="no-print rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ __('Não foi possível salvar o contrato. Verifique os campos e tente novamente.') }}
                </div>
            @endif

            {{-- ====== Advanced Filters ====== --}}
            <details class="no-print group rounded-2xl border border-white/70 bg-white/90 p-4 transition-colors hover:border-slate-300">
                <summary class="flex cursor-pointer items-center justify-between text-sm font-medium text-slate-700">
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        {{ __('Filtros avançados') }}
                        @if (count($excludeCourses) > 0 || count($excludeClasses) > 0)
                            <span class="rounded-full bg-cyan-100 px-2 py-0.5 text-xs font-medium text-cyan-700">{{ __('Filtros ativos') }}</span>
                        @endif
                    </span>
                    <svg class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="mt-4 border-t border-slate-200 pt-4">
                    <form method="GET" action="{{ route('financial.reports') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <input type="hidden" name="month" value="{{ $referenceMonth->format('Y-m') }}">
                        <input type="hidden" name="start" value="{{ $rangeStart->format('Y-m') }}">
                        <input type="hidden" name="end" value="{{ $rangeEnd->format('Y-m') }}">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Excluir cursos') }}</label>
                            <select name="exclude_courses[]" multiple class="h-32 w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}" @selected(in_array((string)$course->id, $excludeCourses, true))>{{ $course->title }}</option>
                                @endforeach
                            </select>
                            <p class="mt-0.5 text-[10px] text-slate-400">{{ __('Ctrl+clique para múltiplos') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Excluir turmas') }}</label>
                            <select name="exclude_classes[]" multiple class="h-32 w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                                @foreach ($allClasses as $class)
                                    <option value="{{ $class->id }}" @selected(in_array((string)$class->id, $excludeClasses, true))>{{ $class->name }} @if($class->course) ({{ $class->course->title }}) @endif</option>
                                @endforeach
                            </select>
                            <p class="mt-0.5 text-[10px] text-slate-400">{{ __('Ctrl+clique para múltiplos') }}</p>
                        </div>
                        <div class="flex items-end gap-2">
                            <button class="rounded-lg bg-cyan-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Aplicar') }}</button>
                            <a href="{{ route('financial.reports', ['month' => $referenceMonth->format('Y-m'), 'start' => $rangeStart->format('Y-m'), 'end' => $rangeEnd->format('Y-m')]) }}"
                               class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-medium text-slate-600">{{ __('Limpar') }}</a>
                        </div>
                    </form>
                </div>
            </details>

            {{-- ====== KPI Cards ====== --}}
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <article class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-slate-400">{{ __('Faturado') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-slate-900">R$ {{ number_format($billingTotal, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-slate-400">{{ __('Recebido') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-emerald-700">R$ {{ number_format($billingPaid, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-slate-400">{{ __('Pendente') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-amber-700">R$ {{ number_format($billingPending, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-slate-400">{{ __('Vencido') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-rose-700">R$ {{ number_format($billingOverdue, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-slate-400">{{ __('Custo instrut.') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-violet-700">R$ {{ number_format($instructorTotal, 2, ',', '.') }}</p>
                </article>
            </div>

            {{-- ====== Mini Calendar + Heatmap ====== --}}
            @php
                $daysInMonth = $referenceMonth->daysInMonth;
                $firstDow = $referenceMonth->copy()->startOfMonth()->dayOfWeek;
                $maxPaid = max(array_column($heatmapDays, 'paid_amount') ?: [1]);
                $maxBilling = max(array_column($heatmapDays, 'billing_due') ?: [1]);
                $maxSessions = max(array_column($heatmapDays, 'session_count') ?: [1]);
            @endphp
            <div class="grid gap-4 lg:grid-cols-5">
                {{-- Mini Calendar --}}
                <div class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">{{ $referenceMonth->translatedFormat('F \d\e Y') }}</h4>
                        <div class="flex gap-1">
                            <a href="{{ route('financial.reports', array_merge(request()->query(), ['month' => $referenceMonth->copy()->subMonth()->format('Y-m')])) }}" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">&larr;</a>
                            <a href="{{ route('financial.reports', array_merge(request()->query(), ['month' => $referenceMonth->copy()->addMonth()->format('Y-m')])) }}" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">&rarr;</a>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-px text-center text-xs">
                        @foreach (['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $dow)
                            <div class="py-1 text-[10px] font-semibold uppercase text-slate-400">{{ $dow }}</div>
                        @endforeach
                        @for ($i = 0; $i < $firstDow; $i++)
                            <div></div>
                        @endfor
                         @foreach ($heatmapDays as $dayData)
                             @php
                                 $hasSession = $dayData['session_count'] > 0;
                                 $hasFinancial = $dayData['paid_amount'] > 0 || $dayData['billing_due'] > 0;
                                 $bgClass = '';
                                 if ($dayData['is_future']) {
                                     $bgClass = 'text-slate-300';
                                 } elseif ($hasSession) {
                                     $bgClass = 'bg-cyan-200 text-cyan-900 font-semibold';
                                 } elseif ($hasFinancial) {
                                     $bgClass = 'bg-emerald-100 text-emerald-800 font-medium';
                                 } else {
                                     $bgClass = 'text-slate-600';
                                 }
                                 $todayClass = $dayData['is_today'] ? 'ring-2 ring-cyan-500' : '';
                             @endphp
                             <div class="relative rounded py-1.5 text-center text-xs {{ $bgClass }} {{ $todayClass }}">
                                 {{ $dayData['day'] }}
                                 @if ($hasSession)
                                     <span class="absolute bottom-0.5 left-1/2 block h-1 w-1 -translate-x-1/2 rounded-full bg-cyan-700"></span>
                                 @endif
                             </div>
                        @endforeach
                    </div>
                    <div class="mt-2 flex justify-center gap-3 text-[10px] text-slate-400">
                        <span>&#9679; {{ __('aula') }}</span>
                        <span class="inline-block h-2 w-2 rounded-full bg-cyan-500"></span>
                    </div>
                </div>

                {{-- Heatmap Table --}}
                <div class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm lg:col-span-3">
                    @php
                        $diasComAulas = count(array_filter($heatmapDays, fn($d) => $d['session_count'] > 0));
                        $diasComFinanceiro = count(array_filter($heatmapDays, fn($d) => $d['paid_amount'] > 0 || $d['billing_due'] > 0));
                        $totalAulas = array_sum(array_column($heatmapDays, 'session_count'));
                        $totalHoras = array_sum(array_column($heatmapDays, 'session_hours'));
                        $totalBilling = array_sum(array_column($heatmapDays, 'billing_due'));
                        $totalPaid = array_sum(array_column($heatmapDays, 'paid_amount'));
                    @endphp
                    <details class="group" open>
                        <summary class="flex cursor-pointer items-center justify-between text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                            <span>
                                {{ __('Detalhamento diário') }}
                                @if ($diasComAulas > 0 || $diasComFinanceiro > 0)
                                    <span class="ml-2 text-[9px] font-normal text-slate-400 lowercase">
                                        {{ $diasComAulas > 0 ? $diasComAulas . ' ' . __('dias com aulas') : '' }}{{ $diasComAulas > 0 && $diasComFinanceiro > 0 ? ', ' : '' }}{{ $diasComFinanceiro > 0 ? $diasComFinanceiro . ' ' . __('com financeiro') : '' }}
                                    </span>
                                @endif
                            </span>
                            <svg class="h-3 w-3 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </summary>
                        <div class="mt-2">
                            {{-- Day headers --}}
                            <div class="grid grid-cols-7 gap-px text-center text-[9px] uppercase tracking-wider text-slate-400">
                                @foreach (['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $dow)
                                    <div class="py-1 font-medium">{{ $dow }}</div>
                                @endforeach
                            </div>
                            {{-- Day grid --}}
                            <div class="grid grid-cols-7 gap-px text-[9px]">
                                @for ($i = 0; $i < $firstDow; $i++)
                                    <div></div>
                                @endfor
                                @foreach ($heatmapDays as $dayData)
                                    @php
                                        $hasSession = $dayData['session_count'] > 0;
                                        $hasBilling = $dayData['billing_due'] > 0;
                                        $hasPaid = $dayData['paid_amount'] > 0;
                                        $cellBg = $dayData['is_today'] ? 'bg-cyan-50' : ($dayData['is_weekend'] ? 'bg-slate-50/50' : '');
                                        $cellBorder = $dayData['is_today'] ? 'ring-1 ring-cyan-300' : '';
                                    @endphp
                                    <div class="min-h-[52px] rounded border border-slate-200 p-0.5 {{ $cellBg }} {{ $cellBorder }}">
                                        <div class="text-right font-semibold text-slate-600 leading-tight">{{ $dayData['day'] }}</div>
                                        @if ($hasSession)
                                            <div class="text-[8px] leading-tight text-cyan-700">{{ $dayData['session_count'] }}a {{ number_format($dayData['session_hours'], 1, ',', '.') }}h</div>
                                        @endif
                                        @if ($hasBilling)
                                            <div class="text-[8px] leading-tight text-amber-700 truncate">R$ {{ number_format($dayData['billing_due'], 0, ',', '.') }}</div>
                                        @endif
                                        @if ($hasPaid)
                                            <div class="text-[8px] leading-tight text-emerald-700 truncate">R$ {{ number_format($dayData['paid_amount'], 0, ',', '.') }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            {{-- Total bar --}}
                            <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-0.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-[10px] font-semibold text-slate-800">
                                <span>{{ __('Total') }}:</span>
                                <span class="text-cyan-700">{{ $totalAulas }} {{ __('aulas') }} / {{ number_format($totalHoras, 1, ',', '.') }}h</span>
                                @if ($totalBilling > 0)
                                    <span class="text-amber-700">{{ __('Faturado') }}: R$ {{ number_format($totalBilling, 2, ',', '.') }}</span>
                                @endif
                                @if ($totalPaid > 0)
                                    <span class="text-emerald-700">{{ __('Recebido') }}: R$ {{ number_format($totalPaid, 2, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                    </details>
                </div>
            </div>

            {{-- ====== Daily comparison across months (aulas + horas) ====== --}}
            <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ __('Detalhamento diário comparativo — aulas e horas') }}</h3>
                        <p class="mt-0.5 text-xs text-slate-500">{{ __('Dia a dia por mês (mesmos meses do comparativo). Diferenças vs. mês anterior.') }}</p>
                    </div>
                </div>

                {{-- Totals per month with differences --}}
                <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-{{ min(count($dailyCompareMonths), 4) }}">
                    @foreach ($dailyCompareMonths as $idx => $dc)
                        <article class="rounded-2xl border {{ $dc['is_reference'] ? 'border-cyan-300 bg-cyan-50/70' : 'border-slate-200 bg-slate-50/70' }} p-3">
                            <p class="text-xs font-semibold text-slate-700">
                                {{ $dc['full_label'] }}
                                @if ($dc['is_reference'])
                                    <span class="ml-1 rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-semibold text-cyan-700">{{ __('ref') }}</span>
                                @endif
                            </p>
                            <p class="mt-1 text-sm tabular-nums text-slate-800">
                                <span class="font-bold text-cyan-700">{{ $dc['total_sessions'] }}</span> {{ __('aulas') }}
                                •
                                <span class="font-bold text-violet-700">{{ number_format($dc['total_hours'], 1, ',', '.') }}h</span>
                            </p>
                            @if ($idx === 0 || $dc['diff_sessions'] === null)
                                <p class="mt-0.5 text-[11px] text-slate-400">— {{ __('primeiro mês da comparação') }}</p>
                            @else
                                <p class="mt-0.5 text-[11px] font-semibold tabular-nums">
                                    <span class="{{ $dc['diff_sessions'] > 0 ? 'text-emerald-600' : ($dc['diff_sessions'] < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                                        {{ $dc['diff_sessions'] > 0 ? '+' : '' }}{{ $dc['diff_sessions'] }} {{ __('aulas') }}
                                    </span>
                                    <span class="text-slate-300">|</span>
                                    <span class="{{ $dc['diff_hours'] > 0.005 ? 'text-emerald-600' : ($dc['diff_hours'] < -0.005 ? 'text-rose-600' : 'text-slate-400') }}">
                                        {{ $dc['diff_hours'] > 0 ? '+' : '' }}{{ number_format($dc['diff_hours'], 1, ',', '.') }}h
                                    </span>
                                    <span class="font-normal text-slate-400">{{ __('vs. anterior') }}</span>
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>

                {{-- Weekday table (per month: aulas + horas by day of week) --}}
                <h4 class="mt-4 text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">{{ __('Por dia da semana — aulas e horas') }}</h4>
                <div class="mt-2 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="sticky left-0 bg-slate-100 px-3 py-2 text-left font-semibold text-slate-700">{{ __('Dia da semana') }}</th>
                                @foreach ($dailyCompareMonths as $dc)
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">
                                        {{ $dc['label'] }}
                                        @if ($dc['is_reference'])
                                            <span class="ml-0.5 rounded-full bg-cyan-100 px-1.5 py-px text-[9px] text-cyan-700">*</span>
                                        @endif
                                        <span class="block text-[9px] font-normal text-slate-400">{{ __('aulas / horas') }}</span>
                                    </th>
                                @endforeach
                                @if (count($dailyCompareMonths) > 1)
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">
                                        {{ __('Δ (último − anterior)') }}
                                        <span class="block text-[9px] font-normal text-slate-400">{{ __('aulas / horas') }}</span>
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ([1, 2, 3, 4, 5, 6, 0] as $wd)
                                @php $wdNames = [0 => __('Dom'), 1 => __('Seg'), 2 => __('Ter'), 3 => __('Qua'), 4 => __('Qui'), 5 => __('Sex'), 6 => __('Sáb')]; @endphp
                                <tr class="hover:bg-slate-50/50 {{ $wd >= 6 || $wd === 0 ? 'bg-slate-50/50' : '' }}">
                                    <td class="sticky left-0 bg-white px-3 py-1.5 font-semibold text-slate-700">{{ $wdNames[$wd] }}</td>
                                    @foreach ($dailyCompareMonths as $dc)
                                        @php
                                            $wcell = $dc['weekdays'][$wd] ?? ['sessions' => 0, 'hours' => 0];
                                            $occ = $dc['weekday_counts'][$wd] ?? 0;
                                        @endphp
                                        <td class="px-3 py-1.5 text-center tabular-nums">
                                            @if ($wcell['sessions'] === 0 && $wcell['hours'] == 0)
                                                <span class="text-slate-300">0</span>
                                                <span class="block text-[9px] text-slate-300">({{ $occ }}x)</span>
                                            @else
                                                <span class="font-semibold text-slate-800">{{ $wcell['sessions'] }}a</span>
                                                <span class="text-slate-400">/</span>
                                                <span class="text-violet-700">{{ number_format($wcell['hours'], 1, ',', '.') }}h</span>
                                                <span class="block text-[9px] text-slate-400">({{ $occ }}x {{ $occ > 0 ? number_format($wcell['hours'] / $occ, 1, ',', '.') . 'h/' . __('dia') : '' }})</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    @if (count($dailyCompareMonths) > 1)
                                        @php
                                            $lastIdx = count($dailyCompareMonths) - 1;
                                            $lastW = $dailyCompareMonths[$lastIdx]['weekdays'][$wd] ?? ['sessions' => 0, 'hours' => 0];
                                            $prevW = $dailyCompareMonths[$lastIdx - 1]['weekdays'][$wd] ?? ['sessions' => 0, 'hours' => 0];
                                            $dWS = $lastW['sessions'] - $prevW['sessions'];
                                            $dWH = round($lastW['hours'] - $prevW['hours'], 1);
                                        @endphp
                                        <td class="px-3 py-1.5 text-center tabular-nums">
                                            @if ($dWS === 0 && abs($dWH) < 0.05)
                                                <span class="text-slate-300">=</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 font-semibold {{ ($dWS + $dWH) >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                                    {{ $dWS > 0 ? '+' : '' }}{{ $dWS }}a / {{ $dWH > 0 ? '+' : '' }}{{ number_format($dWH, 1, ',', '.') }}h
                                                </span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-semibold">
                                <td class="sticky left-0 bg-slate-50 px-3 py-2 text-slate-700">{{ __('Total') }}</td>
                                @foreach ($dailyCompareMonths as $dc)
                                    <td class="px-3 py-2 text-center tabular-nums text-slate-800">{{ $dc['total_sessions'] }}a / {{ number_format($dc['total_hours'], 1, ',', '.') }}h</td>
                                @endforeach
                                @if (count($dailyCompareMonths) > 1)
                                    @php
                                        $l = $dailyCompareMonths[count($dailyCompareMonths) - 1];
                                        $p = $dailyCompareMonths[count($dailyCompareMonths) - 2];
                                        $tS = $l['total_sessions'] - $p['total_sessions'];
                                        $tH = round($l['total_hours'] - $p['total_hours'], 1);
                                    @endphp
                                    <td class="px-3 py-2 text-center tabular-nums {{ ($tS + $tH) >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $tS > 0 ? '+' : '' }}{{ $tS }}a / {{ $tH > 0 ? '+' : '' }}{{ number_format($tH, 1, ',', '.') }}h
                                    </td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="mt-3 h-[200px]">
                    <canvas id="weekdayCompareChart"></canvas>
                </div>

                {{-- Day-by-day table --}}
                <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="sticky left-0 bg-slate-100 px-3 py-2 text-left font-semibold text-slate-700">{{ __('Dia') }}</th>
                                @foreach ($dailyCompareMonths as $dc)
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">
                                        {{ $dc['label'] }}
                                        @if ($dc['is_reference'])
                                            <span class="ml-0.5 rounded-full bg-cyan-100 px-1.5 py-px text-[9px] text-cyan-700">*</span>
                                        @endif
                                        <span class="block text-[9px] font-normal text-slate-400">{{ __('aulas / horas') }}</span>
                                    </th>
                                @endforeach
                                @if (count($dailyCompareMonths) > 1)
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">
                                        {{ __('Δ (último − anterior)') }}
                                        <span class="block text-[9px] font-normal text-slate-400">{{ __('aulas / horas') }}</span>
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @for ($day = 1; $day <= $dailyCompareMaxDays; $day++)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="sticky left-0 bg-white px-3 py-1.5 font-semibold tabular-nums text-slate-600">{{ $day }}</td>
                                    @foreach ($dailyCompareMonths as $dc)
                                        @php $cell = $dc['days'][$day] ?? null; @endphp
                                        <td class="px-3 py-1.5 text-center tabular-nums">
                                            @if ($cell === null)
                                                <span class="text-slate-200">—</span>
                                            @elseif ($cell['sessions'] === 0 && $cell['hours'] == 0)
                                                <span class="text-slate-300">0</span>
                                            @else
                                                <span class="font-semibold text-slate-800">{{ $cell['sessions'] }}a</span>
                                                <span class="text-slate-400">/</span>
                                                <span class="text-violet-700">{{ number_format($cell['hours'], 1, ',', '.') }}h</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    @if (count($dailyCompareMonths) > 1)
                                        @php
                                            $lastIdx = count($dailyCompareMonths) - 1;
                                            $last = $dailyCompareMonths[$lastIdx]['days'][$day] ?? null;
                                            $prevM = $dailyCompareMonths[$lastIdx - 1]['days'][$day] ?? null;
                                        @endphp
                                        <td class="px-3 py-1.5 text-center tabular-nums">
                                            @if ($last === null || $prevM === null)
                                                <span class="text-slate-200">—</span>
                                            @else
                                                @php
                                                    $dS = $last['sessions'] - $prevM['sessions'];
                                                    $dH = round($last['hours'] - $prevM['hours'], 1);
                                                @endphp
                                                @if ($dS === 0 && abs($dH) < 0.05)
                                                    <span class="text-slate-300">=</span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 font-semibold {{ ($dS + $dH) >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                                        {{ $dS > 0 ? '+' : '' }}{{ $dS }}a / {{ $dH > 0 ? '+' : '' }}{{ number_format($dH, 1, ',', '.') }}h
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endfor
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-semibold">
                                <td class="sticky left-0 bg-slate-50 px-3 py-2 text-slate-700">{{ __('Total') }}</td>
                                @foreach ($dailyCompareMonths as $dc)
                                    <td class="px-3 py-2 text-center tabular-nums text-slate-800">{{ $dc['total_sessions'] }}a / {{ number_format($dc['total_hours'], 1, ',', '.') }}h</td>
                                @endforeach
                                @if (count($dailyCompareMonths) > 1)
                                    @php
                                        $l = $dailyCompareMonths[count($dailyCompareMonths) - 1];
                                        $p = $dailyCompareMonths[count($dailyCompareMonths) - 2];
                                        $tS = $l['total_sessions'] - $p['total_sessions'];
                                        $tH = round($l['total_hours'] - $p['total_hours'], 1);
                                    @endphp
                                    <td class="px-3 py-2 text-center tabular-nums {{ ($tS + $tH) >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $tS > 0 ? '+' : '' }}{{ $tS }}a / {{ $tH > 0 ? '+' : '' }}{{ number_format($tH, 1, ',', '.') }}h
                                    </td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-3 h-[220px]">
                    <canvas id="dailyCompareChart"></canvas>
                </div>
            </section>

            {{-- ====== Period Summary Cards ====== --}}
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-2xl border border-cyan-200 bg-cyan-50/90 p-4">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-cyan-700">{{ __('Faturado (período)') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-slate-900">R$ {{ number_format($rangeBillingTotal, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-emerald-700">{{ __('Recebido (período)') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-emerald-700">R$ {{ number_format($rangeBillingPaid, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-amber-200 bg-amber-50/90 p-4">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-amber-700">{{ __('Pendente (período)') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-amber-700">R$ {{ number_format($rangeBillingPending, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-violet-200 bg-violet-50/90 p-4">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-violet-700">{{ __('Custo instrut. (período)') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-violet-700">R$ {{ number_format($rangeInstructorTotal, 2, ',', '.') }}</p>
                </article>
            </div>

            {{-- ====== Month Comparison (with differences) ====== --}}
            <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ __('Comparativo entre meses') }}</h3>
                        <p class="mt-0.5 text-xs text-slate-500">{{ __('Meses do período (início–fim) + mês de referência. Diferenças vs. mês anterior.') }}</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ count($monthComparison) }} {{ __('meses') }}</span>
                </div>
                <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="px-4 py-2.5 text-left font-semibold text-slate-700">{{ __('Mês') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Faturado') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Recebido') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Custo instr.') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Instr. pago') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Instr. pendente') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Resultado') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Δ Faturado') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Δ Custo') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Δ Pago instr.') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Δ Resultado') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($monthComparison as $idx => $cmp)
                                <tr class="hover:bg-slate-50/50 {{ $cmp['is_reference'] ? 'bg-cyan-50/60' : '' }}">
                                    <td class="px-4 py-2.5 font-medium text-slate-800">
                                        <a href="{{ route('financial.reports', array_merge(request()->query(), ['month' => $cmp['key']])) }}"
                                           class="hover:text-cyan-700 hover:underline" title="{{ __('Ver como mês de referência') }}">
                                            {{ $cmp['full_label'] }}
                                        </a>
                                        @if ($cmp['is_reference'])
                                            <span class="ml-1 rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-semibold text-cyan-700">{{ __('ref') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-slate-700">R$ {{ number_format($cmp['billing_total'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-emerald-700">R$ {{ number_format($cmp['billing_paid'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-violet-700">R$ {{ number_format($cmp['instructor_total'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-emerald-700">R$ {{ number_format($cmp['instructor_paid'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-amber-700">R$ {{ number_format($cmp['instructor_pending'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums font-semibold {{ $cmp['net'] >= 0 ? 'text-slate-900' : 'text-rose-600' }}">R$ {{ number_format($cmp['net'], 2, ',', '.') }}</td>
                                    @php
                                        $diffCells = [
                                            ['v' => $cmp['diff_billing'], 'p' => $cmp['diff_billing_pct'], 'invert' => false],
                                            ['v' => $cmp['diff_instructor'], 'p' => $cmp['diff_instructor_pct'], 'invert' => true],
                                            ['v' => $cmp['diff_instructor_paid'], 'p' => $cmp['diff_instructor_paid_pct'], 'invert' => false],
                                            ['v' => $cmp['diff_net'], 'p' => $cmp['diff_net_pct'], 'invert' => false],
                                        ];
                                    @endphp
                                    @foreach ($diffCells as $d)
                                        <td class="px-4 py-2.5 text-right tabular-nums">
                                            @if ($idx === 0 || $d['v'] === null)
                                                <span class="text-xs text-slate-300">—</span>
                                            @else
                                                @php
                                                    $positive = $d['v'] > 0.005;
                                                    $negative = $d['v'] < -0.005;
                                                    $good = $d['invert'] ? $negative : $positive;
                                                    $bad = $d['invert'] ? $positive : $negative;
                                                    $cls = $good ? 'bg-emerald-100 text-emerald-700' : ($bad ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-500');
                                                    $arrow = $positive ? '▲' : ($negative ? '▼' : '•');
                                                @endphp
                                                <span class="inline-flex flex-col items-end rounded-lg px-2 py-0.5 text-xs font-semibold {{ $cls }}">
                                                    <span>{{ $arrow }} R$ {{ number_format(abs($d['v']), 2, ',', '.') }}</span>
                                                    @if ($d['p'] !== null)
                                                        <span class="text-[10px] font-medium opacity-80">{{ $d['v'] > 0 ? '+' : '' }}{{ number_format($d['p'], 1, ',', '.') }}%</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 h-[200px]">
                    <canvas id="monthCompareChart"></canvas>
                </div>
            </section>

            {{-- ====== Charts Row ====== --}}
            <div class="grid gap-4 md:grid-cols-3">
                {{-- Distribution Doughnut --}}
                <section class="rounded-3xl border border-slate-900/10 bg-slate-950 p-5">
                    <h3 class="text-sm font-semibold text-white">{{ __('Distribuição financeira') }}</h3>
                    <div class="mt-3 h-[220px]">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </section>
                {{-- Monthly Evolution --}}
                <section class="rounded-3xl border border-slate-900/10 bg-slate-950 p-5">
                    <h3 class="text-sm font-semibold text-white">{{ __('Evolução mensal (6 meses)') }}</h3>
                    <div class="mt-3 h-[220px]">
                        <canvas id="moneyFlowChart"></canvas>
                    </div>
                </section>
                {{-- Range Growth --}}
                <section class="rounded-3xl border border-slate-900/10 bg-slate-950 p-5">
                    <h3 class="text-sm font-semibold text-white">{{ __('Evolução no período') }}</h3>
                    <div class="mt-3 h-[220px]">
                        <canvas id="rangeGrowthChart"></canvas>
                    </div>
                </section>
            </div>

            {{-- ====== Projection Chart ====== --}}
            @php
                $combinedLabels = array_merge($monthlyLabels, $projectLabels);
                $histBilling = array_merge($monthlyBillingValues, array_fill(0, count($projectLabels), null));
                $projBilling = array_merge(array_fill(0, count($monthlyLabels), null), $projectBillingValues);
                $histInstructor = array_merge($monthlyInstructorValues, array_fill(0, count($projectLabels), null));
                $projInstructor = array_merge(array_fill(0, count($monthlyLabels), null), $projectInstructorValues);
            @endphp
            <section class="rounded-3xl border border-slate-900/10 bg-slate-950 p-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-white">{{ __('Projeção financeira') }}</h3>
                    <span class="text-[10px] text-slate-500">{{ __('Regressão linear sobre os últimos 6 meses') }}</span>
                </div>
                <div class="mt-3 h-[240px]">
                    <canvas id="projectionChart"></canvas>
                </div>
            </section>

            {{-- ====== Course Breakdown ====== --}}
            <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm{{ $courseBilling->isEmpty() ? ' no-print' : '' }}">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('Receitas por curso') }}</h3>
                <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="px-4 py-2.5 text-left font-semibold text-slate-700">{{ __('Curso') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Total') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Pago') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Pendente') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Cancelado') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('% Pago') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($courseBilling as $row)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $row['course_name'] }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-slate-700">R$ {{ number_format($row['amount'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-emerald-700">R$ {{ number_format($row['paid'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-amber-700">R$ {{ number_format($row['pending'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-rose-400">R$ {{ number_format($row['canceled'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        @if ($row['amount'] > 0)
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ round(($row['paid'] / $row['amount']) * 100) }}%</span>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-400">{{ __('Sem dados para o período.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ====== Class Billing ====== --}}
            <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm{{ $classBilling->isEmpty() ? ' no-print' : '' }}">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('Receitas por turma') }}</h3>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Cobranças dos alunos no mês selecionado.') }}</p>
                <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="px-4 py-2.5 text-left font-semibold text-slate-700">{{ __('Turma') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Total') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Pago') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Pendente') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Receita') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($classBilling as $row)
                                @php $paidPct = $row['amount'] > 0 ? round(($row['paid'] / $row['amount']) * 100) : 0; @endphp
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $row['class_name'] }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-slate-700">R$ {{ number_format($row['amount'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-emerald-700">R$ {{ number_format($row['paid'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-amber-700">R$ {{ number_format($row['pending'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-200">
                                                <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $paidPct }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-slate-600">{{ $paidPct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">{{ __('Sem cobranças para o período.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($billingOverdue > 0)
                    <div class="mt-3 rounded-xl border border-rose-100 bg-rose-50 px-4 py-2.5 text-sm text-rose-700">
                        {{ __('Cobranças vencidas: R$ :value', ['value' => number_format($billingOverdue, 2, ',', '.')]) }}
                    </div>
                @endif
            </section>

            {{-- ====== Instructor Payment Summary ====== --}}
            <div class="grid gap-3 sm:grid-cols-3">
                <article class="rounded-2xl border border-rose-100 bg-rose-50/80 p-4">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-rose-600">{{ __('Total instrutores') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-rose-700">R$ {{ number_format($rangeInstructorTotal, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-emerald-100 bg-emerald-50/80 p-4">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-emerald-600">{{ __('Já pago') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-emerald-700">R$ {{ number_format($rangeInstructorPaidTotal, 2, ',', '.') }}</p>
                </article>
                <article class="rounded-2xl border border-amber-100 bg-amber-50/80 p-4">
                    <p class="text-[10px] uppercase tracking-[0.15em] text-amber-600">{{ __('Pendente') }}</p>
                    <p class="mt-1.5 text-2xl font-bold text-amber-700">R$ {{ number_format($rangeInstructorPendingTotal, 2, ',', '.') }}</p>
                </article>
            </div>

            {{-- ====== Instructor Earnings Table (per-course breakdown) ====== --}}
            @php $rowsComContratoOuGanho = $instructorRows->filter(fn($r) => $r['total_earnings'] > 0 || collect($r['courses'])->some(fn($c) => $c['payment_type'] !== null)); @endphp
            <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('Ganhos por instrutor') }}</h3>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Base: presença nas aulas do mês selecionado.') }}</p>
                <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="px-4 py-2.5 text-left font-semibold text-slate-700">{{ __('Instrutor') }}</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-slate-700">{{ __('Curso') }}</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-slate-700">{{ __('Contrato') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Horas') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Aulas') }}</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ __('Ganho') }}</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-slate-700">{{ __('Pagamento') }}</th>
                                <th class="px-4 py-2.5 text-center font-semibold text-slate-700">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($rowsComContratoOuGanho as $row)
                                @php
                                    $courseCount = count($row['courses']);
                                    $payment = $row['payment'];
                                    $paidAmount = $row['paid_amount'];
                                    $paymentStatus = $row['payment_status'];
                                @endphp
                                @foreach ($row['courses'] as $ci => $courseRow)
                                    @php
                                        $contract = $courseRow['contract'];
                                        $earnings = $courseRow['earnings'];
                                        $diff = $paidAmount - $row['total_earnings'];
                                    @endphp
                                    <tr class="hover:bg-slate-50/50">
                                        @if ($ci === 0)
                                            <td class="px-4 py-2.5 font-medium text-slate-800" rowspan="{{ $courseCount }}">
                                                {{ $row['instructor']->full_name }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-2.5 text-slate-700">
                                            {{ $courseRow['course']?->title ?? __('Sem curso') }}
                                        </td>
                                        <td class="px-4 py-2.5 text-slate-600">
                                            @if ($courseRow['payment_type'] === 'hourly')
                                                <span class="text-xs">R$ {{ number_format($courseRow['contract_value'], 2, ',', '.') }}/h</span>
                                            @elseif ($courseRow['payment_type'] === 'monthly_fixed')
                                                <span class="text-xs">{{ __('Fixo') }} R$ {{ number_format($courseRow['contract_value'], 2, ',', '.') }}</span>
                                            @else
                                                <span class="text-xs text-rose-500">{{ __('Sem contrato') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-slate-700">{{ number_format($courseRow['hours'], 1, ',', '.') }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-slate-700">{{ $courseRow['sessions'] }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums font-semibold text-slate-900">R$ {{ number_format($earnings, 2, ',', '.') }}</td>
                                        @if ($ci === 0)
                                            <td class="px-4 py-2.5" rowspan="{{ $courseCount }}">
                                                <form method="POST" action="{{ route('financial.reports.instructor-pay', $row['instructor']) }}" class="flex flex-wrap items-center gap-1.5">
                                                    @csrf
                                                    <input type="hidden" name="reference_month" value="{{ $referenceMonth->format('Y-m') }}">
                                                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $paidAmount ?: $row['total_earnings']) }}"
                                                        class="w-24 rounded-lg border border-slate-300 px-2 py-1 text-xs tabular-nums text-right">
                                                    <button type="submit" name="paid" value="1"
                                                        class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $paymentStatus === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-900 text-white' }}">
                                                        {{ $paymentStatus === 'paid' ? __('Pago') : __('Pagar') }}
                                                    </button>
                                                    @if ($payment)
                                                        <button type="submit" name="remove" value="1"
                                                            class="rounded-lg border border-rose-300 px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50"
                                                            onclick="return confirm('{{ __('Remover pagamento deste instrutor?') }}')">
                                                            {{ __('Remover') }}
                                                        </button>
                                                    @endif
                                                </form>
                                            </td>
                                            <td class="px-4 py-2.5 text-center" rowspan="{{ $courseCount }}">
                                                @php $anyHasContract = collect($row['courses'])->some(fn($c) => $c['payment_type'] !== null); @endphp
                                                @if (!$anyHasContract)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                                                        {{ __('N/A') }}
                                                    </span>
                                                @elseif ($paymentStatus === 'paid')
                                                    @if ($diff > 0.01)
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700" title="{{ __('Pago a mais: R$ :value', ['value' => number_format($diff, 2, ',', '.')]) }}">
                                                            +R$ {{ number_format($diff, 2, ',', '.') }}
                                                        </span>
                                                    @elseif ($diff < -0.01)
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-700" title="{{ __('Diferença: R$ :value', ['value' => number_format(abs($diff), 2, ',', '.')]) }}">
                                                            -R$ {{ number_format(abs($diff), 2, ',', '.') }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                                            {{ __('OK') }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-700">
                                                        R$ {{ number_format($row['total_earnings'], 2, ',', '.') }}
                                                    </span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                {{-- Total row for this instructor --}}
                                @if ($courseCount > 1)
                                    <tr class="bg-slate-50/50 font-semibold">
                                        <td colspan="2" class="px-4 py-2 text-right text-xs uppercase tracking-wider text-slate-500">{{ __('Total') }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums text-slate-800">{{ number_format($row['total_hours'], 1, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums text-slate-800">{{ $row['total_sessions'] }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums text-slate-900">R$ {{ number_format($row['total_earnings'], 2, ',', '.') }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                @endif
                            @empty
                                <tr><td colspan="8" class="px-4 py-8 text-center text-sm text-slate-400">{{ __('Sem dados de instrutores para o período.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ====== Contract Editor (collapsible, per-course) ====== --}}
            <details class="no-print group rounded-3xl border border-slate-900/10 bg-slate-950 p-5">
                <summary class="flex cursor-pointer items-center justify-between text-sm font-medium text-slate-200">
                    <span>{{ __('Contratos de instrutores por curso') }}</span>
                    <svg class="h-4 w-4 text-slate-500 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="mt-4 space-y-2">
                    @foreach ($instructorRows as $row)
                        <details class="group rounded-2xl border border-slate-700/50 bg-slate-900/30 transition-colors hover:border-slate-600/50">
                            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-slate-200">
                                <span>{{ $row['instructor']->full_name }}</span>
                                <span class="text-xs text-slate-500 group-open:hidden">{{ __('Clique para editar contratos') }}</span>
                                <span class="hidden text-xs text-cyan-400 group-open:inline">{{ __('Editando contratos') }}</span>
                            </summary>
                            <div class="border-t border-slate-700/50 px-4 py-4 space-y-4">
                                @php
                                    $allContracts = $row['instructor']->contracts;
                                    $coursesList = \App\Models\Course::orderBy('title')->get();
                                @endphp
                                {{-- Per-course contract forms --}}
                                @foreach ($coursesList as $course)
                                    @php
                                        $courseContract = $allContracts->first(fn($c) => $c->course_id === $course->id);
                                    @endphp
                                    <div class="rounded-xl border border-slate-700/30 bg-slate-900/20 p-3">
                                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-cyan-400">{{ $course->title }}</h4>
                                        <form method="POST" action="{{ route('financial.reports.instructor-contract.save', $row['instructor']) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                            @csrf
                                            <input type="hidden" name="month" value="{{ $referenceMonth->format('Y-m') }}">
                                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                                            <input type="hidden" name="contract_id" value="{{ $courseContract?->id }}">

                                            <select name="payment_type" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200">
                                                <option value="hourly" @selected(($courseContract?->payment_type ?? 'hourly') === 'hourly')>{{ __('Por hora') }}</option>
                                                <option value="monthly_fixed" @selected(($courseContract?->payment_type ?? '') === 'monthly_fixed')>{{ __('Fixo mensal') }}</option>
                                            </select>

                                            <select name="active" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200">
                                                <option value="1" @selected(($courseContract?->active ?? true) === true)>{{ __('Ativo') }}</option>
                                                <option value="0" @selected(($courseContract?->active ?? true) === false)>{{ __('Inativo') }}</option>
                                            </select>

                                            <input type="number" step="0.01" min="0" name="hourly_rate" value="{{ old('hourly_rate', $courseContract?->hourly_rate) }}"
                                                placeholder="{{ __('Valor/hora') }}" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200 placeholder-slate-500">

                                            <input type="number" step="0.01" min="0" name="monthly_amount" value="{{ old('monthly_amount', $courseContract?->monthly_amount) }}"
                                                placeholder="{{ __('Valor mensal') }}" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200 placeholder-slate-500">

                                            <input type="date" name="starts_at" value="{{ old('starts_at', optional($courseContract?->starts_at)->format('Y-m-d') ?? $referenceMonth->copy()->startOfMonth()->format('Y-m-d')) }}"
                                                class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200">

                                            <input type="date" name="ends_at" value="{{ old('ends_at', optional($courseContract?->ends_at)->format('Y-m-d')) }}"
                                                class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200">

                                            <input type="text" name="notes" value="{{ old('notes', $courseContract?->notes) }}"
                                                placeholder="{{ __('Observações') }}" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200 placeholder-slate-500 lg:col-span-2">

                                            <button class="rounded-lg bg-cyan-600 px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-cyan-500">
                                                {{ __('Salvar contrato') }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                                {{-- Fallback contract (no specific course) --}}
                                @php $fallbackContract = $allContracts->first(fn($c) => $c->course_id === null); @endphp
                                <div class="rounded-xl border border-dashed border-slate-600/30 bg-slate-900/10 p-3">
                                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Contrato genérico (fallback)') }}</h4>
                                    <form method="POST" action="{{ route('financial.reports.instructor-contract.save', $row['instructor']) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                        @csrf
                                        <input type="hidden" name="month" value="{{ $referenceMonth->format('Y-m') }}">
                                        <input type="hidden" name="contract_id" value="{{ $fallbackContract?->id }}">

                                        <select name="payment_type" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200">
                                            <option value="hourly" @selected(($fallbackContract?->payment_type ?? 'hourly') === 'hourly')>{{ __('Por hora') }}</option>
                                            <option value="monthly_fixed" @selected(($fallbackContract?->payment_type ?? '') === 'monthly_fixed')>{{ __('Fixo mensal') }}</option>
                                        </select>

                                        <select name="active" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200">
                                            <option value="1" @selected(($fallbackContract?->active ?? true) === true)>{{ __('Ativo') }}</option>
                                            <option value="0" @selected(($fallbackContract?->active ?? true) === false)>{{ __('Inativo') }}</option>
                                        </select>

                                        <input type="number" step="0.01" min="0" name="hourly_rate" value="{{ old('hourly_rate', $fallbackContract?->hourly_rate) }}"
                                            placeholder="{{ __('Valor/hora') }}" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200 placeholder-slate-500">

                                        <input type="number" step="0.01" min="0" name="monthly_amount" value="{{ old('monthly_amount', $fallbackContract?->monthly_amount) }}"
                                            placeholder="{{ __('Valor mensal') }}" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200 placeholder-slate-500">

                                        <input type="date" name="starts_at" value="{{ old('starts_at', optional($fallbackContract?->starts_at)->format('Y-m-d') ?? $referenceMonth->copy()->startOfMonth()->format('Y-m-d')) }}"
                                            class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200">

                                        <input type="date" name="ends_at" value="{{ old('ends_at', optional($fallbackContract?->ends_at)->format('Y-m-d')) }}"
                                            class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200">

                                        <input type="text" name="notes" value="{{ old('notes', $fallbackContract?->notes) }}"
                                            placeholder="{{ __('Observações') }}" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-200 placeholder-slate-500 lg:col-span-2">

                                        <button class="rounded-lg bg-cyan-600 px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-cyan-500">
                                            {{ __('Salvar contrato') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>
            </details>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // --- Daily comparison chart (hours per day, per month) ---
        (function() {
            const el = document.getElementById('dailyCompareChart');
            if (!el) return;
            const months = @json($dailyCompareMonths);
            const maxDays = @json($dailyCompareMaxDays);
            const palette = [
                { border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.12)' },
                { border: '#8b5cf6', bg: 'rgba(139, 92, 246, 0.12)' },
                { border: '#10b981', bg: 'rgba(16, 185, 129, 0.12)' },
                { border: '#f59e0b', bg: 'rgba(245, 158, 11, 0.12)' },
                { border: '#f43f5e', bg: 'rgba(244, 63, 94, 0.12)' },
                { border: '#64748b', bg: 'rgba(100, 116, 139, 0.12)' },
            ];
            const labels = [];
            for (let d = 1; d <= maxDays; d++) labels.push(String(d));
            const datasets = months.slice(0, 6).map((m, i) => {
                const c = palette[i % palette.length];
                const data = [];
                for (let d = 1; d <= maxDays; d++) data.push(m.days[d] ? m.days[d].hours : null);
                return {
                    label: m.label + (m.is_reference ? ' *' : ''),
                    data,
                    borderColor: c.border,
                    backgroundColor: c.bg,
                    fill: false,
                    tension: 0.3,
                    pointRadius: 2,
                    borderWidth: m.is_reference ? 2.5 : 1.5,
                };
            });
            new Chart(el.getContext('2d'), {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#475569', boxWidth: 10, font: { size: 10 } } } },
                    scales: {
                        x: { title: { display: true, text: @json(__('Dia do mês')), color: '#94a3b8', font: { size: 9 } }, ticks: { color: '#64748b', font: { size: 9 } }, grid: { display: false } },
                        y: { beginAtZero: true, title: { display: true, text: @json(__('Horas')), color: '#94a3b8', font: { size: 9 } }, ticks: { color: '#64748b', font: { size: 9 } }, grid: { color: 'rgba(148, 163, 184, 0.15)' } },
                    },
                },
            });
        })();

        // --- Weekday comparison chart (sessions per weekday, per month) ---
        (function() {
            const el = document.getElementById('weekdayCompareChart');
            if (!el) return;
            const months = @json($dailyCompareMonths);
            const order = [1, 2, 3, 4, 5, 6, 0];
            const names = [@json(__('Seg')), @json(__('Ter')), @json(__('Qua')), @json(__('Qui')), @json(__('Sex')), @json(__('Sáb')), @json(__('Dom'))];
            const palette = ['rgba(6, 182, 212, 0.72)', 'rgba(139, 92, 246, 0.72)', 'rgba(16, 185, 129, 0.72)', 'rgba(245, 158, 11, 0.72)', 'rgba(244, 63, 94, 0.72)', 'rgba(100, 116, 139, 0.72)'];
            const datasets = months.slice(0, 6).map((m, i) => ({
                label: m.label + (m.is_reference ? ' *' : ''),
                data: order.map(w => m.weekdays[w] ? m.weekdays[w].sessions : 0),
                backgroundColor: palette[i % palette.length],
                borderRadius: 6,
            }));
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: { labels: names, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#475569', boxWidth: 10, font: { size: 10 } } } },
                    scales: {
                        x: { ticks: { color: '#64748b', font: { size: 9 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { color: '#64748b', font: { size: 9, stepSize: 1 } }, grid: { color: 'rgba(148, 163, 184, 0.15)' } },
                    },
                },
            });
        })();

        // --- Month comparison chart ---
        (function() {
            const el = document.getElementById('monthCompareChart');
            if (!el) return;
            const cmp = @json($monthComparison);
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: cmp.map(r => r.label + (r.is_reference ? ' *' : '')),
                    datasets: [
                        {
                            label: @json(__('Faturado')),
                            data: cmp.map(r => r.billing_total),
                            backgroundColor: 'rgba(16, 185, 129, 0.72)',
                            borderRadius: 6,
                        },
                        {
                            label: @json(__('Custo instr.')),
                            data: cmp.map(r => r.instructor_total),
                            backgroundColor: 'rgba(139, 92, 246, 0.72)',
                            borderRadius: 6,
                        },
                        {
                            label: @json(__('Instr. pago')),
                            data: cmp.map(r => r.instructor_paid),
                            backgroundColor: 'rgba(16, 185, 129, 0.45)',
                            borderRadius: 6,
                        },
                        {
                            label: @json(__('Resultado')),
                            data: cmp.map(r => r.net),
                            backgroundColor: 'rgba(14, 165, 233, 0.72)',
                            borderRadius: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#475569', boxWidth: 10, font: { size: 10 } } } },
                    scales: {
                        x: { ticks: { color: '#64748b', font: { size: 9 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { color: '#64748b', font: { size: 9 } }, grid: { color: 'rgba(148, 163, 184, 0.15)' } },
                    },
                },
            });
        })();

        // --- Distribution doughnut ---
        (function() {
            const el = document.getElementById('distributionChart');
            if (!el) return;
            const data = @json($billingStatusAmount);
            new Chart(el.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: [@json(__('Recebido')), @json(__('A vencer')), @json(__('Vencido')), @json(__('Cancelado'))],
                    datasets: [{
                        data: [data.paid, data.pending_within, data.overdue, data.canceled],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#e2e8f0', boxWidth: 10, padding: 12, font: { size: 10 } },
                        },
                    },
                },
            });
        })();

        // --- Monthly bar chart ---
        (function() {
            const labels = @json($monthlyLabels);
            const billing = @json($monthlyBillingValues);
            const instructors = @json($monthlyInstructorValues);
            const el = document.getElementById('moneyFlowChart');
            if (!el) return;
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: @json(__('Faturamento alunos')),
                            data: billing,
                            backgroundColor: 'rgba(16, 185, 129, 0.72)',
                            borderRadius: 6,
                        },
                        {
                            label: @json(__('Custo instrutores')),
                            data: instructors,
                            backgroundColor: 'rgba(244, 63, 94, 0.72)',
                            borderRadius: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#e2e8f0', boxWidth: 10, font: { size: 10 } } } },
                    scales: {
                        x: { ticks: { color: '#94a3b8', font: { size: 9 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { color: '#94a3b8', font: { size: 9 } }, grid: { color: 'rgba(148, 163, 184, 0.12)' } },
                    },
                },
            });
        })();

        // --- Range line chart ---
        (function() {
            const labels = @json($rangeLabels);
            const billing = @json($rangeBillingValues);
            const instructors = @json($rangeInstructorValues);
            const el = document.getElementById('rangeGrowthChart');
            if (!el) return;
            new Chart(el.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: @json(__('Faturamento alunos')),
                            data: billing,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                        },
                        {
                            label: @json(__('Custo instrutores')),
                            data: instructors,
                            borderColor: '#f43f5e',
                            backgroundColor: 'rgba(244, 63, 94, 0.08)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#e2e8f0', boxWidth: 10, font: { size: 10 } } } },
                    scales: {
                        x: { ticks: { color: '#94a3b8', font: { size: 9 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { color: '#94a3b8', font: { size: 9 } }, grid: { color: 'rgba(148, 163, 184, 0.12)' } },
                    },
                },
            });
        })();

        // --- Projection chart ---
        (function() {
            const labels = @json($combinedLabels);
            const histBill = @json($histBilling);
            const projBill = @json($projBilling);
            const histInst = @json($histInstructor);
            const projInst = @json($projInstructor);
            const el = document.getElementById('projectionChart');
            if (!el) return;
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: @json(__('Faturamento (real)')),
                            data: histBill,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderRadius: 4,
                        },
                        {
                            label: @json(__('Faturamento (projeção)')),
                            data: projBill,
                            backgroundColor: 'rgba(16, 185, 129, 0.25)',
                            borderColor: 'rgba(16, 185, 129, 0.6)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: @json(__('Custo instr. (real)')),
                            data: histInst,
                            backgroundColor: 'rgba(244, 63, 94, 0.8)',
                            borderRadius: 4,
                        },
                        {
                            label: @json(__('Custo instr. (projeção)')),
                            data: projInst,
                            backgroundColor: 'rgba(244, 63, 94, 0.25)',
                            borderColor: 'rgba(244, 63, 94, 0.6)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#e2e8f0', boxWidth: 10, font: { size: 10 } } },
                    },
                    scales: {
                        x: { ticks: { color: '#94a3b8', font: { size: 9 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { color: '#94a3b8', font: { size: 9 } }, grid: { color: 'rgba(148, 163, 184, 0.12)' } },
                    },
                },
            });
        })();
    </script>

    <style>
        @media print {
            .no-print { display: none !important; }
            header, nav, footer { display: none !important; }
            body { background: white !important; font-size: 11pt; }
            .min-h-screen {
                background: white !important;
                padding: 0 !important;
                min-height: auto !important;
                margin-top: 0 !important;
            }
            .max-w-7xl { max-width: 100% !important; padding: 0 10px !important; margin: 0 !important; }
            .space-y-5 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.75rem !important; }
            .rounded-2xl, .rounded-3xl {
                border: 1px solid #ccc !important;
                box-shadow: none !important;
                break-inside: avoid;
                page-break-inside: avoid;
                background: white !important;
            }
            .bg-slate-950 { background: white !important; border-color: #ccc !important; }
            .bg-slate-950 h3, .bg-slate-950 canvas { color: #333 !important; }
            .bg-gradient-to-b, .bg-\[radial-gradient\] { background: white !important; }
            .text-white { color: #1e293b !important; }
            .text-slate-200, .text-slate-300, .text-slate-400, .text-slate-500 { color: #475569 !important; }
            .grid { page-break-inside: avoid; }
            table { font-size: 9pt; }
            th { background: #f1f5f9 !important; color: #334155 !important; }
            .overflow-y-auto { max-height: none !important; overflow: visible !important; }
            .sticky { position: static !important; }
        }
        @media print {
            @page { margin: 15mm; }
        }
    </style>
</x-app-layout>
