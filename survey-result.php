<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/5.0.7/sweetalert2.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/5.0.7/sweetalert2.min.css">
<?php 
include('function/function.php');
include('function/get_data_function.php');
//Get Survey Details

$surveyid = $_GET['surveyid'];
$cby_userid = $_GET['userid'];
//$client_id = '';
if(isset($_GET['surveyid'])){
  record_set("get_survey", "select * from surveys where id='".$surveyid."' and cstatus=1");
  if($totalRows_get_survey > 0){
    $row_get_survey = mysqli_fetch_assoc($get_survey);
    //$client_id = $row_get_survey['clientid'];
  }else{
    echo 'Wrong survey ID.'; 
    exit;
  }
}else{
  echo 'Missing survey ID.';  
  exit;
}

$co_action = "";
$contact_comment="";
$created_date ="";
$showAllComment =[];

if($_SESSION['user_type'] == 1 && !isset($_POST['submit'])){
  $survey_id =  $_GET['surveyid'];
  $surveyData = get_survey_detail($survey_id);
  $tasks = $_GET['userid'];
  $assing_to_user_id = $_SESSION['user_id'];
  $survey_type_id = $surveyData['survey_type'];

  $data = array(
    "assign_to_user_id"   => $assing_to_user_id,
    "task_id"             => $tasks,
    "survey_id"           => $survey_id,
    "survey_type"         => $survey_type_id,
    "task_status"         => 2,
    "assign_by_user_id"   => $assing_to_user_id,
    "cdate"               => date("Y-m-d H:i:s")
  );
  // check the assign task already exists for this user or not
  record_set("check_assign_task", "SELECT * FROM assign_task where assign_to_user_id = $assing_to_user_id  and task_id = $tasks and survey_id = $survey_id");
  $row_check_assign_task = mysqli_fetch_assoc($check_assign_task);

  if($totalRows_check_assign_task > 0 ){
    //$insert_value = dbRowUpdate("assign_task", $data, "where id=".$row_check_assign_task['id']);
  }else {
    $insert_value = dbRowInsert("assign_task",$data);
    $data_contact_action = array(
      "user_id"=> $tasks,
      "action"=> 2,
      "cby" =>$assing_to_user_id,
      "comment"=> 'response assigned to '.$_SESSION['user_name'],
      'created_date'=>date("Y-m-d H:i:s")
    );
    $insert_contact_action =  dbRowInsert("survey_contact_action",$data_contact_action);
  }
  // $data_contact_action = array(
  //   "user_id"=> $tasks,
  //   "action"=> 2,
  //   "cby" =>$assing_to_user_id,
  //   "comment"=> 'response assigned to '.$_SESSION['user_name'],
  //   'created_date'=>date("Y-m-d H:i:s")
  // );
  // $insert_contact_action =  dbRowInsert("survey_contact_action",$data_contact_action);
}


if(isset($_GET['userid'])){
record_set("get_contact_action", "select * from survey_contact_action where user_id='".$_GET['userid']."'");
if($totalRows_get_contact_action > 0){ 
$i = 0;
  while($row_get_contact_action = mysqli_fetch_assoc($get_contact_action)){
    if($row_get_contact_action['action'] == 1){
      $showAllComment[$i]['action']='UNASSIGNED';
      $showAllComment[$i]['status']=$row_get_contact_action['action'];
    }
    if($row_get_contact_action['action'] == 2){
      $showAllComment[$i]['action']='ASSIGNED';
      $showAllComment[$i]['status']=$row_get_contact_action['action'];
    }
    if($row_get_contact_action['action'] == 3){
      $showAllComment[$i]['action']='IN PROGRESS';
      $showAllComment[$i]['status']=$row_get_contact_action['action'];
    }
    if($row_get_contact_action['action'] == 4){
      $showAllComment[$i]['action']='VOID';
      $showAllComment[$i]['status']=$row_get_contact_action['action'];
    }
    if($row_get_contact_action['action'] == 5){
      $showAllComment[$i]['action']='RESOLVED-POSITIVE';
      $showAllComment[$i]['status']=$row_get_contact_action['action'];
    }
    if($row_get_contact_action['action'] == 6){
      $showAllComment[$i]['action']='RESOLVED-NEGATIVE';
      $showAllComment[$i]['status']=$row_get_contact_action['action'];
    }
    
    $showAllComment[$i]['comment']=$row_get_contact_action['comment'];
    $showAllComment[$i]['created_date']=$row_get_contact_action['created_date'];

    $showAllComment[$i]['cby_user_id']=$row_get_contact_action['cby'];
    $i++;
  }
}

record_set("get_contact_action_single", "select * from survey_contact_action where user_id='".$_GET['userid']."' order by action desc");
  if($totalRows_get_contact_action_single > 0){ 
    
    $row_get_contact_action_single = mysqli_fetch_assoc($get_contact_action_single);
    if($row_get_contact_action_single['action'] == 1){
      $co_action = "In progress";
      $contact_comment = $row_get_contact_action_single['comment'];
      $created_date=$row_get_contact_action_single['created_date'];

    }else if($row_get_contact_action_single['action'] == 2){
      $co_action =  "Void";
      $contact_comment = $row_get_contact_action_single['comment'];
      $created_date=$row_get_contact_action_single['created_date'];

    }else if($row_get_contact_action_single['action'] == 3){
      $co_action =  "Resolved-Positive";
      $contact_comment = $row_get_contact_action_single['comment'];
      $created_date=$row_get_contact_action_single['created_date'];
    }
    else if($row_get_contact_action_single['action'] == 4){
      $co_action =  "Resolved-Negative";
      $contact_comment = $row_get_contact_action_single['comment'];
      $created_date=$row_get_contact_action_single['created_date'];
    }
  }
 
}

record_set("get_survey_id", "select surveyid from answers where cby='".$_GET['userid']."'");
if($totalRows_get_survey_id > 0){
  $row_get_survey_id = mysqli_fetch_assoc($get_survey_id);
  $survey_id = $row_get_survey_id['surveyid'];
}
if(isset($_POST['contact_action']) && $_POST['contact_action'] != ""){

  $user_id = $_GET['userid'];
  record_set("total_contact", "select * from survey_contact_action where user_id=".$user_id." and action=".$_POST['contact_action']."  and cby=".$_SESSION['user_id']."");
 
  if($totalRows_total_contact > 0){
    $data_contact_action_update = array(
      "action"=> $_POST['contact_action'],
      "comment"=> $_POST['comment'],
      'created_date'=>date("Y-m-d H:i:s")
    );
    $whereCondition = 'user_id='.$user_id.' and action='.$_POST['contact_action'];
    $update_contact_action =  dbRowUpdate("survey_contact_action",$data_contact_action_update,$whereCondition);
    // send mail if status change to Resolve-negative
    if($_POST['contact_action'] == 5 OR $_POST['contact_action'] == 6){
      record_set("get_survey_id", "select surveyid from answers where cby='".$_GET['userid']."'");
      if($totalRows_get_survey_id > 0){ 
        $row_get_survey_id = mysqli_fetch_assoc($get_survey_id);
        $survey_id = $row_get_survey_id['surveyid'];
        $user_data = get_admin_manager_of_survey($survey_id);
        foreach($user_data as $key => $value){
          //for admin manager
          if($value['user_type']==3 OR $value['user_type']==4){
            $uemail = $value['email'];
            $uname  = $value['name'];
            send_email_to_assign_user($uemail,$uname);
          }
        }
      }
    }  
    // send mail to super admin and admin on survey result submit
    $user_data = get_admin_manager_of_survey($_GET['surveyid']);
    foreach($user_data as $key => $value){
      //for super admin and admin
      if($value['user_type']==2 OR $value['user_type']==3){
        $uemail = $value['email'];
        $uname  = $value['name'];
        survey_result_submitted_pdf_mail($uemail,$uname);
      }
    }
    if($update_contact_action){

      header("Refresh:0");
    }
  }else{
    $data_contact_action = array(
      "user_id"=> $_GET['userid'],
      "action"=> $_POST['contact_action'],
      "cby" =>$_SESSION['user_id'],
      "comment"=> $_POST['comment'],
      'created_date'=>date("Y-m-d H:i:s")
    );
    $data =array(
      'task_status' => $_POST['contact_action'],
    );
    record_set("total_assign_task", "select * from assign_task where assign_to_user_id =".$_SESSION['user_id']." and task_id=".$_GET['userid']);
    if($totalRows_total_assign_task > 0){ 
      $insert_value=	dbRowUpdate("assign_task", $data, "where assign_to_user_id =".$_SESSION['user_id']." and task_id=".$_GET['userid']);
      if($insert_value>0){
        $insert_contact_action =  dbRowInsert("survey_contact_action",$data_contact_action);
      }
    }
    else {
      $mess = "Only the assigned user can update this task";
      alertdanger($mess,'');
    }


    // send mail if status change to Resolve-negative
    if($_POST['contact_action'] == 5 OR $_POST['contact_action'] == 6){
      record_set("get_survey_id", "select surveyid from answers where cby='".$_GET['userid']."'");
      if($totalRows_get_survey_id > 0){ 
        $row_get_survey_id = mysqli_fetch_assoc($get_survey_id);
        $survey_id = $row_get_survey_id['surveyid'];
        $user_data = get_admin_manager_of_survey($survey_id);
        foreach($user_data as $key => $value){
          //for admin manager
          if($value['user_type']==3 OR $value['user_type']==4){
            $uemail = $value['email'];
            $uname  = $value['name'];
            send_email_to_assign_user($uemail,$uname);
          }
        }
      }
    }  
    // send mail to super admin and admin on survey result submit
    $user_data = get_admin_manager_of_survey($_GET['surveyid']);
    foreach($user_data as $key => $value){
      //for super admin and admin
      if($value['user_type']==2 OR $value['user_type']==3){
        $uemail = $value['email'];
        $uname  = $value['name'];
        survey_result_submitted_pdf_mail($uemail,$uname);
      }
    }

    if($insert_contact_action){
      header("Refresh:0");
    }

  }

}

//filter
$ans_filter_query='';
if($_REQUEST['userid']){
  $ans_filter_query .= " and cby='".$_REQUEST['userid']."' ";
}
if($_REQUEST['month']){
  $ans_filter_query .= " and cdate like '".$_REQUEST['month']."-%' ";
}

//Survey Steps 
$survey_steps = array();
if($row_get_survey['isStep'] == 1){
  record_set("get_surveys_steps", "select * from surveys_steps where survey_id='".$surveyid."' order by step_number asc");
  while($row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps)){
    $survey_steps[$row_get_surveys_steps['id']]['number'] = $row_get_surveys_steps['step_number'];
    $survey_steps[$row_get_surveys_steps['id']]['title'] = $row_get_surveys_steps['step_title'];
  }
}

//Survey Questions
record_set("get_questions", "select * from questions where surveyid='".$surveyid."' and cstatus='1' and parendit='0' order by dposition asc");
$questions = array();
while($row_get_questions = mysqli_fetch_assoc($get_questions)){
  if($row_get_questions['survey_step_id'] != 0){
    $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
    $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
    $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
    $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
  }
  if($row_get_questions['survey_step_id'] == 0){
    $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
    $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
    $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
    $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
  }
}

?>
<!DOCTYPE HTML>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $row_get_survey['name']; ?></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
    body{
      color: #000;
    }
    td, th {
      padding: 5px ;
    }
    .container{
      width:80%;
      margin-left: 10%;
    }
    </style>
  </head>
  <body>

  <div id="reportPage">
    <div align="center"><img src="<?=MAIN_LOGO?>" width="200"></div>
    <h2 align="center" style="margin:20px;"> <?= strtoupper($row_get_survey['name']); ?> </h2>
    <?php 
    record_set("get_loc_dep", "select locationid,groupid,roleid,departmentid from answers where surveyid='".$surveyid."' ".$ans_filter_query);
    $row_get_loc_dep = mysqli_fetch_assoc($get_loc_dep);
    
    //Department
    record_set("get_department", "select name from departments where id = '".$row_get_loc_dep['departmentid']."'");
    $row_get_department = mysqli_fetch_assoc($get_department);

    //Location
    record_set("get_location", "select name from locations where id = '".$row_get_loc_dep['locationid']."'");
    $row_get_location = mysqli_fetch_assoc($get_location);

     //School
    //  record_set("get_school", "select answertext from answers where surveyid ='".$surveyid."' and answerid='-3' and answerval='100' and cstatus=1");

    //Contact Details
    record_set("get_contact", "select answertext from answers where surveyid ='".$surveyid."' and cby='".$cby_userid."' and answerid='-2' and answerval='100' and cstatus=1");
    $row_get_contact = mysqli_fetch_assoc($get_contact);
    $contactDetails = json_decode($row_get_contact['answertext'],1);

    ?>
    <div class="container">
      <table style="font-size:14px;width:100%;" align="center"  cellspacing="0" cellpadding="4" >
        <thead style="border-top:1px solid black;border-bottom:1px solid black;">
          <tr>
            <th class="thead" style="width:5%;">Group:</th>
            <th class="thead" style="width:45%;padding: 0px;"><?php echo getGroup()[$row_get_loc_dep['groupid']] ?></th>
            <th class="thead" style="width:5%;">Location:</th>
            <th class="thead" style="width:45%;padding: 0px;"><?php echo getLocation()[$row_get_loc_dep['locationid']] ?></th>
          </tr>
        </thead>
        <thead style="border-top:1px solid black;border-bottom:1px solid black;">
          <tr>
          <th class="thead" style="width:5%;">Department:</th>
            <th class="thead" style="width:45%;padding: 0px;"><?php echo getDepartment()[$row_get_loc_dep['departmentid']] ?></th>
            <th class="thead" style="width:5%;">Role:</th>
            <th class="thead" style="width:45%;padding: 0px;"><?php echo getRole()[$row_get_loc_dep['groupid']] ?></th>
          </tr>
        </thead>
      </table>
      <?php if($_GET['contacted'] == 1){ ?>
        <table style="font-size:14px;width:100%;" align="center"  cellspacing="0" cellpadding="4" >
          <thead>
            <tr>
              <th colspan="3"></th>
            </tr>
            <tr>
              <th colspan="3">Contact Details</th>
            </tr>
          </thead>

          <tbody style="border-bottom:1px solid black;">
              <?php 
                $name =  $contactDetails['first_name'].' '.$contactDetails['last_name'];
              ?>
              <tr>
                <td><strong>Name</strong> : <?= $contactDetails['first_name'].' '.$contactDetails['last_name'] ?> </td>
                <td><strong>Phone Number</strong> : <?= $contactDetails['phone_number'] ?></td>
                <td><strong>Contact Email</strong> : <?= $contactDetails['to_be_contact_mail'] ?></td>
              </tr>
          </tbody>
        </table>
      <?php } ?>
      <?php if(isset($_GET['score'])){ ?> 
        <h2 class="text-center" style="margin-top:20px;">SURVEY SCORE: <?=$_GET['score'] ?>%</h2>
      <?php } ?>
    </div>
    
        <div class="container">
          <?php 
            if(count($survey_steps)> 0) { 
              foreach($survey_steps AS $key => $value) {  ?>
                <div class="">  
                  <h4 align="center" style="margin-top:20px;margin-bottom:10px;"><?php echo $value['title']; ?></h4>
                  <table style="font-size:14px;width:100%;" border ="1" cellspacing="0" cellpadding="4" align="center">
                    <thead>
                      <tr>
                        <th scope="col" style="width:40px;">#</th>
                        <th scope="col" style="border-left:none;border-right:none;width:600px;">QUESTION</th>
                        <th scope="col" style="width:500px;">ANSWER</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      $i=0;
                    
                        foreach($questions[$key] AS $question){
                          $i++;
                          $questionid = $question['id']; 
                          $answer_type = $question['answer_type'];
                          $totalRows_get_child_questions = 0;
                          $questions_array = array();
                          $answers_array = array();
                        // for radio rating dropdown
                      if($answer_type == 1 || $answer_type == 4 || $answer_type == 6){
                        record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'"); 
                        if($totalRows_get_questions_detail>0){
                          while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
                            if($row_get_questions_detail['condition_yes_no'] == 1){
                              $questions_array[$row_get_questions_detail['id']] = $row_get_questions_detail['description'].' (Conditional)';
                            }else{
                              $questions_array[$row_get_questions_detail['id']] = $row_get_questions_detail['description'];
                            }
                            
                          }
                        }
                        record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' ");  
                        if($totalRows_get_answers>0){
                          while($row_get_answers = mysqli_fetch_assoc($get_answers)){
                            $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
                          }
                        }
                        $question = $question['question'];
                        foreach($answers_array as $key => $value){
                          $answer_value =  $questions_array[$value];
                        }
                      }
                      // textbox or textarea
                      if($answer_type == 2 || $answer_type == 3){
                        record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' ");  
                          if($totalRows_get_answers>0){
                            while($row_get_answers = mysqli_fetch_assoc($get_answers)){
                            //print_r($row_get_answers);
                            if($row_get_answers['answerid']==0){
                              $answers_array[$row_get_answers['questionid']] = $row_get_answers['answertext'];
                            }else{
                              $answers_array[$row_get_answers['id']] = 0011;
                            }
                          
                          }
                        }
                        foreach($answers_array as $key => $value){
                          if($key == $question['id']){
                            $question = $question['question'];
                            $answer_value = $value;
                          }
                        }
                      } 
                      if($answer_type !=5){ ?>
                      <tr>
                        <td scope="row" class="remove-bt"><?=$i?></td>
                        <td class="remove-bt" style="border-left:none;border-right:none;"><?=(is_array($question) ? $question['question'] : $question )?></td>
                        <td class="remove-bt" ><?=$answer_value ?? 'N/A' ?></td>
                      </tr>
                      <?php } }
                      ?>
                    </tbody>
                  </table>
                </div>
              <?php 
              } 
            }else{
               ?>
              <div class="">  
                  <h4 align="center" style="margin-top:20px;margin-bottom:10px;"><?php echo $value['title']; ?></h4>
                  <table style="font-size:14px;width:100%;" border ="1" cellspacing="0" cellpadding="4" align="center">
                    <thead>
                      <tr>
                        <th scope="col" style="width:40px;">#</th>
                        <th scope="col" style="border-left:none;border-right:none;width:600px;">QUESTION</th>
                        <th scope="col" style="width:500px;">ANSWER</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                        $i=0;
                        foreach($questions[0] AS $question){
                          $i++;
                          $questionid = $question['id']; 
                          $answer_type = $question['answer_type'];
                          $totalRows_get_child_questions = 0;
                          if($answer_type == 1 || $answer_type == 4 || $answer_type == 6){
                            record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'"); 
                            if($totalRows_get_questions_detail>0){
                              while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
                                if($row_get_questions_detail['condition_yes_no'] == 1){
                                  $questions_array[$row_get_questions_detail['id']] = $row_get_questions_detail['description'].' (Conditional)';
                                }else{
                                  $questions_array[$row_get_questions_detail['id']] = $row_get_questions_detail['description'];
                                }
                                
                              }
                            }
                            record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' ");  
                            if($totalRows_get_answers>0){
                              while($row_get_answers = mysqli_fetch_assoc($get_answers)){
                                $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
                              }
                            }
                            $question = $question['question'];
                            foreach($answers_array as $key => $value){
                              $answer_value =  $questions_array[$value];
                            }
                          }
                          // textbox or textarea
                          if($answer_type == 2 || $answer_type == 3){
                            record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' ");  
                              if($totalRows_get_answers>0){
                                while($row_get_answers = mysqli_fetch_assoc($get_answers)){
                                //print_r($row_get_answers);
                                if($row_get_answers['answerid']==0){
                                  $answers_array[$row_get_answers['questionid']] = $row_get_answers['answertext'];
                                }else{
                                  $answers_array[$row_get_answers['id']] = 0011;
                                }
                              
                              }
                            }
                            foreach($answers_array as $key => $value){
                              if($key == $question['id']){
                                $question = $question['question'];
                                $answer_value = $value;
                              }
                            }
                          } 
                          if($answer_type !=5){
                            ?>
                           <tr>
                            <td scope="row" class="remove-bt"><?=$i?></td>
                            <td class="remove-bt" style="border-left:none;border-right:none;"><?=(is_array($question) ? $question['question'] : $question )?></td>
                            <td class="remove-bt" ><?=$answer_value ?? 'N/A' ?></td>
                          </tr>
                          <?php
                        }}
                      ?>
                    </tbody>
                  </table>
                </div>
                <?php 
            }
          ?>
            <br>
          <div class="row">
            <?php if(count($showAllComment) > 0 ) { ?>
            <h2 align="center" style="margin-top:20px;margin-bottom:10px;">CONTACT</h2>
            <?php } ?>
            <?php 
            foreach($showAllComment as $comm) { ?>
              <hr style="border: 0.5px solid #d3cccc;"/>
                <div class="col-md-12" style="text-align: center;">
                   <div class="col-md-4">
                       <div class="col-md-6" style="padding: 0px;"><strong>STATUS UPDATED TO :</strong></div>
                       <div class="col-md-6" style="padding: 0px;text-align:left;"><?php echo $comm['action']; ?></div> 
                   </div>
                   <?php ?>
                   <div class="col-md-4">
                         <div class="col-md-5" style="padding: 0px;text-align: right;"><strong>USERNAME :</strong></div>
                         <div class="col-md-7" style="padding: 0px 5px;text-align:left;"><?php echo get_user_datails($comm['cby_user_id'])['name'] ?></div>
                   </div>
                   <div class="col-md-4">
                         <div class="col-md-7" style="padding: 0px;text-align: right;"><strong>CONTACTED ON :</strong></div>
                         <div class="col-md-5" style="padding: 0px;text-align: right;"><?php echo date('d-m-Y h:s:i',strtotime($comm['created_date'])); ?></div>
                   </div>
                </div>
                <div class="col-md-12" style="margin: 43px 0px 40px 0px;">
                   <div class="col-md-8">
                     <div class="col-md-6" style="text-align:right;padding: 0px;"><strong>COMMENT :</strong></div>
                     <div class="col-md-6" style="text-align:left;padding: 0px 5px;"><?php echo $comm['comment']; ?></div>
                   </div>
               </div>
              <hr style="border: 0.5px solid #d3cccc;"/>
             <?php } ?>
             <?php if($_GET['status']== 'assign' || $_SESSION['user_type'] == 1) { ?>
              <form id="contactActionForm" role="form" method="POST">
                <div class="row notforpdf" style="text-align: center;margin-top: 20px;">
                  <div class="col-md-12">
                    <div class="col-md-6" >
                      <label for="contact-date" style="font-size:14px;"><strong>CONTACT DATE</strong></label>
                      <div class="form-group">
                        <input type="date" name="" id="" class="form-control" max="<?=date('2050-01-01')?>" min="<?=date('Y-m-d')?>" value="<?= date('Y-m-d')?>">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label for="contact-date" style="font-size:14px;"><strong>CONTACT STATUS</strong></label>
                      <div class="form-group">
                        <select id="contact_action" name="contact_action" class="form-control" required="required">
                          <?php foreach(assign_task_status() as $key => $value) { 
                             if($key <= $comm['status']){
                               continue;
                             }
                            ?>
                            
                            <option value="<?=$key?>" <?=($comm['status'] == $key) ? 'selected' : '' ?>><?=$value?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="col-md-2"></div>
                      <div class="col-md-8">
                        <label for="contact-date" style="font-size:14px;"><strong>CONTACT COMMENT</strong></label>
                        <div class="form-group">
                            <textarea class="form-control" name="comment" placeholder="Comments" required="required"></textarea>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="col-md-2"></div>
                      <div class="col-md-8">
                        <div class="form-group">
                          <input type="submit" name="submit" value="Submit" class="btn btn-primary">
                        </div>
                      </div>
                    </div>
                  </div>
                </div> 
              </form>
            <?php } ?>
          </div>
          <?php //} ?>
          </div>
        </div>
    </div>
<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>
<!-- Resources -->
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-html2canvas@latest/dist/jspdf-html2canvas.min.js"></script> 
<script>
    // start export pdf 
    const pages = document.getElementById('reportPage');
    $('#exportPDF').click(function(){
        $('.notforpdf').addClass('d-none');
        html2PDF(pages, {
            margin: [50,10],//PDF margin (in jsPDF units). Can be a single number, [vMargin, hMargin], or [top, left, bottom, right].
            jsPDF: {
                orientation: "p",
                unit: "in",
                format: 'letter',
            },
            html2canvas: { scale: 2 },
            imageType: 'image/jpeg',
            output: '.<?php echo $row_get_survey['name']; ?>/pdf/<?=date('Y-m-d-H-i-s')?>.pdf'
        });
        setTimeout(function(){
            window.location.reload();
        }, 2000);
    });
 // End export pdf
 </script>
<div style="text-align: center;">
<?php echo POWERED_BY; ?>
<center><img  src="<?= BASE_URL.FOOTER_LOGO?>" alt="" width="150"/></center>
</div>
</body>
</html>

