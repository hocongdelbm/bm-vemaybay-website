<link rel="stylesheet" href="./themes/SuiteP/libs/css/select2.min.css">

{literal}
    <script>
        $(document).ready(function() {
            $("#airlines, #user_id").select2();
        });
    </script>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="title">Kiểm tra ngày bay</h1>
    <svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block hide-landscape" viewBox="0 0 16 16">
        <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
    </svg>
</div>
 
<div class="box-section position-relative mt-0">
	<div class="overlay-mobile"></div>

    <form id="ec_search_form" name="search_form" method="post" action="index.php">
        <input type="hidden" name="module" value="EC_Flight_Bookings" />
        <input type="hidden" name="action" value="checkflydate" />

        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-md-none d-block" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
        </svg>

        <div class="checkfly-date__wrap action--wrap flex-wrap">
            <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
                <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
                    <span class="text-label">Ngày giờ bay: </span>    
                    <div class="dateTime d-flex gap-2 position-relative">
                        <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$POST_TUNGAY}" id="tungay" name="tungay" autocomplete="off">
                        <button class="icon_dateTime" type="button" id="tungay_trigger" onclick="return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                            </svg>
                        </button>
                        {literal}
                            <script type="text/javascript">
                                Calendar.setup({
                                            inputField: "tungay",
                                            daFormat: "%d-%m-%Y",
                                            button: "tungay_trigger",
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
                    <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
                        <span class="text-label">&nbsp;</span>    
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$POST_DENNGAY}" id="denngay" name="denngay" autocomplete="off">
                            <button class="icon_dateTime" type="button" id="denngay_trigger" onclick="return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                    <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                                </svg>
                            </button>
                            {literal}
                                <script type="text/javascript">
                                    Calendar.setup({
                                                inputField: "denngay",
                                                daFormat: "%d-%m-%Y",
                                                button: "denngay_trigger",
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

            <div class="select-checkfly__date">
                <div class="select_airlines--wrap">
                    <span class="text-label">Hãng:</span>
                    <select class="box-select" id="airlines" name="airlines">{$AIRLINES}</select>
                </div>
            
                <div class="select_userid--wrap">
                    <span class="text-label">User:</span>
                    <select class="box-select" id="user_id" name="user_id"><option value="">-không-</option>{$USER_LIST}</select>
                </div>
            </div>
        </div>

        <div class="button-action--wrap">
            <input type="submit" id="btnSearch" name="btnSearch" class="btn btn-primary button-action" value="Tìm" title="Tìm"/>
            <input type="button" id="btnSearch_cancel" name="search" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" value="Hủy bỏ" title="Hủy bỏ"/>
        </div>

        <div class="frmSearch-note d-flex gap-2 align-items-center mb-3 hide-mobile">
            <div class="box-note"></div>
            <div class="text-note">Đã gọi thông báo cho khách hàng về lịch bay.</div>
        </div>
    </form>

    <table class="tbl_wrapper table-details__booking" cellpadding="0" cellspacing="0" border="0">
        <thead>
            <tr>
                <th width="2%" class="hide-mobile">STT</th>
                <th width="7%">Booking</th>
                <th width="15%">Liên hệ</th>
                <th width="7%">Điện thoại</th>
                <!-- <th width="7%">Email</th> -->
                <th width="7%" class="hide-mobile">Hãng</th>
                <th width="7%" class="hide-mobile">Mã chuyến</th>
                <th width="7%" class="hide-mobile">Hành trình</th>
                <th width="11%">Ngày giờ bay</th>
                <th width="6%" class="hide-mobile">Hạng vé</th>
                <th width="6%" class="hide-mobile">Giá cơ bản</th>
                <th width="3%" class="hide-mobile">SL</th>
                <th width="6%" class="hide-mobile">Ngày xuất</th>
                <th width="6%" class="hide-mobile">TG hoàn tất</th>
            </tr>
        </thead>
        <tbody>
            {$DATA}
        </tbody>
    </table>
</div>
