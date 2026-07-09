<?php
class EC_Bonus_Helper {
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
     * @return array [users, sources]
     */
    public static function save_bonus_report(string $fromTime = '', string $toTime = '', bool $isSave = true): array {
        global $db, $current_user, $sugar_config;

        $timezone = $current_user->getPreference('timezone') ?: 'Asia/Ho_Chi_Minh';
        $dateFormat = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');
        $timeFormat = $current_user->getPreference('timef') ?: ($sugar_config['timef'] ?? 'H:i');

        // Main query (Booking)
        $sql = self::build_booking_bonus_sql("iti.departure_date BETWEEN '$fromTime' AND '$toTime'");

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

        $validZaloList = [];
        $results = [
            "users" => [],
            "sources" => [],
        ];

        try {
            $res = $db->query($sql);
            while ($row = $db->fetchByAssoc($res)) {
                if($row['parent_type'] == 'EC_Flight_Bookings') {
                    $calc = self::calc_booking_bonus($row, $validZaloList);
                    if($calc === null) continue;

                    foreach($calc['userKPI'] as $userId => $kpiInfo) {
                        $countKPI = $kpiInfo['total_kpi'] ?? 0;
                        if(isset($kpiInfo['completed']) && $kpiInfo['completed'] > 0) $countKPI -= $kpiInfo['completed'];

                        // Init empty array
                        if(!isset($results['users'][$userId])) $results['users'][$userId] = [];

                        if(!isset($results['users'][$userId][$row['parent_id']])) {
                            $results['users'][$userId][$row['parent_id']] = [
                                'srcName'       => $calc['srcName'],
                                'srcType'       => $calc['srcType'],
                                'bonusTime'     => $calc['bonusTime'],
                                'kpi'           => $countKPI,
                                'indirectBonus' => $calc['indirectBonusPerKPI'] * $countKPI,
                            ];
                        }
                        else {
                            $results['users'][$userId][$row['parent_id']]['indirectBonus'] += $calc['indirectBonusPerKPI'] * $countKPI;
                        }

                        if($kpiInfo['completed'] > 0) {
                            $results['users'][$userId][$row['parent_id']]['directBonus'] = $calc['totalDirectBonus'];
                        }
                    }
                }
            }

            if (!$isSave) return $results;

            // Persist: one ec_bonus row per (source record, user), updated in place when it already exists
            foreach ($results['users'] as $userId => $src) {
                foreach ($src as $srcId => $row) {
                    $bonusTimeUTC = DatetimeHelper::convert_datetime(
                        (string) ($row['bonusTime'] ?? ''),
                        'Y-m-d H:i:s', 'Y-m-d H:i:s', 'Asia/Ho_Chi_Minh', 'UTC'
                    ) ?: '';

                    $direct   = $row['directBonus'] ?? 0;
                    $indirect = $row['indirectBonus'] ?? 0;
                    $kpi      = (int) ($row['kpi'] ?? 0);

                    $is_exists = (int) $db->getOne(
                        "SELECT COUNT(*)
                        FROM ec_bonus
                        WHERE source_id = '{$srcId}'
                            AND source_type = '{$row['srcType']}'
                            AND assigned_user_id = '{$userId}'
                            AND deleted = 0"
                    );

                    if (!$is_exists) {
                        $bonusTimeUser = DatetimeHelper::convert_datetime(
                            (string) ($row['bonusTime'] ?? ''),
                            "Y-m-d H:i:s", "$dateFormat $timeFormat", "Asia/Ho_Chi_Minh", $timezone
                        ) ?: '';

                        /** @var EC_Bonus **/
                        $bean = BeanFactory::newBean('EC_Bonus');
                        $bean->name             = (string) ($row['srcName'] ?? '');
                        $bean->source_id        = $srcId;
                        $bean->source_type      = $row['srcType'];
                        $bean->assigned_user_id = $userId;
                        $bean->bonus_time       = $bonusTimeUser;
                        $bean->direct_bonus     = $direct;
                        $bean->indirect_bonus   = $indirect;
                        $bean->kpi              = $kpi;
                        $bean->save();
                    }
                    else {
                        $db->query(
                            "UPDATE ec_bonus
                            SET bonus_time = '{$bonusTimeUTC}'
                                ,direct_bonus = {$direct}
                                ,indirect_bonus = {$indirect}
                                ,kpi = {$kpi}
                                ,date_modified = NOW()
                                ,modified_user_id = '1'
                            WHERE source_id = '{$srcId}'
                                AND source_type = '{$row['srcType']}'
                                AND assigned_user_id = '{$userId}'
                                AND deleted = 0"
                        );
                    }
                }
            }

            return $results;
        }
        catch(Throwable $th) {
            $GLOBALS['log']->error(__METHOD__ . ": {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return [];
        }
    }

    /**
     * Get bonus of a specific source
     *
     * @param string $sourceName
     * @param string $sourceType
     * @return array
     */
    public static function get_source_bonus(string $sourceName, string $sourceType) {
        if(empty($sourceType) || empty($sourceName)) return [];

        global $db;

        $sourceName = trim($sourceName);
        $sourceType = trim($sourceType);

        $sql = "";
        if($sourceType == 'EC_Flight_Bookings') {
            $sql = self::build_booking_bonus_sql("bk.name = '$sourceName'");
        }

        if($sql === "") return [];

        $result = [];
        $validZaloList = [];

        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $calc = self::calc_booking_bonus($row, $validZaloList);
            if($calc === null) continue;

            unset($calc['userKPI']);
            $result = $calc;
        }
        return $result;
    }

    /**
     * Build the booking bonus query shared by save_bonus_report and get_source_bonus.
     * Selects one row per booking with revenue, cost, flight date and the
     * CONCAT-packed booking_data used by calc_booking_bonus().
     *
     * @param string $whereClause Scope condition (already escaped), e.g. date range or booking name
     */
    private static function build_booking_bonus_sql(string $whereClause): string {
        return
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
            WHERE {$whereClause}
                AND bk.date_ticket_issue > '2026-06-30'
                AND bk.booking_status = '8'
                AND bkd.deleted = 0
            GROUP BY bk.id";
    }

    /**
     * Apply the bonus rules to one row of build_booking_bonus_sql().
     *
     * @param array $row           SQL row (parent_*, revenue, cost, flight_date, booking_data)
     * @param array $validZaloList Per-run zalo_id => bool cache, shared by reference between rows
     * @return array|null Full calculation detail, or null when the booking
     *                    does not reach the minimum profit threshold
     */
    private static function calc_booking_bonus(array $row, array &$validZaloList) {
        global $db;

        $revenue = $row['revenue'];
        $cost    = $row['cost'];
        $profit  = $revenue - $cost;

        if($profit < 1) return null;

        // Unpack booking fields combined by CONCAT in the main query
        list(
            $bkId,
            $bkTicketType,
            $bkFlightType,
            $bkTicketQty,
            $bkSource,
            $bkZaloId,
            $bkIsReference
        ) = array_pad(explode('|', $row['booking_data'] ?? ''), 7, '');

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

        $avgProfit = round($profit / $bkTicketQty);

        if($avgProfit < $minThresholdValue) return null;
        $bonusPerTicket = $bonusPercent * $minThresholdValue;
        if($avgProfit > $extraThresholdValue) $bonusPerTicket += $extraBonusPercent * ($avgProfit - $extraThresholdValue);

        // Total bonus, rounded to whole VND (direct + indirect always equals total)
        if(!$isValidZalo) $bonusPerTicket = round($bonusPerTicket / 2);
        $totalBonus = $bonusPerTicket * $bkTicketQty;
        $totalDirectBonus   = round($totalBonus * 0.7); // For user completed booking
        $totalIndirectBonus = $totalBonus - $totalDirectBonus;

        // Indirect bonus detail
        $kpiData = EC_Working_Process_Helper::get_kpi_by_bookings([$bkId]);
        $totalIndirectKPI = $kpiData['total_indirect_kpi'] ?: 0;
        $indirectBonusPerKPI = $totalIndirectKPI > 0 ? round($totalIndirectBonus / $totalIndirectKPI) : 0;

        return [
            'srcId'                 => $row['parent_id'],
            'srcName'               => $row['parent_name'],
            'srcType'               => 'EC_Flight_Bookings',
            'bonusTime'             => $row['flight_date'],
            'revenue'               => $revenue,
            'cost'                  => $cost,
            'profit'                => $profit,
            'ticketQty'             => $bkTicketQty,
            'minThresholdValue'     => $minThresholdValue,
            'extraThresholdValue'   => $extraThresholdValue,
            'bonusPercent'          => $bonusPercent,
            'extraBonusPercent'     => $extraBonusPercent,
            'isValidZalo'           => (bool) $isValidZalo,
            'bonusPerTicket'        => $bonusPerTicket,
            'totalBonus'            => $totalBonus,
            'totalDirectBonus'      => $totalDirectBonus,
            'totalIndirectBonus'    => $totalIndirectBonus,
            'totalIndirectKPI'      => $totalIndirectKPI,
            'indirectBonusPerKPI'   => $indirectBonusPerKPI,
            'userKPI'               => $kpiData['bookings'][$bkId] ?? [],
        ];
    }
}
