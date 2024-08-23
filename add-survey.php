
<?php 
    if(!empty($_GET['id'])){
        record_set("get_surveys", "select * from surveys where id='".$_GET['id']."'");
        record_set("get_question", "select * from questions where surveyid='".$_GET['id']."'");
        $row_get_surveys = mysqli_fetch_assoc($get_surveys);
        record_set("get_mailing_users", "select * from surveys_mailing_users where survey_id='".$_GET['id']."'");
        record_set("isSurveyRelatedToAssignTask", "select * from assign_task  where survey_id='".$_GET['id']."'");
    }
    
    if($_POST['update']){
        $dataCol = array(
            "name"                          => $_POST['name'],
            "survey_type"                   => $_POST['survey_type'],
            "survey_needed"                 => $_POST['survey_needed'],
            "cstatus"                       => $_POST['status'],
            "confidential"                  => (isset($_POST['confidential'])) ? 1 : 0,
            "intervals"                     => $_POST['interval'],
            "css_txt"                       => $_POST['css_txt'],
            "send_by"                       => $_POST['send_by'],
            "alter_email"                   => $_POST['alter_email'],
            "contact_requested"             => $_POST['contact_requested'],
            "start_date"                    => $_POST['sdate'],
            "end_date"                      => $_POST['edate'],
            "question_limit"                => $_POST['question_limit'],
            // "isStep"                        => (isset($_POST['isStep'])) ? 1 : 0,
            "isStep"                        => 1,
            "isEnableContacted"             => (isset($_POST['isEnableContacted'])) ? 1 : 0,
            "contacted_request_label"       => $_POST['contacted_request_label'],
            "google_review_link"            => $_POST['google_review_link'],
            "facebook_review_link"          => $_POST['facebook_review_link'],
            "other_link"                    => $_POST['other_link'],
        );

        // avoid updating disable value
        $new_data =  array(
            "groups"        => implode(",",$_POST['groupid']),
            "locations"     => implode(",",$_POST['locationid']),
            "departments"   => implode(",",$_POST['departments']),
            "roles"         => implode(",",$_POST['roles']),
            "notification_threshold_users"  => implode(",",$_POST['notification_threshold_users']),
            "notification_threshold"        => (isset($_POST['notification_threshold'])) ? 1 : 0,
            "select_percentage"             => $_POST['select_percentage'],

            //"isStep"             => (isset($_POST['isStep'])) ? 1 : 0,
            //"isEnableContacted"  => (isset($_POST['isEnableContacted'])) ? 1 : 0,
        );

        if($_SESSION['user_type']==1){
            $data = array_merge($dataCol,$new_data);
            $mailing_user_ids = $_POST['mailing_user_id'];
            $contact_requested = $_POST['contact_requested'];
            $is_pdf = isset($_POST['is_pdf']) && $_POST['is_pdf'] == 'on' ? 1 : 0;
        }else if($_SESSION['user_type']==2) {
            $data = $new_data;
            $mailing_user_ids = $_POST['mailing_user_id'];
            $contact_requested = $_POST['contact_requested'];
            $is_pdf = isset($_POST['is_pdf']) && $_POST['is_pdf'] == 'on' ? 1 : 0;

        }
        $updte=	dbRowUpdate("surveys", $data, "where id=".$_GET['id']);		

    
        if(!empty($updte)){
            $numberOfStep = (isset($_POST['numberOfStep']) && !empty($_POST['numberOfStep'])) ?  $_POST['numberOfStep'] : 1;
            //if(isset($_POST['isStep'])){
                for($step = 0; $step < $numberOfStep; $step++){
                    if($numberOfStep == 1){
                        $step_title = 'Step 1';
                    }else{
                        $step_title = $_POST['stepstitle'][$step];

                    }
                    record_set("get_old_steps", "select * from surveys_steps where survey_id='".$_GET['id']."' and step_number='".intval($step+1)."'");
                    if($totalRows_get_old_steps > 0){   
                        $step_data = array("step_title" => $_POST['stepstitle'][$step]);
                        $updte_steps= dbRowUpdate("surveys_steps", $step_data, "where survey_id=".$_GET['id']." and step_number=".intval($step+1));
                    }else{
                        $step_data = array("survey_id" => $_GET['id'], "step_number" => $step+1, "step_title" => $step_title ,'cby'=> $_SESSION['user_id'], 'cdate'=> date("Y-m-d H:i:s"));
                        $insert_steps =  dbRowInsert("surveys_steps",$step_data);
                    }
                }
            //}

            // insert user mails for notification
            dbRowDelete('surveys_mailing_users', 'survey_id ='.$_GET['id']);

            if(is_array($mailing_user_ids) && count($mailing_user_ids) > 0 && is_array($contact_requested) && count($contact_requested) > 0){
                foreach ($mailing_user_ids as $key => $user_id){
                    $is_pdf_value = isset($_POST['is_pdf'][$user_id]) ? 1 : 0;
                    $userMailData = array("survey_id" => $_GET['id'], "user_id" => $user_id, "is_contact_requested" => $contact_requested[$key], "is_pdf" => $is_pdf_value);
                    $insert_steps =  dbRowInsert("surveys_mailing_users",$userMailData);
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

       //get qrcode
        $length = '8';
        $string = rand(10,100);
        $original_string = array_merge(range(0,9), range('a','z'), range('A', 'Z'));
        $original_string = implode("", $original_string);
        $string1=  substr(str_shuffle($original_string), 0, $length);
        $randomCode = $string1.$string;  
        $dataCol =  array(
  			"name"                          => $_POST['name'],
  			"survey_needed"                 => $_POST['survey_needed'],
  			// "clientid"                   => $_POST['clientid'],
            // "adminid"                    => $_POST['adminid'],
            // "user_type"                  => $_POST['user_type'],
            "survey_type"                   => $_POST['survey_type'],
            "intervals"                     => $_POST['interval'],
            "start_date"                    => $_POST['sdate'],
            "end_date"                      => $_POST['edate'],
            "question_limit"                => $_POST['question_limit'],
            "qrcode"                        => $randomCode,
            "confidential"                  => (isset($_POST['confidential'])) ? 1 : 0,
            "alter_email"                   => $_POST['alter_email'],
            "contact_requested"             => $_POST['contact_requested'],
           // "isStep"                        => (isset($_POST['isStep'])) ? 1 : 0,
            "isStep"                        => 1,
            "isEnableContacted"             => (isset($_POST['isEnableContacted'])) ? 1 : 0,
            "contacted_request_label"       => $_POST['contacted_request_label'],
            "notification_threshold"        => (isset($_POST['notification_threshold'])) ? 1 : 0,
            "select_percentage"             => $_POST['select_percentage'],
            "notification_threshold_users"  => implode(",",$_POST['notification_threshold_users']),
            //"isSchoolAllowed"             => (isset($_POST['isSchoolAllowed'])) ? 1 : 0,
  			"css_txt"                       => $_POST['css_txt'],
  			"cstatus"                       => $_POST['status'],
  			"cip"                           => ipAddress(),
            "cby"                           => $_SESSION['user_id'],
            "send_by"                       => $_POST['send_by'],
            "groups"                        => implode(",",$_POST['groupid']),
            "locations"                     => implode(",",$_POST['locationid']),
            "departments"                   => implode(",",$_POST['departments']),
            "roles"                         => implode(",",$_POST['roles']),
  			"cdate"                         => date("Y-m-d H:i:s"),
            "google_review_link"            => $_POST['google_review_link'],
            "facebook_review_link"          => $_POST['facebook_review_link'],
            "other_link"                    => $_POST['other_link'],
  		);

        $mailing_user_ids = $_POST['mailing_user_id'];
        $contact_requested = $_POST['contact_requested'];
        $is_pdf = isset($_POST['is_pdf']) ? $_POST['is_pdf'] : [];
        

        $insert_value =  dbRowInsert("surveys",$dataCol);

        if(!empty($insert_value)){	
            //Insert Survey Steps
            $numberOfStep = (isset($_POST['numberOfStep']) && !empty($_POST['numberOfStep'])) ?  $_POST['numberOfStep'] : 1;
            //if(isset($_POST['isStep'])){
                for($step = 0; $step < $numberOfStep; $step++){
                    if($numberOfStep == 1){
                        $step_title = 'Step 1';
                    }else{
                        $step_title = $_POST['stepstitle'][$step];

                    }
                    $step_data_col = array("survey_id" => $insert_value, "step_number" => $step+1, "step_title" => $step_title, 'cby'=> $_SESSION['user_id'], 'cdate'=> date("Y-m-d H:i:s"));
                    $insert_steps =  dbRowInsert("surveys_steps",$step_data_col);
                }
            //}

            // insert user mails for notification
            if(is_array($mailing_user_ids) && count($mailing_user_ids) > 0 && is_array($contact_requested) && count($contact_requested) > 0){
                foreach ($mailing_user_ids as $key => $user_id){
                    $is_pdf_value = isset($is_pdf[$key]) && $is_pdf[$key] == 'on' ? 1 : 0;
                    $userMailData = array("survey_id" => $insert_value, "user_id" => $user_id, "is_contact_requested" => $contact_requested[$key], "is_pdf" => $is_pdf_value);
                    $insert_steps =  dbRowInsert("surveys_mailing_users",$userMailData);
                }
            }
            
  	        $msg = "Survey Added Successfully";
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

$allUsers = getUsers();

?>


<section class="content-header">
    <h1> <?=($_GET['id'])?'EDIT SURVEY':'ADD SURVEY'?></h1>
    <a href="?page=view-survey" class="btn btn-danger pull-right" style="margin-top:-25px">Cancel</a> 
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
        #alter_email-error{
            position:absolute;
        }

        /**
        * Error color for the validation plugin
        */

        .error {
        color: #e74c3c;
        }
        span.select2.select2-container.select2-container--default {
            width: 100% !important;
        }
    </style>
<section class="content">
    <div class="box box-secondary">
        <?php if(isset($_GET['msg'])){ ?>
			<div class="alert alert-success" role="alert">
				<?php echo $_GET['msg']; ?>
			</div>
		<?php } ?>
        <div class="row">
            <div class="col-md-12">
                <!-- <div class="box-header"><i class="fa fa-edit"></i>Input</div> -->
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
                            <div class="col-md-6">
                                <label>Question Limit *</label>
                                <input type="number" class="form-control" name="question_limit" id="question_limit" value="<?php echo $row_get_surveys['question_limit'];?>" min="<?=($totalRows_get_question > 0 ) ? $totalRows_get_question : 1 ?>" required/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Survey Name *</label>
                                    <input type="text" class="form-control" name="name" id="name" value="<?php echo $row_get_surveys['name'];?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Response Limit</label>
                                    <input type="text" class="form-control" name="survey_needed" id="survey_needed" min="1" max="2147483646" value="<?php echo $row_get_surveys['survey_needed'];?>"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status" <?=($_GET['id'] and $_SESSION['user_type']>2) ? 'disabled ': ''?>>
                                        <?php foreach(status() as $key => $value){ ?>
                                            <option <?php if($row_get_surveys['cstatus']==$key){?> selected="selected"<?php }?> value="<?php echo $key; ?>"><?php echo $value;?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Interval</label>
                                    <select class="form-control" id="interval" name="interval">
                                        <?php foreach(service_type() as $key => $value){ ?>
                                            <option <?php if($row_get_surveys['intervals']==$key){?> selected="selected"<?php }?> value="<?php echo $key; ?>"><?php echo $value;?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date *</label>
                                    <input type="date" class="form-control" name="sdate" id="sdate" value="<?=($_GET['id'] and $row_get_surveys['start_date'])?date('Y-m-d',strtotime($row_get_surveys['start_date'])):''?>" required/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" class="form-control" min="" name="edate" id="edate" value="<?=($_GET['id'] and $row_get_surveys['end_date'])?date('Y-m-d',strtotime($row_get_surveys['end_date'])):''?>"/>
                                </div>
                            </div>
                            <?php 
                            ?>
                            <div class="col-md-6 dropdwn ">
                                <label>Group</label>
                                <input type="checkbox"id="allgrp" class="multiselect" onchange="select_all_option('allgrp','group_id')">
                                <select name="groupid[]" id="group_id" class="form-control form-control-lg multiple-select" multiple=multiple>
                                    <?php 
                                    $groups = $row_get_surveys['groups'];
                                    $groupId = explode(',',$groups);
                                    $getGroup = getGroup(); 
                                    if($_SESSION['user_type']>2){
                                        $assignGroupId = get_assigned_user_data($_SESSION['user_id'],'group');
                                        if(count($assignGroupId)>0){
                                            $array =[];
                                            foreach($getGroup as $key=> $value){
                                            if(in_array($key,$assignGroupId)){
                                                $array[$key] =$value;
                                            }
                                            }
                                            $getGroup = $array;
                                        }else{
                                            $getGroup = []; 
                                        }
                                    }
                                    foreach($getGroup as $key => $value){ 
                                        $selected = '';
                                        if(in_array($key,$groupId)){
                                            $selected ='selected';
                                        }
                                        ?>
                                        <option value="<?=$key?>" <?=$selected?>> <?=$value?> </option> 
                                    <?php } ?>                
                                </select>	
                            </div>
                            <div class="col-md-6 dropdwn ">
                                <div><label>Location</label>
                                <?php 
                                 $locations = $row_get_surveys['locations'];
                                 $locationsId = explode(',',$locations);
                                 $getLocation = getLocation();
                                 if($_SESSION['user_type']>2){
                                    $assignLocationId = get_assigned_user_data($_SESSION['user_id'],'location');
                                    if(count($assignLocationId)>0){
                                        $array =[];
                                        foreach($getLocation as $key=> $value){
                                        if(in_array($key,$assignLocationId)){
                                            $array[$key] =$value;
                                        }
                                        }
                                        $getLocation = $array;
                                    }else{
                                        $getLocation = []; 
                                    }
                                }
                                ?>
                                <input type="checkbox" id="allLoc" class="multiselect" onchange="select_all_option('allLoc','location_id')">
                            </div>

                                <select name="locationid[]" id="location_id" class="form-control form-control-lg multiple-select" multiple=multiple>
                                     <?php 
                                     foreach($getLocation as $key => $value){ 
                                        $selected = '';
                                        if(in_array($key , $locationsId)){
                                            $selected ='selected';
                                        }
                                        ?>
                                        <option value="<?=$key?>" <?=$selected?>><?=$value?></option>
                                    <?php } ?>   
                                </select>	
                            </div>
                            <div class="col-md-6 dropdwn ">
                                <label>Department</label>
                                <input type="checkbox"id="alldep" class="multiselect" onchange="select_all_option('alldep','departments')">
                                <select name="departments[]" id="departments" class="form-control form-control-lg multiple-select" multiple=multiple>
                                <?php 
                                  $department = $row_get_surveys['departments'];
                                  $departmentId = explode(',',$department);
                                  $getDeparment = getDepartment();
                                    if($_SESSION['user_type']>2){
                                        $assignDeparmentId = get_assigned_user_data($_SESSION['user_id'],'department');
                                        if(count($assignDeparmentId)>0){
                                            $array =[];
                                            foreach($getDeparment as $key=> $value){
                                            if(in_array($key,$assignDeparmentId)){
                                                $array[$key] =$value;
                                            }
                                            }
                                            $getDeparment = $array;
                                        }else{
                                            $getDeparment = []; 
                                        }
                                    }
                                    foreach($getDeparment as $key => $value){ 
                                        $selected = '';
                                        if(in_array($key,$departmentId)){
                                            $selected = 'selected';
                                        }
                                    ?>
                                    <option value="<?=$key?>" <?=$selected ?>><?=$value?></option>
                                <?php } ?> 
                                </select>	
                            </div>
                            <div class="col-md-6 dropdwn ">
                                <label>Role</label>
                                <input type="checkbox"id="allrole" class="multiselect" onchange="select_all_option('allrole','roles')">
                                <select name="roles[]" id="roles" class="form-control form-control-lg multiple-select" multiple=multiple>
                                <?php 
                                  $role = $row_get_surveys['roles'];
                                  $role = $row_get_surveys['roles'];
                                  $roleId = explode(',',$role);
                                    $getRole = getRole();  
                                    if($_SESSION['user_type']>2){
                                        $assignRoleId = get_assigned_user_data($_SESSION['user_id'],'role');
                                        if(count($assignRoleId)>0){
                                            $array =[];
                                            foreach($getRole as $key=> $value){
                                            if(in_array($key,$assignRoleId)){
                                                $array[$key] =$value;
                                            }
                                            }
                                            $getRole = $array;
                                        }else{
                                            $getRole = []; 
                                        }
                                    }  
                                    foreach($getRole as $key => $value){ 
                                        $selected = '';
                                        if(in_array($key,$roleId)){
                                            $selected = 'selected';
                                        }
                                    ?>
                                    <option value="<?=$key?>" <?=$selected?>><?=$value?></option>
                                <?php } ?> 
                                </select>	
                            </div>
                            <div class="col-md-12 contacted_request_section" style="<?=$row_get_surveys['isEnableContacted'] == 1 ? 'display:block;':'display:none;'?>">
                                <label for="">Enter the label for contact request *</label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="contacted_request_label" name="contacted_request_label" value="<?=$row_get_surveys['contacted_request_label']?>" required/>
                                    </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="isEnableContacted" name="isEnableContacted" <?php echo ($row_get_surveys['isEnableContacted'] == 1) ? "checked" : ""; ?> <?=$totalRows_isSurveyRelatedToAssignTask > 0 ? 'disabled':''?>>
                                                <label class="form-check-label" for="isEnableContacted"> Enable Contact Requests </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="confidential" name="confidential" <?php echo ($row_get_surveys['confidential'] == 1) ? "checked" : ""; ?>>
                                                <label class="form-check-label" for="confidencial"> Confidential </label>
                                            </div>
                                        </div>
                                    </div> 
                                </div>           
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="threshold-notification" name="notification_threshold" <?php echo ($row_get_surveys['notification_threshold'] == 1) ? "checked" : ""; ?>>
                                            <label class="form-check-label" for="threshold-notification">
                                                Set Notification Threshold
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 threshold-notification-section" style="<?=($row_get_surveys['notification_threshold'] ==1)? 'display:block':'display:none'?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Percentage</label>
                                            <input type=number min=0 max=100 class="form-control" id="select_percentage" name="select_percentage" value="<?=$row_get_surveys['select_percentage']?>"   placeholder="insert percentage">
                                                <?php 
                                                // $thresholdPercentage = getThresholdPercentage();
                                                ?>
                                        </div>
                                    </div> 
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Select Users</label>
                                            <select class="form-control multiple-select" id="select_users" name="notification_threshold_users[]" multiple>
                                                <?php 
                                                    $users = getUsers();
                                                    $thresholdUser = explode(',',$row_get_surveys['notification_threshold_users']);
                                                    foreach($users as $key => $value){ 
                                                        $selected = '';
                                                        if (in_array($key, $thresholdUser)){
                                                            $selected = 'selected';
                                                        }
                                                    ?>
                                                    <option value="<?=$key?>" <?= $selected?>><?=$value?></option>      
                                                <?php  }
                                            ?>
                                            </select>
                                        </div>
                                    </div> 
                                </div> 
                            </div>
                            <?php 
                                record_set("get_surveys_steps", "select * from surveys_steps where survey_id='".$_GET['id']."'");
                                $old_steps_titles = "testing";
                            ?>
                            <div class="col-md-12" style="padding-top:20px;">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="isStep" name="isStep" <?php echo ($row_get_surveys['isStep'] == 1 && $totalRows_get_surveys_steps >1) ? "checked" : ""; ?>>
                                    <label class="form-check-label" for="isStep"> Will have steps </label>
                                </div>
                            </div>
                          
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
                            <!-- <div class="col-md-12">
                                <h5><strong>Alert Email</strong></h5>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Email">Email</label>
                                        <input type="email" class="form-control" id="alert-email" name="alert-email" placeholder="Enter Email" value="">
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-12" style="margin-bottom: 12px;">
                                <p style="margin:15px 5px 6px 0px !important"><strong>Send By</strong></p>
                                <div class="row">
                                    <div class="col-md-1">
                                        <input type="radio" id="send_by" class="send_by" name="send_by" value="1" <?=($row_get_surveys['send_by']==1)?'checked':''?>>  <strong> Text</strong>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="radio"id="send_by" name="send_by" class="send_by" value="2" <?=($row_get_surveys['send_by']==2)?'checked':''?>> <strong> Email</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="new-content-main">
                                    <div class="contact-request-div">
                                        <div class="row">
                                            <div class="col-xs-8 col-sm-8 col-md-8">
                                                <div class="form-group gap-rm">
                                                    <label>Alert Email</label>
                                                </div>
                                            </div>
                                            <div class="col-xs-4 col-sm-4 col-md-4">
                                                <div class="form-group gap-rm">
                                                    <label>Contact Requested ?</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-8 col-sm-8 col-md-8">
                                                <div class="form-group" style="margin-bottom: 0;">
                                                    <label>Email</label>
                                                </div>
                                            </div>
                                            <div class="col-xs-4 col-sm-4 col-md-4">
                                                <div class="row">
                                                    <div class="col-xs-3 col-sm-3 col-md-3">
                                                        <div class="form-group" style="margin-bottom: 0;">
                                                            <label>Yes</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-3 col-sm-3 col-md-3">
                                                        <div class="form-group" style="margin-bottom: 0;">
                                                            <label>No</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-3 col-sm-3 col-md-3">
                                                        <div class="form-group" style="margin-bottom: 0;">
                                                            <label>PDF</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-sm-6 col-md-6 text-right">
                                                        <div class="form-group" style="margin-bottom: 0;">
                                                            <label></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php

                                        if(isset($_GET['id']) && $_GET['id'] > 0 && $totalRows_get_mailing_users > 0){ 
                                            while ($row_mailing_user = mysqli_fetch_assoc($get_mailing_users)) {
                                                $mailing_user_id = $row_mailing_user['user_id'];
                                            ?>
                                            <div class="row new-row">
                                            <div class="col-xs-8 col-sm-8 col-md-8">
                                                <div class="form-group">
                                                <select class="form-control mailing_users" name="mailing_user_id[]" id="mailing_user_id">
                                                    <option value=""> Select Email</option>
                                                    <?php foreach($allUsers as $key => $userName) { ?>
                                                    <option value="<?=$key?>" <?=($row_mailing_user['user_id']==$key)? 'selected' : '' ?>><?=$userName?></option>
                                                    <?php } ?>
                                                </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-4 col-sm-4 col-md-4">
                                                <div class="row ">
                                                    <div class="col-xs-3 col-sm-3 col-md-3">
                                                        <div class="form-group" style="margin-bottom: 0;margin-top: 4px;">
                                                            <input style="zoom: 2;margin: 0;" class="form-check-input contacted-checkbox" name="contact_requested[]" type="checkbox" value="1" id="flexCheckDisabled" <?=($row_mailing_user['is_contact_requested'] == 1) ? 'checked' : ''?>>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-3 col-sm-3 col-md-3">
                                                        <div class="form-group" style="margin-bottom: 0;margin-top: 4px;">
                                                            <input style="zoom: 2;margin: 0;" class="form-check-input contacted-checkbox" name="contact_requested[]" type="checkbox" value="2" id="flexCheckCheckedDisabled" <?=($row_mailing_user['is_contact_requested'] == 2) ? 'checked' : ''?>>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-3 col-sm-3 col-md-3">
                                                        <div class="form-group" style="margin-bottom: 0; margin-top: 4px;">
                                                            <input 
                                                                style="zoom: 2; margin: 0;" 
                                                                class="form-check-input pdf-checkbox" 
                                                                name="is_pdf[<?= $mailing_user_id ?>]" 
                                                                type="checkbox" 
                                                                value="<?= ($row_mailing_user['is_pdf'] == 1) ? 1 : 0 ?>" 
                                                                id="is_pdf_<?= $mailing_user_id ?>" 
                                                                <?= ($row_mailing_user['is_pdf'] == 1) ? 'checked' : '' ?>
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } } else{?>
                                        <div class="row new-row">
                                            <div class="col-xs-8 col-sm-8 col-md-8">
                                                <div class="form-group">
                                                <select class="form-control mailing_users" name="mailing_user_id[]" id="mailing_user_id">
                                                    <option value="">Select Email</option>
                                                    <?php foreach($allUsers as $key => $userName) { ?>
                                                    <option value="<?=$key?>" ><?=$userName?></option>
                                                    <?php } ?>
                                                </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-4 col-sm-4 col-md-4">
                                                <div class="row ">
                                                    <div class="col-xs-3 col-sm-3 col-md-3">
                                                        <div class="form-group" style="margin-bottom: 0;margin-top: 4px;">
                                                            <input style="zoom: 2;margin: 0;" class="form-check-input contacted-checkbox" name="contact_requested[]" type="checkbox" value="1" id="flexCheckDisabled" >
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-3 col-sm-3 col-md-3">
                                                        <div class="form-group" style="margin-bottom: 0;margin-top: 4px;">
                                                            <input style="zoom: 2;margin: 0;" class="form-check-input contacted-checkbox" name="contact_requested[]" type="checkbox" value="2" id="flexCheckCheckedDisabled" >
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-3 col-sm-3 col-md-3">
                                                        <div class="form-group" style="margin-bottom: 0; margin-top: 4px;">
                                                            <input 
                                                                style="zoom: 2; margin: 0;" 
                                                                class="form-check-input pdf-checkbox" 
                                                                name="is_pdf[]" 
                                                                type="checkbox"
                                                                id="is_pdf_new"
                                                            >
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>                             
                                    </div>
                                    <?php if($_SESSION['user_type'] < 3) { ?>
                                    <div class="row ">
                                        <div class="col-md-12 text-right" style="margin-bottom: 20px;">
                                            <span id="add-row" class="btn btn-primary">Add Row</span>
                                        </div>
                                    </div>
                                    <?php } ?>

                                </div>
                            </div>
                            
                            
                            <?php if($_SESSION['user_type'] != 2) { ?>
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
                            <?php } ?>         
                            <!-- Start submit button -->
                            <div class="col-md-12">
                                <div class="text-right">
                                    <span class="text-danger"><?php echo $_GET['msg']; ?></span> &nbsp;
                                    <?php if(empty($_GET['id'])){ ?>
                                        <input type="Submit" class="btn btn-primary" value="Create" name="submit" id="submit" style="margin-top:24px"/>
                                    <?php }else{ 
                                        if($_SESSION['user_type']<=2){ ?>
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

    $("#numberOfStep").on('keyup change', function() {
      $("#stepsTitle").html("");
      var numberOfSteps = $(this).val();
      if(numberOfSteps>50){
        alert('Step size can not be greater than 50');
        return;
      }
      var i;
      var html = "";
      var intial = 1;
      if(numberOfSteps != "" && numberOfSteps > 0){
        var intial = "<?php echo ($totalRows_get_surveys_steps > 0) ? $totalRows_get_surveys_steps+1 : 1; ?>";
      }
      for(i=intial; i <= numberOfSteps; i++){
        html += '<div class="col-md-12"><div class="form-group"><label>Step '+i+' Title</label><input type="text" class="form-control step_checkbox" id="stepTitle'+i+'" name="stepstitle[]"></div></div>';
      }
      $("#stepsTitle").html(html);
      $(".step_checkbox").attr("required", true);
    });
    // Start js according to language

    // End js according to language
		$('.form-control').click(function(){
			$(this).css("border-color", "#ccc");
		});
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
<?php if($_GET['id'] and $_SESSION['user_type'] >1){ ?>
    $("#survey_from :input").prop("disabled", true);
    <?php if($_GET['id'] and $_SESSION['user_type'] ==2){ ?>
        $(".multiple-select").prop("disabled", false);
        $(".multiselect").prop("disabled", false);
        $("#survey_from :submit").prop("disabled", false);
        $("#threshold-notification").prop("disabled", false);
        $("#select_percentage").prop("disabled", false);
        $("#select_users").prop("disabled", false);
        $(".mailing_users").prop("disabled", false);
        $(".contacted-checkbox").prop("disabled", false);
        $(".pdf-checkbox").prop("disabled", false);

<?php } } ?>

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

$('#sdate').change(function(){
    let sdate = $(this).val();
    $("#edate").attr("min", sdate);
});

$(document).ready(function(){
    $('#group_id').change(function () {
        let group_id = $(this).val();
        let type     = 'group';
        //load_location(group_id,type);
    });
    $('#location_id').change(function () {
        let location_id = $(this).val();
        let type     = 'location';
        //load_location(location_id,type);
    });
    $('#departments').change(function () {
        let department_id = $(this).val();
        console.log("department_id", department_id);
        let type     = 'department';
        //load_location(department_id,type);
    });
})

function load_location(ids,mode){
    $.ajax({
        type: "POST",
        url: 'ajax/common_file.php',
        data: {id: ids,mode:mode}, 
        success: function(response){
            console.log(response);
            if(mode == 'group'){
                $('#location_id').html(response);
                $('#departments').html('');
            }else if(mode == 'location'){
                $('#departments').html(response);
            }else if(mode == 'department'){
                $('#roles').html(response);
            }
           
        }
    });
}

$('.send_by').change(function(){
    $('.send_by').prop('checked', false);
    $(this).prop('checked', true);
})

$("#isEnableContacted").change(function(){
    if ($(this).is(":checked")) {
        let isValue = $("#contacted_request_label").val();
        console.log(isValue,'asdds');
        if(isValue !=''){
            console.log(isValue,'asdds2');
            $("#contacted_request_label").prop("required", false);
        }else{
            console.log(isValue,'asdds3');
            $("#contacted_request_label").prop("required", true);
        }
        $(".contacted_request_section").show();
    } else {
        $(".contacted_request_section").hide();
        $("#contacted_request_label").prop("required", false);
    }
})

$("#threshold-notification").change(function(){
    if($(this).is(":checked")){
        $(".threshold-notification-section").show();
        $("#select_percentage").prop("required", true);
        $("#select_users").prop("required", true);
    }else{
        $(".threshold-notification-section").hide();
        $("#select_percentage").prop("required", false);
        $("#select_users").prop("required", false);
    }
})

$(document).on('change','.contacted-checkbox', function(){
    isChecked = $(this).is(':checked');
     if(isChecked){
        $(this).parents().closest('.new-row').find('.contacted-checkbox').prop('checked',false);
        $(this).prop('checked', true);
    }else{
        $(this).parents().closest('.new-row').find('.contacted-checkbox').prop('checked',false);
        $(this).prop('checked', false);
    }
});

$('#select_percentage').keyup(function(){
  if ($(this).val() < 1 || $(this).val() > 100){
    $(this).val(1);
  }
});

$(document).on('click','.delete-row',function(){
    $(this).parents().closest('.new-row').remove();
})

$("#add-row").click(function(){
        let selectBoxes = $('.mailing_users');
        let selectedUserIdsArr = [];
        let isValue = $("#mailing_user_id").find(":selected").val();
        if(isValue == ''){
            alert("Please select the Email");
        }
        selectBoxes.each(function() {
            let selectBox = $(this);
            let selected = selectBox.find('option:selected');
            selected.each(function() {
                let optionValue = $(this).val();
                selectedUserIdsArr.push(optionValue);
            });
        });
    $.ajax({
        type: "POST",
        url: 'ajax/common_file.php',
        data: {
			mode:'add_edit_survey_form',
            excludedUserIds:selectedUserIdsArr,
		}, 
        success: function(responseHtml){
			if(responseHtml !=''){
                $('.contact-request-div').append(responseHtml);
			} 
        }
    });
});


$(document).on('click', '.mailing_users', function() {
    let currentIndex = $(this).parents('.new-row').closest('.new-row').index();
    $(this).parents('.new-row').nextAll('.new-row').remove();
}) ;

</script>