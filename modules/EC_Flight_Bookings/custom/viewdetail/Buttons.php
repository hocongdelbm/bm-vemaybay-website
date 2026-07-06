<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Custom action buttons for EC_Flight_Bookings detail view.
 *
 * Used by EC_Flight_BookingsViewDetail. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait ButtonsTrait
{
	private function renderCancelledBookingButton()
	{
		global $app_list_strings;

		// Cancelled booking button - hủy
		if (!in_array((int)$this->bean->booking_status, [4, 7, 8]) && $this->editing_rights) {
			$cancelled = '</form>
			<form action="index.php" method="post" name="frmCancelled" id="frmCancelled">
			  <input type="hidden" name="module" value="EC_Flight_Bookings" />
			  <input type="hidden" name="action" value="Save" />
			  <input type="hidden" name="record" value="' . $this->bean->id . '" />
			  <input type="hidden" name="booking_status" value="4" />
			  <input type="hidden" name="lydothangthua_id" value="' . $this->bean->lydothangthua_id . '" />
			  <input type="hidden" name="ghichuthangthua" value="' . $this->bean->ghichuthangthua . '" />
			  <input type="hidden" name="optLyDoThangThua" value="' . str_replace('"', "'", myGetSelectOptionsWithDb('EC_LyDoThangThua', $this->bean->lydothangthua_id, 'id', " AND loailydo='1' ORDER BY date_entered ")) . '" />
			  <input type="button" class="btn btn-secondary" name="btnCancelled" id="btnCancelled" value="' . $app_list_strings['booking_status_list']['4'] . '" title="' . $app_list_strings['booking_status_list']['4'] . '" />
			</form>';
			$this->ss->assign('CANCELLED', $cancelled);
		}
	}

	private function assignStatusAndCallsButtons()
	{
		global $app_list_strings, $current_user;

		$status_button = $calls_button = '';
		if (in_array((int)$this->bean->booking_status, [1, 8]) && $this->editing_rights) {
			// Ở trạng thái hoàn tất - cho phép Auto LK call
			$item_link_call_auto = '';
			if ((int)$this->bean->booking_status === 8) {
				$item_link_call_auto = <<<HTML
					<li>
						<a id="btnAutoMapping" class="dropdown-item btn-voiceip-mapping" booking_id="{$this->bean->id}" booking_name="{$this->bean->name}" phone="{$this->bean->phone}">
							<svg fill="#000000" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<g id="SVGRepo_bgCarrier" stroke-width="0"></g>
								<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
								<g id="SVGRepo_iconCarrier">
									<path fill-rule="evenodd" d="M10.9745053,6.25438069 C11.5604671,6.90391332 11.3746817,7.63976469 10.8565778,8.33797195 C10.7337406,8.50350982 10.5921521,8.6666145 10.4211441,8.84634226 C10.3390625,8.93260918 10.2750591,8.99748744 10.141183,9.13149508 C9.83714115,9.43583583 9.58155513,9.69156272 9.37441088,9.89868984 C9.27396046,9.99913195 9.95978257,11.3696024 11.2907766,12.7019048 C12.6210476,14.0334833 13.9914431,14.7197765 14.0923663,14.6187976 L14.8586096,13.852132 C15.2805737,13.4297532 15.5040355,13.2259664 15.8111037,13.0245121 C16.4494656,12.6057102 17.1457524,12.4919023 17.7329975,13.0170075 C19.6503895,14.3885354 20.7354185,15.2301771 21.2669798,15.782495 C22.303783,16.8597835 22.1679037,18.5180455 21.2728679,19.4640525 C20.9625009,19.7920945 20.5689704,20.1858419 20.1041752,20.6339203 C17.2926326,23.4470127 11.3589665,21.7350681 6.81145433,17.1830859 C2.26291105,12.6300716 0.5518801,6.69583839 3.35753082,3.88868121 C3.86122573,3.37707043 4.02729858,3.211082 4.51785466,2.72771931 C5.43117982,1.82778693 7.16594962,1.68687606 8.22050841,2.7286095 C8.77521019,3.27656509 9.65955176,4.41440275 10.9745053,6.25438069 Z M16.2721965,15.266193 L15.5058008,16.0330112 C14.203091,17.336439 11.9845452,16.2253927 9.8770373,14.1158132 C7.76808363,12.0047866 6.65827534,9.78706944 7.96142436,8.48402821 C8.16828995,8.27717972 8.42363443,8.0216945 8.72744369,7.71758662 C8.8500234,7.59488642 8.90609452,7.5380489 8.97339653,7.46731514 C9.06509326,7.37094278 9.1404434,7.28630078 9.20077275,7.211402 C8.03540499,5.58806095 7.24320651,4.57370892 6.8161396,4.15183592 C6.59558525,3.93396391 6.1017247,3.97407893 5.9204189,4.1527261 C5.43686641,4.6291879 5.27792422,4.78804929 4.77626041,5.29755675 C2.9719475,7.10286418 4.35321008,11.8933879 8.22519368,15.7691775 C12.0959638,19.6437524 16.8857659,21.0256764 18.7038097,19.2068681 C19.161375,18.7655298 19.5342402,18.3924591 19.8212354,18.08912 C20.0286173,17.8699279 20.0656783,17.4176384 19.8271235,17.1697684 C19.4297888,16.7569185 18.4570205,15.9984643 16.777362,14.7922626 C16.6549304,14.8908077 16.5044234,15.033738 16.2721965,15.266193 Z M17.5857864,7 L13,7 L13,5 L17.5857864,5 L16.2928932,3.70710678 L17.7071068,2.29289322 L21.4142136,6 L17.7071068,9.70710678 L16.2928932,8.29289322 L17.5857864,7 Z"></path>
								</g>
							</svg>
							<span class="ms-1">Auto LK</span>
						</a>
					</li>
				HTML;
			}

			// Ở trạng thái mới tạo - cho phép Link BK thủ công
			$item_link_call_manual = '';
			if ((int)$this->bean->booking_status === 1) {
				$item_link_call_manual = <<<HTML
					<li>
						<a class="dropdown-item btn-voiceip-mapping" data-bs-toggle="modal" data-bs-target="#manualLinkCall">
							<svg fill="#882e2e" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<g id="SVGRepo_bgCarrier" stroke-width="0"></g>
								<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
								<g id="SVGRepo_iconCarrier">
									<path fill-rule="evenodd" d="M10.9745053,6.25438069 C11.5604671,6.90391332 11.3746817,7.63976469 10.8565778,8.33797195 C10.7337406,8.50350982 10.5921521,8.6666145 10.4211441,8.84634226 C10.3390625,8.93260918 10.2750591,8.99748744 10.141183,9.13149508 C9.83714115,9.43583583 9.58155513,9.69156272 9.37441088,9.89868984 C9.27396046,9.99913195 9.95978257,11.3696024 11.2907766,12.7019048 C12.6210476,14.0334833 13.9914431,14.7197765 14.0923663,14.6187976 L14.8586096,13.852132 C15.2805737,13.4297532 15.5040355,13.2259664 15.8111037,13.0245121 C16.4494656,12.6057102 17.1457524,12.4919023 17.7329975,13.0170075 C19.6503895,14.3885354 20.7354185,15.2301771 21.2669798,15.782495 C22.303783,16.8597835 22.1679037,18.5180455 21.2728679,19.4640525 C20.9625009,19.7920945 20.5689704,20.1858419 20.1041752,20.6339203 C17.2926326,23.4470127 11.3589665,21.7350681 6.81145433,17.1830859 C2.26291105,12.6300716 0.5518801,6.69583839 3.35753082,3.88868121 C3.86122573,3.37707043 4.02729858,3.211082 4.51785466,2.72771931 C5.43117982,1.82778693 7.16594962,1.68687606 8.22050841,2.7286095 C8.77521019,3.27656509 9.65955176,4.41440275 10.9745053,6.25438069 Z M16.2721965,15.266193 L15.5058008,16.0330112 C14.203091,17.336439 11.9845452,16.2253927 9.8770373,14.1158132 C7.76808363,12.0047866 6.65827534,9.78706944 7.96142436,8.48402821 C8.16828995,8.27717972 8.42363443,8.0216945 8.72744369,7.71758662 C8.8500234,7.59488642 8.90609452,7.5380489 8.97339653,7.46731514 C9.06509326,7.37094278 9.1404434,7.28630078 9.20077275,7.211402 C8.03540499,5.58806095 7.24320651,4.57370892 6.8161396,4.15183592 C6.59558525,3.93396391 6.1017247,3.97407893 5.9204189,4.1527261 C5.43686641,4.6291879 5.27792422,4.78804929 4.77626041,5.29755675 C2.9719475,7.10286418 4.35321008,11.8933879 8.22519368,15.7691775 C12.0959638,19.6437524 16.8857659,21.0256764 18.7038097,19.2068681 C19.161375,18.7655298 19.5342402,18.3924591 19.8212354,18.08912 C20.0286173,17.8699279 20.0656783,17.4176384 19.8271235,17.1697684 C19.4297888,16.7569185 18.4570205,15.9984643 16.777362,14.7922626 C16.6549304,14.8908077 16.5044234,15.033738 16.2721965,15.266193 Z M17,5 L17,2 L19,2 L19,5 L22,5 L22,7 L19,7 L19,10 L17,10 L17,7 L14,7 L14,5 L17,5 Z"></path>
								</g>
							</svg>
							<span class="ms-1">Liên kết</span>
						</a>
					</li>
				HTML;

				$status_button .= <<<HTML
					</form>
					<form action="index.php" method="post" name="frmPaymentPending" id="frmPaymentPending">
						<input type="hidden" name="module" value="EC_Flight_Bookings" />
						<input type="hidden" name="action" value="Save" />
						<input type="hidden" name="record" value="{$this->bean->id}" />
						<input type="hidden" name="booking_status" value="2" />
						<input type="hidden" name="assigned_user_id" value="{$current_user->id}" />
						<input type="submit" name="btnPaymentPending" class="btn btn-warning button-action" id="btnPaymentPending" value="{$app_list_strings['booking_status_list']['2']}" title="{$app_list_strings['booking_status_list']['2']}" />
					</form>
				HTML;
			}

			$calls_button .= <<<HTML
				<div class="btn-group btn-group-called">
					<button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
						Gọi
					</button>
					<ul class="dropdown-menu dropdown-menu-lg-end">
						<li>
							<a class="dropdown-item btn-voiceip-calling" id="btnCalled" booking_id="{$this->bean->id}" booking_name="{$this->bean->name}" phone="{$this->bean->phone}">
								<svg fill="#000000" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" d="M10.9745053,6.25438069 C11.5604671,6.90391332 11.3746817,7.63976469 10.8565778,8.33797195 C10.7337406,8.50350982 10.5921521,8.6666145 10.4211441,8.84634226 C10.3390625,8.93260918 10.2750591,8.99748744 10.141183,9.13149508 C9.83714115,9.43583583 9.58155513,9.69156272 9.37441088,9.89868984 C9.27396046,9.99913195 9.95978257,11.3696024 11.2907766,12.7019048 C12.6210476,14.0334833 13.9914431,14.7197765 14.0923663,14.6187976 L14.8586096,13.852132 C15.2805737,13.4297532 15.5040355,13.2259664 15.8111037,13.0245121 C16.4494656,12.6057102 17.1457524,12.4919023 17.7329975,13.0170075 C19.6503895,14.3885354 20.7354185,15.2301771 21.2669798,15.782495 C22.303783,16.8597835 22.1679037,18.5180455 21.2728679,19.4640525 C20.9625009,19.7920945 20.5689704,20.1858419 20.1041752,20.6339203 C17.2926326,23.4470127 11.3589665,21.7350681 6.81145433,17.1830859 C2.26291105,12.6300716 0.5518801,6.69583839 3.35753082,3.88868121 C3.86122573,3.37707043 4.02729858,3.211082 4.51785466,2.72771931 C5.43117982,1.82778693 7.16594962,1.68687606 8.22050841,2.7286095 C8.77521019,3.27656509 9.65955176,4.41440275 10.9745053,6.25438069 Z M16.2721965,15.266193 L15.5058008,16.0330112 C14.203091,17.336439 11.9845452,16.2253927 9.8770373,14.1158132 C7.76808363,12.0047866 6.65827534,9.78706944 7.96142436,8.48402821 C8.16828995,8.27717972 8.42363443,8.0216945 8.72744369,7.71758662 C8.8500234,7.59488642 8.90609452,7.5380489 8.97339653,7.46731514 C9.06509326,7.37094278 9.1404434,7.28630078 9.20077275,7.211402 C8.03540499,5.58806095 7.24320651,4.57370892 6.8161396,4.15183592 C6.59558525,3.93396391 6.1017247,3.97407893 5.9204189,4.1527261 C5.43686641,4.6291879 5.27792422,4.78804929 4.77626041,5.29755675 C2.9719475,7.10286418 4.35321008,11.8933879 8.22519368,15.7691775 C12.0959638,19.6437524 16.8857659,21.0256764 18.7038097,19.2068681 C19.161375,18.7655298 19.5342402,18.3924591 19.8212354,18.08912 C20.0286173,17.8699279 20.0656783,17.4176384 19.8271235,17.1697684 C19.4297888,16.7569185 18.4570205,15.9984643 16.777362,14.7922626 C16.6549304,14.8908077 16.5044234,15.033738 16.2721965,15.266193 Z M17.5857864,7 L13,7 L13,5 L17.5857864,5 L16.2928932,3.70710678 L17.7071068,2.29289322 L21.4142136,6 L17.7071068,9.70710678 L16.2928932,8.29289322 L17.5857864,7 Z"></path> </g></svg>
								<span class="ms-1">Gọi ngay</span>
							</a>
						</li>
						<li>
							<a class="dropdown-item btn-voiceip-calling btn-voiceip-calling-zalo" id="btnCalled" booking_id="{$this->bean->id}" booking_name="{$this->bean->name}" phone="{$this->bean->phone}">
								<svg fill="#000000" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" d="M10.9745053,6.25438069 C11.5604671,6.90391332 11.3746817,7.63976469 10.8565778,8.33797195 C10.7337406,8.50350982 10.5921521,8.6666145 10.4211441,8.84634226 C10.3390625,8.93260918 10.2750591,8.99748744 10.141183,9.13149508 C9.83714115,9.43583583 9.58155513,9.69156272 9.37441088,9.89868984 C9.27396046,9.99913195 9.95978257,11.3696024 11.2907766,12.7019048 C12.6210476,14.0334833 13.9914431,14.7197765 14.0923663,14.6187976 L14.8586096,13.852132 C15.2805737,13.4297532 15.5040355,13.2259664 15.8111037,13.0245121 C16.4494656,12.6057102 17.1457524,12.4919023 17.7329975,13.0170075 C19.6503895,14.3885354 20.7354185,15.2301771 21.2669798,15.782495 C22.303783,16.8597835 22.1679037,18.5180455 21.2728679,19.4640525 C20.9625009,19.7920945 20.5689704,20.1858419 20.1041752,20.6339203 C17.2926326,23.4470127 11.3589665,21.7350681 6.81145433,17.1830859 C2.26291105,12.6300716 0.5518801,6.69583839 3.35753082,3.88868121 C3.86122573,3.37707043 4.02729858,3.211082 4.51785466,2.72771931 C5.43117982,1.82778693 7.16594962,1.68687606 8.22050841,2.7286095 C8.77521019,3.27656509 9.65955176,4.41440275 10.9745053,6.25438069 Z M16.2721965,15.266193 L15.5058008,16.0330112 C14.203091,17.336439 11.9845452,16.2253927 9.8770373,14.1158132 C7.76808363,12.0047866 6.65827534,9.78706944 7.96142436,8.48402821 C8.16828995,8.27717972 8.42363443,8.0216945 8.72744369,7.71758662 C8.8500234,7.59488642 8.90609452,7.5380489 8.97339653,7.46731514 C9.06509326,7.37094278 9.1404434,7.28630078 9.20077275,7.211402 C8.03540499,5.58806095 7.24320651,4.57370892 6.8161396,4.15183592 C6.59558525,3.93396391 6.1017247,3.97407893 5.9204189,4.1527261 C5.43686641,4.6291879 5.27792422,4.78804929 4.77626041,5.29755675 C2.9719475,7.10286418 4.35321008,11.8933879 8.22519368,15.7691775 C12.0959638,19.6437524 16.8857659,21.0256764 18.7038097,19.2068681 C19.161375,18.7655298 19.5342402,18.3924591 19.8212354,18.08912 C20.0286173,17.8699279 20.0656783,17.4176384 19.8271235,17.1697684 C19.4297888,16.7569185 18.4570205,15.9984643 16.777362,14.7922626 C16.6549304,14.8908077 16.5044234,15.033738 16.2721965,15.266193 Z M17.5857864,7 L13,7 L13,5 L17.5857864,5 L16.2928932,3.70710678 L17.7071068,2.29289322 L21.4142136,6 L17.7071068,9.70710678 L16.2928932,8.29289322 L17.5857864,7 Z"></path> </g></svg>
								<span class="ms-1">Gọi Zalo</span>
							</a>
						</li>
						$item_link_call_manual
						$item_link_call_auto
					</ul>
					<div class="modal fade" id="manualLinkCall" tabindex="-1" aria-labelledby="manualLinkCallLabel" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header">
									<h6 class="modal-title" id="manualLinkCallLabel">Liên kết cuộc gọi</h6>
								</div>
								<div class="modal-body">
									<input type="text" name="call_name" class="form-control" placeholder="Nhập mã cuộc gọi" />
									<div id="suggested-calls-container" class="mt-3" style="display:none;">
										<p class="mb-1 fw-semibold text-muted small">Cuộc gọi đến gần đây:</p>
										<div id="suggested-calls-list"></div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
									<button type="button" class="btn btn-primary" id="btn-mapping-call-booking" booking_id="{$this->bean->id}" booking_name="{$this->bean->name}" phone="{$this->bean->phone}">Liên kết</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			HTML;
		}
		// Payment pending button
		else if ($this->bean->booking_status == '6' && $this->editing_rights) {
			$status_button = '</form>
			<form action="index.php" method="post" name="frmPaymentPending" id="frmPaymentPending">
			  <input type="hidden" name="module" value="EC_Flight_Bookings" />
			  <input type="hidden" name="action" value="Save" />
			  <input type="hidden" name="record" value="' . $this->bean->id . '" />
			  <input type="hidden" name="booking_status" value="2" />
			  <input type="hidden" name="assigned_user_id" value="' . $current_user->id . '" />
			  <input type="submit" name="btnPaymentPending" class="btn btn-warning button-action" id="btnPaymentPending" value="' . $app_list_strings['booking_status_list']['2'] . '" title="' . $app_list_strings['booking_status_list']['2'] . '" />
			</form>';
		}
		// Completed button
		else if ($this->bean->booking_status == '7' && $this->editing_rights) {
			// Check booking with full baggage price or not
			$complete_ok = $this->bean->db->getOne("SELECT COUNT(*)
				FROM ec_booking_passengers 
				WHERE booking_id = '{$this->bean->id}'
					AND deleted = 0
					AND (
						(luggage_purchase > 0 AND (luggage_price IS NULL OR luggage_price = 0))
						OR
						(luggage_purchase_inbound > 0 AND (luggage_price_inbound IS NULL OR luggage_price_inbound = 0))
					)
			") > 0 ? 0 : 1;

			$status_button = '</form>
				<form class="frmBookingStatus" action="index.php" method="post" name="frmCompleted" id="frmCompleted">
				<input type="hidden" name="module" value="EC_Flight_Bookings" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<input type="hidden" name="record_name" value="' . $this->bean->name . '" />
				<input type="hidden" name="booking_status" value="8" />
				<input type="hidden" name="flight_type" value="' . $this->bean->flight_type . '" />
				<input type="hidden" name="is_ticket_exported" id="is_ticket_exported" value="' . (isset($this->bean->is_ticket_exported) ? $this->bean->is_ticket_exported : 0) . '" />
				<input type="hidden" name="lydothangthua_id" value="' . $this->bean->lydothangthua_id . '" />
				<input type="hidden" name="ghichuthangthua" value="' . $this->bean->ghichuthangthua . '" />
				<input type="hidden" name="optLyDoThangThua" value="' . str_replace('"', "'", myGetSelectOptionsWithDb('EC_LyDoThangThua', $this->bean->lydothangthua_id, 'id', " AND loailydo='0' ORDER BY date_entered ")) . '" />
				<input type="hidden" name="complete_ok" value="' . $complete_ok . '" />
				<input type="submit" class="btn btn-success save-popup-dialog" name="btnCompleted" id="btnCompleted" value="' . $app_list_strings['booking_status_list']['8'] . '" title="' . $app_list_strings['booking_status_list']['8'] . '" />
			</form>';
		}
		$this->ss->assign('STATUS_BUTTON', $status_button);
		$this->ss->assign('CALLS_BUTTON', $calls_button);
	}

	private function assignSendMailConfirmButton($use_mail_confirm)
	{
		// Send mail confirm button
		if (!in_array((int)$this->bean->booking_status, [4, 7, 8]) && $use_mail_confirm) {
			$send_mail = '
			</form>
			<form action="index.php" method="post" name="frmSendMail" id="frmSendMail" class="frmSendMail">
				<input type="hidden" name="module" value="EC_Flight_Bookings" />
				<input type="hidden" name="action" value="sendconfirm" />
				<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<input type="hidden" name="email" value="' . $this->bean->email . '" />
				<input type="hidden" name="return_module" value="EC_Flight_Bookings" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="' . $this->bean->id . '" />
				<span style="display:none;" id="frmContinueSendMail">
					<select name="form_mail" class="box-select">
						<option value="sendmail_confirm.html">Mail xác nhận</option>
						<option value="sendmail_closetime.html">Mail cận giờ bay</option>
					</select>
					<input type="submit" class="btn btn-primary save-popup-dialog" value="Gửi mail" title="Gửi mail" />
					<input type="button" class="btn btn-primary" id="btnPreviewSendMail" booking_id="' . $this->bean->id . '" value="Xem trước" title="Xem trước" />
					<div id="dialog_mail_confirm_preview" style="display: none;"></div>
					<input type="button" class="btn btn-secondary" id="btnCancelSendMail" value="Hủy bỏ" title="Hủy bỏ" />
				</span>
				<button type="button" class="btn btn-email" id="btnSendMail" value="Gửi mail" >
					<svg width="21px" height="21px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#fff" style="margin-bottom: 1px;"><path d="M7 9l5 3.5L17 9" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2 17V7a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" stroke="#fff" stroke-width="1.5"></path></svg>
					<span>Gmail</span>
				</button>
			</form>';
			$this->ss->assign('SEND_MAIL', $send_mail);
		}
	}

	private function assignChangeBookingStatusButton()
	{
		global $app_list_strings, $current_user;

		// Change booking status button
		$now = date("Y-m-d H:i:s");
		$time_current = date("H:i:s", strtotime('+7 hours', strtotime($now)));

		/**
		 * Trong khung giờ 21h - 6h sáng thì được thấy nút "chuyển trạng thái booking"
		 */
		if (isManagerUser($current_user->id) && !in_array($this->bean->booking_status, [7, 8]) || is_admin($current_user)) {
			$change_status = '</form>
				<form action="index.php" method="post" name="frmChangeStatus" id="frmChangeStatus" class="d-flex align-items-center gap-2">
					<input type="hidden" name="module" value="EC_Flight_Bookings" />
					<input type="hidden" name="action" value="Save" />
					<input type="hidden" name="record" value="' . $this->bean->id . '" />
					<select class="select-box" id="booking_status" name="booking_status" >' . get_select_options_with_id($app_list_strings['booking_status_list'], (int) $this->bean->booking_status) . '</select>
					<input type="hidden" name="flight_type" value="' . $this->bean->flight_type . '" />
					<input type="hidden" name="is_ticket_exported" id="is_ticket_exported" value="' . (isset($this->bean->is_ticket_exported) ? $this->bean->is_ticket_exported : 0) . '" />
					<input type="submit" class="btn btn-warning d-none" name="btnChangeStatus" id="btnChangeStatus" value="Đổi trạng thái" title="Đổi trạng thái" />
				</form>';
			$this->ss->assign('CHANGE_STATUS', $change_status);
		}
	}

	private function assignViewedBookingButton()
	{
		global $current_user;

		// Những nhân viên đã xem booking
		if (is_admin($current_user) && $current_user->title != 'QuanLy') {
			$viewed = '</form>
			<form action="index.php" method="post" name="frmViewedBooking" id="frmViewedBooking" class="d-flex align-items-center gap-2">
				<input type="hidden" name="module" value="EC_Flight_Bookings" />
				<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<button type="button" class="btn btn-secondary" id="btnViewBooking" value="Đã xem Booking" >
					<span>Đã xem</span>
				</button>
				<div id="viewed_booking--wrap"></div>
			</form>';
			$this->ss->assign('VIEWED_BOOKING', $viewed);
		}
	}

	private function assignDocumentButtons()
	{
		$common_style = 'display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 12px; vertical-align: middle; gap: 6px;';
		//Thêm style flex để căn chỉnh
		$doc_button = '<a id="btnDocument" class="btn btn-primary btn btn-primary-2 cursor-pointer" 
        style="' . $common_style . '" 
        href="index.php?module=Documents&action=EditView&booking_id=' . $this->bean->id . '&booking_name=' . $this->bean->name . '" target="_blank">';

		// 2. Chèn SVG: 
		$doc_button .= '<svg xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 24 24"><title/><g id="Complete"><g id="upload"><g>
                <path d="M3,12.3v7a2,2,0,0,0,2,2H19a2,2,0,0,0,2-2v-7" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><g>
                <polyline data-name="Right" fill="none" id="Right-2" points="7.9 6.7 12 2.7 16.1 6.7" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                <line fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x1="12" x2="12" y1="16.3" y2="4.8"/>
                </g></g></g></g>
                </svg>';

		$doc_button .= '<span>Tài liệu</span></a>';

		// Gán vào Smarty
		$this->ss->assign('DOC_BUTTON', $doc_button);

		// --- NÚT 2: DANH SÁCH ĐÃ TẢI ---
		$doc_count = (int)$this->bean->db->getOne(
			"SELECT COUNT(id) FROM documents WHERE booking_id = '{$this->bean->id}' AND deleted = 0"
		);
		$badge_class = $doc_count > 0 ? 'doc-count-badge' : 'doc-count-badge doc-count-badge--empty';
		$doc_button_2  = '<button id="btn-uploaded-docs" class="btn btn-primary btn btn-primary-2 cursor-pointer" type="button"';
		$doc_button_2 .= ' style="' . $common_style . '" onclick="showUploadedDocuments(\'' . $this->bean->id . '\')">';
		$doc_button_2 .= '<span>D/s đã tải</span>';
		$doc_button_2 .= '<span class="' . $badge_class . '" id="doc-count-badge">' . $doc_count . '</span>';
		$doc_button_2 .= '</button>';
		$this->ss->assign('DOC_LIST_BUTTON', $doc_button_2);
	}

	private function assignCreateReceiptVoucherButton()
	{
		// Create receipt voucher button
		$this->_is_had_rv = myCheckValueExist('EC_Receipt_Voucher', array('booking_id'), array($this->bean->id), '');
		if (
			$this->bean->booking_status == 2
			&& !$this->_is_had_rv
			&& ACLController::checkAccess('EC_Receipt_Voucher', 'edit', true)
			||
			$this->bean->is_agent == 1
			&&
			!$this->_is_had_rv  /* && $this->bean->is_agent != 1 */
		) {
			$receipt_type = ($this->bean->payment_type == 3 || $this->bean->payment_type == 4) ? 'credit_transfer' : 'cash';
			$create_rv = '</form>
			<form action="index.php" method="post" name="frmCreateRV" id="frmCreateRV">
			  <input type="hidden" name="module" value="EC_Receipt_Voucher" />
			  <input type="hidden" name="action" value="EditView" />
			  <input type="hidden" name="amount" value="' . format_number($this->bean->total_amount) . '" />
			  <input type="hidden" name="amount_converted" value="' . format_number($this->bean->total_amount) . '" />
			  <input type="hidden" name="receipt_type" value="' . $receipt_type . '" />
			  <input type="hidden" name="guest_name" value="' . trim(stripslashes($this->bean->contact_name)) . '" />
			  <input type="hidden" name="guest_phone" value="' . trim(stripslashes($this->bean->phone)) . '" />
			  <input type="hidden" name="guest_address" value="' . trim(stripslashes($this->bean->address)) . '" />
			  <input type="hidden" name="description" value="Thu tiền booking: ' . $this->bean->name . '" />
			  <input type="hidden" name="loai_thu" value="1" />
			  <input type="hidden" name="from_ec_flight_bookings" value="1" />
			  <input type="hidden" name="booking_name" value="' . $this->bean->name . '" />
			  <input type="hidden" name="booking_id" value="' . $this->bean->id . '" />
			  <input type="submit" name="btnCreateRV" id="btnCreateRV" class="btn btn-warning save-popup-dialog" value="Tạo phiếu thu" title="Tạo phiếu thu" />									
			</form>';
			$this->ss->assign('CREATE_RV', $create_rv);
		}
	}

	private function assignMarkAsReferenceButton()
	{
		// Mark as preference button
		$mask_as_refer = '';
		if (!$this->bean->is_reference) {
			$mask_as_refer = '</form>
								<form action="index.php" method="post" name="frmMarkAdPreferance" id="frmMarkAdPreferance">
									<input type="hidden" name="module" value="' . $this->bean->object_name . '" />
									<input type="hidden" name="action" value="Save" />
									<input type="hidden" name="record" value="' . $this->bean->id . '" />
										<input type="hidden" name="record_name" value="' . $this->bean->name . '" />
									<input type="hidden" name="is_reference" value="1" />
									<input type="submit" name="btnMarkAsPreference" id="btnMarkAsPreference" class="btn btn-primary" value="BK tham khảo" title="BK tham khảo" onclick="return confirm(\'Bạn có chắc chắn muốn đánh dấu đây là BK tham khảo?\');"/>									
								</form>';
		}
		$this->ss->assign('MARK_AS_REFERENCE', $mask_as_refer);
	}

	private function assignTicketReturnButton()
	{
		// Ticket return button
		if (
			($this->bean->booking_status == '7' || $this->bean->booking_status == '8')
			&& $this->editing_rights
			&& !myCheckValueExist('EC_HoanVe', array('booking_id'), array($this->bean->id), '')
		) {
			$ticket_return = '</form>
			<form action="index.php" method="post" name="frmTicketReturn" id="frmTicketReturn">
			  <input type="hidden" name="module" value="EC_HoanVe" />
			  <input type="hidden" name="action" value="EditView" />
			  <input type="hidden" name="booking" value="' . $this->bean->name . '" />
			  <input type="hidden" name="booking_id" value="' . $this->bean->id . '" />
			  <input type="submit" class="btn btn-danger" name="btnTicketReturn" id="btnTicketReturn" value="Hoàn vé" title="Hoàn vé" />
			</form>';
			$this->ss->assign('TICKET_RETURN', $ticket_return);
		}
	}

	private function assignPostTicketEditButtons()
	{
		// Create invoice button
		if (in_array((int)$this->bean->booking_status, [7, 8])) {
			$create_inv = '
			<input type="button" id="btnCreateInvoice" class="btn btn-warning" value="Sửa YC xuất HĐ" title="Sửa YC xuất HĐ" />
			</form>
			<form id="edit_invoice_frm" method="post" style="display:none; background-color:#fff;" name="edit_invoice_frm">
				<input type="hidden" name="module" value="EC_Flight_Bookings">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="booking_id" value="' . $this->bean->id . '">
				<div class="detail view" id="invoice_inf"></div>
				<div class="text-center">
					<input class="btn btn-primary save-popup-dialog" type="submit" value="Lưu" name="save_request_invoice">
				</div>
			</form>';
			$this->ss->assign('CREATE_INVOICE', $create_inv);

			// Edit booking detail
			$bkg_detail = '<input type="button" class="btn btn-warning" id="edit_bkg_btn" value="Sửa chi tiết">
							</form><form id="bkg_detail" method="post" style="display:none; background-color:#fff;">
								<input type="hidden" name="module" value="EC_Flight_Bookings">
								<input type="hidden" name="action" value="Save">
								<input type="hidden" id="bkg_no" name="record" value="' . $this->bean->id . '">
								<input type="hidden" name="edit_detail">
								<div class="detail view in-popup">
									<h4 class="dialog-title">Chi tiết vé</h4>
									<table id="tbl_line_details" class="table_config table-edit-details__booking table-details__booking" cellpadding="0" cellspacing="0" border="0"></table>
								</div>
								<input class="btn btn-primary mt-2 d-block mx-auto save-popup-dialog" type="submit" value="Lưu">
							</form>';
			$this->ss->assign('EDIT_BKG_DETAIL', $bkg_detail);

			// Change name - Đổi tên hành khách
			$change_name = <<<HTML
				<input id="change_name_btn" type="button" value="Hành khách / Hành lý / Code vé">
				</form>
				<form id="change_name" method="post" style="display:none;background-color:#fff;">
					<input type="hidden" name="record" id="bkg_no_name" value="{$this->bean->id}" />
					<input type="hidden" name="module" value="EC_Flight_Bookings" />
					<input type="hidden" name="action" value="Save" />
					<div id="line_passengers_name_area" class="detail view"></div>
					<input type="submit" value="Lưu" class="btn btn-primary mt-2" />
				</form>
			HTML;
			$this->ss->assign('CHANGE_PASSENGER_NAME', $change_name);

			// Đổi thông tin ngày bay / hành trình / tên hành khách / hành lý
			$change_flight_time = <<<HTML
				<input id="change_flight_time" class="btn btn-warning" type="button" value="Đổi thông tin">
				</form>
				<form id="tbl_change_flight_time" name="tbl_change_flight_time" method="post" class="tbl-change-flight-time">
					<input type="hidden" name="module" value="EC_Flight_Bookings" />
					<input type="hidden" name="action" value="Save" />
					<input type="hidden" name="booking_id" value="{$this->bean->id}" />
					<div id="line_itineraries_area" class="detail view in-popup"></div>
					<input type="submit" name="save_change_flight" value="Lưu" class="btn btn-primary mt-3 d-block mx-auto" />
				</form>
			HTML;
			$this->ss->assign('CHANGE_FLIGHT_TIME', $change_flight_time);
		}
	}

	private function assignAutoBookButton()
	{
		global $current_user;

		// Auto book
		if (!$this->isTelesaleRole($current_user->id) && in_array($this->bean->booking_status, [1, 2, 3, 6])) {
			// if (in_array($this->bean->booking_status, [1, 2, 3, 6]) && !$this->bean->is_hold && !$this->bean->holding_status) {
			$agencyOptions = '
				<li>
					<a type="button" id="auto-book-datacom" class="dropdown-item btn-auto-book" data-entry-class="entryAutoBookDatacomClass">
						<span class="ms-1">Hồng Ngọc Hà</span>
					</a>
				</li>
				<li>
					<a type="button" id="auto-book-phuongnam" class="dropdown-item btn-auto-book" data-entry-class="entryAutoBookPhuongNamClass">
						<span class="ms-1">Phương Nam</span>
					</a>
				</li>
			';
			if ($this->bean->ticket_type == '2') {
				$agencyOptions = '<li>
					<a type="button" id="auto-book-datacom" class="dropdown-item btn-auto-book" data-entry-class="entryAutoBookDatacomClass">
						<span class="ms-1">Hồng Ngọc Hà</span>
					</a>
				</li>';
			}

			$this->ss->assign(
				'BUTTON_AUTO_BOOK',
				'<div class="btn-group btn-group-autobook">
					<button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
						Auto book
					</button>
					<ul class="dropdown-menu dropdown-menu-lg-end">' . $agencyOptions . '</ul>
				</div>'
			);
		} else {
			$this->ss->assign('BUTTON_AUTO_BOOK', '');
		}
	}

	private function assignPrintAndSendTicketButton()
	{
		// PRINT_TICKET_NEW - Nút in vé mới với popup chọn
		$resolvedItineraries = $this->getResolvedItinerariesForPopup();
		$itinerariesJson = htmlspecialchars(json_encode($resolvedItineraries), ENT_QUOTES, 'UTF-8');

		// Per-passenger itinerary changes detection
		$hasPerPaxChanges = $this->hasPerPassengerItineraryChanges();
		$perPaxDataAttr = '';
		if ($hasPerPaxChanges) {
			$perPaxItineraries = $this->getPerPassengerItinerariesForPopup();
			$perPaxDataAttr = ' data-per-pax-itineraries="' . htmlspecialchars(json_encode($perPaxItineraries), ENT_QUOTES, 'UTF-8') . '"';
		}
		$this->ss->assign(
			'PRINT_TICKET_NEW',
			'<div class="btn-group btnPrintEticketNew-selection">
				<button type="button" class="btn btn-primary btnPrintEticketNew" data-bs-display="static" aria-expanded="false">In & Gửi vé</button>
			</div>
			<div id="dlgPrintTicketNew" style="display:none;" data-itineraries="' . $itinerariesJson . '" data-booking-id="' . $this->bean->id . '" data-booking="' . $this->bean->name . '" data-ticket-type="' . $this->bean->ticket_type . '" data-has-per-pax-changes="' . ($hasPerPaxChanges ? '1' : '0') . '"' . $perPaxDataAttr . '>
				<div class="popup-itinerary-section" style="margin-bottom:14px;">
					<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
						<strong style="font-size:14px;">
							<svg width="16px" height="16px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000" style="vertical-align:sub;margin-right:2px;">
								<path d="M20 10C20 14.4183 12 22 12 22C12 22 4 14.4183 4 10C4 5.58172 7.58172 2 12 2C16.4183 2 20 5.58172 20 10Z" stroke="#000000" stroke-width="1.5"></path><path d="M12 11C12.5523 11 13 10.5523 13 10C13 9.44772 12.5523 9 12 9C11.4477 9 11 9.44772 11 10C11 10.5523 11.4477 11 12 11Z" fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
							</svg>
							Hành trình
						</strong>
						<label style="font-size:12px; cursor:pointer;"><input type="checkbox" id="popup-select-all-iti" checked /> Chọn tất cả</label>
					</div>
					<div class="popup-itinerary-list" style="max-height:160px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:6px; padding:6px 10px; background:#fafafa;"></div>
				</div>
				<div class="popup-passenger-section" style="margin-bottom:14px;">
					<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
						<strong style="font-size:14px;">
							<svg width="17px" height="17px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000" style="vertical-align:sub;margin-right:2px;">
								<path d="M1 20V19C1 15.134 4.13401 12 8 12V12C11.866 12 15 15.134 15 19V20" stroke="#000000" stroke-width="1.5" stroke-linecap="round"></path>
								<path d="M13 14V14C13 11.2386 15.2386 9 18 9V9C20.7614 9 23 11.2386 23 14V14.5" stroke="#000000" stroke-width="1.5" stroke-linecap="round"></path>
								<path d="M8 12C10.2091 12 12 10.2091 12 8C12 5.79086 10.2091 4 8 4C5.79086 4 4 5.79086 4 8C4 10.2091 5.79086 12 8 12Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 9C19.6569 9 21 7.65685 21 6C21 4.34315 19.6569 3 18 3C16.3431 3 15 4.34315 15 6C15 7.65685 16.3431 9 18 9Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
							</svg> 
							Hành khách
						</strong>
						<label style="font-size:12px; cursor:pointer;"><input type="checkbox" id="popup-select-all-psg" checked /> Chọn tất cả</label>
					</div>
					<div class="popup-passenger-list" style="max-height:260px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:6px; padding:6px 10px; background:#fafafa;"></div>
				</div>
				<div class="popup-perpax-section" style="display:none; margin-bottom:14px;">
					<strong style="font-size:14px; display:block; margin-bottom:8px;">👤 Hành khách & Hành trình</strong>
					<div class="popup-perpax-list" style="max-height:480px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:6px; padding:8px 10px; background:#fafafa;"></div>
				</div>
				<div style="display:flex; align-items:center; justify-content:center; gap:16px; margin-bottom:10px;">
					<label style="cursor:pointer;"><input type="radio" name="popup_ngonngu" value="vn" checked /> Tiếng Việt</label>
					<label style="cursor:pointer;"><input type="radio" name="popup_ngonngu" value="en" /> English</label>
				</div>
				<div style="text-align:center;">
					<button type="button" class="btn btn-primary" id="btnSubmitPrintNew" style="padding:8px 28px; font-size:14px;">In vé</button>
					<button type="button" class="btn btn-primary" id="btnSubmitSendNew" style="padding:8px 28px; font-size:14px;">Gửi vé</button>
				</div>
			</div>'
		);
	}

	private function assignUpdateRevenueButton()
	{
		global $current_user;

		// Cập nhật doanh số của booking trong table ec_revenue
		$update_revenue = '';
		if (is_admin($current_user) && $current_user->user_name == 'hungnh') {
			$update_revenue = '<input id="update_revenue" class="btn btn-primary" type="button" value="Cập nhật DS">';
		}

		$this->ss->assign('UPDATE_REVENUE', $update_revenue);
	}
}
