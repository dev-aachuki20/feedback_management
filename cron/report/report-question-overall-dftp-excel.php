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
    $field_value  = implode(',', $filter['field_value']);
    $interval     = $row_get_reports['time_interval']/24;
    $nextDate     = $row_get_reports['next_date'];
    $startDate    = date('Y-m-d', strtotime("-".$interval." day",strtotime($nextDate)));
    if(is_array($surveyid)){
        $surveyid = implode(',',$surveyid);
    }
    if($data_type == 'location'){
      $ans_filter_query .= " and locationid IN ($field_value)" ;
    }
    if($data_type == 'department'){
      $ans_filter_query .= " and departmentid IN ($field_value)" ;
    }
    if($data_type == 'group'){
      $ans_filter_query .= " and groupid IN ($field_value)";
    }
    if(!empty($startDate) and !empty($nextDate)){
      $ans_filter_query .= " and  cdate between '".date('Y-m-d', strtotime($startDate))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($nextDate)))."'";
    }
    //Survey Questions
    record_set("get_questions", "select * from questions where surveyid='".$surveyid."' and cstatus='1' and parendit='0' order by dposition asc");
    $questions = array();
    while($row_get_questions = mysqli_fetch_assoc($get_questions)){
      $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
      $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
      $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
      $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
    
      /* Get answer values attempted */
      record_set("get_questions_answers", "select * from answers where surveyid='".$surveyid."' and cstatus='1' $ans_filter_query  and questionid = ".$row_get_questions['id'],1);
      while($row_get_questions_answers = mysqli_fetch_assoc($get_questions_answers)){
        $answer_type = $row_get_questions['answer_type'];
        $created_date = date('d-m-Y',strtotime($row_get_questions_answers['cdate']));
        echo $created_date.'<br>';
        if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
          $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$created_date][$row_get_questions_answers['answerid']] += 1;
        }else if($answer_type == 2 || $answer_type == 3){
          $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$created_date][] = ($row_get_questions_answers['answertext']) ? $row_get_questions_answers['answertext'] : 'UnAnswered';
        }
      }
    }
     echo '<pre>';
     print_r($questions);
      die();
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
    $dateParameter = date('d/m/Y', strtotime($startDate)).' - '.date('d/m/Y', strtotime($nextDate));

    $spreadsheet = new Spreadsheet();
    $activeSheet = $spreadsheet->getActiveSheet();
    $activeSheet->mergeCells('A1:C1');
    $activeSheet->mergeCells('A2:C2');
    $activeSheet->mergeCells('A5:I5');

    $activeSheet->setCellValue('A1', $surveyName);
    $activeSheet->setCellValue('A2', $dateParameter);

    $activeSheet->getColumnDimension('A')->setWidth(700, 'px');
    $activeSheet->getColumnDimension('B')->setWidth(100, 'px');
    $activeSheet->getColumnDimension('C')->setWidth(100, 'px');
    $i = 3;

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
        $i++;
        
        if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
          $activeSheet->setCellValue('A'.$i, '');
          $activeSheet->setCellValue('B'.$i, 'RESULT');
          $activeSheet->setCellValue('C'.$i, 'RESPONSES');
        }else{
          $activeSheet->setCellValue('A'.$i, '');
          $activeSheet->setCellValue('B'.$i, 'S.NO.');
          $activeSheet->setCellValue('C'.$i, 'ANSWERS');
        }
    
        $activeSheet->getStyle('A'.$i)->applyFromArray($style);
        $activeSheet->getStyle('B'.$i)->applyFromArray($style);
        $activeSheet->getStyle('C'.$i)->applyFromArray($style);
        $counter = 1;
        
        $sum_of_count = 0;
        foreach($data['survey_responses'] as $key => $value){
          $i++;
          $questionDetails = record_set_single("get_question_details", "SELECT description FROM questions_detail where id =". $key);
          $answer_type = $data['answer_type'];
          if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
          
            $counts = array_values($data['survey_responses']);
            $sum_of_count = array_sum($counts);
            if ($sum_of_count > 0) {
              $perResponsePercentage = 100 / $sum_of_count;
            }
            $scoreValue = round($perResponsePercentage * $value, 2);
            $answerName = $questionDetails['description'];
            $activeSheet->setCellValue('A'.$i, $answerName);
            $activeSheet->setCellValue('B'.$i, $scoreValue.'%');
            $activeSheet->setCellValue('C'.$i, $value);
    
            $activeSheet->getStyle('A'.$i)->applyFromArray(['font' => $style['font']]);
    
          }else{
            $activeSheet->setCellValue('A'.$i, "");
            $activeSheet->setCellValue('B'.$i, $counter);
            $activeSheet->setCellValue('C'.$i, "$value");
            $counter++;
          }
        }
        $i++;
        if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
          $activeSheet->setCellValue('A'.$i, '');
          $activeSheet->setCellValue('B'.$i, 'TOTAL');
          $activeSheet->setCellValue('C'.$i, "$sum_of_count");
          $activeSheet->getStyle('B'.$i)->applyFromArray(['font' => $style['font']]);
          $activeSheet->getStyle('C'.$i)->applyFromArray(['font' => $style['font']]);
        }
        $i++;
      }
    }
    $activeSheet->getStyle('A1')->applyFromArray($style);
    // Save the Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save('document/survey-report-question-'.$report_id.'.xlsx');


    //send mail
    $attachments = array('document/survey-report-question-' . $report_id . '.pdf', 'document/survey-report-question-' . $report_id . '.xlsx');
    $mail_users = explode(",", $row_get_reports['send_to']);
    foreach ($mail_users as $userId) {
      $user_details = get_user_datails($userId);
      $to = $user_details['email'];
      $from_mail = ADMIN_EMAIL;
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
        //unlink($value);
      }
    }
  }
}
?>