
<!-- PAGIGATION -->
<div class="d-flex align-items-center mb-3">
    {$PAGINATION}
</div>

<table cellpadding="0" cellspacing="0" border="0" class="table-details__booking subpanel-table__in-theme" data-empty="{$APP.MSG_LIST_VIEW_NO_RESULTS_BASIC}" {literal}data-breakpoints='{ "xs": 754, "sm": 750, "md": 768, "lg": 992}'{/literal}>
    <thead>
        <tr class="footable-header">
            {counter start=0 name="colCounter" print=false assign="colCounter"}
            <th class="text-center" data-type="html"><!-- extra th for the plus button --></th> 

            {foreach from=$HEADER_CELLS key=colHeader item=header}
                {* calculate break points for footable *}
                {if $colCounter <= 1}
                    {capture assign="breakpoints"}1{/capture}
                {/if}

                {if $colCounter >= 2 && $colCounter < 5}
                    {capture assign="breakpoints"}xs sm{/capture}
                {/if}

                {if $colCounter >= 5 && $colCounter}
                    {capture assign="breakpoints"}xs sm md{/capture}
                {/if}
                <th data-breakpoints="{if $breakpoints != 1}{$breakpoints}{/if}" data-type="html">{$header}</th>
                {counter name="colCounter" print=false}
            {/foreach}

            <th class="text-center" data-type="html"><!-- extra th for the button --></th>
        </tr>
        
        {* TODO: Break $pagination so that it can be fully customisable *}
        {* $PAGINATION *}
        <tr id="{$SUBPANEL_ID}_search" class="pagination" style="{$DISPLAY_SPS}">
            <td align="right" colspan="20">
                {$SUBPANEL_SEARCH}
            </td>
        </tr>
    </thead>
    <tbody>
    {counter start=0 name="rowCounter" print=false assign="rowCounter"}
    {foreach from=$ROWS key=rowHeader item=row}
        {if $rowCounter % 2 == 0}
            {*Odd row*}
            {assign var="rowClass" value="oddListRowS1"}
        {else}
            {*Even row*}
            {assign var="rowClass" value="evenListRowS1"}
        {/if}
        <tr class="{$rowClass}" >
            <td>&nbsp;</td>
            {foreach from=$row key=colHeader item=cell}
                <td>{$cell}</td>
            {/foreach}

            <td class="center">
                {if isset($ROWS_BUTTONS.$rowHeader) and  $ROWS_BUTTONS.$rowHeader|@count gt 0}
                    {sugar_action_menu id="$rowHeader" buttons=$ROWS_BUTTONS.$rowHeader class="btn btn-primary btn-action__dynamic" flat=false}
                {/if}
            </td>
        </tr>
        {counter name="rowCounter" print=false}
    {/foreach}
    </tbody>
</table>