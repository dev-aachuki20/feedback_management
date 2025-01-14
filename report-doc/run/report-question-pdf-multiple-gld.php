<?php
// include('../../function/function.php');
// include('../../function/get_data_function.php');

$filter = $_POST;

$data_type = $filter['sch_template_field_name'];
$surveyId   = $filter['survey'];
$field_value = '';
$selected_template_fields = array();

if (isset($filter['template_field'])) {
  if (is_array($filter['template_field']) && count($filter['template_field']) > 1) {
    $field_value = implode(',', $filter['template_field']);
  } else {
    echo 'CASE- Single Group/Location/Department is selected';
    exit;
  }
}

if (isset($surveyId)) {
  record_set("get_survey", "select * from surveys where id IN ($surveyId) and cstatus=1");
  if ($totalRows_get_survey > 0) {
    $row_get_survey = mysqli_fetch_assoc($get_survey);
  } else {
    echo 'Wrong survey ID.';
    exit;
  }
} else {
  echo 'Missing survey ID.';
  exit;
}

$ans_filter_query = '';
if ($data_type == 'location' && $field_value != '') {
  $ans_filter_query .= " and locationid IN($field_value)";
  record_set("get_location", "select name from `locations` where id IN ($field_value)");
  $selected_template_fields = mysqli_fetch_assoc($get_location);
}
if ($data_type == 'department' && $field_value != '') {
  $ans_filter_query .= " and departmentid IN($field_value)";
  record_set("get_department", "select name from `departments` where id IN ($field_value)");
  $selected_template_fields = mysqli_fetch_assoc($get_department);
}
if ($data_type == 'group' && $field_value != '') {
  $ans_filter_query .= " and groupid IN($field_value)";
  record_set("get_group", "select name from `groups` where id IN ($field_value)");
  $selected_template_fields = mysqli_fetch_assoc($get_group);
}

if (!empty($filter['start_date']) and !empty($filter['end_date'])) {
  $ans_filter_query .= " and  cdate between '" . date('Y-m-d', strtotime($filter['start_date'])) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($filter['end_date']))) . "'";
}

// Get survey questions. 
record_set("get_questions", "select * from questions where surveyid='" . $surveyId . "' and cstatus='1' and parendit='0' order by dposition asc");
$surveyQuestions = array();

while ($row_get_question = mysqli_fetch_assoc($get_questions)) {
  $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['id'] = $row_get_question['id'];
  $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['question'] = $row_get_question['question'];
  $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['ifrequired'] = $row_get_question['ifrequired'];
  $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['answer_type'] = $row_get_question['answer_type'];

  foreach ($selected_template_fields as $fieldKey => $field_val) {
      echo $filed_val.'';
  }
    // Get answer values attempted.
  record_set("get_questions_answers", "select * from answers where surveyid='" . $surveyId . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_question['id'], 1);
  $answer_type = $row_get_question['answer_type'];

    //Survey Questions
    record_set("get_questions", "select * from questions where surveyid='" . $surveyId . "' and cstatus='1' and parendit='0' order by dposition asc");
    $questions = array();
    while ($row_get_questions = mysqli_fetch_assoc($get_questions)) {
      $answer_type = $row_get_questions['answer_type'];
      /* Get answer values attempted */
      record_set("get_questions_answers", "select * from answers where surveyid='" . $surveyId . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_questions['id']." order By cdate asc");
      if (!empty($totalRows_get_questions_answers)) {
        while ($row_get_questions_answers = mysqli_fetch_assoc($get_questions_answers)) {
          $created_date = date('d-m-Y', strtotime($row_get_questions_answers['cdate']));
          $data_type_id = $row_get_questions_answers[$data_type.'id'];

          $questions[$data_type_id][$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
          $questions[$data_type_id][$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
          $questions[$data_type_id][$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
          $questions[$data_type_id][$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];

          if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
            $questions[$data_type_id][$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$created_date][$row_get_questions_answers['answerid']] += 1;
          } else if ($answer_type == 2 || $answer_type == 3) {
            $questions[$data_type_id][$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$created_date][] = ($row_get_questions_answers['answertext']) ? $row_get_questions_answers['answertext'] : 'UnAnswered';
          }
        }
      } else if ($answer_type == 1) {
        // record_set("get_child_questions", "select * from questions where parendit='" . $row_get_questions['id'] . "' and cstatus='1'");
        // if (!empty($totalRows_get_child_questions)) {
        //   $created_date = date('d-m-Y', strtotime($row_get_questions_answers['cdate']));
        //   $data_type_id = $row_get_questions_answers[$data_type.'id'];

        //   $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
        //   $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
        //   $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
        //   $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];

        //   $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['having_child'] = true;
    
        //   record_set("get_parent_question_options", "select id from questions_detail where surveyid='" . $surveyId . "' and questionid = " . $row_get_question['id']);
        //   $options = array();
        //   if (!empty($totalRows_get_parent_question_options)) {
        //     while ($row_parent_question_option = mysqli_fetch_assoc($get_parent_question_options)) {
        //       $options[$row_parent_question_option['id']] = 0;
        //     }
        //   }
    
        //   while ($row_get_child_question = mysqli_fetch_assoc($get_child_questions)) {
        //     $questions[$row_get_question['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['id'] = $row_get_child_question['id'];
        //     $questions[$row_get_question['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['question'] = $row_get_child_question['question'];
        //     $questions[$row_get_question['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['ifrequired'] = $row_get_child_question['ifrequired'];
        //     $questions[$row_get_question['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['answer_type'] = $row_get_child_question['answer_type'];
    
        //     $questions[$row_get_question['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['survey_responses'] = $options;
    
        //     record_set("get_child_questions_answers", "select * from answers where surveyid='" . $surveyId . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_child_question['id']);
    
        //     if (!empty($totalRows_get_child_questions_answers)) {
        //       while ($row_get_child_questions_answer = mysqli_fetch_assoc($get_child_questions_answers)) {
        //         $questions[$row_get_question['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['survey_responses'][$row_get_child_questions_answer['answerid']]  += 1;
        //       }
        //     }
        //   }
        // }
      } 
    }
}
$html = '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Survey Report Question</title>
        <style>
            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td,
            th {
                border: 1px solid #0a0a0a;
                text-align: left;
                padding: 3px 4px;
            }

            th[rowspan="2"]{
                background-color: #dddddd9e;
            }

        </style>
    </head>
    <body>';
    $surveyName = getSurvey()[$surveyId];
    $html .='<div align="center"><img src="'.getHomeUrl().MAIN_LOGO.'"  width="200"></div>';
    $html .='<h3 align="center" style="border-bottom:1px solid gray;padding-bottom: 8px;margin-top:1px;margin-bottom: 0;font-family: Arial, Helvetica, sans-serif;">'.$surveyName.'</h3>';
    $valData = 1;
    if(count($questions)> 0){
      foreach($questions as $id => $step){
        $dataTypeName =""; 

        if($valData != 1){
          $html .= "<pagebreak />";
        }
        $valData++;
        if($data_type == 'location'){
          $dataTypeName = getLocation()[$id];
        }else if ($data_type == 'group') {
          $dataTypeName = getGroup()[$id];
        }else if ($data_type == 'department') {
          $dataTypeName = getDepartment()[$id];
        }
        
        /* Break the page and show the survey name,dataTypeName and date range for all the new page  */
        // $html .='<h4 align="center" style="border-bottom:1px solid gray">'.$surveyName.'</h4>';
        $html .='<h4 align="center" style="margin-bottom: 0;margin-top: 9px;font-family: Arial, Helvetica, sans-serif;">'.$dataTypeName.'</h4>';
        if (!empty($filter['start_date']) and !empty($filter['end_date'])) {
          $html .= '<h4 align="center" style="margin-top: 6px;margin-bottom: 6px;font-family: Arial, Helvetica, sans-serif;">' 
                    . date('d/m/Y', strtotime($filter['start_date'])) . '-' . date('d/m/Y', strtotime($filter['end_date'])) . 
                  '</h4>';
        }
        foreach($step as $stepId => $question){
            $surveyStep = record_set_single("get_survey_step", "SELECT step_title FROM surveys_steps where id =" . $stepId);
            $surveyStepName = strtoupper(trim($surveyStep['step_title']));
            $html .='<h4 align="center" style="margin-top: 6px;margin-bottom: 0;font-family: Arial, Helvetica, sans-serif;">'.$surveyStepName.'</h4>';
            foreach($question as $ques){
                  if($ques['answer_type'] == 2 || $ques['answer_type'] == 3){
                    $html .='<h4 align="center" style="margin-top:3px;font-family: Arial, Helvetica, sans-serif;margin-bottom: 5px;">'.$ques['question'].'</h4>';
                    $html .= '<table width="100%" align="center" style="font-size:14px;margin-bottom: 13px;font-family: Arial, Helvetica, sans-serif;margin-top: 0;" border="1" cellspacing="0" cellpadding="4">
                      <tr style="background-color:#f0f0f0;">
                        <th></th>
                        <th>RESPONDENT</th>
                        <th>ANSWERS</th>
                      </tr>';

                    foreach($ques['survey_responses'] as $date_key => $reponses){
                      $html .='<tr>';
                      $ftrr = count($reponses);
                      $html .= '<th rowspan="'.$ftrr.'" style="background-color: #f0f0f0;">'.$date_key.'</th>';
                      $i=1;
                      foreach($reponses as $respondent_key => $text_reponse){
                        $counter = $respondent_key+1;
                        if($i == 1){
                          $html .= '<td>'.$counter.'</td>
                                    <td>'.$text_reponse.'</td><tr>';   
                        }else{
                          $html .= '<tr>
                                      <td>'.$counter.'</td>
                                      <td>'.$text_reponse.'</td>
                                    </tr>';
                        }
                      }
                    }
                    $html .= '</table>';
                  }else{
                    $html .='<h4 align="center" style="margin-top:3px;font-family: Arial, Helvetica, sans-serif;margin-bottom: 5px;">'.$ques['question'].'</h4>
                      <table class="table table-design mb-4" style="font-family: Arial, Helvetica, sans-serif;margin-bottom: 13px;">
                        <tbody>
                            <tr style="background-color: #f0f0f0;">
                              <th></th>
                              <th></th>
                              <th>RESULT</th>
                              <th>RESPONSE</th>
                            </tr>';

                    foreach($ques['survey_responses'] as $key => $value){
                      record_set("get_questions_detail", "select * from questions_detail where surveyid=$surveyId  and questionid= ".$ques['id']." and cstatus=1");
                      $i=0;
                      while ($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)) {
                        $i++;
                        $scoreCount = 0 ;
                        $responseCount = array_sum(array_values($value));
                        if($responseCount == 0){
                          $scoreValue = '0%';
                        }else{
                          $perPercent = 100/$responseCount;
                          $scoreCount = ($value[$row_get_questions_detail['id']]) ? $value[$row_get_questions_detail['id']] : 0;
                          $scoreValue = round($scoreCount * $perPercent, 2)."%";
                        }
                        $html .='<tr>';
                        if($i==1){
                          $html .='<th rowspan="'.$totalRows_get_questions_detail.'" style="background-color: #f0f0f0;">'.$key.'</th>';
                        }
                          $html .=' <td>'.$row_get_questions_detail['description'].' </td>
                                  <td>'.$scoreValue.'</td>
                                  <td>'.$scoreCount.'</td>
                                </tr>';
                      }
                    }
                      $html .='</tbody>
                    </table>';
                  }
            }
        }
      }
    }else{
      $html .="<h2 align='center' style='font-family: Arial, Helvetica, sans-serif;'>No Data Found</h2>";
    }
    $html .='</body>
</html>';
create_mpdf($html, 'Survey Report Question -' . date('Y-m-d-H-i-s') . '.pdf', 'D');