<?php
require_once __DIR__ . '/vendor/autoload.php';
include('function/function.php');
include('function/get_data_function.php');
$mpdf = new \Mpdf\Mpdf();

$mpdf = new \Mpdf\Mpdf();
    $subject = 'Survey Response Submitted';
    $body = " ";
	$html = '<table width="100%" style="background-color:#dbdbdb;">
		<tr>
		<td><table align="center" width="690" border="">
			<tr>
				<td style="background-color:#fff; padding:4%;" width="94%">
				<table width="100%;">
				<tr>
				<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="'.getHomeUrl().'upload_image/dgs-logo.png" /></td>
				</tr>
				
				<tr>
				<td align="center"><h2> SURVEY RESPONSE CONTACT REQUEST</h2></td>
				</tr>
				<tr>
					<td></td>
				</tr>

				<tr>
					<td><p style="font-size:15px;margin:10px 0;">Hello Amit ,</p>
						<p style="font-size:15px;margin:10px 0;">A Survey Response has been submitted and the respondent has requested contact </p>
					</td>
				</tr>
				<tr>
					<td></td>
				</tr>
				<tr>
				<td><p style="font-size:15px;margin:10px 0;"><a style=" Green border: none;color: white;padding: 3px 18px;text-align: center;text-decoration: none;display: inline-block;margin: 4px 2px;  color:blue; cursor: pointer;" href="' . BASE_URL . 'index.php?page=view-contacted-list&type=survey" target="_blank">Click here </a> to view.</p></td>
				</tr>
				<tr>
				<td height="20px;">&nbsp;</td>
				</tr>
				<tr>
				<td style="color:#404040;font-size:18px;"><p style="margin:5px 0;">DGAM SYSTEM</p>
					<p style="font-weight:bold;margin:0;">Powered by Datagroup solutions</p></td>
				</tr>
				</table></td>
		</tr>
			<tr>
			<td colspan="3" align="center" style="padding:10px 0px;">
            <p><img width="100px" src="'.getHomeUrl().'upload_image/dgs-logo.png" /></p>
            <p style="color:#a3a3a3;">copyright ' . date('Y') . '  <strong>Data Group Solutions</strong> All Rights Reserved.</p></td>
			</tr>
			
			</table></td>
		</tr>
		</table>';
	$mpdf->WriteHTML($html);
	//$pdf = $mpdf->Output('', 'S');
$mpdf->Output();
?>