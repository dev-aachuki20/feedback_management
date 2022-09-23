<?php
$surveyid=$_GET['surveyid'];

if(empty($surveyid)){
	echo 'Survey ID msising.'; exit;
}

record_set("get_survey_details", "select * from surveys where id = '".$surveyid."'");				
$row_get_survey_details = mysqli_fetch_assoc($get_survey_details);

$languages = explode(',',$row_get_survey_details['language']);

 if(!empty($_POST['submit'])){		
	
	$data_que =  array(
			"parendit"=> $_POST['parent'],
			"question" => $_POST['question'],
			"surveyid"=>$surveyid,
			"answer_type" => $_POST['atype'],
			"ifrequired" => $_POST['ifrequired'],
			"dposition" => $_POST['dposition'],
			"cstatus" => $_POST['status'],
			'cip'=>ipAddress(),
			'cby'=>$_SESSION['user_id'],
			'cdate'=>date("Y-m-d H:i:s"),
			'survey_step_id'=>(isset($_POST['survey_step'])) ? $_POST['survey_step'] : 0,
		);

	$lang_que_col=array();
	$lang_head_col=array();
	record_set("get_language", "select * from languages where id in(".$row_get_survey_details['language'].") and cby='".$_SESSION['user_id']."'");				
	while($row_get_language = mysqli_fetch_assoc($get_language)){	
		if($row_get_language['id'] !=1){
			$lang_que_col["question_".$row_get_language['iso_code']] = (isset($_POST['question_'.$row_get_language['iso_code']]))?$_POST['question_'.$row_get_language['iso_code']]:'';

			$lang_head_col["description_".$row_get_language['iso_code']] = (isset($_POST['question_sub_heading_'.$row_get_language['iso_code']]))?$_POST['question_sub_heading_'.$row_get_language['iso_code']]:'';
		}
	}

	$data = array_merge($data_que,$lang_que_col);

	$insert_value =  dbRowInsert("questions",$data);

	if(!empty($insert_value )){	
		if(isset($_POST['question_sub_heading']) && !empty($_POST['question_sub_heading'])){
			$data_head =  array(
				"description"=> $_POST['question_sub_heading'],
				"questionid" => $insert_value,
				"surveyid"=>$surveyid,
				"answer"=>0,
				"cstatus" => $_POST['status'],
				'cip'=>ipAddress(),
				'cby'=>$_SESSION['user_id'],
				'cdate'=>date("Y-m-d H:i:s")
			);
			//print_r($data1); exit;
			$data1 = array_merge($data_head,$lang_head_col);
			$insert_value1 =  dbRowInsert("questions_detail",$data1);
		}
		record_set("get_quest", "select id from questions order by id desc limit 1");				
		$row_get_quest = mysqli_fetch_assoc($get_quest);
		$correct=$_POST['correct'];

		if(!empty($correct)){
			$cans=$_POST['cans'];
			$condition_yes_no=$_POST['condition_yes_no'];
			$condition_question=$_POST['condition_question'];

			$i=0;
			//print_r($cans);
			
			foreach($correct as $ans)
			{
				$answer=$ans;
				if($answer!="")
				{
					$data_correct =  array(
							"description"=> $answer,
							"questionid" => $insert_value,
							"surveyid"=>$surveyid,
							"answer"=>$cans[$i],
							"condition_yes_no"=>(isset($condition_yes_no[$i])?$condition_yes_no[$i]:'0'),
							"condition_qid"=>(isset($condition_question[$i])?$condition_question[$i]:'0'),
							"cstatus" => $_POST['status'],
							'cip'=>ipAddress(),
							'cby'=>$_SESSION['user_id'],
							'cdate'=>date("Y-m-d H:i:s")
						);

						$lang_correct = array();
						record_set("get_lang_ans", "select * from languages where id in(".$row_get_survey_details['language'].") and cby='".$_SESSION['user_id']."'");				
						while($row_get_lang_ans = mysqli_fetch_assoc($get_lang_ans)){	
							if($row_get_lang_ans['id'] !=1){
								$lang_correct["description_".$row_get_lang_ans['iso_code']] =$_POST['correct_'.$row_get_lang_ans['iso_code']][$i];

							}
						}
						
						$data2 = array_merge($data_correct,$lang_correct);
						
						//print_r($data1); exit;
						 $insert_value2 =  dbRowInsert("questions_detail",$data2);
						/*if($x){
							record_set("get_question", "select id from questions_detail order by id desc limit 1");				
							$row_get_question = mysqli_fetch_assoc($get_question);
							
							$qdata=array("answer" => $row_get_question['id']);
							$updte=	dbRowUpdate("questions", $qdata, "where id=".$insert_value);
							
						}*/
						$i++;
				}	
			}
		}
		$msg = "Question Added Successfully";
	}else{
		$msg = "Some Error Occourd. Please try again..";
	}
	reDirect("?page=add-survey_questions&msg=".$msg."&surveyid=".$_REQUEST['surveyid']);		
}
?>
<section class="content-header">
  <h1> Add Survey Questions</h1>
  <a href="?page=view-survey_questions&surveyid=<?php  echo $_REQUEST['surveyid'];?>" class="btn btn-primary pull-right" style="margin-top:-25px">View Survey Questions</a> 
</section>
<section class="content">
  <div class="box box-danger">
		<?php if(isset($_GET['msg'])){ ?>
			<div class="alert alert-success" role="alert">
				<?php echo $_GET['msg']; ?>
			</div>
		<?php } ?>


		<form action="" method="post" enctype="multipart/form-data">
			<!-- Start Language tab panel -->
			<div class="tab-content" id="myTabContent">
				<?php
				foreach($languages as $val){
					record_set("get_language", "select * from languages where id='".$val."'");				
					$row_get_language = mysqli_fetch_assoc($get_language);
				?>
				<div class="tab-pane fade <?=($row_get_language['id'] == '1')?'active in':''?>" id="lang_<?=$row_get_language['iso_code']?>" role="tabpanel" aria-labelledby="<?=$row_get_language['iso_code']?>-tab">

					<div class="box-body">
						<div class="row">
							<!-- Start Survey Steps Section -->
							<?php if($row_get_survey_details['isStep'] == 1){ ?>
								<div class="col-md-6">
									<div class="form-group">
										<label>Survey Steps</label>
										<select class="form-control survey_step" name="survey_step" required>
											<option value="">Select Step</option>
											<?php 
												record_set("get_surveys_steps", "select * from surveys_steps where survey_id='".$_REQUEST['surveyid']."'");				
												while($row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps))
												{
											?>
												<option value="<?php echo $row_get_surveys_steps['id']; ?>"><?php echo $row_get_surveys_steps[($val==1)?'step_title':'step_title_fr']; ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							<?php } ?>
							<!-- End Survey Steps Section -->
							<!-- Start Parent Question Section -->
							<div class="col-md-6">
								<div class="form-group">
									<label>Parent Question</label>
									<select class="form-control parent" name="parent">
										<option value="0">No Parent</option>
										<?php 
											record_set("get_parent", "select * from questions where cby='".$_SESSION['user_id']."' and surveyid='".$_REQUEST['surveyid']."' and parendit='0' and survey_step_id!=0");				
											while($row_get_parent = mysqli_fetch_assoc($get_parent))
											{
										?>
											<option value="<?php echo $row_get_parent['id'];?>"><?php echo $row_get_parent[($val==1)?'question':'question_fr'];?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<!-- End Parent Question Section -->
							<!-- Start Answer Type Section -->
							<div class="col-md-4">
								<div class="form-group">
									<label>Answer Type</label>
									<select class="form-control atype" name="atype">
										<?php foreach(question_type() as $key => $value){?>
												<option <?php if($row_update_data['cstatus']==$key){?> selected="selected"<?php  }?> value="<?php echo $key; ?>"><?php echo $value; ?></option>						
										<?php }?>
									</select>
								</div>
							</div>
							<!-- End Answer Type Section -->
							<!-- Start Position Section -->
							<div class="col-md-2">
								<div class="form-group">
									<label>Position</label>
									<input type="number" class="form-control" name="dposition" min="0" value="1" />
								</div>
							</div>
							<!-- End Position Section -->
						</div>
						<!-- End Row -->
						<!-- Start Question Section -->
						<div class="col-md-12 answer_type_other">
							<div class="form-group">
								<label class="question_label">Question</label>
								<input type="text" class="form-control" name="<?=($row_get_language['id'] == 1)?'question':'question_'.$row_get_language['iso_code']?>" />
							</div>
						</div>
						<!-- End Question Section -->
						<!-- Start Sub Heading Section -->
						<div class="col-md-12 answer_type_5">
							<div class="form-group">
								<label>Sub Heading</label>
								<input type="text" class="form-control" name="<?=($row_get_language['id'] == 1)?'question_sub_heading':'question_sub_heading_'.$row_get_language['iso_code']?>" />
							</div>
						</div> 
						<!-- End Sub Heading Section -->
						<?php if($row_get_language['id'] == 1){ ?>
							<!-- Start Answer Section For English -->
							<div class="options" id="options" data-langid="<?=$row_get_language['id']?>">
								<div class="col-md-3">
									<div class="form-group">
										<label>Answer 1 </label>
										<input type="text" class="form-control" name="correct[]" />
									</div>
								</div>
								<div class="col-md-1">
									<div class="form-group">
									<label>Value</label>
									<input type="text" value="0" class="form-control canval" name="cans[]" />
									</div>
								</div>
								
								<div class="col-md-2">
									<label>Conditional</label>
									<div class="form-group">	
										<input type="radio" class="form-check-input condition_yes_no" name="condition_yes_no[0]"   value="1" data-count="0"/>  Yes
										<input type="radio" class="form-check-input condition_yes_no" name="condition_yes_no[0]"   value="0" data-count="0"/>  No
									</div>
								</div>
							</div>
							<!-- End Answer Section For English -->
						<?php }else{ ?>
							<!-- Start Answer Section For Other Language -->
							<div class="options_other" id="options_other" data-langcode=<?=$row_get_language['iso_code']?>>
								<div class="col-md-6">
									<div class="form-group">
									<label>Answer 1 </label>
									<input type="text" class="form-control" name="correct_<?=$row_get_language['iso_code']?>[]" />
									</div>
								</div>
							</div>
							<!-- End Answer Section For Other Language -->
						<?php } ?>
						
						<?php if($row_get_language['id'] == 1){?>
							<!-- Start Conditional Question Section-->
							<div class="conditional_questions" id="conditional_questions">

							</div>
							<!-- End Conditional Question Section -->
						<?php } ?>


						<div class="row">
							<div class="col-md-12 text-right btnopt">
								<?php if($row_get_language['id'] == 1){?>
									<a href="JavaScript:Void(0);" id="btnaddoption" class="btn btn-info">Add New Option</a>
								<?php }?>
							</div>
							<div class="col-md-6">
								<div class="form-group">
								<label>Status</label>
								<select class="form-control" name="status">
									<option value="1">Active</option>
									<option value="0">Deactive</option>
								</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
								<label>Required or Not ?</label>
								<select class="form-control ifrequired" name="ifrequired">
								<?php foreach(required() as $key => $value){?>
										<option <?php if($row_update_data['ifrequired'] == $key){?> selected="selected"<?php  }?> value="<?php echo $key; ?>"><?php echo $value; ?></option>						
										<?php }?>
								</select>
								</div>
							</div>
						</div>
					</div>
					<!-- end box body -->
					<div class="box-footer text-right">
						<?php 
							record_set("get_language_last", "select max(id) from languages where id in(".$row_get_survey_details['language'].")");				
							$row_get_language_last = mysqli_fetch_assoc($get_language_last);

							if($val == $row_get_language_last['max(id)']){
						?>
							<input type="submit" class="btn btn-primary createquestion" value="Create Question" name="submit">
						<?php
							}else{
						?>
							<!-- <a  class="btn btn-primary createquestion" data-toggle="tab" href="#lang_fr" role="tab">Next</a> -->
						<?php } ?>
					</div>
				</div>
				<!-- End tab Panel -->
				<?php } ?>
			</div>
			<!-- End Language tab panel -->
		</form>
  	</div>
</section>
<script type="text/javascript">
var chkvalue="2";
var datacount = "1";
var lcode = new Array();

	$('.nav-item').click(function(){
		$('#lang_'+$(this).data('langcode')).find('.new-option').attr('name','correct_'+$(this).data('langcode')+'[]');
	});


    $("#btnaddoption").click(function () {

		$(".options").append('<div class="col-md-3"><div class="form-group"><label>New Option</label><input type="text" class="form-control" name="correct[]"></div></div></div><div class="col-md-1"><div class="form-group"><label>Value</label><input type="text" class="form-control canval" value="0" name="cans[]" /></div></div><div class="col-md-2 "><label>Conditional</label><div class="form-group"><input type="radio" class="form-check-input condition_yes_no'+chkvalue+'" name="condition_yes_no['+datacount+']"  value="1" data-count="'+datacount+'" />  Yes <input type="radio" class="form-check-input condition_yes_no'+chkvalue+'" name="condition_yes_no['+datacount+']"   value="0" data-count="'+datacount+'" />  No</div></div>');


		$(".options_other").append('<div class="col-md-6"><div class="form-group"><label>New Option</label><input type="text" class="form-control new-option" ></div></div></div>');

		$(".condition_yes_no"+chkvalue).change(function () {
			var count=$(this).data('count');
			var selectArr = new Array();
			
			if($(this).val() == 1){	
				
				$('.conditional_questions').each(function(index,value){
				$(this).append('<div class="col-md-12 condition_que'+chkvalue+'"><div class="form-group"><label class="question_label">Question</label><select class="form-control conque conditionQuestion'+count+'" name="condition_question['+count+']" id="conditionQuestion'+count+'"></select></div></div>');
				});

				$('.conque').each(function(index, value) {
					if($(this).val() != null){
						selectArr[index]=$(this).val();
					}else{
						selectArr[index]=0;
					}

					// console.log(index+'-'+$(this).val());
				});

				
			}
			
			var questionId = selectArr;
			var user_id  = <?php echo $_SESSION['user_id'];?>;
			var surveyid = <?php echo $_REQUEST['surveyid'];?>;
			$.ajax({
				type: "POST",
				url: 'ajax/ajaxOnSelectQuestion.php',
				data: {questionId: questionId,user_id: user_id,surveyid: surveyid}, 
				success: function(response)
				{
					if (response == '') {
						// $('#conditionQuestion'+count).html('<option value="0">No Question</option>');
					}else{
						$('#conditionQuestion'+count).html(response); 
					}
				}
			});
			
			$('#conditionQuestion'+count).change(function(){
				var questionId = selectArr;
				var user_id  = <?php echo $_SESSION['user_id'];?>;
				var surveyid = <?php echo $_REQUEST['surveyid'];?>;
				$.ajax({
					type: "POST",
					url: 'ajax/ajaxOnSelectQuestion.php',
					data: {questionId: questionId,user_id: user_id,surveyid: surveyid}, 
					success: function(response)
					{
						if (response == '') {
							// $('#conditionQuestion'+(count+1)).html('<option value="0">No Question</option>');
						}else{
							$('.conque').each(function(){
								$(this).html(response);
							});
							
						}
					
					}
				});
			});  

			$('#conditionQuestion').change(function(){
				var questionId = selectArr;
				var user_id  = <?php echo $_SESSION['user_id'];?>;
				var surveyid = <?php echo $_REQUEST['surveyid'];?>;
				$.ajax({
					type: "POST",
					url: 'ajax/ajaxOnSelectQuestion.php',
					data: {questionId: questionId,user_id: user_id,surveyid: surveyid}, 
					success: function(response)
					{
						// console.log(response);
						if (response == '') {
							// $('#conditionQuestion'+count).html('<option value="0">No Question</option>');
						}else{
							$('.conditionQuestion'+count).each(function(){
									$(this).html(response);
							});
						
						}
					}
				});
				
			});  

			if($(this).val() == 0){
				$('.condition_que'+chkvalue).remove();
			}
		});

		datacount++;
		chkvalue++;
		
    });
</script>
<script>
$(document).ready(function(){
	
	$(".answer_type_other").hide();
	$(".options").hide();
	$(".options_other").hide();
	$(".btnopt").hide();
	$(".answer_type_5").hide();
    $("select.atype").change(function(){
        var atype = $(this).children("option:selected").val();
		// console.log(atype);
        if(atype == "2" || atype == "3" || atype == "5"){
			$(".answer_type_other").show();
			$(".options").hide();
			$(".options_other").hide();
			$(".conditional_questions").hide();
			$(".btnopt").hide();
			$(".answer_type_5").hide();
			$(".question_label").text("Question");
			if(atype=="5"){
				$(".answer_type_5").show();
				$(".question_label").text("Title");
			}
		}else{
			$(".answer_type_other").show();
			$(".conditional_questions").show();
			var  parent = $('.parent').val();
			$(".question_label").text("Question");
			$(".answer_type_5").hide();

			if(parent != 0 && atype == 1){
				$(".options").hide();
				$(".options_other").hide();
				$(".btnopt").hide();
			}else{
				$(".options").show();
				$(".options_other").show();
				$(".btnopt").show();
			}

			$( "form" ).submit(function( event ) {
				$('.canval').each(function() {
					sum = Number($(this).val());
					if(sum>100){
						alert("Max sum values should be 100 only.");
						$(this).focus();
						event.preventDefault();
						return false;
					}
				});
			});
		}
    });

    $("select.parent").change(function(){
        var atype = $(this).children("option:selected").val();
		// console.log(atype);
        if(atype!="0"){
			$(".options").hide();
			$(".options_other").hide();
			$(".btnopt").hide();
			$(".answer_type_5").hide();
			$(".question_label").text("Question");
		}
		else{
			$(".answer_type_5").show();
			$(".question_label").text("Question");
			$(".options").show();
			$(".options_other").show();
			$(".btnopt").show();
		}
    });

	$(".condition_yes_no").change(function () {
		var selectArr1 = new Array();
			if($(this).val() == 1){	
				$('.conditional_questions').each(function(index,value){
					$(this).append('<div class="col-md-12 condition_que"><div class="form-group"><label class="question_label">Question</label><select class="form-control conque conditionQuestion" name="condition_question[0]" id="conditionQuestion">'+
					'</select></div></div>');
				});

					selectArr1[0] = 0; 
					// On Parent question change ajax execute
					var questionId = selectArr1;
					var user_id  = <?=$_SESSION['user_id'];?>;
					var surveyid = <?=$_REQUEST['surveyid'];?>;
				
					$.ajax({
						type: "POST",
						url: 'ajax/ajaxOnSelectQuestion.php',
						data: {questionId: questionId,user_id: user_id,surveyid: surveyid}, 
						success: function(response)
						{
							console.log(response);
							if (response == '') {
								// $('#conditionQuestion').html('<option value="0">No Question</option>');
							}else{
								$('.conditionQuestion').html(response);
							}
						}
					});
			}
			if($(this).val() == 0){
				$('.condition_que').remove();
			}
	});
	
	$(".survey_step").change(function(e){
		var $s = $(e.target);
		$(".survey_step").val($s.val());
		$(".survey_step").not($s).attr('disabled', true);
	});

	$(".parent").change(function(e){
		var $s = $(e.target);
		$(".parent").val($s.val());
		$(".parent").not($s).attr('disabled', true);
	});
	
	$(".atype").change(function(e){
		var $s = $(e.target);
		$(".atype").val($s.val());
		$(".atype").not($s).attr('disabled', true);
	});
	 
	
});
</script>