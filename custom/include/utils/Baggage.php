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
     * Parse baggage description to array
     * 
     * @param string $str 1 kiện x 23kg, 23kg,...
     * @return array [package, weight]
     */
    public static function parsePackage(string $str): array {
        $str = strtolower($str);

        // Extract package count
        $packages = null;
        if (preg_match('/(\d+)\s*packages?/', $str, $matches)) {
            $packages = intval($matches[1]);
        }
        else if (preg_match('/(\d+)\s*kiện?/', $str, $matches)) {
            $packages = intval($matches[1]);
        }

        // Extract weight
        $weight = null;
        if (preg_match('/(\d+)\s*kg/', $str, $matches)) {
            $weight = intval($matches[1]);
        }

        return [
            'package' => $packages,
            'weight' => $weight
        ];
    }
}