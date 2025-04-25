<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('csv.upload');
});

use App\Http\Controllers\CsvUploadController;

Route::get('/upload', [CsvUploadController::class, 'showForm'])->name('csv.form');
Route::post('/upload', [CsvUploadController::class, 'handleUpload'])->name('csv.upload');
