{$Flash}

{if !$Flash}
<div class="notification-header">
    <h3 class="notification-title">Thông báo</h3>
    <div class="clear-all-alerts-container">
        <a class="clear-all-alerts-btn cursor-pointer">{sugar_translate label="LBL_CLEARALL"}</a>
        {literal}
        <script>
              $('.clear-all-alerts-btn').unbind('click').click(function (event) {
                $('.desktop_notifications:first .close').each(function (i, v) {
                  $(v).click();
                });
              });
        </script>
        {/literal}
    </div> 
</div>
{/if}
{foreach from=$Results item=result}
    <div class="alert alert-item alert-light module-alert py-3 px-2 m-0 {if $result->is_read != 0}readed{else}unread{/if}" role="alert">
        <h4 class="alert-header flex-fill">
            <strong class="text-dark">{$result->name|nl2br}</strong>
        </h4>
        <a onclick="Alerts.prototype.markAsRead('{$result->id}');" class="notification-close close">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path></svg>
        </a>
        <p class="alert-body alert-description">
            {$result->description|nl2br}
        </p>
        {if $result->url_redirect != null && !($result->url_redirect|strstr:"fake_") }
            <a class="alert-redirect" href="index.php?module=Alerts&action=redirect&record={$result->id}"></a>
        {/if}
    </div>
{/foreach}

