<?php
class EC_HoanVeLogicHook {
	
	function checkBeforeDelete($focus, $event, $arguments){
		if(trim($focus->id) != ''){
			
			// tình trạng hoàn vé
			// 2: mới tạo
			// 1: đã hoàn
			// 0: đang hoàn
			
			if($focus->tinhtrang != '2'){
				header("Location: index.php?module=EC_HoanVe&action=Error&error_string=".urlencode("Bạn không được quyền xóa chứng từ này"));
				exit();
			}
			
			$update = "UPDATE ec_chitiethoanve SET deleted=1 
					   WHERE hoanve_id='".trim($focus->id)."' AND deleted=0 ";
			$focus->db->query($update);

			// Xóa KPI - working process
			myRemoveWorkingProcess($focus->module_dir, $focus->id, 'create_repaid');
		}
	}
	
	function coloringStatus($focus, $event, $arguments){
		global $app_list_strings;
		$focus->tinhtrang = '<label class="fw-semibold" style="color:'.$app_list_strings['tinhtranghoanvecolor_list'][$focus->tinhtrang].'; ">'.$app_list_strings['tinhtranghoanve_list'][$focus->tinhtrang].'</label>';
		
		$focus->booking = '<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record='.$focus->booking_id.'" target="_blank">'.$focus->booking.'</a>';
		
		// thông báo tình trạng đã chi tiền
		$total_paid = 0;
		$sql = "SELECT SUM(IFNULL(p.amount,0))
				FROM ec_payment_voucher p
				WHERE p.hoanve_id = '".$focus->id."'
					AND p.pv_status = '3'
					AND p.deleted = 0  ";
		$total_paid += $focus->db->getOne($sql);
		if($total_paid > 0)
			$focus->thongbao = format_number($total_paid);
		else
			$focus->thongbao = '<label style="color:red;">'.format_number($total_paid).'</label>';
	}
}
?>