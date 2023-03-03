<?php 
    // survey by user
    $surveyByUsers = get_survey_data_by_user($_GET['type']);
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
        border: unset;
    }
    .table-bordered>thead>tr>th, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>tbody>tr>td, .table-bordered>tfoot>tr>td {
        border: 1px solid #c4c1c1;
    }
    .table>thead:first-child>tr:first-child>th {
        border-top: 1px solid #c4c1c1;
    }
</style>
<section class="content-header">
  <h1>LEAGUE TABLES</h1>
</section>
<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="row filter_form">
            
                    <!-- <input type="hidden" name="post_values" value =<?=json_encode($_POST)?> > -->
                    <div class="box-header">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        <h3 class="box-title"> Search</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
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
                            <div class="col-md-3">
                                <div class="form-group"><label> <?=($_GET['type']) ? ucfirst($_GET['type']) : 'Survey'?></label>
                                    <select name="survey" class="form-control form-control-lg survey" required> <option value="">Select <?=ucfirst($_GET['type'])?></option>
                                    <?php foreach($surveyByUsers as $surveyData){
                                        $surveyId = $surveyData['id'];
                                        $surveyName = $surveyData['name']; ?>
                                            <option value="<?=$surveyId?>"><?=$surveyName?></option> 
                                    <?php } ?>
                                    </select>
                                    <span class="error" style="display:none;">This Field is Required</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="button" style="background-color: #00a65a !important;border-color: #008d4c;"name="filter" class="btn btn-success btn-block search" value="Search"/>
                                </div>
                            </div>
                        </div>
                        <hr style="border: 2px solid #6c757d36;">
                        <div class="row" style="margin-bottom: 21px;">
                            <div class="col-md-12">
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-secondary graph-btn" data-type="group">Group</button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-secondary graph-btn" data-type="location">Location</button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" data-type="department" class="btn btn-outline-secondary graph-btn" >Department</button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" data-type="role" class="btn btn-outline-secondary graph-btn" >Role</button>
                                </div>
                            </div>
                        </div>
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
                                </div>
                                <!-- loader div end  -->
                                <div class="col-md-12">
                                    <div class="col-md-2"></div>
                                    <div class="col-md-7 renderTable">
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
<script>
    ajax_league_table();
    function ajax_league_table(fdate,sdate,type,survey){
        $.ajax({
            method:"POST",
            url:'<?=baseUrl()?>ajax/view-league-data.php',
            data:{
                survey_type:type,
                fdate:fdate,
                sdate:sdate,
                survey:survey,
                mode:'survey_league'
            },
            success:function(response){
                response = JSON.parse(response);
                $('.renderTable').html(response);
                $('.loader').hide();
                $('.renderTable').show();
                $('#datatable1').DataTable( {  "aaSorting": [ [2,'desc'] ]}).destroy();  
                $('#datatable1').DataTable( {  "aaSorting": [ [2,'desc']]});
            }
        })
    }

    // check survey type (group,location or department)
    $(document).on('click','.graph-btn',function(){
        let type = $(this).data('type');
        $('.survey_type').val(type);
        $('.graph-btn').removeClass('active');
        $(this).addClass('active');
    })

    $(document).on('click','.search',function(){
        let fdate       = $('.start_data').val();
        let sdate       = $('.end_date').val();
        let type        = $('.survey_type').val();
        let survey      = $('.survey').val();
        if(new Date(fdate) > new Date(sdate)){
            alert('End Date Must Be Greater Than Start Date');
            return ;
        }
        if(type == ''){
            alert('Please choose Location,Group or department');
            return ;
        }
        if(survey == ''){
            $('.error').show();
            return false;
        }else {
            $('.error').hide();
        }
        $('.loader').show();
        $('.renderTable').hide();
        ajax_league_table(fdate,sdate,type,survey);
    })
</script>


