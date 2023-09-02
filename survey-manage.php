<style>
.col-md-3 {
    height: 80px;
}
</style>
<?php 
    $assigned = get_assign_task_count_by_status(2);
    $page_type = $_GET['type'];
    // get data by user
    $departmentByUsers = get_filter_data_by_user('departments');
    $locationByUsers   = get_filter_data_by_user('locations');
    $groupByUsers      = get_filter_data_by_user('groups');
    $surveyByUsers     = get_survey_data_by_user($_GET['type'],1);
    $sid               = $_GET['id'];

    $assign_survey = array();
    foreach($surveyByUsers as $survey){
        $assign_survey[] = $survey['id'];
    }
    $surveys_ids = implode(',',$assign_survey);
  // assign task to user
  if(isset($_POST['assign'])){
    $survey_id           = $_POST['survey_id_hidden'];
    $task_id             = explode(',',$_POST['response_id_hidden']);
    $assing_to_user_id   = $_POST['assing_to_user_id'];
    $assign_by_user_type = $_SESSION['user_type'];
    $assign_by_user_id   = $_SESSION['user_id'];
    foreach($task_id as $tasks){
        $data = array(
            "assign_to_user_id"   => $assing_to_user_id,
            "task_id"             => $tasks,
            "survey_id"           => $survey_id,
            "survey_type"         => $survey_type_id,
            "task_status"         => 2,
            "assign_by_user_id"   => $assign_by_user_id,
            "cdate"               => date("Y-m-d H:i:s")
        );

         // check the assign task already exists for this user or not
         record_set("check_assign_task", "SELECT * FROM assign_task where assign_to_user_id = $assing_to_user_id and task_id = $tasks and survey_id = $survey_id");
         $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);
         if($totalRows_check_assign_task > 0 ){
             $insert_value=	dbRowUpdate("assign_task", $data, "where id=".$row_check_assign_task['id']);
         }else {
             $insert_value =  dbRowInsert("assign_task",$data);
         }
        $userdata   = get_user_datails($assing_to_user_id);

        $user_email = $userdata['email'];
        $user_name  = $userdata['name'];  
           
        $data_contact_action = array(
            "user_id"=> $tasks,
            "action"=> 2,
            "cby" =>$assing_to_user_id,
            "comment"=> 'response assigned to '.$user_name,
            'created_date'=>date("Y-m-d H:i:s")
        );
        $insert_contact_action =  dbRowInsert("survey_contact_action",$data_contact_action);
    }
    // send mail to user assigned task
    send_email_to_assign_user($user_name,$user_email);

    if(!empty($insert_value )){	
        $msg = "Task Assigned Successfully";
        alertSuccess( $msg,'?page=view-report&type='.$_GET['type']);
        die();
    }
        $msg = "Task Not Assigned";
        alertdanger( $msg,'?page=view-report&type='.$_GET['type']);
}
    //self assign task
    if(isset($_POST['self_assign_hidden']) and !empty($_POST['self_assign_hidden'])){
        $survey_id           = $_POST['survey_id_hidden'];
        $task_id             = explode(',',$_POST['response_id_hidden']);
        $assing_to_user_id   = $_SESSION['user_id'];

        foreach($task_id as $tasks){
            $data = array(
                "assign_to_user_id"   => $assing_to_user_id,
                "task_id"             => $tasks,
                "survey_id"           => $survey_id,
                "survey_type"         => $survey_type_id,
                "task_status"         => 2,
                "assign_by_user_id"   => $assing_to_user_id,
                "cdate"               => date("Y-m-d H:i:s")
            );
            // check the assign task already exists for this user or not
            record_set("check_assign_task", "SELECT * FROM assign_task where assign_to_user_id = $assing_to_user_id  and task_id = $tasks and survey_id = $survey_id");
            $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);
            
            if($totalRows_check_assign_task > 0 ){
                $insert_value = dbRowUpdate("assign_task", $data, "where id=".$row_check_assign_task['id']);
            }else {
                $insert_value = dbRowInsert("assign_task",$data);
            }

            $data_contact_action = array(
                "user_id"=> $tasks,
                "action"=> 2,
                "cby" =>$assing_to_user_id,
                "comment"=> 'response assigned to '.$_SESSION['user_name'],
                'created_date'=>date("Y-m-d H:i:s")
            );
            $insert_contact_action =  dbRowInsert("survey_contact_action",$data_contact_action);
        }
        if(!empty($insert_value )){	
            $msg = "Task Assigned Successfully";
            alertSuccess( $msg,'?page=view-my-assign-task&type='.$_GET['type']);
            die();
        }
            $msg = "Task Not Assigned";
            alertdanger( $msg,'?page=survey-manage&type=survey&req=contact requests');
    }

    // end assign 

    $query = 'SELECT * FROM answers where id !=0';

    /**-----------------filter record start-----------*/

    if(!empty($_POST['locationid'])){
        if($_POST['locationid'] == 4){
            $query .= " and locationid in (select id from locations where cstatus=1)";  
        }else{
            $query .= " and locationid = '".$_POST['locationid']."'";
        }
    }
    
    if(!empty($_POST['departmentid'])){
        if($_POST['departmentid'] == 4){
            record_set("get_all_department","select id from departments where cstatus=1");	
            $all_departments = array();
            while($row_get_all_department = mysqli_fetch_assoc($get_all_department)){
                $all_departments[] = $row_get_all_department['id'];
            }
            $query .= " and departmentid in (".implode(',',$all_departments).")";
        }else{
            $query .= " and departmentid = '".$_POST['departmentid']."'";
        }
    }

    if(!empty($_POST['roleid'])){
        echo $_POST['roleid'].':::::';
        if($_POST['roleid'] == 4){
            record_set("get_all_role","select id from roles where cstatus=1");	
            $all_roles = array();
            while($row_get_all_role = mysqli_fetch_assoc($get_all_role)){
                $all_roles[] = $row_get_all_role['id'];
            }
            $query .= " and roleid in (".implode(',',$all_roles).")";
        }else{
            $query .= " and roleid = '".$_POST['roleid']."'";
        }
    }
    
    if(!empty($_POST['fdate']) && !empty($_POST['sdate'])){  
        $query .= " and cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
    }

    /**-----------------filter record end-------------*/
    $assign_survey = array();
    foreach($surveyByUsers as $survey){
        $assign_survey[] = $survey['id'];
    }
    $surveys_ids = implode(',',$assign_survey);

    if($loggedIn_user_type > 2){
        if($assign_survey){
            $query .= " and surveyid IN (".$surveys_ids.")";
        }else {
            $query .= " and surveyid IN (0)";
        }
    }

    $filter_status = '';
    if(!empty($_GET['task_status']) and $_GET['task_status']!=1){
        $filter_status = ' and task_status ='.$_GET['task_status'];
    }
    $qFilter = '';
    if($_SESSION['user_type'] >2){
        $qFilter = " and assign_to_user_id = $loggedIn_user_id ";
        // if($ids){
        //     $qFilter = "and id IN (".implode(',',$ids).")"; 
        // }else {
        //     $qFilter = "and id IN (0)"; 
        // }
    }
    record_set("get_assign_task", "SELECT * FROM assign_task where id !='' $qFilter ".$filter_status);

    $arr_task_id = array();
    while($row_get_assign_task = mysqli_fetch_assoc($get_assign_task)){
        $arr_task_id[] = $row_get_assign_task['task_id'];
    }
    $task_id = implode(",",$arr_task_id);
    if(empty($task_id)){
        $task_id = '0';
    }
    // if($_SESSION['user_type']<3){
    //     $survey_ids = get_survey_data_by_user($_GET['type']);
    //     // get only ids
    //     $ids = array_column($survey_ids , 'id');
    //     $query .= " and surveyid IN (".implode(',',$ids).")";
    // }

    if(!empty($_POST['surveys'])){
        $query .= " and surveyid =".$_POST['surveys'];
        if($_GET['task_status']==1){
            $query .= " and cby NOT IN (".$task_id.") GROUP by cby";
        }else if($_GET['req']=='contact requests'){
            $query .= " GROUP by cby";
        }else {
            $query .= " and cby IN (".$task_id.") GROUP by cby";
        }
        record_set("get_recent_entry",$query);
    }

  
?>
<style>
.d-none{
    display: none !important;
}
</style>
<section class="content-header">
  <h1><?=strtoupper($_GET['req'])?></h1>
</section>
<section class="content">
    <!-- top box container start-->
    <!-- top box container start-->
    <div class="row" >
        <!-- Dashboard Counter -->
        <a class="" href="index.php?page=survey-manage&type=<?=$page_type?>&req=contact requests&aid=-2&avl=10"> 
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa-solid fa-image-portrait"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Contact Requests</span>
                        <span class="info-box-number">
                            <?php 
                                $reqCount =0; 
                                $filtr = '';
                                //if($_SESSION['user_type']>2){
                                    if($surveys_ids){
                                        $filtr = " and surveyid IN ($surveys_ids)";
                                    }else {
                                        $filtr = " and surveyid IN (0)";
                                    }
                                //}
                                record_set("get_contact_request", "SELECT * FROM answers WHERE answerid=-2 AND answerval = 100 $locationQueryAndCondition $filtr GROUP BY cby");
                                while($row_get_contact_request = mysqli_fetch_assoc($get_contact_request)){
                                    // record_set("get_action", "select * from survey_contact_action where user_id=".$row_get_contact_request['cby']."");
                                    // if($totalRows_get_action == 0){
                                    //      ++$reqCount;
                                    // }
                                    ++$reqCount;
                                }
                            ?>
                            <?php 
                                echo $reqCount;
                            ?>
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
                        
                        <span class="info-box-number"><?=(get_assign_task_count_by_status(3,$surveys_ids,$dep_ids,$grp_ids,$loc_ids) ? get_assign_task_count_by_status(3,$surveys_ids,$dep_ids,$grp_ids,$loc_ids):0)?></span>
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
                        <span class="info-box-number"><?=(get_assign_task_count_by_status(4,$surveys_ids,$dep_ids,$grp_ids,$loc_ids)?get_assign_task_count_by_status(4,$surveys_ids,$dep_ids,$grp_ids,$loc_ids):0)?></span>
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
                        <span class="info-box-number"><?=(get_assign_task_count_by_status(5,$surveys_ids,$dep_ids,$grp_ids,$loc_ids)?get_assign_task_count_by_status(5,$surveys_ids,$dep_ids,$grp_ids,$loc_ids):0)?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
        </a>  
    </div>
    <!-- top box container start-->
    <div class="box box-default" style="margin-top:25px;">
        <form action="" method="POST" id="viewReportcsv">
            <!-- <input type="hidden" name="post_values" value =<?=json_encode($_POST)?> > -->
            <div class="box-header">
                <i class="fa fa-search" aria-hidden="true"></i>
                <h3 class="box-title">Search</h3>
            </div>
            
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="fdate" class="form-control start_data" value="<?=(!empty($_POST['fdate'])) ? date('Y-m-d', strtotime($_POST['fdate'])) : "" ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="sdate" class="form-control end_date" value="<?=(!empty($_POST['sdate'])) ? date('Y-m-d', strtotime($_POST['sdate'])): ''?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?=($_GET['type']) ? ucfirst($_GET['type']) : 'Survey'?></label>
                            <select id="surveys" name="surveys" class="form-control surveys singleSelect2">
                                <option value="">Select Survey</option>
                                <?php
                                foreach($surveyByUsers as $row_get_surveys){ ?>
                                    <option value="<?php echo $row_get_surveys['id'];?>" <?=($row_get_surveys['id'] == $_POST['surveys']) ? 'selected':''?>><?php echo $row_get_surveys['name'];?></option>
                                <?php } ?>
                            </select>
                            <label for="" class="error" style="display:none ;"> This field is required</label>
                        </div>
                    </div>
                    <!-- filter by group -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Group</label>
                            <select name="groupid" id="groupid" class="form-control form-control-lg group singleSelect2">
                                <option value="">Select Group</option>
                                <?php foreach($groupByUsers as $groupData){ 
                                    $groupId    = $groupData['id'];
                                    $groupName  = $groupData['name']; ?>
                                    <option value="<?php echo $groupId;?>"><?php echo $groupName;?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Location</label>
                            <select name="locationid" id="locationid" class="form-control form-control-lg locationid singleSelect2">
                                <option value="">Select Location</option>
                                <?php
                                    // record_set("get_location", "select * from locations where cstatus=1 $locationDropDownCondition order by name asc");        
                                    // while($row_get_location = mysqli_fetch_assoc($get_location)){ 
                                    foreach($locationByUsers as $locationData){ 
                                    $locationId     = $locationData['id'];
                                    $locationName   = $locationData['name'];?>
                                    <option value="<?php echo $locationId;?>" <?=($locationId == $_POST['locationid'])?'selected':''?>><?php echo $locationName;?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Department</label>
                            <select name="departmentid" id="departmentid" class="form-control form-control-lg department singleSelect2">
                                <option value="">Select Department</option>
                                <?php
                                    // record_set("get_department", "select * from departments where cstatus=1");        
                                    // while($row_get_department = mysqli_fetch_assoc($get_department)){ 
                                    foreach($departmentByUsers as $departmentData){ 
                                    $departmentId     = $departmentData['id'];
                                    $departmentName   = $departmentData['name'];?>
                                    <option value="<?php echo $departmentId;?>" <?=($departmentId == $_POST['departmentid'])?'selected':''?>><?php echo $departmentName;?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Role</label>
                            <select name="roleid" id="roleid" class="form-control form-control-lg role singleSelect2">
                            <option value=''> Select Role</option>
                                <?php 
                                 record_set("get_all_role","select id,name from roles where cstatus=1");	
                                 $all_roles = array();
                                 while($row_get_all_role = mysqli_fetch_assoc($get_all_role)){ ?>
                                    <option value="<?=$row_get_all_role['id']?>" <?=($row_get_all_role['id'] == $_POST['roleid'])?'selected':''?>><?=$row_get_all_role['name']?></option>
                                 <?php }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <input type="submit" style="background-color: #00a65a !important;border-color: #008d4c;"name="filter" class="btn btn-success btn-block search" value="Search"/>
                        </div>
                    </div>
                    <!-- <div class="col-md-3">
                        <label>&nbsp;</label>
                        <input type="reset" style="background-color: #00a65a !important;border-color: #008d4c;" class="btn btn-primary btn-block reset" value="Reset"/>
                    </div> -->
                </div>
            </div>
            <!-- <div>
                <button type="button" class="btn btn-success" id="exportascsv" style="margin-bottom: 20px;">Export CSV</button>
            </div> -->
        </form>
    </div>
    <div class="row">
        <div class="col-lg-12" id="dataforpdf">
            <div class="box">
                <div class="box-header"></div>
                    <div class="box-body">
                        <div class="col-md-12">
                            <div class="col-md-3" style="text-align: left;padding: 0;margin: 5px;">
                                <a href="?page=survey-outcomes&type=<?=$_GET['type']?>">
                                <button type="button" class="btn btn-success"  style="background-color: #00a65a !important;border-color: #008d4c;">Back</button>
                                </a>
                        
                            </div>
                        </div>
                        <div class="table-responsive col-md-12">
                            <table id="datatable" class="table table-bordered table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <?=($_GET['req']== 'contact requests') ? '<th></th>':''?>
                                        <th>DATE</th>
                                        <th>SURVEY NAME</th>
                                        <th>GROUP</th>
                                        <th>LOCATION</th>
                                        <th>DEPARTMENT</th>
                                        <th>RESPONDENT NUMBER</th>
                                        <th>RESULT</th>
                                        <!-- <th>Contacted</th> -->
                                        <th>Status</th>
                                        <th class="notforpdf">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    while($row_get_recent_entry = mysqli_fetch_assoc($get_recent_entry)){
                                        record_set("get_survey_detail", "SELECT * FROM surveys where id='".$row_get_recent_entry['surveyid']."'");	
                                        $row_get_survey_detail = mysqli_fetch_assoc($get_survey_detail);
                                        $row_survey_entry = 1;
                                        record_set("survey_entry", "SELECT DISTINCT cby FROM answers where surveyid='".$row_get_survey_detail['id']."' and cby <".$row_get_recent_entry['cby']);
                                        
                                        $row_survey_entry = $totalRows_survey_entry+$row_survey_entry;
                                        
                                        $total_result_val=0;
                                            record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_get_recent_entry['surveyid']."' and cby='".$row_get_recent_entry['cby']."'");
                                            $achieved_result_val = 0;
                                            $to_bo_contacted     = 0;
                                            $i=0;
                                            while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
                                                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_survey_result['questionid']);
                                                    if($result_question){
                                                        if(!in_array($result_question['answer_type'],array(2,3,5))){
                                                            $total_result_val = ($i+1)*100;
                                                            $achieved_result_val += $row_get_survey_result['answerval'];
                                                            $i++;
                                                        }
                                                    }
                                                    if($row_get_survey_result['answerid'] == -2 &&  $row_get_survey_result['answerval'] == 100){
                                                        $to_bo_contacted = 1;
                                                    }
                                            }
                                            // for showing only contacted yes data
                                            if($to_bo_contacted == 0){
                                                continue;
                                            }
                                            $result_response = $achieved_result_val*100/$total_result_val;

                                            if($achieved_result_val==0 and $total_result_val==0){
                                                $result_response=100;
                                            }
                                            $label_class = 'success';
                                            if($result_response<50){
                                                $label_class = 'danger';
                                            }else 
                                            if($result_response<75){
                                                $label_class = 'info';
                                            }
                                            if($to_bo_contacted==1){ 
                                                $contacted='<a class="btn btn-xs btn-success">Yes</a>';
                                            }else{ 
                                                //show only contacted us data 
                                                if($_GET['req'] == 'contact request'){
                                                    continue;
                                                }
                                                // end 
                                                $contacted ='<a class="btn btn-xs btn-info">No</a>';
                                            } 
                                        
                                            // get taskstatus
                                            $fData  = '';
                                            if($_SESSION['user_type']>2){
                                                $fData = " and assign_to_user_id = ".$_SESSION['user_id'];
                                            }
                                            if(isset($_GET['task_status'])){
                                                $fData .= " and task_status =".$_GET['task_status'];
                                            }
                                            record_set("check_assign_task", "SELECT * FROM assign_task where task_id = ".$row_get_recent_entry['cby']."$fData");

                                            $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);
                                            $task_status = $row_check_assign_task['task_status'];
                                            if($_GET['task_status']==1 || empty($task_status)){
                                                $task_status = 1;
                                            }
                                        ?>
                                        <tr>
                                            <?php
                                            if(($_GET['req'] == 'contact requests')){ ?>
                                                <td><input type="checkbox" name="assign" value="<?=$row_get_recent_entry['cby'] ?>" class="assignSurveyCheckbox" task-type="" data-sid="<?=$row_get_recent_entry['surveyid']?>"></td>
                                            <?php }
                                            ?>
                                            <td><?=date("d-m-Y", strtotime($row_get_recent_entry['cdate']))?></td>

                                            <td><?=getSurvey()[$row_get_recent_entry['surveyid']]?></td>
                                            <td><?=getGroup()[$row_get_recent_entry['groupid']]?></td>
                                            <td><?=getLocation()[$row_get_recent_entry['locationid']]?></td>
                                            <td><?=getDepartment()[$row_get_recent_entry['departmentid']]?></td>
                                            
                                            <td><?=$row_survey_entry?></td>
                                            <td><label class="label label-<?=$label_class?>"><?=round($result_response,2)?> %</label></td>
                                            <!-- <td><?=$contacted ?></td> -->
                                            <td><a class="btn btn-xs btn-success"><?=assign_task_status()[$task_status]?></a></td>
                                            <td><a class="btn btn-xs btn-primary" href="survey-result.php?surveyid=<?=$row_get_recent_entry['surveyid']?>&userid=<?=$row_get_recent_entry['cby']?><?=($_GET['task_status']!=1)?'&status=assign':''?>" target="_blank">VIEW DETAILS</a></td>
                                        </tr> 
                                    <?php }
                                    ?>
                                </tbody>  
                                <?php if($_SESSION['user_type'] != 4 && $_GET['req'] == 'contact requests'){ ?>
                                <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <!-- <th></th> -->
                                    <th> </th>
                                    <th></th>
                                    <th></th>
                                    <th style="text-align: right;">
                                            <button type="button" class="btn btn-primary self-assign-btn" style="display: none; <?=$display?>" name="self_assign">Self Assign</button>
                                        </th>
                                        <th class="notforpdf">
                                            <button type="button" class="btn btn-primary btn-submit" data-toggle="modal" value="" data-target="#exampleModalCenter" style="display: none; <?=$display?>">
                                            Assign
                                            </button>
                                        </th>
                                </tr>
                                
                                </tfoot>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
</section>
<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Assign Task</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <form method="post" id="assign_form">
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="hidden" name="self_assign_hidden" value="" id="set_self_assign">
                        <input type="hidden" class="survey_id_hidden" name="survey_id_hidden" value="">
                        <input type="hidden" class="response_id_hidden" name="response_id_hidden" value="">
                        <label>User Type</label>
                        <select class="form-control " tabindex=7 id="user_type" name="user_type">
                            <option value="">Select User Type</option>
                        <?php 
                            $user_types_array=user_type();  
                            foreach($user_types_array as $key => $value){
                            // if($_SESSION['user_type']==3){
                            //     $allowed_key=3;
                            // } 
                            if($key == 1){ continue; }
                            ?>
                            <option <?php if($type==$key){?> selected="selected"<?php  }?> value="<?php echo $key; ?>"> <?php echo $value; ?>
                            </option>
                            <?php 
                            }
                        
                        ?>
                        </select>
                    </div>
                </div>
                <!-- select admin -->
                <div class="col-md-12" id="users">
                </div>                                    
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary submit_task" name="assign">Save changes</button>
            </div>
        </form>
    </div>
  </div>
</div>
<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>
<!-- Resources -->

<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-html2canvas@latest/dist/jspdf-html2canvas.min.js"></script> 

<script>

$(document).on('change','.assignSurveyCheckbox',function(){
    var value = $(this).is(':checked');
    let sid  = $(this).data('sid');
    var checkedArray=[];
    $("input[name='assign']:checked").each(function(){
        checkedArray.push($(this).val());
    });
    $('.survey_id_hidden').val(sid);
    $('.response_id_hidden').val(checkedArray);
   

    if(checkedArray.length >0){
        $('.btn-submit').show();
       $('.self-assign-btn').show();
    }else{
        $('.btn-submit').hide();
        $('.self-assign-btn').hide();
    }
});

function assign_user(survey_id,user_type){
    $.ajax({
        method:"POST",
        url:'<?=baseUrl()?>ajax/common_file.php',
        data:{
            survey_id:survey_id,
            user_type:user_type,
            mode:'assign_users'
        },
        success:function(response){
            response = JSON.parse(response);
            console.log(response);
            $('#users').html(response);
        }
    })
}
// ajax on the user type change in assign task
$(document).on('change','#user_type',function(){
    let user_type = $(this).val();
    let survey_id  = $('.survey_id_hidden').val();
    assign_user(survey_id,user_type);
});
$(document).on('click','.btn-submit',function(){
    
    $('#set_self_assign').val('');
    let checkSurveyIdExist = $('.survey_id_hidden').val();
})
// submit form when self assign the tasks 
$(document).on('click','.self-assign-btn',function(){
    let checkSurveyIdExist = $('.survey_id_hidden').val();
    $('#set_self_assign').val('set');
    $('#assign_form').submit();
})

// ajax to check the task is completed or reassigned to choose user so it can not be resassign
$(document).on('change','#user_id',function(){
    let user_type   = $('#user_type').val();
    let user_id     = $(this).val();
    let response_ids = $('.response_id_hidden').val();
    check_selected_task(user_id,user_type,response_ids);
});

function check_selected_task(user_id,user_type,response_ids){
    $('.error_1').hide();
    $('.submit_task').show();
    $.ajax({
        method:"POST",
        url:'<?=baseUrl()?>ajax/common_file.php',
        data:{
            user_id     : user_id,
            user_type   : user_type,
            response_ids: response_ids,
            mode:'check_assign_task_for_user'
        },
        success:function(response){
            console.log(response);
            if(response>0){
                $('.error_1').show();
                $('.submit_task').hide();
            }
        }
    })
}

$(document).ready(function(){
    $('.search').click(function(e){
        e.preventDefault(); 
        let surveyValue = $("#surveys").val();
        if(surveyValue ==''){
            $('.error').show();   
        }else{
            $('.error').hide()
            $("#viewReportcsv").submit();
        }
    })
})
</script>