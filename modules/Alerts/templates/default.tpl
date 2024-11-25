{$Flash}

{literal}
<script>
        $(document).on("click", ".clear-all-alerts-btn", function () {
            $('.desktop_notifications:first .alert-item').each(function (i, v) {
               let alert    = $(v);
               let alert_id = $(v).attr('alert-id');

               Alerts.clearAlert(alert_id);
            });
        });

        $(document).on("click", ".mark-all-alerts-btn", function () {
            $('.desktop_notifications:first .alert-item').each(function (i, v) {
               let alert    = $(v);
               let alert_id = $(v).attr('alert-id');

               Alerts.markAsRead(alert_id);
            });
        });

        document.querySelectorAll('.notification-show').forEach(item => {
            item.addEventListener('click', function () {
                document.querySelectorAll('.notification-show').forEach(el => el.classList.remove('active'));
                this.classList.add('active');
            });
        });

</script>
{/literal}

{foreach from=$Results item=result}
    {* <div class="alert alert-item alert-light module-alert py-3 px-2 m-0 {if $result->is_read != 0}readed{else}unread{/if}" role="alert">
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
    </div> *}

    <div class="alert alert-item flex-start gap-2 me-2 p-2 flex-fill" alert-id="{$result->id}">
        <div class="d-flex flex-column gap-1 flex-fill">
            <h4 class="alert-header">
                <strong class="text-dark">{$result->name|nl2br}</strong>
            </h4>
            <p class="alert-description">
                {$result->description|nl2br}
            </p>
        </div>
        <div class="alert-footer w-10 {if $result->is_read != 0}readed{else}unread{/if}">
            <span></span>
        </div>
        {if $result->url_redirect != null && !($result->url_redirect|strstr:"fake_") }
            <a class="alert-redirect" href="index.php?module=Alerts&action=redirect&record={$result->id}"></a>
        {/if}
    </div>
{/foreach}

