<?php 
// assign task to user
if(isset($_POST['reassign'])){
    // echo '<pre>';
    // print_r($_POST); die();
    $survey_id           = explode(',',$_POST['survey_id']);
    $task_id             = explode(',',$_POST['response_id_hidden']);
    $assing_to_user_id   = $_POST['assing_to_user_id'];
    // $assign_to_user_type = $_POST['user_type'];
    // $assign_by_user_type = $_SESSION['user_type'];
    $assign_by_user_id   = $_SESSION['user_id'];
    $i = 0;
    foreach($task_id as $tasks){
        $data = array(
            "assign_to_user_id"   => $assing_to_user_id,
            "task_id"             => $tasks,
            "task_status"         => 2,
            "survey_id"           => $survey_id[$i],
            "survey_type"         => $survey_type_id,
            "assign_by_user_id"   => $assign_by_user_id,
            "reassign_status"     => 1,
            "cdate"               => date("Y-m-d H:i:s")
        );

        // check the assign task already exists for this user or not
        record_set("check_assign_task", "SELECT * FROM assign_task where assign_to_user_id = $assign_by_user_id  and task_id = $tasks");
        $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);
       
        if($totalRows_check_assign_task > 0 ){
            $insert_value=	dbRowUpdate("assign_task", $data, "where id=".$row_check_assign_task['id']);
        }else {
            $insert_value =  dbRowInsert("assign_task",$data);
        }
        $userdata   = get_user_datails($assing_to_user_id,$assign_to_user_type);

        $user_email = $userdata['email'];
        $user_name  = $userdata['name']; 

        $data_contact_action = array(
            "user_id"=> $tasks,
            "action"=> 2,
            "cby" =>$assing_to_user_id,
            "comment"=> 'response assigned to '.$user_name,
            'created_date'=>date("Y-m-d H:i:s")
        );

        $insert_contact_action =  dbRowInsert("survey_contact_action",$data_contact_action);
        // send mail to user assigned task
        send_email_to_assign_user($user_name,$user_email);
        $i++;
    }

    $userdata   = get_user_datails($assing_to_user_id);
    $user_email = $userdata['email'];
    $user_name  = $userdata['name'];

    // send mail to user assigned task
    send_email_to_assign_user($user_name,$user_email);
    if(!empty($insert_value )){	
        $msg = "Task Assigned Successfully";
        alertSuccess( $msg,'?page=view-my-assign-task&type='.$_GET['type']);
        die();
    }
        $msg = "Task Not Assigned";
        alertdanger( $msg,'?page=view-my-assign-task='.$_GET['type']);
}
?>

<button type="button" class="btn btn-primary btn-submit" style="display:none;" data-toggle="modal" value="" data-target="#exampleModalCenter">
    Re Assign
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Assign Task</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <form method="post" id="assign_form">
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="form-group">
                    <input type="hidden" class="survey_id" name="survey_id" value="">
                    <input type="hidden" class="response_id_hidden" name="response_id_hidden" value="">
                    <label>User Type</label>
                        <select class="form-control" tabindex=7 id="user_type" name="user_type">
                            <option value="">Select User Type</option>
                        <?php 
                            $user_types_array=user_type();  
                            foreach($user_types_array as $key => $value){
                            if($_SESSION['user_type']==2){
                                $allowed_key=2;
                            }
                             ?>
                            <option <?php if($type==$key){?> selected="selected"<?php  }?> value="<?php echo $key; ?>"> <?php echo $value; ?>
                            </option>
                            <?php }
                            
                        ?>
                        </select>
                    </div>
                </div>
                <!-- select admin -->
                <div class="col-md-12" id="users">
                </div>                                    
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" name="reassign">Save changes</button>
            </div>
        </form>
    </div>
  </div>
</div>
<script>
    
    $(document).on('change','.assignSurveyCheckbox',function(){
    var value = $(this).is(':checked');
    let sid  = $(this).data('sid');
    var checkedArray=[];
    var surveyArray = []
    $("input[name='assign']:checked").each(function(){
        checkedArray.push($(this).val());
        surveyArray.push($(this).data('sid'));
    });
    //console.log(checkedArray,'checkedArray',surveyArray);
    $('.survey_id').val(surveyArray);
    $('.response_id_hidden').val(checkedArray);
   

    if(checkedArray.length >0){
        $('.btn-submit').show();
       $('.self-assign-btn').show();
    }else{
        $('.btn-submit').hide();
        $('.self-assign-btn').hide();
    }
    });

    // ajax on the user type change in assign task
    $(document).on('change','#user_type',function(){
        let user_type = $(this).val();
        let survey_id  = $('.survey_id').val();
        assign_user(survey_id,user_type);
    });

    function assign_user(survey_id,user_type){
        $.ajax({
            method:"POST",
            url:'<?=baseUrl()?>ajax/common_file.php',
            data:{
                survey_id:survey_id,
                user_type:user_type,
                mode:'assign_users'
            },
            success:function(response){
                response = JSON.parse(response);
                console.log(response);
                $('#users').html(response);
            }
        })
    }
    
</script>