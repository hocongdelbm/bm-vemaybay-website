<form name="bugsQuickCreate" id="bugsQuickCreate" method="POST" action="index.php">
<input type="hidden" name="module" value="Bugs">
<input type="hidden" name="email_id" value="{$REQUEST.email_id}">
<input type="hidden" name="account_id" value="{$REQUEST.account_id}">			
<input type="hidden" name="case_id" value="{$REQUEST.acase_id}">
<input type="hidden" name="contact_id" value="{$REQUEST.contact_id}">
<input type="hidden" name="return_action" value="{$REQUEST.return_action}">
<input type="hidden" name="return_module" value="{$REQUEST.return_module}">
<input type="hidden" name="return_id" value="{$REQUEST.return_id}">
<input type="hidden" name="action" value='Save'>
<input type="hidden" name="duplicate_parent_id" value="{$REQUEST.duplicate_parent_id}">
<input type="hidden" name="to_pdf" value='1'>
<input id='assigned_user_id' name='assigned_user_id' type="hidden" value="{$ASSIGNED_USER_ID}" />
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td align="left" style="padding-bottom: 2px;">
		<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button" type="submit" name="button" {$saveOnclick|default:"onclick=\"return check_form('BugsQuickCreate');\""} value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " >
		<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" type="submit" name="button" {$cancelOnclick|default:"onclick=\"this.form.action.value='$RETURN_ACTION'; this.form.module.value='$RETURN_MODULE'; this.form.record.value='$RETURN_ID'\""} value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  ">
		<input title="{$APP.LBL_FULL_FORM_BUTTON_TITLE}" accessKey="{$APP.LBL_FULL_FORM_BUTTON_KEY}" class="button" type="submit" name="button" onclick="this.form.to_pdf.value='0';this.form.action.value='EditView'; this.form.module.value='Bugs';" value="  {$APP.LBL_FULL_FORM_BUTTON_LABEL}  "></td>
	<td align="right" nowrap><span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span> {$APP.NTC_REQUIRED}</td>
	</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
<tr>
<td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<th align="left" scope="row" colspan="4"><h4><span>{$MOD.LBL_BUG_INFORMATION}</span></h4></th>
	</tr>
	<tr>
	<td valign="top" scope="row" width="15%"><span>{$MOD.LBL_SUBJECT} <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></span></td>
	<td width="35%"><span><textarea name='name' cols="40" tabindex='1' rows="1">{$NAME}</textarea></span></td>
	<td scope="row" width="15%"><span>{$MOD.LBL_TYPE}</span></td>
	<td width="35%"><span><select tabindex='2' name='type'>{$TYPE_OPTIONS}</select></span></td>
	</tr>
	<tr>
	<td valign="top" scope="row" rowspan="2" width="15%"><span>{$MOD.LBL_DESCRIPTION}</span></td>
	<td rowspan="2" width="35%"><span><textarea name='description' tabindex='1' cols="40" rows="4">{$DESCRIPTION}</textarea></span></td>
	<td scope="row" width="15%"><span>{$MOD.LBL_PRIORITY}</span></td>
	<td  nowrap width="35%"><span><select  tabindex='2' name='priority'>{$PRIORITY_OPTIONS}</select></span></td>
	</tr>
	<tr>
	<td scope="row" width="15%"><span>{$MOD.LBL_STATUS}</span></td>
	<td width="35%"><span><select tabindex='2' name='status'>{$STATUS_OPTIONS}</select></span></td>
	</tr>
	</table>
	</form>
<script>
	{$additionalScripts}
</script>