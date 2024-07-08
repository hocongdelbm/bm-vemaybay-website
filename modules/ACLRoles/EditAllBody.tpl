
{* BEGIN - SECURITY GROUPS *}
<script type="text/javascript" src='{sugar_getjspath file ='include/javascript/yui/build/selector/selector-min.js'}'></script>
<script language="Javascript" type="text/javascript">

{literal}
function cascadeAccessOption(action,selectEle) {
	var accessOption = selectEle.options[selectEle.selectedIndex].value;
	var accessLabel = selectEle.options[selectEle.selectedIndex].text;
	var nodes = YAHOO.util.Selector.query('.'+action);
	var selectId = '';
	for(i=0; i < nodes.length; i++) {

		selectId 		= nodes[i].id.substring(8);
		nodes[i].value = accessOption;
		var roleCell 	= document.getElementById(selectId+'link');

		if(roleCell != undefined) {
			roleCell.innerHTML = accessLabel;
		}
	}
}
{/literal}

</script>

{* END - SECURITY GROUPS *}



<form method='POST' name='EditView' id='ACLEditView'>
<input type='hidden' name='record' value='{$ROLE.id}'>
<input type='hidden' name='module' value='ACLRoles'>
<input type='hidden' name='action' value='Save'>
<input type='hidden' name='return_record' value='{$RETURN.record}'>
<input type='hidden' name='return_action' value='{$RETURN.action}'>
<input type='hidden' name='return_module' value='{$RETURN.module}'>

<div class="d-flex align-items-center gap-2">
	<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="btn btn-primary" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" onclick="this.form.action.value='Save';aclviewer.save('ACLEditView');return false;" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" id="SAVE_HEADER">
	<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" class='btn btn-danger' accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" type='button' name='save' value="{$APP.LBL_CANCEL_BUTTON_LABEL} " class='button' onclick='aclviewer.view("{$ROLE.id}", "All");'>
</div>

<table class="table-ACLRoles table-details__booking box-section" border='0' cellpadding=0 cellspacing=1>
<thead>
<tr id="ACLEditView_Access_Header">
	<th id="ACLEditView_Access_Header_category"></th>

{* BEGIN - SECURITY GROUPS
	Just get the accessOptions for the Accounts module and use for the header select...less file edits this way.
	Not ideal but it'll work since it's the only way to get that info without editing DetailView.php to pass this with ACTION_NAMES
	{foreach from=$ACTION_NAMES item="ACTION_LABEL" key="ACTION_NAME"}
*}

{foreach from=$CATEGORIES item="TYPES" key="CATEGORY_NAME"}
	{if $CATEGORY_NAME=='Accounts'}
		{foreach from=$ACTION_NAMES item="ACTION_LABEL" key="ACTION_NAME"}
			{foreach from=$TYPES item="ACTIONS"}
				{foreach from=$ACTIONS item="ACTION" key="ACTION_NAME_ACTIVE"}
				{if $ACTION_NAME==$ACTION_NAME_ACTIVE}
				<th align='center'>
					<div class="text-center" id="{$ACTION_NAME}link" onclick="aclviewer.toggleDisplay('{$ACTION_NAME}')">
						<b>{$ACTION_LABEL}</b>
					</div>
					<div style="all: initial; display: none; text-align: center;" id="{$ACTION_NAME}">
						<select class="box-select" name='act_guid{$ACTION_NAME}' id='act_guid{$ACTION_NAME}' onblur="cascadeAccessOption('{$ACTION_NAME}',this); aclviewer.toggleDisplay('{$ACTION_NAME}');" >
						{html_options options=$ACTION.accessOptions selected=$ACTION.aclaccess }
						</select>
					</div>
				</th>

				{*
				<th align='center' id="ACLEditView_Access_Header_{$ACTION_NAME}">
					<div class="text-center"><b>{$ACTION_LABEL}</b></div>
				</th>
				*}
				{/if}
				{/foreach}
			{/foreach}
		{foreachelse}
			<th colspan="2">&nbsp;</th>
		{/foreach}
	{/if}
{/foreach}
{* END - SECURITY GROUPS *}
</tr>
</thead>

{foreach from=$CATEGORIES item="TYPES" key="CATEGORY_NAME"}
	{if $APP_LIST.moduleList[$CATEGORY_NAME] != 'Users'}
	<tr id="ACLEditView_Access_{$CATEGORY_NAME}">
		<td nowrap width='1%' id="ACLEditView_Access_{$CATEGORY_NAME}_category">
			<b>
			{if $APP_LIST.moduleList[$CATEGORY_NAME]=='Users'}
			{$MOD.LBL_USER_NAME_FOR_ROLE}
			{elseif !empty($APP_LIST.moduleList[$CATEGORY_NAME])}
			{$APP_LIST.moduleList[$CATEGORY_NAME]}
			{else}
			{$CATEGORY_NAME}
			{/if}
			</b>
		</td>	
	{foreach from=$ACTION_NAMES item="ACTION_LABEL" key="ACTION_NAME"}
		{assign var='ACTION_FIND' value='false'}
		{foreach from=$TYPES item="ACTIONS"}
			{foreach from=$ACTIONS item="ACTION" key="ACTION_NAME_ACTIVE"}
				{if $ACTION_NAME==$ACTION_NAME_ACTIVE}
					<td nowrap width='{$TDWIDTH}%' class="text-center" id="ACLEditView_Access_{$CATEGORY_NAME}_{$ACTION_NAME}">
					<div  style="display: none" id="{$ACTION.id}">
					{if $APP_LIST.moduleList[$CATEGORY_NAME]==$APP_LIST.moduleList.Users && $ACTION_LABEL != $MOD.LBL_ACTION_ADMIN}
					<select DISABLED name='act_guid{$ACTION.id}' id = 'act_guid{$ACTION.id}' onblur="document.getElementById('{$ACTION.id}link').innerHTML=this.options[this.selectedIndex].text; aclviewer.toggleDisplay('{$ACTION.id}');" >
                    {html_options options=$ACTION.accessOptions selected=$ACTION.aclaccess }
                    </select>
					{else}
{* BEGIN - SECURITY GROUPS : Add class='{$ACTION_NAME}' *}
{*
					<select name='act_guid{$ACTION.id}' id = 'act_guid{$ACTION.id}' onblur="document.getElementById('{$ACTION.id}link').innerHTML=this.options[this.selectedIndex].text; aclviewer.toggleDisplay('{$ACTION.id}');" >
*}
                        <select class='{$ACTION_NAME} box-select' style="all: initial" name='act_guid{$ACTION.id}' id = 'act_guid{$ACTION.id}' onblur="document.getElementById('{$ACTION.id}link').innerHTML=this.options[this.selectedIndex].text; aclviewer.toggleDisplay('{$ACTION.id}');" >
 					{* END - SECURITY GROUPS *}
					{html_options options=$ACTION.accessOptions selected=$ACTION.aclaccess }
					</select>
					{/if}
					</div>
					{if $ACTION.accessLabel == 'dev' || $ACTION.accessLabel == 'admin_dev'}
					   <div class="aclAdmin"  id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div>
					{else}
                       		<!-- <div class="acl{$ACTION.accessName}"  id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div> -->
						{if $ACTION.accessName|lower == 'disabled' || $ACTION.accessName|lower == 'bị chặn'}
							<div class="acl{$ACTION.accessName} aclDisabled"  id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div>
						{elseif $ACTION.accessName|lower == 'all' || $ACTION.accessName|lower == 'tất cả'}
							<div class="acl{$ACTION.accessName} aclAll"  id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div>
						{elseif $ACTION.accessName|lower == 'none' || $ACTION.accessName|lower == 'không'}
							<div class="acl{$ACTION.accessName} aclNone"  id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div>
						{elseif $ACTION.accessName|lower == 'enabled' || $ACTION.accessName == 'Được quyền'}
							<div class="acl{$ACTION.accessName} aclEnabled"  id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div>
						{elseif $ACTION.accessName|lower == 'owner' || $ACTION.accessName|lower == 'chủ sở hữu'}
							<div class="acl{$ACTION.accessName} aclOwner"  id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div>
						{elseif $ACTION.accessName|lower == 'not set' || $ACTION.accessName|lower == 'không thiết lập'}
							<div class="acl{$ACTION.accessName} aclNot_set"  id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div>
						{elseif $ACTION.accessName|lower == 'group' || $ACTION.accessName|lower == 'nhóm'}
							<div class="acl{$ACTION.accessName} aclGroup"  id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div>
						{else}
							<div class="acl{$ACTION.accessName} normal"  id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div>
						{/if}
                    	{/if}
					</td>
					{assign var='ACTION_FIND' value='true'}
				{/if}
			{/foreach}
		{/foreach}
		{if $ACTION_FIND=='false'}
			<td nowrap width='{$TDWIDTH}%' class="text-center" id="ACLEditView_Access_{$CATEGORY_NAME}_{$ACTION_NAME}">
				<div><font color='red'>N/A</font></div>
			</td>
		{/if}
	{/foreach}
	</tr>
    {/if}
{foreachelse}
    <tr> <td colspan="2">No Actions Defined</td></tr>
{/foreach}
</table>

<div class="d-flex align-items-center gap-2 my-3">
	<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="btn btn-primary" onclick="this.form.action.value='Save';aclviewer.save('ACLEditView');return false;" type="button" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " id="SAVE_FOOTER">
	<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}"   class='btn btn-danger' type='button' name='save' value="  {$APP.LBL_CANCEL_BUTTON_LABEL} " class='button' onclick='aclviewer.view("{$ROLE.id}", "All");'>
</div>

</form>
