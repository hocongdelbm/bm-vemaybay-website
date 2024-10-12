{if !empty($parentFieldArray.$col)}
{multienum_to_array string=$parentFieldArray.$col assign="vals"}
{foreach from=$vals item=item name=multiEnum}
{$vardef.options_list.$item}{if !$smarty.foreach.multiEnum.last},
{/if}
{/foreach}
{/if}
&nbsp;
