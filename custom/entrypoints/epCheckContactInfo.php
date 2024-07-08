<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db, $current_user;

if(!empty($_SESSION['authenticated_user_id'])) {
	
	$ct_mobile 	= $_POST['ct_mobile'];
	$ct_name 		= $_POST['ct_name'];
	$ct_id_booking = $_POST['ct_id_booking'];

	$sql = "
		SELECT id, name, info_data
		FROM ec_customer c
		WHERE  c.phone = '" . $ct_mobile . "' AND c.deleted = 0";

	$res = $db->query($sql);

	$current_date 			= date('Y-m-d');
	$booking_lastest_date 	= getDurationDateBetweenBookingLastest($ct_mobile)['booking_date_lastest'];
	$label_type_customer 	= getCustomerType($ct_mobile);

	$html_card = '<div class="d-flex align-items-center flex-wrap gap-4 justify-content-center mb-3">';
	$html ='<div class="list-booking-customer">
			<table class="tbl-check-contact-info table-details__booking">
				<thead>
					<tr>
						<th class="hide-mobile">STT</th>
						<th>Booking</th>
						<th class="hide-mobile">Hành trình</th>
						<th class="hide-mobile">Tình trạng</th>
						<th>Ngày đặt</th>
						<th>Tên khách hàng</th>
						<th>Số vé</th>
						<th>Doanh thu</th>
						<th>Doanh số</th>
						<th class="hide-mobile">IP</th>
					</tr>
				</thead>';
	
	$total_amount_quantity = 0;
	$total_amount_price = 0;
	$total_amount_revenue = 0;

	$total_amount_quantity_datduoc = 0;
	$total_amount_datduoc = 0;
	$total_amount_revenue_datduoc = 0;

	while($row = $db->fetchByAssoc($res)){

		// CARD - html_card
		if($label_type_customer == 'VIP'){
			$html_card .= '<div class="card-custommer card-customer-vip">
						<span class="type">vip</span>
						<div class="info">
							<p class="number">'.formatPhone($ct_mobile).'</p>
							<p class="name">'.$row['name'].'</p>
							<p class="title">Khách hàng VIP</p>
						</div>
						<a href="index.php?module=EC_Customer&action=DetailView&record='.$row['id'].'" target="_blank"></a>
					</div>';
		} elseif($label_type_customer == 'LOYAL'){
			$html_card .= '<div class="card-custommer card-customer-loyal">
						<span class="type">loyal</span>
						<div class="info">
							<p class="number">'.formatPhone($ct_mobile).'</p>
							<p class="name">'.$row['name'].'</p>
							<p class="title">Khách hàng thân thiết</p>
						</div>
						<a href="index.php?module=EC_Customer&action=DetailView&record='.$row['id'].'" target="_blank"></a>
					</div>';
		} elseif($label_type_customer == 'RETURN'){
			$html_card .= '<div class="card-custommer card-customer-return">
							<span class="type">return</span>
							<div class="info">
								<p class="number">'.formatPhone($ct_mobile).'</p>
								<p class="name">'.$row['name'].'</p>
								<p class="title">Khách hàng trở lại</p>
							</div>
							<a href="index.php?module=EC_Customer&action=DetailView&record='.$row['id'].'" target="_blank"></a>
						</div>';
		} elseif($label_type_customer == 'DANGER'){
			$html_card .= '<div class="card-custommer card-customer-danger cheater">
					<div class="alert">
						<svg width="15px" height="15px" stroke-width="1.5" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg" color="#FB0"><path d="M9.172 14.828L12.001 12m2.828-2.828L12.001 12m0 0L9.172 9.172M12.001 12l2.828 2.828M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" stroke="#FB0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					<span style="margin: 0 2px">DANGER</span>
						<svg width="15px" height="15px" stroke-width="1.5" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg" color="#FB0"><path d="M9.172 14.828L12.001 12m2.828-2.828L12.001 12m0 0L9.172 9.172M12.001 12l2.828 2.828M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" stroke="#FB0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					</div>
					<div class="info">
						<p class="number">'.formatPhone($ct_mobile).'</p>
						<p class="name">'.$row['name'].'</p>
						<p class="title">Lý thông gấu</p>
					</div>
					<a href="index.php?module=EC_Customer&action=DetailView&record='.$row['id'].'" target="_blank"></a>
				</div>';
		} elseif($label_type_customer == 'WARNING'){
			$html_card .= ' <div class="card-custommer card-customer-warning cheater">
					<div class="alert">
						<svg width="15px" height="15px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#FB0"><path d="M20.043 21H3.957c-1.538 0-2.5-1.664-1.734-2.997l8.043-13.988c.77-1.337 2.699-1.337 3.468 0l8.043 13.988C22.543 19.336 21.58 21 20.043 21zM12 9v4" stroke="#FB0" stroke-width="1.5" stroke-linecap="round"></path><path d="M12 17.01l.01-.011" stroke="#FB0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					<span style="margin: 0 2px">WARNING</span>
						<svg width="15px" height="15px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#FB0"><path d="M20.043 21H3.957c-1.538 0-2.5-1.664-1.734-2.997l8.043-13.988c.77-1.337 2.699-1.337 3.468 0l8.043 13.988C22.543 19.336 21.58 21 20.043 21zM12 9v4" stroke="#FB0" stroke-width="1.5" stroke-linecap="round"></path><path d="M12 17.01l.01-.011" stroke="#FB0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					</div>
					<div class="info">
						<p class="number">'.formatPhone($ct_mobile).'</p>
						<p class="name">'.$row['name'].'</p>
						<p class="title">Lý thông me</p>
					</div>
					<a href="index.php?module=EC_Customer&action=DetailView&record='.$row['id'].'" target="_blank"></a>
				</div>';
		} elseif($label_type_customer == 'IGNORE'){
			$html_card .= '<div class="card-custommer card-customer-ignore cheater">
						<div class="alert">IGNORE</div>
					<div class="info">
						<p class="number">'.formatPhone($ct_mobile).'</p>
						<p class="name">'.$row['name'].'</p>
						<p class="title">Khách hàng linh tinh</p>
					</div>
					<a href="index.php?module=EC_Customer&action=DetailView&record='.$row['id'].'" target="_blank"></a>
				</div>';
		} else {
			// NEW
			$html_card .= '<div class="card-custommer card-customer-new">
					<span class="type">new</span>
					<div class="info">
						<p class="number">'.formatPhone($ct_mobile).'</p>
						<p class="name">'.$row['name'].'</p>
						<p class="title">Khách hàng mới</p>
					</div>
					<a href="index.php?module=EC_Customer&action=DetailView&record='.$row['id'].'" target="_blank"></a>
				</div>';
		}

		// LIST BOOKING OF CUSTOMER - html
		$data 		= json_decode(html_entity_decode($row['info_data']), true);
		$reversed_data = array_reverse($data);
		$total_count   = count($data);
		$i 	 		= 1;
		
		foreach($reversed_data as $id => $v) {

			if($v['booking_status'] == 2){ //CHỜ THANH TOÁN
				$color = 'color: #f9b113';
			}
			elseif($v['booking_status'] == 3){ //XÁC NHẬN
				$color = 'color: #004eff';
			}
			elseif($v['booking_status'] == 4){ //HỦY
				$color = 'color: #EC2029';
			}
			elseif($v['booking_status'] == 6){ //ĐÃ GỌI
				$color = 'color: #0688f9';
			} 
			elseif($v['booking_status'] == 7){ //XUẤT VÉ
				$color = 'color: #CF822E';
			} 
			elseif($v['booking_status'] == 8){ //HOÀN TẤT
				$color = 'color: #004eff';
			}
			else {
				// MỚI TẠO
				$color = 'color: #444';
			}

			$current_booking = ($id == $ct_id_booking) ? 'current_booking' : '';

			$html .= '
				<tr>
					<td align="center" class="fw-bold hide-mobile '.$current_booking.'">'.$i.'</td>
					<td class="'.$current_booking.'"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record='.$id.'" target="_blank">'.$v['booking_number'].'</a></td>
					<td class="text-center hide-mobile '.$current_booking.'">'.$v['journey'].'</td>
					<td class="hide-mobile '.$current_booking.' fw-bold text-center" style="'.$color.';" align="center">'.$app_list_strings['booking_status_list'][$v['booking_status']].'</td>
					<td class="'.$current_booking.'">'.($v['booking_date'] != '' ? date('d-m-Y H:i', strtotime('+7 hours', strtotime($v['booking_date']))) : '').'</td>
					<td class="'.$current_booking.'">'.$v['customer_name'].'</td>';

					if($v['booking_status'] == 8 || $v['booking_status'] == 7 || $v['booking_status'] == 3 ){
						$html .= '<td class="'.$current_booking.' fw-bold" style="color: #004eff;" align="center">'.floor($v['booking_quantity']).'</td>
						<td class="'.$current_booking.' fw-bold" style="color: #004eff;" align="right">'.format_number($v['customer_price_total']).'</td>
						<td class="'.$current_booking.' fw-bold" style="color: #004eff;" align="right">'.format_number($v['customer_price_revenue']).'</td>';
					} else {
						$html .= '<td class="'.$current_booking.'" align="center">'.floor($v['booking_quantity']).'</td>
						<td class="'.$current_booking.'" align="right">'.format_number($v['customer_price_total']).'</td>
						<td class="'.$current_booking.'" align="right">'.format_number($v['customer_price_revenue']).'</td>';
					}
					
					$html .= '<td class="hide-mobile '.$current_booking.'">'.$v['customer_ip'].'</td></tr>';

			if($v['booking_status'] == 8 || $v['booking_status'] == 7 || $v['booking_status'] == 3 ){
				$total_amount_quantity_datduoc += $v['booking_quantity'];
				$total_amount_datduoc += $v['customer_price_total'];
				$total_amount_revenue_datduoc += $v['customer_price_revenue'];
			}
			
			$total_amount_quantity += $v['booking_quantity'];
			$total_amount += $v['customer_price_total'];
			$total_amount_revenue += $v['customer_price_revenue'];
			$i++;
		}
	}

	// Close - html_card
	$html_card .= '<ul class="d-flex flex-column justify-content-center gap-2">';

	if(strtotime($current_date) == strtotime($booking_lastest_date)){
		$html_card .= '<li><span class="fst-italic">- Khách vừa đặt booking trong hôm nay.</span></li>';
	} else {
		$html_card .= '<li><span class="fst-italic">'.getDurationDateBetweenBookingLastest($ct_mobile)['duration_date'].'</span></li>';
	}

	$html_card .= '<li><span class="fst-italic">- Số booking khách đã chuyển tiền là:  <b class="color-red">'.getDurationDateBetweenBookingLastest($ct_mobile)['count_paymented'].'</b>.</span></li>
				<li><span class="fst-italic">- Vui lòng tư vấn khách thật ngọt ngào.</span></li>
			</ul>
		</div>';


	// Close - html
	$html .= '
		<tr class="footer-tr">
			<td colspan="3" align="center"><strong>Số dòng: '.$total_count.'</strong></td>
			<td class="hide-mobile" align="center"></td>
			<td class="hide-mobile" align="center"></td>
			<td class="hide-mobile" align="center"></td>
			<td align="center"><strong>'.format_number($total_amount_quantity).'</strong></td>
			<td align="right"><strong>'.format_number($total_amount).'</strong></td>
			<td align="right"><strong>'.format_number($total_amount_revenue).'</strong></td>
			<td class="hide-mobile" align="center"></td>
		</tr>
		<tr class="footer-tr">
			<td colspan="3" align="center"><strong>Tổng</strong></td>
			<td class="hide-mobile" align="center"></td>
			<td class="hide-mobile" align="center"></td>
			<td class="hide-mobile" align="center"></td>
			<td style="color: #ec2029; font-weight: bold;" align="center"><strong>'.format_number($total_amount_quantity_datduoc).'</strong></td>
			<td style="color: #ec2029; font-weight: bold;" align="right"><strong>'.format_number($total_amount_datduoc).'</strong></td>
			<td style="color: #ec2029; font-weight: bold;" align="right"><strong>'.format_number($total_amount_revenue_datduoc).'</strong></td>
			<td class="hide-mobile" align="center"></td>
		</tr>';

	$html .= '</table></div>';
	
	echo $html_card.$html;
}

function formatPhone($phoneNumber){
	$formattedPhoneNumber = substr($phoneNumber, 0, 3) . ' ' . substr($phoneNumber, 3, 3) . ' ' . substr($phoneNumber, 6);
	return $formattedPhoneNumber;
}