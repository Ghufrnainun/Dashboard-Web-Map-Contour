<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContourController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Jalur ini untuk tampilan User Interface (Blade).
|
*/

// 1. Halaman HOME (List Project & Form Bikin Project Baru)
// URL: http://localhost:8000/
Route::get('/', [ContourController::class, 'index'])->name('home');

// 2. Action Simpan Project Baru (Saat tombol Submit diklik)
// URL: http://localhost:8000/projects (POST)
Route::post('/projects', [ContourController::class, 'storeProject'])->name('projects.store');

// 3. Halaman DASHBOARD UTAMA (Lihat Peta)
// URL: http://localhost:8000/project/1
Route::get('/project/{id}', [ContourController::class, 'show'])->name('projects.show');

// 4. Jalur DATA JSON (Khusus dipanggil sama Javascript/Alpine.js)
// URL: http://localhost:8000/project/1/data
// Ini yang bikin peta bisa muncul titik-titiknya tanpa reload halaman
Route::get('/project/{id}/data', [ContourController::class, 'getMeasurements'])->name('projects.data');

// 5. Hapus Data Pengukuran
Route::delete('/measurements/{id}', [ContourController::class, 'destroy'])->name('measurements.destroy');
