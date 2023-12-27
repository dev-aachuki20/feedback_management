<?php 
require('../../function/function.php');
require('../../function/get_data_function.php');
require '../../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filter = $_POST;
$data_type = $filter['sch_template_field_name'];
$surveyid   = $filter['survey'];
$field_value = implode(',',$filter['template_field']);

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
record_set("get_questions", "select * from questions where surveyid='".$surveyid."' and cstatus='1' and parendit='0' order by dposition asc",1);
$questions = array();
while($row_get_questions = mysqli_fetch_assoc($get_questions)){
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];

  /* Get answer values attempted */
  record_set("get_questions_answers", "select * from answers where surveyid='".$surveyid."' and cstatus='1' $ans_filter_query  and questionid = ".$row_get_questions['id']		);
  while($row_get_questions_answers = mysqli_fetch_assoc($get_questions_answers)){


	$answer_type = $row_get_questions['answer_type'];
	if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
		$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$row_get_questions_answers['locationid']][$row_get_questions_answers['answerid']] += 1;
	}else if($answer_type == 2 || $answer_type == 3){
		$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$row_get_questions_answers['locationid']][] = ($row_get_questions_answers['answertext']) ? $row_get_questions_answers['answertext'] : 'UnAnswered';
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

$surveyName = getSurvey()[$surveyid];
$dateParameter = date('d/m/Y', strtotime($filter['start_date'])).' - '.date('d/m/Y', strtotime($filter['end_date']));

$spreadsheet = new Spreadsheet();
$activeSheet = $spreadsheet->getActiveSheet();
// Merge cells A1 to C1 (you can change the range as needed)
$activeSheet->mergeCells('A1:C1');
$activeSheet->mergeCells('E1:G1');
$activeSheet->setCellValue('A1', $surveyName);
$activeSheet->setCellValue('E1', $dateParameter);

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
		$i++;
		
		if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
			$activeSheet->setCellValue('A'.$i, '');
			$activeSheet->setCellValue('B'.$i, 'LOCATION 1');
	
			$activeSheet->getStyle('B'.$i)->applyFromArray($style);
			$activeSheet->mergeCells("B$i:C$i");

			$i++;
			$activeSheet->setCellValue('A'.$i, '');
			$activeSheet->setCellValue('B'.$i, 'RESULT');
			$activeSheet->setCellValue('C'.$i, 'RESPONSE');

			$activeSheet->getStyle('B'.$i)->applyFromArray($style);
			$activeSheet->getStyle('C'.$i)->applyFromArray($style);

		}
		$counter = 1;
		
	}
}


$activeSheet->getStyle('A1')->applyFromArray($style);

// Save the Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('excel/average-survey-question-multiple-location.xlsx');