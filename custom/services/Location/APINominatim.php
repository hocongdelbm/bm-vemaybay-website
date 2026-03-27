<?php
namespace Custom\Services\Location;

/**
 * Open-source geocoding with OpenStreetMap data
 * Documentation: https://nominatim.org/release-docs/latest/
 */
class APINominatim {
    private $endpoint;

    public function __construct() {
        global $sugar_config;
        $this->endpoint = $sugar_config['opencage']['endpoint'] ?? '';
    }

    /**
     * Reverse geocoding
     * 
     * @param string $lat
     * @param string $long
     * @param string $format Response format: json, xml, jsonv2, geojson, geocodejson
     * @return string
     */
    public function reverseGeocode(string $lat, string $long, string $format = 'json') {
        $path = "reverse";
        $params = [
            "lat" => $lat,
            "lon" => $long,
            "format" => $format,
        ];
        $url = "{$this->endpoint}/{$path}?" . http_build_query($params);
        return $this->sendHTTPRequest("GET", $url);
    }

    /**
     * Normalise a raw Nominatim result into the shared location schema.
     *
     * @param array $rawData Data attribute from reverseGeocode()
     * @return array
     */
    public function normaliseReverseGeocode(array $rawData): array {
        $address = $rawData["address"] ?? [];
        return [
            "provider"      => get_class($this),
            "country_code"  => $address["country_code"] ?? "",
            "country"       => $address["country"] ?? "",
            "city"          => $address["city"] ?? $address["state"] ?? '',
            "ward"          => $address["suburb"] ?? "",
            "road"          => $address["road"] ?? "",
            "neighbourhood" => $address["neighbourhood"] ?? "",
            "house_number"  => $address["house_number"] ?? "",
            "building"      => $address["building"] ?? "",
            "address"       => $rawData["display_name"] ?? "",
            "lat"           => $rawData["lat"] ?? "",
            "lng"           => $rawData["lon"] ?? "",
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
                    else if ($httpCode >= 200 && $httpCode < 300) {
                        $status = 1;
                        $data = $responseArr;
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