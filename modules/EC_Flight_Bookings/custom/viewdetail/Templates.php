<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Modal and popup template helpers.
 */
trait TemplatesTrait
{
	private function populateCheckinNoteModal()
	{
		// Modal nhập ghi chú khi chuyển hành trình sang trạng thái "Cần checkin".
		$html = '
		<div class="modal fade" id="checkinNoteModal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Ghi chú hành trình</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<textarea id="checkinNoteText" class="form-control" rows="5" placeholder="Nhập ghi chú..."></textarea>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
						<button type="button" class="btn btn-primary" id="btnSaveCheckinNote">Lưu</button>
					</div>
				</div>
			</div>
		</div>';
		return $html;
	}

	private function populateRemindTemplate()
	{
		// Popup xác nhận nội dung nhắc lịch bay trước khi lưu trạng thái đã nhắc khách.
		$html = '
			<div id="dlgRemind" style="display:none;" title="Thông báo lịch bay">
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td style="text-align:left; vertical-align:top;">
							<textarea id="txtRemind" name="txtRemind" rows="10" style="font-size:13px"></textarea>
						</td>
					</tr>
					<tr>
						<td class="text-end">
							<div class="d-flex align-items-center gap-2 justify-content-end mt-2">
								<input type="hidden" name="current-journey-id" id="current-journey-id" value="">
								<input type="button" class="btn btn-primary" name="btn-confirm-remind" id="btn-confirm-remind" value="Đồng ý" title="Đồng ý" />
								<input type="button" class="btn btn-danger" name="btn-cancel-remind" id="btn-cancel-remind" value="Hủy" title="Hủy" />
							</div>
						</td>
					</tr>
				</table>
			</div>';
		return $html;
	}

	private function populateWinLoseTemplate()
	{
		// Popup chọn lý do thắng/thua khi hủy booking hoặc hoàn tất booking theo nghiệp vụ cũ.
		$html = '
			<div id="dlgLyDoThangThua" style="display:none;" title="Xác nhận hủy Booking">
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td style="width:15%; text-align:left; vertical-align:top; font-weight:600;" class="text-label">Lý do:</td>
						<td style="width:85%; text-align:left; vertical-align:top;">
							<table class="win-lose-radio" border="0" cellpadding="0" cellspacing="0"></table>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; vertical-align:top; font-weight:600; padding-top: 10px" class="text-label">Ghi chú:</td>
						<td style="text-align:left; vertical-align:top;">
							<textarea id="txtGhiChuThangThua" name="txtGhiChuThangThua" rows="10" style="font-size:13px"></textarea>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="text-end">
							<input type="hidden" name="which_form" id="which_form" value="" />
							<input type="button" class="btn btn-confirm mt-2" name="btnDongY" id="btnDongY" value="Đồng ý" title="Đồng ý" />
						</td>
					</tr>
				</table>
			</div>';
		return $html;
	}

	private function populatePassportInfoModal()
	{
		global $app_list_strings;

		// Popup nhập/sửa thông tin giấy tờ (CCCD/Passport) của hành khách, lưu qua entryBookingClass::updatePassengerFields.
		$passportTypeOptions = $this->buildSelectOptions($app_list_strings['passport_type_list'] ?? []);
		$nationalityOptions = $this->buildSelectOptions($app_list_strings['nationality_list'] ?? []);

		$html = '
		<div class="modal fade" id="passportInfoModal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content passport-card-modal">
					<button type="button" class="btn-close btn-close-white passport-card-close" data-bs-dismiss="modal" aria-label="Close"></button>
					<div class="passport-card">
						<input type="hidden" id="passport_passenger_id" value="" />
						<div class="passport-card__header">
							<div class="passport-card__emblem">&#9733;</div>
							<div class="passport-card__titles">
								<span class="passport-card__title">Thông tin giấy tờ hành khách</span>
							</div>
						</div>
						<div class="passport-card__body">
							<div class="passport-field passport-field--half">
								<label class="passport-field__label">Loại giấy tờ <b title="Bắt buộc">*</b></label>
								<select id="passport_type" class="passport-field__select select2-field">' . $passportTypeOptions . '</select>
							</div>
							<div class="passport-field passport-field--half">
								<label class="passport-field__label">Số giấy tờ / No. <b title="Bắt buộc">*</b></label>
								<input type="text" id="passport_number" class="passport-field__input passport-field__input--mono" minlength="5" maxlength="16" autocomplete="off" />
							</div>
							<div class="passport-field passport-field--half">
								<label class="passport-field__label">Quốc tịch / Nationality</label>
								<select id="passport_nationality" class="passport-field__select select2-field">' . $nationalityOptions . '</select>
							</div>
							<div class="passport-field passport-field--half">
								<label class="passport-field__label">Nơi cấp / Issuing country</label>
								<select id="passport_issue_country" class="passport-field__select select2-field">' . $nationalityOptions . '</select>
							</div>
							<div class="passport-field passport-field--half">
								<label class="passport-field__label">Ngày hết hạn / Expiry date <b title="Bắt buộc">*</b></label>
								<input type="text" id="passport_expired_date" class="passport-field__input passport-field__input--mono passport-date-input" placeholder="yyyy-mm-dd" maxlength="10" autocomplete="off" />
							</div>
							<div class="passport-field passport-field--half">
								<label class="passport-field__label">Ngày cấp / Issue date</label>
								<input type="text" id="passport_issue_date" class="passport-field__input passport-field__input--mono passport-date-input" placeholder="yyyy-mm-dd" maxlength="10" autocomplete="off" />
							</div>
						</div>
						<div class="passport-card__footer">
							<button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Hủy</button>
							<button type="button" class="btn btn-warning btn-sm fw-semibold" id="btnSavePassportInfo">Lưu thông tin</button>
						</div>
					</div>
				</div>
			</div>
		</div>';
		return $html;
	}

	private function buildSelectOptions($options, $emptyLabel = '-- Chọn --')
	{
		$html = '<option value="">' . $emptyLabel . '</option>';
		foreach ($options as $key => $label) {
			$html .= '<option value="' . htmlspecialchars((string) $key, ENT_QUOTES) . '">' . htmlspecialchars((string) $label, ENT_QUOTES) . '</option>';
		}
		return $html;
	}

	private function getWinLoseReasonRadio($select, $reason_type)
	{
		// Query danh sách lý do thắng/thua theo loại lý do để đổ vào popup xác nhận.
		$sql = "SELECT id, name
                FROM ec_lydothangthua
                WHERE loailydo = '$reason_type' AND deleted = 0
                ORDER BY date_entered ";

		$res = $this->bean->db->query($sql);
		$html = '';
		$i = 1;
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if ($i % 2 != 0) {
				$html .= '<tr>';
			}
			$checked = ($row['id'] == $select || $i == 1) ? 'checked' : '';
			$html .= '<td width="50%"><label for="rad-' . $row['id'] . '"><input ' . $checked . ' type="radio" name="radWinLoseReason" txt="' . $row['name'] . '" id="rad-' . $row['id'] . '" value="' . $row['id'] . '"><span class="label_WinLoseReason"> ' . $row['name'] . '</span></label></td>';
			if ($i % 2 == 0) {
				$html .= '</tr>';
			}
			$i++;
		}

		return $html;
	}
}
