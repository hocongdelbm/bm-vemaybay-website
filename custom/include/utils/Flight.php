<?php
class Flight {
    /**
     * Get airline name by code
     * 
     * @param string $code Airline code
     * @return array
     */
    public static function getAirline($code) {
        $json = file_get_contents('custom/json_files/airlines.json');
        $arr = json_decode($json, true);
        if($code == 'ALL') return $json;

        unset($json);
        if($arr && !is_null($arr) && !empty($arr)) {
            $mappingAirlineCode = ['VJA' => 'VJ', 'VNA' => 'VN', 'VNP' => 'VN', 'BBA' => 'QH', 'VTA' => 'VU'];
            $code = $mappingAirlineCode[$code] ?? $code;
            $name = isset($arr[$code]) ? $arr[$code] : 'Unknown';
            unset($arr);
            return $name;
        }
        return 'Unknown';
    }

    /**
     * Get info airport by location code
     * 
     * @param string $code SGN, BKK,...
     * @return array [AirPortCode, AirPortName, CityName, PostalCode, Prefix, RegionCode, Region, International:bool, GeoCountryId:int, GeoCountryName]
     */
    public static function getAirport($code) {
        $json = file_get_contents('custom/json_files/airports.json');
        $arr = json_decode($json, true);
        if($code == 'ALL') return $json;
        unset($json);
        if($arr && !is_null($arr) && !empty($arr)) {
            $info = isset($arr[$code]) ? $arr[$code] : [];
            unset($arr);
            return $info;
        }
        return [];
    }
}