<?php 
    include('function/function.php');
    //Get Survey Details
    $manager_id = $_GET['managerid'];
    $manager_location = $_SESSION['user_locationid'];
    $manager_location_arr = explode(',',$manager_location);
    rsort($manager_location_arr);
    $manager_loca = $manager_location_arr[0];
    if(isset($_POST['change_location']) && $_POST['change_location'] == "change_location"){
        $manager_loca = $_POST['manager_location'];
    }

    $surveyid = $_GET['surveyid'];
    $client_id = '';
    if(isset($_GET['surveyid'])){
        record_set("get_survey", "select * from surveys where id='".$surveyid."' and cstatus=1");
        if($totalRows_get_survey > 0){
            $row_get_survey = mysqli_fetch_assoc($get_survey);
            $client_id = $row_get_survey['clientid'];
        }else{
            echo 'Wrong survey ID.'; 
            exit;
        }
    }else{
        echo 'Missing survey ID.';  
        exit;
    }

    $co_action = "";
    $contact_comment="";
    $created_date ="";
    $showAllComment =[];
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
        if($row_get_questions['survey_step_id'] == 0){
    
        }else{
            $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['id'] = $row_get_questions['id'];
            $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['question'] = $row_get_questions['question'];
            $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['ifrequired'] = $row_get_questions['ifrequired'];
            $questions[$row_get_questions['survey_step_id']][$row_get_questions['id']]['answer_type'] = $row_get_questions['answer_type'];
        }
    } 
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $row_get_survey['name']; ?></title>
        <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Roboto', sans-serif;
            }
            .btn.btn-primary {
                color: white;
                background: navy;
                min-width: 100px;
            }
            .depart-list {
                padding: 10px 0;
                list-style: none;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                max-width: 70%;
                margin: 0 auto;
            }

            .depart-list li {
                flex: 25%;
                max-width: 25%;
            }
            
            .depart-list h4 {
                margin: 0;
            }

            form#contactActionForm .form-group {
                display: flex;
                justify-content: center;
                padding-top:10px;
            }

            .form-control {
                display: block;
                width: 30%;
                height: calc(1.5em + .75rem + 2px);
                padding: .375rem .75rem;
                font-size: 1rem;
                font-weight: 400;
                line-height: 1.5;
                color: #495057;
                background-color: #fff;
                background-clip: padding-box;
                border: 1px solid #ced4da;
                border-radius: .25rem;
                transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            }

            .btn-primary {
                color: #fff;
                background-color: #007bff;
                border-color: #007bff;
            }

            .btn {
                display: inline-block;
                font-weight: 400;
                color: #212529;
                text-align: center;
                vertical-align: middle;
                padding: .375rem .75rem;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: .25rem;
                transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            }
            #manager_location{
                width: 15% !important;
            }
            .d-none{
                display:none !important;
            }
        </style>
    </head>
    <body>
        <div id="reportPage">
            <div align="center"><img src="<?=MAIN_LOGO?>" width="200"></div>
            <?php 
                record_set("get_loc_dep", "select locationid, departmentid from answers where surveyid='".$surveyid."' ".$ans_filter_query);
                $row_get_loc_dep = mysqli_fetch_assoc($get_loc_dep);
            
                //Department
                record_set("get_department", "select name from departments where id = '".$row_get_loc_dep['departmentid']."'");
                $row_get_department = mysqli_fetch_assoc($get_department);

                //Location
                record_set("get_location", "select name from locations where id = '".$row_get_loc_dep['locationid']."'");
                $row_get_location = mysqli_fetch_assoc($get_location);

                //School
                record_set("get_school", "select answertext from answers where surveyid ='".$surveyid."' and answerid='-3' and answerval='10' and cstatus=1");
            ?>
            <div class="row notforpdf" style="text-align: center;">
                <div class="col-md-12">
                    <a class="btn btn-xs btn-info " href="export-feedback.php?surveyid=<?php echo $surveyid;?>&aid=-2&avl=10" style="text-decoration: none;background-color: deepskyblue; color: white;" target="_blank">Export CSV</a>

                    <a class="btn btn-xs btn-info " id="exportPDF" href="#" style="text-decoration: none;background-color: deepskyblue; color: white;">Export PDF</a>  
                </div>
            </div>
            <form action="" method="POST" id="survey_answers">
                <input type="hidden" name="change_location" value="change_location">
                <div class="row notforpdf" style="margin-top: 15px;margin-bottom: 15px">
                    <div class="col-md-12">
                        <div class="form-group" align="center">
                            <?php 
                                if(isset($manager_location_arr) && count($manager_location_arr) > 0){ 
                                    $location_names =  record_set("get_locations", "select * from locations where id IN($manager_location) AND  cstatus=1"); 
                            ?>
                                <label for="manager_location">Location : </label>
                                <select class="" id="manager_location" name="manager_location" style="font-family: inherit !important;">
                                    <?php while($row_get_locations = mysqli_fetch_assoc($get_locations)){ ?>
                                        <option <?=($row_get_locations['id'] == $manager_loca) ? "selected" : '' ?> value="<?=$row_get_locations['id']?>"><?=$row_get_locations['name']?></option>
                                    <?php } ?>
                                </select>
                            <?php } ?>
                        </div>
                    </div>        
                </div>
                <table width="100%">
                    <thead>
                        <tr>
                            <td style="border-bottom:2px solid #000;">
                                <h2 align="center" style="margin:0px;">
                                <?php echo $row_get_survey['name']; ?>
                                </h2>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <!-- Survey Steps -->
                                <?php foreach($survey_steps AS $key => $value) { ?>
                                    <h4 align="center" style="margin-top:20px;margin-bottom:10px;">
                                        <?php echo $value['number'].".".$value['title']; ?>
                                    </h4>
                                    <!-- Survey Questions -->
                                    <?php
                                        foreach($questions[$key] AS $question){
                                            $questionid = $question['id']; 
                                            $answer_type = $question['answer_type'];
                                            $totalRows_get_child_questions = 0;
                                            $questions_array = array();
                                            $answers_array = array();
                                    ?>
 
                                        <!-- Answer Type 1 & 4 -->
                                        <?php if($answer_type == 1 || $answer_type == 4 || $answer_type == 6){ ?>
                                            <?php 
                                                record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'"); 
                                                if($totalRows_get_questions_detail>0){
                                                    while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){

                                                        if($row_get_questions_detail['condition_yes_no'] == 1){
                                                            $questions_array[$row_get_questions_detail['id']] = $row_get_questions_detail['description'].' (Conditional)';
                                                        }else{
                                                            $questions_array[$row_get_questions_detail['id']] = $row_get_questions_detail['description'];
                                                        }
                                            
                                                    }
                                                }
                                                record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."'   and locationid=".$manager_loca);  
                                                if($totalRows_get_answers>0){
                                                    while($row_get_answers = mysqli_fetch_assoc($get_answers)){
                                                        $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
                                                    }
                                                }
                                                $counts = array_count_values($answers_array);
                                            ?>
                                        <?php } ?>
                                        <!-- End Answer Type 1 & 4 -->

                                        <!-- Answer Type 2 & 3 -->
                                        <?php if($answer_type == 2 || $answer_type == 3){ ?>
                                            <?php 
                                                record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$questionid."' and locationid=".$manager_loca);  
                                                if($totalRows_get_answers>0){
                                                    while($row_get_answers = mysqli_fetch_assoc($get_answers)){
                                                        //print_r($row_get_answers);
                                                        $answers_array[$row_get_answers['id']] = $row_get_answers['answertext'];
                                                    }
                                                }
                                                $counts = array_count_values($answers_array);
                                            ?>
                                        <?php } ?>
                                        <!-- End Answer Type 2 & 3-->

                                        <!-- Answer Type 1 & 4 -->
                                    <?php if($answer_type==1 || $answer_type==4 || $answer_type==6){
                                        if($answer_type==1){
                                            //get Child Questions
                                            $get_child_questions = "select * from questions where parendit='".$questionid."' and cstatus='1'";
                                            record_set("get_child_questions", $get_child_questions);
                                        }
                                        if(empty($totalRows_get_child_questions)){
                                    ?>
                                        <table width="505px" align="center">
                                            <tr>
                                                <td align="center" colspan="2">
                                                    <h3 style="margin-top:10px;">
                                                        <?php echo $question['question']?>
                                                    </h3>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" align="right" width="200px">
                                                    <div style="padding:20px 0;">
                                                        <?php foreach($questions_array as $key=>$val){ ?>
                                                            <div style="margin:0 0 22px 0;padding:5px 0 0 0;">
                                                                <?php echo $val; ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                                <td width="304px;">
                                                    <div style="width:300px;border:1px solid #999;padding:13px 0;background-image:url(back.jpg);">
                                                        <?php
                                                            $clr_loop=0;
                                                            $table_display_data = array();
                                                            foreach($questions_array as $key=>$val){
                                                                $clr_loop++;
                                                                $total_ans = count($answers_array);
                                                                $percentage = (100/$total_ans)*$counts[$key];
                                                                $table_display_data[$val]=$percentage;
                                                        ?>
                                                            <div title="<?php echo round($percentage,2); ?>%" style="width:<?php echo $percentage; ?>%;height:30px;margin:0 0 13px 0;background-color:#<?php echo survey_result_graph_colors_name($clr_loop); ?>;">
                                                            </div>
                                                        <?php } ?> 
                                                    </div>
                                                    <table width="302px" style="font-size:10px;">
                                                        <tr>
                                                            <td align="left">0%</td>
                                                            <td align="right">10%</td>
                                                            <td align="right">20%</td>
                                                            <td align="right">30%</td>
                                                            <td align="right">40%</td>
                                                            <td align="right">50%</td>
                                                            <td align="right">60%</td>
                                                            <td align="right">70%</td>
                                                            <td align="right">80%</td>
                                                            <td align="right">90%</td>
                                                            <td align="right">100%</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <table style="font-size:14px;" border="1" width="100%" cellspacing="0" cellpadding="4">
                                                        <tr>
                                                            <td style="background-color:#f0f0f0;">Answer Choices</td>
                                                            <td style="background-color:#f0f0f0;">Responses</td>
                                                        </tr>
                                                        <?php foreach($table_display_data as $key=>$val){ ?>
                                                            <tr>
                                                                <td><?php echo $key; ?></td>
                                                                <td><?php echo round($val,2); ?>%</td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="height:40px;">&nbsp;</td>
                                            </tr>
                                        </table>
                                    <?php  }else{ ?>
                                        <table width="505px" align="center">
                                            <tr>
                                                <td align="center" colspan="2">
                                                    <h3 style="margin-top:10px;">
                                                        <?php echo $row_get_questions['question']?>
                                                    </h3>
                                                </td>
                                            </tr>
                                        </table>
                                        <table width="505px" align="center" style="font-size:14px;" border="1" cellspacing="0" cellpadding="4">
                                            <tbody>
                                                <tr>
                                                    <td style="background-color:#f0f0f0;">&nbsp;</td>
                                                    <?php
                                                        $child_answer = array();
                                                        $tdloop = 0;
                                                        record_set("get_questions_detail", "select * from questions_detail where questionid='".$questionid."' and surveyid='".$surveyid."' and cstatus='1'  ");
                                                        while($row_get_questions_detail = mysqli_fetch_assoc($get_questions_detail)){ 
                                                            $tdloop++; 
                                                    ?>
                                                        <td style="background-color:#f0f0f0;">
                                                            <?php
                                                                $child_answer[$row_get_questions_detail['id']] = $row_get_questions_detail['description'];
                                                                echo $row_get_questions_detail['description'];
                                                            ?>
                                                        </td>
                                                    <?php } ?>
                                                </tr>
                                                <?php 
                                                    $allPercentage = 0;
                                                    while($row_get_child_questions = mysqli_fetch_assoc($get_child_questions)){
                                                ?>
                                                    <tr>
                                                        <td style="background-color:#f0f0f0;">
                                                            <?php echo $row_get_child_questions['question']?>
                                                        </td>
                                                        <?php
                                                            $answers_array = array();
                                                            record_set("get_answers", "select * from answers where surveyid='".$surveyid."' ".$ans_filter_query." and questionid='".$row_get_child_questions['id']."' and locationid=".$manager_loca); 
                                                            if($totalRows_get_answers>0){
                                                                while($row_get_answers = mysqli_fetch_assoc($get_answers)){
                                                                    $answers_array[$row_get_answers['id']] = $row_get_answers['answerid'];
                                                                }
                                                            }
                                                            $anscount =  count($answers_array);
                                                            $counts = array_count_values($answers_array);
                                                            $totalPercentage = 0;
                                                            foreach($child_answer as $key=>$child_answer_option){
                                                        ?>
                                                            <td>
                                                                <?php 
                                                                    $percentage = round(((100/$anscount)*$counts[$key]),2);
                                                                    echo $percentage.'%';
                                                                ?>
                                                            </td>
                                                        <?php } ?>
                                                    </tr>
                                                <?php $allPercentage += $percentage;} ?>
                                                <?php //echo $allPercentage; ?>
                                            </tbody>
                                        </table>
                                    <?php  } } ?>
                                    <!-- End Answer Type 1 & 4 -->
                                    <!-- Answer Type 2 & 3-->
                                    <?php if($answer_type == 2 || $answer_type == 3){ ?>
                                        <table width="505px" align="center">
                                            <tr>
                                                <td align="center">
                                                    <h3 style="margin-top:10px;"><?php echo $question['question']?></h3>
                                                </td>
                                            </tr>
                                            <?php 
                                                $qno = 0; 
                                                foreach ($answers_array as $key=>$val){ 
                                                    $qno++; 
                                            ?>
                                                <tr>
                                                    <td><?php echo $qno; ?>. <?php echo $val; ?></td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <td style="height:40px;">&nbsp;</td>
                                            </tr>
                                        </table>  
                                    <?php } ?>
                                    <!-- End Answer Type 2 & 3-->
                                <?php }  ?>
                                <?php if($answer_type == 4000000000000){ ?>
                                    <table width="505px" align="center">
                                        <tr>
                                            <td align="center">
                                                <h3 style="margin-top:10px;"><?php echo $row_get_questions['question']?></h3>
                                            </td>
                                        </tr>
                                        <?php 
                                            $qno = 0; 
                                            foreach ($answers_array as $key=>$val){ 
                                                $qno++; 
                                        ?>
                                            <tr>
                                                <td><?php echo $qno; ?>. <?php echo $val; ?></td>
                                            </tr>
                                        <?php } ?>
                                        <tr>
                                            <td style="height:40px;">&nbsp;</td>
                                        </tr>
                                    </table>  
                                <?php } ?>
                                <!-- End Survey Questions -->
                            <?php } ?>
                            <!-- End Survey Steps -->
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="custom_contact_action" style="text-align: center;">
                <?php 
                    if(!empty($co_action)){
                        $client_name='';
                        record_set("get_client", "select * from clients where id='".$client_id."' and cstatus=1");
                        if($totalRows_get_client > 0){
                            $row_get_client = mysqli_fetch_assoc($get_client);
                            $client_name =  $row_get_client['name'];
                        }
                ?>
                    <?php
                        foreach($showAllComment as $key=>$item){
                            echo '<h3>'.ucfirst($client_name).' contacted on '.date("d/m/Y",strtotime($item['created_date'])).' | '.ucfirst($client_name).' '.$item['action'].' on '.date("d/m/Y",strtotime($item['created_date'])).' : comment : '.$item['comment'].'</h3>'; 
                        }
                    ?>
                <?php  } ?>
            </div>
        </form>
    </div>
</body>
<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>
<!-- Resources -->
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-html2canvas@latest/dist/jspdf-html2canvas.min.js"></script> 
<script>
    // start export pdf 
    const pages = document.getElementById('reportPage');
    $('#exportPDF').click(function(){
        $('.notforpdf').addClass('d-none');
        html2PDF(pages, {
            margin: [50,10],//PDF margin (in jsPDF units). Can be a single number, [vMargin, hMargin], or [top, left, bottom, right].
            jsPDF: {
                orientation: "p",
                unit: "in",
                format: 'letter',
            },
            html2canvas: { scale: 2 },
            imageType: 'image/jpeg',
            output: '.<?php echo $row_get_survey['name']; ?>/pdf/<?=date('Y-m-d-H-i-s')?>.pdf'
        });
        setTimeout(function(){
            window.location.reload();
        }, 2000);
    });

 // End export pdf
 </script>
<script>
    $(document).on('change','#manager_location',function(){
        $('#survey_answers').submit();
    })
</script>
</html>

