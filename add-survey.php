
<?php 
    if(!empty($_GET['id'])){
        record_set("get_surveys", "select * from surveys where id='".$_GET['id']."'");
        $row_get_surveys = mysqli_fetch_assoc($get_surveys);
    }
    if($_POST['update']){
        $dataCol = array(
            "name"                 => $_POST['name'],
            "survey_type"          => $_POST['survey_type'],
            "survey_needed"        => $_POST['survey_needed'],
            "cstatus"              => $_POST['status'],
            "confidential"         => (isset($_POST['confidential'])) ? 1 : 0,
            "intervals"            => $_POST['interval'],
            "css_txt"              => $_POST['css_txt'],
            //"send_by"              => $_POST['send_by'],
            "alter_email"          => $_POST['alter_email'],
            "start_date"           => $_POST['sdate'],
            "end_date"             => $_POST['edate'],
             "isStep"               => (isset($_POST['isStep'])) ? 1 : 0,
             "isEnableContacted"    => (isset($_POST['isEnableContacted'])) ? 1 : 0,
            "google_review_link"   => $_POST['google_review_link'],
            "facebook_review_link" => $_POST['facebook_review_link'],
            "other_link"           => $_POST['other_link'],
        );

        // avoid updating disable value
        $new_data =  array(
            "groups"        => implode(",",$_POST['groupid']),
            "locations"     => implode(",",$_POST['locationid']),
            "departments"   => implode(",",$_POST['departments']),
            //"isStep"             => (isset($_POST['isStep'])) ? 1 : 0,
            //"isEnableContacted"  => (isset($_POST['isEnableContacted'])) ? 1 : 0,
        );
        if($_SESSION['user_type']==1){
            $data = array_merge($dataCol,$new_data);
        }else if($_SESSION['user_type']==2) {
            $data = $new_data;
        }
        $updte=	dbRowUpdate("surveys", $data, "where id=".$_GET['id']);		
        if(!empty($updte)){
            if(isset($_POST['isStep']) && isset($_POST['numberOfStep'])){
                for($step = 0; $step < $_POST['numberOfStep']; $step++){
                    record_set("get_old_steps", "select * from surveys_steps where survey_id='".$_GET['id']."' and step_number='".intval($step+1)."'");
                    if($totalRows_get_old_steps > 0){   
                        $step_data = array("step_title" => $_POST['stepstitle'][$step]);
                       
                        $updte_steps= dbRowUpdate("surveys_steps", $step_data, "where survey_id=".$_GET['id']." and step_number=".intval($step+1));
                    }else{
                        $step_data = array("survey_id" => $_GET['id'], "step_number" => $step+1, "step_title" => $_POST['stepstitle'][$step],'cby'=> $_SESSION['user_id'], 'cdate'=> date("Y-m-d H:i:s"));
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
        //get qrcode
        $length = '8';
        $string = rand(10,100);
        $original_string = array_merge(range(0,9), range('a','z'), range('A', 'Z'));
        $original_string = implode("", $original_string);
        $string1=  substr(str_shuffle($original_string), 0, $length);
        $randomCode = $string1.$string;  
       	
        $dataCol =  array(
  			"name"                   => $_POST['name'],
  			"survey_needed"          => $_POST['survey_needed'],
  			// "clientid"               => $_POST['clientid'],
            // "adminid"                => $_POST['adminid'],
            // "user_type"              => $_POST['user_type'],
            "survey_type"            => $_POST['survey_type'],
            "intervals"              => $_POST['interval'],
            "start_date"             => $_POST['sdate'],
            "end_date"               => $_POST['edate'],
            "qrcode"                 => $randomCode,
            "confidential"           => (isset($_POST['confidential'])) ? 1 : 0,
            "alter_email"            => $_POST['alter_email'],
            "isStep"                 => (isset($_POST['isStep'])) ? 1 : 0,
            "isEnableContacted"      => (isset($_POST['isEnableContacted'])) ? 1 : 0,
            //"isSchoolAllowed"        => (isset($_POST['isSchoolAllowed'])) ? 1 : 0,
  			"css_txt"                => $_POST['css_txt'],
  			"cstatus"                => $_POST['status'],
  			"cip"                    => ipAddress(),
            "cby"                    => $_SESSION['user_id'],
            "send_by"                => $_POST['send_by'],
            "groups"                 => implode(",",$_POST['groupid']),
            "locations"              => implode(",",$_POST['locationid']),
            "departments"            => implode(",",$_POST['departments']),
  			"cdate"                  => date("Y-m-d H:i:s"),
            "google_review_link"     => $_POST['google_review_link'],
            "facebook_review_link"   => $_POST['facebook_review_link'],
            "other_link"             => $_POST['other_link'],
  		);
 	
        $insert_value =  dbRowInsert("surveys",$dataCol);

        if(!empty($insert_value)){	
            //Insert Survey Steps
            if(isset($_POST['isStep']) && isset($_POST['numberOfStep'])){
                for($step = 0; $step < $_POST['numberOfStep']; $step++){
                    $step_data_col = array("survey_id" => $insert_value, "step_number" => $step+1, "step_title" => $_POST['stepstitle'][$step], 'cby'=> $_SESSION['user_id'], 'cdate'=> date("Y-m-d H:i:s"));
                    $insert_steps =  dbRowInsert("surveys_steps",$step_data_col);
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
    <h1> <?=($_GET['id'])?'EDIT SURVEY':'ADD SURVEY'?></h1>
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
                                <p style="margin:15px 5px 20px 0px !important"><strong>Seen By</strong></p>
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

    $("#numberOfStep").keyup(function() {
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
        load_location(group_id,type);
    });
    $('#location_id').change(function () {
        let location_id = $(this).val();
        let type     = 'location';
        load_location(location_id,type);
    });
})
function load_location(ids,mode){
    $.ajax({
        type: "POST",
        url: 'ajax/common_file.php',
        data: {id: ids,mode:mode}, 
        success: function(response){
            if(mode == 'group'){
                $('#location_id').html(response);
                $('#departments').html('');
            }else if(mode == 'location'){
                $('#departments').html(response);
            }
           
        }
    });
}
$('.send_by').change(function(){
    $('.send_by').prop('checked', false);
    $(this).prop('checked', true);
})
</script>