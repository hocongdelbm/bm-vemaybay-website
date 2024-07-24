<table width="100%">
	{foreach from=$reportCharts item=chart}
	<tr>
		<td align="left"><a class="mbLBLL" href="javascript:void(0)" onclick="{$chart.onclick}">{$chart.title}</a><br /></td>
	</tr>
	{/foreach}
</table>