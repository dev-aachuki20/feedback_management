<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css" />
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

  .export {
    text-decoration: none;
    background-color: deepskyblue;
    color: white;
    padding: 5px;
    font-size: 16px;
    margin-bottom: 18px;
    display: none;
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
              <label><?= ($_GET['type']) ? ucfirst($_GET['type']) : 'Survey' ?></label>
              <select name="survey_name" class="form-control form-control-lg survey_id" id="survey_name">
                <option value="">Select <?= ucfirst($_GET['type']) ?></option>
                <?php
                // survey by user
                //$surveyByUsers = get_filter_data_by_user('surveys');
                $surveyByUsers = get_survey_data_by_user($_GET['type']);
                foreach ($surveyByUsers as $surveyData) {
                  $surveyId   = $surveyData['id'];
                  $surveyName = $surveyData['name'];
                ?>
                  <option value="<?= $surveyId ?>" <?php if ($surveyId == $_POST['survey_name']) {
                                                      echo 'selected';
                                                    } ?>><?= $surveyName ?></option>
                <?php } ?>
              </select>
              <span class="error" style="display:none ;">Please select survey</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Start Date</label>
              <input type="date" id="fdate" name="fdate" min="2000-01-01" max="<?= date('Y-m-d'); ?>" class="form-control" value="<?php echo $_POST['fdate']; ?>" />
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>End Date</label>
              <input type="date" id="sdate" name="sdate" class="form-control" min="2000-01-01" max="<?= date('Y-m-d'); ?>" value="<?php echo $_POST['sdate']; ?>" />
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>&nbsp;</label>
              <input type="button" name="filter" class="btn btn-primary btn-block filter" value="Filter" />
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
        <div class="col-md-3" style="text-align:center ;"><strong>Please select</strong></div>
        <div class="col-md-2">
          <button type="button" class="btn btn-outline-secondary graph-btn" data-type="group">Group</button>
        </div>
        <div class="col-md-2">
          <button type="button" class="btn btn-outline-secondary graph-btn" data-type="location">Location</button>
        </div>
        <div class="col-md-2">
          <button type="button" data-type="department" class="btn btn-outline-secondary graph-btn">Department</button>
        </div>
        <div class="col-md-2">
          <button type="button" data-type="role" class="btn btn-outline-secondary graph-btn">Role</button>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4" style="width: 33.33333333%;margin-bottom: 10px;">
    <form action="" id="document_form" method="post">
      <input type="hidden" name="survey" id="survey_id" value="">
      <input type="hidden" name="sdate" id="start_date" value="">
      <input type="hidden" name="edate" id="end_date" value="">
      <input type="hidden" name="survey_type" id="survey_data_type" value="">
      <div class="row">
        <div class="col-md-6 custum-btn">
          <a class="btn btn-xs btn-info export" id="exportPDF" href="#" data-action="export">Export PDF</a>
        </div>
        <div class="col-md-6 custum-btn">
          <a class="btn btn-xs btn-info export" id="exportCSV" href="#" data-action="csv">Export CSV</a>
        </div>
      </div>
    </form>
  </div>

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
          <div class="box-body" id="analytics-pdf" style="display: block;">
            <div class="pdf-head" style="display: none;">
              <table width="93%" style="margin:0 auto;">
                <tr>
                  <td colspan="3" style="text-align:center;margin-top:100px;"> <img src="<?= baseUrl() . MAIN_LOGO ?>" width="200"></td>
                </tr>
                <tr>
                  <td colspan="3" style="text-align:center;height: 30px;"></td>
                </tr>
                <tr class="borderClass filterSurvey">
                  <td colspan="3" style="text-align:center;font-size:18px;"><strong> <?= strtoupper('Survey Statistics') ?></strong></td>
                </tr>
                <tr class="borderClass filterSurveyType">
                  <td colspan="3" style="text-align:center;font-size:20px;"> </td>
                </tr>
                <tr class="borderClass filterDate" style="font-weight: 900;font-size: 16px;">
                  <td style="text-align:center;">Start Date: <span class="pdf-sdate"></span></td>
                  <td style="text-align:center;"> </td>
                  <td style="text-align:center;">End Date:<span class="pdf-edate"></span></td>
                </tr>
                <tr class="borderClass">
                  <td colspan="3" style="text-align:center;"> </td>
                </tr>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
              </table>
            </div>
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
            <hr style="border: 0.5px solid #e8e3e3;" />
            <div class="col-sm-12 graph-listing" style="display: none;">
            </div>
            <hr style="border: 0.5px solid #e8e3e3;" />

            <div class="col-sm-12">
              <footer id="pdf-footer" style="display: none;">
                <div style="text-align: center;margin-bottom:10px;font-size:15px;">
                  <?= POWERED_BY ?>
                  <center><img src="<?= BASE_URL . FOOTER_LOGO ?>" alt="" width="150" /></center>
                </div>
              </footer>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.8.0/html2pdf.bundle.js"></script>
<script type="text/javascript">
  //for graph
  let locationChart;

  function mychart(label, data) {
    if (locationChart) {
      locationChart.destroy()
    }
    let locationChartCtx = document.getElementById('chartData').getContext('2d');
    locationChart = new Chart(locationChartCtx, {
      type: 'pie',
      data: {
        labels: label,
        datasets: [{
          backgroundColor: [<?= '"' . implode('","', generate_unique_color(200)) . '"' ?>],
          data: data,
        }]
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
  $(document).on('click', '.export', function() {
    let survey = $('#survey_name').val();
    let sdate = $('#fdate').val();
    let edate = $('#sdate').val();
    let data_type = $('.graph-btn.active').data('type');
    let exportAction = $(this).data('action');
    if (exportAction === "export") {
      export_pdf(survey, sdate, edate, data_type);
    } else {
      $('#survey_id').val(survey);
      $('#start_date').val(edate);
      $('#end_date').val(edate);
      $('#survey_data_type').val(data_type);
      $('#document_form').attr('action', './Export-Csv/view-analytics.php');
      $('#document_form').submit();
    }
  });

  /**@abstract
   * export_pdf
   */
  function export_pdf(survey, sdate, edate, data_type = '') {
    // get all surveys 
    const survey_arr = <?php echo json_encode(getSurvey()); ?>;

    if (sdate != '' && edate != '') {
      $('.filterDate').show();
      $('.pdf-sdate').html(sdate);
      $('.pdf-edate').html(edate);
    } else {
      $('.filterDate').hide();
    }
    $('.filterSurvey').hide();
    var file_name = '<?= 'Survey Statics-' . date('Y-m-d-H-i-s') . '.pdf' ?>';
    if (data_type != '') {
      $('.filterSurveyType').show();
      let heading = data_type + ' Statistics';
      $('.filterSurveyType>td').html('<strong>' + heading.toUpperCase() + '</strong>');
      if (survey) {
        var file_name = survey_arr[survey] + '<?= '-' . date('Y-m-d-H-i-s') . '.pdf' ?>';
        $('.filterSurvey').show();
        $('.filterSurvey>td').html('<strong>' + survey_arr[survey].toUpperCase() + '</strong>');
      }
    } else {
      let heading = 'Survey Statics';
      $('.filterSurveyType>td').html('<strong>' + heading.toUpperCase() + '</strong>');
    }

    $('.listing').hide();
    $('.pdf-head').show();
    $('.survey_name').hide();
    $('#pdf-footer').show();
    $('.graph-listing').show();
    $('.content-wrapper').css('margin-bottom','58px');

    // draw html in pdf
    let element = document.getElementById('analytics-pdf');
    //$(".chartjs-render-monitor").css("width", "180");
    let box = document.querySelector('.chartjs-size-monitor');
    // let width = box.offsetWidth;
    let height = box.offsetHeight;
    $('.col-md-3').attr('class', 'col-md-4');
    $(".chartjs-size-monitor").css("height", "90");

    
    html2pdf(element, {
      margin: 5,
      filename: file_name,
      image: {
        type: 'jpeg',
        quality: 0.98
      },
      html2canvas: {
        scale: 2,
        logging: true,
        dpi: 192,
        letterRendering: true
      },
      jsPDF: {
        unit: 'mm',
        format: 'a4',
        orientation: 'portrait'
      },
      pagebreak: {
        mode: ['avoid-all', 'css', 'legacy']
      }
    });


    $('#pdf-footer').hide();
    $(".chartjs-size-monitor").css("height", height);
    $('.col-md-4').attr('class', 'col-md-3 ');
    $('.pdf-head').hide();
    $('.survey_name').show();
    $('.graph-listing').hide();
    $('.listing').show();

  }
  // End export pdf

  // add survey type div like loc dept group on survey choose
  $(".survey_id").change(function() {
    let survey = $('.survey_id').val();
    if (survey) {
      $('.survey_type_div').show();
    } else {
      $('.survey_type_div').hide();
    }
  });

  // choose survey type
  $(".graph-btn").click(function() {

    $('.graph-btn').removeClass('active');
    $(this).addClass('active');

    let type = $(this).data('type');
    $('#survey_type').val(type);
    let survey = $('.survey_id').val();
    let fdate = $('#fdate').val();
    let sdate = $('#sdate').val();

    $('.graphTitle').html('SURVEY ' + type.toUpperCase());
    survey_graph(fdate, sdate, survey, type, 'survey_type');
  });

  $(".filter").click(function() {
    let survey = $('.survey_id').val();
    let fdate = $('#fdate').val();
    let sdate = $('#sdate').val();
    let type = $('#survey_type').val();

    // check the survey is selected or not 
    (survey) ? $('.error').hide(): $('.error').show();
    survey_graph(fdate, sdate, survey, type, 'filter')
  });

  // function for ajax request
  function survey_graph(fdate, sdate, survey, type, mode) {
    $('.loader').show();
    $('.data-available').hide();
    $('.export').hide();

    $.ajax({
      method: "POST",
      url: '<?= baseUrl() ?>ajax/ajaxOn_survey_analytics.php',
      data: {
        mode: 'survey_statics',
        fdate: fdate,
        sdate: sdate,
        survey: survey,
        survey_type: type,
        mode: mode,
      },
      success: function(response) {
        $('.loader').hide();
        response = JSON.parse(response);
        $('.overallresult').html(response.overall);
        if (response.html == 0) {
          $('.data-available').hide();
          $('.data-notAvailable').show();
          if (type) {
            $('.data-notAvailable').html('<p style="margin-left: 20px !important;">THIS SEARCH PARAMETER IS NOT AVAILABLE FOR THIS SURVEY</p>');
          } else {
            $('.data-notAvailable').html('');
          }

          return;
        } else {
          $('.export').show();
          $('.data-available').show();
          $('.data-notAvailable').hide();
          $('.listing').html(response.html);
          $('.graph-listing').html(response.html);
          const result = response.result;
          const locName = Object.keys(result);
          const avgResult = Object.values(result);
          mychart(locName, avgResult);
        }
      }
    });
  }
</script>


