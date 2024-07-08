<h4>{$lblSearchResults} - <i>{$searchString}</i>:</h4>
<hr>
{if count($dashlets)}
<h3>{$searchCategoryString}</h3>
<table width="95%">
	{counter assign=rowCounter start=0 print=false}
	{foreach from=$dashlets item=module}
	{if $rowCounter % 2 == 0}
	<tr>
	{/if}
		<td width="50%" align="left"><a id="{$module.id}_icon" href="javascript:void(0)" onclick="{$module.onclick}" style="text-decoration:none">
				<span class="suitepicon suitepicon-module-{$module.module_name|lower|replace:'_':'-'}"></span>&nbsp;
				<span id="mbLBLL" class="mbLBLL">{$module.title}</span></a><br /></td>
	{if $rowCounter % 2 == 1}
	</tr>
	{/if}
	{counter}
	{/foreach}
</table>
{/if}