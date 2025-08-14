<?php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideoController::class, 'index'])->name('home');
Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
Route::post('/videos/{video}/transcribe', [VideoController::class, 'transcribe'])->name('videos.transcribe');
Route::post('/videos/{video}/rewrite', [VideoController::class, 'rewrite'])->name('videos.rewrite');
Route::get('/videos/{video}/view', [VideoController::class, 'view'])->name('videos.view');
Route::get('/videos/{video}/download', [VideoController::class, 'download'])->name('videos.download');
Route::delete('/videos/{video}', [VideoController::class, 'delete'])->name('videos.delete');
