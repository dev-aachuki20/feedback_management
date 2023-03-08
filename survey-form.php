<?php 
include('function/function.php');
require __DIR__ . '/translation/vendor/autoload.php';

use Google\Cloud\Translate\V2\TranslateClient;

if(isset($_GET['qrcode'])){
	record_set("get_survey_id", "select * from surveys where qrcode='".$_GET['qrcode']."' and cstatus=1 ");
	$row_get_survey_id = mysqli_fetch_assoc($get_survey_id);
	$surveyid=$row_get_survey_id['id'];
}else{
	$surveyid=$_GET['surveyid'];
}
//$surveyid=$_GET['surveyid'];

//Get Survey Record
if(isset($surveyid)){
	record_set("get_survey", "select * from surveys where id='".$surveyid."' and cstatus=1");	
	if($totalRows_get_survey>0){
		$row_get_survey = mysqli_fetch_assoc($get_survey);
		record_set("max_survey_id", "SELECT max(id) as maxid FROM answers");
		$row_max_survey_id = mysqli_fetch_assoc($max_survey_id);
		$_SESSION['maxid']= $row_max_survey_id['maxid'];
		if(empty($_SESSION['maxid'])){
			$_SESSION['maxid']=1;
		}
		record_set("survey_entry", "SELECT DISTINCT cby FROM answers where surveyid='".$surveyid."'");
		$row_survey_entry = $totalRows_survey_entry;
	}else{
		echo 'Wrong survey ID.'; exit;
	}
}else{
	echo 'Missing survey ID.';  exit;
}

//Submit Survey
if(isset($_POST['submit'])){
    $api_key 		= 'AIzaSyCuDHZB-Yu6rYuIsISs7sSmvtlU8AEDQEo';
    //$translate 		= new TranslateClient(['key' => $api_key]);
    $flagStatus 	= false;
	$answerid 		= $_POST['answerid'];
	$locationid		= $_POST['locationid'];
	$departmentid	= $_POST['departmentid'];
	$groupid		= $_POST['groupid'];
	$roleid			= $_POST['roleid'];
	$questionid		= array_unique($_POST['questionid']);

	//echo '<pre>';

	foreach($questionid as $value){
		$questionid 	= $value;
		$str 			= $answerid[$value];
		$strarray 		= explode("--",$str);
		$strarraycount 	= count($strarray);
		$conditionalFlag = false;
		// echo $strarraycount .' : '.$str; 
		// print_r($strarray);
		if($strarraycount==1){
			//If textbox or textarea
			$ansid = 0;
			$ansttxt = $strarray[0]; 
		}else if($strarraycount==2){
			if(is_numeric($strarray[0])){
				//if radio
				$ansid = $strarray[0];
				$ansttxt = $strarray[1];
			}else{
				$ansid = 0;
				$ansttxt = $strarray[0];
			}
		}else if($strarraycount==0){
			$ansid = -1;
			$ansttxt = $str;
		}
		else if($strarraycount==3){
			$ansid = 0;
			$ansttxt = '';
			$conditionalFlag = true;
		}
		if($ansid<0){
			foreach($ansttxt as $key=>$ansttxtvalue){
				$ansttxtvaluearray = explode("--",$ansttxtvalue);
				//get question data
				record_set("question_val", "SELECT answer FROM questions_detail where id='".$ansttxtvaluearray[0]."' limit 1");
				if($totalRows_question_val>0){
					$row_question_val = mysqli_fetch_assoc($question_val);
					$answerval = $row_question_val['answer'];
				}else{
					$answerval = 100;
				}

				$data = array(

					"locationid"=> $locationid,

					"groupid"=> $groupid,

					"departmentid"=> $departmentid,

					"roleid"	  => $roleid,

					"questionid"=> $key,

					"answerid" => $ansttxtvaluearray[0],

					"surveyid"=>$surveyid,

					"answertext" => $ansttxtvaluearray[1],

					"answerval" => $answerval,

					"cstatus" => "1",

					'cip'=>ipAddress(),

					'cby'=>$_SESSION['maxid'],

					'cdate'=>date("Y-m-d H:i:s")
				);

				$insert_value =  dbRowInsert("answers",$data);
				$flagStatus = true;
			}
		}else{
		    //get question data
			record_set("question_val", "SELECT answer FROM questions_detail where id='".$ansid."' limit 1");
			if($totalRows_question_val>0){
				$row_question_val = mysqli_fetch_assoc($question_val);
				$answerval = $row_question_val['answer'];
			}else{
				$answerval = 100;
			}
			//echo $questionid.' : '.$conditionalFlag;
			if($conditionalFlag){
				$answerval = 0;
				$key = 0;
			}
			$data = array(

				"locationid"	=> $locationid,

				"groupid"		=> $groupid,

				"departmentid"	=> $departmentid,

				"roleid"	  	=> $roleid,

				"questionid"	=> $questionid,

				"answerid" 		=> $ansid,

				"surveyid"		=>	$surveyid,

				"answertext" 	=> $ansttxt,

				"answerval" 	=> $answerval,

				"cstatus" 		=> "1",

				'cip'			=>	ipAddress(),

				'cby'			=>	$_SESSION['maxid'],

				'cdate'			=>	date("Y-m-d H:i:s")

			);
			//print_r($data);
			
			$insert_value =  dbRowInsert("answers",$data);
		    $flagStatus = true;
		}
	}
	$to_be_contacted = 0;
	$to_be_contacted_mail = "";
	$contactArr=[
		'first_name'=>$_POST['first_name'],
		'last_name'=>$_POST['last_name'],
		'phone_number'=>$_POST['phone_number'],
		'to_be_contact_mail'=>$_POST['to_be_contact_mail']
	];
	$contact = json_encode($contactArr);
	if(isset($_POST['to_be_contact']) && $_POST['to_be_contact'] == 1){

		$data_to_be_contact = array(

			"locationid"=> $locationid,

			"groupid"=> $groupid,

			"departmentid"=> $departmentid,

			"roleid"	  => $roleid,

			"questionid"=> 0,

			"answerid" => -2,

			"surveyid"=>$surveyid,

			"answertext" => $contact,

			"answerval" => 100,

			"cstatus" => "1",

			'cip'=>ipAddress(),

			'cby'=>$_SESSION['maxid'],

			'cdate'=>date("Y-m-d H:i:s")

		);
		$insert_to_be_contact =  dbRowInsert("answers",$data_to_be_contact);
		if($insert_to_be_contact){
			$to_be_contacted = 1;
			$to_be_contacted_mail = $_POST['to_be_contact_mail'];
			$flagStatus = true;
		}
	}
	// if(!empty($insert_value)){	
	if($flagStatus){
		$msg = "Question Submitted Successfully";
		// $to_mail = array();
		// record_set("survey_clients", "SELECT name,email FROM clients where FIND_IN_SET(".$locationid.",locationid)");
		// $i = 0;
		
		// if($totalRows_survey_clients > 0){
		// 	while($row_survey_clients = mysqli_fetch_assoc($survey_clients)){
		// 		$to_mail[$i]['name']  = $row_survey_clients['name'];
		// 		$to_mail[$i]['email'] = $row_survey_clients['email'];
		// 		$i++;
		// 	}
		// }

		if(!empty($row_get_survey['alter_email'])){
			$to_mail[$i]['name'] = "User";
			$to_mail[$i]['email'] = $row_get_survey['alter_email'];
		}
		if(count($to_mail) > 0){
			 send_survey_email($to_mail, $row_get_survey['name'], $surveyid, $to_be_contacted, $to_be_contacted_mail,$contact, $_SESSION['maxid']);
		}
	}else{
		$msg = "Some Error Occourd. Please try again..";
	}
	$msg = "Question Submitted Successfully";
	reDirect("survey-thankyou.php?msg=".$msg."&surveyid=".$_REQUEST['surveyid']);

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

	$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];

	$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];

	$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];

	$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['conditional_logic'] = $row_get_questions['conditional_logic'];

	$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['conditional_answer'] = $row_get_questions['conditional_answer'];

	$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['skip_to_question_id'] = $row_get_questions['skip_to_question_id'];

	$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
} 	
	
?>
<!DOCTYPE HTML>

<html lang="en" class="notranslate" translate="no">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo $row_get_survey['name']; ?></title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<link rel="stylesheet" href="dist/css/my-style.css">
		<!-- autocomplete css -->
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta name="google" content="notranslate" />
		<style>

		select{
			font-size: 50px;
			}
			h2 {
				border-bottom:1px solid #000;
				padding:0 0 5px 0;
			}

			table, h4, .form-check{

				text-transform:uppercase;

			}

			body{

				font-family: 'Comfortaa', arial;

			}



			@import url('https://fonts.googleapis.com/css?family=Roboto');



			.signup-step-container{

				padding: 35px 0px;

				padding-bottom: 60px;

			}

			button:focus{

			    outline: 0;

			}

			.wizard .nav-tabs {

			    position: relative;

			    margin-bottom: 0;

			    border-bottom-color: transparent;

			    display: flex;

			    flex-wrap: wrap;

			    margin-top: 3em;

			}



			.wizard > div.wizard-inner {

			    position: relative;

			    margin-bottom: 50px;

			    text-align: center;

			}

			.wizard .nav-tabs li:not(:last-child):after {

			    height: 2px;

			    background: #e0e0e0;

			    position: absolute;

			    width: 100%;

			    left: 0;

			    content: '';

			    z-index: 1;

			    bottom: 0;

			    margin: auto auto;

			    top: 0;

			}



			.wizard .nav-tabs > li.active > a, .wizard .nav-tabs > li.active > a:hover, 

			.wizard .nav-tabs > li.active > a:focus {

			    color: #555555;

			    cursor: default;

			    border: 0;

			    border-bottom-color: transparent;

			}

			span.round-tab {

			    width: 30px;

			    height: 30px;

			    line-height: 30px;

			    display: inline-block;

			    border-radius: 50%;

			    background: #fff;

			    z-index: 2;

			    position: absolute;

			    left: 0;

			    text-align: center;

			    font-size: 16px;

			    color: #0e214b;

			    font-weight: 500;

			    border: 1px solid #ddd;

			}

			span.round-tab i{

			    color:#555555;

			}

			.wizard li.active span.round-tab {

			    background: #0d90b7;

			    color: #fff;

			    border-color: #0d90b7;

			}

			.wizard li.active span.round-tab i{

			    color: #5bc0de;

			}

			.wizard .nav-tabs > li.active > a i {

			    color: #0d90b7;

			}



			.wizard .nav-tabs > li {

			    flex: auto;

			}

			.wizard .nav-tabs > li:last-child {

			    flex: none;

			}

			.nav-tabs>li{

				float: unset !important;

			}

			.wizard .nav-tabs > li a {

			    width: 30px;

			    height: 30px;

			    margin: 20px 0;

			    border-radius: 100%;

			    padding: 0;

			    background-color: transparent;

			    position: relative;

			    top: 0;

			}

			.wizard .nav-tabs > li a i {

			    position: absolute;

			    top: -15px;

			    font-style: normal;

			    font-weight: 400;

			    white-space: nowrap;

			    left: 50%;

			    transform: translate(-50%, -50%);

			    font-size: 16px;

			    font-weight: 500;

			    color: #0d90b7;

			}

			.wizard .tab-pane {

			    position: relative;

			    padding-top: 20px;

			}



			.prev-step,

			.next-step{

			    font-size: 13px;

			    padding: 8px 24px;

			    border: none;

			    border-radius: 4px;

			    margin-top: 30px;

			}

			.next-step {

			    background-color: #0d90b7;

			    color: #fff;

			}

			.skip-btn{

				background-color: #cec12d;

			}

			.signup-logo-header .nav > li{

				padding: 0;

			}

			.list-inline li{

			    display: inline-block;

			}

			.error{

				color:red !important;

			}

			.finalSubmit {

				background-color: #0d90b7;

				color: #fff;

				font-size: 13px;

				padding: 8px 24px;

				border: none;

				border-radius: 4px;

				margin-top: 30px;

			}

			.smile-block.active .smily_icon {

				border-radius: 25px;

				border-style: solid;

				border-width: 5px;

				border-color: #3c3e3e;

				box-shadow: 7px 1px 7px #8a8686;

			}

			

@media only screen and (max-width: 800px) {

.langselect {

    margin-top: 1rem;

}

}

@media only screen and (min-width: 800px) {

.langselect {

    max-width: 130px;

    float: right;

}

}

			<?php echo $row_get_survey['css_txt']; ?>

		</style>

	</head>

<body>



<section class="signup-step-container">

    <div class="container">



    	<div align="center">
    		<img src="<?=MAIN_LOGO?>" width="200">
    	</div>
		<h2 align="center">

			<?php echo $row_get_survey['name']?>

		</h2>

	  	<?php

		  	$survey_needed = $row_get_survey['survey_needed'];

			if(empty($survey_needed)){

				$survey_needed = 9999999999999999999999999999999999;

			}

			  

			if($row_survey_entry>$survey_needed){

				echo '<div class="alert alert-danger" role="alert"> Survey closed.</div>'; exit;

			}



			if(isset($_GET['msg']))

			{

		  	?>

		  	<div class="alert alert-success" role="alert"> <?php echo $_GET['msg']; ?> </div>

		  	<?php 

		  	} 

	  	?>

        <div class="row">

            <div class="col-lg-12">

                <div class="wizard">

                	<?php if(count($survey_steps) > 0){ ?>

                    <div class="wizard-inner">

                    	

                        <div class="connecting-line"></div>

                        <ul class="nav nav-tabs" role="tablist">

                    		<?php foreach($survey_steps AS $survey_step){ ?>

		                    <li role="presentation" class="<?php if($survey_step['number'] == 1){ ?>active<?php } ?>">

		                        <a href="#step<?php echo $survey_step['number']; ?>" data-toggle="tab" aria-controls="step<?php echo $survey_step['number']; ?>" role="tab" aria-expanded="true"><span class="round-tab <?php echo ($survey_step['number']>1)?'tab-next':'tab-prev'; ?>"><?php echo $survey_step['number']; ?></span> <i class="<?php echo ($survey_step['number']>1)?'tab-next':'tab-prev'; ?>">Step <?php echo $survey_step['number']; ?></i></a>

		                    </li>

		                	<?php } ?>

                        </ul>

                    </div>

    				<?php } ?>



                    <form id="surveyForm" role="form" method="POST" class="login-box">

                        <div class="tab-content" id="main_form">

                        	<?php 

                        	$new_survey_steps = $survey_steps;

                        	if(count($survey_steps) <= 0){

                        	$new_survey_steps = array("0" => array("number" => 1, "title" => ""));

                        	}

                        	?>

                        	<?php foreach($new_survey_steps AS $key => $value) { ?>

                            <div class="tab-pane <?php if($value['number'] == 1){ ?>active<?php } ?>" role="tabpanel" id="step<?php echo $value['number']; ?>">

                                <h4 class="text-center"><?php echo $value['title']; ?></h4>

                                <?php if($value['number'] == 1){ ?>

                                <div class="row">	



									<!-- Start Schools -->

									<?php

										if($row_get_survey['isSchoolAllowed'] == 1){

									?>

										<!-- <div class="col-md-3"></div>

										<div class="col-md-6">

											<div class="form-group">

												<label for="locationid">

													<?php echo 'Please tell us the name of the school or <br> other educational establishment travelled to or from?'?>

												</label>

												<input type="text" name="school_name" id="school_name" class="form-control form-control-lg school_autocomplete" required>

												<input type="hidden" name="locationid"  class="form-control form-control-lg school_value">

											</div>

										</div>

										<div class="col-md-3"></div> -->

									<?php
										}
									?>
									<!-- Ends Schools -->

									<!-- Start groups -->
									<?php 
									if(!empty($row_get_survey['groups'])){ 
										record_set("get_group", "select * from groups where id in(".$row_get_survey['groups'].") AND id != 4 AND cstatus=1 order by name asc");	
										if($totalRows_get_group == 1){
											while($row_get_group = mysqli_fetch_assoc($get_group)){
												echo '<input type="hidden" name="groupid" value="'.$row_get_group['id'].'">';
											}
										}else{ 
											echo ($totalRows_get_location==1)?'<div class="col-md-3"></div>':'';?>
											<div class="col-md-6">
												<div class="form-group">
													<label for="groupid">group</label>
													<select name="groupid" id="groupid" class="form-control form-control-lg" required>
														<option value="">Please select</option>
														<?php	
															while($row_get_group = mysqli_fetch_assoc($get_group)){	?>
														<option value="<?php echo $row_get_group['id'];?>"><?php echo $row_get_group['name']?></option>
														<?php  } ?>
													</select>
												</div>
											</div>	
											<?php echo ($totalRows_get_location==1)?'<div class="col-md-3"></div>':'';?>
											<?php
										}
									} 
									?>	
									<!-- End Group -->

									<!-- Start Locations -->
									<?php 
									if(!empty($row_get_survey['locations'])){ 
										record_set("get_location", "select * from locations where id in(".$row_get_survey['locations'].") AND id != 4 AND cstatus=1 order by name asc");	
										if($totalRows_get_location == 1){
											while($row_get_location = mysqli_fetch_assoc($get_location)){
												echo '<input type="hidden" name="locationid" value="'.$row_get_location['id'].'">';
											}
										}else{ 
											echo ($totalRows_get_department==1)?'<div class="col-md-3"></div>':'';?>
											<div class="col-md-6">
												<div class="form-group">
													<label for="locationid">Location</label>
													<select name="locationid" id="locationid" class="form-control form-control-lg" required>
														<option value="">Please select</option>
														<?php	
															while($row_get_location = mysqli_fetch_assoc($get_location)){	

														?>
														<option value="<?php echo $row_get_location['id'];?>"><?php echo $row_get_location['name']?></option>
														<?php  } ?>
													</select>
												</div>
											</div>	
											<?php echo ($totalRows_get_department==1)?'<div class="col-md-3"></div>':'';?>
											<?php
										}
									} 
									?>	
									<!-- End Locations -->
									<!-- Start Department -->
									<?php 
										if(!empty($row_get_survey['departments'])){
											record_set("get_department", "select * from departments where id in(".$row_get_survey['departments'].") AND cstatus=1 and id != 4");				
											record_set("get_location", "select * from locations where id in(".$row_get_survey['locations'].") AND id != 4 AND cstatus=1 order by name asc");

											if($totalRows_get_department == 1){
												while($row_get_department = mysqli_fetch_assoc($get_department)){
													echo '<input type="hidden" name="departmentid" value="'.$row_get_department['id'].'">';
												}
											}else{
												echo ($totalRows_get_location==1)?'<div class="col-md-3"></div>':'';?>
												<div class="col-md-6">

													<div class="form-group">

														<label for="departmentid">Department</label>

														<select name="departmentid" id="departmentid" class="form-control form-control-lg" required>
															<option value="">Please select</option>
															<?php

																while($row_get_department = mysqli_fetch_assoc($get_department)){	

															?>

																<option value="<?php echo $row_get_department['id'];?>"><?php echo $row_get_department['name'];?></option>

															<?php } ?>

														</select>

													</div>

												</div>
												<?php echo ($totalRows_get_location==1)?'<div class="col-md-3"></div>':'';
											} 
										}
									?>
									<!-- End Department -->

									<!-- Start Roles -->
									<?php 
										if(!empty($row_get_survey['roles'])){
											record_set("get_role", "select * from roles where id in(".$row_get_survey['roles'].") AND cstatus=1 and id != 4");				
											record_set("get_department", "select * from departments where id in(".$row_get_survey['departments'].") AND id != 4 AND cstatus=1 order by name asc");

											if($totalRows_get_role == 1){
												while($row_get_role = mysqli_fetch_assoc($get_role)){
													echo '<input type="hidden" name="roleid" value="'.$row_get_role['id'].'">';
												}
											}else{
												echo ($totalRows_get_department==1)?'<div class="col-md-3"></div>':'';?>
												<div class="col-md-6">

													<div class="form-group">

														<label for="roleid">Role</label>

														<select name="roleid" id="roleid" class="form-control form-control-lg" required>
															<option value="">Please select</option>
															<?php

																while($row_get_role = mysqli_fetch_assoc($get_role)){	

															?>

																<option value="<?php echo $row_get_role['id'];?>"><?php echo $row_get_role['name'];?></option>

															<?php } ?>

														</select>

													</div>

												</div>
												<?php echo ($totalRows_get_department==1)?'<div class="col-md-3"></div>':'';
											} 
										}
									?>
									<!-- End Roles -->

								</div>

	                        	<?php } ?>

	                        	<?php 

	                        	$eachindex = 0;

	                        	foreach($questions[$key] AS $question){
	                        	  $questionid = $question['id'];  ?>

	                        	<div class="question-div question_container_<?php echo $questionid; ?>">
								<div class="col-md-12">
								  <h4><?php echo $question['question'];?><?php if($question['ifrequired']==1){ ?>  <?php } ?></h4>
								</div>	
	                        	

	                        	<!-- When Answer Type 1 -->
	                        	<?php 
									if($question['answer_type'] == 1){  

									//get Questions

									record_set("get_child_questions", "select * from questions where parendit='".$questionid."' and cstatus='1'");

									//get Questions
									record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'  ");

									if($totalRows_get_child_questions>0){ ?>
										<table class="table table-hover table-bordered">

											<tbody>

												<tr align="center">

												<?php

												$child_answer = array();

												$sub_answer = array();

												$tdloop = 0;

												while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){ $tdloop++; ?>

													<td>

													<?php

													$child_answer[$row_get_questions_detail['id']]=$row_get_questions_detail['description'];
													echo $row_get_questions_detail['description'];?>	
													</td>
												<?php } ?>

												</tr>

											<?php while($row_get_child_questions = mysqli_fetch_assoc($get_child_questions)){ ?>

											<tr>
												<td colspan="<?php echo count($child_answer); ?>"><strong><?php echo $row_get_child_questions['question'];?></strong></td>
											</tr>
											<tr align="center">
											<?php 
											if($row_get_child_questions['parendit'] == 0){
											record_set("get_answer_detail", "select * from questions_detail where questionid='".$row_get_child_questions['id']."' and surveyid='".$surveyid."' and cstatus='1'  ");

											if($totalRows_get_answer_detail>0){

												echo'<input type="hidden" name="questionid[]" value="'.$row_get_child_questions['id'].'">';

												while($row_get_answer_detail = mysqli_fetch_assoc($get_answer_detail)){
													// echo $row_get_answer_detail['description'];
													$sub_answer[$row_get_answer_detail['id']]= $row_get_answer_detail['description'];
												}

											?>

											<?php

												foreach($sub_answer as $key=>$child_answer_option){

											?>



											<td colspan="<?php echo count($sub_answer); ?>"><input type="radio" class="form-check-input subque" name="answerid[<?php echo $questionid; ?>][<?php echo $row_get_child_questions['id'];?>]" 
											value="<?php echo $key; ?>--<?php echo $child_answer_option; ?>"  <?php if($question['ifrequired']==1){ ?> required <?php } ?> data-questionid="<?php echo $questionid;?>"
											> <?php echo $child_answer_option; ?></td>



											<?php

												}

											}



											}else{



											?>

											<?php

											foreach($child_answer as $key=>$child_answer_option){ ?>

											<td><input type="radio" class="form-check-input" name="answerid[<?php echo $questionid; ?>][<?php echo $row_get_child_questions['id'];?>]" value="<?php echo $key; ?>--<?php echo $child_answer_option; ?>"  <?php if($question['ifrequired']==1){ ?> required <?php } ?> data-questionid="<?php echo $questionid;?>"></td>



											<?php } ?>



											<?php } ?>

											</tr>

											<?php } ?>

											</tbody>

										</table>
										<?php 
									}else {
										while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
										$langRadioAnsVal= $row_get_questions_detail['description'];	
										?>
										
										<div class="form-check col-md-2">
											<label class="form-check-label">
												<input type="radio" class="form-check-input subque" name="answerid[<?php echo $questionid; ?>]" value="<?php echo $row_get_questions_detail['id']."--".$langRadioAnsVal?>"  <?php if($question['ifrequired']==1){ ?> required <?php } ?> data-questionid="<?php echo $questionid;?>"
												data-condlogic="<?php echo $question['conditional_logic'];?>"
												data-condans="<?php echo $question['conditional_answer'];?>"
												data-skiptoquestion="<?php echo $question['skip_to_question_id'];?>"
												data-currentanswer="<?=$row_get_questions_detail['answer']?>"
												>
												<?php echo $row_get_questions_detail['description'];?> 
											</label>
										</div>
									<?php } ?>
									<?php } ?>	

									<span class="viewQuestion<?php echo $questionid;?>"></span>
									<input type="hidden" name="questionid[]" value="<?php echo $questionid; ?>">
								<?php } ?>

	                        	<!-- End Answer Type 1 -->

	                        	<!-- When Answer Type 2 -->

	                        	<?php if($question['answer_type'] == 2){ ?>

									<div class="form-group">

										<input type="text" name="answerid[<?php echo $questionid; ?>]" value="" class="form-control" <?php if($question['ifrequired']==1){ ?> required <?php } ?>>

										<input type="hidden" name="questionid[]" value="<?php echo $questionid; ?>">

									</div>	

	                        	<?php } ?>

	                        	<!-- End Answer Type 2 -->

	                        	<!-- When Answer Type 3 -->

	                        	<?php if($question['answer_type'] == 3){ ?>

									<div class="form-group">

										<textarea name="answerid[<?php echo $questionid; ?>]"  id="answerid_<?php echo $eachindex; ?>" value="" class="form-control" <?php if($question['ifrequired']==1){ ?> required <?php } ?>></textarea>

									<input type="hidden" name="questionid[]" value="<?php echo $questionid; ?>">

									</div>

	                        	<?php } ?>

	                        	<!-- End Answer Type 3 -->

	                        	<!-- When Answer Type 4 -->

	                        	<?php 

	                        	if($question['answer_type'] == 4){ 

	                        		//get Questions

									record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'  ");

									if($totalRows_get_questions_detail>0){

	                        		?>

									

	                        	    <table class="table table-hover table-bordered">

								      <tbody>

								        <tr>

								          <?php

									  $child_answer = array();

									  $tdloop = 0;

									  //$question_options = array();

									  	while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
										  $tdloop++; ?>
											<td align="center">
												<?php
													$child_answer[$row_get_questions_detail['id']]= $row_get_questions_detail['description'];
													echo $row_get_questions_detail['description'];
												?>
											</td>
								          <?php } ?>
								        </tr>
								        <?php
										  $ans_count = 0;

										  $show_smily = 0;

										  $smily_loop = 0;

										  $ans_count = count($child_answer);

										  if($ans_count==2 || $ans_count==3 || $ans_count==5 || $ans_count==11){

											  $show_smily = 1;

										  }

										  ?>

								        <tr align="center" <?php if($ans_count==2){ ?> class="yesno" <?php } ?>>

								          <?php

										  foreach($child_answer as $key=>$child_answer_option){ ?>

								          <td class="show_smily_<?php echo $show_smily; ?> smile-block"><label>

								          <?php if($show_smily==1){ ?> 

											<div>

													<img style="width:40px" class="smily_icon" src="<?php if($ans_count==2){ echo "dist/img/".strtolower($child_answer_option).".png"; }else{ echo smile_format_icon($smily_loop,$ans_count); } ?>">

											</div>

										  <?php

										  	$smily_loop++;

											//$show_smily++;

										  } ?>



											<input <?php if($show_smily==1){ ?> style="visibility:hidden;" <?php } ?> type="radio" class="form-check-input <?php if($show_smily==1){ ?> smily_icon_input <?php } ?> subque" name="answerid[<?php echo $question['id']; ?>]" value="<?php echo $key; ?>--<?php echo $child_answer_option; ?>"  <?php if($question['ifrequired']==1){ ?> required <?php } ?> data-questionid="<?php echo $question['id']; ?>">

								          </label>

								          </td>

								          <?php } ?>

								        </tr>

								      </tbody>

								    </table>

								    <input type="hidden" name="questionid[]" value="<?php echo $questionid; ?>">

									<span class="viewQuestion<?php echo $questionid;?>"></span>

	                        	<?php 

	                        		}

	                        	} ?>

	                        	<!-- End Answer Type 4 -->

	                        	<!-- When Answer Type 5 -->

	                        	<?php 

	                        	if($question['answer_type'] == 5){ 

		                        	record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'  ");

									if($totalRows_get_questions_detail>0){

										while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){

	                        	 ?>

									<h5>

										<?php

											 echo $row_get_questions_detail['description']; 

										?>

									</h5>

	                        	<?php 

				                        }

				                    }

	                        	}

	                        	?>

	                        	<!-- End Answer Type 5 -->

	                        	<!-- When Answer Type 6 -->

	                        	<?php 

	                        	if($question['answer_type'] == 6){

	                        	?>

									<div class="form-group">

										<select name="answerid[<?php echo $question['id']; ?>]" <?php if($question['ifrequired'] == 1){ ?> required <?php } ?> class="form-control subque_select" data-questionid="<?php echo $question['id']; ?>">

											<option value="">Select</option>

											<?php 

											record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'  ");

											if($totalRows_get_questions_detail>0){

											while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){

											?>

											<option value="<?php echo $row_get_questions_detail['id'].'--'.$row_get_questions_detail['answer']; ?>"><?php echo $row_get_questions_detail['description']; ?></option>

											<?php 

												}

											}

											?>

										</select>

										<input type="hidden" name="questionid[]" value="<?php echo $questionid; ?>">

									</div>

								  <span class="viewQuestion<?php echo $questionid;?>"></span>

	                        	<?php 

	                        	}

	                        	?>

	                        	<!-- End Answer Type 6 -->

	                        	<?php

	                        	$eachindex++; 

	                        	?>	

	                        	</div>	

	                        	<?php 

	                        	} 

	                        	?>

	                        	<?php if($value['number'] == count($survey_steps) && $row_get_survey['isEnableContacted'] == 1){ ?>

	               

	                    		<h4>Can HATS Group contact you about your comments/feedback?</h4>

	                    		<div class="form-group">

	                    		    <div class="form-check contact">

									<input class="form-check-input"  type="radio" name="to_be_contact" id="to_be_contact_yes" value="1" style="visibility:hidden;"  required>Yes

									  <label class="form-check-label" for="to_be_contact_yes">

									    <img style="width:40px" class="smily_icon" src="dist/img/yes.png">

									  </label>

									</div>

									<div class="form-check">

									<input class="form-check-input" type="radio" name="to_be_contact" id="to_be_contact_no" value="0" style="visibility:hidden;"  required>No

									  <label class="form-check-label" for="to_be_contact_no">

									   <img style="width:40px" class="smily_icon" src="dist/img/no.png">

									  </label>

									</div>

								    

								</div>



								<div class="form-group" id="to_be_contact_mail_div">

									<div class="row">

										<div class="col-md-6">

											<input type="text" class="form-control fname" id="fname" name="first_name" placeholder="Your first name" required>

										</div>

										<div class="col-md-6">

											<input type="text" class="form-control" id="lname" name="last_name" placeholder="Your last name" required>

										</div>

									</div>

									<div class="row">

										<div class="col-md-6">

											<input type="email" class="form-control" id="to_be_contact_mail" placeholder="Your email" name="to_be_contact_mail" required> 

										</div>

										<div class="col-md-6">

											<input type="number" class="form-control" id="phone" name="phone_number" placeholder="Your phone number" required>

										</div>

									</div> 

									<div class="row">

										<div class="col-md-12">
											<input type="checkbox" id="accept_privacy" name="accept_privacy" value="agree">
											<label for="accept_privacy">Please confirm you agree with <a href="https://www.datagroupsolutions.com/privacy-policy/" target="_blank">our privacy policy</a>  </label>
											<br>
										</div>

									</div> 

								</div>

	                        	<?php } ?>

	                        	<?php if(count($survey_steps) > 0){ ?>

                                <ul class="list-inline text-center">

                                    <?php if($value['number'] == 1){ ?>

		                            <li>

										<button type="button" class="default-btn next-step">
											Continue to next step
										</button>

									</li>

		                        	<?php } ?>

		                        	<?php if($value['number'] > 1){ ?>
		                            <li>
										<button type="button" class="default-btn prev-step">
										Back
										</button>

									</li>

		                        	<?php } ?>

		                        	<?php if($value['number'] == count($survey_steps)) { ?>

		                            <li><input type="submit" name="submit" class="default-btn finalSubmit submitform" id="submit" value="Finish"></li>

		                            <?php }else if($value['number'] > 1 && $value['number'] < count($survey_steps)){ ?>

		                            <li>

										<button type="button" class="default-btn next-step">
											Continue
										</button>
									</li>
		                            <?php } ?>
                                </ul>

                            <?php }else{ ?>
								<div class="col-md-12">
								<input type="submit" name="submit" class="default-btn submitform" id="submit" value="Submit">
								</div>

                            <?php } ?>

                            </div>

                            <?php } ?>

                            <div class="clearfix"></div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- partial script-->

<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>

<script src='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js'></script>

<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js'></script>

<!-- jQuery UI Autocomplete-->

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script type="text/javascript">

// ------------step-wizard-------------

$(document).ready(function () {

	$("#to_be_contact_mail_div").hide();

	

	$('body').on('click', '.smily_icon', function () {



		$(this).closest("tr").find("td").css("background-color", "#FFF");

		$(this).closest("td").css("background-color", "#FFF");



		$(this).closest("tr").find("td").removeClass("active");

		$(this).closest("td").addClass('active');



	});



	$('input[type=radio][name=to_be_contact]').change(function() {

	    if (this.value == '1') {

			$('#to_be_contact-error').remove();

	        $("#to_be_contact_mail_div").show();

	        $("#accept_privacy").attr("required", "required");

	    } else {

			$('#to_be_contact-error').remove();

	        $("#to_be_contact_mail_div").hide();

	        $("#accept_privacy").removeAttr("required");

	    }

	});



    $('.nav-tabs > li a[title]').tooltip();

    

    //Wizard

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {



        var target = $(e.target);

    

        if (target.parent().hasClass('disabled')) {

            return false;

        }

    });





    $(".next-step,.tab-next").click(function (e) {

    	$("#surveyForm").validate().settings.ignore = ":disabled,:hidden";

        if($("#surveyForm").valid()){

        	var active = $('.wizard .nav-tabs li.active');

	        active.next().removeClass('disabled');

	        nextTab(active);

        }else{

        	return false;

        }

    });



	



    $(".prev-step,.tab-prev").click(function (e) {



        var active = $('.wizard .nav-tabs li.active');

        prevTab(active);



    });



	

	$('.next-step, .prev-step').click(function(){

		$('html, body').animate({scrollTop:0});

		return false;

	});



	$('.finalSubmit').click(function(){

		$("#surveyForm").validate().settings.ignore = ":disabled,:hidden";

        if($("#surveyForm").valid()){

        	var active = $('.wizard .nav-tabs li.active');

	        active.next().removeClass('disabled');

	        nextTab(active);

        }else{

        	return false;

        }

	});



	$.extend($.validator.messages, {

	    required: "This field is required"

	});



});



function nextTab(elem) {

    $(elem).next().find('a[data-toggle="tab"]').click();

}

function prevTab(elem) {

    $(elem).prev().find('a[data-toggle="tab"]').click();

}





$('.nav-tabs').on('click', 'li', function() {

    $('.nav-tabs li.active').removeClass('active');

    $(this).addClass('active');

});	



<?php

if($row_get_survey['isSchoolAllowed'] == 1){

?>

// $(function() {



// 	var langId = 0;



// 	$( ".school_autocomplete" ).autocomplete({

// 		// source: 'ajax/ajaxOnSelectSchool.php'+$(this).val(),

// 		source: function(request, response) {

// 		$.getJSON('ajax/ajaxOnSelectSchool.php', 

// 			{ 

// 				term: request.term,

// 				langId: langId

// 			}, 

// 		response);

// 		},

// 		focus: function(event, ui) {

// 					// prevent autocomplete from updating the textbox

// 					event.preventDefault();

// 					// manually update the textbox

// 					$(this).val(ui.item.label);

// 		},

// 		select: function(event, ui) {

// 			// prevent autocomplete from updating the textbox

// 			event.preventDefault();

// 			// manually update the textbox and hidden field

// 			$(this).val(ui.item.label);

// 			$(".school_value").val(ui.item.value);

// 		},

// 		change:function(event,ui){

// 			event.preventDefault();

// 			if(ui.item == null){

// 				$(".school_autocomplete").val('');

// 				$(".school_value").val('');  

// 			}

// 		}

// 	});

	

// });

<?php } ?>

$(document).ready(function() {

  $(window).keydown(function(event){

    if(event.keyCode == 13) {

      event.preventDefault();

      return false;

    }

  });

});



$('.subque').change(function(){
	if($(this).is(':checked')){
		var view_question_id = $(this).data('questionid');
		var questionDetailId =$(this).val();
		var questionCurrentValue =$(this).data('currentanswer');
		var surveyId = <?php echo $surveyid;?>;
		var parentqueid = <?php echo (isset($questionid))?$questionid:'0';?>;
		//var langId = <?php echo (!empty($_GET['langid']))?$_GET['langid']:'0'; ?>;
		var skiptoquestion = $(this).data('skiptoquestion');
		var condans = $(this).data('condans');
		var condlogic = $(this).data('condlogic');
		let min_id = view_question_id;
		let max_id = skiptoquestion;
		if(min_id>max_id){
			min_id = max_id;
			max_id = view_question_id;
		}
		let counter = max_id-min_id;
		let startId = parseInt(min_id)  + 1;
		console.log("skipto : ",skiptoquestion,"condans : ",condans,"condlogic : ",condlogic);
		console.log(min_id,max_id);

		if(condlogic == 1 && questionCurrentValue == condans && skiptoquestion>0){
			for(let i = startId; i<max_id; i++){
				let values = $(".question_container_"+i).find('input').val();
				if(values !=undefined){
					const myArray = values.split("--");
					const cleanArray = myArray.filter((a) => a);
					let newValues = cleanArray.join("--");
					newValues = '--'+newValues;
					$(".question_container_"+i).find('input').prop('required',false);
					$(".question_container_"+i).find("input[name='answerid["+i+"]']").val(newValues);
					$(".question_container_"+i).hide();
				}
				
			}
		}else if(condlogic == 2 && questionCurrentValue != condans && skiptoquestion>0){
			for(let i = startId; i<max_id; i++){
				let values = $(".question_container_"+i).find('input').val();
				if(values !=undefined){
					const myArray = values.split("--");
					const cleanArray = myArray.filter((a) => a);
					let newValues = cleanArray.join("--");
					newValues = '--'+newValues;
					$(".question_container_"+i).find('input').prop('required',false);
					$(".question_container_"+i).find("input[name='answerid["+i+"]']").val(newValues);
					$(".question_container_"+i).hide();
				}
			}
		}else {
			for(let i = startId; i<max_id; i++){
				let values = $(".question_container_"+i).find('input').val();
				if(values !=undefined){
					const myArray = values.split("--");
					const cleanArray = myArray.filter((a) => a);
					let newValues = cleanArray.join("--");
					$(".question_container_"+i).find('input').prop('required',true);
					$(".question_container_"+i).find("input[name='answerid["+i+"]']").val(newValues);
					$(".question_container_"+i).show();
				}
			}
		}
		
		// old conditional Question

		// $.ajax({
		// 	type: "POST",
		// 	url: 'ajax/ajaxGetQuestionOnSelectAnswer.php',
		// 	data: {questionDetailId: questionDetailId,surveyId: surveyId,parentqueid: parentqueid}, 
		// 	success: function(response)
		// 	{
		// 		if (response != '') {
		// 			$('.viewQuestion'+view_question_id).html(response);
		// 		}else{
		// 			$('.viewQuestion'+view_question_id).html('');
		// 		}
		// 	}
		// });
			console.log(questionDetailId,surveyId,parentqueid);
		$.ajax({
			type: "POST",
			url: 'ajax/ajaxGetQuestionOnSelectAnswer.php',
			data: {questionDetailId: questionDetailId,surveyId: surveyId,parentqueid: parentqueid}, 
			success: function(response)
			{
				// if (response != '') {
				// 	$('.viewQuestion'+view_question_id).html(response);
				// }else{
				// 	$('.viewQuestion'+view_question_id).html('');
				// }
			}
		});

	}
});



$('.subque_select').change(function(){

	var view_question_id = $(this).data('questionid');

	var questionDetailId =$(this).val();

	var surveyId = <?php echo $surveyid;?>;

	var parentqueid = <?php echo (isset($questionid))?$questionid:'0';?>;

	var langId = <?php echo (!empty($_GET['langid']))?$_GET['langid']:'0'; ?>;

	$.ajax({

		type: "POST",

		url: 'ajax/ajaxGetQuestionOnSelectAnswer.php',

		data: {questionDetailId: questionDetailId,surveyId: surveyId,parentqueid: parentqueid,langId: langId}, 

		success: function(response)

		{

			if (response != '') {

				$('.viewQuestion'+view_question_id).html(response);

			}else{
				$('.viewQuestion'+view_question_id).html('');
			}
		}
	});
});



$('#langid').change(function(){

	window.location.replace(window.location.origin+window.location.pathname+'?surveyid='+<?=$surveyid ?>+'&langid='+$(this).val());

});

</script>
<div style="text-align: center;">
Powered by Datagroup Solutions
<center><img  src="https://www.datagroupsolutions.com/wp-content/uploads/2020/11/Data-Group-Solutions-survey.png" alt="" width="200" height="36" /></center>
</div>
</body>

</html>