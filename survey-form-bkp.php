<?php include('function/function.php');
//print_r($_REQUEST);
$surveyid=$_GET['surveyid'];
if(isset($_GET['surveyid'])){
	record_set("get_survey", "select * from surveys where id='".$surveyid."' and cstatus=1 ");	
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
if(isset($_POST['submit'])){
//var_dump($_POST);
$answerid = $_POST['answerid'];
//print_r($answerid);
$locationid=$_POST['locationid'];
$departmentid=$_POST['departmentid'];
$questionid=array_unique($_POST['questionid']);

foreach($questionid as $value){
	//echo 'ans for question '. $value;
	$questionid = $value;
	$str = $answerid[$value];
	$strarray = explode("--",$str);
	//print_r($strarray);
	$strarraycount = count($strarray);
	
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
	//echo $questionid.'===='.$ansid.'===='.$ansttxt.'####1';
	//print_r ($strarray);
	//print_r($answerid[$value]);

	if($ansid<0){
		foreach($ansttxt as $key=>$ansttxtvalue){
			$ansttxtvaluearray = explode("--",$ansttxtvalue);
			//get question data
			record_set("question_val", "SELECT answer FROM questions_detail where id='".$ansttxtvaluearray[0]."' limit 1");
			if($totalRows_question_val>0){
				$row_question_val = mysqli_fetch_assoc($question_val);
				$answerval = $row_question_val['answer'];
			}else{
				$answerval = 10;
			}
			$data =  array(
					//"questionid"=> $questionid,
					"locationid"=> $locationid,
					"departmentid"=> $departmentid,
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
		}
	}else{
		//get question data
			record_set("question_val", "SELECT answer FROM questions_detail where id='".$ansid."' limit 1");
			if($totalRows_question_val>0){
				$row_question_val = mysqli_fetch_assoc($question_val);
				$answerval = $row_question_val['answer'];
			}else{
				$answerval = 10;
			}
		$data =  array(
				"locationid"=> $locationid,
				"departmentid"=> $departmentid,
				"questionid"=> $questionid,
				"answerid" => $ansid,
				"surveyid"=>$surveyid,
				"answertext" => $ansttxt,
				"answerval" => $answerval,
				"cstatus" => "1",
				'cip'=>ipAddress(),
				'cby'=>$_SESSION['maxid'],
				'cdate'=>date("Y-m-d H:i:s")
			);
		$insert_value =  dbRowInsert("answers",$data);
		$to_be_contacted = 0;
		if($insert_value){
			if($answertext=='YES'){
				$to_be_contacted = 1;
			}
		}
	}
	//echo "=============";
}
//exit;
//print_r($data);die;
if(!empty($insert_value )){	
	$msg = "Question Submitted Successfully";
	record_set("survey_client", "SELECT locationid FROM clients where id='".$row_get_survey['clientid']."'");
	$row_survey_client = mysqli_fetch_assoc($survey_client);
	$locations = explode(",", $row_survey_client['locationid']);
	if(in_array($locationid, $locations)){	
		send_survey_email($row_get_survey['alter_email'],$row_get_survey['name'],$surveyid,$to_be_contacted,$_SESSION['maxid']);
	}
}else{
	$msg = "Some Error Occourd. Please try again..";
}
	reDirect("survey-thankyou.php?msg=".$msg."&surveyid=".$_REQUEST['surveyid']);
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $row_get_survey['name']; ?></title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<link rel="stylesheet" href="dist/css/my-style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
h2 {
	border-bottom:1px solid #000;
	padding:0 0 5px 0;
}
<?php echo $row_get_survey['css_txt']; ?>
</style>
</head>

<body>
<div class="container">
<div align="center"><img src="upload_image/logo.png" width="200"></div>
  <h2 align="center"><?php echo $row_get_survey['name']; ?></h2>
  <?php
  //echo $row_survey_entry.'=='.$row_get_survey['survey_needed'];
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
   <!--<p>At HATS Group we are committed to providing a high-quality service for all our patients and will do everything possible to make sure that your transport is as comfortable as possible. We are always seeking feedback and comments about our service as this helps us to continuously improve what we do and helps develop the services we provide for our patients and visitors.</p>-->
  <form method="post">
  <div class="row">
	  <div class="col-md-6">
	  	<div class="form-group">
		    <label for="departmentid">Department</label>
		    <select name="departmentid" id="departmentid" class="form-control form-control-lg" required>
		    	<option value="">Please select</option>
		  		<?php
                record_set("get_department", "select * from departments where cstatus=1 and id != 4");				
				while($row_get_department = mysqli_fetch_assoc($get_department)){	
				?>
                <option value="<?php echo $row_get_department['id'];?>"><?php echo $row_get_department['name'];?></option>
                <?php }?>
		  	</select>
		</div>
	  </div>
	  <div class="col-md-6">
	  	<div class="form-group">
		    <label for="locationid">Location</label>
		    <select name="locationid" id="locationid" class="form-control form-control-lg" required>
		    	<option value="">Please select</option>
				<?php
				record_set("get_location", "select * from locations where cstatus=1 and id != 4 order by name asc");				
				while($row_get_location = mysqli_fetch_assoc($get_location)){	
				?>
				<option value="<?php echo $row_get_location['id'];?>"><?php echo $row_get_location['name'];?></option>
				<?php }?>
		  	</select>
		</div>
	  </div>
  </div>
  <div class="col-md-12">
    <?php 
	record_set("get_questions", "select * from questions where surveyid='".$surveyid."' and cstatus='1' and parendit='0' order by dposition asc");	
$index=0;	
	while($row_get_questions = mysqli_fetch_assoc($get_questions)){
		$questionid=$row_get_questions['id'];
		//echo $row_get_questions['answer_type'];
	?>
    <div class="question_container_<?php echo $row_get_questions['id']?>">
    <h4><?php echo $row_get_questions['question']?><?php if($row_get_questions['ifrequired']==1){ ?> * <?php } ?>
	<?php //echo $row_get_questions['ifrequired']?></h4>
    <?php
  		if($row_get_questions['answer_type']==1){
			//Radio
			
			//get Questions
			record_set("get_child_questions", "select * from questions where parendit='".$questionid."' and cstatus='1'");
			
			//get Questions
			record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'  ");
			if($totalRows_get_child_questions>0){
				?>
    <table class="table table-hover table-bordered">
      <tbody>
        <tr align="center">
          <?php
	  $child_answer = array();
	  $tdloop = 0;
	  while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){ $tdloop++; ?>
          <td><?php
	  $child_answer[$row_get_questions_detail['id']]= $row_get_questions_detail['description'];
	  echo $row_get_questions_detail['description']; ?></td>
          <?php } ?>
        </tr>
        <?php while($row_get_child_questions = mysqli_fetch_assoc($get_child_questions)){ ?>
        <tr>
          <td colspan="<?php echo count($child_answer); ?>"><strong><?php echo $row_get_child_questions['question']?></strong></td>
        </tr>
        <tr align="center">
          <?php
		  foreach($child_answer as $key=>$child_answer_option){ ?>
          <td><input type="radio" class="form-check-input" name="answerid[<?php echo $row_get_questions['id']?>][<?php echo $row_get_child_questions['id']?>]" value="<?php echo $key; ?>--<?php echo $child_answer_option; ?>" <?php if($row_get_questions['ifrequired']==1){ ?> required <?php } ?>></td>
          <?php } ?>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php
			}else{
			while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){			
		?>
    <div class="form-check">
      <label class="form-check-label">
        <input type="radio" class="form-check-input" name="answerid[<?php echo $row_get_questions['id']; ?>]" value="<?php echo $row_get_questions_detail['id']."--".$row_get_questions_detail['description']?>"  <?php if($row_get_questions['ifrequired']==1){ ?> required <?php } ?>>
        <?php echo $row_get_questions_detail['description']?> </label>
    </div>
    <?php
			}
			}
			?>
            <input type="hidden" name="questionid[]" value="<?php echo $row_get_questions['id']?>">
            <?php
		}
		if($row_get_questions['answer_type']==2){
			//Text Box
			?>
            <div class="form-group">
                <input type="text" name="answerid[<?php echo $questionid?>]" value="" class="form-control">
                <input type="hidden" name="questionid[]" value="<?php echo $row_get_questions['id']?>">
            </div>
            <?php
		}
		if($row_get_questions['answer_type']==3){
			//Text Area
			?>
			<div class="form-group">
            <textarea name="answerid[<?php echo $row_get_questions['id']?>]"  id="answerid_<?php echo $index?>" value="" class="form-control"></textarea>
               <input type="hidden" name="questionid[]" value="<?php echo $row_get_questions['id']?>">
            </div>
            
		<?php }
		if($row_get_questions['answer_type']==4){
			//Rating			
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
          <td class="show_smily_<?php echo $show_smily; ?>"><label><input <?php if($show_smily==1){ ?> style="visibility:hidden; display:none;" <?php } ?> type="radio" class="form-check-input <?php if($show_smily==1){ ?> smily_icon_input <?php } ?>" name="answerid[<?php echo $row_get_questions['id']; ?>]" value="<?php echo $key; ?>--<?php echo $child_answer_option; ?>"  <?php if($row_get_questions['ifrequired']==1){ ?> required <?php } ?>>
          <?php if($show_smily==1){ ?> 
          <img style="width:30px" class="smily_icon" src="<?php if($ans_count==2){ echo "dist/img/".strtolower($child_answer_option).".png"; }else{ echo smile_format_icon($smily_loop,$ans_count); } ?>">
		  <?php
		  	$smily_loop++;
			//$show_smily++;
		  } ?>
          </label>
          </td>
          <?php } ?>
        </tr>
      </tbody>
    </table>
    <input type="hidden" name="questionid[]" value="<?php echo $row_get_questions['id']?>">
    <?php
			}
		}
	if($row_get_questions['answer_type']==5){
		record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'  ");
			if($totalRows_get_questions_detail>0){
				while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
	?>
	<h5>
		<?php echo $row_get_questions_detail['description']; ?>
	</h5>
<?php  }
}
}
 $index++; ?>
 </div>
 <?php
}
?>
</div>
    <div class="col-md-12"><input type="submit" name="submit" class="btn btn-info submitform" id="submit"></div>
  </form>
</div>
<br>
<br>
<script>
$(document).ready(function(){
  $(".smily_icon").click(function(){
	  $(this).closest("tr").find("td").css("background-color", "#FFF");
	  $(this).closest("td").css("background-color", "#DDD");
  });
});
</script>
<style type="text/css">
table, h4, .form-check{
	text-transform:uppercase;
}
body{
	font-family: 'Comfortaa', arial;
}
</style>
</body>
</html>