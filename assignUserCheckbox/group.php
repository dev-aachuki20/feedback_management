

  <div class="col-md-12 surveyCheck" style="padding:0px;">
      <?php 
      if(isset($_GET['id']) and count($groupid_array)>0){ 
        $groupByUsers = get_filter_data_by_user('groups'); ?>
        <div class="col-md-12 with-border">
          <h4>Assign Group</h4>
          <input type="checkbox" class="group_checkbox" /><strong> Select All</strong><br/><br/>
        </div>
        <?php
        $group_id = get_assigned_user_data($_GET['id'],'group');
        $group_id = implode(',',$group_id);
        if($group_id){
          $filterSurvey = " where id IN($group_id)";
        }else {
          $filterSurvey = " where id IN(0)";
        }

        record_set("get_group_id", "select * from groups $filterSurvey");
        $locationid_array= array();
        while($row_get_group_id = mysqli_fetch_assoc($get_group_id)){
          $group_id_saved[]   = $row_get_group_id['id'];
          // get location assign to given group
          $locidByGroup = $row_get_group_id['location_id'];
          $locidByGroups = explode(',',$locidByGroup);
          foreach($locidByGroups as $loc){
            $locationid_array[$loc] = $loc;
          }
        ///end
        }
        
          $groupid_array_explode = implode(',',$groupid_array);
          $groupName = get_data_by_id('groups',$groupid_array_explode);
          foreach($groupName as $key => $value){ 
          $groupId    = $key;
          $groupName  = $value;
          ?>
          <div class="col-md-4">
              <input type="checkbox" <?=(in_array($groupId,$group_id_saved) ? 'checked ':' ')?> id="locationids<?php echo $groupId ?>" class="loc_checkbox" value="<?php echo $groupId; ?>" name="locationids[<?php echo $groupId; ?>]" /> 
              <label for="locationids<?php echo $groupId; ?>">
              <?php echo $groupName ?>
              </label>
          </div>
        <?php }
      } 
      // for create report page
      else if($_GET['page'] =='create-report' and !empty($_GET['viewid'])){?>
        <!-- assign group -->
        <?php if(count($groupByUsers)>0) { ?>
          <div class="row groupCheck">         
              <div class="col-md-12 with-border">
                  <h4>Assign Group</h4>
                  <input <?=$checkDisable?> type="checkbox" onclick="checked_all(this,'group_checkbox')" /><strong> Select All</strong><br/><br/>
              </div>
              <?php 
              foreach($groupByUsers as $groupData){ 
                  $groupId    = $groupData['id'];
                  $groupName  = $groupData['name'];
              ?>
                  <div class="col-md-4">
                      <input <?=$checkDisable?> type="checkbox" id="groupids<?php echo $groupId ?>" class="group_checkbox" <?=(in_array($groupId,$template_grp)) ? 'checked':''?> value="<?php echo $groupId; ?>" name="groupids[<?php echo $groupId; ?>]" /> 
                      
                      <label for="groupids<?php echo $groupId; ?>">
                      <?php echo $groupName ?>
                      </label>
                  </div>
              <?php } ?>  
          </div>
        <?php } ?>
      <?php }?>
  </div>

  <script>
    // for group checkbox
    $(document).on("change", '.group_checkbox', function(event) { 
      select_group();
    });

    //checked all location using select all
    $(document).on("change", '.group_checkbox_all', function(event) { 
      checked_all(this,"group_checkbox");
      select_group();
    });

    function select_group(){
      var checkedArray=[];
      $(".group_checkbox:checkbox:checked").each(function() {
        checkedArray.push($(this).val());
      });
      var filteredArray = checkedArray.filter(e => e !== 'on')
      ajax_for_checkbox(filteredArray,'add_user_group_assign')
    }
  </script>