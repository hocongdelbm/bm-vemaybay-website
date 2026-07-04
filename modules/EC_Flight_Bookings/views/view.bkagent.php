<?php
require_once("include/Sugar_Smarty.php");

/**
 * Thống kê vé theo hãng (bkagent).
 *
 * Dùng CHUNG nguồn dữ liệu với báo cáo "Doanh thu bán vé" (report_sales_revenue):
 * hàm calculateRevenueOfDate() trả về mọi dòng BK + PT (phiếu thu 4/5/10-16) + HV (hoàn vé),
 * bkagent chỉ gom theo HÃNG. Nhờ vậy tổng của 2 báo cáo luôn khớp (một nguồn sự thật)
 * và tránh N truy vấn con như bản cũ (nhanh hơn).
 *
 * Suy ra hãng: theo hãng của booking (ec_booking_itineraries). Dòng không gắn booking
 * (VD phiếu thu khách sạn loại 14) rơi vào nhóm "Khác".
 *
 * Booking 2 chiều khác hãng (VD chặng đi VJA / chặng về VNA): được TÁCH theo từng chiều,
 * phân bổ tiền theo tỷ lệ giá mua (total_bought_price) của từng chiều trong
 * ec_booking_details — xem getDirectionSplitMap()/splitByDirection(). PT/HV chưa tách
 * theo chiều (giữ nguyên gán theo hãng đại diện).
 */
class Viewbkagent extends SugarView
{
    function display()
    {
        global $current_user;

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

        // OPTION DATE - check quarter
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

        $report = $this->buildAirlineReport($from_date, $to_date);
        $smarty->assign('AGENT_LIST_TBL', $report['summary']);
        $smarty->assign('BOOKING_LIST_TBL', $report['detail']);
        $smarty->assign('VERSION', '1.3.1');
    }

    /**
     * Tên hiển thị của hãng theo mã. Có memoize vì myGetAirlineInfo2() đọc + parse file
     * airlines.xml mỗi lần gọi — nếu gọi theo từng dòng (hàng trăm dòng) sẽ rất chậm.
     */
    private function airlineDisplayName($code)
    {
        static $cache = array();
        if (isset($cache[$code])) return $cache[$code];

        if ($code === 'N/A' || $code === '') {
            return $cache[$code] = 'Khác';
        }
        $airline = myGetAirlineInfo2($code, 'CODE');
        $name = isset($airline['data'][0]['name']) ? $airline['data'][0]['name'] : '';
        if (!empty($name)) {
            return $cache[$code] = $name;
        }
        if ($code === '0V') {
            return $cache[$code] = 'VASCO';
        }
        return $cache[$code] = 'Hãng khác';
    }

    /**
     * Map booking_id -> ['airline' => mã hãng đại diện, 'dir' => nhãn chiều bay,
     * 'dir_airline' => [direction => mã hãng], 'is_multi' => 2 chiều khác hãng].
     *
     * 'airline' lấy theo chặng đầu (Lượt đi) — dùng khi booking chỉ 1 hãng.
     * Khi 'is_multi' = true (đổi hãng ở chặng về, VD chặng đi VJA / chặng về VNA),
     * buildAirlineReport() sẽ phân bổ tiền/vé theo TỪNG CHIỀU thay vì gán hết vào 1 hãng
     * — xem getDirectionSplitMap()/splitByDirection().
     */
    private function getAirlineMap(array $bkIds)
    {
        global $db;
        $map = array();
        $inList = array();
        foreach ($bkIds as $id) {
            if (!empty($id)) $inList[] = "'" . $db->quote($id) . "'";
        }
        if (empty($inList)) return $map;

        // Lấy tất cả chặng của các booking, gom trong PHP: hãng theo từng chiều.
        $sql = "SELECT booking_id, direction, airline_code
                FROM ec_booking_itineraries
                WHERE deleted = 0 AND booking_id IN (" . implode(',', $inList) . ")
                ORDER BY booking_id, direction ASC, CAST(IFNULL(sabre_logs,0) AS UNSIGNED) ASC";
        $res = $db->query($sql);

        $tmp = array(); // booking_id => ['first' => code, 'dir_airline' => [dir => code]]
        while ($row = $db->fetchByAssoc($res)) {
            $bid = $row['booking_id'];
            if (!isset($tmp[$bid])) $tmp[$bid] = array('first' => '', 'dir_airline' => array());
            $code = ($row['airline_code'] !== '') ? myNormalizeAirlineCode($row['airline_code']) : '';
            // hãng đại diện = chặng đầu tiên có mã (đã ORDER BY chiều đi trước)
            if ($tmp[$bid]['first'] === '' && $code !== '') {
                $tmp[$bid]['first'] = $code;
            }
            $d = (string) $row['direction'];
            if ($d !== '' && $code !== '' && !isset($tmp[$bid]['dir_airline'][$d])) {
                $tmp[$bid]['dir_airline'][$d] = $code;
            }
        }

        foreach ($tmp as $bid => $info) {
            $has0 = isset($info['dir_airline']['0']);
            $has1 = isset($info['dir_airline']['1']);
            if ($has0 && $has1) {
                $dir = 'Lượt đi & về';
            } elseif ($has0) {
                $dir = 'Lượt đi';
            } elseif ($has1) {
                $dir = 'Lượt về';
            } else {
                $dir = '-';
            }
            $distinctAirlines = array_unique(array_values($info['dir_airline']));

            $map[$bid] = array(
                'airline'     => ($info['first'] !== '') ? $info['first'] : 'N/A',
                'dir'         => $dir,
                'dir_airline' => $info['dir_airline'], // [direction => code]
                'is_multi'    => count($distinctAirlines) > 1,
            );
        }
        return $map;
    }

    /**
     * Chỉ gọi cho các booking 'is_multi' = true (2 chiều khác hãng).
     * Trả booking_id => [direction => ['qty','price','bought']] lấy từ ec_booking_details —
     * đúng dữ liệu ở bảng "CHI TIẾT VÉ": Thành tiền (total_price = giá bán, đã gồm phí/thuế/
     * chiết khấu của dòng), Giá mua (total_bought_price) theo từng dòng Lượt đi/Lượt về.
     * Dùng làm cơ sở phân bổ doanh thu (theo giá bán) & giá mua (theo giá mua) theo chiều/hãng.
     */
    private function getDirectionSplitMap(array $bkIds)
    {
        global $db;
        $map = array();
        $inList = array();
        foreach ($bkIds as $id) {
            if (!empty($id)) $inList[] = "'" . $db->quote($id) . "'";
        }
        if (empty($inList)) return $map;

        $sql = "SELECT booking_id, direction,
                    SUM(IFNULL(quantity, 0)) AS qty,
                    SUM(IFNULL(total_price, 0)) AS price,
                    SUM(IFNULL(total_bought_price, 0)) AS bought
                FROM ec_booking_details
                WHERE deleted = 0 AND booking_id IN (" . implode(',', $inList) . ")
                GROUP BY booking_id, direction";
        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $d = (string) $row['direction'];
            $map[$row['booking_id']][$d] = array(
                'qty'    => (int) $row['qty'],
                'price'  => (float) $row['price'],
                'bought' => (float) $row['bought'],
            );
        }
        return $map;
    }

    /**
     * Phân bổ 1 dòng doanh thu BOOKING (subtotal_amount/total_bought_price ở cấp cả booking,
     * từ calculateRevenueOfDate) về từng (hãng, chiều):
     *   - Doanh thu (dt) chia theo TỶ LỆ GIÁ BÁN (total_price) từng chiều
     *   - Giá mua  (gm) chia theo TỶ LỆ GIÁ MUA (total_bought_price) từng chiều
     *   - Doanh số (ds) = dt - gm của chính chiều đó -> phản ánh ĐÚNG margin từng hãng
     *   - SL vé lấy đúng quantity của chiều đó
     *
     * Cách này xử lý luôn các khoản làm lệch doanh số khi tính theo hành trình:
     *   + Mã giảm giá / phí khác phát sinh ở cấp booking (chênh giữa bk.total_amount và
     *     SUM(total_price)) -> phân bổ theo tỷ lệ giá bán (đúng bên doanh thu).
     *   + Phí hành lý ở cấp booking (chênh bên giá mua) -> phân bổ theo tỷ lệ giá mua.
     * Vì mỗi tỷ lệ cộng lại = 1, tổng dt/gm/ds LUÔN bằng đúng giá trị gốc -> không lệch số.
     *
     * @return array danh sách phần ['airline','direction','ve','dt','gm','ds']
     */
    private function splitByDirection(array $dirAirline, array $dirAmounts, $totalVe, $totalDt, $totalGm, $totalDs)
    {
        $dirLabels = array('0' => 'Lượt đi', '1' => 'Lượt về');

        // hợp các chiều xuất hiện ở itinerary (có hãng) hoặc ở booking_details (có tiền/vé)
        $dirs = array_unique(array_merge(array_keys($dirAirline), array_keys($dirAmounts)));

        $sumPrice = $sumBought = $sumQty = 0;
        foreach ($dirs as $d) {
            $sumPrice  += isset($dirAmounts[$d]) ? $dirAmounts[$d]['price'] : 0;
            $sumBought += isset($dirAmounts[$d]) ? $dirAmounts[$d]['bought'] : 0;
            $sumQty    += isset($dirAmounts[$d]) ? $dirAmounts[$d]['qty'] : 0;
        }

        $parts = array();
        $n = count($dirs);
        foreach ($dirs as $d) {
            $qty    = isset($dirAmounts[$d]) ? $dirAmounts[$d]['qty'] : 0;
            $price  = isset($dirAmounts[$d]) ? $dirAmounts[$d]['price'] : 0;
            $bought = isset($dirAmounts[$d]) ? $dirAmounts[$d]['bought'] : 0;

            // tỷ lệ doanh thu theo giá bán; giá mua theo giá mua; fallback: SL vé, rồi chia đều
            if ($sumPrice > 0) {
                $ratioDt = $price / $sumPrice;
            } elseif ($sumQty > 0) {
                $ratioDt = $qty / $sumQty;
            } else {
                $ratioDt = $n > 0 ? (1 / $n) : 0;
            }
            if ($sumBought > 0) {
                $ratioGm = $bought / $sumBought;
            } elseif ($sumQty > 0) {
                $ratioGm = $qty / $sumQty;
            } else {
                $ratioGm = $n > 0 ? (1 / $n) : 0;
            }

            $dt = $totalDt * $ratioDt;
            $gm = $totalGm * $ratioGm;
            $parts[] = array(
                'airline'   => isset($dirAirline[$d]) ? $dirAirline[$d] : 'N/A',
                'direction' => isset($dirLabels[$d]) ? $dirLabels[$d] : '-',
                've'        => $qty,
                'dt'        => $dt,
                'gm'        => $gm,
                'ds'        => $dt - $gm, // margin thực của chiều/hãng này
            );
        }

        // an toàn: không tách được chiều nào -> giữ nguyên 1 phần "N/A" bằng tổng gốc
        if (empty($parts)) {
            $parts[] = array('airline' => 'N/A', 'direction' => '-', 've' => $totalVe, 'dt' => $totalDt, 'gm' => $totalGm, 'ds' => $totalDs);
        }

        return $parts;
    }

    /**
     * Map booking_id -> nhãn loại vé (Nội địa/Quốc tế), lấy từ ec_flight_bookings.ticket_type
     * (1 = Nội địa, 2 = Quốc tế theo booking_ticket_type_list).
     */
    private function getTicketTypeMap(array $bkIds)
    {
        global $db, $app_list_strings;
        $map = array();
        $inList = array();
        foreach ($bkIds as $id) {
            if (!empty($id)) $inList[] = "'" . $db->quote($id) . "'";
        }
        if (empty($inList)) return $map;

        $sql = "SELECT id, ticket_type FROM ec_flight_bookings WHERE id IN (" . implode(',', $inList) . ")";
        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $tt = $row['ticket_type'];
            $map[$row['id']] = isset($app_list_strings['booking_ticket_type_list'][$tt])
                ? $app_list_strings['booking_ticket_type_list'][$tt]
                : '-';
        }
        return $map;
    }

    /**
     * Gom dữ liệu doanh thu (BK + PT + HV) theo hãng -> trả 2 bảng HTML: summary & detail.
     */
    function buildAirlineReport($from_date, $to_date)
    {
        $fromD = date('Y-m-d', strtotime($from_date));
        $toD   = date('Y-m-d', strtotime($to_date));

        // 1 nguồn duy nhất (giống report_sales_revenue)
        $rev  = calculateRevenueOfDate($fromD, $toD, array());
        $rows = (!empty($rev['details']) && is_array($rev['details'])) ? $rev['details'] : array();

        // Map hãng cho toàn bộ booking liên quan (1 truy vấn)
        $bkIds = array();
        foreach ($rows as $r) {
            if (!empty($r['booking_id'])) $bkIds[$r['booking_id']] = true;
        }
        $airlineMap = $this->getAirlineMap(array_keys($bkIds));
        $ticketTypeMap = $this->getTicketTypeMap(array_keys($bkIds));

        // Booking 2 chiều khác hãng (VD chặng đi VJA / chặng về VNA) -> cần tách theo chiều.
        $multiBkIds = array();
        foreach ($airlineMap as $bid => $info) {
            if (!empty($info['is_multi'])) $multiBkIds[] = $bid;
        }
        $directionSplitMap = $this->getDirectionSplitMap($multiBkIds);

        // Gom theo hãng
        $byAirline = array();
        $detailRows = array();
        $g_ve = $g_dt = $g_gm = $g_ds = 0;
        $g_bk_ids = array();

        foreach ($rows as $r) {
            $bkid = isset($r['booking_id']) ? $r['booking_id'] : '';
            $info = (!empty($bkid) && isset($airlineMap[$bkid])) ? $airlineMap[$bkid] : null;
            // Loại vé (Nội địa/Quốc tế) theo booking gắn với dòng này (áp dụng cả PT/HV nếu có booking_id)
            $ticket_type_label = (!empty($bkid) && isset($ticketTypeMap[$bkid])) ? $ticketTypeMap[$bkid] : '-';

            $ve = (int) $r['total_quantity'];
            $dt = (float) $r['subtotal_amount'];
            $gm = (float) $r['total_bought_price'];
            $ds = (float) $r['profit_amount'];

            // Chỉ tách theo chiều cho dòng BOOKING có 2 chiều khác hãng; PT/HV giữ nguyên 1 dòng
            // (gán theo hãng đại diện/chặng đầu) vì chưa có cơ sở tách theo chiều cho chứng từ.
            $isMultiBooking = ($r['parent_type'] === 'EC_Flight_Bookings' && $info && !empty($info['is_multi']) && isset($directionSplitMap[$bkid]));

            if ($isMultiBooking) {
                $parts = $this->splitByDirection($info['dir_airline'], $directionSplitMap[$bkid], $ve, $dt, $gm, $ds);
            } else {
                $code = $info ? $info['airline'] : 'N/A';
                // Chiều bay chỉ có ý nghĩa với dòng booking (BK); PT/HV để '-'
                $dir_label = ($r['parent_type'] === 'EC_Flight_Bookings' && $info) ? $info['dir'] : '-';
                $parts = array(array('airline' => $code, 'direction' => $dir_label, 've' => $ve, 'dt' => $dt, 'gm' => $gm, 'ds' => $ds));
            }

            // ngày hiển thị: BK theo ngày xuất vé; PT/HV theo ngày chứng từ (voucher_date)
            $date_show = ($r['parent_type'] === 'EC_Flight_Bookings')
                ? (!empty($r['date_ticket_issue']) ? $r['date_ticket_issue'] : '')
                : (!empty($r['voucher_date']) ? $r['voucher_date'] : '');
            $date_entered = ($r['parent_type'] === 'EC_Flight_Bookings' && !empty($r['bk_date_entered'])) ? $r['bk_date_entered'] : '';

            foreach ($parts as $part) {
                $code = $part['airline'];

                if (!isset($byAirline[$code])) {
                    $byAirline[$code] = array(
                        'name' => $this->airlineDisplayName($code),
                        'bk_ids' => array(),
                        've' => 0,
                        'dt' => 0,
                        'gm' => 0,
                        'ds' => 0,
                    );
                }
                $byAirline[$code]['ve'] += $part['ve'];
                $byAirline[$code]['dt'] += $part['dt'];
                $byAirline[$code]['gm'] += $part['gm'];
                $byAirline[$code]['ds'] += $part['ds'];
                if ($r['parent_type'] === 'EC_Flight_Bookings' && !empty($bkid)) {
                    // booking đa hãng: được tính vào SL BK của MỖI hãng nó chạm tới
                    $byAirline[$code]['bk_ids'][$bkid] = true;
                }

                $g_ve += $part['ve'];
                $g_dt += $part['dt'];
                $g_gm += $part['gm'];
                $g_ds += $part['ds'];

                $detailRows[] = array(
                    'airline' => $code,
                    'direction' => $part['direction'],
                    'ticket_type' => $ticket_type_label,
                    'parent_type' => $r['parent_type'],
                    'parent_id' => $r['parent_id'],
                    'name' => $r['parent_name'],
                    've' => $part['ve'],
                    'dt' => $part['dt'],
                    'gm' => $part['gm'],
                    'ds' => $part['ds'],
                    'date_show' => $date_show,
                    'date_entered' => $date_entered,
                );
            }

            // Tổng SL BK: đếm booking đúng 1 lần dù được tách thành nhiều dòng theo hãng
            if ($r['parent_type'] === 'EC_Flight_Bookings' && !empty($bkid)) {
                $g_bk_ids[$bkid] = true;
            }
        }

        // ===== Bảng tổng hợp theo hãng =====
        uasort($byAirline, function ($a, $b) {
            if ($b['ve'] !== $a['ve']) return $b['ve'] <=> $a['ve'];
            return $b['dt'] <=> $a['dt'];
        });

        $summary = '
            <tr class="airline-row cursor-pointer bg-label-secondary" data-airline="ALL" title="Bấm để xem tất cả">
                <td></td>
                <td class="text-center text-decoration-underline"><b>Tổng</b></td>
                <td class="text-center"><b>' . format_number(count($g_bk_ids)) . '</b></td>
                <td class="text-center"><b>' . format_number($g_ve) . '</b></td>
                <td class="text-center"><b>' . format_number($g_dt) . '</b></td>
                <td class="text-center"><b>' . format_number($g_gm) . '</b></td>
                <td class="text-center"><b>' . format_number($g_ds) . '</b></td>
            </tr>
        ';
        $i = 0;
        foreach ($byAirline as $code => $a) {
            $summary .= '
                <tr class="airline-row cursor-pointer" data-airline="' . $code . '" title="Bấm để lọc vé của hãng này">
                    <td class="text-center">' . ($i + 1) . '</td>
                    <td class="text-center fw-semibold text-decoration-underline">' . $a['name'] . ' (' . $code . ')</td>
                    <td class="text-center">' . format_number(count($a['bk_ids'])) . '</td>
                    <td class="text-center">' . format_number($a['ve']) . '</td>
                    <td class="text-center">' . format_number($a['dt']) . '</td>
                    <td class="text-center">' . format_number($a['gm']) . '</td>
                    <td class="text-center">' . format_number($a['ds']) . '</td>
                </tr>
            ';
            $i++;
        }

        // ===== Bảng chi tiết (BK + PT + HV) =====
        // Sắp theo SL vé giảm dần
        usort($detailRows, function ($a, $b) {
            return $b['ve'] <=> $a['ve'];
        });

        $detail = '
            <tr id="booking_list_total_row" class="bg-label-secondary">
                <td></td>
                <td class="center"><b>Tổng</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="center"><b id="total_filtered_ticket_qty">' . format_number($g_ve) . '</b></td>
                <td class="center"><b id="total_filtered_dt">' . format_number($g_dt) . '</b></td>
                <td class="center"><b id="total_filtered_gm">' . format_number($g_gm) . '</b></td>
                <td class="center"><b id="total_filtered_ds">' . format_number($g_ds) . '</b></td>
                <td></td>
                <td></td>
            </tr>
        ';
        $d = 0;
        foreach ($detailRows as $r) {
            $airline_disp = $this->airlineDisplayName($r['airline']) . ' (' . $r['airline'] . ')';
            $ve_cell = ($r['ve'] != 0) ? format_number($r['ve']) : '-';
            $row_class = 'booking-row';
            if ($r['parent_type'] === 'EC_Receipt_Voucher') {
                $row_class .= ' bg-label-info';
            } elseif ($r['parent_type'] === 'EC_HoanVe') {
                $row_class .= ' bg-label-danger';
            }
            $detail .= '
                <tr class="' . $row_class . '" data-airline="' . $r['airline'] . '" data-qty="' . (int)$r['ve'] . '" data-dt="' . round($r['dt']) . '" data-gm="' . round($r['gm']) . '" data-amount="' . round($r['ds']) . '">
                    <td class="center stt-cell">' . ($d + 1) . '</td>
                    <td class="center"><a href="index.php?module=' . $r['parent_type'] . '&action=DetailView&record=' . $r['parent_id'] . '" target="_blank">' . $r['name'] . '</a></td>
                    <td class="center">' . $airline_disp . '</td>
                    <td class="center">' . $r['direction'] . '</td>
                    <td class="center">' . $r['ticket_type'] . '</td>
                    <td class="center">' . $ve_cell . '</td>
                    <td class="text-end">' . format_number($r['dt']) . '</td>
                    <td class="text-end">' . format_number($r['gm']) . '</td>
                    <td class="text-end">' . format_number($r['ds']) . '</td>
                    <td class="center">' . $r['date_show'] . '</td>
                    <td class="center">' . $r['date_entered'] . '</td>
                </tr>
            ';
            $d++;
        }
        if ($d === 0) {
            $detail .= '<tr><td colspan="11" class="text-center text-muted">Không có dữ liệu trong kỳ.</td></tr>';
        }

        return array('summary' => $summary, 'detail' => $detail);
    }
}
