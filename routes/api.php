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
    Route::get('certificates/code/{code}', [CertificateController::class, 'getByCode']);
    Route::post('certificates/{code}/verify', [CertificateController::class, 'verify']);

    // Protected endpoints (require authentication)
    // Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('courses', CourseController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('students', StudentController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('instructors', InstructorController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('certificates', CertificateController::class)->except(['index', 'show']);
        Route::apiResource('course-enrollments', CourseEnrollmentController::class);
        Route::apiResource('verification-logs', VerificationLogController::class)->only(['index', 'store', 'show', 'destroy']);
    // });
});