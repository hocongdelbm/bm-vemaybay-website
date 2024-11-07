$(document).ready(function () {
    Calendar.setup({
        inputField: "from_date",
        daFormat: "%d-%m-%Y %H:%M",
        button: "from_date_trigger",
        singleClick: true,
        dateStr: "'.date('d-m-Y').'",
        step: 1,
        weekNumbers: false
    });
    Calendar.setup({
        inputField: "to_date",
        daFormat: "%d-%m-%Y %H:%M",
        button: "to_date_trigger",
        singleClick: true,
        dateStr: "'.date('d-m-Y').'",
        step: 1,
        weekNumbers: false
    });

    $(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);

    $('input[type=radio][name=type]').change(function() {
        if (this.value == 'group') {
            $('.row-voucher-code').show();
            $('.row-website').show();
        }
        else if (this.value == 'single') {
            $('.row-voucher-code').hide();
            $('.row-website').hide();
        }
    });

    $("#voucher_condition").on('change', function () {
        $("#voucher_condition").after(insertConditionVoucher($(this).val()));
        $(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);
    });

    $(document).on("click", ".remove_condition", function () {
        let id_condition = $(this).attr('data-field').trim();
        $(`#${id_condition}`).remove();
    });

    $("#create_voucher_frm").submit(function () {
        let qty = parseInt($("#voucher_qty").val()) || 0;
        let code_voucher = $("#voucher_code").val();
       
        if (qty <= 0) {
            let text_warning = 'Vui lòng bổ sung số lượng voucher phát hành!';
            showToastWarning(text_warning);
            $("#voucher_qty").focus();
            return false;
        }

        if (code_voucher.length < 3 || code_voucher.length > 6) {
            let text_warning = 'Mã voucher tối thiểu 3 kí tự';
            showToastWarning(text_warning);
            $("#voucher_code").focus();
            return false;
        }

        if ($('#condition_value_total_qty').length !== 0 && $('#condition_value_total_qty').val() == '') {
            let text_warning = 'Vui lòng nhập số vé.';
            showToastWarning(text_warning);
            $("#condition_value_total_qty").focus();
            return false;
        }
        if ($('#condition_value_total_amount').length !== 0 && $('#condition_value_total_amount').val() == '') {
            let text_warning = 'Vui lòng nhập đơn giá tối thiểu.';
            showToastWarning(text_warning);
            $("#condition_value_total_amount").focus();
            return false;
        }

        if ($('#condition_value_journey').length !== 0 && $('#condition_value_journey').val() == '') {
            let text_warning = 'Vui lòng nhập hành trình áp dụng voucher.';
            showToastWarning(text_warning);
            $("#condition_value_journey").focus();
            return false;
        }

        return true;
    });
});

function insertConditionVoucher(field) {
    let condition_id = $("#" + field);

    if (field && condition_id.length === 0) {
        let text_value = '';
        let text_lass = '';
        let placeholder = '';
        let selected = '';
        let hide_type = '', hide_journey = '';

        if (field == 'total_qty') {
            text_value = 'Số vé';
            placeholder = '';
            text_lass = 'allow-number-only';
        } else if (field == 'journey') {
            text_value = 'Hành trình áp dụng';
            placeholder = 'SGN-HAN,DAD-TBB';
            selected = 'selected';
            hide_journey = 'd-none';
        } else if (field == 'min_price') {
            text_value = 'Đơn giá tối thiểu';
            placeholder = '';
            text_lass = 'allow-number-only';
        } else if (field == 'ticket_type') {
            text_value = 'Phạm vi áp dụng';
            selected = 'selected';
            hide_type = 'd-none';
        } else if (field == 'flight_type') {
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

        if (field == 'ticket_type') {
            html += `<select name="value[]" id="condition_value_${field}" class="w-33 box-select">
                                    <option value="1">Nội địa</option>
                                    <option value="2">Quốc tế</option>
                                </select>`;
        } else if (field == 'flight_type') {
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