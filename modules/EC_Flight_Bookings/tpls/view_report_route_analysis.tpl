{literal}
<style>
    .title-wrap {
        border-bottom: 2px solid #0056b3;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .title-wrap .title {
        color: #0056b3;
        font-weight: 700;
        margin: 0;
    }

    .data-list {
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        border-radius: 8px;
        overflow: hidden;
    }

    .data-list thead tr th {
        position: sticky;
        top: -1px; 
        z-index: 2;
        background-color: #f4f6f9;
        border-bottom: 2px solid #dee2e6;
        padding: 12px;
        font-weight: 600;
    }

    .data-list thead tr:nth-child(2) th{
        top: 45px; 
    }

    .data-list thead tr.total-line th {
        background-color: #fff3cd;
        color: #856404;
        font-weight: 700;
    }

    .data-list tbody tr.main-line td {
        background-color: #fff;
        color: #333;
        font-weight: 500;
        cursor: pointer;
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s ease;
    }
    
    .data-list tbody tr.detail-row td {
        background-color: #f8f9fa;
        padding: 0;
        border-bottom: none;
    }
    
    .data-list tbody tr.detail-row table {
        margin-bottom: 0;
        background-color: transparent;
    }
    
    .data-list tbody tr.detail-row th {
        background-color: #e9ecef;
        font-size: 0.9em;
        position: static;
        padding: 8px;
        color: #495057;
    }
    
    .data-list tbody tr.detail-row td {
        font-size: 0.95em;
        background-color: transparent;
        border-bottom: 1px solid #dee2e6;
        padding: 8px;
    }

    .data-list tbody tr.main-line:hover {
        background-color: #e2e6ea;
    }

    .report-wrap {
        max-height: 65vh;
        overflow-y: auto;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    #chartjs__report {
        width: 100% !important;
        max-height: 350px !important;
    }
    
    .text-up { color: #198754; }
    .text-down { color: #dc3545; }
    .text-none { color: #6c757d; }
    
    .metric-bk { font-size: 1.15em; font-weight: 700; }
    .metric-bk a { color: #0d6efd; text-decoration: none; border-bottom: 1px dashed #0d6efd; }
    .metric-bk a:hover { color: #0a58ca; border-bottom: 1px solid #0a58ca; }
    
    .metric-ticket { font-size: 0.9em; color: #6c757d; margin-top: 4px; }
    .metric-change { font-size: 0.85em; font-weight: bold; margin-top: 4px; }
    .text-up { color: #198754; }
    .text-down { color: #dc3545; }
    .text-none { color: #6c757d; }
    
    .filter-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        margin-bottom: 20px;
    }
    
    .date-label { font-weight: 600; color: #495057; margin-right: 8px; }
    .box-input { border: 1px solid #ced4da; padding: 6px 10px; border-radius: 4px; font-size: 14px; }
    .box-select { border: 1px solid #ced4da; padding: 6px 10px; border-radius: 4px; font-size: 14px; background: #fff; }
    
    /* MOCK Modal */
    .mock-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1050; justify-content: center; align-items: center; }
    .mock-modal-content { background: #fff; padding: 20px; border-radius: 8px; width: 100%; max-width: 60vw; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    .mock-modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #dee2e6; padding-bottom: 10px; margin-bottom: 15px; flex-shrink: 0; }
    .mock-modal-title { margin: 0; font-size: 1.25rem; font-weight: bold; color: #0056b3; }
    .mock-modal-close { cursor: pointer; font-size: 1.5rem; color: #6c757d; border: none; background: none; }
    .mock-modal-body { overflow-y: auto; }
</style>

<script type="text/javascript">
$(document).ready(function() {
    // Accordion for rows
    $('.main-line td.period-col').click(function(e) {
        if ($(e.target).closest('a').length) return; // Ignore click on link
        
        let periodId = $(this).data('period');
        let periodLabel = $(this).data('label');
        
        let trMain = $(this).parent();
        let detailRow = trMain.next('.detail-row');
        let icon = trMain.find('.toggle-icon');
        
        // Update header label
        detailRow.find('.period-name-header').text(periodLabel);
        
        // Update all data cells based on period
        detailRow.find('.route-item').each(function() {
            let bkStr = $(this).attr('data-' + periodId + '-bk');
            let ticketStr = $(this).attr('data-' + periodId + '-ticket');
            let fromF = $(this).attr('data-' + periodId + '-f');
            let toT = $(this).attr('data-' + periodId + '-t');
            
            // Update link
            let link = $(this).find('.show-bk-list');
            link.text(bkStr);
            link.data('period', periodLabel);
            link.data('fromdate', fromF);
            link.data('todate', toT);
            
            // Also update attributes so jQuery click handler (which uses .data()) gets the updated values
            link.attr('data-period', periodLabel);
            link.attr('data-fromdate', fromF);
            link.attr('data-todate', toT);
            
            // Update ticket
            $(this).find('.route-ticket').text(ticketStr);
        });
        
        if (detailRow.is(':visible') && detailRow.data('active-period') === periodId) {
            detailRow.fadeOut(200);
            icon.html('▶');
            return;
        }
        
        detailRow.data('active-period', periodId);
        
        if (!detailRow.is(':visible')) {
            detailRow.fadeIn(200);
            icon.html('▼');
        }
    });
    
    // JS cho Date Select Dropdown
    $(document).on("change", "#date_select", function(e) {
        const $opt = $(this).find('option:selected');
        const from = $opt.attr('fromdate');
        const to = $opt.attr('todate');
        if(from) $('#from_date').val(from);
        if(to) $('#to_date').val(to);
    });

    // Real AJAX click xem danh sách BK
    $(document).on('click', '.show-bk-list', function(e) {
        e.preventDefault();
        const country = $(this).attr('data-country');
        const period = $(this).attr('data-period');
        
        const dep = $(this).attr('data-dep') || '';
        const arr = $(this).attr('data-arr') || '';
        const scope = $(this).attr('data-scope') || '';
        const destCountry = $(this).attr('data-dest-country') || '';
        const fromDate = $(this).attr('data-fromdate');
        const toDate = $(this).attr('data-todate');
        
        $('#mock-modal-title-text').text(`Danh sách Booking - ${country} (${period})`);
        $('#mock-modal-body').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Đang tải dữ liệu...</div>');
        $('#mock-modal').css('display', 'flex');
        
        $.ajax({
            url: "index.php?entryPoint=entryPointFlightBookings",
            type: "POST",
            data: {
                departure: dep,
                arrival: arr,
                scope: scope,
                dest_country: destCountry,
                from_date: fromDate,
                to_date: toDate,
                for: 'getDetailsAirportStatistics'
            },
            success: function(response) {
                $('#mock-modal-body').html(response);
            },
            error: function() {
                $('#mock-modal-body').html('<div class="text-danger text-center p-4">Có lỗi xảy ra khi tải dữ liệu!</div>');
            }
        });
    });
    
    $('#mock-modal-close-btn').click(function() {
        $('#mock-modal').hide();
    });

    // Chart.js init
    const ctx = document.getElementById('chartjs__report').getContext('2d');
    
    const chartLabels = {/literal}{$CHART_LABELS}{literal};
    const chartDataP0 = {/literal}{$CHART_DATA_P0}{literal};
    const chartDataP1 = {/literal}{$CHART_DATA_P1}{literal};
    const chartDataP2 = {/literal}{$CHART_DATA_P2}{literal};
    const chartDataP3 = {/literal}{$CHART_DATA_P3}{literal};
    const chartDataP4 = {/literal}{$CHART_DATA_P4}{literal};
    const chartDataP5 = {/literal}{$CHART_DATA_P5}{literal};
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: '{/literal}{$P5_LABEL}{literal}',
                    data: chartDataP5,
                    backgroundColor: 'rgba(220, 53, 69, 0.5)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1,
                    hidden: true
                },
                {
                    label: '{/literal}{$P4_LABEL}{literal}',
                    data: chartDataP4,
                    backgroundColor: 'rgba(25, 135, 84, 0.5)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1,
                    hidden: true
                },
                {
                    label: '{/literal}{$P3_LABEL}{literal}',
                    data: chartDataP3,
                    backgroundColor: 'rgba(108, 117, 125, 0.5)',
                    borderColor: 'rgba(108, 117, 125, 1)',
                    borderWidth: 1,
                    hidden: true
                },
                {
                    label: '{/literal}{$P2_LABEL}{literal}',
                    data: chartDataP2,
                    backgroundColor: 'rgba(23, 162, 184, 0.6)',
                    borderColor: 'rgba(23, 162, 184, 1)',
                    borderWidth: 1
                },
                {
                    label: '{/literal}{$P1_LABEL}{literal}',
                    data: chartDataP1,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                },
                {
                    label: '{/literal}{$P0_LABEL}{literal}',
                    data: chartDataP0,
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: Math.round,
                    font: { weight: 'bold' }
                },
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Số Booking' } }
            }
        }
    });
    // Pagination logic for route tables
    $('.route-pagination-wrapper').each(function() {
        const countryCode = $(this).data('country');
        const rows = $(`.route-row-${countryCode}`);
        const totalRows = rows.length;
        const perPage = 10;
        const wrapper = $(this);
        
        let totalPages = Math.ceil(totalRows / perPage);
        let currentPage = 1;
        
        function showPage(page) {
            rows.hide();
            rows.slice((page-1)*perPage, page*perPage).show();
            renderPagination(page);
        }
        
        function renderPagination(page) {
            let html = '<ul class="pagination pagination-sm justify-content-center mb-0">';
            html += `<li class="page-item ${page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${page-1}">«</a></li>`;
            
            let start = Math.max(1, page - 2);
            let end = Math.min(totalPages, page + 2);
            
            if(start > 1) html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li><li class="page-item disabled"><span class="page-link">...</span></li>`;
            
            for(let i=start; i<=end; i++) {
                html += `<li class="page-item ${i === page ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
            
            if(end < totalPages) html += `<li class="page-item disabled"><span class="page-link">...</span></li><li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
            
            html += `<li class="page-item ${page === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${page+1}">»</a></li>`;
            html += '</ul>';
            
            wrapper.html(html);
        }
        
        wrapper.on('click', '.page-link', function(e) {
            e.preventDefault();
            let p = $(this).data('page');
            if(p && p >= 1 && p <= totalPages) showPage(p);
        });
        
        showPage(1);
    });

});
</script>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="title">Hành trình theo quốc gia</h1>
</div>

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
                            <input class="date_input box-input" type="text" maxlength="10" size="10" tabindex="103" title="" value="{$FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
                            <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/><path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/></svg>
                            </button>
                            {literal}
                            <script type="text/javascript">
                                Calendar.setup({ inputField: "from_date", daFormat: "%d-%m-%Y", button: "fdate_trigger", singleClick: true, dateStr: "", step: 1 });
                            </script>
                            {/literal}
                        </div>
                    </div>
                    
                    <span class="text-dark fw-bold">→</span>
                    
                    <div class="d-flex gap-2 align-items-center">
                        <span class="date-label">Đến ngày: </span>    
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input class="date_input box-input" type="text" maxlength="10" size="10" tabindex="104" title="" value="{$TO_DATE}" id="to_date" name="to_date" autocomplete="off">
                            <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/><path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/></svg>
                            </button>
                            {literal}
                            <script type="text/javascript">
                                Calendar.setup({ inputField: "to_date", daFormat: "%d-%m-%Y", button: "tdate_trigger", singleClick: true, dateStr: "", step: 1 });
                            </script>
                            {/literal}
                        </div>
                    </div>
                </div>
                
                <div>
                    <input type="submit" class="btn btn-primary px-4 fw-bold" name="btnSearch" id="btnSearch" value="Xem báo cáo" title="Xem báo cáo" />
                </div>
            </div>
        </form>
    </div>

    <div class="box-data">
        <div class="chartjs__report--wrap w-100 mb-4 bg-white p-3 border rounded shadow-sm">
           <canvas id="chartjs__report" class="mx-auto"></canvas>
        </div>

        <div class="report-wrap mt-3 bg-white shadow-sm">
            <table class="data-list table w-100 m-0" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th width="3%" class="text-center border-bottom-0"></th>
                        <th width="13%" class="border-bottom-0 align-middle">Quốc gia</th>
                        <th width="14%" class="text-center border-bottom-0">
                            <span class="d-block text-primary fs-6">{$P0_LABEL}</span>
                            <span class="text-dark fw-normal">{$P0_RANGE}</span>
                        </th>
                        <th width="14%" class="text-center border-bottom-0">
                            <span class="d-block text-warning fs-6 text-dark">{$P1_LABEL}</span>
                            <span class="text-dark fw-normal">{$P1_RANGE}</span>
                        </th>
                        <th width="14%" class="text-center border-bottom-0">
                            <span class="d-block text-info fs-6 text-dark">{$P2_LABEL}</span>
                            <span class="text-dark fw-normal">{$P2_RANGE}</span>
                        </th>
                        <th width="14%" class="text-center border-bottom-0">
                            <span class="d-block text-secondary fs-6 text-dark">{$P3_LABEL}</span>
                            <span class="text-dark fw-normal">{$P3_RANGE}</span>
                        </th>
                        <th width="14%" class="text-center border-bottom-0">
                            <span class="d-block text-success fs-6 text-dark">{$P4_LABEL}</span>
                            <span class="text-dark fw-normal">{$P4_RANGE}</span>
                        </th>
                        <th width="14%" class="text-center border-bottom-0">
                            <span class="d-block text-danger fs-6 text-dark">{$P5_LABEL}</span>
                            <span class="text-dark fw-normal">{$P5_RANGE}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="total-line fs-5 bg-light border-bottom border-2">
                        <td colspan="2" class="text-center align-middle fw-bold text-primary">TỔNG CỘNG</td>
                        <td class="text-center border-start align-middle">
                            <div class="fw-bold text-primary">
                                <a href="#" class="show-bk-list text-primary text-decoration-none" data-country="Tất cả quốc gia" data-period="{$P0_LABEL}" data-scope="all" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$TOTAL_DATA.p0.bk_ok}/{$TOTAL_DATA.p0.bk}</a>
                            </div>
                            <div class="fs-6 fw-normal mt-1 text-dark">{$TOTAL_DATA.p0.ticket} vé</div>
                            {if $TOTAL_DATA.p0_change.type == 'up'}
                                <div class="metric-change text-up">↑ {$TOTAL_DATA.p0_change.val} <small class="text-dark fw-normal">(vs. Kỳ Chọn -1)</small></div>
                            {elseif $TOTAL_DATA.p0_change.type == 'down'}
                                <div class="metric-change text-down">↓ {$TOTAL_DATA.p0_change.val} <small class="text-dark fw-normal">(vs. Kỳ Chọn -1)</small></div>
                            {else}
                                <div class="metric-change text-none">- <small class="text-dark fw-normal">(vs. Kỳ Chọn -1)</small></div>
                            {/if}
                        </td>
                        <td class="text-center border-start align-middle">
                            <div class="fw-bold text-warning text-dark">
                                <a href="#" class="show-bk-list text-warning text-dark text-decoration-none" data-country="Tất cả quốc gia" data-period="{$P1_LABEL}" data-scope="all" data-fromdate="{$P1_F}" data-todate="{$P1_T}">{$TOTAL_DATA.p1.bk_ok}/{$TOTAL_DATA.p1.bk}</a>
                            </div>
                            <div class="fs-6 fw-normal mt-1 text-dark">{$TOTAL_DATA.p1.ticket} vé</div>
                            {if $TOTAL_DATA.p1_change.type == 'up'}
                                <div class="metric-change text-up">↑ {$TOTAL_DATA.p1_change.val} <small class="text-dark fw-normal">(vs. Kỳ Chọn -2)</small></div>
                            {elseif $TOTAL_DATA.p1_change.type == 'down'}
                                <div class="metric-change text-down">↓ {$TOTAL_DATA.p1_change.val} <small class="text-dark fw-normal">(vs. Kỳ Chọn -2)</small></div>
                            {else}
                                <div class="metric-change text-none">- <small class="text-dark fw-normal">(vs. Kỳ Chọn -2)</small></div>
                            {/if}
                        </td>
                        <td class="text-center border-start align-middle">
                            <div class="fw-bold text-info text-dark">
                                <a href="#" class="show-bk-list text-info text-dark text-decoration-none" data-country="Tất cả quốc gia" data-period="{$P2_LABEL}" data-scope="all" data-fromdate="{$P2_F}" data-todate="{$P2_T}">{$TOTAL_DATA.p2.bk_ok}/{$TOTAL_DATA.p2.bk}</a>
                            </div>
                            <div class="fs-6 fw-normal mt-1 text-dark">{$TOTAL_DATA.p2.ticket} vé</div>
                        </td>
                        <td class="text-center border-start align-middle">
                            <div class="fw-bold text-secondary text-dark">
                                <a href="#" class="show-bk-list text-secondary text-dark text-decoration-none" data-country="Tất cả quốc gia" data-period="{$P3_LABEL}" data-scope="all" data-fromdate="{$P3_F}" data-todate="{$P3_T}">{$TOTAL_DATA.p3.bk_ok}/{$TOTAL_DATA.p3.bk}</a>
                            </div>
                            <div class="fs-6 fw-normal mt-1 text-dark">{$TOTAL_DATA.p3.ticket} vé</div>
                            {if $TOTAL_DATA.p3_change.type == 'up'}
                                <div class="metric-change text-up">↑ {$TOTAL_DATA.p3_change.val} <small class="text-dark fw-normal">(vs. Tuần Trước -1)</small></div>
                            {elseif $TOTAL_DATA.p3_change.type == 'down'}
                                <div class="metric-change text-down">↓ {$TOTAL_DATA.p3_change.val} <small class="text-dark fw-normal">(vs. Tuần Trước -1)</small></div>
                            {else}
                                <div class="metric-change text-none">- <small class="text-dark fw-normal">(vs. Tuần Trước -1)</small></div>
                            {/if}
                        </td>
                        <td class="text-center border-start align-middle">
                            <div class="fw-bold text-success text-dark">
                                <a href="#" class="show-bk-list text-success text-dark text-decoration-none" data-country="Tất cả quốc gia" data-period="{$P4_LABEL}" data-scope="all" data-fromdate="{$P4_F}" data-todate="{$P4_T}">{$TOTAL_DATA.p4.bk_ok}/{$TOTAL_DATA.p4.bk}</a>
                            </div>
                            <div class="fs-6 fw-normal mt-1 text-dark">{$TOTAL_DATA.p4.ticket} vé</div>
                            {if $TOTAL_DATA.p4_change.type == 'up'}
                                <div class="metric-change text-up">↑ {$TOTAL_DATA.p4_change.val} <small class="text-dark fw-normal">(vs. Tuần Trước -2)</small></div>
                            {elseif $TOTAL_DATA.p4_change.type == 'down'}
                                <div class="metric-change text-down">↓ {$TOTAL_DATA.p4_change.val} <small class="text-dark fw-normal">(vs. Tuần Trước -2)</small></div>
                            {else}
                                <div class="metric-change text-none">- <small class="text-dark fw-normal">(vs. Tuần Trước -2)</small></div>
                            {/if}
                        </td>
                        <td class="text-center border-start align-middle">
                            <div class="fw-bold text-danger text-dark">
                                <a href="#" class="show-bk-list text-danger text-dark text-decoration-none" data-country="Tất cả quốc gia" data-period="{$P5_LABEL}" data-scope="all" data-fromdate="{$P5_F}" data-todate="{$P5_T}">{$TOTAL_DATA.p5.bk_ok}/{$TOTAL_DATA.p5.bk}</a>
                            </div>
                            <div class="fs-6 fw-normal mt-1 text-dark">{$TOTAL_DATA.p5.ticket} vé</div>
                        </td>
                    </tr>
                    
                    {foreach from=$MOCK_DATA key=countryCode item=data}
                    
                    <tr class="main-line" title="Click để xem chi tiết route">
                        <td class="text-center align-middle toggle-icon text-dark period-col" data-period="p0" data-label="{$P0_LABEL}">▶</td>
                        <td class="align-middle period-col" data-period="p0" data-label="{$P0_LABEL}">
                            <img src="https://flagcdn.com/w20/{$countryCode|lower}.png" alt="{$countryCode}" class="me-2 shadow-sm border"> 
                            <span class="fw-bold">{$data.name}</span>
                        </td>
                        <td class="text-center border-start period-col" data-period="p0" data-label="{$P0_LABEL}">
                            <div class="metric-bk text-primary">
                                <a href="#" class="show-bk-list" data-country="{$data.name}" data-period="{$P0_LABEL}" data-scope="country" data-dest-country="{$countryCode}" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$data.p0.bk_ok}/{$data.p0.bk}</a>
                            </div>
                            <div class="metric-ticket"><i class="fa fa-ticket" aria-hidden="true"></i> {$data.p0.ticket} vé</div>
                            {if $data.p0_change.type == 'up'}
                                <div class="metric-change text-up">↑ {$data.p0_change.val}</div>
                            {elseif $data.p0_change.type == 'down'}
                                <div class="metric-change text-down">↓ {$data.p0_change.val}</div>
                            {else}
                                <div class="metric-change text-none">-</div>
                            {/if}
                        </td>
                        <td class="text-center border-start period-col" data-period="p1" data-label="{$P1_LABEL}">
                            <div class="metric-bk text-warning text-dark">
                                <a href="#" class="show-bk-list text-warning text-dark" style="border-color: #ffc107;" data-country="{$data.name}" data-period="{$P1_LABEL}" data-scope="country" data-dest-country="{$countryCode}" data-fromdate="{$P1_F}" data-todate="{$P1_T}">{$data.p1.bk_ok}/{$data.p1.bk}</a>
                            </div>
                            <div class="metric-ticket"><i class="fa fa-ticket" aria-hidden="true"></i> {$data.p1.ticket} vé</div>
                            {if $data.p1_change.type == 'up'}
                                <div class="metric-change text-up">↑ {$data.p1_change.val}</div>
                            {elseif $data.p1_change.type == 'down'}
                                <div class="metric-change text-down">↓ {$data.p1_change.val}</div>
                            {else}
                                <div class="metric-change text-none">-</div>
                            {/if}
                        </td>
                        <td class="text-center border-start period-col" data-period="p2" data-label="{$P2_LABEL}">
                            <div class="metric-bk text-info text-dark">
                                <a href="#" class="show-bk-list text-info text-dark" style="border-color: #0dcaf0;" data-country="{$data.name}" data-period="{$P2_LABEL}" data-scope="country" data-dest-country="{$countryCode}" data-fromdate="{$P2_F}" data-todate="{$P2_T}">{$data.p2.bk_ok}/{$data.p2.bk}</a>
                            </div>
                            <div class="metric-ticket"><i class="fa fa-ticket" aria-hidden="true"></i> {$data.p2.ticket} vé</div>
                        </td>
                        <td class="text-center border-start period-col" data-period="p3" data-label="{$P3_LABEL}">
                            <div class="metric-bk text-secondary text-dark">
                                <a href="#" class="show-bk-list text-secondary text-dark" style="border-color: #6c757d;" data-country="{$data.name}" data-period="{$P3_LABEL}" data-scope="country" data-dest-country="{$countryCode}" data-fromdate="{$P3_F}" data-todate="{$P3_T}">{$data.p3.bk_ok}/{$data.p3.bk}</a>
                            </div>
                            <div class="metric-ticket"><i class="fa fa-ticket" aria-hidden="true"></i> {$data.p3.ticket} vé</div>
                            {if $data.p3_change.type == 'up'}
                                <div class="metric-change text-up">↑ {$data.p3_change.val}</div>
                            {elseif $data.p3_change.type == 'down'}
                                <div class="metric-change text-down">↓ {$data.p3_change.val}</div>
                            {else}
                                <div class="metric-change text-none">-</div>
                            {/if}
                        </td>
                        <td class="text-center border-start period-col" data-period="p4" data-label="{$P4_LABEL}">
                            <div class="metric-bk text-success text-dark">
                                <a href="#" class="show-bk-list text-success text-dark" style="border-color: #198754;" data-country="{$data.name}" data-period="{$P4_LABEL}" data-scope="country" data-dest-country="{$countryCode}" data-fromdate="{$P4_F}" data-todate="{$P4_T}">{$data.p4.bk_ok}/{$data.p4.bk}</a>
                            </div>
                            <div class="metric-ticket"><i class="fa fa-ticket" aria-hidden="true"></i> {$data.p4.ticket} vé</div>
                            {if $data.p4_change.type == 'up'}
                                <div class="metric-change text-up">↑ {$data.p4_change.val}</div>
                            {elseif $data.p4_change.type == 'down'}
                                <div class="metric-change text-down">↓ {$data.p4_change.val}</div>
                            {else}
                                <div class="metric-change text-none">-</div>
                            {/if}
                        </td>
                        <td class="text-center border-start period-col" data-period="p5" data-label="{$P5_LABEL}">
                            <div class="metric-bk text-danger text-dark">
                                <a href="#" class="show-bk-list text-danger text-dark" style="border-color: #dc3545;" data-country="{$data.name}" data-period="{$P5_LABEL}" data-scope="country" data-dest-country="{$countryCode}" data-fromdate="{$P5_F}" data-todate="{$P5_T}">{$data.p5.bk_ok}/{$data.p5.bk}</a>
                            </div>
                            <div class="metric-ticket"><i class="fa fa-ticket" aria-hidden="true"></i> {$data.p5.ticket} vé</div>
                        </td>
                    </tr>
                    
                    <!-- Dòng chi tiết route ẩn mặc định -->
                    <tr class="detail-row" style="display: none;">
                        <td colspan="8" class="p-3 bg-light border-bottom">
                            <table class="table table-sm table-bordered bg-white shadow-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">STT</th>
                                        <th width="30%" class="text-center">Nơi đi</th>
                                        <th width="30%" class="text-center">Nơi đến</th>
                                        <th width="15%" class="text-center">Booking <span class="period-name-header text-primary">{$P0_LABEL}</span></th>
                                        <th width="20%" class="text-center">Vé <span class="period-name-header text-primary">{$P0_LABEL}</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$data.routes item=route name=routeLoop}
                                    <tr class="route-item route-row-{$countryCode}"
                                        data-p0-bk="{$route.bk_ok}/{$route.bk}" data-p0-ticket="{$route.ticket}" data-p0-f="{$P0_F}" data-p0-t="{$P0_T}"
                                        data-p1-bk="{$route.p1_bk_ok}/{$route.p1_bk}" data-p1-ticket="{$route.p1_ticket}" data-p1-f="{$P1_F}" data-p1-t="{$P1_T}"
                                        data-p2-bk="{$route.p2_bk_ok}/{$route.p2_bk}" data-p2-ticket="{$route.p2_ticket}" data-p2-f="{$P2_F}" data-p2-t="{$P2_T}"
                                        data-p3-bk="{$route.p3_bk_ok}/{$route.p3_bk}" data-p3-ticket="{$route.p3_ticket}" data-p3-f="{$P3_F}" data-p3-t="{$P3_T}"
                                        data-p4-bk="{$route.p4_bk_ok}/{$route.p4_bk}" data-p4-ticket="{$route.p4_ticket}" data-p4-f="{$P4_F}" data-p4-t="{$P4_T}"
                                        data-p5-bk="{$route.p5_bk_ok}/{$route.p5_bk}" data-p5-ticket="{$route.p5_ticket}" data-p5-f="{$P5_F}" data-p5-t="{$P5_T}"
                                    >
                                        <td class="text-center align-middle">{$smarty.foreach.routeLoop.iteration}</td>
                                        <td class="text-center align-middle fw-bold text-primary">{$route.dep}</td>
                                        <td class="text-center align-middle fw-bold text-primary">{$route.arr}</td>
                                        <td class="text-center align-middle">
                                            <a href="#" class="show-bk-list fw-bold text-decoration-none" data-country="{$data.name} ({$route.dep_code}-{$route.arr_code})" data-period="{$P0_LABEL}" data-dep="{$route.dep_code}" data-arr="{$route.arr_code}" data-fromdate="{$P0_F}" data-todate="{$P0_T}">{$route.bk_ok}/{$route.bk}</a>
                                        </td>
                                        <td class="text-center align-middle text-dark route-ticket">{$route.ticket}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                            {if $data.routes|@count > 10}
                            <div class="route-pagination-wrapper mt-2" data-country="{$countryCode}"></div>
                            {/if}
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Mock Modal -->
<div id="mock-modal" class="mock-modal">
    <div class="mock-modal-content">
        <div class="mock-modal-header">
            <h4 class="mock-modal-title" id="mock-modal-title-text">Danh sách Booking</h4>
            <button class="mock-modal-close" id="mock-modal-close-btn">&times;</button>
        </div>
        <div id="mock-modal-body" class="mock-modal-body">
            <!-- Content will be injected by JS -->
        </div>
    </div>
</div>
