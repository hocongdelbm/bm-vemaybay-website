{literal}
    <style>
        .menu {
            z-index: 100;
        }

        .subDmenu {
            z-index: 100;
        }

        div.moduleTitle {
            height: 10px;
        }
    </style>
{/literal}

{sugar_getscript file="cache/include/javascript/sugar_grp_yui_widgets.js"}
{sugar_getscript file='include/javascript/dashlets.js'}

<!-- Construct Dashlets -->
<div id="pageContainer" class="yui-skin-sam">
    <div class="row" id="pageNum_{$activePage}_div">
        <table width="100%" class="dashletTable">
            <tr>
                {counter assign=hiddenCounter start=0 print=false}
                {foreach from=$columns key=colNum item=data}
                    <td class="dashletcontainer" valign='top'>
                        <ul class='noBullet' id='col_{$activePage}_{$colNum}'>
                            <li id='page_{$activePage}_hidden{$hiddenCounter}b'
                                style='height: 5px; margin-top:12px;' class='noBullet'>
                                &nbsp;&nbsp;&nbsp;
                            </li>
                            {foreach from=$data.dashlets key=id item=dashlet}
                                <li class='noBullet' id='dashlet_{$id}'>
                                    <div id='dashlet_entire_{$id}' class='dashletPanel'>
                                        {$dashlet.script}
                                        {$dashlet.displayHeader}
                                        {$dashlet.display}
                                        {$dashlet.displayFooter}
                                    </div>
                                </li>
                            {/foreach}
                            <li id='page_{$activePage}_hidden{$hiddenCounter}' style='height: 5px'
                                class='noBullet'>&nbsp;&nbsp;&nbsp;</li>
                        </ul>
                    </td>
                    {counter}
                {/foreach}
            </tr>
        </table>
    </div>
</div>

<script type="text/javascript">
    var activePage = {$activePage};
    var colNum = {$colNum};
    var theme = '{$theme}';
    current_user_id = '{$current_user}';
    jsChartsArray = new Array();
    var moduleName = '{$module}';
    document.body.setAttribute("class", "yui-skin-sam");
    {literal}

    $(function(){
        var percent = Math.round(100/(colNum+1));
        $('#pageContainer #pageNum_'+activePage+'_div .dashletcontainer').addClass('col-'+percent);
    });

    var mySugarLoader = new YAHOO.util.YUILoader({
        require : ["my_sugar", "sugar_charts"],
        // Bug #48940 Skin always must be blank
        skin: {
            base: 'blank',
            defaultSkin: ''
        },
        onSuccess: function(){
            initMySugar();
            initmySugarCharts();
            SUGAR.mySugar.maxCount = 	{/literal}{$maxCount}{literal};
            SUGAR.mySugar.homepage_dd = new Array();
            var j = 0;

            {/literal}
            var dashletIds = {$dashletIds};

            {if !$lock_homepage}
            for(i in dashletIds) {ldelim}
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
            for(var wp = 0; wp <= {$hiddenCounter}; wp++) {ldelim}
                SUGAR.mySugar.homepage_dd[j++] = new ygDDListBoundary('page_'+activePage+'_hidden' + wp);
                {rdelim}
            {/if}
            YAHOO.util.DDM.mode = 1;
            {/if}
            {literal}
            SUGAR.mySugar.renderDashletsDialog();
            SUGAR.mySugar.sugarCharts.loadSugarCharts(activePage);

            resizeGraphs();
            {/literal}
            {literal}
        }
    });
    mySugarLoader.addModule({
        name :"my_sugar",
        type : "js",
        fullpath: {/literal}"{sugar_getjspath file='include/MySugar/javascript/MySugar.js'}"{literal},
        varName: "initMySugar",
        requires: []
    });
    mySugarLoader.addModule({
        name :"sugar_charts",
        type : "js",
        fullpath: {/literal}"{sugar_getjspath file="include/SugarCharts/Jit/js/mySugarCharts.js"}"{literal},
        varName: "initmySugarCharts",
        requires: []
    });
    mySugarLoader.insert();
    {/literal}
</script>
