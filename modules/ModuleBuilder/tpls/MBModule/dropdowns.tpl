<div id='dropdowns'>
<input type='button' name='adddropdownbtn' value='{$LBL_BTN_ADDDROPDOWN}' class='btn btn-primary' onclick='ModuleBuilder.getContent("module=ModuleBuilder&action=dropdown&refreshTree=1");'>

<table width='100%'>
	<colgroup span='3' width='33%'>
	<tr>
		{counter name='items' assign='items' start=0} {foreach from=$dropdowns
		key='name' item='def'} {if $items % 3 == 0 && $items != 0}
	</tr>
	<tr>
		{/if}
		<td><a class='mbLBLL' href='javascript:void(0)'
			onclick='ModuleBuilder.getContent("module=ModuleBuilder&action=dropdown&dropdown_name={$name}")'>{$name}</a></td>
		{counter name='items'} {/foreach} {if $items == 0}
		<td class='mbLBLL'>{$mod_strings.LBL_NONE}</td>
		{elseif $items % 3 == 1}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{elseif $items % 3 == 2}
		<td>&nbsp;</td>
		{/if}
	</tr>
</table>

<script>
ModuleBuilder.helpRegisterByID('dropdowns', 'input');
ModuleBuilder.helpSetup('dropdowns','default');
</script> {include
file='modules/ModuleBuilder/tpls/assistantJavascript.tpl'}