<?php
include('function/function.php');

if(!empty($_GET['surveyid'])){
	record_set("get_surveys", "select * from surveys where id='".$_GET['surveyid']."'");
	$row_get_surveys = mysqli_fetch_assoc($get_surveys);
}
 $thanku_message =  "Thank you for completing our survey, <br>
 we really appreciate your feedback"; 
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Thank You.</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
/* h2 {
	border-bottom:1px solid #000;
	padding:0 0 5px 0;
} */
.h2, h2,h5 {
    font-size: 2rem;
}
hr{
   border-bottom:1px solid #000;
	padding:0 0 5px 0;
}
.fa-brands{
   font-size: 5rem;
}
</style>
</head>

<body lang="en" class="notranslate" translate="no">
<div class="container">
<div align="center"><img src="<?=MAIN_LOGO?>" width="200"></div>
<br>
<h2 align="center"><?php echo $thanku_message; ?><br><br></h2>

<?php if( !empty($row_get_surveys['google_review_link']) || !empty($row_get_surveys['facebook_review_link']) || !empty($row_get_surveys['other_link']) ){ ?>
   <h5 align="center">Leave a review</h5>
<?php } ?>

<p span align="center">
   <?php if( !empty($row_get_surveys['google_review_link']) ){ ?>
      <a href="<?=$row_get_surveys['google_review_link']?>" target="_blank"><i class="lg-2 fa fa-brands fa-google"></i></a>
   <?php } ?>

   <?php if( !empty($row_get_surveys['facebook_review_link']) ){ ?>
      <a href="<?=$row_get_surveys['facebook_review_link']?>" target="_blank"><i class="fa fa-brands fa-facebook"></i></a>
   <?php } ?>
</p>

<?php if( !empty($row_get_surveys['other_link']) ){ ?>
<h5 align="center"><a href="<?=$row_get_surveys['other_link']?>" target="_blank"> <?=$row_get_surveys['other_link']?></a></h5>
<?php } ?>

<hr>

</div><br><br>
<center>Powered by Datagroup Solutions<br><br>
<img  src="https://www.datagroupsolutions.com/wp-content/uploads/2020/11/Data-Group-Solutions-survey.png" alt="" width="200" height="36" /></center>
</body>
</html>