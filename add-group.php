<?php
if(!empty($_GET['id'])){
    record_set("get_groups_id", "select * from groups where id='".$_GET['id']."'");
    $row_get_groups_id = mysqli_fetch_assoc($get_groups_id);
}

$client_id = '';
$admin_id  = '';
if($_POST){
  $client_id .= implode('|', $_POST['client_id']); 
  $client_id ="|".$client_id."|";

  $admin_id .= implode('|', $_POST['admin_id']); 
  $admin_id ="|".$admin_id."|";
}
// Start Update
if($_POST['update']){
    $data =  array(
        "name"          => $_POST['name'],
        "cstatus"       => $_POST['status'],
        "location_id"   => implode('|',$_POST['locations']),
        "client_ids"    => $client_id,
        "admin_ids"     => $admin_id 
    );

	$updte=	dbRowUpdate("groups", $data, "where id=".$_GET['id']);
    if(!empty($updte)){
      $msg = "Group Updated Successfully";
      alertSuccess($msg,'?page=manage-groups');
    }else{
      $msg = "User Not Updated Successfully";
      alertdanger($msg,'manage-groups&id='.$_GET["id"]);
    }
   // reDirect("?page=manage-groups&id=".$_GET["id"]."&msg=".$msg);			
}
    // End Update	

    // Start insert
	if(!empty($_POST['submit'])){
        $data =  array(
            "name"          => $_POST['name'],
            "cstatus"       => $_POST['status'],
            "location_id"   => implode('|',$_POST['locations']),
            'cip'           => ipAddress(),
            "client_ids"    => $client_id,
            "admin_ids"     => $admin_id,
            'cby'           => $_SESSION['user_id'],
            'cdate'         => date("Y-m-d H:i:s")
        );

        $insert_value =  dbRowInsert("groups",$data);
        if(!empty($insert_value )){	
            $msg = "Group Added Successfully";
            alertSuccess($msg,'?page=manage-groups');
        }else{
            $msg = "Some Error Occourd. Please try again..";
            alertdanger($msg,'?page=add-group');
        }
       // reDirect("?page=manage-groups&msg=".$msg);		
	}
  // End Insert  
?>
<section class="content-header">
  <h1> Add Group</h1>
</section>
<section class="content">
  <div class="box box-danger">
    <div class="box-body">
      <form action="" method="post" id="groupForm">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Name</label>
              <input type="text" class="form-control" name="name" id="name" value="<?php echo $row_get_groups_id['name'];?>" required/>
            </div>
          </div>
          <!-- End name according to languages -->
          <div class="col-md-4">
            <div class="form-group">
              <label>Status</label>
              <select class="form-control" name="status">
                <?php 
                  foreach(status() as $key => $value){
                ?>
                  <option <?php if($row_get_groups_id['cstatus']==$key){?> selected="selected"<?php }?>value="<?php echo $key; ?>"><?php echo $value; ?></option>                    
                <?php }?>
              </select>
            </div>
          </div>
          <!-- add location -->
          <div class="col-md-12 with-border">
              <h4>Location</h4>
              <input type="checkbox" style="margin-left: 15px;" onclick="checked_all(this,'locCheckbox')" /><strong> Select All</strong><br/><br/>
            </div>
            <div class="col-md-12">
              <?php
               $location_ids = explode('|',$row_get_groups_id['location_id']);
               foreach(getLocation() as $key => $value){
                ?>
                <div class="col-md-4">
                  <input type="checkbox" <?=(in_array($key,$location_ids) ? 'checked ':' ')?> id="locations_id_<?php echo $key ?>" class="locCheckbox" value="<?php echo $key; ?>" name="locations[<?php echo $key; ?>]" /> 
                  
                  <label for="locations_id_<?php echo $key; ?>">
                  <?php echo $value ?>
                  </label>
                </div>
              <?php } ?>
              <!-- <div class="row">
                <span class="col-md-12 user_error" style="color: red;font-weight: 700;margin-left: 17px;display:none;">Please choose atleast one option either from admin or client</span>    
              </div> -->
            </div>          
          <!-- assign user start -->        
            <div class="col-md-12 with-border">
              <h4>Assign Admins</h4>
              <input type="checkbox" style="margin-left: 15px;" onclick="checked_all(this,'admin_checkbox')" /><strong> Select All</strong><br/><br/>
            </div>
            <div class="col-md-12">
              <?php
              if(($row_get_groups_id['admin_ids'])){
                  $admin_saved = explode("|",$row_get_groups_id['admin_ids']);
              }else{
                  $admin_saved = array();
              }
              foreach(getAdmin() as $key => $value){ ?>
                <div class="col-md-4">
                  <input type="checkbox" <?=(in_array($key,$admin_saved) ? 'checked ':' ')?> id="admin_id_<?php echo $key ?>" class="userClass admin_checkbox" value="<?php echo $key; ?>" name="admin_id[<?php echo $key; ?>]" /> 
                  
                  <label for="admin_id_<?php echo $key; ?>">
                  <?php echo $value ?>
                  </label>
                </div>
              <?php } ?>
              <div class="row">
                <span class="col-md-12 user_error" style="color: red;font-weight: 700;margin-left: 17px;display:none;">Please choose atleast one option either from admin or client</span>    
              </div>
            </div>
            <div class="col-md-12 with-border">
              <h4>Assign Clients</h4>
              <input type="checkbox" style="margin-left: 15px;" onclick="checked_all(this,'client_checkbox')" /><strong> Select All</strong><br/><br/>
            </div>
            <div class="col-md-12">
              <?php
              if(($row_get_groups_id['client_ids'])){
                $client_saved = explode("|",$row_get_groups_id['client_ids']);
              }else{
                $client_saved = array();
              }
              foreach(getClient() as $key => $value){ ?>
                <div class="col-md-4">
                  <input type="checkbox" <?=(in_array($key,$client_saved) ? 'checked ':' ')?>  id="client_id_<?php echo $key ?>" class="userClass client_checkbox" value="<?php echo $key; ?>" name="client_id[<?php echo $key; ?>]" /> 
                  
                  <label for="client_id_<?php echo $key; ?>">
                  <?php echo $value ?>
                  </label>
                </div>
              <?php } ?>
              <div class="row">
                <span class="col-md-12 user_error" style="color: red;font-weight: 700;margin-left: 17px;display:none;">Please choose atleast one option either from admin or client</span>    
              </div>
            </div>
            <!-- assign user end -->   
        </div>
        <!-- End row -->
        <!-- Start submit button -->
        <div class="text-right">
            <?php  if(empty($_GET['id'])){ ?>
              <input type="Submit" class="btn btn-primary" value="Create" name="submit" id="submit"/>
            <?php }else{?>                
              <input type="Submit" class="btn btn-primary" value="Update" name="update"/>
            <?php }?>
            <span class="text-danger"><?php echo $_GET['msg']; ?></span>
        </div>
        <!-- End submit button -->
      </form>
    </div>
  </div>
</section>
<!-- End content section -->
<script type="application/javascript">
  $(document).ready(function(){
    $(function() {
      var validator =  $("#groupForm").validate({
          rules: {
              name: "required",
              deparments:"required",
              
          },
          messages: {
              name		: "This Field required",
              deparments: "This Field required",
          },
          
          // Make sure the form is submitted to the destination defined
          // in the "action" attribute of the form when valid
          submitHandler: function(form) {
            if ($('.userClass').is(':checked')) {
            $('.user_error').hide();
              form.submit();
            }else {
              $('.user_error').show();
            }
          }
      });
    })	
    $('.userClass').change(function () {
      if ($('.userClass').is(':checked')) {
        $('.user_error').hide();
      }
    });

	});
</script>