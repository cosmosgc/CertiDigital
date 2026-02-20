<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CertificateSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
})->name('home');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // pages that any authenticated user can view
    Route::view('/certificates/emit', 'certificates.emit')->name('certificates.emit');

    // administrative pages (only for users with the `admin` role)
    Route::middleware('role:admin')->group(function () {
        Route::view('/students', 'students.index')->name('students.index');
        Route::view('/instructors', 'instructors.index')->name('instructors.index');
        Route::view('/courses', 'courses.index')->name('courses.index');
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
