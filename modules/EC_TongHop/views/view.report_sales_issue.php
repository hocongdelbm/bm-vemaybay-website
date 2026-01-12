<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewreport_sales_issue extends SugarView
{
    public $_loai_thu_str = "'4', '5', '10', '11', '12', '13', '14', '16'";

    function display()
    {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'view', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/' . $this->bean->object_name . '/tpls/report_sales_issue.tpl');
        } else {
            header("Location: index.php?module=" . $this->bean->object_name . "&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smartyobj)
    {
        global $db, $current_user;

        $smartyobj->assign('MODULE_NAME', $this->bean->object_name);
        $smartyobj->assign('MODULE_ACTION', 'report_sales_issue');

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
        $smartyobj->assign('FROM_DATE_VALUE', date('d-m-Y', strtotime($from_date_value)));

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

        $smartyobj->assign('TO_DATE_VALUE', date('d-m-Y', strtotime($to_date_value)));
        $smartyobj->assign('CURRENT_FROMDATE', date('d-m-Y', strtotime('first day of this month')));
        $smartyobj->assign('CURRENT_TODATE', date('d-m-Y', strtotime('last day of this month')));
        $smartyobj->assign('PREVIOUS_FROMDATE', date('d-m-Y', strtotime('first day of last month')));
        $smartyobj->assign('PREVIOUS_TODATE', date('t-m-Y', strtotime('last day of last month')));
        $smartyobj->assign('PREVIOUSYEAR_FROMDATE', date('d-m-Y', strtotime('-1 year')));
        $smartyobj->assign('PREVIOUSYEAR_TODATE', date('d-m-Y', strtotime('-1 year')));

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
        $smartyobj->assign('DATE_OPTION', implode('', $arr_date));

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
                            WHEN date_ticket_issue BETWEEN '{$ranges['today_current']['from']}' AND '{$ranges['today_current']['to']}' THEN 'today_current'
                            WHEN date_ticket_issue BETWEEN '{$ranges['today_prev1']['from']}'   AND '{$ranges['today_prev1']['to']}'   THEN 'today_prev1'
                            WHEN date_ticket_issue BETWEEN '{$ranges['today_prev2']['from']}'   AND '{$ranges['today_prev2']['to']}'   THEN 'today_prev2'
                            WHEN date_ticket_issue BETWEEN '{$ranges['yesterday_current']['from']}' AND '{$ranges['yesterday_current']['to']}' THEN 'yesterday_current'
                            WHEN date_ticket_issue BETWEEN '{$ranges['yesterday_prev1']['from']}'   AND '{$ranges['yesterday_prev1']['to']}'   THEN 'yesterday_prev1'
                            WHEN date_ticket_issue BETWEEN '{$ranges['yesterday_prev2']['from']}'   AND '{$ranges['yesterday_prev2']['to']}'   THEN 'yesterday_prev2'
                            WHEN date_ticket_issue BETWEEN '{$ranges['daybefore_current']['from']}' AND '{$ranges['daybefore_current']['to']}' THEN 'daybefore_current'
                            WHEN date_ticket_issue BETWEEN '{$ranges['daybefore_prev1']['from']}'   AND '{$ranges['daybefore_prev1']['to']}'   THEN 'daybefore_prev1'
                            WHEN date_ticket_issue BETWEEN '{$ranges['daybefore_prev2']['from']}'   AND '{$ranges['daybefore_prev2']['to']}'   THEN 'daybefore_prev2'
                            ELSE 'unknown'
                        END AS period";
        $where_period = "AND (
                            date_ticket_issue BETWEEN '{$ranges['today_current']['from']}' AND '{$ranges['today_current']['to']}'
                            OR date_ticket_issue BETWEEN '{$ranges['today_prev1']['from']}'   AND '{$ranges['today_prev1']['to']}'
                            OR date_ticket_issue BETWEEN '{$ranges['today_prev2']['from']}'   AND '{$ranges['today_prev2']['to']}'

                            OR date_ticket_issue BETWEEN '{$ranges['yesterday_current']['from']}' AND '{$ranges['yesterday_current']['to']}'
                            OR date_ticket_issue BETWEEN '{$ranges['yesterday_prev1']['from']}'   AND '{$ranges['yesterday_prev1']['to']}'
                            OR date_ticket_issue BETWEEN '{$ranges['yesterday_prev2']['from']}'   AND '{$ranges['yesterday_prev2']['to']}'

                            OR date_ticket_issue BETWEEN '{$ranges['daybefore_current']['from']}' AND '{$ranges['daybefore_current']['to']}'
                            OR date_ticket_issue BETWEEN '{$ranges['daybefore_prev1']['from']}'   AND '{$ranges['daybefore_prev1']['to']}'
                            OR date_ticket_issue BETWEEN '{$ranges['daybefore_prev2']['from']}'   AND '{$ranges['daybefore_prev2']['to']}'
                        )";

        try {
            // get thông tin doanh số theo ngày xuất vé
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
                    SUM(t.tham_khao_bk) AS tham_khao_bk
                FROM (
                    -- Block 1: Doanh số booking
                    SELECT
                        $select_period,
                        COUNT(id) AS total_qty,
                        SUM(ticket_qty) AS total_ticket_qty,
                        SUM(total_amount) AS total_amount,
                        SUM(total_purchase) AS total_purchase,
                        SUM(total_profit) AS total_profit,

                        -- Domestic
                        SUM(CASE WHEN ticket_type = 1 THEN total_amount ELSE 0 END) AS total_amount_domestic,
                        SUM(CASE WHEN ticket_type = 1 THEN total_purchase ELSE 0 END) AS total_purchase_domestic,
                        SUM(CASE WHEN ticket_type = 1 THEN total_profit ELSE 0 END) AS total_profit_domestic,
                        -- Inter
                        SUM(CASE WHEN ticket_type = 2 THEN total_amount ELSE 0 END) AS total_amount_inter,
                        SUM(CASE WHEN ticket_type = 2 THEN total_purchase ELSE 0 END) AS total_purchase_inter,
                        SUM(CASE WHEN ticket_type = 2 THEN total_profit ELSE 0 END) AS total_profit_inter,

                        -- Booking theo số vé
                        SUM(CASE WHEN ticket_qty BETWEEN 2 AND 3 THEN 1 ELSE 0 END) AS total_bk_2_3,
                        SUM(CASE WHEN ticket_qty BETWEEN 4 AND 6 THEN 1 ELSE 0 END) AS total_bk_4_6,
                        0 AS inbound,
                        0 AS missed,
                        0 AS inbound_bk,
                        0 AS tham_khao_bk
                    FROM ec_revenue
                    WHERE deleted = 0
                    $where_period
                    GROUP BY period

                    -- Block 2: Cuộc gọi
                    UNION ALL
					SELECT
						" . str_replace('date_ticket_issue', 'DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR))', $select_period) . ",
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
						SUM(CASE WHEN direction = 'inbound' THEN 1 ELSE 0 END) AS inbound,
						SUM(CASE WHEN direction = 'missed'  THEN 1 ELSE 0 END) AS missed,
						SUM(CASE WHEN direction = 'inbound' AND booking_id IS NOT NULL AND booking_id <> '' THEN 1 ELSE 0 END) AS inbound_bk,
                        0 AS tham_khao_bk
					FROM calls
					WHERE deleted = 0
					" . str_replace('date_ticket_issue', 'DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR))', $where_period) . "
					GROUP BY period

                    -- Block 3: Booking tham khảo
                    UNION ALL
					SELECT
						" . str_replace('date_ticket_issue', 'DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR))', $select_period) . ",
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
                        SUM(
							IFNULL(
							(SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Tham Khao')),
							0
							) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ('Tham Khao'))
						) AS tham_khao_bk
					FROM ec_flight_bookings bk
					WHERE deleted = 0
					" . str_replace('date_ticket_issue', 'DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR))', $where_period) . "
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

            // pr($sql);

            $html = '';
            $mark_current = false;
            $mark_previous1 = false;
            $mark_previous2 = false;

            if (is_admin($current_user)) {
                $res = $db->query($sql);
                while ($row = $db->fetchByAssoc($res)) {
                    $ds_pt_arr = calculateRevenueOfDate($row['from_date'], $row['to_date']);
                    // if ($row['from_date'] == '2026-01-08') {
                    //     pr($ds_pt_arr);
                    // }

                    $from_date_row = 'Từ ngày <span class="form-label fw-semibold text-danger">' . date('d-m-Y', strtotime($row['from_date'])) . '</span> Đến ngày <span class="form-label fw-semibold text-danger">' . date('d-m-Y', strtotime($row['to_date'])) . '</span>';
                    if (strtotime($row['from_date']) === strtotime($row['to_date'])) {
                        $from_date_row = '<span class="form-label fw-semibold text-danger">' . date('d-m-Y', strtotime($row['from_date'])) . '</span>';
                    }

                    if (strpos($row['period_group'], 'current') !== false && !$mark_current) {
                        $mark_current = true;

                        $html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold">' . $title_current . '</span> 
								</td>
							</tr>';
                    } else if (strpos($row['period_group'], 'prev1') !== false && !$mark_previous1) {
                        $mark_previous1 = true;

                        $html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold">' . $title_prev1 . '</span> 
								</td>
							</tr>';
                    } else if (strpos($row['period_group'], 'prev2') !== false && !$mark_previous2) {
                        $mark_previous2 = true;

                        $html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold">' . $title_prev2 . '</span> 
								</td>
							</tr>';
                    }

                    $html .= '<tr data-period="' . $row['period'] . '">
                                <td align="center" data-label="Ngày" class="text-nowrap">
                                    ' . $from_date_row . '
                                </td>
                                <td align="center" data-label="Tổng số vé">
                                    <span class="detail_domestic" data-from-date="' . $row['from_date'] . '" data-to-date="' . $row['to_date'] . '">' . format_number($row['total_ticket_qty']) . '</span>
                                </td>
                                <td align="right" data-label="Nội địa">' . format_number($row['total_profit_domestic']) . '</td>
                                <td align="right" data-label="Quốc tế">
                                    ' . format_number($row['total_profit_inter']) . '
                                </td>
                                <td align="right" data-label="Doanh số tổng">' . format_number($row['total_profit']) . '</td>
                                <td align="right" data-label="Doanh số PThu">' . format_number($ds_pt_arr['total_revenue']) . '</td>
                                <td align="center" data-label="Booking">' . format_number($row['total_qty']) . '</td>
                                <td align="center" data-label="BK 2-3 vé">' . format_number($row['total_bk_2_3']) . '</td>
                                <td align="center" data-label="BK 4-6 vé">' . format_number($row['total_bk_4_6']) . '</td>
                                <td align="center" data-label="BK tham khảo">' . format_number($row['tham_khao_bk']) . '</td>
                                <td align="center" data-label="Cuộc gọi đến">
                                    <span class="show_detail_call text-decoration-underline cursor-pointer text-primary fw-bold" data-from-date="' . $row['from_date'] . '" data-to-date="' . $row['to_date'] . '" data-direction="inbound" data-is-booking="0">' . format_number($row['inbound']) . '</span>
                                </td>
                                <td align="center" data-label="Gọi đến tạo BK">
                                    <span class="show_detail_call text-decoration-underline cursor-pointer text-primary fw-bold" data-from-date="' . $row['from_date'] . '" data-to-date="' . $row['to_date'] . '" data-direction="inbound" data-is-booking="1">' . format_number($row['inbound_bk']) . '</span>
                                </td>
                                <td align="center" data-label="Gọi nhỡ">
                                    <span class="show_detail_call text-decoration-underline cursor-pointer text-primary fw-bold" data-from-date="' . $row['from_date'] . '" data-to-date="' . $row['to_date'] . '" data-direction="missed">' . format_number($row['missed']) . '</span>
                                </td>
                            </tr>';
                }
            }

            $smartyobj->assign('DATA', $html);
        } catch (Exception $e) {
            error_log("Exception in: " . $e->getMessage());
            return '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }

        // Danh sách booking chưa xuất vé
        $paid_booking = $this->getPaidBooking();
        $smartyobj->assign('DATA2', $paid_booking['html']);
        $smartyobj->assign('SODONG', format_number($paid_booking['total_row']));
        $smartyobj->assign('TONGTIENDOANHSO', format_number($paid_booking['total_ds']));
        $smartyobj->assign('TONGTIENCHUAXUAT', format_number($paid_booking['total_amt']));
        $smartyobj->assign('TONGSOVECHUAXUAT', format_number($paid_booking['total_tkt']));
    }

    /**
     * Get paid booking list but ticket not issued
     * @return array
     */
    function getPaidBooking()
    {
        global $db, $app_list_strings, $timedate;
        $date_format = $timedate->get_date_time_format();

        $total_row  = 0;
        $total_amt  = 0;
        $total_ds   = 0;
        $total_tkt  = 0;
        $arr        = array();
        $html       = '';

        // Booking ở tình trạng xác nhận -> hiện hết lên báo cáo
        // Booking ở tình trạng xuất vé -> chỉ lấy booking có những vé chưa xuất
        $sql = "SELECT 
                    (SELECT u.user_name FROM users u WHERE u.id=b.assigned_user_id AND u.deleted=0 LIMIT 1) AS user_name,
                    (SELECT u.id FROM users u WHERE u.id=b.assigned_user_id AND u.deleted=0 LIMIT 1) AS user_id,
					b.name AS booking,
					b.id AS booking_id,
					(SELECT i.departure FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS dep_code,
					(SELECT i.arrival FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS arv_code,
					(SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS dep_airline,
					(SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='1' AND i.deleted=0 LIMIT 1) AS ret_airline,
					(SELECT i.departure_date FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS dep_date,
					(SELECT i.departure_date FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='1' AND i.deleted=0 LIMIT 1) AS ret_date,
					b.booking_status,
					b.total_amount,
					b.date_entered,
					(SELECT SUM(d.quantity) FROM ec_booking_details d WHERE d.booking_id=b.id AND d.deleted=0 LIMIT 1) AS total_tkt,
                    (SELECT SUM(d.quantity) FROM ec_booking_details d WHERE d.booking_id=b.id AND d.supplier_id IS NULL AND d.deleted=0 LIMIT 1) AS total_not_issued,
                    (SELECT SUM(d.total_bought_price) FROM ec_booking_details d WHERE d.booking_id=b.id AND d.deleted=0 LIMIT 1) AS total_bought_price,
					b.flight_type
				FROM ec_flight_bookings b
				WHERE b.booking_status IN ('3', '7') AND b.deleted = 0
				ORDER BY b.date_entered ";

        // if($current_user->user_name == 'hungnh'){
        //     pr($sql);
        // }

        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            if ($row['booking_status'] == '3' || !empty($row['total_not_issued']) && $row['total_not_issued'] < $row['total_tkt']) {
                $html .= '<tr>
    				<td align="center"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '" target="_blank">' . $row['booking'] . '</a></td>
    				<td align="center">' . $row['dep_code'] . ' - ' . $row['arv_code'] . ($row['flight_type'] == '0' ? ' - ' . $row['dep_code'] : '') . '</td>
    				<td align="center" class="hide-mobile">' . $row['dep_airline'] . ($row['flight_type'] == '0' ? '-' . $row['ret_airline'] : '') . '</td>
    				<td align="center" class="hide-mobile">' . date($date_format, strtotime($row['dep_date'])) . ($row['flight_type'] == '0' ? '<br />' . date($date_format, strtotime($row['ret_date'])) : '') . '</td>
    				<td align="center" class="fw-bold hide-mobile" style="color:' . $app_list_strings['booking_status_color_list'][$row['booking_status']] . '">' . $app_list_strings['booking_status_list'][$row['booking_status']] . '</td>
    				<td align="center">' . format_number($row['total_tkt']) . '</td>
    				<td align="right"  class="fw-bold">' . format_number($row['total_amount'] - $row['total_bought_price']) . '</td>
    				<td align="right"  class="fw-bold hide-mobile">' . format_number($row['total_amount']) . '</td>
    				<td align="center" class="hide-mobile"><a href="index.php?module=Employees&return_module=Employees&action=DetailView&record=' . $row['user_id'] . '" target="_blank">' . $row['user_name'] . '</a></td>
    				<td align="center" class="hide-mobile">' . date('d-m-Y H:i', strtotime($row['date_entered']) + 7 * 3600) . '</td>
    			</tr>';

                $total_row++;
                $total_tkt += $row['total_tkt'];
                $total_amt += $row['total_amount'];
                $total_ds += ($row['total_amount'] - $row['total_bought_price']);
            }
        }
        $arr['total_row']   = $total_row;
        $arr['total_amt']   = $total_amt;
        $arr['total_ds']    = $total_ds;
        $arr['total_tkt']   = $total_tkt;
        $arr['html'] = $html;
        return $arr;
    }
}
