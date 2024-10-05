<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');

class EC_Flight_BookingsViewList extends ViewList {
	function __construct() {
		parent::__construct();
	}

	function listViewPrepare() {
		if (empty($_REQUEST['orderBy']) || isset($_REQUEST['query'])) {
			$_REQUEST['orderBy'] = 'date_entered';
			$_REQUEST['sortOrder'] = 'desc';
		}
		parent::listViewPrepare();
	}

	function listViewProcess() {
		global $current_user;

		if (isset($_POST['from']) && $_POST['from'] == 'bkqtyreport') {
			if (!isset($_REQUEST['current_query_by_page'])) {
				$user_name_con = $exc_contact_name = '';

				if (!empty($_POST['contact_name_advanced'])) {
					$exc_contact_name_arr = explode(',', $_POST['contact_name_advanced']);
					if (isset($_POST['contact_name_advanced_NOT'])) {
						$exc_contact_name = ' AND (NOT(COALESCE(ec_flight_bookings.contact_name, "") IN ("' . implode('","', $exc_contact_name_arr) . '")))';
					}
					else {
						$exc_contact_name = ' AND (ec_flight_bookings.contact_name IN ("' . implode('","', $exc_contact_name_arr) . '"))';
					}
				}

				if (isset($_POST['created_by_name_advanced'])) {
					$user_name_con = ' AND ( jt1.user_name LIKE "%' . $_POST['created_by_name_advanced'] . '%" )';
				}

				$this->params['custom_where'] .= ' OR (DATE_ADD(ec_flight_bookings.date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d 00:00:00', strtotime($_POST['date_entered_advanced'])) . '" AND DATE_ADD(ec_flight_bookings.date_entered, INTERVAL 7 HOUR) <= "' . date('Y-m-d 23:59:59', strtotime($_POST['date_entered_advanced_upperbound'])) . '"' . $user_name_con . $exc_contact_name . ' AND ec_flight_bookings.deleted = 0 AND ec_flight_bookings.booking_status IN ("' . implode('","', $_POST['booking_status_advanced']) . '"))';
			} 
			else {
				$current_query = unserialize(base64_decode($_REQUEST['current_query_by_page']));
				$user_name_con = $exc_contact_name = '';

				if (!empty($current_query['contact_name_advanced'])) {
					$exc_contact_name_arr = explode(',', $current_query['contact_name_advanced']);
					if (isset($current_query['contact_name_advanced_NOT'])) {
						$exc_contact_name = ' AND (NOT(COALESCE(ec_flight_bookings.contact_name, "") IN ("' . implode('","', $exc_contact_name_arr) . '")))';
					} else {
						$exc_contact_name = ' AND (ec_flight_bookings.contact_name IN ("' . implode('","', $exc_contact_name_arr) . '"))';
					}
				}

				if (isset($current_query['created_by_name_advanced'])) {
					$user_name_con = ' AND ( jt1.user_name LIKE "%' . $current_query['created_by_name_advanced'] . '%" )';
				}

				$this->params['custom_where'] .= ' OR (DATE_ADD(ec_flight_bookings.date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d 00:00:00', strtotime($current_query['date_entered_advanced'])) . '" AND DATE_ADD(ec_flight_bookings.date_entered, INTERVAL 7 HOUR) <= "' . date('Y-m-d 23:59:59', strtotime($current_query['date_entered_advanced_upperbound'])) . '"' . $user_name_con . $exc_contact_name . ' AND ec_flight_bookings.deleted = 0)';
			}
		}
		else $this->params['custom_where'] = '';

		parent::listViewProcess();
	}

	function display() {
		// global $current_user;

		// if(isset($current_user->view_percent) && $current_user->view_percent < 100){
		// 	header('Location: index.php?module=EC_Flight_Bookings&action=currentsales');
		// }

		$this->lv->quickViewLinks = false;
		$this->displayCSS();
		$this->displayJS();
		parent::display();
	}

	function displayCSS() {
		$smarty = new Sugar_Smarty;
		$smarty->display('modules/' . $this->bean->module_dir . '/tpls/view.list_css.tpl');
	}

	function displayJS() {
		echo `<script>
			$(document).ready(function() {
				$(".search_form").submit(function() {
					$("#search_form_submit").attr("disabled", "disabled");
					$("input[name='clear']").attr("disabled", "disabled");
					$("button[name='listViewStartButton']").attr("disabled", "disabled");
					$("button[name='listViewPrevButton']").attr("disabled", "disabled");
					$("button[name='listViewNextButton']").attr("disabled", "disabled");
					$("button[name='listViewEndButton']").attr("disabled", "disabled");
				});

				$("input[name='clear'], button[name='listViewStartButton'], button[name='listViewPrevButton'], button[name='listViewNextButton'], button[name='listViewEndButton']").click(function() {
					$("#search_form_submit").attr("disabled", "disabled");
					$("input[name='clear']").attr("disabled", "disabled");
					$("button[name='listViewStartButton']").attr("disabled", "disabled");
					$("button[name='listViewPrevButton']").attr("disabled", "disabled");
					$("button[name='listViewNextButton']").attr("disabled", "disabled");
					$("button[name='listViewEndButton']").attr("disabled", "disabled");
				});
			});
		</script>`;

		if (isset($_POST['from']) && $_POST['from'] == 'bkqtyreport') {
			echo `<script>
				$(document).ready(function() {
					$("#MassUpdate").append("<input type='hidden' name='from' value='` . $_POST['from'] . `'>");
				});	
			</script>`;
		}
	}
}
