<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

trait PermissionsTrait
{
	public function checkIsPaidNote($booking_id)
	{
		// Kiểm tra booking đã có working_process paid=1 hay chưa để quyết định hiển thị trạng thái đã thanh toán.
		$sql = "SELECT COUNT(id) 
				FROM ec_working_process
				WHERE parent_id = '$booking_id' AND paid = 1 AND deleted = 0";
		$res = $this->bean->db->getOne($sql);
		if ($res > 0)
			return true;
		return false;
	}

	//Kiểm tra có phải telesale hay không
	public function isTelesaleRole($user_id)
	{
		// Kiểm tra user có role telesale hay không, dùng để ẩn/hiện một số action như Auto book.
		$sql = 'SELECT COUNT(id) 
			FROM acl_roles_users 
			WHERE user_id = "' . $user_id . '" AND role_id = "34beb2a2-5ee7-f001-2496-68ca264d1d3f" AND deleted = 0';
		return $this->bean->db->getOne($sql);
	}
}
