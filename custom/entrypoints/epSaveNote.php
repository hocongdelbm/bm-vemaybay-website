<?php
use Custom\Services\Notification\NotificationService;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $db;
    $type = isset($_POST["type"]) ? $_POST["type"] : null;
    
    if($type == 'ADD') {
        // Validate
        $booking_name   = $_POST["name"] ?? "";
        $description    = $_POST["description"] ?? "";
        $parent_id      = $_POST["parent_id"] ?? "";
        $booking_status = $_POST["booking_status"] ?? null;
        $contact_name   = $_POST["contact_name"] ?? "";
        $total_amount   = $_POST["total_amount"] ?? null;
        $total_qty      = $_POST["total_qty"] ?? null;

        if(empty($booking_name) || empty($description) || empty($parent_id) || is_null($booking_status)) {
            echo 0;
            exit();
        }

        $n = new Note();
        $n->name            = $booking_name;
        $n->description     = $description;
        $n->parent_type     = 'EC_Flight_Bookings';
        $n->parent_id       = $parent_id;
        $n->booking_status  = $booking_status;
        if($n->save()) {
            if (mb_stripos($description, "Đã chuyển khoản") !== false) {
                global $sugar_config, $app_list_strings;
                $channel = $sugar_config['notification_channel'] ?? 'Telegram';

                $m = '';
                if($n->hasMoney($description)) $m = trim("$booking_name - $contact_name - $description");
                else {
                    $total_amount_format = !is_null($total_amount) ? number_format($total_amount, 0) : '';
                    $m = trim("$booking_name - $contact_name - $description $total_amount_format ($total_qty vé)");
                }

                $customer_source = $db->getOne("SELECT customer_source FROM ec_flight_bookings WHERE id = '$parent_id' AND deleted = 0");
                if(isset($app_list_strings['booking_customer_source_list'][$customer_source])) {
                    $c = $app_list_strings['booking_customer_source_list'][$customer_source];
                    if($c == 'Mới') $c = 'KH ' . strtolower($c);
                    $m .= " - <b>$c</b>";
                }

                NotificationService::sendMessage($m, 'thongbao');
            }
            echo 1;
        }
        else echo 0;

        exit();
    }
    elseif ($type == "DELETE") {
        // Validate
        $id_note        = (isset($_POST["id_note"]) && !empty($_POST["id_note"])) ? $_POST["id_note"] : null;
        $id_process     = (isset($_POST["id_process"]) && !empty($_POST["id_process"])) ? $_POST["id_process"] : null;
        $type_process   = (isset($_POST["type_process"]) && !empty($_POST["type_process"])) ? $_POST["type_process"] : null;
        $booking_id     = (isset($_POST["booking_id"]) && !empty($_POST["booking_id"])) ? $_POST["booking_id"] : null;
        if(is_null($id_note) ) {
            echo 0;
            exit();
        }

        $sql = 'UPDATE notes 
                SET deleted = 1
                WHERE id = "'.$id_note.'" AND deleted = 0';
        $count = $db->query($sql);

        if(!is_null($id_process) && !is_null($type_process) && !is_null($booking_id)) {
            $sql_process = 'UPDATE ec_working_process 
                SET deleted = 1
                WHERE id = "'.$id_process.'" AND deleted = 0';
            $count += $db->query($sql_process);

            // Update booking status
            if($type_process == "called") {
                $sql_status = "UPDATE ec_flight_bookings
                    SET booking_status = 1
                    WHERE id = '".$booking_id."'";
                $count += $db->query($sql_status);
            }
            elseif($type_process == "paid") {
                $sql_status = "UPDATE ec_flight_bookings
                    SET booking_status = 6, is_hold = 0, is_ticket_exported = 0, is_paid = 0, holding_status = 0, is_mail_confirm = 0
                    WHERE id = '".$booking_id."'";
                $count += $db->query($sql_status);
            }
        }

        echo $count;
        exit();
    }
}

echo 0;
exit();
?>
