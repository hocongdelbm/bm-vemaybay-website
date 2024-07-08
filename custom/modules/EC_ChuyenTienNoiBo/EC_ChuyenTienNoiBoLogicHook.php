<?php
class EC_ChuyenTienNoiBoLogicHook {
	// Kiểm tra trước khi xóa
	function CheckBeforeDelete($focus, $event, $arguments){
		if($focus->ghiso == 1){
			header("Location: index.php?module=EC_ChuyenTienNoiBo&action=Error&error_string=".urlencode("Chứng từ đã ghi sổ. Vui lòng kiểm tra lại."));
			exit();
		}
	}
}
