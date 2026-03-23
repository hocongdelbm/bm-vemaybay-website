<script type="text/javascript">
var numPages = {$numPages};
var theme = '{$theme}';
var loadedPages = new Array();
var activePage = {$activePage};
loadedPages[0] = '{$loadedPage}';
current_user_id = '{$current_user}';
jsChartsArray = new Array();
var moduleName = 'Home';
</script>

<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui_widgets.js'}"></script>
<link rel='stylesheet' href='{sugar_getjspath file='include/javascript/yui/build/assets/skins/sam/skin.css'}'>

<!-- CSS Files -->
<link type="text/css" href="{sugar_getjspath file='custom/include/SugarCharts/js/Jit/Examples/css/base.css'}" rel="stylesheet" />
<link type="text/css" href="{sugar_getjspath file='custom/include/SugarCharts/js/Jit/css/Examples/BarChart.css'}" rel="stylesheet" />
<script language="javascript" type="text/javascript" src="{sugar_getjspath file='custom/include/SugarCharts/js/Jit/jit.js'}"></script>

{if !$lock_homepage}
{literal}
<script type="text/javascript">
SUGAR.mySugar.maxCount = 	{/literal}{$maxCount}{literal};
SUGAR.mySugar.homepage_dd = new Array();
SUGAR.mySugar.init = function () {
	j = 0;
	{/literal}
	dashletIds = {$dashletIds};
	{literal}
	for(i in dashletIds) {
		SUGAR.mySugar.homepage_dd[j] = new ygDDList('dashlet_' + dashletIds[i]);
		SUGAR.mySugar.homepage_dd[j].setHandleElId('dashlet_header_' + dashletIds[i]);
		SUGAR.mySugar.homepage_dd[j].onMouseDown = SUGAR.mySugar.onDrag;
		SUGAR.mySugar.homepage_dd[j].afterEndDrag = SUGAR.mySugar.onDrop;
		j++;
	}
	{/literal}
	{if $hiddenCounter > 0}
	for(var wp = 0; wp <= {$hiddenCounter}; wp++) {ldelim}
	    SUGAR.mySugar.homepage_dd[j++] = new ygDDListBoundary('page_'+activePage+'_hidden' + wp);
	{rdelim}
	{/if}
	{literal}

	YAHOO.util.DDM.mode = 1;
}

YAHOO.util.Event.addListener(window, 'load', SUGAR.mySugar.init);

</script>
{/literal}
{/if}