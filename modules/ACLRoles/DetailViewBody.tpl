{strip}
<table class='table-access table-details__booking my-3' border='0' cellpadding=0 cellspacing=1>
	<thead>
		<tr>
			<th></th>
		{foreach from=$ACTION_NAMES item="ACTION_NAME" }
			<th class="text-center" scope="row"><b>{$ACTION_NAME}</b></th>
		{foreachelse}
			<th colspan="2">&nbsp;</th>
		{/foreach}
		</tr>
	</thead>

{foreach from=$CATEGORIES item="TYPES" key="CATEGORY_NAME"}
    {if $APP_LIST.moduleList[$CATEGORY_NAME]!='Users'}
	<tr>
	{if $APP_LIST.moduleList[$CATEGORY_NAME]=='Users'}
	<td nowrap width='1%' scope="row"><b>{$MOD.LBL_USER_NAME_FOR_ROLE}</b></td>
	{else}
	<td nowrap width='1%' scope="row"><b>{$APP_LIST.moduleList[$CATEGORY_NAME]}</b></td>
	{/if}
	{foreach from=$ACTION_NAMES item="ACTION_LABEL" key="ACTION_NAME"}
		{assign var='ACTION_FIND' value='false'}
		{foreach from=$TYPES item="ACTIONS" key="TYPE_NAME"}
			{foreach from=$ACTIONS item="ACTION" key="ACTION_NAME_ACTIVE"}
				{if $ACTION_NAME==$ACTION_NAME_ACTIVE}
					{assign var='ACTION_FIND' value='true'}
					<td  width='{$TDWIDTH}%' align='center'><div align='center' class="acl{$ACTION.accessLabel|capitalize}"><b>{$ACTION.accessName}</b></div></td>
				{/if}
			{/foreach}
		{/foreach}
		{if $ACTION_FIND=='false'}
			<td nowrap width='{$TDWIDTH}%' style="text-align: center;">
				<div><font color='red'>N/A</font></div>
			</td>
		{/if}
	{/foreach}
	</tr>
    {/if}
{foreachelse}
	<tr> <td colspan="2">No Actions</td></tr>
{/foreach}
</table>
{/strip}