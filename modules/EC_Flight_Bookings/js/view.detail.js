$(document).ready(function () {
	const formDetailView = $('#formDetailView');
	const bookingId = formDetailView.find('input[name="record"]').val();
	const getResponsiveDialogOptions = function (preferredWidth) {
		const horizontalMargin = 48;
		const verticalMargin = 48;
		const viewportWidth = $(window).width();
		const viewportHeight = $(window).height();

		return {
			width: Math.min(preferredWidth, viewportWidth - horizontalMargin),
			maxWidth: viewportWidth - horizontalMargin,
			maxHeight: viewportHeight - verticalMargin,
			position: { my: 'center', at: 'center', of: window },
			modal: true,
			resizable: false,
			draggable: true
		};
	};

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

	renderPreloadedChangeState();

	// Sửa thông tin hành trình thay đổi nếu có nhập sai
	$(document).on('click', '.edit_iti_row', function () {
		$("#line_itineraries_area").html("<div></div>");
		getItiLine($("form[name='DetailView']>input[name='record']").val(), $(this).attr("data-id"));

		$("#tbl_change_flight_time").dialog({
			...getResponsiveDialogOptions(900),
			title: "Sửa thông tin hành trình",
		});
	});

	// Sửa thông tin hành khách thay đổi nếu có nhập sai
	$(document).on('click', '.edit_pass_row', function () {
		$("#line_itineraries_area").html("<div></div>");
		getPassengerLine($("form[name='DetailView']>input[name='record']").val(), $(this).attr("data-id"), 'edit');

		$("#tbl_change_flight_time").dialog({
			...getResponsiveDialogOptions(1023),
			title: "Sửa thông tin hành khách / hành lý / số vé",
		});
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

	if (booking_status == '8' && is_invoice_input_export == '1') {
		$('#btnCheckInvoiceInputExport').hide();
	}

	// Cancelled booking
	$('#frmCancelled').submit(function () {
		if (!confirm('Bạn có chắc là muốn hủy booking này ?')) return false;
		else return true;
	});

	$(document).on('click', '#update_revenue', function () {
		if (!confirm('Bạn có chắc chắn muốn cập nhật doanh số cho booking này?')) return false;
		else {
			let booking_id = $("input[name='booking_id']").val();

			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				data: {
					booking_id: booking_id,
					for: "updateRevenueBooking",
				},
				type: "POST",
				cache: false,
				success: function (response) {
					if (response == 1) {
						let text_warning = 'Cập nhật doanh số thành công.';
						showModalNotify(1, text_warning);
						$('.modal-overlay, .btn-modal-close').addClass('reload');
					} else {
						let text_warning = 'Cập nhật thất bại. Vui lòng liên hệ IT để được hỗ trợ.';
						showModalNotify(0, text_warning);
						$('.modal-overlay, .btn-modal-close').addClass('reload');
					}
				}
			});
		};
	});

	$(document).on('click', '#btnAutoMapping', function () {
		let booking_id = $(this).attr('booking_id');
		let booking_name = $(this).attr('booking_name');
		let phone = $(this).attr('phone');

		$.ajax({
			url: "index.php?entryPoint=entryPointCallContact",
			data: {
				booking_id: booking_id,
				booking_name: booking_name,
				phone: phone,
				type: "map_call_booking_auto",
			},
			type: "POST",
			cache: false,
			success: function (response) {
				if (response == 1) {
					let text_warning = 'Liên kết cuộc gọi thành công.';
					showModalNotify(1, text_warning);
					$('.modal-overlay, .btn-modal-close').addClass('reload');
				} else if (response == 2) {
					showModalNotify(2, "Vui lòng cập nhật thông tin cuộc gọi trước khi liên kết");
				} else if (response == 400) {
					showModalNotify(2, "SĐT hoặc ID của Booking không xác định. Kiểm tra lại thông tin SĐT hoặc liên hệ IT để được hỗ trợ!");
				} else {
					let text_warning = 'LK thất bại. Không tìm thấy cuộc gọi phù hợp để liên kết.';
					showModalNotify(0, text_warning);
					$('.modal-overlay, .btn-modal-close').addClass('reload');
				}
			}
		});
	});

	$(document).on('click', '#confirm-remind', function () {
		let journey_id = $(this).attr('iti_id');
		let booking_id = $(this).attr('booking_id');

		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			data: {
				journey_id: journey_id,
				booking_id: booking_id,
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

	// Track previous checkin status before change
	$(document).on('focus', 'select.checkin_status_iti', function () {
		$(this).data('prev-val', $(this).val());
	});

	// Change checkin status
	$(document).on("change", "select.checkin_status_iti", function () {
		let $select = $(this);
		let status = $select.find(":selected").val();
		let prevVal = $select.data('prev-val');
		let journey_id = $select.attr('iti_id');
		let journey_name = $select.attr('iti_name');
		let booking_id = $select.attr('booking_id');
		let record_name = $select.attr('record_name');

		function doChangeStatus() {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				data: {
					status: status,
					journey_id: journey_id,
					journey_name: journey_name,
					booking_id: booking_id,
					record_name: record_name,
					for: "changeCheckinStatus",
				},
				type: "POST",
				cache: false,
				success: function (response) {
					if (response == 1) {
						setTimeout(() => { location.reload(); }, 150);
					} else {
						$select.val(prevVal);
						let text_warning = 'Lỗi khi thực hiện thay đổi trạng thái checkin. Vui lòng liên hệ IT để được hỗ trợ.';
						showModalNotify(0, text_warning);
						$('.modal-overlay, .btn-modal-close').addClass('reload');
					}
				}
			});
		}

		if (status == '1') {
			// Cần checkin — hiện modal nhập ghi chú
			let $modal = $('#checkinNoteModal');
			let existingNotes = $select.data('notes') || '';
			$('#checkinNoteText').val(existingNotes);
			$modal.removeData('saved');

			let bsModal = new bootstrap.Modal($modal[0]);
			bsModal.show();

			$modal.off('hidden.bs.modal').on('hidden.bs.modal', function () {
				if (!$(this).data('saved')) {
					$select.val(prevVal);
				}
			});

			$('#btnSaveCheckinNote').off('click').on('click', function () {
				let notes = $('#checkinNoteText').val().trim();
				if (!notes) {
					$modal.data('saved', true);
					bsModal.hide();
					doChangeStatus();
					return;
				}
				$.ajax({
					type: 'POST',
					url: 'index.php?entryPoint=entryPointFlightBookings',
					data: { for: 'saveItineraryNotes', itinerary_id: journey_id, notes: notes },
					dataType: 'json',
					success: function (resp) {
						if (resp.success) {
							$select.data('notes', notes);
							$modal.data('saved', true);
							bsModal.hide();
							doChangeStatus();
						} else {
							alert('Lỗi khi lưu ghi chú: ' + (resp.message || ''));
						}
					},
					error: function () {
						alert('Lỗi khi lưu ghi chú!');
					}
				});
			});
		} else {
			// Đã checkin hoặc reset — giữ logic confirm cũ
			if (!confirm('Thay đổi trạng thái checkin?')) {
				$select.val(prevVal);
				return false;
			}
			doChangeStatus();
		}
	});

	// Open form send mail
	$('#btnSendMail').on('click', function () {
		$('#frmContinueSendMail').css('display', 'block');
		$(this).hide();
	});

	// Preview form send mail
	$('#btnPreviewSendMail').on('click', function () {
		$("#dialog_mail_confirm_preview").dialog({
			title: "Xác nhận thông tin",
			width: 700,
			modal: true,
			resizable: false,
			position: {
				my: "center top",
				at: "center top+50",
				of: window
			}
		});

		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: {
				"booking_id": $(this).attr('booking_id'),
				"for": "previewSendMail",
			},
			beforeSend: function () {
				$("#dialog_mail_confirm_preview").html('');
			},
			success: function (response) {
				$("#dialog_mail_confirm_preview").html(response);
			}
		});
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
		// var timesChangeIti = $(this).attr('data-times-change');
		$('#what_form').val($(this).closest('form[name="frmPrintEticket"]').attr('id'));
		$(`#frmPrintEticket${ln}`).attr('target', '_blank');
		$(`#frmPrintEticket${ln}`).attr('action', 'index.php?print=true');
		$(`#frmPrintEticket${ln} input:hidden[name="action"]`).val('printeticket');
		$(`#frmPrintEticket${ln} input:hidden[name="return_action"]`).val('');

		let checkBoxPassengers = '';
		$('table#tbl_pax tbody tr.psg-line:not(.luggage)').each(function (index, element) {
			let passId = $(this).attr('data-id');
			let passName = $(this).find('td.passenger_name .fullname').text();
			checkBoxPassengers += `<div class="form-check">
				<input class="form-check-input" type="checkbox" id="pass${passId}" name="passenger_list_print_eticket[]" value="${passId}" checked />
				<label class="form-check-label" for="pass${passId}" style="vertical-align:sub;">${passName}</label>
			</div>`;
		});
		$('#dlgChonNgonNgu .option-passenger').html(checkBoxPassengers);

		$('#dlgChonNgonNgu').dialog({
			height: 80,
			width: 320,
			modal: true,
			resizable: false
		});
		Set_Cookie('showLeftCol', 'false', 30, '/', '', '');
	});

	// Send mail eticket button
	$(document).on('click', 'input[name="btnSendEticket"]', function (index, element) {
		var ln = $(this).attr('ln');
		// var timesChangeIti = $(this).attr('data-times-change');
		$('#what_form').val($(this).closest('form[name="frmPrintEticket"]').attr('id'));
		$(`#frmPrintEticket${ln}`).attr('target', '_self');
		$(`#frmPrintEticket${ln}`).attr('action', 'index.php?print=false');
		$(`#frmPrintEticket${ln} input:hidden[name="action"]`).val('sendeticket');
		$(`#frmPrintEticket${ln} input:hidden[name="return_action"]`).val('DetailView');

		let checkBoxPassengers = '';
		$('table#tbl_pax tbody tr.psg-line:not(.luggage)').each(function () {
			// if(ln > 0 && index + 1 < ln) return true; // Skip

			// let timesChangePass = $(this).attr('data-times-change');
			// if(timesChangeIti !== timesChangePass) return true; // Skip

			let passId = $(this).attr('data-id');
			let passName = $(this).find('td.passenger_name .fullname').text();
			checkBoxPassengers += `<div class="form-check">
				<input class="form-check-input" type="checkbox" id="pass${passId}" name="passenger_list_print_eticket[]" value="${passId}" checked />
				<label class="form-check-label" for="pass${passId}" style="vertical-align:sub;">${passName}</label>
			</div>`;
		});
		$('#dlgChonNgonNgu .option-passenger').html(checkBoxPassengers);

		$('#dlgChonNgonNgu').dialog({
			height: 80,
			width: 320,
			modal: true,
			resizable: false
		});
	});

	$(document).on('click', '#btnChonNgonNgu', function () {
		let what_form = '#' + $('#what_form').val();
		let lang = $('input:radio[name="ngonngu"]:checked').val();
		let khuhoi = $('#khuhoi').is(':checked') ? 1 : 0;
		let new_version = $('#new_version').is(':checked') ? 1 : 0;
		let wayflight = $(`${what_form} input:hidden[name="direction"]`).val(); // 0:dep 1:ret
		let checkedPassIds = $("input[name='passenger_list_print_eticket[]']:checked").map(function () {
			return $(this).val();
		}).get();
		let listPassengers = encodeURIComponent(checkedPassIds.join(','));

		let currentAction = $(what_form).find('input[name="action"]').val();
		if (new_version) {
			if (!currentAction.includes("new")) $(what_form).find('input[name="action"]').val(`${currentAction}new`);
			$(what_form).attr('action', $(what_form).attr('action') + `&lang=${lang}&isRoundTrip=${khuhoi}&listPassengers=${listPassengers}`);
		}
		else {
			$(what_form).find('input[name="action"]').val(currentAction.replace("new", ""));
			$(what_form).attr('action', $(what_form).attr('action') + `&lang=${lang}&khuhoi=${khuhoi}&wayflight=${wayflight}&listPassengers=${listPassengers}`);
		}
		$(what_form).submit();
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

	$('#btn-open-mobile-menu').click(function () {
		$('.message_list').scrollTop($('.message_list')[0].scrollHeight);
	});

	$('#icon-send-notes').click(function () {
		let name = $('#note-name').val();
		let parent_id = $('#note-parent-id').val();
		let booking_status = $('#note-booking-status').val();
		let description = $('#note-description').val().trim();
		let username = $('#note-username').val();
		let contact_name = $('#note-contact-name').val();
		let total_amount = $('#note-total-amount').val();
		let total_qty = $('#note-total-qty').val();
		let customer_source = $('#note-customer-source').val();
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
				type: "ADD",
				name: name,
				parent_id: parent_id,
				description: description,
				booking_status: booking_status,
				contact_name: contact_name,
				total_amount: total_amount,
				total_qty: total_qty,
				customer_source: customer_source
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
			let complete_ok = parseInt($(`#${frmSaveWorkingProcess} input:hidden[name="complete_ok"]`).val());
			if (!complete_ok) {
				showModalNotify(2, "Vui lòng điền đầy đủ giá bán hành lý trước khi hoàn tất");
				return;
			}
			$(this).submit();
		}
		else {
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

		if ($(`#${frmSaveWorkingProcess} input:hidden[name="booking_status"]`).val() == '8') {
			$(`#${frmSaveWorkingProcess} input:hidden[name="lydothangthua_id"]`).val($('input:radio[name="radWinLoseReason"]:checked').val());
			$(`#${frmSaveWorkingProcess} input:hidden[name="ghichuthangthua"]`).val($.trim($('#txtWorkingProcessNote').val()));
		}

		if ($(`#${frmSaveWorkingProcess} input:hidden[name="bonus"]`).length > 0) {
			$(`#${frmSaveWorkingProcess} input:hidden[name="bonus"]`).val($.trim($('#txtBonus').val()));
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
			var save_post_data = $(`#${frmSaveWorkingProcess}`).serialize();
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
						$(`#${frmSaveWorkingProcess}`).unbind('submit');
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
			...getResponsiveDialogOptions(1350),
			title: "Chi tiết booking",
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
				$("#line_itineraries_area").html("<center><i>Vui lòng chờ trong giây lát...</i></center>");
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
			...getResponsiveDialogOptions(1023),
			title: "Đối ngày bay / hành trình / hành khách / hành lý / số vé / code vé",
		});
	});

	$(document).on('change', 'select[name="pass_luggage_ob[]"], select[name="pass_luggage_ib[]"]', function () {
		let name = $(this).attr('name'); // name="pass_luggage_ob[]" or "...ib[]"
		let index = $(`select[name="${name}"]`).index(this);
		let dataCost = $(this).find(':selected').data('cost'); // get data-cost

		// Update luggage_price[] at same index
		if (name == 'pass_luggage_ob[]') {
			$('input[name="pass_luggage_price[]"]').eq(index).val(formatNumber(dataCost));
		}
		else if (name == 'pass_luggage_ib[]') {
			$('input[name="pass_luggage_price_inbound[]"]').eq(index).val(formatNumber(dataCost));
		}
	});

	$("#tbl_change_flight_time").on("submit", function (event) {
		if (!checkLineItems(3)) return false;
		return true;
	});
	// End change flight time

	$(document).on("focus", ".allow-number-only", function () {
		var dec_seperator = $('#dec_seperator').val();
		var grp_seperator = $('#grp_seperator').val();
		var sig_digits = $('#sig_digits').val();
		$('.allow-number-only').number(true, sig_digits, dec_seperator, grp_seperator);
	});

	$(document).on("change", "#receipt_type", function () {
		const isCash = $(this).val() === "cash";
		$("#com_location_id").toggle(isCash);
		$("#tknganhang_id").toggle(!isCash);
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

	// QR code dialog
	$('#get_qr_code').on('click', function () {
		// Reset về trạng thái ban đầu mỗi lần mở dialog
		$('#select_bank_get_qr_code').val('');
		$('#img_qr_code').attr('src', '');
		$('#qr_placeholder').show();
		$('#copyQRCodeImage').prop('disabled', true);
		showDialog("dialog_qr_code");
	});

	// Khi đổi ngân hàng: reset QR, yêu cầu tạo lại
	$('#select_bank_get_qr_code').change(function () {
		$('#img_qr_code').attr('src', '');
		$('#qr_placeholder').show();
		$('#copyQRCodeImage').prop('disabled', true);
	});

	// Tạo mã QR: build URL VietQR từ bank data-attributes + số tiền
	$(document).on("click", "#btnRenderQRCode", function () {
		const $option = $('#select_bank_get_qr_code option:selected');
		const bankID     = $option.val();
		const accountNo  = $option.data('account');
		const accountName = $option.data('name');
		const addInfo    = $('#dialog_qr_code').data('addinfo') || '';
		const amount     = parseInt($('#new_payment_amount').val()) || 0;

		if (!bankID) {
			showToastNotify('error', 'Vui lòng chọn tài khoản ngân hàng');
			return;
		}
		if (!amount || amount < 1000) {
			showToastNotify('error', 'Số tiền không hợp lệ (tối thiểu 1.000 VND)');
			return;
		}

		const url = `https://img.vietqr.io/image/${bankID}-${accountNo}-compact2.jpg`
			+ `?amount=${amount}`
			+ `&addInfo=${encodeURIComponent(addInfo)}`
			+ `&accountName=${encodeURIComponent(accountName)}`;

		const $img = $('#img_qr_code');
		$img.off('load error').on('load', function () {
			$('#qr_placeholder').hide();
			$('#copyQRCodeImage').prop('disabled', false);
			showToastNotify('success', 'Tạo mã QR thành công');
		}).on('error', function () {
			showToastNotify('error', 'Không thể tải mã QR từ VietQR, kiểm tra lại');
			$('#qr_placeholder').show();
			$('#copyQRCodeImage').prop('disabled', true);
		});
		$img.attr('src', url);
	});

	// Sao chép ảnh QR vào clipboard
	$(document).on("click", "#copyQRCodeImage", async function () {
		const img = document.getElementById("img_qr_code");
		if (!img || !img.src || !img.src.startsWith('http')) {
			showToastNotify("error", "Chưa có mã QR để sao chép");
			return;
		}
		try {
			const response = await fetch(img.src);
			const blob = await response.blob();
			await navigator.clipboard.write([new ClipboardItem({ "image/png": blob })]);
			showToastNotify("success", "Đã sao chép ảnh QR Code");
		} catch (error) {
			showToastNotify("error", "Không thể sao chép ảnh!");
			console.error("Lỗi:", error);
		}
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
		if (call_name.length === 0 || call_name.length > 20 || booking_id.length === 0) return;

		let alreadyLinked = $('input[name=call_name]').data('already-linked');
		let linkedBooking = $('input[name=call_name]').data('linked-booking') || '';

		function doMapCall(force) {
			$.ajax({
				url: "index.php?entryPoint=entryPointCallContact",
				data: { type: "map_call_booking", call_name, booking_id, booking_name, force },
				type: "POST",
				cache: false,
				success: function (response) {
					response = $.trim(response);
					if (response == 1) {
						showModalNotify(1, "Liên kết cuộc gọi thành công");
						$('.modal-overlay, .btn-modal-close').addClass('reload');
					} else if (response == 2) {
						showModalNotify(2, "Vui lòng cập nhật thông tin cuộc gọi trước khi liên kết");
					} else if (response == 3) {
						if (confirm("Cuộc gọi này đã liên kết với BK, bạn có chắc chắn liên kết không?")) {
							doMapCall(1);
						}
					} else {
						showModalNotify(0, "Thao tác không thành công. Không tìm thấy cuộc gọi để liên kết");
					}
				}
			});
		}

		if (alreadyLinked === '1') {
			let confirmMsg = 'Cuộc gọi này đã liên kết với BK' + (linkedBooking.length > 0 ? ' ' + linkedBooking : '') + ', bạn có chắc chắn liên kết không?';
			if (!confirm(confirmMsg)) return;
			doMapCall(1);
		} else {
			doMapCall(0);
		}
	});

	// Auto-search related calls when manualLinkCall modal opens
	$('#manualLinkCall').on('shown.bs.modal', function () {
		// Reset state on every open
		$('input[name=call_name]').val('').removeData('already-linked').removeData('linked-booking');
		$('#suggested-calls-list').empty();
		$('#suggested-calls-container').hide();

		let phone = $('#btn-mapping-call-booking').attr('phone');
		if (!phone || phone.trim().length === 0) return;

		$('#suggested-calls-container').show();
		$('#suggested-calls-list').html('<p class="text-muted small">Đang tìm kiếm...</p>');

		$.ajax({
			url: "index.php?entryPoint=entryPointCallContact",
			type: "POST",
			dataType: "json",
			data: { type: "search_calls_by_phone", phone: phone.trim() },
			cache: false,
			success: function (calls) {
				$('#suggested-calls-list').empty();
				if (!Array.isArray(calls) || calls.length === 0) {
					$('#suggested-calls-container').hide();
					return;
				}
				let html = '<div class="list-group list-group-flush">';
				$.each(calls, function (i, call) {
					let alreadyLinked = (call.booking_id && call.booking_id.length > 0) ? '1' : '0';
					let linkedBookingName = call.booking_name_linked ? call.booking_name_linked : '';
					let linkedBadge = alreadyLinked === '1' ? '<span class="badge bg-warning text-dark ms-2">Đã liên kết: ' + $('<div>').text(linkedBookingName).html() + '</span>' : '';
					html += '<button type="button" class="list-group-item list-group-item-action py-2 mb-2 w-100 suggest-call-item"'
						+ ' data-call-name="' + $('<div>').text(call.name).html() + '"'
						+ ' data-already-linked="' + alreadyLinked + '"'
						+ ' data-linked-booking="' + $('<div>').text(linkedBookingName).html() + '">'
						+ '<span class="fw-semibold">' + $('<div>').text(call.name).html() + '</span>'
						+ '<span class="form-label small ms-2">' + (call.date_start || '') + '</span>'
						+ linkedBadge
						+ '</button>';
				});
				html += '</div>';
				$('#suggested-calls-list').html(html);
			},
			error: function () {
				$('#suggested-calls-container').hide();
			}
		});
	});

	// Clicking a suggestion row populates the input and stores link state
	$(document).on('click', '.suggest-call-item', function () {
		let callName = $(this).attr('data-call-name');
		$('input[name=call_name]').val(callName).data('already-linked', $(this).attr('data-already-linked')).data('linked-booking', $(this).attr('data-linked-booking'));
		$('.suggest-call-item').removeClass('active');
		$(this).addClass('active');
	});

	// Voucher
	$(document).on('click', '.voucher', function () {
		let id = $(this).attr('for');

		$(`#${id}`).dialog({
			width: 500,
			modal: true,
			resizable: false,
			closeOnEscape: false,
			title: "Chi tiết voucher"
		});
	});

	$('.btn-use-voucher').on('click', function () {
		let id = $(this).attr('for');
		let bookingId = $(this).attr('booking_id') || $('#btn_apply_booking_voucher').attr('booking_id') || $(`#${id}`).attr('booking_id');

		if (!bookingId) {
			showBookingVoucherToast("error", "Không lấy được thông tin booking. Vui lòng tải lại trang và thử lại.");
			return;
		}

		$('#apply_voucher_code').val('');
		resetBookingVoucherState(bookingId);
		setBookingVoucherMessage('<p class="booking-voucher-empty">Đang tải danh sách mã giảm giá phù hợp...</p>');

		$(`#${id}`).dialog({
			width: 390,
			modal: true,
			resizable: false,
			closeOnEscape: false,
			title: "Chọn Voucher"
		});

		loadBookingVoucherOptions(bookingId);
	});

	function escapeBookingVoucherHtml(text) {
		return $('<div>').text(text || '').html();
	}

	function setBookingVoucherMessage(html) {
		$('#apply_voucher_message').html(html);
	}

	function showBookingVoucherToast(type, message) {
		if (typeof showToastNotify === 'function') {
			showToastNotify(type, message);
			return;
		}

		if (typeof showToastWarning === 'function') {
			showToastWarning(message);
			return;
		}

		alert(message);
	}

	const BOOKING_VOUCHER_ENDPOINT = "index.php?entryPoint=entryPointVoucher";
	const BOOKING_VOUCHER_ACTIONS = {
		list: "list_booking_vouchers",
		validate: "validate_booking_voucher",
		apply: "apply_booking_voucher",
		remove: "remove_booking_voucher",
		save: "save_booking_vouchers"
	};
	const BOOKING_VOUCHER_SELECTORS = {
		dialog: '#dialog_apply_voucher',
		discountValue: '#discount_amount span.discount_value',
		totalAmount: '#total_amount',
		selectedCount: '#booking_voucher_selected_count',
		selectedAmount: '#booking_voucher_selected_amount'
	};

	let bookingVoucherState = {
		bookingId: '',
		applied: {},
		selected: {},
		options: {}
	};

	function decodeBookingVoucherPayload(payload) {
		try {
			return JSON.parse(decodeURIComponent(atob(payload || ''))) || [];
		} catch (e) {
			return [];
		}
	}

	function encodeBookingVoucherPayload(vouchers) {
		try {
			return btoa(encodeURIComponent(JSON.stringify(vouchers || [])));
		} catch (e) {
			return '';
		}
	}

	function normalizeBookingVoucherItem(voucher) {
		let discountAmount = parseInt(voucher.discount_amount || 0, 10);
		let voucherId = voucher.voucher_id || voucher.id || '';
		return {
			key: voucherId || voucher.code || voucher.name || '',
			voucher_id: voucherId,
			code: voucher.code || voucher.name || '',
			type: voucher.type || 'single',
			campaign_name: voucher.campaign_name || '',
			end_time: voucher.end_time || '',
			reduce_amount: parseInt(voucher.reduce_amount || 0, 10) || 0,
			reduce_percent: parseInt(voucher.reduce_percent || 0, 10) || 0,
			max_discount: parseInt(voucher.max_discount || 0, 10) || 0,
			discount_type: voucher.discount_type || (parseInt(voucher.reduce_percent || 0, 10) > 0 ? 'percent' : 'amount'),
			discount_amount: isNaN(discountAmount) ? 0 : discountAmount,
			selectable: voucher.selectable !== false,
			disabled_reason: voucher.disabled_reason || '',
			condition_voucher: voucher.condition_voucher || {},
			applied_invalid_reason: voucher.applied_invalid_reason || '',
			applied: !!voucher.applied
		};
	}

	function getBookingVoucherByKey(voucherKey) {
		return bookingVoucherState.options[voucherKey] || null;
	}

	function formatBookingVoucherExpiry(endTime) {
		if (!endTime) {
			return 'Không giới hạn';
		}

		let datePart = String(endTime).split(' ')[0];
		let parts = datePart.split('-');
		if (parts.length !== 3) {
			return escapeBookingVoucherHtml(endTime);
		}

		return `${parts[2]}-${parts[1]}-${parts[0]}`;
	}

	function formatBookingVoucherConditionLines(condition) {
		let lines = [];
		condition = condition || {};

		if (condition.for_phone_value) {
			lines.push(`Áp dụng cho SĐT ${escapeBookingVoucherHtml(condition.for_phone_value)}`);
		}
		if (parseInt(condition.min_order_value || 0, 10) > 0) {
			lines.push(`Đơn tối thiểu ${formatNumber(condition.min_order_value)} VND`);
		}
		if (parseInt(condition.number_of_tickets || 0, 10) > 0) {
			lines.push(`Booking từ ${escapeBookingVoucherHtml(condition.number_of_tickets)} vé trở lên`);
		}
		if (condition.flight_type) {
			lines.push(`Chỉ áp dụng cho chuyến ${condition.flight_type === 'domestic' ? 'Nội địa' : 'Quốc tế'}`);
		}
		if (parseInt(condition.ticket_type || 0, 10) > 0) {
			lines.push(`Chỉ áp dụng cho vé ${parseInt(condition.ticket_type, 10) === 2 ? 'Khứ hồi' : 'Một chiều'}`);
		}
		if (condition.journey && String(condition.journey).length > 6) {
			lines.push(`Hành trình áp dụng: ${escapeBookingVoucherHtml(String(condition.journey).replace(/,/g, ', '))}`);
		}

		return lines;
	}

	function getBookingVoucherDiscountLabel(voucher) {
		if (voucher.discount_type === 'percent' && voucher.reduce_percent > 0) {
			let label = `Giảm ${formatNumber(voucher.reduce_percent)}%`;
			if (voucher.max_discount > 0) {
				label += `, tối đa ${formatNumber(voucher.max_discount)}đ`;
			}
			return label;
		}

		return `Giảm ${formatNumber(voucher.reduce_amount || voucher.discount_amount || 0)}đ`;
	}

	function getBookingVoucherTypeClass(voucher) {
		return voucher.discount_type === 'percent' ? 'percent' : 'amount';
	}

	function getBookingVoucherTypeIcon(voucher) {
		return voucher.discount_type === 'percent' ? '%' : '₫';
	}

	function renderBookingVoucherBadge(condition, className, label) {
		return condition ? `<span class="${className}">${label}</span>` : '';
	}

	function renderBookingVoucherCondition(voucherKey) {
		let voucher = getBookingVoucherByKey(voucherKey);
		if (!voucher) {
			return;
		}

		let title = escapeBookingVoucherHtml(voucher.campaign_name || voucher.code || '');
		let code = escapeBookingVoucherHtml(voucher.code || '');
		let expiryDate = escapeBookingVoucherHtml(formatBookingVoucherExpiry(voucher.end_time || ''));
		let discountLabel = escapeBookingVoucherHtml(getBookingVoucherDiscountLabel(voucher));
		let conditionLines = formatBookingVoucherConditionLines(voucher.condition_voucher);
		let conditionHtml = conditionLines.length > 0
			? conditionLines.map(function (line) {
				return `<li>${line}</li>`;
			}).join('')
			: '<li>Không có điều kiện bổ sung.</li>';

		setBookingVoucherMessage(`
			<div class="booking-voucher-condition-view">
				<button type="button" class="booking-voucher-back" id="btn_back_booking_voucher_list">← Quay lại</button>
				<div class="booking-voucher-condition-card">
					<div class="booking-voucher-condition-heading">
						<span class="booking-voucher-type ${getBookingVoucherTypeClass(voucher)}">${getBookingVoucherTypeIcon(voucher)}</span>
						<div>
							<strong>${title}</strong>
							<p>Mã: ${code}</p>
						</div>
					</div>
					<ul class="booking-voucher-condition-lines">
						<li>${discountLabel}</li>
						<li>HSD: ${expiryDate}</li>
						${conditionHtml}
					</ul>
				</div>
			</div>
		`);
	}

	function upsertBookingVoucherOption(voucher, selected) {
		let item = normalizeBookingVoucherItem(voucher);
		if (!item.key || !item.code) return;

		let existing = bookingVoucherState.options[item.key];
		let isAlreadyApplied = !!(existing && existing.applied) || !!bookingVoucherState.applied[item.key];
		if (isAlreadyApplied) {
			item.selectable = true;
			item.disabled_reason = '';
		}

		bookingVoucherState.options[item.key] = $.extend({}, existing || {}, item, {
			applied: isAlreadyApplied
		});

		if (selected && item.selectable) {
			selectBookingVoucherItem(item.key);
		} else if (bookingVoucherState.selected[item.key]) {
			bookingVoucherState.selected[item.key] = bookingVoucherState.options[item.key];
		}
	}

	function selectBookingVoucherItem(voucherKey) {
		let voucher = getBookingVoucherByKey(voucherKey);
		if (!voucher || voucher.selectable === false) {
			return;
		}

		// Only one voucher per code can be selected.
		Object.keys(bookingVoucherState.selected).forEach(function (selectedKey) {
			let selectedVoucher = bookingVoucherState.selected[selectedKey];
			if (selectedVoucher.code === voucher.code) {
				delete bookingVoucherState.selected[selectedKey];
			}
		});

		bookingVoucherState.selected[voucherKey] = voucher;
	}

	function resetBookingVoucherState(bookingId) {
		bookingVoucherState = {
			bookingId: bookingId,
			applied: {},
			selected: {},
			options: {}
		};

		let appliedVouchers = decodeBookingVoucherPayload($(BOOKING_VOUCHER_SELECTORS.dialog).attr('data-applied-vouchers'));
		appliedVouchers.forEach(function (voucher) {
			let item = normalizeBookingVoucherItem(voucher);
			if (!item.key || !item.code) {
				return;
			}

			item.applied = true;
			bookingVoucherState.applied[item.key] = item;
			bookingVoucherState.options[item.key] = item;
			bookingVoucherState.selected[item.key] = item;
		});

		updateBookingVoucherFooter();
	}

	function getBookingVoucherItems() {
		return Object.keys(bookingVoucherState.options).map(function (voucherKey) {
			return bookingVoucherState.options[voucherKey];
		}).sort(function (firstVoucher, secondVoucher) {
			let firstApplied = bookingVoucherState.applied[firstVoucher.key] ? 1 : 0;
			let secondApplied = bookingVoucherState.applied[secondVoucher.key] ? 1 : 0;
			if (firstApplied !== secondApplied) {
				return secondApplied - firstApplied;
			}

			let firstSelectable = firstVoucher.selectable !== false ? 1 : 0;
			let secondSelectable = secondVoucher.selectable !== false ? 1 : 0;
			if (firstSelectable !== secondSelectable) {
				return secondSelectable - firstSelectable;
			}

			let firstDiscount = parseInt(firstVoucher.discount_amount || 0, 10) || 0;
			let secondDiscount = parseInt(secondVoucher.discount_amount || 0, 10) || 0;
			if (firstDiscount !== secondDiscount) {
				return secondDiscount - firstDiscount;
			}

			return String(firstVoucher.code || '').localeCompare(String(secondVoucher.code || ''));
		});
	}

	function updateBookingVoucherFooter() {
		let selectedItems = Object.keys(bookingVoucherState.selected).map(function (voucherKey) {
			return bookingVoucherState.selected[voucherKey];
		});
		let totalDiscount = selectedItems.reduce(function (sum, item) {
			return sum + (parseInt(item.discount_amount || 0, 10) || 0);
		}, 0);

		$(BOOKING_VOUCHER_SELECTORS.selectedCount).text(selectedItems.length);
		$(BOOKING_VOUCHER_SELECTORS.selectedAmount).text(formatNumber(totalDiscount) + 'đ');
	}

	function buildAppliedBookingVoucherTags(vouchers) {
		if (!vouchers || vouchers.length === 0) {
			return '';
		}

		let html = '<div class="wrap-voucher">';
		vouchers.forEach(function (voucher) {
			let voucherId = escapeBookingVoucherHtml(voucher.voucher_id || '');
			let code = escapeBookingVoucherHtml(voucher.code || '');
			let type = escapeBookingVoucherHtml(voucher.type || 'single');
			let campaignName = escapeBookingVoucherHtml(voucher.campaign_name || '');
			let discountAmount = formatNumber(voucher.discount_amount || 0);
			let invalidReason = escapeBookingVoucherHtml(voucher.applied_invalid_reason || '');
			let invalidBadge = invalidReason
				? `<span class="voucher-invalid-badge" title="${invalidReason}">!</span>`
				: '';
			let dialogId = `dialog_voucher_detail_${voucherId}`;
			html += `
				<a class="${type}-voucher voucher" for="${dialogId}" title="Xem chi tiết">
					<span class="code">${code}</span>
					${invalidBadge}
				</a>
				<dialog id="${dialogId}" class="dialog dialog-voucher-detail" style="display:none; border-radius:0">
					<ul class="voucher-list-items">
						<li class="voucher-item">
							<span class="label">Sự kiện/Chiến dịch:</span>
							<span class="value">${campaignName}</span>
						</li>
						<li class="voucher-item voucher-item-code">
							<span class="label">Mã giảm giá:</span>
							<a class="value" href="index.php?module=EC_Vouchers&action=DetailView&record=${voucherId}" target="_blank">
								<span class="me-1">${code}</span>
							</a>
						</li>
						<li class="voucher-item voucher-item-discount-amount">
							<span class="label">Số tiền được giảm:</span>
							<span class="value">${discountAmount} VND</span>
						</li>
						${invalidReason ? `
							<li class="voucher-item voucher-item-invalid">
								<span class="label">Cảnh báo:</span>
								<span class="value">${invalidReason}</span>
							</li>
						` : ''}
					</ul>
				</dialog>
			`;
		});
		html += '</div>';

		return html;
	}

	function updateBookingVoucherStateAfterSave(data) {
		let appliedVouchers = data.applied_vouchers || [];
		$(BOOKING_VOUCHER_SELECTORS.dialog).attr('data-applied-vouchers', encodeBookingVoucherPayload(appliedVouchers));
		let discountValue = $(BOOKING_VOUCHER_SELECTORS.discountValue);
		if (!discountValue.length) {
			discountValue = $('span.discount_value').first();
		}
		let discountContainer = discountValue.closest('#discount_amount');
		if (!discountContainer.length) {
			discountContainer = discountValue.parent();
		}
		discountValue.text(formatNumber(data.discount_amount || 0));
		discountContainer.find('.wrap-voucher').remove();
		discountValue.after(buildAppliedBookingVoucherTags(appliedVouchers));

		let totalAmount = formatNumber(data.total_amount || 0);
		let totalAmountField = $(BOOKING_VOUCHER_SELECTORS.totalAmount);
		if (totalAmountField.is('input')) {
			totalAmountField.val(totalAmount);
		} else if (totalAmountField.find('.sugar_field').length) {
			totalAmountField.find('.sugar_field').text(totalAmount);
		} else {
			totalAmountField.text(totalAmount);
		}
		resetBookingVoucherState(data.booking_id || bookingVoucherState.bookingId);
		renderBookingVoucherList();
	}

	function renderBookingVoucherItem(voucher) {
		let title = escapeBookingVoucherHtml(voucher.campaign_name || voucher.code || '');
		let voucherKey = escapeBookingVoucherHtml(voucher.key || '');
		let isSelected = !!bookingVoucherState.selected[voucher.key];
		let isDisabled = voucher.selectable === false;
		let disabledReason = escapeBookingVoucherHtml(voucher.disabled_reason || 'Không áp dụng cho booking này');
		let bestBadge = renderBookingVoucherBadge(voucher.is_best_in_group, 'booking-voucher-best-badge', 'Tốt nhất');
		let appliedBadge = renderBookingVoucherBadge(voucher.applied, 'booking-voucher-applied-badge', 'Đang áp dụng');
		let invalidReason = escapeBookingVoucherHtml(voucher.applied_invalid_reason || '');
		let invalidBadge = renderBookingVoucherBadge(invalidReason, 'booking-voucher-invalid-badge', invalidReason);
		let appliedStatusBadge = appliedBadge
			? `<div class="booking-voucher-status-badges">${appliedBadge}</div>`
			: '';
		let invalidStatusBadge = invalidBadge
			? `<div class="booking-voucher-bottom-badges">${invalidBadge}</div>`
			: '';

		return `
			<li class="booking-voucher-item ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}"
				data-voucher-key="${voucherKey}"
				title="${isDisabled ? disabledReason : ''}">
				${appliedStatusBadge}
				${invalidStatusBadge}
				<div class="booking-voucher-icon ${getBookingVoucherTypeClass(voucher)}">${getBookingVoucherTypeIcon(voucher)}</div>
				<div class="booking-voucher-info">
					<strong>${title} ${bestBadge}</strong>
					<small>${escapeBookingVoucherHtml(getBookingVoucherDiscountLabel(voucher))}</small>
					<em>HSD: ${escapeBookingVoucherHtml(formatBookingVoucherExpiry(voucher.end_time || ''))}
						<button type="button" class="booking-voucher-condition-link" data-voucher-key="${voucherKey}">Điều kiện</button>
					</em>
					${isDisabled ? `<b>${disabledReason}</b>` : ''}
				</div>
				<button type="button" class="booking-voucher-check ${isSelected ? 'selected' : ''}"
					data-voucher-key="${voucherKey}"
					title="${isDisabled ? disabledReason : (isSelected ? 'Bỏ chọn' : 'Chọn')}"
					${isDisabled ? 'disabled' : ''}>
					<span class="booking-voucher-check-icon">${isSelected ? '✓' : '+'}</span>
					<span class="booking-voucher-check-text">Bỏ chọn</span>
				</button>
			</li>
		`;
	}

	function renderBookingVoucherList() {
		let vouchers = getBookingVoucherItems();
		if (vouchers.length === 0) {
			setBookingVoucherMessage('<p class="booking-voucher-empty">Không có mã giảm giá phù hợp.</p>');
			updateBookingVoucherFooter();
			return;
		}

		let html = '<ul class="booking-voucher-items">';
		vouchers.forEach(function (voucher) {
			html += renderBookingVoucherItem(voucher);
		});
		html += '</ul>';
		setBookingVoucherMessage(html);
		updateBookingVoucherFooter();
	}

	function renderBookingVoucherOptions(obj) {
		if (obj.error != 0) {
			setBookingVoucherMessage(`<p class="booking-voucher-empty text-danger">${escapeBookingVoucherHtml(obj.message || 'Không tải được danh sách mã giảm giá')}</p>`);
			return;
		}

		(obj.data || []).forEach(function (voucher) {
			upsertBookingVoucherOption(voucher, false);
		});

		// Đảm bảo applied vouchers luôn có trong options dù API không trả về
		Object.keys(bookingVoucherState.applied).forEach(function (key) {
			if (!bookingVoucherState.options[key]) {
				bookingVoucherState.options[key] = bookingVoucherState.applied[key];
			}
		});

		renderBookingVoucherList();
	}

	function loadBookingVoucherOptions(bookingId) {
		$.ajax({
			url: BOOKING_VOUCHER_ENDPOINT,
			data: {
				action: BOOKING_VOUCHER_ACTIONS.list,
				booking_id: bookingId
			},
			type: "POST",
			dataType: "json",
			cache: false,
			success: function (obj) {
				renderBookingVoucherOptions(obj);
			},
			error: function () {
				setBookingVoucherMessage('<p class="text-danger">Không tải được danh sách mã giảm giá. Bạn có thể nhập mã để kiểm tra thủ công.</p>');
			}
		});
	}

	$(document).on('click', '.booking-voucher-condition-link', function (event) {
		event.preventDefault();
		event.stopPropagation();

		let voucherKey = $(this).attr('data-voucher-key');
		renderBookingVoucherCondition(voucherKey);
	});

	$(document).on('click', '#btn_back_booking_voucher_list', function (event) {
		event.preventDefault();
		renderBookingVoucherList();
	});

	$(document).on('click', '.booking-voucher-item, .booking-voucher-check', function (event) {
		event.preventDefault();
		event.stopPropagation();

		if ($(event.target).closest('.booking-voucher-condition-link').length) {
			return;
		}

		let voucherKey = $(this).attr('data-voucher-key') || $(this).closest('.booking-voucher-item').attr('data-voucher-key');
		if (!voucherKey || !bookingVoucherState.options[voucherKey]) {
			return;
		}

		if (bookingVoucherState.options[voucherKey].selectable === false) {
			showBookingVoucherToast("warning", bookingVoucherState.options[voucherKey].disabled_reason || "Mã giảm giá không áp dụng cho booking này");
			return;
		}

		if (bookingVoucherState.selected[voucherKey]) {
			delete bookingVoucherState.selected[voucherKey];
		} else {
			selectBookingVoucherItem(voucherKey);
		}

		renderBookingVoucherList();
	});

	$('#btn_check_booking_voucher').click(function () {
		let voucherCode = $.trim($('#apply_voucher_code').val()).toUpperCase();
		let bookingId = $(this).attr('booking_id');

		if (!voucherCode) {
			showBookingVoucherToast("warning", "Vui lòng nhập mã giảm giá");
			return false;
		}

		$.ajax({
			url: BOOKING_VOUCHER_ENDPOINT,
			data: {
				action: BOOKING_VOUCHER_ACTIONS.validate,
				booking_id: bookingId,
				voucher_code: voucherCode
			},
			type: "POST",
			dataType: "json",
			cache: false,
			beforeSend: function () { $('.container-waiting').show(); },
			success: function (obj) {
				$('.container-waiting').hide();
				if (obj.error == 0) {
					upsertBookingVoucherOption(obj.data || {}, true);
					$('#apply_voucher_code').val('');
					renderBookingVoucherList();
					showBookingVoucherToast("success", "Đã chọn mã giảm giá");
					return;
				}

				showBookingVoucherToast("error", obj.message || "Mã giảm giá không hợp lệ");
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				$('.container-waiting').hide();
				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
				showBookingVoucherToast("error", "Lỗi! Liên hệ IT để được hỗ trợ.");
			}
		});
	});

	$('#apply_voucher_code').on('input', function () {
		$(this).val($(this).val().toUpperCase());
	});

	$('#btn_cancel_booking_voucher').click(function () {
		$(BOOKING_VOUCHER_SELECTORS.dialog).dialog('close');
	});

	$('#btn_apply_booking_voucher').click(function () {
		let bookingId = $(this).attr('booking_id');
		let selectedVoucherIds = [];
		let hasChanges = false;

		Object.keys(bookingVoucherState.selected).forEach(function (voucherKey) {
			if (bookingVoucherState.selected[voucherKey].voucher_id) {
				selectedVoucherIds.push(bookingVoucherState.selected[voucherKey].voucher_id);
			}
		});

		Object.keys(bookingVoucherState.applied).forEach(function (voucherKey) {
			if (!bookingVoucherState.selected[voucherKey]) {
				hasChanges = true;
			}
		});
		Object.keys(bookingVoucherState.selected).forEach(function (voucherKey) {
			if (!bookingVoucherState.applied[voucherKey]) {
				hasChanges = true;
			}
		});

		if (!hasChanges) {
			showBookingVoucherToast("warning", "Danh sách mã giảm giá chưa thay đổi");
			return false;
		}

		if (Object.keys(bookingVoucherState.selected).length > 0 && selectedVoucherIds.length === 0) {
			showBookingVoucherToast("error", "Không lấy được ID mã giảm giá. Vui lòng tải lại trang và thử lại.");
			return false;
		}

		$.ajax({
			url: BOOKING_VOUCHER_ENDPOINT,
			data: {
				action: BOOKING_VOUCHER_ACTIONS.save,
				booking_id: bookingId,
				voucher_ids: selectedVoucherIds
			},
			type: "POST",
			dataType: "json",
			cache: false,
			beforeSend: function () {
				$('.container-waiting').show();
				$('#btn_apply_booking_voucher').prop('disabled', true).text('Đang lưu...');
			},
			success: function (obj) {
				$('.container-waiting').hide();
				$('#btn_apply_booking_voucher').prop('disabled', false).text('Xác nhận');
				if (obj.error != 0) {
					showBookingVoucherToast("error", obj.message || "Thao tác không thành công. Liên hệ IT để được hỗ trợ.");
					return;
				}

				updateBookingVoucherStateAfterSave(obj.data || {});
				$(BOOKING_VOUCHER_SELECTORS.dialog).dialog('close');
				showBookingVoucherToast("success", obj.message || "Cập nhật mã giảm giá thành công");
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				$('.container-waiting').hide();
				$('#btn_apply_booking_voucher').prop('disabled', false).text('Xác nhận');
				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
				showBookingVoucherToast("error", "Lỗi! Liên hệ IT để được hỗ trợ.");
			}
		});
	});

	$(document).ajaxError(function (event, jqxhr, settings, thrownError) {
		if (settings && settings.url === BOOKING_VOUCHER_ENDPOINT) {
			if ($('.container-waiting').is(':visible')) {
				$('.container-waiting').hide();
			}
			console.error("Error: " + thrownError);
		}
	});

	// Points
	$('.btn-use-point').on('click', function () {
		let id = $(this).attr('for');

		$(`#${id}`).dialog({
			width: 400,
			modal: true,
			resizable: false,
			closeOnEscape: false,
			title: "Dùng điểm tích lũy"
		});
	});

	$('input[name="point_of_use"]').on('input', function () {
		let p = $(this).val();
		let step = parseInt($(this).attr('min'));
		let ttp = parseInt($('#tt_points').attr('data'));

		if (p < step || p > ttp || p % step != 0) {
			$('#btn_apply_points_discount').prop('disabled', true);
			$('input[name="points_discount"]').val(0);
		}
		else {
			$('#btn_apply_points_discount').prop('disabled', false);
			$('input[name="points_discount"]').val(p * 1000);
		}
	});

	$('#btn_apply_points_discount').click(function () {
		let p = $('input[name="point_of_use"]').val();
		let step = parseInt($('input[name="point_of_use"]').attr('min'));
		let ttp = parseInt($('#tt_points').attr('data'));
		let contact_id = $(this).attr('contact_id');
		let booking_id = $(this).attr('booking_id');

		if (p < step || p > ttp || p % step != 0) {
			showModalNotify(2, "Số điểm áp dụng không hợp lệ");
			return false;
		}

		if (contact_id.length > 0) {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				data: {
					for: "apply_points",
					apply_points: p,
					contact_id: contact_id,
					booking_id: booking_id
				},
				type: "POST",
				cache: false,
				beforeSend: function () { $('.container-waiting').show(); },
				success: function (response) {
					$('.container-waiting').hide();
					try {
						let obj = JSON.parse(response);

						if (obj.error == 0) {
							showModalNotify(1, "Áp điểm thành công");
							$('.modal-overlay, .btn-modal-close').addClass('reload');
						}
						else {
							let m = obj.message ? obj.message : 'Thao tác không thành công. Liên hệ IT để được hỗ trợ.';
							showModalNotify(0, m);
						}
					}
					catch (err) {
						console.error(err);
						showModalNotify(0, "Lỗi! Liên hệ IT để được hỗ trợ.");
					}
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					$('.container-waiting').hide();
					console.error("Status: " + textStatus);
					console.error("Error: " + errorThrown);
					showModalNotify(0, "Lỗi! Liên hệ IT để được hỗ trợ.");
				}
			});
		}
	});

	// Change customer source
	$('input[name="customer_source"]').click(function () {
		let customer_source = $(this).val();
		if (customer_source && customer_source.length > 0) {
			$.ajax({
				url: "index.php?entryPoint=entryPointGeneral",
				type: "POST",
				contentType: "application/json",
				dataType: "json",
				data: JSON.stringify({
					class: "entryBookingClass",
					method: "updateFields",
					params: {
						bookingId: bookingId,
						fields: { customer_source: customer_source }
					}
				}),
				beforeSend: function () {
					$('.container-waiting').show();
				},
				success: function (res) {
					if ('status' in res && res.status === 1) {
						$('input[type="checkbox"][name="customer_source"]').prop('checked', false);
						$(`input#customer_source_${customer_source}`).prop('checked', true);
					}
					else {
						$(`input#customer_source_${customer_source}`).prop('checked', false);
					}
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					$(`input#customer_source_${customer_source}`).prop('checked', false);
					console.error("Status: " + textStatus);
					console.error("Error: " + errorThrown);
					showModalNotify(0, "Lỗi! Liên hệ IT để được hỗ trợ.");
				},
				complete: function () {
					$('.container-waiting').hide();
				},
			});
		}
	});

	// Get location from geocode in booking
	const regexlatlong = /^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/;
	const latlong = $("#city").text().trim();
	if (regexlatlong.test(latlong)) {
		const latlongparts = latlong.split(',');
		if (latlongparts.length != 2) return false;
		const lat = latlongparts[0].trim();
		const long = latlongparts[1].trim();

		$.ajax({
			url: "index.php?entryPoint=entryPointGeneral&class=entryBookingClass&method=getLocation",
			type: "POST",
			contentType: "application/json",
			dataType: "json",
			data: JSON.stringify({
				params: {
					lat: lat,
					long: long,
					bookingId: bookingId,
				}
			}),
			beforeSend: function () {
				$("#city").append(`<i id="location-loading" class="ms-2" style="color:#a7a7a7;">Đang định vị...</i>`);
			},
			success: function (res) {
				$("#location-loading").remove();
				if ('status' in res && res.status === 1) {
					if (res.data.length > 0) $("#city").text(res.data);
				}
				else {
					let message = res.message || 'Có lỗi xảy ra khi lấy dữ liệu';
					$("#city").append(`
						<span class="ms-1" type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" title="${message}">
							<svg width="14px" height="14px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M1 12C1 5.92487 5.92487 1 12 1C18.0751 1 23 5.92487 23 12C23 18.0751 18.0751 23 12 23C5.92487 23 1 18.0751 1 12ZM10.0586 6.05547C10.0268 5.48227 10.483 5 11.0571 5H12.9429C13.517 5 13.9732 5.48227 13.9414 6.05547L13.5525 13.0555C13.523 13.5854 13.0847 14 12.554 14H11.446C10.9153 14 10.477 13.5854 10.4475 13.0555L10.0586 6.05547ZM14 17C14 18.1046 13.1046 19 12 19C10.8954 19 10 18.1046 10 17C10 15.8954 10.8954 15 12 15C13.1046 15 14 15.8954 14 17Z" fill="#ff0000"></path></g></svg>
						</span>
					`);
				}
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
			},
		});
	} else if ($("#city").text().trim() === '') {
		// Fallback - get location from IP
		const ipAddress = $("#ip_address").text().trim();
		if (!ipAddress) return false;

		$.ajax({
			url: "index.php?entryPoint=entryPointGeneral&class=entryBookingClass&method=getLocationByIp",
			type: "POST",
			contentType: "application/json",
			dataType: "json",
			data: JSON.stringify({ params: { ip: ipAddress, bookingId: bookingId } }),
			beforeSend: function () {
				$("#city").append(`<i id="location-loading" class="ms-2" style="color:#a7a7a7;">Đang định vị...</i>`);
			},
			success: function (res) {
				$("#location-loading").remove();
				if ('status' in res && res.status === 1) {
					if (res.data.length > 0) $("#city").text(res.data);
				}
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
			},
		});
	}
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

			// Active select2
			$('.table-change-passengers select.box-select2').select2();
			$('.table-change-passengers select.box-select2-non-search').select2({
				minimumResultsForSearch: Infinity
			});
		}
	});
}

// BUTTON "CHỈNH SỬA CHI TIẾT BOOKING"
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
	total_bought_price = qty * (price + tax_fee + admin_fee + airport_fee);
	total_price = qty * (price + tax_fee + service_fee + admin_fee + airport_fee);
	// }

	if (supplier_discount != 0) {
		total_bought_price = Math.abs(total_bought_price - supplier_discount);
	}

	if (supplier_ticketing_fee != 0) {
		total_bought_price += supplier_ticketing_fee;
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
	// var thuephi_quocte = unformatNumber($.trim($('#thuephi_quocte').text()));
	var thuephi_quocte = 0;
	var total_amount = subtotal_amt + luggage_fee + other_fee + thuephi_quocte;

	// Hiện tại đã off % discount
	// var discount_percent = unformatNumber($('#discount_percent :selected').val());
	var discount_amount = unformatNumber($.trim($('#discount_amount span.discount_value').text()));
	// if (discount_percent > 0) {
	// 	discount_amount = total_amount * discount_percent / 100;
	// }
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

function renderPreloadedChangeState() {
	var $itineraryTable = $("div[data-id='LBL_LINEITINERARIES_PANEL'] table#itinerary_tbl");
	if ($itineraryTable.length && $itineraryTable.find("tbody .edited_iti_line").length === 0) {
		$itineraryTable.find("#no-change__edit-iti").text(" Không có thông tin thay đổi ngày bay.");
	}

	var $passengerTable = $("div[data-id='LBL_LINEPASSENGERS_PANEL'] table#tbl_pax");
	if ($passengerTable.length && $passengerTable.find("tbody .edited_pass_line, tbody .edited_pass_group, tbody .psg-line[data-times-change]").length === 0) {
		$passengerTable.find("#no-change__edit-pass").text("Chưa có hành khách nào thay đổi thông tin.");
	}
}

