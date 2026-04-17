{assign var="alt_start" value=$navStrings.start}
{assign var="alt_next" value=$navStrings.next}
{assign var="alt_prev" value=$navStrings.previous}
{assign var="alt_end" value=$navStrings.end}

{if !isset($hideColumnFilter)}
    {assign var="currentModule" value = $pageData.bean.moduleDir}
    {assign var="hideColumnFilter" value = false}

    {php}
      $currentModule = $this->get_template_vars('currentModule');
      $APP_CONFIG = $this->get_template_vars("APP_CONFIG");

      if (
          isset($APP_CONFIG['hideColumnFilter'][$currentModule])
           && $APP_CONFIG['hideColumnFilter'][$currentModule] == true
        ) {
    {/php}
          {assign var="hideColumnFilter" value = true}
    {php}
        }
    {/php}
{/if}

	<tr id='pagination' class="pagination-unique pagination-bottom" role='presentation'>
		<td colspan='{if $prerow}{$colCount+1}{else}{$colCount}{/if}'>
			<table border='0' cellpadding='0' cellspacing='0' width='100%' class='paginationTable'>
				<tr>
					<td nowrap="nowrap" class='paginationActionButtons d-flex gap-2'>
						{if $prerow}

                        {sugar_action_menu id=$link_select_id params=$selectLink}
					
						{/if}

						{sugar_action_menu id=$link_action_id params=$actionsLink}

                        { if $actionDisabledLink ne "" }<div class='selectActionsDisabled' id='select_actions_disabled_{$action_menu_location}'>{$actionDisabledLink}</div>{/if}
                        {include file='include/ListView/ListViewButtons.tpl'}
                        {if $showFilterIcon}
							{include file='include/ListView/ListViewSearchLink.tpl'}
						{/if}
      {if empty($hideColumnFilter)}
          {include file='include/ListView/ListViewColumnsFilterLink.tpl'}
      {/if}
						&nbsp;{$selectedObjectsSpan}
					</td>
					<td  nowrap='nowrap' align="right" class='paginationChangeButtons' width="1%">
						{if $pageData.urls.startPage}
							<button type='button' id='listViewStartButton_{$action_menu_location}' name='listViewStartButton' title='{$navStrings.start}' class='btn btn-primary list-view-pagination-button' {if $prerow}onclick='return sListView.save_checks(0, "{$moduleString}");'{else} onClick='location.href="{$pageData.urls.startPage}"' {/if}>
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-bar-left" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M11.854 3.646a.5.5 0 0 1 0 .708L8.207 8l3.647 3.646a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 0 1 .708 0zM4.5 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 1 0v-13a.5.5 0 0 0-.5-.5z"/>
</svg>
							</button>
						{else}
							<button type='button' id='listViewStartButton_{$action_menu_location}' name='listViewStartButton' title='{$navStrings.start}' class='btn btn-disabled list-view-pagination-button' disabled='disabled'>
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-bar-left" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M11.854 3.646a.5.5 0 0 1 0 .708L8.207 8l3.647 3.646a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 0 1 .708 0zM4.5 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 1 0v-13a.5.5 0 0 0-.5-.5z"/>
</svg>
							</button>
						{/if}
						{if $pageData.urls.prevPage}
							<button type='button' id='listViewPrevButton_{$action_menu_location}' name='listViewPrevButton' title='{$navStrings.previous}' class='btn btn-primary list-view-pagination-button' {if $prerow}onclick='return sListView.save_checks({$pageData.offsets.prev}, "{$moduleString}")' {else} onClick='location.href="{$pageData.urls.prevPage}"'{/if}>
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
</svg>
							</button>
						{else}
							<button type='button' id='listViewPrevButton_{$action_menu_location}' name='listViewPrevButton' class='btn btn-disabled list-view-pagination-button' title='{$navStrings.previous}' disabled='disabled'>
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
</svg>
							</button>
						{/if}
					</td>
					<td nowrap='nowrap' width="1%" class="paginationActionButtons">
						<div class='pageNumbers'>({if $pageData.offsets.lastOffsetOnPage == 0}0{else}{$pageData.offsets.current+1}{/if} - {$pageData.offsets.lastOffsetOnPage} {$navStrings.of} {if $pageData.offsets.totalCounted}{$pageData.offsets.total}{else}{$pageData.offsets.total}{if $pageData.offsets.lastOffsetOnPage != $pageData.offsets.total}+{/if}{/if})</div>
					</td>
					<td nowrap='nowrap' align="right" class='paginationActionButtons' width="1%">
						{if $pageData.urls.nextPage}
							<button type='button' id='listViewNextButton_{$action_menu_location}' name='listViewNextButton' title='{$navStrings.next}' class='btn btn-primary list-view-pagination-button' {if $prerow}onclick='return sListView.save_checks({$pageData.offsets.next}, "{$moduleString}")' {else} onClick='location.href="{$pageData.urls.nextPage}"'{/if}>
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
</svg>
							</button>
						{else}
							<button type='button' id='listViewNextButton_{$action_menu_location}' name='listViewNextButton' class='btn btn-disabled list-view-pagination-button' title='{$navStrings.next}' disabled='disabled'>
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
</svg>
							</button>
						{/if}
						{if $pageData.urls.endPage  && $pageData.offsets.total != $pageData.offsets.lastOffsetOnPage}
							<button type='button' id='listViewEndButton_{$action_menu_location}' name='listViewEndButton' title='{$navStrings.end}' class='btn btn-primary list-view-pagination-button' {if $prerow}onclick='return sListView.save_checks("end", "{$moduleString}")' {else} onClick='location.href="{$pageData.urls.endPage}"'{/if}>
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-bar-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M4.146 3.646a.5.5 0 0 0 0 .708L7.793 8l-3.647 3.646a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708 0zM11.5 1a.5.5 0 0 1 .5.5v13a.5.5 0 0 1-1 0v-13a.5.5 0 0 1 .5-.5z"/>
</svg>
							</button>
						{elseif !$pageData.offsets.totalCounted || $pageData.offsets.total == $pageData.offsets.lastOffsetOnPage}
							<button type='button' id='listViewEndButton_{$action_menu_location}' name='listViewEndButton' title='{$navStrings.end}' class='btn btn-disabled list-view-pagination-button' disabled='disabled'>
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-bar-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M4.146 3.646a.5.5 0 0 0 0 .708L7.793 8l-3.647 3.646a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708 0zM11.5 1a.5.5 0 0 1 .5.5v13a.5.5 0 0 1-1 0v-13a.5.5 0 0 1 .5-.5z"/>
</svg>
							</button>
						{/if}
					</td>
					<td nowrap='nowrap' width="4px" class="paginationActionButtons"></td>
				</tr>
			</table>
		</td>
	</tr>