<?php
if(!empty($_GET['id'])){
	record_set("get_locations_id", "select * from locations where id='".$_GET['id']."'");
	$row_get_locations_id = mysqli_fetch_assoc($get_locations_id);
	}
  //only created by and super admin can change status
  if(!empty($_GET['id'])) {
  if((($_SESSION['user_type']<=2) OR ($row_get_locations_id['cby'] == $_SESSION['user_id'] and $row_get_locations_id['user_type']==$_SESSION['user_type']))){
    $disabled = "";
  }else {
    $disabled = "disabled";
  }
}else {
  $disabled = "";
}

// Start Update
if($_POST['update']){
    $deparment_id = implode(",",$_POST['deparments']);
    $dataCol =  array(
        "name"          => $_POST['name'],
        "department_id" => $deparment_id,
    );
    
    //if status is readonly than it will not update
    $dataStatus = array(
      "cstatus"=> $_POST['status']
    );
    if($disabled === 'disabled'){
      $data = $dataCol;
    }else {
      $data = array_merge($dataCol,$dataStatus);
    }

		$updte=	dbRowUpdate("locations", $data, "where id=".$_GET['id']);
    
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
    $deparment_id = implode(",",$_POST['deparments']);
    $dataCol =  array(
        "name"          => $_POST['name'],
        "cstatus"       => $_POST['status'],
        'cip'           => ipAddress(),
        "department_id" => $deparment_id,
        // "client_ids"  => $client_id,
        // "user_type"   => $_SESSION["user_type"],
        // "admin_ids"   => $admin_id,
        'cby'            => $_SESSION['user_id'],
        'cdate'          => date("Y-m-d H:i:s")
    );
			$insert_value =  dbRowInsert("locations",$dataCol);
	
			if(!empty($insert_value )){	
				$msg = "Data Added Successfully";
        alertSuccess( $msg,'?page=manage-locations');
			}else{
				$msg = "Some Error Occourd. Please try again..";
        alertdanger( $msg,'page=add-location');
			}
	}
  // End Insert 

// get department by user
$departmentByUsers = get_filter_data_by_user('departments'); 
?>
<section class="content-header">
  <h1><?=isset($_GET['id']) ? 'EDIT LOCATION':'ADD LOCATION'?></h1>
</section>
<section class="content">
  <div class="box box-secondary">
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
          <!-- remove dependency-->  
          <!-- assign user start --> 
          <?php include ('./assign_users.php');?>
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