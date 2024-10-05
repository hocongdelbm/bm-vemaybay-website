<form name='CreatePackage' id='CreatePackage'>
<input type='hidden' name='module' value='ModuleBuilder'>
<input type='hidden' name='action' value='SavePackage'>
<input type='hidden' name='duplicate' value='0'>
<input type='hidden' name='to_pdf' value='1'>
<input type='hidden' name='original_name' value='{$package->name}'>

<h2>{$package->title}</h2>
<table class='mbTable' >
	<tr>
		<td></td>
		<td>
			<div class="d-flex gap-2 align-items-center">
				<input type='button' name='savebtn' value='{$mod_strings.LBL_BTN_SAVE}' class='btn btn-primary' onclick="document.CreatePackage.action.value='SavePackage';ModuleBuilder.handleSave('CreatePackage');">
				{if !empty($package->name)}
					<input type='button' name='duplicatebtn' value='{$mod_strings.LBL_BTN_DUPLICATE}' class='btn btn-primary' onclick="document.CreatePackage.action.value='SavePackage';document.CreatePackage.duplicate.value=1;ModuleBuilder.handleSave('CreatePackage');">
					<input type='button' name='deploybtn' value='{$mod_strings.LBL_BTN_DEPLOY}' class='btn btn-success' onclick="ModuleBuilder.packageDeploy('CreatePackage', {$package_already_deployed});">
					<input type='button' name='publishbtn' value='{$mod_strings.LBL_BTN_PUBLISH}' class='btn btn-primary' onclick="ModuleBuilder.packagePublish('CreatePackage');">
					<input type='button' name='exportbtn' value='{$mod_strings.LBL_BTN_EXP}' class='btn btn-primary' onclick="ModuleBuilder.packageExport('CreatePackage');">
					<input type='button' name='deletebtn' value='{$mod_strings.LBL_BTN_DELETE}' class='btn btn-danger' onclick="ModuleBuilder.packageDelete('{$package->name}');">
				{/if}
			</div>
		</td>
	</tr>
	<tr>
		<td height='100%'>&nbsp;</td><td>&nbsp;</td>
	</tr>
	{if !empty($package->name)}
	<tr><td class='mbLBL'  ><b>{$mod_strings.LBL_LAST_MODIFIED}</b></td><td>{$package->date_modified}</td></tr>
	{/if}
	<tr>
		<td class='mbLBL' ><font color="#ff0000"> * </font><b>{$mod_strings.LBL_PACKAGE_NAME}</b></td><td>
		<input type='text' name='name' class="box-input" size='36' maxlength='36' value='{$package->name}'>
		</td>
	</tr>
	<tr>
		<td class='mbLBL' ><b>{$mod_strings.LBL_AUTHOR}</b></td><td><input type='text' name='author' class="box-input" size='36' maxlength='36' value='{$package->author}'></td>
	</tr>
	
	<tr>
	
		<td class='mbLBL' ><font color="#ff0000"> * </font><b>{$mod_strings.LBL_KEY}</b></td>
		<td>
		{if empty($package->key)}
		<input type='text' class="box-input" name='key' size='5' maxlength='5' value='{$package->key}'>
		{else}
			{$package->key}<input type='hidden' name='key' size='5' maxlength='5' value='{$package->key}'>
		{/if}
		</td>
	</tr>

	<tr>
		<td class='mbLBL'  ><b>{$mod_strings.LBL_DESCRIPTION}</b></td><td><textarea class="box-textarea" name='description' cols='60' rows='5'>{$package->description}</textarea></td>
	</tr>

	<tr>	
		<td></td><td id='readme'>{sugar_getimage name="advanced_search" ext=".gif" alt=$mod_strings.LBL_HIDEOPTIONS other_attributes='border="0" id="options" '}<a style="text-decoration:none" href="javascript: void(0)" onclick ="toggleEl('readmefield');"><b>{$mod_strings.LBL_ADD_README}</b></a></td>
	</tr>
	<tr>
		<td height='100%'>&nbsp;</td><td>&nbsp;</td>
	</tr>
	<tr id='readmefield' style="display:none;">
		<td></td>
		<td ><textarea name='readme' cols='60' rows='10'>{$package->readme}</textarea></td>
	</tr>
	
	{if !empty($package->name)}
	
	<tr>
		<td class='mbLBL'><b>{$mod_strings.LBL_MODULES}</b></td>
		<td >
			<table>
				<tr id="package_modules">
					{counter name='items' assign='items' start=1}
					<td align='center'>
						<table id="new_module" onclick="ModuleBuilder.addModule('{$package->name}')" class='wizardButton' onmousedown="ModuleBuilder.buttonDown(this);return false;" onmouseout="ModuleBuilder.buttonOut(this);">
						<tr>
							<td  align='center'>
								<a href="#">
									<span class="suitepicon suitepicon-module-new-module"></span>
								</a>
							</td>
						</tr>
						<tr><td>
						  <a  class='studiolink' href="javascript:void(0)" onclick="ModuleBuilder.addModule('{$package->name}')">
						      {$mod_strings.LBL_NEW_MODULE}</a>
						</td></tr>
						</table>
						
						
					</td>
					{foreach from=$package->moduleTypes key='name' item='type'}
					{assign var='imgurl' value=$type|cat:'_32'}
						{if $items % 4 == 0 && $items != 0}
							</tr><tr>
						{/if}
						<td align='center'>
						<table id= "existing_module" onclick="ModuleBuilder.viewModule('{$package->name}', '{$name}')" class='wizardButton' onmousedown="ModuleBuilder.buttonDown(this);return false;" onmouseout="ModuleBuilder.buttonOut(this);">
						  <tr>
						      <td  align='center'>{sugar_image name=$type width=48 height=48}</td>
						  </tr><tr><td align='center'>
						      <a  class='studiolink' href="javascript:void(0)" onclick="ModuleBuilder.viewModule('{$package->name}', '{$name}')">
				              {$name}</a>
				          </td></tr>
				        </table></td>
					{counter name='items'}	
					{/foreach}
					   
					</tr>
	</table>
		</td>
	</tr>
	
	{/if}
	
	<tr>
		<td height='100%'>&nbsp;</td><td>&nbsp;</td>
	</tr>
</table>
</form>
{literal}
<script>
addForm('CreatePackage');
addToValidate('CreatePackage', 'name', 'DBName', true, '{/literal}{$mod_strings.LBL_JS_VALIDATE_NAME}{literal}');
addToValidateIsInArray('CreatePackage', 'name', 'in_array', false, '{/literal}{$mod_strings.LBL_JS_VALIDATE_PACKAGE_NAME}','{$package_labels}'{literal}, 'u==');
addToValidate('CreatePackage', 'key', 'DBNameRaw', true, '{/literal}{$mod_strings.LBL_JS_VALIDATE_KEY}{literal}');

ModuleBuilder.helpRegister('CreatePackage');
ModuleBuilder.helpRegisterByID('CreatePackage','td');
if(document.getElementById('package_modules'))
	ModuleBuilder.helpRegisterByID('package_modules', 'table');
ModuleBuilder.helpSetup({/literal}'{$package->help.group}','{$package->help.default}'{literal});
function toggleEl(){
		var display = document.getElementById('readmefield').style.display;
		if(display=='table-row' || display=='inline-block'){
			document.getElementById('options').src = "{/literal}{sugar_getimagepath file='advanced_search.gif'}{literal}";
			document.getElementById('readmefield').style.display ='none';
		}else if (display=='none'){
			document.getElementById('options').src = "{/literal}{sugar_getimagepath file='basic_search.gif'}{literal}";
			document.getElementById('readmefield').style.display = (navigator.appName=="Microsoft Internet Explorer")? 'inline-block' : 'table-row';
		}
	}
</script>
{/literal}
{include file='modules/ModuleBuilder/tpls/assistantJavascript.tpl'}