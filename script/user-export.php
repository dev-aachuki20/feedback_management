<?php 
include('../function/function.php');
include('../function/get_data_function.php');

record_set("get_client", "select * from clients order by id");
while($row_get_client = mysqli_fetch_assoc($get_client)){
    // $data =  array(
    //     "name"          => $row_get_client['name'],
    //     "email"         => $row_get_client['email'],
    //     "password"      => $row_get_client['password'],
    //     "user_type"     => 4,
    //     "phone"         => $row_get_client['phone'],
    //     "photo"         => $row_get_client['photo'],
    //     'cip'           => $row_get_client['cip'],
    //     'cby'           => $row_get_client['cby'],
    //     'cdate'         => $row_get_client['cdate'],
    //     'cstatus'       => $row_get_client['cstatus']
    // );
    // $insert_value =  dbRowInsert("manage_users",$data);
}



?>