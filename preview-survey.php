
<?php 
    if(!empty($_GET['id'])){
        record_set("get_surveys", "select * from surveys where id='".$_GET['id']."'");
        $row_get_surveys = mysqli_fetch_assoc($get_surveys);
    }
// get data by user
$departmentByUsers = get_filter_data_by_user('departments');
$locationByUsers   = get_filter_data_by_user('locations');
$groupByUsers      = get_filter_data_by_user('groups');  
?>


<section class="content-header">
    <h1>Preview</h1>
    <!-- <a href="?page=view-survey" class="btn btn-primary pull-right" style="margin-top:-25px">View Survey</a>  -->
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
                                    <input type="text" class="form-control" name="survey_needed" id="survey_needed" value="<?php echo $row_get_surveys['survey_needed'];?>"/>
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
                                 <?php 

                                 $location = $row_get_surveys['locations'];
                                 $locationId = get_data_by_id('locations',$location);
                                ?>
                                <input type="checkbox" id="allLoc" class="multiselect" onchange="select_all_option('allLoc','location_id')">
                            </div>

                                <select name="locationid[]" id="location_id" class="form-control form-control-lg multiple-select" multiple=multiple>
                                     <?php 
                                     foreach($locationId as $key => $value){ ?>
                                        <option value="<?=$key?>" selected><?=$value?></option>
                                    <?php } ?>   
                                </select>	
                            </div>
                            <div class="col-md-6 dropdwn ">
                                <label>Department</label>
                                <input type="checkbox"id="alldep" class="multiselect" onchange="select_all_option('alldep','departments')">
                                <select name="departments[]" id="departments" class="form-control form-control-lg multiple-select" multiple=multiple>
                                <?php 
                                  $department = $row_get_surveys['departments'];
                                  $departmentId = get_data_by_id('departments',$department);
                                    foreach($departmentId as $key => $value){ ?>
                                    <option value="<?=$key?>" selected><?=$value?></option>
                                <?php } ?> 
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
                                            <label class="form-check-label" for="confidencial"> Confidential </label>
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
                            <div class="col-md-12">
                                <p style="margin:15px 5px 20px 0px !important"><strong>Send By</strong></p>
                                <div class="col-md-1" style="padding-left: 0px;">
                                    <input type="radio" id="send_by" class="send_by" name="send_by" value="1" <?=($row_get_surveys['send_by']==1)?'checked':''?>>  <strong> Text</strong>
                                </div>
                                <div class="col-md-1">
                                    <input type="radio"id="send_by" name="send_by" class="send_by" value="2" <?=($row_get_surveys['send_by']==2)?'checked':''?>> <strong> Email</strong>
                                </div>
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
// for preview survey
$("#survey_from :input").prop("disabled", true);
$(".multiple-select").prop("disabled", true);
$("#update").remove();

</script>