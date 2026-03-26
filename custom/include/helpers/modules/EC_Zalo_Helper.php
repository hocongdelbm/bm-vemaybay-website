<?php
require_once "custom/include/helpers/api/APIZaloOA.php";

use Custom\Services\Notification\NotificationService;

class EC_Zalo_Helper {
    public const IMAGE_PATH = "themes/SuiteP/images/zalo_messages"; 

    /**
     * Get info Zalo OA
     * 
     * @param string $oa_id
     * @return array
     */
    public static function get_info_oa($oa_id) {
        if(!is_string($oa_id) || strlen($oa_id) < 10) return [];
        
        global $db;
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
        $res = $db->query($sql);
        $dbInfo = $db->fetchByAssoc($res);

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
                        $q["valid_through"] = self::format_date($q["valid_through"]);
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
                    $beanZaloOA->package_valid_through_date = self::format_date($data_info_oa["package_valid_through_date"]);
                    $beanZaloOA->package_auto_renew_date = self::format_date($data_info_oa["package_auto_renew_date"]);
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
            if(!is_numeric($error_code) || $error_code == 0) return;

            global $db;
            $date_modified = date('Y-m-d H:i:s', time() - 7*60*60);
            $description = $error_description;

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
                    $message  = "Unprocessed cases in ".__FUNCTION__."()";
                    $message .= "\n{$error_code}: {$error_description}";
                    if(!empty($zalo_id)) $message .= "\nZalo Id: {$zalo_id}";
                    if(!empty($oa_id)) $message .= "\nOA Id: {$oa_id}";
                    NotificationService::sendWarningMessage($message, '', ['threadKey' => 'logs']);
                    break;
            }
        }
        catch(Throwable $th) {
            $message  = "Throwable in ".__FUNCTION__."()";
            $message .= "\n{$th->getMessage()} on line {$th->getLine()}";
            NotificationService::sendErrorMessage($message, '', ['threadKey' => 'logs']);
        }
    }

    /**
     * Format date
     * 
     * @param string $dateStr
     * @param string $format
     * @return string
     */
    protected static function format_date($dateStr, $format = 'd-m-Y') {
        if(!is_string($dateStr) || strlen($dateStr) < 10) return $dateStr;

        $str = str_replace("/", "-", $dateStr);
        if(strtotime($str)) return date($format, strtotime($str));
        return $dateStr;
    }
}