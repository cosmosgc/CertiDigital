@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">CertiDigital Admin</h1>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('students.index') }}" class="p-4 bg-indigo-600 text-white rounded hover:bg-indigo-700">Students</a>
            <a href="{{ route('instructors.index') }}" class="p-4 bg-green-600 text-white rounded hover:bg-green-700">Instructors</a>
            <a href="{{ route('courses.index') }}" class="p-4 bg-yellow-500 text-white rounded hover:bg-yellow-600">Courses</a>
            <a href="{{ route('certificates.emit') }}" class="p-4 bg-pink-600 text-white rounded hover:bg-pink-700">Emit Certificate</a>
        </div>

        <p class="mt-6 text-sm text-gray-600">Use the top links to manage Students, Instructors, Courses, or issue certificates. All data modifications are performed via the API.</p>
    </div>
</div>
@endsection
