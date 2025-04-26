<?php

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

        //Remove BOM chars screwing with STORE header.
        $fileContent = file_get_contents($file);
        $fileContent = preg_replace('/^\xEF\xBB\xBF/', '', $fileContent);
        file_put_contents($file, $fileContent);

        //Error column and message variables:
        $errorColumn = '';
        $errorMessage = '';


        //Count the number of rows to determine progress of loading bar.
        $rowCount = 0;
        if (($handle = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rowCount++;
            }
            fclose($handle);
        }

        $validCount = 0;
        $invalidCount = 0;

        //Open for reading.
        if(($handle = fopen($file, 'r')) !== false) {
            $headers = null;

            //Parse each csv row as an array, check if there are rows to parse.
            while(($row = fgetcsv($handle, 1000, ',')) !== false) {
                //Set the first row to the header.
                if(!$headers) {
                    $headers = $row;
                    // echo "Headers: ";
                    // foreach ($headers as $header) {
                    //     //Log::info("[" . $header . "]\n");
                    // }
                    continue; //Skip tyo next row.
                }

                //Map header columns to values.
                $data = array_combine($headers, $row);

                // Call the function and destructure the returned array into variables
                list($isValid, $errorColumn, $errorMessage) = $this->isValidRow($data);

                //Validate the row.
                if($isValid) {
                    //Save to sales records.
                    $validCount++;
                    $this->saveValidRecord($data);                    
                } else {
                    //Save to invalid sales records.
                    $invalidCount++;
                    $this->saveInvalidRecord($data, $errorColumn, $errorMessage);                    
                }

                //Update progress tracking here.
                //Possibly use cache to save progress and animate progress bar.
            }

            //Log new csv file entry into database.
            $tableMap = [
                'csv_file_path' => $file,
                'valid_row_count' => $validCount,
                'invalid_row_count' => $invalidCount
            ];

            CsvProcessingJob::create($tableMap);

            //Close csv file.
            fclose($handle);
        }
    }

    protected function isValidRow(array $data)
    {
        //To track on what row validation failed.
        //$orderNum = $data['ORDER #'];
        //echo $orderNum;

        //All tables that can not be null or empty:
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
            if($data[$tableName] === null || $data[$tableName] == '') {
                return [false, $tableName, "Non nullable value is null or empty."];
            }
        }

        //Check where delivery date is Canceled or N/A.
        if(in_array($data['DELIVERY DATE'], ['CANCELED', 'N/A'])) {
            return [false, 'DELIVERY DATE', "Order is canceled or date is N/A."];
        }

        //Loop for checking yes/no enums.
        //COMM YES/NO, DIRECT Y/N, WALL INCLUDED.
        $yesNoEnumTables = ['COMM YES/NO', 'DIRECT Y/N', 'WALL INCLUDED'];
        foreach($yesNoEnumTables as $tableName)
        {
            if(!in_array($data[$tableName], ['YES', 'NO'])) {
                return [false, $tableName, "Value not yes or no!"];
            }
        }

        //Loop for checking X,N/A enums.
        //HEAD+BASE, HEAD+BASE + FRAME, COMPLETE
        $xNAEnumTables = ['HEAD+BASE', 'HEAD+BASE + FRAME', 'COMPLETE'];
        foreach($xNAEnumTables as $tableName) {
            if(!in_array($data[$tableName], ['X', 'N/A'])) {
                return [false, $tableName, "Value not X or N/A!"];
            }
        }

        //Check if values are contained in NO, G, P enum.
        if(!in_array($data['PHOTO grnte/prpxs'], ['NO', 'G', 'P'])) {
            return [false, 'PHOTO grnte/prpxs', "Value does not match NO, G or P."];
        }

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
            return [false, 'DELIVERYDATE', "Date format incorrect."]; // Invalidate row if delivery date format is incorrect
        }

        //If no validation has failed, pass the validation and return an empty error message and column.
        return [true, '', ''];
    }

    protected function saveValidRecord(array $data)
    {
        //Insert values into sales table in db.

        //Correct date format from y-m-d to d/m/y.
        $date = Carbon::createFromFormat('d/m/y', $data['DATE'])->format('Y-m-d');
        $deliveryDate = Carbon::createFromFormat('d/m/y', $data['DELIVERY DATE'])->format('Y-m-d');

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
}
