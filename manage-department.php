
<section class="content-header">
<h1> View Departments</h1>
</section>
<section class="content">
  <!-- Start Department Table-->
  <div class="box box-secondary">
    <div class="row">
      <div class="col-md-12">
        <div class="box-body">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Action</th>
               </tr>
            </thead>
            <tbody>
            <?php 
            //for super admin and Dgs User
            if($_SESSION['user_type'] <=2){
              $filter = '';
            }else {
              //for other
              $filter = " and cby='".$_SESSION['user_id']."'";
              $department_ids = get_assing_id_dept_loc_grp_survey('department');
              if($department_ids){
                $filter .= " OR id IN ($department_ids)";
              }
            }
              record_set("get_departments", "select * from departments where id>0 $filter order by cdate desc");				
              while($row_get_departments = mysqli_fetch_assoc($get_departments)){ ?>
              <tr>
                <td><?php echo $row_get_departments['name'];?></td>
                <td>
                  <?php  if($row_get_departments['cstatus']==1){ ?>		
                      <span class="label label-success">Active</span>
                  <?php 	}else{?>	
                      <span class="label label-danger">Inactive</span>
                  <?php }?>
                </td>
                <td>
                  <a class="btn btn-xs btn-info btn-yellow" href="?page=add-department&id=<?php echo $row_get_departments['id'];?>">Edit</a>
                </td>
              </tr>
             <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- End Department Table-->
</section>

<link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
<script src="plugins/datatables/jquery.dataTables.min.js"></script> 
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script> 
<script>
      $(function () {
        $("#example1").DataTable({"ordering": false});
        $('#example2').DataTable({
          "paging": true,
          "lengthChange": false,
          "searching": false,
          "ordering": false,
          "info": true,
          "autoWidth": false
        });
      });
    </script>
<script type="application/javascript">
 $(document).ready(function(){
	  $('.form-control').click(function(){
		  $(this).css('border-color', '#ccc');
		});
		
	  $('#submit').click(function(){
			var name = $("#name").val();
			if(name==''){
				document.getElementById('name').style.borderColor = "#ff0000";	
				alert("Name is required");
				document.getElementById('name').focus();
				return false;					
				}
			
		});	
		
	});
</script>