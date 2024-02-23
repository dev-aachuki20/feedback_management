<?php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();

$data_type = $filter['field'];
$surveyId   = $filter['survey_id'];

$days_to_subtract = $row_report['sch_interval'] / 24;
$survey_end_date = $row_report['next_date'];
$survey_start_date = date('Y-m-d', strtotime('-' . $days_to_subtract . ' days', strtotime($survey_end_date)));

$ans_filter_query = '';
if (!empty($survey_start_date) and !empty($next_date)) {
  $ans_filter_query .= " and  cdate between '" . date('Y-m-d', strtotime($survey_start_date)) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($next_date))) . "'";
}

// Get survey questions. 
record_set("get_questions", "select * from questions where surveyid='" . $surveyId . "' and cstatus='1' and parendit='0' order by dposition asc");
$surveyQuestions = array();

while ($row_get_question = mysqli_fetch_assoc($get_questions)) {
  $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['id'] = $row_get_question['id'];
  $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['question'] = $row_get_question['question'];
  $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['ifrequired'] = $row_get_question['ifrequired'];
  $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['answer_type'] = $row_get_question['answer_type'];

  // Get answer values attempted.
  record_set("get_questions_answers", "select * from answers where surveyid='" . $surveyId . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_question['id']);
  $answer_type = $row_get_question['answer_type'];

  if (!empty($totalRows_get_questions_answers)) {
    while ($row_get_questions_answer = mysqli_fetch_assoc($get_questions_answers)) {

      if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
        $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['survey_responses'][$row_get_questions_answer['answerid']] += 1;
      } else if ($answer_type == 2 || $answer_type == 3) {
        $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['survey_responses'][] = ($row_get_questions_answer['answertext']) ? $row_get_questions_answer['answertext'] : 'UnAnswered';
      }
    }
  } else if ($answer_type == 1) {
    record_set("get_child_questions", "select * from questions where parendit='" . $row_get_question['id'] . "' and cstatus='1'");
    if (!empty($totalRows_get_child_questions)) {
      $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['having_child'] = true;


      record_set("get_parent_question_options", "select id from questions_detail where surveyid='" . $surveyId . "' and questionid = " . $row_get_question['id']);
      $options = array();
      if (!empty($totalRows_get_parent_question_options)) {
        while ($row_parent_question_option = mysqli_fetch_assoc($get_parent_question_options)) {
          $options[$row_parent_question_option['id']] = 0;
        }
      }

      while ($row_get_child_question = mysqli_fetch_assoc($get_child_questions)) {
        $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['children'][$row_get_child_question['id']]['id'] = $row_get_child_question['id'];
        $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['children'][$row_get_child_question['id']]['question'] = $row_get_child_question['question'];
        $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['children'][$row_get_child_question['id']]['ifrequired'] = $row_get_child_question['ifrequired'];
        $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['children'][$row_get_child_question['id']]['answer_type'] = $row_get_child_question['answer_type'];

        $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['children'][$row_get_child_question['id']]['survey_responses'] = $options;

        record_set("get_child_questions_answers", "select * from answers where surveyid='" . $surveyId . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_child_question['id']);

        if (!empty($totalRows_get_child_questions_answers)) {
          while ($row_get_child_questions_answer = mysqli_fetch_assoc($get_child_questions_answers)) {
            $surveyQuestions[$row_get_question['survey_step_id']][$row_get_question['id']]['children'][$row_get_child_question['id']]['survey_responses'][$row_get_child_questions_answer['answerid']]  += 1;
          }
        }
      }
    }
  }
}

// echo '<pre>';
// print_r($surveyQuestions);
// echo '</pre>';
// die('AFGH');

$message = '<div align="center">
                <img src="' . getHomeUrl() . MAIN_LOGO . '"  width="200"></div>
                  <table width="100%" style="font-family: Arial, Helvetica, sans-serif;">
                  <thead>
                    <tr>
                      <td colspan="4" style="text-align:center;border-bottom: 1px solid gray;padding-bottom: 10px;"><h3 align="center" style="margin-top:0;font-family: Arial, Helvetica, sans-serif;">' . strtoupper(getSurvey()[$surveyId]) . ' </h3></td>
                    </tr>';

if ($days_to_subtract == 1) {
  $message .= '<tr>
        <td colspan="4" style="text-align:center;padding-top: 9px;font-family: Arial, Helvetica, sans-serif;"><h4 align="center" style="margin-top: 0;margin-bottom: 0;">' . date('d/m/Y', strtotime($survey_start_date)) . ' </h4></td>
      </tr>';
} else {
  $message .= '<tr>
      <td colspan="4" style="text-align:center;padding-top: 9px;font-family: Arial, Helvetica, sans-serif;"><h4 align="center" style="margin-top: 0;margin-bottom: 0;">' . date('d/m/Y', strtotime($survey_start_date)) . '-' .  date('d/m/Y', strtotime("-1 day", strtotime($survey_end_date))) . ' </h4></td>
      </tr>';
}

$message .= '</thead></table>';

if (count($surveyQuestions) > 0) {
  foreach ($surveyQuestions as $surveyStepId => $questions) {
    $surveyStep = record_set_single("get_survey_step", "SELECT step_title FROM surveys_steps where id =" . $surveyStepId);
    if (isset($surveyStep) && is_array($surveyStep) && count($surveyStep) > 0) {
      $message .= '<div class="container">
          <h4 align="center" style="margin-top:5px;margin-bottom:0;font-family: Arial, Helvetica, sans-serif;">' . strtoupper($surveyStep['step_title']) . '</h4>';
    }
    foreach ($questions as $question) {
      if ($question['answer_type'] == 1 || $question['answer_type'] == 4 || $question['answer_type'] == 6) {
        if (!array_key_exists("having_child", $question)) {
          $message .= '<table width="505px" align="center" style="page-break-inside: avoid;font-family: Arial, Helvetica, sans-serif;">
                          <tr>
                            <td align="center" colspan="3">
                            <h4 colspan="2" style="margin-top:15px;text-align:center;">' . $question['question'] . '</h4>
                            </td>
                          </tr>
                          <tr>
                              <td colspan="3">
                                <table style="font-size:14px;" width="100%" cellspacing="0" cellpadding="4" border-bottom:none !important;>
                                  <tr>
                                <th style="background-color:#f0f0f0; border: 1px solid ;border-bottom: none;text-align: left;">ANSWERS</th>
                                <th style="background-color:#f0f0f0;width:80px;border: 1px solid ;border-bottom: none;text-align:left;">RESULT</th>
                                <th style="background-color:#f0f0f0;width:70px;border: 1px solid;border-bottom: none;text-align:left;">RESPONSES</th>
                          </tr>';

          $total = 0;

          if (isset($question['survey_responses']) && is_array($question['survey_responses']) && count($question['survey_responses']) > 0) {
            $sum_of_count = array_sum($question['survey_responses']);
            if ($sum_of_count > 0) {
              $perResponsePercentage = 100 / $sum_of_count;
            }

            $answeredOptions = implode(",", array_keys($question['survey_responses']));

            record_set("get_remaining_questions_options", "SELECT questions_detail.id FROM questions_detail WHERE surveyid='" . $surveyId . "' AND cstatus='1'  AND questionid = " . $question['id'] . " AND id NOT IN (" . $answeredOptions . ")");

            if (!empty($totalRows_get_remaining_questions_options)) {
                while ($row_remaining_questions_option = mysqli_fetch_assoc($get_remaining_questions_options)) {
                    $question['survey_responses'][$row_remaining_questions_option['id']] = 0;
                }
            }
            
            foreach ($question['survey_responses'] as $key => $val) {
              $scoreValue = round($perResponsePercentage * $val, 2);
              $questionDescriptionData = record_set_single("get_question_description_data", "SELECT description FROM questions_detail where id =" . $key);
              $questionDescription = '';
              if (isset($questionDescriptionData) && is_array($questionDescriptionData) && count($questionDescriptionData) > 0 && isset($questionDescriptionData['description']) && $questionDescriptionData['description'] != null) {
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
                              <td style="border: 1px solid;text-align:center;" colspan="3">NO ANSWER AVAILABLE</td>
                            </tr>';
          }
          $message .= '<tr style="border:none;">
                              <td style="border:none;"></td>
                              <td style="border:none;text-align:center;"><strong>TOTAL</strong></td>
                              <td style="border:none;text-align:center"><strong>' . $total . '</strong></td>
                            </tr></table></td></tr></table>';
        } else {
          if ($question['answer_type'] == 1) {
            $message .= '<table width="505px" align="center" class="question_table" style="max-width: 505px; margin: auto; width: 100%;font-family: Arial, Helvetica, sans-serif;">
                <tr>
                  <td align="center" colspan="3">
                  <h3 style="margin-top:10px;">' . $question['question'] . '</h3>
                </td>
                </tr>
                </table>
                <table align="center" style="font-size:14px;border-collapse: collapse;font-family: Arial, Helvetica, sans-serif;" border="1" cellspacing="0" cellpadding="4">
                <thead>
                <tr>
                <th  style="background-color:#f0f0f0;" rowspan="2"></th>';

            record_set("get_parent_question_options", "select id, description from questions_detail where surveyid='" . $surveyId . "' and questionid = " . $question['id']);

            if (!empty($totalRows_get_parent_question_options)) {
              $i = 0;
              while ($row_parent_question_option = mysqli_fetch_assoc($get_parent_question_options)) {
                $message .= '<th style="background-color:#f0f0f0;" colspan="2">' . $row_parent_question_option['description'] . '</th>';
                $i++;
              }
              $message .= '</tr><tr>';

              for ($j = $i; $j > 0; $j--) {
                $message .= '<th align="left">RESULT</th><th align="left">RESPONSES</th>';
              }
              $message .= '</tr></thead><tbody><tr>';
            }

            $mergedSurveyResponses = [];
            foreach ($question['children'] as $key => $child_question) {
              foreach ($child_question['survey_responses'] as $child_key => $response) {
                $mergedSurveyResponses[$child_key] += $response > 0 ? $response : 0;
              }
            }

            foreach ($question['children'] as $key => $child_question) {
              $message .= '<td style="background-color:#f0f0f0;">' . $child_question['question'] . '</td>';
              $k = 0;
              foreach ($child_question['survey_responses'] as $child_key => $response) {
                $sum_of_responses = $mergedSurveyResponses[$child_key];
                if ($sum_of_responses > 0) {
                  $per_res_score = 100 / $sum_of_responses;
                } else {
                  $per_res_score = 0;
                }
                $score = round($per_res_score * $response, 2);
                $message .= '<td style="background-color:#f0f0f0;">' . $score . '%</td>';
                $message .= '<td style="background-color:#f0f0f0;">' . $response . '</td>';
                $k++;
                if ($k == $i) {
                  $message .= '</tr><tr>';
                }
              }
            }
            $message .= '</tr></tbody></table>';
          }
        }
      }

      if ($question['answer_type'] == 2 || $question['answer_type'] == 3) {
        $message .= '<table width="505px" align="center" style="font-family: Arial, Helvetica, sans-serif;">
                <tr>
                  <td align="center">
                    <h4 style="margin-top:10px;">' . $question['question'] . '</h4>
                  </td>
                </tr>
              </table>';

        $message .= '<table width="505px" align="center" style="font-size:14px;margin-bottom: 10px;font-family: Arial, Helvetica, sans-serif;" border="1" cellspacing="0" cellpadding="4">
                <tr style="background-color:#f0f0f0;">
                  <th align="left">RESPONDENT</th>
                  <th align="left">ANSWERS</th>
                </tr>';
        $sno = 0;

        if (isset($question['survey_responses']) && is_array($question['survey_responses']) && count($question['survey_responses']) > 0) {
          foreach ($question['survey_responses'] as $key => $val) {
            if (isset($val) && !empty($val) && $val != "") {
              $message .=  '<tr><td>' . ++$sno . '</td><td>' . $val . '</td></tr>';
            }
          }
        } else {
          $message .=  '<tr><td style="text-align:center;" colspan="2">NO ANSWER AVAILABLE</td></tr>';
        }
        $message .= '</table>';
      }
    }
    $message .= '</div>';
  }
} else {
  $message .=  '<table width="100%" style="font-family: Arial, Helvetica, sans-serif;"><tr><td align="center">No Answers Available.</td></tr></table>';
}

$footer = '<div style="text-align: center;"> ' . POWERED_BY . '
                  <center><img  src="' . BASE_URL . FOOTER_LOGO . '" alt="" width="150"/></center>
                </div>';

$mpdf->SetHTMLFooter($footer);
$mpdf->WriteHTML($message);
$mpdf->Output('document/survey-report-question-' . $row_report['id'] . '.pdf', 'F');
