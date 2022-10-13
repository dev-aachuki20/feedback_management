<?php
//$locationByUsers   = get_filter_data_by_user('locations');
?>

<div class="col-md-12 groupCheck" style="padding:0px;">
    <?php 
    if(isset($_GET['id'])){ 
        $location_id = get_assigned_user_data($_GET['id'],'location');
        if(count($location_id)>0){ ?>
            <div class="col-md-12 with-border">
                <h4>Assign Location</h4>
                <input type="checkbox" onclick="checked_all(this,'loc_checkbox')" /><strong> Select All</strong><br/><br/>
            </div>
        <?php }
        $location_id = implode(',',$location_id);
        if($location_id){
            $filterSurvey = " where id IN($location_id)";
        }else {
            $filterSurvey = " where id IN(0)";
        }
        record_set("get_location_id", "select * from locations $filterSurvey");
        $deptid_array= array();
        while($row_get_location_id=mysqli_fetch_assoc($get_location_id)){
        $location_id_saved[] =$row_get_location_id['id'];
        // get location assign to given group
            $deptidByGroup = $row_get_location_id['department_id'];
            $deptidByGroup = explode(',',$deptidByGroup);
            foreach($deptidByGroup as $dept){
                if($dept){
                    $deptid_array[$dept] = $dept;
                }
            }
        ///end
        }
        if(empty($locationid_array)){
            $locationid_array = array_keys(getLocation());
        }
        $locationid_array_explode = implode(',',$locationid_array);
        $locationName = get_data_by_id('locations',$locationid_array_explode);
        foreach( $locationName as $key => $value){ 
        $locationId    = $key;
        $locationName  = $value;
        ?>
        <div class="col-md-4">
            <input type="checkbox" <?=(in_array($locationId,$location_id_saved) ? 'checked ':' ')?> id="locationids<?php echo $locationId ?>" class="loc_checkbox" value="<?php echo $locationId; ?>" name="locationids[<?php echo $locationId; ?>]" /> 
            <label for="locationids<?php echo $locationId; ?>">
            <?php echo $locationName ?>
            </label>
        </div>
        <?php } 
    }
    else if($_GET['page'] =='create-report' and !empty($_GET['viewid'])){ ?>
    <!-- assign location -->
    <?php if(count($locationByUsers)>0) { ?>
        <div class="row locationCheck">
            <div class="col-md-12 with-border">
                <h4>Assign Location</h4>
                <input <?=$checkDisable?> type="checkbox" onclick="checked_all(this,'loc_checkbox')" /><strong> Select All</strong><br/><br/>
            </div>
            <?php 
            foreach($locationByUsers as $locationData){ 
            $locationId     = $locationData['id'];
            $locationName   = $locationData['name']; ?>
            <div class="col-md-4">
                <input <?=$checkDisable?> type="checkbox" id="locationids<?php echo $locationId ?>" <?=(in_array($locationId,$template_loc)) ? 'checked':''?> class="loc_checkbox" value="<?php echo $locationId; ?>" name="locationids[<?php echo $locationId; ?>]" /> 
                
                <label for="locationids<?php echo $locationId; ?>">
                <?php echo $locationName ?>
                </label>
            </div>
            <?php } ?>
        </div>
    <?php } ?>
    <?php } ?>
</div>

<script>
// for location checkbox
$(document).on("change", '.loc_checkbox', function(event) { 
    select_location();
});

//checked all location using select all
$(document).on("change", '.loc_checkbox_all', function(event) { 
  checked_all(this,"loc_checkbox");
  select_location();
});
function select_location(){
    var checkedArray=[];
    $(".loc_checkbox:checkbox:checked").each(function() {
        checkedArray.push($(this).val());
    });
    var filteredArray = checkedArray.filter(e => e !== 'on')
    ajax_for_checkbox(filteredArray,'add_user_location_assign')
}
</script>