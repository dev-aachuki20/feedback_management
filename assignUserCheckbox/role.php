<div class="col-md-12 departmentCheck" style="padding:0px;">
    <?php 
    $roleidByGroup_explode = implode(',',$roleid_array);
    $roleName = get_data_by_id('roles',$roleidByGroup_explode);
    // if($_SESSION['user_type']<3){
    //     $roleName = getrole();
    // }
    if(isset($_GET['id'])){ ?>
    <div class="col-md-12 with-border">
        <h4>Assign Roles</h4>
        <input type="checkbox" onclick="checked_all(this,'role_checkbox')" /><strong> Select All</strong><br/><br/>
    </div>
    <?php
    $role_id = get_assigned_user_data($_GET['id'],'role');
    $role_id = implode(',',$role_id);
    if($role_id){
        $filterSurvey = " where id IN($role_id)";
    }else {
        $filterSurvey = " where id IN(0)";
    }
    record_set("get_role_id", "select * from roles $filterSurvey");
    while($row_get_role_id=mysqli_fetch_assoc($get_role_id)){
        $role_id_saved[] = $row_get_role_id['id'];
    }
    foreach($roleName as $key => $value){ 
    $roleId  = $key;
    $roleName = $value; ?>
    <div class="col-md-4">
        <input type="checkbox" <?=(in_array($roleId,$role_id_saved) ? 'checked ':' ')?> id="roleids<?php echo $roleId ?>" class="role_checkbox" value="<?php echo $roleId; ?>" name="roleids[<?php echo $roleId; ?>]"/> 
        <label for="roleids<?php echo $roleId; ?>">
        <?php echo $roleName ?>
        </label>
    </div>
    <?php } }
    else if($_GET['page'] =='create-report' and !empty($_GET['viewid'])){ ?>
        <!-- assign role -->
        <?php if(count($roleByUsers)>0) { ?>
            <div class="row roleCheck">        
                <div class="col-md-12 with-border">
                    <h4>Assign Roles</h4>
                    <input <?=$checkDisable?> type="checkbox" onclick="checked_all(this,'role_checkbox')" /><strong> Select All</strong><br/><br/>
                </div>
                <?php 
                foreach($roleByUsers as $roleData){ 
                    $roleId   = $roleData['id'];
                    $roleName = $roleData['name'];
                    ?>
                    <div class="col-md-4">
                    <input <?=$checkDisable?> type="checkbox" id="roleids<?php echo $roleId ?>" class="role_checkbox" <?=(in_array($roleId,$template_dep)) ? 'checked':''?> value="<?php echo $roleId; ?>" name="roleids[<?php echo $roleId; ?>]"/> 
                    <label for="roleids<?php echo $roleId; ?>">
                    <?php echo $roleName ?>
                    </label>
                    </div>
                <?php }?>
            </div>
        <?php } ?>
    <?php } ?>
</div>

<script>
//checked all role using select all
$(document).on("change", '.role_checkbox_all', function(event) { 
    checked_all(this,"role_checkbox");
});

</script>