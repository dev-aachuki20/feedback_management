<section class="content-header">
  <h1>VIEW GROUPS</h1>
</section>
<section class="content">
  <!-- Start location table -->
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
                /* if($_SESSION['user_type']<=2){
                  $filter = '';
                }else {
                  //for other
                  $filter = " and cby='".$_SESSION['user_id']."'";
                  $group_ids = get_assing_id_dept_loc_grp_survey('group');
                  if($group_ids){
                    $filter .= " OR id IN ($group_ids)";
                  }
                } */
                /* For Dgs Office */
                if($_SESSION['user_type'] < 2){
                  $filter = '';
                }else {
                  $group_ids = get_assing_id_dept_loc_grp_survey('group');
                  if($group_ids){
                    $filter .= " AND id IN ($group_ids)";
                  } else {
                    $filter .= " AND id IN (0)";
                  }
                }
                record_set("get_groups", "select * from `groups` where id>0 $filter order by cdate desc");				
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
                        <span class="label label-danger">Inactive</span>
                      <?php }?>
                  </td>
                  <td>
                    <a class="btn btn-xs btn-info btn-yellow" href="?page=add-group&id=<?php echo $row_get_groups['id'];?>">Edit</a>
                  </td>
                </tr>
              <?php }?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- End location table -->
</section>
<!-- End content section -->
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