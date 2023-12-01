<?php include('function/function.php'); 
if(isset($_POST['recover'])){
  if(isset($_POST['email'])){
	$uemail = $_POST['email'];
	record_set('admin',"SELECT id FROM  manage_users WHERE email='".$uemail."' AND cstatus='1'");   
		if($totalRows_admin>0){
			 $row_user_admin=mysqli_fetch_assoc($admin);
			 $forget_key =random_code(5);
			$user_data_update = array(
				"forget_key" => $forget_key
			);	
			$update = dbRowUpdate('manage_users', $user_data_update, 'where id='.$row_user_admin['id']);
			if($update){
				forgot_password_otp($uemail,$forget_key);
				reDirect('forget-password.php?action=enter-otp');
			}else{
				alert($msg_some_error);
			}
		}
		else{
			alert('Invalid email ID');
	    }
	}else{
		alert('Please fill the fields');
	}
}
if(isset($_POST['otp'])){
  if(isset($_POST['otpval'])){
	record_set('admin',"SELECT id FROM  manage_users WHERE forget_key='".$_POST['otpval']."' AND cstatus='1'");   
		if($totalRows_admin>0){
			reDirect('forget-password.php?action=enter-password&otp='.$_POST['otpval']);
		}else{
			alert('Invalid OTP');
	  }
	}else{
		alert('Please fill the fields');
	}
}
if(isset($_POST['reset_password'])){
  if(isset($_POST['password']) && isset($_POST['cpassword'])){
	  if($_POST['password']==$_POST['cpassword']){
		  record_set('admin',"SELECT id FROM  manage_users WHERE forget_key='".$_GET['otp']."' AND cstatus='1'");
		  if($totalRows_admin>0){
			  	$row_user_admin=mysqli_fetch_assoc($admin);
				$user_data_update = array(
					"forget_key" => '',
					"password"=>md5($_POST['password'])
				);	
				$update = dbRowUpdate('manage_users', $user_data_update, 'where id='.$row_user_admin['id']);
				if($update){
					alert('Password changed successfully.');
					reDirect('login.php');
				}else{
					alert($msg_some_error);
				}
		  }else{
			  alert('Request not found or expired. Please try again.');
		  }
		
	  }else{
		  alert('Password and Confirm Password is not same.');
	  }
	}else{
		alert('Please fill the fields');
	}
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Private Ambulance Service | Log in</title>
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

    <style type="text/css">
      .logheading{
          text-align: center;
          margin: 20px 0;
          font-size: 28px;
          font-weight: 600;
          color: #000;
      }
      .login-box .form-group .form-control-feedback{
        left: 0;
        background: #a020f0;
        color: #fff;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px 0 0 3px;
        padding: 5px 20px;
      }
      .login-box .form-group .form-control{
        padding: 10px 15px 10px 50px;
        height: 40px;
        border-radius: 3px;
      }
      .login-box form .btn-primary{
        background: #a020f0;
        border-color: #a020f0;
        color: #fff;
        text-transform: capitalize;
        font-weight: 500;
        transition: all 0.3s ease-in;
      }
      .login-box form .btn-primary:hover{
        background: #891acf;
        border-color: #891acf;
      }
      .grayText{
        color: #707070;
        font-size: 14px;
        font-weight: 400;
        text-decoration: underline;
        margin-top: 5px;
        display: inline-block;
      }
      .grayText:hover{
        color: #a020f0;
      }
      .customcheck{
        margin:0 0 15px 0;
        position: relative;
      }
      .linkText{
        display: inline-block;
        color: #2196F3;
        font-size: 14px;
      }
      .linkText:hover{
        text-decoration: underline;
        color: #2196F3;
      }
      .styled-checkbox {
        position: absolute;
        opacity: 0;
      }
      .styled-checkbox + label {
        position: relative;
        cursor: pointer;
        padding: 0;
        color: #707070;
        font-size: 14px;
        font-weight: 400; 
      }
      .styled-checkbox + label:before {
        content: "";
        margin-right: 5px;
        display: inline-block;
        vertical-align: text-top;
        width: 20px;
        height: 20px;
        background: white;
        border: 1px solid #ccc;
      }

      .styled-checkbox:checked + label:before {
        background: #a020f0;
        border: 1px solid #a020f0;
      }
      .styled-checkbox:disabled + label {
        color: #b8b8b8;
        cursor: auto;
      }
      .styled-checkbox:disabled + label:before {
        box-shadow: none;
        background: #ddd;
      }
      .styled-checkbox:checked + label:after {
        content: "";
        position: absolute;
        left: 5px;
        top: 9px;
        background: white;
        width: 2px;
        height: 2px;
        box-shadow: 2px 0 0 white, 4px 0 0 white, 4px -2px 0 white, 4px -4px 0 white, 4px -6px 0 white, 4px -8px 0 white;
        transform: rotate(45deg);
      }
      .login-box-body, .register-box-body{
        padding-bottom: 50px;
      }
      p.login-box-msg {
          font-size: 18px;
          color: black;
          font-weight: 700;
      }
    </style>

  </head>
  <body class="hold-transition login-page">
    <div class="login-box">
      <div class="login-box-body">
        <div align="center">
    		  <img src="<?=MAIN_LOGO?>" width="120">
    	  </div>
       
        <?=$msg?>
        <?php if($_GET['action']=='enter-otp'){ ?>
          <p class="login-box-msg">Enter your OTP sent on your email</p>
          <form action="#" method="post">
          <div class="form-group has-feedback">
            <input required type="text" name="otpval" maxlength="5" class="form-control" placeholder="OTP">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span> </div>
          
          <div class="row">
            <div class="col-xs-6">
              <a href="login.php"><strong>Login?</strong></a>
            </div>
            <div class="col-xs-6">
              <button type="submit" name="otp"  class="btn btn-success btn-block btn-flat">Validate OTP</button>
            </div>
            
          </div>
        </form>
        <?php }else if($_GET['action']=='enter-password'){ ?>
          <p class="login-box-msg">Enter your new password</p>
          <form method="post" action="" name="myForm">
            <div class="form-group has-feedback">
              <input type="password" name="password" class="form-control" placeholder="Password">
              <span class="glyphicon glyphicon-lock form-control-feedback"></span> 
            </div>
            <div class="form-group has-feedback">
              <input type="password" name="cpassword" class="form-control" placeholder="Confirm Password">
              <span class="glyphicon glyphicon-lock form-control-feedback"></span> 
            </div>
            <div class="row">
              
              <div class="col-xs-4" style="margin-top: 3px; color:#891acf;"><a style="color:#891acf;" href="./login.php"><strong>Login?</strong></a></div>
              <div class="col-xs-8 text-right">
              <input type="submit" name="reset_password" value="Confirm Password" class="btn btn-primary">
              </div><!-- /.col -->
            </div>
          </form>
        <?php }else{ ?>
          <p class="login-box-msg">Enter Your Email To Reset Password</p>
          <form method="post" action="" name="myForm">
            <div class="form-group has-feedback">
              <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
              <input type="text" name="email" class="form-control" placeholder="Email">
            </div>
            <div class="row">
              
              <div class="col-xs-4" style="margin-top: 3px; color:#891acf;"><a style="color:#891acf;" href="./login.php"><strong>Login?</strong></a></div>
              <div class="col-xs-8 text-right">
              <input type="submit" name="recover" value="Recover Password" class="btn btn-primary">
              </div><!-- /.col -->
            </div>
          </form>
        <?php } ?>

        <!-- /.social-auth-links -->

      </div><!-- /.login-box-body -->
      <center style="margin-top:20px;"><img  src="<?=getHomeUrl()?>upload_image/Data-Group-footer.png" alt="" width="150" /></center>
    </div><!-- /.login-box -->

    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <!-- iCheck -->
    <script src="plugins/iCheck/icheck.min.js"></script>
    <script>
      // $(function () {
      //   $('input').iCheck({
      //     checkboxClass: 'icheckbox_square-blue',
      //     radioClass: 'iradio_square-blue',
      //     increaseArea: '20%' // optional
      //   });
      // });
    </script>
  </body>
</html>
