<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewbksalereport extends SugarView {
	/** @var Sugar_Smarty **/
	public $smartyObj;

	// ===== Cấu hình thưởng (Phase 1: chỉ thưởng trực tiếp) =====
	const BONUS_MIN_SERVICE_FEE = 110000; // Phí dịch vụ tối thiểu/vé để đủ điều kiện thưởng (nội địa)
	const BONUS_THRESHOLD_FEE   = 120000; // Mốc tính phần dôi ra (surplus)
	const BONUS_DIRECT_RATE     = 0.7;    // Booker nhận 70% (30% còn lại là quỹ gián tiếp - Phase 2)
	const BONUS_OA_MULTIPLIER   = 1.0;    // Phase 1: chưa có trường "Đã vào OA", tạm tính 1.0
	const BONUS_EFFECTIVE_DATE  = '2026-07-01'; // Áp dụng cho vé xuất từ 01-07-2026

	function display() {
		$this->smartyObj = new Sugar_Smarty();
		$this->populateContent();
		$this->smartyObj->display('modules/EC_Flight_Bookings/tpls/view_bksalereport.tpl');
	}

	public function populateContent() {
		global $current_user;

		$current_year = date('Y');
		switch (ceil(date('n') / 3)) {
			case 1:
				$cq_from_date 	= "01-01-$current_year";
				$cq_to_date 	= "31-03-$current_year";
				$lq_from_date 	= "01-01-" . date("Y", strtotime("- 1 year"));
				$lq_to_date 	= "31-03-" . date("Y", strtotime("- 1 year"));
				break;
			case 2:
				$cq_from_date 	= "01-04-$current_year";
				$cq_to_date 	= "30-06-$current_year";
				$lq_from_date 	= "01-01-$current_year";
				$lq_to_date 	= "31-03-$current_year";
				break;
			case 3:
				$cq_from_date 	= "01-07-$current_year";
				$cq_to_date 	= "30-09-$current_year";
				$lq_from_date 	= "01-04-$current_year";
				$lq_to_date 	= "30-06-$current_year";
				break;
			case 4:
				$cq_from_date 	= "01-10-$current_year";
				$cq_to_date 	= "31-12-$current_year";
				$lq_from_date 	= "01-07-$current_year";
				$lq_to_date 	= "30-09-$current_year";
				break;
			default:
				$cq_from_date 	= '';
				$cq_to_date 	= '';
				$lq_from_date 	= '';
				$lq_to_date 	= '';
				break;
		}

		$report_term_list = '<option ' . (isset($_POST['report_term_list']) && (string)$_POST['report_term_list'] === 'today' ? 'selected' : '') . ' value="today" data-fromdate="' . date('d-m-Y') . '" data-todate="' . date('d-m-Y') . '" data-term="' . date('m') . '" data-year="' . date('Y') . '">Hôm nay</option>';
		$report_term_list .= '<option ' . (isset($_POST['report_term_list']) && (string)$_POST['report_term_list'] === 'yesterday' ? 'selected' : '') . ' value="yesterday" data-fromdate="' . date('d-m-Y', strtotime("-1 day")) . '" data-todate="' . date('d-m-Y', strtotime("-1 day")) . '" data-term="' . date('m', strtotime("-1 day")) . '" data-year="' . date('Y', strtotime("-1 day")) . '">Hôm qua</option>';
		$report_term_list .= '<option ' . (isset($_POST['report_term_list']) && (string)$_POST['report_term_list'] === 'this_week' ? 'selected' : '') . ' value="this_week" data-fromdate="' . date('d-m-Y', strtotime("monday this week")) . '" data-todate="' . date('d-m-Y', strtotime("sunday this week")) . '" data-term="' . date('m', strtotime("sunday this week")) . '" data-year="' . date('Y', strtotime("sunday this week")) . '">Tuần này</option>';
		$report_term_list .= '<option ' . (isset($_POST['report_term_list']) && (string)$_POST['report_term_list'] === 'previous_week' ? 'selected' : '') . ' value="previous_week" data-fromdate="' . date('d-m-Y', strtotime("monday previous week")) . '" data-todate="' . date('d-m-Y', strtotime("sunday previous week")) . '" data-term="' . date('m', strtotime("sunday previous week")) . '" data-year="' . date('Y', strtotime("sunday previous week")) . '">Tuần trước</option>';
		$report_term_list .= '<option ' . (isset($_POST['report_term_list']) && (string)$_POST['report_term_list'] === 'this_month' ? 'selected' : '') . ' value="this_month" data-fromdate="' . date('d-m-Y', strtotime("first day of this month")) . '" data-todate="' . date('d-m-Y', strtotime("last day of this month")) . '" data-term="' . date('m', strtotime("last day of this month")) . '" data-year="' . date('Y', strtotime("last day of this month")) . '">Tháng này</option>';
		$report_term_list .= '<option ' . (isset($_POST['report_term_list']) && (string)$_POST['report_term_list'] === 'previous_month' ? 'selected' : '') . ' value="previous_month" data-fromdate="' . date('d-m-Y', strtotime("first day of previous month")) . '" data-todate="' . date('d-m-Y', strtotime("last day of previous month")) . '" data-term="' . date('m', strtotime("last day of previous month")) . '" data-year="' . date('Y', strtotime("last day of previous month")) . '">Tháng trước</option>';
		$report_term_list .= '<option ' . (isset($_POST['report_term_list']) && (string)$_POST['report_term_list'] === 'this_quater' ? 'selected' : '') . ' value="this_quater" data-fromdate="' . date('d-m-Y', strtotime($cq_from_date)) . '" data-todate="' . date('d-m-Y', strtotime($cq_to_date)) . '" data-term="' . date('m', strtotime($cq_from_date)) . '" data-year="' . date('Y', strtotime($cq_from_date)) . '">Quý này</option>';
		$report_term_list .= '<option ' . (isset($_POST['report_term_list']) && (string)$_POST['report_term_list'] === 'previous_quater' ? 'selected' : '') . ' value="previous_quater" data-fromdate="' . date('d-m-Y', strtotime($lq_from_date)) . '" data-todate="' . date('d-m-Y', strtotime($lq_to_date)) . '" data-term="' . date('m', strtotime($lq_from_date)) . '" data-year="' . date('Y', strtotime($lq_from_date)) . '">Quý trước</option>';
		$this->smartyObj->assign('REPORT_TERM_LIST', $report_term_list);

		// From date
		$from_date = (empty($_REQUEST['from_date'])) ? date('d-m-Y') : $_REQUEST['from_date'];
		$this->smartyObj->assign('FROM_DATE', $from_date);

		// To date
		$to_date = (empty($_REQUEST['to_date'])) ? date('d-m-Y') : $_REQUEST['to_date'];
		$this->smartyObj->assign('TO_DATE', $to_date);

		if (!isset($_REQUEST['for'])) {
			if (is_admin($current_user) || $current_user->title == 'QuanLy') {
				$ds = $this->getDSBooking($from_date, $to_date);
			} else {
				$ds = $this->getDSBooking($from_date, $to_date, $current_user->id);
			}
			$detail = 0;
		}
		else {
			$ds = $this->getDetailDSBooking($from_date, $to_date, $_REQUEST['user']);
			$detail = 1;

			$u = new User;
			$u->retrieve($_REQUEST['user']);
			$this->smartyObj->assign('EMPOYEE_NAME', trim("{$u->last_name} {$u->first_name}"));
		}

		$this->smartyObj->assign('DETAIL', $detail);
		$this->smartyObj->assign('DOANHSO', $ds);
	}

	// Doanh số booker
	public function getDSBooking(string $from_date, string $to_date, string $assigned_user_id = '') {
		global $current_user;
		$show_bonus = ($current_user->id === '1');

		$user_list = get_user_array(true, '', '', true);

		$html = '
			<thead>
				<th width="5%">STT</th>
				<th width="20%">Họ và tên</th>
				<th width="5%">Booking</th>
				<th width="5%">Vé</th>
				<th width="10%">Doanh thu</th>
				<th width="10%">Giá mua</th>
				<th width="10%">Doanh số</th>'
				. ($show_bonus ? '<th width="10%">Thưởng</th>' : '') . '
			</thead><tbody>';

		// doanh số bao gồm doanh số bán vé, đổi ngày bay, hành lý và hoàn vé
		$data_revenue = calculateRevenueOfDate($from_date, $to_date);

		// Thông tin phục vụ tính thưởng (nguồn KH, quốc tế/nội địa, ngày bay...) cho các booking vé
		$bonus_info = $show_bonus ? $this->getBookingBonusInfo($this->collectBookingIds($data_revenue)) : [];

		$total_bk_qty = $total_ticket_qty = 0;
		$total_revenue_amount = 0;
		$total_bought_amount = 0;
		$total_profit_amount = 0;
		$total_bonus_amount = 0;
		$i = 1;

		// 1. Gom dữ liệu
		$grouped = [];

		if (!empty($data_revenue) && $data_revenue['count'] > 0) {
			foreach ($data_revenue['details'] as $row) {
				$uid = $row['user_id'] ?: 'empty';

				// Phân quyền: nhân viên chỉ gom dữ liệu của mình
				if (!empty($assigned_user_id) && $uid !== $assigned_user_id) {
					continue;
				}

				if (!isset($grouped[$uid])) {
					$grouped[$uid] = [
						'user_id'    => $uid !== 'empty' ? $uid : null,
						'full_name'  => $uid !== 'empty' ? $user_list[$uid] : '(Trống)',
						'title'      => $row['title'],
						'total_bk'   => 0,
						'ticket_qty' => 0,
						'revenue_amount'    => 0,
						'bought_amount'    => 0,
						'profit_amount'    => 0,
						'bonus_amount'    => 0,
					];
				}

				// Booking
				if ($row['parent_type'] === 'EC_Flight_Bookings') {
					$grouped[$uid]['total_bk'] += 1;
				}

				$grouped[$uid]['ticket_qty']     += (int)$row['total_quantity'];
				$grouped[$uid]['revenue_amount'] += (int)$row['subtotal_amount'];
				$grouped[$uid]['bought_amount']  += (int)$row['total_bought_price'];
				$grouped[$uid]['profit_amount']  += (int)$row['profit_amount'];
				$grouped[$uid]['bonus_amount']   += $show_bonus ? $this->calculateDirectBonus($row, $bonus_info) : 0;
			}

			// 2. SORT ở đây
			uasort($grouped, function ($a, $b) {
				return $b['profit_amount'] <=> $a['profit_amount'];
			});

			// 3. Loop render
			foreach ($grouped as $row) {
				$html .= '<tr>
					<td class="text-center">' . $i . '</td>
					<td>
						<a href="index.php?module=EC_Flight_Bookings&action=bksalereport&for=showDetail&user=' . $row['user_id'] . '&from_date=' . $from_date . '&to_date=' . $to_date . '" target="_blank">
							' . $row['full_name'] . '
						</a>
					</td>
					<td class="text-center">' . format_number($row['total_bk']) . '</td>
					<td class="text-center">' . format_number($row['ticket_qty']) . '</td>
					<td class="text-end">' . format_number($row['revenue_amount']) . '</td>
					<td class="text-end">' . format_number($row['bought_amount']) . '</td>
					<td class="text-end">' . format_number($row['profit_amount']) . '</td>'
					. ($show_bonus ? '<td class="text-end">' . format_number($row['bonus_amount']) . '</td>' : '') . '
				</tr>';

				$i++;

				$total_bk_qty     += $row['total_bk'];
				$total_ticket_qty += $row['ticket_qty'];
				$total_revenue_amount    += $row['revenue_amount'];
				$total_bought_amount    += $row['bought_amount'];
				$total_profit_amount    += $row['profit_amount'];
				$total_bonus_amount    += $row['bonus_amount'];
			}
		}

		$html .= '<tr class="last-row footer-tr">
				<td></td>
				<td>Tổng cộng</td>
				<td class="text-center">' . format_number($total_bk_qty) . '</td>
				<td class="text-center">' . format_number($total_ticket_qty) . '</td>
				<td class="text-end">' . format_number($total_revenue_amount) . '</td>
				<td class="text-end">' . format_number($total_bought_amount) . '</td>
				<td class="text-end">' . format_number($total_profit_amount) . '</td>'
				. ($show_bonus ? '<td class="text-end">' . format_number($total_bonus_amount) . '</td>' : '') . '
			</tr>
		</tbody>';

		return $html;
	}

	// Chi tiết doanh số
	public function getDetailDSBooking($from_date, $to_date, $assigned_user_id) {
		global $current_user;
		// Cột "Thưởng" đang phát triển: chỉ hiển thị cho tôi (user id = 1)
		$show_bonus = ($current_user->id === '1');

		$html = '<thead>
					<th>STT</th>
					<th>Ngày chứng từ</th>
					<th>Booking</th>
					<th>Ngày xuất vé</th>
					<th>Số vé</th>
					<th>Doanh số</th>'
					. ($show_bonus ? '<th>Thưởng</th>' : '') . '
				</thead><tbody>';

		$data_revenue = calculateRevenueOfDate($from_date, $to_date);

		// Thông tin phục vụ tính thưởng cho các booking vé
		$bonus_info = $show_bonus ? $this->getBookingBonusInfo($this->collectBookingIds($data_revenue)) : [];

		$i 			= 1;
		$total_qty  = $total_ds_booking = $total_bonus = 0;

		if (!empty($data_revenue) && $data_revenue['count'] > 0) {

			usort($data_revenue['details'], function ($a, $b) {
				return strtotime($a['voucher_date']) <=> strtotime($b['voucher_date']);
			});

			foreach ($data_revenue['details'] as $row) {
				$uid = $row['user_id'] ?: 'empty';

				if ($uid === $assigned_user_id) {
					$bonus = $show_bonus ? $this->calculateDirectBonus($row, $bonus_info) : 0;
					$html .= '<tr>
						<td class="text-center fw-semibold">' . $i . '</td>
						<td class="text-center">' . $row['voucher_date'] . '</td>
						<td class="text-center">
						<a target="_blank" href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '">' . $row['booking_name'] . '</a>
						' . (!empty($row['parent_name'] && $row['parent_type'] != 'EC_Flight_Bookings') ? '/ <a target="_blank" href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['voucher_id'] . '">' . $row['parent_name'] . '</a>' : '') . '
						</td>
						<td class="text-center">' . $row['date_ticket_issue'] . '</td>
						<td class="text-center">' . $row['total_quantity'] . '</td>
						<td class="text-end">' . format_number($row['profit_amount']) . '</td>'
						. ($show_bonus ? '<td class="text-end">' . format_number($bonus) . '</td>' : '') . '
					</tr>';
					$i++;

					$total_qty += $row['total_quantity'];
					$total_ds_booking += $row['profit_amount'];
					$total_bonus += $bonus;
				}
			}
		}

		$html .= '
				<tr class="last-row footer-tr">
			 		<td></td>
			 		<td></td>
			 		<td></td>
			 		<td></td>
			 		<td class="text-center">' . format_number($total_qty) . '</td>
			 		<td class="text-end">' . format_number($total_ds_booking) . '</td>'
			 		. ($show_bonus ? '<td class="text-end">' . format_number($total_bonus) . '</td>' : '') . '
			 	</tr></tbody>';

		return $html;
	}

	/**
	 * Lấy danh sách booking_id (vé) từ kết quả doanh số để tra cứu thông tin tính thưởng.
	 */
	private function collectBookingIds($data_revenue) {
		$ids = [];
		if (!empty($data_revenue) && !empty($data_revenue['count'])) {
			foreach ($data_revenue['details'] as $row) {
				if ($row['parent_type'] === 'EC_Flight_Bookings' && !empty($row['booking_id'])) {
					$ids[$row['booking_id']] = $row['booking_id'];
				}
			}
		}
		return array_values($ids);
	}

	/**
	 * Truy vấn thông tin phục vụ tính thưởng cho từng booking:
	 * nguồn khách, loại vé (nội địa/quốc tế), ngày xuất vé, ngày bay cuối cùng và các sân bay đến.
	 *
	 * @return array map booking_id => ['customer_source','ticket_type','date_ticket_issue','flight_departure_date','arrivals'[]]
	 */
	private function getBookingBonusInfo(array $booking_ids) {
		global $db;

		$info = [];
		if (empty($booking_ids)) {
			return $info;
		}

		$quoted = array_map(function ($id) use ($db) {
			return "'" . $db->quote($id) . "'";
		}, $booking_ids);
		$in_clause = implode(',', $quoted);

		$sql = "SELECT bk.id
					, bk.customer_source
					, bk.ticket_type
					, bk.date_ticket_issue
					, (
						SELECT MAX(iti.departure_date)
						FROM ec_booking_itineraries iti
						WHERE iti.booking_id = bk.id AND iti.deleted = 0
					) AS flight_departure_date
				FROM ec_flight_bookings bk
				WHERE bk.id IN ($in_clause) AND bk.deleted = 0";

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$row['arrivals'] = [];
			$info[$row['id']] = $row;
		}

		// Sân bay đến (để phân vùng vé quốc tế)
		$sql_iti = "SELECT booking_id, arrival
					FROM ec_booking_itineraries
					WHERE booking_id IN ($in_clause) AND deleted = 0
						AND arrival IS NOT NULL AND arrival != ''";

		$res_iti = $db->query($sql_iti);
		while ($row = $db->fetchByAssoc($res_iti)) {
			if (isset($info[$row['booking_id']])) {
				$info[$row['booking_id']]['arrivals'][] = strtoupper(trim($row['arrival']));
			}
		}

		return $info;
	}

	/**
	 * Tính thưởng trực tiếp (70%) cho 1 dòng doanh số.
	 * Phase 1: chỉ tính cho booking vé (EC_Flight_Bookings); chưa xử lý hoàn vé / dịch vụ cộng thêm.
	 */
	private function calculateDirectBonus($row, array $bonus_info) {
		// Chỉ tính cho booking vé
		if ($row['parent_type'] !== 'EC_Flight_Bookings') {
			return 0;
		}

		$booking_id = $row['booking_id'] ?? '';
		$info = $bonus_info[$booking_id] ?? null;
		if (empty($info)) {
			return 0;
		}

		$qty = (int)$row['total_quantity'];
		if ($qty <= 0) {
			return 0;
		}

		// Điều kiện: áp dụng cho vé xuất từ ngày hiệu lực
		$issue_date = substr((string)($info['date_ticket_issue'] ?? ''), 0, 10);
		if ($issue_date === '' || $issue_date < self::BONUS_EFFECTIVE_DATE) {
			return 0;
		}

		// Điều kiện: chỉ tính khi chuyến bay đã khởi hành (ngày bay đã qua)
		$dep = $info['flight_departure_date'] ?? '';
		if (empty($dep) || strtotime($dep . ' UTC') === false || strtotime($dep . ' UTC') >= time()) {
			return 0;
		}

		// Phí dịch vụ trung bình mỗi vé = doanh số / số vé
		$p_dv = $row['profit_amount'] / $qty;

		if ((int)$info['ticket_type'] === 2) {
			// Vé quốc tế: thưởng cố định theo vùng, không phụ thuộc surplus
			$t_per_ticket = $this->getIntlBonusRate($info['arrivals']);
		} else {
			// Vé nội địa: phải đạt phí dịch vụ tối thiểu
			if ($p_dv < self::BONUS_MIN_SERVICE_FEE) {
				return 0;
			}

			$surplus = max(0, $p_dv - self::BONUS_THRESHOLD_FEE);

			switch ($this->getSourceCategory($info['customer_source'])) {
				case 'reference':
					$t_per_ticket = 22000 + 0.30 * $surplus;
					break;
				case 'personal':
					$t_per_ticket = 33000 + 0.40 * $surplus;
					break;
				case 'system':
				default:
					$t_per_ticket = 5500 + 0.10 * $surplus;
					break;
			}

			$t_per_ticket *= self::BONUS_OA_MULTIPLIER;
		}

		$total_bonus = $t_per_ticket * $qty;

		// Booker nhận 70% (phần trực tiếp)
		return (int)round($total_bonus * self::BONUS_DIRECT_RATE);
	}

	/**
	 * Phân loại nguồn khách thành 3 nhóm thưởng.
	 * Mặc định (trống/không xác định) -> 'system' (mức thưởng thấp nhất - an toàn).
	 */
	private function getSourceCategory($customer_source) {
		$customer_source = (string)$customer_source;

		$system    = ['system_ads', 'system_old', 'receipt_voucher'];
		$reference = ['is_reference', 'care'];
		$personal  = ['new', 'agent'];

		if (in_array($customer_source, $reference, true)) {
			return 'reference';
		}
		if (in_array($customer_source, $personal, true)) {
			return 'personal';
		}
		return 'system';
	}

	/**
	 * Mức thưởng cố định/vé cho vé quốc tế theo vùng đến.
	 * Nếu hành trình tới nhiều vùng -> lấy mức cao nhất.
	 */
	private function getIntlBonusRate(array $arrivals) {
		global $app_list_strings;

		$se_asia  = $app_list_strings['southeast_asia_airport_list'] ?? [];
		$ne_asia  = $app_list_strings['northeast_asia_airport_list'] ?? [];
		$domestic = $app_list_strings['domestic_airport_list'] ?? [];

		$rate = 0;
		foreach ($arrivals as $code) {
			if (isset($domestic[$code])) {
				continue; // bỏ qua chặng nội địa (vé khứ hồi)
			}
			if (isset($se_asia[$code])) {
				$rate = max($rate, 300000); // Đông Nam Á
			} elseif (isset($ne_asia[$code])) {
				$rate = max($rate, 350000); // Châu Á khác
			} else {
				$rate = max($rate, 400000); // Vùng khác (Âu/Mỹ/Úc/Phi...)
			}
		}

		// Không xác định được vùng đến quốc tế -> mặc định vùng khác
		return $rate ?: 400000;
	}
}
