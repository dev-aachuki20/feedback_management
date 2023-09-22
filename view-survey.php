<section class="content-header">
  <h1>VIEW <?=($_GET['type'])?strtoupper($_GET['type'].'S'):'SURVEYS'?> </h1>
   <!-- <a href="?page=add-survey" class="btn btn-primary pull-right" style="margin-top:-25px">Add Survey</a> -->
    </section>
  <script>
  $(function () {
  $('[data-toggle="popover"]').popover()
  })
  </script>
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th style="padding: 0px 0px 10px 0px;width: 61px;">Survey ID</th>
                    <th>Survey Name</th>
                    <th>Survey Type	</th>
                    <!-- <th>Department</th> -->
                    <th>Confidential</th>
                    <th>Status</th>
                    <!-- <th>Entry Needed</th> -->
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                 <?php 
                    
                    //for super admin and dgs
                    $survey_id = get_assing_id_dept_loc_grp_survey();
                    //$survey_id = implode(',',array_unique($survey_id));

                    if($_SESSION['user_type']<=2){
                      $filter = '';
                    }else {
                      //for admin and other user
                      $filter = " and cby=".$_SESSION['user_id']."";
                      if($survey_id){
                        $filter .= " OR id IN($survey_id)";
                      }else {
                        $filter .= " OR id IN(0)";
                      }
                    }
                    // check survey type using type 
                    $survey_type = array_search (ucfirst($_GET['type']), survey_type());
                    if($survey_type){
                      $filter .= " and 	survey_type =$survey_type";
                    }
                    
                  record_set("get_surveys", "select * from surveys where id>0 $filter order by cdate desc");				
                  while($row_get_surveys = mysqli_fetch_assoc($get_surveys)){
                    record_set("dname", "select * from departments where id='".$row_get_surveys['departmentid']."'");		
                    $row_dname = mysqli_fetch_assoc($dname);

                    $department_id  = $row_get_surveys['departments'];
                    if($row_get_surveys['survey_type'] == 1){
                      $btnClass = 'purple-btn';
                    }else if($row_get_surveys['survey_type'] == 2){
                      $btnClass = 'sky-blue-btn';
                    }else {
                      $btnClass = 'dark-blue-btn';
                    }
                    // $department_ids = explode(',',$department_id);
                    // $all_deparmentName = array();
                    // foreach($department_ids as $dept){
                    //   $all_deparmentName[] = getDepartment()[$dept];
                    // }
                    // $deptName = implode(',',$all_deparmentName);
                    ?>
                  <tr>
                    <td><?php echo $row_get_surveys['id'];?></td>
                    <td><?php echo $row_get_surveys['name'];?></td>
                    <td><?php if($row_get_surveys['survey_type']) { ?> <span class="label label-primary <?=$btnClass?>"><?=survey_type()[$row_get_surveys['survey_type']]?></span> <?php } ?></td>
                    <!-- <td>
                      <?php //if($deptName){?> 
                        <button type="button" class="btn btn-xs bg-green popover-dept" data-container="body" data-toggle="popover" data-placement="top" data-content="<?=$deptName?>"><?= getDepartment()[$department_ids[0]] ?></button> 
                      <?php //}?></td> -->
                    <td>
                      <?php if($row_get_surveys['confidential']==1) {
                        echo '<span class="btn btn-xs bg-green">Yes</span>';
                      }else {
                        echo '<span class="btn btn-xs btn-danger">No</span>';
                      }?>
                    <td><span class="label <?=($row_get_surveys['cstatus']==1)?'label-success':'label-danger'?>"><?php echo status_data($row_get_surveys['cstatus']);?></span></td>
                    <!-- <td><?php //echo $row_get_surveys['survey_needed']; ?></td> -->
                    <td>
                      <div class="btnCol">
                        <?php if(!isset($_GET['type'])) { ?>
                        <a class="btn btn-xs btn-danger btn-yellow" href="?page=add-survey&id=<?php echo $row_get_surveys['id'];?>">Edit</a>
                        <?php } ?>
                        <a class="btn btn-xs btn-primary addQrcode"  href="#" data-toggle="modal" data-target="#exampleModal" data-qr="<?php echo $row_get_surveys['qrcode'];?>" data-id="<?php echo $row_get_surveys['id'];?>">View QR</a>

                        <!-- <a class="btn btn-xs bg-green" href="?page=preview-survey&id=<?php //echo $row_get_surveys['id'];?>" >Preview</a> -->
                        <a class="btn btn-xs bg-green"  href="./survey-form.php?surveyid=<?=$row_get_surveys['id']?>" target="_blank">Preview</a>

                        <?php if($_SESSION['user_type']==1 and !isset($_GET['type'])) {?>
                          <a class="btn btn-xs btn-info" href="?page=view-survey_questions&surveyid=<?php echo $row_get_surveys['id'];?>">Questions</a>

                          <!-- <a class="btn btn-xs btn-primary" href="survey-form.php?surveyid=<?php echo $row_get_surveys['id'];?>" target="_blank">View</a>

                          <a class="btn btn-xs btn-success" href="survey-result.php?surveyid=<?php echo $row_get_surveys['id'];?>" target="_blank">Result</a> -->

                          <!-- <a class="btn btn-xs bg-black" href="export-result.php?surveyid=<?php echo $row_get_surveys['id'];?>&name=<?php echo $row_get_surveys['name'];?>" target="_blank">Export CSV</a> -->

                          <!-- <a class="btn btn-xs bg-green" href="export-pdf.php?surveyid=<?php echo $row_get_surveys['id'];?>&name=<?php echo $row_get_surveys['name'];?>" target="_blank">Export PDF</a> -->
                        <?php } ?>
                      </div>
                    </td>
                  </tr>
                <?php }?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
 <!-- Qr code modal -->
 <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div id="print">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" id="exampleModalLabel"><strong>Survey ID: </strong><span class="surveyId"></span></h4>
          <h4 class="modal-title" id="exampleModalLabel"><strong>Survey Name: </strong><span class="surveyName"></span></h4>
        </div>
        <div class="modal-body">
				<center>
          <div id='qrImage'>

          </div>
        </center>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="printId" >Print</button>
      </div>
    </div>
  </div>
</div>

  <link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
  <script src="plugins/datatables/jquery.dataTables.min.js"></script> 
  <script src="plugins/datatables/dataTables.bootstrap.min.js"></script>
  <script type="text/JavaScript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery.print/1.6.0/jQuery.print.js"></script>

  <script type="text/javascript">
    $(function () {
      $("#example1").DataTable({"paging": true,"ordering": false});
      
      $('#example2').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": true,
        "info": true,
        "autoWidth": false
      });
    });

  $("#printId").on('click',function () {
    //document.title='My new title';
    $("#print").print();
  });

  $(".addQrcode").on('click',function(){
    let allSurvey = jQuery.parseJSON('<?=json_encode(getSurvey())?>');
    let qrCode = $(this).data("qr");
    let surveyId = $(this).data("id");
    
    $('.surveyId').html(surveyId);
    $('.surveyName').html(allSurvey[surveyId]);
    let imageUrl = '<img alt='+qrCode+' src=qrcode.php?text=<?=getHomeUrl()?>survey-form.php?qrcode='+qrCode+'>';
    $("#qrImage").html(imageUrl);
  })
  </script>