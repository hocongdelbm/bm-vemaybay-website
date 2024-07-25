	
{{capture name=idname assign=idname}}{{sugarvar key='name'}}{{/capture}}
{{if !empty($displayParams.idName)}}
    {{assign var=idname value=$displayParams.idName}}
{{/if}}

{{assign var=flag_field value=$vardef.name|cat:_flag}}
<table border="0" cellpadding="0" cellspacing="0" class="dateTime">
<tr valign="middle">

<td nowrap class="dateTimeComboColumn d-flex gap-2 align-items-center">
	<span class="dateTime d-flex gap-2 position-relative">
		<input autocomplete="off" type="text" id="{{$idname}}_date" class="datetimecombo_date date_input" value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}" size="11" maxlength="10" title='{{$vardef.help}}' tabindex="{{$tabindex}}" onblur="combo_{{$idname}}.update();" onchange="combo_{{$idname}}.update(); {{if isset($displayParams.updateCallback)}}{{$displayParams.updateCallback}}{{/if}}"   {{if !empty($displayParams.accesskey)}} accesskey='{{$displayParams.accesskey}}' {{/if}} >
		<button type="button" id="{{$idname}}_trigger" class="icon_dateTime" onclick="return false;">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
				<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
				<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
			</svg>
		</button>
	</span>

	<div id="{{$idname}}_time_section" class="datetimecombo_time_section flex-fill"></div>
	{{if $displayParams.showNoneCheckbox}}
		<script type="text/javascript">
		function set_{{$idname}}_values(form) {ldelim}
		if(form.{{$idname}}_flag.checked)  {ldelim}
			form.{{$idname}}_flag.value=1;
			form.{{$idname}}.value="";
			form.{{$idname}}.readOnly=true;
		{rdelim} else  {ldelim}
			form.{{$idname}}_flag.value=0;
			form.{{$idname}}.readOnly=false;
		{rdelim}
		{rdelim}
		</script>
	{{/if}}
</td>
</tr>


{{if $displayParams.showFormats}}
<tr valign="middle">
<td nowrap>
<span class="dateFormat">{$USER_DATEFORMAT}</span>
</td>
<td nowrap>
<span class="dateFormat">{$TIME_FORMAT}</span>
</td>
</tr>
{{/if}}
</table>
{{if !empty($displayParams.originalFieldName)}}
<input type="hidden" class="DateTimeCombo" id="{{$idname}}" name="{{$displayParams.originalFieldName}}" value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}">
{{else}}
<input type="hidden" class="DateTimeCombo" id="{{$idname}}" name="{{$idname}}" value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}">
{{/if}}
<script type="text/javascript" src="{sugar_getjspath file="include/SugarFields/Fields/Datetimecombo/Datetimecombo.js"}"></script>
<script type="text/javascript">
var combo_{{$idname}} = new Datetimecombo("{$fields[{{sugarvar key='name' stringFormat=true}}].value}", "{{$idname}}", "{$TIME_FORMAT}", "{{$tabindex}}", '{{$displayParams.showNoneCheckbox}}', false, true);
//Render the remaining widget fields
text = combo_{{$idname}}.html('{{$displayParams.updateCallback}}');
document.getElementById('{{$idname}}_time_section').innerHTML = text;

//Call eval on the update function to handle updates to calendar picker object
eval(combo_{{$idname}}.jsscript('{{$displayParams.updateCallback}}'));

addToValidateBinaryDependency('{$form_name}',"{{$idname}}_hours", 'alpha', false, "{$APP.ERR_MISSING_REQUIRED_FIELDS} {$APP.LBL_HOURS}" ,"{{$idname}}_date");
addToValidateBinaryDependency('{$form_name}', "{{$idname}}_minutes", 'alpha', false, "{$APP.ERR_MISSING_REQUIRED_FIELDS} {$APP.LBL_MINUTES}" ,"{{$idname}}_date");
addToValidateBinaryDependency('{$form_name}', "{{$idname}}_meridiem", 'alpha', false, "{$APP.ERR_MISSING_REQUIRED_FIELDS} {$APP.LBL_MERIDIEM}","{{$idname}}_date");

YAHOO.util.Event.onDOMReady(function()
{ldelim}
	Calendar.setup ({ldelim}
	onClose : update_{{$idname}},
	inputField : "{{$idname}}_date",
    form : "{{$displayParams.formName}}",
	ifFormat : "{$CALENDAR_FORMAT}",
	daFormat : "{$CALENDAR_FORMAT}",
	button : "{{$idname}}_trigger",
	singleClick : true,
	step : 1,
	weekNumbers: false,
        startWeekday: {$CALENDAR_FDOW|default:'0'},
	comboObject: combo_{{$idname}}
	{rdelim});

	//Call update for first time to round hours and minute values
	combo_{{$idname}}.update(false);

{rdelim}); 
</script>
