<?php

//Remember to modularize all validation function to simplifiy unit test logic.

namespace App\Jobs;

use App\Models\CsvProcessingJob;
use App\Models\InvalidSale;
use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
//use Illuminate\Support\Facades\Log; //For debugging, remember to remove on cleanup.

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
        echo "Path: $this->path"; //Output: (Path: uploads/JpDWH9kPSqIVuQFR1n3rsU8bA0aO76nKQPwy72fX.csv)
        //Open the file.
        $file = Storage::path($this->path);

        //Remove 'uploads/' prefix from the path.
        $fileName = str_replace('uploads/', '', $this->path);
        echo "Trimmed path: $fileName";

        //Remove BOM chars screwing with STORE header.
        $fileContent = file_get_contents($file);
        $fileContent = preg_replace('/^\xEF\xBB\xBF/', '', $fileContent);
        file_put_contents($file, $fileContent);

        //Count the number of rows to determine progress of loading bar.
        $totalRowCount = -1; //-1 for taking into account headers.
        if (($handle = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $totalRowCount++;
            }
            fclose($handle);
        }

        $validCount = 0;
        $invalidCount = 0;
        $currentRowCount = 0;

        //Open for reading.
        if(($handle = fopen($file, 'r')) !== false) {
            $headers = null;

            //Parse each csv row as an array, check if there are rows to parse.
            while(($row = fgetcsv($handle, 1000, ',')) !== false) {
                //Set the first row to the header.
                if(!$headers) {
                    $headers = $row;
                    continue; //Skip to next row.
                }

                //Map header columns to values.
                $data = array_combine($headers, $row);

                //Call the function and destructure the returned array into variables
                list($isValid, $errorColumn, $errorMessage) = $this->isValidRow($data);

                //Validate the row.
                if($isValid) {
                    //Save to sales records.
                    $currentRowCount++;
                    $validCount++;
                    $this->saveValidRecord($data);                    
                } else {
                    //Save to invalid sales records.
                    $currentRowCount++;
                    $invalidCount++;
                    $this->saveInvalidRecord($data, $errorColumn, $errorMessage);                    
                }

                //Use cache to save row progress for loading bar.
                //Update progress in cache every 10 rows.
                if ($currentRowCount % 10 === 0) {
                    $this->cacheProgress($fileName, $currentRowCount, $totalRowCount, $validCount, $invalidCount);
                }
            }

            //When processing completes, $currentRowCount will be equal to $rowCount. Cache one more time so loading bar can finish.
            $this->cacheProgress($fileName, $currentRowCount, $totalRowCount, $validCount, $invalidCount);

            //Log new csv file entry into database.
            $tableMap = [
                'csv_file_path' => $file,
                'valid_row_count' => $validCount,
                'invalid_row_count' => $invalidCount
            ];

            //Create db entry into csv_processing_jobs.
            CsvProcessingJob::create($tableMap);

            //Close csv file.
            fclose($handle);
        }
    }

    public function isValidRow(array $data)
    {
        //To track on what row validation failed.
        //$orderNum = $data['ORDER #'];
        //echo $orderNum;
        
        //All rows that are non nullable in csv. Some can be null in db.
        $tableNames = [
            'STORE', 
            'STORE CODE', 
            'DATE', 'ORDER #', 
            'RECEIPT#', 
            'AMOUNT OF RECEIPTS', 
            'BALANCE OUTSTANDING', 
            'DELIVERY DATE', 
            'COMM YES/NO', 
            'PRODUCT CODE', 
            'HEAD+BASE', 
            'HEAD+BASE + FRAME', 
            'COMPLETE', 'COLOR', 
            'DIRECT Y/N', 
            'PHOTO grnte/prpxs', 
            'WALL INCLUDED'
        ];

        //Check for empty or null fields. Comments not included.
        foreach($tableNames as $tableName) {
            if($data[$tableName] === null || $data[$tableName] === '') {
                return [false, $tableName, "Non nullable value is null or empty."];
            }
        }

        //Loop for checking yes/no enums.
        //COMM YES/NO, DIRECT Y/N, WALL INCLUDED.
        $yesNoEnumTables = ['COMM YES/NO', 'DIRECT Y/N', 'WALL INCLUDED'];
        $yesNoEnum = ['YES', 'NO'];
        //Memoize answer instead of running twice.
        list($valid, $errorCol, $errorMsg) = $this->enumValidation($data, $yesNoEnumTables, $yesNoEnum, 'Value not yes or no!');
        if(!$valid) {
            return [$valid, $errorCol, $errorMsg];
        }

        //Loop for checking X,N/A enums.
        //HEAD+BASE, HEAD+BASE + FRAME, COMPLETE
        $xNAEnumTables = ['HEAD+BASE', 'HEAD+BASE + FRAME', 'COMPLETE'];
        $xNAEnum = ['X', 'N/A'];
        //Memoize again.
        list($valid, $errorCol, $errorMsg) = $this->enumValidation($data, $xNAEnumTables, $xNAEnum, 'Value not X or N/A!');
        if(!$valid) {
            return [$valid, $errorCol, $errorMsg];
        }

        //Check if values are contained in NO, G, P enum.
        if(!in_array($data['PHOTO grnte/prpxs'], ['NO', 'G', 'P'])) {
            return [false, 'PHOTO grnte/prpxs', "Value does not match NO, G or P."];
        }

        //Check where delivery date is canceled.
        //Check for al spellings of canceled.
        if(strtoupper($data['DELIVERY DATE']) == 'CANCELED' || strtoupper($data['DELIVERY DATE']) == 'CANCELLED') {
            return [false, 'DELIVERY DATE', "Order is canceled."];
        }

        //Check if date value is N/A, this is just an empty date.
        if($data['DELIVERY DATE'] != 'N/A')
        {
            //Validate date format, if error is caught, date is not in a date format: like 18/0624 instead of 18/06/24.
            try {
                Carbon::createFromFormat('d/m/y', $data['DATE'])->format('Y-m-d');
            } 
            catch (\Exception $e) {
                return [false, 'DATE', "Date format incorrect."]; // Invalidate row if date format is incorrect
            }
            try {
                Carbon::createFromFormat('d/m/y', $data['DELIVERY DATE'])->format('Y-m-d');
            } 
            catch (\Exception $e) {
                return [false, 'DELIVERY DATE', "Date format incorrect."]; // Invalidate row if delivery date format is incorrect
            }
        }

        //If no validation has failed, pass the validation and return an empty error message and column.
        return [true, '', ''];
    }

    protected function saveValidRecord(array $data)
    {
        /////////////////////////////////////////
        //Insert values into sales table in db.//
        /////////////////////////////////////////

        //Correct date format from y-m-d to d/m/y.
        $date = Carbon::createFromFormat('d/m/y', $data['DATE'])->format('Y-m-d');
        if($data['DELIVERY DATE'] == 'N/A')
        {
            $deliveryDate = null;
        }
        else {
            $deliveryDate = Carbon::createFromFormat('d/m/y', $data['DELIVERY DATE'])->format('Y-m-d');
        }

        //echo $data['STORE'];
        //['STORE'] is an undefined array key. Please investigate.

        //Map all fields from CSV to their respective names used in the db table.
        $mappedData = [
            'store' => $data['STORE'],
            'store_code' => $data['STORE CODE'],
            'date' => $date,
            'order_num' => $data['ORDER #'],
            'receipt_num' => $data['RECEIPT#'],
            'receipt_count' => $data['AMOUNT OF RECEIPTS'],
            'balance_outstanding' => $data['BALANCE OUTSTANDING'],
            'delivery_date' => $deliveryDate,
            'commission_earned' => $data['COMM YES/NO'],
            'product_code' => $data['PRODUCT CODE'],
            'head_base_included' => $data['HEAD+BASE'],
            'head_base_frame_included' => $data['HEAD+BASE + FRAME'],
            'completion_status' => $data['COMPLETE'],
            'product_color' => $data['COLOR'],
            'direct' => $data['DIRECT Y/N'],
            'photo' => $data['PHOTO grnte/prpxs'],
            'wall_included' => $data['WALL INCLUDED'],
            'comments' => $data['COMMENTS']
        ];
        //Create db entry through Sale model.
        Sale::create($mappedData);
    }

    protected function saveInvalidRecord(array $data, string $errorColumn, string $errorMessage)
    {
        $mappedData = [
            'store' => $data['STORE'],
            'store_code' => $data['STORE CODE'],
            'date' => $data['DATE'],
            'order_num' => $data['ORDER #'],
            'receipt_num' => $data['RECEIPT#'],
            'receipt_count' => $data['AMOUNT OF RECEIPTS'],
            'balance_outstanding' => $data['BALANCE OUTSTANDING'],
            'delivery_date' => $data['DELIVERY DATE'],
            'commission_earned' => $data['COMM YES/NO'],
            'product_code' => $data['PRODUCT CODE'],
            'head_base_included' => $data['HEAD+BASE'],
            'head_base_frame_included' => $data['HEAD+BASE + FRAME'],
            'completion_status' => $data['COMPLETE'],
            'product_color' => $data['COLOR'],
            'direct' => $data['DIRECT Y/N'],
            'photo' => $data['PHOTO grnte/prpxs'],
            'wall_included' => $data['WALL INCLUDED'],
            'comments' => $data['COMMENTS'],
            'error_column' => $errorColumn,
            'error_message' => $errorMessage
        ];
        //Create db entry through InvalidSale model.
        InvalidSale::create($mappedData);
    }

    protected function enumValidation(array $data, array $tableNames, array $enumValues, string $errorMessage): array
    {
        foreach($tableNames as $tableName)
        {
            if(!in_array($data[$tableName], $enumValues)) {
                return [false, $tableName, $errorMessage];
            }
        }
        return [true,'', ''];
    }

    protected function cacheProgress($fileName, $currentRowCount, $totalRowCount, $validCount, $invalidCount): void
    {
        Cache::put("csv_progress_$fileName", [
            'current' => $currentRowCount,
            'total' => $totalRowCount,  
            'valid' => $validCount,
            'invalid' => $invalidCount,
        ], now()->addMinutes(5));
    }
}
