<?php

function getDepartment($status = null)
{
	$arr = array();
	$filter = '';
	if ($status != 'all') {
		$filter = "where cstatus=1";
	}
	$departments_data = getaxecuteQuery_fn("select id,name from departments $filter order by name ASC ");
	foreach ($departments_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getLocation($status = null)
{
	$arr = array();
	$filter = '';
	if ($status != 'all') {
		$filter = "where cstatus=1";
	}
	$locations_data = getaxecuteQuery_fn("select id,name from locations $filter order by name ASC");
	foreach ($locations_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getGroup($status = null)
{
	$arr = array();
	$filter = '';
	if ($status != 'all') {
		$filter = "where cstatus=1";
	}
	$groups_data = getaxecuteQuery_fn("select id,name from `groups`  $filter order by name ASC");
	foreach ($groups_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getSurvey()
{
	$arr = array();
	$survey_data = getaxecuteQuery_fn("select id,name from surveys  where cstatus=1 order by name ASC");
	foreach ($survey_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getRole($status = null)
{
	$arr = array();
	$filter = '';
	if ($status != 'all') {
		$filter = "where cstatus=1";
	}
	$role_data = getaxecuteQuery_fn("select id,name from roles $filter order by name ASC ");
	foreach ($role_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getAdmin($id = null)
{
	$arr = array();
	$filter = '';
	if ($id != null) {
		$filter  = " and id IN ($id)";
	}
	$admin_data = getaxecuteQuery_fn("select id,name from manage_users where user_type = 3 and cstatus=1 $filter order by name ASC");
	foreach ($admin_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getClient($id = null)
{
	$arr = array();
	$filter = '';
	if ($id != null) {
		$filter  = " and id IN ($id)";
	}
	$clients_data = getaxecuteQuery_fn("select id,name from manage_users where user_type = 4 and cstatus=1 $filter order by name ASC");
	foreach ($clients_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}

function getUsers($user_type = null)
{
	$arr = array();
	$filter = '';
	if ($user_type != null) {
		$filter .= " and user_type = $user_type";
	}
	$clients_data = getaxecuteQuery_fn("select id,name from manage_users where cstatus=1 $filter order by name ASC");
	foreach ($clients_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}

// get assign location department group survey id of loging user from relation table
function get_assing_id_dept_loc_grp_survey($table_name = null)
{
	if ($table_name == null) {
		$table_name = "survey','engagement','pulse";
	}
	$relation_data = getaxecuteQuery_fn("select * from relation_table where user_id = " . $_SESSION['user_id'] . " and table_name IN ('$table_name')");

	$arr_id = array();
	while ($row_get_relation_data = mysqli_fetch_assoc($relation_data)) {
		$arr_id[] = $row_get_relation_data['table_id'];
	}

	if ($_SESSION['user_type'] < 2) {
		$table_ids = '';
	} else {
		$table_ids = implode(',', $arr_id);
	}
	return $table_ids;
}
// only survey id and survey name
function get_allowed_survey($survey_type = '', $confidential = 0, $active = "")
{
	$arr = array();
	$sFilter = '';
	// get survey type
	if ($survey_type == 'engagement') {
		$sFilter = " and survey_type = 3";
	} else if ($survey_type == 'pulse') {
		$sFilter = " and survey_type = 2";
	} else if ($survey_type == 'survey') {
		$sFilter = " and survey_type = 1";
	}
	// survey assign to user 
	$user_id = $_SESSION['user_id'];
	if ($_SESSION['user_type'] > 2) {
		$survey_id = get_assigned_user_data($user_id, $survey_type);
		$survey_id = implode(',', $survey_id);
		if ($survey_id) {
			$sFilter .= " and id IN ($survey_id)";
		} else {
			$sFilter .= " and id IN (0)";
		}
	}
	if ($active == 1) {
		$sFilter .= " and cstatus = 1";
	}
	//get unconfidential data
	if ($confidential == 1) {
		$sFilter .= " and confidential !=1";
	}
	$allowed_data = getaxecuteQuery_fn("select id,name from surveys  where id !=0 $sFilter order by name ASC");
	foreach ($allowed_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
// get all userid by table id(location id,dept id,grp id, or survey id)
function get_assigned_data($table_id = null, $table)
{
	$sFilter = '';
	if ($table_id) {
		$sFilter .= " and table_id =" . $table_id;
	}
	if ($table) {
		$sFilter .= " and table_name = '$table' ";
	}
	$allowed_data = getaxecuteQuery_fn("select * from relation_table  WHERE id != '' $sFilter ");
	foreach ($allowed_data as $val) {
		$arr[] = $val['user_id'];
	}
	return $arr;
}

// get location_id,dept_id,group_id,or survey_id by user_id 
function get_assigned_user_data($user_id = null, $table)
{
	$sFilter = '';
	if ($user_id) {
		$sFilter .= " and user_id =" . $user_id;
	}
	if ($table) {
		$sFilter .= " and table_name = '$table' ";
	}
	$allowed_data = getaxecuteQuery_fn("select * from relation_table  WHERE id != '' $sFilter ");
	foreach ($allowed_data as $val) {
		$arr[] = $val['table_id'];
	}
	return $arr;
}
function get_filter_data_by_user($table)
{
	//get assigned department location group survey
	if ($table == 'departments') {
		$type = 'department';
	} else if ($table == 'locations') {
		$type = 'location';
	} else if ($table == 'groups') {
		$type = 'group';
	} else if ($table == 'surveys') {
		$type = 'survey';
	} else if ($table == 'roles') {
		$type = 'role';
	} else {
		$type = $table;
	}

	$relation_data = getaxecuteQuery_fn("select * from relation_table where user_id = " . $_SESSION['user_id'] . " and table_name = '$type'");

	$arr_id = array();
	while ($row_get_relation_data = mysqli_fetch_assoc($relation_data)) {
		$arr_id[] = $row_get_relation_data['table_id'];
	}
	$table_ids = implode(',', $arr_id);
	$filter = '';
	/* if ($_SESSION['user_type'] <= 2) {
		$filter = '';
	} else {
		//for other user
		$filter = " and cby='" . $_SESSION['user_id'] . "' ";
		if ($table_ids) {
			$filter .= " OR id IN ($table_ids)";
		}
	} */
	if ($_SESSION['user_type'] < 2) {
		$filter = '';
	} else {
		//for other user
		if ($table_ids) {
			$filter .= " AND id IN ($table_ids)";
		} else {
			$filter .= " AND id IN (0)";
		}
	}

	$allowed_data = getaxecuteQuery_fn("select * from `$table` where id>0 and cstatus=1 $filter order by cdate desc");
	$arr = array();
	while ($row_get_data = mysqli_fetch_assoc($allowed_data)) {
		$arr[] = $row_get_data;
	}
	return $arr;
}

// get assign survey of user with details
function get_survey_data_by_user($survey_type, $confidential = 0)
{
	// get survey by user access
	if ($_SESSION['user_type'] < 2) {
		$filter = '';
	} else {
		$survey_id = get_assigned_user_data($_SESSION['user_id'], $survey_type);
		if ($survey_id) {
			$survey_id = implode(',', $survey_id);
			$filter = " and id IN ($survey_id)";
		} else {
			$filter = " and id IN (0)";
		}
	}
	// get survey type
	if ($survey_type == 'engagement') {
		$sFilter = " and survey_type = 3";
	} else if ($survey_type == 'pulse') {
		$sFilter = " and survey_type = 2";
	} else if ($survey_type == 'survey') {
		$sFilter = " and survey_type = 1";
	}

	//get unconfidential data
	if ($confidential == 1) {
		$sFilter .= " and confidential !=1";
	}
	$allowed_data = getaxecuteQuery_fn("select * from surveys where id>0 and cstatus=1 $filter $sFilter order by cdate desc");
	$arr = array();
	while ($row_get_data = mysqli_fetch_assoc($allowed_data)) {
		$arr[] = $row_get_data;
	}
	return $arr;
}

function get_user_datails($id = null)
{
	$user_data = getaxecuteQuery_fn("select * from manage_users  where id = $id ");
	$row_get_data = mysqli_fetch_assoc($user_data);
	return $row_get_data;
}
function get_assign_task_count_by_status($status_id, $surevy_ids = null, $group_ids = null, $department_ids = null, $loc_ids = null)
{
	$user_id   = $_SESSION['user_id'];
	$user_type = $_SESSION['user_type'];
	$queryFilter = '';
	if ($user_type > 2) {
		$queryFilter = " and assign_to_user_id = $user_id";
	}
	$array = array();
	$filter = '';
	if ($surevy_ids) {
		$filter .= " and surveyid IN ($surevy_ids)";
	} else {
		$filter .= " and surveyid IN (0)";
	}
	if ($status_id == 1) {
		// get assigned task id 
		$user_data = getaxecuteQuery_fn("SELECT * FROM assign_task where id !='' $queryFilter");
		while ($row_get_data = mysqli_fetch_assoc($user_data)) {
			$array[] = $row_get_data['task_id'];
		}
		$task_ids = implode(",", $array);
		if ($task_ids) {
			$filter .= " and cby NOT IN ($task_ids)";
		}
		$user_data = getaxecuteQuery_fn("SELECT * FROM answers where id !=0 $filter group by cby");
	} else {
		if ($surevy_ids) {
			$filter = " and survey_id IN ($surevy_ids)";
		}
		$user_data = getaxecuteQuery_fn("SELECT * FROM assign_task where task_status = $status_id $queryFilter $filter");
	}
	$row = mysqli_num_rows($user_data);
	return $row;
}
function get_admin_manager_of_survey($survey_id)
{
	$survey_data   = getaxecuteQuery_fn("SELECT * from surveys where id = $survey_id");
	$row 		 = mysqli_fetch_assoc($survey_data);
	$groupId     = $row['groups'];
	$user_array  = array();
	// get user related to survey (by their groups)
	$user_data = getaxecuteQuery_fn("SELECT * FROM `relation_table` WHERE `table_id` IN ($groupId) AND `table_name`= 'group' group by user_id");
	$s = 0;
	while ($row_user = mysqli_fetch_assoc($user_data)) {
		$user_details = get_user_datails($row_user['user_id']);
		if ($user_details) {
			$user_array[$s]['email']  	  = $user_details['email'];
			$user_array[$s]['name']   	  = $user_details['name'];
			$user_array[$s]['user_type']   = $user_details['user_type'];
		}
		$s++;
	}
	return $user_array;
}

function get_data_by_id($table, $id)
{
	$arr = array();
	$user_type = '';
	$query = '';
	if ($id) {
		$query = "where id IN($id)";
	}
	$data = getaxecuteQuery_fn("select id,name from `$table` $query order by name ASC");
	foreach ($data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}

function get_survey_detail($id)
{
	$survey_data = getaxecuteQuery_fn("select * from surveys  where id IN ($id)");
	$row_get_data = mysqli_fetch_assoc($survey_data);
	return $row_get_data;
}

function survey_score_calculation($surveyId, $cby)
{
	$total_result_val = 0;
	$achieved_result_val = 0;
	$to_bo_contacted     = 0;
	$count = 0;
	$get_survey_result = getaxecuteQuery_fn("SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='" . $surveyId . "' and cby=" . $cby);
	//record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$surveyId."' and cby=".$cby);
	while ($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)) {
		$get_question_type = getaxecuteQuery_fn("SELECT answer_type FROM questions where is_weighted=1 and id =" . $row_get_survey_result['questionid']);
		$result_question = mysqli_fetch_assoc($get_question_type);
		if ($result_question) {
			if (!in_array($result_question['answer_type'], array(2, 3, 5))) {
				$total_result_val = ($count + 1) * 100;
				$achieved_result_val += $row_get_survey_result['answerval'];
				$count++;
			}
		}
		if ($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100) {
			$to_bo_contacted = 1;
		}
	}
	$result_response = $achieved_result_val * 100 / $total_result_val;
	if ($achieved_result_val == 0 and $total_result_val == 0) {
		$result_response = 100;
	}
	return array(
		"survey_score" => $result_response,
		"to_bo_contacted" => $to_bo_contacted,
	);
}

function getThresholdPercentage()
{
	return array(
		25 => 25,
		50 => 50,
		75 => 75,
		100 => 100,
	);
}


// date 14-10-2022
// if contacted is yes then only mail will send to admin and super admin
// function send_mail_on_survey_respond_submitted(){
// }


function getDaily($startDate, $endDate)
{
	// echo $startDate . ' startDate <br>';
	// echo $endDate . ' endDate <br>';
	// echo 'getDaily <br>';

	$date_range_array = [];
	while ($startDate < $endDate) {
		$date_range_array[] = $startDate;
		$startDate = date('Y-m-d', strtotime("+1 day", strtotime($startDate)));
	}
	// array_pop($date_range_array);

	// echo '<pre>';
	// print_r($date_range_array);
	// echo '</pre>';
	return $date_range_array;
}

function getWeeklyDate($startDate, $endDate)
{
	// echo $startDate . ' startDate <br>';
	// echo $endDate . ' endDate <br>';
	// echo 'getWeeklyDate <br>';

	$date_range_array = [];

	while ($startDate < $endDate) {
		$date_range_array[] = $startDate;
		$startDate = date('Y-m-d', strtotime("+7 day", strtotime($startDate)));
	}
	$lastDate = $date_range_array[count($date_range_array) - 1];
	$endDate = date('Y-m-d', strtotime($endDate));
	$is_today_due_date = check_differenceDate($lastDate, $endDate, 'eq');

	if ($is_today_due_date != 1) {
		// $date_range_array[count($date_range_array)] = date('Y-m-d', strtotime("-1 day", strtotime($endDate)));
		$date_range_array[count($date_range_array)] = date('Y-m-d', strtotime($endDate));
	}

	// echo '<pre>';
	// print_r($date_range_array);
	// echo '</pre>';
	return $date_range_array;
}


function getMonthly($startDate, $endDate)
{
	// echo $startDate . ' startDate <br>';
	// echo $endDate . ' endDate <br>';
	// echo 'getMonthly <br>';

	$date_range_array = [];
	$endDate = date('Y-m-d', strtotime($endDate));

	while ($startDate < $endDate) {
		$date_range_array[] = $startDate;
		$startDate = date('Y-m-d', strtotime("+30 day", strtotime($startDate)));
	}

	$lastDate = $date_range_array[count($date_range_array) - 1];
	$endDate = date('Y-m-d', strtotime($endDate));
	$is_today_due_date = check_differenceDate($lastDate, $endDate, 'eq');
	if ($is_today_due_date != 1) {
		$date_range_array[count($date_range_array)] = $endDate;
	}

	// echo '<pre>';
	// print_r($date_range_array);
	// echo '</pre>';
	return $date_range_array;
}

function getQuarterly($startDate, $endDate)
{
	// echo $startDate . ' startDate <br>';
	// echo $endDate . ' endDate <br>';
	// echo 'getQuarterly <br>';

	$date_range_array = [];
	$endDate = date('Y-m-d', strtotime($endDate));
	while ($startDate < $endDate) {
		$date_range_array[] = $startDate;
		$startDate = date('Y-m-d', strtotime("+90 day", strtotime($startDate)));
	}

	$lastDate = $date_range_array[count($date_range_array) - 1];
	$endDate = date('Y-m-d', strtotime($endDate));
	$is_today_due_date = check_differenceDate($lastDate, $endDate, 'eq');
	if ($is_today_due_date != 1) {
		$date_range_array[count($date_range_array)] = $endDate;
	}

	// echo '<pre>';
	// print_r($date_range_array);
	// echo '</pre>';
	return $date_range_array;
}
