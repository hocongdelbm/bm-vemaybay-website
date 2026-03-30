<input type="hidden" id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}_multiselect"
name="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}_multiselect" value="true">
{multienum_to_array string={{sugarvar key='value' string=true}} default={{sugarvar key='default' string=true}} assign="values"}
<select id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}"
name="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}[]"
multiple="true" size='{{$displayParams.size|default:6}}' style="width:150" title='{{$vardef.help}}' tabindex="{{$tabindex}}" {{$displayParams.field}}
{{if !empty($displayParams.accesskey)}} accesskey='{{$displayParams.accesskey}}' {{/if}}
{{if isset($displayParams.javascript)}}{{$displayParams.javascript}}{{/if}}>
{html_options options={{sugarvar key='options' string=true}} selected=$values}
</select>

