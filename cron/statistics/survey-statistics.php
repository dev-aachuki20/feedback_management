<?php
require_once dirname(__FILE__, 3).'/dompdf/autoload.inc.php'; 
// Reference the Dompdf namespace 
use Dompdf\Dompdf; 
// Instantiate and use the dompdf class 
$message = file_get_contents ("http://localhost/feedback_management/cron/statistics/chart.php");
$dompdf = new Dompdf();
$dompdf->setPaper('A4', 'PORTRAIT'); 
$dompdf->loadHtml($message); 

// Render the HTML as PDF 
$dompdf->render(); 
$dompdf->stream('test', array("Attachment"=>0));