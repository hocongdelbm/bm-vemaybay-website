<?php
class EC_Bonus_Helper
{
    /**
     * Compute the bonus report and persist it into ec_bonus,
     * one record per (parent record, user).
     *
     * Re-runnable for the same range: existing rows are updated, and rows
     * whose (booking, user) no longer appears in the fresh result (e.g. the
     * booking left status 8) are soft-deleted.
     *
     * @param string $fromTime Y-m-d H:i:s, Vietnam timezone
     * @param string $toTime   Y-m-d H:i:s, Vietnam timezone
     * @param bool   $isSave   true: also persist the calculated rows into ec_bonus (upsert)
     * @return array user_id => source_id => ['kpi' => int, 'directBonus' => float, 'indirectBonus' => float]
     */
    public static function save_bonus_report(string $fromTime = '', string $toTime = '', bool $isSave = false): array {
        global $db, $current_user;

        // Main query (Booking)
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
            WHERE iti.departure_date BETWEEN '$fromTime' AND '$toTime'
                AND bk.date_ticket_issue > '2026-06-30'
                AND bk.booking_status = '8'
                AND bkd.deleted = 0
            GROUP BY bk.id";

        // // Receipt voucher
        // $sql .= " UNION " .
        //     "SELECT 
        //         rv.id AS parent_id
        //         , rv.name AS parent_name
        //         , 'EC_Receipt_Voucher' AS parent_type
        //         , rv.rv_status AS parent_status
        //         , rv.assigned_user_id AS parent_assigned_user_id
        //         , SUM(IFNULL(rv.amount, 0)) AS revenue
        //         , SUM(IFNULL(rv.bought_amount, 0) + IFNULL(rv.bought_amount2, 0) + IFNULL(rv.bought_amount3, 0)) AS cost
        //         , '' AS flight_date
        //         , '' AS booking_data
        //     FROM ec_receipt_voucher rv
        //         LEFT JOIN ec_flight_bookings bk ON bk.id = rv.booking_id AND bk.deleted = 0
        //     WHERE 
        //         rv.rv_status = '1'
        //         AND rv.loai_thu = '4'
        //         AND rv.deleted = 0
        //         " . str_replace('bk.', 'rv.', $sql_role) . "
        //     GROUP BY rv.id";

        $results = [];
        $parentInfo = [];
        $validZaloList = [];

        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $revenue = $row['revenue'];
            $cost    = $row['cost'];
            $profit  = $revenue - $cost;

            if($row['parent_type'] == 'EC_Flight_Bookings') {
                $parentInfo[$row['parent_id']] = [
                    'name'       => $row['parent_name'],
                    'type'       => $row['parent_type'],
                    'flightDate' => $row['flight_date'],
                ];

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

                // Check zalo rule
                $isValidZalo = 0;
                if(!isset($validZaloList[$bkZaloId]) && !empty($bkZaloId)) {
                    $isValidZalo = $db->getOne("SELECT COUNT(*) FROM ec_zalo_contacts WHERE zalo_id = '$bkZaloId' AND deleted = 0") ?? 0;
                    if($isValidZalo) $validZaloList[$bkZaloId] = true;
                    else $validZaloList[$bkZaloId] = false;
                }
                else {
                    $isValidZalo = empty($bkZaloId) ? 0 : $validZaloList[$bkZaloId];
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

                foreach($indirectHeirData['bookings'][$bkId] as $userId => $arr) {
                    $countKPI = $arr['total_kpi'] ?? 0;
                    if(isset($arr['completed']) && $arr['completed'] > 0) $countKPI -= $arr['completed'];

                    if(!isset($results[$userId])) $results[$userId] = [];

                    if(!isset($results[$userId][$row['parent_id']])) {
                        $results[$userId][$row['parent_id']] = [
                            'kpi' => $countKPI,
                            'indirectBonus' => $indirectBonusPerKPI * $countKPI,
                        ];
                    }
                    else {
                        $results[$userId][$row['parent_id']]['indirectBonus'] += $indirectBonusPerKPI * $countKPI;
                    }

                    if($arr['completed'] > 0) {
                        $results[$userId][$row['parent_id']]['directBonus'] = $totalDirectBonus;
                    }
                }
            }
        }

        if (!$isSave) {
            return $results;
        }

        // Persist: one ec_bonus row per (source record, user), updated in place when it already exists
        foreach ($results as $user_id => $bookings) {
            foreach ($bookings as $parent_id => $row) {
                $info = $parentInfo[$parent_id] ?? [];
                $source_type = !empty($info['type']) ? $info['type'] : 'EC_Flight_Bookings';

                $bonusTimeUTC = DatetimeHelper::convert_datetime(
                    (string) ($info['flightDate'] ?? ''),
                    'Y-m-d H:i:s', 'Y-m-d H:i:s', 'Asia/Ho_Chi_Minh', 'UTC'
                ) ?: '';

                $direct   = (float) ($row['directBonus'] ?? 0);
                $indirect = (float) ($row['indirectBonus'] ?? 0);
                $kpi      = (int) ($row['kpi'] ?? 0);

                $parent_id_q   = $db->quote((string) $parent_id);
                $user_id_q     = $db->quote((string) $user_id);
                $source_type_q = $db->quote((string) $source_type);

                $is_exists = (int) $db->getOne(
                    "SELECT COUNT(*)
                    FROM ec_bonus
                    WHERE source_id = '{$parent_id_q}'
                        AND source_type = '{$source_type_q}'
                        AND assigned_user_id = '{$user_id_q}'
                        AND deleted = 0"
                );

                if ($is_exists) {
                    $db->query(
                        "UPDATE ec_bonus
                        SET bonus_time = '" . $db->quote($bonusTimeUTC) . "'
                            ,direct_bonus = {$direct}
                            ,indirect_bonus = {$indirect}
                            ,kpi = {$kpi}
                            ,date_modified = NOW()
                            ,modified_user_id = '1'
                        WHERE source_id = '{$parent_id_q}'
                            AND source_type = '{$source_type_q}'
                            AND assigned_user_id = '{$user_id_q}'
                            AND deleted = 0"
                    );
                }
                else {
                    $bean = BeanFactory::newBean('EC_Bonus');
                    $bean->name             = (string) ($info['name'] ?? '');
                    $bean->source_id        = $parent_id;
                    $bean->source_type      = $source_type;
                    $bean->assigned_user_id = $user_id;
                    $bean->bonus_time       = $bonusTimeUTC;
                    $bean->direct_bonus     = $direct;
                    $bean->indirect_bonus   = $indirect;
                    $bean->kpi              = $kpi;
                    $bean->save();
                }
            }
        }

        return $results;








    }

    public static function get_source_bonus($source_id, $source_type) {

    }
}
