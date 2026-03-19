(function () {
    'use strict';

    /* ── Helpers ─────────────────────────────────────────── */
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    /** Parse số từ chuỗi định dạng VN (1.000,50 → 1000.50) */
    function parseNum(str) {
        if (!str) return 0;
        return parseFloat(String(str).replace(/\./g, '').replace(',', '.')) || 0;
    }

    /** Format số sang chuỗi VN */
    function fmtNum(n) {
        if (!n) return '0';
        return n.toLocaleString('vi-VN');
    }

    /* ── Tính toán 1 dòng ────────────────────────────────── */
    function calcRow(row) {
        const qty = parseNum($('.js-qty', row)?.value);
        const price = parseNum($('.js-price', row)?.value);
        const subtotal = qty * price;
        const vatRate = parseFloat($('.js-vat-rate', row)?.value) || 0;
        const vatAmt = subtotal * vatRate / 100;

        const subEl = $('.js-subtotal', row);
        const vatEl = $('.js-vat-amount', row);
        if (subEl) subEl.value = fmtNum(subtotal);
        if (vatEl) vatEl.value = vatAmt ? fmtNum(vatAmt) : '';

        return { subtotal, vatAmt };
    }

    /* ── Tính lại toàn bộ tổng ───────────────────────────── */
    function recalcAll() {
        let totalQty = 0, totalHang = 0, totalThue = 0;

        $$('#tbodyLineItems .misa-table__row').forEach(row => {
            const qty = parseNum($('.js-qty', row)?.value);
            totalQty += qty;
            const { subtotal, vatAmt } = calcRow(row);
            totalHang += subtotal;
            totalThue += vatAmt;
        });

        const totalTT = totalHang + totalThue;

        // Footer table
        const el = id => document.getElementById(id);
        if (el('totalQty')) el('totalQty').textContent = fmtNum(totalQty);
        if (el('totalTienHang')) el('totalTienHang').textContent = fmtNum(totalHang);
        if (el('totalTienThue')) el('totalTienThue').textContent = fmtNum(totalThue);

        // Summary box
        if (el('summaryTienHang')) el('summaryTienHang').textContent = fmtNum(totalHang);
        if (el('summaryTienThue')) el('summaryTienThue').textContent = fmtNum(totalThue);
        if (el('summaryTotal')) el('summaryTotal').textContent = fmtNum(totalTT);

        // Header total
        if (el('totalDisplay')) el('totalDisplay').textContent = fmtNum(totalTT);
    }

    /* ── Cập nhật số dòng hiển thị ───────────────────────── */
    function updateRowCount() {
        const rows = $$('#tbodyLineItems .misa-table__row').length;
        const countEl = document.getElementById('lbl_row_count');
        const hiddenEl = document.getElementById('row_count');
        if (countEl) countEl.textContent = rows;
        if (hiddenEl) hiddenEl.value = rows;

        // Cập nhật lại số thứ tự
        $$('#tbodyLineItems .misa-table__row').forEach((row, i) => {
            row.dataset.row = i + 1;
            const stt = row.querySelector('.col-stt');
            if (stt) stt.textContent = i + 1;

            // Cập nhật name index
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, `[${i + 1}]`);
            });
        });
    }

    /* ── Tạo dòng mới ────────────────────────────────────── */
    function createNewRow(stt) {
        const tr = document.createElement('tr');
        tr.className = 'misa-table__row';
        tr.dataset.row = stt;

        tr.innerHTML = `
            <td class="col-ma-hang">
                <input type="text" name="ma_hang[${stt}]" class="misa-cell-input js-ma-hang" autocomplete="off" />
                <input type="hidden" name="line_id[${stt}]" value="" />
            </td>
            <td class="col-ten-hang">
                <input type="text" name="ten_hang[${stt}]" class="misa-cell-input" />
            </td>
            <td class="col-tk-cn">
                <input type="text" name="tk_cong_no[${stt}]" class="misa-cell-input misa-cell-input--center" value="131" />
            </td>
            <td class="col-tk-dt">
                <input type="text" name="tk_doanh_thu[${stt}]" class="misa-cell-input misa-cell-input--center" value="5111" />
            </td>
            <td class="col-dvt">
                <input type="text" name="dvt[${stt}]" class="misa-cell-input misa-cell-input--center" />
            </td>
            <td class="col-soluong">
                <input type="text" name="so_luong[${stt}]" class="misa-cell-input misa-cell-input--right js-qty" value="1,00" />
            </td>
            <td class="col-dongia">
                <input type="text" name="don_gia[${stt}]" class="misa-cell-input misa-cell-input--right js-price" value="0,00" />
            </td>
            <td class="col-thanhtien">
                <input type="text" name="thanh_tien[${stt}]" class="misa-cell-input misa-cell-input--right js-subtotal" value="0" readonly />
            </td>
            <td class="col-ptvat">
                <select name="ptram_vat[${stt}]" class="misa-cell-select js-vat-rate">
                    <option value=""></option>
                    <option value="0">0%</option>
                    <option value="5">5%</option>
                    <option value="8">8%</option>
                    <option value="10">10%</option>
                    <option value="KT">KT</option>
                </select>
            </td>
            <td class="col-tienvat">
                <input type="text" name="tien_thue[${stt}]" class="misa-cell-input misa-cell-input--right js-vat-amount" readonly />
            </td>
            <td class="text-center col-action">
                <button type="button" class="misa-cell-delete js-delete-row" title="Xóa dòng">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#333"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
                </button>
            </td>
        `;

        return tr;
    }

    /* ── Tạo dòng ghi chú ────────────────────────────────── */
    function createNoteRow(stt) {
        const tr = document.createElement('tr');
        tr.className = 'misa-table__row misa-table__row--note';
        tr.dataset.row = stt;
        tr.innerHTML = `
      <td class="col-stt">${stt}</td>
      <td colspan="11">
        <input type="text" name="ghi_chu[${stt}]" class="misa-cell-input" placeholder="Ghi chú..." />
        <input type="hidden" name="is_note[${stt}]" value="1" />
      </td>
      <td class="col-action">
        <button type="button" class="misa-cell-delete js-delete-row" title="Xóa dòng">🗑</button>
      </td>
    `;
        return tr;
    }

    /* ── Event delegation ────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('tbodyLineItems');
        if (!tbody) return;

        // Tính ban đầu
        recalcAll();

        // Input số lượng / đơn giá / VAT → tính lại
        tbody.addEventListener('input', e => {
            const row = e.target.closest('.misa-table__row');
            if (!row) return;
            if (e.target.matches('.js-qty, .js-price')) {
                calcRow(row);
                recalcAll();
            }
        });

        tbody.addEventListener('change', e => {
            const row = e.target.closest('.misa-table__row');
            if (!row) return;
            if (e.target.matches('.js-vat-rate')) {
                calcRow(row);
                recalcAll();
            }
        });

        // Xóa dòng
        tbody.addEventListener('click', e => {
            if (e.target.matches('.js-delete-row, .js-delete-row *')) {
                const row = e.target.closest('.misa-table__row');
                if (tbody.querySelectorAll('.misa-table__row').length <= 1) {
                    alert('Chứng từ phải có ít nhất 1 dòng hàng hóa.');
                    return;
                }
                if (confirm('Bạn có chắc muốn xóa dòng này?')) {
                    row.remove();
                    updateRowCount();
                    recalcAll();
                }
            }
        });

        // Nút Thêm dòng
        document.getElementById('btnAddRowMisa')?.addEventListener('click', () => {
            const rows = tbody.querySelectorAll('.misa-table__row').length;
            const newRow = createNewRow(rows + 1);
            tbody.appendChild(newRow);
            updateRowCount();
            newRow.querySelector('.js-ma-hang')?.focus();
        });

        // Nút Thêm ghi chú
        document.getElementById('btnAddNote')?.addEventListener('click', () => {
            const rows = tbody.querySelectorAll('.misa-table__row').length;
            const noteRow = createNoteRow(rows + 1);
            tbody.appendChild(noteRow);
            updateRowCount();
            noteRow.querySelector('input[type=text]')?.focus();
        });

        // Xóa hết dòng
        document.getElementById('btnClearRows')?.addEventListener('click', () => {
            if (!confirm('Bạn có chắc muốn xóa tất cả dòng?')) return;
            tbody.innerHTML = '';
            // Thêm lại 1 dòng trống
            tbody.appendChild(createNewRow(1));
            updateRowCount();
            recalcAll();
        });

        // Tab line items
        $$('.misa-line-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                $$('.misa-line-tab').forEach(t => t.classList.remove('misa-line-tab--active'));
                tab.classList.add('misa-line-tab--active');
            });
        });

        // Tab header
        $$('.misa-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                $$('.misa-tab').forEach(t => t.classList.remove('misa-tab--active'));
                tab.classList.add('misa-tab--active');
            });
        });

        // Toggle tham chiếu
        document.getElementById('toggleThamChieu')?.addEventListener('click', e => {
            e.preventDefault();
            const panel = document.getElementById('thamChieuPanel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        });

        // Thu tiền ngay → show/hide hình thức TT
        $$('[name="thu_tien"]').forEach(radio => {
            radio.addEventListener('change', () => {
                const hinhThucEl = document.getElementById('hinhThucTT');
                if (hinhThucEl) {
                    hinhThucEl.style.opacity = radio.value === '1' ? '1' : '0.5';
                    hinhThucEl.disabled = radio.value === '0';
                }
            });
        });

        // Đóng form
        document.getElementById('btnCloseMisa')?.addEventListener('click', () => {
            if (confirm('Bạn có muốn đóng chứng từ? Dữ liệu chưa lưu sẽ bị mất.')) {
                document.getElementById('misaBanHangForm')?.remove();
            }
        });
    });
})();