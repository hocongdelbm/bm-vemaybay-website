<script language="javascript">
    var _form_id = '{$form_id}';
    {literal}
    SUGAR.util.doWhen(function(){
        _form_id = (_form_id == '') ? 'EditView' : _form_id;
        return document.getElementById(_form_id) != null;
    }, SUGAR.themes.actionMenu);
    {/literal}
</script>

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="dcQuickEdit">
    <tr>
        <td class="buttons">
            {assign var='place' value="_FOOTER"}
            <!-- to be used for id for buttons with custom code in def files-->
            {{if isset($form.hidden)}}
            {{foreach from=$form.hidden item=field}}
            {{$field}}
            {{/foreach}}
            {{/if}}
            {{if empty($form.button_location) || $form.button_location == 'bottom'}}
            {{if !empty($form) && !empty($form.buttons)}}
            {{foreach from=$form.buttons key=val item=button}}
            {{sugar_button module="$module" id="$button" form_id="$form_id" view="$view" appendTo="footer_buttons" location="FOOTER"}}
            {{/foreach}}
            {{else}}
            {{sugar_button module="$module" id="SAVE" view="$view" form_id="$form_id" location="FOOTER" appendTo="footer_buttons"}}
            {{sugar_button module="$module" id="CANCEL" view="$view" form_id="$form_id" location="FOOTER" appendTo="footer_buttons"}}
            {{/if}}
            {{if empty($form.hideAudit) || !$form.hideAudit}}
            {{sugar_button module="$module" id="Audit" view="$view" form_id="$form_id" appendTo="footer_buttons"}}
            {{/if}}
            {{/if}}
            {{sugar_action_menu buttons=$footer_buttons class="fancymenu" flat=true}}
        </td>
        <td align='right' class="edit-view-pagination">{{$ADMIN_EDIT}}
            {{if $panelCount == 0}}
            {{* Render tag for VCR control if SHOW_VCR_CONTROL is true *}}
            {{if $SHOW_VCR_CONTROL}}
            {$PAGINATION}
            {{/if}}
            {{/if}}
        </td>
    </tr>
</table>
</form>
{{if $externalJSFile}}
{sugar_include include=$externalJSFile}
{{/if}}

{$set_focus_block}

{{if isset($scriptBlocks)}}
<!-- Begin Meta-Data Javascript -->
{{$scriptBlocks}}
<!-- End Meta-Data Javascript -->
{{/if}}
<script>SUGAR.util.doWhen("document.getElementById('EditView') != null",
        function(){ldelim}SUGAR.util.buildAccessKeyLabels();{rdelim});
</script>
