<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if($action == "get_cities") {
        echo json_encode([
            'error'     => 0,
            'message'   => 'Success',
            'data'      => globalGetCitiesAddress()
        ]);
        exit();
    }
    elseif($action == "get_districts") {
        $parent_value = isset($_POST['parent_value']) ? $_POST['parent_value'] : '';
        echo json_encode([
            'error'     => 0,
            'message'   => 'Success',
            'data'      => globalGetDistrictsAddress($parent_value)
        ]);
        exit();
    }
    elseif($action == "get_wards") {
        $parent_value = isset($_POST['parent_value']) ? $_POST['parent_value'] : '';
        echo json_encode([
            'error'     => 0,
            'message'   => 'Success',
            'data'      => globalGetWardsAddress($parent_value)
        ]);
        exit();
    }
}

?>