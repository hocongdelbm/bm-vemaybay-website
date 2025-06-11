<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once("modules/EC_Zalo/Zalo.php");
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
                            SET sub_type = '$zalomes->sub_type'
                                ,thumbnail = '$zalomes->thumbnail'
                                ,url = '$zalomes->url'
                                ,attached_description = '$zalomes->attached_description'
                                ,data = '$zalomes->data'
                                ,response = '$zalomes->response'
                            WHERE message_id = '$zalomes->message_id' AND deleted = 0";
                        $db->query($sql_update_message);
                    }
                    else $zalomes->save();

                    // Update zalo last interaction
                    if($zalomes->src == 1) {
                        $zalo_last_interaction = date('Y-m-d H:i:s', (int)($timestamp / 1000));
                        $sql_update_contact = "UPDATE contacts SET zalo_last_interaction = '$zalo_last_interaction' WHERE zalo_id = '$sender_id' AND deleted = 0";
                        $db->query($sql_update_contact);
                    }

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
                        $bean_zalo = new EC_Zalo();
                        $data_chat['quote_data'] = $bean_zalo->get_quote_message_data($zalomes->quote_message_id);
                    }
                    $client = new Client("wss://".$_SERVER['SERVER_NAME']."/chatz/");
                    $client->send(json_encode($data_chat));
                    $client->close();
                }
                catch(Exception $e) {
                    $message = Mattermost::$line_separation;
                    $message .= Mattermost::markdownHeading("[WARNING] Webhook Zalo");
                    $message .= "\n{$e->getMessage()} on line {$e->getLine()} in {$e->getFile()}\n\n$response";
                    Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $message);
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

                    if(strlen($input_phone) > 8) {
                        $ZaloObj = new Zalo();

                        // Get contact by zalo id
                        $count_contact = 0;
                        $contact_id = $contact_phone = '';
                        $sql_check_zalo_id = "SELECT id, phone_mobile FROM contacts WHERE zalo_id = '$zalo_user_id' AND deleted = 0";
                        $res_check_zalo_id = $db->query($sql_check_zalo_id);
                        while($row = $db->fetchByAssoc($res_check_zalo_id)) {
                            $contact_id = $row['id'];
                            $contact_phone = $row['phone_mobile'];
                            $count_contact++;
                        }

                        if($count_contact > 1) {
                            Mattermost::sendMessage(
                                $sugar_config['mattermost']['channel_id_zalo_oa'] ?? '',
                                "**Zalo id $zalo_user_id có nhiều hơn 1 liên hệ trong BM**",
                                [],
                                ["priority" => [ "priority" => "important"]]
                            );
                        }
                        else {
                            if(empty($contact_id)) {
                                // Cập nhật $zalo_user_id cho contact có $input_phone
                                $sql = "UPDATE contacts
                                    SET zalo_id = '$zalo_user_id', zalo_last_interaction = '$zalo_last_interaction'
                                    WHERE phone_mobile = '$input_phone' AND deleted = 0";
                                $db->query($sql);

                                Mattermost::sendMessage(
                                    $sugar_config['mattermost']['channel_id_zalo_oa'] ?? '',
                                    "Hệ thống đã map số điện thoại $input_phone với zalo id $zalo_user_id"
                                );
                            }
                            elseif(empty($contact_phone)) {
                                // Cập nhật $zalo_user_id cho contact có $input_phone
                                $sql = "UPDATE contacts
                                    SET zalo_id = '$zalo_user_id', zalo_last_interaction = '$zalo_last_interaction'
                                    WHERE phone_mobile = '$input_phone' AND deleted = 0";
                                $db->query($sql);

                                Mattermost::sendMessage(
                                    $sugar_config['mattermost']['channel_id_zalo_oa'] ?? '',
                                    "**Zalo id $zalo_user_id có nhiều hơn 1 liên hệ trong BM: $contact_id (contact id)**",
                                    [],
                                    ["priority" => [ "priority" => "important"]]
                                );
                            }
                            elseif(!empty($contact_phone) && $contact_phone != $input_phone) {
                                // Get zalo phone
                                $zalo_phone = '';
                                $user_info = json_decode($ZaloObj->get_user($zalo_user_id), true);
                                if(isset($user_info['error']) && $user_info['error'] == 0) {
                                    $zalo_phone = $user_info['data']['shared_info']['phone'] ?? '';
                                    $zalo_phone = empty($zalo_phone) ? $ZaloObj->get_phone_by_alias($user_info['data']['user_alias'] ?? '') : '';
                                }

                                if(!empty($zalo_phone)) {
                                    if($zalo_phone == $contact_phone) return true;
                                    elseif($zalo_phone == $input_phone) {
                                        // Xóa $zalo_user_id trong contact trước đó
                                        $sql_1 = "UPDATE contacts SET zalo_id = '', zalo_last_interaction = '' WHERE id = '$contact_id'";
                                        $db->query($sql_1);

                                        // Cập nhật $zalo_user_id trong contact mới
                                        $sql_2 = "UPDATE contacts
                                            SET zalo_id = '$zalo_user_id', zalo_last_interaction = '$zalo_last_interaction'
                                            WHERE phone_mobile = '$input_phone' AND deleted = 0";
                                        $db->query($sql_2);

                                        Mattermost::sendMessage(
                                            $sugar_config['mattermost']['channel_id_zalo_oa'] ?? '',
                                            "Hệ thống đã map số điện thoại $input_phone với zalo id $zalo_user_id"
                                        );
                                    }
                                }
                            }
                        }
                    }
                }

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