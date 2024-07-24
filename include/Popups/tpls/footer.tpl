{{if $prerow}}

	<input class="btn btn-primary btn-select__include" style="min-width: 80px;" type="button" id="MassUpdate_select_button" value='{$APP.LBL_SELECT_BUTTON_LABEL}' onclick="send_back_selected('{$module}',document.MassUpdate,'mass[]','{$APP.ERR_NOTHING_SELECTED}');">

	</form></div>
{{/if}}
