<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewbusinessreport extends SugarView
{
    function display()
    {
        $smartyCont = new Sugar_Smarty();
        $this->populateContent($smartyCont);
        $smartyCont->display('modules/EC_TongHop/tpls/view_business_report.tpl');
    }

    function populateContent($smartyobj)
    {
        $arr_date = array(
            '<option value="this_week">Tuần này</option>',
            '<option value="previous_week">Tuần trước</option>',
            '<option value="this_month">Tháng này</option>',
            '<option value="previous_month">Tháng trước</option>',
        );

        $date_report = $_POST['date_select'] ?? $_GET['date_select'] ?? 'this_week';
        $smartyobj->assign('DATE_OPTION', implode('', $arr_date));
        $smartyobj->assign('CURRENT_OPTION', $date_report);

        // DATA REPORT
        $html_report = $this->genInforReport($date_report);
        $smartyobj->assign('DATA_REPORT', $html_report);

        // SAVE QC COST
        if (isset($_POST) && !empty($_POST)) {
            if (isset($_POST['btnSaveCostQc'])) {
                $this->saveCostQc();
            }
        }
    }

    function saveCostQc()
    {
        global $db;
        $cost_qc       = (int)str_replace('.', '', $_POST['cost-qc']) ?? 0;
        $user_id       = $_POST['user_id'];
        $from_date     = $_POST['from_date'];
        $to_date       = $_POST['to_date'];
        $date_select   = $_GET['date_select'];

        // VALIDATE
        $sql_update = '
                UPDATE ec_report_weekly
                SET advertisement_cost = ' . $cost_qc . '
                WHERE user_id = "' . $user_id . '"
                    AND from_date = "' . $from_date . '"
                    AND to_date = "' . $to_date . '"
                    AND deleted = 0';
        $db->query($sql_update);

        header("Location: index.php?module=EC_TongHop&action=businessreport&date_select=" . $date_select);
        return;
    }

    function genInforReport($period)
    {
        global $db, $current_user;

        $date_ranges    = [];
        $today          = date('Y-m-d');
        $period         = $_POST['date_select'] ?? $_GET['date_select'] ?? 'this_week';

        if (isset($_POST['real-time'])) {
            $period = $_POST['date_select'];
        }

        switch ($period) {
            case 'this_week':
                $monday = date('Y-m-d', strtotime('this week Monday', strtotime($today)));
                $sunday = date('Y-m-d', strtotime('this week Sunday', strtotime($today)));

                for ($i = 0; $i < 7; $i++) {
                    $date_ranges[] = [
                        'from' => date('Y-m-d', strtotime("+$i days", strtotime($monday))),
                        'to' => date('Y-m-d', strtotime("+$i days", strtotime($monday))),
                    ];
                }
                break;
            case 'previous_week':
                $monday_prev = date('Y-m-d', strtotime('last week Monday', strtotime($today)));
                $sunday_prev = date('Y-m-d', strtotime('last week Sunday', strtotime($today)));

                for ($i = 0; $i < 7; $i++) {
                    $date_ranges[] = [
                        'from' => date('Y-m-d', strtotime("+$i days", strtotime($monday_prev))),
                        'to' => date('Y-m-d', strtotime("+$i days", strtotime($monday_prev))),
                    ];
                }
                break;
            case 'this_month':
                $start_date = date('Y-m-01'); // Ngày đầu tháng này
                $end_date = date('Y-m-t');    // Ngày cuối tháng này

                $current_day = strtotime($start_date);

                // Xử lý tuần đầu tiên nếu không bắt đầu từ Thứ Hai
                if (date('N', $current_day) != 1) {
                    $week_end = strtotime('next Sunday', $current_day);
                    $date_ranges[] = [
                        'from' => date('Y-m-d', $current_day),
                        'to' => date('Y-m-d', min($week_end, strtotime($end_date))),
                    ];
                    $current_day = strtotime('+1 day', $week_end);
                }

                // Xử lý các tuần đầy đủ
                while ($current_day <= strtotime($end_date)) {
                    $week_start = $current_day;
                    $week_end = strtotime('+6 days', $week_start);

                    $date_ranges[] = [
                        'from' => date('Y-m-d', $week_start),
                        'to' => date('Y-m-d', min($week_end, strtotime($end_date))),
                    ];
                    $current_day = strtotime('+1 week', $week_start);
                }
                break;

            case 'previous_month':
                $start_date = date('Y-m-01', strtotime('first day of last month')); // Ngày đầu tháng trước
                $end_date = date('Y-m-t', strtotime('last day of last month'));     // Ngày cuối tháng trước

                $current_day = strtotime($start_date);

                // Xử lý tuần đầu tiên nếu không bắt đầu từ Thứ Hai
                if (date('N', $current_day) != 1) {
                    $week_end = strtotime('next Sunday', $current_day);
                    $date_ranges[] = [
                        'from' => date('Y-m-d', $current_day),
                        'to' => date('Y-m-d', min($week_end, strtotime($end_date))),
                    ];
                    $current_day = strtotime('+1 day', $week_end);
                }

                // Xử lý các tuần đầy đủ
                while ($current_day <= strtotime($end_date)) {
                    $week_start = $current_day;
                    $week_end = strtotime('+6 days', $week_start);

                    $date_ranges[] = [
                        'from' => date('Y-m-d', $week_start),
                        'to' => date('Y-m-d', min($week_end, strtotime($end_date))),
                    ];
                    $current_day = strtotime('+1 week', $week_start);
                }
                break;
            default:
                $monday = date('Y-m-d', strtotime('this week Monday', strtotime($today)));
                $sunday = date('Y-m-d', strtotime('this week Sunday', strtotime($today)));

                for ($i = 0; $i < 7; $i++) {
                    $date_ranges[] = [
                        'from' => date('Y-m-d', strtotime("+$i days", strtotime($monday))),
                        'to' => date('Y-m-d', strtotime("+$i days", strtotime($monday))),
                    ];
                }
                break;
        }

        $html =  '';
        foreach ($date_ranges as $index => $range) {
            $from_date = $range['from'];
            $to_date   = $range['to'];

            // QUERY BOOKING
            // ====================================================
            // Bước 1: Kiểm tra dữ liệu trong ec_report_weekly
            $sql_exist = '
                SELECT IF(COUNT(id) > 0, 1, 0) as count
                FROM ec_report_weekly
                WHERE from_date = "' . $from_date . '" 
                    AND to_date = "' . $to_date . '"
                    AND type = "BOOKING"
                    AND deleted = 0';
            $count_rows = $db->getOne($sql_exist);

            // Bước 2: Tạo dữ liệu trong ec_report_weekly theo from_date - to_date
            $sql_select = '
                SELECT last_name, user_name, user_id,
                    SUM(bk_created) AS bk_created,
                    SUM(bk_called) AS bk_called,
                    SUM(bk_paying) AS bk_paying,
                    SUM(bk_confirmed) AS bk_confirmed,
                    SUM(bk_printed) AS bk_printed,
                    SUM(bk_completed) AS bk_completed,
                    SUM(bk_cancelled) AS bk_cancelled,
                    SUM(total) AS total,
                    SUM(total_sales) AS total_sales,
                    SUM(total_ticket) AS total_ticket,
                    0 AS advertisement_cost,
                    "' . $from_date . '" AS from_date,
                    "' . $to_date . '" AS to_date
                FROM (
                    SELECT
                        u.last_name, u.user_name, u.id AS user_id,
                        COUNT(IF(bk.booking_status = 1, bk.id, NULL)) AS bk_created,
                        COUNT(IF(bk.booking_status = 6, bk.id, NULL)) AS bk_called,
                        COUNT(IF(bk.booking_status = 2, bk.id, NULL)) AS bk_paying,
                        COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
                        COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
                        COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
                        COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
                        COUNT(bk.id) AS total,
                        SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_amount - bk.total_bought_amount, 0)) AS total_sales,
                        SUM(IF(bk.booking_status IN (3, 7, 8), (SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
                        DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
                    FROM ec_flight_bookings bk
                        LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
                    WHERE u.title = "Bot" 
                        AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= "' . $from_date . '"
                        AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= "' . $to_date . ' 23:59:59"
                        AND bk.deleted = 0 
                    GROUP BY bk.id
                    
                    UNION
                    SELECT 
                        IF(u.title = "Bot", u.last_name, "Chưa xác định") AS last_name,
                        IF(u.title = "Bot", u.user_name, "") AS user_name,
                        IF(u.title = "Bot", u.id, "BK_UNK") AS user_id,
                        0 AS bk_created,
                        0 AS bk_called,
                        0 AS bk_paying,
                        0 AS bk_confirmed,
                        0 AS bk_printed,
                        0 AS bk_completed,
                        0 AS bk_cancelled,
                        0 AS total,
                        - (
                            SUM(IFNULL(bk_psg.luggage_purchase, 0)) + SUM(IFNULL(bk_psg.luggage_purchase_inbound, 0))
                        ) AS total_sales,
                        0 AS total_ticket,
                        "" AS bk_date_entered
                    FROM ec_booking_passengers bk_psg
                        INNER JOIN ec_flight_bookings bk ON bk.id = bk_psg.booking_id
                            AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= "' . $from_date . '"
                            AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= "' . $to_date . ' 23:59:59"
                            AND bk.booking_status IN (3, 7, 8)
                        INNER JOIN users u ON bk.created_by = u.id 
                    WHERE (bk_psg.add_type IS NULL OR bk_psg.add_type = "") AND bk_psg.deleted = 0
                    GROUP BY user_id
                    
                    UNION
                    SELECT
                        "Booking chưa xác định" AS last_name, "" AS user_name, "BK_UNK" AS user_id,
                        COUNT(IF(bk.booking_status = 1, bk.id, NULL)) AS bk_created,
                        COUNT(IF(bk.booking_status = 6, bk.id, NULL)) AS bk_called,
                        COUNT(IF(bk.booking_status = 2, bk.id, NULL)) AS bk_paying,
                        COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
                        COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
                        COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
                        COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
                        COUNT(bk.id) AS total,
                        SUM(IF(bk.booking_status = 8, bk.total_amount - bk.total_bought_amount - bk.luggage_fee, 0)) AS total_sales,
                        SUM(IF(bk.booking_status IN (3, 7, 8), (SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
                        DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
                    FROM ec_flight_bookings bk
                        LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
                    WHERE
                        DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= "' . $from_date . '"
                        AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= "' . $to_date . ' 23:59:59"
                        AND (
                            (u.title = "Bot" AND LOWER(bk.contact_name) IN ("tim chuyen bay", "callnow", "call now"))
                            OR u.title <> "Bot"
                        )
                        AND bk.deleted = 0 
                    GROUP BY bk.id

                ) AS tmp
                GROUP BY user_id
                ORDER BY total_sales DESC
            ';

            if(strtotime($from_date) <= strtotime($today)){
                if ((int)$count_rows == 0) {
                    // CREATE
                    $results = $db->query($sql_select);
                    while ($row = $db->fetchByAssoc($results)) {
                        $rp = new EC_Report_Weekly();
                        $rp->user_id = $row['user_id'];
                        $rp->last_name = $row['last_name'];
                        $rp->user_name = $row['user_name'];
                        $rp->bk_created = $row['bk_created'];
                        $rp->bk_called = $row['bk_called'];
                        $rp->bk_paying = $row['bk_paying'];
                        $rp->bk_confirmed = $row['bk_confirmed'];
                        $rp->bk_printed = $row['bk_printed'];
                        $rp->bk_completed = $row['bk_completed'];
                        $rp->bk_cancelled = $row['bk_cancelled'];
                        $rp->total_qty = $row['total'];
                        $rp->total_sales = $row['total_sales'];
                        $rp->total_ticket = $row['total_ticket'];
                        $rp->type = 'BOOKING';
                        $rp->advertisement_cost = 0;
                        $rp->from_date = $from_date;
                        $rp->to_date = $to_date;
                        $rp->report_date = date('Y-m-d');
                        $rp->save();
                    }
                } else if (isset($_POST['real-time'])) {
                    $results = $db->query($sql_select);
                    while ($row = $db->fetchByAssoc($results)) {
                        // Bước 3: Kiểm tra xem user_id đã tồn tại trong khoảng thời gian chưa
                        $check_sql = '
                            SELECT COUNT(*)
                            FROM ec_report_weekly
                            WHERE user_id = "' . $row['user_id'] . '" 
                                AND from_date = "' . $from_date . '" 
                                AND to_date = "' . $to_date . '"
                                AND type = "BOOKING"
                                AND deleted = 0';
                        $user_exists = $db->getOne($check_sql);

                        if ((int)$user_exists > 0) {
                            $update_sql = '
                                UPDATE ec_report_weekly
                                SET 
                                    bk_created = ' . (int)$row['bk_created'] . ',
                                    bk_called = ' . (int)$row['bk_called'] . ',
                                    bk_paying = ' . (int)$row['bk_paying'] . ',
                                    bk_confirmed = ' . (int)$row['bk_confirmed'] . ',
                                    bk_printed = ' . (int)$row['bk_printed'] . ',
                                    bk_completed = ' . (int)$row['bk_completed'] . ',
                                    bk_cancelled = ' . (int)$row['bk_cancelled'] . ',
                                    total_qty = ' . $row['total'] . ',
                                    total_sales = ' . $row['total_sales'] . ',
                                    total_ticket = ' . $row['total_ticket'] . '
                                WHERE user_id = "' . $row['user_id'] . '" 
                                    AND from_date = "' . $from_date . '" 
                                    AND to_date = "' . $to_date . '"
                                    AND type = "BOOKING"
                                    AND deleted = 0
                            ';

                            $db->query($update_sql);
                        } else {
                            $rp = new EC_Report_Weekly();
                            $rp->user_id = $row['user_id'];
                            $rp->last_name = $row['last_name'];
                            $rp->user_name = $row['user_name'];
                            $rp->bk_created = (int)$row['bk_created'];
                            $rp->bk_called = (int)$row['bk_called'];
                            $rp->bk_paying = (int)$row['bk_paying'];
                            $rp->bk_confirmed = (int)$row['bk_confirmed'];
                            $rp->bk_printed = (int)$row['bk_printed'];
                            $rp->bk_completed = (int)$row['bk_completed'];
                            $rp->bk_cancelled = (int)$row['bk_cancelled'];
                            $rp->total_qty = $row['total'];
                            $rp->total_sales = $row['total_sales'];
                            $rp->total_ticket = $row['total_ticket'];
                            $rp->type = 'BOOKING';
                            $rp->advertisement_cost = 0;
                            $rp->from_date = $from_date;
                            $rp->to_date = $to_date;
                            $rp->report_date = $today;
                            $rp->save();
                        }
                    }
                }
            }

            // QUERY CUỘC GỌI
            // ====================================================
            if(strtotime($from_date) <= strtotime($today)){
                // Bước 1: Kiểm tra dữ liệu calls trong ec_report_weekly
                $sql_exist_calls = '
                    SELECT IF(COUNT(id) > 0, 1, 0)
                    FROM ec_report_weekly
                    WHERE from_date = "' . $from_date . '" 
                        AND to_date = "' . $to_date . '"
                        AND type = "CALLS"
                        AND deleted = 0';
                $count_rows_calls = $db->getOne($sql_exist_calls);
                $sql_calls = 'SELECT 
                                COUNT(IF(c.direction = "inbound", c.id, NULL)) AS total_calls,
                                SUM(c.booking_id IS NOT NULL AND c.booking_id <> "" AND c.direction = "inbound") AS qty_call_booking,
                                COUNT(IF(c.direction = "missed", c.id, NULL)) AS qty_call_missed,
                                SUM(c.booking_id IS NOT NULL AND c.booking_id <> "" AND c.direction = "inbound" AND bk.booking_status = 8) AS qty_call_booking_ok
                            FROM calls c
                            LEFT JOIN ec_flight_bookings bk ON bk.id = c.booking_id
                            WHERE DATE_ADD(c.date_entered, INTERVAL 7 HOUR) BETWEEN "' . $from_date . '" AND "' . $to_date . ' 23:59:59"
                            AND c.deleted = 0';
                if ((int)$count_rows_calls == 0) {
                    // CREATE
                    $res_call   = $db->query($sql_calls);
                    while ($row = $db->fetchByAssoc($res_call)) {
                        $rp = new EC_Report_Weekly();
                        $rp->total_calls            = (int)$row['total_calls'];
                        $rp->qty_call_missed        = (int)$row['qty_call_missed'];
                        $rp->qty_call_booking       = (int)$row['qty_call_booking'];
                        $rp->qty_call_booking_ok    = (int)$row['qty_call_booking_ok'];
                        $rp->type = 'CALLS';
                        $rp->from_date = $from_date;
                        $rp->to_date = $to_date;
                        $rp->report_date = $today;
                        $rp->save();
                    }
                } else if (isset($_POST['real-time'])) {
                    // UPDATE
                    $res_call   = $db->query($sql_calls);
                    while ($row = $db->fetchByAssoc($res_call)) {
                        // Bước 3: Kiểm tra calls đã tồn tại trong khoảng thời gian chưa
                        $check_sql_calls = '
                            SELECT COUNT(*)
                            FROM ec_report_weekly
                            WHERE from_date = "' . $from_date . '" 
                                AND to_date = "' . $to_date . '"
                                AND type = "CALLS"
                                AND deleted = 0';
                        $calls_exists = $db->getOne($check_sql_calls);

                        if ((int)$calls_exists > 0) {
                            $update_sql_calls = '
                                UPDATE ec_report_weekly
                                SET 
                                    total_calls = ' . (int)$row['total_calls'] . ',
                                    qty_call_missed = ' . (int)$row['qty_call_missed'] . ',
                                    qty_call_booking = ' . (int)$row['qty_call_booking'] . ',
                                    qty_call_booking_ok = ' . (int)$row['qty_call_booking_ok'] . '
                                WHERE from_date = "' . $from_date . '" 
                                    AND to_date = "' . $to_date . '"
                                    AND type = "CALLS"
                                    AND deleted = 0
                            ';

                            $db->query($update_sql_calls);
                        } else {
                            // Nếu không tồn tại thì tạo mới
                            $rp = new EC_Report_Weekly();
                            $rp->total_calls            = (int)$row['total_calls'];
                            $rp->qty_call_missed        = (int)$row['qty_call_missed'];
                            $rp->qty_call_booking       = (int)$row['qty_call_booking'];
                            $rp->qty_call_booking_ok    = (int)$row['qty_call_booking_ok'];
                            $rp->type = 'CALLS';
                            $rp->from_date = $from_date;
                            $rp->to_date = $to_date;
                            $rp->report_date = $today;
                            $rp->save();
                        }
                    }
                }
            }

            if (strtotime($from_date) <= strtotime($today)) {
                // GET DATA
                $sql_get_calls      = "SELECT * FROM ec_report_weekly WHERE from_date = '$from_date' AND to_date = '$to_date' AND type = 'CALLS'";
                $res_calls          = $this->bean->db->query($sql_get_calls);
                $row_count_calls    = $this->bean->db->countRows($res_calls);
                $content_call = '';
                while ($row = $this->bean->db->fetchByAssoc($res_calls)) {
                    if($row_count_calls){
                        $content_call = sprintf(
                            'CG nhỡ %02d + CG đến %02d = %02d (CG tạo BK %02d / Hoàn tất %02d)',
                            $row['qty_call_missed'],
                            $row['total_calls'],
                            $row['qty_call_missed'] + $row['total_calls'],
                            $row['qty_call_booking'],
                            $row['qty_call_booking_ok']
                        );

                        // $content_call = 'CG nhỡ '.$row['qty_call_missed'].' + CG đến '.$row['total_calls'].' = '.($row['qty_call_missed']+$row['total_calls']).' (CG tạo BK  '.$row['qty_call_booking'].' /  Hoàn tất  '.$row['qty_call_booking_ok'].')';
                    }
                }
    
                $html .= '<table class="table-details__booking table-report__weekly" cellpadding="0" cellspacing="0">
                            <thead>';
                if (in_array($period, ['this_month', 'previous_month'])) {
                    $start_day = date('d-m-Y', strtotime($from_date));
                    $end_day = date('d-m-Y', strtotime($to_date));
    
                    $start_dayInVietnamese = $this->getDayInVietnamese($from_date);
                    $end_dayInVietnamese = $this->getDayInVietnamese($to_date);
    
                    $html .= '
                            <tr>
                                <th colspan="8"> 
                                    <div class="flex-between gap-2 flex-wrap">
                                        <span>
                                            Từ ' . $start_day . ' (' . $start_dayInVietnamese . ') đến ' . $end_day . ' (' . $end_dayInVietnamese . ')
                                        </span>
                                        <span>
                                            '.$content_call.'
                                        </span>
                                    </div>
                                </th>
                            </tr>';
                } else {
                    $html .= '
                            <tr>
                                <th colspan="8">
                                    <div class="flex-between gap-2 flex-wrap">
                                        <span>
                                            ' . date('d-m-Y', strtotime($from_date)) . ' (' . $this->getDayInVietnamese($from_date) . ')
                                        </span>
                                        <span>
                                            '.$content_call.'
                                        </span>
                                    </div>
                                </th>
                            </tr>';
                }
    
                $html .= '<tr>
                            <th rowspan="2">Trang web</th>
                            <th colspan="4">Doanh số</th>
                            <th colspan="2" rowspan="2">Tổng BK</th>
                            <th rowspan="2">Chi QC</th>
                        </tr>
                        <tr>
                            <th colspan="2">Số tiền</th>
                            <th>Vé</th>	
                            <th>BK OK</th>
                        </tr>	
                    </thead><tbody>';
    
                $sql_get = "SELECT * FROM ec_report_weekly WHERE from_date = '$from_date' AND to_date = '$to_date' AND type = 'BOOKING'";
                $res = $this->bean->db->query($sql_get);
                $row_count     = $this->bean->db->countRows($res);
    
                $sales = array();
                $total_sale = $total_sale_ticket = $total_sale_qty = $total_bk = $total_qc_cost = 0;
    
                if ((int)$row_count > 0) {
                    while ($row = $this->bean->db->fetchByAssoc($res)) {
                        $sales[$row['user_id']] = $row['total_sales'];
                        $total_sale += $row['total_sales'];
                        $total_sale_ticket     += (int)($row['total_ticket']);
                        $total_sale_qty     += (int)($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']);
                        $total_bk             += (int)$row['total_qty'];
                        $total_qc_cost      += (int)$row['advertisement_cost'];
    
                        if (in_array($period, ['this_month', 'previous_month'])) {
                            $data_time = 'Từ ' . $start_day . ' (' . $this->getDayInVietnamese($from_date) . ') đến ' . $end_day . ' (' . $this->getDayInVietnamese($to_date) . ')';
                        } else {
                            $data_time = date('d-m-Y', strtotime($from_date)) . ' (' . $this->getDayInVietnamese($from_date) . ')';
                        }
    
                        $denominator_total = ($row['total_qty'] == 0 || empty($row['total_qty'])) ? 1 : $row['total_qty'];
                        $btn_add_qc = '<button class="btn btn-primary btn-add-qc-cost px-1" data-bs-toggle="modal" data-bs-target="#modalEditCostQc" data-from-date="' . $from_date . '" data-to-date="' . $to_date . '" data-user-id="' . $row['user_id'] . '" data-site="' . $row['last_name'] . '" data-time="' . $data_time . '">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                                            </svg>
                                        </button>';
                        $cost_qc = empty($row['advertisement_cost']) ? $btn_add_qc : format_number($row['advertisement_cost']);
    
                        $html .= '
                                <tr>
                                    <td class="text-start fw-semibold">' . $row['last_name'] . '</td>
        
                                    <td colspan="2" class="text-start">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <div class="total text-start">' . format_number($row['total_sales']) . '</div>
                                            <div class="hide-mobile total_percent text-end">&nbsp;&nbsp;($SALE_PER' . $row['user_id'] . ')</div>
                                        </div>
                                    </td>
                                    <td class="text-center total_ticket">' . $row['total_ticket'] . '</td>
                                    <td class="text-center fw-semibold color-blue">' . format_number($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']) . '</td>
                                    <td colspan="2">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <div class="total_percent text-start">(' . format_number(($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']) / $denominator_total * 100) . '%)</div>
                                            <div class="hide-mobile total text-end color-red fw-semibold">&nbsp;&nbsp;' . format_number($row['total_qty']) . '</div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        ' . $cost_qc . ' 
                                    </td>
                                </tr>';
                    }
                } else {
                    $html .= '<tr>
                                <td colspan="8">Không có dữ liệu</td>
                            </tr>';
                }
    
                foreach ($sales as $flight => $value) {
                    $denominator = ($total_sale == 0 || empty($total_sale)) ? 1 : $total_sale;
                    $html = str_replace('$SALE_PER' . $flight, format_number($value / $denominator * 100) . '%', $html);
                }
    
                $html .= '</tbody>
                            <tfoot>
                            <tr class="footer-tr">
                                <td colspan="1" class="text-center fw-semibold">Tổng cộng</td>
                                <td colspan="2" class="text-end color-red fw-semibold">
                                    <div class="d-flex align-items-center justify-content-between gap-1">
                                        <div class="total_sale">' . format_number($total_sale) . '</div>
                                        <div class="hide-mobile total_sale_percent">&nbsp;&nbsp;(100%)</div>
                                    </div>
                                </td>
                                <td class="text-end color-red fw-semibold total_sale_ticket">' . $total_sale_ticket . '</td>
                                <td class="text-end total_sale_qty">' . $total_sale_qty . '</td>
                                <td colspan="2" class="text-end total__booking">' . format_number($total_bk) . '</td>
                                <td class="text-end total_qc">' . format_number($total_qc_cost) . '</td>
                            </tr>
                        </tfoot>
                    </table>';
            }
        }

        return $html;
    }

    function getDayInVietnamese($date)
    {
        $days = [
            'Monday' => 'Thứ Hai',
            'Tuesday' => 'Thứ Ba',
            'Wednesday' => 'Thứ Tư',
            'Thursday' => 'Thứ Năm',
            'Friday' => 'Thứ Sáu',
            'Saturday' => 'Thứ Bảy',
            'Sunday' => 'Chủ Nhật'
        ];
        $dayOfWeek = date('l', strtotime($date));
        return $days[$dayOfWeek];
    }
}
