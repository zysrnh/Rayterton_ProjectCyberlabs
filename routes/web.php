<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\AlumniProfileController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $profile = Auth::user()->alumniProfile()->with(['educations', 'certificates', 'seaServices'])->first();
        return Inertia::render('Dashboard', [
            'profile' => $profile
        ]);
    })->name('dashboard');

    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    Route::get('/admin/users', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [\App\Http\Controllers\AdminController::class, 'store'])->name('admin.users.store');

    Route::get('/admin/verifications', [\App\Http\Controllers\AdminController::class, 'queue'])->name('admin.verifications.queue');
    Route::post('/admin/verifications/{id}/in-review', [\App\Http\Controllers\AdminController::class, 'markAsReviewing'])->name('admin.verifications.in_review');
    Route::post('/admin/verifications/{id}/approve', [\App\Http\Controllers\AdminController::class, 'approve'])->name('admin.verifications.approve');

    Route::put('/alumni/master-profile', [AlumniProfileController::class, 'updateMasterProfile'])->name('alumni.master-profile.update');
    Route::post('/alumni/educations', [\App\Http\Controllers\AlumniDataController::class, 'storeEducation'])->name('alumni.educations.store');
    Route::post('/alumni/certificates', [\App\Http\Controllers\AlumniDataController::class, 'storeCertificate'])->name('alumni.certificates.store');
    Route::post('/alumni/sea-services', [\App\Http\Controllers\AlumniDataController::class, 'storeSeaService'])->name('alumni.seasearvices.store');
    Route::post('/alumni/verify', [AlumniProfileController::class, 'submitForVerification'])->name('alumni.verify.submit');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
