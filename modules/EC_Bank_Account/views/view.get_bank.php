<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
		
class Viewget_bank extends SugarView {
	function display() {
		$smartyCont= new Sugar_Smarty();
		$this->populateCont($smartyCont);
		$smartyCont->display('modules/EC_Bank_Account/tpls/get_bank.tpl');
	}

	function populateCont($smartyobj){
		$smartyobj->assign('BANKS_LIST', $this->getListBanks());
    }

    function getListBanks(){
		$html 	 = '';
		$i 		 = 0;

		$sql = 'SELECT ba.id AS id,
						ba.account_number AS account_number,
						ba.account_holder AS account_holder,
						b.short_name AS short_name,
						ba.description,
						b.name,
						ba.lastest_get
				FROM ec_bank_account ba 
				INNER JOIN ec_banks b ON b.id = ba.bank_id
				WHERE ba.deleted = 0 AND ba.is_roll = 1
				ORDER BY ba.lastest_get
			';
		
		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$lastest_get = '';
			if (strtotime($row['lastest_get']) !== false) {
				$lastest_get = date('d-m-Y H:i:s', strtotime('+7 hour', strtotime($row['lastest_get'])));
			} 

			$html .= '
					<tr class="tr-bank" ln="' . ($i + 1) . '">
						<td class="fw-bold text-center col_no">' . ($i + 1) . '</td>
						<td class="text-center col_stk">' . $row['account_number'] . '</td>
						<td class="text-start col_bank">' . $row['name'] . '</td>
						<td class="text-center col_bank_short">' . $row['short_name'] . '</td>
						<td class="text-center col_owner">' . $row['account_holder'] . '</td>
						<td class="text-center col_lastest_get d-none">' . $lastest_get . '</td>
						<td class="text-center col_desc">' . $row['description'] . '</td>
						<td class="text-center">
							<div class="d-flex align-items-center justify-content-center flex-wrap gap-1">
								<input type="button" class="bank_btn flex-fill btn btn-primary up_btn" value="UP" change_type="up" stk_id="' . $row['id'] . '">
								<input type="button" class="bank_btn flex-fill btn btn-secondary down_btn" value="DOWN" change_type="down" stk_id="' . $row['id'] . '">
								<input type="button" class="bank_btn flex-fill btn btn-success down_btn d-none" value="COPY" change_type="copy" stk_name="' . $row['account_number'] . '" stk_bank_name="' . $row['name'] . '" stk_bank_holder="' . $row['account_holder'] . '" stk_id="' . $row['id'] . '">
							</div>
						</td>
					</tr>';
			$i++;
		}

		return $html;
    }
}