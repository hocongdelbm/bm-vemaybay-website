<select name='{{$vardef.type_name}}' {{if !empty($tabindex)}} tabindex="{{$tabindex}}" {{/if}}  id='{{$vardef.type_name}}' title='{{$vardef.help}}' onchange='document.{{$form_name}}.{{sugarvar key='name'}}.value="";document.{{$form_name}}.parent_id.value=""; 
        changeParentQSSearchView("{{sugarvar key='name'}}"); checkParentType(document.{{$form_name}}.{{$vardef.type_name}}.value, document.{{$form_name}}.btn_{{sugarvar key='name'}});'>
{html_options options={{sugarvar key='options' string=true}} selected=$fields.{{$vardef.type_name}}.value}
</select>

<br>
{if empty({{sugarvar key='options' string=true}}[$fields.{{$vardef.type_name}}.value])}
	{assign var="keepParent" value = 0}
{else}
	{assign var="keepParent value = 1}
{/if}

<input type="text" name="{{sugarvar key='name'}}" id="{{sugarvar key='name'}}" class="sqsEnabled" {{if !empty($tabindex)}} tabindex="{{$tabindex}}" {{/if}}  size="{{$displayParams.size}}" value="{{sugarvar key='value'}}" autocomplete="off"><input type="hidden" name="{{$vardef.id_name}}" id="{{$vardef.id_name}}"  {if $keepParent}value="{{sugarvar memberName='vardef.id_name' key='value'}}"{/if}>
<span class="id-ff multiple d-flex align-items-center gap-1">
<button type="button" name="btn_{{sugarvar key='name'}}" {{if !empty($tabindex)}} tabindex="{{$tabindex}}" {{/if}}  title="{$APP.LBL_SELECT_BUTTON_TITLE}" class="px-1 btn btn-primary {{if empty($displayParams.selectOnly)}} firstChild{{/if}}" value="{$APP.LBL_SELECT_BUTTON_LABEL}" onclick='if(document.{{$form_name}}.{{$vardef.type_name}}.value != "") open_popup(document.{{$form_name}}.{{$vardef.type_name}}.value, 600, 400, "", true, false, {{$displayParams.popupData}}, "single", true);'>
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
</button>
{{if empty($displayParams.selectOnly)}}
<button type="button" name="btn_clr_{{sugarvar key='name'}}" {{if !empty($tabindex)}} tabindex="{{$tabindex}}" {{/if}}  title="{$APP.LBL_CLEAR_BUTTON_TITLE}"  class="px-1 btn btn-secondary lastChild" onclick="this.form.{{sugarvar key='name'}}.value = ''; this.form.{{sugarvar key='id_name'}}.value = '';" value="{$APP.LBL_CLEAR_BUTTON_LABEL}">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
</button>
{{/if}}
</span>

{literal}
<script type="text/javascript">
if (typeof(changeParentQSSearchView) == 'undefined'){
function changeParentQSSearchView(field) {
	field = YAHOO.util.Dom.get(field);
    var form = field.form;
    var sqsId = form.id + "_" + field.id;
    var typeField =  form.elements["{{$vardef.type_name}}"];
    var new_module = typeField.value;
    if(typeof(disabledModules[new_module]) != 'undefined') {
		sqs_objects[sqsId]["disable"] = true;
		field.readOnly = true;
	} else {
		sqs_objects[sqsId]["disable"] = false;
		field.readOnly = false;
    }
	//Update the SQS globals to reflect the new module choice
    sqs_objects[sqsId]["modules"] = new Array(new_module);
    if (typeof(QSFieldsArray[sqsId]) != 'undefined')
    {
        QSFieldsArray[sqsId].sqs.modules = new Array(new_module);
    }
	if(typeof QSProcessedFieldsArray != 'undefined')
    {
	   QSProcessedFieldsArray[sqsId] = false;
    }
    enableQS(false);
}}
YAHOO.util.Event.onContentReady(
{/literal}
"{{sugarvar key='name'}}"
{literal}
, function() {
    changeParentQSSearchView(
{/literal}
"{{sugarvar key='name'}}"
{literal}
    );
});
</script>
{{$displayParams.disabled_parent_types}}
{/literal}