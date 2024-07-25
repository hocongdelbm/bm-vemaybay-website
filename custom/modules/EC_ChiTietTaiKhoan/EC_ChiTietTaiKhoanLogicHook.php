<?php
class EC_ChiTietTaiKhoanLogicHook
{
	function checkBeforeDelete($focus, $event, $arguments) {
		if(!empty($focus->location_id)) {
			$custom_where = ' AND location_id = "' . $focus->location_id . '"';
		// tài khoản ngân hàng
		} else if(!empty($focus->parent_id)) {
			$custom_where = ' AND parent_id = "' . $focus->parent_id . '"';
		//công ty
		} else if(!empty($focus->company_id)) {
			$custom_where = ' AND company_id = "' . $focus->company_id . '"';
		}
		$focus->updateSDDK($custom_where, 1);
	}
}
