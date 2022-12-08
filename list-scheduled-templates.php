<?php
record_set('scheduled_report_templates', 'SELECT rt.name,rt.report_type, srt.* FROM report_templates AS rt INNER JOIN scheduled_report_templates AS srt ON srt.temp_id = rt.id ORDER BY srt.created_at DESC');
?>

<section class="content-header">
  <h1>VIEW SCHEDULE</h1>
</section>

<section class="content">
  <div class="box">
    <div class="box-body table-responsive">
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
        <?php $i = 0; ?>
        <?php while($scheduled_report_template = mysqli_fetch_assoc($scheduled_report_templates)){ ?>
          <tr>
            <td><?= ++$i ?></td>
            <td><?=$scheduled_report_template['name']?></td>
            <td><?=get_user_datails($scheduled_report_template['cby'])['name']?></td>
            <td><?=ucfirst(service_type()[$scheduled_report_template['sch_interval']])?></td>
            <td><?=date('d-m-Y', strtotime($scheduled_report_template['start_date']))?></td>
            <td><?=date('d-m-Y ', strtotime($scheduled_report_template['next_date']))?></td>
            <td>
              <?php if($scheduled_report_template['report_type']==1){ ?> 
                <a href="report-doc/report-pdf.php?report_id=<?=$scheduled_report_template['id']?>">
                  <button class="btn btn-primary blue-btn btn-xs">VIEW PDF</button>
                </a>
                <a href="report-doc/report-pdf.php?export=csv&report_id=<?=$scheduled_report_template['id']?>">
                  <button type="button" class="btn btn-primary blue-btn btn-xs btn-green">DOWNLOAD CSV</button>
                </a>
                <form action="./ajax/ajaxOn_survey_statistics.php?export=csv&data_type=<?= $report_template['fields'] ?>" method="post" style="display: inline-block;" id="report-form">
                  <!-- <button type="submit" class="btn btn-primary blue-btn btn-xs btn-green">DOWNLOAD CSV</button> -->
                </form>
              <?php } else { ?>
                <a href="report-doc/report-pdf-question-pdf.php?report_id=<?=$scheduled_report_template['id']?>">
                  <button class="btn btn-primary blue-btn btn-xs">VIEW PDF</button>
                </a>
                <a href="report-doc/report-question-csv.php?export=csv&report_id=<?=$scheduled_report_template['id']?>">
                  <button type="submit" class="btn btn-primary blue-btn btn-xs btn-green">DOWNLOAD CSV</button>
                </a>
                <!-- <form action="./ajax/ajaxOn_survey_statistics.php?export=csv&data_type=<?= $report_template['fields'] ?>" method="post" style="display: inline-block;" id="report-form">
                  <button type="submit" class="btn btn-primary blue-btn btn-xs btn-green">DOWNLOAD CSV</button>
                </form> -->
              <?php }?>
                <!-- <button class="btn btn-primary blue-btn btn-xs" style="margin-right: 10px;padding: 0px 16px 0px 13px;" onclick="scheduled_details(<?=$scheduled_report_template['id']?>, '<?=$scheduled_report_template['name']?>')">VIEW DETAILS</button> -->
                <button type="button" class="btn btn-danger btn-xs" style="margin-right: 10px;padding: 0px 16px 0px 13px;background-color:#e51900;" onclick="delete_data('scheduled_report_templates','<?=$scheduled_report_template['id']?>')">DELETE</button>
            </td>
          </tr>  
        <?php } ?>  
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- scheduled details modal -->
<div class="modal" id="scheduled_template_details">
  <div class="modal-dialog">
    <div class="modal-content" style="height:300px ;">
        <div class="modal-header">
          <h5 id="sch_modal_title" class="modal-title"> </h5>
          <button type="button" class="close closes" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
        </div>
        <div id="sch_details_body" class="modal-body">

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

function scheduled_details(sch_id, temp_name){
  $("#sch_modal_title").html(temp_name);
  $.ajax({
    url: "ajax/common_file.php",
    type : 'POST',
    data : {
      mode : 'fetch_scheduled_template_details',
      sch_id : sch_id,
    },
    success: function(result){
      var response = JSON.parse(result);
      var body_html = '<ul">';
      Object.keys(response).forEach(function(key) {
        body_html += '<li>'+response[key]+'</li>';
      });
      body_html += '</ul>';
      $("#sch_details_body").html(body_html);
    }
  })
  $('#scheduled_template_details').modal('show');
}
$('.report-document').click(function(){
  // let doc_type = $(this).val();
  // let report_type = $(this).data('id');
  // if(doc_type == 'pdf' && report_type==1){
  //   $('#report-form').attr('action', './report-doc/report-pdf.php'); 
  //   $('#report-form').submit();
  // }else if(doc_type == 'pdf' && report_type==2){
  //   $('#report-form').attr('action', './report-doc/report-pdf-question.php'); 
  //   $('#report-form').submit();
  // }

  
})
</script>
