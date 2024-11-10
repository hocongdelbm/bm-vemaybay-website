{if !empty({{sugarvar key='value' string=true}}) && ({{sugarvar key='value' string=true}} != '^^')}
<input type="hidden" class="sugar_field" id="{{sugarvar key='name'}}" value="{{sugarvar key='value'}}">
{multienum_to_array string={{sugarvar key='value' string=true}} assign="vals"}
{foreach from=$vals item=item}
<li style="margin-left:10px;">{ {{sugarvar key='options' string=true}}.$item }</li>
{/foreach}
{{if !empty($displayParams.enableConnectors)}}
{{sugarvar_connector view='DetailView'}}
{{/if}}
{/if}