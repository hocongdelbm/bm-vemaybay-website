<table cellpadding="0" cellspacing="0" border="0"  data-empty="{$APP.MSG_LIST_VIEW_NO_RESULTS_BASIC}" class="list view table-responsive">
    <thead>
        {$PAGINATION}
        <tr>
            {foreach from=$HEADER_CELLS key=colHeader item=header}
                <th data-type="html">{$header}</th>
            {/foreach}
            <th data-type="html"><!-- extra th for the button --></th>
        </tr>
        <tr id="{$SUBPANEL_ID}_search" class="pagination" style="{$DISPLAY_SPS}">
            <td align="right" colspan="20">
                {$SUBPANEL_SEARCH}
            </td>
        </tr>
    </thead>
    <tbody>
    {foreach from=$ROWS key=rowHeader item=row}
        <tr>
            {foreach from=$row key=colHeader item=cell}
                <td>{$cell}</td>
            {/foreach}
            <td>
                {if $ROWS_BUTTONS.$rowHeader|@count gt 0}
                    {sugar_action_menu id="$rowHeader" buttons=$ROWS_BUTTONS.$rowHeader class="clickMenu subpanel records fancymenu button" flat=false}
                {/if}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>