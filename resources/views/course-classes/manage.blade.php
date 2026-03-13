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
            <div class="rounded-2xl bg-gray-50 px-4 py-3 text-sm text-gray-600 ring-1 ring-gray-200">
                <div><strong>{{ __('Curso:') }}</strong> <span id="courseTitle"></span></div>
                <div><strong>{{ __('Instrutor:') }}</strong> <span id="instructorName"></span></div>
                <div><strong>{{ __('Alunos:') }}</strong> <span id="studentCount">0</span></div>
            </div>
        </div>

        <div id="classDescription" class="text-sm text-gray-600"></div>

        <div class="rounded-2xl border border-dashed border-emerald-300 bg-emerald-50/50 p-5">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('Adicionar aluno à turma') }}</h3>
            <form id="enrollmentForm" class="mt-4 space-y-4">
                <input type="hidden" name="enrollment_id" />
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Aluno') }}</label>
                        <select name="student_id" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Progresso (%)') }}</label>
                        <input type="number" min="0" max="100" name="progress_percent" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Nota') }}</label>
                        <input type="number" min="0" max="100" step="0.01" name="grade" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div class="flex items-center gap-2 pt-7">
                        <input type="checkbox" name="completed" id="completed" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        <label for="completed" class="text-sm font-medium">{{ __('Concluído') }}</label>
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
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = @json(auth()->id());
const classId = @json($courseClass->id);
const className = document.getElementById('className');
const classMeta = document.getElementById('classMeta');
const classDescription = document.getElementById('classDescription');
const courseTitle = document.getElementById('courseTitle');
const instructorName = document.getElementById('instructorName');
const studentCount = document.getElementById('studentCount');
const enrollmentForm = document.getElementById('enrollmentForm');
const cancelEditBtn = document.getElementById('cancelEdit');
const tableBody = document.querySelector('#classStudentsTable tbody');
let studentsTable = null;
let classData = null;
let allStudents = [];
let allEnrollments = [];

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
    renderClassData();
}

function renderClassData() {
    className.textContent = classData.name;
    classMeta.textContent = `{{ __('Turma ID') }} #${classData.id}`;
    courseTitle.textContent = classData.course?.title || '';
    instructorName.textContent = classData.instructor?.full_name || @json(__('Não definido'));
    classDescription.textContent = classData.description || @json(__('Sem descrição cadastrada.'));
    studentCount.textContent = classData.enrollments?.length || 0;

    tableBody.innerHTML = '';

    (classData.enrollments || []).forEach(enrollment => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2">${enrollment.id}</td>
            <td class="p-2">${enrollment.student?.full_name || ''}</td>
            <td class="p-2">${enrollment.student?.email || ''}</td>
            <td class="p-2">${enrollment.progress_percent ?? 0}%</td>
            <td class="p-2">${enrollment.grade ?? ''}</td>
            <td class="p-2">${enrollment.completed ? @json(__('Sim')) : @json(__('Não'))}</td>
            <td class="p-2 flex flex-wrap gap-2">
                <button class="editBtn px-2 py-1 bg-yellow-400 rounded" data-id="${enrollment.id}">{{ __('Editar') }}</button>
                <button class="removeBtn px-2 py-1 bg-red-500 text-white rounded" data-id="${enrollment.id}">{{ __('Remover') }}</button>
            </td>
        `;
        tableBody.appendChild(tr);
    });

    if (studentsTable) {
        studentsTable.destroy();
    }

    studentsTable = new DataTable('#classStudentsTable');
}

function resetForm() {
    enrollmentForm.reset();
    enrollmentForm.enrollment_id.value = '';
    enrollmentForm.completed.checked = false;
    populateStudentOptions();
    cancelEditBtn.classList.add('hidden');
}

cancelEditBtn.addEventListener('click', resetForm);

tableBody.addEventListener('click', async (e) => {
    if (e.target.classList.contains('editBtn')) {
        const enrollment = (classData.enrollments || []).find(item => String(item.id) === e.target.dataset.id);
        if (!enrollment) return;

        enrollmentForm.enrollment_id.value = enrollment.id;
        enrollmentForm.progress_percent.value = enrollment.progress_percent ?? 0;
        enrollmentForm.grade.value = enrollment.grade ?? '';
        enrollmentForm.completed.checked = Boolean(enrollment.completed);
        populateStudentOptions(enrollment.student_id);
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
            resetForm();
            fetchClassData();
        } else {
            alert(@json(__('Erro ao remover matrícula')));
        }
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
        progress_percent: enrollmentForm.progress_percent.value || 0,
        grade: enrollmentForm.grade.value || null,
        completed: enrollmentForm.completed.checked,
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
        resetForm();
        await loadEnrollments();
        fetchClassData();
    } else {
        const error = await res.json().catch(() => null);
        alert(error?.message || @json(__('Erro ao salvar matrícula')));
    }
});

async function init() {
    await loadStudents();
    await loadEnrollments();
    await fetchClassData();
    resetForm();
}

init();
</script>
@endsection
