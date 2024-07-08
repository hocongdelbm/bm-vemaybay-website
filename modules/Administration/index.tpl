<div class="dashletPanelMenu wizard">
<div class="bd">
		<div class="screen admin-panel">
{foreach  from=$ADMIN_GROUP_HEADER key=j item=val1}
    <div class="box-section mt-3">
   {if isset($GROUP_HEADER[$j][1])}
   <p>{$GROUP_HEADER[$j][0]}{$GROUP_HEADER[$j][1]}
   <table class="other view">

   {else}
   <p>{$GROUP_HEADER[$j][0]}{$GROUP_HEADER[$j][2]}
   <table class="other view">
   {/if}

    {assign var='i' value=0}
    {foreach  from=$VALUES_3_TAB[$j] key=link_idx item=admin_option}
        <tr>
            {if isset($COLNUM[$j][$i])}
                <td>
                    <span class="suitepicon suitepicon-admin-{$ICONS[$j][$i]}"></span>
                    <a id='{$ID_TAB[$j][$i]}' href='{$ITEM_URL[$j][$i]}' class="tabDetailViewDL2Link">{$ITEM_HEADER_LABEL[$j][$i]}</a>
                </td>
                <td>{$ITEM_DESCRIPTION[$j][$i]}</td>
            {else}
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            {/if}
        </tr>
    {assign var='i' value=$i+1}
    {/foreach}
</table>
</p>
</div>
{/foreach}

</div>
</div>

</div>


