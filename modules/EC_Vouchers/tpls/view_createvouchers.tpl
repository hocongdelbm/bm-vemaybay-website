<script src="custom/jqueryui/plugins/jquery.number.min.js"></script>
{literal}
<script>
	$(document).ready(function() {
		Calendar.setup ({
			inputField : "from_date",
			daFormat : "%d-%m-%Y %H:%M",
			button : "from_date_trigger",
			singleClick : true,
			dateStr : "'.date('d-m-Y').'",
			step : 1,
			weekNumbers:false
		});
		Calendar.setup ({
			inputField : "to_date",
			daFormat : "%d-%m-%Y %H:%M",
			button : "to_date_trigger",
			singleClick : true,
			dateStr : "'.date('d-m-Y').'",
			step : 1,
			weekNumbers:false
		});

        	$(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);

		$(document).on("keyup","#voucher_code, #condition_value_journey",function() {
			this.value = this.value.toLocaleUpperCase();
		});

		$(document).on("input",".numbersOnly",function() {
			$(this).val(Number($(this).val().replace(/\D/g, '')).toLocaleString());
		});

		$("#create_voucher_frm").submit(function() {
			let qty 	= parseInt($("#voucher_qty").val()) || 0;
			let price = parseInt($("#voucher_price").val()) || 0;
			if(qty <= 0) {
				let text_warning = 'Vui lòng bổ sung số lượng voucher phát hành!';
				showToastWarning(text_warning);
				$("#voucher_qty").focus();
				return false;
			} else if(price <= 0) {
				let text_warning = 'Vui lòng bổ sung mệnh giá voucher phát hành!';
				showToastWarning(text_warning);
				$("#voucher_price").focus();
				return false;
			} 

			let code_voucher = $("#voucher_code").val();

			if(code_voucher.length < 3 || code_voucher.length > 6){
				let text_warning = 'Mã voucher tối thiểu 3 kí tự';
				showToastWarning(text_warning);
				$("#voucher_code").focus();
				return false;
			}

			if($('#condition_value_total_qty').length !== 0 && $('#condition_value_total_qty').val() == ''){
				let text_warning = 'Vui lòng nhập số vé.';
				showToastWarning(text_warning);
				$("#condition_value_total_qty").focus();
				return false;
			}
			if($('#condition_value_total_amount').length !== 0 && $('#condition_value_total_amount').val() == ''){
				let text_warning = 'Vui lòng nhập đơn giá tối thiểu.';
				showToastWarning(text_warning);
				$("#condition_value_total_amount").focus();
				return false;
			}

			if($('#condition_value_journey').length !== 0 && $('#condition_value_journey').val() == ''){
				let text_warning = 'Vui lòng nhập hành trình áp dụng voucher.';
				showToastWarning(text_warning);
				$("#condition_value_journey").focus();
				return false;
			} 

			return true;
		});

		$("#voucher_condition").on('change', function(){
			$("#voucher_condition").after(insertConditionVoucher($(this).val()));
		});

		$(document).on("click",".remove_condition",function() {
			let id_condition = $(this).attr('data-field').trim();
			$("#"+id_condition).remove();
		});

		function insertConditionVoucher(field){
			let condition_id = $("#"+field);

			if(field && condition_id.length === 0){
				let text_value 	= '';
				let text_lass 		= '';
				let placeholder 	= '';
				let selected 		= '';
				let hide_type  = '', hide_journey = '';

				if(field == 'total_qty'){
					text_value = 'Số vé';
					placeholder = '2';
					text_lass = 'numbersOnly';
				} else if(field == 'journey'){
					text_value = 'Hành trình áp dụng';
					placeholder = 'SGN-HAN|DAD-TBB';
					selected = 'selected';
					hide_journey = 'd-none';
				} else if(field == 'total_amount'){
					text_value = 'Đơn giá tối thiểu';
					placeholder = '100000';
					text_lass = 'numbersOnly';
				} else if(field == 'ticket_type'){
					text_value = 'Phạm vi áp dụng';
					selected = 'selected';
					hide_type  = 'd-none';
				} else if(field == 'flight_type'){
					text_value = 'Chuyến bay';
					hide_type = 'd-none';
					selected = 'selected';
				}
				
				let html = `<div class="condition-wrap mt-2" id="${field}">
							<div class="d-flex align-items-center gap-2">
								<input type="text" class="w-33 box-input" value="${text_value}" disabled />
								<input type="hidden" name="field[]" id="condition_field_${field}" value="${field}" />
								<select name="operator[]" id="condition_operator_${field}" class="w-33 box-select text-start">
									<option class="${hide_type} ${hide_journey}" value="<">Nhỏ hơn</option>
									<option class="${hide_type} ${hide_journey}" value="<=">Nhỏ hơn hoặc bằng</option>
									<option ${selected} value="==">Bằng</option>
									<option class="${hide_type} ${hide_journey}" value=">">Lớn hơn</option>
									<option class="${hide_type} ${hide_journey}" value=">=">Lớn hơn hoặc bằng</option>
									<option class="${hide_type}" value="!=">Khác</option>
								</select>`;

								if(field == 'ticket_type'){
									html += `<select name="value[]" id="condition_value_${field}" class="w-33 box-select">
											<option value="1">Nội địa</option>
											<option value="2">Quốc tế</option>
										</select>`;
								} else if(field == 'flight_type'){
									html += `<select name="value[]" id="condition_value_${field}" class="w-33 box-select">
											<option value="1">Một chiều</option>
											<option value="0">Khứ hồi</option>
										</select>`;
								} else {
									html += `<input type="text" name="value[]" id="condition_value_${field}" placeholder="${placeholder}" class="w-33 box-input ${text_lass}" />`;
								}
								
						html += `<button data-field="${field}" class="remove_condition button-remove-in-edit" title="Xóa điều kiện" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ec2029" class="bi bi-dash-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"></path></svg></button>
					</div>
				</div>`;
	
				return html;
			} else {
				let text_warning = 'Điều kiện đã được chọn!';
				showToastWarning(text_warning);
				return false;
			}
		}
	});
</script>
{/literal}

<h1 class="title">PHÁT HÀNH VOUCHER</h1>

<div id="create_voucher">
	<form id="create_voucher_frm" class="box-section w-60" method="post">
		<table id="voucher_tbl" class="table-edit table-release__voucher" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td class="align-top" width="25%">Tên Sự kiện:</td>
					<td width="75%">
						<input type="text" name="campaign_name" value="{$CAMPAIGN_NAME}" class="box-input">
					</td>
				</tr>
				<tr>
					<td class="align-top" >Mã voucher: (<span class="fw-bold color-red">*</span>)</td>
					<td><input type="text" class="box-input" id="voucher_code" name="voucher_code" minlength="3" maxlength="6" value="{$PREFIX}"></td>
				</tr>
				<tr>
					<td class="align-top" >Thời hạn:</td>
					<td>
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
				<tr>
					<td class="align-top" >Số lượng:</td>
					<td><input type="text" class="allow-number-only box-input" id="voucher_qty" name="voucher_qty" value="{$VOUCHER_QTY}"></td>
				</tr>
				<tr>
					<td class="align-top" >Mệnh giá:</td>
					<td><input type="text" class="allow-number-only box-input" id="voucher_price" name="voucher_price" value="{$VOUCHER_PRICE}"></td>
				</tr>
				<tr>
					<td class="align-top" >Điều kiện áp dụng:</td>
					<td>
						<select name="voucher_condition" id="voucher_condition" class="box-select">
							<option value="">---Chọn điều kiện---</option>
							<option value="flight_type">Chuyến bay</option>
							<option value="ticket_type">Phạm vi áp dụng</option>
							<option value="total_qty">Số vé</option>
							<option value="journey">Hành trình áp dụng</option>
							<option value="total_amount">Đơn giá tối thiểu</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="align-top" width="25%">Mô tả:</td>
					<td width="75%">
						<textarea class="box-textarea" name="voucher_description" id="voucher_description" style="min-height: 150px;"></textarea>
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