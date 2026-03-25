<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td nowrap class="paginationWrapper">
            {if !empty($list_link)}
            <button type="button" id="save_and_continue" class="button" title="{$app_strings.LBL_SAVE_AND_CONTINUE}" onClick="this.form.action.value='Save';if(check_form('EditView')){ldelim}sendAndRedirect('EditView', '{$app_strings.LBL_SAVING} {$module}...', '{$list_link}');{rdelim}">
                {$app_strings.LBL_SAVE_AND_CONTINUE}
            </button>
            {/if}
            <span class="pagination">
                {if !empty($previous_link)}
                <button type="button" class="button" title="{$app_strings.LNK_LIST_PREVIOUS}" onClick="document.location.href='{$previous_link}';">
                    {sugar_getimage name="previous" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_PREVIOUS}
                </button>
                {else}
                <button type="button" class="button" title="{$app_strings.LNK_LIST_PREVIOUS}" disabled='true'>
                    {sugar_getimage name="previous_off" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_PREVIOUS}
                </button>
                {/if}
                &nbsp;&nbsp;
                ({$offset}{if !empty($total)} {$app_strings.LBL_LIST_OF} {$total}{$plus}{/if})
                &nbsp;&nbsp;
                {if !empty($next_link)}
                <button type="button" class="button" title="{$app_strings.LNK_LIST_NEXT}" onClick="document.location.href='{$next_link}';">
                    {sugar_getimage name="next" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_NEXT}
                </button>
                {else}
                <button type="button" class="button" title="{$app_strings.LNK_LIST_NEXT}" disabled="true">
                    {sugar_getimage name="next_off" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_NEXT}
                </button>
                {/if}
            </span>
        </td>
    </tr>
</table>
