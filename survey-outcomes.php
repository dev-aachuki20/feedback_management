<?php 
    $page_type = $_GET['type'];

    // get data by user
    $departmentByUsers = get_filter_data_by_user('departments');
    $locationByUsers   = get_filter_data_by_user('locations');
    $groupByUsers      = get_filter_data_by_user('groups');
    $surveyByUsers     = get_survey_data_by_user($page_type);
    $roleByUsers       = get_filter_data_by_user('roles');
    $loggedIn_user_id    = $_SESSION['user_id'];
    $loggedIn_user_type  = $_SESSION['user_type'];

    // get asssign ids only
    $assign_department = array();
    if(count($departmentByUsers) > 0) {
        foreach($departmentByUsers as $department){
            $assign_department[] = $department['id'];
        }
    }else{
        $assign_department[] = 0;
    }

    $assign_location = array();
    if(count($locationByUsers) > 0) {
        foreach($locationByUsers as $location){
            $assign_location[] = $location['id'];
        }
    }else{
        $assign_location[] = 0;
    }

    $assign_group = array();
    if(count($groupByUsers) > 0){
        foreach($groupByUsers as $group){
            $assign_group[] = $group['id'];
        }
    }else {
        $assign_group[] = 0;
    }
    
    $assign_survey = array();
    if(count($surveyByUsers) > 0){
        foreach($surveyByUsers as $survey){
            $assign_survey[] = $survey['id'];
        }
    } else {
        $assign_survey[] = 0;
    }

    $assign_role = array();
    foreach($roleByUsers as $role){
        $assign_role[] = $role['id'];
    }
    $dep_ids     = implode(',',$assign_department);
    $loc_ids     = implode(',',$assign_location);
    $grp_ids     = implode(',',$assign_group);
    $surveys_ids = implode(',',$assign_survey);

    // get table data

        $dateflag= false;
        $query = 'SELECT * FROM answers where id !=0 ';
        if(!empty($_POST['fdate']) && !empty($_POST['sdate'])){  
            $query .= " and cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
            $dateflag= true;
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

        if(!empty($_POST['locationid'])){
            $query .= "and locationid = '".$_POST['locationid']."'";
        }
        if(!empty($_POST['roleid'])){
            $query .= " and roleid = '".$_POST['roleid']."'";
        }
        if(!empty($_POST['surveys'])){
            $query .= " and surveyid =".$_POST['surveys'];
        }
        if(!empty($_POST['groupid'])){
            $query .= " and groupid = '".$_POST['groupid']."'";
        }

        // for my task
        if(!empty($_POST['status'])){
            $statusFilter = "SELECT * FROM assign_task where id !=0";
            if(!empty($_POST['surveys'])){
                $statusFilter .= " and survey_id =".$_POST['surveys'];
            }
            if($loggedIn_user_type > 2){
                $statusFilter .= " and assign_to_user_id = $loggedIn_user_id ";
            }
            $task_id =array();
            if($_POST['status'] == 1){
                record_set("get_assign_task", $statusFilter);
                while($row_get_assign_task = mysqli_fetch_assoc($get_assign_task)){
                    $task_id[] = $row_get_assign_task['task_id'];
                }
                $task_id = implode(',',$task_id);
                if($task_id){
                    $query .= " and cby NOT IN (".$task_id.")";
                }
            }else{
                $statusFilter .= ' and task_status = '.$_POST['status'];
                record_set("get_assign_task", $statusFilter);	
                while($row_get_assign_task = mysqli_fetch_assoc($get_assign_task)){
                    $task_id[] = $row_get_assign_task['task_id'];
                }
                $task_id = implode(',',$task_id);
                if($task_id){
                    $query .= " and cby IN (".$task_id.")";
                }else {
                    $query .= " and cby = 0";
                }
            }
        }
        $query .= " and answerid=-2 AND answerval = 100 and surveyid IN ($surveys_ids) GROUP by cby";
        record_set("get_departments", "SELECT * FROM departments");	
        $departments = array();
        while($row_get_departments = mysqli_fetch_assoc($get_departments)){
            $departments[$row_get_departments['id']] = $row_get_departments['name'];
        }
        record_set("get_recent_entry",$query);
?>
<style>
    .d-none{
        display: none !important;
    }
</style>
<section class="content-header">
  <h1>CONTACT OUTCOMES</h1>
</section>
<section class="content">
    <!-- top box container start-->
    <?php include ('./section/top-box-container-survey-outcomes.php'); ?>
    <!-- top box container start-->
    <div class="box box-default">
        <form action="" method="POST" id="viewReportcsv">
            <!-- <input type="hidden" name="post_values" value =<?=json_encode($_POST)?> > -->
            <input type="hidden" name="cby" value="" id="createdBy">
            <div class="box-header">
                <i class="fa fa-search" aria-hidden="true"></i>
                <h3 class="box-title"> Search</h3>
            </div>
            
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="fdate" class="form-control start_data" value="<?=$_POST['fdate']?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="sdate" class="form-control end_date" value="<?=$_POST['sdate']?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?=($_GET['type']) ? ucfirst($_GET['type']) : 'Survey'?></label>
                            <select id="surveys" name="surveys" class="form-control surveys">
                                <option value="">Select Survey</option>
                            <?php
                            foreach($surveyByUsers as $row_get_surveys){ ?>
                                <option value="<?php echo $row_get_surveys['id'];?>" <?=($_POST['surveys']==$row_get_surveys['id']) ? 'selected' :''?>><?php echo $row_get_surveys['name'];?></option>
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
                                <option value="">Select Group</option>
                                <?php foreach($groupByUsers as $groupData){ 
                                    $groupId    = $groupData['id'];
                                    $groupName  = $groupData['name']; ?>
                                    <option value="<?php echo $groupId;?>" <?=($_POST['groupid']==$groupId) ? 'selected' :''?>><?php echo $groupName;?></option>
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
                                 foreach($locationByUsers as $locationData){ 
                                    $locationId     = $locationData['id'];
                                    $locationName   = $locationData['name'];?>
                                    <option value="<?php echo $locationId;?>" <?=($_POST['locationid']==$locationId) ? 'selected' :''?>><?php echo $locationName;?></option>
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
                                    <option value="<?php echo $departmentId;?>" <?=($_POST['departmentid']==$departmentId) ? 'selected' :''?> > <?php echo $departmentName;?></option>
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
                                    <option value="<?=$RoleId?>"><?=$RoleName?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control form-control-lg status">
                                <option value="">Select status</option>
                            <?php foreach(assign_task_status() as $key => $value) { ?>
                                <option value="<?=$key?>" <?=($_POST['status']==$key) ? 'selected':'' ?>><?=$value?></option>
                            <?php } ?>
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
            <div class="col-md-3" style="text-align: left;padding: 0;margin: 12px;z-index: 99;">
                <button type="button" class="btn btn-success"  style="background-color: #00a65a !important;border-color: #008d4c;" id="exportascsv">Export CSV</button>
            </div>
            <div class="box">
                <div class="box-header"></div>
                    <div class="box-body">
                        <table id="common-table" class="table table-bordered table-striped" width="100%">
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <th>SURVEY NAME</th>
                                    <th>RESPONDENT NUMBER</th>
                                    <th>DEPARTMENT</th>
                                    <th>LOCATION</th>
                                    <th>GROUP</th>
                                    <th>ROLE</th>
                                    <th>RESULT</th>
                                    <th>STATUS </th>
                                    <th class="notforpdf">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $datata = '';
                                    if($totalRows_get_recent_entry >0){
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
                                            // if($to_bo_contacted == 0){
                                            //     continue;
                                            // }
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

                                            // get taskstatus
                                            $fData  = '';
                                            if($_SESSION['user_type']>2){
                                                $fData = " and assign_to_user_id = ".$_SESSION['user_id'];
                                            }
                                            record_set("check_assign_task", "SELECT * FROM assign_task where task_id = ".$row_get_recent_entry['cby']." $fData");
                                            $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);
                                            $task_status = $row_check_assign_task['task_status'];
                                            $param = "&status=assign";
                                           
                                            if(empty($task_status)){
                                                $task_status =1;
                                                $param = "";
                                            }
                                        ?>
                                            <tr>
                                                <td data-sort="<?=date("Y-m-d", strtotime($row_get_recent_entry['cdate']));?>"><?=date("d-m-Y", strtotime($row_get_recent_entry['cdate']));?></td>
                                                <td><?=$row_get_survey_detail['name'];?></td>
                                                <td><?=$row_survey_entry;?></td>
                                                <td><?=$departments[$row_get_recent_entry['departmentid']];?></td>
                                                <td><?=getLocation()[$row_get_recent_entry['locationid']]?></td>
                                                <td><?=getGroup()[$row_get_recent_entry['groupid']];?></td>
                                                <td><?=getRole()[$row_get_recent_entry['roleid']];?></td>
                                                <td data-sort="<?=$result_response?>"><label class="label label-<?=$label_class?>"><?=round($result_response,2)?>%</label></td>
                                                <td><a class="btn btn-xs btn-success"><?=assign_task_status()[$task_status]?></a></td>
                                                <td><a class="btn btn-xs btn-primary" href="survey-result.php?surveyid=<?=$row_get_recent_entry['surveyid']?>&userid=<?=$row_get_recent_entry['cby'].$param?>&score=<?=round($result_response,2)?>&contacted=<?=$to_bo_contacted?>" target="_blank">VIEW DETAILS</a></td>
                                            </tr>
                                        <?php
                                        }
                                    }
                                ?>
                            </tbody>
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
    $(document).on('click','#exportascsv',function(){
        let survey_name = $('.surveys').val();
        if(survey_name ==''){
            alert('Please Select Survey to Export Data');
            return false;
        }
        $('#viewReportcsv').attr('action', 'export-responses.php');
        $('#viewReportcsv').submit();
        $('#viewReportcsv').attr('action', '');
    })
    $(document).on('click','.search',function(){
        if(surveys ==''){
            $(".col-md-3").css("height", "87");
            $('.error').show();
            return;
        }else {
            $('.error').hide();
            $("#viewReportcsv").submit();
        }
    })

    // $(document).on('click','.search',function(){
    //     // destroy datatable
    //     $("#datatable-ajax").dataTable().fnDestroy()
    //     let start_data  = $('.start_data').val();
    //     let end_date    = $('.end_date').val();
    //     let surveys     = $('.surveys').val();
    //     let group       = $('.group').val();
    //     let locationid  = $('.locationid').val();
    //     let departmentid  = $('.department').val();
    //     let roleid      = $('.role').val();
    //     let status   = $('.status').val();
    //     if(surveys ==''){
    //         $(".col-md-3").css("height", "87");
    //         $('.error').show();
    //         return;
    //     }else {
    //         $('.error').hide();
    //     }
    //     // this is the id of the form
    //     ajax_request(start_data,end_date,surveys,group,locationid,departmentid,roleid,status);
    // });
    // function ajax_request(start_data,end_date,surveys,group,locationid,departmentid,roleid,status){
    //     var dataTable = $('#datatable-ajax').DataTable( {
    //         "processing": true,
    //         "serverSide": true,
    //         "sPagingType": 'simple',
    //         "ajax":{
    //             url :"<?=baseUrl()?>ajax/datatable/view-survey-outcomes.php", 
    //             type: "post", 
    //             dataType: "json",
    //             data: { 
    //                 fdate: start_data,
    //                 sdate:end_date,
    //                 surveys:surveys,
    //                 groupid:group,
    //                 locationid:locationid, 
    //                 departmentid:departmentid,
    //                 roleid:roleid,
    //                 status:status,
    //             },
    //             error: function(){  
    //                 // $(".datatable-ajax-error").html("");
    //                 // $("#datatable-ajax").append('<tbody class="datatable-ajax-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
    //                 // $("#datatable-ajax_processing").css("display","none");
    //             }
    //         }
    //     } );
    // }

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
