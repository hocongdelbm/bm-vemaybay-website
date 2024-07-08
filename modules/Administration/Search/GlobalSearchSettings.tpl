<table width="100%" border="0" cellspacing="0" cellpadding="0" class="mt-4">
<tr>
<td colspan="100">

<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui_widgets.js'}"></script>
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='modules/Connectors/tpls/tabs.css'}"/>

<div class="search-module-selector">
	<div class="panel panel-primary">
		<div class="panel-heading">{$MOD.LBL_SEARCH_MODULES}</div>
		<div class="panel-body tab-content text-center">
			<p class="text-muted decs-section">{$MOD.LBL_SEARCH_MODULES_HELP}</p>
			<div class='add_table'>
				<table id="GlobalSearchSettings" class="GlobalSearchSettings table-edit table-config">
					<tr>
						<td width='1%'>
							<div id="enabled_div"></div>
						</td>
						<td>
							<div id="disabled_div"></div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
(function(){ldelim}
    var Connect = YAHOO.util.Connect;
	Connect.url = 'index.php';
    Connect.method = 'POST';
    Connect.timeout = 300000;
	var get = YAHOO.util.Dom.get;

	var enabled_modules = {$enabled_modules};
	var disabled_modules = {$disabled_modules};
	var lblEnabled = '{sugar_translate label="LBL_ACTIVE_MODULES"}';
	var lblDisabled = '{sugar_translate label="LBL_DISABLED_MODULES"}';
	{literal}
	SUGAR.globalSearchEnabledTable = new YAHOO.SUGAR.DragDropTable(
		"enabled_div",
		[{key:"label",  label: lblEnabled, width: 200, sortable: false},
		 {key:"module", label: lblEnabled, hidden:true}],
		new YAHOO.util.LocalDataSource(enabled_modules, {
			responseSchema: {fields : [{key : "module"}, {key : "label"}]}
		}),
		{height: "450px"}
	);
	SUGAR.globalSearchDisabledTable = new YAHOO.SUGAR.DragDropTable(
		"disabled_div",
		[{key:"label",  label: lblDisabled, width: 200, sortable: false},
		 {key:"module", label: lblDisabled, hidden:true}],
		new YAHOO.util.LocalDataSource(disabled_modules, {
			responseSchema: {fields : [{key : "module"}, {key : "label"}]}
		}),
		{height: "450px"}
	);

	SUGAR.globalSearchEnabledTable.disableEmptyRows = true;
	SUGAR.globalSearchDisabledTable.disableEmptyRows = true;
	SUGAR.globalSearchEnabledTable.addRow({module: "", label: ""});
	SUGAR.globalSearchDisabledTable.addRow({module: "", label: ""});
	SUGAR.globalSearchEnabledTable.render();
	SUGAR.globalSearchDisabledTable.render();

	SUGAR.getEnabledModules = function()
	{
		var enabledTable = SUGAR.globalSearchEnabledTable;
		var modules = "";
		for(var i=0; i < enabledTable.getRecordSet().getLength(); i++)
		{
			var data = enabledTable.getRecord(i).getData();
			if (data.module && data.module != '')
				modules += "," + data.module;
		}
		return modules;
	};

	SUGAR.saveGlobalSearchSettings = function()
	{
		var modules = SUGAR.getEnabledModules();
		modules = modules == "" ? modules : modules.substr(1);

		document.forms['SearchSettings'].elements['enabled_modules'].value = modules;
	}
})();
{/literal}
</script>
