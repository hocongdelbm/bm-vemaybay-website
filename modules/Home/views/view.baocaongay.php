<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewbaocaongay extends SugarView
{

	function display()
	{
		$smartyCont = new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/Home/tpls/baocaongay.tpl');
		parent::display();
	}

	function populateContent($smartyobj)
	{
		global $db, $current_user;
		$sql_search = "";
		if (isset($_POST['tungay']) && !empty($_POST['tungay'])) {
			$sql_search .= " AND DATE(p.date_entered) >= '" . date('Y-m-d', strtotime($_POST['tungay'])) . "' ";
			$post_tungay = $_POST['tungay'];
		} else {
			$sql_search .= " AND DATE(p.date_entered) >= '" . date('Y-m-d') . "' ";
			$post_tungay = date('d-m-Y');
		}

		if (isset($_POST['denngay']) && !empty($_POST['denngay'])) {
			$sql_search .= " AND DATE(p.date_entered) <= '" . date('Y-m-d', strtotime($_POST['denngay'])) . "' ";
			$post_denngay = $_POST['denngay'];
		} else {
			$sql_search .= " AND DATE(p.date_entered) <= '" . date('Y-m-d') . "' ";
			$post_denngay = date('d-m-Y');
		}

		// phan quyen du lieu neu khong phai la admin
		$role_contact 	= "";
		$role_meeting 	= "";
		$role_call 		= "";
		$role_quote 	= "";
		$role_opp 		= "";
		$role_task 		= "";
		if (!is_admin($current_user)) {
			// quyen owner
			if (ACLController::requireOwner('Contacts', 'list')) {
				$role_contact .= " AND p.assigned_user_id='" . $current_user->id . "' ";
			}
			if (ACLController::requireOwner('Meetings', 'list')) {
				$role_meeting .= " AND p.assigned_user_id='" . $current_user->id . "' ";
			}
			if (ACLController::requireOwner('Calls', 'list')) {
				$role_call .= " AND p.assigned_user_id='" . $current_user->id . "' ";
			}
			if (ACLController::requireOwner('Opportunities', 'list')) {
				$role_opp .= " AND p.assigned_user_id='" . $current_user->id . "' ";
			}
			if (ACLController::requireOwner('Tasks', 'list')) {
				$role_task .= " AND p.assigned_user_id='" . $current_user->id . "' ";
			}

			// quyen group
			if (ACLController::requireSecurityGroup('Contacts', 'list')) {
				$role_contact .= " AND " . SecurityGroup::getGroupWhere('p', 'Contacts', $current_user->id);
			}
			if (ACLController::requireSecurityGroup('Meetings', 'list')) {
				$role_meeting .= " AND " . SecurityGroup::getGroupWhere('p', 'Meetings', $current_user->id);
			}
			if (ACLController::requireSecurityGroup('Calls', 'list')) {
				$role_call .= " AND " . SecurityGroup::getGroupWhere('p', 'Calls', $current_user->id);
			}
			if (ACLController::requireSecurityGroup('Opportunities', 'list')) {
				$role_opp .= " AND " . SecurityGroup::getGroupWhere('p', 'Opportunities', $current_user->id);
			}
			if (ACLController::requireSecurityGroup('Tasks', 'list')) {
				$role_task .= " AND " . SecurityGroup::getGroupWhere('p', 'Tasks', $current_user->id);
			}
		}

		$sql = "SELECT u.id AS uid, CONCAT(IFNULL(u.last_name,''), ' ', IFNULL(u.first_name,' ')) AS full_name, SUM(total_contact) AS total_contact
					  ,SUM(total_meeting) AS total_meeting, SUM(total_call) AS total_call, SUM(total_opp) AS total_opp, SUM(total_task) AS total_task
				FROM users u LEFT JOIN (
					SELECT p.assigned_user_id, COUNT(p.id) AS total_contact
						 , 0 AS total_meeting, 0 AS total_call, 0 AS total_opp, 0 AS total_task
					FROM contacts p WHERE p.deleted=0 " . $sql_search . $role_contact . " GROUP BY p.assigned_user_id
					UNION
					SELECT p.assigned_user_id, 0 AS total_contact
						 , COUNT(p.id) AS total_meeting, 0 AS total_call, 0 AS total_opp, 0 AS total_task
					FROM meetings p WHERE p.deleted=0 " . $sql_search . $role_meeting . " GROUP BY p.assigned_user_id
					UNION
					SELECT p.assigned_user_id, 0 AS total_contact
						 , 0 AS total_meeting, COUNT(p.id) AS total_call, 0 AS total_opp, 0 AS total_task
					FROM calls p WHERE p.deleted=0 " . $sql_search . $role_call . " GROUP BY p.assigned_user_id
					UNION
					SELECT p.assigned_user_id, 0 AS total_contact
						 , 0 AS total_meeting, 0 AS total_call, COUNT(p.id) AS total_opp, 0 AS total_task
					FROM opportunities p WHERE p.deleted=0 " . $sql_search . $role_opp . " GROUP BY p.assigned_user_id
					UNION
					SELECT p.assigned_user_id, 0 AS total_contact
						 , 0 AS total_meeting, 0 AS total_call, 0 AS total_opp, COUNT(p.id) AS total_task
					FROM tasks p WHERE p.deleted=0 " . $sql_search . $role_task . " GROUP BY p.assigned_user_id
				) AS t ON u.id=t.assigned_user_id 
				WHERE u.deleted=0 AND u.status='Active' AND u.employee_status='Active' AND u.is_admin=0
				GROUP BY t.assigned_user_id ";

		$res = $db->query($sql);
		$html = '';
		$i = 0;
		while ($row = $db->fetchByAssoc($res)) {
			$html .= '<tr>
						<td align="left">' . $row['full_name'] . '</td>
						<td align="center"><a class="view_detail" module="Tasks" uid="' . $row['uid'] . '" title="Xem chi tiết">' . format_number($row['total_task']) . '</a></td>
						<td align="center"><a class="view_detail" module="Contacts" uid="' . $row['uid'] . '" title="Xem chi tiết">' . format_number($row['total_contact']) . '</a></td>
						<td align="center"><a class="view_detail" module="Meetings" uid="' . $row['uid'] . '" title="Xem chi tiết">' . format_number($row['total_meeting']) . '</a></td>
						<td align="center"><a class="view_detail" module="Calls" uid="' . $row['uid'] . '" title="Xem chi tiết">' . format_number($row['total_call']) . '</a></td>
						<td align="center"><a class="view_detail" module="Opportunities" uid="' . $row['uid'] . '" title="Xem chi tiết">' . format_number($row['total_opp']) . '</a></td>
					</tr>';
			$i++;
		}
		$smartyobj->assign('DATA', $html);
		$smartyobj->assign('POST_TUNGAY', $post_tungay);
		$smartyobj->assign('POST_DENNGAY', $post_denngay);
	}
}
