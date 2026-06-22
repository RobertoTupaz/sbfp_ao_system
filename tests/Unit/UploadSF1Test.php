<?php

namespace Tests\Unit;

use App\Livewire\Dashboard\Pupils\UploadSF1;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class UploadSF1Test extends TestCase
{
    public function test_it_reads_measurements_and_flags_from_the_expected_sf1_columns(): void
    {
        $sheet = (new Spreadsheet)->getActiveSheet();
        $sheet->setCellValue('A7', '123456789012');
        $sheet->setCellValue('C7', 'Dela Cruz, Juan, Santos');
        $sheet->setCellValue('G7', 'Male');
        $sheet->setCellValue('H7', '2015-01-15');
        $sheet->setCellValue('N7', 'Yes');
        $sheet->setCellValue('AV7', 24.5);
        $sheet->setCellValue('AW7', 128.75);
        $sheet->setCellValue('AX7', 'Yes');
        $sheet->setCellValue('AY7', 'No');
        $sheet->setCellValue('AZ7', 'X');

        $method = new ReflectionMethod(UploadSF1::class, 'parseSf1Row');
        $row = $method->invoke(new UploadSF1, $sheet, 7, '5', 'Rizal');

        $this->assertSame(24.5, $row['weight']);
        $this->assertSame(128.75, $row['height']);
        $this->assertTrue($row['dewormed']);
        $this->assertFalse($row['_4ps']);
        $this->assertTrue($row['sbfp_previous_beneficiary']);
        $this->assertSame('m', $row['sex']);
    }
}
