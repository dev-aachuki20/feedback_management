<?php 
// get data by user
$departmentByUsers = get_filter_data_by_user('departments');
$locationByUsers   = get_filter_data_by_user('locations');
$groupByUsers      = get_filter_data_by_user('groups');
$surveyByUsers     = get_filter_data_by_user('surveys');

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

</script>
