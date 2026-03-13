@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden shadow-sm sm:rounded-2xl p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ __('Painel do professor') }}</span>
                <h2 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Turmas') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Acompanhe rapidamente cada turma, visualize os alunos e entre na gestão completa quando precisar.') }}</p>
            </div>
            <button id="showCreate" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 font-medium text-white shadow-sm transition hover:bg-emerald-700">{{ __('Nova turma') }}</button>
        </div>

        <div class="bg-white bg-white mt-6 overflow-hidden rounded-2xl border border-gray-200">
            <table class="w-full table-auto" id="courseClassesTable">
                <thead>
                    <tr class="text-left bg-gray-50 text-sm text-gray-600">
                        <th class="p-2">{{ __('ID') }}</th>
                        <th class="p-2">{{ __('Turma') }}</th>
                        <th class="p-2">{{ __('Curso') }}</th>
                        <th class="p-2">{{ __('Alunos') }}</th>
                        <th class="p-2">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="formContainer" class="mt-4 hidden">
            <div class="rounded-2xl border border-dashed border-emerald-300 bg-emerald-50/60 p-5">
            <h3 id="formTitle" class="font-semibold text-lg text-gray-900"></h3>
            <form id="courseClassForm" class="mt-4 space-y-4">
                <input type="hidden" name="id" />
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('Curso') }}</label>
                    <select name="course_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('Nome da turma') }}</label>
                    <input name="name" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" placeholder="English Class A" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('Descrição') }}</label>
                    <textarea name="description" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" placeholder="{{ __('Observações da turma') }}"></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-white">{{ __('Salvar') }}</button>
                    <button type="button" id="cancelBtn" class="rounded-xl bg-white px-4 py-2 text-gray-700 ring-1 ring-gray-300">{{ __('Cancelar') }}</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = @json(auth()->id());
const tableBody = document.querySelector('#courseClassesTable tbody');
const formContainer = document.getElementById('formContainer');
const courseClassForm = document.getElementById('courseClassForm');
const formTitle = document.getElementById('formTitle');
const showRouteTemplate = @json(route('course-classes.show', ['courseClass' => '__ID__']));
const manageRouteTemplate = @json(route('course-classes.manage', ['courseClass' => '__ID__']));
let courseClassesDataTable = null;
let courses = [];

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

function populateCourseOptions(selectedId = '') {
    courseClassForm.course_id.innerHTML = `
        <option value="">{{ __('Selecione um curso') }}</option>
        ${courses.map(course => `
            <option value="${course.id}" ${String(course.id) === String(selectedId) ? 'selected' : ''}>${course.title}</option>
        `).join('')}
    `;
}

async function loadDependencies() {
    courses = await fetchAllPages('{{ route("api.courses.index") }}');
    populateCourseOptions();
}

async function fetchCourseClasses() {
    const classes = await fetchAllPages('{{ route("api.course-classes.index") }}');
    renderCourseClasses(classes);
}

function renderCourseClasses(list) {
    tableBody.innerHTML = '';

    (list || []).forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2">${item.id}</td>
            <td class="p-2">${item.name}</td>
            <td class="p-2">${item.course?.title || ''}</td>
            <td class="p-2">${item.students?.length || 0}</td>
            <td class="p-2 flex flex-wrap gap-2">
                <a href="${showRouteTemplate.replace('__ID__', item.id)}" class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white">{{ __('Ver turma') }}</a>
                <a href="${manageRouteTemplate.replace('__ID__', item.id)}" class="rounded-lg bg-sky-600 px-3 py-2 text-sm font-medium text-white">{{ __('Gerenciar') }}</a>
                <button class="editBtn rounded-lg bg-amber-300 px-3 py-2 text-sm font-medium text-amber-950" data-id="${item.id}">{{ __('Editar') }}</button>
                <button class="deleteBtn rounded-lg bg-rose-600 px-3 py-2 text-sm font-medium text-white" data-id="${item.id}">{{ __('Excluir') }}</button>
            </td>
        `;
        tableBody.appendChild(tr);
    });

    if (courseClassesDataTable) {
        courseClassesDataTable.destroy();
    }

    courseClassesDataTable = new DataTable('#courseClassesTable');
}

document.getElementById('showCreate').addEventListener('click', () => {
    courseClassForm.reset();
    courseClassForm.id.value = '';
    populateCourseOptions();
    formTitle.textContent = @json(__('Criar turma'));
    formContainer.classList.remove('hidden');
});

document.getElementById('cancelBtn').addEventListener('click', () => {
    formContainer.classList.add('hidden');
});

tableBody.addEventListener('click', async (e) => {
    if (e.target.classList.contains('editBtn')) {
        const id = e.target.dataset.id;
        const res = await fetch(`{{ route("api.course-classes.show", ["course_class" => "__ID__"]) }}`.replace('__ID__', id), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        const item = await res.json();
        courseClassForm.id.value = item.id;
        courseClassForm.name.value = item.name;
        courseClassForm.description.value = item.description || '';
        populateCourseOptions(item.course_id);
        formTitle.textContent = @json(__('Editar turma'));
        formContainer.classList.remove('hidden');
    }

    if (e.target.classList.contains('deleteBtn')) {
        if (!confirm(@json(__('Excluir turma?')))) return;
        const id = e.target.dataset.id;
        const res = await fetch(`{{ route("api.course-classes.destroy", ["course_class" => "__ID__"]) }}`.replace('__ID__', id), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-User-Id': currentUserId,
                'Accept': 'application/json'
            }
        });

        if (res.ok) {
            fetchCourseClasses();
        } else {
            alert(@json(__('Erro ao excluir turma')));
        }
    }
});

courseClassForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = courseClassForm.id.value;
    const url = id
        ? `{{ route("api.course-classes.update", ["course_class" => "__ID__"]) }}`.replace('__ID__', id)
        : '{{ route("api.course-classes.store") }}';

    const res = await fetch(url, {
        method: id ? 'PUT' : 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            course_id: courseClassForm.course_id.value,
            name: courseClassForm.name.value,
            description: courseClassForm.description.value || null,
            user_id: currentUserId
        })
    });

    if (res.ok) {
        formContainer.classList.add('hidden');
        fetchCourseClasses();
    } else {
        const error = await res.json().catch(() => null);
        alert(error?.message || @json(__('Erro ao salvar turma')));
    }
});

async function init() {
    await loadDependencies();
    await fetchCourseClasses();
}

init();
</script>
@endsection
