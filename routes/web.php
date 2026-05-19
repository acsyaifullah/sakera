<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KgbKpController; // Perbaikan typo dari Kbg menjadi Kgb

// Route::get('/manual-link', function () {
//     $target = storage_path('app/public');
//     $link = public_path('storage');

//     if (file_exists($link)) {
//         return 'Link sudah ada.';
//     }

//     // Mencoba membuat link menggunakan fungsi PHP native
//     symlink($target, $link);

//     return 'Link storage berhasil dibuat via Web.';
// });

// --- Login & Logout ---
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Protected Routes (Auth) ---
Route::middleware(['auth'])->group(function () {

    // 1. Dashboard & Berkas Dokumen
    Route::get('/dashboard', [DocumentController::class, 'index'])->name('dashboard');
    Route::post('/upload', [DocumentController::class, 'upload'])->name('upload');
    Route::get('/preview/{id}', [DocumentController::class, 'preview'])->name('preview');
    Route::get('/download/{id}', [DocumentController::class, 'download'])->name('download');
    Route::get('/download-batch', [DocumentController::class, 'downloadBatch'])->name('download.batch');
    Route::delete('/document/{id}', [DocumentController::class, 'destroy'])->name('document.destroy');

    // API untuk mengambil daftar berkas berdasarkan kategori dan judul (AJAX)
    Route::get('/documents/list-by-category', [DocumentController::class, 'getDocumentsByCategory'])->name('documents.by-category');

    // 2. Fitur Ganti Password
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('password.update');

    // 3. Khusus Admin
    Route::middleware(['role:admin'])->group(function () {
        // Fitur Tabel KGB & KP (Penting: Nama Controller harus sesuai dengan file aslinya)
        Route::resource('kgb-kp', KgbKpController::class)->except(['create', 'show', 'edit']);

        // Validasi Dokumen
        Route::post('/document/validate/{id}', [DocumentController::class, 'validateDocument'])->name('document.validate');
        
        // Manajemen User
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});