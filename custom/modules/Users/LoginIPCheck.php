<?php

/**
 * Hook: before_login
 * Chặn đăng nhập nếu user có role Telesale + ip_restriction_enabled = 1
 * mà IP hiện tại không nằm trong danh sách allowed_ips của bất kỳ EC_Location nào.
 */
class LoginIPCheck
{
    const TELESALE_ROLE_ID = '34beb2a2-5ee7-f001-2496-68ca264d1d3f';

    public function checkIPRestriction($bean, $event, $arguments = array())
    {
        $username = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
        if (empty($username)) {
            return;
        }

        global $db;

        $userSql = sprintf(
            "SELECT id, ip_restriction_enabled FROM users WHERE user_name = %s AND deleted = 0 LIMIT 1",
            $db->quoted($username)
        );
        $userRow = $db->fetchOne($userSql);

        if (empty($userRow) || empty($userRow['ip_restriction_enabled'])) {
            return;
        }

        $userId = $userRow['id'];
        $hasRole = (bool)isTelesaleUser($userId);
        if (!$hasRole) {
            return;
        }

        $clientIp = $this->getClientIp();
        if ($this->isIPAllowed($clientIp)) {
            return;
        }

        $GLOBALS['log']->fatal("LoginIPCheck: blocked login for user [{$username}] from IP [{$clientIp}]");
        $_SESSION['login_error'] = 'Vui lòng đăng nhập từ mạng nội bộ công ty.';
        SugarApplication::redirect('index.php?module=Users&action=Login');
        exit();
    }

    private function getClientIp()
    {
        $raw = query_client_ip();
        if (empty($raw)) {
            return '';
        }

        $ip = trim(explode(',', $raw)[0]);
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }

    private function isIPAllowed($clientIp)
    {
        if (empty($clientIp)) {
            return false;
        }

        global $db;

        $sql = "SELECT allowed_ips FROM ec_location WHERE deleted = 0 AND is_display = 0 AND allowed_ips IS NOT NULL AND allowed_ips != ''";
        $result = $db->query($sql);

        while ($row = $db->fetchByAssoc($result)) {
            $entries = preg_split('/[\r\n,]+/', $row['allowed_ips']);
            foreach ($entries as $entry) {
                if (trim($entry) === $clientIp) {
                    return true;
                }
            }
        }
        return false;
    }
}
