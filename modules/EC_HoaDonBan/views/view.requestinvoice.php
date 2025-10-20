<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewrequestinvoice extends SugarView {
    public function display() {
        $smarty = new Sugar_Smarty();
        $this->populateContent($smarty, $_POST);
        // $this->populateContent2($smarty, $_POST);
        $smarty->display('modules/EC_HoaDonBan/tpls/requestinvoice.tpl');
    }

    public function populateContent($smarty, $post_fields) {
        global $app_list_strings;
        $whereCondition = $this->populateCondition($smarty, $post_fields);

        $sql = "SELECT rv.booking_id
                ,rv.ngaychungtu
                ,GROUP_CONCAT(DISTINCT CONCAT_WS(',', rv.id, rv.name) SEPARATOR '|') AS receipt_vouchers_data
                ,bk.name AS booking_name
                ,bk.company_name
                ,bk.tax_code
                ,bk.company_address
                ,bk.shipping_address
                ,(
                    SELECT GROUP_CONCAT(DISTINCT CONCAT_WS(',', hd.id, hd.name, ) SEPARATOR '|') 
                    FROM ec_chitiethoadon ct
                        INNER JOIN ec_hoadonban hd ON hd.id = ct.parent_id
                    WHERE ct.booking_id = rv.booking_id
                        AND hd.deleted = 0
                        AND ct.deleted = 0
                ) AS out_inv_data
                ,(
                    SELECT GROUP_CONCAT(DISTINCT CONCAT_WS(',', inv.invoice_number, inv.supplier, inv.name, inv.itinerary, inv.total) SEPARATOR '|')
                    FROM ec_input_invoices inv
                    WHERE inv.booking_id = rv.booking_id
                        AND inv.invoice_date >= rv.ngaychungtu
                        AND inv.deleted = 0
                ) AS in_inv_data
            FROM ec_receipt_voucher rv
                LEFT JOIN ec_flight_bookings bk ON bk.id = rv.booking_id
            WHERE $whereCondition
                AND rv.loai_thu IN('1', '4', '5')
                AND (
                    rv.receipt_type = 'cash'
                    OR (
                        rv.receipt_type = 'credit_transfer'
                        AND rv.tknganhang_id IN(
                            'c0b01f56-0778-de4a-da11-65f937520972',
                            'ce10c2fc-2f04-0b3f-c1f1-654eee84f8f1',
                            '1eeaf2c3-9126-0406-36aa-64cc93693bc6',
                            '98adc9fa-6e96-4fe6-45bb-6524d2c20920'
                        )
                    )
                )
                AND rv.deleted = 0
            GROUP BY rv.booking_id, rv.ngaychungtu
            ORDER BY rv.ngaychungtu DESC";

        $i = 0;
        $res = $this->bean->db->query($sql);
        $html = '';
        while($row = $this->bean->db->fetchByAssoc($res)) {
            // Thông tin xuất hóa đơn
            $inv_arr = json_decode(html_entity_decode($row['shipping_address']), true);
            // MST
            $inv_tax_code = $row['tax_code'] ?? '';
            $inv_tax_code_html = !empty($inv_tax_code) ? $this->renderItem('MST / Mã KH', $inv_tax_code) : '';
            $inv_tax_code = str_replace("CCCD", "", $inv_tax_code);
            // Tên KH
            $inv_account_name = $inv_arr['iv_account_name'] ?? '';
            $inv_account_name_html = !empty($inv_account_name) ? $this->renderItem('Tên KH', $inv_account_name) : '';
            // Tên công ty
            $inv_company_name = mb_strtoupper($row['company_name'] ?? '', 'UTF-8');
            $inv_company_name_html = !empty($inv_company_name) ? $this->renderItem('Công ty', $inv_company_name) : '';
            // Email
            $inv_email = $inv_arr['iv_email'] ?? '';
            $inv_email_html = !empty($inv_email) ? $this->renderItem('Email', $inv_email) : '';
            if(strpos($inv_email, '@') === false) $inv_email = '';
            // CCCD/Passport
            $inv_identity_number = $inv_arr['iv_identity_number'] ?? '';
            $inv_identity_number_html = !empty($inv_identity_number) ? $this->renderItem('CCCD/Passport', $inv_identity_number) : '';
            // Địa chỉ
            // $inv_address = mb_convert_case(mb_strtolower($row['company_address'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            // $inv_address = str_replace("Cccd", "CCCD", $inv_address);
            $inv_address = $row['company_address'] ?? '';
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
                    $payment_method = 'TM/CK';
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
            $in_invoice_html = '';
            $itinerary_data  = $this->extractConcat($this->getItinerary($row['booking_id']));
            $in_invoice_data = $this->extractConcat($row['in_inv_data']);
            foreach($itinerary_data as $iti) {
                $city_pair = "{$iti[0]}-{$iti[1]}";

                $list_input_invoice_html = '';
                foreach($in_invoice_data as $key => $arr) {
                    if($arr[3] == $city_pair) { // SGN-HAN
                        $list_input_invoice_html .= '<form action="index.php" method="post" target="_blank" class="d-block">
                            <input type="hidden" name="module" value="EC_HoaDonBan" />
                            <input type="hidden" name="action" value="inputinvoice" />
                            <input type="hidden" name="invoice_number" value="'. ($arr[0] ?? "") .'" />
                            <input type="hidden" name="ticket_code" value="'. ($arr[2] ?? "") .'" />
                            <button type="submit" class="open-input-invoice" title="'. format_number($arr[4] ?? 0) .'đ">
                                <span>'. ($arr[0] ?? "") .'</span>
                                <span>'. ($app_list_strings['supplier_invoice_list'][$arr[1]] ?? '') .'</span>
                            </button>
                        </form>';
                        unset($in_invoice_data[$key]);
                    }
                    else if(stripos($arr[3], $city_pair) !== false) { // SGN-HAN-SGN
                        $list_input_invoice_html .= '<form action="index.php" method="post" target="_blank" class="d-block">
                            <input type="hidden" name="module" value="EC_HoaDonBan" />
                            <input type="hidden" name="action" value="inputinvoice" />
                            <input type="hidden" name="invoice_number" value="'. ($arr[0] ?? "") .'" />
                            <input type="hidden" name="ticket_code" value="'. ($arr[2] ?? "") .'" />
                            <button type="submit" class="open-input-invoice" title="'. format_number($arr[4] ?? 0) .'đ">
                                <span>'. ($arr[0] ?? "") .'</span>
                                <span>'. ($app_list_strings['supplier_invoice_list'][$arr[1]] ?? '') .'</span>
                            </button>
                        </form>';
                    }
                }

                $in_invoice_html .= '<div style="display:block; background:#eaf5ff; border-radius:5px; box-shadow:rgba(0, 0, 0, 0.16) 0px 1px 4px; padding:3px 6px; margin-bottom:6px;">
                    <p style="font-weight:600; text-align:center;">'. $city_pair .'</p>
                    '. (!empty($list_input_invoice_html) ? $list_input_invoice_html : '<center><i style="color:red;">Chưa nạp HĐ vào</i></center>') .'
                </div>';
            }

            if(empty($inv_tax_code) && empty($inv_account_name) && empty($inv_company_name) && empty($receipt_data)) continue;

            // if(isset($row['is_output_invoice_checked']) && $row['is_output_invoice_checked'] == 1) {
            //     $out_invoice_checked_html = '<input type="checkbox" name="output_invoice_checked" class="form-check-input checkbox_output_invoice_checked" booking_id="'. $row['booking_id'] .'" selected="selected" checked="checked" />';
            // } else {
            //     $out_invoice_checked_html = '<input type="checkbox" name="output_invoice_checked" class="form-check-input checkbox_output_invoice_checked" booking_id="'. $row['booking_id'] .'" />';
            // }

            $html .= '<tr>
                <td class="text-center">'. (++$i) .'</td>
                <td class="text-center">'. date('d-m-Y', strtotime($row['ngaychungtu'])) .'</td>
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
                        <input type="hidden" name="identity_number" value="'. $inv_identity_number .'">
                        <input type="hidden" name="hinhthuctt" value="'. $payment_method .'">
                        <input type="hidden" name="sotaikhoan" value="' . $inv_bank_account .'">
                        <input type="hidden" name="booking" value="'. $row['booking_name'] .'">
                        <input type="hidden" name="booking_id" value="'. $row['booking_id'] .'">
                        <input type="submit" name="create_invoice" value="Tạo hoá đơn" class="btn btn-primary">
                    </form>
                </td>
                <td class="text-center">'. $receipt_html .'</td>
                <td class="td-info-invoice">
                    '. $inv_tax_code_html .'
                    '. $inv_account_name_html .'
                    '. $inv_company_name_html .'
                    '. $inv_email_html .'
                    '. $inv_identity_number_html .'
                    '. $inv_address_html .'
                </td>
                <td>'. $out_invoice_html .'</td>
                <td>'. $in_invoice_html .'</td>
            </tr>';
        }
        $smarty->assign('OUTPUT_INV_TBL', $html);
    }

    // public function populateContent2($smarty, $post_fields) {
    //     global $app_list_strings;
    //     $whereCondition = $this->populateCondition($smarty, $post_fields);

    //     $sql = "SELECT rv.booking_id
    //             ,rv.ngaychungtu
    //             ,GROUP_CONCAT(DISTINCT CONCAT_WS(',', rv.id, rv.name, rv.loai_thu) SEPARATOR '|') AS receipt_vouchers_data
    //             ,bk.name AS booking_name
    //             ,bk.company_name
    //             ,bk.tax_code
    //             ,bk.company_address
    //             ,bk.shipping_address
    //         FROM ec_receipt_voucher rv
    //             LEFT JOIN ec_flight_bookings bk ON bk.id = rv.booking_id
    //         WHERE $whereCondition
    //             AND rv.loai_thu IN('1', '4', '5')
    //             AND (
    //                 rv.receipt_type = 'cash'
    //                 OR (
    //                     rv.receipt_type = 'credit_transfer'
    //                     AND rv.tknganhang_id IN(
    //                         'c0b01f56-0778-de4a-da11-65f937520972',
    //                         'ce10c2fc-2f04-0b3f-c1f1-654eee84f8f1',
    //                         '1eeaf2c3-9126-0406-36aa-64cc93693bc6',
    //                         '98adc9fa-6e96-4fe6-45bb-6524d2c20920'
    //                     )
    //                 )
    //             )
    //             AND rv.deleted = 0
    //         GROUP BY rv.booking_id, rv.ngaychungtu
    //         ORDER BY rv.ngaychungtu DESC";

    //     $i = 0;
    //     $res = $this->bean->db->query($sql);
    //     $html = '';
    //     while($row = $this->bean->db->fetchByAssoc($res)) {
    //         // Thông tin xuất hóa đơn
    //         $inv_arr = json_decode(html_entity_decode($row['shipping_address']), true);
    //         // MST
    //         $inv_tax_code = $row['tax_code'] ?? '';
    //         $inv_tax_code_html = !empty($inv_tax_code) ? $this->renderItem('MST / Mã KH', $inv_tax_code) : '';
    //         $inv_tax_code = str_replace("CCCD", "", $inv_tax_code);
    //         // Tên KH
    //         $inv_account_name = $inv_arr['iv_account_name'] ?? '';
    //         $inv_account_name_html = !empty($inv_account_name) ? $this->renderItem('Tên KH', $inv_account_name) : '';
    //         // Tên công ty
    //         $inv_company_name = mb_strtoupper($row['company_name'] ?? '', 'UTF-8');
    //         $inv_company_name_html = !empty($inv_company_name) ? $this->renderItem('Công ty', $inv_company_name) : '';
    //         // Email
    //         $inv_email = $inv_arr['iv_email'] ?? '';
    //         $inv_email_html = !empty($inv_email) ? $this->renderItem('Email', $inv_email) : '';
    //         if(strpos($inv_email, '@') === false) $inv_email = '';
    //         // CCCD/Passport
    //         $inv_identity_number = $inv_arr['iv_identity_number'] ?? '';
    //         $inv_identity_number_html = !empty($inv_identity_number) ? $this->renderItem('CCCD/Passport', $inv_identity_number) : '';
    //         // Địa chỉ
    //         $inv_address = $row['company_address'] ?? '';
    //         $inv_address_html = !empty($inv_address) ? $this->renderItem('Địa chỉ', $inv_address) : '';
    //         // Thanh toán
    //         $inv_bank_account = $inv_arr['iv_bank_account'] ?? '';
    //         $inv_payment_method = mb_convert_encoding($inv_arr['iv_payment_method'], 'UTF-8', 'HTML-ENTITIES');
    //         switch($inv_payment_method) {
    //             case 'Tiền mặt':
    //                 $payment_method = 'TM';
    //                 break;
    //             case 'Chuyển khoản':
    //                 $payment_method = 'CK';
    //                 break;
    //             case 'Tiền mặt hoặc Chuyển khoản':
    //                 $payment_method = 'TM/CK';
    //                 break;
    //             default:
    //                 $payment_method = 'TM/CK';
    //                 break;
    //         }

    //         // Phiếu thu
    //         $receipt_data = $this->extractConcat($row['receipt_vouchers_data']);
    //         $out_invoice_data = [];
    //         $in_invoice_data = [];
    //         $receipt_html = '';
    //         foreach($receipt_data as $receipt) {
    //             $receiptId   = $arr[0] ?? '';
    //             $receiptName = $arr[1] ?? '';
    //             $receiptType = (string)($arr[2] ?? '');

    //             $receipt_html .= "<a href='index.php?module=EC_Receipt_Voucher&action=DetailView&record={$receiptId}' class='d-block' target='_blank'>{$receiptName}</a>";
            
    //             // Hoá đơn đầu ra
    //             $sqlOutInv = "SELECT DISTINCT hd.id, hd.name
    //                 FROM ec_chitiethoadon ct
    //                     INNER JOIN ec_hoadonban hd ON hd.id = ct.parent_id
    //                 WHERE ct.booking_id = '{$row['booking_id']}'
    //                     AND hd.deleted = 0
    //                     AND ct.deleted = 0
    //                     AND (
    //                         ct.receipt_voucher_id = '{$receiptId}'
    //                         OR ('{$receiptType}' = '1' AND (ct.mahang = 'VMB_QN' OR ct.mahang = 'VMB_QT'))
    //                         OR ('{$receiptType}' = '4' AND ct.mahang = 'PD')
    //                         OR ('{$receiptType}' = '5' AND ct.mahang = 'PHL')
    //                         OR ('{$receiptType}' = '26' AND ct.mahang = 'PMG')
    //                     )";
    //             $resOutInv = $this->bean->db->query($sqlOutInv);
    //             while($rowOutInv = $this->bean->db->fetchByAssoc($resOutInv)) {
    //                 $out_invoice_data[$rowOutInv['id']] = $rowOutInv['name'];
    //             }

    //             // Hoá đơn đầu vào
    //             $itinerary_data = $this->extractConcat($this->getItinerary($row['booking_id']));
    //             foreach($itinerary_data as $iti) {
    //                 $city_pair = "{$iti[0]}-{$iti[1]}";

    //                 $sqlInInv = "SELECT inv.invoice_number
    //                         ,inv.supplier
    //                         ,inv.name
    //                         ,inv.itinerary
    //                         ,inv.total
    //                     FROM ec_input_invoices inv
    //                     WHERE inv.booking_id = '{$row['booking_id']}'

    //                         AND inv.itinerary LIKE '%{$city_pair}%'
    //                         AND inv.invoice_date >= '{$row['ngaychungtu']}'
    //                         AND inv.deleted = 0";
    //             }
    
    //             $resInInv = $this->bean->db->query($sqlInInv);
    //             while($rowInInv = $this->bean->db->fetchByAssoc($resInInv)) {
                    
    //             }
    //         }   

    //         if(empty($inv_tax_code) && empty($inv_account_name) && empty($inv_company_name) && empty($receipt_data)) continue;


    //         // Hoá đơn đầu vào
    //         $in_invoice_html = '';
    //         $itinerary_data  = $this->extractConcat($this->getItinerary($row['booking_id']));
    //         $in_invoice_data = $this->extractConcat($row['in_inv_data']);
    //         foreach($itinerary_data as $iti) {
    //             $city_pair = "{$iti[0]}-{$iti[1]}";

    //             $list_input_invoice_html = '';
    //             foreach($in_invoice_data as $key => $arr) {
    //                 if($arr[3] == $city_pair) { // SGN-HAN
    //                     $list_input_invoice_html .= '<form action="index.php" method="post" target="_blank" class="d-block">
    //                         <input type="hidden" name="module" value="EC_HoaDonBan" />
    //                         <input type="hidden" name="action" value="inputinvoice" />
    //                         <input type="hidden" name="invoice_number" value="'. ($arr[0] ?? "") .'" />
    //                         <input type="hidden" name="ticket_code" value="'. ($arr[2] ?? "") .'" />
    //                         <button type="submit" class="open-input-invoice" title="'. format_number($arr[4] ?? 0) .'đ">
    //                             <span>'. ($arr[0] ?? "") .'</span>
    //                             <span>'. ($app_list_strings['supplier_invoice_list'][$arr[1]] ?? '') .'</span>
    //                         </button>
    //                     </form>';
    //                     unset($in_invoice_data[$key]);
    //                 }
    //                 else if(stripos($arr[3], $city_pair) !== false) { // SGN-HAN-SGN
    //                     $list_input_invoice_html .= '<form action="index.php" method="post" target="_blank" class="d-block">
    //                         <input type="hidden" name="module" value="EC_HoaDonBan" />
    //                         <input type="hidden" name="action" value="inputinvoice" />
    //                         <input type="hidden" name="invoice_number" value="'. ($arr[0] ?? "") .'" />
    //                         <input type="hidden" name="ticket_code" value="'. ($arr[2] ?? "") .'" />
    //                         <button type="submit" class="open-input-invoice" title="'. format_number($arr[4] ?? 0) .'đ">
    //                             <span>'. ($arr[0] ?? "") .'</span>
    //                             <span>'. ($app_list_strings['supplier_invoice_list'][$arr[1]] ?? '') .'</span>
    //                         </button>
    //                     </form>';
    //                 }
    //             }

    //             $in_invoice_html .= '<div style="display:block; background:#eaf5ff; border-radius:5px; box-shadow:rgba(0, 0, 0, 0.16) 0px 1px 4px; padding:3px 6px; margin-bottom:6px;">
    //                 <p style="font-weight:600; text-align:center;">'. $city_pair .'</p>
    //                 '. (!empty($list_input_invoice_html) ? $list_input_invoice_html : '<center><i style="color:red;">Chưa nạp HĐ vào</i></center>') .'
    //             </div>';
    //         }

    //         $html .= '<tr>
    //             <td class="text-center">'. (++$i) .'</td>
    //             <td class="text-center">'. date('d-m-Y', strtotime($row['ngaychungtu'])) .'</td>
    //             <td class="text-center">
    //                 <a href="index.php?module=EC_Flight_Bookings&action=DetailView&record='. $row['booking_id'] .'" target="_blank">'. $row['booking_name'] .'</a>
    //                 <form action="index.php" method="post" target="_blank" class="mt-2">
    //                     <input type="hidden" name="module" value="EC_HoaDonBan">
    //                     <input type="hidden" name="action" value="EditView">
    //                     <input type="hidden" name="lienhe" value="'. $inv_account_name .'">
    //                     <input type="hidden" name="tencongty" value="'. $inv_company_name .'">
    //                     <input type="hidden" name="masothue" value="'. $inv_tax_code .'">
    //                     <input type="hidden" name="diachi" value="'. $inv_address .'">
    //                     <input type="hidden" name="email" value="'. $inv_email .'">
    //                     <input type="hidden" name="identity_number" value="'. $inv_identity_number .'">
    //                     <input type="hidden" name="hinhthuctt" value="'. $payment_method .'">
    //                     <input type="hidden" name="sotaikhoan" value="' . $inv_bank_account .'">
    //                     <input type="hidden" name="booking" value="'. $row['booking_name'] .'">
    //                     <input type="hidden" name="booking_id" value="'. $row['booking_id'] .'">
    //                     <input type="submit" name="create_invoice" value="Tạo hoá đơn" class="btn btn-primary">
    //                 </form>
    //             </td>
    //             <td class="text-center">'. $receipt_html .'</td>
    //             <td class="td-info-invoice">
    //                 '. $inv_tax_code_html .'
    //                 '. $inv_account_name_html .'
    //                 '. $inv_company_name_html .'
    //                 '. $inv_email_html .'
    //                 '. $inv_identity_number_html .'
    //                 '. $inv_address_html .'
    //             </td>
    //             <td>'. $out_invoice_html .'</td>
    //             <td>'. $in_invoice_html .'</td>
    //         </tr>';
    //     }
    //     // $smarty->assign('OUTPUT_INV_TBL', $html);
    // }

    private function populateCondition($smarty, $post_fields) {
        $sql = '';
        // Tìm kiếm theo booking
        if (!empty($post_fields['booking']) && !isset($post_fields['clear_btn'])) {
            $post_fields['from_date'] = $post_fields['to_date'] = '';
            $sql = 'bk.name = "' . $post_fields['booking'] . '"';
        } 
        // Tìm kiếm mặc định
        else {
            // Từ ngày
            if(!empty($post_fields['from_date'])) {
                $post_fields['from_date'] = date('Y-m-d', strtotime($post_fields['from_date']));
            } else {
                $post_fields['from_date'] = date('Y-m-d');
            }
            $sql .= 'DATE_FORMAT(DATE_ADD(ngaychungtu, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . $post_fields['from_date'] . '"';
            $post_fields['from_date'] = date('d-m-Y', strtotime($post_fields['from_date']));

            // Đến ngày
            if (!empty($post_fields['to_date'])) {
                $post_fields['to_date'] = date('Y-m-d', strtotime($post_fields['to_date']));
            } else {
                $post_fields['to_date'] = date('Y-m-d');
            }
            $sql .= ' AND DATE_FORMAT(DATE_ADD(ngaychungtu, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . $post_fields['to_date'] . '"';
            $post_fields['to_date'] = date('d-m-Y', strtotime($post_fields['to_date']));

            // Booking
            $post_fields['booking'] = '';

            // Limit within 30 days
            $day = (strtotime($post_fields['to_date']) - strtotime($post_fields['from_date'])) / (60 * 60 * 24);
            if($day > 30) {
                $post_fields['from_date'] = $post_fields['to_date'] = date('d-m-Y');
                $sql = ' AND DATE_FORMAT(DATE_ADD(ngaychungtu, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"';
            }
        }

        $smarty->assign('FROM_DATE', $post_fields['from_date']);
        $smarty->assign('TO_DATE', $post_fields['to_date']);
        $smarty->assign('BOOKING', $post_fields['booking']);

        return $sql;
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

    private function getItinerary($booking_id) {
        $sql = "SELECT GROUP_CONCAT(DISTINCT CONCAT_WS(',', startPoint, endPoint) ORDER BY direction SEPARATOR '|') AS itinerary
            FROM (
                SELECT i.direction
                    ,SUBSTRING_INDEX(GROUP_CONCAT(i.departure ORDER BY i.transit_order, i.departure_date ASC SEPARATOR ','), ',', 1) AS startPoint
                    ,SUBSTRING_INDEX(GROUP_CONCAT(i.arrival ORDER BY i.transit_order, i.departure_date ASC SEPARATOR ','), ',', -1) AS endPoint
                FROM ec_booking_itineraries i
                WHERE i.booking_id = '$booking_id'
                    AND i.add_type = 0
                    AND i.deleted = 0
                GROUP BY i.direction
                ORDER BY i.direction
            ) AS listpoint";

        $res = $this->bean->db->query($sql);
        $row = $this->bean->db->fetchByAssoc($res);
        return $row['itinerary'] ?? '';
    }

    private function combineItineraries($arr) {
        if(count($arr) == 2) {
            $dep = $arr[0];
            $ret = $arr[1];

            // Comparing airline code
            if($dep[2] == $ret[2]) return ["{$dep[0]}-{$dep[1]}-{$dep[0]}"]; // SGN-HAN-SGN
            else return ["{$dep[0]}-{$dep[1]}", "{$ret[0]}-{$ret[1]}"]; // SGN-HAN HAN-SGN
        }
        else return ["{$arr[0][0]}-{$arr[0][1]}"]; // SGN-HAN
    }

    private function renderItem($label, $value) {
        return '<div class="item">
            <span class="label">'. $label .': </span>
            <span class="value">'. $value .'</span>
        </div>';
    }
}
