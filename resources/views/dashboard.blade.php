<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Painel') }}
        </h2>
    </x-slot>

    {{-- Data provided by \App\Http\Controllers\DashboardController@index --}}

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('Usuários') }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $userCount }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('Certificados') }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $certificateCount }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('Cursos') }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $courseCount }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">{{ __('Alunos') }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $studentCount }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Certificados mensais') }}</h3>
                    <canvas id="certChart" height="120"></canvas>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Certificados recentes') }}</h3>
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-gray-500">
                                <th class="p-2">{{ __('ID') }}</th>
                                <th class="p-2">{{ __('Aluno') }}</th>
                                <th class="p-2">{{ __('Curso') }}</th>
                                <th class="p-2">{{ __('Data') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCertificates as $c)
                                <tr class="border-t border-gray-100 dark:border-gray-700">
                                    <td class="p-2">{{ $c->id }}</td>
                                    <td class="p-2">{{ $c->student->full_name ?? ($c->student->name ?? '—') }}</td>
                                    <td class="p-2">{{ $c->course->title ?? ($c->course->name ?? '—') }}</td>
                                    <td class="p-2">{{ $c->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const months = @json($months);
        const certs = @json($certsByMonth);

        const ctx = document.getElementById('certChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: '{{ __('Certificados') }}',
                    data: certs,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.1)',
                    fill: true,
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</x-app-layout>
