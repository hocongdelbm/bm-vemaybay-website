<input title="{$APP.LBL_SAVE_BUTTON_TITLE}"
       accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"
       class="btn btn-primary"
       type="submit"
       name="save"
       onclick="return check_form('ConfigureSettings');"
       value="{$APP.LBL_SAVE_BUTTON_LABEL}">
<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}"
       onclick="document.location.href='index.php?module=Administration&action=index'"
       class="btn btn-danger"
       type="button"
       name="cancel"
       value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
