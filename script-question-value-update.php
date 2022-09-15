<?php 
include('function/function.php');
include('function/get_data_function.php');

record_set("get_question", "select * from questions_detail where answer <11 order by id desc");				
while($row_get_survey_details = mysqli_fetch_assoc($get_question)){
        $answerval = $row_get_survey_details['answer'];
        $updated_answer = $answerval*10;
        $data = array(
            "answer" =>$updated_answer
        );
        $updte=	dbRowUpdate("questions_detail", $data, "where id=".$row_get_survey_details['id']);
        //if($updte){
            // record_set("get_answers", "SELECT * FROM `answers` WHERE answerid =".$row_get_survey_details['id']);	
            // while($row_get_answers = mysqli_fetch_assoc($get_answers)){
            //     $answervalss = $row_get_answers['answerval'];
            //     $updated_answer = $answervalss*10;
            //     $data = array(
            //         "answerval" =>$updated_answer
            //     );
            //     $updte=	dbRowUpdate("answers", $data, "where answerid=".$row_get_survey_details['id'],1);echo '<br>';
            // }			
        //}	
}

record_set("get_answer", "select * from answers where answerval <11 order by id desc");				
while($row_get_answer_details = mysqli_fetch_assoc($get_answer)){
    $answerval = $row_get_answer_details['answerval'];
    $updated_answer = $answerval*10;
    $data = array(
        "answerval" =>$updated_answer
    );
    $updte=	dbRowUpdate("answers", $data, "where id=".$row_get_answer_details['id']);
}
echo ' done'; die();
?>