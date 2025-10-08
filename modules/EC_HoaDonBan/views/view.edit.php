<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_HoaDonBanViewEdit extends ViewEdit {
	public function __construct() {
		parent::__construct();
	}

	public function display() {
		$this->css();
		$this->populateLineItems();
		parent::display();
		$this->js();
	}

	public function css() {
		$css = '<link type="text/css" rel="stylesheet" href="modules/EC_HoaDonBan/css/view.edit.css?v=1.0.0">';
		echo $css;
	}

	public function js() {
		$js = '<script src="modules/EC_HoaDonBan/js/view.edit.js?v=1.0.0"></script>';
		echo $js;
	}

	protected function populateLineItems() {
		global $locale;

		// CCCD/Passport
		$id_number = $this->bean->citizen_id ?? '';
		if(empty($id_number)) $id_number = $this->bean->passport_number ?? '';
		$this->ss->assign('CUSTOM_ID_NUMBER', '<input type="text" name="identity_number" value="'.$id_number.'" id="identity_number_input" maxlength="12" />');

		// MST
		$mst_value = $this->bean->masothue ?? ($_REQUEST['masothue'] ?? '');
		$custom_mst = '<div class="wrap-masothue">
			<input type="text" name="masothue" id="masothue" size="30" maxlength="25" value="'.$mst_value.'">
			<span class="mst-active">
				<svg width="22px" height="22px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#28a745" stroke-width="1.5">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 17.9371 6.06294 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12C22.75 6.06294 17.9371 1.25 12 1.25ZM7.53044 11.9697C7.23755 11.6768 6.76268 11.6768 6.46978 11.9697C6.17689 12.2626 6.17689 12.7374 6.46978 13.0303L9.46978 16.0303C9.76268 16.3232 10.2376 16.3232 10.5304 16.0303L17.5304 9.03033C17.8233 8.73744 17.8233 8.26256 17.5304 7.96967C17.2375 7.67678 16.7627 7.67678 16.4698 7.96967L10.0001 14.4393L7.53044 11.9697Z" fill="#28a745"></path>
				</svg>
			</span>
			<span class="mst-alert">
				<svg width="22px" height="22px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#dc3545" stroke-width="1.5">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M1.25 12C1.25 6.06294 6.06294 1.25 12 1.25C17.9371 1.25 22.75 6.06294 22.75 12C22.75 17.9371 17.9371 22.75 12 22.75C6.06294 22.75 1.25 17.9371 1.25 12ZM12 6.25C12.4142 6.25 12.75 6.58579 12.75 7V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V7C11.25 6.58579 11.5858 6.25 12 6.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#dc3545"></path>
				</svg>
			</span>
			<span id="icon-search-masothue" class="icon-search">
				<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#333">
					<path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path>
				</svg>
			</span>
	  	</div>';
		$this->ss->assign('CUSTOM_MST', $custom_mst);

		$html = '<table class="table-edit-hoadonban table-details__booking" cellpadding="0" cellspacing="0" border="0" >';
		$html .= '<thead><tr id="first-row"></tr></thead>'; // Tiêu đề

		// Các dòng chi tiết
		$i = $total_price = $total_vat = $total_authorized = $total_service = $total_giamua = 0;
		if (isset($_POST['create_invoice']) && !empty($_POST['booking_id'])) {
			// $i = 0;
			// $html .= '<tr id="ct_line_' . $i . '">';
			// $html .= "
			// 	<td>
			// 		<input style='text-align:left;' class='ac_booking' ln='" . $i . "' type='text' name='ct_booking[]' id='ct_booking" . $i . "' value='" . $_POST['booking'] . "' maxlength='255' size='30' autocomplete='off' fld='{\"id\":\"ct_booking_id" . $i . "\",\"name\":\"ct_booking" . $i . "\"}'/>
			// 		<input type='hidden' name='ct_booking_id[]' id='ct_booking_id" . $i . "' value='" . $_POST['booking_id'] . "' />
			// 	</td>
			// ";

			// $html .= '
			// 	<td>
			// 		<div class="d-flex align-items-center gap-1">
			// 			<input class="ac_ticket_number text-start" ln="' . $i . '" type="text" name="ct_ticket_number[]" id="ct_ticket_number' . $i . '" maxlength="255" size="30" autocomplete="off" />
			// 			<input type="hidden" name="ct_ticket_number_id[]" id="ct_ticket_number_id' . $i . '" value="" />
			// 			<button title="Tìm" class="button-pick" type="button" onclick="openTicketNumberPopup(' . $i . ')">
			// 				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
			// 			</button>
			// 		</div>
			// 	</td>
			// ';

			// $html .= '
			// 	<td>
			// 		<div class="d-flex align-items-center gap-1">
			// 			<input class="ac_ticket_code text-start" ln="' . $i . '" type="text" name="ct_ticket_code[]" id="ct_ticket_code' . $i . '" maxlength="255" size="30" autocomplete="off" />
			// 			<button title="Tìm" class="button-pick" type="button" onclick="openTicketNumberPopup(' . $i . ')">
			// 				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
			// 			</button>
			// 		</div>
			// 	</td>
			// ';

			// $html .= '<td><input class="allow-number-only text-end" onblur="calculateLineTotal(' . $i . ')" value="1" type="text" name="ct_qty[]" id="ct_qty' . $i . '" size="5" maxlength="20" max_qty=""/></td>';

			// $html .= '<td><input class="allow-number-only text-end" onblur="calculateLineTotal(' . $i . ')" value="" type="text" name="ct_price[]" id="ct_price' . $i . '" size="14" maxlength="20" /></td>';

			// $html .= '<td><input class="allow-number-only text-end" onblur="calculateLineTotal(' . $i . ')" value="" type="text" name="ct_vat[]" id="ct_vat' . $i . '" size="14" maxlength="20" /></td>';

			// $html .= '<td><input class="allow-number-only text-end" onblur="calculateLineTotal(' . $i . ')" value="" type="text" name="ct_authorized[]" id="ct_authorized' . $i . '" size="14" maxlength="20" /></td>';

			// $html .= '<td><input class="allow-number-only text-end" onblur="calculateLineTotalReturn(' . $i . ')" value="" type="text" name="ct_total[]" id="ct_total' . $i . '" size="14" maxlength="20" /></td>';

			// $html .= '<td class="text-center">
			// 			<button title="Xóa" class="button-remove-in-edit" type="button" onclick="markRowDeleted(' . $i . ')"> 
			// 				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
			// 			</button>
			// 			<input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted' . $i . '" />
			// 			<input type="hidden" name="ct_detail_id[]" id="ct_detail_id' . $i . '" value="" />
			// 		</td>';

			// $html .= '</tr>';
			$row_count = 0;
		}
		else {
			$sql = '
				SELECT ct.*
					,(SELECT qty FROM ec_input_invoices WHERE id = ct.ticket_number_id) AS max_qty
					,in_iv.ticket_code
					,IFNULL(re.name, "") AS receipt_voucher_name
				FROM ec_chitiethoadon ct 
					LEFT JOIN ec_input_invoices in_iv ON in_iv.id = ct.ticket_number_id
					LEFT JOIN ec_receipt_voucher re ON re.id = ct.receipt_voucher_id
				WHERE ct.parent_id = "' . $this->bean->id . '" AND ct.deleted = 0
				ORDER BY ct.order_by_no';

			$res = $this->bean->db->query($sql);
			$row_count	= $this->bean->db->countRows($res);
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				$detail_id = isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true' ? '' : $row['id'];

				$html .= '<tr id="ct_line_' . $i . '">';

				if ($this->bean->loaihoadon == 0) {
					$html .= '<td>
						<select name="ct_code[]" id="ct_code'.$i.'">'
							.get_select_options_with_id($GLOBALS['app_list_strings']['invoice_mahang_list'], $row['mahang']).
						'</select>
					</td>';

					$input_type_receipt = !empty($row['receipt_voucher_name']) ? 'text' : 'hidden';
					$html .= "<td>
						<input type='text' name='ct_booking[]' id='ct_booking$i' ln='$i' class='ac_booking' value='". $row['booking'] ."' maxlength='32' size='30' autocomplete='off' fld='{\"id\":\"ct_booking_id$i\",\"name\":\"ct_booking$i\"}' style='text-align:left' />
						<input type='hidden' name='ct_booking_id[]' id='ct_booking_id$i' value='". $row['booking_id'] ."' />
						<input type='$input_type_receipt' name='ct_receipt_voucher[]' class='input-receipt-voucher' value='". $row['receipt_voucher_name'] ."' placeholder='Mã phiếu thu' style='border:1px solid #c2c2c2 !important; border-radius:4px; margin-top:5px; padding-left:5px !important;' />
					</td>";

					$html .= '
						<td>
							<div class="d-flex align-items-center gap-1">
								<input class="ac_ticket_number text-start" ln="' . $i . '" type="text" name="ct_ticket_number[]" id="ct_ticket_number' . $i . '" value="' . $row['name'] . '" maxlength="255" size="30" autocomplete="off" />
								<input type="hidden" name="ct_ticket_number_id[]" id="ct_ticket_number_id' . $i . '" value="' . $row['ticket_number_id'] . '"  />
								<button title="Tìm" class="button-pick" type="button" onclick="openTicketNumberPopup(' . $i . ')">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
								</button>
							</div>
						</td>
					';

					// $html .= '
					// 	<td>
					// 		<div class="d-flex align-items-center gap-1">
					// 			<input class="ac_ticket_code" ln="' . $i . '" type="text" name="ct_ticket_code[]" id="ct_ticket_code' . $i . '" value="' . $row['ticket_code'] . '" maxlength="255" size="30" autocomplete="off" />
					// 			<button title="Tìm" type="button" class="button-pick" onclick="openTicketNumberPopup(' . $i . ')">
					// 				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
					// 			</button>
					// 		</div>
					// 	</td>
					// ';
				}
				else if ($this->bean->loaihoadon == 1) {
					$html .= '
						<td>
							<input class="text-start" ln="' . $i . '" type="text" name="ct_name[]" id="ct_name' . $i . '" value="' . $row['name'] . '" maxlength="255" size="30" autocomplete="off" />
						</td>
					';
				}

				// Số lượng
				$html .= '<td class="text-center"><input class="allow-number-only text-center" onblur="calculateLineTotal('.$i.')" value="' . format_number($row['soluong']) . '" type="text" name="ct_qty[]" id="ct_qty' . $i . '" max_qty="' . format_number($row['max_qty']) . '"/></td>';

				// Giá mua
				$html .= '<td class="text-end"><input class="allow-number-only text-end" onblur="calculateLineTotal('.$i.')" value="' . format_number($row['giamua']) . '" type="text" name="ct_purchase_price[]" id="ct_purchase_price'.$i.'" /></td>';

				// Thu hộ
				$html .= '<td><input class="allow-number-only text-end" onblur="calculateLineTotal('.$i.')" value="' . format_number($row['phithuho']) . '" type="text" name="ct_authorized[]" id="ct_authorized'.$i.'" /></td>';

				// Phí sân bay
				$html .= '<td><input class="allow-number-only text-end" value="' . format_number($row['phisanbay']) . '" type="text" name="ct_airport_fee[]" id="ct_airport_fee'.$i.'" /></td>';

				// Phí khác
				$html .= '<td><input class="allow-number-only text-end" value="' . format_number($row['phikhac']) . '" type="text" name="ct_other_fee[]" id="ct_other_fee'.$i.'" /></td>';

				// Phí DV
				$html .= '<td><input class="allow-number-only text-end" onblur="calculateLineTotal('.$i.')" value="' . format_number($row['phidv']) . '" type="text" name="ct_service[]" id="ct_service'.$i.'" /></td>';

				// Thuế suất
				$html .= '<td>
					<select name="ct_percent_vat[]" id="ct_percent_vat'.$i.'" onchange="calculateLineTotal('.$i.')">
						'.get_select_options_with_id($GLOBALS['app_list_strings']['invoice_percent_vat_list'], $row['thuesuat']).'
					</select>
				</td>';
				
				// Giá bán
				$html .= '<td><input class="allow-number-only text-end" value="' . format_number($row['dongia']) . '" type="text" name="ct_price[]" id="ct_price'.$i.'" readonly /></td>';

				// VAT
				$html .= '<td><input class="allow-number-only text-end" onblur="calculateChangeVAT('.$i.')" value="' . format_number($row['tienthue']) . '" type="text" name="ct_vat[]" id="ct_vat'.$i.'" /></td>';

				// Thành tiền
				$html .= '<td><input class="allow-number-only text-end" value="' . format_number($row['thanhtien']) . '" type="text" name="ct_total[]" id="ct_total'.$i.'" readonly /></td>';

				$html .= '<td class="text-center">
							<button title="Xóa" class="button-remove-in-edit" type="button" onclick="markRowDeleted(' . $i . ')">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
							</button>
							<input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted' . $i . '" />
							<input type="hidden" name="ct_detail_id[]" id="ct_detail_id' . $i . '" value="' . $detail_id . '" />
						</td>';
						
				$html .= '</tr>';

				$i++;
				$total_price += $row['dongia'] * $row['soluong'];
				$total_vat += $row['tienthue'] ;
				$total_service += $row['phidv'] * $row['soluong'];
				$total_authorized += $row['phithuho'] * $row['soluong'];
				$total_giamua += $row['giamua'] * $row['soluong'];
			}
		}

		$sep = my_get_number_separators();
		$html .= '<tr id="last-row" class="footer-tr">
			<td colspan="3">
				<input type="hidden" id="grp_seperator" name="grp_seperator" value="' . $sep[0] . '" />
				<input type="hidden" id="dec_seperator" name="dec_seperator" value="' . $sep[1] . '" />
				<input type="hidden" id="sig_digits" name="sig_digits" value="' . $locale->getPrecision() . '" />
				<input type="hidden" id="row_count" name="row_count" value="' . $row_count . '" />
				<div class="d-flex align-items-center gap-2">
					<input type="button" class="btn btn-primary" id="btnAddRow" name="btnAddRow" value="Thêm dòng" title="Thêm dòng" />
					Số dòng = <label id="lbl_row_count" class="text-label">' . $row_count . '</label>
				</div>
			</td>
			<td>
				<input readonly="readonly" type="text" class="text-center" name="tongsl" id="tongsl" value="' . format_number($this->bean->tongsl) . '" />
			</td>

			<td class="text-end">
				<input readonly="readonly" type="text" class="text-end" name="tonggiamua" id="tonggiamua" value="' . format_number($total_giamua) . '" />
			</td>
			<td>
				<input readonly="readonly" type="text" class="text-end" name="tongthuho" id="tongthuho" value="' . format_number($total_authorized) . '" />
			</td>
			<td></td>
			<td></td>
			<td>
				<input readonly="readonly" type="text" class="text-end" name="tongdichvu" id="tongdichvu" value="' . format_number($total_service) . '" />
			</td>
			<td></td>
			<td>
				<input readonly="readonly" type="text" class="text-end" name="tonggiaban" id="tonggiaban" value="' . format_number($total_price) . '" />
			</td>
			<td>
				<input readonly="readonly" type="text" class="text-end" name="tongvat" id="tongvat" value="' . format_number($total_vat) . '" />
			</td>
			<td>
				<input readonly="readonly" type="text" class="text-end" name="tongthanhtoan" id="tongthanhtoan" value="' . format_number($this->bean->tongthanhtoan) . '" />
			</td>
			<td></td>
		</tr>';

		$html .= '</table>';
		$html .= '
			<div class="wrap-explain mt-3">
				<h4 class="sub-heading-title mb-1">Diễn giải các cột</h4>
				<table class="table-details__booking w-40">
					<tr>
						<td class="fw-semibold">Giá mua</td>
						<td>Tổng giá mua từ hóa đơn đầu vào tương ứng (đã gồm thu hộ)</td>
					</tr>
					<tr>
						<td class="fw-semibold">Thu hộ</td>
						<td>Phí thu hộ từ hóa đơn đầu vào tương ứng</td>
					</tr>
					<tr>
						<td class="fw-semibold">Phí sân bay</td>
						<td>Tách ra từ cột Thu hộ</td>
					</tr>
					<tr>
						<td class="fw-semibold">Phí khác</td>
						<td>Tách ra từ cột Thu hộ</td>
					</tr>
					<tr>
						<td class="fw-semibold">Phí DV</td>
						<td>Phí dịch vụ</td>
					</tr>
					<tr>
						<td class="fw-semibold">Giá bán</td>
						<td>Đơn giá trên hóa đơn</td>
					</tr>
					<tr>
						<td class="fw-semibold">VAT</td>
						<td>Thuế giá trị gia tăng</td>
					</tr>
					<tr>
						<td class="fw-semibold">Thành tiền</td>
						<td>= (Giá bán + VAT + Thu hộ) x Số lượng</td>
					</tr>
				</table>
			</div>
		';
		
		$this->ss->assign('LINE_ITEMS', $html);
	}
}
