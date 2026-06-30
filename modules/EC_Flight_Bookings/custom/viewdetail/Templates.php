<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Modal and popup template helpers.
 *
 * Used by EC_Flight_BookingsViewDetail. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait TemplatesTrait
{
	private function createModal()
	{
		// Modal confirm dùng chung cho các action cần xác nhận nhanh trên detail view.
		echo '<div class="modal fade" id="modal-confirm">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title"></h4>
					</div>
					<div class="modal-footer border-0">
						<button type="button" class="btn btn-confirm" id="confirm-modal" type="" data="" data-bs-dismiss="modal">Xác nhận</button>
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
					</div>
				</div>
			</div>
		</div>';
		return;
	}

	function populateCheckinNoteModal()
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

	function populateRemindTemplate()
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


	function populateWinLoseTemplate()
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


	function populatePrintLanguage()
	{
		// Popup chọn ngôn ngữ và hành khách trước khi in/gửi vé theo luồng legacy.
		$html = '<div id="dlgSelectLanguage" style="display:none;" title="Ngôn ngữ">
			<div class="d-flex flex-column align-items-center gap-3">
				<div class="option-group d-flex gap-4">
					<div class="form-group">
						<label for="vn" class="form-check-label">Tiếng Việt</label>
						<input class="form-check-input" type="radio" name="ngonngu" id="vn" value="vn" style="vertical-align:middle; margin-top: 0;" checked /> 
					</div>
					<div class="form-group">
						<label for="en" class="form-check-label">Tiếng Anh</label>
						<input class="form-check-input" type="radio" name="ngonngu" id="en" value="en" style="vertical-align:middle; margin-top: 0;" /> 
					</div>
					
				</div>
				<div class="option-passenger"></div>
				<div class="form-group">
					<input type="hidden" id="what_form" value="" />
					<input type="button" class="btn btn-primary" id="btnSelectLanguage" value="Tiếp tục" title="Tiếp tục" />
				</div>
			</div>
		</div>';
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

	/**
	 * Get voucher applied
	 * 
	 * @param string $booking_id
	 * @return array
	 */

	/**
	 * Get journeys by booking id (Using for ZBS)
	 * 
	 * @param string $bookingId
	 * @return array
	 */
}
