<?php 
include('../../function/function.php');
include('../../function/get_data_function.php');

$requestData= $_REQUEST;
$columns = array( 
    0 =>'answers.cdate', 
    1 => 'name',
    2 => 'cdate',
    3 => 'cdate',
    4 => 'cdate', 
);

if(!empty($requestData['survey_name'])){
    record_set('getSurveyname','select * from surveys where id="'.$requestData['survey_name'].'"');
    $row_getSurveyname = mysqli_fetch_assoc($getSurveyname);
    $filterQuery = "";
    if(isset($requestData['survey_name']) && $requestData['survey_name'] != '' && $requestData['survey_name'] != 0){
      $filterQuery .= "and answers.surveyid=".$requestData['survey_name'];
    }
    // filter by date
    if(!empty($requestData['fdate']) && !empty($requestData['sdate'])){  
        $filterQuery .= " and answers.cdate between '".date('Y-m-d', strtotime($requestData['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($requestData['sdate'])))."'";
    }

    // filter by departmentid
    if(isset($requestData['departmentid']) && $requestData['departmentid'] != ''){
        if($requestData['departmentid'] == 4){
            if(!empty($filterQuery)){
                $filterQuery .= " and answers.departmentid in (select id from departments where cstatus=1)";    
            }else{
                $filterQuery .= " and answers.departmentid in (select id from departments where cstatus=1)"; 
            }
        }else{
            if(!empty($filterQuery)){  
                $filterQuery .= " and answers.departmentid = '".$requestData['departmentid']."'";   
            }else{
                $filterQuery .= " and answers.departmentid = '".$requestData['departmentid']."'";
            }
        }
    }

    // filter by groupid
    if(isset($requestData['groupid']) && $requestData['groupid'] != ''){
        if($requestData['groupid'] == 4){
            if(!empty($filterQuery)){
                $filterQuery .= " and answers.groupid in (select id from groups where cstatus=1)";    
            }else{
                $filterQuery .= " and answers.groupid in (select id from groups where cstatus=1)"; 
            }
        }else{
            if(!empty($filterQuery)){  
                $filterQuery .= " and answers.groupid = '".$requestData['groupid']."'";   
            }else{
                $filterQuery .= " and answers.groupid = '".$requestData['groupid']."'";
            }
        }
    }

    // filter by locationid
    if(isset($requestData['locationid']) && $requestData['locationid'] != ''){
      if($requestData['locationid'] == 4){
        if(!empty($filterQuery)){
          $filterQuery .= " and answers.locationid in (select id from locations where cstatus=1)";    
        }else{
          $filterQuery .= " and answers.locationid in (select id from locations where cstatus=1)"; 
        }
      }else{
        if(!empty($filterQuery)){  
          $filterQuery .= " and answers.locationid = '".$requestData['locationid']."'";   
        }else{
          $filterQuery .= " and answers.locationid = '".$requestData['locationid']."'";
        }
      }
    }else{
      $filterQuery .= $locationJoinCondition;
    }
    $query = "SELECT answers.surveyid,answers.locationid,surveys.name,answers.cdate FROM `answers` INNER JOIN surveys ON answers.surveyid=surveys.id where answers.surveyid!=0 $filterQuery group by YEAR(answers.cdate), MONTH(answers.cdate)";
    record_set("survey_detail",$query);

    if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
        $query.=" AND ( DATE(answers.cdate) LIKE '".$requestData['search']['value']."%' ";    
        $query.="  )";
    }
   
    $query.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
    
    //$query.=" ORDER BY cdate DESC   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
    record_set("get_recent_entry",$query);

    $data = array();
    if($totalRows_survey_detail>0){
        while($row_survey_detail = mysqli_fetch_assoc($get_recent_entry)){
            $nestested = array();
            $surveyFilter = '';
            if($requestData['survey_name'] != 0){
                $surveyFilter = " and surveyid=".$requestData['survey_name'];
            }
            if(isset($requestData['locationid']) && $requestData['locationid'] != '' && $requestData['locationid'] != 4 ){
                $surveyFilter .= " and locationid=".$requestData['locationid'];
            }
            record_set("survey_count","SELECT * from answers where cdate like '".date_month_qry($row_survey_detail['cdate'])."-%'  $surveyFilter group by cdate");
            //record_set("survey_count","SELECT * from answers where cdate like '2022-10-%' and surveyid=20 group by cdate");

            $nestested[] = date_formate_month($row_survey_detail['cdate']);
            $nestested[] = getSurvey()[$requestData['survey_name']];
            $nestested[] =  $totalRows_survey_count;

             //Average Result Score
            // record_set("average_survey_result","SELECT COUNT(answerval) AS survey_count, SUM(answerval) AS survey_val_sum FROM answers WHERE surveyid='".$row_survey_detail['surveyid']."' AND locationid='".$row_survey_detail['locationid']."' AND cdate like '".date_month_qry($row_survey_detail['cdate'])."-%' GROUP BY cby ORDER BY cdate DESC");
            // $achieved_result_val = 0;
            // $result_score = 0;
            // if($totalRows_average_survey_result > 0){
            //     while($row_average_survey_result = mysqli_fetch_assoc($average_survey_result)){
            //     $achieved_result_val += floatval($row_average_survey_result['survey_val_sum'] * 100) / floatval($row_average_survey_result['survey_count'] * 100);
            //     }
            //     $result_score = floatval($achieved_result_val) / intval($totalRows_average_survey_result);
            // } 
            $result_response = 0;
            $result_response_value= 0;
            $count = 0;
            while($row_get_recent_entry = mysqli_fetch_assoc($survey_count)){
                $total_result_val=0;
                record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_get_recent_entry['surveyid']."' and cby='".$row_get_recent_entry['cby']."'");
    
                $achieved_result_val = 0;
                $to_bo_contacted     = 0;
                $i=0;
                
                while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where id =".$row_get_survey_result['questionid']);
                    if($result_question){
                        if(!in_array($result_question['answer_type'],array(2,3,5))){
                            $total_result_val = ($i+1)*100;
                            $achieved_result_val += $row_get_survey_result['answerval'];
                            $i++;
                        }
                    }
                    if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 10){
                        $to_bo_contacted = 1;
                    }
                }
                $result_response += $achieved_result_val*100/$total_result_val;
                $count++;
            } 
            
            $result_response_value = $result_response/$count;
            if(is_nan($result_response_value)){
                $result_response_value=100;
            }
            $nestested[] = round($result_response_value,2).'%';
            $nestested[] = '<div class="action-btn"><a class="btn btn-xs btn-primary " href="export-pdf.php?surveyid='.$row_survey_detail['surveyid'].'&amp;month='.date_month_qry($row_survey_detail['cdate']).'&location='.$requestData['curr_loc_id'].'" target="_blank">View PDF</a> <a class="btn btn-xs btn-primary" href="export-result.php?surveyid='.$row_survey_detail['surveyid'].'&month='.date_month_qry($row_survey_detail['cdate']).'&location='.$requestData['curr_loc_id'].'&name='.$row_getSurveyname['name'].'" target="_blank">Download CSV</a></div>';

            $data[] = $nestested;
        }
        
    }
}

// $keys = array_column($data, '2');

// array_multisort($keys, SORT_DESC, $data);

// print_r($data);
// die();
$json_data = array(
    "draw"            => intval( $requestData['draw'] ),
    "recordsTotal"    => intval( $totalRows_survey_detail ), 
    "recordsFiltered" => intval( $totalRows_survey_detail ), 
    "data"            => $data  
);
echo json_encode($json_data ); die();
?>