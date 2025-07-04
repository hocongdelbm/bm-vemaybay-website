<?php
class SummaryStie{
    public $url;
    private $apiKey = "c8504e56-c21a-4d34-872a-19cca0cd6b1f";
    private $endpoints = [
        'get_traffic'     => '/get_traffic',
        'get_logs'        => '/get_logs',
        'get_log_detail'  => '/get_detail',
        'get_traffic_insights' => '/get_traffic_insights'
    ];

    function __construct() {
        $this->url = "https://tracking.vemaybay.website/api";
    }

    public function call_api() {
        $input = file_get_contents('php://input');
        // var_dump($input);die;
        $request_data = json_decode($input, true);

        $action = $request_data['action'] ?? '';
        if (!$action || !isset($this->endpoints[$action])) {
            return $this->response(1, 'Invalid action', null, 400);
        }

        $data = [];
        switch ($action) {
            case 'get_traffic':
            case 'get_logs':
                $data['options']['from_time'] = date_format(new DateTime($request_data["options"]['from_date']), "Y-m-d H:i:s");
                $data['options']['to_time']   = date_format(new DateTime($request_data["options"]['to_date']), "Y-m-d H:i:s");
                $data["options"]["flag"]      = $request_data["options"]["flag"] ?? null;
                $data["options"]["filters"]   = $request_data["options"]["filter"] ?? [];
                $data['domain']               = $request_data['domain'] ?? "";
                break;
            case 'get_traffic_insights':
                $data['options']['from_time'] = date_format(new DateTime($request_data["options"]['from_date']), "Y-m-d H:i:s");
                $data['options']['to_time']   = date_format(new DateTime($request_data["options"]['to_date']), "Y-m-d H:i:s");
                $data["options"]["filters"]   = $request_data["options"]["filter"] ?? [];
                $data['domain']               = $request_data['domain'] ?? "";
                break;
            case 'get_log_detail':
                $data['domain'] = $request_data['domain'] ?? "";
                $data['id']     = $request_data['id'] ?? "";
                break;
            default:
                break;
        }

        $endpoint = $this->url . $this->endpoints[$action];
        return $this->exc_curl($endpoint, $data);
    }

    public function exc_curl($url, $request_data) {
        $header = [
            "Content-Type: application/json",
            "api-key: {$this->apiKey}"
        ];
        $curl = curl_init();
        $request = json_encode($request_data);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);

        $response_data = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response_data === false) {
            return $this->response(1, "fail to load data", null, 500);
        }

        $receive_data = json_decode($response_data, true);
        if (isset($receive_data['status'], $receive_data['message'], $receive_data['data'])) {
            return $this->response(
                $receive_data['status'] == 1 ? 0 : 1,
                $receive_data['message'],
                $receive_data['data'],
                $httpCode
            );
        } else {
            return $this->response(1, "Invalid response format", null, 500);
        }
    }

    public function response($error = 0, $message = '', $data = null, $http_code = 200) {
        http_response_code($http_code);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $error,
            'message' => $message,
            'data' => $data,
            'http_code' => $http_code
        ]);
        exit;
    }
}
$class = new SummaryStie();
$class->call_api();