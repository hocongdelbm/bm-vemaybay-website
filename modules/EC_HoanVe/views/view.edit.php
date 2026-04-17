<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php'); 

class EC_HoanVeViewEdit extends ViewEdit{

	function display(){
		if(empty($this->bean->id) || $this->bean->tinhtrang == '2' || (isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true')){
			$this->populateLineItems();
			parent::display();
		} else {
			header("Location: index.php?module=EC_HoanVe&action=Error&error_string=".urlencode("Bạn không được quyền sửa chứng từ này"));
			exit();
		}
	}

	function populateLineItems(){
		global $locale, $db, $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();
		
		$html = '';
		$html .= '<table border="0" cellspacing="0" cellpadding="0" class="table-vertical__mobile table-edit__booking table-details__booking table-details__hoanve">';
		$html .= '<thead><tr id="first-row">
					<th scope="col" width="7%" class="text-center">Loại HK</th>
					<th scope="col" width="4%" class="text-center">Danh xưng</th>
					<th scope="col" width="14%" class="text-center">Họ tên</th>
					<th scope="col" width="8%" class="text-center">Ngày sinh</th>
					<th scope="col" width="6%" class="text-center">Chiều</th>
					<th scope="col" width="4%" class="text-center">Mã hãng</th>
					<th scope="col" width="4%" class="text-center">Nơi đi</th>
					<th scope="col" width="4%" class="text-center">Nơi đến</th>
					<th scope="col" width="10%" class="text-center">Số vé</th>
					<th scope="col" width="8%" class="text-center">PNR</th>
					<th scope="col" width="10%" class="text-center">NCC</th>
					<th scope="col" width="6%" class="text-center">Tiền lấy về</th>
					<th scope="col" width="6%" class="text-center">Tiền HV</th>
					<th scope="col" width="6%" class="text-center">Phí hoàn</th>
					<th scope="col" width="2%" class="text-center">Đã hoàn</th>
					<th scope="col" width="2%" class="text-center">&nbsp;</th>
				</tr></thead>';
		
		if(isset($_POST['booking_id']) && !empty($_POST['booking_id'])){
			
			$sql1 = "SELECT direction AS chieubay
						  , airline_code
						  , departure AS noidi
						  , arrival AS noiden
					 FROM ec_booking_itineraries
					 WHERE deleted = 0 
					 AND booking_id = '".$_POST['booking_id']."' ";

			$res1 	= $this->bean->db->query($sql1);
			$i 		= 0;

			while($row1 = $this->bean->db->fetchByAssoc($res1)){
				$sql2 = "SELECT p.name AS hoten
							  , p.type AS loaihk
							  , p.salutation AS danhxung
							  , p.birthday AS ngaysinh
							  , ".($row1['chieubay'] == '0' ? "p.eticket_outbound" : "p.eticket_inbound")." AS sove
							  , ".($row1['chieubay'] == '0' ? "p.pnr_outbound" : "p.pnr_inbound")." AS pnr
							  , 0 AS sotienhang
							  , 0 AS sotienkhach
							  , 0 AS phidichvu
							  , (
							  		SELECT d.supplier_id 
									FROM ec_booking_details d
									WHERE d.deleted=0
									AND d.is_active=1
									AND d.direction='".$row1['chieubay']."'
									AND d.passenger_type=p.type
									AND d.booking_id=p.booking_id
									LIMIT 1
							  ) AS nhacc_id
						 FROM ec_booking_passengers p
						 WHERE p.deleted=0 
						 AND p.booking_id='".$_POST['booking_id']."' ";

				$res2 = $this->bean->db->query($sql2);

				while($row2 = $this->bean->db->fetchByAssoc($res2)){
					$html .= '<tr id="ct_line_'.$i.'" class="res2">
								<td data-label="Loại HK"><select name="ct_loaihk[]" id="ct_loaihk'.$i.'">'.get_select_options_with_id($app_list_strings['passenger_type_list'], (int)$row2['loaihk']).'</select></td>
								<td data-label="Danh xưng"><select name="ct_danhxung[]" id="ct_danhxung'.$i.'">'.get_select_options_with_id($app_list_strings['passenger_salutation_list'], (int)$row2['danhxung']).'</select></td>
								<td data-label="Họ tên"><input type="text" class="text-start" maxlength="255" name="ct_hoten[]" id="ct_hoten'.$i.'" value="'.$row2['hoten'].'" /></td>
								<td data-label="Ngày sinh">
									<div class="d-flex align-items-center gap-1">
										<input class="text-center w-80" type="text" maxlength="10" name="ct_ngaysinh[]" id="ct_ngaysinh'.$i.'" value="'.(trim($row2['ngaysinh']) != '' ? date($date_format, strtotime($row2['ngaysinh'])) : '').'" />
										<img class="cursor-pointer" border="0" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="ct_ngaysinh_trigger'.$i.'" align="absmiddle" />
									</div>
								</td>
								<td data-label="Chiều"><select name="ct_chieubay[]" id="ct_chieubay'.$i.'">'.get_select_options_with_id($app_list_strings['bk_direction_list'], (int)$row1['chieubay']).'</select></td>
								<td data-label="Mã hãng"><input type="text" class="ac airline text-center" id="ct_airline_code'.$i.'" name="ct_airline_code[]" value="'.$row1['airline_code'].'"></td>
								<td data-label="Nơi đi"><input type="text" class="text-center" maxlength="3" name="ct_noidi[]" id="ct_noidi'.$i.'" value="'.$row1['noidi'].'" /></td>
								<td data-label="Nơi đến"><input type="text" class="text-center" maxlength="3" name="ct_noiden[]" id="ct_noiden'.$i.'" value="'.$row1['noiden'].'" /></td>
								<td data-label="Số vé"><input type="text" class="text-center" maxlength="25" name="ct_sove[]" id="ct_sove'.$i.'" value="'.$row2['sove'].'" /></td>
								<td data-label="PNR"><input type="text" class="text-center" maxlength="25" name="ct_pnr[]" id="ct_pnr'.$i.'" value="'.$row2['pnr'].'" /></td>
								<td data-label="NCC"><select id="ct_nhacc_id'.$i.'" name="ct_nhacc_id[]"><option value=""></option>'.myGetSelectOptionsWithDb('Accounts', $row2['nhacc_id'], 'id', " AND account_type='Supplier' AND is_stop_tracking = 0").'</select></td>
								<td data-label="Tiền hãng hoàn"><input class="allow-number-only text-end" onblur="calculateLineTotal('.$i.')" type="text" maxlength="25" name="ct_sotienhang[]" id="ct_sotienhang'.$i.'" value="'.format_number($row2['sotienhang']).'" /></td>
								<td data-label="Tiền hoàn khách"><input class="allow-number-only text-end" onblur="calculateLineTotal('.$i.')" type="text" maxlength="25" name="ct_sotienkhach[]" id="ct_sotienkhach'.$i.'" value="'.format_number($row2['sotienkhach']).'" /></td>
								<td data-label="Phí DV"><input class="allow-number-only text-end" onblur="calculateLineTotal('.$i.')" type="text" maxlength="25" name="ct_phidichvu[]" id="ct_phidichvu'.$i.'" value="'.format_number($row2['phidichvu']).'" /></td>
								<td data-label="Đã hoàn" class="text-center align-middle">
									<input type="checkbox" name="ct_chk_dahoan[]" id="ct_chk_dahoan'.$i.'" checked="checked" />
									<input type="hidden" name="ct_dahoan[]" id="ct_dahoan'.$i.'" value="1" />
								</td>
								<td data-label="Xóa dòng">
									<button title="Xóa" type="button" class="button-remove-in-edit" onclick="markRowDeleted('.$i.')" >
										<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>	
									</button>
									<input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted'.$i.'" />
									<input type="hidden" name="ct_detail_id[]" id="ct_detail_id'.$i.'" value="" />
								</td> 
							</tr>';
					$i++;
				}// end while 2
				
			} // end while 1
			
		} else {
			$sql = "SELECT c.id AS detail_id
						  ,c.name AS hoten
						  ,c.loaihk
						  ,c.danhxung
						  ,c.ngaysinh
						  ,c.chieubay
						  ,c.airline_code
						  ,c.noidi
						  ,c.noiden
						  ,c.sove
						  ,c.pnr
						  ,c.nhacc_id
						  ,c.dahoan
						  ,c.sotienhang
						  ,c.sotienkhach
						  ,c.phidichvu
					FROM ec_chitiethoanve c
					WHERE c.deleted=0 AND c.hoanve_id='".$this->bean->id."' ";
					
			$res 		= $db->query($sql);
			// $row_count 	= $db->getRowCount($res);
			$row_count 	= $db->countRows($res);
			$i=0;
			while($row = $db->fetchByAssoc($res)){
				$detail_id = isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true' ? '' : $row['detail_id'];

				$html .= '<tr id="ct_line_'.$i.'" class="res">
							<td data-label="Loại HK"><select class="box-select text-center" name="ct_loaihk[]" id="ct_loaihk'.$i.'">'.get_select_options_with_id($app_list_strings['passenger_type_list'], (int)$row['loaihk']).'</select></td>
							<td data-label="Danh xưng"><select class="box-select text-center" name="ct_danhxung[]" id="ct_danhxung'.$i.'">'.get_select_options_with_id($app_list_strings['passenger_salutation_list'], (int)$row['danhxung']).'</select></td>
							<td data-label="Họ tên"><input type="text" class="text-start" maxlength="255" name="ct_hoten[]" id="ct_hoten'.$i.'" value="'.$row['hoten'].'" /></td> 
							<td data-label="Ngày sinh">
								<div class="d-flex align-items-center gap-1">
									<input class="w-80 text-center" type="text" maxlength="10" name="ct_ngaysinh[]" id="ct_ngaysinh'.$i.'" value="'.(trim($row['ngaysinh']) != '' ? date($date_format, strtotime($row['ngaysinh'])) : '').'" />
									<img class="cursor-pointer" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="ct_ngaysinh_trigger'.$i.'" align="absmiddle" />
								</div>
							</td>
							<td data-label="Chiều"><select class="box-select text-center" name="ct_chieubay[]" id="ct_chieubay'.$i.'">'.get_select_options_with_id($app_list_strings['bk_direction_list'], (int)$row['chieubay']).'</select></td>
							<td data-label="Mã hãng"><input type="text" class="ac airline text-center" id="ct_airline_code'.$i.'" name="ct_airline_code[]" value="'.$row['airline_code'].'"></td>
							<td data-label="Nơi đi"><input type="text" class="text-center" maxlength="3" name="ct_noidi[]" id="ct_noidi'.$i.'" value="'.$row['noidi'].'" /></td>
							<td data-label="Nơi đến"><input type="text" class="text-center" maxlength="3" name="ct_noiden[]" id="ct_noiden'.$i.'" value="'.$row['noiden'].'" /></td>
							<td data-label="Số vé"><input type="text" class="text-center" maxlength="25" name="ct_sove[]" id="ct_sove'.$i.'" value="'.$row['sove'].'" /></td>
							<td data-label="PNR"><input type="text" class="text-center" maxlength="25" name="ct_pnr[]" id="ct_pnr'.$i.'" value="'.$row['pnr'].'" /></td>
							<td data-label="NCC"><select id="ct_nhacc_id'.$i.'" name="ct_nhacc_id[]"><option value=""></option>'.myGetSelectOptionsWithDb('Accounts', $row['nhacc_id'], 'id', " AND account_type='Supplier' AND is_stop_tracking = 0").'</select></td>
							<td data-label="Tiền hoàn hãng"><input class="text-end allow-number-only" onblur="calculateLineTotal('.$i.')" type="text" maxlength="25" name="ct_sotienhang[]" id="ct_sotienhang'.$i.'" value="'.format_number($row['sotienhang']).'" /></td>
							<td data-label="Tiền hoàn khách"><input class="text-end allow-number-only" onblur="calculateLineTotal('.$i.')" type="text" maxlength="25" name="ct_sotienkhach[]" id="ct_sotienkhach'.$i.'" value="'.format_number($row['sotienkhach']).'" /></td>
							<td data-label="Phí dịch vụ"><input class="text-end allow-number-only2" onblur="calculateLineTotal('.$i.')" type="text" maxlength="25" name="ct_phidichvu[]" id="ct_phidichvu'.$i.'" value="'.format_number($row['phidichvu']).'" /></td>
							<td data-label="Đã hoàn" class="text-center align-middle">
								<input type="checkbox" name="ct_chk_dahoan[]" id="ct_chk_dahoan'.$i.'" '.($row['dahoan'] ? 'checked="checked"' : '').' />
								<input type="hidden" name="ct_dahoan[]" id="ct_dahoan'.$i.'" value="'.$row['dahoan'].'" />
							</td> 
							<td data-label="Xóa dòng">
								<button title="Xóa" type="button" class="button-remove-in-edit" onclick="markRowDeleted('.$i.')">
									<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>	
								</button>
								<input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted'.$i.'" />
								<input type="hidden" name="ct_detail_id[]" id="ct_detail_id'.$i.'" value="'.$detail_id.'" />
							</td>
						</tr>';
				$i++;
			}
		}// end else
		
		$tongtienhang 		= isset($_POST['tongtienhang']) && !empty($_POST['tongtienhang']) ? $_POST['tongtienhang'] : $this->bean->tongtienhang;
		$tongtienkhach 	= isset($_POST['tongtienkhach']) && !empty($_POST['tongtienkhach']) ? $_POST['tongtienkhach'] : $this->bean->tongtienkhach;
		$tongtiendv 		= isset($_POST['tongtiendv']) && !empty($_POST['tongtiendv']) ? $_POST['tongtiendv'] : $this->bean->tongtiendv;
		$row_count 		= (isset($row_count) && !empty($row_count)) ? $row_count : 0;
		
		// $sep = get_number_seperators();
		$sep = my_get_number_separators();

		$html .= '<tr id="last-row" class="footer-tr">
					<td class="text-start" colspan="11">
						<input type="hidden" id="grp_seperator" name="grp_seperator" value="'.$sep[0].'" />
						<input type="hidden" id="dec_seperator" name="dec_seperator" value="'.$sep[1].'" />
						<input type="hidden" id="sig_digits" name="sig_digits" value="'.$locale->getPrecision().'" />
						<input type="hidden" id="row_count" name="row_count" value="'.$row_count.'" />
						<input type="hidden" id="cal_date_format" name="cal_date_format" value="'.$timedate->get_cal_date_format().'" />
						<input type="hidden" id="loaihk_list" name="loaihk_list" value="'.get_select_options_with_id($app_list_strings['passenger_type_list'],'').'" />
						<input type="hidden" id="danhxung_list" name="danhxung_list" value="'.get_select_options_with_id($app_list_strings['passenger_salutation_list'],'').'" />
						<input type="hidden" id="chieubay_list" name="chieubay_list" value="'.get_select_options_with_id($app_list_strings['bk_direction_list'],'').'" />
						<input type="hidden" id="aircode_list" name="aircode_list" value="'.get_select_options_with_id($app_list_strings['aircode_list'],'').'" />
						<input type="hidden" id="ncc_list" name="ncc_list" value="'.str_replace('"',"'",myGetSelectOptionsWithDb('Accounts', '', 'id', " AND account_type='Supplier' AND is_stop_tracking = 0")).'" />
						<input type="button" class="btn btn-primary" id="btnAddRow" name="btnAddRow" value="Thêm dòng" title="Thêm dòng" />
						Số dòng = <label id="lbl_row_count">'.$row_count.'</label>
					</td>
				<td data-label="Tổng tiền hoàn hãng">
					<input readonly="readonly" type="text" style="color: #ec2029;" class="text-end fw-bold" name="tongtienhang" id="tongtienhang" value="'.format_number($tongtienhang).'" />
				</td>
				<td data-label="Tổng tiền hoàn khách">
					<input readonly="readonly" type="text" style="color: #ec2029;" class="text-end fw-bold" name="tongtienkhach" id="tongtienkhach" value="'.format_number($tongtienkhach).'" />
				</td>
				<td data-label="Tổng phí DV">
					<input readonly="readonly" type="text" style="color: #ec2029;" class="text-end fw-bold" name="tongtiendv" id="tongtiendv" value="'.format_number($tongtiendv).'" />
				</td>
				<td class="hide-mobile show-landscape">&nbsp;</td>
				<td class="hide-mobile show-landscape">&nbsp;</td>
			</tr>';
		$html .= '</table>';
		
		$this->ss->assign('LINE_ITEMS', $html);	
	}
}
?>