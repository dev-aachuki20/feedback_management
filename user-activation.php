<?php 
include('function/function.php'); 
include('function/get_data_function.php');
$id="";
$nm="";
$email="";
if(isset($_GET['email']) && isset($_GET['key'])){
  $email=$uemail = $_GET['email'];
  $akey = $_GET['key'];
  
  record_set('admin',"SELECT * FROM  manage_users WHERE email='".$uemail."' AND activation_key='".$akey."' AND cstatus='2'");   
    if($totalRows_admin>0){
      $row_user_admin=mysqli_fetch_assoc($admin);
      $id=$row_user_admin['id'];
      $nm=$row_user_admin['name'];
      //echo "<script>location.href='login.php';<\script>";
    }
    else{
      echo "<script>location.href='login.php';</script>";
	  }
}
else{
  echo "<script>location.href='login.php';</script>";
}
if(isset($_REQUEST['set_pass'])){
  $pass=md5($_POST['password']);
  $email=$_POST['uid'];
  $update = dbRowUpdate('manage_users',array("password"=>$pass,"activation_key"=>'',"cstatus"=>'1'),'where id='.$email);
  //send_activation_email($_POST['email']);
  echo "<script>location.href='login.php';</script>";
}
?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>HATS PATIENT SURVEY LOGIN | Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/iCheck/square/blue.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="hold-transition login-page">
  
    <div class="login-box">
            
      <div class="login-logo">
        <a href="#">Set Password </a>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
          	<div align="center">
    		<img src="<?=MAIN_LOGO?>" width="200">
    	</div>
        <p class="login-box-msg">Sign in to start your session</p>
        <?=$msg?>
          <form action="" method="post" name="myForm" onsubmit="return validateForm()">
              <div class="form-group has-feedback">
                <input type="password" name="password" class="form-control" placeholder="Enter New Password">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span> 
              </div>
              <div class="form-group has-feedback">
                <input type="hidden" name="uid" value="<?php echo $id; ?>">
                <input type="hidden" name="email" value="<?php echo $email; ?>">
                <input type="password" name="confirmpassword" class="form-control" placeholder="Confirm Password">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span> 
              </div>
              <div class="row">
                <div class="col-xs-4">
                  <button type="submit" name="set_pass"  class="btn btn-primary btn-block btn-flat">Submit</button>
                </div>
                
              </div>
          </form>

        <!-- /.social-auth-links -->

      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->

    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <!-- iCheck -->
    <script src="plugins/iCheck/icheck.min.js"></script>
    <script>
      $(function () {
        $('input').iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          increaseArea: '20%' // optional
        });
      });
      // $(".view_password").click(function(){
      //   alert('amit');
      // })
    </script>
<center>Powered by Datagroup Solutions<br><img  src="<?=getHomeUrl()?>upload_image/Data-Group-footer.png" alt="" width="200" height="36" /></center>
</body>
</html>
