{$PAGE_TITLE}

<form name="gcAuthentication"
      enctype='multipart/form-data'
      method="post"
      action="index.php?module=Administration&action=GoogleCalendarSettings&do=save"
      onSubmit="return (add_checks(document.gcAuthentication) && check_form('gcAuthentication'));"
>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
      <tr>
          <td>
              <input title="{$APP.LBL_SAVE_BUTTON_TITLE}"
                     accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"
                     class="btn btn-primary"
                     type="submit"
                     name="save"
                     onclick="return check_form('ConfigureSettings');"
                     value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " />
              <input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}"
                     onclick="document.location.href='index.php?module=Administration&action=index'"
                     class="btn btn-danger"
                     type="button"
                     name="cancel"
                     value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " />
          </td>
      </tr>
    </table>

    <div class="box-section mt-3">
    <table border="0" cellspacing="1" cellpadding="0" class="table-edit table-googlecalendar__settings">
        <tr>
            <th align="left" scope="row" colspan="4"><h4 class="sub-admin__title">{$MOD.LBL_GOOGLE_CALENDAR_SETTINGS_TITLE}</h4></th>
        </tr>
        <tr>
            <td width="25%" scope="row" valign='middle'>
                {$MOD.LBL_GOOGLE_CALENDAR_SETTINGS_JSON}&nbsp{sugar_help text=$MOD.LBL_GOOGLE_CALENDAR_SETTINGS_JSON_HELP}
            </td>
            <td id="google_json" width="75%" align="left"  valign='middle' colspan='3'>
                <script type='text/javascript'>
                    {literal}
                        var openGoogleJson = function(event) {
                            var input = event.target;
                            var reader = new FileReader();
                            var parent_td = document.getElementById('google_json');
                            reader.onload = function(){
                                console.log(reader.result.substring(0, 1024));
                                var json_input = document.getElementById("google_auth_json");
                                if (json_input == null) {
                                    var json_input_text = document.createElement('span');
                                    json_input_text.innerHTML = '<input type="hidden" id="google_auth_json" name="google_auth_json" />';
                                    parent_td.appendChild(json_input_text);
                                }
                                document.getElementById('google_auth_json').value = btoa(reader.result.substring(0, 1024));
                            };
                            reader.readAsText(input.files[0]);
                        };
                    {/literal}
                </script>
                JSON file is: <span style="color:{$GOOGLE_JSON_CONF.color}">{$GOOGLE_JSON_CONF.status}</span><input type="file" accept="text/plain" onchange="openGoogleJson(event)">
            </td>
        </tr>
        <tr>
            <td></td>
            <td><a href="https://developers.google.com/calendar/quickstart/php" target="_blank">{$MOD.LBL_GOOGLE_CALENDAR_GET_API_KEY}</a></td>
        </tr>

    </table>
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
        <tr>
            <td>
                <input title="{$APP.LBL_SAVE_BUTTON_TITLE}"
                accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"
                class="btn btn-primary" type="submit" name="save"
                onclick="return check_form('ConfigureSettings');"
                value="{$APP.LBL_SAVE_BUTTON_LABEL}" />

         <input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}"
                onclick="document.location.href='index.php?module=Administration&action=index'"
                class="btn btn-danger" type="button" name="cancel"
                value="{$APP.LBL_CANCEL_BUTTON_LABEL}" />
            </td>
        </tr>
      </table>

    {$JAVASCRIPT}

</form>
