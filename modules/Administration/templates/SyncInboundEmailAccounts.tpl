{literal}
<script>
    $(function(){

        /**
         * Event handler for submit button
         * - prevent form submit
         * - fill ie multi-select if empty
         * - submit the form
         */
        $('form[name="sync-form"]').submit(function (e) {
            e.preventDefault();

            // select all inbound email if no one is selected

            if (null === $('select[name="ie-sel[]"]').val()) {
                $('select[name="ie-sel[]"] option').prop('selected', true);
            }

            this.submit();

            $('form[name="sync-form"]').hide();

            setInterval(function(){
                $.get('modules/Administration/SyncInboundEmailAccounts/sync_output.html', function(resp){
                    $('#sync-results').html(resp);
                });
            }, 1000);

        });

    });

</script>
{/literal}

{$app_strings.LBL_SYNC_IE_EMAILS}

<form name="sync-form" method="POST" action="">

    <input type="hidden" name="method" value="sync">

    {$app_strings.LBL_EMAIL_SETTINGS_NAME}

    <br>

    <select name="ie-sel[]" class="ie-sel" multiple="multiple" title="{$app_strings.LBL_EMAIL_SETTINGS_NAME}">
        {foreach from=$ieList item=ie}
            <option value="{$ie.id}">{$ie.name}</option>
        {/foreach}
    </select>

    <br>

    <input class="sync-btn" type="submit" value="{$app_strings.LBL_SYNC}">

</form>
<div id="sync-results"></div>
