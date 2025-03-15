<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/import', ImportController::class)->name('import');
Route::get('/import/data', \App\Http\Controllers\DataController::class)->name('import.data');
