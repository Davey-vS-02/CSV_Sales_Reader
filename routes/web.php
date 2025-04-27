<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvUploadController;
use App\Http\Controllers\CsvProgressController;

Route::get('/upload', [CsvUploadController::class, 'showForm'])->name('csv.form');
Route::get('/', [CsvUploadController::class, 'showForm'])->name('csv.form'); //Homepage
Route::post('/upload', [CsvUploadController::class, 'handleUpload'])->name('csv.upload');
Route::post('/', [CsvUploadController::class, 'handleUpload'])->name('csv.upload');

Route::fallback(function () {
    return view('csv.upload');
});

Route::get('/csv-progress/{path}', [CsvProgressController::class, 'getProgress']);

Route::get('/csv-invalid/{filename}', [CsvUploadController::class, 'showInvalidRows'])->name('csv.invalid');