<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewsendconfirm extends SugarView {
	function display() {
		global $db, $current_user;

		// Check email is valid or not
		$not_allowed = array(
            'gmail.vn',
            'gmail.com.vn',
            'gmall.com',
            'yahoo.con',
            'mobiphone.vn',
            'g.comail.com',
            'gsogov.vn',
            'gmail.con',
            'gmal.com',
            'gmail.vom',
            'g-mail.com',
            'gamil.com',
            'gmail.cim',
            'yhaoo.com',
            'gmal.vn',
            'gmail.co',
            'gmail.c',
            'gmai.com',
            'facebook.vn',
            'facebook.com.vn',
            'facebook.com',
            'facebook.net',
            'fb.com',
		);

		$email_domain = substr(strrchr($this->bean->email, '@'), 1);
		if(empty($this->bean->email) 
			|| filter_var($this->bean->email, FILTER_VALIDATE_EMAIL) === false 
			|| in_array($email_domain, $not_allowed)
			|| !checkdnsrr($email_domain, 'MX')
		){
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=".urlencode('Email không hợp lệ. Vui lòng kiểm tra lại.'));
			exit;
		}

		$is_ok = $this->sendConfirm();
		if($is_ok){
			$db->query("UPDATE ec_flight_bookings SET is_mail_confirm=1 WHERE id='".$_POST['return_id']."' ");
			header("Location: index.php?module=EC_Flight_Bookings&action=DetailView&record=".$_POST['return_id']);
			exit;
		}
		else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=".urlencode('Email xác nhận gửi thất bại.'));
			exit;
		}
	}

	function sendConfirm() {
		global $db, $current_user, $app_list_strings;
		$send_ok = true;

		// Detect department id
		$created_by = new User();
		$created_by->retrieve($this->bean->created_by);

		// $department_info = myGetDepartmentInfo("48840c01-3a4f-c430-f703-56f32c7cd8a4"); // Security travelpass
		$department_info 	= myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f"); // Security MHV
		$com_address 		= $department_info['com_address'];

		if(!is_null($department_info['com_address2']) && !empty($department_info['com_address2'])){
			$com_address .= ' hoặc '.$department_info['com_address2'];
		}
		if(!is_null($department_info['com_address3']) && !empty($department_info['com_address3'])){
			$com_address .= ' hoặc '.$department_info['com_address3'];
		}
		if(!is_null($department_info['com_address4']) && !empty($department_info['com_address4'])){
			$com_address .= ' hoặc '.$department_info['com_address4'];
		}

		$contact_name 		= ucwords(myRemoveUnicodeChars($this->bean->contact_name));
		$booking_status 	= $app_list_strings['booking_status_list'][$this->bean->booking_status];
		$trip_type 		= $app_list_strings['bk_flight_type_list'][$this->bean->flight_type];
		$payment_type 		= $app_list_strings['booking_payment_type_list'][$this->bean->payment_type];
		$pax_infos 		= $this->getPaxInfos($this->bean->id, $this->bean->flight_type);
		$route_infos 		= $this->getRouteInfos($this->bean->id);
		$bank_infos 		= $this->getBankInfos($created_by->department_id);	
		$time_limit		= $this->getTripType($route_infos['time_limit']);

		$form_mail 		= isset($_POST['form_mail']) && !empty($_POST['form_mail']) ? $_POST['form_mail'] : 'sendmail_confirm.html';
		$form_header 		= file_get_contents('modules/EC_Flight_Bookings/tpls/sendmail_header.html');
		$form_footer 		= file_get_contents('modules/EC_Flight_Bookings/tpls/sendmail_footer.html');
		$form_body 		= $form_header.file_get_contents('modules/EC_Flight_Bookings/tpls/'.$form_mail).$form_footer;

		// EMAIL SUBJECT
		$subject = 'Xác nhận đơn hàng '.$this->bean->name.' - '.$contact_name;
		if($form_mail == 'sendmail_closetime.html'){
			$subject = 'Đặt vé cận giờ bay '.$this->bean->name.' - '.$contact_name;
		}
		if($form_mail == 'sendmail_promo.html'){
			$subject = 'Đặt vé khuyến mãi '.$this->bean->name.' - '.$contact_name;
		}
		if($form_mail == 'sendmail_voucher.html') {
			$this->changeVoucherStatus($this->bean->id);
			$voucher 	= $this->getVoucherInfo($this->bean->id);
			$form_body 	= $form_header.file_get_contents('modules/EC_Flight_Bookings/tpls/'.$form_mail);	
			$subject 	= 'Voucher Timchuyenbay gởi tặng!';
		} 
		else $voucher = array();

		// Ngân lượng (Thanh toán online)
		$nganluong_code = $this->bean->nganluong_code;
		$nganluong_datepaid =  date('Y-m-d', strtotime('-7 hours', strtotime($this->bean->nganluong_datepaid)));

		$name_site = array(
			'TCB' => 'timchuyenbay.com',
			'VJ2' => 'vietjet.net',
		);
		$name_website 	= substr($this->bean->name, 0, 3);
		if(isset($nganluong_code) && !empty($nganluong_code)){
			$payment_link 	= $name_site[$name_website].'/thanh-toan-online?paymentlink='.$nganluong_code.'&datepaid='.$nganluong_datepaid.'';
		} else {
			$payment_link = '#';
		}

		$body = str_replace(
			array(
				'{$EMAIL_SUBJECT}',
				'{$COM_LOGO}',
				'{$COM_DURATION}',
				'{$COM_NAME}',
				'{$COM_HOTLINE1}',
				'{$COM_HOTLINE2}',
				'{$COM_HOTLINE3}',
				'{$CONTACT_NAME}',
				'{$BOOKING_NUM}',
				'{$BOOKING_STATUS}',
				'{$TRIP_TYPE}',
				'{$TOTAL_AMOUNT}',
				'{$PAYMENT_TYPE}',
				'{$TIME_LIMIT}',
				'{$PAX_INFOS}',
				'{$ROUTE_INFOS}',
				'{$BANK_OWNER}',
				'{$BANK_INFOS}',
				'{$BANK_MAIN}',
				'{$DELIVERY_FEE}',
				'{$COM_FULL_NAME}',
				'{$COM_ADDRESS}',
				'{$COM_TEL}',
				'{$COM_TEL2}',
				'{$COM_TEL3}',
				'{$COM_TAXCODE}',
				'{$COM_EMAIL1}',
				'{$COM_EMAIL2}',
				'{$COM_WEBSITE1}',
				'{$COM_WEBSITE1_TXT}',
				'{$COM_WEBSITE2}',
				'{$COM_WEBSITE3}',
				'{$COM_WEBSITE4}',
				'{$MAIN_COLOR}',
				'{$PROMO_LINK}',
				'{$PAYMENT_GUIDE_LINK}',
				'{$LOCAL_EMAIL}',
				'{$COM_SUPPORT_HOTLINE}',
				'{$VOUCHER}',
				'{$VOUCHER_AMT}',
				'{$VOUCHER_IMG}',
				'{$VOUCHER_EXPIRE_DATE}',
				'{$COM_FULL_NAME_HEADER}',
				'{$TEXT_RIGHT_TRAVELPASS}',
				'{$PAYMENT_ONLINE}',
			),
			array(
				$subject,
				// 'https://drive.google.com/uc?export=view&id=141C4go6xNZDmuxWjuZIBCmsijktcdLmB',
				'https://drive.google.com/uc?export=view&id=15_0lx_uKJcNYiqYTDd6TyyQ0pRc2__OG',
				'https://drive.google.com/uc?export=view&id=1IEb4HTiMlC_FifewJ0NjF2OV-n2VwYdH',
				$department_info['notify_fromname'], // short name
				$department_info['com_hotline1'],
				$department_info['com_hotline2'],
				$department_info['com_hotline3'],
				$contact_name,
				$this->bean->name,
				$booking_status,
				$trip_type,
				format_number($this->bean->total_amount).' VND',
				$payment_type,
				$time_limit,
				$pax_infos,
				$route_infos['html'],
				$bank_infos['bank_owner'],
				$bank_infos['html'],
				$bank_infos['bank_main'],
				format_number($department_info['delivery_fee']),
				str_replace("Công ty", "Cty", $department_info['com_name']), // full name str_replace Công ty ==> Cty footer
				$com_address,
				$department_info['com_phone'],
				($department_info['com_phone2'] != '' ? ' - '.$department_info['com_phone2'] : ''),
				($department_info['com_phone3'] != '' ? ' - '.$department_info['com_phone3'] : ''),
				$department_info['com_taxcode'],
				$department_info['com_email'],
				$department_info['com_email2'],
				$department_info['com_website'],
				(strtolower($department_info['com_website']) == 'vietjet.net' ? 'Vietjet (net)' : $department_info['com_website']),
				$department_info['com_website2'],
				$department_info['com_website3'],
				'', // website 4
				$department_info['color'],
				from_html($department_info['promo_link']),
				$department_info['payment_guide_link'],
				str_replace("\n", "<br />", $department_info['com_email']),
				($department_info['com_hotline1'] != '' ? ' - '.$department_info['com_hotline1'] : ''),
				$voucher['name'],
				format_number($voucher['amt']).' VND',
				'https://drive.google.com/uc?export=view&id=1L-eMFTQQYbIkK6LqoVnp6q_5hR6FSH0D',
				$voucher['expire_date'],
				$department_info['com_name'], // header company name
				'',
				$payment_link
			),
			$form_body
		);

		return mySendMail($current_user->id, $this->bean->email, $contact_name, $subject, $body); 
	}

	function getTripType($time_limit){

		$html = '';

		if(!is_null($time_limit) && !empty($time_limit)){
			$html .= '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
						<tbody>
							<tr>
								<td>
									<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000;width:100%;">
										<tbody>
											<tr>
											<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
												<div class="" style="display: flex;">
													<table border="0" cellpadding="0" cellspacing="0" class="text_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
														<tr>
															<td class="pad" style="padding-left: 35px;">
															<div style="font-family: sans-serif">
																<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; padding-top: 5px; padding-bottom: 5px; mso-line-height-alt: 14.399999999999999px; color: #232323; line-height: 1.5; ">
																	<p style=" margin: 0; font-size: 14px; mso-line-height-alt: 16.8px; ">
																		<span style="font-size: 14px">Thời hạn giữ chỗ</span>
																	</p>
																</div>
															</div>
															</td>
														</tr>
													</table>
													<table border="0" cellpadding="0" cellspacing="0" class="text_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
														<tr>
															<td class="pad" style="padding-right: 35px; text-align: right;">
																<div style="font-family: sans-serif">
																	<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; padding-top: 5px; padding-bottom: 5px; mso-line-height-alt: 14.399999999999999px; line-height: 1.5; ">
																		<p style=" margin: 0; font-size: 14px; text-align: left; mso-line-height-alt: 16.8px; ">
																			<strong>'.date('d/m/Y H:i', strtotime($time_limit) - (2*60*60)).'</strong>
																		</p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</div>
											</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>';
		}

		return $html;

	}

	function getPaxInfos($booking_id, $flight_type) {
		global $db, $app_list_strings, $current_user;
		$sql = "SELECT p.type AS pax_type
					,p.salutation AS pax_title
					,p.name AS pax_name
					,p.birthday AS pax_dob
					,p.date_entered
					,(
						SELECT i.airline_code
						FROM ec_booking_itineraries i
						WHERE i.deleted=0
						AND i.is_layover=0
						AND i.direction='0'
						AND i.booking_id=p.booking_id
						LIMIT 1
					) AS aircode_out
					,(
						SELECT i.ticket_class
						FROM ec_booking_itineraries i
						WHERE i.deleted=0
						AND i.is_layover=0
						AND i.direction='0'
						AND i.booking_id=p.booking_id
						LIMIT 1
					) AS ticket_class_out
					,(
						SELECT i.airline_code
						FROM ec_booking_itineraries i
						WHERE i.deleted=0
						AND i.is_layover=0
						AND i.direction='1'
						AND i.booking_id=p.booking_id
						LIMIT 1
					) AS aircode_in
					,(
						SELECT i.ticket_class
						FROM ec_booking_itineraries i
						WHERE i.deleted=0
						AND i.is_layover=0
						AND i.direction='1'
						AND i.booking_id=p.booking_id
						LIMIT 1
					) AS ticket_class_in
					,p.luggage_price AS bag_out
					,p.luggage_price_inbound AS bag_in
					,p.luggage_index_outbound
					,p.luggage_index_inbound
				FROM ec_booking_passengers p
				WHERE p.deleted=0 AND add_type IS NULL
				AND p.booking_id='".$booking_id."'
				ORDER BY pax_type, p.date_entered ";

		$res = $db->query($sql);

		$html = '<tr>
					<td style="width:12%; border:1px solid #e7e7e7; padding: 5px;">Đối tượng</td>
					<td style="width:30%; border:1px solid #e7e7e7; padding: 5px; text-align:center;">Họ tên hành khách</td>
					<td style="width:14%; border:1px solid #e7e7e7; padding: 5px; text-align:center;">Ngày sinh</td>';

		  if($flight_type == '0'){
			  $html .= '<td style="width:22%; border:1px solid #e7e7e7; padding: 5px; text-align:center;">Hành lý chiều đi</td>
						<td style="width:22%; border:1px solid #e7e7e7; padding: 5px; text-align:center;">Hành lý chiều về</td>';
		  } else {
			  $html .= '<td style="width:40%; border:1px solid #e7e7e7; padding: 5px; text-align:center;">Hành lý</td>';
		  }

		$html .= '</tr>';

		while($row = $db->fetchByAssoc($res)){

			$dob = '';
			if ($row['pax_dob'] != '' && $row['pax_dob'] != '0000-00-00') {
				try {
					$dob = new DateTime($row['pax_dob']);
					$dob = date_format($dob, 'd/m/Y');
				}catch (\Exception $ex) {
					$dob = '';
				}
			} else {
				$dob = '';
			}

			$html .= '<tr>
				<td style="border:1px solid #e7e7e7; padding: 5px; text-align: center;">'.$app_list_strings['passenger_type_list'][$row['pax_type']].'</td>
				<td style="border:1px solid #e7e7e7; padding: 5px;"><label style="text-transform:uppercase;">'.$row['pax_name'].'</label></td>
				<td style="border:1px solid #e7e7e7; padding: 5px; text-align: center;">'.$dob.'</td>';

			$bag_out = generateLuggage($row['date_entered'], $row['aircode_out'], $row['ticket_class_out'], $row['pax_type'], $row['luggage_index_outbound']);
			if (!is_null($row['luggage_index_outbound']) && !empty($row['luggage_index_outbound'])) {
				$row['bag_out'] = $row['luggage_index_outbound'];
			}

			$bag_out2 		= $bag_out[(int)$row['bag_out']];
			$bag_weight_out 	= 0;

			if (isset($bag_out2) && !empty($bag_out2)) {
				preg_match('/(\d+)kg/isU', $bag_out2, $ob_output);
				$bag_weight_out = isset($ob_output[1]) ? (int)$ob_output[1] : 0;
			}

			// Hiện luôn hành lý 0đ
			// if ($bag_weight_out >= 0) {
			if ($bag_weight_out > 0) {
				$html .= '<td style="border:1px solid #e7e7e7; padding: 5px; text-align: center;">' . substr_replace($bag_out2, '', strpos($bag_out2, '(') - 1) . '</td>';
			} else {
				$html .= '<td style="border:1px solid #e7e7e7; padding: 5px; text-align: left;"></td>';
			}

			if ($flight_type == '0') {
				// hành lý chiều về
				$bag_in = generateLuggage($row['date_entered'], $row['aircode_in'], $row['ticket_class_in'], $row['pax_type'], $row['luggage_index_inbound']);
				if (!is_null($row['luggage_index_inbound']) && !empty($row['luggage_index_inbound'])) {
					$row['bag_in'] = $row['luggage_index_inbound'];
				}
				$bag_in2 = $bag_in[(int)$row['bag_in']];
				$bag_weight_in = 0;
				if (isset($bag_in2) && !empty($bag_in2)) {
					preg_match('/(\d+)kg/isU', $bag_in2, $ib_output);
					$bag_weight_in = isset($ib_output[1]) ? (int)$ib_output[1] : 0;
				}

				// Hiện luôn hành lý 0đ
				// if ($bag_weight_in >= 0) {
				if ($bag_weight_in > 0) {
					$html .= '<td style="border:1px solid #e7e7e7; padding: 5px; text-align: center;">' . substr_replace($bag_in2, '', strpos($bag_in2, '(') - 1) . '</td>';
				} else {
					$html .= '<td style="border:1px solid #e7e7e7; padding: 5px; text-align: left;"></td>';
				}
			}

			$html .= '</tr>';
		}
		return $html;
	}

	function getRouteInfos($booking_id) {
		global $db, $app_list_strings;
		$sql = "SELECT i.direction,
					i.flight_number,
					i.departure_date,
					i.arrival_date,
					(
					CASE 
						WHEN i.airline_code='VNA' THEN 'VN'
						WHEN i.airline_code='VJA' THEN 'VJ'
						WHEN i.airline_code='JET' THEN 'BL'
						WHEN i.airline_code='VNP' THEN 'BL'
						WHEN i.airline_code='AMK' THEN 'P8'
						WHEN i.airline_code='BBA' THEN 'QH'
						ELSE i.airline_code
					END
					) AS airline_code,
					i.departure,
					i.arrival,
					i.ticket_class,
					i.time_limit
				FROM ec_booking_itineraries i
				WHERE i.booking_id='".$booking_id."' AND i.deleted=0
				ORDER BY i.direction, i.departure_date, i.date_entered";

				$res 		= $db->query($sql);
				$html 		= '';
				$time_limit 	= '';
				$airline_code 	= '';
				$i = 0;

		while($row = $db->fetchByAssoc($res)){

			if($row['airline_code'] == 'VN'){
				$row['airline_code'] = 'VNA';
			}

			$airline    = myGetAirlineInfo2(trim($row['airline_code']), 'CODE');
			$departure 	= myGetAirportInfo2(trim($row['departure']));
			$arrival 	= myGetAirportInfo2(trim($row['arrival']));

			if($row['direction'] == '0' && $i == 0){
				$time_limit 	= $row['time_limit'];
				$airline_code 	= $row['airline_code'];
			}

			$array_airline = array('VJ', 'VJA', 'QH', 'BBA', 'VU', 'VTA', 'BL', 'VNP', 'VN', 'VNA');
			if(in_array(trim($row['airline_code']), $array_airline)){
				$bg_airline = $GLOBALS['app_list_strings']['airlines_color_list'][$row['airline_code']];
			} else {
				$bg_airline = '#EA5256';
			}

			$html .= '
				<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-5" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
					<tbody>
						<tr>
							<td>
								<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #a9e0ff; color: #000000;width:100%;">
									<tbody>
										<tr>
											<td class="column column-1" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; border: 0px; " width="33.333333333333336%">
												<table border="0" cellpadding="0" cellspacing="0" class="text_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
													<tr>
														<td class="pad">
															<div style="font-family: sans-serif">
																<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
																	<p style=" margin: 0; font-size: 14px; text-align: center; mso-line-height-alt: 16.8px; ">
																		<span style="font-size: 32px;"><strong>'.$departure['data'][0]['code'].'</strong></span>
																	</p>
																</div>
															</div>
														</td>
													</tr>
												</table>
												<table border="0" cellpadding="10" cellspacing="0" class="text_block block-3" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
													<tr>
														<td class="pad" style="padding: 0;">
															<div style="font-family: sans-serif">
																<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
																	<p style=" margin: 0; text-align: center; mso-line-height-alt: 14.399999999999999px; ">
																		<span style="font-size: 16px">'.$departure['data'][0]['name'].'</span>
																	</p>
																</div>
															</div>
														</td>
													</tr>
												</table>
											</td>
											<td class="column column-2" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; border: 0px; " width="33.333333333333336%">
												<table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
													<tr>
														<td class="pad" style=" width: 100%; padding-right: 0px; padding-left: 0px; ">
															<div align="center" class="alignment" style="line-height: 10px; font-size: 18px; color: #000; padding: 12px 0; font-weight: 600;">
																'.$row['flight_number'].'
															</div>
														</td>
													</tr>
												</table>
												<table border="0" cellpadding="0" cellspacing="0" class="text_block block-4" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
													<tr>
														<td class="pad">
															<div style="font-family: sans-serif">
																<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; font-weight: 600; line-height: 1.5; ">
																	<p style=" margin: 0; font-size: 14px; text-align: center; mso-line-height-alt: 16.8px; ">
																		<span style="font-size: 15px">'.date('d/m/Y H:i', strtotime($row['departure_date'])).' &rarr; '.date('H:i', strtotime($row['arrival_date'])).'</span>
																	</p>
																</div>
															</div>
														</td>
													</tr>
												</table>
											</td>
											<td class="column column-3" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; border: 0px; " width="33.333333333333336%">
												<table border="0" cellpadding="0" cellspacing="0" class="text_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
													<tr>
														<td class="pad">
															<div style="font-family: sans-serif">
															<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
																<p style=" margin: 0; font-size: 14px; text-align: center; mso-line-height-alt: 16.8px; ">
																	<span style="font-size: 32px;"><strong>'.$arrival['data'][0]['code'].'</strong></span>
																</p>
															</div>
															</div>
														</td>
													</tr>
												</table>
												<table border="0" cellpadding="10" cellspacing="0" class="text_block block-3" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
													<tr>
														<td class="pad" style="padding: 0;">
															<div style="font-family: sans-serif">
																<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
																	<p style="margin: 0;text-align: center;mso-line-height-alt: 14.399999999999999px;">
																		<span style="font-size: 16px">'.$arrival['data'][0]['name'].'</span>
																	</p>
																</div>
															</div>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
				<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-6" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
					<tbody>
						<tr>
							<td>
								<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #a9e0ff;color: #000000;width:100%;">
									<tbody>
										<tr>
											<td class="column column-1" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;font-weight: 400;text-align: left;vertical-align: top;border: 0px;" width="100%">
												<table border="0" cellpadding="10" cellspacing="0" class="divider_block block-1" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;" width="100%">
													<tr>
														<td class="pad">
															<div align="center" class="alignment">
																<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;" width="90%">
																	<tr>
																		<td class="divider_inner" style="font-size: 1px;line-height: 1px;border-top: 1px dashed #000;">
																			<span> </span>
																		</td>
																	</tr>
																</table>
															</div>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
				<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-7" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
					<tbody>
						<tr>
							<td>
								<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #a9e0ff;color: #000000;width:100%;">
									<tbody>
										<tr>
											<td class="column column-1" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;font-weight: 400;text-align: left;border: 0px;" width="100%">
												<table border="0" cellpadding="10" cellspacing="0" class="text_block block-1" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;word-break: break-word;" width="100%">
													<tr>
														<td class="pad" style="padding-top: 0px;">
															<div style="font-family: sans-serif">
																<div class="" style="font-size: 12px;font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif;mso-line-height-alt: 14.399999999999999px;color: #000;line-height: 1.5;">
																	<p style="margin: 0;text-align: center;mso-line-height-alt: 14.399999999999999px;">
																		<span style="font-size: 13px; font-weight: 600;">Hãng: '.$airline['data'][0]['name'].'</span>
																	</p>
																</div>
															</div>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>';
			$i++;
		}

		return array('html' => $html, 'airline_code' => $airline_code, 'time_limit' => $time_limit);
	}

	function getBankInfos($department_id) {
		global $current_user;
		$json = file_get_contents('custom/banklist.json');
		$arr = json_decode($json, true);
		$bank_owner = '';

		$item = 1;
		$html = '';

		foreach($arr['data'] as $row){
			// in đậm màu đỏ cho các tk ngân hàng
			if($row['account'] == '1000123456678' // MBBank Sở Giao Dịch 2
			) {
				$row['account'] = '<p color="#ec2029"><b>'.$row['account'].'</b></p>';
			} 

			// các tk khác
			if($row['account'] != '9704229203961231605') {
				$html .= '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-21" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
								<tr>
									<td>
										<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000;width:100%;">
											<tbody>
												<tr>
													<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; ">
														<table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
															<tr>
																<td class="pad" style="width: 100%; padding-right: 0px; padding-left: 0px; ">
																	<div align="center" class="alignment"   style="line-height: 10px">
																		<img alt="credit-card" src="https://drive.google.com/uc?export=view&id=14fyuwKf4lUImHxePbixr8alo6j7oC0J6" style=" display: block; height: auto; border: 0; width: 34px; max-width: 100%; " title="credit-card" width="34">
																	</div>
																</td>
															</tr>
														</table>
													</td>
													<td class="column column-2" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top;  border: 0px;">
														<table border="0" cellpadding="10" cellspacing="0" class="text_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
															<tr>
																<td class="pad">
																	<div style="font-family: sans-serif">
																		<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; line-height: 1.2; ">
																			<p style=" margin: 0; font-size: 14px; mso-line-height-alt: 16.8px; ">
																				STK: '.$row['account'].' - '.$row['short_name'].'</b>&nbsp;'.$row['branch'].'
																			</p>
																		</div>
																	</div>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>';
			} else { // nếu là shinhan bank
				$html .= '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-21" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
							<tr>
								<td>
									<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000;width:100%;">
										<tbody>
											<tr>
												<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="25%">
													<table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
														<tr>
															<td class="pad" style="width: 100%; padding-right: 0px; padding-left: 0px; ">
																<div align="center" class="alignment"   style="line-height: 10px">
																	<img alt="credit-card" src="https://drive.google.com/uc?export=view&id=14fyuwKf4lUImHxePbixr8alo6j7oC0J6" style=" display: block; height: auto; border: 0; width: 34px; max-width: 100%; " title="credit-card" width="34">
																</div>
															</td>
														</tr>
													</table>
												</td>
												<td class="column column-2" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top;  border: 0px; " width="75%">
													<table border="0" cellpadding="10" cellspacing="0" class="text_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
														<tr>
															<td class="pad">
																<div style="font-family: sans-serif">
																	<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; line-height: 1.2; ">
																		<p style=" margin: 0; font-size: 14px; mso-line-height-alt: 16.8px; ">
																			STK MB: 0000920990898 - Công ty TNHH Minh Hồng Võ - MB Bank HCM
																		</p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							</tbody>
						</table>';
			}

			$bank_owner = $row['owner'];
			$item++;
		} // end foreach

		// thêm dòng ngân lượng
		$html .= '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-21" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
					<tbody>
						<tr>
							<td>
								<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000;width:100%;">
									<tbody>
										<tr>
											<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="25%">
												<table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
													<tr>
														<td class="pad" style="width: 100%; padding-right: 0px; padding-left: 0px; ">
															<div align="center" class="alignment"   style="line-height: 10px">
																<img alt="credit-card" src="https://drive.google.com/uc?export=view&id=14fyuwKf4lUImHxePbixr8alo6j7oC0J6" style=" display: block; height: auto; border: 0; width: 34px; max-width: 100%; " title="credit-card" width="34">
															</div>
														</td>
													</tr>
												</table>
											</td>
											<td class="column column-2" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top;  border: 0px; " width="75%">
												<table border="0" cellpadding="10" cellspacing="0" class="text_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
													<tr>
														<td class="pad">
															<div style="font-family: sans-serif">
																<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; line-height: 1.2; ">
																	<p style=" margin: 0; font-size: 14px; mso-line-height-alt: 16.8px; ">
																		Ngân lượng: vmbsale@gmail.com
																	</p>
																</div>
															</div>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>';

		// TK chính của công ty
		$bank_main = '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-21" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
						<tbody>
							<tr>
								<td>
									<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000;width:100%;">
										<tbody>
											<tr>
												<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="25%">
													<table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
														<tr>
															<td class="pad" style="width:50%;padding-right:0px;padding-left:35px">
																<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; line-height: 1.2; ">
																	<p style="margin: 0; font-size: 14px; mso-line-height-alt: 16.8px; ">
																		HD Bank - Số TK : <strong>081704070006171</strong>
																	</p>
																	<p style="margin: 0; font-size: 14px; mso-line-height-alt: 16.8px; margin-top: 5px;">
																	Chủ TK: <strong>Công ty TNHH Minh Hồng Võ</strong>
																	</p>
																</div>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>';

		return array('html' => $html, 'bank_owner' => $bank_owner, 'bank_main' => $bank_main);
	}

	function changeVoucherStatus($booking_id) {
		$sql = 'UPDATE ec_vouchers SET status = 2 
				WHERE booking_receive_id = "' . $booking_id . '"
				AND deleted = 0';
		$this->bean->db->query($sql);
	}

	function getVoucherInfo($booking_id) {
		$sql = 'SELECT name, reduce_amount, validate_to_date
				FROM ec_vouchers 
				WHERE booking_receive_id = "' . $booking_id . '"
				AND deleted = 0';
		$res = $this->bean->db->query($sql);
		$row = $this->bean->db->fetchByAssoc($res);
		return array('name' => $row['name'], 'amt' => $row['reduce_amount'], 'expire_date' => date('d-m-Y', strtotime($row['validate_to_date'])));
	}
}
