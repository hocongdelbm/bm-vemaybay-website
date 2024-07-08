<div class="moduleTitle title-search__form">
    <h2 class="module-title-text">{$APP.LBL_SEARCH_TITLE}</h2>
</div>
<div style="clear:both;">
    <form id="search-wrapper-form">
        {*hidden input to handle actions*}
        {search_controller}

        <table>
            <tbody>
            <tr style="padding-bottom: 10px">
                <td class="submitButtons" colspan="8" nowrap="">
                    <label for="searchFieldMain" class="text-muted hide">{$APP.LBL_SEARCH_QUERY}</label>
                    <input id="searchFieldMain" title="{$APP.LBL_SEARCH_TEXT_FIELD_TITLE_ATTR}" class="searchField"
                           type="text" size="80" name="search-query-string" value="{$searchQueryString}" autofocus>
                    <input type="submit" onclick="searchForm.onSubmitClick(this);"
                           title="{$APP.LBL_SEARCH_SUBMIT_FIELD_TITLE_ATTR}" class="button primary"
                           value="{$APP.LBL_SEARCH_SUBMIT_FIELD_VALUE}">&nbsp;
                </td>
            <tr>
            <tr style="padding-top: 10px;">
                <td colspan="6" style="padding-left: 20px;" nowrap="">
                    <div id="inlineGlobalSearch">
                        <table style="margin-bottom:0;">
                            <tbody>
                            <label for="search-query-size"
                                   class="text-muted">{$APP.LBL_SEARCH_RESULTS_PER_PAGE}</label>
                            {html_options options=$sizeOptions selected=$searchQuerySize id="search-query-size" name="search-query-size"}
                            &nbsp;&nbsp;
                            <input type="hidden" name="search-query-from" value="{$searchQueryFrom}">

                            {if $engineOptions|@count gt 1}
                                <label for="search-query-size" class="text-muted">{$APP.LBL_SEARCH_ENGINE}</label>
                                {html_options options=$engineOptions selected=$searchQueryEngine id="search-engine" name="search-engine"}
                            {else}
                                {assign var=firstRow value=$engineOptions|@key}
                                <input type="hidden" name="search-engine" value="{$firstRow}" />
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
    <script>
        {literal}
        var searchForm = {
          onSubmitClick: function (e) {
            // jump to the first page on new results list
            $('input[name="search-query-from"]').val(0);
            return true;
          }
        };
        {/literal}
    </script>
</div>
