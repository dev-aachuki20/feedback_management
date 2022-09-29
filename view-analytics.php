<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css"/>
<style>
.listing::-webkit-scrollbar-button {
  height: 12px;
}
.listing::-webkit-scrollbar {
    width: 12px;
}
/* Track */
.listing::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px #c0c0c0;
    -webkit-border-radius: 10px;
    border-radius: 10px;
}
/* Handle */
.listing::-webkit-scrollbar-thumb {
    -webkit-border-radius: 10px;
    border-radius: 10px;
    background: #c0c0c0;
    -webkit-box-shadow: inset 0 0 6px #c0c0c0;
}
.listing::-webkit-scrollbar-thumb:window-inactive {
  background: #c0c0c0;
}

</style>
<?php
$query = "";
if(isset($_POST['survey_id'])&& !empty($_POST['survey_id'])){
  $query .= " and  surveyid = ".$_POST['survey_id'];
}
if(isset($_POST['fdate']) && isset($_POST['sdate']) && isset($_POST['filter']) && !empty($_POST['fdate']) && !empty($_POST['sdate'])){
  $query .= " and cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
}

  //get no. of survey of all locations
  $perLocations = array();
  record_set("per_location", "SELECT DISTINCT(surveyid),locationid FROM `answers` WHERE `locationid` in (select id from locations where cstatus=1) $query ORDER BY `locationid` ASC");

  while($row_per_location = mysqli_fetch_assoc($per_location)){
      record_set("survey_data", "SELECT * FROM answers where id!=0 and surveyid =".$row_per_location['surveyid']." and locationid =".$row_per_location['locationid']);
      $count = 0;
      $answerval=0;
      while($row_survey_data = mysqli_fetch_assoc($survey_data)){
        $count++;
        $answerval +=  $row_survey_data['answerval'];
      }
      $avgresult = $answerval/$count;
      $perLocations[$row_per_location['locationid']][$row_per_location['surveyid']]['result'] =$avgresult;
      $perLocations[$row_per_location['locationid']][$row_per_location['surveyid']]['count'] =$count;
  }

  $finial_data_location = array();
  $locSurveyScore = array();
  foreach($perLocations as $key => $value){
    $total_loc_result = 0;
    $count_number = 0;
    foreach($value as $survey_result){
      $count_number++;
      $total_loc_result += $survey_result['result'];
    }
    $avg_score = $total_loc_result/$count_number;
    $avg_score = round($avg_score, 2);
    $finial_data_location[getLocation()[$key]]['count']  = $count_number;
    $finial_data_location[getLocation()[$key]]['result'] = $avg_score;
    $locSurveyScore[getLocation()[$key]] = $avg_score;
    $overall_location_result += $avg_score;
    $overall_loc_response_no +=$count_number; 
  }

//get no. of survey of all department
$perDepartments = array();
record_set("per_department", "SELECT DISTINCT(surveyid),departmentid FROM `answers` WHERE `departmentid` in (select id from departments where cstatus=1) $query ORDER BY `departmentid` ASC");
while($row_per_department = mysqli_fetch_assoc($per_department)){
    record_set("survey_data_dept", "SELECT * FROM answers where id!=0 and surveyid =".$row_per_department['surveyid']." and departmentid =".$row_per_department['departmentid']);
    $count = 0;
    $answerval=0;
    while($row_survey_data_dept = mysqli_fetch_assoc($survey_data_dept)){
      $count++;
      $answerval +=  $row_survey_data_dept['answerval'];
    }
    $avgresult = $answerval/$count;
    $perDepartments[$row_per_department['departmentid']][$row_per_department['surveyid']]['result'] =$avgresult;
    $perDepartments[$row_per_department['departmentid']][$row_per_department['surveyid']]['count'] = $count;
}

$finial_data_department = array();
$deptSurveyScore = array();
foreach($perDepartments as $key => $value){
  $total_dep_result = 0;
  $count_number = 0;
  foreach($value as $survey_result){
    $count_number++;
    $total_dep_result += $survey_result['result'];
  }
  $avg_score = $total_dep_result/$count_number;
  $finial_data_department[getDepartment()[$key]]['count']  = $count_number;
  $finial_data_department[getDepartment()[$key]]['result'] = round($avg_score, 2);
  $deptSurveyScore[getDepartment()[$key]] = round($avg_score, 2);
  $overall_dept_response_no +=$count_number; 
}
//get no. of survey of all group
$perGroups = array();
record_set("per_group", "SELECT DISTINCT(surveyid),groupid FROM `answers` WHERE `groupid` in (select id from groups where cstatus=1) $query ORDER BY `groupid` ASC");

while($row_per_group = mysqli_fetch_assoc($per_group)){
    record_set("survey_data_grp", "SELECT * FROM answers where id!=0 and surveyid =".$row_per_group['surveyid']." and departmentid =".$row_per_group['groupid']);
    $count = 0;
    $answerval=0;
    while($row_survey_data_grp = mysqli_fetch_assoc($row_survey_data_grp)){
      $count++;
      $answerval +=  $row_survey_data_grp['answerval'];
    }
    $avgresult = $answerval/$count;
    $perGroups[$row_per_group['groupid']][$row_per_group['surveyid']]['result'] =$avgresult;
    $perGroups[$row_per_group['groupid']][$row_per_group['surveyid']]['count'] = $count;
}

$finial_data_group = array();
$grpSurveyScore =array();
foreach($perGroups as $key => $value){
  $total_grp_result = 0;
  $count_number = 0;
  foreach($value as $survey_result){
    $count_number++;
    $total_grp_result += $survey_result['result'];
  }
  $avg_score = $total_grp_result/$count_number;
  $finial_data_group[getGroup()[$key]]['count']  = $count_number;
  $finial_data_group[getGroup()[$key]]['result'] = round($avg_score, 2);
  $grpSurveyScore[getGroup()[$key]] = round($avg_score, 2);
  $overall_grp_response_no +=$count_number; 
}
?>
<style>
  #exportPDF{
    text-decoration: none;
    background-color: deepskyblue;
    color: white;
    padding: 5px;
    font-size: 16px;
    margin-bottom: 18px;
  }
</style>
<section class="content-header">
  <h1>ANALYTICS</h1>
</section>
<section class="content">
  <div class="box">
    <div class="box-body">
      <form action="" method="post">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Survey</label>
              <select name="survey_id" class="form-control form-control-lg contact" id="">
                <option value="">select survey</option>
                <?php 
                // survey by user
                $surveyByUsers = get_filter_data_by_user('surveys');

                foreach($surveyByUsers as $surveyData){ 
                  $surveyId   = $surveyData['id'];
                  $surveyName = $surveyData['name'];
                ?>
                  <option value="<?=$surveyId?>" <?php if($surveyId==$_POST['survey_id']) {echo 'selected';}?>><?=$surveyName?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Start Date</label>
              <input type="date"  name="fdate" min ="2000-01-01" max="<?= date('Y-m-d'); ?>" class="form-control" value="<?php echo $_POST['fdate']; ?>"/>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>End Date</label>
              <input type="date"  name="sdate" class="form-control" min ="2000-01-01" max="<?= date('Y-m-d'); ?>" value="<?php echo $_POST['sdate']; ?>"/>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>&nbsp;</label>
              <input type="submit" name="filter" class="btn btn-primary btn-block" value="Filter"/>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <a class="btn btn-xs btn-info " id="exportPDF" href="#">Export PDF</a>  
    <div id="reportPage">
      <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">Surveys Per Location</h3>
                <div class="box-tools pull-right" style="top:-4px !important;">
                  <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <div class="box-body " style="display: block;">
                <?php 
                if(count($finial_data_location)>0){ ?>
                <div class="row" style="text-align:center;margin-bottom: 20px;">
                  <div class="col-md-6">
                     <div class="col-6"><strong>Total Survey Response(Overall)</strong></div>
                     <div class="col-6"><strong><?=$overall_loc_response_no?></strong></div>
                  </div>
                  <div class="col-md-6">
                    <div class="col-6"><strong>Average Survey Score(Overall)</strong></div>
                    <div class="col-6"><strong><?=round(array_sum($locSurveyScore)/count($locSurveyScore),2) ?> %</strong></div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-sm-8">
                      <canvas id="locationChart"></canvas>
                    </div>
                    <div class="col-sm-4 listing">
                      <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col" style="text-align: left;">Location Name</th>
                            <th scope="col" style="text-align: left;">Total Surveys</th>
                            <th scope="col" style="text-align: left;">Average Score</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                          if($finial_data_location){
                            foreach($finial_data_location AS $key => $value){ ?>
                              <tr>
                                <th scope="row" style="text-align: left;"><?php echo $key; ?></th>
                                <td style="text-align: left;"><?php echo $value['count']; ?></td>
                                <td style="text-align: left;"><?php echo $value['result']; ?> %</td>
                              </tr>
                            <?php } ?>
                          <?php }else{ ?>
                            <tr>
                              <td colspan="2">No Data Found.</td>
                            </tr>  
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>
                </div>
                <hr style="border: 0.5px solid #e8e3e3;"/>
                <?php }else{ ?>
                    <div class="col-md-12">
                      <p>No Data Found.</p>
                    </div>
                    <?php } ?>
              </div>
            </div>
          </div>

          <!-- for department survey-->
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">Surveys Per Department</h3>
                <div class="box-tools pull-right" style="top:-4px !important;">
                  <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <div class="box-body " style="display: block;">
               
                <?php 
                if(count($finial_data_department)>0){ ?>
                  <div class="row" style="text-align:center;margin-bottom: 20px;">
                    <div class="col-md-6">
                      <div class="col-6"><strong>Total Survey Response(Overall)</strong></div>
                      <div class="col-6"><strong><?=$overall_dept_response_no?></strong></div>
                    </div>
                    <div class="col-md-6">
                      <div class="col-6"><strong>Average Survey Score(Overall)</strong></div>
                      <div class="col-6"><strong><?=round(array_sum($deptSurveyScore)/count($deptSurveyScore),2) ?> %</strong></div>
                    </div>
                  </div>  
                  <div class="row">
                    <div class="col-sm-8">
                      <canvas id="departmentChart"></canvas>
                    </div>
                    <div class="col-sm-4 listing">
                      <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col" style="text-align: left;">Department Name</th>
                            <th scope="col" style="text-align: left;">Total Surveys</th>
                            <th scope="col" style="text-align: left;">Average Score</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                          if($finial_data_department){
                            foreach($finial_data_department AS $key => $value){ ?>
                              <tr>
                                <th scope="row" style="text-align: left;"><?php echo $key; ?></th>
                                  <td style="text-align: left;"><?php echo $value['count']; ?></td>
                                  <td style="text-align: left;"><?php echo $value['result']; ?> %</td>
                                </tr>
                            <?php } ?>
                          <?php }else{ ?>
                            <tr>
                              <td colspan="2">No Data Found.</td>
                            </tr>  
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <hr style="border: 0.5px solid #e8e3e3;"/>
                <?php }else{ ?>
                  <p>No Data Dound.</p>
                <?php } ?> 
              </div>
            </div>
          </div>

          <!-- for group survey-->
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">Surveys Per Group</h3>
                <div class="box-tools pull-right" style="top:-4px !important;">
                  <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
              </div>

              <div class="box-body " style="display: block;">
                <?php 
                if($finial_data_group){ ?>
                  <div class="row">
                    <div class="row" style="text-align:center;margin-bottom: 20px;">
                      <div class="col-md-6">
                        <div class="col-6"><strong>Total Survey Response(Overall)</strong></div>
                        <div class="col-6"><strong><?=$overall_dept_response_no?></strong></div>
                      </div>
                      <div class="col-md-6">
                        <div class="col-6"><strong>Average Survey Score(Overall)</strong></div>
                        <div class="col-6"><strong><?=round(array_sum($grpSurveyScore)/count($grpSurveyScore),2) ?> %</strong></div>
                      </div>
                    </div> 
                    <div class="col-sm-8">
                      <canvas id="groupChart"></canvas>
                    </div>
                    <div class="col-sm-4 listing">
                      <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col" style="text-align: left;">Group Name</th>
                            <th scope="col" style="text-align: left;">Total Surveys</th>
                            <th scope="col" style="text-align: left;">Average Score</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                          if($finial_data_group){
                            foreach($finial_data_group AS $key => $value){ ?>
                              <tr>
                                  <th scope="row" style="text-align: left;"><?php echo $key; ?></th>
                                  <td style="text-align: left;"><?php echo $value['count']; ?></td>
                                  <td style="text-align: left;"><?php echo $value['result']; ?> %</td>
                              </tr>
                            <?php } ?>
                          <?php }else{ ?>
                            <tr>
                              <td colspan="2">No Data Found.</td>
                            </tr>  
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <hr style="border: 0.5px solid #e8e3e3;"/>
                <?php }else { ?>
                  <p>No Data Found.</p>
                <?php } ?>  
              </div>
            </div>
          </div>
      </div>
    </div>
</section>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>
<script type="text/javascript">

let best_locations  =  '<?=count($locSurveyScore)?>';
//for location
if(best_locations>0){
  var locationChartCtx = document.getElementById('locationChart').getContext('2d');   
  var locationChart = new Chart(locationChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($locSurveyScore)); ?>,
      datasets: [
          {
            backgroundColor: [<?='"'.implode('","',generate_unique_color(count($perLocations))).'"'?>],
            data: <?php echo json_encode(array_values($locSurveyScore)); ?>
          }
      ]
    }
  }); 
}

// for department
let best_departments  =  '<?=count($perDepartments)?>';

if(best_departments>0){
  var departmentChartCtx = document.getElementById('departmentChart').getContext('2d');  
  var departmentChart = new Chart(departmentChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($deptSurveyScore)); ?>,
      datasets: [
          {
            backgroundColor: [
              <?='"'.implode('","',generate_unique_color(count($deptSurveyScore))).'"'?>
            ],
            data: <?php echo json_encode(array_values($deptSurveyScore)); ?>
          }
      ]
    }
  });
}

// for group
let best_groups =  '<?=count($deptSurveyScore)?>';

if(best_groups>0){
  var groupChartCtx = document.getElementById('groupChart').getContext('2d');   
  var groupChart = new Chart(groupChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($deptSurveyScore)); ?>,
      datasets: [
          {
            backgroundColor: [
              <?='"'.implode('","',generate_unique_color(count($deptSurveyScore))).'"'?>
            ],
            data: <?php echo json_encode(array_values($deptSurveyScore)); ?>
          }
      ]
    }
  });
}

</script>
<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>
<!-- Resources -->
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-html2canvas@latest/dist/jspdf-html2canvas.min.js"></script> 
<script>
    // start export pdf 
    const pages = document.getElementById('reportPage');
    $('#exportPDF').click(function(){
        html2PDF(pages, {
            margin: [50,50,50,50],
            //margin: [20,20],//PDF margin (in jsPDF units). Can be a single number, [vMargin, hMargin], or [top, left, bottom, right].
            jsPDF: {
                orientation: "p",
                unit: "in",
                format: 'letter',
            },
            html2canvas: { scale: 2 },
            imageType: 'image/jpeg',
            output: './pdf/<?=date('Y-m-d-H-i-s')?>.pdf'
        });
    });

 // End export pdf
 </script>