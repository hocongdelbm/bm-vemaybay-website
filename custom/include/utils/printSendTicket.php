<?php
function buildPassengerHTMLFromData($passengersData, $khuhoi, $lang)
{
    $html = '';

    if (empty($passengersData) || !is_array($passengersData)) {
        return '<tr>
    <td colspan="3" style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">Không có hành khách</td>
</tr>';
    }

    $labelOutbound = ($lang == 'en') ? 'Outbound' : 'Lượt đi';
    $labelInbound = ($lang == 'en') ? 'Inbound' : 'Lượt về';

    foreach ($passengersData as $passenger) {

        // Get PNR
        $pnr = '';
        if ($khuhoi) {
            $pnr = (!empty($passenger['pnrOutbound']) ? $passenger['pnrOutbound'] : (!empty($passenger['eticketOutbound']) ?
                $passenger['eticketOutbound'] : ''));
            $pnr .= trim($pnr) != '' ? ' - ' : '';
            $pnr .= (!empty($passenger['pnrInbound']) ? $passenger['pnrInbound'] : (!empty($passenger['eticketInbound']) ?
                $passenger['eticketInbound'] : ''));
        } else {
            $pnr = (!empty($passenger['pnrOutbound']) ? $passenger['pnrOutbound'] : (!empty($passenger['eticketOutbound']) ?
                $passenger['eticketOutbound'] : ''));
        }

        $baggageDescription = '';
        if (isset($passenger['luggage'])) {
            // if ($khuhoi) {
                if (!empty($passenger['luggage']['outbound'])) {
                    $luggageOutbound = cleanLuggageText($passenger['luggage']['outbound']);
                    $luggageOutbound = translateLuggageText($luggageOutbound, $lang);
                    $baggageDescription .= $luggageOutbound . ' (' . $labelOutbound . ')';
                }
                if (!empty($passenger['luggage']['outbound']) && !empty($passenger['luggage']['inbound'])) {
                    $baggageDescription .= ' -';
                }
                if (!empty($passenger['luggage']['inbound'])) {
                    $luggageInbound = cleanLuggageText($passenger['luggage']['inbound']);
                    $luggageInbound = translateLuggageText($luggageInbound, $lang);
                    $baggageDescription .= ($baggageDescription ? ' ' : '') . $luggageInbound . ' (' . $labelInbound . ')';
                }
            // } else {
            //     $baggageDescription = cleanLuggageText($passenger['luggage']['outbound'] ?? '');
            //     $baggageDescription = translateLuggageText($baggageDescription, $lang);
            // }
        }

        // Build HTML row
        $html .= '<tr>
    <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">' . htmlspecialchars($passenger['fullname']) . '
    </td>
    <td align="center" style="border:1px solid #ccc; padding: 10px 7px;">' . strtoupper($pnr) . '</td>
    <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">' . $baggageDescription . '</td>
</tr>';
    }

    return $html;
}
function cleanLuggageText($text)
{
    $text = strip_tags($text);

    $text = preg_replace('/\s*\([^)]*\)/', '', $text);

    $text = preg_replace('/Giá bán:.*?VND/i', '', $text);
    $text = preg_replace('/Giá mua:.*?VND/i', '', $text);
    $text = preg_replace('/Nhà cung cấp:.*?(\n|$)/i', '', $text);

    $text = preg_replace('/Lượt đi:/i', '', $text);
    $text = preg_replace('/Lượt về:/i', '', $text);
    $text = preg_replace('/Outbound:/i', '', $text);
    $text = preg_replace('/Inbound:/i', '', $text);

    $text = preg_replace('/Thêm\s+/i', '+ ', $text);

    $text = preg_replace('/\s+/', ' ', $text);

    $text = trim($text);

    return $text;
}

function translateLuggageText($text, $lang)
{
    if ($lang != 'en') {
        return $text;
    }

    $translations = [
        'kiện' => 'piece',
        'Kiện' => 'Piece',
        'kg' => 'kg',
        'hành lý' => 'baggage',
        'Hành lý' => 'Baggage',
        'x' => 'x',
        '+' => '+'
    ];

    $translatedText = str_replace(array_keys($translations), array_values($translations), $text);

    return $translatedText;
}

function buildItineraryHTMLFromData($itinerariesData, $lang)
{
    $html = '';

    if (empty($itinerariesData) || !is_array($itinerariesData)) {
        return '<tr>
    <td colspan="5" style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">Không có hành trình</td>
</tr>';
    }

    usort($itinerariesData, function ($a, $b) {
        $t1 = strtotime(str_replace('/', '-', $a['departureDate'] ?? ''));
        $t2 = strtotime(str_replace('/', '-', $b['departureDate'] ?? ''));
        return $t1 - $t2;
    });

    foreach ($itinerariesData as $itinerary) {

        $airlineCode = $itinerary['airlineCode'] ?? $itinerary['airline'] ?? '';
        $airline = myGetAirlineInfo2(trim($airlineCode), 'CODE');
        $airlineName = $airline['data'][0]['name'] ?? $itinerary['airline'];

        $departureCode = $itinerary['departure'] ?? '';
        $departure = myGetAirportInfo2(trim($departureCode));
        $departureName = ($departure['data'][0]['name'] ?? '') . ' (' . ($departure['data'][0]['code'] ?? $departureCode) . ')';

        $arrivalCode = $itinerary['arrival'] ?? '';
        $arrival = myGetAirportInfo2(trim($arrivalCode));
        $arrivalName = ($arrival['data'][0]['name'] ?? '') . ' (' . ($arrival['data'][0]['code'] ?? $arrivalCode) . ')';

        $departureDateTime = $itinerary['departureDate'] ?? '';
        $arrivalDateTime = $itinerary['arrivalDate'] ?? '';

        $dateDisplay = '';
        $timeRange = '';

        if (!empty($departureDateTime)) {
            $parts = explode(' ', $departureDateTime);
            if (isset($parts[0])) {
                $dateDisplay = str_replace('-', '/', $parts[0]);
            }
            if (isset($parts[1])) {
                $timeRange .= $parts[1];
            }
        }

        if (!empty($arrivalDateTime)) {
            $parts = explode(' ', $arrivalDateTime);
            if (isset($parts[1])) {
                if ($timeRange !== '')
                    $timeRange .= ' - ';
                $timeRange .= $parts[1];
            }
        }

        $flightDisplay = $dateDisplay;
        if ($timeRange !== '') {
            $flightDisplay .= '<br>' . $timeRange;
        }

        $flightNumber = trim($itinerary['flightNumber']);

        $html .= '<tr>
    <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . $flightDisplay . '</td>
    <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($airlineName) . '</td>
    <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($flightNumber) . '
    </td>
    <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($departureName) . '
    </td>
    <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($arrivalName) . '</td>
</tr>';
    }

    return $html;
}