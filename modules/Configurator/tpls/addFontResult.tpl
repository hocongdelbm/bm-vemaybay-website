<p>
{$MODULE_TITLE}
</p>
<form name="addFontResult" method="POST" action="index.php" id="addFontResult">
<input type="hidden" name="module" value="Configurator">
<input type="hidden" name="action" value="FontManager">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td>
            <input title="{$MOD.LBL_FONTMANAGER_TITLE}" class="btn btn-primary"  type="submit" name="back" value="  {$MOD.LBL_FONTMANAGER_BUTTON}  " >
        </td>
    </tr>
</table>
<br>
<div>{if isset($error)}<span class='error'><b>{$MOD.LBL_STATUS_FONT_ERROR}</b></span>{else}<b>{$MOD.LBL_STATUS_FONT_SUCCESS}</b>{/if}</div>
<span class='error'><pre>{$error}</pre></span>
<pre>{$info}</pre>
</form>