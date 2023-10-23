<?php
include('../function/function.php');
include('../function/get_data_function.php');
$data = array();

$surveyid   = $_POST['survey'];
$surveyName = getSurvey()[$surveyid];

$querys = 'SELECT * FROM answers where id!=0 and surveyid = ' . $surveyid;
$groupBy = '';
if ($_POST['survey_type'] == 'location') {
    $query = " and locationid in (select id from locations where cstatus=1)";
    $groupBy = 'locationid';
} else if ($_POST['survey_type'] == 'group') {
    $query = " and groupid in (select id from `groups` where cstatus=1)";
    $groupBy = 'group';
} else if ($_POST['survey_type'] == 'department') {
    $query = " and departmentid in (select id from departments where cstatus=1)";
    $groupBy = 'departmentid';
} else if ($_POST['survey_type'] == 'role') {
    $query = " and roleid in (select id from roles where cstatus=1)";
    $groupBy = 'roleid';
}

if (!empty($_POST['fdate']) and !empty($_POST['fdate'])) {
    $query .= " and  cdate between '" . date('Y-m-d', strtotime($_POST['fdate'])) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($_POST['sdate']))) . "'";
}

record_set("total_survey", "SELECT COUNT(DISTINCT(cby)) as totalCount FROM answers WHERE id!=0  $query");
$row_total_survey = mysqli_fetch_assoc($total_survey);
$total_survey = $row_total_survey['totalCount'];
record_set("get_entry", $querys . $query . " GROUP by cby");

if ($totalRows_get_entry) {
    $survey_data = array();
    $survey_overall = array();
    $overallCount = 0;
    while ($row_get_entry = mysqli_fetch_assoc($get_entry)) {
        $locId      = $row_get_entry['locationid'];
        $depId      = $row_get_entry['departmentid'];
        $roleId     = $row_get_entry['roleid'];
        $grpId      = $row_get_entry['groupid'];
        $cby        = $row_get_entry['cby'];

        if ($_POST['survey_type'] == 'location') {
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
            }
            $average_value = ($total_answer / ($i * 100)) * 100;
            if ($total_answer == 0 and $total_result_val == 0) {
                $average_value = 100;
            }
            $survey_data[$locId][$cby] = $average_value;
        } else if ($_POST['survey_type'] == 'department') {
            $count = array();
            record_set("get_question", "select * from answers where departmentid=$depId and cby=$cby");
            $total_answer = 0;
            $i = 0;
            $total_result_val = 0;
            while ($row_get_question = mysqli_fetch_assoc($get_question)) {
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and  id =" . $row_get_question['questionid']);
                if ($result_question) {
                    if (!in_array($result_question['answer_type'], array(2, 3, 5))) {
                        $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
            }
            $average_value = ($total_answer / ($i * 100)) * 100;
            if ($total_answer == 0 and $total_result_val == 0) {
                $average_value = 100;
            }
            $survey_data[$depId][$cby] = $average_value;
        } else if ($_POST['survey_type'] == 'role') {
            $count = array();
            record_set("get_question", "select * from answers where roleid=$roleId and cby=$cby");
            $total_answer = 0;
            $i = 0;
            $total_result_val = 0;
            while ($row_get_question = mysqli_fetch_assoc($get_question)) {
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and  id =" . $row_get_question['questionid']);
                if ($result_question) {
                    if (!in_array($result_question['answer_type'], array(2, 3, 5))) {
                        $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
            }
            $average_value = ($total_answer / ($i * 100)) * 100;
            if ($total_answer == 0 and $total_result_val == 0) {
                $average_value = 100;
            }
            $survey_data[$roleId][$cby] = $average_value;
        } else if ($_POST['survey_type'] == 'group') {
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
            }
            $average_value = ($total_answer / ($i * 100)) * 100;
            if ($total_answer == 0 and $total_result_val == 0) {
                $average_value = 100;
            }
            $survey_data[$grpId][$cby] = $average_value;
        }

        record_set("get_question_overall", "select * from answers where surveyid=$surveyid and cby=$cby");
        $total_answer = 0;
        $i = 0;
        $total_result_val = 0;
        while ($row_get_question = mysqli_fetch_assoc($get_question_overall)) {
            $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =" . $row_get_question['questionid']);
            if ($result_question) {
                if (!in_array($result_question['answer_type'], array(2, 3, 5))) {
                    $i++;
                    $total_answer += $row_get_question['answerval'];
                }
            }
        }
        $average_value = ($total_answer / ($i * 100)) * 100;
        $overallCount++;
        $survey_overall[$overallCount] += $average_value;
    }
}
$avgScore = round(array_sum($survey_overall) / count($survey_overall), 2);

if (is_nan($avgScore)) {
    if (count($survey_overall) > 0) {
        $avgScore = 100;
    } else {
        $avgScore = 0;
    }
}

// send data with table
$arrayData = array();
$filename = 'view-analytics-' . date("Y-m-d-H:i:s", time()) . ".xls"; // File Name

header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Type: application/vnd.ms-excel");

$i = 1;
ksort($survey_data);
$graph_data = array();
$key_values = array_column($current_data, 'avg');
array_multisort($key_values, SORT_DESC, $current_data);
foreach ($survey_data as $key => $datasurvey) {
    $total =  array_sum($datasurvey) / count($datasurvey);
    $total =  round($total, 2);

    if ($_POST['survey_type'] == 'location') {
        $titleName = getLocation()[$key];
    } else if ($_POST['survey_type'] == 'group') {
        $titleName = getGroup()[$key];
    } else if ($_POST['survey_type'] == 'department') {
        $titleName = getDepartment()[$key];
    } else if ($_POST['survey_type'] == 'role') {
        $titleName = getRole()[$key];
    }
    $graph_data[$titleName] = $total;
    $arrayData[] = array(
        "SURVEY" => $surveyName,
        strtoupper($_POST['survey_type']) => $titleName,
        "NO OF SURVEY" => count($datasurvey),
        "AVERAGE SCORE" => ($total > 0) ? $total . '%' : '0' . '%',
    );
    $i++;
}

foreach ($arrayData as $data) {
    if (!$flag) {
        echo implode("\t", array_keys($data)) . "\r\n";
        $flag = true;
    }
    echo implode("\t", array_values($data)) . "\r\n";
}
