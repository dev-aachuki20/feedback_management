<?php
    include('../function/function.php');
    
	if(!empty($_GET['term'])){
		
		$search_val = $_GET['term'];
		$langId = $_GET['langId'];
		$lang_iso='en';

		record_set("get_language", "select * from languages where id='".$langId."'");				
		$row_get_language = mysqli_fetch_assoc($get_language);
		if($row_get_language['id'] == $langId){
			$lang_iso = $row_get_language['iso_code'];
		}

		if($lang_iso == 'en'){
			record_set("get_school", "select * from schools where name like '".$search_val."%' and cstatus=1 order by name asc");
		}else{
			record_set("get_school", "select * from schools where name_".$lang_iso." like '".$search_val."%' and cstatus=1 order by name asc");
		}

		$availableSchools = array();
		while($row_get_school = mysqli_fetch_assoc($get_school)){	
			$school_name = ($lang_iso == 'en')?$row_get_school['name']:$row_get_school['name_'.$lang_iso];
			$availableSchools[] =['value'=>$row_get_school['locationid'], 'label'=>strtoupper($school_name)]; 
		}
		echo json_encode($availableSchools);
	}
    
?>