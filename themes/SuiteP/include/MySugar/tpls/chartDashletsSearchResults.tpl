<h4>{$lblSearchResults} - <i>{$searchString}</i>:</h4>
<hr>
{if count($charts)}
<h3>{sugar_translate label='LBL_BASIC_CHARTS' module='Home'}</h3>
<table width="100%">
	{foreach from=$charts item=chart}
	<tr>
		<td width="100%" align="left"><a href="javascript:void(0)" onclick="{$chart.onclick}">{$chart.icon}</a>&nbsp;<a class="mbLBLL" href="#" onclick="{$chart.onclick}">{$chart.title}</a><br /></td>
	</tr>
	{/foreach}
</table>
{/if}
