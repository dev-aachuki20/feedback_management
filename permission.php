<?php

if($_SESSION['user_type']==1){
    $user_permission = array('add-department','manage-department','add-location','manage-locations','view-survey','view-statistics','add-user','view-user','manage-groups','add-group','import-user','add-user','monthly-report','report','pulses','create-report','view-template','view-leagues');
}
if($_SESSION['user_type']==2){
    $user_permission = array('add-department','manage-department','add-location','manage-locations','view-survey','view-statistics','add-user','view-user','manage-groups','add-group','import-user','add-user','monthly-report','report','pulses','create-report','view-template','view-leagues');
}
if($_SESSION['user_type']==3){
    $user_permission = array('add-department','manage-department','add-location','manage-locations','view-survey','view-statistics','add-user','view-user','manage-groups','add-group','import-user','add-user','monthly-report','report','pulses','view-leagues');
}
if($_SESSION['user_type']==4){
    $user_permission = array('view-statistics','report-statistics','view-report','monthly-report','report','pulses','view-leagues');
}


/* sidebar active permission */

$configuration = array('add-department','manage-department','add-location','manage-locations','add-group','manage-groups','view-survey','add-survey');

$surveysMenu = array('view-report','monthly-report','view-statistics','report-statistics','add-group','manage-groups','view-survey','view-analytics','survey-outcomes','view-leagues','about','contact-us','view-surveys');

$userMenu = array('view-users','add-user','import-users');

$reportMenu = array('create-report','view-template','report','manage-report-template', 'list-report-templates');
$pulsesMenu = array('pulses','about','contact-us','view-pulses');
?>