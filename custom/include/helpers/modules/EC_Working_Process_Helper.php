<?php
class EC_Working_Process_Helper {
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
			$GLOBALS['log']->error('Unknown user timezone: ' . $timezone);
			$user_tz = new DateTimeZone('Asia/Ho_Chi_Minh');
		}

		// Parse theo định dạng của user (fallback strtotime nếu không khớp), trả về Y-m-d
		$parseUserDate = function ($value) use ($dateFormat, $user_tz) {
			$value = trim((string) $value);
			$dt = DateTime::createFromFormat($dateFormat . '|', $value, $user_tz);
			if (!$dt instanceof DateTime) {
				$ts = strtotime($value);
				$dt = (new DateTime('@' . ($ts !== false ? $ts : time())))->setTimezone($user_tz);
			}
			return $dt->format('Y-m-d');
		};

		$from_db = $parseUserDate($from_date_value);
		$to_db   = $parseUserDate($to_date_value);

		// Điều kiện lọc theo ngày
		$sql_search  = " AND b.date_ticket_issue >= '{$from_db}' AND b.date_ticket_issue <= '{$to_db}' ";
		$sql_search2 = " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) >= '{$from_db}' AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) <= '{$to_db}' ";

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
}
