<?php 
include('function/function.php'); 
include('function/get_data_function.php');

if(isset($_POST['login'])){
  if(isset($_POST['email']) && isset($_POST['password']) !=''){
	$uemail = $_POST['email'];
	$upassword = $_POST['password'];
	record_set('admin',"SELECT * FROM clients WHERE email='".$uemail."' AND password='".md5($upassword)."' AND
	 cstatus='1'");
	    
		if(mysqli_num_rows($admin)>0){
			$row_user_admin=mysqli_fetch_array($admin,MYSQLI_ASSOC);
			foreach($row_user_admin as $key=>$val){
        if($key !='locationid'){
          $_SESSION['user_'.$key] =$val;
        }
			}
      
			$_SESSION['user_type'] = 3;
      $location =  get_filter_data_by_user('locations');
      $arr = array();
      foreach($location as $loc){
        $arr[] = $loc['id'];
      }
      $_SESSION['user_locationid'] = implode(',', $arr);
			$mess='Admin Login Successful';
	    	reDirect('index.php?mess='.$mess);
		}else{
			echo "<script> alert('Email or password is not correct');</script>";
	    }
	}else{
		echo "<script> alert('Please fill the fields');</script>";
	}
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
        <a href="#"><b>User</b>LOGIN</a>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
          	<div align="center">
    		<img src="upload_image/logo.png" width="200">
    	</div>
        <p class="login-box-msg">Sign in to start your session</p>
        <form method="post" action="" name="myForm">
          <div class="form-group has-feedback">
            <input type="text" name="email" class="form-control" placeholder="Email">
            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
          </div>
          <div class="form-group has-feedback">
            <input type="password" name="password" class="form-control" placeholder="Password">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          </div>
          <div class="row">
            <div class="col-xs-8">
              &nbsp;
            </div><!-- /.col -->
            <div class="col-xs-4">
              <input type="submit" name="login" value="Sign In" class="btn btn-primary btn-block btn-flat">
            </div><!-- /.col -->
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
    </script>
<center>Powered by Datagroup Solutions<br><img  src="https://www.datagroupsolutions.com/wp-content/uploads/2020/11/Data-Group-Solutions-survey.png" alt="" width="200" height="36" /></center>
</body>
</html>
