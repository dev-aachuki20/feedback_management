<?php
    require('./function/function.php');
    require('./function/get_data_function.php');
    include('./permission.php');
    require_once __DIR__ . '/vendor/autoload.php';
    $filename = './file.pdf';
    $mpdf = new \Mpdf\Mpdf();
    $querys = 'SELECT * FROM answers where id!=0 ';
    $groupBy = '';
    $data_type = 'survey';
    $fdata = $_POST['fdate'];
    $sdate = $_POST['sdate'];
   // $survey_type = $_POST['survey_type'];
    $survey_type = 'survey';
    if($data_type == 'location'){
        $query = " and surveyid =".$_POST['survey']." and locationid in (select id from locations where cstatus=1)";  
        $groupBy = 'locationid';
    }
    else if($data_type=='group'){
        $query = " and surveyid =".$_POST['survey']." and groupid in (select id from `groups` where cstatus=1)";  
        $groupBy = 'group';
    }
    else if($data_type=='department'){
        $query = " and surveyid =".$_POST['survey']." and departmentid in (select id from departments where cstatus=1)";
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

    if(!empty($fdate) and !empty($fdate)){
        $query .= " and  cdate between '".date('Y-m-d', strtotime($fdate))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($sdate)))."'";
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
                    $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and  id =".$row_get_question['questionid']);
                    if($result_question){
                        if(!in_array($result_question['answer_type'],array(2,3,5))){
                        $i++;
                            $total_answer += $row_get_question['answerval'];
                        }
                    }
                    if($row_get_question['answerid'] == -2 && $row_get_question['answerval'] == 100){
                        //$to_bo_contacted += 1;
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
                        //$to_bo_contacted += 1;
                        $survey_data[$surveyid]['contact'] += 1;
                    }
                }
                //$average_value = ($total_answer/($i*100))*100;
                if($total_answer==0 and $total_result_val==0){
                    $average_value=100;
                }
                //echo $total_answer.' - '.
                $average_value = ($total_answer/($i*100))*100;
                if(is_nan($average_value)){
                    $average_value = 100;
                }
                $survey_data[$surveyid]['data'][$cby] = $average_value;
            }
        }
    }
    ksort($survey_data);
    $html = '<head>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    -webkit-box-sizing: border-box;
                    box-sizing: border-box;
                    outline: none;
                    list-style: none;
                    word-wrap: break-word;
                    font-size: 16px;
                }
    
                .report-container {
                    width: 100%;
                    padding-right: 15px;
                    padding-left: 15px;
                    margin: 0 auto;
                    text-align: center;
                }
                
                .metter-outer.active {
                    width:30%;
                    height:auto;
                    float:left;
                    padding: 15px;
                    border: 1px solid #000;
                    background: #eee;
                    padding-top: 50px;
                    padding-bottom: 50px;
                    text-align: center;
                    margin-bottom:30px;
                }
                .circle-bg {
                        width: 247px;
                        height: 125px;
                        background: #eee;
                        position:absolute;
                        background-image: url("./upload_image/chart.png");
                        background-repeat: no-repeat;
                        background-position: center;
                        background-size: contain;
                    }
                .meter-clock {
                    background-image: url("./upload_image/needle.png");
                    background-position: center;
                    background-size: contain;
                    height: 10%;
                    width: 20%;
                    top: 0;
                    margin: 0 auto;
                    margin-top:-100px;
                    position: absolute;
                    background-repeat: no-repeat;
                    transform: rotate(50deg);
                    transform-origin: bottom;
                    }
                    .circle-frame {
                        position: fixed;
                        overflow: hidden;
                        text-align: center;
                        padding-bottom: 5px;
                        background: #eeeeee;
                    }
                .row {
                        width:100%;
                    } 

                    .top-content h4 {
                        margin-bottom: 30px;
                    }
    
                    .top-content p {
                        font-weight: 600;
                        margin-bottom: 7px;
                    }
    
                    .top-content span {
                        margin-bottom: 15px;
                        display: block;
                        font-weight: 600;
                        font-size: 25px;
                    }  
    
                    .bottom-content h4 {
                        margin-bottom: 15px;
                    }
                    .header img {
                        width: 13%;
                        margin-bottom: 30px;
                        margin-top: 30px;
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
                        margin-bottom: 50px;
                    }
            </style>
        </head>
        <body>
            <div class="report-container">    
                <div class="row">
                    <div class="header">
                        <div class="title">
                            <h3> United States Minor Outlying Islands United States</h3>
                            <h3> United States Minor Outlying Islands United States</h3>
                        </div>
                    </div>
                </div>   
                <div class="row">     
                    <div class="col-md-4">
                        <div class="metter-outer active">
                            <div class="circle">
                                <div class="top-content">
                                    <h4>United States Minor Outlying Islands United States</h4>
                                    <p>Survey ID : 20</p>
                                    <span>100%</span>
                                </div>                                  

                                <div class="circle-frame">
                                    <div class="circle-bg"></div>
                                    <div class="meter-clock meter-clock-overall_1">
                                    </div>
                                </div>
                                <div class="bottom-content">
                                <h4>Total Surveys : 20</h4>
                                <h4>Contact Requests : 20</h4>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metter-outer active">
                            <div class="circle">
                                <div class="top-content">
                                    <h4>United States Minor Outlying Islands United States</h4>
                                    <p>Survey ID : 20</p>
                                    <span>100%</span>
                                </div>                                  

                                <div class="circle-frame">
                                    <div class="circle-bg"></div>
                                    <div class="meter-clock meter-clock-overall_1">
                                    </div>
                                </div>
                                <div class="bottom-content">
                                <h4>Total Surveys : 20</h4>
                                <h4>Contact Requests : 20</h4>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metter-outer active">
                            <div class="circle">
                                <div class="top-content">
                                    <h4>United States Minor Outlying Islands United States</h4>
                                    <p>Survey ID : 20</p>
                                    <span>100%</span>
                                </div>                                  

                                <div class="circle-frame">
                                    <div class="circle-bg"></div>
                                    <div class="meter-clock meter-clock-overall_1">
                                    </div>
                                </div>
                                <div class="bottom-content">
                                <h4>Total Surveys : 20</h4>
                                <h4>Contact Requests : 20</h4>
                            </div>
                            </div>
                        </div>
                    </div>

                </div>
           </div>
        </body>';
    $mpdf->WriteHTML($html);
    //$mpdf->Output($filename,'F');

    $mpdf->Output();
?>