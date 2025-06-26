<?php
class VietjetAPIHelper {
    private $ENDPOINT;
    private $API_KEY;

    public function __construct($supplier_id) {
        $this->ENDPOINT = "https://data01.timchuyenbay.vn/api/v3";
        // $this->ENDPOINT = "https://data01.timchuyenbay.net/api/v1";
        $this->API_KEY = "1r2Lm4Rof1KsOM_SHbiCx1zx59@G54TmQq1T7XY5fK85OG28S+";
    }

    public function getSessionKey() {
        try {
            $curl = curl_init();
            if ($curl === false) return json_encode(["error" => 1, "message" => "Lỗi hệ thống", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/getSessionKey");
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 6);
            curl_setopt($curl, CURLOPT_TIMEOUT, 12);
            $json = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);

            if ($json === false || $errorno) {
                return json_encode(["error" => 1, "message" => "Không thể kết nối tới API", "description" => "cURL error $errorno: $error"]);
            }
            if($httpcode != 200) {
                return json_encode(["error" => 1, "message" => "Không thể kết nối tới API", "description" => "HTTP error $httpcode"]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }
}