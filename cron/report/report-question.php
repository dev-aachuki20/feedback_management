<?php
require dirname(__DIR__, 2) . '/function/function.php';
require dirname(__DIR__, 2) . '/function/get_data_function.php';

// get scheduled reports
record_set("get_scheduled_reports", "select srt.* from scheduled_report_templates as srt INNER JOIN report_templates as rt ON srt.temp_id = rt.id where rt.report_type=2");

while ($row_report = mysqli_fetch_assoc($get_scheduled_reports)) {
    $current_date  = date('Y-m-d', time());
    $start_date = date('Y-m-d', strtotime($row_report['start_date']));
    $next_date   = date('Y-m-d', strtotime($row_report['next_date']));
    $end_date   = date('Y-m-d', strtotime($row_report['end_date']));

    $is_due_gt_start_date = check_differenceDate($next_date, $start_date, 'gt');
    $is_today_due_date = check_differenceDate($current_date, $next_date, 'eq');
    $is_curr_lte_end_date = check_differenceDate($current_date, $end_date, 'lte');

    if ($is_due_gt_start_date && $is_today_due_date && $is_curr_lte_end_date  && $row_report['send_to'] != null) {

        if ($row_report['sch_interval'] == $row_report['time_interval']) {
            include('../report/report-question-overall-sftp-pdf.php');
            include('../report/report-question-overall-sftp-excel.php');
        } else {
            include('../report/report-question-overall-dftp-pdf.php');
            include('../report/report-question-overall-dftp-excel.php');
        }

        // send mail
        $mail_users = explode(",", $row_report['send_to']);
        foreach ($mail_users as $userId) {
            $user_details = get_user_datails($userId);
            $to = $user_details['email'];
            echo $user_details['email'] . '<br>';
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
    } else {
        echo $row_report['id'] . " record is not executed. <br>";
    }
}
