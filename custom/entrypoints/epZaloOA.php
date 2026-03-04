<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once("modules/EC_Zalo/Zalo.php");
    require_once("modules/EC_Zalo/OMNI.php");
    global $current_user, $db;

    $action = isset($_POST['action']) ? $_POST['action'] : "";

    // if($action == 'get_recent_messages') { // Version 2
    //     $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : 0;
    //     $current_list_user = isset($_POST['current_list_user']) && !empty($_POST['current_list_user']) ? array_unique(explode(',', $_POST['current_list_user'])) : []; // array

    //     $bean_zalo = new EC_Zalo();
    //     $results = $bean_zalo->get_list_user($timestamp, $current_list_user);
    //     echo json_encode($results);
    //     exit();
    // }
    // if($action == 'get_messages') { // Version 2
    //     $Zalo = new Zalo();
    //     $bean_zalo = new EC_Zalo();

    //     $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
    //     $offset  = isset($_POST['offset']) ? $_POST['offset'] : 0;
    //     $is_get_user_info = isset($_POST['is_get_user_info']) ? (int)$_POST['is_get_user_info'] : 1;
    //     $result = [];

    //     // User info
    //     if($is_get_user_info == 1) {
    //         $user_data = $bean_zalo->get_zalo_user_info($zalo_id);
    //         $result['user_info']['data'] = $user_data;
    //         $result['user_info']['error'] = !empty($user_data) ? 0 : 1;
    //     }
    //     else $user_data = json_decode(urldecode(base64_decode(trim($_POST['user_info'] ?? ''))), true);

    //     // Message info
    //     $zalo_phone = $user_data['shared_info']['phone'] ?? '';
    //     $message_data = [];
    //     $is_using_api_for_message = false;
    //     $sql = "SELECT zm.message_id
    //             ,zm.description AS message 
    //             ,zm.src
    //             ,zm.from_id
    //             ,zm.to_id
    //             ,zm.timestamp
    //             ,zm.type AS message_type
    //             ,zm.sub_type AS type
    //             ,zm.thumbnail
    //             ,zm.url
    //             ,zm.attached_description AS description
    //             ,zm.latitude
    //             ,zm.longitude
    //             ,zm.quote_message_id AS quote_id
    //             ,zm.template_id
    //             ,zm.data AS message_data
    //             ,zm.assigned_user_id
    //             ,TRIM(CONCAT(IFNULL(u.last_name, ''), ' ', IFNULL(u.first_name, ''))) AS assigned_user_name 
    //             ,u.photo
    //         FROM ec_zalo_messages zm
    //             LEFT JOIN users u ON u.id = zm.assigned_user_id
    //         WHERE (zm.from_id = '$zalo_id' OR zm.to_id = '$zalo_id' OR zm.to_id = '$zalo_phone')
    //             AND zm.deleted = 0
    //         ORDER BY zm.timestamp DESC
    //         LIMIT $offset, $bean_zalo->limit_message";

    //     $res = $db->query($sql);
    //     while($row = $db->fetchByAssoc($res)) {
    //         $row['src'] = (int)$row['src'];
    //         $row['message_data'] = !empty($row['message_data']) ? json_decode(html_entity_decode($row['message_data']), true) : [];
    //         if($row['type'] == 'business_card') $row['description'] = html_entity_decode($row['description']);

    //         // Handle quote message data
    //         if($row['quote_id'] && !empty($row['quote_id'])) {
    //             $row['quote_data'] = $bean_zalo->get_quote_message_data($row['quote_id']);
    //         }

    //         // Handle avatar assigned user (admin)
    //         $row['assigned_user_avatar'] = '';
    //         if($row['src'] == 0 && $row['photo'] && !empty($row['photo']) && !empty($row['assigned_user_id'])) {
    //             $row['assigned_user_avatar'] = "index.php?entryPoint=download&id=".$row['assigned_user_id']."_photo&type=Users";
    //         }
    //         unset($row['photo']);

    //         // Format by message type
    //         if($row['message_type'] == 'zns') {
    //             unset($row['thumbnail']);
    //             unset($row['url']);
    //             unset($row['description']);
    //             unset($row['latitude']);
    //             unset($row['longitude']);
    //             unset($row['quote_id']);
    //         }
    //         else if($row['message_type'] == 'call') {
    //             $row['type'] = $GLOBALS['app_list_strings']['calls_direction_list'][$row['type']] ?? $row['type'];

    //             unset($row['thumbnail']);
    //             unset($row['url']);
    //             unset($row['description']);
    //             unset($row['latitude']);
    //             unset($row['longitude']);
    //             unset($row['quote_id']);
    //             unset($row['template_id']);
    //         }

    //         $message_data[] = $row;
    //     }
    //     if(empty($message_data)) {
    //         $json_messages = $Zalo->get_messages($zalo_id, $offset + 1); // +1 for offset in get more message
    //         $arr_messages = json_decode($json_messages, true);

    //         if(isset($arr_messages['error']) && $arr_messages['error'] == 0) {
    //             $is_using_api_for_message = true;
    //             $result['messages_info']['error'] = 0;

    //             // Format data again
    //             foreach($arr_messages['data'] as $m) {
    //                 if($m['type'] == 'links') $m['message_data'] = $m['links'];

    //                 $result['messages_info']['data'][] = $m;
    //             }
    //             $result['messages_info']['offset'] = count($result['messages_info']['data']) + $offset;
    //         }
    //         else {
    //             $is_using_api_for_message = false;
    //             $result['messages_info']['error'] = 1;
    //             $result['messages_info']['data'] = [];
    //         }
    //     }
    //     else {
    //         $count_message_data = count($message_data);
    //         $result['messages_info']['error']   = 0;
    //         $result['messages_info']['data']    = $message_data;
    //         $result['messages_info']['offset']  = $count_message_data == $bean_zalo->limit_message ? count($message_data) + $offset : -1;
    //     }

    //     // Quota info
    //     $json_quota = $Zalo->get_quota_user($zalo_id);
    //     $arr_quota  = json_decode($json_quota, true);
    //     if(isset($arr_quota['error']) && $arr_quota['error'] == 0) {
    //         $result['quota_info'] = $arr_quota['data'];
    //         $last_interaction = (int)$result['quota_info']['last_interaction'] / 1000; // Timestamp in seconds
    //         $time_check = (time() - $last_interaction) / 3600 / 24;

    //         if($result['quota_info']['cs_reply']['remain'] == 0 && $time_check < 7) {
    //             $json_quota_oa = $Zalo->get_quota_oa();
    //             $arr_quota_oa  = json_decode($json_quota_oa, true);
    //             if(isset($arr_quota_oa['error']) && $arr_quota_oa['error'] == 0) {
    //                 $result['quota_info']['oa_cs'] = $arr_quota_oa['data'][0]['remain'];
    //             }
    //         }
    //     }

    //     echo json_encode($result);

    //     // Save previous message
    //     try {
    //         if($is_using_api_for_message) {
    //             foreach($arr_messages['data'] as $m) {
    //                 $message_id = $m['message_id'] ?? '';
    //                 if(empty($message_id)) continue;
    
    //                 $record_id = $db->getOne("SELECT id FROM ec_zalo_messages WHERE message_id = '$message_id'");
    //                 if(!$record_id || empty($record_id)) {
    //                     $mdata = [];

    //                     // Handle type
    //                     $subtype = '';
    //                     $mtype = $m['type'] ?? '';
    //                     if($mtype == 'text') {
    //                         $mtype = 'consultation';
    //                         $subtype = 'text';
    //                     }
    //                     else if($mtype == 'photo' || $mtype == 'image') {
    //                         $mtype = 'consultation';
    //                         $subtype = 'image';
    //                     }
    //                     else if($mtype == 'voice' || $mtype == 'audio') {
    //                         $subtype = 'audio';
    //                         $mtype = 'consultation';
    //                     }
    //                     else if (in_array($mtype, ['gif', 'sticker', 'video', 'file', 'location', 'link', 'links'])) {
    //                         $subtype = $mtype;
    //                         $mtype = 'consultation';

    //                         if($subtype == 'links') {
    //                             foreach($m['links'] as $link) {
    //                                 $mdata[] = [
    //                                     'title' => $link['title'],
    //                                     'url' => $link['url'],
    //                                     'thumbnail' => $link['thumb'],
    //                                     'description' => $link['description']
    //                                 ];
    //                             }
    //                         }
    //                     }
    //                     else {
    //                         $mtype = 'other';
    //                         $subtype = 'nosupport';
    //                     }
    
    //                     // Location info
    //                     $lat = $long = '';
    //                     if(isset($m['location'])) {
    //                         $location = is_string($m['location']) ? json_decode($m['location'], true) : $m['location'];
    //                         $lat = $location['latitude'] ?? ''; 
    //                         $long = $location['longitude '] ?? ''; 
    //                     }
    
    //                     $zalomes = new EC_Zalo_Messages();
    //                     $zalomes->id = '';
    //                     $zalomes->message_id = $message_id;
    //                     $zalomes->src = $m['src'] ?? '';
    //                     $zalomes->from_id = $m['from_id'] ?? '';
    //                     $zalomes->to_id = $m['to_id'] ?? '';
    //                     $zalomes->timestamp = $m['time'] ?? 0;
    //                     $zalomes->type = $mtype;
    //                     $zalomes->sub_type = $subtype;
    //                     $zalomes->description = $m['message'] ?? '';
    //                     $zalomes->thumbnail = $m['thumb'] ?? '';
    //                     $zalomes->url = $m['url'] ?? '';
    //                     $zalomes->attached_description = $m['description'] ?? '';
    //                     $zalomes->latitude = $lat;
    //                     $zalomes->longitude = $long;
    //                     $zalomes->quote_message_id = $m['quote_id'] ?? '';
    //                     $zalomes->data = json_encode($mdata);
    //                     $zalomes->response = json_encode($m);
    //                     $zalomes->date_entered  = date('Y:m:d H:i:s', (int)($zalomes->timestamp / 1000));
    //                     $zalomes->date_modified = date('Y:m:d H:i:s', (int)($zalomes->timestamp / 1000));
    //                     $zalomes->name = 'Resaved';
    //                     $zalomes->save();
    //                 }
    //             }
    //         }
    //     }
    //     catch(Exception $e) {}
    //     finally {
    //         exit();
    //     }
    // }
    // elseif($action == 'get_user_info') { // Version 2
    //     $bean_zalo = new EC_Zalo();
    //     $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";

    //     $user_data = $bean_zalo->get_zalo_user_info($zalo_id);
    //     $result['data'] = $user_data;
    //     $result['error'] = !empty($user_data) ? 0 : 1;

    //     echo json_encode($result);
    //     exit();
    // }
    // if($action == 'get_list_user') { // Version 2
    //     $Zalo = new Zalo();
    //     $offset                     = isset($_POST['offset']) ? $_POST['offset'] : 0;
    //     $count                      = isset($_POST['count']) ? $_POST['count'] : 50;
    //     $tag_name                   = isset($_POST['tag_name']) ? $_POST['tag_name'] : '';
    //     $last_interaction_period    = isset($_POST['last_interaction_period']) ? $_POST['last_interaction_period'] : '';
    //     $format_list_chat           = isset($_POST['format_list_chat']) ? $_POST['format_list_chat'] : 0;
    //     $value                      = isset($_POST['value']) ? $_POST['value'] : '';

    //     if(in_array($value, ['L7D'])) $last_interaction_period = $value;
    //     elseif(!empty($value)) $tag_name = $value;

    //     $json = $Zalo->get_list_user($offset, $count, $last_interaction_period, null, $tag_name);
    //     $arr = json_decode($json, true);

    //     if(isset($arr['error']) && $arr['error'] == 0) {
    //         if(empty($arr['data']['users'])) {
    //             echo json_encode(["error" => 0, "message" => "Not found", "data" => []]);
    //             exit();
    //         }

    //         $results = [];
    //         $list2 = [];
    //         $bean_zalo = new EC_Zalo();
    //         foreach($arr['data']['users'] as $u) {
    //             $zalo_id = $u['user_id'];
    //             $user_info = $bean_zalo->get_zalo_user_info($zalo_id);
    //             $lastest_message = $bean_zalo->get_lastest_message_user($zalo_id);

    //             if(!empty($user_info)) {
    //                 $last_interaction = str_replace('/', '-', $user_info['user_last_interaction_date']); // d-m-Y

    //                 if($value == 'L7D') {
    //                     $current_date = date('d-m-Y');
    //                     $count_day = (strtotime($current_date) - strtotime($last_interaction)) / 3600 / 24;

    //                     if($count_day < 6 || $count_day > 7) continue;

    //                     // Message
    //                     if(!empty($lastest_message)) $message = $lastest_message['message'];
    //                     else if($count_day == 7) $message = 'Sắp hết hạn tương tác';
    //                     else $message = 'Còn 1 ngày';
    //                 }
    //                 else {
    //                     // Message
    //                     if(!empty($lastest_message)) $message = $lastest_message['message'];
    //                     else $message = 'Tương tác cuối vào ' . $user_info['user_last_interaction_date'];
    //                 }

    //                 if(empty($lastest_message)) {
    //                     $lastest_message['message_type'] = 'custom';
    //                     $lastest_message['type'] = 'custom';
    //                     $lastest_message['src'] = 1;
    //                     $lastest_message['timestamp'] = strtotime($last_interaction) * 1000;
    //                     $lastest_message['from_id'] = $zalo_id;
    //                     $lastest_message['to_id'] = $Zalo->get_oa_id();
    //                 }
    //                 $lastest_message['message'] = $message;

    //                 $key = $lastest_message['timestamp'] . '_' . $zalo_id;
    //                 $results[$key] = [
    //                     'message_info' => $lastest_message,
    //                     'user_info' => $user_info
    //                 ];
    //             }
    //         }

    //         $value == 'L7D' ? ksort($results) : krsort($results);
    //         echo json_encode([
    //             "error" => 0,
    //             "message" => "Success",
    //             "data" => $results
    //         ]);
    //         exit();
    //     }

    //     echo json_encode(["error" => 1, "message" => "Not found", "response" => $arr]);
    //     exit();
    // }
    // elseif($action == 'send_message') { // Version 2
    //     $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
    //     $type    = isset($_POST['type']) ? $_POST['type'] : "text";
    //     $data    = ['text' => isset($_POST['text']) ? $_POST['text'] : ""];

    //     if(empty($zalo_id) || empty($type)) {
    //         echo json_encode([
    //             "error" => 1,
    //             "message" => "Dữ liệu không hợp lệ",
    //             "data" => ["zalo_id" => $zalo_id, "type" => $type]
    //         ]);
    //         exit();
    //     }

    //     $Zalo = new Zalo();

    //     // Prepare body request (data)
    //     if ($type == 'image') {
    //         // Upload
    //         if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    //             $image_name = $_FILES['image']['name']; // name.ext
    //             $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    
    //             // Check extension
    //             if(!in_array($ext, $Zalo->get_file_extension('image'))) {
    //                 echo json_encode([
    //                     "error" => 1,
    //                     "message" => "Không hỗ trợ định dạng $ext",
    //                     "description" => "Chỉ hỗ trợ định dạng " . implode(',', $Zalo->get_file_extension('image'))
    //                 ]);
    //                 exit();
    //             }
    
    //             // Check size
    //             if($ext == 'gif' && $_FILES["image"]["size"] > 5000000) {
    //                 echo json_encode([
    //                     "error" => 1,
    //                     "message" => "Dung lượng ảnh quá lớn",
    //                     "description" => "Dung lượng tối đa 5MB cho định dạng .gif"
    //                 ]);
    //                 exit();
    //             }
    //             elseif($ext != 'gif' && $_FILES["image"]["size"] > 1000000) {
    //                 echo json_encode([
    //                     "error" => 1,
    //                     "message" => "Dung lượng ảnh quá lớn",
    //                     "description" => "Dung lượng tối đa 1MB cho định dạng jpg, png"
    //                 ]);
    //                 exit();
    //             }
                
    //             $json_upload = $Zalo->upload($_FILES['image']['tmp_name'], $ext, $image_name);
    //             $arr_upload = json_decode($json_upload, true);
                
    //             if(isset($arr_upload['error']) && $arr_upload['error'] == 0) {
    //                 $attachment_id = isset($arr_upload['data']['attachment_id']) ? $arr_upload['data']['attachment_id'] : '';
    //                 $data['element'] = [
    //                     "media_type" => $ext == 'gif' ? 'gif' : 'image',
    //                     "attachment_id" => $attachment_id
    //                 ];
    //             }
    //             else {
    //                 echo json_encode([
    //                     'error' => 1,
    //                     'message' => 'Gửi ảnh thất bại, vui lòng thử lại',
    //                     'data' => $arr_upload
    //                 ]);
    //                 exit();
    //             }
    //         }
    //         else {
    //             echo json_encode([
    //                 'error' => 1,
    //                 'message' => 'Gửi ảnh thất bại, vui lòng thử lại',
    //                 'data' => $_FILES
    //             ]);
    //             exit();
    //         }
    //     }
    //     elseif ($type == 'file') {
    //         // Upload
    //         if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    //             $file_name = $_FILES['file']['name']; // name.ext
    //             $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    //             // Check extension
    //             if(!in_array($ext, $Zalo->get_file_extension('file'))) {
    //                 echo json_encode([
    //                     "error" => 1,
    //                     "message" => "Không hỗ trợ định dạng $ext",
    //                     "description" => "Các định dạng hỗ trợ: ". implode(', ', $Zalo->get_file_extension('file'))
    //                 ]);
    //                 exit();
    //             }
    
    //             // Check size
    //             if($_FILES["file"]["size"] > 5000000) {
    //                 echo json_encode([
    //                     "error" => 1,
    //                     "message" => "Dung lượng file quá lớn",
    //                     "description" => "Tối đa 5MB"
    //                 ]);
    //                 exit();
    //             }
                
    //             $json_upload = $Zalo->upload($_FILES["file"]["tmp_name"], $ext, $file_name);
    //             $arr_upload = json_decode($json_upload, true);
                
    //             if(isset($arr_upload['error']) && $arr_upload['error'] == 0) {
    //                 $data['token'] = isset($arr_upload['data']['token']) ? $arr_upload['data']['token'] : '';
    //             }
    //             else {
    //                 echo json_encode([
    //                     'error' => 1,
    //                     'message' => 'Gửi file thất bại, vui lòng thử lại',
    //                     'data' => $arr_upload
    //                 ]);
    //                 exit();
    //             }
    //         }
    //         else {
    //             echo json_encode([
    //                 'error' => 1,
    //                 'message' => 'Gửi file thất bại, vui lòng thử lại',
    //                 'data' => $_FILES
    //             ]);
    //             exit();
    //         }
    //     }
    //     elseif ($type == 'request_user_info') {
    //         $data['element'] = $Zalo->get_template($type);
    //     }

    //     // Tin nhắn text reply
    //     if(isset($_POST['quote_message_id'])) $data['quote_message_id'] = $_POST['quote_message_id'];

    //     // Send
    //     $json_message = $Zalo->send_consultation($type, $zalo_id, $data);
    //     $arr_message = json_decode($json_message, true);

    //     if(isset($arr_message['error']) && $arr_message['error'] == 0) {
    //         $zalomes = new EC_Zalo_Messages();
    //         $zalomes->id = '';
    //         $zalomes->message_id        = $arr_message['data']['message_id'] ?? '';
    //         $zalomes->src               = 0;
    //         $zalomes->from_id           = $Zalo->get_oa_id();
    //         $zalomes->to_id             = $zalo_id;
    //         $zalomes->timestamp         = round(microtime(true) * 1000); // Milliseconds
    //         $zalomes->type              = 'consultation';
    //         $zalomes->sub_type          = $type == 'image' ? ($ext == 'gif' ? 'gif' : 'image') : $type;
    //         $zalomes->description       = $data['text'] ?? '';
    //         $zalomes->quote_message_id  = $data['quote_message_id'] ?? '';
    //         $zalomes->response          = trim($json_message);
    //         $zalomes->assigned_user_id  = $current_user->id;
    //         $zalomes->save();
    //     }

    //     echo $json_message;
    //     exit();
    // }
    // elseif($action == 'send_zns') { // Version 2
    //     $phone          = isset($_POST['phone']) ? $_POST['phone'] : "";
    //     $type_zns       = isset($_POST['type_zns']) ? $_POST['type_zns'] : "";
    //     $parent_id      = isset($_POST['parent_id']) ? $_POST['parent_id'] : "";
    //     $template_data  = isset($_POST['template_data']) ? str_replace('&quot;', '"', $_POST['template_data']) : ""; // json

    //     if(empty($phone) || empty($type_zns) || empty($template_data) || empty($parent_id)) {
    //         echo json_encode([
    //             "error" => 1,
    //             "message" => "Dữ liệu cung cấp không hợp lệ",
    //             "data" => [
    //                 "phone" => $phone,
    //                 "type_zns" => $type_zns,
    //                 "parent_id" => $parent_id,
    //                 "template_data" => json_decode($template_data, true)
    //             ]
    //         ]);
    //         exit();
    //     }

    //     $Zalo = new Zalo();
    //     $Omni = new OMNI();
    //     $template_id = $template_name = '';
    //     // if(in_array($type_zns, ['journey-one-way', 'journey-round-trip', 'payment'])) {
    //     //     $template_id = $Zalo->get_template_id_zns($type_zns);
    //     //     $template_name = $Zalo->get_template_name_zns($template_id);
    //     //     $json = $Zalo->send_zns($phone, $template_id, $template_data);
    //     // }
    //     // else {
    //         $template_id = $Omni->getTemplateCode($type_zns);
    //         $template_name = $Omni->getTemplateName($template_id);
    //         $json = $Omni->sendMessage($phone, $template_id, json_decode($template_data, true));
    //     // }

    //     $arr  = json_decode($json, true);
    //     $category = (in_array($template_id, ['347078', '347088', '345209', '288276', '288279', '346656']) ? 'transaction' : 'customer_care');
    //     $template_data = json_decode($template_data, true);
    //     $template_data['template_id'] = $template_id;

    //     // if((isset($arr['error']) && $arr['error'] == 0) || (isset($arr['status']) && $arr['status'] == 1)) {
    //     if(isset($arr['status']) && $arr['status'] == 1) {
    //         $m = new EC_Messages();
    //         $m->send_from       = $Zalo->get_oa_id();
    //         $m->send_to         = $phone;
    //         $m->content         = $template_name;
    //         $m->type            = 'zalo_zns';
    //         $m->category        = $category;
    //         $m->send_time       = date("Y-m-d H:i:s", strtotime('-7 hours')); // Lưu xuống db giảm 7 tiếng
    //         $m->parent_type     = 'EC_Flight_Bookings';
    //         $m->parent_id       = $parent_id;
    //         $m->data            = json_encode($template_data);
    //         $m->response        = $json;
    //         $m->status          = 'done';
    //         $m->cost            = 220;
    //         $m->assigned_user_id = $current_user->id;
    //         $m->save();

    //         try {
    //             // Save zalo message
    //             $msg_id = $arr['data']['msg_id'] ?? ($arr['idOmniMess'] ?? '');
    //             $timestamp = $arr['data']['sent_time'] ?? time();

    //             $zalomes = new EC_Zalo_Messages();
    //             $zalomes->id            = '';
    //             $zalomes->message_id    = $msg_id;
    //             $zalomes->src           = 0;
    //             $zalomes->from_id       = $Zalo->get_oa_id();
    //             $zalomes->to_id         = $phone;
    //             $zalomes->timestamp     = $timestamp;
    //             $zalomes->type          = 'zns';
    //             $zalomes->sub_type      = $type_zns;
    //             $zalomes->description   = $template_name;
    //             $zalomes->template_id   = $template_id;
    //             $zalomes->data          = json_encode($template_data);
    //             $zalomes->response      = trim($json);
    //             $zalomes->assigned_user_id = $current_user->id;
    //             $zalomes->save();

    //             // Save notes
    //             $n = new Note();
    //             $n->name            = "Gửi Zalo ZNS";
    //             $n->description     = "Gửi Zalo $template_name đến $phone";
    //             $n->parent_type     = "EC_Flight_Bookings";
    //             $n->parent_id       = $parent_id;
    //             $n->assigned_user_id = $current_user->id;
    //             $n->save();
    //         }
    //         catch(Exception $e) {
    //             global $sugar_config;

    //             // $message = Mattermost::$line_separation;
    //             // $message .= Mattermost::markdownHeading("[ERROR] ZNS message saved failed");
    //             // $message .= "\n{$e->getMessage()} on line {$e->getLine()} in {$e->getFile()}\n\n$json";
    //             // Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $message);

    //             $message = "<b>[ERROR] ZNS message saved failed</b>";
    //             $message .= "\n{$e->getMessage()} on line {$e->getLine()} in {$e->getFile()}\n<pre>$json</pre>";
    //             $botToken   = $sugar_config['telegram']['bot_token'] ?? '';
    //             $chatId     = $sugar_config['telegram']['chat_id'] ?? '';
    //             $threadId   = $sugar_config['telegram']['thread_id_logs'] ?? '';
    //             Telegram::sendMessage($message, $botToken, $chatId, $threadId);
    //         }
            
    //         // $fullname = trim($current_user->last_name.' '.$current_user->first_name);
    //         // Mattermost::sendMessage($sugar_config['mattermost']['channel_id_zalo_oa'] ?? '', "**$fullname**: Gửi $template_name đến Zalo **$phone**");

    //         $fullname = trim($current_user->last_name.' '.$current_user->first_name);
    //         $botToken = $sugar_config['telegram']['zalo']['bot_token'] ?? '';
    //         $chatId = $sugar_config['telegram']['zalo']['chat_id'] ?? '';
    //         Telegram::sendMessage("<b>$fullname</b>: Gửi $template_name đến Zalo <b>$phone</b>", $botToken, $chatId);
            
    //         echo json_encode([
    //             "error"   => 0,
    //             "message" => "Gửi tin nhắn thành công",
    //             "data"    => $arr
    //         ]);
    //     }
    //     else {
    //         // $error_code = isset($arr['error']) ? $arr['error'] : '';
    //         // if(empty($error_code)) $error_code = isset($arr['code']) ? $arr['code'] : '';
    //         $error_code = isset($arr['code']) ? $arr['code'] : '';

    //         // if(in_array($type_zns, ['journey-one-way', 'journey-round-trip', 'payment'])) {
    //         //     $message = $Zalo->get_error_description_zns($error_code);
    //         // }
    //         // else {
    //             $message = $Omni->getErrorDescription($error_code);
    //         // }

    //         $m = new EC_Messages();
    //         $m->send_from       = $Zalo->get_oa_id();
    //         $m->send_to         = $phone;
    //         $m->content         = $template_name;
    //         $m->type            = 'zalo_zns';
    //         $m->category        = $category;
    //         $m->send_time       = date("Y-m-d H:i:s", strtotime('-7 hours')); // Lưu xuống db giảm 7 tiếng
    //         $m->parent_type     = 'EC_Flight_Bookings';
    //         $m->parent_id       = $parent_id;
    //         $m->data            = json_encode($template_data);
    //         $m->response        = $json;
    //         $m->status          = 'fail';
    //         $m->description     = $message;
    //         $m->assigned_user_id = $current_user->id;
    //         $m->save();

    //         echo json_encode(["error" => 1, "message" => $message, "data" => $arr]);
    //     }
    //     exit();
    // }
    if($action == 'update_user_alias') { // Version 2
        echo json_encode([
            "error" => 1,
            "message" => "Tính năng đang được nâng cấp. Vui lòng thử lại sau",
        ]);
        exit();

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
        $json = $Zalo->update_user($zalo_id, $alias);
        echo $json;

        $arr = json_decode($json, true);
        if(isset($arr['error']) && $arr['error'] == 0) {
            try {
                $db->query("UPDATE contacts SET zalo_name = '$alias' WHERE zalo_id = '$zalo_id' AND deleted = 0");
            }
            catch(Exception $e) {
                exit();
            }
        }
       
        exit();
    }
    elseif($action == 'update_user_info') { // Version 2
        echo json_encode([
            "error" => 1,
            "message" => "Tính năng đang được nâng cấp. Vui lòng thử lại sau",
        ]);
        exit();

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
        $json = $Zalo->update_user($zalo_id, '', $shared_info);
        echo $json;

        $arr = json_decode($json, true);
        if(isset($arr['error']) && $arr['error'] == 0) {
            try {
                $phone = $Zalo->unformat_zalo_phone($phone);
                $city = $db->getOne("SELECT name FROM cities WHERE id = '$city_id' LIMIT 1") ?? '';
                $district = $db->getOne("SELECT name FROM districts WHERE id = '$district_id' LIMIT 1") ?? '';

                $db->query("UPDATE contacts
                    SET zalo_name = '$name',
                        phone_mobile = '$phone',
                        primary_address_street = '$address',
                        primary_address_city = '$city',
                        primary_address_state = '$district',
                        date_modified = NOW()
                    WHERE zalo_id = '$zalo_id' AND deleted = 0");
            }
            catch(Exception $e) {
                exit();
            }
        }
        exit();
    }
    elseif($action == 'add_tag_user') { // Version 2
        echo json_encode([
            "error" => 1,
            "message" => "Tính năng đang được nâng cấp. Vui lòng thử lại sau",
        ]);
        exit();

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
        $json = $Zalo->add_tag_user($zalo_id, $tag_name);
        echo $json;

        $arr = json_decode($json, true);
        if(isset($arr['error']) && $arr['error'] == 0) {
            try {
                $db->query("UPDATE contacts SET zalo_tags = '$tag_name' WHERE zalo_id = '$zalo_id' AND deleted = 0");
            }
            catch(Exception $e) {
                exit();
            }
        }
        
        exit();
    }
    elseif($action == 'remove_tag_user') { // Version 2
        echo json_encode([
            "error" => 1,
            "message" => "Tính năng đang được nâng cấp. Vui lòng thử lại sau",
        ]);
        exit();

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
        $json = $Zalo->remove_tag_user($zalo_id, $tag_name);
        echo $json;

        $arr = json_decode($json, true);
        if(isset($arr['error']) && $arr['error'] == 0) {
            try {
                $db->query("UPDATE contacts SET zalo_tags = REPLACE(zalo_tags, '$tag_name', '') WHERE zalo_id = '$zalo_id' AND deleted = 0");
            }
            catch(Exception $e) {
                exit();
            }
        }
        exit();
    }
    // elseif($action == 'search_contact') { // Version 2
    //     $search_value = isset($_POST['search_value']) ? trim($_POST['search_value']) : "";

    //     if(empty($search_value)) {
    //         echo json_encode([
    //             "error" => 1,
    //             "message" => "Dữ liệu tìm kiếm không hợp lệ"
    //         ]);
    //         exit();
    //     }

    //     $Zalo = new Zalo();
    //     $beanZalo = new EC_Zalo();
    //     $results = [];

    //     $sql_search = "";
    //     // Search by chat link
    //     if(filter_var($search_value, FILTER_VALIDATE_URL)) {
    //         parse_str(parse_url($search_value, PHP_URL_QUERY), $query);
    //         $zalo_id = $query['uid'] ?? '';
    //         if(!empty($zalo_id)) {
    //             $user_info = $beanZalo->get_zalo_user_info($zalo_id);
    //             if(is_array($user_info) && !empty($user_info)) {
    //                 // Message info
    //                 $lastest_message = $beanZalo->get_lastest_message_user($zalo_id);
    //                 if(empty($lastest_message)) {
    //                     $lastest_message['message_type'] = 'custom';
    //                     $lastest_message['type'] = 'custom';
    //                     $lastest_message['message'] = 'Tương tác cuối vào ' . date('d/m/Y', strtotime($user_info['user_last_interaction_date']));
    //                     $lastest_message['src'] = 1;
    //                     $lastest_message['timestamp'] = strtotime($user_info['user_last_interaction_date']) * 1000;
    //                     $lastest_message['from_id'] = $zalo_id;
    //                     $lastest_message['to_id'] = $Zalo->get_oa_id();
    //                 }

    //                 $results[] = [
    //                     'message_info' => $lastest_message,
    //                     'user_info' => $user_info
    //                 ];
    //             }
    //         }
    //     }
    //     // Search by phone
    //     elseif(is_numeric($search_value)) {
    //         $condition = strlen($search_value) < 10 ? "c.phone_mobile LIKE '%$search_value'" : "c.phone_mobile = $search_value";
    //         $sql_search = "SELECT c.zalo_id
    //             ,c.phone_mobile AS contact_phone
    //             ,c.last_name AS contact_name
    //             ,c.primary_address_street AS contact_address
    //             ,c.primary_address_city AS contact_city
    //             ,c.primary_address_state AS contact_district
    //             ,c.birthdate
    //             ,c.zalo_name
    //             ,c.zalo_avatar
    //             ,c.zalo_last_interaction
    //             ,c.zalo_is_follower
    //             ,c.zalo_tags
    //         FROM contacts c
    //         WHERE $condition
    //             AND c.zalo_id IS NOT NULL
    //             AND c.zalo_id <> ''
    //             AND c.deleted = 0";
    //     }
    //     // Search by name
    //     else {
    //         $sql_search = "SELECT c.zalo_id
    //             ,c.phone_mobile AS contact_phone
    //             ,c.last_name AS contact_name
    //             ,c.primary_address_street AS contact_address
    //             ,c.primary_address_city AS contact_city
    //             ,c.primary_address_state AS contact_district
    //             ,c.birthdate
    //             ,c.zalo_name
    //             ,c.zalo_avatar
    //             ,c.zalo_last_interaction
    //             ,c.zalo_is_follower
    //             ,c.zalo_tags
    //         FROM contacts c
    //         WHERE MATCH(c.zalo_name) AGAINST('\"$search_value\"')
    //             AND c.zalo_id IS NOT NULL
    //             AND c.zalo_id <> ''
    //             AND c.deleted = 0
    //         ORDER BY c.zalo_last_interaction DESC";
    //     }

    //     if(!empty($sql_search)) {
    //         $res = $db->query($sql_search);
    //         while ($row = $db->fetchByAssoc($res)) {
    //             $zalo_name   = $row['zalo_name'] ?? '';
    //             $zalo_avatar = $row['zalo_avatar'] ?? '';
    //             $zalo_tags   = !empty($row['zalo_tags']) ? explode(',', $row['zalo_tags']) : [];
    
    //             // User info
    //             $user_info = [
    //                 'user_id'       => $row['zalo_id'],
    //                 'display_name'  => $row['contact_name'] ?? '',
    //                 'user_alias'    => $zalo_name,
    //                 'avatar'        => $zalo_avatar,
    //                 'user_last_interaction_date' => $row['zalo_last_interaction'] ? date('d/m/Y', strtotime($row['zalo_last_interaction'])) : '',
    //                 'user_is_follower' => $row['zalo_is_follower'] ?? 0,
    //                 'tags_and_notes_info' => [
    //                     'notes' => [],
    //                     'tag_names' => $zalo_tags,
    //                 ],
    //                 'shared_info' => [
    //                     "address"   => $row['contact_address'] ?? '',
    //                     "city"      => $row['contact_city'] ?? '',
    //                     "district"  => $row['contact_district'] ?? '',
    //                     "phone"     => isset($row['contact_phone']) && strlen($row['contact_phone']) > 9 ? $row['contact_phone'] : '',
    //                     "name"      => $row['contact_name'] ?? '',
    //                     "user_dob"  => $row['birthdate'] ? date('d/m/Y', strtotime($row['birthdate'])) : ''
    //                 ],
    //                 'chat_link' => $Zalo->get_chat_link($zalo_id)
    //             ];
    
    //             // Message info
    //             $lastest_message = $beanZalo->get_lastest_message_user($row['zalo_id']);
    //             if(empty($lastest_message)) {
    //                 $lastest_message['message_type'] = 'custom';
    //                 $lastest_message['type'] = 'custom';
    //                 $lastest_message['message'] = 'Tương tác cuối vào ' . date('d/m/Y', strtotime($row['zalo_last_interaction']));
    //                 $lastest_message['src'] = 1;
    //                 $lastest_message['timestamp'] = strtotime($row['zalo_last_interaction']) * 1000;
    //                 $lastest_message['from_id'] = $row['zalo_id'];
    //                 $lastest_message['to_id'] = $Zalo->get_oa_id();
    //             }
    
    //             $results[] = [
    //                 'message_info' => $lastest_message,
    //                 'user_info' => $user_info
    //             ];
    //         }
    //     }

    //     echo json_encode([
    //         "error" => 0,
    //         "message" => "Success",
    //         "data" => $results
    //     ]);
    //     exit();
    // }

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