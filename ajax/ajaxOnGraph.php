<?php
  include('../function/function.php');
    if($_POST['mode']=='latest_survey'){
        $interval = $_POST['interval_id'];
        $days30 = array();
        
        if($interval==2){
            // weekly interval
        $intervalType = 'weeks';
        $limit        = 12 ;  
        }else if($interval==3){
            // monthly interval
            $intervalType = 'months';
            $limit        = 12 ;  
        }else if($interval==4){
            // years interval
            $intervalType = 'years';
            $limit        = 5 ;  
        }else {
            // years interval
            $intervalType = 'days';
            $limit        = 29 ;  
        }
        for($i = 0; $i < $limit; $i++) {
            $days30item = date("d M Y", strtotime('-'. $i . $intervalType));
            $days30[]   = date("d M Y", strtotime('-'. $i .$intervalType));
        }

        record_set("GetDetails", "select id,name from surveys  $filterSurvey ");
        while($row_GetDetails = mysqli_fetch_assoc($GetDetails)){ 
            $clients_array[$row_GetDetails['id']] = $row_GetDetails['name'];
            $ykeys .= "'item".$row_GetDetails['id']."', ";
            $labels .= "'".$row_GetDetails['name']."', ";
        }
        
        $final_chart_array = array();
        $i=0;
        foreach($days30 as $key=> $value){
            $days = $value;
            //get prev and current value
            if ($key != 0) {
                $firstInterval  = $days30[$key - 1];
                $secondInterval =  $days;
            }else {
                $secondInterval =  $days;
            }
            $arra_txt = "";
            $arra_txt .= "{y: '".date("Y-m-d", strtotime($days))."', ";
            foreach($clients_array as $clientkey =>$client){
                if($interval==1){
                    $filter = "and cdate like '".date("Y-m-d", strtotime($days))."%'";
                }else if($interval==2){
                    //interval by week
                    $filter = " and cdate BETWEEN '".date("Y-m-d", strtotime($secondInterval))."' AND '".date("Y-m-d", strtotime($firstInterval))."'";
                }else if($interval==3){
                    //interval by monthly
                    $filter = " and MONTH(cdate) = '".date("m", strtotime($secondInterval))."' ";
                }else if($interval==4){
                    //interval by yearly
                    $filter = " and YEAR(cdate) = '".date("Y", strtotime($secondInterval))."' ";
                }else {
                    $filter = " and cdate like '".date("Y-m-d", strtotime($days))."%'";
                }
                record_set("Getcollectedamnt", "SELECT DISTINCT cby FROM answers where surveyid='".$clientkey."' $filter $locationQueryAndCondition");
                $row_survey_entry = $totalRows_Getcollectedamnt;
                $tamount = 0;
                if(!empty($row_survey_entry)){
                    $tamount = $row_survey_entry;
                }
               // $arra_txt .= "item".$clientkey.": ".$tamount.", ";
            }
            $test = "a : ".$tamount;
            $arra_txt .=  $test;
            $arra_txt .= "},";
           
            $final_chart_array[$days]=$arra_txt;
            $final_chart_array[$i]=$arra_txt;
            $i++;
        }
        
        $final_chart_array_item = implode(" ",$final_chart_array);
        $data =  "[".$final_chart_array_item."]";
       
        $mydata = array();
        $mydata['data']     = $data;       
        $mydata['yKeys']    = $ykeys;       
        $mydata['labels']   = $labels;   
        echo json_encode($mydata); die();
    }
?>