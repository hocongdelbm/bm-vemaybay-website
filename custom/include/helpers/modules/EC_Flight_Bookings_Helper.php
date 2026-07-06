<?php
class EC_Flight_Bookings_Helper
{
    /**
     * Get online payment link
     *
     * @param string|null $booking_id
     * @param string|null $created_by_id User id create booking
     * @return string URL
     */
    public static function get_online_payment_link(?string $booking_id, ?string $created_by_id)
    {
        $booking_id = (string) $booking_id;
        $created_by_id = (string) $created_by_id;

        $domain_name = get_server_name($created_by_id);
        if (in_array($domain_name, ['vietjet.net', 'timchuyenbay.com', 'timchuyenbay.vn', 'vemaybay5s.com'])) {
            $payment_link = "https://$domain_name/thanh-toan-online?bkid=$booking_id";
        } else {
            $payment_link = "https://timchuyenbay.vn/thanh-toan-online?bkid=$booking_id";
        }

        return $payment_link;
    }

    /**
     * Get bonus report by flight datetime
     * 
     * @param string $from_datetime Follow user's timezone and format
     * @param string $to_datetime Follow user's timezone and format
     * @return array
     */
    public static function get_bonus_report(string $from_datetime, string $to_datetime) {
        global $db, $current_user, $sugar_config;

        /******  1. HANDLING DATE & TIME FORMAT  ******/

        $timezone   = $current_user->getPreference('timezone') ?: 'Asia/Ho_Chi_Minh';
        $dateFormat = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');
        $timeFormat = $current_user->getPreference('timef') ?: ($sugar_config['timef'] ?? 'H:i');

        try {
            $user_tz = new DateTimeZone($timezone);
        } catch (Exception $e) {
            $user_tz = new DateTimeZone('Asia/Ho_Chi_Minh');
        }
        // $utc_tz = new DateTimeZone('UTC');
        $vn_tz  = new DateTimeZone('Asia/Ho_Chi_Minh');

        // $from_datetime / $to_datetime đã đúng định dạng của user, chỉ cần parse theo múi giờ user
        // (thử date+time trước, rồi date-only)
        $parseUserDate = function ($value) use ($dateFormat, $timeFormat, $user_tz) {
            $value = trim((string) $value);
            return DateTime::createFromFormat($dateFormat . ' ' . $timeFormat, $value, $user_tz)
                ?: (DateTime::createFromFormat($dateFormat . '|', $value, $user_tz) ?: new DateTime('now', $user_tz));
        };
        $from_dt = $parseUserDate($from_datetime);
        $to_dt   = $parseUserDate($to_datetime);

        $from_vn_datetime_db = (clone $from_dt)->setTime(0, 0, 0)->setTimezone($vn_tz)->format('Y-m-d H:i:s');
        $to_vn_datetime_db   = (clone $to_dt)->setTime(23, 59, 59)->setTimezone($vn_tz)->format('Y-m-d H:i:s');

        // // Use for query KPI
        // $from_date = $from_dt->format($dateFormat);
        // $to_date   = $to_dt->format($dateFormat);

        // $from_utc_datetime_db = (clone $from_dt)->setTime(0, 0, 0)->setTimezone($utc_tz)->format('Y-m-d H:i:s');
        // $to_utc_datetime_db   = (clone $to_dt)->setTime(23, 59, 59)->setTimezone($utc_tz)->format('Y-m-d H:i:s');

        /******  2. HANDLING CONDITIONS  ******/

        $sql_role = "";
        if(!is_admin($current_user) && $current_user->title != 'QuanLy') {
            // Chỉ kế toán trưởng hoặc admin hệ thống mới được xem hết, còn lại xem của mình
            $is_manager = $db->getOne(
                "SELECT COUNT(id) 
                FROM acl_roles_users 
                WHERE user_id = '{$current_user->id}'
                    AND role_id IN (
                        '{$GLOBALS['app_list_strings']['roles_users']['QUANLY']}',
                        '{$GLOBALS['app_list_strings']['roles_users']['KETOAN']}'
                    )
                    AND deleted = 0"
            );

            if(!$is_manager) {
                $sql_role .= " AND bk.assigned_user_id = '{$current_user->id}' ";
            }
        }

        // Main query
        $sql =
            "SELECT 
                bk.id AS parent_id
                , bk.name AS parent_name
                , 'EC_Flight_Bookings' AS parent_type
                , bk.booking_status AS parent_status
                , bk.total_amount AS revenue
                , (
                    SUM(IFNULL(bkd.total_bought_price, 0))
                    +
                    IFNULL((
                        SELECT IF(bk.flight_type = '1',
                                SUM(IFNULL(px.luggage_purchase, 0)),
                                SUM(IFNULL(px.luggage_purchase, 0)) + SUM(IFNULL(px.luggage_purchase_inbound, 0))
                            )
                        FROM ec_booking_passengers px
                        WHERE px.booking_id = bk.id 
                            AND px.deleted = 0 
                            AND (px.add_type IS NULL OR px.add_type = '')
                    ), 0)
                    +
                    SUM(IFNULL(bkd.fee_bought, 0))
                ) AS cost
                , iti.departure_date AS flight_date
                , CONCAT(
                    IFNULL(bk.id, ''), '|',
                    IFNULL(bk.ticket_type, ''), '|',
                    IFNULL(bk.flight_type, ''), '|',
                    SUM(IFNULL(bkd.quantity, 0)), '|',
                    IFNULL(bk.customer_source, ''), '|',
                    IFNULL(bk.zalo_id, ''), '|',
                    IFNULL(bk.is_reference, '')
                ) AS booking_data
            FROM ec_booking_details bkd
                INNER JOIN ec_flight_bookings bk ON bk.id = bkd.booking_id AND bk.deleted = 0
                INNER JOIN ec_booking_itineraries iti ON iti.id = (
                    SELECT iti2.id
                    FROM ec_booking_itineraries iti2
                    WHERE iti2.booking_id = bkd.booking_id
                        AND iti2.deleted = 0
                        AND iti2.direction = IF(bk.flight_type = '1', '0', '1')
                        AND NOT EXISTS (
                            SELECT 1
                            FROM ec_booking_itineraries iti3
                            WHERE iti3.booking_id = iti2.booking_id
                                AND iti3.deleted = 0
                                AND iti3.direction = iti2.direction
                                AND IFNULL(iti3.add_type, 0) = IFNULL(iti2.add_type, 0)
                                AND IFNULL(iti3.assigned_user_id, '') = IFNULL(iti2.assigned_user_id, '')
                                AND CAST(IFNULL(iti3.sabre_logs, '0') AS UNSIGNED) > CAST(IFNULL(iti2.sabre_logs, '0') AS UNSIGNED)
                        )
                    ORDER BY (IFNULL(iti2.add_type, 0) = 3) DESC,
                        iti2.departure_date DESC
                    LIMIT 1
                )
            WHERE iti.departure_date BETWEEN '$from_vn_datetime_db' AND '$to_vn_datetime_db'
                AND bk.booking_status = '8'
                AND bkd.deleted = 0 
                $sql_role
            GROUP BY bk.id";

        $result = [
            'total' => [],
            'parentInfo' => [],
            'details' => []
        ];
        $validZaloList = [];

        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $revenue = $row['revenue'];
            $cost    = $row['cost'];
            $profit  = $revenue - $cost;

            // Total bonus
            $totalBonus = 0;
            if($row['parent_type'] == 'EC_Flight_Bookings') {
                // Unpack booking fields combined by CONCAT in the main query
                list(
                    $bkId,
                    $bkTicketType,
                    $bkFlightType,
                    $bkTicketQty,
                    $bkSource,
                    $bkZaloId,
                    $bkIsReference
                ) = array_pad(explode('|', $row['booking_data'] ?? ''), 6, '');

                // Check zalo in booking
                $isValidZalo = 0;
                if(array_search($bkZaloId, $validZaloList) === false) {
                    $isValidZalo = $db->getOne("SELECT COUNT(*) FROM ec_zalo_contacts WHERE zalo_id = '$bkZaloId' AND deleted = 0") ?? 0;
                    $validZaloList[] = $bkZaloId;
                }
                else {
                    $isValidZalo = 1;
                }
            
                // Percent bonus
                $bonusPercent = 0;
                $extraBonusPercent = 0;
                if($bkIsReference || $bkSource === 'care') {
                    $bonusPercent = 0.2; // 20%
                    $extraBonusPercent = 0.3; // 30%
                }
                else if(in_array($bkSource, ['system_ads', 'system_old'])) {
                    $bonusPercent = 0.05; // 5%
                    $extraBonusPercent = 0.1; // 10%
                }
                else if($bkSource === 'new') {
                    $bonusPercent = 0.3; // 30%
                    $extraBonusPercent = 0.4; // 40%
                }

                // Bonus threshold
                $minThresholdValue = 110000;
                $extraThresholdValue = 120000;
                if($bkTicketType == '2') { // International
                    $minThresholdValue = $extraThresholdValue = $bkFlightType === '1' ? 300000 : 600000;
                }
                
                $avgProfit = $profit / $bkTicketQty;

                $bonusPerTicket = 0;
                if($avgProfit < $minThresholdValue) continue;
                if($avgProfit >= $minThresholdValue) $bonusPerTicket = ($bonusPercent * $minThresholdValue);
                if($avgProfit > $extraThresholdValue) $bonusPerTicket += $extraBonusPercent * ($avgProfit - $extraThresholdValue);

                // Total bonus
                $totalBonus = $bonusPerTicket * $bkTicketQty;
                if(!$isValidZalo) $totalBonus /= 2;
                $totalDirectBonus   = $totalBonus * 0.7; // For user completed booking
                $totalIndirectBonus = $totalBonus - $totalDirectBonus;
                
                // Indirect bonus detail
                $indirectHeirData    = EC_Working_Process_Helper::get_kpi_by_bookings([$bkId]);
                $totalIndirectKPI    = $indirectHeirData['total_kpi'] ?: 0;
                if($totalIndirectKPI > 1) $totalIndirectKPI -= 1; // Remove count of completing step
                $indirectBonusPerKPI = $totalIndirectBonus / $totalIndirectKPI;

                // Parent-level info, shared by every user working on this booking
                $result['parentInfo'][$row['parent_id']] = [
                    'flightDate'         => date("$dateFormat $timeFormat", strtotime($row['flight_date'])),
                    'parentName'         => $row['parent_name'] ?? '',
                    'parentType'         => $row['parent_type'] ?? '',
                    'isInter'            => $bkTicketType == '2' ? true : false,
                    'totalTicketQty'     => $bkTicketQty,
                    'totalRevenue'       => $revenue,
                    'totalCost'          => $cost,
                    'totalProfit'        => $profit,

                    'avgProfit'          => $avgProfit,
                    'minThresholdValue'  => $minThresholdValue,
                    'extraThresholdValue'=> $extraThresholdValue,
                    'bonusPercent'       => $bonusPercent,
                    'extraBonusPercent'  => $extraBonusPercent,
                    'bonusPerTicket'     => $bonusPerTicket,

                    'totalDirectBonus'   => $totalDirectBonus,
                    'totalIndirectBonus' => $totalIndirectBonus,
                    'totalIndirectKPI'   => $totalIndirectKPI,
                ];

                $setDirectBonus = false;
                foreach($indirectHeirData['bookings'][$bkId] as $userId => $arr) {
                    $countKPI = $arr['total_kpi'] ?? 0;

                    if(!isset($result['total'][$userId])) {
                        $result['total'][$userId] = $indirectBonusPerKPI * $countKPI;
                        $result['details'][$userId] = [];
                    }
                    else {
                        $result['total'][$userId] += $indirectBonusPerKPI * $countKPI;
                    }

                    if(!isset($result['details'][$userId][$row['parent_id']])) {
                        $result['details'][$userId][$row['parent_id']] = [
                            'kpi' => $countKPI,
                            'indirectBonus' => $indirectBonusPerKPI * $countKPI,
                        ];
                    }
                    else {
                        $result['details'][$userId][$row['parent_id']]['indirectBonus'] += $indirectBonusPerKPI * $countKPI;
                    }

                    if(!$setDirectBonus && $arr['completed'] > 0) {
                        $result['total'][$userId] += $totalDirectBonus;
                        $result['details'][$userId][$row['parent_id']]['directBonus'] += $totalDirectBonus;
                        $setDirectBonus = true;
                    }
                }
            }
            else if($row['parent_type'] == 'EC_Receipt_Voucher') {

            }
            else if($row['parent_type'] == 'EC_HoanVe') {

            }
        }
        return $result;
    }
}
