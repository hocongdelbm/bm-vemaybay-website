<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewbonusreport extends SugarView {
	/** The bonus policy starts on this date — the report cannot go earlier */
	private const MIN_REPORT_DATE = '01-07-2026';

	/** @var Sugar_Smarty **/
	public $smartyObj;

	/** @var bool Whether the current user may open the booking bonus modal */
	private $canViewParentBonus = false;

	public function display() {
		$this->smartyObj = new Sugar_Smarty();
		$this->populateContent();
		$this->smartyObj->display("modules/{$this->bean->module_dir}/tpls/view_bonusreport.tpl");
	}

	public function populateContent() {
		global $current_user, $db;

		// Only admins and chief accountants get the booking bonus modal —
		// it exposes revenue/cost figures. Everyone else gets a plain label
		$is_chief_accountant = $db->getOne(
			"SELECT COUNT(id)
			FROM acl_roles_users
			WHERE user_id = '{$current_user->id}'
				AND role_id = '{$GLOBALS['app_list_strings']['roles_users']['KETOAN']}'
				AND deleted = 0"
		);
		$this->canViewParentBonus = $is_chief_accountant || is_admin($current_user);

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

		// value, label, from/to range, and the date whose month/year fills
		// data-term / data-year (all as timestamps)
		$presets = [
			['value' => 'today', 			'label' => 'Hôm nay', 	 'from' => strtotime('today'), 						'to' => strtotime('today'), 					 'term' => strtotime('today')],
			['value' => 'yesterday', 		'label' => 'Hôm qua', 	 'from' => strtotime('-1 day'), 					'to' => strtotime('-1 day'), 					 'term' => strtotime('-1 day')],
			['value' => 'this_week', 		'label' => 'Tuần này', 	 'from' => strtotime('monday this week'), 			'to' => strtotime('sunday this week'), 			 'term' => strtotime('sunday this week')],
			['value' => 'previous_week', 	'label' => 'Tuần trước', 'from' => strtotime('monday previous week'), 		'to' => strtotime('sunday previous week'), 		 'term' => strtotime('sunday previous week')],
			['value' => 'this_month', 		'label' => 'Tháng này',  'from' => strtotime('first day of this month'), 	'to' => strtotime('last day of this month'), 	 'term' => strtotime('last day of this month')],
			['value' => 'previous_month', 	'label' => 'Tháng trước','from' => strtotime('first day of previous month'),'to' => strtotime('last day of previous month'), 'term' => strtotime('last day of previous month')],
			['value' => 'this_quater', 		'label' => 'Quý này', 	 'from' => strtotime($cq_from_date), 				'to' => strtotime($cq_to_date), 				 'term' => strtotime($cq_from_date)],
			['value' => 'previous_quater', 	'label' => 'Quý trước',  'from' => strtotime($lq_from_date), 				'to' => strtotime($lq_to_date), 				 'term' => strtotime($lq_from_date)],
		];

		$min_report_ts = DateTime::createFromFormat('!d-m-Y', self::MIN_REPORT_DATE)->getTimestamp();

		$report_term_list = '';
		foreach ($presets as $preset) {
			// Hide presets whose whole range ends before the policy start
			if ($preset['to'] < $min_report_ts) {
				continue;
			}

			$selected = (($_REQUEST['report_term_list'] ?? '') === $preset['value']) ? 'selected' : '';
			$report_term_list .= '<option ' . $selected . ' value="' . $preset['value'] . '"'
				. ' data-fromdate="' . date('d-m-Y', $preset['from']) . '"'
				. ' data-todate="' . date('d-m-Y', $preset['to']) . '"'
				. ' data-term="' . date('m', $preset['term']) . '"'
				. ' data-year="' . date('Y', $preset['term']) . '">'
				. $preset['label'] . '</option>';
		}
		$this->smartyObj->assign('REPORT_TERM_LIST', $report_term_list);

		// Form submits via POST, detail links pass dates via GET
		$from_date 	= (empty($_REQUEST['from_date'])) ? $current_date : $_REQUEST['from_date'];
		$to_date 	= (empty($_REQUEST['to_date'])) ? $current_date : $_REQUEST['to_date'];

		// The JS enforces the same limit, but requests can bypass the form
		$from_date 	= $this->clampToMinReportDate($from_date);
		$to_date 	= $this->clampToMinReportDate($to_date);

		// Helper expects date-only values in the user's format and expands
		// them to full-day boundaries itself
		$report = EC_Flight_Bookings_Helper::get_bonus_report($from_date, $to_date);

		$this->smartyObj->assign('FROM_DATE', $from_date);
		$this->smartyObj->assign('TO_DATE', $to_date);
		$this->smartyObj->assign('BONUS_DATA', $this->renderBonusTotal($report));
	}

	/**
	 * Clamp a date to MIN_REPORT_DATE so the report can never cover days
	 * before the policy start. Users type both 01-07-2026 and 01/07/2026;
	 * anything else (unparseable) also falls back to the minimum date.
	 */
	private function clampToMinReportDate(string $date): string {
		$min = DateTime::createFromFormat('!d-m-Y', self::MIN_REPORT_DATE);
		$dt  = DateTime::createFromFormat('!d-m-Y', str_replace('/', '-', trim($date)));

		return ($dt === false || $dt < $min) ? self::MIN_REPORT_DATE : $date;
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
			$parent = $report['parentInfo'][$booking_id] ?? [];

			$kpi 	  = $row['kpi'] ?? 0;
			$direct   = $row['directBonus'] ?? 0;
			$indirect = $row['indirectBonus'] ?? 0;

			$parent_type = !empty($parent['parentType']) ? $parent['parentType'] : 'EC_Flight_Bookings';
			$link = "index.php?module={$parent_type}&action=DetailView&record={$booking_id}";

			$parent_name = htmlspecialchars((string) ($parent['parentName'] ?? ''), ENT_QUOTES);

			$intl_badge = !empty($parent['isInter'])
				? '<span class="intl-badge" title="Vé quốc tế">QT</span>'
				: '';

			// The modal anchor carries booking-level revenue/cost figures, so
			// unauthorized users get the bare name — no click, no data
			if ($this->canViewParentBonus) {
				$parent_name_html = '<a href="javascript:void(0);" class="js-parent-bonus"
					data-name="' . $parent_name . '"
					data-qty="' . round($parent['totalTicketQty'] ?? 0) . '"
					data-revenue="' . round($parent['totalRevenue'] ?? 0) . '"
					data-cost="' . round($parent['totalCost'] ?? 0) . '"
					data-profit="' . round($parent['totalProfit'] ?? 0) . '"
					data-avgprofit="' . round($parent['avgProfit'] ?? 0) . '"
					data-minthreshold="' . round($parent['minThresholdValue'] ?? 0) . '"
					data-extrathreshold="' . round($parent['extraThresholdValue'] ?? 0) . '"
					data-bonuspercent="' . round(($parent['bonusPercent'] ?? 0) * 100) . '"
					data-extrapercent="' . round(($parent['extraBonusPercent'] ?? 0) * 100) . '"
					data-perticket="' . round($parent['bonusPerTicket'] ?? 0) . '"
					data-indirectkpi="' . round($parent['totalIndirectKPI'] ?? 0) . '"
					data-direct="' . round($parent['totalDirectBonus'] ?? 0) . '"
					data-indirect="' . round($parent['totalIndirectBonus'] ?? 0) . '">' . ($parent['parentName'] ?? '') . '</a>';
			} else {
				$parent_name_html = $parent_name;
			}

			$html .= '<tr class="bonus-booking-row" data-booking-name="' . htmlspecialchars(mb_strtolower((string) ($parent['parentName'] ?? '')), ENT_QUOTES) . '">
				<td class="text-center fw-semibold">' . $i . '</td>
				<td class="text-center">
					' . $parent_name_html . '
					' . $intl_badge . '
					<a href="' . $link . '" target="_blank" class="parent-detail-link" title="Xem chi tiết booking">
						<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
							<path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
							<path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
						</svg>
					</a>
				</td>
				<td class="text-center">' . ($parent['flightDate'] ?? '') . '</td>
				<td class="text-center">' . format_number($kpi) . '</td>
				<td class="text-end">' . format_number(round($indirect)) . '</td>
				<td class="text-end">' . format_number(round($direct)) . '</td>
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
					<th width="15%">Ngày bay cuối</th>
					<th width="10%">KPI</th>
					<th width="15%">Thưởng gián tiếp</th>
					<th width="15%">Thưởng trực tiếp</th>
					<th width="15%">Tổng thưởng</th>
				</thead>
				<tbody>
					$html
					<tr class="last-row footer-tr">
						<td></td>
						<td>Tổng cộng</td>
						<td></td>
						<td class="text-center">{$total_kpi}</td>
						<td class="text-end">{$total_indirect}</td>
						<td class="text-end">{$total_direct}</td>
						<td class="text-end">{$total_bonus}</td>
					</tr>
				</tbody>
			</table>
		HTML;
	}
}
