<?php
require_once __DIR__ . '/vendor/autoload.php';
include('./function/function.php');
include('./function/get_data_function.php');
$mpdf = new \Mpdf\Mpdf();

$data =array();
$querys = 'SELECT * FROM answers where id!=0 ';
$groupBy = '';

if($_POST['data_type']=='location'){
    $query = " and surveyid =".$_POST['survey']." and locationid in (select id from locations where cstatus=1)";  
    $groupBy = 'locationid';
}
else if($_POST['data_type']=='group'){
    $query = " and surveyid =".$_POST['survey']." and groupid in (select id from groups where cstatus=1)";  
    $groupBy = 'group';
}
else if($_POST['data_type']=='department'){
    $query = " and surveyid =".$_POST['survey']." and departmentid in (select id from departments where cstatus=1)";
    $groupBy = 'departmentid';
}
else {
    $survey_allow = get_allowed_survey($_POST['survey_type']);
    $survey_allow_id = implode(',',array_keys($survey_allow));
    $filterdata = '';
    if($survey_allow_id){
        $filterdata = " and id IN($survey_allow_id)";
    }
    $query = " and surveyid IN (select id from surveys where cstatus=1 $filterdata)";
    $groupBy = 'surveyid';
}

if(!empty($_POST['fdate']) and !empty($_POST['fdate'])){
    $query .= " and  cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
}
record_set("total_survey","SELECT COUNT(DISTINCT(cby)) as totalCount FROM answers WHERE id!=0  $query");
$row_total_survey = mysqli_fetch_assoc($total_survey);
$total_survey = $row_total_survey['totalCount'];
record_set("get_entry",$querys.$query." GROUP by cby");
if($totalRows_get_entry){
    $survey_data = array();
    while($row_get_entry = mysqli_fetch_assoc($get_entry)){
        $locId      = $row_get_entry['locationid'];
        $depId      = $row_get_entry['departmentid'];
        $grpId      = $row_get_entry['groupid'];
        $surveyid   = $row_get_entry['surveyid'];
        $cby        = $row_get_entry['cby'];
        
        if($_POST['data_type']=='location'){
            $count = array();
            record_set("get_question","select * from answers where locationid=$locId and cby=$cby");
            $total_answer = 0;
            $i=0;
            $total_result_val = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where id =".$row_get_question['questionid']);
                if($result_question){
                    if(!in_array($result_question['answer_type'],array(2,3,5))){
                       $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
            }
            $average_value = ($total_answer/($i*100))*100;
            if($total_answer==0 and $total_result_val==0){
                $average_value=100;
            }
            $survey_data[$locId][$cby] = $average_value;
        }
        else if($_POST['data_type']=='department'){
            $count = array();
            record_set("get_question","select * from answers where departmentid=$depId and cby=$cby");
            $total_answer = 0;
            $i=0;
            $total_result_val = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where id =".$row_get_question['questionid']);
                if($result_question){
                    if(!in_array($result_question['answer_type'],array(2,3,5))){
                       $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
            }
            $average_value = ($total_answer/($i*100))*100;
            if($total_answer==0 and $total_result_val==0){
                $average_value=100;
            }
            $survey_data[$depId][$cby] = $average_value;
        }
        else if($_POST['data_type']=='group'){
            $count = array();
            record_set("get_question","select * from answers where groupid=$grpId and cby=$cby");
            $total_answer = 0;
            $i=0;
            $total_result_val = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $result_question =  record_set_single("get_question_type", "SELECT answer_type FROM questions where id =".$row_get_question['questionid']);
                if($result_question){
                    if(!in_array($result_question['answer_type'],array(2,3,5))){
                       $i++;
                        $total_answer += $row_get_question['answerval'];
                    }
                }
            }
            $average_value = ($total_answer/($i*100))*100;
            if($total_answer==0 and $total_result_val==0){
                $average_value=100;
            }
            $survey_data[$grpId][$cby] = $average_value;
        }
        else {
            $count = array();
            record_set("get_question","select * from answers where surveyid=$surveyid and cby=$cby");
            $total_answer = 0;
            while($row_get_question= mysqli_fetch_assoc($get_question)){
                $total_answer += $row_get_question['answerval'];
            }
            $average_value = ($total_answer/($totalRows_get_question*100))*100;
            $survey_data[$surveyid][$cby] = $average_value;
        }
    }
}
$html ='';
$i=1;

ksort($survey_data);

$html = '<div class="gauge-wrapper">
    <div class="pdf-div">
        <div class="col-sm-12">
        <div class="pdf-head" style="display: none;">
            <div style="height: 30px;"></div>
            <table width="93%" style="margin:0 auto;">
                <tr>
                <td colspan="3" style="text-align:center;margin-top:100px;"> <img src="'.baseUrl().'hats-logo-survey50.png" width="200"></td>
                </tr>
                <tr >
                    <td colspan="3" style="text-align:center;height: 30px;"></td>
                </tr>
                <tr class="borderClass filterSurveyType">
                    <td colspan="3" style="text-align:center;font-size:18px;"> </td>
                </tr>
                <tr class="borderClass filterSurvey">
                    <td colspan="3" style="text-align:center;font-size:20px;"><strong> Survey Statics</strong></td>
                </tr>
                <tr class="borderClass filterDate">
                    <td  style="text-align:center;">Start Date: <span class="pdf-sdate"></span></td>
                    <td  style="text-align:center;"> </td>
                    <td  style="text-align:center;">End Date:<span class="pdf-edate"></span></td>
                </tr>
                <tr class="borderClass">
                    <td colspan="3" style="text-align:center;"> </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>
        </div>
        <div class="row" style="width:90%; margin:0 auto;">
            <div class="row renderChart"  id="block2" >
                <div class="col-md-3"> 
                    <div class="graph-body">  
                            <p style="font-size: 14px;font-weight: 700;text-align:center;height: 40px;"></p>  
                    <p style="font-size: 14px;font-weight: 700;text-align:center">Survey Name</p>     
                        <div id="canvas-holder">
                            <span class="g-persent"><strong>56%</strong></span>
                            <canvas id="chart_1"></canvas>
                            <div class="row" style="text-align:center;margin-top: -24px;">
                                <div class="col-md-4"><span class="poor" style="font-size: 12px;margin-left: 10px;"><strong>POOR</strong></span></div>
                                <div class="col-md-4"></div>
                                <div class="col-md-4"><span class="poor" style="font-size: 12px;margin-right: 10px;"><strong>GOOD</strong></span></div>
                            </div>
                            <div class="row" style="text-align:center;">
                                <div class="col-md-12"><span class="total-count"><strong>TOTAL:12</strong></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3"> 
                    <div class="graph-body">  
                            <p style="font-size: 14px;font-weight: 700;text-align:center;height: 40px;"></p>  
                    <p style="font-size: 14px;font-weight: 700;text-align:center">Survey Name</p>     
                        <div id="canvas-holder">
                            <span class="g-persent"><strong>86%</strong></span>
                            <canvas id="chart_2"></canvas>
                            <div class="row" style="text-align:center;margin-top: -24px;">
                                <div class="col-md-4"><span class="poor" style="font-size: 12px;margin-left: 10px;"><strong>POOR</strong></span></div>
                                <div class="col-md-4"></div>
                                <div class="col-md-4"><span class="poor" style="font-size: 12px;margin-right: 10px;"><strong>GOOD</strong></span></div>
                            </div>
                            <div class="row" style="text-align:center;">
                                <div class="col-md-12"><span class="total-count"><strong>TOTAL:52</strong></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

$mpdf->WriteHTML($html);
//$pdf = $mpdf->Output('', 'S');
$mpdf->Output();
?>
<script src="https://unpkg.com/chart.js@2.8.0/dist/Chart.bundle.js"></script>
<script src="https://unpkg.com/chartjs-gauge@0.2.0/dist/chartjs-gauge.js"></script>
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-html2canvas@latest/dist/jspdf-html2canvas.min.js"></script> 

<script>
    //console.log(val+' : '+classes+':'+color);
    var ctx = document.getElementById('chart_1').getContext("2d");
    var  values = 0.01 * 20;
    var chart = new Chart(ctx, {
        type: 'gauge',
        data: {
            datasets: [{
                value: ['24'],
                data: [0.2,0.4,0.6,0.8,1],
                backgroundColor: ['#FF4433','#FF9000','#FFEB00','#99B81D','#00B71D'],
            }]
        },
        options: {
            responsive: true,
            layout: {
                padding: {
                bottom: 5
                }
            },
            needle: {
                radiusPercentage: 2,
                widthPercentage: 6,
                lengthPercentage: 100,
                color: '#808080',
                borderColor:'#808080',
            },
            valueLabel: {
                display: false,
                formatter: (value) => {
                    return Math.round(value)+'%';
                },
                color: 'rgba(255, 255, 255, 1)',
                backgroundColor: 'rgba(0, 0, 0, 1)',
                borderRadius: 5,
                padding: {
                top: 10,
                bottom: 10
                }
            },
        }
    });

    var ctx = document.getElementById('chart_2').getContext("2d");
    var  values = 0.01 * 20;
    var chart = new Chart(ctx, {
        type: 'gauge',
        data: {
            datasets: [{
                value: ['24'],
                data: [0.2,0.4,0.6,0.8,1],
                backgroundColor: ['#FF4433','#FF9000','#FFEB00','#99B81D','#00B71D'],
            }]
        },
        options: {
            responsive: true,
            layout: {
                padding: {
                bottom: 5
                }
            },
            needle: {
                radiusPercentage: 2,
                widthPercentage: 6,
                lengthPercentage: 100,
                color: '#808080',
                borderColor:'#808080',
            },
            valueLabel: {
                display: false,
                formatter: (value) => {
                    return Math.round(value)+'%';
                },
                color: 'rgba(255, 255, 255, 1)',
                backgroundColor: 'rgba(0, 0, 0, 1)',
                borderRadius: 5,
                padding: {
                top: 10,
                bottom: 10
                }
            },
        }
    });
    const chartImageUrl = chart.getUrl();
    console.log(chartImageUrl);
</script>

