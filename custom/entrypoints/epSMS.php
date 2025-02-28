<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once('modules/EC_Messages/SMS.php');
    $type = isset($_POST['type']) ? $_POST['type'] : "";

    if($type == 'sms' || $type == 'send_sms') {
        $direction   = isset($_POST['direction']) ? global_test_input($_POST['direction']) : '';
        $phone       = isset($_POST['phone']) ? global_test_input($_POST['phone']) : '';
        $messessage     = isset($_POST['message']) ? $_POST['message'] : '';
        $parent_id   = isset($_POST['parent_id']) ? global_test_input($_POST['parent_id']) : '';
        $parent_type = isset($_POST['parent_type']) ? global_test_input($_POST['parent_type']) : '';

        if(empty($phone) || strlen($phone) < 10 || strlen($phone) > 12) {
            echo json_encode(["status" => 0, "errorcode" => 1, "description" => "Số điện thoại không hợp lệ"]);
            exit();
        }
        if (empty($messessage)) {
            echo json_encode(["status" => 0, "errorcode" => 1, "description" => "Nội dung tin nhắn không hợp lệ"]);
            exit();
        }

        global $current_user;
        $obj = new SMS();
        $json = $obj->send($phone, $messessage);

        $arr = json_decode($json, true);

        if($arr !== false && !is_null($arr) && $arr['status'] == 1) {
            $mess = new EC_Messages();
            $mess->id          = '';
            $mess->send_from   = $obj->SENDER;
            $mess->send_to     = $phone;
            $mess->content     = $messessage;
            $mess->type        = 'sms';
            $mess->category    = 'transaction';
            $mess->send_time   = date("Y-m-d H:i:s", strtotime('-7 hours')); // Lưu xuống db giảm 7 tiếng
            $mess->parent_type = $parent_type;
            $mess->parent_id   = $parent_id;
            $mess->response    = $json;
            $mess->status      = 'done';
            $mess->description = "Gửi tin nhắn " . ($direction == '0' ? "lượt đi" : "lượt về");
            $mess->assigned_user_id = $current_user->id;
            $mess->save();
        }

        echo $json;
        exit();
    }
    elseif($type == 'sms_campaign_static') {
        $record_id = isset($_POST['record_id']) ? global_test_input($_POST['record_id']) : '';
        if(empty($record_id)) {
            echo json_encode([
                "Status" => -1,
                "Description" => "Dữ liệu gửi tin không hợp lệ"
            ]);
            exit();
        }

        $mess = new EC_Messages();
        $mess->retrieve($record_id);
        if((int)((strtotime($mess->send_time) - time()) / 3600) < 24) {
            echo json_encode([
                "Status" => -1,
                "Description" => "Yêu cầu lên lịch gửi tin trước 24 tiếng"
            ]);
            exit();
        }

        if(isset($mess->id) && !empty($mess->id)) {
            $obj = new SMS();
            $json = $obj->send_list_static($mess->name, $mess->id, $mess->send_time, $mess->content, json_decode(html_entity_decode($mess->data), true));
            if(substr($json, -1) == 'n') $json = substr($json, 0, -1); // Fix error json

            if($json) {
                // $arr = json_decode($json, true);
                // if(isset($arr['Status']) && $arr['Status'] == 1) {
                //     $mess->status = 'scheduled';
                //     $mess->campaign_id = $arr['CampaignId'];
                //     $mess->save();
                // }

                $mess->response = $json;
                $mess->save();

                echo $json;
            }
            else echo json_encode([
                "Status" => -1,
                "Description" => "Gửi tin thất bại",
                "json_static" => json_decode($json, true)
            ]);
            exit();
        }
        else {
            echo json_encode([
                "Status" => -1,
                "Description" => "Dữ liệu gửi tin không hợp lệ"
            ]);
            exit();
        }
    }
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