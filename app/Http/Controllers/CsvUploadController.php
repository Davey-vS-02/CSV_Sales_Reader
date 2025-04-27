<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCsvJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CsvUploadController extends Controller
{
    //Method to display file upload form view.
    public function showForm()
    {
        return view('csv.upload');
    }

    public function handleUpload(Request $request)
    {
        //Simple upload validation. File is required, must be a valid file and of type csv.
        //PERSONAL NOTE:
        //MIMES: Multipurpose Internet Mail Extensions, basically a standard that defines the type of file required.
        $request->validate([
            'csv_file' => 'required|file|mimes:csv', //Can add max file size here: |max:10240 (10MB)
        ]);

        //Clear invalid records table to ensure records shown are newest.
        \DB::table('invalid_sales')->truncate();

        //CSV file gets stored under storage/app/private/uploads. Path to file is stored in $path.
        $path = $request->file('csv_file')->store('uploads');
        $fileName = basename( $path ); //Returns the trailing name of a path.

        // Dispatch job here. (Still needs queue worker to start processing jobs in the queue. Run php artisan queue:work)
        ProcessCsvJob::dispatch($path);

        //Success message to display after initial file save is successful and validation has started.
        return redirect()->back()->with('status', 'File saved successfully! Currently processing!')
        ->with('filename', $fileName);
    }

    public function showInvalidRows($filename)
    {
        //Get invalid records from db.
        $invalidRows = DB::table('invalid_sales')->get();

        //Return view where invalid records will be displayed.
        return view('csv.invalid', compact('invalidRows'));
    }
}