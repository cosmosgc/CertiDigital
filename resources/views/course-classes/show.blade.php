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
                    <a href="{{ route('course-classes.manage', $courseClass) }}" class="inline-flex items-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-emerald-800 shadow">{{ __('Gerenciar turma') }}</a>
                    <button id="refreshButton" type="button" class="inline-flex items-center rounded-xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur">{{ __('Atualizar dados') }}</button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Curso') }}</p>
                <p id="courseTitle" class="mt-3 text-lg font-semibold text-gray-900"></p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Alunos inscritos') }}</p>
                <p id="studentCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Concluíram') }}</p>
                <p id="completedCount" class="mt-3 text-3xl font-semibold text-gray-900">0</p>
            </div>
        </section>

        <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('Alunos da turma') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Cartões rápidos para o professor visualizar quem está na turma e como cada aluno está avançando.') }}</p>
                </div>
            </div>

            <div id="studentGrid" class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>
        </section>

        <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ __('Detalhes e acompanhamento') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Lista completa com contato, nota, progresso e status de conclusão.') }}</p>
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
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
const classId = @json($courseClass->id);
const className = document.getElementById('className');
const classDescription = document.getElementById('classDescription');
const courseTitle = document.getElementById('courseTitle');
const studentCount = document.getElementById('studentCount');
const completedCount = document.getElementById('completedCount');
const studentGrid = document.getElementById('studentGrid');
const detailsTableBody = document.querySelector('#studentDetailsTable tbody');
const refreshButton = document.getElementById('refreshButton');
let detailsTable = null;

async function fetchClassData() {
    const res = await fetch(`{{ route("api.course-classes.show", ["course_class" => "__ID__"]) }}`.replace('__ID__', classId), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });

    const data = await res.json();
    renderClass(data);
}

function progressTone(progress) {
    if (progress >= 80) return 'bg-emerald-100 text-emerald-800';
    if (progress >= 50) return 'bg-amber-100 text-amber-800';
    return 'bg-rose-100 text-rose-700';
}

function renderClass(data) {
    const enrollments = data.enrollments || [];
    const completed = enrollments.filter(item => item.completed).length;

    className.textContent = data.name;
    classDescription.textContent = data.description || @json(__('Sem descrição cadastrada para esta turma.'));
    courseTitle.textContent = data.course?.title || '';
    studentCount.textContent = enrollments.length;
    completedCount.textContent = completed;

    studentGrid.innerHTML = '';
    detailsTableBody.innerHTML = '';

    if (!enrollments.length) {
        studentGrid.innerHTML = `
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-500">
                {{ __('Nenhum aluno foi adicionado a esta turma ainda.') }}
            </div>
        `;
    }

    enrollments.forEach(enrollment => {
        const progress = Number(enrollment.progress_percent ?? 0);
        const card = document.createElement('article');
        card.className = 'rounded-2xl border border-gray-200 bg-gradient-to-b from-white to-gray-50 p-5 shadow-sm';
        card.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-lg font-semibold text-gray-900">${enrollment.student?.full_name || ''}</p>
                    <p class="mt-1 text-sm text-gray-500">${enrollment.student?.email || ''}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold ${progressTone(progress)}">${progress}%</span>
            </div>
            <div class="mt-5 h-2 overflow-hidden rounded-full bg-gray-200">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500" style="width: ${progress}%"></div>
            </div>
            <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-white p-3 ring-1 ring-gray-200">
                    <dt class="text-gray-500">{{ __('Nota') }}</dt>
                    <dd class="mt-1 font-semibold text-gray-900">${enrollment.grade ?? '-'}</dd>
                </div>
                <div class="rounded-xl bg-white p-3 ring-1 ring-gray-200">
                    <dt class="text-gray-500">{{ __('Status') }}</dt>
                    <dd class="mt-1 font-semibold text-gray-900">${enrollment.completed ? @json(__('Concluído')) : @json(__('Em andamento'))}</dd>
                </div>
            </dl>
        `;
        studentGrid.appendChild(card);

        const tr = document.createElement('tr');
        tr.className = 'border-t border-gray-100';
        tr.innerHTML = `
            <td class="p-3">
                <div class="font-medium text-gray-900">${enrollment.student?.full_name || ''}</div>
                <div class="text-xs text-gray-500">#${enrollment.student_id}</div>
            </td>
            <td class="p-3 text-sm text-gray-600">${enrollment.student?.email || ''}</td>
            <td class="p-3">
                <div class="font-medium text-gray-900">${progress}%</div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-emerald-500" style="width: ${progress}%"></div>
                </div>
            </td>
            <td class="p-3 text-sm text-gray-600">${enrollment.grade ?? '-'}</td>
            <td class="p-3">
                <span class="rounded-full px-3 py-1 text-xs font-semibold ${enrollment.completed ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">
                    ${enrollment.completed ? @json(__('Concluído')) : @json(__('Em andamento'))}
                </span>
            </td>
        `;
        detailsTableBody.appendChild(tr);
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

refreshButton.addEventListener('click', fetchClassData);

fetchClassData();
</script>
@endsection
