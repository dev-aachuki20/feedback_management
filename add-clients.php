<?php
if(!empty($_GET['id'])){
	record_set("get_clients_id", "select * from clients where id='".$_GET['id']."'");			
	$get_clients_id = mysqli_fetch_assoc($get_clients_id);
}

/**
 * Logic for all locations
 */
if(isset($_POST) && isset($_POST['locationid'])){
	if(($key = array_search('4', $_POST['locationid'])) !== false) {
		$_POST['//'] = array();
	    $_POST['locationid'][] = 4;
	}
}

if($_POST['update']){
	$file_name=$_FILES['photo']['name'];
	$file_tempname = $_FILES['photo']['tmp_name'];
	$folder="upload_image/";
	$image_id=rand(1000,100000)."-";
	$result=upload_image1($folder,$file_name, $file_tempname,$image_id);
	if(empty($_POST['password'])){
		$password = $get_clients_id['password'];
	}else{
		$password = md5($_POST['password']);
	}
	if(!empty($result)){
		unlink("upload_image/".$get_clients_id['photo']);
		$data = array(
				"name" => $_POST['name'],
				"email" => $_POST['email'],										
				"phone"=> $_POST['phone'],
				"password" => $password,
				"photo" => $result,
				//"address" => $_POST['address'],
				//"contact_person" => $_POST['contact_person'],
				//"contact_email" => $_POST['contact_email'],
				//"contact_phone" => $_POST['contact_phone'],
				"cstatus" => $_POST['status'],
				"locationid" => implode(",",$_POST['locationid'])
			);
	}else{			
			$data = array(
					"name" => $_POST['name'],
					"email" => $_POST['email'],										
					"phone"=> $_POST['phone'],		
					"password" => $password,		
					//"address" => $_POST['address'],
					//"contact_person" => $_POST['contact_person'],
					//"contact_email" => $_POST['contact_email'],
					//"contact_phone" => $_POST['contact_phone'],
					"cstatus" => $_POST['status'],
					"locationid" => implode(",",$_POST['locationid'])
				);
			
		}
			$updte=	dbRowUpdate("clients", $data, "where id=".$_GET['id']);
			if(!empty($updte)){
				$msg = "User Updated Successfully";
			}else{
				$msg = "User Not Updated Successfully";
			}
			reDirect("?page=add-clients&id=".$_GET["id"]."&msg=".$msg);

	}


if(!empty($_POST['submit'])){
		
			$file_name=$_FILES['photo']['name'];
			$file_tempname = $_FILES['photo']['tmp_name'];
			$folder="upload_image/";
			$image_id=rand(1000,100000)."-";
			$result=upload_image1($folder,$file_name, $file_tempname,$image_id);
			
			record_set("checkEmail", "select * from clients where email='".$_POST['email']."'");
			
			if($totalRows_checkEmail>0){
					alert("Email already exits");
					reDirect("?page=add-clients&msg=".$mess);
				}else{
					
			$data = array(
					"name" => $_POST['name'],
					"email" => $_POST['email'],
					"password" => md5($_POST['password']),		
					"phone"=> $_POST['phone'],
					"photo" => $result,
					//"address" => $_POST['address'],
					//"contact_person" => $_POST['contact_person'],
					//"contact_email" => $_POST['contact_email'],
					//"contact_phone" => $_POST['contact_phone'],
					"cstatus" => $_POST['status'],
					"locationid" => implode(",",$_POST['locationid']),
					'cip'=>ipAddress(),
					'cby'=>$_SESSION['user_id'],
					'cdate'=>date("Y-m-d H:i:s")
					
				);
				$insert_value =  dbRowInsert("clients",$data);
	
					if(!empty($insert_value )){	
						$msg = "User Added Successfully";
					}else{
						$msg = "User Not Added Successfully";
					}
					reDirect("?page=add-clients&msg=".$msg);
				}
	}
?>
<section class="content-header">
  <h1> Add Manager</h1>
  <a href="?page=view-clients" class="btn btn-primary pull-right" style="margin-top:-25px">View Clients</a> </section>
<section class="content">
  <div class="box box-danger">
    <div class="row">
      <div class="col-md-12">
        <div class="box-header"><i class="fa fa-edit"></i>Input</div>
        <div class="box-body">
          <form action="" method="post" enctype="multipart/form-data">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Managers Name</label>
                  <input type="text" class="form-control" name="name" id="name" value="<?php echo $get_clients_id['name'];?>"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Phone</label>
                  <input type="text" class="form-control" name="phone" id="phone" value="<?php echo $get_clients_id['phone'];?>"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Email</label>
                  <input type="text" class="form-control" name="email" id="email" value="<?php echo $get_clients_id['email'];?>"/>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label>Password123</label>
                  <input type="password" class="form-control" name="password" value=""/>
                </div>
              </div>
              
             
              <!-- <div class="col-md-6">
                <div class="form-group">
                  <label>Address</label>
                  <input type="text" class="form-control" name="address" value="<?php echo $get_clients_id['address'];?>"/>
                </div>
              </div> -->
              <div class="col-md-6">
                <div class="row">
                <div class="col-md-10">
                <div class="form-group">
                  <label>Image</label>
                  <input type="file" class="form-control" name="photo"/>
                
                </div></div>
                 <div class="col-md-2" style="padding-left:0px; margin-top:9px;">	
                   <?php 
				  	if(!empty($_GET['id'])){
				  ?>
                  <img src="upload_image/<?php echo $get_clients_id['photo'];?>" height="50px" width="50px" />
                  <?php }?>
                  </div>
                </div>
              </div>
              <!-- <div class="col-md-6">
                <div class="form-group">
                  <label>Contact Person</label>
                  <input type="text" class="form-control" name="contact_person" id="contact_person" value="<?php echo $get_clients_id['contact_person'];?>"/>
                </div>
              </div> -->
<!--                 <div class="col-md-6">
                <div class="form-group">
                  <label>Contact Email</label>
                  <input type="text" class="form-control" name="contact_email" value="<?php echo $get_clients_id['contact_email'];?>"/>
                </div>
              </div> -->
               <!-- <div class="col-md-6">
                <div class="form-group">
                  <label>Contact Phone</label>
                  <input type="text" class="form-control" name="contact_phone" id="contact_phone" value="<?php echo $get_clients_id['contact_phone'];?>"/>
                </div>
              </div> -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status</label>
                  <select class="form-control" name="status">
                  	<?php 
						foreach(status() as $key => $value){
					?>
                    <option <?php if($get_clients_id['name']==$key){?> selected="selected"<?php }?> value="<?php echo $key; ?>"><?php echo $value;?></option>
                    <?php }?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
	              <label>Location</label>
	              <select name="locationid[]" id="locationid" class="form-control form-control-lg" required multiple="multiple">
	              <option value="">Select</option>
	              <?php
	              record_set("get_location", "select * from locations where cby='".$_SESSION['user_id']."' AND cstatus=1 order by name asc");        
	              while($row_get_location = mysqli_fetch_assoc($get_location)){ 
	              ?>
	                <option value="<?php echo $row_get_location['id'];?>" <?php echo (in_array($row_get_location['id'], explode(",", $get_clients_id['locationid']))) ? "selected" : "" ?>><?php echo $row_get_location['name'];?></option>
	              <?php }?>
	              </select>	
              </div>
              <div class="col-md-6">
                <div class="form-group text-right">
                <?php 
					if(empty($_GET['id'])){
				?>	
			 	<input type="Submit" class="btn btn-primary" value="Create" name="submit" id="submit" style="margin-top:24px"/>
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
<script type="text/javascript">
$(document).ready(function(){
		
		$('.form-control').click(function(){
			
				$(this).css('border-color',"#ccc");
			});
			
		$('#submit').click(function(){
			
				var name = $("#name").val();
				if(name ==''){
						document.getElementById('name').style.borderColor = "#ff0000";
						alert("Name is Required");
						document.getElementById('name').focus();
						return false;
					
					}
					
				var phone = $("#phone").val();
				if(phone==""){
						document.getElementById("phone").style.borderColor = "#ff0000";
						alert('Phone is Required');
						document.getElementById('phone').focus();
						return false;
					}
					
				var email = $("#email").val();
				if(email==""){
						document.getElementById("email").style.borderColor = "#ff0000";
						alert('email is Required');
						document.getElementById('email').focus();;
						return false;
					}	
					
				var contact_person = $("#contact_person").val();
				if(contact_person==""){
						document.getElementById("contact_person").style.borderColor = "#ff0000";
						alert('Add contact person');
						document.getElementById('contact_person').focus();
						return false;
					}
					
				var contact_phone = $("#contact_phone").val();		
				if(contact_phone==''){
					document.getElementById('contact_phone').style.borderColor = "#ff0000";
					alert("Add Phone Number");
					document.getElementById('contact_phone').focus()
					return false;
					}
							
			});	
	});
</script>