{* FYI: This template is also used in the detail view pagination *}
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td nowrap class="paginationWrapper">
            <script>
                SUGAR.saveAndContinue = function (elem)
                    {ldelim}
                        elem.form.action.value='Save';
                        if(check_form('EditView'))
                        {ldelim}
                            sendAndRedirect('EditView', '{$app_strings.LBL_SAVING} {$module}...', '{$list_link}');
                        {rdelim}
                    {rdelim}
            </script>
            {if empty($list_link)}
                {* remove the other save and continue button next to the view change log when you are on the last item on the list *}
                {literal}
                    <script>
                        $(document).ready(function () {
                          $('#save_and_continue').remove();
                        })
                    </script>
                {/literal}
            {/if}

            <ul class="pagination justify-content-center align-items-center gap-2">
                {if !empty($previous_link)}
                    <li>
                        <a title="{$app_strings.LNK_LIST_PREVIOUS}" href="{$previous_link}">
                            {*{sugar_getimage name="previous" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_PREVIOUS}*}
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                            </svg>
                            <span class="pagination-label">{$app_strings.LNK_LIST_PREVIOUS}</span>
                        </a>
                    </li>
                {else}
                    <li>
                        <a title="{$app_strings.LNK_LIST_PREVIOUS}" disabled='true'>
                            {*{sugar_getimage name="previous" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_PREVIOUS}*}
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                            </svg>
                            <span class="pagination-label">{$app_strings.LNK_LIST_PREVIOUS}</span>
                        </a>
                    </li>
                {/if}

                <li><a href="#">({$offset}{if !empty($total)} {$app_strings.LBL_LIST_OF} {$total}{$plus}{/if})</a></li>

                {if !empty($next_link)}
                    <li>
                        <a title="{$app_strings.LNK_LIST_NEXT}" href="{$next_link}">
                            {*{sugar_getimage name="next_off" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_NEXT}*}
                            <span class="pagination-label">{$app_strings.LNK_LIST_NEXT}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </a>
                    </li>
                    {else}
                    <li>
                        <a title="{$app_strings.LNK_LIST_NEXT}" disabled="true">
                            {*{sugar_getimage name="next_off" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_NEXT}*}
                            <span class="pagination-label">{$app_strings.LNK_LIST_NEXT}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </a>
                    </li>
                {/if}
              </ul>
        </td>
    </tr>
</table>
