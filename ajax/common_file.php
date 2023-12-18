<?php
include('../function/function.php');
include('../function/get_data_function.php');

// delete data
if (isset($_POST['mode']) and $_POST['mode'] == 'delete') {
  $delete =  dbRowDelete($_POST['db_table_name'], "id='" . $_POST['deleteid'] . "'");
  echo $delete;
  die();
}

// get option from survey statics
if (isset($_POST['mode']) && $_POST['mode'] == 'survey_statics') {

  if ($_POST['type'] != 'survey') {

    // get survey name
    $surveyByUsers = get_survey_data_by_user($_POST['survey_type']);

    $html = '';
    $html = '<div class="form-group"><label>' . ucfirst($_POST['survey_type']) . '</label>
            <select name="survey" class="form-control form-control-lg survey" required> <option value="">Select ' . ucfirst($_POST['survey_type']) . '</option>';
    foreach ($surveyByUsers as $surveyData) {
      $surveyId = $surveyData['id'];
      $surveyName = $surveyData['name'];
      $html .= '<option value="' . $surveyId . '"';
      if ($_POST['type'] == $surveyId) {
        echo 'selected';
      }
      $html .= '>' . $surveyName . '</option>';
    }
    $html .= ' </select>
        <span class="error" style="display:none;">This Field is Required</span>
      </div>';
  }
  echo json_encode($html);
  die();
}

if (isset($_POST['mode']) and $_POST['mode'] == 'assign_users') {
  $surveyId   = $_POST['survey_id'];
  $user_type  = $_POST['user_type'];

  $user_id = get_assigned_data($surveyId, 'survey');
  $user_id = implode(',', $user_id);
  //$html = '<span style="color:red;">No user Available</span>';
  $title = '';

  if ($user_type == 2) {
    //superadmin
    $userData = getUsers(2);
    $title = "Super Admin";
  } else if ($user_type == 3) {
    //admin
    $title = "Admin";
    $userData = getUsers(3);
  } else if ($user_type == 4) {
    //manger
    $title = "Manger";
    $userData = getUsers(4);
  } else if ($user_type == 1) {
    $title = "DGS User";
    $userData = getUsers(1);
  }
  if ($title) {
    $html = '<div class="form-group">
    <label>' . $title . '</label>
    <select class="form-control select-box" tabindex=7 name="assing_to_user_id"  id="user_id" required>
    <option value=""> select ' . $title . '</option></option>';
    foreach ($userData as $key => $value) {
      $html .= '<option value="' . $key . '">' . $value . '</option>';
    }
    $html .= '</select> <p  class="error_1" style="display:none;color: red;font-weight: 600;"> These Task is either completed by users or already reassigned. Please Choose Other Task </p></div> ';
  }
  echo json_encode($html);
  die();
}

if (isset($_POST['mode']) and $_POST['mode'] == 'check_assign_task_for_user') {
  $user_id      = $_POST['user_id'];
  $user_type    = $_POST['user_type'];
  $response_id  = $_POST['response_ids'];
  $task_type    = $_POST['task_type'];
  //if ($user_type > 1) {
  if ($task_type == 'assign') {
    $user_task = record_set("get_task", "SELECT * FROM `assign_task` WHERE `assign_to_user_id` = $user_id  AND task_id IN ($response_id)");
    if ($totalRows_get_task > 0) {
      echo "This task is already assign to this user please choose another task.";
    }
  } else {
    $user_task = record_set("get_task", "SELECT * FROM `assign_task` WHERE reassign_status =1 and  task_id IN ($response_id)");
    if ($totalRows_get_task > 0) {
      echo "Sorry ! Re assign task can not assign to any users";
    }
  }
  //}
}

if (isset($_POST['mode']) and $_POST['mode'] == 'group') {
  $group_ids = $_POST['id'];
  $group_ids = implode(',', $group_ids);

  record_set("locations_data", "select id,name,location_id from `groups` where id IN ($group_ids) order by name ASC");
  $locationId = array();
  $html = '';
  while ($row_get_location = mysqli_fetch_assoc($locations_data)) {
    $location_id          = $row_get_location['location_id'];
    $location_id_explode  = explode(',', $location_id);
    foreach ($location_id_explode as $loc) {
      if (!in_array($loc, $locationId)) {
        $html .= '<option value="' . $loc . '">' . getLocation()[$loc] . '</option>';
      }
      $locationId[$loc] = $loc;
    }
  }
  echo $html;
  die();
}
if (isset($_POST['mode']) and $_POST['mode'] == 'location') {
  $location_ids = $_POST['id'];
  $location_ids = implode(',', $location_ids);

  record_set("department_data", "select id,name,department_id from locations where id IN ($location_ids) order by name ASC");
  $departmentId = array();
  $html = '';
  while ($row_get_department   = mysqli_fetch_assoc($department_data)) {
    $department_id          = $row_get_department['department_id'];
    $department_id_explode  = explode(',', $department_id);

    foreach ($department_id_explode as $dept) {
      if (!in_array($dept, $departmentId)) {
        if ($dept != '') {
          $html .= '<option value="' . $dept . '">' . getDepartment()[$dept] . '</option>';
        }
      }

      $departmentId[$dept] = $dept;
    }
  }
  echo $html;
  die();
}
if (isset($_POST['mode']) and $_POST['mode'] == 'department') {
  $department_ids = $_POST['id'];
  $department_ids = implode(',', $department_ids);

  record_set("department_data", "select id,name,role_id from departments where id IN ($department_ids) order by name ASC");
  $roleId = array();
  $html = '';
  while ($row_get_department   = mysqli_fetch_assoc($department_data)) {
    $role_id          = $row_get_department['role_id'];
    $role_id_explode  = explode(',', $role_id);

    foreach ($role_id_explode as $role) {
      if (!in_array($role, $roleId)) {
        $html .= '<option value="' . $role . '">' . getRole()[$role] . '</option>';
      }
      $roleId[$dept] = $role;
    }
  }
  echo $html;
  die();
}
if (isset($_POST['mode']) and $_POST['mode'] == 'load_group') {
  $survey_ids = $_POST['id'];
  // implode if current page is not create report
  if ($_POST['page'] != 'create_report') {
    $survey_ids = array_unique($survey_ids);
    $survey_ids = implode(',', $survey_ids);
  }

  record_set("groups_data", "select id,name,groups from surveys where id IN ($survey_ids) order by name ASC");
  $groupId = array();
  $html = '<div class="col-md-12 with-border">
    <h4>Assign Group</h4>
    <input type="checkbox" class="group_checkbox_all" /><strong> Select All</strong><br/><br/>
  </div>';
  // get group id of relative to survey
  while ($row_get_groups  = mysqli_fetch_assoc($groups_data)) {
    $group_id          = $row_get_groups['groups'];
    $group_id_explode  = explode(',', $group_id);
    foreach ($group_id_explode as $grp) {
      if ($grp) {
        $groupId[$grp] = $grp;
      }
    }
  }
  if (count($groupId) > 0) {
    foreach ($groupId as $grp_data) {
      // check assign group for admin and below level
      // if($_SESSION['user_type']>2){
      //   $group_id_assign = get_assigned_user_data($_SESSION['user_id'],'group');
      // }
      //if($_SESSION['user_type']<3 || in_array($grp_data, $group_id_assign)){
      $html .= '<div class="col-md-4">
          <input type="checkbox"  id="groupids' . $grp_data . '" class="group_checkbox" value="' . $grp_data . '" name="groupids[' . $grp_data . ']" /> 
          <label for="groupids' . $grp_data . '">' . getGroup()[$grp_data] . ' </label>
        </div>';
      //}
    }
  } else {
    $data = getLocation();
    $html = '<div class="col-md-12 with-border">
      <h4>Assign Location</h4>
      <input type="checkbox" class="loc_checkbox_all" /><strong> Select All</strong><br/><br/>
    </div>';
    foreach ($data as $key => $value) {
      $html .= '<div class="col-md-4">
      <input type="checkbox" id="locationids' . $key . '" class="loc_checkbox" value="' . $key . '" name="locationids[' . $key . ']" /> 
      <label for="locationids' . $key . '">' . $value . '</label>
    </div>';
    }
  }
  echo $html;
  die();
}


if (isset($_POST['mode']) and $_POST['mode'] == 'add_user_group_assign') {
  $group_ids = $_POST['id'];
  $group_ids = implode(',', $group_ids);

  record_set("locations_data", "select id,name,location_id from `groups` where id IN ($group_ids) order by name ASC");
  $locationId = array();
  $html = '<div class="col-md-12 with-border">
    <h4>Assign Location</h4>
    <input type="checkbox" class="loc_checkbox_all" /><strong> Select All</strong><br/><br/>
  </div>';
  while ($row_get_location = mysqli_fetch_assoc($locations_data)) {
    $location_id          = $row_get_location['location_id'];
    $location_id_explode  = explode(',', $location_id);
    foreach ($location_id_explode as $loc) {
      if (!in_array($loc, $locationId)) {
        $html .= '<div class="col-md-4">
          <input type="checkbox" id="locationids' . $loc . '" class="loc_checkbox" value="' . $loc . '" name="locationids[' . $loc . ']" /> 
          
          <label for="locationids' . $loc . '">' . getLocation()[$loc] . '</label>
        </div>';
      }
      $locationId[$loc] = $loc;
    }
  }
  echo $html;
  die();
}

if (isset($_POST['mode']) and $_POST['mode'] == 'add_user_location_assign') {
  $location_ids = $_POST['id'];
  $location_ids = implode(',', $location_ids);

  record_set("department_data", "select id,name,department_id from locations where id IN ($location_ids) order by name ASC");
  $departmentId = array();
  $html = '<div class="col-md-12 with-border">
  <h4>Assign Department</h4>
  <input type="checkbox" class="dept_checkbox_all" /><strong> Select All</strong><br/><br/>
  </div>';
  while ($row_get_department   = mysqli_fetch_assoc($department_data)) {
    $department_id          = $row_get_department['department_id'];
    $department_id_explode  = explode(',', $department_id);

    foreach ($department_id_explode as $dept) {
      if (!in_array($dept, $departmentId)) {
        if ($dept) {
          $html .= '<div class="col-md-4">
            <input type="checkbox" id="departmentids' . $dept . '" class="dept_checkbox" value="' . $dept . '" name="departmentids[' . $dept . ']" /> 
            
            <label for="departmentids' . $dept . '">' . getDepartment()[$dept] . '</label>
          </div>';
        }
      }
      $departmentId[$dept] = $dept;
    }
  }
  echo $html;
  die();
}
if (isset($_POST['mode']) and $_POST['mode'] == 'add_user_department_assign') {
  $role_ids = $_POST['id'];
  $role_ids = implode(',', $role_ids);

  record_set("role_data", "select id,name,role_id from departments where id IN ($role_ids) order by name ASC");
  $roleId = array();
  $html = '<div class="col-md-12 with-border">
  <h4>Assign Role</h4>
  <input type="checkbox" class="role_checkbox_all" /><strong> Select All</strong><br/><br/>
  </div>';
  while ($row_get_role   = mysqli_fetch_assoc($role_data)) {
    $role_id          = $row_get_role['role_id'];
    $role_id_explode  = explode(',', $role_id);

    foreach ($role_id_explode as $roledata) {
      if (!in_array($roledata, $roleId)) {
        if ($roledata) {
          $html .= '<div class="col-md-4">
            <input type="checkbox" id="roleids' . $roledata . '" class="role_checkbox" value="' . $roledata . '" name="roleids[' . $roledata . ']" /> 
            
            <label for="roleids' . $roledata . '">' . getRole()[$roledata] . '</label>
          </div>';
        }
      }
      $roleId[$role] = $roledata;
    }
  }
  echo $html;
  die();
}
if (isset($_POST['mode']) and $_POST['mode'] == 'dashboard') {
  $filter = '';
  if ($_POST['survey_type']) {
    $filter = " and survey_type = " . $_POST['survey_type'];
  }
  $surveyId = '';
  if (!is_null($_POST['survey_id'])) {
    $surveyId = json_decode($_POST['survey_id']);
  }
  record_set("get_surveys", "select id,name from surveys where id !=0 $filter");
  $html .= '<option value="">Select</option>';
  while ($row_get_surveys = mysqli_fetch_assoc($get_surveys)) {
    $selected = '';
    if ($surveyId != '' and $surveyId == $row_get_surveys['id']) {
      $selected = 'selected';
    }
    $html .= '<option value="' . $row_get_surveys['id'] . '" ' . $selected . '>' . $row_get_surveys['name'] . '</option>';
  }
  echo  json_encode($html);
  die();
}

if (isset($_POST['mode']) and $_POST['mode'] == 'fetch_schedule_filter') {
  // echo "<pre>";
  // print_r($_POST);
  // echo "</pre>";

  // die('gf');
  $field = $_POST['field'];
  $filter = $_POST['filter'];

  $survey_data = get_allowed_survey(strtolower(survey_type()[$filter]));

  $html = '<label id="label_survey_field" for="survey_field" class="col-sm-4 col-form-label">' . strtoupper(survey_type()[$filter]) . '</label>
  <div class="col-sm-8">
      <select id="template_survey" name="template_field[]" class="form-control form-control-lg multiple-select" required >';
  if ($field != 'survey' and $field != 'engagement' and $field != 'pulse') {
    $html .= '<option value="">Select Option</option>';
  }
  foreach ($survey_data as $key => $value) {
    $html .= '<option value="' . $key . '">' . $value . '</option>';
  }
  $html .= '</select></div>';
  // else{
  //   $data = get_filter_data_by_user($field.'s');
  // }
  echo json_encode($html);
  die();
}



if (isset($_POST['mode']) and $_POST['mode'] == 'fetch_filter_data') {
  $html = '';
  $field = $_POST['template_type'] . 's';
  $survey_details = get_survey_detail($_POST['survey_id']);
  $data_id = $survey_details[$field];
  $data = get_data_by_id($field, $data_id);
  $html = '<label id="label_survey_field" for="survey_field" class="col-sm-4 col-form-label">' . strtoupper($field) . '</label>
        <div class="col-sm-8">
          <select id="template_field" name="template_field[]" class="form-control form-control-lg " required>';
  foreach ($data as $key => $value) {
    $html .= '<option value="' . $key . '">' . $value . '</option>';
  }
  $html .= '</select>
  </div>';
  echo json_encode($html);
  die();
}
if (isset($_POST['mode']) and $_POST['mode'] == 'fetch_scheduled_template_details') {
  $sch_id = $_POST['sch_id'];
  record_set('scheduled_report_template', 'SELECT rt.name, srt.* FROM report_templates AS rt INNER JOIN scheduled_report_templates AS srt ON srt.temp_id = rt.id WHERE srt.id= "' . $sch_id . '"');
  $row_data = mysqli_fetch_assoc($scheduled_report_template);
  $filter = json_decode($row_data['filter'], true);
  $filter_values = implode(",", $filter['field_value']);
  $filter_survey = implode(",", $filter['survey']);
  if ($filter['field'] == 'survey' || $filter['field'] == 'pulse' || $filter['field'] == 'engagement') {
    $data = getaxecuteQuery_fn("select id,name from surveys where id IN ($filter_survey)");
  } else {
    $data = getaxecuteQuery_fn("select id,name from " . $filter['field'] . 's' . " where id IN ($filter_values)");
  }
  foreach ($data as $val) {
    $arr[$val['id']] = $val['name'];
  }
  echo json_encode($arr);
  die();
}

if (isset($_POST['mode']) and $_POST['mode'] == 'load_role') {
  $deptId = $_REQUEST['department'];
  record_set("get_role_id", "select role_id from departments where id IN ($deptId) order by name ASC");
  $role_ids = array();
  $new_array = array();
  while ($row_get_role_id = mysqli_fetch_assoc($get_role_id)) {
    $role_id = $row_get_role_id['role_id'];
    if ($role_id != '') {
      $role_id = explode(',', $role_id);
      $new_array = array_merge($role_ids, $role_id);
    }
  }
  if (count($new_array) > 0) {
    $id = implode(',', $new_array);
    record_set("get_role", "select * from roles where id IN ($id) order by name ASC");
    $html = '';
    $data = array();
    while ($row_get_role = mysqli_fetch_assoc($get_role)) {
      $data[$row_get_role['id']] = $row_get_role['name'];
    }
  }
  echo json_encode($data);
  die();
}

if (isset($_POST['mode']) and $_POST['mode'] == 'survey_question_by_step') {
  $step = '';
  $surveyid = $_POST['surveyid'];
  if ($_POST['step_id'] != '') {
    $step = " and survey_step_id >= " . $_POST['step_id'];
  }

  $html = '<select class="form-control skip_to_question" name="skip_to_question[]">';
  $step = '';
  if ($_POST['step_id'] != '') {
    $step = " and survey_step_id = " . $_POST['step_id'];
  }
  if (isset($_POST['questionId']) && $_POST['questionId'] != '') {
    $step  .= " and id != " . $_POST['questionId'];
  }
  record_set("get_question", "select * from questions where surveyid= $surveyid $step and cstatus=1 order by dposition asc");
  if ($totalRows_get_question > 0) {
    while ($row_get_question = mysqli_fetch_assoc($get_question)) {
      $html .= '<option value="' . $row_get_question['id'] . '" >' . $row_get_question['question'] . '</option>';
    }
  }
  $html .= '</select>';
  echo $html;
  die();
}

if (isset($_POST['mode']) and $_POST['mode'] == 'add_edit_survey_form') {

  if (isset($_POST['excludedUserIds']) && is_array($_POST['excludedUserIds']) && count($_POST['excludedUserIds']) > 0) {
    $excludedUserIds = implode(",", $_POST['excludedUserIds']);

    record_set("get_users", "select id,name from manage_users where id NOT IN ($excludedUserIds) and cstatus=1 order by name ASC");
    $options = '';

    if ($totalRows_get_users > 0) {
      while ($row_get_user = mysqli_fetch_assoc($get_users)) {
        $options .= '<option value="' . $row_get_user['id'] . '" >' . $row_get_user['name'] . '</option>';
      }
      $html = '<div class="row new-row">
          <div class="col-xs-8 col-sm-8 col-md-8">
            <div class="form-group">
              <select class="form-control mailing_users" name="mailing_user_id[]">
              ' . $options . '
              </select>
            </div>
          </div>
          <div class="col-xs-4 col-sm-4 col-md-4">
            <div class="row">
                <div class="col-xs-3 col-sm-3 col-md-3">
                  <div class="form-group" style="margin-bottom: 0;margin-top: 4px;">
                    <input style="zoom: 2;margin: 0;" class="form-check-input contacted-checkbox" name="contact_requested[]" type="checkbox" value="1" id="flexCheckDisabled">
                  </div>
                </div>
              <div class="col-xs-3 col-sm-3 col-md-3">
                <div class="form-group" style="margin-bottom: 0;margin-top: 4px;">
                  <input style="zoom: 2;margin: 0;" class="form-check-input contacted-checkbox" name="contact_requested[]" type="checkbox" value="2" checked id="flexCheckCheckedDisabled">
                </div>
              </div>
              <div class="col-xs-6 col-sm-6 col-md-6 text-right">
                <button class="btn btn-danger delete-row"><i class="fa fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>';
    } else {
      $html = '';
    }

    echo $html;
    die();
  }
}


if (isset($_POST['mode']) and $_POST['mode'] == 'fetch_time_periods' && isset($_POST['frequency']) && $_POST['frequency'] > 0) {

  $time_intervals = array();
  if ($_POST['frequency'] == 24) {
    $time_intervals = array(
      '24'   => 'Daily',
    );
  }

  if ($_POST['frequency'] == 168) {
    $time_intervals = array(
      '24'   => 'Daily',
      '168'  => 'Weekly',
    );
  }

  if ($_POST['frequency'] == 336) {
    $time_intervals = array(
      '24'   => 'Daily',
      '168'  => 'Weekly',
    );
  }

  if ($_POST['frequency'] == 720) {
    $time_intervals = array(
      '24'   => 'Daily',
      '168'  => 'Weekly',
      '720'  => 'Monthly',
    );
  }

  if ($_POST['frequency'] == 2160 || $_POST['frequency'] == 4320 || $_POST['frequency'] == 8640) {
    $time_intervals = array(
      '168'  => 'Weekly',
      '720'  => 'Monthly',
    );
  }

  $html = '<select class="form-control" id="time_period" name="time_period" required>';
  if (count($time_intervals) > 0) {
    foreach ($time_intervals as $key => $val) {
      $html .= '<option value="' . $key . '" >' . $val . '</option>';
    }
  }
  $html .= '</select>';

  echo $html;
  die();
}
