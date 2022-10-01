<?php 
if($_SESSION['user_type']==1){
    $rePage = 'sadmin-login.php'; 
}else if($_SESSION['user_type']==2){
    $rePage = 'admin-login.php'; 
}else {
    $rePage = 'login.php'; 
}
session_destroy();
reDirect("$rePage");
?> 