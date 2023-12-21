<?php
require dirname(__DIR__, 2) . '/function/function.php';
require dirname(__DIR__, 2) . '/function/get_data_function.php';
include dirname(__DIR__, 2) . '/permission.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

record_set("get_scheduled_report", "select srt.* from scheduled_report_templates as srt INNER JOIN report_templates as rt ON srt.temp_id = rt.id where rt.report_type=1 ORDER BY srt.id DESC");

while ($row_get_report = mysqli_fetch_assoc($get_scheduled_report)) {
    $mpdf = new \Mpdf\Mpdf();
    $ready_to_run = $not_first_time = false;

    // echo '<pre>';
    // print_r($row_get_report);
    // echo '</pre>';

    $frequency_interval  = $row_get_report['sch_interval'];
    $time_interval = $row_get_report['time_interval'];

    $current_date  = date('Y-m-d', time());
    $schedule_start_date = date('Y-m-d', strtotime($row_get_report['start_date']));
    $schedule_next_date = date('Y-m-d', strtotime($row_get_report['next_date']));
    $end_date = date('Y-m-d', strtotime($row_get_report['end_date']));

    // echo "current_date: $current_date <br>";
    // echo "schedule_start_date: $schedule_start_date <br>";
    // echo "schedule_next_date: $schedule_next_date <br>";
    // echo "end_date: $end_date <br>";

    $curr_eq_st_date = check_differenceDate($current_date, $schedule_start_date, 'eq');
    $curr_eq_nxt_date = check_differenceDate($current_date, $schedule_next_date, 'eq');

    $curr_lte_nxt_date = check_differenceDate($current_date, $schedule_next_date, 'lte');
    $curr_lte_end_date = check_differenceDate($current_date, $end_date, 'lte');

    // echo "curr_eq_st_date: $curr_eq_st_date <br>";
    // echo "curr_eq_nxt_date: $curr_eq_nxt_date <br>";
    // echo "curr_lte_nxt_date: $curr_lte_nxt_date <br>";
    // echo "curr_lte_end_date: $curr_lte_end_date <br>";

    
    if ($curr_eq_st_date  && $curr_lte_nxt_date && $curr_lte_end_date) {
        $ready_to_run = true;
    }
   
    if ($curr_eq_nxt_date && $curr_lte_end_date) {
        $ready_to_run = $not_first_time = true;
    }

    // echo "ready_to_run: $ready_to_run <br>";
    // echo "not_first_time: $not_first_time <br>";

    if ($ready_to_run && $row_get_report['send_to'] != null) {
      
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

        $row_survey = mysqli_fetch_assoc($survey_min_date);

        // time interval calculation.
        if ($frequency_interval == 24 && $time_interval == 24) {
            // echo '<b>F1 && T1</b> <br>';
            $survey_min_date = date('Y-m-d', strtotime('-1 day', strtotime($current_date)));
            $survey_max_date = date('Y-m-d', strtotime($current_date));
        } else if ($frequency_interval == 168 &&  ($time_interval == 24 || $time_interval == 168)) {
            if ($time_interval == 24) {
                // echo  '<b>F7 && T1</> <br>';
            } else {
                // echo  '<b>F7 && T7</> <br>';
            }
            $survey_min_date = date('Y-m-d', strtotime('-7 day', strtotime($current_date)));
            $survey_max_date = date('Y-m-d', strtotime('+7 day', strtotime($survey_min_date)));
        } else if ($frequency_interval == 720 &&  ($time_interval == 24 || $time_interval == 168 ||  $time_interval == 720)) {
            if ($time_interval == 24) {
                // echo  '<b>F30 && T1</> <br>';
            } else if ($time_interval == 168) {
                // echo  '<b>F30 && T7</> <br>';
            } else {
                // echo  '<b>F30 && T30</> <br>';
            }
            $survey_min_date = date('Y-m-d', strtotime('-1 month', strtotime($current_date)));
            $survey_max_date = date('Y-m-d', strtotime('+1 month', strtotime($survey_min_date)));
        } else if ($frequency_interval == 2160 && ($time_interval == 168 ||  $time_interval == 720)) {
            if ($time_interval == 168) {
                // echo  '<b>F90 && T7</> <br>';
            } else {
                // echo  '<b>F90 && T30</> <br>';
            }
            $survey_min_date = date('Y-m-d', strtotime('-3 month', strtotime($current_date)));
            $survey_max_date = date('Y-m-d', strtotime('+3 month', strtotime($survey_min_date)));
        } else if ($frequency_interval == 4320 && ($time_interval == 168 ||  $time_interval == 720)) {
            if ($time_interval == 168) {
                // echo  '<b>F180 && T7</> <br>';
            } else {
                // echo  '<b>F180 && T30</> <br>';
            }
            $survey_min_date = date('Y-m-d', strtotime('-6 month', strtotime($current_date)));
            $survey_max_date = date('Y-m-d', strtotime('+6 month', strtotime($survey_min_date)));
        } else if ($frequency_interval == 8640 && ($time_interval == 720 ||  $time_interval == 4320)) {
            if ($time_interval == 168) {
                // echo  '<b>F365 && T30</> <br>';
            } else {
                // echo  '<b>F365 && T90</> <br>';
            }
            $survey_min_date = date('Y-m-d', strtotime('-1 year', strtotime($current_date)));
            $survey_max_date = date('Y-m-d', strtotime('+1 year', strtotime($survey_min_date)));
        }

        // echo "Data type: $data_type" . '<br>';
        // echo "Current date: $current_date" . '<br>';
        // echo "survey_min_date: $survey_min_date" . '<br>';
        // echo "survey_max_date: $survey_max_date" . '<br>';

        $survey_data = array();

        while ($survey_min_date < $survey_max_date) {
            if ($time_interval == 24) {
                $survey_temp_max_date =  date('Y-m-d', strtotime('+1 day', strtotime($survey_min_date)));
            } else if ($time_interval == 168) {
                $survey_temp_max_date =  date('Y-m-d', strtotime('+8 day', strtotime($survey_min_date)));
            } else if ($time_interval == 720) {
                $survey_temp_max_date =  date('Y-m-d', strtotime('+1 month', strtotime($survey_min_date)));
            } else if ($time_interval == 4320) {
                $survey_temp_max_date =  date('Y-m-d', strtotime('+3 month', strtotime($survey_min_date)));
            }

            // echo "survey_temp_max_date: $survey_temp_max_date" . '<br>';
            record_set("get_entry", $querys . $query . " and cdate BETWEEN '" . $survey_min_date . "' and '" . $survey_temp_max_date . "' GROUP by cby");

            if ($time_interval == 24) {
                $survey_min_date =  date('Y-m-d', strtotime('+1 day', strtotime($survey_min_date)));
            } else if ($time_interval == 168) {
                $survey_min_date =  date('Y-m-d', strtotime('+8 day', strtotime($survey_min_date)));
            } else if ($time_interval == 720) {
                $survey_min_date =  date('Y-m-d', strtotime('+1 month', strtotime($survey_min_date)));
            } else if ($time_interval == 4320) {
                $survey_min_date =  date('Y-m-d', strtotime('+3 month', strtotime($survey_min_date)));
            }
            // echo "survey_min_date: $survey_min_date" . '<br>';

            if ($totalRows_get_entry) {
                $to_bo_contacted = 0;
                $st_date = $end_date = '';

                while ($row_get_entry = mysqli_fetch_assoc($get_entry)) {
                    $locId      = $row_get_entry['locationid'];
                    $depId      = $row_get_entry['departmentid'];
                    $grpId      = $row_get_entry['groupid'];
                    $surveyid   = $row_get_entry['surveyid'];
                    $cby        = $row_get_entry['cby'];
                    $surveyDate = date('Y-m-d', strtotime($row_get_entry['cdate']));

                    // echo "time_interval : $time_interval" . '<br>';

                    if ($time_interval == 24) {
                        $st_date = date('Y-m-d', strtotime('-1 day', strtotime($survey_min_date)));
                    } else if ($time_interval == 168) {
                        $st_date = date('Y-m-d', strtotime('-8 day', strtotime($survey_min_date)));
                        $end_date = date('Y-m-d', strtotime('-1 day', strtotime($survey_temp_max_date)));
                    } else if ($time_interval == 720) {
                        $st_date = date('Y-m-d', strtotime('-1 month', strtotime($survey_min_date)));
                        $end_date = date('Y-m-d', strtotime('-1 day', strtotime($survey_temp_max_date)));
                    } else if ($time_interval == 4320) {
                        $st_date = date('Y-m-d', strtotime('-3 month', strtotime($survey_min_date)));
                        $end_date = date('Y-m-d', strtotime('-1 day', strtotime($survey_temp_max_date)));
                    }

                    if ($end_date > $survey_max_date) {
                        $end_date = $survey_max_date;
                    }

                    // echo "st_date : $st_date" . '<br>';
                    // echo "end_date : $end_date" . '<br>';

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
                                $survey_data[$st_date][$locId]['contact'] += 1;
                            }
                        }
                        $average_value = ($total_answer / ($i * 100)) * 100;
                        if ($total_answer == 0 and $total_result_val == 0) {
                            $average_value = 100;
                        }

                        // time interval $survey_data[$locId]['data'][$cby] = $average_value;
                        if ($time_interval == 24) {
                            $survey_data[$st_date][$locId]['data'][$cby] = $average_value;
                            $survey_data[$st_date][$locId]['data']['survey_id'] = $survey_id;
                        }

                        if ($time_interval != 24) {
                            $survey_data[$st_date][$locId]['data'][$cby] = $average_value;
                            $survey_data[$st_date][$locId]['end_date'] = $end_date;
                            $survey_data[$st_date][$locId]['survey_id'] = $survey_id;
                        }
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
                                $survey_data[$st_date][$depId]['contact'] += 1;
                            }
                        }
                        $average_value = ($total_answer / ($i * 100)) * 100;
                        if ($total_answer == 0 and $total_result_val == 0) {
                            $average_value = 100;
                        }

                        // time interval  $survey_data[$depId]['data'][$cby] = $average_value;
                        if ($time_interval == 24) {
                            $survey_data[$st_date][$depId]['data'][$cby] = $average_value;
                            $survey_data[$st_date][$depId]['data']['survey_id'] = $survey_id;
                        }

                        if ($time_interval != 24) {
                            $survey_data[$st_date][$depId]['data'][$cby] = $average_value;
                            $survey_data[$st_date][$depId]['end_date'] = $end_date;
                            $survey_data[$st_date][$depId]['survey_id'] = $survey_id;
                        }
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
                                $survey_data[$st_date][$grpId]['contact'] += 1;
                            }
                        }
                        $average_value = ($total_answer / ($i * 100)) * 100;
                        if ($total_answer == 0 and $total_result_val == 0) {
                            $average_value = 100;
                        }

                        // time interval $survey_data[$grpId]['data'][$cby] = $average_value;
                        if ($time_interval == 24) {
                            $survey_data[$st_date][$grpId]['data'][$cby] = $average_value;
                            $survey_data[$st_date][$grpId]['data']['survey_id'] = $survey_id;
                        }

                        if ($time_interval != 24) {
                            $survey_data[$st_date][$grpId]['data'][$cby] = $average_value;
                            $survey_data[$st_date][$grpId]['end_date'] = $end_date;
                            $survey_data[$st_date][$grpId]['survey_id'] = $survey_id;
                        }
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
                                $survey_data[$st_date][$surveyid]['contact'] += 1;
                            }
                        }
                        if ($total_answer == 0 and $total_result_val == 0) {
                            $average_value = 100;
                        }
                        $average_value = ($total_answer / ($i * 100)) * 100;
                        if (is_nan($average_value)) {
                            $average_value = 100;
                        }

                        // time interval
                        if ($time_interval == 24) {
                            $survey_data[$st_date][$surveyid]['data'][$cby] = $average_value;
                        }

                        if ($time_interval != 24) {
                            $survey_data[$st_date][$surveyid]['data'][$cby] = $average_value;
                            $survey_data[$st_date][$surveyid]['end_date'] = $end_date;
                        }
                    }
                }
            }
        }

        ksort($survey_data);

        // echo "id:".$row_get_report['id'].'<br>'; 
        // echo '<pre>';
        // print_r($survey_data);
        // echo '</pre>';  

        if (!file_exists('document')) {
            mkdir('document', 0755, true);
        }

        $survey_name = getSurvey()[$survey_id];
        $dir = 'document/survey-report-' . $row_get_report['id'] . '.csv';
        $path[] = $dir;

        download_csv_folder($survey_data, $data_type, $dir, $time_interval);

        $surveyArrayCount = array_sum(array_map('count', $survey_data));
        $html = '';
        $counter = 1;
        $j = 6;
        $dataSurveyCount = 0;
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
                                <h4>' . date('d/m/Y', strtotime($row_get_report['start_date'])) . '-' . date('d/m/Y', strtotime($row_get_report['end_date'])) . '</h4>

                            </div>
                        </div>
                    </div>   
                    <div class="row">';


        if (count($survey_data) > 0) {
            foreach ($survey_data as $mainKey => $datasurveys) {
                foreach ($datasurveys as $key => $datasurvey) {
                    $dataSurveyCount++;
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
                                                            <h5 style="height:30px;margin-top: 0;">' . $titleName . '</h5>';
                        if ($time_interval == 24) {
                            $html .= '<div style="font-size: 10px;padding-bottom: 10px;"><strong>' . date('d/m/Y', strtotime($mainKey))  . '</strong></div>';
                        } else {
                            $html .= '<div style="font-size: 10px;padding-bottom: 10px;"><strong>' . date('d/m/Y', strtotime($mainKey)) . ' - ' . date('d/m/Y', strtotime($datasurvey['end_date']))  . '</strong></div>';
                        }
                        $html .= '<div style="font-size: 13px;padding-bottom: 4px;"><strong>' . $titleId  . '</strong></div>';

                        $html .= '<span style="font-size: 16px;">' . $total . '%</span>
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
                        $html .=  '<div class="col-md-4">
                                            <div class="metter-outer active">
                                                    <div class="circle">
                                                        <div class="top-content">
                                                            <h5 style="height:30px;margin-top: 0;">' . $titleName . '</h5>';
                        if ($time_interval == 24) {
                            $html .= '<div style="font-size: 10px;padding-bottom: 10px;"><strong>' . date('d/m/Y', strtotime($mainKey))  . '</strong></div>';
                        } else {
                            $html .= '<div style="font-size: 10px;padding-bottom: 10px;"><strong>' . date('d/m/Y', strtotime($mainKey)) . ' - ' . date('d/m/Y', strtotime($datasurvey['end_date']))  . '</strong></div>';
                        }
                        $html .= '<div style="font-size: 13px;padding-bottom: 4px;"><strong>' . $titleId  . '</strong></div>';
                        $html .= '<span style="font-size: 16px;">' . $total . '% </span>
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
                    }

                    if ($counter == 6 && $surveyArrayCount > 6) {
                        $j = $j + 9;
                        $html .= '<pagebreak/>';
                    }

                    if ($j > 14 && $counter == $j && $counter < $surveyArrayCount) {
                        $j = $j + 9;
                        $html .= '<pagebreak>';
                    }
                    $counter++;
                }
            }
        }else{
            $html .='<div class="col-md-12"><h3 style="text-align:center;">No records were found.</h3></div>'; 
        }

        $html .= '</div>
                </div>
            </body>
            </html>';


        $dir = 'document/survey-report-' . $row_get_report['id'] . '.pdf';
        $footer = '<div style="text-align: center;"> ' . POWERED_BY . '
        <center><img  src="' . BASE_URL . FOOTER_LOGO . '" alt="" width="150"/></center>
        </div>';

        $mpdf->SetHTMLFooter($footer);
        $mpdf->WriteHTML($html);
        $mpdf->Output($dir, 'F');

        $attachments = array('document/survey-report-' . $row_get_report['id'] . '.csv', 'document/survey-report-' . $row_get_report['id'] . '.pdf');

        $mail_users = explode(",", $row_get_report['send_to']);
        foreach ($mail_users as $userId) {
            //send mail
            $user_details = get_user_datails($userId);
            $to = $user_details['email'];
            $from_mail = ADMIN_EMAIL;
            $name = $user_details['name'];
            $subject = "Schedule Report";
            $message = 'Hello ' . $name . ' you have schedule report';
            $mail = cron_emails($attachments, $to, $from_mail, $name, $subject, $message);
        }

        if ($not_first_time) {
            // update next schedule date with interval
            $nextScheduledDate = $row_get_report['next_date'];
            $updateSchedule = date('Y-m-d H:i:s', strtotime(' + ' . $row_get_report['sch_interval'] . ' hours', strtotime($nextScheduledDate)));
            $data = array(
                "next_date" => $updateSchedule,
            );
            $update = dbRowUpdate("scheduled_report_templates", $data, "where id=" . $row_get_report['id']);
        }

        if (count($attachments) > 0) {
            foreach ($attachments as $key => $value) {
                // echo "<br>" . $value . "<br>";
                unlink($value);
            }
        }

        echo "ready_to_run: ".$ready_to_run." & not_first_time: ".$not_first_time." for the scheduled_report_template_id: ".$row_get_report['id']." <br>";
    } else {
        echo "ready_to_run: $ready_to_run <br>";
    }
}