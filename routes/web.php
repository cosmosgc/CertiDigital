<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin pages that use the API for data modifications
    Route::view('/students', 'students.index')->name('students.index');
    Route::view('/instructors', 'instructors.index')->name('instructors.index');
    Route::view('/courses', 'courses.index')->name('courses.index');
    Route::view('/certificates', 'certificates.index')->name('certificates.index');
    Route::view('/certificates/emit', 'certificates.emit')->name('certificates.emit');
});
Route::get('/certificates/{certificate}/print', [\App\Http\Controllers\CertificatePrintController::class, 'show'])->name('certificates.print');


require __DIR__.'/auth.php';
