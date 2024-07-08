<table border="0" cellpadding="0" cellspacing="0" class="table-searchview__Datetimecombo_in_theme">
<tr valign="middle">
	<td nowrap class="pe-2">
		<!-- <input autocomplete="off" type="text" id="{{sugarvar key='name'}}_date" value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}" size="11" maxlength="10" title='{{$vardef.help}}' {{if !empty($tabindex)}} tabindex='{{$tabindex}}' {{/if}}  onblur="combo_{{sugarvar key='name'}}.update(); {{if isset($displayParams.updateCallback)}}{{$displayParams.updateCallback}}{{/if}}"> -->
		{capture assign="other_attributes"}alt="{$APP.LBL_ENTER_DATE}" style="position:relative; top:6px" border="0" id="{{sugarvar key='name'}}_trigger"{/capture}
		<!-- <button id="{{sugarvar key='name'}}_trigger" class="btn btn-danger">
			<span class="suitepicon suitepicon-module-calendar" alt="{$APP.LBL_ENTER_DATE}"></span>
		</button> -->

		<span class="dateTime position-relative">
			<input class="date_input w-100" autocomplete="off" type="text"  id="{{sugarvar key='name'}}_date" value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}" size="11" maxlength="10" title='{{$vardef.help}}' {{if !empty($tabindex)}} tabindex='{{$tabindex}}' {{/if}}  onblur="combo_{{sugarvar key='name'}}.update(); {{if isset($displayParams.updateCallback)}}{{$displayParams.updateCallback}}{{/if}}">
			<button class="icon_dateTime" type="button" id="{{sugarvar key='name'}}_trigger">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
					<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
					<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
				</svg>
			</button>
		</span>

	{{if empty($displayParams.splitDateTime)}}
	</td>
	<td nowrap>
	{{else}}
	<br>
	{{/if}}
<div id="{{sugarvar key='name'}}_time_section"></div>
{{if $displayParams.showNoneCheckbox}}
<script type="text/javascript">
function set_{{sugarvar key='name'}}_values(form) {ldelim}
 if(form.{{sugarvar key='name'}}_flag.checked)  {ldelim}
	form.{{sugarvar key='name'}}_flag.value=1;
	form.{{sugarvar key='name'}}.value="";
	form.{{sugarvar key='name'}}.readOnly=true;
 {rdelim} else  {ldelim}
	form.{{sugarvar key='name'}}_flag.value=0;
	form.{{sugarvar key='name'}}.readOnly=false;
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
<input type="hidden" id="{{sugarvar key='name'}}" name="{{sugarvar key='name'}}" value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}">
<script type="text/javascript" src="{sugar_getjspath file='include/SugarFields/Fields/Datetimecombo/Datetimecombo.js'}"></script>
<script type="text/javascript">
var combo_{{sugarvar key='name'}} = new Datetimecombo("{$fields[{{sugarvar key='name' stringFormat=true}}].value}", "{{sugarvar key='name'}}", "{$TIME_FORMAT}", "{{$tabindex}}", '{{$displayParams.showNoneCheckbox}}', '{$fields[{{sugarvar key='name' stringFormat=true}}_flag].value}', true);
//Render the remaining widget fields
text = combo_{{sugarvar key='name'}}.html('{{$displayParams.updateCallback}}');
document.getElementById('{{sugarvar key='name'}}_time_section').innerHTML = text;

//Call eval on the update function to handle updates to calendar picker object
eval(combo_{{sugarvar key='name'}}.jsscript('{{$displayParams.updateCallback}}'));
</script>

<script type="text/javascript">
function update_{{sugarvar key='name'}}_available() {ldelim}
      YAHOO.util.Event.onAvailable("{{sugarvar key='name'}}_date", this.handleOnAvailable, this);
{rdelim}

update_{{sugarvar key='name'}}_available.prototype.handleOnAvailable = function(me) {ldelim}
	Calendar.setup ({ldelim}
	onClose : update_{{sugarvar key='name'}},
	inputField : "{{sugarvar key='name'}}_date",
	ifFormat : "{$CALENDAR_FORMAT}",
	daFormat : "{$CALENDAR_FORMAT}",
	button : "{{sugarvar key='name'}}_trigger",
	singleClick : true,
	step : 1,
        startWeekday: {$CALENDAR_FDOW|default:'0'},
	weekNumbers:false
	{rdelim});

	//Call update for first time to round hours and minute values
	combo_{{sugarvar key='name'}}.update(false);
{rdelim}

var obj_{{sugarvar key='name'}} = new update_{{sugarvar key='name'}}_available();
</script>
