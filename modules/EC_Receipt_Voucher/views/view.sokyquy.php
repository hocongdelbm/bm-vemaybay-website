<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewsokyquy extends SugarView {
	function display() {
		$smartyCont = new Sugar_Smarty();
		$this->populateCont($smartyCont);
		$smartyCont->display('modules/EC_Receipt_Voucher/tpls/sokyquy.tpl');
	}

	function populateCont($smartyobj) {
		global $app_list_strings, $db;

		$sql_search = "";
		// Từ ngày
		if (isset($_POST['tungay']) && !empty($_POST['tungay'])) {
			$sql_search .= " AND DATE(p.ngayhachtoan) >= '" . date('Y-m-d', strtotime($_POST['tungay'])) . "' ";
			$post_tungay = $_POST['tungay'];
		} else {
			$sql_search .= " AND DATE(p.ngayhachtoan) >= '" . date('Y-m-d') . "' ";
			$post_tungay = date('d-m-Y');
		}

		// Đến ngày
		if (isset($_POST['denngay']) && !empty($_POST['denngay'])) {
			$sql_search .= " AND DATE(p.ngayhachtoan) <= '" . date('Y-m-d', strtotime($_POST['denngay'])) . "' ";
			$post_denngay = $_POST['denngay'];
		} else {
			$sql_search .= " AND DATE(p.ngayhachtoan) <= '" . date('Y-m-d') . "' ";
			$post_denngay = date('d-m-Y');
		}

		// Xử lý hiển thị
		$doituong_arr_cnt = isset($_POST['doituong_id']) ? count($_POST['doituong_id']) : 0;

		$html = '';
		for ($i = 0; $i < $doituong_arr_cnt; $i++) {
			$voucher_arr = $this->getVoucherList('144', $_POST['doituong_id'][$i], $post_tungay, $sql_search);

			$html .= '<table id="table-wrapper" cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td valign="top">
						<p>
						' . $app_list_strings['company_info_list']['name'] . '<br />
						' . $app_list_strings['company_info_list']['address'] . '<br />
						Mã số thuế: ' . $app_list_strings['company_info_list']['taxcode'] . '
						</p>
					</td>
					<td valign="top" align="center">
						&nbsp;
					</td>
				</tr>
			  
				<tr>
					<td colspan="2" align="center">
					<br />
						<label style="font-weight:bold; font-size:15pt">SỔ THEO DÕI KÝ QUỸ</label><br />
						<label style="font-weight:bold; font-style:italic">Từ ngày ' . $post_tungay . ' đến ngày ' . $post_denngay . '</label>
					<br />
					<br />
					</td>
				</tr>
			  
				<tr>
					<td colspan="2" align="left">
						<label style="font-weight:bold;">Mã đối tượng: ' . $_POST['madt_' . $_POST['doituong_id'][$i]] . '</label><br />
						<label style="font-weight:bold;">Tên đối tượng: ' . $_POST['tendt_' . $_POST['doituong_id'][$i]] . '</label><br />
						<br />
					</td>
				</tr>
			  
				<tr>
					<td colspan="2">
						<table id="table-details" width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
							<td rowspan="2"><div align="center"><strong>Ngày, tháng<br /> ghi sổ</strong></div></td>
							<td rowspan="2"><div align="center"><strong>Ngày, tháng<br /> chứng từ</strong></div></td>
							<td colspan="2"><div align="center"><strong>Số hiệu chứng từ</strong></div></td>
							<td rowspan="2"><div align="center"><strong>Diễn giải</strong></div><div align="center"></div></td>
							<td colspan="3"><div align="center"><strong>Số tiền</strong></div></td>
						</tr>
							<tr>
							<td><div align="center"><strong>Thu</strong></div></td>
							<td><div align="center"><strong>Chi</strong></div></td>
							<td><div align="center"><strong>Thu</strong></div></td>
							<td><div align="center"><strong>Chi</strong></div></td>
							<td><div align="center"><strong>Tồn</strong></div></td>
						</tr>
						<tr>
							<td width="11%"><div align="center"><strong>A</strong></div></td>
							<td width="11%"><div align="center"><strong>B</strong></div></td>
							<td width="12%"><div align="center"><strong>C</strong></div></td>
							<td width="12%"><div align="center"><strong>D</strong></div></td>
							<td width="24%"><div align="center"><strong>E</strong></div></td>
							<td width="10%"><div align="center"><strong>1</strong></div></td>
							<td width="10%"><div align="center"><strong>2</strong></div></td>
							<td width="10%"><div align="center"><strong>3</strong></div></td>
						</tr>
						' . $voucher_arr['html'] . '
						<tr>
							<td colspan="5" ><label style="font-weight:bold">Tổng cộng:</label></td>
							<td align="right" ><label style="font-weight:bold">' . format_number($voucher_arr['tongthu']) . '</label></td>
							<td align="right" ><label style="font-weight:bold">' . format_number($voucher_arr['tongchi']) . '</label></td>
							<td align="right" ><label style="font-weight:bold">' . format_number($voucher_arr['tongton']) . '</label></td>
						</tr>
							
					</table>
					</td>
				</tr>
			  
				<tr>
					<td colspan="2">
						<br />
						<table id="table-signed" width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td width="30%" align="center">&nbsp;</td>
								<td width="30%" align="center">&nbsp;</td>
								<td width="40%" align="center"><label style="font-style:italic">Ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y') . '</label></td>
							</tr>
							<tr>
								<td width="30%" align="center"><label style="font-weight:bold">Thủ Quỹ</label><br />
								<label style="font-style:italic">(Ký, họ tên)</label></td>
								<td width="30%" align="center"><label style="font-weight:bold">Kế toán trưởng</label><br />
								<label style="font-style:italic">(Ký, họ tên)</label></td>
								<td width="40%" align="center">
								<label style="font-weight:bold">Giám đốc</label><br />
								<label style="font-style:italic">(Ký, họ tên, đóng dấu)</label></td>
							</tr>
						</table>
					</td>
				</tr>
		  	</table>';
		}

		$smartyobj->assign('POST_TUNGAY', $post_tungay);
		$smartyobj->assign('POST_DENNGAY', $post_denngay);
		$smartyobj->assign('ACCOUNTING_OBJECT_LIST', $this->getObjectList());
		$smartyobj->assign('DATA', $html);
	}

	// Lấy danh sách các đối tượng
	function getObjectList() {
		global $db;
		$sql = "SELECT id, 
					name AS tendoituong,
					ticker_symbol AS madoituong,
					phone_office AS dienthoai,
					sic_code AS masothue,
					billing_address_street AS diachi 
				FROM accounts 
				WHERE deleted=0 AND account_type='Partner' ";
		$res = $db->query($sql);
		$html = '';
		$i = 0;
		while ($row = $db->fetchByAssoc($res)) {
			$html .= '<tr>
				<td align="center">
					<input type="checkbox" name="doituong_id[]" id="doituong_id' . $i . '" value="' . $row['id'] . '" />
					<input type="hidden" name="madt_' . $row['id'] . '" value="' . $row['madoituong'] . '" />
					<input type="hidden" name="tendt_' . $row['id'] . '" value="' . $row['tendoituong'] . '" />
					<input type="hidden" name="mst_' . $row['id'] . '" value="' . $row['masothue'] . '" />
					<input type="hidden" name="diachi_' . $row['id'] . '" value="' . $row['diachi'] . '" />
					<input type="hidden" name="dthoai_' . $row['id'] . '" value="' . $row['dienthoai'] . '" />
				</td>
				<td align="left">' . $row['madoituong'] . '</td>
				<td align="left">' . $row['masothue'] . '</td>
				<td align="left">' . $row['tendoituong'] . '</td>
				<td align="left">' . $row['dienthoai'] . '</td>
				<td align="left">' . $row['diachi'] . '</td>
			</tr>';
			$i++;
		}
		return $html;
	}

	// Lấy số tồn đầu kỳ của tài khoản tiền mặt
	function getTheOpeningAccount($sotk, $doituong_id, $post_tungay) {
		global $db;
		$sodauky = 0;
		$nam = date('Y', strtotime($post_tungay)) == date('Y') ? '' : date('Y', strtotime($post_tungay));
		$sql_sdk = "SELECT ABS((IFNULL(dunodau,0) - IFNULL(ducodau,0))) 
					FROM ec_chitiettaikhoan" . $nam . " 
					WHERE deleted=0 AND sotaikhoan='" . $sotk . "' AND parent_id='" . $doituong_id . "' AND parent_type='Accounts' ";
		$sodauky += $db->getOne($sql_sdk);

		$sql_search = "AND DATE(p.ngayhachtoan) >= '" . date('Y-01-01', strtotime($post_tungay)) . "' 
					   AND DATE(p.ngayhachtoan) < '" . date('Y-m-d', strtotime($post_tungay)) . "'";

		$sql = "SELECT p.name AS sochungtu,
					p.ngayhachtoan AS ngayghiso,
					p.amount_converted AS sotien,
					'-' AS pheptoan
				FROM ec_receipt_voucher p 
				WHERE p.deleted=0 AND p.amount IS NOT NULL 
					AND p.rv_status='1' 
					AND p.is_margin=1
					AND p.loai_thu<>'7'
					AND p.account_id_c='" . $doituong_id . "' " . $sql_search . "

				UNION

				SELECT p.name AS sochungtu,
					p.ngayhachtoan AS ngayghiso,
					p.amount_converted AS sotien,
					'+' AS pheptoan
				FROM ec_receipt_voucher p 
					WHERE p.deleted=0 AND p.amount IS NOT NULL 
						AND p.rv_status='1' 
						AND p.is_margin=0 
						AND p.loai_thu='7' 
						AND p.account_id_c='" . $doituong_id . "' " . $sql_search . "

				UNION

				SELECT p.name AS sochungtu,
					p.ngayhachtoan AS ngayghiso,
					p.amount AS sotien,
					'-' AS pheptoan
				FROM ec_receipt_voucher p 
				WHERE p.deleted=0 AND p.amount IS NOT NULL 
					AND p.pv_status='3'
					AND p.is_margin=1
					AND p.supplier_id='" . $doituong_id . "' " . $sql_search . "
				ORDER BY ngayghiso ";

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			if ($row['pheptoan'] == '+')
				$sodauky += $row['sotien'];
			else
				$sodauky -= $row['sotien'];
		}
		return $sodauky;
	}

	// Lấy danh sách tất cả các chứng từ phát sinh
	function getVoucherList($sotk, $doituong_id, $post_tungay, $sql_search) {
		global $db;
		$arr = array();
		// main query
		$sql = "SELECT p.ngayhachtoan AS ngayghiso,
					p.ngaychungtu,
					p.name AS sochungtu,
					p.description AS diengiai,
					p.amount_converted AS sotien
					'EC_Receipt_Voucher' AS parent_type,
					p.id AS parent_id,
					'-' AS pheptoan
				FROM ec_receipt_voucher p 
				WHERE p.deleted = 0 AND p.amount IS NOT NULL 
					AND p.rv_status = '1' 
					AND p.is_margin = 1
					AND p.loai_thu <> '7'
					AND p.account_id_c='" . $doituong_id . "' " . $sql_search . "

				UNION

				SELECT p.ngayhachtoan AS ngayghiso,
					p.ngaychungtu,
					p.name AS sochungtu,
					p.description AS diengiai,
					p.amount_converted AS sotien,
					'EC_Receipt_Voucher' AS parent_type,
					p.id AS parent_id,
					'+' AS pheptoan
				FROM ec_receipt_voucher p 
				WHERE p.deleted=0 AND p.amount IS NOT NULL 
					AND p.rv_status='1'
					AND p.is_margin=0
					AND p.loai_thu='7'
					AND p.account_id_c='" . $doituong_id . "' " . $sql_search . "

				UNION

				SELECT p.ngayhachtoan AS ngayghiso,
					p.ngaychungtu,
					p.name AS sochungtu
					p.description AS diengiai,
					p.amount AS sotien
					'EC_Payment_Voucher' AS parent_type,
					p.id AS parent_id,
					'-' AS pheptoan
				FROM ec_payment_voucher p 
				WHERE p.deleted = 0 AND p.amount IS NOT NULL 
					AND p.pv_status = '3'
					AND p.is_margin = 1
					AND p.supplier_id = '" . $doituong_id . "' " . $sql_search . "
				ORDER BY ngayghiso ";

		$res = $db->query($sql);

		$html = '';
		$tongthu = 0;
		$tongchi = 0;
		$tongton = 0;
		$sodauky = $this->getTheOpeningAccount($sotk, $doituong_id, $post_tungay);

		// Số đầu kỳ
		$html .= '<tr>
			<td align="center"><label>&nbsp;</label></td>
			<td align="center"><label>&nbsp;</label></td>
			<td><label>&nbsp;</label></td>
			<td><label>&nbsp;</label></td>
			<td><label style="font-weight:bold">Số đầu kỳ</label></td>
			<td align="right"><label>&nbsp;</label></td>
			<td align="right"><label>&nbsp;</label></td>
			<td align="right"><label>' . format_number($sodauky) . '</label></td>
        </tr>';

		$tongton += $sodauky;

		while ($row = $db->fetchByAssoc($res)) {
			if ($row['pheptoan'] == '+') {
				$chungtuthu = '<a href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['parent_id'] . '" target="_blank">' . $row['sochungtu'] . '</a>';
				$chungtuchi = '&nbsp;';
				$lbl_sotienthu = format_number($row['sotien']);
				$lbl_sotienchi = '&nbsp;';
				$tongthu += $row['sotien'];
				$tongton += $row['sotien'];
			} else {
				$chungtuthu = '&nbsp;';
				$chungtuchi = '<a href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['parent_id'] . '" target="_blank">' . $row['sochungtu'] . '</a>';
				$lbl_sotienthu = '&nbsp;';
				$lbl_sotienchi = format_number($row['sotien']);
				$tongchi += $row['sotien'];
				$tongton -= $row['sotien'];
			}

			$html .= '<tr>
				<td align="center">' . date('d/m/Y', strtotime($row['ngayghiso']) + 7 * 3600) . '</td>
				<td align="center">' . date('d/m/Y', strtotime($row['ngaychungtu'])) . '</td>
				<td>' . $chungtuthu . '</td>
				<td>' . $chungtuchi . '</td>
				<td>' . $row['diengiai'] . '</td>
				<td align="right">' . $lbl_sotienthu . '</td>
				<td align="right">' . $lbl_sotienchi . '</td>
				<td align="right">' . format_number($tongton) . '</td>
           	</tr>';
		}

		$arr['html'] = $html;
		$arr['tongthu'] = $tongthu;
		$arr['tongchi'] = $tongchi;
		$arr['tongton'] = $tongton;
		return $arr;
	}
}
