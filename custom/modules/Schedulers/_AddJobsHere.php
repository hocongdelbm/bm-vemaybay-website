<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$job_strings[] = 'TuDongTaoBang';  // tu dong tao bang moi chi tiet tai khoan
$job_strings[] = 'KetChuyenTienMatSCK'; // ket chuyen tien mat so du cuoi ky vao dau moi nam
$job_strings[] = 'KetChuyenTienGuiNganHangSCK'; // ket chuyen tien gui ngan hang so du cuoi ky vao dau moi nam
$job_strings[] = 'KetChuyenCongNoPhaiThu'; // kết chuyển công nợ phải thu vào đầu mỗi năm
$job_strings[] = 'KetChuyenCongNoPhaiTra'; // kết chuyển công nợ phải trả vào đầu mỗi năm


$job_strings[] = 'createMonthSalary'; // Đầu mỗi tháng tạo 1 bảng lương
$job_strings[] = 'updateSales'; // cập nhật thưởng doanh số trong bảng lương
$job_strings[] = 'updateEfforts'; // cập nhật nỗ lực trong bảng lương
$job_strings[] = 'lockSalaryAtEndMonth'; // khoá bảng lương vào cuối mỗi tháng 
$job_strings[] = 'updateWorkingDays'; // cập nhật ngày công trong bảng lương
$job_strings[] = 'updateMissingEfforts'; // cập nhật nỗ lực thật sự của tháng nếu bảng lương khoá trước ngày cuối tháng
$job_strings[] = 'updateOnlineReport'; // cập nhật ds online mỗi ngày

$job_strings[] = 'LoopCheckIfBookingOver24h'; // kiem tra booking co qua 24h
// $job_strings[] = 'checkOnlineUser'; // Kiểm tra xem booking giao đã được xử lý, để biết user còn online hay không
$job_strings[] = 'checkBookingHandle'; // Kiểm tra xem booking đã giao được xử lý hay chưa
$job_strings[] = 'checkStatusOnlineUser'; // Kiểm tra user còn online hay không
$job_strings[] = 'reAssignBooking'; // lặp lại việc giao booking nếu gặp booking chưa được giao
$job_strings[] = 'calculateCashFlow'; // Tính toán dòng tiền trong 3 ngày trước

$job_strings[] = 'checkExpirationDateVoucher'; // Kiểm tra HSD của voucher

$job_strings[] = 'saveReportWeekly'; // Lưu kết quả doanh số cuối ngày vào table ec_report_weekly

function saveReportWeekly()
{
	global $db;
	$date_report = date('Y-m-d', strtotime('-1 day +7 hours'));
	
	$sql_exist = '
		SELECT IF(COUNT(id) > 0, 1, 0) as count
		FROM ec_report_weekly
		WHERE from_date = "' . $date_report . '" 
			AND to_date = "' . $date_report . '"
			AND type = "BOOKING"
			AND deleted = 0';
	$count_rows = $db->getOne($sql_exist);
	
	// Bước 2: Tạo dữ liệu trong ec_report_weekly theo from_date - to_date
	$sql_select = '
		SELECT last_name, user_name, user_id,
			SUM(bk_created) AS bk_created,
			SUM(bk_called) AS bk_called,
			SUM(bk_paying) AS bk_paying,
			SUM(bk_confirmed) AS bk_confirmed,
			SUM(bk_printed) AS bk_printed,
			SUM(bk_completed) AS bk_completed,
			SUM(bk_cancelled) AS bk_cancelled,
			SUM(total) AS total,
			SUM(total_sales) AS total_sales,
			SUM(total_ticket) AS total_ticket,
			0 AS advertisement_cost,
			"' . $date_report . '" AS from_date,
			"' . $date_report . '" AS to_date
		FROM (
			SELECT
				u.last_name, u.user_name, u.id AS user_id,
				COUNT(IF(bk.booking_status = 1, bk.id, NULL)) AS bk_created,
				COUNT(IF(bk.booking_status = 6, bk.id, NULL)) AS bk_called,
				COUNT(IF(bk.booking_status = 2, bk.id, NULL)) AS bk_paying,
				COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
				COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
				COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
				COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
				COUNT(bk.id) AS total,
				SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_amount - bk.total_bought_amount, 0)) AS total_sales,
				SUM(IF(bk.booking_status IN (3, 7, 8), (SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
				DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
			FROM ec_flight_bookings bk
				LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
			WHERE u.title = "Bot" 
				AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= "' . $date_report . '"
				AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= "' . $date_report . ' 23:59:59"
				AND bk.deleted = 0 
			GROUP BY bk.id
			
			UNION
			SELECT 
				IF(u.title = "Bot", u.last_name, "Chưa xác định") AS last_name,
				IF(u.title = "Bot", u.user_name, "") AS user_name,
				IF(u.title = "Bot", u.id, "BK_UNK") AS user_id,
				0 AS bk_created,
				0 AS bk_called,
				0 AS bk_paying,
				0 AS bk_confirmed,
				0 AS bk_printed,
				0 AS bk_completed,
				0 AS bk_cancelled,
				0 AS total,
				- (
					SUM(IFNULL(bk_psg.luggage_purchase, 0)) + SUM(IFNULL(bk_psg.luggage_purchase_inbound, 0))
				) AS total_sales,
				0 AS total_ticket,
				"" AS bk_date_entered
			FROM ec_booking_passengers bk_psg
				INNER JOIN ec_flight_bookings bk ON bk.id = bk_psg.booking_id
					AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= "' . $date_report . '"
					AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= "' . $date_report . ' 23:59:59"
					AND bk.booking_status IN (3, 7, 8)
				INNER JOIN users u ON bk.created_by = u.id 
			WHERE (bk_psg.add_type IS NULL OR bk_psg.add_type = "") AND bk_psg.deleted = 0
			GROUP BY user_id
			
			UNION
			SELECT
				"Booking chưa xác định" AS last_name, "" AS user_name, "BK_UNK" AS user_id,
				COUNT(IF(bk.booking_status = 1, bk.id, NULL)) AS bk_created,
				COUNT(IF(bk.booking_status = 6, bk.id, NULL)) AS bk_called,
				COUNT(IF(bk.booking_status = 2, bk.id, NULL)) AS bk_paying,
				COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
				COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
				COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
				COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
				COUNT(bk.id) AS total,
				SUM(IF(bk.booking_status = 8, bk.total_amount - bk.total_bought_amount - bk.luggage_fee, 0)) AS total_sales,
				SUM(IF(bk.booking_status IN (3, 7, 8), (SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
				DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
			FROM ec_flight_bookings bk
				LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
			WHERE
				DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= "' . $date_report . '"
				AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= "' . $date_report . ' 23:59:59"
				AND (
					(u.title = "Bot" AND LOWER(bk.contact_name) IN ("tim chuyen bay", "callnow", "call now"))
					OR u.title <> "Bot"
				)
				AND bk.deleted = 0 
			GROUP BY bk.id

		) AS tmp
		GROUP BY user_id
		ORDER BY total_sales DESC
	';
	$results = $db->query($sql_select);

	if ((int)$count_rows == 0) {
		// CREATE
		while ($row = $db->fetchByAssoc($results)) {
			$rp = new EC_Report_Weekly();
			$rp->user_id = $row['user_id'];
			$rp->last_name = $row['last_name'];
			$rp->user_name = $row['user_name'];
			$rp->bk_created = $row['bk_created'];
			$rp->bk_called = $row['bk_called'];
			$rp->bk_paying = $row['bk_paying'];
			$rp->bk_confirmed = $row['bk_confirmed'];
			$rp->bk_printed = $row['bk_printed'];
			$rp->bk_completed = $row['bk_completed'];
			$rp->bk_cancelled = $row['bk_cancelled'];
			$rp->total_qty = $row['total'];
			$rp->total_sales = $row['total_sales'];
			$rp->total_ticket = $row['total_ticket'];
			$rp->type = 'BOOKING';
			$rp->advertisement_cost = 0;
			$rp->from_date = $today;
			$rp->to_date = $today;
			$rp->report_date = date('Y-m-d');
			$rp->save();
			// sendTestTelegram("create EC_Report_Weekly: " . $date_report);
		}
	} else {
		// UPDATE
		while ($row = $db->fetchByAssoc($results)) {
			// Bước 3: Kiểm tra xem user_id đã tồn tại trong khoảng thời gian chưa
			$check_sql = '
				SELECT COUNT(*)
				FROM ec_report_weekly
				WHERE user_id = "' . $row['user_id'] . '" 
					AND from_date = "' . $date_report . '" 
					AND to_date = "' . $date_report . '"
					AND type = "BOOKING"
					AND deleted = 0';
			$user_exists = $db->getOne($check_sql);

			if ((int)$user_exists > 0) {
				$update_sql = '
					UPDATE ec_report_weekly
					SET 
						bk_created = ' . (int)$row['bk_created'] . ',
						bk_called = ' . (int)$row['bk_called'] . ',
						bk_paying = ' . (int)$row['bk_paying'] . ',
						bk_confirmed = ' . (int)$row['bk_confirmed'] . ',
						bk_printed = ' . (int)$row['bk_printed'] . ',
						bk_completed = ' . (int)$row['bk_completed'] . ',
						bk_cancelled = ' . (int)$row['bk_cancelled'] . ',
						total_qty = ' . $row['total'] . ',
						total_sales = ' . $row['total_sales'] . ',
						total_ticket = ' . $row['total_ticket'] . '
					WHERE user_id = "' . $row['user_id'] . '" 
						AND from_date = "' . $date_report . '" 
						AND to_date = "' . $date_report . '"
						AND type = "BOOKING"
						AND deleted = 0
				';

				$result_update = $db->query($update_sql);

				// if($result_update){
				// 	sendTestTelegram("update EC_Report_Weekly : " . $date_report);
				// } else {
				// 	sendTestTelegram("update failed: " . $date_report);
				// }

			} else {
				$rp = new EC_Report_Weekly();
				$rp->user_id = $row['user_id'];
				$rp->last_name = $row['last_name'];
				$rp->user_name = $row['user_name'];
				$rp->bk_created = (int)$row['bk_created'];
				$rp->bk_called = (int)$row['bk_called'];
				$rp->bk_paying = (int)$row['bk_paying'];
				$rp->bk_confirmed = (int)$row['bk_confirmed'];
				$rp->bk_printed = (int)$row['bk_printed'];
				$rp->bk_completed = (int)$row['bk_completed'];
				$rp->bk_cancelled = (int)$row['bk_cancelled'];
				$rp->total_qty = $row['total'];
				$rp->total_sales = $row['total_sales'];
				$rp->total_ticket = $row['total_ticket'];
				$rp->type = 'BOOKING';
				$rp->advertisement_cost = 0;
				$rp->from_date = $date_report;
				$rp->to_date = $date_report;
				$rp->report_date = $today;
				$rp->save();
				// sendTestTelegram("count_rows > 0. Create successed: " . $date_report);
			}
		}
	}

	return true;
}

function checkExpirationDateVoucher()
{
	global $db;

	$today = date('Y-m-d', strtotime(date('Y-m-d H:i:s') . ' +7 hours'));
	$sql_check = '
	SELECT
		id,
		status,
		validate_to_date
		FROM ec_vouchers
		WHERE status IN ("new", "active") AND validate_to_date < "' . $today . '" AND deleted = 0
	';

	$res = $db->query($sql_check);
	while ($row = $db->fetchByAssoc($res)) {
		$sql_update = '
		UPDATE ec_vouchers
		SET status = "expired"
		WHERE id = "' . $row['id'] . '" AND deleted = 0';
		$db->query($sql_update);
	}

	return true;
}

function updateOnlineReport()
{
	global $db;

	// Cập nhật tất cả các user ngoại trừ các admin phía dưới
	$arr_id_admin = array(
		'1', //ducpham
		'168889bb-54c2-59c7-8b3f-649102530d3c', //hungnh
		'9eb0f65f-a9f6-65bb-1985-637ca8511491', //trinh
		'4f4d7a13-4171-9b7d-251c-64dd8f9885e4', //panda
		'622ecf27-f729-7187-7e27-6520e0dab882', //quangnd
		'e4a1676e-536d-b5d2-75c2-6502656a118b', //cuong
		'493ad5e5-ffea-a84f-96d7-6577fed623d6', //booker
		'2751ebe5-6cdc-acfc-7d70-659d08368c2e', //ancao

		'c57196c6-e211-9856-43d5-6695498f39ae', //tiennguyen
	);
	$notInCondition = "'" . implode("', '", $arr_id_admin) . "'";

	$date_check = date('Y-m-d', strtotime(date('Y-m-d H:i:s') . ' +7 hours'));
	$sql = '
		SELECT id, first_name, last_name, title,
			IFNULL(init_exp_mark, 0) AS init_exp_mark,
			IFNULL(init_exp_mark, 0) AS kpi,
			(
				SELECT date_start
				FROM ec_workhistory
				WHERE deleted = 0 AND assigned_user_id = users.id
				ORDER BY date_start DESC
				LIMIT 1
			) AS latest_date_start,
			(
				SELECT status
				FROM ec_workhistory
				WHERE deleted = 0 AND assigned_user_id = users.id
				ORDER BY date_start DESC
				LIMIT 1
			) AS work_stt,
			(
				SELECT date_start
				FROM ec_workhistory
				WHERE deleted = 0 AND assigned_user_id = users.id
				ORDER BY date_start
				LIMIT 1
			) AS date_begin_work
		FROM users 
		-- WHERE title <> "KeToan" AND is_admin = 0 AND deleted = 0
		WHERE id NOT IN (' . $notInCondition . ') AND deleted = 0
		GROUP BY id
		HAVING (work_stt = "Active" OR work_stt = "Online")
			AND latest_date_start <= "' . $date_check . '"
		ORDER BY init_exp_mark DESC, date_begin_work
	';

	$res = $db->query($sql);
	$i 	= 1;

	while ($row = $db->fetchByAssoc($res)) {
		$sql_exist = '
			SELECT IF(COUNT(id) > 0, 1, 0)
			FROM ec_online_report
			WHERE deleted = 0
				AND assigned_user_id = "' . $row['id'] . '"
				AND DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . $date_check . '"
		';

		$is_exist = $db->getOne($sql_exist);


		// Check log
		$GLOBALS['log']->debug($sql_exist);
		$sql_checklog = '
			SELECT assigned_user_id,
				IF(DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . $date_check . '", 1, 0) AS is_exist,
				DATE_ADD(date_entered, INTERVAL 7 HOUR) AS date
			FROM ec_online_report
			WHERE deleted = 0 AND assigned_user_id = "' . $row['id'] . '"
			ORDER BY date_entered
		';
		$res_checklog = $db->query($sql_checklog);
		$json = array();
		while ($row_checklog = $db->fetchByAssoc($res_checklog)) {
			if ($row_checklog['is_exist'] == 0) $json[$row_checklog['assigned_user_id']] = $row_checklog['date'];
		}
		// End check log

		if ($is_exist == 0 || !$is_exist) {
			$online = new EC_Online_Report;
			$online->name = trim($row['last_name']) . ' ' . trim($row['first_name']);
			$online->assigned_user_id = $row['id'];
			$online->status = 0;
			if ($row['title'] != 'Leader' && $row['title'] != 'Booker') {
				$online->ranking = 0;
			} else {
				$online->ranking = $i;
			}
			$online->title 	= $row['title'];
			$online->round 	= 0;
			$online->kpi 	= $row['init_exp_mark'];
			$online->save();
		}

		if ($row['title'] != 'QuanLy') {
			$i++;
		}
	}
	return true;
}

function TuDongTaoBang()
{
	$thang = date('n');
	$nam   = date('Y');

	if ($thang == 1) {
		$GLOBALS['log']->info('----->Tu dong tao bang');
		// $db = DBManagerFactory::TuDongTaoBang();
		$db = DBManagerFactory::getInstance();

		$create = "
			CREATE TABLE ec_chitiettaikhoan" . $nam . " (  
				id char( 36 ) NOT  NULL ,
				name varchar( 255 ) NOT  NULL ,
				date_entered datetime default NULL ,
				date_modified datetime default NULL ,
				modified_user_id char( 36 ) default NULL ,
				created_by char( 36 ) default NULL ,
				description text,
				deleted tinyint( 1 ) default '0',
				assigned_user_id char( 36 ) default NULL ,
				sotaikhoan varchar( 10 ) NOT  NULL ,
				dunodau decimal( 26, 6 ) default NULL ,
				ducodau decimal( 26, 6 ) default NULL ,
				parent_type varchar( 100 ) default NULL ,
				parent_id char( 36 ) default NULL ,
				company_id char( 36 ) default NULL ,
				location_id char( 36 ) default NULL ,
				PRIMARY  KEY (  id  )  
			) ENGINE  =  MyISAM DEFAULT CHARSET = utf8 ";
		$db->query($create);

		// $insert = "INSERT INTO ec_chitiettaikhoan".$nam." SELECT * FROM ec_chitiettaikhoan ";
		// $db->query($insert);
		// $truncate = "TRUNCATE TABLE ec_chitiettaikhoan ";
		// $db->query($truncate);

		$GLOBALS['log']->info("----->End Tu dong tao bang");
		return true;
	} else return false;
}

function KetChuyenTienMatSCK()
{
	$GLOBALS['log']->info('----->Ket chuyen tien mat so du cuoi ky vao dau moi nam');
	$db = DBManagerFactory::getInstance();

	$report_year = date('Y');
	$today = ($report_year - 1) . '-01-01';
	$to_date = ($report_year - 1) . '-12-31';

	$sql_search = " AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-01-01', strtotime($from_date)) . "' ";
	$sql_search .= " AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d 23:59:59', strtotime($to_date)) . "' ";

	$sql = "
		SELECT 
			(SUM(IFNULL(tmp.thutien,0)) - SUM(IFNULL(tmp.chitien,0))) AS sotien,
			tmp.diadiem_id,
			tmp.diadiem 
		FROM (
			-- OPENING AMOUNT
			SELECT
				(IFNULL(dunodau, 0) - IFNULL(ducodau, 0)) AS thutien,
			  	0 AS chitien,
			  	p.location_id AS diadiem_id,
			  	p.name AS diadiem,
			  	p.id
			FROM ec_chitiettaikhoan" . ($report_year - 1) . " p
			WHERE p.deleted = 0 AND SUBSTR(TRIM(p.sotaikhoan), 1, 4) = '1111'

			-- RECEIPT VOUCHER
			UNION
			SELECT 
				IFNULL(p.amount_converted, 0) AS thutien,
			  	0 AS chitien,
			  	p.com_location_id AS diadiem_id,
			  	(
			  		SELECT name FROM ec_location 
			  		WHERE id = p.com_location_id
			  		LIMIT 1
			  	) AS diadiem,
			  	p.id
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
				AND p.receipt_type = 'cash'
				AND p.rv_status = '1'
				AND p.com_location_id IS NOT NULL
				" . $sql_search . "

			-- PAYMENT VOUCHER
			UNION
			SELECT 
				0 AS thutien,
			  	IFNULL(p.amount, 0) AS chitien,
			  	p.com_location_id AS diadiem_id,
			  	(
			  		SELECT name FROM ec_location 
			  		WHERE id = p.com_location_id
			  		LIMIT 1
			  	) AS diadiem,
			  	p.id
			FROM ec_payment_voucher p
			WHERE p.deleted = 0
				AND p.hinhthucchi = 'cash'
				AND p.pv_status = '3'
				AND p.com_location_id IS NOT NULL
				" . $sql_search . "

			-- FROM TRANSFER VOUCHER
			UNION
			SELECT
				0 AS thutien,
			  	IFNULL(p.sotien, 0) AS chitien,
			  	p.tudiadiem_id AS diadiem_id,
			  	(
			  		SELECT name FROM ec_location 
			  		WHERE id = p.tudiadiem_id
			  		LIMIT 1
			  	) AS diadiem,
			  	p.id
			FROM ec_chuyentiennoibo p
			WHERE p.deleted = 0
				AND p.ghiso = 1
				AND p.tutienmat = 1
				AND p.tudiadiem_id IS NOT NULL
				" . str_replace('p.com_location_id', 'p.tudiadiem_id', $sql_search) . "

			-- TO TRANSFER VOUCHER
			UNION
			SELECT
				IFNULL(p.sotien, 0) AS thutien,
			  	0 AS chitien,
			  	p.dendiadiem_id AS diadiem_id,
			  	(
			  		SELECT name FROM ec_location 
			  		WHERE id = p.dendiadiem_id
			  		LIMIT 1
			  	) AS diadiem,
			  	p.id
			FROM ec_chuyentiennoibo p
			WHERE p.deleted = 0
				AND p.ghiso = 1
				AND p.dentienmat = 1 
				AND p.dendiadiem_id IS NOT NULL
				" . str_replace('p.com_location_id', 'p.dendiadiem_id', $sql_search) . "
		) AS tmp
		GROUP BY tmp.diadiem_id";

	$res = $db->query($sql);
	// $soton = 0;
	// $soton += getTheBeginningOfPeriod('111', $thang, $nam);
	while ($row = $db->fetchByAssoc($res)) {
		if ($row['sotien'] < 0) {
			$no = 0;
			$co = $row['sotien'];
		} else {
			$no = $row['sotien'];
			$co = 0;
		}

		// Xoá số đầu kỳ đang có cập nhật lại
		$sql_del = '
			UPDATE ec_chitiettaikhoan' . $report_year . ' 
			SET deleted = 1
			WHERE deleted = 0 
			AND parent_id = "' . $row['diadiem_id'] . '"';
		$db->query($sql_del);

		$sql = 'INSERT INTO ec_chitiettaikhoan' . $report_year . '
				VALUES(
					uuid()
				  , "' . $row['diadiem'] . '"
				  , "' . date('Y-m-d H:i:s') . '"
				  , "' . date('Y-m-d H:i:s') . '"
				  , "' . $GLOBALS['current_user']->id . '"
				  , "' . $GLOBALS['current_user']->id . '"
				  , NULL
				  , 0
				  , "' . $GLOBALS['current_user']->id . '"
				  , "1111"
				  , ' . $no . '
				  , ' . $co . '
				  , "EC_Bank_Account"
				  , NULL
				  , NULL
				  , "' . $row['diadiem_id'] . '"
				)';
		$db->query($sql);
	} // end while

	$GLOBALS['log']->info("----->End Ket chuyen tien mat so du cuoi ky vao dau moi nam");

	return true;
}

function KetChuyenTienGuiNganHangSCK()
{
	$GLOBALS['log']->info('----->Ket chuyen tien gui ngan hang so du cuoi ky vao dau moi nam');
	// $db = DBManagerFactory::getInstance();
	global $db;

	// kết chuyển số đầu kỳ năm mới chính là bảng dòng tiền của năm cũ
	$report_year = date('Y');
	$from_date = ($report_year - 1) . '-01-01';
	$to_date = ($report_year - 1) . '-12-31';

	$sql_search = " AND DATE(p.ngayhachtoan) >= '" . date('Y-01-01', strtotime($from_date)) . "' ";
	$sql_search .= " AND DATE(p.ngayhachtoan) <= '" . date('Y-m-d', strtotime($to_date)) . "' ";

	// lấy thông tin các tài khoản ngân hàng
	// $location_np = $this->getLocationByDep('8df43570-09de-d2b3-b2fd-506eca7522f7');
	// $location_tp = $this->getLocationByDep('48840c01-3a4f-c430-f703-56f32c7cd8a4');
	// $sql_search .= " AND p.com_location_id IN ('".implode("','", $location_np).'\',\''.implode("','", $location_tp)."') ";
	$sql_ba = "
		SELECT 
			SUM(IFNULL(tmp.thutien,0))
			- SUM(IFNULL(tmp.chitien,0)) AS sotien
		  , tmp.tknganhang_id
		  , tmp.tknganhang
		  , tmp.sotaikhoan 
		FROM (
			-- OPENING AMOUNT
			SELECT p.id
				  ,(
				  	  IFNULL(p.dunodau,0)
				  	- IFNULL(p.ducodau,0)
				  ) AS thutien
				  ,0 AS chitien
				  ,p.parent_id AS tknganhang_id
				  ,(SELECT t.name FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.parent_id LIMIT 1) AS tknganhang
				  ,(SELECT t.account_number FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.parent_id LIMIT 1) AS sotaikhoan
				  ,(SELECT t.unfollow FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.parent_id LIMIT 1) AS ngungtheodoi
				  ,(SELECT t.assigned_user_id FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.parent_id LIMIT 1) AS assigned_user_id
			FROM ec_chitiettaikhoan" . ($report_year - 1) . " p
			WHERE p.deleted=0
			AND p.parent_type IN ('EC_TaiKhoanNganHang', 'EC_Bank_Account') 
			AND p.parent_id IS NOT NULL
			-- RECEIPT VOUCHER
			UNION 
			SELECT p.id
				  ,p.amount_converted AS thutien
				  ,0 AS chitien
				  ,p.tknganhang_id
				  ,(SELECT t.name FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS tknganhang
				  ,(SELECT t.account_number FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS sotaikhoan
				  ,(SELECT t.unfollow FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS ngungtheodoi
				  ,(SELECT t.assigned_user_id FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS assigned_user_id
			FROM ec_receipt_voucher p
			WHERE p.deleted=0 
			AND p.receipt_type='credit_transfer' 
			AND p.amount IS NOT NULL 
			AND p.rv_status='1' 
			AND p.is_margin=0 " . $sql_search . "
			
			-- PAYMENT VOUCHER
			UNION
			SELECT p.id
				  ,0 AS thutien
				  ,p.amount AS chitien
				  ,p.tknganhang_id
				  ,(SELECT t.name FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS tknganhang
				  ,(SELECT t.account_number FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS sotaikhoan
				  ,(SELECT t.unfollow FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS ngungtheodoi
				  ,(SELECT t.assigned_user_id FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS assigned_user_id
			FROM ec_payment_voucher p
			WHERE p.deleted=0 
			AND p.hinhthucchi='credit_transfer' 
			AND p.amount IS NOT NULL 
			AND p.pv_status='3' " . $sql_search . "
			
			-- TRANSFER FROM
			UNION
			SELECT p.id
				  ,0 AS thutien
				  ,IFNULL(p.sotien,0) AS chitien
				  ,p.tutknganhang_id AS tknganhang_id
				  ,(SELECT t.name FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tutknganhang_id LIMIT 1) AS tknganhang
				  ,(SELECT t.account_number FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tutknganhang_id LIMIT 1) AS sotaikhoan
				  ,(SELECT t.unfollow FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tutknganhang_id LIMIT 1) AS ngungtheodoi
				  ,(SELECT t.assigned_user_id FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tutknganhang_id LIMIT 1) AS assigned_user_id
			FROM ec_chuyentiennoibo p
			WHERE p.deleted=0 
			AND p.ghiso=1 
			AND p.tutienmat=0 " . $sql_search . "
			
			-- TRANSFER TO
			UNION
			SELECT p.id
				  ,IFNULL(p.sotien,0) AS thutien
				  ,0 AS chitien
				  ,p.dentknganhang_id AS tknganhang_id
				  ,(SELECT t.name FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.dentknganhang_id LIMIT 1) AS tknganhang
				  ,(SELECT t.account_number FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.dentknganhang_id LIMIT 1) AS sotaikhoan
				  ,(SELECT t.unfollow FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.dentknganhang_id LIMIT 1) AS ngungtheodoi
				  ,(SELECT t.assigned_user_id FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.dentknganhang_id LIMIT 1) AS assigned_user_id
			FROM ec_chuyentiennoibo p
			WHERE p.deleted=0 
			AND p.ghiso=1 
			AND p.dentienmat=0 " . $sql_search . "
				
		) AS tmp
		LEFT JOIN users u ON tmp.assigned_user_id=u.id AND u.deleted=0
		WHERE tmp.ngungtheodoi=0
		GROUP BY tmp.tknganhang_id ";

	$res_ba = $db->query($sql_ba);
	while ($row_ba = $db->fetchByAssoc($res_ba)) {
		if ($row_ba['sotien'] < 0) {
			$no = 0;
			$co = $row_ba['sotien'];
		} else {
			$no = $row_ba['sotien'];
			$co = 0;
		}

		// xoá số đầu kỳ đang có cập nhật lại
		$sql_del = '
			UPDATE ec_chitiettaikhoan' . $report_year . ' 
			SET deleted = 1
			WHERE deleted = 0 
			AND parent_id = "' . $row_ba['tknganhang_id'] . '"';
		$db->query($sql_del);

		$sql = 'INSERT INTO ec_chitiettaikhoan' . $report_year . '
				VALUES(
					uuid()
				  , "' . $row_ba['tknganhang'] . '"
				  , "' . date('Y-m-d H:i:s') . '"
				  , "' . date('Y-m-d H:i:s') . '"
				  , "' . $GLOBALS['current_user']->id . '"
				  , "' . $GLOBALS['current_user']->id . '"
				  , NULL
				  , 0
				  , "' . $GLOBALS['current_user']->id . '"
				  , "1121"
				  , ' . $no . '
				  , ' . $co . '
				  , "EC_Bank_Account"
				  , "' . $row_ba['tknganhang_id'] . '"
				  , NULL
				  , NULL
				)';
		$db->query($sql);
	}

	$GLOBALS['log']->info("----->End Ket chuyen tien gui ngan hang so du cuoi ky vao dau moi nam");
	return true;
}

function KetChuyenCongNoPhaiThu()
{
	$GLOBALS['log']->info('----->Ket chuyen cong no phai thu cuoi ky vao dau moi nam');
	$db = DBManagerFactory::getInstance();

	$report_year = date('Y');
	$from_date = ($report_year - 1) . '-01-01';
	$to_date = ($report_year - 1) . '-12-31';

	$sql_search = "
		AND p.date_ticket_issue >= '" . date('Y-m-d', strtotime($from_date)) . "'
		AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($to_date)) . "'";

	$sql_search2 = "
		AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-m-d', strtotime($from_date)) . "'
		AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($to_date)) . "'";

	$sql = "
		SELECT 
			SUM(IFNULL(tmp.debt_amount, 0)) - SUM(IFNULL(tmp.pay_amount, 0)) AS sotien
		  , a.id AS agent_id
		  , a.name AS agent_name
		FROM (
			-- OPENING AMOUNT
			SELECT p.id
				  ,p.parent_id AS agent_id
				  ,SUM(IFNULL(p.dunodau, 0) - IFNULL(p.ducodau, 0)) AS debt_amount
				  ,0 AS pay_amount
			FROM ec_chitiettaikhoan" . ($report_year - 1) . " p
			WHERE p.deleted = 0
			AND p.sotaikhoan = '131'
			AND p.parent_type = 'Accounts'
			GROUP BY p.parent_id

			-- BOOKING
			UNION
			SELECT p.id
				  ,p.agent_id
				  ,SUM(IFNULL(p.total_amount, 0)) AS debt_amount
				  ,0 AS pay_amount
			FROM ec_flight_bookings p
			WHERE p.deleted = 0
			AND p.booking_status IN ('7', '8')
			AND p.is_agent = 1
			" . $sql_search . "
			
			GROUP BY p.id

			-- PAYMENT VOUCHER
			UNION
			SELECT p.id
				  ,p.supplier_id AS agent_id
				  ,SUM(IFNULL(p.amount, 0)) AS debt_amount
				  ,0 AS pay_amount
			FROM ec_payment_voucher p
			LEFT JOIN ec_payment_types pt ON p.ec_payment_types_id_c = pt.id AND pt.deleted = 0
			WHERE p.deleted = 0
			AND p.pv_status = '3'
			AND pt.is_receipt_debt = 1
			" . $sql_search2 . "
			GROUP BY p.id

			-- RECEIPT VOUCHER
			UNION
			SELECT p.id
				  ,p.account_id_c AS agent_id
				  ,0 AS debt_amount
				  ,SUM(IFNULL(p.amount_converted, 0)) AS pay_amount
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
			AND p.rv_status = '1'
			AND p.account_id_c IS NOT NULL
			" . $sql_search2 . "
			GROUP BY p.id
		) AS tmp
		LEFT JOIN accounts a ON tmp.agent_id = a.id 
		AND a.deleted = 0
		WHERE a.is_stop_tracking = 0
		AND a.account_type IS NOT NULL
		AND a.account_type <> 'Supplier'
		GROUP BY tmp.agent_id
		HAVING sotien <> 0";
	$res = $db->query($sql);
	while ($row = $db->fetchByAssoc($res)) {
		if ($row['sotien'] < 0) {
			$no = 0;
			$co = abs($row['sotien']);
		} else {
			$no = abs($row['sotien']);
			$co = 0;
		}

		// xoá số đầu kỳ đang có cập nhật lại
		$sql_del = '
			UPDATE ec_chitiettaikhoan' . $report_year . ' 
			SET deleted = 1
			WHERE deleted = 0 
			AND parent_id = "' . $row['agent_id'] . '"';
		$db->query($sql_del);

		$sql = 'INSERT INTO ec_chitiettaikhoan' . $report_year . '
				VALUES(
					uuid()
				  , "' . $row['agent_name'] . '"
				  , "' . date('Y-m-d H:i:s') . '"
				  , "' . date('Y-m-d H:i:s') . '"
				  , "' . $GLOBALS['current_user']->id . '"
				  , "' . $GLOBALS['current_user']->id . '"
				  , NULL
				  , 0
				  , "' . $GLOBALS['current_user']->id . '"
				  , "131"
				  , ' . $no . '
				  , ' . $co . '
				  , "Accounts"
				  , "' . $row['agent_id'] . '"
				  , NULL
				  , NULL
				)';
		$db->query($sql);
	}

	$GLOBALS['log']->info("----->End Ket chuyen cong no phai thu cuoi ky vao dau moi nam");
	return true;
}

function KetChuyenCongNoPhaiTra()
{
	$GLOBALS['log']->info('----->Ket chuyen cong no phai tra cuoi ky vao dau moi nam');
	$db = DBManagerFactory::getInstance();

	$report_year 	= date('Y');
	$from_date 	= ($report_year - 1) . '-01-01';
	$to_date 		= ($report_year - 1) . '-12-31';

	$sql_search = "
		AND p.date_ticket_issue >= '" . date('Y-m-d', strtotime($from_date)) . "'
		AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($to_date)) . "'";

	$sql_search2 = "
		AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-m-d', strtotime($from_date)) . "'
		AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($to_date)) . "'";

	$sql = "
		SELECT 
			SUM(IFNULL(tmp.debt_amount, 0)) - SUM(IFNULL(tmp.pay_amount, 0)) AS sotien
		  , a.id AS supplier_id
		  , a.name AS supplier
		FROM (
			-- START TERM
			SELECT 
				SUM(IFNULL(p.dunodau, 0) - IFNULL(p.ducodau, 0)) AS debt_amount
			  , 0 AS pay_amount
			  , p.parent_id AS supplier_id 
			  , p.id
			FROM ec_chitiettaikhoan" . ($report_year - 1) . " p
			WHERE p.deleted = 0
			AND p.parent_type = 'Accounts'
			AND p.sotaikhoan IN ('144','331')
			AND p.parent_id IS NOT NULL
			GROUP BY p.parent_id

			-- BOOKING DETAILS
			UNION
			SELECT 
				SUM(IFNULL(d.total_bought_price, 0)) AS thutien
			  , 0 AS pay_amount
			  , d.supplier_id
			  , d.id
			FROM ec_booking_details d
			LEFT JOIN ec_flight_bookings p 
			ON d.booking_id = p.id AND p.deleted = 0
			WHERE d.deleted = 0
			AND p.booking_status IN ('7', '8')
			AND p.is_ticket_exported = 1
			" . $sql_search . "
			AND d.total_bought_price > 0
			AND d.supplier_id IS NOT NULL
			GROUP BY d.supplier_id

			-- BOOKING PAXS OUTBOUND
			UNION
			SELECT 
				SUM(IFNULL(d.luggage_purchase, 0)) AS debt_amount
			  , 0 AS pay_amount
			  , d.supplier_id
			  , CONCAT(d.id, '-OUTBOUND') AS id
			FROM ec_booking_passengers d
			LEFT JOIN ec_flight_bookings p 
			ON d.booking_id = p.id AND p.deleted = 0
			WHERE d.deleted = 0
			AND p.booking_status IN ('7', '8')
			AND p.is_ticket_exported = 1
			" . $sql_search . "
			AND d.luggage_price > 0
			AND d.luggage_purchase > 0
			AND d.supplier_id IS NOT NULL
			AND d.add_type IS NULL
			GROUP BY d.supplier_id

			-- BOOKING PAXS INBOUND
			UNION
			SELECT 
				SUM(IFNULL(d.luggage_purchase_inbound, 0)) AS debt_amount
			  , 0 AS pay_amount
			  , d.supplier_inbound_id AS supplier_id
			  , CONCAT(d.id, '-INBOUND') AS id
			FROM ec_booking_passengers d
			LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
			WHERE d.deleted = 0
			AND p.booking_status IN ('7', '8')
			AND p.is_ticket_exported = 1
			" . $sql_search . "
			AND d.luggage_price_inbound > 0
			AND d.luggage_purchase_inbound > 0
			AND d.supplier_inbound_id IS NOT NULL
			AND d.add_type IS NULL
			GROUP BY d.supplier_inbound_id

			-- RECEIPT
			UNION
			SELECT 
				p.amount AS debt_amount
			  , 0 AS pay_amount
			  , account_id_c AS supplier_id
			  , p.id AS id
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
			AND p.loai_thu = '9'
			AND p.account_id_c IS NOT NULL
			" . $sql_search2 . "

			-- SUPPLIER 1
			UNION
			SELECT 
				IFNULL(p.bought_amount, 0) AS debt_amount
			  , 0 AS pay_amount
			  , p.supplier_id
			  , CONCAT(p.id, '-SUPPLIER1') AS id
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
			AND p.loai_thu IN ('4', '5')
			AND p.supplier_id IS NOT NULL
			AND p.bought_amount IS NOT NULL
			" . $sql_search2 . "

			-- SUPPLIER 2
			UNION
			SELECT 
				IFNULL(p.bought_amount2, 0) AS debt_amount
			  , 0 AS pay_amount
			  , p.supplier2_id AS supplier_id
			  , CONCAT(p.id, '-SUPPLIER2') AS id
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
			AND p.loai_thu IN ('4', '5')
			AND p.supplier2_id IS NOT NULL
			AND p.bought_amount2 IS NOT NULL
			" . $sql_search2 . "

			-- SUPPLIER 3
			UNION
			SELECT 
				IFNULL(p.bought_amount3, 0) AS debt_amount
			  , 0 AS pay_amount
			  , p.supplier3_id AS supplier_id
			  , CONCAT(p.id, '-SUPPLIER3') AS id
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
			AND p.loai_thu IN ('4', '5')
			AND p.supplier3_id IS NOT NULL
			AND p.bought_amount3 IS NOT NULL
			" . $sql_search2 . "

			-- TICKET REFUND
			UNION
			SELECT 
				- SUM(IFNULL(c.sotienhang, 0)) AS debt_amount
			  , 0 AS pay_amount
			  , c.nhacc_id AS supplier_id
			  , p.id
			FROM ec_chitiethoanve c
			LEFT JOIN ec_hoanve p ON c.hoanve_id = p.id AND p.deleted = 0
			WHERE c.deleted = 0
			AND c.dahoan = 1
			AND p.tinhtrang = '1'
			" . $sql_search2 . "
			AND c.sotienhang > 0
			AND c.nhacc_id IS NOT NULL
			GROUP BY c.nhacc_id

			-- PAYMENT VOUCHER
			UNION
			SELECT 
				0 AS debt_amount
			  , IFNULL(p.amount, 0) AS pay_amount
			  , p.supplier_id
			  , p.id
			FROM ec_payment_voucher p
			WHERE p.deleted = 0
			AND p.pv_status = '3'
			" . $sql_search2 . "
			AND p.supplier_id IS NOT NULL
		) AS tmp
		LEFT JOIN accounts a 
		ON tmp.supplier_id = a.id 
		AND a.deleted = 0
		WHERE a.is_stop_tracking = 0
		AND a.account_type = 'Supplier'
		GROUP BY tmp.supplier_id
		HAVING sotien <> 0";

	$GLOBALS['log']->info($sql);
	$res = $db->query($sql);
	while ($row = $db->fetchByAssoc($res)) {
		if ($row['sotien'] < 0) {
			$no = 0;
			$co = abs($row['sotien']);
		} else {
			$no = abs($row['sotien']);
			$co = 0;
		}

		// xoá số đầu kỳ đang có cập nhật lại
		$sql_del = '
			UPDATE ec_chitiettaikhoan' . $report_year . ' 
			SET deleted = 1
			WHERE deleted = 0 
			AND parent_id = "' . $row['supplier_id'] . '"';
		$db->query($sql_del);

		$sql = 'INSERT INTO ec_chitiettaikhoan' . $report_year . '
				VALUES(
					uuid()
				  , "' . $row['supplier'] . '"
				  , "' . date('Y-m-d H:i:s') . '"
				  , "' . date('Y-m-d H:i:s') . '"
				  , "' . $GLOBALS['current_user']->id . '"
				  , "' . $GLOBALS['current_user']->id . '"
				  , NULL
				  , 0
				  , "' . $GLOBALS['current_user']->id . '"
				  , "331"
				  , ' . $no . '
				  , ' . $co . '
				  , "Accounts"
				  , "' . $row['supplier_id'] . '"
				  , NULL
				  , NULL
				)';
		$db->query($sql);
	}

	$GLOBALS['log']->info("----->End Ket chuyen cong no phai tra cuoi ky vao dau moi nam");
	return true;
}

/*
	LOOP CHECK IF BOOKING OVER 24h 
	AND UNPUBLISH IT
	By: LuongQC
	Date: 24-08-2013
 */
function myLoopCheckIfBookingOver24h()
{

	$GLOBALS['log']->info('----->LOOP CHECK IF BOOKING OVER 24h AND UNPUBLISH IT');
	$db = DBManagerFactory::getInstance();

	$sql = "SELECT id, date_published_to_web FROM ec_flight_bookings WHERE deleted=0 AND is_published_to_web=1 ";
	$res = $db->query($sql);
	while ($row = $db->fetchByAssoc($res)) {
		$date_current = strtotime(date('Y-m-d H:i:s'));
		$date_published = strtotime($row['date_published_to_web']) + 7 * 3600; /* GMT+7 */
		if (($date_current - $date_published) > 86400) { // 24h
			$db->query("UPDATE ec_flight_bookings SET is_published_to_web=0 WHERE id='" . $row['id'] . "' ");
		}
	}

	$GLOBALS['log']->info("----->END LOOP CHECK IF BOOKING OVER 24h AND UNPUBLISH IT");
	return true;
}

// Tạo bảng lương đầu mỗi tháng, Kế thừa bảng lương từ tháng trước
function createMonthSalary()
{
	$db = DBManagerFactory::getInstance();

	// $this_m = '2024-01-01';
	$this_m = date('Y-m-01');
	$end_date = date('Y-m-t', strtotime($this_m));

	// từ ngày 01 tháng trước
	$from_date_prev_m = date('Y-m-01', strtotime('-1 month', strtotime($this_m)));

	// kế thừa bảng lương từ tháng trước
	$sql = '
		SELECT 
			s.basic_salary, s.efficient_wage, s.gas_allowance
			, s.lunch_allowance, s.tele_allowance
			, s.responsible_allowance
			, s.seniority_allowance, s.other_allowance1
			, s.other_allowance2
			, s.social_insurance, s.assigned_user_id
			, s.name
		FROM ec_employee_salary s
		INNER JOIN (
			SELECT usr.id, his.status AS his_stt
			FROM users usr
			INNER JOIN ec_workhistory his
			ON his.assigned_user_id = usr.id
			AND his.deleted = 0
			AND DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . $this_m . '"
			AND LAST_DAY(IFNULL(his.date_end, "' . $end_date . '")) >= "' . $end_date . '"
			WHERE usr.deleted = 0
		) AS u ON u.id = s.assigned_user_id
		WHERE u.his_stt <> "InActive" AND s.deleted = 0
		AND s.month = "' . date('n', strtotime($from_date_prev_m)) . '"
		AND s.year = "' . date('Y', strtotime($from_date_prev_m)) . '"';

	$res = $db->query($sql);
	while ($row = $db->fetchByAssoc($res)) {
		// Xóa row cũ tháng mới
		$sql = 'UPDATE ec_employee_salary SET deleted = 1
			WHERE assigned_user_id = "' . $row['assigned_user_id'] . '"
			AND month = "' . date('n', strtotime($this_m)) . '"
			AND year = "' . date('Y', strtotime($this_m)) . '"';
		$db->query($sql);

		// tạo mới row cho tháng mới
		$salary = new EC_Employee_Salary;
		$salary->name 					= $row['name'];
		$salary->assigned_user_id 		= $row['assigned_user_id'];
		$salary->month 				= date('n', strtotime($this_m));
		$salary->year 					= date('Y', strtotime($this_m));
		$salary->basic_salary 			= (int)$row['basic_salary'];
		$salary->efficient_wage 			= (int)$row['efficient_wage'];
		$salary->gas_allowance 			= (int)$row['gas_allowance'];
		$salary->lunch_allowance 		= (int)$row['lunch_allowance'];
		$salary->tele_allowance 			= (int)$row['tele_allowance'];
		$salary->responsible_allowance 	= (int)$row['responsible_allowance'];
		$salary->seniority_allowance 		= (int)$row['seniority_allowance'];
		$salary->other_allowance1 		= (int)$row['other_allowance1'];
		$salary->other_allowance2 		= (int)$row['other_allowance2'];
		$salary->social_insurance 		= (int)$row['social_insurance'];
		$salary->save();
	}

	return true;
}

/* Cập nhật thưởng doanh số nhân viên
 * Chỉ có khi doanh số công ty trong tháng > 80% của target
 */
function updateSales()
{
	global $db;

	$from_date 	= date('Y-m-01');
	$to_date 		= date('Y-m-d', strtotime('-1 day'));

	if (date('j') == 1) {
		$from_date = date('Y-m-01', strtotime('-1 month'));
		$to_date = date('Y-m-t', strtotime('-1 month'));
	}

	// lấy target của công ty trong tháng
	$sql_target_com = '
		SELECT target_month' . date('n', strtotime($from_date)) . ' 
		FROM ec_targets 
		WHERE deleted = 0 AND status = 2 
		AND year = "' . date('Y', strtotime($from_date)) . '" 
		AND target_type = 0';

	$target_month = (int)$db->getOne($sql_target_com);

	// tổng doanh số công ty
	$sql_total_sales = '
		SELECT (SUM(t.doanhso) 
				- SUM(t.luggage_purchase_price))  AS doanhso 
		FROM (
			SELECT 	bk.assigned_user_id AS user_id,
				bk.id, bk.id AS voucher_id,
				COUNT( bk.id ) / COUNT( dt.id ) AS total_bk,
				SUM( dt.quantity ) AS total_qty,
				SUM( bk.total_amount ) / COUNT( dt.id ) - SUM(
				IFNULL( dt.total_bought_price, 0 )) - IFNULL((SELECT SUM(amount) FROM ec_payment_voucher WHERE deleted=0 AND booking_id=bk.id AND pv_status="3" AND ec_payment_types_id_c="3f9f8060-1866-2b2e-8322-52e36b8f58d5"), 0) AS doanhso,
				(
					SELECT SUM(IF(luggage_price > 0, IFNULL( luggage_purchase, 0 ), 0) + IF(luggage_price_inbound > 0, IFNULL( luggage_purchase_inbound, 0 ), 0)) 
					FROM ec_booking_passengers 
					WHERE deleted = 0 
					AND booking_id = bk.id 
					AND add_type IS NULL
				) AS luggage_purchase_price 
			FROM
				ec_flight_bookings bk
				LEFT JOIN ec_booking_details dt ON dt.booking_id = bk.id 
				AND dt.deleted = 0 
			WHERE
				bk.deleted = 0 
				AND bk.booking_status = 8 
				AND bk.date_ticket_issue >= "' . date('Y-m-d', strtotime($from_date)) . '" 
				AND bk.date_ticket_issue <= "' . date('Y-m-d', strtotime($to_date)) . '" 
			GROUP BY
				bk.assigned_user_id,
				bk.id 

			-- hoanve
			UNION
			SELECT
				bk.assigned_user_id AS user_id,
				bk.id, hv.id AS voucher_id,
				0 AS total_bk,
				- COUNT( cthv.id ) AS total_qty,
				IF(SUM(IFNULL( cthv.phidichvu, 0 )) > 0, 0, SUM(IFNULL( cthv.phidichvu, 0 ))) AS doanhso,
				0 AS luggage_purchase_price 
			FROM
				ec_chitiethoanve cthv
				LEFT JOIN ec_hoanve hv ON hv.id = cthv.hoanve_id 
				AND hv.deleted = 0
				LEFT JOIN ec_flight_bookings bk ON bk.id = hv.booking_id 
				AND bk.deleted = 0 
			WHERE
				cthv.deleted = 0 
				AND hv.tinhtrang = 1 
				AND hv.ngayhachtoan >= "' . date('Y-m-d', strtotime($from_date)) . '" 
				AND hv.ngayhachtoan <= "' . date('Y-m-d', strtotime($to_date)) . '"
			GROUP BY hv.id

			-- hoanve > 0
			UNION
			SELECT
				hv.assigned_user_id AS user_id,
				bk.id, hv.id AS voucher_id,
				0 AS total_bk,
				0 AS total_qty,
				SUM(IFNULL( cthv.phidichvu, 0 )) AS doanhso,
				0 AS luggage_purchase_price 
			FROM
				ec_chitiethoanve cthv
				LEFT JOIN ec_hoanve hv ON hv.id = cthv.hoanve_id 
				AND hv.deleted = 0
				LEFT JOIN ec_flight_bookings bk ON bk.id = hv.booking_id 
				AND bk.deleted = 0 
			WHERE
				cthv.deleted = 0 
				AND hv.tinhtrang = 1 
				AND hv.ngayhachtoan >= "' . date('Y-m-d', strtotime($from_date)) . '" 
				AND hv.ngayhachtoan <= "' . date('Y-m-d', strtotime($to_date)) . '" 
			GROUP BY hv.id
			HAVING SUM(IFNULL( cthv.phidichvu, 0 )) > 0

			-- phieu thu hanh ly, doi ngay bay, doi ten
			UNION
			SELECT
				t.assigned_user_id AS user_id,
				bk.id, t.id AS voucher_id,
				0 AS total_bk,
				0 AS total_qty,
				SUM(IFNULL( t.sell_amount, 0 ) + IFNULL( t.sell_amount2, 0 ) + IFNULL( t.sell_amount3, 0 )) 
				- SUM(IFNULL( t.bought_amount, 0 ) + IFNULL( t.bought_amount2, 0 ) + IFNULL( t.bought_amount3, 0 )) AS doanhso,
				0 AS luggage_purchase_price 
			FROM
				ec_receipt_voucher t
				LEFT JOIN ec_flight_bookings bk ON bk.id = t.booking_id 
				AND bk.deleted = 0 
			WHERE
				t.deleted = 0 
				AND t.rv_status = 1 
				AND t.loai_thu IN ( 4, 5 ) 
				AND DATE_FORMAT(DATE_ADD(t.ngayhachtoan, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . date('Y-m-d', strtotime($from_date)) . '" 
				AND DATE_FORMAT(DATE_ADD(t.ngayhachtoan, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . date('Y-m-d', strtotime($to_date)) . '" 
			GROUP BY t.id
		) AS t';
	$total_sales = $db->getOne($sql_total_sales);

	if (!empty($target_month) && $total_sales > 0.8 * $target_month) {
		// mức thưởng doanh số
		$sql_rate_bonus = '
			SELECT * FROM ec_commission 
			WHERE deleted = 0 AND DATE_FORMAT(date_entered, "%Y-%m-%d") = ( 
				SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_commission 
				WHERE deleted = 0 AND CONCAT(year, "-", month, "-01") <= "' . date('Y-n-01') . '"
				ORDER BY date_entered DESC
				LIMIT 1
			)';
		$res_rate_bonus = $db->query($sql_rate_bonus);
		$m = 0;
		while ($row_rate_bonus = $db->fetchByAssoc($res_rate_bonus)) {
			$rate['from_value'][] = $row_rate_bonus['from_value'];
			$rate['to_value'][] = $row_rate_bonus['to_value'];
			$rate['percentage'][] = $row_rate_bonus['percentage'];
			// lấy doanh số tối thiểu
			if ($m == 0) {
				$min_rate = $row_rate_bonus['from_value'];
			}
			$m++;
		}
	}
	// ds thưởng = tổng doanh số - doanh số của những booking cú đêm
	// nếu ds công ty đạt 100% thì xét thưởng
	// nếu ds công ty từ 80% mà không đạt ds tối thiểu thì trừ lương
	$sql = '
		SELECT 
			  t.user_id, CONCAT(u.last_name, " ", IFNULL( u.first_name, "" )) AS full_name
			, u.title AS user_title
			, SUM(t.total_bk) AS total_bk
			, SUM(t.total_qty) AS ticket_qty
			, (SUM(t.doanhso) - SUM(t.luggage_purchase_price) - s.profit_overnight)  AS doanhso 
		FROM (
			SELECT 	
				bk.assigned_user_id AS user_id,
				bk.id, bk.id AS voucher_id,
				COUNT( bk.id ) / COUNT( dt.id ) AS total_bk,
				SUM( dt.quantity ) AS total_qty,
				SUM( bk.total_amount ) / COUNT( dt.id ) - SUM(
				IFNULL( dt.total_bought_price, 0 )) - IFNULL((SELECT SUM(amount) FROM ec_payment_voucher WHERE deleted=0 AND booking_id=bk.id AND pv_status="3" AND ec_payment_types_id_c="3f9f8060-1866-2b2e-8322-52e36b8f58d5"), 0) AS doanhso,
				(
					SELECT SUM(IF(luggage_price > 0, IFNULL( luggage_purchase, 0 ), 0) + IF(luggage_price_inbound > 0, IFNULL( luggage_purchase_inbound, 0 ), 0)) 
					FROM ec_booking_passengers 
					WHERE
						deleted = 0 
						AND booking_id = bk.id 
						AND add_type IS NULL
				) AS luggage_purchase_price 
			FROM
				ec_flight_bookings bk
				LEFT JOIN ec_booking_details dt ON dt.booking_id = bk.id 
				AND dt.deleted = 0 
			WHERE
				bk.deleted = 0 
				AND bk.booking_status = 8 
				AND bk.date_ticket_issue >= "' . date('Y-m-d', strtotime($from_date)) . '" 
				AND bk.date_ticket_issue <= "' . date('Y-m-d', strtotime($to_date)) . '" 
			GROUP BY
				bk.id 

			-- hoanve
			UNION
			SELECT
				bk.assigned_user_id AS user_id,
				bk.id, hv.id AS voucher_id,
				0 AS total_bk,
				- COUNT( cthv.id ) AS total_qty,
				IF(SUM(IFNULL( cthv.phidichvu, 0 )) > 0, 0, SUM(IFNULL( cthv.phidichvu, 0 ))) AS doanhso,
				0 AS luggage_purchase_price 
			FROM
				ec_chitiethoanve cthv
				LEFT JOIN ec_hoanve hv ON hv.id = cthv.hoanve_id 
				AND hv.deleted = 0
				LEFT JOIN ec_flight_bookings bk ON bk.id = hv.booking_id 
				AND bk.deleted = 0 
			WHERE
				cthv.deleted = 0 
				AND hv.tinhtrang = 1 
				AND hv.ngayhachtoan >= "' . date('Y-m-d', strtotime($from_date)) . '" 
				AND hv.ngayhachtoan <= "' . date('Y-m-d', strtotime($to_date)) . '"
			GROUP BY hv.id

			-- hoanve > 0
			UNION
			SELECT
				hv.assigned_user_id AS user_id,
				bk.id, hv.id AS voucher_id,
				0 AS total_bk,
				0 AS total_qty,
				SUM(IFNULL( cthv.phidichvu, 0 )) AS doanhso,
				0 AS luggage_purchase_price 
			FROM
				ec_chitiethoanve cthv
				LEFT JOIN ec_hoanve hv ON hv.id = cthv.hoanve_id 
				AND hv.deleted = 0
				LEFT JOIN ec_flight_bookings bk ON bk.id = hv.booking_id 
				AND bk.deleted = 0 
			WHERE
				cthv.deleted = 0 
				AND hv.tinhtrang = 1 
				AND hv.ngayhachtoan >= "' . date('Y-m-d', strtotime($from_date)) . '" 
				AND hv.ngayhachtoan <= "' . date('Y-m-d', strtotime($to_date)) . '" 
			GROUP BY hv.id
			HAVING SUM(IFNULL( cthv.phidichvu, 0 )) > 0

			-- phieu thu hanh ly, doi ngay bay, doi ten
			UNION
			SELECT
				t.assigned_user_id AS user_id,
				bk.id, t.id AS voucher_id,
				0 AS total_bk,
				0 AS total_qty,
				SUM(IFNULL( t.sell_amount, 0 ) + IFNULL( t.sell_amount2, 0 ) + IFNULL( t.sell_amount3, 0 )) 
				- SUM(IFNULL( t.bought_amount, 0 ) + IFNULL( t.bought_amount2, 0 ) + IFNULL( t.bought_amount3, 0 )) AS doanhso,
				0 AS luggage_purchase_price 
			FROM
				ec_receipt_voucher t
				LEFT JOIN ec_flight_bookings bk ON bk.id = t.booking_id 
				AND bk.deleted = 0 
			WHERE
				t.deleted = 0 
				AND t.rv_status IN ( 1, 2 )
				AND t.loai_thu IN ( 4, 5 )
				AND DATE_FORMAT(DATE_ADD(t.ngayhachtoan, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . date('Y-m-d', strtotime($from_date)) . '" 
				AND DATE_FORMAT(DATE_ADD(t.ngayhachtoan, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . date('Y-m-d', strtotime($to_date)) . '" 
			GROUP BY t.id
		) AS t
		LEFT JOIN users u ON u.id = t.user_id
		LEFT JOIN ec_employee_salary s ON s.assigned_user_id = t.user_id
		AND s.month = ' . date('n', strtotime($from_date)) . ' 
		AND s.year = ' . date('Y', strtotime($from_date)) . ' AND s.deleted = 0
		GROUP BY t.user_id
		ORDER BY doanhso DESC';

	$res = $db->query($sql);
	$bonus_indirect = 0;
	$not_enough = 0;
	while ($row = $db->fetchByAssoc($res)) {
		$bonus = $backup_fund = 0;
		// nếu ds công ty đạt 100% thì xét thưởng
		if (!empty($target_month) && $total_sales >= $target_month) {
			// đối với booker
			if (trim($row['user_title']) == 'Booker') {
				// trên mức doanh số tối thiểu
				if ($row['doanhso'] > $min_rate) {
					for ($i = 0; $i < count($rate['from_value']); $i++) {
						if ($row['doanhso'] > $rate['from_value'][$i] && $row['doanhso'] <= $rate['to_value'][$i]) {
							// doanh số cho booker là 90% bonus
							$bonus = 0.8 * ($row['doanhso'] - $rate['from_value'][0]) * $rate['percentage'][$i] / 100;

							// doanh số cho khối gián tiếp là 30% bonus
							$bonus_indirect += 0.3 * ($row['doanhso'] - $rate['from_value'][0]) * $rate['percentage'][$i] / 100;

							// chia cho quỹ dự phòng
							$backup_fund = 0.1 * ($row['doanhso'] - $rate['from_value'][0]) * $rate['percentage'][$i] / 100;
						}
					}

					// cập nhật thưởng doanh số cho từng người
					$sql2 = '
						UPDATE ec_employee_salary 
						SET sales = ' . $bonus . '
						  , backup_fund = ' . $backup_fund . '
						WHERE deleted = 0
						AND assigned_user_id = "' . $row['user_id'] . '" 
						AND month = "' . date('n', strtotime($from_date)) . '" 
						AND year = "' . date('Y', strtotime($from_date)) . '"
						AND is_approved = 0';
					$db->query($sql2);
				} else { // dưới mức ds tối thiểu trừ thu nhập
					// kiểm tra nếu có rồi thì xoá
					$sql_d = '
						DELETE FROM ec_salary_details 
						WHERE deleted = 0 AND type = "minus" 
						AND assigned_user_id = "' . $row['user_id'] . '"
						AND voucher_date = "' . $from_date . '"
						AND reason = "KhongDatDSToiThieu"';
					$db->query($sql_d);
					if ($row['doanhso'] > 0) {
						$sql_s = '
							SELECT basic_salary, efficient_wage 
							FROM ec_employee_salary
							WHERE deleted = 0
							AND assigned_user_id = "' . $row['user_id'] . '"
							AND month = "' . date('n', strtotime($from_date)) . '" 
							AND year = "' . date('Y', strtotime($from_date)) . '"';
						$res_s = $db->query($sql_s);
						$row_s = $db->fetchByAssoc($res_s);
						$minus_tn = new EC_Salary_Details;
						$minus_tn->name = $row['full_name'];
						$minus_tn->description = 'Không đạt doanh số tối thiểu: ' . $min_rate;
						$minus_tn->assigned_user_id = $row['user_id'];
						$minus_tn->reason = 'KhongDatDSToiThieu';
						$minus_tn->minus_amount = ($row_s['basic_salary'] + $row_s['efficient_wage']) - round(($row_s['basic_salary'] + $row_s['efficient_wage']) * $row['doanhso'] / $min_rate);
						$minus_tn->type = 'minus';
						$minus_tn->voucher_date = date('d-m-Y', strtotime($from_date));
						$minus_tn->save();
					}
				}
			}
		} else {
			$not_enough = 1;
			break;
		}
	}

	if ($bonus_indirect > 0) {
		$sql3 = '
			SELECT COUNT(*) FROM users 
			WHERE deleted = 0 AND title IN (
			"KeToan", "Leader"
			) AND status = "Active" 
			AND start_working_date IS NOT NULL
			AND employee_type <> 3';
		$indirect_ppl = $db->getOne($sql3);

		($indirect_ppl == 0) ? $indirect_ppl = 1 : $indirect_ppl;

		$sql_udt = '
			UPDATE ec_employee_salary s 
			INNER JOIN users u ON u.id = s.assigned_user_id
			SET s.sales = ' . round($bonus_indirect / $indirect_ppl) . '
			WHERE s.deleted = 0 AND u.deleted = 0 AND u.title IN (
				"Leader", "KeToan"
			) AND s.month = "' . date('m', strtotime($from_date)) . '" 
			AND s.year = "' . date('Y', strtotime($from_date)) . '" 
			AND u.status = "Active"
			AND u.start_working_date IS NOT NULL
			AND u.employee_type <> 3
			AND s.is_approved = 0
			AND s.deleted = 0';
		$db->query($sql_udt);
	}

	// nếu ds công ty không đạt 100% thì không có thưởng
	if ($not_enough) {
		$sql2 = '
			UPDATE ec_employee_salary SET sales = 0 WHERE deleted = 0 
			AND month = "' . date('m', strtotime($from_date)) . '" 
			AND year = "' . date('Y', strtotime($from_date)) . '"';
		$db->query($sql2);
	}

	return true;
}

/* 
	* ----------------------------------------------
	* ----------------------------------------------
	* ===== Cập nhật số ngày công, số ngày làm ngoài h, số ngày nghỉ của nhân viên trong bảng lương
	* ----------------------------------------------
	* ----------------------------------------------
*/

// tính các ngày CN giữa 2 ngày
function calSundaysBetweenTwoDays($from_date, $to_date)
{
	$total_sunday = 0;
	$date_range = myGetDateRange($from_date, $to_date);
	foreach ($date_range as $val) {
		if (date('l', strtotime($val)) == 'Sunday') {
			$total_sunday += 1;
		}
	}
	return $total_sunday;
}


function updateWorkingDays()
{
	global $db;

	$today = date('Y-m-d');
	// $today 	= date('Y-m-d H:i:s', strtotime('+7 hours'));
	$start_date 		= date('Y-m-01', strtotime($today));
	$end_date 		= date('Y-m-t', strtotime($today));
	$month 			= date('m-Y', strtotime($today));
	$month_before 		= date('m-Y', strtotime('-1 month', strtotime($today)));
	$end_date_before 	= date('Y-m-t', strtotime('-1 month', strtotime($start_date)));

	// từ ngày phải lấy ngày duyệt lương của tháng trước
	$sql = '
		SELECT DATE_FORMAT(approved_date, "%Y-%m-%d") AS from_date 
		FROM ec_employee_salary 
		WHERE deleted = 0 
		AND month = "' . date('n', strtotime('01-' . $month . ' -1 month')) . '" 
		AND year = "' . date('Y', strtotime('01-' . $month . ' -1 month')) . '"
		LIMIT 1
	';
	$res = $db->query($sql);
	$row_fdate = $db->fetchByAssoc($res);
	$from_date_q = '';

	if (strtotime($row_fdate['from_date']) < strtotime('01-' . $month) && strtotime($row_fdate['from_date']) != false) {
		$from_date_s = $row_fdate['from_date'];
		$from_date_q = ' AND from_date >= "' . $row_fdate['from_date'] . '"';
		$first_sunday_lastm = 7 - date('N', strtotime($from_date_s)) + 1;

		for ($i = $first_sunday_lastm; $i <= date('t', strtotime('-1 month')); $i += 7) {
			if ($i > (int)date('d', strtotime($from_date_s)))
				$sundays_lastm_left[] = $i;
		}
	} else {
		$from_date_s = $start_date;
	}

	$sql = 'SELECT l.used_leave_days_curr AS used_leave_days
			   , IFNULL(l.no_paid_days, 0) AS no_paid_days
			   , IFNULL(l.absence_days, 0) AS leave_days
			  , ot.working_hour AS overtime
			  , u.id AS user_id, u.his_stt
			  , u.start_working_date
			  , (
					 SELECT SUM(d.working_hour) / 8
					 FROM ec_workingovertimedetails d
					 INNER JOIN ec_workingovertimes t
					 ON t.id = d.ec_workingovertimes_id_c
					 AND t.status = 2
					 WHERE d.deleted = 0
					 AND d.assigned_user_id = u.id
					 AND DATE_FORMAT(t.bonus_month, "%m-%Y") = "' . $month . '" 
				 ) AS bonus_work_days
		FROM (
				SELECT usr.id, usr.start_working_date
					 , usr.last_name, usr.first_name
					 , usr.title, usr.deleted, his.with_salary
					 , his.description AS history_desc
					 , his.status AS his_stt
					 , his.date_start AS his_date_start 
				FROM users usr
				INNER JOIN ec_workhistory his
				ON his.assigned_user_id = usr.id
				AND his.deleted = 0
				AND DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . $start_date . '"
				AND LAST_DAY(IFNULL(his.date_end, "' . $end_date . '")) >= "' . $end_date . '"
				WHERE usr.deleted = 0
			) AS u
		LEFT JOIN (
			SELECT SUM(IFNULL(used_leave_days_curr_m, 0)) AS used_leave_days
			 , assigned_user_id
			 , SUM(
			   IF( from_date > "' . $today . '"
				   ,  0
				   ,  CASE WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" 
				   AND DATE_FORMAT( to_date, "%m-%Y" ) = "' . $month . '" 
				   THEN IFNULL( absence_days, 0 )
				   WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" 
				   THEN DATEDIFF("' . $end_date . '", from_date) + 1
				   ELSE DATEDIFF(to_date, "' . $start_date . '") + 1 END 
			   )
			 ) AS absence_days
			 , SUM(
			    IF( from_date > "' . $today . '" OR no_paid_days = 0
				   ,  0
				   ,  CASE 
						   -- trong thang, hom nay > ngay ket thuc nghi
						   WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" AND DATE_FORMAT( to_date, "%m-%Y" ) = "' . $month . '" AND (to_date <= "' . $today . '"  OR DATEDIFF("' . $today . '", from_date) >= no_paid_days)
						   THEN IFNULL(no_paid_days, 0)

						   -- trong thang, hom nay < ngay ket thuc nghi, co chon ngay nghi 0.5 buoi 
						   WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" AND DATE_FORMAT( to_date, "%m-%Y" ) = "' . $month . '" AND to_date > "' . $today . '" AND part_date <= "' . $today . '"
						   THEN DATEDIFF("' . $today . '", from_date) + 0.5

						   -- trong thang, hom nay < ngay ket thuc nghi, ko chon ngay nghi 0.5 buoi 
						   WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" AND DATE_FORMAT( to_date, "%m-%Y" ) = "' . $month . '" AND to_date > "' . $today . '"
						   THEN DATEDIFF("' . $today . '", from_date) + 1

						   -- khac thang
						   WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" 
						   THEN DATEDIFF("' . $end_date . '", from_date) + 1
						   ELSE DATEDIFF("' . $end_date . '", "' . $start_date . '") + 1 END
			   ) 
			 ) AS no_paid_days
			 , (
				SELECT SUM(IFNULL(used_leave_days_next_m, 0)) 
				FROM ec_leaveabsences
				WHERE deleted = 0
				AND status = 2 
				AND (DATE_FORMAT(from_date, "%m-%Y") = "' . $month_before . '" 
				OR DATE_FORMAT(to_date, "%m-%Y") = "' . $month_before . '")
				AND assigned_user_id = a.assigned_user_id
				GROUP BY assigned_user_id
			) AS used_leave_days_curr
			FROM ec_leaveabsences a
			WHERE deleted = 0
			AND status = 2 AND (DATE_FORMAT(from_date, "%m-%Y") = "' . $month . '" 
			OR DATE_FORMAT(to_date, "%m-%Y") = "' . $month . '")' . $from_date_q . '
			GROUP BY assigned_user_id
		) AS l ON l.assigned_user_id = u.id
		LEFT JOIN (
			SELECT SUM(working_hour) AS working_hour, assigned_user_id 
			FROM ec_workingovertimedetails
			WHERE deleted = 0 AND status = 2 
			AND register_date <= "' . $today . '"
			AND register_date >= "' . $from_date_s . '"
			GROUP BY assigned_user_id
		) AS ot ON ot.assigned_user_id = u.id
		WHERE u.deleted = 0 
		AND u.start_working_date IS NOT NULL
		GROUP BY u.id
		ORDER BY (
			CASE 
				WHEN u.title LIKE "%QuanLy%" THEN 1
				WHEN u.title LIKE "%KeToan%" THEN 2
				WHEN u.title LIKE "%Leader%" THEN 3
				WHEN u.title LIKE "%Booker%" THEN 4
			ELSE 5
			END 
		), u.start_working_date';

	$res = $db->query($sql);
	while ($row = $db->fetchByAssoc($res)) {
		if ($row['his_stt'] != 'InActive' && $row['his_stt'] != 'Absent' || (($row['his_stt'] == 'InActive' || $row['his_stt'] == 'Absent') && date('m-Y', strtotime($row['his_date_start'])) == $month)) {
			// tính ngày bắt đầu
			if ($month == date('m-Y')) {
				$tdate = date('j');
				if (date('m-Y') == date('m-Y', strtotime($row['his_date_start'])))
					$working_days = date('j') - date('j', strtotime($row['his_date_start'])) + 1;
				else
					$working_days = date('j', strtotime($today));
			} else {
				$tdate = date('j', strtotime($end_date));
				if (strtotime($end_date) < strtotime($row['his_date_start']))
					$working_days = $tdate = 0;
				else if (strtotime($start_date) < strtotime($row['his_date_start']) && strtotime($end_date) >= strtotime($row['his_date_end']))
					$working_days = date('j', strtotime($row['his_date_end'])) - date('j', strtotime($row['his_date_start'])) + 1;
				else
					$working_days = date('t', strtotime($start_date));
			}

			// tính những ngày nghỉ không lương
			$sql1 = '
				SELECT *
				FROM ec_leaveabsences
				WHERE deleted = 0
				AND status = 2 
				AND (
					DATE_FORMAT(from_date, "%m-%Y") = "' . $month . '" 
					OR DATE_FORMAT(to_date, "%m-%Y") = "' . $month . '"
				)
				AND assigned_user_id = "' . $row['user_id'] . '"
			';
			$res1 = $db->query($sql1);
			$no_paid_days = 0;
			while ($row1 = $db->fetchByAssoc($res1)) {
				if ((float)$row1['no_paid_days'] > 0) {
					// xin trong tháng
					if (
						strtotime($row1['from_date']) >= strtotime($start_date)
						&& strtotime($row1['to_date']) <= strtotime($end_date)
					) {
						// nếu ngày hiện tại chưa tới ngày kết thúc nghỉ
						if (strtotime($row1['to_date']) > strtotime($today)) {
							if (strtotime($today) > strtotime($row1['from_date'])) {
								// đếm số ngày nghỉ cho đến hiện tại
								$leaves = myCalculateDayBetweenDates($row1['from_date'], $today) + 1;
								// nếu số ngày nghỉ > nghỉ không lương
								if ($leaves - $row1['no_paid_days'] >= 0) {
									$no_paid_days += $row1['no_paid_days'];
									// ngược lại
								} else {
									// kiểm tra có CN
									$tt_sun = calSundaysBetweenTwoDays($row1['from_date'], $today);
									$no_paid_days = $leaves - $tt_sun;
								}
							} else $no_paid_days = 0;
						} else {
							$no_paid_days += $row1['no_paid_days'];
						}
						// xin khác tháng
					} else {
						// ngày bắt đầu thuộc tháng trước
						if (strtotime($row1['from_date']) < strtotime($start_date)) {
							$leaves = myCalculateDayBetweenDates($row1['from_date'], $end_date_before) + 1;
							$tt_sun = calSundaysBetweenTwoDays($row1['from_date'], $end_date_before);
							if (($leaves - $tt_sun) < $row1['no_paid_days']) {
								$row1['no_paid_days'] -= ($leaves - $tt_sun);
							}
							if (strtotime($row1['to_date']) <= strtotime($today)) {
								$leaves2 = myCalculateDayBetweenDates($start_date, $today) + 1;
								$tt_sun2 = calSundaysBetweenTwoDays($start_date, $today);
								if ($leaves2 - $tt_sun2 < $row1['no_paid_days']) {
									$no_paid_days += $leaves2 - $tt_sun2;
								} else {
									$no_paid_days += $row1['no_paid_days'];
								}
							} else {
								$no_paid_days += $row1['no_paid_days'];
							}
						} else {
							$leaves = myCalculateDayBetweenDates($row1['from_date'], $end_date) + 1;
							if ($leaves < $row['no_paid_days']) {
								$no_paid_days += $leaves;
							}
						}
					}
				}
			}

			// tính các ngày chủ nhật
			$sundays = array();
			$first_sunday = 7 - date('N', strtotime($start_date)) + 1;
			for ($i = $first_sunday; $i <= $tdate; $i += 7) {
				if (strtotime($i . '-' . $month) >= strtotime($row['his_date_start']) && strtotime($i . '-' . $month) <= strtotime($row['his_date_end']))
					$sundays[] = $i;
			}
			$exclude_days = array_unique(array_merge($sundays), 0);

			// tính số ngày công
			if (strtotime($from_date_s) < strtotime($start_date)) {
				$bonus_days = date('t', strtotime($from_date_s)) - date('d', strtotime($from_date_s)) - count($sundays_lastm_left);
				$working_days += $bonus_days;
			}

			$working_days = $working_days - count($exclude_days) - (float)$no_paid_days + ($row['overtime'] / 8) + $row['bonus_work_days'];

			if ($working_days <= 0) $working_days = 0;

			$sql2 = '
				UPDATE ec_employee_salary 
				SET 
					working_days = ' . $working_days . '
					, ot_days = ' . ($row['overtime'] / 8) . ' 
					, no_paid_days = ' . (float)$row['no_paid_days'] . '
				WHERE is_approved = 0 
				AND assigned_user_id = "' . $row['user_id'] . '"
				AND month = "' . date('n', strtotime($today)) . '" 
				AND year = "' . date('Y', strtotime($today)) . '"
				AND deleted = 0';

			$db->query($sql2);

			// những nhân viên đã nghỉ hoặc tạm vắng thì không tính công
		} else {
			$sql2 = 'UPDATE ec_employee_salary 
						SET working_days = 0
						, ot_days = 0 
						, no_paid_days = 0
						WHERE is_approved = 0 
						AND assigned_user_id = "' . $row['user_id'] . '"
						AND month="' . date('n', strtotime($today)) . '" 
						AND year = "' . date('Y', strtotime($today)) . '" 
						AND deleted = 0';
			$db->query($sql2);
		}
	}
	return true;
}

/* 
	* ---------------------------------------
	* ---------------------------------------
	* ===== Cập nhật nỗ lực trong bảng lương / Nỗ lực = Cú đêm + Giao vé
	* ---------------------------------------
 	* ---------------------------------------
 */
function updateEfforts()
{
	global $db;

	$fdate = date('Y-m-01');
	$tdate = date('Y-m-t');

	$sql = 'SELECT SUM(t.amount) AS amount
					 , SUM(t.overnight) AS overnight
					 , GROUP_CONCAT(t.overnight_bk) AS overnight_bk
					 , SUM(t.delivery) AS delivery
					 , SUM(t.bonus) AS bonus
					 , s.assigned_user_id
				FROM ec_employee_salary s 
				LEFT JOIN (
					SELECT 0 AS amount
		 			 , 0 AS overnight
					 , 0 AS overnight_bk
					 , 0 AS delivery
					 , 0 AS bonus
					 , o.assigned_user_id	
	 				FROM (
		 				SELECT SUM( dt.quantity ) * 20000 AS amount
		 					 , SUM( dt.quantity ) AS qty
		 					 , b.assigned_user_id, b.id AS booking_id
		 					 , b.name AS booking_name, b.booking_status
		 					 , DATE_ADD(b.date_entered, INTERVAL 7 HOUR) AS date_entered 
		 					 , DATE_ADD(
						 		(
						 			SELECT p.date_entered FROM ec_working_process p 
						 			WHERE p.deleted = 0 
						 			AND p.paid > 0 AND p.parent_id = b.id
						 			AND p.description IS NOT NULL AND LENGTH(p.description) > 0
						 			GROUP BY p.parent_id
						 		)
		 					 	, INTERVAL 7 HOUR) AS transfer_time 
		 					 , DATE_ADD(
						 		(
						 			SELECT p.date_entered FROM ec_working_process p 
						 			WHERE p.deleted = 0 
						 			AND p.called > 0 AND p.parent_id = b.id 
						 			GROUP BY p.parent_id
						 		)
		 					 	, INTERVAL 7 HOUR) AS start_process_time 
		 					 , 0 AS bonus, b.is_paid
						FROM
							ec_booking_details dt 
							INNER JOIN ec_flight_bookings b ON b.id = dt.booking_id AND b.deleted = 0
						WHERE
							dt.deleted = 0 
							AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00"
							AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59"
						GROUP BY b.id
						HAVING start_process_time >= "' . $fdate . ' 00:00:00" 
							AND start_process_time <= "' . $tdate . ' 23:59:59" 
							AND start_process_time NOT BETWEEN DATE_FORMAT( start_process_time, "%Y-%m-%d 07:31:00") AND DATE_FORMAT( start_process_time, "%Y-%m-%d 20:59:59")
					) AS o
					GROUP BY o.assigned_user_id

					UNION
					SELECT SUM(p.ticket_delivery) * 20000 AS amount
						 , 0 AS overnight, NULL AS overnight_bk
						 , SUM(p.ticket_delivery) * 20000 AS delivery
						 , 0 AS bonus
						 , p.assigned_user_id 
					FROM ec_working_process p 
					INNER JOIN ec_flight_bookings b ON b.id = p.parent_id AND b.deleted = 0
					AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00" 
					AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59" 
					WHERE p.deleted = 0 AND p.ticket_delivery = 1  
					GROUP BY p.assigned_user_id

					UNION 
					SELECT SUM(bonus_amount) AS amount
						 , 0 AS overnight, NULL AS overnight_bk
						 , 0 AS delivery
						 , SUM(bonus_amount) AS bonus
						 , assigned_user_id			
					FROM ec_salary_details
					WHERE deleted = 0
					AND type = "bonus"
					AND voucher_date >= "' . $fdate . '"
					AND voucher_date <= "' . date('Y-m-d', strtotime($tdate)) . '"
					GROUP BY assigned_user_id

					-- Giao thuc pham ben PT
					UNION ALL
					SELECT SUM(p.ticket_delivery) * 20000 AS amount
						, 0 AS overnight
						, NULL AS overnight_bk
						, SUM(p.ticket_delivery) * 20000 AS delivery
						, 0 AS bonus
						, p.assigned_user_id 
						FROM ec_working_process p 
						INNER JOIN ec_receipt_voucher rv ON rv.id = p.parent_id AND rv.deleted = 0
						AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00" 
						AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59" 
						WHERE p.deleted = 0 AND p.ticket_delivery = 1  
					GROUP BY p.assigned_user_id
				) AS t ON s.assigned_user_id = t.assigned_user_id
				WHERE s.month = ' . date('n', strtotime($fdate)) . '
				AND s.year = ' . date('Y', strtotime($fdate)) . ' 
				AND s.deleted = 0
				GROUP BY s.assigned_user_id';

	$res = $db->query($sql);
	while ($row = $db->fetchByAssoc($res)) {
		// cập nhật cột giao vé
		$sql2 = '
			UPDATE ec_employee_salary 
			SET effort = ' . (empty($row['amount']) ? 0 : $row['amount']) . '
			, delivery = ' . (empty($row['delivery']) ? 0 : $row['delivery']) . '
			WHERE deleted = 0 
			AND assigned_user_id = "' . $row['assigned_user_id'] . '" 
			AND month = "' . date('n', strtotime($fdate)) . '" 
			AND year = "' . date('Y', strtotime($fdate)) . '" 
			AND is_approved = 0';
		$db->query($sql2);
	}

	return true;
}

function calculateTotalBKProfit($user_id, $from_date, $to_date, $bk_arr)
{
	global $db;
	$sql = '
			SELECT 
				SUM( bk.total_amount ) / COUNT( dt.id ) 
				- SUM(
					IFNULL( dt.total_bought_price, 0 )) 
					- IFNULL((
						SELECT SUM(amount) 
						FROM ec_payment_voucher 
						WHERE deleted = 0 AND booking_id = bk.id 
						AND pv_status = "3" 
						AND ec_payment_types_id_c="3f9f8060-1866-2b2e-8322-52e36b8f58d5"
					), 0) 
				- (
					SELECT
						SUM(
							IF(luggage_price > 0, IFNULL( luggage_purchase, 0 ), 0) 
							+ IF(luggage_price_inbound > 0, IFNULL( luggage_purchase_inbound, 0 ), 0)
						) 
					FROM ec_booking_passengers 
					WHERE
						deleted = 0 
						AND booking_id = bk.id 
						AND add_type IS NULL
				) AS doanhso
			FROM
				ec_flight_bookings bk
				LEFT JOIN ec_booking_details dt 
				ON dt.booking_id = bk.id 
				AND dt.deleted = 0 
			WHERE
				bk.deleted = 0 
				AND bk.booking_status = 8 
				AND bk.assigned_user_id = "' . $user_id . '"
				AND bk.id IN ("' . implode('","', $bk_arr) . '")
			GROUP BY bk.id';
	$res = $db->query($sql);
	$total = 0;
	while ($row = $db->fetchByAssoc($res)) {
		$total += $row['doanhso'];
	}
	return $total;
}

// Khoá bảng lương vào cuối mỗi tháng
function lockSalaryAtEndMonth()
{
	global $db;

	$sql = 'UPDATE ec_employee_salary 
			SET is_approved = 1, approved_date = "' . date('Y-m-d H:i:s') . '" 
			WHERE deleted = 0 AND month = "' . date('n', strtotime('-1 month')) . '" 
			AND year = "' . date('Y', strtotime('-1 month')) . '" AND is_approved = 0';
	$db->query($sql);
	return true;
}

// Cập nhật số tiền nỗ lực của tháng trước
// Khi bảng lương khoá trước ngày cuối tháng
// Tiền chênh lệch với cột nỗ lực sẽ cộng dồn vào tháng sau
function updateMissingEfforts()
{
	global $db;

	// Xoá hết các dòng bonus đã cộng thêm vào tháng hiện tại
	$sql_d = 'UPDATE ec_salary_details SET deleted = 1 WHERE deleted = 0 AND name = "Bổ sung nỗ lực còn thiếu của tháng ' . date('n', strtotime('-1 month')) . ' năm ' . date('Y', strtotime('-1 month')) . '"';
	$db->query($sql_d);

	// Thêm bonus nỗ lực còn thiếu lại
	$fdate = date('Y-m-01', strtotime('-1 month'));
	$tdate = date('Y-m-t', strtotime('-1 month'));
	$sql = 'SELECT SUM(t.amount) AS amount
				 , SUM(t.saved_overnight) AS saved_overnight
			 	 , SUM(t.saved_delivery) AS saved_delivery
				 , t.assigned_user_id
			FROM (
				SELECT SUM(CASE	
					WHEN o.start_process_time >= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 00:00:00" ) 
					AND o.start_process_time < DATE_FORMAT( o.start_process_time, "%Y-%m-%d 07:31:00" ) 
					AND transfer_time < DATE_FORMAT( o.start_process_time, "%Y-%m-%d 07:31:00" )
					AND is_paid = 1 
					THEN qty * 20000 
					WHEN o.start_process_time >= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 21:00:00" ) 
					AND o.start_process_time <= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 23:59:59" ) 
					AND transfer_time < DATE_FORMAT( DATE_ADD( o.start_process_time, INTERVAL 1 DAY ), "%Y-%m-%d 07:30:00" )
					AND is_paid = 1  
					THEN qty * 20000
					ELSE 2000 END) AS amount
				 , 0 AS saved_overnight
				 , 0 AS saved_delivery
				 , o.assigned_user_id	
 				FROM (
	 				SELECT SUM( dt.quantity ) * 20000 AS amount
	 					 , SUM( dt.quantity ) AS qty
	 					 , b.assigned_user_id, b.id AS booking_id
	 					 , b.name AS booking_name
	 					 , DATE_ADD(b.date_entered, INTERVAL 7 HOUR) AS date_entered
	 					 , DATE_ADD(
					 		(
					 			SELECT p.date_entered FROM ec_working_process p 
					 			WHERE p.deleted = 0 
					 			AND p.called > 0 AND p.parent_id = b.id 
					 			GROUP BY p.parent_id
					 		)
	 					 	, INTERVAL 7 HOUR) AS start_process_time 
	 					 , DATE_ADD(
					 		(
					 			SELECT p.date_entered FROM ec_working_process p 
					 			WHERE p.deleted = 0 
					 			AND p.paid > 0 AND p.parent_id = b.id
					 			AND p.description IS NOT NULL AND LENGTH(p.description) > 0
					 			GROUP BY p.parent_id
					 		)
	 					 	, INTERVAL 7 HOUR) AS transfer_time 
	 					 , 0 AS bonus, b.is_paid
					FROM
						ec_booking_details dt 
						INNER JOIN ec_flight_bookings b ON b.id = dt.booking_id AND b.deleted = 0
					WHERE
						dt.deleted = 0 
						AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00"
						AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59"
					GROUP BY b.id
					HAVING start_process_time >= "' . $fdate . ' 00:00:00" 
					AND start_process_time <= "' . $tdate . ' 23:59:59" 
					AND start_process_time NOT BETWEEN DATE_FORMAT( start_process_time, "%Y-%m-%d 07:31:00") 
					AND DATE_FORMAT( start_process_time, "%Y-%m-%d 20:59:59")
				) AS o
				GROUP BY o.assigned_user_id
				UNION
				SELECT SUM(p.ticket_delivery) * 20000 AS amount
					 , 0 AS saved_delivery
				 	 , 0 AS saved_effort
					 , p.assigned_user_id 
				FROM ec_working_process p 
				INNER JOIN ec_flight_bookings b ON b.id = p.parent_id AND b.deleted = 0
				AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00" 
				AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59" 
				WHERE p.deleted = 0 AND p.ticket_delivery = 1  
				GROUP BY p.assigned_user_id
				UNION 
				SELECT - SUM(IF(bonus_amount < 0, 0, bonus_amount)) AS amount
					 , 0 AS saved_overnight
					 , 0 AS saved_delivery
					 , assigned_user_id			
				FROM ec_salary_details
				WHERE deleted = 0
				AND type = "bonus"
				AND voucher_date >= "' . $fdate . '"
				AND voucher_date <= "' . $tdate . '"
				GROUP BY assigned_user_id
				UNION
				SELECT 0 AS amount
					 , overnight AS saved_overnight
				 	 , delivery AS saved_delivery
					 , assigned_user_id			
				FROM ec_employee_salary
				WHERE deleted = 0
				AND month = "' . date('n', strtotime($fdate)) . '"
				AND year = "' . date('Y', strtotime($fdate)) . '"
				GROUP BY assigned_user_id
			) AS t GROUP BY t.assigned_user_id';
	$res = $db->query($sql);
	while ($row = $db->fetchByAssoc($res)) {
		$bonus = (int)$row['amount'] - (int)$row['saved_overnight'] - (int)$row['saved_delivery'];
		if ($bonus != 0) {
			// kiểm tra đã có chưa, nếu chưa có thì insert, còn nếu có rồi thì update
			$sql2 = 'INSERT INTO ec_salary_details(id, name, date_entered, date_modified, modified_user_id, created_by, description, deleted, assigned_user_id, bonus_amount, reason, type, voucher_date) VALUES (uuid(), "Bổ sung nỗ lực còn thiếu của tháng ' . date('n', strtotime('-1 month')) . ' năm ' . date('Y', strtotime('-1 month')) . '", "' . date('Y-m-d H:i:s') . '", "' . date('Y-m-d H:i:s') . '", 1, 1, "Bổ sung nỗ lực còn thiếu của tháng ' . date('n', strtotime('-1 month')) . ' năm ' . date('Y', strtotime('-1 month')) . '", 0, "' . $row['assigned_user_id'] . '", ' . $bonus . ', "Khac", "bonus", "' . date('Y-m-01') . '")';
			$db->query($sql2);
		}
	}

	return true;
}


// Kiểm tra user còn online hay không
function checkStatusOnlineUser()
{
	global $db;

	// 0: Offline
	// 1: Online
	// 2: Busy
	$path           = "secure_sessions/check_online_logs/";
	$timestamp_now 	= strtotime('+7 hours');

	foreach (array_diff(scandir($path), ['.', '..', 'Thumbs.db', basename(__FILE__)]) as $file) {
		$user_id 		= str_replace('_', '-', pathinfo($file, PATHINFO_FILENAME));
		$data_user 		= json_decode(read_file_logs_online($user_id), true);
		$diffInSeconds 	= abs($timestamp_now - strtotime($data_user['last_time']));
		$agent 			= custom_get_sip_number($user_id);

		if ($user_id == 'c57196c6-e211-9856-43d5-6695498f39ae') continue; //tiennguyen

		// Kiểm tra trạng thái busy (10 phút) và không tương tác (2 phút 30 giây)
		if (($data_user['busy'] == 1 && $diffInSeconds > 600) || ($data_user['busy'] == 0 && $diffInSeconds > 150)) {
			if ($agent) agent_change_status($agent, 'Logged Out');
		}
	}

	return true;
}


// Kiểm tra xem booking giao cho booker đã được xử lý hay chưa? 
// Thời gian xử lý tối đa là 2 phút
function checkBookingHandle()
{
	global $db, $app_list_strings;
	$sql = '
		SELECT 
			onl.id, onl.booking_id,
			b.total_qty, b.name AS booking_name, b.contact_name, b.phone,
			IF(
				b.booking_status <> 1 
				OR (
	 				SELECT IF(COUNT(id) > 0, 1, 0)
	 				FROM tracker
	 				WHERE user_id = b.assigned_user_id AND item_id = b.id
	 			), 1, 0 
	 		) AS is_processed
		FROM ec_online_report onl
		LEFT JOIN ec_flight_bookings b ON b.id = onl.booking_id AND b.deleted = 0
		WHERE onl.deleted = 0
			AND DATE_ADD(onl.date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
			AND (onl.booking_id <> "" OR onl.booking_id IS NOT NULL) 
			AND TIMESTAMPDIFF(MINUTE, DATE_FORMAT(onl.start_assign, "%Y-%m-%d %H:%i"), "' . date('Y-m-d H:i') . '") >= 2
			AND b.deleted = 0
	';

	$res = $db->query($sql);
	$clear_bk_onl 		= array();
	$reassign_bk_arr 	= array();
	$user_off_arr 		= array();

	while ($row = $db->fetchByAssoc($res)) {
		if ($row['is_processed']) {
			$clear_bk_onl[] = $row['id'];
		} else {
			$reassign_bk_arr[] = array(
				'booking_id' 		=> $row['booking_id'],
				'total_qty' 		=> $row['total_qty'],
				'booking_name' 	=> $row['booking_name'],
				'contact_name' 	=> $row['contact_name'],
				'phone' 			=> $row['phone']
			);
			$onl = new EC_Online_Report;
			$onl->retrieve($row['id']);
			$onl->booking_id = '';
			$onl->start_assign = '';
			$onl->status = 0;
			$onl->last_online = date('Y-m-d H:i:s');

			$user_off_arr[]  = $onl->name;
			$booking_off[] = $row['booking_name'];

			$onl->save();
		}
	}

	// Nếu booking đã giao được xử lý -> user online 
	// -> Xoá thông tin đã giao trong bảng online
	if (count($clear_bk_onl) > 0) {
		$sql1 = '
			UPDATE ec_online_report 
			SET booking_id = NULL, start_assign = NULL
			WHERE id IN ("' . implode('","', $clear_bk_onl) . '")
			AND deleted = 0
		';
		$db->query($sql1);
	}

	// user off thì thông báo
	if (count($user_off_arr) > 0) {
		$sendStatus = myTelegramSendMessage(
			json_encode(array(
				'text' => 'User này đã bị Off vì quá 2 phút không xử lý booking ' . implode(", ", $booking_off) . ' được giao: ' . implode(", ", $user_off_arr),
			)),
			$app_list_strings['system_config_list']['telegram_token_id'],
			$app_list_strings['system_config_list']['telegram_chat_id'],
		);
		if ($sendStatus['code'] != 201) {
			$GLOBALS['log']->error('Telegram sent message failed.');
		}
	}
	// if(count($user_off_arr) > 0) {
	// 	sendTestTelegram('User ' .implode(", ", $user_off_arr). ' đã bị off vì quá 2 phút không xử lý booking '.implode(", ", $booking_off).' được giao');
	// }

	// Giao lại các booking cho user onl khác
	if (is_array($reassign_bk_arr) && count($reassign_bk_arr) > 0) {
		foreach ($reassign_bk_arr as $reassign_bk) {
			$onl_r = new EC_Online_Report;
			$user_reassign_id = $onl_r->assignBooking($reassign_bk['booking_id'], $reassign_bk['total_qty']);

			// Thông báo giao lại cho booker tiếp theo
			$user = new User;
			$user->retrieve($user_reassign_id);

			$sendStatus = myTelegramSendMessage(
				json_encode(array(
					'text' => 'Booking ' . $reassign_bk['booking_name'] . ' được giao lại cho ' . $user->last_name . ' ' . $user->first_name . '',
				)),
				$app_list_strings['system_config_list']['telegram_token_id'],
				$app_list_strings['system_config_list']['telegram_chat_id'],
			);
			if ($sendStatus['code'] != 201) {
				$GLOBALS['log']->error('Telegram sent message failed.');
			}

			// sendTestTelegram('Booking ' .$reassign_bk['booking_name']. ' được giao lại cho ' . $user->last_name . ' ' . $user->first_name .'');
		}
	}

	return true;
}

// Tìm những booking giao cho ksnb, giao lại cho người online
function reAssignBooking()
{
	global $db, $app_list_strings, $sugar_config;

	// kt có người online
	$sql_onl = '
		SELECT IF(COUNT(id) > 0, 1, 0)
		FROM ec_online_report
		WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
			AND status IN (1, 2)
			AND deleted = 0
	';
	$is_onl = $db->getOne($sql_onl); // 0, 1

	if ($is_onl) {
		$sql = '
			SELECT id, name, contact_name, phone, total_qty
			FROM ec_flight_bookings
			WHERE assigned_user_id = "e3bbb3e5-6660-0bf7-8976-54869c4ee609"
				AND deleted = 0 
				AND booking_status = 1
		';
		$res = $db->query($sql);
		$row_count = $db->countRows($res);

		if ($row_count > 0) {
			while ($row = $db->fetchByAssoc($res)) {
				$onl_r = new EC_Online_Report;
				$assgined_user_id = $onl_r->assignBooking($row['id'], $row['total_qty']);

				if ($assgined_user_id != 'e3bbb3e5-6660-0bf7-8976-54869c4ee609') {
					$user = new User;
					$user->retrieve($assgined_user_id);
					$sql_upd = '
						UPDATE ec_flight_bookings
						SET assigned_user_id = "' . $assgined_user_id . '"
						WHERE id = "' . $row['id'] . '"
						AND deleted = 0 
					';
					$db->query($sql_upd);

					// lưu lại thông báo lên group
					$reassign_bk[] = "Booking: " . $row['name'] . ' giao cho: ' . $user->last_name . ' ' . $user->first_name;

					// UPDATE VỀ TRẠNG THÁI BẬN
					$sql_update_busy = '
						SELECT id, assigned_user_id
						FROM ec_online_report 				
						WHERE assigned_user_id = "' . $user->id . '"
							AND DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
							AND deleted = 0
						LIMIT 1
					';
					$res_update_busy = $db->query($sql_update_busy);
					$row_assign = $db->fetchByAssoc($res_update_busy);
					$online = new EC_Online_Report;
					$online->retrieve($row_assign['id']);
					$online->status = 2;
					$online->last_online 	= date('Y-m-d H:i:s');
					$online->start_assign 	= date('Y-m-d H:i:00');
					$online->booking_id 	= $row['id'];
					$online->round 		= (int)$online->round + 1;
					$online->total_qty  	= $row['total_qty'];
					$online->save();
				}
			}

			if (count($reassign_bk) > 0) {
				$sendStatus = myTelegramSendMessage(
					json_encode(array(
						'text' => 'Thông tin giao lại: ' . implode("\n", $reassign_bk),
					)),
					$app_list_strings['system_config_list']['telegram_token_id'],
					$app_list_strings['system_config_list']['telegram_chat_id'],
				);
				if ($sendStatus['code'] != 201) {
					$GLOBALS['log']->error('Telegram sent message failed.');
				}
				// sendTestTelegram('Thông tin giao lại: ' . implode("\n", $reassign_bk));
			}
		}
	}
	return true;
}

// Tính toán trước Báo cáo dòng tiền
function calculateCashFlow()
{
	$EC_CashFlow = new EC_CashFlow();
	$EC_CashFlow->handle();
	return true;
}
