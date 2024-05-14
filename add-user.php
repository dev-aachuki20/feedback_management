<style>
  .select2{
    width: 100% !important;
  }
</style>
<?php
  $coloumn_name = '';
	if(!empty($_GET['id'])){
    record_set("get_user_id", "select * from manage_users where id='".$_GET['id']."'");
    $row_get_user_id = mysqli_fetch_assoc($get_user_id);
    if($totalRows_get_user_id == 0){
      echo 'Invalid User'; die();
    }
  }

	if(!empty($_POST['update'])){
      $file_name=$_FILES['photo']['name'];
      $file_tempname = $_FILES['photo']['tmp_name'];
      $folder="upload_image/";
      $image_id=rand(1000,100000)."-";
      $result=upload_image1($folder,$file_name, $file_tempname,$image_id);
      if(!empty($result)){
          unlink("upload_image/".$row_get_user_id['photo']);	
          $data = array(
              "name"      => $_POST['name'],
              //"email"     => $_POST['email'],
              "phone"     => $_POST['phone'],
              "photo"     => $result,
              "cstatus"   => $_POST['status']
          );
      }else{			
          $data = array(
              "name"      => $_POST['name'],
              //"email"     => $_POST['email'],		
              "phone"     => $_POST['phone'],
              "cstatus"   => $_POST['status'],
          );
      }
      if(!empty($_POST['password'])){
          $data['password']= md5($_POST['password']);
      }
      if($_SESSION['user_type'] <3){
        $data['user_type']= $_POST['user_type'];
      }
      $updte=	dbRowUpdate("manage_users", $data, "where id=".$_GET['id']);
      if(!empty($updte)){
          //assign user location,group,department
          $groupids =$locationids= $departmentids= $survey_type='';
          $uid = $_GET['id'];
          $filter = "user_id =  $uid";
          dbRowDelete('relation_table', $filter);

          // assign group
          if(isset($_POST['groupids'])){
            foreach($_POST['groupids'] as $groupId){
              $data = array(
                  "table_id"    => $groupId,
                  "user_id"    => $uid,
                  "table_name" => 'group'
              );
              $insert =  dbRowInsert("relation_table",$data);
            }
          }
          // assign survey 
          if(isset($_POST['surveyids'])){
            foreach($_POST['surveyids'] as $surveyId){
              //get survey and check survey type
              record_set("get_user_id", "select * from surveys where id='".$surveyId."'");
              $row_get_user_id = mysqli_fetch_assoc($get_user_id);
              $surveyType = $row_get_user_id['survey_type'];

              $type ='';
              if($surveyType == 1){
                $type = 'survey';
              }else if($surveyType == 2){
                $type = 'pulse';
              }else if($surveyType == 3){
                $type = 'engagement';
              }
              $data_sur = array(
                  "table_id"    => $surveyId,
                  "user_id"    =>  $uid,
                  "table_name" => $type
              );
              $insert =  dbRowInsert("relation_table",$data_sur);
            }
          }
          // assign location 
          if(isset($_POST['locationids'])){
            foreach($_POST['locationids'] as $locationId){
              $data_loc = array(
                  "table_id"   => $locationId,
                  "user_id"    =>  $uid,
                  "table_name" => 'location'
              );
              $insert =  dbRowInsert("relation_table",$data_loc);
            }
          }
           // assign department
           if(isset($_POST['departmentids'])){
            foreach($_POST['departmentids'] as $departmentId){
              $data_dept = array(
                  "table_id"   => $departmentId,
                  "user_id"    => $uid,
                  "table_name" => 'department'
              );
              $insert =  dbRowInsert("relation_table",$data_dept);
            }
          }
      
           // assign role
           if(isset($_POST['roleids'])){
            foreach($_POST['roleids'] as $roleId){
              $data_role = array(
                  "table_id"   => $roleId,
                  "user_id"    => $uid,
                  "table_name" => 'role'
              );
              $insert =  dbRowInsert("relation_table",$data_role);
            }
          }
          $msg = "User Updated Successfully";
          if(isset($_GET['user'])){
            alertSuccess($msg,'');
          }else {
            alertSuccess( $msg,'?page=view-user');
          }
      }else{
          $msg = "User Not Updated Successfully";
          if(isset($_GET['user'])){
            alertdanger( $msg,'');
          }else {
            alertdanger( $msg,'?page=add-user');
          }
      }
      //reDirect("?page=view-user&msg=".$msg);
	}	
// get data by user
$surveyByUsers     = get_survey_data_by_user('survey');
$pulseByUsers      = get_survey_data_by_user('pulse');
$engagementByUsers = get_survey_data_by_user('engagement');
$departmentByUsers = get_filter_data_by_user('departments');
$groupByUsers      = get_filter_data_by_user('groups');

?>

<?php
if(!empty($_POST['submit'])){
    $user_type = $_POST['user_type'];
    $file_name=$_FILES['photo']['name'];
    $file_tempname = $_FILES['photo']['tmp_name'];
    $folder="upload_image/";
    $image_id=rand(1000,100000)."-";
    $result=upload_image1($folder,$file_name, $file_tempname,$image_id);
    
    record_set("checkEmail", "select * from manage_users where email='".$_POST['email']."'");
			
    if($totalRows_checkEmail>0){
       // alert("Email already exits");
        $mess = 'Email already exits';
        alertdanger($mess,'');
        //reDirect("?page=add-user&msg=".$mess);		

    }else{
        $rnd = rand(1000, 99999);
        $data = array(
          "name" => $_POST['name'],
          "email" => $_POST['email'],
          "password" => md5($_POST['password']),					
          "phone"=> $_POST['phone'],
          "user_type" => $_POST['user_type'],
          "photo" => $result,
          "activation_key" => $rnd,
          "cstatus" => $_POST['status'],
          'cip'=>ipAddress(),
          'cby'=>$_SESSION['user_id'],
          'cdate'=>date("Y-m-d H:i:s")
        );
      $insert_value =  dbRowInsert("manage_users",$data);
    
      if(!empty($insert_value )){	
          //assign user location,group,department
          $groupids =$locationids= $departmentids= $survey_type='';

          // assign group 
          if(isset($_POST['groupids'])){
            $filter = "table_name = 'group' and user_id = $insert_value";
            dbRowDelete('relation_table', $filter);
            foreach($_POST['groupids'] as $groupId){
              $data = array(
                  "table_id"    => $groupId,
                  "user_id"    => $insert_value,
                  "table_name" => 'group'
              );
              $insert =  dbRowInsert("relation_table",$data);
            }
          }

          // assign survey 
          if(isset($_POST['surveyids'])){
            $filter = "table_name = 'survey' and user_id = $insert_value";
            dbRowDelete('relation_table', $filter);
            
            foreach($_POST['surveyids'] as $surveyId){
              //get survey and check survey type
              record_set("get_user_id", "select * from surveys where id='".$surveyId."'");
              $row_get_user_id = mysqli_fetch_assoc($get_user_id);
              $surveyType = $row_get_user_id['survey_type'];
              $type ='';
              if($surveyType == 1){
                $type = 'survey';
              }else if($surveyType == 2){
                $type = 'pulse';
              }else if($surveyType == 3){
                $type = 'engagement';
              }
              $data = array(
                  "table_id"    => $surveyId,
                  "user_id"     => $insert_value,
                  "table_name"  => $type
              );
              $insert =  dbRowInsert("relation_table",$data);
            }
          }

          // assign location 
          if(isset($_POST['locationids'])){
            $filter = "table_name = 'location' and user_id = $insert_value";
            dbRowDelete('relation_table', $filter);
            foreach($_POST['locationids'] as $locationId){
              $data = array(
                  "table_id"   => $locationId,
                  "user_id"    => $insert_value,
                  "table_name" => 'location'
              );
              $insert =  dbRowInsert("relation_table",$data);
            }
          }
           // assign department
          if(isset($_POST['departmentids'])){
            $filter = "table_name = 'department' and user_id = $insert_value";
            dbRowDelete('relation_table', $filter);
            foreach($_POST['departmentids'] as $departmentId){
              $data = array(
                  "table_id"   => $departmentId,
                  "user_id"    => $insert_value,
                  "table_name" => 'department'
              );
              $insert =  dbRowInsert("relation_table",$data);
            }
          }

           // assign role
           if(isset($_POST['roleids'])){
            $filter = "table_name = 'role' and user_id = $uid";
            dbRowDelete('relation_table', $filter);
            foreach($_POST['roleids'] as $roleId){
              $data_role = array(
                  "table_id"   => $roleId,
                  "user_id"    => $insert_value,
                  "table_name" => 'role'
              );
              $insert =  dbRowInsert("relation_table",$data_role);
            }
          }
          

	      send_welcome_email($_POST['email'], $_POST['name'], $rnd);

          $msg = "User Added Successfully";
          alertSuccess( $msg,'?page=view-user');
      }else{
          $msg = "User Not Added Successfully";
          alertdanger( $msg,'?page=add-user');
      }
    // reDirect("?page=view-user&msg=".$msg);
    
    }
}
?>
<style>
  /* .col-md-12.survey-assign {
    display: none;
} */
</style>
<section class="content-header">
  <h1 class="title"><?=isset($_GET['id'])?'EDIT':'ADD'?> USER</h1>
  <?php if(!isset($_GET['user'])) { ?>
  <a href="?page=view-user" class="btn btn-primary pull-right" style="margin-top:-25px">View Users</a> 
  <?php } ?>
</section>
<section class="content">
  <div class="box box-secondary">
    <div class="row">
      <div class="col-md-12">
        <!-- <div class="box-header"><i class="fa fa-edit"></i>Input</div> -->
        <div class="box-body">
            <?php if(isset($_GET['msg']) && !empty($_GET['msg'])){ ?>
             <div class="alert alert-danger text-denger" role="alert"><?=$_GET['msg']?></div>
            <?php  } ?>
            
          <form action="" method="post" name="myForm" enctype="multipart/form-data">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Name *</label>
                  <input type="text" class="form-control" name="name"  id="name" value="<?php echo $row_get_user_id['name']?>"/>
                </div>
              </div>
              <div class="col-md-6">
                <?php $user_types_array=user_type();?>
                <div class="form-group">
                  <input type="hidden" id="hidden_user_type" name="user_type" value="<?=$row_get_user_id['user_type']?>">
                  <label>User Type *</label>
                    <select class="form-control" tabindex=7 id="user_type" <?=($_GET['id'] && $_SESSION['user_type'] > 2)?'disabled':'required'?>>
                        <option value="">Select User Type</option>
                      <?php   
                        foreach($user_types_array as $key => $value){
                          // if($_SESSION['user_type']==2){
                          //   $allowed_key=2;
                          // }
                          if($key==1 and !isset($_GET['user'])){
                            continue;
                          }
                          if($key>=$_SESSION['user_type']){ ?>
                          <option <?php if($row_get_user_id['user_type']==$key){?> selected="selected"<?php  }?> value="<?php echo $key; ?>"> <?php echo $value; ?>
                          </option>
                        <?php }
                        }
                    
                     ?>
					          </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Email *</label>
                  <input type="text" class="form-control" autocomplete="no-email" id="email" name="email" value="<?php echo $row_get_user_id['email']?>" <?=($_GET['id'])?'disabled':''?>/>
                </div>
              </div>
         
              <div class="col-md-6">
                <div class="form-group">
                  <label>Password</label>
                  <input type="password" class="form-control" autocomplete="no-password" name="password" id="password" value=""/>
                </div>
              </div>
              <!-- <div class="col-md-6 location_field" style="display:<?=($_SESSION['user_type']==3 OR $_GET['t']=='c')?'block':'none'?>">
	              <label style="width:100%;">Location</label>
	              <select name="locationid[]" id="locationid" class="form-control form-control-lg multiple-select" required multiple="multiple" name="locationid">
	              <?php
                $filterQuery = '';
                if($_SESSION['user_type']==3){
                  if($_SESSION['user_locationid']){
                    $filterQuery = " and id IN (".$_SESSION['user_locationid'].")";
                  }else{
                    $filterQuery = '';
                  }
                 
                }
	              // record_set("get_location", "select * from locations where id='".$_SESSION['user_id']."' AND cstatus=1 order by name asc");
                record_set("get_location", "select * from locations where cstatus=1 $filterQuery order by name asc");        
	              while($row_get_location = mysqli_fetch_assoc($get_location)){  
                  $locations = explode(',',$row_get_user_id['locationid']); ?>
                  <option value="<?php echo $row_get_location['id'];?>" 
                  <?=(in_array($row_get_location['id'],$locations)) ? 'selected':''?>>
                    <?php echo $row_get_location['name'];?>
                  </option>
	              <?php }?>
	              </select>	
              </div> -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Phone</label>
                  <input type="text" class="form-control" name="phone" id="phone" value="<?php echo $row_get_user_id['phone']?>"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Photo</label>
                  <input type="file" class="form-control" name="photo"/>
                  <?php if(!empty($_GET['id']) and !empty($row_get_user_id['photo'])){ ?>
                  <img src="upload_image/<?php echo $row_get_user_id['photo']?>" height="50" width="50" />
                  <?php }?>
                </div>
              </div>
            <?php if(empty($_GET['user']) || $_SESSION['user_type'] >1 ) { ?>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status</label>
                  <select class="form-control" name="status">
                    <?php foreach(status() as $key=> $value){ ?>
                      <option value="<?php echo $key; ?>" <?=($row_get_user_id['cstatus']==$key)?'selected':''?>><?php echo $value; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <?php } ?>
            <?php if(!isset($_GET['user'])) {?>
            <div class="col-md-12 survey-assign">  
              <!-- assign survey --> 
              <?php include ('./assignUserCheckbox/survey.php')?>   
              <!-- assign pulse --> 
              <?php include ('./assignUserCheckbox/pulse.php')?>  
               <!-- assign engagement --> 
               <?php include ('./assignUserCheckbox/engagement.php')?>  
               <!-- assign group -->
                <?php include ('./assignUserCheckbox/group.php')?>   
              <!-- assign location -->
              <?php include ('./assignUserCheckbox/location.php')?>   
              <!-- assign department -->
              <?php include ('./assignUserCheckbox/department.php')?>   
              <!-- assign role -->
              <?php include ('./assignUserCheckbox/role.php')?>   
            </div>  
            <?php }?>
              <div class="col-md-12">
                <div class="form-group">
                <?php  if(empty($_GET['id'])){ ?>
                  <input type="Submit" class="btn btn-primary btn-green" id="submit" value="Submit" name="submit" style="margin-top:24px"/>
                  <?php }else{?>
                <input type="Submit" class="btn btn-success btn-green" value="Update" name="update" style="margin-top:24px"/>
                <?php }?>
                <!--<span class="text-denger"><?php echo $_GET['msg']; ?></span>-->
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
$(function() {
    var validator =  $("form[name='myForm']").validate({
        rules: {
            name: "required",
            email: {
                required: true,
                email: true
            },
        },
        messages: {
            name		: "Please enter your firstname",
            email		: "Please enter a valid email address",
        },
        // Make sure the form is submitted to the destination defined
        // in the "action" attribute of the form when valid
        submitHandler: function(form) {
          form.submit();
        }
    });

    $("#validate").click(function() {
        if ($("form[name='myForm']").valid()) 
              alert("Valid!");
        else
              validator.focusInvalid();
        return false;
  });
});

jQuery('.form-control').click(function(){
  $(this).css("border-color", "#ccc");
});

    // jQuery('#submit').click(function(){
    //     jQuery(".error").hide();
    //     var haserror=false;

    //     var name = jQuery("#name").val();
    //     if(name == ''){
    //     document.getElementById("name").style.borderColor = "#ff0000";	
    //     alert("Name field is required");
    //     document.getElementById("name").focus();
    //     return false;
    //     }

    //     var email = jQuery("#email").val();
    //     if(email == ''){
    //         document.getElementById("email").style.borderColor = "#ff0000";
    //         alert("Email is required");
    //         document.getElementById("email").focus();
    //         return false;
            
    //     }

    //     var password = jQuery("#password").val();
    //     if(password == ''){	
    //         document.getElementById("password").style.borderColor = "#ff0000";
    //         alert("Password is required");
    //         document.getElementById("password").focus();
    //         return false;
    //     }

    //     var password = jQuery("#password").val();
    //     if(password == ''){	
    //         document.getElementById("password").style.borderColor = "#ff0000";
    //         alert("Password is required");
    //         document.getElementById("password").focus();
    //         return false;
    //     }

    //     var phone = jQuery("#phone").val();
    //     if(phone == ''){	
    //         document.getElementById("phone").style.borderColor = "#ff0000";
    //         alert("phone is required");
    //         document.getElementById("phone").focus();
    //         return false;
    //     }

    // });
});

$("#user_type").change(function(){
    $(".multiple-select").select2("destroy").select2();
    var currValue = $(this).val();
    if(currValue==3){
        //$('.title').html('Add Client');
        $('.location_field').show();
    }else {
        //$('.title').html('Add Admin');
        $('.location_field').hide();
    }
    $('#hidden_user_type').val(currValue);
});


function ajax_for_checkbox(id,mode){
    $.ajax({
        type: "POST",
        url: 'ajax/common_file.php',
        data: {id: id,mode:mode}, 
        success: function(response){
            if(mode == 'load_group'){
              $('.surveyCheck').html(response);
              $('.locationCheck').html('');
              $('.groupCheck').html('');
              $('.departmentCheck').html('');
            }
            if(mode == 'add_user_group_assign'){
              $('.groupCheck').html(response);
              $('.locationCheck').html('');
              $('.departmentCheck').html('');
            }
            if(mode == 'add_user_location_assign'){
              $('.locationCheck').html(response);
              $('.departmentCheck').html('');
            }
            if(mode == 'add_user_department_assign'){
              console.log(response);
              $('.departmentCheck').html(response);
            }
        }
    });
}
</script>
