<?php

/**
 * TẠO LH CHO BOOKING
 * @param string $phoneNumber
 * @param string $contactName
 * @return bool true/false
 */
function createContactsForBooking($phoneNumber, $contactName = '')
{
    global $db, $current_user;

    $sql_contact = "SELECT id FROM contacts WHERE phone_mobile = '$phoneNumber' AND deleted = 0";
    $contact_id = $db->getOne($sql_contact);

    $contact = new Contact();
    if (!$contact_id) {
        $contact->last_name = $contactName ?? '';
        $contact->phone_mobile = $phoneNumber;
        $contact->save();
        $contact_id = $contact->id;
    } else {
        $contact->retrieve($contact_id);

        if (empty($contact->last_name) || stripos($contact->last_name, "Khách") !== false || stripos($contact->last_name, "Khach") !== false || stripos($contact->last_name, "Tele") !== false || preg_match('/^[0-9 ]*$/', $contact->last_name)) {
            // Get contact name from booking
            $sql_booking = "SELECT contact_name
                FROM ec_flight_bookings
                WHERE phone = '$phoneNumber' AND deleted = 0
                ORDER BY date_entered DESC
                LIMIT 1";
            $get_contact_name = $db->getOne($sql_booking);

            $contact->last_name = $get_contact_name;
            $contact->save();
        }
    }

    $sql_check_phone = '
        SELECT COUNT(*) 
        FROM ec_flight_bookings
        WHERE phone = "' . $phoneNumber . '"
        AND deleted = 0
    ';

    if ($db->getOne($sql_check_phone) > 0) {
        $sql_update = '
            UPDATE ec_flight_bookings
            SET contact_id = "' . $contact_id . '"
            WHERE phone = "' . $phoneNumber . '"
            AND (contact_id IS NULL OR contact_id = "")
            AND deleted = 0
        ';
        $db->query($sql_update);
    }

    return true;
}

/**
 * FILL Thông tin hành trình cho booking
 * @param mixed $phoneNumber
 * @param mixed $booking_id
 * @return bool true/false
 */
function fillJourneyForBooking($booking_id)
{
    global $db, $current_user;

    $sql = 'SELECT
            CASE 
                WHEN EXISTS (SELECT 1 FROM ec_booking_itineraries iti1 WHERE bk.id = iti1.booking_id AND iti1.stops = 1) THEN 
                    CONCAT(MIN(CASE WHEN iti.stops = 1 THEN iti.departure END))
                ELSE 
                    MIN(iti.departure)
                END AS departure,
            CASE 
                WHEN EXISTS (SELECT 1 FROM ec_booking_itineraries iti1 WHERE bk.id = iti1.booking_id AND iti1.stops = 1) THEN 
                        MAX(CASE WHEN iti.stops = 1 THEN iti.arrival END)
                ELSE 
                        MAX(iti.arrival)
                END AS arrival
            FROM ec_booking_itineraries iti
                LEFT JOIN ec_flight_bookings bk ON bk.id = iti.booking_id 
            WHERE iti.booking_id = "' . $booking_id . '" AND iti.direction = 0 AND iti.deleted = 0
            LIMIT 1';

    $res = $db->query($sql);
    $journey = '';
    while ($row = $db->fetchByAssoc($res)) {
        if ($row['departure'] && $row['arrival']) {
            $journey = $row['departure'] . '-' . $row['arrival'];
        }
    }

    $sql_update = '
        UPDATE ec_flight_bookings
        SET journey = "' . $journey . '"
        WHERE id = "' . $booking_id . '"
        AND deleted = 0
    ';

    $db->query($sql_update);

    return true;
}

/**
 * Phân loại liên hệ dựa trên số lượng booking và số booking hoàn tất
 *
 * @param string $contactId ID của liên hệ
 * @return string Màu sắc đại diện cho loại liên hệ
 */
function classifyContact($contactId)
{
    global $db;

    $type_contact = [
        'type' => '',
        'label' => '',
        'desc' => '',
        'totalBookings' => 0,
        'completedBookings' => 0,
    ];

    // Đếm tổng số booking và số booking hoàn tất của liên hệ
    $sql = 'SELECT 
                COUNT(id) AS total_bookings,
                SUM(CASE WHEN booking_status = "8" THEN 1 ELSE 0 END) AS completed_bookings
            FROM ec_flight_bookings
            WHERE contact_id = "' . $contactId . '" 
            AND deleted = 0';
    $result = $db->fetchByAssoc($db->query($sql));

    $totalBookings      = (int)$result['total_bookings'];
    $completedBookings  = (int)$result['completed_bookings'];

    if ($totalBookings >= 5 && $completedBookings == 0) {
        $type_contact = [
            'type' => 'contact_warning',
            'label' => 'Lý thông',
            'desc' => '5 booking trở lên mà không hoàn tất',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    } elseif ($totalBookings > 20 && $completedBookings >= 11) {
        $type_contact = [
            'type' => 'contact_supper_vip',
            'label' => 'Supper VIP',
            'desc' => 'Trên 20 booking và có từ 11 booking hoàn tất',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    } elseif ($totalBookings >= 11 && $totalBookings <= 20 && $completedBookings >= 6) {
        $type_contact = [
            'type' => 'contact_gold_member',
            'label' => 'GOLD Member',
            'desc' => 'Từ 11-20 booking và có từ 6 booking hoàn tất',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    } elseif ($totalBookings >= 6 && $totalBookings <= 10 && $completedBookings >= 3) {
        $type_contact = [
            'type' => 'contact_vip_member',
            'label' => 'VIP Member',
            'desc' => 'Từ 6-10 booking và có từ 3 booking hoàn tất',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    } elseif ($totalBookings >= 2 && $totalBookings <= 5 && $completedBookings >= 1) {
        $type_contact = [
            'type' => 'contact_new_member',
            'label' => 'KH mới',
            'desc' => 'Từ 2-5 booking và có booking hoàn tất',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    } else {
        $type_contact = [
            'type' => 'contact_return',
            'label' => 'Trở lại',
            'desc' => 'Xuất hiện trong bất kỳ booking',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    }

    return $type_contact;
}

/**
 * Phân loại liên hệ dựa trên số lượng booking hoàn tất
 *
 * @param string $contactId ID của liên hệ
 * @return string Màu sắc đại diện cho loại liên hệ
 */
function classifyContactv2($contactId)
{
    $start_date    = date('Y-m-d', strtotime('-1 year +7 hours')); // Ngày 1 năm trước
    $end_date      = date('Y-m-d', strtotime('+7 hours')); // Ngày hiện tại

    global $db, $current_user;

    if (empty($contactId)) {
        return 'contactId is required';
    }

    $type_contact = [
        'type' => '',
        'label' => '',
        'desc' => '',
        'completedCurrentPeriod' => 0,  // Số lượng bk hoàn tất trong chu kỳ hiện tại
        'completedPastPeriods' => 0,    // Số lượng bk hoàn tất trong các chu kỳ quá khứ
        'totalRevenue' => 0, // Doanh thu tổng trong chu kỳ
        'totalProfit' => 0, // Doanh thu lợi nhuận tổng
    ];

    $sql = "
        SELECT 
            SUM(CASE WHEN DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) BETWEEN '$start_date' AND '$end_date' THEN 1 ELSE 0 END) AS completedCurrentPeriod,
            SUM(CASE WHEN DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) < '$start_date' THEN 1 ELSE 0 END) AS completedPastPeriods,
            SUM(CASE WHEN DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) BETWEEN '$start_date' AND '$end_date' THEN total_amount ELSE 0 END) AS revenueCurrentPeriod,
            SUM(CASE WHEN DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) < '$start_date' THEN total_amount ELSE 0 END) AS revenuePastPeriods
        FROM ec_flight_bookings
        WHERE contact_id = '$contactId' 
        AND booking_status = '8' 
        AND deleted = 0
    ";

    $completedCurrentPeriod = $completedPastPeriods = $revenueCurrentPeriod = $revenuePastPeriods = 0;
    $re = $db->query($sql);
    while ($row = $db->fetchByAssoc($re)) {
        $completedCurrentPeriod   = (int)$row['completedCurrentPeriod'];
        $completedPastPeriods     = (int)$row['completedPastPeriods'];
        $revenueCurrentPeriod     = (float)$row['revenueCurrentPeriod'];
        $revenuePastPeriods       = (float)$row['revenuePastPeriods'];
    }

    // Tổng doanh số
    $totalRevenue = (int)($revenueCurrentPeriod + $revenuePastPeriods);
    $totalProfit = calculateBKTotalAmtFromContact($contactId);

    // Cập nhật số liệu vào mảng
    $type_contact['completedCurrentPeriod']   = $completedCurrentPeriod;
    $type_contact['completedPastPeriods']     = $completedPastPeriods;
    $type_contact['totalRevenue']             = $totalRevenue;
    $type_contact['totalProfit']              = $totalProfit;

    // Phân loại khách hàng theo các điều kiện CASE WHEN
    if ($completedCurrentPeriod >= 20 && $totalProfit > 20000000) {
        $type_contact['type'] = 'VIP_MEMBER';
        $type_contact['label'] = 'VIP Member';
        $type_contact['desc'] = 'Có 20 bk trở lên và doanh số trên 20 triệu';
    } elseif ($completedCurrentPeriod >= 10) {
        $type_contact['type'] = 'GOLD_MEMBER';
        $type_contact['label'] = 'Vàng';
        $type_contact['desc'] = 'Chu kỳ năm có từ 10 bk trở lên';
    } elseif ($completedCurrentPeriod >= 4 && $completedCurrentPeriod <= 9) {
        $type_contact['type'] = 'SILVER_MEMBER';
        $type_contact['label'] = 'Bạc';
        $type_contact['desc'] = 'Chu kỳ năm có từ 4-9 bk';
    } elseif ($completedCurrentPeriod >= 1 && $completedCurrentPeriod <= 3 && $completedPastPeriods > 0) {
        $type_contact['type'] = 'RETURN_CUSTOMER';
        $type_contact['label'] = 'Trở lại';
        $type_contact['desc'] = 'Chu kỳ năm có từ 1-3 bk và quá khứ có ít nhất 1 bk';
    } elseif ($completedCurrentPeriod >= 1 && $completedCurrentPeriod <= 3 && $completedPastPeriods == 0) {
        $type_contact['type'] = 'NEW_CUSTOMER';
        $type_contact['label'] = 'KH mới';
        $type_contact['desc'] = 'Chu kỳ năm có từ 1-3 bk và quá khứ không có bk';
    } elseif ($completedCurrentPeriod >= 1){
        $type_contact['type'] = 'OTHER';
        $type_contact['label'] = 'Loại khác';
        $type_contact['desc'] = 'Chưa có bk hoàn tất';
    } else {
        $type_contact['type'] = 'DEFAULT_GROUP';
        $type_contact['label'] = 'Vãng lai';
        $type_contact['desc'] = 'Không thuộc 6 loại đã quy định';
    }

    return $type_contact;
}



/**
 * Tìm tất cả hành trình của 1 booking
 *
 * @param string $booking_id ID của booking
 * @return string Trả về thông tin hành trình của booking đó SNG-HAN
 */
function journeyOfBooking($booking_id)
{
    global $db;

    $sql = 'SELECT
				CASE 
					WHEN EXISTS (SELECT 1 FROM ec_booking_itineraries iti1 WHERE bk.id = iti1.booking_id AND iti1.stops = 1) THEN 
						CONCAT(MIN(CASE WHEN iti.stops = 1 THEN iti.departure END))
					ELSE 
						MIN(iti.departure)
					END AS departure,
				CASE 
					WHEN EXISTS (SELECT 1 FROM ec_booking_itineraries iti1 WHERE bk.id = iti1.booking_id AND iti1.stops = 1) THEN 
							MAX(CASE WHEN iti.stops = 1 THEN iti.arrival END)
					ELSE 
							MAX(iti.arrival)
					END AS arrival
				FROM ec_booking_itineraries iti
					LEFT JOIN ec_flight_bookings bk ON bk.id = iti.booking_id 
				WHERE iti.booking_id = "' . $booking_id . '" AND iti.direction = 0';

    $res = $db->query($sql);
    $journey = array();
    while ($row = $db->fetchByAssoc($res)) {
        $journey = array(
            'departure' => $row['departure'],
            'arrival' => $row['arrival']
        );
    }
    return $journey;
}

/**
 * Tính doanh số của 1 booking
 *
 * @param string $booking_id của booking
 * @return string Trả về thông tin doanh số
 */
function calculateBKTotalAmt($booking_id)
{
    global $db;

    $sql = '
        SELECT 
            total_amount
            - IFNULL((
                SELECT SUM(IFNULL(total_bought_price, 0))
                FROM ec_booking_details
                WHERE deleted = 0 AND booking_id = bk.id
            ), 0)
            - IFNULL((
                SELECT IF(
                    flight_type="0"
                    , SUM(IF(
                        px.luggage_price > 0
                        , IFNULL(px.luggage_purchase,0), 0) 
                        + IF(
                            px.luggage_price_inbound>0
                            , IFNULL(px.luggage_purchase_inbound,0)
                            , 0)
                        ), SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0)
                    ))
                FROM ec_booking_passengers px
                WHERE px.deleted=0 AND px.add_type IS NULL
                AND px.booking_id = bk.id
            ),0)
        FROM ec_flight_bookings bk
        WHERE id = "' . $booking_id . '"
        AND deleted = 0
    ';
    return $db->getOne($sql);
}

/**
 * Tính doanh số của 1 liên hệ
 *
 * @param string $contactId của liên hệ
 * @return string Trả về thông tin doanh số
 */
function calculateBKTotalAmtFromContact($contactId)
{
    global $db;
    $start_date    = date('Y-m-d', strtotime('-1 year +7 hours')); // Ngày 1 năm trước
    $end_date      = date('Y-m-d', strtotime('+7 hours')); // Ngày hiện tại

    $sql = "
        SELECT 
            SUM(
                bk.total_amount
                - IFNULL((
                    SELECT SUM(IFNULL(bd.total_bought_price, 0))
                    FROM ec_booking_details bd
                    WHERE bd.deleted = 0 AND bd.booking_id = bk.id
                ), 0)
                - IFNULL((
                    SELECT SUM(
                        IF(bk.flight_type = '0', 
                            IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                            + IF(px.luggage_price_inbound > 0, IFNULL(px.luggage_purchase_inbound, 0), 0),
                            IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                        )
                    )
                    FROM ec_booking_passengers px
                    WHERE px.deleted = 0 AND px.booking_id = bk.id
                ), 0)
            ) AS total_revenue
        FROM ec_flight_bookings bk
        WHERE bk.contact_id = '$contactId'
        and date_entered BETWEEN '$start_date' AND '$end_date'
        AND bk.booking_status = '8'
        AND bk.deleted = 0
    ";
    return $db->getOne($sql) ?? 0;
}

function calculatePointsFromBooking($booking_id) {
    global $db;
    $sql = "SELECT SUM(service_fee * quantity) as points
        FROM ec_booking_details
        WHERE booking_id = '$booking_id'
            AND deleted = 0";
    $total_service_fee = $db->getOne($sql) ?? 0;
    return (int)($total_service_fee / 10000); // Quy đổi 10.000 VND = 1 point
}