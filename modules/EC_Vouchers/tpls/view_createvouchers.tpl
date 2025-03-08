<link rel="stylesheet" href="modules/EC_Vouchers/css/createvouchers.css">

<h1 class="title">PHÁT HÀNH VOUCHER</h1>
<<<<<<< HEAD
<div class="box-create-voucher box-section w-60">
	<form id="form_create_voucher" method="post">
		<input type="hidden" name="module" value="EC_Vouchers">
		<input type="hidden" name="action" value="Save">
		<table id="voucher_tbl" class="table-edit table-release__voucher table-create-voucher" cellpadding="0"
			cellspacing="0">
=======

<div class="box-create-voucher box-section w-70">
	<form id="form_create_voucher" method="post">
		<input type="hidden" name="module" value="EC_Vouchers">
		<input type="hidden" name="action" value="Save">
		<table id="voucher_tbl" class="table-edit table-release__voucher table-create-voucher" cellpadding="0" cellspacing="0">
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
			<tbody>
				<tr class="row-type">
					<td class="label">Loại voucher:</td>
					<td class="value">
						<div class="d-flex gap-4">
							<div class="form-check">
<<<<<<< HEAD
								<input type="radio" name="type" id="voucher_type_group" class="form-check-input"
									value="group" checked>
								<label class="form-check-label" for="voucher_type_group">
									Nhóm (Công khai)
								</label>
							</div>
							<div class="form-check">
								<input type="radio" name="type" id="voucher_type_single" class="form-check-input"
									value="single">
=======
								<input class="form-check-input" type="radio" name="type" id="voucher_type_group" value="group" checked />
								<label class="form-check-label" for="voucher_type_group">
								  	Nhóm (Mã chung)
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="type" id="voucher_type_single" value="single" />
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
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
<<<<<<< HEAD
						<input type="text" name="campaign_name" class="box-input" value="" required>
					</td>
				</tr>
				<tr class="row-voucher-code">
					<td class="label">Mã voucher: <span class="fw-bold color-red">*</span></td>
					<td class="value">
						<input type="text" name="voucher_code" id="voucher_code" class="box-input" value=""
							minlength="6" maxlength="24">
					</td>
				</tr>
				<tr class="row-website">
					<td class="label">Website áp dụng: <span class="fw-bold color-red">*</span></td>
					<td class="value">
						<select name="website" id="website" class="form-select">
							<option value="vietjet.net">vietjet.net</option>
							<option value="timchuyenbay.com">timchuyenbay.com</option>
							<option value="vemaybay5s.com">vemaybay5s.com</option>
						</select>
=======
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
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
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
<<<<<<< HEAD
								<input type="text" name="reduce_amount" id="reduce_amount" class="allow-number-only box-input" value="">
							</div>
							<div>
								<label>Phần trăm giảm giá</label>
								<input type="text" name="reduce_percent" id="reduce_percent" class="allow-number-only box-input" value="" maxlength="3">
=======
								<input type="text" class="allow-number-only box-input" id="reduce_amount" name="reduce_amount" value="" />
							</div>
							<div>
								<label>Phần trăm giảm giá</label>
								<input type="text" class="allow-number-only box-input" id="reduce_percent" name="reduce_percent" value="" maxlength="3" />
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
							</div>
						</div>
					</td>
				</tr>
				<tr class="row-quantity">
					<td class="label">Giảm tối đa:</td>
					<td class="value">
<<<<<<< HEAD
						<input type="text" name="max_discount" id="max_discount" class="allow-number-only box-input" value="">
					</td>
				</tr>
				<tr class="row-validate-date">
					<td class="label">Thời gian diễn ra: <span class="fw-bold color-red">*</span></td>
=======
						<input type="text" class="allow-number-only box-input" id="voucher_qty" name="voucher_qty" required />
					</td>
				</tr>
				<tr class="row-website">
					<td class="label">Website áp dụng: <span class="fw-bold color-red">*</span></td>
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
					<td class="value">
						<select name="website" id="website" class="form-select">
							<option value="timchuyenbay.com">timchuyenbay.com</option>
						</select>
					</td>
				</tr>
				<tr class="row-datetime">
					<td class="label">Thời gian diễn ra: <span class="fw-bold color-red">*</span></td>
					<td class="value">
						<div class="d-inline-flex gap-2 align-items-center">
							<div class="dateTime d-flex gap-2 position-relative">
<<<<<<< HEAD
								<input type="text" name="from_date" id="from_date" value="{$FROM_DATE}"
									class="date_input box-input">
								<button class="icon_dateTime" type="button" id="from_date_trigger"
									onclick="return false;">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
										class="bi bi-calendar2" viewBox="0 0 16 16">
										<path
											d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z">
										</path>
										<path
											d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z">
										</path>
=======
								<input type="text" name="start_date" id="start_date" value="{$START_DATE}" class="date_input box-input">
								<input type="text" name="start_hour" id="start_hour" value="00" class="hour_input box-input" maxlength="2" size="2"/>
								<b>:</b>
								<input type="text" name="start_minute" id="start_minute" value="00" class="minute_input box-input" maxlength="2" size="2" />
								<button class="icon_dateTime" type="button" id="start_date_trigger" onclick="return false;">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
										<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
										<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
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
<<<<<<< HEAD
								<input type="text" name="to_date" id="to_date" value="{$TO_DATE}"
									class="date_input box-input">
								<button class="icon_dateTime" type="button" id="to_date_trigger"
									onclick="return false;">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
										class="bi bi-calendar2" viewBox="0 0 16 16">
										<path
											d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z">
										</path>
										<path
											d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z">
										</path>
=======
								<input type="text" name="end_date" id="end_date" value="{$END_DATE}" class="date_input box-input">
								<input type="text" name="end_hour" id="end_hour" value="00" class="hour_input box-input" maxlength="2" size="2"/>
								<b>:</b>
								<input type="text" name="end_minute" id="end_minute" value="00" class="minute_input box-input" maxlength="2" size="2" />
								<button class="icon_dateTime" type="button" id="end_date_trigger" onclick="return false;">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
										<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
										<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
									</svg>
								</button>
							</div>
						</div>
					</td>
				</tr>
<<<<<<< HEAD
				<tr class="row-quantity">
					<td class="label">Số lượng: <span class="fw-bold color-red">*</span></td>
					<td class="value">
						<input type="text" name="quantity" id="quantity" class="allow-number-only box-input" value="{$VOUCHER_QTY}" maxlength="8" required>
					</td>
				</tr>
				<tr class="row-condition">
					<td class="label align-top">Điều kiện áp dụng:</td>
					<td class="value">
						<select name="voucher_condition" id="voucher_condition" class="box-select">
							<option value="">--- Chọn điều kiện ---</option>
							<option value="min_price">Đơn tối thiểu</option>
							<option value="total_qty">Số vé</option>
							<option value="flight_type">Chuyến bay</option>
							<option value="ticket_type">Phạm vi áp dụng</option>
							<option value="journey">Hành trình áp dụng</option>
						</select>
=======
				<tr class="row-condition-apply">
					<td class="label align-top">Điều kiện áp dụng:</td>
					<td class="value">
						<div class="list_condition">
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
									<input type="radio" class="form-check-input" id="flight_type_all" name="flight_type" value="">
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
									<input type="radio" class="form-check-input" id="ticket_type_all" name="ticket_type" value="">
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
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
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
<script src="modules/EC_Vouchers/js/createvouchers.js"></script>