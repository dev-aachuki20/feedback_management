<?php 
include('../function/function.php');
if(isset($_POST['mode']) && $_POST['mode']=='step'){
    //get survey 
    $data = array();
    record_set("get_survey", "SELECT locations,departments,groups FROM `surveys` WHERE `id` = ".$_POST['survey_id']);	
    $row_get_survey = mysqli_fetch_assoc($get_survey);

    // get survey step
    record_set("get_step", "SELECT id,step_title FROM `surveys_steps` WHERE `survey_id` = ".$_POST['survey_id']);	
    
    //$response = '<option value="">Select Question</option>';
        //$response = '<option value="all">All Question</option>';
    while($row_get_step = mysqli_fetch_assoc($get_step)){
        $response .='<option value="'.$row_get_step['id'].'" data-qid="'.$row_get_step['id'].'">'.$row_get_step['step_title'].'</option>';
    }
    $countLocation    = explode(",",$row_get_survey['locations']);
    $countDepartment  = explode(",",$row_get_survey['departments']);
    $countGroup       = explode(",",$row_get_survey['groups']);
    $data['response']   = $response;
    $data['location']   = count($countLocation);
    $data['department'] = count($countDepartment);
    $data['group']      = count($countGroup);
    echo json_encode($data); die();
}
if(isset($_POST['mode'])&&$_POST['mode']=='question'){
 
    $question_id = implode(',',$_POST['step_id']);
    record_set("get_step", "SELECT id,question FROM `questions` WHERE `surveyid` = ".$_POST['survey_id']." and `survey_step_id` IN (".$question_id.")");	
    //$response = '<option value="all">All Question</option>';
    while($row_get_step = mysqli_fetch_assoc($get_step)){
        $response .='<option value="'.$row_get_step['id'].'" data-qid="'.$row_get_step['id'].'">'.$row_get_step['question'].'</option>';
    }
    echo $response; die();
}
 ?>