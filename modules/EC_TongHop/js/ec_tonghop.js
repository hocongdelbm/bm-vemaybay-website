/**
 * EC_TongHop — UAT Dashboard Controller v2.0
 * 
 * Calls the REST API from each website's wp-json/uat/v1 endpoints
 * to display a unified analytics dashboard.
 */
$(document).ready(function () {
    // ── State ───────────────────────────────────────────────
    const domainConfig = window.EC_DOMAIN_CONFIG || {};
    let currentSiteKey = Object.keys(domainConfig)[0] || '';
    let currentDate = '';
    let geoMapInstance = null;
    let geoMapPendingData = null;
    let isHeatmapLoaded = false;
    let hourlyChartInstance = null;
    let hourlyLiveCache = [];
    let hourlyHistoryCache = [];
    let currentHourlyChartType = 'line';
    let currentHourlyMode = 'live';

    // ── Init ────────────────────────────────────────────────
    init();

    function init() {
        bindEvents();
        if (currentSiteKey) {
            loadDashboard(currentSiteKey);
        }
    }

    // ── Event Bindings ──────────────────────────────────────
    function bindEvents() {
        // Site selector
        $('#ec_site_select').on('change', function () {
            currentSiteKey = $(this).val();
            currentDate = ''; // reset date selection
            isHeatmapLoaded = false; // reset heatmap state
            $('#ec_heatmap_tbody').html('<tr><td colspan="5" style="text-align:center; padding: 40px; color:#94a3b8;">Đang làm mới dữ liệu...</td></tr>');
            loadDashboard(currentSiteKey);

            // Nếu người dùng đang đứng xem tab heatmap thì tự động nạp luôn phiên mới
            if ($('.uat-tab[data-tab="heatmap"]').hasClass('active')) {
                loadHeatmapData();
            }
        });

        // Date selector
        $('#ec_date_select').on('change', function () {
            currentDate = $(this).val();
            loadDashboardData(currentSiteKey, currentDate);
        });

        // Tab switching
        $(document).on('click', '.uat-tab', function () {
            const tab = $(this).data('tab');
            $('.uat-tab').removeClass('active');
            $(this).addClass('active');
            $('.uat-tab-content').removeClass('active');
            $(`#uat-tab-${tab}`).addClass('active');

            // Handle map when overview tab becomes visible
            if (tab === 'overview') {
                if (geoMapPendingData) {
                    renderGeoMap(geoMapPendingData);
                } else if (geoMapInstance) {
                    setTimeout(function () {
                        geoMapInstance.updateSize();
                    }, 100);
                }
            }
        });

        // Scraping search
        $('#ec_scraping_search').on('input', function () {
            const query = $(this).val().toLowerCase();
            $('#ec_scraping_tbody tr').each(function () {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(query));
            });
        });

        // Toggle more times (Scraping)
        $(document).on('click', '.uat-show-more-times', function () {
            const $grid = $(this).closest('.uat-time-grid');
            const $extra = $grid.find('.uat-extra-time');

            if ($extra.is(':visible')) {
                $extra.hide();
                $(this).text('+' + $extra.length + ' more').css({
                    'background': '#f8fafc',
                    'color': '#3b82f6',
                    'border': '1px dashed #cbd5e1'
                });
            } else {
                $extra.fadeIn('fast', function () {
                    $(this).css('display', '');
                });
                $(this).html('&#8593; Thu gọn').css({
                    'background': '#fee2e2',
                    'color': '#ef4444',
                    'border': '1px dashed #fca5a5'
                });
            }
        });

        // Area Analytics — expand/collapse route table
        const AREA_SVG_DOWN = '<svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';
        const AREA_SVG_UP = '<svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>';

        $(document).on('click', '.uat-area-toggle-btn', function () {
            const $btn = $(this);
            const zone = $btn.data('zone');
            const color = $btn.data('color');
            const expanded = $btn.data('expanded') === 1 || $btn.data('expanded') === '1';
            const $tbody = $(`#ec_area_${zone}_tbody`);
            const LIMIT = 5;

            if (expanded) {
                // Collapse: hide rows beyond limit
                $tbody.find('.uat-area-route-row').each(function () {
                    if (parseInt($(this).data('idx')) >= LIMIT) {
                        $(this).hide();
                    }
                });
                const hidden = $tbody.find('.uat-area-route-row:hidden').length;
                $btn.data('expanded', 0).html(`${AREA_SVG_DOWN} Xem thêm ${hidden} route`).css({ 'background': '#f8fafc', 'color': color });
            } else {
                // Expand: show all
                $tbody.find('.uat-area-route-row:hidden').show();
                $btn.data('expanded', 1).html(`${AREA_SVG_UP} Thu gọn`).css({ 'background': '#fef2f2', 'color': '#ef4444' });
            }
        });

        // Area Analytics IP List — Live Search
        $(document).on('input', '.uat-area-ip-search', function () {
            const query = $(this).val().toLowerCase();
            const zone = $(this).data('zone');
            const $tbody = $(`#ec_area_${zone}_ip_tbody`);

            $tbody.find('.uat-area-ip-row').each(function () {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(query));
            });
        });

        // Simple tables (Top Routes, Route Plans) — expand/collapse
        $(document).on('click', '.uat-simple-toggle-btn', function () {
            const _SVG_DOWN = '<svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';
            const _SVG_UP = '<svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>';

            const $btn = $(this);
            const $table = $btn.prev('table');
            const $tbody = $table.find('tbody');
            const expanded = $btn.data('expanded') === 1 || $btn.data('expanded') === '1';
            const LIMIT = 5;

            if (expanded) {
                // Collapse
                $tbody.find('.uat-simple-row').each(function () {
                    if (parseInt($(this).data('idx')) >= LIMIT) {
                        $(this).hide();
                    }
                });
                const hidden = $tbody.find('.uat-simple-row:hidden').length;
                $btn.data('expanded', 0).html(`${_SVG_DOWN} Xem thêm ${hidden} mục`).css({ 'background': '#f8fafc', 'color': '#3b82f6' });
            } else {
                // Expand
                $tbody.find('.uat-simple-row:hidden').show();
                $btn.data('expanded', 1).html(`${_SVG_UP} Thu gọn`).css({ 'background': '#fef2f2', 'color': '#ef4444' });
            }
        });

        // Copy IP
        $(document).on('click', '.uat-copy-ip', function (e) {
            e.preventDefault();
            const ip = $(this).data('ip');
            const $btn = $(this);
            const oldHtml = $btn.html();

            navigator.clipboard.writeText(ip).then(() => {
                $btn.html('<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>');
                setTimeout(() => {
                    $btn.html(oldHtml);
                }, 2000);
            });
        });

        // Hourly Traffic Toggles
        $('#ec_hourly_toggle_btn').on('click', function () {
            currentHourlyChartType = currentHourlyChartType === 'line' ? 'bar' : 'line';
            renderHourlyTraffic();
        });

        $('#ec_hourly_mode_btn').on('click', function () {
            currentHourlyMode = currentHourlyMode === 'live' ? 'history' : 'live';
            renderHourlyTraffic();
        });
    }

    // ── API Helper ──────────────────────────────────────────
    function getApiConf() {
        return domainConfig[currentSiteKey] || {};
    }

    function apiGet(endpoint, params) {
        const conf = getApiConf();
        const base = conf.api_base || '';
        if (!base) return $.Deferred().reject('No API base configured').promise();

        // Build headers - attach WordPress Application Password auth nếu có
        const headers = {
            'Accept': 'application/json',
        };
        if (conf.token) {
            headers['Authorization'] = 'Basic ' + conf.token;
        }

        return $.ajax({
            url: base + endpoint,
            method: 'GET',
            data: params || {},
            dataType: 'json',
            timeout: 30000,
            crossDomain: true,
            headers: headers,
        });
    }

    // ── Loading Control ─────────────────────────────────────
    function showLoading() {
        $('#ec_global_loading').fadeIn(200);
    }

    function hideLoading() {
        $('#ec_global_loading').fadeOut(200);
    }

    // ── Main Load Functions ─────────────────────────────────
    function loadDashboard(siteKey) {
        showLoading();

        // First fetch available dates
        // API trả về { dates: ['2026-03-21', ...] }
        apiGet('/dashboard/dates')
            .done(function (data) {
                const dates = Array.isArray(data) ? data : (data.dates || []);
                populateDateSelector(dates);
                // Load full data for latest date
                const latestDate = dates.length > 0 ? dates[0] : '';
                currentDate = latestDate;
                loadDashboardData(siteKey, currentDate);
            })
            .fail(function () {
                $('#ec_date_select').html('<option value="">Không có dữ liệu</option>');
                loadDashboardData(siteKey, '');
            });
    }

    function loadDashboardData(siteKey, date) {
        showLoading();
        $('.uat-error-state').remove();

        // Cập nhật nhãn ngày chọn lên tiêu đề
        const dateDisplay = date ? formatDateLabel(date) : 'tính của ngày hôm trước';
        $('#ec_selected_date_label').text(`(${dateDisplay})`);

        const params = date ? { date: date } : {};

        // Fetch all data in parallel
        // Live endpoint: không truyền date → API luôn lấy ngày hôm nay (current_time)
        $.when(
            apiGet('/dashboard', params),
            apiGet('/dashboard/bots', params),
            apiGet('/dashboard/areas', params),
            apiGet('/dashboard/hourly/live'),
            apiGet('/dashboard/hourly/history', params)
        ).then(function (dashRes, botsRes, areaRes, liveRes, histRes) {
            const dash = dashRes[0] || dashRes;
            const bots = botsRes[0] || botsRes;
            const areas = areaRes[0] || areaRes;

            renderOverview(dash.overview || {});
            renderFlights(dash.flights || {});
            renderElements(dash.elements || {});
            renderSuspicious(dash.suspicious || []);
            renderScraping(dash.scraping || []);
            renderAreaAnalytics(areas.data || {});
            renderBots(bots.data || bots);

            // Live trả về {date, data}, history trả về array trực tiếp
            const liveRaw = liveRes[0] || liveRes || {};
            hourlyLiveCache = Array.isArray(liveRaw) ? liveRaw : (liveRaw.data || []);
            hourlyHistoryCache = histRes[0] || histRes || [];
            renderHourlyTraffic();

            // Update live badge - dash.dates là array từ /dashboard endpoint
            const dashDates = Array.isArray(dash.dates) ? dash.dates : [];
            if (dashDates.length > 0 && currentDate === dashDates[0]) {
                $('#ec_live_badge').show();
            } else {
                $('#ec_live_badge').hide();
            }

            hideLoading();
        }).fail(function (xhr) {
            hideLoading();
            let msg = 'Không thể tải dữ liệu';
            if (xhr && xhr.status === 0) msg = 'Không kết nối được đến server. Kiểm tra CORS hoặc URL.';
            else if (xhr && xhr.status === 401) msg = 'Bạn không có quyền truy cập.';
            else if (xhr && xhr.status === 500) msg = 'Lỗi server. Vui lòng thử lại.';

            // Xoá trắng dữ liệu nếu có lỗi xảy ra để khỏi hiển thị fake data
            renderOverview({});
            renderFlights({});
            renderElements({});
            renderSuspicious([]);
            renderScraping([]);
            renderAreaAnalytics({});
            renderBots({});
            $('#ec_live_badge').hide();

            showError(msg);
        });
    }

    function showError(message) {
        $('.uat-error-state').remove();
        const html = `
            <div class="uat-error-state" style="text-align:center; padding:20px; background:#fff; border:1px solid #fee2e2; border-radius:12px; margin-bottom:20px;">
                <div class="error-icon" style="font-size:24px;">&#9888;</div>
                <div style="color:#ef4444; font-weight:600; margin-top:10px;">${message}</div>
                <div style="margin-top:8px; font-size:12px; color:#94a3b8;">
                    Website: ${domainConfig[currentSiteKey]?.label || currentSiteKey} | API: ${getApiConf().api_base || 'N/A'}
                </div>
            </div>`;
        $('#ec_tabs').after(html);
    }

    // ── Render: Area Analytics ───────────────────────────────
    let _areaDonutChart = null;

    function renderAreaAnalytics(apiData) {
        const normalizeRoutes = (arr) => (arr || []).map(r => ({ label: r.route || '', session_count: r.count || 0 }));
        const areas = {
            north: normalizeRoutes(apiData?.North?.routes),
            central: normalizeRoutes(apiData?.Central?.routes),
            south: normalizeRoutes(apiData?.South?.routes),
            other: normalizeRoutes(apiData?.Other?.routes)
        };

        const ipsList = {
            north: apiData?.North?.ips || [],
            central: apiData?.Central?.ips || [],
            south: apiData?.South?.ips || [],
            other: apiData?.Other?.ips || []
        };

        const zoneSess = {
            north: apiData?.North?.session_count || 0,
            central: apiData?.Central?.session_count || 0,
            south: apiData?.South?.session_count || 0,
            other: apiData?.Other?.session_count || 0
        };

        const totalSessions = Object.values(zoneSess).reduce((a, b) => a + b, 0) || 1;
        const colors = { north: '#3b82f6', central: '#f59e0b', south: '#10b981', other: '#e2e8f0' };

        // Total
        $('#ec_area_total_sessions').text(fmt(totalSessions));

        // ── Donut Chart ──────────────────────────────────────
        if (_areaDonutChart) { _areaDonutChart.destroy(); _areaDonutChart = null; }
        const donutCanvas = document.getElementById('ec_area_donut');
        if (donutCanvas) {
            _areaDonutChart = new Chart(donutCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Miền Bắc', 'Miền Trung', 'Miền Nam', 'Khác'],
                    datasets: [{
                        data: [zoneSess.north, zoneSess.central, zoneSess.south, zoneSess.other],
                        backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#e2e8f0'],
                        borderWidth: 3,
                        borderColor: '#fff',
                        hoverOffset: 12
                    }]
                },
                options: {
                    cutout: '72%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { size: 14, weight: '700' },
                            bodyFont: { size: 13 },
                            displayColors: true,
                            boxPadding: 6,
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${fmt(ctx.raw)} (${((ctx.raw / totalSessions) * 100).toFixed(1)}%)`
                            }
                        }
                    },
                    animation: { duration: 700 }
                },
                plugins: [{
                    id: 'centerText',
                    afterDatasetsDraw: (chart) => {
                        const { ctx, width, height } = chart;
                        ctx.save();

                        // Main number
                        ctx.font = '800 22px Inter, system-ui, sans-serif';
                        ctx.fillStyle = '#1e293b';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(fmt(totalSessions), width / 2, height / 2 - 5);

                        // Label "TOTAL"
                        ctx.font = '600 10px Inter, system-ui, sans-serif';
                        ctx.fillStyle = '#94a3b8';
                        ctx.fillText('TOTAL', width / 2, height / 2 + 15);

                        ctx.restore();
                    }
                }]
            });
        }

        // ── Stats Cards ──────────────────────────────────────
        ['north', 'central', 'south'].forEach(function (zone) {
            const routes = areas[zone].sort((a, b) => (b.session_count || 0) - (a.session_count || 0));
            const zSessions = zoneSess[zone];
            const zPct = ((zSessions / totalSessions) * 100).toFixed(1);
            const color = colors[zone];

            $(`#ec_area_${zone}_sessions`).text(fmt(zSessions));
            $(`#ec_area_${zone}_pct`).text(zPct + '%');
            $(`#ec_area_${zone}_routebadge`).text(routes.length + ' routes');

            // Full table with rank badge — default 5 rows
            const $tbody = $(`#ec_area_${zone}_tbody`);
            const $table = $tbody.closest('table');
            // Remove old toggle btn if any
            $table.next('.uat-area-toggle-btn').remove();
            $tbody.empty();

            if (routes.length === 0) {
                $tbody.html('<tr><td colspan="3" class="uat-empty-cell">Chưa có dữ liệu.</td></tr>');
            } else {
                const LIMIT = 5;
                routes.forEach(function (r, idx) {
                    const rankStyles = [
                        { bg: '#fef9c3', color: '#ca8a04' },
                        { bg: '#f1f5f9', color: '#64748b' },
                        { bg: '#fdf4ff', color: '#9333ea' }
                    ];
                    const rs = rankStyles[idx] || { bg: 'transparent', color: '#94a3b8' };
                    const $row = $(`
                        <tr class="uat-area-route-row" data-idx="${idx}" style="${idx >= LIMIT ? 'display:none;' : ''}">
                            <td><span style="font-weight:500; color:#334155;">${escH(r.label || 'N/A')}</span></td>
                            <td style="text-align:right;"><strong style="color:${color}">${fmt(r.session_count)}</strong></td>
                            <td style="text-align:right;"><span style="background:${rs.bg}; color:${rs.color}; padding:2px 7px; border-radius:10px; font-size:11px; font-weight:700;">#${idx + 1}</span></td>
                        </tr>`);
                    $tbody.append($row);
                });

                // Toggle button
                if (routes.length > LIMIT) {
                    const hidden = routes.length - LIMIT;
                    const $btn = $(`<button class="uat-area-toggle-btn" data-zone="${zone}" data-color="${color}" data-expanded="0" style="width:100%; padding:9px 0; background:#f8fafc; border:none; border-top:1px solid #e2e8f0; font-size:12px; font-weight:600; color:${color}; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:background 0.2s;">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        Xem thêm ${hidden} route
                    </button>`);
                    $table.after($btn);
                }
            }

            // Build IP Tables
            const ips = ipsList[zone];
            const $ipBody = $(`#ec_area_${zone}_ip_tbody`);
            const $ipTable = $ipBody.closest('table');
            $ipTable.next('.uat-area-ip-toggle-btn').remove();
            $ipBody.empty();

            if (ips.length === 0) {
                $ipBody.html('<tr><td colspan="2" class="uat-empty-cell">Chưa có dữ liệu.</td></tr>');
            } else {
                ips.forEach(function (ipRow, idx) {
                    const loc = (ipRow.city || ipRow.region || 'Unknown');
                    const $row = $(`
                        <tr class="uat-area-ip-row" data-idx="${idx}">
                            <td style="width:65%; vertical-align:top; border-bottom:1px solid #f1f5f9; padding:8px 12px;">
                                <div style="display:flex; align-items:flex-start; gap:6px;">
                                    <span style="font-weight:600; color:#334155; font-family:monospace; font-size:12px; word-break:break-all; line-height:1.4;">${escH(ipRow.ip || 'N/A')}</span>
                                    <a href="#" class="uat-copy-ip" data-ip="${escH(ipRow.ip)}" style="opacity:0.6; color:#64748b; margin-top:1px; flex-shrink:0; cursor:pointer;" title="Copy IP"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path></svg></a>
                                </div>
                            </td>
                            <td style="width:35%; text-align:right; vertical-align:top; border-bottom:1px solid #f1f5f9; padding:8px 12px;">
                                <span style="color:#64748b; font-size:11px; display:inline-block; word-break:break-word; line-height:1.4;">${escH(loc)}</span>
                            </td>
                        </tr>`);
                    $ipBody.append($row);
                });
            }
        });


        // ── Compare Bars ─────────────────────────────────────
        const $compare = $('#ec_area_compare_bars');
        $compare.empty();
        const zoneInfo = [
            { zone: 'north', label: 'Miền Bắc', color: '#3b82f6' },
            { zone: 'central', label: 'Miền Trung', color: '#f59e0b' },
            { zone: 'south', label: 'Miền Nam', color: '#10b981' }
        ];

        // Header row
        let headerHtml = `<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:10px;">`;
        zoneInfo.forEach(zi => {
            headerHtml += `<div style="font-size:11px; font-weight:700; color:${zi.color}; text-transform:uppercase; letter-spacing:0.06em; border-left:3px solid ${zi.color}; padding-left:8px;">${zi.label}</div>`;
        });
        headerHtml += `</div>`;
        $compare.append(headerHtml);

        // Sorted arrays per zone
        const sorted = {};
        zoneInfo.forEach(zi => {
            sorted[zi.zone] = areas[zi.zone].slice().sort((a, b) => (b.session_count || 0) - (a.session_count || 0));
        });

        const maxRank = 4;
        let hasRow = false;
        for (let rank = 0; rank < maxRank; rank++) {
            const cols = zoneInfo.map(zi => ({ ...zi, route: sorted[zi.zone][rank] || null, maxSess: (sorted[zi.zone][0] || {}).session_count || 1 }));
            if (!cols.some(c => c.route)) continue;
            hasRow = true;

            let rowHtml = `<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #f1f5f9;">`;
            cols.forEach(c => {
                if (!c.route) {
                    rowHtml += `<div style="font-size:12px; color:#cbd5e1; font-style:italic;">—</div>`;
                } else {
                    const barPct = Math.max(5, ((c.route.session_count / c.maxSess) * 100).toFixed(0));
                    rowHtml += `
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px; gap:4px;">
                                <span class="uat-table-truncate" style="font-size:12px; font-weight:600; color:#334155; max-width:120px;" title="${escH(c.route.label)}">${escH(c.route.label || '?')}</span>
                                <strong style="font-size:12px; color:${c.color}; white-space:nowrap;">${fmt(c.route.session_count)}</strong>
                            </div>
                            <div style="height:6px; background:#f1f5f9; border-radius:4px; overflow:hidden;">
                                <div style="height:100%; width:${barPct}%; background:${c.color}; border-radius:4px;"></div>
                            </div>
                        </div>`;
                }
            });
            rowHtml += `</div>`;
            $compare.append(rowHtml);
        }

        if (!hasRow) {
            $compare.append('<p style="color:#94a3b8; font-style:italic; font-size:13px;">Chưa có dữ liệu route để so sánh.</p>');
        }
    }

    // ── Date Selector ───────────────────────────────────────
    function populateDateSelector(dates) {
        const $sel = $('#ec_date_select');
        $sel.empty();

        // Đảm bảo luôn là array
        if (!Array.isArray(dates)) dates = [];

        if (dates.length === 0) {
            $sel.append('<option value="">No data</option>');
            return;
        }

        dates.forEach(function (d, i) {
            const label = formatDateLabel(d);
            const selected = (i === 0) ? 'selected' : '';
            $sel.append(`<option value="${d}" ${selected}>${label}</option>`);
        });
    }

    function formatDateLabel(dateStr) {
        try {
            const dt = new Date(dateStr + 'T00:00:00');
            return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        } catch (e) {
            return dateStr;
        }
    }

    // ── Render: Overview ────────────────────────────────────
    function renderOverview(ov) {
        const lt = ov.lifetime || {};
        $('#ec_total_users').text(fmt(lt.total_users));
        $('#ec_today_sessions').text(fmt(lt.today_sessions));
        $('#ec_total_bots').text(fmt(lt.total_bot_sessions));
        $('#ec_suspicious_users').text(fmt(lt.suspicious_users));
        $('#ec_susp_count').text(fmt(lt.suspicious_users));

        const dq = ov.daily_quality || {};
        renderRate('#ec_engage_rate', '#ec_engage_sess', dq.engagement_rate);
        renderRate('#ec_bounce_rate', '#ec_bounce_sess', dq.bounce_rate);
        renderRate('#ec_typing_rate', '#ec_typing_sess', dq.typing_rate);
        renderRate('#ec_fp_rate', '#ec_fp_sess', dq.fingerprint_coverage);

        // Top pages
        renderTopPages(ov.top_pages || []);

        // Geo map
        renderGeoMap(ov.top_countries || []);

        // Traffic sources
        renderProgressList('#ec_traffic_sources', ov.traffic_sources || [], 'bg-blue', 5);

        // Devices
        renderProgressList('#ec_devices', ov.devices || [], 'bg-purple', 5);

        // OS
        renderProgressList('#ec_os', ov.os || [], 'bg-yellow', 5);

        // Goals
        renderGoals(ov.goals || []);
    }

    function renderHourlyTraffic() {
        let canvas = document.getElementById('ec_hourly_chart');
        if (!canvas) return;

        if (hourlyChartInstance) {
            hourlyChartInstance.destroy();
            hourlyChartInstance = null;
        }

        const dataArr = currentHourlyMode === 'live' ? hourlyLiveCache : hourlyHistoryCache;
        const labels = [];
        const data = [];

        if (dataArr && dataArr.length > 0) {
            dataArr.forEach(item => {
                labels.push(item.hour);
                data.push(item.sessions);
            });
        }

        const ctx = canvas.getContext('2d');
        const color = currentHourlyMode === 'live' ? '#10b981' : '#8b5cf6';
        const bg = currentHourlyMode === 'live' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(139, 92, 246, 0.2)';

        hourlyChartInstance = new Chart(ctx, {
            type: currentHourlyChartType,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sessions',
                    data: data,
                    borderColor: color,
                    backgroundColor: currentHourlyChartType === 'bar' ? color : bg,
                    borderWidth: 2,
                    fill: currentHourlyChartType === 'line',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        if (currentHourlyChartType === 'line') {
            $('#ec_hourly_toggle_btn').text('Đổi sang Bar Chart');
        } else {
            $('#ec_hourly_toggle_btn').text('Đổi sang Line Chart');
        }

        if (currentHourlyMode === 'live') {
            $('#ec_hourly_mode_btn').text('Xem dữ liệu Lịch sử');
            $('#ec_hourly_mode_btn').css({ 'background': '#d1fae5', 'color': '#047857', 'border': '1px solid #a7f3d0' });
        } else {
            $('#ec_hourly_mode_btn').text('Xem dữ liệu Trực tiếp');
            $('#ec_hourly_mode_btn').css({ 'background': '#e0e7ff', 'color': '#4338ca', 'border': '1px solid #c7d2fe' });
        }
    }

    function renderRate(valSel, sessSel, rateObj) {
        if (!rateObj) {
            $(valSel).text('—');
            $(sessSel).text('0 sess');
            return;
        }
        const pct = (rateObj.value * 100).toFixed(1);
        $(valSel).text(pct + '%');
        $(sessSel).text(fmt(rateObj.session_count) + ' sess');
    }

    function renderTopPages(pages) {
        const $tbody = $('#ec_top_pages_tbody');
        $tbody.empty();

        if (!pages || pages.length === 0) {
            $tbody.html('<tr><td colspan="3" class="uat-empty-cell">Chưa có dữ liệu trang nào.</td></tr>');
            return;
        }

        pages.slice(0, 7).forEach(function (p) {
            $tbody.append(`
                <tr>
                    <td><div class="uat-table-truncate" style="color:#3b82f6;" title="${escH(p.label)}">/${escH(p.label)}</div></td>
                    <td style="text-align:right">${fmt(p.session_count)}</td>
                    <td style="text-align:right; color:#64748b">${(p.value * 100).toFixed(1)}%</td>
                </tr>`);
        });
    }

    function renderGeoMap(countries) {
        // Prevent map crashing from rendering in a display:none container
        if ($('#uat-tab-overview').is(':hidden')) {
            geoMapPendingData = countries;
            return;
        }
        geoMapPendingData = null;

        let container = document.getElementById('ec_geo_map');
        if (!container) return;

        // Destroy existing map
        if (geoMapInstance) {
            try { geoMapInstance.destroy(); } catch (e) { /* ignore */ }
            geoMapInstance = null;
        }

        // Hard reset container to remove any corrupted bound events that cause freezing
        const cleanContainer = document.createElement('div');
        cleanContainer.id = container.id;
        cleanContainer.className = container.className;
        cleanContainer.style.cssText = container.style.cssText;
        container.parentNode.replaceChild(cleanContainer, container);
        container = cleanContainer;

        // Remove ghost tooltips injected into body
        $('.jvm-tooltip').remove();

        if (!countries || countries.length === 0) {
            container.innerHTML = '<p style="color:#94a3b8; font-style:italic; font-size:13px; text-align:center; padding:60px 0;">No location data available.</p>';
            return;
        }

        const countryData = {};
        countries.forEach(function (c) {
            if (c.label) {
                countryData[c.label.toUpperCase()] = c.session_count || 0;
            }
        });

        try {
            geoMapInstance = new jsVectorMap({
                selector: '#ec_geo_map',
                map: 'world',
                zoomOnScroll: true,
                zoomButtons: true,
                regionStyle: {
                    initial: { fill: '#e2e8f0', stroke: 'none', "stroke-width": 0 },
                    hover: { "fill-opacity": 0.8, cursor: 'pointer' }
                },
                series: {
                    regions: [{
                        values: countryData,
                        scale: ['#a7f3d0', '#10b981'],
                        normalizeFunction: 'polynomial'
                    }]
                },
                onRegionTooltipShow: function (event, tooltip, code) {
                    const count = countryData[code] || 0;
                    tooltip.text(tooltip.text() + ' - Sessions: ' + count);
                }
            });
        } catch (e) {
            container.innerHTML = '<p style="color:#94a3b8; font-style:italic; font-size:13px; text-align:center; padding:60px 0;">Map not available.</p>';
        }
    }

    function renderProgressList(selector, items, colorClass, limit, labelMap) {
        const $el = $(selector);
        $el.empty();

        if (!items || items.length === 0) {
            $el.html('<p style="color:#94a3b8; font-style:italic; font-size:13px">Chưa có dữ liệu.</p>');
            return;
        }

        items.slice(0, limit || 5).forEach(function (item) {
            const pct = (item.value * 100).toFixed(1);
            const label = item.label ? item.label.replace(/_/g, ' ') : 'Unknown';
            let extra = '';
            if (labelMap && item.label) {
                const key = item.label.toLowerCase().trim();
                if (labelMap[key]) {
                    extra = ` <span style="font-weight:400; color:#94a3b8; font-size:12px; margin-left:8px; text-transform:none;">${escH(labelMap[key])}</span>`;
                }
            }

            $el.append(`
                <div class="uat-progress-wrap">
                    <div class="uat-progress-header">
                        <div>
                            <span style="text-transform:capitalize;">${escH(label)}</span>${extra}
                        </div>
                        <strong>${pct}%</strong>
                    </div>
                    <div class="uat-progress-bar">
                        <div class="uat-progress-fill ${colorClass}" style="width:${item.value * 100}%"></div>
                    </div>
                </div>`);
        });
    }

    function renderGoals(goals) {
        const $tbody = $('#ec_goals_tbody');
        $tbody.empty();

        if (!goals || goals.length === 0) {
            $tbody.html('<tr><td colspan="2" class="uat-empty-cell">Chưa đạt conversion nào.</td></tr>');
            return;
        }

        goals.slice(0, 5).forEach(function (g) {
            const name = g.label || 'Unknown';
            const pct = (g.value * 100).toFixed(2);
            $tbody.append(`
                <tr>
                    <td><div class="uat-table-truncate" title="${escH(name)}">${escH(ucFirst(name.replace(/_/g, ' ')))}</div></td>
                    <td style="text-align:right"><span style="background:#ecfdf5; color:#059669; padding:4px 8px; border-radius:12px; font-weight:700; font-size:12px">${pct}%</span></td>
                </tr>`);
        });
    }

    // ── Render: Flights ─────────────────────────────────────
    function renderFlights(fl) {
        const s2d = fl.search_to_detail || {};
        $('#ec_s2d_rate').text(((s2d.value || 0) * 100).toFixed(1) + '%');
        $('#ec_s2d_sess').text(fmt(s2d.session_count) + ' sess');

        // Journey types
        const $jt = $('#ec_journey_types');
        $jt.empty();
        (fl.journey_types || []).forEach(function (jt) {
            $jt.append(`
                <div style="flex:1; min-width:120px;">
                    <div class="uat-progress-header">
                        <span style="text-transform:capitalize;">${escH(jt.label)}</span>
                        <strong>${(jt.value * 100).toFixed(1)}%</strong>
                    </div>
                    <div class="uat-progress-bar">
                        <div class="uat-progress-fill bg-blue" style="width:${jt.value * 100}%"></div>
                    </div>
                </div>`);
        });
        if ((fl.journey_types || []).length === 0) {
            $jt.html('<p style="color:#94a3b8; font-style:italic; font-size:13px">Chưa có dữ liệu.</p>');
        }

        // Routes
        renderSimpleTable('#ec_routes_tbody', fl.top_routes || [], 'event_count', 10,
            item => `<strong style="color:#3b82f6">${escH(item.label)}</strong>`);

        // Leadtime
        const leadtimeMap = {
            'fast': '(0 - 3 days)',
            'medium': '(4 - 7 days)',
            'low': '(8 - 14 days)',
            'plan': '(15 - 30 days)',
            'far': '(> 30 days)',
            'instant': '(Same day)'
        };
        renderProgressList('#ec_leadtime', fl.departure_leadtime || [], 'bg-green', 10, leadtimeMap);

        // Departure times
        renderProgressList('#ec_departure_times', fl.departure_times || [], 'bg-indigo', 10);

        // Route plans
        renderSimpleTable('#ec_route_plans_tbody', fl.route_plans || [], 'session_count', 10,
            item => `<span style="color:#475569">${escH(item.label)}</span>`);

        // Airlines
        renderProgressList('#ec_airlines', fl.airline_filters || [], 'bg-purple', 10);
    }

    function renderSimpleTable(tbodySelector, items, countField, limit, labelFn) {
        const $tbody = $(tbodySelector);
        const $table = $tbody.closest('table');
        $table.next('.uat-simple-toggle-btn').remove();
        $tbody.empty();

        if (!items || items.length === 0) {
            $tbody.html('<tr><td colspan="2" class="uat-empty-cell">Chưa có dữ liệu.</td></tr>');
            return;
        }

        const DISPLAY_LIMIT = 5;
        const totalItemsToRender = Math.max(limit || 50, items.length); // render all we've got or at least limit

        items.slice(0, totalItemsToRender).forEach(function (item, idx) {
            $tbody.append(`
                <tr class="uat-simple-row" data-idx="${idx}" style="${idx >= DISPLAY_LIMIT ? 'display:none;' : ''}">
                    <td>${labelFn(item)}</td>
                    <td style="text-align:right">${fmt(item[countField])}</td>
                </tr>`);
        });

        const totalRendered = Math.min(totalItemsToRender, items.length);
        if (totalRendered > DISPLAY_LIMIT) {
            const hidden = totalRendered - DISPLAY_LIMIT;
            const _SVG_DOWN = '<svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';
            const $btn = $(`<button class="uat-simple-toggle-btn" data-expanded="0" style="width:100%; padding:9px 0; background:#f8fafc; border:none; border-top:1px solid #e2e8f0; font-size:12px; font-weight:600; color:#3b82f6; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:background 0.2s;">
                ${_SVG_DOWN} Xem thêm ${hidden} mục
            </button>`);
            $table.after($btn);
        }
    }

    // ── Render: Elements ────────────────────────────────────
    function renderElements(el) {
        renderElementTable('#ec_clicks_tbody', el.top_clicks || []);
        renderElementTable('#ec_typing_tbody', el.top_typing || []);
        renderElementTable('#ec_scroll_tbody', el.top_scrolls || []);
    }

    function renderElementTable(tbodySelector, items) {
        const $tbody = $(tbodySelector);
        const $table = $tbody.closest('table');
        $tbody.empty();
        $table.next('.uat-simple-toggle-btn').remove(); // Clear previous button

        if (!items || items.length === 0) {
            $tbody.html('<tr><td colspan="3" class="uat-empty-cell">Chưa có dữ liệu.</td></tr>');
            return;
        }

        const DISPLAY_LIMIT = 5;
        items.forEach(function (item, i) {
            const isHidden = i >= DISPLAY_LIMIT;
            const rank = i + 1;
            const rankColor = rank === 1 ? '#f59e0b' : rank === 2 ? '#64748b' : rank === 3 ? '#b45309' : '#94a3b8';
            const rankBg = rank === 1 ? '#fef3c7' : rank === 2 ? '#f1f5f9' : rank === 3 ? '#fffbeb' : '#f8fafc';

            $tbody.append(`
                <tr class="uat-simple-row" data-idx="${i}" style="${isHidden ? 'display:none;' : ''}">
                    <td>
                        <div class="uat-table-truncate" style="font-weight:500; color:#334155;" title="${escH(item.label)}">
                            ${escH(item.label)}
                        </div>
                    </td>
                    <td style="text-align:right; color:#10b981; font-weight:700;">${fmt(item.event_count)}</td>
                    <td style="text-align:right; width:45px;">
                        <span style="font-size:10px; font-weight:700; background:${rankBg}; color:${rankColor}; padding:2px 6px; border-radius:10px;">#${rank}</span>
                    </td>
                </tr>`);
        });

        if (items.length > DISPLAY_LIMIT) {
            const hidden = items.length - DISPLAY_LIMIT;
            const _SVG_DOWN = '<svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';
            const $btn = $(`<button class="uat-simple-toggle-btn" data-expanded="0" style="width:100%; padding:9px 0; background:#f8fafc; border:none; border-top:1px solid #e2e8f0; font-size:12px; font-weight:600; color:#10b981; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:background 0.2s;">
                ${_SVG_DOWN} Xem thêm ${hidden} mục
            </button>`);
            $table.after($btn);
        }
    }

    // ── Render: Suspicious ──────────────────────────────────
    function renderSuspicious(items) {
        const $tbody = $('#ec_suspicious_tbody');
        $tbody.empty();
        $('#ec_susp_count').text(items.length);

        if (!items || items.length === 0) {
            $tbody.html('<tr><td colspan="5" class="uat-empty-cell">Không có người dùng đáng ngờ nào.</td></tr>');
            return;
        }

        items.forEach(function (su) {
            const scoreClass = su.suspicious_score >= 50 ? 'score-critical' :
                su.suspicious_score >= 30 ? 'score-high' :
                    su.suspicious_score >= 10 ? 'score-medium' : 'score-low';

            let reasonsHtml = '';
            if (Array.isArray(su.reasons) && su.reasons.length > 0) {
                reasonsHtml = `<div style="margin-top:8px; display:flex; flex-direction:column; gap:4px;">`;
                su.reasons.forEach(r => {
                    reasonsHtml += `<div style="font-size:11px; color:#b91c1c; background:#fef2f2; padding:4px 8px; border-radius:6px; border:1px solid #fca5a5; display:inline-block; width:fit-content;">${escH(r)}</div>`;
                });
                reasonsHtml += `</div>`;
            }

            $tbody.append(`
                <tr>
                    <td data-label="IP" style="padding-left:24px; vertical-align:top;">
                        <span style="display:flex; align-items:center; gap:8px;">
                            <svg style="width:16px; height:16px; color:#ef4444;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-1.998A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"></path></svg>
                            <strong style="color:#ef4444; font-size:14px;">${escH(su.ip || 'Unknown')}</strong>
                            <button class="uat-copy-ip" data-ip="${escH(su.ip)}" title="Copy IP" style="background:none; border:none; padding:2px; cursor:pointer; color:#64748b; display:flex; align-items:center; outline:none;">
                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            </button>
                        </span>
                        ${reasonsHtml}
                    </td>
                    <td data-label="User ID" style="vertical-align:top;"><code style="font-size:11px; background:#f1f5f9; padding:4px 8px; border-radius:6px; color:#475569; border:1px solid #e2e8f0;">${escH(su.uid)}</code></td>
                    <td data-label="Score" style="vertical-align:top;"><span class="${scoreClass}" style="padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px">${(su.suspicious_score || 0).toFixed(1)} PTS</span></td>
                    <td data-label="Last Seen" style="vertical-align:top; color:#64748b;">${formatDate(su.last_seen)}</td>
                    <td data-label="Connections" style="vertical-align:top; font-weight:600; color:#334155;">${fmt(su.total_sessions)}</td>
                </tr>`);
        });
    }

    // ── Render: Scraping ────────────────────────────────────
    function renderScraping(items) {
        const $tbody = $('#ec_scraping_tbody');
        $tbody.empty();
        $('#ec_scraping_count').text(items.length);
        $('#ec_scraping_count2').text(items.length);

        if (!items || items.length === 0) {
            $tbody.html('<tr><td colspan="4" style="text-align:center; color:#10b981; padding:40px 0; font-weight:500;">No route scraping behavior detected.</td></tr>');
            return;
        }

        items.forEach(function (cb) {
            const fwdHtml = renderRouteCol(cb.forward || {}, true);
            const revHtml = renderRouteCol(cb.reverse || {}, false);

            $tbody.append(`
                <tr class="uat-scraping-row">
                    <td data-label="IP Address">
                        <div class="uat-entity-info">
                            <span class="entity-icon" style="display:flex;align-items:center;justify-content:center;width:20px;height:20px;">
                                <svg viewBox="0 0 20 20" fill="#dc2626" width="18" height="18"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-1.998A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"></path></svg>
                            </span>
                        </div>
                        <span class="ip_value" style="display:flex; align-items: center; gap: 8px; justify-content: flex-start;">
                            <strong>${escH(cb.ip)}</strong>
                            <button class="uat-copy-ip" data-ip="${escH(cb.ip)}" title="Copy IP" style="background:none; border:none; padding:4px; cursor:pointer; color:#64748b; display:flex; align-items:center; transition:color 0.2s; outline:none;">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            </button>
                        </span>
                    </td>
                    <td data-label="Threat Score">
                        <span class="uat-threat-badge">${fmt(cb.threat_score)} <small style="font-size:10px;">PTS</small></span>
                    </td>
                    <td data-label="Sessions">
                        <span class="uat-session-count">${fmt(cb.session_count)}</span>
                    </td>
                    <td data-label="Activity Log" style="padding:0;">
                        <div class="uat-combined-route-view">
                            ${fwdHtml}
                            <div class="uat-route-divider"></div>
                            ${revHtml}
                        </div>
                    </td>
                </tr>`);
        });
    }

    function renderRouteCol(data, isFwd) {
        const route = data.route || '';
        const count = data.count || 0;
        const times = data.times || [];

        if (count === 0 || route === '') {
            return `<div class="uat-empty-route-state">
                        <span>No ${isFwd ? 'fwd' : 'rev'} data</span>
                    </div>`;
        }

        const colorClass = isFwd ? 'uat-route-fwd' : 'uat-route-rev';
        const iconSvg = isFwd
            ? '<svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>'
            : '<svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>';
        const label = isFwd ? 'FORWARD' : 'REVERSE';

        let timesHtml = '<div class="uat-time-grid">';
        const validTimes = times.map(t => t.trim()).filter(t => t !== '');
        validTimes.forEach(function (t, i) {
            if (i < 6) {
                timesHtml += `<span class="uat-time-badge" title="${escH(t)}">${escH(t)}</span>`;
            } else {
                timesHtml += `<span class="uat-time-badge uat-extra-time" style="display:none;" title="${escH(t)}">${escH(t)}</span>`;
            }
        });

        if (validTimes.length > 6) {
            const hiddenCount = validTimes.length - 6;
            timesHtml += `<span class="uat-time-badge uat-show-more-times" style="cursor:pointer; font-weight:700; background:#f8fafc; color:#3b82f6; border: 1px dashed #cbd5e1; text-align:center;" title="Xem thêm">+${hiddenCount} more</span>`;
        }

        timesHtml += '</div>';

        return `
            <div class="uat-route-container ${colorClass}">
                <div class="uat-route-header">
                    <div class="uat-route-path">
                        <span class="route-icon" style="display:flex;align-items:center;">${iconSvg}</span>
                        <strong>${escH(route)}</strong>
                        <span class="uat-route-label">${label}</span>
                    </div>
                    <div class="uat-route-count">
                        <strong>${count}</strong> times
                    </div>
                </div>
                ${timesHtml}
            </div>`;
    }

    // ── Render: Bots ────────────────────────────────────────
    function renderBots(data) {
        if (!data) data = {};

        renderBotList('#ec_safe_bots_list', data.safe || [], '#ec_safe_bot_count');
        renderBotList('#ec_sus_bots_list', data.suspicious || [], '#ec_sus_bot_count');
        renderBotList('#ec_danger_bots_list', data.danger || [], '#ec_danger_bot_count');
    }

    function renderBotList(selector, items, countSelector) {
        const $el = $(selector);
        $el.empty();
        $(countSelector).text(items.length);

        if (!items || items.length === 0) {
            $el.html('<p style="color:#94a3b8; font-style:italic; font-size:13px; padding:20px 0; text-align:center;">Không phát hiện.</p>');
            return;
        }

        items.forEach(function (bot) {
            $el.append(`
                <div class="uat-bot-item">
                    <span>${escH(bot)}</span>
                </div>`);
        });
    }

    // ── Utilities ───────────────────────────────────────────
    function fmt(n) {
        if (n === null || n === undefined) return '—';
        return Number(n).toLocaleString('en-US');
    }

    function escH(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function ucFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '—';
        try {
            const dt = new Date(dateStr.replace(' ', 'T'));
            return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
        } catch (e) {
            return dateStr;
        }
    }

    // ── Heatmap Feature ─────────────────────────────────────────
    $('.uat-tab[data-tab="heatmap"]').on('click', function () {
        if (!isHeatmapLoaded && currentSiteKey) {
            loadHeatmapData();
        }
    });

    $(document).on('input', '#ec_heatmap_search', function () {
        const query = $(this).val().toLowerCase();
        $('#ec_heatmap_tbody tr').each(function () {
            const hasData = $(this).find('td').length > 1; // skip empty/loading rows
            if (!hasData) return;
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(query));
        });
    });

    function loadHeatmapData() {
        if (!currentSiteKey) return;
        const $tbody = $('#ec_heatmap_tbody');
        $tbody.html('<tr><td colspan="5" style="text-align:center; padding: 60px; color:#94a3b8;"><div class="uat-loading-spinner" style="border-top-color:#f72585; width:30px; height:30px; margin: 0 auto;"></div></td></tr>');

        apiGet('/heatmap').done(function (res) {
            isHeatmapLoaded = true;
            renderHeatmapUI(res.pages || [], res.view_token);
            $('#ec_heatmap_count').text(res.total || 0);
        }).fail(function () {
            $tbody.html('<tr><td colspan="5" style="color:#ef4444; padding:20px; text-align:center; background:#fee2e2;">Lỗi tải dữ liệu heatmap từ API.</td></tr>');
        });
    }

    function renderHeatmapUI(pages, viewToken) {
        const $tbody = $('#ec_heatmap_tbody');
        $tbody.empty();

        if (!pages || pages.length === 0) {
            $tbody.html(`
                <tr>
                    <td colspan="5" style="text-align:center; padding:60px 20px;">
                        <h3 style="margin:0; color:#94a3b8;">Chưa có dữ liệu heatmap</h3>
                        <p style="margin-top:5px; font-size:13px; color:#64748b;">Dữ liệu sẽ xuất hiện tự động khi có thao tác người dùng.</p>
                    </td>
                </tr>
            `);
            return;
        }

        pages.forEach(function (p) {
            const url = p.url || '';
            let relativePath = url.replace(/^(?:\/\/|[^/]+)*\//, '/');
            if (!relativePath) relativePath = '/';
            let viewUrl = url + (url.indexOf('?') > -1 ? '&' : '?') + 'uat_heatmap=1';
            if (viewToken) viewUrl += '&uat_token=' + viewToken;
            const timeAgo = formatDate(p.last_click);

            const html = `
                <tr>
                    <td data-label="Target Page">
                        <div class="uat-page-identity" style="display:flex; flex-direction:column; gap:4px;">
                            <a href="${escH(viewUrl)}" target="_blank" class="uat-page-link" style="color:#f72585; font-weight:600; text-decoration:none;">
                                ${escH(relativePath)}
                            </a>
                            <div class="uat-full-url" style="font-size:11px; color:#94a3b8; max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                ${escH(url)}
                            </div>
                        </div>
                    </td>
                    <td data-label="Total Engagement">
                        <span class="badge badge-blue uat-engagement-badge" style="background:#eff6ff; color:#3b82f6; padding:6px 12px; border-radius:12px; font-weight:bold; font-size:12px;">
                            ${fmt(p.click_count)} clicks
                        </span>
                    </td>
                    <td data-label="Device Affinity">
                        <div class="uat-device-split" style="width:140px;">
                            <div class="uat-device-stats" style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:6px; font-weight:600;">
                                <span title="Desktop: ${p.desktop_clicks} clicks" style="color:#3b82f6; display:flex; align-items:center; gap:4px;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                    ${p.desktop_pct}%
                                </span>
                                <span title="Mobile: ${p.mobile_clicks} clicks" style="color:#f72585; display:flex; align-items:center; gap:4px;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                    ${p.mobile_pct}%
                                </span>
                            </div>
                            <div class="uat-multi-progress" style="display:flex; height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                                <div style="width:${p.desktop_pct}%; background:#3b82f6;"></div>
                                <div style="width:${p.mobile_pct}%; background:#f72585;"></div>
                                <div style="width:${100 - p.desktop_pct - p.mobile_pct}%; background:#cbd5e1;"></div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Last Interaction">
                        <span style="font-size:12px; color:#64748b;">${timeAgo}</span>
                    </td>
                    <td data-label="Actions" class="text-right">
                        <a href="${escH(viewUrl)}" target="_blank" class="uat-btn uat-btn-sm" style="display:inline-block; padding: 6px 16px; font-size: 12px; background: #f72585; color:white; font-weight:600; border-radius: 6px; box-shadow: 0 2px 4px rgba(247, 37, 133, 0.3); text-decoration:none; transition:0.2s;">
                            View
                        </a>
                    </td>
                </tr>
            `;
            $tbody.append(html);
        });
    }
});
