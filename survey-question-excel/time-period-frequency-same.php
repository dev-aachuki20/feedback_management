<?php
/**
 * This is according to the time period if it is different to the same.
*/

require '../vendor/autoload.php'; // Assuming you have PhpSpreadsheet installed via Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$activeSheet = $spreadsheet->getActiveSheet();

// Merge cells A1 to C1 (you can change the range as needed)
$activeSheet->mergeCells('A1:C1');
$activeSheet->mergeCells('A2:C2');
$activeSheet->mergeCells('A5:I5');


// Set the text "Hello World" in the merged cell
$activeSheet->setCellValue('A1', 'Survey Title');

$activeSheet->setCellValue('A2', '20/12/2023 - 30/12/2023');

$activeSheet->setCellValue('A3', '');

$activeSheet->setCellValue('A4', 'STEP 1');

$activeSheet->setCellValue('A5', 'Question 1');

$activeSheet->setCellValue('A6', '');
$activeSheet->setCellValue('B6', 'RESULT');
$activeSheet->setCellValue('C6', 'RESPONSES');

$activeSheet->setCellValue('A7', 'ANSWER 1');
$activeSheet->setCellValue('B7', '50%');
$activeSheet->setCellValue('C7', '2');

$activeSheet->setCellValue('A8', 'ANSWER 2');
$activeSheet->setCellValue('B8', '50%');
$activeSheet->setCellValue('C8', '2');

$activeSheet->setCellValue('A9', 'ANSWER 3');
$activeSheet->setCellValue('B9', '50%');
$activeSheet->setCellValue('C9', '2');

$activeSheet->setCellValue('A10', 'ANSWER 4');
$activeSheet->setCellValue('B10', '50%');
$activeSheet->setCellValue('C10', '2');

$style = [
    'font' => [
        'bold' => true,
        'size' => 12,
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ],
];
$style2 = [
    'font' => [
        'size' => 12,
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ],
];
$alignCenter = [
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ],
];

$activeSheet->getStyle('A1')->applyFromArray($style);
$activeSheet->getStyle('A2')->applyFromArray($style2);
$activeSheet->getStyle('B6')->applyFromArray($style);
$activeSheet->getStyle('C6')->applyFromArray($style);


// Save the Excel file

$writer = new Xlsx($spreadsheet);
$writer->save('excel/TFS.xlsx');