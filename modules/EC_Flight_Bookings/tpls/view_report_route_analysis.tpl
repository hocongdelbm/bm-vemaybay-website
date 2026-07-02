<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="title">Hành trình theo quốc gia</h1>
</div>

{literal}<script type="text/javascript">window.REPORT_ROUTE = {/literal}{$REPORT_JSON}{literal};</script>{/literal}

<div class="box-section position-relative">
    <div class="filter-section">
        <form action="index.php" method="post" name="search_form" id="ec_search_form">
            <input type="hidden" name="module" value="EC_Flight_Bookings"/>
            <input type="hidden" name="action" value="report_route_analysis"/>
            <div class="d-flex gap-4 align-items-center flex-wrap">
                <div class="d-flex align-items-center">
                    <select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <div class="d-flex gap-2 align-items-center">
                        <span class="date-label">Từ ngày: </span>
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input class="date_input box-input" type="text" maxlength="10" size="10" tabindex="103" value="{$FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
                            <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/><path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/></svg>
                            </button>
                        </div>
                    </div>
                    <span class="text-dark fw-bold">→</span>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="date-label">Đến ngày: </span>
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input class="date_input box-input" type="text" maxlength="10" size="10" tabindex="104" value="{$TO_DATE}" id="to_date" name="to_date" autocomplete="off">
                            <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/><path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <input type="submit" class="btn btn-primary px-4 fw-bold" name="btnSearch" id="btnSearch" value="Xem báo cáo" title="Xem báo cáo" />
                </div>
            </div>
        </form>
    </div>

    {* ===== Cảnh báo: Route giảm mạnh nhất (Kỳ Chọn vs Kỳ Trước, BK hoàn tất) ===== *}
    {if $DECLINE_ROUTES}
    <div class="decline-box">
        <h5 class="text-danger fw-semibold fs-6"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>Hành trình sụt giảm mạnh nhất (BK hoàn tất, {$P0_LABEL} vs {$P1_LABEL})</h5>
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

    {* ===== Tabs Quốc tế / Nội địa ===== *}
    <ul class="nav nav-tabs report-tabs mb-3">
        {foreach from=$GROUPS key=gkey item=grp name=navloop}
        <li class="nav-item">
            <a class="nav-link report-tab-link {if $smarty.foreach.navloop.first}active{/if}" data-tab="{$gkey}">
                {$grp.title} <span class="badge bg-primary badge-count ms-2">{$grp.count}</span>
            </a>
        </li>
        {/foreach}
    </ul>

    {foreach from=$GROUPS key=gkey item=grp name=paneloop}
    <div class="report-tab-pane" id="tab-pane-{$gkey}" {if !$smarty.foreach.paneloop.first}style="display:none;"{/if}>

        <div class="w-100 mb-4 bg-white p-3 border rounded">
            <canvas id="chartjs__report_{$gkey}" class="report-chart mx-auto"></canvas>
        </div>

        <div class="report-wrap mt-3 bg-white">
            <table class="data-list table m-0" cellspacing="0" cellpadding="0">
                <thead class="table-secondary">
                    <tr>
                        <th width="3%" class="text-center border-bottom-0"></th>
                        <th width="13%" class="border-bottom-0 align-middle">Quốc gia</th>
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
                                <a href="#" class="show-bk-list text-{$col.color} text-decoration-none fw-bold" title="Xem BK hoàn tất" data-country="Toàn bộ {$grp.title}" data-period="{$col.label}" data-scope="{$grp.scope}" data-status="completed" data-fromdate="{$col.f}" data-todate="{$col.t}">{$col.bk_ok}</a><span class="text-dark">&nbsp;/&nbsp;</span><a href="#" class="show-bk-list text-dark text-decoration-none" title="Xem tất cả BK" data-country="Toàn bộ {$grp.title}" data-period="{$col.label}" data-scope="{$grp.scope}" data-status="" data-fromdate="{$col.f}" data-todate="{$col.t}">{$col.bk}</a>
                            </div>
                            <div class="metric-sub mt-1" title="Vé hoàn tất/Tổng vé · Doanh số · TK = BK tham khảo">{$col.ticket_str} vé · DS <span class="metric-rev">{$col.profit_str}</span> · TK {$col.ref_str}</div>
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
                            <div class="metric-sub mt-1" title="Vé hoàn tất/Tổng vé · Doanh số · TK = BK tham khảo"><i class="fa fa-ticket" aria-hidden="true"></i> {$col.ticket_str} vé · DS <span class="metric-rev">{$col.profit_str}</span> · TK {$col.ref_str}</div>
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
                            <table class="table table-sm table-bordered bg-white mb-0">
                                <thead class="table-warning">
                                    <tr>
                                        <th width="5%" class="text-center p-2">#</th>
                                        <th width="12%" class="text-center p-2">Nơi đi</th>
                                        <th width="12%" class="text-center p-2">Nơi đến</th>
                                        <th width="10%" class="text-center p-2" title="BK hoàn tất / Tổng BK thật (không gồm tham khảo)">BK <span class="period-name-header text-primary">{$P0_LABEL}</span></th>
                                        <th width="8%" class="text-center p-2" title="BK tham khảo hoàn tất / Tổng BK tham khảo của hành trình">Tham khảo</th>
                                        <th width="8%" class="text-center p-2" title="Tỉ lệ chốt = BK hoàn tất / Tổng BK">Tỉ lệ chốt</th>
                                        <th width="8%" class="text-center p-2" title="Vé hoàn tất / Tổng số vé">Vé</th>
                                        <th width="10%" class="text-center p-2" title="Doanh số">Doanh số</th>
                                        <th width="10%" class="text-center p-2" title="Trung bình = doanh số / số vé HT">TB/vé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$data.routes item=route name=routeLoop}
                                    <tr class="route-item route-row-{$route.token}" data-search="{$route.dep|lower} {$route.arr|lower}"{foreach from=$route.columns item=rc} data-{$rc.pid}-bk="{$rc.bk_str}" data-{$rc.pid}-bkok="{$rc.bk_ok}" data-{$rc.pid}-bkall="{$rc.bk}" data-{$rc.pid}-conv="{$rc.conv}" data-{$rc.pid}-ticket="{$rc.ticket_str}" data-{$rc.pid}-ref="{$rc.ref_str}" data-{$rc.pid}-profit="{$rc.profit_str}" data-{$rc.pid}-avg="{$rc.avg_str}" data-{$rc.pid}-f="{$rc.f}" data-{$rc.pid}-t="{$rc.t}"{/foreach}>
                                        <td class="text-center align-middle">{$smarty.foreach.routeLoop.iteration}</td>
                                        <td class="text-start align-middle fw-bold">{$route.dep}</td>
                                        <td class="text-start align-middle fw-bold">{$route.arr}</td>
                                        <td class="text-center align-middle">
                                            <a href="#" class="show-bk-list route-bk-ok fw-bold text-decoration-none" title="Xem BK hoàn tất" data-country="{$data.name} ({$route.dep_code}-{$route.arr_code})" data-period="{$P0_LABEL}" data-dep="{$route.dep_code}" data-arr="{$route.arr_code}" data-status="completed" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$route.bk_ok}</a><span class="text-dark">&nbsp;&nbsp;/&nbsp;&nbsp;</span><a href="#" class="show-bk-list route-bk-all fw-bold text-decoration-none" title="Xem tất cả BK" data-country="{$data.name} ({$route.dep_code}-{$route.arr_code})" data-period="{$P0_LABEL}" data-dep="{$route.dep_code}" data-arr="{$route.arr_code}" data-status="" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$route.bk}</a>
                                        </td>
                                        <td class="text-center align-middle" title="BK tham khảo hoàn tất / Tổng BK tham khảo">
                                            <a href="#" class="show-bk-list route-ref fw-bold text-decoration-none" data-country="{$data.name} ({$route.dep_code}-{$route.arr_code})" data-period="{$P0_LABEL}" data-dep="{$route.dep_code}" data-arr="{$route.arr_code}" data-status="reference" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$route.ref_str}</a>
                                        </td>
                                        <td class="text-center align-middle route-conv text-dark" title="Tỉ lệ chốt = BK hoàn tất / Tổng BK">{$route.conv}</td>
                                        <td class="text-center align-middle route-ticket text-dark" title="Vé hoàn tất / Tổng số vé">{$route.ticket_str}</td>
                                        <td class="text-end align-middle metric-rev route-profit" title="Tổng doanh số của BK hoàn tất">{$route.profit_str}</td>
                                        <td class="text-end align-middle text-dark route-avg text-dark" title="Trung bình = doanh số / số vé hoàn tất">{$route.avg_str}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
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
