<?php
        include('function/function.php');
        $data =  json_decode($_POST['post_values'],1);
       
        $dateflag= false;
        $query = 'SELECT * FROM answers ';
        if(!empty($data['fdate']) && !empty($data['sdate'])){  
            $query .= " where cdate between '".date('Y-m-d', strtotime($data['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($data['sdate'])))."'";
            $dateflag= true;
        }

        if(!empty($data['departmentid'])){
            if($data['departmentid'] == 4){
                record_set("get_all_department","select id from departments where cstatus=1");	
                $all_departments = array();
                while($row_get_all_department = mysqli_fetch_assoc($get_all_department)){
                    $all_departments[] = $row_get_all_department['id'];
                }
                if($dateflag == true){
                    $query .= " and departmentid in (".implode(',',$all_departments).")";
                }else{
                    $query .= " where departmentid in (".implode(',',$all_departments).")";
                }  
            }else{
                if($dateflag == true){
                    $query .= " and departmentid = '".$data['departmentid']."'";
                }else{
                    $query .= " where departmentid = '".$data['departmentid']."' ";
                }
            }
        }

        if(!empty($data['locationid'])){
            if($data['locationid'] == 4){
                $query .= " and locationid in (select id from locations where cstatus=1)";  
            }else{
                if($dateflag == true){
                    $query .= "and locationid = '".$data['locationid']."'";
                }else{
                    $deptflag = (!empty($data['departmentid']))?'and':'where';
                    $query .= "".$deptflag." locationid = '".$data['locationid']."'";
                }
            }
        }
        // else{
        //     $query .= $locationQueryAndCondition;
        // }
        if(!empty($data['surveys'])){
            $query .= " where surveyid =".$data['surveys'];
            $dateflag= true;
        }
       
        $query .= " GROUP by cby order by cdate DESC";
    
        record_set("get_departments", "SELECT * FROM departments");	
        $departments = array();
        while($row_get_departments = mysqli_fetch_assoc($get_departments)){
            $departments[$row_get_departments['id']] = $row_get_departments['name'];
        }
        
        record_set("get_recent_entry",$query);	
        if($totalRows_get_recent_entry >0){
            $i=0;
            $delimiter = ","; 
            $filename = "members-data_" . date('Y-m-d') . ".csv"; 
 
            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 
            
            // Set column headers 
            $fields = array('Date Time', 'Survey','Response', 'Result Score', 'Contacted'); 
            fputcsv($f, $fields, $delimiter); 
            while($row_get_recent_entry = mysqli_fetch_assoc($get_recent_entry)){ 
                $i++;
                record_set("get_survey_detail", "SELECT * FROM surveys where id='".$row_get_recent_entry['surveyid']."'");	
                $row_get_survey_detail = mysqli_fetch_assoc($get_survey_detail);
                $row_survey_entry = 1;
                record_set("survey_entry", "SELECT DISTINCT cby FROM answers where surveyid='".$row_get_survey_detail['id']."' and cby <".$row_get_recent_entry['cby']);
                $row_survey_entry = $totalRows_survey_entry+$row_survey_entry;
                record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_get_recent_entry['surveyid']."' and cby='".$row_get_recent_entry['cby']."'");
                $total_result_val = $totalRows_get_survey_result*10;
                $achieved_result_val = 0;
                $to_bo_contacted = 0;
                while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
                $achieved_result_val += $row_get_survey_result['answerval'];
                if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100){
                    $to_bo_contacted = 1;
                }
                }
                $result_response = $achieved_result_val*100/$total_result_val;
                $date = date("d-m-Y", strtotime($row_get_recent_entry['cdate']));
                if($to_bo_contacted==1){
                    $contact = "Yes";
                }else{ 
                    $contact = "No";
                } 
                $lineData = array();
                record_set("get_question_detail", "SELECT questions_detail.answer,questions.question as ques FROM questions_detail 
                LEFT JOIN questions
                ON questions.id = questions_detail.questionid
                WHERE questions_detail.surveyid=".$row_get_recent_entry['surveyid']);
                while($row_get_question_detail = mysqli_fetch_assoc($get_question_detail)){
                    array_push($lineData, $row_get_question_detail['ques'],$row_get_question_detail['answer']);
                }
                // Output each row of the data, format line as csv and write to file pointer
                $lineData_new = array($date, $row_get_survey_detail['name'],ordinal($row_survey_entry), $result_response, $contact); 
                $final_data =  array_merge($lineData_new, $lineData);
                fputcsv($f, $final_data, $delimiter);     
            }

            // Move back to beginning of file 
            fseek($f, 0); 
                
            // Set headers to download file rather than displayed 
            header('Content-Type: text/csv'); 
            header('Content-Disposition: attachment; filename="' . $filename . '";'); 
    
            //output all remaining data on a file pointer 
            fpassthru($f); 

            exit; 
        }
    
?>