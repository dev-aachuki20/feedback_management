<?php
require dirname(__DIR__, 2) . '/function/function.php';
require dirname(__DIR__, 2) . '/function/get_data_function.php';

// get scheduled reports
record_set("get_scheduled_reports", "select srt.* from scheduled_report_templates as srt INNER JOIN report_templates as rt ON srt.temp_id = rt.id where rt.report_type=2 order by srt.id desc");

while ($row_report = mysqli_fetch_assoc($get_scheduled_reports)) {
    $current_date  = date('Y-m-d', time());
    $start_date = date('Y-m-d', strtotime($row_report['start_date']));
    $next_date   = date('Y-m-d', strtotime($row_report['next_date']));
    $end_date   = date('Y-m-d', strtotime($row_report['end_date']));

    $is_due_gt_start_date = check_differenceDate($next_date, $start_date, 'gt');
    $is_today_due_date = check_differenceDate($current_date, $next_date, 'eq');
    $is_curr_lte_end_date = check_differenceDate($current_date, $end_date, 'lte');

    echo '<hr/> is_due_gt_start_date =>'.$is_due_gt_start_date.'<br/>';
    echo 'current_date =>'.$current_date.'<br/>';
    echo 'is_today_due_date =>'.$is_today_due_date.'<br/>';
    echo 'is_curr_lte_end_date =>'.$is_curr_lte_end_date.'<hr/> <br/> ';

    if ($is_due_gt_start_date && $is_today_due_date && $is_curr_lte_end_date  && $row_report['send_to'] != null) {
        echo $row_report['id'] . ' <br>';
        $filter = json_decode($row_report['filter'], 1);
        if ($filter['field_value'] == null) {
            if ($row_report['sch_interval'] == $row_report['time_interval']) {
                echo 'SFTP ' . $row_report['id'] . '<br>';
                include('../report/report-question-overall-sftp-pdf.php');
                include('../report/report-question-overall-sftp-excel.php');
            } else {
                echo 'DFTP ' . $row_report['id'] . '<br>';
                include('../report/report-question-overall-dftp-pdf.php');
                include('../report/report-question-overall-dftp-excel.php');
            }
        } else {
            // group/location/department
             if ($row_report['sch_interval'] == $row_report['time_interval']) {
                include('../report/report-question-lgd-sftp-excel.php');
            } else {
                include('../report/report-question-lgd-dftp-excel.php');
            }
        }


        // send mail
        $attachments = array('document/survey-report-question-' . $row_report['id'] . '.xlsx', 'document/survey-report-question-' . $row_report['id'] . '.pdf');

        $mail_users = explode(",", $row_report['send_to']);
        foreach ($mail_users as $userId) {
            $user_details = get_user_datails($userId);
            $to = $user_details['email'];
            $from_mail = ADMIN_EMAIL;
            $name = $user_details['name'];
            $subject = "Schedule Report";
            $message = 'Hello ' . $name . ' you have schedule report';

            $mail = cron_emails($attachments, $to, $from_mail, $name, $subject, $message);
        }

        // update next schedule date with interval
        $nextScheduledDate = $row_report['next_date'];
        $updateSchedule = date('Y-m-d H:i:s', strtotime(' + ' . $row_report['sch_interval'] . ' hours', strtotime($nextScheduledDate)));
        $data = array(
            "next_date" => $updateSchedule,
        );

        // $update = dbRowUpdate("scheduled_report_templates", $data, "where id=" . $row_report['id']);

        if (count($attachments) > 0) {
            foreach ($attachments as $key => $value) {
                // echo "<br>" . $value . "<br>";
                // unlink($value);
            }
        }

        echo $row_report['id'] . " record is executed successfully. <br>";
    } else {
        echo $row_report['id'] . " record is not executed. <br>";
    }
}
