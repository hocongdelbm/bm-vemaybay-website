<?php
require_once "custom/include/helpers/api/APIZaloOA.php";

class EC_Zalo_Contacts_Helper {
    /**
     * Add or update zalo user
     * 
     * @param array $user_data Data from API
     * @param string $oa_id
     * @param string $description
     * @return string|bool
     */
    public static function custom_save($user_data, $oa_id = '', $description = '') {
        global $current_user;

        // These properties depend on SuiteCRM version
        $dateFormat = $current_user->date_format ?? 'd-m-Y';
        $timeFormat = $current_user->time_format ?? 'H:i';
        // $timezone   = $current_user->timezone ?? 'Asia/Ho_Chi_Minh';

        $zalo_id = $user_data['user_id'] ?? '';
        if(empty($zalo_id)) return false;

        global $db;
        $zaloOA = new APIZaloOA('', $oa_id);
        if(!is_string($oa_id) || empty($oa_id)) $oa_id = $zaloOA->get_oa_id();

        // $user_external_id = $user_data['user_external_id'] ?? '';
        $record_id = '';
        if(empty($record_id)) {
            $sqlCheck = "SELECT id FROM ec_zalo_contacts WHERE zalo_id = '{$zalo_id}' AND oa_id = '{$oa_id}' AND deleted = 0";
            $record_id = $db->getOne($sqlCheck) ?? '';
        }

        // Alias
        $user_alias = $user_data['user_alias'] ?? '';

        // Last interaction
        $last_interaction = self::format_datetime($user_data['user_last_interaction_date'] ?? '', "$dateFormat $timeFormat");

        // Phone
        $phone = $zaloOA->unformat_zalo_phone($user_data['shared_info']['phone'] ?? '');
        if(empty($phone)) $phone = $zaloOA->get_phone_by_alias($user_alias);

        // Contact
        $contact_id = '';
        if(!empty($phone)) {
            $contact_id = $db->getOne("SELECT id FROM contacts WHERE phone_mobile = '{$phone}' AND deleted = 0 ORDER BY date_entered LIMIT 1") ?? '';
        }

        // Date of birth
        $shared_user_dob = self::format_date($user_data['shared_info']['user_dob'] ?? '');

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
        $zaloContact->last_interaction  = $last_interaction; // Save to DB is UTC timezone
        $zaloContact->is_follower       = (int)$user_data['user_is_follower'];
        $zaloContact->birth_date        = $shared_user_dob;
        $zaloContact->tags              = $tag_names;
        $zaloContact->province_city     = $user_data['shared_info']['city'] ?? '';
        $zaloContact->ward_commune      = $user_data['shared_info']['district'] ?? '';
        $zaloContact->address           = $user_data['shared_info']['address'] ?? '';
        $zaloContact->quota_info        = $quota_info;
        $zaloContact->status            = '';
        return $zaloContact->save();
    }

    /**
     * Map contact id to zalo contact
     * 
     * @param string $contact_id
     * @param string $zalo_id
     * @param string $oa_id
     * @return bool
     */
    public static function map_contact_zalo($contact_id, $zalo_id, $oa_id = '') {
        if(empty($contact_id) || empty($zalo_id)) return false;

        try {
            global $db;
            $zaloOA = new APIZaloOA('', $oa_id);
            if(!is_string($oa_id) || empty($oa_id)) $oa_id = $zaloOA->get_oa_id();

            $sql = "UPDATE ec_zalo_contacts
                SET contact_id = '{$contact_id}'
                WHERE zalo_id = '{$zalo_id}'
                    AND oa_id = '{$oa_id}'
                    AND deleted = 0";
            return $db->query($sql);
        }
        catch(Throwable $th) {
            return false;
        }
    }

    /**
     * Update promotion quota after sending to user success
     * 
     * @param string $zalo_id
     * @param string $oa_id
     * @return bool
     */
    public static function update_promotion_quota($zalo_id, $oa_id = '') {
        if(empty($zalo_id)) return false;

        global $db;
        if(!is_string($oa_id) || empty($oa_id)) {
            $zaloOA = new APIZaloOA();
            $oa_id = $zaloOA->get_oa_id();
        }

        try {
            $sqlCheck = "SELECT COUNT(*) FROM ec_zalo_contacts WHERE zalo_id = '{$zalo_id}' AND oa_id = '{$oa_id}' AND deleted = 0";
            $isExistInDB = (int)($db->getOne($sqlCheck) ?? 0);

            $zaloInfo = EC_Zalo_Contacts_Helper::get_zalo_user_info($zalo_id, $oa_id);

            if($isExistInDB) {
                $quota_info = $zaloInfo['quota'] ?? []; 

                if(is_array($quota_info) && !empty($quota_info) && isset($quota_info["promotion"])) {
                    $quota_info["promotion"]["daily_remain"] = 0;
                    if(isset($quota_info["promotion"]["monthly_remain"]) && $quota_info["promotion"]["monthly_remain"] > 0) {
                        $quota_info["promotion"]["monthly_remain"] -= 1;
                    }
                }
                else {
                    $quota_info = [
                        "promotion" => [
                            "daily_remain"  => 0,
                            "daily_total"   => 1,
                            "monthly_remain"=> 3,
                            "monthly_total" => 4
                        ]
                    ];
                }

                $quota_info = json_encode($quota_info);
                $dateModified = date('Y-m-d H:i:s', time() - 7*60*60);
                $sqlUpdate = "UPDATE ec_zalo_contacts
                    SET quota_info = '{$quota_info}'
                        ,is_follower = 1
                        ,status = ''
                        ,date_modified = '$dateModified'
                        ,modified_user_id = ''
                    WHERE zalo_id = '{$zalo_id}' AND oa_id = '{$oa_id}'";

                return $db->query($sqlUpdate) ?? false;
            }
            elseif(!empty($zaloInfo)) return true;
            return false;
        }
        catch(Throwable $th) {
            $GLOBALS['log']->error("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
        }
    }

    /**
     * Get zalo user info by Zalo id
     * 
     * @param string $zalo_id
     * @param string $oa_id
     * @param bool $only_db Only get data in database
     * @return array User data
     */
    public static function get_zalo_user_info($zalo_id, $oa_id = '', $only_db = false) {
        if(!$zalo_id || strlen($zalo_id) < 15) return [];

        global $db;
        $zaloOA = new APIZaloOA('', $oa_id);
        if(!is_string($oa_id) || empty($oa_id)) $oa_id = $zaloOA->get_oa_id();
        $userData = [];

        $sql = 
        "SELECT zc.id AS user_external_id
            ,zc.contact_id
            ,c.phone_mobile AS phone_number
            ,zc.name AS display_name
            ,zc.alias AS user_alias
            ,zc.avatar
            ,zc.birth_date
            ,DATE_ADD(zc.last_interaction, INTERVAL 7 HOUR) AS last_interaction
            ,zc.is_follower
            ,zc.tags
            ,zc.province_city
            ,zc.ward_commune
            ,zc.address
            ,zc.quota_info
            ,zc.status
            ,DATE_ADD(zc.date_modified, INTERVAL 7 HOUR) AS date_modified
        FROM ec_zalo_contacts zc
            LEFT JOIN contacts c ON c.id = zc.contact_id AND c.deleted = 0
        WHERE zc.zalo_id = '{$zalo_id}'
            AND zc.oa_id = '{$oa_id}'
            AND zc.deleted = 0
        ORDER BY zc.date_entered
        LIMIT 1";

        $res = $db->query($sql);
        $dbInfo = $db->fetchByAssoc($res);
        $refresh_time = 86400*7; // 7 days

        // Receive data from database
        if(is_array($dbInfo) && !empty($dbInfo) && ($only_db == true || time() - strtotime($dbInfo['date_modified']) < $refresh_time)) {
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
                    'user_last_interaction_date' => self::format_datetime($dbInfo['last_interaction'] ?? ''),
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
                        "user_dob"  => self::format_date($dbInfo['birth_date'] ?? '')
                    ],
                    'quota' => $quota_info,
                    'chat_link' => $zaloOA->get_chat_link($zalo_id)
                ];
            }
        }
        
        if($only_db == false) {
            // Receive data from API
            if(empty($userData)) {
                $json_user = $zaloOA->get_user($zalo_id);
                $result_user_info = json_decode($json_user, true);

                if(isset($result_user_info['error']) && $result_user_info['error'] == 0) {
                    $userData = $result_user_info['data'];

                    // Last interaction
                    $zalo_last_interaction = self::format_datetime($userData['user_last_interaction_date'] ?? '');
                    $userData['user_last_interaction_date'] = $zalo_last_interaction;

                    // Shared info
                    if(isset($userData['shared_info']) && !empty($userData['shared_info'])) {
                        // Date of birth
                        $zalo_user_dob = self::format_date($userData['shared_info']['user_dob'] ?? '');
                        $userData['shared_info']['user_dob'] = $zalo_user_dob;
                    }

                    // Quota
                    $quota_info = json_decode(html_entity_decode($dbInfo['quota_info'] ?? ''), true);
                    $userData['quota'] = $quota_info;

                    // Chat link
                    $userData['chat_link'] = $zaloOA->get_chat_link($zalo_id);

                    self::custom_save($userData, $oa_id);
                }
                else {
                    EC_Zalo_Helper::handle_error_oa_api($result_user_info['error'] ?? null, $result_user_info['message'] ?? '', $zalo_id, $oa_id);
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
                    if(isset($arr_quota['data']['promotion']) && !empty($arr_quota['data']['promotion'])) {
                        $quota_info['promotion'] = $arr_quota['data']['promotion'];
                    }

                    if(!empty($quota_info)) {
                        $db->query("UPDATE ec_zalo_contacts
                            SET quota_info = '". json_encode($quota_info) ."'
                                ,last_interaction = '{$last_interaction}'
                            WHERE zalo_id = '{$zalo_id}' AND oa_id = '{$oa_id}' AND deleted = 0");
                    }

                    $userData['user_last_interaction_date'] = self::format_datetime($last_interaction);
                    $userData['quota'] = $quota_info;
                }
            }
        }
       
        return $userData;
    }

    /**
     * Get list zalo user by last interaction day
     * 
     * @param int $day
     * @return array
     */
    public static function get_list_zalo_user_by_last_interaction_day($day) {
        if(!is_numeric($day)) return [];

        global $db;
        $day = (int)$day;
        $results = [];

        $sql = "SELECT zalo_id
                ,oa_id
                ,id AS user_external_id
                ,contact_id
                ,name AS display_name
                ,alias AS user_alias
                ,avatar
                ,birth_date
                ,DATE_ADD(last_interaction, INTERVAL 7 HOUR) AS last_interaction
                ,is_follower
                ,tags
                ,province_city
                ,ward_commune
                ,address
                ,quota_info
                ,status
            FROM ec_zalo_contacts zc
            WHERE TIMESTAMPDIFF(DAY, last_interaction, UTC_TIMESTAMP()) = {$day}
                AND deleted = 0";
                
        $res = $db->query($sql);
        while($row = $db->fetchByAssoc($res)) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Get users with birthday
     * 
     * @param string $birthdate Y-m-d
     * @return array
     */
    public static function get_users_with_birthday($birthdate = '') {
        $birthdate = trim($birthdate);
        if(!is_string($birthdate) || strlen($birthdate) != 10) $birthdate = date('Y-m-d');

        global $db;
        $results = [];

        $sql = "SELECT zalo_id
                ,oa_id
                ,id AS user_external_id
                ,contact_id
                ,name AS display_name
                ,alias AS user_alias
                ,avatar
                ,birth_date
                ,DATE_ADD(last_interaction, INTERVAL 7 HOUR) AS last_interaction
                ,is_follower
                ,tags
                ,province_city
                ,ward_commune
                ,address
                ,quota_info
                ,status
            FROM ec_zalo_contacts
            WHERE birth_date = '{$birthdate}' AND deleted = 0";

        $res = $db->query($sql);
        while($row = $db->fetchByAssoc($res)) {   
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Get users with birth month
     * 
     * @param int $birthmonth
     * @return array
     */
    public static function get_users_with_birthmonth($birthmonth = null) {
        if(!is_numeric($birthmonth) || $birthmonth < 1 || $birthmonth > 12) return [];

        global $db;
        $results = [];

        $sql = "SELECT zalo_id
                ,oa_id
                ,id AS user_external_id
                ,contact_id
                ,name AS display_name
                ,alias AS user_alias
                ,avatar
                ,birth_date
                ,DATE_ADD(last_interaction, INTERVAL 7 HOUR) AS last_interaction
                ,is_follower
                ,tags
                ,province_city
                ,ward_commune
                ,address
                ,quota_info
                ,status
            FROM ec_zalo_contacts
            WHERE MONTH(birth_date) = {$birthmonth} AND deleted = 0";
        $res = $db->query($sql);
        while($row = $db->fetchByAssoc($res)) {   
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Get zalo user by phone number
     * 
     * @param string $search_value
     * @param string $oa_id
     * @return array List user data
     */
    public static function search_zalo_user_by_phone($search_value, $oa_id = '') {
        if(strlen($search_value) < 3) return [];

        global $db;
        $zaloOA = new APIZaloOA('', $oa_id);
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
            ,DATE_ADD(zc.last_interaction, INTERVAL 7 HOUR) AS last_interaction
            ,zc.is_follower
            ,zc.tags
            ,zc.province_city
            ,zc.ward_commune
            ,zc.address
            ,zc.quota_info
            ,zc.status
        FROM ec_zalo_contacts zc
            LEFT JOIN contacts c ON c.id = zc.contact_id
        WHERE $condition
            AND zc.oa_id = '{$oa_id}'
            AND zc.contact_id IS NOT NULL
            AND zc.contact_id != ''
            AND c.deleted = 0";

        $res = $db->query($sql);
        while ($dbInfo = $db->fetchByAssoc($res)) {
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
                    'user_last_interaction_date' => self::format_datetime($dbInfo['last_interaction'] ?? ''),
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
                        "user_dob"  => self::format_date($dbInfo['birth_date'] ?? '')
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
    public static function search_zalo_user_by_alias($search_value, $oa_id = '') {
        if(strlen($search_value) < 4) return [];

        global $db;
        $zaloOA = new APIZaloOA('', $oa_id);
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
            ,DATE_ADD(zc.last_interaction, INTERVAL 7 HOUR) AS last_interaction
            ,zc.is_follower
            ,zc.tags
            ,zc.province_city
            ,zc.ward_commune
            ,zc.address
            ,zc.quota_info
            ,zc.status
        FROM ec_zalo_contacts zc
            LEFT JOIN contacts c ON c.id = zc.contact_id AND c.deleted = 0
        WHERE MATCH(zc.alias) AGAINST('\"$search_value\"')
            AND zc.oa_id = '{$oa_id}'
            AND zc.deleted = 0";

        $res = $db->query($sql);
        while ($dbInfo = $db->fetchByAssoc($res)) {
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
                    'user_last_interaction_date' => self::format_datetime($dbInfo['last_interaction'] ?? ''),
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
                        "user_dob"  => self::format_date($dbInfo['birth_date'] ?? '')
                    ],
                    'quota' => $quota_info,
                    'chat_link' => $zaloOA->get_chat_link($zalo_id)
                ];
            }
        }
        
        return $listUserData;
    }

    /**
     * Check zalo contact action
     * 
     * @param string $act call, send_consultation, send_promotion
     * @param string $zalo_id
     * @param string $oa_id
     * @return bool
     */
    public static function check_zalo_contact_action($act, $zalo_id, $oa_id = '') {
        if(empty($zalo_id)) return false;

        try {
            global $db;
            if(!is_string($oa_id) || empty($oa_id)) {
                $zaloOA = new APIZaloOA();
                $oa_id = $zaloOA->get_oa_id();
            }
            
            $sql = "SELECT zc.id
                    ,zc.is_follower
                    ,DATE_ADD(zc.last_interaction, INTERVAL 7 HOUR) AS last_interaction
                FROM ec_zalo_contacts zc
                WHERE zc.zalo_id = '{$zalo_id}'
                    AND zc.oa_id = '{$oa_id}'
                    AND (zc.status = '' OR zc.status IS NULL)
                    AND zc.deleted = 0";

            $res = $db->query($sql);
            $dbInfo = $db->fetchByAssoc($res);

            if(!$dbInfo || empty($dbInfo)) return false;
            return self::check_zalo_contact_action_by_data($act, $dbInfo['last_interaction'], $dbInfo['is_follower']);
        }
        catch (Throwable $th) {
            return false;
        }
    }

    /**
     * Check zalo contact action by available data
     * 
     * @param string $act call, send_consultation, send_promotion
     * @param string $last_interaction Y-m-d H:i:s (Asia/Ho_Chi_Minh)
     * @param int $is_follower
     * @return bool
     */
    public static function check_zalo_contact_action_by_data($act, $last_interaction, $is_follower) {
        if(is_null($last_interaction) || !strtotime($last_interaction)) return false;
        $is_follower = (bool)($is_follower ?? 0);
        $current_datetime = date('Y-m-d H:i:s');

        $value = strtotime($current_datetime) - strtotime($last_interaction); // Second
        $day = round($value / 86400); // Day

        if($act === 'call') return $day <= 30;
        elseif($act === 'send_consultation') return $day <= 7;
        elseif($act === 'send_promotion') return $is_follower;
        return false;
    }

    /**
     * Sync zalo data from contacts table
     * This function only run by admin
     * 
     * @return void
     */
    public static function sync_data_from_contacts_table() {
        global $current_user, $db;
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

        $res = $db->query($sql);
        while($row = $db->fetchByAssoc($res)) {
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
    public static function format_date($dateStr, $format = 'd-m-Y') {
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
    public static function format_datetime($dateStr, $format = 'd-m-Y H:i:s') {
        if(!is_string($dateStr) || strlen($dateStr) < 10) return $dateStr;

        $str = str_replace("/", "-", $dateStr);
        if(strtotime($str)) return date($format, strtotime($str));
        return $dateStr;
    }
}