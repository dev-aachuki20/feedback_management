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

  if($user_type == 2){
    $adminId    = explode("|",$admin);
    $adminId    = array_filter($adminId);
    $alladminId = implode(',',$adminId);
    $admin_data = getAdmin($alladminId);
    $html = '<div class="form-group">
          <label>Admin</label>
          <select class="form-control" tabindex=7 name="assing_to_user_id">
            <option value="">Select Admin</option></option>';
            foreach($admin_data as $key => $value){
              $html .='<option value="'.$key.'">'.$value.'</option></option>';
            }
          $html .= '</select>
    </div>';
  }else if($user_type == 3) {
    $html = '';
    $managerId    = explode("|",$manager);
    $managerId   = array_filter($managerId);
    $allmanagerId = implode(',',$managerId);
    $manager_data = getClient($allmanagerId);
      $html = '<div class="form-group">
            <label>Manager</label>
            <select class="form-control" tabindex=7 name="assing_to_user_id">
              <option value="">Select Manager</option></option>';
              foreach($manager_data as $key => $value){
                $html .='<option value="'.$key.'">'.$value.'</option></option>';
              }
            $html .= '</select>
      </div>';
  }
  echo json_encode($html); die();
}

?>