<?php
class EC_Booking_Bonus_Helper
{
    /** The bonus policy starts on this date (d-m-Y) — nothing is saved before it */
    private const MIN_REPORT_DATE = '01-07-2026';

    /**
     * Compute the bonus report and persist it into ec_booking_bonus,
     * one record per (booking, user).
     *
     * Re-runnable for the same range: existing rows are updated, and rows
     * whose (booking, user) no longer appears in the fresh result (e.g. the
     * booking left status 8) are soft-deleted.
     *
     * @param string $from_date Date in the current user's format; defaults to 3 days ago
     * @param string $to_date   Date in the current user's format; defaults to today
     * @return array ['saved' => int, 'removed' => int]
     */
    public static function save_bonus_report(string $from_date = '', string $to_date = ''): array
    {
        global $db, $current_user, $sugar_config;

        $dateFormat = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');
        $timeFormat = $current_user->getPreference('timef') ?: ($sugar_config['timef'] ?? 'H:i');
        $timezone   = $current_user->getPreference('timezone') ?: 'Asia/Ho_Chi_Minh';

        try {
            $user_tz = new DateTimeZone($timezone);
        } catch (Exception $e) {
            $user_tz = new DateTimeZone('Asia/Ho_Chi_Minh');
        }
        $vn_tz = new DateTimeZone('Asia/Ho_Chi_Minh');

        if (empty($from_date)) $from_date = date($dateFormat, strtotime('-3 days'));
        if (empty($to_date))   $to_date   = date($dateFormat, time());

        $min_dt  = DateTime::createFromFormat('!d-m-Y', self::MIN_REPORT_DATE, $user_tz);
        $from_dt = DateTime::createFromFormat($dateFormat . '|', trim($from_date), $user_tz) ?: clone $min_dt;
        $to_dt   = DateTime::createFromFormat($dateFormat . '|', trim($to_date), $user_tz) ?: clone $min_dt;

        // Clamp to the policy start so old flights never get bonus rows
        if ($from_dt < $min_dt) $from_dt = clone $min_dt;
        if ($to_dt < $min_dt)   $to_dt   = clone $min_dt;

        $from_date = $from_dt->format($dateFormat);
        $to_date   = $to_dt->format($dateFormat);

        // Same VN-time day boundaries as get_bonus_report uses for its query,
        // reused here to find stale rows of the range
        $from_vn_db = (clone $from_dt)->setTime(0, 0, 0)->setTimezone($vn_tz)->format('Y-m-d H:i:s');
        $to_vn_db   = (clone $to_dt)->setTime(23, 59, 59)->setTimezone($vn_tz)->format('Y-m-d H:i:s');

        $report = EC_Flight_Bookings_Helper::get_bonus_report($from_date, $to_date);

        $saved = 0;
        $fresh_keys = [];

        foreach (($report['details'] ?? []) as $user_id => $bookings) {
            foreach ($bookings as $booking_id => $row) {
                $parent = $report['parentInfo'][$booking_id] ?? [];

                // parentInfo stores flightDate already formatted for display —
                // parse it back to the DB format (VN time end to end)
                $flight_dt = DateTime::createFromFormat($dateFormat . ' ' . $timeFormat, (string) ($parent['flightDate'] ?? ''), $vn_tz);
                $flight_date_db = $flight_dt ? $flight_dt->format('Y-m-d H:i:s') : $from_vn_db;

                $direct   = (float) ($row['directBonus'] ?? 0);
                $indirect = (float) ($row['indirectBonus'] ?? 0);

                $fields = [
                    'name'                  => "'" . $db->quote((string) ($parent['parentName'] ?? '')) . "'",
                    'assigned_user_id'      => "'" . $db->quote((string) $user_id) . "'",
                    'modified_user_id'      => "'" . $db->quote((string) $current_user->id) . "'",
                    'flight_date'           => "'" . $flight_date_db . "'",
                    'is_inter'              => !empty($parent['isInter']) ? 1 : 0,
                    'ticket_qty'            => (int) ($parent['totalTicketQty'] ?? 0),
                    'total_revenue'         => (float) ($parent['totalRevenue'] ?? 0),
                    'total_cost'            => (float) ($parent['totalCost'] ?? 0),
                    'total_profit'          => (float) ($parent['totalProfit'] ?? 0),
                    'avg_profit'            => (float) ($parent['avgProfit'] ?? 0),
                    'min_threshold_value'   => (float) ($parent['minThresholdValue'] ?? 0),
                    'extra_threshold_value' => (float) ($parent['extraThresholdValue'] ?? 0),
                    'bonus_percent'         => (float) ($parent['bonusPercent'] ?? 0),
                    'extra_bonus_percent'   => (float) ($parent['extraBonusPercent'] ?? 0),
                    'bonus_per_ticket'      => (float) ($parent['bonusPerTicket'] ?? 0),
                    'total_direct_bonus'    => (float) ($parent['totalDirectBonus'] ?? 0),
                    'total_indirect_bonus'  => (float) ($parent['totalIndirectBonus'] ?? 0),
                    'total_indirect_kpi'    => (int) ($parent['totalIndirectKPI'] ?? 0),
                    'kpi'                   => (int) ($row['kpi'] ?? 0),
                    'direct_bonus'          => $direct,
                    'indirect_bonus'        => $indirect,
                    'total_bonus'           => $direct + $indirect,
                ];

                $booking_id_q = $db->quote((string) $booking_id);
                $user_id_q    = $db->quote((string) $user_id);
                $fresh_keys[] = "'" . $booking_id_q . '|' . $user_id_q . "'";

                $is_exists = (int) $db->getOne(
                    "SELECT COUNT(*) FROM ec_booking_bonus
                    WHERE booking_id = '{$booking_id_q}'
                        AND assigned_user_id = '{$user_id_q}'
                        AND deleted = 0"
                );

                if ($is_exists) {
                    $set = [];
                    foreach ($fields as $col => $val) $set[] = "$col = $val";
                    $db->query(
                        "UPDATE ec_booking_bonus SET " . implode(', ', $set) . ", date_modified = NOW()
                        WHERE booking_id = '{$booking_id_q}'
                            AND assigned_user_id = '{$user_id_q}'
                            AND deleted = 0"
                    );
                } else {
                    $fields = array_merge([
                        'id'         => 'UUID()',
                        'booking_id' => "'" . $booking_id_q . "'",
                        'created_by' => "'" . $db->quote((string) $current_user->id) . "'",
                    ], $fields);
                    $db->query(
                        "INSERT INTO ec_booking_bonus (" . implode(', ', array_keys($fields)) . ", date_entered, date_modified)
                        VALUES (" . implode(', ', array_values($fields)) . ", NOW(), NOW())"
                    );
                }
                $saved++;
            }
        }

        // Soft-delete rows in the range that the fresh calculation no longer
        // produced (booking dropped out of status 8, KPI reassigned, ...).
        // Only safe when the report covers every user: restricted users get a
        // partial report and would wipe other users' rows here.
        if (!is_admin($current_user) && $current_user->title != 'QuanLy') {
            return ['saved' => $saved, 'removed' => 0];
        }

        $stale_sql = "UPDATE ec_booking_bonus
            SET deleted = 1, date_modified = NOW()
            WHERE deleted = 0
                AND flight_date BETWEEN '{$from_vn_db}' AND '{$to_vn_db}'";
        if (!empty($fresh_keys)) {
            $stale_sql .= " AND CONCAT(booking_id, '|', assigned_user_id) NOT IN (" . implode(', ', $fresh_keys) . ")";
        }
        $stale_res = $db->query($stale_sql);
        $removed = (int) $db->getAffectedRowCount($stale_res);

        return ['saved' => $saved, 'removed' => $removed];
    }
}
