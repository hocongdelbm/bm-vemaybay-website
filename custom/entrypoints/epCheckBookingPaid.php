<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db, $current_user;

if (!empty($_SESSION['authenticated_user_id'])) {
    // nếu là từ phiếu thu thì khi ấn đã thu 
    // thì nếu bk đã ấn nút đã thanh toán thì mới cập nhật đã thanh toán cho bk
    if (isset($_POST['upd_paid'])) {
        // kiểm tra đã ấn đã thanh toán 
        $sql_paid_confirm = '
            SELECT IF(COUNT(id) > 0, 1, 0)
            FROM ec_working_process
            WHERE deleted = 0 AND parent_id = "' . $_POST['booking_id'] .  '"
            AND paid > 0
        ';
        $is_paid_confirm = $db->getOne($sql_paid_confirm);
        if($is_paid_confirm) {
            $sql_upd = '
                UPDATE ec_flight_bookings
                SET is_paid = 1
                WHERE id = "' . $_POST['booking_id'] .  '"
            ';
            $db->query($sql_upd);
        }
    }

    // Chuyển trạng thái
    $booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : '';
    $rv_status  = isset($_POST['rv_status']) ? $_POST['rv_status'] : '';
    if (!empty($booking_id) && $rv_status == '1') {
        echo (myIsBookingPaid($booking_id) ? 1 : 0);
    }
}