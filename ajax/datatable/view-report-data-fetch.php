<?php
include('../../function/function.php');
include('../../function/get_data_function.php');

use Google\Protobuf\Option;

$draw  = $_REQUEST['draw'];
$start = $_REQUEST['start'];
$length = $_REQUEST['length'];
$page_type = $_GET['type'];
$locationByUsers   = get_filter_data_by_user('locations');
$departmentByUsers = get_filter_data_by_user('departments');
$roleByUsers       = get_filter_data_by_user('roles');
$groupByUsers      = get_filter_data_by_user('groups');
$surveyByUsers     = get_survey_data_by_user($page_type, 1);

// get assign ids only
$assign_department = array();
foreach ($departmentByUsers as $department) {
    $assign_department[] = $department['id'];
}

$assign_location = array();
foreach ($locationByUsers as $location) {
    $assign_location[] = $location['id'];
}
$assign_group = array();
foreach ($groupByUsers as $group) {
    $assign_group[] = $group['id'];
}

$assign_role = array();
foreach ($roleByUsers as $role) {
    $assign_role[] = $role['id'];
}

$assign_survey = array();
foreach ($surveyByUsers as $survey) {
    $assign_survey[] = $survey['id'];
}

$dep_ids     = implode(',', $assign_department);
$loc_ids     = implode(',', $assign_location);
$grp_ids     = implode(',', $assign_group);
$surveys_ids = implode(',', $assign_survey);
$role_ids    = implode(',', $assign_role);



$data = array();

## Fetch records
// $dateflag = false;
$query = 'SELECT * FROM answers where id !=0 ';

if (isset($_GET['response']) && !empty($_GET['response'])) {
    $query .= " and cby = '" . $_GET['response'] . "'";
}

if (!empty($_POST['departmentid'])) {
    $query .= " and departmentid = '" . $_POST['departmentid'] . "'";
} else {
    // If no department ID is provided in the POST request, check if there is a predefined list of department IDs ($dep_ids).
    if ($dep_ids) {
        $query .= " and departmentid IN ($dep_ids)";
    } else {
        $query .= " and departmentid IN (0)";
    }
}

if (!empty($_POST['roleid'])) {
    $query .= " and roleid = '" . $_POST['roleid'] . "'";
}

if (!empty($_POST['locationid'])) {
    $query .= "and locationid = '" . $_POST['locationid'] . "'";
} else {
    // If no location ID is provided in the POST request, check if there is a predefined list of location IDs ($loc_ids).
    if ($loc_ids) {
        $query .= " and locationid IN ($loc_ids)";
    } else {
        $query .= " and locationid IN (0)";
    }
}


if (!empty($_POST['surveys'])) {
    $query .= " and surveyid =" . $_POST['surveys'];
} else {
    if ($surveys_ids) {
        $query .= " and surveyid IN ($surveys_ids)";
    } else {
        $query .= " and surveyid IN (0)";
    }
}


if (!empty($_POST['groupid'])) {
    $query .= " and groupid = '" . $_POST['groupid'] . "'";
} else {
    // If no group ID is provided in the POST request, check if there is a predefined list of group IDs ($grp_ids).
    if ($grp_ids) {
        $query .= " and groupid IN ($grp_ids)";
    } else {
        $query .= " and groupid IN (0)";
    }
}

if (!empty($_POST['fdate']) && !empty($_POST['sdate'])) {
    $query .= " and cdate between '" . date('Y-m-d', strtotime($_POST['fdate'])) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($_POST['sdate']))) . "'";
}
// if(!empty($_POST['contacted']) and $_POST['contacted'] !=3){
//     if($_POST['contacted'] == 1){
//         $query .= " and  answerid =-2 and answerval=100";
//     }else {
//         $query .= " and  answerid != -2 and answerval != 100";
//     }
// }
// LIMIT $start, $length
$query .= " GROUP BY cby ORDER BY id DESC";
// Fetch departments for displaying purposes
record_set("get_departments", "SELECT * FROM departments");
$departments = array();
while ($row_get_departments = mysqli_fetch_assoc($get_departments)) {
    $departments[$row_get_departments['id']] = $row_get_departments['name'];
}
record_set("get_recent_entry", $query." LIMIT $start, $length");
record_set("get_recent_COUNT_entry", $query);
$totalRecords = $totalRows_get_recent_COUNT_entry; 

if($totalRecords >0){
    while($row_get_recent_entry = mysqli_fetch_assoc($get_recent_entry)){
        record_set("get_survey_detail", "SELECT * FROM surveys where id='".$row_get_recent_entry['surveyid']."'");	
        $row_get_survey_detail = mysqli_fetch_assoc($get_survey_detail);
        
        $row_survey_entry = 1;
        record_set("survey_entry", "SELECT DISTINCT cby FROM answers where surveyid='".$row_get_survey_detail['id']."' and cby <".$row_get_recent_entry['cby']);
        $row_survey_entry = $totalRows_survey_entry+$row_survey_entry;
    
        $total_result_val=0;  
        $achieved_result_val = 0;
        $to_bo_contacted     = 0;
        $i=0;

        record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_get_recent_entry['surveyid']."' and cby='".$row_get_recent_entry['cby']."'");

        while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_survey_result['questionid']);
            if($result_question){
                if(!in_array($result_question['answer_type'],array(2,3,5))){
                    $total_result_val = ($i+1)*100;
                    $achieved_result_val += $row_get_survey_result['answerval'];
                    $i++;
                }
            }
            if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100){
                $to_bo_contacted = 1;
            }
        }
        $result_response = $achieved_result_val*100/$total_result_val;
        if($achieved_result_val==0 and $total_result_val==0){
            $result_response=100;
        }
        // if($to_bo_contacted == 0 and $_POST['contacted'] == 2){
        //     continue;
        // }
        // for filter using contact
        if($_POST['contacted'] !='' and  $_POST['contacted']!=3){
            if($to_bo_contacted == 1 && $_POST['contacted'] == 2){
                continue;
            }
            if($to_bo_contacted == 0 && $_POST['contacted'] == 1){
                continue;
            }
        }
        $label_class = 'success';
        if($result_response<50){
            $label_class = 'danger';
        }else 
        if($result_response<75){
            $label_class = 'info';
        }
        if($to_bo_contacted==1){ 
            $contactedLabel ='<a class="btn btn-xs bg-green">Yes</a>';
        }else{ 
            $contactedLabel ='<a class="btn btn-xs btn-danger">No</a>';
        } 
            

        // Add row data to the data array
        $data[] = array(
            "date" => date("d-m-Y", strtotime($row_get_recent_entry['cdate'])),
            "survey_name" => $row_get_survey_detail['name'],
            "group" => getGroup()[$row_get_recent_entry['groupid']],
            "location" => getLocation()[$row_get_recent_entry['locationid']],
            "department" => $departments[$row_get_recent_entry['departmentid']] ?? '',
            "roles" => getRole()[$row_get_recent_entry['roleid']],
            "respondendent_number" => $row_survey_entry,
            "result" => '<label class="label label-' . $label_class . '">' . round($result_response, 2) . '%</label>',
            "contact_request" => $contactedLabel,
            "action" => '<a class="btn btn-xs btn-primary" href="survey-result.php?surveyid=' . $row_get_recent_entry['surveyid'] . '&userid=' . $row_get_recent_entry['cby'] . '&score=' . round($result_response, 2) . '&contacted=' . $to_be_contacted . '" target="_blank">VIEW DETAILS</a>'
        );
    }
}

## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecords,
    "aaData" => $data
);
echo json_encode($response);
