<?php
require('../../function/function.php');
require('../../function/get_data_function.php');

$reportType = $_POST['sch_template_field_name'];
$documentType = $_POST['export_document'];

// for pdf 
if ($documentType == 2) {
    if ($reportType == 'survey' || $reportType == 'pulse' || $reportType == 'engagement') {
        include('./report-question-pdf.php');
    } else if ($reportType == 'group' || $reportType == 'location' || $reportType == 'department') {
        if (isset($_POST['template_field'])) {
            if (is_array($_POST['template_field']) && count($_POST['template_field']) == 1) {
                include('./report-question-pdf.php');
            } else {
                include('./report-question-pdf-previous.php');
                // include ('./report-question-pdf-multiple-gld.php');
            }
        }
    }
} else { // for excel
    if ($reportType == 'survey' || $reportType == 'pulse' || $reportType == 'engagement') {
        include('./survey-question-overall-excel.php');
    } else {
        include('./average-survey-question-multiple-gld-excel.php');
    }
}
