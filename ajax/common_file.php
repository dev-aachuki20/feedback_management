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
      $data = get_allowed_data('surveys',$_SESSION['id']);
    
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
            foreach($data as $key => $value){
                $html .='<option value="'.$key.'"';
                if($_POST['type']==$key){
                  echo 'selected';
                }
                $html .='>'.$value.'</option>';
            }
        $html .=' </select>
        <span class="error" style="display:none;">This Field is Required</span>
      </div>';
    }
  echo json_encode($html); die();
}
?>