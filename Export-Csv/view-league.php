<?php
include ('../function/function.php');
include ('../function/get_data_function.php');

$data =array();
$answer_query   = 'SELECT * FROM answers where id!=0 ';
$survey_type = $_GET['survey_type'];
$surveyid    = $_GET['survey'];
$fdate       = $_GET['fdate'];
$sdate       = $_GET['sdate'];
$surveyName = getSurvey()[$surveyid];

if($survey_type=='location'){
    $query = " and surveyid =".$surveyid." and locationid in (select id from locations where cstatus=1)";  
    $groupBy = 'locationid';
}
//survey group
else if($survey_type=='group'){
    $query = " and surveyid =".$surveyid." and groupid in (select id from `groups` where cstatus=1)";  
    $groupBy = 'group';
}
//survey department
else if($survey_type=='department'){
    $query = " and surveyid =".$surveyid." and departmentid in (select id from departments where cstatus=1)";
    $groupBy = 'departmentid';
}
else if($survey_type=='role'){
    $query = " and surveyid =".$surveyid." and roleid in (select id from roles where cstatus=1)";
    $groupBy = 'roleid';
}
if(!empty( $fdate) and !empty( $sdate)){
    $filter_date = " and  cdate between '".date('Y-m-d', strtotime( $fdate))."' and '".date('Y-m-d', strtotime("+1 day",strtotime( $sdate)))."'";
}

if(!empty( $fdate) and !empty( $sdate)){
    $filterDate = date('Y-m-d',(strtotime ( '-7 day' , strtotime ( $fdate) ) ));
    $lastWeekData = " and DATE(cdate) <= '".$filterDate ."'";
}else {
    $lastWeekData = " and DATE(cdate) <= '".date('Y-m-d', strtotime('-7 days'))."'";
}
// get total count of result
record_set("total_survey","SELECT COUNT(DISTINCT(cby)) as totalCount FROM answers WHERE id!=0  $query $filter_date");

$row_total_survey = mysqli_fetch_assoc($total_survey);
$total_survey = $row_total_survey['totalCount'];

//get all record from answer
record_set("get_entry",$answer_query.$query. $filter_date." GROUP by cby");
$survey_data = array();
if($totalRows_get_entry){
    while($row_get_entry = mysqli_fetch_assoc($get_entry)){
        $locId      = $row_get_entry['locationid'];
        $depId      = $row_get_entry['departmentid'];
        $grpId      = $row_get_entry['groupid'];
        $roleId     = $row_get_entry['roleid'];
        $surveyid   = $row_get_entry['surveyid'];
        $cby        = $row_get_entry['cby'];

        if($survey_type=='location'){
            $title = 'Location';
            $count = array();
            record_set("get_question","select * from answers where locationid=$locId and cby=$cby");
            $total_answer = 0;
            $i=0;
            $total_result_val = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_question['questionid']);
                if($result_question){
                    if(!in_array($result_question['answer_type'],array(2,3,5))){
                    $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
            }
            $average_value = ($total_answer/($i*100))*100;
            // if($total_answer==0 and $total_result_val==0){
            //     $average_value=100;
            // }
            if(is_nan($average_value)){
                $average_value=100;
            }
            $survey_data[$locId][$cby] = $average_value;
        }
        else if($survey_type=='department'){
            $title = 'Department';
            $count = array();
            record_set("get_question","select * from answers where departmentid=$depId and cby=$cby");
            $total_answer = 0;
            $i=0;
            $total_result_val = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_question['questionid']);
                if($result_question){
                    if(!in_array($result_question['answer_type'],array(2,3,5))){
                    $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
            }
            $average_value = ($total_answer/($i*100))*100;
            // if($total_answer==0 and $total_result_val==0){
            //     $average_value=100;
            // }
            if(is_nan($average_value)){
                $average_value=100;
            }
            $survey_data[$depId][$cby] = $average_value;
        }
        else if($survey_type=='group'){
            $title = 'Group';
            $count = array();
            record_set("get_question","select * from answers where groupid=$grpId and cby=$cby");
            $total_answer = 0;
            $i=0;
            $total_result_val = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_question['questionid']);
                if($result_question){
                    if(!in_array($result_question['answer_type'],array(2,3,5))){
                    $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
            }
            $average_value = ($total_answer/($i*100))*100;
            // if($total_answer==0 and $total_result_val==0){
            //     $average_value=100;
            // }
            if(is_nan($average_value)){
                $average_value=100;
            }
            $survey_data[$grpId][$cby] = $average_value;
        }
        else if($survey_type=='role'){
            $title = 'Role';
            $count = array();
            record_set("get_question","select * from answers where roleid=$roleId and cby=$cby");
            $total_answer = 0;
            $i=0;
            $total_result_val = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_question['questionid']);
                if($result_question){
                    if(!in_array($result_question['answer_type'],array(2,3,5))){
                    $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
            }
            $average_value = ($total_answer/($i*100))*100;
            // if($total_answer==0 and $total_result_val==0){
            //     $average_value=100;
            // }
            if(is_nan($average_value)){
                $average_value=100;
            }
            $survey_data[$roleId][$cby] = $average_value;
        }
    }
}
// get previous week data
record_set("total_survey_lastweek","SELECT COUNT(DISTINCT(cby)) as totalCount FROM answers WHERE id!=0 $query $lastWeekData");

$row_total_survey_lastweek = mysqli_fetch_assoc($total_survey_lastweek);
$total_survey_lastweek = $row_total_survey_lastweek['totalCount'];


//get all record from answer
record_set("get_entry_lastweek",$answer_query.$query. $lastWeekData." GROUP by cby");

$survey_data_lastweek = array();
if($totalRows_get_entry_lastweek){
while($row_get_entry_lastweek = mysqli_fetch_assoc($get_entry_lastweek)){
    $locId      = $row_get_entry_lastweek['locationid'];
    $depId      = $row_get_entry_lastweek['departmentid'];
    $grpId      = $row_get_entry_lastweek['groupid'];
    $roleId      = $row_get_entry_lastweek['roleid'];
    $surveyid   = $row_get_entry_lastweek['surveyid'];
    $cby        = $row_get_entry_lastweek['cby'];
    if($survey_type=='location'){
        $title = 'Location';
        $count = array();
        record_set("get_question","select * from answers where locationid=$locId and cby=$cby");
        $total_answer = 0;
        while($row_get_question= mysqli_fetch_assoc($get_question)){
            $total_answer += $row_get_question['answerval'];
        }
        $average_value = ($total_answer/($totalRows_get_question*100))*100;
        $survey_data_lastweek[$locId][$cby] = $average_value;
    }
    else if($survey_type=='department'){
        $title = 'Department';
        $count = array();
        record_set("get_question","select * from answers where departmentid=$depId and cby=$cby");
        $total_answer = 0;
        while($row_get_question= mysqli_fetch_assoc($get_question)){
            $total_answer += $row_get_question['answerval'];
        }
        $average_value = ($total_answer/($totalRows_get_question*100))*100;
        $survey_data_lastweek[$depId][$cby] = $average_value;
    }
    else if($survey_type=='group'){
        $title = 'Group';
        $count = array();
        record_set("get_question","select * from answers where groupid=$grpId and cby=$cby");
        $total_answer = 0;
        while($row_get_question= mysqli_fetch_assoc($get_question)){
            $total_answer += $row_get_question['answerval'];
        }
        $average_value = ($total_answer/($totalRows_get_question*100))*100;
        $survey_data_lastweek[$grpId][$cby]['current'] = $average_value;
    }
    else if($survey_type=='role'){
        $title = 'Role';
        $count = array();
        record_set("get_question","select * from answers where roleid=$roleId and cby=$cby");
        $total_answer = 0;
        while($row_get_question= mysqli_fetch_assoc($get_question)){
            $total_answer += $row_get_question['answerval'];
        }
        $average_value = ($total_answer/($totalRows_get_question*100))*100;
        $survey_data_lastweek[$roleId][$cby]['current'] = $average_value;
    }
}
}

ksort($survey_data);
$current_data =array();
foreach($survey_data as $key => $value){
$current_data[$key]['count']  = count($value);
$current_data[$key]['id']  = $key;
$current_data[$key]['avg']  = array_sum($value)/count($value);
}

ksort($survey_data_lastweek);
$last_week_data =array();
foreach($survey_data_lastweek as $key => $value){
$last_week_data[$key]['count']  = count($value);
$last_week_data[$key]['avg']  = array_sum($value)/count($value);
}

$key_values = array_column($current_data, 'avg'); 
array_multisort($key_values, SORT_DESC, $current_data);

// send data with table
$arrayData = array();
$filename = 'view-league-'.date("Y-m-d-H:i:s", time()).".xls"; // File Name

header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Type: application/vnd.ms-excel");

$flag = false;
if(count($current_data)>0){
    foreach($current_data as $key =>$datasurvey){ 
        $total =  round($datasurvey['avg'], 2);
        $titleName='';
        if($survey_type=='location'){
            $titleName = getLocation()[$datasurvey['id']];
        }
        else if($survey_type=='group'){
            $titleName = getGroup()[$datasurvey['id']];
        }
        else if($survey_type=='department'){
            $title = 'Department';
            $titleName = getDepartment()[$datasurvey['id']];
        }
        else if($survey_type=='role'){
            $title = 'Role';
            $titleName = getRole()[$datasurvey['id']];
        }
        if(!empty($fdate) && !empty($sdate)){
            $startDate = date('d/m/Y', strtotime($_GET['fdate']));
            $endDate = date('d/m/Y', strtotime($_GET['sdate']));

            $arrayData[] = array(
                "DATE" => $startDate.' - '.$endDate,
                "SURVEY" => $surveyName,
                "LOCATION" => $titleName,
                "NO OF SURVEY" => $datasurvey['count'],
                "AVERAGE SCORE" => ($total>0) ? $total.'%' : '0'.'%',
            );
        }else{
            $arrayData[] = array(
                "SURVEY" => $surveyName,
                "LOCATION" => $titleName,
                "NO OF SURVEY" => $datasurvey['count'],
                "AVERAGE SCORE" => ($total>0) ? $total.'%' : '0'.'%',
            );
        }
       

    }
}

foreach($arrayData as $data){
	if (!$flag) {
		echo implode("\t", array_keys($data)) . "\r\n";
		$flag = true;
	}
	echo implode("\t", array_values($data)) . "\r\n";	
}