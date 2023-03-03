<?php 
// get data by user
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
    <div class="row" >
        <!-- Dashboard Counter -->
        <a class="" href="index.php?page=survey-manage&type=<?=$page_type?>&req=contact request&aid=-2&avl=10" target="_blank"> 
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
   
        <a class="" href="index.php?page=survey-manage&type=<?=$page_type?>&req=in progress&task_status=3" target="_blank"> 
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
        <a class="" href="index.php?page=survey-manage&type=<?=$page_type?>&req=void&task_status=4" target="_blank">
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
        <a class="" href="index.php?page=survey-manage&type=<?=$page_type?>&req=resolved postive&task_status=5" target="_blank">
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
                            <label><?=($_GET['type']) ? ucfirst($_GET['type']) : 'Survey'?></label>
                            <select id="surveys" name="surveys" class="form-control surveys">
                                <option value="">Select</option>
                                <?php
                                foreach($surveyByUsers as $row_get_surveys){ ?>
                                    <option value="<?php echo $row_get_surveys['id'];?>"><?php echo $row_get_surveys['name'];?></option>
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
                            <select name="locationid" id="locationid" class="form-control form-control-lg locationid ">
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
                            <label>Role</label>
                            <select name="roleid" id="roleid" class="form-control form-control-lg role">
                                <option value="">Select</option>
                                <?php
                                    foreach($roleByUsers as $roleData){ 
                                    $roleId     = $roleData['id'];
                                    $roleName   = $roleData['name'];?>
                                    <option value="<?php echo $roleId;?>"><?php echo $roleName;?></option>
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
                        <table id="datatable-ajax" class="table table-bordered table-striped" width="100%">
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <th>SURVEY NAME</th>
                                    <th> RESPONDENT NUMBER</th>
                                    <th>RESULT</th>
                                    <th>CONTACT REQUESTED ?</th>
                                    <th class="notforpdf">ACTION</th>
                                </tr>
                            </thead>
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
        // destroy datatable
        $("#datatable-ajax").dataTable().fnDestroy()
        // for my task 
        let my_task     =  $(this).data('type');
        let start_data  = $('.start_data').val();
        let end_date    = $('.end_date').val();
        let surveys     = $('.surveys').val();
        let group       = $('.group').val();
        let locationid  = $('.locationid').val();
        let roleid      = $('.role').val();
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
        ajax_request(start_data,end_date,surveys,group,locationid,departmentid,roleid,contacted,my_task);
    });
    function ajax_request(start_data,end_date,surveys,group,locationid,departmentid,roleid,contacted,my_task=''){
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
                    roleid:roleid,
                    contact:contacted,
                    my_task:my_task,
                },
                error: function(){  
                    // $(".datatable-ajax-error").html("");
                    // $("#datatable-ajax").append('<tbody class="datatable-ajax-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    // $("#datatable-ajax_processing").css("display","none");
                }
            }
        });
    }

</script>
