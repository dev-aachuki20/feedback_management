<?php
error_reporting(E_ALL);
$length = '8';
$string = rand(10,100);
$original_string = array_merge(range(0,9), range('a','z'), range('A', 'Z'));
$original_string = implode("", $original_string);
$string1=  substr(str_shuffle($original_string), 0, $length);
$randomCode = $string1.$string;  
echo $randomCode;
die();
?>

<div id="demo">
  <div class="border-bottom px-5 pt-5 pb-4" id="block1">
    <h3>What is Lorem Ipsum?</h3>
    <p>
Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
    </p>
  </div>
  <div class="px-5 pt-5 pb-4" id="block2"> 
    <h3>Why do we use it?</h3>
    <p>
      It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).
    </p>
  </div>
</div>

<div class="flex">
  <a href="javascript:void(0)" class="btn btn-download">Generate PDF</a>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.1/html2pdf.bundle.min.js"></script>
<script>
    const options = {
  margin: 0.3,
  filename: 'filename.pdf',
  image: { 
    type: 'jpeg', 
    quality: 0.98 
  },
  html2canvas: { 
    scale: 2 
  },
  jsPDF: { 
    unit: 'in', 
    format: 'a4', 
    orientation: 'portrait' 
  }
}

var objstr = document.getElementById('block1').innerHTML;
var objstr1 = document.getElementById('block2').innerHTML;
console.log(objstr1);
var strr = '<html><head><title>Testing</title>';   
strr += '</head><body>';
strr += '<div style="border:0.1rem solid #ccc!important;padding:0.5rem 1.5rem 0.5rem 1.5rem;margin-top:1.5rem">'+objstr+'</div>';
strr += '<div style="border:0.1rem solid #ccc!important;padding:0.5rem 1.5rem 0.5rem 1.5rem;margin-top:1.5rem">'+objstr1+'</div>';
strr += '</body></html>';

$('.btn-download').click(function(e){
  e.preventDefault();
  html2pdf().from(objstr).set(options).save();
});
</script>