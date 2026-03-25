<script>
    {literal}
    $(function () {
        var $dialog = $('<div></div>')
                .html(SUGAR.language.get('app_strings', 'LBL_SEARCH_HELP_TEXT'))
                .dialog({
                    autoOpen: false,
                    title: SUGAR.language.get('app_strings', 'LBL_SEARCH_HELP_TITLE'),
                    width: 700
                });

        $('.help-search').click(function () {
            $dialog.dialog('open');
            // prevent the default action, e.g., following a link
        });
    });
    {/literal}
</script>

<ul class="nav nav-tabs-advanced admin_tabs-list" id="myTab" role="tablist">
    {if !$searchFormInPopup}
    <li class="admin_tabs-item d-none" role="presentation">
        <a id="basic_search_link" href="javascript:void(0)" accesskey="{$APP.LBL_ADV_SEARCH_LNK_KEY}">{$APP.LNK_BASIC_FILTER}</a>
    </li>
    {/if}

	<li class="admin_tabs-item" role="presentation">
		<a id="advanced_search_link" href="javascript:void(0)" class="active" accesskey="{$APP.LBL_ADV_SEARCH_LNK_KEY}">{$APP.LNK_ADVANCED_FILTER}</a>
	</li>
</ul>

<div class="advanced-search-wrap__theme box-tabs row g-0 border-top">
    {{assign var='accesskeycount' value=0}}  {{assign var='ACCKEY' value=''}}
    {{foreach name=colIteration from=$formData key=col item=colData}}
    <div class="col-lg-4 col-md-6 col-xs-6 col-12 mb-2 px-2">
        <div class="row align-items-center">
            <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-advanced-search mt-0">
                {{math assign="accesskeycount" equation="$accesskeycount + 1"}}
                {{if $accesskeycount==1}} {{assign var='ACCKEY' value=$APP.LBL_FIRST_INPUT_SEARCH_KEY}} {{else}} {{assign var='ACCKEY' value=''}} {{/if}}
                {counter assign=index}
                {math equation="left % right" left=$index right=$templateMeta.maxColumns assign=modVal}
    
                {{if isset($colData.field.label)}}
                <label class="text-label" for='{{$colData.field.name}}'>{sugar_translate label='{{$colData.field.label}}' module='{{$module}}'}</label>
                {{elseif isset($fields[$colData.field.name])}}
                <label class="text-label" for='{{$fields[$colData.field.name].name}}'>{sugar_translate label='{{$fields[$colData.field.name].vname}}' module='{{$module}}'}</label>
                {{/if}}
            </div>
            <div class="col-8 col-sm-8 col-md-8 col-lg-8 form-item">
                <div class="search-advance-wrap input-group gap-2 flex-nowrap">
                    {{if $fields[$colData.field.name]}}
                    {{sugar_field parentFieldArray='fields' accesskey=$ACCKEY vardef=$fields[$colData.field.name] displayType=$displayType displayParams=$colData.field.displayParams typeOverride=$colData.field.type formName=$form_name}}
                    {{/if}}
                </div>
            </div>
        </div>
    </div>
    {{/foreach}}

    {if $DISPLAY_SAVED_SEARCH}
        <!-- TẠM THỜI OFF CHỨC NĂNG LƯU BỘ LỌC -->
        <div class="display-saved-search__wrap d-none p-2">
            <div class="row mb-2 display-saved-search">
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-advanced-search">
                    {if !$searchFormInPopup}
                        <div>
                            <a class='tabFormAdvLink' onhover href='javascript:toggleInlineSearch()'>
                                {capture assign="alt_show_hide"}{sugar_translate label='LBL_ALT_SHOW_OPTIONS'}{/capture}
                                {sugar_getimage alt=$alt_show_hide name="advanced_search" ext=".gif" other_attributes='border="0" id="up_down_img" '}
                                &nbsp;{$APP.LNK_SAVED_VIEWS}
                            </a><br>
                            <input type='hidden' id='showSSDIV' name='showSSDIV' value='{$SHOWSSDIV}'>
                            <p>
                        </div>
                    {/if}
                    <label>{sugar_translate label='LBL_SAVE_SEARCH_AS' module='SavedSearch'}:</label>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2 d-flex gap-2">
                    <div{if !$searchFormInPopup} style='{$DISPLAYSS}'{/if} id='inlineSavedSearch'>
                        {$SAVED_SEARCH}
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 form-item d-flex gap-2">
                        <input type='text' name='saved_search_name'>
                        <input type='hidden' name='search_module' value=''>
                        <input type='hidden' name='saved_search_action' value=''>
                        <input title='{$APP.LBL_SAVE_BUTTON_LABEL}' value='{$APP.LBL_SAVE_BUTTON_LABEL}' class='btn btn-primary w-fit-content min-w-80' type='button' name='saved_search_submit' onclick='SUGAR.savedViews.setChooser(); return SUGAR.savedViews.saved_search_action("save");'>
    
                        <div class="hideUnusedSavedSearchElements" scope='row' width='10%' nowrap="nowrap"{if !$savedSearchData.selected} style="display: none;"{/if}>
                            <label>{sugar_translate label='LBL_MODIFY_CURRENT_FILTER' module='SavedSearch'}: <span id='curr_search_name'>"{$savedSearchData.options[$savedSearchData.selected]}"</span></label>
                        </div>
                        <div class="hideUnusedSavedSearchElements" width='30%' nowrap{if !$savedSearchData.selected} style="display: none;"{/if}>
                            <input class='btn btn-primary'
                                onclick='SUGAR.savedViews.setChooser(); return SUGAR.savedViews.saved_search_action("update")'
                                value='{$APP.LBL_UPDATE}' title='{$APP.LBL_UPDATE}' name='ss_update' id='ss_update'
                                type='button'>
                            <input class='btn btn-danger'
                                onclick='return SUGAR.savedViews.saved_search_action("delete", "{sugar_translate label='LBL_DELETE_CONFIRM' module='SavedSearch'}")'
                                value='{$APP.LBL_DELETE}' title='{$APP.LBL_DELETE}' name='ss_delete' id='ss_delete'
                                type='button'>
                        </div>
                </div>
            </div>
        </div>
    {/if}

    {if $displayType != 'popupView'}
        <div class="submitButtonsAdvanced--wrap">
            <div class="submitButtonsAdvanced d-flex align-items-center gap-2">
                <div class="search-clear-button--wrap d-flex gap-2">
                    <input tabindex='2' title='{$APP.LBL_SEARCH_BUTTON_TITLE}' onclick='SUGAR.savedViews.setChooser()' class='btn btn-primary' type='submit' name='button' value='{$APP.LBL_SEARCH_BUTTON_LABEL}' id='search_form_submit_advanced'/>
                    <!-- <input tabindex='2' title='{$APP.LBL_CLEAR_BUTTON_TITLE}' onclick='SUGAR.searchForm.clear_form(this.form); if(document.getElementById("saved_search_select")){ldelim}document.getElementById("saved_search_select").options[0].selected=true;{rdelim} return false;' class='btn btn-danger' type='button' name='clear' id='search_form_clear_advanced' value='{$APP.LBL_CLEAR_BUTTON_LABEL}'/> -->
                    <input tabindex='2' title='{$APP.LBL_CLEAR_BUTTON_TITLE}' onclick="SUGAR.searchForm.clear_form(this.form); SUGAR.ajaxUI.submitForm(this.form); return false;" class='btn btn-secondary' type='button' name='clear' id='search_form_clear_advanced' value='{$APP.LBL_CLEAR_BUTTON_LABEL}'/>
                    {if $DOCUMENTS_MODULE}
                        <!-- <input title="{$APP.LBL_BROWSE_DOCUMENTS_BUTTON_TITLE}" type="button" class="btn btn-primary" value="{$APP.LBL_BROWSE_DOCUMENTS_BUTTON_LABEL}" onclick='open_popup("Documents", 600, 400, "&caller=Documents", true, false, "");'/> -->
                    {/if}
                </div>
    
                {if $searchFormInPopup}
                <div class="d-flex gap-2 align-items-center">
                {/if}
                    <a id="basic_search_link" class="btn btn-secondary d-none" href="javascript:void(0)" accesskey="{$APP.LBL_ADV_SEARCH_LNK_KEY}">{$APP.LNK_BASIC_FILTER}</a>
                    
                    <span class='white-space d-flex gap-2 align-items-center d-none'>
                        {if $SAVED_SEARCHES_OPTIONS}|<b>{$APP.LBL_SAVED_FILTER_SHORTCUT}</b>{$SAVED_SEARCHES_OPTIONS} {/if}
                        <span id='go_btn_span' style='display:none'>
                            <input tabindex='2' title='go_select' id='go_select' onclick='SUGAR.searchForm.clear_form(this.form);' class='button' type='button' name='go_select' value=' {$APP.LBL_GO_BUTTON_LABEL} '/>
                        </span>
                    </span>
                    {if $searchFormInPopup}
                </div>
                {/if}
            </div>
        </div>
    {/if}
</div>


<script>
    {literal}
    if (typeof(loadSSL_Scripts) == 'function') {
        loadSSL_Scripts();
    }
    {/literal}
</script>
<script>
    {literal}
    $(document).ready(function () {
        $( '#advanced_search_link' ).one( "click", function() {
			//alert( "This will be displayed only once." );
			SUGAR.searchForm.searchFormSelect('{/literal}{$module}{literal}|advanced_search','{/literal}{$module}{literal}|basic_search');
		});
        
        $('#basic_search_link').one("click", function () {
            //alert( "This will be displayed only once." );
            SUGAR.searchForm.searchFormSelect('{/literal}{$module}{literal}|basic_search', '{/literal}{$module}{literal}|advanced_search');
        });

        $('#search_form select').select2();
    });
    {/literal}
</script>
