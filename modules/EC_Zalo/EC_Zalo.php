<?php
require_once("modules/EC_Zalo/Zalo.php");

class EC_Zalo extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Zalo';
    public $object_name = 'EC_Zalo';
    public $table_name = 'ec_zalo';
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
    public $limit_chat_box = 15;
    public $limit_message = 10;
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
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    /**
     * Get zalo user info
     * 
     * @param string $zalo_id
     * @return array
     */
    public function get_zalo_user_info($zalo_id) {
        if(!$zalo_id || strlen($zalo_id) < 15) return [];

        global $current_user;
        $Zalo = new Zalo();
        $user_data = [];

        $sql = "SELECT c.id AS contact_id
            ,c.phone_mobile AS contact_phone
            ,c.last_name AS contact_name
            ,c.primary_address_street AS contact_address
            ,c.primary_address_city AS contact_city
            ,c.primary_address_state AS contact_district
            ,c.birthdate
            ,c.zalo_name
            ,c.zalo_avatar
            ,c.zalo_last_interaction
            ,c.zalo_is_follower
            ,c.zalo_tags
            ,DATE_ADD(c.date_modified, INTERVAL 7 HOUR) AS date_modified
        FROM contacts c
        WHERE zalo_id = '$zalo_id' AND deleted = 0
        ORDER BY date_entered
        LIMIT 1";

        $res = $this->db->query($sql);
        $row_info = $this->db->fetchByAssoc($res);
        $refresh_time = 86400*7; // 7 days
        if(!empty($row_info) && time() - strtotime($row_info['date_modified']) < $refresh_time) {
            $zalo_name   = $row_info['zalo_name'] ?? '';
            $zalo_avatar = $row_info['zalo_avatar'] ?? '';
            $zalo_tags   = !empty($row_info['zalo_tags']) ? explode(',', $row_info['zalo_tags']) : [];

            if(!empty($zalo_name) && !empty($zalo_avatar)) {
                $user_data = [
                    'user_id'       => $zalo_id,
                    'display_name'  => $row_info['contact_name'] ?? '',
                    'user_alias'    => $zalo_name,
                    'avatar'        => $zalo_avatar,
                    'user_last_interaction_date' => $row_info['zalo_last_interaction'] ? date('d/m/Y', strtotime($row_info['zalo_last_interaction'])) : '',
                    'user_is_follower' => $row_info['zalo_is_follower'] ?? 0,
                    'tags_and_notes_info' => [
                        'notes' => [],
                        'tag_names' => $zalo_tags,
                    ],
                    'shared_info' => [
                        "address"   => $row_info['contact_address'] ?? '',
                        "city"      => $row_info['contact_city'] ?? '',
                        "district"  => $row_info['contact_district'] ?? '',
                        "phone"     => isset($row_info['contact_phone']) && strlen($row_info['contact_phone']) > 9 ? $row_info['contact_phone'] : '',
                        "name"      => $row_info['contact_name'] ?? '',
                        "user_dob"  => $row_info['birthdate'] ? date('d/m/Y', strtotime($row_info['birthdate'])) : ''
                    ],
                    'chat_link' => $Zalo->get_chat_link($zalo_id)
                ];
            }
        }
        
        if(empty($user_data)) {
            $json_user = $Zalo->get_user($zalo_id);
            $result['user_info'] = json_decode($json_user, true);

            if(isset($result['user_info']['error']) && $result['user_info']['error'] == 0) {
                $user_data = $result['user_info']['data'];
                $user_data_alias    = $user_data['user_alias'] ?? '';
                $user_data_name     = $user_data['user_alias'] ?? ($user_data['display_name'] ?? '');
                // Phone
                $user_data_phone    = $Zalo->get_phone_by_alias($user_data_alias);
                if(empty($user_data_phone)) $user_data_phone = $Zalo->unformat_zalo_phone($user_data['shared_info']['phone'] ?? '');
                // Last interaction
                $user_data_last_interaction = $user_data['user_last_interaction_date'] ?? '';
                if(!empty($user_data_last_interaction)) $user_data_last_interaction = date('Y-m-d', strtotime(str_replace("/", "-", $user_data_last_interaction))) . ' 00:00:00';

                $contact_id = '';
                if(!empty($user_data_phone)) {
                    $sql_contact = "SELECT id FROM contacts WHERE phone_mobile = '$user_data_phone' AND deleted = 0 ORDER BY date_entered";
                    $contact_id = $this->db->getOne($sql_contact) ?? '';
                }

                $Contact = new Contact();
                if((!$row_info || empty($row_info)) && (!isset($row_info['contact_id']) || !$row_info['contact_id'] || empty($row_info['contact_id'])) && empty($contact_id)) {
                    $Contact->last_name     = $user_data_name;
                    $Contact->zalo_id       = $user_data['user_id'];
                    $Contact->zalo_name     = $user_data_name;
                    $Contact->zalo_avatar   = $user_data['avatar'] ?? '';
                    $Contact->phone_mobile  = $user_data_phone;
                    $Contact->zalo_is_follower = (int)$user_data['user_is_follower'] ?? 0;
                    $Contact->zalo_last_interaction = $user_data_last_interaction;
                    $Contact->assigned_user_id = $current_user->id;
                    if(isset($user_data['shared_info']) && !empty($user_data['shared_info'])) {
                        $Contact->primary_address_street    = $user_data['shared_info']['address'] ?? '';
                        $Contact->primary_address_city      = $user_data['shared_info']['city'] ?? '';
                        $Contact->primary_address_state     = $user_data['shared_info']['district'] ?? '';
                        $Contact->birthdate                 = $user_data['shared_info']['user_dob'] ?? '';
                        if(!empty($Contact->birthdate)) $Contact->birthdate = date('d-m-Y', strtotime($Contact->birthdate));
                    }
                    if(isset($user_data['tags_and_notes_info']) && !empty($user_data['tags_and_notes_info'])) {
                        if(isset($user_data['tags_and_notes_info']['tag_names']) && !empty($user_data['tags_and_notes_info']['tag_names'])) {
                            $tag_names = $user_data['tags_and_notes_info']['tag_names'];
                            $Contact->zalo_tags = is_array($tag_names) ? implode(',', $tag_names) : $tag_names;
                        }
                    }
                    $Contact->description = "Liên hệ tạo từ Zalo OA " . json_encode($row_info, JSON_UNESCAPED_UNICODE);
                }
                else {
                    $Contact->retrieve(isset($row_info['contact_id']) && !empty($row_info['contact_id']) ? $row_info['contact_id'] : $contact_id);
                    $Contact->zalo_name             = $user_data_name;
                    $Contact->zalo_avatar           = $user_data['avatar'] ?? '';
                    $Contact->zalo_is_follower      = (int)$user_data['user_is_follower'] ?? 0;
                    $Contact->zalo_last_interaction = $user_data_last_interaction;
                    if(isset($user_data['shared_info']) && !empty($user_data['shared_info'])) {
                        if(!$Contact->primary_address_street || empty($Contact->primary_address_street)) {
                            $Contact->primary_address_street = $user_data['shared_info']['address'] ?? '';
                        }  
                        if(!$Contact->primary_address_city || empty($Contact->primary_address_city)) {
                            $Contact->primary_address_city = $user_data['shared_info']['city'] ?? '';
                        }
                        if(!$Contact->primary_address_state || empty($Contact->primary_address_state)) {
                            $Contact->primary_address_state = $user_data['shared_info']['district'] ?? '';
                        }
                        if(!$Contact->phone_mobile || empty($Contact->phone_mobile)) {
                            $Contact->phone_mobile = $user_data_phone;
                        }
                        if(!$Contact->birthdate || empty($Contact->birthdate)) {
                            $user_dob = $user_data['shared_info']['user_dob'] ?? '';
                            if(!empty($user_dob)) $Contact->birthdate = date('d-m-Y', strtotime($user_dob));
                        }
                    }
                    if(isset($user_data['tags_and_notes_info']) && !empty($user_data['tags_and_notes_info'])) {
                        if(isset($user_data['tags_and_notes_info']['tag_names']) && !empty($user_data['tags_and_notes_info']['tag_names'])) {
                            $tag_names = $user_data['tags_and_notes_info']['tag_names'];
                            $Contact->zalo_tags = is_array($tag_names) ? implode(',', $tag_names) : $tag_names;
                        }
                    }
                    $Contact->description = "Cập nhật thông tin qua EC_Zalo get_zalo_user_info($zalo_id) ($Contact->phone_mobile)";
                }
                $Contact->save();

                $user_data['chat_link'] = $Zalo->get_chat_link($zalo_id);
            }
        }
       
        return $user_data;
    }

    /**
     * Get list user chat
     * 
     * @param int $timestamp
     * @param array $current_list_user
     * @return array
     */
    public function get_list_user($last_timestamp = 0, $current_list_user = []) {
        $results = [];
        $list_zalo_id = ['interaction' => [], 'no_interaction' => []];

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
        WHERE zm.type != 'zns'
            $where_clause
        ORDER BY zm.timestamp DESC
        LIMIT 200";

        $res = $this->db->query($sql);
        while($row = $this->db->fetchByAssoc($res)) {
            $zalo_id = $row['src'] == 1 ? $row['from_id'] : $row['to_id'];

            // if(in_array($zalo_id, $current_list_user)) continue;
            if(in_array($zalo_id, $list_zalo_id['no_interaction']) || in_array($zalo_id, $list_zalo_id['interaction'])) continue;

            // Lấy thông tin người dùng
            $user_info = $this->get_zalo_user_info($zalo_id);
            if(empty($user_info)) {
                $list_zalo_id['no_interaction'][] = $zalo_id;
                continue;
            }
            else $list_zalo_id['interaction'][] = $zalo_id;

            if(count($results['data']) >= $this->limit_chat_box) break;

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
     * Get lastest message user
     * 
     * @param string $zalo_id
     * @param bool $only_user_interaction
     * @return array
     */
    public function get_lastest_message_user($zalo_id, $only_user_interaction = false) {
        if(!$zalo_id || strlen($zalo_id) < 15) return [];

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

        if($row['parent_type'] == 'call') {
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

    public function update_zalo_info_to_contact($contact_id, $user_data) {
        if(!$contact_id || empty($contact_id)) return false;

        $zalo_id = $user_data['user_id'] ?? '';
        $user_data_name = $user_data['user_alias'] ?? ($user_data['display_name'] ?? '');
        $user_data_last_interaction = $user_data['user_last_interaction_date'] ?? '';
        if(!empty($user_data_last_interaction)) $user_data_last_interaction = date('Y-m-d', strtotime(str_replace("/", "-", $user_data_last_interaction))) . ' 00:00:00';

        $Zalo = new Zalo();
        $Contact = new Contact();
        $Contact->retrieve($contact_id);
        if(!isset($Contact->id) || empty($Contact->id)) return false;
        $Contact->zalo_id               = $zalo_id;
        $Contact->zalo_name             = $user_data_name;
        $Contact->zalo_avatar           = $user_data['avatar'] ?? '';
        $Contact->zalo_is_follower      = (int)$user_data['user_is_follower'] ?? 0;
        $Contact->zalo_last_interaction = $user_data_last_interaction;
        if(!$Contact->phone_mobile || empty($Contact->phone_mobile)) {
            $alias_phone = $Zalo->get_phone_by_alias($user_data['user_alias'] ?? '');
            if(!empty($alias_phone) && strlen($alias_phone) > 9) $Contact->phone_mobile = $alias_phone;
        }
        if(isset($user_data['shared_info']) && !empty($user_data['shared_info'])) {
            if(!$Contact->primary_address_street || empty($Contact->primary_address_street)) {
                $Contact->primary_address_street = $user_data['shared_info']['address'] ?? '';
            }  
            if(!$Contact->primary_address_city || empty($Contact->primary_address_city)) {
                $Contact->primary_address_city = $user_data['shared_info']['city'] ?? '';
            }
            if(!$Contact->primary_address_state || empty($Contact->primary_address_state)) {
                $Contact->primary_address_state = $user_data['shared_info']['district'] ?? '';
            }
            if(!$Contact->phone_mobile || empty($Contact->phone_mobile)) {
                $shared_phone = $user_data['shared_info']['phone'] ?? '';
                if(!empty($shared_phone)) $Contact->phone_mobile = $Zalo->unformat_zalo_phone($shared_phone);
            }
            if(!$Contact->birthdate || empty($Contact->birthdate)) {
                $user_dob = $user_data['shared_info']['user_dob'] ?? '';
                if(!empty($user_dob)) $Contact->birthdate = date('d-m-Y', strtotime($user_dob));
            }
        }
        if(isset($user_data['tags_and_notes_info']) && !empty($user_data['tags_and_notes_info'])) {
            if(isset($user_data['tags_and_notes_info']['tag_names']) && !empty($user_data['tags_and_notes_info']['tag_names'])) {
                $tag_names = $user_data['tags_and_notes_info']['tag_names'];
                $Contact->zalo_tags = is_array($tag_names) ? implode(',', $tag_names) : $tag_names;
            }
        }
        $Contact->description = "Cập nhật thông tin qua EC_Zalo update_zalo_info_to_contact ($Contact->phone_mobile) ($zalo_id)";
        $Contact->save();
    }

    public function add_new_contact_by_zalo_info($user_data, $debug_data = []) {
        global $current_user;
        $Zalo = new Zalo();

        $user_data_name = $user_data['user_alias'] ?? ($user_data['display_name'] ?? '');
        $user_data_last_interaction = $user_data['user_last_interaction_date'] ?? '';
        if(!empty($user_data_last_interaction)) $user_data_last_interaction = date('Y-m-d', strtotime(str_replace("/", "-", $user_data_last_interaction))) . ' 00:00:00';
        $alias_phone = $Zalo->get_phone_by_alias($user_data['user_alias'] ?? '');

        $Contact = new Contact();
        $Contact->last_name     = $user_data_name;
        $Contact->zalo_id       = $user_data['user_id'] ?? '';
        $Contact->zalo_name     = $user_data_name;
        $Contact->phone_mobile  = (!empty($alias_phone) && strlen($alias_phone) > 9) ? $alias_phone : '';
        $Contact->zalo_avatar   = $user_data['avatar'] ?? '';
        $Contact->zalo_is_follower = (int)$user_data['user_is_follower'] ?? 0;
        $Contact->zalo_last_interaction = $user_data_last_interaction;
        $Contact->assigned_user_id = $current_user->id ?? '';
        if(isset($user_data['shared_info']) && !empty($user_data['shared_info'])) {
            $Contact->primary_address_street = $user_data['shared_info']['address'] ?? '';
            $Contact->primary_address_city = $user_data['shared_info']['city'] ?? '';
            $Contact->primary_address_state = $user_data['shared_info']['district'] ?? '';
            if(!$Contact->phone_mobile || empty($Contact->phone_mobile)) {
                $Contact->phone_mobile = $Zalo->unformat_zalo_phone($user_data['shared_info']['phone'] ?? '');
            }
            $Contact->birthdate = $user_data['shared_info']['user_dob'] ?? '';
            if(!empty($Contact->birthdate)) $Contact->birthdate = date('d-m-Y', strtotime($Contact->birthdate));
        }
        if(isset($user_data['tags_and_notes_info']) && !empty($user_data['tags_and_notes_info'])) {
            if(isset($user_data['tags_and_notes_info']['tag_names']) && !empty($user_data['tags_and_notes_info']['tag_names'])) {
                $tag_names = $user_data['tags_and_notes_info']['tag_names'];
                $Contact->zalo_tags = is_array($tag_names) ? implode(',', $tag_names) : $tag_names;
            }
        }
        
        if(is_array($debug_data)) $Contact->description = "Liên hệ tạo từ Zalo OA " . json_encode($debug_data, JSON_UNESCAPED_UNICODE);
        else $Contact->description = "Liên hệ tạo từ Zalo OA";
        $Contact->save();
    }
}
