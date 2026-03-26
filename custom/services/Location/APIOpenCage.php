<?php
namespace Custom\Services\Location;

/**
 * OpenCage Geocoding API
 * Documentation: https://opencagedata.com/api
 * 2500 requests per day
 */
class APIOpenCage {
    private $endpoint;
    private $apiKey;

    public function __construct() {
        global $sugar_config;
        $this->endpoint = $sugar_config['opencage']['endpoint'] ?? '';
        $this->apiKey = $sugar_config['opencage']['api_key'] ?? '';
    }

    /**
     * Reverse geocoding
     * 
     * @param string $lat
     * @param string $long
     * @param string $format Response format: json, geojson, xml, google-v3-json
     * @return string
     */
    public function reverseGeocode(string $lat, string $long, string $format = 'json') {
        $path = "geocode/v1/$format";
        $params = [
            "q" => "$lat,$long",
            "key" => $this->apiKey,
            "language" => "vi",
            "no_annotations" => 1,
            "no_dedupe" => 1,
            "limit" => 1,
        ];
        $url = "{$this->endpoint}/{$path}?" . http_build_query($params);
        return $this->sendHTTPRequest("GET", $url);
    }

    /**
     * Normalise a raw OpenCage result into a shared location schema.
     *
     * @param array $rawData Data attribute from reverseGeocode()
     * @return array
     */
    public function normaliseReverseGeocode(array $rawData): array {
        $result = $rawData['results'][0] ?? [];
        $comp   = $result['components'] ?? [];
        $geo    = $result['geometry'] ?? [];
        return [
            "provider"      => get_class($this),
            "country_code"  => $comp["country_code"] ?? "",
            "country"       => $comp["country"] ?? "",
            "city"          => $comp["city"] ?? $comp["state"] ?? "",
            "ward"          => $comp["suburb"] ?? "",
            "road"          => $comp["road"] ?? "",
            "neighbourhood" => $comp["neighbourhood"] ?? "",
            "house_number"  => $comp["house_number"] ?? "",
            "building"      => $comp["building"] ?? "",
            "address"       => $result["formatted"] ?? "",
            "lat"           => $geo["lat"] ?? "",
            "lng"           => $geo["lng"] ?? "",
        ];
    }

    /**
     * Send HTTP request
     * 
     * @param string $method GET, POST, PUT,...
     * @param string $url
     * @param array $header
     * @param array|string $requestBody
     * @param array $curlCustomOptions
     * @return string JSON {status, httpCode, message, data, description}
     */
    private function sendHTTPRequest($method, $url, $header = [], $requestBody = null, $curlCustomOptions = []) {
        $status = 0;
        $httpCode = 0;
        $data = null;
        $message = $description ='';

        try {
            $ch = curl_init();
            if ($ch) {
                // Default options
                $curlDefaultOptions = [
                    CURLOPT_URL => $url,
                    CURLOPT_CUSTOMREQUEST => strtoupper($method),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 20,
                ];
                // Headers
                if (!empty($headers)) {
                    $curlDefaultOptions[CURLOPT_HTTPHEADER] = $headers;
                }
                // Body handling
                if(!is_null($requestBody)) $curlDefaultOptions[CURLOPT_POSTFIELDS] = $requestBody;
                // Merge custom options (override defaults if needed)
                curl_setopt_array($ch, $curlDefaultOptions + $curlCustomOptions);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $errorNo  = curl_errno($ch);
                $errorMsg = curl_error($ch);
                curl_close($ch);

                if ($response === false || $errorNo) {
                    $message = "Cannot connect to API";
                    $description = "cURL error $errorNo: $errorMsg";
                }
                else {
                    $responseArr = json_decode($response, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $message = "Invalid JSON response";
                        $description = json_last_error_msg();
                    }
                    else {
                        $statusCode = $responseArr['status']['code'] ?? '';
                        $message    = $responseArr['status']['message'] ?? '';

                        if ($httpCode >= 200 && $httpCode < 300 && $statusCode == 200) {
                            $status = 1;
                            $data = $responseArr;
                        }
                    }
                }
            }
            else {
                $message = "System error";
                $description = "cURL initialization failed";
            }

            return json_encode([ "status" => $status, "httpCode" => $httpCode, "message" => $message, "data" => $data, "description" => $description]);
        }
        catch (\Throwable $th) {
            return json_encode([
                "status" => 0,
                "httpCode" => 500,
                "message" => "An exception error has occurred",
                "data" => null,
                "description" => "Error {$th->getCode()}: {$th->getMessage()} on line {$th->getLine()}",
            ]);
        }
    }
}