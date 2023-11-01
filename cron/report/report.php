<?php
require dirname(__DIR__, 2) . '/function/function.php';
require dirname(__DIR__, 2) . '/function/get_data_function.php';
include dirname(__DIR__, 2) . '/permission.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

record_set("get_scheduled_report", "select srt.* from scheduled_report_templates as srt INNER JOIN report_templates as rt ON srt.temp_id = rt.id where rt.report_type=1 ORDER BY srt.id DESC");


while ($row_get_report = mysqli_fetch_assoc($get_scheduled_report)) {
    $mpdf = new \Mpdf\Mpdf();

    $current_date   = date('Y-m-d', time());
    $end_date       = date('Y-m-d', strtotime($row_get_report['end_date']));
    $next_schedule  = date('Y-m-d', strtotime($row_get_report['next_date']));
    $result_1   = check_differenceDate($current_date, $end_date, 'lte');
    $result_2   = check_differenceDate($current_date, $next_schedule, 'lte');

    if ($result_1 && $result_2 && $row_get_report['send_to'] != null) {

        $filter = json_decode($row_get_report['filter'], 1);
        $data_type = $filter['field'];
        $survey_id   = $filter['survey_id'];
        $field_value = implode(',', $filter['field_value']);
        if (is_array($survey_id)) {
            $survey_id = implode(',', $survey_id);
        }
        // fetch survey data
        $querys = 'SELECT * FROM answers where id!=0 ';
        $groupBy = '';
        if ($data_type == 'location') {
            $query = " and surveyid =" . $survey_id . " and locationid in ($field_value)";
            $groupBy = 'locationid';
        } else if ($data_type == 'group') {
            $query = " and surveyid =" . $survey_id . " and groupid in ($field_value)";
            $groupBy = 'group';
        } else if ($data_type == 'department') {
            $query = " and surveyid =" . $survey_id . " and departmentid in ($field_value)";
            $groupBy = 'departmentid';
        } else {
            $query = " and surveyid IN (select id from surveys where id IN($survey_id ))";
            $groupBy = 'surveyid';
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
                $surveyid   = $row_get_entry['surveyid'];
                $cby        = $row_get_entry['cby'];

                if ($data_type == 'location') {
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
                } else if ($data_type == 'department') {
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
                } else if ($data_type == 'group') {
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
                            $survey_data[$grpId]['contact'] += 1;
                        }
                    }
                    $average_value = ($total_answer / ($i * 100)) * 100;
                    if ($total_answer == 0 and $total_result_val == 0) {
                        $average_value = 100;
                    }
                    $survey_data[$grpId]['data'][$cby] = $average_value;
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
                            $survey_data[$surveyid]['contact'] += 1;
                        }
                    }
                    if ($total_answer == 0 and $total_result_val == 0) {
                        $average_value = 100;
                    }
                    $average_value = ($total_answer / ($i * 100)) * 100;
                    if (is_nan($average_value)) {
                        $average_value = 100;
                    }
                    $survey_data[$surveyid]['data'][$cby] = $average_value;
                }
            }
        }


        ksort($survey_data);
        //export csv in survey static
        // if(isset($_GET['export']) and $_GET['export']=='csv'){
        // }

        if (!file_exists('document')) {
            mkdir('document', 0755, true);
        }

        $survey_name = getSurvey()[$survey_id];
        $dir = 'document/survey-report-' . $row_get_report['id'] . '.csv';
        $path[] = $dir;
        download_csv_folder($survey_data, $data_type, $dir);

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
                                <h4>' . strtoupper(getSurvey()[$survey_id]) . '</h4>
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

        $mpdf->WriteHTML($html);
        $mpdf->SetHTMLFooter($footer);
        $mpdf->Output($dir, 'F');

        $attachments = array('document/survey-report-' . $row_get_report['id'] . '.csv', 'document/survey-report-' . $row_get_report['id'] . '.pdf');
        $mail_users = explode(",", $row_get_report['send_to']);
        foreach ($mail_users as $userId) {
            //send mail
            $user_details = get_user_datails($userId);
            $to = $user_details['email'];
            $from_mail = "dgs@gmail.com";
            $name = $user_details['name'];
            $subject = "Schedule Report";
            $message = 'Hello ' . $name . ' you have schedule report';
            $mail = cron_emails($attachments, $to, $from_mail, $name, $subject, $message);
        }

        // update next schedule date with interval
        $nextScheduledDate = $row_get_report['next_date'];
        $updateSchedule = date('Y-m-d H:i:s', strtotime(' + ' . $row_get_report['sch_interval'] . ' hours', strtotime($nextScheduledDate)));
        $data = array(
            "next_date" => $updateSchedule,
        );
        $update = dbRowUpdate("scheduled_report_templates", $data, "where id=" . $row_get_report['id']);

        if (count($attachments) > 0) {
            foreach ($attachments as $key => $value) {
                // echo "<br>" . $value . "<br>";
                unlink($value);
            }
        }
    }
}

// $anotherArray = array('document/survey-report-1.csv', 'document/survey-report-1.pdf', 'document/survey-report-18.csv', 'document/survey-report-27.pdf');

// if (count($anotherArray) > 0) {
//     foreach ($anotherArray as $key => $value) {
//         if (file_exists($value)) {
//             echo "<br>" . $value . "<br>";
//             unlink($value);
//         }
//     }
// }
