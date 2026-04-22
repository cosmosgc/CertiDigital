<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-cyan-300">{{ __('Monitor de aulas') }}</p>
                <h2 class="mt-2 text-3xl font-semibold leading-tight text-white">
                    {{ __('Aulas ao vivo e planner semanal') }}
                </h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-300">
                    {{ __('Veja quais turmas estão em aula agora, acompanhe a semana e abra a última sessão de presença em modo somente leitura.') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur">
                    {{ __('Voltar ao painel') }}
                </a>
                <a href="{{ route('schedule-events.index') }}" class="inline-flex items-center rounded-2xl bg-cyan-400 px-4 py-3 text-sm font-semibold text-slate-950 shadow">
                    {{ __('Abrir agenda') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.16),_transparent_34%),linear-gradient(180deg,_#f8fafc_0%,_#eef6ff_52%,_#f8fafc_100%)] py-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 md:grid-cols-3">
                <article class="rounded-[28px] border border-white/80 bg-white/90 p-6 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Agora') }}</p>
                    <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $now->format('H:i') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $now->translatedFormat('d M Y') }}</p>
                </article>
                <article class="rounded-[28px] border border-white/80 bg-white/90 p-6 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Aulas ao vivo') }}</p>
                    <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $liveClassCards->count() }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Eventos semanais que batem com o horário atual.') }}</p>
                </article>
                <article class="rounded-[28px] border border-white/80 bg-white/90 p-6 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Semana') }}</p>
                    <p class="mt-4 text-2xl font-semibold text-slate-900">
                        {{ $weekDays->first()->format('d/m') }} - {{ $weekDays->last()->format('d/m') }}
                    </p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Planner das aulas semanais cadastradas.') }}</p>
                </article>
            </section>

            <section class="rounded-[32px] border border-white/80 bg-white/90 p-6 shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)] backdrop-blur">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">{{ __('Em andamento') }}</p>
                        <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ __('Turmas ao vivo') }}</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600">
                        {{ __('Somente leitura') }}
                    </span>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    @each('dashboard-live-classes.live-card', $liveClassCards, 'card', 'dashboard-live-classes.empty-live')
                </div>
            </section>

            <section class="rounded-[32px] border border-white/80 bg-white/90 p-6 shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)] backdrop-blur">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Planner semanal') }}</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ __('Aulas da semana') }}</h3>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full min-w-[860px] border-separate border-spacing-0">
                        <thead>
                            <tr>
                                <th class="sticky left-0 z-10 border-b border-r border-slate-200 bg-slate-50 px-3 py-3 text-left text-sm font-semibold text-slate-600">{{ __('Horário') }}</th>
                                @each('dashboard-live-classes.week-header', $weekHeaders, 'header')
                            </tr>
                        </thead>
                        <tbody>
                            @each('dashboard-live-classes.planner-row', $plannerRows, 'row', 'dashboard-live-classes.empty-planner')
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
