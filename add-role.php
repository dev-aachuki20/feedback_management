<?php
if(!empty($_GET['id'])){
	record_set("get_roles_id", "select * from roles where id='".$_GET['id']."'");
	$row_get_roles_id = mysqli_fetch_assoc($get_roles_id);
}
//only created by and super admin can change status
if(!empty($_GET['id'])) {
  if((($_SESSION['user_type']<=2) OR ($row_get_roles_id['cby'] == $_SESSION['user_id'] and $row_get_roles_id['user_type']==$_SESSION['user_type']))){
    $disabled = "";
  }else {
    $disabled = "disabled";
  }
}else {
  $disabled = "";
}

if($_POST['update']){
    $dataCol =  array(
        "name"        => $_POST['name'],
        // "email"       => $_POST['email'],
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
      $updte=	dbRowUpdate("roles", $data, "where id=".$_GET['id']);
      if(!empty($updte)){
        $msg = "Role Updated Successfully";
        alertSuccess( $msg,'?page=manage-role');
      }else{
        $msg = "Role Not Updated Successfully";
        alertdanger($msg,'?page=manage-role&id='.$_GET["id"]);
      }

	}
//End update  

  //Start insert
    if(!empty($_POST['submit'])){
        $dataCol =  array(
            "name"        => $_POST['name'],
            "cstatus"     => $_POST['status'],
            'cip'         => ipAddress(),
            'cby'         => $_SESSION['user_id'],
            'cdate'       => date("Y-m-d H:i:s")
        );
        $insert_value =  dbRowInsert("roles",$dataCol);
        if(!empty($insert_value )){	
            $msg = "Role Added Successfully";
            alertSuccess($msg,'?page=manage-role');
        }else{
            $msg = "Some Error Occourd. Please try again..";
            alertdanger($msg,'?page=add-role');
        }
    }

    // End Insert
?>
<section class="content-header">
  <h1> <?=isset($_GET['id']) ? 'EDIT ROLE':'ADD ROLE'?></h1>
</section>
<section class="content">
  <div class="box box-secondary">
    <div class="box-body">
        <form action="" method="post" id="roleForm" >
            <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                <label>Name *</label>
                <input type="text" class="form-control" name="name" id="name" value="<?php echo $row_get_roles_id['name'];?>" required/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status" <?=$disabled ?>><?php foreach(status() as $key => $value){ ?>
                    <option <?php if($row_get_roles_id['cstatus']==$key){?> selected="selected"<?php }?>value="<?php echo $key; ?>"><?php echo $value; ?></option>                    
                    <?php }?>
                </select>
                </div>
            </div>
                <!-- assign user start --> 
                <?php //include ('./assign_users.php');?>
                <!-- assign user end -->    
            </div>
            <!-- End row -->
            <!-- Start submit button -->
            <div class="text-right">
            <?php if(empty($_GET['id'])){ ?>
                <input type="Submit" class="btn btn-primary" value="Create" name="submit" id="submit"/>
            <?php }else{?>                
                <input type="Submit" class="btn btn-primary" value="Update" name="update"/>
            <?php }?>
                <span class="text-denger"><?php echo $_GET['msg']; ?></span>
            </div>
            <!-- End submit button -->
        </form>
    </div>
  </div>

</section>

<script type="application/javascript">
$(document).ready(function(){
  $(function() {
    var validator =  $("#roleForm").validate({
        rules: {
            name: "required",
        },
        messages: {
            name		: "This Field required",
            name		: "This Field required",
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