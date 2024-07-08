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
            <ul class="subnav">
                {foreach from=$savedSearchData.options key=id item=option}
                    <li><a href="javascript:void(0)" class="parent-dropdown-action-handler"{if $id!=$savedSearchData.selected} onclick="SUGAR.savedViews.shortcutDropdown('{$id}', '{$currentModule}');"{/if}>{$option}{if $id==$savedSearchData.selected}&nbsp;&#10004{/if}</a></li>
                {/foreach}
            </ul>
            <span><span class="suitepicon suitepicon-action-caret"></span></span>
        </li>
    </ul>
{/if}

<ul class="clickMenu selectmenu searchLink SugarActionMenu listViewLinkButton listViewLinkButton_{$action_menu_location}">
    <li class="sugar_action_button">
        <a href="javascript:void(0)" class="glyphicon glyphicon-filter parent-dropdown-handler" onclick="listViewSearchIcon.toggleSearchDialog('latest'); $('#searchDialog .nav-tabs .active').removeClass('active'); $('#searchDialog .nav-tabs li').first().addClass('active'); $('#searchDialog').modal('toggle');" title='{sugar_translate label="LBL_FILTER_HEADER_TITLE"}'></a>
    </li>
</ul>
<ul class="searchAppliedAlert hidden clickMenu selectmenu searchAppliedAlertLink SugarActionMenu listViewLinkButton listViewLinkButton_{$action_menu_location}">
    <li class="sugar_action_button desktopOnly">
        <a href="javascript:void(0)" class="glyphicon glyphicon-list-alt clearSearchIcon parent-dropdown-handler" onclick="SUGAR.savedViews.shortcutDropdown('_none', '{$currentModule}');"></a>
        <a href="javascript:void(0)" class="glyphicon glyphicon-remove" onclick="SUGAR.savedViews.shortcutDropdown('_none', '{$currentModule}');">&Cross;</a>
    </li>
    <li class="sugar_action_button mobileOnly">
        <a href="javascript:void(0)" class="glyphicon glyphicon-list-alt clearSearchIcon parent-dropdown-handler" onclick=""></a>
        <a href="javascript:void(0)" class="glyphicon glyphicon-remove" onclick="SUGAR.savedViews.shortcutDropdown('_none', '{$currentModule}');"></a>
    </li>
</ul>