<?php

include('../../function/function.php');

include('../../function/get_data_function.php');



use Google\Protobuf\Option;



$draw  = $_REQUEST['draw'];

$start = $_REQUEST['start'];

$length = $_REQUEST['length'];



$search_val = $_REQUEST['search']['value']; // Search value



if(isset($_REQUEST['order'][0]['column'])){

    $col_index 	= $_REQUEST['order'][0]['column']; // Column index

    $columnName = $_REQUEST['columns'][$col_index]['data']; // Column name

    $columnDir 	= $_REQUEST['order'][0]['dir']; // asc or desc

}



if(!isset($_REQUEST['order'][0]['column'])){

    $columnName = 'ans.id';

    $columnDir = 'desc';

}



$page_type = $_REQUEST['type'];



$ids = $_REQUEST['ids'];

$filterData = $_REQUEST['filterData'];



$dep_ids        = $ids['dep_ids'];

$loc_ids        = $ids['loc_ids'];

$surveys_ids    = $ids['surveys_ids'];

$grp_ids        = $ids['grp_ids'];



$departments = json_decode($_REQUEST['departments'], true);

$data = array();





// TotalRecord : Start

$connection=get_connection();



if ($dep_ids) {

    $t_where .= " and departmentid IN ($dep_ids)";

} else {

    $t_where .= " and departmentid IN (0)";

}



if ($loc_ids) {

    $t_where .= " and locationid IN ($loc_ids)";

} else {

    $t_where .= " and locationid IN (0)";

}



if ($surveys_ids) {

    $t_where .= " and surveyid IN ($surveys_ids)";

} else {

    $t_where .= " and surveyid IN (0)";

}



if ($grp_ids) {

    $t_where .= " and groupid IN ($grp_ids)";

} else {

    $t_where .= " and groupid IN (0)";

}



$t_query = "SELECT id FROM answers where id !=0 $t_where";

$t_query .= " GROUP BY cby ORDER BY id DESC";



$t_query = "SELECT COUNT(*) as total_count FROM ($t_query) as grouped_results";

$totalRes       = mysqli_query($connection, $t_query) or die(mysqli_error($error));

$totalData = mysqli_fetch_assoc($totalRes);



$totalRecords = $totalData['total_count']; 

// TotalRecord : End





## Fetch records

// $dateflag = false;

$where = '';

$whereFilter = '';



if (isset($_REQUEST['response']) && !empty($_REQUEST['response'])) {
    $where .= " and ans.cby = '" . $_REQUEST['response'] . "'";
}


if (isset($filterData['departmentid']) && !empty($filterData['departmentid'])) {

    $where .= " and departmentid = '" . $filterData['departmentid'] . "'";

} else {

    if ($dep_ids) {

        $where .= " and departmentid IN ($dep_ids)";

    } else {

        $where .= " and departmentid IN (0)";

    }

}



if (isset($filterData['roleid']) && !empty($filterData['roleid'])) {

    $where .= " and roleid = '" . $filterData['roleid'] . "'";

}



if (isset($filterData['locationid']) && !empty($filterData['locationid'])) {

    $where .= "and locationid = '" . $filterData['locationid'] . "'";

} else {

    if ($loc_ids) {

        $where .= " and locationid IN ($loc_ids)";

    } else {

        $where .= " and locationid IN (0)";

    }

}





if (isset($filterData['surveys']) && !empty($filterData['surveys'])) {

    $where .= " and surveyid =" . $filterData['surveys'];

} else {

    if ($surveys_ids) {

        $where .= " and surveyid IN ($surveys_ids)";

    } else {

        $where .= " and surveyid IN (0)";

    }

}





if (isset($filterData['groupid']) && !empty($filterData['groupid'])) {

    $where .= " and groupid = '" . $filterData['groupid'] . "'";

} else {

    if ($grp_ids) {

        $where .= " and groupid IN ($grp_ids)";

    } else {

        $where .= " and groupid IN (0)";

    }

}



if (isset($filterData['fdate']) && isset($filterData['sdate']) && !empty($filterData['fdate']) && !empty($filterData['sdate'])) {

    $where .= " and cdate between '" . date('Y-m-d', strtotime($filterData['fdate'])) . "' and '" . date('Y-m-d', strtotime("+1 day", strtotime($filterData['sdate']))) . "'";

}



$get_rows = "ans.id,

    ans.cdate,

    ans.cby,

    ans.surveyid,

    ans.groupid,

    ans.locationid,

    ans.departmentid,

    ans.roleid,

    s.name AS survey_name,

    g.name AS group_name,

    l.name AS location_name,

    d.name AS department_name,

    r.name AS role_name,

    

    ROW_NUMBER() OVER (PARTITION BY ans.surveyid ORDER BY ans.cby) AS respondendent_number,

    COALESCE(( SELECT SUM(IF(q.is_weighted = 1 AND q.answer_type NOT IN (2, 3, 5), a.answerval, 0)) FROM `answers` a JOIN `questions` q ON a.questionid = q.id WHERE a.surveyid = ans.surveyid AND a.cby = ans.cby ), 0 ) * 100 / (

        SELECT COUNT(*) * 100 FROM `answers` a JOIN `questions` q ON a.questionid = q.id WHERE a.surveyid = ans.surveyid AND a.cby = ans.cby AND q.is_weighted = 1 AND q.answer_type NOT IN (2, 3, 5)

    ) AS result_response,



    CASE

        WHEN EXISTS (

            SELECT 1

            FROM `answers` a

            WHERE a.surveyid = ans.surveyid 

              AND a.cby = ans.cby 

              AND a.answerid = -2 

              AND a.answerval = 100

        ) THEN 1

        ELSE 0

    END AS contact_request";





$joins = "LEFT JOIN `surveys` s ON s.id = ans.surveyid";

$joins .= " LEFT JOIN `groups` g ON g.id = ans.groupid";

$joins .= " LEFT JOIN `locations` l ON l.id = ans.locationid";

$joins .= " LEFT JOIN `departments` d ON d.id = ans.departmentid";

$joins .= " LEFT JOIN `roles` r ON r.id = ans.roleid";







$query = "SELECT $get_rows FROM `answers` ans $joins where ans.id !=0 $where";

// LIMIT $start, $length

$query .= " GROUP BY ans.cby ORDER BY $columnName $columnDir";



record_set("get_recent_entry", $query." LIMIT $start, $length");



// get count filter count

$queryFilter = "SELECT $get_rows FROM `answers` ans $joins where ans.id !=0 $where";

$queryFilter .= " GROUP BY ans.cby ORDER BY $columnName $columnDir";

$totalQueryCount = "SELECT COUNT(*) as total_count FROM ($queryFilter) as grouped_results";

record_set("get_recent_COUNT_entry", $totalQueryCount);

$row_get_recent_COUNT_entry = mysqli_fetch_assoc($get_recent_COUNT_entry);



$totalRecordwithFilter = $row_get_recent_COUNT_entry['total_count'];



if($totalRecordwithFilter >0){

    while($row_get_recent_entry = mysqli_fetch_assoc($get_recent_entry)){

        $result_response = $row_get_recent_entry['result_response'];

        $to_bo_contacted = $row_get_recent_entry['contact_request'];

        // for filter using contact

        if(isset($filterData['contacted']) && $filterData['contacted'] !='' and  $filterData['contacted']!=3){

            if($to_bo_contacted == 1 && $filterData['contacted'] == 2){

                continue;

            }

            if($to_bo_contacted == 0 && isset($filterData['contacted']) && $filterData['contacted'] == 1){

                continue;

            }

        }

        $label_class = 'success';

        if($result_response<50){

            $label_class = 'danger';

        }else 

        if($result_response<75){

            $label_class = 'info';

        }

        if($to_bo_contacted==1){ 

            $contactedLabel ='<a class="btn btn-xs bg-green">Yes</a>';

        }else{ 

            $contactedLabel ='<a class="btn btn-xs btn-danger">No</a>';

        } 



        $actionBtn = '<a class="btn btn-xs btn-primary" href="survey-result.php?surveyid=' . $row_get_recent_entry['surveyid'] . '&userid=' . $row_get_recent_entry['cby'] . '&score=' . round($result_response, 2) . '&contacted=' . $to_be_contacted . '" target="_blank">VIEW DETAILS</a>';



        // Add row data to the data array

        $data[] = array(

            "cdate"                  => date("d-m-Y", strtotime($row_get_recent_entry['cdate'])),

            "survey_name"           => $row_get_recent_entry['survey_name'],

            "group_name"            => $row_get_recent_entry['group_name'],

            "location_name"         => $row_get_recent_entry['location_name'],

            "department_name"       => $row_get_recent_entry['department_name'],

            "role_name"             => $row_get_recent_entry['role_name'],

            "respondendent_number"  => $row_get_recent_entry['respondendent_number'],

            "result_response"       => '<label class="label label-' . $label_class . '">' . round($result_response, 2) . '%</label>',

            "contact_request"       => $contactedLabel,

            "action"                => $actionBtn

        );

    }

}



## Response

$response = array(

    "draw" => intval($draw),

    "iTotalRecords" => $totalRecords,

    "iTotalDisplayRecords" => $totalRecordwithFilter,

    "aaData" => $data

);

echo json_encode($response);

