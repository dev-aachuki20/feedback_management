<?php 
// get data by user
$departmentByUsers = get_filter_data_by_user('departments');
$locationByUsers   = get_filter_data_by_user('locations');
$groupByUsers      = get_filter_data_by_user('groups');
$surveyByUsers     = get_survey_data_by_user($_GET['type']);
?>
<section class="content-header">
  <h1>Monthly Report</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-lg-12">
        <div class="box box-default">
        <form action="" method="POST" id="viewReportcsv">
            <!-- <input type="hidden" name="post_values" value =<?=json_encode($_POST)?> > -->
            <div class="box-header">
                <i class="fa fa-search" aria-hidden="true"></i>
                <h3 class="box-title"> Search</h3>
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
                        <select name="survey_name" class="form-control form-control-lg surveys" required>
                        <option value="">Select <?=$_GET['type']?></option>
                        <?php 
                          foreach($surveyByUsers as $row_get_surveys){ ?>
                                <option value="<?php echo $row_get_surveys['id'];?>" <?=($_POST['groupid']==$row_get_surveys['id']) ? 'selected' :''?>><?php echo $row_get_surveys['name'];?></option>
                            <?php }?>
                        </select>
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
                                    <option value="<?php echo $groupId;?>" <?=($_POST['groupid']==$groupId) ? 'selected' :''?>><?php echo $groupName;?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Location</label>
                        <select name="locationid" id="locationid" class="form-control form-control-lg location">
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
                    <!-- <div class="col-md-3">
                        <div class="form-group">
                            <label>Contact</label>
                            <select name="departmentid" id="departmentid" class="form-control form-control-lg contact">
                                <option value="">Select</option>
                                <option value="0">Yes</option>
                                <option value="1">No</option>
                                
                            </select>
                        </div>
                    </div> -->
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
      
    </div>
    <?php 
		record_set('getSurveyname','select * from surveys where id="'.$_REQUEST['survey_name'].'"');
		$row_getSurveyname = mysqli_fetch_assoc($getSurveyname);
		
	?>
    <div class="col-lg-12">
      <div class="box">
        <!-- <div class="box-header"><h3><?php echo $row_getSurveyname['name']?> monthly report</h3></div> -->
        <div class="box-body">        
          <table id="datatable-ajax" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>DATE</th>
                <th>SURVEY NAME</th>               
                <th>TOTAL SURVEY</th>               
                <th>AVERAGE RESULT SCORE</th>
                <th>VIEW PDF</th>
                <th>DOWNLOAD CSV</th>                
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
   $(document).on('click','.search',function(){
        // destroy datatable
        $("#datatable-ajax").dataTable().fnDestroy()
        let start_data    = $('.start_data').val();
        let end_date      = $('.end_date').val();
        let surveys       = $('.surveys').val();
        let location      = $('.location').val();
        let group         = $('.group').val();
        let departmentid  = $('.department').val();
        let current_loc_id = '<?=$_GET['locationid']?>'
        // this is the id of the form
        ajax_request(start_data,end_date,location,surveys,group,departmentid,current_loc_id);
    });
    function ajax_request(start_data,end_date,location,surveys,group,departmentid,current_loc_id){
        var dataTable = $('#datatable-ajax').DataTable( {
            "processing": true,
            "serverSide": true,
            "sPagingType": 'simple',
            "ajax":{
                url :"<?=baseUrl()?>ajax/datatable/monthly-report-listing.php", 
                type: "post",  
                data: { 
                    fdate:start_data,
                    sdate:end_date,
                    survey_name:surveys,
                    groupid:group,
                    locationid:location, 
                    departmentid:departmentid,
                    curr_loc_id:current_loc_id,
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