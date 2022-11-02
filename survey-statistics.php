
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
.graph-body {
    background: #ecf0f5;
    border: 1px solid #c8bfbf;
    margin:30px 0 0px ;
}
.graph-btn.active {
    background: #a020f0;
    color: #fff;
}
.chartjs-render-monitor{
    width:100% !important;
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
                            <div class="col-md-3 ">
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
                        <div class="gauge-wrapper" id="element">
                            <div class="pdf-div">
                                <div class="col-sm-12">
                                <div class="pdf-head" style="display: none;">
                                    
                                    <table width="93%" style="margin:0 auto;">
                                        <tr>
                                        <td colspan="3" style="text-align:center;margin-top:100px;"> <img src="<?=baseUrl()?>hats-logo-survey50.png" width="200"></td>
                                        </tr>
                                        <tr >
                                            <td colspan="3" style="text-align:center;height: 30px;"></td>
                                        </tr>
                                        <tr class="borderClass filterSurveyType">
                                            <td colspan="3" style="text-align:center;font-size:20px;"> </td>
                                        </tr>
                                        <tr class="borderClass filterSurvey">
                                            <td colspan="3" style="text-align:center;font-size:18px;"><strong> Survey Statistics</strong></td>
                                        </tr>
                                        <tr class="borderClass filterDate" style="font-weight: 900;font-size: 16px;">
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
            </div>
        </div>
    </div>
</section>

<script src="https://unpkg.com/chart.js@2.8.0/dist/Chart.bundle.js"></script>
<script src="https://unpkg.com/chartjs-gauge@0.2.0/dist/chartjs-gauge.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.8.0/html2pdf.bundle.js"></script>

<script>
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
    // hide div for type survey
    (type == 'survey') ? $(".ajaxData").hide():$(".ajaxData").show();
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

$(document).on('click', '.large-btn', function(){
    
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

    if(sdate !='' && edate !=''){
        $('.filterDate').show();
        $('.pdf-sdate').html(sdate);
        $('.pdf-edate').html(edate);
    }else {
        $('.filterDate').hide();
    }
    $('.filterSurvey').hide();
    var file_name ='<?='Survey Statics-'.date('Y-m-d-H-i-s').'.pdf'?>';
    if(data_type !='' ){
        $('.filterSurveyType').show();
        let heading = data_type+' Statistics';
        $('.filterSurveyType>td').html('<strong>'+heading.toUpperCase()+'</strong>');
        if(survey){
                var file_name = survey_arr[survey]+'<?='-'.date('Y-m-d-H-i-s').'.pdf'?>';
                $('.filterSurvey').show();
                $('.filterSurvey>td').html('<strong>'  +survey_arr[survey].toUpperCase()+'</strong>');
        }
    }else {
        let heading = 'Survey Statics';
        $('.filterSurveyType>td').html('<strong>'+heading.toUpperCase()+'</strong>');
    }
    $('.pdf-head').show();

    // draw html in pdf
    let element = document.getElementById('element');
    //$(".chartjs-render-monitor").css("width", "180");
    // let box = document.querySelector('.pdf-div');
    // let width = box.offsetWidth;
    // let height = box.offsetHeight;
    // $(".col-md-3").css("width", "300");
    html2pdf(element,{
        margin:10,
        filename:file_name,
        image:{type:'jpeg',quality:0.98},
        html2canvas:{scale:2,logging:true,dpi:192,letterRendering:true},
        jsPDF:{unit:'mm',format:'a3',orientation:'portrait'}
    })
    //$(".chartjs-render-monitor").css("width", "233");

    $('.pdf-head').hide();
}
</script>




<!-- <div id="element">
    <div>First Content</div>
    <div class="html2pdf__page-break"></div>
    <div>second content</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.8.0/html2pdf.bundle.js"></script>
<script>
let element = document.getElementById('element')
html2pdf(element,{
    margin:100,
    filename:'output.pdf',
    image:{type:'jpeg',quality:0.98},
    html2canvas:{scale:2,logging:true,dpi:192,letterRendering:true},
    jsPDF:{unit:'mm',format:'a4',orientation:'portrait'}
})
</script> -->
</html>