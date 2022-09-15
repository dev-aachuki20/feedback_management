<?php 
include('../function/function.php');
if(isset($_POST['mode'])&&$_POST['mode']=='step'){
    //get survey 
    $data = array();
    record_set("get_survey", "SELECT locationid,departmentid,groupid FROM `surveys` WHERE `id` = ".$_POST['survey_id']);	
    $row_get_survey = mysqli_fetch_assoc($get_survey);

    // get survey step
    record_set("get_step", "SELECT id,step_title FROM `surveys_steps` WHERE `survey_id` = ".$_POST['survey_id']);	
    //$response = '<option value="">Select Question</option>';
    while($row_get_step = mysqli_fetch_assoc($get_step)){
        $response .='<option value="'.$row_get_step['id'].'" data-qid="'.$row_get_step['id'].'">'.$row_get_step['step_title'].'</option>';
    }
    $data['response']   = $response;
    $data['location']   = $row_get_survey['locationid'];
    $data['department'] = $row_get_survey['departmentid'];
    $data['group']      = $row_get_survey['groupid'];
    echo json_encode($data); die();
}
if(isset($_POST['mode'])&&$_POST['mode']=='question'){
    record_set("get_step", "SELECT id,question FROM `questions` WHERE `surveyid` = ".$_POST['survey_id']." and `survey_step_id`=".$_POST['step_id']);	
    $response = '<option value="">All Question</option>';
    while($row_get_step = mysqli_fetch_assoc($get_step)){
        $response .='<option value="'.$row_get_step['id'].'" data-qid="'.$row_get_step['id'].'">'.$row_get_step['question'].'</option>';
    }
    echo $response; die();
}
 ?>