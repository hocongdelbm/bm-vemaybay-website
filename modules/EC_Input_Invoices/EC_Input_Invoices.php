<?php
class EC_Input_Invoices extends Basic {
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
    public $vat_per;
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
        global $current_user;

        // Using manual import/update
        if (isset($_POST['im_ticket_code']) && !empty($_POST['im_ticket_code'])) {
            $sql = "SELECT id
                FROM ec_input_invoices 
                WHERE name = '{$_POST['im_ticket_code']}'
                    AND ticket_type = '{$_POST['im_ticket_type']}'
                    AND supplier = '{$_POST['im_supplier']}'
                    AND invoice_number = '{$_POST['im_invoice_number']}'
                    AND invoice_serial = '{$_POST['im_invoice_serial']}'
                    AND status = 1
                    AND deleted = 0";

            $res = $this->db->query($sql);

            // Create
            if ($this->db->countRows($res) == 0) {
                if (empty($_POST['record'])) {
                    // Lấy số thứ tự
                    $sql = "SELECT COUNT(id) 
                        FROM ec_input_invoices
                        WHERE invoice_number = '{$_POST['im_invoice_number']}'
                            AND invoice_serial = '{$_POST['im_invoice_serial']}'
                            AND supplier = '{$_POST['im_supplier']}'
                            AND status = 1
                            AND deleted = 0";
                    $order_by_no = $this->db->getOne($sql);
                    $this->order_by_no = $order_by_no;
                }
                $this->name                 = $_POST['im_ticket_code'];
                $this->qty                  = $_POST['im_qty'];
                $this->invoice_number       = $_POST['im_invoice_number'];
                $this->invoice_date         = $_POST['im_invoice_date'];
                $this->invoice_serial       = $_POST['im_invoice_serial'];
                $this->accounting_date      = $_POST['im_accounting_date'];
                $this->supplier             = $_POST['im_supplier'];
                $this->company_unit         = $_POST['im_company_unit'];
                $this->ticket_type          = $_POST['im_ticket_type'] ?? '';
                $this->itinerary            = $_POST['im_iti'];
                $this->booking_id           = $_POST['im_booking_id'];
                $this->status               = 1;
                $this->cost_no_vat          = unformat_number($_POST['im_cost']);
                $this->vat_per              = $_POST['im_vat_percent'] ?? 0.08;
                $this->vat                  = unformat_number($_POST['im_vat']);
                $this->cost                 = unformat_number($_POST['im_cost_vat']);
                $this->authorized_fee       = unformat_number($_POST['im_authorized'] ?? 0);
                $this->total                = unformat_number($_POST['im_total']);
                $this->assigned_user_id     = $current_user->id;

                // Tách phí xuất vé thành dòng riêng vì khác thuế suất (Quốc tế)
                $ticketing_fee = unformat_number($_POST['im_ticketing_fee'] ?? 0);
                if($ticketing_fee > 0) {
                    $beanInInv = new EC_Input_Invoices();
                    $beanInInv->id = '';
                    $beanInInv->name            = $_POST['im_ticket_code'];
                    $beanInInv->qty             = 1;
                    $beanInInv->invoice_number  = $_POST['im_invoice_number'];
                    $beanInInv->invoice_date    = $_POST['im_invoice_date'];
                    $beanInInv->invoice_serial  = $_POST['im_invoice_serial'];
                    $beanInInv->accounting_date = $_POST['im_accounting_date'];
                    $beanInInv->supplier        = $_POST['im_supplier'];
                    $beanInInv->company_unit    = $_POST['im_company_unit'];
                    $beanInInv->ticket_type     = 'ticketing_fee';
                    $beanInInv->itinerary       = $_POST['im_iti'];
                    $beanInInv->booking_id      = $_POST['im_booking_id'];
                    $beanInInv->status          = 1;
                    $beanInInv->cost            = unformat_number($ticketing_fee);
                    $beanInInv->cost_no_vat     = unformat_number($_POST['im_ticketing_fee_no_vat'] ?? 0);
                    $beanInInv->vat_per         = $_POST['im_ticketing_fee_vat_percent'] ?? 0.08;
                    $beanInInv->vat             = unformat_number($_POST['im_ticketing_fee_vat'] ?? 0);
                    $beanInInv->authorized_fee  = 0;
                    $beanInInv->total           = $beanInInv->cost * $beanInInv->qty;
                    $beanInInv->assigned_user_id= $current_user->id;
                    $beanInInv->save2();
                }
            }
            // Update
            else {
                $this->id                   = $_POST['record'];
                $this->name                 = $_POST['im_ticket_code'];
                $this->qty                  = $_POST['im_qty'];
                $this->invoice_number       = $_POST['im_invoice_number'];
                $this->invoice_date         = $_POST['im_invoice_date'];
                $this->invoice_serial       = $_POST['im_invoice_serial'];
                $this->accounting_date      = $_POST['im_accounting_date'];
                $this->supplier             = $_POST['im_supplier'];
                $this->company_unit         = $_POST['im_company_unit'];
                $this->ticket_type          = $_POST['im_ticket_type'] ?? '';
                $this->itinerary            = $_POST['im_iti'];
                $this->booking_id           = $_POST['im_booking_id'];
                $this->status               = 1;
                $this->vat_per              = $_POST['im_vat_percent'] ?? 0.08;
                $this->cost_no_vat          = unformat_number($_POST['im_cost']);
                $this->vat                  = unformat_number($_POST['im_vat']);
                $this->cost                 = unformat_number($_POST['im_cost_vat']);
                $this->authorized_fee       = unformat_number($_POST['im_authorized'] ?? 0);
                $this->total                = unformat_number($_POST['im_total']);
                $this->assigned_user_id     = $current_user->id;
            }
            $input_invoice_id = parent::save($check_notify);

            // Cập nhật note - kpi
            if(is_string($input_invoice_id) && !empty($input_invoice_id) && isset($_POST['im_booking_id']) && !empty($_POST['im_booking_id'])) {
                $booking_id = $_POST['im_booking_id'];
                $booking_name = $_POST['im_booking'] ?? '';

                // Cập nhật đã lấy hoá đơn đầu vào
                $dateModified = date('Y-m-d H:i:s', time() - 7*60*60);
                $this->db->query("UPDATE ec_flight_bookings
                    SET is_invoice_input_export = 1
                        ,modified_user_id = '{$current_user->id}'
                        ,date_modified = '{$dateModified}'
                    WHERE id = '{$booking_id}' AND deleted = 0");

                // Update audit table
                $date_created = date_sub(date_create(date('Y-m-d H:i:s')), date_interval_create_from_date_string("7 hours"));
                $this->db->query('INSERT INTO ec_flight_bookings_audit(id, parent_id, date_created, created_by, field_name, data_type, before_value_string, after_value_string)
                    VALUES (uuid(), "'.$booking_id.'", "'.date('Y-m-d H:i:s', strtotime(date_format($date_created, "Y-m-d"))). '", "'.$current_user->id.'", "is_invoice_input_export", "boolean", 0, 1)');
    
                // Cập nhật kpi
                myCreateWorkingProcess('EC_Flight_Bookings', $booking_id, $booking_name, 'Import từ hoá đơn của hãng (Manual)', $current_user->id, 'invoice_input_issued');
    
                $note = new Note;
                $note->name         = 'Import HD đầu vào';
                $note->parent_type  = 'EC_Flight_Bookings';
                $note->parent_id    = $booking_id;
                $note->description  = 'Đã lấy hóa đơn đầu vào số: ' . $_POST['im_invoice_number'] . '; số vé: ' . $_POST['im_ticket_code'];
                $note->save();

                // Auto create output invoice
                $beanOutInv = new EC_HoaDonBan();
                $beanOutInv->createAuto($booking_id);
            }

            return $input_invoice_id;
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
                ,IFNULL(i.vat_per, 0) AS vat_per
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
