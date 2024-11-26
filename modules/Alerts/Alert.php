<?php
class Alert extends Basic
{
    public $new_schema = true;
    public $module_dir = 'Alerts';
    public $object_name = 'Alert';
    public $table_name = 'alerts';
    public $importable = false;
    public $disable_row_level_security = true; // to ensure that modules created and deployed under CE will continue to function under team security if the instance is upgraded to PRO
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
    public $is_read;

    public $parent_type;
    public $parent_id;
    public $filename;
    public $alert_photo;
    public $parent_alert_id;
    public $priority;
    public $viewed_at;
    public $url_redirect;
    public $type;
    public $target_module;

    /**
     * @var string
     */
    public $reminder_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    function save($check_notify = FALSE)
    {
        global $current_user, $sugar_config, $app_list_strings;

        $list_employee_id   = $_POST['list_employee_id'] ?? [];
        $dataAlert          = $this->prepareAlertData($list_employee_id[0]);

        parent::save($check_notify);

        if (in_array('all', $list_employee_id)) {
            $this->createAlertForAll($dataAlert);
        } elseif (count($list_employee_id) > 1 && !in_array('all', $list_employee_id)) {
            $this->createMultipleAlert(array_slice($list_employee_id, 1), $dataAlert);
        }
    }

    function prepareAlertData($user_id)
    {
        // Gán các giá trị mặc định nếu chưa có
        $this->assigned_user_id = $this->assigned_user_id ?: $user_id;
        $this->target_module = $this->target_module ?: 'Alerts';
        $this->type = $this->type ?: 'readonly';
        $this->url_redirect = $this->url_redirect ?: "index.php?module=" . str_replace("'", '', $this->target_module) . "&action=DetailView&record=$this->id";
        $this->filename = $this->filename ?: $_POST['filename'];
        $this->alert_photo = $this->alert_photo ?: $_POST['alert_photo'];
        $this->parent_alert_id = $this->parent_alert_id ?: $this->id;

        return [
            'name' => $this->name,
            'description' => $this->description,
            'assigned_user_id' => $this->assigned_user_id,
            'is_read' => $this->is_read,
            'target_module' => $this->target_module,
            'type' => $this->type,
            'url_redirect' => $this->url_redirect,
            'reminder_id' => $this->reminder_id,
            'alert_photo' => $this->alert_photo,
            'filename' => $this->filename,
            'priority' => $this->priority,
            'parent_type' => $this->parent_type,
            'parent_id' => $this->parent_id,
            'parent_alert_id' => $this->parent_alert_id,
        ];
    }

    function createMultipleAlert($user_ids, $alertData)
    {
        if (empty($user_ids)) {
            return;
        }

        $values = [];
        if (is_array($user_ids) && count($user_ids) > 0) {
            foreach ($user_ids as $user_id) {
                $id = create_guid();
                $alertData['assigned_user_id'] = $user_id;
                $parent_type    = $alertData['parent_type'] ?? "";
                $parent_id      = $alertData['parent_id'] ?? "";
                $filename       = $alertData['filename'] ?? "";
                $alert_photo    = $alertData['alert_photo'] ?? "";
                $url_redirect   = (!empty($parent_type) && !empty($parent_id)) ? $alertData['url_redirect'] : "index.php?module=" . str_replace("'", '', $alertData['target_module']) . "&action=DetailView&record=$id";

                // Thêm thông tin alert vào mảng
                $values[] = "('" . $id . "', '" . $this->db->quote($alertData['name']) . "', '"
                    . $this->db->quote($alertData['description']) . "', '"
                    . $this->db->quote($url_redirect) . "', '"
                    . $this->db->quote($alertData['target_module']) . "', '"
                    . $this->db->quote($user_id) . "', '"
                    . $this->db->quote($alertData['type']) . "', '"
                    . $this->db->quote($filename) . "', '"
                    . $this->db->quote($alertData['priority']) . "', '"
                    . $this->db->quote($alert_photo) . "', 0, '"
                    . gmdate('Y-m-d H:i:s') . "', '"
                    . gmdate('Y-m-d H:i:s') . "', '"
                    . $this->db->quote($GLOBALS['current_user']->id) . "', '"
                    . $this->db->quote($GLOBALS['current_user']->id) . "', '"
                    . $this->db->quote($parent_type) . "', '"
                    . $this->db->quote($parent_id) . "', '"
                    . $this->db->quote($alertData['parent_alert_id']) . "')";
            }
        }

        // Bulk insert alert
        if (!empty($values)) {
            $sql = "INSERT INTO alerts 
            (id, name, description, url_redirect, target_module, assigned_user_id, type, filename, priority, alert_photo, is_read, date_entered, date_modified, modified_user_id, created_by, parent_type, parent_id, parent_alert_id) 
                VALUES " . implode(", ", $values);
        }

        try {
            $this->db->query($sql);
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("Failed to create multiple alerts: " . $e->getMessage() . " - " . $sql);
        }
    }

    function createAlertForAll($alertData)
    {
        $user_ids = [];
        $sql = 'SELECT id, CONCAT(last_name, " ", IFNULL(first_name, "")) AS full_name 
                    FROM users 
                    WHERE deleted = 0 
                    AND status = "Active" 
                    AND title NOT IN ("Bot")';

        try {
            $result = $this->db->query($sql);
            $user_ids = [];

            while ($row = $this->db->fetchByAssoc($result)) {
                if ($row['id'] !== $alertData['assigned_user_id']) {
                    $user_ids[] = $row['id'];
                }
            }

            $this->createMultipleAlert($user_ids, $alertData);
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("Failed to retrieve users for alert creation: " . $e->getMessage() . " - " . $sql);
        }
    }

    public function autoCreateAlert($module, $list_user, $alertData)
    {
        $alert = new Alert();
        $alert->name                = $alertData['name'] ?? '';
        $alert->description         = $alertData['description'] ?? '';
        $alert->target_module       = $module;
        $alert->parent_type         = $alertData['parent_type'];
        $alert->parent_id           = $alertData['parent_id'];
        $alert->type                = $alertData['type'] ?? 'info';
        $alert->url_redirect        = $alertData['url_redirect'] ?? '';
        $alert->priority            = $alertData['priority'] ?? 'low';
        $alert->assigned_user_id    = $list_user[0] ?? $GLOBALS['current_user']->id;
        $alert->save();

        if (!empty($alert->id)) {
            // Update parent_alert_id cho alert đầu tiên
            $sql1 = 'UPDATE alerts 
                    SET parent_alert_id = "' . $alert->id . '"
                    WHERE deleted = 0 
                    AND id = "' . $alert->id . '"';
            $this->db->query($sql1);

            $alertData['parent_alert_id']   = $alert->id;
            $alertData['target_module']     = $module;

            if (count($list_user) > 1) {
                $alert->createMultipleAlert(array_slice($list_user, 1), $alertData);
            }

            return $alert->id;
        }

        return null; // Lỗi khi lưu alert
    }
}
