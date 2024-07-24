<div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-advanced-search">
        <div{if $orderBySelectOnly} style="display:none;"{/if}>
            <input id='displayColumnsDef' type='hidden' name='displayColumns'>
            <input id='hideTabsDef' type='hidden' name='hideTabs'>
            {$columnChooser}
            <br>
        </div>
        <div class="col-xs-12 saved-search-sort-column-config-row">
            <label>{sugar_translate label='LBL_ORDER_BY_COLUMNS' module='SavedSearch'}</label>

        </div>
        <div class="form-item saved-search-sort-column-config-row">
            <select name='orderBy' id='orderBySelect'>
            </select>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-advanced-search">
        <div class="col-xs-12 saved-search-sort-column-config-row">
            <label>{sugar_translate label='LBL_DIRECTION' module='SavedSearch'}</label>
        </div>
        <div class="form-item radios saved-search-sort-column-config-row">
            <div><input id='sort_order_desc_radio' type='radio' name='sortOrder' value='DESC'
                        {if $selectedSortOrder == 'DESC'}checked{/if}>&nbsp;<span
                        onclick='document.getElementById("sort_order_desc_radio").checked = true'
                        style="cursor: pointer; cursor: hand">{$MOD.LBL_DESCENDING}</span></div>

            <div><input id='sort_order_asc_radio' type='radio' name='sortOrder' value='ASC'
                        {if $selectedSortOrder == 'ASC'}checked{/if}>&nbsp;<span
                        onclick='document.getElementById("sort_order_asc_radio").checked = true'
                        style="cursor: pointer; cursor: hand">{$MOD.LBL_ASCENDING}</span>
            </div>
        </div>
    </div>

</div>
<script>
    SUGAR.savedViews.columnsMeta = {$columnsMeta};
    columnsMeta = {$columnsMeta};
    saved_search_select = "{$SAVED_SEARCH_SELECT}";
    selectedSortOrder = "{$selectedSortOrder|default:'DESC'}";
    selectedOrderBy = "{$selectedOrderBy}";


    {literal}
    //this populates the label that shows the name of the current saved view
    //The label is located under the update/delete buttons
    function fillInLabels() {
        //this javascript runs and populates values in savedSearchForm.tpl
        x = document.getElementById('saved_search_select');
        if ((typeof(x) != 'undefined' && x != null) && x.selectedIndex != 0) {
            curr_search_name = document.getElementById('curr_search_name');
            curr_search_name.innerHTML = '';
            curr_search_name.appendChild(document.createTextNode('"' + x.options[x.selectedIndex].text + '"'));
            document.getElementById('ss_update').disabled = false;
            document.getElementById('ss_delete').disabled = false;
            $('.hideUnusedSavedSearchElements').show();
        } else {
            document.getElementById('ss_update').disabled = true;
            document.getElementById('ss_delete').disabled = true;
            document.getElementById('curr_search_name').innerHTML = '';
            $('.hideUnusedSavedSearchElements').hide();
        }
    }
    //call scripts that need to get run onload of this form.  This function is called when image
    //to collapse/show subpanels is loaded
    function loadSSL_Scripts() {
        //this will fill in the name of the current module, and enable/disable update/delete buttons
        fillInLabels();
        //this populates the order by dropdown, and activates the chooser widget.
        SUGAR.savedViews.handleForm();
    }

    {/literal}
</script>
