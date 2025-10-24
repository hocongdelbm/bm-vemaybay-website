<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
}