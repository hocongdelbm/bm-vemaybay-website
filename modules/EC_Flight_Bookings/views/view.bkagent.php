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
        $smarty->assign('VERSION', '1.2.0');
    }

    /**
     * Tên hiển thị của hãng theo mã.
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
     * Map booking_id -> ['airline' => mã hãng chuẩn hoá, 'dir' => nhãn chiều bay].
     * Hãng lấy theo chặng đầu (Lượt đi). Chiều bay suy từ tập direction của các chặng.
     * Theo nghiệp vụ: 1 booking = 1 hãng (đổi hãng phải hoàn vé + tạo booking mới).
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

        // Lấy tất cả chặng của các booking, gom trong PHP: hãng (chặng đầu) + tập chiều bay.
        $sql = "SELECT booking_id, direction, airline_code
                FROM ec_booking_itineraries
                WHERE deleted = 0 AND booking_id IN (" . implode(',', $inList) . ")
                ORDER BY booking_id, direction ASC, CAST(IFNULL(sabre_logs,0) AS UNSIGNED) ASC";
        $res = $db->query($sql);

        $tmp = array(); // booking_id => ['airline' => code, 'dirs' => [dir => true]]
        while ($row = $db->fetchByAssoc($res)) {
            $bid = $row['booking_id'];
            if (!isset($tmp[$bid])) $tmp[$bid] = array('airline' => '', 'dirs' => array());
            // hãng = chặng đầu tiên có mã (đã ORDER BY chiều đi trước)
            if ($tmp[$bid]['airline'] === '' && $row['airline_code'] !== '') {
                $tmp[$bid]['airline'] = myNormalizeAirlineCode($row['airline_code']);
            }
            $d = (string) $row['direction'];
            if ($d !== '') $tmp[$bid]['dirs'][$d] = true;
        }

        foreach ($tmp as $bid => $info) {
            $has0 = isset($info['dirs']['0']);
            $has1 = isset($info['dirs']['1']);
            if ($has0 && $has1) {
                $dir = 'Lượt đi & về';
            } elseif ($has0) {
                $dir = 'Lượt đi';
            } elseif ($has1) {
                $dir = 'Lượt về';
            } else {
                $dir = '-';
            }
            $map[$bid] = array(
                'airline' => ($info['airline'] !== '') ? $info['airline'] : 'N/A',
                'dir'     => $dir,
            );
        }
        return $map;
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

        // Gom theo hãng
        $byAirline = array();
        $detailRows = array();
        $g_ve = $g_dt = $g_gm = $g_ds = 0;
        $g_bk_ids = array();

        foreach ($rows as $r) {
            $bkid = isset($r['booking_id']) ? $r['booking_id'] : '';
            $info = (!empty($bkid) && isset($airlineMap[$bkid])) ? $airlineMap[$bkid] : null;
            $code = $info ? $info['airline'] : 'N/A';
            // Chiều bay chỉ có ý nghĩa với dòng booking (BK); PT/HV để '-'
            $dir_label = ($r['parent_type'] === 'EC_Flight_Bookings' && $info) ? $info['dir'] : '-';
            // Loại vé (Nội địa/Quốc tế) theo booking gắn với dòng này (áp dụng cả PT/HV nếu có booking_id)
            $ticket_type_label = (!empty($bkid) && isset($ticketTypeMap[$bkid])) ? $ticketTypeMap[$bkid] : '-';

            $ve = (int) $r['total_quantity'];
            $dt = (float) $r['subtotal_amount'];
            $gm = (float) $r['total_bought_price'];
            $ds = (float) $r['profit_amount'];

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
            $byAirline[$code]['ve'] += $ve;
            $byAirline[$code]['dt'] += $dt;
            $byAirline[$code]['gm'] += $gm;
            $byAirline[$code]['ds'] += $ds;
            if ($r['parent_type'] === 'EC_Flight_Bookings' && !empty($bkid)) {
                $byAirline[$code]['bk_ids'][$bkid] = true;
                $g_bk_ids[$bkid] = true;
            }

            $g_ve += $ve;
            $g_dt += $dt;
            $g_gm += $gm;
            $g_ds += $ds;

            // ngày hiển thị: BK theo ngày xuất vé; PT/HV theo ngày chứng từ (voucher_date)
            $date_show = ($r['parent_type'] === 'EC_Flight_Bookings')
                ? (!empty($r['date_ticket_issue']) ? $r['date_ticket_issue'] : '')
                : (!empty($r['voucher_date']) ? $r['voucher_date'] : '');

            $detailRows[] = array(
                'airline' => $code,
                'direction' => $dir_label,
                'ticket_type' => $ticket_type_label,
                'parent_type' => $r['parent_type'],
                'parent_id' => $r['parent_id'],
                'name' => $r['parent_name'],
                've' => $ve,
                'ds' => $ds,
                'date_show' => $date_show,
                'date_entered' => ($r['parent_type'] === 'EC_Flight_Bookings' && !empty($r['bk_date_entered'])) ? $r['bk_date_entered'] : '',
            );
        }

        // ===== Bảng tổng hợp theo hãng =====
        uasort($byAirline, function ($a, $b) {
            if ($b['ve'] !== $a['ve']) return $b['ve'] <=> $a['ve'];
            return $b['dt'] <=> $a['dt'];
        });

        $summary = '
            <tr class="airline-row cursor-pointer" data-airline="ALL" title="Bấm để xem tất cả">
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
            <tr id="booking_list_total_row">
                <td></td>
                <td class="center"><b>Tổng</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="center"><b id="total_filtered_ticket_qty">' . format_number($g_ve) . '</b></td>
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
                <tr class="' . $row_class . '" data-airline="' . $r['airline'] . '" data-qty="' . (int)$r['ve'] . '" data-amount="' . $r['ds'] . '">
                    <td class="center stt-cell">' . ($d + 1) . '</td>
                    <td class="center"><a href="index.php?module=' . $r['parent_type'] . '&action=DetailView&record=' . $r['parent_id'] . '" target="_blank">' . $r['name'] . '</a></td>
                    <td class="center">' . $airline_disp . '</td>
                    <td class="center">' . $r['direction'] . '</td>
                    <td class="center">' . $r['ticket_type'] . '</td>
                    <td class="center">' . $ve_cell . '</td>
                    <td class="center">' . $r['date_show'] . '</td>
                    <td class="center">' . $r['date_entered'] . '</td>
                </tr>
            ';
            $d++;
        }
        if ($d === 0) {
            $detail .= '<tr><td colspan="8" class="text-center text-muted">Không có dữ liệu trong kỳ.</td></tr>';
        }

        return array('summary' => $summary, 'detail' => $detail);
    }
}
