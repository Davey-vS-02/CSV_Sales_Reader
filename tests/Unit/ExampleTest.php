<?php

namespace Tests\Unit;

use App\Jobs\ProcessCsvJob;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_csv_validation_works(): void
    {
        //All inputs correct.
        $testCase1 = [
            'STORE' => 'LICHTENBURG',
            'STORE CODE' => 'LTX0000',
            'DATE' => '2005/01/24',
            'ORDER #' => '997',
            'RECEIPT#' => '2026',
            'AMOUNT OF RECEIPTS' => '1320',
            'BALANCE OUTSTANDING' => '2655',
            'DELIVERY DATE' => 'N/A',
            'COMM YES/NO' => 'YES',
            'PRODUCT CODE' => 'TRD006',
            'HEAD+BASE' => 'X',
            'HEAD+BASE + FRAME' => 'N/A',
            'COMPLETE' => 'N/A',
            'COLOR' => 'B',
            'DIRECT Y/N' => 'NO',
            'PHOTO grnte/prpxs' => 'NO',
            'WALL INCLUDED' => 'NO',
            'COMMENTS' => '',
        ];
        
        $testCase2 = [
            'STORE' => 'LICHTENBURG',
            'STORE CODE' => 'LTX0000',
            'DATE' => '18/0624', //Incorrect date here.
            'ORDER #' => '1121',
            'RECEIPT#' => '2303',
            'AMOUNT OF RECEIPTS' => '9000',
            'BALANCE OUTSTANDING' => '0',
            'DELIVERY DATE' => '04/09/24',
            'COMM YES/NO' => 'YES',
            'PRODUCT CODE' => 'TRB015',
            'HEAD+BASE' => 'X',
            'HEAD+BASE + FRAME' => 'N/A',
            'COMPLETE' => 'N/A',
            'COLOR' => 'B',
            'DIRECT Y/N' => 'NO',
            'PHOTO grnte/prpxs' => 'NO',
            'WALL INCLUDED' => 'NO',
            'COMMENTS' => '',
        ];

        $testCase3 = [
            'STORE' => 'LICHTENBURG',
            'STORE CODE' => null, //Null value.
            'DATE' => '2005/01/24',
            'ORDER #' => '997',
            'RECEIPT#' => '2026',
            'AMOUNT OF RECEIPTS' => '1320',
            'BALANCE OUTSTANDING' => '2655',
            'DELIVERY DATE' => 'N/A',
            'COMM YES/NO' => 'YES',
            'PRODUCT CODE' => 'TRD006',
            'HEAD+BASE' => 'X',
            'HEAD+BASE + FRAME' => 'N/A',
            'COMPLETE' => 'N/A',
            'COLOR' => 'B',
            'DIRECT Y/N' => 'NO',
            'PHOTO grnte/prpxs' => 'NO',
            'WALL INCLUDED' => 'NO',
            'COMMENTS' => '',
        ];

        $testCase4 = [
            'STORE' => 'LICHTENBURG',
            'STORE CODE' => 'LTX0000',
            'DATE' => '2005/01/24',
            'ORDER #' => '997',
            'RECEIPT#' => '2026',
            'AMOUNT OF RECEIPTS' => '1320',
            'BALANCE OUTSTANDING' => '2655',
            'DELIVERY DATE' => 'N/A',
            'COMM YES/NO' => 'X', //Should be YES or NO.
            'PRODUCT CODE' => 'TRD006',
            'HEAD+BASE' => 'X',
            'HEAD+BASE + FRAME' => 'N/A',
            'COMPLETE' => 'N/A',
            'COLOR' => 'B',
            'DIRECT Y/N' => 'NO',
            'PHOTO grnte/prpxs' => 'NO',
            'WALL INCLUDED' => 'NO',
            'COMMENTS' => '',
        ];

        $testCase5 = [
            'STORE' => 'LICHTENBURG',
            'STORE CODE' => 'LTX0000',
            'DATE' => '2005/01/24',
            'ORDER #' => '997',
            'RECEIPT#' => '2026',
            'AMOUNT OF RECEIPTS' => '1320',
            'BALANCE OUTSTANDING' => '2655',
            'DELIVERY DATE' => 'CaNcElLeD', //Spelled cancelled
            'COMM YES/NO' => 'YES',
            'PRODUCT CODE' => 'TRD006',
            'HEAD+BASE' => 'X',
            'HEAD+BASE + FRAME' => 'N/A',
            'COMPLETE' => 'N/A',
            'COLOR' => 'B',
            'DIRECT Y/N' => 'NO',
            'PHOTO grnte/prpxs' => 'NO',
            'WALL INCLUDED' => 'NO',
            'COMMENTS' => '',
        ];

        $testCase6 = [
            'STORE' => 'LICHTENBURG',
            'STORE CODE' => 'LTX0000',
            'DATE' => '2005/01/24',
            'ORDER #' => '997',
            'RECEIPT#' => '2026',
            'AMOUNT OF RECEIPTS' => '1320',
            'BALANCE OUTSTANDING' => '2655',
            'DELIVERY DATE' => 'CaNcElEd', //Spelled cancelled
            'COMM YES/NO' => 'YES',
            'PRODUCT CODE' => 'TRD006',
            'HEAD+BASE' => 'X',
            'HEAD+BASE + FRAME' => 'N/A',
            'COMPLETE' => 'N/A',
            'COLOR' => 'B',
            'DIRECT Y/N' => 'NO',
            'PHOTO grnte/prpxs' => 'NO',
            'WALL INCLUDED' => 'NO',
            'COMMENTS' => '',
        ];

        $job = new ProcessCsvJob('dummy/path.csv'); //Pass a dummy path.
        //All inputs valid.
        $this->assertTrue($job->isValidRow($testCase1)[0]);
        $this->assertEquals($job->isValidRow($testCase1), [true, '', '']);
        //DATE mistyped.
        $this->assertFalse($job->isValidRow($testCase2)[0]);
        $this->assertEquals($job->isValidRow($testCase2), [false, 'DATE', 'Date format incorrect.']);
        //STORE CODE is set to null.
        $this->assertFalse($job->isValidRow($testCase3)[0]);
        $this->assertEquals($job->isValidRow($testCase3), [false, 'STORE CODE', 'Non nullable value is null or empty.']);
        //Check enums.
        $this->assertFalse($job->isValidRow($testCase4)[0]);
        $this->assertEquals($job->isValidRow($testCase4), [false, 'COMM YES/NO', 'Value not yes or no!']);
        //Check where delivery date is canceled. Test different spellings and case.
        $this->assertFalse($job->isValidRow($testCase5)[0]);
        $this->assertEquals($job->isValidRow($testCase5), [false, 'DELIVERY DATE', 'Order is canceled.']);
        $this->assertFalse($job->isValidRow($testCase6)[0]);
        $this->assertEquals($job->isValidRow($testCase6), [false, 'DELIVERY DATE', 'Order is canceled.']);
        
        //More possible cases can follow, like testing for specific values like: NO CODE, under product code.
        //Shuld suffice for purpose of technical assessment.
    }
}
