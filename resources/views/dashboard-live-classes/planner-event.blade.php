<div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-3 py-3 text-sm">
    <p class="font-semibold text-slate-900">{{ $event['title'] }}</p>
    <p class="mt-1 text-xs text-slate-500">{{ $event['course'] }}</p>
    <div class="mt-3 flex flex-wrap gap-2">
        <span class="rounded-full border px-2 py-1 text-[11px] font-semibold {{ $event['attendance']['classes'] }}">
            {{ $event['attendance']['tag'] }}
        </span>
        @if($event['attendance']['url'])
            <a href="{{ $event['attendance']['url'] }}" class="rounded-full bg-white px-2 py-1 text-[11px] font-semibold text-cyan-700 ring-1 ring-cyan-100">
                {{ __('Presença') }}
            </a>
        @endif
    </div>
</div>
