<?php
/**
 * This is according to the time period if it is different to the frequency.
 */
require '../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$activeSheet = $spreadsheet->getActiveSheet();

// Merge cells A1 to C1 (you can change the range as needed)
$activeSheet->mergeCells('A1:C1');
$activeSheet->mergeCells('A2:C2');
$activeSheet->mergeCells('A5:I5');
$activeSheet->mergeCells('B6:C6');
$activeSheet->mergeCells('D6:E6');
$activeSheet->mergeCells('F6:G6');

// Set the text "Hello World" in the merged cell
$activeSheet->setCellValue('A1', 'Survey Title');

$activeSheet->setCellValue('A2', '20/12/2023 - 30/12/2023');

$activeSheet->setCellValue('A3', '');

$activeSheet->setCellValue('A4', 'STEP 1');

$activeSheet->setCellValue('A5', 'Question 1');

$activeSheet->setCellValue('A6', '');
$activeSheet->setCellValue('B6', '01/01/2024');
$activeSheet->setCellValue('D6', '01/04/2024');
$activeSheet->setCellValue('F6', '01/08/2024');

$activeSheet->setCellValue('A7', '');
$activeSheet->setCellValue('B7', 'RESULT');
$activeSheet->setCellValue('C7', 'RESPONSES');
$activeSheet->setCellValue('D7', 'RESULT');
$activeSheet->setCellValue('E7', 'RESPONSES');
$activeSheet->setCellValue('F7', 'RESULT');
$activeSheet->setCellValue('G7', 'RESPONSES');

$activeSheet->setCellValue('A8', 'ANSWER 1');
$activeSheet->setCellValue('B8', '50%');
$activeSheet->setCellValue('C8', '2');
$activeSheet->setCellValue('D8', '30%');
$activeSheet->setCellValue('E8', '5');
$activeSheet->setCellValue('F8', '20%');
$activeSheet->setCellValue('G8', '1');

$activeSheet->setCellValue('A9', 'ANSWER 2');
$activeSheet->setCellValue('B9', '50%');
$activeSheet->setCellValue('C9', '2');
$activeSheet->setCellValue('D9', '30%');
$activeSheet->setCellValue('E9', '5');
$activeSheet->setCellValue('F9', '20%');
$activeSheet->setCellValue('G9', '1');

$activeSheet->setCellValue('A10', 'ANSWER 3');
$activeSheet->setCellValue('B10', '50%');
$activeSheet->setCellValue('C10', '2');
$activeSheet->setCellValue('D10', '30%');
$activeSheet->setCellValue('E10', '5');
$activeSheet->setCellValue('F10', '20%');
$activeSheet->setCellValue('G10', '1');

$activeSheet->setCellValue('A11', 'ANSWER 4');
$activeSheet->setCellValue('B11', '50%');
$activeSheet->setCellValue('C11', '2');
$activeSheet->setCellValue('D11', '30%');
$activeSheet->setCellValue('E11', '5');
$activeSheet->setCellValue('F11', '20%');
$activeSheet->setCellValue('G11', '1');
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
$activeSheet->getStyle('A2')->applyFromArray($style2);
$activeSheet->getStyle('B6')->applyFromArray($alignCenter);
$activeSheet->getStyle('D6')->applyFromArray($alignCenter);
$activeSheet->getStyle('F6')->applyFromArray($alignCenter);
$activeSheet->getStyle('B7')->applyFromArray($style);
$activeSheet->getStyle('C7')->applyFromArray($style);
$activeSheet->getStyle('D7')->applyFromArray($style);
$activeSheet->getStyle('E7')->applyFromArray($style);
$activeSheet->getStyle('F7')->applyFromArray($style);
$activeSheet->getStyle('G7')->applyFromArray($style);

// Save the Excel file

$writer = new Xlsx($spreadsheet);
$writer->save('excel/TFD.xlsx');