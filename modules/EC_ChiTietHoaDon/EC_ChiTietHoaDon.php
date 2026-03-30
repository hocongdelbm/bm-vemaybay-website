<?php

class EC_ChiTietHoaDon extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_ChiTietHoaDon';
    public $object_name = 'EC_ChiTietHoaDon';
    public $table_name = 'ec_chitiethoadon';
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

    public $parent_name;
    public $parent_type;
    public $parent_id;
    public $hanhtrinh;
    public $soluong;
    public $dongia;
    public $currency_id;
    public $thanhtien;
    public $giamua;
    public $tienthue;
    public $phithuho;
    public $phisanbay;
    public $phikhac;
    public $phidv;
    public $order_by_no;
    public $ticket_number_id;
    public $booking_id;
    public $booking;
    public $thuesuat;
    public $mahang;
    public $receipt_voucher_id;
    public $receipt_voucher;
	
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
