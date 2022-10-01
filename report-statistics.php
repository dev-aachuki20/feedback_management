<?php
// start form insert
if(isset($_POST['submitComment'])){
  $que_id = $_POST['que_id'];
  $worst_percent = $_POST['worst_percent'];
  $comment = $_POST['quecomment'];
 
  $data = array(
    "question_id"=> $que_id,
    "worst_percent"=> $worst_percent,
    "comment_text"=> $comment,
    "cip" => ipAddress(),
    "cstatus"=>"1",
    "cdate" => date("Y-m-d H:i:s"),
  );
  $insert_value =  dbRowInsert("question_reports",$data);
}
// end form insert

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

$best_question_id = array();
$worst_question_id = array();

$total_survey = '';
$best_total = 0;
$worst_total = 0;

if(!empty($_GET['loc'])){
  foreach($datasets AS $dataset){
    
    $loc_id = $_GET['loc'];

    record_set("per_location","SELECT surveyid,locationid,cby FROM answers WHERE locationid = $loc_id GROUP BY cby");

    record_set("total_survey","SELECT COUNT(DISTINCT(surveyid)) FROM answers WHERE locationid = $loc_id GROUP BY locationid");
    $row_total_survey = mysqli_fetch_assoc($total_survey);
    $total_survey = $row_total_survey['COUNT(DISTINCT(surveyid))'];

    // echo "<pre>";
    while($row_per_location = mysqli_fetch_assoc($per_location)){
       
      // print_r($row_per_location);
      $locId = $row_per_location['locationid'];
      $surveyid = $row_per_location['surveyid'];
      $cby = $row_per_location['cby'];
      
      $count = array();
      record_set("get_question","select questionid ,questions.answer_type as answer_type from answers left join questions on answers.questionid = questions.id  where answers.surveyid=$surveyid and answers.locationid=$locId and answers.cby=$cby");
      while($row_get_question= mysqli_fetch_assoc($get_question)){

        if($row_get_question['answer_type'] == 1 || $row_get_question['answer_type'] == 4 || $row_get_question['answer_type'] == 6 ){
          $questionid = $row_get_question['questionid'];

        record_set("get_question_id","select answerid,answerval,questionid,answertext from answers  where questionid=$questionid and surveyid=$surveyid");
          
          
        }
      }
    }//end while loop
  }//end foreach
}//end if

$que_count =0;
  $b=0; 
  arsort($best_locations);
  foreach($best_locations AS $key => $value){
    if($b<=4 && !empty($best_question_id[$b])){
      ++$que_count;
      $best_total +=number_format((floatval($value)));
    }
    $b++;
    } 

  $w=0;
  arsort($worst_locations);
  foreach($worst_locations AS $key => $value){ 
    if($w<=4 && !empty($worst_question_id[$w])){
      ++$que_count;
      $worst_total +=number_format((floatval($value)));
    }
    $w++;
  }
  $main_total = number_format((floatval($best_total+$worst_total)));

  $average_percentage = 0;
  if($main_total != 0 && $que_count!=0){
    $average_percentage = number_format((floatval($main_total/$que_count)));
  }
 
  $chart_deg = number_format((floatval(($average_percentage/100)*180)));

?>
<style>
  .gauge-wrapper {
    display: inline-block;
    width: auto;
    margin: 0 auto;
    padding: 20px 15px 5px;
  }

  .poor{
    margin-right:110px;
    font-weight: bolder;
    color:#E84C3D;
  }
  .good{
    font-weight: bolder;
    color:#1eaa59;
  }
  .success-percent p{
    font-size: 100px;
    color:#7ab353;
  }
  .highestresponses{
    list-style:none;
    width: 100%;
    padding: 0;
    max-width: 800px;
    margin: auto;
    color:#7ab353;
  }
  .high-content{
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  }
  .high-content h2{
    width: 100%;
    text-align: left;
    padding-left:25px;
    font-size: x-large;
    font-weight: 600;
  }
  .high-content ol{
    width: 100%;
  }
  .high-content ol li{
    font-weight: 600;
    text-align: left;
  }
  .high-content ol li span{
    float: right;
  }
  

  .lowestresponses{
    list-style:none;
    width: 100%;
    padding: 0;
    max-width: 800px;
    margin: auto;
    color:#E84C3D;
  }
  .low-content{
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  }
  .low-content h2{
    width: 100%;
    text-align: left;
    padding-left:25px;
    font-size: x-large;
    font-weight: 600;
  }
  .low-content ol{
    width: 100%;
  }
  .low-content ol li{
    font-weight: 600;
    text-align: left;
  }
  .low-content ol li span{
    float: right;
  }
  
  .response-content{
    width: 100%;
    margin: auto;
    max-width: 800px;
  }
  .responses-title{
    width: 100%;
    text-align: left;
    padding-left: 25px;
    font-size: x-large;
    font-weight: 600;
    color:#656363;
  }
  .response-content p{
    background-color:#f5f4f5;
    text-align: justify;
    padding: 10px;
    color: #656363;
    font-weight: 600;
  }

  .response-question{
    list-style:none;
    width: 100%;
    padding: 0;
    max-width: 800px;
    margin: auto;
    color:#E84C3D;
  }
  .res-content{
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  }
  .res-content ol{
    width: 100%;
  }
  .res-content ol li{
    font-weight: 600;
    text-align: left;
  }
  .res-content ol li span{
    float: right;
  }
 
  .commentbutton{
    margin-top: 5px;
    width: 100%;
    text-align: left;
    color:black;
  }

</style>
<section class="content">

    <div class="row">
        <div class="col-lg-12">
            <div class="box">
                <div class="box-body">
                  <a class="btn btn-xs btn-info mr-3" href="export-report.php" style="float:right;"  target="_blank">Export CSV</a>
                  <button class="btn btn-xs btn-primary" id="exportPDF" style="float:right;margin-right: 1%;">Export PDF</button>
                  <div id="reportPage">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3>Report</h3>
                            <h5>Location</h5>
                            <select class="locations" name="location_id">
                              <?php
                                if($_SESSION['user_type']==1 OR $_SESSION['user_type']==2){
                                  record_set("get_locations","SELECT * FROM locations");
                                }

                                if($_SESSION['user_type'] == 2){
                                  $client_locations = $_SESSION['user_locationid'];
                                  record_set("get_locations","SELECT * FROM locations where id IN($client_locations)");
                                  echo "<option value='0'>Select-Location</option>";
                                }
                                
                                while($row_get_locations = mysqli_fetch_assoc($get_locations)){
                              ?>
                                <option value="<?=$row_get_locations['id']?>" <?=(!empty($_GET['loc']) && $_GET['loc'] == $row_get_locations['id'])?'selected':''?>><?=$row_get_locations['name']?></option>
                              <?php } ?>
                            </select>
                            <p class="text-bold text-uppercase font-weight-normal">total surveys taken <?=(!empty($total_survey))?$total_survey:'0'?></p>
                              <div class="gauge-wrapper">
                                    <div id="canvas-holder" style="width:200px">
                                      <canvas id="chart"></canvas>
                                      <span class="poor">Poor</span>
                                      <span class="good">Good</span>
                                    </div>
                              </div>
                            <div>
                                
                            </div>
                            <div class="success-percent">
                                <p><?=$average_percentage.'%'?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Start highest responses -->
                    <div class="row">
                      <div class="col-sm-12">
                        <ul class="highestresponses">
                          <li class="high-content">
                            <h2 class="text-uppercase">highest responses</h2>
                              <ol>
                                <?php
                                $i=0; 
                                arsort($best_locations);
                                foreach($best_locations AS $key => $value){
                                  if($i<=4 && !empty($best_question_id[$i])){
                                ?>
                                  <li class="text-uppercase"><?=$key?>
                                    <span><?=$value?>%</span>
                                   </li>
                                <?php } $i++;}?> 
                              </ol>
                          </li>
                        </ul>
                      </div>
                    </div>

                    <!-- Start lowest responses -->
                    <div class="row">
                      <div class="col-sm-12">
                        <ul class="lowestresponses">
                          <li class="low-content">
                            <h2 class="text-uppercase">lowest responses</h2>
                              <ol>
                                <?php 
                                    $k=0;
                                    arsort($worst_locations);
                                    foreach($worst_locations AS $key => $value){
                                      if($k<=4 && !empty($worst_question_id[$k])){
                                ?>
                                  <li class="text-uppercase addComment" style="cursor: pointer;" id="worst_que_<?=$worst_question_id[$k]?>" data-qid="<?=$worst_question_id[$k]?>" data-percent="<?=$value?>"><?=$key?>
                                  <span><?=$value?>%</span>
                                  </li>
                                  <?php
                                   record_set("get_question_reports","select * from question_reports where question_id=$worst_question_id[$k] ");
                                   if($totalRows_get_question_reports == 0){
                                  ?> 
                                    <div class="commentArea_<?=$worst_question_id[$k]?>">
          
                                    </div> 
                                  <?php } ?>
                                   
                                <?php }$k++; }?>  
                              </ol>
                          </li>
                        </ul>
                      </div>
                    </div>
                    <!-- Start responses content -->
                    <div class="row">
                      <div class="col-sm-12">
                        <div class="response-content">
                          <h2 class="text-uppercase responses-title">responses</h2>
                          <ul class="response-question">
                            <li class="res-content">
                                <ol>
                                  <?php 
                                    $kr=0;
                                    arsort($worst_locations);
                                    foreach($worst_locations AS $key => $value){
                                      if($kr<=4 && !empty($worst_question_id[$kr])){
                                
                                   record_set("get_question_reports","select * from question_reports where question_id=$worst_question_id[$kr] ");
                                   if($totalRows_get_question_reports > 0){
                                  ?> 
                                    <li class="text-uppercase" id="worst_que_<?=$worst_question_id[$kr]?>" data-qid="<?=$worst_question_id[$kr]?>" data-percent="<?=$value?>"><?=$key?>
                                    <span><?=$value?>%</span>
                                    </li>
                                    <?php
                                      record_set("get_question_reports","select * from question_reports where question_id=$worst_question_id[$kr] ");
                                      if($totalRows_get_question_reports > 0){
                                        $row_get_question_reports = mysqli_fetch_assoc($get_question_reports);
                                    ?> 
                                      <p><?=$row_get_question_reports['comment_text']?></p>
                                    <?php } ?>
                              

                                  <?php } ?>
                                   
                                <?php }$kr++; }?>

                                </ol>
                            </li>
                          </ul>

                        </div>  

                      </div>
                    </div>  
                  </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>
<!-- Resources -->
<script src="https://unpkg.com/chart.js@2.8.0/dist/Chart.bundle.js"></script>
<script src="https://unpkg.com/chartjs-gauge@0.2.0/dist/chartjs-gauge.js"></script>

<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-html2canvas@latest/dist/jspdf-html2canvas.min.js"></script> 



<script type="text/javascript">
 $(document).ready(function(){
    $('.addComment').click(function(){
      var qid = $(this).data('qid');
      var worst_percent = $(this).data('percent');
      // console.log('add - '+qid);
      $('.commentArea_'+qid).html('<form method="POST"><input type="hidden" name="que_id" value="'+qid+'"><input type="hidden" name="worst_percent" value="'+worst_percent+'"><textarea class="form-control comment" name="quecomment" placeholder="Write a comment..." style="margin-top:10px;" required></textarea>'+
      '<div class="commentbutton"><input type="submit" name="submitComment" value="Submit" style="    margin-right: 5px;"><button class="cancel" onclick="return false;" id="cancel_'+qid+'" data-qid="'+qid+'">Cancel</button></div></form>');
      
    });

    $('body').on('click','.cancel',function(){
      var qid = $(this).data('qid');
      // console.log('cancel - '+qid);
      $('.commentArea_'+qid).html('');
    });
    
  $('.locations').change(function(){
      
    // console.log(window.location.href+'&loc='+$(this).val());
    window.location.replace(window.location.origin+window.location.pathname+'?page=report-statistics&loc='+$(this).val());
  });

// start chart js
var ctx = document.getElementById("chart").getContext("2d");

//0.04 = 1% , 20% 0.04*20 = 0.8
var  val = 0.04*<?=$average_percentage?>;
var chart = new Chart(ctx, {
 type: 'gauge',
 data: {
   datasets: [{
     value: val,
     data: [1, 2, 3, 4],
     backgroundColor: ['#E84C3D', '#f1c40f', '#6cca2c','#1eaa59'],
     borderColor:['#E84C3D', '#f1c40f', '#6cca2c','#1eaa59'],
   }]
 },
 options: {
   responsive: true,
   layout: {
     padding: {
       bottom: 5
     }
   },
   needle: {
     radiusPercentage: 3,
     widthPercentage: 5,
     lengthPercentage: 100,
     color: '#6cca2c',
     borderColor:'#fff',
   },
   valueLabel: {
     display: false,
     formatter: (value) => {
       return Math.round(value)+'%';
     },
     color: 'rgba(255, 255, 255, 1)',
     backgroundColor: 'rgba(0, 0, 0, 1)',
     borderRadius: 5,
     padding: {
       top: 10,
       bottom: 10
     }
   }
 }
});
// End chart js

  // start export pdf 
  const pages = document.getElementById('reportPage');
    $('#exportPDF').click(function(){
      html2PDF(pages, {
        margin: [10,10],//PDF margin (in jsPDF units). Can be a single number, [vMargin, hMargin], or [top, left, bottom, right].
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
  });
 // End export pdf
</script>

