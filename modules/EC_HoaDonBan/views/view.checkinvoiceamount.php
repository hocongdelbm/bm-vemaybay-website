<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewcheckinvoiceamount extends SugarView {
    /**
     * @var EC_HoaDonBan
     */
    public $bean;
    protected $smarty;
    public $userTimezone;
    public $userDateFormat;
    public $userTimeFormat;
    public $dbDateFormat;

    public function __construct() {
        global $sugar_config, $current_user;
        $this->userTimezone = $current_user->getPreference('timezone') ?? 'Asia/Ho_Chi_Minh';
        $this->userDateFormat = $current_user->getPreference('datef') ?? $sugar_config['datef'] ?? 'd-m-Y';
        $this->userTimeFormat = $current_user->getPreference('timef') ?? $sugar_config['timef'] ?? 'H:i';
        $this->dbDateFormat = 'Y-m-d';
    }

    public function display() {
        if (ACLController::checkAccess('EC_HoaDonBan', 'view', true)) {
            $this->smarty = new Sugar_Smarty();
            $this->populateContent();
            $this->smarty->display("modules/{$this->bean->object_name}/tpls/view.checkinvoiceamount.tpl");
            $this->loadScripts();
        }
        else {
            header("Location: index.php?module={$this->bean->object_name}&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    protected function loadScripts() {
        echo <<<HTML
            <script src="modules/{$this->bean->module_dir}/js/view.checkinvoiceamount.js?v=1.0"></script>
        HTML;
    } 

    /**
     * Populate content
     * @return void
     */
    protected function populateContent() {
        global $current_user, $app_list_strings;

        // From date
        if (isset($_REQUEST['from_date']) && !empty($_REQUEST['from_date']) && strtotime($_REQUEST['from_date']) !== false) {
            $req_from_date = $_REQUEST['from_date'];
        } else {
            $req_from_date = date($this->userDateFormat);
        }
        $this->smarty->assign('FROM_DATE_VALUE', $req_from_date);

        // To date
        if (isset($_REQUEST['to_date']) && !empty($_REQUEST['to_date']) && strtotime($_REQUEST['to_date']) !== false) {
            $req_to_date = $_REQUEST['to_date'];
        } else {
            $req_to_date = date($this->userDateFormat);
        }
        $this->smarty->assign('TO_DATE_VALUE', $req_to_date);

        // Pick a date range quickly
        $req_pick_quickly_date = $_REQUEST['pick_quickly_date'] ?? '';
        $month = date('n');
        $year = date('Y');
        $quarter = ceil($month / 3);
        $start_month = ($quarter - 1) * 3 + 1;
        $end_month = $start_month + 2;
        $quarter_from_ts    = strtotime(sprintf('%d-%02d-01', $year, $start_month));
        $quarter_to_ts      = strtotime("last day of " . sprintf('%d-%02d', $year, $end_month));
        $quater_fromdate    = date($this->userDateFormat, $quarter_from_ts);
        $quater_todate      = date($this->userDateFormat, $quarter_to_ts);
        $from_this_month    = date($this->userDateFormat, strtotime('first day of this month'));
        $to_this_month      = date($this->userDateFormat, strtotime('last day of this month'));
        $from_prev_month    = date($this->userDateFormat, strtotime('first day of last month'));
        $to_prev_month      = date($this->userDateFormat, strtotime('last day of last month'));
        $from_prev_quarter  = date($this->userDateFormat, strtotime('-3 months', $quarter_from_ts));
        $to_prev_quarter    = date($this->userDateFormat, strtotime('-3 months', $quarter_to_ts));
        $this_year_from     = date($this->userDateFormat, strtotime('first day of january this year'));
        $this_year_to       = date($this->userDateFormat, strtotime('last day of december this year'));
        $prev_year_from     = date($this->userDateFormat, strtotime('first day of January last year'));
        $prev_year_to       = date($this->userDateFormat, strtotime('last day of December last year'));
        $selected_this_month    = $req_pick_quickly_date === 'this_month' ? 'selected' : '';
        $selected_prev_year     = $req_pick_quickly_date === 'previous_year' ? 'selected' : '';
        $selected_this_year     = $req_pick_quickly_date === 'this_year' ? 'selected' : '';
        $selected_prev_month    = $req_pick_quickly_date === 'previous_month' ? 'selected' : '';
        $selected_quarter       = $req_pick_quickly_date === 'quater_this_month' ? 'selected' : '';
        $selected_prev_quarter  = $req_pick_quickly_date === 'quater_previous_month' ? 'selected' : '';
        $pick_quickly_date_otps = <<<HTML
            <option value="" fromdate="" todate="">-- Chọn nhanh --</option>
            <option value="this_month" fromdate="{$from_this_month}" todate="{$to_this_month}" {$selected_this_month}>Tháng này</option>
            <option value="previous_month" fromdate="{$from_prev_month}" todate="{$to_prev_month}" {$selected_prev_month}>Tháng trước</option>
            <option value="quater_this_month" fromdate="{$quater_fromdate}" todate="{$quater_todate}" {$selected_quarter}>Quý này</option>
            <option value="quater_previous_month" fromdate="{$from_prev_quarter}" todate="{$to_prev_quarter}" {$selected_prev_quarter}>Quý trước</option>
            <option value="this_year" fromdate="{$this_year_from}" todate="{$this_year_to}" {$selected_this_year}>Năm nay</option>
            <option value="previous_year" fromdate="{$prev_year_from}" todate="{$prev_year_to}" {$selected_prev_year}>Năm trước</option>
        HTML;
        $this->smarty->assign('DATE_OPTION', $pick_quickly_date_otps);

        // Check if report over 31 days
        $days_diff = (abs(strtotime($req_to_date) - strtotime($req_from_date)) / 60 / 60 / 24) + 1;
        if (!is_admin($current_user) && $days_diff > 31) {
            echo '<p class="error">Vui lòng chọn trong khoảng thời gian 31 ngày</p>';
            exit;
        }

        // Payment status
        $payment_status_arr = [
            0 => 'Tất cả',
            1 => 'Chưa thu',
            2 => 'Chưa thu đủ',
            3 => 'BK telesale',
            4 => 'BK CTV',
            5 => 'BK tham khảo',
        ];
        $payment_stt = (int)($_REQUEST['payment_stt'] ?? 0);
        $this->smarty->assign('PAYMENT_STT_OPTS', get_select_options_with_id($payment_status_arr, $payment_stt));

        // Customer source
        $customer_source_arr = ['' => 'Tất cả'] + $app_list_strings['booking_customer_source_list'];
        $customer_source = $_REQUEST['customer_source'] ?? '';
        $this->smarty->assign('CUSTOMER_SOURCE_OPTS', get_select_options_with_id($customer_source_arr, $customer_source));

        // Ticket type
        $ticket_type_arr = ['' => 'Tất cả'] + $app_list_strings['booking_ticket_type_list'];
        $ticket_type = $_REQUEST['ticket_type'] ?? '';
        $this->smarty->assign('TICKET_TYPE_OPTS', get_select_options_with_id($ticket_type_arr, (int)$ticket_type));

        $search_conditions = [
            'payment_stt' => $payment_stt,
            'customer_source' => $customer_source,
            'ticket_type' => $ticket_type
        ];
        $main_query = $this->generateMainQuery($req_from_date, $req_to_date, $search_conditions);
        $this->smarty->assign('MAIN_CONTENT', $this->generateMainContent($main_query));
    }

    /**
     * Generate main query to create report
     * If this query wrong data, please check custom/include/utils/booking.php calculateRevenueOfDate() and syn code from the function
     * 
     * @param string $from_date
     * @param string $to_date
     * @param array $conditions
     * @return string SQL query
     */
    protected function generateMainQuery($from_date, $to_date, $conditions = []) {
        global $timedate, $current_user;

        $from_db = $timedate->to_db_date($from_date, false);
        $to_db   = $timedate->to_db_date($to_date, false);
        if (empty($from_db) || empty($to_db)) {
            return '';
        }
        $operator_between_range_date = "BETWEEN '$from_db' AND '$to_db'";

        // Where condition by booking fields
        $where_bk_fields = '';
        if (isset($conditions['customer_source']) && !empty($conditions['customer_source'])) {
            $where_bk_fields .= " AND bk.customer_source = '{$conditions['customer_source']}' ";
        }

        // Where condition by booking fields ticket_type ('1' : Nội địa, '2' : Quốc tế)
        $where_ticket_type = '';
        if (isset($conditions['ticket_type']) && !empty($conditions['ticket_type'])) {
            $where_ticket_type .= " AND bk.ticket_type = '{$conditions['ticket_type']}' ";
        }

        // Chỉ kế toán trưởng hoặc admin hệ thống mới được xem hết, còn lại xem của mình
        $sql_manager = "SELECT COUNT(id) 
            FROM acl_roles_users 
            WHERE user_id = '{$current_user->id}'
                AND role_id IN (
                    '{$GLOBALS['app_list_strings']['roles_users']['QUANLY']}',
                    '{$GLOBALS['app_list_strings']['roles_users']['KETOAN']}'
                )
                AND deleted = 0";
        $is_manager = $this->bean->db->getOne($sql_manager);
        $sql_role = !$is_manager && !is_admin($current_user) ? " AND bk.assigned_user_id = '{$current_user->id}' " : "";

        // Tìm theo tình trạng phiếu thu của booking: chưa thu / chưa thu đủ
        $sql_having = "";
        if (isset($conditions['payment_stt'])) {
            if ((int)$conditions['payment_stt'] === 1) { // Chưa thu
                $sql_having = "HAVING receipt_amount = 0";
            } else if ((int)$conditions['payment_stt'] === 2) { // Chưa thu đủ
                $sql_having = "HAVING receipt_amount < subtotal_amount AND receipt_amount > 0";
            } else if ((int)$conditions['payment_stt'] === 3) { // Booking telesale
                $sql_having = "HAVING is_telesale = 1";
            } else if ((int)$conditions['payment_stt'] === 4) {
                $sql_having = "HAVING is_ctv = 1";
            } else if((int)$conditions['payment_stt'] === 5) {
                $sql_having = "HAVING is_reference = 1";
            }
        }

        $sql = 
            "SELECT 
                bk.id AS parent_id
                , bk.id AS booking_id
                , bk.name AS booking_name
                , bk.name AS parent_name
                , 'EC_Flight_Bookings' AS parent_type
                , SUM(bkd.quantity) AS total_quantity
                , bk.total_amount AS subtotal_amount 
                , (
                    IFNULL((
                        SELECT SUM(IFNULL(pc1.down * 1000, 0))
                        FROM ec_contact_points_log pc1
                        WHERE pc1.parent_type = 'EC_Flight_Bookings' 
                            AND pc1.parent_id = bk.id 
                            AND pc1.deleted = 0
                    ), 0)
                    -  
                    IFNULL((
                        SELECT SUM(IFNULL(pc2.up * 1000, 0))
                        FROM ec_contact_points_log pc2
                        WHERE pc2.parent_type = 'EC_Contact_Points_Log' 
                            AND pc2.parent_id IN (
                                SELECT pc_inner.id
                                FROM ec_contact_points_log pc_inner
                                WHERE pc_inner.parent_type = 'EC_Flight_Bookings' 
                                    AND pc_inner.parent_id = bk.id 
                                    AND pc_inner.deleted = 0
                            )
                            AND pc2.deleted = 0
                    ), 0)
                ) AS total_points_amount
                , (SUM(IFNULL(bkd.total_bought_price,0))
                +
                IFNULL((
                    SELECT IF(bk.flight_type = '0', SUM(IFNULL(px.luggage_purchase, 0)) +  SUM(IFNULL(px.luggage_purchase_inbound, 0)), SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0)))
                    FROM ec_booking_passengers px
                    WHERE px.booking_id = bk.id 
                        AND px.deleted = 0 
                        AND (px.add_type IS NULL OR px.add_type = '')
                ),0)) AS total_bought_price
                , bk.flight_type
                , bk.ticket_type
                , bk.booking_status AS parent_status
                , IFNULL((
                    SELECT SUM(IFNULL(r.amount_converted,0))
                    FROM ec_receipt_voucher r
                    WHERE r.booking_id = bk.id
                        AND r.rv_status='1'
                        AND r.loai_thu='1'
                        AND r.deleted=0
                    GROUP BY r.booking_id
                ), 0) AS receipt_amount
                ,DATE_FORMAT(bk.date_ticket_issue, '%d-%m-%Y') AS date_ticket_issue
                ,DATE_FORMAT(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), '%d-%m-%Y %H:%i') AS bk_date_entered
                ,DATE_FORMAT(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), '%d-%m-%Y') AS voucher_date
                ,(
                    SELECT DATE_ADD(date_entered, INTERVAL 7 HOUR)
                    FROM ec_working_process
                    WHERE deleted = 0 AND paid = 1 AND parent_id = bk.id
                ) AS paid_time
                , bk.is_telesale as is_telesale
                , bk.is_ctv as is_ctv
                , bk.is_reference as is_reference
                , hdb.tongthanhtoan AS invoice_amount
                , hdb.name AS chungtuhoadon
                , hdb.ngayhoadon
                , hdb.sohoadon
            FROM ec_booking_details bkd 
                LEFT JOIN ec_flight_bookings bk ON bkd.booking_id = bk.id AND bk.deleted = 0
                INNER JOIN ec_chitiethoadon cthd ON cthd.booking_id = bk.id AND cthd.deleted = 0 AND cthd.parent_id != ''
                INNER JOIN ec_hoadonban hdb ON hdb.id = cthd.parent_id AND hdb.deleted = 0
            WHERE bk.booking_status IN ('3', '7', '8')
                AND bk.date_ticket_issue $operator_between_range_date
                $where_bk_fields
                $where_ticket_type
                $sql_role
                AND bkd.deleted = 0
            GROUP BY bk.id
            $sql_having";

        return $sql;
    }

    /**
     * Generate content from main query
     * 
     * @param string $main_query
     * @return string HTML
     */
    protected function generateMainContent($main_query) {
        if(empty($main_query)) return;
        
        $i = 1;
        $total_qty = $total_subtotal_amount = $total_receipt_amount = $total_invoice_amount = 0;
        $tr = '';

        $res = $this->bean->db->query($main_query);
        while ($row = $this->bean->db->fetchByAssoc($res)) {
            $subtotal_amount = format_number($row['subtotal_amount']);
            $receipt_amount = format_number($row['receipt_amount']);
            $invoice_amount = format_number($row['invoice_amount']);
            $date_ticket_issue = date($this->userDateFormat, strtotime($row['date_ticket_issue']));
            $ngayhoadon = date($this->userDateFormat, strtotime($row['ngayhoadon']));

            $tr_style = ($subtotal_amount != $receipt_amount || $subtotal_amount != $invoice_amount) ? "background:#ffebeb" : "";

            $tr .= <<<HTML
                <tr style="$tr_style">
                    <td class="text-center hide-mobile">$i</td>
                    <td class="date_ticket_issue">$date_ticket_issue</td>
                    <td class="booking">
                        <a href="index.php?module={$row['parent_type']}&action=DetailView&record={$row['parent_id']}" target="_blank" title="Xem chi tiết">
                            {$row['parent_name']}
                        </a>
                    </td>
                    <td class="text-center ticket_quantity">{$row['total_quantity']}</td>
                    <td class="text-end">$subtotal_amount</td>
                    <td class="text-end">
                        <a href="index.php?action=index&module=EC_Receipt_Voucher&query=true&clear_query=true&searchFormTab=basic_search&booking_name_basic={$row['parent_name']}" target="_blank" title="Xem chi tiết phiếu thu">
                            $receipt_amount
                        </a>
                    </td>
                    <td class="text-end invoice_amount">
                        <a href="index.php?action=index&module=EC_HoaDonBan&action=ListView&query=true&clear_query=true&searchFormTab=basic_search&name_basic={$row['chungtuhoadon']}" target="_blank" title="Xem chi tiết hóa đơn">
                            <b>$invoice_amount</b>
                        </a>
                    </td>
                    <td class="text-center ngay_hoa_don">$ngayhoadon</td>
                    <td class="text-end so_hoa_don">{$row['sohoadon']}</td>
                </tr>
            HTML;

            $total_qty += $row['total_quantity'] ?? 0;
            $total_subtotal_amount += $row['subtotal_amount'] ?? 0;
            $total_receipt_amount += $row['receipt_amount'] ?? 0;
            $total_invoice_amount += $row['invoice_amount'] ?? 0;
            
            $i++;
        }

        $total_subtotal_amount = format_number($total_subtotal_amount);
        $total_receipt_amount = format_number($total_receipt_amount);
        $total_invoice_amount = format_number($total_invoice_amount);

        return <<<HTML
            <table id="main_table" class="table table-hover mt-3">
                <thead style="font-size:0.85rem;">
                    <tr>
                        <th width="6%" class="text-center hide-mobile">STT</th>
                        <th width="10%">Ngày xuất vé</th>
                        <th width="10%">Booking</th>
                        <th width="6%" class="text-center">Vé</th>
                        <th class="text-end">Doanh thu</th>
                        <th class="text-end">Phiếu thu</th>
                        <th class="text-end">Tiền HĐ</th>
                        <th width="10%" class="text-center">Ngày HĐ</th>
                        <th width="8%" class="text-end">Số HĐ</th>
                    </tr>
                </thead>
                <tbody>$tr</tbody>
                <tbody>
                    <tr>
                        <th colspan="3"></th>
                        <th class="text-center color-red">$total_qty</th>
                        <th class="text-end color-red">$total_subtotal_amount</th>
                        <th class="text-end color-red">$total_receipt_amount</th>
                        <th class="text-end color-red">$total_invoice_amount</th>
                        <th colspan="2"></th>
                    </tr>
                </tbody>
            </table>
        HTML;
    }
}