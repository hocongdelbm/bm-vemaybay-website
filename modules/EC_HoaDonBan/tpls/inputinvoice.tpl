<script src="custom/jqueryui/plugins/jquery.number.min.js"></script>
<script src="custom/jqueryui/plugins/formatNumber.js"></script>
{literal}
    <style>
        #preview_frm>.top_area {
            text-align: center;
            position: sticky;
            top: -1px;
            background-color: #fff;
            padding: 5px;
        }

        #preview_frm .warning_text {
            color: var(--red-vj-color);
            margin-bottom: 5px;
        }

        #preview_frm .other_fee {
            background-color: #DDA0DD;
        }

        #data_tbl thead tr.line_1 th,
        #data_tbl thead tr.line_2 th {
            position: sticky;
        }
/* 
        #data_tbl tbody tr:hover
        , #data_tbl thead tr.line_2:hover {
            background-color: #cfeafe;
        } */

        #data_tbl .available {
            font-weight: bold;
            color: green;
        }
        #data_tbl .outofstock {
            font-weight: bold;
            color: var(--red-vj-color);
        }
        #data_tbl .err_minus {
            background-color: #F08080;
        }
     
        #data_tbl .sep_bk_col {
            position: relative;
        }
        .red_cl {
            color: var(--red-vj-color);
        }
     
        #page_number {
            width: 50px;
            text-align: center;
        }

        #pagination_tbl input{
            width: 50px !important;
        }
    </style>

    <script>
        $(document).ready(function() {
            // những input chỉ cho nhập số và format currency
            $(".allow_number_only").number(true, 0, dec_sep, num_grp_sep);

            $("#supplier").change(function() {
                $("#ticket_code").val($(this).children("option:selected").attr("data-ticket-code"));
                $("#pass_qty").val($(this).children("option:selected").attr("data-pass-qty"));
                $("#itinerary").val($(this).children("option:selected").attr("data-itinerary"));
                $("#ticket_price").val($(this).children("option:selected").attr("data-ticket-price"));
                $("#supplier_name").val($(this).children("option:selected").text());
            });

            $("#import_invoice_frm").submit(function() {
                addToValidate('import_invoice_frm', 'invoice_date', 'date', true, 'Ngày phải nhập theo cú pháp: 28-02-2022');
                addToValidate('import_invoice_frm', 'invoice_number', 'varchar', true, 'Không được để trống');
                addToValidate('import_invoice_frm', 'invoice_serial', 'varchar', true, 'Không được để trống');
                addToValidate('import_invoice_frm', 'accounting_date', 'date', true, 'Ngày phải nhập theo cú pháp: 28-02-2022');
                if(!check_form('import_invoice_frm')) {
                    return false;
                }
                $("#import_btn").prop("disabled", true);
                $("#noti_line").text("Hệ thống đang xử lý dữ liệu. Vui lòng chờ trong giây lát...");
            });

            // xoá hoá đơn bắt buộc phải nhập số hoá đơn, ký hiệu hoá đơn
            $("#remove_frm").submit(function() {
                addToValidate('remove_frm', 'rm_invoice_number', 'varchar', true, 'Không được để trống');
                addToValidate('remove_frm', 'rm_invoice_serial', 'varchar', true, 'Không được để trống');
                if(!check_form('remove_frm')) {
                    return false;
                }
            });

            // nếu có form confirm 
            if($("#preview_frm").length > 0) {
                $("#data_tbl th ").css("top", "65px");
            } else {
                $("#data_tbl thead tr.line_1 th").css("top", "75px");
                $("#data_tbl thead tr.line_2 th").css("top", "130px");
            }

            // nút xoá tìm kiếm
            $("#clear_btn").click(function() {
                $("#search_frm input[type='text'], #search_frm select").val("");
                $("#page_number_hid, #page_number").val(1);
                $("#search_frm").submit();
            });

            // tính lại tổng giá vốn
            $(".invoice_cost, .invoice_vat, .invoice_cost_vat, .authorized_fee").keyup(function() {
                var total_cost = total_vat = total_cost_vat = total_authorized = total = 0;  

                $(".invoice_cost").each(function(idx) {
                    var vat             = parseInt($(".invoice_vat").eq(idx).val() || 0);
                    var cost            = parseInt($(".invoice_cost").eq(idx).val() || 0);
                    var authorized_fee  = parseInt($(".authorized_fee").eq(idx).val() || 0);

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

            $("#btn_booking").click(function() {
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

            $("#btn_clr_booking").click(function() {
                $("#im_booking_id").val('');
                $("#im_booking").val('');
                $("#im_booking").focus();
            });

            $("#btn_ticket_code").click(function() {
                open_popup(
                "EC_Input_Invoices", 600, 400, "", true, false,
                {
                    "call_back_function": "set_inv_return",
                    "form_name": "implement_invoice_frm",
                    "field_to_name_array": {
                        "id": "im_ticket_code_id",
                        "name": "im_ticket_code"
                    }
                }, "single", true);
            });

            $("#btn_clr_ticket_code").click(function() {
                $("#implement_invoice_frm input, #implement_invoice_frm select").not("input[type='button'], input[type='submit']").val('');
                $("#im_ticket_code").focus();
            });

            // kiểm tra khi tìm kiếm
            $("#search_frm").submit(function() {

                $("#page_number_hid").val($("#page_number").val());
                addToValidate('search_frm', 'from_date', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
                addToValidate('search_frm', 'to_date', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
                addToValidate('search_frm', 'from_date_accounting', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
                addToValidate('search_frm', 'to_date_accounting', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
                addToValidateRange('search_frm', 'page_number', 'int', true, 'Lỗi', 1, parseInt($("#page_number").attr("max")));

                if(check_form('search_frm')) {
                    $("#search_page_btn").prop("disabled", true);
                    $("#page_number").prop("readonly", true);
                    $(".pagination_txt").addClass('disabled');
                    return true;
                }

                $("#page_number").prop("readonly", false);
                $("#search_page_btn").prop("disabled", false);
                $(".pagination_txt").removeClass('disabled');

                if($("#page_number").val() <= 1) { 
                    $(".lpagi").addClass('disabled'); 
                } else if($("#page_number").val() >= parseInt($("#page_number").attr('max'))) {
                    $(".rpagi").addClass('disabled');
                }
                return false;
            });

            // lưu số vé
            $("#implement_invoice_frm").submit(function() {
                $("#import_btn").prop("disabled", true);    
                if(!check_import_form()) {
                    $("#import_btn").prop("disabled", false);
                    return false;
                } else {
                    $("#noti_line").text("Hệ thống đang lưu số vé. Vui lòng đợi trong giây lát...");
                }
            });

            // thêm dòng số vé, dùng khi tách giá trong cùng số vé 
            // nhưng dành loại khách khác nhau (người lớn / trẻ em / trẻ sơ sinh)
            $("#inv_add_row").click(function() {
                var inv_row_count = $("#inv_row_count").val(); 
                $("#data_tbl tbody #last_row").before(insertNewInvRow(inv_row_count));
                $("#inv_row_count").val(++inv_row_count);
                $(".allow_number_only").number(true, 0, dec_sep, num_grp_sep);
                markInvOrder();
            });

            // nhảy sang trang
            $(".pagination_txt").click(function() {

                if($(this).hasClass("disabled")) {
                    return false;
                } else {
                    var curr_page = parseInt($("#page_number").val());
                    var page_set = $(this).text().trim();

                    if(page_set == 'First') {
                        $("#page_number").val(1);
                    } else if(page_set == 'Prev' && curr_page > 1) {
                        $("#page_number").val(curr_page - 1);
                    } else if(page_set == 'Next' && curr_page < parseInt($("#page_number").attr('max'))) {
                        $("#page_number").val(curr_page + 1);
                    } else if(page_set == 'Last') {
                        $("#page_number").val($("#page_number").attr('max'));
                    }
                    $("#search_frm").submit();   
                }
            });
            $("#search_page_btn").click(function() {
                $("#search_frm").submit();
            });

            Calendar.setup ({
                inputField : "from_date",
                daFormat : "%d-%m-%Y %H:%M",
                button : "from_date_trigger",
                singleClick : true,
                dateStr : "",
                step : 1,
                weekNumbers:false
            });
            Calendar.setup ({
                inputField : "to_date",
                daFormat : "%d-%m-%Y %H:%M",
                button : "to_date_trigger",
                singleClick : true,
                dateStr : "",
                step : 1,
                weekNumbers:false
            });

            Calendar.setup ({
                inputField : "from_date_accounting",
                daFormat : "%d-%m-%Y %H:%M",
                button : "from_date_accounting_trigger",
                singleClick : true,
                dateStr : "",
                step : 1,
                weekNumbers:false
            });
            Calendar.setup ({
                inputField : "to_date_accounting",
                daFormat : "%d-%m-%Y %H:%M",
                button : "to_date_accounting_trigger",
                singleClick : true,
                dateStr : "",
                step : 1,
                weekNumbers:false
            });
        });

        function calculateTicketPrice(is_cal_vat = 0) {
            
            var qty             = unformatNumber($("#im_qty").val());
            var cost            = unformatNumber($("#im_cost").val());
            var authorized      = unformatNumber($("#im_authorized").val());

            if(is_cal_vat) {
                var vat = Math.round(cost * 0.08);
            } else var vat = unformatNumber($("#im_vat").val());

            $("#im_vat").val(formatNumber(vat));
            $("#im_cost_vat").val(formatNumber(cost + vat));
            $("#im_total").val(formatNumber(cost + vat + authorized));
        }

        function check_import_form() {
            // tất cả các trường đều phải nhập
            addToValidate('implement_invoice_frm', 'im_invoice_date', 'date', true, 'Ngày phải nhập theo cú pháp: 28-02-2022');
            addToValidate('implement_invoice_frm', 'im_accounting_date', 'date', true, 'Ngày phải nhập theo cú pháp: 28-02-2022');
            addToValidate('implement_invoice_frm', 'im_invoice_number', 'varchar', true, 'Không được để trống');
            addToValidate('implement_invoice_frm', 'im_invoice_serial', 'varchar', true, 'Không được để trống');
            addToValidate('implement_invoice_frm', 'im_ticket_code', 'varchar', true, 'Không được để trống');
            addToValidate('implement_invoice_frm', 'im_booking_id', 'relate', true, 'Không được để trống');
            addToValidate('implement_invoice_frm', 'im_supplier', 'dropdown', true, 'Không được để trống');
            // addToValidateMoreThan('implement_invoice_frm', 'im_qty', 'int', true, 'Phải > 0', 1);
            // addToValidateMoreThan('implement_invoice_frm', 'im_total', 'int', true, 'Phải > 0', 1);

            if(!check_form('implement_invoice_frm')) {
                return false;
            } else {
                // kiểm tra hành trình
                var ticket_type = $("#im_ticket_type").val();
                var flight_type = $("#im_flight_type").val();
                var iti = $("#im_iti").val();
                if(typeof iti != 'undefined' && iti != '') {
                    iti_arr = iti.split('-');
                    // 1 chiều
                    if(iti_arr.length != 2 && flight_type == 0) {
                        alert("Hành trình nhập sai cú pháp. Cú pháp đúng: SGN-HAN");
                        $("#im_iti").focus();
                        return false;
                    } else if(iti_arr.length != 3 && flight_type == 1) { // 2 chiều
                        alert("Hành trình nhập sai cú pháp. Cú pháp đúng: SGN-HAN-SGN");
                        $("#im_iti").focus();
                        return false;
                    }
                }
            }
            return true;
        }

        function set_inv_return(popupReplyData) {
            fromPopupReturn         = true;
            var formName            = popupReplyData.form_name;
            var nameToValueArray    = popupReplyData.name_to_value_array;
            for (var theKey in nameToValueArray) {
                var displayValue = nameToValueArray[theKey].replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');
                document.getElementById(theKey).value = displayValue;
            }

            $.ajax({
                url: "index.php?entryPoint=entryPointEC_HoaDonBan",
                type: "POST",
                data: {
                    ticket_code: $("#im_ticket_code_id").val(),
                    for: "getTicketCodeInf",
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    $("#im_invoice_date").val(result.invoice_date);
                    $("#im_invoice_number").val(result.invoice_number);
                    $("#im_invoice_serial").val(result.invoice_serial);
                    $("#im_iti").val(result.invoice_iti);
                    $("#im_booking").val(result.invoice_booking);
                    $("#im_booking_id").val(result.invoice_booking_id);
                    $("#im_supplier").val(result.invoice_supplier);
                    $("#im_qty").val(result.invoice_qty);
                    $("#im_cost").val(result.invoice_cost);
                    $("#im_vat").val(result.invoice_vat);
                    $("#im_cost_vat").val(result.invoice_cost_vat);
                    $("#im_authorized").val(result.invoice_authorized);
                    $("#im_total").val(result.invoice_total);
                    $("#im_accounting_date").val(result.invoice_accounting_date);
                    // xác định là 1 chiều hay 2 chiều
                    if(result.invoice_iti.split("-").length == 2) {
                        $("#im_flight_type").val(0);
                    } else {
                        $("#im_flight_type").val(1);
                    }
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

    </script>
{/literal}

<div class="box-section">
    <div id="import_invoice">

        <ul class="nav nav-tabs admin_tabs-list input-invoice-tabs" id="input-invoice-tabs" role="tablist">
            <li class="admin_tabs-item" role="presentation">
                <a class="active" id="search-tab" data-bs-toggle="tab" data-bs-target="#search-tab-pane" type="button" role="tab" aria-controls="search-tab-pane" aria-selected="true">Tìm kiếm</a>
            </li>
            <li class="admin_tabs-item" role="presentation">
                <a class="" id="import-tab" data-bs-toggle="tab" data-bs-target="#import-tab-pane" type="button" role="tab" aria-controls="import-tab-pane" aria-selected="false">Import</a>
            </li>
            <li class="admin_tabs-item" role="presentation">
                <a class="" id="delete-invoice-tab" data-bs-toggle="tab" data-bs-target="#delete-invoice-tab-pane" type="button" role="tab" aria-controls="delete-invoice-tab-pane" aria-selected="false">Xoá hoá đơn đã nạp</a>
            </li>
            <li class="admin_tabs-item" role="presentation">
                <a class="" id="edit-invoice-tab" data-bs-toggle="tab" data-bs-target="#edit-invoice-tab-pane" type="button" role="tab" aria-controls="edit-invoice-tab-pane" aria-selected="false">Bổ sung / Chỉnh sửa số vé</a>
            </li>
        </ul>
        <div class="tab-content" id="input-invoice-tabs__content">
            <div class="tab-pane fade show active" id="search-tab-pane" role="tabpanel" aria-labelledby="search-tab" tabindex="0">
                <div class="box-tabs">
                    <form id="search_frm" name="search_frm" method="post" action="index.php">
                        <input type="hidden" name="module" value="EC_HoaDonBan">
                        <input type="hidden" name="action" value="inputinvoice">

                        <table cellpadding="0" cellspacing="0" class="table-tabs table-invoice table-search__frm">
                            <tbody>
                                <tr>
                                    <td width="12%"><span class="label">Ngày hoá đơn:</span></td>
                                    <td width="21%">
                                        <div class="d-flex align-items-center gap-2">
                                            
                                            <span class="dateTime d-flex position-relative">
                                                <input autocomplete="off" type="text" class="date_input box-input" name="from_date" id="from_date" value="{$FROM_DATE}" size="7" maxlength="10"> 
                                                <button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                                                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                                                        </svg>
                                                </button>
                                            </span>
                                            
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"/>
                                            </svg>

                                            <span class="dateTime d-flex position-relative">
                                                <input autocomplete="off" type="text" class="date_input box-input" name="to_date" id="to_date" value="{$TO_DATE}" size="7" maxlength="10">
                                                <button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                                                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                                                        </svg>
                                                </button>
                                            </span>
                                        </div>
                                    </td>
                                    <td width="12%"><span class="label">Số vé:</span></td>
                                    <td width="21%">
                                        <input class="box-input" type="text" name="ticket_code" value="{$TICKET_CODE}">
                                    </td>
                                    <td width="12%"><span class="label">Số hoá đơn:</span></td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="invoice_number" id="invoice_number" value="{$INVOICE_NUMBER}">
                                    </td>
                                </tr>
                                <tr>
                                    <td width="12%"><span class="label">Ngày hạch toán:</span></td>
                                    <td width="21%">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="dateTime d-flex position-relative">
                                                <input autocomplete="off" type="text" class="date_input box-input" name="from_date_accounting" id="from_date_accounting" value="{$FROM_DATE_ACCOUNTING}" size="7" maxlength="10"> 
                                                <button class="icon_dateTime" type="button" id="from_date_accounting_trigger" onclick="return false;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                                                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                                                        </svg>
                                                </button>
                                            </span>
                                            
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"/>
                                            </svg>

                                            <span class="dateTime d-flex position-relative">
                                                <input autocomplete="off" type="text" class="date_input box-input" name="to_date_accounting" id="to_date_accounting" value="{$TO_DATE_ACCOUNTING}" size="7" maxlength="10">
                                                <button class="icon_dateTime" type="button" id="to_date_accounting_trigger" onclick="return false;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                                                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                                                        </svg>
                                                </button>
                                            </span>

                                        </div>  
                                    </td>
                                    <td width="12%"><span class="label">PNR:</span></td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="ticket_c" value="{$TICKET_C}">
                                    </td>
                                    <td width="12%"><span class="label">KHHĐ:</span></td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="invoice_serial" id="invoice_serial" value="{$INVOICE_SERIAL}">
                                    </td>
                                </tr>
                                <tr>
                                    <td width="12%"><span class="label">Nhà cung cấp:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="supplier">{$SUPPLIER_OPTION}</select>
                                    </td>
                                    <td width="12%"><span class="label">Thiếu booking:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="missing_bk">{$MISSING_BK}</select>
                                    </td>
                                    <td width="12%"><span class="label">Thiếu SL:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="missing_qty">{$MISSING_QTY}</select>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="12%"><span class="label">Tình trạng tồn:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="stock_stt">{$STOCK_STT}</select>
                                    </td>
                                    <td width="12%"><span class="label">Xuất dư:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="over_qty">{$OVER_QTY}</select>
                                    </td>  
                                    <td width="12%"><span class="label">Đơn vị:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="company_unit">{$COMPANY_UNIT_OPTION}</select>
                                    </td>    
                                </tr>
                                {if !$PREVIEW}
                                <tr>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <input type="hidden" id="page_number_hid" name="page_number" value="{$CURRENT_PAGE}">
                                            <input type="submit" class="btn btn-primary" value="Tìm kiếm" id="search_btn">
                                            <input type="submit" class="btn btn-secondary" value="Xóa trắng" id="clear_btn" name="clear_search">
                                            <!-- <input type="button" class="btn btn-success" value="Xuất Excel" id="export_btn"> -->
                                        </div>
                                    </td>
                                </tr>
                                {/if}
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="import-tab-pane" role="tabpanel" aria-labelledby="import-tab" tabindex="0">
                <div class="box-tabs">
                    <form id="import_invoice_frm" name="import_invoice_frm" method="post" enctype='multipart/form-data'>
                        <input type="hidden" name="module" value="EC_HoaDonBan">
                        <input type="hidden" name="action" value="inputinvoice">
                        <input type="hidden" name="importfile">

                        <table cellpadding="0" cellspacing="0" class="table-tabs table-invoice table-import">
                            <tbody>
                                <tr>
                                    <td width="13%"><span class="label">Nhà cung cấp</span></td>
                                    <td width="20%">
                                        <select class="box-select" name="supplier" id="supplier">
                                            <option value="VJA" data-ticket-code="B" data-pass-qty="H" data-itinerary="G" data-ticket-price="L">VietjetAir</option>
                                            <option value="BBA" data-ticket-code="B" data-pass-qty="C" data-itinerary="" data-ticket-price="D,E,F">Bamboo</option>
                                            <option value="VNA" data-ticket-code="A" data-pass-qty="C" data-itinerary="A" data-ticket-price="D">Vietnam Airlines</option>
                                            <option value="VTA" data-ticket-code="B" data-pass-qty="" data-itinerary="C,D" data-ticket-price="G">Vietravel</option>
                                            <option value="HNH" data-ticket-code="B" data-pass-qty="F,G" data-itinerary="C" data-ticket-price="H,I">Hồng Ngọc Hà</option>
                                            <option value="TH" data-ticket-code="B" data-pass-qty="D" data-itinerary="B" data-ticket-price="E">Thành Hoàng</option>
                                            <option value="PNA" data-ticket-code="B,C,D" data-pass-qty="F" data-itinerary="B,C,D" data-ticket-price="G,H">Phương Nam</option>
                                        </select>
                                        <input type="hidden" name="supplier_name" id="supplier_name" value="VietjetAir">
                                    </td>
                                    <td><span class="label">File</span></td>
                                    <td><input type="file" name="from_file"></td>
                                    <td><span class="label">Cột số vé</span></td>
                                    <td><input class="box-input" type="text" name="ticket_code" id="ticket_code" value="B"></td>
                                </tr>
                                <tr>
                                    <td><span class="label">Số hoá đơn</span><span class="required">*</span></td>
                                    <td><input type="text" class="box-input" name="invoice_number"></td>
                                    <td><span class="label">Ký hiệu hoá đơn</span><span class="required">*</span></td>
                                    <td><input type="text" class="box-input" name="invoice_serial"></td>
                                    <td><span class="label">Cột số lượng</span></td>
                                    <td><input type="text" class="box-input" name="pass_qty" id="pass_qty" value="F"></td>   
                                </tr>
                                <tr>
                                    <td><span class="label">Ngày hạch toán</span><span class="required">*</span></td>
                                    <td><input type="text" class="box-input" name="accounting_date" id="accounting_date"></td>
                                    <td><span class="label">Ngày hoá đơn</span><span class="required">*</span></td>
                                    <td><input type="text" class="box-input" name="invoice_date" id="invoice_date" value="{$INVOICE_DATE}"></td>
                                    <td><span class="label">Cột giá vé</span></td>
                                    <td><input type="text" class="box-input" name="ticket_price" id="ticket_price" value="L"></td>
                                </tr>
                                <tr>
                                    <td width="13%"><span class="label">Đơn vị</span></td>
                                    <td width="20%">
                                        <select class="box-select" name="company_unit" id="company_unit">
                                            <option value="MHV">Minh Hồng Võ</option>
                                            <option value="TRAVELPASS">Travelpass</option>
                                        </select>
                                        <input type="hidden" name="company_unit_name" id="company_unit_name" value="">
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td><span class="label">Cột hành trình</span></td>
                                    <td><input type="text" class="box-input" name="itinerary" id="itinerary" value="G"></td>
                                </tr>
                                {if !$PREVIEW}
                                    <tr>
                                        <td colspan="6" class="text-center pb-0">
                                            <input type="submit" class="btn btn-primary" value="Import" id="import_btn">
                                        </td>
                                    </tr>
                                {/if}
                                <tr>
                                    <td colspan="6" id="noti_line" class="text-center pb-0"></td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="delete-invoice-tab-pane" role="tabpanel" aria-labelledby="delete-invoice-tab" tabindex="0">
                <div class="box-tabs">
                    <form id="remove_frm" name="remove_frm" method="post" action="index.php">
                        <input type="hidden" name="module" value="EC_HoaDonBan">
                        <input type="hidden" name="action" value="inputinvoice">

                        <table cellpadding="0" cellspacing="0" class="table-tabs table-invoice table-delete-invoice">
                            <tbody>
                                <tr>
                                    <td width="12%"><span class="label">Số hoá đơn</span>(<span class="required">*</span>)</td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="rm_invoice_number" id="rm_invoice_number" value="">
                                    </td>
                                    <td width="12%"><span class="label">KHHĐ</span>(<span class="required">*</span>)</td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="rm_invoice_serial" id="rm_invoice_serial" value="">
                                    </td>
                                    <td width="12%"><span class="label">Nhà cung cấp</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="rm_supplier">{$SUPPLIER_OPTION}</select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="center">
                                        <input type="submit" value="Xoá HĐ" class="btn btn-danger" id="remove_btn" name="remove">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="edit-invoice-tab-pane" role="tabpanel" aria-labelledby="edit-invoice-tab" tabindex="0">
                <div class="box-tabs">
                    <form id="implement_invoice_frm" name="implement_invoice_frm" action="index.php" method="post">
                        <input type="hidden" name="module" value="EC_Input_Invoices">
                        <input type="hidden" name="action" value="Save"> 

                        <table cellspacing="0" cellpadding="0" class="table-tabs table-invoice table-edit-invoice">
                            <tbody>
                                <tr>
                                    <td width="13%"><span class="label">Ngày hoá đơn</span></td>
                                    <td width="20%"><input type="text" class="box-input" id="im_invoice_date" name="im_invoice_date"></td>
                                    <td width="13%"><span class="label">Chuyến bay</span></td>
                                    <td width="20%">
                                        <select class="box-select" id="im_flight_type">
                                            <option value="0">Một chiều</option>
                                            <option value="1">Khứ hồi</option>
                                        </select>
                                    </td>
                                    <td width="13%"><span class="label">Số lượng</span></td>
                                    <td width="20%"><input type="text" class="box-input" id="im_qty" name="im_qty" oninput="calculateTicketPrice();"></td>
                                   
                                </tr>
                                <tr>
                                    <td><span class="label">Số hoá đơn</span></td>
                                    <td><input type="text" class="box-input" id="im_invoice_number" name="im_invoice_number"></td>
                                    <td><span class="label">Hành trình</span></td>
                                    <td><input type="text" class="box-input" id="im_iti" name="im_iti"></td>
                                    <td><span class="label">Giá vốn</span></td>
                                    <td><input type="text" class="box-input" id="im_cost" name="im_cost" oninput="calculateTicketPrice(1);"></td>
                                </tr>
                                <tr>
                                    <td><span class="label">KHHĐ</span></td>
                                    <td><input type="text" class="box-input" id="im_invoice_serial" name="im_invoice_serial"></td>
                                    <td><span class="label">Số vé</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="text" class="box-input" id="im_ticket_code" name="im_ticket_code">
                                            <input type="hidden" id="im_ticket_code_id" name="record">
                                            <input type="button" name="btn_ticket_code" id="btn_ticket_code" title="Chọn [Alt+T]" accesskey="T" class="btn btn-primary" value="Chọn">
                                            <input type="button" name="btn_clr_ticket_code" id="btn_clr_ticket_code" title="Xóa [Alt+C]" accesskey="C" class="btn btn-danger" value="Xóa">
                                        </div>
                                    </td>
                                    <td><span class="label">VAT</span></td>
                                    <td><input type="text" class="box-input" id="im_vat" name="im_vat" oninput="calculateTicketPrice();"></td>
                                </tr>
                                <tr>
                                    <td><span class="label">Nhà cung cấp</span></td>
                                    <td><select class="box-select" id="im_supplier" name="im_supplier">{$SUPPLIER_OPTION}</select></td>
                                    <td><span class="label">Booking</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="text" class="box-input" id="im_booking" name="im_booking">
                                            <input type="button" name="btn_booking" id="btn_booking" title="Chọn [Alt+T]" accesskey="T" class="btn btn-primary" value="Chọn">
                                            <input type="button" name="btn_clr_booking" id="btn_clr_booking" title="Xóa [Alt+C]"  accesskey="C" class="btn btn-danger" value="Xóa">
                                            <input type="hidden" id="im_booking_id" name="im_booking_id">
                                        </div>
                                    </td>
                                    <td><span class="label">Giá vốn (VAT)</span></td>
                                    <td><input type="text" class="box-input" id="im_cost_vat" name="im_cost_vat" oninput="calculateTicketPrice();"></td>
                                </tr>
                                <tr>
                                    <td><span class="label">Ngày hạch toán</span></td>
                                    <td><input class="box-input" type="text" id="im_accounting_date" name="im_accounting_date"></td>
                                    <td><span class="label">Tổng</span></td>
                                    <td><input class="box-input" type="text" class="allow_number_only" id="im_total" name="im_total" oninput="calculateTicketPrice();"></td>
                                    <td><span class="label">Thu hộ</span></td>
                                    <td><input type="text" class="box-input" id="im_authorized" name="im_authorized" oninput="calculateTicketPrice();"></td>
                                </tr>
                                <tr>
                                    <td width="13%"><span class="label">Đơn vị</span></td>
                                    <td width="20%">
                                        <select class="box-select" id="im_company_unit" name="im_company_unit">
                                            {$COMPANY_UNIT_OPTION}
                                        </select>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-center pb-0">
                                        <input type="submit" class="btn btn-primary" id="save_btn" value="Lưu">
                                    </td>
                                </tr>
                            </tbody>
                        </table>        
                    </form>
                </div>
            </div>

            <input type="hidden" id="grp_seperator" value=",">
            <input type="hidden" id="dec_seperator" value=".">
            <input type="hidden" id="sig_digits" value="0">
        </div>

        <h1 class="title text-center">Hoá đơn đầu vào{$SUPPLIER}</h1>
        {if $PREVIEW}
            {$CONFIRM_FRM}
        {else}
            <table id="pagination_tbl">
                <tbody>
                    <tr>
                        <td class="center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="pagination_txt lpagi d-flex align-items-center gap-2 {$LDISABLED}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-double-left" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8.354 1.646a.5.5 0 0 1 0 .708L2.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                                        <path fill-rule="evenodd" d="M12.354 1.646a.5.5 0 0 1 0 .708L6.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                                    </svg>
                                    First
                                </span>
                                <span class="pagination_txt lpagi d-flex align-items-center gap-2 {$LDISABLED}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                                    </svg> 
                                Prev
                                </span>
                                <input id="page_number" class="allow_number_only box-input" value="{$CURRENT_PAGE}" max="{$MAX_PAGE}"> / <span class="allow_number_only">{$MAX_PAGE}</span>
                                <input type="button" class="btn btn-primary" id="search_page_btn" value="GO">
                                <span class="pagination_txt rpagi d-flex align-items-center gap-2 {$RDISABLED}">
                                    Next
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                    </svg>
                                </span>
                                <span class="pagination_txt rpagi d-flex align-items-center gap-2 {$RDISABLED}">
                                    Last
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-double-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M3.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L9.293 8 3.646 2.354a.5.5 0 0 1 0-.708z"/>
                                        <path fill-rule="evenodd" d="M7.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L13.293 8 7.646 2.354a.5.5 0 0 1 0-.708z"/>
                                    </svg>
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <table id="data_tbl" cellspacing="0" cellpadding="0" class="table-input__invoice table-details__booking">
                <thead>
                    <tr class="line_1">
                        <th width="2%">STT</th>
                        <th width="7%">Ngày<br>hạch toán</th>
                        <th width="7%">Ngày HĐ</th>
                        <th width="7%">Số HĐ</th>
                        <th width="6%">KHHĐ</th>
                        <th width="8%">Số vé</th>
                        <!-- <th width="7%">PNR</th> -->
                        <th width="3%">SL</th>
                        <th width="3%">Xuất</th>
                        <th width="3%">Còn</th>
                        <th width="6%">Hành trình</th>
                        <th width="7%">Giá vốn</th>
                        <th width="7%">VAT</th>
                        <th width="7%">Giá vốn (VAT)</th>
                        <th width="6%">Thu hộ</th>
                        <th width="7%">Tổng</th>  
                        <th width="6%">Booking</th>   
                        <th width="3%">NCC</th>
                        <th>Đơn vị</th>
                    </tr>
                    <tr class="line_2">
                        <th class="right" colspan="6"><b>Tổng</b></th>
                        <th class="center">{$TOTAL_QTY}</th>
                        <th class="center">{$TOTAL_EXPORT}</th>
                        <th class="center">{$TOTAL_LEFT}</th>
                        <th></th>
                        <th class="right">{$TOTAL_COST}</th>
                        <th class="right">{$TOTAL_VAT}</th>
                        <th class="right">{$TOTAL_COST_VAT}</th>
                        <th class="right">{$TOTAL_AUTHORIZED}</th>
                        <th class="right">{$TOTAL}</th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {$DATA}
                </tbody>
            </table>
        {/if}
    </div>
</div>