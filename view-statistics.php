<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css"/>

<?php
  $query = "";
  if(isset($_POST['fdate']) && isset($_POST['sdate']) && isset($_POST['filter'])){
    $query .= " where answers.cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
  }

  $perLocations = array();
  //Per Location Statistic
  record_set("per_location", "SELECT * FROM (SELECT COUNT(DISTINCT(surveyid)) AS total_surveys, locations.name AS location_name FROM answers LEFT JOIN locations ON answers.locationid = locations.id $locationJoinWhereCondition  $query GROUP BY locationid LIMIT 5) AS NEWTABLE ORDER BY total_surveys DESC");
  $locationsChartlabels = array();
  $locationsChartData = array();  
  while($row_per_location = mysqli_fetch_assoc($per_location)){
    $locationsChartlabels[] = $row_per_location['location_name'];
    $locationsChartData[] = $row_per_location['total_surveys'];
    $perLocations[trim($row_per_location['location_name'])] = $row_per_location['total_surveys']; 
  }


  $perDepartments = array();
  //Per Department Statistic
  record_set("per_department", "SELECT * FROM ( SELECT COUNT(DISTINCT(surveyid)) AS total_surveys, departments.name AS department_name FROM answers LEFT JOIN departments ON answers.departmentid = departments.id  $query  GROUP BY departmentid LIMIT 5 ) AS NEWTABLE ORDER BY total_surveys DESC");

  $departmentChartlabels = array();
  $departmentChartData = array();
  while($row_per_department = mysqli_fetch_assoc($per_department)){
    $departmentChartlabels[] = $row_per_department['department_name'];
    $departmentChartData[] = $row_per_department['total_surveys'];
    $perDepartments[$row_per_department['department_name']] = $row_per_department['total_surveys'];
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
  $department_labels = array();
  $best_departments = array();
  $best_department_labels = array();
  $best_department_score = array();
  $worst_departments = array();
  $worst_department_labels = array();
  $worst_department_score = array();

  foreach($datasets AS $dataset){
    if($dataset == 'best'){
      $order_by = 'DESC';
    }
    if($dataset == 'worst'){
      $order_by = 'ASC';
    }
    //Locations
    $query_b = "";
    if(isset($_POST['fdate']) && isset($_POST['sdate']) && isset($_POST['filter'])){
      $query_b .= " and cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
    }

    record_set("average_survey_result","SELECT * FROM ( SELECT locationid, COUNT(answerval) AS survey_count, SUM(answerval) AS survey_val_sum FROM answers WHERE YEAR(cdate) = '$currentYear' $query_b GROUP BY locationid ) AS NEWTABLE ORDER BY survey_val_sum $order_by LIMIT 5");
    $achieved_result_val = 0;
    if($totalRows_average_survey_result > 0){
      while($row_average_survey_result = mysqli_fetch_assoc($average_survey_result)){
        record_set("location_details", "select * from locations where id = '".$row_average_survey_result['locationid']."'");
        $row_location_details = mysqli_fetch_assoc($location_details);

        if($dataset == 'best'){
          $best_score = number_format((floatval($row_average_survey_result['survey_val_sum'] * 100) / floatval($row_average_survey_result['survey_count'] * 10)) / intval($totalRows_average_survey_result), 2);
          $best_locations[trim($row_location_details['name'])] = $best_score;
          $best_location_labels[] = trim($row_location_details['name']);
          $best_location_score[] = $best_score;
        }

        if($dataset == 'worst'){
          $worst_score = number_format((floatval($row_average_survey_result['survey_val_sum'] * 100) / floatval($row_average_survey_result['survey_count'] * 10)) / intval($totalRows_average_survey_result), 2);
          $worst_locations[trim($row_location_details['name'])] = $worst_score;
          $worst_location_labels[] = trim($row_location_details['name']);
          $worst_location_score[] = $worst_score;
        }
      }
    }
  
    //Departments
    record_set("average_department_result","SELECT * FROM ( SELECT departmentid, COUNT(answerval) AS survey_count, SUM(answerval) AS survey_val_sum FROM answers WHERE YEAR(cdate) = '$currentYear' $query_b GROUP BY departmentid ) AS NEWTABLE ORDER BY survey_val_sum $order_by LIMIT 5");
    if($totalRows_average_department_result > 0){
      while($row_average_department_result = mysqli_fetch_assoc($average_department_result)){
        record_set("department_details", "select * from departments where id = '".$row_average_department_result['departmentid']."'");
        $row_department_details = mysqli_fetch_assoc($department_details);
        if($dataset == 'best'){
          $best_dscore = number_format((floatval($row_average_department_result['survey_val_sum'] * 100) / floatval($row_average_department_result['survey_count'] * 10)) / intval($totalRows_average_department_result), 2);
          $best_departments[trim($row_department_details['name'])] = $best_dscore;
          $best_department_labels[] = trim($row_department_details['name']);
          $best_department_score[] = $best_dscore;
        }
        if($dataset == 'worst'){
          $worst_dscore = number_format((floatval($row_average_department_result['survey_val_sum'] * 100) / floatval($row_average_department_result['survey_count'] * 10)) / intval($totalRows_average_department_result), 2);
          $worst_departments[trim($row_department_details['name'])] = $worst_dscore;
          $worst_department_labels[] = trim($row_department_details['name']);
          $worst_department_score[] = $worst_dscore;
        }
      }
    }
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
  <h1>Statistics</h1>
</section>
<section class="content">
  <div class="box">
    <div class="box-body">
      <form action="" method="post">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Start Date</label>
              <input type="date" required name="fdate" class="form-control" value="<?php //echo date('Y-m-d', strtotime('-1 months')); ?>"/>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>End Date</label>
              <input type="date" required name="sdate" class="form-control" value="<?php //echo date('Y-m-d'); ?>"/>
            </div>
          </div>
          <div class="col-md-4">
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
        <div class="col-lg-12">
          <div class="box">
            <div class="box-body">
              <div class="row">
                <div class="col-sm-6">
                  <h4>Surveys Per Location</h4>
                  <?php if($locationsChartData){ ?>
                    <canvas id="locationChart"></canvas>
                  <?php }else{ ?>
                    <p>No Data Found.</p>
                  <?php } ?>
                </div>
                <div class="col-sm-6">
                  <h4>Surveys Per Department</h4>
                  <?php if($departmentChartData){ ?>
                    <canvas id="departmentChart"></canvas>
                  <?php }else{ ?>
                    <p>No Data Found.</p>
                  <?php } ?>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-6">
                  <table class="table">
                    <thead class="thead-dark">
                      <tr>
                        <th scope="col" style="text-align: center;">Location Name</th>
                        <th scope="col" style="text-align: center;">Total Surveys</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      if($perLocations){
                        foreach($perLocations AS $locationName => $locationSurvey){ ?>
                          <tr>
                            <th scope="row" style="text-align: center;"><?php echo $locationName; ?></th>
                            <td style="text-align: center;"><?php echo $locationSurvey; ?></td>
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
                <div class="col-sm-6">
                  <table class="table">
                    <thead class="thead-dark">
                      <tr>
                        <th scope="col" style="text-align: center;">Department Name</th>
                        <th scope="col" style="text-align: center;">Total Surveys</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      if($perDepartments){
                        foreach($perDepartments AS $departmentName => $departmentSurvey){ ?>
                          <tr>
                            <th scope="row" style="text-align: center;"><?php echo $departmentName; ?></th>
                              <td style="text-align: center;"><?php echo $departmentSurvey; ?></td>
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
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="box">
            <div class="box-body">
              <div class="row">
                <div class="col-sm-6">
                  <h4>Best & Worst Survey Locations Of Year</h4>
                <div>
                <?php if($best_location_score){ ?>
                  <canvas id="yearBestLocationChart"></canvas>
                <?php }else{ ?>
                  <p>No Data Found.</p>
                <?php } ?>
                <table class="table">
                  <thead class="thead-dark">
                    <tr>
                      <th scope="col" style="text-align: center;">Location Name</th>
                      <th scope="col" style="text-align: center;">Average Score</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    if($best_locations){
                      foreach($best_locations AS $key => $value){ ?>
                        <tr>
                          <th scope="row" style="text-align: center;"><?php echo $key; ?></th>
                          <td style="text-align: center;"><?php echo $value; ?></td>
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
              <div>
                <?php if($worst_location_score){ ?>
                  <canvas id="yearWorstLocationChart"></canvas>
                <?php }else{ ?>
                  <p>No Data Dound.</p>
                <?php } ?>
                <table class="table">
                  <thead class="thead-dark">
                    <tr>
                      <th scope="col" style="text-align: center;">Location Name</th>
                      <th scope="col" style="text-align: center;">Average Score</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    if($worst_locations){
                      foreach($worst_locations AS $key => $value){ ?>
                        <tr>
                          <th scope="row" style="text-align: center;"><?php echo $key; ?></th>
                          <td style="text-align: center;"><?php echo $value; ?></td>
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
            <div class="col-sm-6">
              <h4>Best & Worst Survey Departments Of Year</h4>
              <div>
                <?php if($best_department_score){ ?>
                <canvas id="yearBestDepartmentChart"></canvas>
                <?php }else{ ?>
                  <p>No Data Found.</p>
                <?php } ?>
                <table class="table">
                  <thead class="thead-dark">
                    <tr>
                      <th scope="col" style="text-align: center;">Department Name</th>
                      <th scope="col" style="text-align: center;">Average Score</th>
                    </tr>
                    </thead>
                    <tbody>
                      <?php
                      if($best_departments){
                        foreach($best_departments AS $key => $value){ ?>
                          <tr>
                            <th scope="row" style="text-align: center;"><?php echo $key; ?></th>
                            <td style="text-align: center;"><?php echo $value; ?></td>
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
                <div>
                  <?php if($worst_department_score){ ?>
                  <canvas id="yearWorstDepartmentChart"></canvas>
                  <?php }else{ ?>
                    <p>No Data Found.</p>
                  <?php } ?>
                  <table class="table">
                    <thead class="thead-dark">
                      <tr>
                        <th scope="col" style="text-align: center;">Department Name</th>
                        <th scope="col" style="text-align: center;">Average Score</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      if($worst_departments){
                        foreach($worst_departments AS $key => $value){ ?>
                          <tr>
                            <th scope="row" style="text-align: center;"><?php echo $key; ?></th>
                            <td style="text-align: center;"><?php echo $value; ?></td>
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
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
</section>


<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>
<script type="text/javascript">
var locationChartCtx = document.getElementById('locationChart').getContext('2d');   
var locationChart = new Chart(locationChartCtx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($locationsChartlabels); ?>,
    datasets: [
        {
          backgroundColor: [
            "#2ecc71",
            "#3498db",
            "#95a5a6",
            "#9b59b6",
            "#f1c40f",
            "#e74c3c",
            "#34495e"
          ],
          data: <?php echo json_encode($locationsChartData); ?>
        }
    ]
  }
}); 


var departmentChartCtx = document.getElementById('departmentChart').getContext('2d');   
var departmentChart = new Chart(departmentChartCtx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($departmentChartlabels); ?>,
    datasets: [
        {
          backgroundColor: [
            "#2ecc71",
            "#3498db",
            "#95a5a6",
            "#9b59b6",
            "#f1c40f",
            "#e74c3c",
            "#34495e"
          ],
          data: <?php echo json_encode($departmentChartData); ?>
        }
    ]
  }
});

var yearBestLocationChartCtx = document.getElementById('yearBestLocationChart').getContext('2d');   
var yearBestLocationChart = new Chart(yearBestLocationChartCtx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($best_location_labels); ?>,
    datasets: [
        {
          backgroundColor: [
            "#2ecc71",
            "#3498db",
            "#95a5a6",
            "#9b59b6",
            "#f1c40f",
            "#e74c3c",
            "#34495e"
          ],
          data: <?php echo json_encode($best_location_score); ?>
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

var yearWorstLocationChartCtx = document.getElementById('yearWorstLocationChart').getContext('2d');   
var yearWorstLocationChart = new Chart(yearWorstLocationChartCtx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($worst_location_labels); ?>,
    datasets: [
        {
          backgroundColor: [
            "#2ecc71",
            "#3498db",
            "#95a5a6",
            "#9b59b6",
            "#f1c40f",
            "#e74c3c",
            "#34495e"
          ],
          data: <?php echo json_encode($worst_location_score); ?>
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

var yearBestDepartmentChartCtx = document.getElementById('yearBestDepartmentChart').getContext('2d');   
var yearBestDepartmentChart = new Chart(yearBestDepartmentChartCtx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($best_department_labels); ?>,
    datasets: [
        {
          backgroundColor: [
            "#2ecc71",
            "#3498db",
            "#95a5a6",
            "#9b59b6",
            "#f1c40f",
            "#e74c3c",
            "#34495e"
          ],
          data: <?php echo json_encode($best_department_score); ?>
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

var yearWorstDepartmentChartCtx = document.getElementById('yearWorstDepartmentChart').getContext('2d');   
var yearWorstDepartmentChart = new Chart(yearWorstDepartmentChartCtx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($worst_department_labels); ?>,
    datasets: [
        {
          backgroundColor: [
            "#2ecc71",
            "#3498db",
            "#95a5a6",
            "#9b59b6",
            "#f1c40f",
            "#e74c3c",
            "#34495e"
          ],
          data: <?php echo json_encode($worst_department_score); ?>
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
            margin: [20,10],//PDF margin (in jsPDF units). Can be a single number, [vMargin, hMargin], or [top, left, bottom, right].
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