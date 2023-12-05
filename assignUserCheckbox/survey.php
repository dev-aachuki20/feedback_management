<?php if(count($surveyByUsers)>0){ ?>
    <div class="col-md-12 with-border">
        <h4>Assign Survey</h4>
        <input type="checkbox" class="survey_checkbox" onclick="checked_all(this,'survey_checkbox')" /><strong> Select All</strong><br/><br/>
    </div>
    <?php 
    if(isset($_GET['id'])){
        $survey_id = get_assigned_user_data($_GET['id'],'survey');
        $survey_id = implode(',',$survey_id);
        if($survey_id){
            $filterSurvey = " where id IN($survey_id)";
        }else {
            $filterSurvey = " where id IN(0)";
        }
        record_set("get_survey_id", "select * from surveys $filterSurvey");
        $groupid_array= array();
        while($row_get_survey_id=mysqli_fetch_assoc($get_survey_id)){
            $survey_saved[] =$row_get_survey_id['id'];
            // get location assign to given group
            $groups_by_survey = $row_get_survey_id['groups'];
            $groups_by_survey = explode(',',$groups_by_survey);
            foreach($groups_by_survey as $survey){
                if($survey){
                    $groupid_array[$survey] = $survey;
                }
            }
        }
    }else{
        $survey_saved = array_keys(get_allowed_survey('survey','',1));
    }
    foreach($surveyByUsers as $suveyData){ 
        $survyId    = $suveyData['id'];
        $survyName  = $suveyData['name'];
        ?>
        <div class="col-md-4">
        <input class="survey_checkbox common_survey_class" type="checkbox" <?=(in_array($survyId,$survey_saved) ? 'checked ':' ')?> id="surveyids<?php echo $survyId ?>" value="<?php echo $survyId; ?>" name="surveyids[<?php echo $survyId; ?>]" /> 
        
        <label for="surveyids<?php echo $survyId; ?>">
        <?php echo $survyName ?>
        </label>
        </div>
    <?php } ?>   
<?php } ?>

<script>
// for group load

// $(".survey_checkbox").change(function(){
//    var checkedSurveyArray=[];
//     $(".common_survey_class:checkbox:checked").each(function() {
//         checkedSurveyArray.push($(this).val());
//     });
//     var filteredArray = checkedSurveyArray.filter(e => e !== 'on')
//     console.log(filteredArray);
//     ajax_for_checkbox(filteredArray,'load_group');
// });
</script>