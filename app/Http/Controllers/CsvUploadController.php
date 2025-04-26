<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCsvJob;
use Illuminate\Http\Request;

class CsvUploadController extends Controller
{
    //Method to display file upload form.
    public function showForm()
    {
        return view('csv.upload');
    }

    public function handleUpload(Request $request)
    {
        //Upload validation. File must be present, valid file and of type csv.
        $request->validate([
            'csv_file' => 'required|file|mimes:csv', //Can add max file size here: |max:10240 (10MB)
        ]);

        //CSV file gets stored under storage/app/private/uploads. Path to file is stored in $path.
        $path = $request->file('csv_file')->store('uploads');
        $fileName = basename( $path ); //Returns the trailing name of a path.

        // Dispatch job here.
        ProcessCsvJob::dispatch($path);

        //Success message to display after file upload and validation is successful.
        return redirect()->back()->with('status', 'File uploaded successfully! Currently processing!')
        ->with('filename', $fileName);
    }
}