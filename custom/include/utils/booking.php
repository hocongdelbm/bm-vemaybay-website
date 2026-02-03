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
    if (!$phoneNumber || empty($phoneNumber)) return false;

    $sql_contact = "SELECT id FROM contacts WHERE phone_mobile = '$phoneNumber' AND deleted = 0";
    $contact_id = $db->getOne($sql_contact);

    $contact = new Contact();
    if (!$contact_id) {
        $contact->last_name = $contactName ?? '';
        $contact->phone_mobile = $phoneNumber;
        $contact->description = 'Liên hệ mới tạo từ booking';
        $contact_id = $contact->save();
        if (empty($contact_id)) {
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

            if ($get_contact_name && !empty($get_contact_name)) {
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

    $type_contact = [
        'type' => '',
        'label' => '',
        'desc' => '',
        'completedCurrentPeriod' => 0,  // Số lượng bk hoàn tất trong chu kỳ hiện tại
        'completedPastPeriods' => 0,    // Số lượng bk hoàn tất trong các chu kỳ quá khứ
        'totalRevenue' => 0, // Doanh thu tổng trong chu kỳ
        'totalProfit' => 0, // Doanh thu lợi nhuận tổng
    ];

    if (empty($contactId)) {
        return $type_contact;
    }

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
 * Tính doanh số của 1 booking.
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
 * @param bool $only_profit: Chỉ lấy giá trị doanh số của booking
 * @return array Trả về mảng gồm tất cả các cột có tác động đến doanh số của booking
 * p/s: Doanh số đã bao gồm hành lý đi kèm, hành lý mua thêm, hoàn vé và sử dụng điểm giảm giá
 */
function calculateBKAmt($booking_id, $only_profit = false)
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
            ) AS total_amount,
            IFNULL(b.total_amount, 0) as total_amount_booking,
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
            ), 0) as total_purchase_points_refunded,
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
            ), 0) as total_purchase_luggage
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
            'total_amount_booking'          => $row['total_amount_booking'],
            'total_amount_points'           => $row['total_amount_points'],
            'total_amount_receipt'          => $row['total_amount_receipt'],
            'total_amount_brand_refunded'   => $row['total_amount_brand_refunded'],
            'total_amount'                  => $row['total_amount'],
            'total_purchase_booking'        => $row['total_purchase_booking'],
            'total_purchase_pass_refunded'  => $row['total_purchase_pass_refunded'],
            'total_purchase_points_refunded'  => $row['total_purchase_points_refunded'],
            'total_purchase_luggage'        => $row['total_purchase_luggage'],
            'total_purchase_receipt'        => $row['total_purchase_receipt'],
            'total_purchase'                => $row['total_purchase'],
            'total_profit'                  => $row['total_amount'] - $row['total_purchase'],
        );
    }

    if ($only_profit) return $result['total_profit'];

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
function calculateBKTotalAmtOfEmployee($user_id, $from_date, $to_date)
{
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

/**
 * Lưu thông tin doanh số của booking
 */
function saveRevenueBooking($booking_id)
{
    if (empty($booking_id)) return;

    global $current_user, $db;

    //Kiểm tra đã có row trong ec_revenue hay chưa
    $check_exists_sql = 'SELECT COUNT(*) FROM ec_revenue WHERE booking_id = "' . $booking_id . '" AND deleted = 0';
    $is_exists = (int)$db->getOne($check_exists_sql);

    $booking = new EC_Flight_Bookings();
    $booking->retrieve($booking_id);

    /**
     * Kiểm tra vé cận
     */
    $is_prior = 0;
    $date_entered = $booking->fetched_row['date_entered'];
    $date_ticket_issue = date('Y-m-d', strtotime($booking->date_ticket_issue));

    $sql_dep_time = 'SELECT MIN(i.departure_date) 
						FROM ec_booking_itineraries i 
						LEFT JOIN ec_flight_bookings bk ON bk.id = i.booking_id AND bk.deleted = 0
						WHERE i.deleted = 0
						AND bk.id = "' . $booking_id . '"';
    $min_dep_time = $db->getOne($sql_dep_time);
    if (!empty($date_entered) && !empty($min_dep_time)) {
        // convert sang timestamp
        $entered_ts = strtotime($date_entered) + 7 * 3600; // +7 tiếng
        $dep_ts     = strtotime($min_dep_time);

        // chênh lệch phút
        $diff_minutes = ($dep_ts - $entered_ts) / 60;

        if ($diff_minutes < 1440 && $diff_minutes >= 0) {
            $is_prior = 1; // vé cận
        }
    }

    $ds_arr = calculateBKAmt($booking_id);
    if ($is_exists) {
        // Update doanh số
        $update = "
            UPDATE ec_revenue SET
                assigned_user_id = '{$booking->assigned_user_id}',
                modified_user_id = '{$current_user->id}',
                date_ticket_issue = '{$date_ticket_issue}',
                ticket_type = '{$booking->ticket_type}',
                ticket_qty = {$booking->total_qty},
                is_prior = {$is_prior},
                total_amount_bk = {$ds_arr['total_amount_booking']},
                total_amount_points = {$ds_arr['total_amount_points']},
                total_amount_brand_refunded = {$ds_arr['total_amount_brand_refunded']},
                total_amount_receipt = {$ds_arr['total_amount_receipt']},
                total_amount = {$ds_arr['total_amount']},
                total_purchase_booking = {$ds_arr['total_purchase_booking']},
                total_purchase_luggage = {$ds_arr['total_purchase_luggage']},
                total_purchase_pass_refunded = {$ds_arr['total_purchase_pass_refunded']},
                total_purchase_points_refunded = {$ds_arr['total_purchase_points_refunded']},
                total_purchase_receipt = {$ds_arr['total_purchase_receipt']},
                total_purchase = {$ds_arr['total_purchase']},
                total_profit = {$ds_arr['total_profit']}
            WHERE booking_id = '{$booking_id}'
            AND deleted = 0
        ";

        $db->query($update);
    } else {
        // Add record
        // INSERT
        $insert = "
            INSERT INTO ec_revenue (
				id,
                booking_id,
                name,
                created_by,
				assigned_user_id,
				modified_user_id,
                date_entered_bk,
                date_ticket_issue,
                ticket_type,
                ticket_qty,
                is_prior,
                total_amount_bk,
                total_amount_points,
                total_amount_brand_refunded,
                total_amount_receipt,
                total_amount,
                total_purchase_booking,
                total_purchase_luggage,
                total_purchase_pass_refunded,
                total_purchase_points_refunded,
                total_purchase_receipt,
                total_purchase,
                total_profit,
				date_entered,
                date_modified
            ) VALUES (
			 	UUID(),
                '{$booking_id}',
                '{$booking->name}',
                '{$booking->created_by}',
                '{$booking->assigned_user_id}',
                '{$current_user->id}',
                '{$date_entered}',
                '{$date_ticket_issue}',
                '{$booking->ticket_type}',
                {$booking->total_qty},
                {$is_prior},
                {$ds_arr['total_amount_booking']},
                {$ds_arr['total_amount_points']},
                {$ds_arr['total_amount_brand_refunded']},
                {$ds_arr['total_amount_receipt']},
                {$ds_arr['total_amount']},
                {$ds_arr['total_purchase_booking']},
                {$ds_arr['total_purchase_luggage']},
                {$ds_arr['total_purchase_pass_refunded']},
                {$ds_arr['total_purchase_points_refunded']},
                {$ds_arr['total_purchase_receipt']},
                {$ds_arr['total_purchase']},
                {$ds_arr['total_profit']},
				NOW(),
                NOW()
            )
        ";
        $db->query($insert);
    }
}

/**
 * Tính toán doanh thu
 */
function calculateRevenueOfDate($from_date, $to_date, $condition_arr = array())
{
    global $db, $current_user;

    // Check permission
    // chỉ kế toán trưởng hoặc admin hệ thống mới được xem hết, còn lại xem của mình
    $sql_manager = '
        SELECT COUNT(id) 
        FROM acl_roles_users 
        WHERE 
            user_id = "' . $current_user->id . '"
            AND role_id IN (
                "' . $GLOBALS['app_list_strings']['roles_users']['QUANLY'] . '",
                "' . $GLOBALS['app_list_strings']['roles_users']['KETOAN'] . '"
            )
            AND deleted = 0';
    $is_manager = $db->getOne($sql_manager);

    $sql_role = "";
    if (!$is_manager && !is_admin($current_user)) {
        $sql_role .= " AND bk.assigned_user_id = '" . $current_user->id . "' ";
    }

    $sql_having = '';

    // Tìm theo tình trạng phiếu thu của booking: chưa thu / chưa thu đủ
    if(isset($condition_arr['payment_stt'])){
        if ($condition_arr['payment_stt'] == 1) {
            // Chưa thu
            $sql_having = ' HAVING receipt_amount = 0';
        } else if ($condition_arr['payment_stt'] == 2) {
            // Chưa thu đủ
            $sql_having = ' HAVING receipt_amount < subtotal_amount AND receipt_amount > 0';
        } else if ($condition_arr['payment_stt'] == 3) {
            // Booking telesale
            $sql_having = ' HAVING is_telesale = 1';
        } else if ($condition_arr['payment_stt'] == 4) {
            $sql_having = ' HAVING is_ctv = 1';
        }
    }

    // Where condition by booking fields
    $where_bk_fields = '';
    if(isset($condition_arr['customer_source']) && !empty($condition_arr['customer_source'])) {
        $where_bk_fields .= " AND bk.customer_source = '{$condition_arr['customer_source']}' ";
    }
    
    // Where condition by booking fields ticket_type (1/Nội địa, 2/Quốc tế)
    $where_ticket_type = '';
    if(isset($condition_arr['ticket_type']) && !empty($condition_arr['ticket_type'])) {
        $where_ticket_type .= " AND bk.ticket_type = '{$condition_arr['ticket_type']}' ";
    }

    $sql = "SELECT 
                bk.id AS parent_id
                , bk.name AS parent_name
                , 'EC_Flight_Bookings' AS parent_type
                , SUM(bkd.quantity) AS total_quantity
                , bk.total_amount AS subtotal_amount 
                , (
                    IFNULL((
                        SELECT SUM(IFNULL(pc1.down * 1000, 0))
                        FROM ec_contact_points_log pc1
                        WHERE pc1.parent_type = 'EC_Flight_Bookings' 
                            AND pc1.parent_id = bk.id 
                            AND pc1.deleted = 0
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
                                    AND pc_inner.parent_id = bk.id 
                                    AND pc_inner.deleted = 0
                            )
                            AND pc2.deleted = 0
                    ), 0)
                ) AS total_points_amount
                , (SUM(IFNULL(bkd.total_bought_price,0)) 
                +
                IFNULL((
                    SELECT IF(bk.flight_type = '0', SUM(IFNULL(px.luggage_purchase, 0)) +  SUM(IFNULL(px.luggage_purchase_inbound, 0)), SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0)))
                    FROM ec_booking_passengers px
                    WHERE px.booking_id = bk.id 
                    AND px.deleted = 0 
                    AND (px.add_type IS NULL OR px.add_type = '')
                ),0)) AS total_bought_price
                , bk.flight_type
                , bk.ticket_type
                , bk.description AS booking_description
                , bk.booking_status AS parent_status
                , bk.assigned_user_id AS user_id
                , IFNULL((
                    SELECT SUM(IFNULL(r.amount_converted,0))
                    FROM ec_receipt_voucher r
                    WHERE 
                        r.booking_id = bk.id
                        AND r.rv_status='1'
                        AND r.loai_thu='1'
                        AND r.deleted=0
                    GROUP BY r.booking_id
                ), 0) AS receipt_amount
                ,DATE_FORMAT(bk.date_ticket_issue, '%d-%m-%Y') AS date_ticket_issue
                ,DATE_FORMAT(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), '%d-%m-%Y %H:%i') AS bk_date_entered
                ,DATE_FORMAT(bk.date_ticket_issue, '%d-%m-%Y') AS bk_date_ticket_issue
                ,(
                    SELECT DATE_ADD(date_entered, INTERVAL 7 HOUR)
                    FROM ec_working_process
                    WHERE deleted = 0 AND paid = 1
                    AND parent_id = bk.id
                ) AS paid_time
                , bk.is_telesale as is_telesale
                , bk.is_ctv as is_ctv
                , bk.phone as contact_mobile
            FROM ec_booking_details bkd 
            LEFT JOIN ec_flight_bookings bk ON bkd.booking_id = bk.id AND bk.deleted=0 
            WHERE bk.booking_status IN ('3', '7', '8')
                AND bk.date_ticket_issue BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . "'
                $where_bk_fields
                $where_ticket_type
                $sql_role
                AND bkd.deleted = 0 
            GROUP BY bk.id
            $sql_having";

    if (empty($condition_arr['payment_stt'])) {
        $sql .= " UNION
                    SELECT 
                        p.id AS parent_id
                        ,p.name AS parent_name
                        ,'EC_Receipt_Voucher' AS parent_type
                        ,0 AS total_quantity
                        ,SUM(IF(p.rv_status IN (1, 2), p.amount, 0))  AS subtotal_amount
                        , 0 AS total_points_amount
                        ,SUM(
                            IF(p.rv_status IN (1, 2), IFNULL(p.bought_amount, 0), 0) 
                            + IF(p.rv_status IN (1, 2), IFNULL(p.bought_amount2, 0), 0) 
                            + IF(p.rv_status IN (1, 2), IFNULL(p.bought_amount3, 0), 0)
                        ) AS total_bought_price
                        ,'' AS flight_type
                        ,'' AS ticket_type
                        ,'' AS booking_description
                        ,p.rv_status AS parent_status
                        , p.assigned_user_id AS user_id
                        ,SUM(IF(p.rv_status IN (1, 2), p.amount, 0)) AS receipt_amount
                        ,DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%d-%m-%Y') AS date_ticket_issue
                        ,'' AS bk_date_entered
                        ,'' AS bk_date_ticket_issue
                        ,'' AS paid_time
                        , 0 as is_telesale
                        , 0 as is_ctv
                        , '' as contact_mobile
                    FROM ec_receipt_voucher p
                    WHERE 
                        p.loai_thu IN ('4', '5', '10', '11', '12', '13', '14', '16') 
                        AND DATE(p.ngayhachtoan) BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . "'
                        AND p.deleted = 0
                        " . str_replace('bk.', 'p.', $sql_role) . "
                        AND IF(p.loai_thu = 10, IF(p.bought_amount IS NULL OR p.bought_amount = 0, 0, 1), 1) = 1
                    GROUP BY p.id

                    UNION
                    SELECT 
                        hv_t.parent_id
                        , hv_t.parent_name
                        , hv_t.parent_type
                        , SUM(hv_t.total_quantity) AS total_quantity
                        , SUM(hv_t.subtotal_amount) AS subtotal_amount
                        , 0 AS total_points_amount
                        , SUM(hv_t.total_bought_price) AS total_bought_price
                        , hv_t.flight_type
                        , hv_t.ticket_type
                        , hv_t.booking_description
                        , hv_t.parent_status
                        , hv_t.user_id
                        , hv_t.receipt_amount
                        , hv_t.date_ticket_issue
                        , '' AS bk_date_entered
                        , '' AS bk_date_ticket_issue
                        , '' AS paid_time
                        , 0 as is_telesale
                        , 0 as is_ctv
                        , '' as contact_mobile
                    FROM 
                    (
                        -- hoan ve < 0
                        SELECT 
                            p.id AS parent_id
                            ,p.name AS parent_name
                            ,'EC_HoanVe' AS parent_type
                            , -(SELECT COUNT(id) FROM ec_chitiethoanve WHERE deleted = 0 AND hoanve_id = p.id) AS total_quantity
                            , - IF(SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) <= 0, SUM(IFNULL(p.tongtienkhach,0)), 0) AS subtotal_amount
                            , 0 AS total_points_amount
                            , - IF(SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) <= 0, SUM(IFNULL(p.tongtienhang,0)), 0)  AS total_bought_price
                            ,'' AS flight_type
                            ,'' AS ticket_type
                            ,'' AS booking_description
                            , p.tinhtrang AS parent_status
                            , p.assigned_user_id AS user_id
                            , 0 AS receipt_amount
                            ,DATE_FORMAT(p.ngayhachtoan, '%d-%m-%Y') AS date_ticket_issue
                        FROM ec_hoanve p
                            INNER JOIN ec_flight_bookings bk ON bk.deleted = 0 AND bk.id = p.booking_id $where_bk_fields
                        WHERE p.deleted=0
                            AND p.tinhtrang='1'
                            AND p.ngayhachtoan BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . "'
                            " . $sql_role . "
                        GROUP BY p.id

                        -- hoan ve > 0
                        UNION
                        SELECT 
                            p.id AS parent_id
                            ,p.name AS parent_name
                            ,'EC_HoanVe' AS parent_type
                            , 0 AS total_quantity
                            , - SUM(IFNULL(p.tongtienkhach,0)) AS subtotal_amount
                            , 0 AS total_points_amount
                            , - SUM(IFNULL(p.tongtienhang,0))  AS total_bought_price
                            , '' AS flight_type
                            , '' AS ticket_type
                            , '' AS booking_description
                            , p.tinhtrang AS parent_status
                            , p.assigned_user_id AS user_id
                            , 0 AS receipt_amount
                            ,DATE_FORMAT(p.ngayhachtoan, '%d-%m-%Y') AS date_ticket_issue
                        FROM ec_hoanve p
                            INNER JOIN ec_flight_bookings bk ON bk.deleted = 0 AND bk.id = p.booking_id $where_bk_fields
                        WHERE p.deleted=0
                            AND p.tinhtrang='1' 
                            AND p.ngayhachtoan BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . "'
                            " . str_replace('bk', 'p', $sql_role) . "
                        GROUP BY p.id
                        HAVING SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) > 0
                    ) AS hv_t
                    GROUP BY hv_t.parent_id
                    ORDER BY total_quantity DESC 
                ";
    }

    $result = array(
        'from_date' => $from_date,
        'to_date' => $to_date,
        'count' => 0,
        'total_profit' => 0,
        'total_revenue' => 0,
        'total_bought' => 0,
        'details' => array()
    );
    // pr($sql);

    $res    = $db->query($sql);
    $i = 0;
    while ($row = $db->fetchByAssoc($res)) {
        // Tổng doanh số
        $profit_amount = $row['subtotal_amount'] - $row['total_bought_price'];
        $result['total_profit'] += $profit_amount;
        $result['total_revenue'] += $row['subtotal_amount'];
        $result['total_bought'] += $row['total_bought_price'];

        // Details
        $result['details'][$row['parent_id']] = $row;
        $result['details'][$row['parent_id']]['profit_amount'] = $profit_amount;

        $i++;
    }

    $result['count'] = $i;

    return $result;
}

/**
 * Cập nhật giá trị vé cận booking
 */
function updateIsPriorForBooking($booking_id)
{

    if (empty($booking_id)) return;

    global $db;
    $sql = "UPDATE ec_flight_bookings bk
            SET bk.is_prior =
            (
                SELECT
                    IF(
                        TIMESTAMPDIFF(
                            MINUTE,
                            DATE_ADD(bk.date_entered, INTERVAL 7 HOUR),
                            MIN(i.departure_date)
                        ) BETWEEN 1 AND 1440,
                        1,
                        0
                    )
                FROM ec_booking_itineraries i
                WHERE i.booking_id = bk.id
                AND i.deleted = 0
            )
            WHERE bk.id = '{$booking_id}';
    ";
    $db->query($sql);
}
