<table id="email_options" class="box-section">
    <div width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
        <div>
            <th align="left" scope="row" colspan="4">
                <h4>{$MOD.LBL_MAIL_OPTIONS_TITLE}</h4>
            </th>
        </div>
        <div>
            <div scope="row" >
                {$MOD.LBL_EMAIL}:  {if $REQUIRED_EMAIL_ADDRESS}<span class="required" id="mandatory_email">{$APP.LBL_REQUIRED_SYMBOL}</span> {/if}
            </div>
            <div>
                {$NEW_EMAIL}
            </div>
        </div>
        <div id="email_options_link_type" style='display:{$HIDE_FOR_GROUP_AND_PORTAL}'>
            <div scope="row" >
                {$MOD.LBL_EMAIL_LINK_TYPE}:&nbsp;{sugar_help text=$MOD.LBL_EMAIL_LINK_TYPE_HELP WIDTH=450}
            </div>
            <div>
                <select id="email_link_type" name="email_link_type" tabindex='410'>
                    {$EMAIL_LINK_TYPE}
                </select>
            </div>
        </div>
    </div>
</table>