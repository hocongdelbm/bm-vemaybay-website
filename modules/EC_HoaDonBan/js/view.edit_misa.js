$(document).ready(function () {
    var dec_seperator = $('#dec_seperator').val();
    var grp_seperator = $('#grp_seperator').val();
    var sig_digits = $('#sig_digits').val();

    $('.number-only').number(true, sig_digits, dec_seperator, grp_seperator);

    const $tbody = $('#tbodyLineItems');
    if (!$tbody.length) return;

    if ($tbody.find('.misa-table__row').length > 0) {
        recalcAll();
    }

    // ============================================================
    // Input số lượng / đơn giá → tính lại
    // ============================================================
    $tbody.on('input', '.js-qty, .js-price', function () {
        const $row = $(this).closest('.misa-table__row');
        calcRow($row);
        recalcAll();
    });

    // ============================================================
    // Thay đổi VAT rate → tính lại
    // ============================================================
    $tbody.on('change', '.js-vat-rate', function () {
        const $row = $(this).closest('.misa-table__row');
        calcRow($row);
        recalcAll();
    });

    // ============================================================
    // Xóa dòng
    // ============================================================
    $tbody.on('click', '.js-delete-row', function () {
        const $row = $(this).closest('.misa-table__row');
        if ($tbody.find('.misa-table__row').length <= 1) {
            alert('Chứng từ phải có ít nhất 1 dòng hàng hóa.');
            return;
        }
        if (confirm('Bạn có chắc muốn xóa dòng này?')) {
            $row.remove();
            updateRowCount();
            recalcAll();
        }
    });

    // ============================================================
    // Nút Thêm dòng
    // ============================================================
    $('#btnAddRowMisa').on('click', function () {
        const rows = $tbody.find('.misa-table__row').length;
        const $newRow = createNewRow(rows + 1);
        $tbody.append($newRow);
        updateRowCount();
        $newRow.find('.js-ma-hang').focus();

        $('.number-only').number(true, sig_digits, dec_seperator, grp_seperator);
    });
});


/* ── Tính toán 1 dòng ────────────────────────────────── */
function calcRow($row) {
    console.warn($row.find('.js-qty').val());

    const qty = unformatNumber($row.find('.js-qty').val() || 0);
    const price = unformatNumber($row.find('.js-price').val() || 0);
    const subtotal = qty * price;
    const vatRate = parseFloat($row.find('.js-vat-rate').val()) || 0;
    const vatAmt = vatRate < 0 ? 0 : (subtotal * vatRate);

    $row.find('.js-subtotal').val(formatNumber(subtotal));
    $row.find('.js-vat-amount').val(vatAmt ? formatNumber(vatAmt) : '');

    return { subtotal, vatAmt };
}

/* ── Tính lại toàn bộ tổng ───────────────────────────── */
function recalcAll() {
    let totalQty = 0, totalHang = 0, totalThue = 0;

    $('#tbodyLineItems .misa-table__row').each(function () {
        const $row = $(this);

        const qty = unformatNumber($row.find('.js-qty').val() || 0);
        console.warn('qty', qty);

        const { subtotal, vatAmt } = calcRow($row);

        totalQty += qty;
        totalHang += subtotal;
        totalThue += vatAmt;
    });

    $('#totalQty').text(formatNumber(totalQty));
    $('#totalTienHang').text(formatNumber(totalHang));
    $('#totalTienThue').text(formatNumber(totalThue));
}

/* ── Cập nhật số dòng + reindex name ─────────────────── */
function updateRowCount() {
    const $rows = $('#tbodyLineItems .misa-table__row');
    const count = $rows.length;

    $('#lbl_row_count_misa').text(count);
    $('#row_count_misa').val(count);

    $rows.each(function (i) {
        const idx = i + 1;
        const $row = $(this);

        $row.data('row', idx);
        $row.find('.col-stt').text(idx);

        // Cập nhật name index
        $row.find('[name]').each(function () {
            this.name = this.name.replace(/\[\d+\]/, '[' + idx + ']');
        });
    });
}

function createNewRow(stt) {
    const mahang_list = document.getElementById('invoice_mahang_list').innerHTML;
    const vat_list = document.getElementById('invoice_percent_vat_list').innerHTML;
    const credit_acc_list = document.getElementById('credit_account_list').innerHTML;
    const debit_acc_list = document.getElementById('debit_account_list').innerHTML;

    const $tr = $('<tr>', {
        class: 'misa-table__row',
        'data-row': stt,
    });

    $tr.html(`
        <td class="col-ma-hang">
            <select name="ma_hang[${stt}]" class="js-ma-hang">${mahang_list}</select>
            <input type="hidden" name="ct_linemisa_id[${stt}]" value="" />
        </td>
        <td class="col-ten-hang">
        <div class="d-flex align-items-center gap-1">
                <input type="text" name="ten_hang[${stt}]" class="ac_ticket_number misa-cell-input" />
				<input type="hidden" name="ct_ticket_number_id[${stt}]" id="ct_ticket_number_id${stt}" value="" />
				<button title="Tìm" class="button-pick" type="button" onclick="openTicketNumberPopup(${stt})">
					<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#333"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
				</button>
			</div>
        </td>
        <td class="col-tk-cn">
            <select name="tk_cong_no[${stt}]">${debit_acc_list}</select>
        </td>
        <td class="col-tk-dt">
            <select name="tk_doanh_thu[${stt}]">${credit_acc_list}</select>
        </td>
        <td class="col-dvt">
            <input type="text" name="dvt[${stt}]" class="misa-cell-input text-center" />
        </td>
        <td class="col-soluong">
            <input type="text" name="so_luong[${stt}]" class="misa-cell-input text-end js-qty number-only" value="1" />
        </td>
        <td class="col-dongia">
            <input type="text" name="don_gia[${stt}]" class="misa-cell-input text-end js-price number-only" value="0" />
        </td>
        <td class="col-thanhtien">
            <input type="text" name="thanh_tien[${stt}]" class="misa-cell-input text-end js-subtotal number-only" value="0" readonly />
        </td>
        <td class="col-ptvat">
            <select name="ptram_vat[${stt}]" class="misa-select js-vat-rate">${vat_list}</select>
        </td>
        <td class="col-tienvat">
            <input type="text" name="tien_thue[${stt}]" class="misa-cell-input text-end js-vat-amount" readonly />
        </td>
        <td class="text-center col-action">
            <button type="button" class="misa-cell-delete js-delete-row" title="Xóa dòng">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#333">
                    <path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"/>
                    <path d="M9 10h2v8H9zm4 0h2v8h-2z"/>
                </svg>
            </button>
        </td>
    `);

    return $tr;
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
