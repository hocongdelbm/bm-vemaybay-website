<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once('modules/EC_SMS_Logs/Zalo.php');
    global $db, $current_user;
    $action = isset($_POST['action']) ? $_POST['action'] : "";

    if($action == 'send_zns') {
        $phone          = isset($_POST['phone']) ? $_POST['phone'] : "";
        $type_zns       = isset($_POST['type_zns']) ? $_POST['type_zns'] : "";
        $parent_id      = isset($_POST['parent_id']) ? $_POST['parent_id'] : "";
        $template_data  = isset($_POST['template_data']) ? str_replace('&quot;', '"', $_POST['template_data']) : "";

        if(empty($phone) || empty($type_zns) || empty($template_data) || empty($parent_id)) {
            echo json_encode(array(
                "code"      => -1,
                "message"   => "ERROR: Dữ liệu cung cấp không hợp lệ",
                "data"      => "Invalid params"
            ));
            exit();
        }

        $zalo = new Zalo();
        $template_id = $zalo->get_template_id_zns($type_zns);
        $json = $zalo->send_zns($phone, $template_id, $template_data);
        $arr = json_decode($json, true);

        if(isset($arr['error']) && $arr['error'] == 0) {
            $ec_sms_logs = new EC_SMS_Logs();
            $ec_sms_logs->send_from     = 'OA Travelpass';
            $ec_sms_logs->send_to       = $phone;
            $ec_sms_logs->content       = ucfirst($zalo->get_template_name_zns($template_id));
            $ec_sms_logs->type          = 'send_zalo_zns';
            $ec_sms_logs->message_type  = 'transaction';
            $ec_sms_logs->send_date     = date('Y-m-d H:i:s');
            $ec_sms_logs->parent_type   = 'EC_Flight_Bookings';
            $ec_sms_logs->parent_id     = $parent_id;
            $ec_sms_logs->campaign_id   = isset($arr['data']['msg_id']) ? $arr['data']['msg_id'] : '';
            $ec_sms_logs->data          = $json;
            $ec_sms_logs->status        = 'done';
            $ec_sms_logs->assigned_user_id = $current_user->id;
            $ec_sms_logs->save();

            $fullname = $current_user->last_name.' '.$current_user->first_name;
            $zalo->send_to_telegram("<b>".$fullname.'</b>: Gửi '.$zalo->get_template_name_zns($template_id).' đến Zalo <b>' . $phone .'</b>');

            echo json_encode(array(
                "code"      => 1,
                "message"   => "Gửi tin nhắn thành công",
                "data"      => $json
            ));
        }
        else {
            $ec_sms_logs = new EC_SMS_Logs();
            $ec_sms_logs->send_from     = 'OA Travelpass';
            $ec_sms_logs->send_to       = $phone;
            $ec_sms_logs->content       = ucfirst($zalo->get_template_name_zns($template_id));
            $ec_sms_logs->type          = 'send_zalo_zns';
            $ec_sms_logs->message_type  = 'transaction';
            $ec_sms_logs->send_date     = date('Y-m-d H:i:s');
            $ec_sms_logs->parent_type   = 'EC_Flight_Bookings';
            $ec_sms_logs->parent_id     = $parent_id;
            // $ec_sms_logs->campaign_id   = isset($arr['data']['msg_id']) ? $arr['data']['msg_id'] : '';
            $ec_sms_logs->data          = $json;
            $ec_sms_logs->status        = 'fail';
            $ec_sms_logs->assigned_user_id = $current_user->id;
            $ec_sms_logs->save();

            $message = "Gửi tin nhắn thất bại";
            if($arr['message'] && strpos($arr['message'], "Zalo account not existed") !== false) $message = "Số điện thoại không có Zalo";

            echo json_encode(array(
                "code"      => 0,
                "message"   => $message,
                "data"      => $json
            ));
        }
    }
    elseif($action == 'send_request_user_info') {
        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";

        if(empty($zalo_id)) exit();

        $zalo = new Zalo();
        echo $zalo->send_request_user_info($zalo_id);
    }
    elseif($action == 'send_promotion'){
        $user_id    = isset($_POST['zalo_id']) ? trim($_POST['zalo_id']) : "";
        $banner     = isset($_POST['banner']) ? trim($_POST['banner']) : "";
        $header     = isset($_POST['header']) ? trim($_POST['header']) : "";
        $text       = isset($_POST['text']) ? trim($_POST['text']) : "";
        $table      = isset($_POST['table']) ? $_POST['table'] : array();
        $text2      = isset($_POST['text2']) ? trim($_POST['text2']) : "";
        $buttons    = isset($_POST['buttons']) ? $_POST['buttons'] : array();

        $phone      = isset($_POST['phone']) ? $_POST['phone'] : "";
        $parent_id  = isset($_POST['parent_id']) ? $_POST['parent_id'] : "";
        $type_zns   = isset($_POST['type_zns']) ? $_POST['type_zns'] : "";
        
        $template_type   = isset($_POST['template_type']) ? $_POST['template_type'] : "";
        $template_text = '';
        if($template_type == 1){
            $template_text = 'Voucher';
        }

        if(empty($user_id) || empty($banner) || empty($header) || empty($text) || count($table) == 0) {
            echo json_encode(array(
                "code"      => -1,
                "message"   => "ERROR: Dữ liệu cung cấp không hợp lệ",
                "data"      => "Invalid params"
            ));
            exit();
        }

        $zalo   = new Zalo();
        $json   = $zalo->send_promotion($user_id, $banner, $header, $text, $table, $text2, $buttons);
        $data   = json_encode([
            "user_id" => $user_id, 
            "banner" => $banner, 
            "header" => $header, 
            "text" => $text,
            "table" => $table,
            "text2" => $text2,
            "buttons" => $buttons
        ]);

        $arr    = json_decode($json, true);

        if(isset($arr['error']) && $arr['error'] == 0) {
            $ec_sms_logs = new EC_SMS_Logs();
            $ec_sms_logs->send_from     = 'OA Travelpass';
            $ec_sms_logs->send_to       = $phone;
            $ec_sms_logs->content       = 'Thông tin khuyến mãi ' . $template_text;
            $ec_sms_logs->type          = 'send_zalo_promotion';
            $ec_sms_logs->message_type  = 'transaction';
            $ec_sms_logs->send_date     = date('Y-m-d H:i:s');
            $ec_sms_logs->parent_type   = 'EC_Flight_Bookings';
            $ec_sms_logs->parent_id     = $parent_id;
            $ec_sms_logs->campaign_id   = isset($arr['data']['message_id']) ? $arr['data']['message_id'] : '';
            $ec_sms_logs->data          = $data;
            $ec_sms_logs->status        = 'done';
            $ec_sms_logs->assigned_user_id = $current_user->id;
            $ec_sms_logs->save();

            $fullname = $current_user->last_name.' '.$current_user->first_name;
            $zalo->send_to_telegram("<b>".$fullname.'</b>: Gửi thông tin khuyến mãi '.$template_text.' đến Zalo <b>' . $phone .'</b>');

            echo json_encode(array(
                "code"      => 1,
                "message"   => "Gửi tin nhắn thành công",
                "data"      => $json
            ));
        } else {
            echo json_encode(array(
                "code"      => 0,
                "message"   => "Gửi tin nhắn khuyến mãi thất bại",
                "data"      => $json
            ));
        }
    }
    elseif($action == 'send_broadcast') {
        $record_id = isset($_POST['record_id']) ? trim($_POST['record_id']) : "";

        $ec_sms_logs = new EC_SMS_Logs();
        $ec_sms_logs->retrieve($record_id);
        $filters = json_decode(html_entity_decode($ec_sms_logs->data), true);

        if(is_null($filters) || empty($filters) || !isset($filters['post']) || empty($filters['post'])) {
            echo json_encode(array(
                "error"      => 0,
                "message"   => "Dữ liệu cung cấp không hợp lệ",
                "data"      => "Invalid params"
            ));
            exit();
        }

        $post_id = $filters['post'];
        if(empty($filters['ages'])) unset($filters['ages']);
        if(empty($filters['locations'])) unset($filters['locations']);
        if(empty($filters['cities'])) unset($filters['cities']);
        else unset($filters['locations']);
        if(empty($filters['platform'])) unset($filters['platform']);

        $zalo = new Zalo();
        $json = $zalo->send_broadcast($post_id, $filters);
        $arr = json_decode($json, true);

        if(isset($arr['error']) && $arr['error'] == 0) {
            $ec_sms_logs->status = 'done';
            $ec_sms_logs->send_date = date('Y-m-d H:i:s');
            $ec_sms_logs->campaign_id = isset($arr['data']['message_id']) ? $arr['data']['message_id'] : '';
            $ec_sms_logs->assigned_user_id = $current_user->id;
            $ec_sms_logs->save();
        }

        echo $json;
    }
    elseif($action == 'get_user_info') {
        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";

        if(empty($zalo_id)) exit();

        $zalo = new Zalo();
        echo $zalo->get_user_info($zalo_id);
    }
    elseif($action == 'get_quota_user') {
        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";

        if(empty($zalo_id)) exit();

        $zalo = new Zalo();
        echo $zalo->get_quota_user($zalo_id);
    }
    elseif($action == 'get_post') {
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : "";

        if(empty($post_id)) exit();

        $zalo = new Zalo();
        echo $zalo->get_post($post_id);
    }

    exit();
}

echo json_encode(array(
    "error"   => 1,
    "code"    => -1,
    "message" => "Dữ liệu cung cấp không hợp lệ",
    "data"    => "Invalid SERVER_REQUEST_METHOD"
));
exit();