<?php 
require('mysql_functions.php');
$msg='';
$active_user_id= $_SESSION['admin_id'];
function pr($data)
{
	echo '<pre style="margin-left: 257px;">';
	print_r($data);
	echo "</pre>";
	exit;
}
 // Make a safe SQL
$invoice_prefix = 'SPPL/2019-20/';
define('DEFAULT_FROM_EMAIL', 'mail@datagroup.dev');
 function test_input($raw_data){
	 foreach($raw_data as $key=>$value) {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlentities($value,ENT_QUOTES);
        if (!is_numeric($value)){ 
			$value =mysqli_real_escape_string(get_connection(),$value);
		}
	   $filter_data[$key]= $value;
     }
	 return $filter_data;
 }
  //Get Ip address
function ipAddress(){
    $ipaddress = '';
    if ($_SERVER['HTTP_CLIENT_IP']){
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	}
    else if($_SERVER['HTTP_X_FORWARDED_FOR']){
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];}
    else if($_SERVER['HTTP_X_FORWARDED']){
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];}
    else if($_SERVER['HTTP_FORWARDED_FOR']){
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];}
    else if($_SERVER['HTTP_FORWARDED']){
	  $ipaddress = $_SERVER['HTTP_FORWARDED'];}
    else if($_SERVER['REMOTE_ADDR']){
        $ipaddress = $_SERVER['REMOTE_ADDR'];}
    else{
        $ipaddress = 'UNKNOWN';
	}
    return $ipaddress;
}
//Redirect to page & message
function reDirect($path) {
	echo '<script>window.open("'.$path.'","_top");</script>';
}
//Redirect to page & message
function giveAlert($message) {
	echo '<script>alert("'.$message.'");</script>';
}
function alert($message) {
	echo '<script>alert("'.$message.'");</script>';
}
//SET SUCCESS NOTICES
function set_msg($msga){
	$_SESSION['msg'] = '';
	$_SESSION['msg'] = $msga;
}
function table_exist($table){
	$connection=get_connection();
	//$qry =  mysqli_query($connection,"select * from client_management where status=1 order by name" );
    $sql = "show tables like '".$table."'";
    $res = $connection->query($sql);
    return ($res->num_rows > 0);
}
function column_exist($table,$column){
	$connection=get_connection();
    //$qry =  mysqli_query($connection,"select * from client_management where status=1 order by name" );
    $sql = "SHOW COLUMNS FROM ".$table." LIKE '".$column."'";
    $res = $connection->query($sql);
    return ($res->num_rows > 0);
}
// CREATE TABLE 
function table_create($table,$LIKE){
	$connection=get_connection();
	$sql_table = "SHOW TABLES LIKE '".$table."'";
	if(mysqli_num_rows(mysqli_query($connection,$sql_table))==1){
	 $table_exist=1;
	}else {
	  $table_exist=0;
	}
	if($table_exist==0){
  // table create
	$sql_CREATE = "create TABLE ".$table." like ".$LIKE."";
	$qry =  mysqli_query($connection,$sql_CREATE);
	}
}
//DISPLAY SUCCESS MESSAGE
function display_msg($msg='') 
{
	if (!empty($_SESSION['msg'])) 
	{
		echo "<span class='notices' style='color:#C00'>$_SESSION[msg]</span>";
		unset($_SESSION['msg']);
	}
	else if($msg)
	{
		echo "<span class='notices' style='color:#C00'>$msg</span>";
	}
}
//Generate Password
function random_code($length) {
    $characters = array(
        "A","B","C","D","E","F","G","H","J","K","L","M",
        "N","P","Q","R","S","T","U","V","W","X","Y","Z",
        "1","2","3","4","5","6","7","8","9");
    if ($length < 0 || $length > count($characters)) return null;
    shuffle($characters);
    return implode("", array_slice($characters, 0, $length));
}	
function generateRandomString( $length ) {
    $chars = array_merge(range('a', 'z'), range(0, 9));
    shuffle($chars);
    return implode(array_slice($chars, 0, $length));
}
function generateRandomNumber( $length ) {
    $chars = array_merge(range('0', '9'), range(0, 9));
    shuffle($chars);
    return implode(array_slice($chars, 0, $length));
}
function upload_image($folder_path,$image_id)
{
	$file_name=$_FILES["image"]["name"];
	$extension=end(explode(".", $file_name));
	$file_name=$image_id.".".$extension;
	$path=$folder_path.'/'.$file_name;
	if(file_exists($path))
	{
		unlink($path);		
	}
	$moved=move_uploaded_file($_FILES['image']['tmp_name'],$path);
	if($moved)
	{
		return $file_name;	
	}
}
function upload_multiple_image($folder_path,$image_id,$j)
{	
	  $file_name=$_FILES["image"]["name"]["$j"];
	  $extension=end(explode(".", $file_name));
	//echo $j;
	 $file_name=$image_id.".".$extension;
	//$path=$file_name;
	  $path=$folder_path.'/'.$file_name;
	if(file_exists($path))
	{
		unlink($path);		
	}
	//echo $path;
//	var_dump($_FILES['image']['tmp_name']);
	//["$j"]
	$moved=move_uploaded_file($_FILES['image']['tmp_name']["$j"],$path);
	//echo $moved;
	//die;
	if($moved)
	{
		return $file_name;	
	}
}
function check_login(){
	if(empty($_SESSION['user_id'])){
		   reDirect("login.php");			
		}
	}		
function forgot_password($user_email,$password){
	$from = DEFAULT_FROM_EMAIL;
	$to =$user_email;
	$subject = "Password Recovery Email";
	$body = "Dear Admin,<br><br>Your login details :<br> Email ID : $user_email <br> password : $password";
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: <'.$from.'>' . "\r\n";
	return mail($to,$subject,$body,$headers);	
}
function send_survey_email($recipients, $survey_name, $surveyid, $to_be_contacted, $to_be_contacted_mail,$contact, $cby)
{
	$from = DEFAULT_FROM_EMAIL;
	$link = $_SERVER['HTTP_HOST'].'/export-pdf.php?surveyid='.$surveyid;
	//$to = $user_email;
	$to = $to_be_contacted_mail;
	$allContactDetail = json_decode($contact,true);
	if($to_be_contacted==1){
		$to_be_contacted = 'YES';
		$to_be_contacted_link = "&contacted=1";
		$to_be_contacted_text = "<br><br>Here is detail for contact <br><br> Name : ".$allContactDetail['first_name'].' '.$allContactDetail['last_name'].'<br> Email : '.$allContactDetail['to_be_contact_mail'].'<br> Phone Number : '.$allContactDetail['phone_number'];
		// $to_be_contacted_text = "<br><br>Here is email for contact ".$to_be_contacted_mail;
	}else{
		$to_be_contacted = 'NO';
		$to_be_contacted_link = "";
		$to_be_contacted_text = "";
	}
	$user_link = $_SERVER['HTTP_HOST'].'/survey-result.php?surveyid='.$surveyid.'&userid='.$cby.$to_be_contacted_link;
	$export_link = $_SERVER['HTTP_HOST'].'/export-feedback.php?surveyid='.$surveyid.'&userid='.$cby.'&aid=-2&avl=10';
	//Entry Count
	$res=getaxecuteQuery_fn("SELECT id as total_entry from answers where surveyid=9 group by cby");
	$entry_number = mysqli_num_rows($res);
	$subject = $survey_name." Entry ".$entry_number." Contact ".$to_be_contacted;
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: Survey Entry Alert<'.$from.'>' . "\r\n";
    foreach($recipients AS $recipient){
    	$body = "Dear ".$recipient['name'].",<br><br>A new entry for survey ($survey_name) has been added, '".$to_be_contacted_text."'<br><br><a href='".$export_link."' download='test.xls' target='_blank'>Open new tab to export csv</a><br><br><a href='".$user_link."'>Click here to view this user entry</a><br><br> Thanks";
		$success = mail($recipient['email'],$subject,$body,$headers);	
    }
}
function upload_image1($folder_path,$file_name, $file_tempname,$image_id)
{
	//$file_name=$_FILES["image"]["name"];
	//$file_name=$_FILES["image"]["name"];
	$extension=end(explode(".", $file_name));
	$file_name=$image_id.".".$extension;
	$path=$folder_path.'/'.$file_name;
	if(file_exists($path))
	{
		unlink($path);		
	}
	$moved=move_uploaded_file($file_tempname,$path);
	if($moved)
	{
		return $file_name;	
	}
}
function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}
function getDayNumber($date){
	$dayNAme = date("l",strtotime($date));
	if($dayNAme == 'Monday'){
		return 1;
	}else if($dayNAme == 'Tuesday'){
		return 2;
	}else if($dayNAme == 'Wednesday'){
		return 3;
	}else if($dayNAme == 'Thursday'){
		return 4;
	}else if($dayNAme == 'Friday'){
		return 5;
	}else if($dayNAme == 'Saturday'){
		return 6;
	}else if($dayNAme == 'Sunday'){
		return 7;
	}
}
function getDatesFromRange($start, $end, $format = 'd-m-Y') {
    $array = array();
    $interval = new DateInterval('P1D');
    $realEnd = new DateTime($end);
    $realEnd->add($interval);
    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
    foreach($period as $date) { 
        $array[] = $date->format($format); 
    }
    return $array;
}
function insert_log($logtype,$logtable,$lognote,$logrefid,$logstatus,$logby,$usertype){
  //if($action=='manage_activity_log'){
	$insert_array=array(
	'log_type'=>$logtype,
	'log_table'=>$logtable,
	'log_note'=>$lognote,
	'log_ref_id'=>$logrefid,
	'user_type'=>$usertype,
	'status'=>$logstatus,
	'cby'=>$logby,
	'cdate'=>date("Y-m-d H:i:s"),
	'cip'=>ipAddress());
	$update=dbRowInsert('manage_activity_log',$insert_array);
  //}   
}
  function displayPagination($per_page,$page,$page_url,$total)
 {
    $adjacents = "1"; 
    $page = ($page == 0 ? 1 : $page);  
    $start = ($page - 1) * $per_page;        
    $prev = $page - 1;       
    $next = $page + 1;
    $setLastpage = ceil($total/$per_page);
    $lpm1 = $setLastpage - 1;
     $setPaginate = "";
     if($setLastpage > 1)
     {
      //$setPaginate .= "<span>Showing Page $page of $setLastpage</span>"; 
      $setPaginate .= "<ul class='pagination'>";
      if ($setLastpage < 7 + ($adjacents * 2))
      { 
       for ($counter = 1; $counter <= $setLastpage; $counter++)
       {
        if ($counter == $page)
         $setPaginate.= "<li class='active'><a>$counter</a></li>";
        else
         $setPaginate.= "<li><a href='{$page_url}p=$counter'>$counter</a></li>";     
       }
      }
      else if($setLastpage > 5 + ($adjacents * 2))
      {
       if($page < 1 + ($adjacents * 2))  
       {
        for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
        {
         if ($counter == $page)
          $setPaginate.= "<li class='active' ><a>$counter</a></li>";
         else
          $setPaginate.= "<li><a href='{$page_url}p=$counter'>$counter</a></li>";     
        }
      //  $setPaginate.= "<li class='dot'>...</li>";
        $setPaginate.= "<li><a href='{$page_url}p=$lpm1'>$lpm1</a></li>";
        $setPaginate.= "<li><a href='{$page_url}p=$setLastpage'>$setLastpage</a></li>";  
       }
       else if($setLastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
       {
        $setPaginate.= "<li><a href='{$page_url}'>1</a></li>";
        $setPaginate.= "<li><a href='{$page_url}'>2</a></li>";
     //   $setPaginate.= "<li class='dot'>...</li>";
        for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
        {
         if ($counter == $page)
          $setPaginate.= "<li class='active'><a>$counter</a></li>";
         else
          $setPaginate.= "<li><a href='{$page_url}p=$counter'>$counter</a></li>";     
        }
      //  $setPaginate.= "<li class='dot'>..</li>";
        $setPaginate.= "<li><a href='{$page_url}p=$lpm1'>$lpm1</a></li>";
        $setPaginate.= "<li><a href='{$page_url}p=$setLastpage'>$setLastpage</a></li>";  
       }
       else
       {
        $setPaginate.= "<li><a href='{$page_url}p=1'>1</a></li>";
        $setPaginate.= "<li><a href='{$page_url}p=2'>2</a></li>";
      //  $setPaginate.= "<li class='dot'>..</li>";
        for ($counter = $setLastpage - (2 + ($adjacents * 2)); $counter <= $setLastpage; $counter++)
        {
          if ($counter == $page)
           $setPaginate.= "<li class='active'><a>$counter</a></li>";
          else
           $setPaginate.= "<li><a href='{$page_url}p=$counter'>$counter</a></li>";     
        }
       }
      }
      if ($page < $counter - 1)
      { 
	    $setPaginate.= "<li><a href='{$page_url}p=$next'>Next</a></li>";
	    $setPaginate.= "<li><a href='{$page_url}p=$setLastpage'>Last</a></li>";
      }
      else
      {
          $setPaginate.= "<li class='active'><a>Next</a></li>";
          $setPaginate.= "<li class='active'><a>Last</a></li>";
      }
      $setPaginate.= "</ul>\n";  
     }
      return $setPaginate;
   }
function status(){
	return array(
		"1"=>"Active",
		"2" => "Deactivated"
	);
}
function status_data($status){
	$status_id=status();
	return $status_id[$status];
}
function required(){
	return array(
		"1"=>"Required",
		"2" => "Not Required"
	);
}
function required_name($status){
	$status_id=required();
	return $status_id[$status];
}
function question_type(){
	return array(
		"0" => "Select Type",
		"1" => "Radio Button",
		"2" => "Text Box",
		"3" => "Text Area",
		"4" => "Rating",
		"5" => "Title",
		"6" => "Drop Down",
		//"5" => "Yes/No"
	);
}
function question_type_name($status){
	$status_id=question_type();
	return $status_id[$status];
}
function survey_result_graph_colors(){
	return array(
		"1"=>"0f477b",
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
function survey_result_graph_colors_name($status){
	$status_id=survey_result_graph_colors();
	return $status_id[$status];
}
function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}
function boostrap_bg_colors(){
	return array(
		"1"=>"bg-dark",
		"2"=>"bg-",
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
		"13"=>"bg-dark",
		"14"=>"bg-dark",
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
function send_email_to_users($name,$email,$enc_id)
{
  $from = DEFAULT_FROM_EMAIL;
  //$link = $_SERVER['HTTP_HOST'].'/verify_email.php?id='.$enc_id;
	$to =$email;
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
	$headers .= 'From: <'.$from.'>' . "\r\n";
	$success = mail($to,$subject,$body,$headers);	
  if (!$success) {
      echo  $errorMessage = error_get_last()['message'];
	    }
  else{
    $msg='Message sent successfully !!';
  }
}
function get_boostrap_bg_colors($status){
	$status_id=boostrap_bg_colors();
	return $status_id[$status];
}
function smile_format($ans_count){
	if($ans_count==3){
		return array(
			"0"=>"dist/img/3-1.png", 
			"1"=>"dist/img/3-2.png", 
			"2"=>"dist/img/3-3.png"	
		);
	}else if($ans_count==5){
		return array(
			"0"=>"dist/img/5-5.png",
			"1"=>"dist/img/5-4.png",
			"2"=>"dist/img/5-3.png",
			"3"=>"dist/img/5-2.png",
			"4"=>"dist/img/5-1.png"	
		);
	}else if($ans_count==11){
		return array(
			"0"=>"dist/img/10-0.png",
			"1"=>"dist/img/10-1.png",
			"2"=>"dist/img/10-2.png",
			"3"=>"dist/img/10-3.png",
			"4"=>"dist/img/10-4.png",
			"5"=>"dist/img/10-5.png",
			"6"=>"dist/img/10-6.png",
			"7"=>"dist/img/10-7.png",
			"8"=>"dist/img/10-8.png",
			"9"=>"dist/img/10-9.png",
			"10"=>"dist/img/10-10.png" 	
		);
	}else{
		return array();
	}
}
function smile_format_icon($status,$ans_count){
	$status_id=smile_format($ans_count);
	return $status_id[$status];
}
function date_formate($type){
	$date_type=date("d-m-Y g:i",strtotime($type));
	return $date_type;
}
function date_formate_ymd($string){
	$string=date("Y-m-d",strtotime($string));
	return $string;
}
function date_formate_month($string){
	$string=date("M Y",strtotime($string));
	return $string;
}
function date_month_qry($string){
	$string=date("Y-m",strtotime($string));
	return $string;
}
function date_formate_cdate($string){
	$string=date("Y-m-d H:i:s",strtotime($string));
	return $string;
}
function user_type(){
	return array(
		'1' => 'Super Admin',
		'2' => 'Admin',
		'3' => 'Manager',
	);
}
function make_sidebar_active($page,$array){
	if(is_array($array)){
		if(in_array($page,$array)){
			return 'active';
		}
	}else {
		if($page == $array){
			return 'active';
		}
	}
}
function generate_unique_color($n){
	$rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
	$color_array=array();
	for($i=0; $i<$n;$i++){
		$color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
		$color_array[] = $color;
	}
	return $color_array;
}
//get randow string
function getName($n=8) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $n; $i++) {
		$index = rand(0, strlen($characters) - 1);
		$randomString .= $characters[$index];
	}
	return $randomString;
}
function service_type(){
	return array(
		'' => '-- Interval --', // Added by manisha
		// '12'   => '2 times per day',
		'24'   => 'Daily',
		'168'  => 'Weekly',
		'336'  => 'fortnightly',
		'720'  => 'Monthly',
		'2160' => 'quarterly',
		'4320' => '6 monthly',
		'8640' => 'annually'

	);
}
?>