<?php
require('mysql_functions.php');
//Turn off error reporting
// error_reporting(0);
// error_reporting(E_ERROR | E_WARNING | E_PARSE);
// error_reporting(E_ALL);
// ini_set("error_reporting", E_ALL);
// error_reporting(E_ALL & ~E_NOTICE);
// php mailer start
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/vendor/autoload.php';

define('SMTP_HOST', "mail.dgfm.app");
define('SMTP_USER', "system@dgfm.app");
define('SMTP_PASS', "3yviSGa8I?Ib");
define('SMTP_PORT', "587");
define('SMTPAuth', true);   

// define('SMTP_HOST', "sandbox.smtp.mailtrap.io");
// define('SMTP_USER', "a4675565cb1dd9");
// define('SMTP_PASS', "4574e6f43e2c75");
// define('SMTP_PORT', "2525");
// define('SMTPAuth', true);
//mail trap

// end 
$msg = '';
$active_user_id = $_SESSION['admin_id'];
function pr($data)
{
	echo '<pre style="margin-left: 257px;">';
	print_r($data);
	echo "</pre>";
	exit;
}
// Make a safe SQL
$invoice_prefix = 'SPPL/2019-20/';
//define('DEFAULT_FROM_EMAIL', 'mail@datagroup.dev');
define('DEFAULT_FROM_EMAIL', 'system@dgfm.app');


function test_input($raw_data)
{
	foreach ($raw_data as $key => $value) {
		$value = trim($value);
		$value = stripslashes($value);
		$value = htmlentities($value, ENT_QUOTES);
		if (!is_numeric($value)) {
			$value = mysqli_real_escape_string(get_connection(), $value);
		}
		$filter_data[$key] = $value;
	}
	return $filter_data;
}

function sendEmailPdf($email_to, $user_name, $subject, $body, $pdf = null, $pdf_name = null)
{
	$mail = new PHPMailer(true);
	try {
		//Server settings
		$mail->SMTPDebug = 0;
		$mail->isSMTP();
		$mail->Host       = SMTP_HOST;
		$mail->SMTPAuth   = true;
		$mail->Username   = SMTP_USER;
		$mail->Password   = SMTP_PASS;
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Port       = SMTP_PORT;

		//Recipients
		$mail->setFrom(ADMIN_EMAIL, EMAIL_SEND_FROM);
		$mail->addAddress($email_to, $user_name);

		//Attachment
		if (!is_null($pdf)) {
			$mail->addStringAttachment($pdf, $pdf_name);
		}

		// Content
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->AltBody = strip_tags($body);
		$mail->send();
		// echo 'Message has been sent';
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: { $e->getMessage()}";
		return false;
	}
}
//Get Ip address
function ipAddress()
{
	$ipaddress = '';
	if ($_SERVER['HTTP_CLIENT_IP']) {
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	} else if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if ($_SERVER['HTTP_X_FORWARDED']) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	} else if ($_SERVER['HTTP_FORWARDED_FOR']) {
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	} else if ($_SERVER['HTTP_FORWARDED']) {
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	} else if ($_SERVER['REMOTE_ADDR']) {
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	} else {
		$ipaddress = 'UNKNOWN';
	}
	return $ipaddress;
}
//Redirect to page & message
function reDirect($path)
{
	echo '<script>window.open("' . $path . '","_top");</script>';
}
//Redirect to page & message
function giveAlert($message)
{
	echo '<script>alert("' . $message . '");</script>';
}

function alert($message)
{
	echo '<script>alert("' . $message . '");</script>';
}

//SET SUCCESS NOTICES
function set_msg($msga)
{
	$_SESSION['msg'] = '';
	$_SESSION['msg'] = $msga;
}

function table_exist($table)
{
	$connection = get_connection();
	//$qry =  mysqli_query($connection,"select * from client_management where status=1 order by name" );
	$sql = "show tables like '" . $table . "'";
	$res = $connection->query($sql);
	return ($res->num_rows > 0);
}

function column_exist($table, $column)
{
	$connection = get_connection();
	//$qry =  mysqli_query($connection,"select * from client_management where status=1 order by name" );
	$sql = "SHOW COLUMNS FROM " . $table . " LIKE '" . $column . "'";
	$res = $connection->query($sql);
	return ($res->num_rows > 0);
}

// CREATE TABLE 
function table_create($table, $LIKE)
{
	$connection = get_connection();
	$sql_table = "SHOW TABLES LIKE '" . $table . "'";
	if (mysqli_num_rows(mysqli_query($connection, $sql_table)) == 1) {
		$table_exist = 1;
	} else {
		$table_exist = 0;
	}
	if ($table_exist == 0) {
		// table create
		$sql_CREATE = "create TABLE " . $table . " like " . $LIKE . "";
		$qry =  mysqli_query($connection, $sql_CREATE);
	}
}

//DISPLAY SUCCESS MESSAGE
function display_msg($msg = '')
{
	if (!empty($_SESSION['msg'])) {
		echo "<span class='notices' style='color:#C00'>$_SESSION[msg]</span>";
		unset($_SESSION['msg']);
	} else if ($msg) {
		echo "<span class='notices' style='color:#C00'>$msg</span>";
	}
}

//Generate Password
function random_code($length)
{
	$characters = array(
		"A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M",
		"N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
		"1", "2", "3", "4", "5", "6", "7", "8", "9"
	);
	if ($length < 0 || $length > count($characters)) return null;
	shuffle($characters);
	return implode("", array_slice($characters, 0, $length));
}

function generateRandomString($length)
{
	$chars = array_merge(range('a', 'z'), range(0, 9));
	shuffle($chars);
	return implode(array_slice($chars, 0, $length));
}

function generateRandomNumber($length)
{
	$chars = array_merge(range('0', '9'), range(0, 9));
	shuffle($chars);
	return implode(array_slice($chars, 0, $length));
}

function upload_image($folder_path, $image_id)
{
	$file_name = $_FILES["image"]["name"];
	$extension = end(explode(".", $file_name));
	$file_name = $image_id . "." . $extension;
	$path = $folder_path . '/' . $file_name;
	if (file_exists($path)) {
		unlink($path);
	}
	$moved = move_uploaded_file($_FILES['image']['tmp_name'], $path);
	if ($moved) {
		return $file_name;
	}
}

function upload_multiple_image($folder_path, $image_id, $j)
{
	$file_name = $_FILES["image"]["name"]["$j"];
	$extension = end(explode(".", $file_name));
	//echo $j;
	$file_name = $image_id . "." . $extension;
	//$path=$file_name;
	$path = $folder_path . '/' . $file_name;
	if (file_exists($path)) {
		unlink($path);
	}
	//echo $path;
	//	var_dump($_FILES['image']['tmp_name']);
	//["$j"]
	$moved = move_uploaded_file($_FILES['image']['tmp_name']["$j"], $path);
	//echo $moved;
	//die;
	if ($moved) {
		return $file_name;
	}
}

function check_login()
{
	if (empty($_SESSION['user_id'])) {
		$_SESSION['REQUESTED_URI'] = $_SERVER['REQUEST_URI'];
		reDirect("login.php");
	}
}

function forgot_password($user_email, $password)
{
	$from = ADMIN_EMAIL;
	$from_name = EMAIL_SEND_FROM;
	$to = $user_email;
	$subject = "Password Recovery Email";
	$body = "Dear Admin,<br><br>Your login details :<br> Email ID : $user_email <br> password : $password";
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
// 	$headers .= 'From: <' . $from . '>' . "\r\n";
	$headers .= 'From: ' . $from_name . ' <' . $from . '>' . "\r\n"; // Include both name and email
	return mail($to, $subject, $body, $headers);
}

function send_survey_email($recipients, $survey_name, $surveyid, $to_be_contacted, $to_be_contacted_mail, $contact, $cby)
{
	$from = ADMIN_EMAIL;
	$link = $_SERVER['HTTP_HOST'] . '/export-pdf.php?surveyid=' . $surveyid;
	//$to = $user_email;
	$to = $to_be_contacted_mail;
	$allContactDetail = json_decode($contact, true);
	if ($to_be_contacted == 1) {
		$to_be_contacted = 'YES';
		$to_be_contacted_link = "&contacted=1";
		$to_be_contacted_text = "<br><br>Here is detail for contact <br><br> Name : " . $allContactDetail['first_name'] . ' ' . $allContactDetail['last_name'] . '<br> Email : ' . $allContactDetail['to_be_contact_mail'] . '<br> Phone Number : ' . $allContactDetail['phone_number'];
		// $to_be_contacted_text = "<br><br>Here is email for contact ".$to_be_contacted_mail;
	} else {
		$to_be_contacted = 'NO';
		$to_be_contacted_link = "";
		$to_be_contacted_text = "";
	}
	$user_link = $_SERVER['HTTP_HOST'] . '/survey-result.php?surveyid=' . $surveyid . '&userid=' . $cby . $to_be_contacted_link;
	$export_link = $_SERVER['HTTP_HOST'] . '/export-feedback.php?surveyid=' . $surveyid . '&userid=' . $cby . '&aid=-2&avl=10';
	//Entry Count
	$res = getaxecuteQuery_fn("SELECT id as total_entry from answers where surveyid=9 group by cby");
	$entry_number = mysqli_num_rows($res);
	$subject = $survey_name . " Entry " . $entry_number . " Contact " . $to_be_contacted;
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: Survey Entry Alert<' . $from . '>' . "\r\n";
	foreach ($recipients as $recipient) {
		$body = "Dear " . $recipient['name'] . ",<br><br>A new entry for survey ($survey_name) has been added, '" . $to_be_contacted_text . "'<br><br><a href='" . $export_link . "' download='test.xls' target='_blank'>Open new tab to export csv</a><br><br><a href='" . $user_link . "'>Click here to view this user entry</a><br><br> Thanks";
		$success = mail($recipient['email'], $subject, $body, $headers);
	}
}

function sendNotificationThreshold($surveyId, $data)
{
	$getSurveyDetails = getaxecuteQuery_fn("SELECT * from surveys where id=$surveyId");
	$row_get_survey_details = mysqli_fetch_assoc($getSurveyDetails);
	$thresholdPercentage = $row_get_survey_details['select_percentage'];
	$thresholdUsers = $row_get_survey_details['notification_threshold_users'];
	$type = 'survey';
	if ($row_get_survey_details['survey_type'] == 2) {
		$type = 'pulse';
	} else if ($row_get_survey_details['survey_type'] == 3) {
		$type = 'engagement';
	}
	$res = getaxecuteQuery_fn("SELECT id from questions where surveyid=$surveyId and is_weighted =1 and `answer_type` NOT IN (2,3,5)");
	$answer_value = array();
	while ($row_get_admin_id = mysqli_fetch_assoc($res)) {
		$question_id[] = $row_get_admin_id['id'];
		$answerData = $data['answerid'][$row_get_admin_id['id']];
		$explodeAnswerData = explode('--', $answerData);
		$answerId = $explodeAnswerData[0];
		$answerQuery = getaxecuteQuery_fn("SELECT * from questions_detail where id=$answerId");
		$row_get_answer_value = mysqli_fetch_assoc($answerQuery);
		$answer_value[] = $row_get_answer_value['answer'];
	}
	$totalScore = array_sum($answer_value);
	$result = $totalScore / count($answer_value);

	$response = round($result, 2);

	## send Threshold Notification mail
	$subject = $row_get_survey_details['name'] . ' - Threshold Notification';

	if ($response < $thresholdPercentage) {
		$users = explode(',', $thresholdUsers);
		foreach ($users as $user) {
			$getUser = getaxecuteQuery_fn("SELECT name, email from manage_users where id=$user");
			$row_get_user = mysqli_fetch_assoc($getUser);
			$uname = $row_get_user['name'];
			$email_to = $row_get_user['email'];

			$body = '<table width="100%" style="background-color:#dbdbdb;">
                	<tr>
                	<td>
                	<table align="center" width="690" border="">
                		<tr>
                			<td style="background-color:#fff;" width="94%">
                			<table width="100%;">
                			<tr>
                			<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/dgs-logo.png" /></td>
                			</tr>
                			<tr> <td height="20px;">&nbsp;</td> </tr>
                			<tr>
                			<td align="center"><h2> SURVEY RESPONSE</h2></td>
                			</tr>
                			<tr> <td height="20px;">&nbsp;</td> </tr>
                
                			<tr>
                				<td><p style="font-size:15px;margin:10px;">Hello ' . $uname . '</p> <br>
                					<p style="font-size:15px;margin:10px;">A Survey Response has been submitted for ' . $row_get_survey_details['name'] . ' and the score is ' . $response . '% which is below the preset notification threshold of ' . $thresholdPercentage . '%</p>
                				</td>
                			</tr>
                			<tr>
                				<td></td>
                			</tr>
                			<tr>
                			<td><p style="font-size:15px;margin:10px;"><a style=" Green border: none;color: white;padding: 2px 2px;text-align: center;text-decoration: none;display: inline-block;margin: 4px 2px;  color:blue; cursor: pointer;" href="' . BASE_URL . 'index.php?page=view-report&type=' . $type . '&response=' . $_SESSION['maxid'] . '" target="_blank">Click here</a> to view the response.</p></td>
                			</tr>
                			<tr>
                			<td height="20px;">&nbsp;</td>
                			</tr>
							<tr>
							<td height="20px;"><p style="font-size:15px;margin:10px;">DGFM System</p></td>
							</tr>
                			</table>
                		</td>
                	</tr>
                		<tr>
                		<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/Data-Group-footer.png" />
                		<p style="color:#a3a3a3;">Copyright ' . date('Y') . '  <strong>Data Group Solutions</strong> All rights reserved.</p>
                		</td>
                		</tr>
                		</table></td>
                	</tr>
                	</table>';

			send_mail($email_to, $subject, $body);
		}
	}
}

function send_survey_completed_email($recipients, $survey_name, $surveyid, $to_be_contacted)
{

	$send_to = $recipients['email'];
	$user_name = $recipients['name'];
	$from_mail = ADMIN_EMAIL;
	$sendFrom = EMAIL_SEND_FROM;
	$message = 'Hello ' . $user_name . ' here is the full servey pdf';
	$attachments = $recipients['attachments'];

	$getSurveyDetails = getaxecuteQuery_fn("SELECT * from surveys where id=$surveyid");
	$row_get_survey_details = mysqli_fetch_assoc($getSurveyDetails);
	$type = 'survey';
	if ($row_get_survey_details['survey_type'] == 2) {
		$type = 'pulse';
	} else if ($row_get_survey_details['survey_type'] == 3) {
		$type = 'engagement';
	}

	## send Threshold Notification mail
	$subject = $row_get_survey_details['name'] . ' - New Response';
	$body = '<table width="100%" style="background-color:#dbdbdb;">
	<tr>
	<td>
	<table align="center" width="690" border="">
		<tr>
			<td style="background-color:#fff;" width="94%">
			<table width="100%;">
			<tr>
			<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/dgs-logo.png" /></td>
			</tr>
			<tr> <td height="20px;">&nbsp;</td> </tr>
			<tr>
			<td align="center"><h2> SURVEY RESPONSE</h2></td>
			</tr>
			<tr> <td height="20px;">&nbsp;</td> </tr>';

	$body .= '<tr>
				<td><p style="font-size:15px;margin:10px;">Hello ' . $recipients['name'] . '</p> <br>
					<p style="font-size:15px;margin:10px;">A new response has been submitted for ' . $survey_name . '.';
	if ($to_be_contacted == 1) {
		$body .= 'The respondent has requested contact.';
	}
	$body .= '</p>
				</td>
			</tr>
			<tr>
				<td></td>
			</tr>
			<tr>
			<td><p style="font-size:15px;margin:6px;"><a style=" Green border: none;color: white;padding: 2px 2px;text-align: center;text-decoration: none;display: inline-block;margin: 4px 2px;  color:blue; cursor: pointer;" href="' . BASE_URL . 'index.php?page=view-report&type=' . $type . '" target="_blank">Click here</a> to view the response.</p></td>
			</tr>
			<tr>
			<td height="20px;"><p style="font-size:15px;margin:10px;">DGFM System</p></td>
			</tr>
			<tr>
			<td height="20px;">&nbsp;</td>
			</tr>
			</table>
		</td>
	</tr>
		<tr>
		<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/Data-Group-footer.png" />
		<p style="color:#a3a3a3;">Copyright ' . date('Y') . '  <strong>Data Group Solutions</strong> All rights reserved.</p>
		</td>
		</tr>
		</table></td>
	</tr>
	</table>';

	// $attachments = array('file.pdf');
	// Send the email with optional PDF attachment
	send_survey_pdf_with_email($attachments, $send_to, $from_mail, $sendFrom, $subject, $message, $body);

	// send_mail($send_to, $subject, $body);
}

function send_mail($send_to, $subject, $body)
{
	$from = ADMIN_EMAIL;
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: Survey Entry Alert<' . $from . '>' . "\r\n";
	$success = mail($send_to, $subject, $body, $headers);
}


function upload_image1($folder_path, $file_name, $file_tempname, $image_id)
{
	//$file_name=$_FILES["image"]["name"];
	//$file_name=$_FILES["image"]["name"];
	$extension = end(explode(".", $file_name));
	$file_name = $image_id . "." . $extension;
	$path = $folder_path . '/' . $file_name;
	if (file_exists($path)) {
		unlink($path);
	}
	$moved = move_uploaded_file($file_tempname, $path);
	if ($moved) {
		return $file_name;
	}
}

function curPageURL()
{
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

function getDayNumber($date)
{
	$dayNAme = date("l", strtotime($date));
	if ($dayNAme == 'Monday') {
		return 1;
	} else if ($dayNAme == 'Tuesday') {
		return 2;
	} else if ($dayNAme == 'Wednesday') {
		return 3;
	} else if ($dayNAme == 'Thursday') {
		return 4;
	} else if ($dayNAme == 'Friday') {
		return 5;
	} else if ($dayNAme == 'Saturday') {
		return 6;
	} else if ($dayNAme == 'Sunday') {
		return 7;
	}
}

function getDatesFromRange($start, $end, $format = 'd-m-Y')
{
	$array = array();
	$interval = new DateInterval('P1D');
	$realEnd = new DateTime($end);
	$realEnd->add($interval);
	$period = new DatePeriod(new DateTime($start), $interval, $realEnd);
	foreach ($period as $date) {
		$array[] = $date->format($format);
	}
	return $array;
}

function insert_log($logtype, $logtable, $lognote, $logrefid, $logstatus, $logby, $usertype)
{
	//if($action=='manage_activity_log'){
	$insert_array = array(
		'log_type' => $logtype,
		'log_table' => $logtable,
		'log_note' => $lognote,
		'log_ref_id' => $logrefid,
		'user_type' => $usertype,
		'status' => $logstatus,
		'cby' => $logby,
		'cdate' => date("Y-m-d H:i:s"),
		'cip' => ipAddress()
	);
	$update = dbRowInsert('manage_activity_log', $insert_array);
	//}   
}

function displayPagination($per_page, $page, $page_url, $total)
{
	$adjacents = "1";
	$page = ($page == 0 ? 1 : $page);
	$start = ($page - 1) * $per_page;
	$prev = $page - 1;
	$next = $page + 1;
	$setLastpage = ceil($total / $per_page);
	$lpm1 = $setLastpage - 1;
	$setPaginate = "";
	if ($setLastpage > 1) {
		//$setPaginate .= "<span>Showing Page $page of $setLastpage</span>"; 
		$setPaginate .= "<ul class='pagination'>";
		if ($setLastpage < 7 + ($adjacents * 2)) {
			for ($counter = 1; $counter <= $setLastpage; $counter++) {
				if ($counter == $page)
					$setPaginate .= "<li class='active'><a>$counter</a></li>";
				else
					$setPaginate .= "<li><a href='{$page_url}p=$counter'>$counter</a></li>";
			}
		} else if ($setLastpage > 5 + ($adjacents * 2)) {
			if ($page < 1 + ($adjacents * 2)) {
				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
					if ($counter == $page)
						$setPaginate .= "<li class='active' ><a>$counter</a></li>";
					else
						$setPaginate .= "<li><a href='{$page_url}p=$counter'>$counter</a></li>";
				}
				//  $setPaginate.= "<li class='dot'>...</li>";
				$setPaginate .= "<li><a href='{$page_url}p=$lpm1'>$lpm1</a></li>";
				$setPaginate .= "<li><a href='{$page_url}p=$setLastpage'>$setLastpage</a></li>";
			} else if ($setLastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
				$setPaginate .= "<li><a href='{$page_url}'>1</a></li>";
				$setPaginate .= "<li><a href='{$page_url}'>2</a></li>";
				//   $setPaginate.= "<li class='dot'>...</li>";
				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
					if ($counter == $page)
						$setPaginate .= "<li class='active'><a>$counter</a></li>";
					else
						$setPaginate .= "<li><a href='{$page_url}p=$counter'>$counter</a></li>";
				}
				//  $setPaginate.= "<li class='dot'>..</li>";
				$setPaginate .= "<li><a href='{$page_url}p=$lpm1'>$lpm1</a></li>";
				$setPaginate .= "<li><a href='{$page_url}p=$setLastpage'>$setLastpage</a></li>";
			} else {
				$setPaginate .= "<li><a href='{$page_url}p=1'>1</a></li>";
				$setPaginate .= "<li><a href='{$page_url}p=2'>2</a></li>";
				//  $setPaginate.= "<li class='dot'>..</li>";
				for ($counter = $setLastpage - (2 + ($adjacents * 2)); $counter <= $setLastpage; $counter++) {
					if ($counter == $page)
						$setPaginate .= "<li class='active'><a>$counter</a></li>";
					else
						$setPaginate .= "<li><a href='{$page_url}p=$counter'>$counter</a></li>";
				}
			}
		}
		if ($page < $counter - 1) {
			$setPaginate .= "<li><a href='{$page_url}p=$next'>Next</a></li>";
			$setPaginate .= "<li><a href='{$page_url}p=$setLastpage'>Last</a></li>";
		} else {
			$setPaginate .= "<li class='active'><a>Next</a></li>";
			$setPaginate .= "<li class='active'><a>Last</a></li>";
		}
		$setPaginate .= "</ul>\n";
	}
	return $setPaginate;
}

function status()
{
	return array(
		"1" => "Active",
		"2" => "Inactive"
	);
}

function status_data($status)
{
	$status_id = status();
	return $status_id[$status];
}

function required()
{
	return array(
		"1" => "Required",
		"2" => "Not Required"
	);
}

function required_name($status)
{
	$status_id = required();
	return $status_id[$status];
}

function question_type()
{
	return array(
		"" => "Select Type",
		"1" => "Radio Button",
		"2" => "Text Box",
		"3" => "Text Area",
		"4" => "Rating",
		"5" => "Title",
		"6" => "Drop Down",
		//"5" => "Yes/No"
	);
}

function answer_type()
{
	return array(
		"" => "Select Type",
		"1" => "Emoticons",
		"2" => "Star rating",
		"3" => "Number rating",
		"4" => "Tick/Cross",
	);
}

function question_type_name($status)
{
	$status_id = question_type();
	return $status_id[$status];
}

function survey_result_graph_colors()
{
	return array(
		"1" => "0f477b",
		"2" => "19a094",
		"3" => "386793",
		"4" => "0761b4",
		"5" => "07bcac",
		"6" => "1065b4",
		"7" => "000000",
		"8" => "CC0000",
		"9" => "097054",
		"10" => "FFDE00",
		"11" => "6599FF",
		"12" => "FF9900"
	);
}

function survey_result_graph_colors_name($status)
{
	$status_id = survey_result_graph_colors();
	return $status_id[$status];
}
function ordinal($number)
{
	$ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
	if ((($number % 100) >= 11) && (($number % 100) <= 13))
		return $number . 'th';
	else
		return $number . $ends[$number % 10];
}

function boostrap_bg_colors()
{
	return array(
		"1" => "bg-dark",
		"2" => "bg-",
		"3" => "bg-dark",
		"4" => "bg-defaul",
		"5" => "bg-dark",
		"6" => "bg-success",
		"7" => "bg-dark",
		"8" => "bg-info",
		"9" => "bg-dark",
		"10" => "bg-warning",
		"11" => "bg-dark",
		"12" => "bg-danger",
		"13" => "bg-dark",
		"14" => "bg-dark",
		"15" => "bg-dark",
		"16" => "bg-",
		"17" => "bg-dark",
		"18" => "bg-success",
		"19" => "bg-dark",
		"20" => "bg-info",
		"21" => "bg-dark",
		"22" => "bg-warning",
		"23" => "bg-dark",
		"24" => "bg-danger"
	);
}

function send_email_to_users($name, $email, $enc_id)
{
	$from = ADMIN_EMAIL;
	//$link = $_SERVER['HTTP_HOST'].'/verify_email.php?id='.$enc_id;
	$to = $email;
	$subject = "Registration Successful";
	$body = "Dear Users,
    <br><br>
    A new survey has been added at Private Ambulance Service.
      <br><br>
    Thank you !!
      <br>
    ";
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: <' . $from . '>' . "\r\n";
	$success = mail($to, $subject, $body, $headers);
	if (!$success) {
		echo  $errorMessage = error_get_last()['message'];
	} else {
		$msg = 'Message sent successfully !!';
	}
}

function send_email_to_assign_user($name, $email, $type = 'assign')
{
	$from = ADMIN_EMAIL;
	//$link = $_SERVER['HTTP_HOST'].'/verify_email.php?id='.$enc_id;
	$to = $email;
	if ($type = 'completed') {
		$subject = "Task Completed";
		$body = "Dear $name, <br><br>
		" . $_SESSION['user_name'] . " has changed the task status to RESOLVED-NEGATIVE <br><br>
		Thank you !!<br>";
	} else {
		$subject = "Task Assigned";
		$body = "Dear $name,
		<br><br>
		A new task has been assign to you.
		<br><br>
		Thank you !!<br>";
	}
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: <' . $from . '>' . "\r\n";
	$success = mail($to, $subject, $body, $headers);
	if (!$success) {
		echo  $errorMessage = error_get_last()['message'];
	} else {
		$msg = 'Message sent successfully !!';
	}
}

function survey_result_submitted_pdf_mail($email_to, $user_name)
{
	$mpdf = new \Mpdf\Mpdf();
	$pdf_name = 'survey-result' . date("Y-m-d-H-i-s") . ".pdf";
	$subject = 'Survey Response Submitted';
	$body = " ";
	$html = '<table width="100%" style="background-color:#dbdbdb;">
		<tr>
		<td><table align="center" width="690" border="">
			<tr>
				<td style="background-color:#fff;" width="94%">
				<table width="100%;">
				<tr>
				<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/dgs-logo.png" /></td>
				</tr>
				<tr> <td height="20px;">&nbsp;</td> </tr>
				<tr>
				<td align="center"><h2> SURVEY RESPONSE CONTACT REQUEST</h2></td>
				</tr>
				<tr> <td height="20px;">&nbsp;</td> </tr>

				<tr>
					<td><p style="font-size:15px;margin:10px 0;">Hello ' . $user_name . '</p> <br>
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
				</table></td>
		</tr>
            <tr>
            <td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/Data-Group-footer.png" />
            <p style="color:#a3a3a3;">Copyright ' . date('Y') . '  <strong>Data Group Solutions</strong> All rights reserved.</p>
            </td>
            </tr>
			</table></td>
		</tr>
		</table>';
	// 	$mpdf->WriteHTML($html);
	// 	$pdf = $mpdf->Output('', 'S');
	sendEmailPdf($email_to, $user_name, $subject, $html);
}

function get_boostrap_bg_colors($status)
{
	$status_id = boostrap_bg_colors();
	return $status_id[$status];
}

function smile_format($ans_count)
{
	if ($ans_count == 3) {
		return array(
			"0" => "dist/img/3-1.png",
			"1" => "dist/img/3-2.png",
			"2" => "dist/img/3-3.png"
		);
	} else if ($ans_count == 5) {
		return array(
			"0" => "dist/img/5-5.png",
			"1" => "dist/img/5-4.png",
			"2" => "dist/img/5-3.png",
			"3" => "dist/img/5-2.png",
			"4" => "dist/img/5-1.png"
		);
	} else if ($ans_count == 11) {
		return array(
			"0" => "dist/img/10-0.png",
			"1" => "dist/img/10-1.png",
			"2" => "dist/img/10-2.png",
			"3" => "dist/img/10-3.png",
			"4" => "dist/img/10-4.png",
			"5" => "dist/img/10-5.png",
			"6" => "dist/img/10-6.png",
			"7" => "dist/img/10-7.png",
			"8" => "dist/img/10-8.png",
			"9" => "dist/img/10-9.png",
			"10" => "dist/img/10-10.png"
		);
	} else {
		return array();
	}
}

function smile_format_icon($status, $ans_count)
{
	$status_id = smile_format($ans_count);
	return $status_id[$status];
}

function date_formate($type)
{
	$date_type = date("d-m-Y g:i", strtotime($type));
	return $date_type;
}

function date_formate_ymd($string)
{
	$string = date("Y-m-d", strtotime($string));
	return $string;
}

function date_formate_month($string)
{
	$string = date("M Y", strtotime($string));
	return $string;
}

function date_month_qry($string)
{
	$string = date("Y-m", strtotime($string));
	return $string;
}

function date_formate_cdate($string)
{
	$string = date("Y-m-d H:i:s", strtotime($string));
	return $string;
}

function user_type()
{
	return array(
		'1' => 'DGS',
		'2' => 'Super Admin',
		'3' => 'Admin',
		'4' => 'Manager',
	);
}

function survey_type()
{
	return array(
		'1' => 'Survey',
		'2' => 'Pulse',
		'3' => 'Engagement',
	);
}

function make_sidebar_active($page, $array)
{
	if (is_array($array)) {
		if (in_array($page, $array)) {
			return 'active';
		}
	} else {
		if ($page == $array) {
			return 'active';
		}
	}
}

function generate_unique_color($n)
{
	$rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
	$color_array = array();
	for ($i = 0; $i < $n; $i++) {
		$color = '#' . $rand[rand(0, 15)] . $rand[rand(0, 15)] . $rand[rand(0, 15)] . $rand[rand(0, 15)] . $rand[rand(0, 15)] . $rand[rand(0, 15)];
		$color_array[] = $color;
	}
	return $color_array;
}

//get randow string
function getName($n = 8)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $n; $i++) {
		$index = rand(0, strlen($characters) - 1);
		$randomString .= $characters[$index];
	}
	return $randomString;
}

function service_type()
{
	return array(
		'' => '-- Interval --', // Added by manisha
		// '12'   => '2 times per day',
		'24'   => 'Daily',
		'168'  => 'Weekly',
		'336'  => 'Fortnightly',
		'720'  => 'Monthly',
		'2160' => 'Quarterly',
		'4320' => '6 Monthly',
		'8640' => 'Annually'

	);
}

function assign_task_status()
{
	return array(
		"1" => "UNASSIGNED",
		"2" => "ASSIGNED",
		"3" => "IN PROGRESS",
		"4" => "VOID",
		"5" => "RESOLVED-POSITIVE",
		"6" => "RESOLVED-NEGATIVE",
	);
}

function upload_excel()
{
	set_include_path(get_include_path() . PATH_SEPARATOR . 'excel_library/Classes/');
	include 'PHPExcel/IOFactory.php';
	$f = 0;
	$file_name = $_FILES['tfile']['name'];
	$file_tempname = $_FILES['tfile']['tmp_name'];
	$folder = "import_file/users/";
	$image_id = date("Y-m-d-H-i-s");
	$result = upload_image1($folder, $file_name, $file_tempname, $image_id);
	if (!empty($result)) {
		//$result = $row_getuserdata['photo'];	
		$mess = 'Uploaded successfully';
	} else {
		//unlink("upload_image/".$row_getuserdata['photo']);
		$mess = 'Please try again.';
	}
	if ($mess == "Uploaded successfully") {
		ini_set('memory_limit', '528M');
		$inputFileName = "import_file/users/" . $result;
		echo 'Loading file ', pathinfo($inputFileName, PATHINFO_BASENAME), ' using IOFactory to identify the format<br />';
		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

		echo '<hr />';

		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
		$myArray = json_decode(json_encode($sheetData), true);
		$i = 0;
		$j = 0;
		$e = 0;
		foreach ($myArray as $arr) {
			$rnd = rand(1000, 99999);
			$e++;
			if ($e < 4) {
				continue;
			}
			if ($arr['A'] == '' or $arr['B'] == '' or $arr['C'] == '') {
				continue;
			}
			$email = $arr['B'];
			// if user type exist
			$user_type =  strtolower(trim($arr['D']));
			if ($user_type == 'super admin') {
				$uType = 2;
			} else if ($user_type == 'admin') {
				$uType = 3;
			} else if ($user_type == 'manager') {
				$uType = 4;
			}
			// only import super admin manager and admin
			if ($uType == 2 or $uType == 3 or $uType == 4) {
				$user_data = array(
					"name"           => $arr['A'],
					"email"          => $email,
					"phone"          => $arr['C'],
					"user_type"      => $uType,
					"cstatus"   	 => 2,
					"activation_key" => $rnd,
					"cip"            => ipAddress(),
					"cby"            => $_SESSION["user_id"],
					"cdate"          => date("Y-m-d H:i:s")
				);
				$res = getaxecuteQuery_fn("select email from manage_users where email='$email' limit 1");
				if (mysqli_num_rows($res) < 1) {
					$i++;
					$insert = dbRowInsert("manage_users", $user_data);
				} else {
					$j++;
				}
				$datapass = $i . '-' . $j;
				//$mess = $mess . "<br> Fresh Data: " . $i . " Duplicate Data: " . $j;
			} else {
				$mess = "Please enter valid user type";
			}
			send_welcome_email($email, $arr['A'], $rnd);
		}
		reDirect("?page=view-user&mess=" . $mess);
	}
}
function sendEmailWithAttachment($email_to, $user_name, $subject, $body, $attachments = null, $attachments_name = null)
{
	$mail = new PHPMailer(true);
	try {
		//Server settings
		$mail->SMTPDebug = 0;
		$mail->isSMTP();
		$mail->Host       = SMTP_HOST;
		$mail->SMTPAuth   = true;
		$mail->Username   = SMTP_USER;
		$mail->Password   = SMTP_PASS;
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Port       = SMTP_PORT;

		//Recipients
		$mail->setFrom(ADMIN_EMAIL, EMAIL_SEND_FROM);
		$mail->addAddress($email_to, $user_name);

		//Attachment
		if (!is_null($attachments)) {
			$mail->addStringAttachment($pdf, $pdf_name);
		}

		// Content
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->AltBody = strip_tags($body);
		$mail->send();
		// echo 'Message has been sent';
	} catch (Exception $e) {
		// echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}
function send_welcome_email($user_email, $user_name, $key)
{
	$from = ADMIN_EMAIL;
	//staticmail
	//$to ='amitpandey.his@gmail.com';
	$image = getHomeUrl() . "/upload_image/Data-Group-footer.png";
	$subject = "DGS Activation Link for " . $user_name;
	$link = getHomeUrl() . "user-activation.php?email=$user_email&key=$key";
	//$link=urlencode($link);
	$body = '<table width="100%" style="background-color:#dbdbdb;">
		<tr>
		<td>
		<table align="center" width="690" border="">
			<tr>
				<td style="background-color:#fff;" width="94%">
				<table width="100%;">
				<tr>
				<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/dgs-logo.png" /></td>
				</tr>
				<tr> <td height="20px;">&nbsp;</td> </tr>
				<tr>
				<td align="center"><h2> ACTIVATE YOUR ACCOUNT</h2></td>
				</tr>
				<tr> <td height="20px;">&nbsp;</td> </tr>
				<tr>
					<td> <p style="font-size:15px;margin:10px;">Hi ' . $user_name . '</p> </td>
				</tr>

				<tr>
					<td> <p style="font-size:15px;margin:10px;">Welcome to the DGS System.</p> </td>
				</tr>
				<tr>
					<td> <p style="font-size:15px;margin:10px;">Please <a href="' . $link . '">click here </a>to set your password and access your account.</p> </td>
				</tr>
				<tr>
					<td></td>
				</tr>
				
				<tr>
				<td height="20px;"><p style="font-size:15px;margin:10px;">DGFM System</p></td>
				</tr>
				</table>
			</td>
		</tr>
			<tr>
			<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/Data-Group-footer.png" />
			<p style="color:#a3a3a3;">Copyright ' . date('Y') . '  <strong>Data Group Solutions</strong> All rights reserved.</p>
			</td>
			</tr>
			</table></td>
		</tr>
	</table>';
	sendEmailWithAttachment($user_email, $user_name, $subject, $body);
}
function forgot_password_otp($user_email, $user_name, $fkey)
{
	$from = ADMIN_EMAIL;
	$subject = "DGS Password Recovery OTP";
	$body = '<table width="100%" style="background-color:#dbdbdb;">
		<tr>
		<td>
		<table align="center" width="690" border="">
			<tr>
				<td style="background-color:#fff;" width="94%">
				<table width="100%;">
				<tr>
				<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/dgs-logo.png" /></td>
				</tr>
				<tr> <td height="20px;">&nbsp;</td> </tr>
				<tr>
				<td align="center"><h2> PASSWORD RECOVERY OTP</h2></td>
				</tr>
				<tr> <td height="20px;">&nbsp;</td> </tr>
				<tr>
					<td> <p style="font-size:15px;margin:10px;">Hi ' . $user_name . '</p> </td>
				</tr>

				<tr>
					<td> <p style="font-size:15px;margin:10px;">Please use the following One Time Password (OTP) to reset your password: <strong>' . $fkey . '</strong>. Do not share this OTP with anyone.</p> </td>
				</tr>
				<tr>
					<td height="20px;"><p style="font-size:15px;margin:10px;">DGFM System</p></td>
				</tr>
				</table>
			</td>
		</tr>
			<tr>
			<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/Data-Group-footer.png" />
			<p style="color:#a3a3a3;">Copyright ' . date('Y') . '  <strong>Data Group Solutions</strong> All rights reserved.</p>
			</td>
			</tr>
			</table></td>
		</tr>
	</table>';
	sendEmailWithAttachment($user_email, $user_name, $subject, $body);
}

function export_csv_file($data, $type, $survey_name, $start_date = null, $end_date = null)
{

	if ($type == 'survey' or empty($type)) {
		$file_name = 'Survey_Statistics-' . date('Y-m-d-H-i-s') . '.csv';
	} else {
		$file_name = str_replace(" ", "-", $survey_name) . '-' . date('Y-m-d-H-i-s') . '.csv';
	}
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=' . $file_name);
	$output = fopen("php://output", "w");

	$excel_data = array();
	$excel_heading = array();

	if ($start_date != null && $end_date != null) {
		$excel_heading[] = 'Date';
	}

	if ($type == 'location') {
		$excel_heading[] = 'Location Name';
	} else if ($type == 'group') {
		$excel_heading[] = 'Group Name';
	} else if ($type == 'department') {
		$excel_heading[] = 'Department Name';
	} else if ($type == 'role') {
		$excel_heading[] = 'Role Name';
	} else {
		$excel_heading[]	= 'Survey id';
		$excel_heading[]	= 'Survey Name';
	}
	$excel_heading[]	= 'Survey Responses';
	$excel_heading[]	= 'Contact Requests';
	$excel_heading[] 	= 'Average Survey Score';
	$i = 0;
	foreach ($data as $key => $datasurvey) {
		$total =  array_sum($datasurvey['data']) / count($datasurvey['data']);
		$total =  round($total, 2);

		if ($start_date != null && $end_date != null) {
			$excel_data[$i]['Date'] = date('d/m/Y', strtotime($start_date)) . '-' . date('d/m/Y', strtotime($end_date));
		}

		if ($type == 'location') {
			$excel_data[$i]['Location_Name'] = getLocation('all')[$key];
		} else if ($type == 'group') {
			$excel_data[$i]['Group_Name'] = getGroup('all')[$key];
		} else if ($type == 'department') {
			$excel_data[$i]['Department_Name'] = getDepartment('all')[$key];
		} else if ($type == 'role') {
			$excel_data[$i]['Role_Name'] = getRole('all')[$key];
		} else {
			$excel_data[$i]['Survey_id']	= $key;
			$excel_data[$i]['Survey_Name']	= getSurvey()[$key];
		}
		$first_value = reset($datasurvey['data']);
		if ($first_value === 'Not-Found') {
			$totalSurvey = 0;
		} else {
			$totalSurvey = count($datasurvey['data']);
		}
		$excel_data[$i]['Survey_Responses'] 	= $totalSurvey;
		$excel_data[$i]['Contact_Requests'] 	= ($datasurvey['contact']) ? $datasurvey['contact'] : 0;
		$excel_data[$i]['Average_Survey_Score'] = $total . " %";
		$i++;
	}


	// replace '_' with " " in array keys

	$replacedKeys = str_replace('_', ' ', $excel_heading);

	fputcsv($output, $replacedKeys);
	foreach ($excel_data as $csv) {
		fputcsv($output, array_values($csv));
	}
	fclose($output);
}

function download_csv_folder($parentData, $type, $dir, $time_interval = null)
{
	$i = 0;
	$excel_data[$i]['Date'] = '';
	$excel_data[$i]['Survey Id']	= '';
	$excel_data[$i]['Survey_Name']	= '';

	if ($type == 'location') {
		$excel_data[$i]['Location_Name'] = '';
	} else if ($type == 'group') {
		$excel_data[$i]['Group_Name'] = '';
	} else if ($type == 'department') {
		$excel_data[$i]['Department_Name'] = '';
	}

	$excel_data[$i]['Survey_Responses'] = '';
	$excel_data[$i]['Contact Requests'] = '';
	$excel_data[$i]['Average_Survey_Score'] = '';



	foreach ($parentData as $mainKey => $data) {
		foreach ($data as $key => $datasurvey) {
			// echo '<pre>';
			// print_r($datasurvey);
			// echo '</pre>';

			$total =  array_sum($datasurvey['data']) / count($datasurvey['data']);
			$total =  round($total, 2);

			if ($time_interval == 24) {
				$excel_data[$i]['Date'] = date('d/m/Y', strtotime($mainKey));
			} else {
				$excel_data[$i]['Date'] = date('d/m/Y', strtotime($mainKey)) . ' - ' . date('d/m/Y', strtotime($datasurvey['end_date']));
			}

			if (isset($datasurvey['survey_id']) && $datasurvey['survey_id'] > 0) {
				$excel_data[$i]['Survey Id'] = $datasurvey['survey_id'];
				$excel_data[$i]['Survey_Name']	= getSurvey()[$datasurvey['survey_id']];
			} else {
				$excel_data[$i]['Survey Id'] = $key;
				$excel_data[$i]['Survey_Name']	= getSurvey()[$key];
			}

			if ($type == 'location') {
				$excel_data[$i]['Location_Name'] = getLocation('all')[$key];
			} else if ($type == 'group') {
				$excel_data[$i]['Group_Name'] = getGroup('all')[$key];
			} else if ($type == 'department') {
				$excel_data[$i]['Department_Name'] = getDepartment('all')[$key];
			}

			$excel_data[$i]['Survey_Responses'] 	= count($datasurvey['data']) ?? 0;
			$excel_data[$i]['Contact Requests'] = $datasurvey['contact'] ?? 0;
			$excel_data[$i]['Average_Survey_Score'] = $total . " %";
			$i++;
		}
	}

	$csv_header = str_replace('_', ' ', array_keys($excel_data[0]));
	$csv_data = implode(',', $csv_header);

	foreach ($excel_data as $data) {
		$csv_data .= "\n" . implode(',', array_values($data));
	}

	$csv_handler = fopen("$dir", 'w');
	fwrite($csv_handler, $csv_data);
	fclose($csv_handler);
}

// different in two date 
function check_differenceDate($date1, $date2, $type = "gt")
{

	$curr_date = new DateTime($date1);
	$next_date = new DateTime($date2);
	// printr($curr_date,0);
	// printr($next_date,1);

	$flg = false;

	// greater than dates
	if ($type == 'gt') {
		if ($curr_date > $next_date) {
			$flg = true;
		}
	}

	// Less than dates
	else if ($type == 'lt') {
		if ($curr_date < $next_date) {
			$flg = true;
		}
	}

	// equal dates
	else if ($type == 'eq') {
		if ($curr_date == $next_date) {
			$flg = true;
		}
	}

	// less than or equal
	else if ($type == 'lte') {
		if ($date1 <= $date2) {
			$flg = true;
		}
	}

	// greater than or equal
	else if ($type == 'gte') {
		if ($curr_date >= $next_date) {
			$flg = true;
		}
	}

	return $flg;
}

function create_mpdf($html = '', $file_name = '', $output)
{
	$mpdf = new \Mpdf\Mpdf();
	$mpdf->AddPageByArray([
		'margin-bottom' => 30,
	]);
	$footer = '<div style="text-align: center;"> ' . POWERED_BY . '
	<center><img  src="' . BASE_URL . FOOTER_LOGO . '" alt="" width="150"/></center>
	</div>';

	$mpdf->SetHTMLFooter($footer);
	$mpdf->WriteHTML($html);
	return $mpdf->Output($file_name, $output);
}

function mail_attachment($path, $to, $from_mail, $from_name, $subject, $message)
{
	// $from = stripslashes('dgfm') . "<" . stripslashes('dgs@gmail.com') . ">";
	$from = ADMIN_EMAIL;
	// generate a random string to be used as the boundary marker
	$mime_boundary = "==Multipart_Boundary_x" . md5(mt_rand()) . "x";

	// now we'll build the message headers
	$headers = "From: $from\r\n" . "MIME-Version: 1.0\r\n" . "Content-Type: multipart/mixed;\r\n" . " boundary=\"{$mime_boundary}\"";

	$message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";

	for ($i = 0; $i < count($path); $i++) {
		// open the file for a binary read
		$temp_name = $path[$i];
		$file = fopen($temp_name, 'rb');

		$file_name = basename($temp_name);
		$file_type = mime_content_type($temp_name);
		// read the file content into a variable
		$data = fread($file, filesize($temp_name));

		// close the file
		fclose($file);

		// now we encode it and split it into acceptable length lines
		$data = chunk_split(base64_encode($data));

		$message .= "--{$mime_boundary}\n" . "Content-Type: {'application/pdf'};\n" . " name=\"{$file_name}\"\n" . "Content-Disposition: attachment;\n" . " filename=\"{$file_name}\"\n" . "Content-Transfer-Encoding: base64\n\n" .
			$data . "\n\n";
	}

	// here's our closing mime boundary that indicates the last of the message
	$message .= "--{$mime_boundary}--\n";
	// now we just send the message
	return @mail($to, $subject, $message, $headers);
}

function emoticonsRatingImages()
{
	return array(
		"0" => "dist/img/5-5.png",
		"1" => "dist/img/5-4.png",
		"2" => "dist/img/5-3.png",
		"3" => "dist/img/5-2.png",
		"4" => "dist/img/5-1.png"
	);
}

function tickCrossRatingImages()
{
	return array(
		"0" => "dist/img/yes.png",
		"1" => "dist/img/no.png",
	);
}

function starRatingImages()
{
	return array(
		"0" => "dist/img/yes.png",
		"1" => "dist/img/no.png",
	);
}

function emoticonsRatingOptions()
{
	return array(
		"0" => "Ecstatic Smiley",
		"1" => "Happy Smiley",
		"2" => "Nonchalant",
		"3" => "Unhappy Smiley",
		"4" => "Furious Smiley"
	);
}

function starRatingOptions()
{
	return array(
		"0" => "One Star",
		"1" => "Two Star",
		"2" => "Three Star",
		"3" => "Four Star",
		"4" => "Five Star",
	);
}

function numberRatingOptions()
{
	return array(
		"0" => "One",
		"1" => "Two",
		"2" => "Three",
		"3" => "Four",
		"4" => "Five",
		"5" => "Six",
		"6" => "Seven",
		"7" => "Eight",
		"8" => "Nine",
		"9" => "Ten",
	);
}

function tickCrossRatingOptions()
{
	return array(
		"0" => "Yes",
		"1" => "No",
	);
}

function cron_emails($attachments, $to, $from_mail, $name, $subject, $message)
{
	try {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = SMTP_HOST;
		$mail->SMTPAuth = true;
		$mail->Port = SMTP_PORT;
		$mail->Username = SMTP_USER;
		$mail->Password = SMTP_PASS;

		$mail->setFrom($from_mail, $name);
		$mail->addAddress($to);

		$mail->isHTML(true);
		$mail->Subject = $subject;
		$body = '<table width="100%" style="background-color:#dbdbdb;">
                	<tr>
                	<td>
                	<table align="center" width="690" border="">
                		<tr>
                			<td style="background-color:#fff;" width="94%">
                			<table width="100%;">
                			<tr>
                			<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/dgs-logo.png" /></td>
                			</tr>
                			<tr> <td height="20px;">&nbsp;</td> </tr>
                			<tr>
                			<td align="center"><h2> SURVEY REPORT</h2></td>
                			</tr>
                			<tr> <td height="20px;">&nbsp;</td> </tr>
                
                			<tr>
                				<td><p style="font-size:15px;margin:10px;">Hello ' . $name . ',</p> <br>
                					<p style="font-size:15px;margin:10px;">You have received schedule reports with attachments.</p>
                				</td>
                			</tr>
                			<tr>
                				<td></td>
                			</tr>
                			<tr>
                			<td height="20px;">&nbsp;</td>
                			</tr>
                			</table>
                		</td>
                	</tr>
                		<tr>
                		<td align="center" style="padding:15px 0;background:#F0F4F5;"><img width="100px" src="' . getHomeUrl() . 'upload_image/Data-Group-footer.png" />
                		<p style="color:#a3a3a3;">Copyright ' . date('Y') . '  <strong>Data Group Solutions</strong> All rights reserved.</p>
                		</td>
                		</tr>
                		</table></td>
                	</tr>
                	</table>';
		$mail->Body = $body;

		// Add the attachments to the email
		foreach ($attachments as $key => $filePath) {
			$mail->addAttachment($filePath);
		}

		// $mail->SMTPDebug = 4;
		$mail->send();

		return true;
	} catch (Exception $e) {
		// echo "Message could not be sent. Mailer Error: { $e->getMessage()}";
		return false;
		//echo "Message could not be sent. Mailer Error: {$e}";
	}
}

function createZip($zipArchive, $folder)
{
	if (is_dir($folder)) {
		if ($f = opendir($folder)) {
			while (($file = readdir($f)) !== false) {
				if (is_file($folder . $file)) {
					if ($file != '' && $file != '.' && $file != '..') {
						$zipArchive->addFile($folder . $file);
					}
				} else {
					if (is_dir($folder . $file)) {
						if ($file != '' && $file != '.' && $file != '..') {
							$zipArchive->addEmptyDir($folder . $file);
							$folder = $folder . $file . '/';
							createZip($zipArchive, $folder);
						}
					}
				}
			}
			closedir($f);
		} else {
			exit("Unable to open directory " . $folder);
		}
	} else {
		exit($folder . " is not a directory.");
	}
}

function downloadZip($filename, $absoluteFilePath)
{
	if (file_exists($filename)) {
		// adjust the below absolute file path according to the folder you have downloaded
		// the zip file
		// I have downloaded the zip file to the current folder
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		// content-type has to be defined according to the file extension (filetype)
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="' . basename($filename) . '";');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($absoluteFilePath));
		readfile($absoluteFilePath);
		// exit();
	}
}

function send_survey_pdf_with_email($attachments, $to, $from_mail, $name, $subject, $message, $body_content)
{
	try {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = SMTP_HOST;
		$mail->SMTPAuth = true;
		$mail->Port = SMTP_PORT;
		$mail->Username = SMTP_USER;
		$mail->Password = SMTP_PASS;

		$mail->setFrom($from_mail, $name);
		$mail->addAddress($to);

		$mail->isHTML(true);
		$mail->Subject = $subject;
		$body = $body_content;
		$mail->Body = $body;
		foreach ($attachments as $key => $filePath) {
			$mail->addAttachment($filePath);
		}

		// $mail->SMTPDebug = 4;
		$mail->send();

		return true;
	} catch (Exception $e) {
		// echo "Message could not be sent. Mailer Error: { $e->getMessage()}";
		return false;
		//echo "Message could not be sent. Mailer Error: {$e}";
	}
}

// create pdf for send survey to user
function generateSurveyPdf($surveyid, $userid, $file_name){
	global $get_survey, $row_get_survey;
	global $get_contact_action, $totalRows_get_contact_action, $row_get_contact_action;
	global $get_contact_action_single, $totalRows_get_contact_action_single, $row_get_contact_action_single;
	global $get_survey_id, $totalRows_get_survey_id, $row_get_survey_id;
	global $get_questions, $row_get_questions;
	global $get_surveys_steps, $row_get_surveys_steps;
	global $get_questions_detail, $totalRows_get_questions_detail, $row_get_questions_detail;
	global $get_answers, $totalRows_get_answers, $row_get_answers;
	global $get_loc_dep, $row_get_loc_dep;
	global $get_department, $row_get_department;
	global $get_location, $row_get_location;
	global $get_contact, $row_get_contact;
	global $get_survey_result, $row_get_survey_result;
	

	record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$surveyid."' and cby='".$userid."'");
                                
	$achieved_result_val = 0;
	$total_result_val=0;
	$contacted     = 0;
	$i=0;	
	$showAllComment =[];

	while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
		$result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_survey_result['questionid']);
		if($result_question){
			if(!in_array($result_question['answer_type'],array(2,3,5))){
				$total_result_val = ($i+1)*100;
				$achieved_result_val += $row_get_survey_result['answerval'];
				$i++;
			}
		}
		if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100){
			$contacted = 1;
		}
	}
	$score = $achieved_result_val*100/$total_result_val;
	if($achieved_result_val==0 and $total_result_val==0){
		$score=100;
	}
	
	record_set("get_survey", "select * from surveys where id='".$surveyid."' and cstatus=1");
	$row_get_survey = mysqli_fetch_assoc($get_survey);
		

	if(isset($userid)){
		record_set("get_contact_action", "select * from survey_contact_action where user_id='".$userid."'");
		if($totalRows_get_contact_action > 0){ 
			$i = 0;
		  	while($row_get_contact_action = mysqli_fetch_assoc($get_contact_action)){
				if($row_get_contact_action['action'] == 1){
				$showAllComment[$i]['action']='UNASSIGNED';
				$showAllComment[$i]['status']=$row_get_contact_action['action'];
				}
				if($row_get_contact_action['action'] == 2){
				$showAllComment[$i]['action']='ASSIGNED';
				$showAllComment[$i]['status']=$row_get_contact_action['action'];
				}
				if($row_get_contact_action['action'] == 3){
				$showAllComment[$i]['action']='IN PROGRESS';
				$showAllComment[$i]['status']=$row_get_contact_action['action'];
				}
				if($row_get_contact_action['action'] == 4){
				$showAllComment[$i]['action']='VOID';
				$showAllComment[$i]['status']=$row_get_contact_action['action'];
				}
				if($row_get_contact_action['action'] == 5){
				$showAllComment[$i]['action']='RESOLVED-POSITIVE';
				$showAllComment[$i]['status']=$row_get_contact_action['action'];
				}
				if($row_get_contact_action['action'] == 6){
				$showAllComment[$i]['action']='RESOLVED-NEGATIVE';
				$showAllComment[$i]['status']=$row_get_contact_action['action'];
				}
				
				$showAllComment[$i]['comment']=$row_get_contact_action['comment'];
				$showAllComment[$i]['created_date']=$row_get_contact_action['created_date'];
			
				$showAllComment[$i]['cby_user_id']=$row_get_contact_action['cby'];
				$i++;
			}
		}
		
		record_set("get_contact_action_single", "select * from survey_contact_action where user_id='".$userid."' order by action desc");
		if($totalRows_get_contact_action_single > 0){
			$row_get_contact_action_single = mysqli_fetch_assoc($get_contact_action_single);
			if($row_get_contact_action_single['action'] == 1){
			  $co_action = "In progress";
			  $contact_comment = $row_get_contact_action_single['comment'];
			  $created_date=$row_get_contact_action_single['created_date'];
		
			}else if($row_get_contact_action_single['action'] == 2){
			  $co_action =  "Void";
			  $contact_comment = $row_get_contact_action_single['comment'];
			  $created_date=$row_get_contact_action_single['created_date'];
		
			}else if($row_get_contact_action_single['action'] == 3){
			  $co_action =  "Resolved-Positive";
			  $contact_comment = $row_get_contact_action_single['comment'];
			  $created_date=$row_get_contact_action_single['created_date'];
			}
			else if($row_get_contact_action_single['action'] == 4){
			  $co_action =  "Resolved-Negative";
			  $contact_comment = $row_get_contact_action_single['comment'];
			  $created_date=$row_get_contact_action_single['created_date'];
			}
		}		 
	}
	
	record_set("get_survey_id", "select surveyid from answers where cby='".$userid."'");
	if($totalRows_get_survey_id > 0){
		$row_get_survey_id = mysqli_fetch_assoc($get_survey_id);
		$survey_id = $row_get_survey_id['surveyid'];
	}

	//filter
	$ans_filter_query='';
	if($userid){
		$ans_filter_query .= " and cby='".$userid."' ";
	}
	if($_REQUEST['month']){
		$ans_filter_query .= " and cdate like '".$_REQUEST['month']."-%' ";
	}

	//Survey Steps 
	$survey_steps = array();
	if($row_get_survey['isStep'] == 1){
		record_set("get_surveys_steps", "select * from surveys_steps where survey_id='".$surveyid."' order by step_number asc");
		while($row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps)){
			$survey_steps[$row_get_surveys_steps['id']]['number'] = $row_get_surveys_steps['step_number'];
			$survey_steps[$row_get_surveys_steps['id']]['title'] = $row_get_surveys_steps['step_title'];
		}
	}

	//Survey Questions
	record_set("get_questions", "select * from questions where surveyid='".$surveyid."' and cstatus='1' and parendit='0' order by dposition asc");
	$questions = array();
	while($row_get_questions = mysqli_fetch_assoc($get_questions)){
		if($row_get_questions['survey_step_id'] != 0){
			$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
			$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
			$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
			$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
		}
		if($row_get_questions['survey_step_id'] == 0){
			$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
			$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
			$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
			$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
		}
	}

	$html = '<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">';

	$html .= '<div id="reportPage" style="font-family: Roboto;">
		<div align="center"><img src="'.MAIN_LOGO.'" width="200"></div>
			<h2 align="center" style="margin:20px;">'.strtoupper($row_get_survey['name']).'</h2>';

			record_set("get_loc_dep", "select locationid,groupid,roleid,departmentid from answers where surveyid='".$surveyid."' ".$ans_filter_query);
			$row_get_loc_dep = mysqli_fetch_assoc($get_loc_dep);
			
			//Department			
			record_set("get_department", "select name from departments where id = '".$row_get_loc_dep['departmentid']."'");
			$row_get_department = mysqli_fetch_assoc($get_department);

			//Location			
			record_set("get_location", "select name from locations where id = '".$row_get_loc_dep['locationid']."'");
			$row_get_location = mysqli_fetch_assoc($get_location);

			//Contact Details			
			record_set("get_contact", "select answertext from answers where surveyid ='".$surveyid."' and cby='".$cby_userid."' and answerid='-2' and answerval='100' and cstatus=1");
			$row_get_contact = mysqli_fetch_assoc($get_contact);
			$contactDetails = json_decode($row_get_contact['answertext'],1);
			
			$html .= '<div class="container">
				<table style="font-size: 14px; width: 100%; font-family: Roboto; border-collapse: collapse; border-left: none; border-right: none;" border="1" align="center" cellspacing="0" cellpadding="4">
					<tbody>
                    <tr>
                        <td class="thead" style="font-size: 16px; padding: 10px; border-left: none; border-right: none;"><div style="display: flex; align-items: center; justify-content: start; gap: 20px;"><strong style="min-width: 100px; text-align: start;">Group : </strong> <span style="font-weight: 400;">'.(getGroup()[$row_get_loc_dep['groupid']]).'</span></div></td>
                        <td class="thead" style="font-size: 16px; padding: 10px; border-left: none; border-right: none;"><div style="display: flex; align-items: center; justify-content: start; gap: 20px;"><strong style="min-width: 100px; text-align: start;">Location : </strong> <span style="font-weight: 400;">'.(getLocation()[$row_get_loc_dep['locationid']]).'</span></div></td>
                    </tr>
                </tbody>
                <tbody>
                    <tr>
                        <td class="thead" style="font-size: 16px; padding: 10px; border-left: none; border-right: none;"><div style="display: flex; align-items: center; justify-content: start; gap: 20px;"><strong style="min-width: 100px; text-align: start;">Department : </strong> <span style="font-weight: 400;">'.(getDepartment()[$row_get_loc_dep['departmentid']]).'</span></div></td>
                        <td class="thead" style="font-size: 16px; padding: 10px; border-left: none; border-right: none;"><div style="display: flex; align-items: center; justify-content: start; gap: 20px;"><strong style="min-width: 100px; text-align: start;">Role : </strong> <span style="font-weight: 400;">'.(getRole()[$row_get_loc_dep['groupid']]).'</span></div></td>
                    </tr>
                </tbody>
				</table>';
			
				if($contacted == 1){
					$html .= '<table style="font-size:14px;width:100%; font-family: Roboto;" align="center"  cellspacing="0" cellpadding="4" >
						<thead>
							<tr>
								<th colspan="3"></th>
							</tr>
							<tr>
								<th colspan="3">Contact Details</th>
							</tr>
						</thead>

						<tbody style="border-bottom:1px solid black;">';
							$name =  $contactDetails['first_name'].' '.$contactDetails['last_name'];
							
							$html .= '<tr>
								<td><strong>Name</strong> : '.$name.'</td>
								<td><strong>Phone Number</strong> : '.$contactDetails['phone_number'].'</td>
								<td><strong>Contact Email</strong> : '.$contactDetails['to_be_contact_mail'].'</td>
							</tr>
						</tbody>
					</table>';
				}
				if(isset($score)){
					$html .= '<h2 align="center" style="margin-top:20px;">SURVEY SCORE: '.$score.'%</h2>';
				} $html .= '
			</div>
			<div class="container">';				
				if(count($survey_steps)> 0) {
					foreach($survey_steps AS $key => $value) {
						$html .= '<div class="" style="page-break-inside: avoid;">  
							<h4 align="center" style="margin-top:20px; margin-bottom:10px;">'.$value['title'].'</h4>
							<table style="font-size:14px;width:100%; border-collapse: collapse; font-family: Roboto;" border ="1" cellspacing="0" cellpadding="4" align="center">
								<thead>
									<tr>
										<th scope="col" style="width:40px; padding: 10px;">#</th>
										<th scope="col" style="border-left:none;border-right:none;width:600px; padding: 10px;">QUESTION</th>
										<th scope="col" style="width:500px; padding: 10px;">ANSWER</th>
									</tr>
								</thead>
								<tbody>';

								$i=0;                    
								foreach($questions[$key] AS $question){
									$i++;
									$questionid = $question['id']; 
									$answer_type = $question['answer_type'];
									$totalRows_get_child_questions = 0;
									$questions_array = array();
									$answers_array = array();

									// for radio rating dropdown
									if($answer_type == 1 || $answer_type == 4 || $answer_type == 6){										
										record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'"); 
										if($totalRows_get_questions_detail>0){
											while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
												if($row_get_questions_detail['condition_yes_no'] == 1){
													$questions_array[$row_get_questions_detail['id']] = $row_get_questions_detail['description'].' (Conditional)';
												}else{
													$questions_array[$row_get_questions_detail['id']] = $row_get_questions_detail['description'];
												}
											}
										}
										
										record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' ");  
										if($totalRows_get_answers>0){
											while($row_get_answers = mysqli_fetch_assoc($get_answers)){
												$answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
											}
										}
										$question = $question['question'];
										foreach($answers_array as $key => $value){
										$answer_value =  $questions_array[$value];
										}
									}

									// textbox or textarea
									if($answer_type == 2 || $answer_type == 3){
										record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' ");  
										if($totalRows_get_answers>0){
											while($row_get_answers = mysqli_fetch_assoc($get_answers)){
												if($row_get_answers['answerid']==0){
												$answers_array[$row_get_answers['questionid']] = $row_get_answers['answertext'];
												}else{
												$answers_array[$row_get_answers['id']] = 0011;
												}
											
											}
										}
										foreach($answers_array as $key => $value){
											if($key == $question['id']){
												$question = $question['question'];
												$answer_value = $value;
											}
										}
									} 
									if($answer_type !=5){
										$html .= '<tr>
											<td align="center" style="padding: 10px;" class="remove-bt">'.$i.'</td>
											<td class="remove-bt" style="padding: 10px;">'.(is_array($question) ? $question['question'] : $question ).'</td>
											<td class="remove-bt" style="padding: 10px;">'.($answer_value ?? 'N/A').'</td>
										</tr>';
									} 
								} $html .= '
							</tbody>
						</table>
						</div>';
					} 
				} else{
					$html .= '<div class="">  
						<h4 align="center" style="margin-top:20px;margin-bottom:10px;">'.$value['title'].'</h4>
						<table style="font-size:14px;width:100%; border-collapse: collapse; font-family: Roboto;" border ="1" cellspacing="0" cellpadding="4" align="center">
							<thead>
								<tr>
									<th scope="col" style="width:40px; padding: 10px;">#</th>
									<th scope="col" style="border-left:none;border-right:none;width:600px; padding: 10px;">QUESTION</th>
									<th scope="col" style="width:500px; padding: 10px;">ANSWER</th>
								</tr>
							</thead>
							<tbody>';
								$i=0;
								foreach($questions[0] AS $question){
									$i++;
									$questionid = $question['id']; 
									$answer_type = $question['answer_type'];
									$totalRows_get_child_questions = 0;
									if($answer_type == 1 || $answer_type == 4 || $answer_type == 6){
										record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'"); 
										if($totalRows_get_questions_detail>0){
											while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
												if($row_get_questions_detail['condition_yes_no'] == 1){
													$questions_array[$row_get_questions_detail['id']] = $row_get_questions_detail['description'].' (Conditional)';
												}else{
													$questions_array[$row_get_questions_detail['id']] = $row_get_questions_detail['description'];
												}
											}
										}
										record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' ");  
										if($totalRows_get_answers>0){
											while($row_get_answers = mysqli_fetch_assoc($get_answers)){
												$answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
											}
										}
										$question = $question['question'];
										foreach($answers_array as $key => $value){
											$answer_value =  $questions_array[$value];
										}
									}
									// textbox or textarea
									if($answer_type == 2 || $answer_type == 3){
										record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' ");  
										if($totalRows_get_answers>0){
											while($row_get_answers = mysqli_fetch_assoc($get_answers)){
												if($row_get_answers['answerid']==0){
													$answers_array[$row_get_answers['questionid']] = $row_get_answers['answertext'];
												}else{
													$answers_array[$row_get_answers['id']] = 0011;
												}
											}
										}
										foreach($answers_array as $key => $value){
											if($key == $question['id']){
												$question = $question['question'];
												$answer_value = $value;
											}
										}
									} 
									if($answer_type !=5){
										$html .= '<tr>
											<td align="center" style="padding: 10px;" class="remove-bt">'.$i.'</td>
											<td class="remove-bt" style="border-left:none;border-right:none; padding: 10px;">'.(is_array($question) ? $question['question'] : $question ).'</td>
											<td class="remove-bt" style="padding: 10px;">'.($answer_value ?? 'N/A').'</td>
										</tr>';
									}
								} $html .= '
							</tbody>
						</table>
					</div>';
				}

				$html .= '<div class="row">';
					if(count($showAllComment) > 0 ) {
						$html .= '<h2 align="center" style="margin-top:20px;margin-bottom:10px;">CONTACT</h2>';
					}
					
					foreach($showAllComment as $comm) {
						$html .= '<hr style="border: 0.5px solid #d3cccc;"/>
						<div class="col-md-12" style="text-align: center;">
							<div class="col-md-4">
								<div class="col-md-6" style="padding: 0px;"><strong>STATUS UPDATED TO :</strong></div>
								<div class="col-md-6" style="padding: 0px;text-align:left;">'.($comm['action']).'</div> 
							</div>
							
							<div class="col-md-4">
								<div class="col-md-5" style="padding: 0px;text-align: right;"><strong>USERNAME :</strong></div>
								<div class="col-md-7" style="padding: 0px 5px;text-align:left;">'.get_user_datails($comm['cby_user_id'])['name'].'</div>
							</div>
							<div class="col-md-4">
								<div class="col-md-7" style="padding: 0px;text-align: right;"><strong>CONTACTED ON :</strong></div>
								<div class="col-md-5" style="padding: 0px;text-align: right;">'.date('d-m-Y h:s:i',strtotime($comm['created_date'])).'</div>
							</div>
						</div>
						<div class="col-md-12" style="margin: 43px 0px 40px 0px;">
							<div class="col-md-8">
								<div class="col-md-6" style="text-align:right;padding: 0px;"><strong>COMMENT :</strong></div>
								<div class="col-md-6" style="text-align:left;padding: 0px 5px;">'.$comm['comment'].'</div>
							</div>
						</div>
						<hr style="border: 0.5px solid #d3cccc;"/>';
					} $html .= '
				</div>
			</div>
		</div>
    </div>';
	create_mpdf($html, $file_name, 'F');
}