<?php
include('../function/function.php');
include('../function/get_data_function.php');
$data = array();
$allSurvey = getSurvey();

$querys = 'SELECT * FROM answers where id!=0 ';
$groupBy = '';
if ($_POST['data_type'] == 'location') {
    $query = " and surveyid =" . $_POST['survey'] . " and locationid in (select id from locations where cstatus=1)";
    $groupBy = 'locationid';
} else if ($_POST['data_type'] == 'group') {
    $query = " and surveyid =" . $_POST['survey'] . " and groupid in (select id from `groups` where cstatus=1)";
    $groupBy = 'group';
} else if ($_POST['data_type'] == 'department') {
    $query = " and surveyid =" . $_POST['survey'] . " and departmentid in (select id from departments where cstatus=1)";
    $groupBy = 'departmentid';
} else if ($_POST['data_type'] == 'role') {
    $query = " and surveyid =" . $_POST['survey'] . " and roleid in (select id from departments where cstatus=1)";
    $groupBy = 'roleid';
} else {
    $survey_allow = get_allowed_survey($_POST['survey_type']);
    $survey_allow_id = implode(',', array_keys($survey_allow));
    $filterdata = '';
    if ($survey_allow_id) {
        $filterdata = " and id IN($survey_allow_id)";
    } else {
        // if no survey is allowed
        $filterdata = " and id IN(0)";
    }
    $query = " and surveyid IN (select id from surveys where cstatus=1 $filterdata)";
    $groupBy = 'surveyid';
}

if (!empty($_POST['sdate']) and !empty($_POST['edate'])) {
    $query .= " and  cdate between '" . date('Y-m-d', strtotime($_POST['sdate'])) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($_POST['edate']))) . "'";
}


record_set("total_survey", "SELECT COUNT(DISTINCT(cby)) as totalCount FROM answers WHERE id!=0  $query");
$row_total_survey = mysqli_fetch_assoc($total_survey);
$total_survey = $row_total_survey['totalCount'];
record_set("get_entry", $querys . $query . " GROUP by cby");
if ($totalRows_get_entry) {
    $survey_data = array();
    $to_bo_contacted = 0;
    while ($row_get_entry = mysqli_fetch_assoc($get_entry)) {
        $locId      = $row_get_entry['locationid'];
        $depId      = $row_get_entry['departmentid'];
        $grpId      = $row_get_entry['groupid'];
        $roleId      = $row_get_entry['roleid'];
        $surveyid   = $row_get_entry['surveyid'];
        $cby        = $row_get_entry['cby'];

        if ($_POST['data_type'] == 'location') {
            $count = array();
            record_set("get_question", "select * from answers where locationid=$locId and cby=$cby");
            $total_answer = 0;
            $i = 0;
            $total_result_val = 0;
            while ($row_get_question = mysqli_fetch_assoc($get_question)) {
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =" . $row_get_question['questionid']);
                if ($result_question) {
                    if (!in_array($result_question['answer_type'], array(2, 3, 5))) {
                        $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
                if ($row_get_question['answerid'] == -2 && $row_get_question['answerval'] == 100) {
                    //$to_bo_contacted += 1;
                    $survey_data[$locId]['contact'] += 1;
                }
            }
            $average_value = ($total_answer / ($i * 100)) * 100;
            if ($total_answer == 0 and $total_result_val == 0) {
                $average_value = 100;
            }
            $survey_data[$locId]['data'][$cby] = $average_value;
        } else if ($_POST['data_type'] == 'department') {
            $count = array();
            record_set("get_question", "select * from answers where departmentid=$depId and cby=$cby");
            $total_answer = 0;
            $i = 0;
            $total_result_val = 0;
            $to_bo_contacted     = 0;
            while ($row_get_question = mysqli_fetch_assoc($get_question)) {
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =" . $row_get_question['questionid']);
                if ($result_question) {
                    if (!in_array($result_question['answer_type'], array(2, 3, 5))) {
                        $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
                if ($row_get_question['answerid'] == -2 && $row_get_question['answerval'] == 100) {
                    $survey_data[$depId]['contact'] += 1;
                }
            }
            $average_value = ($total_answer / ($i * 100)) * 100;
            if ($total_answer == 0 and $total_result_val == 0) {
                $average_value = 100;
            }
            $survey_data[$depId]['data'][$cby] = $average_value;
        } else if ($_POST['data_type'] == 'group') {
            $count = array();
            record_set("get_question", "select * from answers where groupid=$grpId and cby=$cby");
            $total_answer = 0;
            $i = 0;
            $total_result_val = 0;
            while ($row_get_question = mysqli_fetch_assoc($get_question)) {
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =" . $row_get_question['questionid']);
                if ($result_question) {
                    if (!in_array($result_question['answer_type'], array(2, 3, 5))) {
                        $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
                if ($row_get_question['answerid'] == -2 && $row_get_question['answerval'] == 100) {
                    //$to_bo_contacted += 1;
                    $survey_data[$grpId]['contact'] += 1;
                }
            }
            $average_value = ($total_answer / ($i * 100)) * 100;
            if ($total_answer == 0 and $total_result_val == 0) {
                $average_value = 100;
            }
            $survey_data[$grpId]['data'][$cby] = $average_value;
        } else if ($_POST['data_type'] == 'role') {
            $count = array();
            record_set("get_question", "select * from answers where roleid=$roleId and cby=$cby");
            $total_answer = 0;
            $i = 0;
            $total_result_val = 0;
            while ($row_get_question = mysqli_fetch_assoc($get_question)) {
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =" . $row_get_question['questionid']);
                if ($result_question) {
                    if (!in_array($result_question['answer_type'], array(2, 3, 5))) {
                        $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
                if ($row_get_question['answerid'] == -2 && $row_get_question['answerval'] == 100) {
                    //$to_bo_contacted += 1;
                    $survey_data[$roleId]['contact'] += 1;
                }
            }
            $average_value = ($total_answer / ($i * 100)) * 100;
            if ($total_answer == 0 and $total_result_val == 0) {
                $average_value = 100;
            }
            $survey_data[$roleId]['data'][$cby] = $average_value;
        } else {
            $count = array();
            record_set("get_question", "select * from answers where surveyid=$surveyid and cby=$cby");

            $total_answer = 0;
            $i = 0;
            $total_result_val = 0;
            while ($row_get_question = mysqli_fetch_assoc($get_question)) {
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =" . $row_get_question['questionid']);
                if ($result_question) {
                    if (!in_array($result_question['answer_type'], array(2, 3, 5))) {
                        $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
                if ($row_get_question['answerid'] == -2 && $row_get_question['answerval'] == 100) {
                    //$to_bo_contacted += 1;
                    $survey_data[$surveyid]['contact'] += 1;
                }
            }
            //$average_value = ($total_answer/($i*100))*100;
            if ($total_answer == 0 and $total_result_val == 0) {
                $average_value = 100;
            }
            //echo $total_answer.' - '.
            $average_value = ($total_answer / ($i * 100)) * 100;
            if (is_nan($average_value)) {
                $average_value = 100;
            }
            $survey_data[$surveyid]['data'][$cby] = $average_value;
        }
    }
}
$html = '';
$i = 1;
$j = 6;


if ($_POST['data_type'] == 'survey' || $_POST['data_type'] == '') {
    $survey_id = array_keys($survey_data);
    $survey_id_1 = array_keys($allSurvey);
    $unique_array = array_diff($survey_id_1, $survey_id);
    $remainingSurvey = [];
    foreach ($allSurvey as $key => $value) {
        if (!array_key_exists($key, $survey_data)) {
            $survey_data[$key]['data'][0] = 'Not-Found';
            $survey_data[$key]['contact'] = '';
        }
    }
}
ksort($survey_data);

$selectedSurveyId = $_POST['survey'] ?? '';
$data_type = $_POST['data_type'] ?? '';

$html = '';
$counter = 1;
$j = 6;
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
            .metter-outer.active {
                padding: 15 20px;
                border: 1px solid #c8bfbf;
                background: #ecf0f5;
                height: 250px
                display: inline-block;
                text-align: center;
            }
            .circle-bg {
                    width: 258;
                    height: 110px;
                    background: #eee;
                    background-image: url("' . getHomeUrl() . 'upload_image/chart.png");
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
                    margin-left: -15px;
                    margin-right: -15px;
                } 
                .col-md-4{
                    width: 29.1%;
                    float: left;
                    padding-left: 15px;
                    padding-right: 15px;
                    padding-top: 15px;
                    padding-bottom: 15px;
                }
                .top-content h4 {
                    margin-bottom: 10px;
                }

                .top-content p {
                    font-weight: 600;
                    margin-bottom: 7px;
                }

                .top-content span {
                    margin-bottom: 0px;
                    display: block;
                    font-weight: bold;
                    font-size: 18px;
                }  

                .bottom-content h4 {
                    margin-top: 40px;
                    margin-bottom: 10px;
                    font-size: 14px;
                }
                .header img {
                    width: 150px;
                    height: 70px;
                    margin-bottom: 10px;
                    margin-top: 0px;
                }

                .header {
                    width: 100%;
                    text-align: center;
                    padding-left: 15px;
                    padding-right: 15px;
                }

                .header .title{
                    margin-top: 30px;
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
                    margin-top: -110px;
                }
        </style>
    </head>
    <body style="padding: 0;margin: 0;">
        <div class="report-container" style="font-family: Source Sans Pro,Helvetica Neue,Helvetica,Arial,sans-serif">
            <div class="row">
                <div class="header">
                    <div>
                        <img src="' . getHomeUrl() . MAIN_LOGO_2 . '" alt="" style="width: 200px;margin-top: -22px;">
                    </div>
                    <div class="title">
                        <h4 style="border-top: 1px solid #d2cfcf;border-bottom: 1px solid #c8bfbf;padding: 6px 0;font-size: 17px;">' . strtoupper($data_type . ' Statistics') . '</h4>
                        <h4>' . strtoupper(getSurvey()[$selectedSurveyId]) . '</h4>
                    </div>
                </div>
            </div>   
            <div class="row">';


if (count($survey_data) > 0) {
    foreach ($survey_data as $key => $datasurvey) {
        $total =  array_sum($datasurvey['data']) / count($datasurvey['data']);
        $total =  round($total, 2);
        $titleName = '';
        if ($data_type == 'location') {
            $titleId = '';
            $titleName = getLocation('all')[$key];
        } else if ($data_type == 'group') {
            $titleId = '';
            $titleName = getGroup('all')[$key];
        } else if ($data_type == 'department') {
            $titleId = '';
            $titleName = getDepartment('all')[$key];
        } else {
            $titleId = 'Survey ID: ' . $key;
            $titleName = getSurvey()[$key];
        }
        if ($datasurvey['contact']) {
            $contacted = $datasurvey['contact'];
        } else {
            $contacted = 0;
        }
        $i = round($total);
        $degree = 182 - (ceil((180 * $i) / 100));
        if ($counter < 6) {
            $html .=  '<div class="col-md-4">
                                        <div class="metter-outer active">
                                                <div class="circle">
                                                    <div class="top-content">
                                                        <h5 style="height:50px;margin-top: 0;">' . $titleName  . '</h5>
                                                        <div style="font-size: 13px;padding-bottom: 4px;"><strong>' . $titleId  . '</strong></div>
                                                        <span style="font-size: 16px;">' . $total . '%</span>
                                                    </div>                                  
                            
                                                    <div class="circle-frame">
                                                        <div class="circle-bg"></div>
                                                        <div class="" style="">
                                                            <img src="' . getHomeUrl() . 'upload_image/niddle/180_niddle/niddle_with_circle/Asset ' . $degree . '.png" height="190" style="margin-top:-96px" class="meter-clock meter-clock-overall_1"/>
                                                        </div>
                                                    </div>
                                                    <div class="bottom-content">
                                                        <h4 style="font-size: 12px;text-transform: uppercase;">Total Surveys : ' . count($datasurvey['data']) . '</h4>
                                                        <h4 style="margin-top:8px;margin-bottom: 0;font-size: 12px;text-transform: uppercase;">Contact Requests : ' . ($contacted) . '</h4>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>';
        } else {
            $html .=  '
                                <div class="col-md-4">
                                    <div class="metter-outer active">
                                            <div class="circle">
                                                <div class="top-content">
                                                    <h5 style="height:50px;margin-top: 0;">' . $titleName . '</h5>
                                                    <div style="font-size: 13px;padding-bottom: 4px;"><strong>' . $titleId  . '</strong></div>
                                                    <span style="font-size: 16px;">' . $total . '% </span>
                                                </div>                                  
                        
                                                <div class="circle-frame">
                                                    <div class="circle-bg"></div>
                                                    <div class="" style="">
                                                        <img src="' . getHomeUrl() . 'upload_image/niddle/180_niddle/niddle_with_circle/Asset ' . $degree . '.png" height="190" style="margin-top:-96px" class="meter-clock meter-clock-overall_1"/>
                                                    </div>
                                                </div>
                                                <div class="bottom-content">
                                                    <h4 style="font-size: 12px;text-transform: uppercase;">Total Surveys : 20</h4>
                                                    <h4 style="margin-top:8px;margin-bottom: 0;font-size: 12px;text-transform: uppercase;">Contact Requests : 20</h4>
                                                </div>
                                        </div>
                                    </div>
                                </div>';
        }

        if ($counter == 6 && count($survey_data) > 6) {
            $j = $j + 9;
            $html .= '<pagebreak/>';
        }

        if ($j > 14 && $counter == $j && $counter < count($survey_data)) {
            $j = $j + 9;
            $html .= '<pagebreak>';
        }

        $counter++;
    }
}
$html .= '</div>
        </div>
    </body>
    </html>';

$dir = 'document/survey-report-' . $row_get_report['id'] . '.pdf';
$footer = '<div style="text-align: center;"> ' . POWERED_BY . '
<center><img  src="' . BASE_URL . FOOTER_LOGO . '" alt="" width="150"/></center>
</div>';
// create pdf

$fileName = 'Survey Statics-'.date('Y-m-d-H-i-s').'.pdf';
if(isset($selectedSurveyId) && $selectedSurveyId > 0){ 
$fileName = strtoupper(getSurvey()[$selectedSurveyId]).'-'.date('Y-m-d-H-i-s').'.pdf';
}

create_mpdf($html,$fileName,'D');
?>