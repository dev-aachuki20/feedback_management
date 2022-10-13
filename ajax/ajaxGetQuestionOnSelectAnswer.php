<?php
    include('../function/function.php');
    
    if(isset($_POST['questionDetailId'])){
        
        $que_detail = explode('--',$_POST['questionDetailId']);
        $queId = $que_detail[0]; 
        $surveyid = $_POST['surveyId'];
        $parentqueid = $_POST['parentqueid'];
        //$langid = $_POST['langId'];
        $child_answer = array();
        $response = '';
        $questionId = '';

        

        record_set("get_question_id", "select * from questions_detail where id=".$queId." and condition_yes_no='1' and condition_qid!='' ");
        $row_get_question_id = mysqli_fetch_assoc($get_question_id);
        $questionId = $row_get_question_id['condition_qid'];
        record_set("get_question", "select * from questions where id=".$questionId." ");
        $row_get_question = mysqli_fetch_assoc($get_question);
        $required ='';
        if($row_get_question['ifrequired'] == 1){
            $required ='required';
        }
       
        // Radio Option
        if($row_get_question['answer_type'] == 1){
                record_set("get_question_detail", "select * from questions_detail where questionid='".$questionId."' and surveyid='".$surveyid."' and cstatus='1' ");
            if($totalRows_get_question_detail>0){	
                
                while($row_get_question_detail = mysqli_fetch_assoc($get_question_detail)){
                    
                   $child_answer[$row_get_question_detail['id']]= $row_get_question_detail['description'];     
                        
                }

                $question_radiobutton = $row_get_question['question'];

                $response .='<table class="table table-hover table-bordered"><tbody><tr align="center">
                                <td colspan="'.count($child_answer).'"><strong>'.$question_radiobutton.'</strong></td> 
                            </tr>';  
                $response .='<tr align="center">';
                foreach($child_answer as $key=>$child_answer_option){
            
                    $response .='<td>
                                    <input type="radio" class="form-check-input subque" name="answerid['.$row_get_question['id'].']" value="'.$key.'--'.$child_answer_option.'"  '.$required.'> '.$child_answer_option.'
                                </td>';
                }
                $response .='</tr></tbody></table>';
                $response .='<input type="hidden" name="questionid[]" value="'.$row_get_question['id'].'">';
            }
        }

        // Text Box Option
        if($row_get_question['answer_type'] == 2){

            $question_textbox = $row_get_question['question'];

            $response .='<div class="question_container_'.$row_get_question['id'].'">
                            <h4>'.$question_textbox.'</h4>';

            $response .='<div class="form-group">
                            <input type="text" name="answerid['.$row_get_question['id'].']" id="answerid['.$row_get_question['id'].']" value="" class="form-control" '.$required.'>
                            <input type="hidden" name="questionid[]" value="'.$row_get_question['id'].'" '.$required.'>
                        </div>
                        </div>';

        }

        // TextArea Option
        if($row_get_question['answer_type'] == 3){
            $question_textarea = $row_get_question['question'];

            $response .='<div class="question_container_'.$row_get_question['id'].'">
                            <h4>'.$question_textarea.'</h4>';
            $response .='<div class="form-group">
                            <textarea name="answerid['.$row_get_question['id'].']"  id="answerid_'.$row_get_question['id'].'" value="" class="form-control" '.$required.'></textarea>
                            <input type="hidden" name="questionid[]" value="'.$row_get_question['id'].'">
                        </div>';
            $response .='</div>';            
        }

        // Ratting Option
        if($row_get_question['answer_type'] == 4){

            $quetion_rating = $row_get_question['question'];

           $response .='<div class="question_container_'.$questionId.'">
                        <h4>'.$quetion_rating.'</h4>';

                record_set("get_question_detail", "select * from questions_detail where questionid='".$questionId."' and surveyid='".$surveyid."' and cstatus='1' ");
                if($totalRows_get_question_detail>0){	
                    
                    while($row_get_question_detail = mysqli_fetch_assoc($get_question_detail)){
                        
                        $child_answer[$row_get_question_detail['id']]= $row_get_question_detail['description'];     
                            
                    }
                $response .='<table class="table table-hover table-bordered">
                            <tbody>
                            <tr>';
                            foreach($child_answer as $key=>$child_answer_option){
                                $response .='<td align="center">'.$child_answer_option.'</td>';
                            }
                $response .='</tr>';

                $ans_count = 0;
                $show_smily = 0;
                $smily_loop = 0;
                $ans_count = count($child_answer);
                if($ans_count==2 || $ans_count==3 || $ans_count==5 || $ans_count==11){
                    $show_smily = 1;
                }
                $imgSrc ='';
                $smileIcon = '';
                $smileInput = '';
               

                $response .='<tr align="center">';
                     
                    foreach($child_answer as $key=>$child_answer_option){ 
                       $response .='<td class="show_smily_'.$show_smily.' smile-block"><label>';

                         if($ans_count==2){
                            $imgSrc ="dist/img/".strtolower($child_answer_option).".png";
                        }else{
                            $imgSrc = smile_format_icon($smily_loop,$ans_count);
                        } 
                        if($show_smily==1){
                            $smileIcon = 'style="visibility:hidden;"';
                            $smileInput = 'smily_icon_input';
                        }
                       if($show_smily==1){ 
                            $response.='<div>
                                    <img style="width:30px" class="smily_icon" src="'.$imgSrc.'">
                            </div>';
                            
                            $smily_loop++;
                            //$show_smily++;
                        } 

                        $response .='<input '.$smileIcon.' type="radio" class="form-check-input '.$smileInput.'" name="answerid['.$row_get_question['id'].']" value="'.$key.'--'.$child_answer_option.'"  '.$required.'>
                            </label>
                            </td>';
                    }
                    
                    $response .='</tr>
                    </tbody>
                </table>';
                
                $response .='<input type="hidden" name="questionid[]" value="'.$row_get_question['id'].'">';
                $response .='</div>';
            }

        }

        // Title Option
        if($row_get_question['answer_type'] == 5){

            $question_title =$row_get_question['question'];

            $response .='<div class="question_container_'.$row_get_question['id'].'">
                            <h4>'.$question_title.'</h4>';

            record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionId."' and surveyid='".$surveyid."' and cstatus='1' ");
                if($totalRows_get_questions_detail>0){
                    while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){

                        $response .='<h5>'.$row_get_questions_detail['description'].'</h5>';
                    }
                }
            $response .='</div>';    
        }

        // DropDown Option
        if($row_get_question['answer_type'] == 6){
            $question_dropdown = $row_get_question['question'];

            $response .='<div class="question_container_'.$row_get_question['id'].'">
                        <h4>'.$question_dropdown.'</h4>';
            $response .='<div class="form-group">
            <select name="answerid['.$row_get_question['id'].']" '.$required.' class="form-control subque_select" data-questionid="'.$row_get_question['id'].'">
                <option value="">Select</option>';

                record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionId."' and surveyid='".$surveyid."' and cstatus='1' ");
                if($totalRows_get_questions_detail>0){
                    while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
                        $selectOption = $row_get_questions_detail['description'];

                        $response .='<option value="'.$row_get_questions_detail['id'].'--'.$row_get_questions_detail['answer'].'">'.$selectOption.'</option>';
                
                    }
                }
                
            $response .='</select>
                <input type="hidden" name="questionid[]" value="'.$row_get_question['id'].'">
                </div>';
            $response .='</div>';
        }

      echo $response;
    }
    

?>