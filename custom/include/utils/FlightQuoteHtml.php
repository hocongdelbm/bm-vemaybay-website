<?php

/**
 * Flight quote card markup for the /baogia slash command (chat websocket).
 *
 * Port of client/src/common/FlightQuoteHtml.js from the chat repo, keeping only
 * the "full price" mode: the slash preview panel has no basic/full toggle, so
 * the displayed price is always fare price + service fee.
 *
 * Every interpolated value goes through self::esc() — the chat side only runs a
 * hardening filter, escaping is this side's contract.
 */
class FlightQuoteHtml
{
    const MAX_FLIGHTS = 6;

    const AIRLINE_NAMES = [
        'VJ' => 'VietjetAir',
        'VN' => 'VNA',
        'QH' => 'Bamboo',
        '9G' => 'SunPhuQuoc',
        'VU' => 'Vietravel',
    ];

    public static function esc($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function vnd($number)
    {
        return number_format((float) $number, 0, ',', '.');
    }

    /** "2026-07-20" -> "20/7"; anything else is returned untouched. */
    public static function shortDate($iso)
    {
        if (!is_string($iso) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $iso, $m)) {
            return is_string($iso) ? $iso : '';
        }

        return ((int) $m[3]) . '/' . ((int) $m[2]);
    }

    /**
     * Fare System response `data` -> flat flight rows.
     * Domestic: { dep: [...], ret: [...] }. International: [ { dep, ret } ... ].
     */
    public static function mapFlights($data)
    {
        if (!is_array($data) || $data === []) {
            return [];
        }

        $isList = array_keys($data) === range(0, count($data) - 1);
        if ($isList) {
            return self::mapInternational($data);
        }

        return self::mapDomestic($data);
    }

    private static function mapInternational(array $options)
    {
        $flights = [];
        foreach (array_slice($options, 0, self::MAX_FLIGHTS) as $opt) {
            if (!is_array($opt)) {
                continue;
            }
            $dep = is_array($opt['dep'] ?? null) ? $opt['dep'] : [];
            $ret = is_array($opt['ret'] ?? null) ? $opt['ret'] : null;

            $price = (float) ($opt['totalPrice'] ?? 0);
            if ($price <= 0) {
                $price = (float) ($opt['adtPrice'] ?? 0);
            }
            // Rows priced at 0 are dropped instead of shown — a quote with a
            // blank price is worse than a shorter list.
            if ($price <= 0) {
                continue;
            }

            $flightNo = array_filter([$dep['flightNo'] ?? '', $ret['flightNo'] ?? '']);
            $flights[] = [
                'airline' => self::airlineName($dep),
                'flightNo' => implode('/', $flightNo),
                'depTime' => self::timeRange($dep),
                'arrTime' => $ret ? self::timeRange($ret) : '',
                'price' => $price,
                'direction' => 0,
                'roundTrip' => $ret !== null,
            ];
        }

        return $flights;
    }

    private static function mapDomestic(array $data)
    {
        $flights = [];
        foreach (['dep' => 0, 'ret' => 1] as $key => $direction) {
            foreach ((array) ($data[$key] ?? []) as $f) {
                if (!is_array($f)) {
                    continue;
                }
                $price = (float) ($f['price'] ?? 0);
                if ($price <= 0) {
                    continue;
                }
                $flights[] = [
                    'airline' => self::airlineName($f),
                    'flightNo' => (string) ($f['flightNo'] ?? ''),
                    'depTime' => (string) ($f['depTime'] ?? ''),
                    'arrTime' => (string) ($f['arvTime'] ?? ($f['arrTime'] ?? '')),
                    'price' => $price,
                    'direction' => $direction,
                    'roundTrip' => false,
                ];
            }
        }

        $outbound = array_values(array_filter($flights, function ($f) {
            return $f['direction'] !== 1;
        }));
        $inbound = array_values(array_filter($flights, function ($f) {
            return $f['direction'] === 1;
        }));

        if ($inbound) {
            return array_merge(array_slice($outbound, 0, 3), array_slice($inbound, 0, 3));
        }

        return array_slice($flights, 0, self::MAX_FLIGHTS);
    }

    private static function airlineName(array $flight)
    {
        $code = (string) ($flight['airlineCode'] ?? '');

        return self::AIRLINE_NAMES[$code] ?? ((string) ($flight['airline'] ?? '') ?: $code);
    }

    private static function timeRange(array $leg)
    {
        $dep = (string) ($leg['depTime'] ?? '');
        $arr = (string) ($leg['arvTime'] ?? '');

        return ($dep !== '' && $arr !== '') ? $dep . ' - ' . $arr : $dep;
    }

    /**
     * @param array $route   depCode, desCode, depDate (ISO), retDate (ISO)
     * @param array $flights rows from mapFlights()
     * @param int   $serviceFee added on top of every flight price
     */
    public static function build(array $route, array $flights, $serviceFee = 0)
    {
        $serviceFee = (float) $serviceFee;
        $shortDate = self::shortDate($route['depDate'] ?? '');
        $retShortDate = self::shortDate($route['retDate'] ?? '');

        $depLabel = ($route['depCode'] ?? '') ?: 'Điểm đi';
        $desLabel = ($route['desCode'] ?? '') ?: 'Điểm đến';
        $baseRouteLabel = $depLabel . ' → ' . $desLabel;
        $routeLabel = $shortDate ? $baseRouteLabel . ' ' . $shortDate : $baseRouteLabel;

        $hasInbound = false;
        foreach ($flights as $f) {
            if ($f['direction'] === 1) {
                $hasInbound = true;
                break;
            }
        }

        $context = [
            'shortDate' => $shortDate,
            'retShortDate' => $retShortDate,
            'hasInbound' => $hasInbound,
            'serviceFee' => $serviceFee,
        ];

        if ($hasInbound) {
            $outbound = array_filter($flights, function ($f) {
                return $f['direction'] !== 1;
            });
            $inbound = array_filter($flights, function ($f) {
                return $f['direction'] === 1;
            });
            $rows = self::section("Chiều đi ($depLabel → $desLabel)", $outbound, '0', $context)
                . self::section("Chiều về ($desLabel → $depLabel)", $inbound, '1', $context);
        } else {
            $rows = '';
            foreach ($flights as $f) {
                $rows .= self::row($f, $context);
            }
        }

        $blockId = 'sw_quote_' . uniqid();

        return '<div id="' . self::esc($blockId) . '" class="sw-quote" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08);overflow:hidden;border:1px solid #eaeaea;margin:4px 0;">'
            . '<div style="background:#1976d2;color:#fff;padding:10px 12px;text-align:center;">'
            . '<div style="font-weight:600;font-size:13px;margin-bottom:2px;opacity:0.9">' . self::esc('Đã bao gồm thuế, phí (cả phí dịch vụ)') . '</div>'
            . '<div style="font-size:15px;font-weight:700;">' . self::esc($routeLabel) . '</div>'
            . '</div>'
            . '<div style="padding:0 12px;">'
            . $rows
            . '</div>'
            . '<div style="padding:10px 12px;background:#f9f9f9;font-size:12px;color:#555;text-align:center;border-top:1px solid #eee;">'
            . 'Mình bấm nút "<strong>Chọn</strong>" ở chuyến bay ưng ý để nhân viên hỗ trợ đặt vé ngay và luôn ạ.'
            . '</div>'
            . '</div>';
    }

    private static function section($label, $list, $dir, array $ctx)
    {
        if (!$list) {
            return '';
        }
        $rows = '';
        foreach ($list as $f) {
            $rows .= self::row($f, $ctx);
        }

        return '<div class="sw-quote-section" data-dir="' . self::esc($dir) . '">'
            . '<div style="font-weight:bold;font-size:13px;color:#1a1a1a;margin-top:8px;padding-top:8px;">' . self::esc($label) . '</div>'
            . $rows
            . '</div>';
    }

    private static function button($quote, $label = 'Chọn')
    {
        return '<button type="button" class="sw-quote-select sw-quote-flight" data-quote="' . self::esc($quote) . '" style="background:#e3f2fd;color:#1976d2;border:1px solid #bbdefb;border-radius:6px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.2s;white-space:nowrap;">' . self::esc($label) . '</button>';
    }

    private static function chip($text)
    {
        return '<span style="font-weight:600; background:#f5f5f5; padding:2px 6px; border-radius:4px;white-space:nowrap;">' . self::esc($text) . '</span>';
    }

    private static function row(array $f, array $ctx)
    {
        $airlineDisplay = $f['airline'] !== '' ? $f['airline'] : 'Chuyến bay';
        $isReturn = $f['direction'] === 1;
        $rowDate = ($isReturn && $ctx['retShortDate']) ? $ctx['retShortDate'] : $ctx['shortDate'];
        $name = trim($airlineDisplay . ' ' . $f['flightNo']);
        $displayPrice = $f['price'] + $ctx['serviceFee'];

        $nameHtml = '<span style="font-weight:700;font-size:14px;color:#1a1a1a;word-break:break-word;">'
            . self::esc($f['flightNo'] !== '' ? $f['flightNo'] : $airlineDisplay) . '</span>';
        $priceHtml = '<div style="font-weight:700;font-size:14px;color:#d32f2f;white-space:nowrap;">' . self::esc(self::vnd($displayPrice)) . '</div>'
            . '<div style="font-size:11px;color:#9e9e9e;white-space:nowrap;">Giá cuối cùng</div>';

        if ($f['roundTrip']) {
            $legLine = function ($label, $date, $range) {
                $part = $label . ($date ? ' ' . $date : '');
                if ($range) {
                    $pieces = explode(' ', $range);
                    $part .= ' lúc ' . $pieces[0];
                }

                return $part;
            };
            $quoteText = 'Tôi chọn vé khứ hồi ' . $name . ', '
                . $legLine('đi', $ctx['shortDate'], $f['depTime']) . ', '
                . $legLine('về', $ctx['retShortDate'], $f['arrTime'])
                . ', giá đầy đủ ' . self::vnd($displayPrice);

            $legRow = function ($label, $date, $range) {
                if (!$range) {
                    return '';
                }

                return '<div style="display:flex;align-items:center;gap:6px;">'
                    . '<span style="font-weight:700;color:#1976d2;min-width:48px;white-space:nowrap;">' . self::esc($label . ($date ? ' ' . $date : '')) . '</span>'
                    . self::chip($range)
                    . '</div>';
            };

            return '<div style="padding:12px 0;border-bottom:1px solid #f0f0f0;">'
                . '<div style="display:flex;align-items:baseline;justify-content:space-between;gap:8px;">'
                . '<div style="display:flex;align-items:center;flex-wrap:wrap;gap:4px;min-width:0;">' . $nameHtml . '</div>'
                . '<div style="text-align:right;flex-shrink:0;">' . $priceHtml . '</div>'
                . '</div>'
                . '<div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:8px;margin-top:6px;">'
                . '<div style="font-size:12px;color:#424242;display:flex;flex-direction:column;gap:4px;">'
                . $legRow('Đi', $ctx['shortDate'], $f['depTime'])
                . $legRow('Về', $ctx['retShortDate'], $f['arrTime'])
                . '</div>'
                . '<div style="margin-left:auto;">' . self::button($quoteText) . '</div>'
                . '</div>'
                . '</div>';
        }

        $dirStr = $ctx['hasInbound'] ? ($isReturn ? ' (Chiều về)' : ' (Chiều đi)') : '';
        $dateStr = $rowDate ? ' ngày ' . $rowDate : '';
        $timeStr = $f['depTime'] ? ' lúc ' . $f['depTime'] : '';
        $quoteText = 'Tôi chọn chuyến bay ' . $name . $dirStr . $dateStr . $timeStr . ', giá đầy đủ ' . self::vnd($displayPrice);

        $timesHtml = '';
        if ($f['depTime']) {
            $timesHtml = '<div style="font-size:12px;color:#424242;display:flex;align-items:center;gap:4px;flex-wrap:wrap;">'
                . self::chip($f['depTime'])
                . ($f['arrTime'] ? '<span style="color:#bdbdbd;">→</span> ' . self::chip($f['arrTime']) : '')
                . '</div>';
        }

        return '<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f0f0f0;">'
            . '<div style="flex:1 1 auto; padding-right: 8px; min-width:0;">'
            . '<div style="display:flex; align-items:center; flex-wrap:wrap; gap:4px; margin-bottom:6px;">' . $nameHtml . '</div>'
            . $timesHtml
            . '</div>'
            . '<div style="text-align:right; display:flex; flex-direction:column; align-items:flex-end; gap:6px; flex-shrink:0;">'
            . $priceHtml
            . self::button($quoteText)
            . '</div>'
            . '</div>';
    }
}

// Self-check: php custom/include/utils/FlightQuoteHtml.php
if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    $domestic = FlightQuoteHtml::mapFlights([
        'dep' => [
            ['airlineCode' => 'VJ', 'flightNo' => 'VJ120', 'depTime' => '06:00', 'arvTime' => '08:10', 'price' => 1000000],
            ['airlineCode' => 'VN', 'flightNo' => '<b>x</b>', 'depTime' => '09:00', 'price' => 0],
        ],
        'ret' => [
            ['airlineCode' => 'VJ', 'flightNo' => 'VJ121', 'depTime' => '18:00', 'arvTime' => '20:10', 'price' => 1100000],
        ],
    ]);
    assert(count($domestic) === 2, 'row priced 0 is dropped');
    assert($domestic[0]['airline'] === 'VietjetAir');
    assert($domestic[1]['direction'] === 1);

    $html = FlightQuoteHtml::build(
        ['depCode' => 'HAN', 'desCode' => 'SGN', 'depDate' => '2026-07-20', 'retDate' => '2026-07-25'],
        $domestic,
        100000
    );
    assert(strpos($html, '1.100.000') !== false, 'price + service fee');
    assert(strpos($html, 'Chiều đi (HAN → SGN)') !== false);
    assert(strpos($html, 'VJ121 (Chiều về) ngày 25/7') !== false);
    assert(strpos($html, 'Giá cuối cùng') !== false);

    $international = FlightQuoteHtml::mapFlights([[
        'dep' => ['airlineCode' => 'CI', 'airline' => 'China Airlines', 'flightNo' => 'CI784', 'depTime' => '17:30', 'arvTime' => '22:05'],
        'ret' => ['airlineCode' => 'CI', 'flightNo' => 'CI783', 'depTime' => '13:45', 'arvTime' => '16:20'],
        'totalPrice' => 8737000,
    ]]);
    assert($international[0]['roundTrip'] === true);
    assert($international[0]['flightNo'] === 'CI784/CI783');
    $interHtml = FlightQuoteHtml::build(['depCode' => 'SGN', 'desCode' => 'TPE', 'depDate' => '2026-07-12', 'retDate' => '2026-07-15'], $international, 0);
    assert(strpos($interHtml, 'Chiều đi') === false, 'round-trip bundle is not split into sections');
    assert(strpos($interHtml, 'Đi 12/7') !== false && strpos($interHtml, '17:30 - 22:05') !== false);

    $xss = FlightQuoteHtml::build(
        ['depCode' => '"><script>alert(1)</script>', 'desCode' => 'SGN', 'depDate' => '2026-07-20'],
        FlightQuoteHtml::mapFlights(['dep' => [['airline' => '<img onerror=x>', 'price' => 1]]]),
        0
    );
    assert(strpos($xss, '<script>') === false && strpos($xss, '<img') === false, 'user input is escaped');
    assert(strpos($xss, '&lt;script&gt;') !== false && strpos($xss, '&lt;img onerror=x&gt;') !== false);

    echo "FlightQuoteHtml self-check OK\n";
}
