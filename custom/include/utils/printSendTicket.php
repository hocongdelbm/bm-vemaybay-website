<?php
function buildPassengerHTMLFromData($passengersData, $khuhoi, $lang)
{
    $html = '';

    if (empty($passengersData) || !is_array($passengersData)) {
        return '<tr>
    <td colspan="3" style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">Không có hành khách</td>
</tr>';
    }

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
            $depBagText = $passenger['luggage']['outbound'] ?? '';
            $retBagText = $passenger['luggage']['inbound'] ?? '';

            // Clean both
            $depBagText = cleanLuggageText($depBagText);
            $retBagText = cleanLuggageText($retBagText);

            // Combine duplicates
            $depBagText = combineDuplicateBaggage($depBagText);
            $retBagText = combineDuplicateBaggage($retBagText);

            // Add labels
            if ($khuhoi) {
                $labelOutbound = ($lang == 'en') ? '(Outbound)' : '(Lượt đi)';
                $labelInbound = ($lang == 'en') ? '(Inbound)' : '(Lượt về)';

            }
            // Build result
            if (!empty($depBagText) && !empty($retBagText)) {
                $baggageDescription = "{$depBagText} {$labelOutbound} - {$retBagText} {$labelInbound}";
            } elseif (!empty($depBagText)) {
                $baggageDescription = "{$depBagText} {$labelOutbound}";
            } elseif (!empty($retBagText)) {
                $baggageDescription = "{$retBagText} {$labelInbound}";
            }

            // Translate
            $baggageDescription = translateLuggageText($baggageDescription, $lang);
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

function combineDuplicateBaggage($text) {
    // Find ALL main baggage patterns like "1 kiện x 23kg"
    preg_match_all('/(\d+)\s*kiện\s*(?:x\s*)?(\d+)\s*kg/i', $text, $matches);
    
    $baggageItems = [];
    
    // Combine main baggage by weight
    for ($i = 0; $i < count($matches[0]); $i++) {
        $package = (int)$matches[1][$i];
        $weight = (int)$matches[2][$i];
        
        $key = $weight . 'kg';
        if (!isset($baggageItems[$key])) {
            $baggageItems[$key] = 0;
        }
        $baggageItems[$key] += $package;
    }

    // Build combined result
    $result = '';
    foreach ($baggageItems as $weight => $totalPackage) {
        if ($result) $result .= ' + ';
        $result .= $totalPackage . ' kiện x ' . $weight;
    }

    // Find additional baggage like "Thêm 10kg" or "+ 10kg"
    preg_match('/(?:Thêm|\+)\s*(\d+)\s*kg/i', $text, $additionalMatches);
    
    if (!empty($additionalMatches[0])) {
        if ($result) $result .= ' + ';
        $result .= $additionalMatches[1] . 'kg';
    }

    return $result ?: $text;
}

function generateCombinedPassengerBaggageInfo($depAvaiBagText, $depPurchaseBagText, $retAvaiBagText, $retPurchaseBagText, $language = 'vn')
{
    $isRoundtrip = false;
    if ((!empty($depAvaiBagText) || !empty($depPurchaseBagText)) && (!empty($retAvaiBagText) || !empty($retPurchaseBagText)))
        $isRoundtrip = true;

    // Departure
    $baggageDescriptionDep = '';
    if (!empty($depAvaiBagText) && !empty($depPurchaseBagText)) {
        $baggageDescriptionDep .= "$depAvaiBagText + $depPurchaseBagText";
    } elseif (!empty($depAvaiBagText))
        $baggageDescriptionDep .= $depAvaiBagText;
    elseif (!empty($depPurchaseBagText))
        $baggageDescriptionDep .= $depPurchaseBagText;

    // Return
    $baggageDescriptionRet = '';
    if (!empty($retAvaiBagText) && !empty($retPurchaseBagText)) {
        $baggageDescriptionRet .= "$retAvaiBagText + $retPurchaseBagText";
    } elseif (!empty($retAvaiBagText))
        $baggageDescriptionRet .= $retAvaiBagText;
    elseif (!empty($retPurchaseBagText))
        $baggageDescriptionRet .= $retPurchaseBagText;

    if (!empty($baggageDescriptionDep) && !empty($baggageDescriptionRet)) {
        return "{$baggageDescriptionDep} - {$baggageDescriptionRet}";
    } else
        return trim("$baggageDescriptionDep $baggageDescriptionRet");
}

function cleanLuggageText($text)
{
    $text = strip_tags($text);
    $text = preg_replace('/\s*\([^)]*\)/', '', $text);
    $text = preg_replace('/Giá bán.*?VND/i', '', $text);
    $text = preg_replace('/Giá mua.*?VND/i', '', $text);
    $text = preg_replace('/Nhà cung cấp:.*?(\n|$)/i', '', $text);
    $text = preg_replace('/Lượt đi:/i', '', $text);
    $text = preg_replace('/Lượt về:/i', '', $text);
    $text = preg_replace('/Outbound:/i', '', $text);
    $text = preg_replace('/Inbound:/i', '', $text);
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
        'x' => 'x'
    ];

    return str_replace(array_keys($translations), array_values($translations), $text);
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