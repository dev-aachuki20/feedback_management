<?php
    include('../function/function.php');
    
    if(isset($_POST['client_id'])){
        $client_id = $_POST['client_id'];
        record_set("get_client", "select * from clients where id='".$client_id."'");	
        $response = '';
        while($row_get_client = mysqli_fetch_assoc($get_client)){
            $locationId = $row_get_client['locationid'];
            // Retrieve Locations
            record_set("get_location", "select * from locations where id in(".$locationId.") AND cstatus=1 order by name asc");        
            if($totalRows_get_location > 0){
                while($row_get_location = mysqli_fetch_assoc($get_location)){ 
                    $response .="<option value='".$row_get_location['id']."'>".$row_get_location['name']."</option>";
                }
            }
        }
        echo $response;
    }
    

?>