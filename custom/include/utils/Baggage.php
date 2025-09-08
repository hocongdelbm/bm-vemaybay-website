<?php
class Baggage {
    /**
     * Render available checked baggage
     * 
     * @param string $str 23_1, 1x23, 2T23
     * @return string
     */
    public static function renderAvailableBaggage($str, $language = 'vi') {
        if(!$str || empty($str)) return '';

        if(strlen($str) < 3) {
            if((int)$str < 2) return $language == "en" ? "$str package" : "$str kiện";
            elseif((int)$str < 6) return $language == "en" ? "$str packages" : "$str kiện";
            elseif((int)$str > 5) return $language == "en" ? "{$str}kg" : "{$str}kg";
        }

        if(stripos($str, '_') !== false) {
            $arr = explode('_', $str);
            $p = (int)($arr[1] ?? 0);
            $w = (int)($arr[0] ?? 0);
            $packageTextEN = $p > 1 ? "packages" : "package";

            if($p * $w > 0) return $language == "en" ? "$p $packageTextEN x {$w}kg" : "$p kiện x {$w}kg";
            elseif($w > 0) return "{$w}kg";
            elseif($p > 0) return $language == "en" ? "$p $packageTextEN" : "$p kiện";
        }
        elseif(stripos($str, 'x') !== false) {
            $arr = explode('x', $str);
            $p = (int)($arr[0] ?? 0);
            $w = (int)($arr[1] ?? 0);
            $packageTextEN = $p > 1 ? "packages" : "package";

            if($p * $w > 0) return $language == "en" ? "$p $packageTextEN x {$w}kg" : "$p kiện x {$w}kg";
            elseif($w > 0) return "{$w}kg";
            elseif($p > 0) return $language == "en" ? "$p $packageTextEN" : "$p kiện";
        }
        elseif(stripos($str, 'T') !== false) {
            $arr = explode('T', $str);
            $p = (int)($arr[0] ?? 0);
            $w = (int)($arr[1] ?? 0);
            $packageTextEN = $p > 1 ? "packages" : "package";

            if($p * $w > 0) return $language == "en" ? "$p $packageTextEN total {$w}kg" : "$p kiện tổng {$w}kg";
            elseif($w > 0) return "{$w}kg";
            elseif($p > 0) return $language == "en" ? "$p $packageTextEN" : "$p kiện";
        }
        return $str;
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