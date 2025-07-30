{literal}
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on("change", "#date_select", function(e) {
            $("#from_date").val($(this).find("option:selected").attr("fromdate"));
            $("#to_date").val($(this).find("option:selected").attr("todate"));
            $('.container-waiting').show();
            $ ("#ec_search_form").submit();
        });
    });
</script>
{/literal}
<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="title">Thống kê cuộc gọi tự động</h1>
</div>

<div class="box-section position-relative">
    <form action="index.php" method="post" name="search_form" id="ec_search_form">
        <input type="hidden" name="module" value="Calls"/>
        <input type="hidden" name="action" value="statistics_autocall"/>

        <div class="d-flex align-items-center gap-2 action--wrap">
            <select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>
            
            <div class="from-to-date--wrap d-inline-flex gap-3 align-items-center">
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

                <div class="button-action--wrap">
                    <input type="submit" class="btn btn-primary button-action" name="btnSearch" id="btnSearch" value="Xem thống kê" title="Xem thống kê" />
               </div>
            </div>
        </div>
    </form>
</div>

<div class="box-section">
    <table class="table-detail_user table-details__booking" cellpadding="0" cellspacing="0">
        <thead>
             <tr>
                  <th width="3%">#</th>
                  <th>Họ tên</th>
                  <th>Ngày gọi</th>
                  <th>Trạng thái</th>
                  <th>Mô tả</th>
             </tr>
        </thead>
        <tbody>
             {$LIST_AUTOCALL}
        </tbody>
   </table>
</div>
