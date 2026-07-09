<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewreport_route_analysis extends SugarView
{
    function display()
    {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true) && isManagerUser($GLOBALS['current_user']->id)) {
            $smartyCont = new Sugar_Smarty();
            $this->displayJS();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_report_route_analysis.tpl');
        } else {
            header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này!"));
            exit();
        }
    }

    function displayJS()
    {
        $cssPath = 'modules/EC_Flight_Bookings/css/report_route_analysis.css';
        $jsPath  = 'modules/EC_Flight_Bookings/js/report_route_analysis.js';
        $cssVer  = file_exists($cssPath) ? filemtime($cssPath) : time();
        $jsVer   = file_exists($jsPath) ? filemtime($jsPath) : time();

        $out  = '';
        $out .= '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>';
        $out .= '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>';
        $out .= '<link rel="stylesheet" href="' . $cssPath . '?v=' . $cssVer . '">';
        $out .= '<script src="' . $jsPath . '?v=' . $jsVer . '"></script>';
        echo $out;
    }

    function populateContent($smartyobj)
    {
        global $app_list_strings;

        $from_date = isset($_REQUEST['from_date']) ? preg_replace('/[^0-9\-]/', '', $_REQUEST['from_date']) : date('d-m-Y');
        $to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9\-]/', '', $_REQUEST['to_date']) : date('d-m-Y');

        // Chuẩn hoá: đảm bảo from <= to
        if (strtotime($from_date) > strtotime($to_date)) {
            $tmp = $from_date;
            $from_date = $to_date;
            $to_date = $tmp;
        }

        $smartyobj->assign('FROM_DATE', $from_date);
        $smartyobj->assign('TO_DATE', $to_date);

        // Tạo DATE_OPTION cho select dropdown
        $quater_fromdate = '';
        $quater_todate = '';
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
        }

        $arr_date = array(
            '<option value="" fromdate="" todate="">---Trống---</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'today' ? 'selected' : (!isset($_POST['date_select']) ? 'selected' : '')) . ' value="today" fromdate="' . date('d-m-Y') . '" todate="' . date('d-m-Y') . '">Hôm nay</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'yesterday' ? 'selected' : '') . ' value="yesterday" fromdate="' . date('d-m-Y', strtotime('-1 day')) . '" todate="' . date('d-m-Y', strtotime('-1 day')) . '">Hôm qua</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'daybefore' ? 'selected' : '') . ' value="daybefore" fromdate="' . date('d-m-Y', strtotime('-2 day')) . '" todate="' . date('d-m-Y', strtotime('-2 day')) . '">Hôm trước</option>',
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

        /**
         * ========= XỬ LÝ KỲ SO SÁNH =========
         * Dịch kỳ theo ĐƠN VỊ của lựa chọn để tránh chồng lấn:
         *  - Ngày (today/yesterday/daybefore hoặc khoảng 1 ngày): offset 0,1,2,7,8,9 ngày
         *    (3 kỳ gần + 3 kỳ cùng thứ tuần trước).
         *  - Tuần / Tháng / Quý / Năm: 6 kỳ LIÊN TIẾP cùng đơn vị (không chồng lấn).
         *  - Khoảng tự chọn khác: 6 kỳ liên tiếp theo độ dài khoảng chọn (L ngày).
         */
        $sel = isset($_POST['date_select']) ? (string)$_POST['date_select'] : '';
        $L = (int) round((strtotime($to_date) - strtotime($from_date)) / 86400) + 1;
        if ($L < 1) $L = 1;

        $fsql = date('Y-m-d', strtotime($from_date));
        $tsql = date('Y-m-d', strtotime($to_date));

        $mkMonth = function ($base, $k) {
            return [date('Y-m-01', strtotime("$base $k month")), date('Y-m-t', strtotime("$base $k month"))];
        };
        $mkQuarter = function ($base, $k) {
            $ts = strtotime("$base " . ($k * 3) . " months");
            $q  = (int)ceil((int)date('n', $ts) / 3);
            $sm = ($q - 1) * 3 + 1;
            $f  = date('Y-m-d', strtotime(date('Y', $ts) . "-{$sm}-01"));
            return [$f, date('Y-m-t', strtotime("$f +2 months"))];
        };
        $mkYear = function ($base, $k) {
            $y = (int)date('Y', strtotime("$base $k year"));
            return ["{$y}-01-01", "{$y}-12-31"];
        };

        $rf = $rt = [];   // biên từng kỳ (Y-m-d)
        if (in_array($sel, ['this_week', 'previous_week'], true)) {
            for ($p = 0; $p <= 5; $p++) {
                $rf[$p] = date('Y-m-d', strtotime("$fsql -" . (7 * $p) . " days"));
                $rt[$p] = date('Y-m-d', strtotime("$tsql -" . (7 * $p) . " days"));
            }
            $labels = [];
            for ($p = 0; $p <= 5; $p++) $labels[$p] = 'Tuần ' . date('d/m', strtotime($rf[$p]));
        } elseif (in_array($sel, ['this_month', 'previous_month'], true)) {
            for ($p = 0; $p <= 5; $p++) {
                list($rf[$p], $rt[$p]) = $mkMonth($fsql, -$p);
            }
            $labels = [];
            for ($p = 0; $p <= 5; $p++) $labels[$p] = 'Tháng ' . date('n/Y', strtotime($rf[$p]));
        } elseif (in_array($sel, ['quarter_this', 'quarter_previous'], true)) {
            for ($p = 0; $p <= 5; $p++) {
                list($rf[$p], $rt[$p]) = $mkQuarter($fsql, -$p);
            }
            $labels = [];
            for ($p = 0; $p <= 5; $p++) {
                $qts = strtotime($rf[$p]);
                $labels[$p] = 'Quý ' . (int)ceil((int)date('n', $qts) / 3) . '/' . date('Y', $qts);
            }
        } elseif (in_array($sel, ['this_year', 'previous_year'], true)) {
            for ($p = 0; $p <= 5; $p++) {
                list($rf[$p], $rt[$p]) = $mkYear($fsql, -$p);
            }
            $labels = [];
            for ($p = 0; $p <= 5; $p++) $labels[$p] = 'Năm ' . date('Y', strtotime($rf[$p]));
        } elseif ($L == 1 || in_array($sel, ['today', 'yesterday', 'daybefore'], true)) {
            $offs = [0, 1, 2, 7, 8, 9];
            $dow  = ['CN', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
            $labels = [];
            for ($p = 0; $p <= 5; $p++) {
                $rf[$p] = date('Y-m-d', strtotime("$fsql -{$offs[$p]} days"));
                $rt[$p] = date('Y-m-d', strtotime("$tsql -{$offs[$p]} days"));
                $ts = strtotime($rf[$p]);
                $labels[$p] = $dow[(int)date('w', $ts)] . ' ' . date('d/m', $ts);
            }
        } else {
            for ($p = 0; $p <= 5; $p++) {
                $off = $L * $p;
                $rf[$p] = date('Y-m-d', strtotime("$fsql -{$off} days"));
                $rt[$p] = date('Y-m-d', strtotime("$tsql -{$off} days"));
            }
            $labels = ['Kỳ Chọn', 'Kỳ Trước', 'Kỳ Trước 2', 'Kỳ Trước 3', 'Kỳ Trước 4', 'Kỳ Trước 5'];
        }

        // Xác định chế độ "ngày" (2 cụm) để chọn cặp so sánh %
        $isDayMode = ($L == 1 || in_array($sel, ['today', 'yesterday', 'daybefore'], true))
            && !in_array($sel, ['this_week', 'previous_week', 'this_month', 'previous_month', 'quarter_this', 'quarter_previous', 'this_year', 'previous_year'], true);

        $pf = $pt = $putc_f = $putc_t = [];
        for ($p = 0; $p <= 5; $p++) {
            $pf[$p] = date('d/m/Y', strtotime($rf[$p]));
            $pt[$p] = date('d/m/Y', strtotime($rt[$p]));
            $putc_f[$p] = gmdate('Y-m-d H:i:s', strtotime($rf[$p] . ' 00:00:00'));
            $putc_t[$p] = gmdate('Y-m-d H:i:s', strtotime($rt[$p] . ' 23:59:59'));
        }

        // Cặp so sánh % (so với kỳ liền cũ hơn). Chế độ ngày: 2 cụm rời (p2, p5 không so).
        if ($isDayMode) {
            $changePairs = [0 => 1, 1 => 2, 2 => null, 3 => 4, 4 => 5, 5 => null];
        } else {
            $changePairs = [0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => null];
        }

        // 6 màu contrast cao (định nghĩa trong report_route_analysis.css: .text-pc0..pc5)
        $colColors = [0 => 'pc0', 1 => 'pc1', 2 => 'pc2', 3 => 'pc3', 4 => 'pc4', 5 => 'pc5'];

        $format_range = function ($f, $t) {
            return ($f === $t) ? "($f)" : "($f đến $t)";
        };

        // Meta từng cột kỳ (dùng chung) — key phẳng cho Smarty 2
        $colMeta = [];
        for ($p = 0; $p <= 5; $p++) {
            $colMeta[$p] = [
                'pid'   => 'p' . $p,
                'label' => $labels[$p],
                'range' => $format_range($pf[$p], $pt[$p]),
                'f'     => $rf[$p],
                't'     => $rt[$p],
                'color' => $colColors[$p],
            ];
            $smartyobj->assign("P{$p}_LABEL", $labels[$p]);
            $smartyobj->assign("P{$p}_F", $colMeta[$p]['f']);
            $smartyobj->assign("P{$p}_T", $colMeta[$p]['t']);
        }

        // ========= Build SQL (mỗi booking 1 dòng; doanh số tính real-time) =========
        $caseWhen = [];
        $whereOr  = [];
        for ($p = 0; $p <= 5; $p++) {
            $caseWhen[] = "WHEN b.date_entered BETWEEN '{$putc_f[$p]}' AND '{$putc_t[$p]}' THEN {$p}";
            $whereOr[]  = "b.date_entered BETWEEN '{$putc_f[$p]}' AND '{$putc_t[$p]}'";
        }
        $caseWhenStr = implode("\n", $caseWhen);
        $whereOrStr  = implode(" OR\n", $whereOr);

        $sql = "
            SELECT
                sub.booking_id,
                sub.booking_status,
                sub.is_reference,
                sub.contact_name,
                sub.period,
                sub.departure,
                sub.arrival,
                ap_dest.country   AS dest_country,
                ap_dest.city_name AS dest_city,
                ap_dep.city_name  AS dep_city,
                IFNULL(bkd.qty, 0) AS ticket_qty
            FROM (
                SELECT
                    b.id AS booking_id,
                    b.booking_status,
                    b.is_reference,
                    b.contact_name,
                    SUBSTRING_INDEX(GROUP_CONCAT(i.departure ORDER BY i.departure_date ASC), ',', 1) AS departure,
                    SUBSTRING_INDEX(GROUP_CONCAT(i.arrival ORDER BY i.departure_date DESC), ',', 1) AS arrival,
                    CASE
                        {$caseWhenStr}
                    END AS period
                FROM ec_flight_bookings b
                INNER JOIN ec_booking_itineraries i
                    ON i.booking_id = b.id AND i.deleted = 0 AND i.direction = 0 AND i.add_type = 0
                WHERE b.deleted = 0
                  AND (
                      {$whereOrStr}
                  )
                GROUP BY b.id
            ) sub
            LEFT JOIN (
                SELECT booking_id, SUM(quantity) AS qty
                FROM ec_booking_details WHERE deleted = 0 GROUP BY booking_id
            ) bkd ON bkd.booking_id = sub.booking_id
            INNER JOIN ec_airports ap_dest ON ap_dest.iata_code = sub.arrival   AND ap_dest.deleted = 0
            LEFT JOIN  ec_airports ap_dep  ON ap_dep.iata_code  = sub.departure AND ap_dep.deleted = 0
        ";

        $res = $this->bean->db->query($sql);

        $region_dom = $app_list_strings['region_dom'] ?? [];
        $dom_keys   = array_keys(EC_Airports::getAirportList(EC_Airports::AIRPORT_SCOPE_DOMESTIC));

        // Thu thập dòng + id BK hoàn tất, rồi tính doanh số real-time 1 lần (batch)
        $rows          = [];
        $ok_ids        = [];
        $all_ids       = [];
        $contact_names = [];
        while ($row = $this->bean->db->fetchByAssoc($res)) {
            if (!$row['dest_country']) continue;
            $rows[]     = $row;
            $all_ids[]  = $row['booking_id'];
            $contact_names[$row['booking_id']] = $row['contact_name'];
            if (in_array($row['booking_status'], ['8', '7', '3'], true)) {
                $ok_ids[] = $row['booking_id'];
            }
        }
        // [booking_id => ['revenue' => doanh thu, 'profit' => doanh số]] — cùng công thức modal
        $amt_map = calculateBKAmtBatch($ok_ids);
        // [booking_id => ['is_booker' => bool, 'is_customer' => bool]] — 1 query phẳng, không EXISTS tương quan
        $source_map = ec_classify_booking_source($all_ids, $contact_names);

        // ========= Helpers =========
        $money = function ($v) {
            return number_format((float)$v, 0, ',', '.');
        };

        // metrics: bk, bk_ok, ticket, rev (doanh thu gross), profit (doanh số)
        $mkCell = function ($m) use ($money) {
            $bk    = (int)$m['bk'];
            $bk_ok = (int)$m['bk_ok'];
            $tk    = (int)$m['ticket'];
            $tkAll = (int)$m['ticket_all'];
            $rev   = (float)$m['rev'];
            $prof  = (float)$m['profit'];
            $conv  = $bk > 0 ? round($bk_ok / $bk * 100) : 0;
            $avg   = $tk > 0 ? $prof / $tk : 0;  // TB/vé = doanh số / số vé
            return [
                'bk'         => $bk,
                'bk_ok'      => $bk_ok,
                'bk_str'     => $bk_ok . '&nbsp;/&nbsp;' . $bk,
                'conv'       => $conv . '%',
                'ticket'     => $tk,
                'ticket_all' => $tkAll,
                'ticket_str' => $tk . '&nbsp;/&nbsp;' . $tkAll,
                'ref'        => (int)$m['ref'],
                'ref_ok'     => (int)$m['ref_ok'],
                'ref_str'    => (int)$m['ref_ok'] . '&nbsp;/&nbsp;' . (int)$m['ref'],
                'booker'     => (int)$m['booker'],
                'booker_ok'  => (int)$m['booker_ok'],
                'booker_str' => (int)$m['booker_ok'] . '&nbsp;/&nbsp;' . (int)$m['booker'],
                'customer'     => (int)$m['customer'],
                'customer_ok'  => (int)$m['customer_ok'],
                'customer_str' => (int)$m['customer_ok'] . '&nbsp;/&nbsp;' . (int)$m['customer'],
                'rev'        => $rev,
                'profit'     => $prof,
                'profit_str' => $money($prof),
                'avg_str'    => $money($avg),
            ];
        };

        $calc_change = function ($curr, $prev) {
            if ($prev == 0) {
                if ($curr == 0) return ['type' => 'none', 'val' => '0%', 'pct' => 0.0];
                return ['type' => 'up', 'val' => '100%', 'pct' => 100.0];
            }
            $diff = $curr - $prev;
            $pctNum = round(abs($diff) / $prev * 100, 1);
            $pct = $pctNum . '%';
            if ($diff > 0) return ['type' => 'up', 'val' => $pct, 'pct' => $pctNum];
            if ($diff < 0) return ['type' => 'down', 'val' => $pct, 'pct' => $pctNum];
            return ['type' => 'none', 'val' => '0%', 'pct' => 0.0];
        };

        $buildCols = function ($cells) use ($colMeta, $calc_change, $changePairs) {
            $chg = [];
            foreach ($changePairs as $p => $prev) {
                $chg[$p] = ($prev === null) ? null : $calc_change($cells[$p]['bk_ok'], $cells[$prev]['bk_ok']);
            }
            $out = [];
            for ($p = 0; $p <= 5; $p++) {
                $c = $cells[$p];
                $out[$p] = array_merge($colMeta[$p], [
                    'bk_ok'       => $c['bk_ok'],
                    'bk'          => $c['bk'],
                    'bk_str'      => $c['bk_str'],
                    'conv'        => $c['conv'],
                    'ticket'      => $c['ticket'],
                    'ticket_str'  => $c['ticket_str'],
                    'ref'         => $c['ref'],
                    'ref_str'     => $c['ref_str'],
                    'booker_str'    => $c['booker_str'],
                    'customer_str'  => $c['customer_str'],
                    'profit_str'  => $c['profit_str'],
                    'avg_str'     => $c['avg_str'],
                    'change_type' => $chg[$p] ? $chg[$p]['type'] : '',
                    'change_val'  => $chg[$p] ? $chg[$p]['val'] : '',
                ]);
            }
            return $out;
        };

        $emptyMetrics = [
            'bk' => 0,
            'bk_ok' => 0,
            'ticket' => 0,
            'ticket_all' => 0,
            'ref' => 0,
            'ref_ok' => 0,
            'booker' => 0,
            'booker_ok' => 0,
            'customer' => 0,
            'customer_ok' => 0,
            'rev' => 0.0,
            'profit' => 0.0,
        ];

        // ========= Gom dữ liệu, tách Quốc tế / Nội địa =========
        $groups = ['intl' => [], 'dom' => []];
        $all_routes = [];

        foreach ($rows as $row) {
            $cc  = $row['dest_country'];
            $dep = $row['departure'];
            $arr = $row['arrival'];
            $p   = $row['period'];
            if ($p === null || $p === '') continue;
            $p = (int)$p;

            $isDom = in_array($dep, $dom_keys, true) && in_array($arr, $dom_keys, true);
            $g  = $isDom ? 'dom' : 'intl';
            $ok = in_array($row['booking_status'], ['8', '7', '3'], true);

            $isRef      = ((int)$row['is_reference'] === 1);
            $src        = $source_map[$row['booking_id']] ?? ['is_booker' => false, 'is_customer' => false];
            $isBooker   = $src['is_booker'];
            $isCustomer = $src['is_customer'];
            $tk_all = (int)$row['ticket_qty'];
            $tk     = $ok ? $tk_all : 0;
            $amt  = ($ok && isset($amt_map[$row['booking_id']])) ? $amt_map[$row['booking_id']] : ['revenue' => 0, 'profit' => 0];
            $rev  = $ok ? (float)$amt['revenue'] : 0.0;
            $prof = $ok ? (float)$amt['profit'] : 0.0;

            if (!isset($groups[$g][$cc])) {
                $groups[$g][$cc] = [
                    'name'   => $region_dom[$cc] ?? $cc,
                    'raw'    => array_fill(0, 6, $emptyMetrics),
                    'routes' => [],
                ];
            }
            $rk = $dep . '|' . $arr;
            if (!isset($groups[$g][$cc]['routes'][$rk])) {
                $groups[$g][$cc]['routes'][$rk] = [
                    'dep_code' => $dep,
                    'arr_code' => $arr,
                    'dep'      => $dep . ($row['dep_city'] ? ' (' . $row['dep_city'] . ')' : ''),
                    'arr'      => $arr . ($row['dest_city'] ? ' (' . $row['dest_city'] . ')' : ''),
                    'raw'      => array_fill(0, 6, $emptyMetrics),
                ];
            }

            $cRaw = &$groups[$g][$cc]['raw'][$p];
            $rRaw = &$groups[$g][$cc]['routes'][$rk]['raw'][$p];
            // bk = tổng tất cả BK của hành trình; ref (tham khảo) là tập con của bk
            $cRaw['bk']++;
            $rRaw['bk']++;
            $cRaw['ticket_all'] += $tk_all;
            $rRaw['ticket_all'] += $tk_all;
            if ($ok) {
                $cRaw['bk_ok']++;
                $rRaw['bk_ok']++;
                $cRaw['ticket'] += $tk;
                $rRaw['ticket'] += $tk;
                $cRaw['rev']    += $rev;
                $rRaw['rev']    += $rev;
                $cRaw['profit'] += $prof;
                $rRaw['profit'] += $prof;
            }
            if ($isRef) {
                $cRaw['ref']++;
                $rRaw['ref']++;
                if ($ok) {
                    $cRaw['ref_ok']++;
                    $rRaw['ref_ok']++;
                }
            }
            if ($isBooker) {
                $cRaw['booker']++;
                $rRaw['booker']++;
                if ($ok) {
                    $cRaw['booker_ok']++;
                    $rRaw['booker_ok']++;
                }
            } elseif ($isCustomer) {
                $cRaw['customer']++;
                $rRaw['customer']++;
                if ($ok) {
                    $cRaw['customer_ok']++;
                    $rRaw['customer_ok']++;
                }
            }
            unset($cRaw, $rRaw);
        }

        // ========= Hoàn thiện từng group =========
        $GROUPS = [];
        $meta = [
            'intl' => ['title' => 'Quốc Tế', 'scope' => 'international'],
            'dom'  => ['title' => 'Nội Địa', 'scope' => 'domestic'],
        ];
        $chartsJson = [];

        foreach ($groups as $g => $countries) {
            $list = [];
            foreach ($countries as $cc => $cData) {
                $cells = [];
                for ($p = 0; $p <= 5; $p++) $cells[$p] = $mkCell($cData['raw'][$p]);
                $cols = $buildCols($cells);

                $routeList = [];
                foreach ($cData['routes'] as $rt) {
                    $rcells = [];
                    for ($p = 0; $p <= 5; $p++) $rcells[$p] = $mkCell($rt['raw'][$p]);
                    $rcols = $buildCols($rcells);
                    $routeList[] = [
                        'dep_code'   => $rt['dep_code'],
                        'arr_code'   => $rt['arr_code'],
                        'dep'        => $rt['dep'],
                        'arr'        => $rt['arr'],
                        'token'      => $g . '-' . $cc,
                        'p0_ok'      => $rcells[0]['bk_ok'],
                        'bk_ok'      => $rcells[0]['bk_ok'],
                        'bk'         => $rcells[0]['bk'],
                        'bk_str'     => $rcells[0]['bk_str'],
                        'conv'       => $rcells[0]['conv'],
                        'ticket'     => $rcells[0]['ticket'],
                        'ticket_str' => $rcells[0]['ticket_str'],
                        'ref'          => $rcells[0]['ref'],
                        'ref_str'      => $rcells[0]['ref_str'],
                        'booker_str'   => $rcells[0]['booker_str'],
                        'customer_str' => $rcells[0]['customer_str'],
                        'profit_str' => $rcells[0]['profit_str'],
                        'avg_str'    => $rcells[0]['avg_str'],
                        'columns'    => $rcols,
                    ];
                    $all_routes[] = [
                        'cc'       => $cc,
                        'name'     => $cData['name'],
                        'dep_code' => $rt['dep_code'],
                        'arr_code' => $rt['arr_code'],
                        'dep'      => $rt['dep'],
                        'arr'      => $rt['arr'],
                        'p0_ok'    => $rcells[0]['bk_ok'],
                        'p1_ok'    => $rcells[1]['bk_ok'],
                    ];
                }
                usort($routeList, function ($a, $b) {
                    return $b['p0_ok'] <=> $a['p0_ok'];
                });

                $list[] = [
                    'cc'          => $cc,
                    'name'        => $cData['name'],
                    'p0_ok'       => $cells[0]['bk_ok'],
                    'columns'     => $cols,
                    'routes'      => $routeList,
                    'route_count' => count($routeList),
                    'cells'       => $cells,
                ];
            }

            // sort quốc gia theo bk_ok kỳ chọn giảm dần
            usort($list, function ($a, $b) {
                return $b['p0_ok'] <=> $a['p0_ok'];
            });

            // tổng cộng
            $totalRaw = array_fill(0, 6, $emptyMetrics);
            foreach ($list as $c) {
                for ($p = 0; $p <= 5; $p++) {
                    $totalRaw[$p]['bk']         += $c['cells'][$p]['bk'];
                    $totalRaw[$p]['bk_ok']      += $c['cells'][$p]['bk_ok'];
                    $totalRaw[$p]['ticket']     += $c['cells'][$p]['ticket'];
                    $totalRaw[$p]['ticket_all'] += $c['cells'][$p]['ticket_all'];
                    $totalRaw[$p]['ref']        += $c['cells'][$p]['ref'];
                    $totalRaw[$p]['ref_ok']     += $c['cells'][$p]['ref_ok'];
                    $totalRaw[$p]['booker']     += $c['cells'][$p]['booker'];
                    $totalRaw[$p]['booker_ok']  += $c['cells'][$p]['booker_ok'];
                    $totalRaw[$p]['customer']    += $c['cells'][$p]['customer'];
                    $totalRaw[$p]['customer_ok'] += $c['cells'][$p]['customer_ok'];
                    $totalRaw[$p]['rev']        += $c['cells'][$p]['rev'];
                    $totalRaw[$p]['profit']     += $c['cells'][$p]['profit'];
                }
            }
            $totalCells = [];
            for ($p = 0; $p <= 5; $p++) $totalCells[$p] = $mkCell($totalRaw[$p]);
            $totalCols = $buildCols($totalCells);

            // chart top 5 quốc gia theo bk_ok từng kỳ
            $chart_labels = [];
            $chart = ['p0' => [], 'p1' => [], 'p2' => [], 'p3' => [], 'p4' => [], 'p5' => []];
            $cnt = 0;
            foreach ($list as $c) {
                if ($cnt >= 5) break;
                $chart_labels[] = $c['name'];
                for ($p = 0; $p <= 5; $p++) $chart["p{$p}"][] = $c['cells'][$p]['bk_ok'];
                $cnt++;
            }
            $chart['labels'] = $chart_labels;
            $chartsJson[$g] = $chart;

            // bỏ 'cells' nội bộ trước khi đẩy ra template
            foreach ($list as &$c) unset($c['cells']);
            unset($c);

            $GROUPS[$g] = [
                'key'         => $g,
                'title'       => $meta[$g]['title'],
                'scope'       => $meta[$g]['scope'],
                'total_cols'  => $totalCols,
                'data'        => $list,
                'count'       => count($list),
            ];
        }

        // header dùng chung (label/range/color 6 cột)
        $smartyobj->assign('HEAD_COLS', $colMeta);
        $smartyobj->assign('GROUPS', $GROUPS);

        // ========= Top route giảm mạnh nhất =========
        $declines = [];
        foreach ($all_routes as $r) {
            if ($r['p1_ok'] < 3) continue;
            if ($r['p0_ok'] >= $r['p1_ok']) continue;
            $pct = round(($r['p1_ok'] - $r['p0_ok']) / $r['p1_ok'] * 100, 1);
            $r['drop']    = $r['p1_ok'] - $r['p0_ok'];
            $r['pct']     = $pct;
            $r['pct_str'] = $pct . '%';
            $declines[] = $r;
        }
        usort($declines, function ($a, $b) {
            if ($b['pct'] === $a['pct']) return $b['drop'] <=> $a['drop'];
            return $b['pct'] <=> $a['pct'];
        });
        $smartyobj->assign('DECLINE_ROUTES', array_slice($declines, 0, 9));

        // ========= Top route tăng mạnh nhất =========
        $growths = [];
        foreach ($all_routes as $r) {
            if ($r['p0_ok'] <= $r['p1_ok']) continue;
            // % tăng: nếu kỳ trước = 0 thì coi là tăng mới (đặt mốc lớn để xếp trên)
            if ($r['p1_ok'] == 0) {
                $pct = 999999;
                $r['pct_str'] = 'Mới';
            } else {
                $pct = round(($r['p0_ok'] - $r['p1_ok']) / $r['p1_ok'] * 100, 1);
                $r['pct_str'] = $pct . '%';
            }
            $r['gain'] = $r['p0_ok'] - $r['p1_ok'];
            $r['pct']  = $pct;
            $growths[] = $r;
        }
        usort($growths, function ($a, $b) {
            if ($b['pct'] === $a['pct']) return $b['gain'] <=> $a['gain'];
            return $b['pct'] <=> $a['pct'];
        });
        $smartyobj->assign('GROWTH_ROUTES', array_slice($growths, 0, 9));

        // ========= Top 9 hành trình có BK hoàn tất nhiều nhất (kỳ chọn) =========
        usort($all_routes, function ($a, $b) {
            return $b['p0_ok'] <=> $a['p0_ok'];
        });
        $topRoutes = [];
        foreach ($all_routes as $r) {
            if ($r['p0_ok'] < 1) continue;
            $topRoutes[] = $r;
            if (count($topRoutes) >= 9) break;
        }
        $smartyobj->assign('TOP_ROUTES', $topRoutes);

        // ========= Dữ liệu chart (JSON đóng gói cho JS ngoài) =========
        $reportJson = [
            'charts' => $chartsJson,
            'labels' => [
                'p0' => $labels[0],
                'p1' => $labels[1],
                'p2' => $labels[2],
                'p3' => $labels[3],
                'p4' => $labels[4],
                'p5' => $labels[5],
            ],
        ];
        $smartyobj->assign('REPORT_JSON', json_encode($reportJson, JSON_UNESCAPED_UNICODE));
    }
}
