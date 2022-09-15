<?php
if(!empty($_GET['id'])){
	record_set("get_schools_id", "select * from schools where id='".$_GET['id']."'");
			
	$row_get_schools_id = mysqli_fetch_assoc($get_schools_id);
	}
//Start update	
if($_POST['update']){
	
		$dataCol =  array(
						"name"=> $_POST['name'],
						"locationid"=> $_POST['locationid'],
						"cstatus" => $_POST['status']
					);

    $lang_col=array();
    record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
    while($row_get_language = mysqli_fetch_assoc($get_language)){	
      if($row_get_language['id'] !=1){
          $lang_col["name_".$row_get_language['iso_code']] = $_POST['name_'.$row_get_language['iso_code']];
      }
    }

    $data = array_merge($dataCol,$lang_col);

		$updte=	dbRowUpdate("schools", $data, "where id=".$_GET['id']);
		
			if(!empty($updte)){
				$msg = "School Updated Successfully";
			}else{
				$msg = "School Not Updated Successfully";
			}
			reDirect("?page=manage-schools&id=".$_GET["id"]."&msg=".$msg);			
}
//End update	
?>
<?php 
  //Start insert
	if(!empty($_POST['submit'])){
			
			$dataCol =  array(
						'name'=> $_POST['name'],
						"locationid"=> $_POST['locationid'],
						'cip'=>ipAddress(),
            'cstatus' => $_POST['status'],
						'cby'=>$_SESSION['user_id'],
						'cdate'=>date("Y-m-d H:i:s")
					);
      //merge column accordind to column
      $lang_col=array();
      record_set("get_language", "select * from languages where cby='".$_SESSION['user_id']."'");				
      while($row_get_language = mysqli_fetch_assoc($get_language)){	
        if($row_get_language['id'] !=1){
            $lang_col["name_".$row_get_language['iso_code']] = $_POST['name_'.$row_get_language['iso_code']];
        }
      }
  
      $data = array_merge($dataCol,$lang_col);

			$insert_value =  dbRowInsert("schools",$data);
	
			if(!empty($insert_value )){	
				$msg = "Data Added Successfully";
			}else{
				$msg = "Some Error Occourd. Please try again..";
			}
			reDirect("?page=manage-schools&msg=".$msg);		
		}
    //End insert
?>
<section class="content-header">
  <h1> Add School</h1>
</section>
<section class="content">
  <div class="box box-danger">
    <div class="box-body">
      <form action="" method="post" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Name</label>
              <input type="text" class="form-control" name="name" id="name" value="<?php echo $row_get_schools_id['name'];?>"/>
            </div>
          </div>
          <!-- Start name according to language -->
          <?php
            record_set("get_language", "select * from languages");				
            while($row_get_language = mysqli_fetch_assoc($get_language)){
              if($row_get_language['iso_code'] != 'en'){
          ?>
          <div class="col-md-4">
            <div class="form-group">
              <label>Name - <?=$row_get_language['name']?></label>
              <input type="text" class="form-control" name="name_<?=$row_get_language['iso_code']?>" id="name_<?=$row_get_language['iso_code']?>" value="<?php echo $row_get_schools_id['name_'.$row_get_language['iso_code']];?>"/>
            </div>
          </div>
          <?php
              }
            }
          ?>
          <!-- End name according to language -->
          <div class="col-md-4">
            <div class="form-group">
              <label>Location</label>
              <select class="form-control" name="locationid" id="locationid">
                <option value="0">Select location</option>
                <?php 
                    record_set("get_locations", "select * from locations");
                    while($row_get_locations = mysqli_fetch_assoc($get_locations)){
                ?>
                    <option <?php echo ($row_get_locations['id'] == $row_get_schools_id['locationid'])?'selected':'';?> value="<?php echo $row_get_locations['id']; ?>"><?php echo $row_get_locations['name']; ?></option>  
                <?php        
                    }
                ?>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Status</label>
              <select class="form-control" name="status">
                <?php 
                  foreach(status() as $key => $value){
                ?>
                  <option <?php if($row_get_schools_id['cstatus']==$key){?> selected="selected"<?php }?>value="<?php echo $key; ?>"><?php echo $value; ?></option>                    
                <?php }?>
              </select>
            </div>
          </div>
        </div>
        <!-- End row -->
        <!-- Start submit button -->
        <div class="text-right">
          <?php if(empty($_GET['id'])){ ?>
              <input type="Submit" class="btn btn-primary" value="Create" name="submit" id="submit"/>
            <?php }else{?>                
              <input type="Submit" class="btn btn-primary" value="Update" name="update"/>
            <?php }?>
              <span class="text-denger"><?php echo $_GET['msg']; ?></span>
        </div>
        <!-- End submit button -->
      </form>
    </div>
  </div>
  <!-- Start school table -->
  <div class="box box-danger">
    <div class="row">
      <div class="col-md-12">
      <div class="box-header with-border">
          <h3 class="box-title">View School</h3>
      </div>
        <div class="box-body">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Name</th>
                <th>Location</th>
                <th>Status</th>
                <th>Action</th>
               </tr>
            </thead>
            <tbody>
            <?php 
              record_set("get_schools", "select * from schools where cby='".$_SESSION['user_id']."' order by cdate desc");				
              while($row_get_schools = mysqli_fetch_assoc($get_schools)){
            ?>
              <tr>
                <td><?php echo $row_get_schools['name'];?></td>
                <td>
                    <?php
                        record_set("get_location_id", "select * from locations where id='".$row_get_schools['locationid']."'");
                        $row_get_location_id = mysqli_fetch_assoc($get_location_id);
                        echo $row_get_location_id['name'];
                    ?>
                </td>
                <td>
                  <?php if($row_get_schools['cstatus']==1){ ?>		
                          <span class="label label-success">Active</span>
                  <?php }else{?>	
                        <span class="label label-danger">Deactive</span>
                  <?php }?>
                </td>
                <td>
                  <a class="btn btn-xs btn-info" href="?page=manage-schools&id=<?php echo $row_get_schools['id'];?>">Edit</a>
                </td>
              </tr>
            <?php }?>
            </tbody>
            <tfoot>
              <tr>
                <th>Name</th>
                <th>Location</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- End school table -->
</section>

<link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
<script src="plugins/datatables/jquery.dataTables.min.js"></script> 
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script> 
<script>
      $(function () {
        $("#example1").DataTable({"ordering": false});
        $('#example2').DataTable({
          "paging": true,
          "lengthChange": false,
          "searching": false,
          "ordering": false,
          "info": true,
          "autoWidth": false
        });
      });
    </script>
<script type="application/javascript">
  $(document).ready(function(){
	  $('.form-control').click(function(){
		  $(this).css('border-color', '#ccc');
		});
		
	  $('#submit').click(function(){
			
			var name = $("#name").val();
			if(name==''){
				document.getElementById('name').style.borderColor = "#ff0000";	
				alert("Name is required");
				document.getElementById('name').focus();
				return false;					
				}
			
		});	
		
	});
</script>