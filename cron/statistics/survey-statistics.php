<?php
require('../../function/function.php');
require('../../function/get_data_function.php');
include('../../permission.php');
require_once dirname(__DIR__, 2). '/vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf();

record_set("get_report","select * from schedule_report_new ORDER BY created_at DESC;
");
while($row_get_report= mysqli_fetch_assoc($get_report)){
    $current_date   = date('Y-m-d', time());
    $end_date       = date('Y-m-d', strtotime($row_get_report['end_date']));
    $next_schedule  = date('Y-m-d', strtotime($row_get_report['next_schedule_date']));
    $result_1   = check_differenceDate($current_date,$end_date,'lte');
    $result_2   = check_differenceDate($current_date,$next_schedule,'eq');
    
    if($result_1 && $result_2){
        $filter     = json_decode($row_get_report['filter'],1);
        $survey     = $filter['survey_hidden'];
        $data_type  = $filter['data_type_hidden'];

        // update next schedule date with interval
        $nextScheduledDate = $row_get_report['next_schedule_date'];
        $updateSchedule = date('Y-m-d H:i:s', strtotime(' + '.$row_get_report['intervals'].' hours',strtotime($nextScheduledDate)));   
            $data = array(
                "next_schedule_date" => $updateSchedule,
            );
        $updte=	dbRowUpdate("schedule_report_new",$data , "where id=".$row_get_report['id']);
        // fetch survey data
        $querys = 'SELECT * FROM answers where id!=0 ';
        $groupBy = '';
        if($data_type == 'location'){
            $query = " and surveyid =".$survey." and locationid in (select id from locations where cstatus=1)";  
            $groupBy = 'locationid';
        }
        else if($data_type=='group'){
            $query = " and surveyid =".$survey." and groupid in (select id from `groups` where cstatus=1)";  
            $groupBy = 'group';
        }
        else if($data_type=='department'){
            $query = " and surveyid =".$survey." and departmentid in (select id from departments where cstatus=1)";
            $groupBy = 'departmentid';
        }
        else {
            $survey_allow = get_allowed_survey($survey_type);
            $survey_allow_id = implode(',',array_keys($survey_allow));
            $filterdata = '';
            if($survey_allow_id){
                $filterdata = " and id IN($survey_allow_id)";
            }else{
                // if no survey is allowed
                $filterdata = " and id IN(0)";
            }
            $query = " and surveyid IN (select id from surveys where cstatus=1 $filterdata)";
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
                        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_question['questionid']);
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
                        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_question['questionid']);
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
                        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_question['questionid']);
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
                        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_question['questionid']);
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
        $counter = 0;
        $html = '<!DOCTYPE html>
            <html lang="en">
                <head>
                    <title>Test</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            -webkit-box-sizing: border-box;
                            box-sizing: border-box;
                            outline: none;
                            list-style: none;
                            word-wrap: break-word;
                            font-size: 14px;
                        }

                        
                        .col-md-4 {
                            display: inline-block;
                            margin-left: 1.5%;
                            margin-right: 1.5%;
                            margin-bottom: 1.5%;
                            width: 31.33%;
                            float:left;
                        }
                        .metter-outer.active {
                            padding: 5px;
                            border: 1px solid #000;
                            background: #eee;
                            height: 250px
                            display: inline-block;
                            padding-top: 10px;
                            padding-bottom: 10px;
                            text-align: center;
                        }
                        .circle-bg {
                                width: 258;
                                height: 125px;
                                background: #eee;
                                background-image: url("'.getHomeUrl().'upload_image/chart.png");
                                background-repeat: no-repeat;
                                background-position: center;
                                background-size: contain;
                            }
                        .circle-frame {
                                padding-bottom: 5px;
                            }
                        .metter-outer.active .meter-clock {
                            
                                background-position: center;
                                background-size: contain;
                                height: 250px;
                                width: 250px;
                                background-repeat: no-repeat;
                                
                                transform-origin: bottom;
                                margin-top: -100px;
                            }
                            
                        .row {
                                display: -ms-flexbox;
                                display: flex;
                                -ms-flex-wrap: wrap;
                            } 
                            .top-content h4 {
                                margin-bottom: 10px;
                            }

                            .top-content p {
                                font-weight: 600;
                                margin-bottom: 7px;
                            }

                            .top-content span {
                                margin-bottom: 15px;
                                display: block;
                                font-weight: 600;
                                font-size: 22px;
                            }  

                            .bottom-content h4 {
                                margin-top: 40px;
                                margin-bottom: 10px;
                            }
                            .header img {
                                width: 13%;
                                margin-bottom: 10px;
                                margin-top: 10px;
                            }

                            .header {
                                width: 100%;
                                text-align: center;
                            }

                            .header .title h3 {
                                font-size: 20px;
                                padding: 12px;
                                border: 1px solid #eee;
                                border-right: none;
                                border-left: none;
                            }

                            .title {
                                margin-bottom: 10px;
                            }
                            .bottom-content {
                                margin-top: -120px;
                            }
                    </style>
                </head>
                <body>
                    <div class="report-container">
                        <div class="row">
                            <div class="header">
                                <img src="'.getHomeUrl().'/hats-logo-survey50.png" alt="" height="60">
                                <div class="title">
                                    <h4>'.strtoupper($data_type .' Statistics').'</h4>
                                    <h4>'.strtoupper(getSurvey()[$survey]).'</h4>
                                </div>
                            </div>
                        </div>   
                        <div class="row">'; 
                        if(count($survey_data)>0){
                            foreach($survey_data as $key =>$datasurvey){ 
                                $total=  array_sum($datasurvey['data'])/count($datasurvey['data']);
                                $total =  round($total, 2);
                                $titleName='';
                                if($data_type=='location'){
                                    $titleId = '';
                                    $titleName = getLocation('all')[$key];
                                }
                                else if($data_type=='group'){
                                    $titleId = '';
                                    $titleName = getGroup('all')[$key];
                                }
                                else if($data_type=='department'){
                                    $titleId = '';
                                    $titleName = getDepartment('all')[$key];
                                }
                                else {
                                    $titleId ='Survey ID: '.$key;
                                    $titleName = getSurvey()[$key];
                                }
                                if($datasurvey['contact']){
                                    $contacted = $datasurvey['contact'];
                                }else {
                                    $contacted =0;
                                }
                                $i = round($total);
                                $degree = 182 - (ceil((180*$i)/100));
                                    if($counter <6){
                                        $html .=  '<div class="col-md-4">
                                                <div class="metter-outer active">
                                                        <div class="circle">
                                                            <div class="top-content">
                                                                <h5 style="height:30px;">'.$titleName.'</h5>
                                                                <span>'.$total.'%</span>
                                                            </div>                                  
                                    
                                                            <div class="circle-frame">
                                                                <div class="circle-bg"></div>
                                                                <div class="" style="">
                                                                    <img src="'.getHomeUrl().'upload_image/niddle/180_niddle/niddle_with_circle/Asset '.$degree.'.png" height="190" style="margin-top:-107px" class="meter-clock meter-clock-overall_1"/>
                                                                </div>
                                                            </div>
                                                            <div class="bottom-content">
                                                                <h4>Total Surveys : '.count($datasurvey['data']).'</h4>
                                                                <h4 style="margin-top:5px">Contact Requests : '.($contacted).'</h4>
                                                            </div>
                                                    </div>
                                                </div>
                                            </div>';
                                    }else{
                                        $html .=  '
                                        <div style=" margin-left: 1.5%; margin-right: 1.5%; margin-bottom: 1.5%; width: 31.33%; float:left; ">
                                            <div style=" height:90px;width: 31.33%;">
                                            </div>
                                            <div class="metter-outer active">
                                                    <div class="circle">
                                                        <div class="top-content">
                                                            <h5 style="height:30px;">'.$titleName.'</h5>
                                                            <span >'.$total.'% </span>
                                                        </div>                                  
                                
                                                        <div class="circle-frame">
                                                            <div class="circle-bg"></div>
                                                            <div class="" style="">
                                                                <img src="'.getHomeUrl().'upload_image/niddle/180_niddle/niddle_with_circle/Asset '.$degree.'.png" height="190" style="margin-top:-107px" class="meter-clock meter-clock-overall_1"/>
                                                            </div>
                                                        </div>
                                                        <div class="bottom-content">
                                                            <h4>Total Surveys : 20</h4>
                                                            <h4 style="margin-top:5px">Contact Requests : 20</h4>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>';
                                    }
                                $counter++;
                                if($counter % 6==0){
                                    $html .='<pagebreak>';
                                }
                            }
                        }
                    $html .= '</div>
                    </div>
                </body>
            </html>';
            // echo $html; die();
            $mpdf->WriteHTML($html);
            $pdf = $mpdf->Output('', 'S');
            
            $body = 'Hello You Have Schedule the report';
            $filename = 'survey-statistics'.$row_get_report['id'].'.pdf';
            //send pdf attachment
            $userDetails = get_user_datails($row_get_report['cby']);
            $email       = $userDetails['email'];
            $user_name   = $userDetails['name'];
            sendEmailPdf($pdf,$filename,$email,$user_name,'Survey Statistics',$body);
    }

}
//$mpdf->Output($filename,'F');
//$mpdf->Output();
?>