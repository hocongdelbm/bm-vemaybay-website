<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once "custom/include/helpers/api/APIZaloOA.php";
require 'vendor/autoload.php';
use WebSocket\Client;
use custom\services\Notification\NotificationService;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    global $sugar_config, $current_user;
    // Get format date and time configs
    $timezone = $current_user->getPreference('timezone') ?? 'Asia/Ho_Chi_Minh';
    $dateFormat = $current_user->getPreference('datef') ?? $sugar_config['datef'] ?? 'd-m-Y';
    $timeFormat = $current_user->getPreference('timef') ?? $sugar_config['timef'] ?? 'H:i';
    $datetimeDbFormat = 'Y-m-d H:i:s';

    $headers = getallheaders();
    $response = file_get_contents('php://input'); // json
    $data = json_decode($response, true);

    $timestamp  = $data['timestamp'];
    $app_id     = $data['app_id'] ?? $sugar_config['zalo_config']['app_id_default'] ?? '';

    if(!empty($app_id)) {
        $zaloApp = new EC_Zalo_Apps();
        $zaloApp->retrieve($app_id);
    }

    $mac   = "mac=".hash('sha256', $app_id.$response.$timestamp.$zaloApp->oa_secret_key);
    $h_mac = $headers['X-Zevent-Signature'] ?? '';
   
    if($mac === $h_mac) {
        global $db;
        $zaloOA = new APIZaloOA($zaloApp->id, $zaloApp->oa_id);
        $event = $data['event_name'] ?? '';

        try {
            $list_consultation_events = [
                'user_send_text',
                'user_send_image', 'user_send_gif', 'user_send_sticker',
                'user_send_link',
                'user_send_file', 'user_send_audio', 'user_send_video',
                'user_send_location',
                'user_send_business_card',
                'user_submit_info',

                'oa_send_text',
                'oa_send_image', 'oa_send_gif', 'oa_send_sticker',
                'oa_send_file',
                'oa_send_list',
            ];

            // Consultation messages
            if(in_array($event, $list_consultation_events)) {
                try {
                    $zalomes = new EC_Zalo_Messages();

                    $sender_id      = $data['sender']['id'] ?? '';
                    $admin_id       = $data['sender']['admin_id'] ?? ''; // Chỉ có khi OA gửi tin nhắn bằng tool chat OA - https://oa.zalo.me/chatv2
                    $recipient_id   = $data['recipient']['id'] ?? '';
                    $src            = strpos($event, "user_send") === false ? 0 : 1;
                    $msg_id         = $data['message']['msg_id'] ?? '';
                    $quote_id       = $data['message']['quote_msg_id'] ?? '';
                    $msg            = $data['message']['text'] ?? '';
                    $msg_type       = EC_Zalo_Messages_Helper::map_sub_type($event);

                    // Handle attachments
                    $url = $thumbnail = $description = '';
                    $msg_data = [];
                    $lat = $long = '';
                    $attachments = $data['message']['attachments'] ?? [];
                    if(!empty($attachments)) {
                        if(in_array($msg_type, ['image', 'gif', 'sticker'])) {
                            $url = $attachments[0]['payload']['url'] ?? '';
                            $thumbnail = $attachments[0]['payload']['thumbnail'] ?? '';
                        }
                        elseif(in_array($msg_type, ['link', 'audio', 'video', 'business_card'])) {
                            $url = $attachments[0]['payload']['url'] ?? '';
                            $thumbnail = $attachments[0]['payload']['thumbnail'] ?? '';
                            $description = $attachments[0]['payload']['description'] ?? '';
                        }
                        elseif($msg_type == 'links') {
                            foreach($attachments as $at) {
                                $msg_data[] = $at['payload'];
                            }
                        }
                        elseif($msg_type == 'file') {
                            $url  = $attachments[0]['payload']['url'] ?? '';
                            $msg_data = $attachments[0]['payload'];
                        }
                        elseif($msg_type == 'location') {
                            $location = $attachments[0]['payload']['coordinates'] ?? '';
                            if(!empty($location)) {
                                $lat = $location['latitude'] ?? '';
                                $long = $location['longitude'] ?? '';
                            }
                        }
                    }

                    // Handle assigned user
                    $assigned_user_id = $assigned_user_name = $assigned_user_avatar = '';
                    if($src == 0 && !empty($admin_id) && strlen($admin_id) > 10) { // Gửi bằng web quản trị Zalo OA
                        $sql_assigned_user = 
                            "SELECT id
                                ,TRIM(CONCAT(IFNULL(u.last_name, ''), ' ', IFNULL(u.first_name, ''))) AS name 
                                ,u.photo
                            FROM users u
                            WHERE zalo_id = '$admin_id' AND deleted = 0
                            LIMIT 1";

                        $res_assigned_user = $db->query($sql_assigned_user);
                        $row_assigned_user = $db->fetchByAssoc($res_assigned_user);
                        if(!empty($row_assigned_user)) {
                            $assigned_user_id = $row_assigned_user['id'] ?? '';
                            $assigned_user_name = $row_assigned_user['name'] ?? '';

                            if($row_assigned_user['photo'] && !empty($row_assigned_user['photo']) && !empty($assigned_user_id)) {
                                $assigned_user_avatar = "index.php?entryPoint=download&id=".$assigned_user_id."_photo&type=Users";
                            }
                        }
                    }

                    $zalomes->id = '';
                    $zalomes->message_id = $msg_id;
                    $zalomes->src = $src;
                    $zalomes->from_id = $sender_id;
                    $zalomes->to_id = $recipient_id;
                    $zalomes->timestamp = $timestamp;
                    $zalomes->type = 'consultation';
                    $zalomes->sub_type = $msg_type;
                    $zalomes->description = $msg;
                    $zalomes->thumbnail = $thumbnail;
                    $zalomes->url = $url;
                    $zalomes->latitude = $lat;
                    $zalomes->longitude = $long;
                    $zalomes->attached_description = $description;
                    $zalomes->quote_message_id = $quote_id;
                    $zalomes->data = !empty($msg_data) ? json_encode($msg_data, JSON_UNESCAPED_UNICODE) : '';
                    $zalomes->response = trim($response);
                    $zalomes->assigned_user_id = $assigned_user_id;
                    if($zalomes->src == 0 && empty($admin_id)) { // Gửi bằng API
                        $sql_update_message = "UPDATE ec_zalo_messages
                            SET sub_type = '{$zalomes->sub_type}'
                                ,thumbnail = '{$zalomes->thumbnail}'
                                ,url = '{$zalomes->url}'
                                ,attached_description = '{$zalomes->attached_description}'
                                ,data = '{$zalomes->data}'
                                ,response = '{$zalomes->response}'
                            WHERE message_id = '{$zalomes->message_id}' AND deleted = 0";
                        $db->query($sql_update_message);
                    }
                    else $zalomes->save();

                    // Update quota, last interaction
                    try {
                        $quota_user = $quota_oa = [];
                        // Database format
                        $last_interaction = date($datetimeDbFormat, (int)($timestamp / 1000) - 7*3600);
                        $date_modified = date($datetimeDbFormat, time() - 7*3600);

                        // Send from user to OA
                        if($zalomes->src == 1) {
                            $zaloUserInfo = EC_Zalo_Contacts_Helper::get_zalo_user_info($sender_id, $recipient_id);

                            if(is_array($zaloUserInfo) && !empty($zaloUserInfo) && isset($zaloUserInfo['user_id'])) {
                                $sqlUpdate = "UPDATE ec_zalo_contacts 
                                    SET last_interaction = '$last_interaction'
                                        ,description = 'Cập nhật tương tác cuối qua webhook user send'
                                        ,modified_user_id = ''
                                        ,date_modified = '$date_modified'
                                    WHERE zalo_id = '{$zaloUserInfo['user_id']}' AND deleted = 0";
                                    
                                $db->query($sqlUpdate);
                            }
                        }
                        // Quota oa (Only update when not send by API)
                        else if($zalomes->src == 0 && !empty($admin_id)) {
                            // Get user quota info
                            $zaloUserInfo = EC_Zalo_Contacts_Helper::get_zalo_user_info($recipient_id, $sender_id);

                            if(is_array($zaloUserInfo) && !empty($zaloUserInfo) && isset($zaloUserInfo['user_id'])) {
                                if(!isset($zaloUserInfo['user_last_interaction_date']) 
                                    || !is_string($zaloUserInfo['user_last_interaction_date'])
                                    || empty($zaloUserInfo['user_last_interaction_date'])
                                ) {
                                    // Do something with user quota 'welcome_msg'
                                }
                                else if((int)($timestamp / 1000) - strtotime($zaloUserInfo['user_last_interaction_date']) > 48*3600) {
                                    $zaloOAInfo = EC_Zalo_Helper::get_info_oa($sender_id);

                                    $quota_oa = $zaloOAInfo["quota"];
                                    $isUpdate = false;
                                    foreach ($quota_oa as $qkey => $qValue) {
                                        if($qValue['quota_type'] == 'sub_quota') {
                                            if(isset($quota_oa[$qkey]["remain"]) && $quota_oa[$qkey]["remain"] > 0) {
                                                $quota_oa[$qkey]["remain"] -= 1;
                                                $isUpdate = true;
                                                break;
                                            }
                                        }
                                    }

                                    if($isUpdate) {
                                        $sqlUpdate = "UPDATE ec_zalo
                                            SET quota_info = '". json_encode($quota_oa) ."'
                                            WHERE id = '{$sender_id}'AND deleted = 0";
                                        $db->query($sqlUpdate);
                                    }
                                }
                            }
                        }
                    }
                    catch(Exception $e) {
                        $m = "Webhook Zalo";
                        $m .= "\n{$e->getMessage()} on line {$e->getLine()} in {$e->getFile()}";
                        if(isset($response) && !empty($response)) $m .= "\n<pre>$response</pre>";
                        NotificationService::sendErrorMessage($m, "default", ['threadKey' => 'logs']);
                    }
                    
                    // Send data to chat
                    $data_chat = [
                        'event_name'            => $event,
                        'message_id'            => $zalomes->message_id,
                        'src'                   => $zalomes->src,
                        'from_id'               => $sender_id,
                        'to_id'                 => $recipient_id,
                        'timestamp'             => (int)$timestamp,
                        'message_type'          => $zalomes->type,
                        'type'                  => $zalomes->sub_type,
                        'message'               => $zalomes->description,
                        'thumbnail'             => $thumbnail,
                        'url'                   => $url,
                        'description'           => $description,
                        'latitude'              => $zalomes->latitude,
                        'longitude'             => $zalomes->longitude,
                        'quote_id'              => $zalomes->quote_message_id,
                        'template_id'           => $zalomes->template_id,
                        'message_data'          => !empty($msg_data) ? $msg_data : '',
                        'assigned_user_id'      => $assigned_user_id,
                        'assigned_user_name'    => $assigned_user_name,
                        'assigned_user_avatar'  => $assigned_user_avatar,
                        'quota_user'            => $quota_user
                    ];
                    if($zalomes->quote_message_id && !empty($zalomes->quote_message_id)) {
                        $data_chat['quote_data'] = EC_Zalo_Messages_Helper::get_quote_message_data($zalomes->quote_message_id);
                    }
                    $client = new Client("wss://".$_SERVER['SERVER_NAME']."/chatz/");
                    $client->send(json_encode($data_chat));
                    $client->close();
                }
                catch(Exception $e) {
                    $m = "Webhook Zalo";
                    $m .= "\n{$e->getMessage()} on line {$e->getLine()} in {$e->getFile()}";
                    if(isset($response) && !empty($response)) $m .= "\n<pre>$response</pre>";
                    NotificationService::sendErrorMessage($m, "default", ['threadKey' => 'logs']);
                }
                finally {
                    header("HTTP/1.1 200 OK");
                    exit();
                }
            }
            else if($event == 'widget_interaction_accepted') {
                $zalo_user_id = $data['data']['user_id'] ?? ($data['data']['user_external_id'] ?? '');
                $url = $data['data']['url'] ?? '';
                $zalo_last_interaction = date('Y-m-d H:i:s', (int)($timestamp / 1000));
                
                if(!empty($zalo_user_id) && !empty($url)) {
                    $parsed_url = parse_url($url);
                    parse_str($parsed_url['query'], $query_params);
                    $input_phone = $query_params['contact_phone'] ?? '';

                    if(strlen($input_phone) > 9) {
                        // Get contact by zalo id
                        $count_contact = 0;
                        $contact_id = $contact_phone = '';

                        // Get info in ec_zalo_contacts table
                        $sqlZaloContactInfo = "SELECT id, contact_id 
                            FROM ec_zalo_contacts
                            WHERE zalo_id = '{$zalo_user_id}'
                                AND oa_id = '{$zaloOA->get_oa_id()}'
                                AND deleted = 0";
                        $rowZaloContactInfo = $db->fetchByAssoc($db->query($sqlZaloContactInfo));
                        $zalo_contact_id = $row['id'] ?? '';
                        $contact_id_by_zalo = $row['contact_id'] ?? '';
    
                        // Get info in contacts table
                        $contact_id_by_phone = $db->getOne("SELECT id 
                            FROM contacts
                            WHERE phone_mobile = '{$input_phone}' AND deleted = 0
                            ORDER BY date_entered
                            LIMIT 1") ?? '';

                        // Not exist zalo contact
                        if(empty($zalo_contact_id)) {
                            $user_info = json_decode($zaloOA->get_user($zalo_user_id), true);
                            if(isset($user_info['error']) && $user_info['error'] == 0) {
                                EC_Zalo_Contacts_Helper::custom_save($user_info['data'], $zaloOA->get_oa_id(), 'Liên hệ tạo qua widget tương tác');
                            }
                        }
                        elseif(empty($contact_id_by_zalo) && !empty($contact_id_by_phone)) {
                            if($db->query("UPDATE ec_zalo_contacts SET contact_id = '{$contact_id_by_phone}' WHERE id = '{$zalo_contact_id}' AND deleted = 0")) {
                                NotificationService::sendErrorMessage(
                                    "⚙️ Hệ thống đã map SĐT $input_phone với Zalo Id $zalo_user_id",
                                    "",
                                    ['threadKey' => 'system']
                                );
                            }
                        }
                    }
                }

                header("HTTP/1.1 200 OK");
                exit();
            }
            else if(in_array($event, ['follow', 'unfollow'])) {
                // Update quota
                try {
                    $zalo_user_id = $data['follower']['id'] ?? '';
                    $follower = $event == 'follow' ? 1 : 0;
                    $zalo_last_interaction = date($datetimeDbFormat, (int)($timestamp / 1000) - 7*3600);
                    $date_modified = date($datetimeDbFormat, time() - 7*3600);

                    if(!empty($zalo_user_id)) {
                        if($follower == 1) {
                            // Get user info
                            $zaloUserInfo = EC_Zalo_Contacts_Helper::get_zalo_user_info($sender_id, $recipient_id);

                            if(is_array($zaloUserInfo) && !empty($zaloUserInfo) && isset($zaloUserInfo['user_id'])) {
                                $db->query(
                                    "UPDATE ec_zalo_contacts 
                                    SET is_follower = $follower
                                        ,last_interaction = '$zalo_last_interaction'
                                        ,description = 'Cập nhật tương tác qua webhook user follow'
                                        ,modified_user_id = ''
                                        ,date_modified = '$date_modified'
                                    WHERE zalo_id = '{$zaloUserInfo['user_id']}'
                                        AND oa_id = '{$zaloOA->get_oa_id()}'
                                        AND deleted = 0"
                                );
                            }
                        }
                        else {
                            $db->query(
                                "UPDATE ec_zalo_contacts
                                SET is_follower = $follower
                                    ,last_interaction = '$zalo_last_interaction'
                                    ,description = 'Cập nhật tương tác qua webhook user unfollow'
                                    ,modified_user_id = ''
                                    ,date_modified = '$date_modified'
                                WHERE zalo_id = '$zalo_user_id'
                                    AND oa_id = '{$zaloOA->get_oa_id()}'
                                    AND deleted = 0"
                            );
                        }
                    }

                    header("HTTP/1.1 200 OK");
                    exit();
                }
                catch (Throwable $th) {
                    echo json_encode([
                        "error"     => 1,
                        "message"   => "Error: " . $th->getMessage(),
                        "data"      => $data
                    ]);
                    exit();
                }
            }
            else if($event == 'change_template_quality') {
                $template_id = $data['template_id'] ?? '';
                $quality = strtoupper($data['quality'] ?? '');
                $template_name = $zaloOA->get_template_name($template_id);
                $arr_map_quality = [
                    'HIGH' => 'Mức độ chất lượng tốt',
                    'MEDIUM' => 'Mức độ chất lượng trung bình',
                    'LOW' => 'Mức độ chất lượng kém',
                    'UNDEFINED' => 'Mức độ chất lượng chưa được xác định',
                ];

                $message = "<b>Thông báo từ Zalo về chất lượng gửi tin ZBS</b>";
                $message .= "\nMẫu tin: $template_name ($template_id)";
                $message .= "\nChất lượng: " . ($arr_map_quality[$quality] ?? '');
                NotificationService::sendMessage($message, '', ['threadKey' => 'system']);

                echo json_encode(["error" => 0, "message" => "Done"]);
                exit();
            }
            else if($event == 'update_user_info') {
                echo json_encode(["error" => 0, "message" => "Nothing"]);
                exit();
            }
            else {
                echo json_encode(["error" => 0, "message" => "Nothing"]);
                exit();
            }
        }
        catch (Exception $e) {
            echo json_encode([
                "error"     => 1,
                "message"   => "Error: " . $e->getMessage(),
                "data"      => $data
            ]);
            exit();
        }
    }

    echo json_encode(["error" => 1, "code" => 400, "message" => "Bad request"]);
    exit();
}

echo json_encode(["error" => 1, "code" => 404, "message" => "Not found"]);
exit();
?>