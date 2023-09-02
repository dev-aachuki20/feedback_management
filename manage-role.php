
<section class="content-header">
<h1> VIEW ROLES</h1>
</section>
<section class="content">
  <!-- Start Role Table-->
  <div class="box box-secondary">
    <div class="row">
      <div class="col-md-12">
        <div class="box-body">
          <table id="manage-table" class="table table-bordered table-striped">
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
              $role_ids = get_assing_id_dept_loc_grp_survey('role');
              if($role_ids){
                $filter .= " OR id IN ($role_ids)";
              }
            }
              record_set("get_roles", "select * from roles where id>0 $filter order by cdate desc");				
              while($row_get_roles = mysqli_fetch_assoc($get_roles)){ ?>
              <tr>
                <td><?php echo $row_get_roles['name'];?></td>
                <td>
                  <?php  if($row_get_roles['cstatus']==1){ ?>		
                      <span class="label label-success">Active</span>
                  <?php 	}else{?>	
                      <span class="label label-danger">Inactive</span>
                  <?php }?>
                </td>
                <td>
                  <a class="btn btn-xs btn-info btn-yellow" href="?page=add-role&id=<?php echo $row_get_roles['id'];?>">Edit</a>
                </td>
              </tr>
             <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- End role Table-->
</section>
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