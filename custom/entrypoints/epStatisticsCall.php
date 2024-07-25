<?php
global $db, $current_user, $app_list_strings;

if (isset($_POST['for']) && $_POST['for'] == 'getDetailCallUser') {
     if(is_null($_POST['user_id']) || empty($_POST['user_id']) || is_null($_POST['direction']) || empty($_POST['direction'])) return false;

     $user_id       = $_POST['user_id'];
     $direction     = trim($_POST['direction']);
     $from_date     = $_POST['fdate'];
     $to_date       = $_POST['tdate'];
     $array_direction = array(
          'Cuộc gọi đi' => 'outbound',
          'Cuộc gọi đến' => 'inbound',
          'Cuộc gọi nhỡ' => 'missed',
          'Nhá máy' => 'suddenly',
          'Số rác' => 'spam',
          'Nội bộ' => 'internal',
     );

     $sql = "
     SELECT id, name, date_entered, date_start, date_end, status, direction, call_from, call_to, log, booking_id, record_file, description
     FROM calls
     WHERE assigned_user_id = '".$user_id."'
     AND direction = '".$array_direction[$direction]."'
     AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) BETWEEN '". date('Y-m-d', strtotime($from_date)) ."' AND '". date('Y-m-d', strtotime($to_date)) ." 23:59:59'
     AND deleted = 0
     ORDER BY status, date_entered DESC";

     $res = $db->query($sql);
     $i = 1;

     $html = '<div class="box-section detail_calls--user"><table class="table-detail-calls table-details__booking table-details__sticky" cellpadding="0" cellspacing="0">
          <thead>
               <tr>
                    <th width="3%">STT</th>
                    <th width="13%" align="center">Mã cuộc gọi</th>
                    <th width="10%" align="center">Trạng thái</th>
                    <th width="8%" align="center">Gọi từ</th>
                    <th width="8%" align="center">Gọi đến</th>
                    <th width="10%" align="center">Loại cuộc gọi</th>
                    <th width="10%" align="center">Thời gian</th>
                    <th width="8%" align="center">Thời lượng</th>
                    <th width="25%" align="center">Ghi chú</th>
                    <th align="center">Ghi âm</th>
               </tr>
          </thead>';

     while ($row = $db->fetchByAssoc($res)) {
          // Class status
          if($row['status'] == 'processing'){
               $status_class = 'text-warning';
          } else if ($row['status'] == 'done'){
               $status_class = 'text-success';
          } else {
               $status_class = 'text-normal';
          }

          // class direction
          if($row['direction'] == 'suddenly'){
               $direction_class = 'text-warning';
          } else if ($row['direction'] == 'inbound'){
               $direction_class = 'text-success';
          } else if ($row['direction'] == 'missed'){
               $direction_class = 'text-danger';
          } else if ($row['direction'] == 'outbound'){
               $direction_class = 'text-primary';
          } else if ($row['direction'] == 'spam'){
               $direction_class = 'text-spam';
          } else {
               $direction_class = 'text-normal';
          }

          // caculation duration
          $duration =  date('H:i:s', (strtotime($row['date_end']) - strtotime($row['date_start'])));

          // record file
          $record_file = ($row['record_file']) ? $row['record_file'] : "";

          $html .= '
          <tr>
               <td align="center" class="fw-semibold">'.$i.'</td>
               <td align="left"><a href="index.php?module=Calls&return_module=Calls&action=DetailView&record='.$row['id'].'">'.$row['name'].'</a></td>
               <td align="center" class="'.$status_class.'"><strong>'.$app_list_strings['call_status_dom'][$row['status']].'</strong></td>
               <td>'.$row['call_from'].'</td>
               <td>'.$row['call_to'].'</td>
               <td align="center" class="'.$direction_class.'"><strong>'.$app_list_strings['calls_direction_list'][$row['direction']].'</strong></td>
               <td align="center">'.$row['date_start'].'</td>
               <td align="center">'.$duration.'</td>
               <td align="left">'.$row['description'].'</td>
               <td align="center"><audio controls style="height: 35px;"><source src="'.$record_file.'" type="audio/wav"></audio></td>
          </tr>';
          $i++;
     }

     $html .= '</table></div>';

     echo $html;
}