<?php
class Baggage {
    /**
     * Render available checked baggage as HTML
     * 
     * @param string $str 23_1
     * @return string HTML
     */
    public static function renderAvailableBaggage($str) {
        if(!$str || empty($str)) return '';

        $arr = explode('_', $str);
        if(isset($arr[1]) && !empty($arr[1])) return "Đã có sẵn " . $arr[1] . " kiện " . $arr[0] . "kg";
        return "Đã có sẵn " . $arr[0] . "kg";
    }
}