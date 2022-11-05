<section class="content-header">
    <ol class=" dashbord-title pageTitle">
      <li class="active">FEEDBACK MANAGEMENT</li>
    </ol>
</section>
<section class="content">

<?php

$days30 = array();
for($i = 0; $i < 29; $i++) {
    $days30item = date("d M Y", strtotime('-'. $i .' days'));
    $days30[] = date("d M Y", strtotime('-'. $i .' days'));
    
    $filterQuery = " and cdate like '".date("Y-m-d", strtotime($days))."%' ";
}

if(isset($_POST['selectSurvey'])){
   $id =  $_POST['selectSurvey'];
  }
  if($id>0){
    $filterSurvey = "where id= $id";
 }
 
 $interval = $_POST['interval'];
 if($interval==2){
    $days30 = array();
    for($i = 0; $i < 13; $i++) {
        $days30item = date("d M Y", strtotime('-'. $i .' weeks'));
        $days30[]   = date("d M Y", strtotime('-'. $i .' weeks'));
    }
 }

 if($interval==3){
    $days30 = array();
    for($i = 0; $i < 12; $i++) {
        $days30item = date("d M Y", strtotime('-'. $i .' months'));
        $days30[]   = date("d M Y", strtotime('-'. $i .' months'));
    }
 }
 if($interval==4){
    $days30 = array();
    for($i = 0; $i < 5; $i++) {
        $days30item = date("d M Y", strtotime('-'. $i .' years'));
        $days30[]   = date("d M Y", strtotime('-'. $i .' years'));
    }
 }
  //get Survey Data
  $filter_query = '';
  $clients_array = array();
  $ykeys = "";
  $labels = "";

  record_set("GetDetails", "select id,name from surveys  $filterSurvey ".$filter_query);
  while($row_GetDetails = mysqli_fetch_assoc($GetDetails)){ 
      $clients_array[$row_GetDetails['id']] = $row_GetDetails['name'];
      $ykeys .= "'item".$row_GetDetails['id']."', ";
      $labels .= "'".$row_GetDetails['name']."', ";
  }
  //print_r($clients_array);
  //get date range
  $final_chart_array = array();
  foreach($days30 as $key=> $value){
    $days = $value;
    //check we aren't on jan
    if($interval==2){
        $secondInterval = date("d M Y", strtotime($days . "-1 months"));
        $firstInterval   = $days;
    }else if($interval==3){
        $secondInterval = date("d M Y", strtotime($days . "-1 months"));
        $firstInterval   = $days;
    }else  if($interval==4){
        $secondInterval = date("d M Y", strtotime($days . "-1 years"));
        $firstInterval   = $days;
    }else {
        if ($key != 0) {
            $firstInterval  = $days30[$key - 1];
            $secondInterval =  $days;
        }else {
            $secondInterval =  $days;
        }
    }

      $arra_txt = "";
      $arra_txt .= "{y: '".date("Y-m-d", strtotime($days))."', ";
        
      foreach($clients_array as $clientkey =>$client){
        if($interval==1){
            $filter = "and cdate like '".date("Y-m-d", strtotime($days))."%'";
        }else if($interval==2){
            //interval by week
            $filter = " and cdate BETWEEN '".date("Y-m-d", strtotime($secondInterval))."' AND '".date("Y-m-d", strtotime($firstInterval))."'";
        }else if($interval==3){
            //interval by monthly
            $filter = " and MONTH(cdate) = '".date("m", strtotime($secondInterval))."' ";
        }else if($interval==4){
            //interval by yearly
            $filter = " and YEAR(cdate) = '".date("Y", strtotime($secondInterval))."' ";
        }else {
            $filter = " and cdate like '".date("Y-m-d", strtotime($days))."%'";
        }
          record_set("Getcollectedamnt", "SELECT DISTINCT cby FROM answers where surveyid='".$clientkey."' $filter $locationQueryAndCondition");
          $row_survey_entry = $totalRows_Getcollectedamnt;
          $tamount = 0;
          if(!empty($row_survey_entry)){
              $tamount = $row_survey_entry;
          }
          $arra_txt .= "item".$clientkey.": ".$tamount.", ";
      }
      $arra_txt .= "},";
      
      $final_chart_array[$days]=$arra_txt;
  }
  
    $final_chart_array_item = implode(" ",$final_chart_array);
    
    //for dashboard top boxs
    if($_SESSION['user_type']==4){
     //for first box
        $titleFirst = 'CONTACT REQUESTS';
        $urlFirst   = 'view-report';
     //for second box
        $titleSecond = 'IN PROGRESS';
        $urlSecond   = 'survey-manage&req=in progress&testact=1';
     //for second box
        $titleThird = 'VOID';
        $urlThird   = 'survey-manage&req=void&testact=2';
     //for second box
        $titleFourth = 'RESOLVED';
        $urlFourth   = 'survey-manage&req=resolved&testact=3';
    }else {
     //for first box
        $titleFirst = 'CONTACT REQUESTS';
        $urlFirst   = 'view-report';
     //for second box
        $titleSecond = 'OVERALL RESPONSES';
        $urlSecond   = 'monthly-report';
     //for second box
        $titleThird = 'CREATE REPORT';
        $urlThird   = 'create-report&type=report';
     //for second box
        $titleFourth = 'STATISTICS';
        $urlFourth   = 'view-statistics';
    }

    // get survey name
    $surveyByUsers = get_filter_data_by_user('surveys');
?>
    <!-- Dashboard Counter new-->
    
    <div class="row">
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-green" >
            <div class="inner">
              <p><?=$titleFirst?></p>
            </div>
		
            <div class="icon"> <i class="fas fa-clipboard-list"></i> </div>
            <a href="?page=<?=$urlFirst?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua ">
            <div class="inner">
              <p><?=$titleSecond?></p>
            </div>
			
            <div class="icon"> <i class="fas fa-comment-dots"></i> </div>
            <a href="?page=<?=$urlSecond ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> 
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-yellow">
            <div class="inner">
              <p><?=$titleThird ?></p>
            </div>
            <div class="icon"> <i class="fas fa-calendar-alt"></i> </div>
            <?php //if (in_array('inspection', $user_permission)){ ?>
            <a href="?page=<?=$urlThird ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
      
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
            <div class="inner">
                <p><?=$titleFourth?></p>
            </div>
            <div class="icon"> <i class="fas fa-exclamation"></i> </div>
            <a href="?page=<?=$urlFourth ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> 
            </div>
        </div>
    </div>
    <!-- Dashboard Counter old-->
    <!-- <div class="row">
        <a class="" href="index.php?page=survey-manage&req=contact request&aid=-2&avl=10" target="_blank"> 
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="ion ion-ios-gear-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Contact <br/>Request</span>
                        <span class="info-box-number">
                            <?php 
                                $reqCount =0; 
                                record_set("get_contact_request", "SELECT * FROM answers WHERE answerid=-2 AND answerval = 100 $locationQueryAndCondition GROUP BY cby");
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
                
                </div>
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
                            record_set("get_contact_request", "SELECT * FROM answers WHERE answerid=-2 AND answerval = 100 $locationQueryAndCondition GROUP BY cby");
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
                  
                </div>
               
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
                            record_set("get_contact_request", "SELECT * FROM answers WHERE answerid=-2 AND answerval = 100 $locationQueryAndCondition GROUP BY cby");
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
                  
                </div>
           
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
                            record_set("get_contact_request", "SELECT * FROM answers WHERE answerid=-2 AND answerval = 100 $locationQueryAndCondition GROUP BY cby");
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
                   
                </div>
            
            </div>
        </a>  
    </div> -->

    <!-- Charts  -->
    <div class="row">
        <!-- start -->
        <div class="col-md-12">
          <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">Latest Surveys</h3>
                    <form method="post" id="selectSurveyForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Select Survey</label>
                                    <select class="form-control" name="selectSurvey" onchange="this.form.submit()">
                                        <option value=''>All Survey</option>
                                        <?php 
                                        // record_set("get_surveys", "select id,name from surveys where cby='".$_SESSION['user_id']."' order by name desc");				
                                        // while($row_get_surveys = mysqli_fetch_assoc($get_surveys)){ 
                                        foreach($surveyByUsers as $row_get_surveys){ ?>
                                        <option value="<?=$row_get_surveys['id']?>"><?=$row_get_surveys['name']?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Interval</label>
                                    <select class="form-control" id="exampleFormControlSelect1"  name="interval" onchange="this.form.submit()">
                                        <option value="1" <?php if(isset($interval) && $interval==1){ echo 'selected' ;}?>>Daily</option>
                                        <option value="2"  <?php if(isset($interval) && $interval==2){ echo 'selected' ;}?>>Weekly</option>
                                        <option value="3"  <?php if(isset($interval) && $interval==3){ echo 'selected' ;}?>>Monthly</option>
                                        <option value="4"  <?php if(isset($interval) && $interval==4){ echo 'selected' ;}?>>Annually </option>
                                    </select>
                                </div>
                            </div>
                    </form>
                </div>
                <div class="box-tools pull-right" style="top:-4px !important;">
                  <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <div class="box-body " style="display: block;">
                <div class="row">
                  <div class="col-md-12 text-center">
                  <div class="chart" id="revenue-chart" style="position: relative; height: 300px;"></div>
                  </div>
                </div>
              </div>
          </div>  
        </div>
        <!-- end -->
      </div>
    

    <!-- Datatable -->
    <div class="row">
        <div class="col-lg-12">
            <div class="box">
                <div class="box-header text-left">
                    LATEST RESPONSES
                </div>
                <div class="box-body">
                    <table id="examples" class="table table-bordered table-striped">
                        <thead>
                            <tr >
                                <td>Date</td>
                                <td>Survey Name</td>
                                <td>Survey Id</td>
                                <td>Respondent Number</td>
                                <td>Result</td>
                                <td>Contact Requested?</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $filter = '';
                            if($_SESSION['user_type']>2){
                                $assignSurvey = get_assing_id_dept_loc_grp_survey();
                                if($assignSurvey){
                                    $filter = "and surveyid IN($assignSurvey)";
                                }else {
                                    $filter = "and surveyid IN(0)";
                                }
                            }
                                record_set("get_recent_entry", "SELECT surveyid,cby,cdate FROM answers WHERE $locationRecentContact answerid=-2 AND answerval = 100 $filter GROUP by cby order by cdate DESC LIMIT 10");	
                                $i=0;
                                
                                while($row_get_recent_entry = mysqli_fetch_assoc($get_recent_entry)){ $i++;
                                    record_set("get_survey_detail", "SELECT id,name FROM surveys where id='".$row_get_recent_entry['surveyid']."'");	
                                    $row_get_survey_detail = mysqli_fetch_assoc($get_survey_detail);
                                    $row_survey_entry = 1;
                                    record_set("survey_entry", "SELECT DISTINCT cby FROM answers where surveyid='".$row_get_survey_detail['id']."' and cby <".$row_get_recent_entry['cby']);
                                    $row_survey_entry = $totalRows_survey_entry+$row_survey_entry;
                                ?>
                                <tr class="">
                                    <td><?php echo date("d-m-Y", strtotime($row_get_recent_entry['cdate'])); ?></td>
                                    <td><?php echo $row_get_survey_detail['name']; ?></td>
                                    <td><?php echo $row_get_survey_detail['id']; ?></td>
                                    <td><?php echo $row_survey_entry //ordinal($row_survey_entry); ?></td>
                                    <td>
                                        <?php
                                            record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_get_recent_entry['surveyid']."' and cby='".$row_get_recent_entry['cby']."'");
                                            
                                            $total_result_val = $totalRows_get_survey_result*100;
                                            $achieved_result_val = 0;
                                            $to_bo_contacted = 0;
                                            $i=0;
                                            while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
                                                
                                                // $achieved_result_val += $row_get_survey_result['answerval'];
                                                // if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 10){
                                                //     $to_bo_contacted = 1;
                                                // }
                                                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where id =".$row_get_survey_result['questionid']);
                                                    if($result_question){
                                                        if(!in_array($result_question['answer_type'],array(2,3,5))){
                                                            $total_result_val = ($i+1)*100;
                                                            $achieved_result_val += $row_get_survey_result['answerval'];
                                                            if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100){
                                                                $to_bo_contacted = 1;
                                                            }
                                                            $i++;
                                                        }
                                                    }
                                            }
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
                                        ?>
                                        <label class="label label-<?php echo $label_class; ?>"><?php echo round($result_response,2); ?>%</label>
                                    </td>
                                    <td>
                                        <?php if($to_bo_contacted==1){ ?>
                                            <a class="btn btn-xs btn-success">Yes</a>
                                        <?php }else{ ?>
                                            <a class="btn btn-xs btn-info">No</a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;"><a href="?page=view-report">view all request</a></td>
                                </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
<script src="plugins/datatables/jquery.dataTables.min.js"></script> 
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script> 
<script language="javascript">
    /* Morris.js Charts */
    // Sales chart
    $(window).on('load', function() {
        var Data = [<?php echo $final_chart_array_item;?>];
        var yKeys = [<?php echo $ykeys; ?>];
        var labels = [<?php echo $labels; ?>];
        graph(Data,yKeys,labels);
    });

    // graph function
    function graph(Data,yKeys,labels){
        var area = new Morris.Area({
            element: 'revenue-chart',
            resize: true,
            data: Data,
            xkey: 'y',
            ykeys: yKeys,
            labels: labels,
            lineColors: ['#19a094', ' #1fb1c8', ' #1fc888', ' #70c6ee'],
            hideHover: 'auto'
        });
    }
    // $(function () {
    //     $("#examples").DataTable({
    //         // searching: false,
    //         // ordering: false,
    //         "searching": false,
    //         "paging": false,
    //         "pageLength": 10,
    //         "info": false
            
    //     });
    // });
</script>