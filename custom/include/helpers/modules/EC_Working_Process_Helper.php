<?php
class EC_Working_Process_Helper {
	/**
	 * KPI columns aggregated per booking in get_kpi_by_bookings.
	 * Value = weight the raw count is multiplied by in the KPI sums.
	 */
	private const BOOKING_KPI_COLUMNS = [
		'called'               => 1,
		'completed'            => 1,
		'paid'                 => 1,
		'recheck'              => 1,
		'support'              => 1,
		'invoice_issued'       => 3,
		'ticket_delivery'      => 1,
		'checkin_journey'      => 1,
		'recall'               => 1,
		'remind'               => 1,
		'check_debt'           => 1,
		'create_repaid'        => 1,
		'process_repaid'       => 1,
		'create_payment'       => 1,
		'create_receipt'       => 1,
		'create_transfer'      => 1,
		'invoice_input_issued' => 1,
	];

	/**
	 * Build the KPI statistics SQL: assembles the filter conditions
	 * (date range, data access) and returns the query for the report type.
	 *
	 * @param string $from_date_value Start date (any format strtotime understands)
	 * @param string $to_date_value   End date
	 * @param bool   $is_admin_view   true: summary report per employee; false: personal report (owner)
	 * @return string
	 */
	public static function gen_kpi_query($from_date_value, $to_date_value, $is_admin_view) {
		global $current_user, $sugar_config;

		// Current user's date format & timezone, needed to parse the input date strings correctly
		$timezone   = $current_user->getPreference('timezone') ?: 'Asia/Ho_Chi_Minh';
		$dateFormat = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');

		try {
			$user_tz = new DateTimeZone($timezone);
		} catch (Exception $e) {
			$user_tz = new DateTimeZone('Asia/Ho_Chi_Minh');
		}
		$utc_tz = new DateTimeZone('UTC');
		$vn_tz  = new DateTimeZone('Asia/Ho_Chi_Minh');

		// $from_date_value / $to_date_value are already in the user's format, just parse them in the user's timezone
		$from_dt = DateTime::createFromFormat($dateFormat . '|', trim((string) $from_date_value), $user_tz) ?: new DateTime('now', $user_tz);
		$to_dt   = DateTime::createFromFormat($dateFormat . '|', trim((string) $to_date_value), $user_tz) ?: new DateTime('now', $user_tz);

        // Start/end of day in the user's timezone, converted to UTC for the DATETIME column: date_entered
		$from_utc_datetime_db = (clone $from_dt)->setTime(0, 0, 0)->setTimezone($utc_tz)->format('Y-m-d H:i:s');
		$to_utc_datetime_db   = (clone $to_dt)->setTime(23, 59, 59)->setTimezone($utc_tz)->format('Y-m-d H:i:s');

		// Date (Y-m-d) in Vietnam time for the DATE column: date_ticket_issue (stored as VN date)
		$from_vn_date_db = (clone $from_dt)->setTime(0, 0, 0)->setTimezone($vn_tz)->format('Y-m-d');
		$to_vn_date_db   = (clone $to_dt)->setTime(23, 59, 59)->setTimezone($vn_tz)->format('Y-m-d');

		// Date range filter conditions
		$sql_search  = " AND b.date_ticket_issue >= '{$from_vn_date_db}' AND b.date_ticket_issue <= '{$to_vn_date_db}' ";
		$sql_search2 = " AND w.date_entered >= '{$from_utc_datetime_db}' AND w.date_entered <= '{$to_utc_datetime_db}' ";

		// Data access: personal report only includes the current user's records
		if (!$is_admin_view) {
			$sql_search  .= " AND b.assigned_user_id = '{$current_user->id}' ";
			$sql_search2 .= " AND w.assigned_user_id = '{$current_user->id}' ";
		}

        /**
         * completed: number of completed bookings
         * paid: paid / collected
         * recheck: recheck booking
         * invoice_issued: output invoice issued * 3
         * ticket_delivery: ticket / food delivery
         * checkin_journey: journey check-in
         * recall: call recall / remind (completed call with recording and description)
         * check_debt: debt check and reconciliation
         * support: customer support (delay, Zalo)
         * create_repaid: create ticket refund voucher
         * create_payment: create payment voucher
         * create_receipt: create receipt voucher (flight time, itinerary, passenger name change)
         * create_transfer: create internal transfer voucher
         * invoice_input_issued: input invoice issued
         */

		if ($is_admin_view) {
			return 
                "SELECT w.assigned_user_id
                    ,u.user_name
                    ,CONCAT(IFNULL(u.last_name,''),IF(u.first_name IS NOT NULL,' ',''),IFNULL(u.first_name,'')) AS full_name
                    ,SUM(IFNULL(w.called,0)) AS called
                    ,SUM(IFNULL(w.completed,0)) AS completed
                    ,SUM(IFNULL(w.paid,0)) AS paid
                    ,SUM(IFNULL(w.recheck,0)) AS recheck
                    ,SUM(IFNULL(w.support,0)) AS support
                    ,SUM(IFNULL(w.invoice_issued,0) * 3) AS invoice_issued
                    ,SUM(IFNULL(w.ticket_delivery,0)) AS ticket_delivery
                    ,SUM(IFNULL(w.checkin_journey,0)) AS checkin_journey
                    ,SUM(IFNULL(w.recall,0)) AS recall
                    ,SUM(IFNULL(w.remind,0)) AS remind
                    ,SUM(IFNULL(w.check_debt,0)) AS check_debt
                    ,SUM(IFNULL(w.create_repaid,0)) AS create_repaid
                    ,SUM(IFNULL(w.process_repaid,0)) AS process_repaid
                    ,SUM(IFNULL(w.create_payment,0)) AS create_payment
                    ,SUM(IFNULL(w.create_receipt,0)) AS create_receipt
                    ,SUM(IFNULL(w.create_transfer,0)) AS create_transfer
                    ,SUM(IFNULL(w.invoice_input_issued,0)) AS invoice_input_issued
                    -- ,SUM(IFNULL(w.manner, 0)) AS manner
                    -- ,SUM(IFNULL(w.effected, 0)) AS effected
                    -- ,SUM(IFNULL(w.awareness, 0)) AS awareness
                    -- ,SUM(IFNULL(w.minus, 0)) AS minus
                    ,SUM(
                        IFNULL(w.called,0)
                        + IFNULL(w.completed,0)
                        + IFNULL(w.paid,0)
                        + IFNULL(w.recheck,0)
                        + IFNULL(w.support,0)
                        + (IFNULL(w.invoice_issued,0) * 3)
                        + IFNULL(w.ticket_delivery,0)
                        + IFNULL(w.checkin_journey,0)
                        + IFNULL(w.recall,0)
                        + IFNULL(w.remind,0)
                        + IFNULL(w.check_debt,0)
                        + IFNULL(w.create_repaid,0)
                        + IFNULL(w.process_repaid,0)
                        + IFNULL(w.create_payment,0)
                        + IFNULL(w.create_receipt,0)
                        + IFNULL(w.create_transfer,0)
                        + IFNULL(w.invoice_input_issued,0)
                        -- + IFNULL(w.manner,0)
                        -- + IFNULL(w.effected,0)
                        -- + IFNULL(w.awareness,0)
                        -- - IFNULL(w.minus, 0)
                        ) AS total_kpi
                FROM ec_working_process w
                    INNER JOIN users u ON w.assigned_user_id = u.id
                        AND u.deleted = 0
                        AND u.is_admin = 0
                        AND u.title <> 'QuanLy'
                        -- AND u.start_working_date IS NOT NULL
                WHERE w.deleted = 0
                    $sql_search2
                GROUP BY w.assigned_user_id
                ORDER BY total_kpi DESC";
		}

		return 
            "SELECT SUM(IFNULL(t.booking_count,0)) AS booking_count
                ,SUM(IFNULL(t.ticket_count,0)) AS ticket_count
                ,SUM(IFNULL(t.total_bonus,0)) AS total_bonus
                ,SUM(IFNULL(t.total_kpi,0)) AS total_kpi
            FROM (
                SELECT DISTINCT COUNT(w.parent_id) AS booking_count
                    ,0 AS ticket_count
                    ,0 AS total_bonus
                    ,0 AS total_kpi
                FROM ec_working_process w
                WHERE w.deleted=0
                    AND w.parent_type='EC_Flight_Bookings' $sql_search2

                UNION
                SELECT 0 AS booking_count
                    ,SUM(IFNULL(d.quantity,0)) AS ticket_count
                    ,0 AS total_bonus
                    ,0 AS total_kpi
                FROM ec_booking_details d
                    LEFT JOIN ec_flight_bookings b ON d.booking_id=b.id AND b.deleted=0
                WHERE d.deleted=0 AND b.booking_status='8' $sql_search

                UNION
                SELECT 0 AS booking_count
                        ,0 AS ticket_count
                        ,SUM(IFNULL(w.bonus,0)) AS total_bonus
                        ,0 AS total_kpi
                FROM ec_working_process w
                WHERE w.deleted=0
                AND w.parent_type='EC_Flight_Bookings' $sql_search2

                UNION
                SELECT 0 AS booking_count
                    ,0 AS ticket_count
                    ,0 AS total_bonus
                    ,SUM(
                        IFNULL(w.called,0)
                    + IFNULL(w.confirmed,0)
                    + IFNULL(w.completed,0)
                    + IFNULL(w.paid,0)
                    + IFNULL(w.recheck,0)
                    + (IFNULL(w.invoice_issued,0) * 3)
                    + IFNULL(w.ticket_delivery,0)
                    + IFNULL(w.checkin_journey,0)
                    + IFNULL(w.recall,0)
                    + IFNULL(w.remind,0)
                    + IFNULL(w.bonus,0)
                    + IFNULL(w.check_debt,0)
                    + IFNULL(w.create_repaid,0)
                    + IFNULL(w.process_repaid,0)
                    + IFNULL(w.create_payment,0)
                    + IFNULL(w.create_transfer,0)
                    + IFNULL(w.invoice_input_issued,0)
                    ) AS total_kpi
                FROM ec_working_process w
                WHERE w.deleted=0 $sql_search2
            ) AS t";
	}

	/**
	 * Get KPI for a list of bookings, independent of any date range.
	 *
	 * Direct KPI = the 'completed' criterion (user who completed the booking);
	 * indirect KPI = every remaining criterion. total_kpi = direct + indirect.
	 *
	 * @param array $booking_ids List of EC_Flight_Bookings ids
	 * @return array [
	 *   'total_kpi'          => Total KPI across all users,
	 *   'total_indirect_kpi' => The total KPI of all criteria except completed,
	 *   'users'              => Map assigned_user_id => that user's total_kpi,
	 *   'bookings'           => Map booking_id => [assigned_user_id => KPI row
	 *                           (called, completed, ..., total_kpi, total_indirect_kpi)]
	 * ]
	 */
	public static function get_kpi_by_bookings(array $booking_ids) {
		$kpi = [
			'bookings' => [],
			'total_kpi' => 0,
			'total_indirect_kpi' => 0,
		];

		if (empty($booking_ids)) {
			return $kpi;
		}

        global $db;

		$quoted = array_map(function ($id) use ($db) {
			return "'" . $db->quote($id) . "'";
		}, $booking_ids);
		$parent_id_cond = count($quoted) == 1
			? "w.parent_id = {$quoted[0]}"
			: "w.parent_id IN (" . implode(',', $quoted) . ")";

		// One list drives both the per-column SUMs and the total_kpi expression
		$sum_selects = [];
		$total_parts = [];
		foreach (self::BOOKING_KPI_COLUMNS as $col => $weight) {
			$expr = "IFNULL(w.{$col},0)" . ($weight != 1 ? " * {$weight}" : '');
			$sum_selects[] = "SUM({$expr}) AS {$col}";
			$total_parts[] = $weight != 1 ? "({$expr})" : $expr;
		}

		$sql =
            "SELECT w.parent_id AS booking_id
                ,w.assigned_user_id
                ," . implode(",", $sum_selects) . "
                ,SUM(
                    " . implode("+ ", $total_parts) . "
                ) AS total_kpi
            FROM ec_working_process w
            WHERE w.deleted = 0
                AND w.parent_type = 'EC_Flight_Bookings'
                AND $parent_id_cond
            GROUP BY w.parent_id, w.assigned_user_id";

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$uid  = $row['assigned_user_id'];
			$bkid = $row['booking_id'];

			$total    = (int) $row['total_kpi'];
			$direct   = (int) $row['completed']; // 'completed' criterion only
			$indirect = $total - $direct;

            // Details
			$row['total_indirect_kpi'] = $indirect;
            unset($row['assigned_user_id']);
            unset($row['booking_id']);
			$kpi['bookings'][$bkid][$uid] = $row;

            // Total
			$kpi['total_kpi'] += $total;
			$kpi['total_indirect_kpi'] += $indirect;
		}

		return $kpi;
	}
}
