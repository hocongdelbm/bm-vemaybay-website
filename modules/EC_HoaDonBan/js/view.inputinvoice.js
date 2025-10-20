const ENTRYPOINT = "index.php?entryPoint=entryPointGeneral";

$(document).ready(function () {
    $(".allow_number_only").number(true, 0, dec_sep, num_grp_sep);
    $('.decimal-only').on('input', function () {
        let value = $(this).val();
        value = value.replace(/[^0-9.,]/g, '');
        $(this).val(value);
    });

    // Default current date
    if($('input[name="accounting_date"]').val().length < 10) $('input[name="accounting_date"]').val(getCurrentDate());
    if($('input[name="invoice_date"]').val().length < 10) $('input[name="invoice_date"]').val(getCurrentDate());

    $('#import-tab').click(function() {
        $('#supplier').trigger('change');
    });
    $('#supplier').change(function () {
        let optionSelected = $(this).children('option:selected');

        $('#col_ticket_code').val(optionSelected.attr('data-col-ticket-code'));
        $('#col_pass_qty').val(optionSelected.attr('data-col-pass-qty'));
        $('#col_ticket_price').val(optionSelected.attr('data-col-ticket-price'));
        $('#col_vat').val(optionSelected.attr('data-col-vat'));
        $('#col_authorized_collection').val(optionSelected.attr('data-col-authorized-collection'));
        $('#col_other_charge').val(optionSelected.attr('data-col-other-charge'));
        $('#col_total').val(optionSelected.attr('data-col-total'));
        $('#col_itinerary').val(optionSelected.attr('data-col-itinerary'));

        $('#supplier_name').val(optionSelected.text().trim());
    });

    $("#import_invoice_frm").submit(function () {
        addToValidate('import_invoice_frm', 'invoice_date', 'date', true, 'Ngày phải nhập theo cú pháp: 28-02-2022');
        addToValidate('import_invoice_frm', 'invoice_number', 'varchar', true, 'Không được để trống');
        addToValidate('import_invoice_frm', 'invoice_serial', 'varchar', true, 'Không được để trống');
        addToValidate('import_invoice_frm', 'accounting_date', 'date', true, 'Ngày phải nhập theo cú pháp: 28-02-2022');
        if (!check_form('import_invoice_frm')) {
            return false;
        }
        $("#import_btn").prop("disabled", true);
        $("#noti_line").text("Hệ thống đang xử lý dữ liệu. Vui lòng chờ trong giây lát...");
    });

    // Xoá hoá đơn bắt buộc phải nhập số hoá đơn, ký hiệu hoá đơn
    $("#remove_frm").submit(function () {
        addToValidate('remove_frm', 'rm_invoice_number', 'varchar', true, 'Không được để trống');
        addToValidate('remove_frm', 'rm_invoice_serial', 'varchar', true, 'Không được để trống');
        if (!check_form('remove_frm')) {
            return false;
        }
    });

    // nếu có form confirm 
    if ($("#preview_frm").length > 0) {
        $("#data_tbl th ").css("top", "65px");
    } else {
        $("#data_tbl thead tr.line_1 th").css("top", "75px");
        $("#data_tbl thead tr.line_2 th").css("top", "130px");
    }

    // nút xoá tìm kiếm
    $("#clear_btn").click(function () {
        $("#search_frm input[type='text'], #search_frm select").val("");
        $("#page_number_hid, #page_number").val(1);
        $("#search_frm").submit();
    });

    // tính lại tổng giá vốn
    $(".invoice_cost, .invoice_vat, .invoice_cost_vat, .authorized_fee").keyup(function () {
        var total_cost = total_vat = total_cost_vat = total_authorized = total = 0;

        $(".invoice_cost").each(function (idx) {
            var vat = parseInt($(".invoice_vat").eq(idx).val() || 0);
            var cost = parseInt($(".invoice_cost").eq(idx).val() || 0);
            var authorized_fee = parseInt($(".authorized_fee").eq(idx).val() || 0);

            total_vat += vat;
            total_cost += cost;
            $(".invoice_cost_vat").eq(idx).val(cost + vat);
            total_cost_vat += cost + vat;
            total_authorized += authorized_fee;
            $(".ln_total").eq(idx).text(cost + vat + authorized_fee);
            total += cost + vat + authorized_fee;
        });

        $("#total_cost_vat").text(total_cost_vat);
        $("#total_vat").text(total_vat);
        $("#total_cost").text(total_cost);
        $("#total_authorized_fee").text(total_authorized);
        $("#total").text(total);

        $("#total_cost_vat, #total_vat, #total_cost, #total_authorized_fee, #total, .ln_total").number(true, 0, dec_sep, num_grp_sep);
    });

    $("#btn_booking").click(function () {
        open_popup(
            "EC_Flight_Bookings", 600, 400, "", true, false,
            {
                "call_back_function": "set_return",
                "form_name": "implement_invoice_frm",
                "field_to_name_array": {
                    "id": "im_booking_id",
                    "name": "im_booking"
                }
            }, "single", true);
    });

    $("#btn_clr_booking").click(function () {
        $("#im_booking_id").val('');
        $("#im_booking").val('');
        $("#im_booking").focus();
    });

    $("#btn_ticket_code").click(function () {
        open_popup(
            "EC_Input_Invoices", 600, 400, "", true, false,
            {
                "call_back_function": "set_inv_return",
                "form_name": "implement_invoice_frm",
                "field_to_name_array": {
                    "id": "im_ticket_id",
                    "name": "im_ticket_code"
                }
            }, "single", true);
    });

    $("#btn_clr_ticket_code").click(function () {
        $("#implement_invoice_frm input, #implement_invoice_frm select").not("input[type='button'], input[type='submit']").val('');
        $("#im_ticket_code").focus();
    });

    // kiểm tra khi tìm kiếm
    $("#search_frm").submit(function () {

        $("#page_number_hid").val($("#page_number").val());
        addToValidate('search_frm', 'from_date', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
        addToValidate('search_frm', 'to_date', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
        addToValidate('search_frm', 'from_date_accounting', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
        addToValidate('search_frm', 'to_date_accounting', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
        addToValidateRange('search_frm', 'page_number', 'int', true, 'Lỗi', 1, parseInt($("#page_number").attr("max")));

        if (check_form('search_frm')) {
            $("#search_page_btn").prop("disabled", true);
            $("#page_number").prop("readonly", true);
            $(".pagination_txt").addClass('disabled');
            return true;
        }

        $("#page_number").prop("readonly", false);
        $("#search_page_btn").prop("disabled", false);
        $(".pagination_txt").removeClass('disabled');

        if ($("#page_number").val() <= 1) {
            $(".lpagi").addClass('disabled');
        } else if ($("#page_number").val() >= parseInt($("#page_number").attr('max'))) {
            $(".rpagi").addClass('disabled');
        }
        return false;
    });

    // lưu số vé
    $("#implement_invoice_frm").submit(function () {
        $("#import_btn").prop("disabled", true);
        if (!check_import_form()) {
            $("#import_btn").prop("disabled", false);
            return false;
        } else {
            $("#noti_line").text("Hệ thống đang lưu số vé. Vui lòng đợi trong giây lát...");
        }
    });

    // thêm dòng số vé, dùng khi tách giá trong cùng số vé 
    // nhưng dành loại khách khác nhau (người lớn / trẻ em / trẻ sơ sinh)
    $("#inv_add_row").click(function () {
        var inv_row_count = $("#inv_row_count").val();
        $("#data_tbl tbody #last_row").before(insertNewInvRow(inv_row_count));
        $("#inv_row_count").val(++inv_row_count);
        $(".allow_number_only").number(true, 0, dec_sep, num_grp_sep);
        markInvOrder();
    });

    // nhảy sang trang
    $(".pagination_txt").click(function () {

        if ($(this).hasClass("disabled")) {
            return false;
        } else {
            var curr_page = parseInt($("#page_number").val());
            var page_set = $(this).text().trim();

            if (page_set == 'First') {
                $("#page_number").val(1);
            } else if (page_set == 'Prev' && curr_page > 1) {
                $("#page_number").val(curr_page - 1);
            } else if (page_set == 'Next' && curr_page < parseInt($("#page_number").attr('max'))) {
                $("#page_number").val(curr_page + 1);
            } else if (page_set == 'Last') {
                $("#page_number").val($("#page_number").attr('max'));
            }
            $("#search_frm").submit();
        }
    });
    $("#search_page_btn").click(function () {
        $("#search_frm").submit();
    });

    Calendar.setup({
        inputField: "from_date",
        daFormat: "%d-%m-%Y %H:%M",
        button: "from_date_trigger",
        singleClick: true,
        dateStr: "",
        step: 1,
        weekNumbers: false
    });
    Calendar.setup({
        inputField: "to_date",
        daFormat: "%d-%m-%Y %H:%M",
        button: "to_date_trigger",
        singleClick: true,
        dateStr: "",
        step: 1,
        weekNumbers: false
    });

    Calendar.setup({
        inputField: "from_date_accounting",
        daFormat: "%d-%m-%Y %H:%M",
        button: "from_date_accounting_trigger",
        singleClick: true,
        dateStr: "",
        step: 1,
        weekNumbers: false
    });
    Calendar.setup({
        inputField: "to_date_accounting",
        daFormat: "%d-%m-%Y %H:%M",
        button: "to_date_accounting_trigger",
        singleClick: true,
        dateStr: "",
        step: 1,
        weekNumbers: false
    });

    $('i.icon-duplicate').on('click', function () {
        var base64Data = $(this).attr('form-data');
        var jsonStr = atob(base64Data);
        var formData = JSON.parse(jsonStr);

        // Create a form with inputs based on the data
        var form = $('<form id="form-duplicate-input-invoice"></form>');
        let current_booking = '';
        $.each(formData, function (key, field) {
            if(key == 'booking') current_booking = field.value;

            // Create a form-group wrapper
            var $fieldContainer = $('<div>').addClass('form-group pb-1');

            // Handle hidden fields (still wrapped but will be invisible)
            if (field.type === 'hidden') {
                $fieldContainer.append(
                    $('<input>').attr({
                        type: 'hidden',
                        name: key,
                        value: field.value
                    })
                );
                form.append($fieldContainer);
                return;
            }

            // Add label if exists
            if (field.label) {
                $fieldContainer.append(
                    $('<label>').attr('for', key).text(field.label)
                );
            }

            // Handle select fields
            if (field.type === 'select') {
                var $select = $('<select>').attr({
                    name: key,
                    id: key
                }).addClass('form-control');
                $.each(field.options, function (i, option) {
                    $select.append(
                        $('<option>').val(i).text(option)
                    );
                });
                $select.val(field.value);
                $fieldContainer.append($select);
            }
            // Handle number fields
            else if (field.type === 'number') {
                $fieldContainer.append(
                    $('<input>').attr({
                        type: 'number',
                        name: key,
                        id: key,
                        value: field.value
                    }).addClass('form-control ' + (field.class || ''))
                );
            }
            // Handle text fields
            else {
                $fieldContainer.append(
                    $('<input>').attr({
                        type: 'text',
                        name: key,
                        id: key,
                        value: field.value
                    }).addClass('form-control ' + (field.class || ''))
                );
            }

            form.append($fieldContainer);
        });

        // Create a modal dialog
        var $modal = $(`
            <div class="modal" id="modal-duplicate-input-invoice" style="display:block; position:fixed; background:rgba(0,0,0,0.8); top:0; left:0; right:0; bottom:0; z-index:1000; display:flex; justify-content:center; align-items:center;">
                <div style="background:white; padding:20px; border-radius:5px; width:400px;">
                    <h3>Nhân bản hóa đơn</h3>
                    <div class="modal-body" style="max-height: 545px; overflow-y: scroll;"></div>
                    <div class="mt-2" style="text-align:right;">
                        <button type="button" class="btn btn-primary" id="btn-duplicate-input-invoice" current_booking="${current_booking}">Nhân bản</button>
                        <button type="button" class="btn btn-secondary close-modal">Đóng</button>
                    </div>
                </div>
            </div>
        `);

        // Add form to modal body
        $modal.find('.modal-body').append(form);

        // Add close button handler
        $modal.find('.close-modal').on('click', function () {
            $modal.remove();
        });

        // Add modal to body
        $('body').append($modal);
    });
    $(document).on('click', '#btn-duplicate-input-invoice', function () {
        // Create formData
        var formElement = document.getElementById('form-duplicate-input-invoice');
        var formData = new FormData(formElement);

        // Find all inputs with the 'money' class
        $(formElement).find('.money').each(function () {
            var input = $(this);
            var name = input.attr('name'); // input name is used as FormData key
            var value = input.val();

            if (name) {
                var unformatted = parseInt(unformatNumber(value));
                input.val(unformatted); // update visible field if needed
                formData.set(name, unformatted); // update FormData
            }
        });

        // Recheck booking
        let current_booking = $(this).attr('current_booking') ?? '';
        if(current_booking.length > 0 && current_booking != formData.get('booking')) formData.set('booking_id', '');

        formData.set('for', 'duplicateInvoice');

        // Optionally, collect as JSON (if you want to send as JSON)
        // var formData = {};
        // $('#dynamic-form').find('input, select').each(function() {
        //   formData[this.name] = $(this).val();
        // });

        $.ajax({
            url: 'index.php?entryPoint=entryPointEC_HoaDonBan',
            type: 'POST',
            data: formData,
            processData: false, // don't let jQuery process the data
            contentType: false, // let the browser set it (multipart/form-data)
            beforeSend: function() {
                $('.container-waiting').show();
            },
            success: function(response) {
                $('.container-waiting').hide();
                let obj = JSON.parse(response);
                $('#modal-duplicate-input-invoice').hide();
                if(obj.error == 0) showModalNotify(1, 'Thêm thành công');
                else showModalNotify(0, obj.message ?? 'Thao tác thất bại');
            },
            error: function(xhr, status, error) {
                $('.container-waiting').hide();
                $('#modal-duplicate-input-invoice').hide();
                showModalNotify(0, error);
            }
        });
    });

    $(document).on('input', '#modal-duplicate-input-invoice input.money', function () {
        if($(this).attr('id') == 'cost_no_vat') {
            let cost_no_vat = unformatNumber($('#modal-duplicate-input-invoice input#cost_no_vat').val());
            let vat = cost_no_vat * 0.08;
            $('#modal-duplicate-input-invoice input#vat').val(formatNumber(vat))
        }
        if($(this).attr('id') != 'cost') {
            let cost_no_vat = unformatNumber($('#modal-duplicate-input-invoice input#cost_no_vat').val());
            let vat = unformatNumber($('#modal-duplicate-input-invoice input#vat').val());
            let authorized_fee = unformatNumber($('#modal-duplicate-input-invoice input#authorized_fee').val());
            let cost = cost_no_vat + vat + authorized_fee;
            $('#modal-duplicate-input-invoice input#cost').val(formatNumber(cost))
        }

        let format_value = formatNumber(unformatNumber($(this).val()));
        $(this).val(format_value);
    });

    $(document).on('input', '#im_ticketing_fee, #im_ticketing_fee_vat_percent', function () {
        let fee     = unformatNumber($('#im_ticketing_fee').val());
        let percent = $('#im_ticketing_fee_vat_percent').val();
        let vat     = fee * percent;
        $('#im_ticketing_fee').val(formatNumber(fee));
        $('#im_ticketing_fee_vat').val(formatNumber(vat));
        $('#im_ticketing_fee_no_vat').val(formatNumber(fee - vat));
    });
});

function calculateTicketPrice(is_cal_vat = 0) {
    let qty = unformatNumber($("#im_qty").val());
    let cost = unformatNumber($("#im_cost").val());
    let authorized = unformatNumber($("#im_authorized").val());

    let vat = 0;
    let vat_per = parseFloat($("#im_vat_percent").val());
    if (is_cal_vat) vat = Math.round(cost * vat_per);
    else vat = unformatNumber($("#im_vat").val());

    $("#im_cost").val(formatNumber(cost));
    $("#im_vat").val(formatNumber(vat));
    $("#im_cost_vat").val(formatNumber(cost + vat));
    $("#im_total").val(formatNumber(cost + vat + authorized));
}

function check_import_form() {
    // Tất cả các trường đều phải nhập
    addToValidate('implement_invoice_frm', 'im_invoice_date', 'date', true, 'Ngày phải nhập theo cú pháp: 28-02-2022');
    addToValidate('implement_invoice_frm', 'im_accounting_date', 'date', true, 'Ngày phải nhập theo cú pháp: 28-02-2022');
    addToValidate('implement_invoice_frm', 'im_invoice_number', 'varchar', true, 'Không được để trống');
    addToValidate('implement_invoice_frm', 'im_invoice_serial', 'varchar', true, 'Không được để trống');
    addToValidate('implement_invoice_frm', 'im_ticket_code', 'varchar', true, 'Không được để trống');
    addToValidate('implement_invoice_frm', 'im_booking_id', 'relate', true, 'Không được để trống');
    addToValidate('implement_invoice_frm', 'im_supplier', 'dropdown', true, 'Không được để trống');
    // addToValidateMoreThan('implement_invoice_frm', 'im_qty', 'int', true, 'Phải > 0', 1);
    // addToValidateMoreThan('implement_invoice_frm', 'im_total', 'int', true, 'Phải > 0', 1);

    if (!check_form('implement_invoice_frm')) {
        return false;
    } else {
        // kiểm tra hành trình
        var flight_type = $("#im_flight_type").val();
        var iti = $("#im_iti").val();
        if (typeof iti != 'undefined' && iti != '') {
            iti_arr = iti.split('-');
            // 1 chiều
            if (iti_arr.length != 2 && flight_type == 0) {
                alert("Hành trình nhập sai cú pháp. Cú pháp đúng: SGN-HAN");
                $("#im_iti").focus();
                return false;
            } else if (iti_arr.length != 3 && flight_type == 1) { // 2 chiều
                alert("Hành trình nhập sai cú pháp. Cú pháp đúng: SGN-HAN-SGN");
                $("#im_iti").focus();
                return false;
            }
        }
    }
    return true;
}

function set_inv_return(popupReplyData) {
    fromPopupReturn = true;
    var formName = popupReplyData.form_name;
    var nameToValueArray = popupReplyData.name_to_value_array;
    for (var theKey in nameToValueArray) {
        var displayValue = nameToValueArray[theKey].replace(/&amp;/gi, '&').replace(/&lt;/gi, '<').replace(/&gt;/gi, '>').replace(/&#039;/gi, '\'').replace(/&quot;/gi, '"');
        document.getElementById(theKey).value = displayValue;
    }

    $.ajax({
        url: ENTRYPOINT,
        type: "POST",
        contentType: "application/json",
        dataType: 'json',
        cache: false,
        data: JSON.stringify({
            class: "entryInputInvoiceClass",
            method: "getTicketNumberInfoById",
            params: {
                ticketId: $("#im_ticket_id").val().trim(),
            }
        }),
        success: function (response) {
            if(response.status != 1) {
                showToastWarning('Không tìm thấy dữ liệu số vé');
                return;
            }

            let result = response.data ?? {};
            
            $("#im_invoice_date").val(result.inv_date);
            $("#im_invoice_number").val(result.inv_number);
            $("#im_invoice_serial").val(result.inv_serial);
            $("#im_iti").val(result.inv_iti);
            $("#im_booking").val(result.inv_booking);
            $("#im_booking_id").val(result.inv_booking_id);
            $("#im_supplier").val(result.inv_supplier);
            $("#im_qty").val(result.inv_qty);
            $("#im_accounting_date").val(result.inv_accounting_date);
            $("#im_ticket_type").val(result.inv_ticket_type)
            $("#im_vat_percent").val(result.inv_vat_percent);
            $("#im_vat").val(formatNumber(result.inv_vat));
            $("#im_cost").val(formatNumber(result.inv_cost));
            $("#im_cost_vat").val(formatNumber(result.inv_cost_vat));
            $("#im_authorized").val(formatNumber(result.inv_authorized));
            $("#im_total").val(formatNumber(result.inv_total));
            // Xác định là 1 chiều hay 2 chiều
            if (result.inv_iti.split("-").length == 2) $("#im_flight_type").val(0);
            else $("#im_flight_type").val(1);
        }
    });
}

function insertNewInvRow(ln) {
    var html = "<tr>";
    let icon_calendar = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>';

    html += "<td class='text-center sep_order'>" + ln + "</td>";
    html += "<td class='text-center'>" + $("#inv_add_row").attr("inv_date") + "</td>";
    html += "<td class='text-center'>" + $("#inv_add_row").attr("inv_number") + "</td>";
    html += "<td class='text-center'>" + $("#inv_add_row").attr("inv_seri") + "</td>";
    html += "<td><input type='text' name='sep_ticket_number[]' id='sep_ticket_number" + ln + "' class='sep_input'></td>";
    html += "<td><input type='text' name='sep_qty[]' class='sep_input text-center' id='sep_qty" + ln + "' value='1'></td>";
    html += "<td><input type='text' name='sep_iti[]' id='sep_iti" + ln + "' class='sep_input text-center'></td>";
    html += "<td><input type='text' name='sep_cost[]' id='sep_cost" + ln + "' class='sep_input text-end allow_number_only invoice_cost'></td>";
    html += "<td><input type='text' name='sep_vat[]' id='sep_vat" + ln + "' class='sep_input text-end allow_number_only invoice_vat'></td>";
    html += "<td><input type='text' name='sep_cost_vat[]' id='sep_cost_vat" + ln + "' class='sep_input text-end allow_number_only invoice_cost_vat'></td>";
    html += "<td><input type='text' name='sep_authorized[]' id='sep_atuhorized" + ln + "' class='sep_input text-end allow_number_only authorized_fee'></td>";
    html += "<td class='text-end ln_total allow_number_only'></td>";
    html += "\
            <td class='sep_bk_col'>\
                <input type='text' name='sep_booking[]' class='sep_input text-center'>\
            </td>";
    html += "\
            <td> \
                <select name='sep_supplier[]' class='sep_supplier'>" + $("#sep_supplier_opt").val() + "</select> \
            </td>";
    html += "\
            <td class='text-center'>\
                <button title='Xóa' type='button' class='button-remove-in-edit' onclick='markInvRowDeleted(" + ln + ")'> \
                    " + icon_calendar + "\
                </button> \
                <input type='hidden' name='sep_deleted[]' id='sep_deleted" + ln + "' value='0'> \
            </td>";
    html += "</tr>";

    return html;
}

function markInvRowDeleted(ln) {
    $("#sep_deleted" + ln).val(1);
    $("#sep_deleted" + ln).parent().parent().hide();

    markInvOrder();
    calculateInvTotal();
}

function getCurrentDate() {
    const today = new Date();
    const dd = String(today.getDate()).padStart(2, '0'); // Day with leading zero
    const mm = String(today.getMonth() + 1).padStart(2, '0'); // Month (0-based)
    const yyyy = today.getFullYear();
    return `${dd}-${mm}-${yyyy}`;
}