<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewrequestinvoice extends SugarView {
    public function display() {
        $smarty = new Sugar_Smarty();
        $this->populateOIVoucherList($smarty, $_POST);
        $smarty->display('modules/EC_HoaDonBan/tpls/requestinvoice.tpl');
    }

    public function populateOIVoucherList($smarty, $post_fields) {
        global $app_list_strings;
        $where = $this->populateCondition($smarty, $post_fields);

        $sql = "SELECT bk.id AS booking_id
                ,bk.name AS booking_name
                ,bk.company_name
                ,bk.tax_code
                ,bk.company_address
                ,bk.shipping_address
                ,DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS date_entered
                ,(
                    SELECT GROUP_CONCAT(DISTINCT CONCAT_WS(',', rv.id, rv.name) SEPARATOR '|') 
                    FROM ec_receipt_voucher rv
                    WHERE rv.booking_id = bk.id
                        AND rv.deleted = 0
                        AND rv.loai_thu IN('1', '4', '5')
                        AND rv.receipt_type = 'credit_transfer'
                        AND rv.tknganhang_id IN(
                            'c0b01f56-0778-de4a-da11-65f937520972',
                            'ce10c2fc-2f04-0b3f-c1f1-654eee84f8f1',
                            '1eeaf2c3-9126-0406-36aa-64cc93693bc6',
                            '98adc9fa-6e96-4fe6-45bb-6524d2c20920'
                        )
                ) AS receipt_vouchers_data
                ,(
                    SELECT GROUP_CONCAT(DISTINCT CONCAT_WS(',', hd.id, hd.name) SEPARATOR '|') 
                    FROM ec_chitiethoadon ct
                        INNER JOIN ec_hoadonban hd ON hd.id = ct.parent_id
                    WHERE ct.booking_id = bk.id AND hd.deleted = 0 AND ct.deleted = 0
                ) AS out_inv_data
                ,(
                    SELECT GROUP_CONCAT(DISTINCT CONCAT_WS(',', inv.invoice_number, inv.supplier, inv.name) SEPARATOR '|')
                    FROM ec_input_invoices inv
                    WHERE inv.booking_id = bk.id AND inv.deleted = 0
                ) AS in_inv_data
            FROM ec_flight_bookings bk
            WHERE bk.booking_status = 8 AND bk.deleted = 0 $where
            ORDER BY bk.date_entered DESC";

        // $sql    = '
        //     SELECT id, name, DATE_ADD(date_entered, INTERVAL 7 HOUR) AS date_entered,
        //         company_name, tax_code, company_address, shipping_address
        //         , (
        //             SELECT GROUP_CONCAT(DISTINCT CONCAT_WS(",", hd.id, hd.name) SEPARATOR "|") 
        //             FROM ec_chitiethoadon ct INNER JOIN ec_hoadonban hd 
        //                 ON hd.id = ct.parent_id AND hd.deleted = 0
        //             WHERE ct.deleted = 0 AND ct.booking_id = ec_flight_bookings.id
        //         ) AS output_inv
        //     FROM ec_flight_bookings 
        //     WHERE deleted = 0 AND booking_status = 8
        //     ' . $where . '
        //     ORDER BY date_entered DESC';

        $i = 0;
        $res = $this->bean->db->query($sql);
        $html = '';
        while($row = $this->bean->db->fetchByAssoc($res)) {
            // Thông tin xuất hóa đơn
            $inv_arr = json_decode(html_entity_decode($row['shipping_address']), true);
            // MST
            $inv_tax_code = $row['tax_code'] ?? '';
            $inv_tax_code_html = !empty($inv_tax_code) ? $this->renderItem('MST / Mã KH', $inv_tax_code) : '';
            // Tên KH
            $inv_account_name = $inv_arr['iv_account_name'] ?? '';
            $inv_account_name_html = !empty($inv_account_name) ? $this->renderItem('Tên KH', $inv_account_name) : '';
            // Tên công ty
            $inv_company_name = mb_strtoupper($row['company_name'] ?? '', 'UTF-8');
            $inv_company_name_html = !empty($inv_company_name) ? $this->renderItem('Công ty', $inv_company_name) : '';
            // Email
            $inv_email = $inv_arr['iv_email'] ?? '';
            $inv_email_html = !empty($inv_email) ? $this->renderItem('Email', $inv_email) : '';
            // Địa chỉ
            $inv_address = mb_convert_case(mb_strtolower($row['company_address'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $inv_address_html = !empty($inv_address) ? $this->renderItem('Địa chỉ', $inv_address) : '';
            // Thanh toán
            $inv_bank_account = $inv_arr['iv_bank_account'] ?? '';
            $inv_payment_method = mb_convert_encoding($inv_arr['iv_payment_method'], 'UTF-8', 'HTML-ENTITIES');
            switch($inv_payment_method) {
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
                    $payment_method = 'CK';
                    break;
            }


            // Phiếu thu
            $receipt_data = $this->extractConcat($row['receipt_vouchers_data']);
            $receipt_html = '';
            foreach($receipt_data as $arr) {
                $receipt_html .= '<a href="index.php?module=EC_Receipt_Voucher&action=DetailView&record=' . $arr[0] . '" class="d-block" target="_blank">' . $arr[1] . '</a>';
            }

            // Hoá đơn đầu ra
            $out_invoice_data = $this->extractConcat($row['out_inv_data']);
            $out_invoice_html = '';
            foreach($out_invoice_data as $arr) {
                $out_invoice_html .= '<a href="index.php?module=EC_HoaDonBan&action=DetailView&record=' . $arr[0] . '" class="d-block" target="_blank">' . $arr[1] . '</a>';
            }

            // Hoá đơn đầu vào
            $in_invoice_data = $this->extractConcat($row['in_inv_data']);
            $in_invoice_html = '';
            foreach($in_invoice_data as $arr) {
                $in_invoice_html .= '<form action="index.php" method="post" target="_blank" class="d-block">
                    <input type="hidden" name="module" value="EC_HoaDonBan" />
                    <input type="hidden" name="action" value="inputinvoice" />
                    <input type="hidden" name="invoice_number" value="'. $arr[0] .'" />
                    <input type="hidden" name="ticket_code" value="'. $arr[2] .'" />
                    <button type="submit" class="open-input-invoice">
                        <span>'. $arr[0] .'</span>
                        <span>'. ($app_list_strings['supplier_invoice_list'][$arr[1]] ?? '') .'</span>
                    </button>
                </form>';
            }

            $html .= '<tr>
                <td class="text-center">'. (++$i) .'</td>
                <td class="text-center">'. date('d-m-Y', strtotime($row['date_entered'])) .'</td>
                <td class="text-center">
                    <a href="index.php?module=EC_Flight_Bookings&action=DetailView&record='. $row['booking_id'] .'" target="_blank">'. $row['booking_name'] .'</a>
                    <form action="index.php" method="post" target="_blank" class="mt-2">
                        <input type="hidden" name="module" value="EC_HoaDonBan">
                        <input type="hidden" name="action" value="EditView">
                        <input type="hidden" name="lienhe" value="'. $inv_account_name .'">
                        <input type="hidden" name="tencongty" value="'. $inv_company_name .'">
                        <input type="hidden" name="masothue" value="'. $inv_tax_code .'">
                        <input type="hidden" name="diachi" value="'. $inv_address .'">
                        <input type="hidden" name="email" value="'. $inv_email .'">
                        <input type="hidden" name="hinhthuctt" value="'. $payment_method .'">
                        <input type="hidden" name="sotaikhoan" value="' . $inv_bank_account .'">
                        <input type="hidden" name="booking" value="'. $row['booking_name'] .'">
                        <input type="hidden" name="booking_id" value="'. $row['booking_id'] .'">
                        <input type="submit" name="create_invoice" value="Tạo hoá đơn" class="btn btn-primary">
                    </form>
                </td>
                <td>'. $receipt_html .'</td>
                <td class="td-info-invoice">
                    '. $inv_tax_code_html .'
                    '. $inv_account_name_html .'
                    '. $inv_company_name_html .'
                    '. $inv_email_html .'
                    '. $inv_address_html .'
                </td>
                <td>'. $out_invoice_html .'</td>
                <td>'. $in_invoice_html .'</td>
            </tr>';


            // // Xuất hoá đơn công ty thì phải có tên công ty và mst công ty 
            // // Xuất hoá đơn cho cá nhân thì phải có tên khách hàng và mst hoặc địa chỉ
            // if((!empty($row['company_name']) && !empty($row['tax_code'])) || (!empty($inv_arr['iv_account_name']) && (!empty($row['tax_code']) || !empty($row['company_address'])))) {
            //     switch(mb_convert_encoding($inv_arr['iv_payment_method'], 'UTF-8', 'HTML-ENTITIES')) {
            //         case 'Tiền mặt':
            //             $payment_method = 'TM';
            //             break;
            //         case 'Chuyển khoản':
            //             $payment_method = 'CK';
            //             break;
            //         case 'Tiền mặt hoặc Chuyển khoản':
            //             $payment_method = 'TM/CK';
            //             break;
            //         default:
            //             break;
            //     }

            //     // Hoá đơn đầu ra
            //     $output_inv_html = '';
            //     $output_inv = explode('|', $row['output_inv']);
            //     for($k = 0; $k < count($output_inv); $k++) {
            //         if(!empty($output_inv[$k])) {
            //             $output_inv_inf = explode(',', $output_inv[$k]);
            //             if(!empty($output_inv_html)) $sep = ', ';
            //             else $sep = '';
            //             $output_inv_html .= $sep . '<a href="index.php?module=EC_HoaDonBan&action=DetailView&record=' . $output_inv_inf[0] . '" target="_blank">' . $output_inv_inf[1] . '</a>';
            //         }
            //     }
            //     $html .= '
            //         <tr>
            //             <td class="text-center">' . ($i + 1) . '</td>
            //             <td class="text-center">' . date('d-m-Y', strtotime($row['date_entered'])) . '</td>
            //             <td class="text-center">
            //                 <a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['id'] . '" target="_blank">' . $row['name'] . '</a>
            //                 <form action="index.php" method="post" target="_blank" class="mt-2">
            //                     <input type="hidden" name="module" value="EC_HoaDonBan">
            //                     <input type="hidden" name="action" value="EditView">
            //                     <input type="hidden" name="lienhe" value="' . $inv_arr['iv_account_name'] . '">
            //                     <input type="hidden" name="tencongty" value="' . mb_strtoupper($row['company_name'], 'UTF-8') . '">
            //                     <input type="hidden" name="masothue" value="' . $row['tax_code'] . '">
            //                     <input type="hidden" name="diachi" value="' . mb_convert_case(mb_strtolower($row['company_address'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8') . '">
            //                     <input type="hidden" name="email" value="' . $inv_arr['iv_email'] . '">
            //                     <input type="hidden" name="hinhthuctt" value="' . $payment_method . '">
            //                     <input type="hidden" name="sotaikhoan" value="' . $inv_arr['iv_bank_account'] . '">
            //                     <input type="hidden" name="booking" value="' . $row['name'] . '">
            //                     <input type="hidden" name="booking_id" value="' . $row['id'] . '">

            //                     <input type="submit" name="create_invoice" value="Tạo hoá đơn" class="btn btn-primary">
            //                 </form>
            //             </td>
            //             <td>' . $inv_arr['iv_account_name'] . '</td>
            //             <td>' . mb_strtoupper($row['company_name'], 'UTF-8') . '</td>
            //             <td>' . $row['tax_code'] . '</td>
            //             <td class="break-word">' . mb_convert_case(mb_strtolower($row['company_address'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8') . '</td>
            //             <td class="break-word">' . $inv_arr['iv_email'] . '</td>
            //             <td>' . $output_inv_html . '</td>
            //         </tr>';
            //     $i++;
            // }
        }
        $smarty->assign('OUTPUT_INV_TBL', $html);
    }

    private function extractConcat($str) {
        $result = [];
        if (!empty($str)) {
            $arr1 = explode('|', $str);
            foreach ($arr1 as $item) {
                if (!empty($item)) {
                    $result[] = explode(',', $item);
                }
            }
        }
        return $result;
    }

    private function renderItem($label, $value) {
        return '<div class="item">
            <span class="label">'. $label .': </span>
            <span class="value">'. $value .'</span>
        </div>';
    }

    private function populateCondition($smarty, $post_fields) {
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
