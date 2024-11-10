<?php

global $current_user, $db;

// Loại trừ user Booker - 493ad5e5-ffea-a84f-96d7-6577fed623d6

$array_admin = [
     '168889bb-54c2-59c7-8b3f-649102530d3c', //hungnh
     '622ecf27-f729-7187-7e27-6520e0dab882', //quangnd
     '4f4d7a13-4171-9b7d-251c-64dd8f9885e4', //nhat
     '9eb0f65f-a9f6-65bb-1985-637ca8511491', //trinh
     '1', //DDuc
];

// TRUYỀN VÀO USER_ID => ONL / OFF
if (isset($_POST['for']) && $_POST['for'] == 'is_Online') {
     $current_user_id    = $current_user->id;
     $path               = "secure_sessions/check_online_logs/";
	$temp_files         = scandir($path);
	natsort($temp_files);
	$timestamp_now = strtotime(date('Y-m-d H:i:s', strtotime('+7 hour')));

     $is_busy = 0; //Không bận
     foreach ($temp_files as $file) {
          $info               = pathinfo($file);
          $file_name          = basename($file,'.'.$info['extension']);
          $user_id            = str_replace('_', '-', $file_name);

          if ($file != "." && $file != ".." && $file != "Thumbs.db" && $file != basename(__FILE__) && $user_id == $current_user_id) {
               $json_file          = read_file_logs_online($user_id);
               $data_user  	     = json_decode($json_file, true);
               $strtotime_user 	= strtotime(date('Y-m-d H:i:s', strtotime($data_user['last_time'])));
               $diffInSeconds  	= abs($timestamp_now - $strtotime_user);

               if(trim($data_user['busy']) == 1){
                    $status = 'Logged Out';
                    $is_busy = 1;
               } else {
                    if($diffInSeconds > 150){ //2 phút rữ
                         $status = 'Logged Out';
                         $is_busy = 1;
                    } else {
                         $is_busy = 0;
                         $status = 'Available';
                    }
               }
               
               if($is_busy == 1){
                    echo 0;
                    exit();
               } else {
                    echo 1;
                    exit();
               }
          }
     }  
}


if (isset($_POST['for']) && $_POST['for'] == 'changeStatusAgent') {
     $agent  = isset($_POST['agent']) ? trim($_POST['agent']) : '';
     $agent_status = isset($_POST['status']) ? trim($_POST['status']) : '';

     if ($agent && $agent_status) {
          agent_change_status($agent, $agent_status);
      }
}

// Check trạng thái người dùng
if (isset($_POST['for']) && $_POST['for'] == 'checkUsrStt') {
     $time_current  = date('Y-m-d H:i:s', strtotime('+7 hour'));

     // Booker
    if ($current_user->id === '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          content_log($current_user->id, $time_current, $_POST['is_busy']);
          echo 1;
          return;
     }
     
     // Admin - KT
     $array_adminkt = array(
          '72ece22c-cb25-8e30-9dea-56f2201cd359',
          '9ba5c5a0-a402-02f4-76d3-53ba0481ce45',
          'b5523dbd-b9a7-67c0-77b5-533e6ece89b1',
     );
     if ((!is_admin($current_user) || in_array($current_user->id, $array_adminkt)) && $current_user->id !== '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          // Update START_ONLINE khi user Online lần đầu
          $sql_exist = '
               SELECT id, status
               FROM ec_online_report
               WHERE deleted = 0
               AND assigned_user_id = "' . $current_user->id . '"
               AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
          ';
          $row_exist = $db->fetchByAssoc($db->query($sql_exist));
          $online 		= new EC_Online_Report;
          if(isset($row_exist) && !empty($row_exist)){
               $online->retrieve($row_exist['id']);
          } else {
               $online->retrieve($current_user->id);
          }
          if ($row_exist && $row_exist['status'] != 2 && empty($online->start_online)) {
               // Thời gian bắt đầu online - checkin - online lần đầu
               if (strtotime($online->start_online) === false) {
                    $online->status = 1;
                    $online->save();

                    $start_time = date('Y-m-d H:i:s', strtotime($online->date_modified));
                    $db->query("
                        UPDATE ec_online_report
                        SET start_online = '{$start_time}', last_online = '{$start_time}'
                        WHERE id = '{$online->id}'
                        AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) = CURDATE()
                        AND deleted = 0
                    ");
               } 
          }

          // Ghi log trạng thái
          $is_busy = $online->status == 2 ? 1 : 0;
          content_log($current_user->id, $time_current, $is_busy);
          echo $is_busy ? 2 : 1;
     }
}

// Họ click vào button Busy
if (isset($_POST['for']) && $_POST['for'] == 'saveLastBusyUser') {
     $time = $_POST['time'];
     $busy = $_POST['busy'];

     // Booker
    if ($current_user->id === '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          content_log($current_user->id, $time, $busy);
          echo 1;
          return;
     }
     
     // Admin - KT
     $array_adminkt = array(
          '72ece22c-cb25-8e30-9dea-56f2201cd359',
          '9ba5c5a0-a402-02f4-76d3-53ba0481ce45',
          'b5523dbd-b9a7-67c0-77b5-533e6ece89b1',
     );

     if ((!is_admin($current_user) || in_array($current_user->id, $array_adminkt)) && $current_user->id !== '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          // Update last_online khi họ bấm bận
          $sql_exist = '
               SELECT id, status
               FROM ec_online_report
               WHERE deleted = 0
               AND assigned_user_id = "' . $current_user->id . '"
               AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
          ';
          $row_exist = $db->fetchByAssoc($db->query($sql_exist));
          $online 		= new EC_Online_Report;

          $online = new EC_Online_Report;
          if ($row_exist) {
              $online->retrieve($row_exist['id']);
          }

          // Cập nhật trạng thái busy
          if ($row_exist && $row_exist['status'] != 2) {
               $online->status = 2;  // Đang bận
               $online->save();
          } else {
               $online->status = 1;  // Hết bận
               $online->save();
          }

          // Cập nhật thời gian last_online
          if (strtotime($online->last_online) !== false) {
               $last_online_time = date('Y-m-d H:i:s', strtotime($online->date_modified));
               $db->query("
               UPDATE ec_online_report
               SET last_online = '{$last_online_time}'
               WHERE id = '{$online->id}'
               AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) = CURDATE()
               AND deleted = 0
               ");
               echo $online->status == 2 ? 'Cập nhật last_online khi họ bận' : 'Cập nhật last_online khi họ hết bận';
          } else {
               echo 'last_online = NULL';
          }

          // Cập nhật trạng thái busy trong session
          $_SESSION['busy'] = (trim($_POST['checked']) == 1) ? 1 : 0;
          $time_busy = $_POST['time_busy'];
          content_log($current_user->id, $time_busy, $_SESSION['busy']);
     }
}

if (isset($_POST['for']) && $_POST['for'] == 'saveLastClickUser') {
     $time = $_POST['time'] ?? null;
     $busy = $_SESSION['busy'] ?? 0;

     // Booker
     if ($current_user->id === '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          content_log($current_user->id, $time, $busy);
          echo 1;
          return;
     }

     // Admin - KT
     $array_adminkt = array(
          '72ece22c-cb25-8e30-9dea-56f2201cd359',
          '9ba5c5a0-a402-02f4-76d3-53ba0481ce45',
          'b5523dbd-b9a7-67c0-77b5-533e6ece89b1',
     );

     if ((!is_admin($current_user) || in_array($current_user->id, $array_adminkt)) && $current_user->id !== '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          if (is_null($time) || empty($time)) {
               echo 0;
               exit();
           }

          $sql_exist = '
               SELECT id, status
               FROM ec_online_report
               WHERE deleted = 0
               AND assigned_user_id = "' . $current_user->id . '"
               AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
          ';
          $row_exist = $db->fetchByAssoc($db->query($sql_exist));
          $online 		= new EC_Online_Report;

          if(isset($row_exist) && !empty($row_exist)){
               $online->retrieve($row_exist['id']);
          } else {
               $online->retrieve($current_user->id);
          }
          
          $busy = isset($_SESSION['busy']) ? $_SESSION['busy'] : ($online->status == 2 ? 1 : 0);
          content_log($current_user->id, $time, $busy);
          echo 1;
     }
}