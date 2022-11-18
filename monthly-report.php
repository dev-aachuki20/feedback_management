<?php 
  // get data by user
  $departmentByUsers = get_filter_data_by_user('departments');
  $locationByUsers   = get_filter_data_by_user('locations');
  $groupByUsers      = get_filter_data_by_user('groups');
  $surveyByUsers     = get_survey_data_by_user($_GET['type']);

$requestData= $_REQUEST;

if(!empty($requestData['survey_name'])){
    record_set('getSurveyname','select * from surveys where id="'.$requestData['survey_name'].'"');
    $row_getSurveyname = mysqli_fetch_assoc($getSurveyname);
    $filterQuery = "";
    if(isset($requestData['survey_name']) && $requestData['survey_name'] != '' && $requestData['survey_name'] != 0){
      $filterQuery .= "and answers.surveyid=".$requestData['survey_name'];
    }
    // filter by date
    if(!empty($requestData['fdate']) && !empty($requestData['sdate'])){  
        $filterQuery .= " and answers.cdate between '".date('Y-m-d', strtotime($requestData['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($requestData['sdate'])))."'";
    }

    // filter by departmentid
    if(isset($requestData['departmentid']) && $requestData['departmentid'] != ''){
        if($requestData['departmentid'] == 4){
            if(!empty($filterQuery)){
                $filterQuery .= " and answers.departmentid in (select id from departments where cstatus=1)";    
            }else{
                $filterQuery .= " and answers.departmentid in (select id from departments where cstatus=1)"; 
            }
        }else{
            if(!empty($filterQuery)){  
                $filterQuery .= " and answers.departmentid = '".$requestData['departmentid']."'";   
            }else{
                $filterQuery .= " and answers.departmentid = '".$requestData['departmentid']."'";
            }
        }
    }

    // filter by groupid
    if(isset($requestData['groupid']) && $requestData['groupid'] != ''){
        if($requestData['groupid'] == 4){
            if(!empty($filterQuery)){
                $filterQuery .= " and answers.groupid in (select id from groups where cstatus=1)";    
            }else{
                $filterQuery .= " and answers.groupid in (select id from groups where cstatus=1)"; 
            }
        }else{
            if(!empty($filterQuery)){  
                $filterQuery .= " and answers.groupid = '".$requestData['groupid']."'";   
            }else{
                $filterQuery .= " and answers.groupid = '".$requestData['groupid']."'";
            }
        }
    }

    // filter by locationid
    if(isset($requestData['locationid']) && $requestData['locationid'] != ''){
      if($requestData['locationid'] == 4){
        if(!empty($filterQuery)){
          $filterQuery .= " and answers.locationid in (select id from locations where cstatus=1)";    
        }else{
          $filterQuery .= " and answers.locationid in (select id from locations where cstatus=1)"; 
        }
      }else{
        if(!empty($filterQuery)){  
          $filterQuery .= " and answers.locationid = '".$requestData['locationid']."'";   
        }else{
          $filterQuery .= " and answers.locationid = '".$requestData['locationid']."'";
        }
      }
    }else{
      $filterQuery .= $locationJoinCondition;
    }
    record_set("survey_date",'select DATE(cdate) as cdate from answers group by DATE(cdate) order by cdate ');
    $row_get_survey_date = mysqli_fetch_assoc($survey_date);
    $date_array =array();
    $a = 0;
    $startDate = $row_get_survey_date['cdate'];
    while($end <= date("Y-m-d")){
        //echo $end.':'.date("Y-m-d"); echo '<br>';
        if($end >= date("Y-m-d")){
            break;
        }
        $date_array[$a]['start']= $startDate;
        
        if($requestData['interval'] ==24){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+1 days"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==168){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+7 days"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==336){
            $end = date("Y-m-d",strtotime($startDate."+14 days"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==720){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+1 month"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==2160){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+3 month"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==4320){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+6 month"));
            $date_array[$a]['end']=  $end;
        }
        else if($requestData['interval'] ==8640){
            //echo 'startdate'.$startDate; echo '<br>';
            $end = date("Y-m-d",strtotime($startDate."+1 years"));
            $date_array[$a]['end']=  $end;
        }else {
            $end = date("Y-m-d",strtotime($startDate."+1 days"));
            $date_array[$a]['end']=  $end;
        }
        $startDate =  $end;
        $a++;
    }
    
}

?>
<style>
  table tr td:nth-child(3) {
    text-align: center;
  }
  table tr td:nth-child(4) {
    text-align: center;
  }
  table tr td:nth-child(5) {
    text-align: center;
  }
  .form-group {
    height: 55px;
}
</style>
<section class="content-header">
  <h1>RESULTS</h1>
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
                                <option value="<?php echo $row_get_surveys['id'];?>" <?=($_POST['survey_name']==$row_get_surveys['id']) ? 'selected' :''?>><?php echo $row_get_surveys['name'];?></option>
                            <?php }?>
                        </select>
                        <span class="error" style="display:none">This Field required</span>
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
                    <input type="hidden" name="interval" value="" id="interval_hidden">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <input type="button" style="background-color: #00a65a !important;border-color: #008d4c;"name="filter" class="btn btn-success btn-block searchsdsd" value="Search"/>
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
          <div class="col-md-12" style="padding: 0px;">
          <div class="col-md-3" style="padding: 0px;">
              <div class="form-group">
                  <label>Interval</label>
                  <select name="groupid" id="interval" class="form-control form-control-lg interval">
                      <?php foreach(service_type() as $key => $value){ ?>
                          <option value="<?php echo $key;?>" <?=($_POST['interval']==$key) ? 'selected' :''?>><?php echo $value;?></option>
                      <?php }?>
                  </select>
              </div>
          </div>
          </div>      
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>DATE</th>
                <th>SURVEY NAME</th>               
                <th>TOTAL SURVEY</th>   
                <th>CONTACTED REQUESTED</th>              
                <th>AVERAGE RESULT SCORE</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                foreach($date_array as $date){
                  $query = "SELECT answers.surveyid as surveyid,answers.cby as cby,answers.locationid,surveys.name,answers.cdate FROM `answers` INNER JOIN surveys ON answers.surveyid=surveys.id where answers.surveyid!=0 $filterQuery and answers.cdate between '".$date['start']."' and '".$date['end']."' group by answers.cby";
          
                  record_set("survey_detail",$query);
                  
                  //record_set("get_recent_entry",$query);
                  if($totalRows_survey_detail>0){?>
                    <tr>
                      <td><?=$date['start']?></td>
                      <td><?=getSurvey()[$requestData['survey_name']]?></td>
                      <?php
                        $total_result_val=0;
                        $achieved_result_val = 0;
                        $to_bo_contacted     = 0;
                        $i=0;
                        $contactedCount = 0;
                        $count= 0;
                        $result_response = 0;
                        while($row_survey_detail = mysqli_fetch_assoc($survey_detail)){
                            record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_survey_detail['surveyid']."' and cby='".$row_survey_detail['cby']."'");
            
                            while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
                            $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where id =".$row_get_survey_result['questionid']);
                                if($result_question){
                                    if(!in_array($result_question['answer_type'],array(2,3,5))){
                                        $total_result_val = ($i+1)*100;
                                        $achieved_result_val += $row_get_survey_result['answerval'];
                                        $i++;
                                    }
                                }
                                if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100){
                                    $to_bo_contacted = 1;
                                    $contactedCount++;
                                }
                            }
                            //echo $achieved_result_val.' : '.$total_result_val;
                            $result_response += $achieved_result_val*100/$total_result_val;
                            $count++;
                        }
                        
                        $result_response_value = $result_response/$count;
                        if(is_nan($result_response_value)){
                            $result_response_value=100;
                        }
                        ?>
                          <td><?=$count?></td>
                          <td><?=$contactedCount?></td>
                          <td><?=round($result_response_value,2).'%'?></td>
                          <td><?='<div class="action-btn"><a class="btn btn-xs btn-primary " href="export-pdf.php?surveyid='.$requestData['survey_name'].'&amp;start='.$date['start'].'&end='.$date['end'].'&location='.$requestData['curr_loc_id'].'" target="_blank">View PDF</a> <a class="btn btn-xs btn-primary" href="export-result.php?surveyid='.$requestData['survey_name'].'&start='.$date['start'].'&end='.$date['end'].'&location='.$requestData['curr_loc_id'].'&name='.$row_getSurveyname['name'].'" target="_blank">Download CSV</a></div>'?></td>
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
</section>
<script>
  // filter data using interval
  $(document).on('change','#interval',function(){
    let interval = $(this).val();
    let surveys = $('.surveys').val();
    $('.error').hide();
    if(surveys == ''){
      $('.error').show();
      return false;
    }
    $('#interval_hidden').val(interval);
    $('#viewReportcsv').submit();
  });

   $(document).on('click','.searchsdsd',function(){
    let interval = $('#interval').val();
    let surveys = $('.surveys').val();
    $('.error').hide();
    if(surveys == ''){
      $('.error').show();
      return false;
    }
    $('#interval_hidden').val(interval);
       $('#viewReportcsv').submit();
    });
 
</script>
<link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
<script src="plugins/datatables/jquery.dataTables.min.js"></script> 
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script> 
<script>
    $(function () {
      $("#example1").DataTable({"ordering": false});
    });
</script>