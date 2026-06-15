<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Ticket price/detail edit table rendering.
 *
 * Used by EC_Flight_BookingsViewEdit. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait ECFlightBookingEditLineDetailsTrait
{

	function populateLineDetails()
	{
		global $app_list_strings, $current_user;
		$supplier_cus_sql = " AND account_type = 'Supplier' AND is_stop_tracking = 0 ";

		// Danh sách nhà cung cấp còn theo dõi, truyền xuống JS để render select NCC từng dòng.
		$supplier_list = str_replace('"', "'", myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', '', 'id', $supplier_cus_sql));

		// Query chi tiết vé/giá gốc; row HTML sẽ được render phía JS từ JSON.
		$sql = "SELECT id AS detail_id
					   ,direction
					   ,passenger_type
					   ,quantity
					   ,unit_price
					   ,tax_and_fee
					   ,airport_fee
					   ,admin_fee, vat_admin, admin_fee_no_vat
					   ,service_fee
					   ,total_price
					   ,total_bought_price
					   ,fee_bought
					   ,supplier_id
					   ,supplier_discount
				FROM ec_booking_details
				WHERE booking_id='" . $this->bean->id . "'
				AND deleted = 0
				ORDER BY direction, passenger_type, date_entered ";

		$res = $this->bean->db->query($sql);
		$row_count = $this->bean->db->countRows($res);
		$row_count = !empty($row_count) ? $row_count : 0;

		/**
		 * BƯỚC 1: THU THẬP DỮ LIỆU THÀNH MẢNG (CHỈ DỮ LIỆU, KHÔNG HTML)
		 * Row sẽ được render bằng JS (`renderInitialDetails()` + `insertDetailLine(ln)`).
		 */
		$details_data = [];
		if (!empty($this->bean->id)) {
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				$details_data[] = [
					'db_id' => $row['detail_id'],
					'id' => $row['detail_id'],
					'direction' => (int) $row['direction'],
					'passenger_type' => (int) $row['passenger_type'],
					'quantity' => (int) $row['quantity'],
					'unit_price' => (float) $row['unit_price'],
					'tax_and_fee' => (float) $row['tax_and_fee'],
					'airport_fee' => (float) $row['airport_fee'],
					'admin_fee' => (float) $row['admin_fee'],
					'admin_fee_no_vat' => (float) $row['admin_fee_no_vat'],
					'vat_admin' => (float) $row['vat_admin'],
					'service_fee' => (float) $row['service_fee'],
					'total_price' => (float) $row['total_price'],
					'total_bought_price' => (float) ((float) $row['total_bought_price'] ? $row['total_bought_price'] : ($row['total_price'] - ($row['service_fee']) * $row['quantity'])),
					'fee_bought' => (float) $row['fee_bought'],
					'supplier_id' => $row['supplier_id'],
					'supplier_discount' => (float) $row['supplier_discount'],
				];
			}
		}

		$total_qty = isset($_POST['total_qty']) && !empty($_POST['total_qty']) ? $_POST['total_qty'] : (isset($this->bean->total_qty) ? $this->bean->total_qty : 0);
		$subtotal_amount = isset($_POST['subtotal_amount']) && !empty($_POST['subtotal_amount']) ? $_POST['subtotal_amount'] : (isset($this->bean->subtotal_amount) ? $this->bean->subtotal_amount : 0);
		$total_bought_amount = isset($_POST['total_bought_amount']) && !empty($_POST['total_bought_amount']) ? $_POST['total_bought_amount'] : (isset($this->bean->total_bought_amount) ? $this->bean->total_bought_amount : 0);

		/**
		 * BƯỚC 2: TẠO HTML KHUNG BẢNG (không render row ở PHP)
		 * - Header
		 * - <tbody id="bkd_tbody"></tbody> rỗng để JS render
		 * - Footer với các input hidden cần thiết + JSON data
		 */
		$html = '<table id="tbl_line_details" class="table-vertical__mobile table-edit__booking table-details__booking" cellpadding="0" cellspacing="0" border="0">';
		$html .= '<thead>
				<tr id="bkd_first_row">
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Chiều</th>
					<th scope="col" style="width:9%;" class="text-center fw-semibold">Loại HK</th>
					<th scope="col" style="width:3%;" class="text-center fw-semibold">SL</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Giá cơ bản</th>
					<th scope="col" style="width:6%;" class="text-center fw-semibold">VAT</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Phí sân bay</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Phí admin</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Phí dịch vụ</th>
					<th scope="col" style="width:9%;" class="text-center fw-semibold">Thành tiền</th>
					<th scope="col" style="width:9%;" class="text-center fw-semibold">Giá mua</th>
					<th scope="col" style="width:8%;" class="text-center fw-semibold">Chiết khấu</th>
					<th scope="col" style="width:8%;" class="text-center fw-semibold">Phí xuất vé</th>
					<th scope="col" class="text-center fw-semibold">NCC</th>
					<th scope="col" style="width:3%;" class="text-center fw-semibold">&nbsp;</th>
				</tr>
			</thead>';
		$html .= '<tbody id="bkd_tbody"></tbody>';
		$html .= '<tr id="bkd_last_row" class="footer-tr">';
		$html .= '<td colspan="2">';
		$html .= '<input type="hidden" name="bkd_row_count" id="bkd_row_count" value="' . $row_count . '" />';
		$html .= '<input type="hidden" name="supplier_list" id="supplier_list" value="' . $supplier_list . '" />';
		$html .= '<input type="hidden" id="bkd_data_json" value=\'' . htmlspecialchars(json_encode($details_data), ENT_QUOTES, 'UTF-8') . '\' />';
		$html .= '<div class="d-flex align-items-center gap-2">';
		$html .= '<input type="button" class="btn btn-primary" id="btnDetailAddRow" value="Thêm dòng" title="Thêm dòng" />';
		$html .= '<p>Số dòng = <span id="lbl_bkd_row_count">' . $row_count . '</span></p>';
		$html .= '</div>';
		$html .= '</td>';
		$html .= '<td data-label="Tổng số vé"><input type="text" readonly="readonly" name="total_qty" id="total_qty" value="' . format_number($total_qty) . '" /></td>';
		$html .= '<td class="hide-mobile" colspan="5"></td>';
		$html .= '<td data-label="Tổng thành tiền" class="text-center">';
		$html .= '<input type="text" class="text-danger" readonly="readonly" name="subtotal_amount" id="subtotal_amount" value="' . format_number($subtotal_amount) . '" />';
		$html .= '</td>';
		$html .= '<td data-label="Tổng giá mua" class="text-center">';
		$html .= '<input type="text" class="text-danger" readonly="readonly" name="total_bought_amount" id="total_bought_amount" value="' . format_number($total_bought_amount) . '" />';
		$html .= '</td>';
		$html .= '<td class="hide-mobile" colspan="4"></td>';
		$html .= '</tr>';
		$html .= '</table>';

		/**
		 * BƯỚC 3: GỬI DỮ LIỆU VỀ TEMPLATE
		 */
		// Gửi HTML khung bảng (row sẽ được render bằng JS)
		$this->ss->assign('LINE_DETAILS', $html);
	}
}
