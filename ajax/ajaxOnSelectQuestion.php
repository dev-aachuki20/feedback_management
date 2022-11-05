<?php
    include('../function/function.php');
    
    if(isset($_POST['questionId'])){
          
        $questionId = implode(',',$_POST['questionId']);
        $user_id = $_POST['user_id'];
        $surveyid = $_POST['surveyid'];
            record_set("get_add_question", "select * from questions where id not in(".$questionId.") and cby='".$user_id."' and surveyid='".$surveyid."' and survey_step_id='0'");	
          
            $response = '';
            while($row_get_add_question = mysqli_fetch_assoc($get_add_question)){
                record_set("get_question", "select * from questions_detail where surveyid='".$surveyid."' and condition_qid='".$row_get_add_question['id']."'");
                // $row_get_question = mysqli_fetch_assoc($get_question);
                // print_r(' '.$row_get_question['questionid'].' ');
                if($totalRows_get_question == 0){
                    $response .='<option value="'.$row_get_add_question['id'].'" data-qid="'.$row_get_add_question['id'].'">'.$row_get_add_question['question'].'</option>';
                }
            } 
            echo $response;
        
    }
    

?>