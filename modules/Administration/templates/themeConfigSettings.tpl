<form name="themeConfigSettings" method="POST">
	<input type="hidden" name="module" value="Administration">
	<input type="hidden" name="action" value="ThemeConfigSettings">
    <input type="hidden" name="do" value="">
	
	<table border="0" cellspacing="1" cellpadding="1" class="actionsContainer">
		<tr>
			<td>
			<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_TITLE}" class="btn btn-save" onclick="document.themeConfigSettings.do.value='save';" type="submit" name="save_button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
			<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-danger" onclick="document.themeConfigSettings.action.value='ThemeSettings';" type="submit" name="cancel_button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
			</td>
		</tr>
	</table>

	<div class='listViewBody'>
		<table id="themeSettings" class="list view" style='margin-bottom:0;' border="0" cellspacing="0" cellpadding="0">

            {foreach from=$config key=name item=def}
                <tr>
                    <td>{$mod[$def.vname]}</td>
                    <td>
                        {if $def.type == 'colour'}
                            <input type="text" id="{$name}" name="{$name}" class="color" value="{$def.value}" size="15" />
                        {elseif $def.type == 'bool'}
                            <input  name="{$name}" value="false" type="hidden">
                            <input  type="checkbox" name="{$name}" value="true" {if $def.value}CHECKED{/if}/>
                        {/if}
                    </td>
                </tr>
            {/foreach}
		</table>
	</div>
	
	<table border="0" cellspacing="1" cellpadding="1" class="actionsContainer">
		<tr>
			<td>
				<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" class="btn btn-save" onclick="document.themeConfigSettings.do.value='save';" type="submit" name="save_button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
				<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" class="btn btn-danger" onclick="document.themeSettings.action.value='';" type="submit" name="cancel_button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
			</td>
		</tr>
	</table>
</form>