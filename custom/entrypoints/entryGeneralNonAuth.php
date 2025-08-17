<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

try {
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $entryAuth = false;
    require_once 'custom/entrypoints/entryFactory.php';

    $request_method = $_SERVER['REQUEST_METHOD'] ?? '';
    if (in_array($request_method, ['POST', 'GET'])) {
        global $sugar_config;
        $headers = getallheaders();
        $contentType = $headers['Content-Type'] ?? '';
        $api_key = $headers['Api-Key'] ?? '';
        $ip = get_ip_address_from_client();

        if(!in_array($ip, $sugar_config['ip_whitelist'] ?? [])) {
            http_response_code(403);
            echo json_encode([
                "status" => 0,
                "message" => "Access denied"
            ]);
            exit();
        }
        if($api_key != ($sugar_config['api_key']['non_auth_entrypoint'] ?? '')) {
            http_response_code(401);
            echo json_encode([
                "status" => 0,
                "message" => "Unauthorized"
            ]);
            exit();
        }

        // Get data
        $params = $_GET;
        $reqBody = [];
        if(stripos($contentType, 'application/json') !== false) $reqBody = json_decode(file_get_contents('php://input'), true);
        else $reqBody = $_POST;

        $className  = global_test_input($reqBody['class'] ?? $params['class'] ?? '');
        $method     = global_test_input($reqBody['method'] ?? $params['method'] ?? '');
        $methodParams = $reqBody['params'] ?? $params['params'] ?? [];

        $entryClass = entryFactory::create($className);
        if($entryClass) {
            if (method_exists($entryClass, $method)) {
                // // Check required params
                // $refMethod = new ReflectionMethod($entryClass, $method);
                // $expectedParams = $refMethod->getParameters();
                // $expectedParamCount = $refMethod->getNumberOfRequiredParameters();
                // $missingParams = [];

                // foreach ($expectedParams as $index => $param) {
                //     // Check if a required parameter is missing
                //     if (!$param->isOptional() && !array_key_exists($param->getName(), $methodParams)) {
                //         $missingParams[] = $param->getName();
                //     }
                // }

                // if (!empty($missingParams)) {
                //     http_response_code(400);
                //     echo json_encode([
                //         "status" => 0,
                //         "message" => "Action expects at least $expectedParamCount parameter(s), but only " . count($params) . " given",
                //         "description" => [
                //             "missingParams" => $missingParams
                //         ]
                //     ]);
                //     exit();
                // }

                $response = $entryClass->$method($methodParams);
                if(!is_string($response)) $response = json_encode($response);
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
