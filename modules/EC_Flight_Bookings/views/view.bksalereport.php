<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewbksalereport extends SugarView {
	public $smartyObj;

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
		$user_list = get_user_array(true, '', '', true);

		// Revenue: Ticket sales, Change flight date, Baggage, Refund
		$data_revenue = calculateRevenueOfDate($from_date, $to_date);

		$html = '';
		$total_bk_qty = $total_ticket_qty = 0;
		$total_revenue_amount = 0;
		$total_bought_amount = 0;
		$total_profit_amount = 0;
		$i = 1;

		// 1. Gom dữ liệu
		$grouped = [];

		if (!empty($data_revenue) && $data_revenue['count'] > 0) {
			foreach ($data_revenue['details'] as $row) {
				$uid = $row['user_id'] ?: 'empty';

				// Nhân viên chỉ gom dữ liệu của mình
				if (!empty($assigned_user_id) && $uid !== $assigned_user_id) {
					continue;
				}

				if (!isset($grouped[$uid])) {
					$grouped[$uid] = [
						'user_id'    		=> $uid,
						'full_name'  		=> $user_list[$uid] ?? '(Trống)',
						'title'      		=> $row['title'],
						'total_bk'   		=> 0,
						'ticket_qty' 		=> 0,
						'revenue_amount' 	=> 0,
						'bought_amount'  	=> 0,
						'profit_amount'  	=> 0,
					];
				}

				// Booking
				if ($row['parent_type'] === 'EC_Flight_Bookings') {
					$grouped[$uid]['total_bk'] += 1;
				}

				$grouped[$uid]['ticket_qty']     += $row['total_quantity'];
				$grouped[$uid]['revenue_amount'] += $row['subtotal_amount'];
				$grouped[$uid]['bought_amount']  += $row['total_bought_price'];
				$grouped[$uid]['profit_amount']  += $row['profit_amount'];
			}

			// 2. SORT ở đây
			uasort($grouped, function ($a, $b) {
				return $b['profit_amount'] <=> $a['profit_amount'];
			});

			// 3. Loop render
			foreach ($grouped as $row) {
				$link = "index.php?module=EC_Flight_Bookings&action=bksalereport&for=showDetail&user={$row['user_id']}&from_date={$from_date}&to_date=$to_date";
				$html .= '<tr>
					<td class="text-center">'. $i .'</td>
					<td>
						<a href="'. $link .'" target="_blank">
							'. $row['full_name'] .'
						</a>
					</td>
					<td class="text-center">' . format_number($row['total_bk']) . '</td>
					<td class="text-center">' . format_number($row['ticket_qty']) . '</td>
					<td class="text-end">' . format_number($row['revenue_amount']) . '</td>
					<td class="text-end">' . format_number($row['bought_amount']) . '</td>
					<td class="text-end">' . format_number($row['profit_amount']) . '</td>
				</tr>';

				$total_bk_qty     		+= $row['total_bk'];
				$total_ticket_qty 		+= $row['ticket_qty'];
				$total_revenue_amount   += $row['revenue_amount'];
				$total_bought_amount    += $row['bought_amount'];
				$total_profit_amount    += $row['profit_amount'];
				$i++;
			}
		}

		// Format number
		$total_bk_qty = format_number($total_bk_qty);
		$total_ticket_qty = format_number($total_ticket_qty);
		$total_revenue_amount = format_number($total_revenue_amount);
		$total_bought_amount = format_number($total_bought_amount);
		$total_profit_amount = format_number($total_profit_amount);

		return <<<HTML
			<thead>
				<th width="5%">STT</th>
				<th width="20%">Họ và tên</th>
				<th width="5%">Booking</th>
				<th width="5%">Vé</th>
				<th width="10%">Doanh thu</th>
				<th width="10%">Giá mua</th>
				<th width="10%">Doanh số</th>
			</thead>
			<tbody>
				$html
				<tr class="last-row footer-tr">
					<td></td>
					<td>Tổng cộng</td>
					<td class="text-center">{$total_bk_qty}</td>
					<td class="text-center">{$total_ticket_qty}</td>
					<td class="text-end">{$total_revenue_amount}</td>
					<td class="text-end">{$total_bought_amount}</td>
					<td class="text-end">{$total_profit_amount}</td>
				</tr>
			</tbody>
		HTML;
	}

	// Chi tiết doanh số
	public function getDetailDSBooking($from_date, $to_date, $assigned_user_id) {
		$html = '<thead>
					<th>STT</th>
					<th>Ngày chứng từ</th>
					<th>Booking</th>
					<th>Ngày xuất vé</th>
					<th>Số vé</th>
					<th>Doanh số</th>
				</thead><tbody>';

		$data_revenue = calculateRevenueOfDate($from_date, $to_date);

		$i 			= 1;
		$total_qty  = $total_ds_booking = 0;

		if (!empty($data_revenue) && $data_revenue['count'] > 0) {

			usort($data_revenue['details'], function ($a, $b) {
				return strtotime($a['voucher_date']) <=> strtotime($b['voucher_date']);
			});

			foreach ($data_revenue['details'] as $row) {
				$uid = $row['user_id'] ?: 'empty';

				if ($uid === $assigned_user_id) {
					$html .= '<tr>
						<td class="text-center fw-semibold">' . $i . '</td>
						<td class="text-center">' . $row['voucher_date'] . '</td>
						<td class="text-center">
						<a target="_blank" href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '">' . $row['booking_name'] . '</a>
						' . ((!empty($row['parent_name']) && $row['parent_type'] != 'EC_Flight_Bookings') ? '/ <a target="_blank" href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['parent_id'] . '">' . $row['parent_name'] . '</a>' : '') . '
						</td>
						<td class="text-center">' . $row['date_ticket_issue'] . '</td>
						<td class="text-center">' . $row['total_quantity'] . '</td>
						<td class="text-end">' . format_number($row['profit_amount']) . '</td>
					</tr>';
					$i++;

					$total_qty += $row['total_quantity'];
					$total_ds_booking += $row['profit_amount'];
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
			 		<td class="text-end">' . format_number($total_ds_booking) . '</td>
			 	</tr></tbody>';

		return $html;
	}
}
