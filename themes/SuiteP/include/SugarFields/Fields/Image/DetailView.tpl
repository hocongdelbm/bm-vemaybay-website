<span class="sugar_field" id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}">
{if strlen({{sugarvar key='value' string=true}}) <= 0}
    <img src="" style="max-width: {if !$vardef.width}{{$vardef.width}}{else}200{/if}px;" height="{if !$vardef.height}{{$vardef.height}}{else}50{/if}">
{else}
    <!-- <img src="index.php?entryPoint=download&id={$fields.{{$vardef.fileId}}.value}_{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}{$fields.width.value}&type={{$vardef.linkModule}}" style="max-width: {if !$vardef.width}{{$vardef.width}}{else}200{/if}px;" height="{if !$vardef.height}{{$vardef.height}}{else}50{/if}"> -->
    <img src="index.php?entryPoint=download&id={$fields.{{$vardef.fileId}}.value}_{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}{$fields.width.value}&type={{$vardef.linkModule}}" style="max-width: 100%; object-fit: contain;" width="{if !$vardef.width}{{$vardef.width}}{else}200{/if}" height="{if !$vardef.height}{{$vardef.height}}{else}50{/if}">
{/if}
</span>