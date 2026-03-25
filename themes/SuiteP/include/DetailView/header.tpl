{{if $preForm}}
{{$preForm}}
{{/if}}
<script language="javascript">
    {literal}
    SUGAR.util.doWhen(function () {
        return $("#contentTable").length == 0;
    }, SUGAR.themes.actionMenu);
    {/literal}
</script>

<table cellpadding="0" cellspacing="0" border="0" width="100%" id="">
    <tr>
        <td class="buttons" align="left" NOWRAP width="80%">
            <div class="actionsContainer">
                <form action="index.php" method="post" name="DetailView" id="formDetailView">
                    <input type="hidden" name="module" value="{$module}">
                    <input type="hidden" name="record" value="{$fields.id.value}">
                    <input type="hidden" name="return_action">
                    <input type="hidden" name="return_module">
                    <input type="hidden" name="return_id">
                    <input type="hidden" name="module_tab">
                    <input type="hidden" name="isDuplicate" value="false">
                    <input type="hidden" name="offset" value="{$offset}">
                    <input type="hidden" name="action" value="EditView">
                    <input type="hidden" name="sugar_body_only">
                    {{if isset($form.hidden)}}
                    {{foreach from=$form.hidden item=field}}
                    {{$field}}
                    {{/foreach}}
                    {{/if}}
                    {if !$config.enable_action_menu}
                        {{include file="themes/SuiteP/include/DetailView/actions_buttons.tpl"}}
                    {/if}
                </form>

            </div>

        </td>


        <td align="right" width="20%" class="buttons d-none">{$ADMIN_EDIT}
            {{if $panelCount == 0}}
            {{* Render tag for VCR control if SHOW_VCR_CONTROL is true *}}
            {{if $SHOW_VCR_CONTROL and $config.enable_action_menu == false}}
            {$PAGINATION}
            {{/if}}
            {{counter name="panelCount" print=false}}
            {{/if}}
        </td>
        {{* Add $form.links if they are defined *}}
        {{if !empty($form) && isset($form.links)}}
        <td align="right" width="10%">&nbsp;</td>
        <td align="right" width="100%" NOWRAP class="buttons">
            <div class="actionsContainer">
                {{foreach from=$form.links item=link}}
                {{$link}}&nbsp;
                {{/foreach}}
            </div>
        </td>
        {{/if}}
    </tr>
</table>