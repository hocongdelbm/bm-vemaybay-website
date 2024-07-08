<?php

class EC_Completed_Bookings extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Completed_Bookings';
    public $object_name = 'EC_Completed_Bookings';
    public $table_name = 'ec_completed_bookings';
    public $importable = true;

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

    public $ec_flight_bookings_id_c;
    public $booking_name;
    public $completed_bk_type;
    public $bk_status;
    public $bk_mark;
    public $ticket_qty;
    public $bonus;
    public $init_called_time;
    public $completed_time;
    public $payment_time;

	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    function save($check_notify = FALSE) {
		// nếu có thông tin chia DS
		if(isset($_POST['for']) && $_POST['for'] == 'updateShareProfit') {
			$this->saveShareProfit();
		}

		parent::save();
	}

	function save2() {
		parent::save();
	}

	function saveShareProfit() {
		for($i = 0; $i < count($_POST['share_profit_userid']); $i++) {
			// phải chọn người giao cho thì mới lưu
			if(!empty($_POST['share_profit_userid'][$i])) {
				$com_bk = new EC_Completed_Bookings;
				$com_bk->id = $_POST['share_profit_id'][$i];
				if($_POST['share_profit_delete'][$i] == 0) {
					$com_bk->name = $_POST['share_profit_user'][$i];
					$com_bk->assigned_user_id = $_POST['share_profit_userid'][$i];
					$com_bk->deleted = $_POST['share_profit_delete'][$i];
					$com_bk->completed_bk_type = 'SHARE_PROFIT';
					$com_bk->bk_status = $_POST['bk_status'];
					$com_bk->ec_flight_bookings_id_c = $_POST['booking'];
					$com_bk->total_amount = str_replace(array(',', '.'), '', $_POST['share_profit_amt'][$i]);
					$com_bk->save2();
				} else {
					$com_bk->mark_deleted($_POST['share_profit_id'][$i]);
				}
			}
		}
		header("Location: index.php?module=EC_Flight_Bookings&action=DetailView&record=" . $_POST['booking']);
		exit;
	}
	
	
}
