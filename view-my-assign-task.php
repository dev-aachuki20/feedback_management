<style>
/* .col-md-3 {
    height: 80px;
} */
</style>

<?php 
$sid = $_GET['id'];
$type = $_GET['type'];
// get data by user
$surveyByUsers      = get_survey_data_by_user($_GET['type'],1);
$departmentByUsers  = get_filter_data_by_user('departments');
$roleByUsers        = get_filter_data_by_user('roles');
$locationByUsers    = get_filter_data_by_user('locations');
$groupByUsers       = get_filter_data_by_user('groups');

    $dateflag= false;
    $query = 'SELECT * FROM answers where id !=0';
    if(!empty($_POST['fdate']) && !empty($_POST['sdate'])){  
        $query .= " and cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
        $dateflag= true;
    }

    if(!empty($_POST['surveys'])){
        $query .= " and surveyid = ".$_POST['surveys'];
    }
    if(!empty($_POST['departmentid'])){
        if($_POST['departmentid'] == 4){
            $query .= " and departmentid = '".$_POST['hidden_department_id']."'";
        }
    }
    if(!empty($_POST['roleid'])){
        $query .= " and roleid = '".$_POST['roleid']."'";
    }

    if(!empty($_POST['locationid'])){
        $query .= " and locationid = '".$_POST['locationid']."'";
    }
    if(!empty($_POST['groupid'])){
        $query .= " and groupid = '".$_POST['groupid']."'";
    }
    if(!empty($_POST['contacted'])){
        $que= " and  answerid != -2 and answerval != 100";
    }

    $filter_status = '';

    if(!empty($_POST['task_status'])){
        $filter_status = ' and task_status ='.$_POST['task_status'];
    }
    record_set("get_assign_task", "SELECT * FROM assign_task where survey_type=$survey_type_id and assign_to_user_id = $loggedIn_user_id".$filter_status);

    $arr_task_id = array();
    while($row_get_assign_task = mysqli_fetch_assoc($get_assign_task)){
        $arr_task_id[] = $row_get_assign_task['task_id'];
    }
    $task_id = implode(",",$arr_task_id);
    if(empty($task_id)){
        $task_id = '0';
    }

    if($loggedIn_user_type > 2){
        $assign_survey = array();
        foreach($surveyByUsers as $survey){
            $assign_survey[] = $survey['id'];
        }
        if($assign_survey){
            $query .= " and surveyid IN (".implode(',',$assign_survey).")";
        }else {
            $query .= " and surveyid IN (0)";
        }
    }

    $query .= " and cby IN (".$task_id.") GROUP by cby";
    record_set("get_recent_entry",$query);


 $display = '';
// if($_SESSION['user_type'] == 3){
//     $display = 'display:none;';
// }
?>
<style>
.d-none{
    display: none !important;
}
</style>
<section class="content-header">
  <h1>My Task</h1>
</section>
<section class="content">
    <!-- top box container start-->
    <div class="box box-default">
        <div class="box-header">
            <i class="fa fa-search" aria-hidden="true"></i>
            <h3 class="box-title">Search</h3>
        </div>
        
        <div class="box-body">
            <form action="" method="POST" id="viewReportcsv">
                <input type="hidden" name="cby" value="" id="createdBy">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="fdate" class="form-control start_data" value="<?php //echo date('Y-m-d', strtotime('-1 months')); ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="sdate" class="form-control end_date" value="<?php //echo date('Y-m-d'); ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Survey</label>
                            <select id="surveys" name="surveys" class="form-control surveys" required>
                                <option value="">Select</option>
                                <?php
                                    // record_set("get_surveys", "select * from surveys where cstatus=1  order by name asc"); 
                                    // while($row_get_surveys = mysqli_fetch_assoc($get_surveys)){ 
                                    
                                foreach($surveyByUsers as $row_get_surveys){ ?>
                                    <option value="<?php echo $row_get_surveys['id'];?>" <?=($_POST['surveys']==$row_get_surveys['id'])?'selected':''?>><?php echo $row_get_surveys['name'];?></option>
                                <?php }?>
                            </select>
                            <label for="" class="error" style="display:none ;"> This field is required</label>
                        </div>
                    </div>
                        <!-- filter by group -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Group</label>
                                <select name="groupid" id="groupid" class="form-control form-control-lg group">
                                    <option value="">Select</option>
                                    <?php foreach($groupByUsers as $groupData){ 
                                        $groupId    = $groupData['id'];
                                        $groupName  = $groupData['name']; ?>
                                        <option value="<?php echo $groupId;?>" <?=($_POST['groupid']==$groupId)?'selected':''?>><?php echo $groupName;?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Location</label>
                                <select name="locationid" id="locationid" class="form-control form-control-lg locationid">
                                    <option value="">Select</option>
                                    <?php
                                        // record_set("get_location", "select * from locations where cstatus=1 $locationDropDownCondition order by name asc");        
                                        // while($row_get_location = mysqli_fetch_assoc($get_location)){ 
                                        foreach($locationByUsers as $locationData){ 
                                        $locationId     = $locationData['id'];
                                        $locationName   = $locationData['name'];?>
                                        <option value="<?php echo $locationId;?>" <?=($_POST['locationid']==$locationId)?'selected':''?>><?php echo $locationName;?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="departmentid" id="departmentid" class="form-control form-control-lg department">
                                    <option value="">Select</option>
                                    <?php
                                        // record_set("get_department", "select * from departments where cstatus=1");        
                                        // while($row_get_department = mysqli_fetch_assoc($get_department)){ 
                                        foreach($departmentByUsers as $departmentData){ 
                                        $departmentId     = $departmentData['id'];
                                        $departmentName   = $departmentData['name'];?>
                                        <option value="<?php echo $departmentId;?>" <?=($_POST['departmentid']==$departmentId)?'selected':''?>><?php echo $departmentName;?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Role</label>
                                <select name="roleid" id="roleid" class="form-control form-control-lg role">
                                    <option value="">Select Role</option>
                                    <?php 
                                    foreach($roleByUsers as $roleByUser ){ 
                                        $RoleId     = $roleByUser['id'];
                                        $RoleName   = $roleByUser['name']; 
                                    ?>
                                    <option value="<?=$RoleId?>" <?=($_POST['roleid'] == $RoleId) ? 'selected':''?> ><?=$RoleName?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-md-3">
                            <div class="form-group">
                                <label>Contact</label>
                                <select name="contacted" id="contacted" class="form-control form-control-lg contact">
                                    <option value="3">All</option>
                                    <option value="1">Yes</option>
                                    <option value="2">No</option>
                                </select>
                            </div>
                        </div> -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="contact-date" style="font-size:14px;"><strong> Status</strong></label>
                                <div class="form-group">
                                    <select id="task_status" name="task_status" class="form-control" >
                                    <option value="">SELECT</option>
                                    <?php foreach(assign_task_status() as $key => $value) { ?>
                                        <option value="<?=$key?>" <?=($_POST['task_status']==$key) ? 'selected':'' ?>><?=$value?></option>
                                    <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input type="submit" style="background-color: #00a65a !important;border-color: #008d4c;"name="filter" class="btn btn-success btn-block search" value="Search"/>
                            </div>
                        </div>
                </div>
            </form>    
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12" id="dataforpdf">
            <div class="box">
                <div class="box-header"></div>
                    <div class="box-body">
                        <div>
                            <div class="col-md-1" style="text-align: left;padding: 0;margin-left: 10px;">
                                <a href="?page=view-contacted-list&type=<?=$type?>">
                                <button type="button" class="btn btn-success"  style="background-color: #00a65a !important;border-color: #008d4c;margin-bottom: 20px;">Back</button>
                                </a>
                            </div>
                            <div class="col-md-3" style="text-align: left;padding: 0;margin-bottom: 10px;margin-left: -20px; margin-bottom: 20px;">
                              <button type="button" class="btn btn-success"  style="background-color: #00a65a !important;border-color: #008d4c;" id="exportascsv">Export CSV</button>
                            </div>
                        </div>
                        
                        <table id="datatable" class="table table-bordered table-striped" width="100%">
                            <thead>
                                <tr>
                                <?php if($_SESSION['user_type'] < 4) { ?>
                                    <th style="<?=$display?>"></th>
                                <?php } ?>
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
                                    $cby_array = array();
                                    if($totalRows_get_recent_entry >0){
                                        $i=0;
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
                                                if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100){
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
                                            }
                                            if($result_response<75){
                                                $label_class = 'info';
                                            }
                            
                                            ?>
                                            <tr>
                                                <?php if($_SESSION['user_type'] < 4) { ?>
                                                <td scope="row" style="<?=$display?>" ><input type="checkbox" name="assign" value="<?=$row_get_recent_entry['cby'] ?>" class="assignSurveyCheckbox" task-type="" data-sid="<?=$row_get_recent_entry['surveyid']?>">
                                                </td>
                                                <?php } ?>

                                                <td><?=date("d-m-Y", strtotime($row_get_recent_entry['cdate']))?>
                                                </td>
                                                
                                                <td><?=getSurvey()[$row_get_recent_entry['surveyid']]?></td>

                                                <td><?=getGroup()[$row_get_recent_entry['groupid']]?></td>

                                                <td><?=getLocation()[$row_get_recent_entry['locationid']]?></td>

                                                <td><?=getDepartment()[$row_get_recent_entry['departmentid']]?></td>

                                                <td><?=$row_survey_entry?></td>

                                                <?php
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
                                                            if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100){
                                                                $to_bo_contacted = 1;
                                                            }
                                            
                                                    }
                                                    $result_response = $achieved_result_val*100/$total_result_val;

                                                    // for filter using contact
                                                    if($to_bo_contacted == 0 && $_POST['contact']==1){
                                                        continue;
                                                    }
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
                                                        $contacted ='<a class="btn btn-xs btn-info">No</a>';
                                                    } 
                                                    // get taskstatus
                                                    record_set("check_assign_task", "SELECT * FROM assign_task where assign_to_user_id = ".$_SESSION['user_id']." and task_id = ".$row_get_recent_entry['cby']);
                                                    $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);
                                                    $task_status = $row_check_assign_task['task_status'];
                                                    ?>

                                                    <td><label class="label label-<?=$label_class?>"><?=round($result_response,2)?>'%</label></td>

                                                    <!-- <td><?=$contacted ?></td> -->

                                                    <td><a class="btn btn-xs btn-success"><?=assign_task_status()[$task_status]?></a></td>
                                                    <td><a class="btn btn-xs btn-primary" href="survey-result.php?surveyid=<?=$row_get_recent_entry['surveyid']?>&userid=<?=$row_get_recent_entry['cby']?>&status=assign" target="_blank">VIEW DETAILS</a></td>
                                            </tr>

                                      <?php  
                                        $cby_array[] = $row_get_recent_entry['cby'];
                                      }
                                    }
                                ?>
                             </tbody>            
                            <tfoot style="<?=$display?>">
                                <tr >
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th class="notforpdf">
                                        <?php include('./section/task-re-assing.php') ;?>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
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
<script>
    // download csv
    $(document).on('click','#exportascsv',function(){
        let survey_name = $('.surveys').val();
        if(survey_name ==''){
            alert('Please Select Survey to Export Data');
            return false;
        }
       // $('#createdBy').val(<?=json_encode($cby_array)?>);
        $('#createdBy').val(<?=$task_id?>);
        $('#viewReportcsv').attr('action', 'export-responses.php');
        $('#viewReportcsv').submit();
        $('#viewReportcsv').attr('action', '');
    })

    $(document).on('change','.department',function(){
    //let interval = $('#interval').val();
    let department = $(this).val();
    $('#roleid').html('');
      $.ajax({
      type: "POST",
          url: 'ajax/common_file.php',
          dataType: "json",
          data: {
            department: department,
            mode:'load_role',
          }, 
          success: function(response)
          {
            $('#roleid').append(`<option value="">Select Role</option>`);
            for(data in response){
              $('#roleid').append(`<option value="${data}">${response[data]}</option>`);
            }
          }
      })
    });
</script>