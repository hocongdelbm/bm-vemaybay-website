<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/SugarView.php');

class ViewEmployee_report extends SugarView
{
    public function display()
    {
        global $current_user;

        $con_ret = $this->populateCondition();
        $smartyCont = new Sugar_Smarty();
        $this->assignFields($con_ret, $smartyCont);
        $smartyCont->display('modules/Calls/tpls/employee_report.tpl');
    }

    function populateCondition()
    {
        $currMonth     = date('n');
        $currDay       = date('d-m-Y');
        $currYear      = date('Y');
        $reportTime    = 'năm ' . date('Y');

        if (!empty($_REQUEST['report_year'])) {
            $reportYear = $_REQUEST['report_year'];
        } else {
            $reportYear = $currYear;
        }

        if (!empty($_REQUEST['from_date']) && strtotime($_REQUEST['from_date']) !== false) {
            $from_date = date('d-m-Y', strtotime($_REQUEST['from_date']));
        } else {
            $from_date = date('01-m-Y', strtotime($currDay));
            $_REQUEST['from_date'] = $from_date;
        }

        if (!empty($_REQUEST['to_date']) && strtotime($_REQUEST['to_date']) !== false) {
            $to_date = date('d-m-Y', strtotime($_REQUEST['to_date']));
        } else {
            $to_date = date('t-m-Y', strtotime($currDay));
            $_REQUEST['to_date'] =  $to_date;
        }

        $month_select = isset($_REQUEST['month_select']) ? $_REQUEST['month_select'] : '';

        return [
            'curr_month'   => $currMonth,
            'curr_year'    => $currYear,
            'report_year'  => $reportYear,
            'report_time'  => $reportTime,
            'from_date'    => $from_date,
            'to_date'      => $to_date,
            'month_select' => $month_select,
        ];
    }

    function assignFields($con, $smarty)
    {
        global $current_user;

        $time_opt = $this->populateReportMonthSearch($con);
        $smarty->assign('MONTH_SELECT', $time_opt['html']);
        $smarty->assign('REPORT_TIME', $time_opt['report_time']);
        $smarty->assign('MODULE_NAME', $this->bean->module_dir);
        $smarty->assign('FROM_DATE', date("Y-m-d", strtotime($con['from_date'])));
        $smarty->assign('TO_DATE', date("Y-m-d", strtotime($con['to_date'])));

        $data = $this->getEmployeeCallsData($con);

        $smarty->assign('REPORT_EMPLOYEE', $this->genHtmlEmployeeItem($data, $con));

        if ($current_user->user_name == 'hungnh') {
            // pr($data);
        }
    }

    function genHtmlEmployeeItem($data = [], $params = [])
    {
        global $current_user;

        $html = '';

        // if ($current_user->user_name == 'hungnh') {
        //     pr($params);
        //     pr($data);
        // }

        // Phần tab
        $html .= '<ul class="nav nav-pills mb-3 gap-2 border-bottom rounded p-2" id="pills-tab" role="tablist">';
        $index = 0;
        foreach ($data as $title => $employees) {
            $isActive = $index === 0 ? 'active' : '';

            $html .= '<li class="nav-item" role="presentation">
                                <button class="nav-link ' . $isActive . '" id="pills-' . strtolower($title) . '-tab" data-bs-toggle="pill" data-bs-target="#pills-' . strtolower($title) . '" type="button" role="tab" aria-controls="pills-' . strtolower($title) . '" aria-selected="true">' . $title . '</button>
                            </li>';
            $index++;
        }
        $html .= '</ul>';

        // Phần content
        $html .= '<div class="tab-content" id="pills-tabContent">';
        $index_content = 0;
        foreach ($data as $title => $employees) {
            $isActive_content = $index_content === 0 ? 'show active' : '';

            $html .= '<div class="tab-pane fade ' . $isActive_content . '" id="pills-' . strtolower($title) . '" role="tabpanel" aria-labelledby="pills-' . strtolower($title) . '-tab" tabindex="0">
                                <div class="flex-start flex-wrap">';

            foreach ($employees as $id => $employee) {
                $html .= '<div class="card-emp p-2">
                                                <div class="flex-between gap-2">
                                                    <div class="tools">
                                                        <div class="circle">
                                                            <span class="red box"></span>
                                                        </div>
                                                        <div class="circle">
                                                            <span class="yellow box"></span>
                                                        </div>
                                                        <div class="circle">
                                                            <span class="green box"></span>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown">
                                                        <a class="cursor-pointer text-white" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item cursor-pointer view-detail-emp" data-bs-toggle="modal" data-bs-target="#modalViewDetail" data-type="view-statistics" data-employee-id="' . trim($id) . '">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-activity me-2" viewBox="0 0 16 16">
                                                                        <path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2"/>
                                                                    </svg>
                                                                    <span>Thống kê nhân viên</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="dropdown-item cursor-pointer view-detail-emp" data-bs-toggle="modal" data-bs-target="#modalViewDetail" data-type="view-list" data-employee-id="' . trim($id) . '">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-list-ul me-2" viewBox="0 0 16 16">
                                                                        <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                                                    </svg>
                                                                    <span>Danh sách cuộc gọi</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="card__content">
                                                    <div class="card-emp-icon">
                                                        <span class="emp-avatar bg-label-secondary rounded-circle">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1z"></path></svg>
                                                        </span>
                                                        <span class="fs-6 text-white fw-semibold">' . $employee['full_name'] . '</span>
                                                    </div>
                                                    <p class="text-white mb-2">Tổng cuộc gọi: <b>' . format_number($employee['total_calls']) . '</b></p>
                                                    <p class="text-nowrap text-white">Gọi đi: <b>' . format_number($employee['outbound']) . '</b> - Gọi đến: <b>' . format_number($employee['inbound']) . '</b> - Nhá máy: <b>' . format_number($employee['suddenly']) . '</b> - Spam: <b>' . format_number($employee['spam']) . '</b> - Nội bộ: <b>' . format_number($employee['internal']) . '</b></p>
                                                </div>
                                            </div>';
            }

            $html .= '</div>
                            </div>';
            $index_content++;
        }
        $html .= '</div>';

        return $html;
    }

    function getEmployeeCallsData($params = [])
    {
        global $db, $current_user;

        $from_date  = date('Y-m-d', strtotime($params['from_date']));
        $to_date    = date('Y-m-d', strtotime($params['to_date']));
        $result     = [];

        $sql_search = "";
        if (!isAllowedUser()) {
            $sql_search .= " AND c.assigned_user_id='" . $current_user->id . "' ";
        }

        $sql = "SELECT 
                    temp.assigned_user_id,
                    CONCAT(IFNULL(u.last_name, ''), ' ', IFNULL(u.first_name, '')) AS full_name,
                    u.title,
                    temp.direction,
                    temp.total_calls
                FROM (
                    SELECT 
                        c.assigned_user_id,
                        c.direction,
                        COUNT(c.id) AS total_calls
                    FROM calls c
                    WHERE c.deleted = 0
                        AND c.date_entered BETWEEN '{$from_date}' AND '{$to_date}' 
                        AND c.direction != 'missed'
                        ".$sql_search."
                    GROUP BY c.assigned_user_id, c.direction
                ) temp
                INNER JOIN users u ON temp.assigned_user_id = u.id
                ORDER BY temp.total_calls DESC;
            ";

        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $userId     = $row['assigned_user_id'];
            $direction  = $row['direction'];
            $title      = $row['title'];
            $totalCalls = (int)$row['total_calls'];

            // Khởi tạo mảng theo title
            if (!isset($result[$title])) {
                $result[$title] = [];
            }

            // Khởi tạo userId nếu chưa tồn tại
            if (!isset($result[$title][$userId])) {
                $result[$title][$userId] = [
                    'full_name' => $row['full_name'],
                    'outbound' => 0,
                    'inbound' => 0,
                    'suddenly' => 0,
                    'spam' => 0,
                    'internal' => 0,
                    'total_calls' => 0
                ];
            }

            if (isset($result[$title][$userId][$direction])) {
                $result[$title][$userId][$direction] += $totalCalls;
            } else {
                $result[$title][$userId][$direction] = $totalCalls;
            }
            $result[$title][$userId]['total_calls'] += $totalCalls;
        }

        return $result;
    }

    function populateReportMonthSearch($params)
    {
        $quarter_ret = $this->populateTimeReturn('quarter');
        $month_ret = $this->populateTimeReturn('month');
        $opt_arr = [
            [
                'name' => 'Năm nay',
                'from_date' => date('Y-01-01'),
                'to_date' => date('Y-12-31'),
                'report_name' => 'năm ' . date('Y'),
            ],
        ];
        $opt_arr = array_merge($opt_arr, $quarter_ret);
        $opt_arr = array_merge($opt_arr, $month_ret);

        $html = '';
        $report_time = $opt_arr[0]['report_name'];
        foreach ($opt_arr as $opt_idx => $opt_item) {
            $selected = '';
            if ($params['month_select'] == $opt_idx && $params['month_select'] != '') {
                $selected = 'selected';
                $report_time = $opt_arr[$opt_idx]['report_name'];
            } else if (strtotime($opt_item['from_date']) == strtotime($params['from_date']) && strtotime($opt_item['to_date']) == strtotime($params['to_date'])) {
                $selected = 'selected';
                $report_time = $opt_arr[$opt_idx]['report_name'];
            }
            $html .= '<option value="' . $opt_idx . '" ' . $selected . ' data-from-date="' . $opt_item['from_date'] . '" data-to-date="' . $opt_item['to_date'] . '">' . $opt_item['name'] . '</option>';
        }
        return [
            'html' => $html,
            'report_time' => $report_time,
        ];
    }

    function populateTimeReturn($time_opt)
    {
        $ret = [];
        // trả về 4 quý
        if ($time_opt == 'quarter') {
            for ($i = 1; $i <= 10; $i += 3) {
                $from_date = date('Y-' . str_pad($i, 2, 0, STR_PAD_LEFT) . '-01');
                $to_date = date('Y-' . str_pad(($i + 2), 2, 0, STR_PAD_LEFT) . '-01');
                $quarter = ceil($i / 3);
                $ret[$quarter - 1]['from_date'] = $from_date;
                $ret[$quarter - 1]['to_date'] = $to_date;
                $ret[$quarter - 1]['name'] = 'Quý ' . $quarter;
                $ret[$quarter - 1]['report_name'] = 'quý ' . $quarter . '/' . date('Y', strtotime($from_date));
            }
        }
        // trả về 12 tháng
        else if ($time_opt == 'month') {
            for ($i = 1; $i <= 12; $i++) {
                $from_date = date('Y-' . str_pad($i, 2, 0, STR_PAD_LEFT) . '-01');
                $to_date = date('Y-m-t', strtotime($from_date));
                $ret[$i - 1]['from_date'] = $from_date;
                $ret[$i - 1]['to_date'] = $to_date;
                $ret[$i - 1]['name'] = 'Tháng ' . $i;
                $ret[$i - 1]['report_name'] = 'tháng ' . $i . '/' . date('Y', strtotime($from_date));
            }
        }

        return $ret;
    }
}
