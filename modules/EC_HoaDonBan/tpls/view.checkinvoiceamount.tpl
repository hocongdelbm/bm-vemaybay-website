<div class="ticket_report--wrap">
    <div class="box-section position-relative mt-0">
	    <div class="overlay-mobile"></div>

        <form action="index.php" method="post" name="search_form" id="ec_search_form" class="flex-wrap">
            <input type="hidden" name="module" value="EC_TongHop"/>
            <input type="hidden" name="action" value="report_sales_revenue"/>

            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
            </svg>
            
            <div class="action--wrap d-flex align-items-center gap-2 flex-wrap">
                <select id="rc_date_select" name="rc_date_select">
                    {$DATE_OPTION}
                </select>
                <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
                    <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
                        <span class="sublabel">Từ: </span>    
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input class="date_input box-input" type="text" maxlength="10" size="10" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
                            <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                    <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                                </svg>
                            </button>
                            {literal}
                                <script type="text/javascript">
                                    Calendar.setup({
                                        inputField: "from_date",
                                        daFormat: "%d-%m-%Y",
                                        button: "fdate_trigger",
                                        singleClick: true,
                                        dateStr: "",
                                        position: [244, 202],
                                        step: 1
                                    });
                                </script>
                            {/literal}
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
                        <span class="sublabel">Đến: </span>    
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input  class="date_input box-input" type="text" maxlength="10" size="10" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
                            <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                    <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                                </svg>
                            </button>
                            {literal}
                                <script type="text/javascript">
                                    Calendar.setup({
                                        inputField: "to_date",
                                        daFormat: "%d-%m-%Y",
                                        button: "tdate_trigger",
                                        singleClick: true,
                                        dateStr: "",
                                        step: 2
                                    });
                                </script>
                            {/literal}
                        </div>
                    </div>
                </div>

                <div class="function-wrap box-select__search">
                    <div class="d-flex align-items-center gap-2">
                        <label for="payment_stt" class="text-label">Tình trạng thu:</label>
                        <select class="box-select" id="payment_stt" name="payment_stt">{$PAYMENT_STT_OPTS}</select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="customer_source" class="text-label">Nguồn KH:</label>
                        <select class="box-select" id="customer_source" name="customer_source">{$CUSTOMER_SOURCE_OPTS}</select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="ticket_type" class="text-label">Loại vé:</label>
                        <select class="box-select" id="ticket_type" name="ticket_type">{$TICKET_TYPE_OPTS}</select>
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