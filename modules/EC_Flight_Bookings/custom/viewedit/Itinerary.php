<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Itinerary edit table rendering.
 *
 * Used by EC_Flight_BookingsViewEdit. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait ECFlightBookingEditItineraryTrait
{
	function populateLineItineraries()
	{
		global $app_list_strings, $timedate;

		$date_format = $timedate->get_date_format();

		// Khi nhân bản booking, giữ lại mã booking cũ để JS/Save phân biệt dữ liệu nhân bản.
		$booking_prev_name = (isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true') ? $this->bean->name : '';

		// Query hành trình gốc của booking; row HTML sẽ được render phía JS từ JSON.
		$sql = "SELECT i.id AS detail_id,
					i.direction,
					i.airline_code,
					i.flight_number,
					i.ticket_class,
					i.departure,
					i.arrival,
					i.departure_date,
					i.arrival_date,
					i.base_price,
					i.is_layover,
					i.description,
					i.time_limit
				FROM ec_booking_itineraries i
				WHERE i.booking_id = '{$this->bean->id}'
					AND add_type = 0 AND i.deleted = 0
				ORDER BY i.direction, i.transit_order, i.date_entered, i.departure_date";

		$res = $this->bean->db->query($sql);
		$row_count = $this->bean->db->countRows($res);
		$row_count = !empty($row_count) ? $row_count : 0;

		/**
		 * BƯỚC 1: THU THẬP DỮ LIỆU THÀNH MẢNG (CHỈ DỮ LIỆU, KHÔNG HTML)
		 * Row sẽ được render bằng JS (`renderInitialItineraries()` + `insertItineraryLine(ln)`).
		 */
		$itineraries_data = [];
		if (!empty($this->bean->id)) {
			$i = 0;
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				$itineraries_data[] = [
					'db_id' => $row['detail_id'],
					'id' => $row['detail_id'],
					'direction' => (int) $row['direction'],
					'airline_code' => $row['airline_code'],
					'flight_number' => $row['flight_number'],
					'ticket_class' => $row['ticket_class'],
					'departure' => $row['departure'],
					'arrival' => $row['arrival'],
					'departure_date' => $row['departure_date'] != '' ? date($date_format, strtotime($row['departure_date'])) : '',
					'departure_h' => $row['departure_date'] != '' ? date('H', strtotime($row['departure_date'])) : '',
					'departure_m' => $row['departure_date'] != '' ? date('i', strtotime($row['departure_date'])) : '',
					'arrival_date' => $row['arrival_date'] != '' ? date($date_format, strtotime($row['arrival_date'])) : '',
					'arrival_h' => $row['arrival_date'] != '' ? date('H', strtotime($row['arrival_date'])) : '',
					'arrival_m' => $row['arrival_date'] != '' ? date('i', strtotime($row['arrival_date'])) : '',
					'time_limit_date' => $row['time_limit'] != '' ? date($date_format, strtotime($row['time_limit'])) : '',
					'time_limit_h' => $row['time_limit'] != '' ? date('H', strtotime($row['time_limit'])) : '',
					'time_limit_m' => $row['time_limit'] != '' ? date('i', strtotime($row['time_limit'])) : '',
					'base_price' => (float) $row['base_price'],
					'is_layover' => (int) $row['is_layover'],
				];

				// Lưu thông tin airline/ticket_class theo direction để dùng ở chỗ khác
				if ($row['direction'] == '0') {
					$this->_outbound_airline = $row['airline_code'];
					$this->_outbound_ticket_class = $row['ticket_class'];
					if ($i == 0) {
						$this->_journey = $row['departure'] . '-' . $row['arrival'];
					} else {
						$this->_journey = substr($this->_journey, 0, 3) . '-' . $row['arrival'];
					}
				}

				if ($row['direction'] == '1') {
					$this->_inbound_airline = $row['airline_code'];
					$this->_inbound_ticket_class = $row['ticket_class'];
				}

				$i++;
			}
		}

		/**
		 * BƯỚC 2: TẠO HTML KHUNG BẢNG (không render row ở PHP)
		 * - Header
		 * - <tbody id="iti_tbody"></tbody> rỗng để JS render
		 * - Footer với các input hidden cần thiết + JSON data
		 */

		$html = '<table id="tbl_line_itineraries" class="table-vertical__mobile table-edit__booking table-details__booking" border="0" cellpadding="0" cellspacing="0">';
		$html .= '<thead>
			<tr id="iti_first_row">
				<th scope="col" style="width:8%;" class="text-center">Chiều</th>
				<th scope="col" style="width:5%;" class="text-center">Mã hãng</th>
				<th scope="col" style="width:7%;" class="text-center">Số hiệu</th>
				<th scope="col" style="width:11%;" class="text-center">Hạng vé</th>
				<th scope="col" style="width:5%;" class="text-center">Nơi đi</th>
				<th scope="col" style="width:5%;" class="text-center">Nơi đến</th>
				<th scope="col" style="width:14%;" class="text-center">Ngày giờ đi</th>
				<th scope="col" style="width:14%;" class="text-center">Ngày giờ đến</th>
				<th scope="col" style="width:14%;" class="text-center">Hạn giữ chỗ</th>
				<th scope="col" style="width:8%;" class="text-center">Giá cơ bản</th>
				<th scope="col" style="width:2%;" class="text-center">Quá cảnh</th>
				<th scope="col" style="width:3%;" class="text-center">&nbsp;</th>
			</tr>
		</thead>';
		$html .= '<tbody id="iti_tbody"></tbody>';
		$html .= '<tr id="iti_last_row" class="footer-tr">';
		$html .= '<td colspan="12" class="text-start">';
		$html .= '<input type="hidden" name="discount_percent_list" id="discount_percent_list" value="' . get_select_options_with_id($app_list_strings['discount_percent_list'], '') . '" />';
		$html .= '<input type="hidden" name="direction_list" id="direction_list" value="' . get_select_options_with_id($app_list_strings['bk_direction_list'], '') . '" />';
		$html .= '<input type="hidden" name="passenger_type_list" id="passenger_type_list" value="' . get_select_options_with_id($app_list_strings['passenger_type_list'], '') . '" />';
		$html .= '<input type="hidden" name="passenger_salutation_list" id="passenger_salutation_list" value="' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], '') . '" />';
		$html .= '<input type="hidden" id="iti_row_count" name="iti_row_count" value="' . $row_count . '" />';
		$html .= '<input type="hidden" id="booking_prev_name" name="booking_prev_name" value="' . $booking_prev_name . '" />';
		$html .= '<input type="hidden" id="journey" name="journey" value="' . $this->_journey . '" />';
		$html .= '<input type="hidden" id="iti_data_json" value=\'' . htmlspecialchars(json_encode($itineraries_data), ENT_QUOTES, 'UTF-8') . '\' />';
		$html .= '<input type="button" class="btn btn-primary" id="btnItineraryAddRow" value="Thêm dòng" title="Thêm dòng" />';
		$html .= ' Số dòng = <label id="lbl_iti_row_count">' . $row_count . '</label>';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		/**
		 * BƯỚC 3: GỬI DỮ LIỆU VỀ TEMPLATE
		 */
		// Gửi HTML khung bảng (row sẽ được render bằng JS)
		$this->ss->assign('LINE_ITINERARIES', $html);
	}
}
