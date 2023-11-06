<?php
include('../function/function.php');
$surveyid   = $_REQUEST['surveyid'];
$skipedQid  = $_REQUEST['skipQid'];
if(isset($_REQUEST['questionid'])){
  $questionid = $_REQUEST['questionid'];
}else {
    record_set("get_question_min", "select id from questions where surveyid='".$surveyid."' and cstatus='1' order by id");
     $row_get_question_min = mysqli_fetch_assoc($get_question_min);
     $questionid = $row_get_question_min['id'];
}
$newIds = implode(',',$skipedQid);
$count = count($skipedQid);
if($count == 1){
    $min = $skipedQid[0];
    if($min > $questionid){
        $max = $min;
        $min = $questionid;
    }else{
        $max = $questionid;
    }
}else{
    $min = $skipedQid[0];
    $max = $skipedQid[$count-1];
    if($min>$questionid){
        $min = $questionid;
    }
    if($max<$questionid){
        $max = $questionid;
    }
}
if($_REQUEST['mode']=='editQuestion'){
    $html = '<div class="col-md-12 conditional_logic" style="margin-top: 10px;">
    <div class="col-md-2">
    <label for="">If answer</label>		
    </div>
    <div class="col-md-3">
    <select class="form-control" name="conditional_logic[]">
        <option value="1" >Equal To</option>
        <option value="2">Not Equal To</option>
    </select>		
    </div>
    <div class="col-md-3">
    <select class="form-control conditional_answer" name="conditional_answer[]" id="conditional_answer">';
    record_set("get_questions_detail", "select * from questions_detail where surveyid='".$surveyid."'  and questionid='".$questionid."'");		
    while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail))
    {
    $html .= '<option value="'.$row_get_questions_detail['answer'].'">'.$row_get_questions_detail['description'].'</option>';
    }
    $html .='</select>	
    </div>
    <div class="col-md-1">
    <label for="">Skip to</label>
    </div>
    <div class="col-md-3">
    <select class="form-control skip_to_question" name="skip_to_question[]">';
    record_set("get_question", "select * from questions where surveyid='".$surveyid."' and cstatus='1' and id !=".$questionid."");
    if($totalRows_get_question>0){
        while($row_get_question = mysqli_fetch_assoc($get_question)){
        $html .= '<option value="'.$row_get_question['id'].'" >'.$row_get_question['question'].'</option>' ; 
        }
    }
    $html .='</select>	
    </div>
    </div>';
    echo $html; die();
}else if($_REQUEST['mode']=='addQuestion'){

    $html = '<div class="col-md-12 conditional_logic" style="margin-top: 10px;">
    <div class="col-md-2">
    <label for="">If answer</label>		
    </div>
    <div class="col-md-2">
    <select class="form-control" name="conditional_logic[]">
        <option value="1" >Equal To</option>
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
    <select class="form-control skip_to_question" name="skip_to_question[]">';
    record_set("get_question", "select * from questions where surveyid='".$surveyid."' and cstatus='1' order by dposition asc");
    if($totalRows_get_question>0){
        while($row_get_question = mysqli_fetch_assoc($get_question)){
        $html .= '<option value="'.$row_get_question['id'].'" >'.$row_get_question['question'].'</option>' ; 
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