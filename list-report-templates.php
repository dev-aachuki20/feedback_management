<?php 
$survey_types = survey_type();
$selected_survey_type = (isset($_GET['type']) && $_GET['type'] != '') ? $_GET['type'] : '';
$additional_query = '';
if($selected_survey_type){
  $additional_query = ' AND filter = '.$selected_survey_type;
}
record_set('report_templates', 'SELECT * FROM report_templates WHERE status = 2 AND deleted_at IS NULL '.$additional_query.' ORDER BY id ASC');

// Submit modal to schedule report
if(isset($_POST['schedule_btn'])){
    $template_id = $_POST['sch_template_id'];
    $template_field_name = $_POST['sch_template_field_name'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $interval = $_POST['interval'];
    $next_date =  date('Y-m-d H:i:s',strtotime('+'.$_POST['interval'] .'hour',strtotime($start)));
    $filter  = array("field" => $template_field_name, "field_value" => $_POST['template_field']);

    $dataCol =  array(
      "temp_id"         => $template_id,
      "sch_interval"    => $interval,
      'start_date'      => $start,
      'end_date'        => $end,
      'next_date'       => $next_date,
      'filter'          => json_encode($filter),
      'cby'             => $_SESSION['user_id'],
    );

    $insert_value =  dbRowInsert("scheduled_report_templates",$dataCol);
    if($insert_value){
        $msg = "Report Scheduled Successfully";
        alertSuccess($msg,'');
    }else {
        $msg = "Sorry! Report Not Schedule";
        alertdanger($msg,'');
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
          <?php if(!empty($survey_types)){ ?>
          <?php foreach($survey_types AS $key => $value) { ?>
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
      <table id="example1" class="table table-bordered table-hover">
        <thead>
            <tr>
            <th>#</th>
            <th>Report Name</th>
            <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php $i = 0; ?>
        <?php while($report_template = mysqli_fetch_assoc($report_templates)){ ?>
          <tr>
            <td><?= ++$i; ?></td>
            <td><?= $report_template['name']; ?></td>
            <td>
              <button class="btn btn-sm btn-primary">VIEW PDF</button>

              <form action="./ajax/ajaxOn_survey_statistics.php?export=csv&data_type=<?= $report_template['fields'] ?>" method="post" style="display: inline-block;">
                <input type="hidden" name="data_type" value="<?= $report_template['fields'] ?>">
                <input type="hidden" name="survey_type" value="<?= $report_template['fields'] ?>">
                <button type="submit" class="btn btn-sm btn-green">DOWNLOAD CSV</button>
              </form>

              <button class="btn btn-sm btn-yellow" onclick="scheduleTemplate(<?= $report_template['id'] ?>, '<?= $report_template['name'] ?>', '<?= $report_template['fields'] ?>', <?= $report_template['filter'] ?>);">SCHEDULE</button>
            </td>
          </tr>
        <?php } ?>
        </tbody> 
      </table>
    </div>
  </div>
</section>

<style type="text/css">
.select2{
  width: 100% !important;
}
</style>
<!-- schedule report modal -->
<div class="modal" id="schedule_report_templates">
  <div class="modal-dialog">
        <div class="modal-content" style="height:300px ;">
            <div class="modal-header">
              <h5 id="sch_modal_title" class="modal-title"> </h5>
              <button type="button" class="close closes" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <div class="modal-body">
            <div class="form-group">
                <form class="second_form" method="post">
                    <input type="hidden" id="sch_template_id" name="sch_template_id" value="">
                    <input type="hidden" id="sch_template_field_name" name="sch_template_field_name" value="">
                    <div class="form-group row">
                        <label id="label_template_field" for="template_field" class="col-sm-4 col-form-label"></label>
                        <div class="col-sm-8">
                            <select id="template_field" name="template_field[]" class="form-control form-control-lg multiple-select" multiple=multiple>

                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="start_date" class="col-sm-4 col-form-label">Start Date</label>
                        <div class="col-sm-8">
                            <input type="date"  class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="" min="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="end_date" class="col-sm-4 col-form-label">End Date</label>
                        <div class="col-sm-8">
                            <input type="date"  class="form-control" id="end_date" name="end_date" placeholder="End Date" value="" min="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="interval" class="col-sm-4 col-form-label">Interval</label>
                        <div class="col-sm-8">
                        <select class="form-control" id="interval" name="interval" required>
                            <?php foreach(service_type() as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" ><?=$value?></option>
                            <?php } ?>
                        </select>
                        </div>
                    </div>
                    <div class="pull-right">
                        <button type="submit"class="btn btn-success green-btn" id="schedule_btn" name="schedule_btn">Save</button>
                        <button type="button"class="btn btn-danger closes" style="background-color:#ff1c00 !important;">Cancel</button>
                    </div>
                </form>
            </div>
            </div>
        </div>
  </div>
</div>

<link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
<script src="plugins/datatables/jquery.dataTables.min.js"></script> 
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script>
<script type="text/JavaScript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery.print/1.6.0/jQuery.print.js"></script>

<script type="text/javascript">
$('#example1').DataTable({
  "paging": true,
  "lengthChange": true,
  "searching": true,
  "ordering": true,
  "info": true,
  "autoWidth": false
});

$("#survey_type").on('change', function(){
  window.location = window.location.href.split('&')[0] + "&type=" + this.value
});

function scheduleTemplate(template_id, template_name, template_field, template_filter){
  $(".multiple-select").empty().trigger('change');
  $("#sch_modal_title").html(template_name);
  $("#sch_template_id").val(template_id);
  $("#sch_template_field_name").val(template_field);
  $("#label_template_field").text(template_field.toUpperCase());
  $.ajax({
    url: "ajax/common_file.php",
    type : 'POST',
    data : {
      mode : 'fetch_schedule_filter',
      filter : template_filter,
      field : template_field,
    },
    success: function(result){
      $(".multiple-select").append(new Option('Select option', ''));
      var options = JSON.parse(result);
      if(template_field == 'survey' || template_field == 'pulse' || template_field == 'engagement'){
        Object.keys(options).forEach(function(key) {
          var option = new Option(options[key], key, false, false);
          $(".multiple-select").append(option).trigger('change.select2');
        });
      }else{
        for(var i=0; i<options.length; i++){
          var option = new Option(options[i].name, options[i].id, false, false);
          $(".multiple-select").append(option).trigger('change.select2');
        }
      }
    }
  })
  $('#schedule_report_templates').modal('show');
}
</script>