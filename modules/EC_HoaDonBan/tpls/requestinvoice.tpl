{literal}
    <style>
        #output_invoice #output_inv tbody tr:hover {
            background-color: #cfeafe;
        }

        #output_invoice #output_inv thead tr:first-child th {
            position: sticky;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
            top: 0;
        }

        #output_invoice #output_inv thead tr:not(first-child) th {
            position: sticky;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
            top: 23px;
        }

        .break-word {
            word-break: break-word;
        }
    </style>
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

<h1 class="title">DANH SÁCH BK YÊU CẦU XUẤT HOÁ ĐƠN</h1>

<div class="box-section">
<div id="output_invoice">
    <form name="search_form" id="search_form" method="post" action="index.php">
        <input type="hidden" name="module" value="EC_HoaDonBan">
        <input type="hidden" name="action" value="requestinvoice">

        <div class="from-to-date--wrap d-inline-flex gap-2 mb-3 align-items-center">
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

            <div class="supplier-wrap d-flex align-items-center gap-1">
                <span class="sublabel">Booking:</span>
                <input type="text" class="box-input" name="booking" id="booking" value="{$BOOKING}">
            </div>
            <div class="submit-button-wrap d-flex gap-2">
                <input type="submit" class="btn btn-primary" value="Tìm kiếm">
                <input type="submit" class="btn btn-danger" name="clear_btn" value="Xoá">
            </div>
        </div>
    </form>
    <table id="output_inv" class="table-details__booking table-request__invoice" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th width="3%" rowspan="2">STT</th>
                <th width="9%" rowspan="2">Ngày tạo</th>
                <th width="10%" rowspan="2">Booking</th>
                <th colspan="5">Thông tin hoá đơn</th>
                <th width="18%" rowspan="2">Thông tin HĐ đầu ra</th>
            </tr>
            <tr>
                <th width="10%">Họ tên KH</th>
                <th width="14%">Công ty</th>
                <th width="10%">MST / Mã KH</th>
                <th width="14%">Địa chỉ</th>
                <th width="14%">Email</th>
            </tr>
        </thead>
        <tbody>
            {$OUTPUT_INV_TBL}
        </tbody>
    </table>
</div>
</div>