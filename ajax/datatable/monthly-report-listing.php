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
                $filterQuery .= " and answers.groupid in (select id from `groups` where cstatus=1)";    
            }else{
                $filterQuery .= " and answers.groupid in (select id from `groups` where cstatus=1)"; 
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
    $f ="LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
    record_set("survey_date",'select DATE(cdate) as cdate from answers group by DATE(cdate) order by cdate ');
    $row_get_survey_date = mysqli_fetch_assoc($survey_date);
    $date_array =array();
    $a = 0;
    $startDate = $row_get_survey_date['cdate'];
    while($end <= date("Y-m-d")){
        //echo $end.':'.date("Y-m-d"); echo '<br>';
        if($end >= date("Y-m-d")){
            break;
        }
        $date_array[$a]['start']= $startDate;
        
        if($requestData['interval'] ==24){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+1 days"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==168){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+7 days"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==336){
            $end = date("Y-m-d",strtotime($startDate."+14 days"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==720){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+1 month"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==2160){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+3 month"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==4320){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+6 month"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==8640){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+1 years"));
            $date_array[$a]['end']=  $end;
        }else {
            $end = date("Y-m-d",strtotime($startDate."+1 days"));
            $date_array[$a]['end']=  $end;
        }
        $startDate =  $end;
        $a++;
    }
    $data =array();
    foreach($date_array as $date){
        $nestested = array();
        $query = "SELECT answers.surveyid as surveyid,answers.cby as cby,answers.locationid,surveys.name,answers.cdate FROM `answers` INNER JOIN surveys ON answers.surveyid=surveys.id where answers.surveyid!=0 $filterQuery and answers.cdate between '".$date['start']."' and '".$date['end']."' group by answers.cby";

        record_set("survey_detail",$query);

        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter,   $requestData['search']['value'] contains search parameter
            $query.=" AND ( DATE(answers.cdate) LIKE '".$requestData['search']['value']."%' ";    
            $query.="  )";
        }
    
        $query.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        
        //$query.=" ORDER BY cdate DESC   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        record_set("get_recent_entry",$query);
        $nestested[] = $date['start'];
        $nestested[]=getSurvey()[$requestData['survey_name']];
        if($totalRows_get_recent_entry>0){
            
            while($row_survey_detail = mysqli_fetch_assoc($get_recent_entry)){

                record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_survey_detail['surveyid']."' and cby='".$row_survey_detail['cby']."'");
                $total_result_val=0;
                $achieved_result_val = 0;
                $to_bo_contacted     = 0;
                $i=0;
                $contactedCount = 0;
                $count= 0;
                $result_response = 0;
                while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_survey_result['questionid']);
                    if($result_question){
                        if(!in_array($result_question['answer_type'],array(2,3,5))){
                            $total_result_val = ($i+1)*100;
                            $achieved_result_val += $row_get_survey_result['answerval'];
                            $i++;
                        }
                    }
                    if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100){
                        $to_bo_contacted = 1;
                        $contactedCount++;
                    }
                }
                //echo $achieved_result_val.' : '.$total_result_val;
                $result_response += $achieved_result_val*100/$total_result_val;
                $count++;
            }
            
            $result_response_value = $result_response/$count;
            if(is_nan($result_response_value)){
                $result_response_value=100;
            }

            $nestested[] = $count;
            $nestested[] = $contactedCount;
            $nestested[] = round($result_response_value,2).'%';
            $nestested[] = '<div class="action-btn"><a class="btn btn-xs btn-primary " href="export-pdf.php?surveyid='.$requestData['survey_name'].'&amp;start='.$date['start'].'&end='.$date['end'].'&location='.$requestData['curr_loc_id'].'" target="_blank">View PDF</a> <a class="btn btn-xs btn-primary" href="export-result.php?surveyid='.$row_survey_detail['surveyid'].'&start='.$date['start'].'&end='.$date['end'].'&location='.$requestData['curr_loc_id'].'&name='.$row_getSurveyname['name'].'" target="_blank">Download CSV</a></div>';

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
    "recordsTotal"    => intval( count($data) ), 
    "recordsFiltered" => intval( count($data) ), 
    "data"            => $data  
);
echo json_encode($json_data ); die();
?>