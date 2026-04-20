@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="space-y-6">
        <section class="overflow-hidden rounded-[28px] bg-gradient-to-r from-slate-800 via-cyan-800 to-teal-700 px-6 py-8 text-white shadow-xl">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <a href="{{ route('course-classes.show', $courseClass) }}" class="text-sm text-cyan-100/90 hover:text-white">{{ __('Voltar para a turma') }}</a>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100">{{ __('Relatório de presença') }}</p>
                    <h1 id="reportTitle" class="mt-2 text-3xl font-semibold"></h1>
                    <p id="reportMeta" class="mt-3 max-w-2xl text-sm text-cyan-50/90"></p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('course-classes.manage', $courseClass) }}" class="inline-flex items-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-cyan-800 shadow">{{ __('Gerenciar turma') }}</a>
                    <button id="printButton" type="button" class="inline-flex items-center rounded-xl bg-amber-400 px-4 py-3 text-sm font-semibold text-slate-900 shadow">{{ __('Imprimir relatório') }}</button>
                    <button id="refreshButton" type="button" class="inline-flex items-center rounded-xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur">{{ __('Atualizar dados') }}</button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Alunos') }}</p>
                <p id="studentCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Sessões') }}</p>
                <p id="attendanceCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Carga horária') }}</p>
                <p id="workloadHours" class="mt-3 text-3xl font-semibold text-gray-900">0h</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Presença x faltas') }}</p>
                <p id="attendanceRatio" class="mt-3 text-2xl font-semibold text-gray-900">0% / 0%</p>
                <p id="attendanceRatioMeta" class="mt-2 text-sm text-gray-500">0 {{ __('presenças') }} • 0 {{ __('faltas') }}</p>
            </div>
        </section>

        <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div id="printSection">
            <div class="flex flex-col gap-4 border-b border-gray-200 pb-4 print:border-b print:pb-3">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('Matriz de presença') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Cada linha representa um aluno e cada coluna mostra a presença em uma sessão.') }}</p>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <label for="monthFilter" class="block text-sm font-medium text-gray-700">{{ __('Selecionar mês') }}</label>
                        <input type="month" id="monthFilter" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" value="{{ date('Y-m') }}">
                    </div>
                    <button id="applyMonthFilter" type="button" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-medium text-white">{{ __('Aplicar filtro') }}</button>
                </div>
                <div id="printSummary" class="grid gap-3 md:grid-cols-4">
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Curso') }}</p>
                        <p id="printCourseTitle" class="mt-2 text-sm font-semibold text-gray-900"></p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Alunos') }}</p>
                        <p id="printStudentCount" class="mt-2 text-sm font-semibold text-gray-900"></p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Sessões') }}</p>
                        <p id="printAttendanceCount" class="mt-2 text-sm font-semibold text-gray-900"></p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Carga horária') }}</p>
                        <p id="printWorkloadHours" class="mt-2 text-sm font-semibold text-gray-900"></p>
                    </div>
                </div>
            </div>
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full table-auto border-separate border-spacing-0" id="attendanceReportTable">
                    <thead id="attendanceReportHead"></thead>
                    <tbody id="attendanceReportBody"></tbody>
                </table>
            </div>
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

    #attendanceReportTable {
        font-size: 12px;
    }

    #attendanceReportTable th,
    #attendanceReportTable td {
        page-break-inside: avoid;
    }

    #printSummary {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
</style>

<script>
const classId = @json($courseClass->id);
const reportTitle = document.getElementById('reportTitle');
const reportMeta = document.getElementById('reportMeta');
const studentCount = document.getElementById('studentCount');
const attendanceCount = document.getElementById('attendanceCount');
const workloadHours = document.getElementById('workloadHours');
const attendanceRatio = document.getElementById('attendanceRatio');
const attendanceRatioMeta = document.getElementById('attendanceRatioMeta');
const printCourseTitle = document.getElementById('printCourseTitle');
const printStudentCount = document.getElementById('printStudentCount');
const printAttendanceCount = document.getElementById('printAttendanceCount');
const printWorkloadHours = document.getElementById('printWorkloadHours');
const attendanceReportHead = document.getElementById('attendanceReportHead');
const attendanceReportBody = document.getElementById('attendanceReportBody');
const refreshButton = document.getElementById('refreshButton');
const printButton = document.getElementById('printButton');
const monthFilter = document.getElementById('monthFilter');
const applyMonthFilter = document.getElementById('applyMonthFilter');
const attendanceShowBaseUrl = @json(route('course-class-attendances.show', ['courseClass' => $courseClass, 'courseClassAttendance' => '__ATTENDANCE__']));
const enrollmentShowBaseUrl = @json(route('course-class-enrollments.show', ['courseClass' => $courseClass, 'courseEnrollment' => '__ENROLLMENT__']));

function formatHours(value) {
    const numeric = Number(value ?? 0);
    return `${numeric % 1 === 0 ? numeric.toFixed(0) : numeric.toFixed(2)}h`;
}

function formatDateInputValue(value) {
    return value ? String(value).slice(0, 10) : '';
}

function getAttendanceRatio(presentCount, totalCount) {
    if (!totalCount) {
        return { presentPercent: 0, absentPercent: 0 };
    }

    const presentPercent = Math.round((presentCount / totalCount) * 100);

    return {
        presentPercent,
        absentPercent: 100 - presentPercent,
    };
}

function getSelectedMonth() {
    return monthFilter.value || '{{ date('Y-m') }}';
}

function isDateInMonth(dateStr, monthStr) {
    if (!dateStr || !monthStr) return false;
    const date = new Date(dateStr);
    const [year, month] = monthStr.split('-').map(Number);
    return date.getFullYear() === year && date.getMonth() + 1 === month;
}

async function fetchReportData() {
    const res = await fetch(`{{ route("api.course-classes.show", ["course_class" => "__ID__"]) }}`.replace('__ID__', classId), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });

    const data = await res.json();
    renderReport(data);
}

function renderReport(data) {
    const selectedMonth = getSelectedMonth();
    const allAttendances = data.attendances || [];
    const allEnrollments = data.enrollments || [];
    const attendances = allAttendances.filter(attendance => isDateInMonth(attendance.attendance_date, selectedMonth));
    const studentIdsWithAttendance = new Set(attendances.flatMap(attendance => (attendance.records || []).map(record => String(record.student_id))));
    const enrollments = allEnrollments.filter(enrollment => {
        const enrollmentStart = enrollment.start_date || enrollment.created_at;
        return studentIdsWithAttendance.has(String(enrollment.student_id)) && isDateInMonth(enrollmentStart, selectedMonth);
    });
    const totalSlots = enrollments.length * attendances.length;
    const totalPresent = attendances.reduce((sum, attendance) => sum + (attendance.records?.length || 0), 0);
    const totalAbsent = Math.max(totalSlots - totalPresent, 0);
    const { presentPercent, absentPercent } = getAttendanceRatio(totalPresent, totalSlots);
    const attendanceRecordSets = new Map(attendances.map(attendance => [
        String(attendance.id),
        new Set((attendance.records || []).map(record => String(record.student_id))),
    ]));

    reportTitle.textContent = data.name;
    reportMeta.textContent = `${data.course?.title || ''} • ${data.instructor?.full_name || @json(__('Instrutor não definido'))}`;
    studentCount.textContent = enrollments.length;
    attendanceCount.textContent = attendances.length;
    workloadHours.textContent = formatHours(data.course?.workload_hours || 0);
    attendanceRatio.textContent = `${presentPercent}% / ${absentPercent}%`;
    attendanceRatioMeta.textContent = `${totalPresent} {{ __('presenças') }} • ${totalAbsent} {{ __('faltas') }}`;
    printCourseTitle.textContent = data.course?.title || '-';
    printStudentCount.textContent = String(enrollments.length);
    printAttendanceCount.textContent = String(attendances.length);
    printWorkloadHours.textContent = formatHours(data.course?.workload_hours || 0);

    attendanceReportHead.innerHTML = `
        <tr>
            <th class="sticky left-0 z-10 min-w-[220px] bg-white p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Aluno') }}</th>
            ${attendances.map(attendance => {
                const sessionPresentCount = attendance.records?.length || 0;
                const sessionRatio = getAttendanceRatio(sessionPresentCount, enrollments.length);

                return `
                <th class="min-w-[170px] bg-white p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200 align-top">
                    <a href="${attendanceShowBaseUrl.replace('__ATTENDANCE__', attendance.id)}" class="hover:text-cyan-700">
                        ${attendance.name}
                    </a>
                    <div class="mt-1 text-xs font-normal text-gray-500">${formatDateInputValue(attendance.attendance_date)} • ${formatHours(attendance.duration_hours)}</div>
                    <div class="mt-2 text-xs font-semibold text-emerald-700">${sessionRatio.presentPercent}% {{ __('presença') }} • ${sessionPresentCount}/${enrollments.length} {{ __('presentes') }}</div>
                </th>
            `;
            }).join('')}
            <th class="bg-white p-3 text-left text-sm font-semibold text-gray-700 ring-1 ring-gray-200">{{ __('Total') }}</th>
        </tr>
    `;

    if (!enrollments.length) {
        attendanceReportBody.innerHTML = `
            <tr>
                <td colspan="${attendances.length + 2}" class="p-8 text-center text-gray-500 ring-1 ring-gray-200">
                    {{ __('Nenhum aluno está matriculado nesta turma.') }}
                </td>
            </tr>
        `;
        return;
    }

    attendanceReportBody.innerHTML = enrollments.map(enrollment => {
        const presentCountByStudent = attendances.reduce((count, attendance) => {
            const presentStudentIds = attendanceRecordSets.get(String(attendance.id)) || new Set();
            return count + (presentStudentIds.has(String(enrollment.student_id)) ? 1 : 0);
        }, 0);
        const absentCountByStudent = Math.max(attendances.length - presentCountByStudent, 0);
        const studentAttendanceRatio = getAttendanceRatio(presentCountByStudent, attendances.length);

        return `
            <tr>
                <td class="sticky left-0 z-10 bg-white p-3 align-top ring-1 ring-gray-200">
                    <a href="${enrollmentShowBaseUrl.replace('__ENROLLMENT__', enrollment.id)}" class="font-medium text-gray-900 hover:text-cyan-700">
                        ${enrollment.student?.full_name || ''}
                    </a>
                </td>
                ${attendances.map(attendance => {
                    const presentStudentIds = attendanceRecordSets.get(String(attendance.id)) || new Set();
                    const present = presentStudentIds.has(String(enrollment.student_id));

                    return `
                        <td class="p-3 text-center ring-1 ring-gray-200">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${present ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'}">
                                ${present ? @json(__('Presente')) : @json(__('Ausente'))}
                            </span>
                        </td>
                    `;
                }).join('')}
                <td class="p-3 align-top ring-1 ring-gray-200">
                    <div class="text-sm font-medium text-gray-900">${presentCountByStudent}/${attendances.length} {{ __('sessões presentes') }}</div>
                    <div class="mt-1 text-xs text-gray-500">${studentAttendanceRatio.presentPercent}% {{ __('presente') }} • ${studentAttendanceRatio.absentPercent}% {{ __('ausente') }}</div>
                    <div class="mt-1 text-xs text-gray-500">${absentCountByStudent} {{ __('faltas') }}</div>
                </td>
            </tr>
        `;
    }).join('');
}

refreshButton.addEventListener('click', fetchReportData);
applyMonthFilter.addEventListener('click', fetchReportData);
printButton.addEventListener('click', () => window.print());

fetchReportData();
</script>
@endsection
