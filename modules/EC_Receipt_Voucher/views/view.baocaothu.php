<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewbaocaothu extends SugarView
{
	function display()
	{
		$smartyCont = new Sugar_Smarty();
		$this->populateCont($smartyCont);
		$smartyCont->display('modules/'.$this->bean->object_name.'/tpls/baocaothu.tpl');
	}

	function populateCont($smartyobj)
	{
		global $current_user, $app_list_strings;

		$post_fdate = isset($_POST['from_date']) && !empty($_POST['from_date']) ? $_POST['from_date'] : date('01-m-Y');
		$post_tdate = isset($_POST['to_date']) && !empty($_POST['to_date']) ? $_POST['to_date'] : date('d-m-Y');

		$location_id  = array_values(array_filter((array)($_POST['location_id']  ?? []),  fn($v) => $v !== ''));
		$receipt_type = array_values(array_filter((array)($_POST['receipt_type'] ?? []),  fn($v) => $v !== ''));
		$rv_status    = array_values(array_filter((array)($_POST['rv_status']    ?? []), fn($v) => $v !== ''));
		$loai_thu     = array_values(array_filter((array)($_POST['loai_thu']     ?? []),  fn($v) => $v !== ''));
		$group_by     = $_POST['group_by']     ?? 'month';

		$department_id   = $current_user->department_id;
		$department_info = myGetDepartmentInfo($department_id);

		$smartyobj->assign('STYLE_VERSION', inDeveloperMode() ? time() : '1.0.0');
		$smartyobj->assign('MODULE_NAME', $this->bean->module_dir);

		$smartyobj->assign('POST_FDATE', $post_fdate);
		$smartyobj->assign('POST_TDATE', $post_tdate);
		$smartyobj->assign('GROUP_BY', $group_by);
		$smartyobj->assign('LOCATION_ID', myGetLocationListByDepID(count($location_id) === 1 ? $location_id[0] : ''));

		$smartyobj->assign('RECEIPT_TYPE_OPTS', $this->buildOpts(
			array('' => '-- Tất cả --') + ($app_list_strings['receipt_type_list'] ?? array()),
			$receipt_type
		));
		$smartyobj->assign('RV_STATUS_OPTS', $this->buildOpts(
			array('' => '-- Tất cả --') + ($app_list_strings['receipt_voucher_status_list'] ?? array()),
			$rv_status
		));
		$smartyobj->assign('LOAI_THU_OPTS', $this->buildOpts(
			array('' => '-- Tất cả --') + ($app_list_strings['loai_thu_list'] ?? array()),
			$loai_thu
		));

		$report_term = $_POST['report_term'] ?? 'm' . date('n');
		$smartyobj->assign('REPORT_TERMS', myGetNewReportTerms($report_term));
		$smartyobj->assign('GROUP_BY_OPTS', get_select_options_with_id(array(
			'day'   => 'Theo ngày',
			'week'  => 'Theo tuần',
			'month' => 'Theo tháng',
			'year'  => 'Theo năm',
		), $group_by));

		$location_name = $_POST['location_name'] ?? '';
		$smartyobj->assign('LOCATION_NAME', $location_name);
		$smartyobj->assign('COM_NAME', $department_info['com_name'] ?? '');
		$smartyobj->assign('COM_ADDRESS', $department_info['com_address'] ?? '');
		$smartyobj->assign('COM_TAXCODE', $department_info['com_taxcode'] ?? '');

		$data = $this->buildReport($post_fdate, $post_tdate, $location_id, $receipt_type, $rv_status, $loai_thu, $group_by);
		$smartyobj->assign('REPORT_HTML', $data['html']);
		$smartyobj->assign('SHOW_REPORT', true);

		if (isset($_POST['exportexcel'])) {
			ob_clean();
			header("Pragma: cache");
			$xls = '<html><head><meta charset="UTF-8"></head><body>' . $data['html'] . '</body></html>';
			$xls = chr(255) . chr(254) . mb_convert_encoding($xls, "UTF-16LE", "UTF-8");
			header("Content-type: application/x-msdownload");
			header("Content-disposition: xls; filename=baocaothu_" . time() . ".xls; size=" . strlen($xls));
			echo $xls;
			exit();
		}
	}

	function buildOpts($options, $selected)
	{
		$selectedArr = array_map('strval', (array)$selected);
		$html = '';
		foreach ($options as $k => $v) {
			$k = (string)$k;
			$sel = in_array($k, $selectedArr, true) ? ' selected' : '';
			$html .= '<option value="' . htmlspecialchars($k) . '"' . $sel . '>' . htmlspecialchars($v) . '</option>';
		}
		return $html;
	}

	/**
	 * Build report data + HTML. 
	 */
	function buildReport($post_fdate, $post_tdate, $location_id, $receipt_type, $rv_status, $loai_thu, $group_by)
	{
		global $db;

		$from = date('Y-m-d', strtotime($post_fdate));
		$to   = date('Y-m-d', strtotime($post_tdate));

		$where  = " p.deleted = 0 ";
		$where .= " AND DATE(p.ngayhachtoan) BETWEEN  '" . $db->quote($from) . "' AND '" . $db->quote($to)   . "' ";

		if (!empty($location_id)) {
			$q = implode("','", array_map([$db, 'quote'], $location_id));
			$where .= " AND p.com_location_id IN ('" . $q . "') ";
		}
		if (!empty($receipt_type)) {
			$q = implode("','", array_map([$db, 'quote'], $receipt_type));
			$where .= " AND p.receipt_type IN ('" . $q . "') ";
		}
		if (!empty($rv_status)) {
			$q = implode("','", array_map([$db, 'quote'], $rv_status));
			$where .= " AND p.rv_status IN ('" . $q . "') ";
		}
		if (!empty($loai_thu)) {
			$q = implode("','", array_map([$db, 'quote'], $loai_thu));
			$where .= " AND p.loai_thu IN ('" . $q . "') ";
		}

		$period_expr = $this->getPeriodExpr($group_by);

		// 1) Summary by loai_thu
		$sqlByType = "SELECT p.loai_thu, COUNT(p.id) AS cnt, SUM(IFNULL(p.amount_converted, p.amount)) AS total
			FROM ec_receipt_voucher p
			WHERE " . $where . "
			GROUP BY p.loai_thu
			ORDER BY total DESC";
		$rsA = $db->query($sqlByType);

		$rowsByType = array();
		$grandCnt = 0;
		$grandTotal = 0;
		while ($r = $db->fetchByAssoc($rsA)) {
			$rowsByType[] = $r;
			$grandCnt   += (int)$r['cnt'];
			$grandTotal += (float)$r['total'];
		}

		// 2) Summary by period x loai_thu (matrix)
		$sqlByPeriod = "SELECT " . $period_expr . " AS period, p.loai_thu, SUM(IFNULL(p.amount_converted, p.amount)) AS total
			FROM ec_receipt_voucher p
			WHERE " . $where . "
			GROUP BY period, p.loai_thu
			ORDER BY period ASC";
		$rsB = $db->query($sqlByPeriod);

		$matrix = array();       // [period][loai_thu] => total
		$periods = array();
		$typesInRange = array();
		while ($r = $db->fetchByAssoc($rsB)) {
			$p  = $r['period'];
			$lt = $r['loai_thu'];
			$matrix[$p][$lt] = (float)$r['total'];
			$periods[$p] = true;
			$typesInRange[$lt] = true;
		}

		// 3) Summary by receipt_type (cash/bank)
		$sqlByForm = "SELECT p.receipt_type, COUNT(p.id) AS cnt, SUM(IFNULL(p.amount_converted, p.amount)) AS total
			FROM ec_receipt_voucher p
			WHERE " . $where . "
			GROUP BY p.receipt_type";
		$rsC = $db->query($sqlByForm);
		$rowsByForm = array();
		while ($r = $db->fetchByAssoc($rsC)) {
			$rowsByForm[] = $r;
		}

		$html = $this->renderHtml($post_fdate, $post_tdate, $group_by, $rowsByType, $rowsByForm, $matrix, array_keys($periods), array_keys($typesInRange), $grandCnt, $grandTotal);

		return array('html' => $html);
	}

	function getPeriodExpr($group_by)
	{
		switch ($group_by) {
			case 'day':
				return "DATE_FORMAT(p.ngayhachtoan, '%d/%m/%Y')";
			case 'week':
				return "CONCAT('Tuần ', WEEK(p.ngayhachtoan, 3), '/', YEAR(p.ngayhachtoan))";
			case 'year':
				return "DATE_FORMAT(p.ngayhachtoan, '%Y')";
			case 'month':
			default:
				return "DATE_FORMAT(p.ngayhachtoan, '%m/%Y')";
		}
	}

	function renderHtml($post_fdate, $post_tdate, $group_by, $rowsByType, $rowsByForm, $matrix, $periods, $typesInRange, $grandCnt, $grandTotal)
	{
		global $app_list_strings;
		$loaiThuLabels    = $app_list_strings['loai_thu_list']     ?? array();
		$receiptTypeLabel = $app_list_strings['receipt_type_list'] ?? array();

		$html  = '<div class="baocaothu-report box-section">';
		$html .= '<h3 class="text-center fw-bold">BÁO CÁO THU</h3>';
		$html .= '<p class="text-center fst-italic mb-3">Từ ngày ' . htmlspecialchars($post_fdate) . ' đến ngày ' . htmlspecialchars($post_tdate) . '</p>';

		// ---- Section 1: by loai_thu
		$html .= '<h5 class="border-start border-primary border-4 mt-3 ps-3 fs-6">1. Tổng hợp theo loại thu</h4>';
		$html .= '<table class="table table-bordered table-details__booking" cellpadding="5" cellspacing="0" border="1">';
		$html .= '<thead>
					<tr>
						<th width="5%" class="text-center">#</th>
						<th>Loại thu</th>
						<th width="12%" class="text-center">Số phiếu</th>
						<th width="20%" class="text-end">Số tiền (VNĐ)</th>
						<th width="10%" class="text-end">Tỷ trọng</th>
					</tr>
				</thead>
				<tbody>';
		$stt = 0;
		foreach ($rowsByType as $r) {
			$stt++;
			$lt     = $r['loai_thu'];
			$ltName = $loaiThuLabels[(int)$lt] ?? ($lt !== null && $lt !== '' ? ('#' . htmlspecialchars($lt)) : '<i>(Không phân loại)</i>');
			$ratio  = $grandTotal > 0 ? ($r['total'] / $grandTotal * 100) : 0;

			$html .= '<tr>
						<td class="text-center">' . $stt . '</td>
						<td>' . $ltName . '</td>
						<td class="text-center">' . format_number($r['cnt']) . '</td>
						<td class="text-end">' . format_number($r['total']) . '</td>
						<td class="text-end">' . number_format($ratio, 2) . '%</td>
					</tr>';
		}

		// Tổng
		$html .= '<tr class="fw-bold table-secondary align-middle">
					<td colspan="2" class="text-end">TC</td>
					<td class="text-center">' . format_number($grandCnt) . '</td>
					<td class="text-end">' . format_number($grandTotal) . '</td>
					<td class="text-end">100%</td>
				</tr>';
		$html .= '</tbody></table>';

		// ---- Section 2: by receipt_type (cash/bank)
		$html .= '<h5 class="border-start border-primary border-4 mt-3 ps-3 fs-6">2. Tổng hợp theo hình thức thu</h4>';
		$html .= '<table class="table table-bordered table-details__booking" cellpadding="5" cellspacing="0" border="1">';
		$html .= '<thead><tr>
					<th>Hình thức</th>
					<th width="20%" class="text-center">Số phiếu</th>
					<th width="30%" class="text-end">Số tiền (VNĐ)</th>
				</tr></thead><tbody>';
		foreach ($rowsByForm as $r) {
			$formName = $receiptTypeLabel[$r['receipt_type']] ?? ($r['receipt_type'] ?: '<i>(Không rõ)</i>');
			$html .= '<tr>
				<td>' . $formName . '</td>
				<td class="text-center">' . format_number($r['cnt']) . '</td>
				<td class="text-end">' . format_number($r['total']) . '</td>
			</tr>';
		}
		$html .= '</tbody></table>';

		// ---- Section 3: matrix period x loai_thu
		$groupLabel = array(
			'day'   => 'ngày',
			'week'  => 'tuần',
			'month' => 'tháng',
			'year'  => 'năm',
		);
		$html .= '<h5 class="border-start border-primary border-4 mt-3 ps-3 fs-6">3. Tổng hợp theo ' . ($groupLabel[$group_by] ?? 'tháng') . '</h5>';
		if (empty($periods) || empty($typesInRange)) {
			$html .= '<p><i>Không có dữ liệu.</i></p>';
		} else {
			$html .= '<div style="overflow-x:auto"><table class="table table-bordered table-details__booking" cellpadding="5" cellspacing="0" border="1">';
			$html .= '<thead><tr><th>Kỳ</th>';

			foreach ($typesInRange as $lt) {
				$ltName = $loaiThuLabels[(int)$lt] ?? ($lt !== null && $lt !== '' ? ('#' . htmlspecialchars($lt)) : '(K.P.L)');
				$html .= '<th class="text-end">' . $ltName . '</th>';
			}
			$html .= '<th class="text-end">Tổng</th></tr></thead><tbody>';

			$colTotals = array_fill_keys($typesInRange, 0);
			$sumAll = 0;
			foreach ($periods as $p) {
				$html .= '<tr><td>' . htmlspecialchars($p) . '</td>';
				$rowSum = 0;
				foreach ($typesInRange as $lt) {
					$v = $matrix[$p][$lt] ?? 0;
					$rowSum += $v;
					$colTotals[$lt] += $v;
					$html .= '<td class="text-end">' . ($v ? format_number($v) : '&nbsp;') . '</td>';
				}
				$sumAll += $rowSum;
				$html .= '<td class="text-end" style="font-weight:bold">' . format_number($rowSum) . '</td></tr>';
			}

			// Tổng
			$html .= '<tr class="fw-bold table-secondary align-middle"><td>TC</td>';
			foreach ($typesInRange as $lt) {
				$html .= '<td class="text-end">' . format_number($colTotals[$lt]) . '</td>';
			}
			$html .= '<td class="text-end">' . format_number($sumAll) . '</td></tr>';
			$html .= '</tbody></table></div>';
		}

		$html .= '</div>';
		return $html;
	}
}
