<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css" />
<link type="text/css" rel="stylesheet" href="modules/EC_TongHop/css/ec_tonghop.css?v=2.0.4">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/js/jsvectormap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/maps/world.js"></script>

{literal}
    <script>
        var EC_DOMAIN_CONFIG = {/literal}{$DOMAIN_CONFIG_JSON}{literal};
    </script>
{/literal}

<div class="uat-wrap" id="ec-uat-wrap">
    <div class="uat-sticky-header-zone">
        <!-- HEADER -->
        <div class="uat-header">
            <div class="uat-header-info">
                <h1>User Analytics Tracker</h1>
                <p class="uat-header-desc">Theo dõi & phân tích hành vi người dùng đa nền tảng</p>
            </div>
            <div class="uat-header-actions">
                <div class="uat-site-selector">
                    <svg style="width:18px; height:18px; color:#64748b;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9">
                        </path>
                    </svg>
                    <span class="uat-selector-label">Website:</span>
                    <select id="ec_site_select" class="uat-site-select">
                        {foreach from=$SELECT_OPTIONS item=label key=key}
                            <option value="{$key}">{$label}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="uat-date-badge" id="ec_date_badge">
                    <svg style="width:16px; height:16px; color:#64748b;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span style="color:#94a3b8; font-size:12px;">Date:</span>
                    <select id="ec_date_select" class="uat-date-selector">
                        <option value="">Loading...</option>
                    </select>
                    <span id="ec_live_badge" class="uat-live-badge" style="display:none;">Live</span>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <div class="uat-tabs" id="ec_tabs">
            <div class="uat-tab active" data-tab="overview">
                Tổng quan
            </div>
            <div class="uat-tab" data-tab="areas">
                Phân tích khu vực
            </div>
            <div class="uat-tab" data-tab="flights">
                Phân tích chuyến bay
            </div>
            <div class="uat-tab" data-tab="ip_journey">
                IP Journey
            </div>
            <div class="uat-tab" data-tab="elements">
                Tổng hợp hành vi
            </div>
            <div class="uat-tab" data-tab="suspicious">
                IP đáng ngờ <span class="badge badge-red" id="ec_susp_count">0</span>
            </div>
            <div class="uat-tab" data-tab="scraping">
                Nghi vấn quét giá <span class="badge badge-red" id="ec_scraping_count">0</span>
            </div>
            <div class="uat-tab" data-tab="bots">
                Phân tích Bot
            </div>
            <div class="uat-tab" data-tab="heatmap" style="color: #f72585;">
                Heatmap
            </div>
        </div>
    </div> <!-- END STICKY ZONE -->
    <!-- LOADING OVERLAY -->
    <div id="ec_global_loading" class="uat-global-loading" style="display:none;">
        <div class="uat-loading-wrapper">
            <div class="uat-loading-spinner"></div>
            <span>Đang tải dữ liệu...</span>
        </div>
    </div>
    <!-- TAB: OVERVIEW -->
    <div id="uat-tab-overview" class="uat-tab-content active">
        <!-- Lifetime Summary Cards -->
        <h2 class="uat-section-title">Dữ liệu trực tiếp từ website</h2>
        <div class="uat-grid-row" id="ec_lifetime_cards">
            <div class="uat-col-3">
                <div class="uat-card" style="border-top: 4px solid #3b82f6;">
                    <div class="uat-card-title">Total Auth Users</div>
                    <div class="uat-card-des">Người dùng đã được xác thực toàn bộ hệ thống</div>
                    <div class="uat-card-divider"></div>
                    <div class="uat-stat-value" style="color:#3b82f6" id="ec_total_users">—</div>
                </div>
            </div>
            <div class="uat-col-3">
                <div class="uat-card" style="border-top: 4px solid #10b981;">
                    <div class="uat-card-title">Realtime Sessions</div>
                    <div class="uat-card-des">Sessions đang hoạt động online trong ngày hôm nay</div>
                    <div class="uat-card-divider"></div>
                    <div class="uat-stat-value" style="color:#10b981" id="ec_today_sessions">—</div>
                </div>
            </div>
            <div class="uat-col-3">
                <div class="uat-card" style="border-top: 4px solid #8b5cf6;">
                    <div class="uat-card-title">Total Bot Sessions</div>
                    <div class="uat-card-des">Số lượng truy cập từ Bot đã được hệ thống bắt lại</div>
                    <div class="uat-card-divider"></div>
                    <div class="uat-stat-value" style="color:#8b5cf6" id="ec_total_bots">—</div>
                </div>
            </div>
            <div class="uat-col-3">
                <div class="uat-card" style="border-top: 4px solid #ef4444;">
                    <div class="uat-card-title">Suspicious Flagger</div>
                    <div class="uat-card-des">Số người dùng có điểm bất thường bị đánh dấu đỏ</div>
                    <div class="uat-card-divider"></div>
                    <div class="uat-stat-value" style="color:#ef4444" id="ec_suspicious_users">—</div>
                </div>
            </div>
        </div>
        <!-- Hourly Traffic Analytics -->
        <div class="uat-grid-row">
            <div class="uat-col-12">
                <div class="uat-card" style="margin-bottom: 24px; border-top: 4px solid #8b5cf6;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div class="uat-card-title">Hourly Traffic Analytics</div>
                            <div class="uat-card-des">Lưu lượng truy cập theo từng khung giờ trong ngày</div>
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                            <button id="ec_hourly_toggle_btn" class="uat-btn uat-btn-sm"
                                style="background:#f8fafc; color:#334155; border:1px solid #e2e8f0; padding:6px 12px; border-radius:6px; font-weight:600; cursor:pointer;">
                                Đổi sang Bar Chart
                            </button>
                            <button id="ec_hourly_compare_btn" class="uat-btn uat-btn-sm"
                                style="display:none; background:#f0fdf4; color:#16a34a; border:1px solid #86efac; padding:6px 12px; border-radius:6px; font-weight:600; cursor:pointer;">
                                So sánh với Hôm nay
                            </button>
                            <button id="ec_hourly_mode_btn" class="uat-btn uat-btn-sm"
                                style="background:#d1fae5; color:#047857; border:1px solid #a7f3d0; padding:6px 12px; border-radius:6px; font-weight:600; cursor:pointer;">
                                Xem dữ liệu Lịch sử
                            </button>
                        </div>
                    </div>
                    <div class="uat-card-divider"></div>
                    <div style="width:100%; overflow-x:auto; overflow-y:hidden; padding-bottom:8px;">
                        <div style="position:relative; min-width:700px; height:300px;">
                            <canvas id="ec_hourly_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Daily Quality -->
        <h2 class="uat-section-title">Dữ liệu phân tích hằng ngày <span id="ec_selected_date_label">(tính của ngày hôm
                trước)</span></h2>
        <div class="uat-grid-row" id="ec_quality_cards">
            <div class="uat-col-3">
                <div class="uat-card" style="border-left: 4px solid #10b981;">
                    <div class="uat-card-title">Engagement Rate <span class="badge"
                            style="background:#d1fae5; color:#047857" id="ec_engage_sess">0 sess</span></div>
                    <div class="uat-card-des">Tỉ lệ người dùng tương tác tích cực trên 10s</div>
                    <div class="uat-card-divider"></div>
                    <div class="uat-stat-value" id="ec_engage_rate">—</div>
                </div>
            </div>
            <div class="uat-col-3">
                <div class="uat-card" style="border-left: 4px solid #f59e0b;">
                    <div class="uat-card-title">Bounce Rate <span class="badge"
                            style="background:#fef3c7; color:#b45309" id="ec_bounce_sess">0 sess</span></div>
                    <div class="uat-card-des">Tỉ lệ người dùng rời đi trong 5s, 0 tương tác</div>
                    <div class="uat-card-divider"></div>
                    <div class="uat-stat-value" id="ec_bounce_rate">—</div>
                </div>
            </div>
            <div class="uat-col-3">
                <div class="uat-card" style="border-left: 4px solid #6366f1;">
                    <div class="uat-card-title" style="font-size:12px;">Typing Rate <span class="badge"
                            style="background:#e0e7ff; color:#4338ca" id="ec_typing_sess">0 sess</span></div>
                    <div class="uat-card-des">Tỉ lệ sessions có tương tác nhập liệu form liên tục</div>
                    <div class="uat-card-divider"></div>
                    <div class="uat-stat-value" id="ec_typing_rate">—</div>
                </div>
            </div>
            <div class="uat-col-3">
                <div class="uat-card" style="border-left: 4px solid #3b82f6;">
                    <div class="uat-card-title" style="font-size:12px;">Fingerprint Rate <span class="badge"
                            style="background:#eff6ff; color:#1d4ed8" id="ec_fp_sess">0 sess</span></div>
                    <div class="uat-card-des">Tỉ lệ xác thực danh tính & thiết bị qua fingerprint</div>
                    <div class="uat-card-divider"></div>
                    <div class="uat-stat-value" id="ec_fp_rate">—</div>
                </div>
            </div>
        </div>

        <!-- Geo Map + Top Pages -->
        <div class="uat-grid-row">
            <div class="uat-col-6">
                <div class="uat-card" style="display:flex; flex-direction:column;">
                    <div class="uat-card-title">Top Locations</div>
                    <div class="uat-card-des">Khu vực địa lý biểu diễn mật độ session truy cập</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_geo_map" style="width: 100%; flex: 1; min-height: 280px;"></div>
                </div>
            </div>
            <div class="uat-col-6">
                <div class="uat-card">
                    <div class="uat-card-title">Top Viewed Pages</div>
                    <div class="uat-card-des">Các trang đích thu hút lượng xem chi tiết nhiều nhất</div>
                    <div class="uat-card-divider"></div>
                    <table class="uat-table">
                        <thead>
                            <tr>
                                <th style="width:140px;">URL PATH</th>
                                <th style="text-align:right">SESS</th>
                                <th style="text-align:right">%</th>
                            </tr>
                        </thead>
                        <tbody id="ec_top_pages_tbody">
                            <tr>
                                <td colspan="3" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Traffic Sources, Devices, OS, Goals -->
        <div class="uat-grid-row">
            <div class="uat-col-3">
                <div class="uat-card">
                    <div class="uat-card-title">Traffic Sources</div>
                    <div class="uat-card-des">Phân tích nguồn lưu lượng truy cập từ ngoài vào</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_traffic_sources"></div>
                </div>
            </div>
            <div class="uat-col-3">
                <div class="uat-card">
                    <div class="uat-card-title">Device Types</div>
                    <div class="uat-card-des">Nền tảng thiết bị (PC, Mobile, Tablet)</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_devices"></div>
                </div>
            </div>
            <div class="uat-col-3">
                <div class="uat-card">
                    <div class="uat-card-title">Operating Systems</div>
                    <div class="uat-card-des">Thống kê hệ điều hành người dùng sử dụng</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_os"></div>
                </div>
            </div>
            <div class="uat-col-3">
                <div class="uat-card">
                    <div class="uat-card-title">Goals & Funnels</div>
                    <div class="uat-card-des">Tỉ lệ chuyển đổi của các quy trình cốt lõi trên website</div>
                    <div class="uat-card-divider"></div>
                    <table class="uat-table">
                        <thead>
                            <tr>
                                <th style="width:120px;">GOAL NAME</th>
                                <th style="text-align:right">RATE</th>
                            </tr>
                        </thead>
                        <tbody id="ec_goals_tbody">
                            <tr>
                                <td colspan="2" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Passenger Typing Analytics -->
        <h2 class="uat-section-title" style="margin-top: 24px;">Phân tích tương tác nhập liệu (Passenger Typing)</h2>
        <div class="uat-grid-row">
            <div class="uat-col-4">
                <div class="uat-card" style="border-top: 4px solid #f43f5e; height: 100%;">
                    <div class="uat-card-title">Tỷ lệ điền Form</div>
                    <div class="uat-card-des">Tỉ lệ khách hàng thực sự có tương tác nhập liệu</div>
                    <div class="uat-card-divider"></div>
                    <div style="position:relative; width:160px; height:160px; margin: 0 auto 16px;">
                        <canvas id="ec_typing_donut" width="160" height="160"></canvas>
                    </div>
                    <div
                        style="display:flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 12px; border-radius: 8px;">
                        <div>
                            <div style="font-size: 11px; color:#64748b; font-weight:600; text-transform:uppercase;">Có
                                nhập liệu</div>
                            <div style="color:#f43f5e; font-size: 18px; font-weight: 700;" id="ec_typing_sess_count">—
                            </div>
                        </div>
                        <div style="width: 1px; height: 30px; background: #e2e8f0;"></div>
                        <div style="text-align: right;">
                            <div style="font-size: 11px; color:#64748b; font-weight:600; text-transform:uppercase;">Tổng
                                VIEW</div>
                            <div style="color:#334155; font-size: 18px; font-weight: 700;" id="ec_typing_total_viewers">
                                —</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="uat-col-8">
                <div class="uat-card" style="height: 100%; padding:0; overflow:hidden;">
                    <div style="padding: 20px 20px 10px;">
                        <div class="uat-card-title">Live Typing Logs</div>
                        <div class="uat-card-des">Log dữ liệu thực tế khách hàng đã và đang nhập (nếu có form)</div>
                    </div>
                    <div style="max-height: 280px; overflow-y: auto; padding: 0 20px 20px;">
                        <table class="uat-table uat-table-stackable">
                            <thead>
                                <tr>
                                    <th
                                        style="position:sticky; top:0; background:#fff; z-index:2; border-bottom:1px solid #e2e8f0; width: 140px;">
                                        TIME & IP</th>
                                    <th
                                        style="position:sticky; top:0; background:#fff; z-index:2; border-bottom:1px solid #e2e8f0;">
                                        LOCATION</th>
                                    <th
                                        style="position:sticky; top:0; background:#fff; z-index:2; border-bottom:1px solid #e2e8f0;">
                                        DỮ LIỆU ĐÃ NHẬP</th>
                                </tr>
                            </thead>
                            <tbody id="ec_typing_logs_tbody">
                                <tr>
                                    <td colspan="3" class="uat-empty-cell">Đang tải...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- END TAB OVERVIEW -->

    <!-- TAB: FLIGHT ANALYTICS -->
    <div id="uat-tab-flights" class="uat-tab-content">
        <h2 class="uat-section-title">Flight Behavior Analytics</h2>
        <div class="uat-grid-row">
            <div class="uat-col-3">
                <div class="uat-card" style="border-left: 4px solid #10b981;">
                    <div class="uat-card-title">Search to Detail <span class="badge"
                            style="background:#d1fae5; color:#047857" id="ec_s2d_sess">0 sess</span></div>
                    <div class="uat-card-des">Tỉ lệ chuyển đổi ấn vào xem chi tiết sau khi tìm kiếm</div>
                    <div class="uat-card-divider"></div>
                    <div class="uat-stat-value" id="ec_s2d_rate">—</div>
                </div>
            </div>
            <div class="uat-col-6">
                <div class="uat-card">
                    <div class="uat-card-title">Journey Types</div>
                    <div class="uat-card-des">Hành trình người dùng chọn: Một chiều, khứ hồi...</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_journey_types" style="display:flex; gap:20px; flex-wrap:wrap;"></div>
                </div>
            </div>
        </div>
        <div class="uat-grid-row">
            <div class="uat-col-4">
                <div class="uat-card">
                    <div class="uat-card-title">Top Routes</div>
                    <div class="uat-card-des">Các đường bay (điếm đến, đi) được tìm kiếm liên tục</div>
                    <div class="uat-card-divider"></div>
                    <table class="uat-table">
                        <thead>
                            <tr>
                                <th>Route</th>
                                <th style="text-align:right">Lượt</th>
                            </tr>
                        </thead>
                        <tbody id="ec_routes_tbody">
                            <tr>
                                <td colspan="2" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="uat-col-4">
                <div class="uat-card">
                    <div class="uat-card-title">Departure Leadtime</div>
                    <div class="uat-card-des">Tỉ lệ ước tính thời gian đặt vé trước chuyến bay</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_leadtime"></div>
                </div>
            </div>
            <div class="uat-col-4">
                <div class="uat-card">
                    <div class="uat-card-title">Departure Times</div>
                    <div class="uat-card-des">Khoảng thời gian bay (sáng, trưa, tối) phổ biến</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_departure_times"></div>
                </div>
            </div>
        </div>
        <div class="uat-grid-row">
            <div class="uat-col-6">
                <div class="uat-card">
                    <div class="uat-card-title">Route Plans</div>
                    <div class="uat-card-des">Chuyến bay phổ biến theo thiết lập hành trình + ngày đi</div>
                    <div class="uat-card-divider"></div>
                    <table class="uat-table">
                        <thead>
                            <tr>
                                <th>Route & Date</th>
                                <th style="text-align:right">Sessions</th>
                            </tr>
                        </thead>
                        <tbody id="ec_route_plans_tbody">
                            <tr>
                                <td colspan="2" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="uat-col-6">
                <div class="uat-card">
                    <div class="uat-card-title">Airline Filters</div>
                    <div class="uat-card-des">Lựa chọn bộ lọc theo các hãng Hàng Tuyến được chọn</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_airlines"></div>
                </div>
            </div>
        </div>

        <!-- ── Peak Hour Intelligence ───────────────────────────── -->
        <div class="uat-grid-row" style="margin-top:8px;">
            <div class="uat-col-12">
                <div class="uat-card" style="border-top:4px solid #f59e0b;">
                    <div
                        style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                        <div>
                            <div class="uat-card-title">Peak Hour Intelligence</div>
                            <div class="uat-card-des">Phân phối lưu lượng theo giờ — highlight 3 khung giờ cao điểm
                            </div>
                        </div>
                        <div id="ec_peak_stat_chips" style="display:flex; gap:8px; flex-wrap:wrap;"></div>
                    </div>
                    <div class="uat-card-divider"></div>
                    <div style="width:100%; overflow-x:auto; overflow-y:hidden; padding-bottom:8px;">
                        <div style="position:relative; min-width:700px; height:240px;">
                            <canvas id="ec_peak_hours_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── 3 Panels: Popular Routes, Niche Routes, Peak IPs ──────────────────────────── -->
        <div class="uat-grid-row" style="margin-top:8px;">
            <!-- Popular Routes -->
            <div class="uat-col-4">
                <div class="uat-card"
                    style="padding:0; overflow:hidden; border-top:4px solid #3b82f6; display:flex; flex-direction:column;">
                    <div style="padding:18px 20px 10px 20px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:2px;">
                            <span
                                style="background:#dbeafe; color:#1d4ed8; border-radius:6px; padding:3px 9px; font-size:11px; font-weight:700; letter-spacing:.5px;">HOT</span>
                            <div class="uat-card-title" style="color:#1d4ed8; margin:0;">Hành trình Phổ biến-Giờ cao điểm</div>
                        </div>
                        <div class="uat-card-des">
                            Chỉ tính sessions tìm kiếm trong <strong>giờ cao điểm</strong> (top 3 giờ nhiều traffic
                            nhất).
                            <br>— <em>Sess</em> = người dùng duy nhất (unique); <em>Lượt</em> = số lần pageview.
                            <br>— Tổng sess route có thể nhỏ hơn cộng tay các ngày: 1 người search nhiều ngày khởi hành
                            khác nhau vẫn tính 1 sess.
                            <br>— Sess trong ngày có thể nhỏ hơn cộng tay các giờ: 1 người search lúc 13h và 15h vẫn
                            tính 1 sess cho ngày đó.
                        </div>
                        <div class="uat-card-divider"></div>
                    </div>
                    <div id="ec_popular_routes_list"
                        style="padding:0 20px 18px; flex:1; max-height:400px; overflow-y:auto;"></div>
                </div>
            </div>
            <!-- Niche Routes -->
            <div class="uat-col-4">
                <div class="uat-card"
                    style="padding:0; overflow:hidden; border-top:4px solid #8b5cf6; display:flex; flex-direction:column;">
                    <div style="padding:18px 20px 10px 20px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:2px;">
                            <span
                                style="background:#ede9fe; color:#6d28d9; border-radius:6px; padding:3px 9px; font-size:11px; font-weight:700; letter-spacing:.5px;">TIỀM
                                NĂNG</span>
                            <div class="uat-card-title" style="color:#6d28d9; margin:0;">Hành trình Khác-Giờ cao điểm</div>
                        </div>
                        <div class="uat-card-des">
                            Hành trình ngoài top phổ biến — tiềm năng khai thác. Chỉ tính sessions trong <strong>giờ cao
                                điểm</strong>.
                            <br>— Người dùng search những chặng này đúng lúc traffic cao → nhu cầu thực, không phải cồ
                            tìm kiếm ngẫu nhiên.
                            <br>— Nguyên lí đếm sess giống panel bên: unique theo người dùng, không theo số lần click.
                        </div>
                        <div class="uat-card-divider"></div>
                    </div>
                    <div id="ec_niche_routes_list"
                        style="padding:0 20px 18px; flex:1; max-height:400px; overflow-y:auto;"></div>
                </div>
            </div>
            <!-- Peak IPs -->
            <div class="uat-col-4">
                <div class="uat-card"
                    style="padding:0; overflow:hidden; border-top:4px solid #ef4444; display:flex; flex-direction:column;">
                    <div style="padding:18px 20px 10px 20px;">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:2px;">
                            <div class="uat-card-title" style="margin:0;">IP Hoạt động Giờ Cao Điểm</div>
                            <span id="ec_peak_ips_count"
                                style="font-size:11px; font-weight:600; color:#ef4444; background:#fef2f2; padding:3px 8px; border-radius:12px; border:1px solid #fecaca; white-space:nowrap;"></span>
                        </div>
                        <div class="uat-card-des">IP có hành vi tìm chuyến bay lúc cao điểm</div>
                        <div class="uat-card-divider"></div>
                    </div>
                    <div id="ec_peak_ips_grid"
                        style="display:flex; flex-direction:column; gap:8px; padding:0 20px 18px; flex:1; max-height:400px; overflow-y:auto;">
                    </div>
                </div>
            </div>
        </div>


    </div><!-- END TAB FLIGHTS -->

    <!-- TAB: TOP ELEMENTS -->
    <div id="uat-tab-elements" class="uat-tab-content">
        <h2 class="uat-section-title">Top Interacted Elements</h2>
        <p class="uat-tab-des">Các tương tác người dùng thực hiện trên trang</p>
        <div class="uat-grid-row" style="margin-top: 20px;">
            <div class="uat-col-4">
                <div class="uat-card" style="padding:0; overflow:hidden;">
                    <div style="padding:24px 24px 12px 24px;">
                        <div class="uat-card-title">TOP INTERACTION CLICKS</div>
                        <div class="uat-card-des">Cấu trúc các phần tử được click nhiều nhất</div>
                    </div>
                    <table class="uat-table">
                        <thead>
                            <tr>
                                <th>VALUE</th>
                                <th style="text-align:right">SESS</th>
                                <th style="text-align:right; width:45px;">#</th>
                            </tr>
                        </thead>
                        <tbody id="ec_clicks_tbody">
                            <tr>
                                <td colspan="3" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="uat-col-4">
                <div class="uat-card" style="padding:0; overflow:hidden;">
                    <div style="padding:24px 24px 12px 24px;">
                        <div class="uat-card-title">TOP INTERACTION TYPING</div>
                        <div class="uat-card-des">Các ô nhập liệu được tương tác nhiều nhất</div>
                    </div>
                    <table class="uat-table">
                        <thead>
                            <tr>
                                <th>VALUE</th>
                                <th style="text-align:right">SESS</th>
                                <th style="text-align:right; width:45px;">#</th>
                            </tr>
                        </thead>
                        <tbody id="ec_typing_tbody">
                            <tr>
                                <td colspan="3" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="uat-col-4">
                <div class="uat-card" style="padding:0; overflow:hidden;">
                    <div style="padding:24px 24px 12px 24px;">
                        <div class="uat-card-title">TOP INTERACTION SCROLLED</div>
                        <div class="uat-card-des">Lưu lượng độ sâu người dùng cuộn tới</div>
                    </div>
                    <table class="uat-table">
                        <thead>
                            <tr>
                                <th>VALUE</th>
                                <th style="text-align:right">SESS</th>
                                <th style="text-align:right; width:45px;">#</th>
                            </tr>
                        </thead>
                        <tbody id="ec_scroll_tbody">
                            <tr>
                                <td colspan="3" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- END TAB ELEMENTS -->

    <!-- TAB: AREA ANALYTICS -->
    <div id="uat-tab-areas" class="uat-tab-content">
        <h2 class="uat-section-title">Area Analytics</h2>
        <p class="uat-tab-des" style="margin-bottom: 20px;">Phân tích lượt tìm kiếm theo 3 miền địa lý Bắc - Trung -
            Nam. Mỗi miền tổng hợp các route bắt đầu từ sân bay thuộc khu vực đó.</p>

        <!-- Overview Row: Donut + Stats + Top Compare -->
        <div class="uat-grid-row" style="align-items:stretch;">
            <!-- Donut Chart -->
            <div class="uat-col-4">
                <div class="uat-card"
                    style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%;">
                    <div class="uat-card-title">Phân phối theo miền</div>
                    <div class="uat-card-des">Tỷ lệ sessions xuất phát từ mỗi khu vực địa lý</div>
                    <div class="uat-card-divider"></div>
                    <div style="position:relative; width:190px; height:190px; margin: 12px auto 0;">
                        <canvas id="ec_area_donut" width="190" height="190"></canvas>
                    </div>
                    <div
                        style="display:flex; gap:12px; margin-top:16px; flex-wrap:wrap; justify-content:center; font-size:12px; font-weight:600; color:#475569;">
                        <span><span
                                style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#3b82f6; margin-right:4px; vertical-align:middle;"></span>Bắc</span>
                        <span><span
                                style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#f59e0b; margin-right:4px; vertical-align:middle;"></span>Trung</span>
                        <span><span
                                style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#10b981; margin-right:4px; vertical-align:middle;"></span>Nam</span>
                        <span><span
                                style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#e2e8f0; margin-right:4px; vertical-align:middle;"></span>Khác</span>
                    </div>
                </div>
            </div>
            <!-- Stats + Compare -->
            <div class="uat-col-8">
                <div class="uat-grid-row" style="margin-bottom:24px;">
                    <div class="uat-col-4">
                        <div class="uat-card" style="border-top:4px solid #3b82f6; height:100%;">
                            <div class="uat-card-title" style="color:#3b82f6;">Miền Bắc</div>
                            <div class="uat-card-des">HAN, HPH, VII, VDO, THD...</div>
                            <div class="uat-card-divider"></div>
                            <div class="uat-stat-value" id="ec_area_north_sessions"
                                style="color:#3b82f6; font-size:28px;">&#8212;</div>
                            <div style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;">
                                <span
                                    style="background:#dbeafe; color:#1d4ed8; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:700;"
                                    id="ec_area_north_pct">&#8212;</span>
                                <span
                                    style="background:#f1f5f9; color:#64748b; padding:3px 8px; border-radius:20px; font-size:11px;"
                                    id="ec_area_north_routebadge">0 routes</span>
                            </div>
                        </div>
                    </div>
                    <div class="uat-col-4">
                        <div class="uat-card" style="border-top:4px solid #f59e0b; height:100%;">
                            <div class="uat-card-title" style="color:#f59e0b;">Miền Trung</div>
                            <div class="uat-card-des">DAD, HUI, VDH, UIH, TBB...</div>
                            <div class="uat-card-divider"></div>
                            <div class="uat-stat-value" id="ec_area_central_sessions"
                                style="color:#f59e0b; font-size:28px;">&#8212;</div>
                            <div style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;">
                                <span
                                    style="background:#fef3c7; color:#b45309; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:700;"
                                    id="ec_area_central_pct">&#8212;</span>
                                <span
                                    style="background:#f1f5f9; color:#64748b; padding:3px 8px; border-radius:20px; font-size:11px;"
                                    id="ec_area_central_routebadge">0 routes</span>
                            </div>
                        </div>
                    </div>
                    <div class="uat-col-4">
                        <div class="uat-card" style="border-top:4px solid #10b981; height:100%;">
                            <div class="uat-card-title" style="color:#10b981;">Miền Nam</div>
                            <div class="uat-card-des">SGN, VCA, PQC, CXR, VCS...</div>
                            <div class="uat-card-divider"></div>
                            <div class="uat-stat-value" id="ec_area_south_sessions"
                                style="color:#10b981; font-size:28px;">&#8212;</div>
                            <div style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;">
                                <span
                                    style="background:#d1fae5; color:#065f46; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:700;"
                                    id="ec_area_south_pct">&#8212;</span>
                                <span
                                    style="background:#f1f5f9; color:#64748b; padding:3px 8px; border-radius:20px; font-size:11px;"
                                    id="ec_area_south_routebadge">0 routes</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="uat-card" style="height:auto; margin-bottom:24px;">
                    <div class="uat-card-title">So sánh Top Routes theo Miền</div>
                    <div class="uat-card-des">Top 4 route phổ biến nhất của từng khu vực - progress bar tương đối trong
                        miền</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_area_compare_bars"></div>
                </div>
            </div>
        </div>

        <!-- Full Route Tables -->
        <div class="uat-grid-row" style="margin-top:24px;">
            <div class="uat-col-4">
                <div class="uat-card" style="padding:0; overflow:hidden;">
                    <div style="padding:20px 20px 0 20px;">
                        <div class="uat-card-title" style="color:#3b82f6;">Tất cả Routes &#8212; Miền Bắc</div>
                        <div class="uat-card-des">Danh sách đầy đủ hành trình xuất phát từ miền Bắc</div>
                        <div class="uat-card-divider"></div>
                    </div>
                    <table class="uat-table" style="margin-top:-16px;">
                        <thead>
                            <tr>
                                <th>ROUTE</th>
                                <th style="text-align:right; width:70px;">SESS</th>
                                <th style="text-align:right; width:40px;">#</th>
                            </tr>
                        </thead>
                        <tbody id="ec_area_north_tbody">
                            <tr>
                                <td colspan="3" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="uat-col-4">
                <div class="uat-card" style="padding:0; overflow:hidden;">
                    <div style="padding:20px 20px 0 20px;">
                        <div class="uat-card-title" style="color:#f59e0b;">Tất cả Routes &#8212; Miền Trung</div>
                        <div class="uat-card-des">Danh sách đầy đủ hành trình xuất phát từ miền Trung</div>
                        <div class="uat-card-divider"></div>
                    </div>
                    <table class="uat-table" style="margin-top:-16px;">
                        <thead>
                            <tr>
                                <th>ROUTE</th>
                                <th style="text-align:right; width:70px;">SESS</th>
                                <th style="text-align:right; width:40px;">#</th>
                            </tr>
                        </thead>
                        <tbody id="ec_area_central_tbody">
                            <tr>
                                <td colspan="3" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="uat-col-4">
                <div class="uat-card" style="padding:0; overflow:hidden;">
                    <div style="padding:20px 20px 0 20px;">
                        <div class="uat-card-title" style="color:#10b981;">Tất cả Routes &#8212; Miền Nam</div>
                        <div class="uat-card-des">Danh sách đầy đủ hành trình xuất phát từ miền Nam</div>
                        <div class="uat-card-divider"></div>
                    </div>
                    <table class="uat-table" style="margin-top:-16px;">
                        <thead>
                            <tr>
                                <th>ROUTE</th>
                                <th style="text-align:right; width:70px;">SESS</th>
                                <th style="text-align:right; width:40px;">#</th>
                            </tr>
                        </thead>
                        <tbody id="ec_area_south_tbody">
                            <tr>
                                <td colspan="3" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Full IP Tables -->
        <h3 class="uat-section-title" style="margin-top:24px; font-size:16px; margin-bottom: 4px;">Danh sách IP theo Khu
            Vực (Target Adds)</h3>
        <p class="uat-tab-des" style="margin-bottom: 16px;">Tracking danh sách IP thực tế của khách truy cập phân bổ vào
            từng miền để phục vụ lên chiến dịch quảng cáo.</p>
        <div class="uat-grid-row">
            <div class="uat-col-4">
                <div class="uat-card" style="padding:0; overflow:hidden;">
                    <div style="padding:20px 20px 10px 20px;">
                        <div class="uat-card-title" style="color:#3b82f6;">IPs &#8212; Miền Bắc</div>
                        <div class="uat-card-des">Địa chỉ IP khách truy cập từ miền Bắc</div>
                        <div style="margin-top:10px; position:relative;">
                            <input type="text" class="uat-area-ip-search" data-zone="north" placeholder="Tìm kiếm IP..."
                                style="width:100%; padding:8px 12px 8px 30px; border:1px solid #e2e8f0; border-radius:6px; font-size:12px; outline:none; color:#334155; box-sizing:border-box;">
                            <svg viewBox="0 0 24 24" width="14" height="14" stroke="#94a3b8" stroke-width="2"
                                fill="none" stroke-linecap="round" stroke-linejoin="round"
                                style="position:absolute; left:10px; top:10px;">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                    </div>
                    <div style="max-height: 280px; overflow-y: auto;">
                        <table class="uat-table">
                            <thead>
                                <tr>
                                    <th
                                        style="position:sticky; top:0; background:#f8fafc; z-index:2; border-bottom:1px solid #e2e8f0;">
                                        IP ADDRESS</th>
                                    <th
                                        style="position:sticky; top:0; background:#f8fafc; z-index:2; border-bottom:1px solid #e2e8f0; text-align:right;">
                                        CITY/REGION</th>
                                </tr>
                            </thead>
                            <tbody id="ec_area_north_ip_tbody">
                                <tr>
                                    <td colspan="2" class="uat-empty-cell">Đang tải...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="uat-col-4">
                <div class="uat-card" style="padding:0; overflow:hidden;">
                    <div style="padding:20px 20px 10px 20px;">
                        <div class="uat-card-title" style="color:#f59e0b;">IPs &#8212; Miền Trung</div>
                        <div class="uat-card-des">Địa chỉ IP khách truy cập từ miền Trung</div>
                        <div style="margin-top:10px; position:relative;">
                            <input type="text" class="uat-area-ip-search" data-zone="central"
                                placeholder="Tìm kiếm IP..."
                                style="width:100%; padding:8px 12px 8px 30px; border:1px solid #e2e8f0; border-radius:6px; font-size:12px; outline:none; color:#334155; box-sizing:border-box;">
                            <svg viewBox="0 0 24 24" width="14" height="14" stroke="#94a3b8" stroke-width="2"
                                fill="none" stroke-linecap="round" stroke-linejoin="round"
                                style="position:absolute; left:10px; top:10px;">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                    </div>
                    <div style="max-height: 280px; overflow-y: auto;">
                        <table class="uat-table">
                            <thead>
                                <tr>
                                    <th
                                        style="position:sticky; top:0; background:#f8fafc; z-index:2; border-bottom:1px solid #e2e8f0;">
                                        IP ADDRESS</th>
                                    <th
                                        style="position:sticky; top:0; background:#f8fafc; z-index:2; border-bottom:1px solid #e2e8f0; text-align:right;">
                                        CITY/REGION</th>
                                </tr>
                            </thead>
                            <tbody id="ec_area_central_ip_tbody">
                                <tr>
                                    <td colspan="2" class="uat-empty-cell">Đang tải...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="uat-col-4">
                <div class="uat-card" style="padding:0; overflow:hidden;">
                    <div style="padding:20px 20px 10px 20px;">
                        <div class="uat-card-title" style="color:#10b981;">IPs &#8212; Miền Nam</div>
                        <div class="uat-card-des">Địa chỉ IP khách truy cập từ miền Nam</div>
                        <div style="margin-top:10px; position:relative;">
                            <input type="text" class="uat-area-ip-search" data-zone="south" placeholder="Tìm kiếm IP..."
                                style="width:100%; padding:8px 12px 8px 30px; border:1px solid #e2e8f0; border-radius:6px; font-size:12px; outline:none; color:#334155; box-sizing:border-box;">
                            <svg viewBox="0 0 24 24" width="14" height="14" stroke="#94a3b8" stroke-width="2"
                                fill="none" stroke-linecap="round" stroke-linejoin="round"
                                style="position:absolute; left:10px; top:10px;">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                    </div>
                    <div style="max-height: 280px; overflow-y: auto;">
                        <table class="uat-table">
                            <thead>
                                <tr>
                                    <th
                                        style="position:sticky; top:0; background:#f8fafc; z-index:2; border-bottom:1px solid #e2e8f0;">
                                        IP ADDRESS</th>
                                    <th
                                        style="position:sticky; top:0; background:#f8fafc; z-index:2; border-bottom:1px solid #e2e8f0; text-align:right;">
                                        CITY/REGION</th>
                                </tr>
                            </thead>
                            <tbody id="ec_area_south_ip_tbody">
                                <tr>
                                    <td colspan="2" class="uat-empty-cell">Đang tải...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- City Distribution -->
        <div class="uat-grid-row" style="margin-top:24px;">
            <div class="uat-col-12">
                <div class="uat-card" style="padding:0; overflow:hidden;">
                    <div style="padding:20px 20px 0 20px;">
                        <div class="uat-card-title" style="color:#0ea5e9;">Phân bổ theo Tỉnh/Thành phố</div>
                        <div class="uat-card-des">Khách truy cập chủ yếu đến từ đâu (chỉ đếm các session không phải bot)
                        </div>
                        <div class="uat-card-divider"></div>
                    </div>
                    <table class="uat-table" style="margin-top:-16px;">
                        <thead>
                            <tr>
                                <th>KHU VỰC CHI TIẾT</th>
                                <th style="text-align:right; width:90px;">SESS</th>
                                <th style="text-align:right; width:100px;">TỶ LỆ</th>
                                <th style="text-align:right; width:50px;">#</th>
                            </tr>
                        </thead>
                        <tbody id="ec_area_city_tbody">
                            <tr>
                                <td colspan="4" class="uat-empty-cell">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="uat-city-toggle-btn" data-expanded="0"
                        style="text-align:center; padding:10px; cursor:pointer; font-size:12px; font-weight:600; background:#f8fafc; color:#3b82f6; border-top:1px solid #e2e8f0; transition:all 0.2s;">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"
                            style="vertical-align:middle; margin-right:4px; margin-top:-2px;">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg> <span style="vertical-align:middle;">Xem thêm</span>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- END TAB AREAS -->

    <!-- TAB: SUSPICIOUS IPS -->
    <div id="uat-tab-suspicious" class="uat-tab-content">
        <h2 class="uat-section-title" style="margin-bottom:0px;">Suspicious Users Activity</h2>
        <p class="uat-tab-des" style="margin-bottom: 20px;">Những người dùng có hành vi đáng ngờ và có mức điểm tích luỹ
            cao sẽ được báo cáo về đây.</p>
        <div class="uat-card" style="padding:0; overflow:hidden;">
            <!-- For thick fat table cards, no extra divider needed unless asked. But let's add title/des inside standardly as requested -->
            <div style="padding: 24px 24px 0 24px;">
                <div class="uat-card-title">SUSPICIOUS ACTIVITIES RECORDED</div>
                <div class="uat-card-des">Danh sách IP và điểm đánh giá bất thường dựa trên hành vi</div>
                <div class="uat-card-divider"></div>
            </div>
            <table class="uat-table uat-table-stackable" style="margin-top:-16px;">
                <thead>
                    <tr>
                        <th style="padding-left:24px;">IP Address</th>
                        <th>User ID</th>
                        <th>Score</th>
                        <th>Last Seen</th>
                        <th>Connections</th>
                    </tr>
                </thead>
                <tbody id="ec_suspicious_tbody">
                    <tr>
                        <td colspan="5" class="uat-empty-cell">Đang tải...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div><!-- END TAB SUSPICIOUS -->

    <!-- TAB: SCRAPING PAIRS -->
    <div id="uat-tab-scraping" class="uat-tab-content">
        <div class="uat-tab-header-flex">
            <div class="uat-tab-header-info">
                <h2 class="uat-tab-title">Scraping Route Analysis <span class="badge badge-red"
                        id="ec_scraping_count2">0</span></h2>
                <p class="uat-tab-desc">Phân tích các IP tìm kiếm xuôi/ngược nhiều lần trên cùng 1 hành trình để lọc
                    click tặc / bot scraping.</p>
            </div>
            <div class="uat-tab-header-search">
                <input type="text" id="ec_scraping_search" class="uat-search-input" placeholder="Search IP or route..."
                    autocomplete="off">
            </div>
        </div>
        <div class="uat-card" style="padding:0; overflow:hidden; border-top: none; border-radius: 0 0 12px 12px;">
            <div style="padding: 24px 24px 10px 24px;">
                <div class="uat-card-title">SCRAPING DETECTION LOGS</div>
                <div class="uat-card-des">Hồ sơ nghi ngờ cào dữ liệu được thống kê phân loại cho mỗi IP</div>
            </div>
            <div style="max-height: 450px; overflow-y: auto;">
                <table class="uat-table uat-table-stackable uat-scraping-master-table" id="ec_scraping_table">
                    <thead>
                        <tr>
                            <th
                                style="position:sticky; top:0; background:#f8fafc; z-index:2; border-bottom:1px solid #e2e8f0; padding-left:24px; width:200px;">
                                Scraping Entity</th>
                            <th
                                style="position:sticky; top:0; background:#f8fafc; z-index:2; border-bottom:1px solid #e2e8f0; width:100px;">
                                Threat Score</th>
                            <th
                                style="position:sticky; top:0; background:#f8fafc; z-index:2; border-bottom:1px solid #e2e8f0; width:100px;">
                                Total Sess</th>
                            <th
                                style="position:sticky; top:0; background:#f8fafc; z-index:2; border-bottom:1px solid #e2e8f0;">
                                Activity Log (Route Analysis)</th>
                        </tr>
                    </thead>
                    <tbody id="ec_scraping_tbody">
                        <tr>
                            <td colspan="4" class="uat-empty-cell">Đang tải...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- END TAB SCRAPING -->

    <!-- TAB: BOTS -->
    <div id="uat-tab-bots" class="uat-tab-content">
        <h2 class="uat-section-title">Bot Analysis</h2>
        <div class="uat-grid-row">
            <div class="uat-col-4">
                <div class="uat-card" style="border-top: 4px solid #10b981;">
                    <div class="uat-card-title">Safe Bots <span class="badge badge-green"
                            id="ec_safe_bot_count">0</span></div>
                    <div class="uat-card-des">Các định danh bot hợp lệ đã được hệ thống xác nhận (VD: Googlebot)</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_safe_bots_list" class="uat-bot-list"></div>
                </div>
            </div>
            <div class="uat-col-4">
                <div class="uat-card" style="border-top: 4px solid #f59e0b;">
                    <div class="uat-card-title">Suspicious Bots <span class="badge badge-yellow"
                            id="ec_sus_bot_count">0</span></div>
                    <div class="uat-card-des">Danh sách Bot chưa rõ nguồn gốc, cần theo dõi thêm hành vi</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_sus_bots_list" class="uat-bot-list"></div>
                </div>
            </div>
            <div class="uat-col-4">
                <div class="uat-card" style="border-top: 4px solid #ef4444;">
                    <div class="uat-card-title">Danger Bots <span class="badge badge-red"
                            id="ec_danger_bot_count">0</span></div>
                    <div class="uat-card-des">Hệ thống phân tích phát hiện Bot nguy hiểm / pattern scraping rác</div>
                    <div class="uat-card-divider"></div>
                    <div id="ec_danger_bots_list" class="uat-bot-list"></div>
                </div>
            </div>
        </div>
    </div><!-- END TAB BOTS -->

    <!-- TAB: HEATMAP -->
    <div id="uat-tab-heatmap" class="uat-tab-content">
        <div class="uat-header-info" style="margin-bottom: 24px;">
            <h1 style="font-size: 24px; color: #1e293b; margin: 0 0 8px 0;">Heatmap Analytics</h1>
            <p class="description" style="color: #64748b; margin: 0; font-size: 14px;">
                Visualize user interactions with 3-tier precision rendering.
                Click <strong>View Heatmap</strong> to see an overlay intensity on any recorded page.
            </p>
        </div>

        <div class="uat-card" style="border-top: 4px solid #f72585;">
            <div class="uat-card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    Recorded Map Pages <span class="badge badge-blue" id="ec_heatmap_count">0</span>
                    <small style="color:#94a3b8; font-weight:normal; margin-left:8px; font-size:12px;">Top 50 results
                        (All time)</small>
                </div>
                <div class="uat-tab-header-search">
                    <input type="text" id="ec_heatmap_search" class="uat-search-input" placeholder="Tìm kiếm trang..."
                        autocomplete="off"
                        style="width: 200px; padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px;">
                </div>
            </div>

            <div class="uat-table-container">
                <table class="uat-table uat-table-stackable uat-heatmap-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width:40%">Target Page</th>
                            <th>Total Engagement</th>
                            <th>Device Affinity</th>
                            <th>Last Interaction</th>
                            <th style="width:120px" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ec_heatmap_tbody">
                        <tr>
                            <td colspan="5"
                                style="text-align:center; padding: 60px 20px; color:#94a3b8; font-style:italic;">Đang
                                tải dữ liệu Heatmap...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- END TAB HEATMAP -->

    <!-- TAB: IP JOURNEY -->
    <div id="uat-tab-ip_journey" class="uat-tab-content">
        <h2 class="uat-section-title">IP Journey &mdash; Hành Trình Funnel</h2>
        <p class="uat-tab-des" style="margin-bottom:20px;">Trực quan hóa hành trình của một IP qua các bước funnel
            chuẩn. Overlay path thực tế của từng session lên cây chuẩn.</p>

        <!-- Big Card Container -->
        <div class="uat-card" style="padding: 0; overflow: hidden;">

            <!-- Search Form -->
            <div class="ec-journey-search-card" style="border-bottom: 1px solid var(--uat-border);">
                <div class="ec-journey-search-row">
                    <div class="ec-journey-search-field">
                        <label for="ec_journey_ip">Địa chỉ IP</label>
                        <input type="text" id="ec_journey_ip" placeholder="Ví dụ: 113.160.45.12" autocomplete="off" />
                    </div>
                    <div class="ec-journey-search-field">
                        <label for="ec_journey_days">Số ngày gần đây</label>
                        <select id="ec_journey_days">
                            <option value="7">7 ngày</option>
                            <option value="14">14 ngày</option>
                            <option value="30" selected>30 ngày</option>
                            <option value="60">60 ngày</option>
                            <option value="90">90 ngày</option>
                        </select>
                    </div>
                    <div class="ec-journey-search-field ec-journey-search-btn-wrap">
                        <label>&nbsp;</label>
                        <button id="ec_journey_btn" class="uat-btn"
                            style="background:#6366f1;color:#fff;border:none;padding:0 20px;height:38px;border-radius:8px;font-weight:600;cursor:pointer;">Tra
                            cứu</button>
                    </div>
                </div>
            </div>

            <!-- Loading / Error -->
            <div id="ec_journey_loading" style="display:none;" class="ec-journey-state">
                <div class="ec-journey-spinner"></div>
                <span>Đang tải dữ liệu...</span>
            </div>
            <div id="ec_journey_error" style="display:none;" class="ec-journey-state ec-journey-error-state"></div>

            <!-- Result Area -->
            <div id="ec_journey_result" style="display:none;">

                <!-- Summary Bar -->
                <div id="ec_journey_summary" class="ec-journey-summary-bar"
                    style="border-bottom: 1px solid var(--uat-border);"></div>

                <!-- Session Selector -->
                <div class="ec-journey-session-card" id="ec_journey_sessions_card"
                    style="display:none; border-bottom: 1px dashed var(--uat-border);">
                    <div class="ec-journey-sessions-header">
                        <span class="ec-journey-sessions-title">Chọn Session để xem hành trình</span>
                        <span id="ec_journey_session_count" class="badge"
                            style="background:#e0e7ff;color:#4338ca;"></span>
                    </div>
                    <div id="ec_journey_session_list" class="ec-journey-session-list"></div>
                </div>

                <!-- Tree Chart -->
                <div style="padding:24px;">
                    <div class="ec-journey-tree-header">
                        <span class="ec-journey-tree-title">Cây Funnel Chuẩn &amp; Hành Trình Thực Tế</span>
                        <div class="ec-journey-legend">
                            <span class="ec-legend-item ec-legend-visited">Đã đi qua</span>
                            <span class="ec-legend-item ec-legend-dropped">Thoát tại</span>
                            <span class="ec-legend-item ec-legend-backtrack">Quay lại</span>
                            <span class="ec-legend-item ec-legend-converted">Hoàn tất</span>
                            <span class="ec-legend-item ec-legend-not-visited">Chưa đến</span>
                        </div>
                    </div>
                    <div id="ec_journey_tree" class="ec-journey-tree"></div>
                    <div id="ec_journey_path_detail" class="ec-journey-path-detail" style="display:none;"></div>
                </div>
            </div>

        </div><!-- END BIG CARD -->
    </div><!-- END TAB IP JOURNEY -->

</div><!-- END uat-wrap -->

<script type="text/javascript" src="modules/EC_TongHop/js/ec_tonghop.js?v=2.0.8"></script>