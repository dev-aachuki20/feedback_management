<?php if(count($pulseByUsers)>0){ ?>
    <div class="col-md-12 with-border">
        <h4>Assign Pulse</h4>
        <input type="checkbox" class="pulse_checkbox" onclick="checked_all(this,'pulse_checkbox')" /><strong> Select All</strong><br/><br/>
    </div>
    <?php 
    if(isset($_GET['id'])){
        $pulse_id = get_assigned_user_data($_GET['id'],'pulse');
        $pulse_id = implode(',',$pulse_id);
        if($pulse_id){
        $filterSurvey = " where id IN($pulse_id)";
        }else {
        $filterSurvey = " where id IN(0)";
        }
        record_set("get_pulse_id", "select * from surveys $filterSurvey");
        $groupid_Pulsearray= array();
        while($row_get_pulse_id=mysqli_fetch_assoc($get_pulse_id)){
            $pulse_saved[] =$row_get_pulse_id['id'];
            // get location assign to given group
            $groups_by_pulse = $row_get_pulse_id['groups'];
            $groups_by_pulse = explode(',',$groups_by_pulse);
            foreach($groups_by_pulse as $pulse){
                if($pulse){
                    $groupid_Pulsearray[$pulse] = $pulse;
                }
            }
        }
    }else{
        $pulse_saved = array_keys(get_allowed_survey('pulse','',1));
    }
    foreach($pulseByUsers as $pulseData){ 
        $pulseId    = $pulseData['id'];
        $pulseName  = $pulseData['name'];
        ?>
        <div class="col-md-4">
        <input class="pulse_checkbox common_survey_class" type="checkbox" <?=(in_array($pulseId,$pulse_saved) ? 'checked ':' ')?> id="surveyids<?php echo $pulseId ?>" value="<?php echo $pulseId; ?>" name="surveyids[<?php echo $pulseId; ?>]" /> 
        
        <label for="surveyids<?php echo $pulseId; ?>">
        <?php echo $pulseName ?>
        </label>
        </div>
    <?php } ?>   
<?php } ?>

<script>
// for group load
//var checkedPulseArray;
// $(".pulse_checkbox").change(function(){
//     var checkedSurveyArray=[];
//     $(".common_survey_class:checkbox:checked").each(function() {
//         checkedSurveyArray.push($(this).val());
//     });
//     // combine checked survey id and pulse id 
//     //array_survey_pulse = checkedSurveyArray.concat(checkedPulseArray);
//     filteredArray = checkedSurveyArray.filter(e => e !== 'on')
//     console.log(filteredArray);
//     ajax_for_checkbox(filteredArray,'load_group');
// });
</script>