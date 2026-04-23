@extends('layouts.app')

@section('content')
@php($canManageScheduleEvents = auth()->user()?->hasRole('admin'))
<style>
@page {
    size: A4 landscape;
    margin: 10mm;
}

@media print {
    body.print-planner .print-hide,
    body.print-planner nav,
    body.print-planner #formContainer,
    body.print-planner #scheduleSummarySection,
    body.print-planner #calendarSection,
    body.print-planner #scheduleListSection {
        display: none !important;
    }

    body.print-planner #plannerSection {
        display: block !important;
    }

    body.print-calendar .print-hide,
    body.print-calendar nav,
    body.print-calendar #formContainer,
    body.print-calendar #scheduleSummarySection,
    body.print-calendar #plannerSection,
    body.print-calendar #scheduleListSection {
        display: none !important;
    }

    body.print-calendar #calendarSection {
        display: block !important;
    }

    body.print-calendar {
        font-size: 10px !important;
    }

    body.print-planner main,
    body.print-calendar main {
        padding: 0 !important;
        margin: 0 !important;
    }

    body.print-planner #plannerSection,
    body.print-calendar #calendarSection {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    body.print-calendar #calendarSection h3 {
        font-size: 16px !important;
        line-height: 1.2 !important;
    }

    body.print-calendar #calendarSection p {
        font-size: 10px !important;
        line-height: 1.2 !important;
        margin-top: 2px !important;
    }

    body.print-calendar #calendarSection .grid.grid-cols-7.gap-1,
    body.print-calendar #calendarSection .grid.grid-cols-7.gap-1.sm\:gap-2,
    body.print-calendar #calendarSection .mt-4.grid.grid-cols-7 {
        gap: 4px !important;
    }

    body.print-calendar #calendarGrid > div {
        min-height: 86px !important;
        padding: 4px !important;
        border-radius: 10px !important;
        break-inside: avoid !important;
    }

    body.print-calendar #calendarGrid > div > div:first-child span:first-child {
        font-size: 10px !important;
    }

    body.print-calendar #calendarGrid .rounded-lg {
        padding: 2px 4px !important;
        font-size: 8px !important;
        line-height: 1.15 !important;
    }

    body.print-calendar #calendarGrid .space-y-1 > * + * {
        margin-top: 2px !important;
    }

    body.print-calendar #calendarGrid .mt-1\.5,
    body.print-calendar #calendarGrid .sm\:mt-2 {
        margin-top: 4px !important;
    }
}
</style>
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden p-6 shadow-sm sm:rounded-2xl">
        <div class="print-hide flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">{{ __('Calendário acadêmico') }}</span>
                <h2 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Agenda e datas especiais') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Cadastre aulas semanais, provas, reuniões, feriados e outros compromissos importantes em um só lugar.') }}</p>
            </div>
            @if($canManageScheduleEvents)
                <button id="showCreate" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-3 font-medium text-white shadow-sm transition hover:bg-sky-700">{{ __('Novo evento') }}</button>
            @endif
        </div>

        <div class="print-hide mt-6 rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
            <div class="mb-5 rounded-2xl border border-slate-200 bg-slate-50/80 p-4 sm:p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ __('Turmas ativas') }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Clique nas turmas para filtrar a agenda. Clique novamente para remover uma turma do filtro.') }}</p>
                    </div>
                    <div id="courseClassGridStatus" class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400"></div>
                </div>

                <div id="courseClassGrid" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3"></div>
            </div>

            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                <div>
                    <label for="courseClassFilter" class="block text-sm font-medium text-gray-700">{{ __('Filtrar por turma') }}</label>
                    <select id="courseClassFilter" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" multiple>
                        <option value="">{{ __('Todas as turmas') }}</option>
                    </select>
                </div>
                <div>
                    <label for="eventTypeFilter" class="block text-sm font-medium text-gray-700">{{ __('Filtrar por tipo') }}</label>
                    <select id="eventTypeFilter" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                        <option value="">{{ __('Todos os tipos') }}</option>
                        <option value="weekly_class">{{ __('Aula semanal') }}</option>
                        <option value="exam">{{ __('Prova') }}</option>
                        <option value="holiday">{{ __('Feriado') }}</option>
                        <option value="meeting">{{ __('Reunião') }}</option>
                        <option value="deadline">{{ __('Prazo') }}</option>
                        <option value="other">{{ __('Outro') }}</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button id="applyFiltersBtn" type="button" class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-medium text-white">{{ __('Aplicar') }}</button>
                    <button id="resetFiltersBtn" type="button" class="rounded-xl bg-white px-4 py-2.5 text-sm text-gray-700 ring-1 ring-gray-300">{{ __('Limpar') }}</button>
                </div>
            </div>
        </div>

        <div id="scheduleSummarySection" class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                <p class="text-sm font-medium text-sky-700">{{ __('Aulas semanais') }}</p>
                <p id="weeklyCount" class="mt-2 text-3xl font-semibold text-sky-950">0</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                <p class="text-sm font-medium text-amber-700">{{ __('Provas e prazos') }}</p>
                <p id="specialCount" class="mt-2 text-3xl font-semibold text-amber-950">0</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                <p class="text-sm font-medium text-emerald-700">{{ __('Próximos eventos') }}</p>
                <p id="upcomingCount" class="mt-2 text-3xl font-semibold text-emerald-950">0</p>
            </div>
        </div>

        <div class="mt-6 space-y-6">
            <div id="plannerSection" class="min-w-0 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Planner semanal') }}</h3>
                        <p id="weekRangeLabel" class="mt-1 text-sm text-gray-500"></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button id="prevWeekBtn" type="button" class="rounded-xl bg-white px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-300">{{ __('Semana anterior') }}</button>
                        <button id="currentWeekBtn" type="button" class="rounded-xl bg-sky-50 px-3 py-2 text-sm font-medium text-sky-700 ring-1 ring-sky-200">{{ __('Semana atual') }}</button>
                        <button id="nextWeekBtn" type="button" class="rounded-xl bg-white px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-300">{{ __('Próxima semana') }}</button>
                        <button id="printPlannerBtn" type="button" class="print-hide rounded-xl bg-gray-900 px-3 py-2 text-sm font-medium text-white">{{ __('Imprimir planner') }}</button>
                    </div>
                </div>

                <div class="mt-4 max-w-full overflow-x-auto">
                    <table class="w-full min-w-[760px] border-separate border-spacing-0" id="weeklyPlannerTable">
                        <thead id="weeklyPlannerHead"></thead>
                        <tbody id="weeklyPlannerBody"></tbody>
                    </table>
                </div>
            </div>

            <div id="calendarSection" class="min-w-0 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <button id="prevMonthBtn" type="button" class="rounded-xl bg-white px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-300">{{ __('Anterior') }}</button>
                    <div class="text-center">
                        <h3 id="calendarMonthLabel" class="text-lg font-semibold text-gray-900"></h3>
                        <p class="text-sm text-gray-500">{{ __('Calendário do mês') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button id="printCalendarBtn" type="button" class="print-hide rounded-xl bg-gray-900 px-3 py-2 text-sm font-medium text-white">{{ __('Imprimir calendário') }}</button>
                        <button id="nextMonthBtn" type="button" class="rounded-xl bg-white px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-300">{{ __('Próximo') }}</button>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-7 gap-1 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-400 sm:gap-2 sm:text-xs sm:tracking-[0.16em]">
                    <div>{{ __('Seg') }}</div>
                    <div>{{ __('Ter') }}</div>
                    <div>{{ __('Qua') }}</div>
                    <div>{{ __('Qui') }}</div>
                    <div>{{ __('Sex') }}</div>
                    <div>{{ __('Sáb') }}</div>
                    <div>{{ __('Dom') }}</div>
                </div>

                <div id="calendarGrid" class="mt-3 grid grid-cols-7 gap-1 sm:gap-2"></div>
            </div>
        </div>

        <div id="scheduleListSection" class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="w-full table-auto" id="scheduleEventsTable">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-600">
                        <th class="p-2">{{ __('Evento') }}</th>
                        <th class="p-2">{{ __('Tipo') }}</th>
                        <th class="p-2">{{ __('Turma') }}</th>
                        <th class="p-2">{{ __('Data / horário') }}</th>
                        <th class="p-2">{{ __('Recorrência') }}</th>
                        @if($canManageScheduleEvents)
                            <th class="p-2">{{ __('Ações') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        @if($canManageScheduleEvents)
            <div id="formContainer" class="mt-4 hidden">
                <div class="rounded-2xl border border-dashed border-sky-300 bg-sky-50/70 p-5">
                    <h3 id="formTitle" class="text-lg font-semibold text-gray-900"></h3>
                    <form id="scheduleEventForm" class="mt-4 space-y-4">
                        <input type="hidden" name="id" />

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Título') }}</label>
                                <input name="title" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" placeholder="{{ __('Ex.: Prova final de inglês') }}" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Tipo de evento') }}</label>
                                <select name="event_type" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                                    <option value="weekly_class">{{ __('Aula semanal') }}</option>
                                    <option value="exam">{{ __('Prova') }}</option>
                                    <option value="holiday">{{ __('Feriado') }}</option>
                                    <option value="meeting">{{ __('Reunião') }}</option>
                                    <option value="deadline">{{ __('Prazo') }}</option>
                                    <option value="other">{{ __('Outro') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Turma vinculada') }}</label>
                                <select name="course_class_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Local') }}</label>
                                <input name="location" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" placeholder="{{ __('Sala 2 ou link da aula') }}" />
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Data inicial') }}</label>
                                <input name="start_date" type="date" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Data final') }}</label>
                                <input name="end_date" type="date" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Hora inicial') }}</label>
                                <input name="start_time" type="time" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Hora final') }}</label>
                                <input name="end_time" type="time" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Dia da semana') }}</label>
                                <select name="weekday" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                                    <option value="">{{ __('Sem recorrência') }}</option>
                                    <option value="0">{{ __('Domingo') }}</option>
                                    <option value="1">{{ __('Segunda-feira') }}</option>
                                    <option value="2">{{ __('Terça-feira') }}</option>
                                    <option value="3">{{ __('Quarta-feira') }}</option>
                                    <option value="4">{{ __('Quinta-feira') }}</option>
                                    <option value="5">{{ __('Sexta-feira') }}</option>
                                    <option value="6">{{ __('Sábado') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700">
                                <input name="is_recurring_weekly" type="checkbox" class="rounded border-gray-300 text-sky-600 shadow-sm focus:ring-sky-500" />
                                <span>{{ __('Repetir toda semana') }}</span>
                            </label>
                            <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700">
                                <input name="is_all_day" type="checkbox" class="rounded border-gray-300 text-sky-600 shadow-sm focus:ring-sky-500" />
                                <span>{{ __('Evento de dia inteiro') }}</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Descrição') }}</label>
                            <textarea name="description" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" placeholder="{{ __('Observações, conteúdo da aula, instruções da prova, etc.') }}"></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="rounded-xl bg-sky-600 px-4 py-2 text-white">{{ __('Salvar') }}</button>
                            <button type="button" id="cancelBtn" class="rounded-xl bg-white px-4 py-2 text-gray-700 ring-1 ring-gray-300">{{ __('Cancelar') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = @json(auth()->id());
const canManageScheduleEvents = @json($canManageScheduleEvents);
const tableBody = document.querySelector('#scheduleEventsTable tbody');
const formContainer = document.getElementById('formContainer');
const scheduleEventForm = document.getElementById('scheduleEventForm');
const formTitle = document.getElementById('formTitle');
const weeklyCount = document.getElementById('weeklyCount');
const specialCount = document.getElementById('specialCount');
const upcomingCount = document.getElementById('upcomingCount');
const courseClassFilter = document.getElementById('courseClassFilter');
const courseClassGrid = document.getElementById('courseClassGrid');
const courseClassGridStatus = document.getElementById('courseClassGridStatus');
const eventTypeFilter = document.getElementById('eventTypeFilter');
const weekRangeLabel = document.getElementById('weekRangeLabel');
const weeklyPlannerHead = document.getElementById('weeklyPlannerHead');
const weeklyPlannerBody = document.getElementById('weeklyPlannerBody');
const calendarMonthLabel = document.getElementById('calendarMonthLabel');
const calendarGrid = document.getElementById('calendarGrid');
let scheduleEventsDataTable = null;
let courseClasses = [];
let scheduleEvents = [];
let currentWeekDate = startOfWeek(new Date());
let currentMonthDate = new Date();
const initialFilters = {
    course_class_ids: @json(request('course_class_ids', request('course_class_id', []))),
    event_type: @json(request('event_type', '')),
};

const courseClassManageBaseUrl = @json(route('course-classes.show', ['courseClass' => '__ID__']));

const typeLabels = {
    weekly_class: @json(__('Aula semanal')),
    exam: @json(__('Prova')),
    holiday: @json(__('Feriado')),
    meeting: @json(__('Reunião')),
    deadline: @json(__('Prazo')),
    other: @json(__('Outro')),
};

const weekdayLabels = [
    @json(__('Domingo')),
    @json(__('Segunda-feira')),
    @json(__('Terça-feira')),
    @json(__('Quarta-feira')),
    @json(__('Quinta-feira')),
    @json(__('Sexta-feira')),
    @json(__('Sábado')),
];

const weekdayLabelsMondayFirst = [
    @json(__('Segunda-feira')),
    @json(__('Terça-feira')),
    @json(__('Quarta-feira')),
    @json(__('Quinta-feira')),
    @json(__('Sexta-feira')),
    @json(__('Sábado')),
    @json(__('Domingo')),
];

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

function getFirstName(fullName) {
    return String(fullName || '').trim().split(/\s+/).filter(Boolean)[0] || '';
}

function getCourseClassModality(courseClass) {
    return courseClass?.course?.modality || '';
}

function getCourseClassTone(modality) {
    const tones = {
        online: {
            chip: 'bg-sky-100 text-sky-700',
            card: 'border-sky-200 bg-sky-50/70 hover:border-sky-300 hover:bg-sky-100/70',
            accent: 'bg-sky-500',
            selected: 'border-sky-500 bg-sky-100 shadow-sky-100 ring-sky-200',
        },
        in_person: {
            chip: 'bg-emerald-100 text-emerald-700',
            card: 'border-emerald-200 bg-emerald-50/70 hover:border-emerald-300 hover:bg-emerald-100/70',
            accent: 'bg-emerald-500',
            selected: 'border-emerald-500 bg-emerald-100 shadow-emerald-100 ring-emerald-200',
        },
        hybrid: {
            chip: 'bg-amber-100 text-amber-700',
            card: 'border-amber-200 bg-amber-50/70 hover:border-amber-300 hover:bg-amber-100/80',
            accent: 'bg-amber-500',
            selected: 'border-amber-500 bg-amber-100 shadow-amber-100 ring-amber-200',
        },
        default: {
            chip: 'bg-slate-100 text-slate-700',
            card: 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50',
            accent: 'bg-slate-500',
            selected: 'border-slate-500 bg-slate-50 shadow-slate-100 ring-slate-200',
        },
    };

    return tones[modality] || tones.default;
}

function getCourseClassModalityLabel(modality) {
    const labels = {
        online: @json(__('Online')),
        in_person: @json(__('Presencial')),
        hybrid: @json(__('Híbrido')),
    };

    return labels[modality] || @json(__('Turma'));
}

function getCourseClassStudentsCount(courseClass) {
    return Array.isArray(courseClass?.students) ? courseClass.students.length : 0;
}

function getCourseClassAttendancesCount(courseClass) {
    return Array.isArray(courseClass?.attendances) ? courseClass.attendances.length : 0;
}

function populateCourseClassOptions(selectedId = '') {
    if (!scheduleEventForm) {
        return;
    }

    scheduleEventForm.course_class_id.innerHTML = `
        <option value="">{{ __('Sem turma vinculada') }}</option>
        ${courseClasses.map(courseClass => `
            <option value="${courseClass.id}" ${String(courseClass.id) === String(selectedId) ? 'selected' : ''}>
                ${courseClass.name} ${courseClass.course?.title ? `- ${courseClass.course.title}` : ''}
            </option>
        `).join('')}
    `;
}

function normalizeSelectedClassIds(value = []) {
    const values = Array.isArray(value) ? value : String(value || '').split(',');

    return values
        .map(item => String(item || '').trim())
        .filter(Boolean);
}

function getSelectedCourseClassIds() {
    if (!courseClassFilter) {
        return [];
    }

    return Array.from(courseClassFilter.selectedOptions)
        .map(option => option.value)
        .filter(Boolean);
}

function setSelectedCourseClassIds(selectedIds = []) {
    const normalizedIds = normalizeSelectedClassIds(selectedIds);

    Array.from(courseClassFilter.options).forEach(option => {
        option.selected = normalizedIds.includes(String(option.value));
    });
}

function populateCourseClassFilterOptions(selectedIds = []) {
    const normalizedIds = normalizeSelectedClassIds(selectedIds);

    courseClassFilter.innerHTML = `
        <option value="">{{ __('Todas as turmas') }}</option>
        ${courseClasses.map(courseClass => `
            <option value="${courseClass.id}" ${normalizedIds.includes(String(courseClass.id)) ? 'selected' : ''}>
                ${courseClass.name} ${courseClass.course?.title ? `- ${courseClass.course.title}` : ''}
            </option>
        `).join('')}
    `;
}

function renderCourseClassGrid(selectedIds = []) {
    if (!courseClassGrid || !courseClassGridStatus) {
        return;
    }

    const normalizedIds = normalizeSelectedClassIds(selectedIds);
    const selectedClasses = courseClasses.filter(courseClass => normalizedIds.includes(String(courseClass.id)));
    courseClassGridStatus.textContent = selectedClasses.length
        ? `${selectedClasses.length} ${selectedClasses.length === 1 ? @json(__('ativa')) : @json(__('ativas'))}`
        : @json(__('Todas visíveis'));

    if (!courseClasses.length) {
        courseClassGrid.innerHTML = `
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
                {{ __('Nenhuma turma disponível para seleção.') }}
            </div>
        `;
        return;
    }

    courseClassGrid.innerHTML = courseClasses.map(courseClass => {
        const isSelected = normalizedIds.includes(String(courseClass.id));
        const modality = getCourseClassModality(courseClass);
        const tone = getCourseClassTone(modality);
        const studentsCount = getCourseClassStudentsCount(courseClass);
        const attendancesCount = getCourseClassAttendancesCount(courseClass);
        const instructorFullName = courseClass.instructor?.full_name || @json(__('Instrutor não definido'));
        const instructorFirstName = getFirstName(instructorFullName) || @json(__('Sem instrutor'));
        const courseTitle = courseClass.course?.title || @json(__('Curso não informado'));
        const description = courseClass.description || @json(__('Sem observações adicionais.'));

        return `
            <button
                type="button"
                class="course-class-card group relative overflow-visible rounded-2xl border p-4 text-left shadow-sm transition duration-200 ${tone.card} ${isSelected ? `${tone.selected} ring-2` : ''}"
                data-id="${courseClass.id}"
                aria-pressed="${isSelected ? 'true' : 'false'}"
            >
                <span class="absolute inset-y-4 left-0 w-1 rounded-r-full ${tone.accent}"></span>
                <div class="pl-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="truncate text-base font-semibold text-slate-900">${escapeHtml(courseClass.name || '')}</div>
                            <div class="mt-1 truncate text-sm text-slate-500">${escapeHtml(courseTitle)}</div>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] ${tone.chip}">
                            ${escapeHtml(getCourseClassModalityLabel(modality))}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl bg-white/80 px-3 py-2 text-slate-600 ring-1 ring-black/5">
                            <div class="font-semibold text-slate-900">${studentsCount}</div>
                            <div>{{ __('Alunos') }}</div>
                        </div>
                        <div class="rounded-xl bg-white/80 px-3 py-2 text-slate-600 ring-1 ring-black/5">
                            <div class="font-semibold text-slate-900">${attendancesCount}</div>
                            <div>{{ __('Presenças') }}</div>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                        <span class="truncate">{{ __('Instrutor') }}: <span class="font-medium text-slate-700">${escapeHtml(instructorFirstName)}</span></span>
                        <span class="font-medium text-slate-400">${isSelected ? @json(__('Selecionada')) : @json(__('Filtrar'))}</span>
                    </div>
                </div>

                <div class="pointer-events-none absolute left-4 right-4 top-full z-20 mt-2 hidden rounded-2xl border border-slate-200 bg-slate-950/95 p-4 text-sm text-slate-100 shadow-2xl group-hover:block group-focus:block">
                    <div class="font-semibold text-white">${escapeHtml(courseClass.name || '')}</div>
                    <div class="mt-1 text-slate-300">${escapeHtml(courseTitle)}</div>
                    <div class="mt-3 grid gap-2 text-xs text-slate-200">
                        <div><span class="text-slate-400">{{ __('Instrutor') }}:</span> ${escapeHtml(instructorFullName)}</div>
                        <div><span class="text-slate-400">{{ __('Alunos') }}:</span> ${studentsCount}</div>
                        <div><span class="text-slate-400">{{ __('Registros de presença') }}:</span> ${attendancesCount}</div>
                        <div><span class="text-slate-400">{{ __('Descrição') }}:</span> ${escapeHtml(description)}</div>
                    </div>
                </div>
            </button>
        `;
    }).join('');
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

function addDays(date, amount) {
    const next = new Date(date);
    next.setDate(next.getDate() + amount);
    return next;
}

function endOfYearIso(dateValue) {
    const parsedDate = parseLocalDate(dateValue);

    if (!parsedDate) {
        return null;
    }

    return `${parsedDate.getFullYear()}-12-31`;
}

function startOfWeek(date) {
    const normalized = new Date(date);
    normalized.setHours(0, 0, 0, 0);
    const day = normalized.getDay();
    const diff = day === 0 ? -6 : 1 - day;

    return addDays(normalized, diff);
}

function formatDate(dateValue) {
    const parsedDate = parseLocalDate(dateValue);

    if (!parsedDate) {
        return '';
    }

    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short' }).format(parsedDate);
}

function formatTime(value) {
    return value ? value.slice(0, 5) : '';
}

function formatDateRange(item) {
    const start = formatDate(item.start_date);
    const end = item.end_date && item.end_date !== item.start_date ? formatDate(item.end_date) : '';
    const timeRange = item.is_all_day
        ? @json(__('Dia inteiro'))
        : [formatTime(item.start_time), formatTime(item.end_time)].filter(Boolean).join(' - ');

    return [end ? `${start} - ${end}` : start, timeRange].filter(Boolean).join('<br>');
}

function formatRecurrence(item) {
    if (!item.is_recurring_weekly) {
        return @json(__('Evento avulso'));
    }

    const weekday = Number.isInteger(item.weekday) ? weekdayLabels[item.weekday] : '';
    return weekday ? `${@json(__('Semanal'))} • ${weekday}` : @json(__('Semanal'));
}

function updateSummaryCards(list) {
    const now = startOfWeek(new Date());
    const weeklyItems = list.filter(item => item.is_recurring_weekly).length;
    const specialItems = list.filter(item => ['exam', 'holiday', 'meeting', 'deadline'].includes(item.event_type)).length;
    const upcomingItems = list.filter(item => {
        const parsedDate = parseLocalDate(item.start_date);

        return parsedDate && parsedDate >= now;
    }).length;

    weeklyCount.textContent = weeklyItems;
    specialCount.textContent = specialItems;
    upcomingCount.textContent = upcomingItems;
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

function eventTimeLabel(item) {
    if (item.is_all_day) {
        return @json(__('Dia inteiro'));
    }

    const start = formatTime(item.start_time) || '--:--';
    const end = formatTime(item.end_time);

    return end ? `${start} - ${end}` : start;
}

function eventSortValue(item) {
    return item.is_all_day ? '00:00' : (formatTime(item.start_time) || '23:59');
}

function getBadgeClasses(eventType) {
    const palette = {
        weekly_class: 'border-sky-200 bg-sky-50 text-sky-700',
        exam: 'border-rose-200 bg-rose-50 text-rose-700',
        holiday: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        meeting: 'border-violet-200 bg-violet-50 text-violet-700',
        deadline: 'border-amber-200 bg-amber-50 text-amber-700',
        other: 'border-gray-200 bg-gray-50 text-gray-700',
    };

    return palette[eventType] || palette.other;
}

function renderWeeklyPlanner(list) {
    const weekDates = Array.from({ length: 7 }, (_, index) => addDays(currentWeekDate, index));
    const plannerEvents = list.filter(item => weekDates.some(date => eventOccursOnDate(item, date)));
    const timeSlots = Array.from(new Set(plannerEvents.map(eventTimeLabel))).sort((a, b) => {
        if (a === @json(__('Dia inteiro'))) return -1;
        if (b === @json(__('Dia inteiro'))) return 1;
        return a.localeCompare(b);
    });

    weekRangeLabel.textContent = `${formatDate(weekDates[0])} - ${formatDate(weekDates[6])}`;

    weeklyPlannerHead.innerHTML = `
        <tr>
            <th class="sticky left-0 z-10 border-b border-r border-gray-200 bg-gray-50 px-3 py-3 text-left text-sm font-semibold text-gray-600">{{ __('Horário') }}</th>
            ${weekDates.map((date, index) => `
                <th class="min-w-[96px] border-b border-gray-200 bg-gray-50 px-2 py-3 text-left sm:min-w-[120px] sm:px-3">
                    <div class="text-sm font-semibold text-gray-700">${weekdayLabelsMondayFirst[index]}</div>
                    <div class="text-xs text-gray-500">${formatDate(toIsoDate(date))}</div>
                </th>
            `).join('')}
        </tr>
    `;

    if (!timeSlots.length) {
        weeklyPlannerBody.innerHTML = `
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                    {{ __('Nenhum evento encontrado para esta semana.') }}
                </td>
            </tr>
        `;
        return;
    }

    weeklyPlannerBody.innerHTML = timeSlots.map(slot => `
        <tr>
            <td class="sticky left-0 z-10 border-b border-r border-gray-200 bg-white px-2 py-4 align-top text-xs font-medium text-gray-700 sm:px-3 sm:text-sm">${slot}</td>
            ${weekDates.map(date => {
                const dayEvents = plannerEvents
                    .filter(item => eventOccursOnDate(item, date) && eventTimeLabel(item) === slot)
                    .sort((a, b) => eventSortValue(a).localeCompare(eventSortValue(b)));

                return `
                    <td class="border-b border-gray-100 px-1 py-2 align-top sm:px-2">
                        <div class="flex min-h-[88px] flex-col gap-2">
                            ${dayEvents.map(item => `
                                <div class="rounded-xl border px-2 py-2 text-xs sm:px-3 sm:text-sm ${getBadgeClasses(item.event_type)}">
                                    <div class="font-semibold">${item.title}</div>
                                    <div class="mt-1 text-xs opacity-80">
                                        ${item.course_class ? `<a href="${courseClassManageBaseUrl.replace('__ID__', item.course_class.id)}" class="hover:underline font-medium">${item.course_class.name}</a>` : typeLabels[item.event_type] || item.event_type}
                                    </div>
                                </div>
                            `).join('') || '<div class="rounded-xl border border-dashed border-gray-200 px-3 py-4 text-center text-xs text-gray-400">{{ __('Livre') }}</div>'}
                        </div>
                    </td>
                `;
            }).join('')}
        </tr>
    `).join('');
}

function renderCalendar(list) {
    const firstOfMonth = new Date(currentMonthDate.getFullYear(), currentMonthDate.getMonth(), 1);
    const monthStart = startOfWeek(firstOfMonth);
    const monthEnd = addDays(monthStart, 41);

    calendarMonthLabel.textContent = new Intl.DateTimeFormat('pt-BR', {
        month: 'long',
        year: 'numeric'
    }).format(firstOfMonth);

    const days = [];
    for (let cursor = new Date(monthStart); cursor <= monthEnd; cursor = addDays(cursor, 1)) {
        days.push(new Date(cursor));
    }

    const todayIso = toIsoDate(new Date());
    calendarGrid.innerHTML = days.map(date => {
        const isCurrentMonth = date.getMonth() === currentMonthDate.getMonth();
        const isoDate = toIsoDate(date);
        const dayEvents = list
            .filter(item => eventOccursOnDate(item, date))
            .sort((a, b) => eventSortValue(a).localeCompare(eventSortValue(b)));

        return `
            <div class="min-h-[96px] rounded-2xl border p-1.5 sm:min-h-[132px] sm:p-2 ${isCurrentMonth ? 'border-gray-200 bg-white' : 'border-gray-100 bg-gray-50 text-gray-400'} ${isoDate === todayIso ? 'ring-2 ring-sky-300' : ''}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold sm:text-sm">${date.getDate()}</span>
                    ${dayEvents.length ? `<span class="rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-semibold text-sky-700">${dayEvents.length}</span>` : ''}
                </div>
                <div class="mt-1.5 space-y-1 sm:mt-2">
                    ${dayEvents.slice(0, 3).map(item => `
                        <a href="${item.course_class ? courseClassManageBaseUrl.replace('__ID__', item.course_class.id) : '#'}" class="truncate rounded-lg border px-1.5 py-1 text-[10px] sm:px-2 sm:text-[11px] ${getBadgeClasses(item.event_type)} hover:shadow-md transition inline-block max-w-full">
                            ${item.is_all_day ? '' : `${formatTime(item.start_time)} `}
                            ${item.title}
                        </a>
                    `).join('')}
                    ${dayEvents.length > 3 ? `<div class="text-[11px] font-medium text-gray-500">+${dayEvents.length - 3} {{ __('mais') }}</div>` : ''}
                </div>
            </div>
        `;
    }).join('');
}

function renderScheduleEvents(list) {
    scheduleEvents = list || [];
    tableBody.innerHTML = '';

    scheduleEvents.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2">
                <div class="font-medium text-gray-900">${item.title}</div>
                <div class="text-sm text-gray-500">${item.location || ''}</div>
            </td>
            <td class="p-2">${typeLabels[item.event_type] || item.event_type}</td>
            <td class="p-2">${item.course_class?.name || ''}</td>
            <td class="p-2">${formatDateRange(item)}</td>
            <td class="p-2">${formatRecurrence(item)}</td>
            ${canManageScheduleEvents ? `
                <td class="p-2">
                    <div class="flex flex-wrap gap-2">
                        <button class="editBtn rounded-lg bg-amber-300 px-3 py-2 text-sm font-medium text-amber-950" data-id="${item.id}">{{ __('Editar') }}</button>
                        <button class="deleteBtn rounded-lg bg-rose-600 px-3 py-2 text-sm font-medium text-white" data-id="${item.id}">{{ __('Excluir') }}</button>
                    </div>
                </td>
            ` : ''}
        `;
        tableBody.appendChild(tr);
    });

    updateSummaryCards(scheduleEvents);
    renderWeeklyPlanner(scheduleEvents);
    renderCalendar(scheduleEvents);

    if (scheduleEventsDataTable) {
        scheduleEventsDataTable.destroy();
    }

    scheduleEventsDataTable = new DataTable('#scheduleEventsTable');
}

function getActiveFilters() {
    return {
        course_class_ids: getSelectedCourseClassIds(),
        event_type: eventTypeFilter.value || '',
    };
}

function buildScheduleEventsUrl() {
    const url = new URL('{{ route("api.schedule-events.index") }}', window.location.origin);
    const filters = getActiveFilters();

    filters.course_class_ids.forEach(id => {
        url.searchParams.append('course_class_ids[]', id);
    });

    if (filters.course_class_ids.length === 1) {
        url.searchParams.set('course_class_id', filters.course_class_ids[0]);
    }

    if (filters.event_type) {
        url.searchParams.set('event_type', filters.event_type);
    }

    return url.toString();
}

function syncPageUrl() {
    const url = new URL(window.location.href);
    const filters = getActiveFilters();

    url.searchParams.delete('course_class_id');
    url.searchParams.delete('course_class_ids[]');

    filters.course_class_ids.forEach(id => {
        url.searchParams.append('course_class_ids[]', id);
    });

    if (filters.course_class_ids.length === 1) {
        url.searchParams.set('course_class_id', filters.course_class_ids[0]);
    } else {
        url.searchParams.delete('course_class_id');
    }

    if (filters.event_type) {
        url.searchParams.set('event_type', filters.event_type);
    } else {
        url.searchParams.delete('event_type');
    }

    window.history.replaceState({}, '', url);
}

function runPrint(mode) {
    document.body.classList.remove('print-planner', 'print-calendar');
    document.body.classList.add(mode);
    window.print();
    window.setTimeout(() => {
        document.body.classList.remove('print-planner', 'print-calendar');
    }, 300);
}

function resetForm() {
    if (!scheduleEventForm) {
        return;
    }

    scheduleEventForm.reset();
    scheduleEventForm.id.value = '';
    populateCourseClassOptions();
    toggleTimeFields();
}

function toggleTimeFields() {
    if (!scheduleEventForm) {
        return;
    }

    const allDay = scheduleEventForm.is_all_day.checked;
    scheduleEventForm.start_time.disabled = allDay;
    scheduleEventForm.end_time.disabled = allDay;

    if (allDay) {
        scheduleEventForm.start_time.value = '';
        scheduleEventForm.end_time.value = '';
    }
}

function buildPayload() {
    if (!scheduleEventForm) {
        return {};
    }

    return {
        title: scheduleEventForm.title.value,
        event_type: scheduleEventForm.event_type.value,
        course_class_id: scheduleEventForm.course_class_id.value || null,
        location: scheduleEventForm.location.value || null,
        start_date: scheduleEventForm.start_date.value,
        end_date: scheduleEventForm.end_date.value || null,
        start_time: scheduleEventForm.is_all_day.checked ? null : (scheduleEventForm.start_time.value || null),
        end_time: scheduleEventForm.is_all_day.checked ? null : (scheduleEventForm.end_time.value || null),
        weekday: scheduleEventForm.weekday.value === '' ? null : Number(scheduleEventForm.weekday.value),
        is_recurring_weekly: scheduleEventForm.is_recurring_weekly.checked,
        is_all_day: scheduleEventForm.is_all_day.checked,
        description: scheduleEventForm.description.value || null,
        user_id: currentUserId,
    };
}

function scrollToFormContainer() {
    if (!formContainer) {
        return;
    }

    window.requestAnimationFrame(() => {
        formContainer.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        });
    });
}

async function loadDependencies() {
    courseClasses = await fetchAllPages('{{ route("api.course-classes.index") }}');
    if (canManageScheduleEvents) {
        populateCourseClassOptions();
    }
    populateCourseClassFilterOptions(initialFilters.course_class_ids);
    renderCourseClassGrid(initialFilters.course_class_ids);
    eventTypeFilter.value = initialFilters.event_type;
}

async function fetchScheduleEvents() {
    syncPageUrl();
    const events = await fetchAllPages(buildScheduleEventsUrl());
    renderScheduleEvents(events);
}

document.getElementById('prevWeekBtn').addEventListener('click', () => {
    currentWeekDate = addDays(currentWeekDate, -7);
    renderWeeklyPlanner(scheduleEvents);
});

document.getElementById('currentWeekBtn').addEventListener('click', () => {
    currentWeekDate = startOfWeek(new Date());
    renderWeeklyPlanner(scheduleEvents);
});

document.getElementById('nextWeekBtn').addEventListener('click', () => {
    currentWeekDate = addDays(currentWeekDate, 7);
    renderWeeklyPlanner(scheduleEvents);
});

document.getElementById('prevMonthBtn').addEventListener('click', () => {
    currentMonthDate = new Date(currentMonthDate.getFullYear(), currentMonthDate.getMonth() - 1, 1);
    renderCalendar(scheduleEvents);
});

document.getElementById('nextMonthBtn').addEventListener('click', () => {
    currentMonthDate = new Date(currentMonthDate.getFullYear(), currentMonthDate.getMonth() + 1, 1);
    renderCalendar(scheduleEvents);
});

document.getElementById('applyFiltersBtn').addEventListener('click', () => {
    fetchScheduleEvents();
});

document.getElementById('resetFiltersBtn').addEventListener('click', () => {
    setSelectedCourseClassIds([]);
    eventTypeFilter.value = '';
    renderCourseClassGrid([]);
    fetchScheduleEvents();
});

courseClassFilter.addEventListener('change', () => {
    const selectedIds = getSelectedCourseClassIds();
    renderCourseClassGrid(selectedIds);
    fetchScheduleEvents();
});
eventTypeFilter.addEventListener('change', fetchScheduleEvents);

courseClassGrid?.addEventListener('click', (event) => {
    const card = event.target.closest('.course-class-card');

    if (!card) {
        return;
    }

    const selectedId = card.dataset.id || '';
    const selectedIds = new Set(getSelectedCourseClassIds());

    if (selectedIds.has(selectedId)) {
        selectedIds.delete(selectedId);
    } else {
        selectedIds.add(selectedId);
    }

    const nextSelectedIds = Array.from(selectedIds);
    setSelectedCourseClassIds(nextSelectedIds);
    renderCourseClassGrid(nextSelectedIds);
    fetchScheduleEvents();
});

document.getElementById('printPlannerBtn').addEventListener('click', () => {
    runPrint('print-planner');
});

document.getElementById('printCalendarBtn').addEventListener('click', () => {
    runPrint('print-calendar');
});

if (canManageScheduleEvents) {
    document.getElementById('showCreate').addEventListener('click', () => {
        resetForm();
        formTitle.textContent = @json(__('Criar evento'));
        formContainer.classList.remove('hidden');
        scrollToFormContainer();
    });

    document.getElementById('cancelBtn').addEventListener('click', () => {
        formContainer.classList.add('hidden');
    });

    scheduleEventForm.is_all_day.addEventListener('change', toggleTimeFields);

    tableBody.addEventListener('click', async (e) => {
        if (e.target.classList.contains('editBtn')) {
            const id = e.target.dataset.id;
            const res = await fetch(`{{ route("api.schedule-events.show", ["schedule_event" => "__ID__"]) }}`.replace('__ID__', id), {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            const item = await res.json();

            scheduleEventForm.id.value = item.id;
            scheduleEventForm.title.value = item.title;
            scheduleEventForm.event_type.value = item.event_type;
            scheduleEventForm.location.value = item.location || '';
            scheduleEventForm.start_date.value = item.start_date ? item.start_date.slice(0, 10) : '';
            scheduleEventForm.end_date.value = item.end_date ? item.end_date.slice(0, 10) : '';
            scheduleEventForm.start_time.value = formatTime(item.start_time);
            scheduleEventForm.end_time.value = formatTime(item.end_time);
            scheduleEventForm.weekday.value = item.weekday ?? '';
            scheduleEventForm.is_recurring_weekly.checked = Boolean(item.is_recurring_weekly);
            scheduleEventForm.is_all_day.checked = Boolean(item.is_all_day);
            scheduleEventForm.description.value = item.description || '';
            populateCourseClassOptions(item.course_class_id);
            toggleTimeFields();

            formTitle.textContent = @json(__('Editar evento'));
            formContainer.classList.remove('hidden');
            scrollToFormContainer();
        }

        if (e.target.classList.contains('deleteBtn')) {
            if (!confirm(@json(__('Excluir evento?')))) return;

            const id = e.target.dataset.id;
            const res = await fetch(`{{ route("api.schedule-events.destroy", ["schedule_event" => "__ID__"]) }}`.replace('__ID__', id), {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-User-Id': currentUserId,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                fetchScheduleEvents();
            } else {
                alert(@json(__('Erro ao excluir evento')));
            }
        }
    });

    scheduleEventForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = scheduleEventForm.id.value;
        const url = id
            ? `{{ route("api.schedule-events.update", ["schedule_event" => "__ID__"]) }}`.replace('__ID__', id)
            : '{{ route("api.schedule-events.store") }}';

        const res = await fetch(url, {
            method: id ? 'PUT' : 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(buildPayload())
        });

        if (res.ok) {
            formContainer.classList.add('hidden');
            fetchScheduleEvents();
        } else {
            const error = await res.json().catch(() => null);
            alert(error?.message || @json(__('Erro ao salvar evento')));
        }
    });
}

async function init() {
    await loadDependencies();
    await fetchScheduleEvents();
}

init();
</script>
@endsection
