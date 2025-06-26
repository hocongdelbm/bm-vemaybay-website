<h1 id="report-title" class="title">Danh sách khách tham khảo vé trên các site</h1>
<form action="index.php" method="get" name="frmSearch" id="frmSearch">
    <input type="hidden" name="module" value="EC_Flight_Bookings" />
    <input type="hidden" name="action" value="clientphonetcb" />

    <select class="source box-select" name="source">
        <option value="timchuyenbay.vn" {if $source eq "timchuyenbay.vn"}selected{/if}>timchuyenbay.vn</option>
        <option value="vietjet.net" {if $source eq "vietjet.net"}selected{/if}>vietjet.net</option>
        <option value="timchuyenbay.com" {if $source eq "timchuyenbay.com"}selected{/if}>timchuyenbay.com</option>
    </select>
    <button type="submit" class="btn btn-primary">Chọn Site</button>

    <div class="group-button__checkbox flex__wrap mb-3">
        <input type="checkbox" name="call_recheck" id="call_recheck"
            {if isset($callCheck) && $callCheck == 'on'}checked{/if}>
        <label for="call_recheck">Chưa gọi</label>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
            <!-- From Date -->
            <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
                <span class="text-label">Từ ngày: </span>
                <div class="dateTime d-flex gap-2 position-relative">
                    <input class="date_input box-input" type="text" maxlength="10" size="8" name="from_date"
                        id="from_date" value="{$from_date}" autocomplete="off" />
                    <button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
                        <!-- calendar SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path
                                d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z" />
                            <path
                                d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Arrow Icon -->
            <svg width="40" height="20" fill="none">
                <g clip-path="url(#arrow_clip)" stroke="#718096" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M33.5 8.5L36 11M4 11h32" />
                </g>
                <defs>
                    <clipPath id="arrow_clip">
                        <path fill="#fff" d="M0 0h40v20H0z" />
                    </clipPath>
                </defs>
            </svg>

            <!-- To Date -->
            <div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
                <span class="text-label">Đến ngày: </span>
                <div class="dateTime d-flex gap-2 position-relative">
                    <input class="date_input box-input" type="text" maxlength="10" size="8" name="to_date" id="to_date"
                        value="{$to_date}" autocomplete="off" />
                    <button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
                        <!-- calendar SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path
                                d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z" />
                            <path
                                d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z" />
                        </svg>
                    </button>
                </div>
            </div>

            <input type="submit" id="btnView" name="btnView" class="btn btn-primary" value="Lọc" title="Lọc kết quả" />
        </div>
    </div>

    {literal}
        <script type="text/javascript">
            Calendar.setup({
                inputField: "from_date",
                button: "from_date_trigger",
                daFormat: "%d-%m-%Y",
                singleClick: true
            });
            Calendar.setup({
                inputField: "to_date",
                button: "to_date_trigger",
                daFormat: "%d-%m-%Y",
                singleClick: true
            });
        </script>
    {/literal}
</form>
<div class="box-section">
    <table class="table">
        <thead>
            <tr>
                <th scope="col" style="width: 10%;">STT</th>
                <th scope="col" style="width: 10%;">Số ĐT</th>
                <th scope="col" style="width: 30%;">Booking</th>
                <th scope="col" style="width: 10%;">Voucher</th>
                <th scope="col" style="width: 10%;">Ngày Đăng Kí</th>
                <th scope="col" style="width: 10%;">Gửi Zalo</th>
                <th scope="col" style="width: 10%;">Đã gọi</th>
                <th scope="col" style="width: 10%;">Recall</th>
            </tr>
        </thead>
        <tbody>
            {assign var="counter" value=$pageData.offsets.current}
            {foreach from=$phone_data item=row}
                {assign var="counter" value=$counter+1}
                <tr>
                    <th scope="row">{$counter}</th>
                    <td>{$row.phone_number}</td>
                    <td>
                        {if isset($row.booking_name) && $row.booking_name !== ''}
                            <a
                                href="index.php?module=EC_Flight_Bookings&return_module=EC_Flight_Bookings&action=DetailView&record={$row.booking_id}">
                                {$row.booking_name}
                            </a>

                            {if $row.is_used != '1'}
                                (Tham khảo)
                            {/if}

                        {/if}
                    </td>


                    {* <td>{$row.source}</td> *}
                    <td>{sugar_number_format var=$row.discount_value|default:0}</td>
                    <td>{$row.date_entered|date_format:"%H:%M %d/%m/%Y"}</td>
                    {* <td>{if $row.is_zns == '1'}✅{else}❌{/if}</td> *}
                    <td>
                        {if $row.is_zns == true}
                            <a href="index.php?module=EC_Messages&return_module=EC_Messages&action=DetailView&record={$row.zns_id}"
                                target="_blank">✅
                                {if $row.zns_status == 'fail'} (thất bại)
                                {/if}
                            </a>
                        {else}
                            ❌
                        {/if}
                    </td>
                    <td>
                        {if $row.in_calls && $row.call_id}
                            <a href="/index.php?module=Calls&return_module=Calls&action=DetailView&record={$row.call_id}"
                                target="_blank">✅</a>
                        {else}
                            ❌
                        {/if}
                    </td>
                    <td>{$row.recall_count}</td>
                </tr>
            {/foreach}

        </tbody>
        <tr id='pagination' class="pagination-unique" role='presentation'>
            <td colspan="{$colCount}" align="right" class="paginationChangeButtons">
                <!-- Start Button -->
                {if $pageData.urls.startPage}
                    <button class="button" onClick="location.href='{$pageData.urls.startPage}'" title="{$navStrings.start}">
                        {sugar_getimage name="start" ext=".png" alt=$navStrings.start other_attributes='align="absmiddle" border="0"'}
                    </button>
                {else}
                    <button class="button" disabled="disabled">
                        {sugar_getimage name="start_off" ext=".png" alt=$navStrings.start other_attributes='align="absmiddle" border="0"'}
                    </button>
                {/if}

                <!-- Prev Button -->
                {if $pageData.urls.prevPage}
                    <button class="button" onClick="location.href='{$pageData.urls.prevPage}'"
                        title="{$navStrings.previous}">
                        {sugar_getimage name="previous" ext=".png" alt=$navStrings.previous other_attributes='align="absmiddle" border="0"'}
                    </button>
                {else}
                    <button class="button" disabled="disabled">
                        {sugar_getimage name="previous_off" ext=".png" alt=$navStrings.previous other_attributes='align="absmiddle" border="0"'}
                    </button>
                {/if}

                <!-- Page X - Y of Z -->
                <span class="pageNumbers">
                    ({if $pageData.offsets.lastOffsetOnPage == 0}0{else}{$pageData.offsets.current+1}{/if}
                    - {$pageData.offsets.lastOffsetOnPage}
                    {$navStrings.of} {$pageData.offsets.total})
                </span>

                <!-- Next Button -->
                {if $pageData.urls.nextPage}
                    <button class="button" onClick="location.href='{$pageData.urls.nextPage}'" title="{$navStrings.next}">
                        {sugar_getimage name="next" ext=".png" alt=$navStrings.next other_attributes='align="absmiddle" border="0"'}
                    </button>
                {else}
                    <button class="button" disabled="disabled">
                        {sugar_getimage name="next_off" ext=".png" alt=$navStrings.next other_attributes='align="absmiddle" border="0"'}
                    </button>
                {/if}

                <!-- End Button -->
                {if $pageData.urls.endPage && $pageData.offsets.total != $pageData.offsets.lastOffsetOnPage}
                    <button class="button" onClick="location.href='{$pageData.urls.endPage}'" title="{$navStrings.end}">
                        {sugar_getimage name="end" ext=".png" alt=$navStrings.end other_attributes='align="absmiddle" border="0"'}
                    </button>
                {else}
                    <button class="button" disabled="disabled">
                        {sugar_getimage name="end_off" ext=".png" alt=$navStrings.end other_attributes='align="absmiddle" border="0"'}
                    </button>
                {/if}
            </td>
        </tr>
    </table>
</div>