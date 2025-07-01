<?php
class PhuongNamAPI {
    private $ENDPOINT;
    private $API_KEY;

    public function __construct() {
        $this->ENDPOINT = "https://data01.timchuyenbay.vn/api/v3";
        // $this->ENDPOINT = "https://data01.timchuyenbay.net/api/v1";
        $this->API_KEY = "1r2Lm4Rof1KsOM_SHbiCx1zx59@G54TmQq1T7XY5fK85OG28S+";
    }

    public function getSessionKey() {
        try {
            $headers = ["API-Key: $this->API_KEY"];

            $curl = curl_init();
            if ($curl === false) return json_encode(["error" => 1, "message" => "Lỗi hệ thống", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/getSessionKey");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
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

    public function searchFlights($airlineCode, $depCode, $desCode, $departDate, $returnDate, $adt, $chd = 0, $inf = 0, $cabin = 'M') {
        try {
            $headers = [
                "Content-Type: multipart/form-data",
                "API-Key: $this->API_KEY",
            ];
            $requestBody = [
                "airlineCode"   => $airlineCode,
                "depCode"       => $depCode,
                "desCode"       => $desCode,
                "departDate"    => $departDate,
                "returnDate"    => $returnDate,
                "adt"           => $adt,
                "chd"           => $chd,
                "inf"           => $inf,
                "options"       => json_encode(['cabin' => $cabin]),
                "isLive"        => 1
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode(["error" => 1, "message" => "Lỗi hệ thống", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/getFlights");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            $json = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);

            if ($json === false || $errorno) {
                return json_encode(["error" => 1, "message" => "Can not connect to API", "description" => "cURL error $errorno: $error"]);
            }
            if($httpcode != 200) {
                return json_encode([
                    "error" => 1,
                    "message" => "Can not connect to API",
                    "data" => is_string($json) ? json_decode($json, true) : $json,
                    "description" => "HTTP error $httpcode"
                ]);
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

    public function detectCabin($airlineCode, $ticketClass) {
        if($airlineCode == 'VJ') {
            // M: Phổ thông
            // W: Phổ thông đặc biệt (Deluxe, Skyboss)
            // C: thương gia: Business
            $cMatches = ['boss', 'bus'];
            $wMatches = ['dlx', 'deluxe', 'sboss', 'sky'];
            foreach ($wMatches as $w) if (stripos($ticketClass, $w) !== false) return 'W';
            foreach ($cMatches as $c) if (stripos($ticketClass, $c) !== false) return 'C';
            return 'M';
        }
        elseif($airlineCode == 'VN') {
            // M: Phổ thông
            // W: Phổ thông đặc biệt
            // C: Thương gia
            // F: Hạng nhất
            $last_character = substr($ticketClass, -1);
            if(in_array($last_character, ['J','C','D', 'I'])) return 'C';
            elseif(in_array($last_character, ['W','Z','U'])) return 'W';
            elseif(in_array($last_character, ['B','M','S','H','K','L','Q','N','R','T','E','P','A','G'])) return 'M';
            else return 'M';
        }
        elseif($airlineCode == 'QH') {
            // M: Phổ thông (Economy Smart, Economy Saver, Hot Deal)
            // W: Phổ thông đặc biệt (Economy Flex)
            // C: thương gia (Business Smart, Business Flex)
            $cMatches = ['buz', 'bus'];
            $wMatches = ['flex'];
            foreach ($cMatches as $c) if (stripos($ticketClass, $c) !== false) return 'C';
            foreach ($wMatches as $w) if (stripos($ticketClass, $w) !== false) return 'W';
            return 'M';
        }
        elseif($airlineCode == 'VU') {
            // M: Phổ thông (Economy Saver, Economy Flex)
            // W: Phổ thông đặc biệt (Economy Premium)
            $wMatches = ['pre'];
            foreach ($wMatches as $w) if (stripos($ticketClass, $w) !== false) return 'W';
            return 'M';
        }
    }
}