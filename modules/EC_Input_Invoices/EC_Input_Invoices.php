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

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    function save($check_notify = FALSE)
    {
        global $current_user;
        if (isset($_POST['im_ticket_code'])) {

            $sql = '
                SELECT id
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
                    // lấy số thứ tự
                    $sql = '
						SELECT COUNT(id) 
						FROM ec_input_invoices 
						WHERE invoice_number = "' . $_POST['im_invoice_number'] . '"
                            AND invoice_serial = "' . $_POST['im_invoice_serial'] . '"
                            AND supplier = "' . $_POST['im_supplier'] . '"
                            AND status = 1
                            AND deleted = 0
					';
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
        }
    }

    function save2($check_notify = FALSE)
    {
        parent::save($check_notify);
    }
}
