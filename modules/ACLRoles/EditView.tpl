<script>
{literal}
function set_focus(){
	document.getElementById('name').focus();
}
{/literal}
</script>

<form method='POST' name='EditView' action='index.php'>
	<table width='100%' border='0' cellpadding=0 cellspacing=0 class="actionsContainer">
		<tbody>
			<tr>
				<td>
					<input type='hidden' name='record' value='{$ROLE.id}'>
					<input type='hidden' name='module' value='ACLRoles'>
					<input type='hidden' name='action' value='Save'>
					<input type='hidden' name='isduplicate' value='{$ISDUPLICATE}'>
					<input type='hidden' name='return_record' value='{$RETURN.record}'>
					<input type='hidden' name='return_action' value='{$RETURN.action}'>
					<input type='hidden' name='return_module' value='{$RETURN.module}'>

					{sugar_action_menu id="roleEditActions" class="clickMenu fancymenu" buttons=$ACTION_MENU flat=true}
				</td>
			</tr>
		</tbody>
	</table>

	<div class="box-section">
		<table class="table-edit table-edit__role"  border='0' cellpadding=0 cellspacing=0>
			<tr>
				<td class="label" scope="row">
					{$MOD.LBL_NAME}:<span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span>
				</td>
				<td>
					<input class="box-input" id="name" name="name" type="text" value="{$ROLE.name}">
				</td>
			</tr>
			<tr>
				<td class="label" scope="row">{$MOD.LBL_DESCRIPTION}:</td>
				<td><textarea class="box-textarea" name='description' cols="10" rows="5">{$ROLE.description}</textarea></td>
			</tr>
		</table>
	</div>
</form>

<script type="text/javascript">
	addToValidate('EditView', 'name', 'varchar', true, '{$MOD.LBL_NAME}');
</script>