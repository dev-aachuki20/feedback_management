<?php
/**
 * Average Survey Question Score with multiple locations/groups/departments – frequency & time period is same in Run
*/

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$activeSheet = $spreadsheet->getActiveSheet();

// Merge cells A1 to C1 (you can change the range as needed)
$activeSheet->mergeCells('A1:C1');
$activeSheet->mergeCells('E1:G1');
$activeSheet->mergeCells('A2:C2');
$activeSheet->mergeCells('A4:I4');
$activeSheet->mergeCells('B5:C5');
$activeSheet->mergeCells('D5:E5');
$activeSheet->mergeCells('F5:G5');


// Set the text "Hello World" in the merged cell
$activeSheet->setCellValue('A1', 'Survey Title');

$activeSheet->setCellValue('E1', '20/12/2023 - 30/12/2023');

$activeSheet->setCellValue('A3', '');

$activeSheet->setCellValue('A3', 'STEP 1');

$activeSheet->setCellValue('A4', 'Question 1');

$activeSheet->setCellValue('A5', '');
$activeSheet->setCellValue('B5', 'LOCATION 1');
$activeSheet->setCellValue('D5', 'LOCATION 2');
$activeSheet->setCellValue('F5', 'LOCATION 3');

$activeSheet->setCellValue('A6', '');
$activeSheet->setCellValue('B6', 'RESULT');
$activeSheet->setCellValue('C6', 'RESPONSES');
$activeSheet->setCellValue('D6', 'RESULT');
$activeSheet->setCellValue('E6', 'RESPONSES');
$activeSheet->setCellValue('F6', 'RESULT');
$activeSheet->setCellValue('G6', 'RESPONSES');

$activeSheet->setCellValue('A7', 'ANSWER 1');
$activeSheet->setCellValue('B7', '50%');
$activeSheet->setCellValue('C7', '2');
$activeSheet->setCellValue('D7', '30%');
$activeSheet->setCellValue('E7', '5');
$activeSheet->setCellValue('F7', '20%');
$activeSheet->setCellValue('G7', '1');

$activeSheet->setCellValue('A8', 'ANSWER 2');
$activeSheet->setCellValue('B8', '50%');
$activeSheet->setCellValue('C8', '2');
$activeSheet->setCellValue('D8', '30%');
$activeSheet->setCellValue('E8', '5');
$activeSheet->setCellValue('F8', '20%');
$activeSheet->setCellValue('G8', '1');

$activeSheet->setCellValue('A9', 'ANSWER 3');
$activeSheet->setCellValue('B9', '50%');
$activeSheet->setCellValue('C9', '2');
$activeSheet->setCellValue('D9', '30%');
$activeSheet->setCellValue('E9', '5');
$activeSheet->setCellValue('F9', '20%');
$activeSheet->setCellValue('G9', '1');

$activeSheet->setCellValue('A10', 'ANSWER 4');
$activeSheet->setCellValue('B10', '50%');
$activeSheet->setCellValue('C10', '2');
$activeSheet->setCellValue('D10', '30%');
$activeSheet->setCellValue('E10', '5');
$activeSheet->setCellValue('F10', '20%');
$activeSheet->setCellValue('G10', '1');
// Style the text (optional)
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
$activeSheet->getStyle('B5')->applyFromArray($style);
$activeSheet->getStyle('D5')->applyFromArray($style);
$activeSheet->getStyle('F5')->applyFromArray($style);
$activeSheet->getStyle('B6')->applyFromArray($style);
$activeSheet->getStyle('C6')->applyFromArray($style);
$activeSheet->getStyle('D6')->applyFromArray($style);
$activeSheet->getStyle('E6')->applyFromArray($style);
$activeSheet->getStyle('F6')->applyFromArray($style);
$activeSheet->getStyle('G6')->applyFromArray($style);


// Save the Excel file

$writer = new Xlsx($spreadsheet);
$writer->save('excel/run-survey-multiple-location.xlsx');