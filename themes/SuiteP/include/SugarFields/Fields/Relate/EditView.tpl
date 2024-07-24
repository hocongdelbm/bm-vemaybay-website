{{capture name=idname assign=idname}}{{sugarvar key='name'}}{{/capture}}
{{if !empty($displayParams.idName)}}
    {{assign var=idname value=$displayParams.idName}}
{{/if}}
<input type="text" name="{{$idname}}" class={{if empty($displayParams.class) }}"sqsEnabled"{{else}} "{{$displayParams.class}}" {{/if}} tabindex="{{$tabindex}}" id="{{$idname}}" size="{{$displayParams.size}}" value="{{sugarvar key='value'}}" title='{{$vardef.help}}' autocomplete="off" {{$displayParams.readOnly}} {{$displayParams.field}}	{{if !empty($displayParams.accesskey)}} accesskey='{{$displayParams.accesskey}}' {{/if}} >
<input type="hidden" name="{{if !empty($displayParams.idNameHidden)}}{{$displayParams.idNameHidden}}{{/if}}{{sugarvar key='id_name'}}" id="{{if !empty($displayParams.idNameHidden)}}{{$displayParams.idNameHidden}}{{/if}}{{sugarvar key='id_name'}}" {{if !empty($vardef.id_name)}}value="{{sugarvar memberName='vardef.id_name' key='value'}}"{{/if}}>
{{if empty($displayParams.hideButtons) }}
	<span class="id-ff multiple row g-0 gap-1 flex-nowrap flex-fill">
		<button type="button" name="btn_{{$idname}}" id="btn_{{$idname}}" tabindex="{{$tabindex}}" title="{sugar_translate label="{{$displayParams.accessKeySelectTitle}}"}" class="px-1 btn btn-primary" value="{sugar_translate label="{{$displayParams.accessKeySelectLabel}}"}" onclick='open_popup("{{sugarvar key='module'}}",600,400,"{{$displayParams.initial_filter}}",true,false,{{$displayParams.popupData}},"single",true);' {{if isset($displayParams.javascript.btn)}}{{$displayParams.javascript.btn}}{{/if}}>
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
		</button>

		{{if empty($displayParams.selectOnly) }}
			<button type="button" name="btn_clr_{{$idname}}" id="btn_clr_{{$idname}}" tabindex="{{$tabindex}}" title="{sugar_translate label="{{$displayParams.accessKeyClearTitle}}"}"  class="px-1 btn btn-secondary" onclick="SUGAR.clearRelateField(this.form, '{{$idname}}', '{{if !empty($displayParams.idName)}}{{$displayParams.idName}}_{{/if}}{{sugarvar key='id_name'}}');"  value="{sugar_translate label="{{$displayParams.accessKeyClearLabel}}"}" {{if isset($displayParams.javascript.btn_clear)}}{{$displayParams.javascript.btn_clear}}{{/if}}>
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
			</button>
		{{/if}}
	</span>
{{/if}}

{{if !empty($displayParams.allowNewValue) }}
<input type="hidden" name="{{$idname}}_allow_new_value" id="{{$idname}}_allow_new_value" value="true">
{{/if}}

<script type="text/javascript">
SUGAR.util.doWhen("typeof(sqs_objects) != 'undefined' && typeof(sqs_objects['{$form_name}_{{$idname}}']) != 'undefined'", enableQS);
</script>
