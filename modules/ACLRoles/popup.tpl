
<script type="text/javascript" src="{sugar_getjspath file="include/javascript/popup_helper.js"}"></script>

<table cellpadding="0" cellspacing="0" width="100%" border="0" class="list view table-list-popup-roles">
	<tr height="20">
		<td scope="col" width="1%" >{$CHECKALL}&nbsp;</td>
		<td scope="col" width="20%"  nowrap><span>{$MOD.LBL_NAME}</span></td>
		<td scope="col" width="10%"  nowrap><span>{$MOD.LBL_DESCRIPTION}</span></td>
	</tr>

{foreach from=$ROLES item="ROLE"}
	<tr height="20" >
		<td>{$PREROW}&nbsp;</td>
		<td valign=TOP  ><span><a href="#" onclick="send_back('Users','{$ROLE.id}');">{$ROLE.name}</a></span></td>
		<td valign=TOP  ><span>{$ROLE.description}</span></td>
	</tr>
{foreachelse}
	<tr>
		<td colspan="2">No Roles</td>
	</tr>
{/foreach}

</table>
{$ASSOCIATED_JAVASCRIPT_DATA}
