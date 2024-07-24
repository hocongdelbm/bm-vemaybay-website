{literal}
    <script>
        $(document).ready(function() {

            $("#leftHandle").click(function() {
                $(".col_1").width($("#recheck_inf_col").width() + 1 - 6);
                $(".col_2").width($("#recheck_user_col").width() + 1 - 6);
                $(".recheck_inf").height($(".recheck_inf").parent().height());
            });

            $(window).load(function() {
                $(".col_1").width($("#recheck_inf_col").width() + 1 - 6);
                $(".col_2").width($("#recheck_user_col").width() + 1 - 6);
                $(".recheck_inf").height($(".recheck_inf").parent().height());
            });

            $(window).resize(function() {
                $(".col_1").width($("#recheck_inf_col").width() + 1 - 6);
                $(".col_2").width($("#recheck_user_col").width() + 1 - 6);
                $(".recheck_inf").height($(".recheck_inf").parent().height());
            });

            $("#clear_btn").click(function() {
                $("#recheck_frm input[type='text'], #recheck_frm select").val("");
                $("#recheck_frm input[type='checkbox']").attr("checked", false);
            });

            $("#recheck_frm").submit(function() {

                addToValidate('recheck_frm', 'from_date', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
                addToValidate('recheck_frm', 'to_date', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
                
                if(!check_form('recheck_frm')) {
                    return false;
                } else {
                    $("#search_btn, #clear_btn").attr("disabled", true);
                }

            });

            $("#multi_airline_recheck").change(function() {
                if($("#multi_airline_recheck").is(":checked")) {
                    $("#rc_airline, #rc_inf").val("");
                    $("#rc_airline, #rc_inf").attr("disabled", true);
                } else {
                    $("#rc_airline, #rc_inf").attr("disabled", false);
                }
            });

            $("#rc_date_select").change(function() {
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

<h1 class="title d-flex align-items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
        <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/>
        <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"/>
    </svg>
    RECHECK XUẤT VÉ
</h1>

<div class="box-section">
<form method="post" name="recheck_frm" id="recheck_frm">
    <input type="hidden" name="module" value="EC_Flight_Bookings">
    <input type="hidden" name="action" value="recheckbk">

    <div class="flex__wrap recheckbk--wrap mb-3">
        <div class="from-to-date--wrap d-inline-flex gap-3 align-items-center">
            <select class="box-select" id="rc_date_select">
                <option from_date="" to_date="">--Trống--</option>
                <option from_date="{$TODAY}" to_date="{$TODAY}">Hôm nay</option>
                <option from_date="{$YESTERDAY}" to_date="{$YESTERDAY}">Hôm qua</option>
                <option from_date="{$THISWEEK_FROMDATE}" to_date="{$THISWEEK_TODATE}">Tuần này</option>
                <option from_date="{$PREVWEEK_FROMDATE}" to_date="{$PREVWEEK_TODATE}">Tuần trước</option>
                <option from_date="{$THISMONTH_FROMDATE}" to_date="{$THISMONTH_TODATE}">Tháng này</option>
                <option from_date="{$PREVMONTH_FROMDATE}" to_date="{$PREVMONTH_TODATE}">Tháng trước</option>
            </select>

            <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
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
                                Calendar.setup ({
                                    inputField : "from_date",
                                    daFormat : "%d-%m-%Y",
                                    button : "fdate_trigger",
                                    singleClick : true,
                                    dateStr : "",
                                    step : 1
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
    
            <div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
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
                                Calendar.setup ({
                                    inputField : "to_date",
                                    daFormat : "%d-%m-%Y",
                                    button : "tdate_trigger",
                                    singleClick : true,
                                    dateStr : "",
                                    step : 1
                                });
                        </script>
                    {/literal}
                </div>
            </div>
        </div>

        <div class="flex__wrap box-recheckbk--wrap">
            <div class="flex__wrap box-recheckbk">
                <label for="rc_airline" class="sublabel">Hãng:</label>
                <select class="box-select" id="rc_airline" name="rc_airline" {$OTH_DISABLED}>{$RC_AIRLINE}</select>
            </div>
    
            <div class="flex__wrap box-recheckbk">
                <label for="rc_inf" class="sublabel">TT Recheck:</label>
                <select class="box-select" id="rc_inf" name="rc_inf" {$OTH_DISABLED}>{$RC_INF}</select>
            </div>

            <div class="flex__wrap box-recheckbk group-button__submit">
                <input type="submit" class="btn btn-primary" id="search_btn" value="Tìm kiếm">
                <input type="submit" class="btn btn-danger" id="clear_btn" value="Xoá">
            </div>
        </div>
    </div>

    <div class="group-button__checkbox flex__wrap mb-3">
        <input type="checkbox" name="multi_airline_recheck" id="multi_airline_recheck" {$RC_MULTI_MISSING}> 
        <label for="multi_airline_recheck">Tìm BK chứa nhiều hãng nhưng chưa đủ số lần recheck tối thiểu</label>
    </div>
</form>

<table id="recheck_tbl" class="table-recheck_tbl table-details__booking" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th rowspan="2" width="5%">STT</th>
            <th rowspan="2"width="12%">Giờ xuất vé</th>
            <th rowspan="2" width="12%">Booking</th>
            <th rowspan="2" width="8%">Hãng</th>
            <th colspan="3" class="p-0">
                <div class="d-flex align-items-center">
                    <div class="p-2 border-right__table w-60">TT recheck </div>
                    <div class="p-2 w-20 border-right__table">User recheck</div>
                    <div class="p-2 w-20">Thời gian recheck</div>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        {$RECHECK_TBL}
    </tbody>
</table>

</div>