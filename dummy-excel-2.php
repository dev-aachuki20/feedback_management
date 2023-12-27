<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->getActiveSheet()->mergeCells('A1:A3', 'Survey Title');
$writer = new Xlsx($spreadsheet);
$writer->save('survey-question-excel/hello world.xlsx');

?>