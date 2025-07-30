<form name="themeSettings" method="POST">
	<input type="hidden" name="module" value="Administration">
	<input type="hidden" name="action" value="ThemeSettings">
	<input type="hidden" name="disabled_themes" value="">
	
	<table border="0" cellspacing="1" cellpadding="1" class="actionsContainer">
		<tr>
			<td>
			<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_TITLE}" class="btn btn-save" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
			<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-danger" onclick="document.themeSettings.action.value='';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
			</td>
		</tr>
	</table>

	<div class='listViewBody box-section'>
		<table id="themeSettings" class="list view table-responsive" border="0" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th data-toggle="true">{$MOD.LBL_UW_TYPE_THEME}</th>
                    	<th data-hide="phone,phonelandscape"></th>
					<th>{$MOD.LBL_ENABLED}</th>
					<th>{$MOD.DEFAULT_THEME}</th>
				</tr>
			</thead>
			<tbody>
			{counter start=0 name="colCounter" print=false assign="colCounter"}
			{foreach from=$available_themes key=theme item=themedef}
				<tr>
					<td><b>
					{if $themedef.configurable}<a href="index.php?module=Administration&action=ThemeConfigSettings&theme={$theme}">{$themedef.name}</a>
					{else} {$themedef.name}
					{/if}</b></td>
                    <td><img id="themePreview" style="height: 250px;" src="index.php?entryPoint=getImage&themeName={$theme}&imageName=themePreview.png" border="1"></td>
					<td><input class="disableTheme" name="disabled_themes[{$colCounter}]" value="{$theme}" type="hidden" {if $themedef.enabled && $theme == $default_theme}disabled="disabled"{/if}><input class="disableTheme" type="checkbox" name="disabled_themes[{$colCounter}]" value="" {if $themedef.enabled} {if $theme == $default_theme}disabled="disabled"{/if}  CHECKED{/if}/></td>
					<td><input class="defaultTheme" type="radio" name="default_theme" value="{$theme}" {if $theme == $default_theme}CHECKED{elseif !$themedef.enabled}disabled="disabled"{/if} /></td>
				</tr>
				{counter name="colCounter"}
			{/foreach}
			</tbody>
		</table>
	</div>
	
	<table border="0" cellspacing="1" cellpadding="1" class="actionsContainer">
		<tr>
			<td>
				<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" class="btn btn-save" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
				<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" class="btn btn-danger" onclick="document.themeSettings.action.value='';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript">
	{literal}
	$(document).ready(function() {
		$('.disableTheme').change(function() {
			if(!$(this).is(":checked")) {
				$(this).closest('tr').find("input,button,textarea").not(".disableTheme").attr("disabled", "disabled");
			} else {
				$(this).closest('tr').find("input,button,textarea").not(".disableTheme").removeAttr("disabled");
			}

		});
		$('.defaultTheme').change(function() {
			if($(this).is(":checked")) {
				$(".disableTheme").removeAttr("disabled");
				$(this).closest('tr').find(".disableTheme").attr("disabled", "disabled");
			}

		});
	});
	{/literal}
</script>