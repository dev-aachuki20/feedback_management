<div class="col-md-12 locationCheck" style="padding:0px;">
    <div class="col-md-12 with-border">
        <h4>Assign Departments</h4>
        <input type="checkbox" onclick="checked_all(this,'dept_checkbox')" /><strong> Select All</strong><br/><br/>
    </div>
    <?php
    $departmentName = getDepartment(); 
    if(isset($_GET['id'])){
        $department_id = get_assigned_user_data($_GET['id'],'department');
    }
    if($_SESSION['user_type']>2){
        $assignDepartmenttId = get_assigned_user_data($_SESSION['user_id'],'department');
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
    
    foreach($departmentName as $key => $value){ 
        $deptId  = $key;
        $deptName = $value; ?>
        <div class="col-md-4">
            <input type="checkbox" <?=(in_array($deptId,$department_id) ? 'checked ':' ')?> id="departmentids<?php echo $deptId ?>" class="dept_checkbox" value="<?php echo $deptId; ?>" name="departmentids[<?php echo $deptId; ?>]"/> 
            <label for="departmentids<?php echo $deptId; ?>">
            <?php echo $deptName ?>
            </label>
        </div>
    <?php } ?>
</div>

<script>
// for department checkbox
$(document).on("change", '.dept_checkbox', function(event) { 
    select_department();
});
//checked all department using select all
$(document).on("change", '.dept_checkbox_all', function(event) { 
    checked_all(this,"dept_checkbox");
    select_department();
});
// function select_department(){
//     var checkedArray=[];
//     $(".dept_checkbox:checkbox:checked").each(function() {
//         checkedArray.push($(this).val());
//     });
//     var filteredArray = checkedArray.filter(e => e !== 'on')
//     ajax_for_checkbox(filteredArray,'add_user_department_assign')
// }
</script>