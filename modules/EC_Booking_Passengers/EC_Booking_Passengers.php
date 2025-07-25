<?php
class EC_Booking_Passengers extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Booking_Passengers';
    public $object_name = 'EC_Booking_Passengers';
    public $table_name = 'ec_booking_passengers';
    public $importable = false;

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

    public $salutation;
    public $birthday;
    public $booking_id;
    public $booking;
    public $parent_detail_id;
    public $type;
    public $direction;
    public $pnr_outbound;
    public $pnr_inbound;
    public $supplier_id;
    public $supplier_inbound_id;
    public $add_type;
    public $is_active;
    public $cic;
    public $passport_number;
    public $eticket_outbound;
    public $eticket_inbound;
    public $go_with;

    // Thông tin giá mua hành lý từng lượt (Thông tin lưu từ website)
    public $luggage_price;
    public $luggage_price_inbound;
    public $luggage_index_outbound;
    public $luggage_index_inbound;

    // Giá mua hành lý từng lượt (Chưa VAT)
    public $luggage_purchase_no_vat;
    public $luggage_purchase_inbound_no_vat;
    // VAT giá mua hành lý từng lượt
    public $vat_luggage_purchase;
    public $vat_luggage_purchase_inbound;
    // Tổng giá mua hành lý từng lượt
    public $luggage_purchase;
    public $luggage_purchase_inbound;
    // Số vé hành lý đã mua từng lượt (VN, QH)
    public $eluggage_outbound;
    public $eluggage_inbound;

    public function bean_implements($interface) {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }
}
