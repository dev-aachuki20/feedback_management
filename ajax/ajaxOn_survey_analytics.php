<?php
include('../function/function.php');
include('../function/get_data_function.php');
$data =array();
$surveyid   = $_POST['survey'];
$querys = 'SELECT * FROM answers where id!=0 and surveyid = '.$surveyid ;
$groupBy = '';
if($_POST['survey_type']=='location'){
    $query = " and locationid in (select id from locations where cstatus=1)";  
    $groupBy = 'locationid';
}
else if($_POST['survey_type']=='group'){
    $query = " and groupid in (select id from groups where cstatus=1)";  
    $groupBy = 'group';
}
else if($_POST['survey_type']=='department'){
    $query = " and departmentid in (select id from departments where cstatus=1)";
    $groupBy = 'departmentid';
}

if(!empty($_POST['fdate']) and !empty($_POST['fdate'])){
    $query .= " and  cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
}
record_set("total_survey","SELECT COUNT(DISTINCT(cby)) as totalCount FROM answers WHERE id!=0  $query");
$row_total_survey = mysqli_fetch_assoc($total_survey);
$total_survey = $row_total_survey['totalCount'];
record_set("get_entry",$querys.$query." GROUP by cby");
if($totalRows_get_entry){
    $survey_data = array();
    $survey_overall =array();
    $overallCount = 0;
    while($row_get_entry = mysqli_fetch_assoc($get_entry)){
        $locId      = $row_get_entry['locationid'];
        $depId      = $row_get_entry['departmentid'];
        $grpId      = $row_get_entry['groupid'];
        $cby        = $row_get_entry['cby'];
        
        if($_POST['survey_type']=='location'){
            $count = array();
            record_set("get_question","select * from answers where locationid=$locId and cby=$cby");
            $total_answer = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $total_answer += $row_get_question['answerval'];
            }
            $average_value = ($total_answer/($totalRows_get_question*100))*100;
            $survey_data[$locId][$cby] = $average_value;
        }
        else if($_POST['survey_type']=='department'){
            $count = array();
            record_set("get_question","select * from answers where departmentid=$depId and cby=$cby");
            $total_answer = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $total_answer += $row_get_question['answerval'];
            }
            $average_value = ($total_answer/($totalRows_get_question*100))*100;
            $survey_data[$depId][$cby] = $average_value;
        }
        else if($_POST['survey_type']=='group'){
            $count = array();
            record_set("get_question","select * from answers where groupid=$grpId and cby=$cby");
            $total_answer = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $total_answer += $row_get_question['answerval'];
            }
            $average_value = ($total_answer/($totalRows_get_question*100))*100;
            $survey_data[$grpId][$cby] = $average_value;
        }
        
        record_set("get_question_overall","select * from answers where surveyid=$surveyid and cby=$cby");
        $total_answer_overall = 0;
        while($row_get_question_overall= mysqli_fetch_assoc($get_question_overall)){
            $total_answer_overall += $row_get_question_overall['answerval'];
        }
        $average_value_overall = ($total_answer_overall/($totalRows_get_question_overall*100))*100;
        $overallCount++;
        $survey_overall[$overallCount] += $average_value_overall;
    }
}
$avgScore = round(array_sum($survey_overall)/count($survey_overall),2);

if(is_nan($avgScore)){
    $avgScore = 0 ;
}
$overall= '';
$overall = '<div class="col-md-6">
<div class="col-6"><strong>Total Survey Response<br>(Overall)</strong></div>
<div class="col-6"><strong>'.count($survey_overall).'</strong></div>
</div>
<div class="col-md-6">
<div class="col-6"><strong>Average Survey Score<br>(Overall)</strong></div>
<div class="col-6"><strong>'.$avgScore.' %</strong></div>
</div>';

$html ='';
$i=1; 
ksort($survey_data);
if(count($survey_data)>0){
    $html ='<table class="table">
    <thead class="thead-dark">
    <tr>
        <th scope="col" style="text-align: left;">Location Name</th>
        <th scope="col" style="text-align: left;">Total Surveys</th>
        <th scope="col" style="text-align: left;">Average Score</th>
    </tr>
    </thead>
    <tbody>';
    $graph_data = array();
   $key_values = array_column($current_data, 'avg'); 
array_multisort($key_values, SORT_DESC, $current_data);
    foreach($survey_data as $key =>$datasurvey){ 
        $total=  array_sum($datasurvey)/count($datasurvey);
        $total =  round($total, 2);
        if($_POST['survey_type']=='location'){
            $titleName = getLocation()[$key];
        }
        else if($_POST['survey_type']=='group'){
            $titleName = getGroup()[$key];
        }
        else if($_POST['survey_type']=='department'){
            $titleName = getDepartment()[$key];
        }
        $graph_data[$titleName] = $total;
       $html.= '
            <tr>
                <td style="text-align: left;">'.$titleName.'</td>
                <td style="text-align: left;">'.count($datasurvey).'</td>
                <td style="text-align: left;">'.$total.' %</td>
            </tr>';
        $i++; 
    }

    $html .='</tbody>
    </table>';
}else {
    $html = 0;
}
$data['html']= $html;
$data['result']= $graph_data;
$data['overall'] = $overall;

echo json_encode($data) ; die();
