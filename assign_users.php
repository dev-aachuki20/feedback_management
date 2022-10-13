<?php
    $allUserIds = array();
    if($_POST['admin_id'] OR $_POST['client_id']){
        //Update Deprtment
        if(count($_POST['admin_id'])>0 and count($_POST['client_id'])){
            $allUserIds = array_merge($_POST['admin_id'],$_POST['client_id']);
        }else if($_POST['admin_id']){
            $allUserIds =$_POST['admin_id'];
        }else if($_POST['client_id']){
            $allUserIds =$_POST['client_id'];
        }
        $row_data_user='';
        if($_GET['id']){
            $table_id = $_GET['id'];
        }else if($insert_value){
            $table_id = $insert_value;
        }
        if($_GET['page'] == 'add-department'){
            $table_name = 'department';
        }else if($_GET['page'] == 'add-location'){
            $table_name = 'location';
        }else if($_GET['page'] == 'add-group'){
            $table_name = 'group';
        } 
        //if inserted department location group
        if($table_id){
            $filter = "table_name = '$table_name' and table_id = $table_id";
            dbRowDelete('relation_table', $filter);
            foreach($allUserIds as $userId){
               
                $data = array(
                    "user_id"    => $userId,
                    "table_id"   => $table_id,
                    "table_name" => $table_name
                );
                $insert =  dbRowInsert("relation_table",$data);
            }
        }
        
    }
    $id =$_GET['id'];
    if($_GET['id']){
        $assign_data = get_assigned_data($id,$table_name);
    }else {
        $assign_data = array();
    }
    
?>
<div class="row">
    <div class="col-md-12 with-border">
         <div class="col-md-12"><h4>Assign Admins</h4></div>
         <input type="checkbox" style="margin-left: 15px;" onclick="checked_all(this,'adminCheckbox')" /><strong> Select All</strong><br/><br/>
        <?php foreach(getAdmin() as $key => $value){ ?>
            <div class="col-md-4">
                <input type="checkbox" id="admin_id_<?php echo $key ?>" class="userClass adminCheckbox" value="<?php echo $key; ?>" name="admin_id[<?php echo $key; ?>]" <?=(in_array($key,$assign_data)) ? 'checked ': '' ?>/> 
                <label for="admin_id_<?php echo $key; ?>">
                <?php echo $value ?>
                </label>
            </div>
        <?php } ?>
        <div class="row">
            <span class="col-md-12 user_error" style="color: red;font-weight: 700;margin-left: 17px;display:none;">Please choose atleast one option either from admin or client</span>    
        </div>
    </div>
    <div class="col-md-12 with-border">
        <div class="col-md-12"> <h4>Assign Clients</h4> </div>
        <input type="checkbox" style="margin-left: 15px;" onclick="checked_all(this,'clientCheckbox')" /><strong> Select All</strong><br/><br/>
        <?php
            if(($row_get_departments_id['client_ids'])){
            $client_saved = explode("|",$row_get_departments_id['client_ids']);
            }else{
            $client_saved = array();
            }
            foreach(getClient() as $key => $value){ ?>
                <div class="col-md-4">
                  <input type="checkbox" <?=(in_array($key,$assign_data) ? 'checked ':' ')?>  id="client_id_<?php echo $key ?>" class="userClass clientCheckbox" value="<?php echo $key; ?>" name="client_id[<?php echo $key; ?>]" /> 
                  <label for="client_id_<?php echo $key; ?>">
                  <?php echo $value ?>
                  </label>
                </div>
            <?php } ?>
            <div class="row">
                <span class="col-md-12 user_error" style="color: red;font-weight: 700;margin-left: 17px;display:none;">Please choose atleast one option either from admin or client</span>    
            </div>
    </div> 
</div>