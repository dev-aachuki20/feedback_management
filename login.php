<?php 
include('function/function.php'); 
include('function/get_data_function.php');
$msg = '';
if(isset($_POST['login'])){
  if(isset($_POST['email']) && isset($_POST['password']) !=''){
	$uemail = $_POST['email'];
	$upassword = $_POST['password'];
	record_set('user',"SELECT * FROM manage_users WHERE email='".$uemail."' AND password='".md5($upassword)."' AND id!=1 AND cstatus='1'");
	    
		if(mysqli_num_rows($user)>0){
			$row_user_user=mysqli_fetch_array($user,MYSQLI_ASSOC);
			foreach($row_user_user as $key=>$val){
        if($key == 'user_type'){
          $_SESSION['user_type'] =$val;
        }else {
          $_SESSION['user_'.$key] =$val;
        }
			}
      $location =  get_filter_data_by_user($loggedIn_user_id,'locations');
      $arr = array();
      foreach($location as $loc){
        $arr[] = $loc['id'];
      }
      $_SESSION['user_locationid'] = implode(',', $arr);
			$mess='Admin Login Successful';

        if (isset($_SESSION['REQUESTED_URI'])) {
          reDirect($_SESSION['REQUESTED_URI']);
          unset($_SESSION['REQUESTED_URI']);
        }

	    	reDirect('index.php?mess='.$mess);
		}else{
      $msg = '<div style="background: red;color: #fff;text-align: center;margin: 20px 0px;padding: 5px;" role="alert">Email or password is not correct</div>';
			//echo "<script> alert('Email or password is not correct');</script>";
	    }
	}else{
    $msg = '<div style="background: red;color: #fff;text-align: center;margin: 20px 0px;padding: 5px;" role="alert">Please fill the fields</div>';
		//echo "<script> alert('Please fill the fields');</script>";
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
    <!-- <link rel="stylesheet" href="plugins/iCheck/square/blue.css"> -->

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

    </style>
  </head>
  <body class="hold-transition login-page">
  
    <div class="login-box">
            
      <div class="login-box-body">
        <div align="center">
    		  <img src="<?=MAIN_LOGO?>" width="120">
    	  </div>
        <h4 class="logheading">LOGIN</h4>
        <?=$msg?>
        <form method="post" action="" name="myForm">
          <div class="form-group has-feedback">
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
            <input type="text" name="email" class="form-control" placeholder="Email">
          </div>
          <div class="form-group has-feedback">
            <span class="glyphicon glyphicon-lock form-control-feedback view_password"></span>
            <input type="password" name="password" class="form-control" placeholder="Password">
          </div>
          <div class="row">
            <div class="col-sm-12">
           
            <div class="customcheck">
              <input class="styled-checkbox" id="styled-checkbox-1" type="checkbox" value="value1" required>
              <label for="styled-checkbox-1">I agree to the</label>
              <a class="linkText" href="./privacy-policy-pdf/DGFM Privacy Policy.pdf">Privacy Policy</a>
            </div>
            </div>
            <div class="col-xs-4">
            <input type="submit" name="login" value="login" class="btn btn-primary btn-block btn-flat">
            </div><!-- /.col -->
            <div class="col-xs-8 text-right">
             <a href="./forget-password.php" class="grayText">Forgot your password?</a>
            </div><!-- /.col -->
          </div>
        </form>

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
      // $(".view_password").click(function(){
      //   alert('amit');
      // })
    </script>

</body>
</html>
