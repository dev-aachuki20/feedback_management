<?php 
require('../../function/function.php');
require('../../function/get_data_function.php');
require '../../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filter = $_POST;
$data_type = $filter['sch_template_field_name'];
$surveyid   = $filter['survey'];

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
  $answer_type = $row_get_questions['answer_type'];
  	if (!empty($totalRows_get_questions_answers)) {
		while($row_get_questions_answers = mysqli_fetch_assoc($get_questions_answers)){
			if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
				$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][$row_get_questions_answers['answerid']] += 1;
			}else if($answer_type == 2 || $answer_type == 3){
				$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['survey_responses'][] = ($row_get_questions_answers['answertext']) ? $row_get_questions_answers['answertext'] : 'UnAnswered';
			}
		}
	}else if ($answer_type == 1) {
		record_set("get_child_questions", "select * from questions where parendit='" . $row_get_questions['id'] . "' and cstatus='1'");
		if (!empty($totalRows_get_child_questions)) {
			$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['having_child'] = true;

			record_set("get_parent_question_options", "select id from questions_detail where surveyid='" . $surveyid . "' and questionid = " . $row_get_questions['id']);
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
		
				$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['survey_responses'] = $options;
		
				record_set("get_child_questions_answers", "select * from answers where surveyid='" . $surveyId . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_child_question['id']);
		
				if (!empty($totalRows_get_child_questions_answers)) {
				  while ($row_get_child_questions_answer = mysqli_fetch_assoc($get_child_questions_answers)) {
					$questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['children'][$row_get_child_question['id']]['survey_responses'][$row_get_child_questions_answer['answerid']]  += 1;
				  }
				}
			  }
		}
	}
} 
echo '<pre>';
print_r($questions);
echo '</pre>';
echo '<hr/>';
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
			if (!array_key_exists("having_child", $data)) {
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
			}else{
				if ($data['answer_type'] == 1) {
					$activeSheet->setCellValue('A'.$i, '');
					record_set("get_parent_question_options", "select id, description from questions_detail where surveyid= $surveyid and questionid = " . $data['id']);
					$char = "B";
					$j = $i+1;
					while ($row_parent_question_option = mysqli_fetch_assoc($get_parent_question_options)) {
						$activeSheet->setCellValue($char.$i, strtoupper($row_parent_question_option['description']));
						$startCell = chr(ord($char));
						$char = chr(ord($char) + 1);
						$activeSheet->setCellValue($startCell.$j, strtoupper("RESULT"));
						$activeSheet->setCellValue($char.$j, strtoupper("RESPONSE"));

						$activeSheet->mergeCells("$startCell$i:$char$i");
						$activeSheet->getStyle("$startCell$i")->applyFromArray($style);
						$char = chr(ord($char) + 1);
					}
					
					$char = "B";
					$j++;
					foreach($data['children'] as $childQuestion){
						$startCell = chr(ord($char));
						$char = chr(ord($char) + 1);
						$responseSum = array_sum(array_values($childQuestion['survey_responses']));
						if($perPercent > 0){
							$perPercent = 100/$responseSum;
						}else{
							$perPercent = 0;
						}
						$activeSheet->setCellValue("A".$j,  $childQuestion['question']);

						$sCell = $startCell;
						$eCell = $char;
						foreach($childQuestion['survey_responses'] as $optionKey => $optionValue){
							$result = round($perPercent*$optionValue)."%";
							$activeSheet->setCellValue($sCell.$j, $result);
							$activeSheet->setCellValue($eCell.$j, $optionValue);
							$sCell = chr(ord($eCell) + 1);
							$eCell = chr(ord($eCell) + 2);
						}
						$char = "B";
						$j++;
					}
				}
			}
		}else{
			$activeSheet->setCellValue('A'.$i, '');
			$activeSheet->setCellValue('B'.$i, 'S.NO.');
			$activeSheet->setCellValue('C'.$i, 'ANSWERS');
		}

		$activeSheet->getStyle('A'.$i)->applyFromArray($style);
		$activeSheet->getStyle('B'.$i)->applyFromArray($style);
		$activeSheet->getStyle('C'.$i)->applyFromArray($style);
		$counter = 1;
		// foreach($data['survey_responses'] as $key => $value){
		// 	$i++;
		// 	$questionDetails = record_set_single("get_question_details", "SELECT description FROM questions_detail where id =". $key);
		// 	$answer_type = $data['answer_type'];
		// 	if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
		// 		$counts = array_values($data['survey_responses']);
		// 		$sum_of_count = array_sum($counts);
		// 		if ($sum_of_count > 0) {
		// 			$perResponsePercentage = 100 / $sum_of_count;
		// 		}
		// 		$scoreValue = round($perResponsePercentage * $value, 2);
		// 		$answerName = $questionDetails['description'];
		// 		$activeSheet->setCellValue('A'.$i, $answerName);
		// 		$activeSheet->setCellValue('B'.$i, $scoreValue.'%');
		// 		$activeSheet->setCellValue('C'.$i, $value);

		// 		$activeSheet->getStyle('A'.$i)->applyFromArray(['font' => $style['font']]);

		// 	}else{
		// 		$activeSheet->setCellValue('A'.$i, "");
		// 		$activeSheet->setCellValue('B'.$i, $counter);
		// 		$activeSheet->setCellValue('C'.$i, "$value");
		// 		$counter++;
		// 	}
		// }
		$i++;
		if ($answer_type == 1 || $answer_type == 4 || $answer_type == 6) {
			// $activeSheet->setCellValue('A'.$i, '');
			// $activeSheet->setCellValue('B'.$i, 'TOTAL');
			// $activeSheet->setCellValue('C'.$i, "$sum_of_count");
			// $activeSheet->getStyle('B'.$i)->applyFromArray(['font' => $style['font']]);
			// $activeSheet->getStyle('C'.$i)->applyFromArray(['font' => $style['font']]);
		}
		$i++;
	}
}


$activeSheet->getStyle('A1')->applyFromArray($style);

// Save the Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('excel/survey-question-overall.xlsx');