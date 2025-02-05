<?php
include "modules/EC_TongHop/views/view.summaryview.php";
class CounterUserOnline {
    private function getCounterOnline($url){
        $request = [
             "key" => "BNjExQdCmf3dh7wd9ZNVm_D83oXvW+uACPPOrLcK",
             "time" =>300
         ];
         $request = json_encode($request);
         $curl = curl_init();
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
         curl_setopt($curl, CURLOPT_TIMEOUT, 30);
         curl_setopt($curl, CURLOPT_POST, true);
         curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
         $response_data = curl_exec($curl);
         $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
         $return_data = [];
         if($response_data === false){
            return $this -> response(
                $error = 1,
                $message = "fail to load data",
                $data = 0,
                $http_code = 500
            );
         }            
            curl_close($curl);
            $return_data = array(
                'repsonse_data' => $response_data,
                'http_code' => $httpCode
            );
            return $return_data;
     }
    public function fetchDataFromURL(){
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
                $url = $input['url_selected'];
            }
            if($url != null){
                $get_data = $this -> getCounterOnline($url);
                $receive_data = json_decode($get_data['repsonse_data'], true);
            }else{
                return;
            }
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
$online_visitor = new CounterUserOnline;
$call_API = $online_visitor->fetchDataFromURL();