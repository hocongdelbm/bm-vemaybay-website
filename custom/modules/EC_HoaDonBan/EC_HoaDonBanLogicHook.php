<?php
class EC_HoaDonBanLogicHook {
	public function custom_column_list_view(SugarBean $bean, $event, $arguments) {
		// Invoice number
		switch($bean->company_unit) {
			case 'MHV':
			    $bean->sohoadon = (int)$bean->sohoadon;
			    break;
			default:
			    $bean->sohoadon;
		}

		// Get represent booking
		$sqlBookingInfo = "SELECT booking_id, booking, COUNT(*) as count
			FROM ec_chitiethoadon
			WHERE parent_id = '{$bean->id}'
				AND parent_type = 'EC_HoaDonBan'
				AND deleted = 0
			GROUP BY booking_id
			ORDER BY count DESC
			LIMIT 1";
		$resBookingInfo = $GLOBALS['db']->query($sqlBookingInfo);
		$rowBookingInfo = $GLOBALS['db']->fetchByAssoc($resBookingInfo);
		$bean->represent_booking = "<a href='index.php?module=EC_Flight_Bookings&action=DetailView&record={$rowBookingInfo['booking_id']}' target='_blank'>{$rowBookingInfo['booking']}</a>";
	
		// Info customer/company
		$infoName = $bean->loaikh == '0' ? $bean->tencongty : $bean->lienhe;
		$itemIdentityNumber = '';
		if($bean->loaikh == '1') {
			if($bean->passport_number && !empty($bean->passport_number)) {
				$itemIdentityNumber = "<div class='d-flex justify-content-start mb-1'>
					<b style='width:20%;'>Passport:</b>
					<span style='flex:1; letter-spacing:1.5px;'>{$bean->passport_number}</span>
				</div>";
			}
			else {
				$itemIdentityNumber = "<div class='d-flex justify-content-start mb-1'>
					<b style='width:20%;'>CCCD:</b>
					<span style='flex:1;'>{$bean->citizen_id}</span>
				</div>";
			}
		}
		$bean->masothue = "<div style='width:350px;'>
			<div class='d-flex justify-content-start mb-1'>
				<b style='width:20%;'>KH/Cty:</b>
				<b style='flex:1;'>{$infoName}</b>
			</div>
			<div class='d-flex justify-content-start mb-1'>
				<b style='width:20%;'>MST:</b>
				<span style='flex:1;'>{$bean->masothue}</span>
			</div>
			{$itemIdentityNumber}
			<div class='d-flex justify-content-start mb-1'>
				<b style='width:20%;'>Email:</b>
				<span style='flex:1;'>{$bean->email}</span>
			</div>
			<div class='d-flex justify-content-start mb-1'>
				<b style='width:20%;'>Địa chỉ:</b>
				<span style='flex:1;'>{$bean->diachi}</span>
			</div>
		</div>";
	}

	public function checkBeforeDelete($focus, $event, $arguments) {
		if (trim($focus->id) != '') {
			$update = "UPDATE ec_chitiethoadon
				SET deleted = 1 
				WHERE parent_id = '" . trim(stripslashes($focus->id)) . "' 
					AND parent_type = 'EC_HoaDonBan'
					AND deleted = 0";
			$focus->db->query($update);
		}
	}

	public function calculateAmount($focus, $event, $arguments) {
		$sql = "SELECT
			SUM(dongia * soluong) AS tongtruocvat,
			SUM(tienthue * soluong) AS vat,
			SUM(phithuho * soluong) AS thuho
		FROM ec_chitiethoadon
		WHERE parent_id = '{$focus->id}' AND deleted = 0";

		$res = $focus->db->query($sql);
		$row = $focus->db->fetchByAssoc($res);
		$focus->tongtruocvat = format_number($row['tongtruocvat']);
		$focus->tongvat 	 = format_number($row['vat']);
		$focus->tongthuho 	 = format_number($row['thuho']);
	}

	function showCustomFields($focus, $event, $arguments) {
		// Cột tên công ty thể hiện họ tên khách hàng nếu thiếu tên công ty
		if (empty($focus->tencongty) && !empty($focus->lienhe)) $focus->tencongty = $focus->lienhe;

		// Kiểm tra các dòng chi tiết của hoá đơn
		$sql = "SELECT IF(COUNT(id) > 0, 1, 0)
			FROM ec_chitiethoadon 
			WHERE parent_id = '{$focus->id}'
				AND (booking_id IS NULL OR ticket_number_id IS NULL)
				AND deleted = 0";

		$has_wrong_dt = $focus->db->getOne($sql);
		if ($has_wrong_dt) {
			$focus->name = '<font color="red"><b>' . $focus->name . '</b></font>';
			if (!empty($focus->description)) $focus->description .= '<br>';
			$focus->description .= '<font color="red">Thiếu thông tin BK / số vé.</font>';
		}

		// Kiểm tra số hoá đơn có đủ 8 ký tự
		// if(strlen($focus->sohoadon) != 8) {
		// 	if(!$has_wrong_dt) {
		// 		$focus->name = '<font color="red"><b>' . $focus->name . '</b></font>';
		// 	}
		// 	$focus->description .= '<font color="red">Số hoá đơn chưa đủ 8 ký tự.</font>';
		// }
	}
}
