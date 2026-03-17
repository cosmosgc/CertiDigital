@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="space-y-6">
        <section class="overflow-hidden rounded-[28px] bg-gradient-to-r from-amber-600 via-orange-600 to-rose-600 px-6 py-8 text-white shadow-xl">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <a href="{{ route('course-classes.show', $courseClass) }}" class="text-sm text-orange-100/90 hover:text-white">{{ __('Voltar para a turma') }}</a>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.25em] text-orange-100">{{ __('Aluno da turma') }}</p>
                    <h1 id="studentName" class="mt-2 text-3xl font-semibold"></h1>
                    <p id="studentMeta" class="mt-3 max-w-2xl text-sm text-orange-50/90"></p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('course-classes.attendance-report', $courseClass) }}" class="inline-flex items-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-orange-700 shadow">{{ __('Relatório de presença') }}</a>
                    <button id="printButton" type="button" class="inline-flex items-center rounded-xl bg-amber-300 px-4 py-3 text-sm font-semibold text-slate-900 shadow">{{ __('Imprimir aluno') }}</button>
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
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Horas cumpridas') }}</p>
                <p id="progressHours" class="mt-3 text-3xl font-semibold text-gray-900">0h</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Presenças') }}</p>
                <p id="attendanceCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[0.95fr,1.05fr]">
            <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('Resumo da matrícula') }}</h2>
                <div id="summaryCards" class="mt-6 grid gap-4 sm:grid-cols-2"></div>
            </div>

            <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('Presenças do aluno') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Histórico completo das sessões desta turma para este aluno.') }}</p>
                <div id="attendanceTimeline" class="mt-6 grid gap-3"></div>
            </div>
        </section>

        <section id="printSection" class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div class="flex flex-col gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('Relatório individual de presença') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Versão preparada para impressão com os dados de presença deste aluno.') }}</p>
                </div>
                <div id="printSummary" class="grid gap-3 md:grid-cols-4">
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Curso') }}</p>
                        <p id="printCourseTitle" class="mt-2 text-sm font-semibold text-gray-900"></p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Aluno') }}</p>
                        <p id="printStudentName" class="mt-2 text-sm font-semibold text-gray-900"></p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Presenças') }}</p>
                        <p id="printAttendanceCount" class="mt-2 text-sm font-semibold text-gray-900"></p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Carga horária') }}</p>
                        <p id="printWorkloadHours" class="mt-2 text-sm font-semibold text-gray-900"></p>
                    </div>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full table-auto border-separate border-spacing-0" id="studentAttendanceReportTable">
                    <thead id="studentAttendanceReportHead"></thead>
                    <tbody id="studentAttendanceReportBody"></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<style>
@media print {
    body {
        background: #fff !important;
    }

    body * {
        visibility: hidden;
    }

    #printSection,
    #printSection * {
        visibility: visible;
    }

    #printSection {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    #printButton,
    #refreshButton {
        display: none !important;
    }

    #studentAttendanceReportTable {
        font-size: 12px;
    }
}
</style>

<script>
const classId = @json($courseClass->id);
const enrollmentId = @json($courseEnrollment->id);
const studentName = document.getElementById('studentName');
const studentMeta = document.getElementById('studentMeta');
const className = document.getElementById('className');
const courseTitle = document.getElementById('courseTitle');
const progressHours = document.getElementById('progressHours');
const attendanceCount = document.getElementById('attendanceCount');
const summaryCards = document.getElementById('summaryCards');
const attendanceTimeline = document.getElementById('attendanceTimeline');
const refreshButton = document.getElementById('refreshButton');
const printButton = document.getElementById('printButton');
const printCourseTitle = document.getElementById('printCourseTitle');
const printStudentName = document.getElementById('printStudentName');
const printAttendanceCount = document.getElementById('printAttendanceCount');
const printWorkloadHours = document.getElementById('printWorkloadHours');
const studentAttendanceReportHead = document.getElementById('studentAttendanceReportHead');
const studentAttendanceReportBody = document.getElementById('studentAttendanceReportBody');
const classShowUrl = @json(route('course-classes.show', $courseClass));
const attendanceShowBaseUrl = @json(route('course-class-attendances.show', ['courseClass' => $courseClass, 'courseClassAttendance' => '__ATTENDANCE__']));

function formatHours(value) {
    const numeric = Number(value ?? 0);
    return `${numeric % 1 === 0 ? numeric.toFixed(0) : numeric.toFixed(2)}h`;
}

function formatDateInputValue(value) {
    return value ? String(value).slice(0, 10) : '';
}

function getProgressPercent(hours, workload) {
    const workloadValue = Number(workload ?? 0);

    if (!workloadValue) return 0;

    return Math.min(100, Math.round((Number(hours ?? 0) / workloadValue) * 100));
}

async function fetchEnrollmentData() {
    const res = await fetch(`{{ route("api.course-classes.show", ["course_class" => "__ID__"]) }}`.replace('__ID__', classId), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });

    const data = await res.json();
    renderEnrollment(data);
}

function renderEnrollment(data) {
    const enrollment = (data.enrollments || []).find(item => String(item.id) === String(enrollmentId));

    if (!enrollment) {
        window.location.href = classShowUrl;
        return;
    }

    const workload = Number(data.course?.workload_hours ?? 0);
    const progress = Number(enrollment.progress_hours ?? 0);
    const percent = getProgressPercent(progress, workload);
    const attendanceRecordMap = new Map((data.attendances || []).flatMap(attendance =>
        (attendance.records || [])
            .filter(record => String(record.student_id) === String(enrollment.student_id))
            .map(record => [String(attendance.id), record])
    ));
    const attendedSessions = Array.from(attendanceRecordMap.keys()).length;

    studentName.textContent = enrollment.student?.full_name || '';
    studentMeta.textContent = `${enrollment.student?.email || ''} • {{ __('Matrícula') }} #${enrollment.id}`;
    className.textContent = data.name;
    courseTitle.textContent = data.course?.title || '';
    progressHours.textContent = formatHours(progress);
    attendanceCount.textContent = attendedSessions;
    printCourseTitle.textContent = data.course?.title || '-';
    printStudentName.textContent = enrollment.student?.full_name || '-';
    printAttendanceCount.textContent = `${attendedSessions} {{ __('de') }} ${(data.attendances || []).length}`;
    printWorkloadHours.textContent = `${formatHours(progress)} / ${formatHours(workload)}`;

    summaryCards.innerHTML = `
        <article class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Progresso') }}</p>
            <p class="mt-3 text-2xl font-semibold text-gray-900">${formatHours(progress)} / ${formatHours(workload)}</p>
            <p class="mt-1 text-sm text-gray-500">${percent}%</p>
        </article>
        <article class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Status') }}</p>
            <p class="mt-3 text-2xl font-semibold text-gray-900">${enrollment.completed ? @json(__('Concluído')) : @json(__('Em andamento'))}</p>
            <p class="mt-1 text-sm text-gray-500">{{ __('Nota') }}: ${enrollment.grade ?? '-'}</p>
        </article>
        <article class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Presenças') }}</p>
            <p class="mt-3 text-2xl font-semibold text-gray-900">${attendedSessions}</p>
            <p class="mt-1 text-sm text-gray-500">{{ __('de') }} ${(data.attendances || []).length} {{ __('sessões') }}</p>
        </article>
        <article class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Contato') }}</p>
            <p class="mt-3 break-all text-sm font-medium text-gray-900">${enrollment.student?.email || '-'}</p>
        </article>
    `;

    studentAttendanceReportHead.innerHTML = `
        <tr>
            <th class="bg-white p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Sessão') }}</th>
            <th class="bg-white p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Data') }}</th>
            <th class="bg-white p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Horas') }}</th>
            <th class="bg-white p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Status') }}</th>
        </tr>
    `;

    if (!(data.attendances || []).length) {
        attendanceTimeline.innerHTML = `
            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-500">
                {{ __('Nenhuma sessão de presença foi registrada ainda.') }}
            </div>
        `;
        studentAttendanceReportBody.innerHTML = `
            <tr>
                <td colspan="4" class="p-8 text-center text-gray-500 ring-1 ring-gray-200">
                    {{ __('Nenhuma sessão de presença foi registrada ainda.') }}
                </td>
            </tr>
        `;
        return;
    }

    const attendanceRows = (data.attendances || []).map(attendance => {
        const present = attendanceRecordMap.has(String(attendance.id));

        return `
            <article class="rounded-2xl border p-4 ${present ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 bg-white'}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-base font-semibold text-gray-900">${attendance.name}</p>
                        <p class="mt-1 text-sm text-gray-500">${formatDateInputValue(attendance.attendance_date)} • ${formatHours(attendance.duration_hours)}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold ${present ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700'}">
                        ${present ? @json(__('Presente')) : @json(__('Ausente'))}
                    </span>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="${attendanceShowBaseUrl.replace('__ATTENDANCE__', attendance.id)}" class="inline-flex items-center rounded-xl bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300">
                        {{ __('Abrir sessão') }}
                    </a>
                </div>
            </article>
        `;
    });

    attendanceTimeline.innerHTML = attendanceRows.join('');
    studentAttendanceReportBody.innerHTML = (data.attendances || []).map(attendance => {
        const present = attendanceRecordMap.has(String(attendance.id));

        return `
            <tr>
                <td class="p-3 ring-1 ring-gray-200">
                    <a href="${attendanceShowBaseUrl.replace('__ATTENDANCE__', attendance.id)}" class="font-medium text-gray-900 hover:text-orange-700">${attendance.name}</a>
                </td>
                <td class="p-3 ring-1 ring-gray-200">${formatDateInputValue(attendance.attendance_date)}</td>
                <td class="p-3 ring-1 ring-gray-200">${formatHours(attendance.duration_hours)}</td>
                <td class="p-3 ring-1 ring-gray-200">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${present ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'}">
                        ${present ? @json(__('Presente')) : @json(__('Ausente'))}
                    </span>
                </td>
            </tr>
        `;
    }).join('');
}

refreshButton.addEventListener('click', fetchEnrollmentData);
printButton.addEventListener('click', () => window.print());

fetchEnrollmentData();
</script>
@endsection
