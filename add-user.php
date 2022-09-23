<style>
  .select2{
    width: 100% !important;
  }
</style>
<?php

  $coloumn_name = '';
	if(!empty($_GET['id'])){
    if($_GET['t']=='a'){
      record_set("get_admin_id", "select * from admin where id='".$_GET['id']."'");
      $coloumn_name = 'admin_ids';
      $title = 'Admin';
    }else if($_GET['t']=='c'){
      record_set("get_admin_id", "select * from clients where id='".$_GET['id']."'");
      $coloumn_name = 'client_ids';
      $title = 'Manager';
    }else if($_GET['t']=='sa'){
      record_set("get_admin_id", "select * from super_admin where id='".$_GET['id']."'");
      $title = 'Super Admin';
      $coloumn_name = 'admin_ids';
    }else {
      echo 'Invalid User'; die();
    }
    $row_get_admin_id = mysqli_fetch_assoc($get_admin_id);
  }
// Start update
$client_id = '';
$admin_id  = '';
$user_type = '';

	if(!empty($_POST['update'])){
      $file_name=$_FILES['photo']['name'];
      $file_tempname = $_FILES['photo']['tmp_name'];
      $folder="upload_image/";
      $image_id=rand(1000,100000)."-";
      $result=upload_image1($folder,$file_name, $file_tempname,$image_id);
      $user_type = $_POST['user_type'];

      if(!empty($result)){
          unlink("upload_image/".$row_get_admin_id['photo']);	
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
      if($user_type==1){
        // for super admin update
        $updte=	dbRowUpdate("super_admin", $data, "where id=".$_GET['id']);
      }
      if($user_type==2){
        // for admin update
          $updte=	dbRowUpdate("admin", $data, "where id=".$_GET['id']);
      }else {
        // for manager update
          $data['locationid'] =implode(",",$_POST['locationid']);
          $updte=	dbRowUpdate("clients", $data, "where id=".$_GET['id']);

          if(isset($_POST['locationid'])){
            $locationids .= implode('|', $_POST['locationid']); 
          }
      }
      if(!empty($updte)){
          //assign user location,group,department
          $groupids =$locationids= $departmentids= $survey_type='';
          if($_POST){
            $surveyids .= implode('|', $_POST['surveyids']); 
            $surveyids  = "|".$surveyids."|";

            $groupids .= implode('|', $_POST['groupids']); 
            $groupids  = "|".$groupids."|";

            $departmentids .= implode('|', $_POST['departmentids']); 
            $departmentids ="|".$departmentids."|";
            
            $locationids .= implode('|', $_POST['locationids']); 
            $locationids ="|".$locationids."|";
            $uid = $_GET['id'];
            
            // survey update
            $row_data_surveys='';
            record_set("data_surveys","select id,$coloumn_name from surveys");
            while($row_data_surveys=mysqli_fetch_assoc($data_surveys)){
              $manager_ids = str_replace("|".$uid."|","",$row_data_surveys[$coloumn_name]);
              if(in_array($row_data_surveys['id'], $_POST['surveyids'])){
                $manager_ids = $manager_ids."|".$uid."|";
              }
              $manager_ids = (array_unique(array_filter(explode("|",$manager_ids))));
              $manager_ids = implode("|",$manager_ids);
              //if(strlen($manager_ids)>0){
                $user_data_update = array(
                  "$coloumn_name" => "|".$manager_ids."|"
                );
                $update = dbRowUpdate('surveys', $user_data_update, 'where id='.$row_data_surveys['id']);
              //}
            }

            // group update
            $row_data_group='';
            record_set("data_group","select id,$coloumn_name from groups");
            while($row_data_group=mysqli_fetch_assoc($data_group)){
              $manager_ids = str_replace("|".$uid."|","",$row_data_group[$coloumn_name]);
              if(in_array($row_data_group['id'], $_POST['groupids'])){
                $manager_ids = $manager_ids."|".$uid."|";
              }
              $manager_ids = (array_unique(array_filter(explode("|",$manager_ids))));
              $manager_ids = implode("|",$manager_ids);
              //if(strlen($manager_ids)>0){
                $user_data_update = array(
                  "$coloumn_name" => "|".$manager_ids."|"
                );
                $update = dbRowUpdate('groups', $user_data_update, 'where id='.$row_data_group['id']);
              //}
            }
          
              //location update
              $row_data_department='';
              record_set("data_locations","select id,$coloumn_name from locations");
              while($row_data_locations=mysqli_fetch_assoc($data_locations)){
                $manager_ids = str_replace("|".$uid."|","",$row_data_locations[$coloumn_name]);
                if(in_array($row_data_locations['id'], $_POST['locationids'])){
                  $manager_ids = $manager_ids."|".$uid."|";
                }
                $manager_ids = (array_unique(array_filter(explode("|",$manager_ids))));
                $manager_ids = implode("|",$manager_ids);
                $user_data_update = array(
                  "$coloumn_name"  => "|".$manager_ids."|"
                );
                $update = dbRowUpdate("locations", $user_data_update, "where id=".$row_data_locations['id']);
              }
            
            //Update Deprtment
            $row_data_department='';
            record_set("data_departments","select id,$coloumn_name from departments");
            while($row_data_departments=mysqli_fetch_assoc($data_departments)){

              $manager_ids = str_replace("|".$uid."|","",$row_data_departments[$coloumn_name]);
              if(in_array($row_data_departments['id'], $_POST['departmentids'])){
                $manager_ids = $manager_ids."|".$uid."|";
              }
              $manager_ids = (array_unique(array_filter(explode("|",$manager_ids))));
              $manager_ids = implode("|",$manager_ids);
              $user_data_update = array(
                "$coloumn_name"  => "|".$manager_ids."|"
              );
              $update = dbRowUpdate("departments", $user_data_update, "where id=".$row_data_departments['id']);
            }
          }
          $msg = "User Updated Successfully";
          if(isset($_GET['user'])){
            alertSuccess( $msg,'');
          }else {
            //alertSuccess( $msg,'?page=view-user');
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
$departmentByUsers = get_filter_data_by_user('departments');
$locationByUsers   = get_filter_data_by_user('locations');
$groupByUsers      = get_filter_data_by_user('groups');
$surveyByUsers     = get_filter_data_by_user('surveys');
?>

<?php
if(!empty($_POST['submit'])){
    $user_type = $_POST['user_type'];
    $file_name=$_FILES['photo']['name'];
    $file_tempname = $_FILES['photo']['tmp_name'];
    $folder="upload_image/";
    $image_id=rand(1000,100000)."-";
    $result=upload_image1($folder,$file_name, $file_tempname,$image_id);
    
    record_set("checkEmail", "select * from admin where email='".$_POST['email']."'");
			
    if($totalRows_checkEmail>0){
       // alert("Email already exits");
        $mess = 'Email already exits';
        alertdanger($mess,'');
        //reDirect("?page=add-user&msg=".$mess);		

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
      if($user_type==3){
          $data['locationid'] =implode(",",$_POST['locationids']);
          $insert_value =  dbRowInsert("clients",$data);
      }else{
          $insert_value =  dbRowInsert("admin",$data);
      }
    
      if(!empty($insert_value )){	
          //assign user location,group,department
          $groupids =$locationids= $departmentids= $asset_type_allowed='';
          //assign user location,group,department
          $groupids =$locationids= $departmentids= $survey_type='';
          if($_POST){
            $surveyids .= implode('|', $_POST['surveyids']); 
            $surveyids  = "|".$surveyids."|";

            $groupids .= implode('|', $_POST['groupids']); 
            $groupids  = "|".$groupids."|";

            $departmentids .= implode('|', $_POST['departmentids']); 
            $departmentids ="|".$departmentids."|";
            
            $locationids .= implode('|', $_POST['locationids']); 
            $locationids ="|".$locationids."|";
            $uid = $_GET['id'];
            
            // survey update
            $row_data_surveys='';
            record_set("data_surveys","select id,$coloumn_name from surveys");
            while($row_data_surveys=mysqli_fetch_assoc($data_surveys)){
              $manager_ids = str_replace("|".$uid."|","",$row_data_surveys[$coloumn_name]);
              if(in_array($row_data_surveys['id'], $_POST['surveyids'])){
                $manager_ids = $manager_ids."|".$uid."|";
              }
              $manager_ids = (array_unique(array_filter(explode("|",$manager_ids))));
              $manager_ids = implode("|",$manager_ids);
              //if(strlen($manager_ids)>0){
                $user_data_update = array(
                  "$coloumn_name" => "|".$manager_ids."|"
                );
                $update = dbRowUpdate('surveys', $user_data_update, 'where id='.$row_data_surveys['id']);
              //}
            }

            // group update
            $row_data_group='';
            record_set("data_group","select id,$coloumn_name from groups");
            while($row_data_group=mysqli_fetch_assoc($data_group)){
              $manager_ids = str_replace("|".$uid."|","",$row_data_group[$coloumn_name]);
              if(in_array($row_data_group['id'], $_POST['groupids'])){
                $manager_ids = $manager_ids."|".$uid."|";
              }
              $manager_ids = (array_unique(array_filter(explode("|",$manager_ids))));
              $manager_ids = implode("|",$manager_ids);
              //if(strlen($manager_ids)>0){
                $user_data_update = array(
                  "$coloumn_name" => "|".$manager_ids."|"
                );
                $update = dbRowUpdate('groups', $user_data_update, 'where id='.$row_data_group['id']);
              //}
            }
          
              //location update
              $row_data_department='';
              record_set("data_locations","select id,$coloumn_name from locations");
              while($row_data_locations=mysqli_fetch_assoc($data_locations)){
                $manager_ids = str_replace("|".$uid."|","",$row_data_locations[$coloumn_name]);
                if(in_array($row_data_locations['id'], $_POST['locationids'])){
                  $manager_ids = $manager_ids."|".$uid."|";
                }
                $manager_ids = (array_unique(array_filter(explode("|",$manager_ids))));
                $manager_ids = implode("|",$manager_ids);
                $user_data_update = array(
                  "$coloumn_name"  => "|".$manager_ids."|"
                );
                $update = dbRowUpdate("locations", $user_data_update, "where id=".$row_data_locations['id']);
              }
            
            //Update Deprtment
            $row_data_department='';
            record_set("data_departments","select id,$coloumn_name from departments");
            while($row_data_departments=mysqli_fetch_assoc($data_departments)){

              $manager_ids = str_replace("|".$uid."|","",$row_data_departments[$coloumn_name]);
              if(in_array($row_data_departments['id'], $_POST['departmentids'])){
                $manager_ids = $manager_ids."|".$uid."|";
              }
              $manager_ids = (array_unique(array_filter(explode("|",$manager_ids))));
              $manager_ids = implode("|",$manager_ids);
              $user_data_update = array(
                "$coloumn_name"  => "|".$manager_ids."|"
              );
              $update = dbRowUpdate("departments", $user_data_update, "where id=".$row_data_departments['id']);
            }
          }
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
  <h1 class="title"><?=isset($_GET['id'])?'Edit':'Add'?> <?=$title?></h1>
  <?php if(!isset($_GET['user'])) { ?>
  <a href="?page=view-user" class="btn btn-primary pull-right" style="margin-top:-25px">View Users</a> 
  <?php } ?>
</section>
<section class="content">
  <div class="box box-danger">
    <div class="row">
      <div class="col-md-12">
        <div class="box-header"><i class="fa fa-edit"></i>Input</div>
        <div class="box-body">
            <?php if(isset($_GET['msg']) && !empty($_GET['msg'])){ ?>
             <div class="alert alert-danger text-denger" role="alert"><?=$_GET['msg']?></div>
            <?php  } ?>
            
          <form action="" method="post" name="myForm" enctype="multipart/form-data">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Name *</label>
                  <input type="text" class="form-control" name="name"  id="name" value="<?php echo $row_get_admin_id['name']?>"/>
                </div>
              </div>
              <div class="col-md-6">
                <?php 
                  $user_types_array=user_type();
                  if($_GET['t']==='sa'){
                    $type = 1;
                  }else if($_GET['t']==='a'){
                    $type = 2;
                  }else {
                    $type = 3;
                  }
                  
                  ?>
                <div class="form-group">
                  <input type="hidden" id="hidden_user_type" name="user_type" value="<?=$row_get_admin_id['user_type']?>">
                  <label>User Type</label>
                    <select class="form-control" tabindex=7 id="user_type" <?=($_GET['id'])?'disabled':'required'?>>
                        <option value="">Select User Type</option>
                      <?php   
                        foreach($user_types_array as $key => $value){
                          if($_SESSION['user_type']==2){
                            $allowed_key=2;
                          }
                          if($key>=$_SESSION['user_type'] and $key!=1){ ?>
                          <option <?php if($type==$key){?> selected="selected"<?php  }?> value="<?php echo $key; ?>"> <?php echo $value; ?>
                          </option>
                        <?php }
                        }
                    
                     ?>
					          </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Email</label>
                  <input type="text" class="form-control" id="email" name="email"value="<?php echo $row_get_admin_id['email']?>" <?=($_GET['id'])?'disabled':''?>/>
                </div>
              </div>
         
              <div class="col-md-6">
                <div class="form-group">
                  <label>Password</label>
                  <input type="password" class="form-control" name="password" id="password" value=""/>
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
                  $locations = explode(',',$row_get_admin_id['locationid']); ?>
                  <option value="<?php echo $row_get_location['id'];?>" 
                  <?=(in_array($row_get_location['id'],$locations)) ? 'selected':''?>>
                    <?php echo $row_get_location['name'];?>
                  </option>
	              <?php }?>
	              </select>	
              </div> -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Phone *</label>
                  <input type="text" class="form-control" name="phone" id="phone" value="<?php echo $row_get_admin_id['phone']?>"/>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Photo</label>
                  <input type="file" class="form-control" name="photo"/>
                  <?php if(!empty($_GET['id'])){?>
                  <img src="upload_image/<?php echo $row_get_admin_id['photo']?>" height="50" width="50" />
                  <?php }?>
                </div>
              </div>
            <?php if(empty($_GET['user']) || $_SESSION['user_type'] >1 ) { ?>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status</label>
                  <select class="form-control" name="status">
                    <?php foreach(status() as $key=> $value){ ?>
                      <option value="<?php echo $key; ?>" <?=($row_get_admin_id['cstatus']==$key)?'selected':''?>><?php echo $value; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <?php } ?>
            <?php if(!isset($_GET['user'])) {?>
            <div class="col-md-12 survey-assign">  
              <!-- assign survey --> 
              <?php if(count($surveyByUsers)>0){ ?>
                <div class="col-md-12 with-border">
                  <h4>Assign Survey</h4>
                  <input type="checkbox" onclick="checked_all(this,'survey_checkbox')" /><strong> Select All</strong><br/><br/>
                </div>
                <?php 
                if(isset($_GET['id'])){
                    record_set("get_survey_id", "select * from surveys where $coloumn_name like '%|".$_GET['id']."|%'");
                    while($row_get_survey_id=mysqli_fetch_assoc($get_survey_id)){
                      $survey_saved[] =$row_get_survey_id['id'];
                    }
                    }else{
                      $survey_saved = array();
                    }
                foreach($surveyByUsers as $suveyData){ 
                  $survyId    = $suveyData['id'];
                  $survyName  = $suveyData['name'];
                  ?>
                  <div class="col-md-4">
                    <input class="survey_checkbox" type="checkbox" <?=(in_array($survyId,$survey_saved) ? 'checked ':' ')?> id="surveyids<?php echo $survyId ?>" value="<?php echo $survyId; ?>" name="surveyids[<?php echo $survyId; ?>]" /> 
                    
                    <label for="surveyids<?php echo $survyId; ?>">
                    <?php echo $survyName ?>
                    </label>
                  </div>
                <?php } ?>   
              <?php } ?>    
              <!-- assign location -->
              <?php if(count($locationByUsers)>0){ ?>
                <div class="col-md-12 with-border">
                  <h4>Assign Location</h4>
                  <input type="checkbox" onclick="checked_all(this,'loc_checkbox')" /><strong> Select All</strong><br/><br/>
                </div>
                
                <?php 
                if(isset($_GET['id'])){
                  record_set("get_location_id","select id from locations where $coloumn_name like '%|".$_GET['id']."|%'");
                  if($totalRows_get_location_id>0){
                    while($row_getlocation=mysqli_fetch_assoc($get_location_id)){
                      $location_id_saved[] =$row_getlocation['id'];
                    }
                  }
                }else{
                    $location_id_saved = array();
                }
                foreach($locationByUsers as $locationData){ 
                  $locationId    = $locationData['id'];
                  $locationName  = $locationData['name'];
                  ?>
                  <div class="col-md-4">
                    <input type="checkbox" <?=(in_array($locationId,$location_id_saved) ? 'checked ':' ')?> id="locationids<?php echo $locationId ?>" class="loc_checkbox" value="<?php echo $locationId; ?>" name="locationids[<?php echo $locationId; ?>]" /> 
                    
                    <label for="locationids<?php echo $locationId; ?>">
                    <?php echo $locationName ?>
                    </label>
                  </div>
                <?php } ?>
              <?php } ?>  
              <!-- assign department -->
              <?php if(count($departmentByUsers)>0){ ?>
                <div class="col-md-12 with-border">
                  <h4>Assign Departments</h4>
                  <input type="checkbox" onclick="checked_all(this,'dept_checkbox')" /><strong> Select All</strong><br/><br/>
                </div>
                <?php 
                if(isset($_GET['id'])){
                  record_set("get_department_id","select id from departments where $coloumn_name like '%|".$_GET['id']."|%'");
                  if($totalRows_get_department_id>0){
                    while($row_department_id=mysqli_fetch_assoc($get_department_id)){
                      $department_id_saved[] =$row_department_id['id'];
                    }
                  }
                }else{
                    $department_id_saved = array();
                }
                foreach($departmentByUsers as $deptData){ 
                  $deptId  = $deptData['id'];
                  $deptName = $deptData['name']; ?>
                  <div class="col-md-4">
                    <input type="checkbox" <?=(in_array($deptId,$department_id_saved) ? 'checked ':' ')?> id="departmentids<?php echo $deptId ?>" class="dept_checkbox" value="<?php echo $deptId; ?>" name="departmentids[<?php echo $deptId; ?>]"/> 
                    <label for="departmentids<?php echo $deptId; ?>">
                    <?php echo $deptName ?>
                    </label>
                  </div>
                <?php }?>
              <?php } ?>
              <!-- assign group -->
              <?php if(count($groupByUsers)>0){ ?>
                <div class="col-md-12 with-border">
                  <h4>Assign Group</h4>
                  <input type="checkbox" onclick="checked_all(this,'group_checkbox')" /><strong> Select All</strong><br/><br/>
                </div>
                <?php 
                if(isset($_GET['id'])){
                  record_set("get_group_id","select id from groups where $coloumn_name like '%|".$_GET['id']."|%'");
                  if($totalRows_get_group_id>0){
                    while($row_group_id=mysqli_fetch_assoc($get_group_id)){
                      $group_id_saved[] =$row_group_id['id'];
                    }
                  }
                }else{
                    $group_id_saved = array();
                }
                foreach($groupByUsers as $groupData){ 
                  $groupId    = $groupData['id'];
                  $groupName  = $groupData['name'];
                  ?>
                  <div class="col-md-4">
                    <input type="checkbox" <?=(in_array($groupId,$group_id_saved) ? 'checked ':' ')?> id="groupids<?php echo $groupId ?>" class="group_checkbox" value="<?php echo $groupId; ?>" name="groupids[<?php echo $groupId; ?>]" /> 
                    
                    <label for="groupids<?php echo $groupId; ?>">
                    <?php echo $groupName ?>
                    </label>
                  </div>
                <?php } ?>  
              <?php } ?>
            </div>

            <?php }?>
              <div class="col-md-12">
                <div class="form-group">
                <?php  if(empty($_GET['id'])){ ?>
                  <input type="Submit" class="btn btn-primary" id="submit" value="Create" name="submit" style="margin-top:24px"/>
                  <?php }else{?>
                <input type="Submit" class="btn btn-primary" value="Update" name="update" style="margin-top:24px"/>
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
            phone: "required",
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
        $('.title').html('Add Client');
        $('.location_field').show();
    }else {
        $('.title').html('Add Admin');
        $('.location_field').hide();
    }
    $('#hidden_user_type').val(currValue);
});

</script>
