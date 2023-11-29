<?php 
/**  --------------show count on top start-------------------- */
$filtr = '';
$assignFilter ='';
$surveyId = $_POST['surveys'];
if($_SESSION['user_type']>2){
    if(!empty($_POST['surveys'])){
        $filtr = " and surveyid IN ($surveyId)";
    }else{
        if($surveys_ids){
            $filtr = " and surveyid IN ($surveys_ids)";
        }else {
            $filtr = " and surveyid IN (0)";
        }
    }
}else{
    if(!empty($_POST['surveys'])){
        $filtr = " and surveyid IN ($surveyId)";
    }else{
        if($surveys_ids){
            $filtr = " and surveyid IN ($surveys_ids)";
        }
    }
}
$assignFilter = str_replace('surveyid','survey_id',$filtr);

/* ---contact request ----*/
record_set("get_contact_request", "SELECT * FROM answers WHERE answerid=-2 AND answerval = 100 $filtr GROUP BY cby");

/* ---in progress ----*/
record_set("get_in_progress", "SELECT * FROM assign_task WHERE task_status = 3 $assignFilter ");

/* ---in void ----*/
record_set("get_in_void", "SELECT * FROM assign_task WHERE task_status = 4 $assignFilter ");

/* ---in resolved ----*/
record_set("get_resolved", "SELECT * FROM assign_task WHERE task_status IN (5,6) $assignFilter");

/** --------------show count on top end-------------------- */

?>

<div class="row" >
    <!-- Dashboard Counter -->
    <a class="" href="index.php?page=survey-manage&type=<?=$page_type?>&req=contact requests&aid=-2&avl=10"> 
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa-solid fa-image-portrait"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Contact Requests</span>
                    <span class="info-box-number">
                        <?=$totalRows_get_contact_request?>
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </a> 

    <a class="" href="index.php?page=survey-manage&type=<?=$page_type?>&req=in progress&task_status=3" > 
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa-solid fa-spinner"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">In Progress</span>
                    
                    <span class="info-box-number"><?=$totalRows_get_in_progress?></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </a>
    <a class="" href="index.php?page=survey-manage&type=<?=$page_type?>&req=void&task_status=4" >
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-gray"><i class="fa-solid fa-trash"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Void</span>
                    <span class="info-box-number"><?=$totalRows_get_in_void?></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </a>
    <a class="" href="index.php?page=survey-manage&type=<?=$page_type?>&req=resolved postive&task_status=5" >
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa-solid fa-circle-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Resolved</span>
                    <span class="info-box-number"><?=$totalRows_get_resolved?></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </a>  
</div>