<?php
    require('../../function/function.php');
    require('../../function/get_data_function.php');
    include('../../permission.php');
    require_once dirname(__DIR__,2).'/vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf();
    $path = array();
    include('report.php?export=csv');
    include('report-question.php?export=csv');
    include('report.php');
    include('report-question.php');
?>