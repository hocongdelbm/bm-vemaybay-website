<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db;


 if(!empty($_SESSION['authenticated_user_id'])){
	 
	$module 	= trim(stripslashes($_REQUEST['module']));
	$uid 	= trim(stripslashes($_REQUEST['uid']));
	$fdate 	= trim(stripslashes($_REQUEST['fdate']));
	$tdate 	= trim(stripslashes($_REQUEST['tdate']));
		
	if($module == 'Contacts'){
		$sql = "SELECT c.id, CONCAT(IFNULL(c.last_name,''), ' ', IFNULL(c.first_name,'')) AS name
					   ,c.date_entered, c.description
					   ,(SELECT a.name FROM accounts_contacts ac LEFT JOIN accounts a ON ac.account_id=a.id AND a.deleted=0 WHERE ac.deleted=0 AND ac.contact_id=c.id LIMIT 1) AS account_name
				FROM contacts c 
				WHERE c.deleted=0 AND c.assigned_user_id='".$uid."'
				AND DATE(c.date_entered) >= '".date('Y-m-d', strtotime($fdate))."' AND DATE(c.date_entered) <= '".date('Y-m-d', strtotime($tdate))."' ";
	} else if($module == 'Opportunities'){
		$sql = "SELECT o.id, o.name, o.date_entered, o.description
					 ,(SELECT a.name FROM accounts_opportunities ao LEFT JOIN accounts a ON ao.account_id=a.id AND a.deleted=0 WHERE ao.deleted=0 AND ao.opportunity_id=o.id LIMIT 1) AS account_name
				FROM opportunities o
				WHERE o.deleted=0 AND o.assigned_user_id='".$uid."'
				AND DATE(o.date_entered) >= '".date('Y-m-d', strtotime($fdate))."' AND DATE(o.date_entered) <= '".date('Y-m-d', strtotime($tdate))."' ";
	} else if($module == 'Calls'){
		$sql = "SELECT c.id, c.name, c.date_entered, c.description, a.name AS account_name
				FROM calls c LEFT JOIN accounts a ON c.parent_id=a.id AND a.deleted=0
				WHERE c.deleted=0 AND c.parent_type='Accounts' AND c.assigned_user_id='".$uid."'
				AND DATE(c.date_entered) >= '".date('Y-m-d', strtotime($fdate))."' AND DATE(c.date_entered) <= '".date('Y-m-d', strtotime($tdate))."' ";
	} else if($module == 'Meetings'){
		$sql = "SELECT m.id, m.name, m.date_entered, m.description, a.name AS account_name
				FROM meetings m LEFT JOIN accounts a ON m.parent_id=a.id AND a.deleted=0
				WHERE m.deleted=0 AND m.parent_type='Accounts' AND m.assigned_user_id='".$uid."'
				AND DATE(m.date_entered) >= '".date('Y-m-d', strtotime($fdate))."' AND DATE(m.date_entered) <= '".date('Y-m-d', strtotime($tdate))."' ";
	} else if($module == 'Tasks'){
		$sql = "SELECT t.id, t.name, t.date_entered, t.description, a.name AS account_name
				FROM tasks t LEFT JOIN accounts a ON t.parent_id=a.id AND a.deleted=0
				WHERE t.deleted=0 AND t.parent_type='Accounts' AND t.assigned_user_id='".$uid."'
				AND DATE(t.date_entered) >= '".date('Y-m-d', strtotime($fdate))."' AND DATE(t.date_entered) <= '".date('Y-m-d', strtotime($tdate))."' ";
	} else if($module == 'EC_Payment_Voucher'){
		if(empty($uid)) {
			$sql = 'SELECT id, name, "" AS account_name, description
						 , ngayhachtoan AS date_entered, tongtienhang AS amount
					FROM ec_hoanve 
					WHERE deleted = 0 AND tinhtrang = 1 
					AND DATE(ngayhachtoan) >= "'.date('Y-m-d', strtotime($fdate)).'" 
					AND DATE(ngayhachtoan) <= "'.date('Y-m-d', strtotime($tdate)).'"';
		} else {
			$sql = "SELECT p.id, p.name, p.receipent_name AS account_name, p.description, p.ngayhachtoan AS date_entered, p.amount
				FROM ec_payment_voucher p WHERE p.deleted=0 AND p.pv_status='3' AND p.ec_payment_types_id_c='".$uid."'
				AND DATE(p.ngayhachtoan) >= '".date('Y-m-d', strtotime($fdate))."' AND DATE(p.ngayhachtoan) <= '".date('Y-m-d', strtotime($tdate))."' ";
		}
	}


	$res 		= $db->query($sql);
	// $row_count 	= $db->getRowCount($res);
	$row_count 	= $db->countRows($res);
	
	if($row_count > 0){
		$arr = array();
		while($row = $db->fetchByAssoc($res)){
			$arr[] = array('id' => $row['id'],
						   'date_entered' => date('d-m-Y', strtotime($row['date_entered'])+7*3600),
						   'description' => $row['description'],
						   'account_name' => $row['account_name'],
						   'name' => $row['name'],
						   'amount' => format_number($row['amount']),
					 );
		}// end while
		echo json_encode($arr);
	} else echo 0;
	
 } else {
	 echo '<script>
	 		location.reload();
	 	   </script>';
 }
