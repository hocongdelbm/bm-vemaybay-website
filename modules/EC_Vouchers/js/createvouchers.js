$(document).ready(function () {
    Calendar.setup({
        inputField: "start_date",
        daFormat: "%d-%m-%Y %H:%M",
        button: "start_date_trigger",
        singleClick: true,
        step: 1,
        weekNumbers: false
    });
    Calendar.setup({
        inputField: "end_date",
        daFormat: "%d-%m-%Y %H:%M",
        button: "end_date_trigger",
        singleClick: true,
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


    $(document).on("keyup", "#voucher_code, #condition_value_journey", function () {
        this.value = this.value.toLocaleUpperCase();
    });

    $(document).on("input", ".numbersOnly", function () {
        $(this).val(Number($(this).val().replace(/\D/g, '')).toLocaleString());
    });

    $("#create_voucher_frm").submit(function () {
        let voucher_type = $('input[name="type"]:checked').val();
        // let voucher_code = $("#voucher_code").val();
        let quantity = parseInt($("#voucher_qty").val()) || 0;
       
        if (quantity <= 0) {
            let text_warning = 'Vui lòng bổ sung số lượng voucher phát hành!';
            showToastWarning(text_warning);
            $("#voucher_qty").focus();
            return false;
        }

        // if (voucher_code.length < 3 || voucher_code.length > 6) {
        //     let text_warning = 'Mã voucher tối thiểu 3 kí tự';
        //     showToastWarning(text_warning);
        //     $("#voucher_code").focus();
        //     return false;
        // }

        // if ($('#condition_value_total_qty').length !== 0 && $('#condition_value_total_qty').val() == '') {
        //     let text_warning = 'Vui lòng nhập số vé.';
        //     showToastWarning(text_warning);
        //     $("#condition_value_total_qty").focus();
        //     return false;
        // }
        // if ($('#condition_value_total_amount').length !== 0 && $('#condition_value_total_amount').val() == '') {
        //     let text_warning = 'Vui lòng nhập đơn giá tối thiểu.';
        //     showToastWarning(text_warning);
        //     $("#condition_value_total_amount").focus();
        //     return false;
        // }

        // if ($('#condition_value_journey').length !== 0 && $('#condition_value_journey').val() == '') {
        //     let text_warning = 'Vui lòng nhập hành trình áp dụng voucher.';
        //     showToastWarning(text_warning);
        //     $("#condition_value_journey").focus();
        //     return false;
        // }

        return true;
    });
});