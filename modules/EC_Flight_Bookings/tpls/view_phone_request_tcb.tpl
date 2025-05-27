<h1 id="report-title" class="title">Danh sách khách tham khảo trên Tìm chuyến bay</h1>

<table class="table">
    <thead>
        <tr>
            <th scope="col">STT</th>
            <th scope="col">Số ĐT</th>
            <th scope="col">Voucher</th>
            <th scope="col">Ngày Đăng Kí</th>
            <th scope="col">Đã Sử dụng</th>
        </tr>
    </thead>
    <tbody>
        {assign var="counter" value=$pageData.offsets.current}
        {foreach from=$phone_data item=row}
            {assign var="counter" value=$counter+1}
            <tr>
                <th scope="row">{$counter}</th>
                <td>{$row.phone_number}</td>
                <td>{$row.discount_value|default:'0'}</td>
                <td>{$row.date_entered|date_format:"%H:%M %d/%m/%Y"}</td>
                <td>{if $row.is_used == '1'}✅{else}❌{/if}</td>
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
                <button class="button" onClick="location.href='{$pageData.urls.prevPage}'" title="{$navStrings.previous}">
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

{* <tr id='pagination' class="pagination-unique" role='presentation'>
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
            <button class="button" onClick="location.href='{$pageData.urls.prevPage}'" title="{$navStrings.previous}">
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
</tr> *}