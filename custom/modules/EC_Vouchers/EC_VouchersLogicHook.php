<?php
class EC_VouchersLogicHook {
    function customDisplay($focus, $event, $arguments) {
        // $voucher = new EC_Vouchers;
        // $voucher->retrieve($focus->id);

        $focus->validate_from_date = date('d/m/Y', strtotime($focus->validate_from_date)) . ' - ' . date('d/m/Y', strtotime($focus->validate_to_date));

        // if($focus->start_time && $focus->end_time)
        // $focus->validate_from_date = date('d/m/Y', strtotime($voucher->validate_from_date)) . ' - ' . date('d/m/Y', strtotime($voucher->validate_to_date));

        // // Booking sử dụng
        // if (!empty($focus->booking_receive_id)) {
        //     $booking = new EC_Flight_Bookings;
        //     $booking->retrieve($focus->booking_receive_id);
        //     $focus->booking_receive_id = '<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $booking->id . '" target="_blank">' . $booking->name . '</a>';
        // }

        // $status = $GLOBALS['app_list_strings']['voucher_status_list'][$focus->status];
        // if($focus->status == 'new'){
        //     $focus->status = '<b class="text-dark">'.$status.'</b>';
        // } else if($focus->status == 'active'){
        //     $focus->status = '<b class="text-success">'.$status.'</b>';
        // } else if($focus->status == 'cancel'){
        //     $focus->status = '<b class="text-danger">'.$status.'</b>';
        // } else if($focus->status == 'expired'){
        //     $focus->status = '<b class="text-warning">'.$status.'</b>';
        // } else if($focus->status == 'done'){
        //     $focus->status = '<b class="text-primary">'.$status.'</b>';
        // }
    }
}
