{{if isset($vardef.display) && $vardef.display == 'readonly'}}
    <input type="hidden" class="sugar_field" id="{{sugarvar key='name'}}" name="{{sugarvar key='name'}}" value="{ {{sugarvar key='value' string=true}} }">
    { {{sugarvar key='options' string=true}}[{{sugarvar key='value' string=true}}]}
{{else}}
    <select name="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}"
            id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}"
            title='{{$vardef.help}}' {{if !empty($tabindex)}} tabindex="{{$tabindex}}" {{/if}}
            {{if !empty($displayParams.accesskey)}}
            accesskey='{{$displayParams.accesskey}}' {{/if}} {{$displayParams.field}}
            {{if isset($displayParams.javascript)}}{{$displayParams.javascript}}{{/if}}>

        {if isset({{sugarvar key='value' string=true}}) && {{sugarvar key='value' string=true}} != ''}
            {html_options options={{sugarvar key='options' string=true}} selected={{sugarvar key='value' string=true}}}
        {else}
            {html_options options={{sugarvar key='options' string=true}} selected={{sugarvar key='default' string=true}}}
        {/if}
    </select>

{{/if}}

