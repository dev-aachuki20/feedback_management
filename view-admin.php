<section class="content-header">

  <h1> View Admin </h1>

  <a href="?page=add_admin" class="btn btn-primary pull-right" style="margin-top:-25px">Add Admin</a> </section>

<section class="content">

      <div class="box">

        <div class="box-body">

          <table id="example1" class="table table-bordered table-striped">

            <thead>

              <tr>

                <th>Name</th>

                <th>Email</th>

                <th>Phone</th>

                <th>Status</th>

                <th>Action</th>

              </tr>

            </thead>

            <tbody>

            <?php 

				record_set("get_admin", "select * from admin where cstatus=1 order by cdate desc");

				

				while($row_get_admin = mysqli_fetch_assoc($get_admin)){

			?>

              <tr>

                <td><?php echo $row_get_admin['name']?></td>

                <td><?php echo $row_get_admin['email']?></td>

                <td><?php echo $row_get_admin['phone']?></td>

                <td>

                <?php 

					if($row_get_admin['cstatus']==1){

				?>		

				<span class="label label-success">Active</span>

				<?php 	}else{?>	

                <span class="label label-danger">Deactive</span>

                <?php }?>

                </td>

                <td><a class="btn btn-xs btn-info" href="?page=add_admin&id=<?php echo $row_get_admin['id']?>">Edit</a></td>

              </tr>

              <?php }?>

            </tbody>

            <tfoot>

              <tr>

                <th>Name</th>

                <th>Email</th>

                <th>Phone</th>

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