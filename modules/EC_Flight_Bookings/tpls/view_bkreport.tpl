<h1 class="title">Thống kê vé</h1>

<div class="box-section">
<div id="bksupplier_report">
    <form action="index.php" method="post" name="frmSearch" id="frmSearch">
        <input type="hidden" name="module" value="EC_Flight_Bookings" />
        <input type="hidden" name="action" value="bkreport" />
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <select class="box-select" id="report_mode" name="mode" title="Chế độ xem">
                <option value="supplier" {if $REPORT_MODE == 'supplier'}selected{/if}>Theo NCC</option>
                <option value="airline" {if $REPORT_MODE == 'airline'}selected{/if}>Theo Hãng bay</option>
            </select>
            <select class="box-select" id="date_select" name="date_select">
                {$REPORT_TERM_LIST}
            </select>
            <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
                <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
                    <span class="text-label">Từ ngày: </span>
                    <div class="dateTime d-flex gap-2 position-relative">
                    <input class="date_input box-input" type="text" maxlength="10" size="8" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
                    <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                            </svg>
                    </button>
                    </div>
                </div>

                <svg width="40" height="20" fill="none">
                    <g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M33.5 8.5L36 11M4 11h32"></path>
                    </g>
                    <defs>
                    <clipPath id="icon_arrow_flight_long_svg__clip0">
                        <path fill="#fff" d="M0 0h40v20H0z"></path>
                    </clipPath>
                    </defs>
                </svg>

                <div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
                    <div class="dateTime d-flex gap-2 position-relative">
                    <input  class="date_input box-input" type="text" maxlength="10" size="8" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
                    <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                            </svg>
                    </button>
                    </div>
                </div>
            </div>

            <input type="submit" id="btnView" name="btnView" class="btn btn-primary" value="Xem báo cáo" title="Xem báo cáo" />
        </div>
    </form>

    <ul class="bookingqtyreport-note alert alert-info text-dark fw-semibold">
        <li>- Chứng từ không xác định NCC/Hãng nằm ở nhóm <strong>Khác (N/A)</strong>.</li>
        <li>- Bấm vào tên để lọc danh sách chi tiết bên dưới.</li>
    </ul>

    <div class="bkagent-table-wrap overflow-auto">
    <table id="supplier_summary_list" class="list-data table-details__booking mt-3" cellpadding="0" cellspacing="0" border="0">
        <thead>
            <th width="5%">#</th>
            <th>{if $REPORT_MODE == 'supplier'}NCC / Hãng{else}Hãng bay{/if}</th>
            <th width="10%">SL BK</th>
            <th width="10%">SL vé</th>
            <th width="18%">Tổng doanh thu</th>
            <th width="18%">Tổng giá mua</th>
            <th width="18%">Tổng doanh số</th>
        </thead>
        <tbody>
            {$SUPPLIER_SUMMARY_TBL}
        </tbody>
    </table>
    </div>
</div>
</div>

<div class="box-section box-details">
    <div class="bkagent-table-wrap">
    <table id="supplier_detail_list" class="table-details__booking table-booking__list" cellpadding="0" cellspacing="0">
        <thead>
            <th width="3%">#</th>
            <th width="8%">Chứng từ</th>
            {if $REPORT_MODE == 'supplier'}<th width="12%">NCC</th>{/if}
            <th width="13%">Hãng bay</th>
            <th width="8%">Chiều bay</th>
            <th width="6%">Loại vé</th>
            <th width="5%">SL vé</th>
            <th width="8%">Doanh thu</th>
            <th width="8%">Giá mua</th>
            <th width="8%">Doanh số</th>
            <th width="8%">Ngày chứng từ</th>
        </thead>
        <tbody>
            {$SUPPLIER_DETAIL_TBL}
        </tbody>
    </table>
    </div>
</div>

<script src="modules/EC_Flight_Bookings/js/view_bkreport.js?v={$VERSION}"></script>
