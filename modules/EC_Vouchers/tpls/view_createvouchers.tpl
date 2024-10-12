<link rel="stylesheet" href="modules/EC_Vouchers/css/createvouchers.css">

<h1 class="title">PHÁT HÀNH VOUCHER</h1>

<div class="box-create-voucher box-section w-60">
	<form id="form_create_voucher" method="post">
		<table id="voucher_tbl" class="table-edit table-release__voucher table-create-voucher" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td class="label">Loại voucher:</td>
					<td class="value">
						<div class="d-flex gap-4">
							<div class="form-check">
								<input class="form-check-input" type="radio" name="type" id="voucher_type_group" value="group" checked>
								<label class="form-check-label" for="voucher_type_group">
								  	Nhóm (Công khai)
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="type" id="voucher_type_single" value="single">
								<label class="form-check-label" for="voucher_type_single">
									Đơn (Riêng tư)
								</label>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="label">Tên sự kiện: <span class="fw-bold color-red">*</span></td>
					<td class="value">
						<input type="text" name="campaign_name" value="{$CAMPAIGN_NAME}" class="box-input" required>
					</td>
				</tr>
				<tr class="row-voucher-code">
					<td class="label">Mã voucher: <span class="fw-bold color-red">*</span></td>
					<td class="value">
						<input type="text" name="voucher_code" id="voucher_code" class="box-input" minlength="6" maxlength="24" required>
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
								<input type="text" class="allow-number-only box-input" id="reduce_amount" name="reduce_amount" value="">
							</div>
							<div>
								<label>Phần trăm giảm giá</label>
								<input type="text" class="allow-number-only box-input" id="reduce_percent" name="reduce_percent" value="" maxlength="3">
							</div>
						</div>
					</td>
				</tr>
				<tr class="row-quantity">
					<td class="label">Số lượng: <span class="fw-bold color-red">*</span></td>
					<td class="value">
						<input type="text" class="allow-number-only box-input" id="voucher_qty" name="voucher_qty" value="{$VOUCHER_QTY}" required>
					</td>
				</tr>
				<tr class="row-validate-date">
					<td class="label">Thời gian diễn ra:</td>
					<td class="value">
						<div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
							<div class="dateTime d-flex gap-2 position-relative">
								<input type="text" id="from_date" name="from_date" value="{$FROM_DATE}" class="date_input box-input">
								<button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
										<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
										<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
									</svg>
								</button>
							</div>
							<svg width="40" height="20" fill="none">
								<g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
									<path d="M33.5 8.5L36 11M4 11h32"></path>
								</g>
								<defs>
									<clipPath id="icon_arrow_flight_long_svg__clip0">
										<path fill="#fff" d="M0 0h40v20H0z"></path>
									</clipPath>
								</defs>
							</svg>
							<div class="dateTime d-flex gap-2 position-relative">
								<input type="text" id="to_date" name="to_date" value="{$TO_DATE}" class="date_input box-input">
								<button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
										<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
										<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
									</svg>
								</button>
							</div>
						</div>
					</td>
				</tr>
				
				<tr class="row-condition">
					<td class="label align-top">Điều kiện áp dụng:</td>
					<td class="value">
						<select name="voucher_condition" id="voucher_condition" class="box-select">
							<option value="">--- Chọn điều kiện ---</option>
							<option value="min_price">Đơn giá tối thiểu</option>
							<option value="max_discount">Giảm tối đa</option>
							<option value="total_qty">Số vé</option>
							<option value="flight_type">Chuyến bay</option>
							<option value="ticket_type">Phạm vi áp dụng</option>
							<option value="journey">Hành trình áp dụng</option>
						</select>
					</td>
				</tr>
				<tr class="row-description">
					<td class="label align-top">Mô tả:</td>
					<td class="value">
						<textarea class="box-textarea" name="voucher_description" id="voucher_description" rows="5"></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type="submit" id="create_voucher_btn" class="btn btn-primary extra_amt" value="Phát hành"></td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="module" value="EC_Vouchers">
		<input type="hidden" name="action" value="Save">
		<input type="hidden" name="createMultipleVoucher">
	</form>
	{$VOUCHER_TBL}
</div>

<script src="custom/jqueryui/plugins/jquery.number.min.js"></script>
<script src="modules/EC_Vouchers/js/createvouchers.js"></script>