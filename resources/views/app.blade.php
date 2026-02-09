@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('CertiDigital Admin') }}</h1>

        @auth
            <div class="mt-6 bg-white dark:bg-gray-800 p-4 rounded shadow-sm">
                <div class="flex gap-2 items-center">
                    <input id="certSearch" type="search" placeholder="{{ __('Search certificates by code, student or course') }}" class="flex-1 rounded border-gray-300 shadow-sm p-2" />
                    <button id="searchBtn" class="px-4 py-2 bg-indigo-600 text-white rounded">{{ __('Search') }}</button>
                    <a href="{{ route('certificates.index') }}" class="px-4 py-2 bg-gray-200 rounded">{{ __('Manage') }}</a>
                </div>

                <div class="mt-4">
                    <table class="w-full table-auto" id="homeCertificatesTable">
                        <thead>
                            <tr class="text-left">
                                <th class="p-2">{{ __('ID') }}</th>
                                <th class="p-2">{{ __('Code') }}</th>
                                <th class="p-2">{{ __('Student') }}</th>
                                <th class="p-2">{{ __('Course') }}</th>
                                <th class="p-2">{{ __('Issue Date') }}</th>
                                <th class="p-2">{{ __('Status') }}</th>
                                <th class="p-2">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('students.index') }}" class="p-4 bg-indigo-600 text-white rounded hover:bg-indigo-700">{{ __('Students') }}</a>
                <a href="{{ route('instructors.index') }}" class="p-4 bg-green-600 text-white rounded hover:bg-green-700">{{ __('Instructors') }}</a>
                <a href="{{ route('courses.index') }}" class="p-4 bg-yellow-500 text-white rounded hover:bg-yellow-600">{{ __('Courses') }}</a>
                <a href="{{ route('certificates.emit') }}" class="p-4 bg-pink-600 text-white rounded hover:bg-pink-700">{{ __('Emit Certificate') }}</a>
            </div>

            <p class="mt-6 text-sm text-gray-600">{{ __('Use the top links to manage Students, Instructors, Courses, or issue certificates.') }}</p>
            <div class="mt-6 bg-white dark:bg-gray-800 p-4 rounded shadow-sm">
                <div class="flex gap-2 items-center">
                    <input id="certSearch" type="search" placeholder="{{ __('Search certificates by code, student or course') }}" class="flex-1 rounded border-gray-300 shadow-sm p-2" />
                    <button id="searchBtn" class="px-4 py-2 bg-indigo-600 text-white rounded">{{ __('Search') }}</button>
                    <a href="{{ route('certificates.index') }}" class="px-4 py-2 bg-gray-200 rounded">{{ __('Manage') }}</a>
                </div>

                <div class="mt-4">
                    <table class="w-full table-auto" id="homeCertificatesTable">
                        <thead>
                            <tr class="text-left">
                                <th class="p-2">{{ __('ID') }}</th>
                                <th class="p-2">{{ __('Code') }}</th>
                                <th class="p-2">{{ __('Student') }}</th>
                                <th class="p-2">{{ __('Course') }}</th>
                                <th class="p-2">{{ __('Issue Date') }}</th>
                                <th class="p-2">{{ __('Status') }}</th>
                                <th class="p-2">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        @endauth

        <script>
            (function () {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const tableBody = document.querySelector('#homeCertificatesTable tbody');
                const searchInput = document.getElementById('certSearch');
                const searchBtn = document.getElementById('searchBtn');

                async function fetchCertificates(q = '') {
                    const url = '{{ url('/') }}/api/certificates' + (q ? ('?q=' + encodeURIComponent(q)) : '');
                    const res = await fetch(url, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }
                    });

                    if (!res.ok) return;
                    const data = await res.json();
                    const list = data.data || data;
                    render(list);
                }

                function render(list) {
                    if (!tableBody) return;
                    tableBody.innerHTML = '';
                    (list || []).forEach(s => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="p-2">${s.id}</td>
                            <td class="p-2">${s.certificate_code}</td>
                            <td class="p-2">${(s.student && s.student.full_name) || ''}</td>
                            <td class="p-2">${(s.course && s.course.title) || ''}</td>
                            <td class="p-2">${s.issue_date || ''}</td>
                            <td class="p-2">${s.status || ''}</td>
                            <td class="p-2">
                                <a href="{{ route('certificates.print', ['certificate' => '__ID__']) }}" target="_blank" class="px-2 py-1 bg-blue-600 text-white rounded">{{ __('Print') }}</a>
                                <a href="{{ route('certificates.index') }}" class="px-2 py-1 bg-gray-200 rounded">{{ __('Manage') }}</a>
                            </td>
                        `.replace('__ID__', s.id);
                        tableBody.appendChild(tr);
                    });
                }

                if (searchBtn) {
                    searchBtn.addEventListener('click', () => fetchCertificates(searchInput.value));
                    searchInput.addEventListener('keyup', (e) => { if (e.key === 'Enter') fetchCertificates(searchInput.value); });
                    // load recent certificates on auth pages
                    fetchCertificates();
                }
            })();
        </script>
    </div>
</div>
@endsection
