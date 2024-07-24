<div class="email-subject">
    {if $bean}
        {* Handle empty subject *}
        {if $bean.name == ''}
            {capture name=subject assign=subject}{$MOD.LBL_NO_SUBJECT}{/capture}
        {else}
            {capture name=subject assign=subject}{$bean.name}{/capture}
        {/if}

        {* Display Link *}
        {if !empty($bean.id) and $bean.status == $APP_LIST_STRINGS.dom_email_status.draft}
            <a href="index.php?module=Emails&action=DetailDraftView&record={$bean.id}">{$subject}</a>
        {elseif !empty($bean.id) and $bean.status != $APP_LIST_STRINGS.dom_email_status.draft}
            <a href="index.php?module=Emails&action=DetailView&record={$bean.id}">{$subject}</a>
        {else}
            <a href="index.php?module=Emails&action=DisplayDetailView&folder_name={$bean.folder}&folder={$bean.folder_type}&inbound_email_record={$bean.inbound_email_record}&uid={$bean.uid}&msgno={$bean.msgno}">{$subject}</a>
        {/if}
    {/if}
</div>