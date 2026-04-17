<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class ViewTelesaleipmgr extends SugarView
{
    const TELESALE_ROLE_ID = '34beb2a2-5ee7-f001-2496-68ca264d1d3f';

    public function init($bean = null, $view_object_map = array())
    {
        parent::init($bean, $view_object_map);

        if (!empty($_POST['ajax_action'])) {
            $this->options['show_header']    = false;
            $this->options['show_footer']    = false;
            $this->options['show_subpanels'] = false;
            $this->options['show_search']    = false;
        }
    }

    public function display()
    {
        global $current_user;

        if (!is_admin($current_user)) {
            SugarApplication::redirect('index.php?module=EC_Flight_Bookings&action=Error&error_string=' . urlencode('Bạn không được quyền truy cập vào mục này'));
            exit();
        }

        // AJAX request — init() đã tắt HTML header nên output sạch
        if (!empty($_POST['ajax_action'])) {
            if (ob_get_level()) ob_clean();
            $this->handleAjax(trim($_POST['ajax_action']));
            exit();
        }

        $smarty = new Sugar_Smarty();
        $smarty->assign('LOCATION_LIST', $this->getLocationList());
        $smarty->assign('TELESALE_USERS', $this->getTelesaleUsers());
        $smarty->display('modules/EC_Flight_Bookings/tpls/view_telesaleipmgr.tpl');
    }

    private function handleAjax($action)
    {
        global $db;

        if ($action === 'save_location_ips') {
            $locationId = isset($_POST['location_id']) ? trim($_POST['location_id']) : '';
            $allowedIps = isset($_POST['allowed_ips']) ? trim($_POST['allowed_ips']) : '';

            if (empty($locationId)) {
                echo json_encode(array('success' => false, 'message' => 'Thiếu location_id'));
                return;
            }

            $sql = sprintf(
                "UPDATE ec_location SET allowed_ips = %s, date_modified = NOW() WHERE id = %s AND deleted = 0",
                $db->quoted($allowedIps),
                $db->quoted($locationId)
            );
            $db->query($sql);
            echo json_encode(array('success' => true, 'message' => 'Đã lưu danh sách IP'));
            return;
        }

        if ($action === 'toggle_user_restriction') {
            $userId  = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';
            $enabled = isset($_POST['enabled']) ? (int) $_POST['enabled'] : 0;

            if (empty($userId)) {
                echo json_encode(array('success' => false, 'message' => 'Thiếu user_id'));
                return;
            }

            $sql = sprintf(
                "UPDATE users SET ip_restriction_enabled = %d, date_modified = NOW() WHERE id = %s AND deleted = 0",
                $enabled ? 1 : 0,
                $db->quoted($userId)
            );

            $db->query($sql);
            echo json_encode(array('success' => true, 'enabled' => $enabled ? 1 : 0));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Unknown action'));
        return;
    }

    private function getLocationList()
    {
        global $db;

        $sql = "SELECT id, name, allowed_ips FROM ec_location WHERE deleted = 0 AND is_display = 0 ORDER BY name ASC";
        $result = $db->query($sql);
        $list = array();
       
        while ($row = $db->fetchByAssoc($result)) {
            $list[] = $row;
        }

        return $list;
    }

    private function getTelesaleUsers()
    {
        global $db;
        
        $sql = sprintf(
            "SELECT u.id, u.user_name, CONCAT_WS(' ', u.last_name, u.first_name) AS full_name,
                    COALESCE(u.ip_restriction_enabled, 0) AS ip_restriction_enabled
             FROM users u
             INNER JOIN acl_roles_users aru ON aru.user_id = u.id AND aru.deleted = 0
             WHERE aru.role_id = %s AND aru.deleted = 0
               AND u.deleted = 0 AND u.status = 'Active'
             ORDER BY u.last_name",
            $db->quoted(self::TELESALE_ROLE_ID)
        );
        $result = $db->query($sql);
        $list = array();

        while ($row = $db->fetchByAssoc($result)) {
            $list[] = $row;
        }

        return $list;
    }
}
