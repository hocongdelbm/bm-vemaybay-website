{* This is here so currency fields, who don't really have dropdown lists can work. *}
{if is_string({{sugarvar key='options' string=true}})}
<input type="hidden" class="sugar_field" id="{{sugarvar key='name'}}" value="{ {{sugarvar key='options' string=true}} }">
{ {{sugarvar key='options' string=true}} }
{else}
<input type="hidden" class="sugar_field" id="{{sugarvar key='name'}}" value="{ {{sugarvar key='value' string=true}} }">
{ {{sugarvar key='options' string=true}}[{{sugarvar key='value' string=true}}]}
{/if}
{{if !empty($displayParams.enableConnectors)}}
{{sugarvar_connector view='DetailView'}}
{{/if}}