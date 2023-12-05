<?php 
require('../../function/function.php');
require('../../function/get_data_function.php');
$filename = $_GET["name"].date(" Y-m-d-H-i-s").".xls"; 

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


$allQuestion = "SELECT * FROM `questions` WHERE `surveyid` = $surveyid and cstatus=1";
record_set('Questions',$allQuestion);


$question_array=array();
while ($row_ques_query = mysqli_fetch_assoc($Questions)) {
	$question_array[$row_ques_query['id']] = $row_ques_query['question'];
}
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Type: application/vnd.ms-excel");

/** Csv Heading  */
$csv_heading = array();
$questionQuery ="SELECT * FROM questions where surveyid =$surveyid and cstatus=1 order by id ASC,dposition asc";
record_set('getquestionQuery',$questionQuery);
$csv_heading[]		= 'Date'; 
$csv_heading[]		= 'Survey ID'; 
$csv_heading[]		= 'First Name'; 
$csv_heading[] 		= 'Last Name'; 
$csv_heading[]  	= 'Phone Number'; 
$csv_heading[]		= 'Email'; 
$csv_heading[]	    = 'Group';
$csv_heading[]		= 'Location';
$csv_heading[]   	= 'Department';
$csv_heading[]      = 'Role';
$csv_heading[] 		= 'Created Date'; 

while ($row_getquestionQuery = mysqli_fetch_assoc($getquestionQuery)) {
	$csv_heading[] = $row_getquestionQuery['question']; 
}

$flag = false;
record_set('getdata',$query);
if($totalRows_getdata>0){
	$i=0;
	$row_excel_data = array();
	while ($row_getdata = mysqli_fetch_assoc($getdata)) {
		if(!empty($filter['start_date']) and !empty($filter['end_date'])){
			$row_excel_data[$i]['Date'] = date('d/m/Y', strtotime($filter['start_date'])) .'-'.date('d/m/Y', strtotime($filter['end_date']));
		}

		$row_excel_data[$i]['Survey ID'] 	= $row_getdata['surveyid']; 
		$row_excel_data[$i]['First Name'] 	= ''; 
		$row_excel_data[$i]['Last Name'] 	= ''; 
		$row_excel_data[$i]['Phone Number'] = ''; 
		$row_excel_data[$i]['Email'] 		= ''; 

		$row_excel_data[$i]['Group'] 	    = getGroup()[$row_getdata['groupid']] ; 
		$row_excel_data[$i]['Location']     = getLocation()[$row_getdata['locationid']]; 
		$row_excel_data[$i]['Department']   = getDepartment()[$row_getdata['departmentid']]; 
		$row_excel_data[$i]['Role']         = getRole()[$row_getdata['roleid']]; 
		$row_excel_data[$i]['Created Date'] 		= $row_getdata['cdate']; 

		//$row_excel_data[$i]['School'] 	= '';  
	
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
	arsort($row_excel_data);
	foreach($row_excel_data as $data){
		if (!$flag) {
			// display field/column names as first row
			echo implode("\t", array_keys($data)) . "\r\n";
			$flag = true;
		}
		echo implode("\t", array_values($data)) . "\r\n";	
	}	
}else{
		echo implode("\t", $csv_heading) . "\r\n";
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