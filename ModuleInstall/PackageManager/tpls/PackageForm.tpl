{if $ERR_SUHOSIN == true}
{$APP_STRINGS.ERR_SUHOSIN}
{else}
{$scripts}
{$TREEHEADER}
{literal}

<style type="text/css">
#demo { width:100%; }
#demo .yui-content {
    padding:1em; /* pad content container */
}
.list {list-style:square;width:500px;padding-left:16px;}
.list li{padding:2px;font-size:8pt;}

/* hide the tab content while loading */
.tab-content{display:none;}

pre {
   font-size:11px;
}

#tabs1 {width:100%;}
#tabs1 .yui-ext-tabbody {border:1px solid #999;border-top:none;}
#tabs1 .yui-ext-tabitembody {display:none;padding:10px;}

/* default loading indicator for ajax calls */
.loading-indicator {
	font-size:8pt;
	background-image:url('../../resources/images/grid/loading.gif');
	background-repeat: no-repeat;
	background-position: left;
	padding-left:20px;
}
</style>
{/literal}
{$INSTALLED_PACKAGES_HOLDER}

<form action='{$form_action}' method="post" name="installForm">
<input type=hidden name="release_id">
{$hidden_fields}
<div id='server_upload_div'>
{$FORM_2_PLACE_HOLDER}
{$MODULE_SELECTOR}
<div id='search_results_div'></div>
</div>
</form>
<div id='local_upload_div'>
{$FORM_1_PLACE_HOLDER}
</div>

{if $module_load == 'true'}
<div id='upload_table' class="box-section">
<table width='100%' class="table-download"><tr><td><div id='patch_downloads' class='ygrid-mso'></div></td></tr></table>
</div>

{literal}<script>
//PackageManager.toggleView('browse');
</script>
{/literal}
{/if}
{/if}
