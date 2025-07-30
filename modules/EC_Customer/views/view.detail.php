<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_CustomerViewDetail extends ViewDetail {

	function display() {
		$this->inforCustomer();
		parent::display();
	}

	function inforCustomer(){
		global $app_list_strings;

		$html = '';
		$html .= '<div class="details-customer--wrap"><table cellpadding="0" cellspacing="0" border="0" class="table-details__customer table-details__booking" >';
		$html .= '<thead><tr id="first-row">
		<th width="10%" class="fw-bold text-center">Booking</th>
		<th width="10%" class="fw-bold text-center">Hành trình</th>
		<th width="8%" class="fw-bold text-center">Trạng thái</th>
		<th width="10%" class="fw-bold text-center">Ngày đặt vé</th>
		<th width="15%" class="fw-bold text-center">Họ tên</th>
		<th width="10%" class="fw-bold text-center">Email</th>
		<th width="5%" class="fw-bold text-center">Số vé</th>
		<th width="10%" class="fw-bold text-center">Tổng tiền</th>
		<th width="10%" class="fw-bold text-center">Doanh số</th>
		<th width="15%" class="fw-bold text-center">IP</th>
	</tr></thead>';

	// Moi dữ liệu lên để hiển thị
	$sql = 'SELECT c.info_data
			FROM ec_customer c
			WHERE c.deleted=0
			AND c.phone = "'.$this->bean->phone.'"
		';

	$res = $this->bean->db->query($sql);

	// Xử lý dữ liệu trong mảng $arrInfoData
	$total_price 			= 0;
	$total_revenue 		= 0;
	$total_sove 			= 0;
	$total_price_datduoc 	= 0;
	$total_revenue_datduoc 	= 0;
	$total_sove_datduoc 	= 0;

	while($row = $this->bean->db->fetchByAssoc($res)){
		$arr = json_decode(html_entity_decode($row['info_data']), true);
		$array_reverse = array_reverse($arr);

		foreach($array_reverse as $key => $val){

			if($val['booking_status'] == 2){ //CHỜ THANH TOÁN
				$color = 'color: #f9b113';
			}
			elseif($val['booking_status'] == 3){ //XÁC NHẬN
				$color = 'color: #004eff';
			}
			elseif($val['booking_status'] == 4){ //HỦY
				$color = 'color: #EC2029';
			}
			elseif($val['booking_status'] == 6){ //ĐÃ GỌI
				$color = 'color: #0688f9';
			} 
			elseif($val['booking_status'] == 7){ //XUẤT VÉ
				$color = 'color: #CF822E';
			} 
			elseif($val['booking_status'] == 8){ //HOÀN TẤT
				$color = 'color: #004eff';
			}
			else {
				// MỚI TẠO
				$color = 'color: #444';
			}

			$html .= '<tr>
			<td class="text-start fw-bold text-decoration-underline"><a target="_blank" href="index.php?module=EC_Flight_Bookings&action=DetailView&record='.$key.'">'.$val['booking_number'].'</a></td>
			<td class="text-center">'.$val['journey'].'</td>
			<td class="text-center" style="'.$color.'; font-weight: bold;">'.$app_list_strings['c_booking_status_list'][$val['booking_status']].'</td>
			<td class="text-center">'.($val['booking_date'] != '' ? date('d-m-Y', strtotime($val['booking_date'])) : '').'</td>
			<td class="text-start">'.$val['customer_name'].'</td>
			<td class="text-start">'.$val['customer_email'].'</td>';

			if($val['booking_status'] == 8 || $val['booking_status'] == 7 || $val['booking_status'] == 3){
				$html .= '<td class="color-blue fw-bold text-center">'.floor($val['booking_quantity']).'</td>
						<td class="color-blue fw-bold text-end">'.format_number($val['customer_price_total']).'</td>
						<td class="color-blue fw-bold text-end">'.format_number($val['customer_price_revenue']).'</td>';
			} else {
				$html .= '<td class="text-center">'.floor($val['booking_quantity']).'</td>
						<td class="text-end">'.format_number($val['customer_price_total']).'</td>
						<td class="text-end">'.format_number($val['customer_price_revenue']).'</td>';
			}

			$html .= '<td class="text-start">'.$val['customer_ip'].'</td></tr>';	
			
			$total_price 	+= $val['customer_price_total'];
			$total_revenue += $val['customer_price_revenue'];
			$total_sove 	+= $val['booking_quantity'];
			if($val['booking_status'] == 8 || $val['booking_status'] == 7 || $val['booking_status'] == 3 ){
				$total_price_datduoc 	+= $val['customer_price_total'];
				$total_revenue_datduoc 	+= $val['customer_price_revenue'];
				$total_sove_datduoc 	+= $val['booking_quantity'];
			}
		}
		$count_line = count($arr);
	}
	
	$html .= '<tr class="footer-tr">
				<td colspan="6" class="fw-bold text-start">SỐ BOOKING: '.$count_line.'</td>
				<td class="fw-bold text-center">'.format_number($total_sove).'</td>
				<td class="fw-bold text-end">'.format_number($total_price).'</td>
				<td class="fw-bold text-end">'.format_number($total_revenue).'</td>
				<td class="fw-bold text-end">&nbsp;</td>
			</tr>';

	$html .= '<tr class="footer-tr">
				<td colspan="6" class="fw-bold text-start">TỔNG TIỀN</td>
				<td class="fw-bold text-center color-red">'.format_number($total_sove_datduoc).'</td>
				<td class="fw-bold text-end color-red">'.format_number($total_price_datduoc).'</td>
				<td class="fw-bold text-end color-red">'.format_number($total_revenue_datduoc).'</td>
				<td class="fw-bold text-end">&nbsp;</td>
			</tr>';

		$html .= '</table></div>';
		
		$this->ss->assign('LINE_ITEMS', $html);
	}

}
