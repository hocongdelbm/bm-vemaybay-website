<?php
class EC_VouchersLogicHook {
    function customDisplay($focus, $event, $arguments) {
        $amount = ($focus->reduce_amount > 0) ? format_number($focus->reduce_amount) . ' đ': $focus->reduce_percent . '%';
        $focus->reduce_amount = "<b>$amount</b>";

        $focus->validate_from_date = date('d/m/Y', strtotime($focus->validate_from_date)) . ' - ' . date('d/m/Y', strtotime($focus->validate_to_date));

        $focus->status = $focus->getFormatStatus();
    }
}
