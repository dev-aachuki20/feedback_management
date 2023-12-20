<?php
$survey_types = survey_type();
$selected_survey_type = (isset($_GET['type']) && $_GET['type'] != '') ? $_GET['type'] : '';
$additional_query = '';
if ($selected_survey_type) {
  $additional_query = ' AND filter = ' . $selected_survey_type;
}

record_set('report_templates', 'SELECT * FROM report_templates WHERE status = 2 AND deleted_at IS NULL ' . $additional_query . ' ORDER BY id ASC');

// Submit modal to schedule report
if (isset($_POST['schedule_btn'])) {
  $report_name = $_POST['report_name'];
  $send_to = implode(',', $_POST['send_to']);
  $template_id = $_POST['sch_template_id'];
  $template_field_name = $_POST['sch_template_field_name'];
  $start = $_POST['start_date'];
  $end = $_POST['end_date'];
  $interval = $_POST['interval'];
  $time_period = $_POST['time_period'];
  $next_date =  date('Y-m-d H:i:s', strtotime('+' . $interval . 'hour', strtotime($start)));
  $filter  = array("field" => $template_field_name, "survey_id" => $_POST['survey'], "field_value" => $_POST['template_field']);
  $mail_recipients = $_POST['send_to'];


  $dataCol =  array(
    "report_name"     => $report_name,
    "send_to"         => $send_to,
    "temp_id"         => $template_id,
    "sch_interval"    => $interval,
    "time_interval"   => $time_period,
    'start_date'      => $start,
    'end_date'        => $end,
    'next_date'       => $next_date,
    'filter'          => json_encode($filter),
    'cby'             => $_SESSION['user_id'],
  );

  // echo '<pre>';
  // print_r($dataCol);
  // echo '</pre>';
  // die('gf');

  $insert_value =  dbRowInsert("scheduled_report_templates", $dataCol);
  if ($insert_value) {
    $msg = "Report Scheduled Successfully";
    alertSuccess($msg, '');
  } else {
    $msg = "Sorry! Report Not Schedule";
    alertdanger($msg, '');
  }
}
?>
<section class="content-header">
  <h1>VIEW TEMPLATES</h1>
</section>

<section class="">
  <div class="row">
    <div class="col-md-12">
      <div class="col-md-3 col-md-offset-4">
        <select id="survey_type" name="survey_type" class="form-control">
          <option value="">Select Survey Type</option>
          <?php if (!empty($survey_types)) { ?>
            <?php foreach ($survey_types as $key => $value) { ?>
              <option value="<?= $key; ?>" <?= ($selected_survey_type == $key) ? 'selected' : ''; ?>><?= $value; ?></option>
            <?php } ?>
          <?php } ?>
        </select>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="box">
    <div class="box-body table-responsive">
      <table id="common-table" class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Report Name</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 0; ?>
          <?php while ($report_template = mysqli_fetch_assoc($report_templates)) { ?>
            <tr>
              <td><?= ++$i; ?></td>
              <td><?= $report_template['name']; ?></td>
              <td>
                <!-- <button class="btn btn-sm btn-primary">VIEW PDF</button>
              <form action="./ajax/ajaxOn_survey_statistics.php?export=csv&data_type=<?= $report_template['fields'] ?>" method="post" style="display: inline-block;">
                <input type="hidden" name="data_type" value="<?= $report_template['fields'] ?>">
                <input type="hidden" name="survey_type" value="<?= $report_template['fields'] ?>">
                <button type="submit" class="btn btn-sm btn-green">DOWNLOAD CSV</button>
              </form> -->

                <button class="btn btn-sm btn-yellow" onclick="scheduleTemplate(<?= $report_template['id'] ?>, '<?= $report_template['name'] ?>', '<?= $report_template['fields'] ?>', <?= $report_template['filter'] ?>,<?= $report_template['report_type'] ?>,'schedule');" style>SCHEDULE</button>
                <button class="btn btn-sm btn-green" onclick="scheduleTemplate(<?= $report_template['id'] ?>, '<?= $report_template['name'] ?>', '<?= $report_template['fields'] ?>', <?= $report_template['filter'] ?>,<?= $report_template['report_type'] ?>,'preview');">RUN</button>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<style type="text/css">
  .select2 {
    width: 100% !important;
  }
</style>
<!-- schedule report modal -->
<div class="modal" id="schedule_report_templates">
  <div class="modal-dialog">
    <div class="modal-content" style="height:auto;">
      <div class="modal-header">
        <button type="button" class="close closes" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
        <h5 id="sch_modal_title" class="modal-title"> </h5>
      </div>
      <div class="modal-body">
        <form class="second_form" method="post">
          <input type="hidden" id="sch_template_id" name="sch_template_id" value="">
          <input type="hidden" id="sch_template_field_name" name="sch_template_field_name" value="">
          <input type="hidden" id="template_filter" name="template_filter" value="">
          <input type="hidden" id="report_type_data" value=""/>
          <div class="form-group row schedule-field">
            <label for="report-name" class="col-sm-4 col-form-label">Report Name</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" id="report_name" name="report_name" placeholder="Enter Report Name" value="" min="<?= date('Y-m-d') ?>">
            </div>
          </div>

          <div class="form-group row survey">
          </div>

          <div class="form-group row filter_data">
          </div>

          <div class="form-group row">
            <label for="start_date" class="col-sm-4 col-form-label">Report  Start Date</label>
            <div class="col-sm-8">
              <input type="date" class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="" required>
            </div>
          </div>

          <div class="form-group row">
            <label for="end_date" class="col-sm-4 col-form-label">Report  End Date</label>
            <div class="col-sm-8">
              <input type="date" class="form-control" id="end_date" name="end_date" placeholder="End Date" value=""  required>
            </div>
          </div>

          <div class="form-group row schedule-field">
            <label for="interval" class="col-sm-4 col-form-label">Report Frequency</label>
            <div class="col-sm-8">
              <select class="form-control" id="interval" name="interval" required>
                <?php foreach (service_type() as $key => $value) { if($key == 336) continue; ?>
                  
                  <option value="<?php echo $key; ?>"><?= $value ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group row schedule-field">
            <label for="time-period" class="col-sm-4 col-form-label">Time Period</label>
            <div class="col-sm-8 time-intervals">
            </div>
          </div>

          <div class="form-group row schedule-field">
            <label for="send to user" class="col-sm-4 col-form-label">Send to User</label>
            <div class="col-sm-8">
              <select class="form-control recipients-select2" id="send_to" name="send_to[]" placeholder="Select Any User" multiple required>
                <?php foreach (getUsers() as $key => $value) { ?>
                  <option value="<?php echo $key; ?>"><?= $value ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <!-- csv export old -->
          <!-- <div class="form-group row export-checkboxes text-center" style="display:none;">
            <div class="form-check">
              <input class="form-check-input preview-file-type" name="export_pdf" id="chk-export-pdf" type="checkbox" value="1" id="flexCheckDefault">
              <label class="form-check-label" for="flexCheckDefault">
                PDF
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input preview-file-type" name="export_csv" id="chk-export-csv" type="checkbox" value="1" id="flexCheckChecked">
              <label class="form-check-label" for="flexCheckChecked">
                CSV
              </label>
            </div>
          </div> -->
          <input type="hidden" name="export_document" id="chk-export-csv" value="">       
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary export-doc"  data-type="pdf">DOWNLOAD PDF</button>
            <button type="submit" class="btn btn-primary export-doc"  data-type="csv">DOWNLOAD CSV</button>
            <button type="submit" class="btn btn-success green-btn" id="schedule_btn" name="schedule_btn">Save</button>
            <button type="button" class="btn btn-danger closes" data-dismiss="modal" aria-label="Close" style="background-color:#ff1c00 !important;"> Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  let activeReportType = 0;
  let activeModalType = null;

  $("#survey_type").on('change', function() {
    window.location = window.location.href.split('&')[0] + "&type=" + this.value
  });

  function scheduleTemplate(template_id, template_name, template_field, template_filter, report_type, modal_type) {
    activeModalType = modal_type;
    if (modal_type === 'preview') {
      $('.schedule-field').hide();
      $('.export-checkboxes').show();
      // $('.modal-content').css("height", "200px");
      $('#start_date').prop('required', false);
      $('#end_date').prop('required', false);
      $('#interval').prop('required', false);
      $('#report_name').prop('required', false);
      $('#start_date').removeAttr('min');
 
      //$('#send_to').prop('required',false);
      activeReportType = report_type;
      if (report_type == 1) {
        $('.second_form').attr('action', './report-doc/preview-report/report-pdf.php');
      } else if (report_type == 2) {
        $('.second_form').attr('action', './report-doc/preview-report/report-pdf-question-pdf.php');
      }
      $('.export-doc').show();
      $('#schedule_btn').hide();

      //$('.second_form').attr("target", "_blank");

    } else {
      let minDate = '<?= date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d')))); ?>';
      $('#start_date').attr('min', minDate);
      $('.export-doc').hide();
      $('#schedule_btn').show();
      $('.export-checkboxes').hide();
     
      $('#start_date').prop('required', true);
      $('#end_date').prop('required', true);
      $('#interval').prop('required', true);
      $('#report_name').prop('required', true);
      //$('#send_to').prop('required',true);
      $('.second_form').removeAttr('action');
      //$('.second_form').removeAttr('target');
      $('.schedule-field').show();
      // $('.modal-content').css("height", "430px");
    }
    //$(".multiple-select").empty().trigger('change');
    $("#sch_modal_title").html(template_name);
    $("#sch_template_id").val(template_id);
    $("#sch_template_field_name").val(template_field);
    $("#template_filter").val(template_filter);
    $("#report_type_data").val(report_type);
    $("#label_template_field").text(template_field.toUpperCase());
    $.ajax({
      url: "ajax/common_file.php",
      type: 'POST',
      data: {
        mode: 'fetch_schedule_filter',
        filter: template_filter,
        field: template_field,
      },
      success: function(result) {
        var result = JSON.parse(result)
        $('.survey').html(result);
        if ((template_field == 'survey' || template_field == 'pulse' || template_field == 'engagement') && report_type == 1) {
          $('.multiple-select').val(null).prop('multiple', true).attr('name', 'survey[]').select2({
            placeholder: "Select Any",
            allowClear: true
          });

          $('.recipients-select2').val(null).prop('multiple', true).select2({
            placeholder: "Select Any",
            allowClear: true
          });
          

        } else {
          $('.multiple-select').attr('name', 'survey');
          $('.recipients-select2').val(null).prop('multiple', true).select2({
            placeholder: "Select Any",
            allowClear: true
          });
        }
      }
    })
    $('.filter_data').html('');
    $('#schedule_report_templates').modal('show');
  }


  //onchange survey
  $(document).on('change', '#template_survey', function() {
    let survey_id = $(this).val();
    let template_type = $('#sch_template_field_name').val();
    let template_filter = $('#template_filter').val();
    if ((template_type != 'survey') && (template_type != 'pulse') && (template_type != 'engagement')) {
      $.ajax({
        url: "ajax/common_file.php",
        type: 'POST',
        data: {
          mode: 'fetch_filter_data',
          survey_id: survey_id,
          template_type: template_type,
          template_filter: template_filter
        },
        success: function(result) {
          var result = JSON.parse(result)

          $('.filter_data').html(result);
          $('#template_field').val(null).prop('multiple', true).attr('name', 'template_field[]').select2({
            placeholder: "Select",
            allowClear: true
          });
        }
      })
    }
  });

  /* csv export old
    // $(document).ready(function() {
    //   $('input.form-check-input').on('change', function() {
    //     if (activeReportType === 2) {
    //       if ($(this).attr('name') === "export_pdf") {
    //         $('.second_form').attr('action', './report-doc/preview-report/report-pdf-question-pdf.php');
    //       }
    //       if ($(this).attr('name') === "export_csv") {
    //         $('.second_form').attr('action', './report-doc/preview-report/report-question-csv.php');
    //       }
    //     }
    //     $('input.form-check-input').not(this).prop('checked', false);
    //   });

    //   $("#schedule_btn").on('click', function(event) {
    //     if (activeModalType === "preview") {
    //         if ($('#template_survey').val() === '' || $('#template_survey').val() === null) {
    //           alert('Please select a survey!');
    //           return false;
    //         }
    //       let flag = $('.preview-file-type').is(':checked');
    //       if (flag == false) {
    //         alert('Please select a preview format!');
    //         return false;
    //       } else {
    //         $(".second_form").submit();
    //       }
    //     } 
    //   });
    // });
  */

  $('.export-doc').click(function(event){
    event.preventDefault();
    let type = $(this).data('type');
    let reportType = $('#report_type_data').val();
    let value = (type == 'pdf') ? 2: 1;
    let isSurvey = $('#template_survey').val();
    let templateField = $('#template_field').val();


    $('#chk-export-csv').val(value);
    if(reportType == 1){
    }else{
      if(type =='pdf'){
        $('.second_form').attr('action', './report-doc/preview-report/report-pdf-question-pdf.php');
      }else{
        $('.second_form').attr('action', './report-doc/preview-report/report-question-csv.php');
      }
    }
    if(isSurvey == null){
      isSurvey = [];
    }
    if(templateField !== undefined && templateField == null){
      console.log("case 1");
      templateField = [];
    }
    console.log(templateField,'isSurvey');

    if(isSurvey.length == 0){
      alert('Survey is required');
      return false; 
    }else if(templateField !== undefined && templateField.length ==0){
      let text = $('.filter_data').find("#label_survey_field").text();
      text = text.toLowerCase();
      
      alert(`${text[0].toUpperCase()+ text.substring(1)} is required`);
      return false;
    }else{
      $('.second_form').submit();
    }
  })


  let startDateInput = $('#start_date');
  let endDateInput = $('#end_date');

  startDateInput.change(() => validateDates('start'));
  endDateInput.change(() => validateDates('end'));

  function validateDates(type) {
    const currentDate = new Date();
    const startDate = startDateInput.val();
    const endDate = Date.parse(endDateInput.val());
    if (activeModalType === "preview") {
        console.log('Dates are valid:', startDate, endDate);
        $('#end_date').attr('min', startDate);
        if(type == 'start'){
          $('#end_date').val('');
        }
    }else{
      
      if(type == 'start'){
          $('#end_date').val('');
      }
      $('#end_date').attr('min', startDate);

    }
  }

    //onchange survey
  $("#interval").on('change', function() {
    let frequency = $(this).val();
    $('.time-intervals').html('');

    if (parseInt(frequency) > 0) {
      $.ajax({
        url: "ajax/common_file.php",
        type: 'POST',
        data: {
          frequency: frequency,
          mode: 'fetch_time_periods',
        },
        success: function(response) {
          console.log(response)
          if(response !=''){
            $('.time-intervals').html(response);
          }
        },
      })
    }
  });


</script>