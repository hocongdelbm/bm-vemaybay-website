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
	 * Sinh câu SQL thống kê KPI: tự dựng điều kiện lọc (ngày, phân quyền)
	 * rồi trả về câu truy vấn theo loại báo cáo.
	 *
	 * @param string $from_date_value Ngày bắt đầu (định dạng bất kỳ strtotime hiểu được)
	 * @param string $to_date_value   Ngày kết thúc
	 * @param bool   $is_admin_view   true: báo cáo tổng hợp theo nhân viên; false: báo cáo cá nhân (owner)
	 * @return string
	 */
	public static function gen_kpi_query($from_date_value, $to_date_value, $is_admin_view) {
		global $current_user, $sugar_config;

		// Định dạng & múi giờ của user hiện tại để hiểu đúng chuỗi ngày nhập vào
		$timezone   = $current_user->getPreference('timezone') ?: 'Asia/Ho_Chi_Minh';
		$dateFormat = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');

		try {
			$user_tz = new DateTimeZone($timezone);
		} catch (Exception $e) {
			$user_tz = new DateTimeZone('Asia/Ho_Chi_Minh');
		}
		$utc_tz = new DateTimeZone('UTC');
		$vn_tz  = new DateTimeZone('Asia/Ho_Chi_Minh');

		// $from_date_value / $to_date_value đã đúng định dạng của user, chỉ cần parse theo múi giờ user
		$from_dt = DateTime::createFromFormat($dateFormat . '|', trim((string) $from_date_value), $user_tz) ?: new DateTime('now', $user_tz);
		$to_dt   = DateTime::createFromFormat($dateFormat . '|', trim((string) $to_date_value), $user_tz) ?: new DateTime('now', $user_tz);

        // Đầu/cuối ngày theo múi giờ user, đổi sang UTC cho cột DATETIME: date_entered
		$from_utc_datetime_db = (clone $from_dt)->setTime(0, 0, 0)->setTimezone($utc_tz)->format('Y-m-d H:i:s');
		$to_utc_datetime_db   = (clone $to_dt)->setTime(23, 59, 59)->setTimezone($utc_tz)->format('Y-m-d H:i:s');

		// Ngày (Y-m-d) theo giờ Việt Nam cho cột DATE: date_ticket_issue (lưu theo ngày VN)
		$from_vn_date_db = (clone $from_dt)->setTime(0, 0, 0)->setTimezone($vn_tz)->format('Y-m-d');
		$to_vn_date_db   = (clone $to_dt)->setTime(23, 59, 59)->setTimezone($vn_tz)->format('Y-m-d');

		// Điều kiện lọc theo ngày
		$sql_search  = " AND b.date_ticket_issue >= '{$from_vn_date_db}' AND b.date_ticket_issue <= '{$to_vn_date_db}' ";
		$sql_search2 = " AND w.date_entered >= '{$from_utc_datetime_db}' AND w.date_entered <= '{$to_utc_datetime_db}' ";

		// Phân quyền dữ liệu: báo cáo cá nhân thì chỉ lấy của user hiện tại
		if (!$is_admin_view) {
			$sql_search  .= " AND b.assigned_user_id = '{$current_user->id}' ";
			$sql_search2 .= " AND w.assigned_user_id = '{$current_user->id}' ";
		}

        /**
         * completed: số lượng booking hoàn tất
         * paid: Đã thanh toán / đã thu
         * recheck: Recheck booking
         * invoice_issued: Xuất hóa đơn đầu ra * 3
         * ticket_delivery: Giao vé / giao thực phẩm
         * checkin_journey: Checkin hành trình
         * Recall: Recall cuộc gọi / Remind (cuộc gọi hoàn tất, có thoại và mô tả)
         * check_debt: Kiểm tra, đối chiếu công nợ
         * support: Hỗ trợ khách hàng (Delay, zalo)
         * create_repaid: Tạo phiếu hoàn vé
         * create_payment: Lập phiếu chi
         * create_receipt: Lập phiếu thu (Đổi giờ bay, hành trình, tên khách)
         * create_transfer : Lập phiếu điều chuyển nội bộ
         * invoice_input_issued: Xuất hóa đơn đầu vào
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
	 * Lấy KPI theo danh sách booking, không phụ thuộc khoảng ngày.
	 *
	 * @param array $booking_ids Danh sách id của EC_Flight_Bookings
	 * @return array [
	 *   'total_kpi' => tổng KPI của tất cả user,
	 *   'users'     => map assigned_user_id => tổng total_kpi của user đó,
	 *   'bookings'  => map booking_id => [assigned_user_id => row KPI (called, completed, ..., total_kpi)]
	 * ]
	 */
	public static function get_kpi_by_bookings(array $booking_ids) {
		global $db;

		$kpi = [
			'total_kpi' => 0,
			'users'     => [],
			'bookings'  => [],
		];
		if (empty($booking_ids)) {
			return $kpi;
		}

		$quoted = array_map(function ($id) use ($db) {
			return "'" . $db->quote($id) . "'";
		}, $booking_ids);
		$in_clause = implode(',', $quoted);

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
                ," . implode("
                ,", $sum_selects) . "
                ,SUM(
                    " . implode("
                    + ", $total_parts) . "
                ) AS total_kpi
            FROM ec_working_process w
            WHERE w.deleted = 0
                AND w.parent_type = 'EC_Flight_Bookings'
                AND w.parent_id IN ($in_clause)
            GROUP BY w.parent_id, w.assigned_user_id";

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$uid = $row['assigned_user_id'];

			$kpi['bookings'][$row['booking_id']][$uid] = $row;

			if (!isset($kpi['users'][$uid])) {
				$kpi['users'][$uid] = 0;
			}
			$kpi['users'][$uid] += (int)$row['total_kpi'];
			$kpi['total_kpi']   += (int)$row['total_kpi'];
		}

		return $kpi;
	}
}
