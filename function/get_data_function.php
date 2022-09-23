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
function getAdmin($id=null){
	$arr = array();
	$filter ='';
	if($id !=null){
		$filter  = " and id IN ($id)";
	}
	$admin_data = getaxecuteQuery_fn("select id,name from admin  where cstatus=1 $filter order by name ASC");
	foreach ($admin_data as $val) {
		$arr[$val['id']] = $val['name'];
	}
	return $arr;
}
function getClient($id=null){
	$arr = array();
	$filter ='';
	if($id !=null){
		$filter  = " and id IN ($id)";
	}
	$clients_data = getaxecuteQuery_fn("select id,name from clients  where cstatus=1 $filter order by name ASC");
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
 function get_filter_data_by_user($table){
	if($_SESSION['user_type']==1){
		$filter = '';
	  }else if($_SESSION['user_type']==2){
		//for admin
		$filter = " and ((cby='".$_SESSION['user_id']."' and user_type='".$_SESSION['user_type']."') OR (`admin_ids` LIKE '%|".$_SESSION['user_id']."|%')) ";
	  }else if($_SESSION['user_type']==3){
		 //for manager
		$filter = " and ((cby='".$_SESSION['user_id']."' and user_type='".$_SESSION['user_type']."')  OR (`client_ids` LIKE '%|".$_SESSION['user_id']."|%') )";
	  }
	  $allowed_data = getaxecuteQuery_fn("select * from $table where id>0 and cstatus=1 $filter order by cdate desc");
	  $arr =array();
	  while($row_get_data=mysqli_fetch_assoc($allowed_data)){
	  	$arr[] =$row_get_data;
	  }
	return $arr;
 }

 function get_user_datails($id=null,$type=null){
	 if($type == 2){
		$user_data = getaxecuteQuery_fn("select * from admin  where id = $id ");
	 }else if($type == 3){
		$user_data = getaxecuteQuery_fn("select * from clients  where id = $id");
	 }else {
		$user_data = getaxecuteQuery_fn("select * from super_admin  where id = $id" );
	 }
	 $row_get_data=mysqli_fetch_assoc($user_data);

	return $row_get_data;
}
function get_assign_task_count_by_status($status_id){
	$user_id   = $_SESSION['user_id'];
	$user_type = $_SESSION['user_type'];
	$array = array();
	if($status_id == 1){
		$user_data = getaxecuteQuery_fn("SELECT * FROM assign_task where assign_to_user_id = $user_id and assign_to_user_type =$user_type");
		while($row_get_data=mysqli_fetch_assoc($user_data)){
			$array[] =$row_get_data['task_id'];
		}
		$task_ids = implode(",",$array );
		$user_data = getaxecuteQuery_fn("SELECT * FROM answers where cby NOT IN ($task_ids) group by cby" );
	}else {
		$user_data = getaxecuteQuery_fn("SELECT * FROM assign_task where assign_to_user_id = $user_id and assign_to_user_type =$user_type and task_status = $status_id");
	}
	$row = mysqli_num_rows ( $user_data );
	return $row;
}
?>