<?php
$surveyid = $_GET['surveyid'];
$questionid = $_GET['questionid'];

if (empty($surveyid)) {
  echo 'Survey ID msising.';
  exit;
}

record_set("get_survey_details", "select * from surveys where id = '" . $surveyid . "'");
$row_get_survey_details = mysqli_fetch_assoc($get_survey_details);

$languages = explode(',', $row_get_survey_details['language']);

if (!empty($_POST['submit'])) {
  // echo '<pre>';
  // print_r($_POST);
  // die();
  $filterConditionalLogic = "surveyid =$surveyid AND questionid=$questionid";
  dbRowDelete('conditional_logic_questions', $filterConditionalLogic);
  $condition_yes_no = (isset($_POST['condition_yes_no']) ? $_POST['condition_yes_no'] : '0');
  $data =  array(
    //"survey_step_id" => $_POST['survey_step'],
    "cstatus"            => $_POST['status'],
    "dposition"          => $_POST['dposition'],
    "question"           => $_POST['question'],
    "ifrequired"         => $_POST['ifrequired'],
    "conditional_logic"  => $condition_yes_no,
    'cdate'              => date("Y-m-d H:i:s")
  );
  $updte =  dbRowUpdate("questions", $data, "where id=" . $questionid . " and surveyid=" . $surveyid);
  $correct            = $_POST['correct'];

  $conditional_logic  = ($_POST['conditional_logic'] > 0) ? $_POST['conditional_logic'] : 0;
  $conditional_answer = ($condition_yes_no > 0) ? $_POST['conditional_answer'] : 0;
  $conditional_status = ($condition_yes_no > 0) ? $_POST['conditional_order_status'] : 0;
  $skip_to_question   = ($condition_yes_no > 0) ? $_POST['skip_to_question'] : 0;

  $deletedOption      = $_POST['removed_question_option'];
  if ($deletedOption != '') {
    $filter = "id in ($deletedOption)";
    dbRowDelete('questions_detail', $filter);
  }


  if (!empty($correct)) {
    $cans = $_POST['cans'];
    $rating_option_type = $_POST['rating_option_type'];
    $condition_question = $_POST['condition_question'];
    $i = 0;
   
    foreach ($correct as $key => $value) {
      $answer = $value;
      if ($answer != "") {
        $data_correct =  array(
          "description" => $answer,
          "answer" => $cans[$key],
          "rating_option_type" => $rating_option_type[$i],
          "condition_yes_no" => $condition_yes_no,
          // "condition_qid"=>(isset($condition_question[$i])?$condition_question[$i]:'0'),
          // 'cip'=>ipAddress(),
          // 'cby'=>$_SESSION['user_id'],
          // 'cdate'=>date("Y-m-d H:i:s")
        );
        
        $insert_value2 =  dbRowUpdate("questions_detail", $data_correct, " where id=$key");
        $i++;
      }
    }

    $questionDetailsArray = array();
			record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid  ."' and surveyid='".$surveyid."' and cstatus=1");
			while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
				$questionDetailsArray[$row_get_questions_detail['description']] = $row_get_questions_detail['id'];
			}
			// echo '<pre>';
			// print_r($questionDetailsArray);
			// print_r($_POST['conditional_answer']);
			// echo '</pre>';

			// echo '<hr/>';
			// conditional logic
			
			if ($condition_yes_no == 1) {
				$j=0;
				foreach($conditional_logic as $condition_loc){
					$conditionalAnswers = $_POST['conditional_answer'];
					$conditional_logic = $_POST['conditional_logic'];
					$skip_to_question = $_POST['skip_to_question'];
					$conditional_data = array();
					$conditional_data['surveyid'] = $surveyid;
					$conditional_data['questionid'] = $questionid;
					$conditional_data['question_detail_id'] = $questionDetailsArray[$conditionalAnswers[$j]];
					$conditional_data['conditional_logic']   = $conditional_logic[$j];
					$conditional_data['conditional_answer']  = $questionDetailsArray[$conditionalAnswers[$j]];
					$conditional_data['skip_to_question_id'] = $skip_to_question[$j];
					$insert_value3 =  dbRowInsert("conditional_logic_questions", $conditional_data);
					// echo '<pre>';
					// print_r($conditional_data);
					// echo '</pre>';
					$j++;

				}
			} else if ($condition_yes_no == 0){
          dbRowDelete('conditional_logic_questions', $filterConditionalLogic);
        }
  }

// die("exitt");
  if (!empty($updte)) {
    $msg = "Question Updated Successfully";
  } else {
    $msg = "Some Error Occourd. Please try again..";
  }
  reDirect("?page=view-survey_questions&msg=" . $msg . "&surveyid=" . $_REQUEST['surveyid']);
}

/** Question limit */
$surveyQuestionLimit = get_survey_detail($surveyid)['question_limit'];

?>
<section class="content-header">
  <h1> EDIT SURVEY QUESTIONS</h1>
  <a href="?page=view-survey_questions&surveyid=<?php echo $_REQUEST['surveyid']; ?>" class="btn btn-primary pull-right" style="margin-top:-25px">View Survey Questions</a>
</section>
<section class="content">
  <div class="box box-secondary">
    <div class="box-header"><i class="fa fa-edit"></i>Input</div>
    <form action="" method="post" enctype="multipart/form-data">
      <div class="box-body">
        <div class="row">
          <?php
          record_set("get_questions", "select * from questions where surveyid='" . $_REQUEST['surveyid'] . "'  and id='" . $_REQUEST['questionid'] . "' order by dposition asc");
          $row_get_questions = mysqli_fetch_assoc($get_questions);
          $isWeighted = $row_get_questions['is_weighted'];
          $QuestionOrderNo = $row_get_questions['order_no'];
          $currentStep = $row_get_questions['survey_step_id'];

          record_set("get_surveys_steps", "select * from surveys_steps where survey_id='" . $_REQUEST['surveyid'] . "'");
          if ($row_get_survey_details['isStep'] == 1 && $totalRows_get_surveys_steps > 1) { ?>
            <div class="col-md-6">
              <div class="form-group">
                <label>Survey Steps</label>
                <select class="form-control survey_step" name="survey_step" <?=(isset($questionid) && $questionid !='') ? 'disabled':''?>>
                  <option value="">Select Step <?=$row_get_questions['survey_step_id']  ?></option>
                  <?php
                    while ($row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps)) {
                  ?>
                    <option value="<?php echo $row_get_surveys_steps['id']; ?>" <?php echo ($row_get_questions['survey_step_id'] == $row_get_surveys_steps['id']) ? "selected" : "" ?>><?php echo $row_get_surveys_steps['step_title']; ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          <?php } else {
            $row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps);
          ?>
            <input type="hidden" class="survey_step" name="survey_step" value="<?= $row_get_surveys_steps['id'] ?>">
          <?php } ?>

          <div class="col-md-6">
            <div class="form-group">
              <label>Parent Question</label>
              <select class="form-control" name="parent" disabled>
                <option value="0">No Parent</option>
                <?php
                record_set("get_parent", "select id,question from questions where  surveyid='" . $_REQUEST['surveyid'] . "' and parendit='0'");
                while ($row_get_parent = mysqli_fetch_assoc($get_parent)) {
                ?>
                  <option <?php if ($row_get_parent['id'] == $row_get_questions['parendit']) { ?> selected="selected" <?php } ?> value="<?php echo $row_get_parent['id']; ?>"><?php echo $row_get_parent['question']; ?></option>
                <?php  } ?>
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Answer Type</label>
              <select class="form-control" name="atype" disabled>
                <?php
                foreach (question_type() as $key => $value) {
                  if ($row_get_questions['answer_type'] == $key) {
                ?>
                    <option selected="selected" value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php
                  }
                }
                ?>
              </select>
            </div>
          </div>
          <?php if ($row_get_questions['answer_type'] == 4) { ?>
            <div class="col-md-6 rating-type-div">
              <div class="form-group">
                <label>Select the type of rating</label>
                <select class="form-control rating_type" name="rating_type" disabled>
                  <?php foreach (answer_type() as $key => $value) {
                    if ($row_get_questions['rating_type'] == $key) { ?>
                      <option selected="selected" value="<?php echo $key; ?>"><?php echo $value; ?></option>
                  <?php  }
                  } ?>
                </select>
              </div>
            </div>
          <?php } ?>

          <div class="col-md-6">
            <div class="form-group">
              <label>Position</label>
              <input type="number" class="form-control" name="dposition" min="0" value="<?php echo $row_get_questions['dposition']; ?>" />
            </div>
          </div>

          <div class="col-md-10">
            <div class="form-group">
              <label>Question</label>
              <input type="text" class="form-control" name="question" value="<?php echo $row_get_questions['question']; ?>" <?= ($_SESSION['user_type'] != 1) ? 'disabled ' : '' ?> />
            </div>
          </div>
          <?php
          $answerOptions = array();
          record_set("get_questions_logic", "select * from questions_detail where   surveyid='" . $_REQUEST['surveyid'] . "'  and questionid='" . $_REQUEST['questionid'] . "'");
          $row_get_conditional_logic = mysqli_fetch_assoc($get_questions_logic);
          $condLogic = $row_get_conditional_logic['condition_yes_no'];
          if(in_array($row_get_questions['answer_type'],array(1,4,6))){ ?>
            <div class="col-md-2 conditional-radio-btn">
              <label>Conditional</label>
              <div class="form-group">
                <input type="radio" class="form-check-input condition_yes_no" name="condition_yes_no" value="1" data-count="0" <?= ($condLogic == 1) ? 'checked' : '' ?> /> Yes
                <input type="radio" class="form-check-input condition_yes_no noCondition" name="condition_yes_no" value="0" data-count="0" <?= ($condLogic == 0) ? 'checked' : '' ?> /> No
              </div>
            </div>
          </div>
        <?php } ?>
        <div class="row" id="options">
          <?php
          $i = 0;
          record_set("get_questions_detail", "select * from questions_detail where   surveyid='" . $_REQUEST['surveyid'] . "'  and questionid='" . $_REQUEST['questionid'] . "'");
          while ($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)) {
            $ansId = $row_get_questions_detail['id'];
            $answerOptions[$row_get_questions_detail['id']]['description'] = $row_get_questions_detail['description'];
            $answerOptions[$row_get_questions_detail['id']]['answer'] = $row_get_questions_detail['answer'];
            $answerOptions[$row_get_questions_detail['id']]['questions_detail_id'] = $row_get_questions_detail['id'];
            $i++;
          ?>
            <?php
            if ($row_get_questions['answer_type'] == 4) { ?>
              <div class="col-md-12 answer-fields">
                <input type="hidden" value="" name="removed_question_option" class="removed_question_option">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Answer <?= $i ?></label>
                    <input type="text" class="form-control correct_answer" name="correct[<?= $ansId ?>]" value="<?php echo $row_get_questions_detail['description']; ?>" <?= ($_SESSION['user_type'] != 1) ? 'disabled ' : '' ?> />
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Value</label>
                    <input type="text" class="form-control canval" name="cans[<?= $ansId ?>]" value="<?php echo $row_get_questions_detail['answer']; ?>" <?= ($_SESSION['user_type'] != 1 || $isWeighted == 0) ? 'disabled ' : '' ?> />
                  </div>
                </div>
                <div class="col-md-3 rating-type-option">
                  <?php
                  $ratingTypeOption = '';
                  if ($row_get_questions['rating_type'] == 1) {
                    $ratingTypeOption = emoticonsRatingOptions();
                    $label = 'Select Emoticon';
                  } else if ($row_get_questions['rating_type'] == 2) {
                    $ratingTypeOption = starRatingOptions();
                    $label = 'Select Star Rating';
                  } else if ($row_get_questions['rating_type'] == 3) {
                    $ratingTypeOption = numberRatingOptions();
                    $label = 'Select Number Rating';
                  } else if ($row_get_questions['rating_type'] == 4) {
                    $ratingTypeOption = tickCrossRatingOptions();
                    $label = 'Select Tick/Cross';
                  }
                  ?>
                  <label><?= $label ?></label>
                  <select class="form-control survey_step" name="rating_option_type[]">
                    <?php
                    foreach ($ratingTypeOption as $key => $value) { ?>
                      <option value="<?= $key ?>" <?= ($row_get_questions_detail['rating_option_type'] == $key) ? 'selected' : '' ?>><?= $value ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-md-3 remove-answer-div">
                  <!-- <label>&nbsp;</label> -->
                  <button data-id="<?= $row_get_questions_detail['id'] ?>" class="btn btn-danger remove-answer-field" type="button">Remove
                  </button>
                </div>
              </div>
            <?php } else { ?>
              <div class="col-md-6 answer-fields">
                <input type="hidden" value="" name="removed_question_option" class="removed_question_option">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Answer <?= $i ?></label>
                    <input type="text" class="form-control correct_answer" name="correct[<?= $ansId ?>]" value="<?php echo $row_get_questions_detail['description']; ?>" <?= ($_SESSION['user_type'] != 1) ? 'disabled ' : '' ?> />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Value</label>
                    <input type="text" class="form-control canval" name="cans[<?= $ansId ?>]" value="<?php echo $row_get_questions_detail['answer']; ?>" <?= ($_SESSION['user_type'] != 1 || $isWeighted == 0) ? 'disabled ' : '' ?> />
                  </div>
                </div>
                <div class="col-md-2">
                  <!-- <label>&nbsp; </label> -->
                  <button data-id="<?= $row_get_questions_detail['id'] ?>" class="btn btn-danger remove-answer-field" type="button">Remove
                  </button>
                </div>
              </div>
          <?php }
          } ?>
        </div>

        <!-- Start Conditional Question skip to Section-->

        <div class="conditional_questions_skip" id="conditional_questions_skip" style="<?= ($condLogic == 1) ? '' : 'display:none' ?>">
          <div class="col-md-12 logicSection" style="margin-bottom: 30px;">
            <h3>Conditional Logic</h3>
            <?php
            $i = 0;
            $conditionalCount = 0;
            $selectedQuestionId = array();
            record_set("get_questions_conditional_detail", "select * from conditional_logic_questions where surveyid=" . $_REQUEST['surveyid'] . "  and questionid=" . $_REQUEST['questionid'] . "");
            if ($totalRows_get_questions_conditional_detail > 0) {
              while ($row_get_questions_conditional_detail = mysqli_fetch_assoc($get_questions_conditional_detail)) {
                $i++;
                $conditionalCount++;
              ?>
                <div class="col-md-12 conditional_logic" style="margin-top:10px;">
                  <div class="col-md-2">
                    <label for="">If answer</label>
                  </div>
                  <div class="col-md-3">
                    <select class="form-control conditional_logic_dropdown" name="conditional_logic[]">
                      <option value="1" <?= ($row_get_questions_conditional_detail['conditional_logic'] == 1) ? 'selected' : '' ?>>Equal To</option>
                      <option value="2" <?= ($row_get_questions_conditional_detail['conditional_logic'] == 2) ? 'selected' : '' ?>>Not Equal To</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <select class="form-control conditional_answer" name="conditional_answer[]" id="conditional_answer">
                      <?php
                      $i = 0;
                      foreach ($answerOptions as $answerValue) {
                        $i++; ?>
                        <option value="<?= $answerValue['description'] ?>" <?= ($answerValue['questions_detail_id'] == $row_get_questions_conditional_detail['conditional_answer']) ? 'selected' : '' ?>><?= $answerValue['description'] ?></option>
                      <?php } ?>
                    </select>
                  </div>
                  <div class="col-md-1">
                    <label for="">Skip to</label>
                  </div>
                  <div class="col-md-3">
                    <select class="form-control skip_to_question skipQuestionDiv" name="skip_to_question[]" required>
                      <?php
                      if($row_get_questions['survey_step_id'] !=''){
                        $stepQuestion = " and survey_step_id = ".$row_get_questions['survey_step_id'];
                      }
                      $filterQuestion = '';
                      if(count($selectedQuestionId) > 0 ){
                        $selQid = implode(',',$selectedQuestionId);
                        $filterQuestion = " and order_no not In ($selQid)";
                      }
                      // get question related to other steps
                        record_set("get_other_step_question", "select id,order_no from questions where surveyid=$surveyid and survey_step_id != ".$currentStep." and cstatus=1 $where order by id");
                        
                        while($row_get_other_step_question = mysqli_fetch_assoc($get_other_step_question)){
                          $skipedQid[] = $row_get_other_step_question['order_no'];
                        }
                        // to skip all the question which are assigned ::
                        record_set("get_questions_conditional_detail_all", "select * from conditional_logic_questions where questionid !=$questionid and surveyid=" . $_REQUEST['surveyid']);
                        while($row_get_questions_conditional_detail_all = mysqli_fetch_assoc($get_questions_conditional_detail_all)){
                          $skipedQid[] = $row_get_questions_conditional_detail_all['skip_to_question_id'];
                        }
                        
                        for($i=($QuestionOrderNo+1); $i <= $surveyQuestionLimit; $i++){ 
                          if(!in_array($i,$skipedQid) && (!in_array($i, $selectedQuestionId))){ ?>
                            <option value="<?=$i?>" <?= ($i == $row_get_questions_conditional_detail['skip_to_question_id']) ? 'selected' : '' ?>>Question No. <?=$i ?></option>
                      <?php } } ?>
                    </select>
                  </div>
                  <input type="hidden" name="conditional_order_status[]" class="conditional_order_status" value="<?= $conditionalCount ?>">
                </div>
              <?php
              array_push($selectedQuestionId,$row_get_questions_conditional_detail['skip_to_question_id']);
               }
            } else { ?>
              <div class="col-md-12 conditional_logic" style="margin-top:10px;">
                <div class="col-md-2">
                  <label for="">If answer</label>
                </div>
                <div class="col-md-3">
                  <select class="form-control conditional_logic_dropdown" name="conditional_logic[]">
                    <option value="1" <?= ($row_get_questions_conditional_detail['conditional_logic'] == 1) ? 'selected' : '' ?>>Equal To</option>
                    <option value="2" <?= ($row_get_questions_conditional_detail['conditional_logic'] == 2) ? 'selected' : '' ?>>Not Equal To</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <select class="form-control conditional_answer" name="conditional_answer[]" id="conditional_answer">
                    <?php
                    $i = 0;
                    foreach ($answerOptions as $answerValue) {
                      $i++; ?>
                      <option value="<?= $answerValue['description'] ?>" <?= ($row_get_questions_conditional_detail['conditional_answer'] == $answerValue['questions_detail_id']) ? 'selected' : '' ?>><?= $answerValue['description'] ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-md-1">
                  <label for="">Skip to</label>
                </div>
                <div class="col-md-3">
                  <select class="form-control skip_to_question skipQuestionDiv" name="skip_to_question[]" required>
                    <?php
                    if($row_get_questions['survey_step_id'] !=''){
                      $stepQuestion = " and survey_step_id = ".$row_get_questions['survey_step_id']  ;
                    }
                    // get question related to other steps
                    record_set("get_other_step_question", "select id,order_no from questions where surveyid=$surveyid and survey_step_id != ".$currentStep." and cstatus=1 $where order by id");
                    while($row_get_other_step_question = mysqli_fetch_assoc($get_other_step_question)){
                      $skipedQid[] = $row_get_other_step_question['order_no'];
                    }
                    // to skip all the question which are assigned ::
                    record_set("get_questions_conditional_detail_all", "select * from conditional_logic_questions where surveyid=" . $_REQUEST['surveyid']);
                    while($row_get_questions_conditional_detail_all = mysqli_fetch_assoc($get_questions_conditional_detail_all)){
                      $skipedQid[] = $row_get_questions_conditional_detail_all['skip_to_question_id'];
                    }
                      for($i=($QuestionOrderNo+1); $i <= $surveyQuestionLimit; $i++){
                        if(!in_array($i,$skipedQid)){ ?>
                          <option value="<?=$i?>" <?= ($i == $row_get_questionss['order_no']) ? 'selected' : '' ?>>Question No. <?=$i ?></option>
                      <?php } } ?>
                  </select>
                </div>
              </div>
              <input type="hidden" name="conditional_order_status[]" class="conditional_order_status" value="<?= $conditionalCount ?>">
            <?php } ?>
          </div>
          <div class="col-md-12">
            <button type="button" class="add-more btn btn-info" style="float: right;margin: 15px;">Add More Logic</button>
          </div>
        </div>



        <div class="row" style="margin:0px;">
          <?php if (!empty($surveyid) && empty($questionid)) { ?>
            <div class="col-md-12 text-right">
              <a href="JavaScript:Void(0);" id="btnaddoption11" class="btn btn-info">Add New Option</a>
            </div>
          <?php } ?>
          <div class="col-md-6">
            <div class="form-group">
              <label>Status</label>
              <select class="form-control" name="status">

                <?php foreach (status() as $key => $value) { ?>
                  <option value="<?= $key ?>" <?= ($row_get_questions['cstatus'] == $key) ? 'selected' : '' ?>><?= $value ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Required or Not ?</label>
              <select class="form-control ifrequired" name="ifrequired">
                <?php foreach (required() as $key => $value) { ?>
                  <option <?php if ($row_get_questions['ifrequired'] == $key) { ?> selected="selected" <?php  } ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php } ?>
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
  var chkvalue = "2";
  $("#btnaddoption").click(function() {
    $("#options").append('<div class="col-md-6"><div class="form-group"><label>New Answer</label><label class="radio-inline pull-right"><input type="radio" name="cans" checked value="' + chkvalue + '">Correct Answer</label><input type="text" class="form-control " name="correct[]"></div></div></div>');
    chkvalue++;
  });

  $(".condition_yes_no").change(function() {
    var selectArr1 = new Array();
    var numOptions = $('.skip_to_question').children('option').length;

    if ($(this).val() == 1) {
      // if(numOptions == 0 ){
      //   alert("Sorry no question available to skip for this step");
      //   $('.noCondition').trigger('click');
      //   return false;
      // }
      $('#conditional_questions_skip').show();
    }
    if ($(this).val() == 0) {
      $('.condition_que').remove();
      $('#conditional_questions_skip').hide();

    }
  });
  $(document).on("blur", ".correct_answer, .canval", function() {
    let option = '';
    $('.conditional_answer').html('');
    $('#options > .answer-fields').each(function() {
      let text = $(this).find('.correct_answer').val();
      let value = $(this).find('.canval').val();
      let index = parseInt($(this).index()) + 1;
      if (text) {
        option = `<option value="${text}">${text}</option>`;
        // $('#conditional_answer').append(option);
        $('.conditional_answer').each(function() {
          $(this).append(option);
        });
      }
    });
  });

  $(document).on('click', '.add-more', function() {
    getConditionalQuestion();
    // const options = [];
    // $(".conditional_answer").last().find("option").each(function() {
    //   if ($(this).prop("selected") === false) {
    //     options.push({
    //       value: $(this).val(),
    //       label: $(this).text().trim()
    //     });
    //   }
    // });
    // console.log(options, 'options');
  })
  let dataObject = [];
  $(document).on('change', '.skip_to_question', function() {
    let totalLength = $('.skip_to_question').length;
    let currentIndex = $('.skip_to_question').index($(this));
    // let conditionalAnswer = $(this).closest('.conditional_logic').find('.conditional_answer option:selected').text();
    // let conditionalAnswer = $(this).closest('.conditional_logic').find('.conditional_answer option:selected').text();
    // let skipToQid = $(this).val();
    // dataObject.push({[conditionalAnswer]:skipToQid})
   console.log(currentIndex,'conditionalAnswer',totalLength);
    for (let i = 0; i < totalLength; i++) {
      if (i >= currentIndex) {
        $('.conditional_logic').eq(currentIndex + 1).remove();
      }
    }
    // console.log(dataObject, 'dataObject');
  })

  $(document).on('change', '.conditional_answer', function() {
    let totalLength = $('.conditional_answer').length;
    let currentIndex = $('.conditional_answer').index($(this));
    for (let i = 0; i < totalLength; i++) {
      if (i >= currentIndex) {
        $('.conditional_logic').eq(currentIndex + 1).remove();
      }
    }
  })
  function getConditionalQuestion(mode = 'editQuestion') {
    let questionid = '<?= $_GET['questionid'] ?>';
    let currentStep = $('.survey_step').val();

    let questionid23 = $('.skip_to_question').val();
    let surveyid = '<?= $_GET['surveyid'] ?>';
    var possibleAnswerCount = $('.correct_answer').length;
    var conditional_logicCount = $('.conditional_logic').length;
    if (conditional_logicCount >= possibleAnswerCount) {
      alert('Conditional Logic can not be greater than No of Answer');
      return false;
    }
    const QuestionArray = [];
    $('.skip_to_question').each(function() {
      let Qid = $(this).val();
      console.log(Qid);
      QuestionArray.push(Qid);
    });
    QuestionArray.sort();
    console.log(QuestionArray,'QuestionArray');
    // get conditional logic answer 
    const options = [];
    $(".conditional_answer").last().find("option").each(function() {
      if ($(this).prop("selected") === false) {
        options.push({
          value: $(this).val(),
          label: $(this).text().trim()
        });
      }
    });
    console.log(options, 'options');
    $.ajax({
      type: "POST",
      url: 'ajax/ajaxGetConditionalQuestionOnSelect.php',
      data: {
        step: currentStep,
        questionid: questionid,
        surveyid: surveyid,
        skipQid: QuestionArray,
        ques_limit:'<?=$surveyQuestionLimit?>',
        options: options,
        mode: mode,
      },
      success: function(response) {
        console.log(response);
        if (response == 'false') {
          alert("Sorry no more question available to skip for this step");
        } else {
          $(".logicSection").append(response);
        }
      }
    })
  }
  const questionId = [];
  $(document).on('click', '.remove-answer-field', function() {
    let id = $(this).data('id');
    questionId.push(id);
    $('.removed_question_option').val(questionId);
    $(this).closest('.answer-fields').remove();
  });
  $(".survey_step").change(function(e) {
			var $s = $(e.target);
			getSurveyQuestionBystep($s.val());
		});

function getSurveyQuestionBystep(stepId =1){
  let questionId = '<?= $_GET['questionid'] ?>';
	let surveyid = '<?= $_GET['surveyid'] ?>';
	$.ajax({
        type: "POST",
        url: 'ajax/common_file.php',
        data: {
			step_id: stepId,
			surveyid:surveyid,
      questionId:questionId,
			mode:'survey_question_by_step'
		}, 
        success: function(response){
			console.log(response,'response');
			if(response !=''){
				$('.skipQuestionDiv').html(response);
				$('.conditional_logic').not(':first').remove();
			}
        }
    });
}
  // $(document).on('change', '.conditional_answer', function() {
  //   let conditionalAns = $(this).find(":selected").text();
  //   $(this).closest('.conditional_logic').find('.conditional_logic_dropdown').attr('name', `conditional_logic[${conditionalAns}]`);
  // });

  // $(document).on('change', '.conditional_answer', function() {
  //   let conditionalAns = $(this).find(":selected").text();
  //   $(this).closest('.conditional_logic').find('.skip_to_question').attr('name', `skip_to_question[${conditionalAns}]`);
  // });


</script>