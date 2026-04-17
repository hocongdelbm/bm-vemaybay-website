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

    /**
     * Get nice duration from duration
     * 
     * @param int $duration_seconds
     * @return string
     */
    public static function getNiceDuration($duration_seconds) {
        $nice_duration = '';
        $days = floor($duration_seconds / 86400);
        $duration_seconds -= $days * 86400;
        $hours = floor($duration_seconds / 3600);
        $duration_seconds -= $hours * 3600;
        $minutes = floor($duration_seconds / 60);
        $seconds = $duration_seconds - $minutes * 60;

        if ($days > 0) {
            $nice_duration .= (int)$days . 'd';
        }
        if ($hours > 0) {
            $nice_duration .= ' ' . (int)$hours . 'h';
        }
        if ($minutes > 0) {
            $nice_duration .= ' ' . (int)$minutes . 'm';
        }
        if ($seconds > 0) {
            $nice_duration .= ' ' . $seconds . 's';
        }

        return trim($nice_duration);
    }

    /**
     * Check location code is international
     * 
     * @param string $code
     * @return true
     */
    public static function isDomesticLocation($code) {
        return isset($GLOBALS['app_list_strings']['domestic_airport_list'][$code]);
    }

    /**
     * Check location code is international
     * 
     * @param string $code
     * @return true
     */
    public static function isInterLocation($code) {
        return !isset($GLOBALS['app_list_strings']['domestic_airport_list'][$code]);
    }
}