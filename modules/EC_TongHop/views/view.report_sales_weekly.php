<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewreport_sales_weekly extends SugarView
{
    function display()
    {
        global $current_user;

        // Bảo trì
        // if ($current_user->user_name != 'hungnh') {
        // 	echo '<p class="alert alert-danger fw-semibold">Báo cáo doanh số Tuần đang cập nhật. Vui lòng quay lại sau!</p>';
        // 	exit();
        // }

        $smartyCont = new Sugar_Smarty();
        $this->populateContent($smartyCont);
        $smartyCont->display('modules/EC_TongHop/tpls/report_sales_weekly.tpl');
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
        if (isset($_POST) && !empty($_POST) && isset($_POST['btnSaveCostQc'])) {
            $this->saveCostQc();
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

        // Lưu giá trị QC vào file log


        // header("Location: index.php?module=EC_TongHop&action=businessreport&date_select=" . $date_select);
        return true;
    }

    function genInforReport($period)
    {
        global $db;

        $date_ranges    = [];
        $today          = date('Y-m-d');
        $period         = $_POST['date_select'] ?? $_GET['date_select'] ?? 'this_week';

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

            if (strtotime($from_date) <= strtotime($today)) {
                $html .= '<table class="table-details__booking table-report__weekly" cellpadding="0" cellspacing="0">
                            <thead>';

                // Headting time
                $fromDateFormat = date('d-m-Y', strtotime($from_date));
                $fromDayVN      = $this->getDayInVietnamese($from_date);
                $textTime = "{$fromDateFormat} ({$fromDayVN})";

                if (in_array($period, ['this_month', 'previous_month'])) {
                    $toDateFormat = date('d-m-Y', strtotime($to_date));
                    $toDayVN      = $this->getDayInVietnamese($to_date);

                    $textTime = "Từ {$fromDateFormat} ({$fromDayVN}) đến {$toDateFormat} ({$toDayVN})";
                }

                $html .= '
                        <tr>
                            <th colspan="8">
                                <div class="flex-between gap-2 flex-wrap">
                                    <span>' . $textTime . '</span>
                                    <span>
                                        CG nhỡ $QTY_MISSED_CALLS + CG đến $QTY_INBOUND_CALLS = $TOTAL_CALLS (CG tạo BK $QTY_CALLS_BK / Hoàn tất $QTY_CALLS_COM_BK)
                                    </span>
                                </div>
                            </th>
                        </tr>';
                $html .= '<tr>
                            <th rowspan="2">Trang web</th>
                            <th colspan="4">Doanh số</th>
                            <th colspan="2" rowspan="2">Tổng BK</th>
                            <th rowspan="2" class="d-none">Chi QC</th>
                        </tr>
                        <tr>
                            <th colspan="2">Số tiền</th>
                            <th>Vé</th>	
                            <th>BK OK</th>
                        </tr>	
                    </thead><tbody>';

                $sql_get = "SELECT
					t.last_name,
					t.user_name,
					t.user_id,
                    SUM(t.total_qty)             AS total_qty,
                    SUM(t.total_qty_com)         AS total_qty_com,
                    SUM(t.total_ticket_qty)      AS total_ticket_qty,
                    SUM(t.total_profit)      	 AS total_profit,
                    SUM(t.total_profit_inter)    AS total_profit_inter,

                    SUM(t.inbound) AS inbound,
                    SUM(t.missed) AS missed,
                    SUM(t.inbound_bk) AS inbound_bk,
                    SUM(t.inbound_bk_com) AS inbound_bk_com
                FROM (
                    -- Block 1: Doanh số booking
                    SELECT
						u.last_name,
						u.user_name,
						u.id AS user_id,
                        0 AS total_qty,
                        COUNT(bk.id) AS total_qty_com,
                        SUM(bk.ticket_qty) AS total_ticket_qty,
                        SUM(bk.total_profit) AS total_profit,
                        SUM(CASE WHEN bk.ticket_type = 2 THEN bk.total_profit ELSE 0 END) AS total_profit_inter,

                        0 AS inbound,
                        0 AS missed,
                        0 AS inbound_bk,
                        0 AS inbound_bk_com
                    FROM ec_revenue bk
					LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
                    WHERE bk.deleted = 0
                    AND DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR) BETWEEN '$from_date' AND '$to_date 23:59:59'
                    GROUP BY u.id, bk.booking_id

                    -- Block 2: Cuộc gọi
                    UNION ALL
					SELECT
						u.last_name,
						u.user_name,
						u.id AS user_id,
						0 AS total_qty,
						0 AS total_qty_com,
						0 AS total_ticket_qty,
						0 AS total_profit,
						0 AS total_profit_inter,

						SUM(CASE WHEN c.direction = 'inbound' THEN 1 ELSE 0 END) AS inbound,
						SUM(CASE WHEN c.direction = 'missed'  THEN 1 ELSE 0 END) AS missed,
						SUM(CASE WHEN c.direction = 'inbound' AND c.booking_id IS NOT NULL AND c.booking_id <> '' THEN 1 ELSE 0 END) AS inbound_bk,
						SUM(CASE WHEN c.direction = 'inbound' AND c.booking_id IS NOT NULL AND c.booking_id <> '' AND bk.booking_status = 8 THEN 1 ELSE 0 END) AS inbound_bk_com
					FROM calls c
					LEFT JOIN users u ON c.call_sources = u.last_name AND u.deleted = 0
                    LEFT JOIN ec_flight_bookings bk ON bk.id = c.booking_id AND bk.deleted = 0
					WHERE c.deleted = 0
					AND u.title = 'Bot'
                    AND DATE_ADD(c.date_entered, INTERVAL 7 HOUR) BETWEEN '$from_date' AND '$to_date 23:59:59'
					GROUP BY u.id

                    -- Block 3: Các giá trị ngoài hoàn tất (Tổng số BK, ...)
                    UNION ALL
                    SELECT
                        u.last_name,
                        u.user_name,
                        u.id AS user_id,
                        COUNT(bk.id) AS total_qty,
                        0 AS total_qty_com,
                        0 AS total_ticket_qty,
                        0 AS total_profit,
                        0 AS total_profit_inter,
                        0 AS inbound,
                        0 AS missed,
                        0 AS inbound_bk,
                        0 AS inbound_bk_com
                    FROM ec_flight_bookings bk
                    LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
                    WHERE bk.deleted = 0
                    AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '$from_date' AND '$to_date 23:59:59'
                    GROUP BY u.id
                ) t
                GROUP BY user_id
                ORDER BY total_profit DESC
            ";

                // pr($sql_get);

                $res = $db->query($sql_get);
                $row_count     = $db->countRows($res);
                $total_sale = $total_sale_ticket = $total_sale_qty = $total_bk = $total_qc_cost = 0;
                $qty_missed_calls = $qty_inbound_calls = $total_calls = $qty_calls_bk = $qty_calls_com_bk = 0;

                if ((int)$row_count > 0) {
                    while ($row = $db->fetchByAssoc($res)) {
                        $total_sale         += (int)$row['total_profit'];
                        $total_sale_ticket  += (int)$row['total_ticket_qty'];
                        $total_sale_qty     += (int)$row['total_qty_com'];
                        $total_bk           += (int)$row['total_qty'];
                        $total_qc_cost      += (int)$row['advertisement_cost'] ?? 0;

                        $qty_missed_calls      += (int)$row['missed'];
                        $qty_inbound_calls      += (int)$row['inbound'];
                        $total_calls      += ((int)$row['missed'] + (int)$row['inbound']);
                        $qty_calls_bk      += (int)$row['inbound_bk'];
                        $qty_calls_com_bk      += (int)$row['inbound_bk_com'];

                        if (in_array($period, ['this_month', 'previous_month'])) {
                            $data_time = 'Từ ' . $from_date . ' (' . $this->getDayInVietnamese($from_date) . ') đến ' . $to_date . ' (' . $this->getDayInVietnamese($to_date) . ')';
                        } else {
                            $data_time = date('d-m-Y', strtotime($from_date)) . ' (' . $this->getDayInVietnamese($from_date) . ')';
                        }

                        $denominator_total = ($row['total_qty'] == 0 || empty($row['total_qty'])) ? 1 : $row['total_qty'];
                        $btn_add_qc = '<button class="btn btn-primary btn-add-qc-cost px-1" data-bs-toggle="modal" data-bs-target="#modalEditCostQc" data-from-date="' . $from_date . '" data-to-date="' . $to_date . '" data-user-id="' . $row['user_id'] . '" data-site="' . $row['last_name'] . '" data-time="' . $data_time . '">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                                            </svg>
                                        </button>';
                        $cost_qc = empty($row['advertisement_cost']) ? $btn_add_qc : format_number($row['advertisement_cost'] ?? 0);

                        $html .= '
                                <tr>
                                    <td class="text-start fw-semibold">' . $row['last_name'] . '</td>
        
                                    <td colspan="2" class="text-start">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <div class="total text-start">' . format_number($row['total_profit']) . '</div>
                                        </div>
                                    </td>
                                    <td class="text-center total_ticket">' . $row['total_ticket_qty'] . '</td>
                                    <td class="text-center fw-semibold color-blue">' . format_number($row['total_qty_com']) . '</td>
                                    <td colspan="2">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <div class="total_percent text-end">(' . format_number(($row['total_qty_com']) / $denominator_total * 100) . '%)</div>
                                            <div class="hide-mobile total text-end color-red fw-semibold">&nbsp;&nbsp;' . format_number($row['total_qty']) . '</div>
                                        </div>
                                    </td>
                                    <td class="text-center d-none">
                                        ' . $cost_qc . ' 
                                    </td>
                                </tr>';
                    }
                } else {
                    $html .= '<tr>
                                <td colspan="8">Không có dữ liệu</td>
                            </tr>';
                }

                // Line Summary calls
                $html = str_replace('$QTY_MISSED_CALLS', format_number($qty_missed_calls), $html);
                $html = str_replace('$QTY_INBOUND_CALLS', format_number($qty_inbound_calls), $html);
                $html = str_replace('$TOTAL_CALLS', format_number($total_calls), $html);
                $html = str_replace('$QTY_CALLS_BK', format_number($qty_calls_bk), $html);
                $html = str_replace('$QTY_CALLS_COM_BK', format_number($qty_calls_com_bk), $html);

                $html .= '</tbody>
                            <tfoot>
                                <tr class="footer-tr">
                                    <td colspan="1" class="text-center fw-semibold">Tổng cộng</td>
                                    <td colspan="2" class="text-end color-red fw-semibold">
                                        <div class="total_sale">' . format_number($total_sale) . '</div>
                                    </td>
                                    <td class="text-end color-red fw-semibold total_sale_ticket">' . $total_sale_ticket . '</td>
                                    <td class="text-end total_sale_qty">' . $total_sale_qty . '</td>
                                    <td colspan="2" class="text-end total__booking">' . format_number($total_bk) . '</td>
                                    <td class="text-end total_qc d-none">' . format_number($total_qc_cost) . '</td>
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
