
<?php 
$surveyByUsers  = get_survey_data_by_user($_GET['type'],1);

//get all id of survey in array
$assign_survey = array_map(function($element) {
    return $element['id'];
}, $surveyByUsers);

$surveys_ids = implode(',',$assign_survey);

//self assign task
if(isset($_POST['self_assign_hidden']) and !empty($_POST['self_assign_hidden'])){ 
    $survey_id           = explode(',',$_POST['self_assign_survey_id_hidden']);
    $task_id             = explode(',',$_POST['self_assign_response_id_hidden']);
    $assing_to_user_id   = $_SESSION['user_id'];
    $i =0;
    foreach($task_id as $tasks){
        $data = array(
            "assign_to_user_id"   => $assing_to_user_id,
            "task_id"             => $tasks,
            "survey_id"           => $survey_id[$i],
            "survey_type"         => $survey_type_id,
            "task_status"         => 2,
            "assign_by_user_id"   => $assing_to_user_id,
            "cdate"               => date("Y-m-d H:i:s")
        );
        // check the assign task already exists for this user or not
        record_set("check_assign_task", "SELECT * FROM assign_task where task_id = $tasks and survey_id = ".$survey_id[$i]);
        $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);
         
        if($totalRows_check_assign_task > 0 ){
        //  $insert_value = dbRowUpdate("assign_task", $data, "where id=".$row_check_assign_task['id']);
            dbRowDelete('assign_task', "task_id = $tasks and survey_id = $survey_id[$i]");
        }
        $insert_value = dbRowInsert("assign_task",$data);
        $data_contact_action = array(
            "user_id"=> $tasks,
            "action"=> 2,
            "cby" =>$assing_to_user_id,
            "comment"=> 'response assigned to '.$_SESSION['user_name'],
            'created_date'=>date("Y-m-d H:i:s")
        );
        $insert_contact_action =  dbRowInsert("survey_contact_action",$data_contact_action);
        $i++;
    }
    if(!empty($insert_value )){	
        $msg = "Task Assigned Successfully";
        alertSuccess( $msg,'?page=view-my-assign-task&type='.$_GET['type']);
        die();
    }
    $msg = "Task Not Assigned";
    alertdanger( $msg,'?page=view-my-assign-task&type='.$_GET['type']);
}
?>

<button type="button" class="btn btn-primary self-assign-btn" value="" style="display: none; <?=$display?>">Self Assign</button>
<!-- Modal -->

    <form method="post" id="self-assign_form">
        <div class="col-md-12">
            <div class="form-group">
                <input type="hidden" name="self_assign_hidden" value="" id="set_self_assign">
                <input type="hidden" class="survey-id-self-assign-hidden" name="self_assign_survey_id_hidden" value="">
                <input type="hidden" class="self-assign-response-id-hidden" name="self_assign_response_id_hidden" value="">
            </div>
        </div>
    </form>
  
<script>
    // alert message to delete file

$(document).ready(function(){
    $(".self-assign-btn").click(function(e){
        self_assign_confirmation(e);
    })
})
$(document).on('change','.assignSurveyCheckbox',function(){
    var value = $(this).is(':checked');
    let sid  = $(this).data('sid');
    var checkedArray=[];
    var checkedSurveyId=[];
    $("input[name='assign']:checked").each(function(){
        checkedArray.push($(this).val());
        checkedSurveyId.push($(this).data('sid'));
    });
    $('.survey-id-self-assign-hidden').val(checkedSurveyId);
    $('.self-assign-response-id-hidden').val(checkedArray);

    if(checkedArray.length >0){
        $('.self-assign-btn').show();
    }else{
        $('.self-assign-btn').hide();
    }
});
function self_assign_confirmation(e){
    e.preventDefault();
    swal({
		html: '<p style="font-size: 19px;font-weight: 500;">Are you sure want to assign this task you yourself ?</p>',
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, Assign it!'
    }).then(function() {
        $('#set_self_assign').val('set');
        $("#self-assign_form").submit();
	})
}
</script>