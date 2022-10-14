<section class="content-header">
  <h1> View Survey</h1>
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
                    <th>Survey Name</th>
                    <th>Survey Type	</th>
                    <th>Department</th>
                    <th>Confidential</th>
                    <th>Status</th>
                    <th>Entry Needed</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                 <?php 
                    //for super admin and dgs
                    $survey_id = get_assing_id_dept_loc_grp_survey('survey');
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
                    
                  record_set("get_surveys", "select * from surveys where id>0 $filter order by cdate desc");				
                  while($row_get_surveys = mysqli_fetch_assoc($get_surveys)){
                    
                    record_set("dname", "select * from departments where id='".$row_get_surveys['departmentid']."'");		
                    $row_dname = mysqli_fetch_assoc($dname);

                    $department_id  = $row_get_surveys['departments'];
                    $department_ids = explode(',',$department_id);
                    $all_deparmentName = array();
                    foreach($department_ids as $dept){
                      $all_deparmentName[] = getDepartment()[$dept];
                    }
                    $deptName = implode(',',$all_deparmentName);
                ?>
                  <tr>
                    <td><?php echo $row_get_surveys['name'];?></td>
                    <td><?php if($row_get_surveys['survey_type']) { ?> <span class="label label-primary blue-btn"><?=survey_type()[$row_get_surveys['survey_type']]?></span> <?php } ?></td>
                    <td>
                      <?php if($deptName){?> 
                        <button type="button" class="btn btn-xs bg-green popover-dept" data-container="body" data-toggle="popover" data-placement="top" data-content="<?=$deptName?>"><?= getDepartment()[$department_ids[0]] ?></button> 
                      <?php }?></td>
                    <td><span class="btn btn-xs btn-info"><?=($row_get_surveys['confidential']==1)?'YES':'NO'?></span></td>
                    <td><span class="label <?=($row_get_surveys['cstatus']==1)?'label-success':'label-danger'?>"><?php echo status_data($row_get_surveys['cstatus']);?></span></td>
                    <td><?php echo $row_get_surveys['survey_needed']; ?></td>
                    <td>
                      <div class="btnCol">
                    	<a class="btn btn-xs btn-danger" href="?page=add-survey&id=<?php echo $row_get_surveys['id'];?>">Edit</a>
                      <a class="btn btn-xs btn-primary" href="?page=view-survey_questions&surveyid=<?php echo $row_get_surveys['id'];?>">Questions</a>
                      <a class="btn btn-xs btn-info" href="survey-form.php?surveyid=<?php echo $row_get_surveys['id'];?>" target="_blank">View</a>
						          <a class="btn btn-xs btn-success" href="survey-result.php?surveyid=<?php echo $row_get_surveys['id'];?>" target="_blank">Result</a>
                      <a class="btn btn-xs bg-black" href="export-result.php?surveyid=<?php echo $row_get_surveys['id'];?>&name=<?php echo $row_get_surveys['name'];?>" target="_blank">Export CSV</a>
                      <a class="btn btn-xs bg-green" href="export-pdf.php?surveyid=<?php echo $row_get_surveys['id'];?>&name=<?php echo $row_get_surveys['name'];?>" target="_blank">Export PDF</a>
                      </div>
                    </td>
                  </tr>
                <?php }?>
                </tbody>
                <tfoot>
                  <tr>
                    <th>Survey Name</th>
                    <th>Survey Type		</th>
                    <th>Department</th>
                    <th>Confidential</th>
                    <th>Status</th>
                    <th>Entry Needed</th>
                    <th>Action</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

  <link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
  <script src="plugins/datatables/jquery.dataTables.min.js"></script> 
  <script src="plugins/datatables/dataTables.bootstrap.min.js"></script>
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
    


  </script>