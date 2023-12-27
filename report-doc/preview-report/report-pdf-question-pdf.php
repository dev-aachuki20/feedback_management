<?php
include('../../function/function.php');
include('../../function/get_data_function.php');

$filter = $_POST;

$data_type = $filter['sch_template_field_name'];
$surveyId   = $filter['survey'];
// $field_value = implode(',', $filter['template_field']);

// echo '<pre>';
// print_r($filter);
// echo '</pre>';

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

// echo '<pre>';
// print_r($row_get_survey);
// echo '</pre>';

$ans_filter_query = '';
if ($data_type == 'location' && $field_value != '') {
  $ans_filter_query .= " and locationid IN($field_value)";
}
if ($data_type == 'department' && $field_value != '') {
  $ans_filter_query .= " and departmentid IN($field_value)";
}
if ($data_type == 'group' && $field_value != '') {
  $ans_filter_query .= " and groupid IN($field_value)";
}


if (!empty($filter['start_date']) and !empty($filter['end_date'])) {
  $ans_filter_query .= " and  cdate between '" . date('Y-m-d', strtotime($filter['start_date'])) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($filter['end_date']))) . "'";
}

// echo '<pre>';
// print_r($ans_filter_query);
// echo '</pre>';


//Survey Questions
record_set("get_questions", "select * from questions where surveyid='" . $surveyId . "' and cstatus='1' and parendit='0' order by dposition asc");

$surveyQuestions = array();
while ($row_get_questions = mysqli_fetch_assoc($get_questions)) {
  $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
  $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
  $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
  $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
}

// echo '<pre>';
// print_r($surveyQuestions);
// echo '</pre>';

// record_set("get_loc_dep", "select locationid, departmentid from answers where surveyid='" . $surveyId . "' " . $ans_filter_query, 1);
// $row_get_loc_dep = mysqli_fetch_assoc($get_loc_dep);

// echo '<pre>';
// print_r($row_get_loc_dep);
// echo '</pre>';


//  Department
// record_set("get_department", "select name from departments where id = '" . $row_get_loc_dep['departmentid'] . "'", 1);
// $row_get_department = mysqli_fetch_assoc($get_department);

// echo '<pre>';
// print_r($row_get_department);
// echo '</pre>';


// Location
// record_set("get_location", "select name from locations where id = '" . $row_get_loc_dep['locationid'] . "'", 1);
// $row_get_location = mysqli_fetch_assoc($get_location);

// echo '<pre>';
// print_r($row_get_location);
// echo '</pre>';

if (isset($_POST['export_document']) and $_POST['export_document'] == 2) {
  $message = '<div align="center">
  <img src="' . getHomeUrl() . MAIN_LOGO . '"  width="200"></div>
    <table width="100%">
        <thead>
          <tr>
            <td colspan="4" style="text-align:center; margin-top:10px;margin-bottom:10px;"><h2 align="center" style="margin:20px;">' . $row_get_survey['name'] . ' </h2></td>
          </tr>';

  if (!empty($filter['start_date']) and !empty($filter['end_date'])) {
    $message .= '<tr>
                    <td colspan="4" style="text-align:center; margin-top:10px;margin-bottom:10px;"><h2 align="center" style="margin:20px;">' . date('d/m/Y', strtotime($filter['start_date'])) . '-' . date('d/m/Y', strtotime($filter['end_date'])) . ' </h2></td>
                </tr>';
  }

  $message .= '</thead></table>';

  if (count($surveyQuestions) > 0) {
    foreach ($surveyQuestions as $surveyStepId => $questions) {
      $surveyStep = record_set_single("get_survey_step", "SELECT step_title FROM surveys_steps where id =" . $surveyStepId);

      if (isset($surveyStep) && is_array($surveyStep) && count($surveyStep) > 0) {
        $message .= '<div class="container">
        <h4 align="center" style="margin-top:20px;margin-bottom:10px;">' . strtoupper($surveyStep['step_title']) . '</h4>';
      }

      foreach ($questions as $question) {
        $questionId   = $question['id'];
        $answer_type  = $question['answer_type'];
        $totalRows_get_child_questions = 0;
        $questions_array = array();
        $answers_array = array();

        //1=radio && 4=rating && 6=dropdown
        if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
          //echo 'get_questions_detail';
          record_set("get_questions_detail", "select * from questions_detail where questionid='" . $questionId . "' and surveyid='" . $surveyId . "' and cstatus='1'");

          if ($totalRows_get_questions_detail > 0) {
            while ($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)) {
              $questions_array[$row_get_questions_detail['id']][] = $row_get_questions_detail['description'];
              $questions_array[$row_get_questions_detail['id']][] = $row_get_questions_detail['answer'];
            }
          }
          record_set("get_answers", "select * from answers where surveyid='" . $surveyId . "' " . $ans_filter_query . " and questionid='" . $questionId . "' order by id desc");
          if ($totalRows_get_answers > 0) {
            while ($row_get_answers = mysqli_fetch_assoc($get_answers)) {
              $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
            }
          }
          $counts = array_count_values($answers_array);
          // echo '<pre>';
          // print_r($questions_array);
          // print_r($answers_array);
          // echo '</pre>';

          // echo '<pre>';
          // print_r($counts);
          // echo '</pre>';
        }

        // 2=textbox && 3=textarea	
        if ($answer_type == 2 || $answer_type == 3) {
          record_set("get_answers", "select * from answers where surveyid='" . $surveyId . "' " . $ans_filter_query . " and questionid='" .   $questionId . "' order by id desc");
          if ($totalRows_get_answers > 0) {
            while ($row_get_answers = mysqli_fetch_assoc($get_answers)) {
              $answers_array[$row_get_answers['id']] = $row_get_answers['answertext'];
            }
          }
          $counts = ksort(array_count_values($answers_array));
          // echo '<pre>';
          // print_r($answers_array);
          // echo '</pre>';

          // echo '<pre>';
          // print_r($counts);
          // echo '</pre>';
        }

        if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
          if ($answer_type == 1) {
            //get Child Questions
            $get_child_questions = "select * from questions where parendit='" . $questionId . "' and cstatus='1'";
            record_set("get_child_questions", $get_child_questions);
          }
          if (empty($totalRows_get_child_questions)) {
            $message .= '<table width="505px" align="center" style="page-break-inside: avoid;">
                        <tr>
                          <td align="center" colspan="3">
                          <h4 colspan="2" style="margin-top:10px;text-align:center;">' . $question['question'] . '</h4>
                          </td>
                        </tr>
                        <tr>
                          <td width="304px;"></td>
                        </tr>
                        <tr>
                            <td colspan="3">
                              <table style="font-size:14px;" width="100%" cellspacing="0" cellpadding="4" border-bottom:none !important;>
                                <tr>
                              <th style="background-color:#f0f0f0; border: 1px solid ;border-bottom: none;">ANSWERS</th>
                              <th style="background-color:#f0f0f0;width:80px;border: 1px solid ;border-bottom: none;text-align:center;">RESULT</th>
                              <th style="background-color:#f0f0f0;width:70px;border: 1px solid;border-bottom: none;text-align:center;">RESPONSES</th>
                        </tr>';

            $total = 0;
            $sum_of_count = array_sum($counts);
            if ($sum_of_count > 0) {
              $perResponsePercentage = 100 / $sum_of_count;
            }

            if (count($counts) > 0) {
              foreach ($counts as $key => $val) {
                $scoreValue = round($perResponsePercentage * $val, 2);
                $questionDescriptionData = record_set_single("get_question_description_data", "SELECT description FROM questions_detail where id =" . $key);
                $questionDescription = '';
                if (isset($questionDescriptionData) && is_array($questionDescriptionData) && count($questionDescriptionData) > 0) {
                  $questionDescription = $questionDescriptionData['description'];
                }
                $message .= '<tr>
                            <td style="border: 1px solid">' . $questionDescription . '</td>
                            <td style="text-align:center;border: 1px solid">' . $scoreValue . '%</td>
                            <td style="text-align:center;border: 1px solid">' . $val . '</td>
                          </tr>';
                $total += $val;
              }
            } else {
              $message .= '<tr>
                            <td style="border: 1px solid" colspan="3">NO ANSWER AVAILABLE</td>
                          </tr>';
            }
            $message .= '<tr style="border:none;">
                            <td style="border:none;"></td>
                            <td style="border:none;text-align:center;"><strong>TOTAL</strong></td>
                            <td style="border:none;text-align:center"><strong>' . $total . '</strong></td>
                          </tr></table></td></tr></table>';
          } else {
                $message .= '<table width="505px" align="center">
                              <tr>
                                <td align="center" colspan="3">
                                <h3 style="margin-top:10px;">' . $question['question'] . '</h3>
                              </td>
                              </tr>
                              </table>
                              <table width="505px" align="center" style="font-size:14px;" border="1" cellspacing="0" cellpadding="4">
                              <tbody>
                              <tr>
                              <th  style="background-color:#f0f0f0;">Child Question</th>';

                    $child_answer = array();
                    $tdLoop = 0;

                  record_set("get_questions_detail", "select * from questions_detail where questionid='" . $questionId . "' and surveyid='" . $surveyId . "' and cstatus='1'");

                  while ($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)) {
                      $tdLoop++;
                      $child_answer[$row_get_questions_detail['id']] = $row_get_questions_detail['description'];
                      $message .= '<th style="background-color:#f0f0f0;" rowspan="2">'.$row_get_questions_detail['description'].'</th>';

                  }
                  $message .= '</tr><tr>';

                  while ($row_get_child_questions = mysqli_fetch_assoc($get_child_questions)) {
                    // echo '<pre>';
                    // print_r($row_get_child_questions);
                    // echo '</pre>';

                      $message .= '<td style="background-color:#f0f0f0;">' . $row_get_child_questions['question'] . '</td>';

                      $answers_array = array();

                      record_set("get_answers", "select * from answers where surveyid='" . $surveyId . "' " . $ans_filter_query . " and questionid='" . $row_get_child_questions['id'] . "' order by id desc ", 1);
                      if ($totalRows_get_answers > 0) {

                        while ($row_get_answers = mysqli_fetch_assoc($get_answers)) {
                          //print_r($row_get_answers);
                          $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
                        }
                      }
                      // echo '<pre>';
                      // print_r($answers_array);
                      // echo '</pre>';
  
                      $anscount =  count($answers_array);
                      $counts = array_count_values($answers_array);
                      $message .= '</tr>';
                  }
                  $message .= '</tbody>
                </table>';
          }
        }

        if ($answer_type == 2 || $answer_type == 3) {
          $message .= '<table width="505px" align="center">
              <tr>
                <td align="center">
                  <h4 style="margin-top:10px;">' . $question['question'] . '</h4>
                </td>
              </tr>
            </table>';

          $message .= '<table width="505px" align="center" style="font-size:14px;margin-bottom: 10px;" border="1" cellspacing="0" cellpadding="4">
              <tr style="background-color:#f0f0f0;">
                <th>S.NO.</th>
                <th align="center">ANSWERS</th>
              </tr>';
          $sno = 0;

          if (!empty($answers_array)) {
            foreach ($answers_array as $key => $val) {
              if (isset($val) && !empty($val) && $val != "") {
                $message .=  '<tr><td>' . ++$sno . '</td><td>' . $val . '</td></tr>';
              }
            }
          } else {
            $message .=  '<tr><td>NO ANSWER AVAILABLE</td></tr>';
          }
          $message .= '</table>';
        }
      }
      $message .= '</div>';
    }
  } else {
    $message .=  '<table width="100%"><tr><td align="center">No Answers Available.</td></tr></table>';
  }

  echo $message;
  die();
  create_mpdf($message, 'Survey Report Question -' . date('Y-m-d-H-i-s') . '.pdf', 'D');
}
