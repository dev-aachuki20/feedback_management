<?php
require('../../function/function.php');
require('../../function/get_data_function.php');
include('../../permission.php');

$reportType = $_POST['sch_template_field_name'];
$documentType = $_POST['export_document'];

// for pdf 
if($documentType == 2){
    include ('./report-question-pdf.php');
}else{
    if($reportType == 'survey' || $reportType == 'pulse' || $reportType == 'engagement'){
        include ('./survey-question-overall-excel.php');
    }else{
        include ('./average-survey-question-multiple-gld-excel.php');
    }
}
?>