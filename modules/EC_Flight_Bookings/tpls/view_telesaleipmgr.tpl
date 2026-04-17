{literal}
<style>
    #telesale-ipmgr .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--primary-color, #374151);
        border-left: 3px solid var(--primary-color, #374151);
        padding-left: 10px;
        margin-bottom: 12px;
    }
    #telesale-ipmgr .card-loc {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 14px 16px;
        margin-bottom: 12px;
    }
    #telesale-ipmgr .loc-name {
        font-weight: 600;
        margin-bottom: 6px;
        color: #1f2937;
    }
    #telesale-ipmgr .toggle-btn:disabled { opacity: .5; cursor: default; }
    #telesale-ipmgr .save-msg {
        display: inline-block;
        margin-left: 8px;
        font-size: 0.78rem;
        opacity: 0;
        transition: opacity .3s;
    }
    #telesale-ipmgr .save-msg.show { opacity: 1; }
</style>
{/literal}

<h1 class="report_title title">Quản lý IP & Telesale</h1>

<div id="telesale-ipmgr" class="box-section">

    {* ===== SECTION 1: IP CÔNG TY ===== *}
    <div class="mb-4">
        <div class="section-title">Danh sách IP công ty (theo địa điểm)</div>
        <p class="ip-hint text-dark fw-semibold fst-italic mb-3">Lưu ý: Mỗi dòng 1 IP.</p>

        {if $LOCATION_LIST}
            <div class="card-loc-list d-flex gap-3 flex-wrap">
                {foreach from=$LOCATION_LIST item=loc}
                <div class="card-loc flex-fill">
                    <div class="loc-name">{$loc.name|escape}</div>
                    <textarea class="ip-textarea form-control" id="ips_{$loc.id}" data-loc-id="{$loc.id}" rows="4" cols="32">{$loc.allowed_ips|escape}</textarea>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-primary btn-save-ip" onclick="saveLocationIPs('{$loc.id}')">Lưu IP</button>
                        <span class="save-msg fw-semibold text-success" id="msg_{$loc.id}">Đã lưu!</span>
                    </div>
                </div>
                {/foreach}
            </div>
        {/if}
    </div>

    {* ===== SECTION 2: TELESALE USERS ===== *}
    <div>
        <div class="section-title">Danh sách nhân viên Telesale</div>

        {if $TELESALE_USERS}
        <table class="tbl-users table table-hover">
            <thead>
                <tr>
                    <th width="3%">#</th>
                    <th width="15%">Tên đăng nhập</th>
                    <th width="30%">Họ tên</th>
                    <th width="20%">Kiểm tra IP nội bộ</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$TELESALE_USERS item=user name=uloop}
                <tr id="row_{$user.id}">
                    <td>{$smarty.foreach.uloop.iteration}</td>
                    <td class="text-monospace">
                        <a href="index.php?module=Users&action=DetailView&record={$user.id}" target="_blank">
                            {$user.user_name|escape}
                        </a>
                    </td>
                    <td>
                        <a href="index.php?module=Users&action=DetailView&record={$user.id}" target="_blank">
                            {$user.full_name|escape}
                        </a>
                    </td>
                    <td id="status_{$user.id}">
                        {if $user.ip_restriction_enabled}
                            <span class="badge-active badge bg-success">Đang bật</span>
                        {else}
                            <span class="badge-inactive badge bg-danger">Đang tắt</span>
                        {/if}
                    </td>
                    <td>
                        {if $user.ip_restriction_enabled}
                            <button class="toggle-btn btn-disable btn btn-sm btn-danger" data-user-id="{$user.id}" data-target="0" onclick="toggleRestriction(this)">
                                Tắt kiểm tra IP
                            </button>
                        {else}
                            <button class="toggle-btn btn-enable btn btn-sm btn-success" data-user-id="{$user.id}" data-target="1" onclick="toggleRestriction(this)">
                                Bật kiểm tra IP
                            </button>
                        {/if}
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        {else}
            <div class="alert alert-info">Không tìm thấy nhân viên nào có role <strong>Telesale</strong>.</div>
        {/if}
    </div>
</div>

{literal}
<script>
const AJAX_URL = 'index.php?module=EC_Flight_Bookings&action=telesaleipmgr';

function saveLocationIPs(locationId) {
    let ips = document.getElementById('ips_' + locationId).value;
    let msgEl = document.getElementById('msg_' + locationId);

    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        dataType: 'json',
        data: { ajax_action: 'save_location_ips', location_id: locationId, allowed_ips: ips },
        success: function(res) {
            if (res.success) {
                msgEl.textContent = 'Đã lưu!';
                msgEl.style.color = '#059669';
            } else {
                msgEl.textContent = res.message || 'Lỗi!';
                msgEl.style.color = '#dc2626';
            }
            msgEl.classList.add('show');
            setTimeout(function() { msgEl.classList.remove('show'); }, 2500);
        },
        error: function() {
            msgEl.textContent = 'Lỗi kết nối!';
            msgEl.style.color = '#dc2626';
            msgEl.classList.add('show');
            setTimeout(function() { msgEl.classList.remove('show'); }, 2500);
        }
    });
}

function toggleRestriction(btn) {
    let userId = btn.getAttribute('data-user-id');
    let target = parseInt(btn.getAttribute('data-target'));
    btn.disabled = true;

    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        dataType: 'json',
        data: { ajax_action: 'toggle_user_restriction', user_id: userId, enabled: target },
        success: function(res) {
            if (!res.success) {
                btn.disabled = false;
                alert('Lỗi: ' + (res.message || 'Không thể thay đổi trạng thái'));
                return;
            }

            let statusEl = document.getElementById('status_' + userId);
            if (target === 1) {
                statusEl.innerHTML = '<span class="badge-active badge bg-success">Đang bật</span>';
                btn.textContent = 'Tắt kiểm tra IP';
                btn.classList.remove('btn-enable', 'btn-success');
                btn.classList.add('btn-disable', 'btn-danger');
                btn.setAttribute('data-target', '0');
            } else {
                statusEl.innerHTML = '<span class="badge-inactive badge bg-danger">Đang tắt</span>';
                btn.textContent = 'Bật kiểm tra IP';
                btn.classList.remove('btn-disable', 'btn-danger');
                btn.classList.add('btn-enable', 'btn-success');
                btn.setAttribute('data-target', '1');
            }
            btn.disabled = false;

            let row = document.getElementById('row_' + userId);
            row.style.transition = 'background .15s';
            row.style.background = '#d1fae5';
            setTimeout(function() { row.style.background = ''; }, 1200);
        },
        error: function() {
            btn.disabled = false;
            alert('Lỗi kết nối, vui lòng thử lại');
        }
    });
}
</script>
{/literal}
