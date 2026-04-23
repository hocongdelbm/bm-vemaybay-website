<?php
class EC_Zalo_Messages extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Zalo_Messages';
    public $object_name = 'EC_Zalo_Messages';
    public $table_name = 'ec_zalo_messages';
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

    public $message_id;
    public $src;
    public $from_id;
    public $to_id;
    public $timestamp;
    public $type;
    public $sub_type;
    public $thumbnail;
    public $url;
    public $attached_description;
    public $latitude;
    public $longitude;
    public $quote_message_id;
    public $template_id;
    public $cost;
    public $data;
    public $response;
    public $booking_id;

    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    public function save($check_notify = FALSE) {
		return parent::save($check_notify);
	}
}