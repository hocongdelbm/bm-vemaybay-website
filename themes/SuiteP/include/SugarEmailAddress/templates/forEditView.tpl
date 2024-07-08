{php}
global $emailInstances;
if (empty($emailInstances))
	$emailInstances = array();
if (!isset($emailInstances[$this->_tpl_vars['module']]))
	$emailInstances[$this->_tpl_vars['module']] = 0;
$this->_tpl_vars['index'] = $emailInstances[$this->_tpl_vars['module']];
$emailInstances['module']++;
{/php}
<script type="text/javascript" language="javascript">
var emailAddressWidgetLoaded = false;
</script>
	<script type="text/javascript" src="include/SugarEmailAddress/SugarEmailAddress.js"></script>
<script type="text/javascript">
	var module = '{$module}';
</script>

<div class="row gap-2">
	<div class="col-12 col-md-12 email-address-add-line-container emailaddresses" id="{$module}emailAddressesTable{$index}">
		{capture assign="other_attributes"}id="{$module}{$index}_email_widget_add" onclick="SUGAR.EmailAddressWidget.instances.{$module}{$index}.addEmailAddress('{$module}emailAddressesTable{$index}','', false);"{/capture}
		<button type="button" class="btn btn-primary email-address-add-button" title="{$app_strings.LBL_ID_FF_ADD_EMAIL} " {$other_attributes}>
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
				<path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
			</svg>
		</button>
	</div>
	<div class="col-12 col-md-12 email-address-lines-container">
		{*
		@version > SuiteCRM 7.7.5
		@description Template represents a single email line item

		To customise the layout:
		 ** keep the .template class in the most parent container of a line item
		 ** keep the elements with id's
		 ** don't change the id's of the elements.
		 ** don't add js events inline. Instead bind the event in javascript.
	 	*}
		<div class="row template email-address-line-container hidden">
			<div class="col-12 col-sm-6  email-address-input-container {if $module == "Users"} email-address-users-profile{/if}">
				<div class="input-wrap email-address-input-group d-flex">
					<input type="email" id="email-address-input" placeholder="email@example.com" title="{$app_strings.LBL_EMAIL_TITLE}">
					<input type="hidden" id="record-id">
					<input type="hidden" id="verified-flag" class="verified-flag" value="true"/>
					<input type="hidden" id="verified-email-value" class="verified-email-value" value=""/>
					<input type=hidden id="{$module}_email_widget_id" name="{$module}_email_widget_id" value="">
					<input type=hidden id='emailAddressWidget' name='emailAddressWidget' value='1'>
					<span class="input-group-btn">
						<button type="button" id="email-address-remove-button" class="btn btn-danger email-address-remove-button" name="" title="{$app_strings.LBL_ID_FF_REMOVE_EMAIL}">
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16">
								<path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"/>
							</svg>
						</button>
					</span>
				</div>
			</div>
			<div class="col-12 col-sm-6 email-address-options-container">
				<div class="row">
					<div class="col-6 col-sm-6 col-md-4 col-lg-4 text-center email-address-option">
						<label class="text-sm">{$app_strings.LBL_EMAIL_PRIMARY}</label>
						<input type="radio" name="" id="email-address-primary-flag" class="email-address-primary-flag" value="" enabled="true" tabindex="0" checked="true" title="{$app_strings.LBL_EMAIL_PRIM_TITLE}">
					</div>

					{if $useReplyTo == true}
					<div class="col-6 col-sm-6 col-md-4 col-lg-4 text-center email-address-option">
						<label class="text-sm">{$app_strings.LBL_EMAIL_REPLY_TO}</label>
						<input type="checkbox" name="" id="email-address-reply-to-flag" class="email-address-reply-to-flag" value="" enabled="true">
					</div>
					{/if}

					{if $useOptOut == true}
					<div class="col-6 col-sm-6 col-md-4 col-lg-4 text-center email-address-option">
						<label class="text-sm">{$app_strings.LBL_EMAIL_OPT_OUT}</label>
						<input type="checkbox" name="" title="{$app_strings.LBL_ID_FF_OPT_OUT}" id="email-address-opt-out-flag" class="email-address-opt-out-flag" value="" enabled="true">
					</div>
					{/if}

					{if $useInvalid == true}
					<div class="col-3 col-sm-3 col-md-4 col-lg-3 text-center email-address-option">
						<label class="text-sm">{$app_strings.LBL_EMAIL_INVALID}</label>
						<input type="checkbox" name="" title="{$app_strings.LBL_EMAIL_INVALID}" id="email-address-invalid-flag" class="email-address-invalid-flag" value="" enabled="true">
					</div>
					{/if}

					{if $useOptIn == true}
						<div class="col-6 col-sm-6 col-md-4 col-lg-4 text-center email-address-option">
							<label class="text-sm">{$app_strings.LBL_OPT_IN}</label>
							<input type="hidden" name="shouldSaveOptInFlag" value="1">
							<input type="checkbox" name="" title="{$app_strings.LBL_OPT_IN}"  id="email-address-opted-in-flag" class="email-address-opted-in-flag" value="" enabled="true">
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
</div>
<input type="hidden" name="useEmailWidget" value="true">
<script type="text/javascript" language="javascript">
SUGAR_callsInProgress++;
var eaw = SUGAR.EmailAddressWidget.instances.{$module}{$index} = new SUGAR.EmailAddressWidget("{$module}");
eaw.emailView = '{$emailView}';
eaw.emailIsRequired = "{$required}";
eaw.tabIndex = '{$tabindex}';
var addDefaultAddress = '{$addDefaultAddress}';
var prefillEmailAddress = '{$prefillEmailAddresses}';
var prefillData = {$prefillData};
if(prefillEmailAddress == 'true') {ldelim}
	eaw.prefillEmailAddresses('{$module}emailAddressesTable{$index}', prefillData);
{rdelim} else if(addDefaultAddress == 'true') {ldelim}
	eaw.addEmailAddress('{$module}emailAddressesTable{$index}', '',true);
{rdelim}
if('{$module}_email_widget_id') {ldelim}
   document.getElementById('{$module}_email_widget_id').value = eaw.count;
{rdelim}
SUGAR_callsInProgress--;
</script>
