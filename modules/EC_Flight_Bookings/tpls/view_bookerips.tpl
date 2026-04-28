<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="modules/EC_Flight_Bookings/css/bookerips.css?v=1.0.0">

<div class="uat-wrap">
    <div class="uat-header" style="margin-bottom: 24px;">
        <div class="uat-header-info">
            <h1>Booker IP Whitelist</h1>
            <p class="uat-header-desc">Khai báo IP được bỏ qua toàn bộ cảnh báo đáng ngờ vô thời hạn (trừ khi bị xoá).
                Proxy xử lý multi-domain.</p>
        </div>
    </div>

    <div class="uat-grid-row">
        <!-- ===== FORM KHAI BÁO ===== -->
        <div class="uat-col-4">
            <div class="uat-card" style="border-top: 4px solid var(--uat-primary);">
                <div class="uat-card-title">Khai báo IP mới</div>
                <div class="uat-card-des">IP được bypass cảnh báo đáng ngờ</div>
                <div class="uat-card-divider"></div>

                <div class="uat-booker-field">
                    <label for="bip-ips">Danh sách IP <span class="required">*</span></label>
                    <textarea id="bip-ips" rows="4"
                        placeholder="Mỗi IP một dòng, hoặc phân cách bằng dấu phẩy&#10;Ví dụ: 1.2.3.4&#10;5.6.7.8"></textarea>
                    <p class="field-hint">Hỗ trợ IPv4 và IPv6. Nhiều IP trên cùng một lần khai báo.</p>
                </div>

                <div class="uat-booker-field">
                    <label for="bip-note">Ghi chú (Note)</label>
                    <input type="text" id="bip-note" placeholder="Ví dụ: Booker văn phòng HN, Tour HCM…">
                </div>



                <button id="bip-submit" class="uat-btn-form">
                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span class="btn-text">Khai báo IP</span>
                    <span class="btn-loader" style="display:none">Đang xử lý…</span>
                </button>

                <div id="bip-result" class="uat-booker-result" style="display:none"></div>
            </div>
        </div>

        <!-- ===== DANH SÁCH IP ===== -->
        <div class="uat-col-8">
            <div class="uat-card">
                <div
                    style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:10px;">
                    <div>
                        <div class="uat-card-title">Danh sách IP đã khai báo</div>
                        <div class="uat-card-des">IP đang trong trạng thái được theo dõi qua các domain</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <label
                            style="display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:var(--uat-text-muted); cursor:pointer;">
                            <input type="checkbox" id="bip-active-only" checked
                                style="accent-color:var(--uat-primary);">
                            Chỉ hiện còn hiệu lực
                        </label>
                        <button id="bip-refresh" class="uat-btn-ghost" title="Làm mới danh sách">
                            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2"
                                fill="none" stroke-linecap="round" stroke-linejoin="round"
                                style="margin-right:4px; vertical-align:middle;">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="uat-card-divider"></div>

                <div class="bip-table-wrapper">
                    <table class="uat-table" id="bip-table">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>IP</th>
                                <th>Đồng bộ trên</th>
                                <th>Ghi chú</th>
                                <th>Khai báo lúc</th>
                                <th style="width:80px">Trạng thái</th>
                                <th style="min-width:130px; text-align:right;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="bip-tbody">
                            <tr>
                                <td colspan="6" class="uat-empty-cell">Đang tải…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="modules/EC_Flight_Bookings/js/bookerips.js?v=1.0.1"></script>