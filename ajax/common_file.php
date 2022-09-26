<?php
include('../function/function.php');
include('../function/get_data_function.php');

// delete data
if(isset($_POST['mode']) && $_POST['mode']=='delete'){
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

// get table from survey league
if(isset($_POST['mode']) && $_POST['mode']=='survey_league'){

  $data =array();
  
  $answer_query = 'SELECT * FROM answers where id!=0 ';
  //survey location
  if($_POST['survey_type']=='location'){
    $query = " and surveyid =".$_POST['survey']." and locationid in (select id from locations where cstatus=1)";  
    $groupBy = 'locationid';
  }
  //survey group
  else if($_POST['survey_type']=='group'){
    $query = " and surveyid =".$_POST['survey']." and groupid in (select id from groups where cstatus=1)";  
    $groupBy = 'group';
  }
  //survey department
  else if($_POST['survey_type']=='department'){
    $query = " and surveyid =".$_POST['survey']." and departmentid in (select id from departments where cstatus=1)";
    $groupBy = 'departmentid';
  }

  if(!empty($_POST['fdate']) and !empty($_POST['fdate'])){
    $query .= " and  cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
  }

  // get total count of result
  record_set("total_survey","SELECT COUNT(DISTINCT(cby)) as totalCount FROM answers WHERE id!=0  $query");
  $row_total_survey = mysqli_fetch_assoc($total_survey);
  $total_survey = $row_total_survey['totalCount'];

  //get all record from answer
  record_set("get_entry",$answer_query.$query." GROUP by cby");

  if($totalRows_get_entry){
    $survey_data = array();
    while($row_get_entry = mysqli_fetch_assoc($get_entry)){
        $locId      = $row_get_entry['locationid'];
        $depId      = $row_get_entry['departmentid'];
        $grpId      = $row_get_entry['groupid'];
        $surveyid   = $row_get_entry['surveyid'];
        $cby        = $row_get_entry['cby'];

        if($_POST['survey_type']=='location'){
          $title = 'Location';
          $count = array();
          record_set("get_question","select * from answers where locationid=$locId and cby=$cby");
          $total_answer = 0;
          while($row_get_question= mysqli_fetch_assoc($get_question)){
              $total_answer += $row_get_question['answerval'];
          }
          $average_value = ($total_answer/($totalRows_get_question*100))*100;
          $survey_data[$locId][$cby] = $average_value;
        }
        else if($_POST['survey_type']=='department'){
          $title = 'Department';
          $count = array();
          record_set("get_question","select * from answers where departmentid=$depId and cby=$cby");
          $total_answer = 0;
          while($row_get_question= mysqli_fetch_assoc($get_question)){
              $total_answer += $row_get_question['answerval'];
          }
          $average_value = ($total_answer/($totalRows_get_question*100))*100;
          $survey_data[$depId][$cby] = $average_value;
        }
        else if($_POST['survey_type']=='group'){
          $title = 'Group';
          $count = array();
          record_set("get_question","select * from answers where groupid=$grpId and cby=$cby");
          $total_answer = 0;
          while($row_get_question= mysqli_fetch_assoc($get_question)){
              $total_answer += $row_get_question['answerval'];
          }
          $average_value = ($total_answer/($totalRows_get_question*100))*100;
          $survey_data[$grpId][$cby] = $average_value;
        }
    }
  }
  $i=1; 
  ksort($survey_data);
  if(count($survey_data)>0){

    $html ='';
    $html ='<table class="table table-bordered">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">'.$title.'</th>
        <th scope="col">Number of survey</th>
        <th scope="col">Average Score</th>
      </tr>
    </thead>
    <tbody>';
    foreach($survey_data as $key =>$datasurvey){ 
      $total=  array_sum($datasurvey)/count($datasurvey);
        $total =  round($total, 2);
        $titleName='';
        if($_POST['survey_type']=='location'){
            $titleName = getLocation()[$key];
        }
        else if($_POST['survey_type']=='group'){
            $titleName = getGroup()[$key];
        }
        else if($_POST['survey_type']=='department'){
            $title = 'Department';
            $titleName = getDepartment()[$key];
        }
      $html .='<tr>
        <td></td>
        <td>'.$titleName.'</td>
        <td >'.count($datasurvey).'</td>
        <td>'.$total.' %</td>
      </tr>';
    }
  }else {
    $html = 'No result Found';
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