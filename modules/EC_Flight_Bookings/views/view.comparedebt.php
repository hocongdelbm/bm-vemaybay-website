<?php
require_once("include/Sugar_Smarty.php");
// require_once("phpexcel/Classes/PHPExcel/IOFactory.php");

class Viewcomparedebt extends SugarView {
	function display() {
		// global $current_user;
		if (ACLController::checkAccess('Bugs', 'edit', true)) {
			$smartyCont = new Sugar_Smarty();
			$this->populateContent($smartyCont);
			$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_comparedebt.tpl');
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}

	function populateContent($smartyobj) {
		global $db, $app_list_strings, $app_strings, $current_user, $timedate;
		$date_format = $timedate->get_date_format();

		// require_once('include/upload_file.php');

		$html = '';
		$update_hidden = '';

		// upload file
		if (isset($_POST['btnCompare'])) {
			$upload_file = new UploadFile('upload_file');
			$do_final_move = 0;
			if (isset($_FILES['upload_file']) && $upload_file->confirm_upload()) {
				$filename = $upload_file->get_stored_file_name();
				$file_mime_type = $upload_file->mime_type;
				$file_ext = $upload_file->file_ext;
				$do_final_move = 1;
			}

			if ($do_final_move && $file_ext == 'xls' && (int)$_POST['start_row'] <= (int)$_POST['end_row']) {
				$file_id = 'temp_' . create_guid() . '.' . $file_ext;
				if ($upload_file->final_move($file_id)) {

					set_time_limit(0);
					ini_set('memory_limit', '512M');

					$inputFileType = 'Excel5';
					$inputFileName = $GLOBALS['sugar_config']['upload_dir'] . $file_id;
					$sheetname = $_POST['sheet_name'];
					// $filterSubset = new MyReadFilter((int)$_POST['start_row'], (int)$_POST['end_row'], array(
					// 	$_POST['pax_col'], $_POST['eticket_col'], $_POST['pnr_col'], $_POST['iti_col'], $_POST['amount_col'], $_POST['discount_col'], $_POST['airline_col']
					// ));
					// $objReader = PHPExcel_IOFactory::createReader($inputFileType);
					// $objReader->setLoadSheetsOnly($sheetname);
					// $objReader->setReadFilter($filterSubset);
					// $objPHPExcel = $objReader->load($inputFileName);
					// $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
					$sheetData = array();
					
					foreach ($sheetData as $k => $arr_data) {
						$pax_col = trim(stripslashes($arr_data[$_POST['pax_col']]));
						$eticket_col = trim(stripslashes($arr_data[$_POST['eticket_col']]));
						$pnr_col = trim(stripslashes($arr_data[$_POST['pnr_col']]));
						$amount_col = unformat_number($arr_data[$_POST['amount_col']]);
						if ($pax_col != '' && ($eticket_col != '' || $pnr_col != '') && $amount_col > 0) {

							$iti_col = trim(stripslashes($arr_data[$_POST['iti_col']]));
							$discount_col = unformat_number($arr_data[$_POST['discount_col']]);
							$airline_col = trim(stripslashes($arr_data[$_POST['airline_col']]));
							if ($airline_col == 'VNSGN' || $airline_col == 'VN')
								$airline_col = 'VNA';

							// kiểm tra sự tồn tại của số vé
							$sql_search = "";
							$sql_select = "";
							if ($eticket_col != '') {
								$sql_select .= " IF(TRIM(p.eticket_outbound)='" . $eticket_col . "', 0, 1) AS direction ";
								$sql_search .= " (TRIM(p.eticket_outbound)='" . $eticket_col . "' OR TRIM(p.eticket_inbound)='" . $eticket_col . "') ";
							} else {
								$sql_select .= " IF(TRIM(p.pnr_outbound)='" . $pnr_col . "', 0, 1) AS direction ";
								$sql_search .= " (TRIM(p.pnr_outbound)='" . $pnr_col . "' OR TRIM(p.pnr_inbound)='" . $pnr_col . "') ";
							}

							$sql = "SELECT p.name AS passenger, p.booking_id, p.type AS passenger_type
										, p.eticket_outbound, p.eticket_inbound, p.pnr_outbound, p.pnr_inbound, " . $sql_select . " 
									FROM ec_booking_passengers p 
									WHERE " . $sql_search . " AND p.deleted = 0 LIMIT 1 ";
							$res = $db->query($sql);
							$row = $db->fetchByAssoc($res);

							if (!empty($row))
								$is_exist = 1;
							else
								$is_exist = 0;

							$booking_arr[$row['booking_id']][] = array(
								'pax_col' => $pax_col,
								'eticket_col' => $eticket_col,
								'pnr_col' => $pnr_col,
								'iti_col' => $iti_col,
								'amount_col' => $amount_col,
								'discount_col' => $discount_col,
								'airline_col' => $airline_col,
								'is_exist' => $is_exist,
								'booking_id' => $row['booking_id'],
								'passenger_type' => $row['passenger_type'],
								'direction' => $row['direction'],
							);
						} // end if

					} // end foreach

					$g = 0;
					$total_bought_amount = 0;
					$total_discount_amount = 0;
					$total_row = 0;
					foreach ($booking_arr as $k => $booking_data) {
						for ($i = 0; $i < count($booking_data); $i++) {

							$sql = "SELECT SUM(bought_amount) AS bought_amount, date_ticket_issue FROM (
										SELECT SUM(IFNULL(d.total_bought_price,0)) AS bought_amount, b.date_ticket_issue, d.direction 
										FROM ec_booking_details d 
											LEFT JOIN ec_flight_bookings b ON d.booking_id=b.id AND b.deleted=0
											LEFT JOIN ec_booking_itineraries i ON i.booking_id=b.id AND i.deleted=0
										WHERE 
											d.booking_id = '" . $booking_data[$i]['booking_id'] . "'
											AND d.deleted = 0 
											AND b.booking_status = '8' 
											AND i.direction = '0' AND d.direction = '0'
											AND TRIM(i.airline_code) IN ('JET','3K','JQ','BL','VN','VNA')
											
										GROUP BY d.booking_id 
										
										UNION

										SELECT SUM(IFNULL(d.total_bought_price,0)) AS bought_amount, b.date_ticket_issue, d.direction
										FROM ec_booking_details d 
											LEFT JOIN ec_flight_bookings b ON d.booking_id=b.id AND b.deleted=0
											LEFT JOIN ec_booking_itineraries i ON i.booking_id=b.id AND i.deleted=0
										WHERE
											d.booking_id = '" . $booking_data[$i]['booking_id'] . "'
											AND d.deleted = 0
											AND b.booking_status = '8'  
											AND i.direction = '1' AND d.direction = '1'
											AND TRIM(i.airline_code) IN ('JET','3K','JQ','BL','VN','VNA')
										GROUP BY d.booking_id 
									) AS t GROUP BY date_ticket_issue";

							$res = $db->query($sql);
							$row = $db->fetchByAssoc($res);
							$bgcolor = ($g % 2 == 0) ? '#EEEEEE' : '#FFFFFF';

							$html .= '<tr style="background:' . $bgcolor . '">
							<td align="center">' . ($row['date_ticket_issue'] != '' ? date($date_format, strtotime($row['date_ticket_issue'])) : '&nbsp;') . '</td>';

							if (trim($booking_data[$i]['booking_id']) != '') {
								$html .= '<td align="left"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $booking_data[$i]['booking_id'] . '" target="_blank" title="Xem chi tiết">' . $booking_data[$i]['pax_col'] . '</a></td>';
							} else {
								$html .= '<td align="left">' . $booking_data[$i]['pax_col'] . '</td>';
							}

							$html .= '<td align="left">' . $booking_data[$i]['eticket_col'] . '</td>
							<td align="left">' . $booking_data[$i]['pnr_col'] . '</td>
							<td align="center">' . $booking_data[$i]['iti_col'] . '</td>';

							if ($i == 0) {
								$html .= '<td align="right">' . format_number($row['bought_amount']) . '</td>
								<td align="right">' . format_number($booking_data[$i]['discount_col']) . '</td>
								<td align="right">' . format_number($row['bought_amount'] - $booking_data[$i]['discount_col']) . '</td>';
								$total_bought_amount += $row['bought_amount'];
							} else {
								$html .= '<td align="right">0</td>
								<td align="right">' . format_number($booking_data[$i]['discount_col']) . '</td>
								<td align="right">0</td>';
							}

							$html .= '<td align="center">' . $booking_data[$i]['airline_col'] . '</td>
							<td align="center">' . ($booking_data[$i]['is_exist'] == 1 ? '<label style="color:blue;">Đã xuất vé</label>' : '<label style="color:red;">Không tồn tại</label>') . '</td>
								</tr>';

							if ($booking_data[$i]['is_exist'] == 1 && $booking_data[$i]['discount_col'] > 0) {
								$update_hidden .= '<input type="hidden" name="booking_id[]" value="' . $booking_data[$i]['booking_id'] . '" />
								<input type="hidden" name="passenger_type_' . $booking_data[$i]['booking_id'] . '" value="' . $booking_data[$i]['passenger_type'] . '" />
								<input type="hidden" name="discount_amount_' . $booking_data[$i]['booking_id'] . '" value="' . $booking_data[$i]['discount_col'] . '" />';
							}

							$total_discount_amount += $booking_data[$i]['discount_col'];
							$total_row++;
						} //end for
						$g++;
					} //end foreach

					// xóa file sau khi xử lý xong
					$upload_file->unlink_file('', $file_id);
				} // end if final move
			}
		} // end if $_POST['btnCompare']

		// lưu toàn bộ hoa hồng vào trong 1 booking của 1 chiều bay
		if (isset($_POST['btnUpdateDiscount'])) {
			$update_arr_cnt = count($_POST['booking_id']);
			if ($update_arr_cnt > 0) {
				for ($i = 0; $i < $update_arr_cnt; $i++) {
					$sql = "UPDATE ec_booking_details SET discount_amount=IFNULL(discount_amount,0)+" . $_POST['discount_amount_' . $_POST['booking_id'][$i]] . "
							WHERE deleted=0 AND booking_id='" . $_POST['booking_id'][$i] . "' AND passenger_type='" . $_POST['passenger_type_' . $_POST['booking_id'][$i]] . "' AND direction='0' ";
					$db->query($sql);
				}
				echo '<p style="color:blue">Đã cập nhật thành công</p>';
			}
		}

		$total_amount = $total_bought_amount - $total_discount_amount;
		$smartyobj->assign('DATA', $html);
		$smartyobj->assign('UPDATE_HIDDEN', $update_hidden);
		$smartyobj->assign('TOTAL_BOUGHT_AMOUNT', format_number($total_bought_amount));
		$smartyobj->assign('TOTAL_DISCOUNT_AMOUNT', format_number($total_discount_amount));
		$smartyobj->assign('TOTAL_AMOUNT', format_number($total_amount));
		$smartyobj->assign('TOTAL_AMT', $total_amount);
		$smartyobj->assign('TOTAL_ROW', format_number($total_row));
		// $smartyobj->assign('HAS_ACCESS_DEBT', ACLController::checkAccess('EC_Debts', 'edit', true));

		$smartyobj->assign('PAX_COL', isset($_POST['pax_col']) ? $_POST['pax_col'] : 'B');
		$smartyobj->assign('ETICKET_COL', isset($_POST['eticket_col']) ? $_POST['eticket_col'] : 'C');
		$smartyobj->assign('PNR_COL', isset($_POST['pnr_col']) ? $_POST['pnr_col'] : 'D');
		$smartyobj->assign('ITI_COL', isset($_POST['iti_col']) ? $_POST['iti_col'] : 'E');
		$smartyobj->assign('AMOUNT_COL', isset($_POST['amount_col']) ? $_POST['amount_col'] : 'F');
		$smartyobj->assign('DISCOUNT_COL', isset($_POST['discount_col']) ? $_POST['discount_col'] : 'G');
		$smartyobj->assign('AIRLINE_COL', isset($_POST['airline_col']) ? $_POST['airline_col'] : 'L');
		$smartyobj->assign('START_ROW', isset($_POST['start_row']) ? $_POST['start_row'] : 5);
		$smartyobj->assign('END_ROW', isset($_POST['end_row']) ? $_POST['end_row'] : 950);
		$smartyobj->assign('SHEET_NAME', isset($_POST['sheet_name']) ? $_POST['sheet_name'] : 'Sheet1');
	}

	// Kiểm tra booking có phải khứ hồi ko
	function checkReturnWay($booking_id) {
		global $db;
		$sql = "SELECT flight_type FROM ec_flight_bookings WHERE deleted=0 AND id='" . $booking_id . "' ";
		$flight_type = $db->getOne($sql);
		if ($flight_type == '0')
			return true;
		return false;
	}
}

// class MyReadFilter implements PHPExcel_Reader_IReadFilter {
// 	private $_startRow = 0;
// 	private $_endRow = 0;
// 	private $_columns = array();

// 	public function __construct($startRow, $endRow, $columns)
// 	{
// 		$this->_startRow	= $startRow;
// 		$this->_endRow		= $endRow;
// 		$this->_columns		= $columns;
// 	}

// 	public function readCell($column, $row, $worksheetName = '')
// 	{
// 		if ($row >= $this->_startRow && $row <= $this->_endRow) {
// 			if (in_array($column, $this->_columns)) {
// 				return true;
// 			}
// 		}
// 		return false;
// 	}
// }
