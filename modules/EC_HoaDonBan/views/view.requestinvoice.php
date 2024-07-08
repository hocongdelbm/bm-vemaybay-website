<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewrequestinvoice extends SugarView {
    function display() {
        $smarty = new Sugar_Smarty();
        $this->populateOIVoucherList($smarty, $_POST);
        $smarty->display('modules/EC_HoaDonBan/tpls/requestinvoice.tpl');
    }

    function populateOIVoucherList($smarty, $post_fields) {
        $where = $this->populateCondition($smarty, $post_fields);

        $sql    = '
            SELECT id, name, DATE_ADD(date_entered, INTERVAL 7 HOUR) AS date_entered,
                company_name, tax_code, company_address, shipping_address
                , (
                    SELECT GROUP_CONCAT(DISTINCT CONCAT_WS(",", hd.id, hd.name) SEPARATOR "|") 
                    FROM ec_chitiethoadon ct INNER JOIN ec_hoadonban hd 
                        ON hd.id = ct.parent_id AND hd.deleted = 0
                    WHERE ct.deleted = 0 AND ct.booking_id = ec_flight_bookings.id
                ) AS output_inv
            FROM ec_flight_bookings 
            WHERE deleted = 0 AND booking_status = 8
            ' . $where . '
            ORDER BY date_entered DESC';
        $res    = $this->bean->db->query($sql);
        $i      = 0; 
        $html   = '';
        while($row = $this->bean->db->fetchByAssoc($res)) {
            $inv_arr = json_decode(str_replace("&quot;", "\"", $row['shipping_address']), 1);

            // Xuất hoá đơn công ty thì phải có tên công ty và mst công ty 
            // Xuất hoá đơn cho cá nhân thì phải có tên khách hàng và mst hoặc địa chỉ
            if((!empty($row['company_name']) && !empty($row['tax_code'])) || (!empty($inv_arr['iv_account_name']) && (!empty($row['tax_code']) || !empty($row['company_address'])))) {
                switch(mb_convert_encoding($inv_arr['iv_payment_method'], 'UTF-8', 'HTML-ENTITIES')) {
                    case 'Tiền mặt':
                        $payment_method = 'TM';
                        break;
                    case 'Chuyển khoản':
                        $payment_method = 'CK';
                        break;
                    case 'Tiền mặt hoặc Chuyển khoản':
                        $payment_method = 'TM/CK';
                        break;
                    default:
                        break;
                }

                // Hoá đơn đầu ra
                $output_inv_html = '';
                $output_inv = explode('|', $row['output_inv']);
                for($k = 0; $k < count($output_inv); $k++) {
                    if(!empty($output_inv[$k])) {
                        $output_inv_inf = explode(',', $output_inv[$k]);
                        if(!empty($output_inv_html)) $sep = ', ';
                        else $sep = '';
                        $output_inv_html .= $sep . '<a href="index.php?module=EC_HoaDonBan&action=DetailView&record=' . $output_inv_inf[0] . '" target="_blank">' . $output_inv_inf[1] . '</a>';
                    }
                }
                $html .= '
                    <tr>
                        <td class="text-center">' . ($i + 1) . '</td>
                        <td class="text-center">' . date('d-m-Y', strtotime($row['date_entered'])) . '</td>
                        <td class="text-center">
                            <a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['id'] . '" target="_blank">' . $row['name'] . '</a>
                            <form action="index.php" method="post" target="_blank" class="mt-2">
                                <input type="hidden" name="module" value="EC_HoaDonBan">
                                <input type="hidden" name="action" value="EditView">
                                <input type="hidden" name="lienhe" value="' . $inv_arr['iv_account_name'] . '">
                                <input type="hidden" name="tencongty" value="' . mb_strtoupper($row['company_name'], 'UTF-8') . '">
                                <input type="hidden" name="masothue" value="' . $row['tax_code'] . '">
                                <input type="hidden" name="diachi" value="' . mb_convert_case(mb_strtolower($row['company_address'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8') . '">
                                <input type="hidden" name="email" value="' . $inv_arr['iv_email'] . '">
                                <input type="hidden" name="hinhthuctt" value="' . $payment_method . '">
                                <input type="hidden" name="sotaikhoan" value="' . $inv_arr['iv_bank_account'] . '">
                                <input type="hidden" name="booking" value="' . $row['name'] . '">
                                <input type="hidden" name="booking_id" value="' . $row['id'] . '">

                                <input type="submit" name="create_invoice" value="Tạo hoá đơn" class="btn btn-primary">
                            </form>
                        </td>
                        <td>' . $inv_arr['iv_account_name'] . '</td>
                        <td>' . mb_strtoupper($row['company_name'], 'UTF-8') . '</td>
                        <td>' . $row['tax_code'] . '</td>
                        <td class="break-word">' . mb_convert_case(mb_strtolower($row['company_address'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8') . '</td>
                        <td class="break-word">' . $inv_arr['iv_email'] . '</td>
                        <td>' . $output_inv_html . '</td>
                    </tr>';
                $i++;
            }
        }
        $smarty->assign('OUTPUT_INV_TBL', $html);
    }

    function populateCondition($smarty, $post_fields) {
        $sql = '';
        // Tìm kiếm theo booking
        if (!empty($post_fields['booking']) && !isset($post_fields['clear_btn'])) {
            $post_fields['from_date'] = $post_fields['to_date'] = '';
            $sql = ' AND name = "' . $post_fields['booking'] . '"';
        } 
        // Tìm kiếm mặc định
        else {
            // Từ ngày
            if(!empty($post_fields['from_date'])) {
                $post_fields['from_date'] = date('Y-m-d', strtotime($post_fields['from_date']));
            } else {
                $post_fields['from_date'] = date('Y-m-d');
            }
            $sql .= ' AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . $post_fields['from_date'] . '"';
            $post_fields['from_date'] = date('d-m-Y', strtotime($post_fields['from_date']));

            // Đến ngày
            if (!empty($post_fields['to_date'])) {
                $post_fields['to_date'] = date('Y-m-d', strtotime($post_fields['to_date']));
            } else {
                $post_fields['to_date'] = date('Y-m-d');
            }
            $sql .= ' AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . $post_fields['to_date'] . '"';
            $post_fields['to_date'] = date('d-m-Y', strtotime($post_fields['to_date']));

            // Booking
            $post_fields['booking'] = '';
        }

        $smarty->assign('FROM_DATE', $post_fields['from_date']);
        $smarty->assign('TO_DATE', $post_fields['to_date']);
        $smarty->assign('BOOKING', $post_fields['booking']);
        return $sql;
    }
}
