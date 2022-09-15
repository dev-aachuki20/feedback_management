<?php include('function/function.php');
$filename = date("Y-m-d-H-i-s").".csv"; // File Name

$surveyid=$_GET['surveyid'];

if(!empty($_GET['surveyid'])){
    $answerid  = (!empty($_GET['aid']))? $_GET['aid']:'';
    $answerval = (!empty($_GET['avl']))? $_GET['avl']:'';

    if(!empty($_GET['userid'])){
        $uid = $_GET['userid'];
        $query = "SELECT * FROM answers WHERE surveyid=$surveyid AND cby=$uid AND answerid=$answerid AND answerval = $answerval order by cdate DESC";
    }else{
        $query = "SELECT * FROM answers WHERE surveyid=$surveyid AND answerid=$answerid AND answerval = $answerval order by cdate DESC";
    }

}else{
    echo "Invalid request"; exit;
}

header("Content-Disposition: attachment; filename=".$filename."");
header("Content-Type: application/csv");
$rowVal = array();

 $new_csv = fopen('php://output', 'a+');
 $in_csv = fopen('export-survey.csv', 'a+');
 $headers = fgets($in_csv);

?>

<?php
$i=0;
record_set('getdata',$query);
if($totalRows_getdata>0){
	while ($row_getdata = mysqli_fetch_assoc($getdata)) { 
        $row_survey_entry = 1;
        //survey details
        record_set("get_survey_detail", "SELECT id,name FROM surveys where id='".$row_getdata['surveyid']."'");	
        $row_get_survey_detail = mysqli_fetch_assoc($get_survey_detail);

        record_set("survey_entry", "SELECT DISTINCT cby FROM answers where surveyid='".$row_get_survey_detail['id']."' and cby <".$row_getdata['cby']);
        $row_survey_entry = $totalRows_survey_entry+$row_survey_entry;
     
        // survey overall result
        record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_getdata['surveyid']."' and cby='".$row_getdata['cby']."'");
		  $total_result_val = $totalRows_get_survey_result*10;
		  $achieved_result_val = 0;
		  $to_bo_contacted = 0;
		  while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
		  	$achieved_result_val += $row_get_survey_result['answerval'];
  			if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 10){
  				 $to_bo_contacted = 1;
  			}
		  }
		  $result_response = $achieved_result_val*100/$total_result_val;
		  $label_class = 'success';
		  if($result_response<50){
			  $label_class = 'danger';
		  }else 
		  if($result_response<75){
			  $label_class = 'info';
		  }

        // location
        record_set("get_location", "SELECT name FROM locations where id='".$row_getdata['locationid']."'");	
        $row_get_location = mysqli_fetch_assoc($get_location);

        // Department
        record_set("get_department", "SELECT name FROM departments where id='".$row_getdata['departmentid']."'");	
        $row_get_department = mysqli_fetch_assoc($get_department);

        //Contact Details
        $allContactDetail = json_decode($row_getdata['answertext'],true);

        //In progress
        record_set("get_action_progress", "select * from survey_contact_action where user_id=".$row_getdata['cby']." and action='1'");
        $row_get_action_progress = mysqli_fetch_assoc($get_action_progress);
        $progess_date = (!empty($row_get_action_progress['created_date']))?date("d-m-Y", strtotime($row_get_action_progress['created_date'])):'';
        $progess_comment = (!empty($row_get_action_progress['comment']))?$row_get_action_progress['comment']:'';

        //Void
        record_set("get_action_void", "select * from survey_contact_action where user_id=".$row_getdata['cby']." and action='2'");
        $row_get_action_void = mysqli_fetch_assoc($get_action_void);
        $void_date = (!empty($row_get_action_void['created_date']))?date("d-m-Y", strtotime($row_get_action_void['created_date'])):'';
        $void_comment = (!empty($row_get_action_void['comment']))?$row_get_action_void['comment']:'';

        //resolved
        record_set("get_action_resolved", "select * from survey_contact_action where user_id=".$row_getdata['cby']." and action='3'");
        $row_get_action_resolved = mysqli_fetch_assoc($get_action_resolved);
        $resolved_date = (!empty($row_get_action_resolved['created_date']))?date("d-m-Y", strtotime($row_get_action_resolved['created_date'])):'';
        $resolved_comment = (!empty($row_get_action_resolved['comment']))?$row_get_action_resolved['comment']:'';

        $rowVal[$i][]=date("d-m-Y", strtotime($row_getdata['cdate']));
        $rowVal[$i][]=ordinal($row_survey_entry).' - '.$row_get_survey_detail['name'].' - '.round($result_response,2);
        $rowVal[$i][]=$row_get_location['name'];
        $rowVal[$i][]=$row_get_department['name'];
        $rowVal[$i][]=$allContactDetail['first_name'];
        $rowVal[$i][]=$allContactDetail['last_name'];
        $rowVal[$i][]=$allContactDetail['to_be_contact_mail'];
        $rowVal[$i][]=$allContactDetail['phone_number'];
        $rowVal[$i][]=$progess_date;
        $rowVal[$i][]=$progess_comment;
        $rowVal[$i][]=$void_date;
        $rowVal[$i][]=$void_comment;
        $rowVal[$i][]=$resolved_date;
        $rowVal[$i][]=$resolved_comment;

        $i++;
	}

    fputcsv($new_csv, explode(',', $headers));
    foreach ($rowVal as $line) {
        fputcsv($new_csv, $line);
      }

    fclose($new_csv);
}

?>
