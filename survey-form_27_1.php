<?php
include('function/mysql_functions.php');
$surveyid=$_GET['surveyid'];
if(!empty($_POST['submit'])){
	

			
$data =  array(
			
			"parendit"=> $_POST['parent'],
			"question" => $_POST['question'],
			"surveyid"=>$surveyid,
			"answer_type" => $_POST['atype'],
			"dposition" => $_POST['dposition'],
			"cstatus" => $_POST['status'],
			'cip'=>ipAddress(),
			'cby'=>$_SESSION['user_id'],
			'cdate'=>date("Y-m-d H:i:s")
		);
		
$insert_value =  dbRowInsert("questions",$data);
		

if(!empty($insert_value )){	
	$msg = "Question Added Successfully";
}else{
	$msg = "Some Error Occourd. Please try again..";
}
reDirect("?page=survey-form&msg=".$msg."&surveyid=".$_REQUEST['surveyid']);		

}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Untitled Document</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<style>
h2 {
	border-bottom:1px solid #000;
	padding:0 0 5px 0;
}
</style>
</head>

<body>
<div class="container">
<h1 align="center">PAS Staff Survey</h1>
<?php 
	record_set("get_questions", "select * from questions where cby='1'  and surveyid='".$surveyid."' and cstatus='1' order by dposition ");				
	while($row_get_questions = mysqli_fetch_assoc($get_questions)){
		//print_r($row_get_questions);
		$table=true;
		$otable=1;
		$questionid=$row_get_questions['id'];	
		if($row_get_questions['parendit']==0)
			{
			
	?>
	
  <h4><?php echo $row_get_questions['question']?></h4>
  <?php
	
		
		
		record_set("get_questions_detail", "select * from questions_detail where cby='1' and questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'  ");				
		while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
			$table=false;
			$otable=0;
	?>	
  <div class="form-check">
    <label class="form-check-label">
      <input type="radio" class="form-check-input" name="one">
      <?php echo $row_get_questions_detail['description']?> </label>
  </div>
 <?php
		}
	}
	else
	{
		
			
?>
 
  <table class="table table-hover table-bordered">
 
    <tr>
	<td>&nbsp;</td>
	<?php
		$otable=0;
		record_set("get_child_questions_detail", "select * from questions_detail where cby='1' and questionid='".$questionid."' and surveyid='".$surveyid."'  ");
			$radiocount=1;
		while($row_get_child_questions_detail = mysqli_fetch_assoc($get_child_questions_detail)){
			
	?>
      
      <td><?php echo $row_get_child_questions_detail['description']?></td>
      <?php
	  $radiocount++;
		}
		?>
    </tr>

   <tr>
      <td><?php echo $row_get_questions['question']?></td>
	 <?php
	 $x=1;
		while($x<$radiocount)
		{
	 ?>
      <td><input type="radio" class="form-check-input" name="five<?php echo $row_get_questions['id']?>"></td>
     <?php
	 $x++;
		}
	?>
    </tr>
 
  <?php
		}
?>

	</table>  
<?php
	
  
 }
	
?>
  
 
 <input type="submit" name="submit" class="btn btn-info" value="Submit">
</div>
<br>
<br>

</body>
</html>