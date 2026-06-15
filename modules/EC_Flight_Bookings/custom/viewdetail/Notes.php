<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Detail notes and working-process note rendering.
 *
 * Used by EC_Flight_BookingsViewDetail. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait NotesTrait
{
	private function getWorkingProcessNoteActions()
	{
		$actions_kpi = [];

		// Lấy thông tin đã thanh toán từ kpi
		$sql_kpi = '
			SELECT id, description, paid, called, recheck, recall, check_debt, support, remind
			FROM ec_working_process 
			WHERE parent_id = "' . $this->bean->id . '" 
				AND deleted = 0 
				AND (paid = 1 OR called = 1 OR recheck > 0 OR recall > 0 OR remind > 0 OR check_debt > 0 OR support > 0)';

		$res_kpi = $this->bean->db->query($sql_kpi);
		while ($row_kpi = $this->bean->db->fetchByAssoc($res_kpi)) {
			$action_type = null;
			foreach (['called', 'paid', 'recheck', 'recall', 'remind', 'check_debt', 'support'] as $field) {
				if (!empty($row_kpi[$field])) {
					$action_type = $field;
					break;
				}
			}
			if ($action_type !== null) {
				$actions_kpi[$row_kpi['id']] = ['type' => $action_type, 'description' => $row_kpi['description']];
			}
		}

		return $actions_kpi;
	}

	private function renderNoteMessageRows($actions_kpi)
	{
		global $current_user;

		$sql = "SELECT 
				n.id AS detail_id,
				n.description,
				n.date_entered,
				n.working_process_id,
				u.user_name,
				u.id AS user_id
			FROM notes n 
				LEFT JOIN users u ON n.created_by = u.id AND u.deleted = 0
			WHERE n.parent_id = '{$this->bean->id}'
				AND n.parent_type = 'EC_Flight_Bookings' 
				AND n.deleted = 0
			ORDER BY n.date_entered";

		$res = $this->bean->db->query($sql);
		$user_list = get_user_array(true, 'Active', '', true);
		$note_username = "";
		$row_content = "";

		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$note_username = $user_list[$row['user_id']];
			$id_wprocess = $row['working_process_id'];
			$class_of_row = "row-mess";
			if ($row['user_id'] == $current_user->id)
				$class_of_row .= " row-this";

			$row_action = $icon_action = $data_more = '';
			$typemap = [
				'called' => 'Ghi chú "Đã gọi"',
				'paid' => 'Ghi chú "Đã thanh toán"',
				'recheck' => 'Recheck',
				'recall' => 'Recall',
				'remind' => 'Remind',
				'check_debt' => 'Công nợ',
				'support' => 'Hỗ trợ KH',
			];
			if (in_array($id_wprocess, array_keys($actions_kpi))) {
				$t = $actions_kpi[$id_wprocess]['type'];
				$class_of_row .= " row-" . $t;

				if (!empty($typemap[$t]))
					$row_action = '<div class="row-action">
						<span class="' . $t . '">' . $typemap[$t] . '</span>
					</div>';

				if ($t == 'called') {
					$icon_action = '<span class="icon-action">
						<svg width="16px" height="16px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#808080"><path d="M16 5h6m0 0l-3-3m3 3l-3 3M18.118 14.702L14 15.5c-2.782-1.396-4.5-3-5.5-5.5l.77-4.13L7.815 2H4.064c-1.128 0-2.016.932-1.847 2.047.42 2.783 1.66 7.83 5.283 11.453 3.805 3.805 9.286 5.456 12.302 6.113 1.165.253 2.198-.655 2.198-1.848v-3.584l-3.882-1.479z" stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					</span>';
				} else if ($t == 'paid') {
					$icon_action = '<span class="icon-action">
						<svg width="16px" height="16px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#808080"><path d="M7 12.5l3 3 7-7" stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					</span>';
				}

				$data_more = 'data-id-process="' . $id_wprocess . '" data-type-process="' . $t . '"';
			}

			$action = "";
			if (is_admin($current_user)) {
				$action = '<div class="action action-remove" data-toggle="tooltip" data-placement="top" title="Xóa diễn giải" data-id-note="' . $row['detail_id'] . '" ' . $data_more . ' booking-id="' . $this->bean->id . '">
					<svg width="18px" height="18px" viewBox="0 0 24 24" stroke-width="1.76" fill="none" xmlns="http://www.w3.org/2000/svg" color="#a3a4a6">
						<path d="M20 9l-1.995 11.346A2 2 0 0116.035 22h-8.07a2 2 0 01-1.97-1.654L4 9M21 6h-5.625M3 6h5.625m0 0V4a2 2 0 012-2h2.75a2 2 0 012 2v2m-6.75 0h6.75" stroke="#a3a4a6" stroke-width="1.76" stroke-linecap="round" stroke-linejoin="round"></path>
					</svg>
				</div>';
			}

			$send_success = '';
			$row_content .= '<div class="' . $class_of_row . '">';
			$row_content .= '<div class="row-time">' . date('H:i, d/m/Y', strtotime($row['date_entered']) + 7 * 3600) . '</div>
							' . $row_action . '
							<div class="row-user">' . $icon_action . $note_username . '</div>
							<div class="row-content">' . $action . $row['description'] . $send_success . '</div>
						</div>';
		}

		return [$row_content, $note_username];
	}

	private function renderLineNotesPanel($row_content, $note_username)
	{
		$html = '<div class="menu-control__tablet-wrap" id="line-notes">
			<input type="checkbox" id="slide-menu" />
			<label for="slide-menu" class="header-slide-menu__btn btn btn-primary" id="btn-open-mobile-menu">
				<svg width="18px" height="18px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#fff" style="vertical-align:sub;margin-right:2px">
					<path d="M8 10h8M8 14h4M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12c0 1.821.487 3.53 1.338 5L2.5 21.5l4.5-.838A9.955 9.955 0 0012 22z" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
				</svg>
				<span>Diễn giải</span> 
			</label>
			<div class="mobile-menu">
				<div class="mobile-menu__top">
					<h3 class="title">Diễn giải</h3>
					<label for="slide-menu" class="wrap-cancel">
						<svg aria-hidden="true" height="24px" viewBox="0 0 24 24" width="24px"><g stroke="var(--text-primary-color)" stroke-linecap="round" stroke-width="2"><line x1="6" x2="18" y1="6" y2="18"></line><line x1="6" x2="18" y1="18" y2="6"></line></g></svg>
					</label>
				</div>
				<div class="mobile-menu-wrapper">
					<div class="message_list">
						' . $row_content . '
					</div>
					<div class="">
						<div class="wrap-input">
							<div class="wrap-text">
								<textarea rows="1" class="box-input input-note-description" id="note-description" placeholder="Thêm diễn giải..."></textarea>
							</div>
							<div class="wrap-icon">
								<input type="hidden" name="note-username" id="note-username" value="' . $note_username . '" />
								<input type="hidden" name="note-name" id="note-name" value="' . $this->bean->name . '" />
								<input type="hidden" name="note-parent-id" id="note-parent-id" value="' . $this->bean->id . '" />
								<input type="hidden" name="note-booking-status" id="note-booking-status" value="' . $this->bean->booking_status . '" />
								<input type="hidden" name="note-contact-name" id="note-contact-name" value="' . $this->bean->contact_name . '" />
								<input type="hidden" name="note-total-amount" id="note-total-amount" value="' . $this->bean->total_amount . '" />
								<input type="hidden" name="note-total-qty" id="note-total-qty" value="' . $this->bean->total_qty . '" />
								<svg xmlns="http://www.w3.org/2000/svg" id="icon-send-notes" width="20" height="20" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
									<path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>';

		return $html;
	}

	private function renderDeleteMessageDialog()
	{
		$html = '<div id="confirm_delete_message_dialog">
			<div class="content_message_delete">Bạn muốn xóa diễn giải này?</div>
			<div class="action_message_delete d-flex align-items-center justify-content-center gap-2">
				<a class="btn btn-danger cursor-pointer" data-toggle="tooltip" data-placement="top" title="Xác nhận" id="confirm_delete_message" value="default">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
						<path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
					</svg>
				</a>
				<a class="btn btn-secondary cursor-pointer" data-toggle="tooltip" data-placement="top" title="Hủy" id="cancel_delete_message" value="cancel">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
						<path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
					</svg>
				</a>
			</div>
		</div>';

		return $html;
	}

	function populateWorkingProcessNote()
	{
		// Popup nhập ghi chú xử lý nghiệp vụ: recheck, recall, hỗ trợ khách, điều chỉnh công nợ...
		$html = '
		<div id="dlgWorkingProcessNote" title="Ghi chú" style="display:none;">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr style="display:none;">
					<td width="15%">Lý do</td>
					<td width="85%"><table class="win-lose-radio" border="0" cellpadding="0" cellspacing="0"></table></td>
				</tr>
				<tr style="display:none;">
					<td width="15%">Bonus</td>
					<td width="85%"><input type="text" name="txtBonus" id="txtBonus" value="" /></td>
				</tr>
				<tr>
					<td colspan="2"><textarea id="txtWorkingProcessNote" rows="7" placeholder="Aa..."></textarea></td>
				</tr>
				<tr>
					<td class="d-flex justify-content-end align-items-center gap-2 mt-2">
						<input type="button" id="btnSaveWorkingProcess" class="btn btn-primary" value="Lưu" title="Lưu"/>
						<input type="hidden" id="frmSaveWorkingProcess" value="" />
						<input type="button" id="btnCloseWorkingProcess" class="btn btn-danger" value="Hủy bỏ" title="Hủy bỏ" />
						<span id="save-working-process-loading"></span>
						<span id="save-working-process-error" class="error"></span>
					</td>
				</tr>
			</table>	
		</div>';
		return $html;
	}

	// Hiện thông tin hoá đơn
}
