{{sugar_include type="smarty" file=$headerTpl}}
{sugar_include include=$includes}
<form action="index.php" method="post" name="DetailView" id="formDetailView">
    <input type="hidden" name="module" value="{$module}">
    <input type="hidden" name="record" value="{$fields.id.value}">
    <input type="hidden" name="return_action">
    <input type="hidden" name="return_module">
    <input type="hidden" name="return_id">
    <input type="hidden" name="module_tab">
    <input type="hidden" name="isDuplicate" value="false">
    <input type="hidden" name="offset" value="{$offset}">
    <input type="hidden" name="action" value="EditView">
    <input type="hidden" name="sugar_body_only">
</form>
<div class="detail-view mt-3">
    {*display tabs*}
    {{counter name="tabCount" start=-1 print=false assign="tabCount"}}
    <ul class="nav nav-tabs admin_tabs-list" role="tablist">
        {{if $useTabs}}
            {{foreach name=section from=$sectionPanels key=label item=panel}}
                {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
                {* if tab *}
                {{if (isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true)}}
                    {*if tab display*}
                    {{counter name="tabCount" print=false}}
                    {{if $tabCount == '0'}}
                        <li role="presentation" class="admin_tabs-item">
                            <a id="tab{{$tabCount}}" data-bs-toggle="tab" class="active hidden-xs" aria-selected="true">
                                {sugar_translate label='{{$label}}' module='{{$module}}'}
                            </a>
                            <a id="xstab{{$tabCount}}" href="#" class="visible-xs first-tab-xs dropdown-toggle" data-bs-toggle="dropdown">
                                {sugar_translate label='{{$label}}' module='{{$module}}'}
                            </a>
                            <ul id="first-tab-menu-xs" class="dropdown-menu">
                        {{counter name="tabCountXS" start=-1 print=false assign="tabCountXS"}}
                        {{foreach name=sectionXS from=$sectionPanels key=label item=panelXS}}
                            {{counter name="tabCountXS" print=false}}
                            <li role="presentation" class="admin_tabs-item">
                                <a id="tab{{$tabCountXS}}" data-bs-toggle="tab" aria-selected="false" onclick="changeFirstTab(this, 'tab-content-{{$tabCountXS}}');">
                                    {sugar_translate label='{{$label}}' module='{{$module}}'}
                                </a>
                            </li>
                        {{/foreach}}
                        {{counter name="tabCountXS" print=false}}
                        <li role="presentation" class="admin_tabs-item">
                            <a data-bs-toggle="tab" id="tab{{$tabCount}}" aria-selected="false" href="#">{{$MOD.LBL_ADVANCED}}</a>
                        </li>
                        {if $SHOW_ROLES == true}
                            {{counter name="tabCountXS" print=false}}
                            <li role="presentation" class="admin_tabs-item">
                                <a data-bs-toggle="tab" id="tab{{$tabCount}}" aria-selected="false" href="#">{{$MOD.LBL_USER_ACCESS}}</a>
                            </li>
                        {/if}
                        </ul>
                    </li>
                    {{else}}
                    <li role="presentation" class="admin_tabs-item hidden-xs">
                        <a id="tab{{$tabCount}}" data-bs-toggle="tab" aria-selected="false">
                            {sugar_translate label='{{$label}}' module='{{$module}}'}
                        </a>
                    </li>
                    {{/if}}
                {{else}}
                    {* if panel skip*}
                {{/if}}
            {{/foreach}}
        {{/if}}
        {{counter name="tabCount" print=false}}
        <li role="presentation" class="admin_tabs-item hidden-xs ">
            <a data-bs-toggle="tab" id="tab{{$tabCount}}" aria-selected="false" href="#">{{$MOD.LBL_ADVANCED}}</a>
        </li>
        {if $SHOW_ROLES}
            {{counter name="tabCount" print=false}}
            <li role="presentation" class="admin_tabs-item hidden-xs ">
                <a data-bs-toggle="tab" id="tab{{$tabCount}}" aria-selected="false" href="#">{{$MOD.LBL_USER_ACCESS}}</a>
            </li>
        {/if}
        {if $config.enable_action_menu}
            <li id="tab-actions" class="admin_tabs-item dropdown">
                <a class="dropdown-toggle" aria-selected="false" data-bs-toggle="dropdown" href="#">{{$APP.LBL_LINK_ACTIONS}}</a>
                {{include file="themes/SuiteP/include/DetailView/actions_menu.tpl"}}
            </li>
        {/if}
    </ul>

    {{if $useTabs}}
        <!-- TAB CONTENT USE TABS -->
        <div class="tab-content">
    {{else}}
        <!-- TAB CONTENT DOESN'T USE TABS -->
        <div class="tab-content" style="padding: 0; border: 0;">
         {{/if}}
            {* Loop through all top level panels first *}
            {{counter name="tabCount" start=0 print=false assign="tabCount"}}
            {{if $useTabs}}
                {{foreach name=section from=$sectionPanels key=label item=panel}}
                    {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
                    {{if isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true}}
                        {{if $tabCount == '0'}}
                            <div class="tab-pane-NOBOOTSTRAPTOGGLER active show box-tabs tab-info-profile" id='tab-content-{{$tabCount}}'>
                                {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                            </div>
                        {{else}}
                            <div class="tab-pane-NOBOOTSTRAPTOGGLER box-tabs tab-info-profile" id='tab-content-{{$tabCount}}'>
                                {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                            </div>
                        {{/if}}
                    {{/if}}
                    {{counter name="tabCount" print=false}}
                {{/foreach}}
                {* advanced users tab *}
                <div class="tab-pane-NOBOOTSTRAPTOGGLER box-tabs tab-advanced" id='tab-content-{{$tabCount}}' >
                    {{include file='themes/SuiteP/modules/Users/tpls/DetailView-advanced-tab-content.tpl'}}
                </div>
                {if $SHOW_ROLES}
                    {{counter name="tabCount" print=false}}
                    {* access users tab (ACL ROLE matrix) *}
                    <div class="tab-pane-NOBOOTSTRAPTOGGLER box-tabs tab-access" id='tab-content-{{$tabCount}}'>
                        <div class="row detail-view-row g-0">
                            {$ROLE_HTML}
                        </div>
                    </div>
                {/if}
            {{else}}
                <div class="tab-pane-NOBOOTSTRAPTOGGLER box-tabs panel-collapse"></div>
            {{/if}}
            </div>
                {*display panels*}
            <div class="panel-content">
                {{counter name="panelCount" start=-1 print=false assign="panelCount"}}
                {{foreach name=section from=$sectionPanels key=label item=panel}}
                    {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
                    {* if tab *}
                    {{if (isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true && $useTabs)}}
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
                        <div class="panel panel-default">
                        <div class="panel-heading {{$panelHeadingCollapse}}">

                            <button class="row g-0 panel-heading-collapsed {{$collapsed}}" type="button" data-bs-toggle="collapse" data-bs-target="#{{$panelId}}" aria-expanded="false">
                                <div class="col-xs-12 col-sm-12 col-md-12 d-flex justify-content-between">
                                    <span class="panel-heading-text w-100">{sugar_translate label='{{$label}}' module='{{$module}}'}</span>
                                    <span class="panel-heading-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"></path></svg>
                                    </span>
                                </div>
                            </button>
                        </div>
                        <div class="panel-body {{$collapse}} show" id="{{$panelId}}">
                            <div class="tab-content">
                                <!-- TAB CONTENT -->
                                {{include file='themes/SuiteP/include/DetailView/tab_panel_content.tpl'}}
                            </div>
                        </div>
                    </div>

                    {{/if}}
                    {{counter name="panelCount" print=false}}
                {{/foreach}}
                </div>
            </div>

            {{include file=$footerTpl}}
            {*{{if $useTabs}}*}
            {*<script type='text/javascript' src='{sugar_getjspath file='include/javascript/popup_helper.js'}'></script>*}
            {*<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui_widgets.js'}"></script>*}
            {*<script type="text/javascript">*}
            {*var {{$module}}_detailview_tabs = new YAHOO.widget.TabView("{{$module}}_detailview_tabs");*}
            {*{{$module}}_detailview_tabs.selectTab(0);*}
            {*</script>*}
            {*{{/if}}*}
            <script type="text/javascript" src="include/InlineEditing/inlineEditing.js"></script>
            <script type='text/javascript' src='{sugar_getjspath file='modules/Users/DetailView.js'}'></script>

            {*<script type="text/javascript" src="modules/Favorites/favorites.js"></script>*}

            {literal}

                <script type="text/javascript">

                    var selectTab = function(tab) {
                        $('#content div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').hide();
                        $('#content div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').eq(tab).show().addClass('active');
                    };

                    var selectTabOnError = function(tab) {
                        selectTab(tab);
                        $('#content ul.nav.nav-tabs li').removeClass('active');
                        $('#content ul.nav.nav-tabs li a').css('color', '');

                        $('#content ul.nav.nav-tabs li').eq(tab).find('a').first().css('color', 'red');
                        $('#content ul.nav.nav-tabs li').eq(tab).addClass('active');

                    };

                    var selectTabOnErrorInputHandle = function(inputHandle) {
                        var tab = $(inputHandle).closest('.tab-pane-NOBOOTSTRAPTOGGLER').attr('id').match(/^detailpanel_(.*)$/)[1];
                        selectTabOnError(tab);
                    };


                    $(function(){
                        $('#content ul.nav.nav-tabs li').click(function(e){
                            if(typeof $(this).find('a').first().attr('id') != 'undefined') {
                                var tab = parseInt($(this).find('a').first().attr('id').match(/^tab(.)*$/)[1]);
                                selectTab(tab);
                            }
                        });
                        $('#content ul.nav.nav-tabs li.active').each(function(e){
                            if(typeof $(this).find('a').first().attr('id') != 'undefined') {
                                var tab = parseInt($(this).find('a').first().attr('id').match(/^tab(.)*$/)[1]);
                                selectTab(tab);
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
