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
.graph-body {
    background: #ecf0f5;
    border: 1px solid #c8bfbf;
    margin:30px 0 0px ;
    /* height: 270px;
    width: 27% !important; */
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
                    <div class="center-block">
                    <div class="row">
                        <!-- <form action="" method="POST" id="viewReportcsv"> -->
                            <div class="col-md-3">
                                <input type="hidden" name="data_type" class="data_type" value="">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="fdate" class="form-control start_data" min ="2000-01-01" max="<?= date('Y-m-d'); ?>" value="<?php //echo date('Y-m-d', strtotime('-1 months')); ?>"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="sdate" min ="2000-01-01" max="<?= date('Y-m-d'); ?>" class="form-control end_date" value="<?php //echo date('Y-m-d'); ?>"/>
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
                    <div class="row" style="text-align: center;">
                        <div class="col-md-3">
                            <button type="button" class="btn btn-success btn-big btn-green large-btn">Schedule</button>
                        </div>
                        <div class="col-md-6">
                        <form action="" id="document_form" method="post">
                            <input type="hidden" name="survey" id="survey_id" value="">
                            <input type="hidden" name="sdate"  id="start_date" value="">
                            <input type="hidden" name="edate"  id="end_date" value="">
                            <input type="hidden" name="data_type" id="survey_data_type" value="">
                            <input type="hidden" name="survey_type" value="<?=$_GET['type']?>">
                            <div class="row">
                            <div class="col-md-6">
                                <button type="button" id="view-pdf" class="btn btn-big btn-primary large-btn" data-type="pdf">View Pdf</button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" id="download-csv" class="btn btn-big btn-primary large-btn" data-type="csv">Download CSV</button>
                            </div>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <hr style="border: 2px solid #6c757d36;">
                    <div class="row">
                        <div class="gauge-wrapper">
                            <div class="pdf-div">
                                <div class="col-sm-12">
                                    <div class="pdf-head" style="display: none;">
                                        <div style="height: 30px;"></div>
                                        <table width="93%" style="margin:0 auto;">
                                            <tr>
                                            <td colspan="3" style="text-align:center;margin-top:100px;"> <img src="<?=baseUrl()?>hats-logo-survey50.png" width="200"></td>
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
                                    <div class="row renderChart"  id="block2" style="">
                                    </div>
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
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-html2canvas@latest/dist/jspdf-html2canvas.min.js"></script> 

<script>
    //load graph on page load
    ajax_to_load_graph(fdate='',sdate='',survey='',data_type='',survey_type='<?=$_GET['type'] ?>');

    $(document).on('click','.graph-btn',function(){
        let type = $(this).data('type');
        $('.data_type').val(type);
        $('.graph-btn').removeClass('active');
        $(this).addClass('active');
        ajx_report_type(type);
    })

    $(document).on('click','.search',function(){
        let fdate       = $('.start_data').val();
        let sdate       = $('.end_date').val();
        let survey      = $('.survey').val();
        if(survey==''){
            $('.error').show();
            return;
        }
        let data_type = $('.data_type').val();
        let survey_type = '<?=$_GET['type']?>';
        ajax_to_load_graph(fdate,sdate,survey,data_type,survey_type);
    })


    //ajax to load graph data
     function ajax_to_load_graph(fdate,sdate,survey,data_type,survey_type){
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
                data_type:data_type,
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
                        });
                        //calculate avg score
                        let total_value = sum/i;
                        color ='';
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
        let survey_type = '<?=$_GET['type']?>';
        $.ajax({
            method:"POST",
            url:'<?=baseUrl()?>ajax/common_file.php',
            data:{
                type:type,
                survey_type:survey_type,
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
    var ctx = document.getElementById(classes).getContext("2d");
    var  values = 0.01 * val;
    var chart = new Chart(ctx, {
        type: 'gauge',
        data: {
            datasets: [{
                value: [values],
                data: [0.2,0.4,0.6,0.8,1],
                backgroundColor: ['#FF4433','#FF9000','#FFEB00','#99B81D','#00B71D'],
                // data: [values+0.005,1],
                //backgroundColor: [color,'##FF4433'],
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
            // animation: {
            //     onComplete: function() {
            //         console.log(chart.toBase64Image());
            //     }
            // }
        }
    });

}

/* chart js end */

/*-----download csv and pdf----*/

    // start export pdf 
    var pages;
    $('.large-btn').click(function(){
        pages = document.getElementsByClassName('gauge-wrapper');
        let sdate           = $('.start_data').val();
        let edate           = $('.end_date').val();
        let data_type       = $('.data_type').val();
        let survey          = $('.survey').val();
        let document_type   = $(this).data('type');

        //put value in form
        $('#survey_id').val(survey);
        $('#start_date').val(edate);
        $('#end_date').val(edate);
        $('#survey_data_type').val(data_type);
    
        if(document_type == 'pdf'){
            export_pdf(sdate,edate,data_type,survey);
        }else if(document_type == 'csv'){
            $('#document_form').attr('action','./ajax/ajaxOn_survey_statistics.php?export=csv&data_type='+data_type);
            $('#document_form').submit();
        }
    });

    function export_pdf(sdate,edate,data_type ='',survey){
        // get all surveys 
        const survey_arr = <?php echo json_encode(getSurvey()); ?>;
        // get width of div 
        let box = document.querySelector('.pdf-div');
        let width = box.offsetWidth;
        let height = box.offsetHeight;
        if(sdate !='' && edate !=''){
            $('.filterDate').show();
            $('.pdf-sdate').html(sdate);
            $('.pdf-edate').html(edate);
        }else {
            $('.filterDate').hide();
        }
        if(data_type !='' ){
            $('.filterSurveyType').show();
            let heading = data_type+' Statistics';
            $('.filterSurveyType>td').html('<strong>'+heading.toUpperCase()+'</strong>');
            if(survey){
                 $('.filterSurvey>td').html('<strong>'  +survey_arr[survey].toUpperCase()+'</strong>');
            }
        }else {
            let heading = 'Survey Statics';
            $('.filterSurveyType').hide();
            $('.filterSurvey>td').html('<strong>'+heading.toUpperCase()+'</strong>');
        }
        $('.pdf-head').show();
        
        console.log({ width, height });
        html2PDF(pages, {
            // margin: [50,10],
            jsPDF: {  
                orientation: 'p',
                unit: 'mm',
                format: 'a4',
            },
            html2canvas: { 
                // scale: 2,
                width:width,
                scrollX: 0,
                scrollY: -window.scrollY
            },
            imageType: 'image/jpeg',
            output: '<?=date('Y-m-d-H-i-s')?>.pdf'
        });
        // html2PDF(pages, {
        //     output: '<?=date('Y-m-d-H-i-s')?>.pdf'
        // });
        $('.pdf-head').hide();
    }

 // End export pdf
//  $('#download-csv').click(function(){
//     let data_type = $('.data_type').val();
//     let url ="./ajax/ajaxOn_survey_statistics.php?export=csv&data_type="+data_type;
//     alert(url);
//     //window.location.href =url;

//  });
</script>

