<?php
require_once "custom/entrypoints/entryClass.php";
require_once "custom/include/helpers/api/APIZaloOA.php";

/**
 * Class entryZaloOAClass
 */
class entryZaloOAClass extends entryClass {
    /**
     * Get zalo user info
     * 
     * @param array $params ['oa_id', 'zalo_id']
     * @return array
     */
    public function getUserInfo($params = []) {
        $oa_id = $params["oa_id"] ?? "";
        $zalo_id = $params["zalo_id"] ?? "";

        $zaloContact = new EC_Zalo_Contacts();
        $user_data = $zaloContact->get_zalo_user_info($zalo_id, $oa_id);

        return [
            "status" => !empty($user_data) ? 1 : 0,
            "message" => "",
            "data" => $user_data
        ];
    }

    /**
     * Get list user id
     * 
     * @param array $params
     * @return array
     */
    public function getListUser($params = []) {
        $oa_id  = $params['oa_id'] ?? "";
        $offset = (int)($params['offset'] ?? 0);
        $count  = (int)($params['count'] ?? 50);
        $tag_name = $params['tag_name'] ?? '';
        $last_interaction_period = $params['last_interaction_period'] ?? '';
        $value = $params['value'] ?? '';

        if(in_array($value, ['L7D'])) $last_interaction_period = $value;
        elseif(!empty($value)) $tag_name = $value;

        $zaloOA = new APIZaloOA('', $oa_id);
        $json = $zaloOA->get_list_user($offset, $count, $last_interaction_period, null, $tag_name);
        $arr  = json_decode($json, true);

        if(isset($arr['error']) && $arr['error'] == 0) {
            if(empty($arr['data']['users'])) {
                return ["status" => 0, "message" => "Not found", "data" => []];
            }

            $results = [];
            $zaloContact = new EC_Zalo_Contacts();
            $zaloMessage = new EC_Zalo_Messages();
            foreach($arr['data']['users'] as $u) {
                $zalo_id = $u['user_id'];
                $user_info = $zaloContact->get_zalo_user_info($zalo_id, $oa_id);

                if(!empty($user_info)) {
                    $lastest_message  = $zaloMessage->get_lastest_message_user($oa_id, $zalo_id);
                    $last_interaction = $user_info['user_last_interaction_date']; // d-m-Y H:i:s

                    if($value == 'L7D') {
                        $current_date = date('d-m-Y H:i:s');
                        $count_day = (strtotime($current_date) - strtotime($last_interaction)) / 3600 / 24;

                        if($count_day < 6 || $count_day > 7) continue;

                        // Message
                        if(!empty($lastest_message)) $message = $lastest_message['message'];
                        else if($count_day == 7) $message = 'Sắp hết hạn tương tác';
                        else $message = 'Còn 1 ngày';
                    }
                    else {
                        // Message
                        if(!empty($lastest_message)) $message = $lastest_message['message'];
                        else $message = 'Tương tác cuối vào ' . date('d/m/Y H:i', strtotime($user_info['user_last_interaction_date']));
                    }

                    if(empty($lastest_message)) {
                        $lastest_message['message_type'] = 'custom';
                        $lastest_message['type'] = 'custom';
                        $lastest_message['src'] = 1;
                        $lastest_message['timestamp'] = strtotime($last_interaction) * 1000;
                        $lastest_message['from_id'] = $zalo_id;
                        $lastest_message['to_id'] = $oa_id;
                    }
                    $lastest_message['message'] = $message;

                    $key = $lastest_message['timestamp'] . '_' . $zalo_id;
                    $results[$key] = [
                        'message_info' => $lastest_message,
                        'user_info' => $user_info
                    ];
                }
            }

            $value == 'L7D' ? ksort($results) : krsort($results);
            return [
                "status" => 1,
                "message" => "Success",
                "data" => $results
            ];
        }

        return ["status" => 0, "message" => "Not found", "response" => $arr];
    }

    /**
     * Get list recent messages from each user
     * 
     * @param array $params
     * @return array
     */
    public function getListRecentMessages($params = []) {
        $oa_id = $params["oa_id"] ?? "";
        $timestamp = $params['timestamp'] ?? 0;
        $limit_record = $params['limit_record'];
        $current_list_user = isset($params['current_list_user']) && !empty($params['current_list_user']) ? array_unique(explode(',', $params['current_list_user'])) : []; // array

        $zaloMessage = new EC_Zalo_Messages();
        $results = $zaloMessage->get_list_recent_messages($oa_id, $timestamp, $current_list_user, $limit_record);
        $results['status'] == isset($results['data']) && !empty($results['data']) ? 1 : 0;
        return $results;
    }

    /**
     * Get messages of user
     * 
     * @param array $params
     * @return array Not contain status key
     */
    public function getMessages($params = []) {
        $oa_id = $params["oa_id"] ?? "";
        $zalo_id = $params["zalo_id"] ?? "";
        $zalo_phone = $params["zalo_phone"] ?? "";
        $offset = (int)($params['offset'] ?? 0);
        $is_get_user_info = (int)($params['is_get_user_info'] ?? 1);
        $limit_message = $params['limit_message'];

        $zaloMessage = new EC_Zalo_Messages();
        return $zaloMessage->get_messages($oa_id, $zalo_id , $zalo_phone, $offset, $is_get_user_info, $limit_message);
    }

    /**
     * Send consultation message
     * 
     * @param array $params
     * @return array
     */
    public function sendMessage($params = []) {
        $oa_id   = $params['oa_id'] ?? "";
        $zalo_id = $params['zalo_id'] ?? "";
        $type    = $params['type'] ?? "text";
        $data    = ['text' => $params['text'] ?? ""];

        if(empty($zalo_id) || empty($oa_id) || empty($type)) {
            return [
                "status" => 0,
                "message" => "Dữ liệu không hợp lệ",
                "data" => $params
            ];
        }

        $zaloOA = new APIZaloOA('', $oa_id);

        // Prepare body request (data)
        if ($type == 'image') {
            // Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_name = $_FILES['image']['name']; // name.ext
                $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    
                // Check extension
                if(!in_array($ext, $zaloOA->get_file_extension('image'))) {
                    return [
                        "status" => 0,
                        "message" => "Không hỗ trợ định dạng $ext",
                        "description" => "Chỉ hỗ trợ định dạng " . implode(',', $zaloOA->get_file_extension('image')),
                        "data" => null,
                    ];
                }
    
                // Check size
                if($ext == 'gif' && $_FILES["image"]["size"] > 5000000) {
                    return [
                        "status" => 0,
                        "message" => "Dung lượng ảnh quá lớn",
                        "description" => "Dung lượng tối đa 5MB cho định dạng .gif",
                        "data" => null,
                    ];
                }
                elseif($ext != 'gif' && $_FILES["image"]["size"] > 1000000) {
                    return [
                        "status" => 0,
                        "message" => "Dung lượng ảnh quá lớn",
                        "description" => "Dung lượng tối đa 1MB cho định dạng jpg, png",
                        "data" => null,
                    ];
                }
                
                $json_upload = $zaloOA->upload($_FILES['image']['tmp_name'], $ext, $image_name);
                $arr_upload = json_decode($json_upload, true);
                
                if(isset($arr_upload['error']) && $arr_upload['error'] == 0) {
                    $attachment_id = isset($arr_upload['data']['attachment_id']) ? $arr_upload['data']['attachment_id'] : '';
                    $data['element'] = [
                        "media_type" => $ext == 'gif' ? 'gif' : 'image',
                        "attachment_id" => $attachment_id
                    ];
                }
                else {
                    return [
                        "status" => 0,
                        "message" => "Gửi ảnh thất bại, vui lòng thử lại",
                        "data" => null,
                        "description" => $arr_upload,
                    ];
                }
            }
            else {
                return [
                    "status" => 0,
                    "message" => "Gửi ảnh thất bại, vui lòng thử lại",
                    "data" => null,
                    "description" => $_FILES,
                ];
            }
        }
        elseif ($type == 'file') {
            // Upload
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['file']['name']; // name.ext
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
                // Check extension
                if(!in_array($ext, $zaloOA->get_file_extension('file'))) {
                    return [
                        "status" => 0,
                        "message" => "Không hỗ trợ định dạng $ext",
                        "data" => null,
                        "description" => "Các định dạng hỗ trợ: ". implode(', ', $zaloOA->get_file_extension('file'))
                    ];
                }
    
                // Check size
                if($_FILES["file"]["size"] > 5000000) {
                    return [
                        "status" => 0,
                        "message" => "Dung lượng file tối đa 5MB",
                        "data" => null,
                    ];
                }
                
                $json_upload = $zaloOA->upload($_FILES["file"]["tmp_name"], $ext, $file_name);
                $arr_upload = json_decode($json_upload, true);
                
                if(isset($arr_upload['error']) && $arr_upload['error'] == 0) {
                    $data['token'] = isset($arr_upload['data']['token']) ? $arr_upload['data']['token'] : '';
                }
                else {
                    return [
                        "status" => 0,
                        "message" => "Gửi file thất bại, vui lòng thử lại",
                        "data" => null,
                        "description" => $arr_upload
                    ];
                }
            }
            else {
                return [
                    "status" => 0,
                    "message" => "Gửi file thất bại, vui lòng thử lại",
                    "data" => null,
                    "description" => $_FILES
                ];
            }
        }
        elseif ($type == 'request_user_info') {
            $data['element'] = $zaloOA->get_template_handmade($type);
        }

        // Tin nhắn text reply
        if(isset($_POST['quote_message_id'])) $data['quote_message_id'] = $_POST['quote_message_id'];

        // Send
        $json_message = $zaloOA->send_consultation_message($type, $zalo_id, $data);
        $arr_message = json_decode($json_message, true);

        if(isset($arr_message['error']) && $arr_message['error'] == 0) {
            $zalomes = new EC_Zalo_Messages();
            $cost = $zalomes->handle_quota_and_calculate_cost($arr_message['data'], $zalo_id, $oa_id);

            $zalomes->message_id        = $arr_message['data']['message_id'] ?? '';
            $zalomes->src               = 0;
            $zalomes->from_id           = $zaloOA->get_oa_id();
            $zalomes->to_id             = $zalo_id;
            $zalomes->timestamp         = round(microtime(true) * 1000); // Milliseconds
            $zalomes->type              = 'consultation';
            $zalomes->sub_type          = $type == 'image' ? ($ext == 'gif' ? 'gif' : 'image') : $type;
            $zalomes->description       = $data['text'] ?? '';
            $zalomes->quote_message_id  = $data['quote_message_id'] ?? '';
            $zalomes->cost              = $cost;
            $zalomes->response          = trim($json_message);
            $zalomes->assigned_user_id  = $this->currentUser->id;
            $zalomes->save();
        }

        // Replace key error to status
        $arr_message['status'] = !$arr_message['error'];
        unset($arr_message['error']);
        return $arr_message;
    }

    /**
     * Send ZBS template message (Previous is ZNS)
     * 
     * @param array $params
     * @return array
     */
    public function sendTemplateMessage($params = []) {
        try {
            $phoneNumber   = $params["phoneNumber"] ?? "";
            $type          = $params["type"] ?? ""; // ZNS type
            $parentId      = $params["parentId"] ?? "";
            $parentType    = $params["parentType"] ?? "";
            $templateData  = $params['templateData'] ?? [];

            if(empty($phoneNumber) || empty($type) || empty($templateData)) {
                return [
                    "status" => 0,
                    "message" => "Dữ liệu cung cấp không hợp lệ",
                    "data" => null
                ];
            }

            $app_id = "1450532072851803077";
            $oa_id = $sugar_config['zalo_config']['oa_id_default'] ?? '';
            $zaloOA = new APIZaloOA($app_id, $oa_id);
            
            $template_id    = $zaloOA->get_template_id($type);
            $template_name  = $zaloOA->get_template_name($template_id);
            
            // Send by uid
            if($zaloOA->check_template_can_send_by_uid($template_id)) {
                $zaloContact = new EC_Zalo_Contacts();
                $listUsers = $zaloContact->search_zalo_user_by_phone($phoneNumber, $zaloOA->get_oa_id());

                if(count($listUsers) == 1) {
                    $uid  = $listUsers[0]['user_id'] ?? '';
                    $json = $zaloOA->send_template_message_by_uid($uid, $template_id, $templateData);
                    $arr  = json_decode($json, true);
                    if(isset($arr['error']) && $arr['error'] == 1) {
                        $zalomes = new EC_Zalo_Messages();
                        $arr['data']['template_id'] = $template_id;
                        $cost = $zalomes->handle_quota_and_calculate_cost($arr['data'], $uid, $zaloOA->get_oa_id());

                        $zalomes->message_id    = $arr['data']['message_id'] ?? '';
                        $zalomes->src           = 0;
                        $zalomes->from_id       = $zaloOA->get_oa_id();
                        $zalomes->to_id         = $uid;
                        $zalomes->timestamp     = $arr['sent_time'] ?? round(microtime(true) * 1000);
                        $zalomes->type          = 'zbs';
                        $zalomes->sub_type      = $type;
                        $zalomes->description   = $template_name;
                        $zalomes->template_id   = $template_id;
                        $zalomes->data          = json_encode($templateData);
                        $zalomes->response      = trim($json);
                        $zalomes->booking_id    = $parentType == 'EC_Flight_Bookings' ? $parentId : "";
                        $zalomes->cost          = $cost;
                        $zalomes->assigned_user_id = $this->currentUser->id;

                        // Save notes
                        if($zalomes->save() || !empty($parentId)) {
                            $n = new Note();
                            $n->name        = "Gửi tin nhắn theo mẫu Zalo";
                            $n->description = "Gửi Zalo ZBS $template_name đến $phoneNumber";
                            $n->parent_type = $parentType;
                            $n->parent_id   = $parentId;
                            $n->assigned_user_id = $this->currentUser->id;
                            $n->save();
                        }
                    }

                    // Replace key error to status
                    $arr['status'] = !$arr['error'];
                    return $arr;
                }
            }

            // Send by phone number
            $json = $zaloOA->send_template_message_by_phone($phoneNumber, $template_id, $templateData, true);
            $arr = json_decode($json, true);
            if(isset($arr['error']) && $arr['error'] == 0) {
                $templateData['template_id'] = $template_id;

                // Save to zalo message
                try {
                    $msg_id     = $arr['data']['msg_id'] ?? '';
                    $timestamp  = $arr['data']['sent_time'] ?? round(microtime(true) * 1000);

                    $zalomes = new EC_Zalo_Messages();

                    $arr['data']['template_id'] = $template_id;
                    $cost = $zalomes->handle_quota_and_calculate_cost($arr['data'], '', $zaloOA->get_oa_id());

                    $zalomes->message_id    = $msg_id;
                    $zalomes->src           = 0;
                    $zalomes->from_id       = $zaloOA->get_oa_id();
                    $zalomes->to_id         = $phoneNumber;
                    $zalomes->timestamp     = $timestamp;
                    $zalomes->type          = 'zbs';
                    $zalomes->sub_type      = $type;
                    $zalomes->description   = $template_name;
                    $zalomes->template_id   = $template_id;
                    $zalomes->data          = json_encode($templateData);
                    $zalomes->response      = trim($json);
                    $zalomes->booking_id    = $parentType == 'EC_Flight_Bookings' ? $parentId : "";
                    $zalomes->assigned_user_id = $this->currentUser->id;

                    // Save notes
                    if($zalomes->save() || !empty($parentId)) {
                        $n = new Note();
                        $n->name        = "Gửi tin nhắn theo mẫu Zalo";
                        $n->description = "Gửi Zalo ZBS $template_name đến $phoneNumber";
                        $n->parent_type = $parentType;
                        $n->parent_id   = $parentId;
                        $n->assigned_user_id = $this->currentUser->id;
                        $n->save();
                    }
                }
                catch(Exception $e) {
                    $exceptionMessage = "{$e->getMessage()} on line {$e->getLine()} in {$e->getFile()}";
                    if($this->notificationChannel == 'Mattermost') {
                        $message = Mattermost::$line_separation;
                        $message .= Mattermost::markdownHeading("[ERROR] ZBS message saved failed");
                        $message .= "\n$exceptionMessage\n\n$json";
                        Mattermost::sendMessage($this->mattermostConfig['channel_id_logs'] ?? '', $message);
                    }
                    else {
                        $message = "<b>[ERROR] ZBS message saved failed</b>";
                        $message .= "\n$exceptionMessage\n<pre>$json</pre>";
                        $botToken   = $this->telegramConfig['bot_token'] ?? '';
                        $chatId     = $this->telegramConfig['chat_id'] ?? '';
                        $threadId   = $this->telegramConfig['thread_id_logs'] ?? '';
                        Telegram::sendMessage($message, $botToken, $chatId, $threadId);
                    }
                }
                
                if($type != 'cheap-flight') {
                    $fullname   = trim("{$this->currentUser->last_name} {$this->currentUser->first_name}");
                    $botToken   = $this->telegramConfig['zalo']['bot_token'] ?? '';
                    $chatId     = $this->telegramConfig['zalo']['chat_id'] ?? '';
                    $message    = "<b>$fullname</b> gửi mẫu tin $template_name đến Zalo <b>$phoneNumber</b>";
                    if($type == 'after-call-sale') $message = "<b>⚙️Auto:</b> $message";
                    if(!empty($parentId) && $parentType == 'EC_Flight_Bookings') {
                        $bklink = "https://".$zaloOA->get_domain()."/index.php?module={$parentType}&action=DetailView&record={$parentId}";
                        $message .= " - <a href='{$bklink}'>Booking</a>";
                    }
                    Telegram::sendMessage($message, $botToken, $chatId);
                }
            }
            else {
                $arr["message"] = $zaloOA->get_error_description($arr["error"] ?? "");
            }

            // Replace key error to status
            $arr['status'] = !$arr['error'];
            return $arr;
        }
        catch(Throwable $th) {
            $logId = LoggerHelper::generateLogId();
            $GLOBALS['log']->fatal("[{$logId}] {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return [
                "status" => 0,
                "message" => "Exception error $logId",
                "data" => null
            ];
        }
    }

    /**
     * Search zalo contact
     * 
     * @param array $params
     * @return array
     */
    public function searchZaloContact($params = []) {
        $oa_id          = global_test_input($params['oa_id'] ?? "");
        $search_value   = global_test_input($params['search_value'] ?? "");

        if(empty($search_value)) {
            return [
                "status" => 0,
                "message" => "Dữ liệu tìm kiếm không hợp lệ"
            ];
        }

        $zaloContact = new EC_Zalo_Contacts();
        $zaloMessage = new EC_Zalo_Messages();
        $listUserData = [];

        // Search by chat link
        if(filter_var($search_value, FILTER_VALIDATE_URL)) {
            parse_str(parse_url($search_value, PHP_URL_QUERY), $query);
            $zalo_id = $query['uid'] ?? '';
            if(!empty($zalo_id)) {
                $user_info = $zaloContact->get_zalo_user_info($zalo_id, $oa_id);
                if(is_array($user_info) && !empty($user_info)) $listUserData[] = $user_info;
            }
        }
        // Search by phone
        elseif(is_numeric($search_value)) {
            $listUserData = $zaloContact->search_zalo_user_by_phone($search_value);
        }
        // Search by alias
        else {
            $listUserData = $zaloContact->search_zalo_user_by_alias($search_value);
        }

        $results = [];
        if(!empty($listUserData)) {
            foreach ($listUserData as $key => $userData) {
                // Message info
                $lastest_message = $zaloMessage->get_lastest_message_user($oa_id, $userData['user_id']);
                if(empty($lastest_message)) {
                    $lastest_message['message_type'] = 'custom';
                    $lastest_message['type'] = 'custom';
                    $lastest_message['message'] = 'Tương tác cuối vào ' . date('d/m/Y H:i', strtotime($userData['user_last_interaction_date']));
                    $lastest_message['src'] = 1;
                    $lastest_message['timestamp'] = strtotime($userData['user_last_interaction_date']) * 1000;
                    $lastest_message['from_id'] = $userData['zalo_id'];
                    $lastest_message['to_id'] = $oa_id;
                }
    
                $results[] = [
                    'message_info' => $lastest_message,
                    'user_info' => $userData
                ];
            }
        }

        return [
            "status" => 1,
            "message" => "Success",
            "data" => $results
        ];
    }
}