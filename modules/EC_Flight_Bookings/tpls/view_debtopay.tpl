{literal}
    <script>
        $(document).ready(function () {
            $('#checkall').change(function () {
                var is_check_all = $(this).is(':checked');
                if (is_check_all) {
                    $('input:checkbox[name="supplier_id[]"]').attr('checked', true);
                } else {
                    $('input:checkbox[name="supplier_id[]"]').attr('checked', false);
                }
            });

            $('#btnSearch').on('click', function () {
                var from_date   = $('#from_date').val();
                var to_date     = $('#to_date').val();

                if (from_date.length == 0) {
                    let text_warning = 'Vui lòng chọn từ ngày.';
                    showToastWarning(text_warning);

                    $('#from_date').focus();
                    return false;
                }
                if (to_date.length == 0) {
                    let text_warning = 'Vui lòng chọn đến ngày.';
                    showToastWarning(text_warning);

                    $('#to_date').focus();
                    return false;
                }
                $('#frmSearch').attr('target', '_self');
                $('#frmSearch').attr('action', 'index.php');
                $('#frmSearch input:hidden[name="print"]').remove();
            });

            $('#btnViewDetail').on('click', function () {
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                if (from_date.length == 0) {
                    let text_warning = 'Vui lòng chọn từ ngày.';
                    showToastWarning(text_warning);

                    $('#from_date').focus();
                    return false;
                }
                if (to_date.length == 0) {
                    let text_warning = 'Vui lòng chọn đến ngày.';
                    showToastWarning(text_warning);

                    $('#to_date').focus();
                    return false;
                }
                if ($('input:checkbox[name="supplier_id[]"]:checked').length == 0) {
                    let text_warning = 'Bạn chưa chọn đối tượng để tiếp tục.';
                    showToastWarning(text_warning);
                    return false;
                }
            });
        });
    </script>
{/literal}

{php}
    if(isset($_POST['btnViewDetail'])) {
{/php}

{literal}
    <script>
        $(document).ready(function () {
            Set_Cookie('showLeftCol', 'false', 30, '/', '', '');
        });
    </script>
{/literal}

<form action="index.php" method="post">
    <input type="hidden" name="module" value="EC_Flight_Bookings">
    <input type="hidden" name="action" value="debtopay">
    <input type="hidden" name="print" value="true"/>
    <input type="hidden" name="from_date" value="{$POST_FROM_DATE}"/>
    <input type="hidden" name="to_date" value="{$POST_TO_DATE}"/>
    <input type="hidden" name="supcode_{$SUPPLIER_ID}" value="{$SUPPLIER_CODE}"/>
    <input type="hidden" name="supname_{$SUPPLIER_ID}" value="{$SUPPLIER_NAME}"/>
    <input type="hidden" name="exportexcel"/>
    <input type="hidden" name="supplier_id[]" value="{$SUPPLIER_ID}"/>
    {$VOUCHER_LIST}
</form>
{php}
    } else {
{/php}

<h1 class="title">CÔNG NỢ PHẢI TRẢ </h1>
<div class="box-section">
    <form action="index.php" method="post" name="frmSearch" id="frmSearch" target="_blank">
        <input type="hidden" name="module" value="EC_Flight_Bookings"/>
        <input type="hidden" name="action" value="debtopay"/>
        <input type="hidden" name="print" value="true"/>

        <div class="action--wrap flex-wrap d-flex gap-2 align-items-center">
            <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
                <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
                    <span class="sublabel">Từ ngày: </span>    
                    <div class="dateTime d-flex gap-2 position-relative">
                        <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$POST_FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
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
                        <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$POST_TO_DATE}" id="to_date" name="to_date" autocomplete="off">
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

            <div class="button-action--wrap mb-0">
                <input type="submit" id="btnSearch" name="btnSearch" class="btn btn-primary button-action" value="Tìm" title="Tìm"/>
            </div>
        </div>

        <table class="table-data table-debtopay table-details__booking mt-3" cellpadding="0" cellspacing="0" border="0">
            <thead>
                <tr>
                    <th width="5%"><input type="checkbox" id="checkall" value="0"/></th>
                    <th width="25%">Mã đối tượng</th>
                    <th width="40%">Tên đối tượng</th>
                    <th width="30%">Số tiền</th>
                </tr>
            </thead>
            {$SUPPLIER_LIST}
            <tr class="footer-tr">
                <td  colspan="3" class="fw-semibold text-end">Tổng cộng</td>
                <td class="fw-semibold text-end">{$TOTAL_DEBT}</td>
            </tr>
            <tr class="footer-tr">
                <td class="text-start" colspan="5">
                    <input type="submit" class="btn btn-primary" name="btnViewDetail" id="btnViewDetail" value="Xem chi tiết" title="Xem chi tiết"/>
                </td>
            </tr>
        </table>
    </form>
</div>
{php}
    }
{/php}