<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CsvProgressController extends Controller
{
    /**
     * Get the progress of the CSV upload/job.
     *
     * @param  string  $path
     * @return \Illuminate\Http\JsonResponse
     */

     //Gets upload/validation progress from cache and sends it to data endpoint as JSON.
     public function getProgress($path)
    {
        //Retrieve the progress from cache.
        $progress = Cache::get("csv_progress_{$path}");

        //Return values from cache as JSON.
        if($progress) {
            return response()->json($progress);
        } 
        else {
            //Return default values if no data is found in cache.
            return response()->json([
                'current' => 0,
                'total' => 1,
                'valid' => 0,
                'invalid' => 0,
            ]);
        }
    }
}
