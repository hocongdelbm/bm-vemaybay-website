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
    PUBLIC $sub_type;
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

    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    public function save($check_notify = FALSE) {
		parent::save($check_notify);
	}
    
    /**
     * Mapping sub type to event name
     * 
     * @param string $event_name
     * @return string
     */
    public function map_sub_type($event_name) {
        $arr = [
            'user_send_text'            => 'text',
            'user_send_image'           => 'image',
            'user_send_gif'             => 'gif',
            'user_send_sticker'         => 'sticker',
            'user_send_file'            => 'file',
            'user_send_audio'           => 'audio',
            'user_send_video'           => 'video',
            'user_send_location'        => 'location',
            'user_send_link'            => 'link',
            'user_send_business_card'   => 'business_card',
            'user_feedback'             => 'feedback',
            'user_submit_info'          => 'submit_info',

            'oa_send_text'      => 'text',
            'oa_send_image'     => 'image',
            'oa_send_gif'       => 'gif',
            'oa_send_sticker'   => 'sticker',
            'oa_send_file'      => 'file',
            'oa_send_list'      => 'links', // Request user info here
            'oa_send_template'  => 'template',
        ];

        return isset($arr[$event_name]) ? $arr[$event_name] : 'other';
    }
}