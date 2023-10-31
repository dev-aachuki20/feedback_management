<?php
require dirname(__DIR__, 2) . '/function/function.php';
require dirname(__DIR__, 2) . '/function/get_data_function.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

record_set("get_scheduled_reports", "select srt.* from scheduled_report_templates as srt INNER JOIN report_templates as rt ON srt.temp_id = rt.id where rt.report_type=2");

while ($row_get_reports = mysqli_fetch_assoc($get_scheduled_reports)) {
  $mpdf = new \Mpdf\Mpdf();

  $current_date   = date('Y-m-d', time());
  $end_date       = date('Y-m-d', strtotime($row_get_reports['end_date']));
  $next_schedule  = date('Y-m-d', strtotime($row_get_reports['next_date']));
  $result_1  = check_differenceDate($current_date, $end_date, 'lte');
  $result_2  = check_differenceDate($current_date, $next_schedule, 'lte');

  if ($result_1 && $result_2 && $row_get_reports['send_to'] != null) {

    $filter = json_decode($row_get_reports['filter'], 1);
    $data_type = $filter['field'];
    $surveyid   = $filter['survey_id'];
    $field_value = implode(',', $filter['field_value']);

    /** ---------------------------csv export------------------------------------ */
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
    if (is_array($surveyid)) {
      $surveyid = implode(',', $surveyid);
    }

    if (!empty($surveyid)) {
      $query = "SELECT * FROM answers  where surveyid in($surveyid) " . $ans_filter_query . " group by cdate order by cdate DESC;";
    }
    $allQuestion = "SELECT * FROM `questions` WHERE `surveyid` = $surveyid and cstatus=1";
    record_set('Questions', $allQuestion);
    $question_array = array();
    while ($row_ques_query = mysqli_fetch_assoc($Questions)) {
      $question_array[$row_ques_query['id']] = $row_ques_query['question'];
    }
    $flag = false;
    record_set('getdata', $query);


    if ($totalRows_getdata > 0) {
      $i = 0;
      $row_excel_data = array();
      while ($row_getdata = mysqli_fetch_assoc($getdata)) {
        $row_excel_data[$i]['Date']         = $row_getdata['cdate'];
        $row_excel_data[$i]['Survey ID']     = $row_getdata['surveyid'];
        $row_excel_data[$i]['First Name']     = '';
        $row_excel_data[$i]['Last Name']     = '';
        $row_excel_data[$i]['Phone Number'] = '';
        $row_excel_data[$i]['Email']         = '';
        //$row_excel_data[$i]['School'] 		= ''; 

        $sub_query = "SELECT * FROM questions LEFT JOIN answers ON questions.id = answers.questionid and answers.cdate ='" . $row_getdata['cdate'] . "' where questions.surveyid =$surveyid and questions.cstatus=1 order by questions.id ASC,questions.dposition asc";

        $contact_query = "SELECT * FROM answers  where surveyid =$surveyid and answers.cdate ='" . $row_getdata['cdate'] . "'";
        record_set('contact_query', $contact_query);
        while ($row_contact_query = mysqli_fetch_assoc($contact_query)) {
          if ($row_contact_query['answerid'] == -2) {
            $data = json_decode($row_contact_query['answertext']);
            foreach ($data as $key => $value) {

              if ($key == 'first_name') {
                $row_excel_data[$i]['First Name'] = ($value) ? $value : 'N/A';
              }
              if ($key == 'last_name') {
                $row_excel_data[$i]['Last Name'] = ($value) ? $value : 'N/A';
              }
              if ($key == 'phone_number') {
                $row_excel_data[$i]['Phone Number'] = ($value) ? $value : 'N/A';
              }
              if ($key == 'to_be_contact_mail') {
                $row_excel_data[$i]['Email'] = ($value) ? $value : 'N/A';
              }
            }
          } else if ($row_contact_query['answerid'] == -3) {
            //$row_excel_data[$i]['School'] = $row_contact_query['answertext'];
          }
        }

        record_set('sub_queryss', $sub_query);
        while ($row_sub_query = mysqli_fetch_assoc($sub_queryss)) {
          if ($row_sub_query['answertext'] === '0' && $row_sub_query['answertext'] != '') {
            record_set('question_details', "SELECT * FROM `questions_detail` WHERE `id` ='" . $row_sub_query['answerid'] . "'");
            $row_question_details = mysqli_fetch_assoc($question_details);
            $row_excel_data[$i][$row_sub_query['question']] = $row_question_details['description'];
          } else {
            $row_excel_data[$i][$row_sub_query['question']] = $row_sub_query['answertext'];
          }
        }
        $i++;
      }
    }
    arsort($row_excel_data);

    /** ---------------------------csv export------------------------------------ */
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

    $message = '<div align="center">
            <img src="' . getHomeUrl() . MAIN_LOGO . '"  width="200"></div>
            <table width="100%">
                <thead>
                  <tr>
                    <td colspan="4" style="text-align:center; margin-top:10px;margin-bottom:10px;"><h2 align="center" style="margin:20px;">' . $row_get_survey['name'] . ' </h2></td>
                  </tr>
                </thead>
            </table>';

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
            foreach ($table_display_data as $key => $val) {
              $message .= '<tr>
                                <td style="border: 1px solid">' . $key . '</td>
                                <td style="text-align:center;border: 1px solid">' . round($val['percantage'], 2) . '%</td>
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

    $footer = '<div style="text-align: center;"> ' . POWERED_BY . '
        <center><img  src="' . BASE_URL . FOOTER_LOGO . '" alt="" width="150"/></center>
        </div>';

    $mpdf->WriteHTML($message);
    $mpdf->SetHTMLFooter($footer);
    $mpdf->Output('document/survey-report-question-' . $row_get_reports['id'] . '.pdf', 'F');


    $csv_header = array_keys($row_excel_data[0]);
    $csv_data = implode(',', $csv_header);

    foreach ($row_excel_data as $data) {
      $csv_data .= "\n" . implode(',', array_values($data));
    }

    $csv_handler = fopen('document/survey-report-question-' . $row_get_reports['id'] . '.csv', 'w');
    fwrite($csv_handler, $csv_data);
    fclose($csv_handler);

    //send mail
    $attachments = array('document/survey-report-question-' . $row_get_reports['id'] . '.pdf', 'document/survey-report-question-' . $row_get_reports['id'] . '.csv');
    $mail_users = explode(",", $row_get_reports['send_to']);
    foreach ($mail_users as $userId) {
      $user_details = get_user_datails($userId);
      $to = $user_details['email'];
      $from_mail = "dgs@gmail.com";
      $name = $user_details['name'];
      $subject = "Schedule Report";
      $message = 'Hello ' . $name . ' you have schedule report';

      $mail = cron_emails($attachments, $to, $from_mail, $name, $subject, $message);
    }

    // update next schedule date with interval
    $nextScheduledDate = $row_get_reports['next_date'];
    $updateSchedule = date('Y-m-d H:i:s', strtotime(' + ' . $row_get_reports['sch_interval'] . ' hours', strtotime($nextScheduledDate)));
    $data = array(
      "next_date" => $updateSchedule,
    );
    $update = dbRowUpdate("scheduled_report_templates", $data, "where id=" . $row_get_reports['id']);

    if (count($attachments) > 0) {
      foreach ($attachments as $key => $value) {
        // echo "<br>" . $value . "<br>";
        unlink($value);
      }
    }
  }
}
