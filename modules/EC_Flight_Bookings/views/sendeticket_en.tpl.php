<?php

function generateSendmailHtml($booking_infos)
{

	$html = '';
	$html .= '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>' . $booking_infos['email_subject'] . '</title>
		</head>
		<body>

		<div class="wrapper" style="width:100%; font-size:14px; margin: 0 auto; font-family:Arial, Helvetica, sans-serif; background-color: #ebeff8; line-height:16px; color:#202020; padding: 10px 0;">
			<table border="0" cellpadding="0" cellspacing="0" class="nl-container" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt; margin: 0 auto; max-width: 680px">
				<tbody>
					<tr>
						<td>
						<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
								<tr>
									<td>
										<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000;border-top-left-radius: 18px;border-top-right-radius: 18px;width: 100%;">
											<tbody>
												<tr>
													<td class="column column-1" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;font-weight: 400;text-align: left; vertical-align: middle;">
														<table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;" width="100%">
															<tr>
																<td class="pad">
																	<div align="left" class="alignment" style="line-height: 10px">
																		<img alt="tcb" src="https://drive.google.com/uc?export=view&id=1XrGyFhPGyivmA7kka8ZlP8chpGOw8cck" style="display: block;height: auto; border: 0; width: 100%; max-width: 100%;" title="tcb" width="55" />
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
	
	// LIST HÀNH KHÁCH

	// NẾU HÀNH TRÌNH BỊ THAY ĐỔI
	if($_REQUEST['add_type'] == 3) {
		// HÀNH KHÁCH - HÀNH TRÌNH
		$html .= $booking_infos['list_of_passenger'];

	} else {
		// HÀNH KHÁCH
		$html .= '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-14" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
					<tbody>
						<tr>
							<td>
								<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000; width: 100%; ">
									<tbody>
									<tr>
										<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
											<table border="0" cellpadding="0" cellspacing="0" class="text_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
												<tr>
													<td class="pad" style=" padding: 10px 10px 10px 25px;">
														<div style="font-family: sans-serif">
															<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #232323; line-height: 1.2; ">
																<p style=" margin: 0; font-size: 13px; line-height: 20px; mso-line-height-alt: 16.8px; ">
																	<span style="font-size: 16px;font-weight: 600; text-transform: capitalize;">Passenger Information</span>
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
				<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-15" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
					<tbody>
						<tr>
							<td>
								<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000; width: 100%; padding: 0 5px 10px;">
									<thead>
										<tr>
											<th width="35%" align="left" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;text-align:center;">Full Name</th>
											<th width="20%" align="left" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;text-align:center;">Booking Code</th>
											<th width="45%" align="left" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;text-align:center;">Extra Baggage</th>
										</tr>
									</thead>
									<tbody>
										'.$booking_infos['list_of_passenger'].'
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
				<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-13" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
					<tbody>
						<tr>
							<td>
								<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 100%;">
									<tbody>
										<tr>
										<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
											<table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
												<tr>
													<td class="pad" style=" width: 100%; padding-right: 0px; padding-left: 0px; ">
														<div align="center" class="alignment" style="line-height: 10px">
															<img alt="round_corners" class="big" src="https://drive.google.com/uc?export=view&id=1RRkc90dZKVr_wK-hhWQMVL0F_ZxmNfBU" style=" display: block; height: auto; border: 0; width: 100%; max-width: 100%; " title="round_corners" />
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
	
		// HÀNH TRÌNH
		$html .= '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-14" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
					<tbody>
						<tr>
							<td>
								<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000; width: 100%;">
									<tbody>
									<tr>
										<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
											<table border="0" cellpadding="0" cellspacing="0" class="text_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
												<tr>
													<td class="pad" style=" padding: 10px 10px 10px 25px;">
														<div style="font-family: sans-serif">
															<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #232323; line-height: 1.2; ">
																<p style=" margin: 0; font-size: 13px; line-height: 20px; mso-line-height-alt: 16.8px; ">
																	<span style="font-size: 16px;font-weight: 600; text-transform: capitalize;">Route Information</span>
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
				<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-15" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
					<tbody>
						<tr>
							<td>
								<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000; width: 100%; padding: 0 5px 10px;">
									<thead>
										<tr>
											<th width="20%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Departure Time</th>
											<th width="18%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Airlines</th>
											<th width="14%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Flight Number</th>
											<th width="22%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Departure</th>
											<th width="22%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Arrival</th>
										</tr>
									</thead>
									<tbody>
										'.$booking_infos['list_of_itineraries'].'
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
				<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-13" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
					<tbody>
						<tr>
							<td>
								<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 100%; ">
									<tbody>
										<tr>
										<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
											<table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
												<tr>
													<td class="pad" style=" width: 100%; padding-right: 0px; padding-left: 0px; ">
														<div align="center" class="alignment" style="line-height: 10px">
															<img alt="round_corners" class="big" src="https://drive.google.com/uc?export=view&id=1RRkc90dZKVr_wK-hhWQMVL0F_ZxmNfBU" style=" display: block; height: auto; border: 0; width: 100%; max-width: 100%; " title="round_corners" />
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

	// LƯU Ý
	$html .= '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-36" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
				<tbody>
					<tr>
						<td>
							<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000; width: 100%; ">
								<tbody>
									<tr>
										<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
											<table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
												<tr>
													<td class="pad" style=" width: 100%; padding-right: 0px; padding-left: 0px; ">
														<p style="line-height: 25px; margin: 0px;background: #fff;padding: 10px; color: #000; border-bottom-left-radius:18px; border-bottom-right-radius:18px;">
															<span style="color: red; font-weight: 600;">Attention:</span>
															
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															You need to show your booking code and valid identity document regulations to receive boarding pass
											
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															Please be at the airport <b>'.$booking_infos['minute_before'].'</b> minutes before departure (Holidays must 150 - 180 minutes), avoiding late will lose tickets.
											
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															<b>Passengers over the age of 14 must bring one of the following identification: identity card, driving license or valid passport</b>. If you do not have any valid identification proof above, you can bring certifying personal identification form by local police or authorities citing passenger\'s permanent residence or temporary residence. Certifying forms must include the following information: certifying authority offices and their officer; date of certification; certified person\'s name, date of birth, gender, residence and hometown.
											
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															Passengers under the age of 14 must bring birth certificate.   
											
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															Please check carefully these informations: passenger name, take off time, landing time, departure, arrival, extra baggage, registered phone number.
															
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															Always keep your phone on to receive information from the airline or support booker. <b>You have to make sure all your information is corect</b>, compared to your identification. Use your registered phone number or email to contact with us.
															
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															Promotional fare cannot be refunded! Please notice this.
															
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															All mistakes will lead to losing your ticket or ticket change fee.
															
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															<b>IMPORTANT INFORMATION:</b> For <b>round trip flight tickets</b> of VIETNAM AIRLINES, PACIFIC AIRLINES OR BAMBOO AIRWAYS, if you want to cancel the first flight, you <b>need to inform the airline or travel agency before departure date</b> to keep the return flight.
														</p>
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


	// COMPANY NAME - ADDRESS
	$html .= '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-36" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
				<tbody>
					<tr>
						<td>
							<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #858585; width: 100%; ">
								<tbody>
									<tr>
										<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
											<table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
						
												<tr>
													<td class="pad" style="padding: 25px 0 15px; width: 100%;">
														<div align="center" class="alignment" style="line-height: 10px">
															<p style=" margin: 0; font-size: 12px; line-height: 20px; mso-line-height-alt: 21px;  ">
																Address: ' . $booking_infos['com_address'] . '
															</p>
															<p style=" margin: 0; font-size: 12px; line-height: 20px; mso-line-height-alt: 21px;  ">
																Tel: ' . $booking_infos['com_phone_support'] . '&nbsp;&nbsp;|&nbsp;&nbsp;Email: ' . $booking_infos['com_email'] . '
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

	
			// CLOSE WRAP
	$html .= '
			  				</td>
			  			</tr>
			  		</tbody>
				</table>
			</div>
		</body>
	</html>';

	return $html;
}
