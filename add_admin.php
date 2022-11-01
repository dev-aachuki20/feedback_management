<?php
	if(!empty($_GET['id'])){
			record_set("get_admin_id", "select * from admin where id='".$_GET['id']."'");
			
			$row_get_admin_id = mysqli_fetch_assoc($get_admin_id);
				
		}
	
	if(!empty($_POST['update'])){
		$file_name=$_FILES['photo']['name'];
		$file_tempname = $_FILES['photo']['tmp_name'];
		$folder="upload_image/";
		$image_id=rand(1000,100000)."-";
		$result=upload_image1($folder,$file_name, $file_tempname,$image_id);
		
		if(!empty($result)){
			//
		unlink("upload_image/".$row_get_admin_id['photo']);	
		$data = array(
			"name" => $_POST['name'],
			"email" => $_POST['email'],
			"phone"=> $_POST['phone'],
			"photo" => $result,
			"status" => $_POST['status']
										
		);
		}else{			
			$data = array(
				"name" => $_POST['name'],
				"email" => $_POST['email'],		
				"phone"=> $_POST['phone'],				
				"cstatus" => $_POST['status'],
			);
		}
		if(!empty($_POST['password'])){
			$data['password']= md5($_POST['password']);
		}
		
		$updte=	dbRowUpdate("admin", $data, "where id=".$_GET['id']);
		if(!empty($updte)){
			$msg = "User Updated Successfully";
		}else{
			$msg = "User Not Updated Successfully";
		}
		reDirect("?page=add_admin&id=".$_GET["id"]."&msg=".$msg);
	}	
		

?>

<?php
if(!empty($_POST['submit'])){
	
			$file_name=$_FILES['photo']['name'];
			$file_tempname = $_FILES['photo']['tmp_name'];
			$folder="upload_image/";
			$image_id=rand(1000,100000)."-";
			$result=upload_image1($folder,$file_name, $file_tempname,$image_id);
			
			record_set("checkEmail", "select * from admin where email='".$_POST['email']."'");
			
			if($totalRows_checkEmail>0){
					alert("Email already exits");
					reDirect("?page=add_admin&msg=".$mess);		
				
				}else{
			
				$data = array(
					"name" => $_POST['name'],
					"email" => $_POST['email'],
					"password" => md5($_POST['password']),					
					"phone"=> $_POST['phone'],
					"photo" => $result,
					"cstatus" => $_POST['status'],
					'cip'=>ipAddress(),
					'cby'=>$_SESSION['user_id'],
					'cdate'=>date("Y-m-d H:i:s")
				);
				
				$insert_value =  dbRowInsert("admin",$data);
			
					if(!empty($insert_value )){	
						$msg = "User Added Successfully";
					}else{
						$msg = "User Not Added Successfully";
					}
					reDirect("?page=add_admin&msg=".$msg);
		}
	}
?>

<??>
<section class="content-header">
  <h1> Add Admin</h1>
  <a href="?page=view-admin" class="btn btn-primary pull-right" style="margin-top:-25px">View Admin</a> </section>
<section class="content">
  <div class="box box-secondary">
    <div class="row">
      <div class="col-md-12">
        <div class="box-header"><i class="fa fa-edit"></i>Input</div>
        <div class="box-body">
          <form action="" method="post" enctype="multipart/form-data">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Name</label>
                  <input type="text" class="form-control" name="name"  id="name" value="<?php echo $row_get_admin_id['name']?>"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Email</label>
                  <input type="text" class="form-control" id="email" name="email"value="<?php echo $row_get_admin_id['email']?>"/>
                </div>
              </div>
         
              <div class="col-md-6">
                <div class="form-group">
                  <label>Password</label>
                  <input type="password" class="form-control" name="password" id="password" value=""/>
                </div>
              </div>
            
              <div class="col-md-6">
                <div class="form-group">
                  <label>Phone</label>
                  <input type="text" class="form-control" name="phone" id="phone" value="<?php echo $row_get_admin_id['phone']?>"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Photo</label>
                  <input type="file" class="form-control" name="photo"/>
                  <?php
				  	if(!empty($_GET['id'])){						
                  ?>
                  <img src="upload_image/<?php echo $row_get_admin_id['photo']?>" height="50" width="50" />
                  <?php }?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status</label>
                  <select class="form-control" name="status">
                  <?php 
				  	foreach(status() as $key=> $value){
				  ?>
                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                   <?php }?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                <?php 
					if(empty($_GET['id'])){
				?>
 				<input type="Submit" class="btn btn-primary" id="submit" value="Create" name="submit" style="margin-top:24px"/>
                <?php }else{?>
                <input type="Submit" class="btn btn-primary" value="Update" name="update" style="margin-top:24px"/>
                <?php }?>
                <span class="text-denger"><?php echo $_GET['msg']; ?></span>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<br />
<script type="text/javascript">
jQuery(document).ready(function() {
jQuery('.form-control').click(function(){
$(this).css("border-color", "#ccc");
});


jQuery('#submit').click(function(){
jQuery(".error").hide();
var haserror=false;

var name = jQuery("#name").val();
if(name == ''){
document.getElementById("name").style.borderColor = "#ff0000";	
alert("Name field is required");
document.getElementById("name").focus();
return false;
}

var email = jQuery("#email").val();
if(email == ''){
	document.getElementById("email").style.borderColor = "#ff0000";
	alert("Email is required");
	document.getElementById("email").focus();
	return false;
	
	}

var password = jQuery("#password").val();
if(password == ''){	
	document.getElementById("password").style.borderColor = "#ff0000";
	alert("Password is required");
	document.getElementById("password").focus();
	return false;
}

var password = jQuery("#password").val();
if(password == ''){	
	document.getElementById("password").style.borderColor = "#ff0000";
	alert("Password is required");
	document.getElementById("password").focus();
	return false;
}

var phone = jQuery("#phone").val();
if(phone == ''){	
	document.getElementById("phone").style.borderColor = "#ff0000";
	alert("phone is required");
	document.getElementById("phone").focus();
	return false;
}

});
});
</script>
