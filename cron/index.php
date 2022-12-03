<?php

    require('../function/function.php');
    require('../function/get_data_function.php');
    include('../permission.php');

    /* survey statistics cron start */

     include('./statistics/survey-statistics.php');

    /* survey statistics cron end */
?>