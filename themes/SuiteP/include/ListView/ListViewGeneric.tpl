{sugar_include include=$includes}
{include file='include/ListView/ListViewColumnsFilterDialog.tpl'}
<script type='text/javascript' src='{sugar_getjspath file='include/javascript/popup_helper.js'}'></script>

<script>
{literal}
	$(document).ready(function(){
	    $("ul.clickMenu").each(function(index, node){
	  		$(node).sugarActionMenu();
	  	});

        $('.selectActionsDisabled').children().each(function(index) {
            $(this).attr('onclick','').unbind('click');
        });

        var selectedTopValue = $("#selectCountTop").attr("value");
        if(typeof(selectedTopValue) != "undefined" && selectedTopValue != "0"){
        	sugarListView.prototype.toggleSelected();
        }
	});
{/literal}
</script>

{assign var="currentModule" value = $pageData.bean.moduleDir}
{assign var="singularModule" value = $moduleListSingular.$currentModule}
{assign var="moduleName" value = $moduleList.$currentModule}
{assign var="hideTable" value=false}

{if $form.headerTpl}
    {sugar_include type="smarty" file=$form.headerTpl}
{/if}

{if count($data) == 0}
	{assign var="hideTable" value=true}
	<div class="list view listViewEmpty box-section">
        {if $showFilterIcon}
		<div class="filterContainer d-flex gap-2">
			{include file='include/ListView/ListViewSearchLink.tpl'}
		</div>
        {/if}
		{if $displayEmptyDataMesssages}
        	{if strlen($query) == 0}
                {capture assign="createLink"}<a class="btn btn-primary" href="?module={$pageData.bean.moduleDir}&action=EditView&return_module={$pageData.bean.moduleDir}&return_action=DetailView">{$APP.LBL_CREATE_BUTTON_LABEL}</a>{/capture}
                {capture assign="importLink"}<a class="btn btn-primary" href="?module=Import&action=Step1&import_module={$pageData.bean.moduleDir}&return_module={$pageData.bean.moduleDir}&return_action=index">{$APP.LBL_IMPORT}</a>{/capture}
                {capture assign="helpLink"}<a class="btn btn-info" target="_blank" href='?module=Administration&action=SupportPortal&view=documentation&version={$sugar_info.sugar_version}&edition={$sugar_info.sugar_flavor}&lang=&help_module={$currentModule}&help_action=&key='>{$APP.LBL_CLICK_HERE}</a>{/capture}

			{if !(isset($options.hide_edit_link) && $options.hide_edit_link === true) && !empty($quickViewLinks)}
				<p class="msg d-flex gap-2 align-items-center"> {$APP.MSG_EMPTY_LIST_VIEW_NO_RESULTS|replace:"<item2>":$createLink|replace:"<item3>":$importLink} </p>
			{/if}

        {elseif $query == "-advanced_search"}
            <p class="msg emptyResults">
                {$APP.MSG_LIST_VIEW_NO_RESULTS_CHANGE_CRITERIA}
            </p>
        {else}
            <p class="msg">
                {capture assign="quotedQuery"}"{$query}"{/capture}
                {$APP.MSG_LIST_VIEW_NO_RESULTS|replace:"<item1>":$quotedQuery}
            </p>
			{if $pageData}
            <p class="submsg">
                <a href="?module={$pageData.bean.moduleDir}&action=EditView&return_module={$pageData.bean.moduleDir}&return_action=DetailView">
                    {$APP.MSG_LIST_VIEW_NO_RESULTS_SUBMSG|replace:"<item1>":$quotedQuery|replace:"<item2>":$singularModule}
                </a>
            </p>
			{/if}
        {/if}
    {else}
        <p class="msg">
            {$APP.LBL_NO_DATA}
        </p>
	{/if}
	</div>
{/if}
{$multiSelectData}
{if $hideTable == false}
	<div class="list-view-rounded-corners list-view-generic__in-themes">
		<table cellpadding='0' cellspacing='0' border='0' class='list view table-responsive'>
	<thead>
		{assign var="link_select_id" value="selectLinkTop"}
		{assign var="link_action_id" value="actionLinkTop"}
		{assign var="actionsLink" value=$actionsLinkTop}
		{assign var="selectLink" value=$selectLinkTop}
		{assign var="action_menu_location" value="top"}

		<tr height='20'>
			{if $prerow}
				<th class="td_alt">&nbsp;</th>
			{/if}
			{counter start=0 name="colCounter" print=false assign="colCounter"}
            	{assign var='datahide' value="xs sm"}
			{foreach from=$displayColumns key=colHeader item=params}
                {if $colCounter == '3'}{assign var='datahide' value="xs sm"}{/if}
                {if $colCounter == '5'}{assign var='datahide' value="md"}{/if}
				{if $colCounter == '0'}
					{assign var='hide' value=""}
				{elseif $colHeader  == 'NAME' }
					{assign var='hide' value=""}
				{elseif $colCounter  > '10' }
					{assign var='hide' value="hidden-xs hidden-sm hidden-md"}
				{elseif $colCounter > '3' }
					{assign var='hide' value="hidden-xs hidden-sm"}
				{* {elseif $colCounter > '3'}
					{assign var='hide' value="hidden-xs"} *}
				{else}
					{assign var='hide' value=""}
				{/if}
                {if $colHeader == 'NAME' || $params.bold}
					<th scope='col' data-toggle="true" class="{$hide}">
				{else}
					<th scope='col' data-breakpoints="{$datahide}" class="{$hide}">
				{/if}
						{if $params.sortable|default:true}
							{if $params.url_sort}
								<a href='{$pageData.urls.orderBy}{$params.orderBy|default:$colHeader|lower}' class='listViewThLinkS1'>
							{else}
								{if $params.orderBy|default:$colHeader|lower == $pageData.ordering.orderBy}
									<a href='javascript:sListView.order_checks("{$pageData.ordering.sortOrder|default:ASCerror}", "{$params.orderBy|default:$colHeader|lower}" , "{$pageData.bean.moduleDir}{"2_"}{$pageData.bean.objectName|upper}{"_ORDER_BY"}")' class='listViewThLinkS1'>
								{else}
									<a href='javascript:sListView.order_checks("ASC", "{$params.orderBy|default:$colHeader|lower}" , "{$pageData.bean.moduleDir}{"2_"}{$pageData.bean.objectName|upper}{"_ORDER_BY"}")' class='listViewThLinkS1'>
								{/if}
							{/if}
							{if isset($params.hide_header_label) && $params.hide_header_label == true}
							{else}
							{sugar_translate label=$params.label module=$pageData.bean.moduleDir}
						 {/if}
							{if $params.orderBy|default:$colHeader|lower == $pageData.ordering.orderBy}
								{if $pageData.ordering.sortOrder == 'ASC'}
									{capture assign="imageName"}arrow_down.{$arrowExt}{/capture}
									{capture assign="alt_sort"}{sugar_translate label='LBL_ALT_SORT_DESC'}{/capture}
									<span class="d-none suitepicon suitepicon-action-sorting-ascending" title="{$alt_sort}"></span>
								{else}
									{capture assign="imageName"}arrow_up.{$arrowExt}{/capture}
									{capture assign="alt_sort"}{sugar_translate label='LBL_ALT_SORT_ASC'}{/capture}
									<span class="d-none suitepicon suitepicon-action-sorting-descending" title="{$alt_sort}"></span>
								{/if}
							{else}
								{capture assign="imageName"}arrow.{$arrowExt}{/capture}
								{capture assign="alt_sort"}{sugar_translate label='LBL_ALT_SORT'}{/capture}
								<span class="d-none suitepicon suitepicon-action-sorting-none" title="{$alt_sort}"></span>
							{/if}
							</a>
						{else}
							{if !isset($params.noHeader) || $params.noHeader == false}
                                {if isset($params.hide_header_label) && $params.hide_header_label == true}
                                {else}
                                    {sugar_translate label=$params.label module=$pageData.bean.moduleDir}
									{if $params.orderBy|default:$colHeader|lower == $pageData.ordering.orderBy && $params.force_show_sort_direction}
										{if $pageData.ordering.sortOrder == 'ASC'}
											{capture assign="imageName"}arrow_down.{$arrowExt}{/capture}
											{capture assign="alt_sort"}{sugar_translate label='LBL_ALT_SORT_DESC'}{/capture}
											<span class="d-none suitepicon suitepicon-action-sorting-ascending" title="{$alt_sort}"></span>
										{else}
											{capture assign="imageName"}arrow_up.{$arrowExt}{/capture}
											{capture assign="alt_sort"}{sugar_translate label='LBL_ALT_SORT_ASC'}{/capture}
											<span class="d-none suitepicon suitepicon-action-sorting-descending" title="{$alt_sort}"></span>
										{/if}
									{/if}
							&nbsp;  {/if}
							{/if}
						{/if}
					</th>
				{counter name="colCounter"}
			{/foreach}
			{if !(isset($options.hide_edit_link) && $options.hide_edit_link === true) && !empty($quickViewLinks)}
				<th class='td_alt quick_view_links'>&nbsp;</th>
			{/if}
			{* add extra column for icons*}
			<th class="hide-mobile">{$pageData.additionalDetails.$id}</th>
		</tr>
		{include file='themes/SuiteP/include/ListView/ListViewPaginationTop.tpl'}
	</thead>
	<tbody>
		{counter start=$pageData.offsets.current print=false assign="offset" name="offset"}
		{foreach name=rowIteration from=$data key=id item=rowData}
		    {counter name="offset" print=false}
	        {assign var='scope_row' value=true}

			{if $smarty.foreach.rowIteration.iteration is odd}
				{assign var='_rowColor' value=$rowColor[0]}
			{else}
				{assign var='_rowColor' value=$rowColor[1]}
			{/if}
			<tr height='20' class='{$_rowColor}S1'>
				{if $prerow}
				<td>
				 {if !$is_admin && $is_admin_for_user && $rowData.IS_ADMIN==1}
					<input type='checkbox' disabled="disabled" class='listview-checkbox' value='{$rowData.ID}'>
				 {else}
	                    <input title="{sugar_translate label='LBL_SELECT_THIS_ROW_TITLE'}" onclick='sListView.check_item(this, document.MassUpdate)' type='checkbox' class='listview-checkbox' name='mass[]' value='{$rowData.ID}'>
				 {/if}
				</td>
				{/if}
		
				{counter start=0 name="colCounter" print=false assign="colCounter"}
				{foreach from=$displayColumns key=col item=params}
					{if $colCounter == '0'}
						{assign var='hide' value=""}
					{elseif $col  == 'NAME' }
						{assign var='hide' value=""}
					{elseif $colCounter  > '10' }
						{assign var='hide' value="hidden-xs hidden-sm hidden-md"}
					{elseif $colCounter > '3' }
						{assign var='hide' value="hidden-xs hidden-sm"}
					{* {elseif $colCounter > '3'}
						{assign var='hide' value="hidden-xs"} *}
					{else}
						{assign var='hide' value=""}
					{/if}
                    {$displayColumns[type]}
				    {strip}
					<td {if $scope_row} scope='row' {/if} align='{$params.align|default:'left'}' valign="top" type="{$displayColumns.$col.type}" field="{$col|lower}" class="{$hide} {if $inline_edit && ($displayColumns.$col.inline_edit == 1 || !isset($displayColumns.$col.inline_edit))}inlineEdit{/if}{if ($params.type == 'teamset')}nowrap{/if}{if preg_match('/PHONE/', $col)} phone{/if}">
					    {if $params.link && !$params.customCode}
							{capture assign=linkModule}{if $params.dynamic_module}{$rowData[$params.dynamic_module]}{else}{$params.module|default:$pageData.bean.moduleDir}{/if}{/capture}
							{capture assign=action}{if $act}{$act}{else}DetailView{/if}{/capture}
							{capture assign=record}{$rowData[$params.id]|default:$rowData.ID}{/capture}
							{capture assign=url}index.php?module={$linkModule}&offset={$offset}&stamp={$pageData.stamp}&return_module={$linkModule}&action={$action}&record={$record}{/capture}
													<{$pageData.tag.$id[$params.ACLTag]|default:$pageData.tag.$id.MAIN} href="{sugar_ajax_url url=$url}">
						{/if}

						{if $params.customCode}
						{sugar_evalcolumn_old var=$params.customCode rowData=$rowData}
						{else}
							{if $col|lower == 'rv_status' || $col|lower == 'pv_status' || $col|lower == 'booking_status' || $col|lower == 'employee_status' || $col|lower == 'tinhtrang'}
								{capture assign=val}{sugar_field parentFieldArray=$rowData vardef=$params displayType=ListView field=$col}{/capture}
								{if trim($val) == 'Đã thu' || trim($val) == 'Đã chi' || trim($val) == 'Xuất vé' || trim($val) == 'Đã hoàn' || trim($val) == 'Đã ký'}
									<span class="label_badge badge text-success">
										{sugar_field parentFieldArray=$rowData vardef=$params displayType=ListView field=$col}
									</span>
								{elseif trim($val) == 'Công nợ' || trim($val) == 'Hủy' || trim($val) == 'Chờ duyệt' || trim($val) == 'Nghỉ việc'}
									<span class="label_badge badge text-danger">
										{sugar_field parentFieldArray=$rowData vardef=$params displayType=ListView field=$col}
									</span>
								{elseif trim($val) == 'Mới tạo'}
									<span class="label_badge badge text-dark">
										{sugar_field parentFieldArray=$rowData vardef=$params displayType=ListView field=$col}
									</span>
								{elseif trim($val) == 'Đã duyệt' || trim($val) == 'Đã gọi' || trim($val) == 'Hoàn tất' || trim($val) == 'Đang làm việc' || trim($val) == 'Đang hoàn'}
									<span class="label_badge badge text-primary">
										{sugar_field parentFieldArray=$rowData vardef=$params displayType=ListView field=$col}
									</span>
								{elseif trim($val) == 'Chờ TT' || trim($val) == 'Đang xử lý'}
									<span class="label_badge badge text-warning">
										{sugar_field parentFieldArray=$rowData vardef=$params displayType=ListView field=$col}
									</span>
								{else}
									<span class="label_badge badge text-primary">
										{sugar_field parentFieldArray=$rowData vardef=$params displayType=ListView field=$col}
									</span>
								{/if}
							{elseif $col|lower == 'amount' }
									<span class="label_badge badge text-danger fw-bold">
										{sugar_field parentFieldArray=$rowData vardef=$params displayType=ListView field=$col}
									</span>
							{else}
							{sugar_field parentFieldArray=$rowData vardef=$params displayType=ListView field=$col}
							{/if}
						{/if}

						{if empty($rowData.$col) && empty($params.customCode)}{/if}
						{if $params.link && !$params.customCode}
							</{$pageData.tag.$id[$params.ACLTag]|default:$pageData.tag.$id.MAIN}>
	                    	{/if}
					
                        {if $inline_edit && ($displayColumns.$col.inline_edit == 1 || !isset($displayColumns.$col.inline_edit))}<div class="inlineEditIcon">{sugar_getimage name="inline_edit_icon.svg" attr='border="0" ' alt="$alt_edit"}</div>{/if}
					</td>
					{/strip}
	                {assign var='scope_row' value=false}
					{counter name="colCounter"}

				{/foreach}
				{if !empty($quickViewLinks)}
	            	{capture assign=linkModule}{if $params.dynamic_module}{$rowData[$params.dynamic_module]}{else}{$pageData.bean.moduleDir}{/if}{/capture}
	            	{capture assign=action}{if $act}{$act}{else}EditView{/if}{/capture}
				{if isset($options.hide_edit_link) && $options.hide_edit_link === true}
				{else}
					<td>
                        {if $pageData.rowAccess[$id].edit && !empty($quickViewLinks)}
						<a class="edit-link" title='{$editLinkString}' id="edit-{$rowData.ID}" href="index.php?module={$linkModule}&offset={$offset}&stamp={$pageData.stamp}&return_module={$linkModule}&action={$action}&record={$rowData.ID}">
                                	{capture name='tmp1' assign='alt_edit'}{sugar_translate label="LNK_EDIT"}{/capture}
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
								<path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
							</svg>
						</a>
                        {/if}
					</td>
				{/if}

				{/if}
				<td class="hide-mobile" align='right'>{$pageData.additionalDetails.$id}</td>
		    	</tr>
		{foreachelse}
		<tr height='20' class='{$rowColor[0]}S1'>
		    <td colspan="{$colCount}">
		        <em>{$APP.LBL_NO_DATA}</em>
		    </td>
		</tr>
		{/foreach}
    {assign var="link_select_id" value="selectLinkBottom"}
    {assign var="link_action_id" value="actionLinkBottom"}
    {assign var="selectLink" value=$selectLinkBottom}
    {assign var="actionsLink" value=$actionsLinkBottom}
    {assign var="action_menu_location" value="bottom"}
	</tbody>
	<tfoot>
    	{include file='themes/SuiteP/include/ListView/ListViewPaginationBottom.tpl'}
	</tfoot>
	</table></div>
{/if}
{if $contextMenus}
<script type="text/javascript">
{$contextMenuScript}
{literal}
function lvg_nav(m,id,act,offset,t){
  if (t.href.search(/#/) < 0) {
  }
    else{
        if(act=='pte'){
            act='ProjectTemplatesEditView';
        }
        else if(act=='d'){
            act='DetailView';
        }else if( act =='ReportsWizard'){
            act = 'ReportsWizard';
        }else{
            act='EditView';
        }
    {/literal}
        url = 'index.php?module='+m+'&offset=' + offset + '&stamp={$pageData.stamp}&return_module='+m+'&action='+act+'&record='+id;
        t.href=url;
    {literal}
    }
}{/literal}
{literal}
    function lvg_dtails(id){{/literal}
        return SUGAR.util.getAdditionalDetails( '{$pageData.bean.moduleDir|default:$params.module}',id, 'adspan_'+id);{literal}}{/literal}
</script>
<script type="text/javascript" src="include/InlineEditing/inlineEditing.js"></script>
{/if}


{if $form.footerTpl}
    {sugar_include type="smarty" file=$form.headerTpl}
{/if}
