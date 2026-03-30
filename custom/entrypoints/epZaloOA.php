<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once("modules/EC_Zalo/Zalo.php");
    global $current_user, $db;

    $action = isset($_POST['action']) ? $_POST['action'] : "";
   
    if($action == 'update_user_alias') { // Version 2
        echo json_encode([
            "error" => 1,
            "message" => "Tính năng đang được nâng cấp. Vui lòng thử lại sau",
        ]);
        exit();

        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $alias   = isset($_POST['alias']) ? $_POST['alias'] : "";

        if(empty($zalo_id) || empty($alias)) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
                "data" => ["zalo_id" => $zalo_id, "alias" => $alias]
            ]);
            exit();
        }

        $Zalo = new Zalo();
        $json = $Zalo->update_user($zalo_id, $alias);
        echo $json;

        $arr = json_decode($json, true);
        if(isset($arr['error']) && $arr['error'] == 0) {
            try {
                $db->query("UPDATE contacts SET zalo_name = '$alias' WHERE zalo_id = '$zalo_id' AND deleted = 0");
            }
            catch(Exception $e) {
                exit();
            }
        }
       
        exit();
    }
    elseif($action == 'update_user_info') { // Version 2
        echo json_encode([
            "error" => 1,
            "message" => "Tính năng đang được nâng cấp. Vui lòng thử lại sau",
        ]);
        exit();

        $zalo_id        = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $name           = isset($_POST['info_user_name']) ? $_POST['info_user_name'] : "";
        $phone          = isset($_POST['info_user_phone']) ? $_POST['info_user_phone'] : "";
        $address        = isset($_POST['info_user_address']) ? $_POST['info_user_address'] : "";
        $city_id        = isset($_POST['info_user_city']) ? (int)$_POST['info_user_city'] : 0;
        $district_id    = isset($_POST['info_user_district']) ? (int)$_POST['info_user_district'] : 0;
        $shared_info = [
            "name" => $name,
            "phone" => $phone,
            "address" => $address,
            "city_id" => $city_id,
            "district_id" => $district_id
        ];

        if(strlen($zalo_id)*strlen($name)*strlen($phone)*strlen($address)*$district_id*$city_id === 0) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
                "data" => [
                    "zalo_id" => $zalo_id,
                    "name" => $name,
                    "phone" => $phone,
                    "address" => $address,
                    "city_id" => $city_id,
                    "district_id" => $district_id
                ]
            ]);
            exit();
        }

        $Zalo = new Zalo();
        $json = $Zalo->update_user($zalo_id, '', $shared_info);
        echo $json;

        $arr = json_decode($json, true);
        if(isset($arr['error']) && $arr['error'] == 0) {
            try {
                $phone = $Zalo->unformat_zalo_phone($phone);
                $city = $db->getOne("SELECT name FROM cities WHERE id = '$city_id' LIMIT 1") ?? '';
                $district = $db->getOne("SELECT name FROM districts WHERE id = '$district_id' LIMIT 1") ?? '';

                $db->query("UPDATE contacts
                    SET zalo_name = '$name',
                        phone_mobile = '$phone',
                        primary_address_street = '$address',
                        primary_address_city = '$city',
                        primary_address_state = '$district',
                        date_modified = NOW()
                    WHERE zalo_id = '$zalo_id' AND deleted = 0");
            }
            catch(Exception $e) {
                exit();
            }
        }
        exit();
    }
    elseif($action == 'add_tag_user') { // Version 2
        echo json_encode([
            "error" => 1,
            "message" => "Tính năng đang được nâng cấp. Vui lòng thử lại sau",
        ]);
        exit();

        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $tag_name = isset($_POST['tag_name']) ? $_POST['tag_name'] : "";

        if(empty($zalo_id) || empty($tag_name)) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
                "data" => [
                    "zalo_id" => $zalo_id,
                    "tag_name" => $tag_name
                ]
            ]);
            exit();
        }

        $Zalo = new Zalo();
        $json = $Zalo->add_tag_user($zalo_id, $tag_name);
        echo $json;

        $arr = json_decode($json, true);
        if(isset($arr['error']) && $arr['error'] == 0) {
            try {
                $db->query("UPDATE contacts SET zalo_tags = '$tag_name' WHERE zalo_id = '$zalo_id' AND deleted = 0");
            }
            catch(Exception $e) {
                exit();
            }
        }
        
        exit();
    }
    elseif($action == 'remove_tag_user') { // Version 2
        echo json_encode([
            "error" => 1,
            "message" => "Tính năng đang được nâng cấp. Vui lòng thử lại sau",
        ]);
        exit();

        $zalo_id = isset($_POST['zalo_id']) ? $_POST['zalo_id'] : "";
        $tag_name = isset($_POST['tag_name']) ? $_POST['tag_name'] : "";

        if(empty($zalo_id) || empty($tag_name)) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
                "data" => [
                    "zalo_id" => $zalo_id,
                    "tag_name" => $tag_name
                ]
            ]);
            exit();
        }

        $Zalo = new Zalo();
        $json = $Zalo->remove_tag_user($zalo_id, $tag_name);
        echo $json;

        $arr = json_decode($json, true);
        if(isset($arr['error']) && $arr['error'] == 0) {
            try {
                $db->query("UPDATE contacts SET zalo_tags = REPLACE(zalo_tags, '$tag_name', '') WHERE zalo_id = '$zalo_id' AND deleted = 0");
            }
            catch(Exception $e) {
                exit();
            }
        }
        exit();
    }

    echo json_encode([
        "error" => 1,
        "message" => "Tính năng chưa chỗ trợ"
    ]);
    exit();
}

header("HTTP/1.0 405 Method Not Allowed");
echo json_encode([
    "error" => 1,
    "message" => "Method not allowed"
]);
exit();
?>