<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
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
        $js = '';
        $js .= '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>';
        $js .= '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>';
        echo $js;
    }

    function populateContent($smartyobj)
    {
        global $app_list_strings;

        $from_date = isset($_REQUEST['from_date']) ? preg_replace('/[^0-9\-]/', '', $_REQUEST['from_date']) : date('d-m-Y');
        $to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9\-]/', '', $_REQUEST['to_date']) : date('d-m-Y');

        $smartyobj->assign('FROM_DATE', $from_date);
        $smartyobj->assign('TO_DATE', $to_date);

        // Tạo DATE_OPTION cho select dropdown
        $quater_fromdate = '';
        $quater_todate = '';
        switch (ceil(date('n') / 3)) {
            case 1: $quater_fromdate = '01-01-' . date('Y'); $quater_todate = '31-03-' . date('Y'); break;
            case 2: $quater_fromdate = '01-04-' . date('Y'); $quater_todate = '30-06-' . date('Y'); break;
            case 3: $quater_fromdate = '01-07-' . date('Y'); $quater_todate = '30-09-' . date('Y'); break;
            case 4: $quater_fromdate = '01-10-' . date('Y'); $quater_todate = '31-12-' . date('Y'); break;
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

        // Tính ngày cho labels và UI (Dựa trên khoảng tgian đã chọn)
        $p0_from = $from_date;
        $p0_to   = $to_date;

        $p1_from = date('d-m-Y', strtotime($from_date . " -1 day"));
        $p1_to   = date('d-m-Y', strtotime($to_date . " -1 day"));

        $p2_from = date('d-m-Y', strtotime($from_date . " -2 days"));
        $p2_to   = date('d-m-Y', strtotime($to_date . " -2 days"));

        $p3_from = date('d-m-Y', strtotime($from_date . " -7 days"));
        $p3_to   = date('d-m-Y', strtotime($to_date . " -7 days"));

        $p4_from = date('d-m-Y', strtotime($from_date . " -8 days"));
        $p4_to   = date('d-m-Y', strtotime($to_date . " -8 days"));

        $p5_from = date('d-m-Y', strtotime($from_date . " -9 days"));
        $p5_to   = date('d-m-Y', strtotime($to_date . " -9 days"));

        $smartyobj->assign('P0_LABEL', 'Kỳ Chọn');
        $smartyobj->assign('P1_LABEL', 'Kỳ Chọn -1 ngày');
        $smartyobj->assign('P2_LABEL', 'Kỳ Chọn -2 ngày');
        $smartyobj->assign('P3_LABEL', 'Tuần Trước');
        $smartyobj->assign('P4_LABEL', 'Tuần Trước -1');
        $smartyobj->assign('P5_LABEL', 'Tuần Trước -2');

        $format_range = function($f, $t) {
            return ($f === $t) ? "($f)" : "($f - $t)";
        };

        $smartyobj->assign('P0_RANGE', $format_range($p0_from, $p0_to));
        $smartyobj->assign('P1_RANGE', $format_range($p1_from, $p1_to));
        $smartyobj->assign('P2_RANGE', $format_range($p2_from, $p2_to));
        $smartyobj->assign('P3_RANGE', $format_range($p3_from, $p3_to));
        $smartyobj->assign('P4_RANGE', $format_range($p4_from, $p4_to));
        $smartyobj->assign('P5_RANGE', $format_range($p5_from, $p5_to));

        $to_sql = function($d_m_y) {
            return date('Y-m-d', strtotime($d_m_y));
        };

        $p0_f = $to_sql($p0_from); $p0_t = $to_sql($p0_to);
        $p1_f = $to_sql($p1_from); $p1_t = $to_sql($p1_to);
        $p2_f = $to_sql($p2_from); $p2_t = $to_sql($p2_to);
        $p3_f = $to_sql($p3_from); $p3_t = $to_sql($p3_to);
        $p4_f = $to_sql($p4_from); $p4_t = $to_sql($p4_to);
        $p5_f = $to_sql($p5_from); $p5_t = $to_sql($p5_to);

        $smartyobj->assign('P0_F', $p0_f); $smartyobj->assign('P0_T', $p0_t);
        $smartyobj->assign('P1_F', $p1_f); $smartyobj->assign('P1_T', $p1_t);
        $smartyobj->assign('P2_F', $p2_f); $smartyobj->assign('P2_T', $p2_t);
        $smartyobj->assign('P3_F', $p3_f); $smartyobj->assign('P3_T', $p3_t);
        $smartyobj->assign('P4_F', $p4_f); $smartyobj->assign('P4_T', $p4_t);
        $smartyobj->assign('P5_F', $p5_f); $smartyobj->assign('P5_T', $p5_t);

        $sql = "
            SELECT
                ap_dest.country AS dest_country,
                routes.departure,
                routes.arrival,
                ap_dest.city_name AS dest_city,
                ap_dep.city_name AS dep_city,
            
                COUNT(CASE WHEN routes.period = 0 THEN 1 END) AS bk_p0,
                SUM(CASE WHEN routes.period = 0 AND routes.booking_status IN ('8','7','3') THEN 1 ELSE 0 END) AS bk_p0_ok,
                SUM(CASE WHEN routes.period = 0 THEN routes.ticket_qty ELSE 0 END) AS ticket_p0,
            
                COUNT(CASE WHEN routes.period = 1 THEN 1 END) AS bk_p1,
                SUM(CASE WHEN routes.period = 1 AND routes.booking_status IN ('8','7','3') THEN 1 ELSE 0 END) AS bk_p1_ok,
                SUM(CASE WHEN routes.period = 1 THEN routes.ticket_qty ELSE 0 END) AS ticket_p1,
            
                COUNT(CASE WHEN routes.period = 2 THEN 1 END) AS bk_p2,
                SUM(CASE WHEN routes.period = 2 AND routes.booking_status IN ('8','7','3') THEN 1 ELSE 0 END) AS bk_p2_ok,
                SUM(CASE WHEN routes.period = 2 THEN routes.ticket_qty ELSE 0 END) AS ticket_p2,
            
                COUNT(CASE WHEN routes.period = 3 THEN 1 END) AS bk_p3,
                SUM(CASE WHEN routes.period = 3 AND routes.booking_status IN ('8','7','3') THEN 1 ELSE 0 END) AS bk_p3_ok,
                SUM(CASE WHEN routes.period = 3 THEN routes.ticket_qty ELSE 0 END) AS ticket_p3,

                COUNT(CASE WHEN routes.period = 4 THEN 1 END) AS bk_p4,
                SUM(CASE WHEN routes.period = 4 AND routes.booking_status IN ('8','7','3') THEN 1 ELSE 0 END) AS bk_p4_ok,
                SUM(CASE WHEN routes.period = 4 THEN routes.ticket_qty ELSE 0 END) AS ticket_p4,

                COUNT(CASE WHEN routes.period = 5 THEN 1 END) AS bk_p5,
                SUM(CASE WHEN routes.period = 5 AND routes.booking_status IN ('8','7','3') THEN 1 ELSE 0 END) AS bk_p5_ok,
                SUM(CASE WHEN routes.period = 5 THEN routes.ticket_qty ELSE 0 END) AS ticket_p5
            
            FROM (
                SELECT
                    sub.booking_id,
                    sub.departure,
                    sub.arrival,
                    sub.booking_status,
                    sub.period,
                    IFNULL(bkd.qty, 0) AS ticket_qty
                FROM (
                    SELECT
                        b.id AS booking_id,
                        b.booking_status,
                        MIN(i.departure) AS departure,
                        SUBSTRING_INDEX(
                            GROUP_CONCAT(i.arrival ORDER BY i.departure_date DESC), ',', 1
                        ) AS arrival,
                        CASE
                            WHEN DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p0_f}' AND '{$p0_t}' THEN 0
                            WHEN DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p1_f}' AND '{$p1_t}' THEN 1
                            WHEN DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p2_f}' AND '{$p2_t}' THEN 2
                            WHEN DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p3_f}' AND '{$p3_t}' THEN 3
                            WHEN DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p4_f}' AND '{$p4_t}' THEN 4
                            WHEN DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p5_f}' AND '{$p5_t}' THEN 5
                        END AS period
                    FROM ec_flight_bookings b
                    INNER JOIN ec_booking_itineraries i
                        ON i.booking_id = b.id
                        AND i.deleted = 0
                        AND i.direction = 0
                        AND i.add_type = 0
                    WHERE b.deleted = 0
                      AND (
                          DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p0_f}' AND '{$p0_t}' OR
                          DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p1_f}' AND '{$p1_t}' OR
                          DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p2_f}' AND '{$p2_t}' OR
                          DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p3_f}' AND '{$p3_t}' OR
                          DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p4_f}' AND '{$p4_t}' OR
                          DATE(CONVERT_TZ(b.date_entered, '+00:00', '+07:00')) BETWEEN '{$p5_f}' AND '{$p5_t}'
                      )
                    GROUP BY b.id
                ) sub
                LEFT JOIN (
                    SELECT booking_id, SUM(quantity) AS qty
                    FROM ec_booking_details
                    WHERE deleted = 0
                    GROUP BY booking_id
                ) bkd ON bkd.booking_id = sub.booking_id
            ) routes
            INNER JOIN ec_airports ap_dest
                ON ap_dest.iata_code = routes.arrival
                AND ap_dest.deleted = 0
            LEFT JOIN ec_airports ap_dep
                ON ap_dep.iata_code = routes.departure
                AND ap_dep.deleted = 0
            GROUP BY ap_dest.country, routes.departure, routes.arrival
            ORDER BY bk_p0 DESC, ap_dest.country
        ";

        $res = $this->bean->db->query($sql);
        $real_data = [];
        global $app_list_strings;
        $region_dom = $app_list_strings['region_dom'] ?? [];

        while ($row = $this->bean->db->fetchByAssoc($res)) {
            $countryCode = $row['dest_country'];
            if (!$countryCode) continue;

            if (!isset($real_data[$countryCode])) {
                $real_data[$countryCode] = [
                    'name' => $region_dom[$countryCode] ?? $countryCode,
                    'p0' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
                    'p1' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
                    'p2' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
                    'p3' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
                    'p4' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
                    'p5' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
                    'routes' => []
                ];
            }

            // Cộng dồn metrics cho quốc gia
            $real_data[$countryCode]['p0']['bk'] += (int)$row['bk_p0'];
            $real_data[$countryCode]['p0']['bk_ok'] += (int)$row['bk_p0_ok'];
            $real_data[$countryCode]['p0']['ticket'] += (int)$row['ticket_p0'];

            $real_data[$countryCode]['p1']['bk'] += (int)$row['bk_p1'];
            $real_data[$countryCode]['p1']['bk_ok'] += (int)$row['bk_p1_ok'];
            $real_data[$countryCode]['p1']['ticket'] += (int)$row['ticket_p1'];

            $real_data[$countryCode]['p2']['bk'] += (int)$row['bk_p2'];
            $real_data[$countryCode]['p2']['bk_ok'] += (int)$row['bk_p2_ok'];
            $real_data[$countryCode]['p2']['ticket'] += (int)$row['ticket_p2'];

            $real_data[$countryCode]['p3']['bk'] += (int)$row['bk_p3'];
            $real_data[$countryCode]['p3']['bk_ok'] += (int)$row['bk_p3_ok'];
            $real_data[$countryCode]['p3']['ticket'] += (int)$row['ticket_p3'];

            $real_data[$countryCode]['p4']['bk'] += (int)$row['bk_p4'];
            $real_data[$countryCode]['p4']['bk_ok'] += (int)$row['bk_p4_ok'];
            $real_data[$countryCode]['p4']['ticket'] += (int)$row['ticket_p4'];

            $real_data[$countryCode]['p5']['bk'] += (int)$row['bk_p5'];
            $real_data[$countryCode]['p5']['bk_ok'] += (int)$row['bk_p5_ok'];
            $real_data[$countryCode]['p5']['ticket'] += (int)$row['ticket_p5'];

            // Thêm chi tiết route
            $depStr = $row['departure'] . ($row['dep_city'] ? ' (' . $row['dep_city'] . ')' : '');
            $arrStr = $row['arrival'] . ($row['dest_city'] ? ' (' . $row['dest_city'] . ')' : '');

            $real_data[$countryCode]['routes'][] = [
                'dep_code' => $row['departure'],
                'arr_code' => $row['arrival'],
                'dep' => $depStr,
                'arr' => $arrStr,
                'bk' => (int)$row['bk_p0'],
                'bk_ok' => (int)$row['bk_p0_ok'],
                'ticket' => (int)$row['ticket_p0'],
                
                'p1_bk' => (int)$row['bk_p1'], 'p1_bk_ok' => (int)$row['bk_p1_ok'], 'p1_ticket' => (int)$row['ticket_p1'],
                'p2_bk' => (int)$row['bk_p2'], 'p2_bk_ok' => (int)$row['bk_p2_ok'], 'p2_ticket' => (int)$row['ticket_p2'],
                'p3_bk' => (int)$row['bk_p3'], 'p3_bk_ok' => (int)$row['bk_p3_ok'], 'p3_ticket' => (int)$row['ticket_p3'],
                'p4_bk' => (int)$row['bk_p4'], 'p4_bk_ok' => (int)$row['bk_p4_ok'], 'p4_ticket' => (int)$row['ticket_p4'],
                'p5_bk' => (int)$row['bk_p5'], 'p5_bk_ok' => (int)$row['bk_p5_ok'], 'p5_ticket' => (int)$row['ticket_p5']
            ];
        }
        
        // Sort lại các route theo bk_p0 giảm dần bên trong mỗi quốc gia
        foreach ($real_data as &$cData) {
            usort($cData['routes'], function($a, $b) {
                return $b['bk'] <=> $a['bk'];
            });
        }
        unset($cData); // Hủy reference

        // Sort các quốc gia theo tổng bk_p0 giảm dần
        uasort($real_data, function($a, $b) {
            return $b['p0']['bk'] <=> $a['p0']['bk'];
        });

        // Tính tổng cộng cho các kỳ
        $total_data = [
            'p0' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
            'p1' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
            'p2' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
            'p3' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
            'p4' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0],
            'p5' => ['bk' => 0, 'bk_ok' => 0, 'ticket' => 0]
        ];

        // Cộng dồn tổng
        foreach ($real_data as $key => $data) {
            $total_data['p0']['bk'] += $data['p0']['bk'];
            $total_data['p0']['bk_ok'] += $data['p0']['bk_ok'];
            $total_data['p0']['ticket'] += $data['p0']['ticket'];
            
            $total_data['p1']['bk'] += $data['p1']['bk'];
            $total_data['p1']['bk_ok'] += $data['p1']['bk_ok'];
            $total_data['p1']['ticket'] += $data['p1']['ticket'];
            
            $total_data['p2']['bk'] += $data['p2']['bk'];
            $total_data['p2']['bk_ok'] += $data['p2']['bk_ok'];
            $total_data['p2']['ticket'] += $data['p2']['ticket'];
            
            $total_data['p3']['bk'] += $data['p3']['bk'];
            $total_data['p3']['bk_ok'] += $data['p3']['bk_ok'];
            $total_data['p3']['ticket'] += $data['p3']['ticket'];

            $total_data['p4']['bk'] += $data['p4']['bk'];
            $total_data['p4']['bk_ok'] += $data['p4']['bk_ok'];
            $total_data['p4']['ticket'] += $data['p4']['ticket'];

            $total_data['p5']['bk'] += $data['p5']['bk'];
            $total_data['p5']['bk_ok'] += $data['p5']['bk_ok'];
            $total_data['p5']['ticket'] += $data['p5']['ticket'];
        }
        $calc_change = function($curr, $prev) {
            if ($prev == 0) {
                if ($curr == 0) return ['type' => 'none', 'val' => '0%'];
                return ['type' => 'up', 'val' => '100%'];
            }
            $diff = $curr - $prev;
            $pct = round(abs($diff) / $prev * 100, 1) . '%';
            if ($diff > 0) return ['type' => 'up', 'val' => $pct];
            if ($diff < 0) return ['type' => 'down', 'val' => $pct];
            return ['type' => 'none', 'val' => '0%'];
        };

        foreach ($real_data as &$data) {
            $data['p0_change'] = $calc_change($data['p0']['bk_ok'], $data['p1']['bk_ok']);
            $data['p1_change'] = $calc_change($data['p1']['bk_ok'], $data['p2']['bk_ok']);
            $data['p3_change'] = $calc_change($data['p3']['bk_ok'], $data['p4']['bk_ok']);
            $data['p4_change'] = $calc_change($data['p4']['bk_ok'], $data['p5']['bk_ok']);
        }
        unset($data);

        $total_data['p0_change'] = $calc_change($total_data['p0']['bk_ok'], $total_data['p1']['bk_ok']);
        $total_data['p1_change'] = $calc_change($total_data['p1']['bk_ok'], $total_data['p2']['bk_ok']);
        $total_data['p3_change'] = $calc_change($total_data['p3']['bk_ok'], $total_data['p4']['bk_ok']);
        $total_data['p4_change'] = $calc_change($total_data['p4']['bk_ok'], $total_data['p5']['bk_ok']);

        $smartyobj->assign('MOCK_DATA', $real_data); // Giữ nguyên tên biến Smarty để khỏi sửa tpl nhiều
        $smartyobj->assign('TOTAL_DATA', $total_data);

        // Dữ liệu cho biểu đồ Chart.js (lấy top 5 quốc gia)
        $chart_labels = [];
        $chart_data_p0 = [];
        $chart_data_p1 = [];
        $chart_data_p2 = [];
        $chart_data_p3 = [];
        $chart_data_p4 = [];
        $chart_data_p5 = [];

        $count = 0;
        foreach ($real_data as $key => $data) {
            if ($count >= 5) break; // Chỉ show tối đa 5 nước trên chart
            $chart_labels[] = $data['name'];
            $chart_data_p0[] = $data['p0']['bk'];
            $chart_data_p1[] = $data['p1']['bk'];
            $chart_data_p2[] = $data['p2']['bk'];
            $chart_data_p3[] = $data['p3']['bk'];
            $chart_data_p4[] = $data['p4']['bk'];
            $chart_data_p5[] = $data['p5']['bk'];
            $count++;
        }

        $smartyobj->assign('CHART_LABELS', json_encode($chart_labels));
        $smartyobj->assign('CHART_DATA_P0', json_encode($chart_data_p0));
        $smartyobj->assign('CHART_DATA_P1', json_encode($chart_data_p1));
        $smartyobj->assign('CHART_DATA_P2', json_encode($chart_data_p2));
        $smartyobj->assign('CHART_DATA_P3', json_encode($chart_data_p3));
        $smartyobj->assign('CHART_DATA_P4', json_encode($chart_data_p4));
        $smartyobj->assign('CHART_DATA_P5', json_encode($chart_data_p5));
    }
}

