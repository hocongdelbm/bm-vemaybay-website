<?php
class EC_Bank_AccountLogicHook {
	function saveToFileJson($focus, $event, $arguments){
		$sql = "SELECT b.name,
					b.short_name,
					ba.account_holder,
					ba.account_number,
					ba.branch,
					u.department_id
				FROM ec_bank_account ba
					LEFT JOIN ec_banks b ON ba.bank_id = b.id AND b.deleted = 0
					LEFT JOIN users u ON ba.assigned_user_id = u.id AND u.deleted = 0
				WHERE ba.deleted = 0 AND ba.is_display = 1 AND ba.unfollow = 0
				ORDER BY ba.sort ";

		$res = $focus->db->query($sql);
		$arr = array();
		while($row = $focus->db->fetchByAssoc($res)){
			$arr['data'][] = array(
				'name' => $row['name'],
				'short_name' => $row['short_name'],
				'owner' => $row['account_holder'],
				'account' => $row['account_number'],
				'branch' => $row['branch'],
				'department_id' => $row['department_id']
			);
		}
		file_put_contents('custom/banklist.json', json_encode($arr));
	}
}
?>
