<?php
 
if($_GET['type']== 'template'){
  record_set('template', 'select * from create_template_report where id !="" group by temp_id order by id desc');
}else{
  record_set('template', 'select * from schedule_report where id !="" group by temp_id order by id desc');
}

//   alert_delete(); die();
//  //delete report 
//   if(isset($_GET['delete'])){
//     alert_delete();
//     // record_set('template_delete', "DELETE FROM create_template_report where temp_id ='".$_GET['delete']."'");
//   }

if(isset($_POST['schedule_btn'])){
  $temp_id = $_POST['template_id'];
  $start = date('Y-m-d H:i:s', strtotime($_POST['start_date'])); 
  $next_date =  date('Y-m-d H:i:s',strtotime('+'.$_POST['interval'] .'hour',strtotime($start))); 

  record_set('create_report', 'select * from create_template_report where temp_id="'.$temp_id.'"');
    while($report_data = mysqli_fetch_assoc($create_report)){
      $data = array(
        'schedule_date'      => $start,
        'temp_name'          => $report_data['temp_name'],
        'temp_id'            => $report_data['temp_id'],
        'frequency'          => $_POST['interval'],
        'keyword'            => $report_data['keyword'],
        'value'              => $report_data['value'],
        "next_schedule_date" => $next_date,
        'survey_id'          => $_POST['survey_id'],
        'step_id'            => $_POST['step_id'],
        'question_id'        => $_POST['question_id'],
        'cby'                => $_SESSION['user_id'],
        'created_at'         => date('Y-m-d H:i:s'),
      );
      $insert = dbRowInsert('schedule_report', $data);
      if(!empty($insert)){
        dbRowDelete('create_template_report', "temp_id='".$temp_id."'");
        alertSuccess('Schedule Report Successfully','?page=manage-report-template&type=schedule');
      }
  }
}
?>
<section class="content-header">
  <h1><?=($_GET['type']=='template')?'VIEW TEMPLATE':'VIEW SCHEDULE'?></h1>
</section>
<section class="content">
    <div class="box">
    <div class="box-body table-responsive">
          <?php if($_GET['type']=='template') {?>
          <table id="example1" class="table table-bordered table-hover">
            <thead>
                <tr>
                <th>#</th>
                <th>Report Name</th>
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while($rpt = mysqli_fetch_assoc($template)){     
                $temp_id = $rpt['temp_id'];
                ?>
                <tr>
                  <td><?=$i?></td>
                  <td><?=$rpt['temp_name']?></td>
                  <td>
                      <a href="?page=create-report&type=temp-details&viewid=<?=$temp_id?>">
                        <button class="btn btn-primary blue-btn btn-xs" style="margin-right: 10px;padding: 0px 16px 0px 13px;">VIEW DETAILS</button>
                      </a>
                      <button class="btn btn-success btn-xs schedule-now green-btn" style="margin-right: 10px;padding: 0px 16px 0px 13px;" data-id="<?=$temp_id?>" data-date="<?= date('Y-m-d') ?>" >SCHEDULE NOW</button>
                      <!-- <a href="?page=manage-report-template&delete=<?=$temp_id?>"> -->
                          <button type="button" class="btn btn-danger btn-xs" style="margin-right: 10px;padding: 0px 16px 0px 13px;background-color:#e51900;" onclick="delete_data('create_template_report','<?=$temp_id?>')">DELETE</button>
                      <!-- </a> -->

                  </td>
                </tr> 
            <?php
            $i++;
              }
            ?>    
            </tbody>
            
          </table>
          <?php }else { ?> 
          <table id="example1" class="table table-bordered table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Report Name</th>
                <th>Created By</th>
                <th>Frequency</th>
                <th>Start Date</th>
                <th>Next Due Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1;
              while($rpt = mysqli_fetch_assoc($template)){     
                    $temp_id = $rpt['temp_id'];
                    $days = $rpt['frequency']/24;
                  ?>
                  <tr>
                    <td><?=$i?></td>
                    <td><?=$rpt['temp_name']?></td>
                    <td><?=$rpt['cby']?></td>
                    <td><?=$days?></td>
                    <td><?=date('d-m-Y', strtotime($rpt['schedule_date']))?></td>
                    <td><?=date('d-m-Y ', strtotime($rpt['next_schedule_date']))?></td>
                    <td>
                        <a href="?page=create-report&type=schedule-details&viewid=<?=$temp_id?>">
                          <button class="btn btn-primary blue-btn btn-xs" style="margin-right: 10px;padding: 0px 16px 0px 13px;">VIEW DETAILS</button>
                        </a>
                        <!-- <a href="?page=manage-report-template&delete=<?=$temp_id?>"> -->
                            <button type="button" class="btn btn-danger btn-xs" style="margin-right: 10px;padding: 0px 16px 0px 13px;background-color:#e51900;" onclick="delete_data('schedule_report','<?=$temp_id?>')">DELETE</button>
                        <!-- </a> -->

                    </td>
                  </tr> 
              <?php
              $i++;
              } ?>    
            </tbody>
          </table>
          <?php } ?>
        </div>
    </div>
</section>

<div class="modal" id="schedule_popup">
  <div class="modal-dialog" role="document">
      <div class="modal-content" style="height:200px ;">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"> </h5>
          <button type="button" class="close closes" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <form class="second_form" method="post">
              <div class="form-group row">
                <input type="hidden" name="template_id" value="" class="template_id">
                <label for="staticEmail" class="col-sm-4 col-form-label">Start Date</label>
                <div class="col-sm-8">
                  <input type="date"  class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="" min="<?= date('Y-m-d') ?>">
                </div>
              </div>
              <div class="form-group row">
                <label for="staticEmail" class="col-sm-4 col-form-label">Interval</label>
                <div class="col-sm-8">
                <select class="form-control" id="interval" name="interval">
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
           
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  $(".schedule-now").click(function(){
    var temp_id = $(this).data('id');
    var date = $(this).data('date');
    $("#start_date").val(date);
    $(".template_id").val(temp_id);
    $("#schedule_popup").show();
  })
  $(".closes").click(function() {
    $('#schedule_popup').hide();
    $('#create_popup').hide();
  });
  function openmodal(id){
    $('#tid').val(id)
    $('#delete').modal()
  }
</script>
