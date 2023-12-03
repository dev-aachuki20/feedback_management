<?php
include('../function/function.php');
$surveyid       = $_REQUEST['surveyid'];
$skipedQid      = $_REQUEST['skipQid'];
$options        = $_REQUEST['options'];
$quesLimit      = $_REQUEST['ques_limit'];
$currentStep    = $_REQUEST['step'];

$where = '';
$QuestionOrder = 0;
if(isset($_REQUEST['questionid'])){
    $questionid = $_REQUEST['questionid'];
    $where = " or id = $questionid";
    /** skip question on conditional logic */
    record_set("get_total_question", "select * from questions where surveyid = $surveyid and id= $questionid");
    $row_get_total_question = mysqli_fetch_assoc($get_total_question);
    $QuestionOrder = $row_get_total_question['order_no'];
}else {
    record_set("get_question_min", "select id,order_no from questions where surveyid=".$surveyid." and cstatus=1 order by order_no desc");
    $row_get_question_min = mysqli_fetch_assoc($get_question_min);
    $questionid = $row_get_question_min['id'];
    $QuestionOrder = $row_get_question_min['order_no'];
}
$newIds = implode(',',$skipedQid);
$step = '';
if($_POST['step'] !=''){
    $step = " and survey_step_id = ".$_POST['step'];
}

// get question related to other steps
record_set("get_other_step_question", "select id,order_no from questions where surveyid=$surveyid and survey_step_id != ".$_POST['step']." and cstatus=1 $where order by id");
while($row_get_other_step_question = mysqli_fetch_assoc($get_other_step_question)){
    $skipedQid[] = $row_get_other_step_question['order_no'];
}

if($_REQUEST['mode']=='editQuestion'){
        $html = '<div class="col-md-12 conditional_logic" style="margin-top: 10px;">
            <div class="col-md-2">
                <label for="">If answer</label>		
            </div>
            <div class="col-md-3">
                <select class="form-control conditional_logic_dropdown" name="conditional_logic[]">
                    <option value="1" >Equal To</option>
                    <option value="2">Not Equal To</option>
                </select>		
            </div>
            <div class="col-md-3">
                <select class="form-control conditional_answer" name="conditional_answer[]" id="conditional_answer">';
                    foreach($options as $option){
                        $html .= '<option value="'.$option['label'].'">'.$option['label'].'</option>';
                    }
                $html .='</select>	
            </div>
            <div class="col-md-1">
                <label for="">Skip to</label>
            </div>
            <div class="col-md-3">';
                $html .='<select class="form-control skip_to_question" name="skip_to_question[]">';
                    for($i= $QuestionOrder; $i <= $quesLimit; $i++){
                        if(!in_array($i , $skipedQid)){
                            $html .= '<option value="'.$i.'" >Question No. '.$i.'</option>' ; 
                        }
                    }
                $html .='</select>	
            </div>
        </div>';
    echo $html; die();
}   
else if($_REQUEST['mode']=='addQuestion'){
    $html = '<div class="col-md-12 conditional_logic" style="margin-top: 10px;">
        <div class="col-md-2">
            <label for="">If answer</label>		
        </div>
        <div class="col-md-2">
            <select class="form-control conditional_logic_dropdown" name="conditional_logic[]">
                <option value="1" >Equal To</option>
                <option value="2">Not Equal To</option>
            </select>		
        </div>
        <div class="col-md-3">
            <select class="form-control conditional_answer" name="conditional_answer[]" id="conditional_answer">';
                foreach($options as $option){
                    $html .= '<option value="'.$option['label'].'">'.$option['label'].'</option>';
                }
            $html .='</select>	
        </div>
        <div class="col-md-1">
            <label for="">Skip to</label>
        </div>
        <div class="col-md-3">';
            $html .='<select class="form-control skip_to_question" name="skip_to_question[]">';
                for($i= ($QuestionOrder+2); $i <= $quesLimit; $i++){
                   
                    if(!in_array($i,$skipedQid)){
                        $html .= '<option value="'.$i.'" >Question No. '.$i.'</option>' ; 
                    }
                }
            $html .='</select>	
        </div>
        <div class="col-md-1">
            <button class="btn btn-danger remove-conditional-question">Remove</button>
        </div>
    </div>';
    echo $html; die();
}

?>