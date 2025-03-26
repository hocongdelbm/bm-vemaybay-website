<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once("modules/EC_Zalo/Zalo.php");
    global $current_user, $db;

    $action = isset($_POST['action']) ? $_POST['action'] : "";

    if($action == 'get_recent_messages') {
        if($current_user->id == '1') {
            $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : 0;
            $current_list_user = isset($_POST['current_list_user']) ? array_unique(explode(',', $_POST['current_list_user'])) : []; // array

            $bean_zalo = new EC_Zalo();
            $results = $bean_zalo->get_list_user($timestamp, $current_list_user);
            echo json_encode($results);
            exit();
        }

        $offset = isset($_POST['offset']) ? $_POST['offset'] : 0;
        $current_list_user = isset($_POST['current_list_user']) ? array_unique(explode(',', $_POST['current_list_user'])) : []; // array

        try {
            require_once("modules/EC_Zalo/views/view.chatzalo.php");
            $view = new Viewchatzalo();
            echo $view->get_list_user($offset, $current_list_user);
            exit();
        }
        catch(Exception $e) {
            echo json_encode([
                "error" => 1,
                "message" => $e->getMessage(),
                "data" => ["offset" => $offset]
            ]);
            exit();
        }
    }
    elseif($action == 'get_messages') { // Version 2
        $Zalo = new Zalo();
        $bean_zalo = new EC_Zalo();

        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $offset  = isset($_POST['offset']) ? $_POST['offset'] : 0;
        $is_get_user_info = isset($_POST['is_get_user_info']) ? (int)$_POST['is_get_user_info'] : 1;
        $result = [];

        // User info
        $zalo_phone = '';
        if($is_get_user_info == 1) {
            $user_data = $bean_zalo->get_zalo_user_info($zalo_id);
            $result['user_info']['data'] = $user_data;
            $result['user_info']['error'] = !empty($user_data) ? 0 : 1;
        }

        // Message info
        $zalo_phone = $user_data['shared_info']['phone'] ?? '';
        $message_data = [];
        $is_using_api_for_message = false;
        $sql = "SELECT zm.id AS message_id
                ,zm.description AS message 
                ,zm.src
                ,zm.from_id
                ,zm.to_id
                ,zm.timestamp
                ,zm.type AS message_type
                ,zm.sub_type AS type
                ,zm.thumbnail
                ,zm.url
                ,zm.attached_description AS description
                ,zm.latitude
                ,zm.longitude
                ,zm.quote_message_id AS quote_id
                ,zm.template_id
                ,zm.data AS message_data
                ,zm.assigned_user_id
                ,TRIM(CONCAT(u.last_name, ' ', u.first_name)) AS assigned_user_name 
            FROM ec_zalo_messages zm
                LEFT JOIN users u ON u.id = zm.assigned_user_id
            WHERE (zm.from_id = '$zalo_id' OR zm.to_id = '$zalo_id' OR zm.to_id = '$zalo_phone')
                AND zm.deleted = 0
            ORDER BY zm.timestamp DESC
            LIMIT $offset, 10";

        $res = $db->query($sql);
        while($row = $db->fetchByAssoc($res)) {
            if($row['message_type'] == 'zns') {
                $row['message_data'] = json_decode(html_entity_decode($row['message_data']), true);

                unset($row['thumbnail']);
                unset($row['url']);
                unset($row['description']);
                unset($row['latitude']);
                unset($row['longitude']);
                unset($row['quote_id']);
            }
            else if($row['message_type'] == 'call') {
                if($row['src'] == 1) $row['from_avatar'] = $result['user_info']['data']['avatar'] ?? '';
                $row['type'] = $GLOBALS['app_list_strings']['calls_direction_list'][$row['type']];
                $row['message_data'] = json_decode(html_entity_decode($row['message_data']), true);

                unset($row['thumbnail']);
                unset($row['url']);
                unset($row['description']);
                unset($row['latitude']);
                unset($row['longitude']);
                unset($row['quote_id']);
                unset($row['template_id']);
            }
            else {
                // Avatar
                if($row['src'] == 1) $row['from_avatar'] = $result['user_info']['data']['avatar'] ?? '';

                // Links info
                if($row['type'] == 'link' || $row['type'] == 'links') {
                    $row['links'][] = [
                        'url' => $row['url'],
                        'thumb' => $row['thumbnail'],
                        'description' => $row['description']
                    ];
                }
                elseif($row['type'] == 'links') {
                    $row['links'] = json_decode(html_entity_decode($row['message_data']), true);
                }
                // File info
                elseif($row['type'] == 'file') {
                    $row['file'] = json_decode(html_entity_decode($row['message_data']), true);
                }
                
                // Location info
                if($row['latitude'] && $row['longitude']) {
                    $row['location']['latitude'] = $row['latitude'];
                    $row['location']['longitude'] = $row['longitude'];
                }
                unset($row['latitude']);
                unset($row['longitude']);
            }

            $message_data[] = $row;
        }
        if(empty($message_data)) {
            $json_messages = $Zalo->get_messages($zalo_id, $offset);
            $result['messages_info'] = json_decode($json_messages, true);
            $result['messages_info']['offset'] = count($result['messages_info']['data']) + $offset;
            $is_using_api_for_message = true;
        }
        else {
            $result['messages_info']['error']   = 0;
            $result['messages_info']['data']    = $message_data;
            $result['messages_info']['offset']  = count($message_data) + $offset;
        }

        // Quota info
        $json_quota = $Zalo->get_quota_user($zalo_id);
        $arr_quota  = json_decode($json_quota, true);
        if(isset($arr_quota['error']) && $arr_quota['error'] == 0) {
            $result['quota_info'] = $arr_quota['data'];
            $last_interaction = (int)$result['quota_info']['last_interaction'] / 1000; // Timestamp in seconds
            $time_check = (time() - $last_interaction) / 3600 / 24;

            if($result['quota_info']['cs_reply']['remain'] == 0 && $time_check < 7) {
                $json_quota_oa = $Zalo->get_quota_oa();
                $arr_quota_oa  = json_decode($json_quota_oa, true);
                if(isset($arr_quota_oa['error']) && $arr_quota_oa['error'] == 0) {
                    $result['quota_info']['oa_cs'] = $arr_quota_oa['data'][0]['remain'];
                }
            }
        }

        echo json_encode($result);

        // Save previous message
        try {
            if($is_using_api_for_message) {
                foreach($result['messages_info']['data'] as $m) {
                    $message_id = $m['message_id'] ?? '';
                    if(empty($message_id)) continue;
    
                    $mid = $db->getOne("SELECT id FROM ec_zalo_messages WHERE id = '$message_id'");
                    if(!$mid || empty($mid)) {
                        // Handle type
                        $subtype = '';
                        $mtype = $m['type'] ?? '';
                        if($mtype == 'text') {
                            $mtype = 'consultation';
                            $subtype = 'text';
                        }
                        else if($mtype == 'photo' || $mtype == 'image') {
                            $mtype = 'consultation';
                            $subtype = 'image';
                        }
                        else if($mtype == 'voice' || $mtype == 'audio') {
                            $subtype = 'audio';
                            $mtype = 'consultation';
                        }
                        else if (in_array($mtype, ['gif', 'sticker', 'video', 'file', 'location', 'link', 'links'])) {
                            $subtype = $mtype;
                            $mtype = 'consultation';
                        }
                        else {
                            $mtype = 'other';
                        }
    
                        // Location info
                        $lat = $long = '';
                        if(isset($m['location'])) {
                            $location = is_string($m['location']) ? json_decode($m['location'], true) : $m['location'];
                            $lat = $location['latitude'] ?? ''; 
                            $long = $location['longitude '] ?? ''; 
                        }
    
                        $zalomes = new EC_Zalo_Messages();
                        $zalomes->new_with_id = true;
                        $zalomes->id = $message_id;
                        $zalomes->src = $m['src'] ?? '';
                        $zalomes->from_id = $m['from_id'] ?? '';
                        $zalomes->to_id = $m['to_id'] ?? '';
                        $zalomes->timestamp = $m['time'] ?? 0;
                        $zalomes->type = $mtype;
                        $zalomes->sub_type = $subtype;
                        $zalomes->description = $m['message'] ?? '';
                        $zalomes->thumbnail = $m['thumb'] ?? '';
                        $zalomes->url = $m['url'] ?? '';
                        $zalomes->attached_description = $m['description'] ?? '';
                        $zalomes->latitude = $lat;
                        $zalomes->longitude = $long;
                        $zalomes->quote_message_id = $m['quote_id'] ?? '';
                        $zalomes->response = json_encode($m);
                        $zalomes->date_entered  = date('Y:m:d H:i:s', (int)($zalomes->timestamp / 1000));
                        $zalomes->date_modified = date('Y:m:d H:i:s', (int)($zalomes->timestamp / 1000));
                        $zalomes->name = 'Resaved';
                        $zalomes->save();
                    }
                }
            }
        }
        catch(Exception $e) {}
        finally {
            exit();
        }
    }
    elseif($action == 'get_user_info') {
        $Zalo = new Zalo();
        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";

        $json = $Zalo->get_user($zalo_id);

        echo $json;

        try {
            $arr = json_decode($json, true);
            if(isset($arr['error']) && $arr['error'] == 0) {
                $zalo_last_interaction = $arr['data']['user_last_interaction_date'] ?? ''; // d/m/Y
                if(!empty($zalo_last_interaction)) {
                    $zalo_last_interaction = date('Y-m-d', strtotime(str_replace("/", "-", $zalo_last_interaction)));
                    $zalo_last_interaction .= ' 00:00:00';

                    $sql_update_contact = "UPDATE contacts
                        SET zalo_last_interaction = '$zalo_last_interaction'
                        WHERE zalo_id = '$zalo_id'
                            AND (zalo_last_interaction IS NULLL OR zalo_last_interaction = '' OR zalo_last_interaction < '$zalo_last_interaction')
                            AND deleted = 0";
                    $db->query($sql_update_contact);
                }
            }
        }
        catch(Exception $e) {
            exit();
        }

        exit();
    }
    elseif($action == 'get_list_user') {
        $Zalo = new Zalo();
        $offset                     = isset($_POST['offset']) ? $_POST['offset'] : 0;
        $count                      = isset($_POST['count']) ? $_POST['count'] : 50;
        $tag_name                   = isset($_POST['tag_name']) ? $_POST['tag_name'] : '';
        $last_interaction_period    = isset($_POST['last_interaction_period']) ? $_POST['last_interaction_period'] : '';
        $format_list_chat           = isset($_POST['format_list_chat']) ? $_POST['format_list_chat'] : 0;
        $value                      = isset($_POST['value']) ? $_POST['value'] : '';

        if($value == 'default') {
            require_once("modules/EC_Zalo/views/view.chatzalo.php");
            $view = new Viewchatzalo();
            echo $view->get_list_user(0, []);
            exit();
        }
        elseif(in_array($value, ['L7D'])) $last_interaction_period = $value;
        elseif(!empty($value)) $tag_name = $value;

        $json = $Zalo->get_list_user($offset, $count, $last_interaction_period, null, $tag_name);
        $arr = json_decode($json, true);

        if(isset($arr['error']) && $arr['error'] == 0) {
            if(empty($arr['data']['users'])) {
                echo 'No data';
                exit();
            }

            if($format_list_chat == 1) {
                $html = '';

                foreach($arr['data']['users'] as $u) {
                    $zalo_id = $u['user_id'];
                    $json_user = $Zalo->get_user($zalo_id);
                    $arr_user = json_decode($json_user, true);

                    if(isset($arr_user['error']) && $arr_user['error'] == 0) {
                        $last_interaction = str_replace('/', '-', $arr_user['data']['user_last_interaction_date']); // d-m-Y
                        $time = '';

                        if($value == 'L7D') {
                            $current_date = date('d-m-Y');
                            $count_day = (strtotime($current_date) - strtotime($last_interaction)) / 3600 / 24;

                            if($count_day < 5 || $count_day > 7) continue;
                            $message = $count_day == 7 ? '<i class="text-danger">Còn 24 giờ</i>' : '<i>Còn '. (7 - $count_day) .' ngày</i>';
                            $time = strtotime($last_interaction);
                        }
                        else $message = '<i>Tương tác cuối vào ' . $arr_user['data']['user_last_interaction_date'] . '</i>';

                        $arr_message = [
                            'src'  => 1,
                            'type' => 'text',
                            'time' => $time,
                            'message' => $message
                        ];

                        require_once("modules/EC_Zalo/views/view.chatzalo.php");
                        $view = new Viewchatzalo();
                        $html .= $view->create_li_chat($arr_message, $arr_user['data']);
                    }
                }

                echo $html;
                exit();
            }

            echo $json;
            exit();
        }

        echo json_encode([
            "error" => 1,
            "message" => "Không tìm thấy kết quả",
            "data" => $arr
        ]);
        exit();
    }
    elseif($action == 'send_message') {
        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $type    = isset($_POST['type']) ? $_POST['type'] : "text";
        $data    = ['text' => isset($_POST['text']) ? $_POST['text'] : ""];

        if(empty($zalo_id) || empty($type)) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
                "data" => ["zalo_id" => $zalo_id, "type" => $type]
            ]);
            exit();
        }

        $Zalo = new Zalo();

        // Prepare body request (data)
        if ($type == 'image') {
            // Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_name = $_FILES['image']['name']; // name.ext
                $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    
                // Check extension
                if(!in_array($ext, $Zalo->get_file_extension('image'))) {
                    echo json_encode([
                        "error" => 1,
                        "message" => "Không hỗ trợ định dạng $ext",
                        "description" => "Chỉ hỗ trợ định dạng " . implode(',', $Zalo->get_file_extension('image'))
                    ]);
                    exit();
                }
    
                // Check size
                if($ext == 'gif' && $_FILES["image"]["size"] > 5000000) {
                    echo json_encode([
                        "error" => 1,
                        "message" => "Dung lượng ảnh quá lớn",
                        "description" => "Dung lượng tối đa 5MB cho định dạng .gif"
                    ]);
                    exit();
                }
                elseif($ext != 'gif' && $_FILES["image"]["size"] > 1000000) {
                    echo json_encode([
                        "error" => 1,
                        "message" => "Dung lượng ảnh quá lớn",
                        "description" => "Dung lượng tối đa 1MB cho định dạng jpg, png"
                    ]);
                    exit();
                }
                
                $json_upload = $Zalo->upload($_FILES['image']['tmp_name'], $ext, $image_name);
                $arr_upload = json_decode($json_upload, true);
                
                if(isset($arr_upload['error']) && $arr_upload['error'] == 0) {
                    $attachment_id = isset($arr_upload['data']['attachment_id']) ? $arr_upload['data']['attachment_id'] : '';
                    $data['element'] = [
                        "media_type" => $ext == 'gif' ? 'gif' : 'image',
                        "attachment_id" => $attachment_id
                    ];
                }
                else {
                    echo json_encode([
                        'error' => 1,
                        'message' => 'Gửi ảnh thất bại, vui lòng thử lại',
                        'data' => $arr_upload
                    ]);
                    exit();
                }
            }
            else {
                echo json_encode([
                    'error' => 1,
                    'message' => 'Gửi ảnh thất bại, vui lòng thử lại',
                    'data' => $_FILES
                ]);
                exit();
            }
        }
        elseif ($type == 'file') {
            // Upload
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['file']['name']; // name.ext
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
                // Check extension
                if(!in_array($ext, $Zalo->get_file_extension('file'))) {
                    echo json_encode([
                        "error" => 1,
                        "message" => "Không hỗ trợ định dạng $ext",
                        "description" => "Chỉ hỗ trợ định dạng ". implode(',', $Zalo->get_file_extension('file'))
                    ]);
                    exit();
                }
    
                // Check size
                if($_FILES["file"]["size"] > 5000000) {
                    echo json_encode([
                        "error" => 1,
                        "message" => "Dung lượng file quá lớn",
                        "description" => "Tối đa 5MB"
                    ]);
                    exit();
                }
                
                $json_upload = $Zalo->upload($_FILES["file"]["tmp_name"], $ext, $file_name);
                $arr_upload = json_decode($json_upload, true);
                
                if(isset($arr_upload['error']) && $arr_upload['error'] == 0) {
                    $data['token'] = isset($arr_upload['data']['token']) ? $arr_upload['data']['token'] : '';
                }
                else {
                    echo json_encode([
                        'error' => 1,
                        'message' => 'Gửi file thất bại, vui lòng thử lại',
                        'data' => $arr_upload
                    ]);
                    exit();
                }
            }
            else {
                echo json_encode([
                    'error' => 1,
                    'message' => 'Gửi file thất bại, vui lòng thử lại',
                    'data' => $_FILES
                ]);
                exit();
            }
        }
        elseif ($type == 'request_user_info') {
            $data['element'] = $Zalo->get_template($type);
        }

        // Tin nhắn text reply
        if(isset($_POST['quote_message_id'])) $data['quote_message_id'] = $_POST['quote_message_id'];

        // Send
        echo $Zalo->send_consultation($type, $zalo_id, $data);
        exit();
    }
    elseif($action == 'send_zns') { // Version 2
        $phone          = isset($_POST['phone']) ? $_POST['phone'] : "";
        $type_zns       = isset($_POST['type_zns']) ? $_POST['type_zns'] : "";
        $parent_id      = isset($_POST['parent_id']) ? $_POST['parent_id'] : "";
        $template_data  = isset($_POST['template_data']) ? str_replace('&quot;', '"', $_POST['template_data']) : ""; // json

        if(empty($phone) || empty($type_zns) || empty($template_data) || empty($parent_id)) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu cung cấp không hợp lệ",
                "data" => [
                    "phone" => $phone,
                    "type_zns" => $type_zns,
                    "parent_id" => $parent_id,
                    "template_data" => json_decode($template_data, true)
                ]
            ]);
            exit();
        }

        $Zalo = new Zalo();
        $template_id = $Zalo->get_template_id_zns($type_zns);
        $json = $Zalo->send_zns($phone, $template_id, $template_data);
        $arr  = json_decode($json, true);

        $category = (in_array($template_id, ['347078', '347088', '345209', '288276', '288279', '346656']) ? 'transaction' : 'customer_care');
        $template_data = json_decode($template_data, true);
        $template_data['template_id'] = $template_id;

        if(isset($arr['error']) && $arr['error'] == 0) {
            $m = new EC_Messages();
            $m->send_from       = $Zalo->get_oa_id();
            $m->send_to         = $phone;
            $m->content         = $Zalo->get_template_name_zns($template_id);
            $m->type            = 'zalo_zns';
            $m->category        = $category;
            $m->send_time       = date("Y-m-d H:i:s", strtotime('-7 hours')); // Lưu xuống db giảm 7 tiếng
            $m->parent_type     = 'EC_Flight_Bookings';
            $m->parent_id       = $parent_id;
            $m->data            = json_encode($template_data);
            $m->response        = $json;
            $m->status          = 'done';
            $m->cost            = 220;
            $m->assigned_user_id = $current_user->id;
            $m->save();

            try {
                $msg_id = $arr['data']['msg_id'] ?? '';
                $timestamp = $arr['data']['sent_time'] ?? 0;

                $zalomes = new EC_Zalo_Messages();
                $zalomes->new_with_id   = true;
                $zalomes->id            = $msg_id;
                $zalomes->src           = 0;
                $zalomes->from_id       = $Zalo->get_oa_id();
                $zalomes->to_id         = $phone;
                $zalomes->timestamp     = $timestamp;
                $zalomes->type          = 'zns';
                $zalomes->sub_type      = $type_zns;
                $zalomes->description   = $Zalo->get_template_name_zns($template_id);
                $zalomes->template_id   = $template_id;
                $zalomes->data          = json_encode($template_data);
                $zalomes->response      = trim($json);
                $zalomes->assigned_user_id = $current_user->id;
                $zalomes->save();
            }
            catch(Exception $e) {
                sendTestTelegram("Saved failed ZNS message\n" . $e->getMessage() . "\n\n" . $json);
            }
            
            $fullname = $current_user->last_name.' '.$current_user->first_name;
            $Zalo->send_to_telegram("<b>".$fullname.'</b>: Gửi '.$Zalo->get_template_name_zns($template_id).' đến Zalo <b>' . $phone .'</b>');

            echo json_encode([
                "error"   => 0,
                "message" => "Gửi tin nhắn thành công",
                "data"    => $arr
            ]);
        }
        else {
            $error_code = isset($arr['error']) ? $arr['error'] : '';
            $message = $Zalo->get_error_description_zns($error_code);

            $m = new EC_Messages();
            $m->send_from       = $Zalo->get_oa_id();
            $m->send_to         = $phone;
            $m->content         = $Zalo->get_template_name_zns($template_id);
            $m->type            = 'zalo_zns';
            $m->category        = $category;
            $m->send_time       = date("Y-m-d H:i:s", strtotime('-7 hours')); // Lưu xuống db giảm 7 tiếng
            $m->parent_type     = 'EC_Flight_Bookings';
            $m->parent_id       = $parent_id;
            $m->data            = json_encode($template_data);
            $m->response        = $json;
            $m->status          = 'fail';
            $m->description     = $message;
            $m->assigned_user_id = $current_user->id;
            $m->save();

            echo json_encode(["error" => 1, "message" => $message, "data" => $arr]);
        }
        exit();
    }
    elseif($action == 'update_user_alias') {
        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $alias   = isset($_POST['alias']) ? $_POST['alias'] : "";

        if(empty($zalo_id) || empty($alias)) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
                "data" => ["zalo_id" => $zalo_id, "alias" => $alias]
            ]);
            exit();
        }

        $Zalo = new Zalo();
        echo $Zalo->update_user($zalo_id, $alias);
        exit();
    }
    elseif($action == 'update_user_info') {
        $zalo_id        = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $name           = isset($_POST['info_user_name']) ? $_POST['info_user_name'] : "";
        $phone          = isset($_POST['info_user_phone']) ? $_POST['info_user_phone'] : "";
        $address        = isset($_POST['info_user_address']) ? $_POST['info_user_address'] : "";
        $city_id        = isset($_POST['info_user_city']) ? (int)$_POST['info_user_city'] : 0;
        $district_id    = isset($_POST['info_user_district']) ? (int)$_POST['info_user_district'] : 0;
        $shared_info = [
            "name" => $name,
            "phone" => $phone,
            "address" => $address,
            "city_id" => $city_id,
            "district_id" => $district_id
        ];

        if(strlen($zalo_id)*strlen($name)*strlen($phone)*strlen($address)*$district_id*$city_id === 0) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
                "data" => [
                    "zalo_id" => $zalo_id,
                    "name" => $name,
                    "phone" => $phone,
                    "address" => $address,
                    "city_id" => $city_id,
                    "district_id" => $district_id
                ]
            ]);
            exit();
        }

        $Zalo = new Zalo();
        echo $Zalo->update_user($zalo_id, '', $shared_info);
        exit();
    }
    elseif($action == 'add_tag_user') {
        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $tag_name = isset($_POST['tag_name']) ? $_POST['tag_name'] : "";

        if(empty($zalo_id) || empty($tag_name)) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
                "data" => [
                    "zalo_id" => $zalo_id,
                    "tag_name" => $tag_name
                ]
            ]);
            exit();
        }

        $Zalo = new Zalo();
        echo $Zalo->add_tag_user($zalo_id, $tag_name);
        exit();
    }
    elseif($action == 'remove_tag_user') {
        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $tag_name = isset($_POST['tag_name']) ? $_POST['tag_name'] : "";

        if(empty($zalo_id) || empty($tag_name)) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
                "data" => [
                    "zalo_id" => $zalo_id,
                    "tag_name" => $tag_name
                ]
            ]);
            exit();
        }

        $Zalo = new Zalo();
        echo $Zalo->remove_tag_user($zalo_id, $tag_name);
        exit();
    }
    elseif($action == 'save_contact') {
        $zalo_id    = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $phone      = isset($_POST['phone']) ? $_POST['phone'] : "";
        $name       = isset($_POST['name']) ? $_POST['name'] : "";
        $alias      = isset($_POST['alias']) ? $_POST['alias'] : "";
        $city       = isset($_POST['city']) ? $_POST['city'] : "";
        $district   = isset($_POST['district']) ? $_POST['district'] : "";
        $address    = isset($_POST['address']) ? $_POST['address'] : "";
        
        if(empty($zalo_id)) {
            echo json_encode([
                "error" => 1,
                "message" => "Không có dữ liệu để lưu",
                "data" => [
                    'zalo_id'   => $zalo_id,
                    'phone'     => $phone,
                    'name'      => $name,
                    'alias'     => $alias,
                    'city'      => $city,
                    'district'  => $district,
                    'address'   => $address,
                ]
            ]);
            exit();
        }

        $Zalo = new Zalo();
        if(empty($phone)) $phone = $Zalo->get_phone_by_alias($alias);

        // Lấy thông tin liên hệ nếu đã tồn tại
        $sql_exist = "SELECT id FROM contacts WHERE zalo_id = '$zalo_id' AND deleted = 0 LIMIT 1";
        $contact_id = $db->getOne($sql_exist);
        if(!empty($phone) && (!$contact_id || empty($contact_id))) {
            $sql_exist = "SELECT id FROM contacts WHERE phone_mobile = '$phone' AND deleted = 0 LIMIT 1";
            $contact_id = $db->getOne($sql_exist);
        }

        // Lưu thông tin
        $contact = new Contact();
        if($contact_id && !empty($contact_id)) {
            $contact->retrieve($contact_id);
        }
        else {
            $contact->description = "Liên hệ tạo từ Zalo OA";
            $contact->assigned_user_id = $current_user->id;
        }
        $contact->zalo_id = $zalo_id;
        $contact->phone_mobile = $phone;
        $contact->last_name = $name;
        $contact->primary_address_city = $city;
        $contact->primary_address_state = $district;
        $contact->primary_address_street = $address;
        $contact->save();

        echo json_encode([
            "error" => 0,
            "message" => "Lưu thành công"
        ]);
        exit();
    }
    elseif($action == 'search_contact') {
        $search_value = isset($_POST['search_value']) ? $_POST['search_value'] : "";

        if(empty($search_value)) {
            echo '';
            exit();
        }

        $html = "";
        $sql_search = "";
        if(is_numeric($search_value)) {
            $condition = strlen($search_value) < 10 ? "phone_mobile LIKE '%$search_value'" : "phone_mobile = $search_value";
            $sql_search = "SELECT DISTINCT(zalo_id)
                        FROM contacts
                        WHERE $condition
                            AND zalo_id IS NOT NULL
                            AND zalo_id <> ''
                            AND deleted = 0";
        }
        else {
            $sql_search = "SELECT DISTINCT(zalo_id)
                        FROM contacts
                        WHERE MATCH(last_name) AGAINST('\"$search_value\"')
                            AND zalo_id IS NOT NULL
                            AND zalo_id <> ''
                            AND contacts.deleted = 0";
        }

        require_once("modules/EC_Zalo/views/view.chatzalo.php");
        $view = new Viewchatzalo();
        $Zalo = new Zalo();
        $res  = $db->query($sql_search);
        while ($row = $db->fetchByAssoc($res)) {
            if(!$row['zalo_id'] || empty($row['zalo_id'])) continue;

            $Zalo = new Zalo();
            $json = $Zalo->get_user($row['zalo_id']);
            $arr  = json_decode($json, true);

            if(isset($arr['error']) && $arr['error'] == 0) {
                $arr_message = [
                    'src'  => 1,
                    'type' => 'text',
                    'time' => '',
                    'message' => 'Tương tác cuối vào ' . $arr['data']['user_last_interaction_date']
                ];

                $html .= $view->create_li_chat($arr_message, $arr['data']);
            }
        }

        echo $html;
        exit();
    }

    echo json_encode([
        "error" => 1,
        "message" => "Tính năng chưa chỗ trợ"
    ]);
    exit();
}

header("HTTP/1.0 405 Method Not Allowed");
echo json_encode([
    "error" => 1,
    "message" => "Method not allowed"
]);
exit();
?>