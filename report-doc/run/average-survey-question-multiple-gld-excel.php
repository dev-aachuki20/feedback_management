<?php 
require '../../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filter = $_POST;
$data_type = $filter['sch_template_field_name'];
$surveyid   = $filter['survey'];
if($filter['template_field']!='' && count($filter['template_field']) > 0){
	$field_value = implode(',',$filter['template_field']);
}

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
if(!empty($sdate) and !empty($edate)){
    $ans_filter_query .= " and  cdate between '".date('Y-m-d', strtotime($sdate))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($edate)))."'";
}

if(!empty($filter['start_date']) and !empty($filter['end_date'])){
	$ans_filter_query .= " and  cdate between '".date('Y-m-d', strtotime($filter['start_date']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($filter['end_date'])))."'";
}

if(!empty($surveyid)){
	$query = "SELECT * FROM answers  where surveyid in($surveyid) ".$ans_filter_query." group by cdate order by cdate DESC;";
}else{
	echo "Invalid request"; exit;
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
  record_set("get_questions_answers", "select * from answers where surveyid='".$surveyid."' and cstatus='1' $ans_filter_query  and questionid = ".$row_get_questions['id']);
  while($row_get_questions_answers = mysqli_fetch_assoc($get_questions_answers)){
	$answer_type = $row_get_questions['answer_type'];
	if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
		$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$row_get_questions_answers[$data_type.'id']][$row_get_questions_answers['answerid']] += 1;
	}else if($answer_type == 2 || $answer_type == 3){
		$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$row_get_questions_answers[$data_type.'id']][] = ($row_get_questions_answers['answertext']) ? $row_get_questions_answers['answertext'] : 'UnAnswered';
	}
  }
} 

// echo '<pre>';
// print_r($questions);
// echo '</pre>';
// die();
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

// echo '<pre>';
// echo "Amitt";
// print_r($questions);
// die();
// echo '</pre>';
$surveyName = getSurvey()[$surveyid];
$dateParameter = date('d/m/Y', strtotime($filter['start_date'])).' - '.date('d/m/Y', strtotime($filter['end_date']));

$spreadsheet = new Spreadsheet();
$activeSheet = $spreadsheet->getActiveSheet();
// Merge cells A1 to C1 (you can change the range as needed)
$activeSheet->mergeCells('A1:C1');
$activeSheet->mergeCells('E1:G1');
$activeSheet->setCellValue('A1', $surveyName);
$activeSheet->setCellValue('E1', $dateParameter);

$activeSheet->getColumnDimension('A')->setWidth(600, 'px');

$i = 2;

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
			$fieldName = '';
			if($data_type == 'location'){
				$fieldName = getLocation()[$key];
			}else if($data_type == 'group'){
				$fieldName = getGroup()[$key];
			}else if($data_type == 'department'){
				$fieldName = getDepartment()[$key];
			}
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
				$activeSheet->mergeCells($startCell.$i .":". $char.$i);
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
				$activeSheet->setCellValue($char.$j, 'Respondent');
				$char = chr(ord($char) + 1);
				$activeSheet->setCellValue($char.$j, 'ANSWER');
				$startCell = chr(ord($char) - 1);
				$activeSheet->mergeCells($startCell.$i .":". $char.$i);
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
$activeSheet->getStyle('E1')->applyFromArray($style);
// Save the Excel file
// $writer = new Xlsx($spreadsheet);
// $writer->save('excel/average-survey-question-multiple-location-55.xlsx');

$filename = 'Survey Report Question -' . date('Y-m-d-H-i-s') . '.xlsx';
// $filename = 'Survey Report Question -' . date('Y-m-d-H-i-s') . '.xls';
try {
    $writer = new Xlsx($spreadsheet);
	$writer->save('excel/survey-question-overall.xlsx');
    $content = file_get_contents('excel/survey-question-overall.xlsx');
} catch(Exception $e) {
    exit($e->getMessage());
}
header("Content-Disposition: attachment; filename=".$filename);
unlink('excel/survey-question-overall.xlsx');
exit($content);
