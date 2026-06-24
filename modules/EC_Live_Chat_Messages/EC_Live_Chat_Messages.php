<?php
class EC_Live_Chat_Messages extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Live_Chat_Messages';
    public $object_name = 'EC_Live_Chat_Messages';
    public $table_name = 'ec_live_chat_messages';
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

    // Custom fields (declared in vardefs.php)
    public $src;
    public $client_phone;
    public $client_url;
    public $client_user_agent;
    public $client_ip;
    public $sender_type;
    public $message_type;
    public $attachment_url;
    public $attachment_name;
    public $seen_at;
    public $contact_id;
    public $contact;
    public $booking_id;
    public $booking;

    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }
	
}