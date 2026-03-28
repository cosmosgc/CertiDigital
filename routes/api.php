<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseClassController;
use App\Http\Controllers\Api\CourseClassAttendanceController;
use App\Http\Controllers\Api\CourseClassAttendanceRecordController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\InstructorController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CourseEnrollmentController;
use App\Http\Controllers\Api\VerificationLogController;
use App\Http\Controllers\Api\ScheduleEventController;

Route::name('api.')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Public read endpoints
    Route::apiResource('courses', CourseController::class)->only(['index', 'show']);
    Route::apiResource('course-classes', CourseClassController::class)->only(['index', 'show']);
    Route::apiResource('certificates', CertificateController::class)->only(['index', 'show']);
    Route::apiResource('students', StudentController::class)->only(['index', 'show']);
    Route::apiResource('instructors', InstructorController::class)->only(['index', 'show']);
    Route::apiResource('schedule-events', ScheduleEventController::class)->only(['index', 'show']);
    Route::get('certificates/code/{code}', [CertificateController::class, 'getByCode'])->name('certificates.code.show');
    Route::post('certificates/{code}/verify', [CertificateController::class, 'verify']);

    Route::get('admin/users', [\App\Http\Controllers\Admin\UserController::class, 'list'])->name('admin.users');

    // Protected endpoints (require administrator privileges).
    // Previously we used the Spatie `role:admin` middleware along with
    // `auth:sanctum`. the custom `admin.only` middleware bundles both
    // checks (authentication + role) so you no longer need to include a
    // separate `auth:` guard.  switch to it below and remove the `role:`
    // entry if you prefer.
    //
    // Example with both guards:
    // Route::middleware(['auth:sanctum','role:admin'])->group(function () {
    //
    // Example using our new middleware:
    Route::middleware('admin.only')->group(function () {
        Route::apiResource('courses', CourseController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('course-classes', CourseClassController::class)->only(['store', 'update', 'destroy']);
        Route::post('course-classes/{course_class}/attendances', [CourseClassAttendanceController::class, 'store'])->name('course-classes.attendances.store');
        Route::put('course-class-attendances/{course_class_attendance}', [CourseClassAttendanceController::class, 'update'])->name('course-class-attendances.update');
        Route::delete('course-class-attendances/{course_class_attendance}', [CourseClassAttendanceController::class, 'destroy'])->name('course-class-attendances.destroy');
        Route::post('course-class-attendances/{course_class_attendance}/records', [CourseClassAttendanceRecordController::class, 'store'])->name('course-class-attendances.records.store');
        Route::delete('course-class-attendance-records/{course_class_attendance_record}', [CourseClassAttendanceRecordController::class, 'destroy'])->name('course-class-attendance-records.destroy');
        Route::apiResource('students', StudentController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('instructors', InstructorController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('schedule-events', ScheduleEventController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('certificates', CertificateController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('course-enrollments', CourseEnrollmentController::class);
        Route::apiResource('verification-logs', VerificationLogController::class)->only(['index', 'store', 'show', 'destroy']);

        // admin user/role management endpoints
        Route::post('admin/users/{user}/toggle-admin', [\App\Http\Controllers\Admin\UserController::class, 'toggleAdmin'])->name('admin.users.toggle-admin');
    });
});
