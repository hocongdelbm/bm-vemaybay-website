{literal}
<script type='text/javascript'>
var ERR_RULES_NOT_MET = '{/literal}{$MOD.ERR_RULES_NOT_MET}{literal}';
var ERR_ENTER_OLD_PASSWORD = '{/literal}{$MOD.ERR_ENTER_OLD_PASSWORD}{literal}';
var ERR_ENTER_NEW_PASSWORD = '{/literal}{$MOD.ERR_ENTER_NEW_PASSWORD}{literal}';
var ERR_ENTER_CONFIRMATION_PASSWORD = '{/literal}{$MOD.ERR_ENTER_CONFIRMATION_PASSWORD}{literal}';
var ERR_REENTER_PASSWORDS = '{/literal}{$MOD.ERR_REENTER_PASSWORDS}{literal}';
</script>
<script type='text/javascript' src='{/literal}{sugar_getjspath file="modules/Users/PasswordRequirementBox.js"}{literal}'></script>
{/literal}

<form action="index.php" method="post" name="ChangePasswordForm" id="ChangePasswordForm" onsubmit="return document.getElementById('cant_login').value == ''">
	<input type="hidden" name="entryPoint" value="{$ENTRY_POINT}" />
	<input type='hidden' name='action' value="{$ACTION}" />
	<input type='hidden' name='module' value="{$MODULE}" />
	<input type="hidden" name="guid" value="{$GUID}" />
	<input type="hidden" name="key" value="{$KEY}" />
	<input type="hidden" name="return_module" value="Home" />
	<input type="hidden" name="login" value="1" />
	<input type="hidden" name="is_admin" value="{$IS_ADMIN}" />
	<input type="hidden" name="cant_login" id="cant_login" value="" />
	<input type="hidden" name="old_password" id="old_password" value="" />
	<input type="hidden" name="password_change" id="password_change" value="true" />
	<input type="hidden" value="" name="username_password" id="username_password" />
	<input type="hidden" name="page" value="Change" />
	<input type="hidden" name="return_id" value="{$ID}" />
	<input type="hidden" name="return_action" value="{$return_action}" />
	<input type="hidden" name="record" value="{$ID}" />
	<input type="hidden" name="user_name" value="{$USER_NAME}" />
	<input type='hidden' name='saveConfig' value='0' />

	<div id="form-login" class="table-responsive text-nowrap d-flex align-items-center justify-content-center">
		<div class="form-login__wrapper change-password d-flex gap-4 flex-column">
			<img src="themes/SuiteP/images/home/company_logo.png" alt="" class="my-4">

			{if $ERRORS}
				<span class="alert alert-danger d-block m-0">{$ERRORS}</span>
			{/if}
			
			{if $EXPIRATION_TYPE}
				<span id='post_error' class="error">{$EXPIRATION_TYPE}</span>
			{/if}
			{if $OLD_PASSWORD_FIELD == '' &&  $USERNAME_FIELD == '' }
				<span>NULL</span>
			{/if}
			{$OLD_PASSWORD_FIELD}
			
			{$USERNAME_FIELD}

			<div class="text-field">
				<label for="new_password">{sugar_translate module="Users" label="LBL_NEW_PASSWORD" }</label>
				<div class="input-group">
					<input type="password" size="26" tabindex="2" id="new_password" name="new_password" value="" placeholder="{sugar_translate module="Users" label="LBL_NEW_PASSWORD" }" onkeyup="password_confirmation();newrules('{$PWDSETTINGS.minpwdlength}','{$PWDSETTINGS.maxpwdlength}','{$REGEX}');" />
				</div>
			</div>
			<div class="text-field">
				<label for="confirm_pwd">{sugar_translate module="Users" label="LBL_NEW_PASSWORD2" }</label>
				<div class="input-group">
					<input type="password" size="26" tabindex="2" id="confirm_pwd" name="confirm_pwd" value="" placeholder="{sugar_translate module="Users" label="LBL_NEW_PASSWORD2" }" onkeyup="password_confirmation();" /> 
				</div>
			</div>
			<div id="comfirm_pwd_match" class="alert alert-danger m-0" style="display: none;">{$MOD.LBL_PASSWORD_MIS_MATCH}</div>
			{$CAPTCHA}
			{$SUBMIT_BUTTON}
		</div>
	</div>
</form>
