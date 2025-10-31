<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once "custom/include/helpers/api/APIZaloOA.php";
require 'vendor/autoload.php';
use WebSocket\Client;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    global $sugar_config;

    $headers = getallheaders();
    $response = file_get_contents('php://input'); // json
    $data = json_decode($response, true);

    $timestamp  = $data['timestamp'];
    $app_id     = $sugar_config['zalo_config']['app_id'] ? $sugar_config['zalo_config']['app_id'] : '';
    $oa_secret  = $sugar_config['zalo_config']['oa_secret'] ? $sugar_config['zalo_config']['oa_secret'] : '';
    $mac        = "mac=".hash('sha256', $app_id.$response.$timestamp.$oa_secret);
    $h_mac      = isset($headers['X-Zevent-Signature']) ? $headers['X-Zevent-Signature'] : '';
   
    if($mac === $h_mac) {
        global $db;
        $zaloOA = new APIZaloOA();
        $event = isset($data['event_name']) ? $data['event_name'] : '';

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
                    $admin_id       = $data['sender']['admin_id'] ?? '';
                    $recipient_id   = $data['recipient']['id'] ?? '';
                    $src            = strpos($event, "user_send") === false ? 0 : 1;
                    $msg_id         = $data['message']['msg_id'] ?? '';
                    $quote_id       = $data['message']['quote_msg_id'] ?? '';
                    $msg            = $data['message']['text'] ?? '';
                    $msg_type       = $zalomes->map_sub_type($event);

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
                    if($src == 0 && !empty($admin_id) && strlen($admin_id) > 10) {
                        $sql_assigned_user = "SELECT id
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
                    if($zalomes->src == 0 && empty($admin_id)) {
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

                    // Add new or update user (zalo last interaction)
                    try {
                        if($zalomes->src == 1) {
                            // Search by Zalo ID
                            $sqlCheck = "SELECT id FROM ec_zalo_contacts WHERE zalo_id = '{$sender_id}' AND oa_id = '{$recipient_id}' AND deleted = 0";
                            $record_id = $this->db->getOne($sqlCheck) ?? '';

                            if(!$record_id || empty($record_id)) {
                                $json_user = $zaloOA->get_user($sender_id);
                                $user_info = json_decode($json_user, true);
                                if(isset($user_info['error']) && $user_info['error'] == 0) {
                                    $beanZaloContact = new EC_Zalo_Contacts();
                                    $beanZaloContact->custom_save($user_info['data'], $recipient_id, 'Liên hệ tạo bởi webhook gửi/nhận tin nhắn');
                                }
                            }
                            else {
                                $last_interaction = date('Y-m-d H:i:s', (int)($timestamp / 1000));
                                $db->query("UPDATE ec_zalo_contacts SET last_interaction = '$last_interaction' WHERE zalo_id = '{$sender_id}' AND oa_id = '{$recipient_id}' AND deleted = 0");
                            }
                        }
                    }
                    catch(Exception $e) {}
                    
                    // Send data to chat
                    $data_chat = [
                        'event_name'            => $event,
                        'message_id'            => $zalomes->message_id,
                        'src'                   => $zalomes->src,
                        'from_id'               => $sender_id,
                        'to_id'                 => $recipient_id,
                        'timestamp'             => $timestamp,
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
                    ];
                    if($zalomes->quote_message_id && !empty($zalomes->quote_message_id)) {
                        $zaloMessage = new EC_Zalo_Messages();
                        $data_chat['quote_data'] = $zaloMessage->get_quote_message_data($zalomes->quote_message_id);
                    }
                    $client = new Client("wss://".$_SERVER['SERVER_NAME']."/chatz/");
                    $client->send(json_encode($data_chat));
                    $client->close();
                }
                catch(Exception $e) {
                    if(isset($sugar_config['notification_channel']) && $sugar_config['notification_channel'] == 'Mattermost') {
                        $message = Mattermost::$line_separation;
                        $message .= Mattermost::markdownHeading("[WARNING] Webhook Zalo");
                        $message .= "\n{$e->getMessage()} on line {$e->getLine()} in {$e->getFile()}\n\n$response";
                        Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $message);
                    }
                    else {
                        $message = "<b>[WARNING]</b> Webhook Zalo";
                        $message .= "\n{$e->getMessage()} on line {$e->getLine()} in {$e->getFile()}\n<pre>$response</pre>";
                        $botToken   = $sugar_config['telegram']['bot_token'] ?? '';
                        $chatId     = $sugar_config['telegram']['chat_id'] ?? '';
                        $threadId   = $sugar_config['telegram']['thread_id_logs'] ?? '';
                        Telegram::sendMessage($message, $botToken, $chatId, $threadId);
                    }
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
                                $zaloContact = new EC_Zalo_Contacts();
                                $zaloContact->custom_save($user_info['data'], $zaloOA->get_oa_id(), 'Liên hệ tạo qua widget tương tác');
                            }
                        }
                        elseif(empty($contact_id_by_zalo) && !empty($contact_id_by_phone)) {
                            if($db->query("UPDATE ec_zalo_contacts SET contact_id = '{$contact_id_by_phone}' WHERE id = '{$zalo_contact_id}' AND deleted = 0")) {
                                $message    = "⚙️ Hệ thống đã map SĐT $input_phone với Zalo Id $zalo_user_id";
                                $botToken   = $sugar_config['telegram']['bot_token'] ?? '';
                                $chatId     = $sugar_config['telegram']['chat_id'] ?? '';
                                $threadId   = $sugar_config['telegram']['thread_id_system_noti'] ?? '';
                                Telegram::sendMessage($message, $botToken, $chatId, $threadId);
                            }
                        }
                    }
                }

                header("HTTP/1.1 200 OK");
                exit();
            }
            else if(in_array($event, ['follow', 'unfollow'])) {
                try {
                    $zalo_user_id = $data['follower']['id'] ?? '';
                    $zalo_last_interaction = date('Y-m-d H:i:s', (int)($timestamp / 1000));
                    $follower = $event == 'follow' ? 1 : 0;

                    if(!empty($zalo_user_id)) {
                        // // Send code to engage in event 02/09/2025
                        // $eventActive = time() > strtotime('2025-08-21 23:59:59') && time() < strtotime('2025-08-29 00:00:00');
                        // if($eventActive && $follower == 1) {
                        //     try {
                        //         require_once("custom/entrypoints/entryNonAuthClass/entryEvent020925Class.php");
                        //         $event020925 = new entryEvent020925Class();
                        //         $arrUserInfoEvent = $event020925->getUserInfo(['code' => $zalo_user_id]);
                        //         // If user hasn't joined the event, the system will send a link to join
                        //         if(isset($arrUserInfoEvent['status']) && $arrUserInfoEvent['status'] == 0) {
                        //             $res = json_decode($ZaloObj->send_consultation(
                        //                 "text",
                        //                 $zalo_user_id,
                        //                 ["text" => "/-flag Tìm Chuyến Bay gửi bạn trang tham gia sự kiện mừng lễ Quốc Khánh 02/09\nhttps://timchuyenbay.vn/thu-thach-su-viet?code=$zalo_user_id"]
                        //             ), true);

                        //             if(isset($res['error']) && $res['error'] == 0) {
                        //                 $event020925->addUser(['code' => $zalo_user_id]);
                        //             }
                        //         }
                        //     }
                        //     catch(Throwable $th) {
                        //         $message = "<b>[WARNING] Send link to join event 02/09 failed</b>";
                        //         $message .= "\n{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}\nZalo ID: $zalo_user_id";
                        //         $botToken   = $sugar_config['telegram']['bot_token'] ?? '';
                        //         $chatId     = $sugar_config['telegram']['chat_id'] ?? '';
                        //         $threadId   = $sugar_config['telegram']['thread_id_logs'] ?? '';
                        //         Telegram::sendMessage($message, $botToken, $chatId, $threadId);
                        //     }
                        // }

                        $db->query("UPDATE ec_zalo_contacts
                            SET is_follower = $follower, last_interaction = '$zalo_last_interaction'
                            WHERE zalo_id = '$zalo_user_id' AND oa_id = '{$zaloOA->get_oa_id()}' AND deleted = 0");
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
            else if($event == 'update_user_info') {
                header("HTTP/1.1 200 OK");
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