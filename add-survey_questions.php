<?php
$surveyid=$_GET['surveyid'];

if(empty($surveyid)){
	echo 'Survey ID msising.'; exit;
}
record_set("get_survey_details", "select * from surveys where id = '".$surveyid."'");				
$row_get_survey_details = mysqli_fetch_assoc($get_survey_details);


 if(!empty($_POST['submit'])){
	$condition_yes_no=$_POST['condition_yes_no'];
	$data_que =  array(
		"parendit"		=> $_POST['parent'],
		"question" 		=> $_POST['question'],
		"surveyid"		=>$surveyid,
		"answer_type" 	=> $_POST['atype'],
		"ifrequired" 	=> $_POST['ifrequired'],
		"dposition" 	=> $_POST['dposition'],
		"cstatus" 		=> $_POST['status'],
		"conditional_logic"   => (isset($condition_yes_no)?$condition_yes_no:'0'),
		'cip'			=> ipAddress(),
		'cby'			=> $_SESSION['user_id'],
		'cdate'			=> date("Y-m-d H:i:s"),
		'is_weighted'	=> $_POST['weighted_yes_no'],
		'survey_step_id'=> (isset($_POST['survey_step'])) ? $_POST['survey_step'] : 0,
	);
	
	$insert_value =  dbRowInsert("questions",$data_que);
	echo $insert_value ;
	if(!empty($insert_value )){	
		echo 'amit';
		if(isset($_POST['question_sub_heading']) && !empty($_POST['question_sub_heading'])){
			echo 'case2';
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
			$insert_value1 =  dbRowInsert("questions_detail",$data_head,2);
		}
		record_set("get_quest", "select id from questions order by id desc limit 1");				
		$row_get_quest = mysqli_fetch_assoc($get_quest);
		$correct=$_POST['correct'];
		$conditional_logic 	 = $_POST['conditional_logic'];
		$conditional_answer  = $_POST['conditional_answer'];
		$skip_to_question_id = $_POST['skip_to_question'];
		echo '<pre>';
		print_r($correct);
		echo '</pre>';
		if(!empty($correct)){
			$cans=$_POST['cans'];
			$condition_question=$_POST['condition_question'];
			$i=0;
			//print_r($cans);
			foreach($correct as $ans){
				$answer=$ans;
				if($answer!=""){
					$data_correct =  array(
						"description"=> $answer,
						"questionid" => $insert_value,
						"surveyid"=>$surveyid,
						"answer"=>$cans[$i],
						"condition_yes_no"=>(isset($condition_yes_no)?$condition_yes_no:'0'),
						"condition_qid"=>(isset($condition_question[$i])?$condition_question[$i]:'0'),
						"cstatus" => $_POST['status'],
						'cip'=>ipAddress(),
						'cby'=>$_SESSION['user_id'],
						'cdate'=>date("Y-m-d H:i:s")
					);
					if($condition_yes_no == 1){
						$data_correct['conditional_logic']   = $conditional_logic[$i];
						$data_correct['conditional_answer']  = $conditional_answer[$i];
						$data_correct['skip_to_question_id'] = $skip_to_question_id[$i];
					}
					$insert_value2 =  dbRowInsert("questions_detail",$data_correct);
					$i++;
				}	
			}
		}
		die();
		$msg = "Question Added Successfully";
	}else{
		$msg = "Some Error Occourd. Please try again..";
	}
	reDirect("?page=add-survey_questions&msg=".$msg."&surveyid=".$_REQUEST['surveyid']);		
}

?>
<section class="content-header">
  <h1> ADD SURVEY QUESTION</h1>
  <a href="?page=view-survey_questions&surveyid=<?php  echo $_REQUEST['surveyid'];?>" class="btn btn-primary pull-right" style="margin-top:-25px">View Survey Questions</a> 
</section>
<section class="content">
  <div class="box box-secondary">
		<?php if(isset($_GET['msg'])){ ?>
			<div class="alert alert-success" role="alert">
				<?php echo $_GET['msg']; ?>
			</div>
		<?php } ?>

		<form action="" method="post" enctype="multipart/form-data">
			<!-- Start Language tab panel -->
			<div class="box-body">
				<div class="row">
					<!-- Start Survey Steps Section -->
					<?php 
					if($row_get_survey_details['isStep'] == 1){ ?>
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
										<option value="<?php echo $row_get_surveys_steps['id']; ?>"><?php echo $row_get_surveys_steps['step_title']; ?></option>
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
									while($row_get_parent = mysqli_fetch_assoc($get_parent)){ ?>
									<option value="<?php echo $row_get_parent['id'];?>"><?php echo $row_get_parent['question'];?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<!-- End Parent Question Section -->
					<!-- Start Answer Type Section -->
					<div class="col-md-6">
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
					<div class="col-md-6">
						<div class="form-group">
							<label>Position</label>
							<input type="number" class="form-control" name="dposition" min="0" value="1" />
						</div>
					</div>
					<!-- End Position Section -->
					<div class="col-md-6">
						<div class="col-md-3">
							<label>Weighted</label>
							<div class="form-group">	
								<input type="radio" class="form-check-input weighted_yes_no" name="weighted_yes_no" data-id="2334edff" value="1" data-count="0"/>  Yes
								<input type="radio" class="form-check-input weighted_yes_no" name="weighted_yes_no" data-id="2334edff" value="0" data-count="0" checked/>  No
							</div>
						</div>
						<div class="col-md-3 conditional-radio-btn" style="display:none;">
							<label>Conditional</label>
							<div class="form-group">	
								<input type="radio" class="form-check-input condition_yes_no" name="condition_yes_no"   value="1" data-count="0"/>  Yes
								<input type="radio" class="form-check-input condition_yes_no" name="condition_yes_no"   value="0" data-count="0" checked/>  No
							</div>
						</div>
					</div>
				</div>
				<!-- End Row -->
				<!-- Start Question Section -->
				<div class="col-md-12 answer_type_other">
					<div class="form-group">
						<label class="question_label">Question</label>
						<input type="text" class="form-control" name="question" />
					</div>
				</div>
				<!-- End Question Section -->
				<!-- Start Sub Heading Section -->
				<div class="col-md-12 answer_type_5">
					<div class="form-group">
						<label>Sub Heading</label>
						<input type="text" class="form-control" name="question_sub_heading" />
					</div>
				</div> 
				<!-- End Sub Heading Section -->
					<!-- Start Answer Section For English -->
					<div class="options" id="options" data-langid="1">
						<div class="col-md-12 opt-div">
							<div class="col-md-3">
								<div class="form-group">
									<label>Answer 1 </label>
									<input type="text" class="form-control correct_answer" name="correct[]" />
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
								<label>Value</label>
								<input type="number" value="0" class="form-control canval 2334edff" name="cans[]" readonly/>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
					<div class="col-md-12 text-right btnopt">
						<a href="JavaScript:Void(0);" id="btnaddoption" class="btn btn-info">Add New Option</a>
					</div>
					<div class="col-md-6">
						<div class="form-group">
						<label>Status</label>
						<select class="form-control" name="status">
							<?php foreach(status() as $key => $value) {?> 
							<option value="<?=$key?>"><?=$value?></option>
							<?php }?>
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
					<!-- End Answer Section For English -->
				
				
				<!-- Start Conditional Question Section-->
				<div class="conditional_questions" id="conditional_questions">

				</div>
				<!-- End Conditional Question Section -->

				<!-- Start Conditional Question skip to Section-->
				<div class="conditional_questions_skip" id="conditional_questions_skip" style="display:none">
					<div class="col-md-12 logicSection" style="margin-bottom: 30px;">
						<h3>Conditional Logic</h3>
						<div class="col-md-12 conditional_logic">
							<div class="col-md-2">
								<label for="">If answer</label>		
							</div>
							<div class="col-md-3">
								<select class="form-control" name="conditional_logic[]">
									<option value="1">Equal To</option>
									<option value="2">Not Equal To</option>
								</select>		
							</div>
							<div class="col-md-3">
								<select class="form-control conditional_answer" name="conditional_answer[]" id="conditional_answer">
									
								</select>	
							</div>
							<div class="col-md-1">
								<label for="">Skip to</label>
							</div>
							<div class="col-md-3">
								<select class="form-control skip_to_question" name="skip_to_question[]">
									<?php 
										record_set("get_question", "select * from questions where surveyid='".$surveyid."' and cstatus='1' ");
										if($totalRows_get_question>0){	

										while($row_get_question = mysqli_fetch_assoc($get_question)){ ?>
										<option value="<?=$row_get_question['id']?>"><?=$row_get_question['question']?></option>
									<?php
										}
									}
									?>
								</select>	
							</div>
						</div>
					</div>	
					<div class="col-md-12">
						<button type="button" class="add-more btn btn-info" style="float: right;margin: 15px;">Add More Logic</button>
					</div>			
				</div>

				<!-- End Conditional Question skip to Section -->

				
			</div>
			<!-- end box body -->
			<div class="box-footer text-right">
				<input type="submit" class="btn btn-primary createquestion" value="Create Question" name="submit">
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
		let uniqueId = Math.random().toString(36).substr(2, 9);
		$(".options").append('<div class="col-md-12 opt-div"><div class="col-md-3"><div class="form-group"><label>New Option</label><input type="text" class="form-control correct_answer" name="correct[]"></div></div><div class="col-md-2"><div class="form-group"><label>Value</label><input type="number" class="form-control canval '+uniqueId+'" value="0" name="cans[]" readonly/></div></div></div>');
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
		setWeighted();
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
			$('.conditional-radio-btn').hide();
			$(".btnopt").hide();
			$(".answer_type_5").hide();
			$(".question_label").text("Question");
			if(atype=="5"){
				$(".answer_type_5").show();
				$(".question_label").text("Title");
			}
		}else{
			$('.conditional-radio-btn').show();
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

	$(".condition_yes_no").change(async function () {
		var selectArr1 = new Array();
			if($(this).val() == 1){	
				// old conditional question
				// var numDivs = $('#options> .opt-div').length;
				// for(let i=0; i<numDivs;i++){
				// 	$('.conditional_questions').each(function(index,value){
				// 		$(this).append('<div class="col-md-12 condition_que"><div class="form-group"><label class="question_label">Question '+parseInt(i+1)+'</label><select class="form-control conque conditionQuestion'+i+'" name="condition_question['+i+']" id="conditionQuestion'+i+'">'+
				// 		'</select></div></div>');
				// 	});
				// 	if(i>0){
				// 			$('.conque').each(function(index, value) {
				// 			if($(this).val() != null){
				// 				selectArr1[index]=$(this).val();
				// 			}else{
				// 				selectArr1[index]=0;
				// 			}
				// 			// console.log(index+'-'+$(this).val());
				// 		});
				// 	}else{
				// 		selectArr1[0] = 0; 
				// 	}
				// 	// On Parent question change ajax execute
				// 	var questionId = selectArr1;
				// 	var user_id  = <?=$_SESSION['user_id'];?>;
				// 	var surveyid = <?=$_REQUEST['surveyid'];?>;
				
				// 	await $.ajax({
				// 			type: "POST",
				// 			url: 'ajax/ajaxOnSelectQuestion.php',
				// 			data: {questionId: questionId,user_id: user_id,surveyid: surveyid}, 
				// 			success: function(response)
				// 			{
				// 				console.log(response);
				// 				if (response == '') {
				// 					// $('#conditionQuestion').html('<option value="0">No Question</option>');
				// 				}else{
				// 					$('.conditionQuestion'+i).html(response);
				// 				}
				// 			}
				// 		});
				// }
				$('#conditional_questions_skip').show();
			}
			if($(this).val() == 0){
				$('.condition_que').remove();
				$('#conditional_questions_skip').hide();

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
$(document).on('change', '.weighted_yes_no', function() {
	setWeighted();
});

$(document).on("blur", ".correct_answer, .canval", function() {
	let option = '';
	$('.conditional_answer').html('');
	$('#options > .opt-div').each(function() {
		let text = $(this).find('.correct_answer').val();
		let value = $(this).find('.canval').val();
		console.log('text :'+ text ,'value :'+ value);
		if(text){
			option = `<option value="${value}">${text}</option>`;
			$('.conditional_answer')
			$('.conditional_answer').each(function() {
				$(this).append(option);
			});
			
		}
	});
});
function setWeighted(){
	let value = $("input[name='weighted_yes_no']:checked").val();
	if(value == 1){
		$('.canval').prop('readonly', false);
	}else {
		$('.canval').prop('readonly', true);
		$('.canval').val(0);
	}
}
$(document).on('click','.add-more',function(){
  getConditionalQuestion();
})

$(document).on('change','.skip_to_question',function(){
  let totalLength = $('.skip_to_question').length;
  let currentIndex = $('.skip_to_question').index($(this));
  for(let i=0; i<totalLength; i++){
    if(i>=currentIndex){
      $('.conditional_logic').eq(currentIndex+1).remove();
    }
  }
})
function getConditionalQuestion(mode='addQuestion'){
  let questionid23 = $('.skip_to_question').val();
  let surveyid = '<?=$_GET['surveyid']?>';
  var possibleAnswerCount = $('.correct_answer').length;
  var conditional_logicCount = $('.conditional_logic').length;
  if(conditional_logicCount >= possibleAnswerCount){
	alert('Conditional Logic can not be greater than No of Answer');
	return false;
  }

  // alert("optionLength :",numItems);
  // alert("ConditionalLogicLength :",numItems22);
  const QuestionArray = [];
  $('.skip_to_question').each(function(){
    let Qid = $(this).val(); 
    console.log(Qid);
    QuestionArray.push(Qid);
  });
  QuestionArray.sort();
  console.log(QuestionArray);
  $.ajax({
	type: "POST",
	url: 'ajax/ajaxGetConditionalQuestionOnSelect.php',
	data: {
		surveyid: surveyid,
		skipQid : QuestionArray,
		mode:mode,
	}, 
	success: function(response)
	{
	//console.log(response);
		if (response == '') {
		}else{
			$(".logicSection").append(response);
			$('.correct_answer').trigger('blur');
		}
	}
  })
}
</script>