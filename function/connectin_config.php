<?php @session_start(); //echo 'connectin_config.php';
error_reporting(1);
$whitelist = array(
    '127.0.0.1',
    '::1'
);
    $server ='localhost';  
if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){ 
	$user ='root';          
	$password ='';              
	$database ='dgfm_demo';
}else{ 
	$user ='dgfm_demo';          
	$password ='KH7x.p2YSENX';              
	$database ='dgfm_demo';
}
 
define ('DB_HOST', $server);
define ('DB_USER', $user);
define ('DB_PASSWORD', $password);
define ('DB_NAME', $database);

function get_connection(){ 
	$connection=mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	// Check connection
	if (mysqli_connect_errno())
	{
		echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
	}else
	{   
		return $connection;	
	}
}
?>