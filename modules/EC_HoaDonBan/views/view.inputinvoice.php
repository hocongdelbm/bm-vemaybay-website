<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Viewinputinvoice extends SugarView
{
    var $page_row_num = 30;

    function display()
    {
        global $app_list_strings;

        // Xử lý import file excel trong tab import
        if (isset($_POST['importfile'])) {
            $this->importData($_POST);
        }
        $smarty = new Sugar_Smarty();

        // Xoá hoá đơn đã nạp
        if (isset($_POST['remove'])) {
            $this->deleteInvoiceData($_POST);
        }

        // Nếu xác nhận thì cập nhật status = 1
        if (isset($_POST['confirmed']) || isset($_POST['denied'])) {
            $this->updateInvoiceData($_POST);
        }

        // Nếu là màn hình preview thì thêm form xác nhận
        if (isset($_REQUEST['preview'])) {
            $smarty->assign('CONFIRM_FRM', $this->populateConfirmForm($this->showData($_REQUEST), $_REQUEST));
            $smarty->assign('PREVIEW', 1);
        } else {
            $this->removeNotImportedInvoice();

            $return_res = $this->calculateTotalLine($_REQUEST);
            $smarty->assign('DATA', $this->showData($_REQUEST));

            $smarty->assign('TOTAL_QTY', format_number($return_res['total_qty']));
            $smarty->assign('TOTAL_EXPORT', format_number($return_res['total_export']));
            $smarty->assign('TOTAL_LEFT', format_number($return_res['total_left']));
            $smarty->assign('TOTAL_COST', format_number($return_res['total_cost']));
            $smarty->assign('TOTAL_VAT', format_number($return_res['total_vat']));
            $smarty->assign('TOTAL_COST_VAT', format_number($return_res['total_cost_vat']));
            $smarty->assign('TOTAL_AUTHORIZED', format_number($return_res['total_authorized']));
            $smarty->assign('TOTAL', format_number($return_res['total']));

            $max_page = ceil($return_res['row_num'] / $this->page_row_num);
            if (empty($max_page)) $max_page = 1;
            $curr_page = empty($_REQUEST['page_number']) ? 1 : $_REQUEST['page_number'];
            $smarty->assign('CURRENT_PAGE', $curr_page);
            $smarty->assign('MAX_PAGE', $max_page);
            $smarty->assign('LDISABLED', ($curr_page == 1 ? 'btn btn-disabled disabled' : 'btn btn-primary'));
            $smarty->assign('RDISABLED', ($curr_page == $max_page ? 'btn btn-disabled disabled' : 'btn btn-primary'));
        }

        if (!isset($_POST['denied'])) {
            $smarty->assign('SUPPLIER', (empty($_REQUEST['supplier_name']) ? '' : ' NCC ' . $_REQUEST['supplier_name']));
            $smarty->assign('INVOICE_NUMBER', $_REQUEST['invoice_number'] ?? '');
            $smarty->assign('TICKET_CODE', $_REQUEST['ticket_code'] ?? '');
            $smarty->assign('TICKET_C', $_REQUEST['ticket_c'] ?? '');
            $smarty->assign('FROM_DATE_ACCOUNTING', $_REQUEST['from_date_accounting'] ?? '');
            $smarty->assign('TO_DATE_ACCOUNTING', $_REQUEST['to_date_accounting'] ?? '');
        } else {
            $_REQUEST['supplier'] = '';
        }

        if (!isset($_REQUEST['preview']) && !isset($_POST['denied'])) {
            $smarty->assign('FROM_DATE', $_REQUEST['from_date'] ?? '');
            $smarty->assign('TO_DATE', $_REQUEST['to_date'] ?? '');
        }

        $smarty->assign('SUPPLIER_OPTION', get_select_options_with_id($app_list_strings['supplier_invoice_list'], $_REQUEST['supplier'] ?? ''));
        $smarty->assign('COMPANY_UNIT_OPTION', get_select_options_with_id($app_list_strings['company_unit_invoice_list'], $_REQUEST['company_unit'] ?? ''));
        $smarty->assign('MISSING_BK', get_select_options_with_id(array(0 => 'Tất cả', 1 => 'Có'), $_REQUEST['missing_bk'] ?? ''));
        $smarty->assign('MISSING_QTY', get_select_options_with_id(array(0 => 'Tất cả', 1 => 'Có'), $_REQUEST['missing_qty'] ?? ''));
        $smarty->assign('STOCK_STT', get_select_options_with_id(array(0 => 'Tất cả', 1 => 'Còn', 2 => 'Hết'), $_REQUEST['stock_stt'] ?? ''));
        $smarty->assign('OVER_QTY', get_select_options_with_id(array(0 => 'Tất cả', 1 => 'Có'), $_REQUEST['over_qty'] ?? ''));
        $smarty->display('modules/EC_HoaDonBan/tpls/inputinvoice.tpl');
    }

    function removeNotImportedInvoice()
    {
        $sql1 = '
            UPDATE ec_input_invoices SET deleted = 1 
            WHERE deleted = 0 AND status = 0';
        $this->bean->db->query($sql1);
    }

    function updateInvoiceData($post_fields)
    {
        // Lưu lại thông tin giá vốn mới
        if (isset($post_fields['confirmed'])) {
            $bk_arr = array();
            for ($i = 0; $i < count($_POST['invoice_id']); $i++) {
                $sql = '
                    UPDATE ec_input_invoices 
                    SET cost = "' . unformat_number($_POST['invoice_cost_vat'][$i]) . '"
                      , vat = "' . unformat_number($_POST['invoice_vat'][$i]) . '"
                      , cost_no_vat = "' . unformat_number($_POST['invoice_cost'][$i]) . '"
                      , authorized_fee = "' . unformat_number($_POST['authorized_fee'][$i]) . '"
                      , total = cost + authorized_fee
                    WHERE deleted = 0 AND id = "' . $_POST['invoice_id'][$i] . '"';
                $this->bean->db->query($sql);

                // Cập nhật đã xuất hoá đơn đầu vào
                $input_inv = new EC_Input_Invoices;
                $input_inv->retrieve($_POST['invoice_id'][$i]);
                $bk_arr[$input_inv->booking_id]['ticket_code'][] = $input_inv->name;
                $bk_arr[$input_inv->booking_id]['invoice_number'] = $input_inv->invoice_number;
                $bk_arr[$input_inv->booking_id]['booking_name'] = $input_inv->booking;
            }

            // Cập nhật kpi và note
            foreach ($bk_arr as $bk_id => $bk_inf) {
                $this->markExportInputInvoice(
                    array(
                        'booking_id' => $bk_id,
                        'booking' => $bk_inf['booking_name'],
                        'invoice_number' => $bk_inf['invoice_number'],
                        'ticket_code' => implode(', ', $bk_inf['ticket_code']),
                    )
                );
            }
        }

        // Cập nhật tình trạng cho hoá đơn
        $upt_sql = '';
        if (isset($post_fields['confirmed'])) $upt_sql = 'status = 1';
        else if (isset($post_fields['denied'])) $upt_sql = 'deleted = 1';
        if (!empty($upt_sql)) {
            $sql = '
                UPDATE ec_input_invoices SET ' . $upt_sql . '
                WHERE deleted = 0 AND supplier = "' . $post_fields['supplier'] . '"
                    AND invoice_number = "' . $post_fields['invoice_number'] . '"
                    AND invoice_serial = "' . $post_fields['invoice_serial'] . '"
                    AND status = 0';
            $this->bean->db->query($sql);
        }
    }

    function deleteInvoiceData($post_fields)
    {
        // Xoá note đã lấy hoá đơn đầu vào của những booking trong hoá đơn
        $post_fields['rm_invoice_number'] = trim($post_fields['rm_invoice_number']);
        $post_fields['rm_invoice_serial'] = trim($post_fields['rm_invoice_serial']);
        $post_fields['rm_ticket_code'] = trim($post_fields['rm_ticket_code']);
        if (!empty($post_fields['rm_ticket_code'])) {
            $sql_ext = ' AND name = "' . $post_fields['rm_ticket_code'] . '"';
        }
        $sql1 = '
            SELECT * FROM ec_input_invoices 
            WHERE deleted = 0 AND invoice_number = "' . $post_fields['rm_invoice_number'] . '" 
                AND invoice_serial = "' . $post_fields['rm_invoice_serial'] . '"
                AND status = 1' . $sql_ext;

        $res1 = $this->bean->db->query($sql1);

        $rm_note = array();
        $rm_bk = array();
        $rm_id = array();
        while ($row1 = $this->bean->db->fetchByAssoc($res1)) {
            if (!empty($row1['booking_id'])) {
                $rm_bk[] = $row1['booking_id'];
                $rm_note[] = "Đã lấy hóa đơn đầu vào số: " . $row1['invoice_number'] . ", số vé: " . $row1['name'];
            }
            $rm_id[] = $row1['id'];
        }
        $sql2 = '
            UPDATE notes SET deleted = 1 
            WHERE description IN ("' . implode('","', $rm_note) . '") 
                AND parent_id IN ("' . implode('","', $rm_bk) . '")
                AND parent_type = "EC_Flight_Bookings"';
        $this->bean->db->query($sql2);

        // Xoá số hoá đơn đầu vào
        $sql3 = '
            UPDATE ec_input_invoices SET deleted = 1 
            WHERE id IN ("' . implode('","', $rm_id) . '")';
        $this->bean->db->query($sql3);
    }

    function populateConfirmForm($html_invoice, $request_fields)
    {
        global $app_list_strings, $current_user;

        // Hiện lỗi nếu có
        $err_note =  "";
        if (isset($request_fields['is_exist_err']) && !empty($request_fields['is_exist_err'])) {
            $err_note .= "<br>Những số vé bị trùng: " . $request_fields['is_exist_err'];
        }
        if (isset($request_fields['missing_bk_err']) && !empty($request_fields['missing_bk_err'])) {
            $err_note .= "<br>Những số vé không tìm thấy booking: " . $request_fields['missing_bk_err'];
        }
        if (isset($request_fields['missing_qty_err']) && !empty($request_fields['missing_qty_err'])) {
            $err_note .= "<br>Những số vé thiếu số lượng: " . $request_fields['missing_qty_err'];
        }
        if (isset($request_fields['other_err']) && !empty($request_fields['other_err'])) {
            $err_note .= "<br>Những số vé lỗi không nạp được: " . $request_fields['other_err'];
        }
        $html = '
            <form id="preview_frm" method="post" action="index.php">
                <div class="top_area">
                    <div class="text-center warning_text">Vui lòng kiểm tra lại thông tin hoá đơn bên dưới trước khi nạp vào hệ thống.<br>Dòng màu mận là dòng phí dịch vụ.' . $err_note . '</div>
                    <input type="hidden" name="module" value="EC_HoaDonBan">
                    <input type="hidden" name="action" value="inputinvoice">
                    <input type="hidden" name="invoice_number" value="' . $_REQUEST['invoice_number'] . '">
                    <input type="hidden" name="invoice_serial" value="' . $_REQUEST['invoice_serial'] . '">
                    <input type="hidden" name="supplier" value="' . $_REQUEST['supplier'] . '">
                    <input type="hidden" name="supplier_name" value="' . $_REQUEST['supplier_name'] . '">
                    <input type="hidden" name="from_date" value="' . $_REQUEST['invoice_date'] . '">
                    <input type="hidden" name="to_date" value="' . $_REQUEST['invoice_date'] . '">
                    <input type="submit" class="btn btn-primary" name="confirmed" value="Nạp">
                    <input type="submit" class="btn btn-danger" name="denied" value="Bỏ">
                </div>
                <br>
                <table id="data_tbl" cellspacing="0" cellpadding="0" class="table-details__booking table-input__invoices table-config">
                    <thead>
                        <th width="2%">STT</th>
                        <th width="7%">Ngày<br>hạch toán</th>
                        <th width="7%">Ngày HĐ</th>
                        <th width="7%">Số HĐ</th>
                        <th width="6%">KHHĐ</th>
                        <th width="8%">Số vé</th>
                        <th width="3%">SL</th>
                        <th width="7%">Hành trình</th>
                        <th width="7%">Giá vốn</th>
                        <th width="7%">VAT</th>
                        <th width="7%">Giá vốn (VAT)</th>
                        <th width="6%">Thu hộ</th>
                        <th width="7%">Tổng</th>  
                        <th width="7%">Booking</th>   
                        <th width="3%">NCC</th>
                        <th>Đơn vị</th>
                    </thead>
                    <tbody>
                        ' . $html_invoice . '
                    </tbody>
                    <tfoot>
                        <tr class="footer-tr">
                            <td colspan="19" class="no-border text-start">
                                <input type="button" class="btn btn-primary" id="inv_add_row" value="Thêm dòng" inv_date="' . $_REQUEST['invoice_date'] . '" inv_number="' . $_REQUEST['invoice_number'] . '" inv_seri="' . $_REQUEST['invoice_serial'] . '" inv_supplier="' . $_REQUEST['supplier'] . '">
                                <input type="hidden" id="sep_supplier_opt" value="' . get_select_options_with_id($app_list_strings['supplier_invoice_list'], $_REQUEST['supplier']) . '">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </form>';
        return $html;
    }

    function calculateTotalLine($request_fields)
    {

        global $current_user;

        $sql_search = $this->populateSearchCondition($request_fields);

        $sql = '
            SELECT 
                SUM(qty) AS total_qty,
                SUM(
                    IFNULL(
                        (
                            SELECT IFNULL(SUM(soluong), 0) FROM ec_chitiethoadon 
                            WHERE ticket_number_id = ec_input_invoices.id AND deleted = 0
                        ),
                        0
                    )
                ) AS total_export,
                SUM(cost_no_vat) AS total_cost,
                SUM(vat) AS total_vat,
                SUM(cost) AS total_cost_vat,
                SUM(authorized_fee) AS total_authorized,
                COUNT(id) AS row_num
            FROM ec_input_invoices 
            WHERE deleted = 0 ' . $sql_search;

        // if($current_user->user_name == 'hungnh'){
        // 	pr($sql);
        // };

        $res = $this->bean->db->query($sql);
        $row = $this->bean->db->fetchByAssoc($res);

        return [
            'total_qty'         => (int)$row['total_qty'],
            'total_export'      => (int)$row['total_export'],
            'total_left'        => (int)($row['total_qty'] - $row['total_export']),
            'total_cost'        => (int)$row['total_cost'],
            'total_vat'         => (int)$row['total_vat'],
            'total_cost_vat'    => (int)$row['total_cost_vat'],
            'total_authorized'  => (int)$row['total_authorized'],
            'total'             => (int)$row['total_cost_vat'] + $row['total_authorized'],
            'row_num'           => (int)$row['row_num'],
        ];
    }

    function showData($request_fields)
    {
        global $app_list_strings, $current_user;

        $sql_search = $this->populateSearchCondition($request_fields);
        $sql_limit = $this->populateLimitCondition($request_fields);

        $sql = '
            SELECT *
                , (
                    SELECT name FROM ec_flight_bookings 
                    WHERE id = ec_input_invoices.booking_id
                ) AS booking 
                , (
                    SELECT IFNULL(SUM(soluong), 0) FROM ec_chitiethoadon 
                    WHERE ticket_number_id = ec_input_invoices.id AND deleted = 0
                ) AS export
            FROM ec_input_invoices 
            WHERE deleted = 0 ' . $sql_search . '
            ORDER BY invoice_date DESC, supplier, invoice_serial, invoice_number, order_by_no
        ' . $sql_limit;


        // if($current_user->user_name == 'hungnh'){
        //     pr($sql);
        // }

        $res        = $this->bean->db->query($sql);
        $html       = '';
        $total_qty  = $total_export = $total_left = 0;
        $curr_page  = empty($_REQUEST['page_number']) ? 1 : $_REQUEST['page_number'];

        $i = ($curr_page - 1) * $this->page_row_num;
        $total_cost = $total_vat = $total_cost_vat = $total_authorized = $total = 0;
        while ($row = $this->bean->db->fetchByAssoc($res)) {
            // Nếu là dòng tính phí, thì thêm class để đánh dấu
            if ($row['is_other_fee']) {
                $row_class = 'other_fee';
            } else $row_class = '';

            // Màn hình preview trước khi nạp hoá đơn vào hệ thống
            if (isset($request_fields['preview'])) {
                $cost_input = '
                    <input type="text" class="allow_number_only text-end invoice_cost" name="invoice_cost[]" value="' . (int)$row['cost_no_vat'] . '">';
                $vat_input = '
                    <input type="text" class="allow_number_only text-end invoice_vat" name="invoice_vat[]" value="' . (int)$row['vat'] . '">';
                $cost_input_vat = '
                    <input type="text" class="allow_number_only text-end invoice_cost_vat" name="invoice_cost_vat[]" value="' . (int)$row['cost'] . '">
                    <input type="hidden" name="invoice_id[]" value="' . $row['id'] . '">';
                $author_input = '
                    <input type="text" class="allow_number_only text-end authorized_fee" name="authorized_fee[]" value="' . (int)$row['authorized_fee'] . '">';
                $available = '';
                $error_minus = '';
                $tt_colspan = 7;
            } else {
                $cost_input = format_number($row['cost_no_vat']);
                $vat_input = format_number($row['vat']);
                $cost_input_vat = format_number($row['cost']);
                $author_input = format_number($row['authorized_fee']);

                if (($row['qty'] - $row['export']) > 0) {
                    $available = 'available';
                } else $available = 'outofstock';
                // nếu tồn âm, đánh dấu nguyên hàng
                if (($row['qty'] - $row['export']) < 0) {
                    $error_minus = 'err_minus';
                } else $error_minus = '';
            }

            if (strtotime($row['accounting_date']) != false) {
                $accounting_date = date('d-m-Y', strtotime($row['accounting_date']));
            } else {
                $accounting_date = "";
            }

            $html .= '<tr class="' . $row_class . ' ' . $error_minus . '">';
            $html .= '<td class="text-center sep_order">' . ($i + 1) . '</td>';
            $html .= '<td class="text-center accounting_date">' . $accounting_date . '</td>';
            $html .= '<td class="text-center invoice_date">' . date('d-m-Y', strtotime($row['invoice_date'])) . '</td>';
            $html .= '<td class="text-center invoice_number">' . $row['invoice_number'] . '</td>';
            $html .= '<td class="text-center invoice_serial">' . $row['invoice_serial'] . '</td>';
            $html .= '<td class="text-center ' . $available . '">' . $row['name'] . '</td>';
            // $html .= '<td class="text-center ' . $available . '">' . $row['ticket_code'] . '</td>';
            $html .= '<td class="text-center">' . format_number($row['qty']) . '</td>';

            // Cột sl xuất và sl còn lại chỉ hiện khi không ở trong màn hình preview
            if (!isset($request_fields['preview'])) {
                $html .= '<td class="text-center">' . format_number($row['export']) . '</td>';
                $html .= '<td class="text-center ' . $available . '">' . format_number($row['qty'] - $row['export']) . '</td>';
            }

            $html .= '<td class="text-center itinerary">' . $row['itinerary']  . '</td>';
            $html .= '<td class="text-end cost_input">' . $cost_input . '</td>';
            $html .= '<td class="text-end vat_input">' . $vat_input . '</td>';
            $html .= '<td class="text-end cost_input_vat">' . $cost_input_vat . '</td>';
            $html .= '<td class="text-end author_input">' . $author_input . '</td>';
            $html .= '<td class="text-end ln_total allow_number_only">' . format_number($row['cost'] + $row['authorized_fee']) . '</td>';
            $html .= '<td class="text-center"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '" target="_blank">' . $row['booking'] . '</a></td>';
            $html .= '<td class="text-center supplier_infor">' . $app_list_strings['supplier_invoice_list'][$row['supplier']] . '</td>';
            $html .= '<td class="text-center company_unit_infor">' . $app_list_strings['company_unit_invoice_list'][$row['company_unit']] . '</td>';

            // if (isset($request_fields['preview'])) {
            //     $html .= '<td></td>';
            // }

            $html .= '</tr>';
            $i++;

            $total_qty          += (int)$row['qty'];
            $total_export       += (int)$row['export'];
            $total_left         += (int)($row['qty'] - $row['export']);
            $total_cost         += (int) $row['cost_no_vat'];
            $total_vat          += (int)$row['vat'];
            $total_cost_vat     += (int)$row['cost'];
            $total_authorized   += (int)$row['authorized_fee'];
            $total              += (int)($row['cost'] + $row['authorized_fee']);
        }

        if (isset($request_fields['preview'])) {
            $html .= '<tr id="last_row" class="footer-tr">';
            $html .= '<td class="text-end" colspan="6"><b>Tổng</b></td>';
            $html .= '<td class="text-center" id="total_qty">' . format_number($total_qty) . '</td>';
            $html .= '<td class="text-end"></td>';
            $html .= '<td id="total_cost" class="text-end allow_number_only">' . format_number($total_cost) . '</td>';
            $html .= '<td id="total_vat" class="text-end allow_number_only">' . format_number($total_vat) . '</td>';
            $html .= '<td id="total_cost_vat" class="text-end allow_number_only">' . format_number($total_cost_vat) . '</td>';
            $html .= '<td id="total_authorized_fee" class="text-end allow_number_only">' . format_number($total_authorized) . '</td>';
            $html .= '<td id="total" class="text-end allow_number_only">' . format_number($total) . '</td>';
            $html .= '
            <td colspan="3">
                <input type="hidden" id="inv_row_count" value="' . ($i + 1) . '">
            </td>';
            $html .= '</tr>';
        }
        return $html;
    }

    function populateSearchCondition($request_fields)
    {
        $sql_search = '';
        if (!isset($request_fields['denied'])) {
            if (!isset($request_fields['preview'])) {
                // Từ ngày
                if (isset($request_fields['from_date']) && !empty($request_fields['from_date'])) {
                    $from_date = $request_fields['from_date'];
                    $sql_search .= ' AND invoice_date >= "' . date('Y-m-d', strtotime($from_date)) . '"';
                }

                // Đến ngày
                if (isset($request_fields['to_date']) && !empty($request_fields['to_date'])) {
                    $to_date = $request_fields['to_date'];
                    $sql_search .= ' AND invoice_date <= "' . date('Y-m-d', strtotime($to_date)) . '"';
                }

                // Ngày hạch toán từ ngày
                if (isset($request_fields['from_date_accounting']) && !empty($request_fields['from_date_accounting'])) {
                    $from_date_accounting = $request_fields['from_date_accounting'];
                    $sql_search .= ' AND accounting_date >= "' . date('Y-m-d', strtotime($from_date_accounting)) . '"';
                }

                // Ngày hạch toán đến ngày
                if (isset($request_fields['to_date_accounting']) && !empty($request_fields['to_date_accounting'])) {
                    $to_date_accounting = $request_fields['to_date_accounting'];
                    $sql_search .= ' AND accounting_date <= "' . date('Y-m-d', strtotime($to_date_accounting)) . '"';
                }

                // Số vé thiếu sl
                if (isset($request_fields['missing_qty']) && $request_fields['missing_qty'] == 1) {
                    $sql_search .= ' AND (qty IS NULL OR qty = "" OR qty = 0)';
                } else if ($request_fields['missing_bk'] == 2) {
                    $sql_search .= ' AND qty > 0';
                }

                // Số vé bị xuất dư sl
                if (isset($request_fields['over_qty']) && $request_fields['over_qty'] == 1) {
                    $sql_search .= ' AND (qty - (
                        SELECT IFNULL(SUM(soluong), 0) FROM ec_chitiethoadon 
                        WHERE ticket_number_id = ec_input_invoices.id AND deleted = 0
                    )) < 0';
                }

                // Tình trạng tồn
                if (isset($request_fields['stock_stt']) && $request_fields['stock_stt'] == 1) {
                    // Còn tồn
                    $sql_search .= ' AND (qty - (
                        SELECT IFNULL(SUM(soluong), 0) FROM ec_chitiethoadon 
                        WHERE ticket_number_id = ec_input_invoices.id AND deleted = 0
                    )) > 0';
                } else if (isset($request_fields['stock_stt']) && $request_fields['stock_stt'] == 2) {
                    // Hết tồn
                    $sql_search .= ' AND (qty - (
                        SELECT IFNULL(SUM(soluong), 0) FROM ec_chitiethoadon 
                        WHERE ticket_number_id = ec_input_invoices.id AND deleted = 0
                    )) = 0';
                }
            }

            // Nhà cung cấp
            if (isset($request_fields['supplier']) && !empty($request_fields['supplier'])) {
                if ($request_fields['supplier'] == 'EMPTY') {
                    $sql_search .= ' AND (supplier IS NULL OR supplier = "")';
                } else {
                    $sql_search .= ' AND supplier = "' . $request_fields['supplier'] . '"';
                }
            }

            // Đơn vị công ty
            if (isset($request_fields['company_unit']) && !empty($request_fields['company_unit'])) {
                if ($request_fields['company_unit'] == 'EMPTY') {
                    $sql_search .= ' AND (company_unit IS NULL OR company_unit = "")';
                } else {
                    $sql_search .= ' AND company_unit = "' . $request_fields['company_unit'] . '"';
                }
            }

            // Số hoá đơn
            if (isset($request_fields['invoice_number']) && !empty($request_fields['invoice_number'])) {
                $sql_search .= ' AND invoice_number = "' . $request_fields['invoice_number'] . '"';
            }

            // Ký hiệu hoá đơn
            if (isset($request_fields['invoice_serial']) && !empty($request_fields['invoice_serial'])) {
                $sql_search .= ' AND invoice_serial = "' . $request_fields['invoice_serial'] . '"';
            }

            // Số vé
            if (isset($request_fields['ticket_code']) && !empty($request_fields['ticket_code'])) {
                $sql_search .= ' AND name = "' . $request_fields['ticket_code'] . '"';
            }

            // Code vé
            if (isset($request_fields['ticket_c']) && !empty($request_fields['ticket_c'])) {
                $sql_search .= ' AND ticket_code = "' . $request_fields['ticket_c'] . '"';
            }

            // Tình trạng
            if (isset($request_fields['preview'])) {
                $sql_search .= ' AND status = "0"';
            } else $sql_search .= ' AND status = "1"';

            // Số vé không có booking
            if (isset($request_fields['missing_bk']) && $request_fields['missing_bk'] == 1) {
                $sql_search .= ' AND (booking_id IS NULL OR booking_id = "")';
            } else if ($request_fields['missing_bk'] == 2) {
                $sql_search .= ' AND (booking_id IS NOT NULL OR booking_id <> "")';
            }
        } else {
            $sql_search .= ' AND status = "1"';
        }

        return $sql_search;
    }

    function populateLimitCondition($request_fields)
    {
        if (!isset($request_fields['preview'])) {
            if (empty($request_fields['page_number']))
                $request_fields['page_number'] = 1;
            $from_value = ((int)$request_fields['page_number'] - 1) * $this->page_row_num;
            $to_value = $this->page_row_num;
            $sql_limit = 'LIMIT ' . $from_value . ', ' . $to_value;
        } else $sql_limit = '';
        return $sql_limit;
    }

    function importData($post_fields)
    {
        global $current_user;

        if ($_FILES['from_file']['name'] != '') {
            $path_parts = pathinfo($_FILES["from_file"]["name"]);
            $file_type = $path_parts['extension'];

            // Lưu trữ file tải lên vào đường dẫn cache/upload/inputinvoices/
            $fileName = $this->sys_uploads('cache/upload/inputinvoices/', 'from_file', $file_type);
            $data_arr = ['ticket_code', 'pass_qty', 'itinerary', 'ticket_price'];

            // Nếu là VJA thì thêm cột thu hộ, cột vat
            if ($post_fields['supplier'] == 'VJA') {
                $data_arr[]             = 'authorized_collection';
                $post_fields['authorized_collection'] = 'N';
                $data_arr[]             = 'vat';
                $post_fields['vat']     = 'M';
            }

            $invoice_number     = trim($post_fields['invoice_number']);
            // $invoice_date       = str_replace(array('.', '/'), '-', $post_fields['invoice_date']);
            $invoice_date       = $post_fields['invoice_date'];
            $invoice_serial     = trim($post_fields['invoice_serial']);
            $supplier           = $post_fields['supplier'];
            $company_unit       = $post_fields['company_unit'];
            // $accounting_date    = str_replace(array('.', '/'), '-', $post_fields['accounting_date']);
            $accounting_date    = $post_fields['accounting_date'];

            if (!empty($fileName)) {
                if (in_array($file_type, ['xls', 'xlsx'])) {
                    $objReader      = new Spreadsheet();
                    $objReader      = IOFactory::load("cache/upload/inputinvoices/$fileName");
                    $sheet          = $objReader->getActiveSheet();
                    $array_data     = [];

                    $k = 0;
                    for ($i = 1; $i <= $sheet->getHighestRow(); $i++) {

                        // Nếu cột A là stt thì mới import, không thì bỏ qua
                        // Các trường hợp đặc biệt:
                        // 1. Nếu là BBA thì cột A là stt, cột B phải là số vé, không phải số, để phân biệt với dòng đánh số cột
                        // 2. Nếu là VNA thì cột B phải là số lượng thì lưu
                        $cell_A = $sheet->getCell('A' . $i)->getValue();
                        $cell_B = $sheet->getCell('B' . $i)->getValue();

                        if ($supplier == 'VNA') {
                            $cell_compare = $this->getDataFromFile($post_fields['pass_qty'], $i, $sheet);
                        } else $cell_compare = str_replace(array("\n", "\r"), "", $cell_A);

                        if (is_numeric(str_replace(' ', '', $cell_compare))) {
                            if ($supplier == 'BBA' && is_numeric(str_replace(' ', '', $cell_B))) {
                                continue;
                            }
                            foreach ($data_arr as $col) {
                                $array_data[$k][$col] = $this->getDataFromFile($post_fields[$col], $i, $sheet);
                            }
                            $array_data[$k]['invoice_number']   = $invoice_number;
                            $array_data[$k]['invoice_date']     = $invoice_date;
                            $array_data[$k]['invoice_serial']   = $invoice_serial;
                            $array_data[$k]['supplier']         = $supplier;
                            $array_data[$k]['company_unit']     = $company_unit;
                            $array_data[$k]['accounting_date']  = $accounting_date;
                            $k++;
                        }
                    }

                    $array_data = $this->filterData($array_data, $supplier);

                    // Kiểm tra data import => trả về lỗi nếu có
                    $err = $this->insertData($array_data);
                    header("Location: index.php?module=EC_HoaDonBan&action=inputinvoice&preview&supplier=" . $post_fields['supplier'] . "&supplier_name=" . $post_fields['supplier_name'] . "&invoice_number=" . trim($post_fields['invoice_number']) . "&invoice_serial=" . trim($post_fields['invoice_serial']) . "&invoice_date=" . trim($post_fields['invoice_date']) . "&status=0" . $err);
                    exit;
                }
                else {
                    header("Location: index.php?module=EC_HoaDonBan&action=Error&error_string=" . urlencode("Đuôi file excel phải là .xls"));
                    exit;
                }

                // else if ($file_type == 'csv') {
                //     $array_data = $this->getArrFromCSV('cache/upload/timesheets/' . $fileName);

                //     $this->insertDetails($array_data);
                // } else {
                //     echo "Định dạng file không được hỗ trợ!!!";
                //     exit;
                // }
            }
            else {
                header("Location: index.php?module=EC_HoaDonBan&action=Error&error_string=" . urlencode("Vui lòng đặt lại tên file excel."));
                exit;
            }
        }
        else {
            header("Location: index.php?module=EC_HoaDonBan&action=Error&error_string=" . urlencode("Chưa có thông tin file import. Vui lòng import lại"));
            exit;
        }
    }

    function getDataFromFile($col_letter, $ln, $sheet)
    {
        $data = '';
        if (!empty($col_letter)) {
            /**
             * Use getValue() for return formula
             * Use getCalculatedValue() for return value
             */

            if (strpos($col_letter, ',') !== false) {
                $cols = explode(',', $col_letter);
                foreach ($cols as $c) {
                    $cell = $sheet->getCell($c . $ln);
                    // $cell_val = $cell->getValue();
                    $cell_val = $cell->getCalculatedValue();
                    if (!empty($cell_val)) {
                        $data = trim($cell_val);
                    }
                }
            } else {
                $cell = $sheet->getCell($col_letter . $ln);
                // $data = trim($cell->getValue());
                $data = trim($cell->getCalculatedValue());
            }
        }

        $data = str_replace("'", '', $data);
        return $data;
    }

    function insertData($data)
    {
        global $current_user, $db;
        $is_exist_err       = "";
        $missing_bk_err     = "";
        $missing_qty_err    = "";
        $other_err          = "";
        $err                = "";

        for ($i = 0; $i < count($data); $i++) {
            if (!empty($data[$i]['itinerary']) && !$data[$i]['is_intern']) {
                $sql_iti = ' AND itinerary = "' . $data[$i]['itinerary'] . '"';
            } else $sql_iti = '';

            // Kiểm tra hoá đơn đã được import chưa
            $sql = '
                SELECT IF(COUNT(id) > 0, 1, 0) 
                FROM ec_input_invoices 
                WHERE deleted = 0 AND name = "' . $data[$i]['ticket_code'] . '"
                AND supplier = "' . $data[$i]['supplier'] . '" AND status = 1
                AND invoice_number = "' . $data[$i]['invoice_number'] . '"
                AND invoice_serial = "' . $data[$i]['invoice_serial'] . '"' . $sql_iti;


            $is_exist = $db->getOne($sql);
            if (!$is_exist && !empty($data[$i]['booking_id']) && $data[$i]['pass_qty'] > 0) {

                $input_iv = new EC_Input_Invoices;
                $input_iv->name                 = $data[$i]['ticket_code'];
                $input_iv->assigned_user_id     = $current_user->id;
                $input_iv->qty                  = $data[$i]['pass_qty'];
                $input_iv->invoice_number       = $data[$i]['invoice_number'];
                $input_iv->invoice_date         = $data[$i]['invoice_date'];
                $input_iv->invoice_serial       = $data[$i]['invoice_serial'];
                $input_iv->supplier             = $data[$i]['supplier'];
                $input_iv->company_unit         = $data[$i]['company_unit'];
                $input_iv->accounting_date      = $data[$i]['accounting_date'];
                $input_iv->itinerary            = $data[$i]['itinerary'];

                if (isset($data[$i]['is_other_fee'])) {
                    $input_iv->is_other_fee = $data[$i]['is_other_fee'];
                }
                $input_iv->status       = 0;
                $input_iv->booking_id   = $data[$i]['booking_id'];
                $input_iv->order_by_no  = $i;

                if ($data[$i]['supplier'] != 'VJA') {
                    // Tính giá vốn chưa VAT
                    // Nếu là vé quốc tế, VAT = 0
                    if ($data[$i]['is_intern']) {
                        $input_iv->cost_no_vat      = $input_iv->cost;
                        $input_iv->vat              = 0;
                        $input_iv->authorized_fee   = 0;
                    }
                    // Vé nội địa
                    else {
                        // Cột giá vốn trước vat là tổng tiền của các phí chịu vat
                        // Cột phí thu hộ là tổng tiền của các phí không chịu vat
                        $spe_airline = ['VNA', 'HNH', 'TH'];
                        if (in_array($input_iv->supplier, $spe_airline)) {
                            // Tính phí thu hộ
                            // NCC có phí thu hộ = phí admin + phí sân bay
                            $input_iv->authorized_fee   = $data[$i]['admin_fee'] + $data[$i]['authorized_collection'];
                            // Tính giá vốn (VAT)
                            $input_iv->cost             = ($data[$i]['ticket_price'] * $data[$i]['pass_qty']) + $data[$i]['vat'] + $data[$i]['luggage_outbound'] + $data[$i]['luggage_inbound'];
                            // Tính giá vốn chưa vat
                            $input_iv->cost_no_vat      = $input_iv->cost - $data[$i]['vat'] - $data[$i]['vat_luggage_outbound'] - $data[$i]['vat_luggage_inbound'];
                        } else {
                            // Phí thu hộ = phí sân bay
                            $input_iv->authorized_fee = $data[$i]['authorized_collection'];
                            // Tính giá vốn (VAT)
                            $input_iv->cost = ($data[$i]['ticket_price'] * $data[$i]['pass_qty']) + $data[$i]['vat'] + $data[$i]['admin_fee'] + $data[$i]['luggage_outbound'] + $data[$i]['luggage_inbound'];
                            // Tính giá vốn chưa vat
                            $input_iv->cost_no_vat = $input_iv->cost - $data[$i]['vat'] - $data[$i]['vat_admin'] - $data[$i]['vat_luggage_outbound'] - $data[$i]['vat_luggage_inbound'];
                        }
                        // Tính vat
                        $input_iv->vat = $input_iv->cost - $input_iv->cost_no_vat;
                    }
                }
                else {
                    $input_iv->authorized_fee   = $data[$i]['authorized_collection'];
                    $input_iv->vat              = $data[$i]['vat'];
                    $input_iv->cost_no_vat      = $data[$i]['ticket_price'];
                    $input_iv->cost             = $input_iv->cost_no_vat + $input_iv->vat;
                }

                // Cột code vé nếu có thì lưu
                if (!empty($data[$i]['ticket_c'])) {
                    $input_iv->ticket_code = $data[$i]['ticket_c'];
                }

                $input_iv->save2();
            }
            else if ($is_exist) {
                if (!empty($is_exist_err)) $is_exist_err .= ",";
                $is_exist_err .= $data[$i]['ticket_code'];
            }
            else if (empty($data[$i]['booking_id'])) {
                if (!empty($missing_bk_err)) $missing_bk_err .= ",";
                $missing_bk_err .= $data[$i]['ticket_code'];
            }
            else if ($data[$i]['pass_qty'] == 0) {
                if (!empty($missing_qty_err)) $missing_qty_err .= ",";
                $missing_qty_err .= $data[$i]['ticket_code'];
            }
            else {
                if (!empty($other_err)) $other_err = ",";
                $other_err .= $data[$i]['ticket_code'];
            }
        }

        if (!empty($is_exist_err)) $err .= "&is_exist_err=" . $is_exist_err;
        if (!empty($missing_bk_err)) $err .= "&missing_bk_err=" . $missing_bk_err;
        if (!empty($missing_qty_err)) $err .= "&missing_qty_err=" . $missing_qty_err;
        if (!empty($other_err)) $err .= "&other_err=" . $other_err;

        return $err;
    }

    // Làm tròn xuống cho các giá tiền lẻ dưới 1000 đồng
    function roundNumber($amt, $is_ceil = 0)
    {
        $r_amt = $amt / 1000;
        if (is_float($r_amt)) {
            if ($is_ceil) {
                return (ceil($r_amt) * 1000);
            } else {
                return (floor($r_amt) * 1000);
            }
        } else return $amt;
    }

    // Cập nhật đã xuất hoá đơn đầu vào
    function markExportInputInvoice($import_data)
    {
        global $db, $current_user;
        $booking_id = $import_data['booking_id'];
        if (!empty($booking_id)) {
            // Cập nhật đã lấy hoá đơn đầu vào
            $db->query('
                UPDATE ec_flight_bookings 
                SET is_invoice_input_export = 1 
                WHERE id = "' . $booking_id . '"');
            $date_created = date_sub(date_create(date('Y-m-d H:i:s')), date_interval_create_from_date_string("7 hours"));
            $db->query('
                INSERT INTO ec_flight_bookings_audit(id, parent_id, date_created, created_by, field_name, data_type, before_value_string, after_value_string) VALUES (uuid(), "' . $booking_id . '", "' . date('Y-m-d H:i:s', strtotime(date_format($date_created, "Y-m-d"))) . '", "' . $current_user->id . '", "is_invoice_input_export", "boolean", 0, 1)
            ');

            // Cập nhật kpi
            myRemoveWorkingProcess('EC_Flight_Bookings', $booking_id, 'invoice_input_issued');
            myCreateWorkingProcess('EC_Flight_Bookings', $booking_id, $import_data['booking_name'], 'Import từ hoá đơn của hãng', $current_user->id, 'invoice_input_issued');

            // Cập nhật note 
            // Xoá từng note số hoá đơn riêng nếu có
            $ticket_code_arr = explode(',', $import_data['ticket_code']);
            for ($i = 0; $i < count($ticket_code_arr); $i++) {
                $db->query('DELETE FROM notes WHERE parent_id = "' . $booking_id . '" AND description = "Đã lấy hóa đơn đầu vào số: ' . $ticket_code_arr[$i] . ', số vé: ' . trim($import_data['ticket_code']) . '"');
            }

            // Xoá note số hoá đơn gộp chung
            $db->query('DELETE FROM notes WHERE parent_id = "' . $booking_id . '" AND description = "Đã lấy hóa đơn đầu vào số: ' . $import_data['invoice_number'] . '; số vé: ' . $import_data['ticket_code'] . '"');
            $note = new Note;
            $note->name = 'Import HD đầu vào';
            $note->parent_type = 'EC_Flight_Bookings';
            $note->parent_id = $booking_id;
            $note->description = 'Đã lấy hóa đơn đầu vào số: ' . $import_data['invoice_number'] . '; số vé: ' . $import_data['ticket_code'];
            $note->save();
        }
    }

    // Lọc lại dữ liệu
    function filterData($data, $supplier)
    {
        global $current_user;

        for ($i = 0; $i < count($data); $i++) {
            if (!empty($data[$i]['ticket_code'])) {
                if ($supplier == 'VJA') {
                    // Phân tích hành trình, để lấy nơi đi nơi đến
                    $iti = explode('-', $data[$i]['itinerary']);
                    $data[$i]['departure'][] = $this->changeAirportCode($iti[0]);
                    $data[$i]['arrival'][] = $this->changeAirportCode($iti[1]);
                    $data[$i]['is_intern'] = $this->checkIsInternationalTicket($this->changeAirportCode($iti[0]), $this->changeAirportCode($iti[1]));
                    $data[$i]['ticket_price'] = (int)$data[$i]['ticket_price'];
                    $data[$i]['vat'] = (int)$data[$i]['vat'];
                    $data[$i]['authorized_collection'] = (int)$data[$i]['authorized_collection'];
                    if (empty($data[$i]['pass_qty'])) $data[$i]['pass_qty'] = 1;
                } else if ($supplier == 'BBA') {
                    // Lọc cột code vé, số vé
                    $data[$i]['ticket_code'] = preg_replace('/\s+/', ' ', $data[$i]['ticket_code']);
                    $ticket_code_arr = explode(' ', $data[$i]['ticket_code']);
                    // Code vé
                    $data[$i]['ticket_c'] = $ticket_code_arr[0];
                    // Số vé
                    $data[$i]['ticket_code'] = $ticket_code_arr[2];
                } else if ($supplier == 'VNA') {
                    // Lọc cột số vé, hành trình 
                    $data[$i]['ticket_code'] = preg_replace('/\s+/', ' ', trim($data[$i]['ticket_code']));
                    $ticket_code_arr = explode(' ', $data[$i]['ticket_code']);
                    if (count($ticket_code_arr) > 1) { // nhập từ file convert
                        $data[$i]['ticket_code'] = $ticket_code_arr[0];
                        $data[$i]['itinerary'] = $ticket_code_arr[1];
                    }

                    // Lấy thông tin hành trình
                    $itinerary_arr = explode('VN', $data[$i]['itinerary']);
                    if (count($itinerary_arr) > 2) {
                        $data[$i]['itinerary'] = $itinerary_arr[0] . '-' . $itinerary_arr[1] . '-' .  $itinerary_arr[0];
                        $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[0]);
                        $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[1]);
                        $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[0]);
                        $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[1]);
                    } else {
                        $data[$i]['itinerary'] = $itinerary_arr[0] . '-' . $itinerary_arr[1];
                        $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[0]);
                        $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[1]);
                    }
                } else if ($supplier == 'VTA') {
                    // Phân tích hành trình, để lấy nơi đi nơi đến
                    $iti = explode('-', $data[$i]['itinerary']);
                    $data[$i]['departure'][] = $this->changeAirportCode($iti[0]);
                    $data[$i]['arrival'][] = $this->changeAirportCode($iti[1]);
                } else if ($supplier == 'HNH') {
                    // Kiểm tra hành trình quốc tế hay nội địa
                    // Hành trình nội địa sẽ phân cách bằng chữ VN
                    if (strpos($data[$i]['itinerary'], 'VN') !== false) {
                        // Hành trình lượt đi với lượt về khác nhau
                        if (strpos($data[$i]['itinerary'], '//') !== false) {
                            $data[$i]['itinerary'] = str_replace('//', 'VN', $data[$i]['itinerary']);
                        }
                        // Kiểm tra có khứ hồi hay không
                        $itinerary_arr = explode('VN', $data[$i]['itinerary']);

                        // Hành trình lượt đi lượt về khác nhau
                        if (count($itinerary_arr) > 3) {
                            $data[$i]['itinerary'] = implode('-', $itinerary_arr);
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[0]);
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[2]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[1]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[3]);
                        }
                        // Khứ hồi
                        else if (count($itinerary_arr) > 2) {
                            $data[$i]['itinerary'] = $itinerary_arr[0] . '-' . $itinerary_arr[1] . '-' .  $itinerary_arr[0];
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[0]);
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[1]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[0]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[1]);
                        }
                        // 1 chiều
                        else {
                            $data[$i]['itinerary'] = $itinerary_arr[0] . '-' . $itinerary_arr[1];
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[0]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[1]);
                        }
                    }
                    // Hành trình quốc tế
                    else {
                        $data[$i]['is_intern'] = 1;
                    }
                } else if ($supplier == 'TH') {
                    // Lọc số vé
                    $data[$i]['ticket_code'] = preg_replace('/\s+/', ' ', $data[$i]['ticket_code']);
                    $ticket_code_arr = explode(' ', $data[$i]['ticket_code']);
                    if (count($ticket_code_arr) > 1) {
                        $data[$i]['ticket_code'] = $ticket_code_arr[0];
                    } else {
                        $data[$i]['ticket_code'] = trim($data[$i]['ticket_code']);
                    }

                    // Lọc hành trình,
                    $data[$i]['itinerary'] = preg_replace('/\s+/', ' ', $data[$i]['itinerary']);
                    // Kiểm tra có khứ hồi hay không
                    $itinerary_arr = explode(' ', $data[$i]['itinerary']);
                    if (count($itinerary_arr) > 1) {
                        // Có khứ hồi
                        if (count($itinerary_arr) > 4) {
                            $data[$i]['itinerary'] = $itinerary_arr[2] . '-' . $itinerary_arr[3] . '-' .  $itinerary_arr[2];
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[2]);
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[3]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[2]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[3]);
                        }
                        // 1 chiều
                        else {
                            $data[$i]['itinerary'] = $itinerary_arr[2] . '-' . $itinerary_arr[3];
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[2]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[3]);
                        }
                    } else {
                        $itinerary_arr = explode('-', $data[$i]['itinerary']);
                        // Có khứ hồi
                        if (count($itinerary_arr) > 4) {
                            $data[$i]['itinerary'] = $itinerary_arr[0] . '-' . $itinerary_arr[1] . '-' .  $itinerary_arr[0];
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[0]);
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[1]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[0]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[1]);
                        } else if (count($itinerary_arr) > 1) { // 1 chiều
                            $data[$i]['itinerary'] = $itinerary_arr[0] . '-' . $itinerary_arr[1];
                            $data[$i]['departure'][] = $this->changeAirportCode($itinerary_arr[0]);
                            $data[$i]['arrival'][] = $this->changeAirportCode($itinerary_arr[1]);
                        }
                    }
                }

                // Lọc cột đơn giá, 
                // Đầu tiên, bỏ các dấu phân cách
                // Sau đó, giá < 1000 -> giá * 1000 
                $data[$i]['ticket_price'] = $this->changeAmountFormat($data[$i]['ticket_price']);

                // Tìm thông tin giá vé và booking dựa theo số vé trong booking
                $data[$i] = $this->populateBookingPriceDetail($data[$i], $supplier);
            }
        }
        return $data;
    }

    function changeAirportCode($airport_code)
    {
        if ($airport_code == 'CXR') {
            $airport_code = 'NHA';
        }
        return $airport_code;
    }

    function checkIsInternationalTicket($departure, $arrival)
    {
        global $app_list_strings;
        if (!in_array($departure, array_keys($app_list_strings['domestic_airport_list'])) || !in_array($arrival, array_keys($app_list_strings['domestic_airport_list']))) {
            return true;
        }
        return false;
    }

    function populateBookingPriceDetail($data_arr, $supplier)
    {
        global $db;

        if ($supplier == 'VJA') {
            $sql = '
                SELECT p.booking_id
                FROM ec_booking_passengers p 
                    INNER JOIN ec_flight_bookings b ON b.id = p.booking_id AND b.deleted = 0
                        AND b.booking_status = 8
                WHERE p.deleted = 0 
                    AND (
                        TRIM(eticket_outbound) LIKE "' . $data_arr['ticket_code'] . '%"
                        OR TRIM(eticket_inbound) LIKE "' . $data_arr['ticket_code'] . '%"
                    ) LIMIT 1';
            $res = $this->bean->db->query($sql);
            $row = $this->bean->db->fetchByAssoc($res);
            $data_arr['booking_id'] = $row['booking_id'];
        }
        elseif($supplier == 'PNA') {
            $sql = "SELECT p.booking_id
                FROM ec_booking_passengers p 
                    INNER JOIN ec_flight_bookings b ON b.id = p.booking_id
                        AND b.deleted = 0
                        AND b.booking_status = 8
                WHERE p.deleted = 0 
                    AND (
                        TRIM(p.eticket_outbound) = '". $data_arr['ticket_code'] ."'
                        OR TRIM(p.eticket_inbound) = '". $data_arr['ticket_code'] ."'
                    )
                LIMIT 1";
            $res = $this->bean->db->query($sql);
            $row = $this->bean->db->fetchByAssoc($res);
            $data_arr['booking_id'] = $row['booking_id'];
        }
        else {
            // Tính sl khách
            // Tuỳ hãng trong hoá đơn sẽ gộp số vé hay không
            // Nghĩa là 1 số vé dùng cho nhiều khách
            if ($supplier == 'VTA') {
                $qty = 'p.qty';
            } else {
                $qty = 1;
            }

            // Nếu có đơn giá trong hoá đơn
            if (!empty($data_arr['ticket_price'])) {
                // Hãng BBA hoá đơn không có hành trình
                // Vé quốc tế không có chiều đi / về, tiền là tính tổng cả 2
                if ($supplier == 'BBA' || $data_arr['is_intern']) {
                    $sql_iti = '';
                } else {
                    $sql_iti = '
                        AND d.direction IN (
                            SELECT direction FROM ec_booking_itineraries i 
                            WHERE i.deleted = 0
                            AND IF(i.departure IN("NHA", "CXR"), "NHA", i.departure) IN ("' . implode('","', $data_arr['departure']) . '")
                            AND IF(i.arrival IN("NHA", "CXR"), "NHA", i.arrival) IN ("' . implode('","', $data_arr['arrival']) . '")
                            AND i.booking_id = d.booking_id
                        )';
                }
            }

            // Lấy thông tin giá cơ bản trong booking
            // Hãng VAT, đơn giá = đơn giá * sl khách có cùng số vé
            // Các hãng còn lại, đơn giá = đơn giá của 1 khách
            if ($supplier == 'VTA') {
                $sql_uprice = ', SUM(d.unit_price * p.qty) AS unit_price, SUM(p.qty) AS pass_qty';
            } else {
                $sql_uprice = ', d.unit_price';
            }

            $sql1 = '
                SELECT 
                    SUM(d.tax_and_fee * ' . $qty . ') AS tax,
                    SUM(d.airport_fee * ' . $qty . ') AS authorized_fee,
                    SUM(d.admin_fee * ' . $qty . ') AS admin_fee,
                    SUM(d.admin_fee_no_vat * ' . $qty . ') AS admin_fee_no_vat,
                    SUM(d.vat_admin * ' . $qty . ') AS vat_admin,
                    SUM(d.total_bought_price / d.quantity) AS bought_price
                    ' . $sql_uprice . ',
                    d.booking_id, p.add_type, d.direction,
                    SUM(IF(d.direction = 0, p.luggage_outbound, 0)) AS luggage_outbound,
                    SUM(IF(d.direction = 0, p.luggage_outbound_no_vat, 0)) AS luggage_outbound_no_vat,
                    SUM(IF(d.direction = 0, p.vat_luggage_outbound, 0)) AS vat_luggage_outbound,
                    SUM(IF(d.direction = 1, p.luggage_inbound, 0)) AS luggage_inbound,
                    SUM(IF(d.direction = 1, p.luggage_inbound_no_vat, 0)) AS luggage_inbound_no_vat,
                    SUM(IF(d.direction = 1, p.vat_luggage_inbound, 0)) AS vat_luggage_inbound,
                    (SELECT booking_status FROM ec_flight_bookings WHERE id = d.booking_id) AS bk_status,
                    p.outbound, p.inbound, d.passenger_type
                FROM ec_booking_details d
                INNER JOIN (
                    SELECT booking_id, COUNT(id) AS qty, type, add_type,
                        SUM(IF(TRIM(eticket_outbound) LIKE "' . $data_arr['ticket_code'] . '%", luggage_purchase, 0)) AS luggage_outbound,
                        SUM(IF(TRIM(eticket_outbound) LIKE "' . $data_arr['ticket_code'] . '%", luggage_purchase_no_vat, 0)) AS luggage_outbound_no_vat,
                        SUM(IF(TRIM(eticket_outbound) LIKE "' . $data_arr['ticket_code'] . '%", vat_luggage_purchase, 0)) AS vat_luggage_outbound,
                        SUM(IF(TRIM(eticket_inbound) LIKE "' . $data_arr['ticket_code'] . '%", luggage_purchase_inbound, 0)) AS luggage_inbound,
                        SUM(IF(TRIM(eticket_inbound) LIKE "' . $data_arr['ticket_code'] . '%", luggage_purchase_inbound_no_vat, 0)) AS luggage_inbound_no_vat,
                        SUM(IF(TRIM(eticket_inbound) LIKE "' . $data_arr['ticket_code'] . '%", vat_luggage_purchase_inbound, 0)) AS vat_luggage_inbound,
                        IF(TRIM(eticket_outbound) LIKE "' . $data_arr['ticket_code'] . '%", 1, 0) AS outbound,
                        IF(TRIM(eticket_inbound) LIKE "' . $data_arr['ticket_code'] . '%", 1, 0) AS inbound
                    FROM ec_booking_passengers
                    WHERE deleted = 0
                        AND (
                            TRIM(eticket_outbound) LIKE "' . $data_arr['ticket_code'] . '%"
                            OR TRIM(eticket_inbound) LIKE "' . $data_arr['ticket_code'] . '%"
                        )
                    GROUP BY booking_id, type
                ) AS p ON p.booking_id = d.booking_id AND p.type = d.passenger_type
                WHERE d.deleted = 0
                ' . $sql_iti . '
                GROUP BY d.booking_id, d.direction, d.unit_price, d.tax_and_fee, d.service_fee, d.admin_fee, d.airport_fee
                HAVING bk_status = 8
            ';

            $res1 = $db->query($sql1);
            // $rowCount1 = $db->getRowCount($res1);
            $rowCount1 = $db->countRows($res1);
            $result = array();
            if ($rowCount1 > 0) {
                while ($row1 = $db->fetchByAssoc($res1)) {
                    // Vé quốc tế, chỉ có tổng tiền, nên tính là lượt đi
                    if ($data_arr['is_intern']) {
                        $direction_str = 0;
                    }
                    // Vé nội địa
                    else {
                        if ($supplier == 'BBA') {
                            if ($row1['outbound'] && $row1['inbound']) {
                                $direction_str = $row1['direction'];
                            } else if ($row1['outbound'] || $row1['inbound']) {
                                $direction_str = 0;
                            } else {
                                break;
                            }
                        } else {
                            if (count($data_arr['departure']) > 1) {
                                $direction_str = $row1['direction'];
                            } else {
                                $direction_str = 0;
                            }
                        }
                    }
                    $result[$direction_str][] = $row1;
                }

                // Nếu là số vé khứ hồi -> đơn giá sẽ phải cộng giá cơ bản lượt đi và lượt về
                // Để tìm ra giá cơ bản khớp với đơn giá trong hoá đơn
                if (!empty($data_arr['ticket_price'])) {
                    // 2 chiều
                    if (count($result) > 1) {
                        for ($d = 0; $d < count($result[0]); $d++) {
                            for ($a = 0; $a < count($result[1]); $a++) {
                                $data_arr['booking_id'] = $result[0][$d]['booking_id'];
                                $unit_price = $result[0][$d]['unit_price'] + $result[1][$a]['unit_price'];
                                // Đơn giá BBA trong hoá đơn đã bao gồm phí admin chưa VAT
                                if ($supplier == 'BBA') {
                                    if (($unit_price + $this->roundNumber($result[0][$d]['admin_fee_no_vat'], 1) + $this->roundNumber($result[1][$a]['admin_fee_no_vat'], 1)) == $data_arr['ticket_price'] || abs($unit_price + $this->roundNumber($result[0][$d]['admin_fee_no_vat'], 1) + $this->roundNumber($result[1][$a]['admin_fee_no_vat'], 1) - $data_arr['ticket_price']) <= 1000 || $result[0][$d]['passenger_type'] == 2 || $result[1][$a]['passenger_type'] == 2) {
                                        $data_arr['vat'] = $result[0][$d]['tax'] + $result[1][$a]['tax'];
                                        $data_arr['admin_fee'] = $result[0][$d]['admin_fee'] + $result[1][$a]['admin_fee'];
                                        $data_arr['vat_admin'] = $this->roundNumber($result[0][$d]['vat_admin'] + $result[1][$a]['vat_admin']);
                                        $data_arr['authorized_collection'] = $result[0][$d]['authorized_fee'] + $result[1][$a]['authorized_fee'];
                                        $data_arr['luggage_outbound'] = $result[0][$d]['luggage_outbound'] + $result[1][$a]['luggage_outbound'];
                                        $data_arr['vat_luggage_outbound'] = $result[0][$d]['vat_luggage_outbound'] + $result[1][$a]['vat_luggage_outbound'];
                                        $data_arr['luggage_inbound'] = $result[0][$d]['luggage_inbound'] + $result[1][$a]['luggage_inbound'];
                                        $data_arr['vat_luggage_inbound'] = $result[0][$d]['vat_luggage_inbound'] + $result[1][$a]['vat_luggage_inbound'];
                                        $data_arr['ticket_price'] = ($result[0][$d]['unit_price'] + $result[1][$a]['unit_price']);
                                        // khách là trẻ sơ sinh, giá cơ bản = 0
                                        if ($result[0][$d]['passenger_type'] == 2 || $result[1][$a]['passenger_type'] == 2) {
                                            $data_arr['is_infant'] = 1;
                                        }
                                        break;
                                    }
                                } else if ($supplier == 'HNH') {
                                    $unit_price_compare = round(($result[0][$d]['bought_price'] - $result[0][$d]['authorized_fee'] - $result[0][$d]['admin_fee'] + $result[1][$a]['bought_price'] - $result[1][$a]['authorized_fee'] - $result[1][$a]['admin_fee']) / 1.08);
                                    if ($unit_price_compare == $data_arr['ticket_price']) {
                                        $data_arr['vat'] = round($data_arr['ticket_price'] * 0.08);
                                        $data_arr['admin_fee'] = $result[0][$d]['admin_fee'] + $result[1][$a]['admin_fee'];
                                        $data_arr['vat_admin'] = $result[0][$d]['vat_admin'] + $result[1][$a]['vat_admin'];
                                        $data_arr['authorized_collection'] = $result[0][$d]['authorized_fee'] + $result[1][$a]['authorized_fee'];
                                        $data_arr['luggage_outbound'] = $result[0][$d]['luggage_outbound'] + $result[1][$a]['luggage_outbound'];
                                        $data_arr['vat_luggage_outbound'] = $result[0][$d]['vat_luggage_outbound'] + $result[1][$a]['vat_luggage_outbound'];
                                        $data_arr['luggage_inbound'] = $result[0][$d]['luggage_inbound'] + $result[1][$a]['luggage_inbound'];
                                        $data_arr['vat_luggage_inbound'] = $result[0][$d]['vat_luggage_inbound'] + $result[1][$a]['vat_luggage_inbound'];
                                        break;
                                    }
                                } else {
                                    if (($result[0][$d]['unit_price'] + $result[1][$a]['unit_price']) == $data_arr['ticket_price']) {
                                        $data_arr['vat'] = $result[0][$d]['tax'] + $result[1][$a]['tax'];
                                        $data_arr['admin_fee'] = $result[0][$d]['admin_fee'] + $result[1][$a]['admin_fee'];
                                        $data_arr['vat_admin'] = $result[0][$d]['vat_admin'] + $result[1][$a]['vat_admin'];
                                        $data_arr['authorized_collection'] = $result[0][$d]['authorized_fee'] + $result[1][$a]['authorized_fee'];
                                        $data_arr['luggage_outbound'] = $result[0][$d]['luggage_outbound'] + $result[1][$a]['luggage_outbound'];
                                        $data_arr['vat_luggage_outbound'] = $result[0][$d]['vat_luggage_outbound'] + $result[1][$a]['vat_luggage_outbound'];
                                        $data_arr['luggage_inbound'] = $result[0][$d]['luggage_inbound'] + $result[1][$a]['luggage_inbound'];
                                        $data_arr['vat_luggage_inbound'] = $result[0][$d]['vat_luggage_inbound'] + $result[1][$a]['vat_luggage_inbound'];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    // 1 chiều
                    else {
                        for ($d = 0; $d < count($result[0]); $d++) {
                            $data_arr['booking_id'] = $result[0][$d]['booking_id'];
                            $unit_price = $result[0][$d]['unit_price'];

                            // Vé quốc tế
                            if ($data_arr['is_intern']) {
                                // NCC HNH, vé quốc tế thì lấy hãng bay
                                // để lấy thông tin hành trình
                                if ($supplier == 'HNH') {
                                    $booking = new EC_Flight_Bookings;
                                    $booking->retrieve($result[0][$d]['booking_id']);
                                    // Lấy hãng bay
                                    $airline = $booking->airline;
                                    $airline_arr = explode('-', $airline);
                                    $airline = $airline_arr[0];
                                    $iti_arr = explode($airline, $data_arr['itinerary']);
                                    // Có quá cảnh
                                    // Nếu là khứ hồi thì hành trình dep-arr-dep
                                    // 1 chiều thì hành trình dep-arr
                                    if (count($airline_arr) > 1) {
                                        $data_arr['itinerary'] = $iti_arr[0] . '-' . $iti_arr[2];
                                        if ($booking->flight_type == 0) {
                                            $data_arr['itinerary'] .= '-' . $iti_arr[0];
                                        }
                                    } else {
                                        $data_arr['itinerary'] = $iti_arr[0] . '-' . $iti_arr[1];
                                        if ($booking->flight_type == 0) {
                                            $data_arr['itinerary'] .= '-' . $iti_arr[0];
                                        }
                                    }
                                }

                                // Nếu là khứ hồi thì chỉnh lại iti và là hãng VJA
                                if ($row1['bk_direction'] == 0 && $supplier == 'VJA') {
                                    $data_arr['itinerary'] .= '-' . $data_arr['departure'][0];
                                }
                                $data_arr['ticket_price'] = $result[0][$d]['unit_price'];
                                $data_arr['vat'] = $result[0][$d]['tax'];
                                $data_arr['admin_fee'] = $result[0][$d]['admin_fee'];
                                $data_arr['authorized_collection'] = $result[0][$d]['authorized_fee'];
                                $data_arr['luggage_outbound'] = $result[0][$d]['luggage_outbound'];
                                $data_arr['luggage_inbound'] = $result[0][$d]['luggage_inbound'];
                            } else {
                                if ($supplier == 'BBA') {
                                    if (($unit_price + $this->roundNumber($result[0][$d]['admin_fee_no_vat'], 1)) == $data_arr['ticket_price'] || abs($unit_price + $this->roundNumber($result[0][$d]['admin_fee_no_vat'], 1) + $this->roundNumber($result[1][$d]['admin_fee_no_vat'], 1) - $data_arr['ticket_price']) <= 1000 || $result[0][$d]['passenger_type'] == 2) {
                                        // Ngày 29-12-2022, chị Trang nhờ lấy giá và VAT BBA theo hoá đơn không lấy theo bk
                                        $data_arr['vat'] = round($data_arr['ticket_price'] * 0.08);
                                        $data_arr['admin_fee'] = $result[0][$d]['admin_fee'];
                                        $data_arr['vat_admin'] = $result[0][$d]['vat_admin'];
                                        $data_arr['authorized_collection'] = $result[0][$d]['authorized_fee'];
                                        $data_arr['luggage_outbound'] = $result[0][$d]['luggage_outbound'];
                                        $data_arr['vat_luggage_outbound'] = $result[0][$d]['vat_luggage_outbound'];
                                        $data_arr['luggage_inbound'] = $result[0][$d]['luggage_inbound'];
                                        $data_arr['vat_purchase_inbound'] = $result[0][$d]['vat_purchase_inbound'];
                                        // $data_arr['ticket_price'] = $result[0][$d]['unit_price'];

                                        // Khách là trẻ sơ sinh, giá cơ bản = 0
                                        if ($result[0][$d]['passenger_type'] == 2) {
                                            $data_arr['is_infant'] = 1;
                                        }
                                        break;
                                    }
                                } else if ($supplier == 'HNH') {
                                    $unit_price_compare = round(($result[0][$d]['bought_price'] - $result[0][$d]['authorized_fee'] - $result[0][$d]['admin_fee']) / 1.08);
                                    if ($unit_price_compare == $data_arr['ticket_price']) {
                                        $data_arr['vat'] = round($data_arr['ticket_price'] * 0.08);
                                        $data_arr['admin_fee'] = $result[0][$d]['admin_fee'];
                                        $data_arr['vat_admin'] = $result[0][$d]['vat_admin'];
                                        $data_arr['authorized_collection'] = $result[0][$d]['authorized_fee'];
                                        $data_arr['luggage_outbound'] = $result[0][$d]['luggage_outbound'];
                                        $data_arr['vat_luggage_outbound'] = $result[0][$d]['vat_luggage_outbound'];
                                        $data_arr['luggage_inbound'] = $result[0][$d]['luggage_inbound'];
                                        $data_arr['vat_luggage_inbound'] = $result[0][$d]['vat_luggage_inbound'];
                                        break;
                                    }
                                } else {
                                    if ($result[0][$d]['unit_price'] == $data_arr['ticket_price']) {
                                        $data_arr['vat'] = $result[0][$d]['tax'];
                                        $data_arr['admin_fee'] = $result[0][$d]['admin_fee'];
                                        $data_arr['vat_admin'] = $result[0][$d]['vat_admin'];
                                        $data_arr['authorized_collection'] = $result[0][$d]['authorized_fee'];
                                        $data_arr['luggage_outbound'] = $result[0][$d]['luggage_outbound'];
                                        $data_arr['vat_luggage_outbound'] = $result[0][$d]['vat_luggage_outbound'];
                                        $data_arr['luggage_inbound'] = $result[0][$d]['luggage_inbound'];
                                        $data_arr['vat_luggage_inbound'] = $result[0][$d]['vat_luggage_inbound'];
                                        // Hãng VTA, sl trong hoá đơn không phải sl khách cần lấy lại từ booking
                                        // Và giá vé là giá vé của nhiều khách
                                        if ($supplier == 'VTA') {
                                            $data_arr['ticket_price'] = $data_arr['ticket_price'] / $result[0][$d]['pass_qty'];
                                            $data_arr['pass_qty'] = $result[0][$d]['pass_qty'];
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Nếu không có đơn giá, hoặc dòng chi tiết loại khác -> đánh dấu lại để nhập tay
            // Thường là phí hành lý / đổi tên / đổi ngày bay / đổi hành trình,
            if (empty($data_arr['ticket_price']) || !empty($row['add_type'])) {
                $data_arr['pass_qty'] = 1;
                $data_arr['is_other_fee'] = 1;
            }
        }

        return $data_arr;
    }

    function changeAmountFormat($amount)
    {
        // Lọc cột giá tiền, 
        // Đầu tiên, bỏ các dấu phân cách
        // Sau đó, giá < 1000 -> giá * 1000 
        if ((int)$amount > 0) {
            $amount = str_replace(array('.', ','), '', $amount);
        }
        if ((int)$amount < 1000) {
            $amount *= 1000;
        }
        return (int)$amount;
    }

    /*
	| Target: upload file text len he thong
	| Input: ten thu muc, ten file, loai file
	| Output: chuoi neu upload thanh cong
	*/
    function sys_uploads($folder, $file, $type = 'xls')
    {
        $size           = 50000000;
        $upload_file    = "";

        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            return $upload_file;
        }

        if (!isset($_FILES[$file]["error"]) || $_FILES[$file]["error"] != 0) {
            return $upload_file;
        }
        if ($_FILES[$file]["size"] > $size) {
            return $upload_file;
        }

        $temp       = preg_split('/[\/\\\\]+/', $_FILES[$file]["name"]);
        $filename   = $temp[count($temp) - 1];

        if (!preg_match('/\.(' . $type . ')$/i', $filename)) {
            return $upload_file;
        }

        $filename       = str_replace("%20", "", $filename);
        $filename       = str_replace(" ", "", $filename);
        $upload_file    = date('YmdHi') . '_HDDauVao_' . $filename;

        if (move_uploaded_file($_FILES[$file]["tmp_name"], $folder . $upload_file)) {
            return $upload_file;
        } else {
            return $upload_file;
            // 202307180331_HDDauVao_479684.xls
        }
    }
}