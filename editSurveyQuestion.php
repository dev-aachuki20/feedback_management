<?php
$surveyid=$_GET['surveyid'];
$questionid=$_GET['questionid'];

if(empty($surveyid)){
  echo 'Survey ID msising.'; exit;
}

record_set("get_survey_details", "select * from surveys where id = '".$surveyid."'");       
$row_get_survey_details = mysqli_fetch_assoc($get_survey_details);

$languages = explode(',',$row_get_survey_details['language']);

if(!empty($_POST['submit'])){		

	$data_que =  array(
			        "question" => $_POST['question'],
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

    $updte=	dbRowUpdate("questions", $data, "where id=".$questionid." and surveyid=".$surveyid);

	if(!empty($updte)){	
		if(isset($_POST['question_sub_heading']) && !empty($_POST['question_sub_heading'])){
			$data_head =  array(
				"description"=> $_POST['question_sub_heading'],
				"questionid" => $questionid,
				"surveyid"=> $surveyid,
				"answer"=>0,
			);

			$data1 = array_merge($data_head,$lang_head_col);
            $que_head_id= $_POST['question_detail_head_id'];
            $updte1=	dbRowUpdate("questions_detail", $data1, "where id=".$que_head_id." and surveyid=".$surveyid);
		}
		record_set("get_quest", "select id from questions order by id desc limit 1");				
		$row_get_quest = mysqli_fetch_assoc($get_quest);
		$correct=$_POST['correct'];

		if(!empty($correct)){
            $question_detail_id= $_POST['question_detail_id'];
			$i=0;
			
			foreach($correct as $ans)
			{
				$answer=$ans;
				if($answer!="")
				{
					$data_correct =  array(
							"description"=> $answer,
							"questionid" => $questionid,
							"surveyid"=>$surveyid,
						);

                    $lang_correct = array();
                    record_set("get_lang_ans", "select * from languages where id in(".$row_get_survey_details['language'].") and cby='".$_SESSION['user_id']."'");				
                    while($row_get_lang_ans = mysqli_fetch_assoc($get_lang_ans)){	
                        if($row_get_lang_ans['id'] !=1){
                            $lang_correct["description_".$row_get_lang_ans['iso_code']] =$_POST['correct_'.$row_get_lang_ans['iso_code']][$i];

                        }
                    }
                    
                    $data2 = array_merge($data_correct,$lang_correct);

                    $updte2=dbRowUpdate("questions_detail", $data2, "where id=".$question_detail_id[$i]);
                    
                    $i++;
				}	
			}
		}
		$msg = "Question Updated Successfully";
	}else{
		$msg = "Some Error Occourd. Please try again..";
	}
    reDirect("?page=view-survey_questions&msg=".$msg."&surveyid=".$_REQUEST['surveyid']);			
}
?>
<section class="content-header">
  <h1> Edit Survey Questions</h1>
  <a href="?page=view-survey_questions&surveyid=<?php  echo $_REQUEST['surveyid'];?>" class="btn btn-primary pull-right" style="margin-top:-25px">View Survey Questions</a> 
</section>
<section class="content">
  <div class="box box-secondary">
        <?php if(isset($_GET['msg'])){ ?>
            <div class="alert alert-success" role="alert">
                <?php echo $_GET['msg']; ?>
            </div>
        <?php } ?>
        <div class="box-header">
            <i class="fa fa-edit"></i>Input
        </div>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="box-body">
                <div class="row">
                    <?php 
                        record_set("get_questions", "select * from questions where surveyid='".$_REQUEST['surveyid']."'  and id='".$_REQUEST['questionid']."'");				
                        $row_get_questions = mysqli_fetch_assoc($get_questions);
                    ?>
                
                    <div class="col-md-12">
                        <div class="form-group">
                        <label>Question</label>
                        <input type="text" class="form-control" name="question" value="<?php echo $row_get_questions['question'];?>"  />
                        </div>
                    </div>
                </div>
                <div class="row" id="options">
                    <?php 
                        record_set("get_questions_detail", "select * from questions_detail where   surveyid='".$_REQUEST['surveyid']."'  and questionid='".$_REQUEST['questionid']."'");				
                        while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail))
                        {
                        if($row_get_questions['answer_type'] == 5){
                    ?>
                        <input type="hidden" name="question_detail_head_id" value="<?=$row_get_questions_detail['id']?>">
                        <div class="col-md-12">
							<div class="form-group">
								<label>Sub Heading</label>
								<input type="text" class="form-control" name="question_sub_heading" value="<?=$row_get_questions_detail['description']?>"/>
							</div>
						</div> 
                    <?php
                        }else{  
                    ?>
                        <input type="hidden" name="question_detail_id[]" value="<?=$row_get_questions_detail['id']?>">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Answer 1</label>
                                <input type="text" class="form-control" name="correct[]" value="<?php echo $row_get_questions_detail['description'];?>" />
                            </div>
                        </div>
                    <?php } } ?>
                </div>

                <div class="row">
                <?php
                    foreach($languages as $key=>$val){
                    record_set("get_language", "select * from languages where id='".$val."'");				
                    $row_get_language = mysqli_fetch_assoc($get_language);
                    if($row_get_language['id']!=1){
                ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Question - <?=$row_get_language['name']?></label>
                            <input type="text" class="form-control" name="question_<?=$row_get_language['iso_code']?>" value="<?php echo $row_get_questions['question_'.$row_get_language['iso_code']];?>"  />
                        </div>
                    </div>

                    <?php 
                        record_set("get_questions_detail", "select * from questions_detail where   surveyid='".$_REQUEST['surveyid']."'  and questionid='".$_REQUEST['questionid']."'");				
                        while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail))
                        {
                            if($row_get_questions['answer_type'] == 5){
                    ?>
                        <div class="col-md-12 answer_type_5">
							<div class="form-group">
								<label>Sub Heading - <?=$row_get_language['name']?></label>
								<input type="text" class="form-control" name="question_sub_heading_<?=$row_get_language['iso_code']?>" value="<?php echo $row_get_questions_detail['description_'.$row_get_language['iso_code']];?>" />
							</div>
						</div> 
                    <?php } else{ ?>    
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Answer 1 - <?=$row_get_language['name']?></label>
                                
                                <input type="text" class="form-control" name="correct_<?=$row_get_language['iso_code']?>[]" value="<?php echo $row_get_questions_detail['description_'.$row_get_language['iso_code']];?>"  />
                            </div>
                        </div>
                    <?php } } ?>  

                <?php } } ?> 
                </div>  
                
            </div>
            <div class="box-footer text-right">
                <input type="submit" class="btn btn-primary" value="Update Question" name="submit">
            </div>
        </form>
  </div>
</section>
