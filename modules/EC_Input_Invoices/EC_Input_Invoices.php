<?php
class EC_Input_Invoices extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Input_Invoices';
    public $object_name = 'EC_Input_Invoices';
    public $table_name = 'ec_input_invoices';
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

    public $qty;
    public $invoice_number;
    public $invoice_date;
    public $invoice_serial;
    public $airline;
    public $departure;
    public $arrival;
    public $supplier;
    public $company_unit;
    public $accounting_date;
    public $itinerary;
    public $is_other_fee;
    public $status;
    public $booking_id;
    public $booking;
    public $order_by_no;
    public $cost;
    public $cost_no_vat;
    public $vat;
    public $authorized_fee;
    public $ticket_code;
    public $total;
    public $ticket_type;

    public function bean_implements($interface) {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    public function save($check_notify = FALSE) {
        global $db, $current_user;
        if (isset($_POST['im_ticket_code'])) {

            $sql = 'SELECT id
                FROM ec_input_invoices 
                WHERE name = "' . $_POST['im_ticket_code'] . '"
                    AND supplier = "' . $_POST['im_supplier'] . '" AND status = 1
                    AND invoice_number = "' . $_POST['im_invoice_number'] . '"
                    AND invoice_serial = "' . $_POST['im_invoice_serial'] . '"
                    AND deleted = 0';

            $res = $this->db->query($sql);
            $row = $this->db->fetchByAssoc($res);
            
            // if ($this->db->getRowCount($res) == 0) {
            if ($this->db->countRows($res) == 0) {
                if (empty($_POST['record'])) {
                    // Lấy số thứ tự
                    $sql = 'SELECT COUNT(id) 
						FROM ec_input_invoices 
						WHERE invoice_number = "' . $_POST['im_invoice_number'] . '"
                            AND invoice_serial = "' . $_POST['im_invoice_serial'] . '"
                            AND supplier = "' . $_POST['im_supplier'] . '"
                            AND status = 1
                            AND deleted = 0';
                    $order_by_no = $this->db->getOne($sql);
                    $this->order_by_no = $order_by_no;
                }
                $this->name                 = $_POST['im_ticket_code'];
                $this->assigned_user_id     = $current_user->id;
                $this->qty                  = $_POST['im_qty'];
                $this->invoice_number       = $_POST['im_invoice_number'];
                $this->invoice_date         = $_POST['im_invoice_date'];
                $this->invoice_serial       = $_POST['im_invoice_serial'];
                $this->accounting_date      = $_POST['im_accounting_date'];
                $this->supplier             = $_POST['im_supplier'];
                $this->company_unit         = $_POST['im_company_unit'];
                $this->ticket_type          = $_POST['im_ticket_type'] ?? '';
                $this->status               = 1;
                $this->itinerary            = $_POST['im_iti'];
                $this->booking_id           = $_POST['im_booking_id'];
                $this->cost_no_vat          = unformat_number($_POST['im_cost']);
                $this->vat                  = unformat_number($_POST['im_vat']);
                $this->cost                 = unformat_number($_POST['im_cost_vat']);
                $this->authorized_fee       = unformat_number($_POST['im_authorized']);
                $this->total                = unformat_number($_POST['im_total']);
                parent::save($check_notify);
            } else {
                // $this->id                   = $row['id'];
                $this->id                   = $_POST['record'];
                $this->name                 = $_POST['im_ticket_code'];
                $this->assigned_user_id     = $current_user->id;
                $this->qty                  = $_POST['im_qty'];
                $this->invoice_number       = $_POST['im_invoice_number'];
                $this->invoice_date         = $_POST['im_invoice_date'];
                $this->invoice_serial       = $_POST['im_invoice_serial'];
                $this->accounting_date      = $_POST['im_accounting_date'];
                $this->supplier             = $_POST['im_supplier'];
                $this->company_unit         = $_POST['im_company_unit'];
                $this->ticket_type          = $_POST['im_ticket_type'] ?? '';
                $this->status               = 1;
                $this->itinerary            = $_POST['im_iti'];
                $this->booking_id           = $_POST['im_booking_id'];
                $this->cost_no_vat          = unformat_number($_POST['im_cost']);
                $this->vat                  = unformat_number($_POST['im_vat']);
                $this->cost                 = unformat_number($_POST['im_cost_vat']);
                $this->authorized_fee       = unformat_number($_POST['im_authorized']);
                $this->total                = unformat_number($_POST['im_total']);
                parent::save($check_notify);
            }

            // Cập nhật note - kpi
            if(!empty($_POST['im_booking_id'])) {
                $booking_id = $_POST['im_booking_id'];

                // Cập nhật đã lấy hoá đơn đầu vào
                $db->query("UPDATE ec_flight_bookings SET is_invoice_input_export = 1 WHERE id = '{$booking_id}'");
                $date_created = date_sub(date_create(date('Y-m-d H:i:s')), date_interval_create_from_date_string("7 hours"));
                $db->query('INSERT INTO ec_flight_bookings_audit(id, parent_id, date_created, created_by, field_name, data_type, before_value_string, after_value_string)
                    VALUES (uuid(), "'.$booking_id.'", "'.date('Y-m-d H:i:s', strtotime(date_format($date_created, "Y-m-d"))). '", "'.$current_user->id.'", "is_invoice_input_export", "boolean", 0, 1)');
    
                // Cập nhật kpi
                // myRemoveWorkingProcess('EC_Flight_Bookings', $_POST['im_booking_id'], 'invoice_input_issued');
                myCreateWorkingProcess('EC_Flight_Bookings', $_POST['im_booking_id'], $_POST['im_booking'], 'Import từ hoá đơn của hãng (Manual)', $current_user->id, 'invoice_input_issued');
    
                $note = new Note;
                $note->name = 'Import HD đầu vào';
                $note->parent_type = 'EC_Flight_Bookings';
                $note->parent_id = $booking_id;
                $note->description = 'Đã lấy hóa đơn đầu vào số: ' . $_POST['im_invoice_number'] . '; số vé: ' . $_POST['im_ticket_code'];
                $note->save();
            }
        }
    }

    public function save2($check_notify = FALSE) {
        return parent::save($check_notify);
    }

    /**
     * Get list available ticket of booking
     * 
     * @param string $bookingId
     * @return array
     */
    public function getListAvailableTickets($bookingId) {
        if(!is_string($bookingId) || empty($bookingId)) return [];

        $sql = "SELECT i.id
                ,i.name AS ticket_number
                ,i.ticket_code
                ,i.ticket_type
                ,i.supplier
                ,i.itinerary
                ,IFNULL(i.cost_no_vat, 0) AS cost_no_vat
                ,IFNULL(i.vat, 0) AS vat
                ,i.authorized_fee
                ,i.total
                ,i.qty
                ,(
                    SELECT IFNULL(SUM(soluong), 0)
                    FROM ec_chitiethoadon
                    WHERE ticket_number_id = i.id AND deleted = 0
                ) AS used_qty
            FROM ec_input_invoices i
            WHERE i.booking_id = '{$bookingId}'
                AND i.status = '1'
                AND i.company_unit = 'MHV'
                AND i.deleted = 0";
                
        $listTicket = [];

        $res = $this->db->query($sql);
        while ($row = $this->db->fetchByAssoc($res)) {
            if($row['used_qty'] == 0 && isset($row['ticket_number']) && !empty($row['ticket_number'])) {
                $listTicket[$row['id']] = $row;
            }
        }

        return $listTicket;
    }
}
