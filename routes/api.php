<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContourController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Jalur ini untuk ESP32 kirim data.
| Tidak butuh CSRF Token, jadi aman buat mikrokontroler.
|
*/

// Endpoint: POST http://domain-kamu/api/record
Route::post('/record', [ContourController::class, 'storeMeasurement']);
