<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
date_default_timezone_set('Asia/Ho_Chi_Minh');

try {
    // $entryAuth = true;
    // require_once 'custom/entrypoints/entryFactory.php';

    $request_method = $_SERVER['REQUEST_METHOD'] ?? '';
    if (in_array($request_method, ['POST', 'GET'])) {
        $headers = getallheaders();
        $contentType = $headers['Content-Type'] ?? '';
        $api_key = $headers['Api-Key'] ?? '';

        // Get data
        $params = $_GET;
        $reqBody = null;
        if(stripos($contentType, 'application/json') !== false) $reqBody = json_decode(file_get_contents('php://input'), true);
        else $reqBody = $_POST;

        $className      = global_test_input($params['class'] ?? $reqBody['class'] ??'');
        $method         = global_test_input($params['method'] ?? $reqBody['method'] ?? '');
        $methodParams   = $reqBody['params'] ?? $reqBody ?? $params['params'] ?? [];
        if(is_string($methodParams)) $methodParams = json_decode(html_entity_decode($methodParams), true);
        
        $entryClass = entryFactory::create($className);
        if($entryClass) {
            if (method_exists($entryClass, $method)) {
                $response = $entryClass->$method($methodParams);
                if(!is_string($response)) $response = json_encode($response, JSON_UNESCAPED_UNICODE);
                echo $response;
                exit;
            }
            else {
                http_response_code(400);
                echo json_encode([
                    "status" => 0,
                    "message" => trim("Invalid action $method in $className"),
                ]);
                exit();
            }
        }

        http_response_code(400);
        echo json_encode([
            "status" => 0,
            "message" => trim("Invalid class name $className"),
            "description" => "Not found in ".__DIR__."/entryNonAuthClass/$className.php"
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode([
        "status" => 0,
        "message" => "Method not allowed"
    ]);
    exit;
}
catch (RuntimeException $e) {
    http_response_code(408);
    echo json_encode([
        "status" => 0,
        "message" => "Runtime exception: {$e->getMessage()} on line {$e->getLine()} at {$e->getFile()}"
    ]);
    exit;
}
catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => 0,
        "message" => "Exception: {$e->getMessage()} on line {$e->getLine()} at {$e->getFile()}"
    ]);
    exit;
}
catch (Throwable $th) {
    http_response_code(500);
    echo json_encode([
        "status" => 0,
        "message" => "Throwable: {$th->getMessage()} on line {$th->getLine()} at {$th->getFile()}"
    ]);
    exit;
}
