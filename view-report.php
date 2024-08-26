<?php

    $page_type = $_GET['type'];
    
    $locationByUsers   = get_filter_data_by_user('locations');
    $departmentByUsers = get_filter_data_by_user('departments');
    $groupByUsers      = get_filter_data_by_user('groups');
    $surveyByUsers     = get_survey_data_by_user($page_type, 1);
    
    // get assign ids only
    $assign_department = array();
    foreach ($departmentByUsers as $department) {
        $assign_department[] = $department['id'];
    }
    
    $assign_location = array();
    foreach ($locationByUsers as $location) {
        $assign_location[] = $location['id'];
    }
    $assign_group = array();
    foreach ($groupByUsers as $group) {
        $assign_group[] = $group['id'];
    }
    
    $assign_survey = array();
    foreach ($surveyByUsers as $survey) {
        $assign_survey[] = $survey['id'];
    }
    
    $dep_ids     = implode(',', $assign_department);
    $loc_ids     = implode(',', $assign_location);
    $grp_ids     = implode(',', $assign_group);
    $surveys_ids = implode(',', $assign_survey);

    // Fetch departments for displaying purposes
    record_set("get_departments", "SELECT * FROM departments");
    $departments = array();
    while ($row_get_departments = mysqli_fetch_assoc($get_departments)) {
        $departments[$row_get_departments['id']] = $row_get_departments['name'];
    }
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
    <div class="box box-default <?= isset($_GET['response']) && !empty($_GET['response']) ? 'd-none' : '' ?> ">
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
                            <select name="roleid" id="roleid" class="form-control form-control-lg role" >
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
                            <button type="submit" style="background-color: #00a65a !important;border-color: #008d4c;"name="filter" class="btn btn-success btn-block search">Search</button>
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
                        <table id="report-common-table" class="table table-bordered table-striped" width="100%">
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
    /* $(document).on('click','#exportascsv',function(){
        $('#viewReportcsv').attr('action', 'export-report-table.php');
        $('#viewReportcsv').submit();
        $('#viewReportcsv').attr('action', '');
    })
    $(document).on('click','.search',function(){
        let surveys     = $('.surveys').val();
        $("#viewReportcsv").submit();
    }); */

    $(document).ready(function(e){
        runDatatable('init');
    })

    $(document).on('submit', '#viewReportcsv', function(e){
        e.preventDefault();
        filterData = {
            fdate        : $('input[name="fdate"]').val(),
            sdate        : $('input[name="sdate"]').val(),
            surveys      : $('select[name="surveys"]').val(),
            groupid      : $('select[name="groupid"]').val(),
            locationid   : $('select[name="locationid"]').val(),
            departmentid : $('select[name="departmentid"]').val(),
            roleid       : $('select[name="roleid"]').val(),
            contacted    : $('select[name="contacted"]').val(),
        };
        runDatatable('filter', filterData);
    })

    function runDatatable(dt_type = '', filterData=''){
        var ids = {
            'dep_ids' : "<?= $dep_ids ?>",
            'loc_ids' : "<?= $loc_ids ?>",
            'grp_ids' : "<?= $grp_ids ?>",
            'surveys_ids' : "<?= $surveys_ids ?>"
        }

        var tableOptions = {
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            "aaSorting": [],
            "bAutoWidth": false,
            'searching': false, 
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            'ajax': {
                'method': 'GET',
                'url': '<?=baseUrl()?>ajax/datatable/view-report-data-fetch.php',
                'data' : {
                    ids : ids,
                    type : "<?= $page_type ?>",
                    departments : <?= json_encode($departments) ?>,
                    filterData: filterData
                }
            },
            "rowCallback": function(row, data, index) {
            },
            'columns': [
                { data: 'date', "bSortable": false, "sWidth": "5%" },
                { data: 'survey_name', "sWidth": "20%" },
                { data: 'group_name', "sWidth": "20%" },
                { data: 'location_name', "sWidth": "10%" },
                { data: 'department_name', "sWidth": "20%" },
                { data: 'role_name', "sWidth": "20%" },
                { data: 'respondendent_number', "sWidth": "20%" },
                { data: 'result_response', "sWidth": "20%" },
                { data: 'contact_request', "sWidth": "20%", "bSortable": false, },
                { data: 'action', "bSortable": false, "sWidth": "10%" },
            ],
            "language": {
                "processing": ' <i class="fa fa-spinner fa-pulse fa-2x fa-fw" style="color: #d1d3d4; font-size: 40px;"></i>',
            }
        };
        if(dt_type == 'init'){
            $('#report-common-table').DataTable(tableOptions);
        } else {
            $('#report-common-table').DataTable().destroy();
            $('#report-common-table').DataTable(tableOptions);
        }
    }

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
