$(document).ready(function() {
	var dec_seperator 	= $('#dec_seperator').val();
	var grp_seperator 	= $('#grp_seperator').val();
	var sig_digits 	= $('#sig_digits').val();

	$('.allow-number-only').number( true, sig_digits, dec_seperator, grp_seperator);

	changeInvoiceType();
	$('#sohoadon').attr('maxlength', 8);
	
	// không cho nhấn phím enter trên form
	$('input:text').bind("keypress", function(e) {
		if (e.keyCode == 13) return false;
	});
	
	// thay thế phím TAB bằng ENTER
	var $inputs = $('input:text');
	$inputs.on('keypress', function(e){
		if(e.which === 13){
			var ind = $inputs.index(this);
			$inputs.eq(ind + 1).focus();
			$inputs.eq(ind + 1).select();
		}
	});
	
	$('input:text, textarea').on('click', function(){
		$(this).select();	
	});
	
	// Xử lý sự kiện nhất nút thêm dòng
	$('#btnAddRow').click(function(){
		var ln 	= parseInt($('#row_count').val());
		$('#last-row').before(insertRow(ln, $("#loaihoadon").val()));
		$('#ct_sanpham' + ln).focus(); //vô nghĩa
		$('.allow-number-only').number( true, sig_digits, dec_seperator, grp_seperator );
		calculateLineTotal(ln);

		ln++;
		$('#row_count').val(ln);
		$('#lbl_row_count').text(parseInt($('#lbl_row_count').text()) + 1);
		
		// không cho nhấn phím enter trên form
		$('input:text').bind("keypress", function(e) {
			if (e.keyCode == 13) return false;
		});
		
		// thay thế phím TAB bằng phím ENTER
		var $inputs = $('input:text');
		$inputs.on('keypress', function(e){
			if(e.which === 13){
				var ind = $inputs.index(this);
        		$inputs.eq(ind + 1).focus();
				$inputs.eq(ind + 1).select();
			}
		});
	});

	$(document).on('change', 'select[name="ct_code[]"]', function() {
		const index = $('select[name="ct_code[]"]').index(this);
		const value = $(this).val();

		if(value == 'PHL' || value == 'PD') {
			$(`input[name="ct_receipt_voucher[]"]`).eq(index).attr('type', 'text');
		}
		else {
			$(`input[name="ct_receipt_voucher[]"]`).eq(index).attr('type', 'hidden');
		}
	});

	$("#loaihoadon").change(function() {
		$("#first-row").parent().children().not("#first-row").not("#last-row").remove();
		changeInvoiceType();
	});

	$("#company_unit").change(function() {
		let status_invoice = $('#tinhtrang').val();

		if($(this).val() == 'MHV' && status_invoice == 2){
			$('#sohoadon').attr('readonly', 'readonly');
		} else{
			$('#sohoadon').removeAttr('readonly');
		}
	});

	$.widget('custom.autocomplete', $.ui.autocomplete, {
		options: {
		    open: function (event, ui) {
			   // Hack to prevent a 'menufocus' error when doing sequential searches using only the keyboard
			   $('.ui-autocomplete .ui-menu-item:first').trigger('mouseover');
		    },
		    focus: function (event, ui) {
			   event.preventDefault();
		    }
		},
		_create: function () {
		    this._super();
		    // Using a table makes the autocomplete forget how to menu.
		    // With this we can skip the header row and navigate again via keyboard.
		    this.widget().menu("option", "items", ".ui-menu-item");
		},
		_renderMenu: function (ul, items) {
		    var self = this;
		    var $table = $('<table class="table-autocomplete table-autocomplete__hoadonban table-details__booking">'),
			   $thead = $('<thead>'),
			   $headerRow = $('<tr>'),
			   $tbody = $('<tbody>');
	 
		    $.each(self.options.columns, function (index, columnMapping) {
			   $('<th class="text-center" style="width:'+columnMapping.width+';">').html(columnMapping.name).appendTo($headerRow);
		    });
	 
		    $thead.append($headerRow);
		    $table.append($thead);
		    $table.append($tbody);
	 
		    ul.html($table);
	 
		    $.each(items, function (index, item) {
			   self._renderItemData(ul, ul.find("table tbody"), item);
		    });
		},
		_renderItemData: function (ul, table, item) {
		    return this._renderItem(table, item).data("ui-autocomplete-item", item);
		},
		_renderItem: function (table, item) {
		    var self = this;
		    var $tr = $('<tr class="ui-menu-item" role="presentation">');
	 
		    $.each(self.options.columns, function (index, columnMapping) {
			   var cellContent = !item[columnMapping.valueField] ? '' : item[columnMapping.valueField];
			   $('<td class="text-center">').html(cellContent).appendTo($tr);
		    });
	 
		    return $tr.appendTo(table);
		}
	});
	
	// Tìm số vé
	$(document).on('keydown.autocomplete','input.ac_ticket_number', function(){
		$(this).autocomplete({
			showHeader: true,
			columns: [
				{
					name: 'Số vé',
					width: '120px',
					valueField: 'label'
				},
				{
					name: 'PNR',
					width: '50px',
					valueField: 'ticket_code'
				},
				{
					name: 'NCC',
					width: '100px',
					valueField: 'supplier'
				},
				{
					name: 'Hành trình',
					width: '100px',
					valueField: 'iti'
				},
				{
					name: 'SL',
					width: '30px',
					valueField: 'qty'
				},
				{
					name: 'Thu hộ',
					width: '80px',
					valueField: 'authorized_fee'
				},
				{
					name: 'Số tiền',
					width: '100px',
					valueField: 'total'
				},
				{
					name: 'Diễn giải',
					width: '100px',
					valueField: 'desc'
				}
			],
			source: "index.php?entryPoint=entryPointEC_HoaDonBan&for=getInputInvoice",
			minLength: 6,
			select: function(event, ui) {
				if(ui.item.id.length == 0) {
					$('#ct_ticket_number' + ln).val(""); // Số vé
					$('#ct_ticket_code' + ln).val(""); // Code vé
					return;
				}

				$('input#lienhe').val(ui.item.buyer.lienhe);
				$('input#tencongty').val(ui.item.buyer.tencongty);
				$('input#masothue').val(ui.item.buyer.masothue);
				$('input#email').val(ui.item.buyer.email);
				$('input#lienhe.sotaikhoan').val(ui.item.buyer.sotaikhoan);
				$('input#lienhe.hinhthuctt').val(ui.item.buyer.hinhthuctt);
				$('input#lienhe.diachi').val(ui.item.buyer.diachi);

				let ln = $(this).attr('ln');
				$(`#ct_ticket_number_id${ln}`).val(ui.item.id); // ID Input invoice (hidden)
				$(`#ct_booking_id${ln}`).val(ui.item.booking_id); // Booking ID (hidden)
				$(`#ct_booking${ln}`).val(ui.item.booking); // Booking name
				$(`#ct_ticket_number${ln}`).val(ui.item.label); // Số vé
				$(`#ct_ticket_code${ln}`).val(ui.item.ticket_code); // Code vé
 				$(`#ct_qty${ln}`).val(ui.item.qty); // Số lượng
				$(`#ct_qty${ln}`).attr('max_qty', ui.item.max_qty);
				$(`#ct_purchase_price${ln}`).val(ui.item.total); // Giá mua
				$(`#ct_authorized${ln}`).val(ui.item.authorized_fee); // Thu hộ
				calculateLineTotal(ln);
			}
		});
    });

	// Tìm code vé
	$(document).on('keydown.autocomplete','input.ac_ticket_code', function(){
		$(this).autocomplete({
			showHeader: true,
			columns: [
				{
					name: 'Số vé',
					width: '100px',
					valueField: 'label'
				},
				{
					name: 'PNR',
					width: '50px',
					valueField: 'ticket_code'
				},
				{
					name: 'NCC',
					width: '90px',
					valueField: 'supplier'
				},
				{
					name: 'SL',
					width: '30px',
					valueField: 'qty'
				},
				{
					name: 'Thu hộ',
					width: '80px',
					valueField: 'authorized_fee'
				},
				{
					name: 'Số tiền',
					width: '90px',
					valueField: 'total'
				},
				{
					name: 'Diễn giải',
					width: '120px',
					valueField: 'desc'
				}
			],
			source: "index.php?entryPoint=entryPointEC_HoaDonBan&for=getInputInvoice",
			minLength: 6,
			select: function (event, ui) {
				if(ui.item.id.length == 0) {
					$('#ct_ticket_number' + ln).val(""); // Số vé
					$('#ct_ticket_code' + ln).val(""); // Code vé
					return;
				}

				$('input#lienhe').val(ui.item.buyer.lienhe);
				$('input#tencongty').val(ui.item.buyer.tencongty);
				$('input#masothue').val(ui.item.buyer.masothue);
				$('input#email').val(ui.item.buyer.email);
				$('input#lienhe.sotaikhoan').val(ui.item.buyer.sotaikhoan);
				$('input#lienhe.hinhthuctt').val(ui.item.buyer.hinhthuctt);
				$('input#lienhe.diachi').val(ui.item.buyer.diachi);

				let ln = $(this).attr('ln');
				$(`#ct_ticket_number_id${ln}`).val(ui.item.id); // ID Input invoice (hidden)
				$(`#ct_booking_id${ln}`).val(ui.item.booking_id); // Booking ID (hidden)
				$(`#ct_booking${ln}`).val(ui.item.booking); // Booking name
				$(`#ct_ticket_number${ln}`).val(ui.item.label); // Số vé
				$(`#ct_ticket_code${ln}`).val(ui.item.ticket_code); // Code vé
 				$(`#ct_qty${ln}`).val(ui.item.qty); // Số lượng
				$(`#ct_qty${ln}`).attr('max_qty', ui.item.max_qty);
				$(`#ct_purchase_price${ln}`).val(ui.item.total); // Giá mua
				$(`#ct_authorized${ln}`).val(ui.item.authorized_fee); // Thu hộ
				calculateLineTotal(ln);
			}
		});
	});

	// Tìm booking
	$(document).on('keydown.autocomplete','input.ac_booking', function(){
		var fld = $(this).attr('fld');

		$(this).autocomplete({
			// These next two options are what this plugin adds to the autocomplete widget.
			showHeader: true,
			columns: [{
				name: 'Booking',
				width: '120px',
				valueField: 'label'
			}],
			source: "ac_all.php?tbl=ec_flight_bookings&fld=" + fld,
			minLength: 10,
			select: function (event, ui) {
				var obj = JSON.parse(fld);
				for (var prop in obj) {
					$('#' + obj[prop]).val(ui.item[prop]);
				}
			}
		});
	});

	// Tìm tên công ty
	$(document).on('keydown.autocomplete','input#tencongty', function(){
		$(this).autocomplete({
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
			minLength: 2,
			select: function (event, ui) {
				$('#masothue').val(ui.item.sic_code);
				$('#diachi').val(ui.item.address);
				$('#email').val(ui.item.email);
			}
		});
	});
	
	// Kiểm tra MST
	$('#icon-search-masothue').click(function() {
		let mst = $('#masothue').val();

		if(mst.length > 0) {
			$('.container-waiting').show();
			$.ajax({
				url: "index.php?entryPoint=entryPointEC_HoaDonBan&for=getConpanyInfo",
				data: {
					mst: mst
				},
				type: "POST",
				success: function (response) {
					$('.container-waiting').hide();
					let info = JSON.parse(response);

					if (info.mst == null) {
						$('.wrap-masothue .mst-active').hide();
						$('.wrap-masothue .mst-alert').show();
					}
					else if(info.success == true || info.status.indexOf("Đang hoạt động") != -1 || info.status.indexOf("đang hoạt động") != -1) {
						$('.wrap-masothue .mst-active').show();
						$('.wrap-masothue .mst-alert').hide();

						// Kiểm tra tên công ty
						let tencongty = $('input#tencongty').val();
						if(tencongty.length == 0) $('input#tencongty').val(info.name);
						else if(tencongty != info.name) {
							$('input#tencongty').css("border-color", "red");
							$('.correct-value-tencongty').remove();
							$('div.edit-view-field[field="tencongty"]').append(`<i class="correct-value-tencongty" style="color:red">${info.name}</i>`);
						}
						else {
							$('input#tencongty').css("border-color", "#dee2e6");
							$('.correct-value-tencongty').remove();
						}

						// Kiểm tra địa chỉ
						let diachi = $('textarea#diachi').val();
						if(diachi.length == 0) $('textarea#diachi').val(info.address);
						else if(diachi != info.address) {
							$('textarea#diachi').css("border-color", "red");
							$('.correct-value-diachi').remove();
							$('div.edit-view-field[field="diachi"]').append(`<i class="correct-value-diachi" style="color:red">${info.address}</i>`);
						}
						else {
							$('textarea#diachi').css("border-color", "#dee2e6");
							$('.correct-value-diachi').remove();
						}
					}
					else {
						$('.wrap-masothue .mst-active').hide();
						$('.wrap-masothue .mst-alert').show();
					}				
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					$('.container-waiting').hide();

					let text_modal_error = 'ERROR ('+errorThrown+'): Vui lòng liên hệ bộ phận IT.';
					showModalNotify(0, text_modal_error)

					console.error(XMLHttpRequest);
					console.error("Status: " + textStatus);
					console.error("Error: " + errorThrown);
				}
			});
		}
	});

	// Validate form
	$('#EditView input[value="Lưu"], #EditView input[value="Save"]').click(function (e) {
		e.preventDefault(); // Don't remove it

		if($('#company_unit').val() == ''){
			showToastWarning('Vui lòng lựa chọn đơn vị hóa đơn!');
			preventSubmit();
			return false;
		}

		// Kiểm tra chi tiết hoá đơn đủ thông tin booking, số vé
		if ($("#loaihoadon").val() == "0") {
			if($(".ac_ticket_number").length == 0) {
				showToastWarning('Vui lòng nhập chi tiết hóa đơn');
				preventSubmit();
				return false;
			}
			
			// Check list items
			var ticket_num_str = '';
			$(".ac_ticket_number").each(function (ind) {
				if (ticket_num_str != '') ticket_num_str += ",";
				ticket_num_str += $("#ct_ticket_number_id" + ind).val();
			});

			$.ajax({
				url: "index.php?entryPoint=entryPointEC_HoaDonBan",
				type: "POST",
				data: {
					invoice: $("#EditView input[name='record']").val(),
					ticket_num: ticket_num_str,
					for: "getMaxQty",
				},
				beforeSend: function () {
					$('.container-waiting').show();
				},
				success: function (response) {
					ticket_number_max_qty = JSON.parse(response);
					var arr = document.getElementsByName('ct_deleted[]');

					let check = true;
					for (var i = 0; i < arr.length; i++) {
						let type_code = $(`#ct_code${i}`).val();

						// Các loại không check số vé, booking
						if(type_code == 'PK') continue;
						
						$(`#ct_ticket_number${i}, #ct_booking${i}, #ct_qty${i}`).removeClass("ln_error");
						$(`#err_ticket_number${i}, #err_booking${i}, #err_qty${i}`).remove();

						if (arr[i].value == '0' && $(`#ct_ticket_number${i}`).val().length == 0) {
							$(`#ct_ticket_number${i}`).addClass("ln_error");
							$(`#ct_ticket_number${i}`).parent().parent().append(`<div id="err_ticket_number${i}" class="err_text">Số vé không được trống<div>`);
							check = false;
						} 
						else if (arr[i].value == '0' && $(`#ct_ticket_number_id${i}`).val().length == 0) {
							$(`#ct_ticket_number${i}`).addClass("ln_error");
							$(`#ct_ticket_number${i}`).parent().parent().append(`<div id="err_ticket_number${i}" class="err_text">Số vé chưa hợp lệ<div>`);
							check = false;
						}

						if (arr[i].value == '0' && $(`#ct_booking_id${i}`).val().length == 0) {
							$(`#ct_booking${i}`).addClass("ln_error");
							$(`#ct_booking${i}`).parent().append(`<div id="err_booking${i}" class="err_text">BK chưa hợp lệ<div>`);
							check = false;
						}

						if (arr[i].value == '0' && unformatNumber($(`#ct_qty${i}`).val()) <= 0) {
							$(`#ct_qty${i}`).addClass("ln_error");
							$(`#ct_qty${i}`).parent().append(`<div id="err_qty${i}" class="err_text">SL > 0<div>`);
							check = false;
						}
						else if (arr[i].value == '0' && unformatNumber($(`#ct_qty${i}`).val()) > 0) {
							// KT số lượng tồn
							var ticket_number_id = $(`#ct_ticket_number_id${i}`).val();
							// if (!ticket_number_max_qty.hasOwnProperty(ticket_number_id)) {
							// 	ticket_number_max_qty[ticket_number_id] = parseInt($("#ct_qty" + i).attr("max_qty"));
							// }
							if ($(`#ct_qty${i}`).val() <= ticket_number_max_qty[ticket_number_id]) {
								ticket_number_max_qty[ticket_number_id] -= parseInt($(`#ct_qty${i}`).val());
							} else {
								$(`#ct_qty${i}`).addClass("ln_error");
								$(`#ct_qty${i}`).parent().append(`<div id="err_qty${i}" class="err_text">Vượt SL tồn<div>`);
								// $(`#ct_ticket_number_id${i}, #ct_ticket_number${i}`).val("");
								check = false;
							}
						}
					}

					if(check) $('#EditView').submit();
					else {
						$('.container-waiting').hide();
						preventSubmit();
						return false;
					}
				}
			});
		} 
		else {
			$('#EditView').submit();
		}
	});
});

function markRowDeleted(ln){
	$('#ct_line_' + ln).hide();
	$('#ct_deleted' + ln).val(1);
	$('#lbl_row_count').text(parseInt($('#lbl_row_count').text()) - 1);

	calculateLineTotal(ln);
}

function insertRow(ln, invoice_type) {
	// Lấy thông tin booking của dòng trước
	var booking_bef 	= ($("#ct_booking" + (ln - 1)).val() || '');
	var booking_id_bef 	= ($("#ct_booking_id" + (ln - 1)).val() || '');

	var html = `<tr id="ct_line_${ln}">`;
	if (invoice_type == 0) {
		html += `<td>
			<select name="ct_code[]" id="ct_code${ln}">
				<option value="VMB_QN">VMB_QN</option>
				<option value="VMB_QT">VMB_QT</option>
				<option value="PD">PD</option>
				<option value="PHL">PHL</option>
				<option value="PMG">PMG</option>
				<option value="PK">PK</option>
			</select>
		</td>`;

		html += `<td>
			<input type="text" name="ct_booking[]" id="ct_booking${ln}" ln="${ln}" class="ac_booking text-start" maxlength="32" size="30" autocomplete="off" fld='{\"id\":\"ct_booking_id" + ln + "\",\"name\":\"ct_booking" + ln + "\"}' value="${booking_bef}" />
			<input type="hidden" name="ct_booking_id[]" id="ct_booking_id${ln}" value="${booking_id_bef}" />
			<input type="hidden" name="ct_receipt_voucher[]" class="input-receipt-voucher" value="" placeholder="Mã phiếu thu" style="border:1px solid #c2c2c2 !important; border-radius:4px; margin-top:5px; padding-left:5px !important;" />
		</td>`;

		html += `<td>
			<div class="d-flex align-items-center gap-1">
				<input class="ac_ticket_number text-start" ln="${ln}" type="text" name="ct_ticket_number[]" id="ct_ticket_number${ln}" maxlength="255" size="30" autocomplete="off" />
				<input type="hidden" name="ct_ticket_number_id[]" id="ct_ticket_number_id${ln}" value="" />
				<button title="Tìm" class="button-pick" type="button" onclick="openTicketNumberPopup(${ln})">
					<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#333"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
				</button>
			</div>
		</td>`;

		// Số lượng
		html += `<td class="text-center">
			<input class="allow-number-only text-center" onblur="calculateLineTotal(${ln})" value="1" type="text" name="ct_qty[]" id="ct_qty${ln}" size="5" maxlength="20" />
		</td>`;
		
		// Giá mua
		html += `<td class="text-end">
			<input class="allow-number-only text-end" onblur="calculateLineTotal(${ln})" value="0" type="text" name="ct_purchase_price[]" id="ct_purchase_price${ln}" />
		</td>`;

		// Thu hộ
		html += `<td>
			<input class="allow-number-only text-end" onblur="calculateLineTotal(${ln})" value="0" type="text" name="ct_authorized[]" id="ct_authorized${ln}" />
		</td>`;

		// Phí sân bay
		html += `<td>
			<input class="allow-number-only text-end" value="0" type="text" name="ct_airport_fee[]" id="ct_airport_fee${ln}" />
		</td>`;

		// Phí khác
		html += `<td>
			<input class="allow-number-only text-end" value="0" type="text" name="ct_other_fee[]" id="ct_other_fee${ln}" />
		</td>`;

		// Phí dịch vụ
		html += `<td>
			<input class="allow-number-only text-end" onblur="calculateLineTotal(${ln})" value="0" type="text" name="ct_service[]" id="ct_service${ln}" />
		</td>`;
		
		// Thuế suất (%)
		html += `<td>
			<select name="ct_percent_vat[]" id="ct_percent_vat${ln}" onchange="calculateLineTotal(${ln})">
				<option value="0">0%</option>
				<option value="0.08">8%</option>
				<option value="0.1">10%</option>
				<option value="-1">KCT</option>
				<option value="-2">KKKNT</option>
			</select>
		</td>`;

		// Giá bán
		html += `<td>
			<input class="allow-number-only text-end" value="0" type="text" name="ct_price[]" id="ct_price${ln}" readonly />
		</td>`;

		// VAT
		html += `<td>
			<input class="allow-number-only text-end" onblur="calculateChangeVAT(${ln})" value="0" type="text" name="ct_vat[]" id="ct_vat${ln}" />
		</td>`;
		
		// Thành tiền
		html += `<td>
			<input class="allow-number-only text-end" value="0" type="text" name="ct_total[]" id="ct_total${ln}" readonly />
		</td>`;
		
		html += `<td class="text-center">
			<button title="Xóa" class="button-remove-in-edit" type="button" onclick="markRowDeleted(${ln})" >
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#333"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
			</button>
			<input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted${ln}" />
			<input type="hidden" name="ct_detail_id[]" id="ct_detail_id${ln}" value="" />
		</td>`;
	}
	else if(invoice_type == 1) {
		html += `<td><input class="text-start" ln="${ln}" type="text" name="ct_name[]" id="ct_name${ln}" maxlength="255" size="30" autocomplete="off" /></td>`;

		html += `<td><input class="allow-number-only text-center" onblur="calculateLineTotal(${ln})" value="1" type="text" name="ct_qty[]" id="ct_qty${ln}" size="5" maxlength="20" /></td>`;

		html += `<td><input class="allow-number-only text-end" onblur="calculateLineTotal(${ln})" value="0" type="text" name="ct_price[]" id="ct_price${ln}" size="14" maxlength="20" /></td>`;

		html += `<td><input class="allow-number-only text-end" onblur="calculateLineTotal(${ln})" value="0" type="text" name="ct_vat[]" id="ct_vat${ln}" size="14" maxlength="20" /></td>`;

		html += `<td><input class="allow-number-only text-end" onblur="calculateLineTotal(${ln})" value="0" type="text" name="ct_authorized[]" id="ct_authorized${ln}" size="14" maxlength="20" /></td>`;

		html += `<td><input class="allow-number-only text-end" onblur="calculateTotal()" value="0" type="text" name="ct_total[]" id="ct_total${ln}" size="14" maxlength="20" /></td>`;

		html += `<td class="text-center">
				<button title="Xóa" class="button-remove-in-edit" type="button" onclick="markRowDeleted(${ln})" >
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#333"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
				</button>
				<input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted${ln}" />
				<input type="hidden" name="ct_detail_id[]" id="ct_detail_id${ln}" value="" />
			</td>`;
	}
	
	html += `</tr>`;
	return html;
}

function calculateLineTotal(ln) {
	let soluong  = unformatNumber($(`#ct_qty${ln}`).val());
	let giamua 	 = unformatNumber($(`#ct_purchase_price${ln}`).val());
	let dichvu   = unformatNumber($(`#ct_service${ln}`).val());
	let thuesuat = unformatNumber($(`#ct_percent_vat${ln}`).val());
	let thuho 	 = unformatNumber($(`#ct_authorized${ln}`).val());
	// let dongia 	= unformatNumber($(`#ct_price${ln}`).val());
	// let thue 	= unformatNumber($(`#ct_vat${ln}`).val());

	let divide = 1;
	let multiply = 0;
	if(thuesuat == '0.08') {
		divide = 1.08;
		multiply = 0.08;
	}
	else if(thuesuat == '0.1') {
		divide = 1.1;
		multiply = 0.1;
	}
	
	let dongia = Math.round((giamua + dichvu - thuho) / divide);
	let thue = Math.round(dongia * multiply);
	let thanhtien = (dongia + thue + thuho) * soluong;

	$('#ct_price' + ln).val(dongia);
	$('#ct_vat' + ln).val(thue * soluong);
	$('#ct_total' + ln).val(thanhtien);

	// var soluong = unformatNumber($('#ct_qty' + ln).val());
	// var dongia 	= unformatNumber($('#ct_price' + ln).val());
	// var thue 	= unformatNumber($('#ct_vat' + ln).val());
	// var thuho 	= unformatNumber($('#ct_authorized' + ln).val());

	// // tính thuế
	// if (is_cal_vat) {
	// 	thue = Math.round(dongia * vat);
	// }
	// var thanhtien = soluong * (dongia + thue + thuho);

	// $('#ct_vat' + ln).val(thue);
	// $('#ct_total' + ln).val(thanhtien);

	calculateTotal();
}

function calculateChangeVAT(ln) {
	let soluong = unformatNumber($(`#ct_qty${ln}`).val());
	let thue 	= unformatNumber($(`#ct_vat${ln}`).val());
	let dongia 	= unformatNumber($(`#ct_price${ln}`).val());
	let thuho 	= unformatNumber($(`#ct_authorized${ln}`).val());
	let thanhtien = (dongia + thuho) * soluong + thue;
	$('#ct_total' + ln).val(thanhtien);

	calculateTotal();
}

function calculateTotal(){
	let arr 			= document.getElementsByName('ct_deleted[]');
	let soluong 		= document.getElementsByName('ct_qty[]');
	let giamua  		= document.getElementsByName('ct_purchase_price[]');
	let thuho 		= document.getElementsByName('ct_authorized[]');
	let dichvu 		= document.getElementsByName('ct_service[]');
	let dongia 		= document.getElementsByName('ct_price[]');
	let thue 			= document.getElementsByName('ct_vat[]');
	let thanhtien 		= document.getElementsByName('ct_total[]');
	let tongsl = tonggiamua = tongthuho = tongdichvu = tongdongia = tongthue = tongtien = 0;
		
	for(var i = 0; i < arr.length; i++){
		if(arr[i].value == '0') {
			tongsl 		+= unformatNumber(soluong[i].value);
			tonggiamua  	+= unformatNumber(giamua[i].value) * unformatNumber(soluong[i].value);
			tongthuho 	+= unformatNumber(thuho[i].value) * unformatNumber(soluong[i].value);
			tongdichvu 	+= unformatNumber(dichvu[i].value) * unformatNumber(soluong[i].value);
			tongdongia 	+= unformatNumber(dongia[i].value) * unformatNumber(soluong[i].value);
			tongthue 	+= unformatNumber(thue[i].value);
			tongtien 	+= unformatNumber(thanhtien[i].value);
		}
	}

	$('#tongsl').val(formatNumber(tongsl));
	$('#tonggiamua').val(formatNumber(tonggiamua));
	$('#tongthuho').val(formatNumber(tongthuho))
	$('#tongdichvu').val(formatNumber(tongdichvu));
	$('#tonggiaban').val(formatNumber(tongdongia));
	$('#tongvat').val(formatNumber(tongthue));
	$('#tongthanhtoan').val(formatNumber(Math.round(tongtien)));
}

function checkInvoiceInfo() {
	$("#err_sohoadon").remove();
	$("#sohoadon").removeClass("ln_error");
	// if($("#sohoadon").val().length != 8) {
	// 	$("#sohoadon").addClass("ln_error");
	// 	$("#sohoadon").parent().append("<div id='err_sohoadon' class='err_text' style='margin-top: 2px;'>Số hoá đơn chưa đủ 8 ký tự.<div>");
	// }
}

function openTicketNumberPopup(ln) {
	var popupRequestData = {
		"call_back_function": "setObjectReturn",
		"form_name": "EditView",
		"field_to_name_array": {
			"id": "ct_ticket_number_id" + ln,
			"name": "ct_ticket_number" + ln,
			"ticket_code": "ct_ticket_code" + ln,
		}
	};
	open_popup('EC_Input_Invoices', 650, 600, '&out_of_stock_advanced=0', true, false, popupRequestData);
}

function changeInvoiceType() {
	var invoice_type = $("#loaihoadon").val();
	if(invoice_type == 0) {
		$("#last-row").children().eq(0).attr("colspan", 3);
		$("#first-row").html(`
			<th width="6%" align="left">Mã hàng</th> 
			<th width="9%" align="left">Booking</th> 
			<th width="14%" align="left">Số vé</th> 
			<th width="3%" align="center">SL</th> 

			<th width="8%" align="center">Giá mua</th>
			<th width="7%" align="center">Thu hộ</th>
			<th width="7%" align="center">Phí sân bay</th>
			<th width="7%" align="center">Phí khác</th>
			<th width="7%" align="center">Phí DV</th>
			<th width="5%" align="center">% VAT</th>

			<th width="9%" align="center">Giá bán</th>
			<th width="6%" align="center">VAT</th>
			<th width="12%" align="center">Thành tiền</th>
			<th align="center">&nbsp;</th>
		`);
	} else {
		$("#last-row").children().eq(0).attr("colspan", 1);
		$("#first-row").html(`
			<th width="35%" align="left">Tên dịch vụ</th> 
			<th width="10%" align="center">SL</th> 
			<th width="15%" align="center">Giá bán</th> 
			<th width="10%" align="center">VAT</th> 
			<th width="10%" align="center">Thu hộ</th> 
			<th width="15%" align="center">Giá bán (VAT)</th> 
			<th width="5%" align="center">&nbsp;</th> 
		`);
	}
}