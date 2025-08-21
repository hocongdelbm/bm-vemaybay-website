<?php
class FareClass {
    /**
     * 
     * @param string $airlineCode
     * @param string $fareBasis
     * @return string
     */
    public static function getFareClass($airlineCode, $fareBasis, $isFull = 0) {
        $letter = strtoupper(strlen($fareBasis) > 1 ? substr($fareBasis, 0, 1) : $fareBasis);

        if(in_array($airlineCode, ['VN', 'BL', 'VNA', 'VNP'])) {
            if(in_array($letter, ['J', 'C'])) return 'Business Flex';
            elseif(in_array($letter, ['D', 'I'])) return 'Business Classic';
            elseif(in_array($letter, ['W', 'Z'])) return 'Premium Eco Flex';
            elseif(in_array($letter, ['U'])) return 'Premium Eco Classic';
            elseif(in_array($letter, ['B', 'M'])) return 'Eco Flex';
            elseif(in_array($letter, ['S', 'H', 'K', 'L'])) return 'Eco Classic';
            elseif(in_array($letter, ['P', 'A', 'G'])) return 'Eco Super Lite';
            return 'Eco Lite';
        }
        elseif(in_array($airlineCode, ['VJ', 'VJA'])) {
            if (stripos($fareBasis, "SBOSS") !== false
                || stripos($fareBasis, "SKYBOSS") !== false
                || stripos($fareBasis, "SKY") !== false
            )
                return "Sky Boss";
            if (stripos($fareBasis, "BOSS") !== false
                || stripos($fareBasis, "BUSINESS") !== false
                || stripos($fareBasis, "BUZ") !== false
                || stripos($fareBasis, "BUS") !== false
            )
                return "Business";
            if (stripos($fareBasis, "DLX") !== false || stripos($fareBasis, "DELUXE") !== false)
                return "Deluxe";
            
            return "Eco";
        }
        elseif(in_array($airlineCode, ['QH', 'BBA'])) {
            if (preg_match('/BF.*0F/i', $fareBasis) || in_array($letter, ['J']))
                return 'Business Flex';
            elseif (preg_match('/BS.*0F/i', $fareBasis) || in_array($letter, ['I', 'C']))
                return 'Business Smart';
            elseif (preg_match('/EF.*0F/i', $fareBasis) || in_array($letter, ['B', 'W']))
                return 'Eco Flex';
            elseif (preg_match('/ES.*0F/i', $fareBasis) || in_array($letter, ['M', 'N', 'Q', 'L', 'H', 'K', 'T']))
                return 'Eco Smart';
            // return 'Hot Deal';
            return 'Eco Saver Max';
        }
        elseif(in_array($airlineCode, ['VU', 'VTA'])) {
            if (stripos($fareBasis, "PRVNP2") !== false || (stripos($fareBasis, "PREMIUM") !== false && stripos($fareBasis, "ECO") === false) || in_array($letter, ["W"]))
                return "Premium";
            elseif (stripos($fareBasis, "PRVNP") !== false || in_array($letter, ["J"]))
                return "Premium Eco";
            elseif (stripos($fareBasis, "FLEOW") !== false || in_array($letter, ["O", "N", "S", "Y", "Q", "R"]))
                return "Eco Flex";
            return "Eco Saver";
        }

        return '';
    }
}