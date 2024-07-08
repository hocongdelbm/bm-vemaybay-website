<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $IP_WHITELIST  = [
        '14.161.31.237', // LBM
        '157.119.251.148', // api-cskh.travelpass.vn
        '157.119.251.220', // zalo.timchuyenbay.net
    ];
    $ip_client = trim(get_ip_address_from_client());
    if(!in_array($ip_client, $IP_WHITELIST)) {
        echo json_encode([
            'error' => 1,
            'message' => "Access is not allowed.",
        ]);
        exit();
    }

    global $db;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if($action == 'update_telegram') {
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $telegram_id = isset($_POST['telegram_id']) ? trim($_POST['telegram_id']) : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';

        if(empty($phone) || empty($telegram_id)) {
            echo json_encode([
                'error' => 1,
                'message' => "Invalid parameters",
            ]);
            exit();
        }

        // Check exist phone
        $sql_exist = 'SELECT IF(COUNT(id) > 0, 1, 0)
            FROM contacts
            WHERE phone_mobile = "'.$phone.'" AND deleted = 0';
        $is_exist = $db->getOne($sql_exist);

        if($is_exist){
            // Update telegram_id
            $sql_update = 'UPDATE contacts
                           SET telegram_id = "'.$telegram_id.'"
                           WHERE phone_mobile = "'.$phone.'" AND deleted = 0';
   
            $check_update = $db->query($sql_update);
            if ($check_update) echo json_encode(['error' => 0, 'message' => "Update Success"]);
            else echo json_encode(['error' => 1, 'message' => "Update Failed"]);
        }
        else {
            // Create new contact
            $con = new Contact();
            $con->phone_mobile  = $phone;
            $con->telegram_id   = $telegram_id;
            $con->last_name     = empty($name) ? 'Liên hệ Telegram' : $name;
            $con->description   = "Liên hệ tạo từ Telegram";
            $con->save();

            echo json_encode(['error' => 0, 'message' => "New contact created successfully"]);
        }

        exit();
    }

    if($action == 'update_zalo') {
        $phone      = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $zalo_id    = isset($_POST['zalo_id']) ? trim($_POST['zalo_id']) : '';
        $name       = isset($_POST['name']) ? trim($_POST['name']) : '';
        $city       = isset($_POST['city']) ? trim($_POST['city']) : '';
        $district   = isset($_POST['district']) ? trim($_POST['district']) : '';
        $street     = isset($_POST['street']) ? trim($_POST['street']) : '';

        if(empty($phone) || empty($zalo_id)) {
            echo json_encode([
                'error' => 1,
                'message' => "Invalid parameters",
            ]);
            exit();
        }

        $where = '';
        if(!empty($zalo_id)) $where = 'zalo_id = "'.$zalo_id.'"';
        elseif(!empty($phone)) $where = 'phone_mobile = "'.$phone.'"';

        $sql = 'SELECT id
            FROM contacts
            WHERE '.$where.' AND deleted = 0
            LIMIT 1';
        $contact_id = $db->getOne($sql);

        $contact = new Contact();
        if($contact_id && !empty($contact_id)) {
            $save = false;
            $contact->retrieve($contact_id);

            if(empty($contact->phone_mobile)) {
                $contact->phone_mobile = $phone;
                $save = true;
            }
            if(empty($contact->zalo_id)) {
                $contact->zalo_id = $zalo_id;
                $save = true;
            }
            // Họ tên
            if(empty($contact->last_name) && !empty($name)) {
                $contact->last_name = $name;
                $save = true;
            }
            // Tỉnh, thành phố
            if(empty($contact->primary_address_city) && !empty($city)) {
                $contact->primary_address_city = $city;
                $save = true;
            }
            // Quận, huyện
            if(empty($contact->primary_address_state) && !empty($district)) {
                $contact->primary_address_state = $district;
                $save = true;
            }
            // Phường, xã + Địa chỉ
            if(empty($contact->primary_address_street) && !empty($street)) {
                $contact->primary_address_street = $street;
                $save = true;
            }
            if($save === true) {
                $contact->save();
                echo json_encode(['error' => 0, 'message' => "Update Success"]);
            }
            else echo json_encode(['error' => 1, 'message' => "Nothing to do"]);
        }
        else {
            $contact->id = '';
            $contact->phone_mobile              = $phone;
            $contact->zalo_id                   = $zalo_id;
            $contact->last_name                 = $name;
            $contact->primary_address_city      = $city;
            $contact->primary_address_state     = $district;
            $contact->primary_address_street    = $street;
            $contact->description               = "Liên hệ tạo từ Zalo OA";
            $contact->save();

            echo json_encode(['error' => 0, 'message' => "New contact created successfully"]);
        }

        exit();
    }
}

header('HTTP/1.1 404 Not Found');
exit();