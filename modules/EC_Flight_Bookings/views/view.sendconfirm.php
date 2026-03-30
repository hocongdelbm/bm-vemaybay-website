<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewsendconfirm extends SugarView {
	function display() {
		global $db;

		// Check email is valid or not
		$not_allowed = [
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
		];

		$email_domain = substr(strrchr($this->bean->email, '@'), 1);
		if (
			empty($this->bean->email)
			|| filter_var($this->bean->email, FILTER_VALIDATE_EMAIL) === false
			|| in_array($email_domain, $not_allowed)
			|| !checkdnsrr($email_domain, 'MX')
		) {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode('Email không hợp lệ. Vui lòng kiểm tra lại.'));
			exit;
		}

		$is_ok = $this->sendConfirm();
		if ($is_ok) {
			$db->query("UPDATE ec_flight_bookings SET is_mail_confirm=1 WHERE id='" . $_POST['return_id'] . "' ");
			header("Location: index.php?module=EC_Flight_Bookings&action=DetailView&record=" . $_POST['return_id']);
			exit;
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode('Email xác nhận gửi thất bại.'));
			exit;
		}
	}

	function sendConfirm() {
		global $current_user, $app_list_strings;

		// Detect department id
		$created_by = new User();
		$created_by->retrieve($this->bean->created_by);

		// $department_info = myGetDepartmentInfo("48840c01-3a4f-c430-f703-56f32c7cd8a4"); // Security travelpass
		$department_info 	= myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f"); // Security MHV
		$com_address 		= $department_info['com_address'];
		$contact_name 		= ucwords(myRemoveUnicodeChars($this->bean->contact_name));
		$booking_status 	= $app_list_strings['booking_status_list'][$this->bean->booking_status];
		$trip_type 			= $app_list_strings['bk_flight_type_list'][$this->bean->flight_type];
		$payment_type 		= $app_list_strings['booking_payment_type_list'][$this->bean->payment_type];
		$pax_infos 			= $this->bean->getPassengerInfoMailConfirm($this->bean->id, $this->bean->flight_type);
		$route_infos 		= $this->bean->getRouteInfosMailConfirm($this->bean->id);
		$time_limit			= $this->getTripType($route_infos['time_limit']);

		$form_mail 			= isset($_POST['form_mail']) && !empty($_POST['form_mail']) ? $_POST['form_mail'] : 'sendmail_confirm.html';
		$form_header 		= file_get_contents("modules/EC_Flight_Bookings/tpls/sendmail_header.html");
		$form_footer 		= file_get_contents("modules/EC_Flight_Bookings/tpls/sendmail_footer.html");
		$form_body 			= $form_header . file_get_contents("modules/EC_Flight_Bookings/tpls/$form_mail") . $form_footer;

		// EMAIL SUBJECT
		if($form_mail == 'sendmail_confirm.html')
			$subject = "Xác nhận đơn hàng {$this->bean->name} - $contact_name";
		else if ($form_mail == 'sendmail_closetime.html')
			$subject = "Đặt vé cận giờ bay {$this->bean->name} - $contact_name";
		else if ($form_mail == 'sendmail_promo.html')
			$subject = "Đặt vé khuyến mãi {$this->bean->name} - $contact_name";
		else if ($form_mail == 'sendmail_voucher.html') {
			$voucher 	= $this->getVoucherInfo($this->bean->id);
			$form_body 	= $form_header . file_get_contents("modules/EC_Flight_Bookings/tpls/$form_mail");
			$subject 	= 'Voucher Timchuyenbay gởi tặng!';
		}
		else return false;

		// Ngân lượng (Thanh toán online)
		$datepaid =  date('Y-m-d', strtotime('-7 hours', strtotime($this->bean->nganluong_datepaid)));
		$server_name = get_server_name($this->bean->created_by);
		if($server_name === 'timchuyenbay.com') $payment_link = "$server_name/thanh-toan-online?bkid=". $this->bean->id ."&datepaid=$datepaid";
		else $payment_link = "$server_name/thanh-toan-online?paymentlink=". $this->bean->nganluong_code ."&datepaid=$datepaid";

		$body = str_replace(
			[
				'{$EMAIL_SUBJECT}',
				'{$COM_LOGO}',
				'{$COM_DURATION}',
				'{$COM_NAME}',
				'{$COM_HOTLINE1}',
				'{$COM_HOTLINE2}',
				'{$COM_HOTLINE3}',
				'{$CONTACT_NAME}',
				'{$CONTACT_PHONE}',
				'{$BOOKING_NUM}',
				'{$BOOKING_STATUS}',
				'{$TRIP_TYPE}',
				'{$TOTAL_AMOUNT}',
				'{$PAYMENT_TYPE}',
				'{$TIME_LIMIT}',
				'{$PAX_INFOS}',
				'{$ROUTE_INFOS}',
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
			],
			[
				$subject,
				'https://drive.google.com/uc?export=view&id=15_0lx_uKJcNYiqYTDd6TyyQ0pRc2__OG',
				'https://drive.google.com/uc?export=view&id=1IEb4HTiMlC_FifewJ0NjF2OV-n2VwYdH',
				$department_info['notify_fromname'], // short name
				$department_info['com_hotline1'],
				$department_info['com_hotline2'],
				$department_info['com_hotline3'],
				$contact_name,
				$this->bean->phone,
				$this->bean->name,
				$booking_status,
				$trip_type,
				format_number($this->bean->total_amount) . ' VND',
				$payment_type,
				$time_limit,
				$pax_infos ?? '',
				$route_infos['html'] ?? '',
				format_number($department_info['delivery_fee']),
				str_replace("Công ty", "Cty", $department_info['com_name']), // full name str_replace Công ty ==> Cty footer
				$com_address,
				$department_info['com_phone'],
				($department_info['com_phone2'] != '' ? ' - ' . $department_info['com_phone2'] : ''),
				($department_info['com_phone3'] != '' ? ' - ' . $department_info['com_phone3'] : ''),
				$department_info['com_taxcode'] ?? '',
				$department_info['com_email'] ?? '',
				$department_info['com_email2'] ?? '',
				$department_info['com_website'] ?? '',
				(strtolower($department_info['com_website']) == 'vietjet.net' ? 'Vietjet (net)' : $department_info['com_website']),
				$department_info['com_website2'] ?? '',
				$department_info['com_website3'] ?? '',
				'', // website 4
				$department_info['color'],
				from_html($department_info['promo_link']),
				$department_info['payment_guide_link'],
				str_replace("\n", "<br />", $department_info['com_email']),
				($department_info['com_hotline1'] != '' ? ' - ' . $department_info['com_hotline1'] : ''),
				$voucher['name'],
				format_number($voucher['amt']) . ' VND',
				'https://drive.google.com/uc?export=view&id=1L-eMFTQQYbIkK6LqoVnp6q_5hR6FSH0D',
				$voucher['expire_date'] ?? '',
				$department_info['com_name'] ?? '', // header company name
				'',
				$payment_link ?? ''
			],
			$form_body
		);

		return mySendMail($current_user->id, $this->bean->email, $contact_name, $subject, $body);
	}

	public function getTripType($time_limit) {
		if (!is_null($time_limit) && !empty($time_limit)) {
			return '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
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
																	<strong>' . date('d/m/Y H:i', strtotime($time_limit) - (2 * 60 * 60)) . '</strong>
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
		
		return '';
	}

	public function getVoucherInfo($booking_id)
	{
		$sql = 'SELECT name, reduce_amount, end_time
				FROM ec_vouchers 
				WHERE booking_id = "' . $booking_id . '"
				AND deleted = 0';

		$res = $this->bean->db->query($sql);
		$row = $this->bean->db->fetchByAssoc($res);

		return array('name' => $row['name'], 'amt' => $row['reduce_amount'], 'expire_date' => date('d-m-Y', strtotime($row['end_time'])));
	}
}
