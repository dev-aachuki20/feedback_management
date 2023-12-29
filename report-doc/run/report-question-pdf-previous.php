<?php
$filter = $_POST;

$data_type = $filter['sch_template_field_name'];
$surveyid   = $filter['survey'];
$field_value = implode(',', $filter['template_field']);

if (isset($surveyid)) {
  record_set("get_survey", "select * from surveys where id IN ($surveyid) and cstatus=1");
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
}
if ($data_type == 'department' && $field_value != '') {
  $ans_filter_query .= " and departmentid IN($field_value)";
}
if ($data_type == 'group' && $field_value != '') {
  $ans_filter_query .= " and groupid IN($field_value)";
}

if(!empty($filter['start_date']) and !empty($filter['end_date'])){
  $ans_filter_query .= " and  cdate between '".date('Y-m-d', strtotime($filter['start_date']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($filter['end_date'])))."'";
}

//Survey Steps 
$survey_steps = array();
if ($row_get_survey['isStep'] == 1) {
  record_set("get_surveys_steps", "select * from surveys_steps where survey_id='" . $surveyid . "' order by step_number asc");
  while ($row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps)) {
    $survey_steps[$row_get_surveys_steps['id']]['number'] = $row_get_surveys_steps['step_number'];
    $survey_steps[$row_get_surveys_steps['id']]['title'] = $row_get_surveys_steps['step_title'];
  }
}

//Survey Questions
record_set("get_questions", "select * from questions where surveyid='" . $surveyid . "' and cstatus='1' and parendit='0' order by dposition asc");
$questions = array();
while ($row_get_questions = mysqli_fetch_assoc($get_questions)) {
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
}
record_set("get_loc_dep", "select locationid, departmentid from answers where surveyid='" . $surveyid . "' " . $ans_filter_query);
$row_get_loc_dep = mysqli_fetch_assoc($get_loc_dep);

//Department
record_set("get_department", "select name from departments where id = '" . $row_get_loc_dep['departmentid'] . "'");
$row_get_department = mysqli_fetch_assoc($get_department);

//Location
record_set("get_location", "select name from locations where id = '" . $row_get_loc_dep['locationid'] . "'");
$row_get_location = mysqli_fetch_assoc($get_location);


if (isset($_POST['export_document']) and $_POST['export_document'] == 2) {
  $message = '<div align="center">
  <img src="' . getHomeUrl() . MAIN_LOGO . '"  width="200"></div>
    <table width="100%">
        <thead>
          <tr>
            <td colspan="4" style="text-align:center; margin-top:10px;margin-bottom:10px;"><h2 align="center" style="margin:20px;">' . $row_get_survey['name'] . ' </h2></td>
          </tr>';
       
if(!empty($filter['start_date']) and !empty($filter['end_date'])){          
    $message .=' <tr>
    <td colspan="4" style="text-align:center; margin-top:10px;margin-bottom:10px;"><h2 align="center" style="margin:20px;">' . date('d/m/Y', strtotime($filter['start_date'])) .'-'.date('d/m/Y', strtotime($filter['end_date'])). ' </h2></td>
  </tr>';
}

  $message .='</thead></table>';

  foreach ($survey_steps as $key => $value) {
    $message .= '<div class="container">
        <h4 align="center" style="margin-top:20px;margin-bottom:10px;">' . $value['title'] . '</h4>';
    foreach ($questions[$key] as $question) {
      $questionid   = $question['id'];
      $answer_type  = $question['answer_type'];
      $totalRows_get_child_questions = 0;
      //1=radio	2=textbox	3=textarea	4=rating
      $questions_array = array();
      $answers_array = array();
      if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
        //echo 'get_questions_detail';
        record_set("get_questions_detail", "select * from questions_detail where questionid='" . $questionid . "' and surveyid='" . $surveyid . "' and cstatus='1'");
        if ($totalRows_get_questions_detail > 0) {
          while ($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)) {
            $questions_array[$row_get_questions_detail['id']][] = $row_get_questions_detail['description'];
            $questions_array[$row_get_questions_detail['id']][] = $row_get_questions_detail['answer'];
          }
        }
        record_set("get_answers", "select * from answers where surveyid='" . $surveyid . "' " . $ans_filter_query . " and questionid='" . $questionid . "' order by id desc");
        if ($totalRows_get_answers > 0) {
          while ($row_get_answers = mysqli_fetch_assoc($get_answers)) {
            $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
          }
        }
        $counts = array_count_values($answers_array);
      }

      if ($answer_type == 2 || $answer_type == 3) {
        record_set("get_answers", "select * from answers where surveyid='" . $surveyid . "' " . $ans_filter_query . " and questionid='" . $questionid . "' order by id desc ");
        if ($totalRows_get_answers > 0) {
          while ($row_get_answers = mysqli_fetch_assoc($get_answers)) {
            $answers_array[$row_get_answers['id']] = $row_get_answers['answertext'];
          }
        }
        $counts = array_count_values($answers_array);
      }

      if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {

        if ($answer_type == 1) {
          //get Child Questions
          $get_child_questions = "select * from questions where parendit='" . $questionid . "' and cstatus='1'";
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
                    <td width="304px;">';
          $clr_loop = 0;
          $table_display_data = array();
          foreach ($questions_array as $key => $val) {
            $clr_loop++;
            $ansId = array_keys($counts)[0];
            if ($key == $ansId) {
              $percentage = $questions_array[$key][1];
            } else {
              $percentage = 0;
            }
            // //$percentage = ($total_ans*$total_num)/$total_num;
            $table_display_data[$val[0]]['percantage'] = $percentage;
            $table_display_data[$val[0]]['count'] = $counts[$key];
          }

          $message .= '
                    </td>
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
          $sum_of_count = array_sum(array_column($table_display_data, "count"));
          $perResponsePercentage = 100/$sum_of_count;
         
          foreach ($table_display_data as $key => $val) {
            $scoreValue = round($perResponsePercentage*$val['count'],2);
            if(is_nan($scoreValue)){
              $scoreValue = 0;
            }
            $message .= '<tr>
                            <td style="border: 1px solid">' . $key . '</td>
                            <td style="text-align:center;border: 1px solid">'.$scoreValue.'%</td>
                            <td style="text-align:center;border: 1px solid">' . $val['count'] . '</td>
                          </tr>';
            $total += $val['count'];
          }
          $message .= '<tr style="border:none;">
                          <td style="border:none;"></td>
                          <td style="border:none;text-align:center;"><strong>TOTAL</strong></td>
                          <td style="border:none;text-align:center"><strong>' . $total . '</strong></td>
                        </tr>';
          $message .= '</table>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" style="height:40px;">&nbsp;</td>
                  </tr>
                </table>';
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
                  <td style="background-color:#f0f0f0;">&nbsp;</td>' .
            $child_answer = array();
          $tdloop = 0;
          record_set("get_questions_detail", "select * from questions_detail where questionid='" . $questionid . "' and surveyid='" . $surveyid . "' and cstatus='1'  ");
          while ($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)) {
            $tdloop++;
            $message .= '<td style="background-color:#f0f0f0;">' .
              $child_answer[$row_get_questions_detail['id']] = $row_get_questions_detail['description'];
            $row_get_questions_detail['description'];
            '</td>';
          }
          $message .= '</tr>';
          while ($row_get_child_questions = mysqli_fetch_assoc($get_child_questions)) {
            $message .= '<tr>
                    <td style="background-color:#f0f0f0;">' . $row_get_child_questions['question'] . '
                    </td>';

            $answers_array = array();
            record_set("get_answers", "select * from answers where surveyid='" . $surveyid . "' " . $ans_filter_query . " and questionid='" . $row_get_child_questions['id'] . "' order by id desc ");
            if ($totalRows_get_answers > 0) {
              while ($row_get_answers = mysqli_fetch_assoc($get_answers)) {
                //print_r($row_get_answers);
                $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
              }
            }
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

        $message .= '<table width="505px" align="center" style="font-size:14px;" border="1" cellspacing="0" cellpadding="4">
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
  create_mpdf($message, 'Survey Report Question -' . date('Y-m-d-H-i-s') . '.pdf', 'D');
}
