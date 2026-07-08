$(document).ready(function () {
	$('#luggage_fee, #other_fee, #discount_amount, #total_amount').addClass('allow-number-only');
	$('#luggage_fee, #other_fee, #discount_amount, #total_amount').css({ 'text-align': 'right' });

	const discountElement = $("#discount_amount");
	discountElement.prop("disabled", true);
	discountElement.bind("cut copy paste", function (e) {
		e.preventDefault();
	});
	discountElement.on('keydown', function (e) {
		e.preventDefault();
	});
	discountElement.on('input', function (e) {
		e.preventDefault();
	});
	discountElement.on('contextmenu', function (e) {
		e.preventDefault();
	});

	$('.allow-number-only').number(true, 0, dec_sep, num_grp_sep);

	// Not allow enter
	$('input:text').bind("keypress", function (e) {
		if (e.keyCode == 13) return false;
	});

	// Not allow number
	$('.datetime_h, .datetime_m').on('keydown', function (event) {
		$(this).allowNumberOnly(event);
	});

	$('.datetime_h').on('blur', function () {
		var hour = parseInt($(this).val());
		if (hour < 0 || hour > 23) {
			alert('Giờ phải nằm trong khoảng từ 0-23');
			$(this).val('00');
			$(this).focus();
			$(this).select();
			return false;
		}
		if (hour < 10) {
			$(this).val('0' + hour);
			hour = '0' + hour;
		}
		var ele_id = $(this).attr('id');
		ele_id = ele_id.substr(0, ele_id.length - 2);
		var date = $.trim($('#' + ele_id + '_date').val());
		var minute = $.trim($('#' + ele_id + '_m').val());
		$('#' + ele_id).val(date + ' ' + hour + ':' + minute);
	});

	$('.datetime_m').on('blur', function () {
		var minute = parseInt($(this).val());
		if (minute < 0 || minute > 59) {
			alert('Phút phải nằm trong khoảng từ 0 - 59');
			$(this).val('00');
			$(this).focus();
			$(this).select();
			return false;
		}
		if (minute < 10) {
			$(this).val('0' + minute);
			minute = '0' + minute;
		}
		var ele_id = $(this).attr('id');
		ele_id = ele_id.substr(0, ele_id.length - 2);
		var date = $.trim($('#' + ele_id + '_date').val());
		var hour = $.trim($('#' + ele_id + '_h').val());
		$('#' + ele_id).val(date + ' ' + hour + ':' + minute);
	});

	$('.bkd_select_supplier').select2();
	$('.psg_luggage_purchase_select').select2();

	// Xử lý khi tạo booking thì không hiện field booking_name
	if ($.trim($('.edit-view-row-item[data-field="name"] div[type="name"]').html()) == '') {
		$('.edit-view-row-item[data-field="name"] .label').remove();
		$('.edit-view-row-item[data-field="name"] .edit-view-field').remove();
	}

	// Dim itinerary calendar days in the past (before 01-07-2026)
	// when the user navigates the calendar back to previous months
	const CALENDAR_PAST_CUTOFF = new Date(2026, 6, 1); // months are 0-based: 6 = July
	if (typeof YAHOO !== 'undefined' && YAHOO.widget && YAHOO.widget.Calendar) {
		const originalRenderCellDefault = YAHOO.widget.Calendar.prototype.renderCellDefault;
		YAHOO.widget.Calendar.prototype.renderCellDefault = function (workingDate, cell) {
			const result = originalRenderCellDefault.call(this, workingDate, cell);
			// Only for itinerary date pickers (container id: iti_..._trigger<n>_div),
			// keep passenger birthday and other calendars untouched
			const containerId = this.oDomContainer ? this.oDomContainer.id : '';
			if (containerId.indexOf('iti_') === 0 && workingDate < CALENDAR_PAST_CUTOFF) {
				YAHOO.util.Dom.addClass(cell, 'iti-past-date');
			}
			return result;
		};

		$('<style>')
			.text('.cal_panel td.calcell.iti-past-date a.selector { color: #b3b3b3 !important; background-color: #f2f2f2; opacity: .55; filter: blur(.5px); }')
			.appendTo('head');
	}

	// Init calendar for date ticket issue
	setupDateCalendar('date_ticket_issue', 'date_ticket_issue_trigger');

	// When check is ticket exported
	$('#chk_is_ticket_exported').change(function () {
		if ($(this).is(':checked')) {
			$('#is_ticket_exported').val(1);
			$('span.date_ticket_issue').show();
		} else {
			$('#date_ticket_issue_outbound').val('');
			$('#is_ticket_exported').val(0);
			$('span.date_ticket_issue').hide();
		}
	});

	// Set calendar for itineraries
	$('input:text[name="iti_departure_date[]"]').each(function (index, element) {
		setupItineraryCalendars(index);
	});

	// Set calendar for passengers
	$('input:text[name="psg_birthday[]"]').each(function (index, element) {
		setupPassengerCalendar(index);
	});

	// Mã số thuế
	$("input#tax_code").on("keydown.autocomplete", function () {
		$(this).mcautocomplete({
			// These next two options are what this plugin adds to the autocomplete widget.
			showHeader: true,
			columns: [{
				name: 'Mã KH',
				width: '120px',
				valueField: 'ticker_symbol'
			}, {
				name: 'Tên KH',
				width: '200px',
				valueField: 'label'
			}, {
				name: 'MST',
				width: '100px',
				valueField: 'sic_code'
			}, {
				name: 'Địa chỉ',
				width: '300px',
				valueField: 'address'
			}],
			source: "index.php?entryPoint=entryPointEC_HoaDonBan&for=getAccountInf",
			position: {
				my: "right top",
				at: "right bottom",
			},
			minLength: 2,
			select: function (event, ui) {
				event.preventDefault();
				if (ui.item.address != '' && typeof ui.item.address != 'undefined' && ui.item.address != null) {
					$('#company_name').val(ui.item.label);
				} else {
					$('#iv_account_name').val(ui.item.label);
				}
				$('#company_address').val(ui.item.address);
				$('#iv_email').val(ui.item.email);
				$('#tax_code').val(ui.item.sic_code);
			}
		});
	});

	// Kiểm tra lại giá 
	for (var i = 0; i < $('input[name="psg_luggage_purchase[]"]').length; i++) {
		if ($("#psg_deleted" + i).val() == 0) {
			if ($("#psg_luggage_purchase" + i).val() > 0) {
				calculateLugPurchasePrice(i, 0);
			}
			if ($("#psg_luggage_purchase_inbound" + i).val() > 0) {
				calculateLugPurchasePrice(i, 1);
			}
		}
	}

	// Kiểm tra lại giá mua hành lý nếu có (Nội địa)
	if ($('#ticket_type :selected').val() != '2') {
		for (var i = 0; i < $("select[name=\'bkd_direction[]\']").length; i++) {
			if ($("#bkd_deleted" + i).val() == 0) {
				var is_cal_admin = is_cal_tax = 0;

				// Bổ sung phí admin
				if (unformatNumber($("#bkd_admin_fee_no_vat" + i).val()) == 0) {
					is_cal_admin = 1;
				}

				// Chỉnh sửa thuế nếu tiền thuế = 8% giá cơ bản
				if ((unformatNumber($("#bkd_tax_and_fee" + i).val()) / unformatNumber($("#bkd_unit_price" + i).val())) == 0.08) {
					is_cal_tax = 1;
				}

				calculateLineTotal(i, is_cal_admin, is_cal_tax);
			}
		}
	}

	// Handle flight type
	if ($('#flight_type').val() == '1') {
		$(".airline-wrap__inbound").addClass('d-none');
		$(".date_ticket_issue_inbound").addClass('d-none');
		$(".psg_baggage_line_inbound").addClass('d-none');
	} else {
		$(".airline-wrap__inbound").removeClass('d-none');
		$(".date_ticket_issue_inbound").removeClass('d-none');
		$(".psg_baggage_line_inbound").removeClass('d-none');
	}
	$('#flight_type').change(function () {
		if ($('#flight_type :selected').val() == '1') {
			$('#airline_inbound').val('');
			$('#date_ticket_issue_inbound').val('');

			$(".airline-wrap__inbound").addClass('d-none');
			$(".date_ticket_issue_inbound").addClass('d-none');
			$(".psg_baggage_line_inbound").addClass('d-none');
		}
		else {
			$(".airline-wrap__inbound").removeClass('d-none');
			$(".date_ticket_issue_inbound").removeClass('d-none');
			$(".psg_baggage_line_inbound").removeClass('d-none');
		}
	});

	// Change discount percent
	$('#discount_percent').change(function () {
		calculateTotal();
	});

	// Change total fields
	$('#luggage_fee, #other_fee, #discount_amount, #total_amount').blur(function () {
		calculateTotal();
	});

	// SETUP AUTOCOMPLETE FOR AIRPORT AND AIRLINE
	$('input.ac').on('keydown.autocomplete', function () {
		var type = '';
		if ($(this).hasClass('airline')) {
			type = 'airline';
		} else if ($(this).hasClass('airport')) {
			type = 'airport';
		}
		$(this).autocomplete({
			source: 'index.php?entryPoint=entryPointGetAirportAndAirline&type=' + type,
			minLength: 2,
			select: function (event, ui) {
				var eleid = $(this).attr('id');
				$('#' + eleid).val(ui.item.code);
				event.preventDefault();
			}
		});
	});

	// Add itineraries
	$('#btnItineraryAddRow').click(function () {
		var ln = parseInt($('#iti_row_count').val());
		$('#iti_last_row').before(insertItineraryLine(ln));
		$('.allow-number-only').number(true, 0, dec_sep, num_grp_sep);
		$('#iti_airline_code' + ln).focus();

		// SETUP DATETIME
		setupItineraryCalendars(ln);

		ln++;
		$('#iti_row_count').val(ln);
		$('#lbl_iti_row_count').text(parseInt($('#lbl_iti_row_count').text()) + 1);
	});

	// Add details
	$('#btnDetailAddRow').click(function () {
		var ln = parseInt($('#bkd_row_count').val());
		$('#bkd_last_row').before(insertDetailLine(ln));
		calculateLineTotal(ln);
		$('.allow-number-only').number(true, 0, dec_sep, num_grp_sep);
		$('#bkd_direction' + ln).focus();

		ln++;
		$('#bkd_row_count').val(ln);
		$('#lbl_bkd_row_count').text(parseInt($('#lbl_bkd_row_count').text()) + 1);
	});

	// Add passengers
	$('#btnPassengerAddRow').click(function () {
		let ln = $('input[name="psg_id[]"]').length;

		$('#psg_last_row').before(insertPassengerLine(ln));

		$('.allow-number-only').number(true, 0, dec_sep, num_grp_sep);
		$('#psg_traveller_type' + ln).focus();

		setupPassengerCalendar(ln);

		// Initialize Select2 for the new row
		initOptBagSelect2(ln);

		ln++;
		$('#psg_row_count').val(ln);
		$('#lbl_psg_row_count').text(parseInt($('#lbl_psg_row_count').text()) + 1);
	});

	// Render initial passenger rows from JSON data supplied by PHP.
	// All rows (initial data + new) are rendered using the same single
	// function `insertPassengerLine2` defined below.
	renderInitialPassengers();
	renderInitialItineraries();
	renderInitialDetails();

	// Check is agent
	$('#chk_is_agent').change(function () {
		if ($(this).is(':checked')) {
			$('#is_agent').val(1);
			// $('#agent_id_chosen').show();
			$('#agent_id').next('span').show();
		} else {
			$('#is_agent').val(0);
			$('#agent_id').hide();
			$('#agent_id').val('');
			// $('#agent_id_chosen').hide();
			$('#agent_id').next('span').css({ "display": "none" });;
		}
	});

	// Submit event
	$('#EditView').submit(function () {
		const email_regex = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
		const phone_regex = /^[0-9-+]+$/;

		let current_date = new Date().getTime();
		let action = $('#EditView input:hidden[name="action"]').val();

		let flight_type 	 = $('#flight_type :selected').val();
		let airline_outbound = $('#airline').val();
		let airline_inbound  = $('#airline_inbound').val();

		let contact_name = $.trim($('#contact_name').val());
		let email = $.trim($('#email').val());
		let phone = $.trim($('#phone').val());
		let is_agent = $('#is_agent').val();
		let agent_id = $('#agent_id :selected').val();
		let is_ticket_exported = $('#is_ticket_exported').val();
		let dti_arr = $('#date_ticket_issue_outbound').val().split('-');
		let date_ticket_issue = new Date(dti_arr[1] + '/' + dti_arr[0] + '/' + dti_arr[2]).getTime();

		if (action == 'Save') {
			if (is_ticket_exported == 1 && $.trim($('#date_ticket_issue_outbound').val()) == '') {
				alert('Ngày xuất vé không được trống');
				$('#date_ticket_issue_outbound').focus();
				return false;
			}
			if (is_ticket_exported == 1 && date_ticket_issue > current_date) {
				alert('Ngày xuất vé phải nhỏ hơn hoặc bằng ngày hiện tại');
				$('#date_ticket_issue_outbound').focus();
				return false;
			}
			if (is_agent == '1' && agent_id == '') {
				alert('Vui lòng chọn đại lý');
				$('#agent_id').focus();
				return false;
			}
			if (airline_outbound == '') {
				alert('Vui lòng chọn hãng bay lượt đi');
				$('#airline').focus();
				return false;
			}
			if (flight_type == '0' && airline_inbound == '') {
				alert('Vui lòng chọn hãng bay lượt về');
				$('#airline_inbound').focus();
				return false;
			}
			if (contact_name.length < 2) {
				alert('Người liên hệ không hợp lệ');
				$('#contact_name').focus();
				return false;
			}
			if (email != '' && !email_regex.test(email)) {
				alert('Email không hợp lệ');
				$('#email').focus();
				return false;
			}
			if (phone.length < 10 || phone.length > 12 || !phone_regex.test(phone)) {
				alert('Điện thoại không hợp lệ');
				$('#phone').focus();
				return false;
			}
			if (!checkLineItems()) {
				return false;
			}

			return true;
		}
	});

	// Autocomplete Location Booking (Field city)
	initLocationBookingAutocomplete();

	// Enable popover in available baggage
	var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
	var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
		return new bootstrap.Popover(popoverTriggerEl)
	});
});

// Bỏ dấu tiếng Việt để so khớp autocomplete không phân biệt có dấu/không dấu
function removeVietnameseAccents(str) {
	return (str || '')
		.normalize('NFD')
		.replace(/[\u0300-\u036f]/g, '')
		.replace(/đ/g, 'd')
		.replace(/Đ/g, 'D');
}

function initLocationBookingAutocomplete() {
	const $input = $('#location_booking');
	let cities = [];
	try {
		cities = JSON.parse($input.attr('data-cities') || '[]');
	} catch (e) {
		cities = [];
	}

	$input.autocomplete({
		source: function (request, response) {
			var term = removeVietnameseAccents(request.term).toLowerCase();
			response(cities.filter(function (city) {
				return removeVietnameseAccents(city).toLowerCase().indexOf(term) !== -1;
			}));
		},
		autofocus: true,
		minLength: 1
	});
}

function setupDateCalendar(inputField, button) {
	Calendar.setup({
		inputField: inputField,
		daFormat: cal_date_format,
		button: button,
		singleClick: true,
		dateStr: '',
		step: 1,
		weekNumbers: false
	});
}

function setupItineraryCalendars(ln) {
	setupDateCalendar('iti_departure_date' + ln, 'iti_departure_date_trigger' + ln);
	setupDateCalendar('iti_arrival_date' + ln, 'iti_arrival_date_trigger' + ln);
	setupDateCalendar('iti_time_limit_date' + ln, 'iti_time_limit_date_trigger' + ln);
}

function setupPassengerCalendar(ln) {
	setupDateCalendar('psg_birthday' + ln, 'psg_birthday_trigger' + ln);
}

function insertItineraryLine(ln) {
	var direction_list = $('#direction_list').val();
	var html = '';

	html += `<tr id="iti_line_${ln}">
			<td data-label="Chiều"><select onchange="getAirlineCode(${ln})" id="iti_direction${ln}" name="iti_direction[]" class="w-100">${direction_list}</select></td>
			<td data-label="Mã hãng"><input autocomplete="off" class="ac airline" type="text" maxlength="255" name="iti_airline_code[]" id="iti_airline_code${ln}" value="" /></td>
			<td data-label="Số hiệu"><input type="text" maxlength="10" name="iti_flight_number[]" id="iti_flight_number${ln}" value="" /></td>
			<td data-label="Hạng vé"><input type="text" maxlength="100" name="iti_ticket_class[]" id="iti_ticket_class${ln}" value="" /></td>
			<td data-label="Nơi đi"><input autocomplete="off" class="ac airport" type="text" maxlength="255" name="iti_departure[]" id="iti_departure${ln}" value="" /></td>
			<td data-label="Nơi đến"><input autocomplete="off" class="ac airport" type="text" maxlength="255" name="iti_arrival[]" id="iti_arrival${ln}" value="" /></td>`;

	html += `<td data-label="Ngày giờ đi">
			<div class="d-flex align-items-center gap-1 align-middle">
				<div class="date-wrap d-flex w-65 gap-1">
					<input maxlength="10" type="text" name="iti_departure_date[]" id="iti_departure_date${ln}" value="" />
					<img border="0" class="cursor-pointer" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="iti_departure_date_trigger${ln}" align="absmiddle" />
				</div>
				<div class="time-wrap d-flex flex-fill align-items-center">
					<input class="datetime_h text-center w-25-px" type="text" name="iti_departure_h[]" id="iti_departure_h${ln}" value="00" maxlength="2" />
					<span>:</span>
					<input class="datetime_m text-center w-25-px" type="text" name="iti_departure_m[]" id="iti_departure_m${ln}" value="00" maxlength="2" />
				</div>
			</div>
		</td>
		<td data-label="Ngày giờ đến">
			<div class="d-flex align-items-center gap-1 align-middle">
				<div class="date-wrap d-flex w-65 gap-1">
					<input maxlength="10" type="text" name="iti_arrival_date[]" id="iti_arrival_date${ln}" value="" />
					<img border="0" class="cursor-pointer" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="iti_arrival_date_trigger${ln}" align="absmiddle" />
				</div>
				<div class="time-wrap d-flex flex-fill align-items-center">
					<input class="datetime_h text-center w-25-px" type="text" name="iti_arrival_h[]" id="iti_arrival_h${ln}" value="00" maxlength="2" />
					<span>:</span>
					<input class="datetime_m text-center w-25-px" type="text" name="iti_arrival_m[]" id="iti_arrival_m${ln}" value="00" maxlength="2" />
				</div>
			</div>
		</td>`;

	html += `<td data-label="Hạn giữ chỗ">
		<div class="d-flex align-items-center gap-1 align-middle">
			<div class="date-wrap d-flex w-65 gap-1">
				<input maxlength="10" type="text" name="iti_time_limit_date[]" id="iti_time_limit_date${ln}" value="" />
				<img border="0" class="cursor-pointer" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="iti_time_limit_date_trigger${ln}" align="absmiddle" />
			</div>
			<div class="time-wrap d-flex flex-fill align-items-center">
				<input class="datetime_h text-center w-25-px" type="text" name="iti_time_limit_h[]" id="iti_time_limit_h${ln}" value="00" maxlength="2" />
				<span>:</span>
				<input class="datetime_m text-center w-25-px" type="text" name="iti_time_limit_m[]" id="iti_time_limit_m${ln}" value="00" maxlength="2" />
			</div>
		</div>
	</td>`;

	html += `<td data-label="Giá cơ bản"><input class="allow-number-only text-end" type="text" maxlength="20" name="iti_base_price[]" id="iti_base_price${ln}" value="0" /></td>`;

	html += `<td data-label="Transit" class="align-middle text-center">
		<input type="checkbox" name="iti_is_layover_chk[]" id="iti_is_layover_chk${ln}" onchange="checkActive('iti_is_layover_chk${ln}', 'iti_is_layover${ln}')" />
		<input type="hidden" name="iti_is_layover[]" id="iti_is_layover${ln}" value="0" />
	</td>`;

	html += `<td data-label="Xóa dòng" class="align-middle text-center">
				<button title="Xóa" type="button" class="button-remove-in-edit" onclick="markItineraryRowDeleted(${ln})">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
				</button>
				<input type="hidden" value="0" name="iti_deleted[]" id="iti_deleted${ln}" />
				<input type="hidden" name="iti_detail_id[]" id="iti_detail_id${ln}" value="" />
			</td></tr>`;

	return html;
}

function insertDetailLine(ln) {
	var direction_list = $('#direction_list').val();
	var passenger_type_list = $('#passenger_type_list').val();
	var supplier_list = $('#supplier_list').val();
	var html = '';

	html += `<tr id="bkd_line_${ln}" class="bkd_line fw-semibold">
				<td data-label="Chiều"><select class="w-100" name="bkd_direction[]" id="bkd_direction${ln}" >${direction_list}</select></td>
				<td data-label="Loại HK"><select class="w-100" name="bkd_passenger_type[]" id="bkd_passenger_type${ln}">${passenger_type_list}</select></td>
				<td data-label="SL"><input class="allow-number-only text-center" onblur="calculateLineTotal(${ln})" type="text" name="bkd_quantity[]" id="bkd_quantity${ln}" value="1" maxlength="3" /></td>
				<td data-label="Giá cơ bản"><input class="allow-number-only text-center" onblur="calculateLineTotal(${ln})" onkeyup="calculateLineTotal(${ln}, 0, 1)" type="text" name="bkd_unit_price[]" id="bkd_unit_price${ln}" value="0" maxlength="25" /></td>
				<td data-label="VAT"><input class="allow-number-only text-center" onblur="calculateLineTotal(${ln})" type="text" name="bkd_tax_and_fee[]" id="bkd_tax_and_fee${ln}" value="0" maxlength="25" /></td>
				<td data-label="Phí sân bay"><input class="allow-number-only text-center" onblur="calculateLineTotal(${ln})" type="text" name="bkd_airport_fee[]" id="bkd_airport_fee${ln}" value="0" maxlength="25" /></td>
				<td data-label="Phí admin"><input class="allow-number-only text-center" onblur="calculateLineTotal(${ln}, 1)" type="text" name="bkd_admin_fee[]" id="bkd_admin_fee${ln}" value="0" maxlength="25" /></td>
				<td data-label="Phí dịch vụ"><input class="allow-number-only text-center" onblur="calculateLineTotal(${ln})" type="text" name="bkd_service_fee[]" id="bkd_service_fee${ln}" value="0" maxlength="25" /></td>
				<td data-label="Thành tiền"><input class="allow-number-only text-center" onblur="calculateLineTotal(${ln})" type="text" name="bkd_total_price[]" id="bkd_total_price${ln}" value="0" maxlength="25" /></td>
				<td data-label="Giá mua"><input onblur="calculateTotal()" class="allow-number-only text-center" type="text" name="bkd_total_bought_price[]" id="bkd_total_bought_price${ln}" value="0" maxlength="25" /></td>
				<td data-label="Chiết khấu"><input onblur="calculateLineTotal(${ln})" class="allow-number-only text-center" type="text" name="bkd_supplier_discount[]" id="bkd_supplier_discount${ln}" value="0" maxlength="25" /></td>
				<td data-label="Phí xuất vé"><input onblur="calculateLineTotal(${ln})" class="allow-number-only text-center" type="text" name="bkd_supplier_ticketing_fee[]" id="bkd_supplier_ticketing_fee${ln}" value="0" maxlength="25" /></td>
				<td data-label="NCC"><select class="box-select w-100 bkd_select_supplier" id="bkd_supplier_id${ln}" name="bkd_supplier_id[]"><option value=""></option>${supplier_list}</select></td>
				<td data-label="Xóa dòng" class="align-middle text-center">
					<button title="Xóa" type="button" class="button-remove-in-edit" onclick="markDetailRowDeleted(${ln})" >
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
					</button>
					<input type="hidden" value="0" name="bkd_deleted[]" id="bkd_deleted${ln}" />
					<input type="hidden" name="bkd_detail_id[]" id="bkd_detail_id${ln}" value="" />
				</td>
			</tr>`;

	// thêm dòng phí admin chưa VAT
	html += `<tr id="bkd_admin_line_${ln}">
				<td data-label="Chi tiết phí Admin" colspan="15">
					<div class="addmin-fee-wrap d-flex gap-3 align-items-center">
						<div class="d-flex gap-1 align-items-center admin-fee-not-vat">
							<span class="text-label">Phí admin chưa VAT: </span>
							<input type="text" class="psg_luggage_purchase_input allow-number-only" name="bkd_admin_fee_no_vat[]" id="bkd_admin_fee_no_vat${ln}" onkeyup="calculateRelateAdminFee$(${ln});" onpaste="setTimeout(function(){calculateRelateAdminFee(${ln});}, 10);"/> 
						</div>
						<div class="d-flex gap-1 align-items-center admin-fee-vat">
							<span class="text-label">VAT admin: </span>
							<input type="text" class="psg_luggage_purchase_input allow-number-only" name="bkd_vat_admin[]" id="bkd_vat_admin${ln}" onkeyup="calculateRelateAdminFee(${ln});" onpaste="setTimeout(function(){calculateRelateAdminFee(${ln});}, 10);"/>
						</div>
					</div>
				</td>
			</tr>`;
	return html;
}

function calculateRelateAdminFee(ln, is_vat = 0) {
	var admin_fee_vat = unformatNumber($("#bkd_admin_fee" + ln).val());
	var vat_admin = unformatNumber($("#bkd_vat_admin" + ln).val());
	var admin_fee = unformatNumber($("#bkd_admin_fee_no_vat" + ln).val());
	if (is_vat) {
		$("#bkd_admin_fee_no_vat" + ln).val(admin_fee_vat - vat_admin);
	} else {
		$("#bkd_vat_admin" + ln).val(admin_fee_vat - admin_fee);
	}
}

function insertPassengerLine(ln) {
	let supplier_list = $('#supplier_list').val();
	let passenger_type_list = $('#passenger_type_list').val();
	let passenger_salutation_list = $('#passenger_salutation_list').val();

	// Get baggage options from hidden fields
	let baggageOptionsOutbound = [];
	let baggageOptionsInbound = [];

	try {
		let outboundJson = $('#baggage_options_outbound').val();
		let inboundJson = $('#baggage_options_inbound').val();

		if (outboundJson) {
			baggageOptionsOutbound = JSON.parse(outboundJson);
		}
		if (inboundJson) {
			baggageOptionsInbound = JSON.parse(inboundJson);
		}
	} catch (e) {
		console.error('Error parsing baggage options:', e);
	}

	let html = '';
	/**********  Info line   **********/
	html += `<tr id="psg_line_${ln}" class="psg_line">`;

	// Loại khách hàng
	html += `<td data-label="Loại HK">
		<select name="psg_traveller_type[]" id="psg_traveller_type${ln}" class="w-100">
			${passenger_type_list}
		</select>
	</td>`;

	// Danh xưng
	html += `<td data-label="Danh xưng">
		<select name="psg_salutation[]" id="psg_salutation${ln}" class="w-100">
			${passenger_salutation_list}
		</select>
	</td>`;

	// Họ tên
	html += `<td data-label="Họ tên">
		<input type="text" name="psg_full_name[]" id="psg_full_name${ln}" class="text-start" maxlength="128" />
	</td>`;

	// Ngày sinh
	html += `<td data-label="Ngày sinh">
		<div class="d-flex align-items-center gap-1">
			<input type="text" class="w-80" name="psg_birthday[]" id="psg_birthday${ln}" maxlength="10" />
			<img class="cursor-pointer" border="0" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="psg_birthday_trigger${ln}" align="absmiddle" />
		</div>
	</td>`;

	// CCCD/Passport
	html += `<td data-label="CCCD/Passport">
		<input type="text" name="psg_id_number[]"
			id="psg_id_number${ln}"
			class="text-start"
			maxlength="16"
			style="padding-left:8px !important; letter-spacing:1px;"
		/>
	</td>`;

	// PNR lượt đi
	html += `<td data-label="PNR lượt đi"><input type="text" name="psg_pnr_outbound[]" id="psg_pnr_outbound${ln}" class="text-center" maxlength="30" /></td>`;
	// PNR lượt về
	html += `<td data-label="PNR lượt về"><input type="text" name="psg_pnr_inbound[]" id="psg_pnr_inbound${ln}" class="text-center" maxlength="30" /></td>`;
	// Số vé lượt đi
	html += `<td data-label="Số vé lượt đi"><input type="text" name="psg_eticket_outbound[]" id="psg_eticket_outbound${ln}" class="text-center" maxlength="25" /></td>`;
	// Số vé lượt về
	html += `<td data-label="Số vé lượt về"><input type="text" name="psg_eticket_inbound[]" id="psg_eticket_inbound${ln}" class="text-center" maxlength="25" /></td>`;

	// Nút xóa
	html += `<td data-label="Xóa dòng" class="align-middle text-center">
		<button type="button" title="Xóa" class="button-remove-in-edit" onclick="markPassengerRowDeleted(${ln})" >
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
		</button>
		<input type="hidden" name="psg_deleted[]" id="psg_deleted${ln}" value="0" />
		<input type="hidden" name="psg_id[]" id="psg_id${ln}" value="" />
	</td>`;
	html += '</tr>';

	/**********  Baggages line   **********/
	let flight_type = $('#flight_type').val();
	const rounds = ["outbound", "inbound"];
	rounds.forEach(roundName => {
		const suffix = roundName === "outbound" ? "" : "_inbound";
		const direction = roundName === "outbound" ? 0 : 1;

		// Select appropriate baggage options
		const baggageOptions = roundName === "outbound" ? baggageOptionsOutbound : baggageOptionsInbound;

		// Input names
		const inputNameBagtext = `psg_luggage_purchase_text${suffix}`;
		const inputNameBagPrice = `psg_luggage_purchase${suffix}`;
		const inputNameBagTax = `psg_vat_luggage_purchase${suffix}`;
		const inputNameSupplier = `psg_luggage_supplier${suffix}`;
		const inputNameTicketNum = `psg_eluggage_${roundName}`;
		const inputNameSellingPrice = `psg_luggage_price${suffix}`;
		const inputNameAvaiBagIndex = `psg_luggage_index_${roundName}`;
		const inputNameHandBagIndex = `psg_hand_baggage_${roundName}`;
		// Labels
		const suffixtext = roundName === "outbound" ? "lượt đi" : "lượt về";

		// Build baggage options HTML
		let baggageOptionsHtml = '<option value="">-- Chọn hành lý --</option>';
		if (baggageOptions && Array.isArray(baggageOptions)) {
			baggageOptions.forEach(function (baggage) {
				let description = baggage.description || '';
				let cost = baggage.cost || 0;  // giá mua VAT
				let value = baggage.value || 0;  // giá bán VAT

				if (description) {
					// Bỏ tiền tố "Thêm " và phần trong ngoặc ở cuối để lấy text hiển thị
					let displayText = description
						.replace(/^Thêm\s+/i, '')
						.replace(/\s*\([^)]*\)\s*$/, '')
						.trim();
					let saveValue = displayText; // Giá trị lưu vào DB

					baggageOptionsHtml += `<option value="${escapeHtml(saveValue)}" data-cost="${cost}" data-value="${value}">
						${escapeHtml(displayText)}
					</option>`;
				}
			});
		}

		let classShowHide = flight_type == '1' && roundName == 'inbound' ? 'd-none' : '';
		html += `<tr id="psg_baggage_line_${roundName}_${ln}" class="psg_baggage_line_${roundName} ${classShowHide}">
			<td data-label="${roundName} baggage information" class="row_psg_price" colspan="10">
			<div class="psg_price-wrap d-flex gap-3 align-items-center mb-1">
				<span class="text-label" style="width:155px;">Hành lý xách tay ${suffixtext}:</span>
					<div>
						<input type="text" name="${inputNameHandBagIndex}[]"
							id="${inputNameHandBagIndex}${ln}"
							style="width:80px" maxlength="6" size="6"
						/>
						<button type="button" title="Hướng dẫn nhập liệu" style="border:none; background:none; padding:0;"
							data-bs-toggle="popover"
							data-bs-html="true"
							data-bs-content="Nhập <b>1x23</b> = 1 kiện x 23kg<br>Nhập <b>1T23</b> = 1 kiện tổng 23kg<br>Nhập <b>5</b> = 5 kiện<br>Nhập <b>6kg</b> = 6kg">
							<svg width="18px" height="18px" stroke-width="2.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#a1a1a1"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 9C9 5.49997 14.5 5.5 14.5 9C14.5 11.5 12 10.9999 12 13.9999" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 18.01L12.01 17.9989" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
						</button>
					</div>
				</div>
				<div class="psg_price-wrap d-flex gap-3 align-items-center mb-1">
					<span class="text-label" style="width:155px;">Hành lý có sẵn ${suffixtext}:</span>
					<div>
						<input type="text" name="${inputNameAvaiBagIndex}[]"
							id="${inputNameAvaiBagIndex}${ln}"
							style="width:80px" maxlength="6" size="6"
						/>
						<button type="button" title="Hướng dẫn nhập liệu" style="border:none; background:none; padding:0;"
							data-bs-toggle="popover"
							data-bs-html="true"
							data-bs-content="Nhập <b>1x23</b> = 1 kiện x 23kg<br>Nhập <b>1T23</b> = 1 kiện tổng 23kg<br>Nhập <b>5</b> = 5 kiện<br>Nhập <b>6</b> = 6kg">
							<svg width="18px" height="18px" stroke-width="2.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#a1a1a1"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 9C9 5.49997 14.5 5.5 14.5 9C14.5 11.5 12 10.9999 12 13.9999" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 18.01L12.01 17.9989" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
						</button>
					</div>
				</div>
				<div class="psg_price-wrap d-flex gap-3 align-items-center">
					<div class="col_psg_price col-psg-bag-text">
						<span class="text-label">Hành lý mua thêm ${suffixtext}</span>
						<select name="${inputNameBagtext}[]" id="${inputNameBagtext}${ln}" class="psg_luggage_purchase_select" style="width: 100%; max-width: 400px;" onchange="updateBaggagePriceFromSelect(${ln}, '${roundName}')">${baggageOptionsHtml}</select>
					</div>
					<div class="col_psg_price col-psg-bag-selling-price">
						<span class="text-label">Giá bán (VAT): </span>
						<input type="text" name="${inputNameSellingPrice}[]"
							id="${inputNameSellingPrice}${ln}"
							class="allow-number-only psg_luggage_purchase_input"
							maxlength="12"
						/>
					</div>
					<div class="col_psg_price col-psg-bag-price">
						<span class="text-label">Giá mua (VAT): </span>
						<input type="text" name="${inputNameBagPrice}[]" id="${inputNameBagPrice}${ln}" value="0" class="allow-number-only psg_luggage_purchase_input" maxlength="12" onkeyup="calculateBagPurchasePrice(${ln}, ${direction}); updateTotalBaggageFee();" onpaste="calculateBagPurchasePrice(${ln}, ${direction}); updateTotalBaggageFee();" />
					</div>
					<div class="col_psg_price col-psg-bag-tax">
						<span class="text-label">VAT giá mua: </span>
						<input type="text" name="${inputNameBagTax}[]" id="${inputNameBagTax}${ln}" value="0" class="allow-number-only psg_luggage_purchase_input" maxlength="12" onkeyup="calculateBagPurchasePrice(${ln}, ${direction});" />
					</div>
					<div class="col_psg_price col-psg-bag-supplier">
						<span class="text-label">NCC: </span>
						<select name="${inputNameSupplier}[]" id="${inputNameSupplier}${ln}" class="psg_luggage_purchase_select"><option value=""></option>${supplier_list}</select>
					</div>
					<div class="col_psg_price col-psg-bag-ticketnum">
						<span class="text-label">Số vé HL ${suffixtext}: </span>
						<input type="text" name="${inputNameTicketNum}[]" id="${inputNameTicketNum}${ln}" class="psg_luggage_purchase_input" maxlength="25" size="25" />
					</div>
				</div>
			</td>
		</tr>`;
	});
	return html;
}

function escapeHtml(text) {
	var map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;'
	};
	return text.replace(/[&<>"']/g, function (m) { return map[m]; });
}

function initOptBagSelect2(ln) {
	$(`#psg_luggage_purchase_text${ln}`).select2({width: '100%'});
	$(`#psg_luggage_purchase_text_inbound${ln}`).select2({width: '100%'});
}

function calculateBagPurchasePrice(ln, direction) {
	if (direction == 0) {
		var luggage_purchase = unformatNumber($("#psg_luggage_purchase" + ln).val());
		var lug_no_vat = Math.round(luggage_purchase / 1.08);
		$(`#psg_vat_luggage_purchase${ln}`).val(luggage_purchase - lug_no_vat);
	}
	else if (direction == 1) {
		var luggage_purchase_ib = unformatNumber($("#psg_luggage_purchase_inbound" + ln).val());
		var lug_no_vat_ib = Math.round(luggage_purchase_ib / 1.08);
		$(`#psg_vat_luggage_purchase_inbound${ln}`).val(luggage_purchase_ib - lug_no_vat_ib);
	}
}

function calculateLugPurchasePrice(ln, direction) {
	if (direction == 0) {
		var luggage_purchase = unformatNumber($("#psg_luggage_purchase" + ln).val());
		var lug_no_vat = Math.round(luggage_purchase / 1.08);
		$("#psg_detail_lug_pur_no_vat" + ln).val(lug_no_vat);
		$("#psg_detail_lug_pur_vat" + ln).val(luggage_purchase - lug_no_vat);
	} else if (direction == 1) {
		var luggage_purchase_ib = unformatNumber($("#psg_luggage_purchase_inbound" + ln).val());
		var lug_no_vat_ib = Math.round(luggage_purchase_ib / 1.08);
		$("#psg_detail_lug_pur_ib_no_vat" + ln).val(lug_no_vat_ib);
		$("#psg_detail_lug_pur_ib_vat" + ln).val(luggage_purchase_ib - lug_no_vat_ib);
	}
}

function markItineraryRowDeleted(ln) {
	$('#iti_line_' + ln).hide();
	$('#iti_line_desc_' + ln).hide();
	$('#iti_deleted' + ln).val(1);
	$('#lbl_iti_row_count').text(parseInt($('#lbl_iti_row_count').text()) - 1);
}

function markDetailRowDeleted(ln) {
	$('#bkd_line_' + ln).hide();
	$('#bkd_admin_line_' + ln).hide();
	$('#bkd_deleted' + ln).val(1);
	$('#lbl_bkd_row_count').text(parseInt($('#lbl_bkd_row_count').text()) - 1);
	calculateLineTotal(ln);
}

function markPassengerRowDeleted(ln) {
	$(`#psg_deleted${ln}`).val(1);
	$(`#psg_line_${ln}`).hide();
	$(`#psg_baggage_line_outbound_${ln}`).hide();
	$(`#psg_baggage_line_inbound_${ln}`).hide();

	var luggage_fee = unformatNumber($('#luggage_fee').val());
	var luggage_price_outbound = unformatNumber($(`#psg_luggage_purchase${ln}`).val());
	var luggage_price_inbound = unformatNumber($(`#psg_luggage_purchase_inbound${ln}`).val());
	luggage_fee -= (luggage_price_outbound + luggage_price_inbound);
	$('#luggage_fee').val(luggage_fee);

	$('#lbl_psg_row_count').text(parseInt($('#lbl_psg_row_count').text()) - 1);

	updateRowCount();
	calculateLuggagePrice();
}

// Check ncc
function setPhiXuatVeHNH(ln) {
	var supplier_fee = $(`#bkd_supplier_id${ln} :selected`).val();
	var quantity = unformatNumber($(`#bkd_quantity${ln}`).val());
	
	var supplier_ticketing_fee = 0;
	if (supplier_fee == 'ebdf163a-7b85-30bf-62be-5a4af5a1166c') { // NCC Hong Ngoc Ha (HNH)
		supplier_ticketing_fee = 5000 * quantity; // Phi xuat ve 5k/1ve (07/2020)
	}
	$(`#bkd_supplier_ticketing_fee${ln}`).val(supplier_ticketing_fee);
	calculateLineTotal(ln);
}

function calculateLineTotal(ln, is_cal_admin = 0, is_cal_tax = 0) {
	var ticket_type = $('#ticket_type :selected').val();
	var qty = unformatNumber($('#bkd_quantity' + ln).val());
	var price = unformatNumber($('#bkd_unit_price' + ln).val());
	var tax_fee = unformatNumber($('#bkd_tax_and_fee' + ln).val());
	var admin_fee = unformatNumber($('#bkd_admin_fee' + ln).val());
	var vat_admin = admin_fee_no_vat = 0;

	// chỉ tính vat và phí admin đối với vé nội địa
	if (ticket_type != '2') {
		if (is_cal_tax) {
			tax_fee = price * 8 / 100;
		}

		// nếu là thuế của VNA hay VNP thì làm tròn từ ngày 06-04-2022
		var bk_create_time = $("#bk_date_entered").val() * 1000;
		// lấy hãng bay lượt đi / về
		var airline_inf = getAirLineInf();
		// làm tròn thuế lượt đi / về
		if (
			((airline_inf[0] == 'VNA' || airline_inf[0] == 'VNP' || airline_inf[0] == 'BBA') && bk_create_time >= Date.parse('2022-04-06 00:00:00') && $("#bkd_direction" + ln).val() == 0) // lượt đi
			|| ((airline_inf[1] == 'VNA' || airline_inf[1] == 'VNP' || airline_inf[1] == 'BBA') && bk_create_time >= Date.parse('2022-04-06 00:00:00') && $("#bkd_direction" + ln).val() == 1) // lượt về
		) {
			if (is_cal_tax) {
				tax_fee = Math.ceil(tax_fee / 1000) * 1000;
			}
			vat_admin = 0;
		}

		if ((airline_inf[0] != 'VNA' && airline_inf[0] != 'VNP' && $("#bkd_direction" + ln).val() == 0) || (airline_inf[1] != 'VNA' && airline_inf[1] != 'VNP' && $("#bkd_direction" + ln).val() == 1) && is_cal_admin) {
			vat_admin = Math.round(admin_fee / 1.08 * 0.08);
		}
	}

	var service_fee = unformatNumber($('#bkd_service_fee' + ln).val());
	var airport_fee = unformatNumber($('#bkd_airport_fee' + ln).val());
	var supplier_discount = unformatNumber($('#bkd_supplier_discount' + ln).val());
	var supplier_ticketing_fee = unformatNumber($('#bkd_supplier_ticketing_fee' + ln).val());
	var total_price = 0;
	var total_bought_price = 0;

	total_bought_price = qty * (price + tax_fee + admin_fee + airport_fee);
	total_price = qty * (price + tax_fee + service_fee + admin_fee + airport_fee);

	// Chiết khấu
	if (supplier_discount != 0) {
		total_bought_price = Math.abs(total_bought_price - supplier_discount);
	}

	// Phí xuất vé
	if (supplier_ticketing_fee != 0) {
		total_bought_price += supplier_ticketing_fee;
	}

	$('#bkd_quantity' + ln).val(qty);
	$('#bkd_unit_price' + ln).val(price);
	$('#bkd_airport_fee' + ln).val(airport_fee);
	$('#bkd_admin_fee' + ln).val(admin_fee);
	$('#bkd_service_fee' + ln).val(service_fee);
	$('#bkd_total_price' + ln).val(total_price);
	$('#bkd_total_bought_price' + ln).val(total_bought_price);
	$('#bkd_tax_and_fee' + ln).val(tax_fee);
	if (is_cal_admin) {
		$('#bkd_vat_admin' + ln).val(vat_admin);
		$('#bkd_admin_fee_no_vat' + ln).val(admin_fee - vat_admin);
	}
	calculateTotal();
}

function calculateTotal() {
	var arr = document.getElementsByName('bkd_deleted[]');
	var qty = document.getElementsByName('bkd_quantity[]');
	var total_price = document.getElementsByName('bkd_total_price[]');
	var total_bought_price = document.getElementsByName('bkd_total_bought_price[]');
	var total_qty = 0;
	var subtotal_amt = 0;
	var total_bought_amt = 0;
	for (var i = 0; i < arr.length; i++) {
		if (arr[i].value == '0') {
			total_qty += unformatNumber(qty[i].value);
			subtotal_amt += unformatNumber(total_price[i].value);
			total_bought_amt += unformatNumber(total_bought_price[i].value);
		}
	}

	var luggage_fee = unformatNumber($('#luggage_fee').val());
	var other_fee = unformatNumber($('#other_fee').val());
	var total_amount = subtotal_amt + luggage_fee + other_fee;

	var discount_percent = unformatNumber($('#discount_percent :selected').val());
	var discount_amount = unformatNumber($('#discount_amount').val());
	if (discount_percent > 0) {
		discount_amount = total_amount * discount_percent / 100;
	}
	total_amount -= discount_amount;

	$('#discount_amount').val(discount_amount);
	$('#total_qty').val(formatNumber(total_qty));
	$('#subtotal_amount').val(formatNumber(subtotal_amt));
	$('#total_bought_amount').val(formatNumber(total_bought_amt));
	$('#total_amount').val(total_amount);
}

function calculateLuggagePrice() {
	var luggage_ob_price_lines = $("select[name='psg_luggage_price[]']");
	var luggage_index_price = vja_luggage_index_list;
	var luggage_fee = 0;
	luggage_ob_price_lines.each(function (idx, el) {
		if ($("#psg_deleted" + idx).val() == 0) {
			var luggage_ob_price = unformatNumber($(this).val());
			var luggage_ib_price = unformatNumber($("#psg_luggage_price_inbound" + idx).val());
			if (luggage_ob_price < 1000) {
				luggage_ob_price = luggage_index_price[luggage_ob_price] || 0;
			}
			if (luggage_ib_price < 1000) {
				luggage_ib_price = luggage_index_price[luggage_ib_price] || 0;
			}
			luggage_fee += parseInt(luggage_ob_price) + parseInt(luggage_ib_price);
		}
	});

	$('#luggage_fee').val(formatNumber(luggage_fee));
	calculateTotal();
}

function checkActive(id_chk, id_hidden) {
	if ($('#' + id_chk).is(':checked')) {
		$('#' + id_hidden).val(1);
	} else {
		$('#' + id_hidden).val(0);
	}
}

function getAirLineInf() {
	var iti_airline = {};

	for (var i = 0; i < $('.airline').length; i++) {
		var airline_name = $("#iti_airline_code" + i).val();
		if ($("#iti_direction" + i).val() == 0 && $("#iti_deleted" + i).val() == 0) {
			if (!iti_airline.hasOwnProperty(0)) {
				iti_airline[0] = airline_name;
			} else continue;
		} else if ($("#iti_direction" + i).val() == 1 && $("#iti_deleted" + i).val() == 0) {
			if (!iti_airline.hasOwnProperty(1)) {
				iti_airline[1] = airline_name;
			} else continue;
		}
	}
	return iti_airline;
}

function getAirlineCode(ln) {
	let direction = $(`#iti_direction${ln} :selected`).val();
	let airline_code = '';
	if (direction == 1) {
		airline_code = $('#airline_inbound').val();
	}
	else {
		airline_code = $('#airline').val();
	}
	$('#iti_airline_code' + ln).val(airline_code);
}

function checkLineItems() {
	var iti_arr = document.getElementsByName('iti_deleted[]');
	if (iti_arr.length > 0) {
		for (var i = 0; i < iti_arr.length; i++) {
			if (iti_arr[i].value == '0') {
				let row = $(iti_arr[i]).closest('tr');
				let rowId = row.attr('id');
				let lineNum = rowId.replace('iti_line_', '');

				const airlineCodeEl = $(`#iti_airline_code${lineNum}`);
				if (airlineCodeEl.val().trim() == '') {
					showToastWarning('Mã hãng hành trình không hợp lệ');
					airlineCodeEl.focus();
					airlineCodeEl.select();
					return false;
				}

				const flightNoEl = $(`#iti_flight_number${lineNum}`);
				if (flightNoEl.val().trim() == '') {
					showToastWarning('Số hiệu chuyến bay không hợp lệ');
					flightNoEl.focus();
					flightNoEl.select();
					return false;
				}
				const departureEl = $(`#iti_departure${lineNum}`);
				if (departureEl.val().trim() == '') {
					showToastWarning('Nơi đi không hợp lệ');
					departureEl.focus();
					departureEl.select();
					return false;
				}

				const arrivalEl = $(`#iti_arrival${lineNum}`);
				if (arrivalEl.val().trim() == '') {
					showToastWarning('Nơi đến không hợp lệ');
					arrivalEl.focus();
					arrivalEl.select();
					return false;
				}

				const departureHourEl = $(`#iti_departure_h${lineNum}`);
				if (parseInt(departureHourEl.val()) < 0 || parseInt(departureHourEl.val()) > 23) {
					showToastWarning('Thời gian đi không hợp lệ');
					departureHourEl.focus();
					departureHourEl.select();
					return false;
				}

				const departureMinuteEl = $(`#iti_departure_m${lineNum}`);
				if (parseInt(departureMinuteEl.val()) < 0 || parseInt(departureMinuteEl.val()) > 59) {
					showToastWarning('Thời gian đi không hợp lệ');
					departureMinuteEl.focus();
					departureMinuteEl.select();
					return false;
				}

				const arrivalHourEl = $(`#iti_arrival_h${lineNum}`);
				if (parseInt(arrivalHourEl.val()) < 0 || parseInt(arrivalHourEl.val()) > 23) {
					showToastWarning('Thời gian đến không hợp lệ');
					arrivalHourEl.focus();
					arrivalHourEl.select();
					return false;
				}
				
				const arrivalMinuteEl = $(`#iti_arrival_m${lineNum}`);
				if (parseInt(arrivalMinuteEl.val()) < 0 || parseInt(arrivalMinuteEl.val()) > 59) {
					showToastWarning('Thời gian đến không hợp lệ');
					arrivalMinuteEl.focus();
					arrivalMinuteEl.select();
					return false;
				}
			}
		}
	}

	const bookingStatus = $('#booking_status').val();
	const flightType = $('#flight_type').val();

	var bkd_arr = document.getElementsByName('bkd_deleted[]');
	if (bkd_arr.length > 0) {
		var bkd_has_supplier_inb = [
			['0', 0],
			['1', 0],
			['2', 0]
		];
		var bkd_has_supplier_outb = [
			['0', 0],
			['1', 0],
			['2', 0]
		];

		for (var i = 0; i < bkd_arr.length; i++) {
			const quantityEl = $('#bkd_quantity' + i);
			if (bkd_arr[i].value == '0' && unformatNumber(quantityEl.val()) <= 0) {
				showToastWarning('Số lượng phải lớn hơn 0!');

				quantityEl.focus();
				quantityEl.select();
				return false;
			}

			const totalPriceEl = $('#bkd_total_price' + i);
			if (bkd_arr[i].value == '0' && unformatNumber(totalPriceEl.val()) <= 0) {
				showToastWarning('Thành tiền phải lớn hơn 0!');

				totalPriceEl.focus();
				totalPriceEl.select();
				return false;
			}

			// booking ở trạng thái xác nhận
			// lấy số lượng chi tiết vé đã nhập NCC
			if (bookingStatus == '3') {
				if (bkd_arr[i].value == '0') {
					const supplierIdVal = $('#bkd_supplier_id' + i).val();
					const directionVal = $('#bkd_direction' + i).val();
					const passengerTypeVal = $('#bkd_passenger_type' + i).val();

					// lượt đi
					if (supplierIdVal != '' && directionVal == '0') {
						bkd_has_supplier_outb[passengerTypeVal] += parseInt(quantityEl.val());
					}

					// lượt về nếu là khứ hồi
					if (flightType == '0') {
						if (supplierIdVal != '' && directionVal == '1') {
							bkd_has_supplier_inb[passengerTypeVal] += parseInt(quantityEl.val());
						}
					}
				}
			}
		}
	}

	var psg_arr = document.getElementsByName('psg_deleted[]');
	if (psg_arr.length > 0) {
		var psg_has_eticket_outb = [
			['0', 0],
			['1', 0],
			['2', 0]
		];
		var psg_has_eticket_inb = [
			['0', 0],
			['1', 0],
			['2', 0]
		];
		for (var i = 0; i < psg_arr.length; i++) {
			const fullNameEl = $('#psg_full_name' + i);
			if (psg_arr[i].value == '0' && ($.trim(fullNameEl.val()) == '' || $.trim(fullNameEl.val()).length < 5)) {
				showToastWarning('Họ tên hành khách không hợp lệ');

				fullNameEl.focus();
				fullNameEl.select();
				return false;
			}

			const birthdayEl = $('#psg_birthday' + i);
			if (psg_arr[i].value == '0' && !isValidDateBirthDay(birthdayEl.val())) {
				showToastWarning('Ngày sinh không hợp lệ');
				birthdayEl.focus();
				birthdayEl.select();
				return false;
			}

			const pnrOutboundEl = $('#psg_pnr_outbound' + i);
			if (psg_arr[i].value == '0' && $.trim(pnrOutboundEl.val()) != '' && $.trim(pnrOutboundEl.val()).length < 5) {
				showToastWarning('PNR chiều đi không hợp lệ');
				pnrOutboundEl.focus();
				pnrOutboundEl.select();
				return false;
			}

			const pnrInboundEl = $('#psg_pnr_inbound' + i);
			if (psg_arr[i].value == '0' && $.trim(pnrInboundEl.val()) != '' && $.trim(pnrInboundEl.val()).length < 5) {
				showToastWarning('PNR chiều về không hợp lệ');
				pnrInboundEl.focus();
				pnrInboundEl.select();
				return false;
			}

			const eticketOutboundEl = $('#psg_eticket_outbound' + i);
			if (psg_arr[i].value == '0' && $.trim(eticketOutboundEl.val()) != '' && $.trim(eticketOutboundEl.val()).length < 5) {
				showToastWarning('Số vé chiều đi không hợp lệ');
				eticketOutboundEl.focus();
				eticketOutboundEl.select();
				return false;
			}

			const eticketInboundEl = $('#psg_eticket_inbound' + i);
			if (psg_arr[i].value == '0' && $.trim(eticketInboundEl.val()) != '' && $.trim(eticketInboundEl.val()).length < 5) {
				showToastWarning('Số vé chiều về không hợp lệ');
				eticketInboundEl.focus();
				eticketInboundEl.select();
				return false;
			}

			// Booking ở trạng thái xác nhận, lấy số lượng hành khách có code vé
			if (bookingStatus == '3') {
				if (psg_arr[i].value == '0') {
					const travellerTypeVal = $('#psg_traveller_type' + i).val();

					// Lượt đi
					if (eticketOutboundEl.val() != '') {
						psg_has_eticket_outb[travellerTypeVal] += 1;
					}

					// Lượt về
					if (flightType == '0') {
						if (eticketInboundEl.val() != '') {
							psg_has_eticket_inb[travellerTypeVal] += 1;
						}
					}
				}
			}
		}

		// Kiểm tra sl nhà cung cấp tương ứng với số vé của hành khách lượt đi
		if ((psg_has_eticket_outb[0] > bkd_has_supplier_outb[0] || psg_has_eticket_outb[1] > bkd_has_supplier_outb[1] || psg_has_eticket_outb[2] > bkd_has_supplier_outb[2] || psg_has_eticket_inb[0] > bkd_has_supplier_inb[0] || psg_has_eticket_inb[1] > bkd_has_supplier_inb[1] || psg_has_eticket_inb[2] > bkd_has_supplier_inb[2]) && psg_has_eticket_outb[0] > 0) {
			let text_warning = 'Xuất vé lượt nào vui lòng chọn nhà cung cấp tương ứng cho lượt đó';
			showToastWarning(text_warning);
			return false;
		}
	}

	return true;
}

function isValidDateBirthDay(date) {
	if (date.length == 0) {
		return true;
	}

	const dateParts = date.split('-');

	const [day, month, year] = dateParts;

	const currentYear = new Date().getFullYear();
	if (year < 1900 || year > currentYear) {
		return false;
	}

	const daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

	if ((year % 4 === 0 && year % 100 !== 0) || year % 400 === 0) {
		daysInMonth[1] = 29; // Tháng 2 có 29 ngày trong năm nhuận
	}

	if (month < 1 || month > 12 || day < 1 || day > daysInMonth[month - 1]) {
		return false;
	}

	const today = new Date();
	const birthDate = new Date(`${year}-${month}-${day}`);

	if (birthDate > today) {
		return false; // Nếu ngày sinh sau ngày hôm nay thì không hợp lệ
	}

	return true;
}

function updateBaggagePriceFromSelect(rowIndex, direction) {
	var suffix = direction === 'outbound' ? '' : '_inbound';
	var selectId = 'psg_luggage_purchase_text' + suffix + rowIndex;
	var sellingPriceId = 'psg_luggage_price' + suffix + rowIndex;
	var purchasePriceId = 'psg_luggage_purchase' + suffix + rowIndex;
	var vatPriceId = 'psg_vat_luggage_purchase' + suffix + rowIndex;

	var selectedOption = $('#' + selectId + ' option:selected');
	var selectedValue = selectedOption.val();

	// Reset nếu chọn "-- Chọn hành lý --"
	if (!selectedValue) {
		$('#' + sellingPriceId).val('');
		$('#' + purchasePriceId).val('0');
		$('#' + vatPriceId).val('0');
		updateTotalBaggageFee();
		return;
	}

	var cost = parseFloat(selectedOption.data('cost')) || 0; // giá mua VAT
	var value = parseFloat(selectedOption.data('value')) || 0; // giá bán VAT

	// Set giá bán (VAT) — thu của khách
	$('#' + sellingPriceId).val(formatNumber(value));

	// Set giá mua (VAT) — trả cho NCC
	$('#' + purchasePriceId).val(formatNumber(cost));

	// Tính VAT giá mua tự động
	var directionNum = direction === 'outbound' ? 0 : 1;
	calculateBagPurchasePrice(rowIndex, directionNum);

	updateTotalBaggageFee();
}

function updateTotalBaggageFee() {
    var total = 0;
    var rowCount = parseInt($('#psg_row_count').val()) || 0;

    for (var i = 0; i < rowCount; i++) {
        if ($('#psg_deleted' + i).val() == '1') continue;

        // Giá bán hành lý lượt đi
        var ob = unformatNumber($('#psg_luggage_price' + i).val()) || 0;
        // Giá bán hành lý lượt về
        var ib = unformatNumber($('#psg_luggage_price_inbound' + i).val()) || 0;
        total += ob + ib;
    }

    // Dùng số nguyên để tránh float precision
    $('#luggage_fee').val(Math.round(total));
    calculateTotal();
}

function formatNumber(num) {
	if (!num) return '';
	return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function updateRowCount() {
	var rowCount = parseInt($('#psg_row_count').val()) || 0;
	var activeCount = 0;

	for (var i = 0; i < rowCount; i++) {
		var isDeleted = $('#psg_deleted' + i).val();
		if (isDeleted != '1') {
			activeCount++;
		}
	}

	$('#lbl_psg_row_count').text(activeCount);
}

/**
 * Render initial passenger rows from JSON data supplied by PHP.
 *
 * PHP (`populateLinePassengers` in view.edit.php) outputs passenger data
 * as JSON via a hidden input `#psg_passengers_data_json`. This function
 * reads that JSON, then for each passenger it uses the SAME single
 * rendering function `insertPassengerLine(ln)` that the "Thêm dòng"
 * button uses. After inserting the row, it populates the values from
 * the data object (so initial data is shown the same way as new rows
 * are added).
 */
function renderInitialPassengers() {
    var jsonStr = '';
    try {
        jsonStr = $('#psg_passengers_data_json').val() || '';
    } catch (e) {
        jsonStr = '';
    }
    if (!jsonStr) return;

    var passengers = [];
    try {
        passengers = JSON.parse(jsonStr);
    } catch (e) {
        console.error('Error parsing passengers JSON:', e);
        return;
    }
    if (!Array.isArray(passengers) || passengers.length === 0) return;

    for (let i = 0; i < passengers.length; i++) {
        let p = passengers[i];

		// Render HTML elements
        $('#psg_tbody').append(insertPassengerLine(i));
		
		// Assign values
        if (p.id !== undefined && p.id !== null) $(`#psg_id${i}`).val(p.id);
        if (p.type !== undefined && p.type !== null) $(`#psg_traveller_type${i}`).val(String(p.type));
        if (p.salutation !== undefined && p.salutation !== null) $(`#psg_salutation${i}`).val(String(p.salutation));
        if (p.name) $(`#psg_full_name${i}`).val(p.name);
        if (p.birthday) $(`#psg_birthday${i}`).val(p.birthday);
        if (p.id_number) $(`#psg_id_number${i}`).val(p.id_number);
        if (p.pnr_outbound) $(`#psg_pnr_outbound${i}`).val(p.pnr_outbound);
        if (p.pnr_inbound) $(`#psg_pnr_inbound${i}`).val(p.pnr_inbound);
        if (p.eticket_outbound) $(`#psg_eticket_outbound${i}`).val(p.eticket_outbound);
        if (p.eticket_inbound) $(`#psg_eticket_inbound${i}`).val(p.eticket_inbound);
		
		// Available hand baggage
		if (p.hand_baggage_outbound) $(`#psg_hand_baggage_outbound${i}`).val(p.hand_baggage_outbound);
        if (p.hand_baggage_inbound)  $(`#psg_hand_baggage_inbound${i}`).val(p.hand_baggage_inbound);

		// Available checked baggage
        if (p.luggage_index_outbound) $(`#psg_luggage_index_outbound${i}`).val(p.luggage_index_outbound);
        if (p.luggage_index_inbound)  $(`#psg_luggage_index_inbound${i}`).val(p.luggage_index_inbound);

		// Selling price
        if (p.luggage_price) $(`#psg_luggage_price${i}`).val(formatNumber(p.luggage_price));
        if (p.luggage_price_inbound) $(`#psg_luggage_price_inbound${i}`).val(formatNumber(p.luggage_price_inbound));
		// Purchase price
        if (p.luggage_purchase) $(`#psg_luggage_purchase${i}`).val(formatNumber(p.luggage_purchase));
        if (p.luggage_purchase_inbound) $(`#psg_luggage_purchase_inbound${i}`).val(formatNumber(p.luggage_purchase_inbound));
		// VAT of purchase price
        if (p.vat_luggage_purchase !== undefined && p.vat_luggage_purchase !== null)
            $(`#psg_vat_luggage_purchase${i}`).val(p.vat_luggage_purchase);
        if (p.vat_luggage_purchase_inbound !== undefined && p.vat_luggage_purchase_inbound !== null)
            $(`#psg_vat_luggage_purchase_inbound${i}`).val(p.vat_luggage_purchase_inbound);
		// Supplier
        if (p.supplier_id) $(`#psg_luggage_supplier${i}`).val(p.supplier_id);
        if (p.supplier_inbound_id) $(`#psg_luggage_supplier_inbound${i}`).val(p.supplier_inbound_id);
		// Baggage ticket number
		if (p.eluggage_outbound) $(`#psg_eluggage_outbound${i}`).val(p.eluggage_outbound);
        if (p.eluggage_inbound) $(`#psg_eluggage_inbound${i}`).val(p.eluggage_inbound);

        // Re-apply number formatting
        $('.allow-number-only').number(true, 0, dec_sep, num_grp_sep);

        // Calendar
        setupPassengerCalendar(i);

        // ── Populate baggage text + Select2 ──────────────────────────

        // Lượt đi
        if (p.luggage_purchase_text) {
            let $selOb = $(`#psg_luggage_purchase_text${i}`);
            if ($selOb.find(`option[value="${p.luggage_purchase_text}"]`).length === 0) {
                let cost = p.luggage_purchase || 0;  // giá mua VAT
                let value = p.luggage_price || 0;  // giá bán VAT
                var newOpt = `<option
                    value="${escapeHtml(p.luggage_purchase_text)}"
                    data-cost="${cost}"
                    data-value="${value}"
                >${escapeHtml(p.luggage_purchase_text)} (Tùy chỉnh)</option>`;
                $selOb.append(newOpt);
            }
            $selOb.val(p.luggage_purchase_text);
        }

        // Lượt về
        if (p.luggage_purchase_text_inbound) {
            var $selIb = $(`#psg_luggage_purchase_text_inbound${i}`);
            if ($selIb.find(`option[value="${p.luggage_purchase_text_inbound}"]`).length === 0) {
                let cost = p.luggage_purchase_inbound || 0;  // giá mua VAT
                let value = p.luggage_price_inbound || 0;  // giá bán VAT
                var newOptIb = `<option
                    value="${escapeHtml(p.luggage_purchase_text_inbound)}"
                    data-cost="${cost}"
                    data-value="${value}"
                >${escapeHtml(p.luggage_purchase_text_inbound)} (Tùy chỉnh)</option>`;
                $selIb.append(newOptIb);
            }
            $selIb.val(p.luggage_purchase_text_inbound);
        }

        // Khởi tạo Select2 SAU khi đã gán value cho <select> gốc
        initOptBagSelect2(i);
    }

    // Cập nhật tổng phí hành lý sau khi render xong tất cả rows
    updateTotalBaggageFee();
}

/**
 * Render initial itinerary rows from JSON data supplied by PHP.
 *
 * PHP (`populateLineItineraries` in view.edit.php) outputs itinerary data
 * as JSON via a hidden input `#iti_data_json`. This function reads that
 * JSON, then for each itinerary it uses the SAME single rendering function
 * `insertItineraryLine(ln)` that the "Thêm dòng" button uses. After
 * inserting the row, it populates the values from the data object.
 */
function renderInitialItineraries() {
	var jsonStr = '';
	try { jsonStr = $('#iti_data_json').val() || ''; } catch (e) { jsonStr = ''; }
	if (!jsonStr) return;

	var items = [];
	try { items = JSON.parse(jsonStr); } catch (e) { console.error('Error parsing itineraries JSON:', e); return; }
	if (!Array.isArray(items) || items.length === 0) return;

	for (var i = 0; i < items.length; i++) {
		var p = items[i];
		// Use the SAME single function to render this row
		$('#iti_tbody').append(insertItineraryLine(i));

		if (p.id !== undefined && p.id !== null && p.id !== '') {
			$('#iti_detail_id' + i).val(p.id);
		}
		if (p.direction !== undefined && p.direction !== null) {
			$('#iti_direction' + i).val(String(p.direction));
		}
		if (p.airline_code) $('#iti_airline_code' + i).val(p.airline_code);
		if (p.flight_number) $('#iti_flight_number' + i).val(p.flight_number);
		if (p.ticket_class) {
			// BBA has a select for ticket_class; for others it's a text input
			var $tc = $('#iti_ticket_class' + i);
			if ($tc.length) $tc.val(p.ticket_class);
		}
		if (p.departure) $('#iti_departure' + i).val(p.departure);
		if (p.arrival) $('#iti_arrival' + i).val(p.arrival);
		if (p.departure_date) $('#iti_departure_date' + i).val(p.departure_date);
		if (p.departure_h) $('#iti_departure_h' + i).val(p.departure_h);
		if (p.departure_m) $('#iti_departure_m' + i).val(p.departure_m);
		if (p.arrival_date) $('#iti_arrival_date' + i).val(p.arrival_date);
		if (p.arrival_h) $('#iti_arrival_h' + i).val(p.arrival_h);
		if (p.arrival_m) $('#iti_arrival_m' + i).val(p.arrival_m);
		if (p.time_limit_date) $('#iti_time_limit_date' + i).val(p.time_limit_date);
		if (p.time_limit_h) $('#iti_time_limit_h' + i).val(p.time_limit_h);
		if (p.time_limit_m) $('#iti_time_limit_m' + i).val(p.time_limit_m);
		if (p.base_price !== undefined && p.base_price !== null) {
			$('#iti_base_price' + i).val(formatNumber(p.base_price));
		}
		if (p.is_layover !== undefined && p.is_layover !== null && p.is_layover == 1) {
			var $chk = $('#iti_is_layover_chk' + i);
			if ($chk.length) $chk.prop('checked', true);
			var $hid = $('#iti_is_layover' + i);
			if ($hid.length) $hid.val(1);
		}

		// Set up calendars
		setupItineraryCalendars(i);
	}

	// Re-apply number formatting to the new inputs
	$('.allow-number-only').number(true, 0, dec_sep, num_grp_sep);
}

/**
 * Render initial ticket-detail rows from JSON data supplied by PHP.
 *
 * PHP (`populateLineDetails` in view.edit.php) outputs detail data
 * as JSON via a hidden input `#bkd_data_json`. This function reads that
 * JSON, then for each detail it uses the SAME single rendering function
 * `insertDetailLine(ln)` that the "Thêm dòng" button uses. After
 * inserting the row, it populates the values from the data object.
 */
function renderInitialDetails() {
	var jsonStr = '';
	try { jsonStr = $('#bkd_data_json').val() || ''; } catch (e) { jsonStr = ''; }
	if (!jsonStr) return;

	var items = [];
	try { items = JSON.parse(jsonStr); } catch (e) { console.error('Error parsing details JSON:', e); return; }
	if (!Array.isArray(items) || items.length === 0) return;

	for (var i = 0; i < items.length; i++) {
		var p = items[i];

		// Use the SAME single function to render this row (line + admin line)
		$('#bkd_tbody').append(insertDetailLine(i));

		if (p.id !== undefined && p.id !== null && p.id !== '') {
			$('#bkd_detail_id' + i).val(p.id);
		}
		if (p.direction !== undefined && p.direction !== null) {
			$('#bkd_direction' + i).val(String(p.direction));
		}
		if (p.passenger_type !== undefined && p.passenger_type !== null) {
			$('#bkd_passenger_type' + i).val(String(p.passenger_type));
		}
		if (p.quantity !== undefined && p.quantity !== null) {
			$('#bkd_quantity' + i).val(p.quantity);
		}
		if (p.unit_price !== undefined && p.unit_price !== null) {
			$('#bkd_unit_price' + i).val(formatNumber(p.unit_price));
		}
		if (p.tax_and_fee !== undefined && p.tax_and_fee !== null) {
			$('#bkd_tax_and_fee' + i).val(formatNumber(p.tax_and_fee));
		}
		if (p.airport_fee !== undefined && p.airport_fee !== null) {
			$('#bkd_airport_fee' + i).val(formatNumber(p.airport_fee));
		}
		if (p.admin_fee !== undefined && p.admin_fee !== null) {
			$('#bkd_admin_fee' + i).val(formatNumber(p.admin_fee));
		}
		if (p.service_fee !== undefined && p.service_fee !== null) {
			$('#bkd_service_fee' + i).val(formatNumber(p.service_fee));
		}
		if (p.total_price !== undefined && p.total_price !== null) {
			$('#bkd_total_price' + i).val(formatNumber(p.total_price));
		}
		if (p.total_bought_price !== undefined && p.total_bought_price !== null) {
			$('#bkd_total_bought_price' + i).val(formatNumber(p.total_bought_price));
		}
		if (p.supplier_discount !== undefined && p.supplier_discount !== null) {
			$('#bkd_supplier_discount' + i).val(formatNumber(p.supplier_discount));
		}
		if (p.fee_bought !== undefined && p.fee_bought !== null) {
			$('#bkd_supplier_ticketing_fee' + i).val(formatNumber(p.fee_bought));
		}
		if (p.supplier_id) $('#bkd_supplier_id' + i).val(p.supplier_id);
		if (p.admin_fee_no_vat !== undefined && p.admin_fee_no_vat !== null) {
			$('#bkd_admin_fee_no_vat' + i).val(formatNumber(p.admin_fee_no_vat));
		}
		if (p.vat_admin !== undefined && p.vat_admin !== null) {
			$('#bkd_vat_admin' + i).val(formatNumber(p.vat_admin));
		}

		// Re-compute totals for this row
		calculateLineTotal(i);

		// Initialize select2 for the supplier select
		try { $('#bkd_supplier_id' + i).select2({ width: '100%' }); } catch (e) { }
	}

	// Re-apply number formatting
	$('.allow-number-only').number(true, 0, dec_sep, num_grp_sep);
}