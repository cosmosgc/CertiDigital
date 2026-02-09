@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800  overflow-hidden shadow-sm  sm:rounded-lg p-6">
        <h2 class="text-xl font-semibold">{{ __('Emitir certificado') }}</h2>

        <form id="emitForm" class="mt-4 space-y-4">
            <div>
                <label class="block text-sm font-medium">{{ __('Aluno') }}</label>
                <select name="student_id" id="studentSelect" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm "></select>
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
                    <input type="date" name="issue_date" id="issue_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm " />
                </div>
                <div>
                    <label class="block text-sm font-medium">{{ __('Data de início') }}</label>
                    <input type="date" name="start_date" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm " />
                </div>
                <div>
                    <label class="block text-sm font-medium">{{ __('Data de término') }}</label>
                    <input type="date" name="end_date" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm " />
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
const studentSelect = document.getElementById('studentSelect');
const instructorSelect = document.querySelector('select[name="instructor_id"]');
const courseSelect = document.getElementById('courseSelect');
const emitForm = document.getElementById('emitForm');
const result = document.getElementById('result');

async function fetchChoices() {
    const [studentsRes, coursesRes, instructorsRes] = await Promise.all([fetch('{{ route("api.students.index") }}'), fetch('{{ route("api.courses.index") }}'), fetch('{{ route("api.instructors.index") }}')]);
    const students = (await studentsRes.json()).data || (await studentsRes.json());
    const courses = (await coursesRes.json()).data || (await coursesRes.json());
    const instructors = (await instructorsRes.json()).data || (await instructorsRes.json());

    studentSelect.innerHTML = '<option value="">' + @json(__('Escolha um aluno')) + '</option>' + (students || []).map(s => `<option value="${s.id}">${s.full_name}</option>`).join('');
    courseSelect.innerHTML = '<option value="">' + @json(__('Escolha um curso')) + '</option>' + (courses || []).map(c => `<option value="${c.id}">${c.title}</option>`).join('');
    instructorSelect.innerHTML = '<option value="">' + @json(__('Escolha um instrutor')) + '</option>' + (instructors || []).map(i => `<option value="${i.id}">${i.full_name}</option>`).join('');
}

function generateCode() {
    return 'CRT-' + Math.random().toString(36).slice(2, 10).toUpperCase();
}

emitForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        certificate_code: generateCode(),
        student_id: emitForm.student_id.value,
        instructor_id: emitForm.instructor_id.value,
        course_id: emitForm.course_id.value,
        issue_date: emitForm.issue_date.value || null,
        start_date: emitForm.start_date.value || null,
        end_date: emitForm.end_date.value || null,
        status: emitForm.status.value || null,
    };

    const res = await fetch('{{ route("api.certificates.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
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

fetchChoices();
</script>
@endsection
