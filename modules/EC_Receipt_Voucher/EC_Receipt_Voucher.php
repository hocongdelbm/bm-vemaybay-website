<?php

class EC_Receipt_Voucher extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Receipt_Voucher';
    public $object_name = 'EC_Receipt_Voucher';
    public $table_name = 'ec_receipt_voucher';
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

    public $receipt_type;
    public $rv_notes;
    public $rv_number;
    public $amount;
    public $account_id_c;
    public $customer;
    public $guest_name;
    public $guest_phone;
    public $amount_type;
    public $guest_address;
    public $tknganhang;
	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    function save($check_notify = FALSE)
	{
		global $current_user;

		// Check booking is paid
		if (isset($this->booking_id) && !empty($this->booking_id)
			&& isset($this->rv_status) && $this->rv_status == '1'
			&& !myIsBookingPaid($this->booking_id)
		) {
			header('Location: index.php?module=' . $this->module_dir . '&action=Error&error_string=' . urlencode('Booking ' . $this->booking_name . ' chưa nhấn đã thanh toán.'));
			exit;
		}

		if (empty($this->name)) {
			$total_row = $this->db->getOne("SELECT COUNT(id) + 1 FROM ec_receipt_voucher");
			$this->name = 'PT-' . date('ymd') . '-' . $total_row;
		}

        // Ghi nhận ngày ghi sổ
		if (isset($_POST['ngayhachtoan']) && !empty($_POST['ngayhachtoan'])) {
			// $this->ngayhachtoan = date('Y-m-d H:i:s', strtotime($_POST['ngayhachtoan']) - 7 * 3600);
			$this->ngayhachtoan = date('Y-m-d H:i:s', strtotime($_POST['ngayhachtoan']));
		} 

        // Notes - not use?
		if (isset($_POST['ngaythutien']) && $_POST['ngaythutien'] != '0000-00-00 00:00:00') {
			$this->ngaythutien = date('Y-m-d H:i:s', strtotime($_POST['ngaythutien']) - 7 * 3600);
		}

		// nếu là trạng thái công nợ
		if($this->rv_status == 2) {
			$this->is_debt = 1;
		} else if($this->rv_status != 1) {
			$this->is_debt = 0;
		}

		parent::save($check_notify);

		if ($this->rv_status == '1') {
			myCreateWorkingProcess($this->module_dir, $this->id, $this->name, $this->description, $current_user->id, 'paid');
		}else if ($this->rv_status == '0') {
			myRemoveWorkingProcess($this->module_dir, $this->id);
		}

		// Begin save working process for delivery man
        if (isset($this->delivery_man_id) && !empty($this->delivery_man_id) && $this->fetched_row['delivery_man_id'] != $this->delivery_man_id) {
			myRemoveWorkingProcess($this->module_dir, $this->id, 'ticket_delivery');
			$work = new EC_Working_Process();
			$work->id = '';
			$work->name = $this->name;
			$work->description = trim($this->delivery_man);
			$work->parent_type = $this->module_dir;
			$work->parent_id = $this->id;
			$work->assigned_user_id = $this->delivery_man_id;
			$work->ticket_delivery = 1;
			$work->save();
		}
		else if (empty($this->delivery_man_id)) {
			myRemoveWorkingProcess($this->module_dir, $this->id, 'ticket_delivery');
		}
		// End save working process for delivery man

	}
	
}
