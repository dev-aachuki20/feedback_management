<?php 

function getDepartment(){
	$arr = array();
	$departments_data = getaxecuteQuery_fn("select id,name from departments  where cstatus=1 order by name ASC ");
	foreach ($departments_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getLocation(){
	$arr = array();
	$locations_data = getaxecuteQuery_fn("select id,name from locations  where cstatus=1 order by name ASC");
	foreach ($locations_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getGroup(){
	$arr = array();
	$groups_data = getaxecuteQuery_fn("select id,name from groups  where cstatus=1 order by name ASC");
	foreach ($groups_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getSurvey(){
	$arr = array();
	$survey_data = getaxecuteQuery_fn("select id,name from surveys  where cstatus=1 order by name ASC");
	foreach ($survey_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getAdmin(){
	$arr = array();
	$admin_data = getaxecuteQuery_fn("select id,name from admin  where cstatus=1 order by name ASC");
	foreach ($admin_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getClient(){
	$arr = array();
	$clients_data = getaxecuteQuery_fn("select id,name from clients  where cstatus=1 order by name ASC");
	foreach ($clients_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
 function get_allowed_data($table,$user_id){
	$arr = array();
	
	if($_SESSION['user_type']==1){
		$allowed_data = getaxecuteQuery_fn("select id,name from $table  order by name ASC ");
	}
	else if($_SESSION['user_type']==2){
		$allowed_data = getaxecuteQuery_fn("select id,name from $table  WHERE `admin_ids` LIKE '|$user_id|' order by name ASC ");
	}else{
		$allowed_data = getaxecuteQuery_fn("select id,name from $table  WHERE `client_ids` LIKE '|$user_id|' order by name ASC ");
	}
	foreach ($allowed_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
 }
?>