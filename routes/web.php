<?php

use App\Http\Controllers\FetchDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FetchDataController::class, 'index']);
Route::post('/fetch', [FetchDataController::class, 'run'])->name('fetch.run');
