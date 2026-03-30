<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/SugarView.php');

class Viewtypereports extends SugarView
{
    public function display()
    {
        global $current_user;

        $con_return = $this->populateCondition();

        $smartyCont = new Sugar_Smarty();
        $this->assignFields($con_return, $smartyCont);
        $smartyCont->display('modules/Contacts/tpls/typereports.tpl');
    }

    function populateCondition()
    {
        $currYear      = date('Y');
        $reportTime    = 'năm ' . date('Y');
        $type_customer = '';

        if (isset($_REQUEST['type_customer']) && !empty($_REQUEST['type_customer'])) {
            $type_customer = $_REQUEST['type_customer'];
        }

        if (isset($_REQUEST['from_date']) && !empty($_REQUEST['from_date']) && strtotime($_REQUEST['from_date']) !== false) {
            $from_date = date('d-m-Y', strtotime($_REQUEST['from_date']));
        } else {
            $from_date = date('d-m') . '-' . ($currYear - 1);
            $_REQUEST['from_date'] = $from_date;
        }

        if (isset($_REQUEST['to_date']) && !empty($_REQUEST['to_date']) && strtotime($_REQUEST['to_date']) !== false) {
            $to_date = date('d-m-Y', strtotime($_REQUEST['to_date']));
        } else {
            $to_date = date('d-m-Y', strtotime($currYear));
            $_REQUEST['to_date'] =  $to_date;
        }

        $year_select = isset($_REQUEST['year_select']) ? $_REQUEST['year_select'] : '';

        return [
            'curr_year'    => $currYear,
            'report_time'  => $reportTime,
            'type_customer' => $type_customer,
            'from_date'    => $from_date,
            'to_date'      => $to_date,
            'year_select'   => $year_select,
        ];
    }

    function assignFields($con, $smarty)
    {
        global $current_user;

        $time_opt = $this->populateReportYearSelect($con);
        $smarty->assign('YEAR_SELECT', $time_opt['html']);
        $smarty->assign('FROM_DATE', $con['from_date']);
        $smarty->assign('TO_DATE', $con['to_date']);
        $smarty->assign('REPORT_TIME', $time_opt['report_time']);

        $smarty->assign('MODULE_NAME', $this->bean->module_dir);

        // ROLE
        $smarty->assign('IS_ADMIN', (isAllowedUser()));
        $smarty->assign('OWNER', (!isAllowedUser() ? 1 : 0));

        $text_type_customer = isset($GLOBALS['app_list_strings']['contact_type_list'][$con['type_customer']]) ? $GLOBALS['app_list_strings']['contact_type_list'][$con['type_customer']] : '';
        $smarty->assign('TEXT_TYPE_CUSTOMER', $text_type_customer);
        $smarty->assign('TYPE_CUSTOMER', $con['type_customer']);

        // LIST CUSTOMER FOR TYPE
        $list_customer = $this->genListCustomerForType($con);
        $smarty->assign('HTML_LIST_CUSTOMER', $list_customer['data']);
        $smarty->assign('TOTAL_CUSTOMER', $list_customer['total']);

        // EMPLOYEE SELECT
        $employee_option = $this->getEmployeeSelect();
        $smarty->assign('EMLOYEE_OPTION', $employee_option);

        // ASSIGN CONTACTS
        if(isset($_POST['btnSaveAssignContactEmp'])){
            $this->assignContactForEmployee($con);
        } else if (isset($_POST['btnSaveAssignContact'])){
            $this->assignContactForList($con);
        }
    }

    function populateReportYearSelect($con){
        $currYear = $con['curr_year'] ?? date('Y');
        $reportTime = $con['year_select'] ?? '';

        $labels = ['Chu kỳ', 'Năm ' . $currYear, 'Năm ' . ($currYear - 1), 'Năm ' . ($currYear - 2), 'Năm ' . ($currYear - 3)];
        $values = ['period', 'this_year', 'previous_year', 'past_year', 'old_year'];
        $dateOptions = [];

        foreach ($values as $key => $value) {
            $year = $currYear - $key + 1; // Lấy năm tương ứng.
            $fromdate = $value === 'period' ? date('d-m') . '-' . ($currYear - 1) : "01-01-$year";
            $todate = $value === 'period' ? date('d-m-Y') : "31-12-$year";
            $dateOptions[] = [
                'value' => $value,
                'fromdate' => $fromdate,
                'todate' => $todate,
                'label' => $labels[$key],
                'selected' => $reportTime === $value
            ];
        }

        $html = implode('', array_map(function ($option) {
            return '<option value="' . $option['value'] . '" fromdate="' . $option['fromdate'] . '" todate="' . $option['todate'] . '"'
                . ($option['selected'] ? ' selected' : '') . '>' . $option['label'] . '</option>';
        }, $dateOptions));
    
        return [
            'html' => $html,
            'report_time' => $reportTime
        ];
    }

    function genListCustomerForType($params)
    {
        global $db, $current_user;
   
        $type_customer = $params['type_customer'] ?? null;

        // $start_date = date('Y-m-d H:i:s', strtotime('-1 year +7 hours'));
        // $end_date   = date('Y-m-d H:i:s', strtotime('+7 hours'));
        $start_date = date('Y-m-d 00:00:00', strtotime($params['from_date']));
        $end_date = date('Y-m-d 23:59:59', strtotime($params['to_date']));

        $sql_search = '';
        if (!isAllowedUser()) {
			$sql_search .= " WHERE c.assigned_user_id='" . $current_user->id . "' ";
		}

        $sql = "SELECT 
                c.id,
                c.assigned_user_id,
                IFNULL(c.last_name, 'Không xác định') AS last_name,
                c.phone_mobile,
                CASE 
                    -- VIP members: tổng cộng từ 20 booking trở lên và doanh số trên 20tr
                    WHEN completed >= 20 AND profit_period_0 > 20000000 THEN 'VIP_MEMBER'
                    -- Gold members: trong chu kỳ năm có từ 10 booking trở lên
                    WHEN current_period >= 10 THEN 'GOLD_MEMBER'
                    -- Silver members: trong chu kỳ năm có từ 4 - 9 booking
                    WHEN current_period BETWEEN 4 AND 9 THEN 'SILVER_MEMBER'
                    -- Return customer: trong chu kỳ năm có từ 1-3 booking và quá khứ có ít nhất 01 booking
                    WHEN current_period BETWEEN 1 AND 3 AND past_period > 0 THEN 'RETURN_CUSTOMER'
                    -- New customer: trong chu kỳ năm có từ 1-3 booking và quá khứ không có booking
                    WHEN current_period BETWEEN 1 AND 3 AND past_period = 0 THEN 'NEW_CUSTOMER'
                    WHEN current_period > 0 THEN 'OTHER'
                    ELSE 'DEFAULT_GROUP'
                END AS type,
                t.current_period,
                t.past_period,
                t.completed,
                t.profit_period_0,
                t.profit_period_1,
                t.profit_period_2
            FROM (
                SELECT 
                    contact_id,
                    -- Chu kỳ hiện tại
                    SUM(CASE WHEN date_entered BETWEEN '$start_date' AND '$end_date' THEN 1 ELSE 0 END) AS current_period,
                    SUM(CASE WHEN date_entered < '$start_date' THEN 1 ELSE 0 END) AS past_period,
                    -- Chu kỳ trước đó (2022/23)
                    SUM(CASE WHEN date_entered BETWEEN DATE_SUB('$start_date', INTERVAL 1 YEAR) AND DATE_SUB('$end_date', INTERVAL 1 YEAR) THEN 1 ELSE 0 END) AS prev_period_1,
                    -- Chu kỳ 2 năm trước (2021/22)
                    SUM(CASE WHEN date_entered BETWEEN DATE_SUB('$start_date', INTERVAL 2 YEAR) AND DATE_SUB('$end_date', INTERVAL 2 YEAR) THEN 1 ELSE 0 END) AS prev_period_2,
                    SUM(
                        CASE 
                            WHEN date_entered BETWEEN '$start_date' AND '$end_date' THEN (
                                bk.total_amount
                                - IFNULL((
                                    SELECT SUM(IFNULL(bd.total_bought_price, 0))
                                    FROM ec_booking_details bd
                                    WHERE bd.deleted = 0 AND bd.booking_id = bk.id
                                ), 0)
                                - IFNULL((
                                SELECT SUM(
                                    IF(bk.flight_type = '0', 
                                        IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                        + IF(px.luggage_price_inbound > 0, IFNULL(px.luggage_purchase_inbound, 0), 0),
                                        IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                    )
                                )
                                FROM ec_booking_passengers px
                                WHERE px.deleted = 0 AND px.booking_id = bk.id
                                ), 0)
                            )
                            ELSE 0
                        END
                    ) AS profit_period_0, -- Doanh số chu kỳ hiện tại
                    -- Doanh số chu kỳ trước đó (2022/23)
                    SUM(
                        CASE 
                            WHEN date_entered BETWEEN DATE_SUB('$start_date', INTERVAL 1 YEAR) AND DATE_SUB('$end_date', INTERVAL 1 YEAR) THEN (
                                bk.total_amount
                                - IFNULL((SELECT SUM(IFNULL(bd.total_bought_price, 0)) 
                                        FROM ec_booking_details bd 
                                        WHERE bd.deleted = 0 AND bd.booking_id = bk.id), 0)
                                - IFNULL((SELECT SUM(
                                            IF(bk.flight_type = '0', 
                                                IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                                + IF(px.luggage_price_inbound > 0, IFNULL(px.luggage_purchase_inbound, 0), 0),
                                                IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                            )
                                        )
                                        FROM ec_booking_passengers px
                                        WHERE px.deleted = 0 AND px.booking_id = bk.id), 0)
                            )
                            ELSE 0
                        END
                    ) AS profit_period_1, -- Doanh số chu kỳ trước
                    -- Doanh số chu kỳ 2 năm trước (2021/22)
                    SUM(
                        CASE 
                            WHEN date_entered BETWEEN DATE_SUB('$start_date', INTERVAL 2 YEAR) AND DATE_SUB('$end_date', INTERVAL 2 YEAR) THEN (
                                bk.total_amount
                                - IFNULL((SELECT SUM(IFNULL(bd.total_bought_price, 0)) 
                                        FROM ec_booking_details bd 
                                        WHERE bd.deleted = 0 AND bd.booking_id = bk.id), 0)
                                - IFNULL((SELECT SUM(
                                            IF(bk.flight_type = '0', 
                                                IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                                + IF(px.luggage_price_inbound > 0, IFNULL(px.luggage_purchase_inbound, 0), 0),
                                                IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                            )
                                        )
                                        FROM ec_booking_passengers px
                                        WHERE px.deleted = 0 AND px.booking_id = bk.id), 0)
                            )
                            ELSE 0
                        END
                    ) AS profit_period_2, -- Doanh số chu kỳ 2 năm trước
                    COUNT(*) AS completed
                FROM ec_flight_bookings bk
                WHERE bk.booking_status = '8' AND bk.deleted = 0
                AND (bk.contact_id IS NOT NULL OR bk.contact_id != '')
                GROUP BY bk.contact_id
            ) t
            JOIN contacts c ON c.id = t.contact_id
            $sql_search
            ORDER BY t.profit_period_0 DESC";

            // if($current_user->user_name == 'hungnh'){
            //     pr($sql);
            // }

        $html = '<div class="box-list__customer mt-3" style="max-height: 75vh; overflow: auto;">
                    <table class="table-list__customer table-details__booking table__sticky text-nowrap" cellpadding="0" cellspacing="0" width="100%">
                        <thead>
                                <tr>
                                    <th width="3%">#</th>';
                                    if(isAllowedUser()){
                                        $html .= '<th width="3%"><input type="checkbox" id="checkall" value="0"></th>';
                                    }
                                    
                        $html .= '<th width="15%" align="center">Tên khách hàng</th>
                                    <th width="8%" align="center">Doanh số</th>
                                    <th width="8%" align="center">BK Hoàn tất</th>
                                    <th width="8%" align="center">BK Hoàn tất quá khứ</th>
                                    <th width="8%" align="center">SL Booking</th>
                                    <th align="center">Ghi chú</th>
                                    <th width="8%" align="center">Giao cho</th>
                                    <th width="8%" align="center">Xem thêm</th>
                                </tr>
                        </thead>
                        <tbody>';

        $i = 1;
        $res = $db->query($sql);
        // $total_booking = $db->getRowCount($res);
        $user_list = get_user_array(true, '', '', true);

        while ($row = $db->fetchByAssoc($res)) {
            if($type_customer === $row['type']){
                $html .= '
                    <tr>
                        <td align="center" class="fw-semibold">' . $i . '</td>';
                        if(isAllowedUser()){
                            $html .= '<td class="text-center fw-bold">
                                        <input type="checkbox" name="contact_id[]" value="' . $row['id'] . '" />
                                    </td>';
                        }

            $html .= '<td align="left"><a target="_blank" href="index.php?module=Contacts&return_module=Contacts&action=DetailView&record=' . $row['id'] . '">' . $row['last_name'] . '</a></td>
                        <td align="center" class="text-danger"><strong>' . format_number($row['profit_period_0']) . '</strong></td>
                        <td align="center" class="text-dark"><strong>' . $row['current_period'] . '</strong></td>
                        <td align="center" class="text-dark"><strong>' . $row['past_period'] . '</strong></td>
                        <td align="center" class="text-dark"><strong>' . $row['completed'] . '</strong></td>
                        <td align="left">' . $row['phone_mobile'] . '</td>
                        <td align="center">' . $user_list[$row['assigned_user_id']] . '</td>
                        <td align="center">
                            <div class="dropdown text-dark position-static">
                                <a class="cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="index.php?module=Contacts&return_module=Contacts&action=DetailView&record=' . $row['id'] . '" class="dropdown-item cursor-pointer text-decoration-none fw-medium" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                                <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                                            </svg>
                                            <span>Thông tin khách hàng</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item cursor-pointer text-decoration-none fw-medium view-detail-contact" data-bs-toggle="modal" data-bs-target="#modalViewDetail" data-contact-id="' . $row['id'] . '">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-list-ul me-2" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"></path>
                                            </svg>
                                            <span>Danh sách booking</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item cursor-pointer text-decoration-none fw-medium btn-voiceip-calling" id="listview-call_from" phone="' . $row['phone_mobile'] . '">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-outbound me-2" viewBox="0 0 16 16">
                                                <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877zM11 .5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V1.707l-4.146 4.147a.5.5 0 0 1-.708-.708L14.293 1H11.5a.5.5 0 0 1-.5-.5"/>
                                            </svg>
                                            <span>Gọi khách hàng</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item cursor-pointer text-decoration-none fw-medium view-activity-contact" data-bs-toggle="modal" data-bs-target="#modalViewHistoryActivity" data-type="get_history_activity_contacts" data-phone="' . $row['phone_mobile'] . '">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-activity me-2" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2"></path>
                                            </svg>
                                            <span>Lịch sử tương tác</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item cursor-pointer text-decoration-none fw-medium view-activity-contact" data-bs-toggle="modal" data-bs-target="#modalViewHistoryActivity" data-type="get_history_activity_cskh" data-phone="' . $row['phone_mobile'] . '">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-headset me-2" viewBox="0 0 16 16">
                                                <path d="M8 1a5 5 0 0 0-5 5v1h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a6 6 0 1 1 12 0v6a2.5 2.5 0 0 1-2.5 2.5H9.366a1 1 0 0 1-.866.5h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 .866.5H11.5A1.5 1.5 0 0 0 13 12h-1a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h1V6a5 5 0 0 0-5-5"/>
                                            </svg>
                                            <span>Cuộc gọi CSKH</span>
                                        </a>
                                    </li>
                                ';
                                if(isAllowedUser()){
                                $html .= '<li>
                                            <a href="javascript:void(0);" class="dropdown-item cursor-pointer text-decoration-none fw-medium assign-contact" data-contact_id="' . $row['id'] . '" data-contact_name="' . $row['last_name'] . '" data-bs-toggle="modal" data-bs-target="#modalAssignContacts">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-add me-2" viewBox="0 0 16 16">
                                                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                                                    <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                                </svg>
                                                <span>Giao cho nhân viên</span>
                                            </a>
                                        </li>';
                                }

                        $html .= '</ul>
                            </div>
                        </td>
                    </tr>
                ';

                $i++;
            }
        }

        $html .= '</tbody></table></div>';

        return ['data' => $html, 'total' => ($i - 1)];
    }

    function checkExitsContacts()
    {
        // Kết quả trả về chính là những khách hàng có ID trong bảng booking nhưng không tồn tại trong bảng contacts.
        $sql = "SELECT t.contact_id
                FROM (
                    SELECT contact_id
                    FROM ec_flight_bookings
                    WHERE booking_status = '8' AND deleted = 0
                    GROUP BY contact_id
                ) t
                LEFT JOIN contacts c ON t.contact_id = c.id
                WHERE c.id IS NULL";
    }

    function getEmployeeSelect(){
        global $db, $current_user;

        $sql = 'SELECT id, CONCAT(last_name, " ", IFNULL(first_name, "")) AS full_name 
				FROM users 
				WHERE deleted = 0 
				AND status = "Active" 
                AND is_admin = 0
				AND title NOT IN ("Admin", "Bot")';
		$res = $this->bean->db->query($sql);
		while($row = $this->bean->db->fetchByAssoc($res)) {
			$employee_list[$row['id']] = $row['full_name'];
		}

        $html = get_select_options_with_id($employee_list, '');

        return $html;
    }

    function assignContactForEmployee($params){
        global $db, $current_user;

        $user_id    = htmlspecialchars($_POST['employee_id'] ?? '');
        $contact_id = htmlspecialchars($_POST['contact_id'] ?? '');
        $contact_name = htmlspecialchars($_POST['contact_name'] ?? '');
        $typereports = htmlspecialchars($params['type_customer'] ?? '');

        if(empty($user_id) || empty($contact_id)){
            SugarApplication::appendErrorMessage('Lỗi: Thiếu thông tin Khách hàng hoặc thông tin nhân viên. Vui lòng liên hệ Admin để được hỗ trợ!');
            header("Location: index.php?module=Contacts&action=typereports&type_customer=".$typereports."&year_select=past_year&from_date=".date('d-m-Y', strtotime($params['from_date']))."&to_date=".date('d-m-Y', strtotime($params['to_date']))."");
            exit;
        }

        $sql_update = "UPDATE contacts
                        SET assigned_user_id = '$user_id'
                        WHERE id = '$contact_id'
                        AND deleted = 0
                    ";

        $result = $db->query($sql_update);
        if($result){
            SugarApplication::appendSuccessMessage('Chỉ định khách hàng cho nhân viên thành công!');

            // Thông báo cho user user_id
            $alertData = [
				'name' 			=> 'Khách hàng: ' . $contact_name,
				// 'parent_type' 	=> 'Contacts',
				// 'parent_id' 	=> $contact_id,
				'parent_type' 	=> '',
				'parent_id' 	=> '',
				'description' 	=> 'CSKH ' . $contact_name . '. Đừng quên giữ liên lạc thường xuyên và hỗ trợ tận tình!',
				// 'url_redirect' 	=> 'index.php?module=Contacts&action=DetailView&record='.$contact_id.'',
				'url_redirect' 	=> 'index.php?module=Contacts&action=typereports&type_customer='.$typereports,
				'priority' 		=> 'low',
				'type' 			=> 'readonly',
			];
	
			$alert 		= new Alert();
			$alertId 	= $alert->autoCreateAlert('Contacts', [$user_id], $alertData);
        } else {
            SugarApplication::appendErrorMessage('Chỉ định khách hàng cho nhân viên thất bại. Vui lòng liên hệ Admin để được hỗ trợ!');
        }

        header("Location: index.php?module=Contacts&action=typereports&type_customer=".$typereports."&year_select=past_year&from_date=".date('d-m-Y', strtotime($params['from_date']))."&to_date=".date('d-m-Y', strtotime($params['to_date']))."");
        exit;
    }
    
    function assignContactForList($params){
        global $db, $current_user;
        $employee_ids   = $_POST['employee_id'] ?? [];
        $contact_ids    = $_POST['contact_id'] ?? [];
        $typereports    = htmlspecialchars($params['type_customer'] ?? '');

        // Số lượng contact_id và employee_id
        $num_contacts   = count($contact_ids);
        $num_employees  = count($employee_ids);

        if (empty($employee_ids) || empty($contact_ids) || $num_contacts === 0 || $num_employees === 0) {
            SugarApplication::appendErrorMessage('Lỗi: Thiếu thông tin Khách hàng hoặc thông tin nhân viên được giao!');
            header("Location: index.php?module=Contacts&action=typereports&type_customer=".$typereports."&year_select=past_year&from_date=".date('d-m-Y', strtotime($params['from_date']))."&to_date=".date('d-m-Y', strtotime($params['to_date']))."");
            exit;
        } else if ($num_contacts < $num_employees){
            SugarApplication::appendErrorMessage('Lỗi: Số lượng khách hàng phân bổ nhỏ hơn số lượng nhân viên chỉ định. Vui lòng kiểm tra lại!');
            header("Location: index.php?module=Contacts&action=typereports&type_customer=".$typereports."&year_select=past_year&from_date=".date('d-m-Y', strtotime($params['from_date']))."&to_date=".date('d-m-Y', strtotime($params['to_date']))."");
            exit;
        } else {
            $contacts_per_employee  = floor($num_contacts / $num_employees); // Phép chia đều
            $remaining_contacts     = $num_contacts % $num_employees; // Contacts dư sẽ gán cho nhân viên cuối
            $assignments            = array_fill(0, $num_employees, []);
    
            $contact_index = 0;
            for ($i = 0; $i < $num_employees; $i++) {
                $assigned_contacts = $contacts_per_employee + ($i < $remaining_contacts ? 1 : 0); // Nếu có dư, nhân viên nhận thêm 1 contact
                $assignments[$i] = array_slice($contact_ids, $contact_index, $assigned_contacts);
                $contact_index += $assigned_contacts;
            }
    
            $this->updateContactAssignments($assignments, $employee_ids, $typereports, $params);
        }
    }

    function updateContactAssignments($assignments, $employee_ids, $typereports, $params) {
        global $db, $current_user;


        // Chuẩn bị câu lệnh SQL để cập nhật `assigned_user_id` cho từng contact_id
        $update_sql = "UPDATE contacts SET assigned_user_id = CASE id";
    
        // Tạo câu lệnh "CASE" cho từng nhóm assignment
        $contact_ids_to_update = [];
        foreach ($assignments as $index => $assigned_contacts) {
            $employee_id = $employee_ids[$index];
            foreach ($assigned_contacts as $contact_id) {
                $update_sql .= " WHEN '$contact_id' THEN '$employee_id'";  // Gán nhân viên cho mỗi contact
                $contact_ids_to_update[] = $contact_id;  // Dành cho phần WHERE
            }
        }
    
        // Thêm điều kiện WHERE và thực hiện cập nhật
        $update_sql .= " END WHERE id IN ('" . implode("','", $contact_ids_to_update) . "') AND deleted = 0";
        
        // Thực hiện truy vấn
        if ($db->query($update_sql)) {
            SugarApplication::appendSuccessMessage('Phân giao khách hàng cho nhân viên thành công!');

            // Thông báo cho user user_id
            $alertData = [
				'name' 			=> 'Phân công CSKH '. $typereports,
				'parent_type' 	=> '',
				'parent_id' 	=> '',
				'description' 	=> 'Bạn được giao nhiệm vụ CSKH '. $typereports . '. Đừng quên giữ liên lạc thường xuyên và hỗ trợ tận tình nhé!',
				'url_redirect' 	=> 'index.php?module=Contacts&action=typereports&type_customer='.$typereports,
				'priority' 		=> 'low',
				'type' 			=> 'readonly',
			];
			$alert 		= new Alert();
			$alertId 	= $alert->autoCreateAlert('Contacts', $employee_ids, $alertData);
        } else {
            SugarApplication::appendErrorMessage('Chỉ định khách hàng cho nhân viên thất bại. Vui lòng liên hệ Admin để được hỗ trợ!');
        }
        header("Location: index.php?module=Contacts&action=typereports&type_customer=".$typereports."&year_select=past_year&from_date=".date('d-m-Y', strtotime($params['from_date']))."&to_date=".date('d-m-Y', strtotime($params['to_date']))."");
        exit;
    }
}
