<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// User is used to store customer information.
class UserSignature extends SugarBean
{
    public $id;
    public $date_entered;
    public $date_modified;
    public $deleted;
    public $user_id;
    public $name;
    public $signature;
    public $table_name = 'users_signatures';
    public $module_dir = 'Users';
    public $object_name = 'UserSignature';
    public $disable_custom_fields = true;

    public function __construct()
    {
        //Ensure the vardefs get loaded.
        global $dictionary;
        if (file_exists('custom/metadata/users_signaturesMetaData.php')) {
            require_once('custom/metadata/users_signaturesMetaData.php');
        } else {
            require_once('metadata/users_signaturesMetaData.php');
        }


        parent::__construct();
    }



    /**
     * returns the bean name - overrides SugarBean's
     */
    public function get_summary_text()
    {
        return $this->name;
    }

    /**
     * Override's SugarBean's
     */
    public function create_export_query($order_by, $where, $show_deleted = 0)
    {
        return $this->create_new_list_query($order_by, $where, array(), array(), $show_deleted);
    }

    /**
     * Override's SugarBean's
     */
    public function get_list_view_data()
    {
        global $mod_strings;
        global $app_list_strings;
        $temp_array = $this->get_list_view_array();
        $temp_array['MAILBOX_TYPE_NAME'] = $app_list_strings['dom_mailbox_type'][$this->mailbox_type];
        return $temp_array;
    }

    /**
     * Override's SugarBean's
     */
    public function fill_in_additional_list_fields()
    {
        $this->fill_in_additional_detail_fields();
    }

    /**
     * Override's SugarBean's
     */
    public function fill_in_additional_detail_fields()
    {
    }
} // end class definition
