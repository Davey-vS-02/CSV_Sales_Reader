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

     public function getProgress($path)
    {
        // Attempt to retrieve the progress from cache
        $progress = Cache::get("csv_progress_{$path}");

        // If there's no cached progress, return default values
        if ($progress) {
            return response()->json($progress);
        } 
        else {
            // Return default values if no progress is found in cache
            return response()->json([
                'current' => 0,
                'total' => 1,
                'valid' => 0,
                'invalid' => 0,
            ]);
        }
    }
}
