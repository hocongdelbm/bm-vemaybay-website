<span class="sugar_field" id="{{if empty($displayParams.idName)}}{{sugarvar key='name'}}{{else}}{{$displayParams.idName}}{{/if}}">
<a href="index.php?entryPoint=download&id={$fields.{{$vardef.fileId}}.value}&type=Documents" class="tabDetailViewDFLink" target='_blank'>{{sugarvar key='value'}}</a>

{if $fields.show_preview.value !== false}
	{* Preview also uses download.php redirect to public share *}
	<a href="index.php?entryPoint=download&id={$fields.{{$vardef.fileId}}.value}&type=Documents"
		class="tabDetailViewDFLink"
		target='_blank'
		style="border-bottom: 0px;">
		<i class="glyphicon glyphicon-eye-open"></i>
	</a>
{/if}

</span>
