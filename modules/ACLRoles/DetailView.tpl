
<div class="actionsContainer">
<form action="index.php" method="post" name="DetailView" id="form">
	<input type="hidden" name="module" value="ACLRoles">
	<input type="hidden" name="user_id" value="">
	<input type="hidden" name="record" value="{$ROLE.id}">
	<input type="hidden" name="isDuplicate" value=''>
	<input type='hidden' name='return_record' value='{$RETURN.record}'>
	<input type='hidden' name='return_action' value='{$RETURN.action}'>
	<input type='hidden' name='return_module' value='{$RETURN.module}'>
	<input type="hidden" name="action">

{php}
    $APP = $this->get_template_vars('APP');
    $this->append('buttons',
    <<<EOD
    <input title="{$APP['LBL_EDIT_BUTTON_TITLE']}" accessKey="{$APP['LBL_EDIT_BUTTON_KEY']}" class="btn btn-warning" onclick="var _form = $('#form')[0]; _form.action.value='EditView'; _form.submit();" type="submit" name="button" value="{$APP['LBL_EDIT_BUTTON']}" />
EOD
    );
    $this->append('buttons',
    <<<EOD
    <input title="{$APP['LBL_DUPLICATE_BUTTON_TITLE']}" accessKey="{$APP['LBL_DUPLICATE_BUTTON_KEY']}" class="btn btn-secondary" onclick="this.form.isDuplicate.value='1'; this.form.action.value='EditView'" type="submit" name="button" value=" {$APP['LBL_DUPLICATE_BUTTON']} " />
EOD
    );
    $this->append('buttons',
    <<<EOD
    <input title="{$APP['LBL_DELETE_BUTTON_TITLE']}" accessKey="{$APP['LBL_DELETE_BUTTON_KEY']}" class="btn btn-danger" onclick="this.form.return_module.value='ACLRoles'; this.form.return_action.value='index'; this.form.action.value='Delete'; return confirm('{$APP['NTC_DELETE_CONFIRMATION']}')" type="submit" name="button" value=" {$APP['LBL_DELETE_BUTTON']} " />
EOD
    );
{/php}
		{sugar_action_menu id="userEditActions" class="clickMenu fancymenu SugarActionMenu" buttons="$buttons" flat=true}
</form>
</div>

<table class="box-section table-details__booking table-infor__ACLRoles" border='0' cellpadding=0 cellspacing = 1  >
	<tr>
		<td class="text-label" valign='top' width='15%' align='right'>
			<b>{$MOD.LBL_NAME}:</b>
		</td>
		<td class="text-label" width='85%' colspan='3'>{$ROLE.name}</td>
	</tr>
	<tr>
		<td class="text-label" valign='top'  width='15%' align='right'>
			<b>{$MOD.LBL_DESCRIPTION}:</b>
		</td>
		<td class="text-label" colspan='3' valign='top'  width='85%' align='left'>{$ROLE.description | nl2br}</td>
	</tr>
</table>	

{include file="modules/ACLRoles/EditViewBody.tpl" }