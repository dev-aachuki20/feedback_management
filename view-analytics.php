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
.btn-outline-secondary {
    color: #6c757d;
    background-color: transparent;
    background-image: none;
    border-color: #6c757d;
    width: 100%;
}
  #exportPDF{
    text-decoration: none;
    background-color: deepskyblue;
    color: white;
    padding: 5px;
    font-size: 16px;
    margin-bottom: 18px;
  }
  .graph-btn.active {
    background: #a020f0;
    color: #fff;
    border: unset;
}
</style>
<section class="content-header">
  <h1>ANALYTICS</h1>
</section>
<section class="content">
  <div class="box">
    <div class="box-body">
      <form action="" method="post" id="survey_analytics_form">
        <div class="row">
          <input type="hidden" name="survey_type" id="survey_type" value="">
          <div class="col-md-3">
            <div class="form-group">
              <label>Survey</label>
              <select name="survey_name" class="form-control form-control-lg survey_id" id="">
                <option value="">select survey</option>
                <?php 
                // survey by user
                $surveyByUsers = get_filter_data_by_user('surveys');
                foreach($surveyByUsers as $surveyData){ 
                  $surveyId   = $surveyData['id'];
                  $surveyName = $surveyData['name'];
                ?>
                  <option value="<?=$surveyId?>" <?php if($surveyId==$_POST['survey_name']) {echo 'selected';}?>><?=$surveyName?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Start Date</label>
              <input type="date" id="fdate" name="fdate" min ="2000-01-01" max="<?= date('Y-m-d'); ?>" class="form-control" value="<?php echo $_POST['fdate']; ?>"/>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>End Date</label>
              <input type="date" id="sdate" name="sdate" class="form-control" min ="2000-01-01" max="<?= date('Y-m-d'); ?>" value="<?php echo $_POST['sdate']; ?>"/>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>&nbsp;</label>
              <input type="button" name="filter" class="btn btn-primary btn-block filter" value="Filter"/>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="box survey_type_div" style="display:none;">
    <div class="box-body">
        <!-- overall result div -->
        <div class="row overallresult" style="text-align:center;margin-bottom: 20px;">
        
        </div>

      <div class="row" style="margin-bottom: 21px;">
        <div class="col-md-3">
            <button type="button" class="btn btn-outline-secondary graph-btn" data-type="group">Group</button>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-outline-secondary graph-btn" data-type="location">Location</button>
        </div>
        <div class="col-md-3">
            <button type="button" data-type="department" class="btn btn-outline-secondary graph-btn" >Department</button>
        </div>
      </div>
    </div>
  </div>
  <a class="btn btn-xs btn-info " id="exportPDF" href="#">Export PDF</a>  
    <div id="reportPage">
      <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title graphTitle"></h3>
                <div class="box-tools pull-right" style="top:-4px !important;">
                  <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <div class="box-body " style="display: block;">
               <!-- loader div start -->
                <div class="loader col-md-12" style="text-align: center;display: none;">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="150px" height="150px" viewBox="0 0 150 150" enable-background="new 0 0 150 150" xml:space="preserve">

                    <g id="Layer_1">
                        
                            <circle opacity="0.4" fill="#FFFFFF" stroke="#1C75BC" stroke-width="2" stroke-linecap="square" stroke-linejoin="bevel" stroke-miterlimit="10" cx="75" cy="75.293" r="48.707"></circle>
                    </g>
                    <g id="Layer_2">
                        <g>
                            <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="36.2957" y1="34.8138" x2="94.5114" y2="34.8138">
                                <stop offset="0" style="stop-color:#2484C6"></stop>
                                <stop offset="1" style="stop-color:#2484C6;stop-opacity:0"></stop>
                            </linearGradient>
                            <path fill="none" stroke="url(#SVGID_1_)" stroke-width="4" stroke-linecap="round" stroke-linejoin="bevel" d="M38.296,43.227
                                c0,0,21.86-26.035,54.216-13.336">
                                <animateTransform attributeName="transform" attributeType="XML" type="rotate" from="0 75 75" to="-360 75 75" dur=".8s" repeatCount="indefinite"></animateTransform>
                            </path>
                        </g>
                    </g>
                    </svg>
                </div>
                <div class="row data-notAvailable">
                    
                </div>
                <div class="row data-available">
                    <!-- loader div end  -->
                    <div class="col-sm-8 ">
                      <canvas id="chartData"></canvas>
                    </div>
                    <div class="col-sm-4 listing">
                    </div>
                </div>
                <hr style="border: 0.5px solid #e8e3e3;"/>
              </div>
            </div>
          </div>
      </div>
    </div>
</section>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>
<script type="text/javascript">

//for graph
let locationChart;
function mychart(label,data){
    if (locationChart) {
      locationChart.destroy()
    }
  let locationChartCtx = document.getElementById('chartData').getContext('2d');   
      locationChart = new Chart(locationChartCtx, {
    type: 'pie',
    data: {
      labels: label,
      datasets: [
          {
            backgroundColor: [<?='"'.implode('","',generate_unique_color(200)).'"'?>],
            data: data,
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

 // add survey type div like loc dept group on survey choose
 $(".survey_id").change(function(){
  let survey = $('.survey_id').val();
  if(survey){
    $('.survey_type_div').show();
  }else {
    $('.survey_type_div').hide();
  }
 });

 // choose survey type
 $(".graph-btn").click(function(){

  $('.graph-btn').removeClass('active');
  $(this).addClass('active');

  let type = $(this).data('type');
  $('#survey_type').val(type);
  let survey = $('.survey_id').val();
  let fdate  = $('#fdate').val();
  let sdate  = $('#sdate').val();

  $('.graphTitle').html('SURVEY '+type.toUpperCase());
  survey_graph(fdate,sdate,survey,type,'survey_type');
 });

 $(".filter").click(function(){
  let survey = $('.survey_id').val();
  let fdate  = $('#fdate').val();
  let sdate  = $('#sdate').val();
  let type   = $('#survey_type').val();
  survey_graph(fdate,sdate,survey,type,'filter')
 });

 // function for ajax request
 function survey_graph(fdate,sdate,survey,type,mode){
  $('.loader').show();
  $('.data-available').hide();
    $.ajax({
        method:"POST",
        url:'<?=baseUrl()?>ajax/ajaxOn_survey_analytics.php',
        data:{
            mode:'survey_statics',
            fdate:fdate,
            sdate:sdate,
            survey:survey,
            survey_type : type,
            mode:mode,
        },
        success:function(response){
            $('.loader').hide();
            response = JSON.parse(response);
            $('.overallresult').html(response.overall);
            console.log(response);
            if(response.html == 0){
              $('.data-available').hide();
              $('.data-notAvailable').show();
              $('.data-notAvailable').html('<p style="margin-left: 20px !important;">THIS SEARCH PARAMETER IS NOT AVAILABLE FOR THIS SURVEY</p>');
              return;
            }else {
              $('.data-available').show();
              $('.data-notAvailable').hide();
              $('.listing').html(response.html);
              const result    = response.result;
              const locName   = Object.keys(result);
              const avgResult = Object.values(result);
              console.log(locName);
              console.log(avgResult);
              mychart(locName,avgResult);
            }
        }
    })
 }
 </script>