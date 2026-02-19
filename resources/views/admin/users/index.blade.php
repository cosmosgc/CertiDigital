@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 ">
    <div class="bg-white dark:bg-gray-800  overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h2 class="text-xl font-semibold">{{ __('User management') }}</h2>

        <div class="mt-4">
            <table class="w-full table-auto" id="usersTable">
                <thead>
                    <tr class="text-left">
                        <th class="p-2">{{ __('ID') }}</th>
                        <th class="p-2">{{ __('Name') }}</th>
                        <th class="p-2">{{ __('Email') }}</th>
                        <th class="p-2">{{ __('Admin') }}</th>
                        <th class="p-2">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
// authenticated user id
const currentUserId = @json(auth()->id());
const usersTableBody = document.querySelector('#usersTable tbody');
let usersDataTable = null;

async function fetchUsers() {
    const res = await fetch('{{ route("api.admin.users") }}', {
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-User-Id': currentUserId
        }
    });
    const data = await res.json();
    renderUsers(data.data || data);
}

function renderUsers(list) {
    usersTableBody.innerHTML = '';
    (list || []).forEach(u => {
        const tr = document.createElement('tr');

        const isAdmin = u.roles && u.roles.some(r => r.name === 'admin');
        const isSelf = (String(u.id) === String(currentUserId));

        // if this row is the current user, disable the revoke button to
        // prevent self-revocation from the UI (server also enforces this)
        let buttonAttrs = `data-id="${u.id}"`;
        let buttonText = isAdmin ? '{{ __('Revoke admin') }}' : '{{ __('Make admin') }}';
        if (isSelf && isAdmin) {
            buttonAttrs += ' disabled';
            buttonText = '{{ __('Cannot revoke self') }}';
        }

        tr.innerHTML = `
            <td class="p-2">${u.id}</td>
            <td class="p-2">${u.name}</td>
            <td class="p-2">${u.email}</td>
            <td class="p-2">${isAdmin ? '{{ __('Yes') }}' : '{{ __('No') }}'}</td>
            <td class="p-2">
                <button class="toggleBtn px-2 py-1 bg-indigo-600 text-white rounded" ${buttonAttrs}>${buttonText}</button>
            </td>
        `;

        usersTableBody.appendChild(tr);
    });

    if (usersDataTable) {
        usersDataTable.destroy();
    }
    usersDataTable = new DataTable('#usersTable');
}

usersTableBody.addEventListener('click', async (e) => {
    if (e.target.classList.contains('toggleBtn')) {
        const id = e.target.dataset.id;
        const res = await fetch('{{ route("api.admin.users.toggle-admin", ":id") }}'.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-User-Id': currentUserId
            },
            body: JSON.stringify({ user_id: currentUserId }),
            credentials: 'same-origin'
        });
        if (res.ok) {
            fetchUsers();
        } else {
            alert('{{ __('Failed to update user') }}');
        }
    }
});

fetchUsers();
</script>
@endsection
