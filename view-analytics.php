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
  $query .= " and answers.cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
}

  $perLocations = array();
  //Per Location Statistic
  record_set("per_location", "SELECT * FROM (SELECT COUNT(DISTINCT(surveyid)) AS total_surveys, locations.name AS location_name FROM answers LEFT JOIN locations ON answers.locationid = locations.id $locationJoinWhereCondition where 1=1  $query GROUP BY locationid ) AS NEWTABLE ORDER BY total_surveys DESC");

  $locationsChartlabels = array();
  $locationsChartData   = array();  
  while($row_per_location = mysqli_fetch_assoc($per_location)){
    $labels = ($row_per_location['location_name'])?$row_per_location['location_name']:'NA';
    $tSurvey = ($row_per_location['total_surveys'])?$row_per_location['total_surveys']:'';
    $locationsChartlabels[]  = $labels;
    $locationsChartData[]    = $tSurvey;
    $perLocations[$labels]  += $tSurvey; 
  }
  $perDepartments = array();
  //Per Department Statistic
  record_set("per_department", "SELECT * FROM ( SELECT COUNT(DISTINCT(surveyid)) AS total_surveys, departments.name AS department_name FROM answers LEFT JOIN departments ON answers.departmentid = departments.id where 1=1 $query  GROUP BY departmentid  ) AS NEWTABLE ORDER BY total_surveys DESC");

  $departmentChartlabels = array();
  $departmentChartData = array();
  while($row_per_department = mysqli_fetch_assoc($per_department)){
    $labels = ($row_per_department['department_name'])?$row_per_department['department_name']:'NA';
    $tSurvey = ($row_per_department['total_surveys'])?$row_per_department['total_surveys']:'';
    $departmentChartlabels[]  = $labels;
    $departmentChartData[]    = $tSurvey;
    $perDepartments[$labels]  += $tSurvey;
  }
   //Per Group Statistic
   $perGroups = array();
   record_set("per_group", "SELECT * FROM ( SELECT COUNT(DISTINCT(surveyid)) AS total_surveys, groups.name AS group_name FROM answers LEFT JOIN groups ON answers.groupid = groups.id where 1=1 $query  GROUP BY groupid  ) AS NEWTABLE ORDER BY total_surveys DESC");

   $groupChartlabels = array();
   $groupChartData = array();
   while($row_per_group = mysqli_fetch_assoc($per_group)){
    $labels = ($row_per_group['group_name'])?$row_per_group['group_name']:'NA';
    $tSurvey = ($row_per_group['total_surveys'])?$row_per_group['total_surveys']:'';
      
    $groupChartlabels[] = $labels;
    $groupChartData[]   = $tSurvey;
    $perGroups[$labels] += $tSurvey;
   }

  //Yearly Best & Worst Locations
  $currentYear = date('Y');
  $datasets = array("best", "worst");

  //Locations
  $location_labels = array();
  $best_locations = array();
  $best_location_labels = array();
  $best_location_score = array();
  $worst_locations = array();
  $worst_location_labels = array();
  $worst_location_score = array();
  

  //Departments
  $department_graph_data = array();
  $department_labels = array();
  $best_departments = array();
  $best_department_labels = array();
  $best_department_score = array();
  $worst_departments = array();
  $worst_department_labels = array();
  $worst_department_score = array();

  //Groups
  $group_graph_data = array();
  $group_labels = array();
  $best_groups = array();
  $best_group_labels = array();
  $best_group_score = array();
  $worst_groups = array();
  $worst_group_labels = array();
  $worst_group_score = array();
  foreach($datasets AS $dataset){
    if($dataset == 'best'){
      $order_by = 'DESC';
    }
    if($dataset == 'worst'){
      $order_by = 'ASC';
    }
    //Locations
    // $query_b = "";
    // if(isset($_POST['fdate']) && isset($_POST['sdate']) && isset($_POST['filter']) && !empty($_POST['fdate']) && !empty($_POST['sdate'])){
    //   $query_b .= " and cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
    // }

    record_set("average_survey_result","SELECT * FROM ( SELECT locationid, COUNT(answerval) AS survey_count, SUM(answerval) AS survey_val_sum FROM answers WHERE YEAR(cdate) = '$currentYear' $query GROUP BY locationid ) AS NEWTABLE ORDER BY survey_val_sum $order_by");
    $achieved_result_val = 0;
    if($totalRows_average_survey_result > 0){
      while($row_average_survey_result = mysqli_fetch_assoc($average_survey_result)){
        // echo '<pre>';
        // print_r($row_average_survey_result);
        // echo '</pre>';
        record_set("location_details", "select * from locations where id = '".$row_average_survey_result['locationid']."'");
        $row_location_details = mysqli_fetch_assoc($location_details);

        if($dataset == 'best'){
          $best_score = number_format((floatval($row_average_survey_result['survey_val_sum'] * 100) / floatval($row_average_survey_result['survey_count'] * 10)) / intval($totalRows_average_survey_result), 2);
          $locName = (trim($row_location_details['name']))?trim($row_location_details['name']):'NA';
          $best_locations[$locName] += $best_score;
          $best_location_labels[] = $locName;
          $best_location_score[] = $best_score;
        }

        if($dataset == 'worst'){
          $worst_score = number_format((floatval($row_average_survey_result['survey_val_sum'] * 100) / floatval($row_average_survey_result['survey_count'] * 10)) / intval($totalRows_average_survey_result), 2);
          $locName = (trim($row_location_details['name']))?trim($row_location_details['name']):'NA';
          $worst_locations[$locName] += $worst_score;
          $worst_location_labels[]   = $locName;
          $worst_location_score[]    = $worst_score;
        }
      }
    }
   
  arsort($best_locations);
  asort($worst_locations);
  // echo '<pre>';
  // print_r($worst_locations);
  // echo '</pre>';

  // die();
    //Departments
    record_set("average_department_result","SELECT * FROM ( SELECT departmentid, COUNT(answerval) AS survey_count, SUM(answerval) AS survey_val_sum FROM answers WHERE YEAR(cdate) = '$currentYear' $query GROUP BY departmentid ) AS NEWTABLE ORDER BY survey_val_sum $order_by ");
    if($totalRows_average_department_result > 0){
      while($row_average_department_result = mysqli_fetch_assoc($average_department_result)){
        record_set("department_details", "select * from departments where id = '".$row_average_department_result['departmentid']."'");
        $row_department_details = mysqli_fetch_assoc($department_details);
        if($dataset == 'best'){
          $best_dscore = number_format((floatval($row_average_department_result['survey_val_sum'] * 100) / floatval($row_average_department_result['survey_count'] * 10)) / intval($totalRows_average_department_result), 2);
          $deptName = (trim($row_department_details['name']))?trim($row_department_details['name']):'NA';
          $best_departments[$deptName] += $best_dscore;
          $best_department_labels[] = $deptName;
          $best_department_score[] = $best_dscore;
        }
        if($dataset == 'worst'){
          $worst_dscore = number_format((floatval($row_average_department_result['survey_val_sum'] * 100) / floatval($row_average_department_result['survey_count'] * 10)) / intval($totalRows_average_department_result), 2);
          $deptName = (trim($row_department_details['name']))?trim($row_department_details['name']):'NA';
          $worst_departments[$deptName] += $worst_dscore;
          $worst_department_labels[] = $deptName;
          $worst_department_score[] = $worst_dscore;
        }
      
      }
    }
    arsort($best_departments);
    asort($worst_departments);
      //Groups
      record_set("average_group_result","SELECT * FROM ( SELECT groupid, COUNT(answerval) AS survey_count, SUM(answerval) AS survey_val_sum FROM answers WHERE YEAR(cdate) = '$currentYear' $query GROUP BY groupid ) AS NEWTABLE ORDER BY survey_val_sum $order_by");
      if($totalRows_average_group_result > 0){
        while($row_average_group_result = mysqli_fetch_assoc($average_group_result)){
          record_set("group_details", "select * from groups where id = '".$row_average_group_result['groupid']."'");
          $row_group_details = mysqli_fetch_assoc($group_details);
          if($dataset == 'best'){
            $best_dscore = number_format((floatval($row_average_group_result['survey_val_sum'] * 100) / floatval($row_average_group_result['survey_count'] * 10)) / intval($totalRows_average_group_result), 2);
            $grpName = (trim($row_group_details['name']))?trim($row_group_details['name']):'NA';
            $best_groups[$grpName] += $best_dscore;
            $best_group_labels[]    = $grpName;
            $best_group_score[]     = $best_dscore;
            //$graph_data[$grpName] += $best_dscore;
          }
          if($dataset == 'worst'){
            $worst_dscore = number_format((floatval($row_average_group_result['survey_val_sum'] * 100) / floatval($row_average_group_result['survey_count'] * 10)) / intval($totalRows_average_group_result), 2);
            $grpName = (trim($row_group_details['name']))?trim($row_group_details['name']):'NA';
            $worst_groups[$grpName] += $worst_dscore;
            $worst_group_labels[] = $grpName;
            $worst_group_score[] = $worst_dscore;
          }
        }
      }
  }

  arsort($best_group_labels);
  asort($worst_group_score);
  // echo '<pre>';
  // print_r($best_group_labels); 
  // print_r($best_group_score); 
  // print_r($graph_data); 
  // die();
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
                $countLocation = $perLocations;
                unset($countLocation['NA']);
                if($countLocation){ ?>
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
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                          if($countLocation){
                            foreach($perLocations AS $locationName => $locationSurvey){ ?>
                              <tr>
                                <th scope="row" style="text-align: left;"><?php echo $locationName; ?></th>
                                <td style="text-align: left;"><?php echo $locationSurvey; ?></td>
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
                <div class="row">
                  <div class="box-header with-border">
                    <h3 class="box-title">Best Survey Locations Of Year</h3>
                  </div>
                  <div class="col-md-8">
                    <?php if($best_locations){ ?>
                    <canvas id="yearBestLocationChart"></canvas>
                  </div>
                  <div class="col-md-4 listing">  
                    <table class="table">
                        <thead class="thead-dark">
                          <tr>
                              <th scope="col" style="text-align: left;">Location Name</th>
                              <th scope="col" style="text-align: left;">Average Score</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                          if($best_locations){
                              foreach($best_locations AS $key => $value){ ?>
                              <tr>
                                  <th scope="row" style="text-align: left;"><?php echo $key; ?></th>
                                  <td style="text-align: left;"><?php echo $value; ?></td>
                              </tr>
                              <?php } ?>
                          <?php }else{ ?>
                              <tr>
                              <td colspan="2">No Data Found.</td>
                              </tr>
                          <?php } ?>
                        </tbody>
                    </table> 
                    <?php }else { ?>
                       <p>No Data Found.</p>
                    <?php } ?>        
                  </div>
                </div>
                
                <div class="row"> 
                  <div class="box-header with-border">
                    <h3 class="box-title">Worst Survey Locations Of Year</h3>
                  </div> 
                  <div class="col-md-8">
                    <?php if($worst_locations){ ?>
                      <canvas id="yearWorstLocationChart"></canvas>
                  </div>
                  <div class="col-md-4 listing">    
                    <table class="table">
                        <thead class="thead-dark">
                          <tr>
                              <th scope="col" style="text-align: left;">Location Name</th>
                              <th scope="col" style="text-align: left;">Average Score</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                          if($worst_locations){
                            foreach($worst_locations AS $key => $value){ ?>
                            <tr>
                                <th scope="row" style="text-align: left;"><?php echo $key; ?></th>
                                <td style="text-align: left;"><?php echo $value; ?></td>
                            </tr>
                            <?php } ?>
                          <?php }?>
                        </tbody>
                    </table>
                    <?php }else{ ?>
                        <p>No Data Dound.</p>
                    <?php } ?>
                  </div>
                </div>
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
                $countDepartment = $perDepartments;
                unset($countDepartment['NA']);
                if($countDepartment){ ?>
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
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                          if($perDepartments){
                            foreach($perDepartments AS $departmentName => $departmentSurvey){ ?>
                              <tr>
                                <th scope="row" style="text-align: left;"><?php echo $departmentName; ?></th>
                                  <td style="text-align: left;"><?php echo $departmentSurvey; ?></td>
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
                  <div class="row">
                    <div class="box-header with-border">
                      <h3 class="box-title">Best Survey Departments Of Year</h3>
                    </div>
                    <div class="col-md-8">
                      <?php if($best_departments){ ?>
                      <canvas id="yearBestDepartmentChart"></canvas>
                    </div>  
                    <div class="col-md-4">
                      <table class="table">
                          <thead class="thead-dark">
                            <tr>
                                <th scope="col" style="text-align: left;">Department Name</th>
                                <th scope="col" style="text-align: left;">Average Score</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php 
                            if($best_departments){
                              foreach($best_departments AS $key => $value){ ?>
                                <tr>
                                  <th scope="row" style="text-align: left;"><?php echo $key; ?></th>
                                  <td style="text-align: left;"><?php echo $value; ?></td>
                                </tr>
                              <?php } ?>
                            <?php }?>
                          </tbody>
                      </table> 
                      <?php }else{ ?>
                        <p>No Data Dound.</p>
                      <?php } ?>       
                    </div>
                  </div>
                  <div class="row"> 
                    <div class="box-header with-border">
                      <h3 class="box-title">Worst Survey Departments Of Year</h3>
                    </div> 
                    <div class="col-md-8">
                      <?php if($worst_departments){ ?>
                        <canvas id="yearWorstDepartmentChart"></canvas>
                    </div>
                    <div class="col-md-4 listing">
                        <table class="table">
                            <thead class="thead-dark">
                              <tr>
                                <th scope="col" style="text-align: left;">Department Name</th>
                                <th scope="col" style="text-align: left;">Average Score</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php 
                              if($worst_departments){
                                foreach($worst_departments AS $key => $value){ ?>
                                  <tr>
                                    <th scope="row" style="text-align: left;"><?php echo $key; ?></th>
                                    <td style="text-align: left;"><?php echo $value; ?></td>
                                  </tr>
                                <?php } ?>
                              <?php }?>
                            </tbody>
                        </table>
                      <?php }else{ ?>
                        <p>No Data Dound.</p>
                      <?php } ?>
                    </div>
                  </div>
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
                $countGroup = $perGroups;
                unset($countGroup['NA']);
                //print_r($countGroup);
                if($countGroup){ ?>
                  <div class="row">
                    <div class="col-sm-8">
                      <canvas id="groupChart"></canvas>
                    </div>
                    <div class="col-sm-4 listing">
                      <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col" style="text-align: left;">Group Name</th>
                            <th scope="col" style="text-align: left;">Total Surveys</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                          if($perGroups){
                            foreach($perGroups AS $groupName => $groupSurvey){ ?>
                              <tr>
                                  <th scope="row" style="text-align: left;"><?php echo $groupName; ?></th>
                                  <td style="text-align: left;"><?php echo $groupSurvey; ?></td>
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
                  <div class="row">
                    <div class="box-header with-border">
                      <h3 class="box-title">Best Survey Group Of Year</h3>
                    </div>
                    <div class="col-md-8">
                      <?php if($best_group_score){ ?>
                      <canvas id="yearBestGroupChart"></canvas>
                    </div>
                    <div class="col-md-4 listing">      
                      <table class="table">
                          <thead class="thead-dark">
                            <tr>
                                <th scope="col" style="text-align: left;">Group Name</th>
                                <th scope="col" style="text-align: left;">Average Score</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php 
                            if($best_groups){
                              foreach($best_groups AS $key => $value){ ?>
                                <tr>
                                  <th scope="row" style="text-align: left;"><?php echo $key; ?></th>
                                  <td style="text-align: left;"><?php echo $value; ?></td>
                                </tr>
                              <?php } ?>
                            <?php }else{ ?>
                                <tr>
                                <td colspan="2">No Data Found.</td>
                                </tr>
                            <?php } ?>
                          </tbody>
                      </table> 
                      <?php }else { ?>
                        <p>No Data Found.</p>
                      <?php } ?>        
                    </div>
                  </div>
                  <div class="row">
                    <div class="box-header with-border">
                      <h3 class="box-title">Worst Survey Group Of Year</h3>
                    </div>
                    <div class="col-md-8">
                      <?php if($worst_group_score){ ?>
                        <canvas id="yearWorstGroupChart"></canvas>
                    </div>
                    <div class="col-md-4 listing">   
                        <table class="table">
                            <thead class="thead-dark">
                              <tr>
                                <th scope="col" style="text-align: left;">Group Name</th>
                                <th scope="col" style="text-align: left;">Average Score</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php 
                              if($worst_groups){
                                foreach($worst_groups AS $key => $value){ ?>
                                  <tr>
                                    <th scope="row" style="text-align: left;"><?php echo $key; ?></th>
                                    <td style="text-align: left;"><?php echo $value; ?></td>
                                  </tr>
                                <?php } ?>
                              <?php }?>
                            </tbody>
                        </table>
                      <?php }else { ?>
                        <p>No Data Found.</p>
                      <?php } ?>
                    </div>
                  </div>
                <?php }else { ?>
                  <p>No Data Found.</p>
                <?php } ?>  
              </div>
            </div>
          </div>
      </div>
    </div>
</section>

<?php 
// echo $color_implode = '"'.implode('","',generate_unique_color(count($perLocations))).'"'; die();

?>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>
<script type="text/javascript">

let best_locations          =  '<?=count($perLocations)?>';
let best_locations_length   =  '<?=count($best_locations)?>';
let worth_locations_length  =  '<?=count($worst_locations)?>';

//for location
if(best_locations>0){
  var locationChartCtx = document.getElementById('locationChart').getContext('2d');   
  var locationChart = new Chart(locationChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($perLocations)); ?>,
      datasets: [
          {
            backgroundColor: [<?='"'.implode('","',generate_unique_color(count($perLocations))).'"'?>],
            data: <?php echo json_encode(array_values($perLocations)); ?>
          }
      ]
    }
  }); 
}

if(best_locations_length>0){
  var yearBestLocationChartCtx = document.getElementById('yearBestLocationChart').getContext('2d');   
  var yearBestLocationChart = new Chart(yearBestLocationChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($best_locations)); ?>,
      datasets: [
          {
            backgroundColor: [
              <?='"'.implode('","',generate_unique_color(count($best_locations))).'"'?>
            ],
            data: <?php echo json_encode(array_values($best_locations)); ?>
          }
      ]
    },
    options: {
      title: {
        display: true,
        text: 'Year Best Locations'
      }
    }
  });
}
if(worth_locations_length>0){
  var yearWorstLocationChartCtx = document.getElementById('yearWorstLocationChart').getContext('2d');   
  var yearWorstLocationChart = new Chart(yearWorstLocationChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($worst_locations)); ?>,
      datasets: [
          {
            backgroundColor: [
              <?='"'.implode('","',generate_unique_color(count($worst_locations))).'"'?>
            ],
            data: <?php echo json_encode(array_values($worst_locations)); ?>
          }
      ]
    },
    options: {
      title: {
        display: true,
        text: 'Year Worst Locations'
      }
    }
  });
}


// for department
let best_departments          =  '<?=count($perDepartments)?>';
let best_departments_length   =  '<?=count($best_departments)?>';
let worth_departments_length  =  '<?=count($worst_departments)?>';

if(best_departments>0){
  var departmentChartCtx = document.getElementById('departmentChart').getContext('2d');  
  var departmentChart = new Chart(departmentChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($perDepartments)); ?>,
      datasets: [
          {
            backgroundColor: [
              <?='"'.implode('","',generate_unique_color(count($perDepartments))).'"'?>
            ],
            data: <?php echo json_encode(array_values($perDepartments)); ?>
          }
      ]
    }
  });
}

if(best_departments_length>0){
  var yearBestDepartmentChartCtx = document.getElementById('yearBestDepartmentChart').getContext('2d');   
  var yearBestDepartmentChart = new Chart(yearBestDepartmentChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($best_departments)); ?>,
      datasets: [
          {
            backgroundColor: [
              <?='"'.implode('","',generate_unique_color(count($best_departments))).'"'?>
            ],
            data: <?php echo json_encode(array_values($best_departments)); ?>
          }
      ]
    },
    options: {
      title: {
        display: true,
        text: 'Year Best Departments'
      }
    }
  });
}

if(worth_departments_length>0){
  var yearWorstDepartmentChartCtx = document.getElementById('yearWorstDepartmentChart').getContext('2d');   
  var yearWorstDepartmentChart = new Chart(yearWorstDepartmentChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($worst_departments)); ?>,
      datasets: [
          {
            backgroundColor: [
              <?='"'.implode('","',generate_unique_color(count($worst_departments))).'"'?>
            ],
            data: <?php echo json_encode(array_values($worst_departments)); ?>
          }
      ]
    },
    options: {
      title: {
        display: true,
        text: 'Year Worst Departments'
      }
    }
  });
}


// for group
let best_groups          =  '<?=count($perGroups)?>';
let best_groups_length   =  '<?=count($best_groups)?>';
let worth_groups_length  =  '<?=count($worst_groups)?>';

if(best_groups>0){
  var groupChartCtx = document.getElementById('groupChart').getContext('2d');   
  var groupChart = new Chart(groupChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($perGroups)); ?>,
      datasets: [
          {
            backgroundColor: [
              <?='"'.implode('","',generate_unique_color(count($perGroups))).'"'?>
            ],
            data: <?php echo json_encode(array_values($perGroups)); ?>
          }
      ]
    }
  });
}

if(best_groups_length>0){
  var yearBestGroupChartCtx = document.getElementById('yearBestGroupChart').getContext('2d');   
  var yearBestGroupChart = new Chart(yearBestGroupChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($best_groups)); ?>,
      datasets: [
          {
            backgroundColor: [
              <?='"'.implode('","',generate_unique_color(count($best_groups))).'"'?>
            ],
            data: <?php echo json_encode(array_values($best_groups)); ?>
          }
      ]
    },
    options: {
      title: {
        display: true,
        text: 'Year Best Groups'
      }
    }
  });
}

if(worth_groups_length>0){
  var yearWorstGroupChartCtx = document.getElementById('yearWorstGroupChart').getContext('2d');   
  var yearWorstGroupChart = new Chart(yearWorstGroupChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode(array_keys($worst_groups)); ?>,
      datasets: [
          {
            backgroundColor: [
              <?='"'.implode('","',generate_unique_color(count($worst_groups))).'"'?>
            ],
            data: <?php echo json_encode(array_values($worst_groups)); ?>
          }
      ]
    },
    options: {
      title: {
        display: true,
        text: 'Year Worst Groups'
      }
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