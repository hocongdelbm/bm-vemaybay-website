/**
 * disablemodulerole.js
 * JS cho chức năng Vô hiệu hóa theo Module
 */

// ── Chọn tất cả / bỏ chọn tất cả ────────────────────────────────────────────
function dmrToggleAll(type, checked) {
    var listId = type === 'role' ? 'dmr-role-list' : 'dmr-module-list';
    var list   = document.getElementById(listId);
    if (!list) return;

    // Chỉ toggle các item đang hiển thị (chưa bị filter ẩn)
    var checkboxes = list.querySelectorAll('.dmr-item:not(.dmr-item--hidden) input[type="checkbox"]');
    checkboxes.forEach(function(cb) {
        cb.checked = checked;
    });

    dmrUpdateCount(type);
}

// ── Cập nhật badge đếm ────────────────────────────────────────────────────────
function dmrUpdateCount(type) {
    var listId   = type === 'role' ? 'dmr-role-list'    : 'dmr-module-list';
    var badgeId  = type === 'role' ? 'dmr-role-count'   : 'dmr-module-count';
    var allCbId  = type === 'role' ? 'dmr-role-all'     : 'dmr-module-all';

    var list  = document.getElementById(listId);
    var badge = document.getElementById(badgeId);
    var allCb = document.getElementById(allCbId);
    if (!list || !badge) return;

    var total   = list.querySelectorAll('.dmr-item:not(.dmr-item--hidden) input[type="checkbox"]').length;
    var checked = list.querySelectorAll('.dmr-item:not(.dmr-item--hidden) input[type="checkbox"]:checked').length;

    badge.textContent = checked > 0 ? checked : '0';

    // Sync trạng thái "Chọn tất cả"
    if (allCb) {
        allCb.checked       = total > 0 && checked === total;
        allCb.indeterminate = checked > 0 && checked < total;
    }

    dmrUpdateSummary();
    dmrUpdateNextBtn();
}

// ── Cập nhật summary bar ──────────────────────────────────────────────────────
function dmrUpdateSummary() {
    var roleCount   = parseInt(document.getElementById('dmr-role-count')?.textContent   || '0', 10);
    var moduleCount = parseInt(document.getElementById('dmr-module-count')?.textContent || '0', 10);
    var textEl      = document.getElementById('dmr-summary-text');
    if (!textEl) return;

    if (roleCount === 0 && moduleCount === 0) {
        textEl.textContent = 'Chưa chọn gì';
    } else if (roleCount === 0) {
        textEl.textContent = 'Đã chọn ' + moduleCount + ' module — cần chọn thêm ít nhất 1 role';
    } else if (moduleCount === 0) {
        textEl.textContent = 'Đã chọn ' + roleCount + ' role — cần chọn thêm ít nhất 1 module';
    } else {
        textEl.textContent = '✓ ' + roleCount + ' role × ' + moduleCount + ' module = '
            + (roleCount * moduleCount) + ' tổ hợp sẽ bị vô hiệu hóa';
    }
}

// ── Bật / tắt nút Continue ───────────────────────────────────────────────────
function dmrUpdateNextBtn() {
    var btn         = document.getElementById('dmr-next-btn');
    if (!btn) return;
    var roleCount   = parseInt(document.getElementById('dmr-role-count')?.textContent   || '0', 10);
    var moduleCount = parseInt(document.getElementById('dmr-module-count')?.textContent || '0', 10);
    btn.disabled = !(roleCount > 0 && moduleCount > 0);
}

// ── Filter / search ───────────────────────────────────────────────────────────
function dmrFilter(type, query) {
    var listId = type === 'role' ? 'dmr-role-list' : 'dmr-module-list';
    var list   = document.getElementById(listId);
    if (!list) return;

    var q = query.trim().toLowerCase();
    var items = list.querySelectorAll('.dmr-item');

    items.forEach(function(item) {
        var name = item.getAttribute('data-name') || '';
        if (q === '' || name.indexOf(q) !== -1) {
            item.classList.remove('dmr-item--hidden');
        } else {
            item.classList.add('dmr-item--hidden');
        }
    });

    // Sau filter, cập nhật lại "Chọn tất cả" theo visible items
    dmrUpdateCount(type);
}

// ── Confirm submit (bước 2 → 3) ──────────────────────────────────────────────
function drmConfirmSubmitModule(form) {
    var count = parseInt(form.getAttribute('data-count') || '0', 10);
    if (!confirm(
        'Xác nhận vô hiệu hóa?\n\n' +
        count + ' tổ hợp (role × module) sẽ bị ghi đè.\n\n' +
        'Thao tác này không thể hoàn tác tự động.'
    )) {
        return false;
    }
    var btn = document.getElementById('drm-confirm-btn');
    var txt = document.getElementById('drm-confirm-text');
    if (txt) txt.textContent = 'Đang xử lý…';
    setTimeout(function() { if (btn) btn.disabled = true; }, 0);
    return true;
}

// ── Init khi load trang ───────────────────────────────────────────────────────
(function() {
    // Đảm bảo count & button đúng ngay khi load (trường hợp browser restore form state)
    dmrUpdateCount('role');
    dmrUpdateCount('module');
})();
