<?php
require_once "custom/include/helpers/api/APIZaloOA.php";

class EC_Zalo extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Zalo';
    public $object_name = 'EC_Zalo';
    public $table_name = 'ec_zalo';
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

    public $oa_alias;
    public $oa_type;
    public $cate_name;
    public $is_verified;
    public $num_follower;
    public $avatar;
    public $cover;
    public $package_name;
    public $package_valid_through_date;
    public $package_auto_renew_date;
    public $linked_zca;
    public $api_oauth_info;
    public $quota_info;
    public $secret_key;

    // public $limit_chat_box = 15;
    // public $limit_message = 10;
    // public $image_file = [
    //     'excel' => 'modules/EC_Zalo/images/private/files/file_excel.jpg',
    //     'word' => 'modules/EC_Zalo/images/private/files/file_word.jpg',
    //     'powerpoint' => 'modules/EC_Zalo/images/private/files/file_powerpoint.jpg',
    //     'pdf' => 'modules/EC_Zalo/images/private/files/file_pdf.jpg',
    //     'txt' => 'modules/EC_Zalo/images/private/files/file_txt.jpg',
    //     'html' => 'modules/EC_Zalo/images/private/files/file_html.jpg',
    //     'xml' => 'modules/EC_Zalo/images/private/files/file_xml.jpg',
    //     'zip' => 'modules/EC_Zalo/images/private/files/file_zip.jpg',
    //     'rar' => 'modules/EC_Zalo/images/private/files/file_rar.jpg',
    //     'image' => 'modules/EC_Zalo/images/private/files/file_image.jpg',
    //     'default' => 'modules/EC_Zalo/images/private/files/file_default.jpg'
    // ];
	
    public function bean_implements($interface) {
        switch($interface)
        {
            case 'ACL':
                return true;
        }
        return false;
    }
}
