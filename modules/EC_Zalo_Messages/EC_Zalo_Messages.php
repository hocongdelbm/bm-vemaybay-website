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
    public $timestamp_dt;

    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    /**
     * Populate non-db fields after retrieving from DB
     */
    public function fill_in_additional_detail_fields() {
        parent::fill_in_additional_detail_fields();
        $this->populateTimestampDt();
    }

    public function retrieve($id = -1, $encode = true, $deleted = true) {
        $ret = parent::retrieve($id, $encode, $deleted);
        $this->populateTimestampDt();
        return $ret;
    }

    protected function populateTimestampDt() {
        if (!empty($this->timestamp)) {
            // timestamp stored in milliseconds; convert to seconds for PHP
            $ts_seconds = intval($this->timestamp / 1000);
            $this->timestamp_dt = gmdate('Y-m-d H:i:s', $ts_seconds);
        } else {
            $this->timestamp_dt = '';
        }
    }

    public function save($check_notify = FALSE) {
        // If user provided timestamp_dt (datetime), convert to milliseconds timestamp
        if (!empty($this->timestamp_dt)) {
            // expected format: 'Y-m-d H:i:s' — try strtotime
            $ts = strtotime($this->timestamp_dt);
            if ($ts !== false) {
                $this->timestamp = (int) ($ts * 1000);
            }
        }
        // If message_id is empty, generate a unique one before saving
        if (empty($this->message_id)) {
            // ensure create_guid is available
            if (!function_exists('create_guid')) {
                require_once('include/utils.php');
            }
            global $db;
            $tries = 0;
            do {
                // create a 20-char hex-like id using sha1 and take first 20 chars
                $candidate = substr(sha1(create_guid()), 0, 20);
                $existing = $db->getOne("SELECT id FROM ec_zalo_messages WHERE message_id = '$candidate'");
                $tries++;
            } while (!empty($existing) && $tries < 10);

            if (!empty($existing)) {
                // fallback: regenerate a 20-char value using uniqid + random data
                $candidate = substr(sha1(uniqid(mt_rand(), true) . microtime(true)), 0, 20);
            }
            $this->message_id = $candidate;
        }

        return parent::save($check_notify);
	}
}