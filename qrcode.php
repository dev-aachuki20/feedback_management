<?php
include "./phpqrcode/qrlib.php";
$product = $_GET['text'];
QRcode::png($product);
?>