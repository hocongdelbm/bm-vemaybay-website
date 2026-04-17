{*<!-- tab_panel_content.tpl START -->*}
<div class="row detail-view-row">
{{foreach name=rowIteration from=$panel key=row item=rowData}}
    {{counter name="columnCount" start=0 print=false assign="columnCount"}}
    {{foreach name=colIteration from=$rowData key=col item=colData}}
        {*<!-- COLUMN -->*}
        {{if $smarty.foreach.colIteration.total > 1 && $colData.colspan != 3}}
            {*<!-- DIV column - colspan != 3 -->*}
            <div class="col-12 col-sm-6 col-md-6 detail-view-row-item" data-field="{{$colData.field.name}}">
        {{else}}
            {*<!-- DIV column - colspan = 3 -->*}
            <div class="col-12 col-sm-12 col-md-12 detail-view-row-item" data-field="{{$colData.field.name}}">
        {{/if}}

            <div class="pb-2 row align-items-center g-0">
        {{counter name="fieldCount" start=0 print=false assign="fieldCount"}}
        {{foreach name=fieldIteration from=$colData key=field item=subField}}
            {{if !(!isset($subField.name) || !$subField.name)}}
                {*<!-- [hide!!] -->*}
                {{if $fieldCount < $smarty.foreach.colIteration.total && !empty($colData.field.name)}}

                    {{if $smarty.foreach.colIteration.total > 1 && $colData.colspan != 3}}
                        {*<!-- DIV inside - colspan != 3 -->*}
                    {{if $smarty.foreach.colIteration.index == 0}}
                        <div class="col-4 col-sm-4 label col-1-label">
                    {{else}}
                        <div class="col-4 col-sm-4 label col-2-label">
                    {{/if}}
                    {{else}}
                        {*<!-- DIV inside - colspan = 3 -->*}
                        <div class="col-2 col-sm-2 label col-1-label">
                    {{/if}}

                    {*label*}

                    {*<!-- LABEL -->*}

                    {{if isset($colData.field.customLabel)}}
                        {{$colData.field.customLabel}}
                    {{elseif isset($colData.field.label) && strpos($colData.field.label, '$')}}
                        {capture name="label" assign="label"}{{$colData.field.label}}{/capture}
                        {$label|strip_semicolon}:
                    {{elseif isset($colData.field.label)}}
                        {capture name="label" assign="label"}{sugar_translate label='{{$colData.field.label}}' module='{{$module}}'}{/capture}
                        {$label|strip_semicolon}:
                    {{elseif isset($fields[$colData.field.name])}}
                        {capture name="label" assign="label"}{sugar_translate label='{{$fields[$colData.field.name].vname}}' module='{{$module}}'}{/capture}
                        {$label|strip_semicolon}:
                    {{else}}
                        &nbsp;
                    {{/if}}

                    {{if isset($colData.field.popupHelp) || isset($fields[$colData.field.name]) && isset($fields[$colData.field.name].popupHelp) }}
                        {{if isset($colData.field.popupHelp) }}
                            {capture name="popupText" assign="popupText"}{sugar_translate label="{{$colData.field.popupHelp}}" module="{{$module}}"}{/capture}
                        {{elseif isset($fields[$colData.field.name].popupHelp)}}
                            {capture name="popupText" assign="popupText"}{sugar_translate label="{{$fields[$colData.field.name].popupHelp}}" module='{{$module}}'}{/capture}
                        {{/if}}
                        {sugar_help text=$popupText WIDTH=400}
                    {{/if}}

                    </div>
                    {*<!-- /DIV inside  -->*}

                    {{if $smarty.foreach.colIteration.total > 1 && $colData.colspan != 3}}
                        {*<!-- phone (version 1) -->*}
                        <div class="col-8 col-sm-8 detail-view-field{{if $inline_edit && !empty($colData.field.name) && ($fields[$colData.field.name].inline_edit == 1 || !isset($fields[$colData.field.name].inline_edit))}} inlineEdit{{/if}}{{if isset($fields[$colData.field.name].type) && $fields[$colData.field.name].type == 'phone'}} phone{{/if}}" type="{{$fields[$colData.field.name].type}}" field="{{$fields[$colData.field.name].name}}" {{if $colData.colspan}}colspan='{{$colData.colspan}}'{{/if}}>
                    {{else}}
                        {*<!-- phone (version 2) -->*}
                        <div class="col-10 col-sm-10 detail-view-field{{if $inline_edit && !empty($colData.field.name) && ($fields[$colData.field.name].inline_edit == 1 || !isset($fields[$colData.field.name].inline_edit))}} inlineEdit{{/if}}{{if isset($fields[$colData.field.name].type) && $fields[$colData.field.name].type == 'phone'}} phone{{/if}}" type="{{$fields[$colData.field.name].type}}" field="{{$fields[$colData.field.name].name}}" {{if $colData.colspan}}colspan='{{$colData.colspan}}'{{/if}}>
                    {{/if}}

                    {{if !empty($colData.field.name)}}



                    {*<!-- simple hidden start -->*}
                    {if !$fields.{{$colData.field.name}}.hidden}



                    {{/if}}

                    {{$colData.field.prefix}}


                    {{if ($colData.field.customCode && !$colData.field.customCodeRenderField) || $colData.field.assign}}
                        {counter name="panelFieldCount" print=false}
                        <span id="{{$colData.field.name}}" class="sugar_field">{{sugar_evalcolumn var=$colData.field colData=$colData}}</span>
                    {{elseif $fields[$colData.field.name] && !empty($colData.field.fields) }}
                        {{foreach from=$colData.field.fields item=subField}}
                            {{if $fields[$subField]}}
                                {counter name="panelFieldCount" print=false}
                                {{sugar_field parentFieldArray='fields' tabindex=$tabIndex vardef=$fields[$subField] displayType='DetailView'}}
                            {{else}}
                                {counter name="panelFieldCount" print=false}
                                {{$subField}}
                            {{/if}}
                        {{/foreach}}
                    {{elseif $fields[$colData.field.name]}}
                        {counter name="panelFieldCount" print=false}
                        {{sugar_field parentFieldArray='fields' vardef=$fields[$colData.field.name] displayType='DetailView' displayParams=$colData.field.displayParams typeOverride=$colData.field.type}}
                    {{/if}}

                    {{if !empty($colData.field.customCode) && $colData.field.customCodeRenderField}}
                        {counter name="panelFieldCount" print=false}
                        <span id="{{$colData.field.name}}" class="sugar_field">{{sugar_evalcolumn var=$colData.field colData=$colData}}</span>
                    {{/if}}

                    {{$colData.field.suffix}}

                    {{if !empty($colData.field.name)}}



                    {/if}
                    {*<!-- simple hidden finish -->*}



                    {{/if}}

                        {{if $inline_edit && !empty($colData.field.name) && ($fields[$colData.field.name].inline_edit == 1 || !isset($fields[$colData.field.name].inline_edit))}}
                        <div class="inlineEditIcon col-xs-hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                              </svg>
                        </div>
                        {{/if}}

                    </div>
                    {*<!-- /phone (version 1/2) -->*}

                {{/if}}



                {{counter name="fieldCount" print=false}}

            {*<!-- [/hide!!] -->*}
            {{/if}}

        {{/foreach}}
        </div> <!-- end row -->

        </div>
        {*<!-- /DIV column -->*}


    {{/foreach}}
    {{counter name="columnCount" print=false}}

    {{/foreach}}
</div>
