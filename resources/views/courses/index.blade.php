@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 ">
    <div class="bg-white dark:bg-gray-800  overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">{{ __('Cursos') }}</h2>
            <button id="showCreate" class="px-4 py-2 bg-yellow-500 text-white rounded">{{ __('Novo curso') }}</button>
        </div>

        <div class="mt-4">
            <table class="w-full table-auto" id="coursesTable">
                <thead>
                    <tr class="text-left">
                        <th class="p-2">{{ __('ID') }}</th>
                        <th class="p-2">{{ __('Título') }}</th>
                        <th class="p-2">{{ __('Carga horária') }}</th>
                        <th class="p-2">{{ __('Modalidade') }}</th>
                        <th class="p-2">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="formContainer" class="mt-4 hidden">
            <h3 id="formTitle" class="font-semibold"></h3>
            <form id="courseForm" class="space-y-4">
                <input type="hidden" name="id" />
                <div>
                    <label class="block text-sm font-medium">{{ __('Título') }}</label>
                    <input name="title" placeholder="Nome do curso" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium">{{ __('Descrição') }}</label>
                    <textarea name="description" placeholder="Descrição do curso" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">{{ __('Carga horária (horas)') }}</label>
                        <input name="workload_hours" type="number" placeholder="20" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">{{ __('Modalidade') }}</label>
                        <select name="modality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="online">{{ __('Online') }}</option>
                            <option value="in_person">{{ __('Presencial') }}</option>
                            <option value="hybrid">{{ __('Híbrido') }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded">{{ __('Salvar') }}</button>
                    <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-300 rounded">{{ __('Cancelar') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = @json(auth()->id());
const coursesTableBody = document.querySelector('#coursesTable tbody');
const formContainer = document.getElementById('formContainer');
const courseForm = document.getElementById('courseForm');
const formTitle = document.getElementById('formTitle');
let coursesDataTable = null;

async function fetchCourses() {
    const res = await fetch('{{ route("api.courses.index") }}');
    const data = await res.json();
    renderCourses(data.data || data);
}

function renderCourses(list) {
    coursesTableBody.innerHTML = '';
    (list || []).forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2">${s.id}</td>
            <td class="p-2">${s.title}</td>
            <td class="p-2">${s.workload_hours || ''}</td>
            <td class="p-2">${s.modality || ''}</td>
            <td class="p-2">
                <button class="editBtn px-2 py-1 bg-yellow-400 rounded" data-id="${s.id}">{{ __('Editar') }}</button>
                <button class="deleteBtn px-2 py-1 bg-red-500 text-white rounded" data-id="${s.id}">{{ __('Excluir') }}</button>
            </td>
        `;
        coursesTableBody.appendChild(tr);
    });

    if (coursesDataTable) {
        coursesDataTable.destroy();
    }
    coursesDataTable = new DataTable('#coursesTable');
}

document.getElementById('showCreate').addEventListener('click', () => {
    courseForm.reset();
    courseForm.id.value = '';
    formTitle.textContent = @json(__('Criar curso'));
    formContainer.classList.remove('hidden');
});

document.getElementById('cancelBtn').addEventListener('click', () => {
    formContainer.classList.add('hidden');
});

coursesTableBody.addEventListener('click', async (e) => {
    if (e.target.classList.contains('editBtn')) {
        const id = e.target.dataset.id;
        const res = await fetch(`{{ route("api.courses.show", ["course" => "__ID__"]) }}`.replace('__ID__', id));
        const item = await res.json();
        courseForm.title.value = item.title;
        courseForm.description.value = item.description || '';
        courseForm.workload_hours.value = item.workload_hours || '';
        courseForm.modality.value = item.modality || '';
        courseForm.id.value = item.id;
        formTitle.textContent = @json(__('Editar curso'));
        formContainer.classList.remove('hidden');
    }

    if (e.target.classList.contains('deleteBtn')) {
        if (!confirm(@json(__('Excluir curso?')))) return;
        const id = e.target.dataset.id;
        await fetch(`{{ route("api.courses.destroy", ["course" => "__ID__"]) }}`.replace('__ID__', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });
        fetchCourses();
    }
});

courseForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        title: courseForm.title.value,
        description: courseForm.description.value || null,
        workload_hours: courseForm.workload_hours.value || null,
        modality: courseForm.modality.value || null,
        user_id: currentUserId,
    };

    const id = courseForm.id.value;
    const method = id ? 'PUT' : 'POST';
    const url = id ? `{{ route("api.courses.update", ["course" => "__ID__"]) }}`.replace('__ID__', id) : '{{ route("api.courses.store") }}';

    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
    });

    if (res.ok) {
        formContainer.classList.add('hidden');
        fetchCourses();
    } else {
        alert(@json(__('Erro ao salvar curso')));
    }
});

fetchCourses();
</script>
@endsection
