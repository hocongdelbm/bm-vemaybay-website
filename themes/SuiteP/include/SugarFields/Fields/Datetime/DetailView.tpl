{*
    check to see if 'date_formatted_value' has been added to the vardefs, and use it if it has, otherwise use the normal sugarvar function
*}
{if !empty($vardef.date_formatted_value) }
    {assign var="value" value={$vardef.date_formatted_value} }
{else}
    {if strlen({{sugarvar key='value' string=true}}) <= 0}
        {assign var="value" value={{sugarvar key='default_value' string=true}} }
    {else}
        {assign var="value" value={{sugarvar key='value' string=true}} }
    {/if}
{/if}



<span class="sugar_field" id="{{sugarvar key='name'}}">{$value}</span>
{{if !empty($displayParams.enableConnectors)}}
{if !empty($value)}
{{sugarvar_connector view='DetailView'}}
{/if}
{{/if}}