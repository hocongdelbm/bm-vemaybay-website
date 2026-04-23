<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewioinvoice extends SugarView
{
    function display()
    {
        $smarty = new Sugar_Smarty();
        $html = $this->populateIOInvoiceVoucherList($smarty, $_POST);
        if (isset($_POST['export_excel'])) {
            ob_clean();
            header("Pragma: cache");
            require_once('modules/EC_HoaDonBan/views/ioinvoice.xls.php');
            $xls = generateXLSTemplate($html);
            $xls = chr(255) . chr(254) . mb_convert_encoding($xls, "UTF-16LE", "UTF-8");
            header("Content-type: application/x-msdownload");
            header("Content-disposition: xls; filename=BangKeHoaDon_" . time() . ".xls; size=" . strlen($xls));
            echo $xls;
            exit();
        } else {
            $smarty->assign('IO_INV_TBL', $html);
            $smarty->display('modules/EC_HoaDonBan/tpls/ioinvoice.tpl');
        }
    }

    function populateIOInvoiceVoucherList($smarty, $post_fields)
    {
        global $current_user, $app_list_strings;

        $html = '<table id="io_inv_tbl" class="table-io_inv_tbl table-details__sticky table-details__booking" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th width="3%">STT</th>
                <th width="8%">Ngày HĐ</th>
                <th width="8%">Số vé</th>
                <th width="3%">SL</th>
                <th width="8%">GT mua vào<br>chưa VAT</th>
                <th width="8%">VAT<br>(mua vào)</th>
                <th width="8%">Thu hộ<br>(mua vào)</th>
                <th width="8%">Booking<br>(mua vào)</th>
                <th width="8%">GT bán ra<br>chưa VAT</th>
                <th width="8%">VAT<br>(bán ra)</th>
                <th width="8%">Thu hộ<br>(bán ra)</th>
                <th width="8%">Booking<br>(bán ra)</th>
                <th width="8%">Chênh lệch VAT</th>
                <th>Đơn vị</th>
            </tr>
        </thead>
        <tbody>';

        $where = $this->populateCondition($smarty, $post_fields);
        $sql = "SELECT dt.name
                ,dt.soluong
                ,dt.dongia
                ,dt.tienthue
                ,dt.phithuho
                ,dt.thanhtien
                ,i.ngayhoadon
                ,i.id AS output_inv
                ,out_bk.id AS out_bk_id
                ,out_bk.name AS out_bk
                ,in_inv.cost_no_vat
                ,in_inv.qty
                ,in_inv.vat
                ,in_inv.authorized_fee
                ,in_inv.total
                ,i.company_unit
                ,in_bk.id AS in_bk_id
                ,in_bk.name AS in_bk
            FROM ec_chitiethoadon dt
                LEFT JOIN ec_hoadonban i ON i.deleted = 0 AND dt.parent_id = i.id
                LEFT JOIN ec_flight_bookings out_bk ON out_bk.id = dt.booking_id
                    AND out_bk.deleted = 0
                LEFT JOIN ec_input_invoices in_inv ON in_inv.deleted = 0
                    AND in_inv.id = dt.ticket_number_id
                LEFT JOIN ec_flight_bookings in_bk ON in_bk.deleted = 0
                    AND in_bk.id = in_inv.booking_id
            WHERE dt.deleted = 0
                AND dt.ticket_number_id IS NOT NULL
                $where
            ORDER BY i.ngayhoadon, i.date_entered";

        $res = $this->bean->db->query($sql);
        $i = $total = $total_qty = $total_purchase = $total_vat1 = $total_author1 = $total_sell = $total_vat2 = $total_author2 = 0;

        while ($row = $this->bean->db->fetchByAssoc($res)) {
            // COST_NO_VAT
            $cost_no_vat    = empty($row['qty']) ? '' : format_number($row['cost_no_vat'] / $row['qty'] * $row['soluong']);
            $cost_vat       = empty($row['qty']) ? '' : format_number($row['vat'] / $row['qty'] * $row['soluong']);
            $authorized_fee = empty($row['qty']) ? '' : format_number($row['authorized_fee'] / $row['qty'] * $row['soluong']);
            $cost_vat_calc  = empty($row['qty']) ? 0 : ($row['vat'] / $row['qty'] * $row['soluong']);
            $difference_vat = format_number($row['tienthue'] - $cost_vat_calc);

            $html .= '<tr>
                <td class="text-center stt">' . ($i + 1) . '</td>
                <td class="text-center ngayhoadon">' . date('d-m-Y', strtotime($row['ngayhoadon'])) . '</td>
                <td class="text-center name">' . $row['name'] . '</td>
                <td class="text-center soluong">' . format_number($row['soluong']) . '</td>
                <td class="text-end cost_no_vat"><a href="index.php?module=EC_HoaDonBan&action=inputinvoice&ticket_code=' . $row['name'] . '" target="_blank">' . $cost_no_vat . '</a></td>
                <td class="text-end cost_vat">' . $cost_vat . '</td>
                <td class="text-end authorized_fee">' . $authorized_fee . '</td>
                <td class="text-center in_bk"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['in_bk_id'] . '" target="_blank">' . $row['in_bk'] . '</a></td>
                <td class="text-end dongia"><a href="index.php?module=EC_HoaDonBan&action=DetailView&record=' . $row['output_inv'] . '" target="_blank">' . format_number($row['dongia'] * $row['soluong']) . '</a></td>
                <td class="text-end tienthue">' . format_number($row['tienthue']) . '</td>
                <td class="text-end phithuho">' . format_number($row['phithuho'] * $row['soluong']) . '</td>
                <td class="text-center out_bk"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['out_bk_id'] . '" target="_blank">' . $row['out_bk'] . '</a></td>
                <td class="text-end difference_vat">' . $difference_vat . '</td>
                <td class="text-end company_unit_column">' . $app_list_strings['company_unit_invoice_list'][$row['company_unit']] . '</td>
            </tr>';
            $i++;

            $total_qty += $row['soluong'];
            if(!empty($row['qty'])){
                $total_purchase += $row['cost_no_vat'] / $row['qty'] * $row['soluong'];
                $total_vat1     += $row['vat'] / $row['qty'] * $row['soluong'];
                $total_author1  += $row['authorized_fee'] / $row['qty'] * $row['soluong'];
            } 
            $total              += ($row['tienthue'] - $cost_vat_calc);
            $total_sell         += $row['dongia'] * $row['soluong'];
            $total_vat2         += $row['tienthue'];
            $total_author2      += $row['phithuho'] * $row['soluong'];
        }
        $html .= '<tr class="footer-tr">
            <td colspan="3" class="text-end"><b>Tổng</b></td>
            <td class="text-end"><b>' . format_number($total_qty) . '</b></td>
            <td class="text-end"><b>' . format_number($total_purchase) . '</b></td>
            <td class="text-end"><b>' . format_number($total_vat1) . '</b></td>
            <td class="text-end"><b>' . format_number($total_author1) . '</b></td>
            <td class="text-end"></td>
            <td class="text-end"><b>' . format_number($total_sell) . '</b></td>
            <td class="text-end"><b>' . format_number($total_vat2) . '</b></td>
            <td class="text-end"><b>' . format_number($total_author2) . '</b></td>
            <td class="text-end"></td>
            <td class="text-end"><b>' . format_number($total) . '</b></td>
            <td class="text-end"></td>
        </tr>';
        $html .= '</tbody></table>';
        return $html;
    }

    function populateCondition($smarty, $post_fields)
    {
        global $app_list_strings;
        $sql = '';

        if (empty($post_fields['ticket_code']) || isset($post_fields['clear_btn'])) {
            // Từ ngày
            if (!empty($post_fields['from_date'])) {
                $post_fields['from_date'] = date('Y-m-d', strtotime($post_fields['from_date']));
            } else {
                $post_fields['from_date'] = date('Y-m-01');
            }
            $sql .= ' AND i.ngayhoadon >= "' . $post_fields['from_date'] . '"';
            $post_fields['from_date'] = date('d-m-Y', strtotime($post_fields['from_date']));

            // Đến ngày
            if (!empty($post_fields['to_date'])) {
                $post_fields['to_date'] = date('Y-m-d', strtotime($post_fields['to_date']));
            } else {
                $post_fields['to_date'] = date('Y-m-t');
            }
            $sql .= ' AND i.ngayhoadon <= "' . $post_fields['to_date'] . '"';
            $post_fields['to_date'] = date('d-m-Y', strtotime($post_fields['to_date']));

            // Số vé
            $post_fields['ticket_code'] = '';
            
            // Company
            if(!empty($post_fields['company_unit'])) {
                $sql .= ' AND i.company_unit = "' . $post_fields['company_unit'] . '"';
            } else{
                $post_fields['company_unit'] = '';
            }
        }
        else {
            $post_fields['from_date'] = $post_fields['to_date'] = '';
            $sql .= ' AND dt.name = "' . $post_fields['ticket_code'] . '"';

            // Company
            if(!empty($post_fields['company_unit'])) {
                $sql .= ' AND i.company_unit = "' . $post_fields['company_unit'] . '"';
            } else{
                $post_fields['company_unit'] = '';
            }
        }

        $smarty->assign('FROM_DATE', $post_fields['from_date']);
        $smarty->assign('TO_DATE', $post_fields['to_date']);
        $smarty->assign('TICKET_CODE', $post_fields['ticket_code']);
        $smarty->assign('COMPANY_UNIT_OPTION', get_select_options_with_id($app_list_strings['company_unit_invoice_list'], $post_fields['company_unit']));
        return $sql;
    }
}
