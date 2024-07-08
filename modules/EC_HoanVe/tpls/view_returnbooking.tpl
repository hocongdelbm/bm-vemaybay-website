{literal}
<script>
	$(document).ready(function() {
        $("#return_bk_tbl tbody tr td").mouseenter(function() {
        $("." + $(this).parent().attr("class")).children().addClass("hover_bg");
        }).mouseleave(function() {
            $("." + $(this).parent().attr("class")).children().removeClass("hover_bg");
        });
		Calendar.setup ({
			inputField : "from_date",
			daFormat : "%d-%m-%Y",
			button : "from_date_trigger",
			singleClick : true,
			dateStr : "",
			step : 1,
			weekNumbers:false,
		});
		
		Calendar.setup ({
			inputField : "to_date",
			daFormat : "%d-%m-%Y",
			button : "to_date_trigger",
			singleClick : true,
			dateStr : "",
			step : 1,
			weekNumbers:false
		});
	});
</script>
{/literal}

<h1 class="title">
	Booking hoàn vé
    <div style="color: red; text-align: center;">{$warning_sen}</div>
</h1>

<div class="box-section">
<div id="returnbookingreport">

    <form name="search_form" method="POST" action="index.php?module=EC_HoanVe&action=returnbooking">
        <div id="booking-search" class="d-flex gap-2 align-items-center mb-3">
            <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
                <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
                    <span class="date-search sublabel">Từ ngày: </span>    
                    <div class="dateTime d-flex gap-2 position-relative">
                        <input class="date_input box-input" type="text" autocomplete="off" name="from_date" id="from_date" value="{$from_date}" size="11"/>
    
                        <button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
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
                    <span class="date-search sublabel">Đến ngày: </span>    
                    <div class="dateTime d-flex gap-2 position-relative">
                        <input class="date_input box-input" type="text" autocomplete="off" name="to_date" id="to_date" value="{$to_date}" size="11"/>
                        <button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center bk_search--wrap">
                <span class="bk_search sublabel">Booking: </span>    
                <input class="box-input" type="text" autocomplete="off" name="bk_search" id="bk_search" value="{$bk_search}" size="15"/>
            </div>
            <div class="d-flex gap-2 align-items-center return_stt--wrap">
                <span class="return_stt sublabel">Tình trạng: </span>    
                <select class="box-select" id="return_stt" name="return_stt">{$return_stt}</select>
            </div>
            <div class="d-flex align-items-center gap-1">
                <input class="btn btn-primary" type="submit" name="search" value="Tìm kiếm" />
                <input class="btn btn-danger" type="submit" name="clear_search" value="Xoá" />
            </div>
        </div>
    </form>

    <table id="return_bk_tbl" class="table-details__booking table-return_bk" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 2%;">STT</th>
                <th style="width: 8%;">Ngày tạo</th>
                <th style="width: 8%;">Booking</th>
                <th style="width: 8%;">Hãng bay</th>
                <th style="width: 8%;">Code vé</th>
                <th style="width: 8%;">T. trạng</th>
                <th style="width: 8%;">Tổng thu</th>
                <th style="width: 8%;">Hãng hoàn</th>
                <th style="width: 8%;">Phải thu</th>
                <th style="width: 10%;">Tổng trả</th>
                <th style="width: 8%;">Đã chi</th>
                <th style="width: 8%;">Phải trả</th>
                <th style="width: 8%;">Đang hoàn</th>
            </tr>
        </thead>
        {$RETURN_BK}
    </table>
</div>
<form id="search_bk_return" method="post" action="index.php" target="_blank">
    <input type="hidden" name="searchFormTab" value="basic_search">
    <input type="hidden" name="module" value="EC_HoanVe">
    <input type="hidden" name="action" value="ListView">
    <input type="hidden" name="query" value="true">
    <input type="hidden" name="booking_basic" id="booking_basic">
    <input type="hidden" name="booking_id_basic" id="booking_id_basic">
</form>

</div>
