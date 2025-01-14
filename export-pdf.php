<?php 
include('function/function.php');
include('function/get_data_function.php');

$surveyid=$_GET['surveyid'];
$surveyName = 'Default';
if(isset($_GET['surveyid'])){
	record_set("get_survey", "select * from surveys where id='".$surveyid."' and cstatus=1 ");	
	if($totalRows_get_survey>0){
		$row_get_survey = mysqli_fetch_assoc($get_survey);
    $surveyName = $row_get_survey['name'];
	}else{
		echo 'Wrong survey ID.'; exit;
	}
}else{
	echo 'Missing survey ID.';  exit;
}
$ans_filter_query='';
if($_REQUEST['userid']){
	$ans_filter_query .= " and cby='".$_REQUEST['userid']."' ";
}
// if($_REQUEST['month']){
// 	$ans_filter_query .= " and cdate like '".$_REQUEST['month']."-%' ";
// }
if($_REQUEST['start'] and $_REQUEST['end']){
	$ans_filter_query .= " and cdate between '".$_REQUEST['start']."' and '".$_REQUEST['end']."'";
}
if(!empty($_REQUEST['location']) && $_REQUEST['location']!=4){
	$ans_filter_query .= " and locationid = ".$_REQUEST['location'];
}
//Survey Steps 
$survey_steps = array();
if($row_get_survey['isStep'] == 1){
  record_set("get_surveys_steps", "select * from surveys_steps where survey_id='".$surveyid."' order by step_number asc");
  while($row_get_surveys_steps = mysqli_fetch_assoc($get_surveys_steps)){
    $survey_steps[$row_get_surveys_steps['id']]['number'] = $row_get_surveys_steps['step_number'];
    $survey_steps[$row_get_surveys_steps['id']]['title'] = $row_get_surveys_steps['step_title'];
  }
}

//Survey Questions
record_set("get_questions", "select * from questions where surveyid='".$surveyid."' and cstatus='1' and parendit='0' order by dposition asc");
$questions = array();
while($row_get_questions = mysqli_fetch_assoc($get_questions)){
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
  $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
} 
record_set("get_loc_dep", "select locationid, departmentid from answers where surveyid='".$surveyid."' ".$ans_filter_query);
$row_get_loc_dep = mysqli_fetch_assoc($get_loc_dep);

//Department
record_set("get_department", "select name from departments where id = '".$row_get_loc_dep['departmentid']."'");
$row_get_department = mysqli_fetch_assoc($get_department);

//Location
record_set("get_location", "select name from locations where id = '".$row_get_loc_dep['locationid']."'");
$row_get_location = mysqli_fetch_assoc($get_location);
if(!empty($_GET['location']) and $_GET['location']!=4){
     $location_name =$row_get_location['name'];
}else{
     $location_name ="All";
}
$message = '<!DOCTYPE html>
<html lang="en">
<head>
<style>
@page{
  margin: 1cm;
  margin-bottom: 1cm;
}
body{
  padding-bottom: 50px;
}
footer {
  position: fixed;
  bottom: 30px;
  width: 100%;
  text-align: center;
}
</style>
</head>
    <body>
    <table width="100%">
      <thead>
        <tr>
          <td colspan="4" style="text-align:center; margin-top:10px;margin-bottom:10px;">
          <img src="'.MAIN_LOGO.'" width="200">
          </td>
        </tr>
      </thead>
      <tbody style="border-top:1px solid black;border-bottom:1px solid black;">
        <tr style="">
          <td colspan="4" style="text-align:center;font-size:20px;"><span>'.$row_get_survey['name'].'</span></td>
        </tr>
      </tbody>
    </table>';


$message .= '<table width="100%" style="text-align:center;">
      <thead>
        <tr>
          <td">Total Survey</td>
          <td">Contacted Requested</td>
          <td">Average result Score</td>
        </tr>
      </thead>
      <tbody style="">
        <tr style="">
          <td>'.$_GET['survey-count'].'</td>
          <td>'.$_GET['contact'].'</td>
          <td>'.$_GET['score'].'%</td>
        </tr>
      </tbody>
  </table>';

$message .='<footer style="page-break-inside:avoid;">
<div style="text-align: center;" >
<span style="font-size:14px;">'.POWERED_BY.'</span>
<center><img  src="'.FOOTER_LOGO.'" alt="" width="150" style="background-color: #fff;"/></center>
</div>
</footer>';


if(count($survey_steps)>0){
  $mainCount = 1; 
  foreach($survey_steps AS $key => $value) { 
    $message .= '<div class="container">';

      $qcount = 0;
      foreach($questions[$key] AS $question){
        $qcount++;

        $questionid   = $question['id'];
        $answer_type  = $question['answer_type'];
        $totalRows_get_child_questions = 0;
        //1=radio	2=textbox	3=textarea	4=rating
        $questions_array = array();
        $answers_array = array();
        if($answer_type==1 || $answer_type==4 || $answer_type==6){
          //echo 'get_questions_detail';
          record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'");	
          if($totalRows_get_questions_detail>0){
            while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
              $questions_array[$row_get_questions_detail['id']][] = $row_get_questions_detail['description'];
              $questions_array[$row_get_questions_detail['id']][] = $row_get_questions_detail['answer'];
            }
          }
          record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."'");	
          if($totalRows_get_answers>0){
            while($row_get_answers = mysqli_fetch_assoc($get_answers)){
              $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
            }
          }
          $counts = array_count_values($answers_array);
        }

        if($answer_type==2 || $answer_type==3){
          record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' ");  
          if($totalRows_get_answers>0){
            while($row_get_answers = mysqli_fetch_assoc($get_answers)){
              $answers_array[$row_get_answers['id']] = $row_get_answers['answertext'];
            }
          }
          $counts = array_count_values($answers_array);
        }
      
        $message .= '<div style="page-break-inside:avoid;">';
        if($answer_type==1 || $answer_type==4 || $answer_type==6){
        
          if($answer_type==1){
            //get Child Questions
            $get_child_questions = "select * from questions where parendit='".$questionid."' and cstatus='1'";
            record_set("get_child_questions", $get_child_questions);
          }
          if(empty($totalRows_get_child_questions)){
              $message .='<table width="505px" align="center" style="page-break-inside: avoid;">
                <tr>
                  <td align="center" colspan="3">';
                  if($qcount == 1){ $message .='
                    <h4 align="center" style="margin-top:10px;margin-bottom:10px;">'.$value['title'].'</h4>';
                  } $message .='
                  <h4 colspan="2" style="margin-top:10px;text-align:center;">'.$question['question'].'</h4>
                  </td>
                </tr>
                <tr>
                  <td width="304px;">';
                    $clr_loop=0;
                    $table_display_data = array();
                    foreach($questions_array as $key=>$val){
                      $clr_loop++;
                      $ansId = array_keys($counts)[0];
                      if($key ==$ansId ){
                        $percentage = $questions_array[$key][1] ;
                      }else {
                        $percentage = 0;
                      }
                      // //$percentage = ($total_ans*$total_num)/$total_num;
                      $table_display_data[$val[0]]['percantage']=$percentage;
                      $table_display_data[$val[0]]['count']=$counts[$key];
                    } 
                    
                    $message .= '
                  </td>
                </tr>
              </table>
              <table style="font-size:14px;" width="505px" align="center" cellspacing="0" cellpadding="4" >
                <tr>
                  <th style="background-color:#f0f0f0; border: 1px solid ;border-bottom: none;">ANSWERS</th>
                  <th style="background-color:#f0f0f0;width:80px;border: 1px solid ;border-bottom: none;text-align:center;">RESULT</th>
                  <th style="background-color:#f0f0f0;width:70px;border: 1px solid;border-bottom: none;text-align:center;">RESPONSES</th>
                </tr>';
                $total = 0;
                
                $sum_of_count = array_sum(array_column($table_display_data, "count"));
                $perResponsePercentage = 100/$sum_of_count;
                if(count(array_filter($table_display_data)) > 0){
                  foreach($table_display_data as $key=>$val){ 
                    $scoreValue = round($perResponsePercentage*$val['count'],2);
                    if(is_nan($scoreValue)){
                      $scoreValue = 0;
                    }
                    $response = ($val['count'])?$val['count']:0;
                    
                    $message .='<tr>
                      <td style="border: 1px solid">'.$key.'</td>
                      <td style="text-align:center;border: 1px solid">'.$scoreValue.'%</td>
                      <td style="text-align:center;border: 1px solid">'.$response.'</td>
                    </tr>';
                    $total +=$val['count'];
                  } 
                  $message .='<tr style="border:none;">
                    <td style="border:none;"></td>
                    <td style="border:none;text-align:center;"><strong>TOTAL</strong></td>
                    <td style="border:none;text-align:center"><strong>'.$total.'</strong></td>
                  </tr>';
                } else{ 
                  $message .=  '<tr><td colspan="3"  align="center" style="border: 1px solid;">NO ANSWER AVAILABLE</td></tr>';
                }                
              $message .='</table>';
          } else{ 
            $message .= '<table width="505px" align="center" style="page-break-inside: avoid;">
              <tr>
                <td align="center" colspan="3">';
                  if($qcount == 1){ $message .='
                    <h4 align="center" style="margin-top:10px;margin-bottom:10px;">'.$value['title'].'</h4>';
                  } $message .='
                  <h3 style="margin-top:10px;">'.$question['question'].'</h3>
                </td>
              </tr>
            </table>
            <table width="505px" align="center" style="font-size:14px;border-collapse: collapse;font-family: Arial, Helvetica, sans-serif;width: 505px;word-break: break-word;border-bottom: 1px solid #000 !important;" border="1" cellspacing="0" cellpadding="4">
              <thead>
              <tr>
              <th  style="background-color:#f0f0f0;" rowspan="2"></th>';

              record_set("get_parent_question_options", "select id, description from questions_detail where surveyid='" . $surveyid . "' and questionid = " . $question['id']);
              $options = [];
              if (!empty($totalRows_get_parent_question_options)) {
                $i = 0;
                while ($row_parent_question_option = mysqli_fetch_assoc($get_parent_question_options)) {
                  $options[$row_parent_question_option['id']] = 0;
                  $message .= '<th style="background-color:#f0f0f0;" colspan="2">' . $row_parent_question_option['description'] . '</th>';
                  $i++;
                }
                $message .= '</tr><tr>';

                for ($j = $i; $j > 0; $j--) {
                  $message .= '<th style="word-break: break-word;">RESULT</th><th style="word-break: break-word;">RESPONSES</th>';
                }
                $message .= '</tr></thead><tbody><tr>';
              }

              $childQues = [];
              $mergedSurveyResponses = [];
              if (!empty($totalRows_get_child_questions)) {
                while ($row_get_child_question = mysqli_fetch_assoc($get_child_questions)) {
                  $childQues[$row_get_child_question['id']]['id']           = $row_get_child_question['id'];
                  $childQues[$row_get_child_question['id']]['question']     = $row_get_child_question['question'];
                  $childQues[$row_get_child_question['id']]['ifrequired']   = $row_get_child_question['ifrequired'];
                  $childQues[$row_get_child_question['id']]['answer_type']  = $row_get_child_question['answer_type'];

                  $childQues[$row_get_child_question['id']]['survey_responses']  = $options;

                  record_set("get_child_questions_answers", "select * from answers where surveyid='" . $surveyid . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_child_question['id']);

                  if (!empty($totalRows_get_child_questions_answers)) {
                    while ($row_get_child_questions_answer = mysqli_fetch_assoc($get_child_questions_answers)) {
                      $childQues[$row_get_child_question['id']]['survey_responses'][$row_get_child_questions_answer['answerid']]  += 1;
                    }
                  }
                }
              }
              foreach ($childQues as $key => $child_question) {
                foreach ($child_question['survey_responses'] as $child_key => $response) {
                  $mergedSurveyResponses[$child_key] += $response > 0 ? $response : 0;
                }
              }

              foreach ($childQues as $key => $child_question) {
                $message .= '<td style="background-color:#f0f0f0;border: 1px solid;">' . $child_question['question'] . '</td>';
                $k = 0;
                foreach ($child_question['survey_responses'] as $child_key => $response) {
                  $sum_of_responses = $mergedSurveyResponses[$child_key];
                  if ($sum_of_responses > 0) {
                    $per_res_score = 100 / $sum_of_responses;
                  } else {
                    $per_res_score = 0;
                  }
                  $score = round($per_res_score * $response, 2);
                  $message .= '<td style="background-color:#f0f0f0;border: 1px solid;">' . $score . '%</td>';
                  $message .= '<td style="background-color:#f0f0f0;border: 1px solid;">' . $response . '</td>';
                }
                
                $message .= '</tr><tr style="border: 1px solid;">';
              }
            $message .= '</tr></tbody></table>';
          }
        }

        if($answer_type==2 || $answer_type==3){
          $message .= '<table width="505px" height="100vh" align="center">
            <tr>
              <td align="center" colspan="3">';
                if($qcount == 1){ $message .='
                  <h4 align="center" style="margin-top:10px;margin-bottom:10px;">'.$value['title'].'</h4>';
                } $message .='
                <h4 style="margin-top:10px;">'.$question['question'].'</h4>
              </td>
            </tr>
          </table>';

          $message .= '<table width="505px" width="505px" align="center" style="page-break-inside:always;font-size:14px;" border="1" cellspacing="0" cellpadding="4">
            <tr style="background-color:#f0f0f0;">
              <th>RESPONDENT</th>
              <th align="center">ANSWERS</th>
            </tr>';
            $sno = 0;
            
            if(!empty(array_filter($answers_array)) ){
              foreach ($answers_array as $key=>$val){ 
                if(isset($val) && !empty($val) && $val != ""){
                  $message .=  '<tr><td>'.++$sno.'</td><td>'.$val.'</td></tr>';
                }
              }
            }else{ 
              $message .=  '<tr><td colspan="2"  align="center">NO ANSWER AVAILABLE</td></tr>';
            }
          $message .= '</table>';
        }
        // Divider in two questions
        $message .= '<table><tr><td style="height:40px;">&nbsp;</td></tr></table>
        </div>';
      } 
    $message .='</div>';
  }

}else{
  $message .= '<div class="container">';
    foreach($questions[0] AS $question){
      $questionid   = $question['id'];
      $answer_type  = $question['answer_type'];
      $totalRows_get_child_questions = 0;
      //1=radio	2=textbox	3=textarea	4=rating
      $questions_array = array();
      $answers_array = array();
      if($answer_type==1 || $answer_type==4 || $answer_type==6){
        //echo 'get_questions_detail';
        record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'");	
        if($totalRows_get_questions_detail>0){
          while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){
            $questions_array[$row_get_questions_detail['id']][] = $row_get_questions_detail['description'];
            $questions_array[$row_get_questions_detail['id']][] = $row_get_questions_detail['answer'];
          }
        }
        record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."'");	
        if($totalRows_get_answers>0){
          while($row_get_answers = mysqli_fetch_assoc($get_answers)){
            $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
          }
        }
        $counts = array_count_values($answers_array);
      }

      if($answer_type==2 || $answer_type==3){
        record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' ");  
        if($totalRows_get_answers>0){
          while($row_get_answers = mysqli_fetch_assoc($get_answers)){
            $answers_array[$row_get_answers['id']] = $row_get_answers['answertext'];
          }
        }
        $counts = array_count_values($answers_array);
      }
      $message .= '<div style="page-break-inside:avoid;">';
      if($answer_type==1 || $answer_type==4 || $answer_type==6){
      
        if($answer_type==1){
          //get Child Questions
          $get_child_questions = "select * from questions where parendit='".$questionid."' and cstatus='1'";
          record_set("get_child_questions", $get_child_questions);
        }
        if(empty($totalRows_get_child_questions)){
            $message .='<table width="505px" align="center" style="page-break-inside: avoid;">
              <tr>
                <td align="center" colspan="3">
                <h4 colspan="2" style="margin-top:10px;text-align:center;">'.$question['question'].'</h4>
                </td>
              </tr>
              <tr>
                <td width="304px;">';
                  $clr_loop=0;
                  $table_display_data = array();
                  foreach($questions_array as $key=>$val){
                    $clr_loop++;
                    $ansId = array_keys($counts)[0];
                    if($key ==$ansId ){
                      $percentage = $questions_array[$key][1] ;
                    }else {
                      $percentage = 0;
                    }
                    // //$percentage = ($total_ans*$total_num)/$total_num;
                    $table_display_data[$val[0]]['percantage']=$percentage;
                    $table_display_data[$val[0]]['count']=$counts[$key];
                  } 
                  
                  $message .= '
                </td>
              </tr>
            </table>
            <table style="font-size:14px;" align="center" width="505px" cellspacing="0" cellpadding="4">
              <tr>
                <th style="background-color:#f0f0f0; border: 1px solid ;border-bottom: none;">ANSWERS</th>
                <th style="background-color:#f0f0f0;width:80px;border: 1px solid ;border-bottom: none;text-align:center;">RESULT</th>
                <th style="background-color:#f0f0f0;width:70px;border: 1px solid;border-bottom: none;text-align:center;">RESPONSES</th>
              </tr>';
              $total = 0;
              
              $sum_of_count = array_sum(array_column($table_display_data, "count"));
              $perResponsePercentage = 100/$sum_of_count;
              if(count(array_filter($table_display_data)) > 0){
                foreach($table_display_data as $key=>$val){ 
                  $response = ($val['count'])?$val['count']:0;
                  $message .='<tr>
                    <td style="border: 1px solid">'.$key.'</td>
                    <td style="text-align:center;border: 1px solid">'.round($perResponsePercentage*$val['count'],2).'%</td>
                    <td style="text-align:center;border: 1px solid">'.$response.'</td>
                  </tr>';
                  $total +=$val['count'];
                } 
                $message .='<tr style="border:none;">
                  <td style="border:none;"></td>
                  <td style="border:none;text-align:center;"><strong>TOTAL</strong></td>
                  <td style="border:none;text-align:center"><strong>'.$total.'</strong></td>
                </tr>';
              } else {
                $message .=  '<tr><td colspan="3" align="center">NO ANSWER AVAILABLE</td></tr>';
              }
            $message .='</table>';
          
        } else{ 
        $message .= '<table width="505px" align="center" style="page-break-inside: avoid;">
          <tr>
            <td align="center" colspan="3">
              <h3 style="margin-top:10px;">'.$question['question'].'</h3>
            </td>
          </tr>
        </table>
        <table width="505px" align="center" style="font-size:14px;border-collapse: collapse;font-family: Arial, Helvetica, sans-serif;border-bottom: 1px solid #000 !important;" border="1" cellspacing="0" cellpadding="4">
            <thead>
            <tr>
            <th  style="background-color:#f0f0f0;" rowspan="2"></th>';

            record_set("get_parent_question_options", "select id, description from questions_detail where surveyid='" . $surveyid . "' and questionid = " . $question['id']);
            $options = [];
            if (!empty($totalRows_get_parent_question_options)) {
              $i = 0;
              while ($row_parent_question_option = mysqli_fetch_assoc($get_parent_question_options)) {
                $options[$row_parent_question_option['id']] = 0;
                $message .= '<th style="background-color:#f0f0f0;" colspan="2">' . $row_parent_question_option['description'] . '</th>';
                $i++;
              }
              $message .= '</tr><tr>';

              for ($j = $i; $j > 0; $j--) {
                $message .= '<th>RESULT</th><th>RESPONSES</th>';
              }
              $message .= '</tr></thead><tbody><tr>';
            }

            $childQues = [];
            $mergedSurveyResponses = [];
            if (!empty($totalRows_get_child_questions)) {
              while ($row_get_child_question = mysqli_fetch_assoc($get_child_questions)) {
                $childQues[$row_get_child_question['id']]['id']           = $row_get_child_question['id'];
                $childQues[$row_get_child_question['id']]['question']     = $row_get_child_question['question'];
                $childQues[$row_get_child_question['id']]['ifrequired']   = $row_get_child_question['ifrequired'];
                $childQues[$row_get_child_question['id']]['answer_type']  = $row_get_child_question['answer_type'];

                $childQues[$row_get_child_question['id']]['survey_responses']  = $options;

                record_set("get_child_questions_answers", "select * from answers where surveyid='" . $surveyid . "' and cstatus='1' $ans_filter_query  and questionid = " . $row_get_child_question['id']);

                if (!empty($totalRows_get_child_questions_answers)) {
                  while ($row_get_child_questions_answer = mysqli_fetch_assoc($get_child_questions_answers)) {
                    $childQues[$row_get_child_question['id']]['survey_responses'][$row_get_child_questions_answer['answerid']]  += 1;
                  }
                }
              }
            }
            foreach ($childQues as $key => $child_question) {
              foreach ($child_question['survey_responses'] as $child_key => $response) {
                $mergedSurveyResponses[$child_key] += $response > 0 ? $response : 0;
              }
            }

            foreach ($childQues as $key => $child_question) {
              $message .= '<td style="background-color:#f0f0f0;border: 1px solid;">' . $child_question['question'] . '</td>';
              $k = 0;
              foreach ($child_question['survey_responses'] as $child_key => $response) {
                $sum_of_responses = $mergedSurveyResponses[$child_key];
                if ($sum_of_responses > 0) {
                  $per_res_score = 100 / $sum_of_responses;
                } else {
                  $per_res_score = 0;
                }
                $score = round($per_res_score * $response, 2);
                $message .= '<td style="background-color:#f0f0f0;border: 1px solid;">' . $score . '%</td>';
                $message .= '<td style="background-color:#f0f0f0;border: 1px solid;">' . $response . '</td>';
              }
              
              $message .= '</tr><tr style="border: 1px solid;">';
            }
          $message .= '</tr></tbody></table>';
        }
      }

      if($answer_type==2 || $answer_type==3){
        $message .= '<table width="505px" align="center" style="page-break-inside: avoid;">
          <tr>
            <td align="center">
              <h4 style="margin-top:10px;">'.$question['question'].'</h4>
            </td>
          </tr>
        </table>';

        $message .= '<table width="505px" width="505px" align="center" style="page-break-inside:always;font-size:14px;" border="1" cellspacing="0" cellpadding="4">
          <tr style="background-color:#f0f0f0;">
            <th>RESPONDENT</th>
            <th align="center">ANSWERS</th>
          </tr>';
          $sno = 0;
          if(!empty(array_filter($answers_array)) ){
            foreach ($answers_array as $key=>$val){ 
              if(isset($val) && !empty($val) && $val != ""){
                $message .=  '<tr><td>'.++$sno.'</td><td>'.$val.'</td></tr>';
              }
            }
          }else{ 
            $message .=  '<tr><td colspan="2" align="center">NO ANSWER AVAILABLE</td></tr>';
          }
        $message .= '</table>';
      }
      // Divider in two questions
      $message .= '<table><tr><td style="height:40px;">&nbsp;</td></tr></table>
      </div>';
    } 
    $message .='</div>';
}


$message .='</body></html>';

// echo $message;die;

// Include auto loader 
require_once 'dompdf/autoload.inc.php'; 
// Reference the Dompdf namespace 
use Dompdf\Dompdf; 
// Instantiate and use the dompdf class 
$dompdf = new Dompdf();
$dompdf->loadHtml($message); 
$dompdf->setPaper('A4', 'PORTRAIT'); 
// Render the HTML as PDF 
$dompdf->render(); 
/*// save pdf in folder
$pdf = $dompdf->output();
$file_location = "upload/trans_docs/".'Quotation'.$_GET['eid'].".pdf";
file_put_contents($file_location,$pdf);*/
// Output the generated PDF to Browser 
$dompdf->stream($surveyName, array("Attachment"=>0));
