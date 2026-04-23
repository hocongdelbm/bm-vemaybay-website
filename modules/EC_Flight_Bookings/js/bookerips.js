(function () {
    'use strict';

    const API_PROXY = 'index.php?entryPoint=entryPointBookerIps';

    function esc(str) {
        return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function fmtDate(str) {
        if (!str) return '—';
        return str.split(' ')[0];
    }

    function loadList() {
        const activeOnly = document.getElementById('bip-active-only').checked;
        const tbody = document.getElementById('bip-tbody');

        tbody.innerHTML = '<tr><td colspan="7" class="uat-empty-cell">Đang tải…</td></tr>';

        fetch(API_PROXY + (activeOnly ? '&active_only=1' : '&active_only=0'))
            .then(r => r.json())
            .then(json => {
                const rows = json.data || [];
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="7" class="uat-empty-cell">Không có IP nào.</td></tr>';
                    return;
                }
                tbody.innerHTML = rows.map((r, i) => {
                    let badge = '';
                    if (r.is_deleted) {
                        badge = '<span class="badge badge-red">Đã xoá</span>';
                    } else if (r.is_active) {
                        badge = '<span class="badge badge-green">Hiệu lực</span>';
                    } else {
                        badge = '<span class="badge" style="background:#f1f5f9;color:#64748b">Hết hạn</span>';
                    }
                    const delBtn = r.is_deleted ? '' : `<button class="uat-btn-delete bip-del-btn" data-ip="${esc(r.ip)}">Xoá</button>`;
                    const editBtn = r.is_deleted ? '' : `<button class="uat-btn-ghost bip-edit-btn" data-ip="${esc(r.ip)}" data-note="${esc(r.note)}" style="margin-right:6px">Sửa</button>`;

                    const domains = (r.domains || []).map(d => `<span class="badge badge-blue" style="margin-right:4px;">${d}</span>`).join('');
                    return `<tr>
                        <td style="color:var(--uat-text-muted); font-weight:600;">${i + 1}</td>
                        <td class="bip-ip-cell">${esc(r.ip)}</td>
                        <td>${domains}</td>
                        <td class="bip-note-cell" title="${esc(r.note)}">${esc(r.note) || '<em style="color:var(--uat-border)">—</em>'}</td>
                        <td>${esc(fmtDate(r.created_at))}</td>
                        <td>${badge}</td>
                        <td>
                            <div style="display:flex; gap:8px; justify-content:flex-end; align-items:center;">
                                ${editBtn}${delBtn}
                            </div>
                        </td>
                    </tr>`;
                }).join('');

                tbody.querySelectorAll('.bip-edit-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        document.getElementById('bip-ips').value = this.dataset.ip;
                        document.getElementById('bip-note').value = this.dataset.note;
                        document.getElementById('bip-note').focus();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
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
                tbody.innerHTML = `<tr><td colspan="7" class="uat-empty-cell" style="color:var(--uat-danger)">Lỗi tải dữ liệu: ${esc(err.message)}</td></tr>`;
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
                    loadList();
                } else {
                    btn.disabled = false;
                    btn.textContent = originalText;
                    alert('Xoá thất bại: ' + (json.error || 'Lỗi không xác định'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = originalText;
                alert('Lỗi: ' + err.message);
            });
    }

    document.getElementById('bip-submit').addEventListener('click', function () {
        const rawIps = document.getElementById('bip-ips').value.trim();
        const note = document.getElementById('bip-note').value.trim();
        const result = document.getElementById('bip-result');
        const btnText = this.querySelector('.btn-text');
        const btnLoad = this.querySelector('.btn-loader');

        if (!rawIps) {
            result.className = 'uat-booker-result error';
            result.textContent = 'Vui lòng nhập ít nhất một IP.';
            result.style.display = 'block';
            return;
        }

        const ips = rawIps.split(/[\n,]+/).map(s => s.trim()).filter(Boolean);

        this.disabled = true;
        btnText.style.display = 'none';
        btnLoad.style.display = 'inline';
        result.style.display = 'none';

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

                result.className = 'uat-booker-result ' + cls;
                result.innerHTML = lines.map(l => esc(l)).join('<br>');
                result.style.display = 'block';

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
                result.className = 'uat-booker-result error';
                result.textContent = 'Lỗi kết nối proxy: ' + err.message;
                result.style.display = 'block';
            });
    });

    document.getElementById('bip-refresh').addEventListener('click', loadList);
    document.getElementById('bip-active-only').addEventListener('change', loadList);

    loadList();
})();
