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
                    t.total_qty,
                    t.total_ticket_qty,
                    t.total_bk_2_3,
                    t.total_bk_4_6,
                    t.total_profit,
                    t.total_profit_domestic,
                    t.total_profit_inter
                FROM (
                    SELECT
                        CASE
                            WHEN date_ticket_issue BETWEEN '{$ranges['today_current']['from']}' AND '{$ranges['today_current']['to']}' THEN 'today_current'
                            WHEN date_ticket_issue BETWEEN '{$ranges['today_prev1']['from']}'   AND '{$ranges['today_prev1']['to']}'   THEN 'today_prev1'
                            WHEN date_ticket_issue BETWEEN '{$ranges['today_prev2']['from']}'   AND '{$ranges['today_prev2']['to']}'   THEN 'today_prev2'
                            WHEN date_ticket_issue BETWEEN '{$ranges['yesterday_current']['from']}' AND '{$ranges['yesterday_current']['to']}' THEN 'yesterday_current'
                            WHEN date_ticket_issue BETWEEN '{$ranges['yesterday_prev1']['from']}'   AND '{$ranges['yesterday_prev1']['to']}'   THEN 'yesterday_prev1'
                            WHEN date_ticket_issue BETWEEN '{$ranges['yesterday_prev2']['from']}'   AND '{$ranges['yesterday_prev2']['to']}'   THEN 'yesterday_prev2'
                            WHEN date_ticket_issue BETWEEN '{$ranges['daybefore_current']['from']}' AND '{$ranges['daybefore_current']['to']}' THEN 'daybefore_current'
                            WHEN date_ticket_issue BETWEEN '{$ranges['daybefore_prev1']['from']}'   AND '{$ranges['daybefore_prev1']['to']}'   THEN 'daybefore_prev1'
                            WHEN date_ticket_issue BETWEEN '{$ranges['daybefore_prev2']['from']}'   AND '{$ranges['daybefore_prev2']['to']}'   THEN 'daybefore_prev2'
                        END AS period,
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
                        SUM(CASE WHEN ticket_qty BETWEEN 4 AND 6 THEN 1 ELSE 0 END) AS total_bk_4_6
                    FROM ec_revenue
                    WHERE deleted = 0
                    AND (
                        date_ticket_issue BETWEEN '{$ranges['today_current']['from']}' AND '{$ranges['today_current']['to']}'
                        OR date_ticket_issue BETWEEN '{$ranges['today_prev1']['from']}'   AND '{$ranges['today_prev1']['to']}'
                        OR date_ticket_issue BETWEEN '{$ranges['today_prev2']['from']}'   AND '{$ranges['today_prev2']['to']}'

                        OR date_ticket_issue BETWEEN '{$ranges['yesterday_current']['from']}' AND '{$ranges['yesterday_current']['to']}'
                        OR date_ticket_issue BETWEEN '{$ranges['yesterday_prev1']['from']}'   AND '{$ranges['yesterday_prev1']['to']}'
                        OR date_ticket_issue BETWEEN '{$ranges['yesterday_prev2']['from']}'   AND '{$ranges['yesterday_prev2']['to']}'

                        OR date_ticket_issue BETWEEN '{$ranges['daybefore_current']['from']}' AND '{$ranges['daybefore_current']['to']}'
                        OR date_ticket_issue BETWEEN '{$ranges['daybefore_prev1']['from']}'   AND '{$ranges['daybefore_prev1']['to']}'
                        OR date_ticket_issue BETWEEN '{$ranges['daybefore_prev2']['from']}'   AND '{$ranges['daybefore_prev2']['to']}'
                    )
                    GROUP BY period
                ) t
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
									<span class="form-label text-dark fw-semibold">Hiện tại</span> 
								</td>
							</tr>';
                    } else if (strpos($row['period_group'], 'prev1') !== false && !$mark_previous1) {
                        $mark_previous1 = true;

                        $html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold">Cùng kỳ</span> 
								</td>
							</tr>';
                    } else if (strpos($row['period_group'], 'prev2') !== false && !$mark_previous2) {
                        $mark_previous2 = true;

                        $html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold">Cùng kỳ kế tiếp</span> 
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
                                <td align="right" data-label="Doanh số PT">' . format_number($ds_pt_arr['total_revenue']) . '</td>
                                <td align="center" data-label="Booking">' . format_number($row['total_qty']) . '</td>
                                <td align="center" data-label="Booking 2-3 vé">' . format_number($row['total_bk_2_3']) . '</td>
                                <td align="center" data-label="Booking 4-6 vé">' . format_number($row['total_bk_4_6']) . '</td>
                            </tr>';
                }
            }

            $smartyobj->assign('DATA', $html);
        } catch (Exception $e) {
            error_log("Exception in: " . $e->getMessage());
            return '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
