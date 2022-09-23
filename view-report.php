<?php 
// get data by user
$departmentByUsers = get_filter_data_by_user('departments');
$locationByUsers   = get_filter_data_by_user('locations');
$groupByUsers      = get_filter_data_by_user('groups');
$surveyByUsers     = get_filter_data_by_user('surveys');

// assign task to user
if(isset($_POST['assign'])){
    $survey_id           = $_POST['survey_id_hidden'];
    $task_id             = explode(',',$_POST['response_id_hidden']);
    $assing_to_user_id   = $_POST['assing_to_user_id'];
    $assign_to_user_type = $_POST['user_type'];
    $assign_by_user_type = $_SESSION['user_type'];
    $assign_by_user_id   = $_SESSION['user_id'];
    foreach($task_id as $tasks){
        $data = array(
            "assign_to_user_id"   => $assing_to_user_id,
            "assign_to_user_type" => $assign_to_user_type,
            "task_id"             => $tasks,
            "survey_id"           => $survey_id,
            "task_status"         => 2,
            "assign_by_user_id"   => $assign_by_user_id,
            "assign_by_user_type" => $assign_by_user_type,
            "cdate"               => date("Y-m-d H:i:s")
        );

         // check the assign task already exists for this user or not
         record_set("check_assign_task", "SELECT * FROM assign_task where assign_to_user_id = $assing_to_user_id and assign_to_user_type = $assign_to_user_type and task_id = $tasks and survey_id = $survey_id");
         $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);
         if($totalRows_check_assign_task > 0 ){
             $insert_value=	dbRowUpdate("assign_task", $data, "where id=".$row_check_assign_task['id']);
         }else {
             $insert_value =  dbRowInsert("assign_task",$data);
         }
        $userdata   = get_user_datails($assing_to_user_id,$assign_to_user_type);

        $user_email = $userdata['email'];
        $user_name  = $userdata['name'];  
           
        $data_contact_action = array(
            "user_id"=> $tasks,
            "action"=> 2,
            "cby_user_type" =>$assign_to_user_type,
            "cby_user_id" =>$assing_to_user_id,
            "comment"=> 'response assigned to '.$user_name,
            'created_date'=>date("Y-m-d H:i:s")
        );
        $insert_contact_action =  dbRowInsert("survey_contact_action",$data_contact_action);
    }
    // send mail to user assigned task
    send_email_to_assign_user($user_name,$user_email);

    if(!empty($insert_value )){	
        $msg = "Task Assigned Successfully";
        alertSuccess( $msg,'?page=view-report');
        die();
    }
        $msg = "Task Not Assigned";
        alertdanger( $msg,'?page=view-report');
}
//self assign task
if(isset($_POST['self_assign_hidden']) and !empty($_POST['self_assign_hidden'])){
    $survey_id           = $_POST['survey_id_hidden'];
    $task_id             = explode(',',$_POST['response_id_hidden']);
    $assing_to_user_id   = $_SESSION['user_id'];
    $assign_to_user_type = $_SESSION['user_type'];
    $assign_by_user_type = $_SESSION['user_type'];
    $assign_by_user_id   = $_SESSION['user_id'];
    foreach($task_id as $tasks){
        $data = array(
            "assign_to_user_id"   => $assing_to_user_id,
            "assign_to_user_type" => $assign_to_user_type,
            "task_id"             => $tasks,
            "survey_id"           => $survey_id,
            "task_status"         => 2,
            "assign_by_user_id"   => $assign_by_user_id,
            "assign_by_user_type" => $assign_by_user_type,
            "cdate"               => date("Y-m-d H:i:s")
        );
        $insert_value =  dbRowInsert("assign_task",$data);
        $data_contact_action = array(
            "user_id"=> $tasks,
            "action"=> 2,
            "cby_user_type" =>$assign_to_user_type,
            "cby_user_id" =>$assing_to_user_id,
            "comment"=> 'response assigned to '.$_SESSION['user_name'],
            'created_date'=>date("Y-m-d H:i:s")
        );
        $insert_contact_action =  dbRowInsert("survey_contact_action",$data_contact_action);
    }
    if(!empty($insert_value )){	
        $msg = "Task Assigned Successfully";
        alertSuccess( $msg,'?page=view-report');
        die();
    }
        $msg = "Task Not Assigned";
        alertdanger( $msg,'?page=view-report');
}

?>
<style>
.d-none{
    display: none !important;
}
</style>
<section class="content-header">
  <h1>Report</h1>
</section>
<section class="content">
    <!-- top box container start-->
    <div class="row">
        <!-- Dashboard Counter -->
        <a class="" href="index.php?page=survey-manage&req=contact request&aid=-2&avl=10" target="_blank"> 
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="ion ion-ios-gear-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Contact <br/>Request</span>
                        <span class="info-box-number">
                            <?php 
                                $reqCount =0; 
                                record_set("get_contact_request", "SELECT * FROM answers WHERE answerid=-2 AND answerval = 10 $locationQueryAndCondition GROUP BY cby");
                                while($row_get_contact_request = mysqli_fetch_assoc($get_contact_request)){
                                    record_set("get_action", "select * from survey_contact_action where user_id=".$row_get_contact_request['cby']."");
                                    if($totalRows_get_action == 0){
                                        ++$reqCount;
                                    }
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
   
        <a class="" href="index.php?page=survey-manage&req=in progress&testact=1" target="_blank"> 
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-orange"><i class="ion ion-ios-gear-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total In Progress</span>
                        <?php 
                            $progressCount = 0;
                            record_set("get_contact_request", "SELECT * FROM answers WHERE answerid=-2 AND answerval = 10 $locationQueryAndCondition GROUP BY cby");
                            while($row_get_contact_request = mysqli_fetch_assoc($get_contact_request)){
                                record_set("get_progress_action", "select max(action) from survey_contact_action where user_id=".$row_get_contact_request['cby']."");
                                $row_get_progress_action = mysqli_fetch_assoc($get_progress_action);
                                if($row_get_progress_action['max(action)'] == 1){
                                    ++$progressCount;
                                }
                            }
                        ?>
                        <span class="info-box-number"><?php echo $progressCount; ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
        </a>
        <a class="" href="index.php?page=survey-manage&req=void&testact=2" target="_blank">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Void</span>
                        <?php 
                            $voidCount = 0;  
                            record_set("get_contact_request", "SELECT * FROM answers WHERE answerid=-2 AND answerval = 10 $locationQueryAndCondition GROUP BY cby");
                            while($row_get_contact_request = mysqli_fetch_assoc($get_contact_request)){
                                record_set("get_void_action", "select max(action) from survey_contact_action where user_id=".$row_get_contact_request['cby']."");
                                $row_get_void_action = mysqli_fetch_assoc($get_void_action);
                                if($row_get_void_action['max(action)'] == 2){
                                    ++$voidCount;
                                }
                            }
                        ?>
                        <span class="info-box-number"><?php echo $voidCount; ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
        </a>
        <a class="" href="index.php?page=survey-manage&req=resolved&testact=3" target="_blank">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="ion ion-ios-gear-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Resolved</span>
                        <?php 
                            $totalResolved=0;
                            record_set("get_contact_request", "SELECT * FROM answers WHERE answerid=-2 AND answerval = 10 $locationQueryAndCondition GROUP BY cby");
                            while($row_get_contact_request = mysqli_fetch_assoc($get_contact_request)){
                                record_set("get_resolved_action", "select max(action) from survey_contact_action where user_id=".$row_get_contact_request['cby']."");
                                $row_get_resolved_action = mysqli_fetch_assoc($get_resolved_action);
                                if($row_get_resolved_action['max(action)'] == 3){
                                    ++$totalResolved;
                                }
                            }
                        ?>
                        <span class="info-box-number"><?php echo $totalResolved; ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
        </a>  
    </div>
    <!-- top box container start-->
    <div class="box box-default">
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
                            <select id="surveys" name="surveys" class="form-control surveys">
                                <option value="">Select</option>
                                <?php
                                    // record_set("get_surveys", "select * from surveys where cstatus=1  order by name asc"); 
                                    // while($row_get_surveys = mysqli_fetch_assoc($get_surveys)){ 
                                    
                                foreach($surveyByUsers as $row_get_surveys){ ?>
                                    <option value="<?php echo $row_get_surveys['id'];?>"><?php echo $row_get_surveys['name'];?></option>
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
                                    <option value="<?php echo $groupId;?>"><?php echo $groupName;?></option>
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
                                    <option value="<?php echo $locationId;?>"><?php echo $locationName;?></option>
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
                                    <option value="<?php echo $departmentId;?>"><?php echo $departmentName;?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Contact</label>
                            <select name="contacted" id="contacted" class="form-control form-control-lg contact">
                                <option value="3">All</option>
                                <option value="1">Yes</option>
                                <option value="2">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <input type="button" style="background-color: #00a65a !important;border-color: #008d4c;"name="filter" class="btn btn-success btn-block search" value="Search"/>
                        </div>
                    </div>
                </div>
                
            </div>
                
            <!-- <div3
                <button type="button" class="btn btn-success" id="exportascsv" style="margin-bottom: 20px;">Export CSV</button>
            </div> -->
        </form>
    </div>
    <div class="row">
        <div class="col-lg-12" id="dataforpdf">
            <div class="box">
                <div class="box-header"></div>
                    <div class="box-body">
                        <div>
                            <form method="get">
                                <input type="hidden" name="page" value="view-my-assign-task">
                                <!-- <input type="hidden" name="id" value="" id="hidden_survey_id"> -->
                                <!-- <input type="hidden" name="hidden_start_date" value="" id="hidden_start_date">
                                <input type="hidden" name="hidden_end_date" value="" id="hidden_end_date">
                                <input type="hidden" name="hidden_survey_id" value="" id="hidden_survey_id">
                                <input type="hidden" name="hidden_group_id" value="" id="hidden_group_id">
                                <input type="hidden" name="hidden_location_id" value="" id="hidden_location_id">
                                <input type="hidden" name="hidden_department_id" value="" id="hidden_department_id">
                                <input type="hidden" name="hidden_contact" value="" id="hidden_contact"> -->
                                <div class="col-md-3" style="text-align: left;padding: 0;margin: 5px;">
                                    <button type="submit" class="btn btn-success"  style="background-color: #00a65a !important;border-color: #008d4c;">My Task</button>
                            
                                </div>
                            </form>
                            
                        </div>
                        <table id="datatable-ajax" class="table table-bordered table-striped" width="100%">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>DATE</th>
                                    <th>SURVEY NAME</th>
                                    <th> RESPONDENT NUMBER</th>
                                    <th>RESULT</th>
                                    <th>CONTACT REQUESTED ?</th>
                                    <th class="notforpdf">ACTION</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th style="text-align: right;">
                                        <button type="button" class="btn btn-primary self-assign-btn" style="display: none;" name="self_assign">Self Assign</button>
                                    </th>
                                    <th class="notforpdf">
                                        <button type="button" class="btn btn-primary btn-submit" data-toggle="modal" value="" data-target="#exampleModalCenter" style="display: none;">
                                        Assign
                                        </button>
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
                        <select class="form-control" tabindex=7 id="user_type" name="user_type">
                            <option value="">Select User Type</option>
                        <?php 
                            $user_types_array=user_type();  
                            foreach($user_types_array as $key => $value){
                            if($_SESSION['user_type']==2){
                                $allowed_key=2;
                            }
                            if($key>=$_SESSION['user_type'] and $key!=1){ ?>
                            <option <?php if($type==$key){?> selected="selected"<?php  }?> value="<?php echo $key; ?>"> <?php echo $value; ?>
                            </option>
                            <?php }
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
                <button type="submit" class="btn btn-primary" name="assign">Save changes</button>
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
    $(document).on('click','#exportascsv',function(){
        $('#viewReportcsv').attr('action', 'export-report-table.php');
        $('#viewReportcsv').submit();
        $('#viewReportcsv').attr('action', '');
    })
    

    $(document).on('click','.search',function(){
        // destroy datatable
        $("#datatable-ajax").dataTable().fnDestroy()
        // for my task 
        let my_task     =  $(this).data('type');
        let start_data  = $('.start_data').val();
        let end_date    = $('.end_date').val();
        let surveys     = $('.surveys').val();
        let group       = $('.group').val();
        let locationid  = $('.locationid').val();
        let departmentid  = $('.department').val();
        let contacted   = $('.contact').val();


        //add data in hidden field of my task form
        $('#hidden_survey_id').val(surveys);
        // $('#hidden_start_date').val(start_data);
        // $('#hidden_end_date').val(end_date);
        // $('#hidden_group_id').val(group);
        // $('#hidden_location_id').val(locationid);
        // $('#hidden_department_id').val(departmentid);
        // $('#hidden_contact').val(contacted);

        if(surveys ==''){
            $(".col-md-3").css("height", "87");
            $('.error').show();
            return;
        }else {
            $('.error').hide();
        }
        // this is the id of the form
        ajax_request(start_data,end_date,surveys,group,locationid,departmentid,contacted,my_task);
    });
    function ajax_request(start_data,end_date,surveys,group,locationid,departmentid,contacted,my_task=''){
        var dataTable = $('#datatable-ajax').DataTable( {
            "processing": true,
            "serverSide": true,
            "sPagingType": 'simple',
            "ajax":{
                url :"<?=baseUrl()?>ajax/datatable/view-report-listing.php", 
                type: "post",  
                data: { 
                    fdate: start_data,
                    sdate:end_date,
                    surveys:surveys,
                    groupid:group,
                    locationid:locationid, 
                    departmentid:departmentid,
                    contact:contacted,
                    my_task:my_task,
                },
                error: function(){  
                    // $(".datatable-ajax-error").html("");
                    // $("#datatable-ajax").append('<tbody class="datatable-ajax-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    // $("#datatable-ajax_processing").css("display","none");
                }
            }
        } );
    }

$(document).on('change','.assignSurveyCheckbox',function(){
    //$(".assignSurveyCheckbox").prop("checked", false);
    var value = $(this).is(':checked');
    let sid  = $(this).data('sid');
    var checkedArray=[];
    $("input[name='assign']:checked").each(function(){
        checkedArray.push($(this).val());
    });
    
    if(value){
       $('.survey_id_hidden').val(sid);
       $('.response_id_hidden').val(checkedArray);
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
</script>
