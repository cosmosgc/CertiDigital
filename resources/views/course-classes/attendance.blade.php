@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="space-y-6">
        <section class="overflow-hidden rounded-[28px] bg-gradient-to-r from-cyan-700 via-sky-700 to-blue-700 px-6 py-8 text-white shadow-xl">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <a href="{{ route('course-classes.show', $courseClass) }}" class="text-sm text-cyan-100/90 hover:text-white">{{ __('Voltar para a turma') }}</a>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100">{{ __('Sessão de presença') }}</p>
                    <h1 id="attendanceName" class="mt-2 text-3xl font-semibold"></h1>
                    <p id="attendanceMeta" class="mt-3 max-w-2xl text-sm text-cyan-50/90"></p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('course-classes.manage', $courseClass) }}" class="inline-flex items-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-cyan-800 shadow">{{ __('Gerenciar turma') }}</a>
                    <button id="refreshButton" type="button" class="inline-flex items-center rounded-xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur">{{ __('Atualizar dados') }}</button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Turma') }}</p>
                <p id="className" class="mt-3 text-lg font-semibold text-gray-900"></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Curso') }}</p>
                <p id="courseTitle" class="mt-3 text-lg font-semibold text-gray-900"></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Horas da sessão') }}</p>
                <p id="attendanceHours" class="mt-3 text-3xl font-semibold text-gray-900">0h</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Presentes') }}</p>
                <p id="attendanceCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1fr,1fr]">
            <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('Editar sessão') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Atualize nome, data e duração da sessão. O progresso dos alunos será recalculado automaticamente.') }}</p>

                <form id="attendanceForm" class="mt-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Nome') }}</label>
                        <input type="text" name="name" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" placeholder="{{ __('Padrão: Attendance #ID') }}">
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Data') }}</label>
                            <input type="date" name="attendance_date" value="{{ $courseClassAttendance->attendance_date ? \Illuminate\Support\Carbon::parse($courseClassAttendance->attendance_date)->format('Y-m-d') : '' }}" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Horas') }}</label>
                            <input type="number" min="0.25" step="0.25" name="duration_hours" value="1" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-2 text-white">{{ __('Salvar sessão') }}</button>
                        <button type="button" id="deleteAttendanceButton" class="rounded-xl bg-red-500 px-4 py-2 text-white">{{ __('Excluir sessão') }}</button>
                    </div>
                </form>
            </div>

            <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('Gerenciar presença') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Clique em cada aluno para alternar entre presente e ausente nesta sessão.') }}</p>

                <div id="attendanceRoster" class="mt-6 grid gap-3 sm:grid-cols-2"></div>
            </div>
        </section>

        <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('Nova anotação de aluno') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Selecione um aluno presente nesta sessão para registrar uma anotação já vinculada ao registro de presença.') }}</p>
                </div>
                <div id="annotationContext" class="rounded-2xl bg-sky-50 px-4 py-3 text-sm text-sky-800 ring-1 ring-sky-100">
                    {{ __('Nenhum aluno selecionado.') }}
                </div>
            </div>

            <form id="annotationForm" class="mt-6 grid gap-4 lg:grid-cols-[0.9fr,1.1fr]">
                <div class="grid gap-4">
                    <div>
                        <label for="annotationStudent" class="block text-sm font-medium text-gray-700">{{ __('Aluno') }}</label>
                        <select id="annotationStudent" name="student_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></select>
                    </div>
                    <div>
                        <label for="annotationDate" class="block text-sm font-medium text-gray-700">{{ __('Data') }}</label>
                        <input id="annotationDate" name="annotation_date" type="date" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label for="annotationWarningLevel" class="block text-sm font-medium text-gray-700">{{ __('Nível de alerta') }}</label>
                        <select id="annotationWarningLevel" name="warning_level" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></select>
                    </div>
                </div>
                <div class="grid gap-4">
                    <div>
                        <label for="annotationNotes" class="block text-sm font-medium text-gray-700">{{ __('Anotação') }}</label>
                        <textarea id="annotationNotes" name="notes" rows="6" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="rounded-xl bg-sky-600 px-4 py-2 text-white">{{ __('Salvar anotação') }}</button>
                        <button type="button" id="clearAnnotationButton" class="rounded-xl bg-white px-4 py-2 text-gray-700 ring-1 ring-gray-300">{{ __('Limpar') }}</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = @json(auth()->id());
const classId = @json($courseClass->id);
const attendanceId = @json($courseClassAttendance->id);
const attendanceName = document.getElementById('attendanceName');
const attendanceMeta = document.getElementById('attendanceMeta');
const className = document.getElementById('className');
const courseTitle = document.getElementById('courseTitle');
const attendanceHours = document.getElementById('attendanceHours');
const attendanceCount = document.getElementById('attendanceCount');
const attendanceForm = document.getElementById('attendanceForm');
const attendanceRoster = document.getElementById('attendanceRoster');
const deleteAttendanceButton = document.getElementById('deleteAttendanceButton');
const refreshButton = document.getElementById('refreshButton');
const annotationForm = document.getElementById('annotationForm');
const annotationStudent = document.getElementById('annotationStudent');
const annotationDate = document.getElementById('annotationDate');
const annotationWarningLevel = document.getElementById('annotationWarningLevel');
const annotationNotes = document.getElementById('annotationNotes');
const annotationContext = document.getElementById('annotationContext');
const clearAnnotationButton = document.getElementById('clearAnnotationButton');
const classShowUrl = @json(route('course-classes.show', $courseClass));
const studentAnnotationStoreUrl = @json(route('api.student-annotations.store'));

let classData = null;
let attendanceData = null;

const warningLabels = [
    @json(__('0 - Nota simples')),
    @json(__('1 - Observação leve')),
    @json(__('2 - Atenção')),
    @json(__('3 - Advertência séria')),
    @json(__('4 - Advertência grave')),
];
annotationDate.value = new Date().toISOString().slice(0, 10);
function formatHours(value) {
    const numeric = Number(value ?? 0);
    return `${numeric % 1 === 0 ? numeric.toFixed(0) : numeric.toFixed(2)}h`;
}

function formatDateInputValue(value) {
    if (!value) return '';

    return String(value).slice(0, 10);
}

function getAttendance() {
    return (classData?.attendances || []).find(attendance => String(attendance.id) === String(attendanceId)) || null;
}

function getAttendanceRecordMap() {
    return new Map((attendanceData?.records || []).map(record => [String(record.student_id), record]));
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function populateAnnotationWarningLevels() {
    annotationWarningLevel.innerHTML = warningLabels.map((label, index) => `
        <option value="${index}">${label}</option>
    `).join('');
}

function getAnnotationStudentOptions() {
    const attendanceRecordMap = getAttendanceRecordMap();

    return (classData?.enrollments || [])
        .map(enrollment => {
            const record = attendanceRecordMap.get(String(enrollment.student_id));

            if (!record) {
                return null;
            }

            return {
                studentId: enrollment.student_id,
                recordId: record.id,
                label: `${enrollment.student?.full_name || ''} • ${enrollment.student?.email || ''}`,
            };
        })
        .filter(Boolean)
        .sort((left, right) => left.label.localeCompare(right.label));
}

function renderAnnotationStudentOptions(selectedStudentId = '') {
    const options = getAnnotationStudentOptions();

    annotationStudent.innerHTML = `
        <option value="">{{ __('Selecione um aluno presente') }}</option>
        ${options.map(option => `
            <option value="${option.studentId}" data-record-id="${option.recordId}" ${String(option.studentId) === String(selectedStudentId) ? 'selected' : ''}>
                ${escapeHtml(option.label)}
            </option>
        `).join('')}
    `;

    updateAnnotationContext();
}

function updateAnnotationContext() {
    const selectedOption = annotationStudent.selectedOptions[0];
    const recordId = selectedOption?.dataset.recordId;
    const studentLabel = selectedOption?.textContent?.trim();

    if (!recordId) {
        annotationContext.textContent = @json(__('Nenhum aluno selecionado.'));
        return;
    }

    annotationContext.textContent = `${studentLabel} • {{ __('Registro de presença') }} #${recordId}`;
}

function selectAnnotationTarget(studentId, recordId) {
    const targetOption = Array.from(annotationStudent.options).find(option => (
        String(option.value) === String(studentId) && String(option.dataset.recordId) === String(recordId)
    ));

    if (!targetOption) {
        return;
    }

    annotationStudent.value = String(studentId);
    updateAnnotationContext();
    annotationNotes.focus();
    annotationForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function resetAnnotationForm() {
    annotationForm.reset();
    annotationDate.value = new Date().toISOString().slice(0, 10);
    annotationWarningLevel.value = '0';
    renderAnnotationStudentOptions('');
}

function getProgressPercent(hours, workload) {
    const workloadValue = Number(workload ?? 0);

    if (!workloadValue) return 0;

    return Math.min(100, Math.round((Number(hours ?? 0) / workloadValue) * 100));
}

async function fetchAttendanceData() {
    const res = await fetch(`{{ route("api.course-classes.show", ["course_class" => "__ID__"]) }}`.replace('__ID__', classId), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });

    classData = await res.json();
    attendanceData = getAttendance();

    if (!attendanceData) {
        window.location.href = classShowUrl;
        return;
    }

    renderAttendance();
}

function renderAttendance() {
    className.textContent = classData.name;
    courseTitle.textContent = classData.course?.title || '';
    attendanceName.textContent = attendanceData.name;
    attendanceMeta.textContent = `${formatDateInputValue(attendanceData.attendance_date)} • ${formatHours(attendanceData.duration_hours)}`;
    attendanceHours.textContent = formatHours(attendanceData.duration_hours);
    attendanceCount.textContent = attendanceData.records?.length || 0;

    attendanceForm.name.value = attendanceData.name ?? '';
    attendanceForm.attendance_date.value = formatDateInputValue(attendanceData.attendance_date);
    attendanceForm.duration_hours.value = attendanceData.duration_hours ?? 1;
    renderAnnotationStudentOptions(annotationStudent.value);

    const enrollments = classData?.enrollments || [];
    const attendanceRecordMap = getAttendanceRecordMap();
    const workloadHours = Number(classData?.course?.workload_hours ?? 0);

    if (!enrollments.length) {
        attendanceRoster.innerHTML = `
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-500">
                {{ __('Nenhum aluno está matriculado nesta turma.') }}
            </div>
        `;
        return;
    }

    attendanceRoster.innerHTML = enrollments.map(enrollment => {
        const record = attendanceRecordMap.get(String(enrollment.student_id));
        const present = Boolean(record);
        const progressHours = Number(enrollment.progress_hours ?? 0);
        const progressPercent = getProgressPercent(progressHours, workloadHours);

        return `
            <div
                class="attendanceToggle relative w-full rounded-2xl border p-4 pr-24 text-left transition ${present ? 'border-emerald-300 bg-emerald-50 hover:bg-emerald-100/70' : 'border-gray-200 bg-white hover:bg-gray-50'}"
                data-student-id="${enrollment.student_id}"
                data-record-id="${record?.id ?? ''}"
            >
                <span class="absolute right-4 top-4 rounded-full px-3 py-1 text-xs font-semibold ${present ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700'}">
                    ${present ? @json(__('Presente')) : @json(__('Ausente'))}
                </span>
                <div class="min-w-0">
                    <div class="pr-2">
                        <p class="text-base font-semibold text-gray-900">${enrollment.student?.full_name || ''}</p>
                        <p class="mt-1 break-all text-sm text-gray-500">${enrollment.student?.email || ''}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                    <span class="text-gray-500">{{ __('Progresso atual') }}</span>
                    <span class="font-medium text-gray-900">${formatHours(progressHours)} (${progressPercent}%)</span>
                </div>
                <div class="mt-4 flex justify-end">
                    ${present ? `
                        <button
                            type="button"
                            class="openAnnotationButton rounded-xl bg-sky-600 px-3 py-2 text-sm font-medium text-white"
                            data-student-id="${enrollment.student_id}"
                            data-record-id="${record.id}"
                        >
                            {{ __('Anotar ocorrência') }}
                        </button>
                    ` : `
                        <span class="text-xs text-gray-400">{{ __('Crie o registro de presença para anotar nesta sessão.') }}</span>
                    `}
                </div>
            </div>
        `;
    }).join('');
}

attendanceForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const res = await fetch(`{{ route("api.course-class-attendances.update", ["course_class_attendance" => "__ID__"]) }}`.replace('__ID__', attendanceId), {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: attendanceForm.name.value || null,
            attendance_date: attendanceForm.attendance_date.value,
            duration_hours: attendanceForm.duration_hours.value || 1,
            user_id: currentUserId
        })
    });

    if (res.ok) {
        await fetchAttendanceData();
    } else {
        const error = await res.json().catch(() => null);
        alert(error?.message || @json(__('Erro ao salvar sessão')));
    }
});

attendanceRoster.addEventListener('click', async (e) => {
    const annotationButton = e.target.closest('.openAnnotationButton');
    if (annotationButton) {
        e.stopPropagation();
        selectAnnotationTarget(annotationButton.dataset.studentId, annotationButton.dataset.recordId);
        return;
    }

    const toggleButton = e.target.closest('.attendanceToggle');
    if (!toggleButton) return;

    const recordId = toggleButton.dataset.recordId;
    const studentId = toggleButton.dataset.studentId;
    toggleButton.disabled = true;

    let res;

    if (recordId) {
        res = await fetch(`{{ route("api.course-class-attendance-records.destroy", ["course_class_attendance_record" => "__ID__"]) }}`.replace('__ID__', recordId), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-User-Id': currentUserId,
                'Accept': 'application/json'
            }
        });
    } else {
        res = await fetch(`{{ route("api.course-class-attendances.records.store", ["course_class_attendance" => "__ID__"]) }}`.replace('__ID__', attendanceId), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-User-Id': currentUserId,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                student_id: studentId,
                user_id: currentUserId
            })
        });
    }

    if (res.ok) {
        await fetchAttendanceData();
    } else {
        const error = await res.json().catch(() => null);
        alert(error?.message || (recordId ? @json(__('Erro ao remover presença')) : @json(__('Erro ao adicionar presença'))));
        toggleButton.disabled = false;
    }
});

deleteAttendanceButton.addEventListener('click', async () => {
    if (!confirm(@json(__('Excluir esta sessão de presença?')))) return;

    const res = await fetch(`{{ route("api.course-class-attendances.destroy", ["course_class_attendance" => "__ID__"]) }}`.replace('__ID__', attendanceId), {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Accept': 'application/json'
        }
    });

    if (res.ok) {
        window.location.href = classShowUrl;
    } else {
        alert(@json(__('Erro ao excluir sessão')));
    }
});

refreshButton.addEventListener('click', fetchAttendanceData);
annotationStudent.addEventListener('change', updateAnnotationContext);
clearAnnotationButton.addEventListener('click', resetAnnotationForm);
annotationForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const selectedOption = annotationStudent.selectedOptions[0];
    const recordId = selectedOption?.dataset.recordId;

    if (!annotationStudent.value || !recordId) {
        alert(@json(__('Selecione um aluno com presença registrada nesta sessão.')));
        return;
    }

    const res = await fetch(studentAnnotationStoreUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            user_id: currentUserId,
            student_id: Number(annotationStudent.value),
            course_class_id: classId,
            course_class_attendance_record_id: Number(recordId),
            annotation_date: annotationDate.value,
            warning_level: Number(annotationWarningLevel.value),
            notes: annotationNotes.value
        })
    });

    if (res.ok) {
        resetAnnotationForm();
        alert(@json(__('Anotação salva com sucesso.')));
    } else {
        const error = await res.json().catch(() => null);
        alert(error?.message || @json(__('Erro ao salvar anotação.')));
    }
});

populateAnnotationWarningLevels();
fetchAttendanceData();
</script>
@endsection
