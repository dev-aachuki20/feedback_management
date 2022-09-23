<?php
$userdata = array();
record_set("get_clients", "select * from clients where cby='".$_SESSION['user_id']."' order by cdate desc");
if($_POST['user_type']==1 || !isset($_POST['user_type']) || $_POST['user_type']==0){
    while($row_get_clients = mysqli_fetch_assoc($get_clients)){
        $userdata[] = $row_get_clients ;
    }
}				
if($_POST['user_type']==2 || !isset($_POST['user_type']) || $_POST['user_type']==0){
    record_set("get_admin", "select * from admin  order by cdate desc");
    while($row_get_admin = mysqli_fetch_assoc($get_admin)){
        $userdata[] = $row_get_admin ;
    }
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
                        <option value="1" <?php if($_POST['user_type']==1){ echo 'selected';}?>>Client</option>
                        <option value="2" <?php if($_POST['user_type']==2){ echo 'selected';}?>>Admin</option>
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
             <?php  foreach($userdata as $row_get_clients){ 
                if (array_key_exists('locationid', $row_get_clients)) {
                    $userType = 'Client';
                    $t ='c';
                }else {
                    $userType = 'Admin';
                    $t ='a';
                }
             ?>
              <tr>
                <td><?php echo $row_get_clients['name'];?></td>

                <td><?php echo $row_get_clients['email'];?></td>

                <td><?php echo $row_get_clients['phone'];?></td>

                <td> 
                    <span class="label label-primary"><?php echo $userType;?></span>
                </td>

                <td>
                    <?php if($row_get_clients['cstatus']==1){ ?>		
                    <span class="label label-success">Active</span>

                    <?php }else{?>	

                    <span class="label label-danger">Deactive</span>

                    <?php }?>
                </td>

                <td><a class="btn btn-xs btn-info" href="?page=add-user&t=<?=$t?>&id=<?php echo $row_get_clients['id']; ?>">Edit</a></td>
              </tr>

              <?php }?>

             </tbody>

            <tfoot>

              <tr>

                <th>Name</th>

                <th>Email</th>

                <th>Phone</th>

                <!-- <th>Address</th> -->

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