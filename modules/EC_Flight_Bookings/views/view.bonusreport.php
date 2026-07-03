<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewbonusreport extends SugarView {
	/** @var Sugar_Smarty **/
	public $smartyObj;

	public function display() {
		$this->smartyObj = new Sugar_Smarty();
		$this->populateContent();
		$this->smartyObj->display("modules/{$this->bean->module_dir}/tpls/view_bonusreport.tpl");
	}

	public function populateContent() {
		global $current_user;

		$current_year = date('Y');
		$last_year = date("Y", strtotime("- 1 year"));

		$current_date = date('d-m-Y');

		switch (ceil(date('n') / 3)) {
			case 1:
				$cq_from_date 	= "01-01-$current_year";
				$cq_to_date 	= "31-03-$current_year";
				$lq_from_date 	= "01-01-$last_year";
				$lq_to_date 	= "31-03-$last_year";
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

		$report_term_list = '<option ' . (($_POST['report_term_list'] ?? '') === 'today' ? 'selected' : '') . ' value="today" data-fromdate="' . date('d-m-Y') . '" data-todate="' . date('d-m-Y') . '" data-term="' . date('m') . '" data-year="' . date('Y') . '">Hôm nay</option>';
		$report_term_list .= '<option ' . (($_POST['report_term_list'] ?? '') === 'yesterday' ? 'selected' : '') . ' value="yesterday" data-fromdate="' . date('d-m-Y', strtotime("-1 day")) . '" data-todate="' . date('d-m-Y', strtotime("-1 day")) . '" data-term="' . date('m', strtotime("-1 day")) . '" data-year="' . date('Y', strtotime("-1 day")) . '">Hôm qua</option>';
		$report_term_list .= '<option ' . (($_POST['report_term_list'] ?? '') === 'this_week' ? 'selected' : '') . ' value="this_week" data-fromdate="' . date('d-m-Y', strtotime("monday this week")) . '" data-todate="' . date('d-m-Y', strtotime("sunday this week")) . '" data-term="' . date('m', strtotime("sunday this week")) . '" data-year="' . date('Y', strtotime("sunday this week")) . '">Tuần này</option>';
		$report_term_list .= '<option ' . (($_POST['report_term_list'] ?? '') === 'previous_week' ? 'selected' : '') . ' value="previous_week" data-fromdate="' . date('d-m-Y', strtotime("monday previous week")) . '" data-todate="' . date('d-m-Y', strtotime("sunday previous week")) . '" data-term="' . date('m', strtotime("sunday previous week")) . '" data-year="' . date('Y', strtotime("sunday previous week")) . '">Tuần trước</option>';
		$report_term_list .= '<option ' . (($_POST['report_term_list'] ?? '') === 'this_month' ? 'selected' : '') . ' value="this_month" data-fromdate="' . date('d-m-Y', strtotime("first day of this month")) . '" data-todate="' . date('d-m-Y', strtotime("last day of this month")) . '" data-term="' . date('m', strtotime("last day of this month")) . '" data-year="' . date('Y', strtotime("last day of this month")) . '">Tháng này</option>';
		$report_term_list .= '<option ' . (($_POST['report_term_list'] ?? '') === 'previous_month' ? 'selected' : '') . ' value="previous_month" data-fromdate="' . date('d-m-Y', strtotime("first day of previous month")) . '" data-todate="' . date('d-m-Y', strtotime("last day of previous month")) . '" data-term="' . date('m', strtotime("last day of previous month")) . '" data-year="' . date('Y', strtotime("last day of previous month")) . '">Tháng trước</option>';
		$report_term_list .= '<option ' . (($_POST['report_term_list'] ?? '') === 'this_quater' ? 'selected' : '') . ' value="this_quater" data-fromdate="' . date('d-m-Y', strtotime($cq_from_date)) . '" data-todate="' . date('d-m-Y', strtotime($cq_to_date)) . '" data-term="' . date('m', strtotime($cq_from_date)) . '" data-year="' . date('Y', strtotime($cq_from_date)) . '">Quý này</option>';
		$report_term_list .= '<option ' . (($_POST['report_term_list'] ?? '') === 'previous_quater' ? 'selected' : '') . ' value="previous_quater" data-fromdate="' . date('d-m-Y', strtotime($lq_from_date)) . '" data-todate="' . date('d-m-Y', strtotime($lq_to_date)) . '" data-term="' . date('m', strtotime($lq_from_date)) . '" data-year="' . date('Y', strtotime($lq_from_date)) . '">Quý trước</option>';
		$this->smartyObj->assign('REPORT_TERM_LIST', $report_term_list);

		// Form submits via POST, detail links pass dates via GET
		$from_date 	= (empty($_REQUEST['from_date'])) ? $current_date : $_REQUEST['from_date'];
		$to_date 	= (empty($_REQUEST['to_date'])) ? $current_date : $_REQUEST['to_date'];

		// Helper expects date-only values in the user's format and expands
		// them to full-day boundaries itself
		$report = EC_Flight_Bookings_Helper::get_bonus_report($from_date, $to_date);

		$this->smartyObj->assign('FROM_DATE', $from_date);
		$this->smartyObj->assign('TO_DATE', $to_date);
		$this->smartyObj->assign('BONUS_DATA', $this->renderBonusTotal($report));
	}

	/**
	 * Render the summary table: one row per user with total bonus amount,
	 * built from the "total" array of get_bonus_report. Each user row is
	 * followed by a hidden row holding their detail table, toggled by JS.
	 */
	private function renderBonusTotal(array $report) {
		$user_list = get_user_array(true, '', '', true);

		$totals = $report['total'] ?? [];
		arsort($totals);

		$html = '';
		$i = 1;
		$total_booking_qty  = 0;
		$total_bonus_amount = 0;

		foreach ($totals as $user_id => $bonus_amount) {
			$booking_qty = count($report['details'][$user_id] ?? []);
			$full_name 	 = $user_list[$user_id] ?? '(Trống)';

			$html .= '<tr class="bonus-user-row">
				<td class="text-center">' . $i . '</td>
				<td>
					<a href="javascript:void(0);" class="js-toggle-bonus-detail" data-user="' . $user_id . '">' . $full_name . '</a>
				</td>
				<td class="text-center">' . format_number($booking_qty) . '</td>
				<td class="text-end">' . format_number(round($bonus_amount)) . '</td>
			</tr>';

			$html .= '<tr id="bonus-detail-' . $user_id . '" class="bonus-detail-row" style="display: none;">
				<td colspan="4">' . $this->renderBonusDetail($report, $user_id) . '</td>
			</tr>';

			$total_booking_qty  += $booking_qty;
			$total_bonus_amount += $bonus_amount;
			$i++;
		}

		$total_booking_qty  = format_number($total_booking_qty);
		$total_bonus_amount = format_number(round($total_bonus_amount));

		return <<<HTML
			<thead>
				<th width="5%">STT</th>
				<th width="30%">Họ và tên</th>
				<th width="15%">Booking</th>
				<th width="20%">Thưởng</th>
			</thead>
			<tbody>
				$html
				<tr class="last-row footer-tr">
					<td></td>
					<td>Tổng cộng</td>
					<td class="text-center">{$total_booking_qty}</td>
					<td class="text-end">{$total_bonus_amount}</td>
				</tr>
			</tbody>
		HTML;
	}

	/**
	 * Render the per-user detail table (nested inside the summary table):
	 * one row per booking with KPI and direct/indirect bonus, built from
	 * the "details" array of get_bonus_report.
	 */
	private function renderBonusDetail(array $report, string $user_id) {
		$html = '';
		$i = 1;
		$total_kpi 		= 0;
		$total_direct 	= 0;
		$total_indirect = 0;

		foreach (($report['details'][$user_id] ?? []) as $booking_id => $row) {
			$kpi 	  = $row['kpi'] ?? 0;
			$direct   = $row['directBonus'] ?? 0;
			$indirect = $row['indirectBonus'] ?? 0;

			$parent_type = !empty($row['parentType']) ? $row['parentType'] : 'EC_Flight_Bookings';
			$link = "index.php?module={$parent_type}&action=DetailView&record={$booking_id}";

			$html .= '<tr class="bonus-booking-row" data-booking-name="' . htmlspecialchars(mb_strtolower((string) $row['parentName']), ENT_QUOTES) . '">
				<td class="text-center fw-semibold">' . $i . '</td>
				<td class="text-center">
					<a href="' . $link . '" target="_blank">' . $row['parentName'] . '</a>
				</td>
				<td class="text-center">' . $row['flightDate'] . '</td>
				<td class="text-center">' . format_number($kpi) . '</td>
				<td class="text-end">' . format_number(round($direct)) . '</td>
				<td class="text-end">' . format_number(round($indirect)) . '</td>
				<td class="text-end">' . format_number(round($direct + $indirect)) . '</td>
			</tr>';

			$total_kpi 		+= $kpi;
			$total_direct 	+= $direct;
			$total_indirect += $indirect;
			$i++;
		}

		$total_kpi 		= format_number($total_kpi);
		$total_direct 	= format_number(round($total_direct));
		$total_indirect = format_number(round($total_indirect));
		$total_bonus 	= format_number(round($report['total'][$user_id] ?? 0));

		return <<<HTML
			<table cellpadding="0" cellspacing="0" class="table-sale_report_tbl table-details__booking">
				<thead>
					<th width="5%">STT</th>
					<th width="25%">Booking</th>
					<th width="15%">Ngày bay</th>
					<th width="10%">KPI</th>
					<th width="15%">Thưởng trực tiếp</th>
					<th width="15%">Thưởng gián tiếp</th>
					<th width="15%">Tổng thưởng</th>
				</thead>
				<tbody>
					$html
					<tr class="last-row footer-tr">
						<td></td>
						<td>Tổng cộng</td>
						<td></td>
						<td class="text-center">{$total_kpi}</td>
						<td class="text-end">{$total_direct}</td>
						<td class="text-end">{$total_indirect}</td>
						<td class="text-end">{$total_bonus}</td>
					</tr>
				</tbody>
			</table>
		HTML;
	}
}
