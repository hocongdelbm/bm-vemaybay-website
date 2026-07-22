<!-- END METADATA GENERATED CONTENT -->

{{if $useTabs}}
<!-- include a closing div if the useTabs variable is set to true -->
</div>
{{/if}}
<div id="email_options" class="box-section">
    <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view table-profile table-profile--email_options">
        <tr>
            <th align="left" scope="row" colspan="4">
                <h4>{$MOD.LBL_MAIL_OPTIONS_TITLE}</h4>
            </th>
        </tr>
        <tr>
            <td scope="row" width="25%">
                {$MOD.LBL_EMAIL}  {if $REQUIRED_EMAIL_ADDRESS}<span class="required" id="mandatory_email">{$APP.LBL_REQUIRED_SYMBOL}</span> {/if}
            </td>
            <td width="75%">
                {$NEW_EMAIL}
            </td>
        </tr>

        <tr id="email_password">
            <td scope="row" width="25%">{$MOD.LBL_PASSWORD_EMAIL}:</td>
            <td scope="row">
                <input style="width: 45%;" type="password" id="password_email" name="password_email" size="30" value="{$PASSWORD_EMAIL}" title="">
            </td>
        </tr>

        <tr id="email_options_link_type" style='display:{$HIDE_FOR_GROUP_AND_PORTAL}'>
            <td scope="row" width="25%">
                {$MOD.LBL_EMAIL_LINK_TYPE}:&nbsp;{sugar_help text=$MOD.LBL_EMAIL_LINK_TYPE_HELP WIDTH=450}
            </td>
            <td>
                <select id="email_link_type" name="email_link_type" tabindex='410'>
                    {$EMAIL_LINK_TYPE}
                </select>
            </td>
        </tr>

        <tr>
            <td scope="row" width="25%">{$MOD.LBL_EDITOR_TYPE}</td>
            <td width="75%">
                <select id="editor_type" name="editor_type" tabindex='410'>
                    {$EDITOR_TYPE}
                </select>
            </td>
        </tr>
    </table>
    {if $ID}
        <button class="btn btn-info mt-2" id="settingsButton" onclick="SUGAR.email2.settings.showSettings(getUserEditViewUserId()); return false;">
            <img src="themes/default/images/icon_email_settings.gif" align="absmiddle" border="0"> 
            {$APP.LBL_EMAIL_SETTINGS}
        </button>
    {/if}
</div>
</div>

<div class="user-tab-content box-tabs">
    {if ($CHANGE_PWD) == '1'}
        <div id="generate_password">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view table-profile table-profile--generate_password">
                <tr>
                    <td width='50%'>
                        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
                            <tr>
                                <th align="left" scope="row" colspan="4">
                                    <h4>{$MOD.LBL_CHANGE_PASSWORD_TITLE}</h4>
                                    {$ERROR_PASSWORD}
                                </th>
                            </tr>
                        </table>
                        <!-- hide field if user is admin that is not editing themselves -->
                        <div id='generate_password_old_password' {if ($IS_ADMIN && !$ADMIN_EDIT_SELF)} style='display:none' {/if}>

                            <table width='100%' cellspacing='0' cellpadding='0' border='0'>
                                <tr>
                                    <td width='35%' scope="row">
                                        {$MOD.LBL_OLD_PASSWORD}
                                    </td>
                                    <td>
                                        <div class="pwd-field">
                                            <input name='old_password' id='old_password' type='password' tabindex='2' onkeyup="password_confirmation();" autocomplete="new-password">
                                            <button type="button" class="pwd-toggle" data-target="old_password" aria-label="{$MOD.LBL_SHOW_PASSWORD}" aria-pressed="false">
                                                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">
                                                    <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                                                    <path class="pwd-toggle-slash" d="M3 3l18 18" stroke-width="2" stroke-linecap="round" fill="none"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
                            <tr>
                                <td width='35%' scope="row" snowrap>
                                    {$MOD.LBL_NEW_PASSWORD}
                                    <span class="required"
                                          id="mandatory_pwd">{if ($REQUIRED_PASSWORD)}{$APP.LBL_REQUIRED_SYMBOL}{/if}</span>
                                </td>
                                <td class='dataField'>
                                    <div class="pwd-field">
                                        <input name='new_password' id="new_password" type='password' tabindex='2' onkeyup="password_confirmation();newrules('{$PWDSETTINGS.minpwdlength}','{$PWDSETTINGS.maxpwdlength}','{$REGEX}');"/>
                                        <button type="button" class="pwd-toggle" data-target="new_password" aria-label="{$MOD.LBL_SHOW_PASSWORD}" aria-pressed="false">
                                            <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">
                                                <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                                                <path class="pwd-toggle-slash" d="M3 3l18 18" stroke-width="2" stroke-linecap="round" fill="none"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            {if $PWDSETTINGS.minpwdlength || $PWDSETTINGS.maxpwdlength || $PWDSETTINGS.onelower || $PWDSETTINGS.oneupper || $PWDSETTINGS.onenumber || $PWDSETTINGS.onespecial}
                            <tr>
                                <td></td>
                                <td class="dataField">
                                    <div id="password_requirement_box">
                                        <div class="pwd-strength">
                                            <div class="pwd-strength-track">
                                                <div class="pwd-strength-fill" id="pwd_strength_fill"></div>
                                            </div>
                                            <span class="pwd-strength-label" id="pwd_strength_label"></span>
                                        </div>
                                        <ul>
                                            {if $PWDSETTINGS.minpwdlength || $PWDSETTINGS.maxpwdlength}<li><span id="lengths" class="pending"></span> {$PWD_LENGTH_LABEL}</li>{/if}
                                            {if $PWDSETTINGS.onelower}<li><span id="1lowcase" class="pending"></span> {$MOD.ERR_PASSWORD_ONELOWER}</li>{/if}
                                            {if $PWDSETTINGS.oneupper}<li><span id="1upcase" class="pending"></span> {$MOD.ERR_PASSWORD_ONEUPPER}</li>{/if}
                                            {if $PWDSETTINGS.onenumber}<li><span id="1number" class="pending"></span> {$MOD.ERR_PASSWORD_ONENUMBER}</li>{/if}
                                            {if $PWDSETTINGS.onespecial}<li><span id="1special" class="pending"></span> {$MOD.ERR_PASSWORD_SPECCHARS}</li>{/if}
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            {/if}
                            <tr>
                                <td scope="row" width='35%'>
                                    {$MOD.LBL_CONFIRM_PASSWORD}
                                </td>
                                <td class='dataField'>
                                    <div class="pwd-field">
                                        <input name='confirm_new_password' id='confirm_pwd' type='password' tabindex='2' onkeyup="password_confirmation();">
                                        <button type="button" class="pwd-toggle" data-target="confirm_pwd" aria-label="{$MOD.LBL_SHOW_PASSWORD}" aria-pressed="false">
                                            <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">
                                                <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                                                <path class="pwd-toggle-slash" d="M3 3l18 18" stroke-width="2" stroke-linecap="round" fill="none"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div id="comfirm_pwd_match" class="error" style="display: none;">{$MOD.ERR_PASSWORD_MISMATCH}</div>
                                </td>
                            </tr>
                            <tr>
                                <td class='dataLabel'></td>
                                <td class='dataField'></td>
                                </td>
                        </table>

                        <table width='17%' cellspacing='0' cellpadding='1' border='0'>
                            <tr>
                                <td width='50%'>
                                    <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey='{$APP.LBL_SAVE_BUTTON_KEY}'
                                           class='button' id='save_new_pwd_button' LANGUAGE=javascript
                                           onclick='if (set_password(this.form)) window.close(); else return false;'
                                           type='submit' name='button' style='display:none;'
                                           value='{$APP.LBL_SAVE_BUTTON_LABEL}'>
                                </td>
                                <td width='50%'>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width='50%'></td>
                </tr>
            </table>
        </div>
    {else}
        <div id="generate_password">
            <input name='old_password' id='old_password' type='hidden'>
            <input name='new_password' id="new_password" type='hidden'>
            <input name='confirm_new_password' id='confirm_pwd' type='hidden'>
        </div>
    {/if}
</div>

{if $SHOW_THEMES}
    <div class="user-tab-content box-tabs">
        <div id="themepicker" style="display:{$HIDE_FOR_GROUP_AND_PORTAL}">
            <table class="edit view table-profile table-profile--themepicker" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                <tr>
                    <td scope="row" colspan="4"><h4>{$MOD.LBL_THEME}</h4></td>
                </tr>
                <tr>
                    <td width="25%">
                        <select name="user_theme" tabindex='366' size="20" id="user_theme_picker" style="width: 100%">
                            {$THEMES}
                        </select>
                    </td>
                    <td width="33%">
                        <img id="themePreview" src="{sugar_getimagepath file='themePreview.png'}" border="1"/>
                    </td>
                    <td width="25%">&nbsp;</td>
                    <td width="33%">&nbsp;</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
{/if}

<div class="user-tab-content box-tabs">
    <div id="settings" style="display:{$HIDE_FOR_GROUP_AND_PORTAL}">
        <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view table-profile table-profile--settings">
            <tr>
                <th width="100%" align="left" scope="row" colspan="4">
                    <h4>
                       {$MOD.LBL_USER_SETTINGS}
                    </h4>
                </th>
            </tr>
            <tr>
                <td scope="row" valign="top">
                    {$MOD.LBL_EXPORT_DELIMITER}:&nbsp;{sugar_help text=$MOD.LBL_EXPORT_DELIMITER_DESC }
                </td>
                <td>
                    <input type="text" tabindex='12' name="export_delimiter" value="{$EXPORT_DELIMITER}" size="5">
                </td>
                <td scope="row" width="25%">
                   {$MOD.LBL_RECEIVE_NOTIFICATIONS}:
                    &nbsp;{sugar_help text=$MOD.LBL_RECEIVE_NOTIFICATIONS_TEXT}
                </td>
                <td width="33%">
                    <input type='hidden' value='0' name='receive_notifications'>
                    <input name='receive_notifications' class="checkbox" tabindex='12' type="checkbox" value="1" {$RECEIVE_NOTIFICATIONS}>
                </td>
            </tr>

            <tr>
                <td scope="row" valign="top">
                    {$MOD.LBL_EXPORT_CHARSET}:&nbsp;{sugar_help text=$MOD.LBL_EXPORT_CHARSET_DESC }</td>
                <td>
                    <select tabindex='12' name="default_export_charset">{$EXPORT_CHARSET}</select>
                </td>
                <td scope="row" valign="top">
                    {$MOD.LBL_REMINDER}:&nbsp;{sugar_help text=$MOD.LBL_REMINDER_TEXT }
                </td>
                <td valign="top" nowrap>
                    {include file="modules/Reminders/tpls/remindersDefaults.tpl"}
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm" onClick="Alerts.prototype.enable()">
                        {$MOD.LBL_ENABLE_NOTIFICATIONS}
                    </button>
                </td>
            </tr>
            <tr>
                <td scope="row" valign="top">
                    {$MOD.LBL_USE_REAL_NAMES}:&nbsp;{sugar_help text=$MOD.LBL_USE_REAL_NAMES_DESC }</td>
                <td>
                    <input tabindex='12' type="checkbox" name="use_real_names" {$USE_REAL_NAMES}>
                </td>
                <td scope="row" valign="top">
                    {$MOD.LBL_MAILMERGE}:&nbsp;{sugar_help text=$MOD.LBL_MAILMERGE_TEXT }
                </td>
                <td valign="top" nowrap>
                    <input tabindex='12' name='mailmerge_on' class="checkbox" type="checkbox" {$MAILMERGE_ON}>
                </td>
            </tr>
            <!--{if !empty($EXTERNAL_AUTH_CLASS) && !empty($IS_ADMIN)}-->
            <tr>
                {capture name=SMARTY_LBL_EXTERNAL_AUTH_ONLY}&nbsp;{$MOD.LBL_EXTERNAL_AUTH_ONLY} {$EXTERNAL_AUTH_CLASS_1}{/capture}
                <td scope="row" nowrap>
                    {$EXTERNAL_AUTH_CLASS} {$MOD.LBL_ONLY}:&nbsp;{sugar_help text=$smarty.capture.SMARTY_LBL_EXTERNAL_AUTH_ONLY}</td>
                <td>
                    <input type='hidden' value='0' name='external_auth_only'>
                    <input type='checkbox' value='1' name='external_auth_only' {$EXTERNAL_AUTH_ONLY_CHECKED}>
                </td>
                <td></td>
                <td></td>
            </tr>
            <!--{/if}-->
        </table>
    </div>
    <div id="locale" style="display:{$HIDE_FOR_GROUP_AND_PORTAL}">
        <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view table-profile table-profile--locale">
            <tr>
                <th width="100%" align="left" scope="row" colspan="4">
                    <h4>
                        {$MOD.LBL_USER_LOCALE}
                    </h4>
                </th>
            </tr>
            <tr>
                <td width="25%" scope="row">
                    {$MOD.LBL_DATE_FORMAT}:&nbsp;{sugar_help text=$MOD.LBL_DATE_FORMAT_TEXT }</td>
                <td width="33%">
                    <select tabindex='14' name='dateformat'>{$DATEOPTIONS}</select>
                </td>
                <!-- END: prompttz -->
                <!-- BEGIN: currency -->
                <td width="25%" scope="row">
                    {$MOD.LBL_CURRENCY}:&nbsp;{sugar_help text=$MOD.LBL_CURRENCY_TEXT }</td>
                <td>
                    <select tabindex='14' id='currency_select' name='currency'
                            onchange='setSymbolValue(this.options[this.selectedIndex].value);setSigDigits();'>{$CURRENCY}</select>
                    <input type="hidden" id="symbol" value="">
                </td>
                <!-- END: currency -->
            </tr>
            <tr>
                <td scope="row">
                    {$MOD.LBL_TIME_FORMAT}:&nbsp;{sugar_help text=$MOD.LBL_TIME_FORMAT_TEXT }</td>
                <td>
                    <select tabindex='14' name='timeformat'>{$TIMEOPTIONS}</select>
                </td>
                <!-- BEGIN: currency -->
                <td width="25%" scope="row">
                    {$MOD.LBL_CURRENCY_SIG_DIGITS}:
                </td>
                <td>
                    <select id='sigDigits' onchange='setSigDigits(this.value);' name='default_currency_significant_digits'>{$sigDigits}</select>
                </td>
                <!-- END: currency -->
            </tr>
            <tr>
                <td scope="row">
                    {$MOD.LBL_TIMEZONE}:&nbsp;{sugar_help text=$MOD.LBL_TIMEZONE_TEXT }</td>
                <td>
                    <select tabindex='14' name='timezone'>{html_options options=$TIMEZONEOPTIONS selected=$TIMEZONE_CURRENT}</select>
                </td>
                <!-- BEGIN: currency -->
                <td width="25%" scope="row">
                        <i>{$MOD.LBL_LOCALE_EXAMPLE_NAME_FORMAT}</i>:
                </td>
                <td>
                    <input type="text" disabled id="sigDigitsExample" name="sigDigitsExample">
                </td>
                <!-- END: currency -->
            </tr>
            <tr>
                {if ($IS_ADMIN)}
                    <td scope="row">
                        {$MOD.LBL_PROMPT_TIMEZONE}:&nbsp;{sugar_help text=$MOD.LBL_PROMPT_TIMEZONE_TEXT }
                    </td>
                    <td>
                        <input type="checkbox" tabindex='14' class="checkbox" name="ut" value="0" {$PROMPTTZ}>
                    </td>
                {else}
                    <td scope="row">
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>
                {/if}
                <td width="25%" scope="row">
                    {$MOD.LBL_NUMBER_GROUPING_SEP}:&nbsp;{sugar_help text=$MOD.LBL_NUMBER_GROUPING_SEP_TEXT }</td>
                <td>
                    <input tabindex='14' name='num_grp_sep' id='default_number_grouping_seperator'
                            type='text' maxlength='1' size='1' value='{$NUM_GRP_SEP}'
                            onkeydown='setSigDigits();' onkeyup='setSigDigits();'>
                </td>
            </tr>
            {capture name=SMARTY_LOCALE_NAME_FORMAT_DESC}&nbsp;{$MOD.LBL_LOCALE_NAME_FORMAT_DESC}{/capture}
            <tr>
                <td scope="row" valign="top">{$MOD.LBL_LOCALE_DEFAULT_NAME_FORMAT}
                    :&nbsp;{sugar_help text=$smarty.capture.SMARTY_LOCALE_NAME_FORMAT_DESC }</td>
                <td valign="top">
                    <select tabindex='14' id="default_locale_name_format" name="default_locale_name_format"
                                  selected="{$default_locale_name_format}">{$NAMEOPTIONS}</select>
                </td>
                <td width="25%" scope="row">
                    {$MOD.LBL_DECIMAL_SEP}:&nbsp;{sugar_help text=$MOD.LBL_DECIMAL_SEP_TEXT }</td>
                <td>
                    <input tabindex='14' name='dec_sep' id='default_decimal_seperator'
                            type='text' maxlength='1' size='1' value='{$DEC_SEP}'
                            onkeydown='setSigDigits();' onkeyup='setSigDigits();'>
                </td>
            </tr>
        </table>
    </div>
    <div id="calendar_options" style="display:{$HIDE_FOR_GROUP_AND_PORTAL}">
        <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view table-profile table-profile--calendar_options">
            <tr>
                <th align="left" scope="row" colspan="4"><h4>{$MOD.LBL_CALENDAR_OPTIONS}</h4></th>
            </tr>
            <tr>
                <td width="25%" scope="row">
                    {$MOD.LBL_PUBLISH_KEY}:{sugar_help text=$MOD.LBL_CHOOSE_A_KEY}</td>
                <td>
                    <input id='calendar_publish_key' name='calendar_publish_key' tabindex='17' size='25' maxlength='36' type="text" value="{$CALENDAR_PUBLISH_KEY}">
                </td>
            </tr>
            <tr>
                <td width="15%" scope="row">
                    {$MOD.LBL_YOUR_PUBLISH_URL|strip_semicolon}:
                </td>
                <td colspan=2>
                    <span class="calendar_publish_ok">{$CALENDAR_PUBLISH_URL}</span>
                    <span class="calendar_publish_none" style="display: none">{$MOD.LBL_NO_KEY}</span>
                </td>
            </tr>
            <tr>
                <td width="25%" scope="row">
                    {$MOD.LBL_SEARCH_URL|strip_semicolon}:
                </td>
                <td colspan=2><span class="calendar_publish_ok">{$CALENDAR_SEARCH_URL}</span><span
                            class="calendar_publish_none" style="display: none">{$MOD.LBL_NO_KEY}</span></td>
            </tr>
            <tr>
                <td width="15%" scope="row">
                    {$MOD.LBL_ICAL_PUB_URL|strip_semicolon}: {sugar_help text=$MOD.LBL_ICAL_PUB_URL_HELP}
                </td>
                <td colspan=2><span class="calendar_publish_ok">{$CALENDAR_ICAL_URL}</span><span
                            class="calendar_publish_none" style="display: none">{$MOD.LBL_NO_KEY}</span></td>
            </tr>
            <tr>
                <td width="25%" scope="row">
                    {$MOD.LBL_FDOW}:&nbsp;{sugar_help text=$MOD.LBL_FDOW_TEXT}</td>
                <td>
                    <select tabindex='14' name='fdow'>{html_options options=$FDOWOPTIONS selected=$FDOWCURRENT}</select>
                </td>
            </tr>
        </table>
    </div>
    <div id="google_options" style="display:{$HIDE_IF_GAUTH_UNCONFIGURED}">
        <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view table-profile table-profile--google_options">
            <tr>
                <th align="left" scope="row" colspan="4"><h4>{$MOD.LBL_GOOGLE_API_SETTINGS}</h4></th>
            </tr>
            <tr>
                <td width="25%" scope="row">
                    {$MOD.LBL_GOOGLE_API_TOKEN}:&nbsp;{sugar_help text=$MOD.LBL_GOOGLE_API_TOKEN_HELP}
                </td>
                    <td width="20%">
                    Current API Token is: <span style="color:{$GOOGLE_API_TOKEN_COLOR}">{$GOOGLE_API_TOKEN}</span> &nbsp;&nbsp;<input style="display:{$GOOGLE_API_TOKEN_ENABLE_NEW}" class="btn btn-primary btn-sm" id="google_gettoken" type="button" value="{$GOOGLE_API_TOKEN_BTN}" onclick="window.open('{$GOOGLE_API_TOKEN_NEW_URL}', '_self')" />
                </td>
                <td width="63%">
                    &nbsp;
                </td>
            </tr>
            <tr>
                <td width="25%" scope="row">
                  {$MOD.LBL_GSYNC_CAL}:
                </td>
                <td>
                  <input tabindex='12' name='gsync_cal' class="checkbox" type="checkbox" {$GSYNC_CAL}>
                </td>
            </tr>
        </table>
    </div>
</div>

{if $ID}
<div class="user-tab-content box-tabs">
    <div id="eapm_area" style='display:{$HIDE_FOR_GROUP_AND_PORTAL};'>
        <div style="text-align:center; width: 100%">{sugar_getimage name="loading"}</div>
    </div>
</div>
{/if}

<div class="user-tab-content box-tabs">
    <div id="subthemes" style="display:{$HIDE_FOR_GROUP_AND_PORTAL}">
        <table class="edit view table-profile table-profile--subthemes" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <th align="left" scope="row" colspan="4"><h4>{$MOD.LBL_LAYOUT_OPTIONS}</h4></th>
                </tr>
                {if $SUBTHEMES}
                    <tr>
                        <td colspan="4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="label">{$MOD.LBL_SUBTHEME}:</span>
                                        </div>
                                        <div class="col-md-8">
                                            {html_options name=subtheme options=$SUBTHEMES selected=$SUBTHEME}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row" id="use_group_tabs_row" style="display: {$DISPLAY_GROUP_TAB};">
                                        <div class="col-md-4">
                                            <span class="label">{$MOD.LBL_USE_GROUP_TABS}:</span>{sugar_help text=$MOD.LBL_NAVIGATION_PARADIGM_DESCRIPTION }
                                        </div>
                                        <div class="col-md-8">
                                            <input name="use_group_tabs" type="hidden" value="m">
                                            <input id="use_group_tabs" type="checkbox" name="use_group_tabs" {$USE_GROUP_TABS} tabindex='12' value="gm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    <tr>
                {/if}
                <tr>
                    <td colspan="4">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td scope="row" align="left">{$TAB_CHOOSER}</td>
                                <td width="90%" valign="top"><br>&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="25%" scope="row"><span class="label">{$MOD.LBL_SORT_MODULES}:</span>&nbsp;{sugar_help text=$MOD.LBL_SORT_MODULES_DESCRIPTION }</td>
                    <td width="75%" colspan="3">
                        <input type="checkbox" name="sort_modules_by_name" {$SORT_MODULES_BY_NAME} tabindex='13'>
                   </td>
                </tr>
                <tr>
                    <td width="25%" scope="row"><span class="label">{$MOD.LBL_SUBPANEL_TABS}
                            :</span>&nbsp;{sugar_help text=$MOD.LBL_SUBPANEL_TABS_DESCRIPTION }</td>
                    <td width="75%" colspan="3"><input type="checkbox" name="user_subpanel_tabs" {$SUBPANEL_TABS} tabindex='13'></td>
                </tr>
                <tr>
                    <td width="25%" scope="row"><span class="label">{$MOD.LBL_COUNT_COLLAPSED_SUBPANELS}
                            :</span>&nbsp;{sugar_help text=$MOD.LBL_COUNT_COLLAPSED_SUBPANELS_DESCRIPTION }</td>
                    <td width="75%" colspan="3"><input type="checkbox" name="user_count_collapsed_subpanels" {$COUNT_COLLAPSED_SUBPANELS} tabindex='13'></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>

<script type="text/javascript">
  var mail_smtpport = '{$MAIL_SMTPPORT}';
  var mail_smtpssl  = '{$MAIL_SMTPSSL}';

  {literal}
  EmailMan = {};

  function Admin_check() {
    if (('{/literal}{$IS_FOCUS_ADMIN}{literal}') && document.getElementById('is_admin').value == '0') {
      r = confirm('{/literal}{$MOD.LBL_CONFIRM_REGULAR_USER}{literal}');
      return r;
    }
    else
      return true;
  }


  $(document).ready(function () {
    var checkKey = function (key) {
      if (key != '') {
        $(".calendar_publish_ok").css('display', 'inline');
        $(".calendar_publish_none").css('display', 'none');
        $('#cal_pub_key_span').html(key);
        $('#ical_pub_key_span').html(key);
        $('#search_pub_key_span').html(key);
      } else {
        $(".calendar_publish_ok").css('display', 'none');
        $(".calendar_publish_none").css('display', 'inline');
      }
    };
    $('#calendar_publish_key').keyup(function () {
      checkKey($(this).val());
    });
    $('#calendar_publish_key').change(function () {
      checkKey($(this).val());
    });
    checkKey($('#calendar_publish_key').val());
  });
  {/literal}
</script>
{$JAVASCRIPT}
{literal}
<script type="text/javascript" language="Javascript">
    {/literal}
    {$getNameJs}
    {$getNumberJs}
    currencies = {$currencySymbolJSON};
    themeGroupList = {$themeGroupListJSON};

    onUserEditView();


</script>

</form>

<div id="testOutboundDialog" class="yui-hidden">
    <div id="testOutbound">
        <form>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
                <tr>
                    <td scope="row">
                        {$APP.LBL_EMAIL_SETTINGS_FROM_TO_EMAIL_ADDR}
                        <span class="required">
						    {$APP.LBL_REQUIRED_SYMBOL}
					    </span>
                    </td>
                    <td>
                        <input type="text" id="outboundtest_from_address" name="outboundtest_from_address" size="35" maxlength="64" value="{$TEST_EMAIL_ADDRESS}">
                    </td>
                </tr>
                <tr>
                    <td scope="row" colspan="2">
                        <div class="d-flex gap-2 align-items-center">
                            <input type="button" class="btn btn-primary" value="   {$APP.LBL_EMAIL_SEND}   "
                                onclick="javascript:sendTestEmail();">
                            <input type="button" class="btn btn-danger" value="   {$APP.LBL_CANCEL_BUTTON_LABEL}   "
                                onclick="javascript:EmailMan.testOutboundDialog.hide();">
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
    <tr>
        <td>
            {sugar_action_menu id="userEditActions" class="clickMenu fancymenu" buttons=$ACTION_BUTTON_FOOTER flat=true}
        </td>
        <td align="right" nowrap>
            <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span> {$APP.NTC_REQUIRED}
        </td>
    </tr>
</table>

        {if $showEmailSettingsPopup}
        <script>
            {literal}
            $(function(){
                SUGAR.email2.settings.showSettings();
            });
            {/literal}
        </script>
        {/if}
