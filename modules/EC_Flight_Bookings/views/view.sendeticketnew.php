<?php
require_once("include/Sugar_Smarty.php");
require_once('modules/EC_Flight_Bookings/views/view.printeticketnew.php');

class Viewsendeticketnew extends SugarView {
	public $sugarSmarty;
	public $viewPrintETicket;
	public $lang;
	public $direction;
	public $isRoundTrip;
	public $bookingId;
	public $itineraryId; // Current itinerary ID
	public $listPassengerId; // List selected passengers
	public $notAllowed;

	public function __construct() {
        parent::__construct();

		$this->sugarSmarty = new Sugar_Smarty();
		$this->viewPrintETicket = new Viewprinteticketnew();
		$this->lang 		= isset($_REQUEST['lang']) && !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 'vn'; // Default is VN
		$this->direction	= (int)($_REQUEST['directions'] ?? 0);
		$this->isRoundTrip 	= (isset($_REQUEST['isRoundTrip']) && !empty($_REQUEST['isRoundTrip'])) ? (int)$_REQUEST['isRoundTrip'] : 0;
		$this->bookingId 	= $_REQUEST['booking_id'] ?? '';
		$this->itineraryId 	= $_REQUEST['itinerary_id'] ?? '';
		$this->listPassengerId = explode(',', (isset($_REQUEST['listPassengers']) && !empty($_REQUEST['listPassengers'])) ? $_REQUEST['listPassengers'] : []);
		$this->notAllowed = [
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
    }

	public function display() {
		global $current_user;

		$email_domain = substr(strrchr($this->bean->email, '@'), 1);
		if (
			empty($this->bean->email)
			|| filter_var($this->bean->email, FILTER_VALIDATE_EMAIL) === false
			|| in_array($email_domain, $this->notAllowed)
			|| !checkdnsrr($email_domain, 'MX')
		) {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode('Email không hợp lệ. Vui lòng kiểm tra lại.'));
			exit;
		}

		$is_ok = $this->sendEticket($this->sugarSmarty);
		if ($is_ok) {
			header("Location: index.php?module=EC_Flight_Bookings&action=DetailView&record=" . $_REQUEST['return_id']);
			exit;
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode('Email hành trình gửi thất bại.'));
			exit;
		}
	}

	public function sendEticket() {	
		global $current_user, $mod_strings;
		$listStrings = $mod_strings['LBL_SEND_TICKET'][$this->lang] ?? [];

		$send_ok = true;

		// detect department id
		$created_by = new User();
		$created_by->retrieve($this->bean->created_by);

		$contact_name = $this->bean->contact_name;
		$contact_email = $this->bean->email;
		// $department_info = myGetDepartmentInfo("48840c01-3a4f-c430-f703-56f32c7cd8a4"); // Security travelpass
		$department_info = myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f"); // Security MHV

		$subject = trim($listStrings['SUBJECT'] . ' ' . $_REQUEST['booking']);
		$subject .= ' - ' . ucwords(myRemoveUnicodeChars($this->bean->contact_name));

		$bookingInfo = [];
		$bookingInfo['email_subject'] 	= $subject;
		$bookingInfo['image_url_large'] = $department_info['company_logo'];
		$bookingInfo['booking_num'] 	= $_REQUEST['booking'];
		$bookingInfo['add_type'] 		= $_REQUEST['add_type'];
		$bookingInfo['passengers'] 		= $this->viewPrintETicket->getListPassengers();
		$bookingInfo['itineraries'] 	= $this->viewPrintETicket->getListItineraries();

		$bookingInfo['com_name'] 			= $this->lang === 'vn' ? $department_info['com_name'] : removeAccents($department_info['com_name']);
		$bookingInfo['com_taxcode'] 		= $department_info['com_taxcode'];
		$bookingInfo['com_address'] 		= $this->lang === 'vn' ? $department_info['com_address'] : $department_info['com_address2'];
		$bookingInfo['com_phone'] 			= $department_info['com_phone'] . ' - ' . $department_info['com_hotline1'] . ' - ' . $department_info['com_hotline2'];
		$bookingInfo['com_phone_support'] 	= $department_info['com_phone'];
		$bookingInfo['com_website'] 		= $department_info['com_website2'];
		$bookingInfo['com_website_slogan'] 	= $department_info['com_website'];
		$bookingInfo['com_email'] 			= $department_info['com_email'];

		$body = $this->generateSendmaiHltml($bookingInfo);
		return mySendMail($current_user->id, $contact_email, $contact_name, $subject, $body);
	}

	public function generateSendmaiHltml($bookingInfo) {
		$tbody = $this->viewPrintETicket->renderContent($bookingInfo['passengers'], $bookingInfo['itineraries']);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title>' . $bookingInfo['email_subject'] . '</title>
			</head>
			<body>
				<div class="wrapper" style="width:100%; font-size:0.8rem; margin: 0 auto; font-family:Arial, Helvetica, sans-serif; background-color: #fff; line-height:16px; color:#202020; padding: 10px 0;">
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
									</table>
									<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-15" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
										<tbody>'. $tbody .'</tbody>
									</table>
									<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-36" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
										<tbody>
											<tr>
												<td>
													<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000; width: 100%; ">
														<tbody>
															<tr>
																<td class="column column-1" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
																	'. $this->renderNoteContent() .'
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
									<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-36" role="presentation" style="mso-table-lspace:0pt; mso-table-rspace:0pt; display:table" width="100%">
										<tbody>
											<tr>
												<td>
													<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace:0pt; mso-table-rspace:0pt; color:#858585; width:100%;">
														<tbody>
															<tr>
																<td class="column column-1" style="mso-table-lspace:0pt; mso-table-rspace:0pt; font-weight:400; text-align:left; vertical-align:top; border:0px;" width="100%">
																	'. $this->renderFooterContent($bookingInfo) .'
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</body>
		</html>';

		return $html;
	}

	public function renderNoteContent() {
		if($this->lang == 'vn' || $this->lang == 'vi') {
			return '<table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
				<tr>
					<td class="pad" style=" width: 100%; padding-right: 0px; padding-left: 0px; ">
						<p style="line-height: 25px; margin: 0px;background: #fff;padding: 10px 15px; color: #000; border-bottom-left-radius:18px; border-bottom-right-radius:18px;">
							<span style="color:red; font-weight:600;">Lưu ý:</span>
							
							<br />
							<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
							Quý khách cần kiểm tra thông tin kỹ càng trước khi ra sân bay. Họ tên hành khách, hành lý, ngày giờ bay, điểm đi - đến.

							<br />
							<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
							Vui lòng có mặt tại sân bay trước giờ khởi hành <b>120 phút</b> (Lễ, Tết trước 150 - 180 phút), tránh việc đi trễ sẽ mất vé. Xin ghi nhớ: Đến sân bay cần phải làm thủ tục tại quầy.

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
			</table>';
		}
		elseif($this->lang == 'en') {
			return '<table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
				<tr>
					<td class="pad" style=" width: 100%; padding-right: 0px; padding-left: 0px; ">
						<p style="line-height: 25px; margin: 0px;background: #fff;padding: 10px; color: #000; border-bottom-left-radius:18px; border-bottom-right-radius:18px;">
							<span style="color: red; font-weight: 600;">Attention:</span>
							
							<br />
							<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
							You need to show your booking code and valid identity document regulations to receive boarding pass
			
							<br />
							<img alt="ghim" class="big" src="https://drive.google.com/uc?export=view&id=1PHI2FXGauAdQnl-SDWpf6dx3rLhCQsL3" style="height: 1.5em; width: 1.5em; vertical-align: middle; border: 0; max-width: 100%; " title="ghim" /> 
							Please be at the airport <b>180 minutes</b> before departure, avoiding late will lose tickets.
			
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
			</table>';
		}
	}

	public function renderFooterContent($bookingInfo) {
		if($this->lang == 'vn' || $this->lang == 'vi') {
			return '<table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" role="presentation" style="mso-table-lspace:0pt; mso-table-rspace:0pt;" width="100%">
				<tr>
					<td class="pad" style="padding: 25px 0 15px; width: 100%; ">
						<div align="center" class="alignment" style="line-height: 10px">
							<p style=" margin: 0; font-size: 12px; line-height: 20px; mso-line-height-alt: 21px;  ">
								Địa chỉ: '. $bookingInfo['com_address'] .'
							</p>
							<p style=" margin: 0; font-size: 12px; line-height: 20px; mso-line-height-alt: 21px;  ">
								Tel: '. $bookingInfo['com_phone_support'] . '&nbsp;&nbsp;|&nbsp;&nbsp;Email: ' . $bookingInfo['com_email'] . '
							</p>
						</div>
					</td>
				</tr>
			</table>';
		}
		elseif($this->lang == 'en') {
			return '<table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" role="presentation" style="mso-table-lspace:0pt; mso-table-rspace:0pt;" width="100%">
				<tr>
					<td class="pad" style="padding: 25px 0 15px; width: 100%;">
						<div align="center" class="alignment" style="line-height: 10px">
							<p style=" margin: 0; font-size: 12px; line-height: 20px; mso-line-height-alt: 21px;  ">
								Address: '. $bookingInfo['com_address'] .'
							</p>
							<p style=" margin: 0; font-size: 12px; line-height: 20px; mso-line-height-alt: 21px;  ">
								Tel: '. $bookingInfo['com_phone_support'] . '&nbsp;&nbsp;|&nbsp;&nbsp;Email: ' . $bookingInfo['com_email'] .'
							</p>
						</div>
					</td>
				</tr>
			</table>';
		}
	}
}
