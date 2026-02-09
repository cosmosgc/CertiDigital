@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 ">
    <div class="bg-white dark:bg-gray-800  overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">{{ __('Certificados') }}</h2>
            <a href="{{ route('certificates.emit') }}" class="px-4 py-2 bg-pink-600 text-white rounded">{{ __('Emitir certificado') }}</a>
        </div>

        <div class="mt-4">
            <table class="w-full table-auto" id="certificatesTable">
                <thead>
                    <tr class="text-left">
                        <th class="p-2">{{ __('ID') }}</th>
                        <th class="p-2">{{ __('Código') }}</th>
                        <th class="p-2">{{ __('Aluno') }}</th>
                        <th class="p-2">{{ __('Curso') }}</th>
                        <th class="p-2">{{ __('Data de emissão') }}</th>
                        <th class="p-2">{{ __('Status') }}</th>
                        <th class="p-2">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="formContainer" class="mt-4 hidden">
            <h3 id="formTitle" class="font-semibold"></h3>
            <form id="certificateForm" class="space-y-4">
                <input type="hidden" name="id" />
                <div>
                    <label class="block text-sm font-medium">{{ __('Código do certificado') }}</label>
                    <input name="certificate_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly />
                </div>

                <div>
                    <label class="block text-sm font-medium">{{ __('Aluno') }}</label>
                    <select name="student_id" id="studentSelect" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></select>
                </div>

                <div>
                    <label class="block text-sm font-medium">{{ __('Curso') }}</label>
                    <select name="course_id" id="courseSelect" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></select>
                </div>

                <div>
                    <label class="block text-sm font-medium">{{ __('Instrutor') }}</label>
                    <select name="instructor_id" id="instructorSelect" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></select>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium">{{ __('Data de emissão') }}</label>
                        <input type="date" name="issue_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">{{ __('Data de início') }}</label>
                        <input type="date" name="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">{{ __('Data de término') }}</label>
                        <input type="date" name="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium">{{ __('Status') }}</label>
                    <input name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">{{ __('Salvar') }}</button>
                    <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-300 rounded">{{ __('Cancelar') }}</button>
                </div>
            </form>
        </div>

        <div id="viewModal" class="mt-4 hidden">
            <h3 class="font-semibold">{{ __('Detalhes do certificado') }}</h3>
            <pre id="detailPre" class="bg-gray-100 p-4 mt-2 rounded"></pre>
        </div>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const certificatesTableBody = document.querySelector('#certificatesTable tbody');
const formContainer = document.getElementById('formContainer');
const certificateForm = document.getElementById('certificateForm');
const formTitle = document.getElementById('formTitle');
const studentSelect = document.getElementById('studentSelect');
const courseSelect = document.getElementById('courseSelect');
const instructorSelect = document.getElementById('instructorSelect');
const viewModal = document.getElementById('viewModal');
const detailPre = document.getElementById('detailPre');

async function fetchLists() {
    const [studentsRes, coursesRes, instructorsRes] = await Promise.all([
        fetch('{{ route("api.students.index") }}'),
        fetch('{{ route("api.courses.index") }}'),
        fetch('{{ route("api.instructors.index") }}')
    ]);

    const students = (await studentsRes.json()).data || (await studentsRes.json());
    const courses = (await coursesRes.json()).data || (await coursesRes.json());
    const instructors = (await instructorsRes.json()).data || (await instructorsRes.json());

    studentSelect.innerHTML = '<option value="">' + @json(__('Escolha um aluno')) + '</option>' + (students || []).map(s => `<option value="${s.id}">${s.full_name}</option>`).join('');
    courseSelect.innerHTML = '<option value="">' + @json(__('Escolha um curso')) + '</option>' + (courses || []).map(c => `<option value="${c.id}">${c.title}</option>`).join('');
    instructorSelect.innerHTML = '<option value="">' + @json(__('Escolha um instrutor')) + '</option>' + (instructors || []).map(i => `<option value="${i.id}">${i.full_name}</option>`).join('');
}

async function fetchCertificates() {
    const res = await fetch('{{ route("api.certificates.index") }}');
    const data = await res.json();
    const list = data.data || data;
    renderCertificates(list);
}

function renderCertificates(list) {
    certificatesTableBody.innerHTML = '';
    (list || []).forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2">${s.id}</td>
            <td class="p-2">${s.certificate_code}</td>
            <td class="p-2">${s.student?.full_name || ''}</td>
            <td class="p-2">${s.course?.title || ''}</td>
            <td class="p-2">${s.issue_date || ''}</td>
            <td class="p-2">${s.status || ''}</td>
            <td class="p-2">
                <a href="{{ route('certificates.print', ':id') }}" target="_blank" class="printBtn px-2 py-1 bg-blue-600 text-white rounded" data-id="${s.id}">{{ __('Imprimir') }}</a>
                <button class="viewBtn px-2 py-1 bg-blue-400 text-white rounded" data-code="${s.certificate_code}">{{ __('Visualizar') }}</button>
                <button class="editBtn px-2 py-1 bg-yellow-400 rounded" data-id="${s.id}">{{ __('Editar') }}</button>
                <button class="deleteBtn px-2 py-1 bg-red-500 text-white rounded" data-id="${s.id}">{{ __('Excluir') }}</button>
            </td>
        `.replace(':id', s.id);
        certificatesTableBody.appendChild(tr);
    });
}

certificatesTableBody.addEventListener('click', async (e) => {
    if (e.target.classList.contains('editBtn')) {
        const id = e.target.dataset.id;
        const res = await fetch('{{ route("api.certificates.show", ":id") }}'.replace(':id', id));
        const item = await res.json();
        certificateForm.id.value = item.id;
        certificateForm.certificate_code.value = item.certificate_code;
        certificateForm.student_id.value = item.student_id || '';
        certificateForm.course_id.value = item.course_id || '';
        certificateForm.instructor_id.value = item.instructor_id || '';
        certificateForm.issue_date.value = item.issue_date || '';
        certificateForm.start_date.value = item.start_date || '';
        certificateForm.end_date.value = item.end_date || '';
        certificateForm.status.value = item.status || '';
        formTitle.textContent = @json(__('Editar certificado'));
        formContainer.classList.remove('hidden');
    }

    if (e.target.classList.contains('deleteBtn')) {
        if (!confirm(@json(__('Excluir certificado?')))) return;
        const id = e.target.dataset.id;
        await fetch('{{ route("api.certificates.destroy", ":id") }}'.replace(':id', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });
        fetchCertificates();
    }

    if (e.target.classList.contains('viewBtn')) {
        const code = e.target.dataset.code;
        const res = await fetch('{{ route("api.certificates.code.show", ":code") }}'.replace(':code', code));
        const data = await res.json();
        detailPre.textContent = JSON.stringify(data, null, 2);
        viewModal.classList.toggle('hidden');
    }
});

document.getElementById('cancelBtn').addEventListener('click', () => {
    formContainer.classList.add('hidden');
});

certificateForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = certificateForm.id.value;
    const payload = {
        student_id: certificateForm.student_id.value || null,
        course_id: certificateForm.course_id.value || null,
        instructor_id: certificateForm.instructor_id.value || null,
        issue_date: certificateForm.issue_date.value || null,
        start_date: certificateForm.start_date.value || null,
        end_date: certificateForm.end_date.value || null,
        status: certificateForm.status.value || null,
    };

    const method = 'PUT';
    const url = `{{ route("api.certificates.update", ":id") }}`.replace(':id', id);

    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
    });

    if (res.ok) {
        formContainer.classList.add('hidden');
        fetchCertificates();
    } else {
        alert(@json(__('Erro ao salvar certificado')));
    }
});

// init
fetchLists();
fetchCertificates();
</script>
@endsection
