<?php
if(!empty($_GET['id'])){
	record_set("get_locations_id", "select * from locations where id='".$_GET['id']."'");
	$row_get_locations_id = mysqli_fetch_assoc($get_locations_id);
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
    $deparment_id = implode("|",$_POST['deparments']);
    $dataCol =  array(
        "name"          => $_POST['name'],
        "cstatus"       => $_POST['status'],
        "department_id" => $deparment_id,
        "client_ids"    => $client_id,
        "admin_ids"     => $admin_id 
    );
    // $lang_col=array();
    // record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
    // while($row_get_language = mysqli_fetch_assoc($get_language)){	
    //   if($row_get_language['id'] !=1){
    //       $lang_col["name_".$row_get_language['iso_code']] = $_POST['name_'.$row_get_language['iso_code']];
    //   }
    // }

    //$data = array_merge($dataCol,$lang_col);
		$updte=	dbRowUpdate("locations", $dataCol, "where id=".$_GET['id']);
    
    if(!empty($updte)){
      $msg = "User Updated Successfully";
      alertSuccess( $msg,'?page=manage-locations');
    }else{
      $msg = "User Not Updated Successfully";
      alertdanger( $msg,'page=add-location&id='.$_GET["id"]);
    }
    //reDirect("?page=manage-locations&id=".$_GET["id"]);			
}
// End Update	

  // Start insert
	if(!empty($_POST['submit'])){
    $dataCol =  array(
        "name"        => $_POST['name'],
        "cstatus"     => $_POST['status'],
        'cip'         => ipAddress(),
        "client_ids"  => $client_id,
        "user_type"   => $_SESSION["user_type"],
        "admin_ids"   => $admin_id,
        'cby'         => $_SESSION['user_id'],
        'cdate'       => date("Y-m-d H:i:s")
    );

    //   $lang_col=array();
    //   record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
    //   while($row_get_language = mysqli_fetch_assoc($get_language)){	
    //     if($row_get_language['id'] !=1){
    //       $lang_col["name_".$row_get_language['iso_code']] = $_POST['name_'.$row_get_language['iso_code']];
    //     }
    //   }    
      
    //   $data = array_merge($dataCol,$lang_col);

			$insert_value =  dbRowInsert("locations",$dataCol);
	
			if(!empty($insert_value )){	
				$msg = "Data Added Successfully";
        alertSuccess( $msg,'?page=manage-locations');
			}else{
				$msg = "Some Error Occourd. Please try again..";
        alertdanger( $msg,'page=add-location');
			}
			//reDirect("?page=manage-locations&msg=".$msg);		
	}
  // End Insert 

// get department by user
$departmentByUsers = get_filter_data_by_user('departments'); 
?>
<section class="content-header">
  <h1> Add Location</h1>
</section>
<section class="content">
  <div class="box box-danger">
    <div class="box-body">
      <form action="" method="post" id="locationForm">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Name</label>
              <input type="text" class="form-control" name="name" id="name" value="<?php echo $row_get_locations_id['name'];?>"/>
            </div>
          </div>
        
           <!-- Status name -->
            <?php
              //only created by and super admin can change status
                if(!empty($_GET['id'])) {
                  if((($_SESSION['user_type']==1 OR $_SESSION['user_type']==2) OR ($row_get_locations_id['cby'] == $_SESSION['user_id'] and $row_get_locations_id['user_type']==$_SESSION['user_type']))){
                    $disabled = "";
                  }else {
                    $disabled = "disabled";
                  }
                }else {
                  $disabled = "";
                }
            ?>
          <div class="col-md-4">
            <div class="form-group">
              <label>Status</label>
              <select class="form-control" name="status" <?=$disabled?>>
                <?php  foreach(status() as $key => $value){ ?>
                  <option <?php if($row_get_locations_id['cstatus']==$key){?> selected="selected"<?php }?>value="<?php echo $key; ?>"><?php echo $value; ?></option>                    
                <?php }?>
              </select>
            </div>
          </div>
            <!-- Department -->        
          <div class="col-md-12 with-border">
              <h4>Department</h4>
              <input type="checkbox" onclick="checked_all(this,'deptCheckbox')" /><strong> Select All</strong><br /> <br />
          </div>
          <div class="col-md-12 with-border" style="padding:0px ;">
            <?php
            if(($row_get_locations_id['admin_ids'])){
							$department_ids = explode('|',$row_get_locations_id['department_id']);
						}else{
							$department_ids = array();
						}
            
            foreach($departmentByUsers as $deptData){ 
              $deptId   = $deptData['id'];
              $deptName = $deptData['name'];
              ?>
              <div class="col-md-4">
                <input type="checkbox" <?=(in_array($deptId,$department_ids) ? 'checked ':' ')?> id="deparment_id_<?php echo $key ?>" value="<?php echo $deptId; ?>" class="deptCheckbox" name="deparments[<?php echo $deptId; ?>]" /> 
                
                <label for="deparment_id_<?php echo $deptId; ?>">
                <?php echo $deptName ?>
                </label>
              </div>
            <?php } ?>
          </div>          
          <!-- assign user start -->        
          <div class="col-md-12 with-border">
              <h4>Assign Admins</h4>
              <input type="checkbox" onclick="checked_all(this,'admin_checkbox')" /><strong> Select All</strong><br /> <br />
            </div>
          <div class="col-md-12 with-border" style="padding:0px ;">
            <?php
            if(($row_get_locations_id['admin_ids'])){
							$admin_saved = explode("|",$row_get_locations_id['admin_ids']);
						}else{
							$admin_saved = array();
						}
            foreach(getAdmin() as $key => $value){ ?>
              <div class="col-md-4">
                <input type="checkbox" <?=(in_array($key,$admin_saved) ? 'checked ':' ')?> id="admin_id_<?php echo $key ?>" value="<?php echo $key; ?>" class="userClass admin_checkbox" name="admin_id[<?php echo $key; ?>]" /> 
                
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
            <input type="checkbox" onclick="checked_all(this,'client_checkbox')" /><strong> Select All</strong><br /> <br />
          </div>
          <div class="col-md-12 with-border" style="padding:0px ;">
            <?php
            if(($row_get_locations_id['client_ids'])){
              $client_saved = explode("|",$row_get_locations_id['client_ids']);
            }else{
              $client_saved = array();
            }
            foreach(getClient() as $key => $value){ ?>
              <div class="col-md-4">
                <input type="checkbox" <?=(in_array($key,$client_saved) ? 'checked ':' ')?>  id="client_id_<?php echo $key ?>"class="userClass client_checkbox"  value="<?php echo $key; ?>" name="client_id[<?php echo $key; ?>]" /> 
                
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
      var validator =  $("#locationForm").validate({
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