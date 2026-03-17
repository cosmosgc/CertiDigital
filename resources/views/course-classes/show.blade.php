@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="space-y-6">
        <section class="overflow-hidden rounded-[28px] bg-gradient-to-r from-emerald-700 via-teal-700 to-cyan-700 px-6 py-8 text-white shadow-xl">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <a href="{{ route('course-classes.index') }}" class="text-sm text-emerald-100/90 hover:text-white">{{ __('Voltar para turmas') }}</a>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.25em] text-emerald-100">{{ __('Visão da turma') }}</p>
                    <h1 id="className" class="mt-2 text-3xl font-semibold"></h1>
                    <p id="classDescription" class="mt-3 max-w-2xl text-sm text-emerald-50/90"></p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('course-classes.attendance-report', $courseClass) }}" class="inline-flex items-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-emerald-800 shadow">{{ __('Relatório de presença') }}</a>
                    <a href="{{ route('course-classes.manage', $courseClass) }}" class="inline-flex items-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-emerald-800 shadow">{{ __('Gerenciar turma') }}</a>
                    <button id="refreshButton" type="button" class="inline-flex items-center rounded-xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur">{{ __('Atualizar dados') }}</button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Curso') }}</p>
                <p id="courseTitle" class="mt-3 text-lg font-semibold text-gray-900"></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Instrutor') }}</p>
                <p id="instructorName" class="mt-3 text-lg font-semibold text-gray-900"></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Alunos inscritos') }}</p>
                <p id="studentCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Concluíram') }}</p>
                <p id="completedCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 md:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Carga horária do curso') }}</p>
                <p id="workloadHours" class="mt-3 text-3xl font-semibold text-gray-900">0h</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 md:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Sessões registradas') }}</p>
                <p id="attendanceCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
        </section>

        <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('Alunos da turma') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Cartões rápidos com progresso em horas e percentual calculado pela carga horária do curso.') }}</p>
                </div>
            </div>

            <div id="studentGrid" class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>
        </section>

        <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('Sessões de presença') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Resumo das sessões criadas para esta turma, com criação rápida e atalhos para abrir cada sessão.') }}</p>
                </div>
                <form id="attendanceCreateForm" class="grid gap-3 rounded-2xl bg-cyan-50 p-4 ring-1 ring-cyan-100 md:grid-cols-[minmax(180px,1.4fr),150px,110px,auto]">
                    <input type="text" name="name" class="rounded-xl border-gray-300 shadow-sm" placeholder="{{ __('Nome opcional') }}">
                    <input type="date" name="attendance_date" class="rounded-xl border-gray-300 shadow-sm" required>
                    <input type="number" name="duration_hours" min="0.25" step="0.25" value="1" class="rounded-xl border-gray-300 shadow-sm">
                    <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Criar sessão') }}</button>
                </form>
            </div>

            <div id="attendanceGrid" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3"></div>
        </section>

        <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ __('Detalhes e acompanhamento') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Lista completa com contato, horas cumpridas, nota e status de conclusão.') }}</p>
            </div>

            <div class="bg-white mt-6 overflow-hidden rounded-2xl border border-gray-200">
                <table class="w-full table-auto" id="studentDetailsTable">
                    <thead class="bg-gray-50 text-left text-sm text-gray-600">
                        <tr>
                            <th class="p-3">{{ __('Aluno') }}</th>
                            <th class="p-3">{{ __('Contato') }}</th>
                            <th class="p-3">{{ __('Progresso') }}</th>
                            <th class="p-3">{{ __('Nota') }}</th>
                            <th class="p-3">{{ __('Status') }}</th>
                            <th class="p-3">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = @json(auth()->id());
const classId = @json($courseClass->id);
const today = @json(now()->toDateString());
const className = document.getElementById('className');
const classDescription = document.getElementById('classDescription');
const courseTitle = document.getElementById('courseTitle');
const instructorName = document.getElementById('instructorName');
const studentCount = document.getElementById('studentCount');
const completedCount = document.getElementById('completedCount');
const workloadHours = document.getElementById('workloadHours');
const attendanceCount = document.getElementById('attendanceCount');
const studentGrid = document.getElementById('studentGrid');
const attendanceGrid = document.getElementById('attendanceGrid');
const attendanceCreateForm = document.getElementById('attendanceCreateForm');
const detailsTableBody = document.querySelector('#studentDetailsTable tbody');
const refreshButton = document.getElementById('refreshButton');
const emitBaseUrl = @json(route('certificates.emit'));
const attendanceShowBaseUrl = @json(route('course-class-attendances.show', ['courseClass' => $courseClass, 'courseClassAttendance' => '__ATTENDANCE__']));
const enrollmentShowBaseUrl = @json(route('course-class-enrollments.show', ['courseClass' => $courseClass, 'courseEnrollment' => '__ENROLLMENT__']));
const manageClassUrl = @json(route('course-classes.manage', $courseClass));
const defaultIssueDate = @json(now()->toDateString());
let detailsTable = null;

function formatHours(value) {
    const numeric = Number(value ?? 0);
    return `${numeric % 1 === 0 ? numeric.toFixed(0) : numeric.toFixed(2)}h`;
}

function getProgressPercent(hours, workload) {
    const workloadValue = Number(workload ?? 0);

    if (!workloadValue) return 0;

    return Math.min(100, Math.round((Number(hours ?? 0) / workloadValue) * 100));
}

async function fetchClassData() {
    try {
        const res = await fetch(`{{ route("api.course-classes.show", ["course_class" => "__ID__"]) }}`.replace('__ID__', classId), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) {
            throw new Error(`HTTP ${res.status}`);
        }

        const data = await res.json();
        renderClass(data);
    } catch (error) {
        console.error('Failed to fetch class data:', error);
        alert(`@json`(__('Erro ao carregar dados da turma')));
    }
}

function progressTone(progress) {
    if (progress >= 80) return 'bg-emerald-100 text-emerald-800';
    if (progress >= 50) return 'bg-amber-100 text-amber-800';
    return 'bg-rose-100 text-rose-700';
}

function renderClass(data) {
    const enrollments = data.enrollments || [];
    const attendances = data.attendances || [];
    const completed = enrollments.filter(item => item.completed).length;
    const workload = Number(data.course?.workload_hours ?? 0);

    className.textContent = data.name;
    classDescription.textContent = data.description || @json(__('Sem descrição cadastrada para esta turma.'));
    courseTitle.textContent = data.course?.title || '';
    instructorName.textContent = data.instructor?.full_name || @json(__('Não definido'));
    studentCount.textContent = enrollments.length;
    completedCount.textContent = completed;
    workloadHours.textContent = formatHours(workload);
    attendanceCount.textContent = attendances.length;

    studentGrid.innerHTML = '';
    attendanceGrid.innerHTML = '';
    detailsTableBody.innerHTML = '';

    if (!enrollments.length) {
        studentGrid.innerHTML = `
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-500">
                {{ __('Nenhum aluno foi adicionado a esta turma ainda.') }}
            </div>
        `;
    }

    if (!attendances.length) {
        attendanceGrid.innerHTML = `
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-500">
                {{ __('Nenhuma sessão de presença foi registrada ainda.') }}
            </div>
        `;
    }

    enrollments.forEach(enrollment => {
        const progressHours = Number(enrollment.progress_hours ?? 0);
        const progressPercent = getProgressPercent(progressHours, workload);
        const enrollmentStartDate = enrollment.created_at
            ? new Date(enrollment.created_at).toISOString().slice(0, 10)
            : defaultIssueDate;
        const card = document.createElement('article');
        card.className = 'rounded-2xl border border-gray-200 bg-gradient-to-b from-white to-gray-50 p-5 shadow-sm';
        const enrollmentShowUrl = enrollmentShowBaseUrl.replace('__ENROLLMENT__', enrollment.id);
        card.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-lg font-semibold text-gray-900">${enrollment.student?.full_name || ''}</p>
                    <p class="mt-1 text-sm text-gray-500">${enrollment.student?.email || ''}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold ${progressTone(progressPercent)}">${progressPercent}%</span>
            </div>
            <div class="mt-5 h-2 overflow-hidden rounded-full bg-gray-200">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500" style="width: ${progressPercent}%"></div>
            </div>
            <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-white p-3 ring-1 ring-gray-200">
                    <dt class="text-gray-500">{{ __('Horas cumpridas') }}</dt>
                    <dd class="mt-1 font-semibold text-gray-900">${formatHours(progressHours)} / ${formatHours(workload)}</dd>
                </div>
                <div class="rounded-xl bg-white p-3 ring-1 ring-gray-200">
                    <dt class="text-gray-500">{{ __('Status') }}</dt>
                    <dd class="mt-1 font-semibold text-gray-900">${enrollment.completed ? @json(__('Concluído')) : @json(__('Em andamento'))}</dd>
                </div>
            </dl>
            <div class="mt-4">
                <a href="${enrollmentShowUrl}" class="inline-flex items-center rounded-xl bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300">
                    {{ __('Ver aluno') }}
                </a>
            </div>
        `;
        studentGrid.appendChild(card);

        const tr = document.createElement('tr');
        tr.className = 'border-t border-gray-100';
        const emitUrl = new URL(emitBaseUrl, window.location.origin);
        emitUrl.searchParams.set('student_id', enrollment.student_id);
        emitUrl.searchParams.set('course_id', data.course_id);
        if (data.instructor_id) {
            emitUrl.searchParams.set('instructor_id', data.instructor_id);
        }
        emitUrl.searchParams.set('issue_date', defaultIssueDate);
        emitUrl.searchParams.set('start_date', enrollmentStartDate);
        emitUrl.searchParams.set('status', 'valid');

        tr.innerHTML = `
            <td class="p-3">
                <div class="font-medium text-gray-900">${enrollment.student?.full_name || ''}</div>
                <div class="text-xs text-gray-500">#${enrollment.student_id}</div>
            </td>
            <td class="p-3 text-sm text-gray-600">${enrollment.student?.email || ''}</td>
            <td class="p-3">
                <div class="font-medium text-gray-900">${formatHours(progressHours)} / ${formatHours(workload)}</div>
                <div class="text-xs text-gray-500">${progressPercent}%</div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-emerald-500" style="width: ${progressPercent}%"></div>
                </div>
            </td>
            <td class="p-3 text-sm text-gray-600">${enrollment.grade ?? '-'}</td>
            <td class="p-3">
                <span class="rounded-full px-3 py-1 text-xs font-semibold ${enrollment.completed ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">
                    ${enrollment.completed ? @json(__('Concluído')) : @json(__('Em andamento'))}
                </span>
            </td>
            <td class="p-3">
                <a href="${emitUrl.toString()}" class="inline-flex items-center rounded-xl bg-pink-600 px-3 py-2 text-sm font-medium text-white shadow-sm">
                    {{ __('Emitir certificado') }}
                </a>
                <a href="${enrollmentShowUrl}" class="ml-2 inline-flex items-center rounded-xl bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300">
                    {{ __('Ver aluno') }}
                </a>
            </td>
        `;
        detailsTableBody.appendChild(tr);
    });

    attendances.forEach(attendance => {
        const card = document.createElement('article');
        card.className = 'rounded-2xl border border-gray-200 bg-gradient-to-b from-white to-cyan-50/40 p-5 shadow-sm';
        const attendanceShowUrl = attendanceShowBaseUrl.replace('__ATTENDANCE__', attendance.id);
        card.innerHTML = `
            <p class="text-lg font-semibold text-gray-900">${attendance.name}</p>
            <p class="mt-1 text-sm text-gray-500">${attendance.attendance_date}</p>
            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-white p-3 ring-1 ring-gray-200">
                    <dt class="text-gray-500">{{ __('Horas') }}</dt>
                    <dd class="mt-1 font-semibold text-gray-900">${formatHours(attendance.duration_hours)}</dd>
                </div>
                <div class="rounded-xl bg-white p-3 ring-1 ring-gray-200">
                    <dt class="text-gray-500">{{ __('Presentes') }}</dt>
                    <dd class="mt-1 font-semibold text-gray-900">${attendance.records?.length || 0}</dd>
                </div>
            </dl>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="${attendanceShowUrl}" class="inline-flex items-center rounded-xl bg-cyan-600 px-3 py-2 text-sm font-medium text-white">
                    {{ __('Abrir sessão') }}
                </a>
                <a href="${manageClassUrl}" class="inline-flex items-center rounded-xl bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300">
                    {{ __('Gerenciar na turma') }}
                </a>
            </div>
        `;
        attendanceGrid.appendChild(card);
    });

    if (detailsTable) {
        detailsTable.destroy();
    }

    detailsTable = new DataTable('#studentDetailsTable', {
        pageLength: 10,
        ordering: true,
        searching: true,
        lengthChange: false
    });
}

attendanceCreateForm.addEventListener('submit', async (e) => {
    e.preventDefault();

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
            name: attendanceCreateForm.name.value || null,
            attendance_date: attendanceCreateForm.attendance_date.value,
            duration_hours: attendanceCreateForm.duration_hours.value || 1,
            user_id: currentUserId
        })
    });

    if (res.ok) {
        const data = await res.json();
        attendanceCreateForm.reset();
        attendanceCreateForm.attendance_date.value = today;
        attendanceCreateForm.duration_hours.value = 1;
        window.location.href = attendanceShowBaseUrl.replace('__ATTENDANCE__', data.id);
    } else {
        const error = await res.json().catch(() => null);
        alert(error?.message || @json(__('Erro ao criar sessão')));
    }
});

refreshButton.addEventListener('click', fetchClassData);

attendanceCreateForm.attendance_date.value = today;
fetchClassData();
</script>
@endsection
