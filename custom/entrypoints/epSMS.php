<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once('modules/EC_SMS_Logs/SMS.php');
    $type = isset($_POST['type']) ? $_POST['type'] : "";

    if($type == 'sms' || $type == 'send_sms') {
        $direction   = isset($_POST['direction']) ? global_test_input($_POST['direction']) : '';
        $phone       = isset($_POST['phone']) ? global_test_input($_POST['phone']) : '';
        $message     = isset($_POST['message']) ? $_POST['message'] : '';
        $parent_id   = isset($_POST['parent_id']) ? global_test_input($_POST['parent_id']) : '';
        $parent_type = isset($_POST['parent_type']) ? global_test_input($_POST['parent_type']) : '';

        if(empty($phone) || strlen($phone) < 10 || strlen($phone) > 12) {
            echo json_encode(["status" => 0, "errorcode" => 1, "description" => "Số điện thoại không hợp lệ"]);
            exit();
        }
        if (empty($message)) {
            echo json_encode(["status" => 0, "errorcode" => 1, "description" => "Nội dung tin nhắn không hợp lệ"]);
            exit();
        }

        global $current_user;
        $obj = new SMS();
        $json = $obj->send($phone, $message);

        $arr = json_decode($json, true);

        if($arr !== false && !is_null($arr) && $arr['status'] == 1) {
            // $ec_sms_logs = new EC_SMS_Logs();
            // $ec_sms_logs->id = '';
            // $ec_sms_logs->send_from     = $obj->SENDER;
            // $ec_sms_logs->send_to       = $phone;
            // $ec_sms_logs->content       = $message;
            // $ec_sms_logs->type          = $type;
            // $ec_sms_logs->message_type  = 'transaction';
            // $ec_sms_logs->data          = $json;
            // $ec_sms_logs->status        = 'done';
            // $ec_sms_logs->send_date     = date("Y-m-d H:i:s", strtotime('-7 hours')); // Lưu xuống db giảm 7 tiếng
            // $ec_sms_logs->parent_id     = $parent_id;
            // $ec_sms_logs->parent_type   = $parent_type;
            // $ec_sms_logs->description   = "Gửi tin nhắn " . ($direction == '0' ? "lượt đi" : "lượt về");
            // $ec_sms_logs->assigned_user_id = $current_user->id;
            // $ec_sms_logs->save();

            $m = new EC_Messages();
            $m->id          = '';
            $m->send_from   = $obj->SENDER;
            $m->send_to     = $phone;
            $m->content     = $message;
            $m->type        = 'sms';
            $m->category    = 'transaction';
            $m->send_time   = date("Y-m-d H:i:s"); // Lưu xuống db giảm 7 tiếng
            $m->parent_type = $parent_type;
            $m->parent_id   = $parent_id;
            $m->response    = $json;
            $m->status      = 'done';
            $m->description = "Gửi tin nhắn " . ($direction == '0' ? "lượt đi" : "lượt về");
            $m->assigned_user_id = $current_user->id;
            $m->save();
        }

        echo $json;
        exit();
    }
    // elseif($type == 'send_sms_list_static') {
    //     $record_id = isset($_POST['record_id']) ? global_test_input($_POST['record_id']) : '';
    //     if(empty($record_id)) {
    //         echo json_encode([
    //             "Status" => -1,
    //             "Description" => "Dữ liệu gửi tin không hợp lệ"
    //         ]);
    //         exit();
    //     }

    //     $sms_log = new EC_SMS_Logs();
    //     $sms_log->retrieve($record_id);
    //     if((int)((strtotime($sms_log->send_date) - time()) / 3600) < 24) {
    //         echo json_encode([
    //             "Status" => -1,
    //             "Description" => "Yêu cầu lên lịch gửi tin trước 24 tiếng"
    //         ]);
    //         exit();
    //     }

    //     if(isset($sms_log->id) && !empty($sms_log->id)) {
    //         $obj = new SMS();
    //         $json = $obj->send_list_static($sms_log->name, $sms_log->id, $sms_log->send_date, $sms_log->content, json_decode(html_entity_decode($sms_log->data), true));
    //         if(substr($json, -1) == 'n') $json = substr($json, 0, -1); // Fix error json

    //         if($json) {
    //             $arr = json_decode($json, true);

    //             if(isset($arr['Status']) && $arr['Status'] == 1) {
    //                 $sms_log->status = 'scheduled';
    //                 $sms_log->campaign_id = $arr['CampaignId'];
    //                 $sms_log->save();
    //             }

    //             echo $json;
    //         }
    //         else echo json_encode([
    //             "Status" => -1,
    //             "Description" => "Gửi tin thất bại",
    //             "json_static" => $json
    //         ]);
    //         exit();
    //     }
    //     else {
    //         echo json_encode([
    //             "Status" => -1,
    //             "Description" => "Dữ liệu gửi tin không hợp lệ"
    //         ]);
    //         exit();
    //     }
    // }
    // elseif($type == 'send_sms_list_dynamic') {
    //     $record_id = isset($_POST['record_id']) ? global_test_input($_POST['record_id']) : '';
    //     if(empty($record_id)) {
    //         echo json_encode([
    //             "Status" => -1,
    //             "Description" => "Dữ liệu gửi tin không hợp lệ"
    //         ]);
    //         exit();
    //     }

    //     $sms_log = new EC_SMS_Logs();
    //     $sms_log->retrieve($record_id);
    //     if((int)((strtotime($sms_log->send_date) - time()) / 3600) < 24) {
    //         echo json_encode([
    //             "Status" => -1,
    //             "Description" => "Yêu cầu lên lịch gửi tin trước 24 tiếng"
    //         ]);
    //         exit();
    //     }

    //     if(isset($sms_log->id) && !empty($sms_log->id)) {
    //         $obj = new SMS();
    //         $json = trim($obj->send_list_dynamic($sms_log->name, $sms_log->id, $sms_log->send_date, $sms_log->content, json_decode(html_entity_decode($sms_log->data), true)));
    //         if(substr($json, -1) == 'n') $json = substr($json, 0, -1); // Fix error json
            
    //         if($json) {
    //             $arr = json_decode($json, true);

    //             if(isset($arr['Status']) && $arr['Status'] == 1) {
    //                 $sms_log->status = 'scheduled';
    //                 $sms_log->campaign_id = $arr['CampaignId'];
    //                 $sms_log->save();
    //             }

    //             echo $json;
    //         }
    //         else echo json_encode([
    //             "Status" => -1,
    //             "Description" => "Gửi tin thất bại",
    //             "json" => $json
    //         ]);
    //         exit();
    //     }
    //     else {
    //         echo json_encode([
    //             "Status" => -1,
    //             "Description" => "Dữ liệu gửi tin không hợp lệ"
    //         ]);
    //         exit();
    //     }
    // }
}