<?php
require_once "custom/entrypoints/entryAuthClass/entryClass.php";

/**
 * Class entryInputInvoiceClass
 */
class entryInputInvoiceClass extends entryClass {

    /**
     * Get ticket number information
     * 
     * @param array $params
     * @return array
     */
    public function getTicketNumberInfoById($params = []) {
        $ticketId = $params['ticketId'] ?? '';

        if(empty($ticketId)) return ["status" => 0, "message" => "Không tìm thấy số vé", "data" => null];

        global $db;
        $sql = "SELECT i.id AS inv_id
                ,i.invoice_date AS inv_date
                ,i.invoice_number AS inv_number
                ,i.invoice_serial AS inv_serial
                ,i.itinerary AS inv_iti
                ,i.booking_id AS inv_booking_id
                ,bk.name AS inv_booking
                ,i.supplier AS inv_supplier
                ,i.qty AS inv_qty
                ,i.cost_no_vat AS inv_cost
                ,IFNULL(i.vat_per, 0) AS inv_vat_percent
                ,i.vat AS inv_vat
                ,i.cost AS inv_cost_vat
                ,i.authorized_fee AS inv_authorized
                ,i.total AS inv_total
                ,i.accounting_date AS inv_accounting_date
                ,i.ticket_type AS inv_ticket_type
            FROM ec_input_invoices i
                LEFT JOIN ec_flight_bookings bk ON bk.id = i.booking_id AND bk.deleted = 0
            WHERE i.id = '{$ticketId}'
                AND i.status = '1'
                AND i.deleted = 0";

        $res = $db->query($sql);
        $data = $db->fetchByAssoc($res);

        // Format date
        $data['inv_date'] = $data['inv_date'] && strtotime($data['inv_date']) ? date('d-m-Y', strtotime($data['inv_date'])) : '';
        $data['inv_accounting_date'] = $data['inv_accounting_date'] && strtotime($data['inv_accounting_date']) ? date('d-m-Y', strtotime($data['inv_accounting_date'])) : '';

        return [
            "status" => 1,
            "message" => "Success",
            "data" => $data
        ];
    }

    /**
     * Duplicate ticket
     * 
     * @param array $params
     * @return array
     */
    public function duplicateTicket($params = []) {
        try {
            $notRequiredFields = ['booking_id'];

            // Sanitize all POST data
            $data = $errors = [];
            foreach ($params as $key => $value) {
                // Skip validation for fields in the "not required" list
                if (in_array($key, $notRequiredFields)) {
                    $data[$key] = $value;
                    continue;
                }

                // For arrays, check if all elements are empty (optional)
                if (is_array($value)) {
                    $allEmpty = true;
                    foreach ($value as $item) {
                        if ($item !== '' && $item !== null) {
                            $allEmpty = false;
                            break;
                        }
                    }
                    if ($allEmpty) {
                        $errors[$key] = "Field $key is required.";
                    } else {
                        $data[$key] = array_map([$this, 'cleanInput'], $value);
                    }
                }
                // For single values
                elseif ($value === '' || $value === null) {
                    $errors[$key] = "Field $key is required.";
                } else {
                    $data[$key] = $this->cleanInput($value);
                }
            }

            if (!empty($errors)) {
                return ['status' => 0, 'message' => 'Hóa đơn gốc thiếu dữ liệu', 'data' => null, 'description' => $errors];
            }

            // Get booking id from booking name
            if(!isset($data['booking_id']) || !$data['booking_id']) {
                global $db;
                $booking_id = $db->getOne("SELECT id FROM ec_flight_bookings WHERE name = '". $data['booking'] ."' AND deleted=0");
                $data['booking_id'] = $booking_id;

                if(!$booking_id || !is_string($booking_id)) {
                    return ['status' => 0, 'message' => 'Không tìm thấy booking', 'data' => null, 'description' => $data];
                }
            }

            $input_inv = new EC_Input_Invoices();
            $input_inv->id = '';
            foreach(array_keys($data) as $field) {
                if(strpos($field , "_date") !== false) $data[$field] = date('d-m-Y', strtotime(str_replace('/', '-', $data[$field])));
                $input_inv->$field = $data[$field] ?? null;
            }
            // Calculate total price
            $input_inv->total = $input_inv->cost + $input_inv->authorized_fee;
            $input_inv->status = '1';
            $input_inv->deleted = 0;
            $id = $input_inv->save2();
            $data['id'] = $id;

            if(!$id) return ['status' => 0, 'message' => 'Thao tác thất bại', 'data' => null, 'description' => $data];
            else return ['status' => 1, 'message' => 'Nhân bản thành công', 'data' => $data];
        }
        catch(Throwable $th) {
            return ['status' => 0, 'message' => $th->getMessage(), 'data' => null];
        }
    }

    /**
     * Remove ticket by id
     * 
     * @param array $params
     * @return array
     */
    public function removeTicket($params = []) {
        $ticketId = trim($params['ticketId'] ?? '');

        if(empty($ticketId)) return ["status" => 0, "message" => "Không tìm thấy số vé", "data" => null];

        global $db;
        $dateModified = date('Y-m-d H:i:s', time() - 7*60*60);
        $sql = "UPDATE ec_input_invoices
            SET deleted = 1
                ,modified_user_id = '{$this->currentUser->id}'
                ,date_modified = '{$dateModified}'
            WHERE id = '{$ticketId}'";
        
        if($db->query($sql)) return ["status" => 1, "message" => "Xóa thành công", "data" => null];
        return ["status" => 0, "message" => "Thao tác chưa thành công, vui lòng thử lại sau", "data" => null];
    }
}