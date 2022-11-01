<?php
require('../function/connectin_config.php');
require('../function/function.php');
require('../function/get_data_function.php');
include('../permission.php');

$conn = get_connection();
$query = "SELECT * FROM `schedule_report` group by temp_id DESC";
record_set("get_schedule",$query);

while($row_get_schedule = mysqli_fetch_assoc($get_schedule)){ 
    // get all field by their temp_id
    $temp_id = $row_get_schedule['temp_id'];
    $query_schedule = "SELECT * FROM `schedule_report` where temp_id ='$temp_id'";
    record_set("get_query_schedule",$query_schedule);
    $coloum_array = array();

    // add all inserted field in array so we can filter data using this
    while($row_get_data = mysqli_fetch_assoc($get_query_schedule)){
        $coloum_array[$row_get_data['keyword']] = $row_get_data['value'];
        $coloum_array['survey_id']      = $row_get_data['survey_id'];
        $coloum_array['step_id']        = $row_get_data['step_id'];
        $coloum_array['question_id']    = $row_get_data['question_id'];
        $coloum_array['frequency']      = $row_get_data['frequency'];
    }

    $surveyId   = $coloum_array['survey_id'];
    $sectionId  = $coloum_array['step_id'];
    $questionId = $coloum_array['question_id'];

    //checkboxes value
    $location   = $coloum_array['location_id'];
    $department = $coloum_array['department_id'];
    $group      = $coloum_array['group_id'];
    
    $filterData     = '';
    if($location){
        $filterData .= " and locationid IN($location)";
    }
    if($department){
        $filterData .= " and departmentid IN($department)";
    }
    if($group){
        $filterData .= " and groupid IN($group)";
    }
    $query = "SELECT id,question,survey_step_id FROM `questions` WHERE `id` !=0";
    if(!empty($surveyId)){
        $query .=" and surveyid=$surveyId";
    }
    if(!empty($sectionId)){
        $query .=" and survey_step_id IN ($sectionId)";
    }
    if(!empty($questionId)){
        $query .=" and id IN ($questionId)";
    }
    record_set("get_result", $query);
    $question_data = array();
    while($row_get_step = mysqli_fetch_assoc($get_result)){
        $query_anaswer = "SELECT * FROM `answers` WHERE `questionid` =". $row_get_step['id']."$filterData";
        record_set("get_answer_result",$query_anaswer);
        if($totalRows_get_answer_result>0){
            $i=0;
            while($row_get_answer = mysqli_fetch_assoc($get_answer_result)){
                $question_data[$row_get_step['id']]['id'] = $row_get_step['id'];
                $question_data[$row_get_step['id']]['surveyid'] = $row_get_answer['surveyid'];
                $question_data[$row_get_step['id']]['step_id'] = $row_get_step['survey_step_id'];

                $question_data[$row_get_step['id']]['location'] = $row_get_answer['locationid'];
                $question_data[$row_get_step['id']]['department'] = $row_get_answer['departmentid'];
                $question_data[$row_get_step['id']]['group'] = $row_get_answer['groupid'];
                $question_data[$row_get_step['id']]['answertext'][$i]['ans'] = $row_get_answer['answertext'];
                $question_data[$row_get_step['id']]['answertext'][$i]['ansid'] = $row_get_answer['answerid'];
                $i++;
            }
        }
    }
    
    // get question array
    $survey_data = array();
    $i = 0; 
    foreach($question_data as $question){
        // get step
        record_set("get_step", "SELECT * FROM `surveys_steps` WHERE `id` =". $question['step_id']);
        $row_get_step = mysqli_fetch_assoc($get_step);
        $step_title =  trim($row_get_step['step_title']);

        //get questions by their id 
        record_set("get_questions", "SELECT * FROM `questions` WHERE `id` =". $question['id']);
        $row_get_questions = mysqli_fetch_assoc($get_questions);
        $question_name =  $row_get_questions['question'];

        //get survey
        $surveyName = getSurvey()[$question['surveyid']];

        //location
        if(!empty($question['location'])){
            $location = getLocation()[$question['location']];
        }else {
            $location = 'N/A';
        }

        //get department
        if(!empty($question['department'])){
            $department = getDepartment()[$question['department']];
        }else {
            $department = 'N/A';
        }

        //get group
        if(!empty($ques['group'])){
            $groupName = getGroup()[$ques['group']];
        }else {
            $groupName = 'N/A';
        }

        $j=0;
        foreach($question['answertext'] as $answer){
            // get answer
            if($answer['ans']==0){
                record_set("get_survey_questions_detail", "SELECT * FROM `questions_detail` WHERE `id` =". $answer['ansid']);
                $row_get_survey_questions_detail = mysqli_fetch_assoc($get_survey_questions_detail);
                $answer_value = $row_get_survey_questions_detail['description'];
            }else {
                $answer_value = $answer['ans'];
            }
            $survey_data[$i][$j][] = $step_title; 
            $survey_data[$i][$j][] = $question_name;
            $survey_data[$i][$j][] = $answer_value;
            $survey_data[$i][$j][] = $surveyName;
            $survey_data[$i][$j][] = $location;
            $survey_data[$i][$j][] = $department;
            $survey_data[$i][$j][] = $groupName;
            $j++;
        }
        $i++;
    }
    $resp=send_csv_mail($survey_data,'','amitpandey.his@gmail.com', "admin11@gmail.com","Here is Today's Report:");
    if( $resp ){
        echo "Mail sent to ". $email. "<br>" ;
    } else {
        echo "Mail not sent <br>";
    }
    // echo '<pre>';
    // print_r($survey_data);
    // die();
}

// $datas[0][] = 'Amit';
// $datas[0][] = 'Pandey';
// $datas[0][] = 'Roll';
// $datas[0][] = 'Developer';
// $datas[0][] = 'Helpful Insight';

// $datas[1][] = 'Virkam';
// $datas[1][] = 'Singh';
// $datas[1][] = 'Roll';
// $datas[1][] = 'Developer1';
// $datas[1][] = 'Helpful Insight1';

// $datas[2][] = 'Mayur';
// $datas[2][] = 'Sharma';
// $datas[2][] = 'Roll2';
// $datas[2][] = 'Developer2';
// $datas[2][] = 'Helpful Insight2';

//     $email = 'amitpandey.his@gmail.com';
//     echo "emaling now <br>";
//     $resp=send_csv_mail($datas,'','amitpandey.his@gmail.com', "admin11@gmail.com","Here is Today's Report:");
//     if( $resp ){
//     echo "Mail sent to ". $email. "<br>" ;
//     } else {
//     echo "Mail not sent <br>";
//     }
//     echo "fin <br>";
//     echo '<pre>';

function create_csv_string($data2) {

  // Open temp file pointer
  if (!$fp = fopen('php://temp', 'w+')) return FALSE;

  // Loop data and write to file pointer

  foreach ($data2 as $line){
      foreach($line as $l){
        fputcsv($fp, array_values($l));
      }
  }

  // Place stream pointer at beginning
  rewind($fp);

  // Return the data
  return stream_get_contents($fp);

}


function send_csv_mail ($csvData, $body, $to ,  $from,$subject = 'Scheduled Report with attachment' ) {


    //$to = 'amitpandey.his@gmail.com';
  // This will provide plenty adequate entropy
  $multipartSep = '-----'.md5(time()).'-----';

  // Arrays are much more readable
  $headers = array(
    "From: $from",
    "Reply-To: $from",
    "Content-Type: multipart/mixed; boundary=\"$multipartSep\""
  );

  
  // Make the attachment
 
 $attachment = chunk_split(base64_encode(create_csv_string($csvData))); 

  // Make the body of the message
  $body = "--$multipartSep\r\n"
        . "Content-Type: text/plain; charset=ISO-8859-1; format=flowed\r\n"
        . "Content-Transfer-Encoding: 7bit\r\n"
        . "\r\n"
        . "$body\r\n"
        . "--$multipartSep\r\n"
        . "Content-Type: text/csv\r\n"
        . "Content-Transfer-Encoding: base64\r\n"
        . "Content-Disposition: attachment; filename=\"AFILE.csv\"\r\n"
        . "\r\n"
        . "$attachment\r\n"
        . "--$multipartSep--";
  // Send the email, return the result
    return @mail($to, $subject, $body, implode("\r\n", $headers)); 
}
?> 