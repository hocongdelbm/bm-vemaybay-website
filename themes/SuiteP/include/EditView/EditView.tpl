{{sugar_include type="smarty" file=$headerTpl}}
{sugar_include include=$includes}
<div id="EditView_tabs" class="mt-3">
    {*display tabs*}
    {{counter name="tabCount" start=-1 print=false assign="tabCount"}}
    <ul class="nav-tabs tabs-list admin_tabs-list">
        {{if $useTabs}}
        {{foreach name=section from=$sectionPanels key=label item=panel}}
            {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
            {* if tab *}
            {{if (isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true)}}
                {*if tab display*}
                {{counter name="tabCount" print=false}}
                {{if $tabCount == '0'}}
                    <li role="presentation" class="admin_tabs-item">
                        <a id="tab{{$tabCount}}" data-bs-toggle="tab" class="active hidden-xs">{sugar_translate label='{{$label}}' module='{{$module}}'}</a>
                        {* Count Tabs *}
                        {{counter name="tabCountOnlyXS" start=-1 print=false assign="tabCountOnlyXS"}}
                        {{foreach name=sectionOnlyXS from=$sectionPanels key=labelOnly item=panelOnlyXS}}
                            {{capture name=label_upper_count_only assign=label_upper_count_only}}{{$labelOnly|upper}}{{/capture}}
                            {{if (isset($tabDefs[$label_upper_count_only].newTab) && $tabDefs[$label_upper_count_only].newTab == true)}}
                                {{counter name="tabCountOnlyXS" print=false}}
                            {{/if}}
                        {{/foreach}}

                        {* For the mobile view, only show the first tab has a drop down when:
                            * There is more than one tab set
                            * When Acton Menu's are enabled *}
                        <!-- Counting Tabs {{$tabCountOnlyXS}}-->
                        <a id="xstab{{$tabCount}}" href="#" class="visible-xs first-tab{{if $tabCountOnlyXS > 0}}-xs{{/if}} dropdown-toggle" data-bs-toggle="dropdown">{sugar_translate label='{{$label}}' module='{{$module}}'}</a>
                        {{if $tabCountOnlyXS > 0}}
                            <ul id="first-tab-menu-xs" class="dropdown-menu">
                                {{counter name="tabCountXS" start=0 print=false assign="tabCountXS"}}
                                {{foreach name=sectionXS from=$sectionPanels key=label item=panelXS}}
                                    {{capture name=label_upper_xs assign=label_upper_xs}}{{$label|upper}}{{/capture}}
                                    {{if (isset($tabDefs[$label_upper_xs].newTab) && $tabDefs[$label_upper_xs].newTab == true)}}
                                        <li role="presentation">
                                            <a id="tab{{$tabCountXS}}" data-bs-toggle="tab" onclick="changeFirstTab(this, 'tab-content-{{$tabCountXS}}');">{sugar_translate label='{{$label}}' module='{{$module}}'}</a>
                                        </li>
                                        {{counter name="tabCountXS" print=false}}
                                    {{/if}}
                                {{/foreach}}
                            </ul>
                        {{/if}}
                    </li>
                {{else}}
                    <li role="presentation" class="admin_tabs-item hidden-xs">
                        <a id="tab{{$tabCount}}"  data-bs-toggle="tab">{sugar_translate label='{{$label}}' module='{{$module}}'}</a>
                    </li>
                {{/if}}
            {{else}}
                    {* if panel skip*}
            {{/if}}
        {{/foreach}}
        {{/if}}
    </ul>

    <div class="tab-content">
        {{counter name="tabCount" start=0 print=false assign="tabCount"}}
        {* Loop through all top level panels first *}
        {{if $useTabs}}
            {{foreach name=section from=$sectionPanels key=label item=panel}}
                {{capture name=label_upper assign=label_upper}}{{$label|upper}}{{/capture}}
                {{if isset($tabDefs[$label_upper].newTab) && $tabDefs[$label_upper].newTab == true}}
                    {{if $tabCount == '0'}}
                        <div class="tab-pane-NOBOOTSTRAPTOGGLER active show box-tabs" id='tab-content-{{$tabCount}}'>
                            {{include file='themes/SuiteP/include/EditView/tab_panel_content.tpl'}}
                        </div>
                    {{else}}
                        <div class="tab-pane-NOBOOTSTRAPTOGGLER box-tabs" id='tab-content-{{$tabCount}}'>
                            {{include file='themes/SuiteP/include/EditView/tab_panel_content.tpl'}}
                        </div>
                    {{/if}}
                    {{counter name="tabCount" print=false}}
                {{/if}}
            {{/foreach}}
        {{else}}
            <div class="tab-pane panel-collapse"></div>
        {{/if}}
    </div> <!-- End tab-content -->

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
                {* if panel display && if panel collasped*}
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
                            <button class="row g-0 panel-heading-collapsed {{$collapsed}}" type="button" data-bs-toggle="collapse" data-bs-target="#detailpanel_{{$panelCount}}" aria-expanded="false">
                                <div class="col-xs-12 col-sm-12 col-md-12 d-flex justify-content-between">
                                    <span class="panel-heading-text w-100">{sugar_translate label='{{$label}}' module='{{$module}}'}</span>
                                    <span class="panel-heading-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"/></svg>
                                    </span>
                                </div>
                            </button>
                        </div>
                        <div class="panel-body {{$collapse}} panelContainer" id="detailpanel_{{$panelCount}}" data-id="{{$label_upper}}">
                            <div class="tab-content">{{include file='themes/SuiteP/include/EditView/tab_panel_content.tpl'}}</div>
                        </div>
                    </div><!-- end panel panel-default -->
            {{/if}}
            {{counter name="panelCount" print=false}}
        {{/foreach}}
    </div> <!-- end panel-content -->
</div> <!-- end EditView_tabs-->

{{sugar_include type='smarty' file=$footerTpl}}
{{if $useTabs}}
    {sugar_getscript file="cache/include/javascript/sugar_grp_yui_widgets.js"}
    <script type="text/javascript">
        var {{$form_name}}_tabs = new YAHOO.widget.TabView("{{$form_name}}_tabs");
        {{$form_name}}_tabs.selectTab(0);
    </script>
{{/if}}

<script type="text/javascript">
    YAHOO.util.Event.onContentReady("{{$form_name}}", function () {ldelim} initEditView(document.forms.{{$form_name}}) {rdelim});
    //window.setTimeout(, 100);
    {{if $module == "Users"}}
        window.onbeforeunload = function () {ldelim} return disableOnUnloadEditView(); {rdelim};
    {{else}}
        window.onbeforeunload = function () {ldelim} return onUnloadEditView(); {rdelim};
    {{/if}}
    // bug 55468 -- IE is too aggressive with onUnload event
    if ($.browser.msie) {ldelim}
    $(document).ready(function() {ldelim}
        $(".collapseLink,.expandLink").click(function (e) {ldelim} e.preventDefault(); {rdelim});
    {rdelim});
    {rdelim}
</script>

{literal}
    <script type="text/javascript">
        var selectTab = function(tab) {
            $('#EditView_tabs div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').hide();
            $('#EditView_tabs div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').eq(tab).show().addClass('active').addClass('show');
            $('#EditView_tabs div.panel-content div.panel').hide();
            $('#EditView_tabs div.panel-content div.panel.tab-panel-' + tab).show()
        };
        var selectTabOnError = function(tab) {
            selectTab(tab);
            $('#EditView_tabs ul.nav.nav-tabs li').removeClass('active');
            $('#EditView_tabs ul.nav.nav-tabs li a').css('color', '');

            $('#EditView_tabs ul.nav.nav-tabs li').eq(tab).find('a').first().css('color', 'red');
            $('#EditView_tabs ul.nav.nav-tabs li').eq(tab).addClass('active');
        };

        var selectTabOnErrorInputHandle = function(inputHandle) {
            var tab = $(inputHandle).closest('.tab-pane-NOBOOTSTRAPTOGGLER').attr('id').match(/^tab-content-(.*)$/)[1]; 
            selectTabOnError(tab);
        };


        $(function(){
            $('#EditView_tabs ul.nav.nav-tabs li > a[data-bs-toggle="tab"]').click(function(e){
                if(typeof $(this).parent().find('a').first().attr('id') != 'undefined') {
                    var tab = parseInt($(this).parent().find('a').first().attr('id').match(/^tab(?<number>(.)*)$/)[1]);
                    selectTab(tab);
                }
            });

            $('a[data-bs-toggle="collapse"]').click(function(e){
                if($(this).hasClass('collapsed')) {
                // Expand panel
                    // Change style of .panel-header
                    $(this).removeClass('collapsed');
                    // Expand .panel-body
                    $(this).parents('.panel').find('.panel-body').removeClass('show').addClass('show');
                } else {
                // Collapse panel
                    // Change style of .panel-header
                    $(this).addClass('collapsed');
                    // Collapse .panel-body
                    $(this).parents('.panel').find('.panel-body').removeClass('show').removeClass('show');
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
