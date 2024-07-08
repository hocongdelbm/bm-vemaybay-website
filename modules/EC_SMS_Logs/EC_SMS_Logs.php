<?php
require_once('include/upload_file.php');
require_once('modules/EC_SMS_Logs/Zalo.php');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EC_SMS_Logs extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_SMS_Logs';
    public $object_name = 'EC_SMS_Logs';
    public $table_name = 'ec_sms_logs';
    public $importable = true;

	public $disable_row_level_security = true ; // to ensure that modules created and deployed under CE will continue to function under team security if the instance is upgraded to PRO

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

    public $type;
    public $message_type;
    public $send_from;
    public $send_to;
    public $send_date;
    public $file;
    public $data;
    public $content;
    public $is_scheduled;
    public $parent_id;
    public $parent_type;
    public $status;
    public $campaign_id;
	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    public function save($check_notify = FALSE) {
		if (empty($this->name)) {
			$this->name = 'SMS-' . date('ymd') . '-' . $this->count_record();
		}

        if($this->type == 'send_zalo_broadcast') {
            $Zalo = new Zalo();
            $post_id = isset($_POST['field-zalo-broadcast-post']) ? $_POST['field-zalo-broadcast-post'] : '';

            $this->data = json_encode([
                'post'      => $post_id,
                'gender'    => isset($_POST['field-zalo-broadcast-gender']) ? $_POST['field-zalo-broadcast-gender'] : '0',
                'ages'      => isset($_POST['field-zalo-broadcast-ages']) ? implode(",", $_POST['field-zalo-broadcast-ages']) : '',
                'locations' => isset($_POST['field-zalo-broadcast-locations']) ? implode(",", $_POST['field-zalo-broadcast-locations']) : '',
                'cities'    => isset($_POST['field-zalo-broadcast-cities']) ? implode(",", $_POST['field-zalo-broadcast-cities']) : '',
                'platform'  => isset($_POST['field-zalo-broadcast-platform']) ? implode(",", $_POST['field-zalo-broadcast-platform']) : '',
            ]);
            $this->content = $Zalo->get_link_post($post_id);
        }
        elseif($this->type == 'send_sms_list_static' || $this->type == 'send_sms_list_dynamic'){
            if(isset($_POST['file']) && empty($_POST['file'])){
                $this->data = $this->convert_file_to_json();
            }
        } 
        // else $this->data = "";
 
        if(!empty($this->content) && !empty($this->send_from)){
            parent::save($check_notify);
        } 
	}

    public function count_record() {
		$sql = 'SELECT COUNT(id)
			FROM ec_sms_logs
			WHERE DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"';
		return str_pad(($this->db->getOne($sql) + 1), 4, 0, STR_PAD_LEFT);
	}

    public function map_column_name_order($num) {
        $alphabet = array_merge(range('A', 'Z'));
        return isset($alphabet[$num]) ? $alphabet[$num] : '';
    }

    public function map_column_number_order($character) {
        $alphabet = array_merge(range('A', 'Z'));
        return array_search($character, $alphabet);
    }

    public function convert_file_to_json() {
        if($_FILES['file_file']['name'] != '') {
            $objReader = new Spreadsheet();
            $objReader = IOFactory::load('cache/upload/' . $this->id);
            $sheet     = $objReader->getActiveSheet();
    
            $array = array();
            for($row = 1; $row <= $sheet->getHighestRow(); $row++) {
                if($this->type == "send_sms_list_dynamic") {
                    $element = array();
                    for ($col = 0; $col <= $this->map_column_number_order($sheet->getHighestDataColumn($row)); $col++) {
                        array_push($element, $sheet->getCell($this->map_column_name_order($col) . $row)->getValue());
                    }
                    array_push($array, $element);
                }
                else if ($this->type == "send_sms_list_static") {
                    array_push($array, $sheet->getCell("A" . $row)->getValue());
                }
            }
    
            return json_encode($array);
        } else {
            header("Location: index.php?module=EC_SMS_Logs&action=Error&error_string=" . urlencode("Chưa có thông tin file import. Vui lòng import lại"));
            exit;
        }
    }
}
