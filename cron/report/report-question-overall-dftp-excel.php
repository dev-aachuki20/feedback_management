<?php
require dirname(__DIR__, 2) . '/function/function.php';
require dirname(__DIR__, 2) . '/function/get_data_function.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

record_set("get_scheduled_reports", "select srt.* from scheduled_report_templates as srt INNER JOIN report_templates as rt ON srt.temp_id = rt.id where rt.report_type=2");

while ($row_get_reports = mysqli_fetch_assoc($get_scheduled_reports)) {
  $current_date  = date('Y-m-d', time());
  $start_date = date('Y-m-d', strtotime($row_get_reports['start_date']));
  $next_date   = date('Y-m-d', strtotime($row_get_reports['next_date']));
  $end_date   = date('Y-m-d', strtotime($row_get_reports['end_date']));
  $send_to = $row_get_reports['send_to'];
  $report_id = $row_get_reports['id'];
  $is_due_gt_start_date = check_differenceDate($next_date, $start_date, 'gt');
  $is_today_due_date = check_differenceDate($current_date, $next_date, 'eq');
  $is_curr_lte_end_date = check_differenceDate($current_date, $end_date, 'lte');

  if ($is_due_gt_start_date && $is_today_due_date && $is_curr_lte_end_date  && $send_to != null) {
    $filter = json_decode($row_get_reports['filter'], 1);
    $data_type    = $filter['field'];
    $surveyid     = $filter['survey_id'];
    $interval     = $row_get_reports['sch_interval'] / 24;
    $nextDate     = $row_get_reports['next_date'];
    $startDate    = date('Y-m-d', strtotime("-" . $interval . " day", strtotime($nextDate)));


    if (!empty($startDate) and !empty($nextDate)) {
      $ans_filter_query .= " and  cdate between '" . date('Y-m-d', strtotime($startDate)) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($nextDate))) . "'";
    }


    //Survey Questions
    record_set("get_questions", "select * from questions where surveyid='" . $surveyid . "' and cstatus='1' and parendit='0' order by dposition asc");
    $questions = array();
    while ($row_get_questions = mysqli_fetch_assoc($get_questions)) {
      $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
      $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
      $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
      $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
      $answer_type = $row_get_questions['answer_type'];

      /* Get answer values attempted */
      record_set("get_questions_answers", "select * from answers where surveyid='" . $surveyid . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_questions['id']." order By cdate asc", 1);
      if (!empty($totalRows_get_questions_answers)) {
        while ($row_get_questions_answers = mysqli_fetch_assoc($get_questions_answers)) {
          $created_date = date('d-m-Y', strtotime($row_get_questions_answers['cdate']));
          if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
            $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$created_date][$row_get_questions_answers['answerid']] += 1;
          } else if ($answer_type == 2 || $answer_type == 3) {
            $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$created_date][] = ($row_get_questions_answers['answertext']) ? $row_get_questions_answers['answertext'] : 'UnAnswered';
          }
        }
      } elseif ($answer_type == 1) {
        record_set("get_child_questions", "select * from questions where parendit='" . $row_get_questions['id'] . "' and cstatus='1'");
        if (!empty($totalRows_get_child_questions)) {
          $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['having_child'] = true;
          record_set("get_parent_question_options", "select id from questions_detail where surveyid='" . $surveyid . "' and questionid = " . $row_get_questions['id'], 1);
          $options = array();
          if (!empty($totalRows_get_parent_question_options)) {
            while ($row_parent_question_option = mysqli_fetch_assoc($get_parent_question_options)) {
              $options[$row_parent_question_option['id']] = 0;
            }
          }

          while ($row_get_child_question = mysqli_fetch_assoc($get_child_questions)) {

            $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['id'] = $row_get_child_question['id'];
            $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['question'] = $row_get_child_question['question'];
            $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['ifrequired'] = $row_get_child_question['ifrequired'];
            $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['answer_type'] = $row_get_child_question['answer_type'];


            record_set("get_child_questions_answers", "select * from answers where surveyid='" . $surveyid . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_child_question['id'], 1);

            if (!empty($totalRows_get_child_questions_answers)) {
              while ($row_get_child_questions_answer = mysqli_fetch_assoc($get_child_questions_answers)) {
                $created_date = date('d-m-Y', strtotime($row_get_child_questions_answer['cdate']));
                $options[$row_get_child_questions_answer['answerid']] +=1;
                $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['survey_responses'][$created_date]  = $options;
              }
            }
          }
        }
      }
    }

    // create excel start
    /** Print Excel file start */
    $style = [
      'font' => [
        'bold' => true,
        'size' => 12,
      ],
      'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
      ],
    ];

    $surveyName = getSurvey()[$surveyid];
    $dateParameter = date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($nextDate));

    $spreadsheet = new Spreadsheet();
    $activeSheet = $spreadsheet->getActiveSheet();
   

    $activeSheet->setCellValue('A1', $surveyName);
    $activeSheet->setCellValue('A2', $dateParameter);

    $activeSheet->getColumnDimension('A')->setWidth(700, 'px');
    $activeSheet->getColumnDimension('B')->setWidth(100, 'px');
    $activeSheet->getColumnDimension('C')->setWidth(100, 'px');
    $i = 2;
    echo '<pre>';
    print_r($questions);
    echo '</pre>';
    foreach($questions as $stepId => $question){
      $activeSheet->setCellValue('A'.$i, '');
      $i++;
      $surveyStep = record_set_single("get_survey_step", "SELECT step_title FROM surveys_steps where id =" . $stepId);
      $surveyStepName = strtoupper(trim($surveyStep['step_title']));
      $activeSheet->setCellValue('A'.$i, "$surveyStepName");
      $activeSheet->getStyle('A'.$i)->applyFromArray($style);
      foreach($question as $data){
        $i++;
        $questionName = trim($data['question']);
        $activeSheet->setCellValue('A'.$i, "$questionName");
        $answer_type = $data['answer_type'];
        $question_id = $data['id'];
        $i++;
        $surveyResponse = $data['survey_responses'];
        $char = "A";
        foreach($surveyResponse as $key => $value){
          $j= $i+1;
          $fieldName = $key;
          if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
            $activeSheet->setCellValue($char.$i, '');
            $activeSheet->setCellValue("A".$j, "");
    
            // for location names 
            $char++;
            $activeSheet->setCellValue($char.$i, "$fieldName");
            $activeSheet->getStyle($char.$i)->applyFromArray($style);
    
            // for result and response heading
            $activeSheet->setCellValue($char.$j, 'RESULT');
            $activeSheet->getStyle($char.$j)->applyFromArray($style);
            
            $char = chr(ord($char) + 1);
            $activeSheet->setCellValue($char.$j, 'RESPONSE');
            $activeSheet->getStyle(($char).$j)->applyFromArray($style);
            $startCell = chr(ord($char) - 1);
            $questionDetails = record_set_single("get_question_details", "SELECT description FROM questions_detail where id =". $key);
            record_set("get_question_details", "select * from questions_detail where surveyid='".$surveyid."' and questionid=$question_id",1);
            $k =$j+1;
            while($row_get_question_details = mysqli_fetch_assoc($get_question_details)){
              $totalResponse = array_sum(array_values($value));
              $perPercentage = 100/$totalResponse;
              $number = ($value[$row_get_question_details['id']]) ? $value[$row_get_question_details['id']] : 0;
              $result = round($number * $perPercentage,2)."%";
              $response = $number;
              $answerName = $row_get_question_details['description'];
              $activeSheet->setCellValue("A".$k, "$answerName");
              $activeSheet->setCellValue("$startCell".$k, "$result");
              $activeSheet->setCellValue("$char".$k, "$response");
              $k++;
            }
          }else{
            // for location names 
            $char++;
            $activeSheet->setCellValue($char.$i, "$locationName");
            $activeSheet->getStyle($char.$i)->applyFromArray($style);
    
            // for result and response heading
            $activeSheet->setCellValue($char.$j, 'S.NO.');
            $activeSheet->getStyle($char.$j)->applyFromArray($style);

            $char = chr(ord($char) + 1);
            $activeSheet->setCellValue($char.$j, 'ANSWER');
            $activeSheet->getStyle($char.$j)->applyFromArray($style);
            $startCell = chr(ord($char) - 1);
            $counter = 1;
            $k =$j+1;
            foreach($value as $answer){
              $activeSheet->setCellValue("A".$k, "");
              $activeSheet->setCellValue("$startCell".$k, "$counter");
              $activeSheet->setCellValue("$char".$k, "$answer");
              $counter++;
              $k++;
            }
          }
          // echo $char++;
        }
        $i = $k;	
      }
    }
    $activeSheet->getStyle('A1')->applyFromArray($style);
    // Save the Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save('document/survey-report-question.xlsx');

    //send mail
  }
}