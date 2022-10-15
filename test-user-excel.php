<?php
    date_default_timezone_set('Asia/Kolkata');
    // include PHPExcel library and set its path accordingly.
    require('./excel_library/Classes/PHPExcel.php');
    $objPHPExcel = new PHPExcel;
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
    $objSheet = $objPHPExcel->getActiveSheet();
    $objSheet->setTitle('My Form');
    $objSheet->getStyle('A1:E1')->getFont()->setBold(true)->setSize(12);
    $objSheet->getStyle('F1:F20000')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

    $objSheet->getCell('A1')->setValue('NAME');
    $objSheet->getCell('B1')->setValue('EMAIL');
    $objSheet->getCell('C1')->setValue('PHONE NUMBER');
    $objSheet->getCell('D1')->setValue('USER ROLE');
    
    $objSheet->getCell('A2')->setValue('Test User 1');
    $objSheet->getCell('B2')->setValue('testuser@gmail.com');
    $objSheet->getCell('C2')->setValue('9874563210');
    $objSheet->getCell('D2')->setValue('Super Admin');
 
    
    $objSheet->getCell('A3')->setValue('Test User 2');
    $objSheet->getCell('B3')->setValue('testuser2@gmail.com');
    $objSheet->getCell('C3')->setValue('9874560000');
    $objSheet->getCell('D3')->setValue('Admin');
    

    $objSheet->getColumnDimension('A')->setAutoSize(true);
    $objSheet->getColumnDimension('B')->setAutoSize(true);
    $objSheet->getProtection()->setPassword('password hare');
    $objSheet->getProtection()->setSheet(true);
    $objSheet->getStyle('A4:E2000')->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="sample-user.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter->save('php://output');
?>