<?php 
//set logo
define('ADMIN_EMAIL', 'system@dgam.app');
define('MAIN_LOGO', 'upload_image/dgs-logo-2.jpg');
define('FOOTER_LOGO', 'image/dgs-logo-3.png');

require_once("connectin_config.php");
    if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
		define('BASE_URL', 'http://localhost/feedback_management/');
	}
	else{
		//define('BASE_URL', 'https://survey.datagroup.dev/');
			define('BASE_URL', 'https://demo.dgfm.app/');
	} 
//date_default_timezone_set('Asia/calcutta');
date_default_timezone_set('Europe/London');

//Get Home URL
function getHomeUrl(){
	return BASE_URL;
}
function baseUrl(){
	$config['base_url'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
	$config['base_url'] .= "://".$_SERVER['HTTP_HOST'];
	$config['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
	return $config['base_url'];
}
	// record set function 
	function record_set($name,$query,$debug=0) {
		$connection=get_connection();
		if($debug==1){
			echo $query; echo '<br>';
		}
		if($debug==2){
			echo $query; die();
		}	
		//global $customer;
		  global ${"query_$name"};
		  global ${"$name"};
		  global ${"row_$name"};
		  global ${"totalRows_$name"};
			${"query_$name"} = "$query";
			if($_REQUEST['debug']==1){
				echo ${"query_$name"};
			}
			${"$name"} = mysqli_query($connection,${"query_$name"}) or die(mysqli_error($error));
			${"totalRows_$name"} = mysqli_num_rows(${"$name"});
	}
	// record set function for single record
	function record_set_single($name,$query,$debug=0) {
		$connection=get_connection();
		if($debug==1){
			echo $query; echo '<br>';
		}
		if($debug==2){
			echo $query; die();
		}	
		${"$name"} = mysqli_query($connection,$query) or die(mysqli_error($error));
		$row_result = mysqli_fetch_assoc(${"$name"});
		return $row_result;
	}
	function record_set_test($name,$query){
		$connection=get_connection();	
		//global $customer;
		  global ${"query_$name"};
		  global ${"$name"};
		  global ${"row_$name"};
		  global ${"totalRows_$name"};
			echo ${"query_$name"} = "$query";
	}
// Insert Data
function dbRowInsert($table_name,$form_data,$die=0){
	$connection=get_connection();
	// retrieve the keys of the array (column titles)
	$fields = array_keys($form_data);
	// build the query
	$sql = "INSERT INTO ".$table_name."
	(`".implode('`,`', $fields)."`)
	VALUES('".implode("','", $form_data)."')";
	if($die==2){
		echo $sql; die();
	}	
	//echo $sql; //exit;
	// run and return the query result resource
	mysqli_query($connection,$sql);
	return mysqli_insert_id($connection);
}
//
//Delete Data
function dbRowDelete($table_name, $where_clause='',$die=0){
	$connection=get_connection();
	// check for optional where clause
	$whereSQL = '';
if(!empty($where_clause)){
	// check to see if the 'where' keyword exists
	if(substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE'){
	// not found, add keyword
		$whereSQL = " WHERE ".$where_clause;
	}else{
		$whereSQL = " ".trim($where_clause);
	}
}
	// build the query
	$sql = "DELETE FROM ".$table_name.$whereSQL;
	if($die == 2){
		echo $sql; die();
	}
	// run and return the query result resource
	return mysqli_query($connection,$sql);
}
//
//Update Data
function dbRowUpdate($table_name, $form_data, $where_clause='',$die=0){
	$connection=get_connection();
	//mysqli_close($connection);
	// check for optional where clause
	$whereSQL = '';
	if(!empty($where_clause)){
		// check to see if the 'where' keyword exists
		if(substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE'){
			// not found, add key word
			$whereSQL = " WHERE ".$where_clause;
		} else{
			$whereSQL = " ".trim($where_clause);
		}
	}
	// start the actual SQL statement
	$sql = "UPDATE ".$table_name." SET ";
	// loop and build the column /
	$sets = array();
	foreach($form_data as $column => $value){
		 $sets[] = "`".$column."` = '".$value."'";
	}
	$sql .= implode(', ', $sets);
	// append the where statement
	$sql .= $whereSQL;
	if($die==2){
		echo $sql; die();
	}
	if($die==1){
		echo $sql; echo '<br>';
	}
	//echo $sql;
	// run and return the query result
  return mysqli_query($connection,$sql);
}
function dbRowUpdate_test($table_name, $form_data, $where_clause=''){
	$whereSQL = '';
	if(!empty($where_clause)){
		if(substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE'){
			$whereSQL = " WHERE ".$where_clause;
		} else{
			$whereSQL = " ".trim($where_clause);
		}
	}
	 $sql = "UPDATE ".$table_name." SET ";
	$sets = array();
	foreach($form_data as $column => $value){
		 $sets[] = "`".$column."` = '".$value."'";
	}
	$sql .= implode(', ', $sets);
  $sql .= $whereSQL;
  echo $sql;
  exit;
}
	function check_user($table_name,$form_data){
		$connection=get_connection();
	    // retrieve the keys of the array (column titles)
	    $fields = array_keys($form_data);
	    // build the query
	    $sql = "SELECT id from ".$table_name." WHERE
		".implode('`,`', $fields)."
	    ='".implode("','", $form_data)."'";
		// run and return the query result resource
		if($result = mysqli_query($connection,$sql)){
				$row_cnt = mysqli_num_rows($result);
		}
	    //$count = mysqli_num_rows($qry);		
	    return $row_cnt;
	}	
function getrow_fn($sql){
	$connection=get_connection();
	// run and return the query result resource
	$qry =  mysqli_query($connection,$sql);
	return mysqli_fetch_array($qry,MYSQLI_ASSOC);
}
function getallrow_fn($sql){
	$connection=get_connection();
	// run and return the query result resource
	$qry =  mysqli_query($connection,$sql);
	return $qry;
}
	function login_fn($sql)	{
	$connection=get_connection();
	// run and return the query result resource
	if($result = mysqli_query($connection,$sql)){
		$row_cnt = mysqli_num_rows($result);
	}
	 return $row_cnt;
	}
	function getaxecuteQuery_fn($sql,$die=0){
		if($die==1){
			echo $sql; echo '<br>';
		}
		if($die==2){
			echo $sql; die();
		}
		$connection=get_connection();
	    // run  query result resource
		return $qry =  mysqli_query($connection,$sql);
	}
	function alertSuccess($mess =null,$url ="#"){ ?>
		<script>
			swal({
				title: "Success!",
				text: "<?=$mess?>",
				type: "success",
				showConfirmButton: true
			// }, function(isConfirm){
			// 	if (isConfirm){
			// 		window.location.href = "<?=$url?>";
			// 	} 
			}).then(function() { window.location.href = "<?=$url?>";});
		</script>
	<?php }
	function alertdanger($mess =null,$url ="#"){  ?>
		<script>
			swal({
				title: "Sorry!",
				text: "<?=$mess?>",
				type: "error",
				showConfirmButton: true
			// }, function(isConfirm){
			// 	if (isConfirm){
			// 		window.location.href = "<?=$url?>";
			// 		return;
			// 	} 
			}).then(function() { window.location.href = "<?=$url?>";});
		</script>
	<?php } ?>