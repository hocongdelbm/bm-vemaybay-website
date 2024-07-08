<?php
require_once("include/Sugar_Smarty.php");

class Viewrecoveryorder extends SugarView {
    function display() {
        global $current_user;
        if (ACLController::checkAccess('EC_Payment_Voucher', 'edit', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_recoveryorder.tpl');
        } else {
            header('Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=' . urlencode('Bạn không được quyền truy cập vào mục này'));
            exit;
        }
    }

    function populateContent($smartyObj) {
        global $db;
        $bookingNo = '';
        if(!empty($_POST['txtBookingNo'])){
            $bookingNo = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['txtBookingNo']);
        }
        $error = '';
        $success = '';

        if(isset($_POST['btnRecovery'])) {
            $sql = "SELECT id FROM ec_flight_bookings WHERE name = '$bookingNo' AND deleted = 1 LIMIT 1";
            $bookingId = $db->getOne($sql);
            if (!empty($bookingId)) {
                $db->query("UPDATE ec_flight_bookings SET deleted = 0 WHERE id = '$bookingId' AND deleted = 1");
                $db->query("UPDATE ec_booking_itineraries SET deleted = 0 WHERE booking_id = '$bookingId' AND deleted = 1");
                $db->query("UPDATE ec_booking_details SET deleted = 0 WHERE booking_id = '$bookingId' AND deleted = 1");
                $db->query("UPDATE ec_booking_passengers SET deleted = 0 WHERE booking_id = '$bookingId' AND deleted = 1");
                $db->query("UPDATE notes SET deleted = 0 WHERE parent_id = '$bookingId' AND parent_type = 'EC_Flight_Bookings' AND deleted = 1");
                $success = 'Phục hồi thành công';
            } else {
                $error = 'Số booking không tồn tại hoặc chưa được xóa';
            }
        }

        $smartyObj->assign('BOOKING_NO', $bookingNo);
        $smartyObj->assign('SUCCESS', $success);
        $smartyObj->assign('ERROR', $error);
    }
}
