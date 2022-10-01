<?php include('function/function.php');
$filename = date("Y-m-d-H-i-s").".csv"; // File Name


header("Content-Disposition: attachment; filename=".$filename."");
header("Content-Type: application/csv");
$rowVal = array();

$new_csv = fopen('php://output', 'w');
$in_csv = fopen('export-report.csv', 'a');
 //Yearly Best & Worst Locations
$currentYear = date('Y');
$datasets = array("best", "worst");

//Locations
$location_labels = array();
$best_locations = array();
$best_location_labels = array();
$best_location_score = array();
$worst_locations = array();
$worst_location_labels = array();
$worst_location_score = array();

$best_question_id = array();
$worst_question_id = array();

$total_survey = '';
$best_total = 0;
$worst_total = 0;

$i=0;
$array_locations = array();
$array_header = array();

if($_SESSION['user_type'] == 1){
    record_set("get_locations","SELECT * FROM locations");
}

if($_SESSION['user_type'] == 2){
    $client_locations = $_SESSION['user_locationid'];
    record_set("get_locations","SELECT * FROM locations where id IN($client_locations)");
}

while($row_get_locations = mysqli_fetch_assoc($get_locations)){

    $loc_id = $row_get_locations['id'];
    $array_locations[]= 'Location Name : '.$row_get_locations['name'];

    $array_header[$i][]='Question';
    $array_header[$i][]='Score';
    $array_header[$i][]='Title';
    $array_header[$i][]='Comment';

    record_set("total_survey","SELECT COUNT(DISTINCT(surveyid)) FROM answers WHERE locationid = $loc_id GROUP BY locationid");
    $row_total_survey = mysqli_fetch_assoc($total_survey);
    $total_survey = $row_total_survey['COUNT(DISTINCT(surveyid))'];

    record_set("per_location","SELECT surveyid,locationid,cby FROM answers WHERE locationid = $loc_id GROUP BY cby");

    if($totalRows_per_location > 0){
    // echo "<pre>";
    while($row_per_location = mysqli_fetch_assoc($per_location)){

      // print_r($row_per_location);
      $locId = $row_per_location['locationid'];
      $surveyid = $row_per_location['surveyid'];
      $cby = $row_per_location['cby'];
      
      $count = array();
      record_set("get_question","select questionid ,questions.answer_type as answer_type from answers left join questions on answers.questionid = questions.id  where answers.surveyid=$surveyid and answers.locationid=$locId and answers.cby=$cby");
      while($row_get_question= mysqli_fetch_assoc($get_question)){
        if($row_get_question['answer_type'] == 1 || $row_get_question['answer_type'] == 4 || $row_get_question['answer_type'] == 6 ){
       
            $questionid = $row_get_question['questionid'];
            
            $questions = array();
            $answer_id = array();

            record_set("get_question_id","select answerid,answerval,questionid,answertext from answers  where questionid=$questionid and surveyid=$surveyid and locationid=$locId");
            while($row_get_question_id = mysqli_fetch_assoc($get_question_id)){
            
                $count[]=$row_get_question_id['cby'];
                $answer_id[] = $row_get_question_id['answerid'];
            } 

            $answers = array_count_values($answer_id);
        
            $total_ans = 0;
            foreach($answers as $key=>$ans){
                $total_ans += number_format((floatval($ans)));
            }
        
            record_set("get_question_detail","select * from questions where id=$questionid");
            $row_get_question_detail = mysqli_fetch_assoc($get_question_detail);
            
            $total_cby = count($count);
        
            $avg = round((floatval($total_ans/$total_cby)*100),2);

            //For best location 
            if($avg >30){

                  $best_locations[$i][trim($row_get_question_detail['question'])] = $avg;

                  $best_location_score[$i][] = $avg;

                  $best_question_id[$i][] = trim($row_get_question_detail['id']);

            }
            
            //For worst location
            if($avg <=30){

                $worst_locations[$i][trim($row_get_question_detail['question'])] = $avg;
                
                $worst_location_score[$i][] = $avg;

                $worst_question_id[$i][] = trim($row_get_question_detail['id']);
            
            }
            
        }//end answer type if
      }
    }
  }
  $i++;
}


$m=0;
 foreach($array_locations as $k=>$val){
    
    $rowVal[$m][]=$val;
    $rowVal[$m][]='';
    $rowVal[$m][]='';
    $rowVal[$m][]='';
    $m++;


    $rowVal[$m][]=$array_header[$k][0];
    $rowVal[$m][]=$array_header[$k][1];
    $rowVal[$m][]=$array_header[$k][2];
    $rowVal[$m][]=$array_header[$k][3];
    $m++;


    $b=0; 
    arsort($best_locations[$k]);
    foreach($best_locations[$k] as $key=>$value){
      if($b<=4 && !empty($best_question_id[$k][$b])){
        //  $rowVal[$m][]='('.$best_question_id[$k][$b].') '.$key;
         $rowVal[$m][]=$key;
         $rowVal[$m][]=$value.'%';
         $rowVal[$m][]='Highest scoring';
         $rowVal[$m][]='';
         $m++;
      }
        $b++;
    }

    $rowVal[$m][]='';
    $rowVal[$m][]='';
    $rowVal[$m][]='';
    $rowVal[$m][]='';
    $m++;

    $p=0; 
    arsort($worst_locations[$k]);
    foreach($worst_locations[$k] as $key=>$value){
      if($p<=4 && !empty($worst_question_id[$k][$p])){
        //  $rowVal[$m][]='('.$worst_question_id[$k][$p].') '.$key;
         $rowVal[$m][]=$key;
         $rowVal[$m][]=$value.'%';
         $rowVal[$m][]='Lowest scoring';
         
         $qid = $worst_question_id[$k][$p];
         record_set("get_question_reports","SELECT * FROM question_reports WHERE question_id=$qid");
        if($totalRows_get_question_reports > 0){
           
            $row_get_question_reports = mysqli_fetch_assoc($get_question_reports);
            
            $rowVal[$m][]=$row_get_question_reports['comment_text'];
        }else{
            $rowVal[$m][]='';
        } 

         $m++;
      }
        $p++;
    }

    $rowVal[$m][]='';
    $rowVal[$m][]='';
    $rowVal[$m][]='';
    $rowVal[$m][]='';
    $m++;
 }


// echo "<pre>";
// print_r($rowVal);
// die();

foreach ($rowVal as $line) {
    fputcsv($new_csv, $line);
}

fclose($new_csv);


?>
