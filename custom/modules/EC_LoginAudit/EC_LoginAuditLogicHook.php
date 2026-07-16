<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class loginActions
{
    // Trạng thái ec_online_report: 0 = Offline, 1 = Online, 2 = Busy
    const STATUS_OFFLINE = 0;
    const STATUS_ONLINE  = 1;

    public function updateLoginAudit(&$la_bean, $la_event, $la_arguments = 'failed')
    {
        global $current_user, $db;

        if (empty($la_event)) {
            $la_event = 'login_failed';
        }

        switch ($la_event) {
            case 'login_failed':
                $la_result = 'Failed';
                break;
            case 'after_login':
                $la_result = 'Success';
                break;
            case 'before_logout':
                $la_result = 'Logout';
                break;
            default:
                return;
        }

        $uuid       = create_guid();
        $typed_name = isset($_REQUEST['user_name']) ? $db->quote($_REQUEST['user_name']) : '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

        // Browser
        $brower     = isset($_SERVER['HTTP_SEC_CH_UA']) ? trim($_SERVER['HTTP_SEC_CH_UA']) : '';
        $platform   = isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM']) ? trim($_SERVER['HTTP_SEC_CH_UA_PLATFORM'], '"') : ''; // Windows
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $timestamp  = gmdate('Y-m-d H:i:s');

        $query = "INSERT INTO ec_loginaudit (id,name,date_entered,date_modified,modified_user_id,created_by,description,deleted,assigned_user_id,ip_address,typed_name,is_admin,result, platform, browser, user_agent)
                                VALUES ('$uuid','','$timestamp', '$timestamp','$current_user->id','1','','0','1','$ip_address','$typed_name','$current_user->is_admin','$la_result', '$platform', '$brower', '$user_agent')";

        $db->query($query, false);

        // ===== Cơ chế Online / Offline theo phiên đăng nhập BM =====
        // Đăng nhập vào BM  => Online  (đồng bộ agent = Available)
        // Đăng xuất khỏi BM => Offline (đồng bộ agent = Logged Out)
        if ($la_event === 'after_login' && $la_result === 'Success') {
            $this->syncOnlineStatus($current_user, true);
        } else if ($la_event === 'before_logout') {
            $this->syncOnlineStatus($current_user, false);
        }
    }

    /**
     * Đồng bộ trạng thái Online/Offline của user khi đăng nhập / đăng xuất BM.
     *
     * - Cập nhật users.agent_status
     * - Đồng bộ trạng thái agent trên tổng đài + ghi log online.
     *
     * @param User $user     User đang đăng nhập / đăng xuất (thường là $current_user)
     * @param bool $isOnline true = đăng nhập (Online), false = đăng xuất (Offline)
     */
    private function syncOnlineStatus($user, $isOnline)
    {
        global $db, $timedate;

        if (empty($user) || empty($user->id)) {
            return;
        }

        // Cơ chế online/offline không áp dụng cho admin không phải QuanLy
        if (function_exists('isUserEligibleForOnline') && !isUserEligibleForOnline($user)) {
            return;
        }

        $user_id  = $user->id;
        $now_gmt  = $timedate->nowDb();
        $today_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');

        // Thông tin user (bean khi logout có thể chưa nạp đủ các field cần dùng)
        $info = $db->fetchByAssoc($db->query(
            "SELECT td_sip, title
             FROM users
             WHERE id = '{$user_id}' AND deleted = 0
             LIMIT 1"
        ));
        $td_sip = trim($info['td_sip'] ?? '');
        $title  = $info['title'] ?? '';

        $agent_status = $isOnline ? 'Available' : 'Logged Out';

        // (1) users.agent_status: quyết định checkbox Busy + tự kết nối softphone khi tải trang
        $db->query(
            "UPDATE users
             SET agent_status = '" . $db->quote($agent_status) . "'
             WHERE id = '{$user_id}' AND deleted = 0"
        );

        // (2) ec_online_report của hôm nay: nguồn dữ liệu để auto-assign booking
        $row = $db->fetchByAssoc($db->query(
            "SELECT id, start_online
             FROM ec_online_report
             WHERE assigned_user_id = '{$user_id}'
                 AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) = '$today_vn'
                 AND deleted = 0
             LIMIT 1"
        ));

        if (!empty($row)) {
            if ($isOnline) {
                // start_online chỉ set 1 lần cho mốc online đầu tiên trong ngày
                $set_start = empty($row['start_online']) ? ", start_online = '$now_gmt'" : '';
                $db->query(
                    "UPDATE ec_online_report
                     SET status = " . self::STATUS_ONLINE . "
                         , last_online = '$now_gmt'
                         $set_start
                         , date_modified = NOW()
                     WHERE id = '{$row['id']}' AND deleted = 0"
                );
            } else {
                // Đăng xuất => Offline, trả booking đang giữ về hàng chờ để được giao lại
                $db->query(
                    "UPDATE ec_online_report
                     SET status = " . self::STATUS_OFFLINE . "
                         , booking_id = NULL
                         , start_assign = NULL
                         , last_online = '$now_gmt'
                         , date_modified = NOW()
                     WHERE id = '{$row['id']}' AND deleted = 0"
                );
            }
        } else if ($isOnline) {
            $online = new EC_Online_Report();
            $online->name             = trim(trim($user->last_name ?? '') . ' ' . trim($user->first_name ?? ''));
            $online->assigned_user_id = $user_id;
            $online->status           = self::STATUS_ONLINE;
            $online->title            = $title;
            $online->start_online     = $now_gmt;
            $online->last_online      = $now_gmt;
            $online->save();
        }

        // (3) Đồng bộ trạng thái agent trên tổng đài + ghi log online
        if ($td_sip !== '' && function_exists('agent_change_status')) {
            try {
                agent_change_status($td_sip, $agent_status);
            } catch (\Throwable $e) {
                $GLOBALS['log']->error('syncOnlineStatus: agent_change_status error - ' . $e->getMessage());
            }
        }
    }
}
