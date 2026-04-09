<?php

/**
 * Đọc số tiền tiếng Việt
 */
class ReadNumberInWords
{
    protected static $numberWords = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];

    private static function readTens($number, $full)
    {
        $text = '';
        $tens = floor($number / 10);
        $units = $number % 10;

        if ($tens > 1) {
            $text = " " . self::$numberWords[$tens] . " mươi";
            if ($units === 1) {
                $text .= " mốt";
            }
        } elseif ($tens == 1) {
            $text = " mười";
            if ($units === 1) {
                $text .= " một";
            }
        } elseif ($full && $units > 0) {
            $text = " lẻ";
        }

        if ($units == 5 && $tens > 1) {
            $text .= " lăm";
        } elseif ($units > 1 || ($units == 1 && $tens == 0)) {
            $text .= " " . self::$numberWords[$units];
        }
        return $text;
    }

    private static function readHundreds($number, $full)
    {
        $text = '';
        $hundreds = floor($number / 100);
        $remainder = $number % 100;

        if ($full || $hundreds > 0) {
            $text = " " . self::$numberWords[$hundreds] . " trăm";
            $text .= self::readTens($remainder, true);
        } else {
            $text = self::readTens($remainder, false);
        }

        return $text;
    }

    private static function readMillions($number, $full)
    {
        $text = '';
        $millions = floor($number / 1000000);
        $number %= 1000000;

        if ($millions > 0) {
            $text = self::readHundreds($millions, $full) . " triệu";
            $full = true;
        }

        $thousands = floor($number / 1000);
        $number %= 1000;

        if ($thousands > 0) {
            $text .= self::readHundreds($thousands, $full) . " nghìn";
            $full = true;
        }

        if ($number > 0) {
            $text .= self::readHundreds($number, $full);
        }

        return $text;
    }

    public static function readNumber($number)
    {
        if (empty($number) || $number == 0) {
            return self::$numberWords[0];
        }

        if ($number == 0) {
            return ucfirst(self::$numberWords[0]);
        }

        $text = '';
        $suffix = '';

        do {
            $billion = $number % 1000000000;
            $number = floor($number / 1000000000);
            $block = self::readMillions($billion, $number > 0);

            $text = $block . $suffix . $text;
            $suffix = " tỷ";
        } while ($number > 0);

        return ucfirst(trim($text));
    }
}
