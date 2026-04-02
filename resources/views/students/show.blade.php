@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="space-y-6">
        <section class="overflow-hidden rounded-[28px] bg-gradient-to-r from-cyan-700 via-sky-700 to-blue-700 px-6 py-8 text-white shadow-xl">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <a href="{{ route('students.index') }}" class="text-sm text-sky-100/90 hover:text-white">{{ __('Voltar para alunos') }}</a>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.25em] text-sky-100">{{ __('Ficha do aluno') }}</p>
                    <h1 id="studentName" class="mt-2 text-3xl font-semibold text-white">{{ $student->full_name }}</h1>
                    <p id="studentMeta" class="mt-3 max-w-2xl text-sm text-sky-50/90"></p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button id="refreshButton" type="button" class="inline-flex items-center rounded-xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur">{{ __('Atualizar dados') }}</button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Matrículas') }}</p>
                <p id="enrollmentCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Certificados') }}</p>
                <p id="certificateCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Anotações') }}</p>
                <p id="annotationCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Alertas graves') }}</p>
                <p id="criticalWarningCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
            <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ __('Matrículas do aluno') }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Cursos e turmas vinculados a este aluno.') }}</p>
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full table-auto border-separate border-spacing-0">
                        <thead>
                            <tr>
                                <th class="bg-gray-50 p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Curso') }}</th>
                                <th class="bg-gray-50 p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Turma') }}</th>
                                <th class="bg-gray-50 p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Horas') }}</th>
                                <th class="bg-gray-50 p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Nota') }}</th>
                                <th class="bg-gray-50 p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Status') }}</th>
                                <th class="bg-gray-50 p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody id="enrollmentsTableBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 id="annotationFormTitle" class="text-xl font-semibold text-gray-900">{{ __('Nova anotação') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Registre observações, alertas e ocorrências do aluno.') }}</p>

                <form id="annotationForm" class="mt-6 space-y-4">
                    <input type="hidden" name="id" />
                    <div>
                        <label for="annotationDate" class="block text-sm font-medium text-gray-700">{{ __('Data') }}</label>
                        <input id="annotationDate" name="annotation_date" type="date" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label for="warningLevel" class="block text-sm font-medium text-gray-700">{{ __('Nível de alerta') }}</label>
                        <select id="warningLevel" name="warning_level" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></select>
                    </div>
                    <div>
                        <label for="courseClassId" class="block text-sm font-medium text-gray-700">{{ __('Turma relacionada') }}</label>
                        <select id="courseClassId" name="course_class_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></select>
                    </div>
                    <div>
                        <label for="attendanceRecordId" class="block text-sm font-medium text-gray-700">{{ __('Presença relacionada') }}</label>
                        <select id="attendanceRecordId" name="course_class_attendance_record_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></select>
                    </div>
                    <div>
                        <label for="annotationNotes" class="block text-sm font-medium text-gray-700">{{ __('Anotação') }}</label>
                        <textarea id="annotationNotes" name="notes" rows="5" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="rounded-xl bg-sky-600 px-4 py-2 text-white shadow-sm">{{ __('Salvar anotação') }}</button>
                        <button id="cancelEditButton" type="button" class="hidden rounded-xl bg-white px-4 py-2 text-gray-700 ring-1 ring-gray-300">{{ __('Cancelar edição') }}</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('Histórico de anotações') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Linha do tempo com alertas e observações registradas pela equipe.') }}</p>
            <div id="annotationsList" class="mt-6 grid gap-4"></div>
        </section>
    </div>
</div>

<script>
const studentId = @json($student->id);
const currentUserId = @json(auth()->id());
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const studentName = document.getElementById('studentName');
const studentMeta = document.getElementById('studentMeta');
const enrollmentCount = document.getElementById('enrollmentCount');
const certificateCount = document.getElementById('certificateCount');
const annotationCount = document.getElementById('annotationCount');
const criticalWarningCount = document.getElementById('criticalWarningCount');
const enrollmentsTableBody = document.getElementById('enrollmentsTableBody');
const annotationsList = document.getElementById('annotationsList');
const annotationForm = document.getElementById('annotationForm');
const annotationFormTitle = document.getElementById('annotationFormTitle');
const annotationDate = document.getElementById('annotationDate');
const warningLevel = document.getElementById('warningLevel');
const courseClassId = document.getElementById('courseClassId');
const attendanceRecordId = document.getElementById('attendanceRecordId');
const annotationNotes = document.getElementById('annotationNotes');
const cancelEditButton = document.getElementById('cancelEditButton');
const refreshButton = document.getElementById('refreshButton');
const apiStudentUrl = @json(route('api.admin.students.show', ['student' => $student]));
const annotationStoreUrl = @json(route('api.student-annotations.store'));
const annotationShowUrl = @json(route('api.student-annotations.show', ['student_annotation' => '__ID__']));
const annotationUpdateUrl = @json(route('api.student-annotations.update', ['student_annotation' => '__ID__']));
const annotationDestroyUrl = @json(route('api.student-annotations.destroy', ['student_annotation' => '__ID__']));
const courseClassShowUrl = @json(route('api.course-classes.show', ['course_class' => '__ID__']));
const enrollmentShowUrl = @json(route('course-class-enrollments.show', ['courseClass' => '__CLASS__', 'courseEnrollment' => '__ENROLLMENT__']));

const warningLabels = [
    @json(__('0 - Nota simples')),
    @json(__('1 - Observação leve')),
    @json(__('2 - Atenção')),
    @json(__('3 - Advertência séria')),
    @json(__('4 - Advertência grave')),
];

let studentData = null;
let classOptions = [];
const classRecordsCache = new Map();

function formatDate(value) {
    if (!value) return '-';
    return String(value).slice(0, 10);
}

function formatHours(value) {
    const numeric = Number(value ?? 0);
    return `${numeric % 1 === 0 ? numeric.toFixed(0) : numeric.toFixed(2)}h`;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function getWarningBadgeClasses(level) {
    const classes = {
        0: 'bg-slate-100 text-slate-700',
        1: 'bg-sky-100 text-sky-700',
        2: 'bg-amber-100 text-amber-700',
        3: 'bg-orange-100 text-orange-700',
        4: 'bg-rose-100 text-rose-700',
    };

    return classes[level] || classes[0];
}

function populateWarningLevels() {
    warningLevel.innerHTML = warningLabels.map((label, index) => `<option value="${index}">${label}</option>`).join('');
}

function collectClassOptions(data) {
    const optionMap = new Map();

    (data.enrollments || []).forEach(enrollment => {
        if (enrollment.course_class_id && enrollment.course_class) {
            optionMap.set(String(enrollment.course_class_id), {
                id: enrollment.course_class_id,
                label: `${enrollment.course?.title || '-'} • ${enrollment.course_class?.name || '-'}`,
            });
        }
    });

    (data.annotations || []).forEach(annotation => {
        if (annotation.course_class_id && annotation.course_class) {
            optionMap.set(String(annotation.course_class_id), {
                id: annotation.course_class_id,
                label: `${annotation.course_class?.course?.title || '-'} • ${annotation.course_class?.name || '-'}`,
            });
        }
    });

    return Array.from(optionMap.values()).sort((a, b) => a.label.localeCompare(b.label));
}

function renderClassOptions(selectedValue = '') {
    courseClassId.innerHTML = `
        <option value="">{{ __('Sem turma vinculada') }}</option>
        ${classOptions.map(option => `<option value="${option.id}" ${String(option.id) === String(selectedValue) ? 'selected' : ''}>${escapeHtml(option.label)}</option>`).join('')}
    `;
}

function renderAttendanceOptions(records, selectedValue = '') {
    attendanceRecordId.innerHTML = `
        <option value="">{{ __('Sem presença vinculada') }}</option>
        ${records.map(record => `
            <option value="${record.id}" ${String(record.id) === String(selectedValue) ? 'selected' : ''}>
                ${escapeHtml(record.label)}
            </option>
        `).join('')}
    `;
}

async function loadAttendanceOptions(selectedClassId, selectedRecordId = '') {
    if (!selectedClassId) {
        renderAttendanceOptions([], '');
        return;
    }

    if (!classRecordsCache.has(String(selectedClassId))) {
        const res = await fetch(courseClassShowUrl.replace('__ID__', selectedClassId), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        const records = (data.attendances || []).flatMap(attendance =>
            (attendance.records || [])
                .filter(record => String(record.student_id) === String(studentId))
                .map(record => ({
                    id: record.id,
                    label: `${attendance.name} • ${formatDate(attendance.attendance_date)}`,
                }))
        );
        classRecordsCache.set(String(selectedClassId), records);
    }

    renderAttendanceOptions(classRecordsCache.get(String(selectedClassId)) || [], selectedRecordId);
}

function renderEnrollments(data) {
    const enrollments = data.enrollments || [];

    if (!enrollments.length) {
        enrollmentsTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="p-8 text-center text-gray-500 ring-1 ring-gray-200">
                    {{ __('Este aluno ainda não possui matrículas registradas.') }}
                </td>
            </tr>
        `;
        return;
    }

    enrollmentsTableBody.innerHTML = enrollments.map(enrollment => {
        const classUrl = enrollment.course_class_id
            ? enrollmentShowUrl
                .replace('__CLASS__', enrollment.course_class_id)
                .replace('__ENROLLMENT__', enrollment.id)
            : '';

        return `
            <tr>
                <td class="p-3 ring-1 ring-gray-200">${escapeHtml(enrollment.course?.title || '-')}</td>
                <td class="p-3 ring-1 ring-gray-200">${escapeHtml(enrollment.course_class?.name || '{{ __('Sem turma') }}')}</td>
                <td class="p-3 ring-1 ring-gray-200">${formatHours(enrollment.progress_hours)}</td>
                <td class="p-3 ring-1 ring-gray-200">${enrollment.grade ?? '-'}</td>
                <td class="p-3 ring-1 ring-gray-200">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${enrollment.completed ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">
                        ${enrollment.completed ? @json(__('Concluído')) : @json(__('Em andamento'))}
                    </span>
                </td>
                <td class="p-3 ring-1 ring-gray-200">
                    ${classUrl
                        ? `<a href="${classUrl}" class="inline-flex rounded-xl bg-sky-600 px-3 py-2 text-sm font-medium text-white">{{ __('Abrir matrícula') }}</a>`
                        : `<span class="text-sm text-gray-400">{{ __('Sem turma') }}</span>`}
                </td>
            </tr>
        `;
    }).join('');
}

function renderAnnotations(data) {
    const annotations = data.annotations || [];

    if (!annotations.length) {
        annotationsList.innerHTML = `
            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-500">
                {{ __('Nenhuma anotação registrada para este aluno.') }}
            </div>
        `;
        return;
    }

    annotationsList.innerHTML = annotations.map(annotation => `
        <article class="rounded-2xl border border-gray-200 bg-white p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${getWarningBadgeClasses(Number(annotation.warning_level))}">
                            ${escapeHtml(warningLabels[Number(annotation.warning_level)] || warningLabels[0])}
                        </span>
                        <span class="text-sm text-gray-500">${formatDate(annotation.annotation_date)}</span>
                    </div>
                    <p class="whitespace-pre-line text-sm text-gray-700">${escapeHtml(annotation.notes)}</p>
                    <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                        <span class="rounded-full bg-gray-100 px-3 py-1">{{ __('Turma') }}: ${escapeHtml(annotation.course_class?.name || '{{ __('Nenhuma') }}')}</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1">{{ __('Curso') }}: ${escapeHtml(annotation.course_class?.course?.title || '{{ __('Nenhum') }}')}</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1">{{ __('Presença') }}: ${escapeHtml(annotation.attendance_record?.attendance?.name || '{{ __('Nenhuma') }}')}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="editAnnotationBtn rounded-xl bg-amber-400 px-3 py-2 text-sm font-medium text-slate-900" data-id="${annotation.id}">{{ __('Editar') }}</button>
                    <button type="button" class="deleteAnnotationBtn rounded-xl bg-rose-600 px-3 py-2 text-sm font-medium text-white" data-id="${annotation.id}">{{ __('Excluir') }}</button>
                </div>
            </div>
        </article>
    `).join('');
}

function resetAnnotationForm() {
    annotationForm.reset();
    annotationForm.id.value = '';
    annotationFormTitle.textContent = @json(__('Nova anotação'));
    annotationDate.value = new Date().toISOString().slice(0, 10);
    warningLevel.value = '0';
    renderClassOptions('');
    renderAttendanceOptions([], '');
    cancelEditButton.classList.add('hidden');
}

async function fillAnnotationForm(annotationId) {
    const annotation = (studentData.annotations || []).find(item => String(item.id) === String(annotationId));

    if (!annotation) return;

    annotationForm.id.value = annotation.id;
    annotationFormTitle.textContent = @json(__('Editar anotação'));
    annotationDate.value = formatDate(annotation.annotation_date);
    warningLevel.value = String(annotation.warning_level ?? 0);
    annotationNotes.value = annotation.notes || '';
    renderClassOptions(annotation.course_class_id || '');
    await loadAttendanceOptions(annotation.course_class_id || '', annotation.course_class_attendance_record_id || '');
    cancelEditButton.classList.remove('hidden');
    window.scrollTo({ top: annotationForm.offsetTop - 120, behavior: 'smooth' });
}

async function fetchStudentData() {
    const res = await fetch(apiStudentUrl, {
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-User-Id': String(currentUserId),
        }
    });
    studentData = await res.json();
    classOptions = collectClassOptions(studentData);
    renderStudent(studentData);
}

function renderStudent(data) {
    const annotations = data.annotations || [];
    const enrollments = data.enrollments || [];

    studentName.textContent = data.full_name || '';
    studentMeta.textContent = `${data.email || '-'} • {{ __('Documento') }}: ${data.document_id || '-'}`;
    enrollmentCount.textContent = enrollments.length;
    certificateCount.textContent = (data.certificates || []).length;
    annotationCount.textContent = annotations.length;
    criticalWarningCount.textContent = annotations.filter(annotation => Number(annotation.warning_level) >= 4).length;

    renderClassOptions(courseClassId.value);
    renderEnrollments(data);
    renderAnnotations(data);

    if (!annotationForm.id.value) {
        renderAttendanceOptions([], '');
    }
}

async function submitAnnotation(event) {
    event.preventDefault();

    const id = annotationForm.id.value;
    const url = id ? annotationUpdateUrl.replace('__ID__', id) : annotationStoreUrl;
    const method = id ? 'PUT' : 'POST';

    const payload = {
        user_id: currentUserId,
        student_id: studentId,
        annotation_date: annotationDate.value,
        warning_level: Number(warningLevel.value),
        course_class_id: courseClassId.value || null,
        course_class_attendance_record_id: attendanceRecordId.value || null,
        notes: annotationNotes.value,
    };

    const res = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    });

    if (!res.ok) {
        const error = await res.json().catch(() => null);
        alert(error?.message || @json(__('Não foi possível salvar a anotação.')));
        return;
    }

    resetAnnotationForm();
    await fetchStudentData();
}

async function deleteAnnotation(annotationId) {
    if (!confirm(@json(__('Excluir esta anotação?')))) return;

    const res = await fetch(annotationDestroyUrl.replace('__ID__', annotationId), {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ user_id: currentUserId }),
    });

    if (!res.ok) {
        const error = await res.json().catch(() => null);
        alert(error?.message || @json(__('Não foi possível excluir a anotação.')));
        return;
    }

    if (String(annotationForm.id.value) === String(annotationId)) {
        resetAnnotationForm();
    }

    await fetchStudentData();
}

populateWarningLevels();
resetAnnotationForm();
fetchStudentData();

annotationForm.addEventListener('submit', submitAnnotation);
refreshButton.addEventListener('click', fetchStudentData);
cancelEditButton.addEventListener('click', resetAnnotationForm);
courseClassId.addEventListener('change', async () => {
    await loadAttendanceOptions(courseClassId.value, '');
});

annotationsList.addEventListener('click', async (event) => {
    const editButton = event.target.closest('.editAnnotationBtn');
    const deleteButton = event.target.closest('.deleteAnnotationBtn');

    if (editButton) {
        await fillAnnotationForm(editButton.dataset.id);
    }

    if (deleteButton) {
        await deleteAnnotation(deleteButton.dataset.id);
    }
});
</script>
@endsection
