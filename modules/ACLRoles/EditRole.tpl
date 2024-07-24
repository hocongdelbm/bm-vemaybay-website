<form method='POST' name='EditView' id='ACLEditView'>
<input type='hidden' name='record' value='{$ROLE.id}'>
<input type='hidden' name='module' value='ACLRoles'>
<input type='hidden' name='action' value='Save'>
<input type='hidden' name='return_record' value='{$RETURN.record}'>
<input type='hidden' name='return_action' value='{$RETURN.action}'>
<input type='hidden' name='return_module' value='{$RETURN.module}'>

<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-primary" onclick="this.form.action.value='Save';aclviewer.save('ACLEditView');return false;" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" > &nbsp;
<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}"   class='btn btn-danger' accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" type='button' name='save' value="{$APP.LBL_CANCEL_BUTTON_LABEL}" class='button' onclick='aclviewer.view("{$ROLE.id}", "All");'>

<table width="100%" class="table-detail table-detail__role" border="0" cellpadding=0 cellspacing=1>
{if !empty($CATEGORIES[$CATEGORY_NAME])}
	<tr>
	{foreach from=$ACTION_NAMES item="ACTION_LABEL" key="ACTION_NAME"}
		{foreach from=$CATEGORIES[$CATEGORY_NAME] item="ACTIONS"}
			{foreach from=$ACTIONS item="ACTION" key="ACTION_NAME_ACTIVE"}
				{if $ACTION_NAME==$ACTION_NAME_ACTIVE}
					<td align='center'><div><b>{$ACTION_LABEL}</b></div></td>
				{/if}
			{/foreach}
		{/foreach}
	{foreachelse}
		<td colspan="2">&nbsp;</td>
	{/foreach}
	</tr>
	
	<tr>
	{foreach from=$ACTION_NAMES item="ACTION_LABEL" key="ACTION_NAME"}
	    {foreach from=$CATEGORIES[$CATEGORY_NAME] item="ACTIONS"}
	        {foreach from=$ACTIONS item="ACTION" key="ACTION_NAME_ACTIVE"}
	            {if $ACTION_NAME==$ACTION_NAME_ACTIVE}	
				<td nowrap width='{$TDWIDTH}%' style="text-align: center;" >
					<div  style="display: none" id="{$ACTION.id}">
					{if $APP_LIST.moduleList[$CATEGORY_NAME]==$APP_LIST.moduleList.Users && $ACTION_LABEL != $MOD.LBL_ACTION_ADMIN}
					<select DISABLED class="acl{$ACTION.accessName}" name='act_guid{$ACTION.id}' id = 'act_guid{$ACTION.id}' onblur="document.getElementById('{$ACTION.id}link').innerHTML=this.options[this.selectedIndex].text; aclviewer.toggleDisplay('{$ACTION.id}');" >
				   		{html_options options=$ACTION.accessOptions selected=$ACTION.aclaccess }
					</select>
					{else}
					<select class="acl{$ACTION.accessName}" name='act_guid{$ACTION.id}' id = 'act_guid{$ACTION.id}' onblur="document.getElementById('{$ACTION.id}link').innerHTML=this.options[this.selectedIndex].text; aclviewer.toggleDisplay('{$ACTION.id}');" >
					{html_options options=$ACTION.accessOptions selected=$ACTION.aclaccess }
					</select>					
					{/if}
					</div>
					<div class="acl{$ACTION.accessName}" id="{$ACTION.id}link" onclick="aclviewer.toggleDisplay('{$ACTION.id}')">{$ACTION.accessName}</div>
				</td>
	            {/if}
	        {/foreach}
	    {/foreach}
	{foreachelse}
	          <td colspan="2">&nbsp;</td>
	{/foreach}
	
	</tr>
{else}
    <tr> <td colspan="2">No Actions Defined</td></tr>
{/if}
</table>
</form>'