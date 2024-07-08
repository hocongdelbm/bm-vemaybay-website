{if !$currentModule}
    {assign var="currentModule" value=$savedSearchData.module}
    {if !$currentModule}
        {assign var="currentModule" value=$pageData.bean.moduleName}
    {/if}
{/if}

{if $savedSearchData.hasOptions}
    <ul class="action-link action-link-{$action_menu_location} clickMenu selectActions fancymenu show listViewLinkButton listViewLinkButton_{$action_menu_location}">
        <li class="sugar_action_button">
            <a href="javascript:void(0)" class="parent-dropdown-handler" title="{$APP.LBL_SAVED_FILTER_SHORTCUT}" onclick="return false;">
                <label class="selected-actions-label"><span class="glyphicon glyphicon-bookmark"></span><span class="selected-actions-label-text">{$APP.LBL_SAVED_FILTER_SHORTCUT}</span></label>
            </a>
            <ul class="subnav box-list dropdown-menu">
                {foreach from=$savedSearchData.options key=id item=option}
                    <li><a href="javascript:void(0)" class="parent-dropdown-action-handler"{if $id!=$savedSearchData.selected} onclick="SUGAR.savedViews.shortcutDropdown('{$id}', '{$currentModule}');"{/if}>{$option}{if $id==$savedSearchData.selected}&nbsp;&#10004{/if}</a></li>
                {/foreach}
            </ul>
            <span><span class="suitepicon suitepicon-action-caret"></span></span>
        </li>
    </ul>
{/if}

<ul class="clickMenu selectmenu searchLink SugarActionMenu listViewLinkButton listViewLinkButton_{$action_menu_location}">
    <li class="sugar_action_button btn btn-primary">
        <a href="javascript:void(0)" class="parent-dropdown-handler" onclick="listViewSearchIcon.toggleSearchDialog('latest'); $('#searchDialog .nav-tabs .active').removeClass('active'); $('#searchDialog .nav-tabs li').first().addClass('active'); $('#searchDialog').modal('toggle');" title='{sugar_translate label="LBL_FILTER_HEADER_TITLE"}'>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z"/>
            </svg>
        </a>
    </li>
</ul>

<ul class="searchAppliedAlert hidden clickMenu selectmenu searchAppliedAlertLink SugarActionMenu listViewLinkButton listViewLinkButton_{$action_menu_location}">
    <li class="sugar_action_button desktopOnly btn btn-primary">
        <a href="javascript:void(0)" class="clearSearchIcon parent-dropdown-handler" onclick="SUGAR.savedViews.shortcutDropdown('_none', '{$currentModule}');">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-card-list" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8zm0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zM4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zm0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0z"/>
              </svg>
        </a>
        <a href="javascript:void(0)" class="btn btn danger glyphicon glyphicon-remove" onclick="SUGAR.savedViews.shortcutDropdown('_none', '{$currentModule}');">&Cross;</a>
    </li>
    <li class="sugar_action_button mobileOnly btn btn-primary">
        <a href="javascript:void(0)" class="clearSearchIcon parent-dropdown-handler">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-card-list" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8zm0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zM4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zm0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0z"/>
              </svg>
        </a>
        <a href="javascript:void(0)" class="btn btn danger glyphicon glyphicon-remove" onclick="SUGAR.savedViews.shortcutDropdown('_none', '{$currentModule}');"></a>
    </li>
</ul>