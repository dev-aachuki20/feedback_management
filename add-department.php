<?php
if(!empty($_GET['id'])){
	record_set("get_departments_id", "select * from departments where id='".$_GET['id']."'");
	$row_get_departments_id = mysqli_fetch_assoc($get_departments_id);
}

// Start update
$client_id = '';
$admin_id  = '';
	if($_POST){
		$client_id .= implode('|', $_POST['client_id']); 
		$client_id ="|".$client_id."|";

    $admin_id .= implode('|', $_POST['admin_id']); 
		$admin_id ="|".$admin_id."|";
	}

if($_POST['update']){
    $dataCol =  array(
        "name"        => $_POST['name'],
        "email"       => $_POST['email'],
        "cstatus"     => $_POST['status'],
        "client_ids"  => $client_id,
        "admin_ids"   => $admin_id
    );
    // $lang_col=array();
    // record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
    // while($row_get_language = mysqli_fetch_assoc($get_language)){	
    //   if($row_get_language['id'] !=1){
    //       $lang_col["name_".$row_get_language['iso_code']] = $_POST['name_'.$row_get_language['iso_code']];
    //   }
    // }

    //$data = array_merge($dataCol,$lang_col);

    $updte=	dbRowUpdate("departments", $dataCol, "where id=".$_GET['id']);
    if(!empty($updte)){
      $msg = "Department Updated Successfully";
      alertSuccess( $msg,'?page=manage-department');
    }else{
      $msg = "Department Not Updated Successfully";
      alertdanger($msg,'?page=manage-department&id='.$_GET["id"]);
    }
    //reDirect("?page=manage-department&id=".$_GET["id"]."&msg=".$msg);			
	}
//End update  

  //Start insert
	if(!empty($_POST['submit'])){
			$dataCol =  array(
        "name"        => $_POST['name'],
        "email"       => $_POST['email'],
        "cstatus"     => $_POST['status'],
        "client_ids"  => $client_id,
        "admin_ids"   => $admin_id,
        "user_type"   => $_SESSION["user_type"],
        'cip'         => ipAddress(),
        'cby'         => $_SESSION['user_id'],
        'cdate'       => date("Y-m-d H:i:s")
      );

      // $lang_col=array();
      // record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
      // while($row_get_language = mysqli_fetch_assoc($get_language)){	
      //   if($row_get_language['id'] !=1){
      //       $lang_col["name_".$row_get_language['iso_code']] = $_POST['name_'.$row_get_language['iso_code']];
      //   }
      // }

     // $data = array_merge($dataCol,$lang_col);
					
			$insert_value =  dbRowInsert("departments",$dataCol);
	
			if(!empty($insert_value )){	
				$msg = "Department Added Successfully";
        alertSuccess($msg,'?page=manage-department');
			}else{
				$msg = "Some Error Occourd. Please try again..";
        alertdanger($msg,'?page=add-department');
			}
			//reDirect("?page=manage-department&msg=".$msg);		
		}
    // End Insert
?>
<section class="content-header">
  <h1> <?=isset($_GET['id']) ? 'View Department':'Add Department'?></h1>
</section>
<section class="content">
  <div class="box box-danger">
    <div class="box-body">
      <form action="" method="post" id="departmentForm" >
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Name</label>
              <input type="text" class="form-control" name="name" id="name" value="<?php echo $row_get_departments_id['name'];?>" required/>
            </div>
          </div>
          <!-- Start name according to language -->
          <?php
            record_set("get_language", "select * from languages");				
            while($row_get_language = mysqli_fetch_assoc($get_language)){
              if($row_get_language['iso_code'] != 'en'){ ?>
          <!-- <div class="col-md-4">
            <div class="form-group">
              <label>Name - <?=$row_get_language['name']?></label>
              <input type="text" class="form-control" name="name_<?=$row_get_language['iso_code']?>" id="name_<?=$row_get_language['iso_code']?>" value="<?php echo $row_get_departments_id['name_'.$row_get_language['iso_code']];?>"/>
            </div>
          </div> -->
          <?php }
            }
          ?>
          <!-- End name according to language -->
          <div class="col-md-4">
            <div class="form-group">
              <label>Email</label>
              <input type="text" class="form-control" name="email" id="email" value="<?php echo $row_get_departments_id['email'];?>"/>
            </div>
          </div>
              <?php
              //only created by and super admin can change status
                if(!empty($_GET['id'])) {
                  if((($_SESSION['user_type']==1) OR ($row_get_departments_id['cby'] == $_SESSION['user_id'] and $row_get_departments_id['user_type']==$_SESSION['user_type']))){
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
              <select class="form-control" name="status" <?=$disabled ?>><?php foreach(status() as $key => $value){ ?>
                  <option <?php if($row_get_departments_id['cstatus']==$key){?> selected="selected"<?php }?>value="<?php echo $key; ?>"><?php echo $value; ?></option>                    
                <?php }?>
              </select>
            </div>
          </div>
            <!-- assign user start --> 
            <div class="col-md-12 with-border">
              <h4>Assign Admins</h4>
            </div>
            <div class="col-md-12" style="padding: 0px;">
              <?php
              if(($row_get_departments_id['admin_ids'])){
                $admin_saved = explode("|",$row_get_departments_id['admin_ids']);
              }else{
                $admin_saved = array();
              }
              foreach(getAdmin() as $key => $value){ ?>
                <div class="col-md-4">
                  <input type="checkbox" <?=(in_array($key,$admin_saved) ? 'checked ':' ')?> id="admin_id_<?php echo $key ?>" class="userClass" value="<?php echo $key; ?>" name="admin_id[<?php echo $key; ?>]" /> 
                  
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
            </div>
            <div class="col-md-12" style="padding: 0px;">
              <?php
              if(($row_get_departments_id['client_ids'])){
                $client_saved = explode("|",$row_get_departments_id['client_ids']);
              }else{
                $client_saved = array();
              }
              foreach(getClient() as $key => $value){ ?>
                <div class="col-md-4">
                  <input type="checkbox" <?=(in_array($key,$client_saved) ? 'checked ':' ')?>  id="client_id_<?php echo $key ?>" class="userClass" value="<?php echo $key; ?>" name="client_id[<?php echo $key; ?>]" /> 
                  
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
    var validator =  $("#departmentForm").validate({
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