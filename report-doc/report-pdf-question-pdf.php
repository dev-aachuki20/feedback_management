<?php 
include('../function/function.php');
$report_id = $_GET['report_id'];
record_set("get_scheduled_report","select * from scheduled_report_templates where id =".$report_id);

$row_get_report= mysqli_fetch_assoc($get_scheduled_report);
$filter = json_decode($row_get_report['filter'],1);

$data_type = $filter['field'];
$surveyid   = $filter['survey_id'];

/* Get Date */
$sdate = $row_get_report['start_date'];
$edate = $row_get_report['end_date'];

$field_value = implode(',',$filter['field_value']);

if(isset($surveyid)){
	record_set("get_survey", "select * from surveys where id IN ($surveyid) and cstatus=1");	
	if($totalRows_get_survey>0){
		$row_get_survey = mysqli_fetch_assoc($get_survey);
	}else{
		echo 'Wrong survey ID.'; exit;
	}
}else{
	echo 'Missing survey ID.';  exit;
}

$ans_filter_query='';
if($data_type=='location' && $field_value !=''){
	$ans_filter_query .= " and locationid IN($field_value)";
}
if($data_type=='department' && $field_value !=''){
	$ans_filter_query .= " and departmentid IN($field_value)";
}
if($data_type=='group' && $field_value !=''){
	$ans_filter_query .= " and groupid IN($field_value)";
}
if(!empty($sdate) and !empty($edate)){
  $ans_filter_query .= " and  cdate between '".date('Y-m-d', strtotime($sdate))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($edate)))."'";
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

$message = '<div align="center">
  <img src="'.getHomeUrl().MAIN_LOGO.'"  width="200"></div>
    <table width="100%" style="font-family: Arial, Helvetica, sans-serif;">
        <thead>
          <tr>
            <td colspan="4" style="text-align:center;border-bottom: 1px solid gray;padding-bottom: 10px;padding-top: 0;"><h3 align="center" style="margin-top:10px;font-family: Arial, Helvetica, sans-serif;">'.$row_get_survey['name'].' </h3></td>
          </tr>';
          if (!empty($sdate) and !empty($edate)) {
            $message .= '<tr>
                <td colspan="4" style="text-align:center;padding-top: 8px;font-family: Arial, Helvetica, sans-serif;"><h4 align="center">' . date('d/m/Y', strtotime($sdate)) . '-' . date('d/m/Y', strtotime($edate)) . ' </h4></td>
            </tr>';
          }
    $message .= '</thead></table>';
  foreach($survey_steps AS $key => $value) { 
      $message .= '<div class="container" style="page-break-after: always;height: 500px;">
        <h4 align="center" style="margin-top:8px;margin-bottom:0;font-family: Arial, Helvetica, sans-serif;">'.$value['title'].'</h4>';
        foreach($questions[$key] AS $question){
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
        
          if($answer_type==1 || $answer_type==4 || $answer_type==6){
          
            if($answer_type==1){
              //get Child Questions
              $get_child_questions = "select * from questions where parendit='".$questionid."' and cstatus='1'";
              record_set("get_child_questions", $get_child_questions);
            }
            if(empty($totalRows_get_child_questions)){
                $message .='<table width="505px" align="center" style="page-break-inside: avoid;font-family: Arial, Helvetica, sans-serif;">
                  <tr>
                    <td align="center" colspan="3" style="padding-bottom: 0 !important;">
                    <h4 colspan="2" style="margin-top:15px;margin-bottom: 0;text-align:center;font-family: Arial, Helvetica, sans-serif;">'.$question['question'].'</h4>
                    </td>
                  </tr>
                  <tr>
                    <td width="304px;" style="padding: 0 !important;">';
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
                  <tr>
                    <td colspan="3">
                      <table style="font-size:14px;font-family: Arial, Helvetica, sans-serif;border-bottom:none !important" width="100%" cellspacing="0" cellpadding="4">
                        <tr>
                          <th style="background-color:#f0f0f0; border: 1px solid ;border-bottom: none;text-align: left;">ANSWERS</th>
                          <th style="background-color:#f0f0f0;width:80px;border: 1px solid ;border-bottom: none;text-align:left;">RESULT</th>
                          <th style="background-color:#f0f0f0;width:70px;border: 1px solid;border-bottom: none;text-align:left;">RESPONSES</th>
                        </tr>';
                        $total = 0;
                        foreach($table_display_data as $key=>$val){ 
                          $message .='<tr>
                            <td style="border: 1px solid">'.$key.'</td>
                            <td style="text-align:center;border: 1px solid">'.round($val['percantage'],2).'%</td>
                            <td style="text-align:center;border: 1px solid">'.$val['count'].'</td>
                          </tr>';
                          $total +=$val['count'];
                        } 
                        $message .='<tr style="border:none;">
                          <td style="border:none;"></td>
                          <td style="border:none;text-align:center;"><strong>TOTAL</strong></td>
                          <td style="border:none;text-align:center"><strong>'.$total.'</strong></td>
                        </tr>';
                      $message .='</table>
                    </td>
                  </tr>
                </table>';
            } else{ 
            $message .= '<table width="505px" align="center" style="font-family: Arial, Helvetica, sans-serif;">
              <tr>
                <td align="center" colspan="3">
                  <h4 style="margin-top:15px;">'.$question['question'].'</h4>
                </td>
              </tr>
            </table>
            <table width="505px" align="center" style="font-size:14px;font-family: Arial, Helvetica, sans-serif;" border="1" cellspacing="0" cellpadding="4">
              <tbody>
                <tr>
                  <td style="background-color:#f0f0f0;">&nbsp;</td>'.
                  $child_answer = array();
                  $tdloop = 0;
                  record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'  ");
                  while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){ 
                    $tdloop++; 
                    $message .= '<td style="background-color:#f0f0f0;">'.
                      $child_answer[$row_get_questions_detail['id']]= $row_get_questions_detail['description'];
                      $row_get_questions_detail['description'];
                    '</td>';
                  }
                $message .= '</tr>';
                while($row_get_child_questions = mysqli_fetch_assoc($get_child_questions)){
                  $message .= '<tr>
                    <td style="background-color:#f0f0f0;">'.$row_get_child_questions['question'].'
                    </td>';

                    $answers_array = array();
                    record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$row_get_child_questions['id']."' ");	
                    if($totalRows_get_answers>0){
                      while($row_get_answers = mysqli_fetch_assoc($get_answers)){
                      //print_r($row_get_answers);
                      $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
                      }
                    }
                    $anscount =  count($answers_array);
                    $counts = array_count_values($answers_array);
                  $message .= '</tr>';
                }
              $message .= '</tbody>
            </table>';
            }
          }

          if($answer_type==2 || $answer_type==3){
            $message .= '<table width="505px" align="center" style="font-family: Arial, Helvetica, sans-serif;">
              <tr>
                <td align="center" style="padding-top: 10px;">
                  <h4 style="margin-top:15px;">'.$question['question'].'</h4>
                </td>
              </tr>
            </table>';

            $message .= '<table width="505px" width="505px" align="center" style="font-size:14px;font-family: Arial, Helvetica, sans-serif;" border="1" cellspacing="0" cellpadding="4">
              <tr style="background-color:#f0f0f0;">
                <th align="left">RESPONDENT</th>
                <th align="left">ANSWERS</th>
              </tr>';
              $sno = 0;
              if(!empty($answers_array)){
                foreach ($answers_array as $key=>$val){ 
                  if(isset($val) && !empty($val) && $val != ""){
                    $message .=  '<tr><td>'.++$sno.'</td><td>'.$val.'</td></tr>';
                  }
                }
              }else{ 
                $message .=  '<tr><td>NO ANSWER AVAILABLE</td></tr>';
              }
            $message .= '</table>';
          }
        } 
      $message .='</div>';
  }
  create_mpdf($message,'Survey Report Question -' . date('Y-m-d-H-i-s') . '.pdf','I');
?>