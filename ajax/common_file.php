<?php
include('../function/function.php');
include('../function/get_data_function.php');

// delete data
if(isset($_POST['mode']) and $_POST['mode']=='delete'){
    $delete =  dbRowDelete($_POST['db_table_name'], "temp_id='".$_POST['deleteid']."'");
    echo $delete; die();
}

// get option from survey statics
if(isset($_POST['mode']) && $_POST['mode']=='survey_statics'){
    if($_POST['type']!='survey'){
      //$data = get_allowed_data('surveys',$_SESSION['id']);
      // get survey name
      $surveyByUsers = get_filter_data_by_user('surveys');
    
        // if($_POST['type']=='group'){
        //   $data = get_allowed_data('groups',$_SESSION['id']);
        // }
        // if($_POST['type']=='location'){
        //   $data = get_allowed_data('locations',$_SESSION['id']);
        // }
        // if($_POST['type']=='department'){
        //   $data = get_allowed_data('departments',$_SESSION['id']);
        // }
        $html ='';
        $html ='<div class="form-group"><label> Survey</label>
            <select name="survey" class="form-control form-control-lg survey" required> <option value="">Select Survey</option>';
            foreach($surveyByUsers as $surveyData){
              $surveyId = $surveyData['id'];
              $surveyName = $surveyData['name'];
                $html .='<option value="'.$surveyId.'"';
                if($_POST['type']==$surveyId){
                  echo 'selected';
                }
                $html .='>'.$surveyName.'</option>';
            }
        $html .=' </select>
        <span class="error" style="display:none;">This Field is Required</span>
      </div>';
    }
  echo json_encode($html); die();
}

if(isset($_POST['mode']) and $_POST['mode'] == 'assign_users'){
  $surveyId   = $_POST['survey_id'];
  $user_type  = $_POST['user_type'];

  record_set("get_admin", "SELECT * FROM surveys WHERE id = $surveyId");

  $row_get_admin = mysqli_fetch_assoc($get_admin);

  $admin    = $row_get_admin['admin_ids'];
  $manager  = $row_get_admin['client_ids'];
  //$html = '<span style="color:red;">No user Available</span>';
  if($user_type == 1){
    record_set("get_sadmin", "SELECT * FROM `super_admin` where cstatus = 1");
    $html = '<div class="form-group">
    <label>Admin</label>
    <select class="form-control" tabindex=7 name="assing_to_user_id"  id="user_id">
    <option value="">Select Admin</option></option>';
    while($row_get_sadmin = mysqli_fetch_assoc($get_sadmin)){
      $html .='<option value="'.$row_get_sadmin['id'].'">'.$row_get_sadmin['name'].'</option>';
    }
    $html .= '</select>';
  }
  else if($user_type == 2){
    $adminId    = explode("|",$admin);
    $adminId    = array_filter($adminId);
    $alladminId = implode(',',$adminId);
    $admin_data = getAdmin($alladminId);
    $html = '<div class="form-group">
          <label>Admin</label>
          <select class="form-control" tabindex=7 name="assing_to_user_id" id="user_id">
            <option value="">Select Admin</option></option>';
            foreach($admin_data as $key => $value){
              $html .='<option value="'.$key.'">'.$value.'</option></option>';
            }
          $html .= '</select>
          <p  class="error_1" style="display:none;color: red;font-weight: 600;"> These Task is either completed by users or already reassigned. Please Choose Other Task </p>
    </div>';
  }else if($user_type == 3) {
    $html = '';
    $managerId    = explode("|",$manager);
    $managerId   = array_filter($managerId);
    $allmanagerId = implode(',',$managerId);
    $manager_data = getClient($allmanagerId);
      $html = '<div class="form-group">
            <label>Manager</label>
            <select class="form-control" tabindex=7 name="assing_to_user_id"  id="user_id">
              <option value="">Select Manager</option></option>';
              foreach($manager_data as $key => $value){
                $html .='<option value="'.$key.'">'.$value.'</option></option>';
              }
            $html .= '</select>
            <p class="error_1" style="display:none;color: red;font-weight: 600;"> These Task is either completed by users or already reassigned. Please Choose Other Task </p>
      </div>';
  }
  echo json_encode($html); die();
}

if(isset($_POST['mode']) and $_POST['mode'] == 'check_assign_task_for_user'){
  $user_id      = $_POST['user_id'];
  $user_type    = $_POST['user_type'];
  $response_id  = $_POST['response_ids'];
  if($user_type > 1){
    $user_task = record_set("get_task", "SELECT * FROM `assign_task` WHERE `assign_to_user_id` = $user_id AND `assign_to_user_type` = $user_type AND task_id IN ($response_id) AND (`task_status` IN (5,6) OR reassign_status =1)");
    echo $totalRows_get_task;
    
  }
}
?>