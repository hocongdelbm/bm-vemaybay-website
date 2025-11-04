<?php
require_once "custom/include/helpers/api/APIZaloOA.php";

class EC_Zalo_Messages extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Zalo_Messages';
    public $object_name = 'EC_Zalo_Messages';
    public $table_name = 'ec_zalo_messages';
    public $importable = false;

    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $SecurityGroups;

    public $message_id;
    public $src;
    public $from_id;
    public $to_id;
    public $timestamp;
    public $type;
    PUBLIC $sub_type;
    public $thumbnail;
    public $url;
    public $attached_description;
    public $latitude;
    public $longitude;
    public $quote_message_id;
    public $template_id;
    public $cost;
    public $data;
    public $response;
    public $booking_id;
    public $image_file = [
        'excel' => 'modules/EC_Zalo/images/private/files/file_excel.jpg',
        'word' => 'modules/EC_Zalo/images/private/files/file_word.jpg',
        'powerpoint' => 'modules/EC_Zalo/images/private/files/file_powerpoint.jpg',
        'pdf' => 'modules/EC_Zalo/images/private/files/file_pdf.jpg',
        'txt' => 'modules/EC_Zalo/images/private/files/file_txt.jpg',
        'html' => 'modules/EC_Zalo/images/private/files/file_html.jpg',
        'xml' => 'modules/EC_Zalo/images/private/files/file_xml.jpg',
        'zip' => 'modules/EC_Zalo/images/private/files/file_zip.jpg',
        'rar' => 'modules/EC_Zalo/images/private/files/file_rar.jpg',
        'image' => 'modules/EC_Zalo/images/private/files/file_image.jpg',
        'default' => 'modules/EC_Zalo/images/private/files/file_default.jpg'
    ];

    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    public function save($check_notify = FALSE) {
		parent::save($check_notify);
	}
    
    /**
     * Mapping sub type to event name
     * 
     * @param string $event_name
     * @return string
     */
    public function map_sub_type($event_name) {
        $arr = [
            'user_send_text'            => 'text',
            'user_send_image'           => 'image',
            'user_send_gif'             => 'gif',
            'user_send_sticker'         => 'sticker',
            'user_send_file'            => 'file',
            'user_send_audio'           => 'audio',
            'user_send_video'           => 'video',
            'user_send_location'        => 'location',
            'user_send_link'            => 'link',
            'user_send_business_card'   => 'business_card',
            'user_feedback'             => 'feedback',
            'user_submit_info'          => 'submit_info',

            'oa_send_text'      => 'text',
            'oa_send_image'     => 'image',
            'oa_send_gif'       => 'gif',
            'oa_send_sticker'   => 'sticker',
            'oa_send_file'      => 'file',
            'oa_send_list'      => 'links', // Request user info here
            'oa_send_template'  => 'template',
        ];

        return isset($arr[$event_name]) ? $arr[$event_name] : 'other';
    }

    /**
     * Get recent messages from each user
     * 
     * @param string $oa_id
     * @param int $timestamp
     * @param array $current_list_user
     * @param int $limit_record
     * @return array
     */
    public function get_list_recent_messages($oa_id, $last_timestamp = 0, $current_list_user = [], $limit_record = 15) {
        $results = ['data' => []];
        $list_zalo_id = ['interaction' => [], 'no_interaction' => []];
        $zaloContact = new EC_Zalo_Contacts();

        $where_clause = '';
        if($last_timestamp > 0) $where_clause = "AND zm.timestamp < $last_timestamp AND zm.deleted = 0";
        else $where_clause = "AND zm.deleted = 0";

        if(!empty($current_list_user)) {
            $l = implode(',', $current_list_user);
            $where_clause = " AND zm.from_id NOT IN($l) AND zm.to_id NOT IN($l)";
        }

        $sql = "SELECT DISTINCT(CONCAT(zm.from_id, zm.to_id)) AS chat_id
            ,zm.message_id
            ,zm.src
            ,zm.type AS message_type
            ,zm.sub_type AS type
            ,zm.description AS message
            ,zm.timestamp
            ,zm.from_id AS from_id
            ,zm.to_id AS to_id
            ,zm.quote_message_id AS quote_id
            ,zm.template_id
            ,zm.assigned_user_id
            ,TRIM(CONCAT(IFNULL(u.last_name, ''), ' ', IFNULL(u.first_name, ''))) AS assigned_user_name 
        FROM ec_zalo_messages zm
            LEFT JOIN users u ON u.id = zm.assigned_user_id
        WHERE (from_id = '{$oa_id}' OR to_id = '{$oa_id}')
            AND zm.type != 'zns'
            {$where_clause}
        ORDER BY zm.timestamp DESC
        LIMIT 200";

        $res = $this->db->query($sql);
        while($row = $this->db->fetchByAssoc($res)) {
            $zalo_id = $row['src'] == 1 ? $row['from_id'] : $row['to_id'];

            // if(in_array($zalo_id, $current_list_user)) continue;
            if((!empty($list_zalo_id['no_interaction']) && in_array($zalo_id, $list_zalo_id['no_interaction']))
                || (!empty($list_zalo_id['interaction']) && in_array($zalo_id, $list_zalo_id['interaction']))) {
                continue;
            }

            // Get zalo user info
            $user_info = $zaloContact->get_zalo_user_info($zalo_id, $oa_id);
            if(empty($user_info)) {
                $list_zalo_id['no_interaction'][] = $zalo_id;
                continue;
            }
            else $list_zalo_id['interaction'][] = $zalo_id;

            if($results['data'] && count($results['data']) >= $limit_record) break;

            $row['src'] = (int)$row['src'];
            if($row['message_type'] == 'call') $row['type'] = $GLOBALS['app_list_strings']['calls_direction_list'][$row['type']];
            $results['data'][] = [
                'message_info' => $row,
                'user_info' => $user_info
            ];
            if($last_timestamp == 0 || $row['timestamp'] < $last_timestamp) $last_timestamp = $row['timestamp'];
        }
        $results['last_timestamp'] = $last_timestamp;
        return $results;
    }

    /**
     * Get messages
     * 
     * @param string $oa_id
     * @param string $zalo_id
     * @param string $zalo_phone
     * @param int $offset
     * @param int $is_get_user_info
     * @param int $limit_message
     */
    public function get_messages($oa_id, $zalo_id, $zalo_phone = '', $offset = 0, $is_get_user_info = 0, $limit_message = 10) {
        $zaloContact = new EC_Zalo_Contacts();

        $result = [];

        // User info
        if($is_get_user_info == 1) {
            $user_data = $zaloContact->get_zalo_user_info($zalo_id, $oa_id);
            $result['user_info']['data'] = $user_data;
            $result['user_info']['status'] = !empty($user_data) ? 1 : 0;
        }

        // Message info
        $message_data = [];
        $is_using_api_for_message = false;
        $sql = "SELECT zm.message_id
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
                ,TRIM(CONCAT(IFNULL(u.last_name, ''), ' ', IFNULL(u.first_name, ''))) AS assigned_user_name 
                ,u.photo
            FROM ec_zalo_messages zm
                LEFT JOIN users u ON u.id = zm.assigned_user_id
            WHERE (zm.from_id = '$zalo_id' OR zm.to_id = '$zalo_id' OR zm.to_id = '$zalo_phone')
                AND zm.deleted = 0
            ORDER BY zm.timestamp DESC
            LIMIT {$offset}, {$limit_message}";

        $res = $this->db->query($sql);
        while($row = $this->db->fetchByAssoc($res)) {
            $row['src'] = (int)$row['src'];
            $row['message_data'] = !empty($row['message_data']) ? json_decode(html_entity_decode($row['message_data']), true) : [];
            if($row['type'] == 'business_card') $row['description'] = html_entity_decode($row['description']);

            // Handle quote message data
            if($row['quote_id'] && !empty($row['quote_id'])) {
                $row['quote_data'] = $this->get_quote_message_data($row['quote_id']);
            }

            // Handle avatar assigned user (admin)
            $row['assigned_user_avatar'] = '';
            if($row['src'] == 0 && $row['photo'] && !empty($row['photo']) && !empty($row['assigned_user_id'])) {
                $row['assigned_user_avatar'] = "index.php?entryPoint=download&id=".$row['assigned_user_id']."_photo&type=Users";
            }
            unset($row['photo']);

            // Format by message type
            if($row['message_type'] == 'zns') {
                unset($row['thumbnail']);
                unset($row['url']);
                unset($row['description']);
                unset($row['latitude']);
                unset($row['longitude']);
                unset($row['quote_id']);
            }
            else if($row['message_type'] == 'call') {
                $row['type'] = $GLOBALS['app_list_strings']['calls_direction_list'][$row['type']] ?? $row['type'];

                unset($row['thumbnail']);
                unset($row['url']);
                unset($row['description']);
                unset($row['latitude']);
                unset($row['longitude']);
                unset($row['quote_id']);
                unset($row['template_id']);
            }

            $message_data[] = $row;
        }

        if(empty($message_data)) {
            $zaloOA = new APIZaloOA($oa_id);
            $json_messages = $zaloOA->get_messages($zalo_id, $offset + 1); // +1 for offset in get more message
            $arr_messages = json_decode($json_messages, true);

            if(isset($arr_messages['error']) && $arr_messages['error'] == 0) {
                $is_using_api_for_message = true;
                $result['messages_info']['status'] = 1;

                // Format data again
                foreach($arr_messages['data'] as $m) {
                    if($m['type'] == 'links') $m['message_data'] = $m['links'];

                    $result['messages_info']['data'][] = $m;
                }
                $result['messages_info']['offset'] = count($result['messages_info']['data']) + $offset;
            }
            else {
                $is_using_api_for_message = false;
                $result['messages_info']['status'] = 0;
                $result['messages_info']['data'] = [];
            }
        }
        else {
            $count_message_data = count($message_data);
            $result['messages_info']['status']  = 1;
            $result['messages_info']['data']    = $message_data;
            $result['messages_info']['offset']  = $count_message_data == $limit_message ? count($message_data) + $offset : -1;
        }

        // Save previous message which not exist in database
        try {
            if($is_using_api_for_message) {
                foreach($arr_messages['data'] as $m) {
                    $message_id = $m['message_id'] ?? '';
                    if(empty($message_id)) continue;
    
                    $record_id = $this->db->getOne("SELECT id FROM ec_zalo_messages WHERE message_id = '$message_id'");
                    if(!$record_id || empty($record_id)) {
                        $mdata = [];

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

                            if($subtype == 'links') {
                                foreach($m['links'] as $link) {
                                    $mdata[] = [
                                        'title' => $link['title'],
                                        'url' => $link['url'],
                                        'thumbnail' => $link['thumb'],
                                        'description' => $link['description']
                                    ];
                                }
                            }
                        }
                        else {
                            $mtype = 'other';
                            $subtype = 'nosupport';
                        }
    
                        // Location info
                        $lat = $long = '';
                        if(isset($m['location'])) {
                            $location = is_string($m['location']) ? json_decode($m['location'], true) : $m['location'];
                            $lat = $location['latitude'] ?? ''; 
                            $long = $location['longitude '] ?? ''; 
                        }
    
                        $zalomes = new EC_Zalo_Messages();
                        $zalomes->id = '';
                        $zalomes->message_id = $message_id;
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
                        $zalomes->data = json_encode($mdata);
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
        
        return $result;
    }

    /**
     * Get lastest message user
     * 
     * @param string $oa_id
     * @param string $zalo_id
     * @param bool $only_user_interaction
     * @return array
     */
    public function get_lastest_message_user($oa_id, $zalo_id, $only_user_interaction = false) {
        if(!$oa_id || !$zalo_id || empty($oa_id) || strlen($zalo_id) < 15) return [];

        $where_clause = '';
        if($only_user_interaction) $where_clause = "(zm.from_id = '$zalo_id' OR (zm.to_id = '$zalo_id' AND zm.type = 'call' AND zm.sub_type = 'outbound'))";
        else $where_clause = "(zm.from_id = '$zalo_id' OR zm.to_id = '$zalo_id')";

        $sql = "SELECT zm.message_id
                ,zm.src
                ,zm.type AS message_type
                ,zm.sub_type AS type
                ,zm.description AS message
                ,zm.timestamp
                ,zm.from_id AS from_id
                ,zm.to_id AS to_id
                ,zm.quote_message_id AS quote_id
                ,zm.template_id
                ,zm.assigned_user_id
                ,TRIM(CONCAT(IFNULL(u.last_name, ''), ' ', IFNULL(u.first_name, ''))) AS assigned_user_name 
            FROM ec_zalo_messages zm
                LEFT JOIN users u ON u.id = zm.assigned_user_id
            WHERE $where_clause
                AND zm.deleted = 0
            ORDER BY zm.timestamp DESC
            LIMIT 1";
        $res = $this->db->query($sql);
        return $this->db->fetchByAssoc($res) ?? [];
    }

    /**
     * Get quote message data
     * 
     * @param string $quote_id
     * @return array
     */
    public function get_quote_message_data($quote_id) {
        if(!$quote_id || empty($quote_id)) return [];

        $result = [];
        $sql = "SELECT
                zm.src,
                zm.type AS message_type,
                zm.sub_type AS type,
                zm.description AS message,
                zm.thumbnail,
                zm.url,
                zm.data AS message_data
            FROM ec_zalo_messages zm
            WHERE zm.message_id = '$quote_id' AND zm.deleted = 0
            LIMIT 1";
        $res = $this->db->query($sql);
        $row = $this->db->fetchByAssoc($res);

        $message_data = !empty($row['message_data']) ? json_decode(html_entity_decode($row['message_data']), true) : [];
        
        // Default data
        $result['src'] = $row['src'] ?? '';
        $result['message'] = $row['message'] ?? '';
        $result['type'] = $row['type'] ?? '';
        $result['parent_type'] = $row['message_type'] ?? '';
        $result['thumbnail'] = $row['thumbnail'] ?? '';

        if($result['parent_type'] == 'call') {
            $result['message'] = $result['type'] == 'outbound' ? 'Cuộc gọi đi' : 'Cuộc gọi đến';
        }
        else {
            if($row['type'] == 'sticker') {
                $result['thumbnail'] = $row['url'] ?? $result['thumbnail'];
            }
            elseif($row['type'] == 'links') {
                $result['thumbnail'] = $message_data[0]['thumbnail'] ?? $result['thumbnail'];
            }
            elseif($row['type'] == 'file') {
                $file_type = $message_data['type'] ?? '';
                $result['filetype']  = $file_type;
                $result['thumbnail'] = $this->image_file[$file_type] ?? '';
                $result['filename']  = $message_data['name'] ?? '';
            }
        }

        return $result;
    }

    /**
     * Send happy birthday message
     * 
     * @param string $zalo_id
     * @param string $oa_id
     * @return bool
     */
    public function send_happy_birthday_message($zalo_id = '', $oa_id = '') {
        $zaloOA = new APIZaloOA($oa_id);
        $banner = $zaloOA->get_images_path() . "/banners/happy_birthday.png";
        $header = "Chúc mừng sinh nhật quý khách hàng 🎉";
        $text = "Chúc Bạn luôn vui vẻ và hạnh phúc. Nhân dịp đặc biệt này, Tìm Chuyến Bay xin gửi tặng Bạn voucher 50k áp dụng cho vé khứ hồi như một món quà nhỏ.❤️❤️";
        $table = [
            [
                "key" => "Voucher",
                "value" => "Giảm 50.000đ"
            ],
            [
                "key" => "Hạn sử dụng dùng",
                "value" => "Đến hết dd/mm/yyyy"
            ]
        ];
        $buttons = [

        ];

        if(!empty($zalo_id)) {

        }
        else {

            $zaloContact = new EC_Zalo_Contacts();
            $listUser = $zaloContact->get_users_with_birthday();
            foreach($listUser as $u) {
                $zaloOA->send_promotion($u['zalo_id'], $banner, $header, $text, $table, $buttons);
            }
        }
    }

    /**
     * 
     */
    public function send_maintain_interaction_message() {
        $zaloContact = new EC_Zalo_Contacts();

        // $listUsers6day = $zaloContact->get_list_zalo_user_by_last_interaction_day(6);
        // $listUsers30day = $zaloContact->get_list_zalo_user_by_last_interaction_day(30);
        
    }
}