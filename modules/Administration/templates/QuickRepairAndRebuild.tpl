<form>
	<h3>{$MOD.LBL_QUICK_REPAIR_TITLE}</h3><br/>
	<input type="hidden" name="action" value="QuickRepairAndRebuild"/>
	<input type="hidden" name="subaction" value="repairAndClearAll"/> <!--Switch based on $_REQUEST type!-->
	<input type="hidden" name="module" value="Administration"/>
	{html_options multiple ="1" size="10"  name=repair_module[] values=$values output=$output selected=$MOD.LBL_ALL_MODULES}
	<br/><br/>
	{html_checkboxes name="selected_actions" values = $checkbox_values output = $checkbox_output separator="<br />" selected=$checkbox_values }
	<br/>
	<input class="button" type="submit" value="{$MOD.LBL_REPAIR}"/>
</form>
