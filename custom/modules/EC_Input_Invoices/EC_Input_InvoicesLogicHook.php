<?php
class EC_Input_InvoicesLogicHook {
    function customDisplay($focus, $argument, $event) {
        $input_invoice = new EC_Input_Invoices;
        $input_invoice->retrieve($focus->id);
        $focus->total = format_number($input_invoice->cost + $input_invoice->authorized_fee);
        // tính sl đã xuất
        $export_qty = $focus->db->getOne('
            SELECT SUM(soluong)
            FROM ec_chitiethoadon 
            WHERE ticket_number_id = "' . $focus->id . '" AND deleted = 0
        ');
        $focus->left_qty = $focus->qty - $export_qty;
    }
}