<div id="settings_suitep" >
    <div class="row detail-view-row">
        <h4 class="tabs-title">{$MOD.LBL_USER_SETTINGS}</h4>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    <!-- LABEL -->
                    {$MOD.LBL_RECEIVE_NOTIFICATIONS}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field">
                    <!-- simple hidden start -->
                    <input name='receive_notifications' class="checkbox" tabindex='12' type="checkbox" value="12" {$RECEIVE_NOTIFICATIONS} disabled>
                    <!-- simple hidden finish -->
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_MAILMERGE|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                    <input tabindex='3' name='mailmerge_on' disabled class="checkbox" type="checkbox" {$MAILMERGE_ON} disabled>
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_SETTINGS_URL|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    {$SETTINGS_URL}
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_USE_REAL_NAMES|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                    <input tabindex='3' name='use_real_names' disabled class="checkbox" type="checkbox" {$USE_REAL_NAMES} disabled>
                </div>
            </div>
        </div>
        
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_EXPORT_DELIMITER|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                    {$EXPORT_DELIMITER}
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_EXPORT_CHARSET|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                    {$EXPORT_CHARSET_DISPLAY}
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_REMINDER|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    {include file="modules/Reminders/tpls/remindersDefaults.tpl"}
                </div>
            </div>
        </div>
        {if $DISPLAY_EXTERNAL_AUTH}
            <div class="col-xs-12 col-sm-6 detail-view-row-item">
                <div class="row">
                    <div class="col-xs-12 col-sm-4 label col-1-label">
                        {$EXTERNAL_AUTH_CLASS|strip_semicolon}
                    </div>
                    <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                        <input id="external_auth_only" name="external_auth_only" type="checkbox" class="checkbox" {$EXTERNAL_AUTH_ONLY_CHECKED} disabled>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>

<div id='locale_suitep'>
    <div class="row detail-view-row">
        <h4 class="tabs-title">{$MOD.LBL_USER_LOCALE}</h4>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_DATE_FORMAT|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    {$DATEFORMAT}
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_TIME_FORMAT|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                    {$TIMEFORMAT}
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_TIME_FORMAT|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    {$TIMEZONE}
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_CURRENCY|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                    {$CURRENCY_DISPLAY}
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_CURRENCY_SIG_DIGITS|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    {$CURRENCY_SIG_DIGITS}
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_NUMBER_GROUPING_SEP|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                    {$NUM_GRP_SEP}
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_NUMBER_GROUPING_SEP|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    {$NUM_GRP_SEP}
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_DECIMAL_SEP|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                    {$MOD.LBL_DECIMAL_SEP_TEXT}
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_LOCALE_DEFAULT_NAME_FORMAT|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    {$NAME_FORMAT}
                </div>
            </div>
        </div>
    </div>
</div>

<div id='calendar_options_suitep'>
    <div class="row detail-view-row">
        <h4 class="tabs-title">{$MOD.LBL_CALENDAR_OPTIONS}</h4>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_PUBLISH_KEY|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    {$CALENDAR_PUBLISH_KEY}
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_FDOW|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                    {$FDOWDISPLAY}
                </div>
            </div>
        </div>
      
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_SEARCH_URL|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field wrap-text">
                    {if $CALENDAR_PUBLISH_KEY}{$CALENDAR_SEARCH_URL}{else}{$MOD.LBL_NO_KEY}{/if}
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_ICAL_PUB_URL|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field wrap-text" type="name" field="name">
                    {if $CALENDAR_PUBLISH_KEY}{$CALENDAR_ICAL_URL}{else}{$MOD.LBL_NO_KEY}{/if}
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_YOUR_PUBLISH_URL|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field wrap-text" type="name" field="name">
                    {if $CALENDAR_PUBLISH_KEY}{$CALENDAR_PUBLISH_URL}{else}{$MOD.LBL_NO_KEY}{/if}
                </div>
            </div>
        </div>
    </div>
</div>

<div id='google_options_suitep' style="display:{$HIDE_IF_GAUTH_UNCONFIGURED}">
    <div class="row detail-view-row">
        <h4 class="tabs-title">{$MOD.LBL_GOOGLE_API_SETTINGS}</h4>
    </div>
    <div class="row detail-view-row mb-3">
        <div class="col-xs-12 col-sm-6 col-md-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_GOOGLE_API_TOKEN}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    Current API Token is: <span style="color:{$GOOGLE_API_TOKEN_COLOR}">{$GOOGLE_API_TOKEN}</span>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_GSYNC_CAL}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    <input class="checkbox" type="checkbox" disabled {$GSYNC_CAL}>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="layout_suitep">
    <div class="row detail-view-row">
        <h4 class="tabs-title">{$MOD.LBL_LAYOUT_OPTIONS}</h4>
    </div>
    <div class="row detail-view-row">
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_USE_GROUP_TABS}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field ">
                    <input name="use_group_tabs" type="hidden" value="m"><input id="use_group_tabs" type="checkbox" name="use_group_tabs" {$USE_GROUP_TABS} tabindex='12' value="gm" disabled>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 detail-view-row-item">
            <div class="row">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_MAILMERGE|strip_semicolon}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field " type="name" field="name">
                    <input tabindex='3' name='mailmerge_on' disabled class="checkbox" type="checkbox" {$MAILMERGE_ON} disabled>
                </div>
            </div>
        </div>
    </div>
</div>
