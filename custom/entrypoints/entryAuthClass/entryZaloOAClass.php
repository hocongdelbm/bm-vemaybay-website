<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once "custom/entrypoints/entryAuthClass/entryClass.php";
require_once "custom/include/helpers/api/APIZaloOA.php";
require_once "custom/include/helpers/api/APIOMNI.php";

/**
 * Class entryZaloOAClass
 */
class entryZaloOAClass extends entryClass {
    /**
     * Send message
     * 
     * @param array $params
     * @return array
     */
    public function sendMessage($params = []) {
        $zalo_id = $params['zalo_id'] ?? "";
        $type    = $params['type'] ?? "text";
        $data    = ['text' => $params['text'] ?? ""];

        if(empty($zalo_id) || empty($type)) {
            return [
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
                "data" => ["zalo_id" => $zalo_id, "type" => $type]
            ];
        }

        $zalOA = new APIZaloOA();

        // Prepare body request (data)
        if ($type == 'image') {
            // Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_name = $_FILES['image']['name']; // name.ext
                $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    
                // Check extension
                if(!in_array($ext, $zalOA->get_file_extension('image'))) {
                    return [
                        "error" => 1,
                        "message" => "Không hỗ trợ định dạng $ext",
                        "description" => "Chỉ hỗ trợ định dạng " . implode(',', $zalOA->get_file_extension('image'))
                    ];
                }
    
                // Check size
                if($ext == 'gif' && $_FILES["image"]["size"] > 5000000) {
                    return [
                        "error" => 1,
                        "message" => "Dung lượng ảnh quá lớn",
                        "description" => "Dung lượng tối đa 5MB cho định dạng .gif"
                    ];
                }
                elseif($ext != 'gif' && $_FILES["image"]["size"] > 1000000) {
                    return [
                        "error" => 1,
                        "message" => "Dung lượng ảnh quá lớn",
                        "description" => "Dung lượng tối đa 1MB cho định dạng jpg, png"
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
                        'error' => 1,
                        'message' => 'Gửi ảnh thất bại, vui lòng thử lại',
                        'data' => $arr_upload
                    ];
                }
            }
            else {
                return [
                    'error' => 1,
                    'message' => 'Gửi ảnh thất bại, vui lòng thử lại',
                    'data' => $_FILES
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
                        "error" => 1,
                        "message" => "Không hỗ trợ định dạng $ext",
                        "description" => "Các định dạng hỗ trợ: ". implode(', ', $zalOA->get_file_extension('file'))
                    ];
                }
    
                // Check size
                if($_FILES["file"]["size"] > 5000000) {
                    return [
                        "error" => 1,
                        "message" => "Dung lượng file quá lớn",
                        "description" => "Tối đa 5MB"
                    ];
                }
                
                $json_upload = $zalOA->upload($_FILES["file"]["tmp_name"], $ext, $file_name);
                $arr_upload = json_decode($json_upload, true);
                
                if(isset($arr_upload['error']) && $arr_upload['error'] == 0) {
                    $data['token'] = isset($arr_upload['data']['token']) ? $arr_upload['data']['token'] : '';
                }
                else {
                    return [
                        'error' => 1,
                        'message' => 'Gửi file thất bại, vui lòng thử lại',
                        'data' => $arr_upload
                    ];
                }
            }
            else {
                return [
                    'error' => 1,
                    'message' => 'Gửi file thất bại, vui lòng thử lại',
                    'data' => $_FILES
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

        return $arr_message;
    }

    /**
     * Send ZNS
     * 
     * @param array $params
     * @return array
     */
    public function sendZNS($params = []) {
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
}