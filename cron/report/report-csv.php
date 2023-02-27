<?php
require('../../function/function.php');
require('../../function/get_data_function.php');
include('../../permission.php');
record_set("get_scheduled_report","select srt.* from scheduled_report_templates as srt INNER JOIN report_templates as rt ON srt.temp_id = rt.id where rt.report_type=1");

while($row_get_report= mysqli_fetch_assoc($get_scheduled_report)){
    $current_date   = date('Y-m-d', time());
    $end_date       = date('Y-m-d', strtotime($row_get_report['end_date']));
    $next_schedule  = date('Y-m-d', strtotime($row_get_report['next_date']));
    $result_1   = check_differenceDate($current_date,$end_date,'lte');
    $result_2   = check_differenceDate($current_date,$next_schedule,'eq');
    if($result_1 && $result_2){
        require_once dirname(__DIR__,2).'/vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf();
        $filter = json_decode($row_get_report['filter'],1);
        $data_type = $filter['field'];
        $survey_id   = $filter['survey_id'];
        $field_value = implode(',',$filter['field_value']);
        if(is_array($survey_id)){
            $survey_id = implode(',',$survey_id);
        }
        // fetch survey data
        $querys = 'SELECT * FROM answers where id!=0 ';
        $groupBy = '';
        if($data_type == 'location'){
            $query = " and surveyid =".$survey_id." and locationid in ($field_value)";  
            $groupBy = 'locationid';
        }
        else if($data_type=='group'){
            $query = " and surveyid =".$survey_id." and groupid in ($field_value)";  
            $groupBy = 'group';
        }
        else if($data_type=='department'){
            $query = " and surveyid =".$survey_id." and departmentid in ($field_value)";
            $groupBy = 'departmentid';
        }
        else {
            $query = " and surveyid IN (select id from surveys where id IN($survey_id ))";
            $groupBy = 'surveyid';
        }

        record_set("total_survey","SELECT COUNT(DISTINCT(cby)) as totalCount FROM answers WHERE id!=0  $query");

        $row_total_survey = mysqli_fetch_assoc($total_survey);
        $total_survey = $row_total_survey['totalCount'];
        record_set("get_entry",$querys.$query." GROUP by cby");
        if($totalRows_get_entry){
            $survey_data = array();
            $to_bo_contacted = 0;
            while($row_get_entry = mysqli_fetch_assoc($get_entry)){
                $locId      = $row_get_entry['locationid'];
                $depId      = $row_get_entry['departmentid'];
                $grpId      = $row_get_entry['groupid'];
                $surveyid   = $row_get_entry['surveyid'];
                $cby        = $row_get_entry['cby'];
                
                if($data_type=='location'){
                    $count = array();
                    record_set("get_question","select * from answers where locationid=$locId and cby=$cby");
                    $total_answer = 0;
                    $i=0;
                    $total_result_val = 0;
                    while($row_get_question= mysqli_fetch_assoc($get_question)){
                        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where id =".$row_get_question['questionid']);
                        if($result_question){
                            if(!in_array($result_question['answer_type'],array(2,3,5))){
                            $i++;
                                $total_answer += $row_get_question['answerval'];
                            }
                        }
                        if($row_get_question['answerid'] == -2 && $row_get_question['answerval'] == 100){
                            //$to_bo_contacted += 1;
                            $survey_data[$locId]['contact'] += 1;
                        }
                    }
                    $average_value = ($total_answer/($i*100))*100;
                    if($total_answer==0 and $total_result_val==0){
                        $average_value=100;
                    }
                    $survey_data[$locId]['data'][$cby] = $average_value;
                    
                }
                else if($data_type=='department'){
                    $count = array();
                    record_set("get_question","select * from answers where departmentid=$depId and cby=$cby");
                    $total_answer = 0;
                    $i=0;
                    $total_result_val = 0;
                    $to_bo_contacted     = 0;
                    while($row_get_question= mysqli_fetch_assoc($get_question)){
                        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where id =".$row_get_question['questionid']);
                        if($result_question){
                            if(!in_array($result_question['answer_type'],array(2,3,5))){
                            $i++;
                                $total_answer += $row_get_question['answerval'];
                            }
                        }
                        if($row_get_question['answerid'] == -2 && $row_get_question['answerval'] == 100){
                            $survey_data[$depId]['contact'] += 1;
                        }
                    }
                    $average_value = ($total_answer/($i*100))*100;
                    if($total_answer==0 and $total_result_val==0){
                        $average_value=100;
                    }
                    $survey_data[$depId]['data'][$cby] = $average_value;
                }
                else if($data_type=='group'){
                    $count = array();
                    record_set("get_question","select * from answers where groupid=$grpId and cby=$cby");
                    $total_answer = 0;
                    $i=0;
                    $total_result_val = 0;
                    while($row_get_question= mysqli_fetch_assoc($get_question)){
                        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where id =".$row_get_question['questionid']);
                        if($result_question){
                            if(!in_array($result_question['answer_type'],array(2,3,5))){
                            $i++;
                                $total_answer += $row_get_question['answerval'];
                            }
                        }
                        if($row_get_question['answerid'] == -2 && $row_get_question['answerval'] == 100){
                            $survey_data[$grpId]['contact'] += 1;
                        }
                    }
                    $average_value = ($total_answer/($i*100))*100;
                    if($total_answer==0 and $total_result_val==0){
                        $average_value=100;
                    }
                    $survey_data[$grpId]['data'][$cby] = $average_value;
                }
                else {
                
                    $count = array();
                    record_set("get_question","select * from answers where surveyid=$surveyid and cby=$cby");
                    $total_answer = 0;
                    $i=0;
                    $total_result_val = 0;
                    while($row_get_question= mysqli_fetch_assoc($get_question)){
                        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where id =".$row_get_question['questionid']);
                        if($result_question){
                            if(!in_array($result_question['answer_type'],array(2,3,5))){
                            $i++;
                                $total_answer += $row_get_question['answerval'];
                            }
                        }
                        if($row_get_question['answerid'] == -2 && $row_get_question['answerval'] == 100){
                            $survey_data[$surveyid]['contact'] += 1;
                        }
                    }
                    if($total_answer==0 and $total_result_val==0){
                        $average_value=100;
                    }
                    $average_value = ($total_answer/($i*100))*100;
                    if(is_nan($average_value)){
                        $average_value = 100;
                    }
                    $survey_data[$surveyid]['data'][$cby] = $average_value;
                }
            }
        }
        ksort($survey_data);
        $survey_name = getSurvey()[$survey_id];
        $dir = 'document/survey-report-'.$row_get_report['id'].'.csv';
        $path[] = $dir;
        download_csv_folder($survey_data,$data_type,$dir);
    }
}
?>