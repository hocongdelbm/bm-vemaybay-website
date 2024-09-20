<?php

global $current_user, $db;

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
                    $is_busy = 1;
               } else {
                    if($diffInSeconds > 150){ //2 phút rữ
                         $is_busy = 1;
                    } else {
                         $is_busy = 0;
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
     $agent  = isset($_POST['agent']) ? trim($_POST['agent']).'@td.timchuyenbay.net' : '';
     $status = isset($_POST['status']) ? trim($_POST['status']) : '';

     agent_change_status($agent, $status);
}

function agent_change_status($agent, $status){
     if(empty($agent) || empty($status)) {
          $response['success'] = array(
               'code' => 400,
               'title' => 'agent status bad request',
           );
          echo json_encode($response);
          exit();
     }

     $toten  = 'sdjfhsgaksuegrqw38463784672793746rwadjksfgha3e467dhcauw4y5t783yr';
     $body_request = array(
          'agent' => $agent,
          'status' => $status,
          'token' => $toten,
     );

     try {
          $curl = curl_init();
          if ($curl === false) {
               echo json_encode(array('error' => 1, 'httpcode' => 500, 'message' => 'cURL Failed to initialize'));
          }

          curl_setopt_array($curl, array(
               CURLOPT_URL             => "https://td.timchuyenbay.net/agent_status/change_status.php",
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_FOLLOWLOCATION => true,
               CURLOPT_SSL_VERIFYHOST => false, // Use at localhost
               CURLOPT_SSL_VERIFYPEER => false, // Use at localhost
               CURLOPT_TIMEOUT        => 0,
               CURLOPT_CUSTOMREQUEST   => 'POST',
               CURLOPT_POSTFIELDS      => $body_request,
          ));
     
          $json = curl_exec($curl);
          $httpcode   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
          curl_close($curl);
          $arr = json_decode($json, true);

          // if($arr['success']['code'] == 200){
          //      echo 1;
          //      exit();
          // } 

          // echo 0;
          // exit();
     } catch(Exception $e) {
          return json_encode(array('error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()));
     }
}

// Check trạng thái người dùng
if (isset($_POST['for']) && $_POST['for'] == 'checkUsrStt') {

     // booker
     if($current_user->id == '493ad5e5-ffea-a84f-96d7-6577fed623d6'){
          $time_current  = date('Y-m-d H:i:s', strtotime('+7 hour'));
          $busy = $_POST['is_busy'];
          content_log($current_user->id, $time_current, $busy);
          echo 1;
     }

     // Admin - KT
     $array_adminkt = array(
          '72ece22c-cb25-8e30-9dea-56f2201cd359',
          '9ba5c5a0-a402-02f4-76d3-53ba0481ce45',
          'b5523dbd-b9a7-67c0-77b5-533e6ece89b1',
     );

     if ((!is_admin($current_user) || in_array($current_user->id, $array_adminkt)) && $current_user->id != '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          $time_current  = date('Y-m-d H:i:s', strtotime('+7 hour'));

          // Update START_ONLINE khi user Online lần đầu
          $sql_exist = '
               SELECT id, status
               FROM ec_online_report
               WHERE deleted = 0
               AND assigned_user_id = "' . $current_user->id . '"
               AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
          ';

          $res_exist 	= $db->query($sql_exist);
          $row_exist 	= $db->fetchByAssoc($res_exist);
          $online 		= new EC_Online_Report;
          if(isset($row_exist) && !empty($row_exist)){
               $online->retrieve($row_exist['id']);
          } else {
               $online->retrieve($current_user->id);
          }

          if (!empty($row_exist['id']) && $row_exist['status'] != 2) {
               // $online->retrieve($row_exist['id']);

               // Thời gian bắt đầu online - checkin
               if (strtotime($online->start_online) === false) {
                    $online->status = 1;
                    $online->save();

                    $sql = '
                         UPDATE ec_online_report
                         SET start_online = "' . date('Y-m-d H:i:s', strtotime($online->date_modified)) . '", last_online = "' . date('Y-m-d H:i:s', strtotime($online->date_modified)) . '"
                         WHERE id = "' . $online->id . '"
                         AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
                         AND deleted = 0
                    ';
                    $db->query($sql);

                    // ONLINE LẦN ĐẦU
                    echo 'first_online';
               } 
          }

          // GHI VÀO LOG
          $is_busy = 0;

          if($online->status == 2){
               echo 2;
               $is_busy = 1;
          } 

          content_log($current_user->id, $time_current, $is_busy);
     }
}

// Họ click vào button Busy
if (isset($_POST['for']) && $_POST['for'] == 'saveLastBusyUser') {

     // Booker
     if($current_user->id == '493ad5e5-ffea-a84f-96d7-6577fed623d6'){
          $time = $_POST['time'];
          $busy = $_POST['checked'];
          content_log($current_user->id, $time, $busy);
          echo 1;
     }

     // Admin - KT
     $array_adminkt = array(
          '72ece22c-cb25-8e30-9dea-56f2201cd359',
          '9ba5c5a0-a402-02f4-76d3-53ba0481ce45',
          'b5523dbd-b9a7-67c0-77b5-533e6ece89b1',
     );

     if ((!is_admin($current_user) || in_array($current_user->id, $array_adminkt)) && $current_user->id != '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          // Update last_online khi họ bấm bận
          $sql_exist = '
               SELECT id, status
               FROM ec_online_report
               WHERE deleted = 0
               AND assigned_user_id = "' . $current_user->id . '"
               AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
          ';

          $res_exist 	= $db->query($sql_exist);
          $row_exist 	= $db->fetchByAssoc($res_exist);
          $online 		= new EC_Online_Report;

          if (!empty($row_exist['id']) && $row_exist['status'] != 2) {
               // ĐANG BẬN
               $online->retrieve($row_exist['id']);
               $online->status = 2;
               $online->save();

               // Thời gian bắt đầu online - checkin
               if (strtotime($online->last_online) !== false) {
                    $sql = '
                         UPDATE ec_online_report
                         SET last_online = "' . date('Y-m-d H:i:s', strtotime($online->date_modified)) . '"
                         WHERE id = "' . $online->id . '"
                         AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
                         AND deleted = 0
                    ';
                    $db->query($sql);

                    echo 'Cập nhật last_online khi họ bận';
               } else {
                    echo 'last_online = NULL';
               }
          } else {
               // HẾT BẬN
               $online->retrieve($row_exist['id']);
               $online->status = 1;
               $online->save();
               
               // Thời gian bắt đầu online - checkin
               if (strtotime($online->last_online) !== false) {
                    $sql = '
                         UPDATE ec_online_report
                         SET last_online = "' . date('Y-m-d H:i:s', strtotime($online->date_modified)) . '"
                         WHERE id = "' . $online->id . '"
                         AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
                         AND deleted = 0
                    ';
                    $db->query($sql);

                    echo 'Cập nhật last_online khi họ HẾT bận';
               } else {
                    echo 'last_online = NULL';
               }
          }

          if (trim($_POST['checked']) == 1) {
               $_SESSION['busy'] = 1;
          } else {
               $_SESSION['busy'] = 0;
          }
          $time = $_POST['time_busy'];

          content_log($current_user->id, $time, $_SESSION['busy']);
     }
}

if (isset($_POST['for']) && $_POST['for'] == 'saveLastClickUser') {
     // booker
     if($current_user->id == '493ad5e5-ffea-a84f-96d7-6577fed623d6'){
          $time = $_POST['time'];
          $busy = $_SESSION['busy'];
          content_log($current_user->id, $time, $busy);
          echo 1;
     }

     // Admin - KT
     $array_adminkt = array(
          '72ece22c-cb25-8e30-9dea-56f2201cd359',
          '9ba5c5a0-a402-02f4-76d3-53ba0481ce45',
          'b5523dbd-b9a7-67c0-77b5-533e6ece89b1',
     );

     if ((!is_admin($current_user) || in_array($current_user->id, $array_adminkt)) && $current_user->id != '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          $sql_exist = '
               SELECT id, status
               FROM ec_online_report
               WHERE deleted = 0
               AND assigned_user_id = "' . $current_user->id . '"
               AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
          ';
          $res_exist 	= $db->query($sql_exist);
          $row_exist 	= $db->fetchByAssoc($res_exist);
          $online 		= new EC_Online_Report;

          if(isset($row_exist) && !empty($row_exist)){
               $online->retrieve($row_exist['id']);
          } else {
               $online->retrieve($current_user->id);
          }
          
          if (is_null($_POST['time']) || empty($_POST['time'])){
               echo 0; 
               exit();
          } 

          $time = $_POST['time'];
          $busy = isset($_SESSION['busy']) ? $_SESSION['busy'] : ($online->status == 2 ? 1 : 0);

          content_log($current_user->id, $time, $busy);
          echo 1;
     }
}