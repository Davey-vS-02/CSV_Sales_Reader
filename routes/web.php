<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvUploadController;
use App\Http\Controllers\CsvProgressController;

//Return CSV upload view on homeepage.
Route::get('/', [CsvUploadController::class, 'showForm'])->name('csv.form'); //Homepage
Route::post('/', [CsvUploadController::class, 'handleUpload'])->name('csv.upload');

//Fallback route to CSV upload form.
Route::fallback(function () {
    return view('csv.upload');
});

//Route to data endpoint containing JSON. Data from the cache: progress of validation and upload of CSV files.
Route::get('/csv-progress/{path}', [CsvProgressController::class, 'getProgress']);

//View that displays invalid CSV rows. Uses the hashed filename as part of route.
Route::get('/csv-invalid/{filename}', [CsvUploadController::class, 'showInvalidRows'])->name('csv.invalid');