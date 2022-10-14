<?php
include('../function/function.php');
include('../function/get_data_function.php');
$data =array();
$querys = 'SELECT * FROM answers where id!=0 ';
$groupBy = '';
// if(!empty($_POST['survey'])){
//     $query = " and surveyid =".$_POST['survey'];
//     $groupBy = 'surveyid';
// }
if($_POST['data_type']=='location'){
    $query = " and surveyid =".$_POST['survey']." and locationid in (select id from locations)";  
    $groupBy = 'locationid';
}
else if($_POST['data_type']=='group'){
    $query = " and surveyid =".$_POST['survey']." and groupid in (select id from groups)";  
    $groupBy = 'group';
}
else if($_POST['data_type']=='department'){
    $query = " and surveyid =".$_POST['survey']." and departmentid in (select id from departments)";
    $groupBy = 'departmentid';
}
else {
    $survey_allow = get_allowed_data('surveys',$_SESSION['user_id'],$_POST['survey_type']);
    $survey_allow_id = implode(',',array_keys($survey_allow));
    $fdata = '';
    if($survey_allow_id){
        $fdata = " and id IN($survey_allow_id)";
    }
    $query = " and surveyid IN (select id from surveys where cstatus=1 $fdata)";
    $groupBy = 'surveyid';
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
    while($row_get_entry = mysqli_fetch_assoc($get_entry)){
        $locId      = $row_get_entry['locationid'];
        $depId      = $row_get_entry['departmentid'];
        $grpId      = $row_get_entry['groupid'];
        $surveyid   = $row_get_entry['surveyid'];
        $cby        = $row_get_entry['cby'];
        
        if($_POST['data_type']=='location'){
            $count = array();
            record_set("get_question","select * from answers where locationid=$locId and cby=$cby");
            $total_answer = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $total_answer += $row_get_question['answerval'];
            }
            $average_value = ($total_answer/($totalRows_get_question*100))*100;
            $survey_data[$locId][$cby] = $average_value;
        }
        else if($_POST['data_type']=='department'){
            $count = array();
            record_set("get_question","select * from answers where departmentid=$depId and cby=$cby");
            $total_answer = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $total_answer += $row_get_question['answerval'];
            }
            $average_value = ($total_answer/($totalRows_get_question*100))*100;
            $survey_data[$depId][$cby] = $average_value;
        }
        else if($_POST['data_type']=='group'){
            $count = array();
            record_set("get_question","select * from answers where groupid=$grpId and cby=$cby");
            $total_answer = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $total_answer += $row_get_question['answerval'];
            }
            $average_value = ($total_answer/($totalRows_get_question*100))*100;
            $survey_data[$grpId][$cby] = $average_value;
        }
        else {
            $count = array();
            record_set("get_question","select * from answers where surveyid=$surveyid and cby=$cby");
            $total_answer = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $total_answer += $row_get_question['answerval'];
            }
            $average_value = ($total_answer/($totalRows_get_question*100))*100;
            $survey_data[$surveyid][$cby] = $average_value;
        }
    }
}

$html ='';
$i=1; 
ksort($survey_data);
if(count($survey_data)>0){
    foreach($survey_data as $key =>$datasurvey){ 
        $total=  array_sum($datasurvey)/count($datasurvey);
        $total =  round($total, 2);
        $titleName='';
        if($_POST['data_type']=='location'){
            $titleId = '';
            $titleName = getLocation('all')[$key];
        }
        else if($_POST['data_type']=='group'){
            $titleId = '';
            $titleName = getGroup('all')[$key];
        }
        else if($_POST['data_type']=='department'){
            $titleId = '';
            $titleName = getDepartment('all')[$key];
        }
        else {
            $titleId ='Survey Id: '.$key;
            $titleName ='Survey Name: '.getSurvey()[$key];
        }
      
       $html.= '<div class="col-md-3 graph-body">   
            <p style="font-size: 12px;font-weight: 700;text-align:center">'.$titleId.'</p>  
            <p style="font-size: 12px;font-weight: 700;text-align:center">'.$titleName.'</p>     
            <div id="canvas-holder" style="width:200px">
                <span style="margin-left:80px;"><strong>'.$total.' %</strong></span>
                <canvas id="chart_'.$i.'"></canvas>
                <span class="poor" style="font-size: 12px;"><strong>POOR</strong></span>
                <span class="poor" style="margin-left: 36px;margin-right: 10px;font-size: 12px;"><strong>TOTAL:'.count($datasurvey).'</strong></span>
                <span class="poor" style="font-size: 12px;"><strong>EXCELLENT</strong></span>
            </div>
        </div>';
        $i++; 
    }
}else {
    $html .='<p style="margin-left: 21px !important;">No result found</p>';
}

$data['html']= $html;
$data['result']= $survey_data;

echo json_encode($data) ; die();
