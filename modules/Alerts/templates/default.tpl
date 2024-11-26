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
    <div class="alert alert-item alert-{$result->priority|nl2br} flex-start gap-2 me-2 p-2 flex-fill" alert-id="{$result->id}">
        <div class="d-flex flex-column gap-1 flex-fill">
            <h4 class="alert-header">
                <strong class="text-dark">{$result->name|nl2br}</strong>
            </h4>
            <p class="alert-description">
                {$result->description|nl2br}
            </p>
            <div class="alert-bottom">
                {assign var="dateEntered" value=$result->date_entered|cat:' +7 hours'|date_format:"%H:%M:%S %d-%m-%Y"}
                {assign var="currentTime" value=$smarty.now+25200|date_format:"%H:%M:%S %d-%m-%Y"}

                {assign var="dateEntered_strtotime" value=$dateEntered|strtotime}
                {assign var="currentTime_strtotime" value=$currentTime|strtotime}

                {assign var="timeDiff" value=0}
                {math equation="x - y" x=$currentTime_strtotime y=$dateEntered_strtotime assign="timeDiff"} 

                {assign var="timeAgo" value=""}
                {assign var="timeTxt" value=""}

                {if $timeDiff < 60}
                    {assign var="timeAgo" value=$timeDiff|floor}
                    {assign var="timeTxt" value='giây trước'}
                {elseif $timeDiff < 3600}
                    {assign var="timeAgo" value=$timeDiff/60|floor}
                    {assign var="timeTxt" value='phút trước'}
                {elseif $timeDiff < 86400}
                    {assign var="timeAgo" value=$timeDiff/3600|floor}
                    {assign var="timeTxt" value='giờ trước'}
                {elseif $timeDiff < 2592000}
                    {assign var="timeAgo" value=$timeDiff/86400|floor}
                    {assign var="timeTxt" value='ngày trước'}
                {elseif $timeDiff < 31536000}
                    {assign var="timeAgo" value=$timeDiff/2592000|floor}
                    {assign var="timeTxt" value='tháng trước'}
                {else}
                    {assign var="timeAgo" value=$timeDiff/31536000|floor}
                    {assign var="timeTxt" value='năm trước'}
                {/if}

                <span class="time">
                    {$timeAgo} {$timeTxt}
                </span>
            </div>
        </div>
        <div class="alert-footer w-10 {if $result->is_read != 0}readed{else}unread{/if}">
            <span></span>
        </div>
        {if $result->url_redirect != null && !($result->url_redirect|strstr:"fake_") }
            <a class="alert-redirect" href="index.php?module=Alerts&action=redirect&record={$result->id}"></a>
        {/if}
    </div>
{/foreach}

