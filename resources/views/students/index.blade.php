@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden shadow-sm sm:rounded-2xl p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Alunos') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Cadastre, consulte e mantenha a base de alunos sempre organizada.') }}</p>
            </div>
            <button id="showCreate" class="rounded-xl bg-indigo-600 px-4 py-3 text-white shadow-sm">{{ __('Novo aluno') }}</button>
        </div>

        <div class="bg-white mt-6 overflow-hidden rounded-2xl border border-gray-200">
            <table class="w-full table-auto" id="studentsTable">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-600">
                        <th class="p-2">{{ __('ID') }}</th>
                        <th class="p-2">{{ __('Nome') }}</th>
                        <th class="p-2">{{ __('E-mail') }}</th>
                        <th class="p-2">{{ __('Documento') }}</th>
                        <th class="p-2">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Create/Edit Form -->
        <div id="formContainer" class="mt-4 hidden">
            <div class="rounded-2xl border border-dashed border-indigo-300 bg-indigo-50/50 p-5">
            <h3 id="formTitle" class="text-lg font-semibold text-gray-900"></h3>
            <form id="studentForm" class="mt-4 space-y-4">
                <input type="hidden" name="id" />
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('Nome completo') }}</label>
                    <input name="full_name" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('E-mail') }}</label>
                    <input name="email" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('Documento') }}</label>
                    <input name="document_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-white">{{ __('Salvar') }}</button>
                    <button type="button" id="cancelBtn" class="rounded-xl bg-white px-4 py-2 text-gray-700 ring-1 ring-gray-300">{{ __('Cancelar') }}</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
// current authenticated user id (used when creating/updating records)
const currentUserId = @json(auth()->id());
const studentsTableBody = document.querySelector('#studentsTable tbody');
const formContainer = document.getElementById('formContainer');
const studentForm = document.getElementById('studentForm');
const formTitle = document.getElementById('formTitle');
let studentsDataTable = null;
 
async function fetchStudents() {
    // this endpoint is protected by sanctum; we must send the auth cookie
    const res = await fetch('{{ route("api.students.index") }}', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });
    const data = await res.json();
    renderStudents(data.data || data);
}

function renderStudents(list) {
    studentsTableBody.innerHTML = '';
    (list || []).forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2">${s.id}</td>
            <td class="p-2">${s.full_name}</td>
            <td class="p-2">${s.email}</td>
            <td class="p-2">${s.document_id || ''}</td>
            <td class="p-2">
                <button class="editBtn px-2 py-1 bg-yellow-400 rounded" data-id="${s.id}">{{ __('Editar') }}</button>
                <button class="deleteBtn px-2 py-1 bg-red-500 text-white rounded" data-id="${s.id}">{{ __('Excluir') }}</button>
            </td>
        `;
        studentsTableBody.appendChild(tr);
    });

    if (studentsDataTable) {
        studentsDataTable.destroy();
    }
    studentsDataTable = new DataTable('#studentsTable');
}

document.getElementById('showCreate').addEventListener('click', () => {
    studentForm.reset();
    studentForm.id.value = '';
    formTitle.textContent = @json(__('Criar aluno'));
    formContainer.classList.remove('hidden');
});

document.getElementById('cancelBtn').addEventListener('click', () => {
    formContainer.classList.add('hidden');
});

studentsTableBody.addEventListener('click', async (e) => {
    if (e.target.classList.contains('editBtn')) {
        const id = e.target.dataset.id;
        const res = await fetch(`{{ route("api.students.show", ["student" => "__ID__"]) }}`.replace('__ID__', id), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        const student = await res.json();
        studentForm.full_name.value = student.full_name;
        studentForm.email.value = student.email;
        studentForm.document_id.value = student.document_id || '';
        studentForm.id.value = student.id;
        formTitle.textContent = @json(__('Editar aluno'));
        formContainer.classList.remove('hidden');
    }

    if (e.target.classList.contains('deleteBtn')) {
        if (!confirm(@json(__('Excluir aluno?')))) return;
        const id = e.target.dataset.id;
        await fetch(`{{ route("api.students.destroy", ["student" => "__ID__"]) }}`.replace('__ID__', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });
        fetchStudents();
    }
});

studentForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        full_name: studentForm.full_name.value,
        email: studentForm.email.value,
        document_id: studentForm.document_id.value || null,
        user_id: currentUserId,
    };

    const id = studentForm.id.value;
    const method = id ? 'PUT' : 'POST';
    const url = id ? `{{ route("api.students.update", ["student" => "__ID__"]) }}`.replace('__ID__', id) : '{{ route("api.students.store") }}';

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
        fetchStudents();
    } else {
        alert(@json(__('Erro ao salvar aluno')));
    }
});

fetchStudents();
</script>
@endsection
