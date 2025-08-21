<?php
class Baggage {
    /**
     * Render available checked baggage
     * 
     * @param string $str 23_1
     * @return string
     */
    public static function renderAvailableBaggage($str, $language = 'vi') {
        if(!$str || empty($str)) return '';

        $arr = explode('_', $str);
        if(isset($arr[1]) && !empty($arr[1])) 
            return $language == "en" ? "{$arr[1]} packages of {$arr[0]}kg available" : "Đã có sẵn {$arr[1]} kiện {$arr[0]}kg";
        return $language == "en" ? "{$arr[0]}kg available" : "Đã có sẵn {$arr[0]}kg";
    }

    /**
     * Get available checked baggage info
     * 
     * @param string $airlineCode
     * @param string $fareClass
     * @param string $passengerType ADT, CHD, INF
     * @return string
     */
    public static function getAvailableCheckedBaggageInfo($airlineCode, $fareClass, $passengerType) {
        $json = file_get_contents('custom/json_files/checkedBaggage.json');
        $data = json_decode($json, true);
        $result = $data[$airlineCode][$fareClass]['pass_type']['='][strtoupper($passengerType)] ?? [];
        return $result['value'] ?? '';
    }
}