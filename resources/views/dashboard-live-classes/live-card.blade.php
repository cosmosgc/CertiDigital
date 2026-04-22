<article class="rounded-[28px] border border-cyan-200 bg-gradient-to-br from-cyan-50 via-white to-emerald-50 p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">{{ $card['time'] }}</p>
            <h4 class="mt-2 text-xl font-semibold text-slate-900">{{ $card['title'] }}</h4>
            <p class="mt-1 text-sm text-slate-600">
                {{ $card['course'] }}
                @if($card['instructor'])
                    • {{ $card['instructor'] }}
                @endif
            </p>
            @if($card['location'])
                <p class="mt-2 text-sm text-slate-500">{{ __('Local: :location', ['location' => $card['location']]) }}</p>
            @endif
        </div>
        <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $card['attendance']['classes'] }}">
            {{ $card['attendance']['tag'] }} • {{ $card['attendance']['label'] }}
        </span>
    </div>

    <div class="mt-5 flex flex-wrap gap-2">
        @if($card['attendance']['url'])
            <a href="{{ $card['attendance']['url'] }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                {{ __('Abrir última presença') }}
            </a>
        @endif
        @if($card['class_url'])
            <a href="{{ $card['class_url'] }}" class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200">
                {{ __('Ver turma') }}
            </a>
        @endif
    </div>
</article>
