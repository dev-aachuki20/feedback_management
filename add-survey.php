<?php 
    if(!empty($_GET['id'])){
        record_set("get_surveys", "select * from surveys where id='".$_GET['id']."'");
        $row_get_surveys = mysqli_fetch_assoc($get_surveys);
        $languages = explode(',',$row_get_surveys['language']);
    }
    if($_POST['update']){
        $dataCol = array(
            "name"                 => $_POST['name'],
            "survey_needed"        => $_POST['survey_needed'],
            "css_txt"              => $_POST['css_txt'],
            "alter_email"          => $_POST['alter_email'],
            "start_date"           => $_POST['sdate'],
            "end_date"             => $_POST['edate'],
            // "isStep"               => (isset($_POST['isStep'])) ? 1 : 0,
            // "isEnableContacted"    => (isset($_POST['isEnableContacted'])) ? 1 : 0,
            "google_review_link"   => $_POST['google_review_link'],
            "facebook_review_link" => $_POST['facebook_review_link'],
            "other_link"           => $_POST['other_link'],
        );

        // avoid updating disable value
        $new_data =  array(
            "survey_type"   => $_POST['survey_type'],
            "cstatus"       => $_POST['status'],
            "confidential"  => (isset($_POST['confidential'])) ? 1 : 0,
            "intervals"     => $_POST['interval'],
            "groups"        => implode(",",$_POST['groupid']),
            "locations"     => implode(",",$_POST['locationid']),
            "departments"   => implode(",",$_POST['departments']),
            //"isStep"             => (isset($_POST['isStep'])) ? 1 : 0,
            //"isEnableContacted"  => (isset($_POST['isEnableContacted'])) ? 1 : 0,
        );
        if($_SESSION['user_type']==1){
            $data = array_merge($dataCol,$new_data);
        }else {
            $data = $dataCol;
        }
        $updte=	dbRowUpdate("surveys", $data, "where id=".$_GET['id']);		
        if(!empty($updte)){
            if(isset($_POST['isStep']) && isset($_POST['numberOfStep'])){
                for($step = 0; $step < $_POST['numberOfStep']; $step++){
                    record_set("get_old_steps", "select * from surveys_steps where survey_id='".$_GET['id']."' and step_number='".intval($step+1)."'");
            
                    $lang_step_col=array();
                    record_set("get_lang", "select * from languages where cby='".$_SESSION['user_id']."'");				
                    while($row_get_lang = mysqli_fetch_assoc($get_lang)){	
                    if($row_get_lang['id'] !=1){
                        $lang_step_col["step_title_".$row_get_lang['iso_code']] = (isset($_POST['stepstitle_'.$row_get_lang['iso_code']][$step])) ? $_POST['stepstitle_'.$row_get_lang['iso_code']][$step]:'';
                    }
                }

                if($totalRows_get_old_steps > 0){   
                    $step_data_col = array("step_title" => $_POST['stepstitle'][$step]);
                    $step_data = array_merge($step_data_col,$lang_step_col);
                    $updte_steps= dbRowUpdate("surveys_steps", $step_data, "where survey_id=".$_GET['id']." and step_number=".intval($step+1));
                }else{
                    $step_data_col = array("survey_id" => $_GET['id'], "step_number" => $step+1, "step_title" => $_POST['stepstitle'][$step],'cby'=> $_SESSION['user_id'], 'cdate'=> date("Y-m-d H:i:s"));
                    $step_data = array_merge($step_data_col,$lang_step_col);
                    $insert_steps =  dbRowInsert("surveys_steps",$step_data);
                }
            }
        }
            $msg = "Survey Updated Successfully";  
            alertSuccess($msg,'?page=view-survey');
        }else{
            $msg = "Some error occured. Please try again later.";
            alertdanger($msg,'?page=add-survey&id='.$_GET["id"]);
        }
       // reDirect("?page=add-survey&id=".$_GET["id"]."&msg=".$msg);			
    }

    if(!empty($_POST['submit'])){			
        $dataCol =  array(
  			"name"                   => $_POST['name'],
  			"survey_needed"          => $_POST['survey_needed'],
  			"clientid"               => $_POST['clientid'],
            "adminid"                => $_POST['adminid'],
            "user_type"              => $_POST['user_type'],
            "survey_type"            => $_POST['survey_type'],
            "intervals"              => $_POST['interval'],
            "start_date"             => $_POST['sdate'],
            "end_date"               => $_POST['edate'],
            "confidential"           => (isset($_POST['confidential'])) ? 1 : 0,
            "alter_email"            => $_POST['alter_email'],
            "isStep"                 => (isset($_POST['isStep'])) ? 1 : 0,
            "isEnableContacted"      => (isset($_POST['isEnableContacted'])) ? 1 : 0,
            //"isSchoolAllowed"        => (isset($_POST['isSchoolAllowed'])) ? 1 : 0,
  			"css_txt"                => $_POST['css_txt'],
  			"cstatus"                => $_POST['status'],
  			"cip"                    => ipAddress(),
            "cby"                    => $_SESSION['user_id'],
            "groups"                => implode(",",$_POST['groupid']),
            "locations"              => implode(",",$_POST['locationid']),
            "departments"            => implode(",",$_POST['departments']),
  			"cdate"                  => date("Y-m-d H:i:s"),
            "google_review_link"     => $_POST['google_review_link'],
            "facebook_review_link"   => $_POST['facebook_review_link'],
            "other_link"             => $_POST['other_link'],
  		);
 	
        $lang_col=array();
        record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
        while($row_get_language = mysqli_fetch_assoc($get_language)){	
            if($row_get_language['id'] !=1){
                $lang_col["name_".$row_get_language['iso_code']] = $_POST['name_'.$row_get_language['iso_code']];
            }
        }
        $data = array_merge($dataCol,$lang_col);
        $insert_value =  dbRowInsert("surveys",$data);

        if(!empty($insert_value )){	
            //Insert Survey Steps
            if(isset($_POST['isStep']) && isset($_POST['numberOfStep'])){
                for($step = 0; $step < $_POST['numberOfStep']; $step++){
                    $step_data_col = array("survey_id" => $insert_value, "step_number" => $step+1, "step_title" => $_POST['stepstitle'][$step], 'cby'=> $_SESSION['user_id'], 'cdate'=> date("Y-m-d H:i:s"));
                    $lang_step_col=array();
                    record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
                    while($row_get_language = mysqli_fetch_assoc($get_language)){	
                        if($row_get_language['id'] !=1){
                            $lang_step_col["step_title_".$row_get_language['iso_code']] = (isset($_POST['stepstitle_'.$row_get_language['iso_code']][$step])) ? $_POST['stepstitle_'.$row_get_language['iso_code']][$step]:'';
                        }
                    }
                    $step_data = array_merge($step_data_col,$lang_step_col);
                    $insert_steps =  dbRowInsert("surveys_steps",$step_data);
                }
            }
  	        $msg = "Surveys Added Successfully";
            alertSuccess($msg,'?page=view-survey');
            if(!empty($_POST['alter_email'])){
                send_survey_email($_POST['alter_email'], $_POST['name'], $insert_value);
            }
        }else{
            $msg = "Some Error Occourd. Please try again..";
            alertdanger($msg,'?page=add-survey');
  	       
        }
        //reDirect("?page=add-survey&msg=".$msg);		
    }

// get data by user
$departmentByUsers = get_filter_data_by_user('departments');
$locationByUsers   = get_filter_data_by_user('locations');
$groupByUsers      = get_filter_data_by_user('groups');
    
?>

<section class="content-header">
    <h1> <?=($_GET['id'])?'Edit Survey':'Add Survey'?></h1>
    <a href="?page=view-survey" class="btn btn-primary pull-right" style="margin-top:-25px">View Survey</a> 
</section>
<style>
.container {
  margin-top: 20px;
}

.panel-heading {
  font-size: larger;
}

.alert {
  display: none;
}


/**
 * Error color for the validation plugin
 */

.error {
  color: #e74c3c;
}
</style>
<section class="content">
    <div class="box box-danger">
        <?php if(isset($_GET['msg'])){ ?>
			<div class="alert alert-success" role="alert">
				<?php echo $_GET['msg']; ?>
			</div>
		<?php } ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box-header"><i class="fa fa-edit"></i>Input</div>
                <div class="box-body">
                    <form action="" method="post" enctype="multipart/form-data" id="survey_from">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Survey Type *</label>
                                    <select class="form-control" name="survey_type" id="survey_type" required>
                                        <option value="">Select Survey Type </option>
                                        <?php foreach(survey_type() as $key => $value) { ?>
                                            <option value="<?=$key?>" <?=($row_get_surveys['survey_type']==$key)? 'selected' : '' ?>><?=$value?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Survey Name *</label>
                                    <input type="text" class="form-control" name="name" id="name" value="<?php echo $row_get_surveys['name'];?>" />
                                </div>
                            </div>
                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label>User Type</label>
                                    <select class="form-control" name="user_type" id="user_type"  <?=($_GET['id'])? 'disabled':'' ?>>
                                        <option value="">Select User</option>
                                        <option value="2" <?=($row_get_surveys['user_type'] ==2)?'selected':''?>>Admin</option>
                                        <option value="3" <?=($row_get_surveys['user_type'] ==3)?'selected':''?>>Manager</option>
                                    </select>
                                </div>
                            </div> -->
                            <div class="col-md-6" id="client-field" style="<?=($row_get_surveys['clientid']>0) ? 'display: block;':'display: none;'?>">
                                <div class="form-group">
                                    <label>Client Name</label>
                                    <select class="form-control" name="clientid" id="clientId" <?=($_GET['id'])? 'disabled':'' ?>>
                                    <option value="">Select Client</option>
                                        <?php
                                            record_set("get_client", "select * from clients where cby='".$_SESSION['user_id']."' and cstatus = 1" );				
                                            while($row_get_client = mysqli_fetch_assoc($get_client)){ ?>
                                            <option value="<?php echo $row_get_client['id'];?>" <?=($row_get_surveys['clientid'] ==$row_get_client['id'])?'selected':''?>><?php echo $row_get_client['name'];?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="admin-field" style="<?=($row_get_surveys['adminid']>0) ? 'display: block;':'display: none;'?>">
                                <div class="form-group">
                                    <label>Admin Name</label>
                                    <select class="form-control" name="adminid" id="adminId" <?=($_GET['id'])? 'disabled':'' ?>>
                                        <option value="">Select Admin</option>
                                        <?php
                                            record_set("get_admin", "select * from admin where cby='".$_SESSION['user_id']."' and cstatus = 1");				
                                            while($row_get_admin = mysqli_fetch_assoc($get_admin)){ ?>
                                            <option value="<?php echo $row_get_admin['id'];?>" <?=($row_get_surveys['adminid'] ==$row_get_admin['id'])?'selected':''?>><?php echo $row_get_admin['name'];?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
             
                            <?php if(empty($_GET['id'])){?>
                                <div class="col-md-3" style="display:none;">
                                    <div class="form-group">
                                        <label>Location</label>
                                        <select class="form-control" name="locationid" id="locationId">
                  	                        <?php
                                                record_set("get_location", "select * from locations where cby='".$_SESSION['user_id']."'");				
					                            while($row_get_location = mysqli_fetch_assoc($get_location)){	
					                        ?>
                                                <option value="<?php echo $row_get_location['id'];?>"><?php echo $row_get_location['name'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3" style="display:none;">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select class="form-control" name="departmentid">
                  	                        <?php
                                                record_set("get_department", "select * from departments where cby='".$_SESSION['user_id']."'");				
					                            while($row_get_department = mysqli_fetch_assoc($get_department)){	
				          	                ?>
                                                <option value="<?php echo $row_get_department['id'];?>"><?php echo $row_get_department['name'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Entry Needed</label>
                                    <input type="text" class="form-control" name="survey_needed" id="survey_needed" value="<?php echo $row_get_surveys['survey_needed'];?>"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status" <?=($_GET['id'] and $_SESSION['user_type']>1) ? 'disabled ': ''?>>
                                        <?php foreach(status() as $key => $value){ ?>
                                            <option <?php if($row_get_surveys['cstatus']==$key){?> selected="selected"<?php }?> value="<?php echo $key; ?>"><?php echo $value;?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Interval</label>
                                    <select class="form-control" id="interval" name="interval" <?=($_GET['id']) ? 'disabled ': ''?>>
                                        <?php foreach(service_type() as $key => $value){ ?>
                                            <option <?php if($row_get_surveys['intervals']==$key){?> selected="selected"<?php }?> value="<?php echo $key; ?>"><?php echo $value;?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date *</label>
                                    <input type="date" class="form-control" name="sdate" id="sdate" value="<?=date('Y-m-d',strtotime($row_get_surveys['start_date']))?>" required/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" class="form-control" name="edate" id="edate" value="<?=date('Y-m-d',strtotime($row_get_surveys['end_date']))?>"/>
                                </div>
                            </div>
                            <div class="col-md-6 dropdwn ">
                                <label>Group</label>
                                <input type="checkbox"id="allgrp" class="multiselect" onchange="select_all_option('allgrp','group_id')">
                                <select name="groupid[]" id="group_id" class="form-control form-control-lg multiple-select" multiple=multiple>
                                    <!-- <option value="">Please select</option> -->
                                    <?php //if(!empty($row_get_surveys['locations'])){
                                        $groups = explode(',',$row_get_surveys['groups']);
                                        $selected_option_group = array();
                                        foreach($groups as $val){
                                            $selected_option_group[] = $val;
                                        }
                                    foreach($groupByUsers as $groupData){ 
                                        $groupId    = $groupData['id'];
                                        $groupName  = $groupData['name'];
                                    ?>
                                        <option value="<?php echo $groupId;?>" <?php echo (in_array($groupId, $selected_option_group))? 'selected':''; ?>><?php echo $groupName;?></option>
                                    <?php }  ?>
                                </select>	
                            </div>
                            <div class="col-md-6 dropdwn ">
                                <div><label>Location</label>
                                <input type="checkbox" id="allLoc" class="multiselect" onchange="select_all_option('allLoc','location_id')"></div>
                                
                                <select name="locationid[]" id="location_id" class="form-control form-control-lg multiple-select" multiple=multiple>
                                    <!-- <option value="">Please select</option> -->
                                    <?php //if(!empty($row_get_surveys['locations'])){
                                        $locations = explode(',',$row_get_surveys['locations']);
                                        $selected_option_location = array();
                                        // if($row_get_surveys['isSchoolAllowed'] == 0){
                                            foreach($locations as $val){
                                                $selected_option_location[] = $val;
                                            }
                                        //}
                                        foreach($locationByUsers as $row_get_location) {?>
                                        <option value="<?php echo $row_get_location['id'];?>" <?php echo (in_array($row_get_location['id'], $selected_option_location))? 'selected':''; ?>><?php echo $row_get_location['name'];?></option>
                                    <?php }  ?>
                                </select>	
                            </div>
                            <div class="col-md-6 dropdwn ">
                                <label>Department</label>
                                <input type="checkbox"id="alldep" class="multiselect" onchange="select_all_option('alldep','departments')">
                                <select name="departments[]" id="departments" class="form-control form-control-lg multiple-select" multiple=multiple>
                                    <!-- <option value="">Please select</option> -->
                                    <?php
                                        $departments = explode(',',$row_get_surveys['departments']);
                                        $selected_option_department = array();
                                        //if($row_get_surveys['isSchoolAllowed'] == 0){
                                            foreach($departments as $val){
                                                $selected_option_department[] = $val;
                                            }
                                        //}
                                        foreach($departmentByUsers as $row_get_department) {?>
                                        <option value="<?php echo $row_get_department['id'];?>" <?php echo (in_array($row_get_department['id'], $selected_option_department))? 'selected':''; ?>><?php echo $row_get_department['name'];?></option>
                                    <?php  } ?>
                                </select>	
                            </div>
                            <div class="col-md-6">
                                <div class="col-md-6">
                                    <label for=""></label>
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="isEnableContacted" name="isEnableContacted" <?php echo ($row_get_surveys['isEnableContacted'] == 1) ? "checked" : ""; ?>>
                                            <label class="form-check-label" for="isEnableContacted"> Enable To Be Contacted </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for=""></label>
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="confidential" name="confidential" <?php echo ($row_get_surveys['confidential'] == 1) ? "checked" : ""; ?>>
                                            <label class="form-check-label" for="confidencial"> Confidencial </label>
                                        </div>
                                    </div>
                                </div>            
                            </div>
                            <div class="col-md-12" style="padding-top:20px;">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="isStep" name="isStep" <?php echo ($row_get_surveys['isStep'] == 1) ? "checked" : ""; ?>>
                                    <label class="form-check-label" for="isStep"> Will have steps </label>
                                </div>
                            </div>
                            <?php 
                                record_set("get_surveys_steps", "select * from surveys_steps where survey_id='".$_GET['id']."'");
                                $old_steps_titles = "testing";
                            ?>
                            <div class="col-md-12 whenStepAllow">
                                <div class="form-group">
                                    <label for="numberOfStep">How many steps</label>
                                    <input type="number" class="form-control" id="numberOfStep" name="numberOfStep" min="1" max="50" placeholder="Number Of Steps" value="<?php echo ($totalRows_get_surveys_steps > 0) ? $totalRows_get_surveys_steps : ""; ?>">
                                </div>
                            </div>
                            <?php 
                                if($totalRows_get_surveys_steps > 0){ 
                                    while($row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps)){
                            ?>
                                <div id="oldStepsTitle" class="whenStepAllow">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Step <?php echo $row_get_surveys_steps['step_number']; ?> Title</label>
                                            <input type="text" class="form-control" id="stepTitle<?php echo $row_get_surveys_steps['step_number']; ?>" name="stepstitle[]" value=" <?php echo $row_get_surveys_steps['step_title']; ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php  } } ?>
                            <div id="stepsTitle" class="whenStepAllow">
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Alert Email (comma sepration for multiple email)</label>
                                    <textarea name="alter_email" rows="3"  class="form-control"><?php echo $row_get_surveys['alter_email'];?></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Custom CSS</label>
                                    <textarea name="css_txt" rows="3" class="form-control"><?php echo $row_get_surveys['css_txt'];?></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Google Review Link</label>
                                    <input type="text" class="form-control" id="google_review_link" name="google_review_link" value="<?php echo $row_get_surveys['google_review_link']?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Facebook Review Link</label>
                                    <input type="text" class="form-control" id="facebook_review_link" name="facebook_review_link" value="<?php echo $row_get_surveys['facebook_review_link']?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Other Link</label>
                                    <input type="text" class="form-control" id="other_link" name="other_link" value="<?php echo $row_get_surveys['other_link']?>">
                                </div>
                            </div>
              
                            <!-- Start submit button -->
                            <div class="col-md-12">
                                <div class="text-right">
                                    <span class="text-danger"><?php echo $_GET['msg']; ?></span> &nbsp;
                                    <?php if(empty($_GET['id'])){ ?>
                                        <input type="Submit" class="btn btn-primary" value="Create" name="submit" id="submit" style="margin-top:24px"/>
                                    <?php }else{ 
                                        if($_SESSION['user_type']==1){ ?>
                                          <input type="Submit" class="btn btn-primary" value="Update" id="update" name="update" style="margin-top:24px"/>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <!-- End submit button -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
$(document).ready(function(){
    //check user type selcted
    $('#user_type').change(function(){
        let userType = $(this).val();
        if(userType==2){
            $('#client-field').hide();
            $('#admin-field').show();
            $('#clientId').val('');
        }else if(userType==3){
            $('#admin-field').hide();
            $('#client-field').show();
            $('#adminId').val('');
        }
    })



    if ($('#isStep').is(':checked')) {
      $(".whenStepAllow").show();
    } else {
      $(".whenStepAllow").hide();
    }
    $("#isStep").change(function() {
      if(this.checked) {
        $(".whenStepAllow").show();
      }else{
        $(".whenStepAllow").hide();
        $("#stepsTitle").html("");
        $('#numberOfStep').val('');
      }
    });

    $("#numberOfStep").keyup(function() {
      $("#stepsTitle").html("");
      var numberOfSteps = $(this).val();
      var i;
      var html = "";
      var intial = 1;
      if(numberOfSteps != "" && numberOfSteps > 0){
        var intial = "<?php echo ($totalRows_get_surveys_steps > 0) ? $totalRows_get_surveys_steps+1 : 1; ?>";
      }
      for(i=intial; i <= numberOfSteps; i++){
        html += '<div class="col-md-12"><div class="form-group"><label>Step '+i+' Title</label><input type="text" class="form-control step_checkbox" id="stepTitle'+i+'" name="stepstitle[]"></div></div>';
      }
      <?php 
          record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
          while($row_get_language = mysqli_fetch_assoc($get_language)){	
            if($row_get_language['id'] !=1){
      ?>
            if($('#lang_<?=$row_get_language['iso_code']?>').prop('checked')==true){
              if($('#lang_<?=$row_get_language['iso_code']?>').val() == '<?=$row_get_language['id']?>'){
                for(i=intial; i <= numberOfSteps; i++){
                  html += '<div class="col-md-12" id="lang_<?=$row_get_language['iso_code']?>"><div class="form-group"><label>Step '+i+' Title - <?=$row_get_language['name']?></label><input type="text" class="form-control" id="stepTitle'+i+'" name="stepstitle_<?=$row_get_language['iso_code']?>[]"></div></div>';
                }
              }
            }
      <?php 
            } 
          } 
      ?>
      $("#stepsTitle").html(html);
      $(".step_checkbox").attr("required", true);
    });
    // Start js according to language

    // End js according to language
		$('.form-control').click(function(){
			$(this).css("border-color", "#ccc");
		});
		
		// $("#submit").click(function(){
        //     var name = $("#name").val();
        //     if(name==''){
        //         document.getElementById('name').style.borderColor = "#ff0000";
        //         alert('Name field is required');
        //         document.getElementById('name').focus();
        //         return false;
        //     }
		// });

    // $('#clientId').change(function(){
  
    //     var client_id = $(this).val();
    //     $.ajax({
    //         type: "POST",
    //         url: 'ajax/ajaxOnSelectClientLocation.php',
    //         data: {client_id: client_id}, 
    //         success: function(response)
    //         {
    //           //  console.log(response);
    //           if (response == '') {
    //             $('#location_id').html('<option value="">Please select</option>');
    //           }else{
    //             $('#location_id').html('<option value="">Please select</option>'+response); 
    //           }
              
    //         }
    //     });
        
    // });  

   
    // $('#isEnableLocation').click(function(){
    //     if($(this).prop("checked") == true){
    //         $('#location_id').attr('multiple','multiple');
    //       }
    //       else if($(this).prop("checked") == false){
    //         $('#location_id').removeAttr('multiple','multiple');
    //       }
    // });

    // $('#isEnableDepartment').click(function(){
    //     if($(this).prop("checked") == true){
    //         $('#departments').attr('multiple','multiple');
    //       }
    //       else if($(this).prop("checked") == false){
    //         $('#departments').removeAttr('multiple','multiple');
    //       }
    // });

    // $('#isEnableGroup').click(function(){
    //     if($(this).prop("checked") == true){
    //         $('#group_id').attr('multiple','multiple');
    //       }
    //       else if($(this).prop("checked") == false){
    //         $('#group_id').removeAttr('multiple','multiple');
    //       }
    // });
});
    
//select all option for location department,group
function select_all_option(idFirst,idSecond){
    idFirst  = "#"+idFirst;
    idSecond = "#"+idSecond;
    if($(idFirst).is(':checked')){
        $(idSecond+" > option").prop("selected", "selected");
        $(idSecond).trigger("change");
    } else {
        $(idSecond+"> option").removeAttr("selected");
        $(idSecond).trigger("change");
    }
}
//disabled form for other users
<?php if($_GET['id']){ ?>
    $('#survey_from input').attr('readonly','readonly');
    $('#survey_from textarea').attr('readonly','readonly');
    <?php if($_SESSION['user_type'] == 1) { ?>
    $('#interval').removeAttr('disabled');
    $('#sdate').removeAttr('readonly');
    $('#edate').removeAttr('readonly');   
    $('#survey_from input:checkbox').not('.multiselect, #confidential').attr('disabled','true');
    <?php }else { ?> 
    $('#survey_from input:checkbox').attr('disabled','true');
    $('.multiple-select').attr('disabled','true');
    $('#survey_type').attr('disabled','true');
    <?php } ?>
<?php } ?>

var $form = $("#survey_from"),
$successMsg = $(".alert");
$.validator.addMethod("letters", function(value, element) {
  return this.optional(element) || value == value.match(/^[a-zA-Z\s]*$/);
});
$form.validate({
  rules: {
    name: {
      required: true,
    },
    survey_type: {
      required: true,
    },
    sdate: {
      required: true,
    },
  },
  messages: {
    name        : "Please specify your Survey name ",
    survey_type : "Please choose the survey type",
    sdate       : "Please choose the Start Date"
  },

  submitHandler: function($form) {
    $form.submit();
  }
});
</script>