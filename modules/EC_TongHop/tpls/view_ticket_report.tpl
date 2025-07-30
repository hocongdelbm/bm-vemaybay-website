{literal}
    <script>
        $(document).ready(function () {
            // Click search button
            $('#btnSearch').click(function () {
                location.href = location.href.replace(/&?e=([^&]$|[^&]*)/i, "");
            });

            // Check all
            $('#check-all').on('change', function () {
                if ($(this).is(':checked')) {
                    $('.booking-ids:visible').attr('checked', true);
                } else {
                    $('.booking-ids:visible').attr('checked', false);
                }
            });

            // click clear button
            $("#btnClear").click(function() {
                $("#ec_search_form input:not([type=submit], [type=button], [type=hidden]), #ec_search_form select").val("");
            });

            // Click recheck button
            $('#btnRecheck').on('click', function () {
                var bookingIds = $('.booking-ids:checked');
                if (!bookingIds.length) {
                    alert('Vui lòng chọn booking để tiếp tục.');
                    return false;
                }
                if (bookingIds.length > 100) {
                    alert('Vượt quá số lượng booking tối đa cho phép.');
                    return false;
                }

                bookingIds.each(function () {
                    var bookingId = $(this).attr('id');
                    $.ajax({
                        //timeout: 180000, // 180 seconds
                        type: 'POST',
                        url: 'index.php?entryPoint=entryPointMyRecheckFlight',
                        cache: false,
                        data: 'contact_mobile=' + $(this).data('mobile') + '&trip_type=' + $(this).data('triptype') + '&booking_id=' + $(this).val(),
                        beforeSend: function () {
                            var column  = $('#' + bookingId).parent();
                            var row     = column.parent();

                            column.find('span.loading').show();
                            row.removeClass('rc-error').removeClass('rc-success');
                            row.find('td:last-child div.rc-message').html('');
                        },
                        success: function (data) {
                            var column  = $('#' + bookingId).parent();
                            var row     = column.parent();

                            column.find('span.loading').hide();
                            if (data == 0) {
                                $('#' + bookingId).attr('checked', false).hide();
                                row.addClass('rc-success');
                                row.find('td:last-child div.rc-message').html('');
                            } else {
                                row.addClass('rc-error');
                                row.find('td:last-child div.rc-message').html('<br>' + data);
                            }
                        }
                    });
                });
                $('#btnRecheck').attr('disabled', true);
            });

            // ajaxStop
            $(document).ajaxStop(function(){
                $('#btnRecheck').attr('disabled', false);
            });

            $('input[type=radio][name=optionRadio]').change(function() {
               $('#from_date').val($('input[name=optionRadio]:checked').attr('fromdate'));
               $('#to_date').val($('input[name=optionRadio]:checked').attr('todate'));

                sessionStorage.setItem('optionRadio_rc', $(this).val());
			    sessionStorage.removeItem('rc_date_select');
            });

            $("#rc_date_select").change(function() {
                $("#from_date").val($(this).find("option:selected").attr("fromdate"));
                $("#to_date").val($(this).find("option:selected").attr("todate"));

                sessionStorage.setItem('rc_date_select', $(this).val());
			    sessionStorage.removeItem('optionRadio_rc');
            });

            // Check sessionStorage - js
            const selectOption 		= document.getElementById('rc_date_select');
            const radioOptions 		= document.getElementsByName('optionRadio');
            const savedSelectOption = sessionStorage.getItem('rc_date_select');
            if (savedSelectOption) {
                selectOption.value = savedSelectOption;
            } else {
                const savedRadioOption = sessionStorage.getItem('optionRadio_rc');
                if (savedRadioOption) {
                    radioOptions.forEach(radio => {
                        if (radio.value === savedRadioOption) {
                            radio.checked = true;
                        }
                    });
                }
            }
        });
    </script>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="title">DOANH THU BÁN VÉ</h1>
	<svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
		<path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
	</svg>
</div>

{if $IS_ALLOW_RECHECK}
<div class="feature-guide total_bought_price d-none">
    <p>- Nút Recheck sẽ kiểm tra hành trình trong booking.</p>
    <p>- Recheck màu Đỏ là chưa được.</p>
</div>
{/if}

<div class="ticket_report--wrap">
    <div class="box-section position-relative mt-0">
	    <div class="overlay-mobile"></div>

        <form action="index.php" method="post" name="search_form" id="ec_search_form" class="flex-wrap">
            <input type="hidden" name="module" value="EC_TongHop"/>
            <input type="hidden" name="action" value="ticketreport"/>

            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
            </svg>
            
            <div class="action--wrap d-flex align-items-center gap-4">
                <select id="rc_date_select">
                    {$DATE_OPTION}
                </select>
                <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
                    <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
                        <span class="sublabel">Từ ngày: </span>    
                        <div class="dateTime d-flex gap-2 position-relative">
                            <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
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
                            <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
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

                <div class="d-flex align-items-center gap-2 optionRadio--wrap">
                    <input type="radio" value="yesterday" id="yesterday" class="rd_time form-check-input" name="optionRadio" fromdate="{$YESTERDAY_FROMDATE}" todate="{$YESTERDAY_TODATE}">
                    <label class="cursor-pointer" for="yesterday">Hôm qua</label> 
        
                    <input type="radio" value="daybefore" id="daybefore" class="rd_time form-check-input" name="optionRadio" fromdate="{$DAYBEFORE_FROMDATE}" todate="{$DAYBEFORE_TODATE}">
                    <label class="cursor-pointer" for="daybefore">Hôm trước</label> 
        
                    <input type="radio" value="current_week" id="current_week" class="rd_time form-check-input" name="optionRadio" fromdate="{$CURRENT_WEEK_FROMDATE}" todate="{$CURRENT_WEEK_TODATE}">
                    <label class="cursor-pointer" for="current_week">Tuần này</label>
        
                    <input type="radio" value="previous_week" id="previous_week" class="rd_time form-check-input" name="optionRadio" fromdate="{$PREVIOUS_WEEK_FROMDATE}" todate="{$PREVIOUS_WEEK_TODATE}"> 
                    <label class="cursor-pointer" for="previous_week">Tuần trước</label> 

                    <div class="d-flex align-items-center gap-2">
                        <label for="payment_stt" class="text-label">Tình trạng thu:</label>
                        <select class="box-select" id="payment_stt" name="payment_stt">{$PAYMENT_STT}</select>
                    </div>
                </div>
            </div>
           
            {*{php} if($GLOBALS['current_user']->view_percent >= 100){ {/php}
            <input {$CHECKED_1} type="radio" name="group_by" id="group_by1" value="booking"/>
            <label for="group_by1">Booking</label>

            <input {$CHECKED_2} type="radio" name="group_by" id="group_by2" value="airline_code"/>
            <label for="group_by2">Hãng bay</label>

            <input {$CHECKED_4} type="radio" name="group_by" id="group_by4" value="itinerary"/>
            <label for="group_by4">Chặng bay</label>
            {php} } {/php} *}

            <div class="box-color__sales my-3">
                <div class="color-block__wrap">
                    <div class="d-flex align-items-center gap-2">
                        <span class="color-block sales_smaller_receipt"></span>
                        <div class="d-flex align-items-center gap-1">
                            <span>Doanh thu</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/></svg>
                            <span>Phiếu thu</span>
                        </div>
                    </div>
                </div>
                <div class="color-block__wrap">
                    <div class="d-flex align-items-center gap-2">
                        <span class="color-block error1"></span>
                        <div class="d-flex align-items-center gap-1">
                            <span>Doanh thu</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/></svg>
                            <span>Giá mua</span>
                        </div>
                    </div>
                </div>
                <div class="color-block__wrap">
                    <div class="d-flex align-items-center gap-2">
                        <span class="color-block error2"></span>
                        <div class="d-flex align-items-center gap-1">
                            <span>Phiếu thu</span> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/></svg> 
                            <span>Doanh thu</span>
                        </div>
                    </div>
                </div>
                <div class="color-block__wrap">
                    <div class="d-flex align-items-center gap-2">
                        <span class="color-block equal"></span>
                        <div class="d-flex align-items-center gap-1">
                            <span>Doanh thu</span> 
                            <span>=</span>
                            <span>Giá mua</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="button-action--wrap my-3">
                <input type="submit" id="btnSearch" value="Tìm kiếm" name="btnSearch" class="btn btn-primary" title="Tìm kiếm"/>
                <input type="submit" id="btnClear" value="Reset" name="btnClear" class="btn btn-secondary" title="Reset" />
                {if $IS_ALLOW_RECHECK}
                <input type="button" id="btnRecheck" value="Recheck" name="btnRecheck" class="btn btn-warning" title="Recheck"/>
                {/if}
                {if $ALLOWED_EXPORT}
                <!-- <input type="submit" id="btnExport" value="Xuất Excel" name="btnExport" class="btn btn-success" title="Xuất Excel"/> -->
                {/if}
                <input type="button" id="btnSearch_cancel" value="Hủy bỏ" name="search_cancel" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" title="Hủy bỏ"/>
            </div>
        </form>

        <table id="table_total_ticket__report" class="table-details__booking table_total_ticket__report">
            <thead>
                <tr>
                    <th>SL vé</th>
                    <th>Tổng doanh thu</th>
                    <th>Tổng giá mua</th>
                    <th>Tổng doanh số</th>
                    <th>Tổng phiếu thu</th>
                    <th>Tổng sử dụng điểm</th>
                </tr>
            </thead>
            <tbody>
                {$DATA_TOTAL}
            </tbody>
        </table>
    </div>
{php}
    if(isset($_POST['group_by']) && $_POST['group_by'] == 'ticket_class'){
{/php}

<!-- THONG KE DOANH THU THEO HANG VE -->
<div class="box-section">
    <table cellpadding="0" cellspacing="0" class="table-total_sale_tbl_type table-details__booking">
        <thead>
            <tr>
                <th align="center" width="5%">STT</th>
                <th align="center" width="20%">Hạng vé</th>
                <th align="center" width="8%">SL Vé</th>
                <th align="center" width="12%">Doanh thu (đ)</th>
                <th align="center" width="12%">Doanh thu (%)</th>
                <th align="center">Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            {$DATA}
        </tbody>
    </table>
</div>
<!-- END THONG KE DOANH THU THEO HANG VE -->
{php}
    } else if(isset($_POST['group_by']) && $_POST['group_by'] == 'itinerary') {
{/php}

<!-- THONG KE DOANH THU THEO CHANG BAY -->
<div class="box-section">
    <table cellpadding="0" cellspacing="0" class="table-total_sale_tbl_transit table-details__booking">
        <thead>
            <tr>
                <th align="center" width="5%">
                    STT
                </th>
                <th align="center" width="20%">
                    Chặng bay
                </th>
                <th align="center" width="8%">
                    SL Vé
                </th>
                <th align="center" width="12%">
                    Doanh thu (đ)
                </th>
                <th align="center" width="12%">
                    Doanh thu (%)
                </th>
                <th align="center">
                    Ghi chú
                </th>
            </tr>
        </thead>
        <tbody>
            {$DATA}
        </tbody>
    </table>
</div>
<!-- END THONG KE DOANH THU THEO CHANG BAY -->

{php}
    } else if(isset($_POST['group_by']) && $_POST['group_by'] == 'airline_code') {
{/php}

<!-- THONG KE DOANH THU THEO HANG BAY -->
<div class="box-section">
    <table cellpadding="0" cellspacing="0" class="table-total_sale_tbl_airlines table-details__booking">
        <thead>
            <tr>
                <th align="center" width="3%">STT</th> 
                <th align="center" width="20%">Hãng hàng không</th>
                <th align="center" width="7%">SL Vé</th>
                <th align="center" width="14%">Doanh thu (đ)</th>
                <th align="center" width="14%">Số ĐK (đ)</th>
                <th align="center" width="14%">Ký quỹ (đ)</th>
                <th align="center" width="14%">Giá mua (đ)</th>
                <th align="center" width="14%">Số dư khả dụng (đ)</th>
            </tr>
        </thead>
        <tbody>
            {$DATA}
        </tbody>
    </table>
</div>

<!-- END THONG KE DOANH THU THEO HANG BAY -->

{php}
    } else {
{/php}

<!-- THONG KE DOANH THU THEO BOOKING -->
<div class="box-section">
    <table id="total_ticket_report_tbl" cellpadding="0" cellspacing="0" class="table-total-sale__booking table-details__booking">
        <thead>
            <tr class="head">
                {if $IS_ALLOW_RECHECK}
                    <th align="center" width="2%" class="hide-mobile"><input type="checkbox" id="check-all" value="0"></th>
                {/if}
                <th align="center" width="3%" class="hide-mobile">STT</th>
                <th align="center" width="10%">Booking</th>
                <th align="center" width="3%">Vé</th>
                <th align="center" width="18%" class="hide-mobile">Ghi chú</th>
                <th align="center" width="8%" class="hide-mobile">Doanh thu</th>
                <th align="center" width="8%" class="hide-mobile">Giá mua</th>
                <th align="center" width="8%">Doanh số / Sử dụng điểm</th>
                <!-- <th align="center" width="7%" class="hide-mobile">Nơi đặt</th> -->
                <th align="center" width="8%" class="hide-mobile">Phiếu thu</th>
                <th align="center" width="12%">Nhân viên</th>
                <th align="center" width="10%" class="hide-mobile">Ngày tạo</th>
                <th align="center" class="hide-mobile">Ngày xuất vé</th>
            </tr>
        </thead>
        <tbody>
            {$DATA}
        </tbody>
    </table>
</div>
<!-- END THONG KE DOANH THU THEO BOOKING -->

{php}
    }
{/php}

</div>
