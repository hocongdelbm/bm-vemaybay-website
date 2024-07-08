
{{if !$nolink}}
<input type="hidden" class="sugar_field" id="{{sugarvar key='name'}}" value="{{sugarvar key='value'}}">
<input type="hidden" class="sugar_field" id="{{$vardef.id_name}}" value="{{sugarvar key='value' memberName='vardef.id_name'}}">

<a href="index.php?module={{sugarvar objectName='fields' memberName='vardef.type_name' key='value'}}&action=DetailView&record={{sugarvar key='value' memberName='vardef.id_name'}}" class="tabDetailViewDFLink">{{/if}}{{sugarvar key='value'}}{{if !$nolink}}</a>

{{/if}}
{{if !empty($displayParams.enableConnectors)}}
{if !empty({{sugarvar key='value'}})}
{{sugarvar_connector view='DetailView'}}
{/if}
{{/if}}