<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/SugarView.php');

class Viewtelesales extends SugarView
{
    public function display()
    {
        global $current_user;

        $smartyCont = new Sugar_Smarty();
        $this->assignFields($smartyCont);
        $smartyCont->display('modules/Contacts/tpls/telesales.tpl');
    }

    function assignFields($smarty)
    {
        global $current_user;
        $params = [];

        // if(isset($_POST)){
        //     pr($_POST);
        // }

        $smarty->assign('MODULE_NAME', $this->bean->module_dir);

        // ROLE
        $smarty->assign('IS_ADMIN', (isAllowedUser()));
        $smarty->assign('OWNER', (!isAllowedUser() ? 1 : 0));
  
        if (!empty($_POST['from_date'])) {
            $from_date = $_POST['from_date'];
        } else $from_date = date('d-m-Y');
        if (!empty($_POST['to_date'])) {
            $to_date = $_POST['to_date'];
        } else $to_date = date('d-m-Y');
        $smarty->assign('FROM_DATE', $from_date);
        $smarty->assign('TO_DATE', $to_date);

        $checked_uncompleted_bk = '';
        if (isset($_POST['uncompleted_booking'])) {
            $params['uncompleted_booking'] = $_POST['uncompleted_booking'];
            $checked_uncompleted_bk = 'checked';
        } 
        $smarty->assign('checked_uncompleted_bk', $checked_uncompleted_bk);

        $dateSelect = $_REQUEST['date_select'] ?? '';
        $smarty->assign('DATE_OPTION', $this->getDateOptions($dateSelect));

        // GET LIST CONTACTS
        $list_contacts = $this->getListContacts($from_date, $to_date, $params);
        $smarty->assign('LIST_CONTACTS', $list_contacts);

        // EMPLOYEE SELECT
        $employee_select = $this->getEmployeeSelect();
        $smarty->assign('EMLOYEE_SELECT', $employee_select);

        // ASSIGN CONTACTS
        if(isset($_POST['btnSaveAssignContact'])){
            $this->assignContactForEmployee();
        }
    }

    function assignContactForEmployee(){
        $employee_ids   = $_POST['employee_id'] ?? [];
        $contact_ids    = $_POST['contact_id'] ?? [];

        // Số lượng contact_id và employee_id
        $num_contacts   = count($contact_ids);
        $num_employees  = count($employee_ids);

        if (empty($employee_ids) || empty($contact_ids) || $num_contacts === 0 || $num_employees === 0) {
            SugarApplication::appendErrorMessage('Lỗi: Thiếu thông tin Khách hàng hoặc thông tin nhân viên được giao!');
            header("Location: index.php?module=Contacts&action=telesales");
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
    
            $this->updateContactAssignments($assignments, $employee_ids);
        }
    }

    function updateContactAssignments($assignments, $employee_ids) {
        global $db;
    
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
    
        // pr($update_sql);

        // Thực hiện truy vấn
        if ($db->query($update_sql)) {
            SugarApplication::appendSuccessMessage('Phân giao khách hàng cho nhân viên thành công!');
        } else {
            SugarApplication::appendErrorMessage('Chỉ định khách hàng cho nhân viên thất bại. Vui lòng liên hệ Admin để được hỗ trợ!');
        }

        header("Location: index.php?module=Contacts&action=telesales");
        exit;
    }

    /**
     * select nhân viên giao cho
     *
     * @return string HTML của các tùy chọn nhân viên
     */
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

		$html = '<link type="text/css" rel="stylesheet" href="./themes/SuiteP/libs/css/select2.min.css">';
        $html .= '
            <div class="flex-start">
                <label for="employee-select" class="form-label m-0">Nhân viên: </label>
                <select class="box-select" id="employee-select" name="employee_id[]" multiple><option value="">-- Trống --</option>'.get_select_options_with_id($employee_list, '').'</select>
            </div>
        ';

        return $html;
    }

    /**
     * Danh sách khách hàng theo khoảng time
     *
     * @param string $from_date: Từ ngày
     * @param string $to_date: Đến ngyaf
     * @return string HTML danh sách contacts
     */
    private function getListContacts($from_date, $to_date, $params){
        global $db, $current_user;

        // List contact có booking chưa hoàn tất
        $sql_uncompleted_booking = '';
        if(isset($_POST['uncompleted_booking']) && !empty($params['uncompleted_booking'])) {
            $sql_uncompleted_booking .= " AND bk.booking_status NOT IN ('4', '8')";
        }

        $from_date_sql = date('Y-m-d', strtotime($from_date));
        $to_date_sql = date('Y-m-d', strtotime($to_date)) . " 23:59:59";

        $sql_search = '';
        if (!isAllowedUser()) {
			$sql_search .= " AND c.assigned_user_id='" . $current_user->id . "' ";
		}

        $sql = "
            SELECT
                c.id as contact_id,
                c.last_name as contact_name,
                c.phone_mobile as contact_phone,
                c.description as contact_description,
                c.assigned_user_id as assigned_user_id,
                c.date_entered as date_entered
            FROM ec_flight_bookings bk
            LEFT JOIN contacts c ON bk.contact_id = c.id AND c.deleted = 0
            WHERE 
                bk.deleted = 0
                $sql_uncompleted_booking
                $sql_search
                AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '$from_date_sql' AND '$to_date_sql'
            GROUP BY bk.contact_id  
            ORDER BY bk.date_entered DESC
        ";

        // pr($sql);

        $res = $db->query($sql);
        $row_count = $db->countRows($res);
        $user_list = get_user_array(true, '', '', true);
        $html = '';
        $i = 0;
        if($row_count > 0){
            while ($row = $db->fetchByAssoc($res)) {
                $html .= '<tr class="contact-line">
                            <td class="text-center fw-bold">
                                <input type="checkbox" name="contact_id[]" value="' . $row['contact_id'] . '" />
                            </td>
                            <td class="text-center fw-bold">' . ($i + 1) . '</td>
                            <td class="text-start fw-bold"><a target="_blank" href="index.php?module=Contacts&action=DetailView&record=' . $row['contact_id'] . '">'.$row['contact_name'].'</a></td>
                            <td class="text-center fw-bold">'.$row['contact_phone'].'</td>
                            <td class="text-start fw-bold">'.$row['contact_description'].'</td>
                            <td class="text-start fw-bold">' . $user_list[$row['assigned_user_id']] . '</td>
                            <!-- <td class="text-center fw-bold">'.date('d-m-Y H:i:s', strtotime('+7 hour', strtotime($row['date_entered']))).'</td> -->
                            <td class="text-center fw-bold">
                                <div class="dropdown text-dark position-static">
                                    <a class="cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item cursor-pointer text-decoration-none fw-medium view-detail-contact" data-bs-toggle="modal" data-bs-target="#modalViewDetail" data-contact-id="' . $row['contact_id'] . '">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-list-ul me-2" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"></path>
                                                </svg>
                                                <span>Danh sách booking</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item cursor-pointer text-decoration-none fw-medium view-activity-contact" data-bs-toggle="modal" data-bs-target="#modalViewHistoryActivity" data-phone="' . $row['contact_phone'] . '">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-activity me-2" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2"></path>
                                                </svg>
                                                <span>Lịch sử tương tác</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>';
                $i++;
            }
        }
            
        return $html;
    }
    /**
     * Tạo danh sách DATE_OPTION cho dropdown
     *
     * @param string $dateSelect Giá trị đang được chọn
     * @return string HTML của các tùy chọn
     */
    private function getDateOptions($dateSelect)
    {
        $currentYear = date('Y');
        $previousYear = $currentYear - 1;

        $dateOptions = [
            [
                'value' => 'today',
                'fromdate' => date('d-m-Y'),
                'todate' => date('d-m-Y'),
                'label' => 'Hôm nay',
                'selected' => $dateSelect === 'today'
            ],
            [
                'value' => 'this_year',
                'fromdate' => "01-01-$currentYear",
                'todate' => "31-12-$currentYear",
                'label' => 'Năm nay',
                'selected' => $dateSelect === 'this_year'
            ],
            [
                'value' => 'previous_year',
                'fromdate' => "01-01-$previousYear",
                'todate' => "31-12-$previousYear",
                'label' => 'Năm trước',
                'selected' => $dateSelect === 'previous_year'
            ]
        ];

        return implode('', array_map(function ($option) {
            return '<option value="' . $option['value'] . '" fromdate="' . $option['fromdate'] . '" todate="' . $option['todate'] . '"'
                . ($option['selected'] ? ' selected' : '') . '>' . $option['label'] . '</option>';
        }, $dateOptions));
    }
}
