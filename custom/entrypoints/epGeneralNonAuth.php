<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
try {
    $ep_auth = false;
    require_once 'custom/entrypoints/entryFactory.php';

    $request_method = $_SERVER['REQUEST_METHOD'] ?? '';
    if (in_array($request_method, ['POST', 'GET'])) {
        $headers = getallheaders();
        $content_type = $headers['Content-Type'] ?? '';
        $api_key = $headers['Api-Key'] ?? '';

        // Get data
        $data = [];
        if($request_method === 'GET') $data = $_GET;
        elseif(strpos($contentType, 'application/json') !== false) $data = json_decode(file_get_contents('php://input'), true);
        // elseif(strpos($contentType, 'multipart/form-data') !== false || strpos($contentType, 'application/x-www-form-urlencoded') !== false) $data = $_POST;
        else $data = $_REQUEST;
        $class_name = isset($data['class']) ? global_test_input($data['class']) : '';
        $method     = isset($data['method']) ? global_test_input($data['method']) : '';
        $params     = $data['params'] ?? [];

        $entryClass = entryFactory::create($class_name, $data);
        if($entryClass) {
            $response = call_user_func_array([$entryClass, $method], []);
            if(!is_string($response)) $response = json_encode($response);
            echo $response;
            exit();
        }

        http_response_code(400);
        echo json_encode([
            "status" => 0,
            "message" => "Invalid class name $class_name",
            "description" => "Not found in ".__DIR__."/epAuthClass/$class_name.php"
        ]);
        exit();
    }

    http_response_code(405);
    echo json_encode([
        "status" => 0,
        "message" => "Method not allowed"
    ]);
    exit();
}
catch (RuntimeException $e) {
    http_response_code(408);
    echo json_encode([
        "status" => 0,
        "message" => "Runtime exception: {$e->getMessage()} on line {$e->getLine()} at {$e->getFile()}"
    ]);
    exit();
}
catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => 0,
        "message" => "Exception: {$e->getMessage()} on line {$e->getLine()} at {$e->getFile()}"
    ]);
    exit();
}
catch (Throwable $th) {
    http_response_code(500);
    echo json_encode([
        "status" => 0,
        "message" => "Throwable: {$th->getMessage()} on line {$th->getLine()} at {$th->getFile()}"
    ]);
    exit();
}
