<?php
require_once "custom/include/helpers/api/APIZaloOA.php";

class EC_Zalo_Contacts extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Zalo_Contacts';
    public $object_name = 'EC_Zalo_Contacts';
    public $table_name = 'ec_zalo_contacts';
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

    public $zalo_id;
    public $oa_id;
    public $contact_id;
    public $contact_name;
    public $alias;
    public $avatar;
    public $birth_date;
    public $last_interaction;
    public $is_follower;
    public $tags;
    public $province_city;
    public $ward_commune;
    public $address;
    public $quota_info;
	
    public function bean_implements($interface) {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    /**
     * Add or update zalo user
     * 
     * @param array $user_data Data from API
     * @param string $oa_id
     * @param string $description
     * 
     * @return string|bool
     */
    public function custom_save($user_data, $oa_id = '', $description = '') {
        global $current_user;

        $zalo_id = $user_data['user_id'] ?? '';
        if(empty($zalo_id)) return false;

        $zaloOA = new APIZaloOA($oa_id);
        if(!is_string($oa_id) || empty($oa_id)) $oa_id = $zaloOA->get_oa_id();

        $user_external_id = $user_data['user_external_id'] ?? '';
        $record_id = $user_external_id;
        if(empty($record_id)) {
            $sqlCheck = "SELECT id FROM ec_zalo_contacts WHERE zalo_id = '{$zalo_id}' AND oa_id = '{$oa_id}' AND deleted = 0";
            $record_id = $this->db->getOne($sqlCheck) ?? '';
        }

        // Alias
        $user_alias = $user_data['user_alias'] ?? '';

        // Last interaction
        $last_interaction = $this->format_datetime($user_data['user_last_interaction_date'] ?? '');

        // Phone
        $phone = $zaloOA->get_phone_by_alias($user_alias);
        if(empty($phone)) $phone = $zaloOA->unformat_zalo_phone($user_data['shared_info']['phone'] ?? '');

        // Contact
        $contact_id = '';
        if(!empty($phone)) {
            $contact_id = $this->db->getOne("SELECT id FROM contacts WHERE phone_mobile = '{$phone}' AND deleted = 0 ORDER BY date_entered LIMIT 1") ?? '';
        }

        // Date of birth
        $shared_user_dob = $this->format_date($user_data['shared_info']['user_dob'] ?? '');

        // Tags
        $tag_names = $user_data['tags_and_notes_info']['tag_names'] ?? [];
        $tag_names = is_array($tag_names) ? implode(',', $tag_names) : $tag_names;

        // Quota
        $quota_info = null;
        if(isset($user_data['quota']) && !empty($user_data['quota'])) {
            $quota_info = is_string($user_data['quota']) ? $user_data['quota']: json_encode($user_data['quota']);
        }
       
        $zaloContact = new EC_Zalo_Contacts();
        if(empty($record_id)) {
            $zaloContact->zalo_id = $zalo_id;
            $zaloContact->oa_id = $oa_id;
            $zaloContact->description = !empty($description) ? $description : "Liên hệ tạo khi gọi API Zalo OA";
            $zaloContact->assigned_user_id = $current_user->id != '1' ? $current_user->id : null;
        }
        else {
            $zaloContact->retrieve($record_id);
            $zaloContact->description = !empty($description) ? $description : "Thông tin cập nhật qua API Zalo OA";
        }
        $zaloContact->contact_id        = $contact_id;
        $zaloContact->name              = $user_data['display_name'] ?? '';
        $zaloContact->alias             = $user_alias;
        $zaloContact->avatar            = $user_data['avatar'] ?? '';
        $zaloContact->last_interaction  = $last_interaction;
        $zaloContact->is_follower       = (int)$user_data['user_is_follower'];
        $zaloContact->birth_date        = $shared_user_dob;
        $zaloContact->tags              = $tag_names;
        $zaloContact->province_city     = $user_data['shared_info']['city'] ?? '';
        $zaloContact->ward_commune      = $user_data['shared_info']['district'] ?? '';
        $zaloContact->address           = $user_data['shared_info']['address'] ?? '';
        $zaloContact->quota_info        = $quota_info;
        return $zaloContact->save();
    }
    
    public function map_contact_zalo($contact_id, $zalo_id, $oa_id = '') {
        $zaloOA = new APIZaloOA($oa_id);
        if(!is_string($oa_id) || empty($oa_id)) $oa_id = $zaloOA->get_oa_id();

        $sql = "UPDATE ec_zalo_contacts
            SET contact_id = '{$contact_id}'
            WHERE zalo_id = '{$zalo_id}'
                AND oa_id = '{$oa_id}'
                AND deleted = 0";
        return $this->db->query($sql);
    }

    /**
     * Get zalo user info
     * 
     * @param string $zalo_id
     * @param string $oa_id
     * 
     * @return array User data
     */
    public function get_zalo_user_info($zalo_id, $oa_id = '') {
        if(!$zalo_id || strlen($zalo_id) < 15) return [];

        $zaloOA = new APIZaloOA($oa_id);
        if(!is_string($oa_id) || empty($oa_id)) $oa_id = $zaloOA->get_oa_id();
        $userData = [];

        $sql = "SELECT zc.id AS user_external_id
            ,zc.contact_id
            ,c.phone_mobile AS phone_number
            ,zc.name AS display_name
            ,zc.alias AS user_alias
            ,zc.avatar
            ,zc.birth_date
            ,zc.last_interaction
            ,zc.is_follower
            ,zc.tags
            ,zc.province_city
            ,zc.ward_commune
            ,zc.address
            ,zc.quota_info
            ,DATE_ADD(zc.date_modified, INTERVAL 7 HOUR) AS date_modified
        FROM ec_zalo_contacts zc
            LEFT JOIN contacts c ON c.id = zc.contact_id AND c.deleted = 0
        WHERE zc.zalo_id = '{$zalo_id}'
            AND zc.oa_id = '{$oa_id}'
            AND zc.deleted = 0
        ORDER BY zc.date_entered
        LIMIT 1";

        $res = $this->db->query($sql);
        $dbInfo = $this->db->fetchByAssoc($res);
        $refresh_time = 86400*7; // 7 days

        // Receive data from database
        if(is_array($dbInfo) && !empty($dbInfo) && time() - strtotime($dbInfo['date_modified']) < $refresh_time) {
            $zalo_display_name  = $dbInfo['display_name'] ?? '';
            $zalo_user_alias    = $dbInfo['user_alias'] ?? '';
            $zalo_avatar        = $dbInfo['avatar'] ?? '';
            $quota_info         = json_decode(html_entity_decode($dbInfo['quota_info'] ?? ''), true);

            if((!empty($zalo_display_name) || !empty($zalo_user_alias)) && !empty($zalo_avatar)) {
                $userData = [
                    'user_id'           => $zalo_id,
                    'user_id_by_app'    => '',
                    'user_external_id'  => $dbInfo['user_external_id'] ?? '',
                    'display_name'      => $zalo_display_name,
                    'user_alias'        => $zalo_user_alias,
                    'avatar'            => $zalo_avatar,
                    'user_last_interaction_date' => $this->format_datetime($dbInfo['last_interaction'] ?? ''),
                    'user_is_follower' => $dbInfo['is_follower'] ?? 0,
                    'tags_and_notes_info' => [
                        'notes' => [],
                        'tag_names' => !is_null($dbInfo['tags']) && !empty($dbInfo['tags']) ? explode(',', $dbInfo['tags']) : [],
                    ],
                    'shared_info' => [
                        "address"   => $dbInfo['address'] ?? '',
                        "city"      => $dbInfo['province_city'] ?? '',
                        "district"  => $dbInfo['ward_commune'] ?? '',
                        "phone"     => $dbInfo['phone_number'] ?? '',
                        "name"      => $zalo_display_name,
                        "user_dob"  => $this->format_date($dbInfo['birth_date'] ?? '')
                    ],
                    'quota' => $quota_info,
                    'chat_link' => $zaloOA->get_chat_link($zalo_id)
                ];
            }
        }
        
        // Receive data from API
        if(empty($userData)) {
            $json_user = $zaloOA->get_user($zalo_id);
            $result['user_info'] = json_decode($json_user, true);

            if(isset($result['user_info']['error']) && $result['user_info']['error'] == 0) {
                $userData = $result['user_info']['data'];

                // Last interaction
                $zalo_last_interaction = $this->format_datetime($userData['user_last_interaction_date'] ?? '');
                $userData['user_last_interaction_date'] = $zalo_last_interaction;

                // Shared info
                if(isset($userData['shared_info']) && !empty($userData['shared_info'])) {
                    // Date of birth
                    $zalo_user_dob = $this->format_date($userData['shared_info']['user_dob'] ?? '');
                    $userData['shared_info']['user_dob'] = $zalo_user_dob;
                }

                // Quota
                $quota_info = json_decode(html_entity_decode($dbInfo['quota_info'] ?? ''), true);
                $userData['quota'] = $quota_info;

                // Chat link
                $userData['chat_link'] = $zaloOA->get_chat_link($zalo_id);

                $this->custom_save($userData, $oa_id);
            }
        }

        // Add quota
        if(is_null($userData['quota']) || empty($userData['quota'])) {
            $json_quota = $zaloOA->get_quota_user($zalo_id);
            $arr_quota = json_decode($json_quota, true);

            if(isset($arr_quota['error']) && $arr_quota['error'] == 0 && isset($arr_quota['data'])) {
                $quota_info = [];
                $last_interaction = '';

                $timestamp = $arr_quota['data']['last_interaction'] ?? 0;
                if($timestamp > 0) {
                    $last_interaction = date("Y-m-d H:i:s", $timestamp / 1000);
                }
                if(isset($arr_quota['data']['cs_reply']) && !empty($arr_quota['data']['cs_reply'])) {
                    $quota_info['cs_reply'] = $arr_quota['data']['cs_reply'];
                }
                if(isset($arr_quota['data']['promotion']) && !empty($arr_quota['data']['promotion'])) {
                    $quota_info['promotion'] = $arr_quota['data']['promotion'];
                }

                if(!empty($quota_info)) {
                    $this->db->query("UPDATE ec_zalo_contacts
                        SET quota_info = '". json_encode($quota_info) ."'
                            ,last_interaction = '{$last_interaction}'
                        WHERE zalo_id = '{$zalo_id}' AND oa_id = '{$oa_id}' AND deleted = 0");
                }

                $userData['user_last_interaction_date'] =  $this->format_datetime($last_interaction);
                $userData['quota'] = $quota_info;
            }
        }
       
        return $userData;
    }

    /**
     * Get zalo user by phone number
     * 
     * @param string $search_value
     * @param string $oa_id
     * @return array List user data
     */
    public function search_zalo_user_by_phone($search_value, $oa_id = '') {
        if(strlen($search_value) < 3) return [];

        $zaloOA = new APIZaloOA($oa_id);
        if(!is_string($oa_id) || empty($oa_id)) $oa_id = $zaloOA->get_oa_id();
        $listUserData = [];

        $condition = strlen($search_value) < 10 ? "c.phone_mobile LIKE '%$search_value'" : "c.phone_mobile = '$search_value'";
        $sql = "SELECT zc.zalo_id
            ,zc.id AS user_external_id
            ,zc.contact_id
            ,c.phone_mobile AS phone_number
            ,zc.name AS display_name
            ,zc.alias AS user_alias
            ,zc.avatar
            ,zc.birth_date
            ,zc.last_interaction
            ,zc.is_follower
            ,zc.tags
            ,zc.province_city
            ,zc.ward_commune
            ,zc.address
            ,zc.quota_info
        FROM ec_zalo_contacts zc
            LEFT JOIN contacts c ON c.id = zc.contact_id
        WHERE $condition
            AND zc.oa_id = '{$oa_id}'
            AND zc.contact_id IS NOT NULL
            AND zc.contact_id != ''
            AND c.deleted = 0";

        $res = $this->db->query($sql);
        while ($dbInfo = $this->db->fetchByAssoc($res)) {
            $zalo_id            = $dbInfo['zalo_id'] ?? '';
            $zalo_display_name  = $dbInfo['display_name'] ?? '';
            $zalo_user_alias    = $dbInfo['user_alias'] ?? '';
            $quota_info         = json_decode(html_entity_decode($dbInfo['quota_info'] ?? ''), true);

            if(!empty($zalo_id) && (!empty($zalo_display_name) || !empty($zalo_user_alias))) {
                $listUserData[] = [
                    'user_id'           => $dbInfo['zalo_id'] ?? '',
                    'user_id_by_app'    => '',
                    'user_external_id'  => $dbInfo['user_external_id'] ?? '',
                    'display_name'      => $zalo_display_name,
                    'user_alias'        => $zalo_user_alias,
                    'avatar'            => $dbInfo['avatar'] ?? '',
                    'user_last_interaction_date' => $this->format_datetime($dbInfo['last_interaction'] ?? ''),
                    'user_is_follower' => $dbInfo['is_follower'] ?? 0,
                    'tags_and_notes_info' => [
                        'notes' => [],
                        'tag_names' => !is_null($dbInfo['tags']) && !empty($dbInfo['tags']) ? explode(',', $dbInfo['tags']) : [],
                    ],
                    'shared_info' => [
                        "address"   => $dbInfo['address'] ?? '',
                        "city"      => $dbInfo['province_city'] ?? '',
                        "district"  => $dbInfo['ward_commune'] ?? '',
                        "phone"     => $dbInfo['phone_number'] ?? '',
                        "name"      => $zalo_display_name,
                        "user_dob"  => $this->format_date($dbInfo['birth_date'] ?? '')
                    ],
                    'quota' => $quota_info,
                    'chat_link' => $zaloOA->get_chat_link($zalo_id)
                ];
            }
        }
        
        return $listUserData;
    }

    /**
     * Get zalo user by alias
     * 
     * @param string $search_value
     * @param string $oa_id
     * @return array List user data
     */
    public function search_zalo_user_by_alias($search_value, $oa_id = '') {
        if(strlen($search_value) < 4) return [];

        $zaloOA = new APIZaloOA($oa_id);
        if(!is_string($oa_id) || empty($oa_id)) $oa_id = $zaloOA->get_oa_id();
        $listUserData = [];

        $sql = "SELECT zc.zalo_id
            ,zc.id AS user_external_id
            ,zc.contact_id
            ,c.phone_mobile AS phone_number
            ,zc.name AS display_name
            ,zc.alias AS user_alias
            ,zc.avatar
            ,zc.birth_date
            ,zc.last_interaction
            ,zc.is_follower
            ,zc.tags
            ,zc.province_city
            ,zc.ward_commune
            ,zc.address
            ,zc.quota_info
        FROM ec_zalo_contacts zc
            LEFT JOIN contacts c ON c.id = zc.contact_id AND c.deleted = 0
        WHERE MATCH(zc.alias) AGAINST('\"$search_value\"')
            AND zc.oa_id = '{$oa_id}'
            AND zc.deleted = 0";

        $res = $this->db->query($sql);
        while ($dbInfo = $this->db->fetchByAssoc($res)) {
            $zalo_id            = $dbInfo['zalo_id'] ?? '';
            $zalo_display_name  = $dbInfo['display_name'] ?? '';
            $zalo_user_alias    = $dbInfo['user_alias'] ?? '';
            $quota_info         = json_decode(html_entity_decode($dbInfo['quota_info'] ?? ''), true);

            if(!empty($zalo_id) && (!empty($zalo_display_name) || !empty($zalo_user_alias))) {
                $listUserData[] = [
                    'user_id'           => $dbInfo['zalo_id'] ?? '',
                    'user_id_by_app'    => '',
                    'user_external_id'  => $dbInfo['user_external_id'] ?? '',
                    'display_name'      => $zalo_display_name,
                    'user_alias'        => $zalo_user_alias,
                    'avatar'            => $dbInfo['avatar'] ?? '',
                    'user_last_interaction_date' => $this->format_datetime($dbInfo['last_interaction'] ?? ''),
                    'user_is_follower' => $dbInfo['is_follower'] ?? 0,
                    'tags_and_notes_info' => [
                        'notes' => [],
                        'tag_names' => !is_null($dbInfo['tags']) && !empty($dbInfo['tags']) ? explode(',', $dbInfo['tags']) : [],
                    ],
                    'shared_info' => [
                        "address"   => $dbInfo['address'] ?? '',
                        "city"      => $dbInfo['province_city'] ?? '',
                        "district"  => $dbInfo['ward_commune'] ?? '',
                        "phone"     => $dbInfo['phone_number'] ?? '',
                        "name"      => $zalo_display_name,
                        "user_dob"  => $this->format_date($dbInfo['birth_date'] ?? '')
                    ],
                    'quota' => $quota_info,
                    'chat_link' => $zaloOA->get_chat_link($zalo_id)
                ];
            }
        }
        
        return $listUserData;
    }

    /**
     * Init consultation quota for user
     */
    public function init_consultation_quota() {
        return ["remain" => 8, "total" => 8];
    }
    
    /**
     * Check zalo contact action
     * 
     * @param string $act call, send_consultation, send_transaction, send_promotion
     * @param string $zalo_id
     * @param string $oa_id
     * 
     * @return bool
     */
    public function check_zalo_contact_action($act, $zalo_id, $oa_id = '') {
        if(empty($zalo_id)) return false;

        if(!is_string($oa_id) || empty($oa_id)) {
            $zaloOA = new APIZaloOA();
            $oa_id = $zaloOA->get_oa_id();
        }

        $sql = "SELECT zc.id
                ,zc.last_interaction
                ,zc.is_follower
            FROM ec_zalo_contacts zc
            WHERE zc.zalo_id = '{$zalo_id}'
                AND zc.oa_id = '{$oa_id}'
                AND zc.deleted = 0";

        $res = $this->db->query($sql);
        $dbInfo = $this->db->fetchByAssoc($res);

        $last_interaction = $dbInfo['last_interaction'] ?? null;
        if(is_null($last_interaction) || !strtotime($last_interaction)) return false;
        $is_follower = (bool)($dbInfo['last_interaction'] ?? 0);
        $current_datetime = date('Y-m-d H:i:s');
        
        $value = strtotime($current_datetime) - strtotime($last_interaction);

        if($act === 'call') return $value <= 30;
        elseif($act === 'send_consultation') return $value <= 7;
        elseif($act === 'send_transaction') return true;
        elseif($act === 'send_promotion') return $is_follower;
        return false;
    }
	
    /**
     * Sync zalo data from contacts table
     * This function only run by admin
     * 
     * @return void
     */
    public function sync_data_from_contacts_table() {
        global $current_user;
        if($current_user->id != '1') return;

        $sql = "SELECT id
                ,last_name
                ,birthdate
                ,primary_address_street AS address
                ,primary_address_city AS province_city
                ,primary_address_state AS ward_commune
                ,zalo_id
                ,zalo_last_interaction
                ,zalo_name
                ,zalo_avatar
                ,zalo_is_follower
                ,zalo_tags
            FROM contacts
            WHERE zalo_id IS NOT NULL
                AND zalo_id != ''
                AND deleted = 0";

        $res = $this->db->query($sql);
        while($row = $this->db->fetchByAssoc($res)) {
            try {
                $zaloContact = new EC_Zalo_Contacts();
                $zaloContact->zalo_id           = $row['zalo_id'];
                $zaloContact->oa_id             = '2941581384627345950';
                $zaloContact->contact_id        = $row['id'];
                $zaloContact->name              = $row['zalo_name'] ?? $row['last_name'];
                $zaloContact->alias             = $row['zalo_name'] ?? '';
                $zaloContact->avatar            = $row['zalo_avatar'] ?? '';
                $zaloContact->birth_date        = $row['birthdate'];
                $zaloContact->last_interaction  = $row['zalo_last_interaction'];
                $zaloContact->is_follower       = (int)$row['zalo_is_follower'];
                $zaloContact->tags              = $row['zalo_tags'];
                $zaloContact->province_city     = $row['province_city'];
                $zaloContact->ward_commune      = $row['ward_commune'];
                $zaloContact->address           = $row['address'];
                $zaloContact->description       = "Sync from contacts table";
                $newId = $zaloContact->save();

                if($newId) echo "<p>$newId: Success</p>";
                else echo "<p class='text-danger'>{$row['zalo_id']}: Fail $newId</p>";
            }
            catch(Throwable $th) {
                $zid = $row['zalo_id'] ?? '';
                echo "<p class='text-danger'>{$zid}: Maybe duplicate</p>";
            }
        }
    }

    /**
     * Format date
     * 
     * @param string $dateStr
     * @param string $format
     * @return string
     */
    protected function format_date($dateStr, $format = 'd-m-Y') {
        if(!is_string($dateStr) || strlen($dateStr) < 10) return $dateStr;

        $str = str_replace("/", "-", $dateStr);
        if(strtotime($str)) return date($format, strtotime($str));
        return $dateStr;
    }

    /**
     * Format date time
     * 
     * @param string $datetimeStr
     * @param string $format
     * @return string
     */
    public function format_datetime($dateStr, $format = 'd-m-Y H:i:s') {
        if(!is_string($dateStr) || strlen($dateStr) < 10) return $dateStr;

        $str = str_replace("/", "-", $dateStr);
        if(strtotime($str)) return date($format, strtotime($str));
        return $dateStr;
    }
}
