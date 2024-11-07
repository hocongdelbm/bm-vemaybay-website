<div id="testOutbound">
		<table  border="0" cellspacing="0" cellpadding="0" class="edit view">
			<tr>
				<td scope="row">
					{$app_strings.LBL_EMAIL_SETTINGS_FROM_TO_EMAIL_ADDR} 
					<span class="required">
						{$app_strings.LBL_REQUIRED_SYMBOL}
					</span>
				</td>
				<td >
					<input type="text" id="outboundtest_from_address" name="outboundtest_from_address" size="35" maxlength="64" value="{$CURRENT_USER_EMAIL}">
				</td>
			</tr>
			<tr>
				<td scope="row" colspan="2">
					<div class="d-flex gap-2 align-items-center justify-content-end">
						<input type="button" class="btn btn-primary" value="   {$app_strings.LBL_EMAIL_SEND}   " onclick="javascript:SUGAR.email2.accounts.testOutboundSettings();">
						<input type="button" class="btn btn-danger" value="   {$app_strings.LBL_CANCEL_BUTTON_LABEL}   " onclick="javascript:SUGAR.email2.accounts.testOutboundDialog.hide();">
					</div>
				</td>
			</tr>

		</table>
</div>
