<?php

function generateSendmailHtml($booking_infos)
{

	$html = '';
	// $html .= '
	// 	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	// 	<html xmlns="http://www.w3.org/1999/xhtml">
	// 	<head>
	// 		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	// 		<title>' . $booking_infos['email_subject'] . '</title>
	// 	</head>
	// 	<body>

	// 	<div class="wrapper" style="width:100%; font-size:0.8rem; margin: 0 auto; font-family:Arial, Helvetica, sans-serif; background-color: #ebeff8; line-height:16px; color:#202020; padding: 10px 0;">
	// 		<table border="0" cellpadding="0" cellspacing="0" class="nl-container" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt; margin: 0 auto;max-width:780px">
	// 			<tbody>
	// 				<tr>
	// 					<td>
	// 						<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
	// 							<tbody>
	// 								<tr>
	// 									<td>
	// 										<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #a9e0ff; color: #000000;border-top-left-radius: 18px;border-top-right-radius: 18px;width: 100%;">
	// 											<tbody>
	// 												<tr>
	// 													<td class="column column-1" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;font-weight: 400;text-align: left; vertical-align: middle;" width="12%">
	// 														<table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;" width="100%">
	// 															<tr>
	// 																<td class="pad" style="padding: 0 10px;">
	// 																	<div align="center" class="alignment" style="line-height: 10px;padding:10px;">
	// 																		<img alt="tcb" src="https://drive.google.com/uc?export=view&id=141C4go6xNZDmuxWjuZIBCmsijktcdLmB" style="display: block;height: auto; border: 0; width: 55px; max-width: 100%;" title="tcb" />
	// 																	</div>
	// 																</td>
	// 															</tr>
	// 														</table>
	// 													</td>
	// 													<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: middle;" width="48%">
	// 														<table border="0" cellpadding="0" cellspacing="0" class="text_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
	// 															<tr>
	// 																<td class="pad" style=" padding: 15px 5px;">
	// 																	<div style="font-family: sans-serif">
	// 																		<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #3a67a2; line-height: 1.5; font-weight: 600;">
	// 																			<p style=" margin:0;font-size:0.8rem;text-align:left; mso-line-height-alt: 16.8px; ">
	// 																				<span style="display: block; font-weight:bold;">'.$booking_infos['com_website_slogan'].'</span>
	// 																				<span style="display: block; font-weight:bold;">'.$booking_infos['com_website'].'</span>
	// 																			</p>
	// 																		</div>
	// 																	</div>
	// 																</td>
	// 															</tr>
	// 														</table>
	// 													</td>
	// 													<td class="column column-2" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: middle;" width="40%">
	// 														<table border="0" cellpadding="10" cellspacing="0" class="text_block block-1" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
	// 															<tr>
	// 																<td class="pad">
	// 																	<div style="font-family: sans-serif">
	// 																		<div class="" style="padding-right: 10px; font-size: 13px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; line-height: 1.5; ">
	// 																			<p style="margin: 0; text-align: right; mso-line-height-alt: 14.399999999999999px; ">
	// 																				<span><strong>' . $booking_infos['com_name'] . '</strong></span>
	// 																			</p>
	// 																			<p style="margin: 0; text-align: right; mso-line-height-alt: 14.399999999999999px; ">
	// 																				<span><strong>Tổng đài: ' . $booking_infos['com_phone_support'] . '</strong></span>
	// 																			</p>
	// 																		</div>
	// 																	</div>
	// 																</td>
	// 															</tr>
	// 														</table>
	// 													</td>
	// 												</tr>
	// 											</tbody>
	// 										</table>
	// 									</td>
	// 								</tr>
	// 							</tbody>
	// 						</table>';
	$html .= '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>' . $booking_infos['email_subject'] . '</title>
		</head>
		<body>

		<div class="wrapper" style="width:100%; font-size:0.8rem; margin: 0 auto; font-family:Arial, Helvetica, sans-serif; background-color: #ebeff8; line-height:16px; color:#202020; padding: 10px 0;">
			<table border="0" cellpadding="0" cellspacing="0" class="nl-container" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt; margin: 0 auto;max-width:680px">
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
																		<div align="center" class="alignment" style="line-height: 10px;">
																			<img alt="tcb" src="https://drive.google.com/uc?export=view&id=1CNx1BQouU6-G3NnNwYlyTmNs7qDYqALs" style="display: block;height: auto; border: 0; width: 100%; max-width: 100%;" title="tcb" />
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
	
	// INFOR HÀNH TRÌNH (BỊ THAY ĐỔI)
	$infor_iti = ($_REQUEST['add_type'] == 3) ? $booking_infos['list_of_itineraries_changed'] : $booking_infos['list_of_itineraries'];

	// NẾU HÀNH TRÌNH BỊ THAY ĐỔI
	// HÀNH KHÁCH
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
																	<span style="font-size: 16px;font-weight: 600;">Thông tin hành khách</span>
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
							<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000; padding: 0 10px 10px; width:100%;">
								<thead>
									<tr>
										<th width="35%" align="left" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;text-align:center;">Tên Hành Khách</th>
										<th width="20%" align="left" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;text-align:center;">Mã Đặt Chỗ</th>
										<th width="45%" align="left" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;text-align:center;">Hành Lý Ký Gửi</th>
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
														<img alt="round_corners" class="big" src="https://drive.google.com/uc?export=view&id=1RRkc90dZKVr_wK-hhWQMVL0F_ZxmNfBU" style=" display: block; height: auto; border: 0;max-width: 100%; width: 100%;" title="round_corners"/>
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
							<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000; width:100%; ">
								<tbody>
								<tr>
									<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
										<table border="0" cellpadding="0" cellspacing="0" class="text_block block-1" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
											<tr>
												<td class="pad" style=" padding: 10px 10px 10px 25px;">
													<div style="font-family: sans-serif">
														<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #232323; line-height: 1.2; ">
															<p style=" margin: 0; font-size: 13px; line-height: 20px; mso-line-height-alt: 16.8px; ">
																<span style="font-size: 16px;font-weight: 600;">Thông tin hành trình</span>
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
							<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000; width: 100%; padding: 0 10px 10px;">
								<thead>
									<tr>
										<th width="20%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Ngày giờ bay</th>
										<th width="18%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Hãng bay</th>
										<th width="14%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Mã chuyến</th>
										<th width="22%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Điểm đi</th>
										<th width="22%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Điểm đến</th>
									</tr>
								</thead>
								<tbody>
									'.$infor_iti.'
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
														<img alt="round_corners" class="big" src="https://drive.google.com/uc?export=view&id=1RRkc90dZKVr_wK-hhWQMVL0F_ZxmNfBU" style=" display: block; height: auto; border: 0; max-width: 100%; width: 100%;" title="round_corners"/>
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
														<p style="line-height: 25px; margin: 0px;background: #fff;padding: 10px 15px; color: #000; border-bottom-left-radius:18px; border-bottom-right-radius:18px;">
															<span style="color: red; font-weight: 600;">Lưu ý:</span>
															
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															Quý khách cần kiểm tra thông tin kỹ càng trước khi ra sân bay. Họ tên hành khách, hành lý, ngày giờ bay, điểm đi - đến.
											
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															Vui lòng có mặt tại sân bay trước giờ khởi hành <b>' . $booking_infos['minute_before'] . ' phút</b> (Lễ, Tết trước 150 - 180 phút), tránh việc đi trễ sẽ mất vé. Xin ghi nhớ: Đến sân bay cần phải làm thủ tục tại quầy.
											
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															<b>Hành khách từ 14 tuổi trở lên phải có giấy tờ tùy thân: CCCD, bằng lái xe, hộ chiếu hoặc định danh mức độ 2 trên ứng dụng VNeID.</b> Trường hợp không có các giấy tờ trên, đi bằng giấy xác nhận nhân thân, có dấu giáp lai của cơ quan Công An phường, xã. Hành khách dưới 14 tuổi đi bằng giấy khai sinh bản chính.
											
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															Giấy tờ tuỳ thân khi ra sân bay phải là bản chính.
											
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															<i>Phải kiểm tra kỹ lưỡng: Tên hành khách, ngày giờ bay, điểm đi, điểm đến, hành lý ký gởi, số điện thoại đăng ký khi mua vé.</i> Nên rà soát tới lui nhiều lần!
															
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															Luôn mở điện thoại để nhận thông tin từ hãng hoặc nhân viên hỗ trợ. <b>Quý khách cần đảm bảo thông tin chính xác</b> so với giấy tờ tùy thân. Sử dụng số điện thoại chính và gmail để liên lạc.
															
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															<i>Vé khuyến mãi không hoàn đổi. Xin lưu ý điều này!</i>
															
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															Mọi sai sót về sau đều dẫn đến mất vé hoặc phí đổi (điều chỉnh booking).
															
															<br />
															<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
															<b>Vé khứ hồi</b> nếu quý khách không bay chặng đi <b>phải thông báo với chúng tôi trước ngày bay đầu tiên</b> để có thể sử dụng chặng về. Trong mọi trường hợp, việc thay đổi hành trình đều phải thông báo trước khi bắt đầu. Tốt nhất là sử dụng điện thoại kèm email.
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
							<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #858585; width: 100%;">
								<tbody>
									<tr>
										<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
											<table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
												<tr>
													<td class="pad" style="padding: 25px 0 15px; width: 100%; ">
														<div align="center" class="alignment" style="line-height: 10px">
															<p style=" margin: 0; font-size: 12px; line-height: 20px; mso-line-height-alt: 21px;  ">
																Địa chỉ: ' . $booking_infos['com_address'] . '
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
