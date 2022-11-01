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
      
    $data =  array(
          "survey_step_id" => $_POST['survey_step'],
          "cstatus" => $_POST['status'],
          "dposition" => $_POST['dposition'],
          "ifrequired" => $_POST['ifrequired'],
          'cdate'=>date("Y-m-d H:i:s")
        );
    $updte=	dbRowUpdate("questions", $data, "where id=".$questionid." and surveyid=".$surveyid);
    if(!empty($updte )){	
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
    <div class="box-header"><i class="fa fa-edit"></i>Input</div>
    <form action="" method="post" enctype="multipart/form-data">
      <div class="box-body">
        <div class="row">
          <?php 
            record_set("get_questions", "select * from questions where surveyid='".$_REQUEST['surveyid']."'  and id='".$_REQUEST['questionid']."'");				
            $row_get_questions = mysqli_fetch_assoc($get_questions);
          ?>
          <?php if($row_get_survey_details['isStep'] == 1){ ?>
            <div class="col-md-6">
              <div class="form-group">
                <label>Survey Steps</label>
                <select class="form-control survey_step" name="survey_step">
                  <option value="">Select Step</option>
                  <?php 
                      record_set("get_surveys_steps", "select * from surveys_steps where survey_id='".$_REQUEST['surveyid']."'");       
                      while($row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps))
                      {
                  ?>
                      <option value="<?php echo $row_get_surveys_steps['id']; ?>" <?php echo ($row_get_questions['survey_step_id'] == $row_get_surveys_steps['id']) ? "selected" : "" ?>><?php echo $row_get_surveys_steps['step_title']; ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          <?php } ?>

          <div class="col-md-6">
            <div class="form-group">
              <label>Parent Question</label>
              <select class="form-control" name="parent" disabled >
                <option value="0">No Parent</option>
                <?php 
                  record_set("get_parent", "select id,question from questions where  surveyid='".$_REQUEST['surveyid']."' and parendit='0'");				
                  while($row_get_parent = mysqli_fetch_assoc($get_parent))
                  {
                ?>
                  <option <?php if($row_get_parent['id']==$row_get_questions['parendit']){ ?> selected="selected" <?php } ?> value="<?php echo $row_get_parent['id'];?>"><?php echo $row_get_parent['question'];?></option>
			          <?php	} ?>
              </select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label>Answer Type</label>
              <select class="form-control" name="atype" disabled>
                <?php
                  foreach(question_type() as $key => $value){
                    if($row_get_questions['answer_type']==$key){
                ?>
			             <option  selected="selected" value="<?php echo $key; ?>"><?php echo $value; ?></option>			
                <?php 
                    }
                  }
                ?>
              </select>
            </div>
          </div>

          <div class="col-md-2">
            <div class="form-group">
              <label>Position</label>
               <input type="number" class="form-control" name="dposition" min="0" value="<?php echo $row_get_questions['dposition'];?>" />
            </div>
          </div>
                   
          <div class="col-md-12">
            <div class="form-group">
              <label>Question</label>
              <input type="text" class="form-control" name="question" value="<?php echo $row_get_questions['question'];?>" disabled />
            </div>
          </div>

        </div>
        <div class="row" id="options">
          <?php 
            record_set("get_questions_detail", "select * from questions_detail where   surveyid='".$_REQUEST['surveyid']."'  and questionid='".$_REQUEST['questionid']."'");				
            while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail))
            {
          ?>
            <div class="col-md-5">
              <div class="form-group">
                <label>Answer 1</label>
                <input type="text" class="form-control" name="correct[]" value="<?php echo $row_get_questions_detail['description'];?>" disabled />
              </div>
            </div>

            <div class="col-md-1">
              <div class="form-group">
                <label>Value</label>
                <input type="text" class="form-control" name="correct[]" value="<?php echo $row_get_questions_detail['answer'];?>" disabled />
              </div>
            </div>
		      <?php } ?>
        </div>
        <div class="row">
          <?php if(!empty($surveyid) && empty($questionid)){ ?>
            <div class="col-md-12 text-right">
                <a href="JavaScript:Void(0);" id="btnaddoption11" class="btn btn-info">Add New Option</a>
            </div>
          <?php }?>
          <div class="col-md-6">
            <div class="form-group">
              <label>Status</label>
              <select class="form-control" name="status">
                <?php foreach(status() as $key => $value) {?> 
                <option value="<?=$key?>" <?=($row_get_questions['cstatus'] == $key)?'selected':''?>><?=$value?></option>
                <?php }?>
              </select>
            </div>
          </div>
		      <div class="col-md-6">
            <div class="form-group">
              <label>Required or Not ?</label>
              <select class="form-control ifrequired" name="ifrequired">
                <?php foreach(required() as $key => $value){?>
                  <option <?php if($row_get_questions['ifrequired']==$key){?> selected="selected"<?php  }?> value="<?php echo $key; ?>"><?php echo $value; ?></option>						
                <?php }?>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="box-footer text-right">
        <input type="submit" class="btn btn-primary" value="Update Question" name="submit">
      </div>
    </form>
  </div>
  </div>
</section>
<script type="text/javascript">
var chkvalue="2";
    $("#btnaddoption").click(function () {
		
      $("#options").append('<div class="col-md-6"><div class="form-group"><label>New Answer</label><label class="radio-inline pull-right"><input type="radio" name="cans" checked value="'+chkvalue+'">Correct Answer</label><input type="text" class="form-control" name="correct[]"></div></div></div>');
	  chkvalue++;
    });
</script>