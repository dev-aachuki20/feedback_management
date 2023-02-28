<style>
.col-md-3 {
    height: 80px;
}
</style>
<?php 
$assigned = get_assign_task_count_by_status(2);
// get data by user
$departmentByUsers = get_filter_data_by_user('departments');
$locationByUsers   = get_filter_data_by_user('locations');
$groupByUsers      = get_filter_data_by_user('groups');
$surveyByUsers     = get_filter_data_by_user('surveys');

$loggedIn_user_id    = $_SESSION['user_id'];
$loggedIn_user_type  = $_SESSION['user_type'];
$sid                 = $_GET['id'];

    $query = 'SELECT * FROM answers where id !=0';
    if($loggedIn_user_type > 2){
        $assign_survey = array();
        foreach($surveyByUsers as $survey){
            $assign_survey[] = $survey['id'];
        }
        if($assign_survey){
            $query .= " and surveyid IN (".implode(',',$assign_survey).")";
        }
    }

    $filter_status = '';
    if(!empty($_GET['task_status']) and $_GET['task_status']!=1){
        $filter_status = ' and task_status ='.$_GET['task_status'];
    }
    record_set("get_assign_task", "SELECT * FROM assign_task where assign_to_user_id = $loggedIn_user_id and assign_to_user_type = $loggedIn_user_type".$filter_status);

    $arr_task_id = array();
    while($row_get_assign_task = mysqli_fetch_assoc($get_assign_task)){
        $arr_task_id[] = $row_get_assign_task['task_id'];
    }
    $task_id = implode(",",$arr_task_id);
    if(empty($task_id)){
        $task_id = '0';
    }
    if($_GET['task_status']==1){
        $query .= " and cby NOT IN (".$task_id.") GROUP by cby";
    }else{
        $query .= " and cby IN (".$task_id.") GROUP by cby";
    }
    

    record_set("get_recent_entry",$query);


?>
<style>
.d-none{
    display: none !important;
}
</style>
<section class="content-header">
  <h1><?=strtoupper($_GET['req'])?></h1>
</section>
<section class="content">
    <!-- top box container start-->
    <div class="row">
        <div class="col-lg-12" id="dataforpdf">
            <div class="box">
                <div class="box-header"></div>
                    <div class="box-body">
                        <div>
                            <div class="col-md-3" style="text-align: left;padding: 0;margin: 5px;">
                                <a href="?page=survey-outcomes">
                                <button type="button" class="btn btn-success"  style="background-color: #00a65a !important;border-color: #008d4c;">Back</button>
                                </a>
                        
                            </div>
                        </div>
                        
                        <table id="datatable" class="table table-bordered table-striped" width="100%">
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <th>SURVEY NAME</th>
                                    <th>GROUP</th>
                                    <th>LOCATION</th>
                                    <th>DEPARTMENT</th>
                                    <th>RESPONDENT NUMBER</th>
                                    <th>RESULT</th>
                                    <th>Contacted</th>
                                    <th>Status</th>
                                    <th class="notforpdf">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                while($row_get_recent_entry = mysqli_fetch_assoc($get_recent_entry)){
                                record_set("get_survey_detail", "SELECT * FROM surveys where id='".$row_get_recent_entry['surveyid']."'");	
                                $row_get_survey_detail = mysqli_fetch_assoc($get_survey_detail);
                                $row_survey_entry = 1;
                                record_set("survey_entry", "SELECT DISTINCT cby FROM answers where surveyid='".$row_get_survey_detail['id']."' and cby <".$row_get_recent_entry['cby']);
                                
                                $row_survey_entry = $totalRows_survey_entry+$row_survey_entry;
                                ?>
                                <tr>
                                    <td><?=date("d-m-Y", strtotime($row_get_recent_entry['cdate']))?></td>

                                    <td><?=getSurvey()[$row_get_recent_entry['surveyid']]?></td>
                                    <td><?=getGroup()[$row_get_recent_entry['groupid']]?></td>
                                    <td><?=getLocation()[$row_get_recent_entry['locationid']]?></td>
                                    <td><?=getDepartment()[$row_get_recent_entry['departmentid']]?></td>
                                    
                                    <td><?=$row_survey_entry?></td>
                                    <?php
                                    $total_result_val=0;
                                    record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_get_recent_entry['surveyid']."' and cby='".$row_get_recent_entry['cby']."'");
                                    $achieved_result_val = 0;
                                    $to_bo_contacted     = 0;
                                    $i=0;
                                    while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
                                        $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_survey_result['questionid']);
                                            if($result_question){
                                                if(!in_array($result_question['answer_type'],array(2,3,5))){
                                                    $total_result_val = ($i+1)*100;
                                                    $achieved_result_val += $row_get_survey_result['answerval'];
                                                    $i++;
                                                }
                                            }
                                            if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100){
                                                $to_bo_contacted = 1;
                                            }
                            
                                    }
                                    $result_response = $achieved_result_val*100/$total_result_val;

                                    // for filter using contact
                                    // if($_POST['contact']!=3){
                                    //     if($to_bo_contacted == 1 && $_POST['contact']!=1){
                                    //         continue;
                                    //     }
                                    //     if($to_bo_contacted == 0 && $_POST['contact']==1){
                                    //         continue;
                                    //     }
                                    // }
                                    if($achieved_result_val==0 and $total_result_val==0){
                                        $result_response=100;
                                    }
                                    $label_class = 'success';
                                    if($result_response<50){
                                        $label_class = 'danger';
                                    }else 
                                    if($result_response<75){
                                        $label_class = 'info';
                                    }
                                    if($to_bo_contacted==1){ 
                                        $contacted='<a class="btn btn-xs btn-success">Yes</a>';
                                    }else{ 
                                        $contacted ='<a class="btn btn-xs btn-info">No</a>';
                                    } 
                                    // get taskstatus
                                    record_set("check_assign_task", "SELECT * FROM assign_task where assign_to_user_id = ".$_SESSION['user_id']." and assign_to_user_type =".$_SESSION['user_type']." and task_id = ".$row_get_recent_entry['cby']);
                                    $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);
                                    $task_status = $row_check_assign_task['task_status'];
                                    if($_GET['task_status']==1){
                                        $task_status = 1;
                                    }
                                    ?>
                                    <td><label class="label label-<?=$label_class?>"><?=round($result_response,2)?>'%</label></td>
                                    <td><?=$contacted ?></td>
                                    <td><a class="btn btn-xs btn-success"><?=assign_task_status()[$task_status]?></a></td>
                                    <td><a class="btn btn-xs btn-primary" href="survey-result.php?surveyid=<?=$row_get_recent_entry['surveyid']?>&userid=<?=$row_get_recent_entry['cby']?><?=($_GET['task_status']!=1)?'&status=assign':''?>" target="_blank">VIEW DETAILS</a></td>
                                </tr> 
                                <?php }
                                 ?>
                            </tbody>            
                            <tfoot>
                                <tr>
                                   
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th class="notforpdf">
                                        <?php if($_POST['surveys']) { ?>
                                        <button type="button" class="btn btn-primary btn-submit" data-toggle="modal" value="" data-target="#exampleModalCenter">
                                        Re Assign
                                        </button>
                                    <?php } ?>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
</section>
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
                    <input type="hidden" name="self_assign_hidden" value="" id="set_self_assign">
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
                            if($key>=$_SESSION['user_type'] and $key!=1){ ?>
                            <option <?php if($type==$key){?> selected="selected"<?php  }?> value="<?php echo $key; ?>"> <?php echo $value; ?>
                            </option>
                            <?php }
                            }
                        
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
                <button type="submit" class="btn btn-primary" name="assign">Save changes</button>
            </div>
        </form>
    </div>
  </div>
</div>
<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>
<!-- Resources -->

<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-html2canvas@latest/dist/jspdf-html2canvas.min.js"></script> 

<script>

$(document).on('change','.assignSurveyCheckbox',function(){
    //$(".assignSurveyCheckbox").prop("checked", false);
     let survey_id = $('#surveys').val();
     if(survey_id == ''){
        $('.error').show();
        alert("Please Choose Survey Type To Re Assign Any Task");
     }
    var value = $(this).is(':checked');
    let sid  = $(this).data('sid');
    var checkedArray=[];
    $("input[name='assign']:checked").each(function(){
        checkedArray.push($(this).val());
    });
    
    if(value){
       $('.survey_id').val(sid);
       $('.response_id_hidden').val(checkedArray);
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
$(document).ready( function () {
    $('#datatable').DataTable({
        "sPaginationType": "simple_numbers",
        "aoColumnDefs": [
        { 'bSortable': false, 'aTargets': [0] }
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    });
});
</script>