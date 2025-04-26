<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CsvUploadController;

Route::get('/upload', [CsvUploadController::class, 'showForm'])->name('csv.form');
Route::get('/', [CsvUploadController::class, 'showForm'])->name('csv.form'); //Homepage
Route::post('/upload', [CsvUploadController::class, 'handleUpload'])->name('csv.upload');
Route::post('/', [CsvUploadController::class, 'handleUpload'])->name('csv.upload');

Route::fallback(function () {
    return view('csv.upload');
});
