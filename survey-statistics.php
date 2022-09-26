<?php 
if(isset($_POST['filter'])){
    // $querys = 'SELECT * FROM answers where id!=0 ';
    // $groupBy = '';
    // if(!empty($_POST['survey'])){
    //     $query = " and surveyid =".$_POST['survey'];
    //     $groupBy = 'surveyid';
    // }
    // if(!empty($_POST['location'])){
    //     if($_POST['location'] == 4){
    //         $query = " and locationid in (select id from locations where cstatus=1)";  
    //     }else{
    //         $query = "and locationid = '".$_POST['location']."'";
    //     }
    //     $groupBy = 'locationid';
    // }
    // if(!empty($_POST['group'])){
        
    //     if($_POST['group'] == 4){
    //         $query = " and groupid in (select id from groups where cstatus=1)";  
    //     }else{
    //         $query = " and groupid = '".$_POST['group']."'";
    //     }
    //     $groupBy = 'group';
    // }
    // if(!empty($_POST['department'])){
    //     if($_POST['department'] == 4){
    //         $all_departments = getDepartment();
    //         $query = " and departmentid in (select id from departments where cstatus=1)";
    //     }else{
    //         $query = " and departmentid = '".$_POST['department']."' ";
    //     }
    //     $groupBy = 'departmentid';
    // }

    // if(!empty($_POST['fdate']) and !empty($_POST['fdate'])){
    //     $query .= " and  cdate between '".date('Y-m-d', strtotime($_POST['fdate']))."' and '".date('Y-m-d', strtotime("+1 day",strtotime($_POST['sdate'])))."'";
    // }
  
    // record_set("total_survey","SELECT COUNT(DISTINCT(cby)) as totalCount FROM answers WHERE id!=0  $query");
    // $row_total_survey = mysqli_fetch_assoc($total_survey);
    // $total_survey = $row_total_survey['totalCount'];
    // record_set("get_entry",$querys.$query." GROUP by cby");
    // if($totalRows_get_entry){
    //     $survey_data = array();
    //     while($row_get_entry = mysqli_fetch_assoc($get_entry)){
    //         $locId      = $row_get_entry['locationid'];
    //         $depId      = $row_get_entry['departmentid'];
    //         $grpId      = $row_get_entry['groupid'];
    //         $surveyid   = $row_get_entry['surveyid'];
    //         $cby        = $row_get_entry['cby'];
            
    //         // for survey
    //         if(!empty($_POST['survey'])){
    //             $count = array();
    //             record_set("get_question","select * from answers where surveyid=$surveyid and cby=$cby");
    //             $total_answer = 0;
    //             while($row_get_question= mysqli_fetch_assoc($get_question)){
    //                 $total_answer += $row_get_question['answerval'];
    //             }
    //             $average_value = ($total_answer/($totalRows_get_question*100))*100;
    //             $survey_data[$surveyid][$cby] = $average_value;
    //         }
    //         if(!empty($_POST['location'])){
    //             $count = array();
    //             record_set("get_question","select * from answers where locationid=$locId and cby=$cby");
    //             $total_answer = 0;
    //             while($row_get_question= mysqli_fetch_assoc($get_question)){
    //                 $total_answer += $row_get_question['answerval'];
    //             }
    //             $average_value = ($total_answer/($totalRows_get_question*100))*100;
    //             $survey_data[$locId][$cby] = $average_value;
    //         }
    //         if(!empty($_POST['department'])){
    //             $count = array();
    //             record_set("get_question","select * from answers where departmentid=$depId and cby=$cby");
    //             $total_answer = 0;
    //             while($row_get_question= mysqli_fetch_assoc($get_question)){
    //                 $total_answer += $row_get_question['answerval'];
    //             }
    //             $average_value = ($total_answer/($totalRows_get_question*100))*100;
    //             $survey_data[$depId][$cby] = $average_value;
    //         }
    //         if(!empty($_POST['group'])){
    //             $count = array();
    //             record_set("get_question","select * from answers where groupid=$grpId and cby=$cby");
    //             $total_answer = 0;
    //             while($row_get_question= mysqli_fetch_assoc($get_question)){
    //                 $total_answer += $row_get_question['answerval'];
    //             }
    //             $average_value = ($total_answer/($totalRows_get_question*100))*100;
    //             $survey_data[$grpId][$cby] = $average_value;
    //         }
    //     }
    // }
}
?>
<style>
    .btn-outline-secondary {
        color: #6c757d;
        background-color: transparent;
        background-image: none;
        border-color: #6c757d;
        width: 100%;
    }
    .btn:focus {
        outline: none !important;
    }
/* .col-md-3.graph-body {
    background: #e7e7e7a8;
    border: 1px solid #c8bfbf;
    width: 24%;
    margin: 2px;
    height: 270px;
} */
.col-md-3.graph-body {
    background: #ecf0f5;
    border: 1px solid #c8bfbf;
    width: 20%;
    margin: 25px;
    height: 270px;
}
.graph-btn.active {
    background: #a020f0;
    color: #fff;
}
</style>
<section class="content-header">
  <h1>Statistics</h1>
</section>
<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary graph-btn active" data-type="survey">Survey</button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary graph-btn" data-type="group">Group</button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary graph-btn" data-type="location">Location</button>
                </div>
                <div class="col-md-3">
                    <button type="button" data-type="department" class="btn btn-outline-secondary graph-btn" >Department</button>
                </div>
            </div>
            <div class="row filter_form">
            
                    <!-- <input type="hidden" name="post_values" value =<?=json_encode($_POST)?> > -->
                    <div class="box-header">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        <h3 class="box-title"> Search</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <!-- <form action="" method="POST" id="viewReportcsv"> -->
                                <div class="col-md-3">
                                    <input type="hidden" name="survey_type" class="survey_type" value="">
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="date" name="fdate" class="form-control start_data" value="<?php //echo date('Y-m-d', strtotime('-1 months')); ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>End Date</label>
                                        <input type="date" name="sdate" class="form-control end_date" value="<?php //echo date('Y-m-d'); ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-3 ajaxData" style="display: none;">
                                <span>This Field is required</span>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <input type="button" style="background-color: #00a65a !important;border-color: #008d4c;"name="filter" class="btn btn-success btn-block search" value="Search"/>
                                    </div>
                                </div>
                            <!-- </form>     -->
                        </div>
                        <hr style="border: 2px solid #6c757d36;">
                        <div class="row">
                            <div class="gauge-wrapper">
                                <div class="row">
                                    <!-- loader div start -->
                                    <div class="loader col-md-12" style="text-align: center; display:none;">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="150px" height="150px" viewBox="0 0 150 150" enable-background="new 0 0 150 150" xml:space="preserve">

                                        <g id="Layer_1">
                                            
                                                <circle opacity="0.4" fill="#FFFFFF" stroke="#1C75BC" stroke-width="2" stroke-linecap="square" stroke-linejoin="bevel" stroke-miterlimit="10" cx="75" cy="75.293" r="48.707"></circle>
                                        </g>
                                        <g id="Layer_2">
                                            <g>
                                                <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="36.2957" y1="34.8138" x2="94.5114" y2="34.8138">
                                                    <stop offset="0" style="stop-color:#2484C6"></stop>
                                                    <stop offset="1" style="stop-color:#2484C6;stop-opacity:0"></stop>
                                                </linearGradient>
                                                <path fill="none" stroke="url(#SVGID_1_)" stroke-width="4" stroke-linecap="round" stroke-linejoin="bevel" d="M38.296,43.227
                                                    c0,0,21.86-26.035,54.216-13.336">
                                                    <animateTransform attributeName="transform" attributeType="XML" type="rotate" from="0 75 75" to="-360 75 75" dur=".8s" repeatCount="indefinite"></animateTransform>
                                                </path>
                                            </g>
                                        </g>
                                        </svg>
                                    </div>
                                   <!-- loader div end  -->
                                    <div class="col-md-12 renderChart" style="margin-left: 12px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div3
                        <button type="button" class="btn btn-success" id="exportascsv" style="margin-bottom: 20px;">Export CSV</button>
                    </div> -->
                
            </div>
        </div>
    </div>
</section>

<?php 
// echo '<pre>';
// print_r($survey_data);
// echo '<hr/>';
// $i=1; foreach($survey_data as $key => $value){ 
//     // echo $total=  array_sum($value)/count($value);die();
//    // foreach($value as $val){
//         print_r($value);
//    // }
    

//  } 
//  echo '</pre>';
//  die();
 ?>
<!-- Resources -->
<script src="https://unpkg.com/chart.js@2.8.0/dist/Chart.bundle.js"></script>
<script src="https://unpkg.com/chartjs-gauge@0.2.0/dist/chartjs-gauge.js"></script>
<script>
    //load graph on page load
    ajax_to_load_graph(fdate='',sdate='',survey='',survey_type='');

    $(document).on('click','.graph-btn',function(){
        let type = $(this).data('type');
        $('.survey_type').val(type);
        $('.graph-btn').removeClass('active');
        $(this).addClass('active');
        ajx_report_type(type);
    })

    $(document).on('click','.search',function(){
        let fdate       = $('.start_data').val();
        let sdate       = $('.end_date').val();
        let survey      = $('.survey').val();
        // let group       = $('.group').val();
        // let location    = $('.location').val();
        // let department  = $('.department').val();
        if(survey==''){
            $('.error').show();
            return;
        }
        let survey_type = $('.survey_type').val();
        ajax_to_load_graph(fdate,sdate,survey,survey_type);
    })


    //ajax to load graph data
     function ajax_to_load_graph(fdate,sdate,survey,survey_type){
        $('.loader').show();
        $('.renderChart').hide();
        $.ajax({
            method:"POST",
            url:'<?=baseUrl()?>ajax/ajaxOn_survey_statistics.php',
            data:{
                mode:'survey_statics',
                fdate:fdate,
                sdate:sdate,
                survey:survey,
                survey_type:survey_type,
                // group:group,
                // location:location,
                // department:department,
            },
            success:function(response){
                response = JSON.parse(response);
                $('.renderChart').html(response.html);
                $('.loader').hide();
                $('.renderChart').show();
                //console.log(response.result);
                let results = response.result;
                console.log(results);
                let classid = 1;
                $.each(results, function( k, v ) {
                    let value_result = results[k];
                    let i=0;
                    let sum = 0;
                        $.each(value_result, function( a, b ) {
                            i++;
                            sum = sum+b;
                            //console.log(i+'->'+a+':'+b+'='+ sum);
                        });
                        //calculate avg score
                        let total_value = sum/i;
                        color ='';
                        //set color as per value
                        // if(total_value > 69){
                        //     color = '#00a65a';
                        // }
                        // else if(total_value>35 && total_value<70){
                        //     color = '#f1c40f';
                        // }else {
                        //     color = '#ff0000';
                        // }
                        if(total_value > 80){
                            color = '#00B71D';
                        }else if(total_value > 60 && total_value < 80.01){
                            color = '#99B81D';
                        }
                        else if(total_value > 40 && total_value < 60.01){
                            color = '#FFEB00';
                        }
                        else if(total_value > 20 && total_value < 40.01){
                            color = '#FF9000';
                        }else {
                            color = '#FF4433';
                        }
                        //set class
                        clas = 'chart_'+classid;
                        console.log(clas+' : '+total_value);
                        classid++;
                    // calll chart function   
                    mychart(total_value,clas,color);
                });
            }
        })
     }
    //ajax to load button
    function ajx_report_type(type){
        $(this).addClass('active');
        $.ajax({
            method:"POST",
            url:'<?=baseUrl()?>ajax/common_file.php',
            data:{
                type:type,
                mode:'survey_statics'
            },
            success:function(response){
                 response = JSON.parse(response);
                 $('.ajaxData').show();
                 $('.ajaxData').html(response);
            }
        })
    }

/**
 * chart js start
 */
function mychart(val,classes,color){
    //console.log(val+' : '+classes+':'+color);
    var ctx = document.getElementById(classes).getContext("2d");
    var  values = 0.01 * val;
    var chart = new Chart(ctx, {
        type: 'gauge',
        data: {
            datasets: [{
                value: [values],
                //data: [0.35,0.69,1],
                data: [values+0.005,1],
               // backgroundColor: [color],
                backgroundColor: [color,'#d1d1d1'],
                //borderColor:[color],
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
                widthPercentage: 3,
                lengthPercentage: 100,
                color: '#1c1c1c',
                borderColor:'#1c1c1c',
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
            }
        }
    });
}

/* chart js end */
</script>

