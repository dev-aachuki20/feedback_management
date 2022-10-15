<?php
$userdata = array();
if($_SESSION['user_type']>2){
  $filterUser = " and user_type >=".$_SESSION['user_type']." or cby=".$_SESSION['user_id'];
}
$userFilter = '';
if(isset($_POST['user_type']) and $_POST['user_type'] !=0){
  $userFilter = " and user_type =".$_POST['user_type'];
}
record_set("get_users", "select * from manage_users where id !=0  $userFilter $filterUser order by cdate desc");
while($row_get_users = mysqli_fetch_assoc($get_users)){
    $userdata[] = $row_get_users ;
}
?>
<section class="content-header">
  <h1> View Clients</h1>
  <!-- <a href="?page=add-clients" class="btn btn-primary pull-right" style="margin-top:-25px">Add Clients</a>  -->
</section>
<section class="content">
    <div class="box box-danger">
        <div class="row" style="margin: 10px 0px 10px 0px;">
            <form action="" method="post">
                <div class="col-md-3">
                    <label for="user">User Type</label>
                    <select class="form-control" aria-label="Default select example" onchange="this.form.submit()" name="user_type">
                        <option selected value="0">Select All</option>
                        <option value="2" <?php if($_POST['user_type']==2){ echo 'selected';}?>>Super Admin</option>
                        <option value="3" <?php if($_POST['user_type']==3){ echo 'selected';}?>>Admin</option>
                        <option value="4" <?php if($_POST['user_type']==4){ echo 'selected';}?>>Manager</option>
                    </select> 
                </div>
            </form>
        </div>
        <div class="box-body">
          <table id="example1" class="table table-bordered table-striped">

            <thead>

              <tr>

                <th>Name</th>

                <th>Email</th>

                <th>Phone</th>

                <th>Type</th>

                <th>Status</th>

                <th>Action</th>

              </tr>

            </thead>

            <tbody>
             <?php  foreach($userdata as $users){ 
              if($users['user_type']== 1){
                $userType = 'DGS';
              }else if($users['user_type']== 2){
                $userType = 'Super Admin';
              }else if($users['user_type']== 3){
                $userType = 'Admin';
              }else if($users['user_type']== 4){
                $userType = 'Manager';
              }
            ?>
              <tr>
                <td><?php echo $users['name'];?></td>

                <td><?php echo $users['email'];?></td>

                <td><?php echo $users['phone'];?></td>

                <td> 
                    <span class="label label-primary"><?php echo $userType;?></span>
                </td>

                <td>
                    <?php if($users['cstatus']==1){ ?>		
                    <span class="label label-success">Active</span>

                    <?php }else{?>	

                    <span class="label label-danger">Deactive</span>

                    <?php }?> 
                </td>

                <td><a class="btn btn-xs btn-info" href="?page=add-user&id=<?php echo $users['id']; ?>">Edit</a></td>
              </tr>

              <?php }?>

             </tbody>

            <tfoot>

              <tr>

               
                <th>Name</th>

                <th>Email</th>

                <th>Phone</th>

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