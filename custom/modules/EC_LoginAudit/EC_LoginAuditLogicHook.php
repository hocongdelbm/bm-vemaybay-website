<?php

class loginActions
{

    function updateLoginAudit(&$la_bean, $la_event, $la_arguments = 'failed')
    {

        global $current_user, $db;

        // Sugar strangely doesn't populate event on login_failed
        if (empty($la_event)) {
            $la_event = 'login_failed';
        }

        $agent_status   = '';
        switch ($la_event) {
            case 'login_failed':
                $la_result = "Failed";
                break;
            case 'after_login':
                $agent_status   = 'Available';
                $la_result = "Success";
                break;
            case 'before_logout':
                $agent_status   = 'Logged Out';
                $la_result = "Logout";
                break;
            default:
                return;
        }

        $uuid = create_guid();
        $typed_name = isset($_REQUEST['user_name']) ? $db->quote($_REQUEST['user_name']) : '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

        // Browser
        $brower          = trim($_SERVER['HTTP_SEC_CH_UA']);
        $platform        = trim($_SERVER['HTTP_SEC_CH_UA_PLATFORM'], '"'); //Windows
        $user_agent      = $_SERVER['HTTP_USER_AGENT'];
        $timestamp       = gmdate('Y-m-d H:i:s');

        $query = "INSERT INTO ec_loginaudit (id,name,date_entered,date_modified,modified_user_id,created_by,description,deleted,assigned_user_id,ip_address,typed_name,is_admin,result, platform, browser, user_agent)
                                VALUES ('$uuid','','$timestamp', '$timestamp','$current_user->id','1','','0','1','$ip_address','$typed_name','$current_user->is_admin','$la_result', '$platform', '$brower', '$user_agent')";

        $db->query($query, false);
        
        // Update change status agent and update agent status
        if (!empty($la_event) && !empty($agent_status) && !empty($current_user->td_sip)) {
            agent_change_status($current_user->td_sip, $agent_status);
        }

        // Nếu login lần đầu cập nhật start_online
        if ($la_event === 'after_login' && $la_result === 'Success') {
            $today_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
            $sql_exist = 
                "SELECT id, start_online
                FROM ec_online_report
                WHERE assigned_user_id = '{$current_user->id}'
                    AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) = '$today_vn '
                    AND deleted = 0";

            $row = $db->fetchByAssoc($db->query($sql_exist));

            if (!empty($row) && empty($row['start_online'])) {
                $db->query("UPDATE ec_online_report SET start_online = NOW() WHERE id = '{$row['id']}' AND deleted = 0");
            } 
        }
    }
}
