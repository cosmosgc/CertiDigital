<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\InstructorController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CourseEnrollmentController;
use App\Http\Controllers\Api\VerificationLogController;

Route::name('api.')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Public read endpoints
    Route::apiResource('courses', CourseController::class)->only(['index', 'show']);
    Route::apiResource('students', StudentController::class)->only(['index', 'show']);
    Route::apiResource('instructors', InstructorController::class)->only(['index', 'show']);
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
        Route::apiResource('students', StudentController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('instructors', InstructorController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('certificates', CertificateController::class);
        Route::apiResource('course-enrollments', CourseEnrollmentController::class);
        Route::apiResource('verification-logs', VerificationLogController::class)->only(['index', 'store', 'show', 'destroy']);

        // admin user/role management endpoints
        Route::post('admin/users/{user}/toggle-admin', [\App\Http\Controllers\Admin\UserController::class, 'toggleAdmin'])->name('admin.users.toggle-admin');
    });
});