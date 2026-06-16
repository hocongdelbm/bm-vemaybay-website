<link rel="stylesheet" href="modules/EC_Vouchers/css/createvouchers.css?v=20260615-phone-compact">

<h1 class="title">PHÁT HÀNH VOUCHER</h1>

<div class="box-create-voucher box-section w-70">
	<form id="form_create_voucher" method="post">
		<input type="hidden" name="module" value="EC_Vouchers">
		<input type="hidden" name="action" value="Save">
		<table id="voucher_tbl" class="table-edit table-release__voucher table-create-voucher" cellpadding="0" cellspacing="0">
			<tbody>
				<tr class="row-type">
					<td class="label">Loại voucher:</td>
					<td class="value">
						<div class="d-flex gap-4">
							<div class="form-check">
								<input class="form-check-input" type="radio" name="type" id="voucher_type_group" value="group" checked />
								<label class="form-check-label" for="voucher_type_group">
								  	Nhóm (Mã chung)
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="type" id="voucher_type_single" value="single" />
								<label class="form-check-label" for="voucher_type_single">
									Đơn (Mỗi voucher mã khác nhau)
								</label>
							</div>
						</div>
					</td>
				</tr>
				<tr class="row-campaign">
					<td class="label">Tên sự kiện: <span class="fw-bold color-red">*</span></td>
					<td class="value">
						<input type="text" name="campaign_name" class="box-input" required />
					</td>
				</tr>
				<tr class="row-voucher-code">
					<td class="label">Mã voucher:</td>
					<td class="value d-flex gap-3">
						<input type="text" name="voucher_code" id="voucher_code" class="box-input" minlength="6" maxlength="24" />
						<div class="form-check-hide-voucher">
							<input type="checkbox" name="is_hidden" id="is_hidden" class="box-input" value="1" />
							<label class="ms-1" for="is_hidden">Ẩn voucher</label>
						</div>
					</td>
				</tr>
				<tr class="row-price">
					<td class="label">
						Mệnh giá: <span class="fw-bold color-red">*</span>
						<p class="text-secondary fw-light fst-italic pt-1">(Điền 1 trong 2)</p>
					</td>
					<td class="value">
						<div class="d-flex gap-3">
							<div>
								<label>Số tiền giảm giá</label>
								<input type="text" class="allow-number-only box-input" id="reduce_amount" name="reduce_amount" value="" />
							</div>
							<div>
								<label>Phần trăm giảm giá</label>
								<input type="text" class="allow-number-only box-input" id="reduce_percent" name="reduce_percent" value="" maxlength="3" />
							</div>
						</div>
					</td>
				</tr>
				<tr class="row-quantity">
					<td class="label">Số lượng:</td>
					<td class="value">
						<input type="text" class="allow-number-only box-input" id="voucher_qty" name="voucher_qty" required />
					</td>
				</tr>
				<tr class="row-phone-list" style="display:none;">
					<td class="label align-top">Danh sách SĐT:</td>
					<td class="value">
						<div class="voucher-phone-panel">
							<div class="voucher-phone-header">
								<div class="voucher-phone-title">
									<span class="voucher-phone-title-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
											<path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
										</svg>
									</span>
									<strong>Danh sách người nhận</strong>
								</div>
								<button type="button" class="btn btn-secondary btn-sm voucher-phone-sample" id="btn-download-voucher-phone-sample">
									<span>&#8595;</span> File mẫu
								</button>
							</div>
							<div class="voucher-phone-left">
								<label class="voucher-phone-section-label">Tải lên danh sách</label>
								<label class="voucher-phone-dropzone" for="voucher_phone_file">
									<input type="file" id="voucher_phone_file" accept=".xlsx,.xls,.csv,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
									<span class="voucher-phone-upload-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
											<path d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383Z"/>
											<path d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3Z"/>
										</svg>
									</span>
									<span>Kéo thả <b>.xlsx</b> hoặc bấm để chọn</span>
								</label>
								<div class="voucher-phone-file-name" id="voucher_phone_file_name"></div>
								<label class="voucher-phone-section-label voucher-phone-manual-label" for="voucher_phone_manual">Nhập thủ công</label>
								<textarea class="box-textarea" id="voucher_phone_manual" rows="5" placeholder="Nhập danh sách SĐT, mỗi số một dòng..."></textarea>
								<div class="voucher-phone-main-actions">
									<button type="button" class="btn btn-success" id="btn-import-voucher-phones">Áp dụng và Cập nhật</button>
								</div>
							</div>
							<div class="voucher-phone-right">
								<div class="voucher-phone-preview-head">
									<strong id="voucher_phone_manual_label">Xem trước danh sách (0)</strong>
								</div>
								<div class="voucher-phone-result">
									<table class="voucher-phone-preview-table">
										<thead>
											<tr>
												<th>STT</th>
												<th>SĐT</th>
												<th></th>
											</tr>
										</thead>
										<tbody id="voucher_phone_preview"></tbody>
									</table>
									<div class="voucher-phone-pager">
										<span id="voucher_phone_preview_note">Chỉ hiển thị 10 bản ghi đầu tiên</span>
										<button type="button" class="voucher-phone-page-btn" id="voucher_phone_prev">‹</button>
										<button type="button" class="voucher-phone-page-btn" id="voucher_phone_next">›</button>
										<button type="button" class="voucher-phone-clear-link" id="btn-clear-voucher-phones">Xóa tất cả</button>
									</div>
								</div>
							</div>
							<button type="button" class="btn btn-success btn-sm" id="btn-update-voucher-phones" style="display:none">+0</button>
							<div class="voucher-phone-import-status" id="voucher_phone_import_status"></div>
						</div>
						<textarea id="voucher_phones" name="voucher_phones" style="display:none"></textarea>
						<p class="text-secondary fw-light fst-italic pt-1">* Mã voucher sẽ là số điện thoại sau khi chuẩn hóa.</p>
					</td>
				</tr>
				<tr class="row-website">
					<td class="label">Website áp dụng: <span class="fw-bold color-red">*</span></td>
					<td class="value">
						<select name="website" id="website" class="form-select">
							<option value="timchuyenbay.com">timchuyenbay.com</option>
							<option value="app.vemaybay.website">app.vemaybay.website</option>
						</select>
					</td>
				</tr>
				<tr class="row-datetime">
					<td class="label">Thời gian diễn ra: <span class="fw-bold color-red">*</span></td>
					<td class="value">
						<div class="d-inline-flex gap-2 align-items-center">
							<div class="dateTime d-flex gap-2 position-relative">
								<input type="text" name="start_date" id="start_date" value="{$START_DATE}" class="date_input box-input">
								<input type="text" name="start_hour" id="start_hour" value="00" class="hour_input box-input" maxlength="2" size="2"/>
								<b>:</b>
								<input type="text" name="start_minute" id="start_minute" value="00" class="minute_input box-input" maxlength="2" size="2" />
								<button class="icon_dateTime" type="button" id="start_date_trigger" onclick="return false;">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
										<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
										<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
									</svg>
								</button>
							</div>
							<svg width="40" height="20" fill="none">
								<g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096"
									stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
									<path d="M33.5 8.5L36 11M4 11h32"></path>
								</g>
								<defs>
									<clipPath id="icon_arrow_flight_long_svg__clip0">
										<path fill="#fff" d="M0 0h40v20H0z"></path>
									</clipPath>
								</defs>
							</svg>
							<div class="dateTime d-flex gap-2 position-relative">
								<input type="text" name="end_date" id="end_date" value="{$END_DATE}" class="date_input box-input">
								<input type="text" name="end_hour" id="end_hour" value="00" class="hour_input box-input" maxlength="2" size="2"/>
								<b>:</b>
								<input type="text" name="end_minute" id="end_minute" value="00" class="minute_input box-input" maxlength="2" size="2" />
								<button class="icon_dateTime" type="button" id="end_date_trigger" onclick="return false;">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
										<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
										<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
									</svg>
								</button>
							</div>
						</div>
					</td>
				</tr>
				<tr class="row-condition-apply">
					<td class="label align-top">Điều kiện áp dụng:</td>
					<td class="value">
						<div class="list_condition">
							<div class="condition condition-for-phone">
								<div class="condition-name">Cho SĐT:</div>
								<div class="condition-value">
									<input type="text" name="for_phone_value" class="box-input allow-number-only" value="" />
								</div>
							</div>
							<div class="condition">
								<div class="condition-name">Đơn tối thiểu:</div>
								<div class="condition-value">
									<input type="text" name="min_order_value" class="box-input allow-number-only" value="0" />
								</div>
							</div>
							<div class="condition">
								<div class="condition-name">Số lượng vé:</div>
								<div class="condition-value">
									<input type="text" name="number_of_tickets" class="box-input allow-number-only" value="0" />
								</div>
							</div>
							<div class="condition">
								<div class="condition-name">Loại chuyến:</div>
								<div class="condition-value">
									<input type="radio" class="form-check-input" id="flight_type_domestic" name="flight_type" value="domestic">
  									<label class="form-check-label" for="flight_type_domestic">Nội địa</label>
									<input type="radio" class="form-check-input" id="flight_type_international" name="flight_type" value="international">
  									<label class="form-check-label" for="flight_type_international">Quốc tế</label>
									<input type="radio" class="form-check-input" id="flight_type_all" name="flight_type" value="" checked>
  									<label class="form-check-label" for="flight_type_all">Tất cả</label>
								</div>
							</div>
							<div class="condition">
								<div class="condition-name">Loại vé:</div>
								<div class="condition-value">
									<input type="radio" class="form-check-input" id="ticket_type_one_way" name="ticket_type" value="1">
  									<label class="form-check-label" for="ticket_type_one_way">Một chiều</label>
									<input type="radio" class="form-check-input" id="ticket_type_round_trip" name="ticket_type" value="2">
  									<label class="form-check-label" for="ticket_type_round_trip">Khứ hồi</label>
									<input type="radio" class="form-check-input" id="ticket_type_all" name="ticket_type" value="" checked>
  									<label class="form-check-label" for="ticket_type_all">Tất cả</label>
								</div>
							</div>
							<div class="condition">
								<div class="condition-name">Hành trình:</div>
								<div class="condition-value">
									<textarea class="form-control" rows="3" id="journey" name="journey" placeholder="SGN-HAN,HAN-SGN"></textarea>
								</div>
							</div>
							<div></div>
						</div>
					</td>
				</tr>
				<tr class="row-condition-included">
					<td class="label align-top">Điều kiện đi kèm:</td>
					<td class="value">
						<div class="list_condition">
							<div class="condition">
								<div class="condition-name">Giảm tối đa:</div>
								<div class="condition-value">
									<input type="text" name="max_discount" class="box-input allow-number-only" />
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr class="row-description">
					<td class="label align-top">Mô tả:</td>
					<td class="value">
						<textarea name="description" id="description" class="box-textarea" rows="5"></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<input type="submit" id="create_voucher_btn" class="btn btn-primary extra_amt" value="Phát hành">
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>

<script src="custom/jqueryui/plugins/jquery.number.min.js"></script>
<script src="modules/EC_Vouchers/js/createvouchers.js?v=20260615-phone-confirm-delete"></script>
