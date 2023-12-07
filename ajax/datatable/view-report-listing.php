<?php 
include('../../function/function.php');
include('../../function/get_data_function.php');
$requestData= $_REQUEST;
$columns = array( 
    0 =>'cdate', 
    1 => 'name',
    2 => 'cdate',
    3 => 'cdate',
    4 => 'cdate', 
);
$loggedIn_user_id    = $_SESSION['user_id'];
$loggedIn_user_type  = $_SESSION['user_type'];

if(!empty($_POST['surveys'])){
    $dateflag= false;
    $query = 'SELECT * FROM answers where id !=0 ';
    if(!empty($_POST['fdate']) && !empty($_POST['sdate'])){  
        $query .= " and cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
    }

    if(!empty($_POST['departmentid'])){
        $query .= " and departmentid = '".$_POST['departmentid']."'";
    }

    if(!empty($_POST['roleid'])){
        $query .= " and roleid = '".$_POST['roleid']."'";
    }

    if(!empty($_POST['locationid'])){
        $query .= "and locationid = '".$_POST['locationid']."'";
    }
    if(!empty($_POST['surveys'])){
        $query .= " and surveyid =".$_POST['surveys'];
    }
    if(!empty($_POST['groupid'])){
        $query .= " and groupid = '".$_POST['groupid']."'";
    }
    if(!empty($requestData['contact'])){
        $que= " and  answerid != -2 and answerval != 100";
    }
    $query .= " GROUP by cby";
    record_set("get_departments", "SELECT * FROM departments");	
    $departments = array();
    while($row_get_departments = mysqli_fetch_assoc($get_departments)){
        $departments[$row_get_departments['id']] = $row_get_departments['name'];
    }
    record_set("get_all_data",$query);
    if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
        $query.=" AND ( DATE(cdate) LIKE '".$requestData['search']['value']."%' ";    
        $query.="  )";
    }
    $query.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
    
    //$query.=" ORDER BY cdate DESC   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
    record_set("get_recent_entry",$query);
    $data =array(); 
    if($totalRows_get_recent_entry >0){
        $i=0;
        while($row_get_recent_entry = mysqli_fetch_assoc($get_recent_entry)){
            $i++;
            $nestedData=array();
            record_set("get_survey_detail", "SELECT * FROM surveys where id='".$row_get_recent_entry['surveyid']."'");	
            $row_get_survey_detail = mysqli_fetch_assoc($get_survey_detail);
            $row_survey_entry = 1;
            record_set("survey_entry", "SELECT DISTINCT cby FROM answers where surveyid='".$row_get_survey_detail['id']."' and cby <".$row_get_recent_entry['cby']);
            
            $row_survey_entry = $totalRows_survey_entry+$row_survey_entry;
            // $nestedData[] = '<input type="checkbox" name="assign" value="'.$row_get_recent_entry['cby'].'" class="assignSurveyCheckbox" task-type="" data-sid="'.$row_get_recent_entry['surveyid'].'">';
            $nestedData[] = date("d-m-Y", strtotime($row_get_recent_entry['cdate']));
            $nestedData[] = $row_get_survey_detail['name'];
            //$nestedData[] = ordinal($row_survey_entry);
            $nestedData[] = $row_survey_entry;
            
            $total_result_val=0;
            record_set("get_survey_result", "SELECT answerid,answerval,questionid,answertext FROM answers where surveyid='".$row_get_recent_entry['surveyid']."' and cby='".$row_get_recent_entry['cby']."'");

            $achieved_result_val = 0;
            $to_bo_contacted     = 0;
            $i=0;
            while($row_get_survey_result = mysqli_fetch_assoc($get_survey_result)){
            $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where is_weighted=1 and id =".$row_get_survey_result['questionid']);
                if($result_question){
                    if(!in_array($result_question['answer_type'],array(2,3,5))){
                        $total_result_val = ($i+1)*100;
                        $achieved_result_val += $row_get_survey_result['answerval'];
                        $i++;
                    }
                }
                if($row_get_survey_result['answerid'] == -2 && $row_get_survey_result['answerval'] == 100){
                    $to_bo_contacted = 1;
                }
            }
            $result_response = $achieved_result_val*100/$total_result_val;
            if($achieved_result_val==0 and $total_result_val==0){
                $result_response=100;
            }
            // for filter using contact
            if($requestData['contact']!=3){
                if($to_bo_contacted == 1 && $requestData['contact']!=1){
                    continue;
                }
                if($to_bo_contacted == 0 && $requestData['contact']==1){
                    continue;
                }
            }

            $label_class = 'success';
            if($result_response<50){
                $label_class = 'danger';
            }else 
            if($result_response<75){
                $label_class = 'info';
            }
            
            $nestedData[] = '<label class="label label-'.$label_class.'">'.round($result_response,2).'%</label>';
            if($to_bo_contacted==1){ 
                $nestedData[] ='<a class="btn btn-xs bg-green">Yes</a>';
            }else{ 
                $nestedData[] ='<a class="btn btn-xs btn-danger">No</a>';
            } 
            $nestedData[] =' <a class="btn btn-xs btn-primary" href="survey-result.php?surveyid='.$row_get_recent_entry['surveyid'].'&userid='.$row_get_recent_entry['cby'].'" target="_blank">VIEW DETAILS</a>';

            $data[] = $nestedData;
        }
    }
}	
$json_data = array(
    "draw"            => intval( $requestData['draw'] ),
    "recordsTotal"    => intval( $totalRows_get_all_data ), 
    "recordsFiltered" => intval( $totalRows_get_all_data ), 
    "data"            => $data  
);
echo json_encode($json_data);  die();
