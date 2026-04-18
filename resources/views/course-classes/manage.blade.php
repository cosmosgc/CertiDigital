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
                        <input type="number" min="0" max="100" step="0.01" name="grade" class="mt-1 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-emerald-400 focus:ring-emerald-400" />
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
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Sessões de presença') }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ __('Ao criar uma nova sessão, todos os alunos da turma começam marcados como presentes. Depois, você pode ajustar quem entrou atrasado ou faltou.') }}</p>

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

                <div id="attendanceList" class="mt-6 grid gap-3"></div>
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

function getWorkloadValue() {
    return Number(classData?.course?.workload_hours ?? 0);
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
    const res = await fetch(`{{ route("api.course-classes.show", ["course_class" => "__ID__"]) }}`.replace('__ID__', classId), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });

    classData = await res.json();

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
    classDescription.textContent = classData.description || @json(__('Sem descrição cadastrada.'));
    studentCount.textContent = classData.enrollments?.length || 0;

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

    if (studentsTable) {
        studentsTable.destroy();
    }

    studentsTable = new DataTable('#classStudentsTable');

    renderStudentSearchResults(studentSearchInput.value);
    renderAttendanceList();
    renderSelectedAttendance();
}

function renderAttendanceList() {
    const attendances = classData?.attendances || [];

    if (!attendances.length) {
        attendanceList.innerHTML = `
            <div class="rounded-2xl border border-dashed border-cyan-300 bg-white/80 p-5 text-sm text-gray-500">
                {{ __('Nenhuma sessão foi criada ainda.') }}
            </div>
        `;
        return;
    }

    attendanceList.innerHTML = attendances.map(attendance => `
        <article class="rounded-2xl border p-4 ${String(attendance.id) === String(selectedAttendanceId) ? 'border-cyan-500 bg-white shadow-sm' : 'border-cyan-100 bg-white/80'}">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900">${attendance.name}</p>
                    <p class="mt-1 text-sm text-gray-500">${attendance.attendance_date} • ${formatHours(attendance.duration_hours)}</p>
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
    `).join('');
}

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
    selectedAttendanceMeta.textContent = `${attendance.attendance_date} • ${formatHours(attendance.duration_hours)} • ${(attendance.records || []).length} {{ __('presenças') }}`;
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
        attendanceForm.attendance_date.value = attendance.attendance_date;
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
