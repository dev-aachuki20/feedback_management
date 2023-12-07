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
    $allSurveyByUsers     = get_survey_data_by_user($_GET['type']);
    $sid               = $_GET['id'];

    //get all id of survey in array
    if(!empty($_GET['task_status'])){
        $assign_survey = array_map(function($element) {
            return $element['id'];
        }, $allSurveyByUsers);
    }else{
        $assign_survey = array_map(function($element) {
            return $element['id'];
        }, $surveyByUsers);
    }
    $surveys_ids = implode(',',$assign_survey);

    $query = 'SELECT * FROM answers where id !=0';
    /**-----------------filter record start-----------*/
    if(!empty($_POST['surveys'])){
        $query .= " and surveyid =".$_POST['surveys'];
    }else{
        $query .= " and surveyid IN ($surveys_ids)";
    }

    if(!empty($_POST['locationid'])){
        $query .= " and locationid = '".$_POST['locationid']."'";
    }
    if(!empty($_POST['departmentid'])){
        $query .= " and departmentid = '".$_POST['departmentid']."'";
    }

    if(!empty($_POST['roleid'])){
        $query .= " and roleid = '".$_POST['roleid']."'";
    }
    if(!empty($_POST['fdate']) && !empty($_POST['sdate'])){  
        $query .= " and cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
    }

    $filter_status = '';
    if(!empty($_GET['task_status'])){
        if($_GET['task_status'] != 1){
            if($_GET['req'] == 'resolved'){
                $filter_status = ' and task_status IN(5,6)';
            }else{
                $filter_status = ' and task_status ='.$_GET['task_status'];
            }
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
    }
    
    /**-----------------filter record end-------------*/

    if($_GET['task_status']==1){
        $query .= " and answerid=-2 AND answerval = 100 and  cby NOT IN (".$task_id.") GROUP by cby";
    }else if($_GET['req']=='contact requests'){
        $query .= " GROUP by cby";
    }else {
        $query .= " and answerid=-2 AND answerval = 100 and cby IN (".$task_id.") GROUP by cby";
    }
    record_set("get_recent_entry",$query);

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
    <?php include ('./section/top-box-container-count.php');?>
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
                                        <?=($_GET['req'] == 'contact requests' && $_SESSION['user_type'] < 4) ? '<th></th>':''?>
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

                                            record_set("check_assign_task", "SELECT * FROM assign_task where task_id = ".$row_get_recent_entry['cby']);

                                            $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);
                                            $task_status = $row_check_assign_task['task_status'];
                                            if($_GET['task_status']==1 || empty($task_status)){
                                                $task_status = 1;
                                            }
                                            
                                            // check the task is reassigned task or not
                                            $isReassigned =  record_set_single("get_reassigned_task", "SELECT * FROM assign_task where assign_to_user_id = ".$_SESSION['user_id']." and task_id =".$row_get_recent_entry['cby']);

                                            $label = "";
                                            if($isReassigned['assign_to_user_id'] == $_SESSION['user_id'] and $isReassigned['assign_by_user_id'] == $_SESSION['user_id'] ){
                                                $label= " <span class='label label-warning' data-toggle='tooltip' data-placement='top' title='Self Assigned Task'>S</span>";
                                            }
                                            if($isReassigned['reassign_status'] == 1){
                                                $label= " <span class='label label-info' data-toggle='tooltip' data-placement='top' title='Re Assigned Task'>R</span>";
                                            }
                                        ?>
                                        <tr>
                                            <?php
                                            if(($_GET['req'] == 'contact requests' && $_SESSION['user_type'] < 4)){ ?>
                                                <td><input type="checkbox" name="assign" value="<?=$row_get_recent_entry['cby'] ?>" class="assignSurveyCheckbox" task-type="" data-sid="<?=$row_get_recent_entry['surveyid']?>" <?=$totalRows_get_reassigned_task > 0 ? 'disabled':''?>></td>
                                            <?php }
                                            ?>
                                            <td><?=date("d-m-Y", strtotime($row_get_recent_entry['cdate']))?></td>

                                            <td><?=getSurvey()[$row_get_recent_entry['surveyid']] . $label?></td>
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
                                    <!-- <th> </th> -->
                                    <th></th>
                                    <th></th>
                                    <th style="text-align: right;">
                                        <?php include('./section/task-self-assign.php') ;?>
                                    </th>
                                    <th class="notforpdf">
                                        <?php include('./section/task-assign.php') ;?>
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

<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>
<!-- Resources -->

<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-html2canvas@latest/dist/jspdf-html2canvas.min.js"></script> 
