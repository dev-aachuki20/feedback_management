<?php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();

$ans_filter_query = '';
$filter = json_decode($row_report['filter'], 1);
$data_type    = $filter['field'];
$surveyId     = $filter['survey_id'];

$interval     = $row_report['sch_interval'] / 24;
$survey_end_date = $row_report['next_date'];
$survey_start_date = date('Y-m-d', strtotime('-' . $interval . ' days', strtotime($survey_end_date)));

if (!empty($survey_start_date) and !empty($next_date)) {
  $ans_filter_query .= " and  cdate between '" . date('Y-m-d', strtotime($survey_start_date)) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($survey_end_date))) . "'";
}

//Survey Questions
record_set("get_questions", "select * from questions where surveyid='" . $surveyId . "' and cstatus='1' and parendit='0' order by dposition asc");

$surveyQuestions = array();
while ($row_get_questions = mysqli_fetch_assoc($get_questions)) {
  $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
  $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
  $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
  $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
  $answer_type = $row_get_questions['answer_type'];

  /* Get answer values attempted */
  record_set("get_questions_answers", "select * from answers where surveyid='" . $surveyId . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_questions['id']);

  if (!empty($totalRows_get_questions_answers)) {
    while ($row_get_questions_answers = mysqli_fetch_assoc($get_questions_answers)) {
      $created_date = date('d-m-Y', strtotime($row_get_questions_answers['cdate']));
      if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
        $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$created_date][$row_get_questions_answers['answerid']] += 1;
      } else if ($answer_type == 2 || $answer_type == 3) {
        $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$created_date][] = ($row_get_questions_answers['answertext']) ? $row_get_questions_answers['answertext'] : 'UnAnswered';
      }
    }
  } elseif ($answer_type == 1) {

    record_set("get_child_questions", "select * from questions where parendit='" . $row_get_questions['id'] . "' and cstatus='1'");
    if (!empty($totalRows_get_child_questions)) {
      $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['having_child'] = true;

      record_set("get_parent_question_options", "select id from questions_detail where surveyid='" . $surveyId . "' and questionid = " . $row_get_questions['id']);
      $options = array();
      if (!empty($totalRows_get_parent_question_options)) {
        while ($row_parent_question_option = mysqli_fetch_assoc($get_parent_question_options)) {
          $options[$row_parent_question_option['id']] = 0;
        }
      }

      while ($row_get_child_question = mysqli_fetch_assoc($get_child_questions)) {
        $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['id'] = $row_get_child_question['id'];
        $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['question'] = $row_get_child_question['question'];
        $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['ifrequired'] = $row_get_child_question['ifrequired'];
        $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['answer_type'] = $row_get_child_question['answer_type'];

        record_set("get_child_questions_answers", "select * from answers where surveyid='" . $surveyId . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_child_question['id'],1);

        if (!empty($totalRows_get_child_questions_answers)) {

          while ($row_get_child_questions_answer = mysqli_fetch_assoc($get_child_questions_answers)) {
            $created_date = date('d-m-Y', strtotime($row_get_child_questions_answer['cdate']));
            $options[$row_get_child_questions_answer['answerid']] += 1;
            $surveyQuestions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['survey_responses'][$created_date]  = $options;
          }
        }
      }
    }
  }
}


$message = '<div align="center">
    <img src="' . getHomeUrl() . MAIN_LOGO . '"  width="200"></div>
      <table width="100%">
        <thead>
          <tr>
            <td colspan="4" style="text-align:center; margin-top:10px;margin-bottom:10px;"><h2 align="center" style="margin:20px;">' . getSurvey()[$surveyId] . ' </h2></td>
          </tr>';


if ($interval == 1) {
  $message .= '<tr>
              <td colspan="4" style="text-align:center; margin-top:10px;margin-bottom:10px;"><h2 align="center" style="margin:20px;">' . date('d/m/Y', strtotime($survey_start_date)) . ' </h2></td>
            </tr>';
} else {
  $message .= '<tr>
            <td colspan="4" style="text-align:center; margin-top:10px;margin-bottom:10px;"><h2 align="center" style="margin:20px;">' . date('d/m/Y', strtotime($survey_start_date)) . '-' .  date('d/m/Y', strtotime("-1 day", strtotime($survey_end_date))) . ' </h2></td>
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
      if ($question['answer_type'] == 1 || $question['answer_type'] == 4 || $question['answer_type'] == 6) {
        if (!array_key_exists("having_child", $question)) {
          $message .= '<div class="container">
              <h4 align="center" style="margin-top:20px;margin-bottom:10px;">' . $question['question']  . '</h4>';

          $message .= ' <table width="510px" align="center" cellspacing="0" cellpadding="4" border-bottom:none !important; style="font-size:14px;">
              <thead>
                <tr>
                  <th style="background-color:#f0f0f0; border: 1px solid ;border-bottom: none;">Date</th>
                  <th style="background-color:#f0f0f0; border: 1px solid ;border-bottom: none;">ANSWERS</th>
                  <th style="background-color:#f0f0f0;width:80px;border: 1px solid ;border-bottom: none;text-align:center;">RESULT</th>
                  <th style="background-color:#f0f0f0;width:70px;border: 1px solid;border-bottom: none;text-align:center;">RESPONSES</th>
                </tr>
              </thead>
              <tbody>';

          if (isset($question['survey_responses']) && is_array($question['survey_responses']) && count($question['survey_responses']) > 0) {
            $message .= '';

            foreach ($question['survey_responses'] as $key => $val) {
              record_set("get_question_details", "select id,description,answer from questions_detail where surveyid='" . $surveyId . "' and questionid=$question[id]");

              $message .= '<tr>
                                <td rowspan="' . $totalRows_get_question_details . '" style="text-align:center;border: 1px solid">' . $key . '</td>';

              $total = 0;
              $sum_of_count = array_sum($val);
              $perResponsePercentage = 100 / $sum_of_count;

              while ($row_get_question_detail = mysqli_fetch_assoc($get_question_details)) {
                $total++;
                $no_of_response = $val[$row_get_question_detail['id']] > 0 ? $val[$row_get_question_detail['id']] : 0;
                $response_percentage = $perResponsePercentage * $no_of_response;

                $custom_style = $totalRows_get_question_details == $total ? 'text-align:center;border-top: 1px solid;border-right: 1px solid;border-bottom: 1px solid' : 'text-align:center;border-top: 1px solid;border-right: 1px solid;';

                if ($total == 1) {
                  $message .= '<td style="' . $custom_style . '">' . $row_get_question_detail['description'] . '</td>
                      <td style="' . $custom_style . '">' . $response_percentage . '%</td>
                      <td style="' . $custom_style . '">' . $no_of_response . '</td>
                      </tr>';
                } else {
                  $message .= '<tr><td style="' . $custom_style . '">' . $row_get_question_detail['description'] . '</td>
                      <td style="' . $custom_style . '">' . $response_percentage . '%</td>
                      <td style="' . $custom_style . '">' . $no_of_response . '</td>
                      </tr>';
                }
              }
            }

            $message .= '</tbody></table>';
          } else {
            $message .= '<tr>
                                <td style="border: 1px solid; text-align:center;" colspan="4">NO ANSWER AVAILABLE</td>
                              </tr></tbody></table>';
          }
        } else {
          if ($question['answer_type'] == 1) {
            $message .= '<table width="505px" align="center" class="question_table" style="max-width: 505px; margin: auto; width: 100%;">
                  <tr>
                    <td align="center" colspan="3">
                    <h3 style="margin-top:10px;">' . $question['question'] . '</h3>
                  </td>
                  </tr>
                  </table>
                  <table width="510px" align="center" style="font-size:14px;border-collapse: collapse;" border="1" cellspacing="0" cellpadding="4">
                  <thead>
                  <tr>
                  <th  style="background-color:#f0f0f0;" rowspan="2"></th>
                  <th  style="background-color:#f0f0f0;" rowspan="2">Date</th>';

            record_set("get_parent_question_options", "select id, description from questions_detail where surveyid='" . $surveyId . "' and questionid = " . $question['id']);

            if (!empty($totalRows_get_parent_question_options)) {
              $i = 0;
              while ($row_parent_question_option = mysqli_fetch_assoc($get_parent_question_options)) {
                $message .= '<th style="background-color:#f0f0f0;" colspan="2">' . $row_parent_question_option['description'] . '</th>';
                $i++;
              }
              $message .= '</tr><tr>';

              for ($j = $i; $j > 0; $j--) {
                $message .= '<th>RESULT</th><th>RESPONSES</th>';
              }
              $message .= '</tr></thead><tbody>';
            }


            $mergedSurveyResponses = [];
            foreach ($question['children'] as $questionKey => $question_detail) {
              $message .= '<tr>
                    <td rowspan="2" style="background-color:#f0f0f0;border-bottom:2px solid;">' . $question_detail['question'] . '</td>';
              $total = 0;
              $total_responses = count($question_detail['survey_responses']);
              foreach ($question_detail['survey_responses'] as $date_key => $responses) {
                $total++;

                $custom_style = $total == $total_responses ? 'background-color:#f0f0f0;border-bottom:2px solid' : 'background-color:#f0f0f0;';

                if ($total == 1) {
                  $message .= '<td  style="background-color:#f0f0f0;">' . $date_key . '</td>';
                } else {
                  $message .= '<tr><td style="' . $custom_style . '">' . $date_key . '</td>';
                }

                $per_percentage = 0;
                $sum = array_sum(array_values($responses));
                if ($sum > 0) {
                  $per_percentage = 100 / $sum;
                }
                foreach ($responses  as $res_key => $answer) {
                  $percentage_total = round($per_percentage * $answer, 2);
                  $message .= '<td  style="' . $custom_style . '">' . $percentage_total . '%</td>
                      <td  style="' . $custom_style . '">' . $answer . '</td>';
                }
                $message .= '</tr>';
              }
            }
            $message .= '</tbody></table>';
          }
        }
      }

      if ($question['answer_type'] == 2 || $question['answer_type'] == 3) {
        $message .= '<table width="505px" align="center" style="margin-top:30px;">
                  <tr>
                    <td align="center">
                      <h4 style="margin-top:10px;">' . $question['question'] . '</h4>
                    </td>
                  </tr>
                </table>';

        $message .= '<table width="505px" align="center" style="font-size:14px;margin-bottom: 10px;" border="1" cellspacing="0" cellpadding="4">
                  <tr style="background-color:#f0f0f0;">
                    <th>Date</th>
                    <th align="center">ANSWERS</th>
                  </tr>';
        $sno = 0;

        if (isset($question['survey_responses']) && is_array($question['survey_responses']) && count($question['survey_responses']) > 0) {
          foreach ($question['survey_responses'] as $dateKey => $data) {
            $message .= '<tr>
                <td rowspan="' . count($data) . '" style="text-align:center;border: 1px solid">' . $dateKey . '</td>';
            $total = 0;
            foreach ($data as $sr_no => $ans_txt) {
              $total++;
              $custom_style = $totalRows_get_question_details == $total ? 'text-align:center;border-top: 1px solid;border-right: 1px solid;border-bottom: 1px solid' : 'text-align:center;border-top: 1px solid;border-right: 1px solid;';
              if ($total == 1) {
                $message .= '<td style="' . $custom_style . '">' . $ans_txt . '</td>
                    </tr>';
              } else {
                $message .= '<tr><td style="' . $custom_style . '">' . $ans_txt . '</td>
                    </tr>';
              }
            }
          }
        } else {
          $message .=  '<tr><td colspan="2" align="center">NO ANSWER AVAILABLE</td></tr>';
        }
        $message .= '</table>';
      }
    }
    $message .= ' </div></div>';
  }
}

$footer = '<div style="text-align: center;"> ' . POWERED_BY . '
                    <center><img  src="' . BASE_URL . FOOTER_LOGO . '" alt="" width="150"/></center>
                  </div>';

$mpdf->SetHTMLFooter($footer);
$mpdf->WriteHTML($message);
$mpdf->Output('document/survey-report-question-' . $row_report['id'] . '.pdf', 'F');
