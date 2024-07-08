<script type="text/javascript" src='{sugar_getjspath file="include/SugarFields/Fields/Dynamicenum/SugarFieldDynamicenum.js"}'></script>
<select class="box-select" name="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}"
        id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}"
        title='{{$vardef.help}}' {{if !empty($tabindex)}} tabindex="{{$tabindex}}" {{/if}}
        {{if !empty($displayParams.accesskey)}} accesskey='{{$displayParams.accesskey}}' {{/if}}  {{$displayParams.field}}
        {{if isset($displayParams.javascript)}}{{$displayParams.javascript}}{{/if}}>

    {if isset({{sugarvar key='value' string=true}}) && {{sugarvar key='value' string=true}} != ''}
        {html_options options={{sugarvar key='options' string=true}} selected={{sugarvar key='value' string=true}}}
    {else}
        {html_options options={{sugarvar key='options' string=true}} selected={{sugarvar key='default' string=true}}}
    {/if}
</select>

<script type="text/javascript">
    if(typeof de_entries == 'undefined'){literal}{var de_entries = new Array;}{/literal}
    var el = document.getElementById("{{$vardef.parentenum}}");
    addLoadEvent(function(){literal}{loadDynamicEnum({/literal}"{{$vardef.parentenum}}","{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}"{literal})}{/literal});
    if (SUGAR.ajaxUI && SUGAR.ajaxUI.hist_loaded) {literal}{loadDynamicEnum({/literal}"{{$vardef.parentenum}}","{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}"{literal})}{/literal}
</script>
