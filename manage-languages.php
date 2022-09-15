<?php
if(!empty($_GET['id'])){
	record_set("get_language_id", "select * from languages where id='".$_GET['id']."'");
			
	$row_get_language_id = mysqli_fetch_assoc($get_language_id);
  
	}

if($_POST['update']){
		$data =  array(
						
						"name"=> $_POST['name'],
						"iso_code"=> $_POST['iso_code'],
						"your_first_name_text"=> $_POST['your_first_name_text'],
            "your_last_name_text" => $_POST['your_last_name_text'],
            "your_email_text" => $_POST['your_email_text'],
            "your_phone_number_text" => $_POST['your_phone_number_text'],
            "continue_next_step_text" => $_POST['continue_next_step_text'],
            "back_text" => $_POST['back_text'],
            "finish_text" => $_POST['finish_text'],
            "continue_text" => $_POST['continue_text'],
            "yes_text" => $_POST['yes_text'],
            "no_text" => $_POST['no_text'],
            "required_error_text" => $_POST['required_error_text'],
            "thank_you_text" => addslashes($_POST['thank_you_text']),
						"cstatus" => $_POST['status']
						
					);
		$updte=	dbRowUpdate("languages", $data, "where id=".$_GET['id']);
		
			if(!empty($updte)){
				$msg = "Language Updated Successfully";
			}else{
				$msg = "Language Not Updated Successfully";
			}
			reDirect("?page=manage-languages&id=".$_GET["id"]."&msg=".$msg);			
		
	}
?>
<?php 
	if(!empty($_POST['submit'])){
			
			$data =  array(
						
						"name"=> $_POST['name'],
						"iso_code"=> $_POST['iso_code'],
            "your_first_name_text"=> $_POST['your_first_name_text'],
            "your_last_name_text" => $_POST['your_last_name_text'],
            "your_email_text" => $_POST['your_email_text'],
            "your_phone_number_text" => $_POST['your_phone_number_text'],
            "continue_next_step_text" => $_POST['continue_next_step_text'],
            "back_text" => $_POST['back_text'],
            "finish_text" => $_POST['finish_text'],
            "continue_text" => $_POST['continue_text'],
            "yes_text" => $_POST['yes_text'],
            "no_text" => $_POST['no_text'],
            "required_error_text" => $_POST['required_error_text'],
            "thank_you_text" => addslashes($_POST['thank_you_text']),
						'cip'=>ipAddress(),
            "cstatus" => $_POST['status'],
						'cby'=>$_SESSION['user_id'],
						'cdate'=>date("Y-m-d H:i:s")
					);
					
			$insert_value =  dbRowInsert("languages",$data);
	
			if(!empty($insert_value )){	
        record_set("addcol_location", "ALTER TABLE locations ADD name_".$_POST['iso_code']." VARCHAR( 255 ) after name");

        record_set("addcol_department", "ALTER TABLE departments ADD name_".$_POST['iso_code']." VARCHAR( 255 ) after name");

        record_set("addcol_school", "ALTER TABLE schools ADD name_".$_POST['iso_code']." TEXT COLLATE utf8_general_ci after name");

        record_set("addcol_question", "ALTER TABLE questions ADD question_".$_POST['iso_code']." VARCHAR(255) COLLATE utf8_general_ci after question");

        record_set("addcol_question_details", "ALTER TABLE questions_detail ADD description_".$_POST['iso_code']." VARCHAR(255) COLLATE utf8_general_ci after description");

        record_set("addcol_survey", "ALTER TABLE surveys ADD name_".$_POST['iso_code']." VARCHAR( 255 ) COLLATE utf8_general_ci after name");

        record_set("addcol_survey", "ALTER TABLE surveys_steps ADD step_title_".$_POST['iso_code']." VARCHAR( 255 ) COLLATE utf8_general_ci after step_title");

				$msg = "Data Added Successfully";
			}else{
				$msg = "Some Error Occourd. Please try again..";
			}
			reDirect("?page=manage-languages&msg=".$msg);		
			
		}
?>
<section class="content-header">
  <h1> Add Language </h1>
</section>
<section class="content">
  <div class="box box-danger">
        
        <div class="box-body">
          <form action="" method="post" enctype="multipart/form-data">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Name</label>
                  <input type="text" class="form-control" name="name" id="name" value="<?php echo $row_get_language_id['name'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>ISO Code</label>
                
                  <input type="text" class="form-control" name="iso_code" id="iso_code" value="<?php echo $row_get_language_id['iso_code'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Your First Name Text</label>
                  <input type="text" class="form-control" name="your_first_name_text" id="your_first_name_text" value="<?php echo $row_get_language_id['your_first_name_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Your Last Name Text</label>
                  <input type="text" class="form-control" name="your_last_name_text" id="your_last_name_text" value="<?php echo $row_get_language_id['your_last_name_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Your Email Text</label>
                  <input type="text" class="form-control" name="your_email_text" id="your_email_text" value="<?php echo $row_get_language_id['your_email_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Your Phone Number Text</label>
                  <input type="text" class="form-control" name="your_phone_number_text" id="your_phone_number_text" value="<?php echo $row_get_language_id['your_phone_number_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Continue Next Step Text</label>
                  <input type="text" class="form-control" name="continue_next_step_text" id="continue_next_step_text" value="<?php echo $row_get_language_id['continue_next_step_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Back Text</label>
                  <input type="text" class="form-control" name="back_text" id="back_text" value="<?php echo $row_get_language_id['back_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Finish Text</label>
                  <input type="text" class="form-control" name="finish_text" id="finish_text" value="<?php echo $row_get_language_id['finish_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Continue Text</label>
                  <input type="text" class="form-control" name="continue_text" id="continue_text" value="<?php echo $row_get_language_id['continue_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Yes Text</label>
                  <input type="text" class="form-control" name="yes_text" id="yes_text" value="<?php echo $row_get_language_id['yes_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>No Text</label>
                  <input type="text" class="form-control" name="no_text" id="no_text" value="<?php echo $row_get_language_id['no_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Required Error Text</label>
                  <input type="text" class="form-control" name="required_error_text" id="required_error_text" value="<?php echo $row_get_language_id['required_error_text'];?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Thank You Text</label>
                  <textarea class="form-control" name="thank_you_text" id="thank_you_text" required = "required"><?php echo $row_get_language_id['thank_you_text'];?></textarea>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Status</label>
                  <select class="form-control" name="status">
                  	<?php 
                      foreach(status() as $key => $value){
                    ?>
                    <option <?php if($row_get_language_id['cstatus']==$key){?> selected="selected"<?php }?>value="<?php echo $key; ?>"><?php echo $value; ?></option>                    
                    <?php }?>
                  </select>
                </div>
              </div>
            </div>
            <div class="text-right">
            	<?php
                	if(empty($_GET['id'])){
				?>
 				<input type="Submit" class="btn btn-primary" value="Create" name="submit" id="submit"/>
                <?php }else{?>                
                <input type="Submit" class="btn btn-primary" value="Update" name="update"/>
				<?php }?>
                <span class="text-denger"><?php echo $_GET['msg']; ?></span>
            </div>
          </form>
        </div>
      
  </div>

<div class="box box-danger">
    <div class="row">
      <div class="col-md-12">
      <div class="box-header with-border">
              <h3 class="box-title">View Language</h3>
            </div>
        <div class="box-body">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Name</th>
                <th>ISO Code</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php 
			
				record_set("get_languages", "select * from languages where cby='".$_SESSION['user_id']."' order by cdate desc");				
				while($row_get_languages = mysqli_fetch_assoc($get_languages)){
			?>
            
              <tr>
                <td><?php echo $row_get_languages['name'];?></td>
                <td><?php echo $row_get_languages['iso_code'];?></td>
                <td>
                    <?php 
                        if($row_get_languages['cstatus']==1){
                    ?>		
                        <span class="label label-success">Active</span>
                    <?php 	}else{?>	
                        <span class="label label-danger">Deactive</span>
                    <?php }?>
                </td>
                <td><a class="btn btn-xs btn-info" href="?page=manage-languages&id=<?php echo $row_get_languages['id'];?>">Edit</a></td>
              </tr>

             <?php }?>
            </tbody>
            <tfoot>
              <tr>
                <th>Name</th>
                <th>ISO Code</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
<script src="plugins/datatables/jquery.dataTables.min.js"></script> 
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script> 
<script>
      $(function () {
        $("#example1").DataTable({"ordering": false});
      });
    </script>
<script type="application/javascript">
$(document).ready(function(){
	$('.form-control').click(function(){
		$(this).css('border-color', '#ccc');
	});
		
	$('#submit').click(function(){
			
        var name = $("#name").val();
        var isoCode = $("#iso_code").val();
        
        if(name==''){
            document.getElementById('name').style.borderColor = "#ff0000";	
            alert("Name is required");
            document.getElementById('name').focus();
            return false;					
        }

        if(isoCode==''){
            document.getElementById('iso_code').style.borderColor = "#ff0000";	
            alert("ISO Code is required");
            document.getElementById('iso_code').focus();
            return false;					
        }
			
	});	
		
});
</script>