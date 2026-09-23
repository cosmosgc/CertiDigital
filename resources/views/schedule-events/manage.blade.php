@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden p-6 shadow-sm sm:rounded-2xl">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <a href="{{ route('schedule-events.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">{{ __('Voltar para agenda') }}</a>
                <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Gerenciar agenda') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Limpe dias de feriado em massa: exclua as presenças do dia. Aulas semanais são ocultadas do planner nos dias com feriado cadastrado.') }}</p>
            </div>
            <span id="manageStatus" class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400"></span>
        </div>

        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50/60 p-4 sm:p-5">
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ __('Gerenciar dia / feriado') }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ __('Escolha um dia para ver tudo que cai nele (aulas recorrentes, eventos avulsos e presenças) e exclua em massa.') }}</p>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,220px)_minmax(0,1fr)_auto]">
                <div>
                    <label for="dayManagerDate" class="block text-sm font-medium text-gray-700">{{ __('Dia') }}</label>
                    <input id="dayManagerDate" type="date" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="dayManagerHolidayTitle" class="block text-sm font-medium text-gray-700">{{ __('Título do feriado (para ocultar aulas do planner)') }}</label>
                    <input id="dayManagerHolidayTitle" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" placeholder="{{ __('Ex.: Natal') }}" />
                </div>
                <div class="flex flex-wrap items-end gap-2">
                    <button id="dayManagerSearchBtn" type="button" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white">{{ __('Buscar dia') }}</button>
                    <button id="dayManagerCreateHolidayBtn" type="button" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white">{{ __('Cadastrar feriado') }}</button>
                </div>
            </div>

            <div id="dayManagerResults" class="mt-4 hidden grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                    <p class="text-sm font-semibold text-slate-900">{{ __('Eventos da agenda neste dia') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Apenas informativo — eventos da agenda não são excluídos por aqui. Para ocultar aulas semanais do planner, cadastre o feriado acima.') }}</p>
                    <div id="dayManagerEventsList" class="mt-3 max-h-72 space-y-2 overflow-y-auto"></div>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Presenças / chamadas neste dia') }}</p>
                        <label class="flex items-center gap-2 text-xs text-slate-500">
                            <input id="dayManagerSelectAllAttendances" type="checkbox" class="rounded border-gray-300 text-sky-600 shadow-sm" />
                            {{ __('Todas') }}
                        </label>
                    </div>
                    <div id="dayManagerAttendancesList" class="mt-3 max-h-72 space-y-2 overflow-y-auto"></div>
                    <button id="dayManagerDeleteAttendancesBtn" type="button" class="mt-3 w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-medium text-white disabled:opacity-50">{{ __('Excluir presenças selecionadas') }}</button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = @json(auth()->id());
const manageStatus = document.getElementById('manageStatus');
let courseClasses = [];
let scheduleEvents = [];

const typeLabels = {
    weekly_class: @json(__('Aula semanal')),
    exam: @json(__('Prova')),
    holiday: @json(__('Feriado')),
    meeting: @json(__('Reunião')),
    deadline: @json(__('Prazo')),
    other: @json(__('Outro')),
};

async function fetchAllPages(url) {
    const items = [];
    let nextUrl = url;

    while (nextUrl) {
        const res = await fetch(nextUrl, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        items.push(...(data.data || []));
        nextUrl = data.next_page_url;
    }

    return items;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function normalizeDateValue(dateValue) {
    if (!dateValue) {
        return '';
    }

    return typeof dateValue === 'string' ? dateValue.slice(0, 10) : dateValue;
}

function parseLocalDate(dateValue) {
    const normalized = normalizeDateValue(dateValue);

    if (!normalized) {
        return null;
    }

    const parsedDate = new Date(`${normalized}T00:00:00`);

    return Number.isNaN(parsedDate.getTime()) ? null : parsedDate;
}

function toIsoDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function endOfYearIso(dateValue) {
    const parsedDate = parseLocalDate(dateValue);

    if (!parsedDate) {
        return null;
    }

    return `${parsedDate.getFullYear()}-12-31`;
}

function eventOccursOnDate(item, date) {
    const dateOnly = toIsoDate(date);
    const startDate = normalizeDateValue(item.start_date);
    const endDate = normalizeDateValue(item.end_date);

    if (!startDate) {
        return false;
    }

    if (item.is_recurring_weekly) {
        const recurrenceEndDate = endDate && endDate !== startDate
            ? endDate
            : endOfYearIso(startDate);
        const weekday = Number.isInteger(item.weekday)
            ? item.weekday
            : (parseLocalDate(item.start_date)?.getDay() ?? null);

        if (weekday === null) {
            return false;
        }

        return dateOnly >= startDate
            && (!recurrenceEndDate || dateOnly <= recurrenceEndDate)
            && weekday === date.getDay();
    }

    return dateOnly >= startDate && (!endDate || dateOnly <= endDate);
}

function getDayManagerDate() {
    return document.getElementById('dayManagerDate')?.value || '';
}

function getAttendancesOnDate(dateIso) {
    if (!dateIso) {
        return [];
    }

    const rows = [];
    (courseClasses || []).forEach(courseClass => {
        (courseClass.attendances || []).forEach(attendance => {
            if (normalizeDateValue(attendance.attendance_date) === dateIso) {
                rows.push({ ...attendance, course_class_name: courseClass.name });
            }
        });
    });

    return rows.sort((a, b) => String(a.course_class_name || '').localeCompare(String(b.course_class_name || '')));
}

function refreshDayManager() {
    const dateInput = document.getElementById('dayManagerDate');
    const results = document.getElementById('dayManagerResults');
    const eventsList = document.getElementById('dayManagerEventsList');
    const attendancesList = document.getElementById('dayManagerAttendancesList');
    if (!dateInput || !results || !eventsList || !attendancesList) {
        return;
    }

    const dateIso = getDayManagerDate();
    if (!dateIso) {
        results.classList.add('hidden');
        return;
    }

    const parsed = parseLocalDate(dateIso);
    const dayEvents = parsed ? scheduleEvents.filter(item => eventOccursOnDate(item, parsed)) : [];
    const dayAttendances = getAttendancesOnDate(dateIso);

    results.classList.remove('hidden');

    if (manageStatus) {
        manageStatus.textContent = `${dayEvents.length} ${@json(__('evento(s)'))} • ${dayAttendances.length} ${@json(__('presença(s) no dia'))}`;
    }

    eventsList.innerHTML = dayEvents.length ? dayEvents.map(item => `
        <div class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm">
            <span class="block truncate font-medium text-slate-900">${escapeHtml(item.title || '')}</span>
            <span class="block text-xs text-slate-500">${escapeHtml(typeLabels[item.event_type] || item.event_type || '')}${item.course_class?.name ? ` • ${escapeHtml(item.course_class.name)}` : ''}${item.is_recurring_weekly ? ` • ${@json(__('recorrente'))}` : ''}</span>
        </div>
    `).join('') : `<p class="rounded-xl bg-gray-50 px-3 py-4 text-center text-sm text-gray-500">{{ __('Nenhum evento neste dia.') }}</p>`;

    attendancesList.innerHTML = dayAttendances.length ? dayAttendances.map(attendance => `
        <label class="flex items-start gap-3 rounded-xl border border-gray-200 px-3 py-2.5 text-sm hover:bg-gray-50">
            <input type="checkbox" class="dmAttendanceCheck mt-1 rounded border-gray-300 text-rose-600 shadow-sm" value="${attendance.id}" checked />
            <span class="min-w-0">
                <span class="block truncate font-medium text-slate-900">${escapeHtml(attendance.course_class_name || '')} • ${escapeHtml(attendance.name || '')}</span>
                <span class="block text-xs text-slate-500">${(attendance.records || []).length} ${@json(__('registros de alunos'))}</span>
            </span>
        </label>
    `).join('') : `<p class="rounded-xl bg-gray-50 px-3 py-4 text-center text-sm text-gray-500">{{ __('Nenhuma presença neste dia.') }}</p>`;

    const selectAllAttendances = document.getElementById('dayManagerSelectAllAttendances');
    if (selectAllAttendances) {
        selectAllAttendances.checked = dayAttendances.length > 0;
    }
}

async function bulkDestroyAttendances(ids) {
    const res = await fetch('{{ route("api.course-class-attendances.bulk-destroy") }}', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids, user_id: currentUserId })
    });

    if (!res.ok) {
        const error = await res.json().catch(() => null);
        throw new Error(error?.message || @json(__('Erro ao excluir presenças')));
    }

    return res.json();
}

async function reloadAll() {
    courseClasses = await fetchAllPages('{{ route("api.course-classes.index") }}');
    scheduleEvents = await fetchAllPages('{{ route("api.schedule-events.index") }}');
    refreshDayManager();
}

document.getElementById('dayManagerSearchBtn')?.addEventListener('click', refreshDayManager);
document.getElementById('dayManagerDate')?.addEventListener('change', refreshDayManager);

document.getElementById('dayManagerSelectAllAttendances')?.addEventListener('change', (e) => {
    document.querySelectorAll('.dmAttendanceCheck').forEach(cb => {
        cb.checked = e.target.checked;
    });
});

document.getElementById('dayManagerDeleteAttendancesBtn')?.addEventListener('click', async () => {
    const ids = Array.from(document.querySelectorAll('.dmAttendanceCheck:checked')).map(cb => Number(cb.value));
    if (!ids.length) {
        alert(@json(__('Selecione ao menos uma presença.')));
        return;
    }
    if (!confirm(@json(__('Excluir as presenças deste dia? O progresso dos alunos será recalculado.')))) {
        return;
    }

    try {
        const result = await bulkDestroyAttendances(ids);
        alert(`${result.deleted ?? ids.length} ${@json(__('presença(s) excluída(s).'))}`);
        await reloadAll();
    } catch (error) {
        alert(error.message);
    }
});

document.getElementById('dayManagerCreateHolidayBtn')?.addEventListener('click', async () => {
    const dateIso = getDayManagerDate();
    if (!dateIso) {
        alert(@json(__('Escolha o dia primeiro.')));
        return;
    }

    const titleInput = document.getElementById('dayManagerHolidayTitle');
    const title = titleInput?.value?.trim() || @json(__('Feriado'));

    try {
        const res = await fetch('{{ route("api.schedule-events.store") }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-User-Id': currentUserId,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title,
                event_type: 'holiday',
                start_date: dateIso,
                end_date: dateIso,
                is_all_day: true,
                is_recurring_weekly: false,
                description: @json(__('Dia sem aula — aulas semanais ocultadas do planner.')),
                user_id: currentUserId
            })
        });

        if (!res.ok) {
            const error = await res.json().catch(() => null);
            throw new Error(error?.message || @json(__('Erro ao cadastrar feriado')));
        }

        if (titleInput) {
            titleInput.value = '';
        }
        alert(@json(__('Feriado cadastrado. Aulas semanais deste dia serão ocultadas do planner.')));
        await reloadAll();
    } catch (error) {
        alert(error.message);
    }
});

async function init() {
    const dayInput = document.getElementById('dayManagerDate');
    if (dayInput && !dayInput.value) {
        dayInput.value = toIsoDate(new Date());
    }
    await reloadAll();
}

init();
</script>
@endsection
