<?php
if(isset($_POST['create_temp'])){
    $temp_id = strtoupper(getName(8));
    $date = date('d-m-Y h:i:s', time()); 
    $i=0;
    if(empty($_POST['report_name_hidden'])){
        alertdanger('Report Name Can not be blank','');
        die();
    }
    // $filter_data = json_decode($_POST['hidden'],1);
    foreach($_POST as $key => $value){
        $data =array();
        if($key == 'locationids'){
            $data = array(
                'cby' 	      => $_SESSION['user_id'],
                'temp_name'   => $_POST['report_name_hidden'],
                'temp_id'	  => $temp_id ,
                'keyword' 	  => 'location_id',
                'created_at'  => $date,
                'value' 	  => implode(',',$value),
                'survey_id'   => ($_POST['survey_hidden'])?$_POST['survey_hidden']:'',
                'question_id' => ($_POST['question_hidden'])?$_POST['question_hidden']:'',
                'step_id'     => ($_POST['step_hidden'])?$_POST['step_hidden']:'',
                'created_at'  => date('Y-m-d H:i:s'),
            );
            $insert = dbRowInsert("create_template_report",$data);
        }
        else if($key == 'departmentids'){
            $data = array(
                'cby' 	      => $_SESSION['user_id'],
                'temp_name'   => $_POST['report_name_hidden'],
                'temp_id'	  => $temp_id ,
                'keyword' 	  => 'department_id',
                'created_at'  => $date,
                'value' 	  => implode(',',$value),
                'survey_id'   => ($_POST['survey_hidden'])?$_POST['survey_hidden']:'',
                'question_id' => ($_POST['question_hidden'])?$_POST['question_hidden']:'',
                'step_id'     => ($_POST['step_hidden'])?$_POST['step_hidden']:'',
                'created_at'  => date('Y-m-d H:i:s'),
            );
            $insert = dbRowInsert("create_template_report",$data);
        }
        else if($key == 'groupids'){
            $data = array(
                'cby' 	      => $_SESSION['user_id'],
                'temp_name'   => $_POST['report_name_hidden'],
                'temp_id'	  => $temp_id ,
                'keyword' 	  => 'group_id',
                'created_at'  => $date,
                'value' 	  => implode(',',$value),
                'survey_id'   => ($_POST['survey_hidden'])?$_POST['survey_hidden']:'',
                'question_id' => ($_POST['question_hidden'])?$_POST['question_hidden']:'',
                'step_id'     => ($_POST['step_hidden'])?$_POST['step_hidden']:'',
                'created_at'  => date('Y-m-d H:i:s'),
            );
            $insert = dbRowInsert("create_template_report",$data);
        }
    }
    if(!empty($insert)){
        alertSuccess('Template Created Successfully','?page=manage-report-template&type=template');
    }
}

if(isset($_GET['type']) && $_GET['type']=='temp-details'){
    record_set('template', 'select * from create_template_report where temp_id ="'.$_GET['viewid'].'"');
    while($row_get_template = mysqli_fetch_assoc($template)){
       if($row_get_template['keyword']=='location_id'){
            $locationId = $row_get_template['value'];
       }
       else if($row_get_template['keyword']=='department_id'){
            $departmentId = $row_get_template['value'];
       }
       else if($row_get_template['keyword']=='group_id'){
            $groupId = $row_get_template['value'];
       }
    }
    
}
if(isset($_GET['type']) && $_GET['type']=='schedule-details'){
    record_set('template', 'select * from schedule_report where temp_id ="'.$_GET['viewid'].'"');
    while($row_get_template = mysqli_fetch_assoc($template)){
       if($row_get_template['keyword']=='location_id'){
            $locationId = $row_get_template['value'];
       }
       else if($row_get_template['keyword']=='department_id'){
            $departmentId = $row_get_template['value'];
       }
       else if($row_get_template['keyword']=='group_id'){
            $groupId = $row_get_template['value'];
       }
    }
    
}
$template_loc = explode(',',$locationId);
$template_dep = explode(',',$departmentId);
$template_grp = explode(',',$groupId);

?>

<section class="content-header">
  <h1><?=($_GET['type']=='report')? 'Create Report':'Create Template' ?></h1>
</section>
<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="row filter_form" >
                <form action="" method="post" id="filter_form_data">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Survey</label>
                            <select name="survey_id" id="survey_id" class="form-control form-control-lg survey" id="">
                                <option value="">Select Survey</option>
                                <?php foreach(get_allowed_data('surveys',$_SESSION['user_type']) as $key=> $value){ ?>
                                <option value="<?=$key?>"><?=$value?></option>
                                <?php  } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Section</label>
                            <select name="section_id" id="section_id" class="form-control form-control-lg section" id="">
                            
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Question</label>
                            <select name="question_id" class="form-control form-control-lg question" id="question_id">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <input type="button" name="filter" class="btn btn-primary btn-block" value="Filter" id="filter-btn">
                        </div>
                    </div>
                </form>
            </div>

            <!-- checkbox start -->
            <div class="loader" style="text-align: center; display:none;">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="150px" height="150px" viewBox="0 0 150 150" enable-background="new 0 0 150 150" xml:space="preserve">

                <g id="Layer_1">
                    
                        <circle opacity="0.4" fill="#FFFFFF" stroke="#1C75BC" stroke-width="2" stroke-linecap="square" stroke-linejoin="bevel" stroke-miterlimit="10" cx="75" cy="75.293" r="48.707"></circle>
                </g>
                <g id="Layer_2">
                    <g>
                        <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="36.2957" y1="34.8138" x2="94.5114" y2="34.8138">
                            <stop offset="0" style="stop-color:#2484C6"></stop>
                            <stop offset="1" style="stop-color:#2484C6;stop-opacity:0"></stop>
                        </linearGradient>
                        <path fill="none" stroke="url(#SVGID_1_)" stroke-width="4" stroke-linecap="round" stroke-linejoin="bevel" d="M38.296,43.227
                            c0,0,21.86-26.035,54.216-13.336">
                            <animateTransform attributeName="transform" attributeType="XML" type="rotate" from="0 75 75" to="-360 75 75" dur=".8s" repeatCount="indefinite"></animateTransform>
                        </path>
                    </g>
                </g>
                </svg>
            </div>
            <div class="checkboxForm">
                <form action="<?=($_GET['type']=='report') ? './excel-import-files/create-report.php?export=1&type=export' : ''?>" method="post">
                    <!-- assign location -->
                    <div class="row locationCheck">
                        <input type="hidden" name="hidden" id="searchdata" value=<?=json_encode($_POST)?>>
                        <input type="hidden" name="survey_hidden" id="survey_hidden" value="">
                        <input type="hidden" name="step_hidden" id="step_hidden" value="">
                        <input type="hidden" name="question_hidden" id="question_hidden" value="">
                        <input type="hidden" name="report_name_hidden" value="" class="report_name_hidden">
                        <div class="col-md-12 with-border">
                            <h4>Assign Location</h4>
                            <input type="checkbox" onclick="checked_all(this,'loc_checkbox')" /><strong> Select All</strong><br/><br/>
                        </div>
                        <?php 
                        foreach(get_allowed_data('locations',$_SESSION['user_type']) as $key => $value){ ?>
                        <div class="col-md-4">
                            <input type="checkbox" id="locationids<?php echo $key ?>" <?=(in_array($key,$template_loc)) ? 'checked':''?> class="loc_checkbox" value="<?php echo $key; ?>" name="locationids[<?php echo $key; ?>]" /> 
                            
                            <label for="locationids<?php echo $key; ?>">
                            <?php echo $value ?>
                            </label>
                        </div>
                        <?php } ?>
                    </div>
                    <!-- assign department -->
                    <div class="row departmentCheck">        
                        <div class="col-md-12 with-border">
                            <h4>Assign Departments</h4>
                            <input type="checkbox" onclick="checked_all(this,'dept_checkbox')" /><strong> Select All</strong><br/><br/>
                        </div>
                        <?php 
                        foreach(get_allowed_data('departments',$_SESSION['user_type']) as $key => $value){ ?>
                            <div class="col-md-4">
                            <input type="checkbox" id="departmentids<?php echo $key ?>" class="dept_checkbox" <?=(in_array($key,$template_dep)) ? 'checked':''?> value="<?php echo $key; ?>" name="departmentids[<?php echo $key; ?>]"/> 
                            <label for="departmentids<?php echo $key; ?>">
                            <?php echo $value ?>
                            </label>
                            </div>
                        <?php }?>
                    </div>
                    <!-- assign group -->
                    <div class="row groupCheck">         
                        <div class="col-md-12 with-border">
                            <h4>Assign Group</h4>
                            <input type="checkbox" onclick="checked_all(this,'group_checkbox')" /><strong> Select All</strong><br/><br/>
                        </div>
                        <?php 
                        foreach(get_allowed_data('groups',$_SESSION['user_type']) as $key => $value){ ?>
                            <div class="col-md-4">
                                <input type="checkbox" id="groupids<?php echo $key ?>" class="group_checkbox" <?=(in_array($key,$template_grp)) ? 'checked':''?> value="<?php echo $key; ?>" name="groupids[<?php echo $key; ?>]" /> 
                                
                                <label for="groupids<?php echo $key; ?>">
                                <?php echo $value ?>
                                </label>
                            </div>
                        <?php } ?>  
                    </div>
                    <div class="col-md-12 pull-right filter_form">
                        <?php if($_GET['type']=='report') { ?>
                        <input type="submit" class="btn btn-success green-btn" value="Export">
                        <?php }else { ?>
                        <input type="submit" class="btn btn-success green-btn" name="create_temp" value="Create Template">
                        <?php } ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- start report name popup -->
<div class="modal" id="create_popup">
  <div class="modal-dialog" role="document">
   
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"> </h5>
          <button type="button" class="close closes" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <div class="second_form" style="padding: 20px;">
              <div class="form-group row">
                <label for="staticEmail" class="col-sm-4 col-form-label">Name Of Report</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" id="report_name" name="report_name" placeholder="Enter Report name" value="<?=$rpt['report_name']?>" required>
                  <span class="error_msg" style="color:red;display:none;font-weight: 600;">Report name is required</span>
                </div>
              </div>            
             
              <div class="pull-right">                
                <button type="button"class="btn btn-success green-btn" id="create_btn" style="padding: 6px 28px;">Ok</button>
                  <button type="button"class="btn btn-danger closes" style="background-color:#ff1c00 !important;">Cancel</button>
              </div>
			</div>
          </div>
        </div>
        <!-- <div class="modal-footer">
          <button type="button" class="btn btn-secondary closes" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Export Now</button>
        </div> -->
      </div>
    </form>
  </div>
</div>
<script>
    var currentPage = '<?php echo $_GET['type']?>';
		if(currentPage == 'template'){
			$('#create_popup').show();
		} 
    $(document).ready(function() {
		/* start report name popup */
		$("#create_btn").click(function() {
			var report_name = $('#report_name').val();
			$('.report_name_hidden').val(report_name);
			
			if(report_name !=''){
				$('.error_msg').hide();
				$('#create_popup').hide();
			}
			else{
				$('.error_msg').show();
			}
		});
		$(".closes").click(function() {
			$('#create_popup').hide();
		});

        $('#filter-btn').click(function(){
            $('.checkboxForm').hide();
            $('.loader').show();
            $('#survey_hidden').val($('#survey_id').val());
            $('#step_hidden').val($('#section_id').val());
            $('#question_hidden').val($('#question_id').val());
             myTimeout = setTimeout(function(){ 
                $('.checkboxForm').show();
                $('.loader').hide();
            }, 1000);

        })
    })
    function get_step_ajax(survey_id){
        $.ajax({
            type: "POST",
            url: 'ajax/ajaxOnReport.php',
            data: {survey_id: survey_id,mode:'step'}, 
            success: function(response){
                if (response == '') {
                    // $('#conditionQuestion'+count).html('<option value="0">No Question</option>');
                }else{
                    var response = JSON.parse(response);
                    console.log(response);
                    $('.section').html(response.response);
                    if(response.department<1){
                        $('.departmentCheck').hide();
                    }else {
                        $('.departmentCheck').show();
                    }
                    //if(response.location[0]<1 || response.location[1].length){
                    if(response.location[0]<1  ){
                        $('.locationCheck').hide();
                    }else {
                        $('.locationCheck').show();
                    }
                    if(response.group<1){
                        $('.groupCheck').hide();
                    }else {
                        $('.groupCheck').show();
                    }
                }
            }
		});
    }

    function get_question_ajax(survey_id,step){
        $.ajax({
            type: "POST",
            url: 'ajax/ajaxOnReport.php',
            data: {survey_id: survey_id,step_id:step,mode:'question'}, 
            success: function(response){
                if (response == '') {
                    // $('#conditionQuestion'+count).html('<option value="0">No Question</option>');
                }else{
                    $('.question').html(response);
                    console.log(response);
                }
            }
		});
    }

    $(document).on('change','#survey_id',function(){

       $('#section_id').html('<option value="" data-qid="">Select step</option>');
       $('.question').html('<option value="" data-qid="">Select question</option>');
        get_step_ajax($(this).val());
    })

    $(document).on('change','#section_id',function(){
        let survey_id = $('#survey_id').val();
        let step_id = $(this).val();
        get_question_ajax(survey_id,step_id);
    })

let type = '<?=$_GET['type'] ?>';
if(type == 'schedule-details' || type == 'temp-details'){
    $('.filter_form').remove();
}

var report_name = $('#report_name_hidden').val();
console.log(report_name);
if(report_name){
    $('#create_popup').hide();
}
</script>