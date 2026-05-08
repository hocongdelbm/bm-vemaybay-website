<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewcheckinvoiceamount extends SugarView
{
    /**
     * @var EC_HoaDonBan
     */
    public $bean;
    protected $smarty;
    public $userTimezone;
    public $userDateFormat;
    public $userTimeFormat;
    public $dbDateFormat;

    public function __construct()
    {
        global $sugar_config, $current_user;
        $this->userTimezone = $current_user->getPreference('timezone') ?? 'Asia/Ho_Chi_Minh';
        $this->userDateFormat = $current_user->getPreference('datef') ?? $sugar_config['datef'] ?? 'd-m-Y';
        $this->userTimeFormat = $current_user->getPreference('timef') ?? $sugar_config['timef'] ?? 'H:i';
        $this->dbDateFormat = 'Y-m-d';
    }

    public function display()
    {
        if (ACLController::checkAccess('EC_HoaDonBan', 'view', true)) {
            $this->smarty = new Sugar_Smarty();
            $this->populateContent();
            $this->smarty->display("modules/{$this->bean->object_name}/tpls/view.checkinvoiceamount.tpl");
            $this->loadScripts();
        } else {
            header("Location: index.php?module={$this->bean->object_name}&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    protected function loadScripts()
    {
        echo <<<HTML
            <script src="modules/{$this->bean->module_dir}/js/view.checkinvoiceamount.js?v=1.0"></script>
        HTML;
    }

    /**
     * Populate content
     * @return void
     */
    protected function populateContent()
    {
        global $current_user, $app_list_strings, $timedate;

        // From date
        if (isset($_REQUEST['from_date']) && !empty($_REQUEST['from_date'])) {
            $req_from_date = $_REQUEST['from_date'];
        } else {
            $req_from_date = $timedate->to_display_date(date('Y-m-d'));
        }
        $this->smarty->assign('FROM_DATE_VALUE', $req_from_date);

        // To date
        if (isset($_REQUEST['to_date']) && !empty($_REQUEST['to_date'])) {
            $req_to_date = $_REQUEST['to_date'];
        } else {
            $req_to_date = $timedate->to_display_date(date('Y-m-d'));
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
        $quater_fromdate    = $timedate->to_display_date(date('Y-m-d', $quarter_from_ts));
        $quater_todate      = $timedate->to_display_date(date('Y-m-d', $quarter_to_ts));
        $from_this_month    = $timedate->to_display_date(date('Y-m-d', strtotime('first day of this month')));
        $to_this_month      = $timedate->to_display_date(date('Y-m-d', strtotime('last day of this month')));
        $from_prev_month    = $timedate->to_display_date(date('Y-m-d', strtotime('first day of last month')));
        $to_prev_month      = $timedate->to_display_date(date('Y-m-d', strtotime('last day of last month')));
        $from_prev_quarter  = $timedate->to_display_date(date('Y-m-d', strtotime('-3 months', $quarter_from_ts)));
        $to_prev_quarter    = $timedate->to_display_date(date('Y-m-d', strtotime('-3 months', $quarter_to_ts)));
        $this_year_from     = $timedate->to_display_date(date('Y-m-d', strtotime('first day of january this year')));
        $this_year_to       = $timedate->to_display_date(date('Y-m-d', strtotime('last day of december this year')));
        $prev_year_from     = $timedate->to_display_date(date('Y-m-d', strtotime('first day of January last year')));
        $prev_year_to       = $timedate->to_display_date(date('Y-m-d', strtotime('last day of December last year')));
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

        // Check if report over 31 days — compute using normalized DB dates
        $from_db_check = $timedate->to_db_date($req_from_date, false);
        $to_db_check = $timedate->to_db_date($req_to_date, false);
        $days_diff = (abs(strtotime($to_db_check) - strtotime($from_db_check)) / 60 / 60 / 24) + 1;
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

        // Invoice status
        $invoice_check_status_arr = ['' => 'Tất cả'] + $app_list_strings['invoice_check_status_list'];
        $invoice_check_status = $_REQUEST['invoice_check_status'] ?? '';
        $this->smarty->assign('INVOICE_CHECK_STATUS_OPTS', get_select_options_with_id($invoice_check_status_arr, $invoice_check_status));

        $search_conditions = [
            'payment_stt' => $payment_stt,
            'customer_source' => $customer_source,
            'ticket_type' => $ticket_type,
            'invoice_check_status' => $invoice_check_status,
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
    protected function generateMainQuery($from_date, $to_date, $conditions = [])
    {
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

        // Where condition by invoice status — dùng HAVING vì invoice_amount là computed column
        $having_invoice_check_status = '';
        if (isset($conditions['invoice_check_status']) && !empty($conditions['invoice_check_status'])) {
            switch ($conditions['invoice_check_status']) {
                case 'not_exported':
                    // Chưa xuất: tiền HĐ = 0
                    $having_invoice_check_status = 'invoice_amount = 0';
                    break;
                case 'over_exported':
                    // Xuất cao hơn: tiền HĐ > doanh thu booking/phiếu thu
                    $having_invoice_check_status = 'invoice_amount > subtotal_amount AND subtotal_amount > 0';
                    break;
                case 'under_exported':
                    // Xuất thấp hơn: có xuất nhưng thấp hơn doanh thu
                    $having_invoice_check_status = 'invoice_amount > 0 AND invoice_amount < subtotal_amount';
                    break;
            }
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
            } else if ((int)$conditions['payment_stt'] === 5) {
                $sql_having = "HAVING is_reference = 1";
            }
        }

        // Gộp 2 điều kiện HAVING: payment_stt + invoice_check_status
        $having_parts = [];

        if (!empty($sql_having)) {
            // $sql_having đang có dạng "HAVING receipt_amount = 0", cần bỏ chữ HAVING đi để ghép
            $having_parts[] = preg_replace('/^HAVING\s+/i', '', trim($sql_having));
        }
        if (!empty($having_invoice_check_status)) {
            $having_parts[] = $having_invoice_check_status;
        }

        $final_having = !empty($having_parts) ? 'HAVING ' . implode(' AND ', $having_parts) : '';

        $sql =
            "SELECT 
                bk.id AS parent_id
                , bk.id AS booking_id
                , bk.name AS booking_name
                , bk.name AS parent_name
                , bk.name AS invoice_search_name
                , 'EC_Flight_Bookings' AS parent_type
                , SUM(bkd.quantity) AS total_quantity
                , bk.total_amount AS subtotal_amount 
                , (
                    SUM(IFNULL(bkd.total_bought_price,0))
                    +
                    IFNULL((
                        SELECT IF(bk.flight_type = '0', SUM(IFNULL(px.luggage_purchase, 0)) + SUM(IFNULL(px.luggage_purchase_inbound, 0)), SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0)))
                        FROM ec_booking_passengers px
                        WHERE px.booking_id = bk.id 
                            AND (px.add_type IS NULL OR px.add_type = '')
                            AND px.deleted = 0 
                    ), 0)
                ) AS total_bought_price
                , bk.flight_type
                , bk.ticket_type
                , bk.booking_status AS parent_status
                , IFNULL((
                    SELECT SUM(IFNULL(r.amount_converted, 0))
                    FROM ec_receipt_voucher r
                    WHERE r.booking_id = bk.id
                        AND r.loai_thu = '1'
                        AND r.rv_status = '1'
                        AND r.deleted = 0
                    GROUP BY r.booking_id
                ), 0) AS receipt_amount
                , bk.date_ticket_issue AS date_ticket_issue
                , bk.is_telesale as is_telesale
                , bk.is_ctv as is_ctv
                , bk.is_reference as is_reference
                , IFNULL((hd.tong_gia_ban + hd.tong_thue + hd.tong_thu_ho), 0) AS invoice_amount
                , hd.danh_sach_hd AS invoice_list
            FROM ec_booking_details bkd 
                LEFT JOIN ec_flight_bookings bk ON bkd.booking_id = bk.id AND bk.deleted = 0
                LEFT JOIN (
                    SELECT COALESCE(hdb_map.booking_id, cthd.booking_id) AS booking_id
                        ,hdb.tongthanhtoan AS tong_gia_ban
                        ,0 AS tong_thue
				        ,0 AS tong_thu_ho
                        ,GROUP_CONCAT(DISTINCT CONCAT(IFNULL(hdb.sohoadon,''), '|', IFNULL(hdb.ngayhoadon,'')) SEPARATOR ';') AS danh_sach_hd
                        FROM ec_chitiethoadon cthd
                        INNER JOIN ec_hoadonban hdb ON hdb.id = cthd.parent_id AND hdb.deleted = 0
                        LEFT JOIN (
                            SELECT hrv_map.hoadon_id
                                ,SUBSTRING_INDEX(
                                    GROUP_CONCAT(rv_map.booking_id ORDER BY rv_map.date_modified DESC SEPARATOR ','),
                                    ',',
                                    1
                                ) AS booking_id
                            FROM hoadonban_receiptvouchers hrv_map
                            INNER JOIN ec_receipt_voucher rv_map
                                ON rv_map.id = hrv_map.receipt_id
                                AND rv_map.deleted = 0
                                AND rv_map.loai_thu = '1'
                                AND rv_map.booking_id IS NOT NULL
                                AND rv_map.booking_id <> ''
                            WHERE hrv_map.deleted = 0
                            GROUP BY hrv_map.hoadon_id
                        ) AS hdb_map ON hdb_map.hoadon_id = hdb.id
                        WHERE cthd.deleted = 0 
                            AND (
                                -- Dữ liệu mới: hóa đơn đã liên kết junction thì chấp nhận (không bó hẹp theo loai_thu)
                                EXISTS (
                                    SELECT 1
                                    FROM hoadonban_receiptvouchers hrv_bk
                                    INNER JOIN ec_receipt_voucher rv_chk
                                        ON rv_chk.id = hrv_bk.receipt_id
                                        AND rv_chk.deleted = 0
                                        AND rv_chk.loai_thu = '1'
                                    WHERE hrv_bk.hoadon_id = hdb.id
                                        AND hrv_bk.deleted = 0
                                )
                                OR (
                                    -- Fallback dữ liệu cũ: chỉ áp dụng khi hóa đơn chưa có liên kết ở junction
                                    NOT EXISTS (
                                        SELECT 1
                                        FROM hoadonban_receiptvouchers hrv_any
                                        INNER JOIN ec_receipt_voucher rv_any
                                            ON rv_any.id = hrv_any.receipt_id
                                            AND rv_any.deleted = 0
                                            AND rv_any.loai_thu = '1' 
                                        WHERE hrv_any.hoadon_id = hdb.id
                                            AND hrv_any.deleted = 0
                                    )
                                    AND (
                                        cthd.receipt_voucher_id IS NULL
                                        OR cthd.receipt_voucher_id = ''
                                        OR EXISTS (
                                            SELECT 1
                                            FROM ec_receipt_voucher rv
                                            WHERE rv.id = cthd.receipt_voucher_id
                                                AND rv.deleted = 0
                                                AND rv.loai_thu = '1'
                                        )
                                    )
                                )
                            )
                        GROUP BY COALESCE(hdb_map.booking_id, cthd.booking_id)
                ) AS hd ON hd.booking_id = bk.id

            WHERE bk.booking_status IN ('3', '7', '8')
                AND bk.date_ticket_issue $operator_between_range_date
                $where_bk_fields
                $where_ticket_type
                $sql_role
                AND bkd.deleted = 0
            GROUP BY bk.id
            $final_having";

        if ((int)($conditions['payment_stt'] ?? 0) === 0) {

            // PHẦN 2: Phiếu thu phát sinh (loai_thu không phải thu booking thông thường)
            $sql_role_rv = !$is_manager && !is_admin($current_user)
                ? " AND p.assigned_user_id = '{$current_user->id}' "
                : "";

            $hv_from = date("Y-m-d", strtotime($from_date));
            $hv_to = date("Y-m-d", strtotime($to_date));

            $from_utc = date("Y-m-d H:i:s", strtotime($hv_from)  - 7 * 3600);
            $to_utc = date("Y-m-d H:i:s", strtotime($hv_to)  - 7 * 3600 + 86399);

            $sql .= "

                UNION

                SELECT 
                    p.id AS parent_id
                    , p.id AS booking_id
                    , p.name AS booking_name
                    , p.name AS parent_name
                    , p.name AS invoice_search_name
                    , 'EC_Receipt_Voucher' AS parent_type
                    , 0 AS total_quantity
                    , SUM(IF(p.rv_status IN ('1','2'), IFNULL(p.amount_converted, 0), 0)) AS subtotal_amount
                    , 0 AS total_bought_price
                    , '' AS flight_type
                    , '' AS ticket_type
                    , p.rv_status AS parent_status
                    , SUM(IF(p.rv_status IN ('1','2'), IFNULL(p.amount_converted, 0), 0)) AS receipt_amount
                    , DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR) AS date_ticket_issue
                    , 0 AS is_telesale
                    , 0 AS is_ctv
                    , 0 AS is_reference
                    , COALESCE(
                        MAX(hd_pt.invoice_amount),
                        SUM(IF(p.rv_status IN ('1','2'), IFNULL(p.amount_converted, 0), 0))
                    ) AS invoice_amount
                    , MAX(IFNULL(hd_pt.danh_sach_hd, '')) AS invoice_list
                FROM ec_receipt_voucher p
                -- MỚI — ưu tiên junction table; fallback sang cthd cũ nếu chưa migrate
                LEFT JOIN (
                    SELECT receipt_voucher_id
                        , MAX(invoice_amount) AS invoice_amount
                        , MAX(danh_sach_hd)   AS danh_sach_hd
                    FROM (

                        -- Nguồn 1: Junction hoadonban_receiptvouchers (quan hệ N-N mới)
                        SELECT hrv.receipt_id AS receipt_voucher_id
                                -- SUM() bỏ qua NULL → nếu tất cả HĐ là chung thì SUM = NULL
                                -- → COALESCE ngoài sẽ fallback về p.amount_converted
                                -- Nếu có HĐ riêng thì SUM = tổng tongthanhtoan của các HĐ riêng đó
                            ,SUM(
                                CASE 
                                    -- HĐ chung = HĐ có chi tiết thuộc flight booking
                                    -- → PT phát sinh chỉ gom vào, không tính riêng
                                    WHEN EXISTS (
                                        SELECT 1
                                        FROM ec_chitiethoadon cthd_chk
                                        INNER JOIN ec_flight_bookings bk_chk 
                                            ON bk_chk.id = cthd_chk.booking_id 
                                            AND bk_chk.deleted = 0
                                        WHERE cthd_chk.parent_id = hdb.id
                                            AND cthd_chk.deleted = 0
                                    )
                                    THEN NULL                           -- HĐ chung: bỏ qua
                                    ELSE IFNULL(hdb.tongthanhtoan, 0)  -- HĐ riêng: lấy tongthanhtoan
                                END
                            ) AS invoice_amount
                            , GROUP_CONCAT(DISTINCT CONCAT(IFNULL(hdb.sohoadon,''), '|', IFNULL(hdb.ngayhoadon,'')) SEPARATOR ';') AS danh_sach_hd
                        FROM hoadonban_receiptvouchers hrv
                            INNER JOIN ec_hoadonban hdb ON hdb.id = hrv.hoadon_id AND hdb.deleted = 0
                        WHERE hrv.deleted = 0
                        GROUP BY hrv.receipt_id

                        UNION ALL

                        -- Nguồn 2: cthd.receipt_voucher_id cũ — CHỈ lấy cặp chưa migrate sang junction
                        SELECT cthd.receipt_voucher_id AS receipt_voucher_id
                            , (IFNULL(SUM(IFNULL(dongia, 0) * IFNULL(soluong, 0)), 0)
                                + IFNULL(SUM(IFNULL(tienthue, 0)), 0)
                                + IFNULL(SUM(IFNULL(phithuho, 0) * IFNULL(soluong, 0)), 0)
                            ) AS invoice_amount
                            , GROUP_CONCAT(DISTINCT CONCAT(IFNULL(hdb.sohoadon,''), '|', IFNULL(hdb.ngayhoadon,'')) SEPARATOR ';') AS danh_sach_hd
                        FROM ec_chitiethoadon cthd
                            INNER JOIN ec_hoadonban hdb ON hdb.id = cthd.parent_id AND hdb.deleted = 0
                        WHERE cthd.receipt_voucher_id IS NOT NULL
                            AND cthd.receipt_voucher_id <> ''
                            AND cthd.deleted = 0
                            -- Guard: bỏ qua cặp đã migrate sang junction (theo hoadon_id + receipt_id)
                            AND NOT EXISTS (
                                SELECT 1 FROM hoadonban_receiptvouchers hrv2
                                WHERE hrv2.hoadon_id = cthd.parent_id
                                    AND hrv2.receipt_id = cthd.receipt_voucher_id
                                    AND hrv2.deleted = 0
                            )
                        GROUP BY cthd.receipt_voucher_id

                    ) AS _hd_pt_combined
                    GROUP BY receipt_voucher_id
                ) AS hd_pt ON hd_pt.receipt_voucher_id = p.id
                WHERE p.loai_thu IN ('4', '5', '10', '11', '12', '13', '14', '16') 
                    AND p.ngayhachtoan >= '$from_utc' AND p.ngayhachtoan <= '$to_utc'
                    AND p.rv_status IN ('1', '2')
                    AND p.deleted = 0
                    AND IF(p.loai_thu = 10, IF(p.bought_amount IS NULL OR p.bought_amount = 0, 0, 1), 1) = 1
                    $sql_role_rv
                GROUP BY p.id
                $final_having

                UNION

                SELECT 
                    hv_t.parent_id
                    , hv_t.parent_id AS booking_id
                    , hv_t.parent_name AS booking_name
                    , hv_t.parent_name
                    , hv_t.bk_name AS invoice_search_name
                    , hv_t.parent_type
                    , SUM(hv_t.total_quantity) AS total_quantity
                    , SUM(hv_t.subtotal_amount) AS subtotal_amount
                    , SUM(hv_t.total_bought_price) AS total_bought_price
                    , '' AS flight_type
                    , '' AS ticket_type
                    , hv_t.parent_status
                    , SUM(hv_t.subtotal_amount) AS receipt_amount
                    , hv_t.date_ticket_issue
                    , 0 AS is_telesale
                    , 0 AS is_ctv
                    , 0 AS is_reference
                    , SUM(hv_t.subtotal_amount) AS invoice_amount
                    , MAX(IFNULL(hd_hv.danh_sach_hd, '')) AS invoice_list
                FROM (
                    -- Hoàn vé thường (tiền hàng <= tiền khách)
                    SELECT 
                        p.id AS parent_id
                        , p.name AS parent_name
                        , 'EC_HoanVe' AS parent_type
                        , bk.name AS bk_name
                        , -(SELECT COUNT(id) FROM ec_chitiethoanve WHERE deleted = 0 AND hoanve_id = p.id) AS total_quantity
                        , -IF(
                            SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) <= 0,
                            SUM(IFNULL(p.tongtienkhach,0)),
                            0
                        ) AS subtotal_amount
                        , -IF(
                            SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) <= 0,
                            SUM(IFNULL(p.tongtienhang,0)),
                            0
                        ) AS total_bought_price
                        , p.tinhtrang AS parent_status
                        , p.ngayhachtoan AS date_ticket_issue
                    FROM ec_hoanve p
                        INNER JOIN ec_flight_bookings bk 
                            ON bk.id = p.booking_id 
                            AND bk.deleted = 0
                            $where_bk_fields
                    WHERE DATE(p.ngayhachtoan) BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . "'
                        AND p.tinhtrang = '1'
                        AND p.deleted = 0
                        $sql_role
                    GROUP BY p.id

                    UNION

                    -- Hoàn vé đặc biệt (tiền hàng > tiền khách)
                    SELECT 
                        p.id AS parent_id
                        , p.name AS parent_name
                        , 'EC_HoanVe' AS parent_type
                        , bk.name AS bk_name
                        , 0 AS total_quantity
                        , -SUM(IFNULL(p.tongtienkhach,0)) AS subtotal_amount
                        , -SUM(IFNULL(p.tongtienhang,0)) AS total_bought_price
                        , p.tinhtrang AS parent_status
                        , p.ngayhachtoan AS date_ticket_issue
                    FROM ec_hoanve p
                        INNER JOIN ec_flight_bookings bk 
                            ON bk.id = p.booking_id 
                            AND bk.deleted = 0
                            $where_bk_fields
                    WHERE DATE(p.ngayhachtoan) BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . "'
                        AND p.tinhtrang = '1'
                        AND p.deleted = 0
                        $sql_role
                    GROUP BY p.id
                    HAVING SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) > 0

                ) AS hv_t
                LEFT JOIN (
                    SELECT 
                        hv.id AS hoanve_id,
                        bk_inv.invoice_amount,
                        bk_inv.danh_sach_hd
                    FROM ec_hoanve hv
                    INNER JOIN (
                        SELECT 
                            cthd.booking_id,
                            (
                                IFNULL(SUM(IFNULL(cthd.dongia, 0) * IFNULL(cthd.soluong, 0)), 0)
                                + IFNULL(SUM(IFNULL(cthd.tienthue, 0)), 0)
                                + IFNULL(SUM(IFNULL(cthd.phithuho, 0) * IFNULL(cthd.soluong, 0)), 0)
                            ) AS invoice_amount,
                            GROUP_CONCAT(
                                DISTINCT CONCAT(IFNULL(hdb.name,''), '|',IFNULL(hdb.sohoadon,''), '|', IFNULL(hdb.ngayhoadon,'')) 
                                SEPARATOR ';'
                            ) AS danh_sach_hd
                        FROM ec_chitiethoadon cthd
                            INNER JOIN ec_hoadonban hdb 
                                ON hdb.id = cthd.parent_id 
                                AND hdb.deleted = 0
                                AND hdb.tongthanhtoan < 0 -- Chỉ lấy hóa đơn có giá trị âm (liên quan đến hoàn vé)
                        INNER JOIN (
                            SELECT DISTINCT booking_id
                            FROM ec_hoanve
                            WHERE tinhtrang = '1'
                                AND ngayhachtoan BETWEEN '$hv_from' AND '$hv_to'
                                AND deleted = 0
                        ) AS hv_filter ON hv_filter.booking_id = cthd.booking_id
                        WHERE cthd.deleted = 0
                        GROUP BY cthd.booking_id
                    ) AS bk_inv ON bk_inv.booking_id = hv.booking_id
                    WHERE hv.ngayhachtoan BETWEEN '$hv_from' AND '$hv_to'
                        AND hv.tinhtrang = '1'
                        AND hv.deleted = 0
                ) AS hd_hv ON hd_hv.hoanve_id = hv_t.parent_id
                GROUP BY
                    hv_t.parent_id
                    , hv_t.parent_name
                    , hv_t.parent_type
                    , hv_t.parent_status
                    , hv_t.date_ticket_issue
                    $final_having
                ";
        }
        return $sql;
    }

    /**
     * Generate content from main query
     * 
     * @param string $main_query
     * @return string HTML
     */
    protected function generateMainContent($main_query)
    {
        global $timedate;
        if (empty($main_query)) return;

        $i = 1;
        $total_qty = $total_subtotal_amount = $total_receipt_amount = $total_invoice_amount = 0;
        $tr = '';

        $res = $this->bean->db->query($main_query);
        while ($row = $this->bean->db->fetchByAssoc($res)) {
            $subtotal_amount = format_number($row['subtotal_amount']);
            $receipt_amount = format_number($row['receipt_amount']);
            $invoice_amount = format_number($row['invoice_amount']);
            $date_ticket_issue = $timedate->to_display_date($row['date_ticket_issue']);

            // $list_inv_number = $list_inv_date = [];
            // $invoice_list = explode(";", $row['invoice_list'] ?? '');
            // if (is_array($invoice_list)) {
            //     foreach ($invoice_list as $inv) {
            //         $inv_attr = explode("|", $inv);
            //         if (is_array($inv_attr) && count($inv_attr) > 1) {
            //             $list_inv_number[] = $inv_attr[0];
            //             $inv_date = !empty($inv_attr[1]) ? $timedate->to_display_date($inv_attr[1]) : '';
            //             if (array_search($inv_date, $list_inv_date) === false) $list_inv_date[] = $inv_date;
            //         }
            //     }
            // }
            // $list_str_inv_number = implode("<br />", $list_inv_number);
            // $list_str_inv_date = implode("<br />", $list_inv_date);

            $tr_style = ($subtotal_amount != $receipt_amount || $subtotal_amount != $invoice_amount) ? "background:#ffebeb" : "";

            $list_inv_number = $list_inv_date = $list_inv_name = [];
            $invoice_list = explode(";", $row['invoice_list'] ?? '');

            if ($row['parent_type'] === 'EC_HoanVe') {
                // Format: name|sohoadon|ngayhoadon
                foreach ($invoice_list as $inv) {
                    $inv_attr = explode("|", $inv);
                    if (count($inv_attr) >= 3) {
                        $list_inv_name[]   = $inv_attr[0]; // HD-260421-009 → dùng để link
                        $list_inv_number[] = $inv_attr[1]; // 312 → hiển thị cột Số HĐ
                        $inv_date = !empty($inv_attr[2]) ? $timedate->to_display_date($inv_attr[2]) : '';
                        if (array_search($inv_date, $list_inv_date) === false) $list_inv_date[] = $inv_date;
                    }
                }
            } else {
                // Format cũ: sohoadon|ngayhoadon
                foreach ($invoice_list as $inv) {
                    $inv_attr = explode("|", $inv);
                    if (is_array($inv_attr) && count($inv_attr) > 1) {
                        $list_inv_number[] = $inv_attr[0];
                        $inv_date = !empty($inv_attr[1]) ? $timedate->to_display_date($inv_attr[1]) : '';
                        if (array_search($inv_date, $list_inv_date) === false) $list_inv_date[] = $inv_date;
                    }
                }
            }

            $list_str_inv_number = implode("<br />", $list_inv_number);
            $list_str_inv_date   = implode("<br />", $list_inv_date);

            if ($row['parent_type'] === 'EC_HoanVe') {
                if (!empty($list_inv_name)) {
                    $invoice_link = "index.php?module=EC_HoaDonBan&action=ListView"
                        . "&searchFormTab=basic_search&query=true&clear_query=true"
                        . "&name_basic=" . urlencode(trim($list_inv_name[0]));
                    $invoice_cell = "<a href=\"$invoice_link\" target=\"_blank\" title=\"Xem chi tiết hóa đơn\"><b>$invoice_amount</b></a>";
                } else {
                    //nếu phiếu hoàn không có hoá đơn hoàn thì không link mà chỉ hiển thị số tiền
                    $invoice_cell = "<b title=\"Phiếu hoàn chưa có hoá đơn hoàn tương ứng\">$invoice_amount</b>";
                }
            } else {
                $invoice_link = "index.php?module=EC_HoaDonBan&action=ListView"
                    . "&searchFormTab=basic_search&query=true&clear_query=true"
                    . "&booking_basic={$row['invoice_search_name']}";
                $invoice_cell = "<a href=\"$invoice_link\" target=\"_blank\" title=\"Xem chi tiết hóa đơn\"><b>$invoice_amount</b></a>";
            }
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
                        $invoice_cell
                    </td>
                    <td class="text-center ngay_hoa_don">{$list_str_inv_date}</td>
                    <td class="text-end so_hoa_don">{$list_str_inv_number}</td>
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

        $row_total_html = "";
        if (!empty($tr)) {
            $row_total_html = <<<HTML
                <tr>
                    <th class="text-center" colspan="3">Tổng</th>
                    <th class="text-center color-red">$total_qty</th>
                    <th class="text-end color-red">$total_subtotal_amount</th>
                    <th class="text-end color-red">$total_receipt_amount</th>
                    <th class="text-end color-red">$total_invoice_amount</th>
                    <th colspan="2"></th>
                </tr>
            HTML;
        } else {
            $tr = <<<HTML
                <tr>
                    <td colspan="9" class="text-center"><i>Không có dữ liệu</i></td>
                </tr>
            HTML;
        }

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
                    $row_total_html
                </thead>
                <tbody>$tr</tbody>
                <tfoot>$row_total_html</tfoot>
            </table>
        HTML;
    }
}
