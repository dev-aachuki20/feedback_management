<?php
//$locationByUsers   = get_filter_data_by_user('locations');
?>

<div class="col-md-12 groupCheck" style="padding:0px;">
        <div class="col-md-12 with-border">
            <h4>Assign Location</h4>
            <input <?=$checkDisable?> type="checkbox" onclick="checked_all(this,'loc_checkbox')" /><strong> Select All</strong><br/><br/>
        </div>
    <?php 
    if(isset($_GET['id'])){
        $location_id = get_assigned_user_data($_GET['id'],'location');
    }
    $locationName = getLocation(); 
    if(isset($_GET['id'])){
        $location_id = get_assigned_user_data($_GET['id'],'location');
    }else{
        $location_id = array_keys(getLocation());
    }
    if($_SESSION['user_type']>2){
        $assignLocationId = get_assigned_user_data($_SESSION['user_id'],'location');
        if(count($assignLocationId)>0){
            $array =[];
            foreach($locationName as $key=> $value){
                if(in_array($key,$assignLocationId)){
                    $array[$key] =$value;
                }
            }
            $locationName = $array;
        }else{
            $locationName = []; 
        }
    }
    
    foreach( $locationName as $key => $value){ 
        $locationId    = $key;
        $locationName  = $value;
        ?>
        <div class="col-md-4">
            <input type="checkbox" <?=(in_array($locationId,$location_id) ? 'checked ':' ')?> id="locationids<?php echo $locationId ?>" class="loc_checkbox" value="<?php echo $locationId; ?>" name="locationids[<?php echo $locationId; ?>]" /> 
            <label for="locationids<?php echo $locationId; ?>">
            <?php echo $locationName ?>
            </label>
        </div>
    <?php } ?>
</div>

<script>
// for location checkbox
// $(document).on("change", '.loc_checkbox', function(event) { 
//     select_location();
// });

//checked all location using select all
$(document).on("change", '.loc_checkbox_all', function(event) { 
  checked_all(this,"loc_checkbox");
  select_location();
});
// function select_location(){
//     var checkedArray=[];
//     $(".loc_checkbox:checkbox:checked").each(function() {
//         checkedArray.push($(this).val());
//     });
//     var filteredArray = checkedArray.filter(e => e !== 'on')
//     ajax_for_checkbox(filteredArray,'add_user_location_assign')
// }
</script>