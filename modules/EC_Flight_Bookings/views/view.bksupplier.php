<?php
require_once("include/Sugar_Smarty.php");

/**
 * Thống kê vé theo Nhà cung cấp (NCC).
 *
 * Khác với "Thống kê vé" (bkagent - gom theo hãng, tiền ở cấp booking qua calculateBKAmt):
 * báo cáo này lấy theo mô hình CÔNG NỢ PHẢI TRẢ NCC:
 *   - Số tiền lấy trực tiếp từ dòng vé ec_booking_details (total_bought_price = công nợ phải trả).
 *   - Ghi nhận theo NGÀY XUẤT VÉ CỦA TỪNG CHẶNG:
 *       IF(direction = 0, date_ticket_issue, date_ticket_inbound_issue).
 *   - Lọc booking hợp lệ giống debtopay: booking_status IN (7,8) AND is_ticket_exported = 1.
 */
class Viewbksupplier extends SugarView
{
     function display()
     {
          global $current_user;

          if (is_admin($current_user) || isManagerUser($current_user->id)) {
               $smartyCont = new Sugar_Smarty();
               $this->populateContent($smartyCont);
               $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_bksupplier.tpl');
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

          // NCC được chọn (rỗng = tất cả)
          $supplier_id = !empty($_REQUEST['supplier_id']) ? $_REQUEST['supplier_id'] : '';

          // OPTION DATE - check quarter (đồng bộ với bkagent)
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
          $smarty->assign('SUPPLIER_OPTIONS', $this->getSupplierOptions($supplier_id));
          $smarty->assign('SUPPLIER_SUMMARY_TBL', $this->populateSupplierSummary($from_date, $to_date, $supplier_id));
          $smarty->assign('SUPPLIER_DETAIL_TBL', $this->populateSupplierAirlineDetail($from_date, $to_date, $supplier_id));
     }

     /**
      * Dropdown chọn NCC (Supplier còn theo dõi). Rỗng = tất cả.
      */
     function getSupplierOptions($selected)
     {
          global $db;
          $html = '<option value="">-- Tất cả NCC --</option>';
          $sql = "SELECT id, ticker_symbol, name
                  FROM accounts
                  WHERE deleted = 0 AND account_type = 'Supplier' AND is_stop_tracking = 0
                  ORDER BY name";
          $res = $db->query($sql);
          while ($r = $db->fetchByAssoc($res)) {
               $label = trim(($r['ticker_symbol'] !== '' ? $r['ticker_symbol'] . ' - ' : '') . $r['name']);
               $sel = ($selected === $r['id']) ? ' selected' : '';
               $html .= '<option value="' . $r['id'] . '"' . $sel . '>' . htmlspecialchars($label) . '</option>';
          }
          return $html;
     }

     /**
      * Điều kiện WHERE dùng chung cho cả summary & detail.
      */
     private function buildWhere($from_date, $to_date, $supplier_id)
     {
          global $db;
          $from = date('Y-m-d', strtotime($from_date));
          $to   = date('Y-m-d', strtotime($to_date));

          $where = " d.deleted = 0
                    AND p.deleted = 0
                    AND p.booking_status IN ('7','8')
                    AND p.is_ticket_exported = 1
                    AND d.supplier_id IS NOT NULL AND d.supplier_id <> ''
                    AND (IF(d.direction = 0, p.date_ticket_issue, p.date_ticket_inbound_issue)
                         BETWEEN '" . $from . " 00:00:00' AND '" . $to . " 23:59:59') ";
          if (!empty($supplier_id)) {
               $where .= " AND d.supplier_id = '" . $db->quote($supplier_id) . "' ";
          }
          return $where;
     }

     /**
      * Bảng tổng hợp: mỗi dòng = 1 NCC. Trọng tâm Tổng giá mua (công nợ phải trả).
      */
     function populateSupplierSummary($from_date, $to_date, $supplier_id)
     {
          global $db;

          $html = '
            <tr id="supplier_total_row">
                <td></td>
                <td class="text-center text-decoration-underline"><b>Tổng</b></td>
                <td class="text-center"><b>$TOTAL_BK</b></td>
                <td class="text-center"><b>$TOTAL_VE</b></td>
                <td class="text-center"><b>$TOTAL_CK</b></td>
                <td class="text-center"><b>$TOTAL_FEE</b></td>
                <td class="text-center"><b>$TOTAL_BAN</b></td>
                <td class="text-center"><b>$TOTAL_MUA</b></td>
            </tr>
        ';

          $sql = "
            SELECT d.supplier_id,
                   a.ticker_symbol AS supplier_code,
                   a.name          AS supplier_name,
                   COUNT(DISTINCT d.booking_id)        AS sl_bk,
                   SUM(IFNULL(d.quantity, 0))          AS sl_ve,
                   SUM(IFNULL(d.supplier_discount, 0)) AS chiet_khau,
                   SUM(IFNULL(d.fee_bought, 0))        AS phi_xuat_ve,
                   SUM(IFNULL(d.total_price, 0))       AS gia_ban,
                   SUM(IFNULL(d.total_bought_price, 0)) AS gia_mua
            FROM ec_booking_details d
            INNER JOIN ec_flight_bookings p ON d.booking_id = p.id
            LEFT JOIN accounts a ON a.id = d.supplier_id AND a.deleted = 0
            WHERE " . $this->buildWhere($from_date, $to_date, $supplier_id) . "
            GROUP BY d.supplier_id, a.ticker_symbol, a.name
            ORDER BY gia_mua DESC
        ";

          $res = $db->query($sql);
          $i = 0;
          $t_bk = $t_ve = $t_ck = $t_fee = $t_ban = $t_mua = 0;
          while ($row = $db->fetchByAssoc($res)) {
               $sup_code = !empty($row['supplier_code']) ? $row['supplier_code'] : '';
               $sup_name = !empty($row['supplier_name']) ? $row['supplier_name'] : 'N/A';
               $label = trim(($sup_code !== '' ? $sup_code . ' - ' : '') . $sup_name);

               $html .= '
                <tr class="supplier-row cursor-pointer" data-supplier="' . $row['supplier_id'] . '" title="Bấm để xem chi tiết theo hãng của NCC này">
                    <td class="text-center">' . ($i + 1) . '</td>
                    <td class="text-center fw-semibold text-decoration-underline">
                        <a href="index.php?module=Accounts&action=DetailView&record=' . $row['supplier_id'] . '" target="_blank">' . htmlspecialchars($label) . '</a>
                    </td>
                    <td class="text-center">' . format_number($row['sl_bk']) . '</td>
                    <td class="text-center">' . format_number($row['sl_ve']) . '</td>
                    <td class="text-center">' . format_number($row['chiet_khau']) . '</td>
                    <td class="text-center">' . format_number($row['phi_xuat_ve']) . '</td>
                    <td class="text-center">' . format_number($row['gia_ban']) . '</td>
                    <td class="text-center fw-bold">' . format_number($row['gia_mua']) . '</td>
                </tr>
            ';
               $i++;
               $t_bk  += $row['sl_bk'];
               $t_ve  += $row['sl_ve'];
               $t_ck  += $row['chiet_khau'];
               $t_fee += $row['phi_xuat_ve'];
               $t_ban += $row['gia_ban'];
               $t_mua += $row['gia_mua'];
          }

          if ($i === 0) {
               $html .= '<tr><td colspan="8" class="text-center text-muted">Không có dữ liệu trong kỳ.</td></tr>';
          }

          $html = str_replace(
               array('$TOTAL_BK', '$TOTAL_VE', '$TOTAL_CK', '$TOTAL_FEE', '$TOTAL_BAN', '$TOTAL_MUA'),
               array(
                    format_number($t_bk),
                    format_number($t_ve),
                    format_number($t_ck),
                    format_number($t_fee),
                    format_number($t_ban),
                    format_number($t_mua),
               ),
               $html
          );

          return $html;
     }

     /**
      * Bảng chi tiết: tách theo NCC × Hãng bay.
      * Hãng lấy theo chặng (itinerary khớp direction) bằng subquery LIMIT 1 để KHÔNG nhân đôi
      * giá trị dòng vé khi chặng có nhiều segment nối chuyến.
      */
     function populateSupplierAirlineDetail($from_date, $to_date, $supplier_id)
     {
          global $db;

          $sql = "
            SELECT t.supplier_id, t.supplier_code, t.supplier_name,
                   CASE
                        WHEN t.airline_code = 'VJ' THEN 'VJA'
                        WHEN t.airline_code = 'VN' THEN 'VNA'
                        WHEN t.airline_code IS NULL OR t.airline_code = '' THEN 'N/A'
                        ELSE t.airline_code
                   END AS airline_code,
                   SUM(IFNULL(t.quantity, 0))          AS sl_ve,
                   SUM(IFNULL(t.supplier_discount, 0)) AS chiet_khau,
                   SUM(IFNULL(t.fee_bought, 0))        AS phi_xuat_ve,
                   SUM(IFNULL(t.total_price, 0))       AS gia_ban,
                   SUM(IFNULL(t.total_bought_price, 0)) AS gia_mua
            FROM (
                SELECT d.supplier_id,
                       a.ticker_symbol AS supplier_code,
                       a.name          AS supplier_name,
                       d.quantity, d.supplier_discount, d.fee_bought, d.total_price, d.total_bought_price,
                       (
                            SELECT i.airline_code
                            FROM ec_booking_itineraries i
                            WHERE i.booking_id = d.booking_id
                              AND i.direction = d.direction
                              AND i.deleted = 0
                            LIMIT 1
                       ) AS airline_code
                FROM ec_booking_details d
                INNER JOIN ec_flight_bookings p ON d.booking_id = p.id
                LEFT JOIN accounts a ON a.id = d.supplier_id AND a.deleted = 0
                WHERE " . $this->buildWhere($from_date, $to_date, $supplier_id) . "
            ) t
            GROUP BY t.supplier_id, t.supplier_code, t.supplier_name, airline_code
            ORDER BY t.supplier_name, gia_mua DESC
        ";

          $res = $db->query($sql);
          $html = '';
          $i = 0;
          while ($row = $db->fetchByAssoc($res)) {
               $sup_code = !empty($row['supplier_code']) ? $row['supplier_code'] : '';
               $sup_name = !empty($row['supplier_name']) ? $row['supplier_name'] : 'N/A';
               $label = trim(($sup_code !== '' ? $sup_code . ' - ' : '') . $sup_name);

               // tên hãng
               $airline_code = $row['airline_code'];
               $airline_label = $airline_code;
               if ($airline_code !== 'N/A') {
                    $airline = myGetAirlineInfo2($airline_code, 'CODE');
                    $airline_name = isset($airline['data'][0]['name']) ? $airline['data'][0]['name'] : '';
                    if (!empty($airline_name)) {
                         $airline_label = $airline_name . ' (' . $airline_code . ')';
                    }
               }

               $html .= '
                <tr class="supplier-detail-row" data-supplier="' . $row['supplier_id'] . '" style="display:none;">
                    <td class="text-center detail-stt">' . ($i + 1) . '</td>
                    <td class="text-center">' . htmlspecialchars($label) . '</td>
                    <td class="text-center">' . htmlspecialchars($airline_label) . '</td>
                    <td class="text-center">' . format_number($row['sl_ve']) . '</td>
                    <td class="text-center">' . format_number($row['chiet_khau']) . '</td>
                    <td class="text-center">' . format_number($row['phi_xuat_ve']) . '</td>
                    <td class="text-center">' . format_number($row['gia_ban']) . '</td>
                    <td class="text-center fw-bold">' . format_number($row['gia_mua']) . '</td>
                </tr>
            ';
               $i++;
          }

          if ($html === '') {
               $html = '<tr><td colspan="8" class="text-center text-muted">Bấm vào một NCC ở bảng trên để xem chi tiết theo hãng.</td></tr>';
          }

          return $html;
     }
}
