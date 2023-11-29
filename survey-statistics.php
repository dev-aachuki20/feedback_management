
<?php
// submit modal to schedule report
if(isset($_POST['schedule_btn'])){

    $start = $_POST['start_date'];
    $next_date =  date('Y-m-d H:i:s',strtotime('+'.$_POST['interval'] .'hour',strtotime($start)));

    $filter  = array('survey_hidden'=>$_POST['survey_hidden'],'data_type_hidden'=>$_POST['data_type_hidden'],'start_date'=>$_POST['st_date_hidden'],'end_date'=>$_POST['end_date_hidden']);

    $dataCol =  array(
        "temp_name"         => $_POST['report_name'],
        "filter"            => json_encode($filter),
        'schedule_date'     => $start,
        'intervals'         => $_POST['interval'],
        'next_schedule_date'=> $next_date,
        'end_date'          => $_POST['end_date'],
        'cby'               => $_SESSION['user_id'],
        'created_at'        => date("Y-m-d H:i:s")
    );

    $insert_value =  dbRowInsert("schedule_report_new",$dataCol);
    if( $insert_value){
        $msg = "Report Created Successfully";
        alertSuccess($msg,'');
    }else {
        $msg = "Sorry! Report Not Created";
        alertdanger($msg,'');
    }
    
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
    .large-btn{
    padding: 5px !important;
    }
    p {
    margin: 0px !important;
    }
</style>

<section class="content-header">
  <h1>STATISTICS</h1>
</section>
<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="row">
                <div class="col-md-2 statics-tab">
                    <button type="button" class="btn btn-outline-secondary graph-btn active" data-type="survey">Survey</button>
                </div>
                <div class="col-md-2 statics-tab">
                    <button type="button" class="btn btn-outline-secondary graph-btn" data-type="group">Group</button>
                </div>
                <div class="col-md-2 statics-tab">
                    <button type="button" class="btn btn-outline-secondary graph-btn" data-type="location">Location</button>
                </div>
                <div class="col-md-2 statics-tab">
                    <button type="button" data-type="department" class="btn btn-outline-secondary graph-btn" >Department</button>
                </div>
                <div class="col-md-2 statics-tab">
                    <button type="button" data-type="role" class="btn btn-outline-secondary graph-btn" >Role</button>
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
                                    <input type="hidden" name="data_type" class="data_type" value="survey">
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
                                <div class="ajaxData" style="width:25%;display: none;">
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
                        <div class="survey-statistics-btns" style="text-align: center;">
                            <div class="custum-btn">
                                <button type="button" class="btn btn-success btn-big btn-green large-btn schedule_btn" data-type="schedule">SCHEDULE NOW</button>
                            </div>
                            <div class="custum-btn">
                                <a href="?page=view-schedule-report&type=<?=$_GET['type']?>">
                                    <button type="button" class="btn btn-success btn-big btn-green large-btn">VIEW SCHEDULE</button>
                                </a>
                            </div>
                            <div>
                                <form action="" id="document_form" method="post">
                                    <input type="hidden" name="survey" id="survey_id" value="">
                                    <input type="hidden" name="sdate"  id="st_date" value="">
                                    <input type="hidden" name="edate"  id="ed_date" value="">
                                    <input type="hidden" name="data_type" id="survey_data_type" value="">
                                    <input type="hidden" name="survey_type" value="<?=$_GET['type']?>">
                                    <div class="form-right-btns">
                                        <div class="custum-btn">
                                            <button type="button" id="view-pdf" class="btn btn-big btn-primary large-btn" data-type="pdf">VIEW PDF</button>
                                        </div>
                                        <div class="custum-btn">
                                            <button type="button" id="download-csv" class="btn btn-big btn-primary large-btn" data-type="csv">DOWNLOAD CSV</button>
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
                                        <td colspan="3" style="text-align:center;margin-top:100px;"> <img src="<?=baseUrl().MAIN_LOGO?>" width="200"></td>
                                        </tr>
                                        <tr >
                                            <td colspan="3" style="text-align:center;height: 30px;color:red;font-size:20px;"></td>
                                        </tr>
                                        <tr class="borderClass filterSurvey">
                                            <td colspan="3" style="text-align:center;font-size:20px;"><strong><?=strtoupper('Survey Statistics')?></strong></td>
                                        </tr>
                                        <tr class="borderClass filterSurveyType">
                                            <td colspan="3" style="text-align:center;font-size:17px;"> </td>
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
                                <div class="row" style="width:90%; margin:0 auto; page-break-after: always;">
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
                                    <div class="row renderChart" style="page-break-after: always;"  id="block2" style="">
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <footer id="pdf-footer" style="display: none;">
                                        <div style="text-align: center;">
                                        <?=POWERED_BY?>
                                        <center><img  src="<?= BASE_URL.FOOTER_LOGO?>" alt="" width="150"/></center>
                                        </div>
                                    </footer>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- schedule report modal -->
<div class="modal" id="schedule_statistics_popup">
  <div class="modal-dialog" role="document">
        <div class="modal-content" style="height:300px ;">
            <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"> </h5>
            <button type="button" class="close closes" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <div class="modal-body">
            <div class="form-group">
                <form class="second_form" method="post">

                    <input type="hidden" name="survey_hidden" id="survey_hidden" value="">
                    <input type="hidden" name="data_type_hidden" id="data_type_hidden" value="">
                    <input type="hidden" name="st_date_hidden" id="st_date_hidden" value="">
                    <input type="hidden" name="end_date_hidden" id="end_date_hidden" value="">


                    <div class="form-group row">
                        <input type="hidden" name="template_id" value="" class="template_id">
                        <label for="staticEmail" class="col-sm-4 col-form-label">Report Name</label>
                        <div class="col-sm-8">
                            <input type="text"  class="form-control" id="report_name" name="report_name" placeholder="Report Name" value="" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="template_id" value="" class="template_id">
                        <label for="staticEmail" class="col-sm-4 col-form-label">Start Date</label>
                        <div class="col-sm-8">
                            <input type="date"  class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="" min="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="staticEmail" class="col-sm-4 col-form-label">End Date</label>
                        <div class="col-sm-8">
                            <input type="date"  class="form-control" id="end_date" name="end_date" placeholder="End Date" value="" min="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="staticEmail" class="col-sm-4 col-form-label">Interval</label>
                        <div class="col-sm-8">
                        <select class="form-control" id="interval" name="interval" required>
                            <?php foreach(service_type() as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" ><?=$value?></option>
                            <?php } ?>
                        </select>
                        </div>
                    </div>
                    <div class="pull-right">
                        <button type="submit"class="btn btn-success green-btn" id="schedule_btn" name="schedule_btn">Save</button>
                        <button type="button"class="btn btn-danger closes" style="background-color:#ff1c00 !important;">Cancel</button>
                    </div>
                </form>
            </div>
            </div>
        </div>
  </div>
</div>


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

    $('#st_date').val(fdate);
    $('#ed_date').val(sdate);

    
    $('#st_date_hidden').val(fdate);
    $('#end_date_hidden').val(sdate);
        
    if(survey==''){
        $('.error').show();
        return;
    }else{
        $('.error').hide();
    }
    let data_type = $('.data_type').val();
    if(data_type == 'survey'){
        $('.survey_name').hide();
    }else{
        $('.survey_name').show();
    }
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
            // console.log('footer_flag', response.footer_flag);
            // console.log('survey_data_count',response.survey_data_count);
            
            if(parseInt(response.survey_data_count) > 6){
                if(parseInt(response.footer_flag) !=0 && parseInt(response.footer_flag) <=3){
                    $('#pdf-footer').css("margin-top", '675px');
                }else if( parseInt(response.footer_flag) !=0 &&  parseInt(response.footer_flag) <= 6){
                    $('#pdf-footer').css("margin-top", '348px');
                }else{
                    $('#pdf-footer').css("margin-top", '20px');
                }
            }else{
                if(parseInt(response.survey_data_count) !=0 && parseInt(response.survey_data_count) <=3){
                    $('#pdf-footer').css("margin-top", '500px');
                }else if(parseInt(response.footer_flag) !=0 &&  parseInt(response.footer_flag) <= 6){
                    $('#pdf-footer').css("margin-top", '210px');
                }
                else{
                    $('#pdf-footer').css("margin-top", '210px');
                }
            }
            

            $('.renderChart').html(response.html);
            $('.loader').hide();
            $('.renderChart').show();

            let results = response.result;
            let classid = 1;
            $.each(results, function( k, v ) {
                let value_result = results[k]['data'];
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
    if(isNaN(values)){
        values =0;
    }
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
//close modal on click
$(".closes").click(function() {
    $('#schedule_statistics_popup').hide();
});

$(document).on('click', '.large-btn', function(){
        let sdate           = $('.start_data').val();
        let edate           = $('.end_date').val();
        let data_type       = $('.data_type').val();
        let survey          = $('.survey').val();
        let document_type   = $(this).data('type');

        //put value in form
        $('#survey_id').val(survey);
        $('#st_date').val(sdate);
        $('#edate').val(edate);
        $('#survey_data_type').val(data_type);
        if(document_type == 'pdf'){
            // export_pdf(sdate,edate,data_type,survey);
            $('#document_form').attr('action', './Export-Pdf/survey-statistics-pdf.php');
            $('#document_form').submit();

        }else if(document_type == 'csv'){
            $('#document_form').attr('action','./ajax/ajaxOn_survey_statistics.php?export=csv&data_type='+data_type);
            $('#document_form').submit();
        }else if(document_type == 'schedule'){
            // $("#start_date_hidden").val(sdate);

            $("#data_type_hidden").val(data_type);
            $("#survey_hidden").val(survey);
            $('#schedule_statistics_popup').show();
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
    $('.survey_name').hide();
    $('#pdf-footer').show();
    // draw html in pdf
    let element = document.getElementById('element');
    //$(".chartjs-render-monitor").css("width", "180");
    let box = document.querySelector('.chartjs-render-monitor');
    // let width = box.offsetWidth;
    let height = box.offsetHeight;
    // $(".col-md-3").css("width", "300");
    $('.col-md-3').attr('class', 'col-md-4');
    $(".chartjs-render-monitor").css("height", "90");

    html2pdf(element,{
        margin:5,
        filename:file_name,
        image:{type:'jpeg',quality:0.98},
        html2canvas:{scale:2,logging:true,dpi:192,letterRendering:true},
        jsPDF:{unit:'mm',format:'a4',orientation:'portrait'},
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    });

    $('#pdf-footer').hide();
    $(".chartjs-render-monitor").css("height", height);
    $('.col-md-4').attr('class', 'col-md-3 ');
    $('.pdf-head').hide();
    $('.survey_name').show();
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