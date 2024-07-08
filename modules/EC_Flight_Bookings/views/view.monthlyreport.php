<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewmonthlyreport extends SugarView {
	function display() {
		if (ACLController::checkAccess('Bugs', 'view', true)) {
			$smartyCont = new Sugar_Smarty();
			$this->populateContent($smartyCont);
			$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_monthlyreport.tpl');
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}

	function populateContent($smartyobj) {
		global $current_user, $db, $locale;

		// dieu kien tim kiem
		$post_month = isset($_POST['selectMonth']) && !empty($_POST['selectMonth']) ? $_POST['selectMonth'] : date('m');
		$post_year = isset($_POST['selectYear']) && !empty($_POST['selectYear']) ? $_POST['selectYear'] : date('Y');

		$title_src = 'Biểu đồ tháng ' . $post_month . '/' . $post_year;
		$num = cal_days_in_month(CAL_GREGORIAN, $post_month, $post_year); // return number of days of month
		$subtitle_src = ' (Từ ngày 01-' . $post_month . '-' . $post_year . ' đến ngày ' . $num . '-' . $post_month . '-' . $post_year . ')';

		// tao mang ngay
		$category_src = "";
		$day_arr = array();
		for ($d = 0; $d < $num; $d++) {
			$day_arr[] = $post_year . "-" . $post_month . "-" . ($d + 1);
			$category_src .= "'" . ($d + 1) . "'";
			if ($d != ($num - 1))
				$category_src .= ", ";
		}

		// query
		$count_day_arr = count($day_arr);
		$booking_src = "";
		$ticket_src = "";
		$sale_src = "";

		$view_percent = " COUNT(booking_id) AS total_booking
						, SUM(total_quantity) AS total_ticket
						, ROUND(SUM(subtotal_amount)/1000000,3) AS total_sale ";
		if (isset($current_user->view_percent) && $current_user->view_percent < 100) {
			$view_percent = " ROUND(COUNT(booking_id)*" . $current_user->view_percent . "/100) AS total_booking
							, ROUND(SUM(total_quantity)*" . $current_user->view_percent . "/100) AS total_ticket
							, ROUND(ROUND(SUM(subtotal_amount)/1000000,3)*" . $current_user->view_percent . "/100,3) AS total_sale ";
		}

		/*====cus by lak====*/
		$current_user_id = $current_user->id;
		$sql_search = "";
		$sql_search_rv = "";
		$sql_search_hv = "";
		if (!is_admin($current_user)) {
			$sql_search = " AND " . SecurityGroup::getGroupWhere("bk", "EC_Flight_Bookings", $current_user_id);
			$sql_search_rv = " AND " . SecurityGroup::getGroupWhere("p", "EC_Receipt_Voucher", $current_user_id);
			$sql_search_hv = " AND " . SecurityGroup::getGroupWhere("h", "EC_HoanVe", $current_user_id);
		}
		/*====End cus by lak====*/


		for ($i = 0; $i < $count_day_arr; $i++) {

			$total_booking = 0;
			$total_ticket = 0;
			$total_sale = 0;

			$sql = "SELECT " . $view_percent . " 
			 		 FROM (
					 	SELECT bk.id AS booking_id,
							SUM(IFNULL(bkd.quantity,0)) AS total_quantity,
							IFNULL(bk.total_amount,0) AS subtotal_amount
					 	FROM ec_booking_details bkd 
							LEFT JOIN ec_flight_bookings bk ON bkd.booking_id=bk.id AND bk.deleted=0
					 	WHERE
							bk.date_ticket_issue='" . $day_arr[$i] . "'" . $sql_search . " 
							AND bk.booking_status IN ('7','8')
							AND bkd.deleted=0 
						GROUP BY bk.id
						
						UNION
						SELECT p.id AS booking_id,
							0 AS total_quantity,
							SUM(IFNULL(p.amount,0)) AS subtotal_amount
						FROM ec_receipt_voucher p
						WHERE 
							DATE(p.ngayhachtoan)='" . $day_arr[$i] . "'" . $sql_search_rv . "
							AND p.rv_status='1' 
							AND p.loai_thu IN ('4','5') 
							AND p.deleted=0
						GROUP BY p.id
						
						UNION
						SELECT p.id AS booking_id,
							0 AS total_quantity,
							SUM(IFNULL(p.tongtienhang,0)) AS subtotal_amount
						FROM ec_hoanve p
						WHERE
							DATE(p.ngayhachtoan)='" . $day_arr[$i] . "'" . $sql_search_hv . "
							AND p.tinhtrang='1'
							AND p.deleted=0
						GROUP BY p.id
					) AS temp";

			$res = $db->query($sql);
			$row = $db->fetchByAssoc($res);
			if (!empty($row)) {
				$total_booking += $row['total_booking'];
				$total_ticket += $row['total_ticket'];
				$total_sale += $row['total_sale'];
			}

			$booking_src .= $total_booking;
			$ticket_src .= $total_ticket;
			$sale_src .= $total_sale;

			if ($i != ($count_day_arr - 1)) {
				$booking_src .= ",";
				$ticket_src .= ",";
				$sale_src .= ",";
			}
		}

		// Du lieu chinh
		$data_src = "";
		$data_src .= "{name: 'Tổng số vé',";
		$data_src .= "data: [";
		$data_src .= $ticket_src;
		$data_src .= "]";
		$data_src .= "}";
		$data_src .= ",";

		$data_src .= "{name: 'Tổng số booking',";
		$data_src .= "data: [";
		$data_src .= $booking_src;
		$data_src .= "]";
		$data_src .= "}";
		$data_src .= ",";

		$data_src .= "{name: 'Doanh thu (triệu đồng)',";
		$data_src .= "data: [";
		$data_src .= $sale_src;
		$data_src .= "]";
		$data_src .= "}";

		// output
		echo "<script>
		var title_src = '" . $title_src . "';
		var subtitle_src = '" . $subtitle_src . "';
		var ycolumn_name = ' ';
		var category_src = [" . $category_src . "];
		var data_src = [" . $data_src . "];
			 </script>";

		$sep = get_number_seperators();
		$smartyobj->assign('GRP_SEPERATOR', $sep[0]);
		$smartyobj->assign('DEC_SEPERATOR', $sep[1]);
		$smartyobj->assign('SIG_DIGITS', $locale->getPrecision());
		$smartyobj->assign('LIST_OF_MONTH', myGetMonthList($post_month));
		$smartyobj->assign('LIST_OF_YEAR', myGetYearList(date('Y'), 3, $post_year));
	}
}
