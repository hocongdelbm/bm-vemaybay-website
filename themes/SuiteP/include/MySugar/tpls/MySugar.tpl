{sugar_getscript file="cache/include/javascript/sugar_grp_yui_widgets.js"}
{sugar_getscript file='include/javascript/dashlets.js'}

{$chartResources}
{$mySugarChartResources}
<div class="dashboard">
    {*display tabs*}
    <ul class="nav tabs-list admin_tabs-list nav-dashboard">

        {foreach from=$dashboardPages key=tabNum item=tab}
            {if $tabNum == 0}
                <li role="presentation" class="admin_tabs-item active">
                    <a id="tab{$tabNum}" href="#tab_content_{$tabNum}" data-bs-toggle="tab" {if !$lock_homepage}ondblclick="renameTab({$tabNum})"{/if} onClick="retrievePage({$tabNum});" class="active hidden-xs">
                        {$dashboardPages.$tabNum.pageTitle}
                    </a>

                    <a id="xstab{$tabNum}" href="#" class="visible-xs first-tab-xs dropdown-toggle" data-bs-toggle="dropdown">
                        {$dashboardPages.$tabNum.pageTitle}
                        <span class="suitepicon suitepicon-action-caret"></span>
                    </a>
                    <ul id="first-tab-menu-xs" class="dropdown-menu">
                        {counter name="tabCountXS" start=-1 print=false assign="tabCountXS"}
                        {foreach from=$dashboardPages key=ta item=xstab}
                            {counter name="tabCountXS" print=false}
                            <li role="presentation">
                                <a id="tabxs{$tabCountXS}" href="#tab_content_{$tabCountXS}" data-bs-toggle="tab"  onClick="retrievePage({$tabCountXS});">
                                    {$dashboardPages.$tabCountXS.pageTitle}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </li>
            {else}
                <li role="presentation" class="admin_tabs-item">
                    <a id="tab{$tabNum}" href="#tab_content_{$tabNum}"  data-bs-toggle="tab"  {if !$lock_homepage}ondblclick="renameTab({$tabNum})"{/if} onClick="retrievePage({$tabNum});" class="hidden-xs">
                        {$dashboardPages.$tabNum.pageTitle}
                    </a>
                </li>
            {/if}
        {/foreach}

        {if !$lock_homepage}
            <li id="tab-actions" class="admin_tabs-item dropdown">
                <a class="dropdown-toggle" data-bs-toggle="dropdown" href="#">{$APP.LBL_LINK_ACTIONS}</a>
                {include file='themes/SuiteP/include/MySugar/tpls/actions_menu.tpl'}
            </li>
        {/if}
    </ul>
    <div class="tab-content">
        {foreach from=$dashboardPages key=tabNum item=tab}
            {if $tabNum == 0}
            <div class="tab-pane active fade show" id='tab_content_{$tabNum}'>
            {else}
            <div class="tab-pane fade" id='tab_content_{$tabNum}'>
            {/if}
                <img src="themes/SuiteP/images/loading.gif" width="48" height="48" align="baseline" border="0" alt="">
            </div>
        {/foreach}
    </div>
</div>
    <div class="modal fade modal-add-dashlet" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">{$lblAddDashlets}</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                          </svg>
                    </button>
                </div>
                <div class="modal-body" id="dashletsList">
                    <p><img src="themes/SuiteP/images/loading.gif" width="48" height="48" align="baseline" border="0"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">{$app.LBL_CLOSE_BUTTON_TITLE}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal fade modal-add-dashboard" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">{$lblAddTab}</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body" id="dashboardDialog">
                    <p><img src="themes/SuiteP/images/loading.gif" width="48" height="48" align="baseline" border="0" alt=""></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{$app.LBL_CANCEL_BUTTON_LABEL}</button>
                    <button type="button" class="btn btn-primary btn-add-dashboard" data-bs-dismiss="modal">{$lblAddTab}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


    <div class="modal fade modal-edit-dashboard" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">{$app.LBL_EDIT_TAB}</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                          </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p><img src="themes/SuiteP/images/loading.gif" width="48" height="48" align="baseline" border="0" alt=""></p>                </div>
                    <div class="container-fluid">
                        <div class="panel panel-default panel-template">
                            <div class="panel-heading">
                                    <div class="edit-dashboard-tabs d-flex gap-2 align-items-center">
                                        <span class="suitepicon suitepicon-mimetype-tab"></span>
                                        <span class="panel-title">Untitled</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">{$app.LBL_CLOSE_BUTTON_TITLE}</button></div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- fix errors in mysugar.js -->
    <div style="visibility: collapse">
        <div id="dashletsDialog"></div>
        <div id="dashletsDialog_c"></div>
    </div>

<script type="text/javascript" src="themes/SuiteP/include/MySugar/javascript/AddRemoveDashboardPages.js"></script>
<script type="text/javascript" src="themes/SuiteP/include/MySugar/javascript/retrievePage.js"></script>
<script type="text/javascript">

    var activePage = {$activePage};
    var theme = '{$theme}';
    current_user_id = '{$current_user}';
    jsChartsArray = new Array();
    var moduleName = '{$module}';
    document.body.setAttribute("class", "yui-skin-sam");
    {literal}
    var mySugarLoader = new YAHOO.util.YUILoader({
        require: ["my_sugar", "sugar_charts"],
        // Bug #48940 Skin always must be blank
        skin: {
            base: 'blank',
            defaultSkin: ''
        },
        onSuccess: function () {
            initMySugar();
            initmySugarCharts();
            SUGAR.mySugar.maxCount =    {/literal}{$maxCount}{literal};
            SUGAR.mySugar.homepage_dd = new Array();
            var j = 0;

            {/literal}
            var dashletIds = {$dashletIds};

            {if !$lock_homepage}
            for (i in dashletIds) {ldelim}
                SUGAR.mySugar.homepage_dd[j] = new ygDDList('dashlet_' + dashletIds[i]);
                SUGAR.mySugar.homepage_dd[j].setHandleElId('dashlet_header_' + dashletIds[i]);
                // Bug #47097 : Dashlets not displayed after moving them
                // add new property to save real id of dashlet, it needs to have ability reload dashlet by id
                SUGAR.mySugar.homepage_dd[j].dashletID = dashletIds[i];
                SUGAR.mySugar.homepage_dd[j].onMouseDown = SUGAR.mySugar.onDrag;
                SUGAR.mySugar.homepage_dd[j].afterEndDrag = SUGAR.mySugar.onDrop;
                j++;
                {rdelim}
            {if $hiddenCounter > 0}
            for (var wp = 0; wp <= {$hiddenCounter}; wp++) {ldelim}
                SUGAR.mySugar.homepage_dd[j++] = new ygDDListBoundary('page_' + activePage + '_hidden' + wp);
                {rdelim}
            {/if}
            YAHOO.util.DDM.mode = 1;
            {/if}
            {literal}
            SUGAR.mySugar.renderDashletsDialog();
            SUGAR.mySugar.sugarCharts.loadSugarCharts(activePage);
            {/literal}
            {literal}
        }
    });
    mySugarLoader.addModule({
        name: "my_sugar",
        type: "js",
        fullpath: {/literal}"{sugar_getjspath file='include/MySugar/javascript/MySugar.js'}"{literal},
        varName: "initMySugar",
        requires: []
    });
    mySugarLoader.addModule({
        name: "sugar_charts",
        type: "js",
        fullpath: {/literal}"{sugar_getjspath file="include/SugarCharts/Jit/js/mySugarCharts.js"}"{literal},
        varName: "initmySugarCharts",
        requires: []
    });
    mySugarLoader.insert();
    {/literal}
</script>
