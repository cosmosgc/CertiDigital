<tr>
    <td class="sticky left-0 z-10 border-b border-r border-slate-200 bg-white px-3 py-4 align-top text-sm font-medium text-slate-700">
        {{ $row['slot'] }}
    </td>
    @each('dashboard-live-classes.planner-cell', $row['cells'], 'cell')
</tr>
