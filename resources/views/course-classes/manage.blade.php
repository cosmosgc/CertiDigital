@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden shadow-sm sm:rounded-2xl p-6 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-start">
            <div>
                <a href="{{ route('course-classes.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Voltar para turmas') }}</a>
                <h2 id="className" class="text-2xl font-semibold mt-1"></h2>
                <p id="classMeta" class="text-sm text-gray-500"></p>
            </div>
            <div class="flex flex-col gap-3 sm:items-end">
                <a href="{{ route('course-classes.attendance-report', $courseClass) }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">{{ __('Relatório de presença') }}</a>
                <div class="rounded-2xl bg-gray-50 px-4 py-3 text-sm text-gray-600 ring-1 ring-gray-200">
                    <div><strong>{{ __('Curso:') }}</strong> <span id="courseTitle"></span></div>
                    <div><strong>{{ __('Instrutor:') }}</strong> <span id="instructorName"></span></div>
                    <div><strong>{{ __('Carga horária:') }}</strong> <span id="workloadHours">0h</span></div>
                    <div><strong>{{ __('Alunos:') }}</strong> <span id="studentCount">0</span></div>
                </div>
            </div>
        </div>

        <div id="classDescription" class="text-sm text-gray-600"></div>

        <div class="rounded-2xl border border-dashed border-emerald-300 bg-emerald-50/50 p-5">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('Adicionar aluno à turma') }}</h3>
            <p class="mt-1 text-sm text-gray-600">{{ __('O progresso agora é calculado automaticamente pelas presenças registradas nas sessões da turma.') }}</p>
            <form id="enrollmentForm" class="mt-4 space-y-4">
                <input type="hidden" name="enrollment_id" />
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Aluno') }}</label>
                        <select name="student_id" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Nota') }}</label>
                        <input type="number" min="0" max="100" step="0.01" name="grade" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-white">{{ __('Salvar matrícula') }}</button>
                    <button type="button" id="cancelEdit" class="hidden rounded-xl bg-white px-4 py-2 text-gray-700 ring-1 ring-gray-300">{{ __('Cancelar edição') }}</button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200">
            <table class="w-full table-auto" id="classStudentsTable">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-600">
                        <th class="p-2">{{ __('ID matrícula') }}</th>
                        <th class="p-2">{{ __('Aluno') }}</th>
                        <th class="p-2">{{ __('E-mail') }}</th>
                        <th class="p-2">{{ __('Progresso') }}</th>
                        <th class="p-2">{{ __('Nota') }}</th>
                        <th class="p-2">{{ __('Concluído') }}</th>
                        <th class="p-2">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
            <section class="rounded-2xl border border-cyan-200 bg-cyan-50/50 p-5">
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

            <section class="rounded-2xl border border-gray-200 bg-white p-5">
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

                <div class="mt-5 overflow-hidden rounded-2xl border border-gray-200">
                    <table class="w-full table-auto" id="attendanceRecordsTable">
                        <thead>
                            <tr class="bg-gray-50 text-left text-sm text-gray-600">
                                <th class="p-2">{{ __('Aluno') }}</th>
                                <th class="p-2">{{ __('E-mail') }}</th>
                                <th class="p-2">{{ __('Ação') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

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

function populateStudentOptions(selectedId = '') {
    enrollmentForm.student_id.innerHTML = `
        <option value="">{{ __('Selecione um aluno') }}</option>
        ${allStudents.map(student => `
            <option value="${student.id}" ${String(student.id) === String(selectedId) ? 'selected' : ''}>${student.full_name} (${student.email})</option>
        `).join('')}
    `;
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
    populateStudentOptions();
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
        tr.innerHTML = `
            <td class="p-2">${enrollment.id}</td>
            <td class="p-2">${enrollment.student?.full_name || ''}</td>
            <td class="p-2">${enrollment.student?.email || ''}</td>
            <td class="p-2">${formatHours(progressHours)} <span class="text-xs text-gray-500">(${progressPercent}%)</span></td>
            <td class="p-2">${enrollment.grade ?? ''}</td>
            <td class="p-2">${enrollment.completed ? @json(__('Sim')) : @json(__('Não'))}</td>
            <td class="p-2 flex flex-wrap gap-2">
                <a class="px-2 py-1 bg-slate-900 text-white rounded" href="${enrollmentShowBaseUrl.replace('__ENROLLMENT__', enrollment.id)}">{{ __('Ver aluno') }}</a>
                <button class="editBtn px-2 py-1 bg-yellow-400 rounded" data-id="${enrollment.id}">{{ __('Editar') }}</button>
                <button class="removeBtn px-2 py-1 bg-red-500 text-white rounded" data-id="${enrollment.id}">{{ __('Remover') }}</button>
            </td>
        `;
        studentsTableBody.appendChild(tr);
    });

    if (studentsTable) {
        studentsTable.destroy();
    }

    studentsTable = new DataTable('#classStudentsTable');

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
                <td colspan="3" class="p-4 text-sm text-gray-500">{{ __('Nenhuma sessão selecionada.') }}</td>
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
                <td colspan="3" class="p-4 text-sm text-gray-500">{{ __('Nenhum aluno marcado como presente nesta sessão.') }}</td>
            </tr>
        `;
        return;
    }

    attendanceRecordsTableBody.innerHTML = attendance.records.map(record => `
        <tr class="border-t border-gray-100">
            <td class="p-2">${record.student?.full_name || ''}</td>
            <td class="p-2">${record.student?.email || ''}</td>
            <td class="p-2">
                <button type="button" class="removeAttendanceRecordBtn rounded-lg bg-red-500 px-3 py-2 text-sm text-white" data-id="${record.id}">
                    {{ __('Remover presença') }}
                </button>
            </td>
        </tr>
    `).join('');
}

function resetEnrollmentForm() {
    enrollmentForm.reset();
    enrollmentForm.enrollment_id.value = '';
    enrollmentForm.student_id.disabled = false;
    populateStudentOptions();
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

studentsTableBody.addEventListener('click', async (e) => {
    if (e.target.classList.contains('editBtn')) {
        const enrollment = (classData.enrollments || []).find(item => String(item.id) === e.target.dataset.id);
        if (!enrollment) return;

        enrollmentForm.enrollment_id.value = enrollment.id;
        enrollmentForm.grade.value = enrollment.grade ?? '';
        populateStudentOptions(enrollment.student_id);
        enrollmentForm.student_id.disabled = true;
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
