<?php
include('../function/function.php');
include('../function/get_data_function.php');
$data = array();
$allSurvey = getSurvey();

$querys = 'SELECT * FROM answers where id!=0 ';
$groupBy = '';
if ($_POST['data_type'] == 'location') {
    $locationByUsers   = get_filter_data_by_user('locations');
    $locationIds = array_map(function($items) {
        return $items['id'];
    }, $locationByUsers);
    if (count($locationIds) > 0) {
        $locationIds = implode(',', $locationIds);
    } else {
        $locationIds = -1;
    }

    // $query = " and surveyid =" . $_POST['survey'] . " and locationid in (select id from locations where cstatus=1)";
    $query = " and surveyid =" . $_POST['survey'] . " and locationid in ($locationIds)";
    $groupBy = 'locationid';
} else if ($_POST['data_type'] == 'group') {
    $groupByUsers      = get_filter_data_by_user('groups');
    $groupIds = array_map(function($items) {
        return $items['id'];
    }, $groupByUsers);
    if (count($groupIds) > 0) {
        $groupIds = implode(',', $groupIds);
    } else {
        $groupIds = -1;
    }
    // $query = " and surveyid =" . $_POST['survey'] . " and groupid in (select id from `groups` where cstatus=1)";
    $query = " and surveyid =" . $_POST['survey'] . " and groupid in ($groupIds)";
    $groupBy = 'group';
} else if ($_POST['data_type'] == 'department') {
    $departmentByUsers = get_filter_data_by_user('departments');
    $departmentIds = array_map(function($items) {
        return $items['id'];
    }, $departmentByUsers);
    if (count($departmentIds) > 0) {
        $departmentIds = implode(',', $departmentIds);
    } else {
        $departmentIds = -1;
    }
    // $query = " and surveyid =" . $_POST['survey'] . " and departmentid in (select id from departments where cstatus=1)";
    $query = " and surveyid =" . $_POST['survey'] . " and departmentid in ($departmentIds)";
    $groupBy = 'departmentid';
} else if ($_POST['data_type'] == 'role') {
    $roleByUsers       = get_filter_data_by_user('roles');
    $roleIds = array_map(function($items) {
        return $items['id'];
    }, $roleByUsers);
    if (count($roleIds) > 0) {
        $roleIds = implode(',', $roleIds);
    } else {
        $roleIds = -1;
    }
    // $query = " and surveyid =" . $_POST['survey'] . " and roleid in (select id from departments where cstatus=1)";
    $query = " and surveyid =" . $_POST['survey'] . " and roleid in ($roleIds)";
    $groupBy = 'roleid';
} else {
    // $survey_allow = get_allowed_survey($_POST['survey_type'],'',1);
    // $survey_allow_id = implode(',', array_keys($survey_allow));
    // $filterdata = '';
    // if ($survey_allow_id) {
    //     $filterdata = " and id IN($survey_allow_id)";
    // } else {
    //     // if no survey is allowed
    //     $filterdata = " and id IN(0)";
    // } 
    // $query = " and surveyid IN (select id from surveys where cstatus=1 $filterdata)";
    
   
    $surveyByUsers     = get_survey_data_by_user($_POST['survey_type'], 1);
    $surveyIds = array_map(function($items) {
        return $items['id'];
    }, $surveyByUsers);
    if (count($surveyIds) > 0) {
        $surveyIds = implode(',', $surveyIds);
    }else{
        $surveyIds = 0;
    }
    $query = " and surveyid IN ($surveyIds)";
    $groupBy = 'surveyid';
}

$start_date = $end_date = null; 

if (!empty($_POST['fdate']) and !empty($_POST['sdate'])) {
    $start_date = $_POST['fdate'];
    $end_date = $_POST['sdate'];
    $query .= " and  cdate between '" . date('Y-m-d', strtotime($_POST['fdate'])) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($_POST['sdate']))) . "'";
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
            record_set("get_question", "select * from answers where locationid=$locId and cby=$cby $query");
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
            record_set("get_question", "select * from answers where departmentid=$depId and cby=$cby $query");
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
            record_set("get_question", "select * from answers where groupid=$grpId and cby=$cby $query");
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
            record_set("get_question", "select * from answers where roleid=$roleId and cby=$cby $query");
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
            record_set("get_question", "select * from answers where surveyid=$surveyid and cby=$cby $query");

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
    $survey_id_1 = array_keys($survey_allow );
    $unique_array = array_diff($survey_id_1, $survey_id);
    $remainingSurvey = [];
    foreach ($survey_allow as $key => $value) {
        if (!array_key_exists($key, $survey_data)) {
            $survey_data[$key]['data'][0] = 'Not-Found';
            $survey_data[$key]['contact'] = '';
        }
    }
}
ksort($survey_data);
//export csv in survey static


$survey_name = getSurvey()[$_POST['survey']];
if (isset($_GET['export']) and $_GET['export'] == 'csv') {
    export_csv_file($survey_data, $_GET['data_type'], $survey_name, $start_date, $end_date);
    die();
}

// echo "<pre>";
// print_r($survey_data);
// print_r($survey_allow);
// echo "</pre>";
// die();

//$html.='<h2 class="survey_name text-center" style="margin:0px;font-size:20px;">'.strtoupper($survey_name).'</h2>';
if (count($survey_data) > 0) {
    foreach ($survey_data as $key => $datasurvey) {
        $total =  array_sum($datasurvey['data']) / count($datasurvey['data']);
        $total =  round($total, 2);
        $titleName = '';
        if ($_POST['data_type'] == 'location') {
            $titleId = '';
            $titleName = getLocation('all')[$key];
        } else if ($_POST['data_type'] == 'group') {
            $titleId = '';
            $titleName = getGroup('all')[$key];
        } else if ($_POST['data_type'] == 'department') {
            $titleId = '';
            $titleName = getDepartment('all')[$key];
        } else if ($_POST['data_type'] == 'role') {
            $titleId = '';
            $titleName = getRole('all')[$key];
        } else {
            $titleId = 'Survey ID: ' . $key;
            $titleName = getSurvey()[$key];
        }
        if ($datasurvey['contact']) {
            $contacted = $datasurvey['contact'];
        } else {
            $contacted = 0;
        }
        $first_value = reset($datasurvey['data']);
        if ($first_value === 'Not-Found') {
            $totalSurvey = 0;
        } else {
            $totalSurvey = count($datasurvey['data']);
        }
        $html .= '<div class="col-md-3"> 
        <div class="graph-body">  
                <p style="font-size: 14px;font-weight: 700;text-align:center;height: 60px;">' . ucwords($titleName) . '</p>  
                <p style="font-size: 14px;font-weight: 700;text-align:center">' . ucwords($titleId) . '</p>     
                <div id="canvas-holder">
                    <span class="g-persent" style="font-size:18px;margin-left: 15px;"><strong>' . $total . ' %</strong></span>
                    <canvas id="chart_' . $i . '"></canvas>
                        <div class="row" style="text-align:center;">
                            <div class="col-md-12"><span class="total-count"><strong>TOTAL SURVEYS: ' . $totalSurvey . '</strong></span></div>
                        </div>
                        <div class="row" style="text-align:center;">
                            <div class="col-md-12"><span class="total-count"><strong>CONTACT REQUESTS: ' . ($contacted) . '</strong></span></div>
                        </div>
                    </div>
                </div>
            </div>';

        if ($i == 6 && count($survey_data) > 6) {
            $j = $j + 9;
            $html .= '<div class="html2pdf__page-break"></div>';
        }

        if ($j > 14 && $i == $j && $i < count($survey_data)) {
            $j = $j + 9;
            $html .= '<div class="html2pdf__page-break" style="margin-top: 50px"></div>';
        }
        $i++;
    }
} else {
    $html .= '<p style="margin-left: 21px !important;">No result found</p>';
}

$footer_flag = (count($survey_data) - 6) % 9;
$data['survey_data_count'] = count($survey_data);
$data['html'] = $html;
$data['result'] = $survey_data;
$data['footer_flag'] = abs($footer_flag);
echo json_encode($data);
die();
