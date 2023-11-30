<?php
$filtr = '';
$assignFilter ='';
$surveyId = $_POST['surveys'];
if($_SESSION['user_type']>2){
    if(!empty($_POST['surveys'])){
        $filtr = " and survey_id IN ($surveyId)";
    }else{
        if($surveys_ids){
            $filtr = " and survey_id IN ($surveys_ids)";
        }else {
            $filtr = " and survey_id IN (0)";
        }
    }
}else{
    if(!empty($_POST['surveys'])){
        $filtr = " and survey_id IN ($surveyId)";
    }else{
        if($surveys_ids){
            $filtr = " and survey_id IN ($surveys_ids)";
        }
    }
}


/* ---un assigned ----*/
record_set("get_un_assigned", "SELECT * FROM assign_task WHERE id!=0 $filtr ");
record_set("get_total_survey_data","SELECT * FROM answers where id !=0  and surveyid IN ($surveys_ids) GROUP by cby");

/* ---assigned ----*/
record_set("get_assigned", "SELECT * FROM assign_task WHERE task_status = 2 $filtr ");

/* ---in progress ----*/
record_set("get_in_progress", "SELECT * FROM assign_task WHERE task_status = 3 $filtr ");

/* ---in void ----*/
record_set("get_in_void", "SELECT * FROM assign_task WHERE task_status = 4 $filtr ");

/* ---in resolved negative ----*/
record_set("get_resolved_negative", "SELECT * FROM assign_task WHERE task_status = 6 $filtr");

/* ---in resolved positive ----*/
record_set("get_resolved_positive", "SELECT * FROM assign_task WHERE task_status = 5 $filtr");
?>
<div class="row">
    <!-- Dashboard Counter -->
    <a class="" href="index.php?page=survey-manage&type=<?=$_GET['type']?>&req=unassigned&task_status=1" target="_blank"> 
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fa-solid fa-bars"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">UNASSIGNED</span>
                    <span class="info-box-number">
                        <?=$totalRows_get_total_survey_data - $totalRows_get_un_assigned?>
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </a> 

    <a class="" href="index.php?page=survey-manage&type=<?=$_GET['type']?>&req=assigned&task_status=2" target="_blank"> 
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-orange"><i class="fa-solid fa-list-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">ASSIGNED</span>
                    <span class="info-box-number"><?=$totalRows_get_assigned?></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </a>
    <a class="" href="index.php?page=survey-manage&type=<?=$_GET['type']?>&req=in progress&task_status=3" target="_blank">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa-solid fa-spinner"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">IN PROGRESS</span>
                    <span class="info-box-number"><?=$totalRows_get_in_progress?></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </a>
    <a class="" href="index.php?page=survey-manage&type=<?=$_GET['type']?>&req=void&task_status=4" target="_blank">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-gray"><i class="fa-solid fa-trash"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">VOID</span>
                    
                    <span class="info-box-number"><?=$totalRows_get_in_void?></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </a>
    <a class="" href="index.php?page=survey-manage&type=<?=$_GET['type']?>&req=resolved negative&task_status=6" target="_blank">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa-solid fa-circle-xmark"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">RESOLVED NEGATIVE</span>
                    
                    <span class="info-box-number"><?=$totalRows_get_resolved_negative?></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </a> 
    <a class="" href="index.php?page=survey-manage&type=<?=$_GET['type']?>&req=resolved postive&task_status=5" target="_blank">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa-solid fa-circle-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">RESOLVED POSITIVE</span>
                    <span class="info-box-number"><?=$totalRows_get_resolved_positive?></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </a>
</div>