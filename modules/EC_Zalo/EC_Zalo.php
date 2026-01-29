<?php
require_once "custom/include/helpers/api/APIZaloOA.php";

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

    public $oa_alias;
    public $oa_type;
    public $cate_name;
    public $is_verified;
    public $num_follower;
    public $avatar;
    public $cover;
    public $package_name;
    public $package_valid_through_date;
    public $package_auto_renew_date;
    public $linked_zca;
    public $api_oauth_info;
    public $quota_info;
    public $secret_key;
	
    public function bean_implements($interface) {
        switch($interface)
        {
            case 'ACL':
                return true;
        }
        return false;
    }

    /**
     * Get info Zalo OA
     * 
     * @param string $oa_id
     * 
     * @return array
     */
    public function get_info_oa($oa_id) {
        if(!is_string($oa_id) || strlen($oa_id) < 10) return [];

        $sql = "SELECT id AS oaid
                ,name
                ,description
                ,oa_alias
                ,oa_type
                ,cate_name
                ,is_verified
                ,num_follower
                ,avatar
                ,cover
                ,package_name
                ,package_valid_through_date
                ,package_auto_renew_date
                ,linked_zca AS linked_ZCA
                ,quota_info
                ,api_oauth_info 
                ,DATE_ADD(date_modified, INTERVAL 7 HOUR) AS date_modified
            FROM ec_zalo
            WHERE id = '{$oa_id}' AND deleted = 0";
        $res = $this->db->query($sql);
        $dbInfo = $this->db->fetchByAssoc($res);

        $api_oauth_info = json_decode(html_entity_decode($dbInfo['api_oauth_info']), true);
        if(is_null($api_oauth_info) || empty($api_oauth_info) || !isset($api_oauth_info['access_token'])) return ['error' => -216];

        if(is_array($dbInfo) && !empty($dbInfo) && date('Y-m-d') == date('Y-m-d', strtotime($dbInfo['date_modified']))) {
            unset($dbInfo['date_modified']);
            $dbInfo['quota'] = json_decode(html_entity_decode($dbInfo['quota_info']), true);
            return $dbInfo;
        }
        else {
            $zaloOA = new APIZaloOA();
            $json_info_oa = $zaloOA ->get_info_oa();
            $arr_info_oa = json_decode($json_info_oa, true);

            if(isset($arr_info_oa['error']) && $arr_info_oa['error'] == 0) {
                $data_info_oa = $arr_info_oa['data'] ?? [];

                // Get quota info
                $json_quota_oa = $zaloOA ->get_quota_oa();
                $arr_quota_oa = json_decode($json_quota_oa, true);
                if(isset($arr_quota_oa['error']) && $arr_quota_oa['error'] == 0) {
                    $data_quota_oa = $arr_quota_oa['data'] ?? [];

                    $quota_info = [];
                    foreach($data_quota_oa as $q) {
                        $q["valid_through"] = $this->format_date($q["valid_through"]);
                        $quota_info[] = $q;
                    }

                    // Update to db
                    $beanZaloOA = new EC_Zalo();
                    $beanZaloOA->retrieve($oa_id);
                    $beanZaloOA->oa_alias = $data_info_oa["oa_alias"] ?? "";
                    $beanZaloOA->oa_type  = $data_info_oa["oa_type"] ?? "";
                    $beanZaloOA->cate_name = $data_info_oa["cate_name"] ?? "";
                    $beanZaloOA->is_verified = $data_info_oa["is_verified"] ?? null;
                    $beanZaloOA->num_follower = $data_info_oa["num_follower"] ?? null;
                    $beanZaloOA->avatar = $data_info_oa["avatar"] ?? "";
                    $beanZaloOA->cover = $data_info_oa["cover"] ?? "";
                    $beanZaloOA->package_name = $data_info_oa["package_name"] ?? "";
                    $beanZaloOA->package_valid_through_date = $this->format_date($data_info_oa["package_valid_through_date"]);
                    $beanZaloOA->package_auto_renew_date = $this->format_date($data_info_oa["package_auto_renew_date"]);
                    $beanZaloOA->linked_zca = $data_info_oa["linked_ZCA"] ?? "";
                    $beanZaloOA->quota_info = json_encode($quota_info);
                    $beanZaloOA->save();
                    
                    $data_info_oa['quota'] = $quota_info;
                }

                return $data_info_oa;
            }
            else return $dbInfo ?? $arr_info_oa; 
        }

        return [];
    }

    /**
     * Handle error zalo oa api
     * 
     * @param int $error_code
     * @param string $error_description
     * @param string $zalo_id
     * @param string $oa_id
     * @return void
     */
    public static function handle_error_oa_api($error_code, $error_description = '', $zalo_id = '', $oa_id = '') {
        try {
            if(is_null($error_code) || empty($error_code)) return;

            global $db;
            $date_modified = date('Y-m-d H:i:s', time() - 7*60*60);

            $description = $error_description;
            // if(empty($description)) $description = "Handle error({$error_code}) oa api";

            $whereZaloId = "zalo_id = '{$zalo_id}'";
            if(!empty($oa_id)) $whereZaloId .= " AND oa_id = '{$oa_id}'";

            switch ((int)$error_code) {
                case -213:
                    if(!empty($zalo_id)) {
                        if(empty($description)) $description = 'User has not followed OA';
                        $descriptionEscaped = $db->quote($description);

                        $sqlUpdate = "UPDATE ec_zalo_contacts
                            SET is_follower = 0
                                ,description = '{$descriptionEscaped}'
                                ,modified_user_id = ''
                                ,date_modified = '{$date_modified}'
                            WHERE $whereZaloId AND deleted = 0";
                        $db->query($sqlUpdate);
                    }
                    break;
                case -227:
                    if(!empty($zalo_id)) {
                        if(empty($description)) $description = 'User is banned or has been inactive for more than 45 days';
                        $descriptionEscaped = $db->quote($description);

                        $sqlUpdate = "UPDATE ec_zalo_contacts
                            SET status = 'banned'
                                ,description = '{$descriptionEscaped}'
                                ,modified_user_id = ''
                                ,date_modified = '{$date_modified}'
                            WHERE $whereZaloId AND deleted = 0";
                        $db->query($sqlUpdate);
                    }
                    break;
                case -232:
                    if(!empty($zalo_id)) {
                        if(empty($description)) $description = 'User has not interacted with the OA, or the last interaction has expired';
                        $descriptionEscaped = $db->quote($description);

                        $sqlUpdate = "UPDATE ec_zalo_contacts
                            SET status = 'expired'
                                ,description = '{$descriptionEscaped}'
                                ,modified_user_id = ''
                                ,date_modified = '{$date_modified}'
                            WHERE $whereZaloId AND deleted = 0";
                        $db->query($sqlUpdate);
                    }
                    break;
                case -244:
                    if(!empty($zalo_id)) {
                        if(empty($description)) $description = 'User has restricted this message type from your OA';
                        $descriptionEscaped = $db->quote($description);

                        $sqlUpdate = "UPDATE ec_zalo_contacts
                            SET status = 'restricted'
                                ,description = '{$descriptionEscaped}'
                                ,modified_user_id = ''
                                ,date_modified = '{$date_modified}'
                            WHERE $whereZaloId AND deleted = 0";
                        $db->query($sqlUpdate);
                    }
                    break;
                default:
                    global $sugar_config;
                    $botToken = $sugar_config['telegram']['bot_token'] ?? '';
                    $chatId   = $sugar_config['telegram']['chat_id'] ?? '';
                    $threadId = $sugar_config['telegram']['thread_id_system_noti'] ?? '';
                    $message  = "[INFO] Unprocessed cases in ".__FUNCTION__."()";
                    $message .= "\n{$error_code}: {$error_description}";
                    if(!empty($zalo_id)) $message .= "\nZalo Id: {$zalo_id}";
                    Telegram::sendMessage($message, $botToken, $chatId, $threadId);
                    break;
            }
        }
        catch(Throwable $th) {
            global $sugar_config;
            $botToken = $sugar_config['telegram']['bot_token'] ?? '';
            $chatId   = $sugar_config['telegram']['chat_id'] ?? '';
            $threadId = $sugar_config['telegram']['thread_id_logs'] ?? '';
            $message  = "<b>[ERROR] Throwable in ".__FUNCTION__."()</b>";
            $message .= "\n{$th->getMessage()} on line {$th->getLine()}";
            Telegram::sendMessage($message, $botToken, $chatId, $threadId);
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
