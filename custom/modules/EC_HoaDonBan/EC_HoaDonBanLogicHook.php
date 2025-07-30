<?php
class EC_HoaDonBanLogicHook
{
	public function custom_column_list_view(SugarBean $bean, $event, $arguments)
	{
		global $app_list_strings, $current_user;
		// if($current_user->user_name == 'hungnh'){
		// 	pr($bean->company_unit);
		// }
		switch($bean->company_unit) {
			case 'MHV':
			    $bean->sohoadon = (int)$bean->sohoadon;
			    break;
			default:
			    $bean->sohoadon;
		 }

	}

	function checkBeforeDelete($focus, $event, $arguments)
	{
		if (trim($focus->id) != '') {
			$update = "
				UPDATE ec_chitiethoadon
				SET deleted = 1 
				WHERE parent_id = '" . trim(stripslashes($focus->id)) . "' 
					AND parent_type = 'EC_HoaDonBan'
					AND deleted = 0
			";
			$focus->db->query($update);
		}
	}

	function calculateAmount($focus, $event, $arguments)
	{
		$sql = '
			SELECT
				SUM(dongia * soluong) AS tongtruocvat,
				SUM(tienthue * soluong) AS vat,
				SUM(phithuho * soluong) AS thuho
			FROM ec_chitiethoadon
			WHERE parent_id = "' . $focus->id . '" AND deleted = 0';

		$res = $focus->db->query($sql);
		$row = $focus->db->fetchByAssoc($res);
		$focus->tongtruocvat = format_number($row['tongtruocvat']);
		$focus->tongvat 	 = format_number($row['vat']);
		$focus->tongthuho 	 = format_number($row['thuho']);
	}

	function showCustomFields($focus, $event, $arguments)
	{

		$hd = new EC_HoaDonBan;
		$hd->retrieve($focus->id);

		// cột tên công ty thể hiện họ tên khách hàng nếu thiếu tên công ty
		if (!empty($hd->tencongty)) {
			$focus->tencongty = $hd->tencongty;
		} else if (!empty($hd->lienhe)) {
			$focus->tencongty = $hd->lienhe;
		}

		// kiểm tra các dòng chi tiết của hoá đơn
		$sql = '
			SELECT IF(COUNT(id) > 0, 1, 0)
			FROM ec_chitiethoadon 
			WHERE parent_id = "' . $focus->id . '"
				AND (booking_id IS NULL OR ticket_number_id IS NULL)
				AND deleted = 0
		';
		$has_wrong_dt = $focus->db->getOne($sql);
		if ($has_wrong_dt) {
			$focus->name = '<font color="red"><b>' . $focus->name . '</b></font>';
			if (!empty($focus->description)) $focus->description .= '<br>';
			$focus->description .= '<font color="red">Thiếu thông tin BK / số vé.</font>';
		}

		// kiểm tra số hoá đơn có đủ 8 ký tự
		// if(strlen($focus->sohoadon) != 8) {
		// 	if(!$has_wrong_dt) {
		// 		$focus->name = '<font color="red"><b>' . $focus->name . '</b></font>';
		// 	}
		// 	$focus->description .= '<font color="red">Số hoá đơn chưa đủ 8 ký tự.</font>';
		// }
	}
}
