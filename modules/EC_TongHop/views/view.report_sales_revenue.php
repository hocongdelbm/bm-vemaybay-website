<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewreport_sales_revenue extends SugarView
{
    private $_is_allow_recheck = true;

    function __construct()
    {
        global $current_user;
        if (
            is_admin($current_user) || (ACLController::checkAccess('EC_Payment_Voucher', 'edit', true))
        ) {
            $this->_is_allow_recheck = true;
        }
    }

    function display()
    {
        global $current_user;

        // if ($current_user->user_name != 'hungnh') {
        //     echo '<p class="alert alert-danger">Hệ thống đang bảo trì. Vui lòng quay lại sau!</p>';
        //     exit;
        // }

        if (ACLController::checkAccess('EC_Flight_Bookings', 'edit', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_TongHop/tpls/report_sales_revenue.tpl');
        } else {
            header("Location: index.php?module=EC_TongHop&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smartyobj)
    {
        global $current_user, $app_list_strings;

        // Từ ngày
        if (!empty($_REQUEST['from_date']) && strtotime($_REQUEST['from_date']) !== false) {
            $post_from_date = $_REQUEST['from_date'];
        } else {
            $post_from_date = date('d-m-Y');
        }

        // Đến ngày
        if (!empty($_REQUEST['to_date']) && strtotime($_REQUEST['to_date']) !== false) {
            $post_to_date = $_REQUEST['to_date'];
        } else {
            $post_to_date = date('d-m-Y');
        }

        switch (ceil(date('n') / 3)) {
            case 1:
                $quater_fromdate = '01-01-' . date('Y');
                $quater_todate = '31-03-' . date('Y');
                break;
            case 2:
                $quater_fromdate = '01-04-' . date('Y');
                $quater_todate = '30-06-' . date('Y');
                break;
            case 3:
                $quater_fromdate = '01-07-' . date('Y');
                $quater_todate = '30-09-' . date('Y');
                break;
            case 4:
                $quater_fromdate = '01-10-' . date('Y');
                $quater_todate = '31-12-' . date('Y');
                break;
            default:
                break;
        }

        $arr_date = array(
            '<option value="" fromdate="" todate="">---Trống---</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'this_month' ? 'selected' : '') . ' value="this_month" fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'previous_month' ? 'selected' : '') . ' value="previous_month" fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'quater_this_month' ? 'selected' : '') . ' value="quater_this_month" fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'quater_previous_month' ? 'selected' : '') . ' value="quater_previous_month" fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'this_year' ? 'selected' : '') . ' value="this_year" fromdate="' . date('01-01-Y') . '" todate="' . date('31-12-Y') . '">Năm nay</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'previous_year' ? 'selected' : '') . ' value="previous_year" fromdate="' . date('01-01-Y', strtotime('-1 year')) . '" todate="' . date('31-12-Y', strtotime('-1 year')) . '">Năm trước</option>',
        );
        $smartyobj->assign('DATE_OPTION', implode('', $arr_date));

        // RADIO
        $smartyobj->assign('YESTERDAY_FROMDATE', date('d-m-Y', strtotime('-1 day')));
        $smartyobj->assign('YESTERDAY_TODATE', date('d-m-Y', strtotime('-1 day')));
        $smartyobj->assign('DAYBEFORE_FROMDATE', date('d-m-Y', strtotime('-2 days')));
        $smartyobj->assign('DAYBEFORE_TODATE', date('d-m-Y', strtotime('-2 days')));
        $smartyobj->assign('CURRENT_WEEK_FROMDATE', date('d-m-Y', strtotime('monday this week')));
        $smartyobj->assign('CURRENT_WEEK_TODATE', date('d-m-Y', strtotime('sunday this week')));
        $smartyobj->assign('CURRENT_FROMDATE', date('d-m-Y', strtotime('first day of this month')));
        $smartyobj->assign('CURRENT_TODATE', date('d-m-Y', strtotime('last day of this month')));
        $smartyobj->assign('PREVIOUS_FROMDATE', date('d-m-Y', strtotime('first day of last month')));
        $smartyobj->assign('PREVIOUS_TODATE', date('t-m-Y', strtotime('last day of last month')));
        $smartyobj->assign('PREVIOUS_WEEK_FROMDATE', date('d-m-Y', strtotime('monday previous week')));
        $smartyobj->assign('PREVIOUS_WEEK_TODATE', date('d-m-Y', strtotime('sunday previous week')));

        switch (ceil(date('n') / 3)) {
            case 1:
                $quater_fromdate = '01-01-' . date('Y');
                $quater_todate = '31-03-' . date('Y');
                break;
            case 2:
                $quater_fromdate = '01-04-' . date('Y');
                $quater_todate = '30-06-' . date('Y');
                break;
            case 3:
                $quater_fromdate = '01-07-' . date('Y');
                $quater_todate = '30-09-' . date('Y');
                break;
            case 4:
                $quater_fromdate = '01-10-' . date('Y');
                $quater_todate = '31-12-' . date('Y');
                break;
            default:
                break;
        }
        $smartyobj->assign('CURRENT_QUARTER_FROMDATE', $quater_fromdate);
        $smartyobj->assign('CURRENT_QUARTER_TODATE', $quater_todate);

        // Check if report over 31 days
        $days_diff = (abs(strtotime($post_to_date) - strtotime($post_from_date)) / 60 / 60 / 24) + 1;
        if (!is_admin($current_user) && $days_diff > 31) {
            echo '<p class="error">Vui lòng chọn trong khoảng thời gian 31 ngày</p>';
            exit;
        }

        // routing
        $data = '';
        $payment_stt = isset($_POST['payment_stt']) ? $_POST['payment_stt'] : 0;
        $customer_source = $_POST['customer_source'] ?? '';

        $data = $this->bookingQuery($post_from_date, $post_to_date, ['payment_stt' => $payment_stt, 'customer_source' => $customer_source]);

        $smartyobj->assign('DATA', (is_array($data) ? $data['html'] : $data));
        $smartyobj->assign('DATA_TOTAL', $data['html_total']);
        $smartyobj->assign('FROM_DATE_VALUE', $post_from_date);
        $smartyobj->assign('TO_DATE_VALUE', $post_to_date);

        $smartyobj->assign('ALLOWED_EXPORT', ACLController::checkAccess('EC_Payment_Voucher', 'edit', true));
        $smartyobj->assign('IS_ALLOW_RECHECK', $this->_is_allow_recheck);

        // tìm nhanh khoảng thời gian
        $smartyobj->assign('TODAY', date('d-m-Y'));
        $smartyobj->assign('YESTERDAY', date('d-m-Y', strtotime('-1 day')));
        $smartyobj->assign('THISWEEK_FROMDATE', date('d-m-Y', strtotime('monday this week')));
        $smartyobj->assign('THISWEEK_TODATE', date('d-m-Y', strtotime('sunday this week')));
        $smartyobj->assign('PREVWEEK_FROMDATE', date('d-m-Y', strtotime('monday previous week')));
        $smartyobj->assign('PREVWEEK_TODATE', date('d-m-Y', strtotime('sunday previous week')));
        $smartyobj->assign('THISMONTH_FROMDATE', date('d-m-Y', strtotime('first day of this month')));
        $smartyobj->assign('THISMONTH_TODATE', date('d-m-Y', strtotime('last day of this month')));
        $smartyobj->assign('PREVMONTH_FROMDATE', date('d-m-Y', strtotime('first day of last month')));
        $smartyobj->assign('PREVMONTH_TODATE', date('d-m-Y', strtotime('last day of last month')));

        // Tình trạng thu
        $payment_status = array(
            '0' => 'Tất cả',
            '1' => 'Chưa thu',
            '2' => 'Chưa thu đủ',
            '3' => 'Booking telesale',
            '4' => 'Booking ctv',
        );

        $smartyobj->assign('PAYMENT_STT', get_select_options_with_id($payment_status, (int)$payment_stt));
        
        $customer_source_opts = array_merge(['' => 'Tất cả'], $app_list_strings['booking_customer_source_list']);
        $smartyobj->assign('CUSTOMER_SOURCE_OPTS', get_select_options_with_id($customer_source_opts, $customer_source));
    }

    // Thống kê doanh thu theo booking
    function bookingQuery($post_fdate, $post_tdate, $condition_arr)
    {
        $user_list = get_user_array(true, '', '', true);

        $i      = 0;
        $total_quantity      = 0;
        $subtotal_amount     = 0;
        $total_bought_price  = 0;
        $total_profit        = 0;
        $total_receipt       = 0;
        $total_points_amount = 0;

        $data_revenue = calculateRevenueOfDate(date('Y-m-d', strtotime($post_fdate)), date('Y-m-d', strtotime($post_tdate)), $condition_arr);

        $html = '';
        if (!empty($data_revenue) && $data_revenue['count'] > 0) {
            foreach ($data_revenue['details'] as $row) {
                $profit_amount = $row['subtotal_amount'] - $row['total_bought_price'];

                if ($row['total_bought_price'] > $row['subtotal_amount'] && $row['parent_type']) {
                    $bg_class = 'error1';
                } elseif ($row['receipt_amount'] < $row['subtotal_amount'] && $row['parent_type'] == 'EC_Flight_Bookings') {
                    $bg_class = 'error2';
                } elseif ($row['subtotal_amount'] < $row['receipt_amount']) {
                    $bg_class = 'sales_smaller_receipt';
                } elseif ($row['total_bought_price'] == $row['subtotal_amount']) {
                    $bg_class = 'equal';
                } else $bg_class = 'normal';

                // thời điểm khách thanh toán
                $paid_time_timestp = strtotime($row['paid_time']);
                if ($paid_time_timestp !== false) {
                    $paid_time = date('d-m-Y', $paid_time_timestp) . '<br>' . date('H:i', $paid_time_timestp);
                } else $paid_time = '';

                $html .= '<tr class="' . $bg_class . '" >';

                if ($this->_is_allow_recheck) {
                    $html .= '<td class="text-center booking-ids hide-mobile">';
                    if ($row['parent_type'] == 'EC_Flight_Bookings') {
                        $html .= '<span style="display: none;" class="loading"></span>';
                        $html .= '<input type="checkbox" class="booking-ids vertical-middle" data-triptype="' . $row['flight_type'] . '" data-mobile="' . $row['contact_mobile'] . '" id="' . $row['parent_id'] . '" value="' . $row['parent_id'] . '">';
                    }
                    $html .= '</td>';
                }

                $html .= '
                        <td class="text-center hide-mobile">' . ($i + 1) . '</td>
                        <td class="text-center"><a target="_blank" title="Xem chi tiết" href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['parent_id'] . '">' . $row['parent_name'] . '</a></td>
                        <td class="text-center total_quantity">' . format_number($row['total_quantity']) . '</td>
                        <td class="text-start booking_description hide-mobile">' . $row['booking_description'] . '</td>
                        <td class="text-end hide-mobile">' . format_number($row['subtotal_amount']) . '</td>
                        <td class="text-end total_bought_price hide-mobile">' . format_number($row['total_bought_price']) . '</td>
                        <td class="text-end">' . format_number($profit_amount) . ' ' . ((int)$row['total_points_amount'] > 0 ? '<span class="total_points_amount fw-semibold text-dark"> / ' . format_number($row['total_points_amount']) . '</span>' : '') . '</label></td>';

                if ($row['parent_type'] == 'EC_Flight_Bookings') {
                    $html .= '<td class="text-end hide-mobile">
                                <a title="Click vào để xem chi tiết phiếu thu" href="index.php?action=index&module=EC_Receipt_Voucher&query=true&clear_query=true&searchFormTab=basic_search&booking_name_basic=' . $row['parent_name'] . '" target="_blank">
                                    ' . format_number($row['receipt_amount']) . '
                                </a>
                            </td>';
                } else if ($row['parent_type'] == 'EC_HoanVe') {
                    $html .= '<td class="text-end hide-mobile">' . format_number($row['subtotal_amount']) . '</td>';
                } else {
                    $html .= '<td class="text-end hide-mobile">' . format_number($row['receipt_amount']) . '</td>';
                }

                // Xem chi tiết nhân viên
                $user_id_detail = isset($row['user_id']) ? $row['user_id'] : 0;
                $html .= '<td class="text-start row-employees"><a href="index.php?module=Employees&return_module=Employees&action=DetailView&record=' . $user_id_detail . '" target="_bank" title="Xem chi tiết nhân viên ' . $user_list[$row['user_id']] . '">' . $user_list[$row['user_id']] . '</a></td>';
                $html .= '<td class="text-center hide-mobile">' . $paid_time . '</td>';
                $html .= '<td class="text-center bk_date_entered hide-mobile">' . str_replace(' ', '<br>', $row['bk_date_entered']) . '</td>';
                $html .= '<td class="text-center bk_date_ticket_issue hide-mobile">' . $row['bk_date_ticket_issue'] . '</td>';
                $html .= '</tr>';

                $total_quantity += (int)$row['total_quantity'];
                $total_points_amount += (int)$row['total_points_amount'];
                $subtotal_amount += (int)$row['subtotal_amount'];
                $total_bought_price += (int)$row['total_bought_price'];
                $total_profit += (int)$profit_amount;

                if ($row['parent_type'] == 'EC_HoanVe') {
                    $total_receipt += (int)$row['receipt_amount'] + $row['subtotal_amount'];
                } else {
                    $total_receipt += (int)$row['receipt_amount'];
                }
                $i++;
            }
        }

        $html_total = '<tr class="total">
            <td class="text-center fw-semibold color-red total_quantity">' . format_number($total_quantity) . '</td>
            <td class="text-center fw-semibold color-red subtotal_amount">' . format_number($subtotal_amount) . '</td>
            <td class="text-center fw-semibold color-red total_bought_price">' . format_number($total_bought_price) . '</td>
            <td class="text-center fw-semibold color-red total_profit">' . format_number($total_profit) . '</td>
            <td class="text-center fw-semibold color-red total_receipt">' . format_number($total_receipt) . '</td>
            <td class="text-center fw-semibold color-red total_points_amount">' . format_number($total_points_amount) . '</td>
        </tr>';

        $html .= '<tr class="footer-tr">
            <td colspan="2" class="hide-mobile">&nbsp;</td>
            <td>&nbsp;</td>
            <td class="text-center fw-semibold color-red total_quantity">' . format_number($total_quantity) . '</td>
            <td class="notes hide-mobile">&nbsp;</td>
            <td class="text-end fw-semibold color-red subtotal_amount hide-mobile">' . format_number($subtotal_amount) . '</td>
            <td class="text-end fw-semibold color-red total_bought_price hide-mobile">' . format_number($total_bought_price) . '</td>
            <td class="text-end fw-semibold color-red total_profit">' . format_number($total_profit) . ' / ' . format_number($total_points_amount) . '</td>
            <td class="text-end fw-semibold color-red total_receipt hide-mobile">' . format_number($total_receipt) . '</td>
            <td class="employees">&nbsp;</td>
            <td class="date_issue hide-mobile">&nbsp;</td>
            <td class="date_created hide-mobile">&nbsp;</td>
            <td class="date_issue hide-mobile">&nbsp;</td>
        </tr>';

        $arr['html'] = $html;
        $arr['html_total'] = $html_total;
        return $arr;
    }

    // Lấy thông tin phiếu thu
    function getReceiptVoucher($booking_id)
    {
        global $db, $app_list_strings;
        $rv_status = '<label style="color:red; font-weight:bold;">Chưa lập</label>';
        $sql = "SELECT rv_status FROM ec_receipt_voucher WHERE booking_id = '" . $booking_id . "' AND deleted = 0 ";
        $res = $db->query($sql);
        $row = $db->fetchByAssoc($res);
        if (!empty($row)) {
            $rv_status = ($row['rv_status'] == '1') ? $app_list_strings['receipt_voucher_status_list'][$row['rv_status']] : '<label style="color:blue;">' . $app_list_strings['receipt_voucher_status_list'][$row['rv_status']] . '</label>';
        }
        return $rv_status;
    }

    // Lấy thông tin người recheck
    function getRecheckInfo($booking_id)
    {
        global $db;
        $sql = "SELECT w.description, u.user_name
				FROM ec_working_process w
				LEFT JOIN users u ON w.assigned_user_id = u.id AND u.deleted = 0
				WHERE w.deleted = 0
				AND w.parent_type = 'EC_Flight_Bookings'
				AND w.parent_id = '" . $booking_id . "'
				AND w.recheck > 0 ";
        $res = $db->query($sql);
        $row_count = $db->countRows($res);
        $str = '';
        $i = 0;
        while ($row = $db->fetchByAssoc($res)) {
            $str .= '<div style="font-size: 12.5px; text-align: left;">' . $row['description'] . '</div>' . '<div style="text-align: right; font-style: italic; margin-bottom: 2px;">--(' . $row['user_name'] . ')</div>';
            if ($i != ($row_count - 1))
                $str .= "\n";
            $i++;
        }
        return $str;
    }
}
