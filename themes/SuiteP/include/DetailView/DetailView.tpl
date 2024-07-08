{{sugar_include type="smarty" file=$headerTpl}}
{sugar_include include=$includes}
<div class="detail-view mt-3">
    <div class="mobile-pagination">{$PAGINATION}</div>
    {*display tabs*}
    {{counter name="tabCount" start=-1 print=false assign="tabCount"}}
    <ul class="nav nav-tabs admin_tabs-list nav-tabs__detailview">
        {{if $useTabs}}
        {{counter name="isection" start=0 print=false assign="isection"}}
        {{foreach name=section from=$sectionPanels key=label item=panel}}
        {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
        {* if tab *}
        {{if (isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true)}}
        {*if tab display*}
        {{counter name="tabCount" print=false}}
        {{if $tabCount == '0'}}
        <li role="presentation" class="admin_tabs-item active">
            <a id="tab{{$tabCount}}" data-bs-toggle="tab" class="active hidden-xs">
                {sugar_translate label='{{$label}}' module='{{$module}}'}
            </a>

            {* Count Tabs *}
            {{counter name="tabCountOnlyXS" start=-1 print=false assign="tabCountOnlyXS"}}
            {{foreach name=sectionOnlyXS from=$sectionPanels key=labelOnly item=panelOnlyXS}}
            {{capture name=label_upper_count_only assign=label_upper_count_only}}{{$labelOnly|upper}}{{/capture}}
            {{if (isset($tabDefs[$label_upper_count_only].newTab) && $tabDefs[$label_upper_count_only].newTab == true)}}
            {{counter name="tabCountOnlyXS" print=false}}
            {{/if}}
            {{/foreach}}

            {*
                For the mobile view, only show the first tab has a drop down when:
                * There is more than one tab set
                * When Acton Menu's are enabled
            *}
            <a id="xstab{{$tabCount}}" href="#" class="active visible-xs first-tab{{if $tabCountOnlyXS > 0}}-xs{{/if}} dropdown-toggle" data-bs-toggle="dropdown">
                {sugar_translate label='{{$label}}' module='{{$module}}'}
            </a>
            {{if $tabCountOnlyXS > 0}}
            <ul id="first-tab-menu-xs" class="dropdown-menu">
                {{counter name="tabCountXS" start=1 print=false assign="tabCountXS"}}
                {{foreach name=sectionXS from=$sectionPanels key=label item=panelXS}}
                {{capture name=label_upper_xs assign=label_upper_xs}}{{$label|upper}}{{/capture}}
                {{if (isset($tabDefs[$label_upper_xs].newTab) && $tabDefs[$label_upper_xs].newTab == true)}}
                <li role="presentation">
                    <a id="tab{{$tabCountXS}}" data-bs-toggle="tab" onclick="changeFirstTab(this, 'tab-content-{{$tabCountXS}}');">
                        {sugar_translate label='{{$label}}' module='{{$module}}'}
                    </a>
                </li>
                {{/if}}
                {{counter name="tabCountXS" print=false}}
                {{/foreach}}
            </ul>
            {{/if}}

        </li>
        {{else}}
        <li role="presentation" class="admin_tabs-item hidden-xs">
            <a id="tab{{$tabCount}}" data-bs-toggle="tab">
                {sugar_translate label='{{$label}}' module='{{$module}}'}
            </a>
        </li>
        {{/if}}
        {{else}}
        {* if panel skip*}
        {{/if}}
        {{/foreach}}
        {{else}}
        {*
            Since: SuieCRM 7.8
            When action menus are enabled and When there are only panels and there are not any tabs,
            make the first panel a tab so that the action menu looks correct. This is regardless of what the
            meta/studio defines the first panel should always be tab.
        *}
        {if $config.enable_action_menu and $config.enable_action_menu != false}
            {{foreach name=section from=$sectionPanels key=label item=panel}}
            {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
            {{counter name="tabCount" print=false}}
            {{if $tabCount == '0'}}
            <li role="presentation" class="admin_tabs-item active">
                <a id="tab{{$tabCount}}" data-bs-toggle="tab" class="hidden-xs">
                    {sugar_translate label='{{$label}}' module='{{$module}}'}
                </a>
                <a id="xstab{{$tabCount}}" href="#" class="visible-xs first-tab dropdown-toggle" data-bs-toggle="dropdown">
                    {sugar_translate label='{{$label}}' module='{{$module}}'}
                </a>
            </li>
            {{else}}
            {* if panel skip *}
            {{/if}}
        {{/foreach}}
        {/if}
        {{/if}}
        {if $config.enable_action_menu and $config.enable_action_menu != false}
        <li id="tab-actions" class="dropdown">
            <a class="dropdown-toggle" data-bs-toggle="dropdown" href="#">{{$APP.LBL_LINK_ACTIONS}}<span class="suitepicon suitepicon-action-caret"></span></a>
            {{include file="themes/SuiteP/include/DetailView/actions_menu.tpl"}}
        </li>
        <li class="tab-inline-pagination">
            {{if $panelCount == 0}}
            {{* Render tag for VCR control if SHOW_VCR_CONTROL is true *}}
            {{if $SHOW_VCR_CONTROL}}
            {$PAGINATION}
            {{/if}}
            {{counter name="panelCount" print=false}}
            {{/if}}
        </li>
        {/if}
    </ul>
    {{counter name="tabCount" start=0 print=false assign="tabCount"}}
    {{if $useTabs}}
    {*<!-- TAB CONTENT USE TABS -->*}
    <div class="tab-content">
        {{else}}
        {*
           Since: SuieCRM 7.8
           When action menus are enabled and When there are only panels and there are not any tabs,
           make the first panel a tab so that the action menu looks correct. This is regardless of what the
           meta/studio defines the first panel should always be tab.
       *}
        {if $config.enable_action_menu and $config.enable_action_menu != false}
        {{if $tabCount == 0}}
        {*<!-- TAB CONTENT USE TABS -->*}
        <div class="tab-content">
            {{else}}
            {*<!-- TAB CONTENT DOESN'T USE TABS -->*}
            <div class="tab-content" style="padding: 0; border: 0;">
                {{/if}}
                {else}
                {*<!-- TAB CONTENT DOESN'T USE TABS -->*}
                <div class="tab-content" style="padding: 0; border: 0;">
                    {/if}
                    {{/if}}
                    {* Loop through all top level panels first *}
                    {{if $useTabs}}
                    {{foreach name=section from=$sectionPanels key=label item=panel}}
                    {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
                    {{if isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true}}
                    {{if $tabCount == '0'}}
                    <div class="tab-pane-NOBOOTSTRAPTOGGLER active show" id='tab-content-{{$tabCount}}'>
                        {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                    </div>
                    {{else}}
                    <div class="tab-pane-NOBOOTSTRAPTOGGLER" id='tab-content-{{$tabCount}}'>
                        {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                    </div>
                    {{/if}}
                    {{/if}}
                    {{counter name="tabCount" print=false}}
                    {{/foreach}}
                    {{else}}
                    {*
                       Since: SuieCRM 7.8
                       When action menus are enabled and When there are only panels and there are not any tabs,
                       make the first panel a tab so that the action menu looks correct. This is regardless of what the
                       meta/studio defines the first panel should always be tab.
                   *}
                    {if $config.enable_action_menu and $config.enable_action_menu != false}
                        {{foreach name=section from=$sectionPanels key=label item=panel}}
                        {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
                        {{if $tabCount == '0'}}
                        <div class="tab-pane-NOBOOTSTRAPTOGGLER active show" id='tab-content-{{$tabCount}}'>
                            {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                        </div>
                        {{else}}

                        {{/if}}
                    {{counter name="tabCount" print=false}}
                    {{/foreach}}
                    {else}
                    {*<!-- TAB CONTENT DOESN'T USE TABS -->*}
                    <div class="tab-pane-NOBOOTSTRAPTOGGLER panel-collapse"></div>
                    {/if}
                    {{/if}}
                </div>
                {*display panels*}
                <div class="panel-content">
                    {{counter name="tabCount" start=-1 print=false assign="tabCount"}}
                    {{counter name="panelCount" start=-1 print=false assign="panelCount"}}
                    {{foreach name=section from=$sectionPanels key=label item=panel}}

                    {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
                    {* if tab *}
                    {{if (isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true && $useTabs)}}
                    {{counter name="tabCount" print=false}}
                    {*if tab skip*}
                    {{else}}
                    {* if panel display*}
                    {*if panel collasped*}
                    {{if (isset($tabDefs[$label_upper].panelDefault) && $tabDefs[$label_upper].panelDefault == "collapsed") }}
                    {*collapse panel*}
                    {{assign var='collapse' value="panel-collapse collapse"}}
                    {{assign var='collapsed' value="collapsed"}}
                    {{assign var='collapseIcon' value="glyphicon glyphicon-plus"}}
                    {{assign var='panelHeadingCollapse' value="panel-heading-collapse"}}
                    {{else}}
                    {*expand panel*}
                    {{assign var='collapse' value="panel-collapse collapse show"}}
                    {{assign var='collapseIcon' value="glyphicon glyphicon-minus"}}
                    {{assign var='panelHeadingCollapse' value=""}}
                    {{/if}}
                    {{if $label != "LBL_AOP_CASE_UPDATES"}}
                    {{assign var='panelId' value="top-panel-$panelCount"}}
                    {{else}}
                    {{assign var='panelId' value="LBL_AOP_CASE_UPDATES"}}
                    {{/if}}

                    {*
                       Since: SuieCRM 7.8
                       When action menus are enabled and When there are only panels and there are not any tabs,
                       make the first panel a tab so that the action menu looks correct. This is regardless of what the
                       meta/studio defines the first panel should always be tab.
                    *}
                    {if $config.enable_action_menu and $config.enable_action_menu != false}
                        {{if $panelCount == -1}}
                        {* skip panel as it has been converted to a tab*}
                        {{else}}
                        {* display panels as they have always been displayed *}
                        {{if $useTabs}}
                            {{if $tabCount == 0}}
                                <div class="panel panel-default tab-panel-{{$tabCount}}" style="display: block;">
                            {{else}}
                                <div class="panel panel-default tab-panel-{{$tabCount}}" style="display: none;">
                            {{/if}}
                        {{else}}
                            <div class="panel panel-default">
                        {{/if}}
                            <div class="panel-heading {{$panelHeadingCollapse}}">
                                <a class="row g-0 panel-heading-collapsed {{$collapsed}}"  href="#{{$panelId}}" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="{{$panelId}}">
                                    <div class="col-xs-12 col-sm-12 col-md-12 d-flex justify-content-between">
                                        <span class="panel-heading-text w-100">{sugar_translate label='{{$label}}' module='{{$module}}'}</span>
                                        <span class="panel-heading-icon">
                                            <!-- <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"></path></svg> -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"/></svg>
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <div class="panel-body {{$collapse}} panelContainer" id="{{$panelId}}"  data-id="{{$label_upper}}">
                                <div class="tab-content">
                                    <!-- TAB CONTENT -->
                                    {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                                </div>
                            </div>
                        </div>
                        {{/if}}
                    {else}
                    {* display panels as they have always been displayed *}
                    {{if $useTabs}}
                            {{if $tabCount == 0}}
                                <div class="panel panel-default tab-panel-{{$tabCount}}" style="display: block;">
                            {{else}}
                                <div class="panel panel-default tab-panel-{{$tabCount}}" style="display: none;">
                            {{/if}}
                        {{else}}
                            <div class="panel panel-default">
                        {{/if}}
                        <div class="panel-heading {{$panelHeadingCollapse}}">
               
                            <a class="row g-0 panel-heading-collapsed {{$collapsed}}"  href="#{{$panelId}}" role="button" data-bs-toggle="collapse" aria-expanded="false">
                                <div class="col-xs-12 col-sm-12 col-md-12 d-flex justify-content-between">
                                    <span class="panel-heading-text w-100">{sugar_translate label='{{$label}}' module='{{$module}}'}</span>
                                    <span class="panel-heading-icon">
                                        <!-- <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"></path></svg> -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"/></svg>
                                    </span>
                                </div>
                            </a>

                        </div>
                        <div class="panel-body {{$collapse}} panelContainer" id="{{$panelId}}" data-id="{{$label_upper}}">
                            <div class="tab-content">
                                <!-- TAB CONTENT -->
                                {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                            </div>
                        </div>
                    </div>
                    {/if}


                    {{/if}}
                    {{counter name="panelCount" print=false}}
                    {{/foreach}}
                </div>
            </div>

            {{include file=$footerTpl}}
            <script type="text/javascript" src="include/InlineEditing/inlineEditing.js"></script>

            <!-- OFF -->
            <!-- <script type="text/javascript" src="modules/Favorites/favorites.js"></script> -->

            {literal}

                <script type="text/javascript">

                    let selectTabDetailView = function(tab) {
                        $('#content div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').hide();
                        $('#content div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').eq(tab).show().addClass('active');
                        $('#content div.detail-view div.panel-content div.panel.panel').hide();
                        $('#content div.panel-content div.panel.tab-panel-' + tab).show();
                    };

                    var selectTabOnError = function(tab) {
                        selectTabDetailView(tab);
                        $('#content ul.nav.nav-tabs > li').removeClass('active');
                        $('#content ul.nav.nav-tabs > li a').css('color', '');

                        $('#content ul.nav.nav-tabs > li').eq(tab).find('a').first().css('color', 'red');
                        $('#content ul.nav.nav-tabs > li').eq(tab).addClass('active');

                    };

                    var selectTabOnErrorInputHandle = function(inputHandle) {
                        var tab = $(inputHandle).closest('.tab-pane-NOBOOTSTRAPTOGGLER').attr('id').match(/^detailpanel_(.*)$/)[1];
                        selectTabOnError(tab);
                    };


                    $(function(){
                        $('#content ul.nav.nav-tabs > li > a[data-bs-toggle="tab"]').click(function(e){
                            if(typeof $(this).parent().find('a').first().attr('id') != 'undefined') {
                                var tab = parseInt($(this).parent().find('a').first().attr('id').match(/^tab(?<number>(.)*)$/)[1]);
                                selectTabDetailView(tab);
                            }
                        });
                    });

                    // check collapsed panel heading
                    $('.panel-heading-collapsed').on('click', function(){
                        if($(this).hasClass('collapsed')){
                            $(this).find('.panel-heading-icon').html('<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/></svg>');
                        } else {
                            $(this).find('.panel-heading-icon').html('<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"/></svg>');
                        }
                    })
                    
                </script>

            {/literal}

