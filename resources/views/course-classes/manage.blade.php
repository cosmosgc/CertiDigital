@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="space-y-6">
        <section class="overflow-hidden rounded-[28px] bg-gradient-to-r from-amber-100 via-white to-cyan-100 p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <a href="{{ route('course-classes.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">{{ __('Voltar para turmas') }}</a>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">{{ __('Gestão da turma') }}</p>
                    <h2 id="className" class="mt-2 text-3xl font-semibold text-slate-900"></h2>
                    <p id="classMeta" class="mt-2 text-sm text-slate-600"></p>
                    <p id="classDescription" class="mt-4 max-w-2xl text-sm leading-6 text-slate-600"></p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('course-classes.attendance-report', $courseClass) }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm">{{ __('Relatório de presença') }}</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('Curso') }}</p>
                <p id="courseTitle" class="mt-3 text-lg font-semibold text-slate-900"></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('Instrutor') }}</p>
                <p id="instructorName" class="mt-3 text-lg font-semibold text-slate-900"></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('Carga horária') }}</p>
                <p id="workloadHours" class="mt-3 text-3xl font-semibold text-slate-900">0h</p>
                <p id="plannedHoursStats" class="mt-1 text-xs text-cyan-700 font-medium"></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('Alunos') }}</p>
                <p id="studentCount" class="mt-3 text-3xl font-semibold text-slate-900">0</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
            <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-emerald-200">
                <h3 class="text-xl font-semibold text-slate-900">{{ __('Adicionar aluno à turma') }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ __('Pesquise pelo nome ou e-mail e clique no aluno para preencher a matrícula rapidamente.') }}</p>

                <form id="enrollmentForm" class="mt-5 space-y-4">
                    <input type="hidden" name="enrollment_id" />
                    <input type="hidden" name="student_id" />
                    <div class="space-y-3">
                        <div>
                            <label for="studentSearchInput" class="block text-sm font-medium text-slate-700">{{ __('Buscar aluno') }}</label>
                            <input id="studentSearchInput" type="text" autocomplete="off" class="mt-1 block w-full rounded-2xl border-slate-200 bg-slate-50 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="{{ __('Digite nome ou e-mail') }}">
                        </div>
                        <div id="selectedStudentCard" class="hidden rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-900 ring-1 ring-emerald-200"></div>
                        <div id="studentSearchResults" class="max-h-72 space-y-2 overflow-y-auto rounded-2xl bg-slate-50 p-2 ring-1 ring-slate-200"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Nota') }}</label>
                        <input type="number" min="0" max="100" step="0.01" name="grade" value="6" class="mt-1 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-emerald-400 focus:ring-emerald-400" />
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white">{{ __('Salvar matrícula') }}</button>
                        <button type="button" id="cancelEdit" class="hidden rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-300">{{ __('Cancelar edição') }}</button>
                    </div>
                </form>
            </section>

            <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-xl font-semibold text-slate-900">{{ __('Alunos matriculados') }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ __('Visual mais leve para revisar progresso, nota e ações sem cansar a leitura.') }}</p>

                <div class="mt-5 overflow-hidden rounded-3xl ring-1 ring-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto bg-white" id="classStudentsTable">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-sm text-slate-600">
                                    <th class="px-4 py-3 font-semibold">{{ __('ID matrícula') }}</th>
                                    <th class="px-4 py-3 font-semibold">{{ __('Aluno') }}</th>
                                    <th class="px-4 py-3 font-semibold">{{ __('E-mail') }}</th>
                                    <th class="px-4 py-3 font-semibold">{{ __('Progresso') }}</th>
                                    <th class="px-4 py-3 font-semibold">{{ __('Nota') }}</th>
                                    <th class="px-4 py-3 font-semibold">{{ __('Concluído') }}</th>
                                    <th class="px-4 py-3 font-semibold">{{ __('Ações') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
            <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-cyan-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Sessões de presença') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Ao criar uma nova sessão, todos os alunos da turma começam marcados como presentes. Depois, você pode ajustar quem entrou atrasado ou faltou.') }}</p>
                        <p id="plannedHoursSection" class="mt-1 text-xs text-cyan-700 font-medium"></p>
                    </div>
                    <button id="toggleAttendanceList" type="button" class="hidden shrink-0 rounded-xl bg-white px-4 py-2 text-sm font-medium text-cyan-700 ring-1 ring-gray-300 hover:bg-cyan-50"></button>
                </div>

                <form id="attendanceForm" class="mt-4 space-y-4">
                    <input type="hidden" name="attendance_id">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">{{ __('Nome') }}</label>
                            <input type="text" name="name" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" placeholder="{{ __('Padrão: Attendance #ID') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Data') }}</label>
                            <input type="date" name="attendance_date" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Horas') }}</label>
                            <input type="number" min="0.25" step="0.25" name="duration_hours" value="1" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-2 text-white">{{ __('Salvar sessão') }}</button>
                            <button type="button" id="cancelAttendanceEdit" class="hidden rounded-xl bg-white px-4 py-2 text-gray-700 ring-1 ring-gray-300">{{ __('Cancelar') }}</button>
                        </div>
                    </div>
                </form>

                <div id="attendanceList" class="mt-6 hidden grid gap-3"></div>

                <div id="upcomingClassesSection" class="mt-6 hidden">
                    <hr class="mb-4 border-cyan-100">
                    <h4 class="text-sm font-semibold text-gray-900">{{ __('Próximas aulas previstas') }}</h4>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Aulas da agenda que ainda não têm sessão de presença.') }}</p>
                    <p class="mt-1 text-xs text-cyan-700">{{ __('Eventos recorrentes geram sugestões até a data final + 12 meses. Sem data final, valem 12 meses a partir de hoje — edite a data final abaixo para estender.') }}</p>
                    <div id="upcomingClassesList" class="mt-3 grid gap-2"></div>
                </div>

                <div id="scheduleEventsSection" class="mt-6 hidden">
                    <hr class="mb-4 border-cyan-100">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">{{ __('Agenda da turma') }}</h4>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Edite os eventos da agenda desta turma (ex.: estender a data final para gerar mais sugestões).') }}</p>
                        </div>
                        <a href="{{ route('schedule-events.index') }}" class="shrink-0 rounded-lg bg-white px-3 py-1.5 text-xs font-medium text-cyan-700 ring-1 ring-gray-200 hover:bg-cyan-50">{{ __('Abrir agenda') }}</a>
                    </div>
                    <div id="scheduleEventsList" class="mt-3 grid gap-3"></div>
                </div>
            </section>

            <section class="rounded-[28px] bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="selectedAttendanceTitle" class="text-lg font-semibold text-gray-900">{{ __('Selecione uma sessão') }}</h3>
                        <p id="selectedAttendanceMeta" class="mt-1 text-sm text-gray-500">{{ __('Escolha uma sessão para ajustar as presenças individualmente.') }}</p>
                    </div>
                </div>

                <form id="attendanceRecordForm" class="mt-4 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Adicionar presença') }}</label>
                        <select name="student_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></select>
                    </div>
                    <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2 text-white">{{ __('Adicionar aluno presente') }}</button>
                </form>

                <div class="mt-5 overflow-hidden rounded-3xl ring-1 ring-slate-200">
                    <table class="w-full table-auto" id="attendanceRecordsTable">
                        <thead>
                            <tr class="bg-slate-50 text-left text-sm text-slate-600">
                                <th class="px-4 py-3">{{ __('Aluno') }}</th>
                                <th class="px-4 py-3">{{ __('E-mail') }}</th>
                                <th class="px-4 py-3">{{ __('Ação') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
#studentSearchResults:empty::before {
    content: "{{ __('Nenhum aluno carregado.') }}";
    display: block;
    padding: 0.9rem 1rem;
    color: rgb(100 116 139);
    font-size: 0.875rem;
}

.student-search-result {
    width: 100%;
    border-radius: 1rem;
    border: 1px solid rgb(226 232 240);
    background: white;
    padding: 0.9rem 1rem;
    text-align: left;
    transition: 0.2s ease;
}

.student-search-result:hover {
    border-color: rgb(16 185 129);
    background: rgb(236 253 245);
}

#classStudentsTable tbody tr,
#attendanceRecordsTable tbody tr {
    background: white;
}

#classStudentsTable tbody td,
#attendanceRecordsTable tbody td {
    color: rgb(15 23 42);
}
</style>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = @json(auth()->id());
const classId = @json($courseClass->id);
const today = @json(now()->toDateString());
const className = document.getElementById('className');
const classMeta = document.getElementById('classMeta');
const classDescription = document.getElementById('classDescription');
const courseTitle = document.getElementById('courseTitle');
const instructorName = document.getElementById('instructorName');
const workloadHours = document.getElementById('workloadHours');
const studentCount = document.getElementById('studentCount');
const enrollmentForm = document.getElementById('enrollmentForm');
const cancelEditBtn = document.getElementById('cancelEdit');
const studentSearchInput = document.getElementById('studentSearchInput');
const studentSearchResults = document.getElementById('studentSearchResults');
const selectedStudentCard = document.getElementById('selectedStudentCard');
const attendanceForm = document.getElementById('attendanceForm');
const cancelAttendanceEditBtn = document.getElementById('cancelAttendanceEdit');
const attendanceList = document.getElementById('attendanceList');
const attendanceRecordForm = document.getElementById('attendanceRecordForm');
const selectedAttendanceTitle = document.getElementById('selectedAttendanceTitle');
const selectedAttendanceMeta = document.getElementById('selectedAttendanceMeta');
const studentsTableBody = document.querySelector('#classStudentsTable tbody');
const attendanceRecordsTableBody = document.querySelector('#attendanceRecordsTable tbody');
const attendanceShowBaseUrl = @json(route('course-class-attendances.show', ['courseClass' => $courseClass, 'courseClassAttendance' => '__ATTENDANCE__']));
const enrollmentShowBaseUrl = @json(route('course-class-enrollments.show', ['courseClass' => $courseClass, 'courseEnrollment' => '__ENROLLMENT__']));
let studentsTable = null;
let classData = null;
let allStudents = [];
let allEnrollments = [];
let selectedAttendanceId = null;
let selectedEnrollmentStudent = null;

function formatHours(value) {
    const numeric = Number(value ?? 0);
    return `${numeric % 1 === 0 ? numeric.toFixed(0) : numeric.toFixed(2)}h`;
}

function formatSessionDate(value) {
    if (!value) return '';
    const iso = String(value).slice(0, 10);
    const parts = iso.split('-');
    if (parts.length !== 3) return String(value);
    const [year, month, day] = parts;
    if (!year || !month || !day) return String(value);
    return `${day}/${month}/${year}`;
}

function getWorkloadValue() {
    return Number(classData?.course?.workload_hours ?? 0);
}

function calculateEventDuration(event) {
    if (!event.is_all_day && event.start_time && event.end_time) {
        const startParts = (event.start_time || '').split(':');
        const endParts = (event.end_time || '').split(':');
        if (startParts.length >= 2 && endParts.length >= 2) {
            const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
            const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
            return Math.max(0.25, (endMinutes - startMinutes) / 60);
        }
    }
    return 1;
}

function parseDate(val) {
    return new Date((val || '').slice(0, 10) + 'T00:00:00');
}

function fmtDate(date) {
    return date.toISOString().slice(0, 10);
}

function getEventOccurrences(event) {
    const start = parseDate(event.start_date);
    const dates = [];

    if (event.is_recurring_weekly && event.weekday !== null && event.weekday !== undefined) {
        // Default suggestion window: end_date (if set) plus 12 months,
        // or 12 months from today when end_date is empty (null).
        const end = event.end_date
            ? parseDate(event.end_date)
            : parseDate(today);
        end.setMonth(end.getMonth() + 12);

        const current = new Date(start);
        while (current <= end) {
            if (current.getDay() === Number(event.weekday)) {
                dates.push(fmtDate(current));
                current.setDate(current.getDate() + 7);
            } else {
                current.setDate(current.getDate() + 1);
            }
        }
    } else {
        dates.push((event.start_date || '').slice(0, 10));
    }

    return [...new Set(dates)];
}

function calculatePlannedHours() {
    const events = classData?.schedule_events || [];
    let total = 0;
    for (const event of events) {
        total += calculateEventDuration(event) * getEventOccurrences(event).length;
    }
    return total;
}

function displayPlannedHours() {
    const hours = calculatePlannedHours();
    const text = hours > 0 ? '{{ __('Planejado') }}: ' + formatHours(hours) + ' {{ __('na agenda') }}' : '';
    const statsEl = document.getElementById('plannedHoursStats');
    const sectionEl = document.getElementById('plannedHoursSection');
    if (statsEl) statsEl.textContent = text;
    if (sectionEl) sectionEl.textContent = text;
}

function getProgressPercent(hours) {
    const workload = getWorkloadValue();

    if (!workload) return 0;

    return Math.min(100, Math.round((Number(hours ?? 0) / workload) * 100));
}

async function fetchAllPages(url) {
    const items = [];
    let nextUrl = url;

    while (nextUrl) {
        const res = await fetch(nextUrl, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-User-Id': currentUserId
            }
        });
        const data = await res.json();
        items.push(...(data.data || []));
        nextUrl = data.next_page_url;
    }

    return items;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function setSelectedEnrollmentStudent(student) {
    selectedEnrollmentStudent = student || null;
    enrollmentForm.student_id.value = student?.id || '';

    if (!student) {
        selectedStudentCard.classList.add('hidden');
        selectedStudentCard.textContent = '';
        return;
    }

    selectedStudentCard.classList.remove('hidden');
    selectedStudentCard.innerHTML = `
        <div class="font-semibold">${escapeHtml(student.full_name || '')}</div>
        <div class="mt-1 text-xs text-emerald-700">${escapeHtml(student.email || '')}</div>
    `;
}

function getAvailableStudents() {
    const enrolledStudentIds = new Set((classData?.enrollments || [])
        .filter(enrollment => String(enrollment.id) !== String(enrollmentForm.enrollment_id.value || ''))
        .map(enrollment => String(enrollment.student_id)));

    return allStudents.filter(student => !enrolledStudentIds.has(String(student.id)));
}

function renderStudentSearchResults(query = '') {
    const normalizedQuery = String(query || '').trim().toLowerCase();
    const availableStudents = getAvailableStudents();
    const results = availableStudents
        .filter(student => {
            if (!normalizedQuery) return true;

            const haystack = `${student.full_name || ''} ${student.email || ''}`.toLowerCase();
            return haystack.includes(normalizedQuery);
        })
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 12);

    if (!results.length) {
        studentSearchResults.innerHTML = `
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-500">
                ${normalizedQuery ? @json(__('Nenhum aluno encontrado para essa busca.')) : @json(__('Todos os alunos disponíveis já estão matriculados nesta turma.'))}
            </div>
        `;
        return;
    }

    studentSearchResults.innerHTML = results.map(student => `
        <button
            type="button"
            class="student-search-result"
            data-student-id="${student.id}"
        >
            <div class="text-sm font-semibold text-slate-900">${escapeHtml(student.full_name || '')}</div>
            <div class="mt-1 text-xs text-slate-500">${escapeHtml(student.email || '')}</div>
        </button>
    `).join('');
}

function populateAttendanceRecordOptions() {
    const selectedAttendance = getSelectedAttendance();
    const presentStudentIds = new Set((selectedAttendance?.records || []).map(record => String(record.student_id)));
    const enrolledStudents = classData?.enrollments || [];

    attendanceRecordForm.student_id.innerHTML = `
        <option value="">{{ __('Selecione um aluno') }}</option>
        ${enrolledStudents
            .filter(enrollment => !presentStudentIds.has(String(enrollment.student_id)))
            .map(enrollment => `
                <option value="${enrollment.student_id}">${enrollment.student?.full_name || ''} (${enrollment.student?.email || ''})</option>
            `).join('')}
    `;
}

async function loadStudents() {
    allStudents = await fetchAllPages('{{ route("api.students.index") }}');
    renderStudentSearchResults();
}

async function loadEnrollments() {
    allEnrollments = await fetchAllPages('{{ route("api.course-enrollments.index") }}');
}

async function fetchClassData() {
    let url = `{{ route("api.course-classes.show", ["course_class" => "__ID__"]) }}`.replace('__ID__', classId);
    // Add timestamp to force fresh data from server
    url += '?t=' + Date.now();
    
    const res = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        }
    });
    
    if (!res.ok) {
        console.error('Failed to fetch class data:', res.status);
        return;
    }

    const data = await res.json();
    console.log('Updated classData from API:', data);
    classData = data;

    if (!selectedAttendanceId && classData.attendances?.length) {
        selectedAttendanceId = classData.attendances[0].id;
    }

    if (selectedAttendanceId && !(classData.attendances || []).some(attendance => String(attendance.id) === String(selectedAttendanceId))) {
        selectedAttendanceId = classData.attendances?.[0]?.id || null;
    }

    renderClassData();
}

function getSelectedAttendance() {
    return (classData?.attendances || []).find(attendance => String(attendance.id) === String(selectedAttendanceId)) || null;
}

function renderClassData() {
    className.textContent = classData.name;
    classMeta.textContent = `{{ __('Turma ID') }} #${classData.id}`;
    courseTitle.textContent = classData.course?.title || '';
    instructorName.textContent = classData.instructor?.full_name || @json(__('Não definido'));
    workloadHours.textContent = formatHours(classData.course?.workload_hours || 0);
    displayPlannedHours();
    classDescription.textContent = classData.description || @json(__('Sem descrição cadastrada.'));
    studentCount.textContent = classData.enrollments?.length || 0;
    console.log(classData.enrollments?.length || 0, 'enrollments loaded for class');
    studentsTableBody.innerHTML = '';

    (classData.enrollments || []).forEach(enrollment => {
        const progressHours = Number(enrollment.progress_hours ?? 0);
        const progressPercent = getProgressPercent(progressHours);
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/80';
        tr.innerHTML = `
            <td class="px-4 py-4 text-sm">${enrollment.id}</td>
            <td class="px-4 py-4">
                <div class="font-semibold text-slate-900">${enrollment.student?.full_name || ''}</div>
            </td>
            <td class="px-4 py-4 text-sm text-slate-600">${enrollment.student?.email || ''}</td>
            <td class="px-4 py-4">
                <div class="font-medium text-slate-900">${formatHours(progressHours)}</div>
                <div class="mt-1 text-xs text-slate-500">${progressPercent}%</div>
            </td>
            <td class="px-4 py-4 text-sm text-slate-700">${enrollment.grade ?? '—'}</td>
            <td class="px-4 py-4">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${enrollment.completed ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">
                    ${enrollment.completed ? @json(__('Sim')) : @json(__('Não'))}
                </span>
            </td>
            <td class="px-4 py-4">
                <div class="flex flex-wrap gap-2">
                    <a class="rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white" href="${enrollmentShowBaseUrl.replace('__ENROLLMENT__', enrollment.id)}">{{ __('Ver aluno') }}</a>
                    <button class="editBtn rounded-xl bg-amber-100 px-3 py-2 text-sm font-medium text-amber-800" data-id="${enrollment.id}">{{ __('Editar') }}</button>
                    <button class="removeBtn rounded-xl bg-rose-500 px-3 py-2 text-sm font-medium text-white" data-id="${enrollment.id}">{{ __('Remover') }}</button>
                </div>
            </td>
        `;
        studentsTableBody.appendChild(tr);
    });

    // if (studentsTable && typeof studentsTable.destroy === 'function') {
    //     studentsTable.destroy();
    //     studentsTable = null;
    // }

    studentsTable = new DataTable('#classStudentsTable');
    console.log('Class data rendered and table initialized');

    renderStudentSearchResults(studentSearchInput.value);
    renderAttendanceList();
    renderSelectedAttendance();
    renderUpcomingClasses();
    renderScheduleEvents();
}

const toggleAttendanceListBtn = document.getElementById('toggleAttendanceList');
let attendanceListExpanded = false;

const attendanceMonthNames = {
    '01': @json(__('Janeiro')), '02': @json(__('Fevereiro')), '03': @json(__('Março')),
    '04': @json(__('Abril')), '05': @json(__('Maio')), '06': @json(__('Junho')),
    '07': @json(__('Julho')), '08': @json(__('Agosto')), '09': @json(__('Setembro')),
    '10': @json(__('Outubro')), '11': @json(__('Novembro')), '12': @json(__('Dezembro'))
};

function attendanceMonthLabel(key) {
    const parts = String(key || '').split('-');
    if (parts.length !== 2) return @json(__('Sem data'));
    return `${attendanceMonthNames[parts[1]] || parts[1]} ${parts[0]}`;
}

function updateAttendanceToggleLabel() {
    const count = (classData?.attendances || []).length;
    if (!count) {
        toggleAttendanceListBtn.classList.add('hidden');
        return;
    }
    toggleAttendanceListBtn.classList.remove('hidden');
    toggleAttendanceListBtn.textContent = attendanceListExpanded
        ? @json(__('Ocultar sessões'))
        : `${@json(__('Exibir sessões'))} (${count})`;
}

toggleAttendanceListBtn.addEventListener('click', () => {
    attendanceListExpanded = !attendanceListExpanded;
    attendanceList.classList.toggle('hidden', !attendanceListExpanded);
    updateAttendanceToggleLabel();
});

function attendanceCardHTML(attendance) {
    return `
        <article class="rounded-2xl border p-4 ${String(attendance.id) === String(selectedAttendanceId) ? 'border-cyan-500 bg-white shadow-sm' : 'border-cyan-100 bg-white/80'}">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900">${attendance.name}</p>
                    <p class="mt-1 text-sm text-gray-500">${formatSessionDate(attendance.attendance_date)} • ${formatHours(attendance.duration_hours)}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Presentes:') }} ${attendance.records?.length || 0}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="${attendanceShowBaseUrl.replace('__ATTENDANCE__', attendance.id)}" class="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">{{ __('Abrir sessão') }}</a>
                    <button type="button" class="selectAttendanceBtn rounded-lg bg-cyan-600 px-3 py-2 text-sm text-white" data-id="${attendance.id}">{{ __('Gerenciar presença') }}</button>
                    <button type="button" class="editAttendanceBtn rounded-lg bg-white px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-300" data-id="${attendance.id}">{{ __('Editar sessão') }}</button>
                    <button type="button" class="deleteAttendanceBtn rounded-lg bg-red-500 px-3 py-2 text-sm text-white" data-id="${attendance.id}">{{ __('Excluir') }}</button>
                </div>
            </div>
        </article>
    `;
}

function renderAttendanceList() {
    const attendances = [...(classData?.attendances || [])]
        .sort((a, b) => String(b.attendance_date ?? '').slice(0, 10).localeCompare(String(a.attendance_date ?? '').slice(0, 10)));

    updateAttendanceToggleLabel();

    if (!attendances.length) {
        attendanceList.innerHTML = `
            <div class="rounded-2xl border border-dashed border-cyan-300 bg-white/80 p-5 text-sm text-gray-500">
                {{ __('Nenhuma sessão foi criada ainda.') }}
            </div>
        `;
        return;
    }

    const groups = new Map();
    attendances.forEach(attendance => {
        const key = String(attendance.attendance_date || '').slice(0, 7) || 'sem-data';
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(attendance);
    });

    attendanceList.innerHTML = [...groups.entries()].map(([monthKey, items]) => {
        const expanded = items.some(a => String(a.id) === String(selectedAttendanceId));
        return `
        <div class="overflow-hidden rounded-2xl border border-cyan-100" data-month="${monthKey}">
            <button type="button" class="attendanceMonthToggle flex w-full items-center gap-3 bg-cyan-50/60 px-4 py-3 text-left text-sm font-semibold text-cyan-800 hover:bg-cyan-100">
                <svg class="attendance-month-icon h-3 w-3 shrink-0 transition-transform duration-200" style="${expanded ? 'transform: rotate(90deg);' : ''}" viewBox="0 0 12 12" fill="none">
                    <path d="M4 2L8 6L4 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>${attendanceMonthLabel(monthKey)} (${items.length})</span>
            </button>
            <div class="attendanceMonthBody grid gap-3 bg-white p-3 ${expanded ? '' : 'hidden'}">
                ${items.map(attendanceCardHTML).join('')}
            </div>
        </div>`;
    }).join('');
}

attendanceList.addEventListener('click', (e) => {
    const monthToggle = e.target.closest('.attendanceMonthToggle');
    if (monthToggle) {
        const body = monthToggle.parentElement.querySelector('.attendanceMonthBody');
        body.classList.toggle('hidden');
        monthToggle.querySelector('.attendance-month-icon').style.transform =
            body.classList.contains('hidden') ? '' : 'rotate(90deg)';
        return;
    }
});

function renderSelectedAttendance() {
    const attendance = getSelectedAttendance();

    if (!attendance) {
        selectedAttendanceTitle.textContent = @json(__('Selecione uma sessão'));
        selectedAttendanceMeta.textContent = @json(__('Crie uma sessão para começar a controlar presença.'));
        attendanceRecordForm.classList.add('hidden');
        attendanceRecordsTableBody.innerHTML = `
            <tr>
                <td colspan="3" class="px-4 py-4 text-sm text-slate-500">{{ __('Nenhuma sessão selecionada.') }}</td>
            </tr>
        `;
        return;
    }

    attendanceRecordForm.classList.remove('hidden');
    selectedAttendanceTitle.textContent = attendance.name;
    selectedAttendanceMeta.textContent = `${formatSessionDate(attendance.attendance_date)} • ${formatHours(attendance.duration_hours)} • ${(attendance.records || []).length} {{ __('presenças') }}`;
    populateAttendanceRecordOptions();

    if (!attendance.records?.length) {
        attendanceRecordsTableBody.innerHTML = `
            <tr>
                <td colspan="3" class="px-4 py-4 text-sm text-slate-500">{{ __('Nenhum aluno marcado como presente nesta sessão.') }}</td>
            </tr>
        `;
        return;
    }

    attendanceRecordsTableBody.innerHTML = attendance.records.map(record => `
        <tr class="hover:bg-slate-50/80">
            <td class="px-4 py-4">${record.student?.full_name || ''}</td>
            <td class="px-4 py-4 text-sm text-slate-600">${record.student?.email || ''}</td>
            <td class="px-4 py-4">
                <button type="button" class="removeAttendanceRecordBtn rounded-lg bg-rose-500 px-3 py-2 text-sm text-white" data-id="${record.id}">
                    {{ __('Remover presença') }}
                </button>
            </td>
        </tr>
    `).join('');
}

function resetEnrollmentForm() {
    enrollmentForm.reset();
    enrollmentForm.enrollment_id.value = '';
    studentSearchInput.disabled = false;
    studentSearchInput.value = '';
    setSelectedEnrollmentStudent(null);
    renderStudentSearchResults();
    cancelEditBtn.classList.add('hidden');
}

function resetAttendanceForm() {
    attendanceForm.reset();
    attendanceForm.attendance_id.value = '';
    attendanceForm.attendance_date.value = today;
    attendanceForm.duration_hours.value = 1;
    cancelAttendanceEditBtn.classList.add('hidden');
}

cancelEditBtn.addEventListener('click', resetEnrollmentForm);
cancelAttendanceEditBtn.addEventListener('click', resetAttendanceForm);

studentSearchInput.addEventListener('input', () => {
    renderStudentSearchResults(studentSearchInput.value);
});

studentSearchResults.addEventListener('click', (e) => {
    const button = e.target.closest('.student-search-result');
    if (!button) return;

    const student = allStudents.find(item => String(item.id) === String(button.dataset.studentId));
    if (!student) return;

    setSelectedEnrollmentStudent(student);
    studentSearchInput.value = student.full_name || '';
});

studentsTableBody.addEventListener('click', async (e) => {
    if (e.target.classList.contains('editBtn')) {
        const enrollment = (classData.enrollments || []).find(item => String(item.id) === e.target.dataset.id);
        if (!enrollment) return;

        enrollmentForm.enrollment_id.value = enrollment.id;
        enrollmentForm.grade.value = enrollment.grade ?? '';
        studentSearchInput.value = enrollment.student?.full_name || '';
        studentSearchInput.disabled = true;
        setSelectedEnrollmentStudent(enrollment.student || null);
        renderStudentSearchResults(studentSearchInput.value);
        cancelEditBtn.classList.remove('hidden');
    }

    if (e.target.classList.contains('removeBtn')) {
        if (!confirm(@json(__('Remover aluno da turma?')))) return;

        const res = await fetch(`{{ route("api.course-enrollments.destroy", ["course_enrollment" => "__ID__"]) }}`.replace('__ID__', e.target.dataset.id), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-User-Id': currentUserId,
                'Accept': 'application/json'
            }
        });

        if (res.ok) {
            

            resetEnrollmentForm();
            await loadEnrollments();
            await fetchClassData();

            
        } else {
            alert(@json(__('Erro ao remover matrícula')));
        }
    }
});

attendanceList.addEventListener('click', async (e) => {
    const target = e.target;

    if (target.classList.contains('selectAttendanceBtn')) {
        selectedAttendanceId = target.dataset.id;
        renderAttendanceList();
        renderSelectedAttendance();
    }

    if (target.classList.contains('editAttendanceBtn')) {
        const attendance = (classData.attendances || []).find(item => String(item.id) === target.dataset.id);
        if (!attendance) return;

        attendanceForm.attendance_id.value = attendance.id;
        attendanceForm.name.value = attendance.name ?? '';
        attendanceForm.attendance_date.value = String(attendance.attendance_date ?? '').slice(0, 10);
        attendanceForm.duration_hours.value = attendance.duration_hours ?? 1;
        cancelAttendanceEditBtn.classList.remove('hidden');
    }

    if (target.classList.contains('deleteAttendanceBtn')) {
        if (!confirm(@json(__('Excluir esta sessão de presença?')))) return;

        const res = await fetch(`{{ route("api.course-class-attendances.destroy", ["course_class_attendance" => "__ID__"]) }}`.replace('__ID__', target.dataset.id), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-User-Id': currentUserId,
                'Accept': 'application/json'
            }
        });

        if (res.ok) {
            if (String(selectedAttendanceId) === String(target.dataset.id)) {
                selectedAttendanceId = null;
            }
            resetAttendanceForm();
            await fetchClassData();
        } else {
            alert(@json(__('Erro ao excluir sessão')));
        }
    }
});

attendanceRecordsTableBody.addEventListener('click', async (e) => {
    if (!e.target.classList.contains('removeAttendanceRecordBtn')) return;

    const res = await fetch(`{{ route("api.course-class-attendance-records.destroy", ["course_class_attendance_record" => "__ID__"]) }}`.replace('__ID__', e.target.dataset.id), {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Accept': 'application/json'
        }
    });

    if (res.ok) {
        await fetchClassData();
    } else {
        alert(@json(__('Erro ao remover presença')));
    }
});

enrollmentForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const enrollmentId = enrollmentForm.enrollment_id.value;
    const selectedStudentId = enrollmentForm.student_id.value;

    if (!selectedStudentId) {
        alert(@json(__('Selecione um aluno na busca antes de salvar.')));
        return;
    }

    const existingEnrollment = (classData.enrollments || []).find(
        item => String(item.student_id) === String(selectedStudentId) && String(item.id) !== String(enrollmentId)
    );

    if (existingEnrollment) {
        alert(@json(__('Este aluno já está nesta turma.')));
        return;
    }

    const payload = {
        student_id: selectedStudentId,
        course_id: classData.course_id,
        course_class_id: classData.id,
        grade: enrollmentForm.grade.value || null,
        user_id: currentUserId
    };

    let url = '{{ route("api.course-enrollments.store") }}';
    let method = 'POST';

    if (enrollmentId) {
        url = `{{ route("api.course-enrollments.update", ["course_enrollment" => "__ID__"]) }}`.replace('__ID__', enrollmentId);
        method = 'PUT';
    } else {
        const matchingEnrollment = allEnrollments.find(item =>
            String(item.student_id) === String(selectedStudentId) &&
            String(item.course_id) === String(classData.course_id)
        );

        if (matchingEnrollment) {
            url = `{{ route("api.course-enrollments.update", ["course_enrollment" => "__ID__"]) }}`.replace('__ID__', matchingEnrollment.id);
            method = 'PUT';
        }
    }

    const res = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    if (res.ok) {
        resetEnrollmentForm();
        await loadEnrollments();
        await fetchClassData();

        
    } else {
        const error = await res.json().catch(() => null);
        alert(error?.message || @json(__('Erro ao salvar matrícula')));
    }
});

attendanceForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const attendanceId = attendanceForm.attendance_id.value;
    const payload = {
        name: attendanceForm.name.value || null,
        attendance_date: attendanceForm.attendance_date.value,
        duration_hours: attendanceForm.duration_hours.value || 1,
        user_id: currentUserId
    };

    let url = `{{ route("api.course-classes.attendances.store", ["course_class" => "__ID__"]) }}`.replace('__ID__', classId);
    let method = 'POST';

    if (attendanceId) {
        url = `{{ route("api.course-class-attendances.update", ["course_class_attendance" => "__ID__"]) }}`.replace('__ID__', attendanceId);
        method = 'PUT';
    }

    const res = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    if (res.ok) {
        const data = await res.json().catch(() => null);
        selectedAttendanceId = data?.id || selectedAttendanceId;
        attendanceListExpanded = true;
        attendanceList.classList.remove('hidden');
        resetAttendanceForm();
        await fetchClassData();
    } else {
        const error = await res.json().catch(() => null);
        alert(error?.message || @json(__('Erro ao salvar sessão')));
    }
});

attendanceRecordForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const attendance = getSelectedAttendance();
    if (!attendance) return;

    const res = await fetch(`{{ route("api.course-class-attendances.records.store", ["course_class_attendance" => "__ID__"]) }}`.replace('__ID__', attendance.id), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            student_id: attendanceRecordForm.student_id.value,
            user_id: currentUserId
        })
    });

    if (res.ok) {
        await fetchClassData();
    } else {
        const error = await res.json().catch(() => null);
        alert(error?.message || @json(__('Erro ao adicionar presença')));
    }
});

function renderUpcomingClasses() {
    const events = classData?.schedule_events || [];
    console.log('[upcoming] schedule_events:', events);
    const existingDates = new Set((classData?.attendances || []).map(a => (a.attendance_date || '').slice(0, 10)));
    const section = document.getElementById('upcomingClassesSection');
    const list = document.getElementById('upcomingClassesList');

    const upcoming = [];
    for (const event of events) {
        const duration = calculateEventDuration(event);
        const dates = getEventOccurrences(event);
        console.log('[upcoming] event:', event.title || '(no title)', 'dates:', dates, 'recurring:', event.is_recurring_weekly, 'weekday:', event.weekday);
        for (const date of dates) {
            if (date >= today && !existingDates.has(date)) {
                upcoming.push({ date, duration, title: event.title || null });
            }
        }
    }
    console.log('[upcoming] filtered:', upcoming, 'today:', today, 'existingDates:', [...existingDates]);
    upcoming.sort((a, b) => a.date.localeCompare(b.date));

    if (!upcoming.length) {
        section.classList.add('hidden');
        return;
    }

    section.classList.remove('hidden');
    list.innerHTML = upcoming.map(item => `
        <div class="flex items-center gap-3 rounded-xl border border-cyan-100 bg-cyan-50/30 px-4 py-3 text-sm">
            <span class="shrink-0 rounded-md bg-cyan-100 px-2 py-1 text-xs font-semibold text-cyan-700">${formatSessionDate(item.date)}</span>
            <span class="flex-1 font-medium text-gray-800">${item.title || formatSessionDate(item.date)}</span>
            <span class="shrink-0 text-gray-500">${formatHours(item.duration)}</span>
            <button type="button" class="createUpcomingBtn shrink-0 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700" data-date="${item.date}" data-duration="${item.duration}" data-title="${item.title || ''}">
                {{ __('Criar sessão') }}
            </button>
        </div>
    `).join('');
}

const scheduleWeekdayLabels = [
    @json(__('Domingo')),
    @json(__('Segunda-feira')),
    @json(__('Terça-feira')),
    @json(__('Quarta-feira')),
    @json(__('Quinta-feira')),
    @json(__('Sexta-feira')),
    @json(__('Sábado')),
];

function suggestionWindowEnd(event) {
    const base = event.end_date ? String(event.end_date).slice(0, 10) : today;
    const end = parseDate(base);
    end.setMonth(end.getMonth() + 12);
    return fmtDate(end);
}

function renderScheduleEvents() {
    const events = classData?.schedule_events || [];
    const section = document.getElementById('scheduleEventsSection');
    const list = document.getElementById('scheduleEventsList');

    if (!events.length) {
        section.classList.add('hidden');
        return;
    }

    section.classList.remove('hidden');
    list.innerHTML = events.map(event => {
        const startDate = String(event.start_date ?? '').slice(0, 10);
        const endDate = String(event.end_date ?? '').slice(0, 10);
        const startTime = String(event.start_time ?? '').slice(0, 5);
        const endTime = String(event.end_time ?? '').slice(0, 5);
        const weekday = event.weekday ?? '';
        const windowNote = event.is_recurring_weekly
            ? (event.end_date
                ? @json(__('Sugestões até')) + ' ' + formatSessionDate(suggestionWindowEnd(event)) + ' (' + @json(__('data final + 12 meses')) + ')'
                : @json(__('Sem data final: sugestões até')) + ' ' + formatSessionDate(suggestionWindowEnd(event)) + ' (' + @json(__('12 meses a partir de hoje')) + ')')
            : @json(__('Evento de data única'));

        return `
        <div class="rounded-2xl border border-cyan-100 bg-white p-4" data-event-id="${event.id}">
            <div class="flex items-start justify-between gap-3">
                <p class="text-sm font-semibold text-gray-900">${escapeHtml(event.title || @json(__('Sem título')))}</p>
                <span class="shrink-0 text-xs text-cyan-700">${windowNote}</span>
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600">{{ __('Título') }}</label>
                    <input type="text" data-field="title" value="${escapeHtml(event.title || '')}" class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">{{ __('Local') }}</label>
                    <input type="text" data-field="location" value="${escapeHtml(event.location || '')}" class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">{{ __('Data inicial') }}</label>
                    <input type="date" data-field="start_date" value="${startDate}" class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">{{ __('Data final (vazio = +12 meses)') }}</label>
                    <div class="mt-1 flex gap-2">
                        <input type="date" data-field="end_date" value="${endDate}" class="block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                        <button type="button" class="clearScheduleEndBtn shrink-0 rounded-xl bg-white px-3 py-2 text-xs font-medium text-gray-600 ring-1 ring-gray-300">{{ __('Limpar') }}</button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">{{ __('Dia da semana') }}</label>
                    <select data-field="weekday" class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                        ${scheduleWeekdayLabels.map((label, idx) => `<option value="${idx}" ${String(weekday) === String(idx) ? 'selected' : ''}>${label}</option>`).join('')}
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">{{ __('Início') }}</label>
                        <input type="time" data-field="start_time" value="${startTime}" class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">{{ __('Fim') }}</label>
                        <input type="time" data-field="end_time" value="${endTime}" class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                    </div>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <label class="inline-flex items-center gap-2 text-xs text-gray-700">
                    <input type="checkbox" data-field="is_recurring_weekly" ${event.is_recurring_weekly ? 'checked' : ''} class="rounded border-gray-300 text-cyan-600 shadow-sm">
                    {{ __('Repetir toda semana') }}
                </label>
                <button type="button" class="saveScheduleEventBtn ml-auto rounded-xl bg-cyan-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Salvar evento') }}</button>
            </div>
        </div>`;
    }).join('');
}

document.getElementById('scheduleEventsList')?.addEventListener('click', async (e) => {
    const card = e.target.closest('[data-event-id]');
    if (!card) return;

    if (e.target.closest('.clearScheduleEndBtn')) {
        card.querySelector('[data-field="end_date"]').value = '';
        return;
    }

    const btn = e.target.closest('.saveScheduleEventBtn');
    if (!btn) return;

    const get = (field) => card.querySelector(`[data-field="${field}"]`);
    const payload = {
        title: get('title').value || null,
        location: get('location').value || null,
        start_date: get('start_date').value || null,
        end_date: get('end_date').value || null,
        weekday: get('weekday').value === '' ? null : Number(get('weekday').value),
        is_recurring_weekly: get('is_recurring_weekly').checked,
        start_time: get('start_time').value || null,
        end_time: get('end_time').value || null,
    };

    btn.disabled = true;
    try {
        const res = await fetch(`{{ route("api.schedule-events.update", ["schedule_event" => "__ID__"]) }}`.replace('__ID__', card.dataset.eventId), {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-User-Id': currentUserId,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            await fetchClassData();
        } else {
            const error = await res.json().catch(() => null);
            alert(error?.message || @json(__('Erro ao salvar evento da agenda')));
        }
    } finally {
        btn.disabled = false;
    }
});

document.getElementById('upcomingClassesList')?.addEventListener('click', async (e) => {
    const btn = e.target.closest('.createUpcomingBtn');
    if (!btn) return;

    const res = await fetch(`{{ route("api.course-classes.attendances.store", ["course_class" => "__ID__"]) }}`.replace('__ID__', classId), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: btn.dataset.title || null,
            attendance_date: btn.dataset.date,
            duration_hours: btn.dataset.duration || 1,
            user_id: currentUserId
        })
    });

    if (res.ok) {
        await fetchClassData();
    } else {
        const error = await res.json().catch(() => null);
        alert(error?.message || '{{ __('Erro ao criar sessão') }}');
    }
});

async function init() {
    await loadStudents();
    await loadEnrollments();
    resetEnrollmentForm();
    resetAttendanceForm();
    await fetchClassData();
}

init();
</script>
@endsection
