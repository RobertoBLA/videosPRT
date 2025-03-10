<?php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideoController::class, 'index']);
Route::post('/guardar-video', [VideoController::class, 'store'])->name('video.store');
Route::get('/config', [VideoController::class, 'getConfig']);
Route::put('/actualizar-config', [VideoController::class, 'updatePlayerConfig'])->name('update.config');
Route::post('/update-video-order', [VideoController::class, 'updateOrder']);



