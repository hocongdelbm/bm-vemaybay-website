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

    function toggleVoucherTypeFields() {
        let voucherType = $('input[name="type"]:checked').val();

        if (voucherType == 'group') {
            $('.row-voucher-code').show();
            $('.row-website').show();
            $('.row-quantity').show();
            $('.row-phone-list').hide();
            $('.condition-for-phone').show();
            $('#voucher_qty').prop('required', true);
            $('#voucher_phones').prop('required', false);
        }
        else if (voucherType == 'single') {
            $('.row-voucher-code').hide();
            $('.row-website').hide();
            $('.row-quantity').hide();
            $('.row-phone-list').show();
            $('.condition-for-phone').hide();
            $('#voucher_qty').prop('required', false);
            $('#voucher_phones').prop('required', true);
        }
    }

    $('input[type=radio][name=type]').change(toggleVoucherTypeFields);
    toggleVoucherTypeFields();


    $(document).on("keyup", "#voucher_code, #condition_value_journey", function () {
        this.value = this.value.toLocaleUpperCase();
    });

    $(document).on("input", ".numbersOnly", function () {
        $(this).val(Number($(this).val().replace(/\D/g, '')).toLocaleString());
    });

    let voucherPhonePage = 1;
    let voucherPhonePageSize = 10;

    function parseVoucherPhones(phoneText) {
        let phones = (phoneText || '')
            .split(/[\s,;]+/)
            .map(function (phone) {
                phone = phone.replace(/\D/g, '');
                if (phone.indexOf('0084') === 0) {
                    phone = '0' + phone.substring(4);
                } else if (phone.indexOf('84') === 0 && phone.length >= 11) {
                    phone = '0' + phone.substring(2);
                } else if (phone.length === 9) {
                    phone = '0' + phone;
                }
                return phone;
            })
            .filter(function (phone) {
                return phone.length >= 10 && phone.length <= 11;
            });

        return [...new Set(phones)];
    }

    function getVoucherPhones() {
        return parseVoucherPhones($("#voucher_phones").val() || '');
    }

    function mergeVoucherPhones() {
        let merged = [];

        Array.prototype.slice.call(arguments).forEach(function (phoneList) {
            merged = merged.concat(phoneList || []);
        });

        return [...new Set(merged)];
    }

    function setVoucherPhones(phones) {
        let uniquePhones = [...new Set(phones || [])];
        $("#voucher_phones").val(uniquePhones.join("\n")).trigger('change');
        $("#voucher_phone_manual").val(uniquePhones.join("\n"));
        renderVoucherPhonePreview(uniquePhones);
    }

    function updateVoucherPhonesFromManual() {
        voucherPhonePage = 1;
        setVoucherPhones(parseVoucherPhones($("#voucher_phone_manual").val()));
    }

    function showVoucherPhoneMessage(message) {
        if (typeof showToastNotify === 'function') {
            showToastNotify('success', message);
        } else {
            showToastWarning(message);
        }
    }

    function applyVoucherPhonesFromManual() {
        let currentPhones = getVoucherPhones();
        let nextPhones = parseVoucherPhones($("#voucher_phone_manual").val());
        let addedCount = nextPhones.filter(function (phone) {
            return currentPhones.indexOf(phone) === -1;
        }).length;
        let mergedPhones = mergeVoucherPhones(currentPhones, nextPhones);

        voucherPhonePage = 1;
        setVoucherPhones(mergedPhones);

        let message = addedCount > 0
            ? 'Đã bổ sung ' + addedCount + ' số điện thoại'
            : 'Không có số điện thoại mới để bổ sung';

        $("#voucher_phone_import_status").text(message);
        showVoucherPhoneMessage(message);
    }

    function updateVoucherPhoneCounters() {
        let manualPhones = parseVoucherPhones($("#voucher_phone_manual").val());
        let previewCount = getVoucherPhones().length || manualPhones.length;

        $("#voucher_phone_manual_label").text('Xem trước danh sách (' + previewCount + ')');
        $("#btn-update-voucher-phones").text('+' + manualPhones.length);
    }

    function renderVoucherPhonePreview(phones) {
        phones = phones || getVoucherPhones();

        let total = phones.length;
        let totalPages = Math.max(1, Math.ceil(total / voucherPhonePageSize));
        voucherPhonePage = Math.min(Math.max(1, voucherPhonePage), totalPages);

        let start = (voucherPhonePage - 1) * voucherPhonePageSize;
        let pagePhones = phones.slice(start, start + voucherPhonePageSize);
        let html = '';

        if (pagePhones.length === 0) {
            html = '<tr><td colspan="3" class="voucher-phone-empty">Chưa có số điện thoại</td></tr>';
        } else {
            pagePhones.forEach(function (phone, index) {
                let realIndex = start + index;
                html += '<tr class="voucher-phone-row">'
                    + '<td>' + (realIndex + 1) + '</td>'
                    + '<td>' + phone + '</td>'
                    + '<td><button type="button" class="voucher-phone-delete" data-index="' + realIndex + '" title="Xóa số điện thoại">&times;</button></td>'
                    + '</tr>';
            });
        }

        $("#voucher_phone_preview").html(html);
        $("#voucher_phone_preview_note").text(total > voucherPhonePageSize
            ? 'Chỉ hiển thị ' + voucherPhonePageSize + ' bản ghi mỗi trang'
            : 'Chỉ hiển thị 10 bản ghi đầu tiên'
        );
        $("#voucher_phone_prev").prop('disabled', voucherPhonePage <= 1);
        $("#voucher_phone_next").prop('disabled', voucherPhonePage >= totalPages);
        updateVoucherPhoneCounters();
    }

    function clearVoucherPhones() {
        if (getVoucherPhones().length > 0 && !window.confirm('Bạn có chắc muốn hủy toàn bộ danh sách số điện thoại?')) {
            return;
        }

        voucherPhonePage = 1;
        $("#voucher_phone_file").val('');
        $("#voucher_phone_file_name").text('').removeClass('has-file');
        $("#voucher_phone_import_status").text('');
        setVoucherPhones([]);
    }

    function deleteVoucherPhone(index) {
        let phones = getVoucherPhones();
        let phone = phones[index];

        if (!phone || !window.confirm('Bạn có chắc muốn xóa số điện thoại ' + phone + '?')) {
            return;
        }

        phones.splice(index, 1);
        setVoucherPhones(phones);
    }

    function setVoucherPhoneFile(file) {
        let fileInput = $("#voucher_phone_file")[0];
        let dataTransfer = new DataTransfer();

        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        $("#voucher_phone_file_name").text(file.name).addClass('has-file');
    }

    function downloadVoucherPhoneSample() {
        let csvContent = 'phone\n0912345678\n0987654321\n';
        let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        let url = URL.createObjectURL(blob);
        let link = document.createElement('a');

        link.href = url;
        link.download = 'voucher_phone_sample.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function importVoucherPhones() {
        let fileInput = $("#voucher_phone_file")[0];
        let status = $("#voucher_phone_import_status");

        if (!fileInput.files || fileInput.files.length === 0) {
            showToastWarning('Vui lòng chọn file Excel chứa số điện thoại.');
            return;
        }

        let formData = new FormData();
        formData.append('voucher_phone_file', fileInput.files[0]);

        status.text('Đang import...');

        $.ajax({
            url: 'index.php?module=EC_Vouchers&action=importvoucherphones&sugar_body_only=1',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (!response || !response.success) {
                    let message = response && response.message ? response.message : 'Không import được file.';
                    showToastWarning(message);
                    status.text('');
                    return;
                }

                let existingPhones = mergeVoucherPhones(
                    getVoucherPhones(),
                    parseVoucherPhones($("#voucher_phone_manual").val())
                );
                let importedPhones = response.phones || [];
                let addedCount = importedPhones.filter(function (phone) {
                    return existingPhones.indexOf(phone) === -1;
                }).length;
                let mergedPhones = mergeVoucherPhones(existingPhones, importedPhones);

                voucherPhonePage = 1;
                setVoucherPhones(mergedPhones);
                status.text('Đã import ' + importedPhones.length + ' SĐT, bổ sung ' + addedCount + ' số mới');
                $("#voucher_phone_file_name").text(fileInput.files[0].name).addClass('has-file');
            },
            error: function () {
                showToastWarning('Không import được file. Vui lòng kiểm tra lại định dạng.');
                status.text('');
            }
        });
    }

    $("#btn-update-voucher-phones").on('click', applyVoucherPhonesFromManual);
    $("#btn-import-voucher-phones").on('click', applyVoucherPhonesFromManual);
    $("#btn-download-voucher-phone-sample").on('click', downloadVoucherPhoneSample);
    $("#voucher_phone_file").on('change', importVoucherPhones);
    $("#btn-clear-voucher-phones").on('click', clearVoucherPhones);
    $("#btn-cancel-voucher-phones").on('click', clearVoucherPhones);
    $("#voucher_phone_manual").on('input', updateVoucherPhoneCounters);
    $("#voucher_phone_prev").on('click', function () {
        voucherPhonePage--;
        renderVoucherPhonePreview();
    });
    $("#voucher_phone_next").on('click', function () {
        voucherPhonePage++;
        renderVoucherPhonePreview();
    });
    $(document).on('click', '.voucher-phone-delete', function () {
        deleteVoucherPhone(parseInt($(this).data('index'), 10));
    });
    $(".voucher-phone-dropzone")
        .on('dragover', function (event) {
            event.preventDefault();
            $(this).addClass('is-dragover');
        })
        .on('dragleave drop', function (event) {
            event.preventDefault();
            $(this).removeClass('is-dragover');
        })
        .on('drop', function (event) {
            let files = event.originalEvent.dataTransfer.files;
            if (files && files.length > 0) {
                setVoucherPhoneFile(files[0]);
                importVoucherPhones();
            }
        });
    renderVoucherPhonePreview();

    $("#form_create_voucher").submit(function () {
        let voucher_type = $('input[name="type"]:checked').val();
        // let voucher_code = $("#voucher_code").val();
        let quantity = parseInt($("#voucher_qty").val()) || 0;

        if (voucher_type == 'single') {
            let manualPhones = parseVoucherPhones($("#voucher_phone_manual").val());
            let mergedPhones = mergeVoucherPhones(getVoucherPhones(), manualPhones);
            if (mergedPhones.join("\n") !== $("#voucher_phones").val()) {
                setVoucherPhones(mergedPhones);
            }
        }
       
        if (voucher_type == 'group' && quantity <= 0) {
            let text_warning = 'Vui lòng bổ sung số lượng voucher phát hành!';
            showToastWarning(text_warning);
            $("#voucher_qty").focus();
            return false;
        }

        if (voucher_type == 'single' && getVoucherPhones().length <= 0) {
            let text_warning = 'Vui lòng nhập danh sách số điện thoại phát hành voucher!';
            showToastWarning(text_warning);
            $("#voucher_phone_manual").focus();
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
