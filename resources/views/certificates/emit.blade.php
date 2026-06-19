@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white   overflow-hidden shadow-sm  sm:rounded-lg p-6">
        <h2 class="text-xl font-semibold">{{ __('Emitir certificado') }}</h2>

        <form id="emitForm" class="mt-4 space-y-4">
            <div>
                <label class="block text-sm font-medium">{{ __('Aluno') }}</label>
                <div class="relative mt-1" id="studentAutocomplete">
                    <input type="text" id="studentSearch" autocomplete="off" placeholder="{{ __('Digite para buscar um aluno...') }}" class="block w-full rounded-md border-gray-300 shadow-sm ">
                    <input type="hidden" name="student_id" id="studentId">
                    <div id="studentDropdown" class="absolute z-50 mt-1 hidden w-full rounded-md border border-gray-200 bg-white shadow-lg"></div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Instrutor') }}</label>
                <select name="instructor_id" id="instructorSelect"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm ">
                    <option value="">{{ __('Selecione o instrutor') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium">{{ __('Curso') }}</label>
                <select name="course_id" id="courseSelect" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm "></select>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">{{ __('Data de emissão') }}</label>
                    <input type="date" name="issue_date" id="issue_date" value="{{ request('issue_date', now()->toDateString()) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm " />
                </div>
                <div>
                    <label class="block text-sm font-medium">{{ __('Data de início') }}</label>
                    <input type="date" name="start_date" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm " />
                </div>
                <div>
                    <label class="block text-sm font-medium">{{ __('Data de término') }}</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('issue_date', now()->toDateString()) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm " />
                </div>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium">{{ __('Status') }}</label>
                <select
                    name="status"
                    id="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm "
                    required
                >
                    <option value="valid">{{ __('Válido') }}</option>
                    <option value="revoked">{{ __('Revogado') }}</option>
                </select>
            </div>


            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-pink-600 text-white rounded">{{ __('Emitir') }}</button>
            </div>
        </form>

        <div id="result" class="mt-4"></div>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = @json(auth()->id());
const studentSearch = document.getElementById('studentSearch');
const studentIdInput = document.getElementById('studentId');
const studentDropdown = document.getElementById('studentDropdown');
const instructorSelect = document.querySelector('select[name="instructor_id"]');
const courseSelect = document.getElementById('courseSelect');
const emitForm = document.getElementById('emitForm');
const result = document.getElementById('result');
const issueDateInput = document.getElementById('issue_date');
const startDateInput = document.getElementById('start_date');
const endDateInput = document.getElementById('end_date');
const statusSelect = document.getElementById('status');
const presetStudentId = @json(request('student_id'));
const presetCourseId = @json(request('course_id'));
const todayDate = @json(now()->toDateString());
const presetIssueDate = @json(request('issue_date')) || todayDate;
const presetStartDate = @json(request('start_date'));
const presetEndDate = @json(request('end_date'));
const presetStatus = @json(request('status', 'valid'));
const presetInstructorId = @json(request('instructor_id'));
let selectedStudent = null;
let searchTimeout = null;

function selectStudent(student) {
    selectedStudent = student;
    studentIdInput.value = student.id;
    studentSearch.value = student.full_name;
    studentDropdown.classList.add('hidden');
}

function clearStudent() {
    selectedStudent = null;
    studentIdInput.value = '';
}

function renderDropdown(students) {
    if (!students.length) {
        studentDropdown.classList.add('hidden');
        return;
    }
    studentDropdown.innerHTML = students.map(s =>
        `<button type="button" class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 focus:bg-gray-100 focus:outline-none" data-id="${s.id}" data-name="${s.full_name.replace(/"/g, '&quot;')}">${s.full_name}</button>`
    ).join('');
    studentDropdown.classList.remove('hidden');
}

async function searchStudents(query) {
    if (!query || query.length < 1) {
        studentDropdown.classList.add('hidden');
        return;
    }
    const res = await fetch(`{{ route("api.students.index") }}?search=${encodeURIComponent(query)}&per_page=50`);
    const json = await res.json();
    renderDropdown(json.data || json);
}

studentSearch.addEventListener('input', function () {
    clearStudent();
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => searchStudents(this.value), 250);
});

studentSearch.addEventListener('blur', function () {
    setTimeout(() => studentDropdown.classList.add('hidden'), 200);
});

studentSearch.addEventListener('focus', function () {
    if (this.value.length >= 1 && studentDropdown.children.length) {
        studentDropdown.classList.remove('hidden');
    }
});

studentDropdown.addEventListener('mousedown', function (e) {
    const btn = e.target.closest('button');
    if (!btn) return;
    selectStudent({ id: btn.dataset.id, full_name: btn.dataset.name });
});

studentDropdown.addEventListener('click', function (e) {
    const btn = e.target.closest('button');
    if (!btn) return;
    selectStudent({ id: btn.dataset.id, full_name: btn.dataset.name });
});

function applyPresets() {
    if (presetStudentId) {
        fetch(`{{ route("api.students.show", ["student" => "__ID__"]) }}`.replace('__ID__', presetStudentId))
            .then(r => r.ok ? r.json() : null)
            .then(student => { if (student) selectStudent(student); });
    }

    if (presetCourseId) {
        courseSelect.value = String(presetCourseId);
    }

    if (presetInstructorId) {
        instructorSelect.value = String(presetInstructorId);
    }

    if (presetIssueDate) {
        issueDateInput.value = presetIssueDate;
    }

    if (presetStartDate) {
        startDateInput.value = presetStartDate;
    }

    if (presetEndDate) {
        endDateInput.value = presetEndDate;
    }

    if (presetStatus) {
        statusSelect.value = presetStatus;
    }
}

async function fetchChoices() {
    const [coursesRes, instructorsRes] = await Promise.all([fetch('{{ route("api.courses.index") }}'), fetch('{{ route("api.instructors.index") }}')]);
    const courses = (await coursesRes.json()).data || (await coursesRes.json());
    const instructors = (await instructorsRes.json()).data || (await instructorsRes.json());

    courseSelect.innerHTML = '<option value="">' + @json(__('Escolha um curso')) + '</option>' + (courses || []).map(c => `<option value="${c.id}">${c.title}</option>`).join('');
    instructorSelect.innerHTML = '<option value="">' + @json(__('Escolha um instrutor')) + '</option>' + (instructors || []).map(i => `<option value="${i.id}">${i.full_name}</option>`).join('');
    applyPresets();
}

function generateCode() {
    return 'CRT-' + Math.random().toString(36).slice(2, 10).toUpperCase();
}

emitForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        certificate_code: generateCode(),
        student_id: studentIdInput.value,
        instructor_id: emitForm.instructor_id.value,
        course_id: emitForm.course_id.value,
        issue_date: emitForm.issue_date.value || null,
        start_date: emitForm.start_date.value || null,
        end_date: emitForm.end_date.value || null,
        status: emitForm.status.value || null,
        user_id: currentUserId,
    };

    const res = await fetch('{{ route("api.certificates.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-User-Id': currentUserId,
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
    });

    if (res.ok) {
        const data = await res.json();
        result.innerHTML = `<div class="p-4 bg-green-100 rounded">{{ __('Certificado emitido:') }} <strong>${data.certificate_code}</strong></div>`;
        emitForm.reset();
    } else {
        const err = await res.json();
        result.innerHTML = `<div class="p-4 bg-red-100 rounded">{{ __('Erro:') }} ${err.message || @json(__('Não foi possível emitir o certificado'))}</div>`;
    }
});

applyPresets();
fetchChoices();
</script>
@endsection
