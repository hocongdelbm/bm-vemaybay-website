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
    let currentHourlyCompare = false; // so sánh ngày chọn vs hôm nay
    let peakHoursChartInstance = null; // chart giờ cao điểm

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

            if ($('.uat-tab[data-tab="ip_manage"]').hasClass('active')) {
                loadIpManage();
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

            if (tab === 'ip_manage') {
                loadIpManage();
            }

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

        // City Distribution progressive expand/collapse
        $(document).on('click', '.uat-city-toggle-btn', function () {
            const _SVG_DOWN = '<svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';
            const _SVG_UP = '<svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>';

            const $btn = $(this);
            const $table = $btn.prev('table');
            const $tbody = $table.find('tbody');
            const expanded = $btn.data('expanded') === 1 || $btn.data('expanded') === '1';
            const LIMIT = 5;

            if (expanded) {
                // Collapse all but first 5
                $tbody.find('.uat-simple-row').each(function () {
                    if (parseInt($(this).data('idx')) >= LIMIT) {
                        $(this).hide();
                    }
                });
                const hidden = $tbody.find('.uat-simple-row:hidden').length;
                const toShow = hidden > LIMIT ? LIMIT : hidden;
                const remainText = hidden > toShow ? ` (còn ${hidden})` : '';
                $btn.data('expanded', 0)
                    .html(`${_SVG_DOWN} <span style="vertical-align:middle;">Xem thêm ${toShow} mục${remainText}</span>`)
                    .css({ 'background': '#f8fafc', 'color': '#3b82f6' });
            } else {
                // Progressive expand by LIMIT
                const $hiddenRows = $tbody.find('.uat-simple-row:hidden');
                $hiddenRows.slice(0, LIMIT).show();

                const remainingHidden = $tbody.find('.uat-simple-row:hidden').length;
                if (remainingHidden <= 0) {
                    $btn.data('expanded', 1)
                        .html(`${_SVG_UP} <span style="vertical-align:middle;">Thu gọn</span>`)
                        .css({ 'background': '#fef2f2', 'color': '#ef4444' });
                } else {
                    const toShow = remainingHidden > LIMIT ? LIMIT : remainingHidden;
                    const remainText = remainingHidden > toShow ? ` (còn ${remainingHidden})` : '';
                    $btn.data('expanded', 0)
                        .html(`${_SVG_DOWN} <span style="vertical-align:middle;">Xem thêm ${toShow} mục${remainText}</span>`)
                        .css({ 'background': '#f8fafc', 'color': '#3b82f6' });
                }
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
            // Reset so sánh khi chuyển mode
            currentHourlyCompare = false;
            renderHourlyTraffic();
        });

        $('#ec_hourly_compare_btn').on('click', function () {
            // Chỉ hoạt động khi đang ở mode history
            if (currentHourlyMode !== 'history') return;
            currentHourlyCompare = !currentHourlyCompare;
            renderHourlyTraffic();
        });

        // dynamic sort
        $(document).on('click', '.uat-sortable-th', function () {
            const key = $(this).data('sort');
            if (!key) return;
            if (_citySortKey === key) {
                _citySortDir = _citySortDir === 'desc' ? 'asc' : 'desc';
            } else {
                _citySortKey = key;
                _citySortDir = 'desc';
            }
            _updateCitySortHeaders();
            _renderCityRows();
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

    function callPageApi(params) {
        var payload = $.extend({ site_key: currentSiteKey }, params);
        return $.ajax({
            url: 'index.php?entryPoint=entryPointIpManage',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            dataType: 'json',
            timeout: 15000,
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
        $.when(
            apiGet('/dashboard', params),
            apiGet('/dashboard/bots', params),
            apiGet('/dashboard/areas', params),
            apiGet('/dashboard/hourly/live'),
            apiGet('/dashboard/hourly/history', params),
            apiGet('/dashboard/city', params),
            apiGet('/dashboard/passenger-typing', params)
        ).then(function (dashRes, botsRes, areaRes, liveRes, histRes, cityRes, typingRes) {
            const dash = dashRes[0] || dashRes;
            const bots = botsRes[0] || botsRes;
            const areas = areaRes[0] || areaRes;
            const cities = cityRes ? (cityRes[0] || cityRes) : null;
            const typingData = typingRes ? (typingRes[0] || typingRes) : null;

            renderOverview(dash.overview || {});
            renderFlights(dash.flights || {});
            renderElements(dash.elements || {});
            renderSuspicious(dash.suspicious || []);
            renderScraping(dash.scraping || []);
            renderAreaAnalytics(areas.data || {});
            if (cities && cities.data && cities.data.length > 0) {
                // Thu thập cấu trúc mảng IPs theo thành phố
                let cityIPsPayload = {
                    from_date: date ? date : '',
                    to_date: date ? date : '',
                    site_domain: currentSiteKey,
                    cities: {}
                };

                cities.data.forEach(function (c) {
                    if (c['ip-list'] && c['ip-list'].length > 0) {
                        cityIPsPayload.cities[c.city] = c['ip-list'];
                    }
                });

                //loading
                $('#ec_area_city_tbody').html('<tr><td colspan="8" style="text-align:center; padding:40px; color:#94a3b8; font-weight:500;">' +
                    '<svg style="display:inline-block; animation:spin 1s linear infinite; margin-right:8px; vertical-align:middle; width:20px; height:20px; color:#3b82f6;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>' +
                    '<span style="vertical-align:middle;">Đang đồng bộ dữ liệu Booking CRM...</span></td></tr>');

                // Hiện totals bar với placeholder trong khi chờ booking stats
                $('#ec_city_totals_bar').css('display', 'flex');
                $('#ec_city_total_thamkhao').text('—');
                $('#ec_city_total_booking').text('—');
                $('#ec_city_total_hoantat').text('—');

                $.ajax({
                    url: 'index.php?entryPoint=entryPointBookingStats',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(cityIPsPayload),
                    success: function (res) {
                        try {
                            const parsed = typeof res === 'string' ? JSON.parse(res) : res;
                            if (parsed && parsed.status === 'success' && parsed.data) {
                                cities.data.forEach(function (c) {
                                    if (parsed.data[c.city]) {
                                        c.suite_stats = parsed.data[c.city];
                                    } else {
                                        c.suite_stats = { ThamKhao: 0, Booking: 0, HoanTat: 0 };
                                    }
                                });
                            }
                        } catch (e) { }
                        renderCityDistribution(cities || {});
                    },
                    error: function () {
                        renderCityDistribution(cities || {});
                    }
                });
            } else {
                renderCityDistribution(cities || {});
            }
            if (typeof renderPassengerTyping === 'function') renderPassengerTyping(typingData || {});
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

    // ── Render: City / Province Distribution ────────────────────────
    let _cityDataCache = [];
    let _citySortKey = 'sessions';
    let _citySortDir = 'desc';

    function renderCityDistribution(cityData) {
        _cityDataCache = (cityData.data || []).map(function (r) {
            const suiteStats = r.suite_stats || { ThamKhao: 0, Booking: 0, HoanTat: 0 };
            return {
                city: r.city || 'Chưa xác định',
                sessions: r.sessions || 0,
                flight_search: r.flight_search_count || 0,
                pct: r.pct || 0,
                thamkhao: suiteStats.ThamKhao || 0,
                booking: suiteStats.Booking || 0,
                hoantat: suiteStats.HoanTat || 0,
                _raw: r
            };
        });

        // Tính totals
        let totalThamKhao = 0, totalBooking = 0, totalHoanTat = 0;
        _cityDataCache.forEach(function (r) {
            totalThamKhao += r.thamkhao;
            totalBooking += r.booking;
            totalHoanTat += r.hoantat;
        });

        if (_cityDataCache.length > 0) {
            $('#ec_city_totals_bar').css('display', 'flex');
            $('#ec_city_total_thamkhao').text(fmt(totalThamKhao));
            $('#ec_city_total_booking').text(fmt(totalBooking));
            $('#ec_city_total_hoantat').text(fmt(totalHoanTat));
        } else {
            $('#ec_city_totals_bar').hide();
        }

        // Reset sort state về mặc định khi load data mới
        _citySortKey = 'sessions';
        _citySortDir = 'desc';
        _updateCitySortHeaders();
        _renderCityRows();
    }

    function _updateCitySortHeaders() {
        $('.uat-sortable-th').each(function () {
            const key = $(this).data('sort');
            const $icon = $(this).find('.sort-icon');
            if (key === _citySortKey) {
                $icon.text(_citySortDir === 'desc' ? '↓' : '↑');
                $(this).css('color', '#0ea5e9');
            } else {
                $icon.text('↕');
                $(this).css('color', '');
            }
        });
    }

    function _renderCityRows() {
        const $tbody = $('#ec_area_city_tbody');
        const $table = $tbody.closest('table');
        const $btn = $table.nextAll('.uat-city-toggle-btn').first();
        $tbody.empty();

        if (_cityDataCache.length === 0) {
            $tbody.html('<tr><td colspan="8" class="uat-empty-cell">Chưa có dữ liệu phân bổ theo tỉnh thành khu vực.</td></tr>');
            $btn.hide();
            return;
        }

        // Sort
        const sorted = _cityDataCache.slice().sort(function (a, b) {
            const va = a[_citySortKey] || 0;
            const vb = b[_citySortKey] || 0;
            return _citySortDir === 'desc' ? vb - va : va - vb;
        });

        const LIMIT = 5;
        const cityColor = '#0ea5e9';
        sorted.forEach(function (r, idx) {
            const rankStyles = [
                { bg: '#fef9c3', color: '#ca8a04' },
                { bg: '#f1f5f9', color: '#64748b' },
                { bg: '#fdf4ff', color: '#9333ea' }
            ];
            const rs = rankStyles[idx] || { bg: 'transparent', color: '#94a3b8' };
            const isHidden = idx >= LIMIT ? 'display:none;' : '';
            const barPct = Math.max(1, r.pct).toFixed(1);
            const pctText = r.pct.toFixed(1) + '%';

            $tbody.append(`
                <tr class="uat-simple-row" data-idx="${idx}" style="${isHidden}">
                    <td>
                        <div style="font-weight:600; color:#334155; margin-bottom:5px; font-size:13px;">${escH(r.city)}</div>
                        <div style="height:4px; background:#f1f5f9; border-radius:4px; overflow:hidden;">
                            <div style="height:100%; width:${barPct}%; background:${cityColor}; border-radius:4px;"></div>
                        </div>
                    </td>
                    <td style="text-align:right; vertical-align:middle;"><strong style="color:${cityColor}">${fmt(r.sessions)}</strong></td>
                    <td style="text-align:right; vertical-align:middle;"><strong>${fmt(r.flight_search)}</strong></td>
                    <td style="text-align:right; vertical-align:middle;"><strong>${fmt(r.thamkhao)}</strong></td>
                    <td style="text-align:right; vertical-align:middle;"><strong style="color:#1d4ed8;">${fmt(r.booking)}</strong></td>
                    <td style="text-align:right; vertical-align:middle;"><strong style="color:#10b981;">${fmt(r.hoantat)}</strong></td>
                    <td style="text-align:right; vertical-align:middle;"><span style="color:#64748b; font-size:12px; font-weight:600;">${pctText}</span></td>
                    <td style="text-align:right; vertical-align:middle;"><span style="background:${rs.bg}; color:${rs.color}; padding:2px 7px; border-radius:10px; font-size:11px; font-weight:700;">#${idx + 1}</span></td>
                </tr>
            `);
        });

        if (sorted.length <= LIMIT) {
            $btn.hide();
        } else {
            const hidden = sorted.length - LIMIT;
            const toShow = hidden > LIMIT ? LIMIT : hidden;
            const remainText = hidden > toShow ? ` (còn ${hidden})` : '';
            $btn.show().data('expanded', 0)
                .html(`<svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14" style="vertical-align:middle; margin-right:4px; margin-top:-2px;"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg> <span style="vertical-align:middle;">Xem thêm ${toShow} mục${remainText}</span>`);
        }
    }

    // ── Render: Passenger Typing ────────────────────────────
    let _typingDonutChart = null;
    function renderPassengerTyping(data) {
        const summary = data.summary || {};
        const logs = data.typing_logs || [];

        const totalViewers = summary.total_viewers || 0;
        const typingSessions = summary.typing_sessions || 0;
        const nonTypingSessions = summary.non_typing_sessions || 0;
        const typingPct = summary.typing_pct || 0;
        const nonTypingPct = summary.non_typing_pct || 0;

        $('#ec_typing_total_viewers').text(fmt(totalViewers));
        $('#ec_typing_sess_count').text(fmt(typingSessions));

        // Donut Chart
        if (_typingDonutChart) { _typingDonutChart.destroy(); _typingDonutChart = null; }
        const donutCanvas = document.getElementById('ec_typing_donut');
        if (donutCanvas) {
            _typingDonutChart = new Chart(donutCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Có nhập liệu', 'Không nhập liệu'],
                    datasets: [{
                        data: [typingSessions, nonTypingSessions],
                        backgroundColor: ['#f43f5e', '#f1f5f9'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    cutout: '75%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${fmt(ctx.raw)} (${ctx.raw === typingSessions ? typingPct.toFixed(1) : nonTypingPct.toFixed(1)}%)`
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
                        ctx.font = '800 24px Inter, system-ui, sans-serif';
                        ctx.fillStyle = '#1e293b';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(typingPct.toFixed(1) + '%', width / 2, height / 2);
                        ctx.restore();
                    }
                }]
            });
        }

        // Table
        const $tbody = $('#ec_typing_logs_tbody');
        $tbody.empty();

        if (logs.length === 0) {
            $tbody.html('<tr><td colspan="3" class="uat-empty-cell">Chưa có dữ liệu nhập liệu trong khoảng thời gian này.</td></tr>');
            return;
        }

        logs.forEach(function (log) {
            const timeObj = new Date(log.time);
            const timeStr = isNaN(timeObj.getTime()) ? log.time : timeObj.toLocaleTimeString('en-US', { hour12: false });

            let fieldsHtml = '';
            const fields = log.fields_typed || {};
            if (typeof fields === 'object') {
                for (const [key, val] of Object.entries(fields)) {
                    // Cắt ngắn nếu giá trị quá dài
                    let strVal = val;
                    if (typeof strVal !== 'string') {
                        strVal = JSON.stringify(val);
                    }
                    const displayVal = strVal.length > 50 ? strVal.substring(0, 50) + '...' : strVal;
                    fieldsHtml += `<div style="margin-bottom: 4px; font-size: 12px;"><span style="color:#64748b; font-family: monospace; padding: 2px 4px; background: #f1f5f9; border-radius: 4px; margin-right: 6px;">${escH(key)}</span><span style="color:#334155; font-weight: 500;">${escH(displayVal)}</span></div>`;
                }
            } else {
                fieldsHtml = `<span style="color:#94a3b8; font-style:italic;">Không xác định được trường dữ liệu</span>`;
            }

            if (!fieldsHtml) {
                fieldsHtml = `<span style="color:#94a3b8; font-style:italic;">Không có trường tương tác</span>`;
            }

            $tbody.append(`
                <tr>
                    <td data-label="TIME & IP" style="vertical-align: top; padding: 12px;">
                        <div style="font-weight: 600; color: #334155; font-size: 13px; margin-bottom: 4px;">${timeStr}</div>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <span style="font-family: monospace; color: #64748b; font-size: 12px; word-break: break-all;">${escH(log.ip || 'N/A')}</span>
                            <a href="#" class="uat-copy-ip" data-ip="${escH(log.ip)}" style="color: #cbd5e1; flex-shrink: 0;" title="Copy IP"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path></svg></a>
                        </div>
                    </td>
                    <td data-label="LOCATION" style="vertical-align: top; padding: 12px;">
                        <span style="color: #475569; font-size: 12px; line-height: 1.4; display: inline-block;">${escH(log.location || 'Unknown')}</span>
                    </td>
                    <td data-label="DỮ LIỆU ĐÃ NHẬP" style="vertical-align: top; padding: 12px;">
                        ${fieldsHtml}
                    </td>
                </tr>
            `);
        });
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

        // Adaptive thresholds
        renderThresholds(ov.adaptive_thresholds || {});
    }

    function renderThresholds(thData) {
        const $tbody = $('#ec_thresholds_tbody');
        const $meta = $('#ec_thresholds_meta');

        if ($tbody.length === 0) return;

        const rows = Array.isArray(thData.rows) ? thData.rows : [];
        const latestDate = thData.baseline_latest_date || null;

        if ($meta.length) {
            $meta.text(latestDate ? ('Baseline cập nhật mới nhất lúc: ' + latestDate) : 'Đang chờ thu thập đủ chu kỳ Baseline 7 ngày');
        }

        if (rows.length === 0) {
            $tbody.html('<tr><td colspan="7" class="uat-empty-cell" style="padding: 24px 0;">Hệ thống chưa đủ dữ liệu traffic 7 ngày để Build Baseline. Tạm thời sử dụng <strong>Min Floor</strong> làm mức cảnh báo tiêu chuẩn.</td></tr>');
            return;
        }

        const metricKeyMap = {
            'clicks_5min': 'tổng số click trong 5 phút',
            'pageviews_5min': 'tổng số pageview trong 5 phút',
            'unique_urls_5min': 'số URL unique trong 5 phút',
            'events_5min': 'tổng số sự kiện trong 5 phút',
            'search_clicks_1min': 'số click tìm kiếm trong 1 phút',
            'depart_clicks_1min': 'số lần đổi ngày/chiều trong 1 phút',
            'requests_1min': 'số request trong 1 phút',
            'sessions_24h': 'tổng số session trong 24 giờ'
        };

        const tierBadgeMap = {
            'anon': '<span class="ec-tier-badge is-anon">🔴 Anon</span>',
            'aver': '<span class="ec-tier-badge is-aver">🟡 Aver</span>',
            'auth': '<span class="ec-tier-badge is-auth">🟢 Auth</span>',
            'all': '<span class="ec-tier-badge is-all">— Tất cả</span>',
        };

        $tbody.empty();
        rows.forEach(function (r) {
            const metric = r.metric_label || r.metric_key || 'Unknown';
            const context = (r.context || 'all').toString().toLowerCase();
            const p95 = Number(r.avg_p95 || 0);
            const multiplier = Number(r.multiplier || 0);
            const minFloor = Number(r.min_floor || 0);
            const currentLimit = Number(r.current_limit || 0);
            const contextInfo = getThresholdContextInfo(context);

            const rawKey = r.metric_key || '';
            const displayMetricKey = metricKeyMap[rawKey] || rawKey;

            const tierKey = (r.user_tier || 'all').toLowerCase();
            const tierBadge = tierBadgeMap[tierKey] || tierBadgeMap['all'];
            const peakBadge = r.is_peak_hour ? '<span class="ec-peak-badge">⚡ Peak</span>' : '';

            $tbody.append(`
                <tr>
                    <td data-label="Metric">
                        <div class="ec-threshold-metric-label">${escH(metric)}</div>
                        <div class="ec-threshold-metric-key">${escH(displayMetricKey)}</div>
                    </td>
                    <td data-label="Context">
                        <span class="ec-threshold-context-badge ${escH(contextInfo.className)}">${escH(contextInfo.label)}</span>
                    </td>
                    <td data-label="Tier">${tierBadge}</td>
                    <td data-label="P95" class="ec-threshold-cell-right ec-threshold-cell-strong">${p95.toFixed(2)}</td>
                    <td data-label="Multiplier" class="ec-threshold-cell-right">x${multiplier.toFixed(2)}</td>
                    <td data-label="Min Floor" class="ec-threshold-cell-right">${fmt(minFloor)}</td>
                    <td data-label="Current Limit" class="ec-threshold-cell-right">
                        <span class="ec-threshold-limit-badge">${fmt(currentLimit)}</span>
                        ${peakBadge}
                    </td>
                </tr>
            `);
        });
    }

    function getThresholdContextInfo(context) {
        if (context === 'all') {
            return {
                label: 'Toàn site',
                className: 'is-all'
            };
        }
        if (context === 'search') {
            return {
                label: 'Trang tìm chuyến bay',
                className: 'is-search'
            };
        }
        if (context === 'general') {
            return {
                label: 'Trang chung',
                className: 'is-general'
            };
        }
        return {
            label: 'Khác (' + context + ')',
            className: 'is-other'
        };
    }

    function renderHourlyTraffic() {
        let canvas = document.getElementById('ec_hourly_chart');
        if (!canvas) return;

        if (hourlyChartInstance) {
            hourlyChartInstance.destroy();
            hourlyChartInstance = null;
        }

        const isLive = currentHourlyMode === 'live';
        const dataArr = isLive ? hourlyLiveCache : hourlyHistoryCache;

        // Build labels từ union của cả 2 dataset (nếu compare)
        const allHours = new Set();
        (dataArr || []).forEach(item => allHours.add(item.hour));
        if (!isLive && currentHourlyCompare) {
            (hourlyLiveCache || []).forEach(item => allHours.add(item.hour));
        }
        const labels = Array.from(allHours).sort();

        // Map data chính
        const dataMap = {};
        (dataArr || []).forEach(item => { dataMap[item.hour] = item.sessions; });
        const data = labels.map(h => dataMap[h] ?? null);

        const ctx = canvas.getContext('2d');
        const color = isLive ? '#10b981' : '#8b5cf6';
        const bg = isLive ? 'rgba(16, 185, 129, 0.15)' : 'rgba(139, 92, 246, 0.15)';

        const datasets = [{
            label: isLive ? 'Hôm nay (Live)' : `Ngày được chọn`,
            data: data,
            borderColor: color,
            backgroundColor: currentHourlyChartType === 'bar' ? color : bg,
            borderWidth: 2,
            fill: currentHourlyChartType === 'line',
            tension: 0.3,
            pointRadius: 3,
            pointHoverRadius: 6,
            hitRadius: 15 // Mở rộng vùng chạm (hitbox) trên mobile để người dùng cực kỳ dễ tap xem chi tiết
        }];

        // Dataset thứ 2: Hôm nay (compare mode)
        if (!isLive && currentHourlyCompare && hourlyLiveCache && hourlyLiveCache.length > 0) {
            const liveMap = {};
            hourlyLiveCache.forEach(item => { liveMap[item.hour] = item.sessions; });
            const liveData = labels.map(h => liveMap[h] ?? null);
            datasets.push({
                label: 'Hôm nay (Live)',
                data: liveData,
                borderColor: '#10b981',
                backgroundColor: currentHourlyChartType === 'bar' ? 'rgba(16, 185, 129, 0.7)' : 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                borderDash: currentHourlyChartType === 'line' ? [5, 3] : [],
                fill: false,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 6,
                hitRadius: 15
            });
        }

        hourlyChartInstance = new Chart(ctx, {
            type: currentHourlyChartType,
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: !isLive && currentHourlyCompare,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 14,
                            font: { size: 12, weight: '600' }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(15, 23, 42, 0.88)',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y ?? '—'} sessions`
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // ── Update button states ──────────────────────────────
        if (currentHourlyChartType === 'line') {
            $('#ec_hourly_toggle_btn').text('Đổi sang Bar Chart');
        } else {
            $('#ec_hourly_toggle_btn').text('Đổi sang Line Chart');
        }

        if (isLive) {
            $('#ec_hourly_mode_btn').text('Xem dữ liệu Lịch sử');
            $('#ec_hourly_mode_btn').css({ 'background': '#d1fae5', 'color': '#047857', 'border': '1px solid #a7f3d0' });
            // Ẩn nút compare khi đang live
            $('#ec_hourly_compare_btn').hide();
        } else {
            $('#ec_hourly_mode_btn').text('Xem dữ liệu Trực tiếp');
            $('#ec_hourly_mode_btn').css({ 'background': '#e0e7ff', 'color': '#4338ca', 'border': '1px solid #c7d2fe' });
            // Hiện nút compare
            $('#ec_hourly_compare_btn').show();
            if (currentHourlyCompare) {
                $('#ec_hourly_compare_btn').text('✕ Tắt So sánh');
                $('#ec_hourly_compare_btn').css({ 'background': '#fef2f2', 'color': '#dc2626', 'border': '1px solid #fca5a5' });
            } else {
                $('#ec_hourly_compare_btn').text('So sánh với Hôm nay');
                $('#ec_hourly_compare_btn').css({ 'background': '#f0fdf4', 'color': '#16a34a', 'border': '1px solid #86efac' });
            }
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

        // ── NEW: Peak Hour Intelligence ──────────────────────────
        renderPeakHours(fl.peak_hours || [], fl.peak_hours_full || [], fl.peak_hour_labels || []);

        // ── NEW: Popular vs Niche Routes ─────────────────────────
        renderRouteIntelList('#ec_popular_routes_list', fl.popular_routes || [], '#3b82f6');
        renderRouteIntelList('#ec_niche_routes_list', fl.niche_routes || [], '#8b5cf6');

        // ── NEW: Peak IPs ────────────────────────────────────────
        renderPeakIPs(fl.peak_ips || []);
    }

    // ── Peak Hours: Chart.js bar chart với highlight giờ cao điểm ──────
    // peakHours: top 3 chips [{hour, sessions}]
    // fullHours: mảng 24h [{hour, sessions}] đầy đủ từ API
    // peakLabels: ['10:00','11:00','15:00'] — các giờ cần tô vàng
    function renderPeakHours(peakHours, fullHours, peakLabels) {
        const $chips = $('#ec_peak_stat_chips');
        const canvas = document.getElementById('ec_peak_hours_chart');
        $chips.empty();

        if (!peakHours || peakHours.length === 0 || !canvas) {
            $chips.html('<span style="color:#94a3b8;font-size:12px;">Chưa có dữ liệu</span>');
            return;
        }

        // Chips thống kê — số lượng chip phụ thuộc vào dynamic threshold của API
        const chipPalette = [
            { bg: '#fffbeb', border: '#f59e0b', text: '#92400e' }, // Top 1 - vàng
            { bg: '#f1f5f9', border: '#94a3b8', text: '#334155' }, // Top 2 - xám
            { bg: '#fdf6ec', border: '#cd7c2f', text: '#7c3f0e' }, // Top 3 - cam
            { bg: '#f0fdf4', border: '#4ade80', text: '#166534' }, // Top 4 - xanh lá
            { bg: '#eff6ff', border: '#60a5fa', text: '#1e40af' }, // Top 5 - xanh lam
            { bg: '#fdf4ff', border: '#c084fc', text: '#6b21a8' }, // Top 6 - tím
        ];
        peakHours.forEach(function (ph, i) {
            const c = chipPalette[i] || { bg: '#f8fafc', border: '#e2e8f0', text: '#475569' };
            const rank = ph.rank || ('Top ' + (i + 1));
            $chips.append(`<span style="background:${c.bg}; border:1px solid ${c.border}; color:${c.text};
                padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700;">
                ${rank} &nbsp;${escH(ph.hour)}&nbsp;
                <span style="font-weight:400; opacity:.75;">(${fmt(ph.sessions)} sess)</span>
            </span>`);
        });

        // Dùng peak_hours_full (24h từ API) — chính xác nhất
        // Fallback: tự build 24h từ peak_hours nếu API cũ chưa có peak_hours_full
        const peakSet = new Set(Array.isArray(peakLabels) && peakLabels.length > 0
            ? peakLabels
            : peakHours.map(h => h.hour));

        let sourceArr = fullHours;
        if (!sourceArr || sourceArr.length === 0) {
            // Fallback: build từ peak_hours + fill 0 cho giờ còn lại
            const byHour = {};
            peakHours.forEach(ph => { byHour[ph.hour] = ph.sessions; });
            sourceArr = [];
            for (let i = 0; i < 24; i++) {
                const lbl = String(i).padStart(2, '0') + ':00';
                sourceArr.push({ hour: lbl, sessions: byHour[lbl] || 0 });
            }
        }

        const labels = [];
        const data = [];
        const bgArr = [];
        const borderArr = [];

        sourceArr.forEach(function (item) {
            labels.push(item.hour);
            data.push(item.sessions || 0);
            if (peakSet.has(item.hour)) {
                bgArr.push('rgba(245,158,11,0.85)');
                borderArr.push('#d97706');
            } else {
                bgArr.push('rgba(148,163,184,0.35)');
                borderArr.push('rgba(148,163,184,0.6)');
            }
        });

        if (peakHoursChartInstance) {
            peakHoursChartInstance.destroy();
            peakHoursChartInstance = null;
        }

        peakHoursChartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sessions',
                    data: data,
                    backgroundColor: bgArr,
                    borderColor: borderArr,
                    borderWidth: 1.5,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: ctx => ctx[0].label,
                            label: ctx => ` ${ctx.raw} sessions` + (peakSet.has(ctx.label) ? 'Cao điểm' : '')
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 0 } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } }, beginAtZero: true }
                }
            }
        });
    }

    // ── Route Intel List: dùng chung cho popular & niche ───────────
    function renderRouteIntelList(selector, routes, accentColor) {
        const $wrap = $(selector);
        $wrap.empty();

        if (!routes || routes.length === 0) {
            $wrap.html('<p style="color:#94a3b8; font-style:italic; font-size:13px;">Chưa có dữ liệu.</p>');
            return;
        }

        routes.forEach(function (r, idx) {
            const datesMap = r.departure_dates || {};
            // Hiển thị tất cả ngày được lưu (API giờ lưu tối đa 30 ngày)
            const dateEntries = Object.entries(datesMap);
            const border = idx < routes.length - 1 ? 'border-bottom:1px solid #f1f5f9;' : '';
            const itemId = 'route_det_' + Math.random().toString(36).substr(2, 9);

            // Tính sum từ chi tiết để cung cấp context
            const sumSess = dateEntries.reduce((acc, [, inf]) => acc + (inf.sessions || 0), 0);
            // Sessions lệch do cross-date uniqueness
            const sessNote = sumSess > r.sessions
                ? `<span style="font-size:10px; color:#f59e0b; margin-left:4px;"
                      title="Tổng cộng per-ngày = ${sumSess} sess. Lệch vì 1 người search nhiều ngày KH khác nhau chỉ tính 1 unique sess.">(unique)</span>`
                : '';

            // ── Build expandable detail ──────────────────────────
            let detailHtml = '';

            // Dòng tổng kết toàn route
            detailHtml += `
                <div style="display:flex; gap:10px; align-items:center; padding:8px 10px; background:#f0f9ff;
                     border-radius:8px; margin-bottom:12px; border:1px solid #bae6fd; flex-wrap:wrap;">
                    <span style="font-size:12px; font-weight:700; color:#0369a1;">Tổng</span>
                    <span style="font-size:12px; color:#0f172a; font-weight:600;">${fmt(r.sessions)}
                        <span style="font-weight:400; color:#64748b; font-size:11px;"> sess</span>${sessNote}
                    </span>
                    <span style="color:#cbd5e1;">·</span>
                    <span style="font-size:12px; color:#0f172a; font-weight:600;">${fmt(r.events)}
                        <span style="font-weight:400; color:#64748b; font-size:11px;"> lượt tìm</span>
                    </span>
                    <span style="font-size:11px; color:#94a3b8; margin-left:auto;">${dateEntries.length} ngày KH</span>
                </div>`;

            if (dateEntries.length === 0) {
                detailHtml += '<p style="color:#cbd5e1; font-size:12px; font-style:italic;">Chưa có dữ liệu chi tiết ngày.</p>';
            } else {
                const maxInitial = 5;
                const moreClass = 'more_dates_' + itemId;
                const btnId = 'btn_more_' + itemId;

                dateEntries.forEach(([date, info], dIdx) => {
                    // Backward compat: info.sessions (new) | info.total (old)
                    const dmSess = info.sessions != null ? info.sessions : (info.total || 0);
                    const dmEvents = info.events != null ? info.events : 0;
                    const hoursObj = info.hours || {};
                    const hourEntries = Object.entries(hoursObj);

                    const hourRows = hourEntries.map(([h, hData]) => {
                        // Backward compat: hData object (new) | number (old)
                        const hSess = (typeof hData === 'object') ? (hData.sessions || 0) : hData;
                        const hEvents = (typeof hData === 'object') ? (hData.events || 0) : 0;
                        return `<div style="display:flex; justify-content:space-between; align-items:center; padding:4px 0; border-bottom:1px dotted #f1f5f9;">
                            <span style="font-family:monospace; font-size:12px; color:#475569; background:#f8fafc; padding:1px 7px; border-radius:4px;">${escH(h)}</span>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="min-width:50px; text-align:right; font-size:12px;">
                                    <strong style="color:#0f172a;">${hSess}</strong><span style="color:#94a3b8; font-size:10px; margin-left:2px;">sess</span>
                                </div>
                                ${hEvents > 0 ? `
                                    <span style="color:#cbd5e1; font-size:10px;">·</span>
                                    <div style="min-width:50px; text-align:right; font-size:12px;">
                                        <strong style="color:#0f172a;">${hEvents}</strong><span style="color:#94a3b8; font-size:10px; margin-left:2px;">lượt</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>`;
                    }).join('');

                    const wrapperClass = dIdx >= maxInitial ? moreClass : '';
                    const wrapperStyle = dIdx >= maxInitial ? 'display:none; margin-bottom:10px;' : 'margin-bottom:10px;';

                    detailHtml += `
                        <div class="${wrapperClass}" style="${wrapperStyle}">
                            <div style="display:flex; justify-content:space-between; align-items:center;
                                 background:#f8fafc; border-radius:6px; padding:6px 10px; margin-bottom:4px;
                                 border-left:3px solid ${accentColor};">
                                <span style="font-size:13px; font-weight:700; color:#1e293b;">✈ KH: ${escH(date)}</span>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <span style="font-size:11px; background:#dbeafe; color:#1d4ed8;
                                          padding:2px 8px; border-radius:10px; font-weight:600;"
                                          title="Unique sessions (1 người search nhiều giờ vẫn tính 1)">${dmSess} sess</span>
                                    ${dmEvents > 0 ? `<span style="font-size:11px; background:#f0fdf4; color:#166534;
                                          padding:2px 8px; border-radius:10px; font-weight:600;"
                                          title="Tổng lượt pageview tìm kiếm">${dmEvents} lượt</span>` : ''}
                                </div>
                            </div>
                            <div style="padding:2px 10px 2px 14px; border-left:2px solid #e2e8f0;">
                                ${hourRows || '<em style="font-size:11px; color:#cbd5e1;">Không rõ giờ</em>'}
                            </div>
                        </div>`;
                });

                if (dateEntries.length > maxInitial) {
                    const hiddenCount = dateEntries.length - maxInitial;
                    detailHtml += `
                        <div id="${btnId}" style="text-align:center; padding-top:4px; margin-top:8px;">
                            <button onclick="
                                    const $h = $('.${moreClass}:hidden');
                                    $h.slice(0, 5).slideDown(250);
                                    if ($h.length <= 5) {
                                        $('#${btnId}').slideUp(250);
                                    } else {
                                        $(this).text('Xem thêm ' + ($h.length - 5) + ' ngày KH ▼');
                                    }
                                "
                                style="background:#f8fafc; border:1px solid #cbd5e1; color:#475569; font-size:11px; font-weight:600; 
                                border-radius:16px; padding:6px 16px; cursor:pointer; transition:all 0.2s;"
                                onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a';" 
                                onmouseout="this.style.background='#f8fafc'; this.style.color='#475569';">
                                Xem thêm ${hiddenCount} ngày KH ▼
                            </button>
                        </div>`;
                }
            }

            $wrap.append(`
                <div style="padding:12px 0; ${border}">
                    <!-- Title Row -->
                    <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
                        onclick="$('#${itemId}').slideToggle(200); const $a=$(this).find('.uat-arr'); $a.text($a.text()==='▼'?'▲':'▼');">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span style="font-size:15px; font-weight:700; color:${accentColor}; letter-spacing:0.5px;">${escH(r.route)}</span>
                            <span class="uat-arr" style="font-size:9px; color:#cbd5e1; user-select:none;">▼</span>
                        </div>
                        <span style="font-size:12px; color:#64748b; background:#f8fafc; padding:2px 8px; border-radius:12px; border:1px solid #e2e8f0;">
                            ${fmt(r.sessions)} sess · ${fmt(r.events)} lượt
                        </span>
                    </div>

                    <!-- Expandable chi tiết ngày × giờ -->
                    <div id="${itemId}" style="display:none; margin-top:12px; padding-top:12px; border-top:1px dashed #e2e8f0;">
                        ${detailHtml}
                    </div>
                </div>`);
        });
    }

    // ── Peak IPs: list layout cho cột hẹp (uat-col-4) ─────────────────────────
    function renderPeakIPs(ips) {
        const $grid = $('#ec_peak_ips_grid');
        $grid.empty();

        if (!ips || ips.length === 0) {
            $grid.html('<p style="color:#94a3b8; font-style:italic; font-size:13px;">Chưa có dữ liệu IP trong giờ cao điểm.</p>');
            $('#ec_peak_ips_count').text('');
            return;
        }

        $('#ec_peak_ips_count').text(ips.length + ' IPs');

        ips.forEach(function (entry, idx) {
            const isTop3 = idx < 3;
            const rankChip = isTop3 ? `background:#dbeafe; color:#1d4ed8; font-weight:700;` : `background:#f1f5f9; color:#64748b; font-weight:600;`;
            const border = idx < ips.length - 1 ? 'border-bottom:1px solid #f1f5f9;' : '';

            $grid.append(`
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; ${border}">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="display:flex; justify-content:center; align-items:center; width:22px; height:22px; border-radius:12px; font-size:11px; ${rankChip}">
                            ${idx + 1}
                        </span>
                        <div style="display:flex; flex-direction:column;">
                            <span class="uat-ip-link" data-ip="${escH(entry.ip)}" style="font-family:monospace; font-size:13px; color:#1e293b; font-weight:600; cursor:pointer;" title="Bấm để xem lịch sử IP">
                                ${escH(entry.ip)}
                            </span>
                            <span style="font-size:11px; color:#64748b;">${fmt(entry.sessions)} lượt tìm</span>
                        </div>
                    </div>
                    <button class="uat-copy-ip" data-ip="${escH(entry.ip)}" title="Copy IP" style="background:none; border:none; padding:4px; cursor:pointer; color:#64748b; display:flex; align-items:center; transition:color 0.2s; outline:none;"
                        onclick="navigator.clipboard.writeText('${escH(entry.ip)}').then(() => { let o=this.innerHTML; this.innerHTML='<span style=\\'color:#10b981;font-weight:700;display:flex;align-items:center;justify-content:center;width:14px;height:14px;\\'>✓</span>'; setTimeout(()=>this.innerHTML=o,1500); })">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    </button>
                </div>`);
        });
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
            $tbody.html('<tr><td colspan="6" class="uat-empty-cell">Không có người dùng đáng ngờ nào.</td></tr>');
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
                    <td data-label="Thao tác" style="vertical-align:middle;text-align:center;">
                        <button class="uat-btn ec-open-ip-modal" data-ip="${escH(su.ip || '')}"
                                style="background:#ef4444;color:#fff;border:none;padding:0 14px;height:30px;border-radius:6px;font-weight:600;font-size:12px;cursor:pointer;">
                            Quản lý
                        </button>
                    </td>
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
            $tbody.html('<tr><td colspan="5" style="text-align:center; color:#10b981; padding:40px 0; font-weight:500;">No route scraping behavior detected.</td></tr>');
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
                    <td data-label="Thao tác" style="vertical-align:middle;text-align:center;">
                        <button class="uat-btn ec-open-ip-modal" data-ip="${escH(cb.ip || '')}"
                                style="background:#ef4444;color:#fff;border:none;padding:0 14px;height:30px;border-radius:6px;font-weight:600;font-size:12px;cursor:pointer;">
                            Quản lý
                        </button>
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

    // ── IP Journey Tab ────────────────────────────────────────────────────
    (function () {
        var $btn = $('#ec_journey_btn');
        var $ipInput = $('#ec_journey_ip');
        var $daysSelect = $('#ec_journey_days');
        var $loading = $('#ec_journey_loading');
        var $error = $('#ec_journey_error');
        var $result = $('#ec_journey_result');
        var $summary = $('#ec_journey_summary');
        var $manageIpWrap = $('#ec_journey_manage_ip_wrap');
        var $sessCard = $('#ec_journey_sessions_card');
        var $sessCount = $('#ec_journey_session_count');
        var $sessList = $('#ec_journey_session_list');
        var $tree = $('#ec_journey_tree');
        var $detail = $('#ec_journey_path_detail');

        var currentSessionIdx = 0;
        var currentData = null;

        var LBL_MAP = {
            'contact_name': 'Họ tên', 'field_ho_ten': 'Họ tên',
            'contact_phone': 'Số ĐT', 'client-phone': 'Số ĐT', 'field_sdt': 'Số ĐT',
            'contact_email': 'Email', 'field_email': 'Email',
            'special_request': 'Yêu cầu',
            'passenger_name': 'Tên Hành khách',
            'identification': 'CCCD/Thẻ',
            'payment-online': 'TT Online',
            'field_ngay_sinh': 'Ngày sinh',
            'pay_atm': 'Thẻ ATM',
            'pay_visa': 'Thẻ tín dụng',
            'pay_later': 'Trả sau',
            'pay_qr': 'Quét mã QR'
        };

        // --- Hỗ trợ cuộn ngang bằng nút lăn chuột (Smart Scroll) ---
        $tree.on('wheel', function (e) {
            var evt = e.originalEvent;
            // Áp dụng nếu người dùng lăn dọc (deltaY) và chưa đè phím Shift
            if (evt.deltaY !== 0 && !evt.shiftKey) {
                var currentScroll = $tree.scrollLeft();
                var maxScroll = $tree[0].scrollWidth - $tree[0].clientWidth;

                if (maxScroll > 0) {
                    var isAtLeft = currentScroll <= 0 && evt.deltaY < 0;
                    var isAtRight = currentScroll >= maxScroll && evt.deltaY > 0;

                    // Chuyển trục lăn từ dọc sang ngang, 
                    // nếu chưa đụng lề 2 bên thì chặn cuộn nguyên trang web
                    if (!isAtLeft && !isAtRight) {
                        e.preventDefault();
                        $tree.scrollLeft(currentScroll + evt.deltaY);
                    }
                }
            }
        });
        $btn.on('click', fetchJourney);
        $ipInput.on('keydown', function (e) { if (e.key === 'Enter') fetchJourney(); });

        function fetchJourney() {
            var ip = $ipInput.val().trim();
            var days = $daysSelect.val() || 30;
            if (!ip) { $ipInput.focus(); return; }

            if (!/^[0-9a-fA-F:\.]+$/.test(ip)) {
                showJourneyError('Vui lòng nhập địa chỉ IP hợp lệ.');
                $ipInput.focus();
                return;
            }

            $result.hide();
            $loading.show();
            $error.hide();
            $manageIpWrap.hide();

            // Sử dụng apiGet() đã có trong EC_TongHop, endpoint tracking/v1/ip-timeline
            apiGet('/dashboard/ip-timeline', { ip: ip, days: days })
                .done(function (data) {
                    $loading.hide();
                    if (data.error) { showJourneyError(data.error); return; }
                    currentData = data;
                    currentSessionIdx = 0;
                    renderJourney(data);
                    $result.show();
                })
                .fail(function (xhr) {
                    $loading.hide();
                    var msg = 'Lỗi kết nối đến API';
                    if (xhr && xhr.status === 404) msg = 'Endpoint ip-timeline không tìm thấy. Kiểm tra lại api_base.';
                    else if (xhr && xhr.status === 401) msg = 'Không có quyền truy cập API.';
                    showJourneyError(msg);
                });
        }

        function showJourneyError(msg) {
            $error.text(msg).show();
            $manageIpWrap.hide();
        }

        function renderJourney(data) {
            var s = data.summary || {};
            var convertedHtml = s.converted
                ? '<span class="ec-sum-status-done">Đã hoàn tất</span>'
                : '<span class="ec-sum-status-fail">Chưa hoàn tất</span>';

            var firstSeen = s.first_seen ? formatJourneyDate(s.first_seen) : 'N/A';
            var lastSeen = s.last_seen ? formatJourneyDate(s.last_seen) : 'N/A';

            var rawDeepest = s.deepest_stage_label || s.deepest_stage || '';
            var translatedDeepest = rawDeepest;
            if (rawDeepest.indexOf('Passenger') !== -1) translatedDeepest = 'Thông tin HK';
            else if (rawDeepest.indexOf('Completed') !== -1) translatedDeepest = 'Hoàn tất BK';
            else if (rawDeepest.indexOf('Search Flight') !== -1) translatedDeepest = 'TimChuyenBay';

            var totalTimChuyenBay = 0;
            var totalXemTrang = 0;
            if (data.sessions && data.sessions.length > 0) {
                $.each(data.sessions, function (idx, sess) {
                    if (sess.path && sess.path.length > 0) {
                        $.each(sess.path, function (_, node) {
                            if (node.type === 'pageview') {
                                totalXemTrang++;
                            }
                            var url = node.url || node.template_node_id || '';
                            if (node.type === 'pageview' && (url.indexOf('tim-chuyen-bay') !== -1 || url.indexOf('chon-hanh-trinh') !== -1 || url.indexOf('Search Flight') !== -1 || url.indexOf('search_flight') !== -1)) {
                                totalTimChuyenBay++;
                            }
                        });
                    }
                });
            }

            $summary.html(
                '<div style="display:flex; flex-wrap:wrap; gap:16px; align-items:center;">' +
                '<div class="ec-journey-summary-item">' +
                '<span class="ec-journey-summary-label">IP</span>' +
                '<span class="ec-sum-ip">' + escH(data.ip) + '</span>' +
                '</div>' +
                '<div class="ec-journey-summary-divider" style="height:32px;"></div>' +
                '<div class="ec-journey-summary-item">' +
                '<span class="ec-journey-summary-label">Tổng Sessions</span>' +
                '<span class="ec-sum-val-lg">' + (s.total_sessions || 0) + '</span>' +
                '</div>' +
                '<div class="ec-journey-summary-divider" style="height:32px;"></div>' +
                '<div class="ec-journey-summary-item">' +
                '<span class="ec-journey-summary-label">BOOKING</span>' +
                '<span class="ec-sum-val-lg" id="ec_journey_ip_booking" style="color:#1d4ed8;">-</span>' +
                '</div>' +
                '<div class="ec-journey-summary-divider" style="height:32px;"></div>' +
                '<div class="ec-journey-summary-item">' +
                '<span class="ec-journey-summary-label">THAM KHẢO</span>' +
                '<span class="ec-sum-val-lg" id="ec_journey_ip_thamkhao">-</span>' +
                '</div>' +
                '<div class="ec-journey-summary-divider" style="height:32px;"></div>' +
                '<div class="ec-journey-summary-item">' +
                '<span class="ec-journey-summary-label">TimChuyenBay</span>' +
                '<span class="ec-sum-val-lg" style="color:#f59e0b;">' + totalTimChuyenBay + '</span>' +
                '</div>' +
                '<div class="ec-journey-summary-divider" style="height:32px;"></div>' +
                '<div class="ec-journey-summary-item">' +
                '<span class="ec-journey-summary-label">XEM TRANG</span>' +
                '<span class="ec-sum-val-lg" style="color:#10b981;">' + totalXemTrang + '</span>' +
                '</div>' +
                '<div class="ec-journey-summary-divider" style="height:32px;"></div>' +
                '<div class="ec-journey-summary-item">' +
                '<span class="ec-journey-summary-label">Sâu nhất</span>' +
                '<span class="ec-sum-val-md">' + escH(translatedDeepest) + '</span>' +
                '</div>' +
                '<div class="ec-journey-summary-divider" style="height:32px;"></div>' +
                '<div class="ec-journey-summary-item">' +
                '<span class="ec-journey-summary-label">Trạng thái</span>' +
                convertedHtml +
                '</div>' +
                '<div class="ec-journey-summary-divider" style="height:32px;"></div>' +
                '<div class="ec-journey-summary-item">' +
                '<span class="ec-journey-summary-label">Lần đầu</span>' +
                '<span class="ec-sum-val-sm">' + firstSeen + '</span>' +
                '</div>' +
                '<div class="ec-journey-summary-divider" style="height:32px;"></div>' +
                '<div class="ec-journey-summary-item">' +
                '<span class="ec-journey-summary-label">Lần cuối</span>' +
                '<span class="ec-sum-val-sm">' + lastSeen + '</span>' +
                '</div>' +
                '</div>'
            );
            $('#ec_journey_manage_ip_btn').attr('data-ip', data.ip || '');
            $manageIpWrap.show();

            $.ajax({
                url: 'index.php?entryPoint=entryPointBookingStats',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    site_domain: currentSiteKey,
                    cities: { 'Journey': [data.ip] }
                }),
                success: function (res) {
                    try {
                        const parsed = typeof res === 'string' ? JSON.parse(res) : res;
                        if (parsed && parsed.status === 'success' && parsed.data && parsed.data['Journey']) {
                            $('#ec_journey_ip_booking').text(parsed.data['Journey']['Booking']);
                            $('#ec_journey_ip_thamkhao').text(parsed.data['Journey']['ThamKhao']);
                        } else {
                            $('#ec_journey_ip_booking').text('0');
                            $('#ec_journey_ip_thamkhao').text('0');
                        }
                    } catch (e) {
                        $('#ec_journey_ip_booking').text('Lỗi');
                        $('#ec_journey_ip_thamkhao').text('Lỗi');
                    }
                },
                error: function () {
                    $('#ec_journey_ip_booking').text('Lỗi');
                    $('#ec_journey_ip_thamkhao').text('Lỗi');
                }
            });

            if (data.sessions && data.sessions.length > 0) {
                $sessCard.show();
                $sessCount.text(data.sessions.length + ' sessions');
                $sessList.empty();

                $.each(data.sessions, function (idx, sess) {
                    // Bơm dữ liệu ảo (Mock path) vào nếu session lấy được bị mất mảng path nhưng vẫn có point dropping
                    if ((!sess.path || sess.path.length === 0) && sess.funnel_summary && sess.funnel_summary.stage_reached) {
                        sess.path = [{
                            template_node_id: sess.funnel_summary.stage_reached,
                            type: 'pageview',
                            dropped_here: true,
                            entered_at: sess.meta ? sess.meta.created_at : '',
                            url: sess.funnel_summary.stage_label || sess.funnel_summary.stage_reached
                        }];
                    }

                    var fs = sess.funnel_summary || {};
                    var m = sess.meta || {};
                    var chipClass = 'ec-journey-session-chip';
                    if (fs.completed) chipClass += ' converted';
                    else if (fs.dropped_at) chipClass += ' dropped';

                    var dt = m.created_at ? formatJourneyDate(m.created_at) : '';
                    var rawLabel = fs.stage_label || fs.stage_reached || '';
                    var translatedLabel = rawLabel;
                    if (rawLabel.indexOf('Passenger') !== -1) translatedLabel = 'Thông tin HK';
                    else if (rawLabel.indexOf('Completed') !== -1) translatedLabel = 'Hoàn tất BK';
                    else if (rawLabel.indexOf('Search Flight') !== -1) translatedLabel = 'TimChuyenBay';

                    var $chip = $('<button class="' + chipClass + '" type="button"></button>')
                        .attr('title', dt + ' | ' + (m.device || '') + ' | ' + (m.referrer_source || ''))
                        .html('<strong>#' + (idx + 1) + '</strong> ' + escH(translatedLabel));

                    if (idx === 0) $chip.addClass('active');

                    (function (i, s) {
                        $chip.on('click', function () {
                            $('.ec-journey-session-chip').removeClass('active');
                            $(this).addClass('active');
                            currentSessionIdx = i;
                            renderTreeForSession(data.template_tree, s);
                            renderPathDetail(s, i);
                            $detail.show();
                        });
                    })(idx, sess);

                    $sessList.append($chip);
                });

                renderTreeForSession(data.template_tree, data.sessions[0]);
                renderPathDetail(data.sessions[0], 0);
                $detail.show();
            } else {
                $sessCard.hide();
                $tree.html('<p class="ec-empty-msg">IP này không có sessions trong khoảng thời gian đã chọn.</p>');
                $detail.hide();
            }
        }

        function renderTreeForSession(templateTree, session) {
            var path = (session && session.path) ? session.path : [];

            // Build visitedMap: nodeId → { count, deviation, dropped }
            var visitedMap = {};
            var dynamicChildrenMap = {};

            function addOrIncrementItem(arr, val) {
                for (var i = 0; i < arr.length; i++) {
                    if (arr[i].val === val) {
                        arr[i].cnt++;
                        return;
                    }
                }
                arr.push({ val: val, cnt: 1 });
            }

            $.each(path, function (_, node) {
                var tid = node.template_node_id;
                if (!visitedMap[tid]) visitedMap[tid] = { count: 0, deviation: null, dropped: false };
                visitedMap[tid].count++;
                if (node.deviation_type) visitedMap[tid].deviation = node.deviation_type;
                if (node.dropped_here) visitedMap[tid].dropped = true;

                // Dynamically form groups of "typing" & "click" events relative to their parent
                if ((node.type === 'typing' || node.type === 'click') && node.parent_node && node.parent_node !== tid) {
                    var parent = node.parent_node;
                    if (parent === 'form_fill') parent = 'passenger_info'; // tương thích ngược legacy data

                    if (!dynamicChildrenMap[parent]) dynamicChildrenMap[parent] = { typing: [], click: [] };
                    var groupMap = dynamicChildrenMap[parent][node.type]; // natively map typing/click

                    var finalLabel = LBL_MAP[tid] || node.element_value || tid;
                    addOrIncrementItem(groupMap, finalLabel);
                }

                // search_query special grouping
                if (tid === 'search_query') {
                    if (!visitedMap['flight_search_info']) {
                        visitedMap['flight_search_info'] = { count: 0, deviation: null, dropped: false, queries: [] };
                    }
                    visitedMap['flight_search_info'].count++;
                    if (node.element_value) {
                        addOrIncrementItem(visitedMap['flight_search_info'].queries, node.element_value);
                    }
                }
            });

            var converted = session && session.funnel_summary && session.funnel_summary.completed;

            // ── Helper: render một node box ─────────────────────────────
            function nodeBox(tNode) {
                var nodeId = tNode.id;
                var label = tNode.label || nodeId;
                var type = tNode.type || '';

                // Root node
                if (nodeId === 'root') {
                    return '<div class="ec-tree-node root-node">' +
                        '<div class="ec-tree-node-label root">' + escH(label) + '</div>' +
                        '</div>';
                }

                var vi = visitedMap[nodeId];
                var cls = 'ec-tree-node';
                var badge = '';

                if (nodeId === 'completed' && converted) {
                    cls += ' converted';
                    badge = '<span class="ec-tree-node-badge done">Hoàn tất</span>';
                } else if (vi) {
                    if (vi.dropped) {
                        cls += ' dropped';
                        badge = '<span class="ec-tree-node-badge drop">Thoát</span>';
                    } else if (vi.deviation === 'backtrack') {
                        cls += ' backtrack';
                        badge = '<span class="ec-tree-node-badge back">Quay lại</span>';
                    } else {
                        cls += ' visited';
                    }
                } else {
                    cls += ' not-visited';
                }

                var typeLabel = (type === 'pageview') ? 'Trang' : (type === 'click') ? 'Click' : (type === 'typing') ? 'Nhập liệu' : '';

                var mainNodeHtml = '<div class="' + cls + '" title="' + escH(nodeId) + '">' +
                    '<div class="ec-tree-node-label">' + escH(label) + '</div>' +
                    (typeLabel ? '<div class="ec-tree-node-type">' + typeLabel + '</div>' : '') +
                    badge +
                    '</div>';

                // Look for dynamic children (mini branches)
                var dyn = dynamicChildrenMap[nodeId];
                var hasDynTyping = dyn && dyn.typing.length > 0;
                var hasDynClick = dyn && dyn.click.length > 0;
                var hasSearchQueries = (nodeId === 'flight_search' && visitedMap['flight_search_info']);

                if (!hasDynTyping && !hasDynClick && !hasSearchQueries) {
                    return mainNodeHtml;
                }

                function buildMiniBox(title, dynType, items) {
                    var isInfo = dynType === 'info';
                    var isClick = dynType === 'click';

                    var stateCls = isClick ? ' click' : (isInfo ? ' info' : ' data');

                    var html = '<div class="ec-minibox-wrap' + stateCls + '">';
                    html += '<div class="ec-minibox-title">' + escH(title) + '</div>';

                    if (isInfo) {
                        html += '<div class="ec-minibox-info-container">';
                    } else {
                        html += '<div class="ec-minibox-click-container">';
                    }

                    $.each(items, function (_, obj) {
                        if (!obj || !obj.val) return true;
                        var lbl = obj.val;
                        var cntStr = obj.cnt > 1 ? ' (x' + obj.cnt + ')' : '';

                        if (isInfo) {
                            html += '<span class="ec-minibox-info-item' + stateCls + '" title="' + escH(lbl + cntStr) + '">' + escH(lbl + cntStr) + '</span>';
                        } else {
                            html += '<span class="ec-minibox-click-item' + stateCls + '" title="' + escH(lbl + cntStr) + '">' + escH(lbl + cntStr) + '</span>';
                        }
                    });
                    html += '</div></div>';
                    return html;
                }

                var miniBoxes = [];
                if (hasDynTyping) miniBoxes.push('<div class="ec-minibox-item">' + buildMiniBox('Nhập liệu', 'typing', dyn.typing) + '</div>');
                if (hasDynClick) miniBoxes.push('<div class="ec-minibox-item">' + buildMiniBox('Thao tác', 'click', dyn.click) + '</div>');
                if (hasSearchQueries) {
                    var queries = visitedMap['flight_search_info'].queries || [];
                    var chunkSize = 5;
                    var chunks = [];
                    for (var i = 0; i < queries.length; i += chunkSize) chunks.push(queries.slice(i, i + chunkSize));
                    $.each(chunks, function (j, chk) {
                        var isHidden = j >= 4;
                        var lblTitle = chunks.length > 1 ? 'Truy vấn (' + (j * chunkSize + 1) + '-' + Math.min(queries.length, (j + 1) * chunkSize) + ')' : 'Truy vấn';
                        var hideCls = isHidden ? (' ec-query-hide-' + nodeId) : '';
                        var dispAttr = isHidden ? 'display:none;' : '';
                        miniBoxes.push('<div class="ec-minibox-item ec-query-box-' + nodeId + hideCls + '" style="' + dispAttr + '">' + buildMiniBox(lblTitle, 'info', chk) + '</div>');
                    });

                    if (chunks.length > 4) {
                        var btnHtml = '<button type="button" data-nid="' + nodeId + '" class="ec-query-more-btn ec-btn-action" style="margin:auto 2px;">Xem thêm</button>';
                        btnHtml += '<button type="button" data-nid="' + nodeId + '" class="ec-query-less-btn ec-btn-action" style="margin:auto 2px;display:none;">Thu gọn</button>';
                        miniBoxes.push('<div class="ec-minibox-item ec-btn-wrap-query-' + nodeId + '" style="display:flex;">' + btnHtml + '</div>');
                    }
                }

                var wrappedGrid = [];
                $.each(miniBoxes, function (idx, boxHtml) {
                    wrappedGrid.push(boxHtml);
                    if ((idx + 1) % 5 === 0 && (idx + 1) < miniBoxes.length) {
                        wrappedGrid.push('<div class="ec-flex-break"></div>');
                    }
                });

                return '<div class="ec-node-container">' +
                    mainNodeHtml +
                    '<div class="ec-node-line-bottom"></div>' +
                    '<div class="ec-miniboxes-grid">' + wrappedGrid.join('') + '</div>' +
                    '</div>';
            }

            // ── Helper: connector dọc đứt ngang
            function vConnector(active) {
                return '<div style="display:flex;justify-content:center;width:100%;">' +
                    '<div class="ec-tree-connector' + (active ? ' active' : '') + '"></div>' +
                    '</div>';
            }

            // ── Recursive renderer ────────────────────────────────────────
            function renderSubtree(tNode) {
                if (tNode.ref) return '';

                var children = (tNode.children || []).filter(function (c) {
                    return !c.ref && c.id !== 'form_fill' && c.id !== 'payment_method' && c.id.indexOf('flight_search_info') !== 0;
                });

                var nodeIsVisited = !!(visitedMap[tNode.id]) || tNode.id === 'root';
                var boxHtml = '<div class="ec-tree-node-wrap">' + nodeBox(tNode) + '</div>';
                var html = '';

                if (children.length === 0) {
                    html += '<div class="ec-tree-level" style="justify-content:center;">' + boxHtml + '</div>';
                    return html;
                }

                if (children.length === 1) {
                    var child = children[0];
                    var childVisited = !!(visitedMap[child.id]);
                    html += '<div class="ec-tree-level" style="justify-content:center;">' + boxHtml + '</div>';
                    html += vConnector(nodeIsVisited && childVisited);
                    html += renderSubtree(child);
                    return html;
                }

                // Branching flow
                html += '<div class="ec-tree-level" style="justify-content:center;">' + boxHtml + '</div>';
                html += vConnector(nodeIsVisited);

                html += '<div class="ec-tree-level-flex">';
                $.each(children, function (idx, child) {
                    var childActive = !!(visitedMap[child.id]);
                    var clr = (nodeIsVisited && childActive) ? '#6366f1' : '#e2e8f0';
                    var topColor = nodeIsVisited ? '#6366f1' : '#e2e8f0';
                    var lineWidth = (children.length === 2) ? '50%' : '100%';

                    html += '<div class="ec-tree-col">';

                    // Đường kẻ ngang (từ trung tâm ra biên)
                    if (idx === 0) {
                        html += '<div class="ec-tree-h-line" style="background:' + topColor + ';right:0;width:50%;"></div>';
                    } else if (idx === children.length - 1) {
                        html += '<div class="ec-tree-h-line" style="background:' + topColor + ';left:0;width:50%;"></div>';
                    } else {
                        html += '<div class="ec-tree-h-line" style="background:' + topColor + ';left:0;width:100%;"></div>';
                    }

                    // Đường kẻ dọc xuống node con
                    html += '<div class="ec-tree-v-line" style="background:' + clr + ';"></div>';

                    html += renderSubtree(child);
                    html += '</div>';
                });
                html += '</div>';

                return html;
            }

            var html = '<div class="ec-journey-tree-inner">' + renderSubtree(templateTree) + '</div>';
            $tree.html(html);

            $tree.off('click', '.ec-query-more-btn').on('click', '.ec-query-more-btn', function () {
                var nid = $(this).data('nid');
                var $hiddens = $tree.find('.ec-query-hide-' + nid);
                var $parent = $(this).parent();
                if ($hiddens.length > 0) {
                    $hiddens.first().removeClass('ec-query-hide-' + nid).addClass('ec-query-opened-' + nid).fadeIn(250).css('display', '');
                    $parent.find('.ec-query-less-btn').show();
                    if ($hiddens.length <= 1) $(this).hide();
                }
            });

            $tree.off('click', '.ec-query-less-btn').on('click', '.ec-query-less-btn', function () {
                var nid = $(this).data('nid');
                var $opened = $tree.find('.ec-query-opened-' + nid);
                var $parent = $(this).parent();
                if ($opened.length > 0) {
                    $opened.removeClass('ec-query-opened-' + nid).addClass('ec-query-hide-' + nid).hide();
                    $parent.find('.ec-query-more-btn').show();
                    $(this).hide();
                }
            });
        }

        function renderPathDetail(session, sessIdx) {
            var path = (session && session.path) ? session.path : [];
            var fs = session.funnel_summary || {};
            var m = session.meta || {};
            var dur = (m.duration_seconds > 0) ? formatDuration(m.duration_seconds) : 'N/A';
            var dt = m.created_at ? formatJourneyDate(m.created_at) : '';

            var searchTimes = [];
            var stepsHtml = '<div class="ec-journey-path-steps">';
            var renderedCount = 0;
            $.each(path, function (i, node) {
                if (node.template_node_id === 'search_query') {
                    if (node.entered_at) {
                        var t = new Date(node.entered_at.replace(' ', 'T')).getTime();
                        if (!isNaN(t)) searchTimes.push(t);
                    }
                    return true; // Bỏ qua node info để Path detail gọn gàng
                }

                var rawLabel = node.url
                    ? (node.url.replace(/.*\/([^?#]+)(\?.*)?$/, '$1') || node.template_node_id)
                    : (node.element || node.template_node_id);
                var label = rawLabel.substring(0, 22);
                var cls = 'ec-journey-step-node visited';
                if (node.dropped_here) cls = 'ec-journey-step-node dropped';
                else if (node.deviation_type === 'backtrack') cls = 'ec-journey-step-node backtrack-node';
                else if (node.template_node_id === 'completed') cls = 'ec-journey-step-node converted';

                var actionMap = { 'pageview': 'View', 'click': 'Click', 'typing': 'Typing' };
                var actionTxt = actionMap[node.type] || node.type || '';
                var actionHtml = actionTxt ? '<strong class="ec-action-txt">[' + actionTxt + ']</strong> ' : '';

                var isBack = node.deviation_type === 'backtrack';

                // Phân mảnh để ẩn/hiện trên mobile (mỗi 5 item 1 cụm)
                var chunkIdx = Math.floor(renderedCount / 5);
                var chunkCls = chunkIdx > 0 ? (' ec-step-mobile-hidden ec-step-chunk-' + chunkIdx) : '';

                stepsHtml += '<div class="ec-journey-step' + (isBack ? ' is-back' : '') + chunkCls + '">';
                if (renderedCount > 0) {
                    stepsHtml += '<span class="ec-journey-step-arrow' + (isBack ? ' back' : '') + '">' + (isBack ? '↩' : '→') + '</span>';
                }
                stepsHtml += '<span class="' + cls + '" title="' + escH(node.entered_at || '') + '">' + actionHtml + escH(label) + '</span>';
                stepsHtml += '</div>';

                renderedCount++;
            });
            stepsHtml += '</div>';

            // Sinh nút Xem Thêm/Thu Gọn cho Path Detail (Chỉ hiển thị trên Mobile nhờ CSS)
            var totalChunks = Math.ceil(renderedCount / 5);
            if (totalChunks > 1) {
                var mobileBtnHtml = '<div class="ec-mobile-btn-group">';
                mobileBtnHtml += '<button type="button" class="ec-path-more-btn ec-btn-action" data-current-chunk="0" data-max-chunk="' + (totalChunks - 1) + '">Xem thêm</button>';
                mobileBtnHtml += '<button type="button" class="ec-path-less-btn ec-btn-action" style="display:none;">Thu gọn</button>';
                mobileBtnHtml += '</div>';
                stepsHtml += mobileBtnHtml;
            }

            var totalSearches = searchTimes.length;
            var searchStatsHtml = '';
            if (totalSearches > 0) {
                var avgDistStats = '';
                if (totalSearches > 1) {
                    var firstTime = Math.min.apply(null, searchTimes);
                    var lastTime = Math.max.apply(null, searchTimes);
                    var diffSec = (lastTime - firstTime) / 1000;
                    var avgSec = diffSec / (totalSearches - 1);
                    if (avgSec >= 0) {
                        avgDistStats = '<span>Mỗi lần tìm cách nhau: <strong class="ec-metric-highlight">' + formatDuration(Math.round(avgSec)) + '</strong></span>';
                    }
                }
                searchStatsHtml = '<span>Tổng lượt tìm: <strong class="ec-metric-highlight">' + totalSearches + ' lần</strong></span>' + avgDistStats;
            }

            $detail.html(
                '<h4>Chi tiết hành trình Session #' + ((sessIdx !== undefined ? sessIdx : currentSessionIdx) + 1) + '</h4>' +
                '<div class="ec-path-detail-meta">' +
                '<span>' + escH(dt) + '</span>' +
                '<span>Thời gian: <strong>' + dur + '</strong></span>' +
                searchStatsHtml +
                '<span>Thiết bị: <strong>' + escH(m.device || '') + '</strong></span>' +
                '<span>Nguồn: <strong>' + escH(m.referrer_source || '') + '</strong></span>' +
                '<span>Backtracks: <strong class="ec-metric-danger">' + (fs.backtrack_count || 0) + '</strong></span>' +
                '<span>Tổng bước: <strong>' + (fs.total_nodes || path.length) + '</strong></span>' +
                '</div>' +
                stepsHtml
            );

            // Bắt sự kiện Xem thêm / Thu gọn của Mobile Path Detail
            $detail.off('click', '.ec-path-more-btn').on('click', '.ec-path-more-btn', function () {
                var currentChunk = parseInt($(this).attr('data-current-chunk'), 10);
                var maxChunk = parseInt($(this).attr('data-max-chunk'), 10);
                var nextChunk = currentChunk + 1;

                $detail.find('.ec-step-chunk-' + nextChunk).removeClass('ec-step-mobile-hidden');
                $(this).attr('data-current-chunk', nextChunk);
                $detail.find('.ec-path-less-btn').show();

                if (nextChunk >= maxChunk) $(this).hide();
            });

            $detail.off('click', '.ec-path-less-btn').on('click', '.ec-path-less-btn', function () {
                var maxChunk = parseInt($detail.find('.ec-path-more-btn').attr('data-max-chunk'), 10);
                for (var c = 1; c <= maxChunk; c++) {
                    $detail.find('.ec-step-chunk-' + c).addClass('ec-step-mobile-hidden');
                }
                $detail.find('.ec-path-more-btn').attr('data-current-chunk', 0).show();
                $(this).hide();
            });
        }

        function formatJourneyDate(str) {
            if (!str) return '';
            try {
                // Expected format from DB: "YYYY-MM-DD HH:mm:ss"
                var parts = str.split(' ');
                if (parts.length === 2) {
                    var dParts = parts[0].split('-');
                    if (dParts.length === 3) {
                        var timePart = parts[1].substring(0, 5); // HH:mm
                        return dParts[2] + '/' + dParts[1] + '/' + dParts[0] + ' ' + timePart;
                    }
                }
                return str;
            } catch (e) { return str; }
        }

        function formatDuration(secs) {
            secs = parseInt(secs) || 0;
            if (secs <= 0) return 'N/A';
            var m = Math.floor(secs / 60);
            var s = secs % 60;
            return m > 0 ? (m + 'm ' + s + 's') : (s + 's');
        }

        // ── IP Management Modal ──────────────────────────────────
        (function () {
            var OVERLAY = '#ec_ip_modal_overlay';
            var currentIp = '';

            // Map logical action → page-api.php action param.
            // To add a new action: add entry here + elseif block in page-api.php + button in tpl.
            var ACTION_MAP = {
                block:   'block_ip',
                allow:   'allow_ip',
                unblock: 'unblock_ip',
            };
            var TIMED_ACTIONS = ['block', 'allow'];

            function open(ip) {
                currentIp = ip;
                $('#ec_ipm_ip').text(ip);
                $('#ec_ipm_domain').text(currentSiteKey || '—');
                $('#ec_ipm_msg').text('').removeClass('is-ok is-err');
                $(OVERLAY).addClass('is-open');
            }

            function close() {
                $(OVERLAY).removeClass('is-open');
                currentIp = '';
            }

            function setMsg(ok, text) {
                $('#ec_ipm_msg')
                    .text(text)
                    .removeClass('is-ok is-err')
                    .addClass(ok ? 'is-ok' : 'is-err');
            }

            var ACTION_LABEL = {
                block:   'Chặn',
                allow:   'Cho phép',
                unblock: 'Gỡ chặn',
            };

            function doAction(action) {
                if (!currentIp) return;
                var apiAction = ACTION_MAP[action];
                if (!apiAction) return;

                var isTimed = TIMED_ACTIONS.indexOf(action) !== -1;
                var params = { action: apiAction, ip: currentIp };
                var durationLabel = '';
                if (isTimed) {
                    params.dur = parseInt($('#ec_ipm_duration').val(), 10) || 86400;
                    durationLabel = $('#ec_ipm_duration option:selected').text();
                }

                var label = ACTION_LABEL[action] || action;

                setMsg(true, 'Đang xử lý...');
                callPageApi(params)
                    .done(function (res) {
                        var ok = res.error === 0;
                        var msg = label + (ok ? ' thành công' : ' thất bại');
                        if (ok && durationLabel) msg += ' · ' + durationLabel;
                        setMsg(ok, msg);
                    })
                    .fail(function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) || ('Lỗi HTTP ' + xhr.status);
                        setMsg(false, label + ' thất bại · ' + msg);
                    });
            }

            $(document).on('click', '.ec-open-ip-modal', function () {
                var ip = $(this).data('ip');
                if (!ip) { alert('Không có IP để quản lý.'); return; }
                open(ip);
            });

            $(document).on('click', '#ec_ipm_block',   function () { doAction('block');   });
            $(document).on('click', '#ec_ipm_allow',   function () { doAction('allow');   });
            $(document).on('click', '#ec_ipm_unblock', function () { doAction('unblock'); });
            $(document).on('click', '#ec_ipm_cancel',  close);
            $(document).on('click', OVERLAY, function (e) {
                if ($(e.target).is(OVERLAY)) close();
            });
        })();
    })();

    // ── IP Manage Tab ────────────────────────────────────────
    (function () {
        var $blockedTbody = $('#ec_ipm_blocked_tbody');
        var $allowedTbody = $('#ec_ipm_allowed_tbody');

        function proxyCall(action, ip) {
            var payload = { site_key: currentSiteKey, action: action };
            if (ip) payload.ip = ip;
            return $.ajax({
                url: 'index.php?entryPoint=entryPointIpManage',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                dataType: 'json',
                timeout: 15000,
            });
        }

        function fmt(dt) {
            if (!dt) return '—';
            return dt.replace('T', ' ').substring(0, 16);
        }

        function renderBlocked(rows) {
            if (!rows || rows.length === 0) {
                $blockedTbody.html('<tr><td colspan="4" class="uat-empty-cell">Không có IP nào đang bị chặn.</td></tr>');
                return;
            }
            $blockedTbody.empty();
            rows.forEach(function (row) {
                $blockedTbody.append(`
                    <tr>
                        <td style="padding-left:24px; font-weight:600; color:#ef4444;">${escH(row.id)}</td>
                        <td style="color:#64748b; font-size:13px;">${fmt(row.block_from)}</td>
                        <td style="color:#64748b; font-size:13px;">${fmt(row.block_to)}</td>
                        <td style="text-align:center;">
                            <button class="uat-btn ec-ipm-row-action" data-action="unblock_ip" data-ip="${escH(row.id)}"
                                    style="background:#64748b;color:#fff;border:none;padding:0 14px;height:28px;border-radius:6px;font-weight:600;font-size:12px;cursor:pointer;">
                                Bỏ chặn
                            </button>
                        </td>
                    </tr>`);
            });
        }

        function renderAllowed(rows) {
            if (!rows || rows.length === 0) {
                $allowedTbody.html('<tr><td colspan="4" class="uat-empty-cell">Không có IP nào đang được cho phép.</td></tr>');
                return;
            }
            $allowedTbody.empty();
            rows.forEach(function (row) {
                $allowedTbody.append(`
                    <tr>
                        <td style="padding-left:24px; font-weight:600; color:#10b981;">${escH(row.id)}</td>
                        <td style="color:#64748b; font-size:13px;">${fmt(row.allow_from)}</td>
                        <td style="color:#64748b; font-size:13px;">${fmt(row.allow_to)}</td>
                        <td style="text-align:center;">
                            <button class="uat-btn ec-ipm-row-action" data-action="disallow_ip" data-ip="${escH(row.id)}"
                                    style="background:#f59e0b;color:#fff;border:none;padding:0 14px;height:28px;border-radius:6px;font-weight:600;font-size:12px;cursor:pointer;">
                                Xóa
                            </button>
                        </td>
                    </tr>`);
            });
        }

        window.loadIpManage = function () {
            $blockedTbody.html('<tr><td colspan="4" class="uat-empty-cell">Đang tải...</td></tr>');
            $allowedTbody.html('<tr><td colspan="4" class="uat-empty-cell">Đang tải...</td></tr>');

            proxyCall('get_blocked_ips').done(function (res) {
                renderBlocked(res.data || []);
            }).fail(function () {
                $blockedTbody.html('<tr><td colspan="4" class="uat-empty-cell">Lỗi tải dữ liệu.</td></tr>');
            });

            proxyCall('get_allowed_ips').done(function (res) {
                renderAllowed(res.data || []);
            }).fail(function () {
                $allowedTbody.html('<tr><td colspan="4" class="uat-empty-cell">Lỗi tải dữ liệu.</td></tr>');
            });
        };

        $(document).on('click', '.ec-ipm-row-action', function () {
            var $btn = $(this);
            var action = $btn.data('action');
            var ip     = $btn.data('ip');
            $btn.prop('disabled', true).text('...');
            proxyCall(action, ip)
                .done(function (res) {
                    if (res.error === 0) {
                        window.loadIpManage();
                    } else {
                        $btn.prop('disabled', false).text(action === 'unblock_ip' ? 'Bỏ chặn' : 'Xóa');
                    }
                })
                .fail(function () {
                    $btn.prop('disabled', false).text(action === 'unblock_ip' ? 'Bỏ chặn' : 'Xóa');
                });
        });
    })();
});
