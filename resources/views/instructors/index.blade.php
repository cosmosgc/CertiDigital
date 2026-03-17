@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden shadow-sm sm:rounded-2xl p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Instrutores') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Mantenha os instrutores acessíveis com uma visão limpa para edição e consulta.') }}</p>
            </div>
            <button id="showCreate" class="rounded-xl bg-green-600 px-4 py-3 text-white shadow-sm">{{ __('Novo instrutor') }}</button>
        </div>

        <div class="bg-white mt-6 overflow-hidden rounded-2xl border border-gray-200">
            <table class="w-full table-auto" id="instructorsTable">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-600">
                        <th class="p-2">{{ __('ID') }}</th>
                        <th class="p-2">{{ __('Nome') }}</th>
                        <th class="p-2">{{ __('E-mail') }}</th>
                        <th class="p-2">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="formContainer" class="mt-4 hidden">
            <div class="rounded-2xl border border-dashed border-emerald-300 bg-emerald-50/50 p-5">
            <h3 id="formTitle" class="text-lg font-semibold text-gray-900"></h3>
            <form id="instructorForm" class="mt-4 space-y-4">
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
                    <label class="block text-sm font-medium text-gray-700">CPF / CNPJ</label>
                    <input name="cpf_cnpj" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-xl bg-green-600 px-4 py-2 text-white">{{ __('Salvar') }}</button>
                    <button type="button" id="cancelBtn" class="rounded-xl bg-white px-4 py-2 text-gray-700 ring-1 ring-gray-300">{{ __('Cancelar') }}</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<script>
const sanctumCsrfUrl = "{{ url('sanctum/csrf-cookie') }}";

async function initSanctum() {
    await fetch(sanctumCsrfUrl, {
        credentials: 'same-origin'
    });
}
</script>


<script>
    
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = @json(auth()->id());
const instructorsTableBody = document.querySelector('#instructorsTable tbody');
const formContainer = document.getElementById('formContainer');
const instructorForm = document.getElementById('instructorForm');
const formTitle = document.getElementById('formTitle');
let instructorsDataTable = null;

async function fetchInstructors() {
    const res = await fetch('{{ route("api.instructors.index") }}');
    const data = await res.json();
    renderInstructors(data.data || data);
}

function renderInstructors(list) {
    instructorsTableBody.innerHTML = '';
    (list || []).forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2">${s.id}</td>
            <td class="p-2">${s.full_name}</td>
            <td class="p-2">${s.email}</td>
            <td class="p-2">
                <button class="editBtn px-2 py-1 bg-yellow-400 rounded" data-id="${s.id}">{{ __('Editar') }}</button>
                <button class="deleteBtn px-2 py-1 bg-red-500 text-white rounded" data-id="${s.id}">{{ __('Excluir') }}</button>
            </td>
        `;
        instructorsTableBody.appendChild(tr);
    });

    if (instructorsDataTable) {
        instructorsDataTable.destroy();
    }
    instructorsDataTable = new DataTable('#instructorsTable');
}

document.getElementById('showCreate').addEventListener('click', () => {
    instructorForm.reset();
    instructorForm.id.value = '';
    formTitle.textContent = @json(__('Criar instrutor'));
    formContainer.classList.remove('hidden');
});

document.getElementById('cancelBtn').addEventListener('click', () => {
    formContainer.classList.add('hidden');
});

instructorsTableBody.addEventListener('click', async (e) => {
    if (e.target.classList.contains('editBtn')) {
        const id = e.target.dataset.id;
        const res = await fetch(`{{ route("api.instructors.show", ["instructor" => "__ID__"]) }}`.replace('__ID__', id));
        const item = await res.json();
        instructorForm.full_name.value = item.full_name;
        instructorForm.email.value = item.email;
        instructorForm.cpf_cnpj.value = item.cpf_cnpj || '';
        instructorForm.id.value = item.id;
        formTitle.textContent = @json(__('Editar instrutor'));
        formContainer.classList.remove('hidden');
    }

    if (e.target.classList.contains('deleteBtn')) {
        if (!confirm(@json(__('Excluir instrutor?')))) return;
        const id = e.target.dataset.id;
        await fetch(`{{ route("api.instructors.destroy", ["instructor" => "__ID__"]) }}`.replace('__ID__', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });
        fetchInstructors();
    }
});

instructorForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        full_name: instructorForm.full_name.value,
        email: instructorForm.email.value,
        cpf_cnpj: instructorForm.cpf_cnpj.value || null,
        user_id: currentUserId,
    };

    const id = instructorForm.id.value;
    const method = id ? 'PUT' : 'POST';
    const url = id ? `{{ route("api.instructors.update", ["instructor" => "__ID__"]) }}`.replace('__ID__', id) : '{{ route("api.instructors.store") }}';

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
        fetchInstructors();
    } else {
        alert(@json(__('Erro ao salvar instrutor')));
    }
});

(async () => {
    await initSanctum();
    fetchInstructors();
})();

</script>
@endsection
