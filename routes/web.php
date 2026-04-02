<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CertificateSettingController;
use App\Http\Controllers\ScheduleEventController;
use App\Models\CourseClass;
use App\Models\CourseClassAttendance;
use App\Models\CourseEnrollment;
use App\Models\Student;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
})->name('home');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
    
Route::get('/schedule-events', [ScheduleEventController::class, 'index'])->name('schedule-events.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // pages that any authenticated user can view
    Route::view('/certificates/emit', 'certificates.emit')->name('certificates.emit');

    // administrative pages (only for users with the `admin` role)
    Route::middleware('role:admin')->group(function () {
        Route::view('/students', 'students.index')->name('students.index');
        Route::get('/students/{student}', function (Student $student) {
            return view('students.show', compact('student'));
        })->name('students.show');
        Route::view('/instructors', 'instructors.index')->name('instructors.index');
        Route::view('/courses', 'courses.index')->name('courses.index');
        Route::view('/course-classes', 'course-classes.index')->name('course-classes.index');
        Route::get('/course-classes/{courseClass}', function (CourseClass $courseClass) {
            return view('course-classes.show', compact('courseClass'));
        })->name('course-classes.show');
        Route::get('/course-classes/{courseClass}/manage', function (CourseClass $courseClass) {
            return view('course-classes.manage', compact('courseClass'));
        })->name('course-classes.manage');
        Route::get('/course-classes/{courseClass}/enrollments/{courseEnrollment}', function (CourseClass $courseClass, CourseEnrollment $courseEnrollment) {
            abort_unless($courseEnrollment->course_class_id === $courseClass->id, 404);

            return view('course-classes.enrollment', compact('courseClass', 'courseEnrollment'));
        })->name('course-class-enrollments.show');
        Route::get('/course-classes/{courseClass}/attendances/{courseClassAttendance}', function (CourseClass $courseClass, CourseClassAttendance $courseClassAttendance) {
            abort_unless($courseClassAttendance->course_class_id === $courseClass->id, 404);

            return view('course-classes.attendance', compact('courseClass', 'courseClassAttendance'));
        })->name('course-class-attendances.show');
        Route::get('/course-classes/{courseClass}/attendance-report', function (CourseClass $courseClass) {
            return view('course-classes.attendance-report', compact('courseClass'));
        })->name('course-classes.attendance-report');
        Route::view('/certificates', 'certificates.index')->name('certificates.index');

        // Certificate settings routes
        Route::get('/certificate-settings', [CertificateSettingController::class, 'edit'])->name('certificate-settings.edit');
        Route::put('/certificate-settings', [CertificateSettingController::class, 'update'])->name('certificate-settings.update');

        // admin user management screen
        Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])
            ->name('admin.users.index');
    });
});
Route::get('/certificates/{certificate}/print', [\App\Http\Controllers\CertificatePrintController::class, 'show'])->name('certificates.print');


require __DIR__.'/auth.php';
