<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log; //For debugging, remember to remove on cleanup.

class ProcessCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;

    //Create a new job instance.
    public function __construct($path)
    {
        //Get path of csv file saved in storage/app/private.
        $this->path = $path;
    }

    //Execute the job.
    public function handle()
    {
        //Open the file.
        $file = Storage::path($this->path);

        //Count the number of rows to determine progress of loading bar.
        $rowCount = 0;
        if (($handle = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rowCount++;
            }
            fclose($handle);
        }


        //Open for reading.
        if(($handle = fopen($file, 'r')) !== false) {
            $header = null;

            //Parse each csv row as an array, check if there are rows to parse.
            while(($row = fgetcsv($handle, 1000, ',')) !== false) {
                //Set the first row to the header.
                if(!$header) {
                    $header = $row;
                    continue; //Skip tyo next row.
                }

                //Map header columns to values.
                $data = array_combine($header, $row);

                //Validate the row.
                if($this->isValidRow($data)) {
                    //Save to sales records.
                    $this->saveValidRecord($data);                    
                } else {
                    //Save to invalid sales records.
                    $this->saveInvalidRecord($data);                    
                }

                //Update progress tracking here.
                //Possibly use cache to save progress and animate progress bar.
            }

            fclose($handle);
        }
    }

    protected function isValidRow(array $data)
    {
        $orderNum = $data['ORDER #'];
        echo $orderNum;
        //Check if all values are not null.
        if(in_array(null, $data, true)) {
            //Row contains at least one empty field.
            echo "\033[31mInvalid row found! Null value!\033[0m";
            return false;
        }

        //Check if values are contained in enums.
        if(!in_array($data['COMM YES/NO'], ['YES', 'NO'])) {
            echo "\033[31mInvalid row found! Value not in COMM enum!\033[0m";
            return false;
        }

        if(!in_array($data['HEAD+BASE'], ['X', 'N/A'])) {
            echo "\033[31mInvalid row found! Value not in H+B enum!\033[0m";
            return false;
        }

        if(!in_array($data['HEAD+BASE + FRAME'], ['X', 'N/A'])) {
            echo "\033[31mInvalid row found! Value not in H+B+F enum!\033[0m";
            return false;
        }

        if(!in_array($data['COMPLETE'], ['X', 'N/A'])) {
            echo "\033[31mInvalid row found! Value not in COMPLETE enum!\033[0m";
            return false;
        }

        if(!in_array($data['DIRECT Y/N'], ['YES', 'NO'])) {
            echo "\033[31mInvalid row found! Value not in DIRECT enum!\033[0m";
            return false;
        }

        if(!in_array($data['PHOTO grnte/prpxs'], ['NO', 'G', 'P'])) {
            echo "\033[31mInvalid row found! Value not in PHOTO enum!\033[0m";
            return false;
        }

        if(!in_array($data['WALL INCLUDED'], ['YES', 'NO'])) {
            echo "\033[31mInvalid row found! Value not in WALL enum!\033[0m";
            return false;
        }        

        //If no validation has failed, pass the validation.
        echo "\033[32mValid row found yeah.\033[0m";
        return true;
    }

    protected function saveValidRecord(array $data)
    {
        //Insert into sales table
        //SalesRecord::create($data);
    }

    protected function saveInvalidRecord(array $data)
    {
        //Insert into your invalid records table
        //InvalidSalesRecord::create(['row_data' => json_encode($data)]);
    }
}
