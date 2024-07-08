<?php

class EC_LeaveAbsences extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_LeaveAbsences';
    public $object_name = 'EC_LeaveAbsences';
    public $table_name = 'ec_leaveabsences';
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
    public $SecurityGroups;

    public $reason;
    public $from_date;
    public $to_date;
    public $absence_days;
    public $working_hour;
    public $ec_leaveabsencetypes_id_c;
    public $absence_type;
    public $status;

    public $part_date;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    public function save($check_notify = FALSE)
    {

        global $current_user;

        if (empty($this->name)) {
            if (empty($this->assigned_user_name)) $name = $current_user->user_name;
            else $name = $this->assigned_user_name;
            if (empty($this->assigned_user_id)) $user_id = $current_user->id;
            else $user_id = $this->assigned_user_id;
            $this->name = $name . '-' . preg_replace('/0/', '', date('Y'), 1) . date('m') . ($this->countVoucher($user_id) + 1);
        }

        // ngày đăng ký nửa buổi
        if (strtotime($_POST['part_date_chosen']) != false) {
            $this->part_date = $_POST['part_date_chosen'];
        }

        parent::save();
    }

    public function countVoucher($user_id)
    {
        $sql = 'SELECT COUNT(*) FROM ec_leaveabsences 
				WHERE assigned_user_id = "' . $user_id . '"';
        return $this->db->getOne($sql);
    }

}
