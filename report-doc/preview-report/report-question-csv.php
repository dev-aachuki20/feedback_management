<?php 
require('../../function/function.php');
$filename = $_GET["name"].date(" Y-m-d-H-i-s").".xls"; 

$filter = $_POST;
$data_type = $filter['sch_template_field_name'];
$surveyid   = $filter['survey'];
$field_value = implode(',',$filter['template_field']);

if(is_array($surveyid)){
    $surveyid = implode(',',$surveyid);
}

if($data_type == 'location'){
	$ans_filter_query .= " and locationid = ".$field_value ;
}
if($data_type == 'department'){
	$ans_filter_query .= " and departmentid = ".$field_value ;
}
if($data_type == 'group'){
	$ans_filter_query .= " and groupid = ".$field_value ;
}
if(!empty($surveyid)){
	$query = "SELECT * FROM answers  where surveyid in($surveyid) ".$ans_filter_query." group by cdate order by cdate DESC;";
}else{
	echo "Invalid request"; exit;
}

$allQuestion = "SELECT * FROM `questions` WHERE `surveyid` = $surveyid and cstatus=1";
record_set('Questions',$allQuestion);
$question_array=array();
while ($row_ques_query = mysqli_fetch_assoc($Questions)) {
	$question_array[$row_ques_query['id']] = $row_ques_query['question'];
}
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Type: application/vnd.ms-excel");

$flag = false;
record_set('getdata',$query);
if($totalRows_getdata>0){
	$i=0;
	$row_excel_data = array();
	while ($row_getdata = mysqli_fetch_assoc($getdata)) {
		$row_excel_data[$i]['Date'] 		= $row_getdata['cdate']; 
		$row_excel_data[$i]['Survey ID'] 	= $row_getdata['surveyid']; 
		$row_excel_data[$i]['First Name'] 	= ''; 
		$row_excel_data[$i]['Last Name'] 	= ''; 
		$row_excel_data[$i]['Phone Number'] = ''; 
		$row_excel_data[$i]['Email'] 		= ''; 
		//$row_excel_data[$i]['School'] 		= ''; 
	
		$sub_query ="SELECT * FROM questions LEFT JOIN answers ON questions.id = answers.questionid and answers.cdate ='".$row_getdata['cdate']."' where questions.surveyid =$surveyid and questions.cstatus=1 order by questions.id ASC,questions.dposition asc";

		$contact_query ="SELECT * FROM answers  where surveyid =$surveyid and answers.cdate ='".$row_getdata['cdate']."'";
		record_set('contact_query',$contact_query);
		while ($row_contact_query = mysqli_fetch_assoc($contact_query)) {
			if($row_contact_query['answerid']==-2){
				$data = json_decode($row_contact_query['answertext']);
				foreach($data as $key =>$value){
				
					if($key =='first_name'){
						$row_excel_data[$i]['First Name'] = ($value)?$value:'N/A';
					}
					if($key =='last_name'){
						$row_excel_data[$i]['Last Name'] = ($value)?$value:'N/A';
					}
					if($key =='phone_number'){
						$row_excel_data[$i]['Phone Number'] = ($value)?$value:'N/A';
					}
					if($key =='to_be_contact_mail'){
						$row_excel_data[$i]['Email'] = ($value)?$value:'N/A';
					}
				}
			}else if($row_contact_query['answerid']==-3){
				//$row_excel_data[$i]['School'] = $row_contact_query['answertext'];
			}
		}

		record_set('sub_queryss',$sub_query);
		while($row_sub_query = mysqli_fetch_assoc($sub_queryss)) {
			if($row_sub_query['answertext'] === '0' && $row_sub_query['answertext']!='') {
				record_set('question_details',"SELECT * FROM `questions_detail` WHERE `id` ='".$row_sub_query['answerid']."'");
				$row_question_details = mysqli_fetch_assoc($question_details);
				$row_excel_data[$i][$row_sub_query['question']] = $row_question_details['description'];
			}else{
				$row_excel_data[$i][$row_sub_query['question']] = $row_sub_query['answertext'];
			}
		}
		$i++;
	}	
}
arsort($row_excel_data);
foreach($row_excel_data as $data){
	if (!$flag) {
		// display field/column names as first row
		echo implode("\t", array_keys($data)) . "\r\n";
		$flag = true;
	}
	echo implode("\t", array_values($data)) . "\r\n";	
}

// $zipArchive = new ZipArchive();
// $zipFile = "./example-zip-file.zip";
// if ($zipArchive->open($zipFile, ZipArchive::CREATE) !== TRUE) {
//     exit("Unable to open file.");
// }
// $folder = 'preview-document/';
// createZip($zipArchive, $folder);
// $zipArchive->close();
// echo 'Zip file created.';
?>