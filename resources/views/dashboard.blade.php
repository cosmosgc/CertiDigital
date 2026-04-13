<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-cyan-300">{{ __('Painel gerencial') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white leading-tight">
                    {{ __('Operação acadêmica em tempo real') }}
                </h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-300">
                    {{ __('Uma visão consolidada de cursos, turmas, matrículas, certificados e atividades recentes para apoiar a operação do CertiDigital.') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3 text-sm">
                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-slate-200 backdrop-blur">
                    <span class="block text-xs uppercase tracking-[0.24em] text-slate-400">{{ __('Matrículas concluídas') }}</span>
                    <span class="mt-1 block text-2xl font-semibold text-white">{{ $completionRate }}%</span>
                </div>
                <div class="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-cyan-100">
                    <span class="block text-xs uppercase tracking-[0.24em] text-cyan-200/80">{{ __('Eventos futuros') }}</span>
                    <span class="mt-1 block text-2xl font-semibold text-white">{{ $upcomingEventCount }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $primaryStats = [
            [
                'label' => __('Alunos'),
                'value' => number_format($studentCount, 0, ',', '.'),
                'helper' => trans_choice(':count aluno ativo neste mes|:count alunos ativos neste mes', $activeStudentsThisMonth, ['count' => number_format($activeStudentsThisMonth, 0, ',', '.')]),
                'tone' => 'from-sky-500/20 to-cyan-500/10 text-sky-700',
            ],
            [
                'label' => __('Matrículas'),
                'value' => number_format($enrollmentCount, 0, ',', '.'),
                'helper' => trans_choice(':count conclusao registrada|:count conclusoes registradas', $completedEnrollmentCount, ['count' => number_format($completedEnrollmentCount, 0, ',', '.')]),
                'tone' => 'from-emerald-500/20 to-lime-500/10 text-emerald-700',
            ],
            [
                'label' => __('Turmas'),
                'value' => number_format($classCount, 0, ',', '.'),
                'helper' => trans_choice(':count curso disponivel|:count cursos disponiveis', $courseCount, ['count' => number_format($courseCount, 0, ',', '.')]),
                'tone' => 'from-violet-500/20 to-fuchsia-500/10 text-violet-700',
            ],
            [
                'label' => __('Certificados'),
                'value' => number_format($certificateCount, 0, ',', '.'),
                'helper' => trans_choice(':count emitido neste mes|:count emitidos neste mes', $certificateGrowthThisMonth, ['count' => number_format($certificateGrowthThisMonth, 0, ',', '.')]),
                'tone' => 'from-amber-500/20 to-orange-500/10 text-amber-700',
            ],
        ];

        $secondaryStats = [
            [
                'label' => __('Horas de carga'),
                'value' => number_format($totalWorkloadHours, 0, ',', '.') . 'h',
            ],
            [
                'label' => __('Horas ministradas'),
                'value' => number_format($attendanceHours, 0, ',', '.') . 'h',
            ],
            [
                'label' => __('Nota média'),
                'value' => $averageGrade > 0 ? number_format($averageGrade, 1, ',', '.') : '—',
            ],
            [
                'label' => __('Progresso médio'),
                'value' => number_format($averageProgressHours, 1, ',', '.') . 'h',
            ],
            [
                'label' => __('Ocorrências'),
                'value' => number_format($annotationCount, 0, ',', '.'),
            ],
            [
                'label' => __('Usuários'),
                'value' => number_format($userCount, 0, ',', '.'),
            ],
        ];
    @endphp

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(34,211,238,0.12),_transparent_30%),linear-gradient(180deg,_#f8fbff_0%,_#eef4ff_48%,_#f8fafc_100%)] py-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($primaryStats as $stat)
                    <article class="overflow-hidden rounded-[28px] border border-white/70 bg-white/90 p-6 shadow-[0_20px_60px_-35px_rgba(15,23,42,0.45)] backdrop-blur">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ $stat['label'] }}</p>
                                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $stat['value'] }}</p>
                            </div>
                            <div class="rounded-2xl bg-gradient-to-br {{ $stat['tone'] }} px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em]">
                                {{ __('Agora') }}
                            </div>
                        </div>
                        <p class="mt-5 text-sm text-slate-500">{{ $stat['helper'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-4 lg:grid-cols-[minmax(0,1.8fr),minmax(320px,1fr)]">
                <article class="rounded-[32px] border border-slate-900/10 bg-slate-950 p-6 shadow-[0_25px_80px_-40px_rgba(15,23,42,0.75)]">
                    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.26em] text-cyan-300">{{ __('Tendência anual') }}</p>
                            <h3 class="mt-2 text-2xl font-semibold text-white">{{ __('Matrículas e certificados por mês') }}</h3>
                            <p class="mt-2 max-w-2xl text-sm text-slate-400">
                                {{ __('Comparação entre captação acadêmica e emissão de certificados nos últimos 12 meses.') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 h-[320px]">
                        <canvas id="operationsChart"></canvas>
                    </div>
                </article>

                <article class="rounded-[32px] border border-white/70 bg-white/90 p-6 shadow-[0_20px_60px_-35px_rgba(15,23,42,0.45)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-400">{{ __('Status das matrículas') }}</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ __('Distribuição operacional') }}</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ __('Entenda rapidamente quantas matrículas já foram concluídas, seguem em andamento ou ainda não foram vinculadas a uma turma.') }}
                    </p>
                    <div class="mt-6 h-[240px]">
                        <canvas id="enrollmentStatusChart"></canvas>
                    </div>
                    <div class="mt-6 grid gap-3">
                        <div class="flex items-center justify-between rounded-2xl bg-emerald-50 px-4 py-3">
                            <span class="text-sm font-medium text-emerald-800">{{ __('Concluídas') }}</span>
                            <span class="text-lg font-semibold text-emerald-900">{{ number_format($enrollmentStatus['completed'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-amber-50 px-4 py-3">
                            <span class="text-sm font-medium text-amber-800">{{ __('Em andamento') }}</span>
                            <span class="text-lg font-semibold text-amber-900">{{ number_format($enrollmentStatus['inProgress'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-slate-100 px-4 py-3">
                            <span class="text-sm font-medium text-slate-700">{{ __('Sem turma') }}</span>
                            <span class="text-lg font-semibold text-slate-900">{{ number_format($enrollmentStatus['withoutClass'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </article>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                @foreach ($secondaryStats as $stat)
                    <article class="rounded-[26px] border border-white/70 bg-white/85 p-5 shadow-sm backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">{{ $stat['label'] }}</p>
                        <p class="mt-4 text-3xl font-semibold text-slate-900">{{ $stat['value'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr),minmax(0,1fr)]">
                <article class="rounded-[32px] border border-white/70 bg-white/90 p-6 shadow-[0_20px_60px_-35px_rgba(15,23,42,0.45)]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Cursos em destaque') }}</p>
                            <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ __('Performance por curso') }}</h3>
                        </div>
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                            {{ __('Ver cursos') }}
                        </a>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-[24px] border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                <tr>
                                    <th class="px-5 py-4">{{ __('Curso') }}</th>
                                    <th class="px-5 py-4">{{ __('Turmas') }}</th>
                                    <th class="px-5 py-4">{{ __('Matrículas') }}</th>
                                    <th class="px-5 py-4">{{ __('Conclusões') }}</th>
                                    <th class="px-5 py-4">{{ __('Certificados') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($topCourses as $course)
                                    <tr class="align-top">
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-slate-900">{{ $course->title }}</p>
                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ number_format((float) $course->workload_hours, 0, ',', '.') }}h
                                                @if ($course->modality)
                                                    • {{ $course->modality }}
                                                @endif
                                            </p>
                                        </td>
                                        <td class="px-5 py-4 text-sm font-medium text-slate-700">{{ number_format($course->classes_count, 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-sm font-medium text-slate-700">{{ number_format($course->enrollments_count, 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-sm font-medium text-slate-700">{{ number_format($course->completed_enrollments_count, 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-sm font-medium text-slate-700">{{ number_format($course->certificates_count, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">
                                            {{ __('Ainda nao ha cursos com atividade suficiente para compor este quadro.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="rounded-[32px] border border-white/70 bg-white/90 p-6 shadow-[0_20px_60px_-35px_rgba(15,23,42,0.45)]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Agenda') }}</p>
                            <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ __('Próximos eventos') }}</h3>
                        </div>
                        <a href="{{ route('schedule-events.index') }}" class="inline-flex items-center rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">
                            {{ __('Abrir agenda') }}
                        </a>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse ($upcomingEvents as $event)
                            <article class="rounded-[24px] border border-slate-200 bg-slate-50 px-5 py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">{{ __($event->event_type) }}</p>
                                        <h4 class="mt-2 text-lg font-semibold text-slate-900">{{ $event->title }}</h4>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $event->courseClass->name ?? __('Sem turma vinculada') }}
                                            @if ($event->courseClass?->course?->title)
                                                • {{ $event->courseClass->course->title }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="rounded-2xl bg-white px-4 py-3 text-right shadow-sm ring-1 ring-slate-200">
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('Data') }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ optional($event->start_date)->format('d/m/Y') }}</p>
                                        @if ($event->start_time)
                                            <p class="mt-1 text-xs text-slate-500">{{ substr($event->start_time, 0, 5) }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if ($event->location)
                                    <p class="mt-3 text-sm text-slate-600">{{ __('Local: :location', ['location' => $event->location]) }}</p>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">
                                {{ __('Nenhum evento futuro cadastrado no momento.') }}
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr),minmax(0,1fr)]">
                <article class="rounded-[32px] border border-white/70 bg-white/90 p-6 shadow-[0_20px_60px_-35px_rgba(15,23,42,0.45)]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Turmas prioritárias') }}</p>
                            <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ __('Visão rápida das turmas') }}</h3>
                        </div>
                        <a href="{{ route('course-classes.index') }}" class="inline-flex items-center rounded-2xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">
                            {{ __('Gerenciar turmas') }}
                        </a>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @forelse ($classOverview as $courseClass)
                            <article class="rounded-[26px] border border-slate-200 bg-[linear-gradient(180deg,_rgba(255,255,255,1)_0%,_rgba(240,249,255,1)_100%)] p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">{{ $courseClass->course->title ?? __('Curso') }}</p>
                                <h4 class="mt-2 text-xl font-semibold text-slate-900">{{ $courseClass->name }}</h4>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $courseClass->instructor->full_name ?? __('Instrutor nao definido') }}
                                </p>

                                <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-200">
                                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-emerald-500" style="width: {{ $courseClass->completion_rate }}%"></div>
                                </div>

                                <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                                        <p class="text-slate-500">{{ __('Matrículas') }}</p>
                                        <p class="mt-1 text-xl font-semibold text-slate-900">{{ number_format($courseClass->enrollments_count, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                                        <p class="text-slate-500">{{ __('Conclusão') }}</p>
                                        <p class="mt-1 text-xl font-semibold text-slate-900">{{ $courseClass->completion_rate }}%</p>
                                    </div>
                                    <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                                        <p class="text-slate-500">{{ __('Presenças') }}</p>
                                        <p class="mt-1 text-xl font-semibold text-slate-900">{{ number_format($courseClass->attendances_count, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                                        <p class="text-slate-500">{{ __('Ocorrências') }}</p>
                                        <p class="mt-1 text-xl font-semibold text-slate-900">{{ number_format($courseClass->student_annotations_count, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="md:col-span-2 rounded-[26px] border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">
                                {{ __('As turmas vao aparecer aqui assim que houver registros de matricula.') }}
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-[32px] border border-white/70 bg-white/90 p-6 shadow-[0_20px_60px_-35px_rgba(15,23,42,0.45)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Atividade recente') }}</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ __('Certificados emitidos') }}</h3>

                    <div class="mt-6 overflow-hidden rounded-[24px] border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                <tr>
                                    <th class="px-5 py-4">{{ __('Código') }}</th>
                                    <th class="px-5 py-4">{{ __('Aluno') }}</th>
                                    <th class="px-5 py-4">{{ __('Curso') }}</th>
                                    <th class="px-5 py-4">{{ __('Emissão') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($recentCertificates as $certificate)
                                    <tr>
                                        <td class="px-5 py-4 text-sm font-medium text-slate-700">
                                            {{ $certificate->certificate_code ?: '#' . $certificate->id }}
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-slate-900">{{ $certificate->student->full_name ?? ($certificate->student->name ?? '—') }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-slate-600">{{ $certificate->course->title ?? ($certificate->course->name ?? '—') }}</td>
                                        <td class="px-5 py-4 text-sm text-slate-600">
                                            {{ optional($certificate->issue_date)->format('d/m/Y') ?? $certificate->created_at?->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">
                                            {{ __('Nenhum certificado recente encontrado.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Acompanhamento disciplinar') }}</p>
                        <h4 class="mt-2 text-xl font-semibold text-slate-900">{{ __('Últimas anotações') }}</h4>
                        <div class="mt-4 space-y-3">
                            @forelse ($recentAnnotations as $annotation)
                                <article class="rounded-[22px] border border-slate-200 bg-slate-50 px-4 py-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $annotation->student->full_name ?? __('Aluno') }}</p>
                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $annotation->courseClass->name ?? __('Sem turma') }}
                                                @if ($annotation->courseClass?->course?->title)
                                                    • {{ $annotation->courseClass->course->title }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="rounded-full px-3 py-1 text-xs font-semibold {{ $annotation->warning_level >= 3 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ __('Nível :level', ['level' => $annotation->warning_level]) }}
                                        </div>
                                    </div>
                                    <p class="mt-3 text-sm text-slate-600">{{ $annotation->notes ?: __('Sem observações detalhadas.') }}</p>
                                    <p class="mt-3 text-xs uppercase tracking-[0.18em] text-slate-400">{{ optional($annotation->annotation_date)->format('d/m/Y') }}</p>
                                </article>
                            @empty
                                <div class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                    {{ __('Nenhuma anotação registrada recentemente.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </article>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const months = @json($months);
        const certsByMonth = @json($certsByMonth);
        const enrollmentsByMonth = @json($enrollmentsByMonth);
        const enrollmentStatus = @json(array_values($enrollmentStatus));

        const operationsChartElement = document.getElementById('operationsChart');
        const enrollmentStatusChartElement = document.getElementById('enrollmentStatusChart');

        if (operationsChartElement) {
            new Chart(operationsChartElement.getContext('2d'), {
                data: {
                    labels: months,
                    datasets: [
                        {
                            type: 'bar',
                            label: @json(__('Matrículas')),
                            data: enrollmentsByMonth,
                            backgroundColor: 'rgba(34, 211, 238, 0.75)',
                            borderRadius: 10,
                            maxBarThickness: 28,
                        },
                        {
                            type: 'line',
                            label: @json(__('Certificados')),
                            data: certsByMonth,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.2)',
                            tension: 0.35,
                            fill: true,
                            pointBackgroundColor: '#f59e0b',
                            pointBorderColor: '#0f172a',
                            pointRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#cbd5e1'
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.15)'
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        }
                    }
                }
            });
        }

        if (enrollmentStatusChartElement) {
            new Chart(enrollmentStatusChartElement.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: [
                        @json(__('Concluídas')),
                        @json(__('Em andamento')),
                        @json(__('Sem turma'))
                    ],
                    datasets: [{
                        data: enrollmentStatus,
                        backgroundColor: ['#10b981', '#f59e0b', '#64748b'],
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 18,
                                color: '#475569'
                            }
                        }
                    }
                }
            });
        }
    </script>
</x-app-layout>
