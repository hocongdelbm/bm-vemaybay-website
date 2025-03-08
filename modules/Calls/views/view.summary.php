<?php
require_once("include/Sugar_Smarty.php");

class Viewsummary extends SugarView
{
     function display()
     {
          if (ACLController::checkAccess('Calls', 'list', true)) {
               $smartyCont = new Sugar_Smarty();
               $con_ret = $this->populateContent();

               $this->assignFields($con_ret, $smartyCont);
               $smartyCont->display('modules/Calls/tpls/summary.tpl');
          }
     }

     function populateContent()
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

          $smarty->assign('MODULE_NAME', $this->bean->module_dir);
          $smarty->assign('FROM_DATE', $con['from_date']);
          $smarty->assign('TO_DATE', $con['to_date']);
          $smarty->assign('REPORT_YEAR', $con['report_year']);
          $smarty->assign('REPORT_TIME', $con['report_time']);

          $month_select = $this->generateDateOptions($con);
          $smarty->assign('MONTH_SELECT', $month_select);

          // chart mặc định
          $smarty->assign('CHART_TYPE', 'line');
          $chart_data = $this->populateChartData($con);
          $smarty->assign('CHART_YAXIS', $chart_data['data_y']);
          $smarty->assign('CHART_XAXIS', $chart_data['data_x']);
          $smarty->assign('CHART_TYPE', $chart_data['type']);

          // REPORT EMPLOYEE
          $smarty->assign('EMPLOYEE_MONTH', $this->populateEmpMonthlyCallsReport($con));

          // LOẠI CUỘC GỌI
          $data_type_call = $this->callTypeDirectionTotals($con);
          $smarty->assign('DATA_DIRECTION', $this->reportDirectionData($data_type_call));

          $smarty->assign('TOTAL_INBOUND', format_number($data_type_call['inbound']));
          $smarty->assign('TOTAL_OUTBOUND', format_number($data_type_call['outbound']));
          $smarty->assign('TOTAL_MISSED', format_number($data_type_call['failed']));
          $smarty->assign('TOTAL_INTERNAL', format_number($data_type_call['internal']));

          // CDR Statistics
          $calls = BeanFactory::getBean('Calls');
          $cdr_stats = $calls->getCDRStatistics();

          $smarty->assign('DATA_CDR_TOTAL', json_encode($cdr_stats['total']));
          $smarty->assign('DATA_CDR_FAILED', json_encode($cdr_stats['failed']));
          $smarty->assign('DATA_CDR_ANSWERED', json_encode($cdr_stats['answered']));
          $smarty->assign('DATA_CDR_MINUTES', json_encode($cdr_stats['minutes']));
          $smarty->assign('DATA_CDR_CPM', json_encode($cdr_stats['call_per_min']));
          $smarty->assign('DATA_CDR_ASR', json_encode($cdr_stats['asr']));
          $smarty->assign('DATA_CDR_ALOC', json_encode($cdr_stats['aloc']));
          $smarty->assign('CDR_STATS_TABLE', $cdr_stats['data'] ? $this->renderDataTableCdrStats($cdr_stats['data']) : []);
     }

     function reportDirectionData($data)
     {
          $data = [
               array(
                    'label' => 'Cuộc gọi đi',
                    'data' => format_number($data['outbound']),
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16.712 13.288a.999.999 0 0 0-1.414 0l-1.594 1.594c-.739-.22-2.118-.72-2.992-1.594s-1.374-2.253-1.594-2.992l1.594-1.594a.999.999 0 0 0 0-1.414l-4-4a.999.999 0 0 0-1.414 0L2.586 6c-.38.38-.594.902-.586 1.435.023 1.424.4 6.37 4.298 10.268S15.142 21.977 16.566 22h.028c.528 0 1.027-.208 1.405-.586l2.712-2.712a.999.999 0 0 0 0-1.414l-3.999-4zM16.585 20c-1.248-.021-5.518-.356-8.873-3.712C4.346 12.922 4.02 8.637 4 7.414l2.005-2.005 2.586 2.586-1.293 1.293a1 1 0 0 0-.272.912c.024.115.611 2.842 2.271 4.502s4.387 2.247 4.502 2.271a.993.993 0 0 0 .912-.271l1.293-1.293 2.586 2.586L16.585 20z"></path><path d="m16.795 5.791-4.497 4.497 1.414 1.414 4.497-4.497L21.005 10V2.995H14z"></path></svg>',
                    'link' => '',
                    'class' => 'primary',
               ),
               array(
                    'label' => 'Cuộc gọi đến',
                    'data' => format_number($data['inbound']),
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16.712 13.288a.999.999 0 0 0-1.414 0l-1.597 1.596c-.824-.245-2.166-.771-2.99-1.596-.874-.874-1.374-2.253-1.594-2.992l1.594-1.594a.999.999 0 0 0 0-1.414l-4-4a1.03 1.03 0 0 0-1.414 0l-2.709 2.71c-.382.38-.597.904-.588 1.437.022 1.423.396 6.367 4.297 10.268C10.195 21.6 15.142 21.977 16.566 22h.028c.528 0 1.027-.208 1.405-.586l2.712-2.712a.999.999 0 0 0 0-1.414l-3.999-4zM16.585 20c-1.248-.021-5.518-.356-8.874-3.712C4.343 12.92 4.019 8.636 4 7.414l2.004-2.005L8.59 7.995 7.297 9.288c-.238.238-.34.582-.271.912.024.115.611 2.842 2.271 4.502s4.387 2.247 4.502 2.271a.994.994 0 0 0 .912-.271l1.293-1.293 2.586 2.586L16.585 20z"></path><path d="M15.795 6.791 13.005 4v6.995H20l-2.791-2.79 4.503-4.503-1.414-1.414z"></path></svg>',
                    'link' => '',
                    'class' => 'success',
               ),
               array(
                    'label' => 'Cuộc gọi nhỡ',
                    'data' => format_number($data['missed']),
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10.09 12.5a8.92 8.92 0 0 1-1-2.2l1.59-1.59a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-1.41 0L2.59 6A2 2 0 0 0 2 7.44 15.44 15.44 0 0 0 5.62 17L2.3 20.29l1.41 1.42 18-18-1.41-1.42zM7 15.55a13.36 13.36 0 0 1-3-8.13l2-2L8.59 8 7.3 9.29a1 1 0 0 0-.27.92 11 11 0 0 0 1.62 3.73zm9.71-2.26a1 1 0 0 0-1.41 0l-1.6 1.6-.34-.12-1.56 1.55a12.06 12.06 0 0 0 2 .66 1 1 0 0 0 .91-.27l1.3-1.3L18.59 18l-2 2A13.61 13.61 0 0 1 10 18.1l-1.43 1.45a15.63 15.63 0 0 0 8 2.45 2 2 0 0 0 1.43-.58l2.71-2.71a1 1 0 0 0 0-1.42z"></path></svg>',
                    'link' => '',
                    'class' => 'danger',
               ),
               array(
                    'label' => 'Cuộc gọi nội bộ',
                    'data' => format_number($data['internal']),
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-mic" viewBox="0 0 16 16"><path d="M3.5 6.5A.5.5 0 0 1 4 7v1a4 4 0 0 0 8 0V7a.5.5 0 0 1 1 0v1a5 5 0 0 1-4.5 4.975V15h3a.5.5 0 0 1 0 1h-7a.5.5 0 0 1 0-1h3v-2.025A5 5 0 0 1 3 8V7a.5.5 0 0 1 .5-.5"/><path d="M10 8a2 2 0 1 1-4 0V3a2 2 0 1 1 4 0zM8 0a3 3 0 0 0-3 3v5a3 3 0 0 0 6 0V3a3 3 0 0 0-3-3"/></svg>',
                    'link' => '',
                    'class' => 'dark',
               ),
          ];
          return $data;
     }
     function generateDateOptions($params)
     {
          $quarter_ret   = $this->populateTimeReturn('quarter');
          $month_ret     = $this->populateTimeReturn('month');

          $opt_arr = [
               [
                    'name' => 'Năm nay',
                    'from_date' => date('Y-01-01'),
                    'to_date' => date('Y-12-31'),
               ],
          ];
          $opt_arr = array_merge($opt_arr, $quarter_ret);
          $opt_arr = array_merge($opt_arr, $month_ret);

          $html = '';
          foreach ($opt_arr as $opt_idx => $opt_item) {
               $selected = '';
               if ($params['month_select'] == $opt_idx && $params['month_select'] != '') {
                    $selected = 'selected';
               } else if (strtotime($opt_item['from_date']) == strtotime($params['from_date']) && strtotime($opt_item['to_date']) == strtotime($params['to_date'])) {
                    $selected = 'selected';
               }
               $html .= '<option value="' . $opt_idx . '" ' . $selected . ' data-from-date="' . $opt_item['from_date'] . '" data-to-date="' . $opt_item['to_date'] . '">' . $opt_item['name'] . '</option>';
          }
          return $html;
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
               }
          }

          return $ret;
     }

     function changeDateInDataCon($init_con, $from_date, $to_date)
     {
          $con = $init_con;
          $con['from_date'] = $from_date;
          $con['to_date'] = $to_date;
          return $con;
     }

     function populateChartData($con = [])
     {
          $ret = [];
          $y_data = '';
          $x_data = '';
          $chart_type = 'line';

          // Tìm theo tháng 
          if (date('n', strtotime($con['from_date'])) == date('n', strtotime($con['to_date']))) {
               $last_date     = date('t', strtotime($con['to_date']));
               $month_year    = date('n-Y', strtotime($con['from_date']));

               /**
                * Trục Y: tổng số lượng cuộc gọi theo ngày
                * Trục X: Tất cả các ngày trong tháng
                */
               for ($d = 1; $d <= $last_date; $d++) {
                    if (!empty($x_data)) {
                         $x_data .= ',';
                    }
                    $x_data .= $d;

                    // Nếu là tháng hiện tại, trục x chỉ lấy tới ngày hiện tại
                    if (($month_year == date('n-Y') && $d <= date('j')) || $month_year != date('n-Y')) {
                         $date = date('Y-m-d', strtotime($d . '-' . $month_year));
                         $data = $this->getTotalCalls($this->changeDateInDataCon($con, $date, $date));
                         if (!empty($y_data)) {
                              $y_data .= '|';
                         }
                         $y_data .= (int)$data['call_cnt'];
                    }
               }
          }
          // Tìm theo quý
          else if ($con['month_select'] >= 1 && $con['month_select'] <= 4) {
               $chart_type = 'bar';
               $start_month   = date('n', strtotime($con['from_date']));
               $end_month     = date('n', strtotime($con['to_date']));

               /**
                * Trục X: Tất cả các tháng trong quý
                * Trục Y: Tổng số lượng cuộc gọi theo tháng
                */
               for ($m = $start_month; $m <= $end_month; $m++) {
                    if (!empty($x_data)) {
                         $x_data .= ',';
                    }
                    $x_data .= 'Tháng ' . $m;

                    if ($m <= $con['curr_month'] && date('Y', strtotime($con['from_date']) <= $con['curr_year'])) {
                         $from_date     = date('Y-' . str_pad($m, 2, 0, STR_PAD_LEFT) . '-01');
                         $to_date       = date('Y-m-t', strtotime($from_date));

                         $data = $this->getTotalCalls($this->changeDateInDataCon($con, $from_date, $to_date));
                         if (!empty($y_data)) {
                              $y_data .= '|';
                         }
                         $y_data .= (int)$data['call_cnt'];
                    }
               }
          }
          // Tìm theo năm
          else if ($con['month_select'] == '0') {
               /**
                * Trục X: Tất cả các tháng trong năm
                * Trục Y: Tổng số lượng cuộc gọi theo tháng
                */
               for ($m = 1; $m <= 12; $m++) {
                    if (!empty($x_data)) {
                         $x_data .= ',';
                    }
                    $x_data .= 'Tháng ' . $m;

                    $from_date     = date('Y-' . str_pad($m, 2, 0, STR_PAD_LEFT) . '-01');
                    $to_date       = date('Y-m-t', strtotime($from_date));

                    if ($m <= $con['curr_month'] && date('Y', strtotime($con['from_date']) <= $con['curr_year'])) {
                         $data = $this->getTotalCalls($this->changeDateInDataCon($con, $from_date, $to_date));
                         if (!empty($y_data)) {
                              $y_data .= '|';
                         }
                         $y_data .= (int)$data['call_cnt'];
                    }
               }
          }

          $ret['data_y'] = $y_data;
          $ret['data_x'] = $x_data;
          $ret['type']   = $chart_type;
          return $ret;
     }

     /**
      * Tổng số lượng cuộc gọi 
      * @param mixed $params: Khoảng thời gian xem báo báo
      * @return int 
      */
     function getTotalCalls($params = [])
     {
          global $db, $current_user;
          $ret = [
               'call_cnt' => 0,
          ];

          $sql_search = "";
          if (!isAllowedUser()) {
               $sql_search .= " AND assigned_user_id='" . $current_user->id . "' ";
          }

          $sql = '
               SELECT COUNT(id) as call_cnt 
               FROM calls
               WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d', strtotime($params['from_date'])) . '"
               AND DATE_ADD(date_entered, INTERVAL 7 HOUR) <= "' . date('Y-m-d', strtotime($params['to_date'])) . ' 23:59:59"
              ' . $sql_search . '
               AND deleted = 0';

          $res = $db->query($sql);
          if ($db->getRowCount($res) > 0) {
               $row = $db->fetchByAssoc($res);
               $ret['call_cnt'] = (int)$row['call_cnt'];
          }

          return $ret;
     }

     /**
      * Báo cáo tổng quan tổng số cuộc gọi theo nhân viên
      * @param mixed $params: Khoảng thời gian xem báo báo
      * @return html 
      */
     function populateEmpMonthlyCallsReport($params = [])
     {
          $data = $this->getEmployeeMonthlyCalls($params);
          $html = '';

          if (is_array($data) && count($data) > 0) {
               foreach ($data as $user_id => $user_value) {
                    $html .= '<li class="month-emp-row flex-fill w-100 flex-between p-2">
                                   <div class="flex-start">
                                        <div class="month-emp-avatar">
                                             <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="22" height="22" viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1z"></path></svg>
                                        </div>
                                        <div class="month-emp-name">
                                             <div class="month-emp-fullname fw-bold"><a target="_blank" href="index.php?module=Users&action=DetailView&record=' . $user_id . '">' . $user_value['full_name'] . '</a></div>
                                        </div>
                                   </div>
                                   <div class="month-emp-qty fw-bold">' . format_number($user_value['quantity']) . '</div>
                              </li>';
               }
          } else {
               $html .= '<p>Chưa có dữ liệu!</p>';
          }

          return $html;
     }

     /**
      * Lấy tổng số cuộc gọi theo nhân viên
      * @param mixed $params: Khoảng thời gian xem báo báo
      * @return int: Tổng số lượng cuộc gọi theo nhân viên
      */
     function getEmployeeMonthlyCalls($params = [])
     {
          global $db, $current_user;

          $from_date     = date('Y-m-d', strtotime($params['from_date']));
          $to_date       = date('Y-m-d', strtotime($params['to_date']));
          $ret = [];

          $sql_search = "";
          if (!isAllowedUser()) {
               $sql_search .= " AND c.assigned_user_id='" . $current_user->id . "' ";
          }

          $sql = 'SELECT 
                    COUNT(c.id) as quantity, 
                    c.assigned_user_id as user_id, 
                    CONCAT(IFNULL(u.last_name, "Khác"),IF(u.first_name IS NOT NULL," ",""),IFNULL(u.first_name,"")) AS full_name
               FROM calls c
               LEFT JOIN users u ON u.id = c.assigned_user_id
               WHERE DATE_ADD(c.date_entered, INTERVAL 7 HOUR) >= "' . $from_date . '"
                    AND DATE_ADD(c.date_entered, INTERVAL 7 HOUR) <= "' . $to_date . ' 23:59:59"
                    AND c.direction NOT IN ("missed")
                    AND c.assigned_user_id IS NOT NULL 
                    AND c.assigned_user_id != ""
                    AND c.deleted = 0
                    ' . $sql_search . '
               GROUP BY c.assigned_user_id
               ORDER BY quantity DESC';

          $res = $db->query($sql);
          while ($row = $db->fetchByAssoc($res)) {
               $ret[$row['user_id']] = [
                    'full_name' => $row['full_name'],
                    'quantity' => (int)$row['quantity'],
               ];
          }

          return $ret;
     }

     /**
      * Lấy tổng số lượng cuộc gọi theo loại - direction
      * @param mixed $params: Khoảng thời gian xem báo báo
      * @return int: Tổng số lượng cuộc gọi theo từng loại
      */
     function callTypeDirectionTotals($params)
     {
          global $db, $current_user;

          $sql_search = "";
          if (!isAllowedUser()) {
               $sql_search .= " AND assigned_user_id='" . $current_user->id . "' AND direction NOT IN ('missed')";
          }

          $inbound = $outbound = $missed = $internal = 0;

          $sql_dir = '
               SELECT COUNT(id) as quantity, direction 
               FROM calls
               WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d', strtotime($params['from_date'])) . '"
               AND DATE_ADD(date_entered, INTERVAL 7 HOUR) <= "' . date('Y-m-d', strtotime($params['to_date'])) . ' 23:59:59"
              ' . $sql_search . '
               AND deleted = 0
               GROUP BY direction';

          $res_dir = $db->query($sql_dir);

          while ($row = $db->fetchByAssoc($res_dir)) {
               switch ($row['direction']) {
                    case 'inbound':
                         $inbound = $row['quantity'];
                         break;
                    case 'outbound':
                         $outbound = $row['quantity'];
                         break;
                    case 'missed':
                         $missed = $row['quantity'];
                         break;
                    case 'internal':
                         $internal = $row['quantity'];
                         break;
                    default:
                         break;
               }
          }

          return [
               'inbound' => $inbound,
               'outbound' => $outbound,
               'missed' => $missed,
               'internal' => $internal,
          ];
     }

     function renderDataTableCdrStats($rows)
     {
          $table = '<table class="table table-hover table-striped mt-5">';
          $table .= '<thead class="table-dark">
                         <tr>
                              <th style="border-top-left-radius: 10px;">Hours</th>
                              <th>Date</th> 
                              <th>Time</th>
                              <th>Total</th>
                              <th>Failed</th>
                              <th>Answered</th>
                              <th>Minutes</th>
                              <th>Calls Per Min</th>   
                              <th>ASR</th>
                              <th style="border-top-right-radius: 10px;">ALOC</th>
                         </tr>
                    </thead>';

          $hours = 23;
          foreach ($rows as $index => $row) {
               $table .= '<tr>';
               if ($index <= $hours) {
                    $table .= '<td><strong>' . $row['hours'] . '</strong></td>';
               } 
               else if ($index == $hours + 1) {
                    $table .= '
                              <tr>
                              <td colspan="9">
                                        <br>
                                   </td>
                              </tr>
                              <thead class="table-dark">
                                   <tr>
                                        <th style="border-top-left-radius: 10px;">Days</th>
                                        <th>Date</th> 
                                        <th>Time</th>
                                        <th>Total</th>
                                        <th>Failed</th>
                                        <th>Answered</th>
                                        <th>Minutes</th>
                                        <th>Calls Per Min</th>
                                        <th>ASR</th>
                                        <th style="border-top-right-radius: 10px;">ALOC</th>
                                   </tr>
                              </thead>';
               }

               if ($index > $hours) {
                    $table .= '<td><strong>' . floor(trim($row['s_hour']) / 24) . '</strong></td>';
               }

               $table .= '<td>' . trim($row['date']) . '</td>';
               $table .= '<td>' . trim($row['time']) . '</td>';
               $table .= '<td>' . trim($row['total']) . '</td>';
               $table .= '<td>' . trim($row['failed']) . '</td>';
               $table .= '<td>' . trim($row['answered']) . '</td>';
               $table .= '<td>' . trim(round($row['minutes'] ?? 0, 2)) . '</td>';
               $table .= '<td>' . trim(round($row['calls_per_minute'] ?? 0, 2)) . ' / ' . trim(round($row['cpm_answered'] ?? 0, 2)) . '</td>';
               $table .= '<td>' . trim(round($row['asr'] ?? 0, 2)) . '%</td>';
               $table .= '<td>' . trim(round($row['aloc'] ?? 0, 2)) . '</td>';

               $table .= '</tr>';
          }

          $table .= '<tfoot>
                         <tr>
                              <td colspan="9">
                                   <ul class="annotation-list">
                                        <li><strong>Date: </strong>Ngày tháng.</li>
                                        <li><strong>Time: </strong>Khoảng thời gian (AM: Buổi sáng, PM: Buổi tối).</li>
                                        <li><strong>Total: </strong>Tổng số lượng cuộc gọi.</li>
                                        <li><strong>Failed: </strong>Số cuộc gọi không có hội thoại.</li>
                                        <li><strong>Answered: </strong>Số cuộc gọi có hội thoại.</li>
                                        <li><strong>Seconds: </strong>Thời gian hội thoại của cuộc gọi.</li>
                                        <li><strong>Minutes (call_talk/60): </strong>Số phút hội thoại cuộc gọi.</li>
                                        <li><strong>Calls Per Min: </strong>Số lượng cuộc gọi trong mỗi phút / Số lượng cuộc gọi được trả lời mỗi phút</li>
                                        <li><strong>ASR: </strong>Tỷ lệ cuộc gọi được trả lời.</li>
                                        <li><strong>ALOC: </strong>Thời lượng trung bình cuộc gọi được trả lời.</li>
                                   </ul>
                              </td>
                         </tr>
                    </tfoot>';

          $table .= '</table>';
          return $table;
     }
}
