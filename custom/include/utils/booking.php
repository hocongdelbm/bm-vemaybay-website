<?php

/**
 * TẠO LH CHO BOOKING
 * @param mixed $phoneNumber
 * @param mixed $booking_id
 * @return bool true/false
 */
function createContactsForBooking($phoneNumber)
{
    global $db;

    $sql_contact = 'SELECT id
                    FROM contacts
                    WHERE phone_mobile = "' . $phoneNumber . '"
                    AND deleted = 0';
    $contact_id = $db->getOne($sql_contact);

    if (!$contact_id) {
        $contact = new Contact();
        $contact->name = $phoneNumber;
        $contact->phone_mobile = $phoneNumber;
        $contact->save();
        $contact_id = $contact->id;
    }

    // Kiểm tra tồn tại phone trước khi cập nhật
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
            WHERE iti.booking_id = "' . $booking_id . '" AND iti.direction = 0 AND iti.deleted = 0
            LIMIT 1';

    $res = $db->query($sql);
    $journey = '';
    while ($row = $db->fetchByAssoc($res)) {
        $journey = $row['departure'] . '-' . $row['arrival'];
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
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    } elseif ($totalBookings >= 20 && $completedBookings >= 11) {
        $type_contact = [
            'type' => 'contact_supper_vip',
            'label' => 'Supper VIP',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    } elseif ($totalBookings >= 11 && $totalBookings <= 20 && $completedBookings >= 6) {
        $type_contact = [
            'type' => 'contact_gold_member',
            'label' => 'GOLD Member',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    } elseif ($totalBookings >= 6 && $totalBookings <= 10 && $completedBookings >= 3) {
        $type_contact = [
            'type' => 'contact_vip_member',
            'label' => 'VIP Member',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    } elseif ($totalBookings >= 2 && $totalBookings <= 5 && $completedBookings >= 1) {
        $type_contact = [
            'type' => 'contact_vip_member',
            'label' => 'VIP Member',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
    } else {
        $type_contact = [
            'type' => 'contact_return',
            'label' => 'Trở lại',
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
        ];
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
    ';
    return $db->getOne($sql);
}
