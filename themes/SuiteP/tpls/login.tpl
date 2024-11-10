
<script type='text/javascript'>
    var LBL_LOGIN_SUBMIT = '{sugar_translate module="Users" label="LBL_LOGIN_SUBMIT"}';
    var LBL_REQUEST_SUBMIT = '{sugar_translate module="Users" label="LBL_REQUEST_SUBMIT"}';
    var LBL_SHOWOPTIONS = '{sugar_translate module="Users" label="LBL_SHOWOPTIONS"}';
    var LBL_HIDEOPTIONS = '{sugar_translate module="Users" label="LBL_HIDEOPTIONS"}';
</script>

<!-- Start login container -->

<div id="form-login" class="p_login d-flex align-items-center justify-content-center">
    <div class="form-login__wrapper">
        <img src="themes/SuiteP/images/home/company_logo.png" alt="" class="my-4">

        <form class="form-signin d-flex gap-4 flex-column" role="form" action="index.php" method="post" name="DetailView" id="form" onsubmit="return document.getElementById('cant_login').value == ''" autocomplete="off">
            <span class="error" id="browser_warning" style="display: none">
                {sugar_translate label="WARN_BROWSER_VERSION_WARNING"}
            </span>
            <span class="error" id="ie_compatibility_mode_warning" style="display: none">
                {sugar_translate label="WARN_BROWSER_IE_COMPATIBILITY_MODE_WARNING"}
            </span>

            {if $LOGIN_ERROR_MESSAGE}
                <span class='error'>{$LOGIN_ERROR_MESSAGE}</span>
            {/if}

            {if $LOGIN_ERROR !=''}
                <span class="error">{$LOGIN_ERROR}</span>
            {if $WAITING_ERROR !=''}
                <span class="error">{$WAITING_ERROR}</span>
            {/if} {else}
                <span id="post_error" class="error"></span>
            {/if}

            <input type="hidden" name="module" value="Users" />
            <input type="hidden" name="action" value="Authenticate" />
            <input type="hidden" name="return_module" value="Users" />
            <input type="hidden" name="return_action" value="Login" />
            <input type="hidden" id="cant_login" name="cant_login" value="" />

            {foreach from=$LOGIN_VARS key=key item=var}
                <input type="hidden" name="{$key}" value="{$var}" />
            {/foreach} {if !empty($SELECT_LANGUAGE)}

            <div class="text-field login-language-chooser d-none">
                <label for="login_language">{sugar_translate module="Users" label="LBL_LANGUAGE"}</label>
                <select id="login_language" name="login_language" onchange="switchLanguage(this.value)">
                    {$SELECT_LANGUAGE}
                </select>
            </div>
            {/if}
            
            <div class="text-field">
                <label for="user_name">{sugar_translate module="Users" label="LBL_USER_NAME" }</label>
                <input type="text" class="form-control" placeholder="{sugar_translate module="Users" label="LBL_USER_NAME" }" required autofocus tabindex="1" id="user_name" name="user_name" value='{$LOGIN_USER_NAME}' autocomplete="off">
            </div>

            <div class="text-field">
                <label for="username_password">{sugar_translate module="Users" label="LBL_PASSWORD" }</label>

                <div class="input-group input-group-merge position-relative">
                    <input type="password" class="form-control" placeholder="{sugar_translate module="Users" label="LBL_PASSWORD" }" tabindex="2" id="username_password" name="username_password" value='{$LOGIN_PASSWORD}' autocomplete="off">
                    <span id="view-password" class="cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 19c.946 0 1.81-.103 2.598-.281l-1.757-1.757c-.273.021-.55.038-.841.038-5.351 0-7.424-3.846-7.926-5a8.642 8.642 0 0 1 1.508-2.297L4.184 8.305c-1.538 1.667-2.121 3.346-2.132 3.379a.994.994 0 0 0 0 .633C2.073 12.383 4.367 19 12 19zm0-14c-1.837 0-3.346.396-4.604.981L3.707 2.293 2.293 3.707l18 18 1.414-1.414-3.319-3.319c2.614-1.951 3.547-4.615 3.561-4.657a.994.994 0 0 0 0-.633C21.927 11.617 19.633 5 12 5zm4.972 10.558-2.28-2.28c.19-.39.308-.819.308-1.278 0-1.641-1.359-3-3-3-.459 0-.888.118-1.277.309L8.915 7.501A9.26 9.26 0 0 1 12 7c5.351 0 7.424 3.846 7.926 5-.302.692-1.166 2.342-2.954 3.558z"></path></svg>
                    </span>
                </div>
            </div>

            <div class="form__field">
                <input id="bigbutton" class="btn-block" type="submit" title="{sugar_translate module="Users" label="LBL_LOGIN_BUTTON_LABEL" }" tabindex="3" name="Login" value="{sugar_translate module="Users" label="LBL_LOGIN_BUTTON_LABEL" }">
            </div>

            <div id="forgotpasslink" style="cursor: pointer; display:{$DISPLAY_FORGOT_PASSWORD_FEATURE};" onclick='toggleDisplay("forgot_password_dialog");'>
                <a href="javascript:void(0)">{sugar_translate module="Users" label="LBL_LOGIN_FORGOT_PASSWORD"}</a>
            </div>
        </form>

        <form class="form-signin passform" role="form" action="index.php" method="post" name="DetailView" name="fp_form" id="fp_form" autocomplete="off">
            <div id="forgot_password_dialog" style="display: none">
                <input type="hidden" name="entryPoint" value="GeneratePassword" />
                <div id="generate_success" class="text-danger my-3 small fw-semibold"></div>

                <div class="d-flex gap-4 flex-column">
                    <div class="text-field">
                        <label for="fp_user_name">{sugar_translate module="Users" label="LBL_USER_NAME" }</label>
                        <div class="input-group">
                            <input type="text" class="form-control" size='26' id="fp_user_name" name="fp_user_name" value='{$LOGIN_USER_NAME}' placeholder="{sugar_translate module="Users" label="LBL_USER_NAME"}" autocomplete="off">
                        </div>
                    </div>
                    <div class="text-field">
                        <label for="fp_user_mail">{sugar_translate module="Users" label="LBL_EMAIL" }</label>
                        <div class="input-group">
                            <input type="text" class="form-control" size='26' id="fp_user_mail" name="fp_user_mail" value='' placeholder="{sugar_translate module="Users" label="LBL_EMAIL"}" autocomplete="off">
                        </div>
                    </div>
    
                    {$CAPTCHA}
                    <div id="wait_pwd_generation"></div>
                    <input title="Email Temp Password" class="button btn-block" type="button" style="display:inline" onclick="validateAndSubmit();
                    return document.getElementById('cant_login').value == ''" id="generate_pwd_button" name="fp_login" value="{sugar_translate module="Users" label="LBL_LOGIN_SUBMIT"}" autocomplete="off">
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End login container -->



