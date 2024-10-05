<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $db;
    $type = isset($_POST["type"]) ? $_POST["type"] : null;
    
    if($type == 'ADD') {
        // Validate
        $name           = (isset($_POST["name"]) && !empty($_POST["name"])) ? $_POST["name"] : null;
        $description    = (isset($_POST["description"]) && !empty($_POST["description"])) ? $_POST["description"] : null;
        $parent_id      = (isset($_POST["parent_id"]) && !empty($_POST["parent_id"])) ? $_POST["parent_id"] : null;
        $booking_status = (isset($_POST["booking_status"]) && !empty($_POST["booking_status"])) ? $_POST["booking_status"] : null;
        if(is_null($name)  || is_null($description) || is_null($parent_id) || is_null($booking_status)) {
            echo 0;
            exit();
        }

        $n = new Note();
        $n->name            = $name;
        $n->description     = $description;
        $n->parent_type     = 'EC_Flight_Bookings';
        $n->parent_id       = $parent_id;
        $n->booking_status  = $booking_status;
        $check = $n->save();

        if(strlen($check) > 0) echo 1;
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

        // UPDATE booking_status - EC_customer
        UpdateInforBookingOfCustomer($booking_id);

        echo $count;
        exit();
    }
}

echo 0;
exit();
?>
