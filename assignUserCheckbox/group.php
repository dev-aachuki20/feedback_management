

  <div class="col-md-12 surveyCheck" style="padding:0px;">
        <div class="col-md-12 with-border">
          <h4>Assign Group</h4>
          <input type="checkbox" class="group_checkbox_all" /><strong> Select All</strong><br/><br/>
        </div>
        <?php
          $groupName = getGroup(); 
          if(isset($_GET['id'])){
            $group_id = get_assigned_user_data($_GET['id'],'group');
          }
          if($_SESSION['user_type']>2){
     
            $assignGroupId = get_assigned_user_data($_SESSION['user_id'],'group');
            if(count($assignGroupId)>0){
              $array =[];
              foreach($groupName as $key=> $value){
                if(in_array($key,$assignGroupId)){
                  $array[$key] =$value;
                }
              }
              $groupName = $array;
            }else{
              $groupName = []; 
            }
          }
         
          foreach($groupName as $key => $value){ 
          $groupId    = $key;
          $groupName  = $value;
          ?>
          <div class="col-md-4">
              <input type="checkbox" <?=(in_array($groupId,$group_id) ? 'checked ':' ')?> id="groupids<?php echo $groupId ?>" class="group_checkbox" value="<?php echo $groupId; ?>" name="groupids[<?php echo $groupId; ?>]" /> 
              <label for="groupids<?php echo $groupId; ?>">
              <?php echo $groupName ?>
              </label>
          </div>
        <?php }?>
  </div>

  <script>
    // for group checkbox
    $(document).on("change", '.group_checkbox', function(event) { 
      select_group();
    });

    // //checked all group using select all
    $(document).on("change", '.group_checkbox_all', function(event) { 
      checked_all(this,"group_checkbox");
      select_group();
    });

    // function select_group(){
    //   var checkedArray=[];
    //   $(".group_checkbox:checkbox:checked").each(function() {
    //     checkedArray.push($(this).val());
    //   });
    //   var filteredArray = checkedArray.filter(e => e !== 'on')
    //   ajax_for_checkbox(filteredArray,'add_user_group_assign')
    // }
  </script>