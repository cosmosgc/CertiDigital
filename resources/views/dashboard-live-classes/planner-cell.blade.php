<td class="border-b border-slate-100 px-2 py-3 align-top">
    <div class="flex min-h-[96px] flex-col gap-2">
        @each('dashboard-live-classes.planner-event', $cell['events'], 'event', 'dashboard-live-classes.planner-free')
    </div>
</td>
