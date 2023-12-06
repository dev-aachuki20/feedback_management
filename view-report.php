<?php 
// get data by user

use Google\Protobuf\Option;

$page_type = $_GET['type'];
$locationByUsers   = get_filter_data_by_user('locations');
$departmentByUsers = get_filter_data_by_user('departments');
$roleByUsers       = get_filter_data_by_user('roles');
$groupByUsers      = get_filter_data_by_user('groups');
$surveyByUsers     = get_survey_data_by_user($page_type,1);

// get asssign ids only
$assign_department = array();
foreach($departmentByUsers as $department){
    $assign_department[] = $department['id'];
}

$assign_location = array();
foreach($locationByUsers as $location){
    $assign_location[] = $location['id'];
}
$assign_group = array();
foreach($groupByUsers as $group){
    $assign_group[] = $group['id'];
}

$assign_role = array();
foreach($roleByUsers as $role){
    $assign_role[] = $role['id'];
}

$assign_survey = array();
foreach($surveyByUsers as $survey){
    $assign_survey[] = $survey['id'];
}

$dep_ids     = implode(',',$assign_department);
$loc_ids     = implode(',',$assign_location);
$grp_ids     = implode(',',$assign_group);
$surveys_ids = implode(',',$assign_survey);
$role_ids = implode(',',$assign_role);


// get record 
//if(!empty($_POST['surveys'])){
    $dateflag= false;
    $query = 'SELECT * FROM answers where id !=0 ';
    if(!empty($_POST['fdate']) && !empty($_POST['sdate'])){  
        $query .= " and cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
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

    if(!empty($_POST['locationid'])){
        if($_POST['locationid'] == 4){
            $query .= " and locationid in (select id from locations where cstatus=1)";  
        }else{
            $query .= "and locationid = '".$_POST['locationid']."'";
        }
    }
    if(!empty($_POST['surveys'])){
        $query .= " and surveyid =".$_POST['surveys'];
    }else{
        if($surveys_ids){
            $query .= " and surveyid IN ($surveys_ids)";
        }else{
            $query .= " and surveyid IN (0)";
        }
    }
    if(!empty($_POST['groupid'])){
        if($_POST['groupid'] == 4){
            $query .= " and groupid in (select id from `groups` where cstatus=1)";  
        }else{
            $query .= " and groupid = '".$_POST['groupid']."'";
        }
    }
    // if(!empty($_POST['contacted']) and $_POST['contacted'] !=3){
    //     if($_POST['contacted'] == 1){
    //         $query .= " and  answerid =-2 and answerval=100";
    //     }else {
    //         $query .= " and  answerid != -2 and answerval != 100";
    //     }
    // }

    $query .= " GROUP by cby";
    record_set("get_departments", "SELECT * FROM departments");	
    $departments = array();
    while($row_get_departments = mysqli_fetch_assoc($get_departments)){
        $departments[$row_get_departments['id']] = $row_get_departments['name'];
    }
    record_set("get_recent_entry",$query);
//}
?>
<style>
    .d-none{
        display: none !important;
    }
</style>
<section class="content-header">
  <h1>INDIVIDUAL RESPONSES</h1>
</section>
<section class="content">
    <!-- top box container start-->
     <?php include ('./section/top-box-container-count.php');?>
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
                                    <option value="<?php echo $row_get_surveys['id'];?>"  <?=($_POST['surveys'] == $row_get_surveys['id']) ? 'selected':''?>><?php echo $row_get_surveys['name'];?></option>
                                <?php } ?>
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
                                    <option value="<?php echo $groupId;?>" <?=($_POST['groupid'] == $groupId) ? 'selected':''?>><?php echo $groupName;?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Location</label>
                            <select name="locationid" id="locationid" class="form-control form-control-lg locationid ">
                                <option value="">Select Location</option>
                                <?php
                                    // record_set("get_location", "select * from locations where cstatus=1 $locationDropDownCondition order by name asc");        
                                    // while($row_get_location = mysqli_fetch_assoc($get_location)){ 
                                    foreach($locationByUsers as $locationData){ 
                                    $locationId     = $locationData['id'];
                                    $locationName   = $locationData['name'];?>
                                    <option value="<?php echo $locationId;?>" <?=($_POST['locationid'] == $locationId) ? 'selected':''?> ><?php echo $locationName;?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Department</label>
                            <select name="departmentid" id="departmentid" class="form-control form-control-lg department">
                                <option value="">Select Department</option>
                                <?php
                                    // record_set("get_department", "select * from departments where cstatus=1");        
                                    // while($row_get_department = mysqli_fetch_assoc($get_department)){ 
                                    foreach($departmentByUsers as $departmentData){ 
                                    $departmentId     = $departmentData['id'];
                                    $departmentName   = $departmentData['name'];?>
                                    <option value="<?php echo $departmentId;?>" <?=($_POST['departmentid'] == $departmentId) ? 'selected':''?> ><?php echo $departmentName;?></option>
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
                            <label>Contact</label>
                            <select name="contacted" id="contacted" class="form-control form-control-lg contact">
                                <option value="3" <?=($_POST['contacted'] == 3) ? 'selected':''?>>All</option>
                                <option value="1"  <?=($_POST['contacted'] == 1) ? 'selected':''?>>Yes</option>
                                <option value="2"  <?=($_POST['contacted'] == 2) ? 'selected':''?>>No</option>
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
                        <table id="common-table" class="table table-bordered table-striped" width="100%">
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <th>SURVEY NAME</th>

                                    <th>Group</th>
                                    <th>Location</th>
                                    <th>Department</th>
                                    <th>Roles</th>

                                    <th> RESPONDENT NUMBER</th>
                                    <th>RESULT</th>
                                    <th>CONTACT REQUESTED ?</th>
                                    <th class="notforpdf">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
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
                                            $result_response = $achieved_result_val*100/$total_result_val;
                                            if($achieved_result_val==0 and $total_result_val==0){
                                                $result_response=100;
                                            }
                                            // if($to_bo_contacted == 0 and $_POST['contacted'] == 2){
                                            //     continue;
                                            // }
                                            // for filter using contact
                                            if($_POST['contacted'] !='' and  $_POST['contacted']!=3){
                                                if($to_bo_contacted == 1 && $_POST['contacted'] == 2){
                                                    continue;
                                                }
                                                if($to_bo_contacted == 0 && $_POST['contacted'] == 1){
                                                    continue;
                                                }
                                            }
                                            $label_class = 'success';
                                            if($result_response<50){
                                                $label_class = 'danger';
                                            }else 
                                            if($result_response<75){
                                                $label_class = 'info';
                                            }
                                            if($to_bo_contacted==1){ 
                                                $contactedLabel ='<a class="btn btn-xs bg-green">Yes</a>';
                                            }else{ 
                                                $contactedLabel ='<a class="btn btn-xs btn-danger">No</a>';
                                            } 
                                        ?>
                                        <tr>
                                            <td data-sort="<?=date("Ymd", strtotime($row_get_recent_entry['cdate']))?>"><?=date("d-m-Y", strtotime($row_get_recent_entry['cdate']))?></td>
                                            <td><?=$row_get_survey_detail['name']?></td>

                                            <td><?=getGroup()[$row_get_recent_entry['groupid']];?></td>

                                            <td><?=getLocation()[$row_get_recent_entry['locationid']]?></td>

                                            <td><?=$departments[$row_get_recent_entry['departmentid']];?></td>

                                            <td><?=getRole()[$row_get_recent_entry['roleid']];?></td>
                                            
                                            <td><?=$row_survey_entry?></td>
                                            <td data-sort="<?=round($result_response,2)?>"><label class="label label-<?=$label_class?>"><?=round($result_response,2)?>%</label></td>
                                            <td data-sort="<?=$to_bo_contacted?>"><?=$contactedLabel?></td>
                                            <td> <a class="btn btn-xs btn-primary" href="survey-result.php?surveyid=<?=$row_get_recent_entry['surveyid']?>&userid=<?=$row_get_recent_entry['cby']?>&score=<?=round($result_response,2)?>&contacted=<?=$to_bo_contacted?>" target="_blank">VIEW DETAILS</a></td>
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
        $('#viewReportcsv').attr('action', 'export-report-table.php');
        $('#viewReportcsv').submit();
        $('#viewReportcsv').attr('action', '');
    })
    $(document).on('click','.search',function(){
        let surveys     = $('.surveys').val();
        $("#viewReportcsv").submit();
        // if(surveys ==''){
        //     $(".col-md-3").css("height", "87");
        //     $('.error').show();
        //     return;
        // }else {
        //     $('.error').hide();
        //     $("#viewReportcsv").submit();
        // }
    });

    // $(document).on('click','.search',function(){
    //     // destroy datatable
    //     $("#datatable-ajax").dataTable().fnDestroy()
    //     // for my task 
    //     let my_task     =  $(this).data('type');
    //     let start_data  = $('.start_data').val();
    //     let end_date    = $('.end_date').val();
    //     let surveys     = $('.surveys').val();
    //     let group       = $('.group').val();
    //     let locationid  = $('.locationid').val();
    //     let roleid      = $('.role').val();
    //     let departmentid  = $('.department').val();
    //     let contacted   = $('.contact').val();

    //     //add data in hidden field of my task form
    //     $('#hidden_survey_id').val(surveys);
    //     // $('#hidden_start_date').val(start_data);
    //     // $('#hidden_end_date').val(end_date);
    //     // $('#hidden_group_id').val(group);
    //     // $('#hidden_location_id').val(locationid);
    //     // $('#hidden_department_id').val(departmentid);
    //     // $('#hidden_contact').val(contacted);

    //     if(surveys ==''){
    //         $(".col-md-3").css("height", "87");
    //         $('.error').show();
    //         return;
    //     }else {
    //         $('.error').hide();
    //     }
    //     // this is the id of the form
    //    // ajax_request(start_data,end_date,surveys,group,locationid,departmentid,roleid,contacted,my_task);
    // });
    // function ajax_request(start_data,end_date,surveys,group,locationid,departmentid,roleid,contacted,my_task=''){
    //     var dataTable = $('#datatable-ajax').DataTable({
    //         "processing": true,
    //         "serverSide": true,
    //         "sPagingType": 'simple',
    //         "ajax":{
    //             url :"<?=baseUrl()?>ajax/datatable/view-report-listing.php", 
    //             type: "post",  
    //             data: { 
    //                 fdate: start_data,
    //                 sdate:end_date,
    //                 surveys:surveys,
    //                 groupid:group,
    //                 locationid:locationid, 
    //                 departmentid:departmentid,
    //                 roleid:roleid,
    //                 contact:contacted,
    //                 my_task:my_task,
    //             },
    //             error: function(){  
    //             }
    //         }
    //     });
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
