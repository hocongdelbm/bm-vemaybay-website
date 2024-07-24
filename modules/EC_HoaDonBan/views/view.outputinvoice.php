<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewoutputinvoice extends SugarView {
    function display() {
        $smarty = new Sugar_Smarty();
        $this->populateOutputInvoiceList($smarty, $_POST);
        $smarty->display('modules/EC_HoaDonBan/tpls/outputinvoice.tpl');
    }

    function populateOutputInvoiceList($smarty, $post_fields) {
        global $current_user, $app_list_strings;

        $where = $this->populateCondition($smarty, $post_fields);

        // $sql = '
        //     SELECT hd.id, hd.sohoadon, hd.tencongty, hd.diachi, hd.email, hd.company_unit, hd.name as invoice_name,
        //         hd.tongthanhtoan, hd.ngayhoadon, hd.masothue,
        //         SUM(ct.dongia * ct.soluong) AS tongtruocvat,
        //         SUM(ct.tienthue * ct.soluong) AS tongvat,
        //         SUM(ct.phithuho * ct.soluong) AS tongthuho
        //     FROM ec_chitiethoadon ct INNER JOIN ec_hoadonban hd
        //         ON hd.deleted = 0 AND ct.parent_id = hd.id
        //     WHERE ct.deleted = 0
        //     ' . $where . '
        //     GROUP BY hd.id
        //     -- ORDER BY IF(hd.sohoadon IS NULL OR hd.sohoadon = "", "A", hd.sohoadon) DESC, hd.ngayhoadon DESC, hd.date_entered
        //     ORDER BY hd.sohoadon DESC, hd.ngayhoadon DESC, hd.date_entered
        // ';
        $sql = '
            SELECT hd.id, hd.sohoadon, hd.tencongty, hd.diachi, hd.email, hd.company_unit, hd.name as invoice_name,
                hd.tongthanhtoan, hd.ngayhoadon, hd.masothue,
                SUM(ct.dongia * ct.soluong) AS tongtruocvat,
                SUM(ct.tienthue) AS tongvat,
                SUM(ct.phithuho * ct.soluong) AS tongthuho
            FROM ec_chitiethoadon ct INNER JOIN ec_hoadonban hd
                ON hd.deleted = 0 AND ct.parent_id = hd.id
            WHERE ct.deleted = 0
            ' . $where . '
            GROUP BY hd.id
            -- ORDER BY IF(hd.sohoadon IS NULL OR hd.sohoadon = "", "A", hd.sohoadon) DESC, hd.ngayhoadon DESC, hd.date_entered
            ORDER BY hd.sohoadon DESC, hd.ngayhoadon DESC, hd.date_entered
        ';

        // if($current_user->user_name == 'hungnh'){
        //     pr($sql);
        // }

        $res = $this->bean->db->query($sql);
        $html = ''; $i = $tong = $tongdoanhthu = $tongvat = $tongthuho = 0;
        while($row = $this->bean->db->fetchByAssoc($res)) {
            $html .= '
                <tr>
                    <td class="text-center">' . ($i + 1) . '</td>
                    <td class="text-center">' . date('d-m-Y', strtotime($row['ngayhoadon'])) . '</td>
                    <td class="text-center"><a href="index.php?module=EC_HoaDonBan&action=DetailView&record=' . $row['id'] . '" target="_blank">' . (empty($row['sohoadon']) ? '' : $row['sohoadon']) . '</a></td>
                    <td class="text-center"><a href="index.php?module=EC_HoaDonBan&action=DetailView&record=' . $row['id'] . '" target="_blank">' . $row['invoice_name'] . '</a></td>
                    <td>' . $row['tencongty'] . '</td>
                    <td>' . $row['masothue'] . '</td>
                    <td>' . $row['diachi'] . '</td>
                    <td class="break-word">' . $row['email'] . '</td>
                    <td class="text-end tongtruocvat">' . format_number($row['tongtruocvat']) . '</td>
                    <td class="text-end tongvat">' . format_number($row['tongvat']) . '</td>
                    <td class="text-end tongthuho">' . format_number($row['tongthuho']) . '</td>
                    <td class="text-end">' . format_number($row['tongthanhtoan']) . '</td>
                    <td class="text-center company_unit">' . $app_list_strings['company_unit_invoice_list'][$row['company_unit']] . '</td>
                </tr>';

            $tongdoanhthu += $row['tongtruocvat'];
            $tongvat += $row['tongvat'];
            $tongthuho += $row['tongthuho'];
            $tong += $row['tongthanhtoan'];
            $i++;
        }

        $html .= '
            <tr class="footer-tr">
                <td class="text-start" colspan="8"><b>Tổng</b></td>
                <td class="text-end"><b>' . format_number($tongdoanhthu) . '</b></td>
                <td class="text-end"><b>' . format_number($tongvat) . '</b></td>
                <td class="text-end"><b>' . format_number($tongthuho) . '</b></td>
                <td class="text-end"><b>' . format_number($tong) . '</b></td>
                <td class="text-end"></td>
            </tr>';
        $smarty->assign('OUTPUT_INV_TBL', $html);
    }

    function populateCondition($smarty, $post_fields) {
        global $app_list_strings;

        $sql = '';

        if((!empty($post_fields['invoice_name']) || !empty($post_fields['invoice_number']) || !empty($post_fields['company_unit']) || !empty($post_fields['from_date']) || !empty($post_fields['to_date'])) && !isset($post_fields['clear_btn'])) {

            if(!empty($post_fields['invoice_name'])){
                $sql .= ' AND hd.name = "' . trim($post_fields['invoice_name']) . '"';
            } else {
                $post_fields['invoice_name'] = '';
            }
            
            if(!empty($post_fields['invoice_number'])){
                $sql .= ' AND hd.sohoadon = "' . trim($post_fields['invoice_number']) . '"';
            } else {
                $post_fields['invoice_number'] = '';
            }
            
            if(!empty($post_fields['company_unit'])){
                $sql .= ' AND hd.company_unit = "' . trim($post_fields['company_unit']) . '"';
            } else {
                $post_fields['company_unit'] = '';
            }

            // Từ ngày
            if (!empty($post_fields['from_date'])) {
                $post_fields['from_date'] = date('Y-m-d', strtotime($post_fields['from_date']));
                $sql .= ' AND DATE_FORMAT(DATE_ADD(ngayhoadon, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . $post_fields['from_date'] . '"';
                $post_fields['from_date'] = date('d-m-Y', strtotime($post_fields['from_date']));
            } else {
                $post_fields['from_date'] = date('Y-m-d');
            }

            // Đến ngày
            if (!empty($post_fields['to_date'])) {
                $post_fields['to_date'] = date('Y-m-d', strtotime($post_fields['to_date']));
                $sql .= ' AND DATE_FORMAT(DATE_ADD(ngayhoadon, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . $post_fields['to_date'] . '"';
                $post_fields['to_date'] = date('d-m-Y', strtotime($post_fields['to_date']));
            } else {
                $post_fields['to_date'] = date('Y-m-d');
            }
        } 
        else if(isset($post_fields['clear_btn'])) {
            $post_fields['company_unit'] = '';
            $post_fields['invoice_number'] = '';
            $post_fields['invoice_name'] = '';

            // Từ ngày
            $post_fields['from_date'] = date('Y-m-d');
            $sql .= ' AND DATE_FORMAT(DATE_ADD(ngayhoadon, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . $post_fields['from_date'] . '"';
            $post_fields['from_date'] = date('d-m-Y');

            // Đến ngày
            $post_fields['to_date'] = date('Y-m-d');
            $sql .= ' AND DATE_FORMAT(DATE_ADD(ngayhoadon, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . $post_fields['to_date'] . '"';
            $post_fields['to_date'] = date('d-m-Y', strtotime($post_fields['to_date']));
        } 
        else {
            // Từ ngày
            if (!empty($post_fields['from_date']) && !isset($post_fields['clear_btn'])) {
                $post_fields['from_date'] = date('Y-m-d', strtotime($post_fields['from_date']));
            } else {
                $post_fields['from_date'] = date('Y-m-d');
            }
            $sql .= ' AND DATE_FORMAT(DATE_ADD(ngayhoadon, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . $post_fields['from_date'] . '"';
            $post_fields['from_date'] = date('d-m-Y', strtotime($post_fields['from_date']));

            // Đến ngày
            if (!empty($post_fields['to_date']) && !isset($post_fields['clear_btn'])) {
                $post_fields['to_date'] = date('Y-m-d', strtotime($post_fields['to_date']));
            } else {
                $post_fields['to_date'] = date('Y-m-d');
            }
            $sql .= ' AND DATE_FORMAT(DATE_ADD(ngayhoadon, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . $post_fields['to_date'] . '"';
            $post_fields['to_date'] = date('d-m-Y', strtotime($post_fields['to_date']));

            // $post_fields = $this->removeValueFromPostFields(array('invoice_name', 'invoice_number'), $post_fields);
        }


        $smarty->assign('FROM_DATE', $post_fields['from_date']);
        $smarty->assign('TO_DATE', $post_fields['to_date']);
        $smarty->assign('INVOICE_NAME', $post_fields['invoice_name']);
        $smarty->assign('INVOICE_NUMBER', $post_fields['invoice_number']);
        $smarty->assign('COMPANY_UNIT_OPTION', get_select_options_with_id($app_list_strings['company_unit_invoice_list'], $post_fields['company_unit']));

        return $sql;
    }

    function removeValueFromPostFields($field_arr, $post_fields) {
        foreach($field_arr as $field) {
            $post_fields[$field] = '';
        }

        return $post_fields;
    }
}
