<?php 
include('function/function.php');
include('function/get_data_function.php');
//print_r($_SESSION).'Harish'; //exit;
check_login();
$locationQueryAndCondition = "";
$locationQueryWhereCondition = "";
$locationDropDownCondition = "";
$locationJoinCondition = "";
$locationJoinWhereCondition = "";
$locationRecentContact="";
if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 2){
  if($_SESSION['user_locationid'] == 4){
    $locationQueryAndCondition = "and locationid in (select id from locations where cstatus=1)";
    $locationQueryWhereCondition = "where locationid in (select id from locations where cstatus=1)";
    $locationDropDownCondition = "";
    $locationJoinCondition = "and answers.locationid in (select id from locations where cstatus=1)";
    $locationJoinWhereCondition = "and answers.locationid in (select id from locations where cstatus=1)";
    $locationRecentContact= "locationid in (select id from locations where cstatus=1) and";
  }else{ 
    if($_SESSION['user_locationid']){
      $locationQueryAndCondition = "and locationid in (".$_SESSION['user_locationid'].")";
      $locationQueryWhereCondition = "where locationid in ('".$_SESSION['user_locationid']."')";
      $locationDropDownCondition = "and id in (".$_SESSION['user_locationid'].")";
      $locationJoinCondition = "and answers.locationid in (".$_SESSION['user_locationid'].")";
      $locationRecentContact= "locationid in (".$_SESSION['user_locationid'].") and";
    }
    $locationJoinWhereCondition = "and answers.locationid in (select id from locations where cstatus=1)";
  }
}
if(isset($_REQUEST['page'])){
	$page_heading = ucwords(str_replace("-"," ",$_REQUEST['page']));
	if($_REQUEST['page']=='logout'){
		$inc_page = 'logout.php';
	}else if($_REQUEST['page']=='login'){
  		$inc_page = 'login.php';
	}
	else if($_REQUEST['page']=='add_admin'){
  		$inc_page = 'add_admin.php';
	}
	else if($_REQUEST['page']=='view-admin'){
  		$inc_page = 'view-admin.php';
	}
	else if($_REQUEST['page']=='add-clients'){
  		$inc_page = 'add-clients.php';
	}
	else if($_REQUEST['page']=='view-clients'){
  		$inc_page = 'view-clients.php';
	}
	else if($_REQUEST['page']=='manage-department'){
  		$inc_page = 'manage-department.php';
	}
	else if($_REQUEST['page']=='manage-locations'){
  		$inc_page = 'manage-locations.php';
	}
	else if($_REQUEST['page']=='add-survey'){
  		$inc_page = 'add-survey.php';
	}
	else if($_REQUEST['page']=='view-survey'){
  		$inc_page = 'view-survey.php';
	}
	else if($_REQUEST['page']=='add-survey_questions'){
  		$inc_page = 'add-survey_questions.php';
	}
	else if($_REQUEST['page']=='edit-survey_questions'){
  		$inc_page = 'edit-survey_questions.php';
	}
	else if($_REQUEST['page']=='view-survey_questions'){
  		$inc_page = 'view-survey_questions.php';
	}
	else if($_REQUEST['page']=='manage-survey-reports'){
  		$inc_page = 'manage-survey-reports.php';
	}
	else if($_REQUEST['page']=='survey-form'){
  		$inc_page = 'survey-form.php';
	}
	else if($_REQUEST['page']=='view-report'){
  		$inc_page = 'view-report.php';
	}
	else if($_REQUEST['page']=='monthly-report'){
  		$inc_page = 'monthly-report.php';
	}
  else if($_REQUEST['page']=='view-statistics'){
      $inc_page = 'view-statistics.php';
  }
  else if($_REQUEST['page']=='survey-manage'){
    $inc_page = 'survey-manage.php';
  }
  else if($_REQUEST['page']=='manage-schools'){
    $inc_page = 'manage-schools.php';
  }
  else if($_REQUEST['page']=='manage-languages'){
    $inc_page = 'manage-languages.php';
  }
  else if($_REQUEST['page']=='editSurveyQuestion'){
    $inc_page = 'editSurveyQuestion.php';
  }
  else if($_REQUEST['page']=='report-statistics'){
    $inc_page = 'report-statistics.php';
  }
	else{				
		$inc_page = $_GET['page'].'.php';		
	}
}
else{
  $inc_page='home.php'; 
}
?>
<?php 
	record_set("get_image", "select photo from admin where id='".$_SESSION['user_id']."'");
	$row_get_image = mysqli_fetch_assoc($get_image);
	if(!empty($row_get_image['photo'])){
		$pimg = 'upload_image/'.$row_get_image['photo'];
	}else{
		$pimg = 'upload_image/survey-pngrepo-com.png';
	}
  ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>HATS feedback</title>
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<link rel="stylesheet" href="dist/css/AdminLTE.min.css">
<link rel="stylesheet" href="dist/css/my-style.css">
<link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
<link rel="stylesheet" href="plugins/iCheck/flat/blue.css">
<link rel="stylesheet" href="plugins/morris/morris.css">
<link rel="stylesheet" href="plugins/jvectormap/jquery-jvectormap-1.2.2.css">
<link rel="stylesheet" href="plugins/datepicker/datepicker3.css">
<link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">
<link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- amit -->
<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/5.0.7/sweetalert2.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/5.0.7/sweetalert2.min.css">
<!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<script src="plugins/jQuery/jQuery-2.1.4.min.js"></script> 
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script> 

<!-- Datatable CSS -->
<link href='https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css' rel='stylesheet' type='text/css'>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<header class="main-header"> <a href="" class="logo"> <img src="<?=baseUrl()?>hats-logo-survey50.png" width="138" height="45"></a>
    <nav class="navbar navbar-static-top" role="navigation"> <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"> <span class="sr-only">Toggle navigation</span> </a>
      <!-- <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li class="dropdown user user-menu"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> 
          
          <img src="<?php echo $pimg; ?>" class="user-image" alt="User Image"> <span class="hidden-xs"><?php echo ucfirst($_SESSION['admin_name']); ?></span> </a>
            <ul class="dropdown-menu">
              <li class="user-header"> <img src="<?php echo $pimg; ?>" class="img-circle" alt="User Image">
                <p><?php echo ucfirst($_SESSION['admin_name']);?></p>
              </li>
              <li class="user-footer">
                <div class="pull-right"> <a href="?page=logout" class="btn btn-default btn-flat">Sign out</a> </div>
              </li>
            </ul>
          </li>
        </ul>
      </div> -->
    </nav>
  </header>
  <aside class="main-sidebar">
    <section class="sidebar">
      <!-- <div class="user-panel">
        <div class="pull-left image"> <img src="<?php echo $pimg; ?>" class="img-circle" alt="User Image"s> </div>
        <div class="pull-left info">
          <p><?php echo ucfirst($_SESSION['admin_name']);?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a> </div>
      </div> -->
      <!-- sidebar start -->
      <!-- <ul class="sidebar-menu">
        <li class="treeview profile-active"><a href="?page=add_admin&amp;id=1"><i class="fa fa-user"></i> <span>Admin</span></a></li>
        <li><a href="index.php"><i class="fa fa-dashboard"></i><span>Dashboard</span></a></li>
        <?php if($_SESSION['user_type']==1){?>
        <li><a href="?page=view-admin"><i class="fa fa-user"></i><span>Admin</span></a></li>
        <?php //}?>
        
         <?php //if($_SESSION['user_type']==1){?>
        <li><a href="?page=view-clients"><i class="fa fa-user"></i><span>Managers</span></a></li>
        <li><a href="?page=manage-languages"><i class="fa fa-language"></i><span>Languages</span></a></li>
        <li><a href="?page=manage-locations"><i class="fa fa-sitemap"></i><span>Locations</span></a></li>
        <li><a href="?page=manage-department"><i class="fa fa-sitemap"></i><span>Department</span></a></li>
        <li><a href="?page=manage-schools"><i class="fa fa-sitemap"></i><span>Schools</span></a></li>
        
        <li><a href="?page=view-survey"> <i class="fa fa-comments"></i> <span>Manage Survey</span></a></li>
        <?php }?>
        <li><a href="?page=view-report"> <i class="fa fa-list"></i> <span>View Report</span></a></li>
        <li><a href="?page=monthly-report"> <i class="fa fa-file-pdf-o"></i> <span>Monthly Report</span></a>
        </li>
        <li><a href="?page=report-statistics"> <i class="fa fa-list"></i> <span>View Report Statistics</span></a></li>
        <li><a href="?page=view-statistics"> <i class="fa fa-list"></i> <span>View Statistics</span></a></li>
        <li><a href="?page=logout"> <i class="fa fa-list"></i> <span>View Logout</span></a></li>
      </ul> -->
      <!-- sidebar end -->
      <ul class="sidebar-menu">
      <?php 
        include('permission.php');
        include('sidebar.php');
      ?>
      </ul>
    </section>
    <div class="sidebar-footer">
      <li class="help-question "> <a href="#" > <i class="fa fa-question"></i> <span>Help</span></a> </li>
    </div>
  </aside>
  <div class="content-wrapper">
      <?php include($inc_page);?>
  </div>
   <?php include('footer.php') ?>
  <div class="control-sidebar-bg"></div>
</div>
  <script>
    $.widget.bridge('uibutton', $.ui.button);
  </script> 
<script src="bootstrap/js/bootstrap.min.js"></script> 


<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script> 
<script src="plugins/morris/morris.min.js"></script> 

<script src="plugins/sparkline/jquery.sparkline.min.js"></script> 
<script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script> 
<script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script> 
<script src="plugins/knob/jquery.knob.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script> 
<script src="plugins/daterangepicker/daterangepicker.js"></script> 
<script src="plugins/datepicker/bootstrap-datepicker.js"></script> 
<script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script> 
<script src="plugins/slimScroll/jquery.slimscroll.min.js"></script> 
<script src="plugins/fastclick/fastclick.min.js"></script> 
<script src="dist/js/app.min.js"></script> 

<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.js"></script>
<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/additional-methods.js"></script>

<!-- Datatable JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<!--<script src="dist/js/pages/dashboard.js"></script> 

<script src="dist/js/demo.js"></script>-->

<script>
  $(document).ready(function(){
    // multiple department select
    $(".multiple-select").select2({
    placeholder: "Select option",
    allowClear: true
    });
  })

function checked_all(source,element) {
  var checkboxes = document.querySelectorAll('.'+ element);
  for (var i = 0; i < checkboxes.length; i++) {
    if (checkboxes[i] != source)
    checkboxes[i].checked = source.checked;
  }
}

// alert message to delete file

function delete_data(table,id){
    swal({
		title: 'Are you sure?',
		text: "It will permanently deleted !",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, delete it!'
    }).then(function() {
      /* ajax to delete file start*/
        $.ajax({
          type: "POST",
          url: '<?=baseUrl()?>ajax/common_file.php',
          data: {
            deleteid: id,
            db_table_name:table,
            mode:'delete'
          }, 
            success: function(response){ 
                if(response==1){
                  swal({
                    title: "Deleted!",
                    text: "Your file has been deleted.",
                    type: "success",
                    showConfirmButton: true
                  }).then(function() {
                    location.reload();
                  })
                }else {
                  swal({
                    title: "Deleted!",
                    text: "Sorry File Not Deleted",
                    type: "error",
                    showConfirmButton: true
                  }).then(function() { location.reload();})
                }
            }
        });
	})
}

</script>
</body>
</html>
