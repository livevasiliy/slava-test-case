<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/import', ImportController::class)->name('import');
