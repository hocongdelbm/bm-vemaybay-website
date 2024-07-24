
<script type="text/javascript">
js_iso4217 = {$JS_ISO4217};
</script>
<script type="text/javascript" src="{sugar_getjspath file='modules/Currencies/EditView.js'}"></script>

<div class="box-section">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="table-edit table-config tabel-config__currencies">
  <tr>
      <td>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="15%" scope="row" nowrap><span>{$MOD.LBL_LIST_NAME}: <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></span></td>
            <td width="35%"><span><input name='name' tabindex='1' size='30' maxlength='50' type="text" value="{$NAME}"></span></td>
            <td width="15%" scope="row" nowrap><span>{$MOD.LBL_LIST_ISO4217}:&nbsp;{sugar_help text=$MOD.LBL_LIST_ISO4217_HELP}</span></td>
            <td width="35%"><span><input name='iso4217' tabindex='1' size='3' maxlength='3' type="text" value="{$ISO4217}" onKeyUp='isoUpdate(this);'></span></td>
          </tr>
          <tr></tr>
          <tr>
            <td width="15%" scope="row" nowrap><span> {$MOD.LBL_LIST_RATE}: <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></span></td>
            <td width="35%"><span><input name='conversion_rate' tabindex='1' size='30' maxlength='50' type="text" value="{$CONVERSION_RATE}">
            {sugar_help text=$MOD.LBL_LIST_RATE_HELP }
            </span></td>
            <td width="15%" scope="row" nowrap><span>{$MOD.LBL_LIST_SYMBOL}: <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></span></td>
            <td width="35%"><span><input name='symbol' tabindex='1' size='3' maxlength='50' type="text" value="{$SYMBOL}"></span></td>
          </tr>
          <tr></tr>
          <tr>
            <td scope="row"><span>{$MOD.LBL_LIST_STATUS}:</span></td>
            <td><span><select name='status' tabindex='1'>{$STATUS_OPTIONS}</select> <em>{$MOD.NTC_STATUS}</em></span></td>
          </tr>
        </table>
      </td>
  </tr>
</table>
</div>

<input type='hidden' name='record' value='{$ID}'>
</form>
{$JAVASCRIPT}
