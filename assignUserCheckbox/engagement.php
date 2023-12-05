<?php if(count($engagementByUsers)>0){ ?>
    <div class="col-md-12 with-border">
        <h4>Assign Engagement</h4>
        <input type="checkbox" class="engagement_checkbox" onclick="checked_all(this,'engagement_checkbox')" /><strong> Select All</strong><br/><br/>
    </div>
    <?php 
    if(isset($_GET['id'])){
        $engagement_id = get_assigned_user_data($_GET['id'],'engagement');
        $engagement_id = implode(',',$engagement_id);
        if($engagement_id){
        $filterSurvey = " where id IN($engagement_id)";
        }else {
        $filterSurvey = " where id IN(0)";
        }
        record_set("get_engagement_id", "select * from surveys $filterSurvey");
        $groupid_Pulsearray= array();
        while($row_get_engagement_id=mysqli_fetch_assoc($get_engagement_id)){
            $engagement_saved[] = $row_get_engagement_id['id'];
            // get location assign to given group
            $groups_by_pulse = $row_get_engagement_id['groups'];
            $groups_by_pulse = explode(',',$groups_by_pulse);
            foreach($groups_by_pulse as $pulse){
                if($pulse){
                    $groupid_Pulsearray[$pulse] = $pulse;
                }
            }
        }
    }else{
        $engagement_saved = array_keys(get_allowed_survey('engagement','',1));
    }
    foreach($engagementByUsers as $engagementData){ 
        $engagementId    = $engagementData['id'];
        $engagementName  = $engagementData['name'];
        ?>
        <div class="col-md-4">
        <input class="engagement_checkbox common_survey_class" type="checkbox" <?=(in_array($engagementId,$engagement_saved) ? 'checked ':' ')?> id="surveyids<?php echo $engagementId ?>" value="<?php echo $engagementId; ?>" name="surveyids[<?php echo $engagementId; ?>]" /> 
        
        <label for="surveyids<?php echo $engagementId; ?>">
        <?php echo $engagementName ?>
        </label>
        </div>
    <?php } ?>   
<?php } ?>

<script>
// for group load
//var checkedEngagementArray;
// $(".engagement_checkbox").change(function(){
//     var checkedSurveyArray=[];
//     $(".common_survey_class:checkbox:checked").each(function() {
//         checkedSurveyArray.push($(this).val());
//     });
//     // combine checked survey id and pulse id and engegament id  
//     //array_survey_pulse_engagement = array_survey_pulse.concat(checkedEngagementArray);
//     filteredArray = checkedSurveyArray.filter(e => e !== 'on')
//     console.log(filteredArray);
//     ajax_for_checkbox(filteredArray,'load_group');
// });
</script>