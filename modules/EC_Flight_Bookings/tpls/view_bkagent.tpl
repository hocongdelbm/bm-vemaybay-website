{literal}
    <script>
        $(document).ready(function() {
            Calendar.setup({
                inputField: "from_date",
                daFormat: "%d-%m-%Y",
                button: "fdate_trigger",
                singleClick: true,
                dateStr: "",
                step: 1,
                position: [230, 202],
            });
            Calendar.setup({
                inputField: "to_date",
                daFormat: "%d-%m-%Y",
                button: "tdate_trigger",
                singleClick: true,
                dateStr: "",
                step: 2
            });
            $("#date_select").change(function() {
                var fdate_val = $(this).children("option:selected").attr("from_date");
                var tdate_val = $(this).children("option:selected").attr("to_date");
                if(fdate_val != "") {
                    $("#from_date").val(fdate_val);
                }
                if(tdate_val != "") {
                    $("#to_date").val(tdate_val);
                }
            });
        });
    </script>
{/literal}

<h1 class="title">Thống kê vé</h1>

<div class="box-section">
<div id="bkagent_report">
    <form action="index.php" method="post" name="frmSearch" id="frmSearch">
        <input type="hidden" name="module" value="EC_Flight_Bookings" />
        <input type="hidden" name="action" value="bkagent" />
        <div class="d-flex align-items-center gap-2">
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
                    {literal}
                        <script type="text/javascript">
                            Calendar.setup({
                                    inputField: "from_date",
                                    daFormat: "%d-%m-%Y",
                                    button: "from_date_trigger",
                                    singleClick: true,
                                    dateStr: "",
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
        
                <div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
                    <div class="dateTime d-flex gap-2 position-relative">
                    <input  class="date_input box-input" type="text" maxlength="10" size="8" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
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
                                    button: "to_date_trigger",
                                    singleClick: true,
                                    dateStr: "",
                                    step: 2
                                    }
                            );
                        </script>
                    {/literal}
                    </div>
                </div>
        
                <input type="submit" id="btnView" name="btnView" class="btn btn-primary" value="Xem" title="Xem" />
            </div>
        </div>
    </form>

    <table id="bkagent_tbl" class="list-data table-details__booking mt-3" cellpadding="0" cellspacing="0" border="0">
        <thead>
            <th width="5%">STT</th>
            <th width="25%">Hãng</th>
            <th width="20%">SL vé</th>
            <th width="50%">Ghi chú</th>
        </thead>
        <tbody>
            {$AGENT_LIST_TBL}
        </tbody>
    </table>
</div>
</div>

<h2 class="change-title mt-4">Chi tiết:</h2>
<div class="box-section box-details">
    <table id="booking_list" class="table-details__booking table-booking__list" cellpadding="0" cellspacing="0">
        <thead>
            <th width="5%">STT</th>
            <th width="25%">Booking</th>
            <th width="20%">Hãng bay</th>
            <th width="15%">Chiều bay</th>
            <th width="10%">SL vé</th>
            <th width="20%">Ghi chú</th>
        </thead>
        <tbody>
            {$BOOKING_LIST_TBL}
        </tbody>
    </table>
</div>