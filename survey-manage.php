
 
<section class="content-header">
  <h1> <?php echo (!empty($_GET['req']))?strtoupper($_GET['req']):'';?> </h1>
</section>
<section class="content">
    <div class="row">
     <div class="col-lg-12">
      <div class="box">
        <div class="box-header">
          <?php echo (!empty($_GET['req']))?'TOTAL '.strtoupper($_GET['req']):'';?>
        </div>
        <div class="box-body">
          <table id="examples" class="table table-bordered table-striped">
          <thead>
            <tr>
              <td>Date Time</td>
              <td>Survey</td>
              <!--<td>Department</td>-->
              <td> Response</td>
              <td>Result Score</td>
              <td>Requested Contact?</td>
              <td>Action</td>
            </tr>
          </thead>
          <tbody>
          <?php
            $answerid  = (!empty($_GET['aid']))? $_GET['aid']:'';
            $answerval = (!empty($_GET['avl']))? $_GET['avl']:'';
            if(!empty($_GET['aid']) && !empty($_GET['avl'])){
              
              $user_id = array();
              record_set("get_survey_check", "SELECT cby FROM answers WHERE answerid=$answerid AND answerval = $answerval $locationQueryAndCondition GROUP by cby order by cdate DESC ");
              while($row_get_survey_check = mysqli_fetch_assoc($get_survey_check)){
                record_set("get_action_request", "select * from survey_contact_action where user_id=".$row_get_survey_check['cby']."");
                if($totalRows_get_action_request == 0){
                  $row_get_action_request = mysqli_fetch_assoc($get_action_request);
                  $user_id[] = $row_get_survey_check['cby'];
                }
              }
              record_set("get_recent_entry", "SELECT surveyid,cby,cdate FROM answers WHERE answerid=$answerid AND answerval = $answerval AND cby IN(".implode(",",$user_id).") GROUP by cby order by cdate DESC ");
            }
            
            $userId = array();
            if(!empty($_GET['testact'])){
              record_set("total_action", "select * from survey_contact_action where action=".$_GET['testact']."");
              while($row_total_action =  mysqli_fetch_assoc($total_action)){
               
                record_set("get_action", "select max(action) from survey_contact_action where user_id=".$row_total_action['user_id']."");
                $row_get_action = mysqli_fetch_assoc($get_action);
                if($row_get_action['max(action)'] == $_GET['testact']){
                  $userId[] = $row_total_action['user_id'];
                }
              }  
              if(count($userId)>0){
                $u = "AND cby IN(".implode(",",$userId).")";
              }
              record_set("get_recent_entry", "SELECT surveyid,cby,cdate FROM answers WHERE $locationRecentContact answerid=-2 AND answerval = 10   $u GROUP by cby order by cdate DESC ");
            }
           
            $i=0;
            while($row_get_recent_entry = mysqli_fetch_assoc($get_recent_entry)){
              $i++;
              record_set("get_survey_detail", "SELECT id,name FROM surveys where id='".$row_get_recent_entry['surveyid']."'");	
              $row_get_survey_detail = mysqli_fetch_assoc($get_survey_detail);
              $row_survey_entry = 1;
              record_set("survey_entry", "SELECT DISTINCT cby FROM answers where surveyid='".$row_get_survey_detail['id']."' and cby <".$row_get_recent_entry['cby']);
              $row_survey_entry = $totalRows_survey_entry+$row_survey_entry;
            ?>
                <tr class="<?php echo get_boostrap_bg_colors($i); ?>">
                <td><?php echo date("d-m-Y", strtotime($row_get_recent_entry['cdate'])); ?></td>
                <td><?php echo $row_get_survey_detail['name']; ?></td>
                <!--<td><?php echo $departments[$row_get_survey_detail['departmentid']]; ?></td>-->
                <td><?php echo ordinal($row_survey_entry); ?></td>
                <td>
                  <?php
                  record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_get_recent_entry['surveyid']."' and cby='".$row_get_recent_entry['cby']."'");
                  $total_result_val = $totalRows_get_survey_result*10;
                  $achieved_result_val = 0;
                  $to_bo_contacted = 0;
                  while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
                  $achieved_result_val += $row_get_survey_result['answerval'];
                  if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 10){
                    $to_bo_contacted = 1;
                  }
                  }
                  $result_response = $achieved_result_val*100/$total_result_val;
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
          <td>
            <a class="btn btn-xs btn-success" href="survey-result.php?surveyid=<?php echo $row_get_recent_entry['surveyid'];?>&userid=<?php echo $row_get_recent_entry['cby'];?>&contacted=1" target="_blank">User Response</a> &nbsp;
            <a class="btn btn-xs btn-info" href="survey-result.php?surveyid=<?php echo $row_get_recent_entry['surveyid'];?>" target="_blank">Survey Result</a>
            
          </td>
          </tr>
          <?php } ?>
          
          </tbody>
          <tfoot>
          <tr>
          <td>Date Time</td>
          <td>Survey</td>
          <!--<td>Department</td>-->
          <td>Response</td>
          <td>Result Score</td>
          <td>Requested Contact?</td>
          <td>Action</td>
          </tr>
          </tfoot>
          </table>
        </div>
      </div>
     </div>
    </div>
    
</section>
<link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
<script src="plugins/datatables/jquery.dataTables.min.js"></script> 
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script> 
<script>
  $(function () {
    $("#examples").DataTable({searching: false});
  });
</script>
<?php
  //get Survey Data
  $filter_query = '';
  $clients_array = array();
  $ykeys = "";
  $labels = "";
  record_set("GetDetails", "select id,name from surveys ".$filter_query);
  while($row_GetDetails = mysqli_fetch_assoc($GetDetails)){ 
    $clients_array[$row_GetDetails['id']] = $row_GetDetails['name'];
    $ykeys .= "'item".$row_GetDetails['id']."', ";
    $labels .= "'".$row_GetDetails['name']."', ";
  }
  //print_r($clients_array);
  //get date range
  $days30 = array();
  for($i = 0; $i < 30; $i++) {
    $days30item = date("d M Y", strtotime('-'. $i .' days'));
      $days30[] = date("d M Y", strtotime('-'. $i .' days'));
  }
  //print_r($days30);
  $final_chart_array = array();
  foreach($days30 as $days){
    $arra_txt = "";
    $arra_txt .= "{y: '".date("Y-m-d", strtotime($days))."', ";
    foreach($clients_array as $clientkey =>$client){
      record_set("Getcollectedamnt", "SELECT DISTINCT cby FROM answers where surveyid='".$clientkey."'  and cdate like '".date("Y-m-d", strtotime($days))."%' $locationQueryAndCondition");
      $row_survey_entry = $totalRows_Getcollectedamnt;
      $tamount = 0;
      if(!empty($row_survey_entry)){
        $tamount = $row_survey_entry;
      }
      $arra_txt .= "item".$clientkey.": ".$tamount.", ";
    }
    $arra_txt .= "},";
    //$days = date("Y M d", strtotime($days));
    $final_chart_array[$days]=$arra_txt;
  }
  $final_chart_array_item = implode(" ",$final_chart_array);

?>

<script language="javascript">
  /* Morris.js Charts */
  // Sales chart
  $(window).on('load', function() {
    var area = new Morris.Area({
      element: 'revenue-chart',
      resize: true,
      data: [
        <?php echo $final_chart_array_item;?>
          ],
      xkey: 'y',
      ykeys: [<?php echo $ykeys; ?>],
      labels: [<?php echo $labels; ?>],
      lineColors: ['#6600CC', '#FFCC00', '#FF9900', '#CC0000'],
      hideHover: 'auto'
    });
  });
</script>