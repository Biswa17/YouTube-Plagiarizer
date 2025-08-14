<?php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideoController::class, 'index']);
Route::post('/videos', [VideoController::class, 'store']);
