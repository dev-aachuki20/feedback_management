<section class="content-header">
  <h1> Survey Question</h1>
   <a href="?page=add-survey_questions&surveyid=<?php  echo $_REQUEST['surveyid'];?>" class="btn btn-primary pull-right" style="margin-top:-25px">Add Question</a>
    </section>
    <section class="content">
          <div class="box box-solid">
		  <?php 
					record_set("get_surveys", "select * from surveys where id='".$_REQUEST['surveyid']."'");				
					$row_get_surveys = mysqli_fetch_assoc($get_surveys);
          $filter ="";
					if($row_get_surveys['departments']){
            $filter = "where id IN (".$row_get_surveys['departments'].")";
          }
					record_set("dname", "select * from departments $filter");		
          $allDepartment = array();		
					while($row_dname = mysqli_fetch_assoc($dname)){
            $allDepartment[] = $row_dname['name'];
          }
			?>
            <div class="box-header with-border">
			<?php
					if(isset($_GET['msg']))
					{
				?>
				<div class="alert alert-success" role="alert">
				 <?php echo $_GET['msg']; ?>
				</div>
				<?php
					}
				?>
              <i class="fa fa-text-width"></i>

              <h3 class="box-title">SURVEY NAME - <?php echo $row_get_surveys['name']?></h3>
            </div>
            <div class="box-body">
            <p><strong>Client Department</strong> - <?php foreach($allDepartment as $dept){ ?>
              <label for="" class="btn btn-xs btn-info" style=" cursor: unset;"> <?=$dept?></label>
            <?php }?>
           </p>
            </div>
          </div>
          <div class="box">
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Questions</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
				<?php 
					record_set("get_questions", "select * from questions where cby='".$_SESSION['user_id']."' OR surveyid='".$_REQUEST['surveyid']."'");				
					while($row_get_questions = mysqli_fetch_assoc($get_questions))
					{   ?>
              <tr>
                <td><?php if(empty($row_get_questions['parendit'])){ ?><strong><?php } ?><?php echo $row_get_questions['question'];?><?php if(empty($row_get_questions['parendit'])){ ?></strong><?php } ?></td>
                <td><?php echo question_type_name($row_get_questions['answer_type']); ?></td>
                <td><?=($row_get_questions['cstatus']==1)? '<span class="label label-success">'.status_data($row_get_questions['cstatus']).'</span>':'<span class="label label-danger">'.status_data($row_get_questions['cstatus']).'</span>'?></td>
                <td>
                <a class="btn btn-xs btn-info" href="?page=edit-survey_questions&surveyid=<?php  echo $_REQUEST['surveyid'];?>&questionid=<?php  echo $row_get_questions['id'];?>">Edit</a>

                <!-- <a class="btn btn-xs btn-info" href="?page=editSurveyQuestion&surveyid=<?php  //echo $_REQUEST['surveyid'];?>&questionid=<?php  //echo $row_get_questions['id'];?>">Edit Language Text</a> -->
                </td>
              </tr>
        <?php	} ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th>Survey Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        
    </section>
    
    <link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
    <script src="plugins/datatables/jquery.dataTables.min.js"></script> 
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script> 
    
    <script>
      $(function () {
        $('#example1').DataTable({
          "paging": true,
          "lengthChange": false,
          "searching": false,
          "ordering": false,
          "info": true,
          "autoWidth": false,
		  "order": []
        });
      });
    </script>