<?php
global $db, $current_user, $app_list_strings;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $type = isset($_POST['for']) ? $_POST['for'] : "";

     $user_id    = $_POST['employee_id'] ?? null;
     $from_date  = $_POST['from_date']   ?? null;
     $to_date    = $_POST['to_date']     ?? null;

     $user = BeanFactory::newBean('Users');
     $user->retrieve($user_id);
     $full_name     = trim($user->full_name);
     $title         = $user->title;

     if (
          is_null($user_id)
          || empty($user_id)
          || !strtotime($from_date)
          || !strtotime($to_date)
          || strtotime($from_date) > strtotime($to_date)
     ) {
          return false;
     }

     if ($type == 'viewListCallsEmployees') {
          $sql = "SELECT id, name, date_entered, date_start, date_end, call_talk, status, direction, call_from, call_to, log, description
                    FROM calls
                    WHERE assigned_user_id = '" . $user_id . "'
                    AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . " 23:59:59'
                    AND deleted = 0
                    AND direction != 'missed'
                    ORDER BY direction, date_entered DESC";

          $res = $db->query($sql);
          $row_count = $db->getRowCount($res);
          $i = 1;

          // table-details__sticky
          $html = '<div class="box-detail__calls table-responsive"><table class="table-detail-calls table-details__booking table__sticky" cellpadding="0" cellspacing="0">
                         <thead>
                              <tr>
                                   <th width="3%">#</th>
                                   <th width="8%" align="center">Mã cuộc gọi</th>
                                   <th width="8%" align="center">Trạng thái</th>
                                   <th width="8%" align="center">Gọi từ</th>
                                   <th width="8%" align="center">Gọi đến</th>
                                   <th width="8%" align="center">Loại cuộc gọi</th>
                                   <th width="10%" align="center">Thời gian gọi</th>
                                   <th width="8%" align="center">Hội thoại</th>
                                   <th width="25%" align="center">Ghi chú</th>
                                   <th align="center">Nhân viên phụ trách</th>
                              </tr>
                         </thead>';

          if ($row_count > 0) {
               while ($row = $db->fetchByAssoc($res)) {
                    // Class status
                    if ($row['status'] == 'processing') {
                         $status_class = 'text-warning';
                    } else if ($row['status'] == 'done') {
                         $status_class = 'text-success';
                    } else {
                         $status_class = 'text-normal';
                    }

                    // class direction
                    if ($row['direction'] == 'suddenly') {
                         $direction_class = 'text-warning';
                    } else if ($row['direction'] == 'inbound') {
                         $direction_class = 'text-success';
                    } else if ($row['direction'] == 'missed') {
                         $direction_class = 'text-danger';
                    } else if ($row['direction'] == 'outbound') {
                         $direction_class = 'text-primary';
                    } else if ($row['direction'] == 'spam') {
                         $direction_class = 'text-spam';
                    } else {
                         $direction_class = 'text-normal';
                    }

                    $html .= '
                         <tr>
                              <td align="center" class="fw-semibold">' . $i . '</td>
                              <td align="left"><a target="_blank" href="index.php?module=Calls&return_module=Calls&action=DetailView&record=' . $row['id'] . '">' . $row['name'] . '</a></td>
                              <td align="center" class="' . $status_class . '"><strong>' . $app_list_strings['call_status_dom'][$row['status']] . '</strong></td>
                              <td align="center">' . $row['call_from'] . '</td>
                              <td align="center">' . $row['call_to'] . '</td>
                              <td align="center" class="' . $direction_class . '"><strong>' . $app_list_strings['calls_direction_list'][$row['direction']] . '</strong></td>
                              <td align="center">' . $row['date_start'] . '</td>
                              <td align="center">' . global_secondsToTimeFormat($row['call_talk']) . '</td>
                              <td align="left">' . $row['description'] . '</td>
                              <td align="center">' . $full_name . '</td>
                         </tr>';
                    $i++;
               }
          } else {
               $html .= '
                         <tr>
                            <td colspan="10">
                                <div class="d-flex align-items-center flex-column py-4 gap-3">
                                    <svg fill="#b3b3b3" width="70" height="70" viewBox="0 0 846.66 846.66" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" stroke="#b3b3b3"><g stroke-width="0"></g><g stroke-linecap="round" stroke-linejoin="round"></g><g> <defs> <style type="text/css">  .fil0 {fill:black;fill-rule:nonzero}  </style> </defs> <g id="Layer_x0020_1"> <path class="fil0" d="M93.28 100.89l69.12 0 0 -71.08c0,-11.44 9.28,-20.71 20.72,-20.71l414.03 0c97.36,0 176.94,79.58 176.94,176.94l0 539.02c0,11.44 -9.27,20.71 -20.71,20.71l-69.12 0 0 71.08c0,11.44 -9.28,20.71 -20.72,20.71l-570.26 0c-11.44,0 -20.71,-9.27 -20.71,-20.71l0 -695.25c0,-11.44 9.27,-20.71 20.71,-20.71zm148.42 178.12c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm0 216.78c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm0 -108.39c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm-37.87 -286.51l303.48 0c97.36,0 176.95,79.58 176.95,176.94l0 426.52 48.41 0 0 -518.31c0,-74.49 -61.03,-135.52 -135.52,-135.52l-393.32 0 0 50.37zm11.51 478.47l326.15 0c11.43,0 20.71,9.28 20.71,20.71l0 105.46c0,11.44 -9.28,20.72 -20.71,20.72l-326.15 0c-11.44,0 -20.71,-9.28 -20.71,-20.72l0 -105.46c0,-11.43 9.27,-20.71 20.71,-20.71zm305.43 41.42l-284.72 0 0 64.04 284.72 0 0 -64.04zm-13.46 -478.47l-393.32 0 0 653.83 528.84 0 0 -518.31c0,-74.49 -61.02,-135.52 -135.52,-135.52z"></path> </g> </g></svg>
                                    <p class="text-dark fw-bold">Không có thông tin cuộc gọi</p>
                                </div>
                            </td>   
                        </tr>';
          }

          $html .= '</table></div>';

          echo $html;
     } else if ($type == 'viewStatisticsCallsEmployees') {
          $sql = "SELECT 
                    c.assigned_user_id,
                    CONCAT(IFNULL(u.last_name, ''), ' ', IFNULL(u.first_name, '')) AS full_name,
                    c.direction,
                    COUNT(c.id) AS total_calls,
                    AVG(c.call_duration) AS avg_duration,
                    AVG(c.call_talk) AS avg_talk_time,
                    AVG(c.call_wait) AS avg_wait_time,
                    0 AS completed_calls,
                    0 AS new_calls,
                    0 AS processing_calls,
                    1 AS sort_order -- Chi tiết loại gọi
               FROM calls c
               INNER JOIN users u ON c.assigned_user_id = u.id
               WHERE c.date_entered BETWEEN '{$from_date}' AND '{$to_date}' 
               AND c.direction != 'missed'
               AND c.assigned_user_id = '{$user_id}'
               AND c.deleted = 0
               GROUP BY c.direction

               UNION ALL
               SELECT 
                    c.assigned_user_id,
                    CONCAT(IFNULL(u.last_name, ''), ' ', IFNULL(u.first_name, '')) AS full_name,
                    'total' AS direction,
                    COUNT(c.id) AS total_calls,
                    AVG(c.call_duration) AS avg_duration,
                    AVG(c.call_talk) AS avg_talk_time,
                    AVG(c.call_wait) AS avg_wait_time,
                    0 AS completed_calls,
                    0 AS new_calls,
                    0 AS processing_calls,
                    2 AS sort_order -- Tổng hợp
               FROM calls c
               INNER JOIN users u ON c.assigned_user_id = u.id
               WHERE c.date_entered BETWEEN '{$from_date}' AND '{$to_date}' 
               AND c.direction != 'missed'
               AND c.assigned_user_id = '{$user_id}'
               AND c.deleted = 0

               UNION ALL
               SELECT 
                    c.assigned_user_id,
                    CONCAT(IFNULL(u.last_name, ''), ' ', IFNULL(u.first_name, '')) AS full_name,
                    'status' AS direction,
                    0 AS total_calls,
                    0 AS avg_duration,
                    0 AS avg_talk_time,
                    0 AS avg_wait_time,
                    COUNT(CASE WHEN c.status = 'done' THEN c.id END) AS completed_calls,
                    COUNT(CASE WHEN c.status = 'new' THEN c.id END) AS new_calls,
                    COUNT(CASE WHEN c.status = 'processing' THEN c.id END) AS processing_calls,
                    3 AS sort_order
               FROM calls c
               INNER JOIN users u ON c.assigned_user_id = u.id
               WHERE c.deleted = 0
               AND c.date_entered BETWEEN '{$from_date}' AND '{$to_date}'
               AND c.direction != 'missed'
               AND c.assigned_user_id = '{$user_id}'
               ORDER BY assigned_user_id, sort_order, total_calls DESC
               ";

          $res = $db->query($sql);
          $data = [];
          while ($row = $db->fetchByAssoc($res)) {
               $direction     = $row['direction'];
               $totalCalls    = (int)$row['total_calls'];
               $avgDuration   = round((float)$row['avg_duration'], 2);
               $avgTalkTime   = round((float)$row['avg_talk_time'], 2);
               $avgWaitTime   = round((float)$row['avg_wait_time'], 2);

               $completed_calls     = (int)$row['completed_calls'];
               $new_calls           = (int)$row['new_calls'];
               $processing_calls    = (int)$row['processing_calls'];

               if ($direction === 'total') {
                    $data['totals'] = [
                         'total_calls'   => $totalCalls,
                         'avg_duration'  => $avgDuration,
                         'avg_talk_time' => $avgTalkTime,
                         'avg_wait_time' => $avgWaitTime,
                    ];
               } else if ($direction === 'status') {
                    $data['status'] = [
                         'done'   => $completed_calls,
                         'new'  => $new_calls,
                         'processing' => $processing_calls,
                    ];
               } else {
                    $data['directions'][$direction] = [
                         'total_calls'   => $totalCalls,
                         'avg_duration'  => $avgDuration,
                         'avg_talk_time' => $avgTalkTime,
                         'avg_wait_time' => $avgWaitTime,
                    ];
               }
          }

          // pr($data);

          // Chuẩn bị dữ liệu biểu đồ combo chart bar
          $avgDurations = [];
          $avgTalkTimes = [];
          $avgWaitTimes = [];
          foreach ($data['directions'] as $direction => $values) {
               $avgDurations[] = $values['avg_duration'];
               $avgTalkTimes[] = $values['avg_talk_time'];
               $avgWaitTimes[] = $values['avg_wait_time'];
          }
          $chartComboData = implode('|', [
               implode(',', $avgDurations),  // Dữ liệu `avg_duration`
               implode(',', $avgTalkTimes), // Dữ liệu `avg_talk_time`
               implode(',', $avgWaitTimes)  // Dữ liệu `avg_wait_time`
          ]);

          $directions_type = implode('|', array_map(
               fn($key) => $app_list_strings['calls_direction_list'][$key] ?? $key,
               array_keys($data['directions'])
          ));

          $status_call = implode('|', array_map(
               fn($key) => $app_list_strings['call_status_dom'][$key] ?? $key,
               array_keys($data['status'])
          ));

          // pr($status_call);

          $html = ' <div class="row g-0 border-bottom">
                         <div class="col-md-12">
                              <h5>' . $full_name . ' (' . $title . ')</h5>
                         </div>
                    </div>';

          $html .= '<div class="row g-0">
                         <div class="col-md-5">
                              <div class="card card-employee h-100">
                                   <h5 class="card-header card-header__detail">Loại cuộc gọi</h5>
                                   <div class="card-body">
                                        <canvas id="chart-emp-direction"></canvas>
                                        <div id="chart-emp-direction-label" class="d-none">' . $directions_type . '</div>
                                        <div id="chart-emp-direction-data" class="d-none">' . implode('|', array_column($data['directions'], 'total_calls')) . '</div>
                                        <div id="chart-emp-direction-type" class="d-none">doughnut</div>
                                   </div>
                              </div>
                         </div>
                         <div class="col-md-7">
                              <div class="card card-employee h-100">
                                   <h5 class="card-header card-header__detail">Thời lượng trung bình</h5>
                                   <div class="card-body">
                                        <canvas id="chart-emp-avg_duration"></canvas>
                                        <div id="chart-emp-avg_duration-label" class="d-none">Thời lượng|Hội thoại|Thời gian chờ</div>
                                        <div id="chart-emp-avg_duration-data" class="d-none">' . $data['totals']['avg_duration'] . '|' . $data['totals']['avg_talk_time'] . '|' . $data['totals']['avg_wait_time'] . '</div>
                                        <div id="chart-emp-avg_duration-type" class="d-none">bar</div>
                                   </div>
                              </div>
                         </div>
                    </div>
                    <div class="row g-0">
                         <div class="col-md-5">
                              <div class="card card-employee h-100">
                                   <h5 class="card-header card-header__detail">Trạng thái cuộc gọi</h5>
                                   <div class="card-body">
                                        <canvas id="chart-emp-status"></canvas>
                                        <div id="chart-emp-status-label" class="d-none">' . $status_call . '</div>
                                        <div id="chart-emp-status-data" class="d-none">' . implode('|', $data['status']) . '</div>
                                        <div id="chart-emp-status-type" class="d-none">doughnut</div>
                                   </div>
                              </div>
                         </div>
                         <div class="col-md-7">
                              <div class="card card-employee h-100">
                                   <h5 class="card-header card-header__detail">Thời lượng trung bình theo loại</h5>
                                   <div class="card-body">
                                        <canvas id="chart-emp-avg_dir"></canvas>
                                        <div id="chart-emp-avg_dir-label" class="d-none">' . $directions_type . '</div>
                                        <div id="chart-emp-avg_dir-data" class="d-none">' . $chartComboData . '</div>
                                        <div id="chart-emp-avg_dir-type" class="d-none">bar-combo</div>
                                   </div>
                              </div>
                         </div>
                    </div>
          ';

          echo $html;
     } else if ($type == 'sendTeleConfirmCallSales'){
          $emp_outbound                 = $_POST['emp_outbound'] ?? 0;
          $emp_outbound_answer          = $_POST['emp_outbound_answer'] ?? 0;
          $emp_question_ticket          = $_POST['emp_question_ticket'] ?? 0;
          $emp_noanswer_nonote          = $_POST['emp_noanswer_nonote'] ?? 0;
          $emp_noanswer_up_15           = $_POST['emp_noanswer_up_15'] ?? 0;
          $emp_noanswer_under_15        = $_POST['emp_noanswer_under_15'] ?? 0;
          $emp_noanswer_unconnected     = $_POST['emp_noanswer_unconnected'] ?? 0;
          $total_talk_outbound          = $_POST['total_talk_outbound'] ?? 0;
          $asr                          = ($emp_outbound > 0) ? round(($emp_outbound_answer / $emp_outbound) * 100, 2) : 0;
          $avg_talk                     = ($emp_outbound_answer > 0) ? round($total_talk_outbound / $emp_outbound_answer) : 0;
               
          if(strtotime($from_date) === strtotime($to_date)){
               $mess_date = date('d/m/Y', strtotime($from_date));
          } else{
               $mess_date = date('d/m/Y', strtotime($from_date)) . ' - ' . date('d/m/Y', strtotime($to_date));
          }

          $mess_nonote = "";
          if ((int)$emp_noanswer_nonote > 0) {
               $mess_nonote .= "- Không ghi chú/phân loại: <b>" . $emp_noanswer_nonote . "</b>\n";
          } 
          $messages = "- Nhân viên: <b>" . $full_name . "</b>\n" .
                    "- Ngày: <b>" . $mess_date . "</b>\n" .
                    "- Cuộc gọi đi: <b>" . $emp_outbound . "</b>\n" .
                    "- Nghe máy: <b>" . $emp_outbound_answer . "</b>\n" .
                    "- Tỉ lệ nghe máy: <b>" . $asr . "%</b>\n" .
                    "- Thời gian nghe máy trung bình: <b>" . $avg_talk . "s</b>\n" .
                    "- Không nghe máy (>15s): <b>" . $emp_noanswer_up_15 . "</b>\n" .
                    "- Đổ chuông ngắn: <b>" . $emp_noanswer_under_15 . "</b>\n" .
                    "- Số không liên lạc: <b>" . $emp_noanswer_unconnected . "</b>\n" .
                    $mess_nonote .
                    "- Khách hỏi vé: <b>" . $emp_question_ticket . "</b>\n" .
                    "<pre>[INFO]: " . proposeCallImprovementStrategy($asr) . "</pre>";
          $content = html_entity_decode($messages, ENT_QUOTES | ENT_HTML5, 'UTF-8');

          $botToken   = $sugar_config['telegram']['bot_token'] ?? '';
          $chatId     = $sugar_config['telegram']['chat_id'] ?? '';
          $threadId   = $sugar_config['telegram']['thread_id_system'] ?? '';
          Telegram::sendMessageData(json_encode(['text' => $content, 'parse_mode' => 'HTML'], JSON_UNESCAPED_UNICODE), $botToken, $chatId, $threadId);

          echo json_encode($result);
          exit();
     }
}
