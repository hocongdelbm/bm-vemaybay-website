<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="modules/EC_Flight_Bookings/css/bookerips.css?v=1.0.6">

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
                <div class="bip-toolbar">
                    <div class="bip-toolbar-copy">
                        <div class="uat-card-title">Danh sách IP đã khai báo</div>
                        <div class="uat-card-des">IP đang trong trạng thái được theo dõi qua các domain</div>
                    </div>
                    <div class="bip-toolbar-actions">
                        <label class="bip-filter-toggle">
                            <input type="checkbox" id="bip-active-only" checked>
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

                <div class="bip-domain-panel">
                    <div class="bip-domain-panel-head">
                        <div>
                            <div class="bip-domain-panel-title">Domain áp dụng</div>
                            <div class="bip-domain-panel-subtitle">Tất cả IP khai báo sẽ tự động đồng bộ trên các domain này</div>
                        </div>
                        <div class="bip-domain-panel-meta" id="bip-domain-count">Đang tải…</div>
                    </div>
                    <div class="bip-domain-panel-list" id="bip-domain-list">
                        <span class="badge badge-muted">Đang tải…</span>
                    </div>
                </div>

                <div class="bip-table-wrapper">
                    <table class="uat-table" id="bip-table">
                        <thead>
                            <tr>
                                <th class="bip-col-index">#</th>
                                <th class="bip-col-ip">IP</th>
                                <th class="bip-col-note">Ghi chú</th>
                                <th class="bip-col-date">Khai báo lúc</th>
                                <th class="bip-col-status">Trạng thái</th>
                                <th class="bip-col-actions">Hành động</th>
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

<script src="modules/EC_Flight_Bookings/js/bookerips.js?v=1.0.5"></script>
