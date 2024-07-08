<?php
require_once("include/Sugar_Smarty.php");
class Viewsyncpnr extends SugarView {

	function display() {
		if( ACLController::checkAccess('EC_Flight_Bookings', 'edit', true) && isset($_POST['btnSyncPNR']) && isset($_POST['record']) ){
			$this->syncAgentPNR($_POST['record']);
			header('Location: index.php?module=EC_Flight_Bookings&action=DetailView&record='.$_POST['record']);
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=".urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}
	
	function syncAgentPNR($record){
		global $db;
		require_once('sugar_rest.php');
		
		$bk = new EC_Flight_Bookings();
		$bk->retrieve($record);
		$agent_user_id = $bk->created_by;
		
		$usr = new User();
		$usr->retrieve($agent_user_id);
		
		if($bk->is_agent && $usr->agent_url && $usr->agent_user && $usr->agent_passwd){
		
			$sugar = new Sugar_REST($usr->agent_url, $usr->agent_user, $usr->agent_passwd);
			$sql = "SELECT agent_pax_detail_id
						 , eticket_outbound
						 , eticket_inbound
						 , pnr_outbound
						 , pnr_inbound 
					FROM ec_booking_passengers 
					WHERE deleted=0 
					AND booking_id='".$record."' ";
			
			$res = $db->query($sql);
			while($row = $db->fetchByAssoc($res)){
				$booking_pax = array(
					'id' => $row['agent_pax_detail_id'],
					'eticket_outbound' => $row['eticket_outbound'],
					'eticket_inbound' => $row['eticket_inbound'],
					'pnr_outbound' => $row['pnr_outbound'],
					'pnr_inbound' => $row['pnr_inbound'],
				);
				$sugar->set('EC_Booking_Passengers',$booking_pax);
			}// while
			
		}// if
	}
}
