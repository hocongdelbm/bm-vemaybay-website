<?php
if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewreport_sales_create extends SugarView
{
    function display()
    {
        global $current_user;
        $user_title = $current_user->title;

        // Bảo trì
        // if ($current_user->user_name != 'hungnh') {
        // 	echo '<p class="alert alert-warning fw-semibold">Báo cáo doanh số booking đang bảo trì. Vui lòng quay lại sau!</p>';
        // 	exit();
        // }

        if (
            is_admin($current_user) ||
            $user_title == 'QuanLy'
        ) {
            $smarty = new Sugar_Smarty();
            $this->populateContent($smarty);
            $smarty->display('modules/EC_TongHop/tpls/report_sales_create.tpl');
        } else {
            header("Location: index.php?module=EC_TongHop&action=Error&error_string=" . urlencode("Bạn không có quyền xem báo cáo này. Vui lòng liên hệ quản trị viên để được cấp quyền."));
            exit;
        }
    }

    function populateContent($smarty)
    {
       global $db, $current_user;

        $smarty->assign('MODULE_NAME', $this->bean->object_name);
        $smarty->assign('MODULE_ACTION', 'report_sales_issue');

        $from_date          = isset($_REQUEST['from_date']) ? preg_replace('/[^0-9\-]/', '', $_REQUEST['from_date']) : date('Y-m-d');
        $to_date            = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9\-]/', '', $_REQUEST['to_date']) : date('Y-m-d');
        $fr_date_arr        = explode('-', $from_date); // 0=>day, 1=>month, 2=>year
        $to_date_arr        = explode('-', $to_date);

        // From date
        if (!empty($from_date) && checkdate((int)$fr_date_arr[1], (int)$fr_date_arr[0], (int)$fr_date_arr[2])) {
            $from_date_sql = date('Y-m-d', strtotime($from_date));
            $from_date_value = $from_date;
        } else {
            $from_date_sql = date('Y-m-d');
            $from_date_value = date('d-m-Y');
        }
        $from_date_sql_yesterday = date('Y-m-d', strtotime($from_date_sql . ' -1 day'));
        $from_date_sql_daybefore = date('Y-m-d', strtotime($from_date_sql . ' -2 day'));
        $smarty->assign('FROM_DATE_VALUE', date('d-m-Y', strtotime($from_date_value)));

        // To date
        if (!empty($to_date) && checkdate((int)$to_date_arr[1], (int)$to_date_arr[0], (int)$to_date_arr[2])) {
            $to_date_sql = date('Y-m-d', strtotime($to_date));
            $to_date_value = $to_date;
        } else {
            $to_date_sql = date('Y-m-d');
            $to_date_value = date('d-m-Y');
        }
        $to_date_sql_yesterday   = date('Y-m-d', strtotime($to_date_sql . ' -1 day'));
        $to_date_sql_daybefore   = date('Y-m-d', strtotime($to_date_sql . ' -2 day'));
        $smarty->assign('TO_DATE_VALUE', date('d-m-Y', strtotime($to_date_value)));

        switch (ceil(date('n') / 3)) {
            case 1:
                $quater_fromdate = '01-01-' . date('Y');
                $quater_todate = '31-03-' . date('Y');
                break;
            case 2:
                $quater_fromdate = '01-04-' . date('Y');
                $quater_todate = '30-06-' . date('Y');
                break;
            case 3:
                $quater_fromdate = '01-07-' . date('Y');
                $quater_todate = '30-09-' . date('Y');
                break;
            case 4:
                $quater_fromdate = '01-10-' . date('Y');
                $quater_todate = '31-12-' . date('Y');
                break;
            default:
                break;
        }

        $arr_date = array(
            '<option value="" fromdate="" todate="">---Trống---</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'today' ? 'selected' : '') . ' value="today" fromdate="' . date('d-m-Y') . '" todate="' . date('d-m-Y') . '">Hôm nay</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'yesterday' ? 'selected' : '') . ' value="yesterday" fromdate="' . date('d-m-Y', strtotime('-1 day')) . '" todate="' . date('d-m-Y', strtotime('-1 day')) . '">Hôm qua</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'this_week' ? 'selected' : '') . ' value="this_week" fromdate="' . date('d-m-Y', strtotime('monday this week')) . '" todate="' . date('d-m-Y', strtotime('sunday this week')) . '">Tuần này</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'previous_week' ? 'selected' : '') . ' value="previous_week" fromdate="' . date('d-m-Y', strtotime('monday previous week')) . '" todate="' . date('d-m-Y', strtotime('sunday previous week')) . '">Tuần trước</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'this_month' ? 'selected' : '') . ' value="this_month" fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'previous_month' ? 'selected' : '') . ' value="previous_month" fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'quarter_this' ? 'selected' : '') . ' value="quarter_this" fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'quarter_previous' ? 'selected' : '') . ' value="quarter_previous" fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'this_year' ? 'selected' : '') . ' value="this_year" fromdate="' . date('01-01-Y') . '" todate="' . date('31-12-Y') . '">Năm nay</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'previous_year' ? 'selected' : '') . ' value="previous_year" fromdate="' . date('01-01-Y', strtotime('-1 year')) . '" todate="' . date('31-12-Y', strtotime('-1 year')) . '">Năm trước</option>',
        );
        $smarty->assign('DATE_OPTION', implode('', $arr_date));

        $sel = ($_POST['date_select'] ?? 'today');
        // Helpers
        $shiftDays = function ($day, $deltaDays) {
            return date('Y-m-d', strtotime("$day $deltaDays"));
        };
        $shiftRange = function ($from, $to, $step) {
            $days = (strtotime($to) - strtotime($from)) / 86400 + 1;
            $offset = -$days * $step;

            return [
                'from' => date('Y-m-d', strtotime("$from $offset days")),
                'to'   => date('Y-m-d', strtotime("$to   $offset days")),
            ];
        };
        $mkRange = function ($fromDay, $toDay) {
            return ['from' => $fromDay, 'to' => $toDay];
        };
        $mkWeekRange = function ($base, $shiftWeek = 0) {
            $monday = date('Y-m-d', strtotime("monday this week $shiftWeek week", strtotime($base)));
            $sunday = date('Y-m-d', strtotime("sunday this week $shiftWeek week", strtotime($base)));
            return ['from' => $monday, 'to' => $sunday];
        };
        $mkMonthRange = function ($base, $shiftMonth = 0) {
            $from = date('Y-m-01', strtotime("$base $shiftMonth month"));
            $to   = date('Y-m-t',  strtotime("$base $shiftMonth month"));
            return ['from' => $from, 'to' => $to];
        };
        $mkQuarterRange = function ($base, $shiftQuarter = 0) {
            $ts = strtotime("$base " . ($shiftQuarter * 3) . " months");

            $year    = (int)date('Y', $ts);
            $month   = (int)date('n', $ts);
            $quarter = (int)ceil($month / 3);

            $startMonth = ($quarter - 1) * 3 + 1;

            $from = date('Y-m-d', strtotime("$year-$startMonth-01"));
            $to   = date('Y-m-t', strtotime("$from +2 months"));

            return ['from' => $from, 'to' => $to];
        };
        $mkYearRange = function ($base, $shiftYear = 0) {
            $year = (int)date('Y', strtotime("$base $shiftYear year"));
            return [
                'from' => "$year-01-01",
                'to'   => "$year-12-31",
            ];
        };

        $sel = ($_POST['date_select'] ?? 'today');
        $title_current = 'Hiện tại: mốc thời gian hôm nay, hôm qua và hôm trước';
        $title_prev1 = 'Cùng kỳ: so sánh với mốc thời gian hiện tại ở trên (07 ngày)';
        $title_prev2 = 'Cùng kỳ kế tiếp: so sánh với mốc thời gian hiện tại ở trên (14 ngày)';
        switch ($sel) {
            case 'today':
            case 'yesterday': {
                    $ranges = [
                        // ===== HÔM NAY =====
                        'today_current' => $mkRange($from_date_sql, $to_date_sql),
                        'today_prev1'   => $mkRange(
                            $shiftDays($from_date_sql, '-7 days'),
                            $shiftDays($to_date_sql,   '-7 days')
                        ),
                        'today_prev2'   => $mkRange(
                            $shiftDays($from_date_sql, '-14 days'),
                            $shiftDays($to_date_sql,   '-14 days')
                        ),

                        // ===== HÔM QUA =====
                        'yesterday_current' => $mkRange($from_date_sql_yesterday, $to_date_sql_yesterday),
                        'yesterday_prev1'   => $mkRange(
                            $shiftDays($from_date_sql_yesterday, '-7 days'),
                            $shiftDays($to_date_sql_yesterday,   '-7 days')
                        ),
                        'yesterday_prev2'   => $mkRange(
                            $shiftDays($from_date_sql_yesterday, '-14 days'),
                            $shiftDays($to_date_sql_yesterday,   '-14 days')
                        ),

                        // ===== HÔM KIA =====
                        'daybefore_current' => $mkRange($from_date_sql_daybefore, $to_date_sql_daybefore),
                        'daybefore_prev1'   => $mkRange(
                            $shiftDays($from_date_sql_daybefore, '-7 days'),
                            $shiftDays($to_date_sql_daybefore,   '-7 days')
                        ),
                        'daybefore_prev2'   => $mkRange(
                            $shiftDays($from_date_sql_daybefore, '-14 days'),
                            $shiftDays($to_date_sql_daybefore,   '-14 days')
                        ),
                    ];
                    break;
                }
            case 'this_week':
            case 'previous_week': {
                    $weekAnchor = $from_date_sql;

                    $ranges = [
                        // ===== CURRENT =====
                        'today_current'     => $mkWeekRange($weekAnchor, 0),
                        'yesterday_current' => $mkWeekRange($weekAnchor, 0),
                        'daybefore_current' => $mkWeekRange($weekAnchor, 0),

                        // ===== PREV 1 =====
                        'today_prev1'     => $mkWeekRange($weekAnchor, -1),
                        'yesterday_prev1' => $mkWeekRange($weekAnchor, -1),
                        'daybefore_prev1' => $mkWeekRange($weekAnchor, -1),

                        // ===== PREV 2 =====
                        'today_prev2'     => $mkWeekRange($weekAnchor, -2),
                        'yesterday_prev2' => $mkWeekRange($weekAnchor, -2),
                        'daybefore_prev2' => $mkWeekRange($weekAnchor, -2),
                    ];
                    $title_current = 'Hiện tại: Tuần đang chọn';
                    $title_prev1 = 'Cùng kỳ: so sánh với tuần đang chọn hiện tại ở trên';
                    $title_prev2 = 'Cùng kỳ kế tiếp: so sánh với tuần cùng kỳ ở trên';
                    break;
                }
            case 'this_month':
            case 'previous_month': {
                    $monthAnchor = $from_date_sql;

                    $ranges = [
                        // ===== CURRENT =====
                        'today_current'     => $mkMonthRange($monthAnchor, 0),
                        'yesterday_current' => $mkMonthRange($monthAnchor, 0),
                        'daybefore_current' => $mkMonthRange($monthAnchor, 0),

                        // ===== PREV 1 =====
                        'today_prev1'     => $mkMonthRange($monthAnchor, -1),
                        'yesterday_prev1' => $mkMonthRange($monthAnchor, -1),
                        'daybefore_prev1' => $mkMonthRange($monthAnchor, -1),

                        // ===== PREV 2 =====
                        'today_prev2'     => $mkMonthRange($monthAnchor, -2),
                        'yesterday_prev2' => $mkMonthRange($monthAnchor, -2),
                        'daybefore_prev2' => $mkMonthRange($monthAnchor, -2),
                    ];
                    $title_current = 'Hiện tại: Tháng đang chọn';
                    $title_prev1 = 'Cùng kỳ: so sánh với tháng đang chọn hiện tại ở trên';
                    $title_prev2 = 'Cùng kỳ kế tiếp: so sánh với tháng cùng kỳ ở trên';
                    break;
                }
            case 'quarter_this':
            case 'quarter_previous': {
                    $quarterAnchor = $from_date_sql;
                    $offset = ($sel === 'quarter_previous') ? -1 : 0;

                    $ranges = [
                        // ===== CURRENT =====
                        'today_current'     => $mkQuarterRange($quarterAnchor, $offset),
                        'yesterday_current' => $mkQuarterRange($quarterAnchor, $offset),
                        'daybefore_current' => $mkQuarterRange($quarterAnchor, $offset),

                        // ===== PREV 1 =====
                        'today_prev1'     => $mkQuarterRange($quarterAnchor, $offset - 1),
                        'yesterday_prev1' => $mkQuarterRange($quarterAnchor, $offset - 1),
                        'daybefore_prev1' => $mkQuarterRange($quarterAnchor, $offset - 1),

                        // ===== PREV 2 =====
                        'today_prev2'     => $mkQuarterRange($quarterAnchor, $offset - 2),
                        'yesterday_prev2' => $mkQuarterRange($quarterAnchor, $offset - 2),
                        'daybefore_prev2' => $mkQuarterRange($quarterAnchor, $offset - 2),
                    ];
                    $title_current = 'Hiện tại: Quý đang chọn';
                    $title_prev1 = 'Cùng kỳ: so sánh với quý đang chọn hiện tại ở trên';
                    $title_prev2 = 'Cùng kỳ kế tiếp: so sánh với quý cùng kỳ ở trên';
                    break;
                }
            case 'this_year':
            case 'previous_year': {
                    $yearAnchor = $from_date_sql;
                    $offset = ($sel === 'previous_year') ? -1 : 0;

                    $ranges = [
                        // ===== CURRENT =====
                        'today_current'     => $mkYearRange($yearAnchor, $offset),
                        'yesterday_current' => $mkYearRange($yearAnchor, $offset),
                        'daybefore_current' => $mkYearRange($yearAnchor, $offset),

                        // ===== PREV 1 =====
                        'today_prev1'     => $mkYearRange($yearAnchor, $offset - 1),
                        'yesterday_prev1' => $mkYearRange($yearAnchor, $offset - 1),
                        'daybefore_prev1' => $mkYearRange($yearAnchor, $offset - 1),

                        // ===== PREV 2 =====
                        'today_prev2'     => $mkYearRange($yearAnchor, $offset - 2),
                        'yesterday_prev2' => $mkYearRange($yearAnchor, $offset - 2),
                        'daybefore_prev2' => $mkYearRange($yearAnchor, $offset - 2),
                    ];
                    $title_current = 'Hiện tại: Năm đang chọn';
                    $title_prev1 = 'Cùng kỳ: so sánh với năm đang chọn hiện tại ở trên';
                    $title_prev2 = 'Cùng kỳ kế tiếp: so sánh với năm cùng kỳ ở trên';
                    break;
                }
            default: {
                    $anchorFrom = $from_date_sql;
                    $anchorTo   = $to_date_sql;

                    $current = $mkRange($anchorFrom, $anchorTo);
                    $prev1   = $shiftRange($anchorFrom, $anchorTo, 1);
                    $prev2   = $shiftRange($anchorFrom, $anchorTo, 2);

                    $ranges = [
                        // ===== CURRENT =====
                        'today_current'     => $current,
                        'yesterday_current' => $current,
                        'daybefore_current' => $current,

                        // ===== PREV 1 =====
                        'today_prev1'     => $prev1,
                        'yesterday_prev1' => $prev1,
                        'daybefore_prev1' => $prev1,

                        // ===== PREV 2 =====
                        'today_prev2'     => $prev2,
                        'yesterday_prev2' => $prev2,
                        'daybefore_prev2' => $prev2,
                    ];
                    break;
                }
        }

		// pr($ranges);

		$select_period = "CASE
                            WHEN DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_current']['from']}' AND '{$ranges['today_current']['to']}' THEN 'today_current'
                            WHEN DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_prev1']['from']}'   AND '{$ranges['today_prev1']['to']}'   THEN 'today_prev1'
                            WHEN DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_prev2']['from']}'   AND '{$ranges['today_prev2']['to']}'   THEN 'today_prev2'
                            WHEN DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_current']['from']}' AND '{$ranges['yesterday_current']['to']}' THEN 'yesterday_current'
                            WHEN DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_prev1']['from']}'   AND '{$ranges['yesterday_prev1']['to']}'   THEN 'yesterday_prev1'
                            WHEN DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_prev2']['from']}'   AND '{$ranges['yesterday_prev2']['to']}'   THEN 'yesterday_prev2'
                            WHEN DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_current']['from']}' AND '{$ranges['daybefore_current']['to']}' THEN 'daybefore_current'
                            WHEN DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_prev1']['from']}'   AND '{$ranges['daybefore_prev1']['to']}'   THEN 'daybefore_prev1'
                            WHEN DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_prev2']['from']}'   AND '{$ranges['daybefore_prev2']['to']}'   THEN 'daybefore_prev2'
                            ELSE 'unknown'
                        END AS period";
        $where_period = "AND (
                            DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_current']['from']}' AND '{$ranges['today_current']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_prev1']['from']}'   AND '{$ranges['today_prev1']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_prev2']['from']}'   AND '{$ranges['today_prev2']['to']}'

                            OR DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_current']['from']}' AND '{$ranges['yesterday_current']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_prev1']['from']}'   AND '{$ranges['yesterday_prev1']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_prev2']['from']}'   AND '{$ranges['yesterday_prev2']['to']}'

                            OR DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_current']['from']}' AND '{$ranges['daybefore_current']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_prev1']['from']}'   AND '{$ranges['daybefore_prev1']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_prev2']['from']}'   AND '{$ranges['daybefore_prev2']['to']}'
                        )";

        try {
			// get thông tin doanh số theo ngày tạo
            $sql = "SELECT
                    t.period,
                    CASE
                        WHEN t.period IN ('today_current', 'yesterday_current', 'daybefore_current')
                            THEN 'current'
                        WHEN t.period IN ('today_prev1', 'yesterday_prev1', 'daybefore_prev1')
                            THEN 'prev1'
                        WHEN t.period IN ('today_prev2', 'yesterday_prev2', 'daybefore_prev2')
                            THEN 'prev2'
                    END AS period_group,
                    CASE
                        WHEN t.period = 'today_current' THEN '{$ranges['today_current']['from']}'
                        WHEN t.period = 'today_prev1'   THEN '{$ranges['today_prev1']['from']}'
                        WHEN t.period = 'today_prev2'   THEN '{$ranges['today_prev2']['from']}'
                        WHEN t.period = 'yesterday_current' THEN '{$ranges['yesterday_current']['from']}'
                        WHEN t.period = 'yesterday_prev1'   THEN '{$ranges['yesterday_prev1']['from']}'
                        WHEN t.period = 'yesterday_prev2'   THEN '{$ranges['yesterday_prev2']['from']}'
                        WHEN t.period = 'daybefore_current' THEN '{$ranges['daybefore_current']['from']}'
                        WHEN t.period = 'daybefore_prev1'   THEN '{$ranges['daybefore_prev1']['from']}'
                        WHEN t.period = 'daybefore_prev2'   THEN '{$ranges['daybefore_prev2']['from']}'
                    END AS from_date,
                    CASE
                        WHEN t.period = 'today_current' THEN '{$ranges['today_current']['to']}'
                        WHEN t.period = 'today_prev1'   THEN '{$ranges['today_prev1']['to']}'
                        WHEN t.period = 'today_prev2'   THEN '{$ranges['today_prev2']['to']}'
                        WHEN t.period = 'yesterday_current' THEN '{$ranges['yesterday_current']['to']}'
                        WHEN t.period = 'yesterday_prev1'   THEN '{$ranges['yesterday_prev1']['to']}'
                        WHEN t.period = 'yesterday_prev2'   THEN '{$ranges['yesterday_prev2']['to']}'
                        WHEN t.period = 'daybefore_current' THEN '{$ranges['daybefore_current']['to']}'
                        WHEN t.period = 'daybefore_prev1'   THEN '{$ranges['daybefore_prev1']['to']}'
                        WHEN t.period = 'daybefore_prev2'   THEN '{$ranges['daybefore_prev2']['to']}'
                    END AS to_date,
                    SUM(t.total_qty)             AS total_qty,
                    SUM(t.total_ticket_qty)      AS total_ticket_qty,
                    SUM(t.total_bk_2_3)          AS total_bk_2_3,
                    SUM(t.total_bk_4_6)          AS total_bk_4_6,
                    SUM(t.total_profit)          AS total_profit,
                    SUM(t.total_profit_domestic) AS total_profit_domestic,
                    SUM(t.total_profit_inter)    AS total_profit_inter,
                    SUM(t.inbound) AS inbound,
                    SUM(t.missed) AS missed,
                    SUM(t.inbound_bk) AS inbound_bk,
                    SUM(t.tham_khao_bk) AS tham_khao_bk,
                    SUM(t.total_qty_inter) AS total_qty_inter,
                    SUM(t.total_qty_inter_com) AS total_qty_inter_com
                FROM (
                    -- Block 1: Doanh số booking
                    SELECT
                        $select_period,
						u.last_name,
						u.user_name,
						u.id AS user_id,
                        COUNT(bk.id) AS total_qty,
                        SUM(bk.ticket_qty) AS total_ticket_qty,
                        SUM(bk.total_amount) AS total_amount,
                        SUM(bk.total_purchase) AS total_purchase,
                        SUM(bk.total_profit) AS total_profit,

                        -- Domestic
                        SUM(CASE WHEN bk.ticket_type = 1 THEN bk.total_amount ELSE 0 END) AS total_amount_domestic,
                        SUM(CASE WHEN bk.ticket_type = 1 THEN bk.total_purchase ELSE 0 END) AS total_purchase_domestic,
                        SUM(CASE WHEN bk.ticket_type = 1 THEN bk.total_profit ELSE 0 END) AS total_profit_domestic,
                        -- Inter
                        SUM(CASE WHEN bk.ticket_type = 2 THEN bk.total_amount ELSE 0 END) AS total_amount_inter,
                        SUM(CASE WHEN bk.ticket_type = 2 THEN bk.total_purchase ELSE 0 END) AS total_purchase_inter,
                        SUM(CASE WHEN bk.ticket_type = 2 THEN bk.total_profit ELSE 0 END) AS total_profit_inter,

                        -- Booking theo số vé
                        SUM(CASE WHEN bk.ticket_qty BETWEEN 2 AND 3 THEN 1 ELSE 0 END) AS total_bk_2_3,
                        SUM(CASE WHEN bk.ticket_qty BETWEEN 4 AND 6 THEN 1 ELSE 0 END) AS total_bk_4_6,
                        0 AS inbound,
                        0 AS missed,
                        0 AS inbound_bk,
                        0 AS tham_khao_bk,
                        0 AS total_qty_inter,
                        SUM(CASE WHEN bk.ticket_type = 2 THEN 1 ELSE 0 END) AS total_qty_inter_com
                    FROM ec_revenue bk
					LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
                    WHERE bk.deleted = 0
                    $where_period
                    GROUP BY period

                    -- Block 2: Cuộc gọi
                    UNION ALL
					SELECT
						" . str_replace('bk.date_entered', 'c.date_entered', $select_period) . ",
						u.last_name,
						u.user_name,
						u.id AS user_id,
						0 AS total_qty,
						0 AS total_ticket_qty,
						0 AS total_amount,
						0 AS total_purchase,
						0 AS total_profit,
						0 AS total_amount_domestic,
						0 AS total_purchase_domestic,
						0 AS total_profit_domestic,
						0 AS total_amount_inter,
						0 AS total_purchase_inter,
						0 AS total_profit_inter,
						0 AS total_bk_2_3,
						0 AS total_bk_4_6,
						SUM(CASE WHEN c.direction = 'inbound' THEN 1 ELSE 0 END) AS inbound,
						SUM(CASE WHEN c.direction = 'missed'  THEN 1 ELSE 0 END) AS missed,
						SUM(CASE WHEN c.direction = 'inbound' AND c.booking_id IS NOT NULL AND c.booking_id <> '' THEN 1 ELSE 0 END) AS inbound_bk,
                        0 AS tham_khao_bk,
                        0 AS total_qty_inter,
                        0 AS total_qty_inter_com
					FROM calls c
					LEFT JOIN users u ON c.call_sources = u.last_name AND u.deleted = 0
					WHERE bk.deleted = 0
					" . str_replace('bk.date_entered', 'c.date_entered', $where_period) . "
					GROUP BY period

                    -- Block 3: Booking tham khảo
                    UNION ALL
					SELECT
                        $select_period,
						0 AS total_qty,
						0 AS total_ticket_qty,
						0 AS total_amount,
						0 AS total_purchase,
						0 AS total_profit,
						0 AS total_amount_domestic,
						0 AS total_purchase_domestic,
						0 AS total_profit_domestic,
						0 AS total_amount_inter,
						0 AS total_purchase_inter,
						0 AS total_profit_inter,
						0 AS total_bk_2_3,
						0 AS total_bk_4_6,
						0 AS inbound,
						0 AS missed,
						0 AS inbound_bk,
                        SUM(CASE WHEN bk.is_reference = 1 THEN 1 ELSE 0 END) AS tham_khao_bk,
                        SUM(CASE WHEN bk.ticket_type = 2 THEN 1 ELSE 0 END) AS total_qty_inter,
                        0 AS total_qty_inter_com
					FROM ec_flight_bookings bk
					LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
					WHERE bk.deleted = 0
                    $where_period
					GROUP BY period
                ) t
                GROUP BY period
                ORDER BY
                    CASE t.period
                        WHEN 'today_current' THEN 1
                        WHEN 'yesterday_current' THEN 2
                        WHEN 'daybefore_current' THEN 3
                        WHEN 'today_prev1'   THEN 4
                        WHEN 'yesterday_prev1'   THEN 5
                        WHEN 'daybefore_prev1'   THEN 6
                        WHEN 'today_prev2'   THEN 7
                        WHEN 'yesterday_prev2'   THEN 8
                        WHEN 'daybefore_prev2'   THEN 9
                        ELSE 10
                    END;
            ";

			pr($sql);

            $sql = "
				SELECT
					period,
					last_name,
					user_name,
					user_id,
					SUM(bk_confirmed) AS bk_confirmed,
					SUM(bk_printed) AS bk_printed,
					SUM(bk_completed) AS bk_completed,
					SUM(bk_cancelled) AS bk_cancelled,
					SUM(total) AS total,
					SUM(total_sales) AS total_sales,
					SUM(total_ticket) AS total_ticket,
					SUM(my_bk) AS my_bk,
					SUM(com_my_bk) AS com_my_bk,
					SUM(khach_hang_bk) AS khach_hang_bk,
					SUM(com_khach_hang_bk) AS com_khach_hang_bk,
					SUM(tham_khao_bk) AS tham_khao_bk,
					SUM(com_tham_khao_bk) AS com_tham_khao_bk,
					SUM(bk_1_3_ticket) AS bk_1_3_ticket,
					SUM(com_1_3ticket_qty) AS com_1_3ticket_qty,
					SUM(com_1_3ticket) AS com_1_3ticket,
					SUM(bk_1to3ticket_sales) AS bk_1to3ticket_sales,
					SUM(bk_4_8_ticket) AS bk_4_8_ticket,
					SUM(com_4_8ticket_qty) AS com_4_8ticket_qty,
					SUM(com_4_8ticket) AS com_4_8ticket,
					SUM(bk_4to8ticket_sales) AS bk_4to8ticket_sales,
					SUM(prior_bk) AS prior_bk,
					SUM(com_prior_bk) AS com_prior_bk,
					SUM(com_prior_ticket) AS com_prior_ticket,
					SUM(prior_bk_sales) AS prior_bk_sales,
					SUM(bk_inter_ticket) AS bk_inter_ticket,
					SUM(com_inter_ticket_qty) AS com_inter_ticket_qty,
					SUM(com_inter_ticket) AS com_inter_ticket,
					SUM(bk_inter_ticket_sales) AS bk_inter_ticket_sales,
					SUM(inbound) AS c_inbound,
					SUM(missed) AS c_missed,
					SUM(inbound_bk) AS c_inbound_bk
				FROM
				(
					-- BLOCK 1: Main bookings > 1440 
					SELECT
						$select_period,
						u.last_name,
						u.user_name,
						u.id AS user_id,
						COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
						COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
						COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
						COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
						COUNT(bk.id) AS total,
						SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_amount - bk.total_bought_amount, 0)) AS total_sales,
						SUM(IF(bk.booking_status IN (3, 7, 8),(SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
						SUM(
							IFNULL((
								SELECT COUNT(DISTINCT parent_id)
								FROM ec_flight_bookings_audit
								WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi')
							), 0) 
							+ 
							(SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi'))
						) AS my_bk,
						SUM(
							IFNULL(
							(
								SELECT COUNT(DISTINCT parent_id)
								FROM ec_flight_bookings_audit
								WHERE parent_id = bk.id AND field_name = 'contact_name'
								AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi')
								AND bk.booking_status IN (3, 7, 8)
							), 0) 
							+ 
							(
								SELECT COUNT(DISTINCT id)
								FROM ec_flight_bookings
								WHERE id = bk.id AND contact_name IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi') AND booking_status IN (3, 7, 8)
							)
						) AS com_my_bk,
						IF(
							bk.contact_name NOT IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')
							AND bk.contact_name IS NOT NULL
							AND bk.contact_name <> ''
							AND NOT EXISTS (SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')),
							1,
							0
						) AS khach_hang_bk,
						IF(
							bk.contact_name NOT IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')
							AND bk.contact_name IS NOT NULL
							AND bk.contact_name <> ''
							AND bk.booking_status IN (3, 7, 8)
							AND NOT EXISTS (SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')),
							1,
							0
						) AS com_khach_hang_bk,
						SUM(
							IFNULL(
							(SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Tham Khao')),
							0
							) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ('Tham Khao'))
						) AS tham_khao_bk,
						SUM(
							IFNULL(
							(
								SELECT
									COUNT(DISTINCT parent_id)
								FROM
									ec_flight_bookings_audit
								WHERE
									parent_id = bk.id
									AND field_name = 'contact_name'
									AND before_value_string IN ('Tham Khao')
									AND bk.booking_status IN (3, 7, 8)
							),
							0
							) + (
							SELECT
								COUNT(DISTINCT id)
							FROM
								ec_flight_bookings
							WHERE
								id = bk.id
								AND contact_name IN ('Tham Khao')
								AND booking_status IN (3, 7, 8)
							)
						) AS com_tham_khao_bk,
						SUM(IF(bk.total_qty <= 3, 1, 0)) AS bk_1_3_ticket,
						SUM(IF(bk.total_qty <= 3 AND bk.booking_status IN (3, 7, 8), 1, 0)) AS com_1_3ticket_qty,
						SUM(IF(bk.total_qty <= 3 AND bk.booking_status IN (3, 7, 8), bk.total_qty, 0)) AS com_1_3ticket,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.total_qty <= 3, bk.total_amount - bk.total_bought_amount, 0)) AS bk_1to3ticket_sales,
						SUM(IF(bk.total_qty >= 4 AND bk.total_qty <= 8, 1, 0)) AS bk_4_8_ticket,
						SUM(
							IF(
							bk.total_qty >= 4
							AND bk.total_qty <= 8
							AND bk.booking_status IN (3, 7, 8),
							1,
							0
							)
						) AS com_4_8ticket_qty,
						SUM(
							IF(
							bk.total_qty >= 4
							AND bk.total_qty <= 8
							AND bk.booking_status IN (3, 7, 8),
							bk.total_qty,
							0
							)
						) AS com_4_8ticket,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.total_qty >= 4 AND bk.total_qty <= 8, bk.total_amount - bk.total_bought_amount, 0)) AS bk_4to8ticket_sales,
						0 AS prior_bk,
						0 AS com_prior_bk,
						0 AS com_prior_ticket,
						0 AS prior_bk_sales,
						SUM(IFNULL((SELECT IF(SUM(quantity), 1, 0) FROM ec_booking_details WHERE booking_id = bk.id AND bk.ticket_type = 2 AND deleted = 0), 0)) AS bk_inter_ticket,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, 1, 0)) AS com_inter_ticket_qty,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, bk.total_qty, 0)) AS com_inter_ticket,
						SUM(
							IF(
							bk.booking_status IN (3, 7, 8)
							AND bk.ticket_type = 2,
							bk.total_amount - bk.total_bought_amount,
							0
							)
						) AS bk_inter_ticket_sales,
						0 AS inbound,
						0 AS missed,
						0 AS inbound_bk,
						(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS ts_local
					FROM ec_flight_bookings bk
					LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
					WHERE u.title = 'Bot'
					$where_period
					AND bk.deleted = 0
					GROUP BY period, bk.id
					HAVING TIMESTAMPDIFF(MINUTE, ts_local, min_dep_time) > 1440 
						
					-- BLOCK 2: Luggage adjustments
					UNION ALL
					SELECT
						$select_period,
						IF(u.title = 'Bot', u.last_name, 'Chưa xác định') AS last_name,
						IF(u.title = 'Bot', u.user_name, '') AS user_name,
						IF(u.title = 'Bot', u.id, 'BK_UNK') AS user_id,
						0 AS bk_confirmed,
						0 AS bk_printed,
						0 AS bk_completed,
						0 AS bk_cancelled,
						0 AS total,
						- SUM(IF(bk.booking_status IN (3, 7, 8), IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0), 0)) AS total_sales,
						0 AS total_ticket,
						0 AS my_bk,
						0 AS com_my_bk,
						0 AS khach_hang_bk,
						0 AS com_khach_hang_bk,
						0 AS tham_khao_bk,
						0 AS com_tham_khao_bk,
						0 AS bk_1_3_ticket,
						0 AS com_1_3ticket_qty,
						0 AS com_1_3ticket,
						- SUM(
							IF(
							bk.id = (
								SELECT DISTINCT i.booking_id
								FROM ec_booking_itineraries i
								WHERE i.booking_id = bk.id
								AND i.deleted = 0
								AND TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), i.departure_date) > 1440
								AND bk.booking_status IN (3, 7, 8)
								AND bk.total_qty <= 3
							),
							IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0),
							0
							)
						) AS bk_1to3ticket_sales,
						0 AS bk_4_8_ticket,
						0 AS com_4_8ticket_qty,
						0 AS com_4_8ticket,
						- SUM(
							IF(
							bk.id = (
								SELECT DISTINCT i.booking_id
								FROM ec_booking_itineraries i
								WHERE i.booking_id = bk.id
								AND i.deleted = 0
								AND TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), i.departure_date) > 1440
								AND bk.booking_status IN (3, 7, 8)
								AND bk.total_qty >= 4
								AND bk.total_qty <= 9
							),
							IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0),
							0
							)
						) AS bk_4to8ticket_sales,
						0 AS prior_bk,
						0 AS com_prior_bk,
						0 AS com_prior_ticket,
						0 AS prior_bk_sales,
						0 AS bk_inter_ticket,
						0 AS com_inter_ticket_qty,
						0 AS com_inter_ticket,
						- SUM(
							IF(
							bk.id = (
								SELECT DISTINCT
									i.booking_id
								FROM
									ec_booking_itineraries i
								WHERE
									i.booking_id = bk.id
									AND i.deleted = 0
									AND TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), i.departure_date) > 1440
									AND bk.booking_status IN (3, 7, 8)
									AND bk.ticket_type = 2
							),
							IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0),
							0
							)
						) AS bk_inter_ticket_sales,
						0 AS inbound,
						0 AS missed,
						0 AS inbound_bk,
						(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS ts_local
					FROM ec_booking_passengers bk_psg
					JOIN ec_flight_bookings bk ON bk.id = bk_psg.booking_id AND bk_psg.deleted = 0
					JOIN users u ON bk.created_by = u.id AND u.deleted = 0
					WHERE (bk_psg.add_type IS NULL OR bk_psg.add_type = '')
					AND u.title = 'Bot'
					$where_period
					GROUP BY period, bk.id, user_id
					-- HAVING TIMESTAMPDIFF(MINUTE, ts_local, min_dep_time) > 1440 
					
					--  BLOCK 3: Prior bookings <=1440
					UNION ALL
					SELECT
						$select_period,
						u.last_name,
						u.user_name,
						u.id AS user_id,
						COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
						COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
						COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
						COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
						COUNT(bk.id) AS total,
						SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_amount - bk.total_bought_amount, 0)) AS total_sales,
						SUM(IF(bk.booking_status IN (3, 7, 8), (SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
						SUM(
							IFNULL(
							(
								SELECT
									COUNT(DISTINCT parent_id)
								FROM
									ec_flight_bookings_audit
								WHERE
									parent_id = bk.id
									AND field_name = 'contact_name'
									AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi')
							),
							0
							) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi'))
						) AS my_bk,
						SUM(
							IFNULL(
							(
								SELECT
									COUNT(DISTINCT parent_id)
								FROM
									ec_flight_bookings_audit
								WHERE
									parent_id = bk.id
									AND field_name = 'contact_name'
									AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi')
									AND bk.booking_status IN (3, 7, 8)
							),
							0
							) + (
							SELECT
								COUNT(DISTINCT id)
							FROM
								ec_flight_bookings
							WHERE
								id = bk.id
								AND contact_name IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi')
								AND booking_status IN (3, 7, 8)
							)
						) AS com_my_bk,
						IF(
							bk.contact_name NOT IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')
							AND bk.contact_name IS NOT NULL
							AND bk.contact_name <> ''
							AND NOT EXISTS (SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')),
							1,
							0
						) AS khach_hang_bk,
						IF(
							bk.contact_name NOT IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')
							AND bk.contact_name IS NOT NULL
							AND bk.contact_name <> ''
							AND bk.booking_status IN (3, 7, 8)
							AND NOT EXISTS (SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')),
							1,
							0
						) AS com_khach_hang_bk,
						SUM(
							IFNULL(
							(SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Tham Khao')),
							0
							) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ('Tham Khao'))
						) AS tham_khao_bk,
						SUM(
							IFNULL(
							(
								SELECT
									COUNT(DISTINCT parent_id)
								FROM
									ec_flight_bookings_audit
								WHERE
									parent_id = bk.id
									AND field_name = 'contact_name'
									AND before_value_string IN ('Tham Khao')
									AND bk.booking_status IN (3, 7, 8)
							),
							0
							) + (
							SELECT
								COUNT(DISTINCT id)
							FROM
								ec_flight_bookings
							WHERE
								id = bk.id
								AND contact_name IN ('Tham Khao')
								AND booking_status IN (3, 7, 8)
							)
						) AS com_tham_khao_bk,
						0 AS bk_1_3_ticket,
						0 AS com_1_3ticket_qty,
						0 AS com_1_3ticket,
						0 AS bk_1to3ticket_sales,
						0 AS bk_4_8_ticket,
						0 AS com_4_8ticket_qty,
						0 AS com_4_8ticket,
						0 AS bk_4to8ticket_sales,
						COUNT(bk.id) AS prior_bk,
						SUM(IF(bk.booking_status IN (3, 7, 8), 1, 0)) AS com_prior_bk,
						SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_qty, 0)) AS com_prior_ticket,
						-- SUM(IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount), 0)) AS prior_bk_sales,
						SUM(IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount)- IFNULL(p.luggage_total, 0), 0)) AS prior_bk_sales,
						SUM(
							IFNULL(
							(
								SELECT
									IF(SUM(quantity), 1, 0)
								FROM
									ec_booking_details
								WHERE
									booking_id = bk.id
									AND bk.ticket_type = 2
									AND deleted = 0
							),
							0
							)
						) AS bk_inter_ticket,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, 1, 0)) AS com_inter_ticket_qty,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, bk.total_qty, 0)) AS com_inter_ticket,
						SUM(
							IF(
							bk.booking_status IN (3, 7, 8)
							AND bk.ticket_type = 2,
							bk.total_amount - bk.total_bought_amount - (
								SELECT
									(IFNULL(luggage_purchase, 0)) + (IFNULL(luggage_purchase_inbound, 0))
								FROM
									ec_booking_passengers
								WHERE
									deleted = 0
									AND booking_id = bk.id
								ORDER BY
									bk.date_entered DESC
									LIMIT 1
							),
							0
							)
						) AS bk_inter_ticket_sales,
						0 AS inbound,
						0 AS missed,
						0 AS inbound_bk,
						(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS ts_local
					FROM ec_flight_bookings bk
					LEFT JOIN (
						SELECT
							booking_id,
							SUM(IFNULL(luggage_purchase, 0) + IFNULL(luggage_purchase_inbound, 0)) AS luggage_total
						FROM ec_booking_passengers
						WHERE deleted = 0
						AND (add_type IS NULL OR add_type = '')
						GROUP BY booking_id
					) p ON p.booking_id = bk.id
					LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
					WHERE u.title = 'Bot'
					$where_period
					AND bk.deleted = 0
					GROUP BY period, bk.id
					HAVING TIMESTAMPDIFF(MINUTE, ts_local, min_dep_time) <= 1440 
					
					-- BLOCK 4: Unknown bookings >1440 
					UNION ALL
					SELECT
						$select_period,
						'Booking chưa xác định' AS last_name,
						'' AS user_name,
						'BK_UNK' AS user_id,
						COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
						COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
						COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
						COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
						COUNT(bk.id) AS total,
						SUM(IF(bk.booking_status = 8, bk.total_amount - bk.total_bought_amount - bk.luggage_fee, 0)) AS total_sales,
						SUM(
							IF(
							bk.booking_status IN (3, 7, 8),
							(SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0),
							0
							)
						) AS total_ticket,
						0 AS my_bk,
						0 AS com_my_bk,
						0 AS khach_hang_bk,
						0 AS com_khach_hang_bk,
						0 AS tham_khao_bk,
						0 AS com_tham_khao_bk,
						0 AS bk_1_3_ticket,
						0 AS com_1_3ticket_qty,
						0 AS com_1_3ticket,
						0 AS bk_1to3ticket_sales,
						0 AS bk_4_8_ticket,
						0 AS com_4_8ticket_qty,
						0 AS com_4_8ticket,
						0 AS bk_4to8ticket_sales,
						0 AS prior_bk,
						0 AS com_prior_bk,
						0 AS com_prior_ticket,
						0 AS prior_bk_sales,
						0 AS bk_inter_ticket,
						0 AS com_inter_ticket_qty,
						0 AS com_inter_ticket,
						0 AS bk_inter_ticket_sales,
						0 AS inbound,
						0 AS missed,
						0 AS inbound_bk,
						(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS ts_local
					FROM ec_flight_bookings bk
					LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
					WHERE
					(
						(u.title = 'Bot' AND LOWER(bk.contact_name) IN ('tim chuyen bay', 'callnow', 'call now'))
						OR u.title <> 'Bot'
					)
					$where_period
					AND bk.deleted = 0
					GROUP BY period, bk.id
					HAVING TIMESTAMPDIFF(MINUTE, ts_local, min_dep_time) > 1440 
					
					-- BLOCK 5: Calls
					UNION ALL
					SELECT
						" . str_replace('bk.date_entered', 'c.date_entered', $select_period) . ",
						u.last_name,
						u.user_name,
						u.id AS user_id,
						0 AS bk_confirmed,
						0 AS bk_printed,
						0 AS bk_completed,
						0 AS bk_cancelled,
						0 AS total,
						0 AS total_sales,
						0 AS total_ticket,
						0 AS my_bk,
						0 AS com_my_bk,
						0 AS khach_hang_bk,
						0 AS com_khach_hang_bk,
						0 AS tham_khao_bk,
						0 AS com_tham_khao_bk,
						0 AS bk_1_3_ticket,
						0 AS com_1_3ticket_qty,
						0 AS com_1_3ticket,

						0 AS bk_1to3ticket_sales,
						0 AS bk_4_8_ticket,
						0 AS com_4_8ticket_qty,
						0 AS com_4_8ticket,
						0 AS bk_4to8ticket_sales,
						0 AS prior_bk,
						0 AS com_prior_bk,
						0 AS com_prior_ticket,
						0 AS prior_bk_sales,
						0 AS bk_inter_ticket,
						0 AS com_inter_ticket_qty,
						0 AS com_inter_ticket,
						0 AS bk_inter_ticket_sales,
						SUM(CASE WHEN c.direction = 'inbound' THEN 1 ELSE 0 END) AS inbound,
						SUM(CASE WHEN c.direction = 'missed'  THEN 1 ELSE 0 END) AS missed,
						SUM(CASE WHEN c.direction = 'inbound' AND c.booking_id IS NOT NULL AND c.booking_id <> '' THEN 1 ELSE 0 END) AS inbound_bk,
						NULL AS min_dep_time,
						DATE_ADD(c.date_entered, INTERVAL 7 HOUR) AS ts_local
					FROM
						calls c
						LEFT JOIN users u ON c.call_sources = u.last_name
						AND c.deleted = 0
					WHERE
					u.title = 'Bot'
					" . str_replace('bk.date_entered', 'c.date_entered', $where_period) . "
					AND c.deleted = 0
					GROUP BY period, u.id
				) agg
				GROUP BY period, user_id
				ORDER BY
				CASE
					WHEN period = 'current' THEN
						1
					WHEN period = 'prev1' THEN
						2
					ELSE
						3
				END,
				total_sales DESC";

            // $res = $this->bean->db->query($sql);

            $html = '<tbody><form id="booking_search" name="search_form" method="POST" action="index.php?module=EC_TongHop&action=ListView" target="_blank">';
            $i = 0;
            $mark_current = false;
            $mark_previous1 = false;
            $mark_previous2 = false;

            $total_completed = $total_cancelled = $total = $total_sale = $total_sale_qty = 0;
            $total_sale_ticket = $total_my_bk = $total_prior_bk = $total_1_3ticket_bk = 0;
            $total_4_8ticket_bk = $total_com_mybk = 0;
            $total_comprior = $total_com1_3ticket = $total_com4_8ticket = 0;
            $total_inter_bk = $total_com_inter_ticket_qty = $total_com_inter_ticket = $total_inter_ticket_sales = 0;
            $total_ticket_prior_bk = $total_sale_prior_bk = $total_ticket_1_3bk = $total_sale_1_3bk = $total_ticket_4_8bk = $total_sale_4_8bk = 0;
            $total_ticket_inter = $total_sale_inter = 0;
            $total_khach_hang_bk = 0;
            $total_com_khach_hang_bk = 0;
            $total_inbound = $total_missed = 0;
            $total_tham_khao_bk = 0;
            $total_com_tham_khao_bk = 0;
            $sales = array();

            // cộng dồn theo nhóm (period)
            $sub = [
                'total_sales' => 0,
                'total_ticket' => 0,
                'sale_qty' => 0,
                'total' => 0,
                'my_bk' => 0,
                'com_my_bk' => 0,
                'khach_hang_bk' => 0,
                'com_khach_hang_bk' => 0,
                'tham_khao_bk' => 0,
                'com_tham_khao_bk' => 0,
                'c_inbound' => 0,
                'c_missed' => 0,
                'c_inbound_bk' => 0,
                'prior_bk' => 0,
                'com_prior_bk' => 0,
                'com_prior_ticket' => 0,
                'prior_bk_sales' => 0,
                'bk_1_3_ticket' => 0,
                'com_1_3ticket_qty' => 0,
                'com_1_3ticket' => 0,
                'bk_1to3ticket_sales' => 0,
                'bk_4_8_ticket' => 0,
                'com_4_8ticket_qty' => 0,
                'com_4_8ticket' => 0,
                'bk_4to8ticket_sales' => 0,
                'bk_inter_ticket' => 0,
                'com_inter_ticket_qty' => 0,
                'com_inter_ticket' => 0,
                'bk_inter_ticket_sales' => 0,
                'bk_cancelled' => 0,
            ];
            $periodNow = null;

            // in subtotal của 1 period
            $printSubtotal = function (string $period, array $s, array $range) use (&$html) {
                $percent = $s['total'] ? round(($s['sale_qty'] / $s['total']) * 100, 1) : 0;
                $from_date = $range[$period]['from'];
                $to_date   = $range[$period]['to'];

                $html .= '
					<tr class="bg-light fw-semibold subtotal ' . $period . '">
						<td class="text-start">Tổng</td>

						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="total">' . format_number($s['total_sales']) . '</div>
							<div class="hide-mobile total_percent text-end">&nbsp;</div>
							</div>
						</td>

						<td class="text-center">' . format_number($s['total_ticket']) . '</td>
						<td class="text-center color-blue">' . format_number($s['sale_qty']) . '</td>

						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="total_percent text-start">(' . format_number($percent) . '%)</div>
							<div class="hide-mobile total text-end color-red fw-semibold show_detail_total show_detail" from_date="' . $from_date . '" to_date="' . $to_date . '" title="Chi tiết booking trong ngày">' . format_number($s['total']) . '</div>
							</div>
						</td>

						<td class="text-end">' . $s['my_bk'] . '&nbsp;/&nbsp;' . $s['com_my_bk'] . '</td>
						<td class="text-end">' . $s['khach_hang_bk'] . '&nbsp;/&nbsp;' . $s['com_khach_hang_bk'] . '</td>
						<td class="text-end">' . $s['tham_khao_bk'] . '&nbsp;/&nbsp;' . $s['com_tham_khao_bk'] . '</td>

						<td class="text-end">' . $s['c_inbound'] . ' / ' . $s['c_inbound_bk'] . '</td>
						<td class="text-end">' . $s['c_missed'] . '</td>

						<td class="text-end">' . $s['prior_bk'] . '&nbsp;/&nbsp;' . $s['com_prior_bk'] . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_prior_ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['prior_bk_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['bk_1_3_ticket']) . '&nbsp;/&nbsp;' . format_number($s['com_1_3ticket_qty']) . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_1_3ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['bk_1to3ticket_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['bk_4_8_ticket']) . '&nbsp;/&nbsp;' . format_number($s['com_4_8ticket_qty']) . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_4_8ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['bk_4to8ticket_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['bk_inter_ticket']) . '&nbsp;/&nbsp;' . format_number($s['com_inter_ticket_qty']) . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_inter_ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['bk_inter_ticket_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['bk_cancelled']) . '</td>
						<td class="text-end">' . ($s['total'] ? round($s['bk_cancelled'] / $s['total'] * 100, 1) : 0) . '%</td>
					</tr>';
            };

            // reset mảng cộng dồn nhóm
            $resetSub = function (array &$s) {
                foreach ($s as $k => $v) $s[$k] = 0;
            };

            while ($row = $this->bean->db->fetchByAssoc($res)) {
                if ($periodNow !== null && $row['period'] !== $periodNow) {
                    $printSubtotal($periodNow, $sub, $ranges);
                    $resetSub($sub);
                }
                $periodNow = $row['period'];

                $total_sale += $row['total_sales'];
                $total_sale_qty += ($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']);

                $sub['total_sales']          += (float)$row['total_sales'];
                $sub['total_ticket']         += (float)$row['total_ticket'];
                $sub['sale_qty']             += (int)$row['bk_confirmed'] + (int)$row['bk_printed'] + (int)$row['bk_completed'];
                $sub['total']                += (int)$row['total'];

                $sub['my_bk']                += (int)$row['my_bk'];
                $sub['com_my_bk']            += (int)$row['com_my_bk'];
                $sub['khach_hang_bk']        += (int)$row['khach_hang_bk'];
                $sub['com_khach_hang_bk']    += (int)$row['com_khach_hang_bk'];
                $sub['tham_khao_bk']         += (int)$row['tham_khao_bk'];
                $sub['com_tham_khao_bk']     += (int)$row['com_tham_khao_bk'];

                $sub['c_inbound']            += (int)$row['c_inbound'];
                $sub['c_missed']             += (int)$row['c_missed'];
                $sub['c_inbound_bk']         += (int)$row['c_inbound_bk'];

                $sub['prior_bk']             += (int)$row['prior_bk'];
                $sub['com_prior_bk']         += (int)$row['com_prior_bk'];
                $sub['com_prior_ticket']     += (int)$row['com_prior_ticket'];
                $sub['prior_bk_sales']       += (float)$row['prior_bk_sales'];

                $sub['bk_1_3_ticket']        += (int)$row['bk_1_3_ticket'];
                $sub['com_1_3ticket_qty']    += (int)$row['com_1_3ticket_qty'];
                $sub['com_1_3ticket']        += (int)$row['com_1_3ticket'];
                $sub['bk_1to3ticket_sales']  += (float)$row['bk_1to3ticket_sales'];

                $sub['bk_4_8_ticket']        += (int)$row['bk_4_8_ticket'];
                $sub['com_4_8ticket_qty']    += (int)$row['com_4_8ticket_qty'];
                $sub['com_4_8ticket']        += (int)$row['com_4_8ticket'];
                $sub['bk_4to8ticket_sales']  += (float)$row['bk_4to8ticket_sales'];

                $sub['bk_inter_ticket']      += (int)$row['bk_inter_ticket'];
                $sub['com_inter_ticket_qty'] += (int)$row['com_inter_ticket_qty'];
                $sub['com_inter_ticket']     += (int)$row['com_inter_ticket'];
                $sub['bk_inter_ticket_sales'] += (float)$row['bk_inter_ticket_sales'];

                $sub['bk_cancelled']         += (int)$row['bk_cancelled'];

                if (!empty($row['user_name'])) {
                    $total_completed += $row['bk_completed'];
                    $total_cancelled += $row['bk_cancelled'];
                    $total += $row['total'];
                    $total_my_bk += $row['my_bk'];

                    $total_ticket = '<a href="#" onclick="' . (empty($row['user_name']) ? 'document.getElementById(\'contact_name_advanced_OPER\').setAttribute(\'name\', \'contact_name_advanced_OPER\'); document.getElementById(\'contact_name_advanced\').setAttribute(\'name\', \'contact_name_advanced\');document.getElementById(\'created_by_name_advanced\').removeAttribute(\'name\'); ' : 'document.getElementById(\'contact_name_advanced_OPER\').removeAttribute(\'name\'); document.getElementById(\'contact_name_advanced\').removeAttribute(\'name\'); document.getElementById(\'created_by_name_advanced\').setAttribute(\'name\', \'created_by_name_advanced\'); document.getElementById(\'created_by_name_advanced\').value = \'' . $row['user_name'] . '\';') . 'document.getElementById(\'booking_status_advanced1\').setAttribute(\'name\', \'booking_status_advanced[]\'); document.getElementById(\'booking_status_advanced2\').setAttribute(\'name\', \'booking_status_advanced[]\'); document.getElementById(\'booking_status_advanced3\').setAttribute(\'name\', \'booking_status_advanced[]\'); document.getElementById(\'booking_search\').submit(); return false;">' . ($row['total_ticket']) . '</a>';
                } else {
                    $total_ticket = $row['total_ticket'];
                }

                $sales[$row['user_id']] = $row['total_sales'];
                $tham_khao_bk = ($row['tham_khao_bk'] == 0) ? 0 : format_number($row['tham_khao_bk']);
                $com_tham_khao_bk = ($row['com_tham_khao_bk'] == 0) ? 0 : format_number($row['com_tham_khao_bk']);
                $khach_hang_bk = ($row['khach_hang_bk'] == 0) ? 0 : format_number($row['khach_hang_bk']);
                $com_khach_hang_bk = ($row['com_khach_hang_bk'] == 0) ? 0 : format_number($row['com_khach_hang_bk']);

                // Booking booker
                $my_bk = ($row['my_bk'] == 0) ? 0 : format_number($row['my_bk']);
                $com_my_bk = ($row['com_my_bk'] == 0) ? 0 : format_number($row['com_my_bk']);

                // Vé cận
                $prior_bk = ($row['prior_bk'] == 0) ? 0 : format_number($row['prior_bk']);
                $com_prior_bk = ($row['com_prior_bk'] == 0) ? 0 : format_number($row['com_prior_bk']);
                $prior_bk_sales = format_number($row['prior_bk_sales']);
                $denominator_total = ($row['total'] == 0 || empty($row['total'])) ? 1 : $row['total'];

                // CALLS
                $total_inbound += (int) $row['c_inbound'];
                $total_missed += (int) $row['c_missed'];

                if ($row['period'] == 'prev1' && !$mark_previous1) {
                    $mark_previous1 = true;

                    $html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold">Cùng kỳ</span> 
									<span class="form-label text-dark fw-semibold">
										Từ ngày: <span class="text-danger">' . date('d-m-Y', strtotime($ranges['prev1']['from'])) . '</span>
										Đến ngày <span class="text-danger">' . date('d-m-Y', strtotime($ranges['prev1']['to'])) . '</span>
									</span> 
								</td>
							</tr>';
                } else if ($row['period'] == 'prev2' && !$mark_previous2) {
                    $mark_previous2 = true;

                    $html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold">Cùng kỳ trước</span> 
									<span class="form-label text-dark fw-semibold">
										Từ ngày: <span class="text-danger">' . date('d-m-Y', strtotime($ranges['prev2']['from'])) . '</span>
										Đến ngày <span class="text-danger">' . date('d-m-Y', strtotime($ranges['prev2']['to'])) . '</span>
									</span> 
								</td>
							</tr>';
                } else if ($row['period'] == 'current' && !$mark_current) {
                    $mark_current = true;

                    $html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold"></span> 
									<span class="form-label text-dark fw-semibold">
										Từ ngày: <span class="text-danger">' . date('d-m-Y', strtotime($ranges['current']['from'])) . '</span>
										Đến ngày <span class="text-danger">' . date('d-m-Y', strtotime($ranges['current']['to'])) . '</span>
									</span> 
								</td>
							</tr>';
                }

                $html .= '
						<tr>
							<td class="text-start fw-semibold">' . str_replace(".", " ", $row['last_name']) . '</td>

							<td colspan="2" class="text-start">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total text-start">' . format_number($row['total_sales']) . '</div>
									<div class="hide-mobile total_percent text-end">&nbsp;&nbsp;($SALE_PER' . $row['user_id'] . ')</div>
								</div>
							</td>

							<td class="text-center total_ticket">' . $total_ticket . '</td>
							<td class="text-center fw-semibold color-blue">' . format_number($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']) . '</td>
							
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_percent text-start">(' . format_number(($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']) / $denominator_total * 100) . '%)</div>
									<div class="hide-mobile total text-end color-red fw-semibold">&nbsp;&nbsp;' . format_number($row['total']) . '</div>
								</div>
							</td>
							
							<td class="text-end">
								<span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '"  type="show_booker_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . $my_bk . '&nbsp;/&nbsp;' . $com_my_bk . '</span>
							</td>
							
							<td class="text-end">
								<span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" type="show_khachhang_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . $khach_hang_bk . '&nbsp;/&nbsp;' . $com_khach_hang_bk . '</span>
							</td>
							<td class="text-end">
								<span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" type="show_thamkhao_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . $tham_khao_bk . '&nbsp;/&nbsp;' . $com_tham_khao_bk . '</span>
							</td>

							<td class="text-end c_inbound">
								<span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_detail_call" direction="inbound" user="' . $row['user_id'] . '">' . $row['c_inbound'] . ' / ' . $row['c_inbound_bk'] . '</span>
							</td>
							<td class="text-end c_missed">
								<span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_detail_call" direction="missed" user="' . $row['user_id'] . '">' . $row['c_missed'] . '</span>
							</td>
							
							<td class="text-end"><span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_prior_bk" user="' . $row['user_id'] . '">' . $prior_bk . '&nbsp;/&nbsp;' . $com_prior_bk . '</span></td>
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_prior_ticket text-start">(' . format_number($row['com_prior_ticket']) . '&nbsp;vé)</div>
									<div class="prior_bk_sales text-end">' . $prior_bk_sales . '</div>
								</div>
							</td>

							<td class="text-end"><span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_3ticket_bk" user="' . $row['user_id'] . '">' . format_number($row['bk_1_3_ticket']) . '&nbsp;/&nbsp;' . format_number($row['com_1_3ticket_qty']) . '</span></td>
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_1_3ticket text-start">(' . format_number($row['com_1_3ticket']) . '&nbsp;vé)</div>
									<div class="bk_1to3ticket_sales text-end">' . format_number($row['bk_1to3ticket_sales']) . '</div>
								</div>
							</td>

							<td class="text-end"><span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_4to8ticket_bk" user="' . $row['user_id'] . '">' . format_number($row['bk_4_8_ticket']) . '&nbsp;/&nbsp;' . format_number($row['com_4_8ticket_qty']) . '</span></td>
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_4_8ticket text-start">(' . format_number($row['com_4_8ticket']) . '&nbsp;vé)</div>
									<div class="new_4to8ticket_sales text-end">' . format_number($row['bk_4to8ticket_sales']) . '</div>
								</div>
							</td>

							<td class="text-end"><span class="show_detail_bk show_detail inter" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_inter_bk" user="' . $row['user_id'] . '">' . format_number($row['bk_inter_ticket']) . '&nbsp;/&nbsp;' . format_number($row['com_inter_ticket_qty']) . '</span></td>
							<td colspan="2" class="inter">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_inter_ticket text-start">(' . format_number($row['com_inter_ticket']) . '&nbsp;vé)</div>
									<div class="new_inter_ticket_sales text-end">' . format_number($row['bk_inter_ticket_sales']) . '</div>
								</div>
							</td>

							<td class="text-end bk_cancelled">' . format_number($row['bk_cancelled']) . '</td>
							<td class="text-end bk_cancelled_percent">' . round($row['bk_cancelled'] / $denominator_total * 100, 1) . '%</td>
						</tr>';
                $i++;
            }

            if ($periodNow !== null) {
                $printSubtotal($periodNow, $sub, $ranges); // in subtotal của current hoặc previous cuối
            }

            foreach ($sales as $flight => $value) {
                $denominator = ($total_sale == 0 || empty($total_sale)) ? 1 : $total_sale;
                $html = str_replace('$SALE_PER' . $flight, format_number($value / $denominator * 100) . '%', $html);
            }

            $html .= '<input type="hidden" name="searchFormTab" value="advanced_search">';
            $html .= '<input type="hidden" name="module" value="EC_Flight_Bookings">';
            $html .= '<input type="hidden" name="action" value="ListView">';
            $html .= '<input type="hidden" name="query" value="true">';
            $html .= '<input type="hidden" name="date_entered_advanced" value="' . $_POST['from_date'] . '">';
            $html .= '<input type="hidden" name="date_entered_advanced_upperbound" value="' . $_POST['to_date'] . '">';
            $html .= '<input type="hidden" name="created_by_name_advanced" id="created_by_name_advanced" />';
            $html .= '<input type="hidden" name="contact_name_advanced_OPER" id="contact_name_advanced_OPER" value="IN"/>';
            $html .= '<input type="hidden" name="contact_name_advanced" id="contact_name_advanced" value="tim chuyen bay, call now, callnow"/>';
            $html .= '<input type="hidden" name="booking_status_advanced[]" id="booking_status_advanced1" value="8"/>';
            $html .= '<input type="hidden" name="booking_status_advanced[]" id="booking_status_advanced2" value="7"/>';
            $html .= '<input type="hidden" name="booking_status_advanced[]" id="booking_status_advanced3" value="3"/>';

            $html .= '<input type="hidden" name="from" value="bkqtyreport"/>';
            $html .= '</form></tbody>';

            $html .= '<tfoot class="d-none">
						<tr class="footer-tr bg-secondary">
							<td colspan="1" class="text-center fw-semibold">Tổng cộng</td>
							<td colspan="2" class="text-end color-red fw-semibold">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_sale">' . format_number($total_sale) . '</div>
									<div class="hide-mobile total_sale_percent">&nbsp;&nbsp;(100%)</div>
								</div>
							</td>
							<td class="text-end color-red fw-semibold total_sale_ticket">' . $total_sale_ticket . '</td>
							<td class="text-end total_sale_qty">' . $total_sale_qty . '</td>
							<td colspan="2" class="text-end total__booking-today"><span class="show_detail_total show_detail" title="Chi tiết booking trong ngày">' . format_number($total) . '</span></td>
							<td class="text-end total_my_bk-today">' . format_number($total_my_bk) . '&nbsp;/&nbsp;' . format_number($total_com_mybk) . '</td>
							<td class="text-end">' . format_number($total_khach_hang_bk) . '&nbsp;/&nbsp;' . format_number($total_com_khach_hang_bk) . '</td>
							<td class="text-end">' . format_number($total_tham_khao_bk) . '&nbsp;/&nbsp;' . format_number($total_com_tham_khao_bk) . '</td>

							<td style="text-align: right; background-color: #068FFF;  color: #fff; ">' . $total_inbound . '</td>
							<td style="text-align: right; background-color: #068FFF;  color: #fff; ">' . $total_missed . '</td>

							<td class="text-end total_prior_bk-today">' . format_number($total_prior_bk) . '&nbsp;/&nbsp' . format_number($total_comprior) . '</td>
							<td colspan="2" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_prior_bk">' . format_number($total_ticket_prior_bk) . '</div>
									<div class="total_sale_prior_bk">' . format_number($total_sale_prior_bk) . '</div>
								</div>
							</td>

							<td class="text-end total_ticket_1_3">' . format_number($total_1_3ticket_bk) . '&nbsp;/&nbsp;' . format_number($total_com1_3ticket) . '</td>
							<td colspan="2" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_1_3bk">' . format_number($total_ticket_1_3bk) . '</div>
									<div class="total_sale_1_3bk">' . format_number($total_sale_1_3bk) . '</div>
								</div>
							</td>

							<td class="text-end total_ticket_4_8">' . format_number($total_4_8ticket_bk) . '&nbsp;/&nbsp;' . format_number($total_com4_8ticket) . '</td>
							<td colspan="2" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_4_8bk">' . format_number($total_ticket_4_8bk) . '</div>
									<div class="total_sale_4_8bk">' . format_number($total_sale_4_8bk) . '</div>
								</div>
							</td>

							<td class="text-end" style="background-color: #8BE8E5;">' . format_number($total_inter_bk) . '&nbsp;/&nbsp;' . format_number($total_com_inter_ticket_qty) . '</td>
							<td colspan="2" style="background-color: #8BE8E5;" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_inter">' . format_number($total_ticket_inter) . '</div>
									<div class="total_sale_inter">' . format_number($total_sale_inter) . '</div>
								</div>
							</td>
							
							<td style="text-align: right; background-color: #E94560; color: #fff">' . format_number($total_cancelled) . '</td>
							<td style="text-align: right; background-color: #E94560; color: #fff">' . round($total_cancelled / ($total > 0 ? $total : 1) * 100, 1) . '%</td>
						</tr>
					</tfoot>';

            return $html;
        } catch (Exception $e) {
            error_log("Exception in genBKSale: " . $e->getMessage());
            return '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
