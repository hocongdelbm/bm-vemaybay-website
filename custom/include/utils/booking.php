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

    $phoneNumber = trim($phoneNumber);
    if(!$phoneNumber || empty($phoneNumber)) return false;

    $sql_contact = "SELECT id FROM contacts WHERE phone_mobile = '$phoneNumber' AND deleted = 0";
    $contact_id = $db->getOne($sql_contact);

    $contact = new Contact();
    if (!$contact_id) {
        $contact->last_name = $contactName ?? '';
        $contact->phone_mobile = $phoneNumber;
        $contact->description = 'Liên hệ mới tạo từ booking';
        $contact_id = $contact->save();
        if(empty($contact_id)) {
            // $message = Mattermost::$line_separation;
            // $message .= "Tạo liên hệ mới thất bại với số điện thoại: **$phoneNumber**";
            // Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $message);

            $botToken   = $sugar_config['telegram']['bot_token'] ?? '';
            $chatId     = $sugar_config['telegram']['chat_id'] ?? '';
            $threadId   = $sugar_config['telegram']['thread_id_system_noti'] ?? '';
            Telegram::sendMessage("<b>Tạo liên hệ mới thất bại với SĐT: $phoneNumber</b>", $botToken, $chatId, $threadId);
        }
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

            if($get_contact_name && !empty($get_contact_name)) {
                $contact->last_name = $get_contact_name;
                $contact->save();
            }
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
    $sql = "SELECT 
        COUNT(id) AS total_bookings,
        SUM(CASE WHEN booking_status = '8' THEN 1 ELSE 0 END) AS completed_bookings
    FROM ec_flight_bookings
    WHERE contact_id = '{$contactId}' 
    AND deleted = 0";

    $result = $db->query($sql);
    $data = $db->fetchByAssoc($result);

    $totalBookings      = isset($data['total_bookings']) ? (int)$data['total_bookings'] : 0;
    $completedBookings  = isset($data['completed_bookings']) ? (int)$data['completed_bookings'] : 0;

    if ($totalBookings >= 5 && $completedBookings == 0) {
        $type_contact = [
            'type' => 'contact_warning',
            'label' => 'Lý thông',
            'desc' => '5 booking trở lên mà không hoàn tất',
            'totalBookings' => (int)$totalBookings,
            'completedBookings' => (int)$completedBookings,
        ];
    } elseif ($totalBookings > 20 && $completedBookings >= 11) {
        $type_contact = [
            'type' => 'contact_supper_vip',
            'label' => 'Supper VIP',
            'desc' => 'Trên 20 booking và có từ 11 booking hoàn tất',
            'totalBookings' => (int)$totalBookings,
            'completedBookings' => (int)$completedBookings,
        ];
    } elseif ((int)$totalBookings >= 11 && (int)$totalBookings <= 20 && (int)$completedBookings >= 6) {
        $type_contact = [
            'type' => 'contact_gold_member',
            'label' => 'GOLD Member',
            'desc' => 'Từ 11-20 booking và có từ 6 booking hoàn tất',
            'totalBookings' => (int)$totalBookings,
            'completedBookings' => (int)$completedBookings,
        ];
    } elseif ((int)$totalBookings >= 6 && (int)$totalBookings <= 10 && (int)$completedBookings >= 3) {
        $type_contact = [
            'type' => 'contact_vip_member',
            'label' => 'VIP Member',
            'desc' => 'Từ 6-10 booking và có từ 3 booking hoàn tất',
            'totalBookings' => (int)$totalBookings,
            'completedBookings' => (int)$completedBookings,
        ];
    } elseif ((int)$totalBookings >= 2 && (int)$totalBookings <= 5 && (int)$completedBookings >= 1) {
        $type_contact = [
            'type' => 'contact_new_member',
            'label' => 'KH mới',
            'desc' => 'Từ 2-5 booking và có booking hoàn tất',
            'totalBookings' => (int)$totalBookings,
            'completedBookings' => (int)$completedBookings,
        ];
    } else {
        $type_contact = [
            'type' => 'contact_return',
            'label' => 'Trở lại',
            'desc' => 'Xuất hiện trong bất kỳ booking',
            'totalBookings' => (int)$totalBookings,
            'completedBookings' => (int)$completedBookings,
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

      if($current_user->id == '1') pr($sql);

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

    // if($GLOBALS['current_user']->user_name == 'hungnh') {
    //     pr($totalProfit);
    // }

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
    } elseif ($completedCurrentPeriod >= 1) {
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
 * Tính doanh số của 1 booking. Chỉ tính doanh số thực, không tính hoàn vé
 * Các trạng thái đã chuyển khoản: 8, 7, 3
 * Nếu booking có sử dụng điểm tích lũy thì không tính vào doanh số
 * @param string $booking_id của booking
 * @return string Trả về thông tin doanh số
 */
function calculateBKTotalAmt($booking_id)
{
    global $db;
    $sql = "SELECT
            IFNULL(b.total_amount, 0) 
            + 
            IFNULL((
                SELECT SUM(IFNULL(pc.down*1000, 0))
                FROM ec_contact_points_log pc
                WHERE pc.parent_type = 'EC_Flight_Bookings' AND pc.parent_id = b.id AND pc.deleted = 0
            ), 0)
            +  
            IFNULL((
                SELECT SUM(IFNULL(hv.tongtienhang, 0))
                FROM ec_hoanve hv 
                WHERE hv.tinhtrang='1' AND hv.deleted = 0 AND hv.booking_id = b.id
            ), 0)
            + 
            IFNULL((
                SELECT SUM(IFNULL(pt.amount, 0))
                FROM ec_receipt_voucher pt 
                WHERE pt.booking_id = b.id 
                AND pt.rv_status IN (1, 2)
                AND pt.loai_thu IN ('4','5')
                AND pt.deleted = 0
            ), 0)
            - 
            IFNULL((
                SELECT SUM(IFNULL(d.total_bought_price, 0)) 
                FROM ec_booking_details d 
                WHERE d.booking_id = b.id AND d.deleted = 0
            ), 0)
            - 
            IFNULL((
                SELECT
                IF(
                    b.flight_type = '0',
                    SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0) + IF(p.luggage_price_inbound > 0, IFNULL(p.luggage_purchase_inbound, 0), 0)),
                    SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0))
                    ) 
                FROM ec_booking_passengers p 
                WHERE p.booking_id = b.id AND p.deleted = 0 AND p.add_type IS NULL
            ), 0)
            - 
            IFNULL((
                SELECT SUM(IFNULL(hv.tongtienkhach, 0))
                FROM ec_hoanve hv 
                WHERE hv.tinhtrang = '1' AND hv.deleted = 0 AND hv.booking_id = b.id
            ), 0)
            -
            IFNULL((
                SELECT SUM(IFNULL(pt.bought_amount,0) + IFNULL(pt.bought_amount2,0) + IFNULL(pt.bought_amount3,0))
                FROM ec_receipt_voucher pt 
                WHERE pt.booking_id = b.id 
                AND pt.rv_status IN (1, 2)
                AND pt.loai_thu IN ('4','5')
                AND pt.deleted = 0
            ), 0)
            - 
            IFNULL((
                SELECT SUM(IFNULL(pc2.up * 1000, 0))
                FROM ec_contact_points_log pc2
                WHERE pc2.parent_type = 'EC_Contact_Points_Log' 
                    AND pc2.parent_id IN (
                        SELECT pc_inner.id
                        FROM ec_contact_points_log pc_inner
                        WHERE pc_inner.parent_type = 'EC_Flight_Bookings' 
                            AND pc_inner.parent_id = b.id 
                            AND pc_inner.deleted = 0
                    )
                    AND pc2.deleted = 0
            ), 0)
        FROM ec_flight_bookings b 
        WHERE b.id = '" . $booking_id . "' 
        AND booking_status IN ('8', '7', '3') 
        AND b.deleted = 0";

    return $db->getOne($sql);
}

/**
 * Tính amount của 1 booking.
 * Các trạng thái đã chuyển khoản: 8, 7, 3
 * @param string $booking_id của booking
 * @return array Trả về mảng gồm tất cả các cột có tác động đến doanh số của booking
 */
function calculateBKAmt($booking_id)
{
    global $db;

    $sql = "SELECT
            b.id AS booking_id,
            b.name AS booking,
            (
                IFNULL(b.total_amount, 0) 
                + 
                IFNULL((
                    SELECT SUM(IFNULL(pc.down*1000, 0))
                    FROM ec_contact_points_log pc
                    WHERE pc.parent_type = 'EC_Flight_Bookings' AND pc.parent_id = b.id AND pc.deleted = 0
                ), 0)
                +  
                IFNULL((
                    SELECT SUM(IFNULL(hv.tongtienhang, 0))
                    FROM ec_hoanve hv 
                    WHERE hv.tinhtrang='1' AND hv.deleted = 0 AND hv.booking_id = b.id
                ), 0)
                + 
                IFNULL((
                    SELECT SUM(IFNULL(pt.amount, 0))
                    FROM ec_receipt_voucher pt 
                    WHERE pt.booking_id = b.id 
                    AND pt.rv_status IN (1, 2)
                    AND pt.loai_thu IN ('4','5')
                    AND pt.deleted = 0
                ), 0)
            ) AS total_amount_all,
            IFNULL(b.total_amount, 0) as total_amount,
            -- GIÁ BÁN Đổi giờ bay, hành trình, tên khách, phí mua hành lý, mua ghế
            IFNULL((
                SELECT SUM(IFNULL(pt.amount, 0))
                FROM ec_receipt_voucher pt 
                WHERE pt.booking_id = b.id 
                AND pt.rv_status IN (1, 2)
                AND pt.loai_thu IN ('4','5')
                AND pt.deleted = 0
            ), 0) AS total_amount_receipt,
            -- GIÁ MUA Đổi giờ bay, hành trình, tên khách, phí mua hành lý, mua ghế
            IFNULL((
                SELECT SUM(IFNULL(pt.bought_amount,0) + IFNULL(pt.bought_amount2,0) + IFNULL(pt.bought_amount3,0))
                FROM ec_receipt_voucher pt 
                WHERE pt.booking_id = b.id 
                AND pt.rv_status IN (1, 2)
                AND pt.loai_thu IN ('4','5')
                AND pt.deleted = 0
            ), 0) AS total_purchase_receipt,
            -- Tiền giảm giá sử dụng điểm tích lũy
            IFNULL((
                SELECT SUM(IFNULL(pc.down*1000, 0))
                FROM ec_contact_points_log pc
                WHERE pc.parent_type = 'EC_Flight_Bookings' AND pc.parent_id = b.id AND pc.deleted = 0
            ), 0) as total_amount_points,
            -- Tiền giảm giá sử dụng điểm tích lũy mà bị hoàn lại
            IFNULL((
                SELECT SUM(IFNULL(pc2.up * 1000, 0))
                FROM ec_contact_points_log pc2
                WHERE pc2.parent_type = 'EC_Contact_Points_Log' 
                    AND pc2.parent_id IN (
                        SELECT pc_inner.id
                        FROM ec_contact_points_log pc_inner
                        WHERE pc_inner.parent_type = 'EC_Flight_Bookings' 
                            AND pc_inner.parent_id = b.id 
                            AND pc_inner.deleted = 0
                    )
                    AND pc2.deleted = 0
            ), 0) as total_amount_points_refunded,
            -- Tiền hãng hoàn là khoản total_amount
            IFNULL((
                SELECT SUM(IFNULL(hv.tongtienhang, 0))
                FROM ec_hoanve hv 
                WHERE hv.tinhtrang='1' AND hv.deleted = 0 AND hv.booking_id = b.id
            ), 0) AS total_amount_brand_refunded,  
            -- Tiền Hoàn khách là khoản total_bought_price
            IFNULL((
                SELECT SUM(IFNULL(hv.tongtienkhach, 0))
                FROM ec_hoanve hv 
                WHERE hv.tinhtrang='1' AND hv.deleted = 0 AND hv.booking_id = b.id
            ), 0) AS total_purchase_pass_refunded,  
            (
                IFNULL((
                    SELECT SUM(IFNULL(d.total_bought_price, 0)) 
                    FROM ec_booking_details d 
                    WHERE d.booking_id = b.id AND d.deleted = 0
                ), 0)
                + 
                IFNULL((
                    SELECT
                    IF(
                        b.flight_type = '0',
                        SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0) + IF(p.luggage_price_inbound > 0, IFNULL(p.luggage_purchase_inbound, 0), 0)),
                        SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0))
                        ) 
                    FROM ec_booking_passengers p 
                    WHERE p.booking_id = b.id AND p.deleted = 0 AND p.add_type IS NULL
                ), 0)
                + 
                IFNULL((
                    SELECT SUM(IFNULL(hv.tongtienkhach, 0))
                    FROM ec_hoanve hv 
                    WHERE hv.tinhtrang='1' AND hv.deleted = 0 AND hv.booking_id = b.id
                ), 0)
                + 
                IFNULL((
                    SELECT SUM(IFNULL(pt.bought_amount,0) + IFNULL(pt.bought_amount2,0) + IFNULL(pt.bought_amount3,0))
                    FROM ec_receipt_voucher pt 
                    WHERE pt.booking_id = b.id 
                    AND pt.rv_status IN (1, 2)
                    AND pt.loai_thu IN ('4','5')
                    AND pt.deleted = 0
                ), 0)
                +
                IFNULL((
                    SELECT SUM(IFNULL(pc2.up * 1000, 0))
                    FROM ec_contact_points_log pc2
                    WHERE pc2.parent_type = 'EC_Contact_Points_Log' 
                        AND pc2.parent_id IN (
                            SELECT pc_inner.id
                            FROM ec_contact_points_log pc_inner
                            WHERE pc_inner.parent_type = 'EC_Flight_Bookings' 
                                AND pc_inner.parent_id = b.id 
                                AND pc_inner.deleted = 0
                        )
                        AND pc2.deleted = 0
                ), 0)
            ) AS total_purchase,
            IFNULL((
                SELECT SUM(IFNULL(d.total_bought_price, 0)) 
                FROM ec_booking_details d 
                WHERE d.booking_id = b.id AND d.deleted = 0
            ), 0) AS total_purchase_booking,
            IFNULL((
                SELECT
                IF(
                    b.flight_type = '0',
                    SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0) + IF(p.luggage_price_inbound > 0, IFNULL(p.luggage_purchase_inbound, 0), 0)),
                    SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0))
                    ) 
                FROM ec_booking_passengers p 
                WHERE p.booking_id = b.id AND p.deleted = 0 AND p.add_type IS NULL
            ), 0) as total_purchase_luggage,
            ((SELECT total_amount_all) - (SELECT total_purchase)) AS total_profit 
            FROM ec_flight_bookings b 
            WHERE b.id = '" . $booking_id . "' 
            AND booking_status IN ('8', '7', '3') 
            AND b.deleted = 0";

            // if($GLOBALS['current_user']->user_name == 'hungnh') {
            //     pr($sql);
            // }
    $res = $db->query($sql);
    $result = array();
    while ($row = $db->fetchByAssoc($res)) {
        $result = array(
            'booking_id'                    => $row['booking_id'],
            'booking'                       => $row['booking'],
            'total_amount_all'              => $row['total_amount_all'],
            'total_amount'                  => $row['total_amount'],
            'total_amount_points'           => $row['total_amount_points'],
            'total_amount_points_refunded'  => $row['total_amount_points_refunded'],
            'total_amount_receipt'          => $row['total_amount_receipt'],
            'total_amount_brand_refunded'   => $row['total_amount_brand_refunded'],
            'total_purchase'                => $row['total_purchase'],
            'total_purchase_booking'        => $row['total_purchase_booking'],
            'total_purchase_pass_refunded'  => $row['total_purchase_pass_refunded'],
            'total_purchase_luggage'        => $row['total_purchase_luggage'],
            'total_purchase_receipt'        => $row['total_purchase_receipt'],
            'total_profit'                  => $row['total_profit'],
        );
    }
    return $result;
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
        SELECT id
        FROM ec_flight_bookings bk
        WHERE bk.contact_id = '$contactId'
        and bk.date_entered BETWEEN '$start_date' AND '$end_date'
        AND bk.booking_status = '8'
        AND bk.deleted = 0
    ";

    $res = $db->query($sql);
    $total_profit = 0;
    while ($row = $db->fetchByAssoc($res)) {
        $total_profit += (int)calculateBKTotalAmt($row['id']);
    }

    return $total_profit;
}

/**
 * Tính tổng doanh số BK của 1 nhân viên
 *
 * @param string $user_id     Id của nhân viên
 * @param string $start_date  Ngày bắt đầu tính doanh số
 * @param string $end_date    Ngày kết thúc tính doanh số
 *
 * @return int Trả về tổng doanh số
 */
function calculateBKTotalAmtOfEmployee($user_id, $from_date, $to_date) {
    global $db;

    $sql = "SELECT 
                SUM(
                    IFNULL(b.total_amount, 0) 
                    + IFNULL((
                        SELECT SUM(IFNULL(pc.down * 1000, 0))
                        FROM ec_contact_points_log pc
                        WHERE pc.parent_type = 'EC_Flight_Bookings' 
                            AND pc.parent_id = b.id 
                            AND pc.deleted = 0
                    ), 0)
                    + IFNULL((
                        SELECT SUM(IFNULL(hv.tongtienhang, 0))
                        FROM ec_hoanve hv 
                        WHERE hv.tinhtrang = '1' 
                            AND hv.deleted = 0 
                            AND hv.booking_id = b.id
                    ), 0)
                    + IFNULL((
                        SELECT SUM(IFNULL(pt.amount, 0))
                        FROM ec_receipt_voucher pt 
                        WHERE pt.booking_id = b.id 
                            AND pt.rv_status IN (1, 2)
                            AND pt.loai_thu IN ('4','5')
                            AND pt.deleted = 0
                    ), 0)
                    - IFNULL((
                        SELECT SUM(IFNULL(d.total_bought_price, 0)) 
                        FROM ec_booking_details d 
                        WHERE d.booking_id = b.id 
                            AND d.deleted = 0
                    ), 0)
                    - IFNULL((
                        SELECT 
                            IF(
                                b.flight_type = '0',
                                SUM(
                                    IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0) 
                                    + IF(p.luggage_price_inbound > 0, IFNULL(p.luggage_purchase_inbound, 0), 0)
                                ),
                                SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0))
                            ) 
                        FROM ec_booking_passengers p 
                        WHERE p.booking_id = b.id 
                            AND p.deleted = 0 
                            AND p.add_type IS NULL
                    ), 0)
                    - IFNULL((
                        SELECT SUM(IFNULL(hv.tongtienkhach, 0))
                        FROM ec_hoanve hv 
                        WHERE hv.tinhtrang = '1' 
                            AND hv.deleted = 0 
                            AND hv.booking_id = b.id
                    ), 0)
                    - IFNULL((
                        SELECT SUM(IFNULL(pt.bought_amount, 0) + IFNULL(pt.bought_amount2, 0) + IFNULL(pt.bought_amount3, 0))
                        FROM ec_receipt_voucher pt 
                        WHERE pt.booking_id = b.id 
                            AND pt.rv_status IN (1, 2)
                            AND pt.loai_thu IN ('4','5')
                            AND pt.deleted = 0
                    ), 0)
                )
            FROM ec_flight_bookings b
            WHERE b.assigned_user_id = '" . $user_id . "' 
                AND b.booking_status IN ('8', '7', '3') 
                AND DATE(DATE_ADD(b.date_entered, INTERVAL 7 HOUR)) BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . " 23:59:59'
                AND b.deleted = 0
            ";
    return $db->getOne($sql);
}

function calculatePointsFromBooking($booking_id)
{
    global $db;
    $sql = "SELECT SUM(service_fee * quantity) as points
        FROM ec_booking_details
        WHERE booking_id = '$booking_id'
            AND deleted = 0";
    $total_service_fee = $db->getOne($sql) ?? 0;
    return (int)($total_service_fee / 10000); // Quy đổi 10.000 VND = 1 point
}
