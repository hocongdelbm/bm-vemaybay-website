<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="title">Hành trình theo quốc gia</h1>
</div>

{literal}<script type="text/javascript">window.REPORT_ROUTE = {/literal}{$REPORT_JSON}{literal};</script>{/literal}

<div class="box-section position-relative">
    <div class="filter-section">
        <form action="index.php" method="post" name="ec_route_search_form" id="ec_search_form">
            <input type="hidden" name="module" value="EC_Flight_Bookings"/>
            <input type="hidden" name="action" value="report_route_analysis"/>
            <div class="filter-row">
                <div class="filter-select-wrap">
                    <select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>
                </div>
                <div class="filter-dates">
                    <div class="filter-date-group">
                        <span class="date-label">Từ ngày: </span>
                        <div class="dateTime position-relative">
                            <input class="date_input box-input" type="text" maxlength="10" size="10" tabindex="103" value="{$FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
                            <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/><path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/></svg>
                            </button>
                        </div>
                    </div>
                    <span class="filter-arrow text-dark fw-bold">→</span>
                    <div class="filter-date-group">
                        <span class="date-label">Đến ngày: </span>
                        <div class="dateTime position-relative">
                            <input class="date_input box-input" type="text" maxlength="10" size="10" tabindex="104" value="{$TO_DATE}" id="to_date" name="to_date" autocomplete="off">
                            <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/><path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="filter-submit">
                    <input type="submit" class="btn btn-primary px-4 fw-bold" name="btnSearch" id="btnSearch" value="Xem báo cáo" title="Xem báo cáo" />
                </div>
            </div>
        </form>
        <ul class="filter-notes">
            <li><b>BK hoàn tất</b> = booking ở trạng thái <b>Xác nhận / Xuất vé / Hoàn tất</b>.</li>
            <li>Khoảng thời gian lọc theo <b>ngày tạo booking</b>.</li>
        </ul>
    </div>

    {* ===== Tabs Tổng quan / Quốc tế / Nội địa ===== *}
    <ul class="nav nav-tabs report-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link report-tab-link active" data-tab="overview">
                Tổng quan
            </a>
        </li>
        {foreach from=$GROUPS key=gkey item=grp name=navloop}
        <li class="nav-item">
            <a class="nav-link report-tab-link" data-tab="{$gkey}">
                {$grp.title} <span class="badge bg-primary badge-count ms-2">{$grp.count}</span>
            </a>
        </li>
        {/foreach}
    </ul>

    {* ===== Tab pane: Tổng quan ===== *}
    <div class="report-tab-pane" id="tab-pane-overview">

        {* ===== Top 9 hành trình có BK hoàn tất nhiều nhất (kỳ chọn) ===== *}
        {if $TOP_ROUTES}
        <div class="top-box">
            <h5 class="top-box-title"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5q0 .807-.034 1.536a3 3 0 1 1-2.026 5.091 7 7 0 0 1-.853.9A7 7 0 0 1 8 9.967a7 7 0 0 1-2.587-1.94 7 7 0 0 1-.853-.9A3 3 0 1 1 2.534 2.036Q2.5 1.307 2.5.5m1.068.534a7 7 0 0 0-.574 3.14A3 3 0 0 1 5.174 5.4a7 7 0 0 1-.782-1.09c-.2-.345-.371-.7-.512-1.063A3 3 0 0 0 3.568 1.034M8 14.41c1.474-1.097 3.5-3.396 3.5-6.41C11.5 4.865 10.05 2.5 8 2.5S4.5 4.865 4.5 8c0 3.014 2.026 5.313 3.5 6.41m-.96-8.987A3 3 0 0 0 4.537 4.18a7 7 0 0 0-.512 1.063 7 7 0 0 1-.782 1.09A3 3 0 0 1 5.42 4.17a7 7 0 0 1 1.62-1.747M8 12.5a.5.5 0 0 1-.5-.5V9a.5.5 0 0 1 1 0v3a.5.5 0 0 1-.5.5"/><path d="M6.94 7.44a1.5 1.5 0 1 1 2.12 2.12 1.5 1.5 0 0 1-2.12-2.12"/></svg> Top hành trình có BK hoàn tất nhiều nhất ({$P0_LABEL})</h5>
            <div class="row g-2">
                {foreach from=$TOP_ROUTES item=tr name=topLoop}
                <div class="col-md-6 col-lg-4">
                    <div class="top-item {if $smarty.foreach.topLoop.iteration <= 3}top-item--gold{/if}">
                        <span class="top-rank">{$smarty.foreach.topLoop.iteration}</span>
                        <img src="https://flagcdn.com/w20/{$tr.cc|lower}.png" alt="{$tr.cc}" class="shadow-sm border">
                        <div class="top-route-info">
                            <span class="top-route-name">{$tr.dep} → {$tr.arr}</span>
                        </div>
                        <span class="top-bk-count">{$tr.p0_ok}</span>
                    </div>
                </div>
                {/foreach}
            </div>
        </div>
        {/if}

        {* ===== Route tăng mạnh nhất (Kỳ Chọn vs Kỳ Trước, BK hoàn tất) ===== *}
        {if $GROWTH_ROUTES}
        <div class="growth-box">
            <h5 class="text-success fw-semibold fs-6 d-flex align-items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5"/></svg> Hành trình tăng mạnh nhất (BK hoàn tất, {$P0_LABEL} vs {$P1_LABEL})</h5>
            <div class="row g-2">
                {foreach from=$GROWTH_ROUTES item=gr}
                <div class="col-md-6 col-lg-4">
                    <div class="growth-item">
                        <img src="https://flagcdn.com/w20/{$gr.cc|lower}.png" alt="{$gr.cc}" class="shadow-sm border">
                        <span class="growth-route">{$gr.dep} → {$gr.arr}</span>
                        <span class="ms-auto">
                            <span class="text-dark fw-semibold">{$gr.p1_ok} → {$gr.p0_ok}</span>
                            <span class="growth-pct">↑ {$gr.pct_str}</span>
                        </span>
                    </div>
                </div>
                {/foreach}
            </div>
        </div>
        {/if}

        {* ===== Cảnh báo: Route giảm mạnh nhất (Kỳ Chọn vs Kỳ Trước, BK hoàn tất) ===== *}
        {if $DECLINE_ROUTES}
        <div class="decline-box">
            <h5 class="text-danger fw-semibold fs-6 d-flex align-items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/><path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/></svg> Hành trình sụt giảm mạnh nhất (BK hoàn tất, {$P0_LABEL} vs {$P1_LABEL})</h5>
            <div class="row g-2">
                {foreach from=$DECLINE_ROUTES item=dr}
                <div class="col-md-6 col-lg-4">
                    <div class="decline-item">
                        <img src="https://flagcdn.com/w20/{$dr.cc|lower}.png" alt="{$dr.cc}" class="shadow-sm border">
                        <span class="decline-route">{$dr.dep} → {$dr.arr}</span>
                        <span class="ms-auto">
                            <span class="text-dark">{$dr.p1_ok} → {$dr.p0_ok}</span>
                            <span class="decline-pct">↓ {$dr.pct_str}</span>
                        </span>
                    </div>
                </div>
                {/foreach}
            </div>
        </div>
        {/if}

        {if !$TOP_ROUTES && !$GROWTH_ROUTES && !$DECLINE_ROUTES}
        <div class="text-center text-muted p-4">Không có dữ liệu tổng quan trong kỳ đã chọn.</div>
        {/if}
    </div>

    {* ===== Tab panes: Quốc tế / Nội địa ===== *}
    {foreach from=$GROUPS key=gkey item=grp name=paneloop}
    <div class="report-tab-pane" id="tab-pane-{$gkey}" style="display:none;">

        <div class="w-100 mb-4 bg-white p-3 border rounded">
            <canvas id="chartjs__report_{$gkey}" class="report-chart mx-auto"></canvas>
        </div>

        <div class="report-wrap mt-3 bg-white">
            <table class="data-list table m-0" cellspacing="0" cellpadding="0">
                <thead class="table-secondary">
                    <tr>
                        <th width="3%" class="text-center border-bottom-0"></th>
                        <th width="8%" class="border-bottom-0 align-middle">Quốc gia</th>
                        {foreach from=$HEAD_COLS item=col}
                        <th width="14%" class="text-center border-bottom-0" title="Mỗi ô: BK hoàn tất/Tổng BK · tỉ lệ chốt · số vé · Doanh số · TB/vé · TK (tham khảo)">
                            <span class="d-block text-{$col.color} fs-6 fw-semibold">{$col.label}</span>
                            <span class="text-dark fw-semibold">{$col.range}</span>
                        </th>
                        {/foreach}
                    </tr>
                </thead>
                <tbody>
                    {* ===== TỔNG CỘNG ===== *}
                    {if $grp.count > 1}
                    <tr class="total-line bg-light border-bottom border-2">
                        <td colspan="2" class="text-center align-middle fw-bold text-primary">TỔNG CỘNG</td>
                        {foreach from=$grp.total_cols item=col}
                        <td class="text-center border-start align-middle">
                            <div class="fw-bold text-{$col.color} fw-semibold">
                                <a href="#" class="show-bk-list text-{$col.color} text-decoration-none fw-bold" title="Số lượng BK hoàn tất" data-country="Toàn bộ {$grp.title}" data-period="{$col.label}" data-scope="{$grp.scope}" data-status="completed" data-fromdate="{$col.f}" data-todate="{$col.t}">{$col.bk_ok}</a><span class="text-dark">&nbsp;/&nbsp;</span><a href="#" class="show-bk-list text-dark text-decoration-none" title="Tổng số BK" data-country="Toàn bộ {$grp.title}" data-period="{$col.label}" data-scope="{$grp.scope}" data-status="" data-fromdate="{$col.f}" data-todate="{$col.t}">{$col.bk}</a>
                            </div>
                            <ul class="metric-list">
                                <li title="Vé hoàn tất / Tổng số vé"><span class="ml-label">Số vé</span><span class="ml-value">{$col.ticket_str}</span></li>
                                <li class="ml-rev" title="Doanh số BK"><span class="ml-label">Doanh số</span><span class="ml-value">{$col.profit_str}</span></li>
                                <li title="BK tham khảo hoàn tất / Tổng BK tham khảo"><span class="ml-label">Tham khảo</span><span class="ml-value">{$col.ref_str}</span></li>
                                <li title="Booker đặt: BK do booker đặt — hoàn tất / tổng"><span class="ml-label">Booker</span><span class="ml-value">{$col.booker_str}</span></li>
                                <li title="Khách đặt: BK khách tự đặt — hoàn tất / tổng"><span class="ml-label">Khách</span><span class="ml-value">{$col.customer_str}</span></li>
                            </ul>
                            {if $col.change_type == 'up'}<div class="metric-change text-up" title="% tăng BK hoàn tất so với kỳ liền trước">↑ {$col.change_val}</div>
                            {elseif $col.change_type == 'down'}<div class="metric-change text-down" title="% giảm BK hoàn tất so với kỳ liền trước">↓ {$col.change_val}</div>
                            {elseif $col.change_type == 'none'}<div class="metric-change text-none" title="BK hoàn tất không đổi so với kỳ liền trước">-</div>{/if}
                        </td>
                        {/foreach}
                    </tr>
                    {/if}

                    {* ===== Các quốc gia ===== *}
                    {foreach from=$grp.data item=data}
                    <tr class="main-line" title="Click vào 1 kỳ để xem chi tiết route">
                        <td class="text-center align-middle toggle-icon text-dark period-col" data-period="p0" data-label="{$P0_LABEL}">▶</td>
                        <td class="align-middle period-col" data-period="p0" data-label="{$P0_LABEL}">
                            <img src="https://flagcdn.com/w20/{$data.cc|lower}.png" alt="{$data.cc}" class="me-2 shadow-sm border">
                            <span class="fw-bold">{$data.name}</span>
                        </td>
                        {foreach from=$data.columns item=col}
                        <td class="text-center border-start period-col" data-period="{$col.pid}" data-label="{$col.label}">
                            <div class="metric-bk text-{$col.color} text-dark">
                                <a href="#" class="show-bk-list text-{$col.color}" title="Xem BK hoàn tất" data-country="{$data.name}" data-period="{$col.label}" data-scope="country" data-dest-country="{$data.cc}" data-group="{$gkey}" data-status="completed" data-fromdate="{$col.f}" data-todate="{$col.t}">{$col.bk_ok}</a><span class="text-dark">&nbsp;/&nbsp;</span><a href="#" class="show-bk-list text-dark" title="Xem tất cả BK" data-country="{$data.name}" data-period="{$col.label}" data-scope="country" data-dest-country="{$data.cc}" data-group="{$gkey}" data-status="" data-fromdate="{$col.f}" data-todate="{$col.t}">{$col.bk}</a>
                            </div>
                            <ul class="metric-list">
                                <li title="Vé hoàn tất / Tổng số vé"><span class="ml-label">Số Vé</span><span class="ml-value">{$col.ticket_str}</span></li>
                                <li class="ml-rev" title="Doanh số BK"><span class="ml-label">Doanh số</span><span class="ml-value">{$col.profit_str}</span></li>
                                <li title="BK tham khảo hoàn tất / Tổng BK tham khảo"><span class="ml-label">Tham khảo</span><span class="ml-value">{$col.ref_str}</span></li>
                                <li title="Booker đặt: BK do booker tự đặt — hoàn tất / tổng"><span class="ml-label">Booker</span><span class="ml-value">{$col.booker_str}</span></li>
                                <li title="Khách đặt: BK khách tự đặt — hoàn tất / tổng"><span class="ml-label">Khách</span><span class="ml-value">{$col.customer_str}</span></li>
                            </ul>
                            {if $col.change_type == 'up'}<div class="metric-change text-up" title="% tăng BK hoàn tất so với kỳ liền trước">↑ {$col.change_val}</div>
                            {elseif $col.change_type == 'down'}<div class="metric-change text-down" title="% giảm BK hoàn tất so với kỳ liền trước">↓ {$col.change_val}</div>
                            {elseif $col.change_type == 'none'}<div class="metric-change text-none" title="BK hoàn tất không đổi so với kỳ liền trước">-</div>{/if}
                        </td>
                        {/foreach}
                    </tr>

                    {* Chi tiết route (ẩn mặc định) *}
                    <tr class="detail-row" style="display: none;">
                        <td colspan="8" class="p-3 bg-light border-bottom">
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm route-search" placeholder="Tìm hành trình (mã sân bay / thành phố)..." style="max-width:340px;" autocomplete="off">
                            </div>
                            <div class="table-responsive"><table class="table table-sm table-bordered bg-white mb-0 detail-table">
                                <thead class="table-warning">
                                    <tr>
                                        <th width="5%" class="text-center p-2">#</th>
                                        <th width="12%" class="text-center p-2">Nơi đi</th>
                                        <th width="12%" class="text-center p-2">Nơi đến</th>
                                        <th width="9%" class="text-center p-2" title="BK hoàn tất / Tổng BK thật">Booking <span class="period-name-header text-primary">{$P0_LABEL}</span></th>
                                        <th width="7%" class="text-center p-2" title="Tỉ lệ chốt = BK hoàn tất / Tổng BK">Tỉ lệ chốt</th>
                                        <th width="7%" class="text-center p-2" title="Khách tự cung cấp thông tin (chưa từng qua booker xử lý) — hoàn tất / tổng">Khách đặt</th>
                                        <th width="7%" class="text-center p-2" title="BK do web/bot tạo (tên KH tạm) rồi nhân viên xử lý — hoàn tất / tổng">Booker đặt</th>
                                        <th width="7%" class="text-center p-2" title="BK tham khảo hoàn tất / Tổng BK tham khảo của hành trình">Tham khảo</th>
                                        <th width="8%" class="text-center p-2" title="Vé hoàn tất / Tổng số vé">Vé</th>
                                        <th width="10%" class="text-center p-2" title="Doanh số">Doanh số</th>
                                        <th width="10%" class="text-center p-2" title="Trung bình = doanh số / số vé HT">Trung bình / vé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$data.routes item=route name=routeLoop}
                                    <tr class="route-item route-row-{$route.token}" data-search="{$route.dep|lower} {$route.arr|lower}"{foreach from=$route.columns item=rc} data-{$rc.pid}-bk="{$rc.bk_str}" data-{$rc.pid}-bkok="{$rc.bk_ok}" data-{$rc.pid}-bkall="{$rc.bk}" data-{$rc.pid}-conv="{$rc.conv}" data-{$rc.pid}-ticket="{$rc.ticket_str}" data-{$rc.pid}-ref="{$rc.ref_str}" data-{$rc.pid}-booker="{$rc.booker_str}" data-{$rc.pid}-customer="{$rc.customer_str}" data-{$rc.pid}-profit="{$rc.profit_str}" data-{$rc.pid}-avg="{$rc.avg_str}" data-{$rc.pid}-f="{$rc.f}" data-{$rc.pid}-t="{$rc.t}"{/foreach}>
                                        <td class="text-center align-middle">{$smarty.foreach.routeLoop.iteration}</td>
                                        <td class="text-start align-middle fw-bold">{$route.dep}</td>
                                        <td class="text-start align-middle fw-bold">{$route.arr}</td>
                                        <td class="text-center align-middle">
                                            <a href="#" class="show-bk-list route-bk-ok fw-bold text-decoration-none" title="Xem BK hoàn tất" data-country="{$data.name} ({$route.dep_code}-{$route.arr_code})" data-period="{$P0_LABEL}" data-dep="{$route.dep_code}" data-arr="{$route.arr_code}" data-status="completed" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$route.bk_ok}</a><span class="text-dark">&nbsp;&nbsp;/&nbsp;&nbsp;</span><a href="#" class="show-bk-list route-bk-all fw-bold text-decoration-none" title="Xem tất cả BK" data-country="{$data.name} ({$route.dep_code}-{$route.arr_code})" data-period="{$P0_LABEL}" data-dep="{$route.dep_code}" data-arr="{$route.arr_code}" data-status="" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$route.bk}</a>
                                        </td>
                                        <td class="text-center align-middle route-conv text-dark" title="Tỉ lệ chốt = BK hoàn tất / Tổng BK">{$route.conv}</td>
                                        <td class="text-center align-middle" title="Khách tự cung cấp thông tin — hoàn tất / tổng">
                                            <a href="#" class="show-bk-list route-customer fw-bold text-decoration-none" data-country="{$data.name} ({$route.dep_code}-{$route.arr_code})" data-period="{$P0_LABEL}" data-dep="{$route.dep_code}" data-arr="{$route.arr_code}" data-status="customer" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$route.customer_str}</a>
                                        </td>
                                        <td class="text-center align-middle" title="BK do web/bot tạo (tên KH tạm) rồi nhân viên xử lý — hoàn tất / tổng">
                                            <a href="#" class="show-bk-list route-booker fw-bold text-decoration-none" data-country="{$data.name} ({$route.dep_code}-{$route.arr_code})" data-period="{$P0_LABEL}" data-dep="{$route.dep_code}" data-arr="{$route.arr_code}" data-status="booker" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$route.booker_str}</a>
                                        </td>
                                        <td class="text-center align-middle" title="BK tham khảo hoàn tất / Tổng BK tham khảo">
                                            <a href="#" class="show-bk-list route-ref fw-bold text-decoration-none" data-country="{$data.name} ({$route.dep_code}-{$route.arr_code})" data-period="{$P0_LABEL}" data-dep="{$route.dep_code}" data-arr="{$route.arr_code}" data-status="reference" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$route.ref_str}</a>
                                        </td>
                                        <td class="text-center align-middle route-ticket text-dark" title="Vé hoàn tất / Tổng số vé">{$route.ticket_str}</td>
                                        <td class="text-end align-middle metric-rev route-profit" title="Tổng doanh số của BK hoàn tất">{$route.profit_str}</td>
                                        <td class="text-end align-middle text-dark route-avg text-dark" title="Trung bình = doanh số / số vé hoàn tất">{$route.avg_str}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table></div>
                            {if $data.route_count > 10}
                            <div class="route-pagination-wrapper mt-2" data-token="{$gkey}-{$data.cc}"></div>
                            {/if}
                        </td>
                    </tr>
                    {/foreach}

                    {if $grp.count == 0}
                    <tr><td colspan="8" class="text-center text-dark p-4">Không có dữ liệu trong kỳ đã chọn.</td></tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
    {/foreach}
</div>

<!-- Modal -->
<div id="mock-modal" class="mock-modal">
    <div class="mock-modal-content">
        <div class="mock-modal-header">
            <h4 class="mock-modal-title" id="mock-modal-title-text">Danh sách Booking</h4>
            <button class="mock-modal-close" id="mock-modal-close-btn">&times;</button>
        </div>
        <div id="mock-modal-body" class="mock-modal-body"></div>
    </div>
</div>
