<table cellpadding="4" class="view">
    <tr>
        <th colspan="4">
            <h4>{$app_strings.LBL_EMAIL_FOLDERS_TITLE}</h4>
        </th>
    </tr>
    <tr>
        <td valign="top" scope="row">
            <div class="div-title">
                {$app_strings.LBL_EMAIL_SETTINGS_USER_FOLDERS}:
                <div id="rollover">
                    <a href="#"
                       class="rollover">{sugar_getimage alt=$mod_strings.LBL_HELP name="helpInline" ext=".gif" other_attributes='border="0" '}
                        <span>{$app_strings.LBL_EMAIL_MULTISELECT}</span></a>
                </div>
            </div>
            <div>
                <select class="box-select" multiple size="8" name="userFolders[]" id="userFolders" onchange="SUGAR.email2.folders.updateSubscriptions();"></select>
            </div>
        </td>

    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align="right">
            <input type="button" class="btn btn-primary" value="   {$app_strings.LBL_EMAIL_DONE_BUTTON_LABEL}   " onclick="javascript:SUGAR.email2.settings.saveOptionsGeneral(true);">
        </td>
    </tr>
</table>
