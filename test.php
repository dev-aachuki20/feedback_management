<style>
.select2-container {
  width: 90% !important;
}

.select2-container .select-all {
        position: absolute;
        top: 6px;
        right: 4px;
        width: 20px;
        height: 20px;
        margin: auto;
        display: block;
        background-size: contain;
        cursor: pointer;
        z-index: 999999;
    }
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

<select class="multiple-selectss" multiple id="my-select">
<option value="1">Option 1</option>
<option value="2">Option 2</option>
<option value="3">Option 3</option>
<option value="4">Option 4</option>
</select>

<script>
$('.multiple-selectss').select2({
    placeholder: 'Press CTRL+A for selecr or unselect all options'
});

$('.multiple-selectss[multiple]').siblings('.select2-container').append('<input type="checkbox" name="" id="" value="1" onclick="selectAll()" class="select-all">');
        
function selectAll() {
    let isChecked = $('.select-all').is(':checked');
    if(isChecked){
        $("#my-select > option").prop("selected", true);
    }else {
        $("#my-select > option").prop("selected", false);
    }
    $("#my-select").trigger("change");
}
      
</script>