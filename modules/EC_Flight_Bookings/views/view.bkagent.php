<?php
require_once("include/Sugar_Smarty.php");
class Viewbkagent extends SugarView
{

    function display()
    {
        global $current_user;
        // if ($current_user->user_name != 'hungnh') {
        //      echo '<p class="alert alert-danger">Hệ thống đang bảo trì. Vui lòng quay lại sau.</p>';
        //      exit;
        // }

        if (is_admin($current_user)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_bkagent.tpl');
        } else {
            header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smarty)
    {
        // đến ngày
        if (empty($_REQUEST['to_date'])) {
            $to_date = date('Y-m-d');
        } else $to_date = $_REQUEST['to_date'];

        // từ ngày
        if (empty($_REQUEST['from_date'])) {
            $from_date = date('Y-m-d', strtotime($to_date));
        } else $from_date = $_REQUEST['from_date'];

        // OPTION DATE
        // check quarter 
        switch (ceil(date('n') / 3)) {
            case 1:
                $cq_from_date = '01-01-' . date('Y');
                $cq_to_date = '31-03-' . date('Y');
                $lq_from_date = '01-01-' . date('Y', strtotime('- 1 year'));
                $lq_to_date = '31-03-' . date('Y', strtotime('- 1 year'));
                break;
            case 2:
                $cq_from_date = '01-04-' . date('Y');
                $cq_to_date = '30-06-' . date('Y');
                $lq_from_date = '01-01-' . date('Y');
                $lq_to_date = '31-03-' . date('Y');
                break;
            case 3:
                $cq_from_date = '01-07-' . date('Y');
                $cq_to_date = '30-09-' . date('Y');
                $lq_from_date = '01-04-' . date('Y');
                $lq_to_date = '30-06-' . date('Y');
                break;
            case 4:
                $cq_from_date = '01-10-' . date('Y');
                $cq_to_date = '31-12-' . date('Y');
                $lq_from_date = '01-07-' . date('Y');
                $lq_to_date = '30-09-' . date('Y');
                break;
            default:
                $cq_from_date = '';
                $cq_to_date = '';
                $lq_from_date = '';
                $lq_to_date = '';
                break;
        }
        $report_term_list = '<option from_date="' . date('d-m-Y') . '" to_date="' . date('d-m-Y') . '">Hôm nay</option>';
        $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime("-1 day")) . '" to_date="' . date('d-m-Y', strtotime("-1 day")) . '">Hôm qua</option>';
        $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime("first day of this month")) . '" to_date="' . date('d-m-Y', strtotime("last day of this month")) . '">Tháng này</option>';
        $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime("first day of previous month")) . '" to_date="' . date('d-m-Y', strtotime("last day of previous month")) . '">Tháng trước</option>';
        $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime($cq_from_date)) . '" to_date="' . date('d-m-Y', strtotime($cq_to_date)) . '">Quý này</option>';
        $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime($lq_from_date)) . '" to_date="' . date('d-m-Y', strtotime($lq_to_date)) . '">Quý trước</option>';
        $smarty->assign('REPORT_TERM_LIST', $report_term_list);

        $smarty->assign('FROM_DATE_VALUE', date('d-m-Y', strtotime($from_date)));
        $smarty->assign('TO_DATE_VALUE', date('d-m-Y', strtotime($to_date)));
        // Phần phát sinh theo ngày chứng từ: phiếu thu (loại 4/5) và hoàn vé, gán theo hãng.
        // BK tính theo rule report_sales_revenue (chỉ total_amount, không gồm PT/HV) nên không trùng.
        $ptc = $this->getPTContrib($from_date, $to_date);
        $hvc = $this->getHVContrib($from_date, $to_date);

        $smarty->assign('AGENT_LIST_TBL', $this->populateBKAgentReport($from_date, $to_date, $ptc, $hvc));
        $smarty->assign('BOOKING_LIST_TBL', $this->populateBKReport($from_date, $to_date, $ptc, $hvc));
    }

    /**
     * Tên hiển thị của hãng theo mã (đồng bộ cách xử lý trong báo cáo).
     */
    private function airlineDisplayName($code)
    {
        if ($code === 'N/A' || $code === '') return 'Khác';
        $airline = myGetAirlineInfo2($code, 'CODE');
        $name = isset($airline['data'][0]['name']) ? $airline['data'][0]['name'] : '';
        if (!empty($name)) return $name;
        if ($code === '0V') return 'VASCO';
        return 'Hãng khác';
    }

    /**
     * Doanh số của 1 booking theo ĐÚNG rule report_sales_revenue (KHÔNG dùng calculateBKAmt).
     *   - Giá bán  = bk.total_amount
     *   - Giá mua  = SUM(ec_booking_details.total_bought_price) + phí mua hành lý (ec_booking_passengers)
     *   - Doanh số = Giá bán - Giá mua
     * KHÔNG cộng phiếu thu (PT) / hoàn vé (HV) / điểm vào đây — vì calculateBKAmt gộp cả PT/HV
     * bất kể ngày chứng từ (kể cả ngoài kỳ đang xem) gây sai lệch. PT/HV được tính riêng theo
     * ngày chứng từ ở getPTContrib()/getHVContrib().
     */
    private function calcBookingRaw($bk_id)
    {
        global $db;
        static $cache = array();
        if (isset($cache[$bk_id])) return $cache[$bk_id];

        $sql = "SELECT
                    IFNULL(bk.total_amount, 0) AS total_amount,
                    (
                        IFNULL((
                            SELECT SUM(IFNULL(d.total_bought_price, 0))
                            FROM ec_booking_details d
                            WHERE d.booking_id = bk.id AND d.deleted = 0
                        ), 0)
                        +
                        IFNULL((
                            SELECT IF(
                                bk.flight_type = '0',
                                SUM(IFNULL(px.luggage_purchase, 0)) + SUM(IFNULL(px.luggage_purchase_inbound, 0)),
                                SUM(IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0))
                            )
                            FROM ec_booking_passengers px
                            WHERE px.booking_id = bk.id AND px.deleted = 0 AND (px.add_type IS NULL OR px.add_type = '')
                        ), 0)
                    ) AS total_purchase
                FROM ec_flight_bookings bk
                WHERE bk.id = '" . $db->quote($bk_id) . "' AND bk.deleted = 0";

        $res = $db->query($sql);
        $row = $db->fetchByAssoc($res);
        $amount   = (float) (isset($row['total_amount']) ? $row['total_amount'] : 0);
        $purchase = (float) (isset($row['total_purchase']) ? $row['total_purchase'] : 0);

        $cache[$bk_id] = array(
            'total_amount'   => $amount,
            'total_purchase' => $purchase,
            'total_profit'   => $amount - $purchase,
        );
        return $cache[$bk_id];
    }

    /**
     * Phần phát sinh từ PHIẾU THU loại 4/5 (đổi giờ/hành trình, mua hành lý/ghế),
     * ghi nhận theo NGÀY CHỨNG TỪ (ngayhachtoan), gán hãng theo sup_direction.
     *
     * Lấy TẤT CẢ phiếu thu 4/5 có ngày chứng từ trong kỳ (không loại theo booking) — vì BK
     * đã tính theo rule report_sales_revenue (chỉ total_amount, KHÔNG gồm PT/HV) nên không trùng.
     *
     * @return array ['by_airline' => [normCode => ['amount','purchase','profit']], 'rows' => [...]]
     */
    function getPTContrib($from_date, $to_date)
    {
        global $db;
        $fromD = date('Y-m-d', strtotime($from_date));
        $toD   = date('Y-m-d', strtotime($to_date));
        // ngayhachtoan lưu theo UTC -> dịch mốc ngày local sang UTC (giống report_sales_revenue)
        $from_utc = date('Y-m-d H:i:s', strtotime($fromD . ' 00:00:00') - 7 * 3600);
        $to_utc   = date('Y-m-d H:i:s', strtotime($toD . ' 23:59:59') - 7 * 3600);

        $sql = "SELECT rv.id, rv.name, rv.booking_id, rv.amount, rv.ngayhachtoan,
                    rv.supplier_id, rv.supplier2_id, rv.supplier3_id,
                    rv.sell_amount, rv.sell_amount2, rv.sell_amount3,
                    rv.bought_amount, rv.bought_amount2, rv.bought_amount3,
                    rv.sup_direction, rv.sup_direction2, rv.sup_direction3
                FROM ec_receipt_voucher rv
                WHERE rv.deleted = 0
                    AND rv.loai_thu IN ('4','5')
                    AND rv.rv_status IN (1,2)
                    AND rv.ngayhachtoan >= '" . $from_utc . "' AND rv.ngayhachtoan <= '" . $to_utc . "'";

        $res = $db->query($sql);
        $by = array();
        $rows = array();
        $cache = array();

        while ($row = $db->fetchByAssoc($res)) {
            $date_disp = !empty($row['ngayhachtoan']) ? date('d-m-Y', strtotime($row['ngayhachtoan']) + 7 * 3600) : '';

            $lines = array(
                array($row['supplier_id'],  $row['sell_amount'],  $row['bought_amount'],  $row['sup_direction']),
                array($row['supplier2_id'], $row['sell_amount2'], $row['bought_amount2'], $row['sup_direction2']),
                array($row['supplier3_id'], $row['sell_amount3'], $row['bought_amount3'], $row['sup_direction3']),
            );
            // nếu cả 3 dòng đều không có giá bán mà phiếu có amount -> gán amount cho dòng NCC đầu tiên
            $sum_sell = (float)$row['sell_amount'] + (float)$row['sell_amount2'] + (float)$row['sell_amount3'];

            foreach ($lines as $ln) {
                list($sup_id, $sell, $bought, $dir) = $ln;
                $sell = (float) $sell;
                $bought = (float) $bought;
                if (empty($sup_id) && $sell == 0 && $bought == 0) continue;

                if ($sum_sell == 0 && (float)$row['amount'] > 0 && $sell == 0) {
                    $sell = (float) $row['amount'];
                    $sum_sell = -1; // chỉ gán 1 lần
                }

                $ck = $row['booking_id'] . '|' . $sup_id . '|' . $dir;
                if (!isset($cache[$ck])) {
                    $cache[$ck] = resolveRVSupplierAirline($row['booking_id'], $sup_id, $dir);
                }
                $resolved = $cache[$ck];
                $code = (!$resolved['ambiguous'] && !empty($resolved['airline_code'])) ? $resolved['airline_code'] : 'N/A';
                $profit = $sell - $bought;

                if (!isset($by[$code])) $by[$code] = array('amount' => 0, 'purchase' => 0, 'profit' => 0);
                $by[$code]['amount']   += $sell;
                $by[$code]['purchase'] += $bought;
                $by[$code]['profit']   += $profit;

                $dirLabel = ($resolved['direction'] === '0') ? 'Lượt đi' : (($resolved['direction'] === '1') ? 'Lượt về' : '-');
                $rows[] = array(
                    'airline' => $code,
                    'type' => 'PT',
                    'name' => $row['name'],
                    'link_module' => 'EC_Receipt_Voucher',
                    'link_id' => $row['id'],
                    'amount' => $sell,
                    'purchase' => $bought,
                    'profit' => $profit,
                    've' => 0,
                    'date' => $date_disp,
                    'direction' => $dirLabel,
                );
            }
        }

        return array('by_airline' => $by, 'rows' => $rows);
    }

    /**
     * Phần phát sinh từ HOÀN VÉ, ghi nhận theo NGÀY CHỨNG TỪ (ngayhachtoan),
     * gán hãng theo từng dòng ec_chitiethoanve.airline_code. Giá trị âm (giảm doanh thu).
     *
     * Lấy TẤT CẢ hoàn vé có ngày chứng từ trong kỳ (BK dùng rule report_sales_revenue nên không trùng).
     *
     * @return array ['by_airline' => [...], 'rows' => [...]]
     */
    function getHVContrib($from_date, $to_date)
    {
        global $db;
        $fromD = date('Y-m-d', strtotime($from_date));
        $toD   = date('Y-m-d', strtotime($to_date));

        $sql = "SELECT hv.id, hv.name, hv.booking_id, hv.ngayhachtoan,
                    ct.airline_code, ct.chieubay,
                    SUM(IFNULL(ct.sotienkhach,0)) AS sotienkhach,
                    SUM(IFNULL(ct.sotienhang,0))  AS sotienhang,
                    COUNT(ct.id) AS ve_count
                FROM ec_hoanve hv
                INNER JOIN ec_chitiethoanve ct ON ct.hoanve_id = hv.id AND ct.deleted = 0
                WHERE hv.deleted = 0 AND hv.tinhtrang = '1'
                    AND hv.ngayhachtoan >= '" . $fromD . " 00:00:00' AND hv.ngayhachtoan <= '" . $toD . " 23:59:59'
                GROUP BY hv.id, ct.airline_code";

        $res = $db->query($sql);
        $by = array();
        $rows = array();

        while ($row = $db->fetchByAssoc($res)) {
            $code = myNormalizeAirlineCode($row['airline_code']);
            if (empty($code)) $code = 'N/A';

            $amount   = -(float) $row['sotienkhach']; // giá bán (doanh thu) giảm
            $purchase = -(float) $row['sotienhang'];  // giá mua giảm
            $profit   = $amount - $purchase;          // = sotienhang - sotienkhach
            $ve_neg   = -(int) $row['ve_count'];      // hoàn vé -> TRỪ số lượng vé

            if (!isset($by[$code])) $by[$code] = array('amount' => 0, 'purchase' => 0, 'profit' => 0, 've' => 0);
            $by[$code]['amount']   += $amount;
            $by[$code]['purchase'] += $purchase;
            $by[$code]['profit']   += $profit;
            $by[$code]['ve']       += $ve_neg;

            $date_disp = !empty($row['ngayhachtoan']) ? date('d-m-Y', strtotime($row['ngayhachtoan'])) : '';
            $dirLabel = ($row['chieubay'] === '0') ? 'Lượt đi' : (($row['chieubay'] === '1') ? 'Lượt về' : '-');
            $rows[] = array(
                'airline' => $code,
                'type' => 'HV',
                'name' => $row['name'],
                'link_module' => 'EC_HoanVe',
                'link_id' => $row['id'],
                'amount' => $amount,
                'purchase' => $purchase,
                'profit' => $profit,
                've' => $ve_neg,
                'date' => $date_disp,
                'direction' => $dirLabel,
            );
        }

        return array('by_airline' => $by, 'rows' => $rows);
    }

    function populateBKAgentReport($from_date, $to_date, $ptc = null, $hvc = null)
    {
        global $db;
        $from_date  = date('Y-m-d 00:00:00', strtotime($from_date));
        $to_date    = date('Y-m-d 23:59:59', strtotime($to_date));

        $html = '
            <tr class="airline-row cursor-pointer" data-airline="ALL" title="Bấm để xem tất cả">
                <td></td>
                <td class="text-center text-decoration-underline"><b>Tổng</b></td>
                <td class="text-center"><b>$TOTAL_BK_QTY</b></td>
                <td class="text-center"><b>$TOTAL_TICKET_QTY</b></td>
                <td class="text-center"><b>$TOTAL_AMOUNT</b></td>
                <td class="text-center"><b>$TOTAL_PURCHASE</b></td>
                <td class="text-center"><b>$TOTAL_PROFIT</b></td>
            </tr>
        ';

        $sql = '
            SELECT 
                airline_code
                , SUM(IF(ticket_qty > 0, ticket_qty, 0)) AS ticket_qty
                -- , GROUP_CONCAT(IF(ticket_qty > 0, bk_name, NULL) SEPARATOR ", ") AS bk_name_err
                , GROUP_CONCAT(IF(ticket_qty > 0, booking_id, NULL) SEPARATOR ", ") AS bk_id_arr
            FROM ( 
                SELECT 
                    i.airline_code
                    , bk.name AS bk_name
                    , bk.id as booking_id
                    , IF(
                        bk.ticket_type = 2
                        , 1
                        , (
                            SELECT SUM(quantity)
                            FROM ec_booking_details
                            WHERE deleted = 0
                            AND booking_id = i.booking_id
                            AND direction = i.direction
                            GROUP BY direction
                        ) 
                    ) AS ticket_qty
                FROM ec_booking_itineraries i
                INNER JOIN ec_flight_bookings bk ON bk.id = i.booking_id AND bk.deleted = 0 AND bk.booking_status IN (3, 7, 8)
                AND bk.date_ticket_issue BETWEEN "' . $from_date . '" AND "' . $to_date . '"
                WHERE i.deleted = 0 
                GROUP BY i.airline_code, i.direction, bk.id
            ) AS t
            GROUP BY CASE 
                WHEN airline_code = "VJ" THEN "VJA"
                WHEN airline_code = "VN" THEN "VNA"
                ELSE airline_code END        
            ORDER BY ticket_qty DESC
        ';

        // if($GLOBALS['current_user']->user_name == 'hungnh') {
        //     pr($sql);
        // }

        $res = $db->query($sql);
        $total_bk_qty = 0;
        $total_ticket_qty = 0;
        $total_amout = 0;
        $total_purchase = 0;
        $total_profit = 0;
        $priceCache = [];
        $countedBkIds = [];
        $byAirline = []; // normCode => [name, bk_qty, ticket_qty, amount, purchase, profit]

        while ($row = $db->fetchByAssoc($res)) {
            $airline_amout = 0;
            $airline_purchase = 0;
            $airline_profit = 0;

            $bkIds = [];
            if (!empty($row['bk_id_arr'])) {
                $bkIds = array_values(array_unique(
                    array_filter(
                        array_map('trim', preg_split('/\s*,\s*/', $row['bk_id_arr'], -1, PREG_SPLIT_NO_EMPTY)),
                        'strlen'
                    )
                ));
            }

            $sl_bk = count($bkIds);

            if ($sl_bk > 0) {
                foreach ($bkIds as $bk_id) {
                    if (!isset($priceCache[$bk_id])) {
                        $priceCache[$bk_id] = $this->calcBookingRaw($bk_id);
                    }
                    $info_price = $priceCache[$bk_id];
                    $airline_amout += $info_price['total_amount'];
                    $airline_purchase += $info_price['total_purchase'];
                    $airline_profit += $info_price['total_profit'];

                    if (!isset($countedBkIds[$bk_id])) {
                        $countedBkIds[$bk_id] = true;
                        $total_amout += $info_price['total_amount'];
                        $total_purchase += $info_price['total_purchase'];
                        $total_profit += $info_price['total_profit'];
                    }
                }
            }

            // chuẩn hoá mã hãng để gộp chung với PT/HV
            $norm = myNormalizeAirlineCode($row['airline_code']);
            if (empty($norm)) $norm = 'N/A';

            if (!isset($byAirline[$norm])) {
                $byAirline[$norm] = array(
                    'name' => $this->airlineDisplayName($norm),
                    'bk_qty' => 0,
                    'ticket_qty' => 0,
                    'amount' => 0,
                    'purchase' => 0,
                    'profit' => 0,
                );
            }
            $byAirline[$norm]['bk_qty']     += $sl_bk;
            $byAirline[$norm]['ticket_qty'] += (int) $row['ticket_qty'];
            $byAirline[$norm]['amount']     += $airline_amout;
            $byAirline[$norm]['purchase']   += $airline_purchase;
            $byAirline[$norm]['profit']     += $airline_profit;

            $total_bk_qty += $sl_bk;
            $total_ticket_qty += $row['ticket_qty'];
        }

        // ===== Gộp thêm PT (phiếu thu 4/5) và HV (hoàn vé) theo hãng =====
        foreach (array($ptc, $hvc) as $contrib) {
            if (empty($contrib) || empty($contrib['by_airline'])) continue;
            foreach ($contrib['by_airline'] as $code => $c) {
                if (empty($code)) $code = 'N/A';
                if (!isset($byAirline[$code])) {
                    $byAirline[$code] = array(
                        'name' => $this->airlineDisplayName($code),
                        'bk_qty' => 0,
                        'ticket_qty' => 0,
                        'amount' => 0,
                        'purchase' => 0,
                        'profit' => 0,
                    );
                }
                $ve = isset($c['ve']) ? $c['ve'] : 0; // hoàn vé: âm
                $byAirline[$code]['ticket_qty'] += $ve;
                $byAirline[$code]['amount']   += $c['amount'];
                $byAirline[$code]['purchase'] += $c['purchase'];
                $byAirline[$code]['profit']   += $c['profit'];

                $total_ticket_qty += $ve;
                $total_amout    += $c['amount'];
                $total_purchase += $c['purchase'];
                $total_profit   += $c['profit'];
            }
        }

        // sắp xếp: SL vé giảm dần, rồi doanh thu giảm dần
        uasort($byAirline, function ($a, $b) {
            if ($b['ticket_qty'] !== $a['ticket_qty']) return $b['ticket_qty'] <=> $a['ticket_qty'];
            return $b['amount'] <=> $a['amount'];
        });

        $i = 0;
        foreach ($byAirline as $code => $a) {
            $html .= '
                         <tr class="airline-row cursor-pointer" data-airline="' . $code . '" title="Bấm để lọc vé của hãng này">
                              <td class="text-center">' . ($i + 1) . '</td>
                              <td class="text-center fw-semibold text-decoration-underline">' . $a['name'] . ' (' . $code . ')</td>
                              <td class="text-center">' . format_number($a['bk_qty']) . '</td>
                              <td class="text-center">' . format_number($a['ticket_qty']) . '</td>
                              <td class="text-center">' . format_number($a['amount']) . '</td>
                              <td class="text-center">' . format_number($a['purchase']) . '</td>
                              <td class="text-center">' . format_number($a['profit']) . '</td>
                         </tr>
                    ';
            $i++;
        }

        $html = str_replace(
            array(
                '$TOTAL_BK_QTY',
                '$TOTAL_TICKET_QTY',
                '$TOTAL_AMOUNT',
                '$TOTAL_PURCHASE',
                '$TOTAL_PROFIT',
            ),
            array(
                format_number($total_bk_qty),
                format_number($total_ticket_qty),
                format_number($total_amout),
                format_number($total_purchase),
                format_number($total_profit),
            ),
            $html
        );

        return $html;
    }

    function populateBKReport($from_date, $to_date, $ptc = null, $hvc = null)
    {
        global $db, $app_list_strings;
        $from_date = date('Y-m-d 00:00:00', strtotime($from_date));
        $to_date = date('Y-m-d 23:59:59', strtotime($to_date));
        $html = '
            <tr id="booking_list_total_row">
                <td></td>
                <td class="center"><b>Tổng</b></td>
                <td></td>
                <td></td>
                <td class="center"><b id="total_filtered_ticket_qty">$TOTAL_TICKET_QTY</b></td>
                <td></td>
                <td></td>
            </tr>
        ';
        //  <td class="center"><b id="total_filtered_amount">$TOTAL_AMOUNT</b></td>

        $sql = '
            SELECT 
                airline_code, bk_name, bk_id, GROUP_CONCAT(direction) AS direction    
                , SUM(IF(ticket_qty > 0, ticket_qty, 0)) AS ticket_qty
                , date_entered, date_ticket_issue
                -- , GROUP_CONCAT(IF(ticket_qty > 0, bk_name, NULL) SEPARATOR ", ") AS bk_name_err
            FROM ( 
                SELECT 
                    i.airline_code, i.direction
                    , bk.name AS bk_name, bk.id AS bk_id, bk.date_entered, bk.date_ticket_issue
                    , IF(
                        bk.ticket_type = 2
                        , 1
                        , (
                            SELECT SUM(quantity)
                            FROM ec_booking_details
                            WHERE deleted = 0
                            AND booking_id = i.booking_id
                            AND direction = i.direction
                            GROUP BY direction
                        ) 
                    ) AS ticket_qty
                FROM ec_booking_itineraries i
                INNER JOIN ec_flight_bookings bk ON bk.id = i.booking_id AND bk.deleted = 0 AND bk.booking_status IN (3, 7, 8)
                AND bk.date_ticket_issue >= "' . $from_date . '"
                AND bk.date_ticket_issue <= "' . $to_date . '"
                WHERE i.deleted = 0 
                GROUP BY i.airline_code, i.direction, bk.id
            ) AS t
            GROUP BY bk_id, airline_code
            ORDER BY ticket_qty DESC, date_entered
        ';

        $res = $db->query($sql);
        $i = $total_ticket_qty = $total_amount = 0;
        while ($row = $db->fetchByAssoc($res)) {
            // $airline = myGetAirlineInfo2($row['airline_code'], 'CODE');
            if ($row['direction'] == '0') {
                $direction = 'Lượt đi';
            } else if ($row['direction'] == '1') {
                $direction = 'Lượt về';
            } else $direction = 'Lượt đi & về';

            $info_price = $this->calcBookingRaw($row['bk_id']);
            $doanh_so = $info_price['total_profit'];
            global $timedate;
            $date_ticket_issue = !empty($row['date_ticket_issue']) ? $timedate->to_display_date($row['date_ticket_issue']) : '';
            $date_entered = !empty($row['date_entered']) ? $timedate->to_display_date_time($row['date_entered']) : '';

            $filter_airline_code = myNormalizeAirlineCode($row['airline_code']);
            if (empty($filter_airline_code)) $filter_airline_code = 'N/A';
            $airline_disp = $this->airlineDisplayName($filter_airline_code) . ' (' . $filter_airline_code . ')';
            //<td class="center">' . format_number($doanh_so) . '</td> tạm ẩn
            $html .= '
                <tr class="booking-row" data-airline="' . $filter_airline_code . '" data-qty="' . $row['ticket_qty'] . '" data-amount="' . $doanh_so . '">
                    <td class="center stt-cell">' . ($i + 1) . '</td>
                    <td class="center"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a></td>
                    <td class="center">' . $airline_disp . '</td>
                    <td class="center">' . $direction . '</td>
                    <td class="center">' . format_number($row['ticket_qty']) . '</td>
                    <td class="center">' . $date_ticket_issue . '</td>
                    <td class="center">' . $date_entered . '</td>
                </tr>
            ';
            $i++;
            $total_ticket_qty += $row['ticket_qty'];
            $total_amount += $doanh_so;
        }

        // ===== Thêm dòng PT (phiếu thu 4/5) và HV (hoàn vé) phát sinh theo ngày chứng từ =====
        foreach (array($ptc, $hvc) as $contrib) {
            if (empty($contrib) || empty($contrib['rows'])) continue;
            foreach ($contrib['rows'] as $r) {
                $ve_qty = (int) $r['ve']; // HV: âm; PT: 0
                $ve_cell = ($ve_qty != 0) ? format_number($ve_qty) : '-';
                $airline_disp = $this->airlineDisplayName($r['airline']) . ' (' . $r['airline'] . ')';
                $html .= '
                <tr class="booking-row" data-airline="' . $r['airline'] . '" data-qty="' . $ve_qty . '" data-amount="' . $r['profit'] . '">
                    <td class="center stt-cell">' . ($i + 1) . '</td>
                    <td class="center"><a href="index.php?module=' . $r['link_module'] . '&action=DetailView&record=' . $r['link_id'] . '" target="_blank">' . $r['name'] . '</a></td>
                    <td class="center">' . $airline_disp . '</td>
                    <td class="center">' . $r['direction'] . '</td>
                    <td class="center">' . $ve_cell . '</td>
                    <td class="center">' . $r['date'] . '</td>
                    <td class="center"></td>
                </tr>
            ';
                $i++;
                $total_ticket_qty += $ve_qty;
            }
        }

        $html = str_replace(
            array(
                '$TOTAL_TICKET_QTY',
                // '$TOTAL_AMOUNT'
            ),
            array(
                format_number($total_ticket_qty),
                // format_number($total_amount)
            ),
            $html
        );

        return $html;
    }
}
