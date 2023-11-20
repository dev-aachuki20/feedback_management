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
$filterSurvey = '';
if(isset($_POST['survey_type']) and $_POST['survey_type']>0){
    $filterSurvey .= " and survey_type=".$_POST['survey_type'];
}
if(isset($_POST['selectSurvey']) and $_POST['selectSurvey']>0){
    $filterSurvey .= " and id=".$_POST['selectSurvey'];
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

  record_set("GetDetails", "select id,name from surveys where id !=0 $filterSurvey ".$filter_query." ORDER BY cdate desc LIMIT 5");
  while($row_GetDetails = mysqli_fetch_assoc($GetDetails)){ 
      $clients_array[$row_GetDetails['id']] = $row_GetDetails['name'];
      $ykeys .= "'item".$row_GetDetails['id']."', ";
      $labels .= "'".$row_GetDetails['name']."', ";
  }

  //print_r($clients_array);
  //get date range
  $final_chart_array = array();
    $a = 0;
    $tableData = array();
  foreach($days30 as $key=> $value){
    $days = $value;
    //check we aren't on jan
    if($interval==2){
        $secondInterval = date("d M Y", strtotime($days . "-7 days"));
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
            $date_type=  'daily';
        }else if($interval==2){
            //interval by week
            $filter = " and cdate BETWEEN '".date("Y-m-d", strtotime($secondInterval))."' AND '".date("Y-m-d", strtotime($firstInterval))."'";
            $date_type=  'weekly';
        }else if($interval==3){
            //interval by monthly
            $filter = " and MONTH(cdate) = '".date("m", strtotime($secondInterval))."' ";
            $date_type=  'monthly';
        }else if($interval==4){
            //interval by yearly
            $filter = " and YEAR(cdate) = '".date("Y", strtotime($secondInterval))."' ";
            $date_type=  'yearly';
        }else {
            $filter = " and cdate like '".date("Y-m-d", strtotime($days))."%'";
            $date_type =  'daily';
        }
          record_set("Getcollectedamnt", "SELECT DISTINCT cby FROM answers where surveyid='".$clientkey."' $filter $locationQueryAndCondition");
        
         $a=0;
          while($row_get_recent_entry = mysqli_fetch_assoc($Getcollectedamnt)){ 

            if($totalRows_Getcollectedamnt>0){
                // echo '<pre>';
                // print_r($row_get_recent_entry);   echo '</pre>';
                $tableData[$firstInterval]['cby'][$clientkey][] = $row_get_recent_entry['cby'];
                //$tableData[$firstInterval]['surveyid'][$a]= $clientkey ;
                $tableData[$firstInterval]['start_date'] = date("d-m-Y", strtotime($firstInterval));
                $tableData[$firstInterval]['end_date'] = date("d-m-Y", strtotime($secondInterval));
                $a++;
            }
          }
          $a++;
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
        $urlFirst   = 'view-report&type=survey';
     //for second box
        $titleSecond = 'IN PROGRESS';
        $urlSecond   = 'survey-manage&type=survey&req=in progress&testact=1';
     //for second box
        $titleThird = 'VOID';
        $urlThird   = 'survey-manage&type=survey&req=void&testact=2';
     //for second box
        $titleFourth = 'RESOLVED';
        $urlFourth   = 'survey-manage&type=survey&req=resolved&testact=3';
    }else {
     //for first box
        $titleFirst = 'CONTACT REQUESTS';
        $urlFirst   = 'view-contacted-list&type=survey';
     //for second box
        $titleSecond = 'OVERALL RESPONSES';
        $urlSecond   = 'monthly-report&type=survey';
     //for second box
        $titleThird = 'CREATE REPORT';
        $urlThird   = 'create-report&type=report';
     //for second box
        $titleFourth = 'STATISTICS';
        $urlFourth   = 'survey-statistics&type=survey';
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
                                    <label for="exampleFormControlSelect1">Select Survey Type <?= $_POST['selectSurvey']; ?></label>
                                    <select class="form-control singleSelect2" name="survey_type" id="survey_type">
                                        <option value=''>All Survey</option>
                                        <?php 
                                        // record_set("get_surveys", "select id,name from surveys where cby='".$_SESSION['user_id']."' order by name desc");                
                                        // while($row_get_surveys = mysqli_fetch_assoc($get_surveys)){ 
                                          
                                        foreach(survey_type() as $key => $value){ ?>
                                        <option value="<?=$key?>" <?=($_POST['selectSurvey']===$key) ? 'selected':''?>><?=$value?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Select Survey</label>
                                    <select class="form-control singleSelect2" name="selectSurvey" id="selectSurvey">
                                        <option value=''>All Survey</option>
                                        <?php 
                                        // record_set("get_surveys", "select id,name from surveys where cby='".$_SESSION['user_id']."' order by name desc");                
                                        // while($row_get_surveys = mysqli_fetch_assoc($get_surveys)){ 
                                        foreach($surveyByUsers as $row_get_surveys){ ?>
                                        <option value="<?=$row_get_surveys['id']?>" <?=($_POST['selectSurvey']==$row_get_surveys['id']) ? 'selected':''?>><?=$row_get_surveys['name']?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Interval</label>
                                    <select class="form-control singleSelect2" id="exampleFormControlSelect1"  name="interval">
                                        <option value="1" <?php if(isset($interval) && $interval==1){ echo 'selected' ;}?>>Daily</option>
                                        <option value="2"  <?php if(isset($interval) && $interval==2){ echo 'selected' ;}?>>Weekly</option>
                                        <option value="3"  <?php if(isset($interval) && $interval==3){ echo 'selected' ;}?>>Monthly</option>
                                        <option value="4"  <?php if(isset($interval) && $interval==4){ echo 'selected' ;}?>>Annually </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary" name="filter" style="margin-top: 25px;" onclick="this.form.submit()">Filter</button>
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
                <div class="box-header text-left"> LATEST RESPONSES </div>
                <div class="box-body">
                    <table id="examples" class="table table-bordered table-striped">
                        <thead>
                            <tr>
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
                            $where = "";
                            $id = $_POST['selectSurvey']; 
                            if($id){
                                $where = " and surveyid = $id";
                            }else{
                                record_set("get_last_survey_id_result", "SELECT surveyid FROM `answers` where id!='' GROUP BY cby ORDER BY cdate DESC LIMIT 1");
                                $row_get_survey_id_result = mysqli_fetch_assoc($get_last_survey_id_result);
                                $surveyId = $row_get_survey_id_result['surveyid'];
                                $where = " and surveyid = $surveyId";
                            }
                            // if($_POST['selectSurvey']){
                            //     $survey_type = strtolower(survey_type()[$_POST['selectSurvey']]); 
                            //     $allSurveyList = get_allowed_survey($survey_type);
                            //     $allSurveyIds = implode(',',array_keys($allSurveyList));
                            //     $where .= " and surveyid IN ($allSurveyIds)";
                            // }
                            record_set("get_survey_result", "SELECT * FROM `answers` where id!='' $where GROUP BY cby ORDER BY cdate DESC  LIMIT 10"); 
                            $counter = 0;
                            $surveyIdExist = [];
                            if($totalRows_get_survey_result>0){
                                while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
                                $cdate = date('Y-m-d',strtotime($row_get_survey_result['cdate']));
                                $sid = $row_get_survey_result['surveyid'];
                                $row_survey_response_entry = 1;
                                $achieved_result_val = 0;
                                $to_bo_contacted     = 0;
                                $total_result_val=0;

                               record_set("survey_entry", "SELECT DISTINCT cby FROM answers where surveyid='".$row_get_survey_result['surveyid']."' and cby <".$row_get_survey_result['cby']);
                               $row_survey_response_entry = $totalRows_survey_entry+$row_survey_response_entry;
                                
                                record_set("get_survey_result_data", "SELECT id,answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_get_survey_result['surveyid']."' and cby='".$row_get_survey_result['cby']."'");
                                    $i=0;
                                    while($row_get_survey_response = mysqli_fetch_assoc($get_survey_result_data)){

                                        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_survey_response['questionid']);
                                        if($result_question){
                                            if(!in_array($result_question['answer_type'],array(2,3,5))){
                                                // echo $row_get_survey_response['questionid'].'::'.$row_get_survey_response['answerval'].'<br>';
                                                $total_result_val = ($i+1)*100;
                                                $achieved_result_val += $row_get_survey_response['answerval'];
                                                $i++;
                                            }
                                        }
                                        if($row_get_survey_response['answerid'] == -2 && $row_get_survey_response['answerval'] == 100){
                                            $to_bo_contacted = 1;
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
                                    if($to_bo_contacted==1){ 
                                        $contactedLabel ='<a class="btn btn-xs bg-green">Yes</a>';
                                    }else{ 
                                        $contactedLabel ='<a class="btn btn-xs btn-danger">No</a>';
                                    } 
                                    $new_date = date('jS M Y', strtotime($cdate));
                                ?>
                                <tr>
                                    <td><?=$new_date?></td>
                                    <td><?=getSurvey()[$sid]?></td>
                                    <td><?=$sid?></td>
                                    <td><?=$row_survey_response_entry?></td>
                                    <td><label class="label label-<?php echo $label_class; ?>"><?php echo round($result_response,2); ?>%</label></td>
                                    <td><?=$contactedLabel?></td>
                                </tr> 
                                <?php } ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;"><a href="?page=view-report">view all request</a></td>
                                </tr> 
                            <?php 
                            }else{ ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No Data available</td>
                                </tr>
                            <?php } ?>      
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
        // load survey name
        let survey_type = $('#survey_type').val();
        survey_load(survey_type);
        // make seleted after load
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
    $("#survey_type").on('change', function() {
       let survey_type = $(this).val();
       survey_load(survey_type);
    });
    function survey_load(survey_type){
        $.ajax({
            type: "POST",
            url: './ajax/common_file.php',
            data: {
                survey_type: survey_type,
                survey_id:'<?=json_encode($_POST['selectSurvey'])?>',
                mode:'dashboard',
            }, 
            success: function(response){
                response = JSON.parse(response);
                $('#selectSurvey').html(response);
            }
        });
    }
    const myTimeout = setTimeout(myStopFunction, 2000);

    function myStopFunction() {
        $('#selectSurvey option[value=<?=$_POST['selectSurvey']?>]').attr('selected','selected');
    }
    
</script>