<section class="content-header">

  <h1> View Clients</h1>

  <a href="?page=add-clients" class="btn btn-primary pull-right" style="margin-top:-25px">Add Clients</a> </section>

<section class="content">

  <div class="box box-danger">

        <div class="box-body">

          <table id="example1" class="table table-bordered table-striped">

            <thead>

              <tr>

                <th>Name</th>

                <th>Email</th>

                <th>Phone</th>

                <!-- <th>Address</th> -->
                
                <th>Status</th>

                <th>Action</th>

              </tr>

            </thead>

            <tbody>

             <?php 

				record_set("get_clients", "select * from clients where cby='".$_SESSION['user_id']."' order by cdate desc");				
				while($row_get_clients = mysqli_fetch_assoc($get_clients)){ ?>
              <tr>
                <td><?php echo $row_get_clients['name'];?></td>

                <td><?php echo $row_get_clients['email'];?></td>

                <td><?php echo $row_get_clients['phone'];?></td>

                <!-- <td><?php echo $row_get_clients['address'];?></td> -->

                <td>

                 <?php 

					if($row_get_clients['cstatus']==1){

				?>		

				<span class="label label-success">Active</span>

				<?php 	}else{?>	

                <span class="label label-danger">Deactive</span>

                <?php }?>

                </td>

                <td><a class="btn btn-xs btn-info" href="?page=add-clients&id=<?php echo $row_get_clients['id']; ?>">Edit</a></td>

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