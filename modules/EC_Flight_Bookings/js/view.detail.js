$(document).ready(function () {

	// Hover button RECALL
	$(document).on('mouseenter', '.btn-calling--wrap', function () {
		$(this).addClass('active');
		$(this).children('.box-list--calling').addClass('show');
		$(this).children('.recall-link').removeClass('collapsed');
	});

	$(document).on('mouseleave', '.btn-calling--wrap', function () {
		$(this).removeClass('active');
		$(this).children('.box-list--calling').removeClass('show');
		$(this).children('.recall-link').addClass('collapsed');
	});

	// XEM DANH SÁCH BOOKING CỦA CONTACTS
	$('.card-contact-footer').click(function () {
		let contact_id = $(this).attr('contact_id');
		let booking_id = $(this).attr('booking_id');

		if (contact_id.length > 0) {
			$.ajax({
				cache: false,
				type: 'post',
				data: {
					contact_id: contact_id,
					booking_id: booking_id,
					for: "showHistoryBookingContact"
				},
				async: false,
				url: 'index.php?entryPoint=entryPointFlightBookings',
				beforeSend: function () {
					$('.container-waiting').show();
				},
				success: function (output) {
					$('.container-waiting').hide();
					$('#dialog-history-bookings').html(output);
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					console.error(XMLHttpRequest);
					console.error("Status: " + textStatus);
					console.error("Error: " + errorThrown);
				}
			});
		}
	});

	// Xem danh sách cuộc gọi
	$('#view-history-calls').click(function () {
		$('#dialog-view-history-calls').dialog({
			minHeight: 200,
			width: 1000,
			modal: true,
			resizable: false,
		});
	});

	$('#dialog-view-history-calls').on('dialogopen', function (event, ui) {
		let call_phone = $('#view-history-calls').attr('call_phone');
		let call_id_booking = $('#view-history-calls').attr('call_id_booking');

		if (call_phone != '' && call_id_booking != '') {
			$.ajax({
				cache: false,
				type: 'post',
				data: 'call_phone=' + call_phone + '&call_id_booking=' + call_id_booking,
				async: false,
				url: 'index.php?entryPoint=entryPointCheckCallsHistory',
				success: function (output) {
					$('#dialog-view-history-calls').html(output);
				}
			});
		}
	});

	// Sửa thông tin yêu cầu xuất hoá đơn
	$(document).on('click', '#btnCreateInvoice', function () {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: {
				booking: $("form[name='DetailView']>input[name='record']").val(),
				for: "getInvoiceInf"
			},

			beforeSend: function () {
				$("#invoice_inf").html("<div>Please wait for a moment...</div>");
			},
			success: function (response) {
				$("#invoice_inf").html(response);
				$("#iv_name_banks").select2();

			}
		});

		$("#edit_invoice_frm").dialog({
			title: "Sửa thông tin yêu cầu xuất hoá đơn",
			width: 400,
			modal: true,
			resizable: false,
		});
	});

	// Nếu chọn áp dụng cho tất cả thì disable select và xoá hết option đã chọn
	$(document).on('change', '#applied_all', function () {
		if ($(this).is(":checked")) {
			$("#applied_passenger").val("");
			$("#applied_passenger").prop("disabled", "disabled");

			getPassengerLine($("form[name='DetailView']>input[name='record']").val());
		} else {
			$(".line_pass").remove();
			$("#applied_passenger").prop("disabled", false);
		}
	});

	// Lấy tên các hành khách được chọn
	$(document).on('change', '#applied_passenger', function () {

		var passengers = $('#applied_passenger').val();

		if (passengers == null || passengers.length == 0) {
			$(".line_pass").remove();
		} else {
			if ($(".line_pass").length > 0) {

				// Remove thông tin khách bị xoá
				$("input[name='pass_id[]']").each(function (index) {
					if (!passengers.includes($(this).val())) {
						$(".line_pass" + $(this).val()).remove();
						// Chỉnh lại stt
						$("input[name='pass_id[]']").each(function (index) {
							$(".pass_birthday").eq(index).attr("name", "pass_birthday" + index);
							$(".pass_order").eq(index).text("Hành khách " + (index + 1));
						});
					}
				});

				// Thêm thông tin khách bổ sung
				passengers.forEach(element => {
					if ($(".line_pass" + element).length == 0) {
						getPassengerLine($("form[name='DetailView']>input[name='record']").val(), element.toString(), 'insert');
					}
				});

			} else {
				if (passengers != null && passengers.length > 0) {
					getPassengerLine($("form[name='DetailView']>input[name='record']").val(), passengers.toString());
				}
			}
		}
	});

	// Thông tin hành trình sau khi đổi ngày bay
	$.ajax({
		url: "index.php?entryPoint=entryPointFlightBookings",
		type: "POST",
		data: {
			id: $("form[name='DetailView']>input[name='record']").val(),
			for: "showEditedFlightTime"
		},
		success: function (response) {
			if (response != '') {
				$("div[data-id='LBL_LINEITINERARIES_PANEL'] table#itinerary_tbl>tbody").append(response);
			} else {
				// $("div[data-id='LBL_LINEITINERARIES_PANEL'] table#itinerary_tbl").append("<tr class='edited_iti_line'><td colspan='13' style='border: 1px solid #ccc; padding: 5px 3px;'>Không có thông tin đổi ngày bay.</td></tr>");
				$("div[data-id='LBL_LINEITINERARIES_PANEL'] table#itinerary_tbl #no-change__edit-iti").append(" Không có thông tin thay đổi ngày bay.");
			}
		}
	});

	// Sửa thông tin hành trình thay đổi nếu có nhập sai
	$(document).on('click', '.edit_iti_row', function () {
		$("#line_itineraries_area").html("<div></div>");
		getItiLine($("form[name='DetailView']>input[name='record']").val(), $(this).attr("data-id"));

		$("#tbl_change_flight_time").dialog({
			title: "Sửa thông tin hành trình",
			width: 900,
			modal: true,
			resizable: false,
		});
	});

	// Sửa thông tin hành khách thay đổi nếu có nhập sai
	$(document).on('click', '.edit_pass_row', function () {
		$("#line_itineraries_area").html("<div></div>");
		getPassengerLine($("form[name='DetailView']>input[name='record']").val(), $(this).attr("data-id"), 'edit');

		$("#tbl_change_flight_time").dialog({
			title: "Sửa thông tin hành khách / hành lý / số vé",
			width: 1023,
			modal: true,
			resizable: false,
		});
	});

	// Thông tin hành khách sau khi thay đổi
	$.ajax({
		url: "index.php?entryPoint=entryPointFlightBookings",
		type: "POST",
		data: {
			id: $("form[name='DetailView']>input[name='record']").val(),
			pass_qty: $("#total_pass_qty").val(),
			for: "showChangedPassenger"
		},
		success: function (response) {
			if (response != '') {
				$("div[data-id='LBL_LINEPASSENGERS_PANEL'] table#tbl_pax").append(response);
			} else {
				// $("div[data-id='LBL_LINEPASSENGERS_PANEL'] table#tbl_pax").append("<tr class='edited_pass_line'><td colspan='10' style='border: 1px solid #ccc; padding: 5px 3px;'>Chưa có hành khách nào đổi thông tin.</td></tr>");
				$("div[data-id='LBL_LINEPASSENGERS_PANEL'] table#tbl_pax #no-change__edit-pass").append("Chưa có hành khách nào thay đổi thông tin.");
			}
		}
	});

	// Booking ở trạng thái "hoàn tất", "xuất vé" không đc edit và delete ngoại trừ kế toán trưởng và admin
	const chief_accountant_arr = ['Administrator', 'Admin', 'QuanLy'];
	if ((booking_status == '7' || booking_status == '8') && (chief_accountant_arr.indexOf(current_user_title) == -1)) {
		$('form[name="DetailView"] input:button[name="Edit"]').remove();
		$('form[name="DetailView"] input:submit[name="Delete"]').remove();
	}
	if (booking_status == '4') {
		$('form[name="DetailView"] input:button[name="Edit"]').remove();
	}

	if (booking_status == '8' && is_invoice_export == '1') {
		$('#btnCheckInvoiceExport').hide();
	}
	if (booking_status == '8' && is_invoice_input_export == '1') {
		$('#btnCheckInvoiceInputExport').hide();
	}

	// Cancelled booking
	$('#frmCancelled').submit(function () {
		if (!confirm('Bạn có chắc là muốn hủy booking này ?')) return false;
		else return true;
	});

	$(document).on('click', '#confirm-remind', function () {
		let journey_id = $(this).attr('iti_id');

		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			data: {
				journey_id: journey_id,
				for: "remindFlightSchedules",
			},
			type: "POST",
			cache: false,
			success: function (response) {
				if (response == 1) {
					let text_warning = 'Thông báo lịch bay cho khách hàng thành công.';
					showModalNotify(1, text_warning);
					$('.modal-overlay, .btn-modal-close').addClass('reload');
				} else {
					let text_warning = 'Lỗi khi thực hiện nhấn nút remind. Vui lòng liên hệ IT để được hỗ trợ.';
					showModalNotify(0, text_warning);
					$('.modal-overlay, .btn-modal-close').addClass('reload');
				}
			}
		});
	});


	// Open form send mail
	$('#btnSendMail').on('click', function () {
		$('#frmContinueSendMail').css('display', 'block');
		$(this).hide();
	});

	// Close form send mail
	$('#btnCancelSendMail').on('click', function () {
		$('#frmContinueSendMail').hide();
		$('#btnSendMail').show();
	});

	// Send mail confirm button
	$(document).on('submit', '#frmSendMail', function () {
		var email_regex = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
		var email = $.trim($('#frmSendMail input:hidden[name="email"]').val());
		if (email == '' || !email_regex.test(email)) {
			let text_warning = 'Email không hợp lệ!';
			showToastWarning(text_warning);

			return false;
		}
	});

	// Print eticket button
	$(document).on('click', 'input[name="btnPrintEticket"]', function () {
		var ln = $(this).attr('ln');
		$('#what_form').val($(this).closest('form[name="frmPrintEticket"]').attr('id'));
		$('#frmPrintEticket' + ln).attr('target', '_blank');
		$('#frmPrintEticket' + ln).attr('action', 'index.php?print=true');
		$('#frmPrintEticket' + ln + ' input:hidden[name="action"]').val('printeticket');
		$('#frmPrintEticket' + ln + ' input:hidden[name="return_action"]').val('');

		$('#dlgChonNgonNgu').dialog({
			height: 80,
			width: 320,
			modal: true,
			resizable: false
		});
		Set_Cookie('showLeftCol', 'false', 30, '/', '', '');
	});

	$(document).on('click', '#btnChonNgonNgu', function () {
		var what_form = '#' + $('#what_form').val();
		var lang = $('input:radio[name="ngonngu"]:checked').val();
		var khuhoi = $('#khuhoi').is(':checked') ? 1 : 0;
		var wayflight = $(what_form + ' input:hidden[name="direction"]').val();

		$(what_form).attr('action', $(what_form).attr('action') + '&lang=' + lang + '&khuhoi=' + khuhoi + '&wayflight=' + wayflight);
		$(what_form).submit();
	});

	// Send mail eticket button
	$(document).on('click', 'input[name="btnSendEticket"]', function () {
		var ln = $(this).attr('ln');
		$('#what_form').val($(this).closest('form[name="frmPrintEticket"]').attr('id'));

		$('#frmPrintEticket' + ln).attr('target', '_self');
		$('#frmPrintEticket' + ln).attr('action', 'index.php?print=false');
		$('#frmPrintEticket' + ln + ' input:hidden[name="action"]').val('sendeticket');
		$('#frmPrintEticket' + ln + ' input:hidden[name="return_action"]').val('DetailView');
		$('#dlgChonNgonNgu').dialog({
			height: 80,
			width: 320,
			modal: true,
			resizable: false
		});
	});

	// Ticket exported button
	$('#frmTicketExported, #frmCompleted, #frmChangeStatus').submit(function (e) {
		var booking_status = $(this).find('input:hidden[name="booking_status"]').val();
		if ($(this).find('select[name="booking_status"]').length > 0) {
			booking_status = $(this).find('select[name="booking_status"] :selected').val();
		}

		if (booking_status == '7' || booking_status == '8') {
			var is_ticket_exported = $(this).find('input:hidden[name="is_ticket_exported"]').val(); //1

			if (is_ticket_exported == 0) {
				let text_warning = 'Vui lòng Check vào Đã xuất vé!';
				showToastWarning(text_warning);
				return false;
			}

			var bought_price = document.getElementsByName('check_total_bought_price[]'); // giá mua
			var supplier_id = document.getElementsByName('check_supplier_id[]'); // id NCC
			var price_err = 0;
			var supplier_err = 0;

			for (var j = 0; j < bought_price.length; j++) {
				// Trường hợp Vé đã xuất trong ngày, có thể Void ( có thể hiểu là huỷ đặt chỗ ) giá mua có thể = 0 chỉ đối với VNA
				if ($.trim(bought_price[j].value) == '' || unformatNumber(bought_price[j].value) < 0) {
					price_err++;
				}
				if ($.trim(supplier_id[j].value) == '') {
					supplier_err++;
				}
			}

			if (price_err > 0) {
				let text_warning = 'Vui lòng nhập đầy đủ giá mua!';
				showToastWarning(text_warning);
				return false;
			}
			if (supplier_err > 0) {
				let text_warning = 'Vui lòng nhập đầy đủ nhà cung cấp!';
				showToastWarning(text_warning);
				return false;
			}


			var flight_type = $(this).find('input:hidden[name="flight_type"]').val();
			var arr = document.getElementsByName('eticket_outbound[]');
			var outbound_err = 0;
			var inbound_err = 0;


			for (var i = 0; i < arr.length; i++) {
				if (flight_type == '1' && (
					$('#eticket_outbound' + i).val() == ''
					|| $('#eticket_outbound' + i).val().length < 5
					|| $('#pnr_outbound' + i).val() == ''
					|| $('#pnr_outbound' + i).val().length < 5)
				) {
					outbound_err++;
				}

				if (flight_type == '0' && (
					$('#eticket_inbound' + i).val() == ''
					|| $('#eticket_inbound' + i).val().length < 5
					|| $('#pnr_inbound' + i).val() == ''
					|| $('#pnr_inbound' + i).val().length < 5)
				) {
					inbound_err++;
				}
			}

			if (outbound_err > 0) {
				let text_warning = 'Số vé, PNR chiều đi không hợp lệ!';
				showToastWarning(text_warning);
				return false;
			}
			if (inbound_err > 0) {
				let text_warning = 'Số vé, PNR chiều về không hợp lệ!';
				showToastWarning(text_warning);
				return false;
			}

		} // end if booking_status
	});

	$(".closemodal").click(function () {
		$.fancybox.close();
		return false;
	});

	$('#btnAddNote').click(function () {
		var ln = parseInt($('#note_row_count').val());
		$('#note_last_row').before(insertNoteLine(ln));
		$('#note_desc' + ln).focus();
		ln++;
		$('#note_row_count').val(ln);
	});

	$('#frmAddNote').submit(function () {
		if (!checkNoteLine()) {
			return false;
		}
	});

	// Line Note Message - Made by: DucPham at 28/09/2022
	const MESSAGE_LIST_CHAT_ID = "#message_list";
	const HEIGHT_ROW_TEXTAREA = 17; // 17px
	const HEIGHT_MESSAGE_LIST_CHAT = 465; // 465px
	const tag_textarea = $('textarea#note-description');

	$(document).ready(function () {
		// KHI TEXTAREA FOCUS
		tag_textarea.focus(function () {
			$('svg#icon-send-notes').css('fill', 'rgb(0, 132, 255)');
			$('.wrap-cancel svg g').attr('stroke', 'rgb(0, 132, 255)');
		});
		// KHI TEXTAREA KHÔNG FOCUS
		tag_textarea.blur(function () {
			$('svg#icon-send-notes').css('fill', '#BCC0C4');
			$('.wrap-cancel svg g').attr('stroke', '#BCC0C4');

		});

		// Change textarea height when texting
		tag_textarea.keyup(function (event) {
			if (event.keyCode == 13 && event.shiftKey) { // Khi bấm xuống hàng
				let rows = parseInt($(this).attr('rows')) + 1;
				let height = parseInt($(MESSAGE_LIST_CHAT_ID).height()) - HEIGHT_ROW_TEXTAREA;
				if (rows <= 5) {
					$(this).attr('rows', rows);
					$(MESSAGE_LIST_CHAT_ID).height(height);
				}
			}
			else { // Xuống hàng do độ dài
				let count_row = countRows($(this).val());
				let rows = parseInt($(this).attr('rows'));

				if (1 < count_row && count_row <= 5 && count_row > rows) {
					let t = count_row - rows;
					let height = parseInt($(MESSAGE_LIST_CHAT_ID).height()) - t * HEIGHT_ROW_TEXTAREA;
					$(this).attr('rows', count_row);
					$(MESSAGE_LIST_CHAT_ID).height(height);
				}
			}
		});
		tag_textarea.bind("paste", function (e) { // Copy paste
			let count_row = countRows(e.originalEvent.clipboardData.getData('text'));
			let rows = parseInt($(this).attr('rows'));

			if (1 < count_row && count_row > rows) {
				let t = count_row - rows;
				let height = parseInt($(MESSAGE_LIST_CHAT_ID).height()) - t * HEIGHT_ROW_TEXTAREA;
				$(this).attr('rows', count_row);
				$(MESSAGE_LIST_CHAT_ID).height(height);
			}
		});
		tag_textarea.on('keydown keyup', function () {
			var key = event.keyCode || event.charCode;

			if (key == 8 || key == 46) {
				let count_row = $(this).val().split("\n").length;
				let rows = parseInt($(this).attr('rows'));

				if (count_row - 1 > 0) { // Có ký tự /n
					if (count_row < 5 && count_row < rows) {
						let t = rows - count_row;
						let height = parseInt($(MESSAGE_LIST_CHAT_ID).height()) + t * HEIGHT_ROW_TEXTAREA;
						$(this).attr('rows', count_row);
						$(MESSAGE_LIST_CHAT_ID).height(height);
					}
				}
				else {
					count_row = countRows($(this).val());
					if (1 <= count_row && count_row <= 5 && count_row < rows) {
						let t = rows - count_row;
						let height = parseInt($(MESSAGE_LIST_CHAT_ID).height()) + t * HEIGHT_ROW_TEXTAREA;
						$(this).attr('rows', count_row);
						$(MESSAGE_LIST_CHAT_ID).height(height);
					}
				}

				if ($(this).val().length == 0) $(MESSAGE_LIST_CHAT_ID).height(HEIGHT_MESSAGE_LIST_CHAT);
			}
		});
	});

	$('#btn-open-mobile-menu').click(function () {
		$('.message_list').scrollTop($('.message_list')[0].scrollHeight);
	});

	$('#icon-send-notes').click(function () {
		let name = $('#note-name').val();
		let parent_id = $('#note-parent-id').val();
		let booking_status = $('#note-booking-status').val();
		let description = $('#note-description').val().trim();
		let username = $('#note-username').val();
		let send_loading = '<div class="lds-ring-notes"><div></div><div></div><div></div><div></div></div>';

		let dt = new Date();
		let datetime = ("0" + dt.getHours()).slice(-2) + ":" + ("0" + dt.getMinutes()).slice(-2) + ",  " + ("0" + dt.getDate()).slice(-2) + "/" + ("0" + (dt.getMonth() + 1)).slice(-2) + "/" + dt.getFullYear();
		let new_row = `<div class="row-mess row-this">
							<div class="row-time">${datetime}</div>
							<div class="row-user">${username}</div>
							<div class="row-content">${description}${send_loading}</div>
						</div>`;

		if (description.length == 0) return;
		else if (description.length < 5) {
			let text_warning = 'Diễn giải quá ngắn!';
			showToastWarning(text_warning);
			return;
		}

		// Add
		$(".message_list").append(new_row);
		$('#note-description').val("");
		$('.message_list').scrollTop($('.message_list')[0].scrollHeight);

		$.ajax({
			url: "index.php?entryPoint=entryPointSaveNote",
			type: "POST",
			data: {
				name: name,
				parent_id: parent_id,
				description: description,
				booking_status: booking_status,
				type: "ADD"
			},
			success: function (res) {
				if (res == 1) {
					$('.row-mess .row-content .lds-ring-notes').remove();
				}
				return;
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				let text_warning = 'ERROR: Vui lòng liên hệ bộ phận IT!';
				showToastWarning(text_warning);

				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
			}
		});

		// Sau khi gửi xong tn thì reset textarea về rows="1"
		$('#note-description').attr('rows', '1');

		return;
	});

	$(document).on('click', '.action-remove', function () {
		const dialog = $('#confirm_delete_message_dialog');
		let id_note = $(this).attr("data-id-note");
		let id_process = $(this).attr("data-id-process");
		let type_process = $(this).attr("data-type-process");
		let booking_id = $(this).attr("booking-id");

		if (id_process === undefined) id_process = "";

		dialog.addClass('active');
		$("#confirm_delete_message").attr("data-id-note", id_note);
		$("#confirm_delete_message").attr("data-id-process", id_process);
		$("#confirm_delete_message").attr("data-type-process", type_process);
		$("#confirm_delete_message").attr("booking-id", booking_id);

	});

	$('#cancel_delete_message').on('click', function () {
		$('#confirm_delete_message_dialog').removeClass('active');
	});

	$('#confirm_delete_message').click(function (event) {
		let id_note = $(this).attr("data-id-note");
		let id_process = $(this).attr("data-id-process");
		let type_process = $(this).attr("data-type-process");
		let booking_id = $(this).attr("booking-id");
		if (id_process === undefined) id_process = "";

		$('.action-remove[data-id-note=' + id_note + ']').closest('.row-mess').fadeOut(1000, function () { $(this).remove(); });

		$.ajax({
			url: "index.php?entryPoint=entryPointSaveNote",
			type: "POST",
			data: {
				id_note: id_note,
				id_process: id_process,
				type_process: type_process,
				booking_id: booking_id,
				type: "DELETE"
			},
			success: function (res) { },
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				let text_warning = 'ERROR: Vui lòng liên hệ bộ phận IT!';
				showToastWarning(text_warning);

				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
			}
		});

		$('#confirm_delete_message_dialog').removeClass('active');

	});
	// End Line Note (New)

	// CLICK BUTTON CANCEL
	$('#btnCancelled').click(function () {
		$('#dlgLyDoThangThua').dialog({
			modal: true,
			resizable: false
		});
		var which_form = $(this).parent('form');
		$('#which_form').val(which_form.attr('id'));
		$('.win-lose-radio').html(lose_reason);
		$('#txtGhiChuThangThua').text($('input:radio[name="radWinLoseReason"]:checked').attr('txt'));
		$('#txtGhiChuThangThua').focus();
		$('div.ui-dialog[aria-describedby=dlgLyDoThangThua]').css("width", "400px");
		$('#dlgLyDoThangThua').css({ "width": "auto", "height": "375px" });
	});

	// WIN LOSE REASON RADIO CHANGE
	$('.win-lose-radio').on('change', 'input:radio[name="radWinLoseReason"]', function () {
		$('#txtGhiChuThangThua').text($('input:radio[name="radWinLoseReason"]:checked').attr('txt'));
		if ($('#txtWorkingProcessNote').length != 0)
			$('#txtWorkingProcessNote').text($('input:radio[name="radWinLoseReason"]:checked').attr('txt'));
	});

	// SAVE WIN LOSE REASON
	$('#btnDongY').click(function () {
		var which_form = $('#which_form').val();
		$('#' + which_form + ' input:hidden[name="lydothangthua_id"]').val($('input:radio[name="radWinLoseReason"]:checked').val());
		$('#' + which_form + ' input:hidden[name="ghichuthangthua"]').val($.trim($('#txtGhiChuThangThua').val()));
		$('#' + which_form).submit();
	});

	// Open working process popup 
	$('.frmBookingStatus').submit(function (e) {
		var frmSaveWorkingProcess = $(this).attr('id');
		$('#frmSaveWorkingProcess').val(frmSaveWorkingProcess);
		var booking_status = $('#' + frmSaveWorkingProcess + ' input:hidden[name="booking_status"]').val();

		if ($('#' + frmSaveWorkingProcess + ' input:hidden[name="bonus"]').length > 0) {
			$('#txtBonus').val($('#' + frmSaveWorkingProcess + ' input:hidden[name="bonus"]').val());
			$('#txtBonus').parent().parent().show();
		}

		if (booking_status == '8' && frmSaveWorkingProcess == 'frmCompleted') {
			$(this).submit();
		} else {
			if (!$(this).hasClass("error")) {
				$('#dlgWorkingProcessNote').dialog({
					modal: true,
					resizable: false,
					closeOnEscape: true
				});
			}
			e.preventDefault();
		}
	});

	$(document).one('click', '#btnSaveWorkingProcess', function (event) {
		$(this).attr("disabled", "disabled");
		var frmSaveWorkingProcess = $('#frmSaveWorkingProcess').val();

		if ($('#' + frmSaveWorkingProcess + ' input:hidden[name="booking_status"]').val() == '8') {
			$('#' + frmSaveWorkingProcess + ' input:hidden[name="lydothangthua_id"]').val($('input:radio[name="radWinLoseReason"]:checked').val());
			$('#' + frmSaveWorkingProcess + ' input:hidden[name="ghichuthangthua"]').val($.trim($('#txtWorkingProcessNote').val()));
		}

		if ($('#' + frmSaveWorkingProcess + ' input:hidden[name="bonus"]').length > 0) {
			$('#' + frmSaveWorkingProcess + ' input:hidden[name="bonus"]').val($.trim($('#txtBonus').val()));
		}

		// Kiểm tra diễn giải phải dài hơn 30 ký tự và tối đa 200 ký tự, không tính khoảng trắng, chấm và phẩy
		var replace_arr = [' ', '.', ',', "\n"];
		var des = $("#txtWorkingProcessNote").val();
		for (i = 0; i < replace_arr.length; i++) {
			des = des.replaceAll(replace_arr[i], '');
		}

		if (des.length < 20) {
			$('#dlgWorkingProcessNote').dialog('close');
			let text_warning = 'Bạn note quá ít! Trang sẽ tự động reload trong vòng <span id="count-down" class="fw-semibold color-red"> ' + countdownAndReload(10) + '</span> nữa.';
			showModalNotify(0, text_warning);
			$('.modal-overlay, .btn-modal-close').addClass('reload');
		} else if (des.length > 200) {
			$('#dlgWorkingProcessNote').dialog('close');
			let text_warning = 'Bạn note quá nhiều! Trang sẽ tự động reload trong vòng <span id="count-down" class="fw-semibold color-red"> ' + countdownAndReload(10) + '</span> nữa.';
			showModalNotify(0, text_warning);
			$('.modal-overlay, .btn-modal-close').addClass('reload');
		}
		else {
			var save_post_data = $('#' + frmSaveWorkingProcess).serialize();
			save_post_data += '&txtWorkingProcessNote=' + $.trim($('#txtWorkingProcessNote').val());

			$.ajax({
				url: 'index.php?entryPoint=entryPointSaveWorkingProcess',
				data: save_post_data,
				type: 'POST',
				cache: false,
				beforeSend: function () {
					$('#save-working-process-error').text('');
				},
				success: function (res) {
					res = parseInt(res);
					if (res == 1) {
						$('#' + frmSaveWorkingProcess).unbind('submit');
						event.preventDefault();
						$('.container-waiting').show();
						location.reload();
						return true;
					}
					else if (res == 2) {
						event.preventDefault();
						$('.container-waiting').show();
						location.reload();
						return true;
					}
					else {
						$('#save-working-process-error').text('Lỗi xảy ra trong quá trình lưu. Vui lòng thử lại');
						$('.container-waiting').hide();

						setTimeout(function () {
							location.reload();
						}, 3000);

						return false;
					}
				}
			});
		}
	});

	$('#btnCloseWorkingProcess').click(function () {
		$('#dlgWorkingProcessNote').dialog('close');
	});
	// End open working process popup

	// Begin check contact info
	$('#btnCheckContactInfo').click(function () {
		$('#CheckContactInfoDialog').dialog({
			minHeight: 200,
			width: 1000,
			modal: true,
			resizable: false,
		});
	});

	$('#CheckContactInfoDialog').on('dialogopen', function (event, ui) {
		var ct_name = $('#btnCheckContactInfo').attr('ct_name');
		var ct_mobile = $('#btnCheckContactInfo').attr('ct_mobile');
		var ct_email = $('#btnCheckContactInfo').attr('ct_email');
		var ct_id_booking = $('#btnCheckContactInfo').attr('ct_id_booking');

		if (ct_name != '' && (ct_mobile != '' || ct_email != '')) {
			$.ajax({
				cache: false,
				type: 'post',
				data: 'ct_name=' + ct_name + '&ct_mobile=' + ct_mobile + '&ct_email=' + ct_email + '&ct_id_booking=' + ct_id_booking,
				async: false,
				url: 'index.php?entryPoint=entryPointMyCheckContactInfo',
				success: function (output) {
					$('#CheckContactInfoDialog').html(output);
				}
			});
		}
	});
	// End check contact info

	// Begin edit booking detail
	$("#edit_bkg_btn").on("click", function () {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: "for=populateBookingDetail&id=" + $("#bkg_no").val() + "&flight_type=" + $("#flight_type").val() + "&airline_out=" + $("#airline_out").val() + "&airline_in=" + $("#airline_in").val() + "&ticket_class0=" + $(".ticket_class0").text() + "&ticket_class1=" + $(".ticket_class1").text(),
			beforeSend: function () {
				$("body").css({ "cursor": "wait" });
			},
			success: function (response) {
				response = jQuery.parseJSON(response);
				$("#tbl_line_details").html(response.line_html);
				$("#tbl_line_passengers").html(response.pass_html);
			}
		});

		$("#bkg_detail").dialog({
			title: "Chi tiết booking",
			width: 1350,
			modal: true,
			resizable: false,
		});
	});
	// End edit booking detail


	// Thông tin những người đã xem booking
	$("#btnViewBooking").on("click", function () {
		$.ajax({
			url: "index.php?entryPoint=entryPointTracker",
			type: "POST",
			data: {
				"item_id": $("#frmViewedBooking input[name='record']").val(),
				"for": "Tracker_ViewBooking",
			},
			beforeSend: function () {
				$("body").css({ "cursor": "wait" });
			},
			success: function (response) {
				$("#viewed_booking--wrap").html(response);
			}
		});

		$("#viewed_booking--wrap").dialog({
			title: "Đã xem booking",
			width: 1000,
			modal: true,
			resizable: false,
		});
	});
	// End edit booking detail

	// Kiểm tra ncc và số vé
	$("#bkg_detail").submit(function () {
		if (!checkLineItems(0)) {
			return false;
		}
		return true;
	});


	// Begin add luggage
	$("#add_luggage_btn").click(function () {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: "for=addLuggage&id=" + $("#bkg_no_luggage").val() + "&flight_type=" + $("#flight_type").val() + "&airline_out=" + $("#airline_out").val() + "&airline_in=" + $("#airline_in").val() + "&ticket_class0=" + $(".ticket_class0").text() + "&ticket_class1=" + $(".ticket_class1").text() + "&contact_name=" + $("#btnCheckContactInfo").attr("ct_name") + "&contact_phone=" + $("#btnCheckContactInfo").attr("ct_mobile"),
			beforeSend: function () {
				$("body").css({ "cursor": "wait" });
			},
			success: function (response) {
				$("#line_passengers_luggage_area").html(response);
			}
		});
		$("#add_luggage").dialog({
			title: "Thêm hành lý",
			width: 1300,
			modal: true,
			resizable: false,
		});
	});

	$("#add_luggage").submit(function () {
		if ($("#com_location_id").val() == '' && $("#tknganhang_id").val() == '') {
			let text_warning = 'Bạn chưa chọn hình thức thanh toán!';
			showToastWarning(text_warning);

			$("#receipt_type").focus();
			return false;
		}
		if (!checkLineItems(1)) {
			return false;
		}
		createNewLineDetail(1);
		return true;
	});
	// End add luggage


	// Begin change name
	$("#change_name_btn").click(function () {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: "for=changeName&id=" + $("#bkg_no_name").val() + "&flight_type=" + $("#flight_type").val() + "&airline_out=" + $("#airline_out").val() + "&airline_in=" + $("#airline_in").val() + "&ticket_class0=" + $(".ticket_class0").text() + "&ticket_class1=" + $(".ticket_class1").text(),
			beforeSend: function () {
				$("body").css({ "cursor": "wait" });
			},
			success: function (response) {
				$("#line_passengers_name_area").html(response);
			}
		});
		$("#change_name").dialog({
			title: "Đối tên hành khách",
			width: 1300,
			modal: true,
			resizable: false,
		});
	});

	$("#change_name").submit(function () {
		if (!checkLineItems(2)) {
			return false;
		}
		createNewLineDetail(2);
		return true;
	});
	// End change name

	// Begin change flight time
	$("#change_flight_time").click(function () {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: "for=changeFlightTime&id=" + $("form[name='DetailView']>input[name='record']").val(),
			beforeSend: function () {
				$("body").css({ "cursor": "wait" });
				$("#line_itineraries_area").html("Loading, Please wait ... ");
			},
			success: function (response) {
				$("#line_itineraries_area").html(response);

				// $('#applied_passenger').removeAttr('multiple');
				$("#applied_passenger").select2();
				$("#applied_passenger").next().width("100%");

				$("#departure0, #arrival0, #departure1, #arrival1").autocomplete({
					source: domestic_airport_lst
				});

				$("#ticket_class0, #ticket_class1").autocomplete({
					source: all_ticket_class
				});
			}
		});

		$("#tbl_change_flight_time").dialog({
			title: "Đối ngày bay / hành trình / hành khách / hành lý / số vé / code vé",
			width: 1023,
			modal: true,
			resizable: false,
		});
	});

	$("#tbl_change_flight_time").on("submit", function (event) {

		if (!checkLineItems(3)) {
			return false;
		}

		return true;
	});
	// End change flight time


	$(document).on("focus", ".allow-number-only", function () {
		var cal_date_format = $('#cal_date_format').val();
		var dec_seperator = $('#dec_seperator').val();
		var grp_seperator = $('#grp_seperator').val();
		var sig_digits = $('#sig_digits').val();
		$('.allow-number-only').number(true, sig_digits, dec_seperator, grp_seperator);
	});

	$(document).on("change", "#receipt_type", function () {
		var type = $("#receipt_type").val();
		if (type == 'cash') {
			$("#com_location_id").show();
			$("#tknganhang_id").hide();
		} else {
			$("#tknganhang_id").show();
			$("#com_location_id").hide();
		}
	});

	$(".input_hour, .input_minute").on('keydown', function (event) {
		$(this).allowNumberOnly(event);
	});

	$('#add_luggage, #change_name, #bkg_detail').on('dialogclose', function (event) {
		$("#tbl_line_passengers_luggage").html("");
		$("#tbl_line_passengers_name").html("");
		$("#tbl_line_details").html("");
		$("#tbl_line_passengers").html("");
	});

	$(document).ajaxComplete(function () {
		$("body").css({ "cursor": "default" });
	});

	// Nút chia doanh số
	$("#share_profit_btn").on("click", function () {

		$("#share_profit_frm").dialog({
			title: "Thông tin chia doanh số",
			width: 400,
			modal: true,
			resizable: false,
		});

		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: {
				"bk": $(this).attr("bk"),
				"for": "getShareProfit",
			},
			beforeSend: function () {
				$("#bk_ttl_amt").text("");
				// $("#share_profit_tbl>tbody").html("<tr class='share_profit_loading'><td><img src='custom/themes/default/images/loading.gif' width='23'></td></tr>");
			},
			success: function (response) {
				res = JSON.parse(response);
				// $(".share_profit_loading").remove();
				$("#bk_ttl_amt").text(res.profit);
				$("#share_profit_tbl>tbody").html(res.html);
				$("#shareprofit_cnt").val(res.line_cnt);
			}
		});
	});

	$(document).on("click", "#add_shareprofit_line", function () {
		var ln = $("#shareprofit_cnt").val();
		$("#share_profit_tbl>tbody>tr").last().before(
			`<tr class='profit_ln'>
				<td id='share_profit_no${ln}' class="fw-bold text-center align-center"></td>
				<td>
					<div class="d-flex align-items-center gap-2">
						<input class="box-input" type='text' name='share_profit_user[]' id='share_profit_user${ln}' size='20' autocomplete='off'>
						<input type='button' class='btn btn-primary' value='Chọn' onclick='open_popup(&quot;Users&quot;, 600, 400, &quot;&quot;, true, false, {&quot;call_back_function&quot;:&quot;set_return&quot;,&quot;form_name&quot;:&quot;share_profit_frm&quot;,&quot;field_to_name_array&quot;:{&quot;id&quot:&quot;share_profit_user_id${ln}&quot;,&quot;user_name&quot;:&quot;share_profit_user${ln}&quot;}},&quot;single&quot;, true);' style='vertical-align: baseline;'>
						<input type='hidden' name='share_profit_userid[]' id='share_profit_user_id${ln}'>
					</div>
				</td>
				<td>
					<input class="box-input text-end w-100" type='text' name='share_profit_amt[]' id='share_profit_amt${ln}' oninput='this.value = formatNumber(unformatNumber(this.value)); calculateTotalShareProfit();'>
				</td>
				<td class="text-center">
					<svg xmlns="http://www.w3.org/2000/svg" class="cursor-pointer" onclick="markShareProfitDelete(${ln});" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
					<input type='hidden' name='share_profit_delete[]' id='share_profit_delete${ln}' value='0'>
					<input type='hidden' name='share_profit_id[]'>
				</td>
			</tr>`);

		markOrderProfitLine();
		$("#shareprofit_cnt").val(parseInt(ln) + 1);
	});

	$("#share_profit_frm").submit(function () {
		return checkShareProfit();
	});

	// Change booking status
	$(document).on("change", "select#booking_status", function () {
		let status = $(this).find(":selected").val();

		$('#modal-confirm .modal-title').html('Xác nhận đổi tình trạng');
		$('#modal-confirm #confirm-modal').attr('type', 'change-status');
		$('#modal-confirm #confirm-modal').attr('data', status);

		let myModal = new bootstrap.Modal(document.getElementById('modal-confirm'))
		myModal.show();
	});

	$(document).on("click", "#confirm-modal", function () {
		$('#frmChangeStatus').submit();
	});

	// Icon get QR code
	$('#get_qr_code').on('click', function () {
		showDialog("dialog_qr_code");
	});
	$('#select_bank_get_qr_code').change(function () {
		let selectedValue = $(this).val();
		$("#img_qr_code").attr('src', selectedValue);
	});

	// GET THÔNG TIN BANK - SEND CUSTOMER
	$('#get_bank').on('click', function () {
		let booking_id = $(this).attr('booking_id');

		$.ajax({
			url: "index.php?entryPoint=entryPointBankAccount",
			type: "POST",
			data: {
				booking: booking_id,
				type: 'get_infor_bank',
				for: "changeBankAccountPosition",
			},
			beforeSend: function () { },
			success: function (response) {
				if (response.length > 0) {
					copyContent(response);
				}
			}
		});
	});

	// Handle mapping call with booking
	$('#btn-mapping-call-booking').click(function () {
		let call_name = $('input[name=call_name]').val().trim();
		let booking_id = $(this).attr('booking_id');
		let booking_name = $(this).attr('booking_name');
		if (call_name.length == 0 || call_name.length > 20 || booking_id.length == 0) return;

		$.ajax({
			url: "index.php?entryPoint=entryPointCallContact",
			data: {
				type: "map_call_booking",
				call_name: call_name,
				booking_id: booking_id,
				booking_name: booking_name
			},
			type: "POST",
			cache: false,
			success: function (response) {
				closeDialog("mapping_call_booking");
				if (response == 1) {
					showModalNotify(1, "Liên kết cuộc gọi thành công");
					$('.modal-overlay, .btn-modal-close').addClass('reload');
				} else if (response == 2) {
					// warning
					showModalNotify(2, "Vui lòng cập nhật thông tin cuộc gọi trước khi liên kết");
				}
				else {
					showModalNotify(0, "Thao tác không thành công. Liên hệ IT để được hỗ trợ.");
				}
			}
		});
	});

	// Voucher
	$('.voucher').on('click', function () {
		$('#dialog_voucher_detail').dialog({
			width: 500,
			modal: true,
			resizable: false,
			closeOnEscape: false,
			title: "Chi tiết voucher"
		});
	});
});

// Count row for textarea
function countRows(stringValue) {
	let count = 1;
	// let stringValue = document.getElementById(textarea_id).value;
	// let stringLength = document.getElementById(textarea_id).value.length;
	let stringLength = stringValue.length;
	// var count = Math.round( stringLength / document.getElementById(textarea_id).cols );

	let arr_special = ['i', 'í', 'ì', 'ị', 'ĩ', 'ỉ', 'I', 'Í', 'Ì', 'Ị', 'Ĩ', 'Ỉ', 'l', 'j', 't', ',', '.', ';', ':', '[', ']', "'", '!', '|'];
	let more = 0;
	for (let i = 0; i < arr_special.length; i++) {
		more += stringValue.split(arr_special[i]).length - 1;
	}
	stringLength += more;

	if (stringLength >= 50) count++;
	if (stringLength >= 100) count++;
	if (stringLength >= 150) count++;
	if (stringLength >= 200) count++;

	// Line-down character
	let total_line_down = stringValue.split("\n").length - 1;

	return count + total_line_down;
}

// Remove note line
function markNoteLineDeleted(ln) {
	$('#note_line' + ln).hide();
	$('#note_line_btn' + ln).hide();
	$('#note_deleted' + ln).val(1);
}

// Add new note
function insertNoteLine(ln) {
	var html = '';

	html += `<tr id="note_line${ln}">
				<td width="3%" class="text-center align-top">
					<button title="Xóa" type="button" onclick="markNoteLineDeleted(${ln});">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
					</button>
					<input type="hidden" value="0" name="note_deleted[]" id="note_deleted${ln}" />
					<input type="hidden" name="note_detail_id[]" id="note_detail_id${ln}" value="" />
				</td>
				<td colspan="5" width="40%"><textarea rows="3" type="text" value="" id="note_desc${ln}" name="note_desc[]"></textarea></td>
			</tr>
	`;

	return html;
}

// Check note line valid
function checkNoteLine() {
	var arr = document.getElementsByName('note_deleted[]');
	for (var i = 0; i < arr.length; i++) {
		if ($('#note_deleted' + i).val() == '0' && $.trim($('#note_desc' + i).val()) == '') {

			let text_warning = 'Ghi chú không hợp lệ!';
			showToastWarning(text_warning);

			$('#note_desc' + i).focus();
			return false;
		}
	}
	return true;
}

function checkLineItems(type) {
	if (type == 0) {
		var bkd_arr = document.getElementsByName('bkd_deleted[]');
		var psg_arr = document.getElementsByName('psg_deleted[]');
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
		if (bkd_arr.length > 0) {
			for (var i = 0; i < bkd_arr.length; i++) {
				if (bkd_arr[i].value == '0' && unformatNumber($('#bkd_quantity' + i).val()) <= 0) {

					let text_warning = 'Số lượng phải lớn hơn 0!';
					showToastWarning(text_warning);

					$('#bkd_quantity' + i).focus();
					$('#bkd_quantity' + i).select();
					return false;
				}
				if (bkd_arr[i].value == '0' && unformatNumber($('#bkd_total_price' + i).val()) <= 0) {

					let text_warning = 'Thành tiền phải lớn hơn 0!';
					showToastWarning(text_warning);

					$('#bkd_total_price' + i).focus();
					$('#bkd_total_price' + i).select();
					return false;
				}

				// booking ở trạng thái xuất vé
				// lấy số lượng chi tiết vé đã nhập NCC
				if (bkd_arr[i].value == '0') {
					// lượt đi
					if ($('#bkd_supplier_id' + i).val() != '' && $('#bkd_direction' + i).val() == '0') {
						bkd_has_supplier_outb[$('#bkd_passenger_type' + i).val()][1] += parseInt($('#bkd_quantity' + i).val());
					}

					// lượt về nếu là khứ hồi
					if ($('#flight_type').val() == '0') {
						if ($('#bkd_supplier_id' + i).val() != '' && $('#bkd_direction' + i).val() == '1') {
							bkd_has_supplier_inb[$('#bkd_passenger_type' + i).val()][1] += parseInt($('#bkd_quantity' + i).val());
						}
					}
				}
			}
		}
		// end if
		// if(psg_arr.length > 0){
		// 	var psg_has_eticket_outb = [
		// 		['0', 0],
		// 		['1', 0],
		// 		['2', 0]
		// 	];
		// 	var psg_has_eticket_inb = [
		// 		['0', 0],
		// 		['1', 0],
		// 		['2', 0]
		// 	];
		// 	for(var i=0; i<psg_arr.length; i++){
		// 		// || $.trim($('#psg_full_name' + i).val()).length < 5
		// 		if(psg_arr[i].value == '0' && ($.trim($('#psg_full_name' + i).val()) == '')){
		// 			alert('Họ tên hành khách không hợp lệ');
		// 			$('#psg_full_name' + i).focus();
		// 			$('#psg_full_name' + i).select();
		// 			return false;
		// 		}
		// 		if(psg_arr[i].value == '0' && $.trim($('#psg_eticket_outbound' + i).val()) != '' && $.trim($('#psg_eticket_outbound' + i).val()).length < 5){
		// 			alert('Số vé chiều đi không hợp lệ');
		// 			$('#psg_eticket_outbound' + i).focus();
		// 			$('#psg_eticket_outbound' + i).select();
		// 			return false;
		// 		}
		// 		if(psg_arr[i].value == '0' && $.trim($('#psg_eticket_inbound' + i).val()) != '' && $.trim($('#psg_eticket_inbound' + i).val()).length < 5){
		// 			alert('Số vé chiều về không hợp lệ');
		// 			$('#psg_eticket_inbound' + i).focus();
		// 			$('#psg_eticket_inbound' + i).select();
		// 			return false;
		// 		}
		// 		if(psg_arr[i].value == '0' && $.trim($('#psg_pnr_outbound' + i).val()) != '' && $.trim($('#psg_pnr_outbound' + i).val()).length < 5){
		// 			alert('PNR chiều đi không hợp lệ');
		// 			$('#psg_pnr_outbound' + i).focus();
		// 			$('#psg_pnr_outbound' + i).select();
		// 			return false;
		// 		}
		// 		if(psg_arr[i].value == '0' && $.trim($('#psg_pnr_inbound' + i).val()) != '' && $.trim($('#psg_pnr_inbound' + i).val()).length < 5){
		// 			alert('PNR chiều về không hợp lệ');
		// 			$('#psg_pnr_inbound' + i).focus();
		// 			$('#psg_pnr_inbound' + i).select();
		// 			return false;
		// 		}

		// 		// booking ở trạng thái xuất vé
		// 		// lấy số lượng hành khách có code vé
		// 		if(psg_arr[i].value == '0') {
		// 			// lượt đi
		// 			if($('#psg_eticket_outbound' + i).val() != '') {
		// 				psg_has_eticket_outb[$('#psg_traveller_type' + i).val()][1] += 1;
		// 			}

		// 			//lượt về nếu là khứ hồi
		// 			if($('#flight_type').val() == '0') {
		// 				if($('#psg_eticket_inbound' + i).val() != '') {
		// 					psg_has_eticket_inb[$('#psg_traveller_type' + i).val()][1] += 1;
		// 				}
		// 			}
		// 		} 

		// 	}

		// 	// kiểm tra sl nhà cung cấp tương ứng với số vé của hành khách
		// 	// lượt đi
		// 	if(psg_has_eticket_outb[0][1] > bkd_has_supplier_outb[0][1] || psg_has_eticket_outb[1][1] > bkd_has_supplier_outb[1][1] || psg_has_eticket_outb[2][1] > bkd_has_supplier_outb[2][1] || psg_has_eticket_inb[0][1] > bkd_has_supplier_inb[0][1] || psg_has_eticket_inb[1][1] > bkd_has_supplier_inb[1][1] || psg_has_eticket_inb[2][1] > bkd_has_supplier_inb[2][1]) {
		// 		alert("Xuất vé lượt nào vui lòng chọn nhà cung cấp tương ứng cho lượt đó.");
		// 		return false;
		// 	}
		// }// end if
	} else if (type == 1) {
		var luggage_arr = document.getElementsByName('psg_detail_id[]');
		for (i = 0; i < luggage_arr.length; i++) {
			if ($('#psg_luggage_price' + i).val() != '0') {
				if ($("#psg_luggage_purchase" + i).val() == 0) {
					let text_warning = 'Bạn chưa điền giá mua cho lượt đi!';
					showToastWarning(text_warning);

					$("#psg_luggage_purchase" + i).focus();
					return false;
				}
				if ($("#psg_luggage_supplier" + i).val() == '') {
					let text_warning = 'Bạn chưa điền NCC cho lượt đi!';
					showToastWarning(text_warning);


					$("#psg_luggage_supplier" + i).focus();
					return false;
				}
			}

			if ($('#psg_luggage_price_inbound' + i).val() != '0') {
				if ($("#psg_luggage_purchase_inbound" + i).val() == 0) {
					let text_warning = 'Bạn chưa điền giá mua cho lượt về!';
					showToastWarning(text_warning);

					$("#psg_luggage_purchase_inbound" + i).focus();
					return false;
				}
				if ($("#psg_luggage_supplier_inbound" + i).val() == '') {
					let text_warning = 'Bạn chưa điền NCC cho lượt về!';
					showToastWarning(text_warning);

					$("#psg_luggage_supplier_inbound" + i).focus();
					return false;
				}
			}
		}
	} else if (type == 2) {
		var name_arr = document.getElementsByName('psg_detail_id[]');
		for (i = 0; i < name_arr.length; i++) {
			if ($.trim($("#new_psg_full_name" + i).val()) == '' || $.trim($("#new_psg_full_name" + i).val().length) < 5) {
				let text_warning = 'Tên khách hàng chưa hợp lệ!';
				showToastWarning(text_warning);

				$("#new_psg_full_name" + i).focus();
				$("#new_psg_full_name" + i).select();
				return false;
			}
		}
	} else if (type == 3) { // check thông tin đổi ngày bay, hành trình

		// tối đa 4 ô, 2 ô ở chiều đi, 2 ô ở chiều về (nếu có)
		var iti_hour_arr = document.getElementsByClassName('input_hour');
		var iti_minute_arr = document.getElementsByClassName('input_minute');

		if (iti_hour_arr.length > 0) {
			for (var i = 0; i < 2; i++) {

				// kiểm tra số giờ nhập vào
				if ((iti_hour_arr[i].value < 0 || iti_hour_arr[i].value > 23)) {

					let text_warning = 'Giờ phải >= 0 và < 24';
					showToastWarning(text_warning);

					iti_hour_arr[i].focus();
					iti_hour_arr[i].select();
					return false;
				}
				// kiểm tra số phút nhập vào
				if ((iti_minute_arr[i].value < 0 || iti_minute_arr[i].value > 59)) {

					let text_warning = 'Phút phải >= 0 và < 60';
					showToastWarning(text_warning);

					iti_minute_arr[i].focus();
					iti_minute_arr[i].select();
					return false;
				}
			}

			// kiểm tra ngày đi, ngày đến -- lượt đi
			addToValidate('tbl_change_flight_time', 'departure_date0', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
			addToValidate('tbl_change_flight_time', 'arrival_date0', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
			addToValidate('tbl_change_flight_time', 'departure_date1', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
			addToValidate('tbl_change_flight_time', 'arrival_date1', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');

			if (!check_form('tbl_change_flight_time')) {
				return false;
			} else { // kiểm tra ngày đi < ngày đến
				var departure_date0 = $("input[name=departure_date0]").val();
				var arrival_date0 = $("input[name=arrival_date0]").val();
				var departure_date_arr = departure_date0.split('-');
				var arrival_date_arr = arrival_date0.split('-');
				var date1 = departure_date_arr[1] + '-' + departure_date_arr[0] + '-' + departure_date_arr[2];
				var date2 = arrival_date_arr[1] + '-' + arrival_date_arr[0] + '-' + arrival_date_arr[2];
				$date_result = compareTwoDate(date1, date2);

				if ($date_result == false) {

					let text_warning = 'Ngày đi phải nhỏ hơn ngày đến.';
					showToastWarning(text_warning);

					$("input[name=departure_date0]").focus();
					$("input[name=departure_date0]").select();
					return false;
				}
			}
		}
	}
	return true;
}

function compareTwoDate(date1, date2) {
	var d1 = new Date(date1);
	var d2 = new Date(date2);
	if (d1 > d2) {
		return false;
	}
	return true;
}

function createNewLineDetail(type) {
	if (type < 3) {
		var detail_arr = document.getElementsByName('psg_detail_id[]');
		for (i = 0; i < detail_arr.length; i++) {
			if (type == 1) {
				if ($("#psg_luggage_price" + i).val() != '0' || $("#psg_luggage_price_inbound" + i).val() != '0') {
					$("#psg_full_name" + i).val($("#psg_new_full_name" + i).text());
					$("#psg_add_type" + i).val(type);
					$("#psg_detail_id" + i).val("");
				} else {
					$("#psg_parent_detail_id" + i).val("");
				}
			}

			if (type == 2) {
				if ($('#new_psg_full_name' + i).val() != $("#psg_full_name" + i).val()) {
					$("#psg_detail_id" + i).val("");
					$("#psg_add_type" + i).val(type);
				} else {
					$("#new_psg_full_name" + i).val($("#psg_full_name" + i).attr("old_name"));
					$("#psg_parent_detail_id" + i).val("");
				}
			}
		}
	}
}

// Lấy thông tin hành trình
function getItiLine(booking_id, iti_id = '') {
	$.ajax({
		url: "index.php?entryPoint=entryPointFlightBookings",
		type: "POST",
		data: {
			booking: booking_id,
			iti_id: iti_id,
			for: "getItiLine"
		},
		beforeSend: function () {
			$("#line_itineraries_area").html("<div class='line_pass'>Please wait for a moment...</div>");
		},
		success: function (response) {
			$("#line_itineraries_area").html(response);
			$("#departure0, #arrival0").autocomplete({
				source: domestic_airport_lst
			});
			$("#ticket_class0").autocomplete({
				source: all_ticket_class
			});
		}
	});
}

// Lấy thông tin khách hàng
function getPassengerLine(booking_id, pass_id = '', type = '') {
	$.ajax({
		url: "index.php?entryPoint=entryPointFlightBookings",
		type: "POST",
		data: {
			booking: booking_id,
			pass_id: pass_id,
			type: type,
			ticket_class_ob: $("input[name='ticket_class0']").val(),
			ticket_class_ib: $("input[name='ticket_class1']").val(),
			for: "getPassengerLine"
		},
		beforeSend: function () {
			if (type == 'edit') {
				$("#line_itineraries_area").html("<div class='line_pass'>Please wait for a moment...</div>");
			}
		},
		success: function (response) {
			if (type == 'insert') {
				$("#passenger_tbl").append(response);
			} else {
				$(".line_pass").remove();
				$("#line_itineraries_area").append(response);
			}

			// $(".pass_luggage").select2();
			// $(".supplier_line>select").select2();

			// chỉnh lại stt
			$("input[name='pass_id[]']").each(function (index) {
				$(".pass_birthday").eq(index).attr("name", "pass_birthday" + index);
				$(".pass_order").eq(index).text("Hành khách " + (index + 1) + ":");
				addToValidate('tbl_change_flight_time', 'pass_birthday' + index, 'date', false, 'Ngày phải nhập theo cú pháp: 01-01-2022');
			});
		}
	});
}

function markOrderProfitLine() {
	var i = 0;
	$(".profit_ln").each(function (ind) {
		if ($("#share_profit_delete" + (ind + 1)).val() == 0) {
			$("#share_profit_no" + (ind + 1)).text(i + 1);
			i++;
		}
	});
}

function calculateTotalShareProfit() {
	var share_profit = 0;
	$(".profit_ln").each(function (ind) {
		if ($("#share_profit_delete" + (ind + 1)).val() == 0) {
			share_profit += parseInt(unformatNumber($("#share_profit_amt" + (ind + 1)).val()));
		}
	});
	$("#ttl_share_profit").text(formatNumber(share_profit));
	return share_profit;
}

function markShareProfitDelete(ln) {
	$("#share_profit_delete" + ln).val(1);
	$("#share_profit_delete" + ln).parent().parent().hide();
	markOrderProfitLine();
	calculateTotalShareProfit();
}

function checkShareProfit() {
	// DS chia không thể cao hơn tổng DS
	var total_profit = unformatNumber($("#bk_ttl_amt").text());
	var share_profit = calculateTotalShareProfit();
	if (share_profit > total_profit) {
		let text_warning = 'Không thể chia doanh số cao hơn doanh số tổng!';
		showToastWarning(text_warning);
		return false;
	}
	return true;
}


// BUTTON "CHỈNH SỬA CHI TIẾT BOOKING"
// ==================================
function calculateLineEditDetails(ln, is_cal_admin = 0, is_cal_tax = 0) {
	let ticket_type = $("input[name='ticket_type']").val();
	let qty = unformatNumber($('#bk_edit_quantity' + ln).val());
	let price = unformatNumber($('#bk_edit_unit_price' + ln).val());
	let tax_fee = unformatNumber($('#bk_edit_tax_and_fee' + ln).val());
	let admin_fee = unformatNumber($('#bk_edit_admin_fee' + ln).val());
	let vat_admin = admin_fee_no_vat = 0;

	// chỉ tính vat và phí admin đối với vé nội địa
	if (ticket_type != '2') {
		if (is_cal_tax) {
			tax_fee = price * 8 / 100;
		}

		// lấy hãng bay lượt đi / về
		let airline_inf = getAirLineInf();

		// làm tròn thuế lượt đi / về
		if (
			((airline_inf[0] == 'VNA' || airline_inf[0] == 'VNP' || airline_inf[0] == 'BBA') && $("#bk_edit_direction" + ln).val() == 0) // lượt đi
			|| ((airline_inf[1] == 'VNA' || airline_inf[1] == 'VNP' || airline_inf[1] == 'BBA') && $("#bk_edit_direction" + ln).val() == 1) // lượt về
		) {
			if (is_cal_tax) {
				tax_fee = Math.ceil(tax_fee / 1000) * 1000;
			}
			vat_admin = 0;
		}

		if ((airline_inf[0] != 'VNA' && airline_inf[0] != 'VNP' && $("#bk_edit_direction" + ln).val() == 0) || (airline_inf[1] != 'VNA' && airline_inf[1] != 'VNP' && $("#bk_edit_direction" + ln).val() == 1) && is_cal_admin) {
			vat_admin = Math.round(admin_fee / 1.08 * 0.08);
		}
	}

	// Check cái này
	let service_fee = unformatNumber($('#bk_edit_service_fee' + ln).val());
	let airport_fee = unformatNumber($('#bk_edit_airport_fee' + ln).val());
	let supplier_discount = unformatNumber($('#bk_edit_supplier_discount' + ln).val());
	let supplier_ticketing_fee = unformatNumber($('#bk_edit_supplier_ticketing_fee' + ln).val());
	let total_price = 0;
	let total_bought_price = 0;

	// VE QUOC TE
	// if(ticket_type == '2') {
	//	total_price = price + tax_fee + service_fee + admin_fee + airport_fee;
	//	total_bought_price = price + tax_fee + admin_fee + airport_fee;
	// } else {
	total_price = qty * (price + tax_fee + service_fee + admin_fee + airport_fee);
	total_bought_price = qty * (price + tax_fee + admin_fee + airport_fee);
	// }

	if (supplier_discount != 0) {
		total_bought_price = Math.abs(total_bought_price - supplier_discount);
	}

	if (supplier_ticketing_fee != 0) {
		total_bought_price = Math.abs(total_bought_price + supplier_ticketing_fee);
	}

	$('#bk_edit_quantity' + ln).val(qty);
	$('#bk_edit_unit_price' + ln).val(price);
	$('#bk_edit_airport_fee' + ln).val(airport_fee);
	$('#bk_edit_admin_fee' + ln).val(admin_fee);
	$('#bk_edit_service_fee' + ln).val(service_fee);
	$('#bk_edit_total_price' + ln).val(total_price);
	$('#bk_edit_total_bought_price' + ln).val(total_bought_price);
	$('#bk_edit_tax_and_fee' + ln).val(tax_fee);
	if (is_cal_admin) {
		$('#bk_edit_vat_admin' + ln).val(vat_admin);
		$('#bk_edit_admin_fee_no_vat' + ln).val(admin_fee - vat_admin);
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

	var luggage_fee = unformatNumber($.trim($('#luggage_fee').text()));
	var other_fee = unformatNumber($.trim($('#other_fee').text()));
	var thuephi_quocte = unformatNumber($.trim($('#thuephi_quocte').text()));
	var total_amount = subtotal_amt + luggage_fee + other_fee + thuephi_quocte;

	// Hiện tại đã off % discount
	var discount_percent = unformatNumber($('#discount_percent :selected').val());
	var discount_amount = unformatNumber($.trim($('#discount_amount span.discount_value').text()));
	if (discount_percent > 0) {
		discount_amount = total_amount * discount_percent / 100;
	}
	total_amount -= discount_amount;

	// Display
	$('#total_qty').val(formatNumber(total_qty));
	$('#subtotal_amount').val(formatNumber(subtotal_amt));
	$('#total_bought_amount').val(formatNumber(total_bought_amt));
	$('#discount_amount span.discount_value').text(discount_amount);
	$('#bk_edit_total_amount').val(total_amount);
}

function getAirLineInf() {
	var iti_airline = {};

	for (var i = 0; i < $('.dt_airline').length; i++) {
		var airline_name = $("#detail_airline" + i).attr('data-airline');

		if ($("#detail_direction" + i).attr('data-direction') == 0) {
			if (!iti_airline.hasOwnProperty(0)) {
				iti_airline[0] = airline_name;
			} else continue;
		} else if ($("#detail_direction" + i).attr('data-direction') == 1) {
			if (!iti_airline.hasOwnProperty(1)) {
				iti_airline[1] = airline_name;
			} else continue;
		}
	}

	return iti_airline;
}