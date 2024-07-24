<div class="box-section">
<table class='table-config table-connectors__admin' border="0" cellspacing="1" cellpadding="0" >		
	<tr valign="top">
		<td width="35%">
			<table  border="0" cellspacing="2" cellpadding="0" >	
				<tr valign='top'>
					<td>
						<img src="{$IMG}icon_ConnectorConfig.gif" name="connectorConfig" onclick="document.location.href='index.php?module=Connectors&action=ModifyProperties';"
						onMouseOver="document.connectorConfig.src='{$IMG}icon_ConnectorConfigOver.gif'"
						onMouseOut="document.connectorConfig.src='{$IMG}icon_ConnectorConfig.gif'"></td>
					<td>&nbsp;&nbsp;</td>
					<td><b>{$mod.LBL_MODIFY_PROPERTIES_TITLE}</b><br/>
						{$mod.LBL_MODIFY_PROPERTIES_DESC}
					</td>
				</tr>
				<tr>
					<td colspan=2>&nbsp;</td>
				</tr>
				<tr valign='top'>
					<td><img src="{$IMG}icon_ConnectorEnable.gif" name="enableImage" onclick="document.location.href='index.php?module=Connectors&action=ModifyDisplay';"
						onMouseOver="document.enableImage.src='{$IMG}icon_ConnectorEnableOver.gif'"
						onMouseOut="document.enableImage.src='{$IMG}icon_ConnectorEnable.gif'"></td>
					<td>&nbsp;&nbsp;</td>
					<td><b>{$mod.LBL_MODIFY_DISPLAY_TITLE}</b><br/>
						{$mod.LBL_MODIFY_DISPLAY_DESC}
					</td>
				</tr>			
			</table>
		</td>
		<td width="10%">&nbsp;</td>
		<td width="35%">
			<table  border="0" cellspacing="2" cellpadding="0">	   
				<tr valign='top'>
					<td><img src="{$IMG}icon_ConnectorMap.gif" name="connectorMapImg" onclick="document.location.href='index.php?module=Connectors&action=ModifyMapping';"
						onMouseOver="document.connectorMapImg.src='{$IMG}icon_ConnectorMapOver.gif'"
						onMouseOut="document.connectorMapImg.src='{$IMG}icon_ConnectorMap.gif'"></td>
					<td>&nbsp;&nbsp;</td>
					<td><b>{$mod.LBL_MODIFY_MAPPING_TITLE}</b><br/>
						{$mod.LBL_MODIFY_MAPPING_DESC}
					</td>
				</tr>

				<tr>
					<td colspan=2>&nbsp;</td>
				</tr>


				<tr valign='top'>
					<td>
					    <img src="{$IMG}icon_ConnectorSearchFields.gif" name="connectorSearchImg" onclick="document.location.href='index.php?module=Connectors&action=ModifySearch';"
						onMouseOver="document.connectorSearchImg.src='{$IMG}icon_ConnectorSearchFieldsOver.gif'"
						onMouseOut="document.connectorSearchImg.src='{$IMG}icon_ConnectorSearchFields.gif'">
				    </td>
					<td>&nbsp;&nbsp;</td>
				</tr>

			</table>
		</td>				
		<td width="20%">&nbsp;</td>
	</tr>
</table>
</div>
