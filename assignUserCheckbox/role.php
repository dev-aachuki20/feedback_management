<div class="col-md-12 departmentCheck" style="padding:0px;">
    <div class="col-md-12 with-border">
        <h4>Assign Roles</h4>
        <input type="checkbox" onclick="checked_all(this,'role_checkbox')" /><strong> Select All</strong><br/><br/>
    </div>
    <?php
     $roleName = getRole(); 
    if(isset($_GET['id'])){
    $role_id = get_assigned_user_data($_GET['id'],'role');
    }
    if($_SESSION['user_type']>2){
        $assignRoleId = get_assigned_user_data($_SESSION['user_id'],'role');
        if(count($assignRoleId)>0){
            $array =[];
            foreach($roleName as $key=> $value){
            if(in_array($key,$assignRoleId)){
                $array[$key] =$value;
            }
            }
            $roleName = $array;
        }else{
            $roleName = []; 
        }
    }
    foreach($roleName as $key => $value){ 
    $roleId  = $key;
    $roleName = $value; ?>
    <div class="col-md-4">
        <input type="checkbox" <?=(in_array($roleId,$role_id) ? 'checked ':' ')?> id="roleids<?php echo $roleId ?>" class="role_checkbox" value="<?php echo $roleId; ?>" name="roleids[<?php echo $roleId; ?>]"/> 
        <label for="roleids<?php echo $roleId; ?>">
        <?php echo $roleName ?>
        </label>
    </div>
    <?php } ?> 

</div>

<script>
//checked all role using select all
$(document).on("change", '.role_checkbox_all', function(event) { 
    checked_all(this,"role_checkbox");
});

</script>