<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    $data_type    = $filter['field'];
    $surveyid     = $filter['survey_id'];
    $interval     = $row_report['sch_interval'] / 24;
    $freqInterval = $row_report['time_interval'] / 24;
    $nextDate     = $row_report['next_date'];
    $startDate    = date('Y-m-d', strtotime("-" . $interval . " day", strtotime($nextDate)));

    if ($row_report['time_interval'] == 24) {
      $timeIntervalArray = getDaily($startDate, $nextDate);
    } else if ($row_report['time_interval'] == 168) {
      $timeIntervalArray = getWeeklyDate($startDate, $nextDate);
    } else if ($row_report['time_interval'] == 720) {
      $timeIntervalArray = getMonthly($startDate, $nextDate);
    } else if ($row_report['time_interval'] == 2160) {
      $timeIntervalArray = getQuarterly($startDate, $nextDate);
    }
    if (!empty($startDate) and !empty($nextDate)) {
      $ans_filter_query .= " and  cdate between '" . date('Y-m-d', strtotime($startDate)) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($nextDate))) . "'";
    }


    //Survey Questions
    record_set("get_questions", "select * from questions where surveyid='" . $surveyid . "' and cstatus='1' and parendit='0' order by dposition asc");
    $questions = array();
    while ($row_get_questions = mysqli_fetch_assoc($get_questions)) {
      $answer_type = $row_get_questions['answer_type'];
      /* Get answer values attempted */
      
      for ($i = 0; $i < count($timeIntervalArray) - 1; $i++) {
        $fromDate = date('Y-m-d', strtotime($timeIntervalArray[$i]));
        $toDate = date('Y-m-d', strtotime($timeIntervalArray[$i + 1]));
        
        $filterData = " and  cdate between '$fromDate' and '" . date('Y-m-d', strtotime($toDate)) . "'";

        record_set("get_questions_answers", "select * from answers where surveyid='" . $surveyid . "' and cstatus='1' $filterData  and questionid = " . $row_get_questions['id']." order By cdate asc");
        $created_date = $fromDate;

        if (!empty($totalRows_get_questions_answers)) {
          while ($row_get_questions_answers = mysqli_fetch_assoc($get_questions_answers)) {
            // $created_date = date('d-m-Y', strtotime($row_get_questions_answers['cdate']));
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
        } 
      }
    
    }
    // echo '<pre>';
    // print_r($questions);
    // echo '</pre>';
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
    if ($interval == 1) {
      $dateParameter = date('d/m/Y', strtotime($startDate));
    } else {
      $dateParameter = date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime("-1 day", strtotime($nextDate)));
    }

    $spreadsheet = new Spreadsheet();
    $activeSheet = $spreadsheet->getActiveSheet();

    $tab = 0;
    foreach($questions as $id => $surveyData){
      $tab++;
      $spreadsheet->createSheet();
      $spreadsheet->setActiveSheetIndex($tab - 1);  // Set the active sheet
      $activeSheet = $spreadsheet->getActiveSheet();
      $dataTypeName = '';
      
      $activeSheet->setTitle("LOCATION ID ".$id);
            
      $activeSheet->setCellValue('A1', $surveyName);
      $activeSheet->setCellValue('A2', $dateParameter);

      $activeSheet->getColumnDimension('A')->setWidth(500, 'px');
      $activeSheet->getColumnDimension('B')->setWidth(100, 'px');
      $activeSheet->getColumnDimension('C')->setWidth(100, 'px');
      $dataTypeName ="";
      if($data_type == 'location'){
        $dataTypeName = getLocation()[$id];
      }else if ($data_type == 'group') {
        $dataTypeName = getGroup()[$id];
      }else if ($data_type == 'department') {
        $dataTypeName = getDepartment()[$id];
      }
      $activeSheet->setCellValue('B1', "$dataTypeName");
      $activeSheet->mergeCells('B1:F1');

      $activeSheet->getStyle('A1')->applyFromArray($style);
      $activeSheet->getStyle('B1')->applyFromArray($style);

      // $activeSheet->getStyle('B'.$i)->applyFromArray($style);

      $i = 3;
      foreach($surveyData as $stepId => $question){
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
          // echo '<pre>';
          // print_r($surveyResponse);
          // echo '</pre>';
          foreach($surveyResponse as $key => $value){
            $j= $i+1;
            if ($freqInterval == 24) {
              $fieldName = $key;
            } else {
              $nextNewDate = date('Y-m-d', strtotime("+" . ($freqInterval-1) . " day", strtotime($key)));
              $isNextDateExceed = check_differenceDate($nextNewDate, $nextDate, 'gt');
              if($isNextDateExceed){
                $nextNewDate = date('Y-m-d', strtotime("-1 day", strtotime($nextDate)));
              }
              $isEqualBothDateEqual = check_differenceDate($nextNewDate, $key, 'eq');
              if($isEqualBothDateEqual){
                $fieldName =  date('d/m/Y', strtotime($key)) ;
              }else{
                $fieldName =  date('d/m/Y', strtotime($key)) . ' - ' . date('d/m/Y', strtotime($nextNewDate));
              }
            }
            if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
              $activeSheet->setCellValue($char.$i, '');
              $activeSheet->setCellValue("A".$j, "");
      
              // for dates  
              $char++;
              $CellTo = chr(ord($char) +1);
              // echo 'start cell : '.$char.' End Cell : '.$CellTo.'<br>';
              // echo "$fieldName => i :".$i.'  J : '.$j.'<br>';
              $activeSheet->mergeCells("$char$i:$CellTo$i");
              $activeSheet->setCellValue($char.$i, $fieldName);
              $activeSheet->getStyle($char.$i)->applyFromArray($style);
              

              // for result and response heading
              $activeSheet->setCellValue($char.$j, 'RESULT');
              $activeSheet->getStyle($char.$j)->applyFromArray($style);
              
              $char = chr(ord($char) + 1);
              $activeSheet->setCellValue($char.$j, 'RESPONSE');
              $activeSheet->getStyle(($char).$j)->applyFromArray($style);
              $startCell = chr(ord($char) - 1);
              
              $questionDetails = record_set_single("get_question_details", "SELECT description FROM questions_detail where id =". $key);
              record_set("get_question_details", "select * from questions_detail where surveyid='".$surveyid."' and questionid=$question_id");
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
    }
    $spreadsheet->setActiveSheetIndex(0);  // Set the active sheet
    
    // Save the Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save('document/survey-report-question-' . $row_report['id'] . '.xlsx');

