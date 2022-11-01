<?php
include('../function/function.php');
include('../function/get_data_function.php');
$data =array();
$querys = 'SELECT * FROM answers where id!=0 ';
$groupBy = '';
if($_POST['data_type']=='location'){
    $query = " and surveyid =".$_POST['survey']." and locationid in (select id from locations where cstatus=1)";  
    $groupBy = 'locationid';
}
else if($_POST['data_type']=='group'){
    $query = " and surveyid =".$_POST['survey']." and groupid in (select id from groups where cstatus=1)";  
    $groupBy = 'group';
}
else if($_POST['data_type']=='department'){
    $query = " and surveyid =".$_POST['survey']." and departmentid in (select id from departments where cstatus=1)";
    $groupBy = 'departmentid';
}
else {
    $survey_allow = get_allowed_survey($_POST['survey_type']);
    $survey_allow_id = implode(',',array_keys($survey_allow));
    $filterdata = '';
    if($survey_allow_id){
        $filterdata = " and id IN($survey_allow_id)";
    }
    $query = " and surveyid IN (select id from surveys where cstatus=1 $filterdata)";
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
            }
            $average_value = ($total_answer/($i*100))*100;
            if($total_answer==0 and $total_result_val==0){
                $average_value=100;
            }
            $survey_data[$locId][$cby] = $average_value;
        }
        else if($_POST['data_type']=='department'){
            $count = array();
            record_set("get_question","select * from answers where departmentid=$depId and cby=$cby");
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
            }
            $average_value = ($total_answer/($i*100))*100;
            if($total_answer==0 and $total_result_val==0){
                $average_value=100;
            }
            $survey_data[$depId][$cby] = $average_value;
        }
        else if($_POST['data_type']=='group'){
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
            }
            $average_value = ($total_answer/($i*100))*100;
            if($total_answer==0 and $total_result_val==0){
                $average_value=100;
            }
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
//export csv in survey static
if(isset($_GET['export']) and $_GET['export']=='csv'){
    $survey_name = getSurvey()[$_POST['survey']];
    export_csv_file($survey_data,$_GET['data_type'],$survey_name); die();
}
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
            $titleId ='Survey ID: '.$key;
            $titleName ='Survey Name: '.getSurvey()[$key];
        }
      
       $html.= '<div class="col-md-3"> 
       <div class="graph-body">  
            <p style="font-size: 14px;font-weight: 700;text-align:center;height: 40px;">'.$titleName.'</p>  
            <p style="font-size: 14px;font-weight: 700;text-align:center">'.$titleId.'</p>     
            <div id="canvas-holder">
                <span class="g-persent"><strong>'.$total.' %</strong></span>
                <canvas id="chart_'.$i.'"></canvas>
                <div class="row" style="text-align:center;margin-top: -24px;">
                    <div class="col-md-4"><span class="poor" style="font-size: 12px;margin-left: 10px;"><strong>POOR</strong></span></div>
                    <div class="col-md-4"></div>
                    <div class="col-md-4"><span class="poor" style="font-size: 12px;margin-right: 10px;"><strong>GOOD</strong></span></div>
                </div>
                <div class="row" style="text-align:center;">
                    <div class="col-md-12"><span class="total-count"><strong>TOTAL:'.count($datasurvey).'</strong></span></div>
                </div>
                </div>
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
