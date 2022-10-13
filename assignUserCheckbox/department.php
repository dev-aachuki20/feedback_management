<div class="col-md-12 locationCheck" style="padding:0px;">
    <?php 
    if(isset($_GET['id'])){ ?>
    <div class="col-md-12 with-border">
        <h4>Assign Departments</h4>
        <input type="checkbox" onclick="checked_all(this,'dept_checkbox')" /><strong> Select All</strong><br/><br/>
    </div>
    <?php
    $department_id = get_assigned_user_data($_GET['id'],'department');
    $department_id = implode(',',$department_id);
    if($department_id){
        $filterSurvey = " where id IN($department_id)";
    }else {
        $filterSurvey = " where id IN(0)";
    }
    record_set("get_department_id", "select * from departments $filterSurvey");
    while($row_get_department_id=mysqli_fetch_assoc($get_department_id)){
        $department_id_saved[] = $row_get_department_id['id'];
    }
    
    $deptidByGroup_explode = implode(',',$deptid_array);
    $departmentName = get_data_by_id('departments',$deptidByGroup_explode);

    foreach($departmentName as $key => $value){ 
    $deptId  = $key;
    $deptName = $value; ?>
    <div class="col-md-4">
        <input type="checkbox" <?=(in_array($deptId,$department_id_saved) ? 'checked ':' ')?> id="departmentids<?php echo $deptId ?>" class="dept_checkbox" value="<?php echo $deptId; ?>" name="departmentids[<?php echo $deptId; ?>]"/> 
        <label for="departmentids<?php echo $deptId; ?>">
        <?php echo $deptName ?>
        </label>
    </div>
    <?php } }
    else if($_GET['page'] =='create-report' and !empty($_GET['viewid'])){ ?>
        <!-- assign department -->
        <?php if(count($departmentByUsers)>0) { ?>
            <div class="row departmentCheck">        
                <div class="col-md-12 with-border">
                    <h4>Assign Departments</h4>
                    <input <?=$checkDisable?> type="checkbox" onclick="checked_all(this,'dept_checkbox')" /><strong> Select All</strong><br/><br/>
                </div>
                <?php 
                foreach($departmentByUsers as $departmentData){ 
                    $departmentId   = $departmentData['id'];
                    $departmentName = $departmentData['name'];
                    ?>
                    <div class="col-md-4">
                    <input <?=$checkDisable?> type="checkbox" id="departmentids<?php echo $departmentId ?>" class="dept_checkbox" <?=(in_array($departmentId,$template_dep)) ? 'checked':''?> value="<?php echo $departmentId; ?>" name="departmentids[<?php echo $departmentId; ?>]"/> 
                    <label for="departmentids<?php echo $departmentId; ?>">
                    <?php echo $departmentName ?>
                    </label>
                    </div>
                <?php }?>
            </div>
        <?php } ?>
    <?php } ?>
</div>

<script>
//checked all department using select all
$(document).on("change", '.dept_checkbox_all', function(event) { 
    checked_all(this,"dept_checkbox");
});
</script>