{literal}
    <script>
        $(document).ready(function () {
            $("#rc_date_select").change(function() {
                $("#from_date").val($(this).find("option:selected").attr("fromdate"));
                $("#to_date").val($(this).find("option:selected").attr("todate"));
            });

            // click clear button
            $("#btnClear").click(function() {
                $("#ec_search_form input:not([type=submit], [type=button], [type=hidden]), #ec_search_form select").val("");
            });
        });
    </script>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="title">DOANH SỐ BOOKING TELESALE</h1>
	<svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
		<path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
	</svg>
</div>

<div class="ticket_report--wrap">
    <div class="box-section position-relative mt-0">
	    <div class="overlay-mobile"></div>

        <form action="index.php" method="post" name="search_form" id="ec_search_form" class="flex-wrap">
            <input type="hidden" name="module" value="{$MODULE_NAME}"/>
            <input type="hidden" name="action" value="bkreport_telesale"/>

            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
            </svg>
            
            <div class="action--wrap d-flex align-items-center gap-4">
                <select id="rc_date_select" name="rc_date_select">
                    {$DATE_OPTION}
                </select>
                <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
                    <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
                        <span class="sublabel">Từ ngày: </span>    
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
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
                                            }
                                    );
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
                        <span class="sublabel">Đến ngày: </span>    
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE}" id="to_date" name="to_date" autocomplete="off">
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
                                        }
                                    );
                                </script>
                            {/literal}
                        </div>
                    </div>
                </div>
            </div>
           
            <div class="button-action--wrap my-3">
                <input type="submit" id="btnSearch" value="Tìm kiếm" name="btnSearch" class="btn btn-primary" title="Tìm kiếm"/>
                <input type="submit" id="btnClear" value="Reset" name="btnClear" class="btn btn-secondary" title="Reset" />
                <input type="button" id="btnSearch_cancel" value="Hủy bỏ" name="search_cancel" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" title="Hủy bỏ"/>
            </div>
        </form>

        <table id="table_total_ticket__report" class="table-details__booking table_total_ticket__report">
            <thead>
                <tr>
                    <th>Tổng doanh thu</th>
                    <th>Tổng giá mua</th>
                    <th>Tổng doanh số</th>
                </tr>
            </thead>
            <tbody>
                {$DATA_TOTAL}
            </tbody>
        </table>
    </div>

    <div class="box-section">
        <table id="total_ticket_report_tbl" cellpadding="0" cellspacing="0" class="table-total-sale__booking table-details__booking">
            <thead>
                <tr class="head">
                    <th align="center" width="5%" class="hide-mobile">#</th>
                    <th align="center" width="8%">Booking</th>
                    <th align="center" width="8%" class="hide-mobile">Doanh thu</th>
                    <th align="center" width="8%" class="hide-mobile">Giá mua</th>
                    <th align="center" width="8%">Doanh số</th>
                    <th align="center" width="10%" class="hide-mobile">Thời gian đặt</th>
                    <th align="center" width="8%" class="hide-mobile">Ngày xuất vé</th>
                    <th align="center" width="10%" class="hide-mobile">Cuộc gọi</th>
                    <th align="center" width="12%" class="hide-mobile">Thời gian gọi</th>
                    <th align="center" width="12%">Nhân viên Telesale</th>
                    <th align="center" class="hide-mobile">Khoảng thời gian đặt vé sau khi gọi sale</th>
                </tr>
            </thead>
            <tbody>
                {$DATA}
            </tbody>
        </table>
    </div>
</div>
