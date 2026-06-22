<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $db, $app_list_strings;

function voucherJsonResponse($payload)
{
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($payload);
	exit();
}

function isBookingVoucherSugarId($id)
{
	$id = trim((string)$id);
	// SugarCRM record ids are typically 36 chars UUIDs.
	// Some frontends may pass empty / malformed ids; handle safely.
	return strlen($id) === 36;
}

function isBookingVoucherEditableStatus($status)
{
	return in_array((int)$status, [1, 2, 6]);
}

function getBookingVoucherPost($key)
{
	return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

function findBookingVoucherBooking($bookingId, $fields)
{
	global $db;

	$safeBookingId = $db->quote($bookingId);
	$sql = "SELECT $fields
		FROM ec_flight_bookings
		WHERE id = '$safeBookingId' AND deleted = 0
		LIMIT 1";

	return $db->fetchByAssoc($db->query($sql));
}

function findPendingSingleBookingVouchersByCode($voucherCode)
{
	global $db;

	$safeVoucherCode = $db->quote($voucherCode);
	$sql = "SELECT id, name, type, campaign_name, start_time, end_time,
			reduce_amount, reduce_percent, max_discount, condition_voucher, date_entered
		FROM ec_vouchers
		WHERE name = '$safeVoucherCode'
			AND type = 'single'
			AND status = 'pending'
			AND deleted = 0
		ORDER BY date_entered DESC
		LIMIT 100";

	$res = $db->query($sql);
	$vouchers = [];
	while ($row = $db->fetchByAssoc($res)) {
		$vouchers[] = $row;
	}

	return $vouchers;
}

function findPendingSingleBookingVoucherById($voucherId)
{
	global $db;

	$safeVoucherId = $db->quote($voucherId);
	$sql = "SELECT id, name, type, campaign_name, start_time, end_time,
			reduce_amount, reduce_percent, max_discount, condition_voucher, date_entered
		FROM ec_vouchers
		WHERE id = '$safeVoucherId'
			AND type = 'single'
			AND status = 'pending'
			AND deleted = 0
		LIMIT 1";

	return $db->fetchByAssoc($db->query($sql));
}

function normalizeBookingVoucherPhone($phone)
{
	$phone = preg_replace('/\D/', '', (string)$phone);

	if (strpos($phone, '0084') === 0) {
		$phone = '0' . substr($phone, 4);
	} elseif (strpos($phone, '84') === 0 && strlen($phone) >= 11) {
		$phone = '0' . substr($phone, 2);
	} elseif (strlen($phone) === 9) {
		$phone = '0' . $phone;
	}

	return $phone;
}

function parseBookingVoucherCondition($conditionVoucher)
{
	$condition = json_decode(html_entity_decode(trim((string)$conditionVoucher)), true);
	return is_array($condition) ? $condition : [];
}

function getBookingVoucherRoutes($bookingId, $bookingJourney)
{
	global $db;

	$routes = [];
	foreach (explode(',', strtoupper((string)$bookingJourney)) as $route) {
		$route = trim(str_replace(' ', '', $route));
		if (strlen($route) >= 7) {
			$routes[$route] = $route;
		}
	}

	$safeBookingId = $db->quote($bookingId);
	$sql = "SELECT departure, arrival
			FROM ec_booking_itineraries
			WHERE booking_id = '$safeBookingId'
				AND deleted = 0
				AND (add_type IS NULL OR add_type = 0 OR add_type = '')
			ORDER BY direction ASC, departure_date ASC";
	$res = $db->query($sql);
	while ($row = $db->fetchByAssoc($res)) {
		$route = strtoupper(trim($row['departure']) . '-' . trim($row['arrival']));
		if (strlen($route) >= 7) {
			$routes[$route] = $route;
		}
	}

	return array_values($routes);
}

function calculateBookingVoucherDiscount($voucher, $baseAmount)
{
	$discount = 0;
	if ((int)$voucher['reduce_amount'] > 0) {
		$discount = (int)$voucher['reduce_amount'];
	} elseif ((int)$voucher['reduce_percent'] > 0) {
		$discount = (int)floor($baseAmount * (int)$voucher['reduce_percent'] / 100);
	}

	if ((int)$voucher['max_discount'] > 0 && $discount > (int)$voucher['max_discount']) {
		$discount = (int)$voucher['max_discount'];
	}

	return max(0, $discount);
}

function getBookingVoucherTimestamp($voucher)
{
	return empty($voucher['date_entered']) ? 0 : (int)strtotime($voucher['date_entered']);
}

function isBetterBookingVoucherCandidate($voucher, $result, $bestVoucher, $bestResult)
{
	if ($bestVoucher === null || $bestResult === null) {
		return true;
	}

	$discountAmount = isset($result['data']['discount_amount']) ? (int)$result['data']['discount_amount'] : 0;
	$bestDiscountAmount = isset($bestResult['data']['discount_amount']) ? (int)$bestResult['data']['discount_amount'] : 0;
	if ($discountAmount !== $bestDiscountAmount) {
		return $discountAmount > $bestDiscountAmount;
	}

	return getBookingVoucherTimestamp($voucher) > getBookingVoucherTimestamp($bestVoucher);
}

function getBookingVoucherFlightScope($bookingTicketType)
{
	return ((int)$bookingTicketType === 1) ? 'domestic' : 'international';
}

function getBookingVoucherTripType($bookingFlightType)
{
	return ((int)$bookingFlightType === 0) ? 2 : 1;
}

function buildBookingVoucherListItem($voucher, $validation)
{
	$data = empty($validation['data']) ? [] : $validation['data'];
	$discountAmount = isset($data['discount_amount']) ? (int)$data['discount_amount'] : calculateBookingVoucherDiscount($voucher, 0);

	return [
		'voucher_id' => $voucher['id'],
		'code' => $voucher['name'],
		'type' => $voucher['type'],
		'campaign_name' => $voucher['campaign_name'],
		'end_time' => $voucher['end_time'],
		'reduce_amount' => (int)$voucher['reduce_amount'],
		'reduce_percent' => (int)$voucher['reduce_percent'],
		'max_discount' => (int)$voucher['max_discount'],
		'discount_type' => (int)$voucher['reduce_percent'] > 0 ? 'percent' : 'amount',
		'discount_amount' => $discountAmount,
		'selectable' => empty($validation['error']),
		'disabled_reason' => empty($validation['error']) ? '' : $validation['message'],
		'condition_voucher' => parseBookingVoucherCondition($voucher['condition_voucher']),
	];
}

function buildDisabledGroupBookingVoucherListItem($voucher)
{
	return buildBookingVoucherListItem($voucher, [
		'error' => 1,
		'message' => 'Voucher nhóm không áp dụng trực tiếp cho booking',
	]);
}

function getAppliedBookingVouchers($bookingId)
{
	global $db;

	$booking = findBookingVoucherBooking($bookingId, 'id, phone');
	$bookingPhone = $booking ? normalizeBookingVoucherPhone($booking['phone']) : '';
	$safeBookingId = $db->quote($bookingId);
	$sql = "SELECT
				bv.booking_id,
				bv.voucher_id,
				v.name AS code,
				v.type,
				v.status,
				v.campaign_name,
				v.end_time,
				v.reduce_amount,
				v.reduce_percent,
				v.max_discount,
				v.condition_voucher,
				bv.discount_amount
			FROM bookings_vouchers bv
				LEFT JOIN ec_vouchers v ON v.id = bv.voucher_id
			WHERE bv.booking_id = '$safeBookingId'
				AND bv.deleted = 0";

	$res = $db->query($sql);
	$vouchers = [];
	while ($row = $db->fetchByAssoc($res)) {
		$row['reduce_amount'] = (int)$row['reduce_amount'];
		$row['reduce_percent'] = (int)$row['reduce_percent'];
		$row['max_discount'] = (int)$row['max_discount'];
		$row['discount_type'] = (int)$row['reduce_percent'] > 0 ? 'percent' : 'amount';
		$row['condition_voucher'] = parseBookingVoucherCondition($row['condition_voucher']);
		$voucherPhone = empty($row['condition_voucher']['for_phone_value'])
			? ''
			: normalizeBookingVoucherPhone($row['condition_voucher']['for_phone_value']);
		$row['applied_invalid_reason'] = ($voucherPhone !== '' && $bookingPhone !== '' && $voucherPhone !== $bookingPhone)
			? 'Không còn hợp lệ do đổi SĐT'
			: '';
		$vouchers[] = $row;
	}

	return $vouchers;
}

function getAppliedBookingVoucherIds($bookingId)
{
	$voucherIds = [];
	foreach (getAppliedBookingVouchers($bookingId) as $voucher) {
		if (!empty($voucher['voucher_id'])) {
			$voucherIds[$voucher['voucher_id']] = $voucher['voucher_id'];
		}
	}

	return $voucherIds;
}

function getBookingVoucherUpdatedData($bookingId)
{
	$booking = findBookingVoucherBooking($bookingId, 'id, discount_amount, total_amount');
	if (!$booking) {
		return [];
	}

	return [
		'booking_id' => $booking['id'],
		'discount_amount' => (int)$booking['discount_amount'],
		'total_amount' => (int)$booking['total_amount'],
		'applied_vouchers' => getAppliedBookingVouchers($bookingId),
	];
}

function sortBookingVoucherListItems($vouchers)
{
	usort($vouchers, function ($firstVoucher, $secondVoucher) {
		$firstSelectable = !empty($firstVoucher['selectable']) ? 1 : 0;
		$secondSelectable = !empty($secondVoucher['selectable']) ? 1 : 0;
		if ($firstSelectable !== $secondSelectable) {
			return $secondSelectable - $firstSelectable;
		}

		$firstDiscount = isset($firstVoucher['discount_amount']) ? (int)$firstVoucher['discount_amount'] : 0;
		$secondDiscount = isset($secondVoucher['discount_amount']) ? (int)$secondVoucher['discount_amount'] : 0;
		if ($firstDiscount !== $secondDiscount) {
			return $secondDiscount - $firstDiscount;
		}

		return strcmp((string)$firstVoucher['code'], (string)$secondVoucher['code']);
	});

	return $vouchers;
}

function limitBookingVoucherListItems($vouchers)
{
	return array_slice(sortBookingVoucherListItems($vouchers), 0, 20);
}

function bookingVoucherRelationExists($bookingId, $voucherId)
{
	global $db;

	$safeBookingId = $db->quote($bookingId);
	$safeVoucherId = $db->quote($voucherId);

	return (int)$db->getOne("SELECT COUNT(id)
		FROM bookings_vouchers
		WHERE booking_id = '$safeBookingId'
			AND voucher_id = '$safeVoucherId'
			AND deleted = 0") > 0;
}

function validateBookingVoucherRecord($booking, $voucher, $bookingId, $voucherCode)
{
	if (bookingVoucherRelationExists($bookingId, $voucher['id'])) {
		return ['error' => 1, 'message' => 'Booking đã áp mã giảm giá này'];
	}

	$now = time();
	if (!empty($voucher['start_time']) && strtotime($voucher['start_time']) > $now) {
		return ['error' => 1, 'message' => 'Mã giảm giá chưa đến thời gian sử dụng'];
	}
	if (!empty($voucher['end_time']) && strtotime($voucher['end_time']) < $now) {
		return ['error' => 1, 'message' => 'Mã giảm giá đã hết hạn'];
	}

	$condition = parseBookingVoucherCondition($voucher['condition_voucher']);
	$bookingPhone = normalizeBookingVoucherPhone($booking['phone']);
	if (!empty($condition['for_phone_value']) && normalizeBookingVoucherPhone($condition['for_phone_value']) !== $bookingPhone) {
		return ['error' => 1, 'message' => 'Mã giảm giá không áp dụng cho SĐT của booking'];
	}

	$baseAmount = (int)$booking['total_amount'] + (int)$booking['discount_amount'];
	if (!empty($condition['min_order_value']) && $baseAmount < (int)$condition['min_order_value']) {
		return ['error' => 1, 'message' => 'Booking chưa đạt giá trị đơn tối thiểu'];
	}

	if (!empty($condition['number_of_tickets']) && (int)$booking['total_qty'] < (int)$condition['number_of_tickets']) {
		return ['error' => 1, 'message' => 'Booking chưa đạt số lượng vé tối thiểu'];
	}

	if (!empty($condition['flight_type'])) {
		$bookingFlightScope = getBookingVoucherFlightScope($booking['ticket_type']);
		if ($condition['flight_type'] !== $bookingFlightScope) {
			return ['error' => 1, 'message' => 'Mã giảm giá không áp dụng cho loại chuyến của booking'];
		}
	}

	if (!empty($condition['ticket_type']) && (int)$condition['ticket_type'] !== getBookingVoucherTripType($booking['flight_type'])) {
		return ['error' => 1, 'message' => 'Mã giảm giá không áp dụng cho loại vé của booking'];
	}

	if (!empty($condition['journey'])) {
		$allowedRoutes = [];
		foreach (explode(',', strtoupper((string)$condition['journey'])) as $route) {
			$route = trim(str_replace(' ', '', $route));
			if (strlen($route) >= 7) {
				$allowedRoutes[$route] = $route;
			}
		}

		$bookingRoutes = getBookingVoucherRoutes($booking['id'], $booking['journey']);
		if (empty($bookingRoutes)) {
			return ['error' => 1, 'message' => 'Booking chưa có hành trình để kiểm tra mã giảm giá'];
		}

		foreach ($bookingRoutes as $route) {
			if (!isset($allowedRoutes[$route])) {
				return ['error' => 1, 'message' => 'Mã giảm giá không áp dụng cho hành trình của booking'];
			}
		}
	}

	$discountAmount = calculateBookingVoucherDiscount($voucher, $baseAmount);
	$currentPayable = (int)$booking['total_amount'];
	if ($discountAmount > $currentPayable) {
		$discountAmount = $currentPayable;
	}

	if ($discountAmount <= 0) {
		return ['error' => 1, 'message' => 'Mệnh giá mã giảm giá không hợp lệ'];
	}

	return [
		'error' => 0,
		'message' => 'Mã giảm giá hợp lệ',
		'data' => [
			'booking_id' => $booking['id'],
			'booking_name' => $booking['name'],
			'voucher_id' => $voucher['id'],
			'code' => $voucher['name'],
			'type' => $voucher['type'],
			'campaign_name' => $voucher['campaign_name'],
			'end_time' => $voucher['end_time'],
			'reduce_amount' => (int)$voucher['reduce_amount'],
			'reduce_percent' => (int)$voucher['reduce_percent'],
			'max_discount' => (int)$voucher['max_discount'],
			'discount_type' => (int)$voucher['reduce_percent'] > 0 ? 'percent' : 'amount',
			'discount_amount' => $discountAmount,
			'condition_voucher' => $condition,
		],
	];
}

function validateBookingVoucher($bookingId, $voucherCode)
{
	$bookingId = trim((string)$bookingId);
	$voucherCode = strtoupper(trim((string)$voucherCode));
	if (!isBookingVoucherSugarId($bookingId) || $voucherCode === '') {
		return ['error' => 1, 'message' => 'Dữ liệu không hợp lệ'];
	}

	$booking = findBookingVoucherBooking($bookingId, 'id, name, booking_status, phone, flight_type, ticket_type, journey, total_qty, total_amount, discount_amount');
	if (!$booking) {
		return ['error' => 1, 'message' => 'Không tìm thấy booking'];
	}

	if (!isBookingVoucherEditableStatus($booking['booking_status'])) {
		return ['error' => 1, 'message' => 'Trạng thái booking hiện tại không cho phép áp mã giảm giá'];
	}

	$vouchers = findPendingSingleBookingVouchersByCode($voucherCode);
	if (empty($vouchers)) {
		return ['error' => 1, 'message' => 'Không tìm thấy mã giảm giá'];
	}

	$firstInvalidResult = null;
	$bestVoucher = null;
	$bestResult = null;
	foreach ($vouchers as $voucher) {
		$result = validateBookingVoucherRecord($booking, $voucher, $bookingId, $voucherCode);
		if (empty($result['error'])) {
			if (isBetterBookingVoucherCandidate($voucher, $result, $bestVoucher, $bestResult)) {
				$bestVoucher = $voucher;
				$bestResult = $result;
			}
			continue;
		}

		if ($firstInvalidResult === null) {
			$firstInvalidResult = $result;
		}
	}

	return $bestResult ?: ($firstInvalidResult ?: ['error' => 1, 'message' => 'Mã giảm giá không hợp lệ']);
}

function validateBookingVoucherById($bookingId, $voucherId)
{
	$bookingId = trim((string)$bookingId);
	$voucherId = trim((string)$voucherId);
	if (!isBookingVoucherSugarId($bookingId) || !isBookingVoucherSugarId($voucherId)) {
		return ['error' => 1, 'message' => 'Dữ liệu không hợp lệ'];
	}

	$booking = findBookingVoucherBooking($bookingId, 'id, name, booking_status, phone, flight_type, ticket_type, journey, total_qty, total_amount, discount_amount');
	if (!$booking) {
		return ['error' => 1, 'message' => 'Không tìm thấy booking'];
	}

	if (!isBookingVoucherEditableStatus($booking['booking_status'])) {
		return ['error' => 1, 'message' => 'Trạng thái booking hiện tại không cho phép áp mã giảm giá'];
	}

	$voucher = findPendingSingleBookingVoucherById($voucherId);
	if (!$voucher) {
		return ['error' => 1, 'message' => 'Không tìm thấy mã giảm giá'];
	}

	return validateBookingVoucherRecord($booking, $voucher, $bookingId, $voucher['name']);
}

function listBookingVouchers($bookingId)
{
	global $db;

	$bookingId = trim((string)$bookingId);
	if (!isBookingVoucherSugarId($bookingId)) {
		return ['error' => 1, 'message' => 'Dữ liệu không hợp lệ'];
	}

	$booking = findBookingVoucherBooking($bookingId, 'id, name, booking_status, phone, flight_type, ticket_type, journey, total_qty, total_amount, discount_amount');
	if (!$booking) {
		return ['error' => 1, 'message' => 'Không tìm thấy booking'];
	}

	$sql = "SELECT v.id, v.name, v.type, v.campaign_name, v.start_time, v.end_time,
				v.reduce_amount, v.reduce_percent, v.max_discount, v.condition_voucher, v.date_entered
			FROM ec_vouchers v
			WHERE v.deleted = 0
				AND v.type IN ('single', 'group')
				AND v.status = 'pending'
			ORDER BY v.date_entered DESC
			LIMIT 100";
	$res = $db->query($sql);

	$voucherEntries = [];
	$bestVoucherByCode = [];
	$bestResultByCode = [];

	while ($row = $db->fetchByAssoc($res)) {
		$code = $row['name'];

		if ($row['type'] === 'group') {
			$voucherEntries[] = ['voucher' => $row, 'result' => null, 'is_group' => true];
			continue;
		}

		if (!isBookingVoucherEditableStatus($booking['booking_status'])) {
			$voucherEntries[] = ['voucher' => $row, 'result' => null, 'is_group' => false];
			continue;
		}

		$result = validateBookingVoucherRecord($booking, $row, $bookingId, $code);
		$voucherEntries[] = ['voucher' => $row, 'result' => $result, 'is_group' => false];

		if (empty($result['error'])) {
			$bestVoucher = isset($bestVoucherByCode[$code]) ? $bestVoucherByCode[$code] : null;
			$bestResult = isset($bestResultByCode[$code]) ? $bestResultByCode[$code] : null;
			if (isBetterBookingVoucherCandidate($row, $result, $bestVoucher, $bestResult)) {
				$bestVoucherByCode[$code] = $row;
				$bestResultByCode[$code] = $result;
			}
		}
	}

	$vouchers = [];
	foreach ($voucherEntries as $entry) {
		$code = $entry['voucher']['name'];
		$bestId = isset($bestVoucherByCode[$code]) ? $bestVoucherByCode[$code]['id'] : null;

		if ($entry['is_group']) {
			$item = buildDisabledGroupBookingVoucherListItem($entry['voucher']);
		} else {
			$result = $entry['result'] ?: ['error' => 1, 'message' => 'Không áp dụng được'];
			$item = buildBookingVoucherListItem($entry['voucher'], $result);
		}

		$item['is_best_in_group'] = ($bestId !== null && $entry['voucher']['id'] === $bestId);
		$vouchers[] = $item;
	}

	return [
		'error' => 0,
		'message' => 'Success',
		'data' => limitBookingVoucherListItems($vouchers),
	];
}

function removeBookingVoucher($bookingId, $voucherId)
{
	global $db;

	$bookingId = trim((string)$bookingId);
	$voucherId = trim((string)$voucherId);
	if (!isBookingVoucherSugarId($bookingId) || !isBookingVoucherSugarId($voucherId)) {
		return ['error' => 1, 'message' => 'Dữ liệu không hợp lệ'];
	}

	$safeBookingId = $db->quote($bookingId);
	$safeVoucherId = $db->quote($voucherId);

	$booking = findBookingVoucherBooking($bookingId, 'id, booking_status, discount_amount');
	if (!$booking) {
		return ['error' => 1, 'message' => 'Không tìm thấy booking'];
	}

	if (!isBookingVoucherEditableStatus($booking['booking_status'])) {
		return ['error' => 1, 'message' => 'Trạng thái booking hiện tại không cho phép bỏ mã giảm giá'];
	}

	$relationSql = "SELECT bv.discount_amount, v.type
		FROM bookings_vouchers bv
		INNER JOIN ec_vouchers v ON v.id = bv.voucher_id AND v.deleted = 0
		WHERE bv.booking_id = '$safeBookingId'
			AND bv.voucher_id = '$safeVoucherId'
			AND bv.deleted = 0
		LIMIT 1";
	$relation = $db->fetchByAssoc($db->query($relationSql));
	if (!$relation) {
		return ['error' => 1, 'message' => 'Booking chưa áp mã giảm giá này'];
	}

	$discountAmount = min((int)$relation['discount_amount'], (int)$booking['discount_amount']);
	if ($discountAmount <= 0) {
		return ['error' => 1, 'message' => 'Số tiền giảm giá không hợp lệ'];
	}

	$resBooking = $db->query("UPDATE ec_flight_bookings
		SET discount_amount = discount_amount - $discountAmount,
			total_amount = total_amount + $discountAmount
		WHERE id = '$safeBookingId'
			AND deleted = 0
			AND discount_amount >= $discountAmount");
	if ($resBooking === false) {
		return ['error' => 1, 'message' => 'Không cập nhật được tổng tiền booking'];
	}

	$db->query("DELETE FROM bookings_vouchers
			WHERE booking_id = '$safeBookingId'
				AND voucher_id = '$safeVoucherId'
				AND deleted = 1");

	$resRelation = $db->query("UPDATE bookings_vouchers
			SET deleted = 1,
				date_modified = NOW()
			WHERE booking_id = '$safeBookingId'
				AND voucher_id = '$safeVoucherId'
				AND deleted = 0");
	if ($resRelation === false) {
		$db->query("UPDATE ec_flight_bookings
			SET discount_amount = discount_amount + $discountAmount,
				total_amount = total_amount - $discountAmount
			WHERE id = '$safeBookingId'
				AND deleted = 0");
		return ['error' => 1, 'message' => 'Không bỏ được mã giảm giá khỏi booking'];
	}

	$db->query("UPDATE ec_vouchers
		SET status = 'pending'
		WHERE id = '$safeVoucherId'
			AND deleted = 0
			AND status = 'done'");

	return [
		'error' => 0,
		'message' => 'Đã bỏ mã giảm giá khỏi booking',
		'data' => [
			'booking_id' => $bookingId,
			'voucher_id' => $voucherId,
			'discount_amount' => $discountAmount,
		],
	];
}

function applyBookingVoucher($bookingId, $voucherId = '', $voucherCode = '')
{
	global $db;

	if ($voucherId !== '') {
		$result = validateBookingVoucherById($bookingId, $voucherId);
	} else {
		$result = validateBookingVoucher($bookingId, $voucherCode);
	}

	if (!empty($result['error'])) {
		return $result;
	}

	$data = $result['data'];
	$safeBookingId = $db->quote($data['booking_id']);
	$safeVoucherId = $db->quote($data['voucher_id']);
	$discountAmount = (int)$data['discount_amount'];
	$relationId = create_guid();

	$deletedRelationId = $db->getOne("SELECT id
		FROM bookings_vouchers
		WHERE booking_id = '$safeBookingId'
			AND voucher_id = '$safeVoucherId'
			AND deleted = 1
		LIMIT 1");

	if (!empty($deletedRelationId)) {
		$safeDeletedRelationId = $db->quote($deletedRelationId);
		$insertSql = "UPDATE bookings_vouchers
			SET discount_amount = $discountAmount,
				date_modified = NOW(),
				deleted = 0
			WHERE id = '$safeDeletedRelationId'";
	} else {
		$insertSql = "INSERT INTO bookings_vouchers (id, booking_id, voucher_id, discount_amount, deleted, date_modified)
			VALUES ('$relationId', '$safeBookingId', '$safeVoucherId', $discountAmount, 0, NOW())
			ON DUPLICATE KEY UPDATE discount_amount = $discountAmount, date_modified = NOW(), deleted = 0";
	}
	$resRelation = $db->query($insertSql);
	if ($resRelation === false) {
		return ['error' => 1, 'message' => 'Không lưu được mã giảm giá cho booking'];
	}

	$updateBookingSql = "UPDATE ec_flight_bookings
		SET discount_amount = discount_amount + $discountAmount,
			total_amount = total_amount - $discountAmount
		WHERE id = '$safeBookingId' AND deleted = 0";
	$resBooking = $db->query($updateBookingSql);
	if ($resBooking === false) {
		$db->query("UPDATE bookings_vouchers
			SET deleted = 1,
				date_modified = NOW()
			WHERE booking_id = '$safeBookingId'
				AND voucher_id = '$safeVoucherId'
				AND deleted = 0");
		return ['error' => 1, 'message' => 'Không cập nhật được tổng tiền booking'];
	}

	$db->query("UPDATE ec_vouchers SET status = 'done' WHERE id = '$safeVoucherId' AND deleted = 0");

	return $result;
}

function getSelectedBookingVoucherIdsFromPost()
{
	$rawVoucherIds = isset($_POST['voucher_ids']) ? $_POST['voucher_ids'] : [];
	if (empty($rawVoucherIds) && isset($_POST['voucher_ids[]'])) {
		$rawVoucherIds = $_POST['voucher_ids[]'];
	}

	if (is_string($rawVoucherIds)) {
		$decodedVoucherIds = json_decode($rawVoucherIds, true);
		$rawVoucherIds = is_array($decodedVoucherIds) ? $decodedVoucherIds : [$rawVoucherIds];
	}

	$voucherIds = [];
	foreach ((array)$rawVoucherIds as $voucherId) {
		$voucherId = trim((string)$voucherId);
		if (isBookingVoucherSugarId($voucherId)) {
			$voucherIds[$voucherId] = $voucherId;
		}
	}

	return $voucherIds;
}

function areBookingVoucherIdsSame($firstVoucherIds, $secondVoucherIds)
{
	ksort($firstVoucherIds);
	ksort($secondVoucherIds);

	return array_values($firstVoucherIds) === array_values($secondVoucherIds);
}

function saveBookingVouchers($bookingId)
{
	global $db;

	$bookingId = trim((string)$bookingId);
	if (!isBookingVoucherSugarId($bookingId)) {
		return ['error' => 1, 'message' => 'Dữ liệu không hợp lệ'];
	}

	$booking = findBookingVoucherBooking($bookingId, 'id, booking_status');
	if (!$booking) {
		return ['error' => 1, 'message' => 'Không tìm thấy booking'];
	}

	if (!isBookingVoucherEditableStatus($booking['booking_status'])) {
		return ['error' => 1, 'message' => 'Trạng thái booking hiện tại không cho phép cập nhật mã giảm giá'];
	}

	$currentVoucherIds = getAppliedBookingVoucherIds($bookingId);
	$selectedVoucherIds = getSelectedBookingVoucherIdsFromPost();
	if (empty($currentVoucherIds) && empty($selectedVoucherIds)) {
		return ['error' => 1, 'message' => 'Chưa chọn mã giảm giá hợp lệ'];
	}

	$db->query('START TRANSACTION');
	foreach ($currentVoucherIds as $voucherId) {
		if (!isset($selectedVoucherIds[$voucherId])) {
			$result = removeBookingVoucher($bookingId, $voucherId);
			if (!empty($result['error'])) {
				$db->query('ROLLBACK');
				return $result;
			}
		}
	}

	foreach ($selectedVoucherIds as $voucherId) {
		if (!isset($currentVoucherIds[$voucherId])) {
			$result = applyBookingVoucher($bookingId, $voucherId);
			if (!empty($result['error'])) {
				$db->query('ROLLBACK');
				return $result;
			}
		}
	}

	$updatedVoucherIds = getAppliedBookingVoucherIds($bookingId);
	if (!areBookingVoucherIdsSame($updatedVoucherIds, $selectedVoucherIds)) {
		$db->query('ROLLBACK');
		return ['error' => 1, 'message' => 'Chưa cập nhật được mã giảm giá'];
	}

	$db->query('COMMIT');

	return [
		'error' => 0,
		'message' => 'Cập nhật mã giảm giá thành công',
		'data' => getBookingVoucherUpdatedData($bookingId),
	];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$action = getBookingVoucherPost('action');

	if ($action == 'list_booking_vouchers') {
		voucherJsonResponse(listBookingVouchers(getBookingVoucherPost('booking_id')));
	}

	if ($action == 'validate_booking_voucher') {
		voucherJsonResponse(validateBookingVoucher(getBookingVoucherPost('booking_id'), getBookingVoucherPost('voucher_code')));
	}

	if ($action == 'apply_booking_voucher') {
		voucherJsonResponse(applyBookingVoucher(getBookingVoucherPost('booking_id'), getBookingVoucherPost('voucher_id'), getBookingVoucherPost('voucher_code')));
	}

	if ($action == 'remove_booking_voucher') {
		voucherJsonResponse(removeBookingVoucher(getBookingVoucherPost('booking_id'), getBookingVoucherPost('voucher_id')));
	}

	if ($action == 'save_booking_vouchers') {
		voucherJsonResponse(saveBookingVouchers(getBookingVoucherPost('booking_id')));
	}

	if ($action == 'release_to_website') {
		$record_id = isset($_POST["record_id"]) ? trim($_POST["record_id"]) : '';

		if (empty($record_id) && strlen($record_id) != 36) {
			echo json_encode([
				'error' => 1,
				'message' => 'Dữ liệu không hợp lệ',
				'record_id' => $record_id
			]);
			exit();
		}

		$voucher = new EC_Vouchers();
		$voucher->retrieve($record_id);
		if ($voucher->id == $record_id) {
			$voucher_info = [
				'id' 			=> $record_id,
				'voucher_code' 	=> $voucher->name,
				'status' 		=> 'pending',
				'campaign_name' => $voucher->campaign_name,
				'campaign_id' 	=> $voucher->campaign_id,
				'max_discount' 	=> (int)$voucher->max_discount,
				'quantity' 		=> (int)$voucher->quantity,
				'start_time' 	=> date('Y-m-d H:i:00', strtotime($voucher->start_time) - 7*3600),
				'end_time' 		=> date('Y-m-d H:i:00', strtotime($voucher->end_time) - 7*3600),
				'condition_voucher' => html_entity_decode(trim($voucher->condition_voucher)),
				'is_hidden' => $voucher->is_hidden
			];
			if($voucher->reduce_amount > 0) $voucher_info['reduce_amount'] = (int)$voucher->reduce_amount;
			elseif($voucher->reduce_percent > 0) $voucher_info['reduce_percent'] = (int)$voucher->reduce_percent;

			$json_result = $voucher->uploadWebsite($voucher->website, $voucher_info);
			$arr_result = json_decode($json_result, true);
			if(isset($arr_result['error']) && $arr_result['error'] == 0) {
				$voucher->status = 'pending';
				$voucher->save2();
			}
			echo $json_result;
			exit();
		}

		echo json_encode([
			'error' => 1,
			'message' => 'Dữ liệu không hợp lệ',
			'record_id' => $record_id
		]);
		exit();
	}
}
