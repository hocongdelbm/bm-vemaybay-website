<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewbaocaothuchi extends SugarView
{
	function display()
	{
		$smartyCont = new Sugar_Smarty();
		$this->populateCont($smartyCont);
		$smartyCont->display('modules/'.$this->bean->object_name.'/tpls/baocaothuchi.tpl');
	}

	function populateCont($smartyobj)
	{
		global $current_user, $app_list_strings;

		$post_fdate = isset($_POST['from_date']) && !empty($_POST['from_date']) ? $_POST['from_date'] : date('01-m-Y');
		$post_tdate = isset($_POST['to_date']) && !empty($_POST['to_date']) ? $_POST['to_date'] : date('d-m-Y');

		$location_id  = array_values(array_filter((array)($_POST['location_id']  ?? []),  fn($v) => $v !== ''));
		$receipt_type = array_values(array_filter((array)($_POST['receipt_type'] ?? []),  fn($v) => $v !== ''));
		$rv_status    = array_values(array_filter((array)($_POST['rv_status']    ?? ['1']), fn($v) => $v !== ''));
		$loai_thu     = array_values(array_filter((array)($_POST['loai_thu']     ?? []),  fn($v) => $v !== ''));
		$pv_status    = array_values(array_filter((array)($_POST['pv_status']    ?? ['3']), fn($v) => $v !== ''));
		$loai_chi     = array_values(array_filter((array)($_POST['loai_chi']     ?? []),  fn($v) => $v !== ''));
		$group_by     = $_POST['group_by'] ?? 'month';

		$department_id   = $current_user->department_id;
		$department_info = myGetDepartmentInfo($department_id);

		$smartyobj->assign('STYLE_VERSION', inDeveloperMode() ? time() : '1.0.2');
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
		$smartyobj->assign('PV_STATUS_OPTS', $this->buildOpts(
			array('' => '-- Tất cả --') + ($app_list_strings['payment_voucher_status_list'] ?? array()),
			$pv_status
		));
		$smartyobj->assign('LOAI_CHI_OPTS', $this->buildOpts(
			array('' => '-- Tất cả --') + $this->getPaymentTypesList(),
			$loai_chi
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

		$data = $this->buildReport($post_fdate, $post_tdate, $location_id, $receipt_type, $rv_status, $loai_thu, $group_by, $pv_status, $loai_chi);
		$smartyobj->assign('REPORT_HTML', $data['html']);
		$smartyobj->assign('SHOW_REPORT', true);

		if (isset($_POST['exportexcel'])) {
			ob_clean();
			header("Pragma: cache");
			$xls = '<html><head><meta charset="UTF-8"></head><body>' . $data['html'] . '</body></html>';
			$xls = chr(255) . chr(254) . mb_convert_encoding($xls, "UTF-16LE", "UTF-8");
			header("Content-type: application/x-msdownload");
			header("Content-disposition: xls; filename=baocaothuchi_" . time() . ".xls; size=" . strlen($xls));
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

	function getPaymentTypesList()
	{
		global $db;
		$rs   = $db->query("SELECT id, name FROM ec_payment_types WHERE deleted = 0 ORDER BY name ASC");
		$list = [];
		while ($r = $db->fetchByAssoc($rs)) {
			$list[$r['id']] = $r['name'];
		}
		return $list;
	}

	function buildReport($post_fdate, $post_tdate, $location_id, $receipt_type, $rv_status, $loai_thu, $group_by, $pv_status = [], $loai_chi = [])
	{
		global $db;

		$from = date('Y-m-d', strtotime($post_fdate));
		$to   = date('Y-m-d', strtotime($post_tdate));

		// ---- WHERE: thu ----
		$whereThu  = " p.deleted = 0 ";
		$whereThu .= " AND DATE(p.ngayhachtoan) BETWEEN '" . $db->quote($from) . "' AND '" . $db->quote($to) . "' ";

		if (!empty($location_id)) {
			$q = implode("','", array_map([$db, 'quote'], $location_id));
			$whereThu .= " AND p.com_location_id IN ('" . $q . "') ";
		}
		if (!empty($receipt_type)) {
			$q = implode("','", array_map([$db, 'quote'], $receipt_type));
			$whereThu .= " AND p.receipt_type IN ('" . $q . "') ";
		}
		if (!empty($rv_status)) {
			$q = implode("','", array_map([$db, 'quote'], $rv_status));
			$whereThu .= " AND p.rv_status IN ('" . $q . "') ";
		}
		if (!empty($loai_thu)) {
			$q = implode("','", array_map([$db, 'quote'], $loai_thu));
			$whereThu .= " AND p.loai_thu IN ('" . $q . "') ";
		}

		// ---- WHERE: chi ----
		$whereChi  = " pv.deleted = 0 ";
		$whereChi .= " AND DATE(pv.ngaychungtu) BETWEEN '" . $db->quote($from) . "' AND '" . $db->quote($to) . "' ";

		if (!empty($pv_status)) {
			$q = implode("','", array_map([$db, 'quote'], $pv_status));
			$whereChi .= " AND pv.pv_status IN ('" . $q . "') ";
		}
		if (!empty($loai_chi)) {
			$q = implode("','", array_map([$db, 'quote'], $loai_chi));
			$whereChi .= " AND pv.ec_payment_types_id_c IN ('" . $q . "') ";
		}

		$exprThu = $this->getPeriodExpr($group_by, 'p',  'ngayhachtoan');
		$exprChi = $this->getPeriodExpr($group_by, 'pv', 'ngaychungtu');

		// ==== THU queries ====
		$rsA = $db->query("SELECT p.loai_thu, COUNT(p.id) AS cnt, SUM(IFNULL(p.amount_converted, p.amount)) AS total
			FROM ec_receipt_voucher p WHERE $whereThu GROUP BY p.loai_thu ORDER BY total DESC");

		$rowsByType = []; $grandCntThu = 0; $grandTotalThu = 0;
		while ($r = $db->fetchByAssoc($rsA)) {
			$rowsByType[]   = $r;
			$grandCntThu   += (int)$r['cnt'];
			$grandTotalThu += (float)$r['total'];
		}

		$rsB = $db->query("SELECT $exprThu AS period, p.loai_thu, SUM(IFNULL(p.amount_converted, p.amount)) AS total
			FROM ec_receipt_voucher p WHERE $whereThu GROUP BY period, p.loai_thu ORDER BY period ASC");

		$matrixThu = []; $periodsThu = []; $typesThu = [];
		while ($r = $db->fetchByAssoc($rsB)) {
			$matrixThu[$r['period']][$r['loai_thu']] = (float)$r['total'];
			$periodsThu[$r['period']] = true;
			$typesThu[$r['loai_thu']] = true;
		}

		$rsC = $db->query("SELECT p.receipt_type, COUNT(p.id) AS cnt, SUM(IFNULL(p.amount_converted, p.amount)) AS total
			FROM ec_receipt_voucher p WHERE $whereThu GROUP BY p.receipt_type");

		$rowsByForm = [];
		while ($r = $db->fetchByAssoc($rsC)) { $rowsByForm[] = $r; }

		// ==== CHI queries ====
		$rsD = $db->query("SELECT pt.id AS loai_chi_id, pt.name AS loai_chi_name,
				COUNT(pv.id) AS cnt, SUM(pv.amount) AS total
			FROM ec_payment_voucher pv
			LEFT JOIN ec_payment_types pt ON pt.deleted = 0 AND pt.id = pv.ec_payment_types_id_c
			WHERE $whereChi GROUP BY pv.ec_payment_types_id_c ORDER BY total DESC");

		$rowsByChi = []; $grandCntChi = 0; $grandTotalChi = 0;
		while ($r = $db->fetchByAssoc($rsD)) {
			$rowsByChi[]    = $r;
			$grandCntChi   += (int)$r['cnt'];
			$grandTotalChi += (float)$r['total'];
		}

		$rsE = $db->query("SELECT $exprChi AS period, pt.id AS loai_chi_id, pt.name AS loai_chi_name,
				SUM(pv.amount) AS total
			FROM ec_payment_voucher pv
			LEFT JOIN ec_payment_types pt ON pt.deleted = 0 AND pt.id = pv.ec_payment_types_id_c
			WHERE $whereChi GROUP BY period, pv.ec_payment_types_id_c ORDER BY period ASC");

		$matrixChi = []; $periodsChi = []; $typesChi = []; // id => name
		while ($r = $db->fetchByAssoc($rsE)) {
			$matrixChi[$r['period']][$r['loai_chi_id']] = (float)$r['total'];
			$periodsChi[$r['period']] = true;
			$typesChi[$r['loai_chi_id']] = $r['loai_chi_name'];
		}

		$html = $this->renderHtml(
			$post_fdate, $post_tdate, $group_by,
			$rowsByType, $rowsByForm, $matrixThu, array_keys($periodsThu), array_keys($typesThu), $grandCntThu, $grandTotalThu,
			$rowsByChi, $matrixChi, array_keys($periodsChi), $typesChi, $grandCntChi, $grandTotalChi
		);

		return ['html' => $html];
	}

	function getPeriodExpr($group_by, $alias = 'p', $field = 'ngayhachtoan')
	{
		$col = $alias . '.' . $field;
		switch ($group_by) {
			case 'day':   return "DATE_FORMAT($col, '%d/%m/%Y')";
			case 'week':  return "CONCAT('Tuần ', WEEK($col, 3), '/', YEAR($col))";
			case 'year':  return "DATE_FORMAT($col, '%Y')";
			default:      return "DATE_FORMAT($col, '%m/%Y')";
		}
	}

	function renderHtml(
		$post_fdate, $post_tdate, $group_by,
		$rowsByType, $rowsByForm, $matrixThu, $periodsThu, $typesThu, $grandCntThu, $grandTotalThu,
		$rowsByChi, $matrixChi, $periodsChi, $typesChi, $grandCntChi, $grandTotalChi
	) {
		global $app_list_strings;
		$loaiThuLabels    = $app_list_strings['loai_thu_list']     ?? [];
		$receiptTypeLabel = $app_list_strings['receipt_type_list'] ?? [];
		$groupLabel       = ['day' => 'ngày', 'week' => 'tuần', 'month' => 'tháng', 'year' => 'năm'];
		$gLbl             = $groupLabel[$group_by] ?? 'tháng';

		$html  = '<div class="baocaothuchi-report box-section">';
		$html .= '<h3 class="text-center fw-bold">BÁO CÁO THU CHI</h3>';
		$html .= '<p class="text-center fst-italic mb-3">Từ ngày ' . htmlspecialchars($post_fdate) . ' đến ngày ' . htmlspecialchars($post_tdate) . '</p>';

		// ======================================================
		// PHẦN I: THU
		// ======================================================
		$html .= '<h4 class="fw-bold text-primary border-bottom border-primary pb-1 mt-3">PHẦN I — BÁO CÁO THU</h4>';

		// I.1 by loai_thu
		$html .= '<h5 class="border-start border-primary border-4 mt-3 ps-3 fs-6">1. Tổng hợp theo loại thu</h5>';
		$html .= '<table class="table table-bordered table-details__booking" cellpadding="5" cellspacing="0" border="1">';
		$html .= '<thead class="align-middle"><tr>
					<th width="5%" class="text-center">#</th>
					<th>Loại thu</th>
					<th width="12%" class="text-center">Số phiếu</th>
					<th width="20%" class="text-end">Số tiền (VNĐ)</th>
					<th width="10%" class="text-end">Tỷ trọng</th>
				</tr></thead><tbody>';
		$stt = 0;
		foreach ($rowsByType as $r) {
			$stt++;
			$lt       = $r['loai_thu'];
			$ltName   = $loaiThuLabels[(int)$lt] ?? ($lt !== null && $lt !== '' ? '#' . htmlspecialchars($lt) : '<i>(Không phân loại)</i>');
			$ratio    = $grandTotalThu > 0 ? ($r['total'] / $grandTotalThu * 100) : 0;
			$drillLbl = htmlspecialchars(strip_tags($ltName), ENT_QUOTES);
			$html .= '<tr class="bct-drill-row" data-kind="thu" data-drill="loai_thu" data-drill-val="' . htmlspecialchars((string)$lt, ENT_QUOTES) . '" data-drill-label="' . $drillLbl . '">
						<td class="text-center">' . $stt . '</td>
						<td>' . $ltName . '</td>
						<td class="text-center">' . format_number($r['cnt']) . '</td>
						<td class="text-end">' . format_number($r['total']) . '</td>
						<td class="text-end">' . number_format($ratio, 2) . '%</td>
					</tr>';
		}
		$html .= '<tr class="fw-bold table-secondary"><td colspan="2" class="text-end">TC</td>
					<td class="text-center">' . format_number($grandCntThu) . '</td>
					<td class="text-end">' . format_number($grandTotalThu) . '</td>
					<td class="text-end">100%</td></tr>';
		$html .= '</tbody></table>';

		// I.2 by receipt_type
		$html .= '<h5 class="border-start border-primary border-4 mt-3 ps-3 fs-6">2. Tổng hợp theo hình thức thu</h5>';
		$html .= '<table class="table table-bordered table-details__booking" cellpadding="5" cellspacing="0" border="1">';
		$html .= '<thead class="align-middle"><tr>
					<th>Hình thức</th>
					<th width="20%" class="text-center">Số phiếu</th>
					<th width="30%" class="text-end">Số tiền (VNĐ)</th>
				</tr></thead><tbody>';
		foreach ($rowsByForm as $r) {
			$formName = $receiptTypeLabel[$r['receipt_type']] ?? ($r['receipt_type'] ?: '<i>(Không rõ)</i>');
			$drillLbl = htmlspecialchars(strip_tags($formName), ENT_QUOTES);
			$html .= '<tr class="bct-drill-row" data-kind="thu" data-drill="receipt_type" data-drill-val="' . htmlspecialchars((string)$r['receipt_type'], ENT_QUOTES) . '" data-drill-label="' . $drillLbl . '">
					<td>' . $formName . '</td>
					<td class="text-center">' . format_number($r['cnt']) . '</td>
					<td class="text-end">' . format_number($r['total']) . '</td></tr>';
		}
		$html .= '</tbody></table>';

		// I.3 matrix period x loai_thu
		$html .= '<h5 class="border-start border-primary border-4 mt-3 ps-3 fs-6">3. Tổng hợp thu theo ' . $gLbl . '</h5>';
		$html .= $this->renderMatrix($matrixThu, $periodsThu, $typesThu, $loaiThuLabels, true, 'thu');

		// ======================================================
		// PHẦN II: CHI
		// ======================================================
		$html .= '<h4 class="fw-bold text-danger border-bottom border-danger pb-1 mt-4">PHẦN II — BÁO CÁO CHI</h4>';

		// II.1 by loai_chi
		$html .= '<h5 class="border-start border-danger border-4 mt-3 ps-3 fs-6">1. Tổng hợp theo loại chi</h5>';
		$html .= '<table class="table table-bordered table-details__booking" cellpadding="5" cellspacing="0" border="1">';
		$html .= '<thead class="align-middle"><tr>
					<th width="5%" class="text-center">#</th>
					<th>Loại chi</th>
					<th width="12%" class="text-center">Số phiếu</th>
					<th width="20%" class="text-end">Số tiền (VNĐ)</th>
					<th width="10%" class="text-end">Tỷ trọng</th>
				</tr></thead><tbody>';
		$stt = 0;
		foreach ($rowsByChi as $r) {
			$stt++;
			$chiName  = $r['loai_chi_name'] ?: '<i>(Không phân loại)</i>';
			$ratio    = $grandTotalChi > 0 ? ($r['total'] / $grandTotalChi * 100) : 0;
			$drillVal = (string)$r['loai_chi_id'];
			$drillLbl = htmlspecialchars(strip_tags($chiName), ENT_QUOTES);
			$html .= '<tr class="bct-drill-row" data-kind="chi" data-drill="loai_chi" data-drill-val="' . htmlspecialchars($drillVal, ENT_QUOTES) . '" data-drill-label="' . $drillLbl . '">
						<td class="text-center">' . $stt . '</td>
						<td>' . htmlspecialchars($chiName) . '</td>
						<td class="text-center">' . format_number($r['cnt']) . '</td>
						<td class="text-end">' . format_number($r['total']) . '</td>
						<td class="text-end">' . number_format($ratio, 2) . '%</td>
					</tr>';
		}
		$html .= '<tr class="fw-bold table-secondary"><td colspan="2" class="text-end">TC</td>
					<td class="text-center">' . format_number($grandCntChi) . '</td>
					<td class="text-end">' . format_number($grandTotalChi) . '</td>
					<td class="text-end">100%</td></tr>';
		$html .= '</tbody></table>';

		// II.2 matrix period x loai_chi
		$html .= '<h5 class="border-start border-danger border-4 mt-3 ps-3 fs-6">2. Tổng hợp chi theo ' . $gLbl . '</h5>';
		$html .= $this->renderMatrix($matrixChi, $periodsChi, array_keys($typesChi), $typesChi, false, 'chi');

		// ======================================================
		// PHẦN III: TỔNG HỢP
		// ======================================================
		$net = $grandTotalThu - $grandTotalChi;
		$html .= '<h4 class="fw-bold text-success border-bottom border-success pb-1 mt-4">PHẦN III — TỔNG HỢP</h4>';
		$html .= '<table class="table table-bordered table-details__booking" cellpadding="5" cellspacing="0" border="1">';
		$html .= '<thead class="align-middle"><tr><th class="text-start">Chỉ tiêu</th><th width="25%" class="text-end">Số tiền (VNĐ)</th></tr></thead><tbody>';
		$html .= '<tr><td>Tổng thu</td><td class="text-end text-primary fw-semibold">' . format_number($grandTotalThu) . '</td></tr>';
		$html .= '<tr><td>Tổng chi</td><td class="text-end text-danger fw-semibold">' . format_number($grandTotalChi) . '</td></tr>';
		$netClass = $net >= 0 ? 'text-success' : 'text-danger';
		$html .= '<tr class="fw-bold"><td>Chênh lệch (Thu - Chi)</td>
					<td class="text-end ' . $netClass . '">' . format_number($net) . '</td></tr>';
		$html .= '</tbody></table>';

		$html .= '</div>';
		return $html;
	}

	function renderMatrix($matrix, $periods, $typeKeys, $typeLabels, $useIntKey, $kind = 'thu')
	{
		if (empty($periods) || empty($typeKeys)) {
			return '<p><i>Không có dữ liệu.</i></p>';
		}
		$drillCol = $kind === 'thu' ? 'matrix_thu' : 'matrix_chi';
		$html  = '<div style="overflow-x:auto"><table class="table table-bordered table-details__booking" cellpadding="5" cellspacing="0" border="1">';
		$html .= '<thead class="align-middle"><tr><th>Kỳ</th>';
		foreach ($typeKeys as $k) {
			$lbl  = $useIntKey ? ($typeLabels[(int)$k] ?? ('#' . htmlspecialchars($k))) : ($typeLabels[$k] ?? htmlspecialchars($k));
			$html .= '<th class="text-end">' . $lbl . '</th>';
		}
		$html .= '<th class="text-end">Tổng</th></tr></thead><tbody>';

		$colTotals = array_fill_keys($typeKeys, 0);
		$sumAll    = 0;
		foreach ($periods as $p) {
			$html .= '<tr><td>' . htmlspecialchars($p) . '</td>';
			$rowSum = 0;
			foreach ($typeKeys as $k) {
				$v = $matrix[$p][$k] ?? 0;
				$rowSum        += $v;
				$colTotals[$k] += $v;
				if ($v) {
					$lbl  = $useIntKey ? ($typeLabels[(int)$k] ?? ('#' . $k)) : ($typeLabels[$k] ?? $k);
					$dlbl = htmlspecialchars(strip_tags($lbl) . ' — ' . $p, ENT_QUOTES);
					$html .= '<td class="text-end bct-drill-cell" data-kind="' . $kind . '" data-drill="' . $drillCol
						. '" data-drill-val="' . htmlspecialchars((string)$k, ENT_QUOTES)
						. '" data-period="' . htmlspecialchars($p, ENT_QUOTES)
						. '" data-drill-label="' . $dlbl . '">' . format_number($v) . '</td>';
				} else {
					$html .= '<td class="text-end">&nbsp;</td>';
				}
			}
			$sumAll += $rowSum;
			$html   .= '<td class="text-end fw-bold">' . format_number($rowSum) . '</td></tr>';
		}
		$html .= '<tr class="fw-bold table-secondary"><td>TC</td>';
		foreach ($typeKeys as $k) {
			$html .= '<td class="text-end">' . format_number($colTotals[$k]) . '</td>';
		}
		$html .= '<td class="text-end">' . format_number($sumAll) . '</td></tr>';
		$html .= '</tbody></table></div>';
		return $html;
	}
}
