<?php
require_once('include/upload_file.php');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EC_Messages extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Messages';
    public $object_name = 'EC_Messages';
    public $table_name = 'ec_messages';
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

    public $type;
    public $category;
    public $send_from;
    public $send_to;
    public $send_time;
    public $file;
    public $data;
    public $response;
    public $content;
    public $is_scheduled;
    public $parent_id;
    public $parent_type;
    public $status;
    public $cost;
	
    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }
	
    public function save($check_notify = FALSE) {
		if (empty($this->name)) {
			$this->name = 'MESS-' . date('ymd') . '-' . $this->count_record();
        }

        if($this->type == 'sms_campaign_static' || $this->type == 'sms_campaign_dynamic'){
            if(isset($_POST['file']) && empty($_POST['file'])) {
                $this->data = $this->convert_file_to_json();
            }
        }

 
        if(!empty($this->content) && !empty($this->send_from)){
            parent::save($check_notify);
        } 
	}

    public function count_record() {
		$sql = 'SELECT COUNT(id)
			FROM ec_messages
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
                if($this->type == "sms_campaign_dynamic") {
                    $element = array();
                    for ($col = 0; $col <= $this->map_column_number_order($sheet->getHighestDataColumn($row)); $col++) {
                        array_push($element, $sheet->getCell($this->map_column_name_order($col) . $row)->getValue());
                    }
                    array_push($array, $element);
                }
                else if ($this->type == "sms_campaign_static") {
                    if($sheet->getCell("A$row")->getValue()) array_push($array, $sheet->getCell("A$row")->getValue());
                }
            }
    
            return json_encode($array);
        } else {
            header("Location: index.php?module=EC_Messages&action=Error&error_string=" . urlencode("Chưa có thông tin file. Vui lòng import lại"));
            exit;
        }
    }

    public function get_list_view_data($filter_fields = array()) {
        $temp_array = parent::get_list_view_data();

        $temp_array['CONTENT'] = '<p style="width:400px">'.$this->content.'</p>';

        return $temp_array;
    }
}
