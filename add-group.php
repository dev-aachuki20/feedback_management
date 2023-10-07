<?php
if(!empty($_GET['id'])){
    record_set("get_groups_id", "select * from `groups` where id='".$_GET['id']."'");
    $row_get_groups_id = mysqli_fetch_assoc($get_groups_id);
}
//only created by and super admin can change status
if(!empty($_GET['id'])) {
  if((($_SESSION['user_type']<=2) OR ($row_get_departments_id['cby'] == $_SESSION['user_id'] and $row_get_departments_id['user_type']==$_SESSION['user_type']))){
    $disabled = "";
  }else {
    $disabled = "disabled";
  }
}else {
  $disabled = "";
}
// Start Update
if($_POST['update']){
  $dataCol =  array(
      "name"          => $_POST['name'],
      "location_id"   => implode(',',$_POST['locations']),
      // "client_ids"    => $client_id,
      // "admin_ids"     => $admin_id 
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
	$updte=	dbRowUpdate("`groups`", $data, "where id=".$_GET['id']);
    if(!empty($updte)){
      $msg = "Group Updated Successfully";
      alertSuccess($msg,'?page=manage-groups');
    }else{
      $msg = "User Not Updated Successfully";
      alertdanger($msg,'?page=manage-groups&id='.$_GET["id"]);
    }
   // reDirect("?page=manage-groups&id=".$_GET["id"]."&msg=".$msg);			
}
    // End Update	

    // Start insert
	if(!empty($_POST['submit'])){
        $data =  array(
            "name"          => $_POST['name'],
            "cstatus"       => $_POST['status'],
            "location_id"   => implode(',',$_POST['locations']),
            'cip'           => ipAddress(),
            //"user_type"     => $_SESSION["user_type"],
            // "client_ids"    => $client_id,
            // "admin_ids"     => $admin_id,
            'cby'           => $_SESSION['user_id'],
            'cdate'         => date("Y-m-d H:i:s")
        );

        $insert_value =  dbRowInsert("`groups`",$data);
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

  // get group by user
  $locationByUsers = get_filter_data_by_user('locations');
?>
<section class="content-header">
  <h1> <?=isset($_GET['id']) ? 'EDIT GROUP':'ADD GROUP'?></h1>
</section>
<section class="content">
  <div class="box box-secondary">
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
              <select class="form-control" name="status" <?=$disabled?>>
                <?php  
             
                foreach(status() as $key => $value){ ?>
                  <option <?php if($row_get_groups_id['cstatus']==$key){?> selected="selected"<?php }?>value="<?php echo $key; ?>"><?php echo $value; ?></option>                    
                <?php }?>
              </select>
            </div>
          </div>
          <!-- add location -->
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