<?php
require('../../function/function.php');
require('../../function/get_data_function.php');
include('../../permission.php');
require_once dirname(__DIR__, 2). '/vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf();

$html ='test';
$mpdf->WriteHTML($html);
$mpdf->Output($filename,'I');
?>