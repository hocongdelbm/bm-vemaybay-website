{if strlen({{sugarvar key='value' string=true}}) <= 0}
{assign var="value" value={{sugarvar key='default_value' string=true}} }
{else}
{assign var="value" value={{sugarvar key='value' string=true}} }
{/if}
<form name="vcard" action="index.php" style="display: inline;">
<span id='{{sugarvar key='name'}}'>{{sugarvar key='value'}}</span>
&nbsp;&nbsp;
<input type="hidden" name="action" value="vCard" />
<input type="hidden" name="record" value="{$fields.id.value}" />
<input type="hidden" name="module" value="{$module}" />
<input type="hidden" name="to_pdf" value="true" />
<span class="id-ff">
<button type="submit" name="vCardButton" id="btn_vCardButton" class="vcard" value="{$APP.LBL_VCARD}" title="{$APP.LBL_VCARD}" class="btn btn-primary"><span class="suitepicon suitepicon-action-vcard"></span></button>
</span>
</form>
{{if !empty($displayParams.enableConnectors)}}
{if !empty($value)}
{{sugarvar_connector view='DetailView'}}
{/if}
{{/if}}