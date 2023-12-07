<?php 
// get data by user
$page_type = $_GET['type'];
$departmentByUsers = get_filter_data_by_user('departments');
$roleByUsers       = get_filter_data_by_user('roles');
$locationByUsers   = get_filter_data_by_user('locations');
$groupByUsers      = get_filter_data_by_user('groups');
$surveyByUsers     = get_survey_data_by_user($_GET['type'],1);

$assign_survey = array();
foreach($surveyByUsers as $survey){
    $assign_survey[] = $survey['id'];
}
$surveys_ids = implode(',',$assign_survey);



//disable checkbox and assign button for manager
$display = '';
if($_SESSION['user_type'] == 4){
    $display = "display:none;";
}

//fetch data in table 
    $query = 'SELECT * FROM answers where id !=0 ';
    if(!empty($_POST['fdate']) && !empty($_POST['sdate'])){  
        $query .= " and cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
    }
    
    if(!empty($_POST['departmentid'])){
        $query .= " and departmentid = '".$_POST['departmentid']."'";
    }
    if(!empty($_POST['roleid'])){
        $query .= " and roleid = '".$_POST['roleid']."'";
    }
    if(!empty($_POST['locationid'])){
        $query .= "and locationid = '".$_POST['locationid']."'";
    }
    if(!empty($_POST['groupid'])){
        $query .= " and groupid = '".$_POST['groupid']."'";
    }
    if(!empty($_POST['surveys'])){
        $query .= " and surveyid =".$_POST['surveys'];
    }else{
        if($surveys_ids){
            $query .= " and surveyid IN  ($surveys_ids)";
        }else{
            $query .= " and surveyid IN  (0)";
        }
    }
    if($loggedIn_user_type == 3){
        record_set("get_assign_task", "SELECT * FROM assign_task where assign_to_user_id = $loggedIn_user_id ".$filter_status);

        $arr_task_id = array();
        while($row_get_assign_task = mysqli_fetch_assoc($get_assign_task)){
            $arr_task_id[] = $row_get_assign_task['task_id'];
        }
        $task_id = implode(",",$arr_task_id);
        if(empty($task_id)){
            $task_id = '0';
        }
    }
    
    $query .= " and  answerid =-2 and answerval=100 GROUP by cby";
    record_set("get_recent_entry",$query);

     // record_set("get_departments", "SELECT * FROM departments");	
    // $departments = array();
    // while($row_get_departments = mysqli_fetch_assoc($get_departments)){
    //     $departments[$row_get_departments['id']] = $row_get_departments['name'];
    // }

?>
<style>
.d-none{
    display: none !important;
}
</style>
<section class="content-header">
  <h1>CONTACT REQUESTS </h1>
</section>
<section class="content">
    <!-- top box container start-->
    <?php include ('./section/top-box-container-count.php');?>
    <!-- top box container start-->
    <div class="box box-default">
        <form action="" method="POST" id="viewReportcsv">
            <!-- <input type="hidden" name="post_values" value =<?=json_encode($_POST)?> > -->
            <input type="hidden" name="cby" value="" id="createdBy">
            <div class="box-header">
                <i class="fa fa-search" aria-hidden="true"></i>
                <h3 class="box-title">Search</h3>
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
                                <option value="">Select</option>
                                <?php
                                foreach($surveyByUsers as $row_get_surveys){ ?>
                                    <option value="<?php echo $row_get_surveys['id'];?>" <?=($_POST['surveys']==$row_get_surveys['id'])?'selected':''?> ><?php echo $row_get_surveys['name'];?></option>
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <input type="button" style="background-color: #00a65a !important;border-color: #008d4c;"name="filter" class="btn btn-success btn-block search" value="Search"/>
                        </div>
                    </div>
                </div>
            </div>
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
                                <input type="hidden" name="type" value="<?=$_GET['type']?>">
                                <div class="col-md-1" style="text-align: left;padding: 0;margin: 5px;">
                                    <button type="submit" class="btn btn-success"  style="background-color: #00a65a !important;border-color: #008d4c;">My Tasks</button>
                            
                                </div>
                                <div class="col-md-3" style="text-align: left;padding: 0;margin: 5px;">
                                    <button type="button" class="btn btn-success"  style="background-color: #00a65a !important;border-color: #008d4c;" id="exportascsv">Export CSV</button>
                                </div>
                            </form>
                        </div>
                        <table id="datatable" class="table table-bordered table-striped" width="100%">
                            <thead>
                                <tr>
                                    <?php if($_SESSION['user_type'] < 4){ ?>
                                    <th></th>
                                    <?php } ?>
                                    <th>DATE</th>
                                    <th>SURVEY NAME</th>

                                    <th>Group</th>
                                    <th>Location</th>
                                    <th>Department</th>
                                    <th>Roles</th>
                                    <th> RESPONDENT NUMBER</th>
                                    <th>RESULT</th>
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
                                            }
                                            if($result_response<75){
                                                $label_class = 'info';
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
                                                <?php if($_SESSION['user_type'] < 4) { ?>
                                                <td><input type="checkbox" name="assign" value="<?=$row_get_recent_entry['cby'] ?>" class="assignSurveyCheckbox" task-type="" data-sid="<?=$row_get_recent_entry['surveyid']?>" <?=$totalRows_get_reassigned_task > 0 ? 'disabled':''?>></td>
                                                <?php } ?>
                                                <td data-sort="<?=date("Ymdhhmmss", strtotime($row_get_recent_entry['cdate']))?>"><?=date("d-m-Y", strtotime($row_get_recent_entry['cdate']))?></td>

                                                <td><?=$row_get_survey_detail['name'] . $label ?></td>

                                                <td><?=getGroup()[$row_get_recent_entry['groupid']];?></td>

                                                <td><?=getLocation()[$row_get_recent_entry['locationid']]?></td>

                                                <td><?=$departments[$row_get_recent_entry['departmentid']];?></td>

                                                <td><?=getRole()[$row_get_recent_entry['roleid']];?></td>

                                                <td><?=$row_survey_entry?></td>

                                                <td><label class="label label-<?=$label_class?>"><?=round($result_response,2)?>%</label></td>

                                                <td><a class="btn btn-xs btn-primary" href="survey-result.php?surveyid=<?=$row_get_recent_entry['surveyid']?>&userid=<?=$row_get_recent_entry['cby']?>&score=<?=round($result_response,2)?>&contacted=<?=$to_bo_contacted?>" target="_blank">VIEW DETAILS</a></td>
                                            </tr>  
                                      <?php $cby_array[] = $row_get_recent_entry['cby'];
                                       }
                                    }
                                   $cby_csv = (count($cby_array)>0)?json_encode($cby_array):'-4';
                                ?>
                             </tbody>                
                            <?php if($_SESSION['user_type'] != 4){ ?>
                            <tfoot class="assign-tfoot" style="display:none;">
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?php include('./section/task-self-assign.php') ;?></th>
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
        $('#createdBy').val(<?=$cby_csv?>);
        $('#viewReportcsv').attr('action', 'export-responses.php');
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

        // if(surveys ==''){
        //     $(".col-md-3").css("height", "87");
        //     $('.error').show();
        //     return;
        // }else {
        //     $('#viewReportcsv').submit();
        //     $('.error').hide();
        // }
        $('#viewReportcsv').submit();

        // this is the id of the form
        ajax_request(start_data,end_date,surveys,group,locationid,departmentid,contacted,my_task);
    });
    function ajax_request(start_data,end_date,surveys,group,locationid,departmentid,contacted,my_task=''){
        var dataTable = $('#datatable-ajax').DataTable({
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





// submit form when self assign the tasks 
// $(document).on('click','.self-assign-btn',function(){
//     let checkSurveyIdExist = $('.survey_id_hidden').val();
//     $('#set_self_assign').val('set');
//     $('#assign_form').submit();
// })



// $(document).on('change','.department',function(){
//     let department = $(this).val();
//     $('#roleid').html('');
//       $.ajax({
//       type: "POST",
//           url: 'ajax/common_file.php',
//           dataType: "json",
//           data: {
//             department: department,
//             mode:'load_role',
//           }, 
//           success: function(response)
//           {
//             $('#roleid').append(`<option value="">Select Role</option>`);
//             for(data in response){
//               $('#roleid').append(`<option value="${data}">${response[data]}</option>`);
//             }
//           }
//       })
//     });
</script>
