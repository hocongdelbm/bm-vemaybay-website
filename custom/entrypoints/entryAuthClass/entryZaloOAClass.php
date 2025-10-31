<?php
require_once "custom/entrypoints/entryAuthClass/entryClass.php";
require_once "custom/include/helpers/api/APIZaloOA.php";
require_once "custom/include/helpers/api/APIOMNI.php";

/**
 * Class entryZaloOAClass
 */
class entryZaloOAClass extends entryClass {
    /**
     * Get zalo user info
     * 
     * @param array $params
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
     * Get list recent messages from each user
     * 
     * @param array $params
     * @return array
     */
    public function getListRecentMessages($params = []) {
        $oa_id = $params["oa_id"] ?? "";
        $timestamp = $params['timestamp'] ?? 0;
        $current_list_user = isset($params['current_list_user']) && !empty($params['current_list_user']) ? array_unique(explode(',', $params['current_list_user'])) : []; // array

        $zaloMessage = new EC_Zalo_Messages();
        $results = $zaloMessage->get_list_recent_messages($oa_id, $timestamp, $current_list_user);
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

        $zaloMessage = new EC_Zalo_Messages();
        return $zaloMessage->get_messages($oa_id, $zalo_id , $zalo_phone, $offset, $is_get_user_info);
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
                "data" => null
            ];
        }

        $zalOA = new APIZaloOA($oa_id);

        // Prepare body request (data)
        if ($type == 'image') {
            // Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_name = $_FILES['image']['name']; // name.ext
                $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    
                // Check extension
                if(!in_array($ext, $zalOA->get_file_extension('image'))) {
                    return [
                        "status" => 0,
                        "message" => "Không hỗ trợ định dạng $ext",
                        "description" => "Chỉ hỗ trợ định dạng " . implode(',', $zalOA->get_file_extension('image')),
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
                
                $json_upload = $zalOA->upload($_FILES['image']['tmp_name'], $ext, $image_name);
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
                if(!in_array($ext, $zalOA->get_file_extension('file'))) {
                    return [
                        "status" => 0,
                        "message" => "Không hỗ trợ định dạng $ext",
                        "data" => null,
                        "description" => "Các định dạng hỗ trợ: ". implode(', ', $zalOA->get_file_extension('file'))
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
                
                $json_upload = $zalOA->upload($_FILES["file"]["tmp_name"], $ext, $file_name);
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
            $data['element'] = $zalOA->get_template($type);
        }

        // Tin nhắn text reply
        if(isset($_POST['quote_message_id'])) $data['quote_message_id'] = $_POST['quote_message_id'];

        // Send
        $json_message = $zalOA->send_consultation($type, $zalo_id, $data);
        $arr_message = json_decode($json_message, true);

        if(isset($arr_message['error']) && $arr_message['error'] == 0) {
            // Calculate cost
            $cost = 0;
            $quotaData = $arr_message['data']['quota'] ?? [];
            if(!empty($quotaData)) {
                try {
                    global $db;

                    switch ($quotaData['quota_type']) {
                        case 'reply': // Tin gửi ra là tin trong khung 8 tin 48h
                            // Get current quota user from db
                            $quotaInfo = $db->getOne("SELECT IFNULL(quota_info, '') FROM ec_zalo_contacts WHERE zalo_id = '{$zalo_id}' AND oa_id = '{$oa_id}' AND deleted = 0") ?? '';
                            if(!empty($quotaInfo)) $quotaInfo = json_decode($quotaInfo, true);
                            else $quotaInfo = [];

                            $quotaInfo['cs_reply'] = [
                                'remain' => $quotaData['remain'],
                                'total' => $quotaData['total']
                            ];

                            // Update new quota user to db
                            $quotaInfo = json_encode($quotaInfo);
                            $db->query("UPDATE ec_zalo_contacts SET quota_info = '{$quotaInfo}' WHERE zalo_id = '{$zalo_id}' AND oa_id = '{$oa_id}' AND deleted = 0");
                            break;

                        case 'sub_quota': // Tin gửi ra là tin nằm trong hạn mức miễn phí theo gói
                            // Get current quota oa from db
                            $quotaInfo = $db->getOne("SELECT IFNULL(quota_info, '') FROM ec_zalo WHERE id = '{$oa_id}' AND deleted = 0") ?? '';
                            $isUpdated = false;

                            if(!empty($quotaInfo)) {
                                $quotaInfo = json_decode($quotaInfo, true);
                                foreach($quotaInfo as $qKey => $qValue) {
                                    if($qValue['quota_type'] == 'sub_quota') {
                                        $quotaInfo[$qKey]['remain'] = $quotaData['remain'];
                                        $quotaInfo[$qKey]['total'] = $quotaData['total'];
                                        $quotaInfo[$qKey]['valid_through'] = date('d-m-Y', strtotime(str_replace("/","-", $quotaData['expired_date'])));
                                        $isUpdated = true;
                                        break;
                                    }
                                }
                            }
                            else $quotaInfo = [];

                            if(!$isUpdated) {
                                $quotaInfo[] = [
                                    "asset_id"      => "",
                                    "product_type"  => "cs",
                                    "quota_type"    => "sub_quota",
                                    "valid_through" => date('d-m-Y', strtotime(str_replace("/","-", $quotaData['expired_date']))),
                                    "total"         => $quotaData['total'],
                                    "remain"        => $quotaData['remain']
                                ];
                            }

                            // Update new quota oa to db
                            $quotaInfo = json_encode($quotaInfo);
                            $db->query("UPDATE ec_zalo SET quota_info = '{$quotaInfo}' WHERE id = '{$oa_id}' AND deleted = 0");
                            break;

                        case 'purchase_quota': // Tin gửi ra là tin nằm trong hạn mức gói tính năng lẻ
                            // // Cập nhật thông tin vào OA
                            // "owner_type": "OA",
                            // "owner_id": "4462152339089565647"
                            break;

                        case 'reward_quota': // Tin gửi ra là tin nằm trong hạn mức Redeem code
                            // // Cập nhật thông tin vào OA
                            // "owner_type": "OA",
                            // "owner_id": "4462152339089565647"
                            break;
                        default:
                            break;
                    }
                }
                catch(Throwable $th) {}
            }
            else $cost = 55;

            $zalomes = new EC_Zalo_Messages();
            $zalomes->message_id        = $arr_message['data']['message_id'] ?? '';
            $zalomes->src               = 0;
            $zalomes->from_id           = $zalOA->get_oa_id();
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
     * Send ZNS
     * 
     * @param array $params
     * @return array
     */
    public function sendZNS($params = []) {
        try {
            $phoneNumber   = $params["phoneNumber"] ?? "";
            $type          = $params["type"] ?? ""; // ZNS type
            $parentId      = $params["parentId"] ?? "";
            $parentType    = $params["parentType"] ?? "";
            $templateData  = $params['templateData'] ?? [];

            if(empty($phoneNumber) || empty($type) || empty($templateData) || empty($parentId)) {
                return [
                    "status" => 0,
                    "message" => "Dữ liệu cung cấp không hợp lệ",
                ];
            }

            $zaloOA = new APIZaloOA();
            $omni = new APIOMNI();

            $template_id    = $omni->getTemplateCode($type);
            $template_name  = $omni->getTemplateName($template_id);

            $json = $omni->sendMessage($phoneNumber, $template_id, $templateData, true);
            $arr = json_decode($json, true);

            if(isset($arr['status']) && $arr['status'] == 1) {
                // $category = (in_array($template_id, ['347078', '347088', '345209', '288276', '288279', '346656']) ? 'transaction' : 'customer_care');
                $templateData['template_id'] = $template_id;

                // Save to zalo message
                try {
                    $msg_id     = $arr['data']['msg_id'] ?? ($arr['idOmniMess'] ?? '');
                    $timestamp  = $arr['data']['sent_time'] ?? time();

                    $zalomes = new EC_Zalo_Messages();
                    $zalomes->message_id    = $msg_id;
                    $zalomes->src           = 0;
                    $zalomes->from_id       = $zaloOA->get_oa_id();
                    $zalomes->to_id         = $phoneNumber;
                    $zalomes->timestamp     = $timestamp;
                    $zalomes->type          = 'zns';
                    $zalomes->sub_type      = $type;
                    $zalomes->description   = $template_name;
                    $zalomes->template_id   = $template_id;
                    $zalomes->data          = json_encode($templateData);
                    $zalomes->response      = trim($json);
                    $zalomes->booking_id    = $parentId;
                    $zalomes->assigned_user_id = $this->currentUser->id;
                    $zalomes->save();

                    // Save notes
                    if(!empty($parentId)) {
                        $n = new Note();
                        $n->name        = "Gửi Zalo ZNS";
                        $n->description = "Gửi Zalo ZNS $template_name đến $phoneNumber";
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
                        $message .= Mattermost::markdownHeading("[ERROR] ZNS message saved failed");
                        $message .= "\n$exceptionMessage\n\n$json";
                        Mattermost::sendMessage($this->mattermostConfig['channel_id_logs'] ?? '', $message);
                    }
                    else {
                        $message = "<b>[ERROR] ZNS message saved failed</b>";
                        $message .= "\n$exceptionMessage\n<pre>$json</pre>";
                        $botToken   = $this->telegramConfig['bot_token'] ?? '';
                        $chatId     = $this->telegramConfig['chat_id'] ?? '';
                        $threadId   = $this->telegramConfig['thread_id_logs'] ?? '';
                        Telegram::sendMessage($message, $botToken, $chatId, $threadId);
                    }
                }
                
                $fullname   = trim("{$this->currentUser->last_name} {$this->currentUser->first_name}");
                $botToken   = $this->telegramConfig['zalo']['bot_token'] ?? '';
                $chatId     = $this->telegramConfig['zalo']['chat_id'] ?? '';
                Telegram::sendMessage("<b>$fullname</b>: Gửi ZNS $template_name đến Zalo <b>$phoneNumber</b>", $botToken, $chatId);
            }
            else {
                $arr["message"] = $omni->getErrorDescription($arr["code"] ?? "");
            }

            return $arr;
        }
        catch(Throwable $th) {
            return [
                "status" => 0,
                "message" => "Exception error {$th->getMessage()} on line {$th->getLine()}"
            ];
        }
    }
}