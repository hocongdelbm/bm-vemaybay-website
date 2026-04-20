<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewbaocaothuchi_detail extends SugarView
{
	function display()
	{
		@ob_clean();
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this->buildJson());
		exit();
	}

	function preDisplay() {}

	function buildJson()
	{
		global $db, $app_list_strings;

		$kind     = $_REQUEST['kind'] ?? 'thu';
		$page     = max(1, (int)($_REQUEST['page'] ?? 1));
		$pageSize = 20;

		$post_fdate = !empty($_REQUEST['from_date']) ? $_REQUEST['from_date'] : date('01-m-Y');
		$post_tdate = !empty($_REQUEST['to_date'])   ? $_REQUEST['to_date']   : date('d-m-Y');
		$from = date('Y-m-d', strtotime($post_fdate));
		$to   = date('Y-m-d', strtotime($post_tdate));

		$location_id  = array_values(array_filter((array)($_REQUEST['location_id']  ?? []), fn($v) => $v !== ''));
		$receipt_type = array_values(array_filter((array)($_REQUEST['receipt_type'] ?? []), fn($v) => $v !== ''));
		$rv_status    = array_values(array_filter((array)($_REQUEST['rv_status']    ?? []), fn($v) => $v !== ''));
		$loai_thu     = array_values(array_filter((array)($_REQUEST['loai_thu']     ?? []), fn($v) => $v !== ''));
		$pv_status    = array_values(array_filter((array)($_REQUEST['pv_status']    ?? []), fn($v) => $v !== ''));
		$loai_chi     = array_values(array_filter((array)($_REQUEST['loai_chi']     ?? []), fn($v) => $v !== ''));

		$drill        = $_REQUEST['drill']        ?? '';
		$drill_val    = $_REQUEST['drill_val']    ?? '';
		$drill_period = $_REQUEST['drill_period'] ?? '';
		$group_by     = $_REQUEST['group_by']     ?? 'month';

		if ($drill_period !== '') {
			$range = $this->getPeriodDateRange($group_by, $drill_period);
			if ($range) {
				$from = $range[0];
				$to   = $range[1];
			}
		}

		if ($kind === 'thu') {
			if ($drill === 'loai_thu' || $drill === 'matrix_thu') {
				$loai_thu = [$drill_val];
			} elseif ($drill === 'receipt_type') {
				$receipt_type = [$drill_val];
			}

			$where  = " p.deleted = 0 ";
			$where .= " AND DATE(p.ngayhachtoan) BETWEEN '" . $db->quote($from) . "' AND '" . $db->quote($to) . "' ";
			if (!empty($location_id))  { $q = implode("','", array_map([$db,'quote'],$location_id));  $where .= " AND p.com_location_id IN ('".$q."') "; }
			if (!empty($receipt_type)) { $q = implode("','", array_map([$db,'quote'],$receipt_type)); $where .= " AND p.receipt_type IN ('".$q."') "; }
			if (!empty($rv_status))    { $q = implode("','", array_map([$db,'quote'],$rv_status));    $where .= " AND p.rv_status IN ('".$q."') "; }
			if (!empty($loai_thu))     { $q = implode("','", array_map([$db,'quote'],$loai_thu));     $where .= " AND p.loai_thu IN ('".$q."') "; }

			$cntRs = $db->query("SELECT COUNT(p.id) AS c, SUM(IFNULL(p.amount_converted, p.amount)) AS s
				FROM ec_receipt_voucher p WHERE $where");
			$cntR  = $db->fetchByAssoc($cntRs);
			$total = (int)($cntR['c'] ?? 0);
			$sumAmount = (float)($cntR['s'] ?? 0);

			$offset = ($page - 1) * $pageSize;
			$sql = "SELECT p.id, p.name, p.ngayhachtoan, p.loai_thu, p.receipt_type, p.guest_name,
						IFNULL(p.amount_converted, p.amount) AS amount, p.rv_status
					FROM ec_receipt_voucher p
					WHERE $where
					ORDER BY p.ngayhachtoan DESC, p.name DESC
					LIMIT $offset, $pageSize";
			$rs = $db->query($sql);

			$loaiLabels = $app_list_strings['loai_thu_list']              ?? [];
			$rtLabels   = $app_list_strings['receipt_type_list']          ?? [];
			$stLabels   = $app_list_strings['receipt_voucher_status_list']?? [];
			$rows = [];
			while ($r = $db->fetchByAssoc($rs)) {
				$rows[] = [
					'id'        => $r['id'],
					'name'      => $r['name'],
					'ngay'      => $r['ngayhachtoan'] ? date('d/m/Y', strtotime($r['ngayhachtoan'])) : '',
					'loai'      => $loaiLabels[(int)$r['loai_thu']] ?? $r['loai_thu'],
					'hinh_thuc' => $rtLabels[$r['receipt_type']] ?? $r['receipt_type'],
					'nguoi'     => $r['guest_name'],
					'amount'    => (float)$r['amount'],
					'status'    => $stLabels[$r['rv_status']] ?? $r['rv_status'],
				];
			}

			return [
				'ok'          => true,
				'kind'        => 'thu',
				'page'        => $page,
				'page_size'   => $pageSize,
				'total'       => $total,
				'total_pages' => $total > 0 ? (int)ceil($total / $pageSize) : 0,
				'sum_amount'  => $sumAmount,
				'module'      => 'EC_Receipt_Voucher',
				'period_from' => $from,
				'period_to'   => $to,
				'rows'        => $rows,
			];
		}

		// CHI
		if ($drill === 'loai_chi' || $drill === 'matrix_chi') {
			$loai_chi = [$drill_val];
		}

		$where  = " pv.deleted = 0 ";
		$where .= " AND DATE(pv.ngaychungtu) BETWEEN '" . $db->quote($from) . "' AND '" . $db->quote($to) . "' ";
		if (!empty($pv_status)) { $q = implode("','", array_map([$db,'quote'],$pv_status)); $where .= " AND pv.pv_status IN ('".$q."') "; }
		if (!empty($loai_chi))  { $q = implode("','", array_map([$db,'quote'],$loai_chi));  $where .= " AND pv.ec_payment_types_id_c IN ('".$q."') "; }

		$cntRs = $db->query("SELECT COUNT(pv.id) AS c, SUM(pv.amount) AS s FROM ec_payment_voucher pv WHERE $where");
		$cntR  = $db->fetchByAssoc($cntRs);
		$total = (int)($cntR['c'] ?? 0);
		$sumAmount = (float)($cntR['s'] ?? 0);

		$offset = ($page - 1) * $pageSize;
		$sql = "SELECT pv.id, pv.name, pv.ngaychungtu, pt.name AS loai_chi_name, pv.receipent_name,
					pv.amount, pv.pv_status
				FROM ec_payment_voucher pv
				LEFT JOIN ec_payment_types pt ON pt.deleted = 0 AND pt.id = pv.ec_payment_types_id_c
				WHERE $where
				ORDER BY pv.ngaychungtu DESC, pv.name DESC
				LIMIT $offset, $pageSize";
		$rs = $db->query($sql);

		$pvStLabels = $app_list_strings['payment_voucher_status_list'] ?? [];
		$rows = [];
		while ($r = $db->fetchByAssoc($rs)) {
			$rows[] = [
				'id'     => $r['id'],
				'name'   => $r['name'],
				'ngay'   => $r['ngaychungtu'] ? date('d/m/Y', strtotime($r['ngaychungtu'])) : '',
				'loai'   => $r['loai_chi_name'],
				'nguoi'  => $r['receipent_name'],
				'amount' => (float)$r['amount'],
				'status' => $pvStLabels[$r['pv_status']] ?? $r['pv_status'],
			];
		}

		return [
			'ok'          => true,
			'kind'        => 'chi',
			'page'        => $page,
			'page_size'   => $pageSize,
			'total'       => $total,
			'total_pages' => $total > 0 ? (int)ceil($total / $pageSize) : 0,
			'sum_amount'  => $sumAmount,
			'module'      => 'EC_Payment_Voucher',
			'period_from' => $from,
			'period_to'   => $to,
			'rows'        => $rows,
		];
	}

	function getPeriodDateRange($group_by, $period)
	{
		$period = trim($period);
		switch ($group_by) {
			case 'day':
				$dt = DateTime::createFromFormat('d/m/Y', $period);
				if (!$dt) return null;
				$d = $dt->format('Y-m-d');
				return [$d, $d];
			case 'week':
				if (!preg_match('/(\d+)\/(\d{4})/u', $period, $m)) return null;
				$week = (int)$m[1]; $year = (int)$m[2];
				$dt = new DateTime();
				$dt->setISODate($year, $week);
				$from = $dt->format('Y-m-d');
				$dt->modify('+6 days');
				return [$from, $dt->format('Y-m-d')];
			case 'year':
				$y = (int)$period;
				return ["$y-01-01", "$y-12-31"];
			default:
				if (!preg_match('/(\d{1,2})\/(\d{4})/', $period, $m)) return null;
				$from = sprintf('%s-%02d-01', $m[2], (int)$m[1]);
				$to   = date('Y-m-t', strtotime($from));
				return [$from, $to];
		}
	}
}
