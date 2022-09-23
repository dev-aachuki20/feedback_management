<section class="content-header">
  <h1> View Groups</h1>
</section>
<section class="content">
  <!-- Start location table -->
  <div class="box box-danger">
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
              if($_SESSION['user_type']==1){
                $filter = '';
              }else if($_SESSION['user_type']==2){
                //for admin
                $filter = " and (cby='".$_SESSION['user_id']."' and user_type='".$_SESSION['user_type']."') OR (`admin_ids` LIKE '|".$_SESSION['user_id']."|') ";
              }else if($_SESSION['user_type']==3){
                 //for manager
                $filter = " and (cby='".$_SESSION['user_id']."' and user_type='".$_SESSION['user_type']."')  OR (`client_ids` LIKE '|".$_SESSION['user_id']."|') ";
              }
                record_set("get_groups", "select * from groups where id>0 $filter order by cdate desc");				
                while($row_get_groups = mysqli_fetch_assoc($get_groups)){
              ?>
                <tr>
                  <td><?php echo $row_get_groups['name'];?></td>
                  <td>
                      <?php 
                        if($row_get_groups['cstatus']==1){
                      ?>		
                        <span class="label label-success">Active</span>
                      <?php 	}else{?>	
                        <span class="label label-danger">Deactivated</span>
                      <?php }?>
                  </td>
                  <td>
                    <a class="btn btn-xs btn-info" href="?page=add-group&id=<?php echo $row_get_groups['id'];?>">Edit</a>
                  </td>
                </tr>
              <?php }?>
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
  <!-- End location table -->
</section>
<!-- End content section -->

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