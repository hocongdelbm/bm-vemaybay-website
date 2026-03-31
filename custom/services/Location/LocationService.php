<?php
namespace custom\services\Location;

class LocationService
{
    private array $providers = [];

    public function __construct()
    {
        $this->providers = [
            new APIOpenCage(),
            new APINominatim(),
        ];
    }

    public function reverseGeocode($lat, $long): array
    {
        $lat = (string) $lat;
        $long = (string) $long;

        $envelope = [];
        foreach ($this->providers as $provider) {
            $envelope = json_decode($provider->reverseGeocode($lat, $long), true);
            if (isset($envelope['status']) && $envelope['status'] === 1) {
                $envelope['data'] = $provider->normaliseReverseGeocode($envelope['data']);
                break;
            }
        }

        return $envelope;
    }

    public function getLocationByIp(string $ip): array
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://ipinfo.io/{$ip}/json",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 16,
            ]);
            $response = curl_exec($curl);
            curl_close($curl);

            if (!$response) {
                return ["status" => 0, "message" => "Không thể kết nối IP API", "data" => null];
            }

            $data = json_decode($response, true);
            if (!$data || isset($data['error'])) {
                return ["status" => 0, "message" => "Không tìm thấy vị trí từ IP", "data" => null];
            }

            $city = $data['city'] ?? '';
            $city = trim(str_replace("City", "", $city));

            return [
                "status" => 1,
                "message" => "Success",
                "data" => [
                    "city" => $city,
                ]
            ];
        } catch (\Throwable $th) {
            return ["status" => 0, "message" => $th->getMessage(), "data" => null];
        }
    }
}