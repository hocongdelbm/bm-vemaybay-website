
{{* If templateMeta.maxColumnsBasic is not set, use maxColumns *}}
<input type='hidden' id="orderByInput" name='orderBy' value=''/>
<input type='hidden' id="sortOrder" name='sortOrder' value=''/>
{if !isset($templateMeta.maxColumnsBasic)}
	{assign var="basicMaxColumns" value=$templateMeta.maxColumns}
{else}
    {assign var="basicMaxColumns" value=$templateMeta.maxColumnsBasic}
{/if}

<script>
{literal}
	$(function() {
		var $dialog = $('<div></div>')
			.html(SUGAR.language.get('app_strings', 'LBL_SEARCH_HELP_TEXT'))
			.dialog({
				autoOpen: false,
				title: SUGAR.language.get('app_strings', 'LBL_HELP'),
				width: 700
			});
			
			$('#filterHelp').click(function() {
			$dialog.dialog('open');
			// prevent the default action, e.g., following a link
		});

	});
{/literal}
</script>

<ul class="nav nav-tabs-basic admin_tabs-lis" id="myTab" role="tablist">
	<li class="admin_tabs-item" role="presentation">
		<a id="basic_search_link" href="javascript:void(0)" class="active" accesskey="{$APP.LBL_ADV_SEARCH_LNK_KEY}">{$APP.LNK_BASIC_FILTER}</a>
	</li>
	{if !$searchFormInPopup}
	<li class="admin_tabs-item" role="presentation">
		<a id="advanced_search_link" href="javascript:void(0)" accesskey="{$APP.LBL_ADV_SEARCH_LNK_KEY}">{$APP.LNK_ADVANCED_FILTER}</a>
	</li>
	{/if}
 </ul>

<div class="search-fields-basic__theme box-tabs row g-0 border-top">
{{foreach name=colIteration from=$formData key=col item=colData}}
    {{math assign="accesskeycount" equation="$accesskeycount + 1"}}
    {{if $accesskeycount==1}} {{assign var='ACCKEY' value=$APP.LBL_FIRST_INPUT_SEARCH_KEY}} {{else}} {{assign var='ACCKEY' value=''}} {{/if}}

	{counter assign=index}
	{math equation="left % right" left=$index right=$basicMaxColumns assign=modVal }
	<div class="col-lg-4 col-md-6 col-xs-6 col-12 mb-2 px-2">
		<div class="row g-1 align-items-center">
			<div class="col-4 col-sm-12 col-md-4 col-lg-4 mt-0">
				{{if isset($colData.field.label)}}
				<label class="text-label" for='{{$colData.field.name}}' >{sugar_translate label='{{$colData.field.label}}' module='{{$module}}'}</label>
				{{elseif isset($fields[$colData.field.name])}}
				<label class="text-label" for='{{$fields[$colData.field.name].name}}'> {sugar_translate label='{{$fields[$colData.field.name].vname}}' module='{{$module}}'}</label>
				{{/if}}
			</div>
			<div class="col-8 col-sm-12 col-md-8 col-lg-8">
				<div class="search_basic-wrap">
					{{sugar_field parentFieldArray='fields' vardef=$fields[$colData.field.name] accesskey=$ACCKEY displayType='searchView' displayParams=$colData.field.displayParams typeOverride=$colData.field.type formName=$form_name}}
				</div>
			</div>
		</div>
	</div>	
{{/foreach}}

<div class="row g-0">
	<div class="col-12 col-sm-12 col-md-12 col-lg-12">
		<div class="submitButtons submitButtons__search-basic d-flex align-items-center gap-2">
			{{sugar_button module="$module" id="search" view="searchView"}}
			<input tabindex="2" title="Xóa" onclick="SUGAR.searchForm.clear_form(this.form); SUGAR.ajaxUI.submitForm(this.form); return false;" class="btn btn-secondary btn-clear" type="button" name="clear" id="search_form_clear" value="Reset">
		</div>
	</div>
</div>

</div>

<script>
	{literal}
	$(document).ready(function () {
		$( '#advanced_search_link' ).one( "click", function() {
			SUGAR.searchForm.searchFormSelect('{/literal}{$module}{literal}|advanced_search','{/literal}{$module}{literal}|basic_search');
		});

		$('#basic_search_link').one("click", function () {
			SUGAR.searchForm.searchFormSelect('{/literal}{$module}{literal}|basic_search', '{/literal}{$module}{literal}|advanced_search');
		});
	});
	{/literal}
</script>
