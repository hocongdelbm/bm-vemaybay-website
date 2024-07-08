<?php
set_time_limit(180);
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db;

//sleep(1);
$trip_type = preg_replace('/\D/', '', $_POST['trip_type']);
$booking_id = preg_replace('/[^a-zA-Z0-9-]/', '', $_POST['booking_id']);
$contact_mobile = preg_replace('/\D/', '', $_POST['contact_mobile']);

if (isset($trip_type) && !empty($booking_id) && is_guid($booking_id) && !empty($contact_mobile)) {
    $sql = "SELECT is_recheck_success_c FROM ec_flight_bookings_cstm WHERE id_c = '" . $booking_id . "' LIMIT 1";
    $res = $db->query($sql);
    $row = $db->fetchByAssoc($res);
    if (!empty($row) && $row['is_recheck_success_c'] == 1) {
        echo 0;
        exit;
    }

    $sql = "SELECT CONCAT(p.id, '-outbound') AS pax_id,
                i.airline_code AS aircode,
                i.flight_number AS flight_no,
                IF(i.departure = 'NHA', 'CXR', i.departure) AS dep_code,
                IF(i.arrival = 'NHA', 'CXR', i.arrival) AS arv_code,
                i.departure_date AS dep_date,
                (
                    CASE p.type
                        WHEN '2' THEN 'INF'
                        WHEN '1' THEN 'CHD'
                        ELSE 'ADT'
                    END 
                ) AS pax_type,
                IF(p.salutation = '1', 'F', 'M') AS pax_title,
                p.name AS pax_name,
                p.birthday AS pax_dob,
                p.pnr_outbound AS pnr,
                p.eticket_outbound AS eticket_no,
                p.luggage_price AS bag_price,
                i.direction AS way_flight,
                p.date_entered
            FROM ec_booking_passengers p
                LEFT JOIN ec_booking_itineraries i ON i.booking_id = p.booking_id AND i.deleted = 0
            WHERE p.deleted = 0
                AND (p.pnr_outbound IS NOT NULL OR p.eticket_outbound IS NOT NULL)
                AND i.direction = '0'
                AND p.booking_id = '" . $booking_id . "' ";

    if ($trip_type == 0) {
        $sql .= "UNION
            SELECT CONCAT(p.id, '-inbound') AS pax_id,
                i.airline_code AS aircode,
                i.flight_number AS flight_no,
                IF(i.departure = 'NHA', 'CXR', i.departure) AS dep_code,
                IF(i.arrival = 'NHA', 'CXR', i.arrival) AS arv_code,
                i.departure_date AS dep_date,
                (
                    CASE p.type
                    WHEN '2' THEN 'INF'
                    WHEN '1' THEN 'CHD'
                    ELSE 'ADT'
                    END 
                ) AS pax_type,
                IF(p.salutation = '1', 'F', 'M') AS pax_title,
                p.name AS pax_name,
                p.birthday AS pax_dob,
                p.pnr_inbound AS pnr,
                p.eticket_inbound AS eticket_no,
                p.luggage_price_inbound AS bag_price,
                i.direction AS way_flight,
                p.date_entered
            FROM ec_booking_passengers p
                LEFT JOIN ec_booking_itineraries i ON i.booking_id = p.booking_id AND i.deleted = 0
            WHERE p.deleted = 0
                AND (p.pnr_inbound IS NOT NULL OR p.eticket_inbound IS NOT NULL)
                AND i.direction = '1'
                AND p.booking_id = '" . $booking_id . "' ";
    }
    $sql .= " ORDER BY pax_type, date_entered ";

    $airlines = array('VNA' => 'VN', 'VJA' => 'VJ');
    $airline_bag_list = array('VNA' => 'vietnamair', 'VJA' => 'vietjet');
    $pnr_list = array();
    $res = $db->query($sql);
    $p = 0;
    while ($row = $db->fetchByAssoc($res)) {
        if (!isset($airlines[$row['aircode']])) {
            continue;
        }
        $pnr = preg_replace('/[^a-zA-Z0-9]/', '', $row['pnr']);
        $pnr_key = $airlines[$row['aircode']] . '_' . $row['way_flight'] . '_' . $pnr;
        $pnr_list[$pnr_key][$p] = $row;
        if ((int)$row['bag_price'] > 0 && isset($app_list_strings[$airline_bag_list[$row['aircode']] . '_luggage_price_list'][(int)$row['bag_price']])) {
            preg_match('/\s(\d+)kg/isU', $app_list_strings[$airline_bag_list[$row['aircode']] . '_luggage_price_list'][(int)$row['bag_price']], $output);
            $pnr_list[$pnr_key][$p]['bag_weight'] = $output[1];
        } else {
            $pnr_list[$pnr_key][$p]['bag_weight'] = 0;
        }
        $p++;
    }

    $errors = array();
    $now = time();
    if (!empty($pnr_list)) {
        foreach ($pnr_list as $pnr_key => $pnr_val) {
            $pax_list = array_values($pnr_val);
            $first_pax = $pax_list[0];

            // Check if flyed already?
            if (strtotime($first_pax['dep_date']) <= $now) {
                continue;
            }

            list($aircode, $way_flight, $pnr) = explode('_', $pnr_key);

            $way_flight_search = $way_flight;
            // Check if booking are round trip and different pnr
            if ($trip_type == 0 && !isset($pnr_list[$aircode . '_' . ($way_flight == 0 ? 1 : 0) . '_' . $pnr])) {
                $way_flight_search = 0;
            }

            $flight_no = strtoupper(myRemoveNoneWordChar($first_pax['flight_no']));
            $email = $aircode == 'BL' ? rawurlencode('vmbnamphuong@gmail.com') : '';
            $full_name = rawurlencode(strtoupper(preg_replace('/[^a-zA-Z\s]/', '', $first_pax['pax_name'])));
            $response = myRecheckFlight($aircode, $pnr, $full_name, $flight_no, 180, 1, $email);

            if ($GLOBALS['current_user']->user_name == 'nponline') {
                //print_r($response);
            }

            $way_flight_str = $trip_type == 0 ? '(<strong>' . $app_list_strings['bk_direction_list'][$way_flight] . ' - ' . $pnr . '</strong>).' : '(<strong>' . $pnr . '</strong>).';
            if (empty($response['error']) && !empty($response['data'])) {
                $result = $response['data'];
                if ($result['unpaid']) {
                    $errors[] = 'Booking chưa xuất vé ' . $way_flight_str;
                }
                $mobiles = !empty($result['mobile']) ? explode(',', $result['mobile']) : '';
                if (!empty($mobiles) && !in_array(myRemoveNoneDigitChar($contact_mobile), $mobiles)) {
                    $errors[] = 'Số điện thoại không đúng ' . $way_flight_str;
                }
                if (
                    myRemoveNoneWordChar($result['dep_code']) != myRemoveNoneWordChar($first_pax['dep_code'])
                    || myRemoveNoneWordChar($result['arv_code']) != myRemoveNoneWordChar($first_pax['arv_code'])
                ) {
                    $errors[] = 'Nơi đi hoặc nơi đến không đúng ' . $way_flight_str;
                }
                if (myRemoveNoneWordChar($result['flight_no']) != myRemoveNoneWordChar($first_pax['flight_no'])) {
                    $errors[] = 'Mã chuyến bay không đúng ' . $way_flight_str;
                }
                if (strtotime($result['dep_date']) != strtotime($first_pax['dep_date'])) {
                    $errors[] = 'Ngày giờ bay không đúng ' . $way_flight_str;
                }
                foreach ($pax_list as $pax) {
                    $find_pax = findPax($result['pax_list'], $pax['pax_type'], $pax['pax_name']);
                    if ($find_pax !== false) {
                        if (($pax['pax_type'] == 'CHD' || $pax['pax_type'] == 'INF')
                            && !empty($pax['pax_dob'])
                            && !empty($find_pax['dob'])
                            && strtotime($pax['pax_dob']) != strtotime($find_pax['dob'])
                        ) {
                            $errors[] = $pax['pax_name'] . ' - Ngày tháng năm sinh không đúng ' . $way_flight_str;
                        }
                        if ($aircode != 'VN' && $pax['bag_weight'] != $find_pax['bag_weight']) {
                            $errors[] = $pax['pax_name'] . ' - Hành lý ký gửi không đúng ' . $way_flight_str;
                        }
                        if (
                            $aircode == 'VN'
                            && !empty($pax['eticket_no'])
                            && !empty($find_pax['eticket_no'])
                            && myRemoveNoneDigitChar($pax['eticket_no']) != myRemoveNoneDigitChar($find_pax['eticket_no'])
                        ) {
                            $errors[] = $pax['pax_name'] . ' - Số vé không đúng ' . $way_flight_str;
                        }
                    } else {
                        $errors[] = $pax['pax_name'] . ' - Hành khách không tồn tại hoặc loại hành khách không đúng ' . $way_flight_str;
                    }
                }
            } else {
                $errors[] = 'Lỗi kết nối hoặc không tìm thấy dữ liệu ' . $way_flight_str . '. Vui lòng thử lại.';
            }
        }
    }

    if (!empty($errors)) {
        echo implode('<br>', $errors);
    } else {
        $sql = "UPDATE ec_flight_bookings_cstm SET is_recheck_success_c = 1 WHERE id_c = '" . $booking_id . "' LIMIT 1";
        $db->query($sql);
        echo 0;
    }
}

/**
 * Find pax from recheck list
 * @param array $recheck_list
 * @param string $pax_type
 * @param string $pax_name
 * @return bool
 */
function findPax($recheck_list, $pax_type, $pax_name)
{
    foreach ($recheck_list as $pax) {
        if ($pax['type'] == $pax_type && myRemoveNoneWordChar($pax['name']) == myRemoveNoneWordChar($pax_name)) {
            return $pax;
        }
    }

    return false;
}
