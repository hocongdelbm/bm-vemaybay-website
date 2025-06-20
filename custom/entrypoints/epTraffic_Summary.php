<?php
class TrafficSummary{
     public function get_traffic($url = "https://tracking.vemaybay.website/api/get_traffic"){
        try {
        $header = [
            "Content-Type: application/json",
            "api-key: c8504e56-c21a-4d34-872a-19cca0cd6b1f" 
        ];
        $curl = curl_init();
        $request_data = file_get_contents('php://input');
        $request_data = json_decode($request_data, true);
        // $from_time = $request_data["from_time"] = new DateTime($request_data["from_date"]);
        // $to_time   = $request_data["to_time"]   = new DateTime($request_data["to_date"]);
        $from_time = $request["from_time"] = date_format(new DateTime($request_data["from_date"]), "Y-m-d H:i:s");
        $to_time   = $request["to_time"]   = date_format(new DateTime($request_data["to_date"]),"Y-m-d H:i:s");
        $domain    = $request["domain"]    = $request_data["domain"] ?? "";
        $request = json_encode($request);
        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        $response_data = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if($response_data === false){
        return $this -> response(
            $error = 1,
            $message = "fail to load data",
            $data = 0,
            $http_code = 500
        );
        }            
        curl_close($curl);
        $receive_data = json_decode($response_data, true);
        if(isset($receive_data['status'], $receive_data['message'], $receive_data['data']))
            return $this -> response(
            $error = $receive_data['status'],
            $message = $receive_data['message'],
            $data = $receive_data['data'],
            $http_code = $httpCode
            );
       } catch (\Throwable $th) {
            // throw $th;

       }
    }

    public function fetchDataFromURL(){
        $protocol = "";
        $host = $_SERVER["HTTP_HOST"];
        $URI = $_SERVER["REQUEST_URI"];
        if(!empty($_SERVER["HTTP"])&& $_SERVER["HTTPS"] !== 'off' || $_SERVER["SERVER_PORT"] == 443){
            $protocol = "https://";
        }else{
            $protocol = "http://";
        }
        $called_url = $protocol.$host.$URI;
        $access_url = $_SERVER["HTTP_REFERER"];
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this -> response(
                $error = 1,
                    $message = "method not allowed",
                    $data = 0,
                    $http_code = 405
            );    
        }else{
            $inputJSON = file_get_contents('php://input');
            if($inputJSON){
                $input = json_decode($inputJSON, true);
                // $url = $input['url_selected'];
            }
            // if($url != null){
                $get_data = $this -> get_traffic();
                $receive_data = json_decode($get_data['repsonse_data'], true);
            // }else{
            //     return;
            // }
            if(isset($receive_data['error'], $receive_data['message'], $receive_data['data']))
            return $this -> response(
            $error = $receive_data['error'],
        $message = $receive_data['message'],
            $data = $receive_data['data'],
            $http_code = $get_data['http_code']
            );
         }
     }

    public function response($error = 0, $message = '', $data = null, $http_code = ""){
        http_response_code($http_code);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $error,
            'message' => $message,
            'data' => $data,
            'http_code' =>$http_code
        ]);
    exit;
    }  
}
$class = new TrafficSummary;
$class->get_traffic();