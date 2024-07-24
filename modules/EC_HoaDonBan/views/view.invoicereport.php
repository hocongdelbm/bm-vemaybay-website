<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewinvoicereport extends SugarView {
    function display() {
        $smarty = new Sugar_Smarty();
        $this->populateContent($smarty, $_POST);
        $smarty->display('modules/EC_HoaDonBan/tpls/invoicereport.tpl');
    }

    function populateContent($smarty, $post_fields) {
        $where = $this->populateCondition($smarty, $post_fields);

        $sql = '
            SELECT i.name, i.qty, i.cost, i.invoice_date,
                SUM(dt_curr.soluong) AS curr_soluong,
                SUM(dt_opening.soluong) AS opening_soluong
            FROM ec_input_invoices i
                LEFT JOIN (
                    SELECT o_dt.soluong, o_dt.ticket_number_id
                    FROM ec_chitiethoadon o_dt INNER JOIN ec_hoadonban o_inv
                        ON o_inv.deleted = 0 AND o_inv.id = o_dt.parent_id
                        ' . $where['sub_query_curr'] . '
                    WHERE o_dt.deleted = 0
                ) AS dt_curr ON dt_curr.ticket_number_id = i.id
                LEFT JOIN (
                    SELECT o_dt.soluong, o_dt.ticket_number_id
                    FROM ec_chitiethoadon o_dt INNER JOIN ec_hoadonban o_inv
                        ON o_inv.deleted = 0 AND o_inv.id = o_dt.parent_id
                        ' . $where['sub_query_opening'] . '
                    WHERE o_dt.deleted = 0
                ) AS dt_opening ON dt_opening.ticket_number_id = i.id
            WHERE i.deleted = 0 AND i.status = 1' . $where['main_query'] . '
            GROUP BY i.id
            ORDER BY i.invoice_date DESC, i.supplier, i.invoice_serial, i.invoice_number, i.order_by_no';

        $res = $this->bean->db->query($sql);
        $i = 0; $html = '';
        if(empty($post_fields['from_date'])) $post_fields['from_date'] = date('d-m-Y');
        while($row = $this->bean->db->fetchByAssoc($res)) {
            // Giá 1 vé
            $price_per_unit = $row['cost'] / $row['qty'];
            // Nếu ngày hoá đơn < từ ngày, hiện ở cột đầu kỳ
            $opening_qty = $opening_amt = ''; 
            if(strtotime($row['invoice_date']) < strtotime($post_fields['from_date'])) {
                $opening_qty = format_number($row['qty'] - $row['opening_soluong']);
                $opening_amt = format_number($opening_qty * $price_per_unit);
                $bought_qty = '';
                $bought_amt = '';
            } else {
                $bought_qty = format_number($row['qty']);
                $bought_amt = format_number($row['cost']);
            }

            // Cột bán ra
            $sell_qty = (empty($row['curr_soluong'])? '' : format_number($row['curr_soluong']));
            $sell_price = (empty($row['curr_soluong']) ? '' : format_number($price_per_unit * $row['curr_soluong']));

            $html .= '
                <tr>
                    <td class="text-center">' . ($i + 1) . '</td>
                    <td class="text-center">' . date('d-m-Y', strtotime($row['invoice_date'])) . '</td>
                    <td class="text-center">' . $row['name'] . '</td>
                    <td class="text-center">' . $opening_qty . '</td>
                    <td class="text-end">' . $opening_amt . '</td>
                    <td class="text-center">' . $bought_qty . '</td>
                    <td class="text-end">' . $bought_amt . '</td>
                    <td class="text-center">' . $sell_qty . '</td>
                    <td class="text-end">' . $sell_price . '</td>
                    <td class="text-center">' . format_number($row['qty'] - $row['opening_soluong'] - $row['curr_soluong']) . '</td>
                    <td class="text-center">' . format_number($price_per_unit * ($row['qty'] - $row['opening_soluong'] - $row['curr_soluong'])) . '</td>
                </tr>
            ';
            
            $i++;
        }
        $smarty->assign('INV_REPORT_TBL', $html);
    }

    function populateCondition($smarty, $post_fields) {
        global $app_list_strings;
        $sql = $sql1 = $sql2 = '';
        // Nhà cung cấp
        if (!empty($post_fields['supplier']) && !isset($post_fields['clear'])) {
            $sql .= ' AND supplier = "' . $post_fields['supplier'] . '"';
            $supplier = $post_fields['supplier'];
        } else {
            $supplier = 'Tất cả';
            $post_fields['supplier'] = '';
        }

        // Số vé
        if (!empty($post_fields['ticket_number']) && !isset($post_fields['clear'])) {
            $sql .= ' AND name = "' . TRIM($post_fields['ticket_number']) . '"';
        } else $post_fields['ticket_number'] = '';

        // Từ ngày
        if (!empty($post_fields['from_date']) && !isset($post_fields['clear'])) {
            $post_fields['from_date'] = date('Y-m-d', strtotime(trim($post_fields['from_date'])));
        } else {
            // $post_fields['from_date'] = date('Y-m-01');
            $post_fields['from_date'] = date('Y-m-d');
        }
        $sql1 .= ' AND ngayhoadon >= "' . trim($post_fields['from_date']) . '"';
        $sql2 .= ' AND ngayhoadon < "' . trim($post_fields['from_date']) . '"';

        // Đến ngày
        if (!empty($post_fields['to_date']) && !isset($post_fields['clear'])) {
            $post_fields['to_date'] = date('Y-m-d', strtotime(trim($post_fields['to_date'])));
        } else {
            // $post_fields['to_date'] = date('Y-m-t');
            $post_fields['to_date'] = date('Y-m-d');
        }
        $sql .= ' AND invoice_date <= "' . trim($post_fields['to_date']) . '"';
        $sql1 .= ' AND ngayhoadon <= "' . trim($post_fields['to_date']) . '"';

        $smarty->assign('FROM_DATE', date('d-m-Y', strtotime(trim($post_fields['from_date']))));
        $smarty->assign('TO_DATE', date('d-m-Y', strtotime(trim($post_fields['to_date']))));
        $smarty->assign('SUPPLIER', $supplier);
        $smarty->assign('SUPPLIER_OPT', get_select_options_with_id($app_list_strings['supplier_invoice_list'], $post_fields['supplier']));
        $smarty->assign('TICKET_NUMBER', trim($post_fields['ticket_number']));

        return array('main_query' => $sql, 'sub_query_curr' => $sql1, 'sub_query_opening' => $sql2);
    }
}
