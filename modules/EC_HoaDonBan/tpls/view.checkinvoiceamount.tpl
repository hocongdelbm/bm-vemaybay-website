<div class="ticket_report--wrap">
    <div class="box-section position-relative mt-0">
	    <div class="overlay-mobile"></div>

        <form action="index.php" method="get" name="search_form_custom" id="search_form_custom" class="flex-wrap">
            <input type="hidden" name="module" value="EC_HoaDonBan"/>
            <input type="hidden" name="action" value="checkinvoiceamount"/>

            <div class="action--wrap d-flex align-items-center gap-2 flex-wrap">
                <select name="pick_quickly_date" id="pick_quickly_date" class="box-select">
                    {$DATE_OPTION}
                </select>
                <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
                    <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input class="date_input box-input" type="text" maxlength="10" size="10" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
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
                    <div class="d-flex gap-2 align-items-center date_trigger--wrap tdate_trigger--wrap">
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input class="date_input box-input" type="text" maxlength="10" size="10" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
                            <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                    <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="function-wrap box-select__search">
                    <div class="d-flex align-items-center gap-2">
                        <label for="payment_stt" class="text-label">Tình trạng thu:</label>
                        <select name="payment_stt" id="payment_stt" class="box-select">{$PAYMENT_STT_OPTS}</select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="customer_source" class="text-label">Nguồn KH:</label>
                        <select name="customer_source" id="customer_source" class="box-select">{$CUSTOMER_SOURCE_OPTS}</select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="ticket_type" class="text-label">Loại vé:</label>
                        <select name="ticket_type" id="ticket_type" class="box-select">{$TICKET_TYPE_OPTS}</select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="invoice_check_status" class="text-label">Trạng thái hoá đơn:</label>
                        <select name="invoice_check_status" id="invoice_check_status" class="box-select">{$INVOICE_CHECK_STATUS_OPTS}</select>
                    </div>
                </div>
                <div class="function-wrap button-action--wrap mt-2">
                    <input type="submit" id="btnSearch" value="Tìm kiếm" name="btnSearch" class="btn btn-primary" title="Tìm kiếm"/>
                    <input type="submit" id="btnClear" value="Reset" name="btnClear" class="btn btn-secondary" title="Reset" />
                    <input type="button" id="btnSearch_cancel" value="Hủy bỏ" name="search_cancel" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" title="Hủy bỏ"/>
                </div>
            </div>
        </form>
        {$MAIN_CONTENT}
    </div>
</div>