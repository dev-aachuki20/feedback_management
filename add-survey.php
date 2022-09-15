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
            "cstatus"              => $_POST['status'],
            "locationid"           => $_POST['locationid'],
            "groupid"              => $_POST['groupid'],
            "isStep"               => (isset($_POST['isStep'])) ? 1 : 0,
            "isEnableContacted"    => (isset($_POST['isEnableContacted'])) ? 1 : 0,
            "isSchoolAllowed"      => (isset($_POST['isSchoolAllowed'])) ? 1 : 0,
            "language"             => implode(",",$_POST['langid']),
            "locations"            => implode(",",$_POST['locationid']),
            "groups"               => implode(",",$_POST['groupid']),
            "departments"          => implode(",",$_POST['departments']),
            "google_review_link"   => $_POST['google_review_link'],
            "facebook_review_link" => $_POST['facebook_review_link'],
            "other_link"           => $_POST['other_link'],
        );

        $lang_col=array();
        record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
        while($row_get_language = mysqli_fetch_assoc($get_language)){	
            if($row_get_language['id'] !=1){
                $lang_col["name_".$row_get_language['iso_code']] = $_POST['name_'.$row_get_language['iso_code']];
            }
        }
        // merge two array
        $data = array_merge($dataCol,$lang_col);
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
            alertSuccess($msg,'?page=add-survey&id='.$_GET["id"]);
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
  			"departmentid"           => (isset($_POST['departmentid']))?$_POST['departmentid']:'',
  			"locationid"             => (isset($_POST['locationid']))?$_POST['locationid']:'',
  			"groups"                => (isset($_POST['groupid']))?$_POST['groupid']:'',
            "alter_email"            => $_POST['alter_email'],
            "isStep"                 => (isset($_POST['isStep'])) ? 1 : 0,
            "isEnableContacted"      => (isset($_POST['isEnableContacted'])) ? 1 : 0,
            "isSchoolAllowed"        => (isset($_POST['isSchoolAllowed'])) ? 1 : 0,
  			"css_txt"                => $_POST['css_txt'],
  			"cstatus"                => $_POST['status'],
  			"cip"                    => ipAddress(),
            "cby"                    => $_SESSION['user_id'],
            "language"               => implode(",",$_POST['langid']),
            "groupid"                => implode(",",$_POST['groupid']),
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
                send_survey_email($_POST['alter_email'],$_POST['name'],$insert_value);
            }
        }else{
            $msg = "Some Error Occourd. Please try again..";
            alertdanger($msg,'?page=add-survey');
  	       
        }
        //reDirect("?page=add-survey&msg=".$msg);		
    }
?>

<section class="content-header">
    <h1> Add Survey</h1>
    <a href="?page=view-survey" class="btn btn-primary pull-right" style="margin-top:-25px">View Survey</a> 
</section>
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
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Language</label>
                                <div class="form-group">
                                    <!-- english default -->
                                    <input type="hidden" name="langid[]" value="1">
                                    <?php 
                                        record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
                                        while($row_get_language = mysqli_fetch_assoc($get_language)){	
                                            if($row_get_language['id'] !=1){
                                    ?>
                                        <input type="checkbox" class="form-check-input" id="lang_<?=$row_get_language['iso_code']?>" name="langid[]" value="<?php echo $row_get_language['id'];?>" <?php echo (in_array($row_get_language['id'], explode(',',$row_get_surveys['language']))) ? "checked" : ""; ?> <?=(!empty($_GET['id']))?'disabled':''?> >
                                        <label class="form-check-label" for="langid" style="margin-left:5px;margin-right:10px;"> <?php echo $row_get_language['name'];?> </label>

                                    <?php  } } ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Survey Name</label>
                                    <input type="text" class="form-control" name="name" id="name" value="<?php echo $row_get_surveys['name'];?>"/>
                                </div>
                            </div>
              
                            <div class="survey-name">
                                <?php
                                    if(!empty($_GET['id'])){
                                        foreach($languages as $lid){
                                            record_set("get_langs", "select * from languages where id='".$lid."' and cby='".$_SESSION['user_id']."'");				
                                            $row_get_langs = mysqli_fetch_assoc($get_langs);	
                                            if($row_get_langs['id'] !=1){
                                ?>
                                    <div class="col-md-6 lang_<?=$row_get_langs['iso_code']?>">
                                        <div class="form-group">
                                            <label>Survey Name - <?=$row_get_langs['name']?></label> 
                                            <input type="text" class="form-control" name="name_<?=$row_get_langs['iso_code']?>" id="name_<?=$row_get_langs['iso_code']?>" value="<?=$row_get_surveys['name_'.$row_get_langs['iso_code']]?>">
                                        </div>
                                    </div>
                                <?php  } } } ?>
                            </div>

                            <?php if(empty($_GET['id'])){?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Client Name</label>
                                        <select class="form-control" name="clientid" id="clientId">
                  	                        <?php
          		                                record_set("get_client", "select * from clients where cby='".$_SESSION['user_id']."'");				
                                                while($row_get_client = mysqli_fetch_assoc($get_client)){
                                            ?>
                                                <option value="<?php echo $row_get_client['id'];?>"><?php echo $row_get_client['name'];?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                            <?php }?>
             
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
                                    <select class="form-control" name="status">
                                        <?php 
                                            foreach(status() as $key => $value){
                                        ?>
                                            <option <?php if($row_get_surveys['cstatus']==$key){?> selected="selected"<?php }?> value="<?php echo $key; ?>"><?php echo $value;?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="isSchoolAllowed" name="isSchoolAllowed" <?php echo ($row_get_surveys['isSchoolAllowed'] == 1) ? "checked" : ""; ?>>
                                        <label class="form-check-label" for="isSchoolAllowed"> School Allow </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="isEnableContacted" name="isEnableContacted" <?php echo ($row_get_surveys['isEnableContacted'] == 1) ? "checked" : ""; ?>>
                                        <label class="form-check-label" for="isEnableContacted"> Enable To Be Contacted </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 dropdwn">
                                <div class="col-md-6">
                                    <?php 
                                        $groupCount = 0;
                                        if(!empty($row_get_surveys['groups'])){
                                            $groups = explode(',',$row_get_surveys['groups']);
                                            foreach($groups as $val){
                                                ++$groupCount;
                                            }
                                        }
                                    ?>
                                    <div class="col-md-12 grplabel dropdwn" style="<?php echo ($row_get_surveys['isSchoolAllowed'] == 1)?'display:none;':'';?>">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="isEnableGroup" name="isEnableGroup" <?php echo ($row_get_surveys['isSchoolAllowed']== 0 && $groupCount>1)?'checked':'';?>>
                                                <label class="form-check-label" for="isEnableGroup"> Group Allow </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 grp dropdwn" style="<?php echo ($row_get_surveys['isSchoolAllowed'] == 1)?'display:none;':'';?>">
                                        <label>Group</label>
                                        <select name="groupid[]" id="group_id" class="form-control form-control-lg "   <?php echo ($groupCount>1)?'multiple=multiple':'';?>>
                                            <option value="">Please select</option>
                                            <?php //if(!empty($row_get_surveys['locations'])){
                                                $groups = explode(',',$row_get_surveys['groups']);
                                                $selected_option_group = array();
                                                if($row_get_surveys['isSchoolAllowed'] == 0){
                                                    foreach($groups as $val){
                                                        $selected_option_group[] = $val;
                                                    }
                                                }
                                            foreach(getGroup() as $key => $value){ ?>
                                                <option value="<?php echo $key;?>" <?php echo (in_array($key, $selected_option_group))? 'selected':''; ?>><?php echo $value;?></option>
                                            <?php }  ?>
                                        </select>	
                                    </div>               
                                </div>
                                <div class="col-md-6">
                                    <?php 
                                        $locationCount = 0;
                                        if(!empty($row_get_surveys['locations'])){
                                            $locations = explode(',',$row_get_surveys['locations']);
                                            foreach($locations as $val){
                                                ++$locationCount;
                                            }
                                        }
                                    ?>
                                    <div class="col-md-12 loclabel" style="<?php echo ($row_get_surveys['isSchoolAllowed'] == 1)?'display:none;':'';?>">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="isEnableLocation" name="isEnableLocation" <?php echo ($row_get_surveys['isSchoolAllowed']== 0 && $locationCount>1)?'checked':'';?>>
                                                <label class="form-check-label" for="isEnableLocation"> Location Allow </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 loc" style="<?php echo ($row_get_surveys['isSchoolAllowed'] == 1)?'display:none;':'';?>">
                                        <label>Location</label>
                                        <select name="locationid[]" id="location_id" class="form-control form-control-lg"   <?php echo ($locationCount>1)?'multiple=multiple':'';?>>
                                            <option value="">Please select</option>
                                            <?php //if(!empty($row_get_surveys['locations'])){
                                                $locations = explode(',',$row_get_surveys['locations']);
                                                $selected_option_location = array();
                                                if($row_get_surveys['isSchoolAllowed'] == 0){
                                                    foreach($locations as $val){
                                                        $selected_option_location[] = $val;
                                                    }
                                                }
                                                record_set("get_location", "select * from locations where cstatus=1 and id != 4 order by name asc");	
                                                // record_set("get_location", "select * from locations where id in(".$row_get_location['id'].") cstatus=1 and id != 4 order by name asc");				  			  
                                                if($totalRows_get_location > 0){
                                                    while($row_get_location = mysqli_fetch_assoc($get_location)){	
                    
                                            ?>
                                                <option value="<?php echo $row_get_location['id'];?>" <?php echo (in_array($row_get_location['id'], $selected_option_location))? 'selected':''; ?>><?php echo $row_get_location['name'];?></option>
                                            <?php } } ?>
                                        </select>	
                                    </div>              
                                </div>
                                <div class="col-md-6">
                                    <?php 
                                        $departmentCount = 0;
                                        if(!empty($row_get_surveys['departments'])){
                                            $departments = explode(',',$row_get_surveys['departments']);
                                            foreach($departments as $val){
                                                ++$departmentCount;
                                            }
                                        }
                                    ?>  
                                    <div class="col-md-12 dropdwn deptlabel" style="<?php echo ($row_get_surveys['isSchoolAllowed'] == 1)?'display:none;':'';?>">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="isEnableDepartment" name="isEnableDepartment" <?php echo ($row_get_surveys['isSchoolAllowed']== 0 && $departmentCount>1)?'checked':'';?>>
                                                <label class="form-check-label" for="isEnableDepartment"> Department Allow </label>
                                            </div>
                                        </div>
                                    </div>  
                                    <div class="col-md-12 dropdwn dept" style="<?php echo ($row_get_surveys['isSchoolAllowed'] == 1)?'display:none;':'';?>">
                                        <label>Department</label>
                                        <select name="departments[]" id="departments" class="form-control form-control-lg " <?php echo ($departmentCount>1)?'multiple=multiple':'';?>>
                                            <option value="">Please select</option>
                                            <?php
                                                $departments = explode(',',$row_get_surveys['departments']);
                                                $selected_option_department = array();
                                                if($row_get_surveys['isSchoolAllowed'] == 0){
                                                    foreach($departments as $val){
                                                        $selected_option_department[] = $val;
                                                    }
                                                }
                                                record_set("get_department", "select * from departments where cstatus=1 and id != 4");				
                                                if($totalRows_get_department > 0){
                                                    while($row_get_department = mysqli_fetch_assoc($get_department)){
                                            ?>
                                                <option value="<?php echo $row_get_department['id'];?>" <?php echo (in_array($row_get_department['id'], $selected_option_department))? 'selected':''; ?>><?php echo $row_get_department['name'];?></option>
                                            <?php  }  } ?>
                                        </select>	
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
                                    <input type="number" class="form-control" id="numberOfStep" name="numberOfStep" placeholder="Number Of Steps" value="<?php echo ($totalRows_get_surveys_steps > 0) ? $totalRows_get_surveys_steps : ""; ?>">
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
                                <?php 
                                    if(!empty($_GET['id'])){
                                        record_set("get_surveys_steps", "select * from surveys_steps where survey_id='".$_GET['id']."'");
                                        if($totalRows_get_surveys_steps > 0){ 
                                            while($row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps)){
                                                foreach($languages as $key=>$val){
                                                    record_set("get_language", "select * from languages where id='".$val."'");				
                                                    $row_get_language = mysqli_fetch_assoc($get_language);
                                                    if($row_get_language['id']!=1){
                                ?>
                                    <div class="col-md-12" id="lang_<?=$row_get_language['iso_code']?>">
                                        <div class="form-group">
                                            <label>Step <?php echo $row_get_surveys_steps['step_number']; ?> Title - <?=$row_get_language['name']?></label>
                                            <input type="text" class="form-control" id="stepTitle<?php echo $row_get_surveys_steps['step_number']; ?>" name="stepstitle_<?=$row_get_language['iso_code']?>[]" value="<?php echo $row_get_surveys_steps['step_title_'.$row_get_language['iso_code']]?>">
                                        </div>
                                    </div>
                                <?php } } } } } ?>
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
                                    <?php }else{?>
                                        <input type="Submit" class="btn btn-primary" value="Update" id="update" name="update" style="margin-top:24px"/>
                                    <?php }?>
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
        html += '<div class="col-md-12"><div class="form-group"><label>Step '+i+' Title</label><input type="text" class="form-control" id="stepTitle'+i+'" name="stepstitle[]"></div></div>';
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
    });

    // Start js according to language
    <?php 
          record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
          while($row_get_language = mysqli_fetch_assoc($get_language)){	
            if($row_get_language['id'] !=1){
            
      ?>

      $('#update').click(function(e){
        $('#lang_<?=$row_get_language['iso_code']?>').removeAttr('disabled');
      });

      
        $('#lang_<?=$row_get_language['iso_code']?>').change(function() {
            if($(this).is(':checked')) {
              // $("#stepsTitle").html("");
              
              var numberOfSteps = $('#numberOfStep').val();
              var i;
              var html = "";
              var intial = 1;

              if(numberOfSteps != "" && numberOfSteps > 0){
                var intial = "<?php echo ($totalRows_get_surveys_steps > 0) ? $totalRows_get_surveys_steps+1 : 1; ?>";
              }

              if($(this).prop('checked') == true){
              
                if($(this).val() == '<?=$row_get_language['id']?>'){

                  for(i=intial; i <= numberOfSteps; i++){
                    html += '<div class="col-md-12" id="lang_<?=$row_get_language['iso_code']?>"><div class="form-group"><label>Step '+i+' Title - <?=$row_get_language['name']?></label><input type="text" class="form-control" id="stepTitle'+i+'" name="stepstitle_<?=$row_get_language['iso_code']?>[]"></div></div>';
                  }

                  
                  <?php 
                  if(!empty($_GET['id'])){
                  record_set("get_surveys_steps", "select * from surveys_steps where survey_id='".$_GET['id']."'");
                 if($totalRows_get_surveys_steps > 0){ 
                  while($row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps)){
                      foreach($languages as $key=>$val){
                        
                        record_set("get_lang_step", "select * from languages where id='".$val."'");				
                        $row_get_lang_step = mysqli_fetch_assoc($get_lang_step);
                        if($row_get_lang_step['id']!=1){
                 ?>
                    html +='<div class="col-md-12" id="lang_<?=$row_get_lang_step['iso_code']?>">'
                      +'<div class="form-group">'
                          +'<label>Step <?php echo $row_get_surveys_steps['step_number']; ?> - <?=$row_get_lang_step['name']?></label>'
                          +'<input type="text" class="form-control" id="stepTitle<?php echo $row_get_surveys_steps['step_number']; ?>" name="stepstitle_<?=$row_get_lang_step['iso_code']?>[]" value="<?php echo $row_get_surveys_steps['step_title_'.$row_get_lang_step['iso_code']]?>">'
                        +'</div>'
                    +'</div>';
                 <?php 
                        }
                      }
                    }
                  }
                }
                ?>


                  $('.survey-name').append('<div class="col-md-6 lang_<?=$row_get_language['iso_code']?>">'
                    +'<div class="form-group">'
                    +'<label>Survey Name - <?=$row_get_language['name']?></label>'
                    +'<input type="text" class="form-control" name="name_<?=$row_get_language['iso_code']?>"' +'id="name_<?=$row_get_language['iso_code']?>" value="<?php echo $row_get_surveys['name_'.$row_get_language['iso_code']]?>"/>'
                    +'</div>'
                  +'</div>');

                }
              }
              
              <?php if(!empty($_GET['id'])){ ?>
                $("#stepsTitle").html(html);
              <?php }else{ ?>
              $("#stepsTitle").append(html);
              <?php } ?>
            }
           
           if($(this).prop("checked") == false){
            // console.log('hello <?php // echo $row_get_language['name']?>');
              $("#stepsTitle").find('#lang_<?=$row_get_language['iso_code']?>').remove();

              $('.survey-name').find('.lang_<?=$row_get_language['iso_code']?>').remove();
            }
        });
      
      <?php 
            } 
          } 
      ?>
  
     
    // End js according to language

		$('.form-control').click(function(){
				$(this).css("border-color", "#ccc");
			});
		
		$("#submit").click(function(){
			
				var name = $("#name").val();
				if(name==''){
					
						document.getElementById('name').style.borderColor = "#ff0000";
						alert('Name field is required');
						document.getElementById('name').focus();
						return false;
					}
			});

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

   
    $('#isEnableLocation').click(function(){
        if($(this).prop("checked") == true){
            $('#location_id').attr('multiple','multiple');
          }
          else if($(this).prop("checked") == false){
            $('#location_id').removeAttr('multiple','multiple');
          }
    });

    $('#isEnableDepartment').click(function(){
        if($(this).prop("checked") == true){
            $('#departments').attr('multiple','multiple');
          }
          else if($(this).prop("checked") == false){
            $('#departments').removeAttr('multiple','multiple');
          }
    });

    $('#isEnableGroup').click(function(){
        if($(this).prop("checked") == true){
            $('#group_id').attr('multiple','multiple');
          }
          else if($(this).prop("checked") == false){
            $('#group_id').removeAttr('multiple','multiple');
          }
    });

    $('#isSchoolAllowed').click(function(){
       if($(this).prop("checked") == true){
          $('.dept').css('display','none');
          $('.loc').css('display','none');
          $('.grp').css('display','none');
          $('.grplabel').css('display','none');
          $('.loclabel').css('display','none');
          $('.deptlabel').css('display','none');

          if($('#isEnableLocation').prop("checked")){
            $('#isEnableLocation').prop('checked', false);
          }

          if($('#isEnableDepartment').prop("checked")){
            $('#isEnableDepartment').prop('checked', false);
          }

        }
        else if($(this).prop("checked") == false){
          $('.dept').css('display','block');
          $('.loc').css('display','block');
          $('.loclabel').css('display','block');
          $('.deptlabel').css('display','block');
          $('.grp').css('display','block');
          $('.grplabel').css('display','block');

          $('#group_id').removeAttr('multiple','multiple');
          $('#location_id').removeAttr('multiple','multiple');
          $('#departments').removeAttr('multiple','multiple');
        }
    });
				
	});
    
</script>