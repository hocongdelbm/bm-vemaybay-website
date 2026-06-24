<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once('modules/EC_Messages/SMS.php');
    $type = isset($_POST['type']) ? $_POST['type'] : "";

    if($type == 'sms' || $type == 'send_sms') {
        $direction   = isset($_POST['direction']) ? global_test_input($_POST['direction']) : '';
        $phone       = isset($_POST['phone']) ? global_test_input($_POST['phone']) : '';
        $messessage  = isset($_POST['message']) ? $_POST['message'] : '';
        $parent_id   = isset($_POST['parent_id']) ? global_test_input($_POST['parent_id']) : '';
        $parent_type = isset($_POST['parent_type']) ? global_test_input($_POST['parent_type']) : '';

        if(empty($phone) || strlen($phone) < 10 || strlen($phone) > 12) {
            echo json_encode(["status" => 0, "errorcode" => 53, "description" => "Số điện thoại không hợp lệ"]);
            exit();
        }
        if (empty($messessage)) {
            echo json_encode(["status" => 0, "errorcode" => 55, "description" => "Nội dung tin nhắn không hợp lệ"]);
            exit();
        }

        global $current_user;
        $sms = new SMS();
        $json = $sms->send($phone, $messessage);

        $arr = json_decode($json, true);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $mess = new EC_Messages();
            $mess->send_from   = $sms->SENDER;
            $mess->send_to     = $phone;
            $mess->content     = $messessage;
            $mess->type        = "sms";
            $mess->category    = "transaction";
            $mess->send_time   = date("Y-m-d H:i:s");
            $mess->parent_type = $parent_type;
            $mess->parent_id   = $parent_id;
            $mess->response    = $json;
            $mess->cost        = $sms->caculate_fee('sms', $messessage);
            $mess->status      = "done";
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
                "Status" => 0,
                "Code" => null,
                "Description" => "Dữ liệu gửi tin không hợp lệ (No record id)"
            ]);
            exit();
        }

        $mess = new EC_Messages();
        $mess->retrieve($record_id);
        if((int)((strtotime($mess->send_time) - time()) / 60) < 10) {
            echo json_encode([
                "Status" => 0,
                "Code" => null,
                "Description" => "Tin được gửi phải lên lịch trước 10 phút"
            ]);
            exit();
        }

        if(isset($mess->id) && !empty($mess->id)) {
            $sms = new SMS();
            $json = $sms->send_list_static($mess->send_time, $mess->content, json_decode(html_entity_decode($mess->data), true), $mess->name, $mess->id);
            if(substr($json, -1) == 'n') $json = substr($json, 0, -1); // Fix error json

            if($json && strlen($json) > 2) {
                $arr = json_decode($json, true);
                if(isset($arr['Status']) && $arr['Status'] == 1) {
                    $mess->status = 'scheduled';
                }
                $mess->response = $json;
                $mess->save();
                echo $json;
            }
            else {
                echo json_encode([
                    "Status"        => 0,
                    "Code"          => null,
                    "Description"   => "Gửi tin thất bại",
                    "Response"      => json_decode($json, true)
                ]);
            }
            exit();
        }
        else {
            echo json_encode([
                "Status" => 0,
                "Code" => null,
                "Description" => "Dữ liệu gửi tin không hợp lệ (Not found record $record_id)"
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