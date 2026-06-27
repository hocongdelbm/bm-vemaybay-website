<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * SMS payment template, QR code, and payment dialog rendering.
 *
 * Used by EC_Flight_BookingsViewDetail. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait PaymentTrait
{
	public function populateSMSTemplate()
	{
		// Popup Gửi SMS: chọn loại tin hành trình/cuộc gọi/thanh toán/code vé và gửi tới số điện thoại booking.
		$source = 'Tim chuyen bay';
		$html = '
			<div id="dialog_send_sms" class="dialog-confirm" title="Gửi SMS" style="display:none; border-radius:0">
				<div class="wrap-type">
					<h3 class="subtitle" style="text-align:center">Chọn mẫu tin nhắn</h3>
					<div class="d-flex align-items-center justify-content-between" style="height:25px;">
						<div>
							<input type="radio" class="form-check-input m-0" id="send_sms_journey" name="sms_type" value="send_sms_journey">
							<label for="send_sms_journey" class="form-check-label">Tin nhắn hành trình</label>
						</div>
						<div>
							<input type="radio" class="form-check-input m-0" id="send_sms_call" name="sms_type" value="send_sms_call">
							<label for="send_sms_call" class="form-check-label">Tin nhắn cuộc gọi</label>
						</div>
						<div>
							<input type="radio" class="form-check-input m-0" id="send_sms_payment" name="sms_type" value="send_sms_payment">
							<label for="send_sms_payment" class="form-check-label">Tin nhắn thanh toán</label>
						</div>
						<div>
							<input type="radio" class="form-check-input m-0" id="send_sms_code" name="sms_type" value="send_sms_code">
							<label for="send_sms_code" class="form-check-label">Tin nhắn code vé</label>
						</div>
					</div>
				</div>
				<div class="wrap-form mt-3">
					<table cellpadding="0" cellspacing="0" border="0" class="table-config table-sendsms">
						<tr class="tr-select-payment-templates" style="display:none">
							<td class="text-label">Mẫu tin:</td>
							<td> 
								<select class="box-select w-100" id="SmsTemplateList" name="SmsTemplateList">
									<option value="">----- Chọn ngân hàng -----</option>
									' . $this->getSMSPaymentTemplate() . '
								</select>
							</td>
						</tr>
						<tr>
							<td valign="top" class="text-label">Nội dung:</td>
							<td>
								<p class="sms_content_display" id="sms_content_display"><i>Chưa có nội dung</i></p>
								<textarea class="box-textarea" rows="5" name="sms_content" id="sms_content" style="display:none"></textarea>
							</td>
						</tr>
						<tr>
							<td class="text-label">Gửi đến:</td>
							<td>
								<input type="text" name="send_sms_to" id="send_sms_to" value="' . $this->bean->phone . '" maxlength="20" style="width:120px"/>
								<button class="btn btn-primary" type="button" id="btn-confirm-send-sms"
									booking_id="' . $this->bean->id . '"
									booking_name="' . $this->bean->name . '"
									booking_source="' . $source . '"
									>Gửi SMS
								</button>
								<button class="btn btn-secondary" type="button" id="copy-sms">Copy</button>
								<i class="text-danger">Vui lòng kiểm tra kỹ càng nội dung trước khi gửi</i>
							</td>
						</tr>
					</table>
				</div>
			</div>';
		echo $html;
	}

	function getSMSPaymentTemplate()
	{
		// Lấy danh sách tài khoản ngân hàng đang theo dõi để đổ vào mẫu SMS thanh toán.
		$sql = 'SELECT ba.account_number AS account,
				ba.account_holder AS owner,
				b.short_name AS short_name,
				ba.name 
			FROM ec_bank_account ba 
				INNER JOIN ec_banks b ON b.id = ba.bank_id AND is_sms = 1
			WHERE ba.deleted = 0 AND ba.unfollow = 0
			ORDER BY IF(ba.sort IS NULL OR ba.sort = "", 100, ba.sort)';

		$banks = [];
		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$banks[] = [
				'short_name' => $row['short_name'],
				'account' => $row['account'],
				'owner' => $row['owner'],
				'name' => $row['name']
			];
		}

		$html = '';  // Ngan hang.{0,90}. So tien.{0,40} Noi dung.{0,80}
		foreach ($banks as $bank) {
			$optVal = 'Ngan hang: ' . $bank['short_name'];
			if (!empty($bank['branch'])) {
				$optVal .= ' - ' . myRemoveUnicodeChars($bank['branch']);
			}
			$optVal .= ' - So TK: ' . $bank['account'];
			$optVal .= ' - ' . ucwords(myRemoveUnicodeChars($bank['owner']));

			$total_amount = number_format($this->bean->total_amount, 0, ',', '.');
			$optVal .= '. So tien: ' . $total_amount . ' VND. ';
			// Ngày 02-02-2023 đổi nội dung từ thanh toan + tên booking -> thanh toán + SĐT
			$optVal .= 'Noi dung: thanh toan ' . $this->bean->phone;

			$html .= '<option value="' . $optVal . '">' . $bank['name'] . '</option>';
		}
		return $html;
	}


	public function generateDialogGetQRCode($amount, $phone)
	{
		// Popup lấy QR thanh toán: tạo danh sách ngân hàng, JS sẽ build URL VietQR khi user bấm "Tạo mã QR".
		$addInfo = "Thanh toan $phone"; // max 25 ký tự theo spec VietQR Quick Link
		$defaultAmount = (int)$amount;

		$sql = 'SELECT ba.account_number AS account,
				ba.account_holder AS owner,
				b.short_name AS short_name,
				ba.name
			FROM ec_bank_account ba
				INNER JOIN ec_banks b ON b.id = ba.bank_id AND is_sms = 1
			WHERE ba.deleted = 0 AND ba.unfollow = 0
			ORDER BY IF(ba.sort IS NULL OR ba.sort = "", 100, ba.sort)';

		$options = '<option value="">-- Chọn tài khoản ngân hàng --</option>';
		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$bankID     = str_replace(' ', '', $row['short_name']);
			$accountNo  = $row['account'];
			$accountName = strtoupper(myRemoveUnicodeChars($row['owner'])); // VietQR yêu cầu uppercase, không dấu

			$options .= '<option'
				. ' value="' . htmlspecialchars($bankID) . '"'
				. ' data-account="' . htmlspecialchars($accountNo) . '"'
				. ' data-name="' . htmlspecialchars($accountName) . '">'
				. htmlspecialchars($row['name'])
				. '</option>';
		}

		return '<dialog id="dialog_qr_code" class="dialog_qr_code" data-addinfo="' . htmlspecialchars($addInfo) . '">
					<h3 class="title">QR THANH TOÁN BOOKING</h3>
					<select id="select_bank_get_qr_code" class="select_bank">' . $options . '</select>
					<div class="wrap-qr-amount">
						<input type="number" id="new_payment_amount" class="box-input" placeholder="Nhập số tiền" value="' . $defaultAmount . '" min="1000" />
						<button class="btn btn-primary" id="btnRenderQRCode">Tạo mã QR</button>
					</div>
					<div id="qr_placeholder" class="qr-placeholder">
						<span>Chọn ngân hàng & nhập số tiền, sau đó bấm <b>Tạo mã QR</b></span>
					</div>
					<img id="img_qr_code" class="img_qr_code" src="" alt="QR thanh toán" />
					<div class="flex-center mt-2 gap-2">
						<button class="btn btn-secondary" onclick="closeDialog(\'dialog_qr_code\')">Đóng</button>
						<button class="btn btn-primary" id="copyQRCodeImage" disabled>Sao chép QR</button>
					</div>
				</dialog>';
	}

	/**
	 * Normalize an internal airline code to its display code, logo code, and image style.
	 * Returns ['code' => string, 'logo' => string, 'img_style' => string].
	 */
}
