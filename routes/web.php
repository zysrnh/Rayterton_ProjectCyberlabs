<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\AlumniProfileController;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'pages.home')->name('home');

Route::view('/about', 'pages.about')->name('about');

Route::view('/contact', 'pages.contact')->name('contact');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $profile = Auth::user()->alumniProfile()->with(['educations', 'certificates', 'seaServices'])->first();
        return Inertia::render('Dashboard', [
            'profile' => $profile
        ]);
    })->name('dashboard');

    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    Route::get('/admin/users', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/residents', [\App\Http\Controllers\AdminController::class, 'residents'])->name('admin.residents');
    Route::post('/admin/users', [\App\Http\Controllers\AdminController::class, 'store'])->name('admin.users.store');

    Route::get('/admin/verifications', [\App\Http\Controllers\AdminController::class, 'queue'])->name('admin.verifications.queue');
    Route::post('/admin/verifications/{id}/in-review', [\App\Http\Controllers\AdminController::class, 'markAsReviewing'])->name('admin.verifications.in_review');
    Route::post('/admin/verifications/{id}/approve', [\App\Http\Controllers\AdminController::class, 'approve'])->name('admin.verifications.approve');
    
    // Account Governance & Trash Center
    Route::delete('/admin/users/{id}', [\App\Http\Controllers\AdminController::class, 'destroyAlumni'])->name('admin.users.destroy');
    Route::get('/admin/trash', [\App\Http\Controllers\AdminController::class, 'trash'])->name('admin.trash');
    Route::post('/admin/trash/{id}/restore', [\App\Http\Controllers\AdminController::class, 'restoreAlumni'])->name('admin.users.restore');
    Route::delete('/admin/trash/{id}/purge', [\App\Http\Controllers\AdminController::class, 'purgeAlumni'])->name('admin.users.purge');

    Route::post('/alumni/master-profile', [AlumniProfileController::class, 'updateMasterProfile'])->name('alumni.master-profile.update');
    Route::post('/alumni/toggle-availability', [AlumniProfileController::class, 'toggleAvailability'])->name('alumni.toggle_availability');
    
    // Education management
    Route::post('/alumni/educations', [\App\Http\Controllers\AlumniDataController::class, 'storeEducation'])->name('alumni.educations.store');
    Route::post('/alumni/educations/{id}/update', [\App\Http\Controllers\AlumniDataController::class, 'updateEducation'])->name('alumni.educations.update');
    Route::delete('/alumni/educations/{id}', [\App\Http\Controllers\AlumniDataController::class, 'destroyEducation'])->name('alumni.educations.destroy');

    // Certificate management
    Route::post('/alumni/certificates', [\App\Http\Controllers\AlumniDataController::class, 'storeCertificate'])->name('alumni.certificates.store');
    Route::post('/alumni/certificates/{id}/update', [\App\Http\Controllers\AlumniDataController::class, 'updateCertificate'])->name('alumni.certificates.update');
    Route::delete('/alumni/certificates/{id}', [\App\Http\Controllers\AlumniDataController::class, 'destroyCertificate'])->name('alumni.certificates.destroy');

    // Sea Service management
    Route::post('/alumni/sea-services', [\App\Http\Controllers\AlumniDataController::class, 'storeSeaService'])->name('alumni.seasearvices.store');
    Route::post('/alumni/sea-services/{id}/update', [\App\Http\Controllers\AlumniDataController::class, 'updateSeaService'])->name('alumni.seasearvices.update');
    Route::delete('/alumni/sea-services/{id}', [\App\Http\Controllers\AlumniDataController::class, 'destroySeaService'])->name('alumni.seasearvices.destroy');

    Route::post('/alumni/verify', [AlumniProfileController::class, 'submitForVerification'])->name('alumni.verify.submit');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Secure Document Vault Proxy
    Route::get('/vault/access/{path}', [\App\Http\Controllers\FileControlController::class, 'access'])
        ->where('path', '.*')
        ->name('vault.access');
});

require __DIR__.'/auth.php';
