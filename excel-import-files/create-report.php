<?php
    require('../excel_library/Classes/PHPExcel.php');
    require('../function/connectin_config.php');
    require('../function/get_data_function.php');
    include('../permission.php');
    $conn = get_connection();
    
    $filename = "Asset_Data_".date('Y_m_d_H_i_s').".xlsx";
    if($_GET['export']==1){
        // coloumn heading for excel 
        $arrayHeading = array(
            "A1"    => "STEP",
            "B1" 	=> "SURVEY",
            "C1"    => "QUESTION",
            "D1" 	=> "ANSWER",
            "E1" 	=> "LOCATION",
            "F1" 	=> "DEPARTMENT",
            "G1" 	=> "GROUP",
        );
    
        // $filterData = json_decode($_POST['hidden'],1);
        // $surveyId   = $filterData['survey_id'];
        // $sectionId  = $filterData['section_id'];
        // $questionId = $filterData['question_id'];
        //filter data 
        $surveyId   = $_POST['survey_hidden'];
        $sectionId  = $_POST['step_hidden'];
        $questionId = $_POST['question_hidden'];
        //checkboxes value
        $location   = $_POST['locationids'];
        $department = $_POST['departmentids'];
        $group      = $_POST['groupids'];

        $location_id    = implode(',', $_POST['locationids']);
        $department_id  = implode(',', $_POST['departmentids']);
        $group_id       = implode(',', $_POST['groupids']);
        
        $filterData     = '';
        if($location){
            $filterData .= " and locationid IN($location_id)";
        }
        if($department){
            $filterData .= " and departmentid IN($department_id)";
        }
        if($group){
            $filterData .= " and groupid IN($group_id)";
        }
        $query = "SELECT id,question,survey_step_id FROM `questions` WHERE `id` !=0";
        if(!empty($surveyId)){
            $query .=" and surveyid=$surveyId";
        }
        if(!empty($sectionId)){
            $query .=" and survey_step_id=$sectionId";
        }
        if(!empty($questionId)){
            $query .=" and id=$questionId";
        }
        //record_set("get_result", $query,1);
        $get_result =  mysqli_query($conn,$query);
        $question_data = array();	
        while($row_get_step = mysqli_fetch_assoc($get_result)){
            $get_answer_result =  mysqli_query($conn,"SELECT * FROM `answers` WHERE `questionid` =". $row_get_step['id']."$filterData");
            if(mysqli_num_rows($get_answer_result)>0){
                $i=0;
                while($row_get_answer = mysqli_fetch_assoc($get_answer_result)){
                    $question_data[$row_get_step['id']]['id'] = $row_get_step['id'];
                    $question_data[$row_get_step['id']]['surveyid'] = $row_get_answer['surveyid'];
                    $question_data[$row_get_step['id']]['step_id'] = $row_get_step['survey_step_id'];

                    $question_data[$row_get_step['id']]['location'] = $row_get_answer['locationid'];
                    $question_data[$row_get_step['id']]['department'] = $row_get_answer['departmentid'];
                    $question_data[$row_get_step['id']]['group'] = $row_get_answer['groupid'];
                    $question_data[$row_get_step['id']]['answertext'][$i]['ans'] = $row_get_answer['answertext'];
                    $question_data[$row_get_step['id']]['answertext'][$i]['ansid'] = $row_get_answer['answerid'];
                    $i++;
                }
            }
        }
        $objPHPExcel = new PHPExcel;
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->getStyle('A1:Z1')->getFont()->setBold(true)->setSize(12);
        foreach($arrayHeading as $key=> $value){
            $objSheet->getCell($key)->setValue($value);
        }
    
        foreach($question_data as $ques){
            // get step name
            $get_step_name =  mysqli_query($conn,"SELECT * FROM `surveys_steps` WHERE `id` =". $ques['step_id']);
            $row_get_step_name = mysqli_fetch_assoc($get_step_name);

            //get question
            $get_question_name =  mysqli_query($conn,"SELECT * FROM `questions` WHERE `id` =". $ques['id']);
            $row_get_question_name = mysqli_fetch_assoc($get_question_name);

            //get survey name
            $get_survey_name =  mysqli_query($conn,"SELECT * FROM `surveys` WHERE `id` =". $ques['surveyid']);
            $row_get_survey_name = mysqli_fetch_assoc($get_survey_name);

            //get location
            if(!empty($ques['location'])){
                $get_location_name =  mysqli_query($conn,"SELECT * FROM `locations` WHERE `id` =". $ques['location']);
                $row_get_location_name = mysqli_fetch_assoc($get_location_name);
                $locationName = $row_get_location_name['name'];
            }else {
                $locationName = 'N/A';
            }

            //get department
            if(!empty($ques['department'])){
                $get_department_name =  mysqli_query($conn,"SELECT * FROM `departments` WHERE `id` =". $ques['department']);
                $row_get_department_name = mysqli_fetch_assoc($get_department_name);
                $departmentName = $row_get_department_name['name'];
            }else {
                $departmentName = 'N/A';
            }
           
            //get group
            if(!empty($ques['group'])){
                $get_group_name =  mysqli_query($conn,"SELECT * FROM `groups` WHERE `id` =". $ques['group']);
                $row_get_group_name = mysqli_fetch_assoc($get_group_name);
                $groupName = $row_get_group_name['name'];
            }else {
                $groupName = 'N/A';
            }

            $surveyName     = $row_get_survey_name['name'];
            $stepName       = $row_get_step_name['step_title'];
            $questionName   = $row_get_question_name['question'];
            $i=1;
            foreach($ques['answertext'] as $answer){ $i++;
                if($answer['ans']==0){
                    $get_survey_questions_detail =  mysqli_query($conn,"SELECT * FROM `questions_detail` WHERE `id` =". $answer['ansid']);
                    $row_get_survey_questions_detail = mysqli_fetch_assoc($get_survey_questions_detail);
                    $answer_value = $row_get_survey_questions_detail['description'];
                }else {
                    $answer_value = $answer['ans'];
                }
                $objSheet->getCell('A'.$i)->setValue(trim($stepName));
                $objSheet->getCell('B'.$i)->setValue($surveyName);
                $objSheet->getCell('C'.$i)->setValue($questionName);
                $objSheet->getCell('D'.$i)->setValue($answer_value);
                $objSheet->getCell('E'.$i)->setValue($locationName);
                $objSheet->getCell('F'.$i)->setValue($departmentName);
                $objSheet->getCell('G'.$i)->setValue($groupName);
            }
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename='.$filename);
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        
    }
?>