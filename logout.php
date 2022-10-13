<?php 
if($_SESSION['user_type']==1){
    $rePage = 'login.php'; 
}else if($_SESSION['user_type']==2){
    $rePage = 'login.php'; 
}else {
    $rePage = 'login.php'; 
}
session_destroy();
reDirect("$rePage");
?> 