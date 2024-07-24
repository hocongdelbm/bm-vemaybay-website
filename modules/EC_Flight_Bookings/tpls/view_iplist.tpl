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
            
            $(".bk_detail").click(function() {
                var add = $(this).attr("add");
                $.ajax({
                    url: "index.php?entryPoint=entryPointFlightBookings",
                    type: "POST",
                    data: {
                        add: add,
                        stt: $(this).attr("stt"),
                        fdate: $(this).attr("fdate"),
                        tdate: $(this).attr("tdate"),
                        for: "getBookingIPList",
                    },
                    beforeSend: function () {
                        document.getElementById("ip_report").style.cursor = "progress";
                    },
                    success: function(response) {
                        document.getElementById("ip_report").style.cursor = "default";
                        $("#list-ip__wrap").dialog({
                            title: "Danh sách Booking",
                            width: 500,
                            resizable: false
                        });

                        $('#list-ip__wrap').html(response);
                    }
                });
            });

            $(".btn-deny").click(function() {

                let add = $(this).attr("add");

                // $.ajax({
                //     url: "index.php?entryPoint=entryPointFlightBookings",
                //     type: "POST",
                //     data: {
                //         add: add,
                //         for: "getInforLyThong",
                //     },
                //     beforeSend: function () {
                //         $('.container-waiting').show();
                //     },
                //     success: function(response) {
                //         if(response == 1){
                //             $('.container-waiting').hide();
                //         }
                //     }
                // });
            });

            // TELE GR LÝ THỐNG
            // $token   = '2062223399:AAGhuTA3jvRBeCLq8fixOFrY-MecvuA_7AA';
            // $chatID  = '-1001656085253';
        });
    </script>
{/literal}
<h1 class="report_title title">Danh sách IP</h1>

<div class="box-section">
<div id="ip_report">
    <form action="index.php" method="post" name="frmSearch" id="frmSearch" class="mb-3">
        <input type="hidden" name="module" value="EC_Flight_Bookings" />
        <input type="hidden" name="action" value="iplist" />

        <select class="box-select me-2" id="date_select" name="date_select">
            <option from_date="" to_date="">--Trống--</option>
            <option from_date="{$TODAY}" to_date="{$TODAY}">Hôm nay</option>
            <option from_date="{$YESTERDAY}" to_date="{$YESTERDAY}">Hôm qua</option>
            <option from_date="{$THREEDAY_AGO}" to_date="{$TODAY}">Cách 3 ngày</option>
            <option from_date="{$SEVENDAY_AGO}" to_date="{$TODAY}">Cách 7 ngày</option>
            <option from_date="{$THIRTYDAY_AGO}" to_date="{$TODAY}">Cách 30 ngày</option>
        </select>

        <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
            <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
                <span class="sublabel">Từ ngày: </span>    
                <div class="dateTime d-flex gap-2 position-relative">
                    <input class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
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
                <span class="sublabel">Đến ngày: </span>    
                <div class="dateTime d-flex gap-2 position-relative">
                   <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
                    <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                          </svg>
                    </button>
                </div>
            </div>

            <input type="submit" id="btnView" name="btnView" class="btn btn-primary" value="Xem" title="Xem"/>
            <input type="submit" id="btnExport" name="btnExport" class="btn btn-danger" value="List Deny" title="List Deny" />
        </div>
    </form>

    <table id="iplist_tbl" class="table-iplist table-details__booking" cellpadding="0" cellspacing="0" border="0">
        <thead>
            <th width="3%">STT</th>
            <th width="15%">Danh sách IP</th>
            <th width="5%">Booking</th>
            <th width="10%">Ngày đầu</th>
            <th width="10%">Ngày cuối</th> 
            <th width="5%">Booker</th>
            <th width="10%">Giá trị</th>
            <th class="hide-mobile">Diễn giải</th>
            <th></th>
        </thead>
        <tbody>
            {$IP_LIST_TBL}
        </tbody>
    </table>

    <div id="list-ip__wrap" class="p-2"></div>
    
</div>

</div>