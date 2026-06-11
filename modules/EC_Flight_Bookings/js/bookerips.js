(function () {
    'use strict';

    const API_PROXY = 'index.php?entryPoint=entryPointBookerIps';
    const AUTO_HIDE_MS = 30000;

    let resultTimer = null;

    function esc(str) {
        return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function fmtDate(str) {
        if (!str) return '—';
        return str.split(' ')[0];
    }

    function showResult(cls, html) {
        const result = document.getElementById('bip-result');
        clearTimeout(resultTimer);
        result.className = 'uat-booker-result ' + cls;
        result.innerHTML = html;
        result.style.display = 'block';
        resultTimer = setTimeout(() => {
            result.style.transition = 'opacity 0.4s';
            result.style.opacity = '0';
            setTimeout(() => {
                result.style.display = 'none';
                result.style.opacity = '1';
                result.style.transition = '';
            }, 400);
        }, AUTO_HIDE_MS);
    }

    function renderDomainPanel(domains) {
        const list = document.getElementById('bip-domain-list');
        const count = document.getElementById('bip-domain-count');
        const uniqueDomains = Array.from(new Set(
            (Array.isArray(domains) ? domains : []).filter(Boolean)
        )).sort((a, b) => a.localeCompare(b));

        if (!uniqueDomains.length) {
            count.textContent = '0 domain áp dụng';
            list.innerHTML = '<span class="badge badge-muted">Chưa cấu hình domain áp dụng</span>';
            return;
        }

        count.textContent = `${uniqueDomains.length} domain áp dụng`;
        list.innerHTML = uniqueDomains.map(d => `<span class="badge badge-blue">${esc(d)}</span>`).join('');
    }

    function loadList() {
        const activeOnly = document.getElementById('bip-active-only').checked;
        const tbody = document.getElementById('bip-tbody');

        tbody.innerHTML = '<tr><td colspan="6" class="uat-empty-cell">Đang tải…</td></tr>';

        fetch(API_PROXY + (activeOnly ? '&active_only=1' : '&active_only=0'))
            .then(r => r.json())
            .then(json => {
                const rows = json.data || [];
                renderDomainPanel(json.domains || []);

                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="6" class="uat-empty-cell">Không có IP nào.</td></tr>';
                    return;
                }
                tbody.innerHTML = rows.map((r, i) => {
                    let badge = '';
                    if (r.is_deleted) {
                        badge = '<span class="badge badge-red">Đã xoá</span>';
                    } else if (r.is_active) {
                        badge = '<span class="badge badge-green">Hiệu lực</span>';
                    } else {
                        badge = '<span class="badge badge-muted">Hết hạn</span>';
                    }
                    const delBtn = r.is_deleted ? '' : `<button class="uat-btn-delete bip-del-btn" data-ip="${esc(r.ip)}">Xoá</button>`;
                    const editBtn = r.is_deleted ? '' : `<button class="uat-btn-ghost bip-edit-btn" data-ip="${esc(r.ip)}" data-note="${esc(r.note)}">Sửa</button>`;
                    return `<tr>
                        <td class="bip-stt-cell" data-label="#">${i + 1}</td>
                        <td class="bip-ip-cell" data-label="IP">${esc(r.ip)}</td>
                        <td class="bip-note-cell" data-label="Ghi chú" title="${esc(r.note)}">${esc(r.note) || '<em class="bip-empty-text">—</em>'}</td>
                        <td class="bip-date-cell" data-label="Khai báo">${esc(fmtDate(r.created_at))}</td>
                        <td class="bip-status-cell" data-label="Trạng thái">${badge}</td>
                        <td class="bip-action-cell">
                            <div class="bip-action-group">
                                ${editBtn}${delBtn}
                            </div>
                        </td>
                    </tr>`;
                }).join('');

                tbody.querySelectorAll('.bip-edit-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const ip = this.dataset.ip;
                        document.getElementById('bip-ips').value = ip;
                        document.getElementById('bip-note').value = this.dataset.note;
                        document.getElementById('bip-note').focus();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        showResult('partial', `Đã chọn <strong>${esc(ip)}</strong> để chỉnh sửa — cập nhật Ghi chú rồi nhấn Khai báo IP.`);
                    });
                });

                tbody.querySelectorAll('.bip-del-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const ip = this.dataset.ip;
                        if (!confirm(`Bạn muốn xoá IP ${ip} khỏi tất cả các domain đã đồng bộ?`)) return;
                        deleteEntry(ip, this);
                    });
                });
            })
            .catch(err => {
                document.getElementById('bip-domain-count').textContent = 'Lỗi tải';
                document.getElementById('bip-domain-list').innerHTML = '<span class="badge badge-red">Không tải được domain</span>';
                tbody.innerHTML = `<tr><td colspan="6" class="uat-empty-cell" style="color:var(--uat-danger)">Lỗi tải dữ liệu: ${esc(err.message)}</td></tr>`;
            });
    }

    function deleteEntry(ip, btn) {
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Đang xoá…';

        const formData = new FormData();
        formData.append('ip', ip);

        fetch(API_PROXY + '&action=delete', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(json => {
                if (json.deleted) {
                    showResult('success', `Đã xoá IP <strong>${esc(ip)}</strong> khỏi tất cả domain thành công.`);
                    loadList();
                } else {
                    btn.disabled = false;
                    btn.textContent = originalText;
                    showResult('error', `Xoá thất bại IP <strong>${esc(ip)}</strong>: ${esc(json.error || 'Lỗi không xác định')}`);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = originalText;
                showResult('error', `Lỗi kết nối khi xoá IP <strong>${esc(ip)}</strong>: ${esc(err.message)}`);
            });
    }

    document.getElementById('bip-submit').addEventListener('click', function () {
        const rawIps = document.getElementById('bip-ips').value.trim();
        const note = document.getElementById('bip-note').value.trim();
        const btnText = this.querySelector('.btn-text');
        const btnLoad = this.querySelector('.btn-loader');

        if (!rawIps) {
            showResult('error', 'Vui lòng nhập ít nhất một IP.');
            return;
        }

        const ips = rawIps.split(/[\n,]+/).map(s => s.trim()).filter(Boolean);

        this.disabled = true;
        btnText.style.display = 'none';
        btnLoad.style.display = 'inline';

        const body = { ips: ips };
        if (note) body.note = note;

        fetch(API_PROXY, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        })
            .then(r => r.json())
            .then(json => {
                this.disabled = false;
                btnText.style.display = 'inline';
                btnLoad.style.display = 'none';

                const ins = json.inserted || [];
                const inv = json.invalid || [];
                const skipped = json.skipped || [];

                let cls = 'success';
                let lines = [];

                if (ins.length) lines.push(`Đã khai báo (${ins.length}): ${ins.join(', ')}`);
                if (inv.length) { lines.push(`IP không hợp lệ (${inv.length}): ${inv.join(', ')}`); cls = 'partial'; }
                if (skipped.length) { lines.push(`Bỏ qua/Lỗi DB (${skipped.length}): ${skipped.map(s => s.ip || s).join(', ')}`); cls = 'partial'; }
                if (!ins.length && !inv.length && !skipped.length) {
                    lines.push('Không có thay đổi.');
                    cls = 'partial';
                }
                if (!ins.length && (inv.length || skipped.length)) cls = 'error';

                showResult(cls, lines.map(l => esc(l)).join('<br>'));

                if (ins.length) {
                    document.getElementById('bip-ips').value = '';
                    document.getElementById('bip-note').value = '';
                    loadList();
                }
            })
            .catch(err => {
                this.disabled = false;
                btnText.style.display = 'inline';
                btnLoad.style.display = 'none';
                showResult('error', `Lỗi kết nối proxy: ${esc(err.message)}`);
            });
    });

    document.getElementById('bip-refresh').addEventListener('click', loadList);
    document.getElementById('bip-active-only').addEventListener('change', loadList);

    loadList();
})();
