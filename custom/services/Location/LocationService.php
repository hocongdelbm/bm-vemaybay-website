<?php
namespace custom\services\Location;

class LocationService {
    private array $providers = [];

    public function __construct() {
        $this->providers = [
            new APIOpenCage(),
            new APINominatim(),
        ];
    }

    public function reverseGeocode($lat, $long): array {
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
}