<?php
class EC_WorkingOverTimesLogicHook {
	function checkBeforeDelete($focus, $event, $argument) {
		// kiểm tra phiếu làm ngoài h
		// nếu đã duyệt thì không xoá
		if($focus->status == 2) {
			header("Location: index.php?module=EC_WorkingOverTimes&action=DetailView&record=".$focus->id);
			exit;
		} else {
			$sql = 'UPDATE ec_workingovertimedetails SET deleted = 1 
					WHERE deleted = 0 AND ec_workingovertimes_id_c = "' . $focus->id . '"';
			$focus->db->query($sql);
		}
	}

	function updateDetail($focus, $event, $argument) {
		// cập nhật trạng thái cho các dòng chi tiết
		$sql = 'UPDATE ec_workingovertimedetails 
				SET status = '.$focus->status.' 
				WHERE deleted = 0 AND ec_workingovertimes_id_c = "' . $focus->id . '"';
		$focus->db->query($sql);
	}
}