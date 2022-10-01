<section class="content-header">
  <h1> View Survey</h1>
   <!-- <a href="?page=add-survey" class="btn btn-primary pull-right" style="margin-top:-25px">Add Survey</a> -->
    </section>
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Survey Name</th>
                    <th>User Name	</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Entry Needed</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                 <?php 
                    //for super admin
                    if($_SESSION['user_type']==1){
                      $filter = '';
                    }else if($_SESSION['user_type']==2){
                      //for admin
                      $filter = " and (cby='".$_SESSION['user_id']."' and user_type='".$_SESSION['user_type']."') OR (`admin_ids` LIKE '%|".$_SESSION['user_id']."|%') ";
                    }else if($_SESSION['user_type']==3){
                        //for manager
                      $filter = " and (cby='".$_SESSION['user_id']."' and user_type='".$_SESSION['user_type']."')  OR (`client_ids` LIKE '%|".$_SESSION['user_id']."|%') ";
                    }
                  record_set("get_surveys", "select * from surveys where id>0 $filter order by cdate desc");				
                  while($row_get_surveys = mysqli_fetch_assoc($get_surveys)){
                    if($row_get_surveys['user_type']==2){
                      record_set("cname", "select * from admin where id='".$row_get_surveys['adminid']."'");
                      $tag = '<span class="label label-success blue-btn">A</span>';
                    }else {
                      record_set("cname", "select * from clients where id='".$row_get_surveys['clientid']."'");
                      $tag = '<span class="label label-info blue-btn">M</span>';
                    }
                    $row_cname = mysqli_fetch_assoc($cname);
                    record_set("dname", "select * from departments where id='".$row_get_surveys['departmentid']."'");		
                    $row_dname = mysqli_fetch_assoc($dname);
                ?>
                  <tr>
                    <td><?php echo $row_get_surveys['name'];?></td>
                    <td><?=($row_cname['name'])? $tag :'' ?><?php echo $row_cname['name'];?></td>
                    <td><?php echo $row_dname['name'];?></td>
                    <td><span class="label <?=($row_get_surveys['cstatus']==1)?'label-success':'label-danger'?>"><?php echo status_data($row_get_surveys['cstatus']);?></span></td>
                    <td><?php echo $row_get_surveys['survey_needed']; ?></td>
                    <td>
                    	<a class="btn btn-xs btn-danger" href="?page=add-survey&id=<?php echo $row_get_surveys['id'];?>">Edit</a>
                      <a class="btn btn-xs btn-primary" href="?page=view-survey_questions&surveyid=<?php echo $row_get_surveys['id'];?>">Questions</a>
                      <a class="btn btn-xs btn-info" href="survey-form.php?surveyid=<?php echo $row_get_surveys['id'];?>" target="_blank">View</a>
						          <a class="btn btn-xs btn-success" href="survey-result.php?surveyid=<?php echo $row_get_surveys['id'];?>" target="_blank">Result</a>
                      <a class="btn btn-xs bg-black" href="export-result.php?surveyid=<?php echo $row_get_surveys['id'];?>&name=<?php echo $row_get_surveys['name'];?>" target="_blank">Export CSV</a>
                      <a class="btn btn-xs bg-green" href="export-pdf.php?surveyid=<?php echo $row_get_surveys['id'];?>&name=<?php echo $row_get_surveys['name'];?>" target="_blank">Export PDF</a>
                    </td>
                  </tr>
                <?php }?>
                </tbody>
                <tfoot>
                  <tr>
                    <th>Survey Name</th>
                    <th>User Name	</th>
                    <th>Department</th>
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