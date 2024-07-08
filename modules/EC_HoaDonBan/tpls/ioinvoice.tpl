{literal}
    <script>
        $(document).ready(function() {
            addToValidate('search_form', 'from_date', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
            addToValidate('search_form', 'to_date', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
            $("#search_form").submit(function() {
                if(!check_form('search_form')) {
                    return false;
                }
            });
        });
    </script>
{/literal}

<h1 class="title">Bảng kê mua vào - bán ra</h1>
<div class="box-section">
    <div id="io_inv" class="bill-of-sale">
        <form name="search_form" id="search_form" method="post" action="index.php">
            <input type="hidden" name="module" value="EC_HoaDonBan">
            <input type="hidden" name="action" value="ioinvoice">

            <div class="io_inv_search from-to-date--wrap d-inline-flex gap-2 mb-3 align-items-center">
                <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
                    <span class="sublabel">Từ ngày: </span>    
                    <div class="dateTime d-flex gap-2 position-relative">
                        <input class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
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

                <div class="ticket_code-wrap d-flex align-items-center gap-1">
                    <span class="sublabel">Số vé:</span>
                    <input type="text" class="box-input" name="ticket_code" id="ticket_code" value="{$TICKET_CODE}">
                </div>

                <div class="company-unit-wrap d-flex gap-2 align-items-center">
                    <span class="sublabel">Đơn vị:</span>
                    <select class="box-select" name="company_unit" id="company_unit">
                        {$COMPANY_UNIT_OPTION}
                    </select>
                </div>
    
                <input type="submit" class="btn btn-primary" value="Tìm kiếm">
                <input type="submit" class="btn btn-danger" name="clear_btn" value="Reset">
                <input type="submit" class="btn btn-success" name="export_excel" value="Xuất Excel">
            </div>
        </form>
        {$IO_INV_TBL}
    </div>
</div>